<?php
/**
* Corrects avatar filenames to match the new avatar delivery method.
*
* You should make a backup from your users table and the avatar directory in case something goes wrong
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
 
if (!isset($config['avatar_salt']))
{
	$cache->purge();
	if (!isset($config['avatar_salt']))
	{
		die('database not up to date');
	}
	die('database not up to date');
}

// let's start with the users using a group_avatar.
$sql = 'SELECT group_id, group_avatar
	FROM ' . GROUPS_TABLE . '
	WHERE group_avatar_type = ' . AVATAR_UPLOAD;

// We'll skip these, so remember them
$group_avatars = array();

echo '<br /> Updating groups' . "\n";

$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$new_avatar_name = adjust_avatar($row['group_avatar'], 'g' . $row['group_id']);
	$group_avatars[] = $new_avatar_name;
	
	// failure is probably due to the avatar name already being adjusted
	if ($new_avatar_name !== false)
	{
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_avatar = '" . $db->sql_escape($new_avatar_name) . "'
			WHERE user_avatar = '" . $db->sql_escape($row['group_avatar']) . "' 
			AND user_avatar_type = " . AVATAR_UPLOAD;
		$db->sql_query($sql);
		
		$sql = 'UPDATE ' . GROUPS_TABLE . "
			SET group_avatar = '" . $db->sql_escape($new_avatar_name) . "'
			WHERE group_id = {$row['group_id']}";
		$db->sql_query($sql);
	}
	else
	{
		echo '<br /> Failed updating group ' . $row['group_id'] . "\n";
	}

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

$sql = 'SELECT user_id, username, user_avatar, user_avatar_type
	FROM ' . USERS_TABLE . ' 
	WHERE user_avatar_type = ' . AVATAR_UPLOAD . ' 
	AND ' . $db->sql_in_set('user_avatar', $group_avatars, true, true);
$result = $db->sql_query($sql);

echo '<br /> Updating users' . "\n";

while ($row = $db->sql_fetchrow($result))
{
	$new_avatar_name = adjust_avatar($row['user_avatar'], $row['user_id']);

	// failure is probably due to the avatar name already being adjusted
	if ($new_avatar_name !== false)
	{
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_avatar = '" . $db->sql_escape($new_avatar_name) . "'
			WHERE user_id = {$row['user_id']}";
		$db->sql_query($sql);
	}
	else
	{
		// nuke this avatar
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_avatar = '', user_avatar_type = 0
			WHERE user_id = {$row['user_id']}";
		$db->sql_query($sql);
		echo '<br /> Failed updating user ' . $row['user_id'] . "\n";
	}
	
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

function adjust_avatar($old_name, $midfix)
{
	global $config, $phpbb_root_path;
	
	$avatar_path = $phpbb_root_path . $config['avatar_path'];
	$extension = strtolower(substr(strrchr($old_name, '.'), 1));
	$new_name = $config['avatar_salt'] . '_' . $midfix . '.' . $extension;

	if (@file_exists($avatar_path . '/' . $old_name) && @is_writable($avatar_path . '/' . $old_name) && @is_writable($avatar_path . '/' . $new_name))
	{
		@rename($avatar_path . '/' . $old_name, $avatar_path . '/' . $new_name);
		return $midfix . '.' . $extension;
	}
	return false;
}
