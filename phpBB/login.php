<?php
/***************************************************************************  
 *                                 login.php
 *                            -------------------                         
 *   begin                : Saturday, Feb 13, 2001 
 *   copyright            : (C) 2001 The phpBB Group        
 *   email                : support@phpbb.com                           
 *                                                          
 *   $Id$
 *                                                            
 * 
 ***************************************************************************/ 


/***************************************************************************  
 *                                                     
 *   This program is free software; you can redistribute it and/or modify    
 *   it under the terms of the GNU General Public License as published by   
 *   the Free Software Foundation; either version 2 of the License, or  
 *   (at your option) any later version.                      
 *                                                          
 * 
 ***************************************************************************/ 
include('extension.inc');
include('common.'.$phpEx);

//
// Set page ID for session management
//
$userdata = session_pagestart($user_ip, PAGE_LOGIN, $session_length);
init_userprefs($userdata);
//
// End session management
//

if(isset($HTTP_POST_VARS['submit']) || isset($HTTP_GET_VARS['submit']))
{
	if($HTTP_POST_VARS['submit'] == "Login" && !$userdata['session_logged_in'])
	{

		$username = $HTTP_POST_VARS["username"];
		$password = $HTTP_POST_VARS["password"];
		$sql = "SELECT *
			FROM ".USERS_TABLE."
			WHERE username = '$username'";
		$result = $db->sql_query($sql);
		if(!$result)
		{
			error_die(SQL_QUERY, "Error in obtaining userdata : login", __LINE__, __FILE__);
		}
	
		$rowresult = $db->sql_fetchrow($result);
		if(count($rowresult))
		{
			if(md5($password) == $rowresult["user_password"])
			{
				$session_id = session_begin($rowresult["user_id"], $user_ip, PAGE_INDEX, $session_length, 1, $rowresult["user_password"]);
				if($session_id)
				{
					header("Location: index.$phpEx");
				}
				else
				{
					error_die(GENERAL_ERROR, "Couldn't start session : login", __LINE__, __FILE__);
				}
			}
			else
			{
				error_die(LOGIN_FAILED);
			}
		}
		else
		{
			error_die(LOGIN_FAILED);
		}
	}
	else if($HTTP_GET_VARS['submit'] == "logout" && $userdata['session_logged_in'])
	{
		if($userdata['session_logged_in'])
		{
			session_end($userdata["session_id"], $userdata["user_id"]);
		}
		header("Location: index.$phpEx");
	}
	else
	{
		header("Location: index.$phpEx");
	}
}
else
{
	//
	// Do a full login page dohickey
	//
	$pagetype = "login";
	$page_title = "Log In";

	include('includes/page_header.'.$phpEx);

	$template->assign_vars(array(
		"PHPEX" => $phpEx,
		"PHP_SELF" => $PHP_SELF,

		"T_TR_COLOR1" => "#",
		"T_TR_COLOR2" => "#",
		"T_TR_COLOR3" => "#",
		"T_TH_COLOR1" => "#495FA8",
		"T_TH_COLOR2" => "#",
		"T_TH_COLOR3" => "#",
		"T_TD_COLOR1" => "#000000",
		"T_TD_COLOR2" => "#CCCCCC",
		"T_TD_COLOR3" => "#DDDDDD",
		"T_FONTFACE1" => "sans-serif",
		"T_FONTFACE2" => "",
		"T_FONTFACE3" => "",
		"T_FONTSIZE1" => "2",
		"T_FONTSIZE2" => "",
		"T_FONTSIZE3" => "",
		"T_FONTCOLOR1" => "#FFFFFF",
		"T_FONTCOLOR2" => "#000000",
		"T_FONTCOLOR3" => "",

		"L_USERNAME" => $l_username,
		"L_PASSWORD" => $l_password,
		"L_SEND_PASSWORD" => $l_resend_password,
		"L_AUTO_LOGIN" => $l_autologin,
		"L_LOGIN" => $l_login,

		"USERNAME" => $userdata['username']
		)
	);

	$template->pparse("body");

	include('includes/page_tail.'.$phpEx);

}

?>
