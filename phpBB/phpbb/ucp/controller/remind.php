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

class remind
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\passwords\manager */
	protected $password_manager;

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
	 * @param \phpbb\config\config				$config				Config object
	 * @param \phpbb\db\driver\driver_interface	$db					Database object
	 * @param \phpbb\event\dispatcher			$dispatcher			Event dispatcher object
	 * @param \phpbb\language\language			$lang				Language object
	 * @param \phpbb\passwords\manager			$password_manager	Password manager object
	 * @param \phpbb\request\request			$request			Request object
	 * @param \phpbb\template\template			$template			Template object
	 * @param \phpbb\user						$user				User object
	 * @param string							$root_path			phpBB root path
	 * @param string							$php_ext			php File extension
	 * @param array								$tables				phpBB tables
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\language\language $lang,
		\phpbb\passwords\manager $password_manager,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->config			= $config;
		$this->db				= $db;
		$this->dispatcher		= $dispatcher;
		$this->lang				= $lang;
		$this->password_manager	= $password_manager;
		$this->request			= $request;
		$this->template			= $template;
		$this->user				= $user;

		$this->root_path		= $root_path;
		$this->php_ext			= $php_ext;
		$this->tables			= $tables;
	}

	function main($id, $mode)
	{
		if (!$this->config['allow_password_reset'])
		{
			trigger_error($this->lang->lang('UCP_PASSWORD_RESET_DISABLED', '<a href="mailto:' . htmlspecialchars($this->config['board_contact']) . '">', '</a>'));
		}

		$username	= $this->request->variable('username', '', true);
		$email		= strtolower($this->request->variable('email', ''));
		$submit		= $this->request->is_set_post('submit');

		$form_key = 'ucp_remind';
		add_form_key($form_key);

		if ($submit)
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->lang->lang('FORM_INVALID'));
			}

			if (empty($email))
			{
				trigger_error($this->lang->lang('NO_EMAIL_USER'));
			}

			$sql_array = [
				'SELECT'	=> 'user_id, username, user_permissions, user_email, user_jabber, user_notify_type, user_type, user_lang, user_inactive_reason',
				'FROM'		=> [$this->tables['users'] => 'u'],
				'WHERE'		=> "user_email_hash = '" . $this->db->sql_escape(phpbb_email_hash($email)) . "'" .
					(!empty($username) ? " AND username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'" : ''),
			];

			/**
			 * Change SQL query for fetching user data
			 *
			 * @event core.ucp_remind_modify_select_sql
			 * @var string	email		User's email from the form
			 * @var string	username	User's username from the form
			 * @var array	sql_array	Fully assembled SQL query with keys SELECT, FROM, WHERE
			 * @since 3.1.11-RC1
			 */
			$vars = [
				'email',
				'username',
				'sql_array',
			];
			extract($this->dispatcher->trigger_event('core.ucp_remind_modify_select_sql', compact($vars)));

			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query_limit($sql, 2); // don't waste resources on more rows than we need
			$rowset = $this->db->sql_fetchrowset($result);

			if (count($rowset) > 1)
			{
				$this->db->sql_freeresult($result);

				$this->template->assign_vars([
					'USERNAME_REQUIRED'	=> true,
					'EMAIL'				=> $email,
				]);
			}
			else
			{
				$message = $this->lang->lang('PASSWORD_UPDATED_IF_EXISTED') . '<br /><br />' . $this->lang->lang('RETURN_INDEX', '<a href="' . append_sid("{$this->root_path}index.$this->php_ext") . '">', '</a>');

				if (empty($rowset))
				{
					trigger_error($message);
				}

				$user_row = $rowset[0];
				$this->db->sql_freeresult($result);

				if (!$user_row)
				{
					trigger_error($message);
				}

				if ($user_row['user_type'] == USER_IGNORE || $user_row['user_type'] == USER_INACTIVE)
				{
					trigger_error($message);
				}

				// Check users permissions
				$auth2 = new \phpbb\auth\auth();
				$auth2->acl($user_row);

				if (!$auth2->acl_get('u_chgpasswd'))
				{
					trigger_error($message);
				}

				$server_url = generate_board_url();

				// Make password at least 8 characters long, make it longer if admin wants to.
				// gen_rand_string() however has a limit of 12 or 13.
				$user_password = gen_rand_string_friendly(max(8, mt_rand((int) $this->config['min_pass_chars'], (int) $this->config['max_pass_chars'])));

				// For the activation key a random length between 6 and 10 will do.
				$user_act_key = gen_rand_string(mt_rand(6, 10));

				$sql = 'UPDATE ' . $this->tables['users'] . "
					SET user_newpasswd = '" . $this->db->sql_escape($this->password_manager->hash($user_password)) . "', user_actkey = '" . $this->db->sql_escape($user_act_key) . "'
					WHERE user_id = " . (int) $user_row['user_id'];
				$this->db->sql_query($sql);

				include_once($this->root_path . 'includes/functions_messenger.' . $this->php_ext);

				$messenger = new \messenger(false);

				$messenger->template('user_activate_passwd', $user_row['user_lang']);

				$messenger->set_addresses($user_row);

				$messenger->anti_abuse_headers($this->config, $this->user);

				$messenger->assign_vars([
					'USERNAME'		=> htmlspecialchars_decode($user_row['username']),
					'PASSWORD'		=> htmlspecialchars_decode($user_password),
					'U_ACTIVATE'	=> "$server_url/ucp.$this->php_ext?mode=activate&u={$user_row['user_id']}&k=$user_act_key",
				]);

				$messenger->send($user_row['user_notify_type']);

				trigger_error($message);
			}
		}

		$this->template->assign_vars([
			'USERNAME'			=> $username,
			'EMAIL'				=> $email,
			'S_PROFILE_ACTION'	=> append_sid($this->root_path . 'ucp.' . $this->php_ext, 'mode=sendpassword'),
		]);

		$this->tpl_name = 'ucp_remind';
		$this->page_title = 'UCP_REMIND';
	}
}
