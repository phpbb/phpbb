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

//
// This function gets called to output any message or error
// that doesn't require additional output from the calling 
// page. 
//
// $msg_code takes one of four constant values:
//
// GENERAL_MESSAGE -> Use for any simple text message, eg.
// results of an operation, authorisation failures, etc.
//
// GENERAL ERROR -> Use for any error which occurs _AFTER_
// the common.php include and session code, ie. most errors
// in pages/functions
//
// CRITICAL_MESSAGE -> Only currently used to announce a user
// has been banned, can be used where session results cannot
// be relied upon to exist
//
// CRITICAL_ERROR -> Used whenever a DB connection cannot be
// guaranteed and/or sessions have failed. Shouldn't be used
// in general pages/functions (it results in a simple echo'd
// statement, no templates are used)
//
function message_die($msg_code, $msg_text = "", $msg_title = "", $err_line = "", $err_file = "", $sql = "") 
{
	global $db, $template, $board_config, $theme, $lang, $phpEx, $phpbb_root_path;
	global $userdata, $user_ip, $session_length;
	global $starttime;

	$sql_store = $sql;

	if( empty($userdata) && ( $msg_code == GENERAL_MESSAGE || $msg_code == GENERAL_ERROR ) )
	{
		$userdata = session_pagestart($user_ip, PAGE_INDEX, $session_length);
		init_userprefs($userdata);
	}

	//
	// If the header hasn't been output then do it
	//
	if( !defined("HEADER_INC") && $msg_code != CRITICAL_ERROR )
	{
		if( !empty($board_config['default_lang']) )
		{
			include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '.'.$phpEx);
		}
		else
		{
			include($phpbb_root_path . 'language/lang_english.'.$phpEx);
		}

		if( empty($template) )
		{
			$template = new Template($phpbb_root_path . "templates/Default");
		}

		if( empty($theme) )
		{
			$theme = setuptheme(1);
		}

		//
		// Load the Page Header
		//
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);
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
			//
			// Critical errors mean we cannot rely on _ANY_ DB information being
			// available so we're going to dump out a simple echo'd statement
			//
			include($phpbb_root_path . 'language/lang_english.'.$phpEx);

			if($msg_text == "")
			{
				$msg_text = $lang['A_critical_error'];
			}

			if($msg_title == "")
			{
				$msg_title = "phpBB : <b>" . $lang['Critical_Error'] . "</b>";
			}
			break;
	}

	//
	// Add on DEBUG info if we've enabled debug mode and this is an error. This
	// prevents debug info being output for general messages should DEBUG be
	// set TRUE by accident (preventing confusion for the end user!)
	//
	if(DEBUG && ( $msg_code == GENERAL_ERROR || $msg_code == CRITICAL_ERROR ) )
	{
		$sql_error = $db->sql_error();

		$debug_text = "";

		if($sql_error['message'] != "")
		{
			$debug_text .= "<br /><br />SQL Error : " . $sql_error['code'] . " " . $sql_error['message'];
		}

		if($sql_store != "")
		{
			$debug_text .= "<br /><br />$sql_store";
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

	if( $msg_code != CRITICAL_ERROR )
	{
		$template->set_filenames(array(
			"message_body" => "message_body.tpl")
		);
		$template->assign_vars(array(
			"MESSAGE_TITLE" => $msg_title,
			"MESSAGE_TEXT" => $msg_text)
		);
		$template->pparse("message_body");

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
	else
	{
		echo "<html>\n<body>\n" . $msg_title . "\n<br /><br />\n" . $msg_text . "</body>\n</html>";
	}

	exit;

}

?>