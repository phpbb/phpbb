<?php

// Authentication plug-ins is largely down to Sergey Kanareykin, our thanks to him.
//
// This is for initial authentication via Apaches basic realm authentication methods,
// user data is then obtained from the integrated user table
//
// You can do any kind of checking you like here ... the return data format is
// either the resulting row of user information, an integer zero (indicating an
// inactive user) or some error string
function login_apache(&$username, &$password)
{
	global $db;

	$php_auth_user = (!empty($_SERVER['PHP_AUTH_USER'])) ? $_SERVER['PHP_AUTH_USER'] : $_GET['PHP_AUTH_USER'];
	$php_auth_pw = (!empty($_SERVER['PHP_AUTH_PW'])) ? $_SERVER['PHP_AUTH_PW'] : $_GET['PHP_AUTH_PW'];

	if ($php_auth_user && $php_auth_pw)
	{
		$sql = "SELECT user_id, username, user_password, user_email, user_active
			FROM " . USERS_TABLE . "
			WHERE username = '" . $db->sql_escape($username) . "'";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$db->sql_freeresult($result);
			return (empty($row['user_active'])) ? 0 : $row;
		}
	}

	return false;
}

?>