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
class mcp_pm_reports
{
	var $p_master;
	var $u_action;

	function mcp_pm_reports(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($id, $mode)
	{
		global $auth, $db, $user, $template, $cache;
		global $config, $phpbb_root_path, $phpEx, $action;

		include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		include_once($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);

		$start = request_var('start', 0);

		$this->page_title = 'MCP_PM_REPORTS';

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

				if (!function_exists('close_report'))
				{
					include($phpbb_root_path . 'includes/mcp/mcp_reports.' . $phpEx);
				}

				close_report($report_id_list, $mode, $action, true);

			break;
		}

		switch ($mode)
		{
			case 'pm_report_details':

				$user->add_lang(array('posting', 'viewforum', 'viewtopic', 'ucp'));

				$report_id = request_var('r', 0);

				$sql = 'SELECT r.pm_id, r.user_id, r.report_id, r.report_closed, report_time, r.report_text, rr.reason_title, rr.reason_description, u.username, u.username_clean, u.user_colour
					FROM ' . REPORTS_TABLE . ' r, ' . REPORTS_REASONS_TABLE . ' rr, ' . USERS_TABLE . ' u
					WHERE r.report_id = ' . $report_id . '
						AND rr.reason_id = r.reason_id
						AND r.user_id = u.user_id
						AND r.post_id = 0
					ORDER BY report_closed ASC';
				$result = $db->sql_query_limit($sql, 1);
				$report = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$report_id || !$report)
				{
					trigger_error('NO_REPORT');
				}

				$pm_id = $report['pm_id'];
				$report_id = $report['report_id'];

				$pm_info = get_pm_data(array($pm_id));

				if (!sizeof($pm_info))
				{
					trigger_error('NO_REPORT_SELECTED');
				}

				$pm_info = $pm_info[$pm_id];

				write_pm_addresses(array('to' => $pm_info['to_address'], 'bcc' => $pm_info['bcc_address']), (int) $pm_info['author_id']);

				$reason = array('title' => $report['reason_title'], 'description' => $report['reason_description']);
				if (isset($user->lang['report_reasons']['TITLE'][strtoupper($reason['title'])]) && isset($user->lang['report_reasons']['DESCRIPTION'][strtoupper($reason['title'])]))
				{
					$reason['description'] = $user->lang['report_reasons']['DESCRIPTION'][strtoupper($reason['title'])];
					$reason['title'] = $user->lang['report_reasons']['TITLE'][strtoupper($reason['title'])];
				}

				// Process message, leave it uncensored
				$message = $pm_info['message_text'];

				if ($pm_info['bbcode_bitfield'])
				{
					include_once($phpbb_root_path . 'includes/bbcode.' . $phpEx);
					$bbcode = new bbcode($pm_info['bbcode_bitfield']);
					$bbcode->bbcode_second_pass($message, $pm_info['bbcode_uid'], $pm_info['bbcode_bitfield']);
				}

				$message = bbcode_nl2br($message);
				$message = smiley_text($message);

				if ($pm_info['message_attachment'] && $auth->acl_get('u_pm_download'))
				{
					$sql = 'SELECT *
						FROM ' . ATTACHMENTS_TABLE . '
						WHERE post_msg_id = ' . $pm_id . '
							AND in_message = 1
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
						parse_attachments(0, $message, $attachments, $update_count);
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
					'S_PM'					=> true,
					'S_CLOSE_ACTION'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=pm_reports&amp;mode=pm_report_details&amp;r=' . $report_id),
					'S_CAN_VIEWIP'			=> $auth->acl_getf_global('m_info'),
					'S_POST_REPORTED'		=> $pm_info['message_reported'],
					'S_USER_NOTES'			=> true,

					'U_MCP_REPORT'				=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=pm_reports&amp;mode=pm_report_details&amp;r=' . $report_id),
					'U_MCP_REPORTER_NOTES'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $report['user_id']),
					'U_MCP_USER_NOTES'			=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $pm_info['author_id']),
					'U_MCP_WARN_REPORTER'		=> ($auth->acl_get('m_warn')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=warn&amp;mode=warn_user&amp;u=' . $report['user_id']) : '',
					'U_MCP_WARN_USER'			=> ($auth->acl_get('m_warn')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=warn&amp;mode=warn_user&amp;u=' . $pm_info['author_id']) : '',
					
					'EDIT_IMG'				=> $user->img('icon_post_edit', $user->lang['EDIT_POST']),
					'MINI_POST_IMG'			=> $user->img('icon_post_target', 'POST'),

					'RETURN_REPORTS'			=> sprintf($user->lang['RETURN_REPORTS'], '<a href="' . append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=pm_reports' . (($pm_info['message_reported']) ? '&amp;mode=pm_reports' : '&amp;mode=pm_reports_closed') . '&amp;start=' . $start) . '">', '</a>'),
					'REPORTED_IMG'				=> $user->img('icon_topic_reported', $user->lang['POST_REPORTED']),
					'REPORT_DATE'				=> $user->format_date($report['report_time']),
					'REPORT_ID'					=> $report_id,
					'REPORT_REASON_TITLE'		=> $reason['title'],
					'REPORT_REASON_DESCRIPTION'	=> $reason['description'],
					'REPORT_TEXT'				=> $report['report_text'],

					'POST_AUTHOR_FULL'		=> get_username_string('full', $pm_info['author_id'], $pm_info['username'], $pm_info['user_colour']),
					'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $pm_info['author_id'], $pm_info['username'], $pm_info['user_colour']),
					'POST_AUTHOR'			=> get_username_string('username', $pm_info['author_id'], $pm_info['username'], $pm_info['user_colour']),
					'U_POST_AUTHOR'			=> get_username_string('profile', $pm_info['author_id'], $pm_info['username'], $pm_info['user_colour']),

					'REPORTER_FULL'				=> get_username_string('full', $report['user_id'], $report['username'], $report['user_colour']),
					'REPORTER_COLOUR'			=> get_username_string('colour', $report['user_id'], $report['username'], $report['user_colour']),
					'REPORTER_NAME'				=> get_username_string('username', $report['user_id'], $report['username'], $report['user_colour']),
					'U_VIEW_REPORTER_PROFILE'	=> get_username_string('profile', $report['user_id'], $report['username'], $report['user_colour']),

					'POST_PREVIEW'			=> $message,
					'POST_SUBJECT'			=> ($pm_info['message_subject']) ? $pm_info['message_subject'] : $user->lang['NO_SUBJECT'],
					'POST_DATE'				=> $user->format_date($pm_info['message_time']),
					'POST_IP'				=> $pm_info['author_ip'],
					'POST_IPADDR'			=> ($auth->acl_getf_global('m_info') && request_var('lookup', '')) ? @gethostbyaddr($pm_info['author_ip']) : '',
					'POST_ID'				=> $pm_info['msg_id'],

					'U_LOOKUP_IP'			=> ($auth->acl_getf_global('m_info')) ? $this->u_action . '&amp;r=' . $report_id . '&amp;pm=' . $pm_id . '&amp;lookup=' . $pm_info['author_ip'] . '#ip' : '',
				));

				$this->tpl_name = 'mcp_post';

			break;

			case 'pm_reports':
			case 'pm_reports_closed':
				$user->add_lang(array('ucp'));

				$sort_days = $total = 0;
				$sort_key = $sort_dir = '';
				$sort_by_sql = $sort_order_sql = array();
				mcp_sorting($mode, $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total);

				$limit_time_sql = ($sort_days) ? 'AND r.report_time >= ' . (time() - ($sort_days * 86400)) : '';

				if ($mode == 'pm_reports')
				{
					$report_state = 'p.message_reported = 1 AND r.report_closed = 0';
				}
				else
				{
					$report_state = 'r.report_closed = 1';
				}

				$sql = 'SELECT r.report_id
					FROM ' . PRIVMSGS_TABLE . ' p, ' . REPORTS_TABLE . ' r ' . (($sort_order_sql[0] == 'u') ? ', ' . USERS_TABLE . ' u' : '') . (($sort_order_sql[0] == 'r') ? ', ' . USERS_TABLE . ' ru' : '') . "
					WHERE $report_state
						AND r.pm_id = p.msg_id
						" . (($sort_order_sql[0] == 'u') ? 'AND u.user_id = p.author_id' : '') . '
						' . (($sort_order_sql[0] == 'r') ? 'AND ru.user_id = r.user_id' : '') . "
						AND r.post_id = 0
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
					$sql = 'SELECT p.*, u.username, u.username_clean, u.user_colour, r.user_id as reporter_id, ru.username as reporter_name, ru.user_colour as reporter_colour, r.report_time, r.report_id
						FROM ' . REPORTS_TABLE . ' r, ' . PRIVMSGS_TABLE . ' p, ' . USERS_TABLE . ' u, ' . USERS_TABLE . ' ru
						WHERE ' . $db->sql_in_set('r.report_id', $report_ids) . "
							AND r.pm_id = p.msg_id
							AND p.author_id = u.user_id
							AND ru.user_id = r.user_id
						ORDER BY $sort_order_sql";
					$result = $db->sql_query($sql);

					$pm_list = $pm_by_id = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$pm_by_id[(int) $row['msg_id']] = $row;
						$pm_list[] = (int) $row['msg_id'];
					}
					$db->sql_freeresult($result);

					if (sizeof($pm_list))
					{
						$address_list = get_recipient_strings($pm_by_id);

						foreach ($pm_list as $message_id)
						{
							$row = $pm_by_id[$message_id];
							$template->assign_block_vars('postrow', array(
								'U_VIEW_DETAILS'			=> append_sid("{$phpbb_root_path}mcp.$phpEx", "i=pm_reports&amp;mode=pm_report_details&amp;r={$row['report_id']}"),

								'PM_AUTHOR_FULL'		=> get_username_string('full', $row['author_id'], $row['username'], $row['user_colour']),
								'PM_AUTHOR_COLOUR'		=> get_username_string('colour', $row['author_id'], $row['username'], $row['user_colour']),
								'PM_AUTHOR'				=> get_username_string('username', $row['author_id'], $row['username'], $row['user_colour']),
								'U_PM_AUTHOR'			=> get_username_string('profile', $row['author_id'], $row['username'], $row['user_colour']),

								'REPORTER_FULL'			=> get_username_string('full', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
								'REPORTER_COLOUR'		=> get_username_string('colour', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
								'REPORTER'				=> get_username_string('username', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
								'U_REPORTER'			=> get_username_string('profile', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),

								'PM_SUBJECT'			=> ($row['message_subject']) ? $row['message_subject'] : $user->lang['NO_SUBJECT'],
								'PM_TIME'				=> $user->format_date($row['message_time']),
								'REPORT_ID'				=> $row['report_id'],
								'REPORT_TIME'			=> $user->format_date($row['report_time']),

								'RECIPIENTS'			=> implode(', ', $address_list[$row['msg_id']]),
							));
						}
					}
				}

				// Now display the page
				$template->assign_vars(array(
					'L_EXPLAIN'				=> ($mode == 'pm_reports') ? $user->lang['MCP_PM_REPORTS_OPEN_EXPLAIN'] : $user->lang['MCP_PM_REPORTS_CLOSED_EXPLAIN'],
					'L_TITLE'				=> ($mode == 'pm_reports') ? $user->lang['MCP_PM_REPORTS_OPEN'] : $user->lang['MCP_PM_REPORTS_CLOSED'],
					
					'S_PM'					=> true,
					'S_MCP_ACTION'			=> $this->u_action,
					'S_CLOSED'				=> ($mode == 'pm_reports_closed') ? true : false,

					'PAGINATION'			=> generate_pagination($this->u_action . "&amp;st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir", $total, $config['topics_per_page'], $start),
					'PAGE_NUMBER'			=> on_page($total, $config['topics_per_page'], $start),
					'TOTAL'					=> $total,
					'TOTAL_REPORTS'			=> ($total == 1) ? $user->lang['LIST_REPORT'] : sprintf($user->lang['LIST_REPORTS'], $total),					
					)
				);

				$this->tpl_name = 'mcp_reports';
			break;
		}
	}
}

?>