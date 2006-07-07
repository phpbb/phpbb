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
* Login function
*/
function login_db(&$username, &$password)
{
	global $db, $config;

	$sql = 'SELECT user_id, username, user_password, user_passchg, user_email, user_type, user_login_attempts
		FROM ' . USERS_TABLE . "
		WHERE username = '" . $db->sql_escape($username) . "'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

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
	if ($config['max_login_attempts'] && $row['user_login_attempts'] > $config['max_login_attempts'])
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
			global $user;

			$sql = 'SELECT code
				FROM ' . CONFIRM_TABLE . "
				WHERE confirm_id = '" . $db->sql_escape($confirm_id) . "'
					AND session_id = '" . $db->sql_escape($user->session_id) . "'
					AND confirm_type = " . CONFIRM_LOGIN;
			$result = $db->sql_query($sql);
			$confirm_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($confirm_row)
			{
				if (strcasecmp($confirm_row['code'], $confirm_code) === 0)
				{
					$sql = 'DELETE FROM ' . CONFIRM_TABLE . "
						WHERE confirm_id = '" . $db->sql_escape($confirm_id) . "'
							AND session_id = '" . $db->sql_escape($user->session_id) . "'
							AND confirm_type = " . CONFIRM_LOGIN;
					$db->sql_query($sql);
				}
				else
				{
					return array(
						'status'		=> LOGIN_ERROR_ATTEMPTS,
						'error_msg'		=> 'CONFIRM_CODE_WRONG',
						'user_row'		=> $row,
					);
				}
			}
			else
			{
				return array(
					'status'		=> LOGIN_ERROR_ATTEMPTS,
					'error_msg'		=> 'CONFIRM_CODE_WRONG',
					'user_row'		=> $row,
				);
			}
		}
	}

	// Password correct...
	if (md5($password) == $row['user_password'])
	{
		// Successful, reset login attempts (the user passed all stages)
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_login_attempts = 0
			WHERE user_id = ' . $row['user_id'];
		$db->sql_query($sql);

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
		WHERE user_id = ' . $row['user_id'];
	$db->sql_query($sql);

	// Give status about wrong password...
	return array(
		'status'		=> LOGIN_ERROR_PASSWORD,
		'error_msg'		=> 'LOGIN_ERROR_PASSWORD',
		'user_row'		=> $row,
	);
}

?>