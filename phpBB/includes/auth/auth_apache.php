<?php

//
// Authentication plug-ins is largely down to
// Sergey Kanareykin, our thanks to him. 
//
function login_apache(&$username, &$password)
{
	global $HTTP_SERVER_VARS, $HTTP_ENV_VARS;

	$php_auth_user = ( !empty($HTTP_SERVER_VARS['PHP_AUTH_USER']) ) ? $HTTP_SERVER_VARS['PHP_AUTH_USER'] : $HTTP_GET_VARS['PHP_AUTH_USER']
	$php_auth_pw = ( !empty($HTTP_SERVER_VARS['PHP_AUTH_PW']) ) ? $HTTP_SERVER_VARS['PHP_AUTH_PW'] : $HTTP_GET_VARS['PHP_AUTH_PW']

	if ( $php_auth_user && $php_auth_pw )
	{
		$sql = "SELECT user_id, username, user_password, user_email, user_active  
			FROM " . USERS_TABLE . "
			WHERE username = '" . str_replace("\'", "''", $username) . "'";
		$result = $db->sql_query($sql);

		return ( $row = $db->sql_fetchrow($result) ? $row : false;
	}

	return false;
}

?>