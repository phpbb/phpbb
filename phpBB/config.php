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
$dbname = "phpbb";
$dbuser = "iamstillanidiot";
$dbpasswd = "iamstillanidiotspassword";

// Date format (needs to go into DB)
$date_format = "M d, Y h:i:s a";

// DB table config
define("BANLIST_TABLE", "phpbb_banlist");
define("CATEGORIES_TABLE", "phpbb_categories");
define("CONFIG_TABLE", "phpbb_config");
define("DISALLOW_TABLE", "phpbb_disallow");
define("FORUM_ACCESS_TABLE", "phpbb_forum_access");
define("FORUM_MODS_TABLE", "phpbb_forum_mods");
define("FORUMS_TABLE", "phpbb_forums");
define("HEADERMETAFOOTER_TABLE", "phpbb_headermetafooter");
define("POSTS_TABLE", "phpbb_posts");
define("POSTS_TEXT_TABLE", "phpbb_posts_text");
define("PRIV_MSGS_TABLE", "phpbb_priv_msgs");
define("RANKS_TABLE", "phpbb_ranks");
define("SESSIONS_TABLE", "phpbb_sessions");
define("THEMES_TABLE", "phpbb_themes");
define("TOPICS_TABLE", "phpbb_topics");
define("USERS_TABLE", "phpbb_users");
define("WHOSONLINE_TABLE", "phpbb_whosonline");
define("WORDS_TABLE", "phpbb_words");

?>
