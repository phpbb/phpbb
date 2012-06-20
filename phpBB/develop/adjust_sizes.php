<?php
/**
* Only adjust the [size] bbcode tag from pc to percent.
*
* You should make a backup from your users, posts and privmsgs table in case something goes wrong
* Forum descriptions and rules need to be re-submitted manually if they use the [size] tag.
*
* Since we limit the match to the sizes from 0 to 29 no newly applied sizes should be affected...
*/
die("Please read the first lines of this script for instructions on how to enable it");

set_time_limit(0);
@ini_set('memory_limit', '128M');

define('IN_PHPBB', true);
$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$echos = 0;

function replace_size($matches)
{
	return '[size=' . ceil(100.0 * (((double) $matches[1])/12.0)) . ':' . $matches[2] . ']';
}

// Adjust user signatures
$sql = 'SELECT user_id, user_sig, user_sig_bbcode_uid
	FROM ' . USERS_TABLE;
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$bbcode_uid = $row['user_sig_bbcode_uid'];

	// Only if a bbcode uid is present, the signature present and a size tag used...
	if ($bbcode_uid && $row['user_sig'] && strpos($row['user_sig'], '[size=') !== false)
	{
		$row['user_sig'] = preg_replace_callback('/\[size=(\d*):(' . $bbcode_uid . ')\]/', 'replace_size', $row['user_sig']);

		$sql = 'UPDATE ' . USERS_TABLE . " SET user_sig = '" . $db->sql_escape($row['user_sig']) . "'
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
$sql = 'SELECT post_id, post_text, bbcode_uid, enable_bbcode
	FROM ' . POSTS_TABLE;
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$bbcode_uid = $row['bbcode_uid'];

	// Only if a bbcode uid is present, bbcode enabled and a size tag used...
	if ($row['enable_bbcode'] && $bbcode_uid && strpos($row['post_text'], '[size=') !== false)
	{
		$row['post_text'] = preg_replace_callback('/\[size=(\d*):' . $bbcode_uid . '\]/', 'replace_size', $row['post_text']);

		$sql = 'UPDATE ' . POSTS_TABLE . " SET post_text = '" . $db->sql_escape($row['post_text']) . "'
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
$sql = 'SELECT msg_id, message_text, bbcode_uid, enable_bbcode
	FROM ' . PRIVMSGS_TABLE;
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$bbcode_uid = $row['bbcode_uid'];

	// Only if a bbcode uid is present, bbcode enabled and a size tag used...
	if ($row['enable_bbcode'] && $bbcode_uid && strpos($row['message_text'], '[size=') !== false)
	{
		$row['message_text'] = preg_replace_callback('/\[size=(\d*):' . $bbcode_uid . '\]/', 'replace_size', $row['message_text']);

		$sql = 'UPDATE ' . PRIVMSGS_TABLE . " SET message_text = '" . $db->sql_escape($row['message_text']) . "'
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
