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
   global $template, $phpEx, $default_lang;

   if(!$template->get("overall_header"))
     {
	if(!empty($default_lang))
	  {
	     include('language/lang_'.$default_lang.'.'.$phpEx);
	  }
	else
	  {
	     include('language/lang_english.'.$phpEx);
	  }
	include('page_header.'.$phpEx);
     }
   if(!$error_msg)
   {
      switch($error_code)
	{
	 case GENERAL_ERROR:
	   if(!$error_msg)
	     {
		$error_msg = "An Error Occured";
	     }
	   break;
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
	 case NO_POSTS:
	   $error_msg = "There are no posts in this forum. Click on the 'Post New Topic' link on this page to post one.";
	   break;
	 case LOGIN_FAILED:
	   $error_msg = "Login Failed. You have specified an incorrect username or password, please go back and try again.";
	   break;
	}
   }
   if(DEBUG)
     {
	//$error_msg .= "<br>Line number: ".__LINE__."<br>In File: ".__FILE__;
     }
   $template->set_file(array("error_body" => "error_body.tpl"));
   $template->set_var(array("ERROR_MESSAGE" => $error_msg));
   $template->pparse("output", "error_body");
   include('page_tail.'.$phpEx);
   exit();
}
   
	   


?>
