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

		$data['width'] = $width;
		$data['height'] = $height;
	}
	else if ($data['width'] > $config['avatar_max_width'] || $data['height'] > $config['avatar_max_height'])
	{
		$error[] = sprintf($user->lang['AVATAR_WRONG_SIZE'], $config['avatar_max_width'], $config['avatar_max_height']);
		return false;
	}

	// Set type
	$data['filename'] = $data['remotelink']; 
	$data['type'] = AVATAR_REMOTE;

	return $data;
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

	$data['filename'] = $data['user_id'] . '_' . str_replace($bad_chars, '_', $realname) . '.' . $filetype;
	$data['width'] = $width;
	$data['height'] = $height;

	if(!$php_move($filename, $phpbb_root_path . $config['avatar_path'] . '/' . $data['filename']))
	{
		@unlink($filename);
		$error[] = $user->lang['AVATAR_NOT_UPLOADED'];
		return false;
	}
	@unlink($filename);

	$filesize = @filesize($phpbb_root_path . $config['avatar_path'] . '/' . $data['filename']);
	if (!$filesize || $filesize > $config['avatar_filesize'])
	{
		@unlink($phpbb_root_path . $config['avatar_path'] . '/' . $data['filename']);
		$error[] = sprintf($user->lang['AVATAR_WRONG_FILESIZE'], $config['avatar_filesize']);
		return false;
	}

	// Set type
	$data['type'] = AVATAR_UPLOAD;

	return $data;
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


//
// Usergroup functions
//

function add_to_group($action, $group_id, $user_id_ary, $username_ary, $leader, $colour, $rank, $avatar, $avatar_type)
{
	global $db;

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
		$id_ary[] = $row['user_id'];
	}
	while ($row = $db->sql_fetchrow($result));
	$db->sql_freeresult($result);

	// Remove users who are already members of this group
	$sql = 'SELECT user_id, group_leader  
		FROM ' . USER_GROUP_TABLE . '   
		WHERE user_id IN (' . implode(', ', $id_ary) . ") 
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
	$add_id_ary = array_diff($id_ary, $add_id_ary);
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

		$sql = 'UPDATE ' . USERS_TABLE . " 
			SET user_permissions = '' 
			WHERE user_id IN (" . implode(', ', $add_id_ary) . ')';
		$db->sql_query($sql);
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
	unset($username_ary);

	// Update user settings (color, rank) if applicable
	// TODO
	// Do not update users who are not approved
	if (!empty($_POST['default']))
	{
		$sql = 'UPDATE ' . USERS_TABLE . " 
			SET group_id = $group_id, user_colour = '" . $db->sql_escape($color) . "', user_rank = $rank   
			WHERE user_id IN (" . implode(', ', array_merge($add_id_ary, $update_id_ary)) . ")";
		$db->sql_query($sql);
	}
	unset($update_id_ary);
	unset($add_id_ary);

	if (!function_exists('add_log'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
	}

	$log = ($leader) ? 'LOG_MODS_ADDED' : 'LOG_USERS_ADDED';
	add_log('admin', $log, $group_name, implode(', ', $usernames));

	return false;
}

function create_group($action, $group_id, &$type, &$name, &$desc, &$colour, &$rank, &$avatar)
{
	global $phpbb_root_path, $config, $db, $user;

	$error = array();

	$can_upload = (file_exists($phpbb_root_path . $config['avatar_path']) && is_writeable($phpbb_root_path . $config['avatar_path']) && (@ini_get('file_uploads') || strtolower(@ini_get('file_uploads')) == 'on')) ? true : false;

	if (isset($type) && $type != GROUP_SPECIAL)
	{
		$name = request_var('group_name', '');
		$type = request_var('group_type', 0);
	}
	$desc		= request_var('group_description', '');
	$colour2	= request_var('group_colour', '');
	$rank2		= request_var('group_rank', 0);

	$data['uploadurl']	= request_var('uploadurl', '');
	$data['remotelink'] = request_var('remotelink', '');
	$data['width']		= request_var('width', '');
	$data['height']		= request_var('height', '');
	$delete				= request_var('delete', '');

	// Check data
	if (!strlen($name) || strlen($name) > 40)
	{
		$error[] = (!strlen($name)) ? $user->lang['GROUP_ERR_USERNAME'] : $user->lang['GROUP_ERR_USER_LONG'];
	}

	if (strlen($desc) > 255)
	{
		$error[] = $user->lang['GROUP_ERR_DESC_LONG'];
	}

	if ($type < GROUP_OPEN || $type > GROUP_FREE)
	{
		$error[] = $user->lang['GROUP_ERR_TYPE'];
	}

	// Avatar stuff
	$var_ary = array(
		'uploadurl'		=> array('string', true, 5, 255), 
		'remotelink'	=> array('string', true, 5, 255), 
		'width'			=> array('string', true, 1, 3), 
		'height'		=> array('string', true, 1, 3), 
	);

	$error = validate_data($data, $var_ary);

	if (!sizeof($error))
	{
		$data['user_id'] = "g$group_id";

		if (!empty($_FILES['uploadfile']['tmp_name']) && $can_upload)
		{
			$data = avatar_upload($data, $error);
		}
		else if ($data['uploadurl'] && $can_upload)
		{
			$data = avatar_upload($data, $error);
		}
		else if ($data['remotelink'])
		{
			$data = avatar_remote($data, $error);
		}
		else if ($delete)
		{
			$data['filename'] = $data['width'] = $data['height'] = '';
		}

		// Update group preferences
		$sql_ary = array(
			'group_name'			=> (string) $name,
			'group_description'		=> (string) $desc,
			'group_type'			=> (int) $type,
			'group_rank'			=> (int) $rank2,
			'group_colour'			=> (string) $colour2, 
			'group_avatar'			=> (string) $data['filename'], 
			'group_avatar_type'		=> (int) $data['type'], 
			'group_avatar_width'	=> (int) $data['width'], 
			'group_avatar_height'	=> (int) $data['height'], 
		);

		$sql = ($action == 'edit' && $group_id) ? 'UPDATE ' . GROUPS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "	WHERE group_id = $group_id" : 'INSERT INTO ' . GROUPS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		if ($group_id && ($colour != $colour2 || $rank != $rank2 || $avatar != $data['filename']))
		{
			$sql_ary = array(
				'user_rank'			=> (string) $rank2,
				'user_colour'		=> (string) $colour2,
				'user_avatar'		=> (string) $data['filename'], 
				'user_avatar_type'	=> (int) $data['type'], 
				'user_avatar_width'	=> (int) $data['width'], 
				'user_avatar_height'=> (int) $data['height'], 
			);

			$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
				WHERE group_id = $group_id";
			$db->sql_query($sql);

			// Delete old avatar if present
			if ($avatar != '' && $avatar != $data['filename'])
			{
				avatar_delete($avatar);
			}
		}

		if (!function_exists('add_log'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
		}

		$log = ($action == 'edit') ? 'LOG_GROUP_UPDATED' : 'LOG_GROUP_CREATED';
		add_log('admin', $log, $group_name);
	}

	$colour = $colour2;
	$rank = $rank2;
	$avatar = $avatar2;

	return (sizeof($error)) ? $error : false;
}

// Call with: user_id_ary or username_ary set ... if both false entire group
// will be set default
function set_default_group($id, $user_id_ary, $username_ary, &$name, &$colour, &$rank, $avatar, $avatar_type)
{
	global $db;

	if (is_array($user_id_ary) || is_array($username_ary))
	{
		$sql_where = ($user_id_ary) ? 'user_id IN (' . implode(', ', $user_id_ary) . ')' : 'username IN (' . implode(', ', $username_ary) . ')';

		$sql = 'UPDATE ' . USERS_TABLE . "
			SET group_id = $id, user_colour = '$colour', user_rank = $rank  
			WHERE $sql_where";
		$db->sql_query($sql);
	}
	else
	{
		switch (SQL_LAYER)
		{
			case 'mysql':
			case 'mysql4':
				// With no subselect we do mysql updates in batches to ward off
				// potential issues with large groups

				$start = 0;
				do
				{
					$sql = 'SELECT user_id 
						FROM ' . USER_GROUP_TABLE . "
						WHERE group_id = $id 
						ORDER BY user_id 
						LIMIT $start, 200";
					$result = $db->sql_query($sql);

					$user_id_ary = array();
					if ($row = $db->sql_fetchrow($result))
					{
						do
						{
							$user_id_ary[] = $row['user_id'];
						}
						while ($row = $db->sql_fetchrow($result));

						$sql = 'UPDATE ' . USERS_TABLE . "
							SET group_id = $id, user_colour = '$colour', user_rank = $rank 
							WHERE user_id IN (" . implode(', ', $user_id_ary) . ')';
						$db->sql_query($sql);

						$start = (sizeof($user_id_ary) < 200) ? 0 : $start + 200;
					}
					else
					{
						$start = 0;
					}
					$db->sql_freeresult($result);
				}
				while ($start);
				break;

			default:
				$sql = 'UPDATE ' . USERS_TABLE . " 
					SET group_id = $id, user_colour = '$colour', user_rank = $rank  
					WHERE user_id IN (
						SELECT user_id
							FROM " . USER_GROUP_TABLE . "
							WHERE group_id = $id
					)";
				$db->sql_query($sql);
				break;
		}
	}

	if (!function_exists('add_log'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
	}

	add_log('admin', 'LOG_GROUP_DEFAULTS', $name);

	return false;
}

// Call with: user_id_ary or username_ary set ... if both false entire group
// will be approved
function approve_user($group_id, $user_id_ary, $username_ary, &$group_name) 
{
	global $db;

	if (is_array($user_id_ary) || is_array($username_ary))
	{
		$sql_where = ($user_id_ary) ? 'user_id IN (' . implode(', ', $user_id_ary) . ')' : 'username IN (' . implode(', ', $username_ary) . ')';

		$sql = 'SELECT user_id, username 
			FROM ' . USERS_TABLE . " 
			WHERE $sql_where";
	}
	else
	{
		$sql = 'SELECT u.user_id, u.username 
			FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . "  
			WHERE ug.group_id = $group_id
				AND u.user_id = ug.user_id";
	}
	$result = $db->sql_query($sql);

	$usernames = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$username_ary[] = $row['username'];
		$user_id_ary[]	= $row['user_id'];
	}
	$db->sql_freeresult($result);
		
	$sql = 'UPDATE ' . USER_GROUP_TABLE . " 
		SET user_pending = 0 
		WHERE group_id = $group_id 
			AND user_id IN (" . implode(', ', $user_id_ary) . ')';
	$db->sql_query($sql);

	add_log('admin', 'LOG_GROUP_APPROVE', $group_name, implode(', ', $username_ary));

	unset($username_ary);
	unset($user_id_ary);

	return false;
}

// Setting action to demote true will demote leaders to users 
// (if appropriate), deleting leaders removes them from group as with
// normal users
function group_memberships($action, $id, $user_id_ary, $username_ary, &$group_name)
{
	global $db;

	// If no user_id or username data is submitted we'll act  the entire group 
	if ($action == 'delete' && !$user_id_ary && !$username_ary)
	{
		$sql = 'SELECT user_id 
			FROM ' . USER_GROUP_TABLE . " 
			WHERE group_id = $id";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$user_id_ary[] = $row['user_id'];
		}
		$db->sql_freeresult($result);
	}

	$which_ary = ($user_id_ary) ? 'user_id_ary' : 'username_ary';

	if ($$which_ary  && !is_array($$which_ary))
	{
		$$which_ary = array($$which_ary);
	}

	$sql_in = ($which_ary == 'user_id_ary') ? array_map('intval', $user_id_ary) : preg_replace('#^[\s]*?(.*?)[\s]*?$#e', "\"'\" . \$db->sql_escape('\\1') . \"'\"", $username_ary);

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
		$id_ary[] = $row['user_id'];
	}
	while ($row = $db->sql_fetchrow($result));
	$db->sql_freeresult($result);

	switch ($action)
	{
		case 'demote':
			$sql = 'UPDATE ' . USER_GROUP_TABLE . '
				SET group_leader = 0 
				WHERE user_id IN (' . implode(', ', $id_ary) . ")  
					AND group_id = $id";
			$db->sql_query($sql);

			$log = 'LOG_GROUP_DEMOTED';
			break;

		case 'promote':
			$sql = 'UPDATE ' . USER_GROUP_TABLE . '
				SET group_leader = 1 
				WHERE user_id IN (' . implode(', ', $id_ary) . ")  
					AND group_id = $id";
			$db->sql_query($sql);

			$log = 'LOG_GROUP_PROMOTED';
			break;

		case 'delete':
		case 'deleteusers':
			$group_order = array('ADMINISTRATORS', 'SUPER_MODERATORS', 'REGISTERED', 'REGISTERED_COPPA', 'BOTS', 'GUESTS');

			$sql = 'SELECT * 
				FROM ' . GROUPS_TABLE . ' 
				WHERE group_name IN (' . implode(', ', preg_replace('#^(.*)$#', "'\\1'", $group_order)) . ')';
			$result = $db->sql_query($sql);

			$group_order_keys = $group_data = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$group_order_keys[$row['group_name']] = $row['group_id'];

				$group_data[$row['group_id']]['color'] = $row['group_colour'];
				$group_data[$row['group_id']]['rank'] = $row['group_rank'];
			}
			$db->sql_freeresult($result);

			$new_group_order = array();
			foreach ($group_order as $group)
			{
				$new_group_order[$group] = $group_order_keys[$group];
			}
			$group_order = $new_group_order;
			unset($new_group_order);
			unset($group_order_keys);

			$sql = 'SELECT g.group_id, g.group_name, ug.user_id 
				FROM ' . USER_GROUP_TABLE . ' ug, ' . GROUPS_TABLE . ' g 
				WHERE ug.user_id IN (' . implode(', ', $user_id_ary) . ") 
					AND g.group_id = ug.group_id
					AND g.group_id <> $id 
					AND g.group_type = " . GROUP_SPECIAL . '
				ORDER BY ug.user_id, g.group_id';
			$result = $db->sql_query($sql);

			$default_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$default_ary[$row['user_id']][] = $row['group_name'];
			}
			$db->sql_freeresult($result);

			foreach ($default_ary as $user_id => $group_ary)
			{
				foreach ($group_order as $group_name => $group_id)
				{
					if (in_array($group_name, $group_ary))
					{
						$default_group_ary[$group_id][] = $user_id;
						continue 2;
					}
				}
			}

			foreach ($default_group_ary as $group_id => $new_default_ary)
			{
				// Set new default
				$sql = 'UPDATE ' . USERS_TABLE . " 
					SET group_id = $group_id, user_colour = '" . $group_data[$group_id]['color'] . "', user_rank = " . $group_data[$group_id]['rank'] . " 
					WHERE user_id IN (" . implode(', ', $new_default_ary) . ')';
					$db->sql_query($sql);
			}
			unset($default_group_ary);

			if ($action == 'delete')
			{
				$sql = 'DELETE FROM ' . GROUPS_TABLE . " 
					WHERE group_id = $db";
				$db->sql_query($sql);

				$sql = 'DELETE FROM ' . USER_GROUP_TABLE . " 
					WHERE group_id = $id";
			}
			else
			{
				$sql = 'DELETE FROM ' . USER_GROUP_TABLE . " 
					WHERE group_id = $id
						AND user_id IN (" . implode(', ', array_keys($default_ary)) . ')';
			}
			$db->sql_query($sql);
			unset($default_ary);

			$log = ($action == 'deleteusers') ? 'LOG_GROUP_REMOVE' : 'LOG_GROUP_DELETED';
			break;
	}

	if (!function_exists('add_log'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
	}

	add_log('admin', $log, $group_name, implode(', ', $username_ary));

	return false;
}

?>