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

/**
 * Resending activation emails
 */
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
	protected $language;

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
	 * @param \phpbb\language\language			$language		Language object
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
		\phpbb\language\language $language,
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
		$this->language		= $language;
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
	public function main()
	{
		$username	= $this->request->variable('username', '', true);
		$email		= strtolower($this->request->variable('email', ''));
		$submit		= $this->request->is_set_post('submit');

		$form_key = 'ucp_resend';
		add_form_key($form_key);

		$return = '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $this->helper->route('ucp_account', ['mode' => 'resend_activation']) . '">', '</a>');

		if ($submit)
		{
			if (!check_form_key($form_key))
			{
				return trigger_error($this->language->lang('FORM_INVALID') . $return, E_USER_WARNING);
			}

			$sql = 'SELECT user_id, group_id, username, user_email, user_type, user_lang, user_actkey, user_inactive_reason
				FROM ' . $this->tables['users'] . "
				WHERE user_email = '" . $db->sql_escape($email) . "'
					AND username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
			$result = $this->db->sql_query($sql);
			$user_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($user_row === false)
			{
				return trigger_error($this->language->lang('NO_EMAIL_USER') . $return, E_USER_WARNING);
			}

			if ($user_row['user_type'] == USER_IGNORE)
			{
				return trigger_error($this->language->lang('NO_USER') . $return, E_USER_WARNING);
			}

			if (!$user_row['user_actkey'] && $user_row['user_type'] != USER_INACTIVE)
			{
				return trigger_error($this->language->lang('ACCOUNT_ALREADY_ACTIVATED') . $return, E_USER_WARNING);
			}

			if (!$user_row['user_actkey'] || ($user_row['user_type'] == USER_INACTIVE && $user_row['user_inactive_reason'] == INACTIVE_MANUAL))
			{
				return trigger_error($this->language->lang('ACCOUNT_DEACTIVATED') . $return, E_USER_WARNING);
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
				return trigger_error($this->language->lang('NO_GROUP') . $return, E_USER_WARNING);
			}

			$coppa = ($row['group_name'] == 'REGISTERED_COPPA' && $row['group_type'] == GROUP_SPECIAL) ? true : false;

			if (!class_exists('messenger'))
			{
				include($this->root_path . 'includes/functions_messenger.' . $this->php_ext);
			}

			$messenger = new \messenger(false);

			if ($this->config['require_activation'] == USER_ACTIVATION_SELF || $coppa)
			{
				$messenger->template($coppa ? 'coppa_resend_inactive' : 'user_resend_inactive', $user_row['user_lang']);
				$messenger->set_addresses($user_row);
				$messenger->anti_abuse_headers($this->config, $this->user);

				$messenger->assign_vars([
					'USERNAME'		=> htmlspecialchars_decode($user_row['username']),
					'WELCOME_MSG'	=> htmlspecialchars_decode($this->language->lang('WELCOME_SUBJECT', $this->config['sitename'])),
					'U_ACTIVATE'	=> generate_board_url(true) . $this->helper->route('ucp_account', [
						'mode'	=> 'activate',
						'u'		=> $user_row['user_id'],
						'k'		=> $user_row['user_actkey'],
					]),
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
						'U_ACTIVATE'		=> generate_board_url(true) . $this->helper->route('ucp_account', [
							'mode'	=> 'activate',
							'u'		=> $user_row['user_id'],
							'k'		=> $user_row['user_actkey'],
						]),
					]);

					$messenger->send($row['user_notify_type']);
				}
				$this->db->sql_freeresult($result);
			}

			$this->helper->assign_meta_refresh_var(3, append_sid("{$this->root_path}index.$this->php_ext"));

			$message = $this->config['require_activation'] == USER_ACTIVATION_ADMIN ? 'ACTIVATION_EMAIL_SENT_ADMIN' : 'ACTIVATION_EMAIL_SENT';
			$message = $this->language->lang($message);
			$message .= '<br /><br />' . $this->language->lang('RETURN_INDEX', '<a href="' . append_sid("{$this->root_path}index.$this->php_ext") . '">', '</a>');

			return $this->helper->message($message);
		}

		$this->template->assign_vars([
			'USERNAME'			=> $username,
			'EMAIL'				=> $email,
			'S_PROFILE_ACTION'	=> $this->helper->route('ucp_account', ['mode' => 'resend_activation']),
		]);

		return $this->helper->render('ucp_resend.html', $this->language->lang('UCP_RESEND'));
	}
}
