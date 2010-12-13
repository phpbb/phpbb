<?php
/**
* Only adjust bitfields, do not rewrite text...
* All new parsings have the img, flash and quote modes set to true
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
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$echos = 0;

// Adjust user signatures
$message_parser = new parse_message();
$message_parser->mode = 'sig';
$message_parser->bbcode_init();

$sql = 'SELECT user_id, user_sig, user_sig_bbcode_uid, user_sig_bbcode_bitfield
	FROM ' . USERS_TABLE;
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	// Convert bbcodes back to their normal form
	if ($row['user_sig_bbcode_uid'] && $row['user_sig'])
	{
		decode_message($row['user_sig'], $row['user_sig_bbcode_uid']);

		$message_parser->message = $row['user_sig'];

		$message_parser->prepare_bbcodes();
		$message_parser->parse_bbcode();

		$bitfield = $message_parser->bbcode_bitfield;

		$sql = 'UPDATE ' . USERS_TABLE . " SET user_sig_bbcode_bitfield = '" . $db->sql_escape($bitfield) . "'
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
	else
	{
		$sql = 'UPDATE ' . USERS_TABLE . " SET user_sig_bbcode_bitfield = ''
			WHERE user_id = " . $row['user_id'];
		$db->sql_query($sql);
	}
}
$db->sql_freeresult($result);


// Now adjust posts

$message_parser = new parse_message();
$message_parser->mode = 'post';
$message_parser->bbcode_init();

// Update posts
$sql = 'SELECT post_id, post_text, bbcode_uid, enable_bbcode, enable_smilies, enable_sig
	FROM ' . POSTS_TABLE;
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	// Convert bbcodes back to their normal form
	if ($row['enable_bbcode'])
	{
		decode_message($row['post_text'], $row['bbcode_uid']);

		$message_parser->message = $row['post_text'];

		$message_parser->prepare_bbcodes();
		$message_parser->parse_bbcode();

		$bitfield = $message_parser->bbcode_bitfield;

		$sql = 'UPDATE ' . POSTS_TABLE . " SET bbcode_bitfield = '" . $db->sql_escape($bitfield) . "'
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
	else
	{
		$sql = 'UPDATE ' . POSTS_TABLE . " SET bbcode_bitfield = ''
			WHERE post_id = " . $row['post_id'];
		$db->sql_query($sql);
	}
}
$db->sql_freeresult($result);

// Now to the private messages
$message_parser = new parse_message();
$message_parser->mode = 'post';
$message_parser->bbcode_init();

// Update pms
$sql = 'SELECT msg_id, message_text, bbcode_uid, enable_bbcode
	FROM ' . PRIVMSGS_TABLE;
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	// Convert bbcodes back to their normal form
	if ($row['enable_bbcode'])
	{
		decode_message($row['message_text'], $row['bbcode_uid']);

		$message_parser->message = $row['message_text'];

		$message_parser->prepare_bbcodes();
		$message_parser->parse_bbcode();

		$bitfield = $message_parser->bbcode_bitfield;

		$sql = 'UPDATE ' . PRIVMSGS_TABLE . " SET bbcode_bitfield = '" . $db->sql_escape($bitfield) . "'
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
	else
	{
		$sql = 'UPDATE ' . PRIVMSGS_TABLE . " SET bbcode_bitfield = ''
			WHERE msg_id = " . $row['msg_id'];
		$db->sql_query($sql);
	}
}
$db->sql_freeresult($result);

// Done
$db->sql_close();
