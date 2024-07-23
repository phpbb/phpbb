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

class acp_logs
{
	var $u_action;

	function main($id, $mode)
	{
		global $user, $auth, $template, $phpbb_container;
		global $config;
		global $request;

		$user->add_lang('mcp');

		// Set up general vars
		$action		= $request->variable('action', '');
		$forum_id	= $request->variable('f', 0);
		$start		= $request->variable('start', 0);
		$deletemark = $request->variable('delmarked', false, false, \phpbb\request\request_interface::POST);
		$deleteall	= $request->variable('delall', false, false, \phpbb\request\request_interface::POST);
		$marked		= $request->variable('mark', array(0));

		// Sort keys
		$sort_days	= $request->variable('st', 0);
		$sort_key	= $request->variable('sk', 't');
		$sort_dir	= $request->variable('sd', 'd');

		$this->tpl_name = 'acp_logs';
		$this->log_type = constant('LOG_' . strtoupper($mode));

		/* @var $pagination \phpbb\pagination */
		$pagination = $phpbb_container->get('pagination');

		// Delete entries if requested and able
		if (($deleteall || ($deletemark && count($marked))) && $auth->acl_get('a_clearlogs'))
		{
			if (confirm_box(true))
			{
				$conditions = array();

				if ($deletemark && count($marked))
				{
					$conditions['log_id'] = array('IN' => $marked);
				}

				if ($deleteall)
				{
					if ($sort_days)
					{
						$conditions['log_time'] = array('>=', time() - ($sort_days * 86400));
					}

					$keywords = $request->variable('keywords', '', true);
					$conditions['keywords'] = $keywords;
				}

				/* @var $phpbb_log \phpbb\log\log_interface */
				$phpbb_log = $phpbb_container->get('log');
				$phpbb_log->delete($mode, $conditions);
			}
			else
			{
				confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
					'f'			=> $forum_id,
					'start'		=> $start,
					'delmarked'	=> $deletemark,
					'delall'	=> $deleteall,
					'mark'		=> $marked,
					'st'		=> $sort_days,
					'sk'		=> $sort_key,
					'sd'		=> $sort_dir,
					'i'			=> $id,
					'mode'		=> $mode,
					'action'	=> $action))
				);
			}
		}

		// Sorting
		$limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
		$sort_by_text = array('u' => $user->lang['SORT_USERNAME'], 't' => $user->lang['SORT_DATE'], 'i' => $user->lang['SORT_IP'], 'o' => $user->lang['SORT_ACTION']);
		$sort_by_sql = array('u' => 'u.username_clean', 't' => 'l.log_time', 'i' => 'l.log_ip', 'o' => 'l.log_operation');

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		// Define where and sort sql for use in displaying logs
		$sql_where = ($sort_days) ? (time() - ($sort_days * 86400)) : 0;
		$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

		$keywords = $request->variable('keywords', '', true);
		$keywords_param = !empty($keywords) ? '&amp;keywords=' . urlencode(html_entity_decode($keywords, ENT_COMPAT)) : '';

		$l_title = $user->lang['ACP_' . strtoupper($mode) . '_LOGS'];
		$l_title_explain = $user->lang['ACP_' . strtoupper($mode) . '_LOGS_EXPLAIN'];

		$this->page_title = $l_title;

		// Define forum list if we're looking @ mod logs
		if ($mode == 'mod')
		{
			$forum_box = '<option value="0">' . $user->lang['ALL_FORUMS'] . '</option>' . make_forum_select($forum_id);

			$template->assign_vars(array(
				'S_SHOW_FORUMS'			=> true,
				'S_FORUM_BOX'			=> $forum_box)
			);
		}

		// Grab log data
		$log_data = array();
		$log_count = 0;
		$start = view_log($mode, $log_data, $log_count, $config['topics_per_page'], $start, $forum_id, 0, 0, $sql_where, $sql_sort, $keywords);

		$base_url = $this->u_action . "&amp;$u_sort_param$keywords_param";
		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $log_count, $config['topics_per_page'], $start);

		$template->assign_vars(array(
			'L_TITLE'		=> $l_title,
			'L_EXPLAIN'		=> $l_title_explain,
			'U_ACTION'		=> $this->u_action . "&amp;$u_sort_param$keywords_param&amp;start=$start",

			'S_LIMIT_DAYS'	=> $s_limit_days,
			'S_SORT_KEY'	=> $s_sort_key,
			'S_SORT_DIR'	=> $s_sort_dir,
			'S_CLEARLOGS'	=> $auth->acl_get('a_clearlogs'),
			'S_KEYWORDS'	=> $keywords,
			)
		);

		foreach ($log_data as $row)
		{
			$data = array();

			$checks = array('viewpost', 'viewtopic', 'viewlogs', 'viewforum');
			foreach ($checks as $check)
			{
				if (isset($row[$check]) && $row[$check])
				{
					$data[] = '<a href="' . $row[$check] . '">' . $user->lang['LOGVIEW_' . strtoupper($check)] . '</a>';
				}
			}

			$template->assign_block_vars('log', array(
				'USERNAME'			=> $row['username_full'],
				'REPORTEE_USERNAME'	=> ($row['reportee_username'] && $row['user_id'] != $row['reportee_id']) ? $row['reportee_username_full'] : '',

				'IP'				=> $row['ip'],
				'DATE'				=> $user->format_date($row['time']),
				'ACTION'			=> $row['action'],
				'DATA'				=> (count($data)) ? implode(' | ', $data) : '',
				'ID'				=> $row['id'],
				)
			);
		}
	}
}
