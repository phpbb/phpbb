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

use phpbb\exception\http_exception;

class pm_reports
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

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

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth					$auth					Auth object
	 * @param \phpbb\config\config				$config					Config object
	 * @param \phpbb\db\driver\driver_interface	$db						Database object
	 * @param \phpbb\controller\helper			$helper					Controller helper
	 * @param \phpbb\language\language			$language				Language object
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
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
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
		$this->helper				= $helper;
		$this->language				= $language;
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

	public function main($mode, $page = 1)
	{
		include_once($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		include_once($this->root_path . 'includes/functions_privmsgs.' . $this->php_ext);

		$action = $this->request->variable('action', ['' => '']);
		$action = is_array($action) && !empty($action) ? key($action) : $this->request->variable('action', '');

		$limit = (int) $this->config['topics_per_page'];
		$start = ($page - 1) * $limit;

		$route = $route = $mode === 'details' ? 'mcp_pm_report_details' : "mcp_pm_reports_{$mode}";

		switch ($action)
		{
			case 'close':
			case 'delete':
				$report_id_list = $this->request->variable('report_id_list', [0]);

				if (empty($report_id_list))
				{
					$route .= $page > 1 ? '_pagination' : '';
					$return = '<br><br>' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->helper->route($route, ['page' => $page]) . '">&laquo; ', '</a>');

					return trigger_error($this->language->lang('NO_REPORT_SELECTED') . $return, E_USER_WARNING);
				}

				return $this->mcp_reports->close_report($report_id_list, $mode, $action, true);
			break;
		}

		switch ($mode)
		{
			case 'details':
				$this->language->add_lang(['posting', 'viewforum', 'viewtopic', 'ucp']);

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
					throw new http_exception(404, 'NO_REPORT');
				}

				$this->notification_manager->mark_notifications_by_parent('report_pm', $report_id, $this->user->data['user_id']);

				$pm_id = $report['pm_id'];
				$report_id = $report['report_id'];

				$pm_info = phpbb_get_pm_data([$pm_id]);

				if (empty($pm_info))
				{
					throw new http_exception(400, 'REPORT_CLOSED');
				}

				$pm_info = $pm_info[$pm_id];

				write_pm_addresses(['to' => $pm_info['to_address'], 'bcc' => $pm_info['bcc_address']], (int) $pm_info['author_id']);

				$reason = ['title' => $report['reason_title'], 'description' => $report['reason_description']];
				if (isset($this->language->get_lang_array()['report_reasons']['TITLE'][strtoupper($reason['title'])]) && isset($this->language->get_lang_array()['report_reasons']['DESCRIPTION'][strtoupper($reason['title'])]))
				{
					$reason['description'] = $this->language->get_lang_array()['report_reasons']['DESCRIPTION'][strtoupper($reason['title'])];
					$reason['title'] = $this->language->get_lang_array()['report_reasons']['TITLE'][strtoupper($reason['title'])];
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

				$lookup = $this->request->variable('lookup', '');

				$s_info = $this->auth->acl_getf_global('m_info');
				$s_warn = $this->auth->acl_get('m_warn');

				$u_notes_reporter	= $this->helper->route('mcp_notes_user', ['u' => (int) $report['user_id']]);
				$u_notes_user		= $this->helper->route('mcp_notes_user', ['u' => (int) $pm_info['user_id']]);
				$u_report			= $this->helper->route('mcp_pm_report_details', ['r' => $report_id]);
				$u_return			= $this->helper->route(($pm_info['message_reported'] ? 'mcp_pm_reports_open' : 'mcp_pm_reports_closed'));
				$u_warn_reporter	= $this->helper->route('mcp_warn_user', ['u' => (int) $report['user_id']]);
				$u_warn_user		= $this->helper->route('mcp_warn_user', ['u' => (int) $pm_info['user_id']]);

				$this->template->assign_vars([
					'S_MCP_REPORT'			=> true,
					'S_USER_NOTES'			=> true,
					'S_PM'					=> true,
					'S_CAN_VIEWIP'			=> $this->auth->acl_getf_global('m_info'),
					'S_POST_REPORTED'		=> $pm_info['message_reported'],
					'S_REPORT_CLOSED'		=> $report['report_closed'],
					'S_CLOSE_ACTION'		=> $u_report,

					'U_MCP_REPORT'				=> $u_report,
					'U_MCP_REPORTER_NOTES'		=> $u_notes_reporter,
					'U_MCP_USER_NOTES'			=> $u_notes_user,
					'U_MCP_WARN_REPORTER'		=> $s_warn ? $u_warn_reporter : '',
					'U_MCP_WARN_USER'			=> $s_warn ? $u_warn_user : '',

					'EDIT_IMG'					=> $this->user->img('icon_post_edit', $this->language->lang('EDIT_POST')),
					'MINI_POST_IMG'				=> $this->user->img('icon_post_target', 'POST'),

					'RETURN_REPORTS'			=> $this->language->lang('RETURN_REPORTS', '<a href="' . $u_return . '">', '</a>'),
					'REPORTED_IMG'				=> $this->user->img('icon_topic_reported', $this->language->lang('POST_REPORTED')),
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
					'POST_SUBJECT'			=> $pm_info['message_subject'] ? $pm_info['message_subject'] : $this->language->lang('NO_SUBJECT'),
					'POST_DATE'				=> $this->user->format_date($pm_info['message_time']),
					'POST_IP'				=> $pm_info['author_ip'],
					'POST_IPADDR'			=> ($s_info && $lookup) ? @gethostbyaddr($pm_info['author_ip']) : '',
					'POST_ID'				=> $pm_info['msg_id'],

					'U_LOOKUP_IP'			=> $s_info ? $this->helper->route('mcp_pm_report_details', ['r' => $report_id, 'pm' => $pm_id, 'lookup' => $pm_info['author_ip'], '#' => 'ip']) : '',
				]);

				return $this->helper->render('mcp_post.html', $this->language->lang('MCP_PM_REPORT_DETAILS'));
			break;

			case 'open':
			case 'closed':
				$this->language->add_lang(['ucp']);

				$sort_mode = $mode === 'open' ? 'pm_reports': 'pm_reports_closed';
				$sort_days = $total = 0;
				$sort_key = $sort_dir = '';
				$sort_by_sql = $sort_order_sql = [];
				phpbb_mcp_sorting($sort_mode, $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total);

				$limit_time_sql = ($sort_days) ? 'AND r.report_time >= ' . (time() - ($sort_days * 86400)) : '';

				if ($mode === 'open')
				{
					$report_state = 'AND p.message_reported = 1 AND r.report_closed = 0';
				}
				else
				{
					$report_state = 'AND r.report_closed = 1';
				}

				$i = 0;
				$report_ids = [];

				$sql = 'SELECT r.report_id
					FROM ' . $this->tables['privmsgs'] . ' p, 
						' . $this->tables['reports'] . ' r 
						' . ($sort_order_sql[0] === 'u' ? ', ' . $this->tables['users'] . ' u' : '') .
					($sort_order_sql[0] === 'r' ? ', ' . $this->tables['users'] . ' ru' : '') . '
					WHERE r.post_id = 0
						AND r.pm_id = p.msg_id
						' . ($sort_order_sql[0] === 'u' ? 'AND u.user_id = p.author_id' : '') . '
						' . ($sort_order_sql[0] === 'r' ? 'AND ru.user_id = r.user_id' : '') . "
						$report_state
						$limit_time_sql
					ORDER BY $sort_order_sql";
				$result = $this->db->sql_query_limit($sql, $limit, $start);
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
								'U_VIEW_DETAILS'		=> $this->helper->route('mcp_pm_report_details', ['r' => (int) $row['report_id']]),

								'PM_AUTHOR_FULL'		=> get_username_string('full', $row['author_id'], $row['username'], $row['user_colour']),
								'PM_AUTHOR_COLOUR'		=> get_username_string('colour', $row['author_id'], $row['username'], $row['user_colour']),
								'PM_AUTHOR'				=> get_username_string('username', $row['author_id'], $row['username'], $row['user_colour']),
								'U_PM_AUTHOR'			=> get_username_string('profile', $row['author_id'], $row['username'], $row['user_colour']),

								'REPORTER_FULL'			=> get_username_string('full', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
								'REPORTER_COLOUR'		=> get_username_string('colour', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
								'REPORTER'				=> get_username_string('username', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
								'U_REPORTER'			=> get_username_string('profile', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),

								'PM_SUBJECT'			=> $row['message_subject'] ? $row['message_subject'] : $this->language->lang('NO_SUBJECT'),
								'PM_TIME'				=> $this->user->format_date($row['message_time']),
								'REPORT_ID'				=> $row['report_id'],
								'REPORT_TIME'			=> $this->user->format_date($row['report_time']),

								'RECIPIENTS'			=> implode($this->language->lang('COMMA_SEPARATOR'), $address_list[$row['msg_id']]),
								'ATTACH_ICON_IMG'		=> ($this->auth->acl_get('u_download') && $row['message_attachment']) ? $this->user->img('icon_topic_attach', $this->language->lang('TOTAL_ATTACHMENTS')) : '',
							]);
						}
					}
				}

				$this->pagination->generate_template_pagination([
					'routes' => [$route, "{$route}_pagination"],
					'params' => ['sk' => $sort_key, 'sd' => $sort_dir, 'st' => $sort_days],
				], 'pagination', 'page', $total, $limit, $start);

				$l_mode = 'MCP_PM_REPORTS_' . utf8_strtoupper($mode);

				// Now display the page
				$this->template->assign_vars([
					'L_EXPLAIN'		=> $this->language->lang("{$l_mode}_EXPLAIN"),
					'L_TITLE'		=> $this->language->lang($l_mode),

					'S_PM'			=> true,
					'S_MCP_ACTION'	=> $this->helper->route($route),
					'S_CLOSED'		=> $mode === 'closed',

					'TOTAL'			=> $total,
					'TOTAL_REPORTS'	=> $this->language->lang('LIST_REPORTS', (int) $total),
				]);

				return $this->helper->render('mcp_reports.html', $this->language->lang($l_mode));
			break;

			default:
				$return = '<br><br>' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->helper->route('mcp_pm_reports_open') . '">&laquo; ', '</a>');

				return trigger_error($this->language->lang('U_MODE') . $return, E_USER_WARNING);
			break;
		}
	}
}
