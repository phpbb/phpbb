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
	global $db, $template, $board_config, $theme, $lang, $phpEx, $phpbb_root_path;

	if(!defined("HEADER_INC"))
	{
		if(!empty($board_config['default_lang']))
		{
			include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '.'.$phpEx);
		}
		else
		{
			include($phpbb_root_path . 'language/lang_english.'.$phpEx);
		}
		if(!$template)
		{
			$template = new Template($phpbb_root_path . "templates/Default");
		}
		if(!$theme)
		{
			$theme = setuptheme(1);
		}
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);
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
				if(!$message_title)
				{
					$message_title = "General Error";
				}
				break;

			case SQL_CONNECT:
				$message_title = "General Error";
				$error_msg = "Couldn't connect to database!";
				break;

			case BANNED:
				$message_title = $lang['Information'];
				$error_msg = "You have been banned from this forum.";
				break;
			
			case SQL_QUERY:
				break;
			
			case SESSION_CREATE:
				$message_title = "General Error";
				$error_msg = "Error creating session<br>Could not log you in, please go back and try again.";
				break;
			
			case NO_POSTS:
				$message_title = $lang['Information'];
				$error_msg = "There are no posts in this forum<br>Click on the <b>Post New Topic</b> link on this page to post one.";
				break;

			case LOGIN_FAILED:
				$message_title = $lang['Information'];
				$error_msg = "Login Failed<br>You have specified an incorrect/inactive username or invalid password, please go back and try again.";
				break;
		}
	}
	if(DEBUG)
	{
		if($line != "" && $file != "")
			$error_msg .= "<br /><br /><u>DEBUG INFO</u></br /><br>Line: ".$line."<br />File: ".$file;
	}

	$template->set_filenames(array(
		"message_body" => "error_body.tpl")
	);
	$template->assign_vars(array(
		"ERROR_MESSAGE" => $error_msg)
	);
	$template->pparse("message_body");

	include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

	exit();
}

?>