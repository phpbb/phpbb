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

namespace phpbb\acp\controller;

class inactive
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

	/** @var string phpBB admin path */
	protected $admin_path;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var string phpBB users table */
	protected $users_table;

	/** @todo */
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
	 * @param string							$admin_path		phpBB admin path
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param string							$users_table	phpBB users table
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
		$admin_path,
		$root_path,
		$php_ext,
		$users_table
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

		$this->admin_path	= $admin_path;
		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->users_table	= $users_table;
	}

	function main($id, $mode)
	{
		$this->lang->add_lang('memberlist');

		if (!function_exists('user_active_flip'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->php_ext);
		}

		$form_key = 'acp_inactive';
		add_form_key($form_key);

		$action = $this->request->variable('action', '');
		$submit = $this->request->is_set_post('submit');
		$start	= $this->request->variable('start', 0);
		$mark	= $this->request->is_set_post('mark') ? $this->request->variable('mark', [0]) : [];

		// Sort keys
		$sort_days	= $this->request->variable('st', 0);
		$sort_key	= $this->request->variable('sk', 'i');
		$sort_dir	= $this->request->variable('sd', 'd');

		// We build the sort key and per page settings here, because they may be needed later

		// Number of entries to display
		$per_page = $this->request->variable('users_per_page', (int) $this->config['topics_per_page']);

		// Sorting
		$limit_days = [0 => $this->lang->lang('ALL_ENTRIES'), 1 => $this->lang->lang('1_DAY'), 7 => $this->lang->lang('7_DAYS'), 14 => $this->lang->lang('2_WEEKS'), 30 => $this->lang->lang('1_MONTH'), 90 => $this->lang->lang('3_MONTHS'), 180 => $this->lang->lang('6_MONTHS'), 365 => $this->lang->lang('1_YEAR')];
		$sort_by_text = ['i' => $this->lang->lang('SORT_INACTIVE'), 'j' => $this->lang->lang('SORT_REG_DATE'), 'l' => $this->lang->lang('SORT_LAST_VISIT'), 'd' => $this->lang->lang('SORT_LAST_REMINDER'), 'r' => $this->lang->lang('SORT_REASON'), 'u' => $this->lang->lang('SORT_USERNAME'), 'p' => $this->lang->lang('SORT_POSTS'), 'e' => $this->lang->lang('SORT_REMINDER')];
		$sort_by_sql = ['i' => 'user_inactive_time', 'j' => 'user_regdate', 'l' => 'user_lastvisit', 'd' => 'user_reminded_time', 'r' => 'user_inactive_reason', 'u' => 'username_clean', 'p' => 'user_posts', 'e' => 'user_reminded'];

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		if ($submit && !empty($mark))
		{
			if ($action !== 'delete' && !check_form_key($form_key))
			{
				trigger_error($this->lang->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			switch ($action)
			{
				case 'activate':
				case 'delete':
					$user_affected = [];

					$sql = 'SELECT user_id, username
						FROM ' . $this->users_table . '
						WHERE ' . $this->db->sql_in_set('user_id', $mark);
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$user_affected[$row['user_id']] = $row['username'];
					}
					$this->db->sql_freeresult($result);

					if ($action === 'activate')
					{
						$inactive_users = [];

						// Get those 'being activated'...
						$sql = 'SELECT user_id, username' . (($this->config['require_activation'] == USER_ACTIVATION_ADMIN) ? ', user_email, user_lang' : '') . '
							FROM ' . $this->users_table . '
							WHERE ' . $this->db->sql_in_set('user_id', $mark) . '
								AND user_type = ' . USER_INACTIVE;
						$result = $this->db->sql_query($sql);
						while ($row = $this->db->sql_fetchrow($result))
						{
							$inactive_users[] = $row;
						}
						$this->db->sql_freeresult($result);

						user_active_flip('activate', $mark);

						if ($this->config['require_activation'] == USER_ACTIVATION_ADMIN && !empty($inactive_users))
						{
							if (!class_exists('messenger'))
							{
								include($this->root_path . 'includes/functions_messenger.' . $this->php_ext);
							}

							$messenger = new \messenger(false);

							foreach ($inactive_users as $row)
							{
								$messenger->template('admin_welcome_activated', $row['user_lang']);

								$messenger->set_addresses($row);

								$messenger->anti_abuse_headers($this->config, $this->user);

								$messenger->assign_vars([
									'USERNAME'	=> htmlspecialchars_decode($row['username']),
								]);

								$messenger->send(NOTIFY_EMAIL);
							}

							$messenger->save_queue();
						}

						if (!empty($inactive_users))
						{
							foreach ($inactive_users as $row)
							{
								$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_ACTIVE', false, [$row['username']]);
								$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_ACTIVE_USER', false, [
									'reportee_id' => $row['user_id'],
								]);
							}

							trigger_error($this->lang->lang('LOG_INACTIVE_ACTIVATE', implode($this->lang->lang('COMMA_SEPARATOR'), $user_affected)) . adm_back_link($this->u_action));
						}

						// For activate we really need to redirect, else a refresh can result in users being deactivated again
						$u_action = $this->u_action . "&amp;$u_sort_param&amp;start=$start";
						$u_action .= ($per_page != $this->config['topics_per_page']) ? "&amp;users_per_page=$per_page" : '';

						redirect($u_action);
					}
					else if ($action === 'delete')
					{
						if (confirm_box(true))
						{
							if (!$this->auth->acl_get('a_userdel'))
							{
								send_status_line(403, 'Forbidden');
								trigger_error($this->lang->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action), E_USER_WARNING);
							}

							user_delete('retain', $mark, true);

							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_INACTIVE_' . strtoupper($action), false, [implode($this->lang->lang('COMMA_SEPARATOR'), $user_affected)]);

							trigger_error($this->lang->lang('LOG_INACTIVE_DELETE', implode($this->lang->lang('COMMA_SEPARATOR'), $user_affected)) . adm_back_link($this->u_action));
						}
						else
						{
							confirm_box(false, $this->lang->lang('CONFIRM_OPERATION'), build_hidden_fields([
								'mode'			=> $mode,
								'action'		=> $action,
								'start'			=> $start,
								'mark'			=> $mark,
								'submit'		=> true,
							]));
						}
					}

				break;

				case 'remind':
					if (empty($this->config['email_enable']))
					{
						trigger_error($this->lang->lang('EMAIL_DISABLED') . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type, user_regdate, user_actkey
						FROM ' . $this->users_table . '
						WHERE ' . $this->db->sql_in_set('user_id', $mark) . '
							AND user_inactive_reason';

					$sql .= ($this->config['require_activation'] == USER_ACTIVATION_ADMIN) ? ' = ' . INACTIVE_REMIND : ' <> ' . INACTIVE_MANUAL;

					$result = $this->db->sql_query($sql);

					if ($row = $this->db->sql_fetchrow($result))
					{
						// Send the messages
						if (!class_exists('messenger'))
						{
							include($this->root_path . 'includes/functions_messenger.' . $this->php_ext);
						}

						$messenger = new \messenger();
						$usernames = $user_ids = [];

						do
						{
							$messenger->template('user_remind_inactive', $row['user_lang']);

							$messenger->set_addresses($row);

							$messenger->anti_abuse_headers($this->config, $this->user);

							$messenger->assign_vars([
								'USERNAME'		=> htmlspecialchars_decode($row['username']),
								'REGISTER_DATE'	=> $this->user->format_date($row['user_regdate'], false, true),
								'U_ACTIVATE'	=> generate_board_url() . "/ucp.{$this->php_ext}?mode=activate&u=" . $row['user_id'] . '&k=' . $row['user_actkey'],
							]);

							$messenger->send($row['user_notify_type']);

							$usernames[] = $row['username'];
							$user_ids[] = (int) $row['user_id'];
						}
						while ($row = $this->db->sql_fetchrow($result));

						$messenger->save_queue();

						// Add the remind state to the database
						$sql = 'UPDATE ' . $this->users_table . '
							SET user_reminded = user_reminded + 1,
								user_reminded_time = ' . time() . '
							WHERE ' . $this->db->sql_in_set('user_id', $user_ids);
						$this->db->sql_query($sql);

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_INACTIVE_REMIND', false, [implode(', ', $usernames)]);

						trigger_error($this->lang->lang('LOG_INACTIVE_REMIND', implode($this->lang->lang('COMMA_SEPARATOR'), $usernames)) . adm_back_link($this->u_action));
					}
					$this->db->sql_freeresult($result);

					// For remind we really need to redirect, else a refresh can result in more than one reminder
					$u_action = $this->u_action . "&amp;$u_sort_param&amp;start=$start";
					$u_action .= ($per_page !== (int) $this->config['topics_per_page']) ? "&amp;users_per_page=$per_page" : '';

					redirect($u_action);

				break;
			}
		}

		// Define where and sort sql for use in displaying logs
		$sql_where = ($sort_days) ? (time() - ($sort_days * 86400)) : 0;
		$sql_sort = $sort_by_sql[$sort_key] . ' ' . ($sort_dir === 'd' ? 'DESC' : 'ASC');

		$inactive = [];
		$inactive_count = 0;

		$start = view_inactive_users($inactive, $inactive_count, $per_page, $start, $sql_where, $sql_sort);

		foreach ($inactive as $row)
		{
			$this->template->assign_block_vars('inactive', [
				'INACTIVE_DATE'	=> $this->user->format_date($row['user_inactive_time']),
				'REMINDED_DATE'	=> $this->user->format_date($row['user_reminded_time']),
				'JOINED'		=> $this->user->format_date($row['user_regdate']),
				'LAST_VISIT'	=> !$row['user_lastvisit'] ? ' - ' : $this->user->format_date($row['user_lastvisit']),

				'REASON'		=> $row['inactive_reason'],
				'USER_ID'		=> $row['user_id'],
				'POSTS'			=> $row['user_posts'] ? $row['user_posts'] : 0,
				'REMINDED'		=> $row['user_reminded'],

				'REMINDED_EXPLAIN'	=> $this->lang->lang('USER_LAST_REMINDED', (int) $row['user_reminded'], $this->user->format_date($row['user_reminded_time'])),

				'USERNAME_FULL'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], false, append_sid("{$this->admin_path}index.$this->php_ext", 'i=users&amp;mode=overview&amp;redirect=acp_inactive')),
				'USERNAME'		=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
				'USER_COLOR'	=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),
				'USER_EMAIL'	=> $row['user_email'],

				'U_USER_ADMIN'	=> append_sid("{$this->admin_path}index.$this->php_ext", "i=users&amp;mode=overview&amp;u={$row['user_id']}"),
				'U_SEARCH_USER'	=> $this->auth->acl_get('u_search') ? append_sid("{$this->root_path}search.$this->php_ext", "author_id={$row['user_id']}&amp;sr=posts") : '',
			]);
		}

		$option_ary = ['activate' => 'ACTIVATE', 'delete' => 'DELETE'];
		if ($this->config['email_enable'])
		{
			$option_ary += ['remind' => 'REMIND'];
		}

		$base_url = $this->u_action . "&amp;$u_sort_param&amp;users_per_page=$per_page";
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $inactive_count, $per_page, $start);

		$this->template->assign_vars([
			'S_INACTIVE_USERS'		=> true,
			'S_INACTIVE_OPTIONS'	=> build_select($option_ary),

			'S_LIMIT_DAYS'		=> $s_limit_days,
			'S_SORT_KEY'		=> $s_sort_key,
			'S_SORT_DIR'		=> $s_sort_dir,
			'USERS_PER_PAGE'	=> $per_page,

			'U_ACTION'		=> $this->u_action . "&amp;$u_sort_param&amp;users_per_page=$per_page&amp;start=$start",
		]);

		$this->tpl_name = 'acp_inactive';
		$this->page_title = 'ACP_INACTIVE_USERS';
	}
}