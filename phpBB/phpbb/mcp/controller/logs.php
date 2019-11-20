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

/**
 * Handling warning the users
 */
class logs
{
	var $u_action;
	var $p_master;

	function __construct($p_master)
	{
		$this->p_master = $p_master;
	}

	public function main($id, $mode)
	{

		$this->language->add_lang('acp/common');

		$action = $this->request->variable('action', ['' => '']);

		if (is_array($action))
		{
			$action = key($action);
		}
		else
		{
			$action = $this->request->variable('action', '');
		}

		// Set up general vars
		$start		= $this->request->variable('start', 0);
		$deletemark = ($action == 'del_marked') ? true : false;
		$deleteall	= ($action == 'del_all') ? true : false;
		$marked		= $this->request->variable('mark', [0]);

		// Sort keys
		$sort_days	= $this->request->variable('st', 0);
		$sort_key	= $this->request->variable('sk', 't');
		$sort_dir	= $this->request->variable('sd', 'd');

		$this->tpl_name = 'mcp_logs';
		$this->page_title = 'MCP_LOGS';

		/* @var $pagination \phpbb\pagination */
		$pagination = $phpbb_container->get('pagination');

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
					FROM ' . TOPICS_TABLE . '
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
		if (($deletemark || $deleteall) && $this->auth->acl_get('a_clearlogs'))
		{
			if (confirm_box(true))
			{
				if ($deletemark && count($marked))
				{
					$conditions = [
						'forum_id'	=> ['IN' => $forum_list],
						'log_id'	=> ['IN' => $marked],
					];

					$this->log->delete('mod', $conditions);
				}
				else if ($deleteall)
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

					if ($mode == 'topic_logs')
					{
						$conditions['topic_id'] = $topic_id;
					}

					$this->log->delete('mod', $conditions);
				}
			}
			else
			{
				confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
					'f'			=> $forum_id,
					't'			=> $topic_id,
					'start'		=> $start,
					'delmarked'	=> $deletemark,
					'delall'	=> $deleteall,
					'mark'		=> $marked,
					'st'		=> $sort_days,
					'sk'		=> $sort_key,
					'sd'		=> $sort_dir,
					'i'			=> $id,
					'mode'		=> $mode,
					'action'	=> $this->request->variable('action', ['' => ''])])
				);
			}
		}

		// Sorting
		$limit_days = [0 => $this->language->lang('ALL_ENTRIES'), 1 => $this->language->lang('1_DAY'), 7 => $this->language->lang('7_DAYS'), 14 => $this->language->lang('2_WEEKS'), 30 => $this->language->lang('1_MONTH'), 90 => $this->language->lang('3_MONTHS'), 180 => $this->language->lang('6_MONTHS'), 365 => $this->language->lang('1_YEAR')];
		$sort_by_text = ['u' => $this->language->lang('SORT_USERNAME'), 't' => $this->language->lang('SORT_DATE'), 'i' => $this->language->lang('SORT_IP'), 'o' => $this->language->lang('SORT_ACTION')];
		$sort_by_sql = ['u' => 'u.username_clean', 't' => 'l.log_time', 'i' => 'l.log_ip', 'o' => 'l.log_operation'];

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		// Define where and sort sql for use in displaying logs
		$sql_where = ($sort_days) ? (time() - ($sort_days * 86400)) : 0;
		$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

		$keywords = $this->request->variable('keywords', '', true);
		$keywords_param = !empty($keywords) ? '&amp;keywords=' . urlencode(htmlspecialchars_decode($keywords)) : '';

		// Grab log data
		$log_data = [];
		$log_count = 0;
		$start = view_log('mod', $log_data, $log_count, $this->config['topics_per_page'], $start, $forum_list, $topic_id, 0, $sql_where, $sql_sort, $keywords);

		$base_url = $this->u_action . "&amp;$u_sort_param$keywords_param";
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $log_count, $this->config['topics_per_page'], $start);

		$this->template->assign_vars([
			'TOTAL'				=> $this->user->lang('TOTAL_LOGS', (int) $log_count),

			'L_TITLE'			=> $this->language->lang('MCP_LOGS'),

			'U_POST_ACTION'			=> $this->u_action . "&amp;$u_sort_param$keywords_param&amp;start=$start",
			'S_CLEAR_ALLOWED'		=> ($this->auth->acl_get('a_clearlogs')) ? true : false,
			'S_SELECT_SORT_DIR'		=> $s_sort_dir,
			'S_SELECT_SORT_KEY'		=> $s_sort_key,
			'S_SELECT_SORT_DAYS'	=> $s_limit_days,
			'S_LOGS'				=> ($log_count > 0),
			'S_KEYWORDS'			=> $keywords,
			]
		);

		foreach ($log_data as $row)
		{
			$data = [];

			$checks = ['viewpost', 'viewtopic', 'viewforum'];
			foreach ($checks as $check)
			{
				if (isset($row[$check]) && $row[$check])
				{
					$data[] = '<a href="' . $row[$check] . '">' . $this->language->lang('LOGVIEW_' . strtoupper($check)) . '</a>';
				}
			}

			$this->template->assign_block_vars('log', [
				'USERNAME'		=> $row['username_full'],
				'IP'			=> $row['ip'],
				'DATE'			=> $this->user->format_date($row['time']),
				'ACTION'		=> $row['action'],
				'DATA'			=> (count($data)) ? implode(' | ', $data) : '',
				'ID'			=> $row['id'],
				]
			);
		}
	}
}
