<?php
/**
* Corrects user_email_hash values if DB moved from 32-bit system to 64-bit system or vice versa.
* The CRC32 function in PHP generates different results for both systems.
* @PHP dev team: no, a hexdec() applied to it does not solve the issue. And please document it.
*
*/
die("Please read the first lines of this script for instructions on how to enable it");

set_time_limit(0);

define('IN_PHPBB', true);
$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$start = $request->variable('start', 0);
$num_items = 1000;

echo '<br />Updating user email hashes' . "\n";

$sql = 'SELECT user_id, user_email
	FROM ' . USERS_TABLE . '
	ORDER BY user_id ASC';
$result = $db->sql_query($sql);

$echos = 0;
while ($row = $db->sql_fetchrow($result))
{
	$echos++;

	$sql = 'UPDATE ' . USERS_TABLE . "
		SET user_email_hash = '" . $db->sql_escape(phpbb_email_hash($row['user_email'])) . "'
		WHERE user_id = " . (int) $row['user_id'];
	$db->sql_query($sql);

	if ($echos == 200)
	{
		echo '<br />';
		$echos = 0;
	}

	echo '.';
	flush();
}
$db->sql_freeresult($result);

echo 'FINISHED';

// Done
$db->sql_close();
