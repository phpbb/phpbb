<?php
/***************************************************************************  
 *                               config.php  
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

// Constants
// User Levels
define(ADMIN, 4);
define(SUPERMOD, 3);
define(MODERATOR, 2);
define(USER, 1);
define(DELETED, -1);
define(ANONYMOUS, -1);

// Forum access levels
define(PUBLIC, 1);
define(PRIVATE, 2);

// Forum posting levels
define(ANONALLOWED, 1);
define(REGONLY, 2);
define(MODONLY, 3);

// Topic state
define(UNLOCKED, 0);
define(LOCKED, 1);

// Ban time types
define(SECONDS, 1);
define(MINUTES, 2);
define(HOURS, 3);
define(DAYS, 4);
define(YEARS, 5);

// Error codes
define(SQL_CONNECT, 1);
define(BANNED, 2);
define(QUERY_ERROR, 3);
define(SESSION_CREATE, 4);


// Session data
$session_cookie = "phpBBsession";
$session_cookie_time = 3600; 

// DB connection config
$dbms = "mysql";
$dbhost = "localhost";
$dbname = "mydbname";
$dbuser = "imanidiot";
$dbpasswd = "imanidiotspassword";

// DB table config

$banlist_table = "phpbb_banlist";
$config_table = "phpbb_config";
$disallow_table = "phpbb_disallow";
$forum_access_table = "phpbb_forum_access";
$forum_mods_table = "phpbb_forum_mods";
$forums_table = "phpbb_forums";
$headermetafooter_table = "phpbb_headermetafooter";
$posts_table = "phpbb_posts";
$posts_text_table = "phpbb_posts_text";
$priv_msgs_table = "phpbb_priv_msgs";
$ranks_table = "phpbb_ranks";
$sessions_table = "phpbb_sessions";
$themes_table = "phpbb_themes";
$topics_table = "phpbb_topics";
$users_table = "phpbb_users";
$whosonline_table = "phpbb_whosonline";
$words_table = "phpbb_words";

?>