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
* mcp_reports
* Handling the reports queue
* @package mcp
*/
class mcp_reports
{
	var $p_master;
	var $u_action;

	function mcp_main(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($id, $mode)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx, $action;

		include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);

		$forum_id = request_var('f', 0);
		$start = request_var('start', 0);

		$this->page_title = 'MCP_REPORTS';

		switch ($action)
		{
			case 'close':
			case 'delete':
				include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

				$post_id_list = request_var('post_id_list', array(0));

				if (!sizeof($post_id_list))
				{
					trigger_error('NO_POST_SELECTED');
				}

				close_report($post_id_list, $mode, $action);

			break;
		}

		switch ($mode)
		{
			case 'report_details':

				$user->add_lang('posting');

				$post_id = request_var('p', 0);

				// closed reports are accessed by report id
				$report_id = request_var('r', 0);

				$sql = 'SELECT r.post_id, r.user_id, r.report_closed, report_time, r.report_text, rr.reason_title, rr.reason_description, u.username
					FROM ' . REPORTS_TABLE . ' r, ' . REPORTS_REASONS_TABLE . ' rr, ' . USERS_TABLE . ' u
					WHERE ' . (($report_id) ? 'r.report_id = ' . $report_id : "r.post_id = $post_id AND r.report_closed = 0") . '
						AND rr.reason_id = r.reason_id
						AND r.user_id = u.user_id';
				$result = $db->sql_query($sql);
				$report = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$report)
				{
					trigger_error('NO_POST_REPORT');
				}

				if ($report_id)
				{
					$post_id = $report['post_id'];
				}

				$post_info = get_post_data(array($post_id), 'm_report');

				if (!sizeof($post_info))
				{
					trigger_error('NO_POST_SELECTED');
				}

				$post_info = $post_info[$post_id];

				$reason = array('title' => $report['reason_title'], 'description' => $report['reason_description']);
				if (isset($user->lang['report_reasons']['TITLE'][strtoupper($reason['title'])]) && isset($user->lang['report_reasons']['DESCRIPTION'][strtoupper($reason['title'])]))
				{
					$reason['description'] = $user->lang['report_reasons']['DESCRIPTION'][strtoupper($reason['title'])];
					$reason['title'] = $user->lang['report_reasons']['TITLE'][strtoupper($reason['title'])];
				}

				if (topic_review($post_info['topic_id'], $post_info['forum_id'], 'topic_review', 0, false))
				{
					$template->assign_vars(array(
						'S_TOPIC_REVIEW'	=> true,
						'TOPIC_TITLE'		=> $post_info['topic_title'])
					);
				}

				// Set some vars
				if ($post_info['user_id'] == ANONYMOUS)
				{
					$poster = ($post_info['post_username']) ? $post_info['post_username'] : $user->lang['GUEST'];
				}

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

				$template->assign_vars(array(
					'S_MCP_REPORT'			=> true,
					'S_CLOSE_ACTION'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", "i=reports&amp;p=$post_id&amp;f=$forum_id"),
					'S_CAN_VIEWIP'			=> $auth->acl_get('m_info', $post_info['forum_id']),
					'S_POST_REPORTED'		=> $post_info['post_reported'],
					'S_POST_UNAPPROVED'		=> !$post_info['post_approved'],
					'S_POST_LOCKED'			=> $post_info['post_edit_locked'],
					'S_USER_NOTES'			=> true,

					'U_EDIT'					=> ($auth->acl_get('m_edit', $post_info['forum_id'])) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=edit&amp;f={$post_info['forum_id']}&amp;p={$post_info['post_id']}") : '',
					'U_MCP_APPROVE'				=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=approve_details&amp;f=' . $post_info['forum_id'] . '&amp;p=' . $post_id),
					'U_MCP_REPORT'				=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=reports&amp;mode=report_details&amp;f=' . $post_info['forum_id'] . '&amp;p=' . $post_id),
					'U_MCP_REPORTER_NOTES'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $report['user_id']),
					'U_MCP_USER_NOTES'			=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $post_info['user_id']),
					'U_MCP_WARN_REPORTER'		=> ($auth->acl_getf_global('m_warn')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=warn&amp;mode=warn_user&amp;u=' . $report['user_id']) : '',
					'U_MCP_WARN_USER'			=> ($auth->acl_getf_global('m_warn')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=warn&amp;mode=warn_user&amp;u=' . $post_info['user_id']) : '',
					'U_VIEW_POST'				=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $post_info['forum_id'] . '&amp;p=' . $post_info['post_id'] . '#p' . $post_info['post_id']),
					'U_VIEW_PROFILE'			=> ($post_info['user_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $post_info['user_id']) : '',
					'U_VIEW_REPORTER_PROFILE'	=> ($report['user_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $report['user_id']) : '',
					'U_VIEW_TOPIC'				=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $post_info['forum_id'] . '&amp;t=' . $post_info['topic_id']),

					'EDIT_IMG'				=> $user->img('icon_post_edit', $user->lang['EDIT_POST']),
					'UNAPPROVED_IMG'		=> $user->img('icon_topic_unapproved', $user->lang['POST_UNAPPROVED']),

					'RETURN_REPORTS'			=> sprintf($user->lang['RETURN_REPORTS'], '<a href="' . append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=reports' . (($post_info['post_reported']) ? '&amp;mode=reports' : '&amp;mode=reports_closed') . '&amp;start=' . $start) . '">', '</a>'),
					'REPORTED_IMG'				=> $user->img('icon_topic_reported', $user->lang['POST_REPORTED']),
					'REPORT_REASON_TITLE'		=> $reason['title'],
					'REPORT_REASON_DESCRIPTION'	=> $reason['description'],
					'REPORTER_NAME'				=> ($report['user_id'] == ANONYMOUS) ? $user->lang['GUEST'] : $report['username'],
					'REPORT_DATE'				=> $user->format_date($report['report_time']),
					'REPORT_TEXT'				=> $report['report_text'],

					'POSTER_NAME'			=> $poster,
					'POST_PREVIEW'			=> $message,
					'POST_SUBJECT'			=> $post_info['post_subject'],
					'POST_DATE'				=> $user->format_date($post_info['post_time']),
					'POST_IP'				=> $post_info['poster_ip'],
					'POST_IPADDR'			=> @gethostbyaddr($post_info['poster_ip']),
					'POST_ID'				=> $post_info['post_id'])
				);

				$this->tpl_name = 'mcp_post';

			break;

			case 'reports':
			case 'reports_closed':
				$topic_id = request_var('t', 0);

				$forum_info = array();
				$forum_list_reports = get_forum_list('m_report', false, true);

				if ($topic_id)
				{
					$topic_info = get_topic_data(array($topic_id));

					if (!sizeof($topic_info))
					{
						trigger_error($user->lang['TOPIC_NOT_EXIST']);
					}

					$topic_info = $topic_info[$topic_id];
					$forum_id = $topic_info['forum_id'];
				}

				$forum_list = array();

				if (!$forum_id)
				{
					foreach ($forum_list_reports as $row)
					{
						$forum_list[] = $row['forum_id'];
					}

					$global_id = $forum_list[0];

					if (!sizeof($forum_list))
					{
						trigger_error('NOT_MODERATOR');
					}

					$sql = 'SELECT SUM(forum_topics) as sum_forum_topics
						FROM ' . FORUMS_TABLE . '
						WHERE ' . $db->sql_in_set('forum_id', $forum_list);
					$result = $db->sql_query($sql);
					$forum_info['forum_topics'] = (int) $db->sql_fetchfield('sum_forum_topics');
					$db->sql_freeresult($result);
				}
				else
				{
					$forum_info = get_forum_data(array($forum_id), 'm_report');

					if (!sizeof($forum_info))
					{
						trigger_error('NOT_MODERATOR');
					}

					$forum_info = $forum_info[$forum_id];
					$forum_list = array($forum_id);
					$global_id = $forum_id;
				}

				$forum_list[] = 0;
				$forum_data = array();

				$forum_options = '<option value="0"' . (($forum_id == 0) ? ' selected="selected"' : '') . '>' . $user->lang['ALL_FORUMS'] . '</option>';
				foreach ($forum_list_reports as $row)
				{
					$forum_options .= '<option value="' . $row['forum_id'] . '"' . (($forum_id == $row['forum_id']) ? ' selected="selected"' : '') . '>' . $row['forum_name'] . '</option>';
					$forum_data[$row['forum_id']] = $row;
				}
				unset($forum_list_reports);

				$sort_days = $total = 0;
				$sort_key = $sort_dir = '';
				$sort_by_sql = $sort_order_sql = array();
				mcp_sorting($mode, $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $forum_id, $topic_id);

				$forum_topics = ($total == -1) ? $forum_info['forum_topics'] : $total;
				$limit_time_sql = ($sort_days) ? 'AND t.topic_last_post_time >= ' . (time() - ($sort_days * 86400)) : '';

				if ($mode == 'reports')
				{
					$report_state = 'AND p.post_reported = 1 AND r.report_closed = 0';
				}
				else
				{
					$report_state = 'AND r.report_closed = 1';
				}

				$sql = 'SELECT r.report_id
					FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . REPORTS_TABLE . ' r ' . (($sort_order_sql[0] == 'u') ? ', ' . USERS_TABLE . ' u' : '') . (($sort_order_sql[0] == 'r') ? ', ' . USERS_TABLE . ' ru' : '') . '
					WHERE ' . $db->sql_in_set('p.forum_id', $forum_list) . "
						$report_state
						AND r.post_id = p.post_id
						" . (($sort_order_sql[0] == 'u') ? 'AND u.user_id = p.poster_id' : '') . '
						' . (($sort_order_sql[0] == 'r') ? 'AND ru.user_id = p.poster_id' : '') . '
						' . (($topic_id) ? 'AND p.topic_id = ' . $topic_id : '') . "
						AND t.topic_id = p.topic_id
						$limit_time_sql
					ORDER BY $sort_order_sql";
				$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);

				$i = 0;
				$report_ids = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$report_ids[] = $row['report_id'];
					$row_num[$row['report_id']] = $i++;
				}
				$db->sql_freeresult($result);

				if (sizeof($report_ids))
				{
					$sql = 'SELECT t.forum_id, t.topic_id, t.topic_title, p.post_id, p.post_subject, p.post_username, p.poster_id, p.post_time, u.username, r.user_id as reporter_id, ru.username as reporter_name, r.report_time, r.report_id
						FROM ' . REPORTS_TABLE . ' r, ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u, ' . USERS_TABLE . ' ru
						WHERE ' . $db->sql_in_set('r.report_id', $report_ids) . '
							AND t.topic_id = p.topic_id
							AND r.post_id = p.post_id
							AND u.user_id = p.poster_id
							AND ru.user_id = r.user_id';
					$result = $db->sql_query($sql);

					$report_data = $rowset = array();
					while ($row = $db->sql_fetchrow($result))
					{
						if ($row['poster_id'] == ANONYMOUS)
						{
							$poster = (!empty($row['post_username'])) ? $row['post_username'] : $user->lang['GUEST'];
						}
						else
						{
							$poster = $row['username'];
						}

						$global_topic = ($row['forum_id']) ? false : true;
						if ($global_topic)
						{
							$row['forum_id'] = $global_id;
						}

						$template->assign_block_vars('postrow', array(
							'U_VIEWFORUM'				=> (!$global_topic) ? append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $row['forum_id']) : '',
							'U_VIEWPOST'				=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $row['forum_id'] . '&amp;p=' . $row['post_id']) . '#p' . $row['post_id'],
							'U_VIEW_DETAILS'			=> append_sid("{$phpbb_root_path}mcp.$phpEx", "i=reports&amp;start=$start&amp;mode=report_details&amp;f={$row['forum_id']}&amp;r={$row['report_id']}"),
							'U_VIEW_POSTER_PROFILE'		=> ($row['poster_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $row['poster_id']) : '',
							'U_VIEW_REPORTER_PROFILE'	=> ($row['reporter_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $row['reporter_id']) : '',

							'FORUM_NAME'	=> (!$global_topic) ? $forum_data[$row['forum_id']]['forum_name'] : $user->lang['GLOBAL_ANNOUNCEMENT'],
							'POSTER'		=> $poster,
							'POST_ID'		=> $row['post_id'],
							'POST_SUBJECT'	=> $row['post_subject'],
							'POST_TIME'		=> $user->format_date($row['post_time']),
							'REPORTER'		=> ($row['reporter_id'] == ANONYMOUS) ? $user->lang['GUEST'] : $row['reporter_name'],
							'REPORT_TIME'	=> $user->format_date($row['report_time']),
							'TOPIC_TITLE'	=> $row['topic_title'])
						);
					}
					$db->sql_freeresult($result);
					unset($report_ids, $row);
				}

				// Now display the page
				$template->assign_vars(array(
					'L_EXPLAIN'				=> ($mode == 'reports') ? $user->lang['MCP_REPORTS_OPEN_EXPLAIN'] : $user->lang['MCP_REPORTS_CLOSED_EXPLAIN'],
					'L_TITLE'				=> ($mode == 'reports') ? $user->lang['MCP_REPORTS_OPEN'] : $user->lang['MCP_REPORTS_CLOSED'],
					'L_ONLY_TOPIC'			=> ($topic_id) ? sprintf($user->lang['ONLY_TOPIC'], $topic_info['topic_title']) : '',

					'S_MCP_ACTION'			=> build_url(array('t', 'f', 'sd', 'st', 'sk')),
					'S_FORUM_OPTIONS'		=> $forum_options,
					'S_CLOSED'				=> ($mode == 'reports_closed') ? true : false,

					'PAGINATION'			=> generate_pagination($this->u_action . "&amp;f=$forum_id&amp;t=$topic_id", $total, $config['topics_per_page'], $start),
					'PAGE_NUMBER'			=> on_page($total, $config['topics_per_page'], $start),
					'TOPIC_ID'				=> $topic_id,
					'TOTAL'					=> $total)
				);

				$this->tpl_name = 'mcp_reports';
			break;
		}
	}
}

/**
* Closes a report
*/
function close_report($post_id_list, $mode, $action)
{
	global $db, $template, $user, $config;
	global $phpEx, $phpbb_root_path;

	if (!($forum_id = check_ids($post_id_list, POSTS_TABLE, 'post_id', 'm_report')))
	{
		trigger_error('NOT_AUTHORIZED');
	}

	if (($action == 'delete') && (strpos($user->data['session_page'], 'mode=report_details') !== false))
	{
		$redirect = request_var('redirect', build_url(array('mode')) . '&amp;mode=reports');
	}
	else
	{
		$redirect = request_var('redirect', $user->data['session_page']);
	}
	$success_msg = '';

	$s_hidden_fields = build_hidden_fields(array(
		'i'				=> 'reports',
		'mode'			=> $mode,
		'post_id_list'	=> $post_id_list,
		'f'				=> $forum_id,
		'action'		=> $action,
		'redirect'		=> $redirect)
	);

	if (confirm_box(true))
	{
		$post_info = get_post_data($post_id_list, 'm_report');

		$sql = 'SELECT r.post_id, r.report_closed, r.user_id, r.user_notify, u.username, u.user_email, u.user_jabber, u.user_lang, u.user_notify_type
			FROM ' . REPORTS_TABLE . ' r, ' . USERS_TABLE . ' u
			WHERE ' . $db->sql_in_set('r.post_id', array_keys($post_info)) . '
				' . (($action == 'close') ? 'AND r.report_closed = 0' : '') . '
				AND r.user_id = u.user_id';
		$result = $db->sql_query($sql);

		$reports = array();
		while ($report = $db->sql_fetchrow($result))
		{
			$reports[$report['post_id']] = $report;
		}
		$db->sql_freeresult($result);

		$close_report_posts = $close_report_topics = $notify_reporters = array();
		foreach ($post_info as $post_id => $post_data)
		{
			if (isset($reports[$post_id]))
			{
				$close_report_posts[] = $post_id;
				$close_report_topics[] = $post_data['topic_id'];

				if ($reports[$post_id]['user_notify'] && !$reports[$post_id]['report_closed'])
				{
					$notify_reporters[$post_id] = $reports[$post_id];
				}
			}
		}

		if (sizeof($close_report_posts))
		{
			$close_report_topics = array_unique($close_report_topics);

			// Get a list of topics that still contain reported posts
			$sql = 'SELECT DISTINCT topic_id
				FROM ' . POSTS_TABLE . '
				WHERE ' . $db->sql_in_set('topic_id', $close_report_topics) . '
					AND post_reported = 1
					AND ' . $db->sql_in_set('post_id', $close_report_posts, true);
			$result = $db->sql_query($sql);

			$keep_report_topics = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$keep_report_topics[] = $row['topic_id'];
			}
			$db->sql_freeresult($result);

			$close_report_topics = array_diff($close_report_topics, $keep_report_topics);
			unset($keep_report_topics);

			$db->sql_transaction('begin');

			if ($action == 'close')
			{
				$sql = 'UPDATE ' . REPORTS_TABLE . '
					SET report_closed = 1
					WHERE ' . $db->sql_in_set('post_id', $close_report_posts);
			}
			else
			{
				$sql = 'DELETE FROM ' . REPORTS_TABLE . '
					WHERE ' . $db->sql_in_set('post_id', $close_report_posts);
			}
			$db->sql_query($sql);

			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET post_reported = 0
				WHERE ' . $db->sql_in_set('post_id', $close_report_posts);
			$db->sql_query($sql);

			if (sizeof($close_report_topics))
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET topic_reported = 0
					WHERE ' . $db->sql_in_set('topic_id', $close_report_topics);
				$db->sql_query($sql);
			}

			$db->sql_transaction('commit');
		}
		unset($close_report_posts, $close_report_topics);

		$messenger = new messenger();

		// Notify reporters
		if (sizeof($notify_reporters))
		{
			$email_sig = str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']);

			foreach ($notify_reporters as $post_id => $reporter)
			{
				if ($reporter['user_id'] == ANONYMOUS)
				{
					continue;
				}

				$messenger->template('report_' . $action . 'd', $reporter['user_lang']);

				$messenger->replyto($config['board_email']);
				$messenger->to($reporter['user_email'], $reporter['username']);
				$messenger->im($reporter['user_jabber'], $reporter['username']);

				$messenger->assign_vars(array(
					'EMAIL_SIG'		=> $email_sig,
					'SITENAME'		=> $config['sitename'],
					'USERNAME'		=> html_entity_decode($reporter['username']),
					'CLOSER_NAME'	=> html_entity_decode($user->data['username']),
					'POST_SUBJECT'	=> html_entity_decode(censor_text($post_info[$post_id]['post_subject'])),
					'TOPIC_TITLE'	=> html_entity_decode(censor_text($post_info[$post_id]['topic_title'])))
				);

				$messenger->send($reporter['user_notify_type']);
				$messenger->reset();
			}

			$messenger->save_queue();
		}
		unset($notify_reporters, $post_info);

		$success_msg = (sizeof($post_id_list) == 1) ? 'REPORT_' . strtoupper($action) . 'D_SUCCESS' : 'REPORTS_' . strtoupper($action) . 'D_SUCCESS';
	}
	else
	{
		confirm_box(false, $user->lang[strtoupper($action) . '_REPORT' . ((sizeof($post_id_list) == 1) ? '' : 'S') . '_CONFIRM'], $s_hidden_fields);
	}

	$redirect = request_var('redirect', "index.$phpEx");
	$redirect = reapply_sid($redirect);

	if (!$success_msg)
	{
		redirect($redirect);
	}
	else
	{
		meta_refresh(3, $redirect);
		trigger_error($user->lang[$success_msg] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], "<a href=\"$redirect\">", '</a>'));
	}
}

?>