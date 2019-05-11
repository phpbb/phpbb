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

namespace phpbb\mcp\controller;

class pm_reports
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\mcp\controller\reports */
	protected $mcp_reports;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth					$auth					Auth object
	 * @param \phpbb\config\config				$config					Config object
	 * @param \phpbb\db\driver\driver_interface	$db						Database object
	 * @param \phpbb\language\language			$lang					Language object
	 * @param reports							$mcp_reports			MCP Report controller object
	 * @param \phpbb\notification\manager		$notification_manager	Notification manager object
	 * @param \phpbb\pagination					$pagination				Pagination object
	 * @param \phpbb\request\request			$request				Request object
	 * @param \phpbb\template\template			$template				Template object
	 * @param \phpbb\user						$user					User object
	 * @param string							$root_path				phpBB root path
	 * @param string							$php_ext				php File extension
	 * @param array								$tables					phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $lang,
		reports $mcp_reports,
		\phpbb\notification\manager $notification_manager,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth					= $auth;
		$this->config				= $config;
		$this->db					= $db;
		$this->lang					= $lang;
		$this->mcp_reports			= $mcp_reports;
		$this->notification_manager	= $notification_manager;
		$this->pagination			= $pagination;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
		$this->tables				= $tables;
	}

	function main($id, $mode)
	{
		/** @todo */
		global $action;

		include_once($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		include_once($this->root_path . 'includes/functions_privmsgs.' . $this->php_ext);

		$start = $this->request->variable('start', 0);

		$this->page_title = 'MCP_PM_REPORTS';

		switch ($action)
		{
			case 'close':
			case 'delete':
				include_once($this->root_path . 'includes/functions_messenger.' . $this->php_ext);

				$report_id_list = $this->request->variable('report_id_list', [0]);

				if (empty($report_id_list))
				{
					trigger_error('NO_REPORT_SELECTED');
				}

				$this->mcp_reports->close_report($report_id_list, $mode, $action, true);
			break;
		}

		switch ($mode)
		{
			case 'pm_report_details':
				$this->lang->add_lang(['posting', 'viewforum', 'viewtopic', 'ucp']);

				$report_id = $this->request->variable('r', 0);

				$sql = 'SELECT r.pm_id, r.user_id, r.report_id, r.report_closed, report_time, r.report_text, rr.reason_title, rr.reason_description, u.username, u.username_clean, u.user_colour
					FROM ' . $this->tables['reports'] . ' r, ' . $this->tables['reports_reasons'] . ' rr, ' . $this->tables['users'] . ' u
					WHERE r.report_id = ' . (int) $report_id . '
						AND rr.reason_id = r.reason_id
						AND r.user_id = u.user_id
						AND r.post_id = 0
					ORDER BY report_closed ASC';
				$result = $this->db->sql_query_limit($sql, 1);
				$report = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$report_id || !$report)
				{
					trigger_error('NO_REPORT');
				}

				$this->notification_manager->mark_notifications_by_parent('report_pm', $report_id, $this->user->data['user_id']);

				$pm_id = $report['pm_id'];
				$report_id = $report['report_id'];

				$pm_info = phpbb_get_pm_data([$pm_id]);

				if (empty($pm_info))
				{
					trigger_error('NO_REPORT_SELECTED');
				}

				$pm_info = $pm_info[$pm_id];

				write_pm_addresses(['to' => $pm_info['to_address'], 'bcc' => $pm_info['bcc_address']], (int) $pm_info['author_id']);

				$reason = ['title' => $report['reason_title'], 'description' => $report['reason_description']];
				if (isset($this->lang->get_lang_array()['report_reasons']['TITLE'][strtoupper($reason['title'])]) && isset($this->lang->get_lang_array()['report_reasons']['DESCRIPTION'][strtoupper($reason['title'])]))
				{
					$reason['description'] = $this->lang->get_lang_array()['report_reasons']['DESCRIPTION'][strtoupper($reason['title'])];
					$reason['title'] = $this->lang->get_lang_array()['report_reasons']['TITLE'][strtoupper($reason['title'])];
				}

				// Process message, leave it uncensored
				$parse_flags = ($pm_info['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
				$message = generate_text_for_display($pm_info['message_text'], $pm_info['bbcode_uid'], $pm_info['bbcode_bitfield'], $parse_flags, false);

				$report['report_text'] = make_clickable(bbcode_nl2br($report['report_text']));

				if ($pm_info['message_attachment'] && $this->auth->acl_get('u_pm_download'))
				{
					$sql = 'SELECT *
						FROM ' . $this->tables['attachments'] . '
						WHERE post_msg_id = ' . (int) $pm_id . '
							AND in_message = 1
						ORDER BY filetime DESC';
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$attachments[] = $row;
					}
					$this->db->sql_freeresult($result);

					if (!empty($attachments))
					{
						$update_count = [];
						parse_attachments(0, $message, $attachments, $update_count);
					}

					// Display not already displayed Attachments for this post, we already parsed them. ;)
					if (!empty($attachments))
					{
						$this->template->assign_var('S_HAS_ATTACHMENTS', true);

						foreach ($attachments as $attachment)
						{
							$this->template->assign_block_vars('attachment', ['DISPLAY_ATTACHMENT' => $attachment]);
						}
					}
				}

				$this->template->assign_vars([
					'S_MCP_REPORT'			=> true,
					'S_USER_NOTES'			=> true,
					'S_PM'					=> true,
					'S_CAN_VIEWIP'			=> $this->auth->acl_getf_global('m_info'),
					'S_POST_REPORTED'		=> $pm_info['message_reported'],
					'S_REPORT_CLOSED'		=> $report['report_closed'],
					'S_CLOSE_ACTION'		=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=pm_reports&amp;mode=pm_report_details&amp;r=' . $report_id),

					'U_MCP_REPORT'				=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=pm_reports&amp;mode=pm_report_details&amp;r=' . $report_id),
					'U_MCP_REPORTER_NOTES'		=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=notes&amp;mode=user_notes&amp;u=' . $report['user_id']),
					'U_MCP_USER_NOTES'			=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=notes&amp;mode=user_notes&amp;u=' . $pm_info['author_id']),
					'U_MCP_WARN_REPORTER'		=> ($this->auth->acl_get('m_warn')) ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=warn&amp;mode=warn_user&amp;u=' . $report['user_id']) : '',
					'U_MCP_WARN_USER'			=> ($this->auth->acl_get('m_warn')) ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=warn&amp;mode=warn_user&amp;u=' . $pm_info['author_id']) : '',

					'EDIT_IMG'					=> $this->user->img('icon_post_edit', $this->lang->lang('EDIT_POST')),
					'MINI_POST_IMG'				=> $this->user->img('icon_post_target', 'POST'),

					'RETURN_REPORTS'			=> $this->lang->lang('RETURN_REPORTS', '<a href="' . append_sid("{$this->root_path}mcp.$this->php_ext", 'i=pm_reports' . (($pm_info['message_reported']) ? '&amp;mode=pm_reports' : '&amp;mode=pm_reports_closed') . '&amp;start=' . $start) . '">', '</a>'),
					'REPORTED_IMG'				=> $this->user->img('icon_topic_reported', $this->lang->lang('POST_REPORTED')),
					'REPORT_DATE'				=> $this->user->format_date($report['report_time']),
					'REPORT_ID'					=> $report_id,
					'REPORT_REASON_TITLE'		=> $reason['title'],
					'REPORT_REASON_DESCRIPTION'	=> $reason['description'],
					'REPORT_TEXT'				=> $report['report_text'],

					'POST_AUTHOR_FULL'			=> get_username_string('full', $pm_info['author_id'], $pm_info['username'], $pm_info['user_colour']),
					'POST_AUTHOR_COLOUR'		=> get_username_string('colour', $pm_info['author_id'], $pm_info['username'], $pm_info['user_colour']),
					'POST_AUTHOR'				=> get_username_string('username', $pm_info['author_id'], $pm_info['username'], $pm_info['user_colour']),
					'U_POST_AUTHOR'				=> get_username_string('profile', $pm_info['author_id'], $pm_info['username'], $pm_info['user_colour']),

					'REPORTER_FULL'				=> get_username_string('full', $report['user_id'], $report['username'], $report['user_colour']),
					'REPORTER_COLOUR'			=> get_username_string('colour', $report['user_id'], $report['username'], $report['user_colour']),
					'REPORTER_NAME'				=> get_username_string('username', $report['user_id'], $report['username'], $report['user_colour']),
					'U_VIEW_REPORTER_PROFILE'	=> get_username_string('profile', $report['user_id'], $report['username'], $report['user_colour']),

					'POST_PREVIEW'			=> $message,
					'POST_SUBJECT'			=> $pm_info['message_subject'] ? $pm_info['message_subject'] : $this->lang->lang('NO_SUBJECT'),
					'POST_DATE'				=> $this->user->format_date($pm_info['message_time']),
					'POST_IP'				=> $pm_info['author_ip'],
					'POST_IPADDR'			=> ($this->auth->acl_getf_global('m_info') && $this->request->variable('lookup', '')) ? @gethostbyaddr($pm_info['author_ip']) : '',
					'POST_ID'				=> $pm_info['msg_id'],

					'U_LOOKUP_IP'			=> $this->auth->acl_getf_global('m_info') ? $this->u_action . '&amp;r=' . $report_id . '&amp;pm=' . $pm_id . '&amp;lookup=' . $pm_info['author_ip'] . '#ip' : '',
				]);

				$this->tpl_name = 'mcp_post';
			break;

			case 'pm_reports':
			case 'pm_reports_closed':
				$this->lang->add_lang(['ucp']);

				$sort_days = $total = 0;
				$sort_key = $sort_dir = '';
				$sort_by_sql = $sort_order_sql = [];
				phpbb_mcp_sorting($mode, $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total);

				$limit_time_sql = ($sort_days) ? 'AND r.report_time >= ' . (time() - ($sort_days * 86400)) : '';

				if ($mode === 'pm_reports')
				{
					$report_state = 'p.message_reported = 1 AND r.report_closed = 0';
				}
				else
				{
					$report_state = 'r.report_closed = 1';
				}

				$i = 0;
				$report_ids = [];

				$sql = 'SELECT r.report_id
					FROM ' . $this->tables['privmsgs'] . ' p, 
						' . $this->tables['reports'] . ' r 
						' . ($sort_order_sql[0] === 'u' ? ', ' . $this->tables['users'] . ' u' : '') .
						($sort_order_sql[0] === 'r' ? ', ' . $this->tables['users'] . ' ru' : '') . "
					WHERE $report_state
						AND r.pm_id = p.msg_id
						" . ($sort_order_sql[0] === 'u' ? 'AND u.user_id = p.author_id' : '') . '
						' . ($sort_order_sql[0] === 'r' ? 'AND ru.user_id = r.user_id' : '') . "
						AND r.post_id = 0
						$limit_time_sql
					ORDER BY $sort_order_sql";
				$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page'], $start);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$report_ids[] = (int) $row['report_id'];
					$row_num[(int) $row['report_id']] = $i++;
				}
				$this->db->sql_freeresult($result);

				if (!empty($report_ids))
				{
					$pm_list = $pm_by_id = [];

					$sql = 'SELECT p.*, u.username, u.username_clean, u.user_colour, r.user_id as reporter_id, ru.username as reporter_name, ru.user_colour as reporter_colour, r.report_time, r.report_id
						FROM ' . $this->tables['reports'] . ' r, ' . $this->tables['privmsgs'] . ' p, ' . $this->tables['users'] . ' u, ' . $this->tables['users'] . ' ru
						WHERE ' . $this->db->sql_in_set('r.report_id', $report_ids) . "
							AND r.pm_id = p.msg_id
							AND p.author_id = u.user_id
							AND ru.user_id = r.user_id
						ORDER BY $sort_order_sql";
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$pm_by_id[(int) $row['msg_id']] = $row;
						$pm_list[] = (int) $row['msg_id'];
					}
					$this->db->sql_freeresult($result);

					if (!empty($pm_list))
					{
						$address_list = get_recipient_strings($pm_by_id);

						foreach ($pm_list as $message_id)
						{
							$row = $pm_by_id[$message_id];
							$this->template->assign_block_vars('postrow', [
								'U_VIEW_DETAILS'			=> append_sid("{$this->root_path}mcp.$this->php_ext", "i=pm_reports&amp;mode=pm_report_details&amp;r={$row['report_id']}"),

								'PM_AUTHOR_FULL'		=> get_username_string('full', $row['author_id'], $row['username'], $row['user_colour']),
								'PM_AUTHOR_COLOUR'		=> get_username_string('colour', $row['author_id'], $row['username'], $row['user_colour']),
								'PM_AUTHOR'				=> get_username_string('username', $row['author_id'], $row['username'], $row['user_colour']),
								'U_PM_AUTHOR'			=> get_username_string('profile', $row['author_id'], $row['username'], $row['user_colour']),

								'REPORTER_FULL'			=> get_username_string('full', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
								'REPORTER_COLOUR'		=> get_username_string('colour', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
								'REPORTER'				=> get_username_string('username', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
								'U_REPORTER'			=> get_username_string('profile', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),

								'PM_SUBJECT'			=> $row['message_subject'] ? $row['message_subject'] : $this->lang->lang('NO_SUBJECT'),
								'PM_TIME'				=> $this->user->format_date($row['message_time']),
								'REPORT_ID'				=> $row['report_id'],
								'REPORT_TIME'			=> $this->user->format_date($row['report_time']),

								'RECIPIENTS'			=> implode($this->lang->lang('COMMA_SEPARATOR'), $address_list[$row['msg_id']]),
								'ATTACH_ICON_IMG'		=> ($this->auth->acl_get('u_download') && $row['message_attachment']) ? $this->user->img('icon_topic_attach', $this->lang->lang('TOTAL_ATTACHMENTS')) : '',
							]);
						}
					}
				}

				$base_url = $this->u_action . "&amp;st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir";
				$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $total, $this->config['topics_per_page'], $start);

				// Now display the page
				$this->template->assign_vars([
					'L_EXPLAIN'				=> $mode === 'pm_reports' ? $this->lang->lang('MCP_PM_REPORTS_OPEN_EXPLAIN') : $this->lang->lang('MCP_PM_REPORTS_CLOSED_EXPLAIN'),
					'L_TITLE'				=> $mode === 'pm_reports' ? $this->lang->lang('MCP_PM_REPORTS_OPEN') : $this->lang->lang('MCP_PM_REPORTS_CLOSED'),

					'S_PM'					=> true,
					'S_MCP_ACTION'			=> $this->u_action,
					'S_CLOSED'				=> $mode === 'pm_reports_closed',

					'TOTAL'					=> $total,
					'TOTAL_REPORTS'			=> $this->lang->lang('LIST_REPORTS', (int) $total),
				]);

				$this->tpl_name = 'mcp_reports';
			break;
		}
	}
}
