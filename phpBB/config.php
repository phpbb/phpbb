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

// Session data
$cookiename = "phpbb2";
$cookiedomain = "";
$cookiepath = "";
$cookiesecure = "";
$cookielife = 31536000;
$session_length = 300;

// DB connection config
$dbms = "mysql";
$dbhost = "localhost";
$dbname = "";
$dbuser = "";
$dbpasswd = "";

// Date format (needs to go into DB)
//$date_format = "m-d-Y H:i:s"; // American datesformat
$date_format = "d M Y h:i:s a"; // European datesformat

// DB table prefix
$table_prefix = "phpbb_";

$url_images = "images";
$image_quote = "$url_images/quote.gif";

$image_edit = "$url_images/edit.gif";
$image_profile = "$url_images/profile.gif";
$image_email = "$url_images/email.gif";
$image_pmsg = "$url_images/pm.gif";
$image_delpost = "$url_images/edit.gif";

$image_ip = "$url_images/ip_logged.gif";

$image_www = "$url_images/www_icon.gif";
$image_icq = "$url_images/icq_add.gif";
$image_aim = "$url_images/aim.gif";
$image_yim = "$url_images/yim.gif";
$image_msnm = "$url_images/msnm.gif";

?>
