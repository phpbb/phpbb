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

use phpbb\exception\back_exception;
use phpbb\exception\form_invalid_exception;

class resend
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

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
	 * @param \phpbb\auth\auth					$auth			Auth object
	 * @param \phpbb\config\config				$config			Config object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\controller\helper			$helper			Controller helper object
	 * @param \phpbb\language\language			$lang			Language object
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\template\template			$template		Template object
	 * @param \phpbb\user						$user			User object
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param array								$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $lang,
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
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	/**
	 * Display and handle the "Resend activation" page.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	function main()
	{
		$username	= $this->request->variable('username', '', true);
		$email		= utf8_strtolower($this->request->variable('email', ''));
		$submit		= $this->request->is_set_post('submit');

		$u_mode		= ['ucp_account', 'mode' => 'send_password'];

		$form_key = 'ucp_resend';
		add_form_key($form_key);

		if ($submit)
		{
			if (!check_form_key($form_key))
			{
				throw new form_invalid_exception($u_mode);
			}

			$sql = 'SELECT user_id, group_id, username, user_email, user_type, user_lang, user_actkey, user_inactive_reason
				FROM ' . $this->tables['users'] . "
				WHERE user_email_hash = '" . $this->db->sql_escape(phpbb_email_hash($email)) . "'
					AND username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
			$result = $this->db->sql_query($sql);
			$user_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($user_row === false)
			{
				throw new back_exception(404, 'NO_EMAIL_USER', $u_mode);
			}

			if ($user_row['user_type'] == USER_IGNORE)
			{
				throw new back_exception(400, 'NO_USER', $u_mode);
			}

			if (!$user_row['user_actkey'] && $user_row['user_type'] != USER_INACTIVE)
			{
				throw new back_exception(400, 'ACCOUNT_ALREADY_ACTIVATED', $u_mode);
			}

			if (!$user_row['user_actkey'] || ($user_row['user_type'] == USER_INACTIVE && $user_row['user_inactive_reason'] == INACTIVE_MANUAL))
			{
				throw new back_exception(400, 'ACCOUNT_DEACTIVATED', $u_mode);
			}

			// Determine coppa status on group (REGISTERED(_COPPA))
			$sql = 'SELECT group_name, group_type
				FROM ' . $this->tables['groups'] . '
				WHERE group_id = ' . (int) $user_row['group_id'];
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row === false)
			{
				throw new back_exception(400, 'NO_GROUP', $u_mode);
			}

			$coppa = ($row['group_name'] == 'REGISTERED_COPPA' && $row['group_type'] == GROUP_SPECIAL) ? true : false;

			include_once($this->root_path . 'includes/functions_messenger.' . $this->php_ext);
			$messenger = new \messenger(false);

			if ($this->config['require_activation'] == USER_ACTIVATION_SELF || $coppa)
			{
				$messenger->template(($coppa) ? 'coppa_resend_inactive' : 'user_resend_inactive', $user_row['user_lang']);
				$messenger->set_addresses($user_row);

				$messenger->anti_abuse_headers($this->config, $this->user);

				$messenger->assign_vars([
					'USERNAME'		=> htmlspecialchars_decode($user_row['username']),
					'WELCOME_MSG'	=> htmlspecialchars_decode($this->lang->lang('WELCOME_SUBJECT', $this->config['sitename'])),
					'U_ACTIVATE'	=> generate_board_url() . "/ucp.$this->php_ext?mode=activate&u={$user_row['user_id']}&k={$user_row['user_actkey']}",
				]);

				if ($coppa)
				{
					$messenger->assign_vars([
						'FAX_INFO'		=> $this->config['coppa_fax'],
						'MAIL_INFO'		=> $this->config['coppa_mail'],
						'EMAIL_ADDRESS'	=> $user_row['user_email'],
					]);
				}

				$messenger->send(NOTIFY_EMAIL);
			}

			if ($this->config['require_activation'] == USER_ACTIVATION_ADMIN)
			{
				// Grab an array of user_id's with a_user permissions ... these users can activate a user
				$admin_ary = $this->auth->acl_get_list(false, 'a_user', false);

				$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
					FROM ' . $this->tables['users'] . '
					WHERE ' . $this->db->sql_in_set('user_id', $admin_ary[0]['a_user']);
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$messenger->template('admin_activate', $row['user_lang']);
					$messenger->set_addresses($row);

					$messenger->anti_abuse_headers($this->config, $this->user);

					$messenger->assign_vars([
						'USERNAME'			=> htmlspecialchars_decode($user_row['username']),
						'U_USER_DETAILS'	=> generate_board_url() . "/memberlist.$this->php_ext?mode=viewprofile&u={$user_row['user_id']}",
						'U_ACTIVATE'		=> generate_board_url() . "/ucp.$this->php_ext?mode=activate&u={$user_row['user_id']}&k={$user_row['user_actkey']}",
					]);

					$messenger->send($row['user_notify_type']);
				}
				$this->db->sql_freeresult($result);
			}

			$route = append_sid("{$this->root_path}index.$this->php_ext");
			$return = '<br /><br />' . $this->lang->lang('RETURN_INDEX', '<a href="' . append_sid("{$this->root_path}index.$this->php_ext") . '">', '</a>');
			$message = $this->config['require_activation'] == USER_ACTIVATION_ADMIN ? $this->lang->lang('ACTIVATION_EMAIL_SENT_ADMIN') : $this->lang->lang('ACTIVATION_EMAIL_SENT');

			$this->helper->assign_meta_refresh_var(3, $route);

			return $this->helper->message($message . $return);
		}

		$this->template->assign_vars([
			'USERNAME'			=> $username,
			'EMAIL'				=> $email,
			'S_PROFILE_ACTION'	=> $this->helper->route('ucp_account', ['mode' => 'resend_activation']),
		]);

		return $this->helper->render('ucp_resend.html', $this->lang->lang('UCP_RESEND'));
	}
}
