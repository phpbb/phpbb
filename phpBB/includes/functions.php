<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Common global functions

/**
* Wrapper function of phpbb_request::variable which exists for backwards compatability.
* See {@link phpbb_request::variable phpbb_request::variable} for documentation of this function's use.
*
* @param string|array	$var_name	The form variable's name from which data shall be retrieved.
* 									If the value is an array this may be an array of indizes which will give
* 									direct access to a value at any depth. E.g. if the value of "var" is array(1 => "a")
* 									then specifying array("var", 1) as the name will return "a".
* @param mixed			$default	A default value that is returned if the variable was not set.
* 									This function will always return a value of the same type as the default.
* @param bool			$multibyte	If $default is a string this paramater has to be true if the variable may contain any UTF-8 characters
*									Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks
* @param bool			$cookie		This param is mapped to phpbb_request::COOKIE as the last param for phpbb_request::variable for backwards compatability reasons.
*
* @return mixed	The value of $_REQUEST[$var_name] run through {@link set_var set_var} to ensure that the type is the
*				the same as that of $default. If the variable is not set $default is returned.
*/
function request_var($var_name, $default, $multibyte = false, $cookie = false)
{
	return phpbb_request::variable($var_name, $default, $multibyte, ($cookie) ? phpbb_request::COOKIE : phpbb_request::REQUEST);
}

/**
* Set config value.
* Creates missing config entry if update did not succeed and phpbb::$config for this entry empty.
*
* @param string	$config_name	The configuration keys name
* @param string	$config_value	The configuration value
* @param bool	$is_dynamic		True if the configuration entry is not cached
*/
function set_config($config_name, $config_value, $is_dynamic = false)
{
	$sql = 'UPDATE ' . CONFIG_TABLE . "
		SET config_value = '" . phpbb::$db->sql_escape($config_value) . "'
		WHERE config_name = '" . phpbb::$db->sql_escape($config_name) . "'";
	phpbb::$db->sql_query($sql);

	if (!phpbb::$db->sql_affectedrows() && !isset(phpbb::$config[$config_name]))
	{
		$sql = 'INSERT INTO ' . CONFIG_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', array(
			'config_name'	=> (string) $config_name,
			'config_value'	=> (string) $config_value,
			'is_dynamic'	=> (int) $is_dynamic,
		));
		phpbb::$db->sql_query($sql);
	}

	phpbb::$config[$config_name] = $config_value;

	if (!$is_dynamic)
	{
		phpbb::$acm->destroy('#config');
	}
}

/**
* Return formatted string for filesizes
*/
function get_formatted_filesize($bytes, $add_size_lang = true)
{
	if ($bytes >= pow(2, 20))
	{
		return ($add_size_lang) ? round($bytes / 1024 / 1024, 2) . ' ' . phpbb::$user->lang['MIB'] : round($bytes / 1024 / 1024, 2);
	}

	if ($bytes >= pow(2, 10))
	{
		return ($add_size_lang) ? round($bytes / 1024, 2) . ' ' . phpbb::$user->lang['KIB'] : round($bytes / 1024, 2);
	}

	return ($add_size_lang) ? ($bytes) . ' ' . phpbb::$user->lang['BYTES'] : ($bytes);
}

/**
* Determine whether we are approaching the maximum execution time. Should be called once
* at the beginning of the script in which it's used.
* @return	bool	Either true if the maximum execution time is nearly reached, or false
*					if some time is still left.
*/
function still_on_time($extra_time = 15)
{
	static $max_execution_time, $start_time;

	$time = explode(' ', microtime());
	$current_time = $time[0] + $time[1];

	if (empty($max_execution_time))
	{
		$max_execution_time = (function_exists('ini_get')) ? (int) @ini_get('max_execution_time') : (int) @get_cfg_var('max_execution_time');

		// If zero, then set to something higher to not let the user catch the ten seconds barrier.
		if ($max_execution_time === 0)
		{
			$max_execution_time = 50 + $extra_time;
		}

		$max_execution_time = min(max(10, ($max_execution_time - $extra_time)), 50);

		// For debugging purposes
		// $max_execution_time = 10;

		global $starttime;
		$start_time = (empty($starttime)) ? $current_time : $starttime;
	}

	return (ceil($current_time - $start_time) < $max_execution_time) ? true : false;
}

/**
* Global function for chmodding directories and files for internal use
* This function determines owner and group whom the file belongs to and user and group of PHP and then set safest possible file permissions.
* The function determines owner and group from common.php file and sets the same to the provided file. Permissions are mapped to the group, user always has rw(x) permission.
* The function uses bit fields to build the permissions.
* The function sets the appropiate execute bit on directories.
*
* Supported constants representing bit fields are:
*
* phpbb::CHMOD_ALL - all permissions (7)
* phpbb::CHMOD_READ - read permission (4)
* phpbb::CHMOD_WRITE - write permission (2)
* phpbb::CHMOD_EXECUTE - execute permission (1)
*
* NOTE: The function uses POSIX extension and fileowner()/filegroup() functions. If any of them is disabled, this function tries to build proper permissions, by calling is_readable() and is_writable() functions.
*
* @param $filename The file/directory to be chmodded
* @param $perms Permissions to set
* @return true on success, otherwise false
*
* @author faw, phpBB Group
*/
function phpbb_chmod($filename, $perms = phpbb::CHMOD_READ)
{
	// Return if the file no longer exists.
	if (!file_exists($filename))
	{
		return false;
	}

	if (!function_exists('fileowner') || !function_exists('filegroup'))
	{
		$file_uid = $file_gid = false;
		$common_php_owner = $common_php_group = false;
	}
	else
	{
		// Determine owner/group of common.php file and the filename we want to change here
		$common_php_owner = fileowner(PHPBB_ROOT_PATH . 'common.' . PHP_EXT);
		$common_php_group = filegroup(PHPBB_ROOT_PATH . 'common.' . PHP_EXT);

		$file_uid = fileowner($filename);
		$file_gid = filegroup($filename);

		// Try to set the owner to the same common.php has
		if ($common_php_owner !== $file_uid && $common_php_owner !== false && $file_uid !== false)
		{
			// Will most likely not work
			if (@chown($filename, $common_php_owner));
			{
				clearstatcache();
				$file_uid = fileowner($filename);
			}
		}

		// Try to set the group to the same common.php has
		if ($common_php_group !== $file_gid && $common_php_group !== false && $file_gid !== false)
		{
			if (@chgrp($filename, $common_php_group));
			{
				clearstatcache();
				$file_gid = filegroup($filename);
			}
		}
	}

	// And the owner and the groups PHP is running under.
	$php_uid = (function_exists('posix_getuid')) ? @posix_getuid() : false;
	$php_gids = (function_exists('posix_getgroups')) ? @posix_getgroups() : false;

	// Who is PHP?
	if ($file_uid === false || $file_gid === false || $php_uid === false || $php_gids === false)
	{
		$php = NULL;
	}
	else if ($file_uid == $php_uid /* && $common_php_owner !== false && $common_php_owner === $file_uid*/)
	{
		$php = 'owner';
	}
	else if (in_array($file_gid, $php_gids))
	{
		$php = 'group';
	}
	else
	{
		$php = 'other';
	}

	// Owner always has read/write permission
	$owner = phpbb::CHMOD_READ | phpbb::CHMOD_WRITE;
	if (is_dir($filename))
	{
		$owner |= phpbb::CHMOD_EXECUTE;

		// Only add execute bit to the permission if the dir needs to be readable
		if ($perms & phpbb::CHMOD_READ)
		{
			$perms |= phpbb::CHMOD_EXECUTE;
		}
	}

	switch ($php)
	{
		case null:
		case 'owner':
			/* ATTENTION: if php is owner or NULL we set it to group here. This is the most failsafe combination for the vast majority of server setups.

			$result = @chmod($filename, ($owner << 6) + (0 << 3) + (0 << 0));

			clearstatcache();

			if (!is_null($php) || (is_readable($filename) && is_writable($filename)))
			{
				break;
			}
		*/

		case 'group':
			$result = @chmod($filename, ($owner << 6) + ($perms << 3) + (0 << 0));

			clearstatcache();

			if (!is_null($php) || ((!($perms & phpbb::CHMOD_READ) || is_readable($filename)) && (!($perms & phpbb::CHMOD_WRITE) || is_writable($filename))))
			{
				break;
			}

		case 'other':
			$result = @chmod($filename, ($owner << 6) + ($perms << 3) + ($perms << 0));

			clearstatcache();

			if (!is_null($php) || ((!($perms & phpbb::CHMOD_READ) || is_readable($filename)) && (!($perms & phpbb::CHMOD_WRITE) || is_writable($filename))))
			{
				break;
			}

		default:
			return false;
		break;
	}

	return $result;
}

	/**
	* Add a secret hash   for use in links/GET requests
	* @param string  $link_name The name of the link; has to match the name used in check_link_hash, otherwise no restrictions apply
	* @return string the hash
	*/
/*
@todo should use our hashing instead of a "custom" one
*/
	function generate_link_hash($link_name)
	{
		if (!isset(phpbb::$user->data["hash_$link_name"]))
		{
			phpbb::$user->data["hash_$link_name"] = substr(sha1(phpbb::$user->data['user_form_salt'] . $link_name), 0, 8);
		}

		return phpbb::$user->data["hash_$link_name"];
	}


	/**
	* checks a link hash - for GET requests
	* @param string $token the submitted token
	* @param string $link_name The name of the link
	* @return boolean true if all is fine
	*/

	function check_link_hash($token, $link_name)
	{
		return $token === generate_link_hash($link_name);
	}

// functions used for building option fields

/**
* Pick a language, any language ...
*/
function language_select($default = '')
{
	$sql = 'SELECT lang_iso, lang_local_name
		FROM ' . LANG_TABLE . '
		ORDER BY lang_english_name';
	$result = phpbb::$db->sql_query($sql);

	$lang_options = '';
	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$selected = ($row['lang_iso'] == $default) ? ' selected="selected"' : '';
		$lang_options .= '<option value="' . $row['lang_iso'] . '"' . $selected . '>' . $row['lang_local_name'] . '</option>';
	}
	phpbb::$db->sql_freeresult($result);

	return $lang_options;
}

/**
* Pick a template/theme combo,
*/
function style_select($default = '', $all = false)
{
	$sql_where = (!$all) ? 'WHERE style_active = 1 ' : '';
	$sql = 'SELECT style_id, style_name
		FROM ' . STYLES_TABLE . "
		$sql_where
		ORDER BY style_name";
	$result = phpbb::$db->sql_query($sql);

	$style_options = '';
	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$selected = ($row['style_id'] == $default) ? ' selected="selected"' : '';
		$style_options .= '<option value="' . $row['style_id'] . '"' . $selected . '>' . $row['style_name'] . '</option>';
	}
	phpbb::$db->sql_freeresult($result);

	return $style_options;
}

/**
* Pick a timezone
*/
function tz_select($default = '', $truncate = false)
{
	$tz_select = '';
	foreach (phpbb::$user->lang['tz_zones'] as $offset => $zone)
	{
		if ($truncate)
		{
			$zone_trunc = truncate_string($zone, 50, 255, false, '...');
		}
		else
		{
			$zone_trunc = $zone;
		}

		if (is_numeric($offset))
		{
			$selected = ($offset == $default) ? ' selected="selected"' : '';
			$tz_select .= '<option title="'.$zone.'" value="' . $offset . '"' . $selected . '>' . $zone_trunc . '</option>';
		}
	}

	return $tz_select;
}

// Functions handling topic/post tracking/marking

/**
* Marks a topic/forum as read
* Marks a topic as posted to
*
* @param int $user_id can only be used with $mode == 'post'
*/
function markread($mode, $forum_id = false, $topic_id = false, $post_time = 0, $user_id = 0)
{
	if ($mode == 'all')
	{
		if ($forum_id === false || !sizeof($forum_id))
		{
			if (phpbb::$config['load_db_lastread'] && phpbb::$user->data['is_registered'])
			{
				// Mark all forums read (index page)
				phpbb::$db->sql_query('DELETE FROM ' . TOPICS_TRACK_TABLE . ' WHERE user_id = ' . phpbb::$user->data['user_id']);
				phpbb::$db->sql_query('DELETE FROM ' . FORUMS_TRACK_TABLE . ' WHERE user_id = ' . phpbb::$user->data['user_id']);
				phpbb::$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_lastmark = ' . time() . ' WHERE user_id = ' . phpbb::$user->data['user_id']);
			}
			else if (phpbb::$config['load_anon_lastread'] || phpbb::$user->data['is_registered'])
			{
				$tracking_topics = phpbb_request::variable(phpbb::$config['cookie_name'] . '_track', '', false, phpbb_request::COOKIE);
				$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();

				unset($tracking_topics['tf']);
				unset($tracking_topics['t']);
				unset($tracking_topics['f']);
				$tracking_topics['l'] = base_convert(time() - phpbb::$config['board_startdate'], 10, 36);

				phpbb::$user->set_cookie('track', tracking_serialize($tracking_topics), time() + 31536000);
				phpbb_request::overwrite(phpbb::$config['cookie_name'] . '_track', tracking_serialize($tracking_topics), phpbb_request::COOKIE);

				unset($tracking_topics);

				if (phpbb::$user->data['is_registered'])
				{
					phpbb::$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_lastmark = ' . time() . ' WHERE user_id = ' . phpbb::$user->data['user_id']);
				}
			}
		}

		return;
	}
	else if ($mode == 'topics')
	{
		// Mark all topics in forums read
		if (!is_array($forum_id))
		{
			$forum_id = array($forum_id);
		}

		// Add 0 to forums array to mark global announcements correctly
		$forum_id[] = 0;

		if (phpbb::$config['load_db_lastread'] && phpbb::$user->data['is_registered'])
		{
			$sql = 'DELETE FROM ' . TOPICS_TRACK_TABLE . '
				WHERE user_id = ' . phpbb::$user->data['user_id'] . '
					AND ' . phpbb::$db->sql_in_set('forum_id', $forum_id);
			phpbb::$db->sql_query($sql);

			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TRACK_TABLE . '
				WHERE user_id = ' . phpbb::$user->data['user_id'] . '
					AND ' . phpbb::$db->sql_in_set('forum_id', $forum_id);
			$result = phpbb::$db->sql_query($sql);

			$sql_update = array();
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$sql_update[] = $row['forum_id'];
			}
			phpbb::$db->sql_freeresult($result);

			if (sizeof($sql_update))
			{
				$sql = 'UPDATE ' . FORUMS_TRACK_TABLE . '
					SET mark_time = ' . time() . '
					WHERE user_id = ' . phpbb::$user->data['user_id'] . '
						AND ' . phpbb::$db->sql_in_set('forum_id', $sql_update);
				phpbb::$db->sql_query($sql);
			}

			if ($sql_insert = array_diff($forum_id, $sql_update))
			{
				$sql_ary = array();
				foreach ($sql_insert as $f_id)
				{
					$sql_ary[] = array(
						'user_id'	=> (int) phpbb::$user->data['user_id'],
						'forum_id'	=> (int) $f_id,
						'mark_time'	=> time()
					);
				}

				phpbb::$db->sql_multi_insert(FORUMS_TRACK_TABLE, $sql_ary);
			}
		}
		else if (phpbb::$config['load_anon_lastread'] || phpbb::$user->data['is_registered'])
		{
			$tracking = phpbb_request::variable(phpbb::$config['cookie_name'] . '_track', '', false, phpbb_request::COOKIE);
			$tracking = ($tracking) ? tracking_unserialize($tracking) : array();

			foreach ($forum_id as $f_id)
			{
				$topic_ids36 = (isset($tracking['tf'][$f_id])) ? $tracking['tf'][$f_id] : array();

				if (isset($tracking['tf'][$f_id]))
				{
					unset($tracking['tf'][$f_id]);
				}

				foreach ($topic_ids36 as $topic_id36)
				{
					unset($tracking['t'][$topic_id36]);
				}

				if (isset($tracking['f'][$f_id]))
				{
					unset($tracking['f'][$f_id]);
				}

				$tracking['f'][$f_id] = base_convert(time() - phpbb::$config['board_startdate'], 10, 36);
			}

			if (isset($tracking['tf']) && empty($tracking['tf']))
			{
				unset($tracking['tf']);
			}

			phpbb::$user->set_cookie('track', tracking_serialize($tracking), time() + 31536000);
			phpbb_request::overwrite(phpbb::$config['cookie_name'] . '_track', tracking_serialize($tracking), phpbb_request::COOKIE);

			unset($tracking);
		}

		return;
	}
	else if ($mode == 'topic')
	{
		if ($topic_id === false || $forum_id === false)
		{
			return;
		}

		if (phpbb::$config['load_db_lastread'] && phpbb::$user->data['is_registered'])
		{
			$sql = 'UPDATE ' . TOPICS_TRACK_TABLE . '
				SET mark_time = ' . (($post_time) ? $post_time : time()) . '
				WHERE user_id = ' . phpbb::$user->data['user_id'] . '
					AND topic_id = ' . $topic_id;
			phpbb::$db->sql_query($sql);

			// insert row
			if (!phpbb::$db->sql_affectedrows())
			{
				phpbb::$db->sql_return_on_error(true);

				$sql_ary = array(
					'user_id'		=> (int) phpbb::$user->data['user_id'],
					'topic_id'		=> (int) $topic_id,
					'forum_id'		=> (int) $forum_id,
					'mark_time'		=> ($post_time) ? (int) $post_time : time(),
				);

				phpbb::$db->sql_query('INSERT INTO ' . TOPICS_TRACK_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_ary));

				phpbb::$db->sql_return_on_error(false);
			}
		}
		else if (phpbb::$config['load_anon_lastread'] || phpbb::$user->data['is_registered'])
		{
			$tracking = phpbb_request::variable(phpbb::$config['cookie_name'] . '_track', '', false, phpbb_request::COOKIE);
			$tracking = ($tracking) ? tracking_unserialize($tracking) : array();

			$topic_id36 = base_convert($topic_id, 10, 36);

			if (!isset($tracking['t'][$topic_id36]))
			{
				$tracking['tf'][$forum_id][$topic_id36] = true;
			}

			$post_time = ($post_time) ? $post_time : time();
			$tracking['t'][$topic_id36] = base_convert($post_time - phpbb::$config['board_startdate'], 10, 36);

			// If the cookie grows larger than 10000 characters we will remove the smallest value
			// This can result in old topics being unread - but most of the time it should be accurate...
			if (strlen(phpbb_request::variable(phpbb::$config['cookie_name'] . '_track', '', false, phpbb_request::COOKIE)) > 10000)
			{
				//echo 'Cookie grown too large' . print_r($tracking, true);

				// We get the ten most minimum stored time offsets and its associated topic ids
				$time_keys = array();
				for ($i = 0; $i < 10 && sizeof($tracking['t']); $i++)
				{
					$min_value = min($tracking['t']);
					$m_tkey = array_search($min_value, $tracking['t']);
					unset($tracking['t'][$m_tkey]);

					$time_keys[$m_tkey] = $min_value;
				}

				// Now remove the topic ids from the array...
				foreach ($tracking['tf'] as $f_id => $topic_id_ary)
				{
					foreach ($time_keys as $m_tkey => $min_value)
					{
						if (isset($topic_id_ary[$m_tkey]))
						{
							$tracking['f'][$f_id] = $min_value;
							unset($tracking['tf'][$f_id][$m_tkey]);
						}
					}
				}

				if (phpbb::$user->data['is_registered'])
				{
					phpbb::$user->data['user_lastmark'] = intval(base_convert(max($time_keys) + phpbb::$config['board_startdate'], 36, 10));
					phpbb::$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_lastmark = ' . phpbb::$user->data['user_lastmark'] . ' WHERE user_id = ' . phpbb::$user->data['user_id']);
				}
				else
				{
					$tracking['l'] = max($time_keys);
				}
			}

			phpbb::$user->set_cookie('track', tracking_serialize($tracking), time() + 31536000);
			phpbb_request::overwrite(phpbb::$config['cookie_name'] . '_track', tracking_serialize($tracking));
		}

		return;
	}
	else if ($mode == 'post')
	{
		if ($topic_id === false)
		{
			return;
		}

		$use_user_id = (!$user_id) ? phpbb::$user->data['user_id'] : $user_id;

		if (phpbb::$config['load_db_track'] && $use_user_id != ANONYMOUS)
		{
			phpbb::$db->sql_return_on_error(true);

			$sql_ary = array(
				'user_id'		=> (int) $use_user_id,
				'topic_id'		=> (int) $topic_id,
				'topic_posted'	=> 1
			);

			phpbb::$db->sql_query('INSERT INTO ' . TOPICS_POSTED_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_ary));

			phpbb::$db->sql_return_on_error(false);
		}

		return;
	}
}

/**
* Get topic tracking info by using already fetched info
*/
function get_topic_tracking($forum_id, $topic_ids, &$rowset, $forum_mark_time, $global_announce_list = false)
{
	$last_read = array();

	if (!is_array($topic_ids))
	{
		$topic_ids = array($topic_ids);
	}

	foreach ($topic_ids as $topic_id)
	{
		if (!empty($rowset[$topic_id]['mark_time']))
		{
			$last_read[$topic_id] = $rowset[$topic_id]['mark_time'];
		}
	}

	$topic_ids = array_diff($topic_ids, array_keys($last_read));

	if (sizeof($topic_ids))
	{
		$mark_time = array();

		// Get global announcement info
		if ($global_announce_list && sizeof($global_announce_list))
		{
			if (!isset($forum_mark_time[0]))
			{
				$sql = 'SELECT mark_time
					FROM ' . FORUMS_TRACK_TABLE . '
					WHERE user_id = ' . phpbb::$user->data['user_id'] . '
						AND forum_id = 0';
				$result = phpbb::$db->sql_query($sql);
				$row = phpbb::$db->sql_fetchrow($result);
				phpbb::$db->sql_freeresult($result);

				if ($row)
				{
					$mark_time[0] = $row['mark_time'];
				}
			}
			else
			{
				if ($forum_mark_time[0] !== false)
				{
					$mark_time[0] = $forum_mark_time[0];
				}
			}
		}

		if (!empty($forum_mark_time[$forum_id]) && $forum_mark_time[$forum_id] !== false)
		{
			$mark_time[$forum_id] = $forum_mark_time[$forum_id];
		}

		$user_lastmark = (isset($mark_time[$forum_id])) ? $mark_time[$forum_id] : phpbb::$user->data['user_lastmark'];

		foreach ($topic_ids as $topic_id)
		{
			if ($global_announce_list && isset($global_announce_list[$topic_id]))
			{
				$last_read[$topic_id] = (isset($mark_time[0])) ? $mark_time[0] : $user_lastmark;
			}
			else
			{
				$last_read[$topic_id] = $user_lastmark;
			}
		}
	}

	return $last_read;
}

/**
* Get topic tracking info from db (for cookie based tracking only this function is used)
*/
function get_complete_topic_tracking($forum_id, $topic_ids, $global_announce_list = false)
{
	$last_read = array();

	if (!is_array($topic_ids))
	{
		$topic_ids = array($topic_ids);
	}

	if (phpbb::$config['load_db_lastread'] && phpbb::$user->data['is_registered'])
	{
		$sql = 'SELECT topic_id, mark_time
			FROM ' . TOPICS_TRACK_TABLE . '
			WHERE user_id = ' . phpbb::$user->data['user_id'] . '
				AND ' . phpbb::$db->sql_in_set('topic_id', $topic_ids);
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$last_read[$row['topic_id']] = $row['mark_time'];
		}
		phpbb::$db->sql_freeresult($result);

		$topic_ids = array_diff($topic_ids, array_keys($last_read));

		if (sizeof($topic_ids))
		{
			$sql = 'SELECT forum_id, mark_time
				FROM ' . FORUMS_TRACK_TABLE . '
				WHERE user_id = ' . phpbb::$user->data['user_id'] . '
					AND forum_id ' .
					(($global_announce_list && sizeof($global_announce_list)) ? "IN (0, $forum_id)" : "= $forum_id");
			$result = phpbb::$db->sql_query($sql);

			$mark_time = array();
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$mark_time[$row['forum_id']] = $row['mark_time'];
			}
			phpbb::$db->sql_freeresult($result);

			$user_lastmark = (isset($mark_time[$forum_id])) ? $mark_time[$forum_id] : phpbb::$user->data['user_lastmark'];

			foreach ($topic_ids as $topic_id)
			{
				if ($global_announce_list && isset($global_announce_list[$topic_id]))
				{
					$last_read[$topic_id] = (isset($mark_time[0])) ? $mark_time[0] : $user_lastmark;
				}
				else
				{
					$last_read[$topic_id] = $user_lastmark;
				}
			}
		}
	}
	else if (phpbb::$config['load_anon_lastread'] || phpbb::$user->data['is_registered'])
	{
		global $tracking_topics;

		if (!isset($tracking_topics) || !sizeof($tracking_topics))
		{
			$tracking_topics = phpbb_request::variable(phpbb::$config['cookie_name'] . '_track', '', false, phpbb_request::COOKIE);
			$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();
		}

		if (!phpbb::$user->data['is_registered'])
		{
			$user_lastmark = (isset($tracking_topics['l'])) ? base_convert($tracking_topics['l'], 36, 10) + phpbb::$config['board_startdate'] : 0;
		}
		else
		{
			$user_lastmark = phpbb::$user->data['user_lastmark'];
		}

		foreach ($topic_ids as $topic_id)
		{
			$topic_id36 = base_convert($topic_id, 10, 36);

			if (isset($tracking_topics['t'][$topic_id36]))
			{
				$last_read[$topic_id] = base_convert($tracking_topics['t'][$topic_id36], 36, 10) + phpbb::$config['board_startdate'];
			}
		}

		$topic_ids = array_diff($topic_ids, array_keys($last_read));

		if (sizeof($topic_ids))
		{
			$mark_time = array();
			if ($global_announce_list && sizeof($global_announce_list))
			{
				if (isset($tracking_topics['f'][0]))
				{
					$mark_time[0] = base_convert($tracking_topics['f'][0], 36, 10) + phpbb::$config['board_startdate'];
				}
			}

			if (isset($tracking_topics['f'][$forum_id]))
			{
				$mark_time[$forum_id] = base_convert($tracking_topics['f'][$forum_id], 36, 10) + phpbb::$config['board_startdate'];
			}

			$user_lastmark = (isset($mark_time[$forum_id])) ? $mark_time[$forum_id] : $user_lastmark;

			foreach ($topic_ids as $topic_id)
			{
				if ($global_announce_list && isset($global_announce_list[$topic_id]))
				{
					$last_read[$topic_id] = (isset($mark_time[0])) ? $mark_time[0] : $user_lastmark;
				}
				else
				{
					$last_read[$topic_id] = $user_lastmark;
				}
			}
		}
	}

	return $last_read;
}

/**
* Check for read forums and update topic tracking info accordingly
*
* @param int $forum_id the forum id to check
* @param int $forum_last_post_time the forums last post time
* @param int $f_mark_time the forums last mark time if user is registered and load_db_lastread enabled
* @param int $mark_time_forum false if the mark time needs to be obtained, else the last users forum mark time
*
* @return true if complete forum got marked read, else false.
*/
function update_forum_tracking_info($forum_id, $forum_last_post_time, $f_mark_time = false, $mark_time_forum = false)
{
	global $tracking_topics;

	// Determine the users last forum mark time if not given.
	if ($mark_time_forum === false)
	{
		if (phpbb::$config['load_db_lastread'] && phpbb::$user->data['is_registered'])
		{
			$mark_time_forum = (!empty($f_mark_time)) ? $f_mark_time : phpbb::$user->data['user_lastmark'];
		}
		else if (phpbb::$config['load_anon_lastread'] || phpbb::$user->data['is_registered'])
		{
			$tracking_topics = phpbb_request::variable(phpbb::$config['cookie_name'] . '_track', '', false, phpbb_request::COOKIE);
			$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();

			if (!phpbb::$user->data['is_registered'])
			{
				phpbb::$user->data['user_lastmark'] = (isset($tracking_topics['l'])) ? (int) (base_convert($tracking_topics['l'], 36, 10) + phpbb::$config['board_startdate']) : 0;
			}

			$mark_time_forum = (isset($tracking_topics['f'][$forum_id])) ? (int) (base_convert($tracking_topics['f'][$forum_id], 36, 10) + phpbb::$config['board_startdate']) : phpbb::$user->data['user_lastmark'];
		}
	}

	// Check the forum for any left unread topics.
	// If there are none, we mark the forum as read.
	if (phpbb::$config['load_db_lastread'] && phpbb::$user->data['is_registered'])
	{
		if ($mark_time_forum >= $forum_last_post_time)
		{
			// We do not need to mark read, this happened before. Therefore setting this to true
			$row = true;
		}
		else
		{
			$sql = 'SELECT t.forum_id FROM ' . TOPICS_TABLE . ' t
				LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt ON (tt.topic_id = t.topic_id AND tt.user_id = ' . phpbb::$user->data['user_id'] . ')
				WHERE t.forum_id = ' . $forum_id . '
					AND t.topic_last_post_time > ' . $mark_time_forum . '
					AND t.topic_moved_id = 0
					AND (tt.topic_id IS NULL OR tt.mark_time < t.topic_last_post_time)
				GROUP BY t.forum_id';
			$result = phpbb::$db->sql_query_limit($sql, 1);
			$row = phpbb::$db->sql_fetchrow($result);
			phpbb::$db->sql_freeresult($result);
		}
	}
	else if (phpbb::$config['load_anon_lastread'] || phpbb::$user->data['is_registered'])
	{
		// Get information from cookie
		$row = false;

		if (!isset($tracking_topics['tf'][$forum_id]))
		{
			// We do not need to mark read, this happened before. Therefore setting this to true
			$row = true;
		}
		else
		{
			$sql = 'SELECT topic_id
				FROM ' . TOPICS_TABLE . '
				WHERE forum_id = ' . $forum_id . '
					AND topic_last_post_time > ' . $mark_time_forum . '
					AND topic_moved_id = 0';
			$result = phpbb::$db->sql_query($sql);

			$check_forum = $tracking_topics['tf'][$forum_id];
			$unread = false;

			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				if (!isset($check_forum[base_convert($row['topic_id'], 10, 36)]))
				{
					$unread = true;
					break;
				}
			}
			phpbb::$db->sql_freeresult($result);

			$row = $unread;
		}
	}
	else
	{
		$row = true;
	}

	if (!$row)
	{
		markread('topics', $forum_id);
		return true;
	}

	return false;
}

/**
* Transform an array into a serialized format
*/
function tracking_serialize($input)
{
	$out = '';
	foreach ($input as $key => $value)
	{
		if (is_array($value))
		{
			$out .= $key . ':(' . tracking_serialize($value) . ');';
		}
		else
		{
			$out .= $key . ':' . $value . ';';
		}
	}
	return $out;
}

/**
* Transform a serialized array into an actual array
*/
function tracking_unserialize($string, $max_depth = 3)
{
	$n = strlen($string);
	if ($n > 10010)
	{
		die('Invalid data supplied');
	}
	$data = $stack = array();
	$key = '';
	$mode = 0;
	$level = &$data;
	for ($i = 0; $i < $n; ++$i)
	{
		switch ($mode)
		{
			case 0:
				switch ($string[$i])
				{
					case ':':
						$level[$key] = 0;
						$mode = 1;
					break;
					case ')':
						unset($level);
						$level = array_pop($stack);
						$mode = 3;
					break;
					default:
						$key .= $string[$i];
				}
			break;

			case 1:
				switch ($string[$i])
				{
					case '(':
						if (sizeof($stack) >= $max_depth)
						{
							die('Invalid data supplied');
						}
						$stack[] = &$level;
						$level[$key] = array();
						$level = &$level[$key];
						$key = '';
						$mode = 0;
					break;
					default:
						$level[$key] = $string[$i];
						$mode = 2;
					break;
				}
			break;

			case 2:
				switch ($string[$i])
				{
					case ')':
						unset($level);
						$level = array_pop($stack);
						$mode = 3;
					break;
					case ';':
						$key = '';
						$mode = 0;
					break;
					default:
						$level[$key] .= $string[$i];
					break;
				}
			break;

			case 3:
				switch ($string[$i])
				{
					case ')':
						unset($level);
						$level = array_pop($stack);
					break;
					case ';':
						$key = '';
						$mode = 0;
					break;
					default:
						die('Invalid data supplied');
					break;
				}
			break;
		}
	}

	if (sizeof($stack) != 0 || ($mode != 0 && $mode != 3))
	{
		die('Invalid data supplied');
	}

	return $level;
}

// Pagination functions

/**
* Pagination routine, generates page number sequence
* tpl_prefix is for using different pagination blocks at one page
*/
function generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = false, $tpl_prefix = '')
{
	global $template;

	// Make sure $per_page is a valid value
	$per_page = ($per_page <= 0) ? 1 : $per_page;

	$seperator = '<span class="page-sep">' . phpbb::$user->lang['COMMA_SEPARATOR'] . '</span>';
	$total_pages = ceil($num_items / $per_page);

	if ($total_pages == 1 || !$num_items)
	{
		return false;
	}

	$on_page = floor($start_item / $per_page) + 1;
	$url_delim = (strpos($base_url, '?') === false) ? '?' : '&amp;';

	$page_string = ($on_page == 1) ? '<strong>1</strong>' : '<a href="' . $base_url . '">1</a>';

	if ($total_pages > 5)
	{
		$start_cnt = min(max(1, $on_page - 4), $total_pages - 5);
		$end_cnt = max(min($total_pages, $on_page + 4), 6);

		$page_string .= ($start_cnt > 1) ? ' ... ' : $seperator;

		for ($i = $start_cnt + 1; $i < $end_cnt; $i++)
		{
			$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . $base_url . "{$url_delim}start=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
			if ($i < $end_cnt - 1)
			{
				$page_string .= $seperator;
			}
		}

		$page_string .= ($end_cnt < $total_pages) ? ' ... ' : $seperator;
	}
	else
	{
		$page_string .= $seperator;

		for ($i = 2; $i < $total_pages; $i++)
		{
			$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . $base_url . "{$url_delim}start=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
			if ($i < $total_pages)
			{
				$page_string .= $seperator;
			}
		}
	}

	$page_string .= ($on_page == $total_pages) ? '<strong>' . $total_pages . '</strong>' : '<a href="' . $base_url . "{$url_delim}start=" . (($total_pages - 1) * $per_page) . '">' . $total_pages . '</a>';

	if ($add_prevnext_text)
	{
		if ($on_page != 1)
		{
			$page_string = '<a href="' . $base_url . "{$url_delim}start=" . (($on_page - 2) * $per_page) . '">' . phpbb::$user->lang['PREVIOUS'] . '</a>&nbsp;&nbsp;' . $page_string;
		}

		if ($on_page != $total_pages)
		{
			$page_string .= '&nbsp;&nbsp;<a href="' . $base_url . "{$url_delim}start=" . ($on_page * $per_page) . '">' . phpbb::$user->lang['NEXT'] . '</a>';
		}
	}

	$template->assign_vars(array(
		$tpl_prefix . 'BASE_URL'		=> $base_url,
		'A_' . $tpl_prefix . 'BASE_URL'	=> addslashes($base_url),
		$tpl_prefix . 'PER_PAGE'		=> $per_page,

		$tpl_prefix . 'PREVIOUS_PAGE'	=> ($on_page == 1) ? '' : $base_url . "{$url_delim}start=" . (($on_page - 2) * $per_page),
		$tpl_prefix . 'NEXT_PAGE'		=> ($on_page == $total_pages) ? '' : $base_url . "{$url_delim}start=" . ($on_page * $per_page),
		$tpl_prefix . 'TOTAL_PAGES'		=> $total_pages,
	));

	return $page_string;
}

/**
* Return current page (pagination)
*/
function on_page($num_items, $per_page, $start)
{
	global $template;

	// Make sure $per_page is a valid value
	$per_page = ($per_page <= 0) ? 1 : $per_page;

	$on_page = floor($start / $per_page) + 1;

	$template->assign_vars(array(
		'ON_PAGE'		=> $on_page)
	);

	return phpbb::$user->lang('PAGE_OF', $on_page, max(ceil($num_items / $per_page), 1));
}


//Form validation



/**
* Add a secret token to the form (requires the S_FORM_TOKEN template variable)
* @param string  $form_name The name of the form; has to match the name used in check_form_key, otherwise no restrictions apply
*/
function add_form_key($form_name)
{
	global $template;

	$now = time();
	$token_sid = (phpbb::$user->data['user_id'] == ANONYMOUS && !empty(phpbb::$config['form_token_sid_guests'])) ? phpbb::$user->session_id : '';
	$token = sha1($now . phpbb::$user->data['user_form_salt'] . $form_name . $token_sid);

	$s_fields = build_hidden_fields(array(
		'creation_time' => $now,
		'form_token'	=> $token,
	));

	$template->assign_vars(array(
		'S_FORM_TOKEN'	=> $s_fields,
	));
}

/**
* Check the form key. Required for all altering actions not secured by confirm_box
* @param string  $form_name The name of the form; has to match the name used in add_form_key, otherwise no restrictions apply
* @param int $timespan The maximum acceptable age for a submitted form in seconds. Defaults to the config setting.
* @param string $return_page The address for the return link
* @param bool $trigger If true, the function will triger an error when encountering an invalid form
*/
function check_form_key($form_name, $timespan = false, $return_page = '', $trigger = false)
{
	if ($timespan === false)
	{
		// we enforce a minimum value of half a minute here.
		$timespan = (phpbb::$config['form_token_lifetime'] == -1) ? -1 : max(30, phpbb::$config['form_token_lifetime']);
	}

	if (phpbb_request::is_set_post('creation_time') && phpbb_request::is_set_post('form_token'))
	{
		$creation_time	= abs(request_var('creation_time', 0));
		$token = request_var('form_token', '');

		$diff = time() - $creation_time;

		// If creation_time and the time() now is zero we can assume it was not a human doing this (the check for if ($diff)...
		if ($diff && ($diff <= $timespan || $timespan === -1))
		{
			$token_sid = (phpbb::$user->data['user_id'] == ANONYMOUS && !empty(phpbb::$config['form_token_sid_guests'])) ? phpbb::$user->session_id : '';
			$key = sha1($creation_time . phpbb::$user->data['user_form_salt'] . $form_name . $token_sid);

			if ($key === $token)
			{
				return true;
			}
		}
	}

	if ($trigger)
	{
		trigger_error(phpbb::$user->lang['FORM_INVALID'] . $return_page);
	}

	return false;
}

// Message/Login boxes

/**
* Build Confirm box
* @param boolean $check True for checking if confirmed (without any additional parameters) and false for displaying the confirm box
* @param string $title Title/Message used for confirm box.
*		message text is _CONFIRM appended to title.
*		If title cannot be found in user->lang a default one is displayed
*		If title_CONFIRM cannot be found in user->lang the text given is used.
* @param string $hidden Hidden variables
* @param string $html_body Template used for confirm box
* @param string $u_action Custom form action
*/
function confirm_box($check, $title = '', $hidden = '', $html_body = 'confirm_body.html', $u_action = '')
{
	global $template;

	if (phpbb_request::is_set_post('cancel'))
	{
		return false;
	}

	$confirm = false;
	if (phpbb_request::is_set_post('confirm'))
	{
		// language frontier
		if (request_var('confirm', '') === phpbb::$user->lang['YES'])
		{
			$confirm = true;
		}
	}

	if ($check && $confirm)
	{
		$user_id = request_var('user_id', 0);
		$session_id = request_var('sess', '');
		$confirm_key = request_var('confirm_key', '');

		if ($user_id != phpbb::$user->data['user_id'] || $session_id != phpbb::$user->session_id || !$confirm_key || !phpbb::$user->data['user_last_confirm_key'] || $confirm_key != phpbb::$user->data['user_last_confirm_key'])
		{
			return false;
		}

		// Reset user_last_confirm_key
		$sql = 'UPDATE ' . USERS_TABLE . " SET user_last_confirm_key = ''
			WHERE user_id = " . phpbb::$user->data['user_id'];
		phpbb::$db->sql_query($sql);

		return true;
	}
	else if ($check)
	{
		return false;
	}

	$s_hidden_fields = build_hidden_fields(array(
		'user_id'	=> phpbb::$user->data['user_id'],
		'sess'		=> phpbb::$user->session_id,
		'sid'		=> phpbb::$user->session_id,
	));

	// generate activation key
	$confirm_key = gen_rand_string(10);

	page_header((!isset(phpbb::$user->lang[$title])) ? phpbb::$user->lang['CONFIRM'] : phpbb::$user->lang[$title]);

	$template->set_filenames(array(
		'body' => $html_body)
	);

	// If activation key already exist, we better do not re-use the key (something very strange is going on...)
	if (request_var('confirm_key', ''))
	{
		// This should not occur, therefore we cancel the operation to safe the user
		return false;
	}

	// re-add sid / transform & to &amp; for user->page (user->page is always using &)
	$use_page = ($u_action) ? PHPBB_ROOT_PATH . $u_action : PHPBB_ROOT_PATH . str_replace('&', '&amp;', phpbb::$user->page['page']);
	$u_action = reapply_sid($use_page);
	$u_action .= ((strpos($u_action, '?') === false) ? '?' : '&amp;') . 'confirm_key=' . $confirm_key;

	$template->assign_vars(array(
		'MESSAGE_TITLE'		=> (!isset(phpbb::$user->lang[$title])) ? phpbb::$user->lang['CONFIRM'] : phpbb::$user->lang[$title],
		'MESSAGE_TEXT'		=> (!isset(phpbb::$user->lang[$title . '_CONFIRM'])) ? $title : phpbb::$user->lang[$title . '_CONFIRM'],

		'YES_VALUE'			=> phpbb::$user->lang['YES'],
		'S_CONFIRM_ACTION'	=> $u_action,
		'S_HIDDEN_FIELDS'	=> $hidden . $s_hidden_fields)
	);

	$sql = 'UPDATE ' . USERS_TABLE . " SET user_last_confirm_key = '" . phpbb::$db->sql_escape($confirm_key) . "'
		WHERE user_id = " . phpbb::$user->data['user_id'];
	phpbb::$db->sql_query($sql);

	page_footer();
}

/**
* Generate login box or verify password
*/
function login_box($redirect = '', $l_explain = '', $l_success = '', $admin = false, $s_display = true)
{
	$err = '';

	// Make sure user->setup() has been called
	if (empty(phpbb::$user->lang))
	{
		phpbb::$user->setup();
	}

	// Print out error if user tries to authenticate as an administrator without having the privileges...
	if ($admin && !phpbb::$acl->acl_get('a_'))
	{
		// Not authd
		// anonymous/inactive users are never able to go to the ACP even if they have the relevant permissions
		if (phpbb::$user->is_registered)
		{
			add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
		}

		$admin = false;
	}

	if (phpbb_request::is_set_post('login'))
	{
		// Get credential
		if ($admin)
		{
			$credential = request_var('credential', '');

			if (strspn($credential, 'abcdef0123456789') !== strlen($credential) || strlen($credential) != 32)
			{
				if (phpbb::$user->is_registered)
				{
					add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
				}

				trigger_error('NO_AUTH_ADMIN');
			}

			$password = request_var('password_' . $credential, '', true);
		}
		else
		{
			$password = request_var('password', '', true);
		}

		$username	= request_var('username', '', true);
		$autologin	= phpbb_request::variable('autologin', false, false, phpbb_request::POST);
		$viewonline = (phpbb_request::variable('viewonline', false, false, phpbb_request::POST)) ? 0 : 1;
		$admin 		= ($admin) ? 1 : 0;
		$viewonline = ($admin) ? phpbb::$user->data['session_viewonline'] : $viewonline;

		// Check if the supplied username is equal to the one stored within the database if re-authenticating
		if ($admin && utf8_clean_string($username) != utf8_clean_string(phpbb::$user->data['username']))
		{
			// We log the attempt to use a different username...
			add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
			trigger_error('NO_AUTH_ADMIN_USER_DIFFER');
		}

		// If authentication is successful we redirect user to previous page
		$result = phpbb::$user->login($username, $password, $autologin, $viewonline, $admin);

		// If admin authentication and login, we will log if it was a success or not...
		// We also break the operation on the first non-success login - it could be argued that the user already knows
		if ($admin)
		{
			if ($result['status'] == LOGIN_SUCCESS)
			{
				add_log('admin', 'LOG_ADMIN_AUTH_SUCCESS');
			}
			else
			{
				// Only log the failed attempt if a real user tried to.
				// anonymous/inactive users are never able to go to the ACP even if they have the relevant permissions
				if (phpbb::$user->is_registered)
				{
					add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
				}
			}
		}

		// The result parameter is always an array, holding the relevant information...
		if ($result['status'] == LOGIN_SUCCESS)
		{
			$redirect = request_var('redirect', phpbb::$user->page['page']);

			$message = ($l_success) ? $l_success : phpbb::$user->lang['LOGIN_REDIRECT'];
			$l_redirect = ($admin) ? phpbb::$user->lang['PROCEED_TO_ACP'] : (($redirect === PHPBB_ROOT_PATH . 'index.' . PHP_EXT || $redirect === 'index.' . PHP_EXT) ? phpbb::$user->lang['RETURN_INDEX'] : phpbb::$user->lang['RETURN_PAGE']);

			// append/replace SID (may change during the session for AOL users)
			$redirect = phpbb::$url->reapply_sid($redirect);

			// Special case... the user is effectively banned, but we allow founders to login
			if (defined('IN_CHECK_BAN') && $result['user_row']['user_type'] != phpbb::USER_FOUNDER)
			{
				return;
			}

			// $redirect = phpbb::$url->meta_refresh(3, $redirect);
			trigger_error($message . '<br /><br />' . sprintf($l_redirect, '<a href="' . $redirect . '">', '</a>'));
		}

		// Something failed, determine what...
		if ($result['status'] == LOGIN_BREAK)
		{
			trigger_error($result['error_msg']);
		}

		// Special cases... determine
		switch ($result['status'])
		{
			case LOGIN_ERROR_ATTEMPTS:

				$captcha = phpbb_captcha_factory::get_instance(phpbb::$config['captcha_plugin']);
				$captcha->init(CONFIRM_LOGIN);
				$captcha->reset();

				$template->assign_vars(array(
					'S_CONFIRM_CODE'			=> true,
					'CONFIRM'					=> $captcha->get_template(''),
				));

				$err = phpbb::$user->lang[$result['error_msg']];

			break;

			case LOGIN_ERROR_PASSWORD_CONVERT:
				$err = sprintf(
					phpbb::$user->lang[$result['error_msg']],
					(phpbb::$config['email_enable']) ? '<a href="' . append_sid('ucp', 'mode=sendpassword') . '">' : '',
					(phpbb::$config['email_enable']) ? '</a>' : '',
					(phpbb::$config['board_contact']) ? '<a href="mailto:' . utf8_htmlspecialchars(phpbb::$config['board_contact']) . '">' : '',
					(phpbb::$config['board_contact']) ? '</a>' : ''
				);
			break;

			// Username, password, etc...
			default:
				$err = phpbb::$user->lang[$result['error_msg']];

				// Assign admin contact to some error messages
				if ($result['error_msg'] == 'LOGIN_ERROR_USERNAME' || $result['error_msg'] == 'LOGIN_ERROR_PASSWORD')
				{
					$err = (!phpbb::$config['board_contact']) ? sprintf(phpbb::$user->lang[$result['error_msg']], '', '') : sprintf(phpbb::$user->lang[$result['error_msg']], '<a href="mailto:' . utf8_htmlspecialchars(phpbb::$config['board_contact']) . '">', '</a>');
				}

			break;
		}
	}

	if (!$redirect)
	{
		// We just use what the session code determined...
		// If we are not within the admin directory we use the page dir...
		$redirect = '';

		if (!$admin && !defined('ADMIN_START'))
		{
			$redirect .= (phpbb::$user->page['page_dir']) ? phpbb::$user->page['page_dir'] . '/' : '';
		}

		$redirect .= phpbb::$user->page['page_name'] . ((phpbb::$user->page['query_string']) ? '?' . utf8_htmlspecialchars(phpbb::$user->page['query_string']) : '');
	}

	// Assign credential for username/password pair
	$credential = ($admin) ? md5(phpbb::$security->unique_id()) : false;

	$s_hidden_fields = array(
		'redirect'	=> $redirect,
		'sid'		=> phpbb::$user->session_id,
	);

	if ($admin)
	{
		$s_hidden_fields['credential'] = $credential;
	}

	$s_hidden_fields = build_hidden_fields($s_hidden_fields);

	phpbb::$template->assign_vars(array(
		'LOGIN_ERROR'		=> $err,
		'LOGIN_EXPLAIN'		=> $l_explain,

		'U_SEND_PASSWORD' 		=> (phpbb::$config['email_enable']) ? phpbb::$url->append_sid('ucp', 'mode=sendpassword') : '',
		'U_RESEND_ACTIVATION'	=> (phpbb::$config['require_activation'] != USER_ACTIVATION_NONE && phpbb::$config['email_enable']) ? phpbb::$url->append_sid('ucp', 'mode=resend_act') : '',
		'U_TERMS_USE'			=> phpbb::$url->append_sid('ucp', 'mode=terms'),
		'U_PRIVACY'				=> phpbb::$url->append_sid('ucp', 'mode=privacy'),

		'S_DISPLAY_FULL_LOGIN'	=> ($s_display) ? true : false,
		'S_LOGIN_ACTION'		=> (!$admin && !defined('ADMIN_START'))  ? phpbb::$url->append_sid('ucp', 'mode=login') : phpbb::$url->append_sid(PHPBB_ADMIN_PATH . 'index.' . PHP_EXT, false, true, phpbb::$user->session_id),
		'S_HIDDEN_FIELDS' 		=> $s_hidden_fields,

		'S_ADMIN_AUTH'			=> $admin,
		'S_ACP_LOGIN'			=> defined('ADMIN_START'),
		'USERNAME'				=> ($admin) ? phpbb::$user->data['username'] : '',

		'USERNAME_CREDENTIAL'	=> 'username',
		'PASSWORD_CREDENTIAL'	=> ($admin) ? 'password_' . $credential : 'password',
	));

	phpbb::$template->set_filenames(array(
		'body' => 'login_body.html')
	);

	page_header(phpbb::$user->lang['LOGIN'], false);
	make_jumpbox('viewforum');

	page_footer();
}

/**
* Generate forum login box
*/
function login_forum_box($forum_data)
{
	global $template;

	$password = request_var('password', '', true);

	$sql = 'SELECT forum_id
		FROM ' . FORUMS_ACCESS_TABLE . '
		WHERE forum_id = ' . $forum_data['forum_id'] . '
			AND user_id = ' . phpbb::$user->data['user_id'] . "
			AND session_id = '" . phpbb::$db->sql_escape(phpbb::$user->session_id) . "'";
	$result = phpbb::$db->sql_query($sql);
	$row = phpbb::$db->sql_fetchrow($result);
	phpbb::$db->sql_freeresult($result);

	if ($row)
	{
		return true;
	}

	if ($password)
	{
		// Remove expired authorised sessions
		$sql = 'SELECT f.session_id
			FROM ' . FORUMS_ACCESS_TABLE . ' f
			LEFT JOIN ' . SESSIONS_TABLE . ' s ON (f.session_id = s.session_id)
			WHERE s.session_id IS NULL';
		$result = phpbb::$db->sql_query($sql);

		if ($row = phpbb::$db->sql_fetchrow($result))
		{
			$sql_in = array();
			do
			{
				$sql_in[] = (string) $row['session_id'];
			}
			while ($row = phpbb::$db->sql_fetchrow($result));

			// Remove expired sessions
			$sql = 'DELETE FROM ' . FORUMS_ACCESS_TABLE . '
				WHERE ' . phpbb::$db->sql_in_set('session_id', $sql_in);
			phpbb::$db->sql_query($sql);
		}
		phpbb::$db->sql_freeresult($result);

		if (phpbb_check_hash($password, $forum_data['forum_password']))
		{
			$sql_ary = array(
				'forum_id'		=> (int) $forum_data['forum_id'],
				'user_id'		=> (int) phpbb::$user->data['user_id'],
				'session_id'	=> (string) phpbb::$user->session_id,
			);

			phpbb::$db->sql_query('INSERT INTO ' . FORUMS_ACCESS_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_ary));

			return true;
		}

		$template->assign_var('LOGIN_ERROR', phpbb::$user->lang['WRONG_PASSWORD']);
	}

	page_header(phpbb::$user->lang['LOGIN']);

	$template->assign_vars(array(
		'S_HIDDEN_FIELDS'		=> build_hidden_fields(array('f' => $forum_data['forum_id'])))
	);

	$template->set_filenames(array(
		'body' => 'login_forum.html')
	);

	page_footer();
}

// Little helpers

/**
* Little helper for the build_hidden_fields function
*/
function _build_hidden_fields($key, $value, $specialchar, $stripslashes)
{
	$hidden_fields = '';

	if (!is_array($value))
	{
		$value = ($stripslashes) ? stripslashes($value) : $value;
		$value = ($specialchar) ? utf8_htmlspecialchars($value) : $value;

		$hidden_fields .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
	}
	else
	{
		foreach ($value as $_key => $_value)
		{
			$_key = ($stripslashes) ? stripslashes($_key) : $_key;
			$_key = ($specialchar) ? utf8_htmlspecialchars($_key) : $_key;

			$hidden_fields .= _build_hidden_fields($key . '[' . $_key . ']', $_value, $specialchar, $stripslashes);
		}
	}

	return $hidden_fields;
}

/**
* Build simple hidden fields from array
*
* @param array $field_ary an array of values to build the hidden field from
* @param bool $specialchar if true, keys and values get specialchared
* @param bool $stripslashes if true, keys and values get stripslashed
*
* @return string the hidden fields
*/
function build_hidden_fields($field_ary, $specialchar = false, $stripslashes = false)
{
	$s_hidden_fields = '';

	foreach ($field_ary as $name => $vars)
	{
		$name = ($stripslashes) ? stripslashes($name) : $name;
		$name = ($specialchar) ? utf8_htmlspecialchars($name) : $name;

		$s_hidden_fields .= _build_hidden_fields($name, $vars, $specialchar, $stripslashes);
	}

	return $s_hidden_fields;
}

/**
* Parse cfg file
*/
function parse_cfg_file($filename, $lines = false)
{
	$parsed_items = array();

	if ($lines === false)
	{
		$lines = file($filename);
	}

	foreach ($lines as $line)
	{
		$line = trim($line);

		if (!$line || $line[0] == '#' || ($delim_pos = strpos($line, '=')) === false)
		{
			continue;
		}

		// Determine first occurrence, since in values the equal sign is allowed
		$key = strtolower(trim(substr($line, 0, $delim_pos)));
		$value = trim(substr($line, $delim_pos + 1));

		if (in_array($value, array('off', 'false', '0')))
		{
			$value = false;
		}
		else if (in_array($value, array('on', 'true', '1')))
		{
			$value = true;
		}
		else if (!trim($value))
		{
			$value = '';
		}
		else if (($value[0] == "'" && $value[sizeof($value) - 1] == "'") || ($value[0] == '"' && $value[sizeof($value) - 1] == '"'))
		{
			$value = substr($value, 1, sizeof($value)-2);
		}

		$parsed_items[$key] = $value;
	}

	return $parsed_items;
}

/**
* Add log event
*/
function add_log()
{
	$args = func_get_args();

	$mode			= array_shift($args);
	$reportee_id	= ($mode == 'user') ? intval(array_shift($args)) : '';
	$forum_id		= ($mode == 'mod') ? intval(array_shift($args)) : '';
	$topic_id		= ($mode == 'mod') ? intval(array_shift($args)) : '';
	$action			= array_shift($args);
	$data			= (!sizeof($args)) ? '' : serialize($args);

	$sql_ary = array(
		'user_id'		=> (empty(phpbb::$user->data)) ? ANONYMOUS : phpbb::$user->data['user_id'],
		'log_ip'		=> phpbb::$user->ip,
		'log_time'		=> time(),
		'log_operation'	=> $action,
		'log_data'		=> $data,
	);

	switch ($mode)
	{
		case 'admin':
			$sql_ary['log_type'] = LOG_ADMIN;
		break;

		case 'mod':
			$sql_ary += array(
				'log_type'	=> LOG_MOD,
				'forum_id'	=> $forum_id,
				'topic_id'	=> $topic_id
			);
		break;

		case 'user':
			$sql_ary += array(
				'log_type'		=> LOG_USERS,
				'reportee_id'	=> $reportee_id
			);
		break;

		case 'critical':
			$sql_ary['log_type'] = LOG_CRITICAL;
		break;

		default:
			return false;
	}

	phpbb::$db->sql_query('INSERT INTO ' . LOG_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_ary));

	return phpbb::$db->sql_nextid();
}

/**
* Return a nicely formatted backtrace (parts from the php manual by diz at ysagoon dot com)
*/
function get_backtrace()
{
	$output = '<div style="font-family: monospace;">';
	$backtrace = debug_backtrace();
	$path = phpbb::$url->realpath(PHPBB_ROOT_PATH);

	foreach ($backtrace as $number => $trace)
	{
		// We skip the first one, because it only shows this file/function
		if ($number == 0)
		{
			continue;
		}

		if (empty($trace['file']) && empty($trace['line']))
		{
			continue;
		}

		// Strip the current directory from path
		if (empty($trace['file']))
		{
			$trace['file'] = '';
		}
		else
		{
			$trace['file'] = str_replace(array($path, '\\'), array('', '/'), $trace['file']);
			$trace['file'] = substr($trace['file'], 1);
		}
		$args = array();

		// If include/require/include_once is not called, do not show arguments - they may contain sensible information
		if (!in_array($trace['function'], array('include', 'require', 'include_once')))
		{
			unset($trace['args']);
		}
		else
		{
			// Path...
			if (!empty($trace['args'][0]))
			{
				$argument = htmlspecialchars($trace['args'][0]);
				$argument = str_replace(array($path, '\\'), array('', '/'), $argument);
				$argument = substr($argument, 1);
				$args[] = "'{$argument}'";
			}
		}

		$trace['class'] = (!isset($trace['class'])) ? '' : $trace['class'];
		$trace['type'] = (!isset($trace['type'])) ? '' : $trace['type'];

		$output .= '<br />';
		$output .= '<b>FILE:</b> ' . htmlspecialchars($trace['file']) . '<br />';
		$output .= '<b>LINE:</b> ' . ((!empty($trace['line'])) ? $trace['line'] : '') . '<br />';

		$output .= '<b>CALL:</b> ' . htmlspecialchars($trace['class'] . $trace['type'] . $trace['function']) . '(' . ((sizeof($args)) ? implode(', ', $args) : '') . ')<br />';
	}
	$output .= '</div>';
	return $output;
}

/**
* This function returns a regular expression pattern for commonly used expressions
* Use with / as delimiter for email mode and # for url modes
* mode can be: email|bbcode_htm|url|url_inline|www_url|www_url_inline|relative_url|relative_url_inline|ipv4|ipv6
*/
function get_preg_expression($mode)
{
	switch ($mode)
	{
		case 'email':
			return '(?:[a-z0-9\'\.\-_\+\|]++|&amp;)+@[a-z0-9\-]+\.(?:[a-z0-9\-]+\.)*[a-z]+';
		break;

		case 'bbcode_htm':
			return array(
				'#<!\-\- e \-\-><a href="mailto:(.*?)">.*?</a><!\-\- e \-\->#',
				'#<!\-\- l \-\-><a (?:class="[\w-]+" )?href="(.*?)(?:(&amp;|\?)sid=[0-9a-f]{32})?">.*?</a><!\-\- l \-\->#',
				'#<!\-\- ([mw]) \-\-><a (?:class="[\w-]+" )?href="(.*?)">.*?</a><!\-\- \1 \-\->#',
				'#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/.*? \/><!\-\- s\1 \-\->#',
				'#<!\-\- .*? \-\->#s',
				'#<.*?>#s',
			);
		break;

		// Whoa these look impressive!
		// The code to generate the following two regular expressions which match valid IPv4/IPv6 addresses
		// can be found in the develop directory
		case 'ipv4':
			return '#^(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$#';
		break;

		case 'ipv6':
			return '#^(?:(?:(?:[\dA-F]{1,4}:){6}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:::(?:[\dA-F]{1,4}:){5}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:):(?:[\dA-F]{1,4}:){4}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,2}:(?:[\dA-F]{1,4}:){3}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,3}:(?:[\dA-F]{1,4}:){2}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,4}:(?:[\dA-F]{1,4}:)(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,5}:(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,6}:[\dA-F]{1,4})|(?:(?:[\dA-F]{1,4}:){1,7}:))$#i';
		break;

		case 'url':
		case 'url_inline':
			$inline = ($mode == 'url') ? ')' : '';
			$scheme = ($mode == 'url') ? '[a-z\d+\-.]' : '[a-z\d+]'; // avoid automatic parsing of "word" in "last word.http://..."
			// generated with regex generation file in the develop folder
			return "[a-z]$scheme*:/{2}(?:(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})+|[0-9.]+|\[[a-z0-9.]+:[a-z0-9.]+:[a-z0-9.:]+\])(?::\d*)?(?:/(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?";
		break;

		case 'www_url':
		case 'www_url_inline':
			$inline = ($mode == 'www_url') ? ')' : '';
			return "www\.(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})+(?::\d*)?(?:/(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?";
		break;

		case 'relative_url':
		case 'relative_url_inline':
			$inline = ($mode == 'relative_url') ? ')' : '';
			return "(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})*(?:/(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?";
		break;
	}

	return '';
}

/**
* Returns the first block of the specified IPv6 address and as many additional
* ones as specified in the length paramater.
* If length is zero, then an empty string is returned.
* If length is greater than 3 the complete IP will be returned
*/
function short_ipv6($ip, $length)
{
	if ($length < 1)
	{
		return '';
	}

	// extend IPv6 addresses
	$blocks = substr_count($ip, ':') + 1;
	if ($blocks < 9)
	{
		$ip = str_replace('::', ':' . str_repeat('0000:', 9 - $blocks), $ip);
	}
	if ($ip[0] == ':')
	{
		$ip = '0000' . $ip;
	}
	if ($length < 4)
	{
		$ip = implode(':', array_slice(explode(':', $ip), 0, 1 + $length));
	}

	return $ip;
}

/**
* Wrapper for php's checkdnsrr function.
*
* The windows failover is from the php manual
* Please make sure to check the return value for === true and === false, since NULL could
* be returned too.
*
* @return true if entry found, false if not, NULL if this function is not supported by this environment
*/
function phpbb_checkdnsrr($host, $type = '')
{
	$type = (!$type) ? 'MX' : $type;

	if (DIRECTORY_SEPARATOR == '\\')
	{
		if (!function_exists('exec'))
		{
			return NULL;
		}

		// @exec('nslookup -retry=1 -timout=1 -type=' . escapeshellarg($type) . ' ' . escapeshellarg($host), $output);
		@exec('nslookup -type=' . escapeshellarg($type) . ' ' . escapeshellarg($host) . '.', $output);

		// If output is empty, the nslookup failed
		if (empty($output))
		{
			return NULL;
		}

		foreach ($output as $line)
		{
			if (!trim($line))
			{
				continue;
			}

			// Valid records begin with host name:
			if (strpos($line, $host) === 0)
			{
				return true;
			}
		}

		return false;
	}
	else if (function_exists('checkdnsrr'))
	{
		// The dot indicates to search the DNS root (helps those having DNS prefixes on the same domain)
		return (checkdnsrr($host . '.', $type)) ? true : false;
	}

	return NULL;
}

// Handler, header and footer

/**
* Error and message handler, call with trigger_error if reqd
*/
function msg_handler($errno, $msg_text, $errfile, $errline)
{
	global $msg_title, $msg_long_text;

	// Do not display notices if we suppress them via @
	if (error_reporting() == 0)
	{
		return;
	}

	// Message handler is stripping text. In case we need it, we are able to define long text...
	if (isset($msg_long_text) && $msg_long_text && !$msg_text)
	{
		$msg_text = $msg_long_text;
	}

	switch ($errno)
	{
		case E_NOTICE:
		case E_WARNING:
		case E_STRICT:

			// Check the error reporting level and return if the error level does not match
			// If DEBUG is defined the default level is E_ALL
			if (($errno & ((phpbb::$base_config['debug']) ? E_ALL | E_STRICT : error_reporting())) == 0)
			{
				return;
			}

//			if (strpos($errfile, 'cache') === false && strpos($errfile, 'template.') === false)
//			{
				// flush the content, else we get a white page if output buffering is on
				if ((int) @ini_get('output_buffering') === 1 || strtolower(@ini_get('output_buffering')) === 'on')
				{
					@ob_flush();
				}

				// Another quick fix for those having gzip compression enabled, but do not flush if the coder wants to catch "something". ;)
				if (!empty(phpbb::$config['gzip_compress']))
				{
					if (@extension_loaded('zlib') && !headers_sent() && !ob_get_level())
					{
						@ob_flush();
					}
				}

				// remove complete path to installation, with the risk of changing backslashes meant to be there
				if (phpbb::registered('url'))
				{
					$errfile = str_replace(array(phpbb::$url->realpath(PHPBB_ROOT_PATH), '\\'), array('', '/'), $errfile);
					$msg_text = str_replace(array(phpbb::$url->realpath(PHPBB_ROOT_PATH), '\\'), array('', '/'), $msg_text);
				}

				echo '<b>[phpBB Debug] PHP Notice</b>: in file <b>' . $errfile . '</b> on line <b>' . $errline . '</b>: <b>' . $msg_text . '</b><br />' . "\n";
//			}

			return;

		break;

		case E_RECOVERABLE_ERROR:
		case E_USER_ERROR:

			if (phpbb::registered('user'))
			{
				$msg_text = (!empty(phpbb::$user->lang[$msg_text])) ? phpbb::$user->lang[$msg_text] : $msg_text;
				$msg_title = (!isset($msg_title)) ? phpbb::$user->lang['GENERAL_ERROR'] : ((!empty(phpbb::$user->lang[$msg_title])) ? phpbb::$user->lang[$msg_title] : $msg_title);

				$l_return_index = phpbb::$user->lang('RETURN_INDEX', '<a href="' . PHPBB_ROOT_PATH . '">', '</a>');
				$l_notify = '';

				if (!empty(phpbb::$config['board_contact']))
				{
					$l_notify = '<p>' . phpbb::$user->lang('NOTIFY_ADMIN_EMAIL', phpbb::$config['board_contact']) . '</p>';
				}
			}
			else
			{
				$msg_title = 'General Error';
				$l_return_index = '<a href="' . PHPBB_ROOT_PATH . '">Return to index page</a>';
				$l_notify = '';

				if (!empty(phpbb::$config['board_contact']))
				{
					$l_notify = '<p>Please notify the board administrator or webmaster: <a href="mailto:' . phpbb::$config['board_contact'] . '">' . phpbb::$config['board_contact'] . '</a></p>';
				}
			}

			garbage_collection();

			// Try to not call the adm page data...
			// @todo put into failover template file

			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
			echo '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">';
			echo '<head>';
			echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
			echo '<title>' . $msg_title . '</title>';
			echo '<style type="text/css">' . "\n" . '/* <![CDATA[ */' . "\n";
			echo '* { margin: 0; padding: 0; } html { font-size: 100%; height: 100%; margin-bottom: 1px; background-color: #E4EDF0; } body { font-family: "Lucida Grande", Verdana, Helvetica, Arial, sans-serif; color: #536482; background: #E4EDF0; font-size: 62.5%; margin: 0; } ';
			echo 'a:link, a:active, a:visited { color: #006699; text-decoration: none; } a:hover { color: #DD6900; text-decoration: underline; } ';
			echo '#wrap { padding: 0 20px 15px 20px; min-width: 615px; } #page-header { text-align: right; height: 40px; } #page-footer { clear: both; font-size: 1em; text-align: center; } ';
			echo '.panel { margin: 4px 0; background-color: #FFFFFF; border: solid 1px  #A9B8C2; } ';
			echo '#errorpage #page-header a { font-weight: bold; line-height: 6em; } #errorpage #content { padding: 10px; } #errorpage #content h1 { line-height: 1.2em; margin-bottom: 0; color: #DF075C; } ';
			echo '#errorpage #content div { margin-top: 20px; margin-bottom: 5px; border-bottom: 1px solid #CCCCCC; padding-bottom: 5px; color: #333333; font: bold 1.2em "Lucida Grande", Arial, Helvetica, sans-serif; text-decoration: none; line-height: 120%; text-align: left; } ';
			echo "\n" . '/* ]]> */' . "\n";
			echo '</style>';
			echo '</head>';
			echo '<body id="errorpage">';
			echo '<div id="wrap">';
			echo '	<div id="page-header">';
			echo '		' . $l_return_index;
			echo '	</div>';
			echo '	<div id="acp">';
			echo '	<div class="panel">';
			echo '		<div id="content">';
			echo '			<h1>' . $msg_title . '</h1>';

			echo '			<div>' . $msg_text;

			if ((phpbb::registered('acl') && phpbb::$acl->acl_get('a_')) || defined('IN_INSTALL') || phpbb::$base_config['debug_extra'])
			{
				echo ($backtrace = get_backtrace()) ? '<br /><br />BACKTRACE' . $backtrace : '';
			}
			echo '</div>';

			echo $l_notify;

			echo '		</div>';
			echo '	</div>';
			echo '	</div>';
			echo '	<div id="page-footer">';
			echo '		Powered by phpBB &copy; 2000, 2002, 2005, 2007 <a href="http://www.phpbb.com/">phpBB Group</a>';
			echo '	</div>';
			echo '</div>';
			echo '</body>';
			echo '</html>';

			exit_handler();

			// On a fatal error (and E_USER_ERROR *is* fatal) we never want other scripts to continue and force an exit here.
			exit;
		break;

		case E_USER_WARNING:
		case E_USER_NOTICE:

			define('IN_ERROR_HANDLER', true);

			if (empty(phpbb::$user->data))
			{
				phpbb::$user->session_begin();
			}

			// We re-init the auth array to get correct results on login/logout
			phpbb::$acl->init(phpbb::$user->data);

			if (empty(phpbb::$user->lang))
			{
				phpbb::$user->setup();
			}

			$msg_text = (!empty(phpbb::$user->lang[$msg_text])) ? phpbb::$user->lang[$msg_text] : $msg_text;
			$msg_title = (!isset($msg_title)) ? phpbb::$user->lang['INFORMATION'] : ((!empty(phpbb::$user->lang[$msg_title])) ? phpbb::$user->lang[$msg_title] : $msg_title);

			if (!defined('HEADER_INC'))
			{
				page_header($msg_title);
			}

			phpbb::$template->set_filenames(array(
				'body' => 'message_body.html')
			);

			phpbb::$template->assign_vars(array(
				'MESSAGE_TITLE'		=> $msg_title,
				'MESSAGE_TEXT'		=> $msg_text,
				'S_USER_WARNING'	=> ($errno == E_USER_WARNING) ? true : false,
				'S_USER_NOTICE'		=> ($errno == E_USER_NOTICE) ? true : false)
			);

			// We do not want the cron script to be called on error messages
			define('IN_CRON', true);

			page_footer();

			exit_handler();
		break;
	}

	// If we notice an error not handled here we pass this back to PHP by returning false
	// This may not work for all php versions
	return false;
}

/**
* Generate page header
* @plugin-support override, default, return
*/
function page_header($page_title = '', $display_online_list = true)
{
	if (phpbb::$plugins->function_override(__FUNCTION__)) return phpbb::$plugins->call_override(__FUNCTION__, $page_title, $display_online_list);

	if (defined('HEADER_INC'))
	{
		return;
	}

	define('HEADER_INC', true);

	// gzip_compression
	if (phpbb::$config['gzip_compress'])
	{
		if (@extension_loaded('zlib') && !headers_sent())
		{
			ob_start('ob_gzhandler');
		}
	}

	if (phpbb::$plugins->function_inject(__FUNCTION__)) phpbb::$plugins->call_inject(__FUNCTION__, array('default', &$page_title, &$display_online_list));

	// Generate logged in/logged out status
	if (phpbb::$user->data['user_id'] != ANONYMOUS)
	{
		$u_login_logout = phpbb::$url->append_sid('ucp', 'mode=logout', true, phpbb::$user->session_id);
		$l_login_logout = sprintf(phpbb::$user->lang['LOGOUT_USER'], phpbb::$user->data['username']);
	}
	else
	{
		$u_login_logout = phpbb::$url->append_sid('ucp', 'mode=login');
		$l_login_logout = phpbb::$user->lang['LOGIN'];
	}

	// Last visit date/time
	$s_last_visit = (phpbb::$user->data['user_id'] != ANONYMOUS) ? phpbb::$user->format_date(phpbb::$user->data['session_last_visit']) : '';

	// Get users online list ... if required
	$online_userlist = array();
	$l_online_users = $l_online_record = '';
	$forum = request_var('f', 0);

	if (phpbb::$config['load_online'] && phpbb::$config['load_online_time'] && $display_online_list)
	{
		$logged_visible_online = $logged_hidden_online = $guests_online = $prev_user_id = 0;
		$prev_session_ip = $reading_sql = '';

		if ($forum)
		{
			$reading_sql = ' AND s.session_forum_id = ' . $forum;
		}

		// Get number of online guests
		if (!phpbb::$config['load_online_guests'])
		{
			if (phpbb::$db->features['count_distinct'])
			{
				$sql = 'SELECT COUNT(DISTINCT s.session_ip) as num_guests
					FROM ' . SESSIONS_TABLE . ' s
					WHERE s.session_user_id = ' . ANONYMOUS . '
						AND s.session_time >= ' . (time() - (phpbb::$config['load_online_time'] * 60)) .
					$reading_sql;
			}
			else
			{
				$sql = 'SELECT COUNT(session_ip) as num_guests
					FROM (
						SELECT DISTINCT s.session_ip
							FROM ' . SESSIONS_TABLE . ' s
							WHERE s.session_user_id = ' . ANONYMOUS . '
								AND s.session_time >= ' . (time() - (phpbb::$config['load_online_time'] * 60)) .
								$reading_sql .
					')';
			}
			$result = phpbb::$db->sql_query($sql);
			$guests_online = (int) phpbb::$db->sql_fetchfield('num_guests');
			phpbb::$db->sql_freeresult($result);
		}

		$sql = 'SELECT u.username, u.username_clean, u.user_id, u.user_type, u.user_allow_viewonline, u.user_colour, s.session_ip, s.session_viewonline
			FROM ' . USERS_TABLE . ' u, ' . SESSIONS_TABLE . ' s
			WHERE s.session_time >= ' . (time() - (intval(phpbb::$config['load_online_time']) * 60)) .
				$reading_sql .
				((!phpbb::$config['load_online_guests']) ? ' AND s.session_user_id <> ' . ANONYMOUS : '') . '
				AND u.user_id = s.session_user_id
			ORDER BY u.username_clean ASC, s.session_ip ASC';
		$result = phpbb::$db->sql_query($sql);

		$prev_user_id = false;

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			// User is logged in and therefore not a guest
			if ($row['user_id'] != ANONYMOUS)
			{
				// Skip multiple sessions for one user
				if ($row['user_id'] != $prev_user_id)
				{
					if ($row['session_viewonline'])
					{
						$logged_visible_online++;
					}
					else
					{
						$row['username'] = '<em>' . $row['username'] . '</em>';
						$logged_hidden_online++;
					}

					if (($row['session_viewonline']) || phpbb::$acl->acl_get('u_viewonline'))
					{
						$user_online_link = get_username_string(($row['user_type'] <> phpbb::USER_IGNORE) ? 'full' : 'no_profile', $row['user_id'], $row['username'], $row['user_colour']);
						$online_userlist[] = $user_online_link;
					}
				}

				$prev_user_id = $row['user_id'];
			}
			else
			{
				// Skip multiple sessions for one user
				if ($row['session_ip'] != $prev_session_ip)
				{
					$guests_online++;
				}
			}

			$prev_session_ip = $row['session_ip'];
		}
		phpbb::$db->sql_freeresult($result);

		if (!sizeof($online_userlist))
		{
			$online_userlist = phpbb::$user->lang['NO_ONLINE_USERS'];
		}
		else
		{
			$online_userlist = implode(', ', $online_userlist);
		}

		if (!$forum)
		{
			$online_userlist = phpbb::$user->lang['REGISTERED_USERS'] . ' ' . $online_userlist;
		}
		else
		{
			$online_userlist = phpbb::$user->lang('BROWSING_FORUM_GUESTS', $online_userlist, $guests_online);
		}

		$total_online_users = $logged_visible_online + $logged_hidden_online + $guests_online;

		if ($total_online_users > phpbb::$config['record_online_users'])
		{
			set_config('record_online_users', $total_online_users, true);
			set_config('record_online_date', time(), true);
		}

		$l_online_users = phpbb::$user->lang('ONLINE_USER_COUNT', $total_online_users);
		$l_online_users .= phpbb::$user->lang('REG_USER_COUNT', $logged_visible_online);
		$l_online_users .= phpbb::$user->lang('HIDDEN_USER_COUNT', $logged_hidden_online);
		$l_online_users .= phpbb::$user->lang('GUEST_USER_COUNT', $guests_online);

		$l_online_record = phpbb::$user->lang('RECORD_ONLINE_USERS', phpbb::$config['record_online_users'], phpbb::$user->format_date(phpbb::$config['record_online_date']));
		$l_online_time = phpbb::$user->lang('VIEW_ONLINE_TIME', phpbb::$config['load_online_time']);
	}
	else
	{
		$l_online_time = '';
	}

	$l_privmsgs_text = $l_privmsgs_text_unread = '';
	$s_privmsg_new = false;

	// Obtain number of new private messages if user is logged in
	if (!empty(phpbb::$user->data['is_registered']))
	{
		if (phpbb::$user->data['user_new_privmsg'])
		{
			$l_privmsgs_text = phpbb::$user->lang('NEW_PM', phpbb::$user->data['user_new_privmsg']);

			if (!phpbb::$user->data['user_last_privmsg'] || phpbb::$user->data['user_last_privmsg'] > phpbb::$user->data['session_last_visit'])
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_last_privmsg = ' . phpbb::$user->data['session_last_visit'] . '
					WHERE user_id = ' . phpbb::$user->data['user_id'];
				phpbb::$db->sql_query($sql);

				$s_privmsg_new = true;
			}
			else
			{
				$s_privmsg_new = false;
			}
		}
		else
		{
			$l_privmsgs_text = phpbb::$user->lang['NO_NEW_PM'];
			$s_privmsg_new = false;
		}

		$l_privmsgs_text_unread = '';

		if (phpbb::$user->data['user_unread_privmsg'] && phpbb::$user->data['user_unread_privmsg'] != phpbb::$user->data['user_new_privmsg'])
		{
			$l_privmsgs_text_unread = phpbb::$user->lang('UNREAD_PM', phpbb::$user->data['user_unread_privmsg']);
		}
	}

	// Which timezone?
	$tz = (phpbb::$user->data['user_id'] != ANONYMOUS) ? strval(doubleval(phpbb::$user->data['user_timezone'])) : strval(doubleval(phpbb::$config['board_timezone']));

	// Send a proper content-language to the output
	$user_lang = phpbb::$user->lang['USER_LANG'];
	if (strpos($user_lang, '-x-') !== false)
	{
		$user_lang = substr($user_lang, 0, strpos($user_lang, '-x-'));
	}

	// The following assigns all _common_ variables that may be used at any point in a template.
	phpbb::$template->assign_vars(array(
		'SITENAME'						=> phpbb::$config['sitename'],
		'SITE_DESCRIPTION'				=> phpbb::$config['site_desc'],
		'PAGE_TITLE'					=> $page_title,
		'SCRIPT_NAME'					=> str_replace('.' . PHP_EXT, '', phpbb::$user->page['page_name']),
		'LAST_VISIT_DATE'				=> phpbb::$user->lang('YOU_LAST_VISIT', $s_last_visit),
		'LAST_VISIT_YOU'				=> $s_last_visit,
		'CURRENT_TIME'					=> phpbb::$user->lang('CURRENT_TIME', phpbb::$user->format_date(time(), false, true)),
		'TOTAL_USERS_ONLINE'			=> $l_online_users,
		'LOGGED_IN_USER_LIST'			=> $online_userlist,
		'RECORD_USERS'					=> $l_online_record,
		'PRIVATE_MESSAGE_INFO'			=> $l_privmsgs_text,
		'PRIVATE_MESSAGE_INFO_UNREAD'	=> $l_privmsgs_text_unread,

		'S_USER_NEW_PRIVMSG'			=> phpbb::$user->data['user_new_privmsg'],
		'S_USER_UNREAD_PRIVMSG'			=> phpbb::$user->data['user_unread_privmsg'],

		'SESSION_ID'		=> phpbb::$user->session_id,
		'ROOT_PATH'			=> PHPBB_ROOT_PATH,

		'L_LOGIN_LOGOUT'	=> $l_login_logout,
		'L_INDEX'			=> phpbb::$user->lang['FORUM_INDEX'],
		'L_ONLINE_EXPLAIN'	=> $l_online_time,

		'U_PRIVATEMSGS'			=> phpbb::$url->append_sid('ucp', 'i=pm&amp;folder=inbox'),
		'U_RETURN_INBOX'		=> phpbb::$url->append_sid('ucp', 'i=pm&amp;folder=inbox'),
		'U_POPUP_PM'			=> phpbb::$url->append_sid('ucp', 'i=pm&amp;mode=popup'),
		'UA_POPUP_PM'			=> addslashes(phpbb::$url->append_sid('ucp', 'i=pm&amp;mode=popup')),
		'U_MEMBERLIST'			=> phpbb::$url->append_sid('memberlist'),
		'U_VIEWONLINE'			=> (phpbb::$acl->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel')) ? phpbb::$url->append_sid('viewonline') : '',
		'U_LOGIN_LOGOUT'		=> $u_login_logout,
		'U_INDEX'				=> phpbb::$url->append_sid('index'),
		'U_SEARCH'				=> phpbb::$url->append_sid('search'),
		'U_REGISTER'			=> phpbb::$url->append_sid('ucp', 'mode=register'),
		'U_PROFILE'				=> phpbb::$url->append_sid('ucp'),
		'U_MODCP'				=> phpbb::$url->append_sid('mcp', false, true, phpbb::$user->session_id),
		'U_FAQ'					=> phpbb::$url->append_sid('faq'),
		'U_SEARCH_SELF'			=> phpbb::$url->append_sid('search', 'search_id=egosearch'),
		'U_SEARCH_NEW'			=> phpbb::$url->append_sid('search', 'search_id=newposts'),
		'U_SEARCH_UNANSWERED'	=> phpbb::$url->append_sid('search', 'search_id=unanswered'),
		'U_SEARCH_ACTIVE_TOPICS'=> phpbb::$url->append_sid('search', 'search_id=active_topics'),
		'U_DELETE_COOKIES'		=> phpbb::$url->append_sid('ucp', 'mode=delete_cookies'),
		'U_TEAM'				=> (phpbb::$user->data['user_id'] != ANONYMOUS && !phpbb::$acl->acl_get('u_viewprofile')) ? '' : phpbb::$url->append_sid('memberlist', 'mode=leaders'),
		'U_RESTORE_PERMISSIONS'	=> (phpbb::$user->data['user_perm_from'] && phpbb::$acl->acl_get('a_switchperm')) ? phpbb::$url->append_sid('ucp', 'mode=restore_perm') : '',

		'S_USER_LOGGED_IN'		=> (phpbb::$user->data['user_id'] != ANONYMOUS) ? true : false,
		'S_AUTOLOGIN_ENABLED'	=> (phpbb::$config['allow_autologin']) ? true : false,
		'S_BOARD_DISABLED'		=> (phpbb::$config['board_disable']) ? true : false,
		'S_REGISTERED_USER'		=> (!empty(phpbb::$user->is_registered)) ? true : false,
		'S_IS_BOT'				=> (!empty(phpbb::$user->is_bot)) ? true : false,
		'S_USER_PM_POPUP'		=> phpbb::$user->optionget('popuppm'),
		'S_USER_LANG'			=> $user_lang,
		'S_USER_BROWSER'		=> (isset(phpbb::$user->data['session_browser'])) ? phpbb::$user->data['session_browser'] : phpbb::$user->lang['UNKNOWN_BROWSER'],
		'S_USERNAME'			=> phpbb::$user->data['username'],
		'S_CONTENT_DIRECTION'	=> phpbb::$user->lang['DIRECTION'],
		'S_CONTENT_FLOW_BEGIN'	=> (phpbb::$user->lang['DIRECTION'] == 'ltr') ? 'left' : 'right',
		'S_CONTENT_FLOW_END'	=> (phpbb::$user->lang['DIRECTION'] == 'ltr') ? 'right' : 'left',
		'S_CONTENT_ENCODING'	=> 'UTF-8',
		'S_TIMEZONE'			=> (phpbb::$user->data['user_dst'] || (phpbb::$user->data['user_id'] == ANONYMOUS && phpbb::$config['board_dst'])) ? sprintf(phpbb::$user->lang['ALL_TIMES'], phpbb::$user->lang['tz'][$tz], phpbb::$user->lang['tz']['dst']) : sprintf(phpbb::$user->lang['ALL_TIMES'], phpbb::$user->lang['tz'][$tz], ''),
		'S_DISPLAY_ONLINE_LIST'	=> ($l_online_time) ? 1 : 0,
		'S_DISPLAY_SEARCH'		=> (!phpbb::$config['load_search']) ? 0 : (phpbb::$acl->acl_get('u_search') && phpbb::$acl->acl_getf_global('f_search')),
		'S_DISPLAY_PM'			=> (phpbb::$config['allow_privmsg'] && !empty(phpbb::$user->data['is_registered']) && (phpbb::$acl->acl_get('u_readpm') || phpbb::$acl->acl_get('u_sendpm'))) ? true : false,
		'S_DISPLAY_MEMBERLIST'	=> (isset($auth)) ? phpbb::$acl->acl_get('u_viewprofile') : 0,
		'S_NEW_PM'				=> ($s_privmsg_new) ? 1 : 0,
		'S_REGISTER_ENABLED'	=> (phpbb::$config['require_activation'] != USER_ACTIVATION_DISABLE) ? true : false,

		'T_THEME_PATH'			=> PHPBB_ROOT_PATH . 'styles/' . phpbb::$user->theme['theme_path'] . '/theme',
		'T_TEMPLATE_PATH'		=> PHPBB_ROOT_PATH . 'styles/' . phpbb::$user->theme['template_path'] . '/template',
		'T_IMAGESET_PATH'		=> PHPBB_ROOT_PATH . 'styles/' . phpbb::$user->theme['imageset_path'] . '/imageset',
		'T_IMAGESET_LANG_PATH'	=> PHPBB_ROOT_PATH . 'styles/' . phpbb::$user->theme['imageset_path'] . '/imageset/' . phpbb::$user->data['user_lang'],
		'T_IMAGES_PATH'			=> PHPBB_ROOT_PATH . 'images/',
		'T_SMILIES_PATH'		=> PHPBB_ROOT_PATH . phpbb::$config['smilies_path'] . '/',
		'T_AVATAR_PATH'			=> PHPBB_ROOT_PATH . phpbb::$config['avatar_path'] . '/',
		'T_AVATAR_GALLERY_PATH'	=> PHPBB_ROOT_PATH . phpbb::$config['avatar_gallery_path'] . '/',
		'T_ICONS_PATH'			=> PHPBB_ROOT_PATH . phpbb::$config['icons_path'] . '/',
		'T_RANKS_PATH'			=> PHPBB_ROOT_PATH . phpbb::$config['ranks_path'] . '/',
		'T_UPLOAD_PATH'			=> PHPBB_ROOT_PATH . phpbb::$config['upload_path'] . '/',
		'T_STYLESHEET_LINK'		=> (!phpbb::$user->theme['theme_storedb']) ? PHPBB_ROOT_PATH . 'styles/' . phpbb::$user->theme['theme_path'] . '/theme/stylesheet.css' : phpbb::$url->get(PHPBB_ROOT_PATH . 'style.' . PHP_EXT . '?id=' . phpbb::$user->theme['style_id'] . '&amp;lang=' . phpbb::$user->data['user_lang']), //PHPBB_ROOT_PATH . "store/{$user->theme['theme_id']}_{$user->theme['imageset_id']}_{$user->lang_name}.css"
		'T_STYLESHEET_NAME'		=> phpbb::$user->theme['theme_name'],

		'SITE_LOGO_IMG'			=> phpbb::$user->img('site_logo'),

		'A_COOKIE_SETTINGS'		=> addslashes('; path=' . phpbb::$config['cookie_path'] . ((!phpbb::$config['cookie_domain'] || phpbb::$config['cookie_domain'] == 'localhost' || phpbb::$config['cookie_domain'] == '127.0.0.1') ? '' : '; domain=' . phpbb::$config['cookie_domain']) . ((!phpbb::$config['cookie_secure']) ? '' : '; secure')),
	));

	// application/xhtml+xml not used because of IE
	header('Content-type: text/html; charset=UTF-8');

	header('Cache-Control: private, no-cache="set-cookie"');
	header('Expires: 0');
	header('Pragma: no-cache');

	if (phpbb::$plugins->function_inject(__FUNCTION__, 'return')) return phpbb::$plugins->call_inject(__FUNCTION__, 'return');
}

/**
* Generate page footer
*/
function page_footer($run_cron = true)
{
	global $starttime;

	// Output page creation time
	if (phpbb::$base_config['debug'])
	{
		$mtime = explode(' ', microtime());
		$totaltime = $mtime[0] + $mtime[1] - $starttime;

		if (phpbb_request::variable('explain', false) && /*phpbb::$acl->acl_get('a_') &&*/ phpbb::$base_config['debug_extra'] && method_exists(phpbb::$db, 'sql_report'))
		{
			phpbb::$db->sql_report('display');
		}

		$debug_output = sprintf('Time : %.3fs | ' . phpbb::$db->sql_num_queries() . ' Queries | GZIP : ' . ((phpbb::$config['gzip_compress']) ? 'On' : 'Off') . ((phpbb::$user->system['load']) ? ' | Load : ' . phpbb::$user->system['load'] : ''), $totaltime);

		if (/*phpbb::$acl->acl_get('a_') &&*/ phpbb::$base_config['debug_extra'])
		{
			if (function_exists('memory_get_usage'))
			{
				if ($memory_usage = memory_get_usage())
				{
					$memory_usage -= phpbb::$base_config['memory_usage'];
					$memory_usage = get_formatted_filesize($memory_usage);

					$debug_output .= ' | Memory Usage: ' . $memory_usage;
				}
			}

			$debug_output .= ' | <a href="' . phpbb::$url->build_url() . '&amp;explain=1">Explain</a>';
		}
	}

	phpbb::$template->assign_vars(array(
		'DEBUG_OUTPUT'			=> (phpbb::$base_config['debug']) ? $debug_output : '',
		'TRANSLATION_INFO'		=> (!empty(phpbb::$user->lang['TRANSLATION_INFO'])) ? phpbb::$user->lang['TRANSLATION_INFO'] : '',

		'U_ACP' => (phpbb::$acl->acl_get('a_') && !empty(phpbb::$user->is_registered)) ? phpbb::$url->append_sid(phpbb::$base_config['admin_folder'] . '/index', false, true, phpbb::$user->session_id) : '',
	));

	// Call cron-type script
	if (!defined('IN_CRON') && $run_cron && !phpbb::$config['board_disable'])
	{
		$cron_type = '';

		if (time() - phpbb::$config['queue_interval'] > phpbb::$config['last_queue_run'] && !defined('IN_ADMIN') && file_exists(PHPBB_ROOT_PATH . 'cache/queue.' . PHP_EXT))
		{
			// Process email queue
			$cron_type = 'queue';
		}
		else if (method_exists(phpbb::$acm, 'tidy') && time() - phpbb::$config['cache_gc'] > phpbb::$config['cache_last_gc'])
		{
			// Tidy the cache
			$cron_type = 'tidy_cache';
		}
		else if (time() - phpbb::$config['warnings_gc'] > phpbb::$config['warnings_last_gc'])
		{
			$cron_type = 'tidy_warnings';
		}
		else if (time() - phpbb::$config['database_gc'] > phpbb::$config['database_last_gc'])
		{
			// Tidy the database
			$cron_type = 'tidy_database';
		}
		else if (time() - phpbb::$config['search_gc'] > phpbb::$config['search_last_gc'])
		{
			// Tidy the search
			$cron_type = 'tidy_search';
		}
		else if (time() - phpbb::$config['session_gc'] > phpbb::$config['session_last_gc'])
		{
			$cron_type = 'tidy_sessions';
		}

		if ($cron_type)
		{
			phpbb::$template->assign_var('RUN_CRON_TASK', '<img src="' . phpbb::$url->append_sid('cron', 'cron_type=' . $cron_type) . '" width="1" height="1" alt="cron" />');
		}
	}

	phpbb::$template->display('body');

	garbage_collection();
	exit_handler();
}

/**
* Closing the cache object and the database
* Cool function name, eh? We might want to add operations to it later
*/
function garbage_collection()
{
	// Unload cache, must be done before the DB connection if closed
	if (phpbb::registered('acm'))
	{
		phpbb::$acm->unload();
	}

	// Close our DB connection.
	if (phpbb::registered('db'))
	{
		phpbb::$db->sql_close();
	}
}

/**
* Handler for exit calls in phpBB.
* This function supports hooks.
*
* Note: This function is called after the template has been outputted.
*/
function exit_handler()
{
	// needs to be run prior to the hook
	if (phpbb_request::super_globals_disabled())
	{
		phpbb_request::enable_super_globals();
	}

	// As a pre-caution... some setups display a blank page if the flush() is not there.
	(empty(phpbb::$config['gzip_compress'])) ? @flush() : @ob_flush();

	exit;
}

?>