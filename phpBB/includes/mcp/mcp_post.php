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
	global $SID, $phpEx, $phpbb_root_path, $config;
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
	$url = "{$phpbb_root_path}mcp.$phpEx$SID" . extra_url();

	switch ($action)
	{
		case 'chgposter':

			$username = request_var('username', '');

			$sql = 'SELECT user_id
					FROM ' . USERS_TABLE . '
					WHERE username = \'' . $db->sql_escape($username) . '\'';
			$result = $db->sql_query($sql);

			if (!($row = $db->sql_fetchrow($result)))
			{
				trigger_error($user->lang['NO_USER']);
			}
			$new_user = $row['user_id'];

			if ($auth->acl_get('m_', $post_info['forum_id']))
			{
				change_poster($post_info, $new_user);
			}
		break;

		case 'chgposter_ip':

			$new_user = request_var('u', 0);

			$sql = 'SELECT user_id
					FROM ' . USERS_TABLE . '
					WHERE user_id = ' . $new_user;
			$result = $db->sql_query($sql);

			if (!($row = $db->sql_fetchrow($result)))
			{
				trigger_error($user->lang['NO_USER']);
			}

			if ($auth->acl_get('m_', $post_info['forum_id']))
			{
				change_poster($post_info, $new_user);
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
		include_once($phpbb_root_path . 'includes/bbcode.'.$phpEx);
		$bbcode = new bbcode($post_info['bbcode_bitfield']);
		$bbcode->bbcode_second_pass($message, $post_info['bbcode_uid'], $post_info['bbcode_bitfield']);
	}
	$message = smiley_text($message);

	$template->assign_vars(array(
		'U_MCP_ACTION'			=> "$url&amp;i=main&amp;quickmod=1", // Use this for mode paramaters
		'U_POST_ACTION'			=> "$url&amp;i=$id&amp;mode=post_details", // Use this for action parameters
		'U_APPROVE_ACTION'		=> "{$phpbb_root_path}mcp.$phpEx$SID&amp;i=queue&amp;p=$post_id",

		'S_CAN_VIEWIP'			=> $auth->acl_get('m_ip', $post_info['forum_id']),
		'S_CAN_CHGPOSTER'		=> $auth->acl_get('m_', $post_info['forum_id']),
		'S_CAN_LOCK_POST'		=> $auth->acl_get('m_lock', $post_info['forum_id']),
		'S_CAN_DELETE_POST'		=> $auth->acl_get('m_delete', $post_info['forum_id']),

		'S_POST_REPORTED'		=> $post_info['post_reported'],
		'S_POST_UNAPPROVED'		=> !$post_info['post_approved'],
		'S_POST_LOCKED'			=> $post_info['post_edit_locked'],
		'S_USER_NOTES'			=> $auth->acl_gets('m_', 'a_') ? true : false,
		'S_CLEAR_ALLOWED'		=> ($auth->acl_get('a_clearlogs')) ? true : false,

		'U_FIND_MEMBER'			=> "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=searchuser&amp;form=mcp_chgposter&amp;field=username",
		'U_VIEW_PROFILE'		=> "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $post_info['user_id'],
		'U_MCP_USER_NOTES'			=> $auth->acl_gets('m_', 'a_') ? "{$phpbb_root_path}mcp.$phpEx$SID&amp;i=notes&amp;mode=user_notes&amp;u=" . $post_info['user_id'] : '',
		'U_MCP_WARN_USER'		=> "{$phpbb_root_path}mcp.$phpEx$SID&amp;i=warn&amp;mode=warn_user&amp;u=" . $post_info['user_id'],
		'U_EDIT'				=> ($auth->acl_get('m_edit', $post_info['forum_id'])) ? "{$phpbb_root_path}posting.$phpEx$SID&amp;mode=edit&amp;f={$post_info['forum_id']}&amp;p={$post_info['post_id']}" : '',

		'RETURN_TOPIC'			=> sprintf($user->lang['RETURN_TOPIC'], "<a href=\"{$phpbb_root_path}viewtopic.$phpEx$SID&amp;p=$post_id#$post_id\">", '</a>'),
		'RETURN_FORUM'			=> sprintf($user->lang['RETURN_FORUM'], "<a href=\"{$phpbb_root_path}viewforum.$phpEx$SID&amp;f={$post_info['forum_id']}&amp;start={$start}\">", '</a>'),
		'REPORTED_IMG'			=> $user->img('icon_reported', $user->lang['POST_REPORTED']),
		'UNAPPROVED_IMG'		=> $user->img('icon_unapproved', $user->lang['POST_UNAPPROVED']),
		'EDIT_IMG'				=> $user->img('btn_edit', $user->lang['EDIT_POST']),

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
			FROM ' . REPORTS_TABLE . ' r, ' . USERS_TABLE . ' u, ' . REASONS_TABLE . " re
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
				$template->assign_block_vars('reports', array(
					'REPORT_ID'		=> $row['report_id'],
					'REASON_TITLE'	=> $user->lang['report_reasons']['TITLE'][strtoupper($row['reason_name'])],
					'REASON_DESC'	=> $user->lang['report_reasons']['DESCRIPTION'][strtoupper($row['reason_name'])],
					'REPORTER'		=> ($row['user_id'] != ANONYMOUS) ? $row['username'] : $user->lang['GUEST'],
					'U_REPORTER'	=> ($row['user_id'] != ANONYMOUS) ? "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u={$row['user_id']}" : '',
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
	if ($auth->acl_get('m_ip', $post_info['forum_id']))
	{
		$rdns_ip_num = request_var('rdns', '');

		if ($rdns_ip_num != 'all')
		{
			$template->assign_vars(array(
				'U_LOOKUP_ALL'	=> "$url&amp;i=main&amp;mode=post_details&amp;rdns=all")
			);
		}

		// Get other users who've posted under this IP
		$sql = 'SELECT u.user_id, u.username, COUNT(*) as postings
			FROM ' . USERS_TABLE . ' u, ' . POSTS_TABLE . " p
			WHERE p.poster_id = u.user_id
				AND p.poster_ip = '{$post_info['poster_ip']}'
				AND p.poster_id <> {$post_info['user_id']}
			GROUP BY u.user_id
			ORDER BY postings DESC";
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

				'U_PROFILE'		=> ($row['user_id'] == ANONYMOUS) ? '' : "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id'],
				'U_SEARCHPOSTS' => "{$phpbb_root_path}search.$phpEx$SID&amp;author=" . urlencode($row['username']) . "&amp;sr=topics")
			);
		}
		$db->sql_freeresult($result);

		// Get other IP's this user has posted under
		$sql = 'SELECT poster_ip, COUNT(*) AS postings
			FROM ' . POSTS_TABLE . '
			WHERE poster_id = ' . $post_info['poster_id'] . '
			GROUP BY poster_ip
			ORDER BY postings DESC';
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
				'U_WHOIS'		=> "{$phpbb_root_path}mcp.$phpEx$SID&amp;i=$id&amp;mode=whois&amp;ip={$row['poster_ip']}")
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
* Changes a post's poster_id
*/
function change_poster(&$post_info, $new_user)
{
	global $auth, $db;

	if ($new_user && $new_user != $post_info['user_id'])
	{
		$post_id = $post_info['post_id'];

		$sql = 'UPDATE ' . POSTS_TABLE . "
			SET poster_id = $new_user
			WHERE post_id = " . $post_id;
		$db->sql_query($sql);

		if ($post_info['topic_last_post_id'] == $post_id || $post_info['forum_last_post_id'] == $post_id)
		{
			sync('topic', 'topic_id', $post_info['topic_id'], false, false);
			sync('forum', 'forum_id', $post_info['forum_id'], false, false);
		}

		// Renew post info
		$post_info = get_post_data(array($post_id));

		if (!sizeof($post_info))
		{
			trigger_error($user->lang['POST_NOT_EXIST']);
		}

		$post_info = $post_info[$post_id];
	}
}

?>