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

function error_die($error_code, $error_msg = "", $line = "", $file = "") 
{
	global $db, $template, $phpEx, $default_lang;
	global $table_bgcolor, $color1;
	global $starttime, $phpbbversion;

	if(!defined("HEADER_INC"))
	{
		if(!empty($default_lang))
		{
			include('language/lang_'.$default_lang.'.'.$phpEx);
		}
		else
		{
			include('language/lang_english.'.$phpEx);
		}
		include('includes/page_header.'.$phpEx);
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
				$error_msg .= "<br />SQL connect error - " . $db_error["message"];
				break;

			case BANNED:
				$error_msg = "You have been banned from this forum.";
				break;
			
			case SQL_QUERY:
				$db_error = $db->sql_error();
				$error_msg .= "<br />SQL query error - ".$db_error["message"];
				break;
			
			case SESSION_CREATE:
				$error_msg = "Error creating session. Could not log you in. Please go back and try again.";
				break;
			
			case NO_POSTS:
				$error_msg = "There are no posts in this forum. Click on the <b>Post New Topic</b> link on this page to post one.";
				break;

			case LOGIN_FAILED:
				$error_msg = "Login Failed. You have specified an incorrect/inactive username or invalid password, please go back and try again.";
				break;
		}
	}
	if(DEBUG)
	{
		if($line != "" && $file != "")
			$error_msg .= "<br /><br /><u>DEBUG INFO</u></br /><br>Line: ".$line."<br />File: ".$file;
	}

	$template->set_filenames(array("error_body" => "error_body.tpl"));
	$template->assign_vars(array("ERROR_MESSAGE" => $error_msg));
	$template->pparse("error_body");

	include('includes/page_tail.'.$phpEx);

	exit();
}

?>
