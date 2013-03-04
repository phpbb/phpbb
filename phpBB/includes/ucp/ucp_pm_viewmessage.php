<?php
/**
*
* @package ucp
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
	global $user, $template, $auth, $db, $cache;
	global $phpbb_root_path, $request, $phpEx, $config, $phpbb_dispatcher;

	$user->add_lang(array('viewtopic', 'memberlist'));

	$msg_id		= (int) $msg_id;
	$folder_id	= (int) $folder_id;
	$author_id	= (int) $message_row['author_id'];
	$view		= request_var('view', '');

	// Not able to view message, it was deleted by the sender
	if ($message_row['pm_deleted'])
	{
		$meta_info = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=pm&amp;folder=$folder_id");
		$message = $user->lang['NO_AUTH_READ_REMOVED_MESSAGE'];

		$message .= '<br /><br />' . sprintf($user->lang['RETURN_FOLDER'], '<a href="' . $meta_info . '">', '</a>');
		trigger_error($message);
	}

	// Do not allow hold messages to be seen
	if ($folder_id == PRIVMSGS_HOLD_BOX)
	{
		trigger_error('NO_AUTH_READ_HOLD_MESSAGE');
	}

	// Grab icons
	$icons = $cache->obtain_icons();

	$bbcode = false;

	// Instantiate BBCode if need be
	if ($message_row['bbcode_bitfield'])
	{
		include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
		$bbcode = new bbcode($message_row['bbcode_bitfield']);
	}

	// Load the custom profile fields
	if ($config['load_cpf_pm'])
	{
		if (!class_exists('custom_profile'))
		{
			include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);
		}
		$cp = new custom_profile();

		$profile_fields = $cp->generate_profile_fields_template('grab', $author_id);
	}

	// Assign TO/BCC Addresses to template
	write_pm_addresses(array('to' => $message_row['to_address'], 'bcc' => $message_row['bcc_address']), $author_id);

	$user_info = get_user_information($author_id, $message_row);

	// Parse the message and subject
	$message = censor_text($message_row['message_text']);

	// Second parse bbcode here
	if ($message_row['bbcode_bitfield'])
	{
		$bbcode->bbcode_second_pass($message, $message_row['bbcode_uid'], $message_row['bbcode_bitfield']);
	}

	// Always process smilies after parsing bbcodes
	$message = bbcode_nl2br($message);
	$message = smiley_text($message);

	// Replace naughty words such as farty pants
	$message_row['message_subject'] = censor_text($message_row['message_subject']);

	// Editing information
	if ($message_row['message_edit_count'] && $config['display_last_edited'])
	{
		$l_edit_time_total = ($message_row['message_edit_count'] == 1) ? $user->lang['EDITED_TIME_TOTAL'] : $user->lang['EDITED_TIMES_TOTAL'];
		$l_edited_by = '<br /><br />' . sprintf($l_edit_time_total, (!$message_row['message_edit_user']) ? $message_row['username'] : $message_row['message_edit_user'], $user->format_date($message_row['message_edit_time'], false, true), $message_row['message_edit_count']);
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
			if (!sizeof($attachments))
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
		if (sizeof($update_count))
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
		$signature = censor_text($signature);

		if ($user_info['user_sig_bbcode_bitfield'])
		{
			if ($bbcode === false)
			{
				include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
				$bbcode = new bbcode($user_info['user_sig_bbcode_bitfield']);
			}

			$bbcode->bbcode_second_pass($signature, $user_info['user_sig_bbcode_uid'], $user_info['user_sig_bbcode_bitfield']);
		}

		$signature = bbcode_nl2br($signature);
		$signature = smiley_text($signature);
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
			$cp_row = $cp->generate_profile_fields_template('show', false, $profile_fields[$author_id]);
		}
	}

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
		'AUTHOR_FROM'		=> (!empty($user_info['user_from'])) ? $user_info['user_from'] : '',

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

		'U_PM'			=> ($config['allow_privmsg'] && $auth->acl_get('u_sendpm') && ($user_info['user_allow_pm'] || $auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_'))) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;mode=compose&amp;u=' . $author_id) : '',
		'U_WWW'			=> (!empty($user_info['user_website'])) ? $user_info['user_website'] : '',
		'U_ICQ'			=> ($user_info['user_icq']) ? 'http://www.icq.com/people/' . urlencode($user_info['user_icq']) . '/' : '',
		'U_AIM'			=> ($user_info['user_aim'] && $auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=contact&amp;action=aim&amp;u=' . $author_id) : '',
		'U_YIM'			=> ($user_info['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . urlencode($user_info['user_yim']) . '&amp;.src=pg' : '',
		'U_MSN'			=> ($user_info['user_msnm'] && $auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=contact&amp;action=msnm&amp;u=' . $author_id) : '',
		'U_JABBER'		=> ($user_info['user_jabber'] && $auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=contact&amp;action=jabber&amp;u=' . $author_id) : '',

		'U_DELETE'			=> ($auth->acl_get('u_pm_delete')) ? "$url&amp;mode=compose&amp;action=delete&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
		'U_EMAIL'			=> $user_info['email'],
		'U_REPORT'			=> ($config['allow_pm_report']) ? append_sid("{$phpbb_root_path}report.$phpEx", "pm=" . $message_row['msg_id']) : '',
		'U_QUOTE'			=> ($auth->acl_get('u_sendpm') && $author_id != ANONYMOUS) ? "$url&amp;mode=compose&amp;action=quote&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
		'U_EDIT'			=> (($message_row['message_time'] > time() - ($config['pm_edit_time'] * 60) || !$config['pm_edit_time']) && $folder_id == PRIVMSGS_OUTBOX && $auth->acl_get('u_pm_edit')) ? "$url&amp;mode=compose&amp;action=edit&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
		'U_POST_REPLY_PM'	=> ($auth->acl_get('u_sendpm') && $author_id != ANONYMOUS) ? "$url&amp;mode=compose&amp;action=reply&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
		'U_POST_REPLY_ALL'	=> ($auth->acl_get('u_sendpm') && $author_id != ANONYMOUS) ? "$url&amp;mode=compose&amp;action=reply&amp;f=$folder_id&amp;reply_to_all=1&amp;p=" . $message_row['msg_id'] : '',
		'U_PREVIOUS_PM'		=> "$url&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] . "&amp;view=previous",
		'U_NEXT_PM'			=> "$url&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] . "&amp;view=next",

		'U_PM_ACTION'		=> $url . '&amp;mode=compose&amp;f=' . $folder_id . '&amp;p=' . $message_row['msg_id'],

		'S_HAS_ATTACHMENTS'	=> (sizeof($attachments)) ? true : false,
		'S_HAS_MULTIPLE_ATTACHMENTS' => (sizeof($attachments) > 1),
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
	* var	array	folder		Array with data of user's message folders
	* @var	array	message_row		Array with message data
	* @var	array	cp_row		Array with senders custom profile field data
	* @var	array	msg_data	Template array with message data
	* @since 3.1-A1
	*/
	$vars = array('id', 'mode', 'folder_id', 'msg_id', 'folder', 'message_row', 'cp_row', 'msg_data');
	extract($phpbb_dispatcher->trigger_event('core.ucp_pm_view_messsage', compact($vars)));

	$template->assign_vars($msg_data);

	// Display the custom profile fields
	if (!empty($cp_row['row']))
	{
		$template->assign_vars($cp_row['row']);

		foreach ($cp_row['blockrow'] as $cp_block_row)
		{
			$template->assign_block_vars('custom_fields', $cp_block_row);
		}
	}

	// Display not already displayed Attachments for this post, we already parsed them. ;)
	if (isset($attachments) && sizeof($attachments))
	{
		$methods = phpbb_gen_download_links('post_msg_id', $msg_id, $phpbb_root_path, $phpEx);
		foreach ($methods as $method)
		{
			$template->assign_block_vars('dl_method', $method);
		}
	
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
	global $db, $auth, $user, $cache;
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

	if (!function_exists('phpbb_get_user_avatar'))
	{
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
	}

	$user_row['avatar'] = ($user->optionget('viewavatars')) ? phpbb_get_user_avatar($user_row) : '';

	get_user_rank($user_row['user_rank'], $user_row['user_posts'], $user_row['rank_title'], $user_row['rank_image'], $user_row['rank_image_src']);

	if ((!empty($user_row['user_allow_viewemail']) && $auth->acl_get('u_sendemail')) || $auth->acl_get('a_email'))
	{
		$user_row['email'] = ($config['board_email_form'] && $config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=email&amp;u=$user_id") : ((($config['board_hide_emails'] && !$auth->acl_get('a_email')) || empty($user_row['user_email'])) ? '' : 'mailto:' . $user_row['user_email']);
	}

	return $user_row;
}
