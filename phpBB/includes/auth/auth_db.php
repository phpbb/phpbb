<?php

// Authentication plug-ins is largely down to Sergey Kanareykin, our thanks to him.
//
// This is for authentication via the integrated user table
//
// You can do any kind of checking you like here ... the return data format is
// either the resulting row of user information, an integer zero (indicating an
// inactive user) or some error string
function login_db(&$username, &$password)
{
	global $db, $config;

	$sql = "SELECT user_id, username, user_password, user_email, user_active
		FROM " . USERS_TABLE . "
		WHERE username = '" . $db->sql_escape($username) . "'";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$db->sql_freeresult($result);
		if (md5($password) == $row['user_password'])
		{
			return (empty($row['user_active'])) ? 0 : $row;
		}
	}

	return false;
}

?>