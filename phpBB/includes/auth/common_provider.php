<?php
/**
 *
 * @package auth
 * @copyright (c) 2012 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
 * This class holds common functions that all providers must call to in order to
 * login or register.
 *
 * @package auth
 */
abstract class phpbb_auth_common_provider implements phpbb_auth_provider_interface
{
	protected $request;
	protected $db;
	protected $config;
	protected $user;

	/**
	 * Links a user to provider and an index.
	 *
	 * @param int $user_id The user id of the account requested to be linked.
	 * @param string $provider The authentication provider.
	 * @param string $index The index needed to discover additional information from the authentication provider.
	 * @throws phpbb_auth_exception Throws on invalid user id or inability to find the associated account.
	 */
	protected function link($user_id, $provider, $index)
	{
		if (!is_int($user_id) || $user_id <= 0)
		{
			throw new phpbb_auth_exception('Invalid user id supplied.');
		}

		// Verify that the user exists.
		$sql = 'SELECT *
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int)$user_id;
		$result = $this->db->sql_query($sql);
		if (!$result)
		{
			throw new phpbb_auth_exception('User id, ' . $user_id . ', does not resolve to any known phpBB user.');
		}
		$this->db->sql_freeresult($result);

		$link_manager = new phpbb_auth_link_manager($this->db);
		$link_manager->add_link($provider, $user_id, $index);
	}

	/**
	 * Perform phpBB login from data gathered returned from a third party
	 * provider.
	 *
	 * @global string $SID
	 * @global string $_SID
	 * @param int $user_id The ID of the user attempting to log in.
	 * @param boolean $admin Whether the user is trying to reauthenticate for the administration panel.
	 * @param boolean $autologin Whether the user wants to autologin.
	 * @param boolean $viewonline Whether the user wants to appear online or offline.
	 * @throws phpbb_auth_exception On user not found, inactive user, or session creation error.
	 */
	protected function login($user_id, $admin = false, $autologin = false, $viewonline = true)
	{
		// Get user
		$sql = 'SELECT user_id, username, user_password, user_passchg, user_pass_convert, user_email, user_type, user_login_attempts
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $user_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if(!$row)
		{
			$this->login_auth_fail($user_id);
			throw new phpbb_auth_exception('User not found.');
		}

		// Delete login attemps
		$sql = 'DELETE FROM ' . LOGIN_ATTEMPT_TABLE . '
				WHERE user_id = ' . $row['user_id'];
		$this->db->sql_query($sql);

		if ($row['user_login_attempts'] != 0)
		{
			// Successful, reset login attempts (the user passed all stages)
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_login_attempts = 0
				WHERE user_id = ' . $row['user_id'];
			$this->db->sql_query($sql);
		}

		// User inactive...
		if ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE)
		{
			throw new phpbb_auth_exception('ACTIVE_ERROR');
		}

		// Admin reauthentication can be allowed or disallowed on a provider-by-provider basis.
		if ($admin)
		{
			// Save the old session id so the session can be deleted.
			$old_session_id = $this->user->session_id;

			// phpbb_session->session_create() needs these to be modified as globals.
			global $SID, $_SID;

			$cookie_expire = time() - 31536000;
			$this->user->set_cookie('u', '', $cookie_expire);
			$this->user->set_cookie('sid', '', $cookie_expire);
			unset($cookie_expire);

			$SID = '?sid=';
			$this->user->session_id = $_SID = '';
		}

		// Create a session.
		$result = $this->user->session_create($row['user_id'], $admin , $autologin, $viewonline);
		if ($result === true)
		{
			// If admin re-authentication we remove the old session entry because a new one has been created.
			if ($admin)
			{
				$sql = 'DELETE FROM ' . SESSIONS_TABLE . '
					WHERE session_id = \'' . $this->db->sql_escape($old_session_id) . '\'
					AND session_user_id = ' . $row['user_id'];
				$this->db->sql_query($sql);
			}
		}
		else
		{
			throw new phpbb_auth_exception($result);
		}
	}

	/**
	 * Handles the limiting of login activity if authentication fails. This may
	 * prompt additional requirements for authentication such as CAPTCHA.
	 *
	 * @param int $user_id
	 * @param string $username
	 * @param string $username_clean
	 * @throws phpbb_auth_exception Invalid user id and excess login attempts.
	 */
	protected function login_auth_fail($user_id, $username = 0, $username_clean = 0)
	{
		if (!is_int($user_id))
		{
			throw new phpbb_auth_exception('Invalid user_id');
		}

		// Password incorrect - increase login attempts
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_login_attempts = user_login_attempts + 1
			WHERE user_id = ' . (int)$user_id . '
				AND user_login_attempts < ' . LOGIN_ATTEMPTS_MAX;
		$this->db->sql_query($sql);

		if (($this->user->ip && !$this->config['ip_login_limit_use_forwarded']) ||
		($this->user->forwarded_for && $this->config['ip_login_limit_use_forwarded']))
		{
			$sql = 'SELECT COUNT(*) AS attempts
				FROM ' . LOGIN_ATTEMPT_TABLE . '
				WHERE attempt_time > ' . (time() - (int) $this->config['ip_login_limit_time']);
			if ($this->config['ip_login_limit_use_forwarded'])
			{
				$sql .= " AND attempt_forwarded_for = '" . $this->db->sql_escape($this->user->forwarded_for) . "'";
			}
			else
			{
				$sql .= " AND attempt_ip = '" . $this->db->sql_escape($this->user->ip) . "' ";
			}

			$result = $this->db->sql_query($sql);
			$attempts = (int) $this->db->sql_fetchfield('attempts');
			$this->db->sql_freeresult($result);

			$attempt_data = array(
				'attempt_ip'			=> $this->user->ip,
				'attempt_browser'		=> trim(substr($this->user->browser, 0, 149)),
				'attempt_forwarded_for'	=> $this->user->forwarded_for,
				'attempt_time'			=> time(),
				'user_id'				=> $user_id,
				'username'				=> $username,
				'username_clean'		=> $username_clean,
			);
			$sql = 'INSERT INTO ' . LOGIN_ATTEMPT_TABLE . $this->db->sql_build_array('INSERT', $attempt_data);
			$result = $this->db->sql_query($sql);
		}
		else
		{
			$attempts = 0;
		}

		if ($this->config['ip_login_limit_max'] && $attempts >= $this->config['ip_login_limit_max'])
		{
			throw new phpbb_auth_exception('LOGIN_ERROR_ATTEMPTS');
		}
	}

	/**
	 * Redirects the user to the specified page after the authentication action
	 * is completed.
	 *
	 * @param string $redirect_to
	 */
	protected function redirect($redirect_to = null)
	{
		if ($redirect_to === null)
		{
			GLOBAL $phpEx;
			$redirect_to = 'index.' . $phpEx;
		}
		$redirect_to = reapply_sid($redirect_to);
		redirect($redirect_to);
	}

	/**
	 * Validates information going to be used in registration.
	 *
	 * @param array $data
	 * @return boolean true on success
	 */
	protected function register_check_data($data)
	{
		$data_to_validate = array();

		$data_to_validate['username'] = array(
					array('string', false, $this->config['min_name_chars'], $this->config['max_name_chars']),
					array('username', ''),);

		$data_to_validate['email'] = array(
					array('string', false, 6, 60),
					array('email'),);

		$data_to_validate['tz'] = array('num', false, -14, 14);

		$data_to_validate['lang'] = array('language_iso_name');

		if (isset($data['new_password']))
		{
			$data_to_validate['new_password'] = array(
					array('string', false, $this->config['min_pass_chars'], $this->config['max_pass_chars']),
					array('password'));
		}

		$error = validate_data($data, $data_to_validate);

		if(sizeof($error))
		{
			throw new phpbb_auth_exception($error);
		}

		return true;
	}

	protected function register($data, $coppa = false)
	{
		if (is_empty($data))
		{
			throw new phpbb_auth_exception('No registration data supplied.');
		}
		else
		{
			if (!isset($data['username'], $data['email'], $data['tz'], $data['lang']))
			{
				throw new phpbb_auth_exception('Required data missing.');
			}

			// Convert data to its proper format.
			$data['username'] = utf8_normalize_nfc($data['username']);
			$data['email'] = strtolower($data['email']);
			$data['lang'] = basename($data['lang']);

			// Check to see if registration data is valid.
			$this->check_registration_data($data);
		}

		// Which group by default?
		$group_name = ($coppa) ? 'REGISTERED_COPPA' : 'REGISTERED';

		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . "
			WHERE group_name = '" . $this->db->sql_escape($group_name) . "'
				AND group_type = " . GROUP_SPECIAL;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			throw new phpbb_auth_exception('NO_GROUP');
		}

		$group_id = $row['group_id'];

		if (($coppa ||
			$this->config['require_activation'] == USER_ACTIVATION_SELF ||
			$this->config['require_activation'] == USER_ACTIVATION_ADMIN) && $this->config['email_enable'])
		{
			$user_actkey = gen_rand_string(mt_rand(6, 10));
			$user_type = USER_INACTIVE;
			$user_inactive_reason = INACTIVE_REGISTER;
			$user_inactive_time = time();
		}
		else
		{
			$user_type = USER_NORMAL;
			$user_actkey = '';
			$user_inactive_reason = 0;
			$user_inactive_time = 0;
		}

		$user_row = array(
			'username'				=> $data['username'],
			'user_password'			=> phpbb_hash($data['new_password']),
			'user_email'			=> $data['email'],
			'group_id'				=> (int) $group_id,
			'user_timezone'			=> (float) $data['tz'],
			'user_dst'				=> $this->config['board_dst'],
			'user_lang'				=> $data['lang'],
			'user_type'				=> $user_type,
			'user_actkey'			=> $user_actkey,
			'user_ip'				=> $this->user->ip,
			'user_regdate'			=> time(),
			'user_inactive_reason'	=> $user_inactive_reason,
			'user_inactive_time'	=> $user_inactive_time,
		);

		if ($this->config['new_member_post_limit'])
		{
			$user_row['user_new'] = 1;
		}

		// Register user.
		$user_id = user_add($user_row, $cp_data);

		// This should not happen, because the required variables are listed above.
		if ($user_id === false)
		{
			throw new phpbb_auth_exception('NO_USER');
		}

		if ($coppa && $this->config['email_enable'])
		{
			$message = $this->user->lang['ACCOUNT_COPPA'];
			$email_template = 'coppa_welcome_inactive';
		}
		else if ($this->config['require_activation'] == USER_ACTIVATION_SELF && $this->config['email_enable'])
		{
			$message = $this->user->lang['ACCOUNT_INACTIVE'];
			$email_template = 'user_welcome_inactive';
		}
		else if ($this->config['require_activation'] == USER_ACTIVATION_ADMIN && $this->config['email_enable'])
		{
			$message = $this->user->lang['ACCOUNT_INACTIVE_ADMIN'];
			$email_template = 'admin_welcome_inactive';
		}
		else
		{
			$message = $this->user->lang['ACCOUNT_ADDED'];
			$email_template = 'user_welcome';
		}

		if ($this->config['email_enable'])
		{
			global $phpbb_root_path, $phpEx;
			$server_url = generate_board_url();
			include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

			$messenger = new messenger(false);

			$messenger->template($email_template, $data['lang']);

			$messenger->to($data['email'], $data['username']);

			$messenger->anti_abuse_headers($this->config, $this->user);

			$messenger->assign_vars(array(
				'WELCOME_MSG'	=> htmlspecialchars_decode(sprintf($this->user->lang['WELCOME_SUBJECT'], $this->config['sitename'])),
				'USERNAME'		=> htmlspecialchars_decode($data['username']),
				'PASSWORD'		=> htmlspecialchars_decode($data['new_password']),
				'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
			);

			if ($coppa)
			{
				$messenger->assign_vars(array(
					'FAX_INFO'		=> $this->config['coppa_fax'],
					'MAIL_INFO'		=> $this->config['coppa_mail'],
					'EMAIL_ADDRESS'	=> $data['email'])
				);
			}

			$messenger->send(NOTIFY_EMAIL);

			if ($this->config['require_activation'] == USER_ACTIVATION_ADMIN)
			{
				// Grab an array of user_id's with a_user permissions ... these users can activate a user
				$admin_ary = $auth->acl_get_list(false, 'a_user', false);
				$admin_ary = (!empty($admin_ary[0]['a_user'])) ? $admin_ary[0]['a_user'] : array();

				// Also include founders
				$where_sql = ' WHERE user_type = ' . USER_FOUNDER;

				if (sizeof($admin_ary))
				{
					$where_sql .= ' OR ' . $this->db->sql_in_set('user_id', $admin_ary);
				}

				$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
					FROM ' . USERS_TABLE . ' ' .
					$where_sql;
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$messenger->template('admin_activate', $row['user_lang']);
					$messenger->to($row['user_email'], $row['username']);
					$messenger->im($row['user_jabber'], $row['username']);

					$messenger->assign_vars(array(
						'USERNAME'			=> htmlspecialchars_decode($data['username']),
						'U_USER_DETAILS'	=> "$server_url/memberlist.$phpEx?mode=viewprofile&u=$user_id",
						'U_ACTIVATE'		=> "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
					);

					$messenger->send($row['user_notify_type']);
				}
				$this->db->sql_freeresult($result);
			}
		}
	}
}
