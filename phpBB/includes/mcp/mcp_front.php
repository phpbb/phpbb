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
* MCP Front Panel
*/
function mcp_front_view($id, $mode, $action)
{
	global $phpEx, $phpbb_root_path, $config;
	global $template, $db, $user, $auth, $module;
	global $phpbb_dispatcher;

	// Latest 5 unapproved
	if ($module->loaded('queue'))
	{
		$forum_list = array_values(array_intersect(get_forum_list('f_read'), get_forum_list('m_approve')));
		$post_list = array();
		$forum_names = array();

		$forum_id = request_var('f', 0);

		$template->assign_var('S_SHOW_UNAPPROVED', (!empty($forum_list)) ? true : false);

		if (!empty($forum_list))
		{
			$sql_ary = array(
				'SELECT' => 'COUNT(post_id) AS total',
				'FROM' => array(
						POSTS_TABLE => 'p',
					),
				'WHERE' => $db->sql_in_set('p.forum_id', $forum_list) . '
					AND ' . $db->sql_in_set('p.post_visibility', array(ITEM_UNAPPROVED, ITEM_REAPPROVE))
			);

			/**
			* Allow altering the query to get the number of unapproved posts
			*
			* @event core.mcp_front_queue_unapproved_total_before
			* @var	int		sql_ary						Query to get the total number of unapproved posts
			* @var	array	forum_list					List of forums to look for unapproved posts
			* @since 3.1.5-RC1
			*/
			$vars = array('sql_ary', 'forum_list');
			extract($phpbb_dispatcher->trigger_event('core.mcp_front_queue_unapproved_total_before', compact($vars)));

			$sql = $db->sql_build_query('SELECT', $sql_ary);
			$result = $db->sql_query($sql);
			$total = (int) $db->sql_fetchfield('total');
			$db->sql_freeresult($result);

			if ($total)
			{
				$sql = 'SELECT forum_id, forum_name
					FROM ' . FORUMS_TABLE . '
					WHERE ' . $db->sql_in_set('forum_id', $forum_list);
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$forum_names[$row['forum_id']] = $row['forum_name'];
				}
				$db->sql_freeresult($result);

				$sql = 'SELECT post_id
					FROM ' . POSTS_TABLE . '
					WHERE ' . $db->sql_in_set('forum_id', $forum_list) . '
						AND ' . $db->sql_in_set('post_visibility', array(ITEM_UNAPPROVED, ITEM_REAPPROVE)) . '
					ORDER BY post_time DESC, post_id DESC';
				$result = $db->sql_query_limit($sql, 5);

				while ($row = $db->sql_fetchrow($result))
				{
					$post_list[] = $row['post_id'];
				}
				$db->sql_freeresult($result);

				if (empty($post_list))
				{
					$total = 0;
				}
			}

			/**
			* Alter list of posts and total as required
			*
			* @event core.mcp_front_view_queue_postid_list_after
			* @var	int		total						Number of unapproved posts
			* @var	array	post_list					List of unapproved posts
			* @var	array	forum_list					List of forums that contain the posts
			* @var	array	forum_names					Associative array with forum_id as key and it's corresponding forum_name as value
			* @since 3.1.0-RC3
			*/
			$vars = array('total', 'post_list', 'forum_list', 'forum_names');
			extract($phpbb_dispatcher->trigger_event('core.mcp_front_view_queue_postid_list_after', compact($vars)));

			if ($total)
			{
				$sql = 'SELECT p.post_id, p.post_subject, p.post_time, p.post_attachment, p.poster_id, p.post_username, u.username, u.username_clean, u.user_colour, t.topic_id, t.topic_title, t.topic_first_post_id, p.forum_id
					FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u
					WHERE ' . $db->sql_in_set('p.post_id', $post_list) . '
						AND t.topic_id = p.topic_id
						AND p.poster_id = u.user_id
					ORDER BY p.post_time DESC, p.post_id DESC';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('unapproved', array(
						'U_POST_DETAILS'	=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=approve_details&amp;f=' . $row['forum_id'] . '&amp;p=' . $row['post_id']),
						'U_MCP_FORUM'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=main&amp;mode=forum_view&amp;f=' . $row['forum_id']),
						'U_MCP_TOPIC'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=main&amp;mode=topic_view&amp;f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id']),
						'U_FORUM'			=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $row['forum_id']),
						'U_TOPIC'			=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id']),

						'AUTHOR_FULL'		=> get_username_string('full', $row['poster_id'], $row['username'], $row['user_colour']),
						'AUTHOR'			=> get_username_string('username', $row['poster_id'], $row['username'], $row['user_colour']),
						'AUTHOR_COLOUR'		=> get_username_string('colour', $row['poster_id'], $row['username'], $row['user_colour']),
						'U_AUTHOR'			=> get_username_string('profile', $row['poster_id'], $row['username'], $row['user_colour']),

						'FORUM_NAME'	=> $forum_names[$row['forum_id']],
						'POST_ID'		=> $row['post_id'],
						'TOPIC_TITLE'	=> $row['topic_title'],
						'SUBJECT'		=> ($row['post_subject']) ? $row['post_subject'] : $user->lang['NO_SUBJECT'],
						'POST_TIME'		=> $user->format_date($row['post_time']),
						'ATTACH_ICON_IMG'	=> ($auth->acl_get('u_download') && $auth->acl_get('f_download', $row['forum_id']) && $row['post_attachment']) ? $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',
					));
				}
				$db->sql_freeresult($result);
			}

			$s_hidden_fields = build_hidden_fields(array(
				'redirect'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=main' . (($forum_id) ? '&amp;f=' . $forum_id : ''))
			));

			$template->assign_vars(array(
				'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
				'S_MCP_QUEUE_ACTION'	=> append_sid("{$phpbb_root_path}mcp.$phpEx", "i=queue"),
				'L_UNAPPROVED_TOTAL'	=> $user->lang('UNAPPROVED_POSTS_TOTAL', (int) $total),
				'S_HAS_UNAPPROVED_POSTS'=> ($total != 0),
			));
		}
	}

	// Latest 5 reported
	if ($module->loaded('reports'))
	{
		$forum_list = array_values(array_intersect(get_forum_list('f_read'), get_forum_list('m_report')));

		$template->assign_var('S_SHOW_REPORTS', (!empty($forum_list)) ? true : false);

		if (!empty($forum_list))
		{
			$sql = 'SELECT COUNT(r.report_id) AS total
				FROM ' . REPORTS_TABLE . ' r, ' . POSTS_TABLE . ' p
				WHERE r.post_id = p.post_id
					AND r.pm_id = 0
					AND r.report_closed = 0
					AND ' . $db->sql_in_set('p.forum_id', $forum_list);

			/**
			* Alter sql query to count the number of reported posts
			*
			* @event core.mcp_front_reports_count_query_before
			* @var	int		sql				The query string used to get the number of reports that exist
			* @var	array	forum_list		List of forums that contain the posts
			* @since 3.1.5-RC1
			*/
			$vars = array('sql', 'forum_list');
			extract($phpbb_dispatcher->trigger_event('core.mcp_front_reports_count_query_before', compact($vars)));

			$result = $db->sql_query($sql);
			$total = (int) $db->sql_fetchfield('total');
			$db->sql_freeresult($result);

			if ($total)
			{
				$sql_ary = array(
					'SELECT'	=> 'r.report_time, p.post_id, p.post_subject, p.post_time, p.post_attachment, u.username, u.username_clean, u.user_colour, u.user_id, u2.username as author_name, u2.username_clean as author_name_clean, u2.user_colour as author_colour, u2.user_id as author_id, t.topic_id, t.topic_title, f.forum_id, f.forum_name',

					'FROM'		=> array(
						REPORTS_TABLE			=> 'r',
						REPORTS_REASONS_TABLE	=> 'rr',
						TOPICS_TABLE			=> 't',
						USERS_TABLE				=> array('u', 'u2'),
						POSTS_TABLE				=> 'p',
					),

					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(FORUMS_TABLE => 'f'),
							'ON'	=> 'f.forum_id = p.forum_id',
						),
					),

					'WHERE'		=> 'r.post_id = p.post_id
						AND r.pm_id = 0
						AND r.report_closed = 0
						AND r.reason_id = rr.reason_id
						AND p.topic_id = t.topic_id
						AND r.user_id = u.user_id
						AND p.poster_id = u2.user_id
						AND ' . $db->sql_in_set('p.forum_id', $forum_list),

					'ORDER_BY'	=> 'p.post_time DESC, p.post_id DESC',
				);

				/**
				* Alter sql query to get latest reported posts
				*
				* @event core.mcp_front_reports_listing_query_before
				* @var	int		sql_ary						Associative array with the query to be executed
				* @var	array	forum_list					List of forums that contain the posts
				* @since 3.1.0-RC3
				*/
				$vars = array('sql_ary', 'forum_list');
				extract($phpbb_dispatcher->trigger_event('core.mcp_front_reports_listing_query_before', compact($vars)));

				$sql = $db->sql_build_query('SELECT', $sql_ary);
				$result = $db->sql_query_limit($sql, 5);

				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('report', array(
						'U_POST_DETAILS'	=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'f=' . $row['forum_id'] . '&amp;p=' . $row['post_id'] . "&amp;i=reports&amp;mode=report_details"),
						'U_MCP_FORUM'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'f=' . $row['forum_id'] . "&amp;i=$id&amp;mode=forum_view"),
						'U_MCP_TOPIC'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id'] . "&amp;i=$id&amp;mode=topic_view"),
						'U_FORUM'			=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $row['forum_id']),
						'U_TOPIC'			=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id']),

						'REPORTER_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
						'REPORTER'			=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
						'REPORTER_COLOUR'	=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),
						'U_REPORTER'		=> get_username_string('profile', $row['user_id'], $row['username'], $row['user_colour']),

						'AUTHOR_FULL'		=> get_username_string('full', $row['author_id'], $row['author_name'], $row['author_colour']),
						'AUTHOR'			=> get_username_string('username', $row['author_id'], $row['author_name'], $row['author_colour']),
						'AUTHOR_COLOUR'		=> get_username_string('colour', $row['author_id'], $row['author_name'], $row['author_colour']),
						'U_AUTHOR'			=> get_username_string('profile', $row['author_id'], $row['author_name'], $row['author_colour']),

						'FORUM_NAME'	=> $row['forum_name'],
						'TOPIC_TITLE'	=> $row['topic_title'],
						'SUBJECT'		=> ($row['post_subject']) ? $row['post_subject'] : $user->lang['NO_SUBJECT'],
						'REPORT_TIME'	=> $user->format_date($row['report_time']),
						'POST_TIME'		=> $user->format_date($row['post_time']),
						'ATTACH_ICON_IMG'	=> ($auth->acl_get('u_download') && $auth->acl_get('f_download', $row['forum_id']) && $row['post_attachment']) ? $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',
					));
				}
				$db->sql_freeresult($result);
			}

			$template->assign_vars(array(
				'L_REPORTS_TOTAL'	=> $user->lang('REPORTS_TOTAL', (int) $total),
				'S_HAS_REPORTS'		=> ($total != 0),
			));
		}
	}

	// Latest 5 reported PMs
	if ($module->loaded('pm_reports') && $auth->acl_getf_global('m_report'))
	{
		$template->assign_var('S_SHOW_PM_REPORTS', true);
		$user->add_lang(array('ucp'));

		$sql = 'SELECT COUNT(r.report_id) AS total
			FROM ' . REPORTS_TABLE . ' r, ' . PRIVMSGS_TABLE . ' p
			WHERE r.post_id = 0
				AND r.pm_id = p.msg_id
				AND r.report_closed = 0';
		$result = $db->sql_query($sql);
		$total = (int) $db->sql_fetchfield('total');
		$db->sql_freeresult($result);

		if ($total)
		{
			include($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);

			$sql_ary = array(
				'SELECT'	=> 'r.report_id, r.report_time, p.msg_id, p.message_subject, p.message_time, p.to_address, p.bcc_address, p.message_attachment, u.username, u.username_clean, u.user_colour, u.user_id, u2.username as author_name, u2.username_clean as author_name_clean, u2.user_colour as author_colour, u2.user_id as author_id',

				'FROM'		=> array(
					REPORTS_TABLE			=> 'r',
					REPORTS_REASONS_TABLE	=> 'rr',
					USERS_TABLE				=> array('u', 'u2'),
					PRIVMSGS_TABLE				=> 'p',
				),

				'WHERE'		=> 'r.pm_id = p.msg_id
					AND r.post_id = 0
					AND r.report_closed = 0
					AND r.reason_id = rr.reason_id
					AND r.user_id = u.user_id
					AND p.author_id = u2.user_id',

				'ORDER_BY'	=> 'p.message_time DESC',
			);
			$sql = $db->sql_build_query('SELECT', $sql_ary);
			$result = $db->sql_query_limit($sql, 5);

			$pm_by_id = $pm_list = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$pm_by_id[(int) $row['msg_id']] = $row;
				$pm_list[] = (int) $row['msg_id'];
			}
			$db->sql_freeresult($result);

			$address_list = get_recipient_strings($pm_by_id);

			foreach ($pm_list as $message_id)
			{
				$row = $pm_by_id[$message_id];

				$template->assign_block_vars('pm_report', array(
					'U_PM_DETAILS'	=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'r=' . $row['report_id'] . "&amp;i=pm_reports&amp;mode=pm_report_details"),

					'REPORTER_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
					'REPORTER'			=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
					'REPORTER_COLOUR'	=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),
					'U_REPORTER'		=> get_username_string('profile', $row['user_id'], $row['username'], $row['user_colour']),

					'PM_AUTHOR_FULL'		=> get_username_string('full', $row['author_id'], $row['author_name'], $row['author_colour']),
					'PM_AUTHOR'			=> get_username_string('username', $row['author_id'], $row['author_name'], $row['author_colour']),
					'PM_AUTHOR_COLOUR'		=> get_username_string('colour', $row['author_id'], $row['author_name'], $row['author_colour']),
					'U_PM_AUTHOR'			=> get_username_string('profile', $row['author_id'], $row['author_name'], $row['author_colour']),

					'PM_SUBJECT'		=> $row['message_subject'],
					'REPORT_TIME'		=> $user->format_date($row['report_time']),
					'PM_TIME'			=> $user->format_date($row['message_time']),
					'RECIPIENTS'		=> implode(', ', $address_list[$row['msg_id']]),
					'ATTACH_ICON_IMG'	=> ($auth->acl_get('u_download') && $row['message_attachment']) ? $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',
				));
			}
		}

		$template->assign_vars(array(
			'L_PM_REPORTS_TOTAL'	=> $user->lang('PM_REPORTS_TOTAL', (int) $total),
			'S_HAS_PM_REPORTS'		=> ($total != 0),
		));
	}

	// Latest 5 logs
	if ($module->loaded('logs'))
	{
		$forum_list = array_values(array_intersect(get_forum_list('f_read'), get_forum_list('m_')));

		if (!empty($forum_list))
		{
			$log_count = false;
			$log = array();
			view_log('mod', $log, $log_count, 5, 0, $forum_list);

			foreach ($log as $row)
			{
				$template->assign_block_vars('log', array(
					'USERNAME'		=> $row['username_full'],
					'IP'			=> $row['ip'],
					'TIME'			=> $user->format_date($row['time']),
					'ACTION'		=> $row['action'],
					'U_VIEW_TOPIC'	=> (!empty($row['viewtopic'])) ? $row['viewtopic'] : '',
					'U_VIEWLOGS'	=> (!empty($row['viewlogs'])) ? $row['viewlogs'] : '')
				);
			}
		}

		$template->assign_vars(array(
			'S_SHOW_LOGS'	=> (!empty($forum_list)) ? true : false,
			'S_HAS_LOGS'	=> (!empty($log)) ? true : false)
		);
	}

	$template->assign_var('S_MCP_ACTION', append_sid("{$phpbb_root_path}mcp.$phpEx"));
	make_jumpbox(append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=main&amp;mode=forum_view'), 0, false, 'm_', true);
}
