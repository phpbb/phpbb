<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : mcp_front.php
// STARTED   : Thu Jul 08, 2004
// COPYRIGHT : © 2004 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

//
// TODO:
//	- add list of forums user is moderator in (with links to common management facilities)
//	- add statistics (how many reports handled, how many posts locked/moved... etc.? - 
//			those would be only valid from the time the log is valid (and not purged)
//

function mcp_front_view($id, $mode, $action, $url)
{
	global $SID, $phpEx, $phpbb_root_path, $config;
	global $template, $db, $user, $auth;

	// Latest 5 unapproved
	$forum_list = get_forum_list('m_approve');
	$post_list = array();
	$forum_names = array();

	$forum_id = request_var('f', 0);

	$template->assign_var('S_SHOW_UNAPPROVED', (!empty($forum_list)) ? true : false);
	if (!empty($forum_list))
	{
		$sql = 'SELECT COUNT(post_id) AS total
			FROM ' . POSTS_TABLE . '
			WHERE forum_id IN (0, ' . implode(', ', $forum_list) . ')
				AND post_approved = 0';
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$total = $row['total'];

		if ($total)
		{
			$sql = 'SELECT forum_id, forum_name
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id IN (' . implode(', ', $forum_list) . ')';
			$result = $db->sql_query_limit($sql);
			
			while ($row = $db->sql_fetchrow($result))
			{
				$forum_names[$row['forum_id']] = $row['forum_name'];
			}
			$db->sql_freeresult($result);

			$sql = 'SELECT post_id
				FROM ' . POSTS_TABLE . '
				WHERE forum_id IN (0, ' . implode(', ', $forum_list) . ')
					AND post_approved = 0
				ORDER BY post_id DESC';
			$result = $db->sql_query_limit($sql, 5);
			while ($row = $db->sql_fetchrow($result))
			{
				$post_list[] = $row['post_id'];
			}

			$sql = 'SELECT p.post_id, p.post_subject, p.post_time, p.poster_id, p.post_username, u.username, t.topic_id, t.topic_title, t.topic_first_post_id, p.forum_id
				FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t,  ' . USERS_TABLE . ' u
				WHERE p.post_id IN (' . implode(', ', $post_list) . ')
					AND t.topic_id = p.topic_id
					AND p.poster_id = u.user_id
				ORDER BY p.post_id DESC';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$template->assign_block_vars('unapproved', array(
					'U_POST_DETAILS'=> $url . '&amp;p=' . $row['post_id'] . '&amp;mode=post_details',
					'U_MCP_FORUM'	=> ($row['forum_id']) ? $url . '&amp;f=' . $row['forum_id'] . '&amp;mode=forum_view' : '',
					'U_MCP_TOPIC'	=> $url . '&amp;t=' . $row['topic_id'] . '&amp;mode=topic_view',
					'U_FORUM'		=> ($row['forum_id']) ? 'viewforum.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] : '',
					'U_TOPIC'		=> $phpbb_root_path . 'viewtopic.' . $phpEx . $SID . '&amp;f=' . (($row['forum_id']) ? $row['forum_id'] : $forum_id) . '&amp;t=' . $row['topic_id'],
					'U_AUTHOR'		=> ($row['poster_id'] == ANONYMOUS) ? '' : 'memberlist.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['poster_id'],

					'FORUM_NAME'	=> ($row['forum_id']) ? $forum_names[$row['forum_id']] : $user->lang['GLOBAL_ANNOUNCEMENT'],
					'TOPIC_TITLE'	=> $row['topic_title'],
					'AUTHOR'		=> ($row['poster_id'] == ANONYMOUS) ? (($row['post_username']) ? $row['post_username'] : $user->lang['GUEST']) : $row['username'],
					'SUBJECT'		=> ($row['post_subject']) ? $row['post_subject'] : $user->lang['NO_SUBJECT'],
					'POST_TIME'		=> $user->format_date($row['post_time']))
				);				
			}
		}

		if ($total == 0)
		{
			$template->assign_vars(array(
				'L_UNAPPROVED_TOTAL'		=> $user->lang['UNAPPROVED_POSTS_ZERO_TOTAL'],
				'S_HAS_UNAPPROVED_POSTS'	=> false)
			);
		}
		else
		{
			$template->assign_vars(array(
				'L_UNAPPROVED_TOTAL'		=> ($total == 1) ? $user->lang['UNAPPROVED_POST_TOTAL'] : sprintf($user->lang['UNAPPROVED_POSTS_TOTAL'], $total),
				'S_HAS_UNAPPROVED_POSTS'	=> true)
			);
		}
	}

	// Latest 5 reported
	$forum_list = get_forum_list('m_');
				
	$template->assign_var('S_SHOW_REPORTS', (!empty($forum_list)) ? true : false);
	if (!empty($forum_list))
	{
		$sql = 'SELECT COUNT(r.report_id) AS total
			FROM ' . REPORTS_TABLE . ' r, ' . POSTS_TABLE . ' p
			WHERE r.post_id = p.post_id
				AND p.forum_id IN (0, ' . implode(', ', $forum_list) . ')';
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$total = $row['total'];

		if ($total)
		{
			$sql = 'SELECT r.*, p.post_id, p.post_subject, u.username, t.topic_id, t.topic_title, f.forum_id, f.forum_name
				FROM ' . REPORTS_TABLE . ' r, ' . REASONS_TABLE . ' rr,' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u
				LEFT JOIN ' . FORUMS_TABLE . ' f ON f.forum_id = p.forum_id
				WHERE r.post_id = p.post_id
					AND r.reason_id = rr.reason_id
					AND p.topic_id = t.topic_id
					AND r.user_id = u.user_id
					AND p.forum_id IN (0, ' . implode(', ', $forum_list) . ')
				ORDER BY p.post_id DESC';
			$result = $db->sql_query_limit($sql, 5);

			while ($row = $db->sql_fetchrow($result))
			{
				$template->assign_block_vars('report', array(
					'U_POST_DETAILS'=> $url . '&amp;p=' . $row['post_id'] . '&amp;mode=post_details',
					'U_MCP_FORUM'	=> ($row['forum_id']) ? $url . '&amp;f=' . $row['forum_id'] . '&amp;mode=forum_view' : '',
					'U_MCP_TOPIC'	=> $url . '&amp;t=' . $row['topic_id'] . '&amp;mode=topic_view',
					'U_FORUM'		=> ($row['forum_id']) ? 'viewforum.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] : '',
					'U_TOPIC'		=> 'viewtopic.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id'],
					'U_REPORTER'	=> ($row['user_id'] == ANONYMOUS) ? '' : 'memberlist.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['user_id'],

					'FORUM_NAME'	=> ($row['forum_id']) ? $row['forum_name'] : $user->lang['POST_GLOBAL'],
					'TOPIC_TITLE'	=> $row['topic_title'],
					'REPORTER'		=> ($row['user_id'] == ANONYMOUS) ? $user->lang['GUEST'] : $row['username'],
					'SUBJECT'		=> ($row['post_subject']) ? $row['post_subject'] : $user->lang['NO_SUBJECT'],
					'REPORT_TIME'	=> $user->format_date($row['report_time']))
				);				
			}
		}

		if ($total == 0)
		{
			$template->assign_vars(array(
				'L_REPORTS_TOTAL'	=>	$user->lang['REPORTS_ZERO_TOTAL'],
				'S_HAS_REPORTS'		=>	false)
			);
		}
		else
		{
			$template->assign_vars(array(
				'L_REPORTS_TOTAL'	=> ($total == 1) ? $user->lang['REPORT_TOTAL'] : sprintf($user->lang['REPORTS_TOTAL'], $total),
				'S_HAS_REPORTS'		=> true)
			);
		}
	}

	// Latest 5 logs
	$forum_list = get_forum_list(array('m_', 'a_general'));

	if (!empty($forum_list))
	{
		// Add forum_id 0 for global announcements
		$forum_list[] = 0;

		$log_count = 0;
		$log = array();
		view_log('mod', $log, $log_count, 5, 0, $forum_list);

		foreach ($log as $row)
		{
			$template->assign_block_vars('log', array(
				'USERNAME'		=> $row['username'],
				'IP'			=> $row['ip'],
				'TIME'			=> $user->format_date($row['time']),
				'ACTION'		=> $row['action'],
				'U_VIEWTOPIC'	=> $row['viewtopic'],
				'U_VIEWLOGS'	=> $row['viewlogs'])
			);
		}
	}

	$template->assign_vars(array(
		'S_SHOW_LOGS'	=> (!empty($forum_list)) ? true : false,
		'S_HAS_LOGS'	=> (!empty($log)) ? true : false)
	);

	$template->assign_var('S_MCP_ACTION', $url);
	make_jumpbox($url . '&amp;mode=forum_view', 0, false, 'm_');
}

?>