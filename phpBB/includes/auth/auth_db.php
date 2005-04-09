<?php
/**
* Database auth plug-in for phpBB3
*
* Authentication plug-ins is largely down to Sergey Kanareykin, our thanks to him.
*
* This is for authentication via the integrated user table
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
function login_db(&$username, &$password)
{
	global $db, $config;

	$sql = 'SELECT user_id, username, user_password, user_passchg, user_email, user_type
		FROM ' . USERS_TABLE . "
		WHERE username = '" . $db->sql_escape($username) . "'";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$db->sql_freeresult($result);
		if (md5($password) == $row['user_password'])
		{
			return ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE) ? 0 : $row;
		}
	}

	return false;
}

?>