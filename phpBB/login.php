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
$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

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

		$username = $HTTP_POST_VARS['username'];
		$password = $HTTP_POST_VARS['password'];

		$sql = "SELECT user_id, username, user_password, user_active
			FROM ".USERS_TABLE."
			WHERE username = '$username'";
		$result = $db->sql_query($sql);
		if(!$result)
		{
			message_die(GENERAL_ERROR, "Error in obtaining userdata : login", __LINE__, __FILE__, $sql);
		}
	
		$rowresult = $db->sql_fetchrow($result);
		if(count($rowresult))
		{
	 		if((md5($password) == $rowresult['user_password']) && $rowresult['user_active'] != 0)
			{	
				$autologin = (isset($HTTP_POST_VARS['autologin'])) ? TRUE : FALSE;

				$session_id = session_begin($rowresult['user_id'], $user_ip, PAGE_INDEX, $session_length, TRUE, $autologin);

				if($session_id)
				{
					if(!empty($HTTP_POST_VARS['forward_page']))
					{
						header("Location: " . append_sid($HTTP_POST_VARS['forward_page']));
					}
					else
					{
						header("Location: " . append_sid("index.$phpEx"));
					}
				}
				else
				{
					message_die(CRITICAL_ERROR, "Couldn't start session : login", __LINE__, __FILE__);
				}
			}
			else
			{
				message_die(GENERAL_MESSAGE, $lang['Error_login']);
			}
		}
		else
		{
			message_die(GENERAL_MESSAGE, $lang['Error_login']);
		}
	}
	else if($HTTP_GET_VARS['submit'] == "logout" && $userdata['session_logged_in'])
	{
		if($userdata['session_logged_in'])
		{
			session_end($userdata['session_id'], $userdata['user_id']);
		}
		if(!empty($HTTP_POST_VARS['forward_page']))
		{
			header("Location: " . append_sid($HTTP_POST_VARS['forward_page']));
		}
		else
		{
			header("Location: " . append_sid("index.$phpEx"));
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
			header("Location: " . append_sid("index.$phpEx"));
		}
	}
}
else
{
	//
	// Do a full login page dohickey if
	// user not already logged in
	//
	if(!$userdata['session_logged_in'])
	{
		$page_title = "Log In";
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			"body" => "login_body.tpl")
		);

		if(isset($HTTP_POST_VARS['forward_page']) || isset($HTTP_GET_VARS['forward_page']))
		{
			$forward_to = $HTTP_SERVER_VARS['QUERY_STRING'];
			
			if(preg_match("/^forward_page=(.*)(&sid=[0-9]*)$|^forward_page=(.*)$/si", $forward_to, $forward_matches))
			{
				$forward_to = ($forward_matches[3]) ? $forward_matches[3] : $forward_matches[1];

				$forward_match = explode("&", $forward_to);

				if(count($forward_match) > 1)
				{
					$forward_page = $forward_match[0] . "?";

					for($i = 1; $i < count($forward_match); $i++)
					{
						$forward_page .= $forward_match[$i];
						if($i < count($forward_match) - 1)
						{
							$forward_page .= "&";
						}
					}
				}
				else
				{
					$forward_page = $forward_match[0];
				}
			}
		}
		else
		{
			$forward_page = "";
		}

		$username = ($userdata['user_id'] != ANONYMOUS) ? $userdata['username'] : "";
	
		$template->assign_vars(array(
			"FORWARD_PAGE" => $forward_page,
			"USERNAME" => $username,

			"L_SEND_PASSWORD" => $lang['Forgotten_password'],

			"U_SEND_PASSWORD" => append_sid("sendpassword.$phpEx")
			)
		);

		$template->pparse("body");

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
	else
	{
		header("Location: index.$phpEx");
	}

}

?>