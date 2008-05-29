<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : calc_email_hash.php
// STARTED   : Tue Feb 03, 2004
// COPYRIGHT : © 2004 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");
@set_time_limit(300);

define('IN_PHPBB', 1);
define('PHPBB_ROOT_PATH', './../');
define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
include(PHPBB_ROOT_PATH . 'common.' . PHP_EXT);

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
 
?>