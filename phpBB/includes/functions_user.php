<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : functions_user.php
// STARTED   : Sat Dec 16, 2000
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

//
// User functions
//

// Obtain user_ids from usernames or vice versa. Returns false on
// success else the error string
function user_get_id_name(&$user_id_ary, &$username_ary)
{
	global $db;

	// Are both arrays already filled? Yep, return else
	// are neither array filled? 
	if ($user_id_ary && $username_ary)
	{
		return;
	}
	else if (!$user_id_ary && !$username_ary)
	{
		return 'NO_USERS';
	}

	$which_ary = ($user_id_ary) ? 'user_id_ary' : 'username_ary';

	if ($$which_ary  && !is_array($$which_ary))
	{
		$$which_ary = array($$which_ary);
	}

	$sql_in = ($which_ary == 'user_id_ary') ? array_map('intval', $$which_ary) : preg_replace('#^[\s]*(.*?)[\s]*$#e', "\"'\" . \$db->sql_escape('\\1') . \"'\"", $$which_ary);
	unset($$which_ary);

	// Grab the user id/username records
	$sql_where = ($which_ary == 'user_id_ary') ? 'user_id' : 'username';
	$sql = 'SELECT user_id, username 
		FROM ' . USERS_TABLE . " 
		WHERE $sql_where IN (" . implode(', ', $sql_in) . ')';
	$result = $db->sql_query($sql);

	if (!($row = $db->sql_fetchrow($result)))
	{
		return 'NO_USERS';
	}

	$id_ary = $username_ary = array();
	do
	{
		$username_ary[$row['user_id']] = $row['username'];
		$user_id_ary[] = $row['user_id'];
	}
	while ($row = $db->sql_fetchrow($result));
	$db->sql_freeresult($result);

	return false;
}

// Updates a username across all relevant tables/fields
function user_update_name($old_name, $new_name)
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

//
// Data validation ... used primarily but not exclusively by
// ucp modules
//

// "Master" function for validating a range of data types
function validate_data($data, $val_ary)
{
	$error = array();

	foreach ($val_ary as $var => $val_seq)
	{
		if (!is_array($val_seq[0]))
		{
			$val_seq = array($val_seq);
		}

		foreach ($val_seq as $validate)
		{
			$function = array_shift($validate);
			array_unshift($validate, $data[$var]);

			if ($result = call_user_func_array('validate_' . $function, $validate))
			{
				$error[] = $result . '_' . strtoupper($var);
			}
		}
	}

	return $error;
}

function validate_string($string, $optional = false, $min = 0, $max = 0)
{
	if (empty($string) && $optional)
	{
		return false;
	}

	if ($min && strlen($string) < $min)
	{
		return 'TOO_SHORT';
	}
	else if ($max && strlen($string) > $max)
	{
		return 'TOO_LONG';
	}

	return false;
}

function validate_num($num, $optional = false, $min = 0, $max = 1E99)
{
	if (empty($num) && $optional)
	{
		return false;
	}

	if ($num < $min)
	{
		return 'TOO_SMALL';
	}
	else if ($num > $max) 
	{
		return 'TOO_LARGE';
	}

	return false;
}

function validate_match($string, $optional = false, $match)
{
	if (empty($string) && $optional)
	{
		return false;
	}

	if (!preg_match($match, $string))
	{
		return 'WRONG_DATA';
	}
	return false;
}

// Check to see if the username has been taken, or if it is disallowed.
// Also checks if it includes the " character, which we don't allow in usernames.
// Used for registering, changing names, and posting anonymously with a username
function validate_username($username)
{
	global $config, $db, $user;

	if (strtolower($user->data['username']) == strtolower($username))
	{
		return false;
	}

	if (!preg_match('#^' . str_replace('\\\\', '\\', $config['allow_name_chars']) . '$#i', $username))
	{
		return 'INVALID_CHARS';
	}

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

	if (strtolower($user->data['user_email']) == strtolower($email))
	{
		return false;
	}

	if (!preg_match('#^[a-z0-9\.\-_\+]+?@(.*?\.)*?[a-z0-9\-_]+?\.[a-z]{2,4}$#i', $email))
	{
		return 'EMAIL_INVALID';
	}

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
		$sql = 'SELECT user_email_hash
			FROM ' . USERS_TABLE . "
			WHERE user_email_hash = " . crc32(strtolower($email)) . strlen($email);
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			return 'EMAIL_TAKEN';
		}
		$db->sql_freeresult($result);
	}

	return false;
}

//
// Avatar functions
//

function avatar_delete($id)
{
	global $phpbb_root_path, $config, $db, $user;

	if (file_exists($phpbb_root_path . $config['avatar_path'] . '/' . $id))
	{
		@unlink($phpbb_root_path . $config['avatar_path'] . '/' . $id);
	}

	return false;
 }

function avatar_remote($data, &$error)
{
	global $config, $db, $user, $phpbb_root_path;

	if (!preg_match('#^(http[s]*?)|(ftp)://#i', $data['remotelink']))
	{
		$data['remotelink'] = 'http://' . $data['remotelink'];
	}

	if (!preg_match('#^(http[s]?)|(ftp)://(.*?\.)*?[a-z0-9\-]+?\.[a-z]{2,4}:?([0-9]*?).*?\.(gif|jpg|jpeg|png)$#i', $data['remotelink']))
	{
		$error[] = $user->lang['AVATAR_URL_INVALID'];
		return false;
	}

	if ((!($data['width'] || $data['height']) || $data['remotelink'] != $user->data['user_avatar']) && ($config['avatar_max_width'] || $config['avatar_max_height']))
	{
		list($width, $height) = @getimagesize($data['remotelink']);

		if (!$width || !$height)
		{
			$error[] = $user->lang['AVATAR_NO_SIZE'];
			return false;
		}
		else if ($width > $config['avatar_max_width'] || $height > $config['avatar_max_height'])
		{
			$error[] = sprintf($user->lang['AVATAR_WRONG_SIZE'], $config['avatar_max_width'], $config['avatar_max_height']);
			return false;
		}
	}
	else if ($data['width'] > $config['avatar_max_width'] || $data['height'] > $config['avatar_max_height'])
	{
		$error[] = sprintf($user->lang['AVATAR_WRONG_SIZE'], $config['avatar_max_width'], $config['avatar_max_height']);
		return false;
	}

	return array(AVATAR_REMOTE, $remotelink, $width, $height);
}

function avatar_upload($data, &$error)
{
	global $phpbb_root_path, $config, $db, $user;

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
			$error[] = $user->lang['AVATAR_NOT_UPLOADED'];
			return false;
		}
	}
	else if (preg_match('#^(http://).*?\.(jpg|jpeg|gif|png)$#i', $data['uploadurl'], $match))
	{
		if (empty($match[2]))
		{
			$error[] = $user->lang['AVATAR_URL_INVALID'];
			return false;
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
			$error[] = $user->lang['AVATAR_NOT_UPLOADED'];
			return false;
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
			$error[] = $user->lang['AVATAR_NOT_UPLOADED'];
			return false;
		}
		unset($url_ary);

		$tmp_path = (!@ini_get('safe_mode')) ? false : $phpbb_root_path . 'cache';
		$filename = tempnam($tmp_path, uniqid(rand()) . '-');

		if (!($fp = @fopen($filename, 'wb')))
		{
			$error[] = $user->lang['AVATAR_NOT_UPLOADED'];
			return false;
		}
		$filesize = fwrite($fp, $avatar_data);
		fclose($fp);
		unset($avatar_data);

		if (!$filesize)
		{
			unlink($filename);
			$error[] = $user->lang['AVATAR_NOT_UPLOADED'];
			return false;
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

	$realfilename = $data['user_id'] . '_' . str_replace($bad_chars, '_', $realname) . '.' . $filetype;

	if(!$php_move($filename, $phpbb_root_path . $config['avatar_path'] . '/' . $realfilename))
	{
		@unlink($filename);
		$error[] = $user->lang['AVATAR_NOT_UPLOADED'];
		return false;
	}
	@unlink($filename);

	$filesize = @filesize($phpbb_root_path . $config['avatar_path'] . "/$realfilename");
	if (!$filesize || $filesize > $config['avatar_filesize'])
	{
		@unlink($phpbb_root_path . $config['avatar_path'] . "/$realfilename");
		$error[] = sprintf($user->lang['AVATAR_WRONG_FILESIZE'], $config['avatar_filesize']);
		return false;
	}

	return array(AVATAR_UPLOAD, $realfilename, $width, $height);
}

function avatar_gallery($category, &$error)
{
	global $config;

	$path = $phpbb_root_path . $config['avatar_gallery_path'];

	// To be replaced with SQL ... before M3 completion
	$dp = @opendir($path);

	$data = array();
	$avatar_row_count = $avatar_col_count = 0;
	while ($file = readdir($dp))
	{
		if ($file{0} != '.' && is_dir("$path/$file"))
		{
			$dp2 = @opendir("$path/$file");

			while ($sub_file = readdir($dp2))
			{
				if (preg_match('#\.(gif$|png$|jpg|jpeg)$#i', $sub_file))
				{
					$data[$file][$avatar_row_count][$avatar_col_count]['file'] = "$file/$sub_file"; 
					$data[$file][$avatar_row_count][$avatar_col_count]['name'] = ucfirst(str_replace('_', ' ', preg_replace('#^(.*)\..*$#', '\1', $sub_file)));

					$avatar_col_count++;
					if ($avatar_col_count == 4)
					{
						$avatar_row_count++;
						$avatar_col_count = 0;
					}
				}
			}
			closedir($dp2);
		}
	}
	closedir($dp);

	@ksort($data);

	return $data;
}

//
// Usergroup functions
//

// Add or edit a group. If we're editing a group we only update user
// parameters such as rank, etc. if they are changed
function group_create($group_id, $type, $name, $desc)
{
	global $phpbb_root_path, $config, $db, $user, $file_upload;

	$error = array();

	// Check data
	if (!strlen($name) || strlen($name) > 40)
	{
		$error[] = (!strlen($name)) ? $user->lang['GROUP_ERR_USERNAME'] : $user->lang['GROUP_ERR_USER_LONG'];
	}

	if (strlen($desc) > 255)
	{
		$error[] = $user->lang['GROUP_ERR_DESC_LONG'];
	}

	if (!in_array($type, array(GROUP_OPEN, GROUP_CLOSED, GROUP_HIDDEN, GROUP_SPECIAL, GROUP_FREE)))
	{
		$error[] = $user->lang['GROUP_ERR_TYPE'];
	}

	if (!sizeof($error))
	{
		$sql_ary = array(
			'group_name'			=> (string) $name,
			'group_description'		=> (string) $desc,
			'group_type'			=> (int) $type,
		);

		$attribute_ary = array('group_colour' => 'string', 'group_rank' => 'int', 'group_avatar' => 'string', 'group_avatar_type' => 'int', 'group_avatar_width' => 'int', 'group_avatar_height' => 'int');

		$i = 4;
		foreach ($attribute_ary as $attribute => $type)
		{
			if (func_num_args() > $i && ($value = func_get_arg($i)) !== false)
			{
				settype($value, $type);

				$sql_ary[$attribute] = $$attribute = $value;
			}
			$i++;
		}

		$sql = ($group_id) ? 'UPDATE ' . GROUPS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "	WHERE group_id = $group_id" : 'INSERT INTO ' . GROUPS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		$sql_ary = array();
		foreach ($attribute_ary as $attribute => $type)
		{
			if (isset($$attribute))
			{
				$sql_ary[str_replace('group', 'user', $attribute)] = $$attribute;
			}
		}

		if (sizeof($sql_ary))
		{
			$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
				WHERE group_id = $group_id";
			$db->sql_query($sql);
		}

		if (!function_exists('add_log'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
		}

		$log = ($group_id) ? 'LOG_GROUP_UPDATED' : 'LOG_GROUP_CREATED';
		add_log('admin', $log, $name);
	}

	return (sizeof($error)) ? $error : false;
}

function group_delete($group_id, $group_name = false)
{
	global $db;

	if (!$group_name)
	{
		$sql = 'SELECT group_name
			FROM ' . GROUPS_TABLE . " 
			WHERE group_id = $group_id";
		$result = $db->sql_query($sql);

		if (!extract($db->sql_fetchrow($result)))
		{
			trigger_error("Could not obtain name of group $group_id", E_USER_ERROR);
		}
		$db->sql_freeresult($result);
	}

	$start = 0;

	do
	{
		$user_id_ary = $username_ary = array();

		// Batch query for group members, call group_user_del
		$sql = 'SELECT u.user_id, u.username
			FROM ' . USER_GROUP_TABLE . ' ug, ' . USERS_TABLE . " u
			WHERE ug.group_id = $group_id
				AND u.user_id = ug.user_id 
			LIMIT $start, 200";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$user_id_ary[] = $row['user_id'];
				$username_ary[] = $row['username'];

				$start++;
			}
			while ($row = $db->sql_fetchrow($result));

			group_user_del($group_id, $user_id_ary, $username_ary, $group_name);
		}
		else
		{
			$start = 0;
		}
		$db->sql_freeresult($result);
	}
	while ($start);
	
	// Delete group
	$sql = 'DELETE FROM ' . GROUPS_TABLE . " 
		WHERE group_id = $group_id";
	$db->sql_query($sql);

	if (!function_exists('add_log'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
	}

	add_log('admin', 'LOG_GROUP_DELETE', $group_name);

	return false;
}

function group_user_add($group_id, $user_id_ary = false, $username_ary = false, $group_name = false, $default = false, $leader = false)
{
	global $db, $auth;

	// We need both username and user_id info
	user_get_id_name($user_id_ary, $username_ary);

	// Remove users who are already members of this group
	$sql = 'SELECT user_id, group_leader  
		FROM ' . USER_GROUP_TABLE . '   
		WHERE user_id IN (' . implode(', ', $user_id_ary) . ") 
			AND group_id = $group_id";
	$result = $db->sql_query($sql);

	$add_id_ary = $update_id_ary = array();
	if ($row = $db->sql_fetchrow($result))
	{
		do
		{
			$add_id_ary[] = $row['user_id'];

			if ($leader && !$row['group_leader'])
			{
				$update_id_ary[] = $row['user_id'];
			}
		}
		while ($row = $db->sql_fetchrow($result));
	}
	$db->sql_freeresult($result);

	// Do all the users exist in this group?
	$add_id_ary = array_diff($user_id_ary, $add_id_ary);
	unset($id_ary);

	// If we have no users 
	if (!sizeof($add_id_ary) && !sizeof($update_id_ary))
	{
		return 'GROUP_USERS_EXIST';
	}

	if (sizeof($add_id_ary))
	{
		// Insert the new users 
		switch (SQL_LAYER)
		{
			case 'mysql':
			case 'mysql4':
				$sql = 'INSERT INTO ' . USER_GROUP_TABLE . " (user_id, group_id, group_leader) 
					VALUES " . implode(', ', preg_replace('#^([0-9]+)$#', "(\\1, $group_id, $leader)",  $add_id_ary));
				$db->sql_query($sql);
				break;

			case 'mssql':
			case 'mssql-odbc':
			case 'sqlite':
				$sql = 'INSERT INTO ' . USER_GROUP_TABLE . " (user_id, group_id, group_leader) 
					" . implode(' UNION ALL ', preg_replace('#^([0-9]+)$#', "(\\1, $group_id, $leader)",  $add_id_ary));
				$db->sql_query($sql);
				break;

			default:
				foreach ($add_id_ary as $user_id)
				{
					$sql = 'INSERT INTO ' . USER_GROUP_TABLE . " (user_id, group_id, group_leader)
						VALUES ($user_id, $group_id, $leader)";
					$db->sql_query($sql);
				}
				break;
		}
	}

	$usernames = array();
	if (sizeof($update_id_ary))
	{
		$sql = 'UPDATE ' . USER_GROUP_TABLE . ' 
			SET group_leader = 1 
			WHERE user_id IN (' . implode(', ', $update_id_ary) . ")
				AND group_id = $group_id";
		$db->sql_query($sql);

		foreach ($update_id_ary as $id)
		{
			$usernames[] = $username_ary[$id];
		}
	}
	else
	{
		foreach ($add_id_ary as $id)
		{
			$usernames[] = $username_ary[$id];
		}
	}

	if ($default)
	{
		$attribute_ary = array('group_colour' => 'string', 'group_rank' => 'int', 'group_avatar' => 'string', 'group_avatar_type' => 'int', 'group_avatar_width' => 'int', 'group_avatar_height' => 'int');

		// Were group attributes passed to the function? If not we need to obtain them
		if (func_num_args() > 6)
		{
			$i = 6;
			foreach ($attribute_ary as $attribute => $type)
			{
				if (func_num_args() > $i && ($value = func_get_arg($i)) !== false)
				{
					settype($value, $type);

					$sql_ary[$attribute] = $$attribute = $value;
				}
				$i++;
			}
		}
		else
		{
			$sql = 'SELECT group_colour, group_rank, group_avatar, group_avatar_type, group_avatar_width, group_avatar_height  
				FROM ' . GROUPS_TABLE . " 
				WHERE group_id = $group_id";
			$result = $db->sql_query($sql);

			if (!extract($db->sql_fetchrow($result)))
			{
				trigger_error("Could not obtain group attributes for group_id $group_id", E_USER_ERROR);
			}
		}

		$sql_set = '';
		foreach ($attribute_ary as $attribute => $type)
		{
			if (isset($$attribute))
			{
				$field = str_replace('group_', 'user_', $attribute);

				switch ($type)
				{
					case 'int':
						$sql_set .= ", $field = " . (int) $$attribute;
						break;
					case 'double':
						$sql_set .= ", $field = " . (double) $$attribute;
						break;
					case 'string':
						$sql_set .= ", $field = '" . (string) $db->sql_escape($$attribute) . "'";
						break;
				}
			}
		}

		$sql = 'UPDATE ' . USERS_TABLE . "
			SET group_id = $group_id$sql_set  
			WHERE user_id IN (" . implode(', ', $user_id_ary) . ')';
		$db->sql_query($sql);
	}

	// Clear permissions cache of relevant users
	$auth->acl_clear_prefetch($user_id_ary);

	if (!$group_name)
	{
		$sql = 'SELECT group_name
			FROM ' . GROUPS_TABLE . " 
			WHERE group_id = $group_id";
		$result = $db->sql_query($sql);

		if (!extract($db->sql_fetchrow($result)))
		{
			trigger_error("Could not obtain name of group $group_id", E_USER_ERROR);
		}
	}

	if (!function_exists('add_log'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
	}

	$log = ($leader) ? 'LOG_MODS_ADDED' : 'LOG_USERS_ADDED';

	add_log('admin', $log, $group_name, implode(', ', $username_ary));

	unset($username_ary);
	unset($user_id_ary);

	return false;
}

// Remove a user/s from a given group. When we remove users we update their
// default group_id. We do this by examining which "special" groups they belong
// to. The selection is made based on a reasonable priority system
function group_user_del($group_id, $user_id_ary = false, $username_ary = false, $group_name = false)
{
	global $db, $auth;

	$group_order = array('ADMINISTRATORS', 'SUPER_MODERATORS', 'REGISTERED_COPPA', 'REGISTERED', 'BOTS', 'GUESTS');

	$attribute_ary = array('group_colour' => 'string', 'group_rank' => 'int', 'group_avatar' => 'string', 'group_avatar_type' => 'int', 'group_avatar_width' => 'int', 'group_avatar_height' => 'int');

	// We need both username and user_id info
	user_get_id_name($user_id_ary, $username_ary);

	$sql = 'SELECT * 
		FROM ' . GROUPS_TABLE . ' 
		WHERE group_name IN (' . implode(', ', preg_replace('#^(.*)$#', "'\\1'", $group_order)) . ')';
	$result = $db->sql_query($sql);

	$group_order_id = $special_group_data = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$group_order_id[$row['group_name']] = $row['group_id'];

		$special_group_data[$row['group_id']]['group_colour']			= $row['group_colour'];
		$special_group_data[$row['group_id']]['group_rank']				= $row['group_rank'];
		$special_group_data[$row['group_id']]['group_avatar']			= $row['group_avatar'];
		$special_group_data[$row['group_id']]['group_avatar_type']		= $row['group_avatar_type'];
		$special_group_data[$row['group_id']]['group_avatar_width']		= $row['group_avatar_width'];
		$special_group_data[$row['group_id']]['group_avatar_height']	= $row['group_avatar_height'];
	}
	$db->sql_freeresult($result);

	// What special group memberships exist for these users?
	$sql = 'SELECT g.group_id, g.group_name, ug.user_id 
		FROM ' . USER_GROUP_TABLE . ' ug, ' . GROUPS_TABLE . ' g 
		WHERE ug.user_id IN (' . implode(', ', $user_id_ary) . ") 
			AND g.group_id = ug.group_id
			AND g.group_id <> $group_id 
			AND g.group_type = " . GROUP_SPECIAL . '
		ORDER BY ug.user_id, g.group_id';
	$result = $db->sql_query($sql);

	$temp_ary = array();
	while ($row = $db->sql_fetchrow($result))
	{
		if (!isset($temp_ary[$row['user_id']]) || array_search($row['group_name'], $group_order) < $temp_ary[$row['user_id']])
		{
			$temp_ary[$row['user_id']] = $row['group_id'];
		}
	}
	$db->sql_freeresult($result);

	$sql_where_ary = array();
	foreach ($temp_ary as $uid => $gid)
	{
		$sql_where_ary[$gid][] = $uid;
	}
	unset($temp_ary);

	foreach ($special_group_data as $gid => $default_data_ary)
	{
		if ($sql_where = implode(', ', $sql_where_ary[$gid]))
		{
			$sql_set = '';
			foreach ($special_group_data[$gid] as $attribute => $value)
			{
				$field = str_replace('group_', 'user_', $attribute);

				switch ($attribute_ary[$attribute])
				{
					case 'int':
						$sql_set .= ", $field = " . (int) $value;
						break;
					case 'double':
						$sql_set .= ", $field = " . (double) $value;
						break;
					case 'string':
						$sql_set .= ", $field = '" . $db->sql_escape($value) . "'";
						break;
				}
			}

			// Set new default
			$sql = 'UPDATE ' . USERS_TABLE . " 
				SET group_id = $gid$sql_set 
				WHERE user_id IN (" . implode(', ', $sql_where_ary[$gid]) . ')';
			$db->sql_query($sql);
		}
	}
	unset($special_group_data);

	$sql = 'DELETE FROM ' . USER_GROUP_TABLE . " 
		WHERE group_id = $group_id
			AND user_id IN (" . implode(', ', $user_id_ary) . ')';
	$db->sql_query($sql);
	unset($default_ary);

	// Clear permissions cache of relevant users
	$auth->acl_clear_prefetch($user_id_ary);

	if (!$group_name)
	{
		$sql = 'SELECT group_name
			FROM ' . GROUPS_TABLE . " 
			WHERE group_id = $group_id";
		$result = $db->sql_query($sql);

		if (!extract($db->sql_fetchrow($result)))
		{
			trigger_error("Could not obtain name of group $group_id", E_USER_ERROR);
		}
	}

	if (!function_exists('add_log'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
	}

	$log = 'LOG_GROUP_REMOVE';

	add_log('admin', $log, $group_name, implode(', ', $username_ary));

	unset($username_ary);
	unset($user_id_ary);

	return false;
}

// This is used to promote (to leader), demote or set as default a member/s
function group_user_attributes($action, $group_id, $user_id_ary = false, $username_ary = false, $group_name = false)
{
	global $db, $auth;

	// We need both username and user_id info
	user_get_id_name($user_id_ary, $username_ary);

	switch ($action)
	{
		case 'demote':
		case 'promote':
			$sql = 'UPDATE ' . USER_GROUP_TABLE . '
				SET group_leader = ' . (($action == 'promote') ? 1 : 0) . "  
				WHERE group_id = $group_id
					AND user_id IN (" . implode(', ', $user_id_ary) . ')';
			$db->sql_query($sql);

			$log = ($action == 'promote') ? 'LOG_GROUP_PROMOTED' : 'LOG_GROUP_DEMOTED';
			break;

		case 'approve':
			$sql = 'UPDATE ' . USER_GROUP_TABLE . " 
				SET user_pending = 0 
				WHERE group_id = $group_id 
					AND user_id IN (" . implode(', ', $user_id_ary) . ')';
			$db->sql_query($sql);

			$log = 'LOG_GROUP_APPROVE';
			break;

		case 'default':
			$attribute_ary = array('group_colour' => 'string', 'group_rank' => 'int', 'group_avatar' => 'string', 'group_avatar_type' => 'int', 'group_avatar_width' => 'int', 'group_avatar_height' => 'int');

			// Were group attributes passed to the function? If not we need
			// to obtain them
			if (func_num_args() > 5)
			{
				$i = 5;
				foreach ($attribute_ary as $attribute => $type)
				{
					if (func_num_args() > $i && ($value = func_get_arg($i)) !== false)
					{
						settype($value, $type);

						$sql_ary[$attribute] = $$attribute = $value;
					}
					$i++;
				}
			}
			else
			{
				$sql = 'SELECT group_colour, group_rank, group_avatar, group_avatar_type, group_avatar_width, group_avatar_height 
					FROM ' . GROUPS_TABLE . " 
					WHERE group_id = $group_id";
				$result = $db->sql_query($sql);

				if (!extract($db->sql_fetchrow($result)))
				{
					return 'NO_GROUP';
				}
				$db->sql_freeresult($result);
			}

			$sql_set = '';
			foreach ($attribute_ary as $attribute => $type)
			{
				if (isset($$attribute))
				{
					$field = str_replace('group_', 'user_', $attribute);

					switch ($type)
					{
						case 'int':
							$sql_set .= ", $field = " . (int) $$attribute;
							break;
						case 'double':
							$sql_set .= ", $field = " . (double) $$attribute;
							break;
						case 'string':
							$sql_set .= ", $field = '" . (string) $db->sql_escape($$attribute) . "'";
							break;
					}
				}
			}

			$sql = 'UPDATE ' . USERS_TABLE . "
				SET group_id = $group_id$sql_set  
				WHERE user_id IN (" . implode(', ', $user_id_ary) . ')';
			$db->sql_query($sql);

			$log = 'LOG_GROUP_DEFAULTS';
			break;
	}

	if (!function_exists('add_log'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
	}

	// Clear permissions cache of relevant users
	$auth->acl_clear_prefetch($user_id_ary);

	if (!$group_name)
	{
		$sql = 'SELECT group_name
			FROM ' . GROUPS_TABLE . " 
			WHERE group_id = $group_id";
		$result = $db->sql_query($sql);

		if (!extract($db->sql_fetchrow($result)))
		{
			trigger_error("Could not obtain name of group $group_id", E_USER_ERROR);
		}
	}

	add_log('admin', $log, $group_name, implode(', ', $username_ary));

	unset($username_ary);
	unset($user_id_ary);

	return false;
}

// Obtain either the members of a specified group or the groups to
// which the specified users are members
function group_memberships($group_id = false, $user_id_ary = false)
{
	global $db;

	if (!$group_id && !$user_id_ary)
	{
		return true;
	}

	if ($group_id)
	{
	}
	else if ($user_id_ary)
	{
	}

	return false;
}

?>