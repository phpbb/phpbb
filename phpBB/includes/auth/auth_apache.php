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
			WHERE username = '" . $db->sql_escape($username) . "'";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$db->sql_freeresult($result);
			return ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE) ? 0 : $row;
		}
	}

	return false;
}

?>