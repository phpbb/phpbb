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

//putenv('SYBASE=/usr/freetds');
//dl('sybase_ct.so');

// DB connection config

/*
//
// ODBC - Access (remote)
//
$dbms = "odbc";
$dbhost = "msaccess:odbctest";
$dbname = "";
$dbuser = "";
$dbpasswd = "efx2KarizonaD";
*/


//
// MSSQL 
//
$dbms = "mssql";
$dbhost = "Typhoon";
$dbname = "dev_starstreak_net";
$dbuser = "devhttp";
$dbpasswd = "efx2KarizonaD";


//
// MySQL (local)
//
$dbms = "mysql";
$dbhost = "localhost";
$dbname = "dev_starstreak_net";
$dbuser = "devhttp";
$dbpasswd = "efx2KarizonaD";


/*
//
// PostgreSQL (local)
//
$dbms = "postgres";
$dbhost = "";
$dbname = "dev_starstreak_net";
$dbuser = "devhttp";
$dbpasswd = "efx2KarizonaD";
*/

// DB table prefix
$table_prefix = "phpbb_";

?>
