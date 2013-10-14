<?php
/**
*
* @package mcp
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
* mcp_reports
* Handling the reports queue
* @package mcp
*/
class mcp_reports
{
	var $p_master;
	var $u_action;

	function mcp_reports(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($id, $mode)
	{
		global $auth, $db, $user, $template, $cache;
		global $config, $phpbb_root_path, $phpEx, $action, $phpbb_container;

		include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);

		$forum_id = request_var('f', 0);
		$start = request_var('start', 0);

		$this->page_title = 'MCP_REPORTS';

		switch ($action)
		{
			case 'close':
			case 'delete':
				include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

				$report_id_list = request_var('report_id_list', array(0));

				if (!sizeof($report_id_list))
				{
					trigger_error('NO_REPORT_SELECTED');
				}

				close_report($report_id_list, $mode, $action);

			break;
		}

		switch ($mode)
		{
			case 'report_details':

				$user->add_lang(array('posting', 'viewforum', 'viewtopic'));

				$post_id = request_var('p', 0);

				// closed reports are accessed by report id
				$report_id = request_var('r', 0);

				$sql = 'SELECT r.post_id, r.user_id, r.report_id, r.report_closed, report_time, r.report_text, r.reported_post_text, r.reported_post_uid, r.reported_post_bitfield, r.reported_post_enable_magic_url, r.reported_post_enable_smilies, r.reported_post_enable_bbcode, rr.reason_title, rr.reason_description, u.username, u.username_clean, u.user_colour
					FROM ' . REPORTS_TABLE . ' r, ' . REPORTS_REASONS_TABLE . ' rr, ' . USERS_TABLE . ' u
					WHERE ' . (($report_id) ? 'r.report_id = ' . $report_id : "r.post_id = $post_id") . '
						AND rr.reason_id = r.reason_id
						AND r.user_id = u.user_id
						AND r.pm_id = 0
					ORDER BY report_closed ASC';
				$result = $db->sql_query_limit($sql, 1);
				$report = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$report)
				{
					trigger_error('NO_REPORT');
				}

				$phpbb_notifications = $phpbb_container->get('notification_manager');

				$phpbb_notifications->mark_notifications_read('report_post', $post_id, $user->data['user_id']);

				if (!$report_id && $report['report_closed'])
				{
					trigger_error('REPORT_CLOSED');
				}

				$post_id = $report['post_id'];
				$report_id = $report['report_id'];
				
				$parse_post_flags = $report['reported_post_enable_bbcode'] ? OPTION_FLAG_BBCODE : 0;
				$parse_post_flags += $report['reported_post_enable_smilies'] ? OPTION_FLAG_SMILIES : 0;
				$parse_post_flags += $report['reported_post_enable_magic_url'] ? OPTION_FLAG_LINKS : 0; 

				$post_info = get_post_data(array($post_id), 'm_report', true);

				if (!sizeof($post_info))
				{
					trigger_error('NO_REPORT_SELECTED');
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
						'S_BBCODE_ALLOWED'	=> $post_info['enable_bbcode'],
						'TOPIC_TITLE'		=> $post_info['topic_title'],
						'REPORTED_POST_ID'	=> $post_id,
					));
				}

				$topic_tracking_info = $extensions = $attachments = array();
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


				$report['report_text'] = make_clickable(bbcode_nl2br($report['report_text']));

				if ($post_info['post_attachment'] && $auth->acl_get('u_download') && $auth->acl_get('f_download', $post_info['forum_id']))
				{
					$sql = 'SELECT *
						FROM ' . ATTACHMENTS_TABLE . '
						WHERE post_msg_id = ' . $post_id . '
							AND in_message = 0
						ORDER BY filetime DESC';
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$attachments[] = $row;
					}
					$db->sql_freeresult($result);

					if (sizeof($attachments))
					{
						$update_count = array();
						parse_attachments($post_info['forum_id'], $report['reported_post_text'], $attachments, $update_count);
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

				$template->assign_vars(array(
					'S_MCP_REPORT'			=> true,
					'S_CLOSE_ACTION'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=reports&amp;mode=report_details&amp;f=' . $post_info['forum_id'] . '&amp;p=' . $post_id),
					'S_CAN_VIEWIP'			=> $auth->acl_get('m_info', $post_info['forum_id']),
					'S_POST_REPORTED'		=> $post_info['post_reported'],
					'S_POST_UNAPPROVED'		=> ($post_info['post_visibility'] == ITEM_UNAPPROVED),
					'S_POST_LOCKED'			=> $post_info['post_edit_locked'],
					'S_REPORT_CLOSED'		=> $report['report_closed'],
					'S_USER_NOTES'			=> true,

					'U_EDIT'					=> ($auth->acl_get('m_edit', $post_info['forum_id'])) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=edit&amp;f={$post_info['forum_id']}&amp;p={$post_info['post_id']}") : '',
					'U_MCP_APPROVE'				=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=approve_details&amp;f=' . $post_info['forum_id'] . '&amp;p=' . $post_id),
					'U_MCP_REPORT'				=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=reports&amp;mode=report_details&amp;f=' . $post_info['forum_id'] . '&amp;p=' . $post_id),
					'U_MCP_REPORTER_NOTES'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $report['user_id']),
					'U_MCP_USER_NOTES'			=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $post_info['user_id']),
					'U_MCP_WARN_REPORTER'		=> ($auth->acl_get('m_warn')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=warn&amp;mode=warn_user&amp;u=' . $report['user_id']) : '',
					'U_MCP_WARN_USER'			=> ($auth->acl_get('m_warn')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=warn&amp;mode=warn_user&amp;u=' . $post_info['user_id']) : '',
					'U_VIEW_FORUM'				=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $post_info['forum_id']),
					'U_VIEW_POST'				=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $post_info['forum_id'] . '&amp;p=' . $post_info['post_id'] . '#p' . $post_info['post_id']),
					'U_VIEW_TOPIC'				=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $post_info['forum_id'] . '&amp;t=' . $post_info['topic_id']),

					'EDIT_IMG'				=> $user->img('icon_post_edit', $user->lang['EDIT_POST']),
					'MINI_POST_IMG'			=> ($post_unread) ? $user->img('icon_post_target_unread', 'UNREAD_POST') : $user->img('icon_post_target', 'POST'),
					'UNAPPROVED_IMG'		=> $user->img('icon_topic_unapproved', $user->lang['POST_UNAPPROVED']),

					'RETURN_REPORTS'			=> sprintf($user->lang['RETURN_REPORTS'], '<a href="' . append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=reports' . (($post_info['post_reported']) ? '&amp;mode=reports' : '&amp;mode=reports_closed') . '&amp;start=' . $start . '&amp;f=' . $post_info['forum_id']) . '">', '</a>'),
					'REPORTED_IMG'				=> $user->img('icon_topic_reported', $user->lang['POST_REPORTED']),
					'REPORT_DATE'				=> $user->format_date($report['report_time']),
					'REPORT_ID'					=> $report_id,
					'REPORT_REASON_TITLE'		=> $reason['title'],
					'REPORT_REASON_DESCRIPTION'	=> $reason['description'],
					'REPORT_TEXT'				=> $report['report_text'],

					'POST_AUTHOR_FULL'		=> get_username_string('full', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
					'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
					'POST_AUTHOR'			=> get_username_string('username', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
					'U_POST_AUTHOR'			=> get_username_string('profile', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),

					'REPORTER_FULL'				=> get_username_string('full', $report['user_id'], $report['username'], $report['user_colour']),
					'REPORTER_COLOUR'			=> get_username_string('colour', $report['user_id'], $report['username'], $report['user_colour']),
					'REPORTER_NAME'				=> get_username_string('username', $report['user_id'], $report['username'], $report['user_colour']),
					'U_VIEW_REPORTER_PROFILE'	=> get_username_string('profile', $report['user_id'], $report['username'], $report['user_colour']),

					'POST_PREVIEW'			=> generate_text_for_display($report['reported_post_text'], $report['reported_post_uid'], $report['reported_post_bitfield'], $parse_post_flags, false),
					'POST_SUBJECT'			=> ($post_info['post_subject']) ? $post_info['post_subject'] : $user->lang['NO_SUBJECT'],
					'POST_DATE'				=> $user->format_date($post_info['post_time']),
					'POST_IP'				=> $post_info['poster_ip'],
					'POST_IPADDR'			=> ($auth->acl_get('m_info', $post_info['forum_id']) && request_var('lookup', '')) ? @gethostbyaddr($post_info['poster_ip']) : '',
					'POST_ID'				=> $post_info['post_id'],

					'U_LOOKUP_IP'			=> ($auth->acl_get('m_info', $post_info['forum_id'])) ? $this->u_action . '&amp;r=' . $report_id . '&amp;p=' . $post_id . '&amp;f=' . $forum_id . '&amp;lookup=' . $post_info['poster_ip'] . '#ip' : '',
				));

				$this->tpl_name = 'mcp_post';

			break;

			case 'reports':
			case 'reports_closed':
				$topic_id = request_var('t', 0);

				$forum_info = array();
				$forum_list_reports = get_forum_list('m_report', false, true);
				$forum_list_read = array_flip(get_forum_list('f_read', true, true)); // Flipped so we can isset() the forum IDs

				// Remove forums we cannot read
				foreach ($forum_list_reports as $k => $forum_data)
				{
					if (!isset($forum_list_read[$forum_data['forum_id']]))
					{
						unset($forum_list_reports[$k]);
					}
				}
				unset($forum_list_read);

				if ($topic_id)
				{
					$topic_info = get_topic_data(array($topic_id));

					if (!sizeof($topic_info))
					{
						trigger_error('TOPIC_NOT_EXIST');
					}

					if ($forum_id != $topic_info[$topic_id]['forum_id'])
					{
						$topic_id = 0;
					}
					else
					{
						$topic_info = $topic_info[$topic_id];
						$forum_id = (int) $topic_info['forum_id'];
					}
				}

				$forum_list = array();

				if (!$forum_id)
				{
					foreach ($forum_list_reports as $row)
					{
						$forum_list[] = $row['forum_id'];
					}

					if (!sizeof($forum_list))
					{
						trigger_error('NOT_MODERATOR');
					}

					$global_id = $forum_list[0];

					$sql = 'SELECT SUM(forum_topics_approved) as sum_forum_topics
						FROM ' . FORUMS_TABLE . '
						WHERE ' . $db->sql_in_set('forum_id', $forum_list);
					$result = $db->sql_query($sql);
					$forum_info['forum_topics_approved'] = (int) $db->sql_fetchfield('sum_forum_topics');
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
				}

				$forum_list[] = 0;
				$forum_data = array();

				$forum_options = '<option value="0"' . (($forum_id == 0) ? ' selected="selected"' : '') . '>' . $user->lang['ALL_FORUMS'] . '</option>';
				foreach ($forum_list_reports as $row)
				{
					$forum_options .= '<option value="' . $row['forum_id'] . '"' . (($forum_id == $row['forum_id']) ? ' selected="selected"' : '') . '>' . str_repeat('&nbsp; &nbsp;', $row['padding']) . $row['forum_name'] . '</option>';
					$forum_data[$row['forum_id']] = $row;
				}
				unset($forum_list_reports);

				$sort_days = $total = 0;
				$sort_key = $sort_dir = '';
				$sort_by_sql = $sort_order_sql = array();
				mcp_sorting($mode, $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $forum_id, $topic_id);

				$forum_topics = ($total == -1) ? $forum_info['forum_topics_approved'] : $total;
				$limit_time_sql = ($sort_days) ? 'AND r.report_time >= ' . (time() - ($sort_days * 86400)) : '';

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
						' . (($sort_order_sql[0] == 'r') ? 'AND ru.user_id = r.user_id' : '') . '
						' . (($topic_id) ? 'AND p.topic_id = ' . $topic_id : '') . "
						AND t.topic_id = p.topic_id
						AND r.pm_id = 0
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
					$sql = 'SELECT t.forum_id, t.topic_id, t.topic_title, p.post_id, p.post_subject, p.post_username, p.poster_id, p.post_time, p.post_attachment, u.username, u.username_clean, u.user_colour, r.user_id as reporter_id, ru.username as reporter_name, ru.user_colour as reporter_colour, r.report_time, r.report_id
						FROM ' . REPORTS_TABLE . ' r, ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u, ' . USERS_TABLE . ' ru
						WHERE ' . $db->sql_in_set('r.report_id', $report_ids) . '
							AND t.topic_id = p.topic_id
							AND r.post_id = p.post_id
							AND u.user_id = p.poster_id
							AND ru.user_id = r.user_id
							AND r.pm_id = 0
						ORDER BY ' . $sort_order_sql;
					$result = $db->sql_query($sql);

					$report_data = $rowset = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$template->assign_block_vars('postrow', array(
							'U_VIEWFORUM'				=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $row['forum_id']),
							'U_VIEWPOST'				=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $row['forum_id'] . '&amp;p=' . $row['post_id']) . '#p' . $row['post_id'],
							'U_VIEW_DETAILS'			=> append_sid("{$phpbb_root_path}mcp.$phpEx", "i=reports&amp;start=$start&amp;mode=report_details&amp;f={$row['forum_id']}&amp;r={$row['report_id']}"),

							'POST_AUTHOR_FULL'		=> get_username_string('full', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
							'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
							'POST_AUTHOR'			=> get_username_string('username', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
							'U_POST_AUTHOR'			=> get_username_string('profile', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),

							'REPORTER_FULL'			=> get_username_string('full', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
							'REPORTER_COLOUR'		=> get_username_string('colour', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
							'REPORTER'				=> get_username_string('username', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
							'U_REPORTER'			=> get_username_string('profile', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),

							'FORUM_NAME'	=> $forum_data[$row['forum_id']]['forum_name'],
							'POST_ID'		=> $row['post_id'],
							'POST_SUBJECT'	=> ($row['post_subject']) ? $row['post_subject'] : $user->lang['NO_SUBJECT'],
							'POST_TIME'		=> $user->format_date($row['post_time']),
							'REPORT_ID'		=> $row['report_id'],
							'REPORT_TIME'	=> $user->format_date($row['report_time']),
							'TOPIC_TITLE'	=> $row['topic_title'],
							'ATTACH_ICON_IMG'	=> ($auth->acl_get('u_download') && $auth->acl_get('f_download', $row['forum_id']) && $row['post_attachment']) ? $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',
						));
					}
					$db->sql_freeresult($result);
					unset($report_ids, $row);
				}

				$base_url = $this->u_action . "&amp;f=$forum_id&amp;t=$topic_id&amp;st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir";
				phpbb_generate_template_pagination($template, $base_url, 'pagination', 'start', $total, $config['topics_per_page'], $start);

				// Now display the page
				$template->assign_vars(array(
					'L_EXPLAIN'				=> ($mode == 'reports') ? $user->lang['MCP_REPORTS_OPEN_EXPLAIN'] : $user->lang['MCP_REPORTS_CLOSED_EXPLAIN'],
					'L_TITLE'				=> ($mode == 'reports') ? $user->lang['MCP_REPORTS_OPEN'] : $user->lang['MCP_REPORTS_CLOSED'],
					'L_ONLY_TOPIC'			=> ($topic_id) ? sprintf($user->lang['ONLY_TOPIC'], $topic_info['topic_title']) : '',

					'S_MCP_ACTION'			=> $this->u_action,
					'S_FORUM_OPTIONS'		=> $forum_options,
					'S_CLOSED'				=> ($mode == 'reports_closed') ? true : false,

					'PAGE_NUMBER'			=> phpbb_on_page($template, $user, $base_url, $total, $config['topics_per_page'], $start),
					'TOPIC_ID'				=> $topic_id,
					'TOTAL'					=> $total,
					'TOTAL_REPORTS'			=> $user->lang('LIST_REPORTS', (int) $total),
					)
				);

				$this->tpl_name = 'mcp_reports';
			break;
		}
	}
}

/**
* Closes a report
*/
function close_report($report_id_list, $mode, $action, $pm = false)
{
	global $db, $template, $user, $config, $auth;
	global $phpEx, $phpbb_root_path, $phpbb_container;

	$pm_where = ($pm) ? ' AND r.post_id = 0 ' : ' AND r.pm_id = 0 ';
	$id_column = ($pm) ? 'pm_id' : 'post_id';
	$module = ($pm) ? 'pm_reports' : 'reports';
	$pm_prefix = ($pm) ? 'PM_' : '';

	$sql = "SELECT r.$id_column
		FROM " . REPORTS_TABLE . ' r
		WHERE ' . $db->sql_in_set('r.report_id', $report_id_list) . $pm_where;
	$result = $db->sql_query($sql);

	$post_id_list = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$post_id_list[] = $row[$id_column];
	}
	$post_id_list = array_unique($post_id_list);

	if ($pm)
	{
		if (!$auth->acl_getf_global('m_report'))
		{
			trigger_error('NOT_AUTHORISED');
		}
	}
	else
	{
		if (!check_ids($post_id_list, POSTS_TABLE, 'post_id', array('m_report')))
		{
			trigger_error('NOT_AUTHORISED');
		}
	}

	if ($action == 'delete' && strpos($user->data['session_page'], 'mode=report_details') !== false)
	{
		$redirect = request_var('redirect', build_url(array('mode', 'r', 'quickmod')) . '&amp;mode=reports');
	}
	elseif ($action == 'delete' && strpos($user->data['session_page'], 'mode=pm_report_details') !== false)
	{
		$redirect = request_var('redirect', build_url(array('mode', 'r', 'quickmod')) . '&amp;mode=pm_reports');
	}
	else if ($action == 'close' && !request_var('r', 0))
	{
		$redirect = request_var('redirect', build_url(array('mode', 'p', 'quickmod')) . '&amp;mode=' . $module);
	}
	else
	{
		$redirect = request_var('redirect', build_url(array('quickmod')));
	}
	$success_msg = '';
	$forum_ids = array();
	$topic_ids = array();

	$s_hidden_fields = build_hidden_fields(array(
		'i'					=> $module,
		'mode'				=> $mode,
		'report_id_list'	=> $report_id_list,
		'action'			=> $action,
		'redirect'			=> $redirect)
	);

	if (confirm_box(true))
	{
		$post_info = ($pm) ? get_pm_data($post_id_list) : get_post_data($post_id_list, 'm_report');

		$sql = "SELECT r.report_id, r.$id_column, r.report_closed, r.user_id, r.user_notify, u.username, u.username_clean, u.user_email, u.user_jabber, u.user_lang, u.user_notify_type
			FROM " . REPORTS_TABLE . ' r, ' . USERS_TABLE . ' u
			WHERE ' . $db->sql_in_set('r.report_id', $report_id_list) . '
				' . (($action == 'close') ? 'AND r.report_closed = 0' : '') . '
				AND r.user_id = u.user_id' . $pm_where;
		$result = $db->sql_query($sql);

		$reports = $close_report_posts = $close_report_topics = $notify_reporters = $report_id_list = array();
		while ($report = $db->sql_fetchrow($result))
		{
			$reports[$report['report_id']] = $report;
			$report_id_list[] = $report['report_id'];

			if (!$report['report_closed'])
			{
				$close_report_posts[] = $report[$id_column];

				if (!$pm)
				{
					$close_report_topics[] = $post_info[$report['post_id']]['topic_id'];
				}
			}

			if ($report['user_notify'] && !$report['report_closed'])
			{
				$notify_reporters[$report['report_id']] = &$reports[$report['report_id']];
			}
		}
		$db->sql_freeresult($result);

		if (sizeof($reports))
		{
			$close_report_posts = array_unique($close_report_posts);
			$close_report_topics = array_unique($close_report_topics);

			if (!$pm && sizeof($close_report_posts))
			{
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
			}

			$db->sql_transaction('begin');

			if ($action == 'close')
			{
				$sql = 'UPDATE ' . REPORTS_TABLE . '
					SET report_closed = 1
					WHERE ' . $db->sql_in_set('report_id', $report_id_list);
			}
			else
			{
				$sql = 'DELETE FROM ' . REPORTS_TABLE . '
					WHERE ' . $db->sql_in_set('report_id', $report_id_list);
			}
			$db->sql_query($sql);


			if (sizeof($close_report_posts))
			{
				if ($pm)
				{
					$sql = 'UPDATE ' . PRIVMSGS_TABLE . '
						SET message_reported = 0
						WHERE ' . $db->sql_in_set('msg_id', $close_report_posts);
					$db->sql_query($sql);

					if ($action == 'delete')
					{
						delete_pm(ANONYMOUS, $close_report_posts, PRIVMSGS_INBOX);
					}
				}
				else
				{
					$sql = 'UPDATE ' . POSTS_TABLE . '
						SET post_reported = 0
						WHERE ' . $db->sql_in_set('post_id', $close_report_posts);
					$db->sql_query($sql);

					if (sizeof($close_report_topics))
					{
						$sql = 'UPDATE ' . TOPICS_TABLE . '
							SET topic_reported = 0
							WHERE ' . $db->sql_in_set('topic_id', $close_report_topics) . '
								OR ' . $db->sql_in_set('topic_moved_id', $close_report_topics);
						$db->sql_query($sql);
					}
				}
			}

			$db->sql_transaction('commit');
		}
		unset($close_report_posts, $close_report_topics);

		foreach ($reports as $report)
		{
			if ($pm)
			{
				add_log('mod', 0, 0, 'LOG_PM_REPORT_' .  strtoupper($action) . 'D', $post_info[$report['pm_id']]['message_subject']);
			}
			else
			{
				add_log('mod', $post_info[$report['post_id']]['forum_id'], $post_info[$report['post_id']]['topic_id'], 'LOG_REPORT_' .  strtoupper($action) . 'D', $post_info[$report['post_id']]['post_subject']);
			}
		}

		// Notify reporters
		if (sizeof($notify_reporters))
		{
			$phpbb_notifications = $phpbb_container->get('notification_manager');

			foreach ($notify_reporters as $report_id => $reporter)
			{
				if ($reporter['user_id'] == ANONYMOUS)
				{
					continue;
				}

				$post_id = $reporter[$id_column];

				if ($pm)
				{
					$phpbb_notifications->add_notifications('report_pm_closed', array_merge($post_info[$post_id], array(
						'reporter'			=> $reporter['user_id'],
						'closer_id'			=> $user->data['user_id'],
						'from_user_id'		=> $post_info[$post_id]['author_id'],
					)));

					$phpbb_notifications->delete_notifications('report_pm', $post_id);
				}
				else
				{
					$phpbb_notifications->add_notifications('report_post_closed', array_merge($post_info[$post_id], array(
						'reporter'			=> $reporter['user_id'],
						'closer_id'			=> $user->data['user_id'],
					)));

					$phpbb_notifications->delete_notifications('report_post', $post_id);
				}
			}
		}

		if (!$pm)
		{
			foreach ($post_info as $post)
			{
				$forum_ids[$post['forum_id']] = $post['forum_id'];
				$topic_ids[$post['topic_id']] = $post['topic_id'];
			}
		}

		unset($notify_reporters, $post_info, $reports);

		$success_msg = (sizeof($report_id_list) == 1) ? "{$pm_prefix}REPORT_" . strtoupper($action) . 'D_SUCCESS' : "{$pm_prefix}REPORTS_" . strtoupper($action) . 'D_SUCCESS';
	}
	else
	{
		confirm_box(false, $user->lang[strtoupper($action) . "_{$pm_prefix}REPORT" . ((sizeof($report_id_list) == 1) ? '' : 'S') . '_CONFIRM'], $s_hidden_fields);
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

		$return_forum = '';
		$return_topic = '';

		if (!$pm)
		{
			if (sizeof($forum_ids) === 1)
			{
				$return_forum = sprintf($user->lang['RETURN_FORUM'], '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . current($forum_ids)) . '">', '</a>') . '<br /><br />';
			}

			if (sizeof($topic_ids) === 1)
			{
				$return_topic = sprintf($user->lang['RETURN_TOPIC'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . current($topic_ids) . '&amp;f=' . current($forum_ids)) . '">', '</a>') . '<br /><br />';
			}
		}

		trigger_error($user->lang[$success_msg] . '<br /><br />' . $return_forum . $return_topic . sprintf($user->lang['RETURN_PAGE'], "<a href=\"$redirect\">", '</a>'));
	}
}
