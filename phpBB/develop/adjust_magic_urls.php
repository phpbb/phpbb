<?php
/**
* Adds class="postlink" to magic urls
*
* You should make a backup from your users, posts and privmsgs table in case something goes wrong
* Forum descriptions and rules need to be re-submitted manually.
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
	'<!-- l --><a href="',
	'<!-- m --><a href="',
	'<!-- w --><a href="',
);
$with = array(
	'<!-- l --><a class="postlink-local" href="',
	'<!-- m --><a class="postlink" href="',
	'<!-- w --><a class="postlink" href="',
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
