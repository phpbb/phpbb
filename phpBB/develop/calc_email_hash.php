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
@set_time_limit(300);

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

$start = 0;
do
{
	// Batch query for group members, call group_user_del
	$sql = "SELECT user_id, user_email 
		FROM  {$table_prefix}users
		LIMIT $start, 100";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		do
		{
			$sql = "UPDATE {$table_prefix}users 
				SET user_email_hash = " . (crc32(strtolower($row['user_email'])) . strlen($row['user_email'])) . '
				WHERE user_id = ' . $row['user_id'];
			$db->sql_query($sql);

			$start++;
		}
		while ($row = $db->sql_fetchrow($result));

		echo "<br />Batch -> $start\n";
		flush();
	}
	else
	{
		$start = 0;
	}
	$db->sql_freeresult($result);
}
while ($start);

echo "<p><b>Done</b></p>\n";
