<?php
/**
* Apache auth plug-in for phpBB3
*
* Authentication plug-ins is largely down to Sergey Kanareykin, our thanks to him.
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
* Checks whether the user is identified to apache
* Only allow changing authentication to apache if the user is identified
* Called in acp_board while setting authentication plugins
*
* @return boolean|string false if the user is identified and else an error message
*/
function init_apache()
{
	global $user;

	if (!isset($_SERVER['PHP_AUTH_USER']) || $user->data['username'] !== $_SERVER['PHP_AUTH_USER'])
	{
		return $user->lang['APACHE_SETUP_BEFORE_USE'];
	}
	return false;
}

/**
* Login function
*/
function login_apache(&$username, &$password)
{
	global $db;

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

	if (!isset($_SERVER['PHP_AUTH_USER']))
	{
		return array(
			'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
			'error_msg'		=> 'LOGIN_ERROR_EXTERNAL_AUTH_APACHE',
			'user_row'		=> array('user_id' => ANONYMOUS),
		);
	}

	$php_auth_user = $_SERVER['PHP_AUTH_USER'];
	$php_auth_pw = $_SERVER['PHP_AUTH_PW'];

	if (!empty($php_auth_user) && !empty($php_auth_pw))
	{
		if ($php_auth_user !== $username)
		{
			return array(
				'status'	=> LOGIN_ERROR_USERNAME,
				'error_msg'	=> 'LOGIN_ERROR_USERNAME',
				'user_row'	=> array('user_id' => ANONYMOUS),
			);
		}

		$sql = 'SELECT user_id, username, user_password, user_passchg, user_email, user_type
			FROM ' . USERS_TABLE . "
			WHERE username = '" . $db->sql_escape($php_auth_user) . "'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			// User inactive...
			if ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE)
			{
				return array(
					'status'		=> LOGIN_ERROR_ACTIVE,
					'error_msg'		=> 'ACTIVE_ERROR',
					'user_row'		=> $row,
				);
			}

			// Successful login...
			return array(
				'status'		=> LOGIN_SUCCESS,
				'error_msg'		=> false,
				'user_row'		=> $row,
			);
		}

		// this is the user's first login so create an empty profile
		return array(
			'status'		=> LOGIN_SUCCESS_CREATE_PROFILE,
			'error_msg'		=> false,
			'user_row'		=> user_row_apache($php_auth_user, $php_auth_pw),
		);
	}

	// Not logged into apache
	return array(
		'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
		'error_msg'		=> 'LOGIN_ERROR_EXTERNAL_AUTH_APACHE',
		'user_row'		=> array('user_id' => ANONYMOUS),
	);
}

/**
* Autologin function
*
* @return array containing the user row or empty if no auto login should take place
*/
function autologin_apache()
{
	global $db;

	if (!isset($_SERVER['PHP_AUTH_USER']))
	{
		return array();
	}

	$php_auth_user = $_SERVER['PHP_AUTH_USER'];
	$php_auth_pw = $_SERVER['PHP_AUTH_PW'];

	if (!empty($php_auth_user) && !empty($php_auth_pw))
	{
		set_var($php_auth_user, $php_auth_user, 'string', true);
		set_var($php_auth_pw, $php_auth_pw, 'string', true);

		$sql = 'SELECT *
			FROM ' . USERS_TABLE . "
			WHERE username = '" . $db->sql_escape($php_auth_user) . "'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			return ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE) ? array() : $row;
		}

		if (!function_exists('user_add'))
		{
			global $phpbb_root_path, $phpEx;

			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}

		// create the user if he does not exist yet
		user_add(user_row_apache($php_auth_user, $php_auth_pw));

		$sql = 'SELECT *
			FROM ' . USERS_TABLE . "
			WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($php_auth_user)) . "'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			return $row;
		}
	}

	return array();
}

/**
* This function generates an array which can be passed to the user_add function in order to create a user
*/
function user_row_apache($username, $password)
{
	global $db, $config, $user;
	// first retrieve default group id
	$sql = 'SELECT group_id
		FROM ' . GROUPS_TABLE . "
		WHERE group_name = '" . $db->sql_escape('REGISTERED') . "'
			AND group_type = " . GROUP_SPECIAL;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$row)
	{
		trigger_error('NO_GROUP');
	}

	// generate user account data
	return array(
		'username'		=> $username,
		'user_password'	=> phpbb_hash($password),
		'user_email'	=> '',
		'group_id'		=> (int) $row['group_id'],
		'user_type'		=> USER_NORMAL,
		'user_ip'		=> $user->ip,
		'user_new'		=> ($config['new_member_post_limit']) ? 1 : 0,
	);
}

/**
* The session validation function checks whether the user is still logged in
*
* @return boolean true if the given user is authenticated or false if the session should be closed
*/
function validate_session_apache(&$user)
{
	// Check if PHP_AUTH_USER is set and handle this case
	if (isset($_SERVER['PHP_AUTH_USER']))
	{
		$php_auth_user = '';
		set_var($php_auth_user, $_SERVER['PHP_AUTH_USER'], 'string', true);

		return ($php_auth_user === $user['username']) ? true : false;
	}

	// PHP_AUTH_USER is not set. A valid session is now determined by the user type (anonymous/bot or not)
	if ($user['user_type'] == USER_IGNORE)
	{
		return true;
	}

	return false;
}
