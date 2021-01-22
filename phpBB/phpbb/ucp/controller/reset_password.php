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

namespace phpbb\ucp\controller;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\db\driver\driver_interface;
use phpbb\event\dispatcher;
use phpbb\exception\http_exception;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\passwords\manager;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\HttpFoundation\Response;

/**
* Handling forgotten passwords via reset password functionality
*/
class reset_password
{
	/** @var config */
	protected $config;

	/** @var driver_interface */
	protected $db;

	/** @var dispatcher */
	protected $dispatcher;

	/** @var helper */
	protected $helper;

	/** @var language */
	protected $language;

	/** @var log_interface */
	protected $log;

	/** @var manager */
	protected $passwords_manager;

	/** @var request_interface */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var array phpBB DB table names */
	protected $users_table;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string PHP extension */
	protected $php_ext;

	/**
	 * Reset password controller constructor.
	 *
	 * @param config $config
	 * @param driver_interface $db
	 * @param dispatcher $dispatcher
	 * @param helper $helper
	 * @param language $language
	 * @param log_interface $log
	 * @param manager $passwords_manager
	 * @param request_interface $request
	 * @param template $template
	 * @param user $user
	 * @param string $users_table
	 * @param string $root_path
	 * @param string $php_ext
	 */
	public function __construct(config $config, driver_interface $db, dispatcher $dispatcher, helper $helper,
								language $language, log_interface $log, manager $passwords_manager,
								request_interface $request, template $template, user $user, string $users_table,
								string $root_path, string $php_ext)
	{
		$this->config = $config;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->helper = $helper;
		$this->language = $language;
		$this->log = $log;
		$this->passwords_manager = $passwords_manager;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->users_table = $users_table;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Init controller
	 */
	protected function init_controller()
	{
		$this->language->add_lang('ucp');

		if (!$this->config['allow_password_reset'])
		{
			throw new http_exception(Response::HTTP_OK, 'UCP_PASSWORD_RESET_DISABLED', [
				'<a href="mailto:' . htmlspecialchars($this->config['board_contact'], ENT_COMPAT) . '">',
				'</a>'
			]);
		}
	}

	/**
	 * Remove reset token for specified user
	 *
	 * @param int $user_id User ID
	 */
	protected function remove_reset_token(int $user_id)
	{
		$sql_ary = [
			'reset_token'				=> '',
			'reset_token_expiration'	=> 0,
		];

		$sql = 'UPDATE ' . $this->users_table . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE user_id = ' . $user_id;
		$this->db->sql_query($sql);
	}

	/**
	 * Handle password reset request
	 *
	 * @return Response
	 */
	public function request()
	{
		$this->init_controller();

		$submit		= $this->request->is_set_post('submit');
		$username	= $this->request->variable('username', '', true);
		$email		= strtolower($this->request->variable('email', ''));

		add_form_key('ucp_reset_password');

		if ($submit)
		{
			if (!check_form_key('ucp_reset_password'))
			{
				throw new http_exception(Response::HTTP_UNAUTHORIZED, 'FORM_INVALID');
			}

			if (empty($email))
			{
				return $this->helper->message('NO_EMAIL_USER');
			}

			$sql_array = [
				'SELECT'	=> 'user_id, username, user_permissions, user_email, user_jabber, user_notify_type, user_type,'
								. ' user_lang, user_inactive_reason, reset_token, reset_token_expiration',
				'FROM'		=> [$this->users_table => 'u'],
				'WHERE'		=> "user_email = '" . $this->db->sql_escape($email) . "'" .
					(!empty($username) ? " AND username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'" : ''),
			];

			/**
			 * Change SQL query for fetching user data
			 *
			 * @event core.ucp_remind_modify_select_sql
			 * @var	string	email		User's email from the form
			 * @var	string	username	User's username from the form
			 * @var	array	sql_array	Fully assembled SQL query with keys SELECT, FROM, WHERE
			 * @since 3.1.11-RC1
			 * @changed 3.3.0-b1 Moved to reset password controller
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
			$this->db->sql_freeresult($result);

			if (count($rowset) > 1)
			{
				$this->template->assign_vars([
					'USERNAME_REQUIRED'	=> true,
					'EMAIL'				=> $email,
				]);
			}
			else
			{
				$message = $this->language->lang('PASSWORD_RESET_LINK_SENT') . '<br /><br />' . $this->language->lang('RETURN_INDEX', '<a href="' . append_sid("{$this->root_path}index.{$this->php_ext}") . '">', '</a>');

				if (empty($rowset))
				{
					return $this->helper->message($message);
				}

				$user_row = $rowset[0];

				if ($user_row['user_type'] == USER_IGNORE || $user_row['user_type'] == USER_INACTIVE)
				{
					return $this->helper->message($message);
				}

				// Do not create multiple valid reset tokens
				if (!empty($user_row['reset_token']) && (int) $user_row['reset_token_expiration'] >= time())
				{
					return $this->helper->message($message);
				}

				// Check users permissions
				$auth = new auth();
				$auth->acl($user_row);

				if (!$auth->acl_get('u_chgpasswd'))
				{
					return $this->helper->message($message);
				}

				// Generate reset token
				$reset_token = strtolower(gen_rand_string(32));

				$sql_ary = [
					'reset_token'				=> $reset_token,
					'reset_token_expiration'	=> strtotime('+1 day'),
				];

				$sql = 'UPDATE ' . $this->users_table . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE user_id = ' . $user_row['user_id'];
				$this->db->sql_query($sql);

				if (!class_exists('messenger'))
				{
					include($this->root_path . 'includes/functions_messenger.' . $this->php_ext);
				}

				/** @var \messenger $messenger */
				$messenger = new \messenger(false);

				$messenger->template('user_forgot_password', $user_row['user_lang']);

				$messenger->set_addresses($user_row);

				$messenger->anti_abuse_headers($this->config, $this->user);

				$messenger->assign_vars([
						'USERNAME'			=> htmlspecialchars_decode($user_row['username'], ENT_COMPAT),
						'U_RESET_PASSWORD'	=> generate_board_url(true) . $this->helper->route('phpbb_ucp_reset_password_controller', [
							'u'		=> $user_row['user_id'],
							'token'	=> $reset_token,
						], false)
				]);

				$messenger->send($user_row['user_notify_type']);

				return $this->helper->message($message);
			}
		}

		$this->template->assign_vars([
			'USERNAME'					=> $username,
			'EMAIL'						=> $email,
			'U_RESET_PASSWORD_ACTION'	=> $this->helper->route('phpbb_ucp_forgot_password_controller'),
		]);

		return $this->helper->render('ucp_reset_password.html', $this->language->lang('RESET_PASSWORD'));
	}

	/**
	 * Handle controller requests
	 *
	 * @return Response
	 */
	public function reset()
	{
		$this->init_controller();

		$submit			= $this->request->is_set_post('submit');
		$reset_token	= $this->request->variable('token', '');
		$user_id		= $this->request->variable('u', 0);

		if (empty($reset_token))
		{
			return $this->helper->message('NO_RESET_TOKEN');
		}

		if (!$user_id)
		{
			return $this->helper->message('NO_USER');
		}

		add_form_key('ucp_reset_password');

		$sql_array = [
			'SELECT'	=> 'user_id, username, user_permissions, user_email, user_jabber, user_notify_type, user_type,'
				. ' user_lang, user_inactive_reason, reset_token, reset_token_expiration',
			'FROM'		=> [$this->users_table => 'u'],
			'WHERE'		=> 'user_id = ' . $user_id,
		];

		/**
		 * Change SQL query for fetching user data
		 *
		 * @event core.ucp_reset_password_modify_select_sql
		 * @var	int	user_id		User ID from the form
		 * @var	string	reset_token Reset token
		 * @var	array	sql_array	Fully assembled SQL query with keys SELECT, FROM, WHERE
		 * @since 3.3.0-b1
		 */
		$vars = [
			'user_id',
			'reset_token',
			'sql_array',
		];
		extract($this->dispatcher->trigger_event('core.ucp_reset_password_modify_select_sql', compact($vars)));

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, 1);
		$user_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$message = $this->language->lang('RESET_TOKEN_EXPIRED_OR_INVALID') . '<br /><br />' . $this->language->lang('RETURN_INDEX', '<a href="' . append_sid("{$this->root_path}index.{$this->php_ext}") . '">', '</a>');

		if (empty($user_row))
		{
			return $this->helper->message($message);
		}

		if (!hash_equals($reset_token, $user_row['reset_token']))
		{
			return $this->helper->message($message);
		}

		if ($user_row['reset_token_expiration'] < time())
		{
			$this->remove_reset_token($user_id);

			return $this->helper->message($message);
		}

		$errors = [];

		if ($submit)
		{
			if (!check_form_key('ucp_reset_password'))
			{
				return $this->helper->message('FORM_INVALID');
			}

			if ($user_row['user_type'] == USER_IGNORE || $user_row['user_type'] == USER_INACTIVE)
			{
				return $this->helper->message($message);
			}

			// Check users permissions
			$auth = new auth();
			$auth->acl($user_row);

			if (!$auth->acl_get('u_chgpasswd'))
			{
				return $this->helper->message($message);
			}

			if (!function_exists('validate_data'))
			{
				include($this->root_path . 'includes/functions_user.' . $this->php_ext);
			}

			$data = [
				'new_password'		=> $this->request->untrimmed_variable('new_password', '', true),
				'password_confirm'	=> $this->request->untrimmed_variable('new_password_confirm', '', true),
			];
			$check_data = [
				'new_password'		=> [
					['string', false, $this->config['min_pass_chars'], 0],
					['password'],
				],
				'password_confirm'	=> ['string', true, $this->config['min_pass_chars'], 0],
			];
			$errors = array_merge($errors, validate_data($data, $check_data));
			if (strcmp($data['new_password'], $data['password_confirm']) !== 0)
			{
				$errors[] = $data['password_confirm'] ? 'NEW_PASSWORD_ERROR' : 'NEW_PASSWORD_CONFIRM_EMPTY';
			}
			if (empty($errors))
			{
				$sql_ary = [
					'user_password'				=> $this->passwords_manager->hash($data['new_password']),
					'user_login_attempts'		=> 0,
					'reset_token'				=> '',
					'reset_token_expiration'	=> 0,
				];
				$sql = 'UPDATE ' . $this->users_table . '
							SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . (int) $user_row['user_id'];
				$this->db->sql_query($sql);
				$this->log->add('user', $user_row['user_id'], $this->user->ip, 'LOG_USER_NEW_PASSWORD', false, [
					'reportee_id' => $user_row['user_id'],
					$user_row['username']
				]);
				meta_refresh(3, append_sid("{$this->root_path}index.{$this->php_ext}"));
				return $this->helper->message($this->language->lang('PASSWORD_RESET'));
			}
		}

		$this->template->assign_vars([
			'PASSWORD_RESET_ERRORS'		=> !empty($errors) ? array_map([$this->language, 'lang'], $errors) : '',
			'S_IS_PASSWORD_RESET'		=> true,
			'U_RESET_PASSWORD_ACTION'	=> $this->helper->route('phpbb_ucp_reset_password_controller'),
			'L_CHANGE_PASSWORD_EXPLAIN'	=> $this->language->lang($this->config['pass_complex'] . '_EXPLAIN', $this->language->lang('CHARACTERS', (int) $this->config['min_pass_chars'])),
			'S_HIDDEN_FIELDS'			=> build_hidden_fields([
				'u'		=> $user_id,
				'token'	=> $reset_token,
			]),
		]);

		return $this->helper->render('ucp_reset_password.html', $this->language->lang('RESET_PASSWORD'));
	}
}
