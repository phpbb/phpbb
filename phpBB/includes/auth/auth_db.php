<?php

//
// Authentication plug-ins is largely down to
// Sergey Kanareykin, our thanks to him. 
//
function login_db(&$username, &$password)
{
	global $db, $board_config;

	$sql = "SELECT user_id, username, user_password, user_email, user_active  
		FROM " . USERS_TABLE . "
		WHERE username = '" . str_replace("\'", "''", $username) . "'";
	$result = $db->sql_query($sql);

	if ( $row = $db->sql_fetchrow($result) )
	{
		$db->sql_freeresult($result);

		if ( md5($password) == $row['user_password'] && $row['user_active'] )
		{
			return $row;
		}
	}

	return false;
}

?>