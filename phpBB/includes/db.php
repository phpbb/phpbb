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

switch($dbms)
{
	case 'mysql':
		include($phpbb_root_path . 'db/mysql.'.$phpEx);
		break;

	case 'postgres':
		include($phpbb_root_path . 'db/postgres7.'.$phpEx);
		break;

	case 'mssql':
		include($phpbb_root_path . 'db/mssql.'.$phpEx);
		break;

	case 'odbc':
		include($phpbb_root_path . 'db/odbc.'.$phpEx);
		break;

	case 'oracle':
		include($phpbb_root_path . 'db/oracle.'.$phpEx);
		break;
}

// Make the database connection.
$db = new sql_db($dbhost, $dbuser, $dbpasswd, $dbname, false);
if(!$db->db_connect_id) 
{
   message_die(CRITICAL_ERROR, "Could not connect to the database");
}

?>