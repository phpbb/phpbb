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
* Handling actions in post details screen
*/
function mcp_post_details($id, $mode, $action)
{
	global $phpEx, $phpbb_root_path, $config, $request;
	global $template, $db, $user, $auth;
	global $phpbb_container, $phpbb_dispatcher;

	$user->add_lang('posting');

	$post_id = $request->variable('p', 0);
	$start	= $request->variable('start', 0);

	// Get post data
	$post_info = phpbb_get_post_data(array($post_id), false, true);

	add_form_key('mcp_post_details');

	if (!count($post_info))
	{
		trigger_error('POST_NOT_EXIST');
	}

	$post_info = $post_info[$post_id];
	$url = append_sid("{$phpbb_root_path}mcp.$phpEx?" . phpbb_extra_url());

	switch ($action)
	{
		case 'whois':

			if ($auth->acl_get('m_info', $post_info['forum_id']))
			{
				$ip = $request->variable('ip', '');
				if (!function_exists('user_ipwhois'))
				{
					include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
				}

				$template->assign_vars(array(
					'RETURN_POST'	=> sprintf($user->lang['RETURN_POST'], '<a href="' . append_sid("{$phpbb_root_path}mcp.$phpEx", "i=$id&amp;mode=$mode&amp;p=$post_id") . '">', '</a>'),
					'U_RETURN_POST'	=> append_sid("{$phpbb_root_path}mcp.$phpEx", "i=$id&amp;mode=$mode&amp;p=$post_id"),
					'L_RETURN_POST'	=> sprintf($user->lang['RETURN_POST'], '', ''),
					'WHOIS'			=> user_ipwhois($ip),
				));
			}

			// We're done with the whois page so return
			return;

		break;

		case 'chgposter':
		case 'chgposter_ip':

			if ($action == 'chgposter')
			{
				$username = $request->variable('username', '', true);
				$sql_where = "username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
			}
			else
			{
				$new_user_id = $request->variable('u', 0);
				$sql_where = 'user_id = ' . $new_user_id;
			}

			$sql = 'SELECT *
				FROM ' . USERS_TABLE . '
				WHERE ' . $sql_where;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$row)
			{
				trigger_error('NO_USER');
			}

			if ($auth->acl_get('m_chgposter', $post_info['forum_id']))
			{
				if (check_form_key('mcp_post_details'))
				{
					change_poster($post_info, $row);
				}
				else
				{
					trigger_error('FORM_INVALID');
				}
			}

		break;

		default:

			/**
			* This event allows you to handle custom post moderation options
			*
			* @event core.mcp_post_additional_options
			* @var	string	action		Post moderation action name
			* @var	array	post_info	Information on the affected post
			* @since 3.1.5-RC1
			*/
			$vars = array('action', 'post_info');
			extract($phpbb_dispatcher->trigger_event('core.mcp_post_additional_options', compact($vars)));

		break;
	}

	// Set some vars
	$users_ary = $usernames_ary = array();
	$attachments = $extensions = array();
	$post_id = $post_info['post_id'];

	// Get topic tracking info
	if ($config['load_db_lastread'])
	{
		$tmp_topic_data = array($post_info['topic_id'] => $post_info);
		$topic_tracking_info = get_topic_tracking($post_info['forum_id'], $post_info['topic_id'], $tmp_topic_data, array($post_info['forum_id'] => $post_info['forum_mark_time']));
		unset($tmp_topic_data);
	}
	else
	{
		$topic_tracking_info = get_complete_topic_tracking($post_info['forum_id'], $post_info['topic_id']);
	}

	$post_unread = (isset($topic_tracking_info[$post_info['topic_id']]) && $post_info['post_time'] > $topic_tracking_info[$post_info['topic_id']]) ? true : false;

	// Process message, leave it uncensored
	$parse_flags = ($post_info['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
	$message = generate_text_for_display($post_info['post_text'], $post_info['bbcode_uid'], $post_info['bbcode_bitfield'], $parse_flags, false);

	if ($post_info['post_attachment'] && $auth->acl_get('u_download') && $auth->acl_get('f_download', $post_info['forum_id']))
	{
		$sql = 'SELECT *
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE post_msg_id = ' . $post_id . '
				AND in_message = 0
			ORDER BY filetime DESC, post_msg_id ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$attachments[] = $row;
		}
		$db->sql_freeresult($result);

		if (count($attachments))
		{
			$user->add_lang('viewtopic');
			$update_count = array();
			parse_attachments($post_info['forum_id'], $message, $attachments, $update_count);
		}

		// Display not already displayed Attachments for this post, we already parsed them. ;)
		if (!empty($attachments))
		{
			$template->assign_var('S_HAS_ATTACHMENTS', true);

			foreach ($attachments as $attachment)
			{
				$template->assign_block_vars('attachment', array(
					'DISPLAY_ATTACHMENT'	=> $attachment)
				);
			}
		}
	}

	// Deleting information
	if ($post_info['post_visibility'] == ITEM_DELETED && $post_info['post_delete_user'])
	{
		// User having deleted the post also being the post author?
		if (!$post_info['post_delete_user'] || $post_info['post_delete_user'] == $post_info['poster_id'])
		{
			$display_username = get_username_string('full', $post_info['poster_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']);
		}
		else
		{
			$sql = 'SELECT user_id, username, user_colour
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $post_info['post_delete_user'];
			$result = $db->sql_query($sql);
			$user_delete_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			$display_username = get_username_string('full', $post_info['post_delete_user'], $user_delete_row['username'], $user_delete_row['user_colour']);
		}

		$user->add_lang('viewtopic');
		$l_deleted_by = $user->lang('DELETED_INFORMATION', $display_username, $user->format_date($post_info['post_delete_time'], false, true));
	}
	else
	{
		$l_deleted_by = '';
	}

	// parse signature
	$parse_flags = ($post_info['user_sig_bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
	$post_info['user_sig'] = generate_text_for_display($post_info['user_sig'], $post_info['user_sig_bbcode_uid'], $post_info['user_sig_bbcode_bitfield'], $parse_flags, true);

	$mcp_post_template_data = array(
		'U_MCP_ACTION'			=> "$url&amp;i=main&amp;quickmod=1&amp;mode=post_details", // Use this for mode paramaters
		'U_POST_ACTION'			=> "$url&amp;i=$id&amp;mode=post_details", // Use this for action parameters
		'U_APPROVE_ACTION'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", "i=queue&amp;p=$post_id"),

		'S_CAN_VIEWIP'			=> $auth->acl_get('m_info', $post_info['forum_id']),
		'S_CAN_CHGPOSTER'		=> $auth->acl_get('m_chgposter', $post_info['forum_id']),
		'S_CAN_LOCK_POST'		=> $auth->acl_get('m_lock', $post_info['forum_id']),
		'S_CAN_DELETE_POST'		=> $auth->acl_get('m_delete', $post_info['forum_id']),

		'S_POST_REPORTED'		=> ($post_info['post_reported']) ? true : false,
		'S_POST_UNAPPROVED'		=> ($post_info['post_visibility'] == ITEM_UNAPPROVED || $post_info['post_visibility'] == ITEM_REAPPROVE) ? true : false,
		'S_POST_DELETED'		=> ($post_info['post_visibility'] == ITEM_DELETED) ? true : false,
		'S_POST_LOCKED'			=> ($post_info['post_edit_locked']) ? true : false,
		'S_USER_NOTES'			=> true,
		'S_CLEAR_ALLOWED'		=> ($auth->acl_get('a_clearlogs')) ? true : false,
		'DELETED_MESSAGE'		=> $l_deleted_by,
		'DELETE_REASON'			=> $post_info['post_delete_reason'],

		'U_EDIT'				=> ($auth->acl_get('m_edit', $post_info['forum_id'])) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=edit&amp;p={$post_info['post_id']}") : '',
		'U_FIND_USERNAME'		=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=mcp_chgposter&amp;field=username&amp;select_single=true'),
		'U_MCP_APPROVE'			=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=approve_details&amp;p=' . $post_id),
		'U_MCP_REPORT'			=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=reports&amp;mode=report_details&amp;p=' . $post_id),
		'U_MCP_USER_NOTES'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $post_info['user_id']),
		'U_MCP_WARN_USER'		=> ($auth->acl_get('m_warn')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=warn&amp;mode=warn_user&amp;u=' . $post_info['user_id']) : '',
		'U_VIEW_POST'			=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $post_info['post_id'] . '#p' . $post_info['post_id']),
		'U_VIEW_TOPIC'			=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . $post_info['topic_id']),

		'MINI_POST_IMG'			=> ($post_unread) ? $user->img('icon_post_target_unread', 'UNREAD_POST') : $user->img('icon_post_target', 'POST'),

		'RETURN_TOPIC'			=> sprintf($user->lang['RETURN_TOPIC'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", "p=$post_id") . "#p$post_id\">", '</a>'),
		'RETURN_FORUM'			=> sprintf($user->lang['RETURN_FORUM'], '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", "f={$post_info['forum_id']}&amp;start={$start}") . '">', '</a>'),
		'REPORTED_IMG'			=> $user->img('icon_topic_reported', $user->lang['POST_REPORTED']),
		'UNAPPROVED_IMG'		=> $user->img('icon_topic_unapproved', $user->lang['POST_UNAPPROVED']),
		'DELETED_IMG'			=> $user->img('icon_topic_deleted', $user->lang['POST_DELETED']),
		'EDIT_IMG'				=> $user->img('icon_post_edit', $user->lang['EDIT_POST']),
		'SEARCH_IMG'			=> $user->img('icon_user_search', $user->lang['SEARCH']),

		'POST_AUTHOR_FULL'		=> get_username_string('full', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
		'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
		'POST_AUTHOR'			=> get_username_string('username', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
		'U_POST_AUTHOR'			=> get_username_string('profile', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),

		'POST_PREVIEW'			=> $message,
		'POST_SUBJECT'			=> $post_info['post_subject'],
		'POST_DATE'				=> $user->format_date($post_info['post_time']),
		'POST_IP'				=> $post_info['poster_ip'],
		'POST_IPADDR'			=> ($auth->acl_get('m_info', $post_info['forum_id']) && $request->variable('lookup', '')) ? @gethostbyaddr($post_info['poster_ip']) : '',
		'POST_ID'				=> $post_info['post_id'],
		'SIGNATURE'				=> $post_info['user_sig'],

		'U_LOOKUP_IP'			=> ($auth->acl_get('m_info', $post_info['forum_id'])) ? "$url&amp;i=$id&amp;mode=$mode&amp;lookup={$post_info['poster_ip']}#ip" : '',
		'U_WHOIS'				=> ($auth->acl_get('m_info', $post_info['forum_id'])) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=$id&amp;mode=$mode&amp;action=whois&amp;p=$post_id&amp;ip={$post_info['poster_ip']}") : '',
	);

	$s_additional_opts = false;

	/**
	* Event to add/modify MCP post template data
	*
	* @event core.mcp_post_template_data
	* @var	array	post_info					Array with the post information
	* @var	array	mcp_post_template_data		Array with the MCP post template data
	* @var	array	attachments					Array with the post attachments, if any
	* @var	bool	s_additional_opts			Must be set to true in extension if additional options are presented in MCP post panel
	* @since 3.1.5-RC1
	*/
	$vars = array(
		'post_info',
		'mcp_post_template_data',
		'attachments',
		's_additional_opts',
	);
	extract($phpbb_dispatcher->trigger_event('core.mcp_post_template_data', compact($vars)));

	$template->assign_vars($mcp_post_template_data);
	$template->assign_var('S_MCP_POST_ADDITIONAL_OPTS', $s_additional_opts);

	unset($mcp_post_template_data);

	// Get User Notes
	$log_data = array();
	$log_count = false;
	view_log('user', $log_data, $log_count, $config['posts_per_page'], 0, 0, 0, $post_info['user_id']);

	if (!empty($log_data))
	{
		$template->assign_var('S_USER_NOTES', true);

		foreach ($log_data as $row)
		{
			$template->assign_block_vars('usernotes', array(
				'REPORT_BY'		=> $row['username_full'],
				'REPORT_AT'		=> $user->format_date($row['time']),
				'ACTION'		=> $row['action'],
				'ID'			=> $row['id'])
			);
		}
	}

	// Get Reports
	if ($auth->acl_get('m_report', $post_info['forum_id']))
	{
		$sql = 'SELECT r.*, re.*, u.user_id, u.username
			FROM ' . REPORTS_TABLE . ' r, ' . USERS_TABLE . ' u, ' . REPORTS_REASONS_TABLE . " re
			WHERE r.post_id = $post_id
				AND r.reason_id = re.reason_id
				AND u.user_id = r.user_id
			ORDER BY r.report_time DESC";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$template->assign_var('S_SHOW_REPORTS', true);

			do
			{
				// If the reason is defined within the language file, we will use the localized version, else just use the database entry...
				if (isset($user->lang['report_reasons']['TITLE'][strtoupper($row['reason_title'])]) && isset($user->lang['report_reasons']['DESCRIPTION'][strtoupper($row['reason_title'])]))
				{
					$row['reson_description'] = $user->lang['report_reasons']['DESCRIPTION'][strtoupper($row['reason_title'])];
					$row['reason_title'] = $user->lang['report_reasons']['TITLE'][strtoupper($row['reason_title'])];
				}

				$template->assign_block_vars('reports', array(
					'REPORT_ID'		=> $row['report_id'],
					'REASON_TITLE'	=> $row['reason_title'],
					'REASON_DESC'	=> $row['reason_description'],
					'REPORTER'		=> get_username_string('username', $row['user_id'], $row['username']),
					'U_REPORTER'	=> get_username_string('profile', $row['user_id'], $row['username']),
					'USER_NOTIFY'	=> ($row['user_notify']) ? true : false,
					'REPORT_TIME'	=> $user->format_date($row['report_time']),
					'REPORT_TEXT'	=> bbcode_nl2br(trim($row['report_text'])),
				));
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);
	}

	// Get IP
	if ($auth->acl_get('m_info', $post_info['forum_id']))
	{
		/** @var \phpbb\pagination $pagination */
		$pagination = $phpbb_container->get('pagination');

		$start_users = $request->variable('start_users', 0);
		$rdns_ip_num = $request->variable('rdns', '');
		$lookup_all = $rdns_ip_num === 'all';

		$base_url = $url . '&amp;i=main&amp;mode=post_details';
		$base_url .= $lookup_all ? '&amp;rdns=all' : '';

		if (!$lookup_all)
		{
			$template->assign_var('U_LOOKUP_ALL', $base_url . '&amp;rdns=all');
		}

		$num_users = false;
		if ($start_users)
		{
			$num_users = phpbb_get_num_posters_for_ip($db, $post_info['poster_ip']);
			$start_users = $pagination->validate_start($start_users, $config['posts_per_page'], $num_users);
		}

		// Get other users who've posted under this IP
		$sql = 'SELECT poster_id, COUNT(poster_id) as postings
			FROM ' . POSTS_TABLE . "
			WHERE poster_ip = '" . $db->sql_escape($post_info['poster_ip']) . "'
				AND poster_id <> " . (int) $post_info['poster_id'] . "
			GROUP BY poster_id
			ORDER BY postings DESC, poster_id ASC";
		$result = $db->sql_query_limit($sql, $config['posts_per_page'], $start_users);

		$page_users = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			$page_users++;
			$users_ary[$row['poster_id']] = $row;
		}
		$db->sql_freeresult($result);

		if ($page_users == $config['posts_per_page'] || $start_users)
		{
			if ($num_users === false)
			{
				$num_users = phpbb_get_num_posters_for_ip($db, $post_info['poster_ip']);
			}

			$pagination->generate_template_pagination(
				$base_url,
				'pagination',
				'start_users',
				$num_users,
				$config['posts_per_page'],
				$start_users
			);
		}

		if (count($users_ary))
		{
			// Get the usernames
			$sql = 'SELECT user_id, username
				FROM ' . USERS_TABLE . '
				WHERE ' . $db->sql_in_set('user_id', array_keys($users_ary));
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$users_ary[$row['user_id']]['username'] = $row['username'];
				$usernames_ary[utf8_clean_string($row['username'])] = $users_ary[$row['user_id']];
			}
			$db->sql_freeresult($result);

			foreach ($users_ary as $user_id => $user_row)
			{
				$template->assign_block_vars('userrow', array(
					'USERNAME'		=> get_username_string('username', $user_id, $user_row['username']),
					'NUM_POSTS'		=> $user_row['postings'],
					'L_POST_S'		=> ($user_row['postings'] == 1) ? $user->lang['POST'] : $user->lang['POSTS'],

					'U_PROFILE'		=> get_username_string('profile', $user_id, $user_row['username']),
					'U_SEARCHPOSTS' => append_sid("{$phpbb_root_path}search.$phpEx", 'author_id=' . $user_id . '&amp;sr=topics'))
				);
			}
		}

		// Get other IP's this user has posted under

		// A compound index on poster_id, poster_ip (posts table) would help speed up this query a lot,
		// but the extra size is only valuable if there are persons having more than a thousands posts.
		// This is better left to the really really big forums.
		$start_ips = $request->variable('start_ips', 0);

		$num_ips = false;
		if ($start_ips)
		{
			$num_ips = phpbb_get_num_ips_for_poster($db, $post_info['poster_id']);
			$start_ips = $pagination->validate_start($start_ips, $config['posts_per_page'], $num_ips);
		}

		$sql = 'SELECT poster_ip, COUNT(poster_ip) AS postings
			FROM ' . POSTS_TABLE . '
			WHERE poster_id = ' . $post_info['poster_id'] . "
			GROUP BY poster_ip
			ORDER BY postings DESC, poster_ip ASC";
		$result = $db->sql_query_limit($sql, $config['posts_per_page'], $start_ips);

		$page_ips = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			$page_ips++;
			$hostname = (($rdns_ip_num == $row['poster_ip'] || $rdns_ip_num == 'all') && $row['poster_ip']) ? @gethostbyaddr($row['poster_ip']) : '';

			$template->assign_block_vars('iprow', array(
				'IP'			=> $row['poster_ip'],
				'HOSTNAME'		=> $hostname,
				'NUM_POSTS'		=> $row['postings'],
				'L_POST_S'		=> ($row['postings'] == 1) ? $user->lang['POST'] : $user->lang['POSTS'],

				'U_LOOKUP_IP'	=> (!$lookup_all && $rdns_ip_num != $row['poster_ip']) ? "$base_url&amp;start_ips={$start_ips}&amp;rdns={$row['poster_ip']}#ip" : '',
				'U_WHOIS'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", "i=$id&amp;mode=$mode&amp;action=whois&amp;p=$post_id&amp;ip={$row['poster_ip']}"))
			);
		}
		$db->sql_freeresult($result);

		if ($page_ips == $config['posts_per_page'] || $start_ips)
		{
			if ($num_ips === false)
			{
				$num_ips = phpbb_get_num_ips_for_poster($db, $post_info['poster_id']);
			}

			$pagination->generate_template_pagination(
				$base_url,
				'pagination_ips',
				'start_ips',
				$num_ips,
				$config['posts_per_page'],
				$start_ips
			);
		}

		$user_select = '';

		if (count($usernames_ary))
		{
			ksort($usernames_ary);

			foreach ($usernames_ary as $row)
			{
				$user_select .= '<option value="' . $row['poster_id'] . '">' . $row['username'] . "</option>\n";
			}
		}

		$template->assign_var('S_USER_SELECT', $user_select);
	}

}

/**
 * Get the number of posters for a given ip
 *
 * @param \phpbb\db\driver\driver_interface $db DBAL interface
 * @param string $poster_ip IP
 * @return int Number of posters
 */
function phpbb_get_num_posters_for_ip(\phpbb\db\driver\driver_interface $db, $poster_ip)
{
	$sql = 'SELECT COUNT(DISTINCT poster_id) as num_users
		FROM ' . POSTS_TABLE . "
		WHERE poster_ip = '" . $db->sql_escape($poster_ip) . "'";
	$result = $db->sql_query($sql);
	$num_users = (int) $db->sql_fetchfield('num_users');
	$db->sql_freeresult($result);

	return $num_users;
}

/**
 * Get the number of ips for a given poster
 *
 * @param \phpbb\db\driver\driver_interface $db
 * @param int $poster_id Poster user ID
 * @return int Number of IPs for given poster
 */
function phpbb_get_num_ips_for_poster(\phpbb\db\driver\driver_interface $db, $poster_id)
{
	$sql = 'SELECT COUNT(DISTINCT poster_ip) as num_ips
		FROM ' . POSTS_TABLE . '
		WHERE poster_id = ' . (int) $poster_id;
	$result = $db->sql_query($sql);
	$num_ips = (int) $db->sql_fetchfield('num_ips');
	$db->sql_freeresult($result);

	return $num_ips;
}

/**
* Change a post's poster
*/
function change_poster(&$post_info, $userdata)
{
	global $auth, $db, $config, $phpbb_root_path, $phpEx, $user, $phpbb_log, $phpbb_dispatcher;

	if (empty($userdata) || $userdata['user_id'] == $post_info['user_id'])
	{
		return;
	}

	$post_id = $post_info['post_id'];

	$sql = 'UPDATE ' . POSTS_TABLE . "
		SET poster_id = {$userdata['user_id']}
		WHERE post_id = $post_id";
	$db->sql_query($sql);

	// Resync topic/forum if needed
	if ($post_info['topic_last_post_id'] == $post_id || $post_info['forum_last_post_id'] == $post_id || $post_info['topic_first_post_id'] == $post_id)
	{
		sync('topic', 'topic_id', $post_info['topic_id'], false, false);
		sync('forum', 'forum_id', $post_info['forum_id'], false, false);
	}

	// Adjust post counts... only if the post is approved (else, it was not added the users post count anyway)
	if ($post_info['post_postcount'] && $post_info['post_visibility'] == ITEM_APPROVED)
	{
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_posts = user_posts - 1
			WHERE user_id = ' . $post_info['user_id'] .'
			AND user_posts > 0';
		$db->sql_query($sql);

		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_posts = user_posts + 1
			WHERE user_id = ' . $userdata['user_id'];
		$db->sql_query($sql);
	}

	// Add posted to information for this topic for the new user
	markread('post', $post_info['forum_id'], $post_info['topic_id'], time(), $userdata['user_id']);

	// Remove the dotted topic option if the old user has no more posts within this topic
	if ($config['load_db_track'] && $post_info['user_id'] != ANONYMOUS)
	{
		$sql = 'SELECT topic_id
			FROM ' . POSTS_TABLE . '
			WHERE topic_id = ' . $post_info['topic_id'] . '
				AND poster_id = ' . $post_info['user_id'];
		$result = $db->sql_query_limit($sql, 1);
		$topic_id = (int) $db->sql_fetchfield('topic_id');
		$db->sql_freeresult($result);

		if (!$topic_id)
		{
			$sql = 'DELETE FROM ' . TOPICS_POSTED_TABLE . '
				WHERE user_id = ' . $post_info['user_id'] . '
					AND topic_id = ' . $post_info['topic_id'];
			$db->sql_query($sql);
		}
	}

	// change the poster_id within the attachments table, else the data becomes out of sync and errors displayed because of wrong ownership
	if ($post_info['post_attachment'])
	{
		$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
			SET poster_id = ' . $userdata['user_id'] . '
			WHERE poster_id = ' . $post_info['user_id'] . '
				AND post_msg_id = ' . $post_info['post_id'] . '
				AND topic_id = ' . $post_info['topic_id'];
		$db->sql_query($sql);
	}

	// refresh search cache of this post
	$search_type = $config['search_type'];

	if (class_exists($search_type))
	{
		// We do some additional checks in the module to ensure it can actually be utilised
		$error = false;
		$search = new $search_type($error, $phpbb_root_path, $phpEx, $auth, $config, $db, $user, $phpbb_dispatcher);

		if (!$error && method_exists($search, 'destroy_cache'))
		{
			$search->destroy_cache(array(), array($post_info['user_id'], $userdata['user_id']));
		}
	}

	$from_username = $post_info['username'];
	$to_username = $userdata['username'];

	/**
	* This event allows you to perform additional tasks after changing a post's poster
	*
	* @event core.mcp_change_poster_after
	* @var	array	userdata	Information on a post's new poster
	* @var	array	post_info	Information on the affected post
	* @since 3.1.6-RC1
	* @changed 3.1.7-RC1		Change location to prevent post_info from being set to the new post information
	*/
	$vars = array('userdata', 'post_info');
	extract($phpbb_dispatcher->trigger_event('core.mcp_change_poster_after', compact($vars)));

	// Renew post info
	$post_info = phpbb_get_post_data(array($post_id), false, true);

	if (!count($post_info))
	{
		trigger_error('POST_NOT_EXIST');
	}

	$post_info = $post_info[$post_id];

	// Now add log entry
	$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_MCP_CHANGE_POSTER', false, array(
		'forum_id' => $post_info['forum_id'],
		'topic_id' => $post_info['topic_id'],
		'post_id'  => $post_info['post_id'],
		$post_info['topic_title'],
		$from_username,
		$to_username
	));
}
