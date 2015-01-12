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
* mcp_logs
* Handling warning the users
*/
class mcp_logs
{
	var $u_action;
	var $p_master;

	function mcp_logs(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($id, $mode)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx, $phpbb_container, $phpbb_log;

		$user->add_lang('acp/common');

		$action = request_var('action', array('' => ''));

		if (is_array($action))
		{
			list($action, ) = each($action);
		}
		else
		{
			$action = request_var('action', '');
		}

		// Set up general vars
		$start		= request_var('start', 0);
		$deletemark = ($action == 'del_marked') ? true : false;
		$deleteall	= ($action == 'del_all') ? true : false;
		$marked		= request_var('mark', array(0));

		// Sort keys
		$sort_days	= request_var('st', 0);
		$sort_key	= request_var('sk', 't');
		$sort_dir	= request_var('sd', 'd');

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
				$forum_id = request_var('f', 0);

				if (!in_array($forum_id, $forum_list))
				{
					trigger_error('NOT_AUTHORISED');
				}

				$forum_list = array($forum_id);
			break;

			case 'topic_logs':
				$topic_id = request_var('t', 0);

				$sql = 'SELECT forum_id
					FROM ' . TOPICS_TABLE . '
					WHERE topic_id = ' . $topic_id;
				$result = $db->sql_query($sql);
				$forum_id = (int) $db->sql_fetchfield('forum_id');
				$db->sql_freeresult($result);

				if (!in_array($forum_id, $forum_list))
				{
					trigger_error('NOT_AUTHORISED');
				}

				$forum_list = array($forum_id);
			break;
		}

		// Delete entries if requested and able
		if (($deletemark || $deleteall) && $auth->acl_get('a_clearlogs'))
		{
			if (confirm_box(true))
			{
				if ($deletemark && sizeof($marked))
				{
					$conditions = array(
						'forum_id'	=> array('IN' => $forum_list),
						'log_id'	=> array('IN' => $marked),
					);

					$phpbb_log->delete('mod', $conditions);
				}
				else if ($deleteall)
				{
					$keywords = utf8_normalize_nfc(request_var('keywords', '', true));

					$conditions = array(
						'forum_id'	=> array('IN' => $forum_list),
						'keywords'	=> $keywords,
					);

					if ($sort_days)
					{
						$conditions['log_time'] = array('>=', time() - ($sort_days * 86400));
					}

					if ($mode == 'topic_logs')
					{
						$conditions['topic_id'] = $topic_id;
					}

					$phpbb_log->delete('mod', $conditions);
				}
			}
			else
			{
				confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
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
					'action'	=> request_var('action', array('' => ''))))
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

		$keywords = utf8_normalize_nfc(request_var('keywords', '', true));
		$keywords_param = !empty($keywords) ? '&amp;keywords=' . urlencode(htmlspecialchars_decode($keywords)) : '';

		// Grab log data
		$log_data = array();
		$log_count = 0;
		$start = view_log('mod', $log_data, $log_count, $config['topics_per_page'], $start, $forum_list, $topic_id, 0, $sql_where, $sql_sort, $keywords);

		$base_url = $this->u_action . "&amp;$u_sort_param$keywords_param";
		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $log_count, $config['topics_per_page'], $start);

		$template->assign_vars(array(
			'TOTAL'				=> $user->lang('TOTAL_LOGS', (int) $log_count),

			'L_TITLE'			=> $user->lang['MCP_LOGS'],

			'U_POST_ACTION'			=> $this->u_action . "&amp;$u_sort_param$keywords_param&amp;start=$start",
			'S_CLEAR_ALLOWED'		=> ($auth->acl_get('a_clearlogs')) ? true : false,
			'S_SELECT_SORT_DIR'		=> $s_sort_dir,
			'S_SELECT_SORT_KEY'		=> $s_sort_key,
			'S_SELECT_SORT_DAYS'	=> $s_limit_days,
			'S_LOGS'				=> ($log_count > 0),
			'S_KEYWORDS'			=> $keywords,
			)
		);

		foreach ($log_data as $row)
		{
			$data = array();

			$checks = array('viewtopic', 'viewforum');
			foreach ($checks as $check)
			{
				if (isset($row[$check]) && $row[$check])
				{
					$data[] = '<a href="' . $row[$check] . '">' . $user->lang['LOGVIEW_' . strtoupper($check)] . '</a>';
				}
			}

			$template->assign_block_vars('log', array(
				'USERNAME'		=> $row['username_full'],
				'IP'			=> $row['ip'],
				'DATE'			=> $user->format_date($row['time']),
				'ACTION'		=> $row['action'],
				'DATA'			=> (sizeof($data)) ? implode(' | ', $data) : '',
				'ID'			=> $row['id'],
				)
			);
		}
	}
}
