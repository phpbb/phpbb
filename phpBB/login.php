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
			error_die($db, "Error in obtaining userdata : login");
		}
	
		$rowresult = $db->sql_fetchrow($result);
		if(count($rowresult))
		{
			if(md5($password) == $rowresult["user_password"])
			{
				$session_id = session_begin($db, $rowresult["user_id"], $user_ip, $session_length, 1, $rowresult["user_password"]);
				if($session_id)
				{
					header("Location: index.$phpEx");
				}
				else
				{
					error_die($db, "Couldn't start session : login");
				}
			}
			else
			{
				error_die($db, LOGIN_FAILED);
			}
		}
		else
		{
			error_die($db, LOGIN_FAILED);
		}
	}
	else if($HTTP_GET_VARS['submit'] == "logout" && $userdata['session_logged_in'])
	{
		if($userdata['session_logged_in'])
		{
			session_end($db, $userdata["session_id"], $userdata["user_id"]);
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
	header("Location: index.$phpEx");
}

?>
