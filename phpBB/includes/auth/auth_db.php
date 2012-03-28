<?php
/**
* Database auth plug-in for phpBB3
*
* Authentication plug-ins is largely down to Sergey Kanareykin, our thanks to him.
*
* This is for authentication via the integrated user table
*
* @package login
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* Login function
*
* @param string $username
* @param string $password
* @param string $ip			IP address the login is taking place from. Used to
*							limit the number of login attempts per IP address.
* @param string $browser	The user agent used to login
* @param string $forwarded_for X_FORWARDED_FOR header sent with login request
* @return array				A associative array of the format
*							array(
*								'status' => status constant
*								'error_msg' => string
*								'user_row' => array
*							)
*/
function login_db($username, $password, $ip = '', $browser = '', $forwarded_for = '')
{
	global $db, $config;

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
		WHERE username_clean = '" . $db->sql_escape($username_clean) . "'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (($ip && !$config['ip_login_limit_use_forwarded']) ||
		($forwarded_for && $config['ip_login_limit_use_forwarded']))
	{
		$sql = 'SELECT COUNT(*) AS attempts
			FROM ' . LOGIN_ATTEMPT_TABLE . '
			WHERE attempt_time > ' . (time() - (int) $config['ip_login_limit_time']);
		if ($config['ip_login_limit_use_forwarded'])
		{
			$sql .= " AND attempt_forwarded_for = '" . $db->sql_escape($forwarded_for) . "'";
		}
		else
		{
			$sql .= " AND attempt_ip = '" . $db->sql_escape($ip) . "' ";
		}

		$result = $db->sql_query($sql);
		$attempts = (int) $db->sql_fetchfield('attempts');
		$db->sql_freeresult($result);

		$attempt_data = array(
			'attempt_ip'			=> $ip,
			'attempt_browser'		=> trim(substr($browser, 0, 149)),
			'attempt_forwarded_for'	=> $forwarded_for,
			'attempt_time'			=> time(),
			'user_id'				=> ($row) ? (int) $row['user_id'] : 0,
			'username'				=> $username,
			'username_clean'		=> $username_clean,
		);
		$sql = 'INSERT INTO ' . LOGIN_ATTEMPT_TABLE . $db->sql_build_array('INSERT', $attempt_data);
		$result = $db->sql_query($sql);
	}
	else
	{
		$attempts = 0;
	}

	if (!$row)
	{
		if ($config['ip_login_limit_max'] && $attempts >= $config['ip_login_limit_max'])
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

	$show_captcha = ($config['max_login_attempts'] && $row['user_login_attempts'] >= $config['max_login_attempts']) ||
		($config['ip_login_limit_max'] && $attempts >= $config['ip_login_limit_max']);

	// If there are too much login attempts, we need to check for an confirm image
	// Every auth module is able to define what to do by itself...
	if ($show_captcha)
	{
		// Visual Confirmation handling
		if (!class_exists('phpbb_captcha_factory'))
		{
			global $phpbb_root_path, $phpEx;
			include ($phpbb_root_path . 'includes/captcha/captcha_factory.' . $phpEx);
		}

		$captcha =& phpbb_captcha_factory::get_instance($config['captcha_plugin']);
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
		// in phpBB2 passwords were used exactly as they were sent, with addslashes applied
		$password_old_format = isset($_REQUEST['password']) ? (string) $_REQUEST['password'] : '';
		$password_old_format = (!STRIP) ? addslashes($password_old_format) : $password_old_format;
		$password_new_format = '';

		set_var($password_new_format, stripslashes($password_old_format), 'string', true);

		if ($password == $password_new_format)
		{
			if (!function_exists('utf8_to_cp1252'))
			{
				global $phpbb_root_path, $phpEx;
				include($phpbb_root_path . 'includes/utf/data/recode_basic.' . $phpEx);
			}

			// cp1252 is phpBB2's default encoding, characters outside ASCII range might work when converted into that encoding
			// plain md5 support left in for conversions from other systems.
			if ((strlen($row['user_password']) == 34 && (phpbb_check_hash(md5($password_old_format), $row['user_password']) || phpbb_check_hash(md5(utf8_to_cp1252($password_old_format)), $row['user_password'])))
				|| (strlen($row['user_password']) == 32  && (md5($password_old_format) == $row['user_password'] || md5(utf8_to_cp1252($password_old_format)) == $row['user_password'])))
			{
				$hash = phpbb_hash($password_new_format);

				// Update the password in the users table to the new format and remove user_pass_convert flag
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_password = \'' . $db->sql_escape($hash) . '\',
						user_pass_convert = 0
					WHERE user_id = ' . $row['user_id'];
				$db->sql_query($sql);

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
				$db->sql_query($sql);

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

			// Update the password in the users table to the new format
			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_password = '" . $db->sql_escape($hash) . "',
					user_pass_convert = 0
				WHERE user_id = {$row['user_id']}";
			$db->sql_query($sql);

			$row['user_password'] = $hash;
		}

		$sql = 'DELETE FROM ' . LOGIN_ATTEMPT_TABLE . '
			WHERE user_id = ' . $row['user_id'];
		$db->sql_query($sql);

		if ($row['user_login_attempts'] != 0)
		{
			// Successful, reset login attempts (the user passed all stages)
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_login_attempts = 0
				WHERE user_id = ' . $row['user_id'];
			$db->sql_query($sql);
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
	$db->sql_query($sql);

	// Give status about wrong password...
	return array(
		'status'		=> ($show_captcha) ? LOGIN_ERROR_ATTEMPTS : LOGIN_ERROR_PASSWORD,
		'error_msg'		=> ($show_captcha) ? 'LOGIN_ERROR_ATTEMPTS' : 'LOGIN_ERROR_PASSWORD',
		'user_row'		=> $row,
	);
}

?>
