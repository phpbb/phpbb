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

namespace phpbb\mcp;

class logs
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

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

	/** @var string phpBB topics table */
	protected $topics_table;

	/** @todo replace */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth					$auth			Auth object
	 * @param \phpbb\config\config				$config			Config object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\language\language			$lang			Language object
	 * @param \phpbb\log\log					$log			Log object
	 * @param \phpbb\pagination					$pagination		Pagination object
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\template\template			$template		Template object
	 * @param \phpbb\user						$user			User object
	 * @param string							$topics_table	phpBB topics table
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$topics_table
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->db			= $db;
		$this->lang			= $lang;
		$this->log			= $log;
		$this->pagination	= $pagination;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->topics_table	= $topics_table;
	}

	function main($id, $mode)
	{
		$this->lang->add_lang('acp/common');

		$this->tpl_name = 'mcp_logs';
		$this->page_title = 'MCP_LOGS';

		$action = $this->request->variable('action', ['' => '']);
		$action = is_array($action) ? key($action) : $this->request->variable('action', '');

		// Set up general vars
		$start			= $this->request->variable('start', 0);
		$marked			= $this->request->variable('mark', [0]);
		$delete_mark	= $action === 'del_marked';
		$delete_all		= $action === 'del_all';

		// Sort keys
		$sort_days	= $this->request->variable('st', 0);
		$sort_dir	= $this->request->variable('sd', 'd');
		$sort_key	= $this->request->variable('sk', 't');

		$forum_list = array_values(array_intersect(get_forum_list('f_read'), get_forum_list('m_')));
		$forum_list[] = 0;

		$forum_id = $topic_id = 0;

		switch ($mode)
		{
			case 'front':
			break;

			case 'forum_logs':
				$forum_id = $this->request->variable('f', 0);

				if (!in_array($forum_id, $forum_list))
				{
					send_status_line(403, 'Forbidden');
					trigger_error('NOT_AUTHORISED');
				}

				$forum_list = [$forum_id];
			break;

			case 'topic_logs':
				$topic_id = $this->request->variable('t', 0);

				$sql = 'SELECT forum_id
					FROM ' . $this->topics_table . '
					WHERE topic_id = ' . $topic_id;
				$result = $this->db->sql_query($sql);
				$forum_id = (int) $this->db->sql_fetchfield('forum_id');
				$this->db->sql_freeresult($result);

				if (!in_array($forum_id, $forum_list))
				{
					send_status_line(403, 'Forbidden');
					trigger_error('NOT_AUTHORISED');
				}

				$forum_list = [$forum_id];
			break;
		}

		// Delete entries if requested and able
		if (($delete_mark || $delete_all) && $this->auth->acl_get('a_clearlogs'))
		{
			if (confirm_box(true))
			{
				if ($delete_mark && !empty($marked))
				{
					$conditions = [
						'forum_id'	=> ['IN' => $forum_list],
						'log_id'	=> ['IN' => $marked],
					];

					$this->log->delete('mod', $conditions);
				}
				else if ($delete_all)
				{
					$keywords = $this->request->variable('keywords', '', true);

					$conditions = [
						'forum_id'	=> ['IN' => $forum_list],
						'keywords'	=> $keywords,
					];

					if ($sort_days)
					{
						$conditions['log_time'] = ['>=', time() - ($sort_days * 86400)];
					}

					if ($mode === 'topic_logs')
					{
						$conditions['topic_id'] = (int) $topic_id;
					}

					$this->log->delete('mod', $conditions);
				}
			}
			else
			{
				confirm_box(false, $this->lang->lang('CONFIRM_OPERATION'), build_hidden_fields([
					'i'			=> $id,
					'mode'		=> $mode,
					'action'	=> $this->request->variable('action', ['' => '']),
					'f'			=> $forum_id,
					't'			=> $topic_id,
					'start'		=> $start,
					'mark'		=> $marked,
					'delmarked'	=> $delete_mark,
					'delall'	=> $delete_all,
					'st'		=> $sort_days,
					'sd'		=> $sort_dir,
					'sk'		=> $sort_key,
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
		$sql_where	= $sort_days ? (time() - ($sort_days * 86400)) : 0;
		$sql_sort	= $sort_by_sql[$sort_key] . ' ' . ($sort_dir === 'd' ? 'DESC' : 'ASC');

		$keywords = $this->request->variable('keywords', '', true);
		$keywords_param = !empty($keywords) ? '&amp;keywords=' . urlencode(htmlspecialchars_decode($keywords)) : '';

		// Grab log data
		$log_data = [];
		$log_count = 0;
		$start = view_log('mod', $log_data, $log_count, $this->config['topics_per_page'], $start, $forum_list, $topic_id, 0, $sql_where, $sql_sort, $keywords);

		$base_url = $this->u_action . "&amp;$u_sort_param$keywords_param";
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $log_count, $this->config['topics_per_page'], $start);

		$this->template->assign_vars([
			'TOTAL'					=> $this->lang->lang('TOTAL_LOGS', (int) $log_count),

			'L_TITLE'				=> $this->lang->lang('MCP_LOGS'),

			'S_CLEAR_ALLOWED'		=> (bool) $this->auth->acl_get('a_clearlogs'),
			'S_SELECT_SORT_DIR'		=> $s_sort_dir,
			'S_SELECT_SORT_KEY'		=> $s_sort_key,
			'S_SELECT_SORT_DAYS'	=> $s_limit_days,
			'S_LOGS'				=> (bool) $log_count > 0,
			'S_KEYWORDS'			=> $keywords,

			'U_POST_ACTION'			=> $this->u_action . "&amp;$u_sort_param$keywords_param&amp;start=$start",
		]);

		foreach ($log_data as $row)
		{
			$data = [];

			foreach (['viewpost', 'viewtopic', 'viewforum'] as $check)
			{
				if (isset($row[$check]) && $row[$check])
				{
					$data[] = '<a href="' . $row[$check] . '">' . $this->lang->lang('LOGVIEW_' . strtoupper($check)) . '</a>';
				}
			}

			$this->template->assign_block_vars('log', [
				'ACTION'		=> $row['action'],
				'DATA'			=> !empty($data) ? implode(' | ', $data) : '',
				'DATE'			=> $this->user->format_date($row['time']),
				'ID'			=> $row['id'],
				'IP'			=> $row['ip'],
				'USERNAME'		=> $row['username_full'],
			]);
		}
	}
}
