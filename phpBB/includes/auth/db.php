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

class phpbb_auth_db /* extends phpbb_auth */
{
	/**
	* Login function
	*/
	function login(&$username, &$password)
	{
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

		$sql = 'SELECT user_id, username, user_password, user_passchg, user_pass_convert, user_email, user_type, user_login_attempts
			FROM ' . USERS_TABLE . "
			WHERE username_clean = '" . phpbb::$db->sql_escape(utf8_clean_string($username)) . "'";
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if (!$row)
		{
			return array(
				'status'	=> LOGIN_ERROR_USERNAME,
				'error_msg'	=> 'LOGIN_ERROR_USERNAME',
				'user_row'	=> array('user_id' => ANONYMOUS),
			);
		}

		// If there are too much login attempts, we need to check for an confirm image
		// Every auth module is able to define what to do by itself...
		if (phpbb::$config['max_login_attempts'] && $row['user_login_attempts'] >= phpbb::$config['max_login_attempts'])
		{
			$confirm_id = request_var('confirm_id', '');
			$confirm_code = request_var('confirm_code', '');

			// Visual Confirmation handling
			if (!$confirm_id)
			{
				return array(
					'status'		=> LOGIN_ERROR_ATTEMPTS,
					'error_msg'		=> 'LOGIN_ERROR_ATTEMPTS',
					'user_row'		=> $row,
				);
			}
			else
			{
				$captcha = phpbb_captcha_factory::get_instance(phpbb::$config['captcha_plugin']);
				$captcha->init(CONFIRM_LOGIN);
				$vc_response = $captcha->validate();
				if ($vc_response)
				{
					return array(
							'status'		=> LOGIN_ERROR_ATTEMPTS,
							'error_msg'		=> 'LOGIN_ERROR_ATTEMPTS',
							'user_row'		=> $row,
					);
				}
			}
		}

		// Check password ...
		if (!$row['user_pass_convert'] && phpbb::$security->check_password($password, $row['user_password']))
		{
			// Check for old password hash...
			if (strlen($row['user_password']) == 32)
			{
				$hash = phpbb::$security->hash_password($password);

				// Update the password in the users table to the new format
				$sql = 'UPDATE ' . USERS_TABLE . "
					SET user_password = '" . phpbb::$db->sql_escape($hash) . "',
						user_pass_convert = 0
					WHERE user_id = {$row['user_id']}";
				phpbb::$db->sql_query($sql);

				$row['user_password'] = $hash;
			}

			if ($row['user_login_attempts'] != 0)
			{
				// Successful, reset login attempts (the user passed all stages)
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_login_attempts = 0
					WHERE user_id = ' . $row['user_id'];
				phpbb::$db->sql_query($sql);
			}

			// User inactive...
			if ($row['user_type'] == phpbb::USER_INACTIVE || $row['user_type'] == phpbb::USER_IGNORE)
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
			WHERE user_id = ' . $row['user_id'];
		phpbb::$db->sql_query($sql);

		// Give status about wrong password...
		return array(
			'status'		=> LOGIN_ERROR_PASSWORD,
			'error_msg'		=> 'LOGIN_ERROR_PASSWORD',
			'user_row'		=> $row,
		);
	}
}

?>