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


$session_cookie = "phpBBsession";
$session_cookie_time = 3600; 

$dbms = "mysql";
$dbhost = "localhost";
$dbname = "phpbb2";
$dbuser = "root";
$dbpasswd = "zocalo";

?>
