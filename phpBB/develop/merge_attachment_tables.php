<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");

$db = $dbhost = $dbuser = $dbpasswd = $dbport = $dbname = '';

define('IN_PHPBB', 1);
define('ANONYMOUS', 1);
$phpEx = substr(strrchr(__FILE__, '.'), 1);
$phpbb_root_path='./../';
include($phpbb_root_path . 'config.'.$phpEx);
require($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.'.$phpEx);
require($phpbb_root_path . 'includes/db/' . $dbms . '.'.$phpEx);
include($phpbb_root_path . 'includes/functions.'.$phpEx);

$cache		= new acm();
$db			= new sql_db();

// Connect to DB
$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false);

// Rename the attachments table... 
$sql = "RENAME TABLE {$table_prefix}attachments TO {$table_prefix}attach_temp";
$db->sql_query($sql);

$sql = "CREATE TABLE {$table_prefix}attachments 
	SELECT d.*, a.post_id, a.user_id_from as poster_id, p.topic_id
		FROM {$table_prefix}attach_desc d, {$table_prefix}attach_temp a, {$table_prefix}posts p
		WHERE a.attach_id = d.attach_id
			AND a.post_id = p.post_id";
$db->sql_query($sql);

switch ($db->get_sql_layer())
{
	case 'mysql':
	case 'mysql4':
		$sql = 'ALTER TABLE ' . $table_prefix . 'attachments 
			ADD PRIMARY KEY (attach_id), 
			ADD INDEX filetime (filetime),
			ADD INDEX post_id (post_id),
			ADD INDEX poster_id (poster_id),
			ADD INDEX physical_filename (physical_filename(10)),
			ADD INDEX filesize (filesize),
			ADD INDEX topic_id (topic_id),
			MODIFY COLUMN attach_id mediumint(8) UNSIGNED NOT NULL auto_increment';
		break;

	case 'mssql':
	case 'mssql-odbc':
	case 'msaccess':
		break;

	case 'postgresql':
		break;
}
$db->sql_query($sql);

//$db->sql_query("DROP TABLE {$table_prefix}attach_temp");

echo "<p><b>Done</b></p>\n";
