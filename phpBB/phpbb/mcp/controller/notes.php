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
 * Displays notes about a user
 */
class notes
{
	var $p_master;
	var $u_action;

	function __construct($p_master)
	{
		$this->p_master = $p_master;
	}

	public function main($id, $mode)
	{

		$action = $this->request->variable('action', ['' => '']);

		if (is_array($action))
		{
			$action = key($action);
		}

		$this->page_title = 'MCP_NOTES';

		switch ($mode)
		{
			case 'front':
				$this->template->assign_vars([
					'U_FIND_USERNAME'	=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=mcp&amp;field=username&amp;select_single=true'),
					'U_POST_ACTION'		=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=notes&amp;mode=user_notes'),

					'L_TITLE'			=> $this->language->lang('MCP_NOTES'),
				]);

				$this->tpl_name = 'mcp_notes_front';
			break;

			case 'user_notes':
				$this->language->add_lang('acp/common');

				$this->mcp_notes_user_view($action);
				$this->tpl_name = 'mcp_notes_user';
			break;
		}
	}

	/**
	 * Display user notes
	 */
	function mcp_notes_user_view($action)
	{

		$user_id = $this->request->variable('u', 0);
		$username = $this->request->variable('username', '', true);
		$start = $this->request->variable('start', 0);
		$st	= $this->request->variable('st', 0);
		$sk	= $this->request->variable('sk', 'b');
		$sd	= $this->request->variable('sd', 'd');

		/* @var $pagination \phpbb\pagination */
		$pagination = $phpbb_container->get('pagination');

		add_form_key('mcp_notes');

		$sql_where = ($user_id) ? "user_id = $user_id" : "username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";

		$sql = 'SELECT *
			FROM ' . USERS_TABLE . "
			WHERE $sql_where";
		$result = $this->db->sql_query($sql);
		$userrow = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$userrow)
		{
			trigger_error('NO_USER');
		}

		$user_id = $userrow['user_id'];

		// Populate user id to the currently active module (this module)
		// The following method is another way of adjusting module urls. It is the easy variant if we want
		// to directly adjust the current module url based on data retrieved within the same module.
		if (strpos($this->u_action, "&amp;u=$user_id") === false)
		{
			$this->p_master->adjust_url('&amp;u=' . $user_id);
			$this->u_action .= "&amp;u=$user_id";
		}

		$deletemark = ($action == 'del_marked') ? true : false;
		$deleteall	= ($action == 'del_all') ? true : false;
		$marked		= $this->request->variable('marknote', [0]);
		$usernote	= $this->request->variable('usernote', '', true);

		// Handle any actions
		if (($deletemark || $deleteall) && $this->auth->acl_get('a_clearlogs'))
		{
			$where_sql = '';
			if ($deletemark && $marked)
			{
				$sql_in = [];
				foreach ($marked as $mark)
				{
					$sql_in[] = $mark;
				}
				$where_sql = ' AND ' . $this->db->sql_in_set('log_id', $sql_in);
				unset($sql_in);
			}

			if ($where_sql || $deleteall)
			{
				if (check_form_key('mcp_notes'))
				{
					$sql = 'DELETE FROM ' . LOG_TABLE . '
						WHERE log_type = ' . LOG_USERS . "
							AND reportee_id = $user_id
							$where_sql";
					$this->db->sql_query($sql);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLEAR_USER', false, [$userrow['username']]);

					$msg = ($deletemark) ? 'MARKED_NOTES_DELETED' : 'ALL_NOTES_DELETED';
				}
				else
				{
					$msg = 'FORM_INVALID';
				}
				$redirect = $this->u_action . '&amp;u=' . $user_id;
				meta_refresh(3, $redirect);
				trigger_error($this->language->lang($msg) . '<br /><br />' . sprintf($this->language->lang('RETURN_PAGE'), '<a href="' . $redirect . '">', '</a>'));
			}
		}

		if ($usernote && $action == 'add_feedback')
		{
			if (check_form_key('mcp_notes'))
			{
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_FEEDBACK', false, [$userrow['username']]);
				$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_FEEDBACK', false, [
					'forum_id' => 0,
					'topic_id' => 0,
					$userrow['username']
				]);
				$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_GENERAL', false, [
					'reportee_id' => $user_id,
					$usernote
				]);

				$msg = $this->language->lang('USER_FEEDBACK_ADDED');
			}
			else
			{
				$msg = $this->language->lang('FORM_INVALID');
			}
			$redirect = $this->u_action;
			meta_refresh(3, $redirect);

			trigger_error($msg .  '<br /><br />' . sprintf($this->language->lang('RETURN_PAGE'), '<a href="' . $redirect . '">', '</a>'));
		}

		// Generate the appropriate user information for the user we are looking at

		$rank_title = $rank_img = '';
		$avatar_img = phpbb_get_user_avatar($userrow);

		$limit_days = [0 => $this->language->lang('ALL_ENTRIES'), 1 => $this->language->lang('1_DAY'), 7 => $this->language->lang('7_DAYS'), 14 => $this->language->lang('2_WEEKS'), 30 => $this->language->lang('1_MONTH'), 90 => $this->language->lang('3_MONTHS'), 180 => $this->language->lang('6_MONTHS'), 365 => $this->language->lang('1_YEAR')];
		$sort_by_text = ['a' => $this->language->lang('SORT_USERNAME'), 'b' => $this->language->lang('SORT_DATE'), 'c' => $this->language->lang('SORT_IP'), 'd' => $this->language->lang('SORT_ACTION')];
		$sort_by_sql = ['a' => 'u.username_clean', 'b' => 'l.log_time', 'c' => 'l.log_ip', 'd' => 'l.log_operation'];

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $st, $sk, $sd, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		// Define where and sort sql for use in displaying logs
		$sql_where = ($st) ? (time() - ($st * 86400)) : 0;
		$sql_sort = $sort_by_sql[$sk] . ' ' . (($sd == 'd') ? 'DESC' : 'ASC');

		$keywords = $this->request->variable('keywords', '', true);
		$keywords_param = !empty($keywords) ? '&amp;keywords=' . urlencode(htmlspecialchars_decode($keywords)) : '';

		$log_data = [];
		$log_count = 0;
		$start = view_log('user', $log_data, $log_count, $this->config['topics_per_page'], $start, 0, 0, $user_id, $sql_where, $sql_sort, $keywords);

		if ($log_count)
		{
			$this->template->assign_var('S_USER_NOTES', true);

			foreach ($log_data as $row)
			{
				$this->template->assign_block_vars('usernotes', [
					'REPORT_BY'		=> $row['username_full'],
					'REPORT_AT'		=> $this->user->format_date($row['time']),
					'ACTION'		=> $row['action'],
					'IP'			=> $row['ip'],
					'ID'			=> $row['id']]
				);
			}
		}

		$base_url = $this->u_action . "&amp;$u_sort_param$keywords_param";
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $log_count, $this->config['topics_per_page'], $start);

		$this->template->assign_vars([
			'U_POST_ACTION'			=> $this->u_action,
			'S_CLEAR_ALLOWED'		=> ($this->auth->acl_get('a_clearlogs')) ? true : false,
			'S_SELECT_SORT_DIR'		=> $s_sort_dir,
			'S_SELECT_SORT_KEY'		=> $s_sort_key,
			'S_SELECT_SORT_DAYS'	=> $s_limit_days,
			'S_KEYWORDS'			=> $keywords,

			'L_TITLE'			=> $this->language->lang('MCP_NOTES_USER'),

			'TOTAL_REPORTS'		=> $this->user->lang('LIST_REPORTS', (int) $log_count),

			'RANK_TITLE'		=> $rank_title,
			'JOINED'			=> $this->user->format_date($userrow['user_regdate']),
			'POSTS'				=> ($userrow['user_posts']) ? $userrow['user_posts'] : 0,
			'WARNINGS'			=> ($userrow['user_warnings']) ? $userrow['user_warnings'] : 0,

			'USERNAME_FULL'		=> get_username_string('full', $userrow['user_id'], $userrow['username'], $userrow['user_colour']),
			'USERNAME_COLOUR'	=> get_username_string('colour', $userrow['user_id'], $userrow['username'], $userrow['user_colour']),
			'USERNAME'			=> get_username_string('username', $userrow['user_id'], $userrow['username'], $userrow['user_colour']),
			'U_PROFILE'			=> get_username_string('profile', $userrow['user_id'], $userrow['username'], $userrow['user_colour']),

			'AVATAR_IMG'		=> $avatar_img,
			'RANK_IMG'			=> $rank_img,
			]
		);
	}

}
