<?php
/**
*
* @package auth
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\auth\provider;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * Database authentication provider for phpBB3
 *
 * This is for authentication via the integrated user table
 *
 * @package auth
 */
class db extends \phpbb\auth\provider\base
{

	/**
	 * Database Authentication Constructor
	 *
	 * @param	\phpbb\db\driver\driver	$db
	 * @param	\phpbb\config\config 	$config
	 * @param	\phpbb\request\request	$request
	 * @param	\phpbb\user		$user
	 * @param	string			$phpbb_root_path
	 * @param	string			$php_ext
	 */
	public function __construct(\phpbb\db\driver\driver $db, \phpbb\config\config $config, \phpbb\request\request $request, \phpbb\user $user, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->config = $config;
		$this->request = $request;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * {@inheritdoc}
	 */
	public function login($username, $password)
	{
		// Auth plugins get the password untrimmed.
		// For compatibility we trim() here.
		$password = trim($password);

		// do not allow empty password
		if (!$password)
		{
			return array(
				'status'	=> LOGIN_ERROR_PASSWORD,
				'error_msg'	=> 'NO_PASSWORD_SUPPLIED',
				'user_row'	=> array('user_id' => ANONYMOUS),
			);
		}

		if (!$username)
		{
			return array(
				'status'	=> LOGIN_ERROR_USERNAME,
				'error_msg'	=> 'LOGIN_ERROR_USERNAME',
				'user_row'	=> array('user_id' => ANONYMOUS),
			);
		}

		$username_clean = utf8_clean_string($username);

		$sql = 'SELECT user_id, username, user_password, user_passchg, user_pass_convert, user_email, user_type, user_login_attempts
			FROM ' . USERS_TABLE . "
			WHERE username_clean = '" . $this->db->sql_escape($username_clean) . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

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
				'user_id'				=> ($row) ? (int) $row['user_id'] : 0,
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

		if (!$row)
		{
			if ($this->config['ip_login_limit_max'] && $attempts >= $this->config['ip_login_limit_max'])
			{
				return array(
					'status'		=> LOGIN_ERROR_ATTEMPTS,
					'error_msg'		=> 'LOGIN_ERROR_ATTEMPTS',
					'user_row'		=> array('user_id' => ANONYMOUS),
				);
			}

			return array(
				'status'	=> LOGIN_ERROR_USERNAME,
				'error_msg'	=> 'LOGIN_ERROR_USERNAME',
				'user_row'	=> array('user_id' => ANONYMOUS),
			);
		}

		$show_captcha = ($this->config['max_login_attempts'] && $row['user_login_attempts'] >= $this->config['max_login_attempts']) ||
			($this->config['ip_login_limit_max'] && $attempts >= $this->config['ip_login_limit_max']);

		// If there are too many login attempts, we need to check for a confirm image
		// Every auth module is able to define what to do by itself...
		if ($show_captcha)
		{
			// Visual Confirmation handling
			if (!class_exists('phpbb_captcha_factory', false))
			{
				include ($this->phpbb_root_path . 'includes/captcha/captcha_factory.' . $this->php_ext);
			}

			$captcha = \phpbb_captcha_factory::get_instance($this->config['captcha_plugin']);
			$captcha->init(CONFIRM_LOGIN);
			$vc_response = $captcha->validate($row);
			if ($vc_response)
			{
				return array(
					'status'		=> LOGIN_ERROR_ATTEMPTS,
					'error_msg'		=> 'LOGIN_ERROR_ATTEMPTS',
					'user_row'		=> $row,
				);
			}
			else
			{
				$captcha->reset();
			}

		}

		// If the password convert flag is set we need to convert it
		if ($row['user_pass_convert'])
		{
			// enable super globals to get literal value
			// this is needed to prevent unicode normalization
			$super_globals_disabled = $this->request->super_globals_disabled();
			if ($super_globals_disabled)
			{
				$this->request->enable_super_globals();
			}

			// in phpBB2 passwords were used exactly as they were sent, with addslashes applied
			$password_old_format = isset($_REQUEST['password']) ? (string) $_REQUEST['password'] : '';
			$password_old_format = (!STRIP) ? addslashes($password_old_format) : $password_old_format;
			$password_new_format = $this->request->variable('password', '', true);

			if ($super_globals_disabled)
			{
				$this->request->disable_super_globals();
			}

			if ($password == $password_new_format)
			{
				if (!function_exists('utf8_to_cp1252'))
				{
					include($this->phpbb_root_path . 'includes/utf/data/recode_basic.' . $this->php_ext);
				}

				// cp1252 is phpBB2's default encoding, characters outside ASCII range might work when converted into that encoding
				// plain md5 support left in for conversions from other systems.
				if ((strlen($row['user_password']) == 34 && (phpbb_check_hash(md5($password_old_format), $row['user_password']) || phpbb_check_hash(md5(utf8_to_cp1252($password_old_format)), $row['user_password'])))
					|| (strlen($row['user_password']) == 32  && (md5($password_old_format) == $row['user_password'] || md5(utf8_to_cp1252($password_old_format)) == $row['user_password'])))
				{
					$hash = phpbb_hash($password_new_format);

					// Update the password in the users table to the new \format and remove user_pass_convert flag
					$sql = 'UPDATE ' . USERS_TABLE . '
						SET user_password = \'' . $this->db->sql_escape($hash) . '\',
							user_pass_convert = 0
						WHERE user_id = ' . $row['user_id'];
					$this->db->sql_query($sql);

					$row['user_pass_convert'] = 0;
					$row['user_password'] = $hash;
				}
				else
				{
					// Although we weren't able to convert this password we have to
					// increase login attempt count to make sure this cannot be exploited
					$sql = 'UPDATE ' . USERS_TABLE . '
						SET user_login_attempts = user_login_attempts + 1
						WHERE user_id = ' . (int) $row['user_id'] . '
							AND user_login_attempts < ' . LOGIN_ATTEMPTS_MAX;
					$this->db->sql_query($sql);

					return array(
						'status'		=> LOGIN_ERROR_PASSWORD_CONVERT,
						'error_msg'		=> 'LOGIN_ERROR_PASSWORD_CONVERT',
						'user_row'		=> $row,
					);
				}
			}
		}

		// Check password ...
		if (!$row['user_pass_convert'] && phpbb_check_hash($password, $row['user_password']))
		{
			// Check for old password hash...
			if (strlen($row['user_password']) == 32)
			{
				$hash = phpbb_hash($password);

				// Update the password in the users table to the new \format
				$sql = 'UPDATE ' . USERS_TABLE . "
					SET user_password = '" . $this->db->sql_escape($hash) . "',
						user_pass_convert = 0
					WHERE user_id = {$row['user_id']}";
				$this->db->sql_query($sql);

				$row['user_password'] = $hash;
			}

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
				return array(
					'status'		=> LOGIN_ERROR_ACTIVE,
					'error_msg'		=> 'ACTIVE_ERROR',
					'user_row'		=> $row,
				);
			}

			// Successful login... set user_login_attempts to zero...
			return array(
				'status'		=> LOGIN_SUCCESS,
				'error_msg'		=> false,
				'user_row'		=> $row,
			);
		}

		// Password incorrect - increase login attempts
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_login_attempts = user_login_attempts + 1
			WHERE user_id = ' . (int) $row['user_id'] . '
				AND user_login_attempts < ' . LOGIN_ATTEMPTS_MAX;
		$this->db->sql_query($sql);

		// Give status about wrong password...
		return array(
			'status'		=> ($show_captcha) ? LOGIN_ERROR_ATTEMPTS : LOGIN_ERROR_PASSWORD,
			'error_msg'		=> ($show_captcha) ? 'LOGIN_ERROR_ATTEMPTS' : 'LOGIN_ERROR_PASSWORD',
			'user_row'		=> $row,
		);
	}
}
