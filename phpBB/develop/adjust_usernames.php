<?php
/**
* Adjust username_clean column.
*
* You should make a backup from your users table in case something goes wrong
*/
die("Please read the first lines of this script for instructions on how to enable it");

set_time_limit(0);

define('IN_PHPBB', true);
$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$echos = 0;

$sql = 'SELECT user_id, username
	FROM ' . USERS_TABLE;
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$sql = 'UPDATE ' . USERS_TABLE . "
		SET username_clean = '" . $db->sql_escape(utf8_clean_string($row['username'])) . "'
		WHERE user_id = " . $row['user_id'];
	$db->sql_query($sql);

	if ($echos > 200)
	{
		echo '<br />' . "\n";
		$echos = 0;
	}

	echo '.';
	$echos++;

	flush();
}
$db->sql_freeresult($result);

echo 'FINISHED';

// Done
$db->sql_close();
