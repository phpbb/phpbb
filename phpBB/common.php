<?php
/***************************************************************************  
 *                                common.php 
 *                            -------------------                         
 *   begin                : Saturday, Feb 23, 2001 
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

include('config.'.$phpEx);

// Find Users real IP (if possible)
$ip = ($HTTP_X_FORWARDED_FOR) ? $HTTP_X_FORWARDED_FOR : $REMOTE_ADDR;
define("USER_IP",$ip);
unset($ip);

include('template.inc');
// Setup what template to use. Currently just use default
$template = new Template("./templates/Default", "keep");

include('functions/error.'.$phpEx);
include('functions/sessions.'.$phpEx);
include('functions/auth.'.$phpEx);
include('functions/functions.'.$phpEx);
include('db.'.$phpEx);

// Initalize these variables to keep them safe.
$user_logged_in = 0;
$logged_in = 0;
$userdata = Array();

// Setup forum wide options.
// This is also the first DB query/connect
$sql = "SELECT * FROM ".CONFIG_TABLE." WHERE selected = 1";
if(!$result = $db->sql_query($sql))
{
	error_die($db, SQL_CONNECT);
}
else  
{
	$config = $db->sql_fetchrowset($result);
	$sitename = stripslashes($config[0]["sitename"]);
	$allow_html = $config[0]["allow_html"];
	$allow_bbcode = $config[0]["allow_bbcode"];
	$allow_sig = $config[0]["allow_sig"];
	$allow_namechange = $config[0]["allow_namechange"];
	$posts_per_page = $config[0]["posts_per_page"];
	$hot_threshold = $config[0]["hot_threshold"];
	$topics_per_page = $config[0]["topics_per_page"];
	$override_user_themes = $config[0]["override_themes"];
	$email_sig = stripslashes($config[0]["email_sig"]);
	$email_from = $config[0]["email_from"];
	$default_lang = $config[0]["default_lang"];
	$sys_lang = $default_lang;            
}

// Check if user is banned
if(!auth("ip ban", $db, "", "", "", "", "", USER_IP, "", "", "")) 
{
	error_die($db, BANNED);
}

if(isset($HTTP_COOKIE_VARS[$session_cookie])) 
{
	$sessid = $HTTP_COOKIE_VARS[$session_cookie];
	$userid = get_userid_from_session($sessid, $session_cookie_time, USER_IP, $db);

	if ($userid)
	{
		$user_logged_in = 1;
		update_session_time($sessid, $db);
	
		if(!auth("username ban", $db, $userid, "", "", "", "", "", "", "", ""))
		{
			error_die($db, BANNED);
		}
		$userdata = get_userdata_from_id($userid, $db);
	}
}            

// If the user isn't logged in check if they have a user ID cookie.
if (!$user_logged_in)
{
	if(isset($HTTP_COOKIE_VARS[$cookie_name]))
	{
		$userdata = get_userdata_from_id($HTTP_COOKIE_VARS["$cookie_name"], $db);
		if(!auth("username ban", $db, $userdata["user_id"], "", "", "", "", "", "", "", ""))
		{
			error_die($db, BANNED);
		}
	}
}

?>
