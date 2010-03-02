<?php
/** 
*
* @package mcp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* Handling actions in post details screen
*/
function mcp_post_details($id, $mode, $action)
{
	global $phpEx, $phpbb_root_path, $config;
	global $template, $db, $user, $auth;

	$user->add_lang('posting');

	$post_id = request_var('p', 0);
	$start	= request_var('start', 0);

	// Get post data
	$post_info = get_post_data(array($post_id));

	if (!sizeof($post_info))
	{
		trigger_error($user->lang['POST_NOT_EXIST']);
	}

	$post_info = $post_info[$post_id];
	$url = append_sid("{$phpbb_root_path}mcp.$phpEx?" . extra_url());

	switch ($action)
	{
		case 'whois':

			$ip = request_var('ip', '');
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
			
			$whois = user_ipwhois($ip);
			
			$whois = preg_replace('#(\s)([\w\-\._\+]+@[\w\-\.]+)(\s)#', '\1<a href="mailto:\2">\2</a>\3', $whois);
			$whois = preg_replace('#(\s)(http:/{2}[^\s]*)(\s)#', '\1<a href="\2" target="_blank">\2</a>\3', $whois);
			
			$template->assign_vars(array(
				'RETURN_POST'	=> sprintf($user->lang['RETURN_POST'], '<a href="' . append_sid("{$phpbb_root_path}mcp.$phpEx", "i=$id&amp;mode=$mode&amp;p=$post_id") . '">', '</a>'),
				'WHOIS'			=> trim($whois))
			);

			// We're done with the whois page so return
			return;

		break;

		case 'chgposter':
		case 'chgposter_ip':

			if ($action == 'chgposter')
			{
				$username = request_var('username', '');
				$sql_where = "username = '" . $db->sql_escape($username) . "'";
			}
			else
			{
				$new_user_id = request_var('u', 0);
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
				trigger_error($user->lang['NO_USER']);
			}

			if ($auth->acl_get('m_chgposter', $post_info['forum_id']))
			{
				change_poster($post_info, $row);
			}

		break;
	}

	// Set some vars
	$users_ary = array();
	$post_id = $post_info['post_id'];
	$poster = ($post_info['user_colour']) ? '<span style="color:#' . $post_info['user_colour'] . '">' . $post_info['username'] . '</span>' : $post_info['username'];

	// Process message, leave it uncensored
	$message = $post_info['post_text'];
	if ($post_info['bbcode_bitfield'])
	{
		include_once($phpbb_root_path . 'includes/bbcode.' . $phpEx);
		$bbcode = new bbcode($post_info['bbcode_bitfield']);
		$bbcode->bbcode_second_pass($message, $post_info['bbcode_uid'], $post_info['bbcode_bitfield']);
	}
	$message = smiley_text($message);
	$message = str_replace("\n", '<br />', $message);

	$template->assign_vars(array(
		'U_MCP_ACTION'			=> "$url&amp;i=main&amp;quickmod=1", // Use this for mode paramaters
		'U_POST_ACTION'			=> "$url&amp;i=$id&amp;mode=post_details", // Use this for action parameters

		'S_CAN_VIEWIP'			=> $auth->acl_get('m_info', $post_info['forum_id']),
		'S_CAN_CHGPOSTER'		=> $auth->acl_get('m_chgposter', $post_info['forum_id']),
		'S_CAN_LOCK_POST'		=> $auth->acl_get('m_lock', $post_info['forum_id']),
		'S_CAN_DELETE_POST'		=> $auth->acl_get('m_delete', $post_info['forum_id']),

		'S_POST_REPORTED'		=> ($post_info['post_reported']) ? true : false,
		'S_POST_UNAPPROVED'		=> (!$post_info['post_approved']) ? true : false,
		'S_POST_LOCKED'			=> ($post_info['post_edit_locked']) ? true : false,
		'S_USER_NOTES'			=> true,
		'S_CLEAR_ALLOWED'		=> ($auth->acl_get('a_clearlogs')) ? true : false,

		'U_EDIT'				=> ($auth->acl_get('m_edit', $post_info['forum_id'])) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=edit&amp;f={$post_info['forum_id']}&amp;p={$post_info['post_id']}") : '',
		'U_FIND_MEMBER'			=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=mcp_chgposter&amp;field=username'),
		'U_MCP_APPROVE'			=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=approve_details&amp;f=' . $post_info['forum_id'] . '&amp;p=' . $post_id),
		'U_MCP_REPORT'			=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=reports&amp;mode=report_details&amp;f=' . $post_info['forum_id'] . '&amp;p=' . $post_id),
		'U_MCP_USER_NOTES'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $post_info['user_id']),
		'U_MCP_WARN_USER'		=> ($auth->acl_getf_global('m_warn')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=warn&amp;mode=warn_user&amp;u=' . $post_info['user_id']) : '',
		'U_VIEW_POST'			=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $post_info['forum_id'] . '&amp;p=' . $post_info['post_id'] . '#p' . $post_info['post_id']),
		'U_VIEW_PROFILE'		=> ($post_info['user_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $post_info['user_id']) : '',
		'U_VIEW_TOPIC'			=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $post_info['forum_id'] . '&amp;t=' . $post_info['topic_id']),
		
		'RETURN_TOPIC'			=> sprintf($user->lang['RETURN_TOPIC'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f={$post_info['forum_id']}&amp;p=$post_id") . "#p$post_id\">", '</a>'),
		'RETURN_FORUM'			=> sprintf($user->lang['RETURN_FORUM'], '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", "f={$post_info['forum_id']}&amp;start={$start}") . '">', '</a>'),
		'REPORTED_IMG'			=> $user->img('icon_topic_reported', $user->lang['POST_REPORTED']),
		'UNAPPROVED_IMG'		=> $user->img('icon_topic_unapproved', $user->lang['POST_UNAPPROVED']),
		'EDIT_IMG'				=> $user->img('icon_post_edit', $user->lang['EDIT_POST']),

		'POSTER_NAME'			=> $poster,
		'POST_PREVIEW'			=> $message,
		'POST_SUBJECT'			=> $post_info['post_subject'],
		'POST_DATE'				=> $user->format_date($post_info['post_time']),
		'POST_IP'				=> $post_info['poster_ip'],
		'POST_IPADDR'			=> @gethostbyaddr($post_info['poster_ip']),
		'POST_ID'				=> $post_info['post_id'])
	);

	// Get User Notes
	$log_data = array();
	$log_count = 0;
	view_log('user', $log_data, $log_count, $config['posts_per_page'], 0, 0, 0, $post_info['user_id']);

	if ($log_count)
	{
		$template->assign_var('S_USER_NOTES', true);

		foreach ($log_data as $row)
		{
			$template->assign_block_vars('usernotes', array(
				'REPORT_BY'		=> $row['username'],
				'REPORT_AT'		=> $user->format_date($row['time']),
				'ACTION'		=> $row['action'],
				'ID'			=> $row['id'])
			);
		}
	}

	// Get Reports
	if ($auth->acl_get('m_', $post_info['forum_id']))
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
					'REPORTER'		=> ($row['user_id'] != ANONYMOUS) ? $row['username'] : $user->lang['GUEST'],
					'U_REPORTER'	=> ($row['user_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $row['user_id']) : '',
					'USER_NOTIFY'	=> ($row['user_notify']) ? true : false,
					'REPORT_TIME'	=> $user->format_date($row['report_time']),
					'REPORT_TEXT'	=> str_replace("\n", '<br />', trim($row['report_text'])))
				);
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);
	}

	// Get IP
	if ($auth->acl_get('m_info', $post_info['forum_id']))
	{
		$rdns_ip_num = request_var('rdns', '');

		if ($rdns_ip_num != 'all')
		{
			$template->assign_vars(array(
				'U_LOOKUP_ALL'	=> "$url&amp;i=main&amp;mode=post_details&amp;rdns=all")
			);
		}

		// Get other users who've posted under this IP

		// Firebird does not support ORDER BY on aliased columns
		// MySQL does not support ORDER BY on functions
		switch (SQL_LAYER)
		{
			case 'firebird':
				$sql = 'SELECT u.user_id, u.username, COUNT(*) as postings
					FROM ' . USERS_TABLE . ' u, ' . POSTS_TABLE . " p
					WHERE p.poster_id = u.user_id
						AND p.poster_ip = '" . $db->sql_escape($post_info['poster_ip']) . "'
						AND p.poster_id <> {$post_info['user_id']}
					GROUP BY u.user_id, u.username
					ORDER BY COUNT(*) DESC";
			break;

			default:
				$sql = 'SELECT u.user_id, u.username, COUNT(*) as postings
					FROM ' . USERS_TABLE . ' u, ' . POSTS_TABLE . " p
					WHERE p.poster_id = u.user_id
						AND p.poster_ip = '" . $db->sql_escape($post_info['poster_ip']) . "'
						AND p.poster_id <> {$post_info['user_id']}
					GROUP BY u.user_id, u.username
					ORDER BY postings DESC";
			break;
		}
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			// Fill the user select list with users who have posted
			// under this IP
			if ($row['user_id'] != $post_info['poster_id'])
			{
				$users_ary[strtolower($row['username'])] = $row;
			}

			$template->assign_block_vars('userrow', array(
				'USERNAME'		=> ($row['user_id'] == ANONYMOUS) ? $user->lang['GUEST'] : $row['username'],
				'NUM_POSTS'		=> $row['postings'],
				'L_POST_S'		=> ($row['postings'] == 1) ? $user->lang['POST'] : $user->lang['POSTS'],

				'U_PROFILE'		=> ($row['user_id'] == ANONYMOUS) ? '' : append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $row['user_id']),
				'U_SEARCHPOSTS' => append_sid("{$phpbb_root_path}search.$phpEx", 'author=' . urlencode($row['username']) . '&amp;sr=topics'))
			);
		}
		$db->sql_freeresult($result);

		// Get other IP's this user has posted under

		// Firebird does not support ORDER BY on aliased columns
		// MySQL does not support ORDER BY on functions
		switch (SQL_LAYER)
		{
			case 'firebird':
				$sql = 'SELECT poster_ip, COUNT(*) AS postings
					FROM ' . POSTS_TABLE . '
					WHERE poster_id = ' . $post_info['poster_id'] . '
					GROUP BY poster_ip
					ORDER BY COUNT(*) DESC';
			break;

			default:
				$sql = 'SELECT poster_ip, COUNT(*) AS postings
					FROM ' . POSTS_TABLE . '
					WHERE poster_id = ' . $post_info['poster_id'] . '
					GROUP BY poster_ip
					ORDER BY postings DESC';
			break;
		}
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$hostname = (($rdns_ip_num == $row['poster_ip'] || $rdns_ip_num == 'all') && $row['poster_ip']) ? @gethostbyaddr($row['poster_ip']) : '';

			$template->assign_block_vars('iprow', array(
				'IP'			=> $row['poster_ip'],
				'HOSTNAME'		=> $hostname,
				'NUM_POSTS'		=> $row['postings'],
				'L_POST_S'		=> ($row['postings'] == 1) ? $user->lang['POST'] : $user->lang['POSTS'],

				'U_LOOKUP_IP'	=> ($rdns_ip_num == $row['poster_ip'] || $rdns_ip_num == 'all') ? '' : "$url&amp;i=$id&amp;mode=post_details&amp;rdns={$row['poster_ip']}#ip",
				'U_WHOIS'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", "i=$id&amp;mode=$mode&amp;action=whois&amp;p=$post_id&amp;ip={$row['poster_ip']}"))
			);
		}
		$db->sql_freeresult($result);

		$user_select = '';
		ksort($users_ary);

		foreach ($users_ary as $row)
		{
			$user_select .= '<option value="' . $row['user_id'] . '">' . $row['username'] . "</option>\n";
		}
		$template->assign_var('S_USER_SELECT', $user_select);
	}

}

/**
* Change a post's poster
*/
function change_poster(&$post_info, $userdata)
{
	global $auth, $db, $config;

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

	// Adjust post counts
	if ($post_info['post_postcount'])
	{
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_posts = user_posts - 1
			WHERE user_id = ' . $post_info['user_id'];
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

	// Do not change the poster_id within the attachments table, since they were still posted by the original user

	$from_username = $post_info['username'];
	$to_username = $userdata['username'];

	// Renew post info
	$post_info = get_post_data(array($post_id));

	if (!sizeof($post_info))
	{
		trigger_error($user->lang['POST_NOT_EXIST']);
	}

	$post_info = $post_info[$post_id];

	// Now add log entry
	add_log('mod', $post_info['forum_id'], $post_info['topic_id'], 'LOG_MCP_CHANGE_POSTER', $post_info['topic_title'], $from_username, $to_username);
}

?>