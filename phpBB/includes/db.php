<?php
/***************************************************************************  
 *                                 db.php
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

switch($dbms)
{
	case 'mysql':
		include('db/mysql.'.$phpEx);
		break;
	case 'postgres':
		include('db/postgres7.'.$phpEx);
		break;
	case 'mssql':
		include('db/mssql.'.$phpEx);
		break;
	case 'odbc':
		include('db/odbc.'.$phpEx);
		break;
	case 'oracle':
		include('db/oracle.'.$phpEx);
		break;
}

// Make the database connection.
$db = new sql_db($dbhost, $dbuser, $dbpasswd, $dbname, false);
if(!$db) 
{
   $db_error = $db->sql_error();
   error_die(SQL_CONNECT, $db_error['message']);
}

?>