<?php
/***************************************************************************
 *                             functions_ucp.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/


// Generates an alphanumeric random string of given length
function gen_rand_string($num_chars)
{
	$chars = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',  'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',  'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9');

	list($usec, $sec) = explode(' ', microtime()); 
	mt_srand($sec * $usec); 

	$max_chars = count($chars) - 1;
	$rand_str = '';
	for ($i = 0; $i < $num_chars; $i++)
	{
		$rand_str .= $chars[mt_rand(0, $max_chars)];
	}

	return $rand_str;
}	

// Check to see if the username has been taken, or if it is disallowed.
// Also checks if it includes the " character, which we don't allow in usernames.
// Used for registering, changing names, and posting anonymously with a username
function validate_username($username)
{
	global $db, $user;

	$sql = 'SELECT username
		FROM ' . USERS_TABLE . "
		WHERE LOWER(username) = '" . strtolower($db->sql_escape($username)) . "'";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		return 'USERNAME_TAKEN';
	}
	$db->sql_freeresult($result);

	$sql = 'SELECT group_name
		FROM ' . GROUPS_TABLE . "
		WHERE LOWER(group_name) = '" . strtolower($db->sql_escape($username)) . "'";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		return 'USERNAME_TAKEN';
	}
	$db->sql_freeresult($result);

	$sql = 'SELECT disallow_username
		FROM ' . DISALLOW_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if (preg_match('#' . str_replace('*', '.*?', preg_quote($row['disallow_username'], '#')) . '#i', $username))
		{
			return 'USERNAME_DISALLOWED';
		}
	}
	$db->sql_freeresult($result);

	$sql = 'SELECT word
		FROM  ' . WORDS_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if (preg_match('#(' . str_replace('\*', '.*?', preg_quote($row['word'], '#')) . ')#i', $username))
		{
			return 'USERNAME_DISALLOWED';
		}
	}
	$db->sql_freeresult($result);

	return false;
}

// Check to see if email address is banned or already present in the DB
function validate_email($email)
{
	global $config, $db, $user;

	if (preg_match('#^[a-z0-9\.\-_\+]+?@(.*?\.)*?[a-z0-9\-_]+?\.[a-z]{2,4}$#i', $email))
	{
		$sql = 'SELECT ban_email
			FROM ' . BANLIST_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if (preg_match('#^' . str_replace('*', '.*?', $row['ban_email']) . '$#i', $email))
			{
				return 'EMAIL_BANNED';
			}
		}
		$db->sql_freeresult($result);

		if (!$config['allow_emailreuse'])
		{
			$sql = 'SELECT user_email
				FROM ' . USERS_TABLE . "
				WHERE user_email = '" . $db->sql_escape($email) . "'";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				return 'EMAIL_TAKEN';
			}
			$db->sql_freeresult($result);
		}

		return false;
	}

	return 'EMAIL_INVALID';
}

function update_username($old_name, $new_name)
{
	global $db;

	$update_ary = array(
		FORUMS_TABLE	=> array('forum_last_poster_name'), 
		MODERATOR_TABLE	=> array('username'), 
		POSTS_TABLE		=> array('poster_username'), 
		TOPICS_TABLE	=> array('topic_first_poster_name', 'topic_last_poster_name'),
	);

	foreach ($update_ary as $table => $field_ary)
	{
		foreach ($field_ary as $field)
		{
			$sql = "UPDATE $table 
				SET $field = '$new_name' 
				WHERE $field = '$old_name'";
			$db->sql_query($sql);
		}
	}

	$sql = 'UPDATE ' . CONFIG_TABLE . " 
		SET config_value = '" . $new_name . "'
		WHERE config_name = 'newest_username'
			AND config_value = '" . $old_name . "'";
	$db->sql_query($sql);
}

function avatar_delete()
{
	global $config, $db, $user;

	if (file_exists('./' . $config['avatar_path'] . '/' . $user->data['user_avatar']))
	{
		@unlink('./' . $config['avatar_path'] . '/' . $user->data['user_avatar']);
	}

	return false;
 }

function avatar_remote(&$data)
{
	global $config, $db, $user, $phpbb_root_path;

	if (!preg_match('#^(http[s]*?)|(ftp)://#i', $data['remotelink']))
	{
		$data['remotelink'] = 'http://' . $data['remotelink'];
	}

	if (!preg_match('#^(http[s]?)|(ftp)://(.*?\.)*?[a-z0-9\-]+?\.[a-z]{2,4}:?([0-9]*?).*?\.(gif|jpg|jpeg|png)$#i', $data['remotelink']))
	{
		return $user->lang['AVATAR_URL_INVALID'];
	}

	if ((!($data['width'] || $data['height']) || $data['remotelink'] != $user->data['user_avatar']) && ($config['avatar_max_width'] || $config['avatar_max_height']))
	{
		list($width, $height) = @getimagesize($data['remotelink']);

		if (!$width || !$height)
		{
			return $user->lang['AVATAR_NO_SIZE'];
		}
		else if ($width > $config['avatar_max_width'] || $height > $config['avatar_max_height'])
		{
			return sprintf($user->lang['AVATAR_WRONG_SIZE'], $config['avatar_max_width'], $config['avatar_max_height']);
		}

		$data['width'] = &$width;
		$data['height'] = &$height;
	}
	else if ($data['width'] > $config['avatar_max_width'] || $data['height'] > $config['avatar_max_height'])
	{
		return sprintf($user->lang['AVATAR_WRONG_SIZE'], $config['avatar_max_width'], $config['avatar_max_height']);
	}

	// Set type
	$data['filename'] = &$data['remotelink']; 
	$data['type'] = AVATAR_REMOTE;

	return false;
}

function avatar_upload(&$data)
{
	global $config, $db, $user;

	if (!empty($_FILES['uploadfile']['tmp_name']))
	{
		$filename = $_FILES['uploadfile']['tmp_name'];
		$filesize = $_FILES['uploadfile']['size'];
		$realname = $_FILES['uploadfile']['name'];

		if (file_exists($filename) && preg_match('#^(.*?)\.(jpg|jpeg|gif|png)$#i', $realname, $match))
		{
			$realname = $match[1];
			$filetype = $match[2];
			$php_move = 'move_uploaded_file';
		}
		else
		{
			return $user->lang['AVATAR_NOT_UPLOADED'];
		}
	}
	else if (preg_match('#^(http://).*?\.(jpg|jpeg|gif|png)$#i', $data['uploadurl'], $match))
	{
		if (empty($match[2]))
		{
			return $user->lang['AVATAR_URL_INVALID'];
		}

		$url = parse_url($data['uploadurl']);

		$host = $url['host'];
		$path = dirname($url['path']);
		$port = (!empty($url['port'])) ? $url['port'] : 80;
		$filetype = array_pop(explode('.', $url['path']));
		$realname = basename($url['path'], '.' . $filetype);
		$filename = $url['path'];
		$filesize = 0;

		if (!($fsock = @fsockopen($host, $port, $errno, $errstr)))
		{
			return $user->lang['AVATAR_NOT_UPLOADED'];
		}

		fputs($fsock, 'GET /' . $filename . " HTTP/1.1\r\n");
		fputs($fsock, "HOST: " . $host . "\r\n");
		fputs($fsock, "Connection: close\r\n\r\n");

		$avatar_data = '';
		while (!feof($fsock))
		{
			$avatar_data .= fread($fsock, $config['avatar_filesize']);
		}
		@fclose($fsock);
		$avatar_data = array_pop(explode("\r\n\r\n", $avatar_data));

		if (empty($avatar_data))
		{
			return $user->lang['AVATAR_NOT_UPLOADED'];
		}
		unset($url_ary);

		$tmp_path = (!@ini_get('safe_mode')) ? false : $phpbb_root_path . 'cache/tmp';
		$filename = tempnam($tmp_path, uniqid(rand()) . '-');

		if (!($fp = @fopen($filename, 'wb')))
		{
			return $user->lang['AVATAR_NOT_UPLOADED'];;
		}
		$filesize = fwrite($fp, $avatar_data);
		fclose($fp);
		unset($avatar_data);

		if (!$filesize)
		{
			unlink($filename);
			return $user->lang['AVATAR_NOT_UPLOADED'];
		}

		$php_move = 'copy';
	}

	list($width, $height) = getimagesize($filename);

	if ($width > $config['avatar_max_width'] || $height > $config['avatar_max_height'] || $width < $config['avatar_min_width'] || $height < $config['avatar_min_height'] || !$width || !$height)
	{
		return sprintf($user->lang['AVATAR_WRONG_SIZE'], $config['avatar_min_width'], $config['avatar_min_height'], $config['avatar_max_width'], $config['avatar_max_height']);
	}

	// Replace any chars which may cause us problems with _
	$bad_chars = array(' ', '/', ':', '*', '?', '"', '<', '>', '|');

	$data['filename'] = $user->data['user_id'] . '_' . str_replace($bad_chars, '_', $realname) . '.' . $filetype;
	$data['width'] = &$width;
	$data['height'] = &$height;

	if(!$php_move($filename, $phpbb_root_path . $config['avatar_path'] . '/' . $data['filename']))
	{
		@unlink($filename);
		return $user->lang['AVATAR_NOT_UPLOADED'];
	}
	@unlink($filename);

	$filesize = @filesize($phpbb_root_path . $config['avatar_path'] . '/' . $data['filename']);
	if (!$filesize || $filesize > $config['avatar_filesize'])
	{
		@unlink($phpbb_root_path . $config['avatar_path'] . '/' . $data['filename']);
		return sprintf($user->lang['AVATAR_WRONG_FILESIZE'], $config['avatar_filesize']);
	}

	// Set type
	$data['type'] = AVATAR_UPLOAD;

	return false;
}

?>