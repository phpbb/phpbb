<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* View private message
*/
function view_message($id, $mode, $folder_id, $msg_id, $folder, $message_row)
{
	global $user, $template, $auth, $db, $phpbb_container;
	global $phpbb_root_path, $request, $phpEx, $config, $phpbb_dispatcher;

	$user->add_lang(array('viewtopic', 'memberlist'));

	$msg_id		= (int) $msg_id;
	$folder_id	= (int) $folder_id;
	$author_id	= (int) $message_row['author_id'];
	$view		= $request->variable('view', '');

	/**
	* Modify private message data before it is prepared to be displayed
	*
	* @event core.ucp_pm_view_message_before
	* @var int		folder_id		ID of the folder the message is in
	* @var array	folder			Array with data of user's message folders
	* @var int		msg_id			ID of the private message
	* @var array	message_row		Array with message data
	* @var int		author_id		ID of the message author
	* @since 3.2.10-RC1
	* @since 3.3.1-RC1
	*/
	$vars = [
		'folder_id',
		'folder',
		'msg_id',
		'message_row',
		'author_id',
	];
	extract($phpbb_dispatcher->trigger_event('core.ucp_pm_view_message_before', compact($vars)));

	// Not able to view message, it was deleted by the sender
	if ($message_row['pm_deleted'])
	{
		$meta_info = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=pm&amp;folder=$folder_id");
		$message = $user->lang['NO_AUTH_READ_REMOVED_MESSAGE'];

		$message .= '<br /><br />' . sprintf($user->lang['RETURN_FOLDER'], '<a href="' . $meta_info . '">', '</a>');
		send_status_line(403, 'Forbidden');
		trigger_error($message);
	}

	// Do not allow hold messages to be seen
	if ($folder_id == PRIVMSGS_HOLD_BOX)
	{
		trigger_error('NO_AUTH_READ_HOLD_MESSAGE');
	}

	// Load the custom profile fields
	if ($config['load_cpf_pm'])
	{
		/* @var $cp \phpbb\profilefields\manager */
		$cp = $phpbb_container->get('profilefields.manager');

		$profile_fields = $cp->grab_profile_fields_data($author_id);
	}

	// Assign TO/BCC Addresses to template
	write_pm_addresses(array('to' => $message_row['to_address'], 'bcc' => $message_row['bcc_address']), $author_id);

	$user_info = get_user_information($author_id, $message_row);

	// Parse the message and subject
	$parse_flags = ($message_row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
	$message = generate_text_for_display($message_row['message_text'], $message_row['bbcode_uid'], $message_row['bbcode_bitfield'], $parse_flags, true);

	// Replace naughty words such as farty pants
	$message_row['message_subject'] = censor_text($message_row['message_subject']);

	// Editing information
	if ($message_row['message_edit_count'] && $config['display_last_edited'])
	{
		if (!$message_row['message_edit_user'])
		{
			$display_username = get_username_string('full', $author_id, $user_info['username'], $user_info['user_colour']);
		}
		else
		{
			$edit_user_info = get_user_information($message_row['message_edit_user'], false);
			$display_username = get_username_string('full', $message_row['message_edit_user'], $edit_user_info['username'], $edit_user_info['user_colour']);
		}
		$l_edited_by = '<br /><br />' . $user->lang('EDITED_TIMES_TOTAL', (int) $message_row['message_edit_count'], $display_username, $user->format_date($message_row['message_edit_time'], false, true));
	}
	else
	{
		$l_edited_by = '';
	}

	// Pull attachment data
	$display_notice = false;
	$attachments = array();

	if ($message_row['message_attachment'] && $config['allow_pm_attach'])
	{
		if ($auth->acl_get('u_pm_download'))
		{
			$sql = 'SELECT *
				FROM ' . ATTACHMENTS_TABLE . "
				WHERE post_msg_id = $msg_id
					AND in_message = 1
				ORDER BY filetime DESC, post_msg_id ASC";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$attachments[] = $row;
			}
			$db->sql_freeresult($result);

			// No attachments exist, but message table thinks they do so go ahead and reset attach flags
			if (!count($attachments))
			{
				$sql = 'UPDATE ' . PRIVMSGS_TABLE . "
					SET message_attachment = 0
					WHERE msg_id = $msg_id";
				$db->sql_query($sql);
			}
		}
		else
		{
			$display_notice = true;
		}
	}

	// Assign inline attachments
	if (!empty($attachments))
	{
		$update_count = array();
		parse_attachments(false, $message, $attachments, $update_count);

		// Update the attachment download counts
		if (count($update_count))
		{
			$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
				SET download_count = download_count + 1
				WHERE ' . $db->sql_in_set('attach_id', array_unique($update_count));
			$db->sql_query($sql);
		}
	}

	$user_info['sig'] = '';

	$signature = ($message_row['enable_sig'] && $config['allow_sig'] && $auth->acl_get('u_sig') && $user->optionget('viewsigs')) ? $user_info['user_sig'] : '';

	// End signature parsing, only if needed
	if ($signature)
	{
		$parse_flags = ($user_info['user_sig_bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
		$signature = generate_text_for_display($signature, $user_info['user_sig_bbcode_uid'], $user_info['user_sig_bbcode_bitfield'], $parse_flags, true);
	}

	$url = append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm');

	// Number of "to" recipients
	$num_recipients = (int) preg_match_all('/:?(u|g)_([0-9]+):?/', $message_row['to_address'], $match);

	$bbcode_status	= ($config['allow_bbcode'] && $config['auth_bbcode_pm'] && $auth->acl_get('u_pm_bbcode')) ? true : false;

	// Get the profile fields template data
	$cp_row = array();
	if ($config['load_cpf_pm'] && isset($profile_fields[$author_id]))
	{
		// Filter the fields we don't want to show
		foreach ($profile_fields[$author_id] as $used_ident => $profile_field)
		{
			if (!$profile_field['data']['field_show_on_pm'])
			{
				unset($profile_fields[$author_id][$used_ident]);
			}
		}

		if (isset($profile_fields[$author_id]))
		{
			$cp_row = $cp->generate_profile_fields_template_data($profile_fields[$author_id]);
		}
	}

	$u_pm = $u_jabber = '';

	if ($config['allow_privmsg'] && $auth->acl_get('u_sendpm') && ($user_info['user_allow_pm'] || $auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_')))
	{
		$u_pm = append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;mode=compose&amp;u=' . $author_id);
	}

	if ($config['jab_enable'] && $user_info['user_jabber'] && $auth->acl_get('u_sendim'))
	{
		$u_jabber = append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=contact&amp;action=jabber&amp;u=' . $author_id);
	}

	$can_edit_pm = ($message_row['message_time'] > time() - ($config['pm_edit_time'] * 60) || !$config['pm_edit_time']) && $folder_id == PRIVMSGS_OUTBOX && $auth->acl_get('u_pm_edit');

	$msg_data = array(
		'MESSAGE_AUTHOR_FULL'		=> get_username_string('full', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username']),
		'MESSAGE_AUTHOR_COLOUR'		=> get_username_string('colour', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username']),
		'MESSAGE_AUTHOR'			=> get_username_string('username', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username']),
		'U_MESSAGE_AUTHOR'			=> get_username_string('profile', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username']),

		'RANK_TITLE'		=> $user_info['rank_title'],
		'RANK_IMG'			=> $user_info['rank_image'],
		'AUTHOR_AVATAR'		=> (isset($user_info['avatar'])) ? $user_info['avatar'] : '',
		'AUTHOR_JOINED'		=> $user->format_date($user_info['user_regdate']),
		'AUTHOR_POSTS'		=> (int) $user_info['user_posts'],
		'U_AUTHOR_POSTS'	=> ($config['load_search'] && $auth->acl_get('u_search')) ? append_sid("{$phpbb_root_path}search.$phpEx", "author_id=$author_id&amp;sr=posts") : '',
		'CONTACT_USER'		=> $user->lang('CONTACT_USER', get_username_string('username', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username'])),

		'ONLINE_IMG'		=> (!$config['load_onlinetrack']) ? '' : ((isset($user_info['online']) && $user_info['online']) ? $user->img('icon_user_online', $user->lang['ONLINE']) : $user->img('icon_user_offline', $user->lang['OFFLINE'])),
		'S_ONLINE'			=> (!$config['load_onlinetrack']) ? false : ((isset($user_info['online']) && $user_info['online']) ? true : false),
		'DELETE_IMG'		=> $user->img('icon_post_delete', $user->lang['DELETE_MESSAGE']),
		'INFO_IMG'			=> $user->img('icon_post_info', $user->lang['VIEW_PM_INFO']),
		'PROFILE_IMG'		=> $user->img('icon_user_profile', $user->lang['READ_PROFILE']),
		'EMAIL_IMG'			=> $user->img('icon_contact_email', $user->lang['SEND_EMAIL']),
		'QUOTE_IMG'			=> $user->img('icon_post_quote', $user->lang['POST_QUOTE_PM']),
		'REPLY_IMG'			=> $user->img('button_pm_reply', $user->lang['POST_REPLY_PM']),
		'REPORT_IMG'		=> $user->img('icon_post_report', 'REPORT_PM'),
		'EDIT_IMG'			=> $user->img('icon_post_edit', $user->lang['POST_EDIT_PM']),
		'MINI_POST_IMG'		=> $user->img('icon_post_target', $user->lang['PM']),

		'SENT_DATE'			=> ($view == 'print') ? $user->format_date($message_row['message_time'], false, true) : $user->format_date($message_row['message_time']),
		'SUBJECT'			=> $message_row['message_subject'],
		'MESSAGE'			=> $message,
		'SIGNATURE'			=> ($message_row['enable_sig']) ? $signature : '',
		'EDITED_MESSAGE'	=> $l_edited_by,
		'MESSAGE_ID'		=> $message_row['msg_id'],

		'U_PM'			=>  $u_pm,
		'U_JABBER'		=>  $u_jabber,

		'U_DELETE'			=> ($auth->acl_get('u_pm_delete')) ? "$url&amp;mode=compose&amp;action=delete&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
		'U_EMAIL'			=> $user_info['email'],
		'U_REPORT'			=> ($config['allow_pm_report']) ? $phpbb_container->get('controller.helper')->route('phpbb_report_pm_controller', array('id' => $message_row['msg_id'])) : '',
		'U_QUOTE'			=> ($auth->acl_get('u_sendpm') && $author_id != ANONYMOUS) ? "$url&amp;mode=compose&amp;action=quote&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
		'U_EDIT'			=> $can_edit_pm ? "$url&amp;mode=compose&amp;action=edit&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
		'U_POST_REPLY_PM'	=> ($auth->acl_get('u_sendpm') && $author_id != ANONYMOUS) ? "$url&amp;mode=compose&amp;action=reply&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
		'U_POST_REPLY_ALL'	=> ($auth->acl_get('u_sendpm') && $author_id != ANONYMOUS) ? "$url&amp;mode=compose&amp;action=reply&amp;f=$folder_id&amp;reply_to_all=1&amp;p=" . $message_row['msg_id'] : '',
		'U_PREVIOUS_PM'		=> "$url&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] . "&amp;view=previous",
		'U_NEXT_PM'			=> "$url&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] . "&amp;view=next",

		'U_PM_ACTION'		=> $url . '&amp;mode=compose&amp;f=' . $folder_id . '&amp;p=' . $message_row['msg_id'],

		'S_HAS_ATTACHMENTS'	=> (count($attachments)) ? true : false,
		'S_DISPLAY_NOTICE'	=> $display_notice && $message_row['message_attachment'],
		'S_AUTHOR_DELETED'	=> ($author_id == ANONYMOUS) ? true : false,
		'S_SPECIAL_FOLDER'	=> in_array($folder_id, array(PRIVMSGS_NO_BOX, PRIVMSGS_OUTBOX)),
		'S_PM_RECIPIENTS'	=> $num_recipients,
		'S_BBCODE_ALLOWED'	=> ($bbcode_status) ? 1 : 0,
		'S_CUSTOM_FIELDS'	=> (!empty($cp_row['row'])) ? true : false,

		'U_PRINT_PM'		=> ($config['print_pm'] && $auth->acl_get('u_pm_printpm')) ? "$url&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] . "&amp;view=print" : '',
		'U_FORWARD_PM'		=> ($config['forward_pm'] && $auth->acl_get('u_sendpm') && $auth->acl_get('u_pm_forward')) ? "$url&amp;mode=compose&amp;action=forward&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
	);

	/**
	* Modify pm and sender data before it is assigned to the template
	*
	* @event core.ucp_pm_view_messsage
	* @var	mixed	id			Active module category (can be int or string)
	* @var	string	mode		Active module
	* @var	int		folder_id	ID of the folder the message is in
	* @var	int		msg_id		ID of the private message
	* @var	array	folder		Array with data of user's message folders
	* @var	array	message_row	Array with message data
	* @var	array	cp_row		Array with senders custom profile field data
	* @var	array	msg_data	Template array with message data
	* @var 	array	user_info	User data of the sender
	* @since 3.1.0-a1
	* @changed 3.1.6-RC1		Added user_info into event
	* @changed 3.2.2-RC1		Deprecated
	* @deprecated 4.0.0			Event name is misspelled and is replaced with new event with correct name
	*/
	$vars = array(
		'id',
		'mode',
		'folder_id',
		'msg_id',
		'folder',
		'message_row',
		'cp_row',
		'msg_data',
		'user_info',
	);
	extract($phpbb_dispatcher->trigger_event('core.ucp_pm_view_messsage', compact($vars)));

	/**
	 * Modify pm and sender data before it is assigned to the template
	 *
	 * @event core.ucp_pm_view_message
	 * @var	mixed	id			Active module category (can be int or string)
	 * @var	string	mode		Active module
	 * @var	int		folder_id	ID of the folder the message is in
	 * @var	int		msg_id		ID of the private message
	 * @var	array	folder		Array with data of user's message folders
	 * @var	array	message_row	Array with message data
	 * @var	array	cp_row		Array with senders custom profile field data
	 * @var	array	msg_data	Template array with message data
	 * @var array	user_info	User data of the sender
	 * @var array	attachments	Attachments data
	 * @since 3.2.2-RC1
	 * @changed 3.2.5-RC1 Added attachments
	 */
	$vars = array(
		'id',
		'mode',
		'folder_id',
		'msg_id',
		'folder',
		'message_row',
		'cp_row',
		'msg_data',
		'user_info',
		'attachments',
	);
	extract($phpbb_dispatcher->trigger_event('core.ucp_pm_view_message', compact($vars)));

	$template->assign_vars($msg_data);

	$contact_fields = array(
		array(
			'ID'		=> 'pm',
			'NAME'		=> $user->lang['SEND_PRIVATE_MESSAGE'],
			'U_CONTACT' => $u_pm,
		),
		array(
			'ID'		=> 'email',
			'NAME'		=> $user->lang['SEND_EMAIL'],
			'U_CONTACT'	=> $user_info['email'],
		),
		array(
			'ID'		=> 'jabber',
			'NAME'		=> $user->lang['JABBER'],
			'U_CONTACT'	=> $u_jabber,
		),
	);

	foreach ($contact_fields as $field)
	{
		if ($field['U_CONTACT'])
		{
			$template->assign_block_vars('contact', $field);
		}
	}

	// Display the custom profile fields
	if (!empty($cp_row['row']))
	{
		$template->assign_vars($cp_row['row']);

		foreach ($cp_row['blockrow'] as $cp_block_row)
		{
			$template->assign_block_vars('custom_fields', $cp_block_row);

			if ($cp_block_row['S_PROFILE_CONTACT'])
			{
				$template->assign_block_vars('contact', array(
					'ID'		=> $cp_block_row['PROFILE_FIELD_IDENT'],
					'NAME'		=> $cp_block_row['PROFILE_FIELD_NAME'],
					'U_CONTACT'	=> $cp_block_row['PROFILE_FIELD_CONTACT'],
				));
			}
		}
	}

	// Display not already displayed Attachments for this post, we already parsed them. ;)
	if (isset($attachments) && count($attachments))
	{
		foreach ($attachments as $attachment)
		{
			$template->assign_block_vars('attachment', array(
				'DISPLAY_ATTACHMENT'	=> $attachment)
			);
		}
	}

	if (!isset($_REQUEST['view']) || $request->variable('view', '') != 'print')
	{
		// Message History
		if (message_history($msg_id, $user->data['user_id'], $message_row, $folder))
		{
			$template->assign_var('S_DISPLAY_HISTORY', true);
		}
	}
}

/**
* Get user information (only for message display)
*/
function get_user_information($user_id, $user_row)
{
	global $db, $auth, $user;
	global $phpbb_root_path, $phpEx, $config;

	if (!$user_id)
	{
		return array();
	}

	if (empty($user_row))
	{
		$sql = 'SELECT *
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $user_id;
		$result = $db->sql_query($sql);
		$user_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
	}

	// Some standard values
	$user_row['online'] = false;
	$user_row['rank_title'] = $user_row['rank_image'] = $user_row['rank_image_src'] = $user_row['email'] = '';

	// Generate online information for user
	if ($config['load_onlinetrack'])
	{
		$sql = 'SELECT session_user_id, MAX(session_time) as online_time, MIN(session_viewonline) AS viewonline
			FROM ' . SESSIONS_TABLE . "
			WHERE session_user_id = $user_id
			GROUP BY session_user_id";
		$result = $db->sql_query_limit($sql, 1);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$update_time = $config['load_online_time'] * 60;
		if ($row)
		{
			$user_row['online'] = (time() - $update_time < $row['online_time'] && ($row['viewonline'] || $auth->acl_get('u_viewonline'))) ? true : false;
		}
	}

	$user_row['avatar'] = ($user->optionget('viewavatars')) ? phpbb_get_user_avatar($user_row) : '';

	if (!function_exists('phpbb_get_user_rank'))
	{
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
	}

	$user_rank_data = phpbb_get_user_rank($user_row, $user_row['user_posts']);
	$user_row['rank_title'] = $user_rank_data['title'];
	$user_row['rank_image'] = $user_rank_data['img'];
	$user_row['rank_image_src'] = $user_rank_data['img_src'];

	if ((!empty($user_row['user_allow_viewemail']) && $auth->acl_get('u_sendemail')) || $auth->acl_get('a_email'))
	{
		$user_row['email'] = ($config['board_email_form'] && $config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=email&amp;u=$user_id") : ((($config['board_hide_emails'] && !$auth->acl_get('a_email')) || empty($user_row['user_email'])) ? '' : 'mailto:' . $user_row['user_email']);
	}

	return $user_row;
}
