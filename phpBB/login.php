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

if($submit)
{
   $userdata = get_userdata($username, $db);
   if($userdata["error"])
     {
	error_die($db, LOGIN_FAILED);
     }
   else 
     {
	if(!auth("login", $db))
	  {
	     error_die($db, LOGIN_FAILED);
	  }
	else 
	  {
	     $sessid = new_session($userdata[user_id], USER_IP, $session_cookie_time, $db);
	     set_session_cookie($sessid, $session_cookie_time, $session_cookie, "", "", 0);
	     header("Location: index.$phpEx");
	  }
     }
}
else if($logout)
{
   if($user_logged_in)
     {
	end_user_session($userdata["user_id"], $db);
     }
   header("Location: index.$phpEx");
}
   
?>
