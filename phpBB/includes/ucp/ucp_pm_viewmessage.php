<?php
/** 
*
* @package ucp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* View private message
*/
function view_message($id, $mode, $folder_id, $msg_id, $folder, $message_row)
{
	global $user, $template, $auth, $db, $cache;
	global $phpbb_root_path, $phpEx, $config;

	$user->add_lang(array('viewtopic', 'memberlist'));

	$msg_id		= (int) $msg_id;
	$folder_id	= (int) $folder_id;
	$author_id	= (int) $message_row['author_id'];

	// Not able to view message, it was deleted by the sender
	if ($message_row['pm_deleted'])
	{
		trigger_error('NO_AUTH_READ_REMOVED_MESSAGE');
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

	// Assign TO/BCC Addresses to template
	write_pm_addresses(array('to' => $message_row['to_address'], 'bcc' => $message_row['bcc_address']), $author_id);

	$user_info = get_user_information($author_id, $message_row);

	// Parse the message and subject
	$message = $message_row['message_text'];
	$message = str_replace("\n", '<br />', censor_text($message));

	// Second parse bbcode here
	if ($message_row['bbcode_bitfield'])
	{
		$bbcode->bbcode_second_pass($message, $message_row['bbcode_uid'], $message_row['bbcode_bitfield']);
	}

	// Always process smilies after parsing bbcodes
	$message = smiley_text($message);

	// Replace naughty words such as farty pants
	$message_row['message_subject'] = censor_text($message_row['message_subject']);

	// Editing information
	if ($message_row['message_edit_count'] && $config['display_last_edited'])
	{
		$l_edit_time_total = ($message_row['message_edit_count'] == 1) ? $user->lang['EDITED_TIME_TOTAL'] : $user->lang['EDITED_TIMES_TOTAL'];
		$l_edited_by = '<br /><br />' . sprintf($l_edit_time_total, (!$message_row['message_edit_user']) ? $message_row['username'] : $message_row['message_edit_user'], $user->format_date($message_row['message_edit_time']), $message_row['message_edit_count']);
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
		$signature = str_replace("\n", '<br />', censor_text($signature));

		if ($user_info['user_sig_bbcode_bitfield'])
		{
			if ($bbcode === false)
			{
				include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
				$bbcode = new bbcode($user_info['user_sig_bbcode_bitfield']);
			}

			$bbcode->bbcode_second_pass($signature, $user_info['user_sig_bbcode_uid'], $user_info['user_sig_bbcode_bitfield']);
		}

		$signature = smiley_text($signature);
	}

	$url = append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm');

	$template->assign_vars(array(
		'MESSAGE_AUTHOR_FULL'		=> get_username_string('full', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username']),
		'MESSAGE_AUTHOR_COLOUR'		=> get_username_string('colour', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username']),
		'MESSAGE_AUTHOR'			=> get_username_string('username', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username']),
		'U_MESSAGE_AUTHOR'			=> get_username_string('profile', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username']),

		'RANK_TITLE'		=> $user_info['rank_title'],
		'RANK_IMG'			=> $user_info['rank_image'],
		'AUTHOR_AVATAR'		=> (isset($user_info['avatar'])) ? $user_info['avatar'] : '',
		'AUTHOR_JOINED'		=> $user->format_date($user_info['user_regdate']),
		'AUTHOR_POSTS'		=> (!empty($user_info['user_posts'])) ? $user_info['user_posts'] : '',
		'AUTHOR_FROM'		=> (!empty($user_info['user_from'])) ? $user_info['user_from'] : '',

		'ONLINE_IMG'		=> (!$config['load_onlinetrack']) ? '' : ((isset($user_info['online']) && $user_info['online']) ? $user->img('icon_user_online', $user->lang['ONLINE']) : $user->img('icon_user_offline', $user->lang['OFFLINE'])),
		'S_ONLINE'			=> (!$config['load_onlinetrack']) ? false : ((isset($user_info['online']) && $user_info['online']) ? true : false),
		'DELETE_IMG'		=> $user->img('icon_post_delete', $user->lang['DELETE_MESSAGE']),
		'INFO_IMG'			=> $user->img('icon_post_info', $user->lang['VIEW_PM_INFO']),
		'PROFILE_IMG'		=> $user->img('icon_user_profile', $user->lang['READ_PROFILE']),
		'EMAIL_IMG'			=> $user->img('icon_contact_email', $user->lang['SEND_EMAIL']),
		'QUOTE_IMG'			=> $user->img('icon_post_quote', $user->lang['POST_QUOTE_PM']),
		'REPLY_IMG'			=> $user->img('button_pm_reply', $user->lang['POST_REPLY_PM']),
		'EDIT_IMG'			=> $user->img('icon_post_edit', $user->lang['POST_EDIT_PM']),
		'MINI_POST_IMG'		=> $user->img('icon_post_target', $user->lang['PM']),

		'SENT_DATE'			=> $user->format_date($message_row['message_time']),
		'SUBJECT'			=> $message_row['message_subject'],
		'MESSAGE'			=> $message,
		'SIGNATURE'			=> ($message_row['enable_sig']) ? $signature : '',
		'EDITED_MESSAGE'	=> $l_edited_by,
		'MESSAGE_ID'		=> $message_row['msg_id'],

		'U_INFO'			=> ($auth->acl_get('m_info') && $message_row['pm_forwarded']) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'mode=pm_details&amp;p=' . $message_row['msg_id'], true, $user->session_id) : '',
		'U_DELETE'			=> ($auth->acl_get('u_pm_delete')) ? "$url&amp;mode=compose&amp;action=delete&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
		'U_EMAIL'			=> $user_info['email'],
		'U_QUOTE'			=> ($auth->acl_get('u_sendpm') && $author_id != ANONYMOUS) ? "$url&amp;mode=compose&amp;action=quote&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
		'U_EDIT'			=> (($message_row['message_time'] > time() - ($config['pm_edit_time'] * 60) || !$config['pm_edit_time']) && $folder_id == PRIVMSGS_OUTBOX && $auth->acl_get('u_pm_edit')) ? "$url&amp;mode=compose&amp;action=edit&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
		'U_POST_REPLY_PM'	=> ($auth->acl_get('u_sendpm') && $author_id != ANONYMOUS) ? "$url&amp;mode=compose&amp;action=reply&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
		'U_PREVIOUS_PM'		=> "$url&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] . "&amp;view=previous",
		'U_NEXT_PM'			=> "$url&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] . "&amp;view=next",

		'S_HAS_ATTACHMENTS'	=> (sizeof($attachments)) ? true : false,
		'S_DISPLAY_NOTICE'	=> $display_notice && $message_row['message_attachment'],
		'S_AUTHOR_DELETED'	=> ($author_id == ANONYMOUS) ? true : false,

		'U_PRINT_PM'		=> ($config['print_pm'] && $auth->acl_get('u_pm_printpm')) ? "$url&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] . "&amp;view=print" : '',
		'U_FORWARD_PM'		=> ($config['forward_pm'] && $auth->acl_get('u_pm_forward')) ? "$url&amp;mode=compose&amp;action=forward&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '')
	);

	// Display not already displayed Attachments for this post, we already parsed them. ;)
	if (isset($attachments) && sizeof($attachments))
	{
		foreach ($attachments as $attachment)
		{
			$template->assign_block_vars('attachment', array(
				'DISPLAY_ATTACHMENT'	=> $attachment)
			);
		}
	}

	if (!isset($_REQUEST['view']) || $_REQUEST['view'] != 'print')
	{
		// Message History
		if (message_history($msg_id, $user->data['user_id'], $message_row, $folder))
		{
			$template->assign_var('S_DISPLAY_HISTORY', true);
		}
	}
}

/**
* Display Message History
*/
function message_history($msg_id, $user_id, $message_row, $folder)
{
	global $db, $user, $config, $template, $phpbb_root_path, $phpEx, $auth, $bbcode;

	// Get History Messages (could be newer)
	$sql = 'SELECT t.*, p.*, u.*
		FROM ' . PRIVMSGS_TABLE . ' p, ' . PRIVMSGS_TO_TABLE . ' t, ' . USERS_TABLE . ' u
		WHERE t.msg_id = p.msg_id
			AND p.author_id = u.user_id
			AND t.folder_id NOT IN (' . PRIVMSGS_NO_BOX . ', ' . PRIVMSGS_HOLD_BOX . ")
			AND t.user_id = $user_id";

	if (!$message_row['root_level'])
	{
		$sql .= " AND (p.root_level = $msg_id OR (p.root_level = 0 AND p.msg_id = $msg_id))";
	}
	else
	{
		$sql .= " AND (p.root_level = " . $message_row['root_level'] . ' OR p.msg_id = ' . $message_row['root_level'] . ')';
	}
	$sql .= ' ORDER BY p.message_time DESC';

	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);

	if (!$row)
	{
		$db->sql_freeresult($result);
		return false;
	}

	$rowset = array();
	$bbcode_bitfield = '';
	$folder_url = append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm') . '&amp;folder=';

	do
	{
		$folder_id = (int) $row['folder_id'];

		$row['folder'][] = (isset($folder[$folder_id])) ? '<a href="' . $folder_url . $folder_id . '">' . $folder[$folder_id]['folder_name'] . '</a>' : $user->lang['UNKNOWN_FOLDER'];

		if (isset($rowset[$row['msg_id']]))
		{
			$rowset[$row['msg_id']]['folder'][] = (isset($folder[$folder_id])) ? '<a href="' . $folder_url . $folder_id . '">' . $folder[$folder_id]['folder_name'] . '</a>' : $user->lang['UNKNOWN_FOLDER'];
		}
		else
		{
			$rowset[$row['msg_id']] = $row;
			$bbcode_bitfield = $bbcode_bitfield | base64_decode($row['bbcode_bitfield']);
		}
	}
	while ($row = $db->sql_fetchrow($result));
	$db->sql_freeresult($result);

	$title = $row['message_subject'];

	if (sizeof($rowset) == 1)
	{
		return false;
	}

	// Instantiate BBCode class
	if ((empty($bbcode) || $bbcode === false) && $bbcode_bitfield !== '')
	{
		if (!class_exists('bbcode'))
		{
			include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
		}
		$bbcode = new bbcode(base64_encode($bbcode_bitfield));
	}

	$title = censor_text($title);

	$url = append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm');
	$next_history_pm = $previous_history_pm = $prev_id = 0;

	foreach ($rowset as $id => $row)
	{
		$author_id	= $row['author_id'];
		$folder_id	= (int) $row['folder_id'];

		$subject	= $row['message_subject'];
		$message	= $row['message_text'];

		$message = censor_text($message);
		$message = str_replace("\n", '<br />', $message);

		if ($row['bbcode_bitfield'])
		{
			$bbcode->bbcode_second_pass($message, $row['bbcode_uid'], $row['bbcode_bitfield']);
		}

		$message = smiley_text($message, !$row['enable_smilies']);

		$subject = censor_text($subject);

		if ($id == $msg_id)
		{
			$next_history_pm = next($rowset);
			$next_history_pm = (sizeof($next_history_pm)) ? (int) $next_history_pm['msg_id'] : 0;
			$previous_history_pm = $prev_id;
		}

		$template->assign_block_vars('history_row', array(
			'MESSAGE_AUTHOR_FULL'		=> get_username_string('full', $author_id, $row['username'], $row['user_colour'], $row['username']),
			'MESSAGE_AUTHOR_COLOUR'		=> get_username_string('colour', $author_id, $row['username'], $row['user_colour'], $row['username']),
			'MESSAGE_AUTHOR'			=> get_username_string('username', $author_id, $row['username'], $row['user_colour'], $row['username']),
			'U_MESSAGE_AUTHOR'			=> get_username_string('profile', $author_id, $row['username'], $row['user_colour'], $row['username']),

			'SUBJECT'		=> $subject,
			'SENT_DATE'		=> $user->format_date($row['message_time']),
			'MESSAGE'		=> $message,
			'FOLDER'		=> implode(', ', $row['folder']),

			'S_CURRENT_MSG'		=> ($row['msg_id'] == $msg_id),
			'S_AUTHOR_DELETED'	=> ($author_id == ANONYMOUS) ? true : false,

			'U_MSG_ID'			=> $row['msg_id'],
			'U_VIEW_MESSAGE'	=> "$url&amp;f=$folder_id&amp;p=" . $row['msg_id'],
			'U_QUOTE'			=> ($auth->acl_get('u_sendpm') && $author_id != ANONYMOUS && $author_id != $user->data['user_id']) ? "$url&amp;mode=compose&amp;action=quote&amp;f=" . $folder_id . "&amp;p=" . $row['msg_id'] : '',
			'U_POST_REPLY_PM'	=> ($author_id != $user->data['user_id'] && $author_id != ANONYMOUS && $auth->acl_get('u_sendpm')) ? "$url&amp;mode=compose&amp;action=reply&amp;f=$folder_id&amp;p=" . $row['msg_id'] : '')
		);
		unset($rowset[$id]);
		$prev_id = $id;
	}

	$template->assign_vars(array(
		'QUOTE_IMG'	=> $user->img('icon_post_quote', $user->lang['REPLY_WITH_QUOTE']),
		'TITLE'		=> $title,

		'U_VIEW_NEXT_HISTORY'		=> "$url&amp;p=" . (($next_history_pm) ? $next_history_pm : $msg_id),
		'U_VIEW_PREVIOUS_HISTORY'	=> "$url&amp;p=" . (($previous_history_pm) ? $previous_history_pm : $msg_id))
	);

	return true;
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
			$user_row['online'] = (time() - $update_time < $row['online_time'] && ($row['viewonline'] && $user_row['user_allow_viewonline'])) ? true : false;
		}
	}

	if (!function_exists('get_user_avatar'))
	{
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
	}

	$user_row['avatar'] = ($user->optionget('viewavatars')) ? get_user_avatar($user_row['user_avatar'], $user_row['user_avatar_type'], $user_row['user_avatar_width'], $user_row['user_avatar_height']) : '';

	get_user_rank($user_row['user_rank'], $user_row['user_posts'], $user_row['rank_title'], $user_row['rank_image'], $user_row['rank_image_src']);

	if (!empty($user_row['user_allow_viewemail']) || $auth->acl_get('a_email'))
	{
		$user_row['email'] = ($config['board_email_form'] && $config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=email&amp;u=$user_id") : ((($config['board_hide_emails'] && !$auth->acl_get('a_email')) || empty($user_row['user_email'])) ? '' : 'mailto:' . $user_row['user_email']);
	}

	return $user_row;
}

?>