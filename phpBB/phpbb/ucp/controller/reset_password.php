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

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\db\driver\driver_interface;
use phpbb\event\dispatcher;
use phpbb\language\language;
use phpbb\passwords\manager;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;

/**
* ucp_remind
* Sending password reminders
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

	/** @var manager */
	protected $passwords_manager;

	/** @var request_interface */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var array phpBB DB table names */
	protected $tables;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string PHP extension */
	protected $php_ext;

	/**
	 * ucp_remind constructor.
	 *
	 * @param config $config
	 * @param driver_interface $db
	 * @param dispatcher $dispatcher
	 * @param helper $helper
	 * @param language $language
	 * @param manager $passwords_manager
	 * @param request_interface $request
	 * @param template $template
	 * @param user $user
	 * @param array $tables
	 * @param $root_path
	 * @param $php_ext
	 */
	public function __construct(config $config, driver_interface $db, dispatcher $dispatcher, helper $helper,
								language $language, manager $passwords_manager, request_interface $request,
								template $template, user $user, $tables, $root_path, $php_ext)
	{
		$this->config = $config;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->helper = $helper;
		$this->language = $language;
		$this->passwords_manager = $passwords_manager;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function request()
	{
		$this->language->add_lang('ucp');

		if (!$this->config['allow_password_reset'])
		{
			trigger_error($this->language->lang('UCP_PASSWORD_RESET_DISABLED', '<a href="mailto:' . htmlspecialchars($this->config['board_contact']) . '">', '</a>'));
		}

		$submit		= $this->request->is_set_post('submit');
		$username	= $this->request->variable('username', '', true);
		$email		= strtolower($this->request->variable('email', ''));

		add_form_key('ucp_remind');

		if ($submit)
		{
			if (!check_form_key('ucp_remind'))
			{
				trigger_error('FORM_INVALID');
			}

			if (empty($email))
			{
				trigger_error('NO_EMAIL_USER');
			}

			$sql_array = array(
				'SELECT'	=> 'user_id, username, user_permissions, user_email, user_jabber, user_notify_type, user_type,'
								. ' user_lang, user_inactive_reason, reset_token, reset_token_expiration',
				'FROM'		=> array(USERS_TABLE => 'u'),
				'WHERE'		=> "user_email_hash = '" . $this->db->sql_escape(phpbb_email_hash($email)) . "'" .
					(!empty($username) ? " AND username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'" : ''),
			);

			/**
			 * Change SQL query for fetching user data
			 *
			 * @event core.ucp_remind_modify_select_sql
			 * @var	string	email		User's email from the form
			 * @var	string	username	User's username from the form
			 * @var	array	sql_array	Fully assembled SQL query with keys SELECT, FROM, WHERE
			 * @since 3.1.11-RC1
			 */
			$vars = array(
				'email',
				'username',
				'sql_array',
			);
			extract($this->dispatcher->trigger_event('core.ucp_remind_modify_select_sql', compact($vars)));

			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query_limit($sql, 2); // don't waste resources on more rows than we need
			$rowset = $this->db->sql_fetchrowset($result);

			if (count($rowset) > 1)
			{
				$this->db->sql_freeresult($result);

				$this->template->assign_vars(array(
					'USERNAME_REQUIRED'	=> true,
					'EMAIL'				=> $email,
				));
			}
			else
			{
				$message = $this->language->lang('PASSWORD_UPDATED_IF_EXISTED') . '<br /><br />' . $this->language->lang('RETURN_INDEX', '<a href="' . append_sid("{$this->root_path}index.{$this->php_ext}") . '">', '</a>');

				$user_row = empty($rowset) ? [] : $rowset[0];
				$this->db->sql_freeresult($result);

				if (!$user_row)
				{
					trigger_error($message);
				}

				if ($user_row['user_type'] == USER_IGNORE || $user_row['user_type'] == USER_INACTIVE)
				{
					trigger_error($message);
				}

				// Do not create multiple valid reset tokens
				if (!empty($user_row['reset_token']) && (int) $user_row['reset_token_expiration'] <= (time() + $this->config['reset_token_lifetime']))
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

				// Generate reset token
				$reset_token = gen_rand_string_friendly(32);

				$sql_ary = array(
					'reset_token'				=> $reset_token,
					'reset_token_expiration'	=> time() + $this->config['reset_token_lifetime'],
				);

				$sql = 'UPDATE ' . $this->tables['users'] . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE user_id = ' . $user_row['user_id'];
				$this->db->sql_query($sql);

				include_once($this->root_path . 'includes/functions_messenger.' . $this->php_ext);

				/** @var \messenger $messenger */
				$messenger = new \messenger(false);

				$messenger->template('user_activate_passwd', $user_row['user_lang']);

				$messenger->set_addresses($user_row);

				$messenger->anti_abuse_headers($this->config, $this->user);

				$messenger->assign_vars(array(
						'USERNAME'			=> htmlspecialchars_decode($user_row['username']),
						'U_RESET_PASSWORD'	=> $this->helper->route('phpbb_ucp_reset_password_controller')
				));

				$messenger->send($user_row['user_notify_type']);

				trigger_error($message);
			}
		}

		$this->template->assign_vars(array(
			'USERNAME'			=> $username,
			'EMAIL'				=> $email,
			'S_PROFILE_ACTION'	=> $this->helper->route('phpbb_ucp_forgot_password_controller'),
		));

		return $this->helper->render('ucp_remind.html', $this->language->lang('UCP_REMIND'));
	}

	/**
	 * Handle controller requests
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function reset()
	{
		$this->language->add_lang('ucp');

		if (!$this->config['allow_password_reset'])
		{
			trigger_error($this->language->lang('UCP_PASSWORD_RESET_DISABLED', '<a href="mailto:' . htmlspecialchars($this->config['board_contact']) . '">', '</a>'));
		}

		$submit		= $this->request->is_set_post('submit');
		$username	= $this->request->variable('username', '', true);
		$email		= strtolower($this->request->variable('email', ''));
		$key		= $this->request->variable('key', '');
		$user_id	= $this->request->variable('user_id', 0);

		add_form_key('ucp_remind');

		if ($submit)
		{
			if (!check_form_key('ucp_remind'))
			{
				trigger_error('FORM_INVALID');
			}

			if (empty($email))
			{
				trigger_error('NO_EMAIL_USER');
			}

			$sql_array = array(
				'SELECT'	=> 'user_id, username, user_permissions, user_email, user_jabber, user_notify_type, user_type, user_lang, user_inactive_reason',
				'FROM'		=> array(USERS_TABLE => 'u'),
				'WHERE'		=> "user_email_hash = '" . $this->db->sql_escape(phpbb_email_hash($email)) . "'" .
					(!empty($username) ? " AND username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'" : ''),
			);

			/**
			* Change SQL query for fetching user data
			*
			* @event core.ucp_remind_modify_select_sql
			* @var	string	email		User's email from the form
			* @var	string	username	User's username from the form
			* @var	array	sql_array	Fully assembled SQL query with keys SELECT, FROM, WHERE
			* @since 3.1.11-RC1
			*/
			$vars = array(
				'email',
				'username',
				'sql_array',
			);
			extract($this->dispatcher->trigger_event('core.ucp_remind_modify_select_sql', compact($vars)));

			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query_limit($sql, 2); // don't waste resources on more rows than we need
			$rowset = $this->db->sql_fetchrowset($result);

			if (count($rowset) > 1)
			{
				$this->db->sql_freeresult($result);

				$this->template->assign_vars(array(
					'USERNAME_REQUIRED'	=> true,
					'EMAIL'				=> $email,
				));
			}
			else
			{
				$message = $this->language->lang('PASSWORD_UPDATED_IF_EXISTED') . '<br /><br />' . $this->language->lang('RETURN_INDEX', '<a href="' . append_sid("{$this->root_path}index.{$this->php_ext}") . '">', '</a>');

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
				$user_actkey = gen_rand_string(mt_rand(6, 10));

				$sql = 'UPDATE ' . USERS_TABLE . "
					SET user_newpasswd = '" . $this->db->sql_escape($this->passwords_manager->hash($user_password)) . "', user_actkey = '" . $this->db->sql_escape($user_actkey) . "'
					WHERE user_id = " . $user_row['user_id'];
				$this->db->sql_query($sql);

				include_once($this->root_path . 'includes/functions_messenger.' . $this->php_ext);

				$messenger = new messenger(false);

				$messenger->template('user_activate_passwd', $user_row['user_lang']);

				$messenger->set_addresses($user_row);

				$messenger->anti_abuse_headers($this->config, $this->user);

				$messenger->assign_vars(array(
					'USERNAME'		=> htmlspecialchars_decode($user_row['username']),
					'PASSWORD'		=> htmlspecialchars_decode($user_password),
					'U_ACTIVATE'	=> "$server_url/ucp.{$this->php_ext}?mode=activate&u={$user_row['user_id']}&k=$user_actkey")
				);

				$messenger->send($user_row['user_notify_type']);

				trigger_error($message);
			}
		}

		$this->template->assign_vars(array(
			'USERNAME'			=> $username,
			'EMAIL'				=> $email,
			'S_PROFILE_ACTION'	=> $this->helper->route('phpbb_ucp_reset_password_controller'),
		));

		return $this->helper->render('ucp_remind.html', $this->language->lang('UCP_REMIND'));
	}
}
