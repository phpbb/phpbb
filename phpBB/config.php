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
// Debug Level
define(DEBUG, 1); // Debugging on
//define(DEBUG, 0); // Debugging off

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
define(NO_TOPICS, 5);
define(GENERAL_ERROR, 6);
define(LOGIN_FAILED, 7);

// Session data
$session_cookie = "phpBBsession";
$session_cookie_time = 3600; 

// DB connection config
$dbms = "mysql";
$dbhost = "localhost";
$dbname = "";
$dbuser = "";
$dbpasswd = "";

// Date format (needs to go into DB)
$date_format = "M d, Y h:i:s a";

// DB table config
$table_prefix = "phpbb_";
define('BANLIST_TABLE', $table_prefix.'banlist');
define('CATEGORIES_TABLE', $table_prefix.'categories');
define('CONFIG_TABLE', $table_prefix.'config');
define('DISALLOW_TABLE', $table_prefix.'disallow');
define('FORUM_ACCESS_TABLE', $table_prefix.'forum_access');
define('FORUM_MODS_TABLE', $table_prefix.'forum_mods');
define('FORUMS_TABLE', $table_prefix.'forums');
define('HEADERMETAFOOTER_TABLE', $table_prefix.'headermetafooter');
define('POSTS_TABLE', $table_prefix.'posts');
define('POSTS_TEXT_TABLE', $table_prefix.'posts_text');
define('PRIV_MSGS_TABLE', $table_prefix.'priv_msgs');
define('RANKS_TABLE', $table_prefix.'ranks');
define('SESSIONS_TABLE', $table_prefix.'sessions');
define('THEMES_TABLE', $table_prefix.'themes');
define('TOPICS_TABLE', $table_prefix.'topics');
define('USERS_TABLE', $table_prefix.'users');
define('WHOSONLINE_TABLE', $table_prefix.'whosonline');
define('WORDS_TABLE', $table_prefix.'words');

?>
