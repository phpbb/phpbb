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

namespace phpbb\acp;

class logs
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;
	public $log_type;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth			$auth			Auth object
	 * @param \phpbb\config\config		$config			Config object
	 * @param \phpbb\language\language	$lang			Language object
	 * @param \phpbb\log\log			$log			Log object
	 * @param \phpbb\pagination			$pagination		Pagination object
	 * @param \phpbb\request\request	$request		Request object
	 * @param \phpbb\template\template	$template		Template object
	 * @param \phpbb\user				$user			User object
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->lang			= $lang;
		$this->log			= $log;
		$this->pagination	= $pagination;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
	}

	function main($id, $mode)
	{
		$this->lang->add_lang('mcp');

		// Set up general vars
		$action		= $this->request->variable('action', '');
		$forum_id	= $this->request->variable('f', 0);
		$start		= $this->request->variable('start', 0);
		$marked		= $this->request->variable('mark', [0]);
		$delete_all	= $this->request->is_set_post('delall');
		$delete_mark = $this->request->is_set_post('delmarked');

		// Sort keys
		$sort_days	= $this->request->variable('st', 0);
		$sort_key	= $this->request->variable('sk', 't');
		$sort_dir	= $this->request->variable('sd', 'd');

		/** @todo is this even used? */
		$this->log_type = constant('LOG_' . strtoupper($mode));

		// Delete entries if requested and able
		if (($delete_mark || $delete_all) && $this->auth->acl_get('a_clearlogs'))
		{
			if (confirm_box(true))
			{
				$conditions = [];

				if ($delete_mark && !empty($marked))
				{
					$conditions['log_id'] = ['IN' => $marked];
				}

				if ($delete_all)
				{
					if ($sort_days)
					{
						$conditions['log_time'] = ['>=', time() - ($sort_days * 86400)];
					}

					$keywords = $this->request->variable('keywords', '', true);
					$conditions['keywords'] = $keywords;
				}

				$this->log->delete($mode, $conditions);
			}
			else
			{
				confirm_box(false, $this->lang->lang('CONFIRM_OPERATION'), build_hidden_fields([
					'i'			=> $id,
					'mode'		=> $mode,
					'delmarked'	=> $delete_mark,
					'delall'	=> $delete_all,
					'action'	=> $action,
					'start'		=> $start,
					'mark'		=> $marked,
					'st'		=> $sort_days,
					'sk'		=> $sort_key,
					'sd'		=> $sort_dir,
					'f'			=> $forum_id,
				]));
			}
		}

		// Sorting
		$limit_days = [0 => $this->lang->lang('ALL_ENTRIES'), 1 => $this->lang->lang('1_DAY'), 7 => $this->lang->lang('7_DAYS'), 14 => $this->lang->lang('2_WEEKS'), 30 => $this->lang->lang('1_MONTH'), 90 => $this->lang->lang('3_MONTHS'), 180 => $this->lang->lang('6_MONTHS'), 365 => $this->lang->lang('1_YEAR')];
		$sort_by_text = ['u' => $this->lang->lang('SORT_USERNAME'), 't' => $this->lang->lang('SORT_DATE'), 'i' => $this->lang->lang('SORT_IP'), 'o' => $this->lang->lang('SORT_ACTION')];
		$sort_by_sql = ['u' => 'u.username_clean', 't' => 'l.log_time', 'i' => 'l.log_ip', 'o' => 'l.log_operation'];

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		// Define where and sort sql for use in displaying logs
		$sql_where = $sort_days ? (time() - ($sort_days * 86400)) : 0;
		$sql_sort = $sort_by_sql[$sort_key] . ' ' . ($sort_dir === 'd' ? 'DESC' : 'ASC');

		$keywords = $this->request->variable('keywords', '', true);
		$keywords_param = !empty($keywords) ? '&amp;keywords=' . urlencode(htmlspecialchars_decode($keywords)) : '';

		$l_title = $this->lang->lang('ACP_' . strtoupper($mode) . '_LOGS');
		$l_explain = $this->lang->lang('ACP_' . strtoupper($mode) . '_LOGS_EXPLAIN');

		// Define forum list if we're looking @ mod logs
		if ($mode === 'mod')
		{
			$forum_box = '<option value="0">' . $this->lang->lang('ALL_FORUMS') . '</option>' . make_forum_select($forum_id);

			$this->template->assign_vars([
				'S_SHOW_FORUMS'			=> true,
				'S_FORUM_BOX'			=> $forum_box,
			]);
		}

		// Grab log data
		$log_data = [];
		$log_count = 0;
		$start = view_log($mode, $log_data, $log_count, $this->config['topics_per_page'], $start, $forum_id, 0, 0, $sql_where, $sql_sort, $keywords);

		$base_url = $this->u_action . "&amp;$u_sort_param$keywords_param";
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $log_count, $this->config['topics_per_page'], $start);

		$this->template->assign_vars([
			'L_TITLE'		=> $l_title,
			'L_EXPLAIN'		=> $l_explain,
			'U_ACTION'		=> $this->u_action . "&amp;$u_sort_param$keywords_param&amp;start=$start",

			'S_LIMIT_DAYS'	=> $s_limit_days,
			'S_SORT_KEY'	=> $s_sort_key,
			'S_SORT_DIR'	=> $s_sort_dir,
			'S_CLEARLOGS'	=> $this->auth->acl_get('a_clearlogs'),
			'S_KEYWORDS'	=> $keywords,
		]);

		foreach ($log_data as $row)
		{
			$data = [];
			$checks = ['viewpost', 'viewtopic', 'viewlogs', 'viewforum'];

			foreach ($checks as $check)
			{
				if (isset($row[$check]) && $row[$check])
				{
					$data[] = '<a href="' . $row[$check] . '">' . $this->lang->lang('LOGVIEW_' . strtoupper($check)) . '</a>';
				}
			}

			$this->template->assign_block_vars('log', [
				'USERNAME'			=> $row['username_full'],
				'REPORTEE_USERNAME'	=> ($row['reportee_username'] && $row['user_id'] != $row['reportee_id']) ? $row['reportee_username_full'] : '',

				'IP'				=> $row['ip'],
				'DATE'				=> $this->user->format_date($row['time']),
				'ACTION'			=> $row['action'],
				'DATA'				=> !empty($data) ? implode(' | ', $data) : '',
				'ID'				=> $row['id'],
			]);
		}

		$this->page_title = $l_title;
		$this->tpl_name = 'acp_logs';
	}
}
