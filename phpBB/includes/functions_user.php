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

//
// Usergroup functions
//

function add_to_group($action, $group_id, $user_id_ary, $username_ary, $colour, $rank, $avatar, $avatar_type)
{
	global $db;

	$which_ary = ($user_id_ary) ? 'user_id_ary' : 'username_ary';

	if ($$which_ary  && !is_array($$which_ary ))
	{
		$user_id_ary = array($user_id_ary);
	}

	$sql_in = array();
	foreach ($$which_ary as $v)
	{
		if ($v = trim($v))
		{
			$sql_in[] = ($which_ary == 'user_id_ary') ? $v : "'$v'";
		}
	}
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

			if ($action == 'addleaders' && !$row['group_leader'])
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
		$group_leader = ($action == 'addleaders') ? 1  : 0;

		// Insert the new users 
		switch (SQL_LAYER)
		{
			case 'mysql':
			case 'mysql4':
				$sql = 'INSERT INTO ' . USER_GROUP_TABLE . " (user_id, group_id, group_leader) 
					VALUES " . implode(', ', preg_replace('#^([0-9]+)$#', "(\\1, $group_id, $group_leader)",  $add_id_ary));
				$db->sql_query($sql);
				break;

			case 'mssql':
			case 'sqlite':
				$sql = 'INSERT INTO ' . USER_GROUP_TABLE . " (user_id, group_id, group_leader) 
					" . implode(' UNION ALL ', preg_replace('#^([0-9]+)$#', "(\\1, $group_id, $group_leader)",  $add_id_ary));
				$db->sql_query($sql);
				break;

			default:
				foreach ($add_id_ary as $user_id)
				{
					$sql = 'INSERT INTO ' . USER_GROUP_TABLE . " (user_id, group_id, group_leader)
						VALUES ($user_id, $group_id, $group_leader)";
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
			SET group_id = $group_id, user_colour = '$color', user_rank = " . intval($rank) . "  
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

	$log = ($action == 'addleaders') ? 'LOG_MODS_ADDED' : 'LOG_USERS_ADDED';
	add_log('admin', $log, $group_name, implode(', ', $usernames));

	return false;
}

function create_group($action, $group_id, &$type, &$name, &$desc, &$colour, &$rank, &$avatar)
{
	global $db, $user;

	$error = array();

	if (isset($type) && $type != GROUP_SPECIAL)
	{
		$name = (!empty($_POST['group_name'])) ? stripslashes(htmlspecialchars($_POST['group_name'])) : '';
		$type = (!empty($_POST['group_type'])) ? intval($_POST['group_type']) : '';
	}
	$desc = (!empty($_POST['group_description'])) ? stripslashes(htmlspecialchars($_POST['group_description'])) : '';
	$colour2 = (!empty($_POST['group_colour'])) ? stripslashes(htmlspecialchars($_POST['group_colour'])) : '';
	$avatar2 = (!empty($_POST['group_avatar'])) ? stripslashes(htmlspecialchars($_POST['group_avatar'])) : '';
	$rank2 = (isset($_POST['group_rank'])) ? intval($_POST['group_rank']) : '';

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

	// Update DB
	if (!sizeof($error))
	{
		// Update group preferences
		$sql_ary = array(
			'group_name'		=> (string) $name,
			'group_description'	=> (string) $desc,
			'group_type'		=> (int) $type,
			'group_rank'		=> (int) $rank2,
			'group_colour'		=> (string) $colour2,
		);

		$sql = ($action == 'edit' && $group_id) ? 'UPDATE ' . GROUPS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "	WHERE group_id = $group_id" : 'INSERT INTO ' . GROUPS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		if ($group_id && ($colour != $colour2 || $rank != $rank2 || $avatar != $avatar2))
		{
			$sql_ary = array(
				'user_rank'		=> (string) $rank2,
				'user_colour'	=> (string) $colour2,
			);

			$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
				WHERE group_id = $group_id";
			$db->sql_query($sql);
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

// If user_id or username_ary are set users are deleted, else group is
// removed. Setting action to demote true will demote leaders to users 
// (if appropriate), deleting leaders removes them from group as with
// normal users
function remove_from_group($type, $id, $user_id_ary, $username_ary, &$group_name)
{
	global $db;

	// Delete or demote individuals if data exists, else delete group
	if (is_array($user_id_ary) || is_array($username_ary))
	{
		$sql_where = ($user_id_ary) ? 'user_id IN (' . implode(', ', $user_id_ary) . ')' : 'username IN (' . implode(', ', $username_ary) . ')';

		$sql = 'SELECT user_id, username 
			FROM ' . USERS_TABLE . " 
			WHERE $sql_where";
		$result = $db->sql_query($sql);

		$usernames = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$username_ary[] = $row['username'];
			$user_id_ary[]	= $row['user_id'];
		}
		$db->sql_freeresult($result);

		switch ($type)
		{
			case 'demote':
				$sql = 'UPDATE ' . USER_GROUP_TABLE . "
					SET group_leader = 0 
					WHERE $sql_where";
				$db->sql_query($sql);
				break;

			default:
				$sql = 'SELECT g.group_id, g.group_name, u.user_id 
					FROM ' . USER_GROUP_TABLE . ' ug, ' . GROUPS_TABLE . ' g 
					WHERE u.user_id IN ' . implode(', ', $user_id_ary) . " 
						AND ug.group_id <> $group_id 
						AND g.group_type = " . GROUP_SPECIAL . '  
					GROUP BY u.user_id';
				break;
		}
	}
	else
	{
	}

	if (!function_exists('add_log'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
	}

	$log = ($action == 'demote') ? 'LOG_GROUP_DEMOTED' : (($action == 'deleteusers') ? 'LOG_GROUP_REMOVE' : 'LOG_GROUP_DELETED');
	add_log('admin', $log, $name, implode(', ', $username_ary));

	return false;
}

?>