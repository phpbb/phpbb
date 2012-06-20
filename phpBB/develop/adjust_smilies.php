<?php
/**
* Updates smilies that were changed to the new ones
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

$replace = array(
	'<img src="{SMILIES_PATH}/icon_biggrin.gif',
	'<img src="{SMILIES_PATH}/icon_confused.gif',
	'<img src="{SMILIES_PATH}/icon_sad.gif',
	'<img src="{SMILIES_PATH}/icon_smile.gif',
	'<img src="{SMILIES_PATH}/icon_surprised.gif',
	'<img src="{SMILIES_PATH}/icon_wink.gif',
);

$with = array(
	'<img src="{SMILIES_PATH}/icon_e_biggrin.gif',
	'<img src="{SMILIES_PATH}/icon_e_confused.gif',
	'<img src="{SMILIES_PATH}/icon_e_sad.gif',
	'<img src="{SMILIES_PATH}/icon_e_smile.gif',
	'<img src="{SMILIES_PATH}/icon_e_surprised.gif',
	'<img src="{SMILIES_PATH}/icon_e_wink.gif',
);

// Adjust user signatures
$sql = 'SELECT user_id, user_sig
	FROM ' . USERS_TABLE;
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$new_content = str_replace($replace, $with, $row['user_sig']);

	if ($new_content != $row['user_sig'])
	{
		$sql = 'UPDATE ' . USERS_TABLE . " SET user_sig = '" . $db->sql_escape($new_content) . "'
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
}
$db->sql_freeresult($result);


// Now adjust posts
$sql = 'SELECT post_id, post_text
	FROM ' . POSTS_TABLE;
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$new_content = str_replace($replace, $with, $row['post_text']);

	if ($row['post_text'] != $new_content)
	{
		$sql = 'UPDATE ' . POSTS_TABLE . " SET post_text = '" . $db->sql_escape($new_content) . "'
			WHERE post_id = " . $row['post_id'];
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
}
$db->sql_freeresult($result);

// Now to the private messages
$sql = 'SELECT msg_id, message_text
	FROM ' . PRIVMSGS_TABLE;
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$new_content = str_replace($replace, $with, $row['message_text']);

	if ($row['message_text'] != $new_content)
	{
		$sql = 'UPDATE ' . PRIVMSGS_TABLE . " SET bbcode_bitfield = '" . $db->sql_escape($new_content) . "'
			WHERE msg_id = " . $row['msg_id'];
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
}
$db->sql_freeresult($result);

// Done
$db->sql_close();
