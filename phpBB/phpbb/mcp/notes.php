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

class notes
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
	 * @param \phpbb\auth\auth					$auth
	 * @param \phpbb\config\config				$config
	 * @param \phpbb\db\driver\driver_interface	$db
	 * @param \phpbb\language\language			$lang
	 * @param \phpbb\log\log					$log
	 * @param \phpbb\pagination					$pagination
	 * @param \phpbb\request\request			$request
	 * @param \phpbb\template\template			$template
	 * @param \phpbb\user						$user
	 * @param string							$root_path
	 * @param string							$php_ext
	 * @param array								$tables
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
		$root_path,
		$php_ext,
		$tables
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

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	public function main($id, $mode)
	{
		$action = $this->request->variable('action', ['' => '']);
		$action = is_array($action) ? key($action) : $action;

		$this->page_title = 'MCP_NOTES';

		switch ($mode)
		{
			case 'front':
				$this->template->assign_vars([
					'L_TITLE'			=> $this->lang->lang('MCP_NOTES'),

					'U_FIND_USERNAME'	=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=mcp&amp;field=username&amp;select_single=true'),
					'U_POST_ACTION'		=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=notes&amp;mode=user_notes'),
				]);

				$this->tpl_name = 'mcp_notes_front';
			break;

			case 'user_notes':
				$this->lang->add_lang('acp/common');

				$this->mcp_notes_user_view($action);
				$this->tpl_name = 'mcp_notes_user';
			break;
		}
	}

	/**
	 * Display user notes.
	 *
	 * @param string	$action		The action
	 * @return void
	 */
	function mcp_notes_user_view($action)
	{
		$user_id	= $this->request->variable('u', 0);
		$username	= $this->request->variable('username', '', true);
		$start		= $this->request->variable('start', 0);

		$st	= $this->request->variable('st', 0);
		$sk	= $this->request->variable('sk', 'b');
		$sd	= $this->request->variable('sd', 'd');

		$form_key = 'mcp_notes';
		add_form_key($form_key);

		$sql = 'SELECT *
			FROM ' . $this->tables['users'] . '
			WHERE ' . ($user_id ? 'user_id = ' . (int) $user_id : "username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'");
		$result = $this->db->sql_query($sql);
		$user_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($user_row === false)
		{
			trigger_error('NO_USER');
		}

		$user_id = $user_row['user_id'];

		// @todo
		// Populate user id to the currently active module (this module)
		// The following method is another way of adjusting module urls. It is the easy variant if we want
		// to directly adjust the current module url based on data retrieved within the same module.
		if (strpos($this->u_action, "&amp;u=$user_id") === false)
		{
			#$this->p_master->adjust_url('&amp;u=' . $user_id);
			$this->u_action .= "&amp;u=$user_id";
		}

		$delete_mark	= $action === 'del_marked';
		$delete_all		= $action === 'del_all';
		$marked			= $this->request->variable('marknote', [0]);
		$user_note		= $this->request->variable('usernote', '', true);

		// Handle any actions
		if (($delete_mark || $delete_all) && $this->auth->acl_get('a_clearlogs'))
		{
			$where_sql = '';

			if ($delete_mark && $marked)
			{
				$sql_in = [];

				foreach ($marked as $mark)
				{
					$sql_in[] = $mark;
				}
				$where_sql = ' AND ' . $this->db->sql_in_set('log_id', $sql_in);

				unset($sql_in);
			}

			if ($where_sql || $delete_all)
			{
				if (check_form_key($form_key))
				{
					$sql = 'DELETE FROM ' . $this->tables['log'] . '
						WHERE log_type = ' . LOG_USERS . '
							AND reportee_id = ' . (int) $user_id .
							$where_sql;
					$this->db->sql_query($sql);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLEAR_USER', false, [$user_row['username']]);

					$msg = $delete_mark ? 'MARKED_NOTES_DELETED' : 'ALL_NOTES_DELETED';
				}
				else
				{
					$msg = 'FORM_INVALID';
				}

				$redirect = $this->u_action . '&amp;u=' . $user_id;
				meta_refresh(3, $redirect);
				trigger_error($this->lang->lang($msg) . '<br /><br />' . $this->lang->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			}
		}

		if ($user_note && $action === 'add_feedback')
		{
			if (check_form_key($form_key))
			{
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_FEEDBACK', false, [$user_row['username']]);
				$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_FEEDBACK', false, [
					'forum_id' => 0,
					'topic_id' => 0,
					$user_row['username'],
				]);
				$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_GENERAL', false, [
					'reportee_id' => (int) $user_id,
					$user_note,
				]);

				$msg = $this->lang->lang('USER_FEEDBACK_ADDED');
			}
			else
			{
				$msg = $this->lang->lang('FORM_INVALID');
			}

			$redirect = $this->u_action;
			meta_refresh(3, $redirect);
			trigger_error($msg . '<br /><br />' . $this->lang->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
		}

		// Generate the appropriate user information for the user we are looking at
		$rank_title = $rank_img = '';
		$avatar_img = phpbb_get_user_avatar($user_row);

		$limit_days		= [0 => $this->lang->lang('ALL_ENTRIES'), 1 => $this->lang->lang('1_DAY'), 7 => $this->lang->lang('7_DAYS'), 14 => $this->lang->lang('2_WEEKS'), 30 => $this->lang->lang('1_MONTH'), 90 => $this->lang->lang('3_MONTHS'), 180 => $this->lang->lang('6_MONTHS'), 365 => $this->lang->lang('1_YEAR')];
		$sort_by_text	= ['a' => $this->lang->lang('SORT_USERNAME'), 'b' => $this->lang->lang('SORT_DATE'), 'c' => $this->lang->lang('SORT_IP'), 'd' => $this->lang->lang('SORT_ACTION')];
		$sort_by_sql	= ['a' => 'u.username_clean', 'b' => 'l.log_time', 'c' => 'l.log_ip', 'd' => 'l.log_operation'];

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $st, $sk, $sd, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		// Define where and sort sql for use in displaying logs
		$sql_where	= $st ? (time() - ($st * 86400)) : 0;
		$sql_sort	= $sort_by_sql[$sk] . ' ' . ($sd === 'd' ? 'DESC' : 'ASC');

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
					'ACTION'		=> $row['action'],
					'ID'			=> $row['id'],
					'IP'			=> $row['ip'],
					'REPORT_BY'		=> $row['username_full'],
					'REPORT_AT'		=> $this->user->format_date($row['time']),
				]);
			}
		}

		$base_url = $this->u_action . "&amp;$u_sort_param$keywords_param";
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $log_count, $this->config['topics_per_page'], $start);

		$this->template->assign_vars([
			'U_POST_ACTION'			=> $this->u_action,
			'S_CLEAR_ALLOWED'		=> (bool) $this->auth->acl_get('a_clearlogs'),
			'S_SELECT_SORT_DIR'		=> $s_sort_dir,
			'S_SELECT_SORT_KEY'		=> $s_sort_key,
			'S_SELECT_SORT_DAYS'	=> $s_limit_days,
			'S_KEYWORDS'			=> $keywords,

			'L_TITLE'			=> $this->lang->lang('MCP_NOTES_USER'),

			'TOTAL_REPORTS'		=> $this->lang->lang('LIST_REPORTS', (int) $log_count),

			'RANK_TITLE'		=> $rank_title,
			'JOINED'			=> $this->user->format_date($user_row['user_regdate']),
			'POSTS'				=> $user_row['user_posts'] ? $user_row['user_posts'] : 0,
			'WARNINGS'			=> $user_row['user_warnings'] ? $user_row['user_warnings'] : 0,

			'USERNAME_FULL'		=> get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
			'USERNAME_COLOUR'	=> get_username_string('colour', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
			'USERNAME'			=> get_username_string('username', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
			'U_PROFILE'			=> get_username_string('profile', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),

			'AVATAR_IMG'		=> $avatar_img,
			'RANK_IMG'			=> $rank_img,
		]);
	}
}
