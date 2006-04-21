<?php
/**
* Apache auth plug-in for phpBB3
*
* Authentication plug-ins is largely down to Sergey Kanareykin, our thanks to him.
*
* This is for initial authentication via Apaches basic realm authentication methods,
* user data is then obtained from the integrated user table
*
* You can do any kind of checking you like here ... the return data format is
* either the resulting row of user information, an integer zero (indicating an
* inactive user) or some error string
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
function login_apache(&$username, &$password)
{
	global $db;

	$php_auth_user = $_SERVER['PHP_AUTH_USER'];
	$php_auth_pw = $_SERVER['PHP_AUTH_PW'];

	if ((!empty($php_auth_user)) && (!empty($php_auth_pw)))
	{
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

		// the user does not exist
		return array(
			'status'	=> LOGIN_ERROR_USERNAME,
			'error_msg'	=> 'LOGIN_ERROR_USERNAME',
			'user_row'	=> array('user_id' => ANONYMOUS),
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

	$php_auth_user = $_SERVER['PHP_AUTH_USER'];
	$php_auth_pw = $_SERVER['PHP_AUTH_PW'];

	if ((!empty($php_auth_user)) && (!empty($php_auth_pw)))
	{
		$sql = 'SELECT *
			FROM ' . USERS_TABLE . "
			WHERE username = '" . $db->sql_escape($php_auth_user) . "'";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$db->sql_freeresult($result);
			return ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE) ? array() : $row;
		}
	}

	return array();
}

/**
* The session validation function checks whether the user is still logged in
*
* @return boolean true if the given user is authenticated or false if the session should be closed
*/
function validate_session_apache(&$user)
{
	return ($_SERVER['PHP_AUTH_USER'] == $user['username']) ? true : false;
}

?>