<?php
/***************************************************************************  
 *                                 error.php
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

function error_die($db, $error_code = "", $error_msg = "") 
{
   if(!$error_msg)
   {
      switch($error_code)
	{
	 case SQL_CONNECT:
	   $db_error = $db->sql_error();
	   $error_msg = "Error: phpBB could not connect to the database. Reason: " . $db_error["message"];
	   break;
	 case BANNED:
	   $error_msg = "You have been banned from this forum.";
	   break;
	 case QUERY_ERROR:
	   $db_error = $db->sql_error();
	   $error_msg = "Error: phpBB could not query the database. Reason: " . $db_error["message"];
	   break;
	 case SESSION_CREATE:
	   $error_msg = "Error creating session. Could not log you in. Please go back and try again.";
	   break;
	}
   }

   die($error_msg);
}
   
	   


?>
