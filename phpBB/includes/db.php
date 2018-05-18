<?php
/***************************************************************************
 *                                 db.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: db.php,v 1.1 2010/10/10 15:05:27 orynider Exp $
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
 ***************************************************************************/

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
}


switch($dbms)
{
	case 'mysql':
		include($phpbb_root_path . 'includes/db/mysql.'.$phpEx);
		break;

	case 'mysql4':
		include($phpbb_root_path . 'includes/db/mysql4.'.$phpEx);
		break;
		
	case 'mysqli':
		include($phpbb_root_path . 'includes/db/mysqli.'.$phpEx);
		break;		

	case 'postgres':
		include($phpbb_root_path . 'includes/db/postgres7.'.$phpEx);
		break;

	case 'mssql':
		include($phpbb_root_path . 'includes/db/mssql.'.$phpEx);
		break;

	case 'oracle':
		include($phpbb_root_path . 'includes/db/oracle.'.$phpEx);
		break;

	case 'msaccess':
		include($phpbb_root_path . 'includes/db/msaccess.'.$phpEx);
		break;

	case 'mssql-odbc':
		include($phpbb_root_path . 'includes/db/mssql-odbc.'.$phpEx);
		break;
}


// Make the database connection.
//$db = new sql_db($dbhost, $dbuser, $dbpasswd, $dbname, false);
//require($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);
/*if(!$db->db_connect_id)
{
	message_die(CRITICAL_ERROR, "Could not connect to the database");
}
*/
?>