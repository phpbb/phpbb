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

use phpbb\exception\http_exception;

/**
 * User activation
 */
class activate
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

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

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth					$auth					Auth object
	 * @param \phpbb\config\config				$config					Config object
	 * @param \phpbb\db\driver\driver_interface	$db						Database object
	 * @param \phpbb\event\dispatcher			$dispatcher				Event dispatcher object
	 * @param \phpbb\controller\helper			$helper					Controller helper object
	 * @param \phpbb\language\language			$language				Language object
	 * @param \phpbb\log\log					$log					Log object
	 * @param \phpbb\notification\manager		$notification_manager	Notification manager object
	 * @param \phpbb\request\request			$request				Request object
	 * @param \phpbb\template\template			$template				Template object
	 * @param \phpbb\user						$user					User object
	 * @param string							$root_path				phpBB root path
	 * @param string							$php_ext				php File extension
	 * @param array								$tables					phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\notification\manager $notification_manager,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth					= $auth;
		$this->config				= $config;
		$this->db					= $db;
		$this->dispatcher			= $dispatcher;
		$this->helper				= $helper;
		$this->language				= $language;
		$this->log					= $log;
		$this->notification_manager	= $notification_manager;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
		$this->tables				= $tables;
	}

	public function main()
	{
		$user_id = $this->request->variable('u', 0);
		$key = $this->request->variable('k', '');

		$sql = 'SELECT user_id, username, user_type, user_email, user_newpasswd, 
						user_lang, user_notify_type, user_actkey, user_inactive_reason
			FROM ' . $this->tables['users'] . '
			WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$user_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($user_row === false)
		{
			throw new http_exception(404, 'NO_USER');
		}

		if ($user_row['user_type'] <> USER_INACTIVE && !$user_row['user_newpasswd'])
		{
			throw new http_exception(400, 'ALREADY_ACTIVATED');
		}

		if ($user_row['user_inactive_reason'] == INACTIVE_MANUAL || $user_row['user_actkey'] !== $key)
		{
			throw new http_exception(400, 'WRONG_ACTIVATION');
		}

		// Do not allow activating by non administrators when admin activation is on
		// Only activation type the user should be able to do is INACTIVE_REMIND
		// or activate a new password which is not an activation state :@
		if (!$user_row['user_newpasswd'] && $user_row['user_inactive_reason'] != INACTIVE_REMIND && $this->config['require_activation'] == USER_ACTIVATION_ADMIN && !$this->auth->acl_get('a_user'))
		{
			if (!$this->user->data['is_registered'])
			{
				return login_box('', $this->language->lang('NO_AUTH_OPERATION'));
			}

			throw new http_exception(403, 'NO_AUTH_OPERATION');
		}

		$update_password = (bool) $user_row['user_newpasswd'];

		if ($update_password)
		{
			$sql_ary = [
				'user_actkey'			=> '',
				'user_password'			=> $user_row['user_newpasswd'],
				'user_newpasswd'		=> '',
				'user_login_attempts'	=> 0,
			];

			$sql = 'UPDATE ' . $this->tables['users'] . '
				SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE user_id = ' . (int) $user_row['user_id'];
			$this->db->sql_query($sql);

			$this->user->reset_login_keys((int) $user_row['user_id']);

			$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_NEW_PASSWORD', false, [
				'reportee_id' => $user_row['user_id'],
				$user_row['username'],
			]);
		}

		if (!$update_password)
		{
			include_once($this->root_path . 'includes/functions_user.' . $this->php_ext);

			user_active_flip('activate', $user_row['user_id']);

			$sql = 'UPDATE ' . $this->tables['users'] . "
				SET user_actkey = ''
				WHERE user_id = " . (int) $user_row['user_id'];
			$this->db->sql_query($sql);

			// Create the correct logs
			$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_ACTIVE_USER', false, [
				'reportee_id' => $user_row['user_id'],
			]);

			if ($this->auth->acl_get('a_user'))
			{
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_ACTIVE', false, [$user_row['username']]);
			}
		}

		if ($this->config['require_activation'] == USER_ACTIVATION_ADMIN && !$update_password)
		{
			$this->notification_manager->delete_notifications('notification.type.admin_activate_user', $user_row['user_id']);

			include_once($this->root_path . 'includes/functions_messenger.' . $this->php_ext);

			$messenger = new \messenger(false);

			$messenger->template('admin_welcome_activated', $user_row['user_lang']);
			$messenger->set_addresses($user_row);
			$messenger->anti_abuse_headers($this->config, $this->user);
			$messenger->assign_vars(['USERNAME' => htmlspecialchars_decode($user_row['username'])]);
			$messenger->send($user_row['user_notify_type']);

			$message = 'ACCOUNT_ACTIVE_ADMIN';
		}
		else
		{
			if (!$update_password)
			{
				$message = $user_row['user_inactive_reason'] == INACTIVE_PROFILE ? 'ACCOUNT_ACTIVE_PROFILE' : 'ACCOUNT_ACTIVE';
			}
			else
			{
				$message = 'PASSWORD_ACTIVATED';
			}
		}

		/**
		 * This event can be used to modify data after user account's activation
		 *
		 * @event core.ucp_activate_after
		 * @var array	user_row	Array with some user data
		 * @var string	message		Language string of the message that will be displayed to the user
		 * @since 3.1.6-RC1
		 */
		$vars = ['user_row', 'message'];
		extract($this->dispatcher->trigger_event('core.ucp_activate_after', compact($vars)));

		$this->helper->assign_meta_refresh_var(3, append_sid("{$this->root_path}index.$this->php_ext"));

		return $this->helper->message($this->language->lang($message));
	}
}
