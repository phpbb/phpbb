<?php
/***************************************************************************  
 *                                message.php
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

function message_die($msg_code, $msg_text = "", $msg_title = "", $err_line = "", $err_file = "", $sql = "") 
{

	global $db, $template, $board_config, $theme, $lang, $phpEx;
	global $userdata, $user_ip, $session_length;
	global $starttime;

	if(empty($userdata) && ( $msg_code == GENERAL_MESSAGE || $msg_code == GENERAL_ERROR ) )
	{
		//
		// Start session management
		//
		$userdata = session_pagestart($user_ip, PAGE_INDEX, $session_length);
		init_userprefs($userdata);
		//
		// End session management
		//
	}
	
	if(!defined("HEADER_INC"))
	{
		if(!empty($board_config['default_lang']))
		{
			include('language/lang_' . $board_config['default_lang'] . '.'.$phpEx);
		}
		else
		{
			include('language/lang_english.'.$phpEx);
		}
		if(!$template)
		{
			$template = new Template("templates/Default");
		}
		if(!$theme)
		{
			$theme = setuptheme(1);
		}
		if($msg_code != CRITICAL_ERROR)
		{
			include('includes/page_header.'.$phpEx);
		}
	}

	switch($msg_code)
	{
		case GENERAL_MESSAGE:
			if($msg_title == "")
			{
				$msg_title = $lang['Information'];
			}
			break;

		case CRITICAL_MESSAGE:
			if($msg_title == "")
			{
				$msg_title = $lang['Critical_Information'];
			}
			break;

		case GENERAL_ERROR:
			if($msg_text == "")
			{
				$msg_text = $lang['An_error_occured'];
			}
			if($msg_title == "")
			{
				$msg_title = $lang['General_Error'];
			}

		case CRITICAL_ERROR:
			if($msg_text == "")
			{
				$msg_text = $lang['A_critical_error'];
			}
			if($msg_title == "")
			{
				$msg_title = $lang['Critical_Error'];
			}
			break;
	}
	if(DEBUG && ( $msg_code == GENERAL_ERROR || $msg_code == CRITICAL_ERROR ) )
	{
		$sql_error = $db->sql_error();

		$debug_text = "";
		if($sql_error['message'] != "")
		{
			$debug_text .= "<br /><br />SQL Error : " . $sql_error['code'] . " " . $sql_error['message'];
		}
		if($sql != "")
		{
			$debug_text .= "<br /><br />$sql";
		}
		if($err_line != "" && $err_file != "")
		{
			$debug_text .= "</br /><br />Line : " . $err_line . "<br />File : " . $err_file;
		}
		if($debug_text != "")
		{
			$msg_text = $msg_text . "<br /><br /><b><u>DEBUG MODE</u></b>" . $debug_text;
		}
	}

	$template->set_filenames(array(
		"message_body" => "message_body.tpl")
	);
	$template->assign_vars(array(
		"MESSAGE_TITLE" => $msg_title,
		"MESSAGE_TEXT" => $msg_text)
	);
	$template->pparse("message_body");

	include('includes/page_tail.'.$phpEx);

	exit();
}

?>