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
			if((md5($password) == $rowresult["user_password"]) && $rowresult['user_active'] != 0)
			{	
				$autologin = (isset($HTTP_POST_VARS['autologin'])) ? TRUE : FALSE;

				$session_id = session_begin($rowresult["user_id"], $user_ip, PAGE_INDEX, $session_length, TRUE, $autologin);
				if($session_id)
				{
					if(!empty($HTTP_POST_VARS['forward_page']))
					{
						header(append_sid("Location: ".$HTTP_POST_VARS['forward_page']));
					}
					else
					{
						header(append_sid("Location: index.$phpEx"));
					}
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
		if(!empty($HTTP_POST_VARS['forward_page']))
		{
			header(append_sid("Location: ".$HTTP_POST_VARS['forward_page']));
		}
		else
		{
			header(append_sid("Location: index.$phpEx"));
		}
	}
	else
	{
		if(!empty($HTTP_POST_VARS['forward_page']))
		{
			header(append_sid("Location: ".$HTTP_POST_VARS['forward_page']));
		}
		else
		{
			header(append_sid("Location: index.$phpEx"));
		}
	}
}
else
{
	//
	// Do a full login page dohickey
	//
	$page_title = "Log In";
	include('includes/page_header.'.$phpEx);
	$template->set_filenames(
		array(
			"body" => "login_body.tpl",
		)
	);
	if(isset($HTTP_POST_VARS['mode']) || isset($HTTP_GET_VARS['mode']))
	{
		$mode = (isset($HTTP_POST_VARS['mode'])) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
		$forward_page .= "?mode=".$mode;
	}

	$username = ($userdata['user_id'] != ANONYMOUS) ? $userdata['username'] : "";

	$template->assign_vars(array(
		"L_SEND_PASSWORD" => $l_resend_password,

		"FORWARD_PAGE" => $forward_page,
		"USERNAME" => $username,

		"U_SEND_PASSWORD" => append_sid("sendpassword.".$phpEx)
		)
	);

	$template->pparse("body");

	include('includes/page_tail.'.$phpEx);

}

?>