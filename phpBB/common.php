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
include('includes/constants.'.$phpEx);

// Find Users real IP (if possible)
$user_ip = ($HTTP_X_FORWARDED_FOR) ? $HTTP_X_FORWARDED_FOR : $REMOTE_ADDR;

// Setup what template to use. Currently just use default
include('includes/template.inc');
$template = new Template("./templates/Default");

include('functions/error.'.$phpEx);
include('functions/sessions.'.$phpEx);
include('functions/auth.'.$phpEx);
include('functions/functions.'.$phpEx);
include('includes/db.'.$phpEx);

// Initalize to keep safe
$userdata = Array();

// Setup forum wide options.
// This is also the first DB query/connect
$sql = "SELECT *
	FROM ".CONFIG_TABLE."
	WHERE selected = 1";
if(!$result = $db->sql_query($sql))
{
	error_die(SQL_QUERY, "Could not query config information.", __LINE__, __FILE__);
}
else  
{
	$config = $db->sql_fetchrow($result);
	$sitename = stripslashes($config["sitename"]);
	$allow_html = $config["allow_html"];
	$allow_bbcode = $config["allow_bbcode"];
	$allow_sig = $config["allow_sig"];
	$allow_namechange = $config["allow_namechange"];
	$posts_per_page = $config["posts_per_page"];
	$hot_threshold = $config["hot_threshold"];
	$topics_per_page = $config["topics_per_page"];
	$override_user_themes = $config["override_themes"];
	$email_sig = stripslashes($config["email_sig"]);
	$email_from = $config["email_from"];
	$default_lang = $config["default_lang"];
	$require_activation = $config["require_activation"];
	$sys_timezone = $config["system_timezone"];
	$sys_lang = $default_lang;            
}

include('language/lang_'.$default_lang.'.'.$phpEx);

?>
