<?php
/***************************************************************************
 *                               functions.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: functions.php,v 1.1 2010/10/10 15:05:27 orynider Exp $
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *
 ***************************************************************************/
 
/**
* set_var
*
* Set variable, used by {@link request_var the request_var function}
*
* @access private
*/
function set_var(&$result, $var, $type, $multibyte = false)
{
	settype($var, $type);
	$result = $var;

	if ($type == 'string')
	{
		$result = trim(htmlspecialchars(str_replace(array("\r\n", "\r", "\0"), array("\n", "\n", ''), $result), ENT_COMPAT, 'UTF-8'));

		if (!empty($result))
		{
			// Make sure multibyte characters are wellformed
			if ($multibyte)
			{
				if (!preg_match('/^./u', $result))
				{
					$result = '';
				}
			}
			else
			{
				// no multibyte, allow only ASCII (0-127)
				$result = preg_replace('/[\x80-\xFF]/', '?', $result);
			}
		}

		$result = (STRIP) ? stripslashes($result) : $result;
	}
}

/**
 * Function: _read() from class mx_request_vars
 * autor John Olson 
* Get the value of the specified request var (post or get) and force the result to be
 * of specified type. It might also transform the result (stripslashes, htmlspecialchars) for security
 * purposes. It all depends on the $type argument.
 * If the specified request var does not exist, then the default ($dflt) value is returned.
* Note the $type argument behaves as a bit array where more than one option can be specified by OR'ing
* the passed argument. This is tipical practice in languages like C, but it can also be done with PHP.
*
* @access private
* @param unknown_type $var
* @param unknown_type $type
* @param unknown_type $dflt
 * @return unknown
 */
function read_var($var, $dflt = '', $type = 0, $not_null = false)
{
	if(($type & (64|128)) == 0)
	{
		$type |= (64|128);
	}

	if(($type & 64) && isset($_POST[$var]) || ($type & 128) && isset($_GET[$var]))
	{
		$val = ( ($type & 64) && isset($_POST[$var]) ? $_REQUEST[$var] : $_GET[$var]);
		if(!($type & 16))
		{
			if(is_array($val))
			{
				foreach($val as $k => $v)
				{
					$val[$k] = trim(stripslashes($v));
				}
			}
			else
			{
				$val = trim(stripslashes($val));
			}
		}
	}
	else if(($type & 64) && isset($HTTP_POST_VARS[$var]) || ($type & 128) && isset($HTTP_GET_VARS[$var]))
	{
		$val = ( ($type & 64) && isset($HTTP_POST_VARS[$var]) ? $HTTP_REQUEST_VARS[$var] : $HTTP_GET_VARS[$var]);
		if(!($type & 16))
		{
			if(is_array($val))
			{
				foreach($val as $k => $v)
				{
					$val[$k] = trim(stripslashes($v));
				}
			}
			else
			{
				$val = trim(stripslashes($val));
			}
		}
	}	
	else
	{
		$val = $dflt;
	}

	if($type & 1)		// integer
	{
		return $not_null && empty($val) ? $dflt : intval($val);
	}

	if($type & 2)		// float
	{
		return $not_null && empty($val) ? $dflt : floatval($val);
	}

	if($type & 8)	// ie username
	{
		if( is_array($val) )
		{
			foreach( $val as $k => $v )
			{
				$val[$k] = htmlspecialchars(strip_tags(ltrim(rtrim($v, " \t\n\r\0\x0B\\"))));
			}
		}
		else
		{
			$val = htmlspecialchars(strip_tags(ltrim(rtrim($val, " \t\n\r\0\x0B\\"))));
		}
	}
	elseif($type & 4)	// no slashes nor html
	{
			if(is_array($val))
			{
			foreach( $val as $k => $v )
			{
				$val[$k] = htmlspecialchars(ltrim(rtrim($v, " \t\n\r\0\x0B\\")));
			}
		}
		else
		{
			$val = htmlspecialchars(ltrim(rtrim($val, " \t\n\r\0\x0B\\")));
		}
	}

	if($type & 32)
	{
		if(is_array($val))
		{
			foreach($val as $k => $v)
			{
				$val[$k] = str_replace(($type & 16 ? "\'" : "'"), "''", $v);
			}
		}
		else
		{
			$val = str_replace(($type & 16 ? "\'" : "'"), "''", $val);
		}
	}
	return $not_null && empty($val) ? $dflt : $val;
}

/**
* request_var
*
* Used to get passed variable
*/
function request_var($var_name, $default, $multibyte = false, $cookie = false)
{
	if (($cookie == false) && isset($_COOKIE[$var_name]))
	{
		if (empty($_GET[$var_name]) && empty($_POST[$var_name]))
		{
			return (is_array($default)) ? array() : $default;
		}
		$_REQUEST[$var_name] = isset($_POST[$var_name]) ? $_POST[$var_name] : $_GET[$var_name];
	}

	$super_global = ($cookie) ? '_COOKIE' : '_REQUEST';
	if (!isset($GLOBALS[$super_global][$var_name]) || is_array($GLOBALS[$super_global][$var_name]) != is_array($default))
	{
		return (is_array($default)) ? array() : $default;
	}

	$var = $GLOBALS[$super_global][$var_name];
	if (!is_array($default))
	{
		$type = gettype($default);
	}
	else
	{
		list($key_type, $type) = each($default);
		$type = gettype($type);
		$key_type = gettype($key_type);
		if ($type == 'array')
		{
			reset($default);
			$default = current($default);
			list($sub_key_type, $sub_type) = each($default);
			$sub_type = gettype($sub_type);
			$sub_type = ($sub_type == 'array') ? 'NULL' : $sub_type;
			$sub_key_type = gettype($sub_key_type);
		}
	}

	if (is_array($var))
	{
		$_var = $var;
		$var = array();

		foreach ($_var as $k => $v)
		{
			set_var($k, $k, $key_type);
			if ($type == 'array' && is_array($v))
			{
				foreach ($v as $_k => $_v)
				{
					if (is_array($_v))
					{
						$_v = null;
					}
					set_var($_k, $_k, $sub_key_type, $multibyte);
					set_var($var[$k][$_k], $_v, $sub_type, $multibyte);
				}
			}
			else
			{
				if ($type == 'array' || is_array($v))
				{
					$v = null;
				}
				set_var($var[$k], $v, $type, $multibyte);
			}
		}
	}
	else
	{
		set_var($var, $var, $type, $multibyte);
	}

	return $var;
}

/**
* Request the var value but returns only true of false, useful for forms validations
*/
function request_boolean_var($var_name, $default, $multibyte = false, $post_only = false)
{
	if ($post_only)
	{
		$return = request_post_var($var_name, $default, $multibyte);
	}
	else
	{
		$return = request_var($var_name, $default, $multibyte);
	}
	$return = !empty($return) ? true : false;
	return $return;
}

/**
* Gets only POST vars
*/
function request_post_var($var_name, $default, $multibyte = false)
{
	$return = $default;
	if (isset($_POST[$var_name]))
	{
		$return = request_var($var_name, $default, $multibyte);
	}
	return $return;
}

/**
* Get only GET vars
*/
function request_get_var($var_name, $default, $multibyte = false)
{
	$return = $default;
	if (isset($_GET[$var_name]))
	{
		$temp_post_var = isset($_POST[$var_name]) ? $_POST[$var_name] : '';
		$_POST[$var_name] = $_GET[$var_name];
		$return = request_var($var_name, $default, $multibyte);
		$_POST[$var_name] = $temp_post_var;
	}
	return $return;
}

/**
* Check GET POST vars exists
*/
function check_http_var_exists($var_name, $empty_var = false)
{
	if ($empty_var)
	{
		if (isset($_GET[$var_name]) || isset($_POST[$var_name]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{
		if (!empty($_GET[$var_name]) || !empty($_POST[$var_name]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	return false;
}

/**
 * Is POST var?
 *
 * Boolean method to check for existence of POST variable.
 * * autor John Olson 
 * @access public
 * @param string $var
 * @return boolean
 */
function is_post($var)
{
	global $_POST;
	// Note: _x and _y are used by (at least IE) to return the mouse position at onclick of INPUT TYPE="img" elements.
	return (isset($_POST[$var]) || ( isset($_POST[$var.'_x']) && isset($_POST[$var.'_y']))) ? 1 : 0;
}

/**
 * Is GET var?
 *
 * Boolean method to check for existence of GET variable.
 * * autor John Olson 
 * @access public
 * @param string $var
 * @return boolean
 */
function is_get($var)
{
	global $_GET;	
	return isset($_GET[$var]) ? 1 : 0 ;
}

/**
 * Is REQUEST (either GET or POST) var?
 *
 * Boolean method to check for existence of any REQUEST (both) variable.
 * * autor John Olson 
 * @access public
 * @param string $var
 * @return boolean
 */
function is_request($var)
{
	return (is_get($var) || is_post($var)) ? 1 : 0;
}

/**
 * Is POST var empty?
 * * autor John Olson 
 * Boolean method to check if POST variable is empty
 * as it might be set but still be empty.
 *
 * @access public
 * @param string $var
 * @return boolean
 */

 function is_empty_post($var)
{
	global $_POST;

	return (empty($_POST[$var]) && ( empty($_POST[$var.'_x']) || empty($_POST[$var.'_y']))) ? 1 : 0 ;
}

/**
 * Is GET var empty?
 * * autor John Olson 
 * Boolean method to check if GET variable is empty
 * as it might be set but still be empty
 *
 * @access public
 * @param string $var
 * @return boolean
 */
function is_empty_get($var)
{
	global $_GET;
	return empty($_GET[$var]) ? 1 : 0 ;
}

/** /
*
* by guillermogomezruiz@gmail.com ¶
/**/
function file_exists_2($filePath)
{
	if (!function_exists('curl_init')) 
	{
		//continue
	}
	else
	{
		return ($ch = curl_init($filePath)) ? @curl_close($ch) || true : false;		
	}	

}

/** /
*
* https://stackoverflow.com/users/2456038/rafasashi
/**/
function custom_file_exists($file_path = '')
{
	if (function_exists('file_exists') && $file_exists = file_exists($file_path)) 
	{
		return $file_exists;
	}
	else
	{
		$file_exists = false;
	}	
	
	//clear cached results
	//clearstatcache();
	
	//trim path
	$file_dir = trim(dirname($file_path));
	
	//normalize path separator
    $file_dir = str_replace('/', DIRECTORY_SEPARATOR, $file_dir) . DIRECTORY_SEPARATOR;
	
	//trim file name
	$file_name = trim(basename($file_path));
	
	//rebuild path
	$file_path = $file_dir . "{$file_name}";
	
	//If you simply want to check that some file (not directory) exists, 
	//and concerned about performance, try is_file() instead.
	//It seems like is_file() is almost 2x faster when a file exists 
	//and about the same when it doesn't.
	if (!function_exists('curl_init')) 
	{
		$file_exists = is_file($file_path);
	}
	else
	{
		$file_exists = is_file($file_path) ? true :  (($ch = @curl_init($file_path)) ? @curl_close($ch) || true : false);		
	}	
	return $file_exists;
}

/**
 * Is REQUEST empty (GET and POST) var?
 * * autor John Olson 
 * Boolean method to check if REQUEST (both) variable is empty.
 *
 * @access public
 * @param string $var
 * @return boolean
 */
function is_empty_request($var)
{
	return (is_empty_get($var) && is_empty_post($var)) ? 1 : 0;
}

/**
* Set config value. Creates missing config entry.
*/
function set_config($config_name, $config_value)
{
	global $db, $board_config;

	$sql = 'UPDATE ' . CONFIG_TABLE . "
		SET config_value = '" . $db->sql_escape($config_value) . "'
		WHERE config_name = '" . $db->sql_escape($config_name) . "'";
	$db->sql_query($sql);

	if (!$db->sql_affectedrows() && !isset($board_config[$config_name]))
	{
		$sql = 'INSERT INTO ' . CONFIG_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'config_name'	=> $config_name,
			'config_value'	=> $config_value));
		$db->sql_query($sql);
	}
	$config[$config_name] = $config_value;
}

function get_db_stat($mode)
{
	global $db;

	switch($mode)
	{
		case 'usercount':
			$sql = "SELECT COUNT(user_id) AS total
				FROM " . USERS_TABLE . "
				WHERE user_id <> " . ANONYMOUS;
			break;

		case 'newestuser':
			$sql = "SELECT user_id, username
				FROM " . USERS_TABLE . "
				WHERE user_id <> " . ANONYMOUS . "
				ORDER BY user_id DESC
				LIMIT 1";
			break;

		case 'postcount':
		case 'topiccount':
			$sql = "SELECT SUM(forum_topics) AS topic_total, SUM(forum_posts) AS post_total
				FROM " . FORUMS_TABLE;
			break;
	}

	if (!($result = $db->sql_query($sql)))
	{
		return false;
	}

	$row = $db->sql_fetchrow($result);

	switch ($mode)
	{
		case 'usercount':
			return $row['total'];
		break;
		case 'newestuser':
			return $row;
		break;
		case 'postcount':
			return $row['post_total'];
		break;
		case 'topiccount':
			return $row['topic_total'];
		break;
	}

	return false;
}

// added at phpBB 2.0.11 to properly format the username
function phpbb_clean_username($username)
{
	$username = substr(htmlspecialchars(str_replace("\'", "'", trim($username))), 0, 25);
	$username = phpbb_rtrim($username, "\\");
	$username = str_replace("'", "\'", $username);

	return $username;
}

/**
* This function is a wrapper for ltrim, as charlist is only supported in php >= 4.1.0
* Added in phpBB 2.0.18
*/
function phpbb_ltrim($str, $charlist = false)
{
	if ($charlist === false)
	{
		return ltrim($str);
	}
	
	$php_version = explode('.', PHP_VERSION);

	// php version < 4.1.0
	if ((int) $php_version[0] < 4 || ((int) $php_version[0] == 4 && (int) $php_version[1] < 1))
	{
		while ($str{0} == $charlist)
		{
			$str = substr($str, 1);
		}
	}
	else
	{
		$str = ltrim($str, $charlist);
	}

	return $str;
}

// added at phpBB 2.0.12 to fix a bug in PHP 4.3.10 (only supporting charlist in php >= 4.1.0)
function phpbb_rtrim($str, $charlist = false)
{
	if ($charlist === false)
	{
		return rtrim($str);
	}
	
	$php_version = explode('.', PHP_VERSION);

	// php version < 4.1.0
	if ((int) $php_version[0] < 4 || ((int) $php_version[0] == 4 && (int) $php_version[1] < 1))
	{
		while ($str{strlen($str)-1} == $charlist)
		{
			$str = substr($str, 0, strlen($str)-1);
		}
	}
	else
	{
		$str = rtrim($str, $charlist);
	}

	return $str;
}

/**
* Generates an alphanumeric random string of given length
*
* @return string
*/
function gen_rand_string($num_chars = 8)
{
	// [a, z] + [0, 9] = 36
	return substr(strtoupper(base_convert(unique_id(), 16, 36)), 0, $num_chars);
}

/**
* Generates a user-friendly alphanumeric random string of given length
* We remove 0 and O so users cannot confuse those in passwords etc.
*
* @return string
*/
function gen_rand_string_friendly($num_chars = 8)
{
	$rand_str = unique_id();

	// Remove Z and Y from the base_convert(), replace 0 with Z and O with Y
	// [a, z] + [0, 9] - {z, y} = [a, z] + [0, 9] - {0, o} = 34
	$rand_str = str_replace(array('0', 'O'), array('Z', 'Y'), strtoupper(base_convert($rand_str, 16, 34)));

	return substr($rand_str, 0, $num_chars);
}

/**
* Return unique id
*/
function unique_id()
{
	return bin2hex(random_bytes(8));
}

/**
* Wrapper for mt_rand() which allows swapping $min and $max parameters.
*
* PHP does not allow us to swap the order of the arguments for mt_rand() anymore.
* (since PHP 5.3.4, see http://bugs.php.net/46587)
*
* @param int $min		Lowest value to be returned
* @param int $max		Highest value to be returned
*
* @return int			Random integer between $min and $max (or $max and $min)
*/
function phpbb_mt_rand($min, $max)
{
	return ($min > $max) ? mt_rand($max, $min) : mt_rand($min, $max);
}

/**
* Wrapper for getdate() which returns the equivalent array for UTC timestamps.
*
* @param int $time		Unix timestamp (optional)
*
* @return array			Returns an associative array of information related to the timestamp.
*						See http://www.php.net/manual/en/function.getdate.php
*/
function phpbb_gmgetdate($time = false)
{
	if ($time === false)
	{
		$time = time();
	}

	// getdate() interprets timestamps in local time.
	// What follows uses the fact that getdate() and
	// date('Z') balance each other out.
	return getdate($time - date('Z'));
}

/**
* Our own generator of random values
* This uses a constantly changing value as the base for generating the values
* The board wide setting is updated once per page if this code is called
* With thanks to Anthrax101 for the inspiration on this one
* Added in phpBB 2.0.20
*/
function dss_rand()
{
	global $db, $board_config, $dss_seeded;

	$val = $board_config['rand_seed'] . microtime();
	$val = md5($val);
	$board_config['rand_seed'] = md5($board_config['rand_seed'] . $val . 'a');
   
	if($dss_seeded !== true)
	{
		$sql = "UPDATE " . CONFIG_TABLE . " SET
			config_value = '" . $board_config['rand_seed'] . "'
			WHERE config_name = 'rand_seed'";
		
		if(!$db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Unable to reseed PRNG", "", __LINE__, __FILE__, $sql);
		}

		$dss_seeded = true;
	}

	return substr($val, 4, 16);
}

//
// Get Userdata, $user can be username or user_id. If force_str is true, the username will be forced.
//
function get_userdata($user, $force_str = false)
{
	global $db;

	if (!is_numeric($user) || $force_str)
	{
		$user = phpbb_clean_username($user);
	}
	else
	{
		$user = intval($user);
	}

	$sql = "SELECT *
		FROM " . USERS_TABLE . " 
		WHERE ";
	$sql .= ((is_integer($user)) ? "user_id = $user" : "username = '" .  str_replace("\'", "''", $user) . "'") . " AND user_id <> " . ANONYMOUS;
	if (!($result = $db->sql_query($sql)))
	{
		message_die(GENERAL_ERROR, 'Tried obtaining data for a non-existent user', '', __LINE__, __FILE__, $sql);
	}

	return ($row = $db->sql_fetchrow($result)) ? $row : false;
}

/**
 * obtain_phpbb_config
 *
* @access public
* @param boolean $use_cache
* @return unknown
 */
function obtain_phpbb_config($use_cache = true)
{
	global $db, $cache, $phpEx;

	if (($config = $cache->get('phpbb_config')) && ($use_cache) )
	{
		return $config;
	}
	else
	{		
		if (!defined('CONFIG_TABLE'))
		{
			global $table_prefix, $phpbb_root_path;
				
			require $phpbb_root_path. "includes/constants.$phpEx";
		}		
		
		$sql = "SELECT *
				FROM " . CONFIG_TABLE;

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			if (!function_exists('message_die'))
			{
				die("Couldnt query config information, Allso this hosting or server is using a cache optimizer not compatible with MX-Publisher or just lost connection to database wile query.");
			}
			else
			{
				message_die( GENERAL_ERROR, 'Couldnt query config information', '', __LINE__, __FILE__, $sql );
			}
		}

		while ( $row = $db->sql_fetchrow($result) )
		{
			$config[$row['config_name']] = $row['config_value'];
		}
		$db->sql_freeresult($result);

		if ($use_cache)
		{
			$cache->put('phpbb_config', $config);
		}
		return $config;
	}
}

/**
* Enter description here...
*
* @return unknown
*/
function generate_group_select_sql()
{
	$sql = "SELECT group_id, group_name
			FROM " . GROUPS_TABLE . "
			WHERE group_single_user <> " . TRUE . "
			ORDER BY group_name ASC";
	return $sql;
}

/**
 * Enter description here...
 *
* @return unknown
 */
function generate_session_online_sql($guest = false)
{
	if ($guest)
	{
		$sql = "SELECT *
				FROM " . SESSIONS_TABLE . "
				WHERE session_logged_in = 0
					AND session_time >= " . ( time() - 300 ) . "
				ORDER BY session_time DESC";
	}
	else
	{
		$sql = "SELECT u.*, s.*
				FROM " . USERS_TABLE . " u, " . SESSIONS_TABLE . " s
				WHERE s.session_logged_in = " . TRUE . "
					AND u.user_id = s.session_user_id
					AND u.user_id <> " . ANONYMOUS . "
					AND s.session_time >= " . ( time() - 300 ) . "
				ORDER BY u.user_session_time DESC";
	}
	return $sql;
}

/**
 * Enter description here...
 *
* @return unknown
*/
function get_phpbb_version()
{
	global $board_config;

	return '2' . $board_config['version'];
}

/**
 * {@inheritdoc}
 */
function mx_chmod($files, $perms = null, $recursive = false, $force_chmod_link = false)
{
	if (is_null($perms))
	{
		// Default to read permission for compatibility reasons
		$perms = CHMOD_READ;
	}

	// Check if we got a permission flag
	if ($perms > CHMOD_ALL)
	{
		$file_perm = $perms;

		// Extract permissions
		//$owner = ($file_perm >> 6) & 7; // This will be ignored
		$group = ($file_perm >> 3) & 7;
		$other = ($file_perm >> 0) & 7;

		// Does any permissions provided? if so we add execute bit for directories
		$group = ($group !== 0) ? ($group | CHMOD_EXECUTE) : $group;
		$other = ($other !== 0) ? ($other | CHMOD_EXECUTE) : $other;

		// Compute directory permissions
		$dir_perm = (CHMOD_ALL << 6) + ($group << 3) + ($other << 3);
	}
	else
	{
		// Add execute bit to owner if execute bit is among perms
		$owner_perm	= (CHMOD_READ | CHMOD_WRITE) | ($perms & CHMOD_EXECUTE);
		$file_perm	= ($owner_perm << 6) + ($perms << 3) + ($perms << 0);

		// Compute directory permissions
		$perm = ($perms !== 0) ? ($perms | CHMOD_EXECUTE) : $perms;
		$dir_perm = (($owner_perm | CHMOD_EXECUTE) << 6) + ($perm << 3) + ($perm << 0);
	}

	// Symfony's filesystem component does not support extra execution flags on directories
	// so we need to implement it again
	foreach ($this->to_iterator($files) as $file)
	{
		if ($recursive && is_dir($file) && !is_link($file))
		{
			mx_chmod(new \FilesystemIterator($file), $perms, true);
		}

		// Don't chmod links as mostly those require 0777 and that cannot be changed
		if (is_dir($file) || (is_link($file) && $force_chmod_link))
		{
			if (true !== @chmod($file, $dir_perm))
			{
				throw new filesystem_exception('CANNOT_CHANGE_FILE_PERMISSIONS', $file,  array());
			}
		}
		else if (is_file($file))
		{
			if (true !== @chmod($file, $file_perm))
			{
				throw new filesystem_exception('CANNOT_CHANGE_FILE_PERMISSIONS', $file,  array());
			}
		}
	}
}
/**
 * {@inheritdoc}
 */
function mx_chown($files, $user, $recursive = false)
{
	try
	{
		@chown($files, $user, $recursive);
	}
	catch (IOException $e)
	{
		// Try to recover filename
		// By the time this is written that is at the end of the message
		$error = trim($e->getMessage());
		$file = substr($error, strrpos($error, ' '));

		throw new filesystem_exception('CANNOT_CHANGE_FILE_GROUP', $file, array(), $e);
	}
}

/**
 * {@inheritdoc}
 */
function mx_chgrp($files, $group, $recursive = false)
{
	try
	{
		@chgrp($files, $group, $recursive);
	}
	catch (IOException $e)
	{
		// Try to recover filename
		// By the time this is written that is at the end of the message
		$error = trim($e->getMessage());
		$file = substr($error, strrpos($error, ' '));

		throw new filesystem_exception('CANNOT_CHANGE_FILE_GROUP', $file, array(), $e);
	}
}

/**
 * Global function for chmodding directories and files for internal use
 *
 * This function determines owner and group whom the file belongs to and user and group of PHP and then set safest possible file permissions.
 * The function determines owner and group from common.php file and sets the same to the provided file.
 * The function uses bit fields to build the permissions.
 * The function sets the appropiate execute bit on directories.
 *
 * Supported constants representing bit fields are:
 *
 * CHMOD_ALL - all permissions (7)
 * CHMOD_READ - read permission (4)
 * CHMOD_WRITE - write permission (2)
 * CHMOD_EXECUTE - execute permission (1)
 *
 * NOTE: The function uses POSIX extension and fileowner()/filegroup() functions. If any of them is disabled, this function tries to build proper permissions, by calling is_readable() and is_writable() functions.
 *
 * @param string	$filename	The file/directory to be chmodded
 * @param int	$perms		Permissions to set
 *
 * @return bool	true on success, otherwise false
 *
 * @deprecated 3.2.0-dev	use \phpbb\filesystem\filesystem::phpbb_chmod() instead
 */

function phpbb_chmod($files, $perms = null, $recursive = false, $force_chmod_link = false)
{
	static $_chmod_info;
	
	if (is_null($perms))
	{
		// Default to read permission for compatibility reasons
		$perms = self::CHMOD_READ;
	}

	if (empty($_chmod_info))
	{
		if (!function_exists('fileowner') || !function_exists('filegroup'))
		{
			$_chmod_info['process'] = false;
		}
		else
		{
			$common_php_owner	= @fileowner(__FILE__);
			$common_php_group	= @filegroup(__FILE__);

			// And the owner and the groups PHP is running under.
			$php_uid	= (function_exists('posic_getuid')) ? @posix_getuid() : false;
			$php_gids	= (function_exists('posix_getgroups')) ? @posix_getgroups() : false;

			// If we are unable to get owner/group, then do not try to set them by guessing
			if (!$php_uid || empty($php_gids) || !$common_php_owner || !$common_php_group)
			{
				$_chmod_info['process'] = false;
			}
			else
			{
				$_chmod_info = array(
					'process'		=> true,
					'common_owner'	=> $common_php_owner,
					'common_group'	=> $common_php_group,
					'php_uid'		=> $php_uid,
					'php_gids'		=> $php_gids,
				);
			}
		}
	}

	if ($_chmod_info['process'])
	{
		try
		{
			foreach ($this->to_iterator($files) as $file)
			{
				$file_uid = @fileowner($file);
				$file_gid = @filegroup($file);

				// Change owner
				if ($file_uid !== $_chmod_info['common_owner'])
				{
					mx_chown($file, $_chmod_info['common_owner'], $recursive);
				}

				// Change group
				if ($file_gid !== $_chmod_info['common_group'])
				{
					mx_chgrp($file, $_chmod_info['common_group'], $recursive);
				}

				clearstatcache();
				$file_uid = @fileowner($file);
				$file_gid = @filegroup($file);
			}
		}

		catch (filesystem_exception $e)
		{
			$_chmod_info['process'] = false;
		}
	}

	// Still able to process?
	if ($_chmod_info['process'])
	{
		if ($file_uid === $_chmod_info['php_uid'])
		{
			$php = 'owner';
		}
		else if (in_array($file_gid, $_chmod_info['php_gids']))
		{
			$php = 'group';
		}
		else
		{
			// Since we are setting the everyone bit anyway, no need to do expensive operations
			$_chmod_info['process'] = false;
		}
	}

	// We are not able to determine or change something
	if (!$_chmod_info['process'])
	{
			$php = 'other';
	}

	switch ($php)
	{
		case 'owner':
			try
			{
				mx_chmod($files, $perms, $recursive, $force_chmod_link);
				clearstatcache();
				if ($this->is_readable($files) && $this->is_writable($files))
				{
					break;
				}
			}
			catch (filesystem_exception $e)
			{
				// Do nothing
			}
		case 'group':
			try
			{
				mx_chmod($files, $perms, $recursive, $force_chmod_link);
				clearstatcache();
				if ((!($perms & self::CHMOD_READ) || $this->is_readable($files, $recursive)) && (!($perms & self::CHMOD_WRITE) || $this->is_writable($files, $recursive)))
				{
					break;
				}
			}
			catch (filesystem_exception $e)
			{
				// Do nothing
			}
		case 'other':
		default:
			mx_chmod($files, $perms, $recursive, $force_chmod_link);
		break;
	}
}


/**
* Meta refresh assignment
*/
function meta_refresh($time, $url)
{
	global $template;

	$url = redirect($url, true);
	// For XHTML compatibility we change back & to &amp;
	$url = str_replace('&', '&amp;', $url);

	$template->assign_vars(array('META' => '<meta http-equiv="refresh" content="' . $time . ';url=' . $url . '" />'));

	return $url;
}

/**
* Outputs correct status line header.
*
* Depending on php sapi one of the two following forms is used:
*
* Status: 404 Not Found
*
* HTTP/1.x 404 Not Found
*
* HTTP version is taken from HTTP_VERSION environment variable,
* and defaults to 1.0.
*
* Sample usage:
*
* send_status_line(404, 'Not Found');
*
* @param int $code HTTP status code
* @param string $message Message for the status code
* @return void
*/
function send_status_line($code, $message)
{
	if (substr(strtolower(@php_sapi_name()), 0, 3) === 'cgi')
	{
		// in theory, we shouldn't need that due to php doing it. Reality offers a differing opinion, though
		@header("Status: $code $message", true, $code);
	}
	else
	{
		if (!empty($_SERVER['SERVER_PROTOCOL']))
		{
			$version = $_SERVER['SERVER_PROTOCOL'];
		}
		else
		{
			$version = 'HTTP/1.0';
		}
		@header("$version $code $message", true, $code);
	}
}

/**
* Setup basic lang
*/
function setup_basic_lang()
{
	global $cache, $config, $lang;

	if (empty($lang))
	{
		if(!file_exists(PHPBB_ROOT_PATH . 'language/lang_' . $config['default_lang'] . '/lang_main.' . PHP_EXT))
		{
			$config['default_lang'] = 'english';
		}

		$lang_files = array(
			'lang_main',
			'lang_bbcb_mg',
			'lang_main_upi2db',
			'lang_news',
			'lang_main_attach',
			'lang_main_cback_ctracker',
		);

		if (!empty($config['plugins']['cash']['enabled']) && defined('IN_CASHMOD'))
		{
			$lang_files = array_merge($lang_files, array('lang_cash'));
		}

		$lang_extend_admin = false;
		if (defined('IN_ADMIN'))
		{
			$lang_extend_admin = true;
			$lang_files_admin = array(
				'lang_admin',
				'lang_admin_cback_ctracker',
				'lang_admin_upi2db',
				'lang_admin_attach',
				'lang_jr_admin',
			);
			$lang_files = array_merge($lang_files, $lang_files_admin);
		}

		if (defined('IN_CMS'))
		{
			$lang_files_cms = array(
				'lang_admin',
				'lang_cms',
				'lang_blocks',
				'lang_permissions',
			);
			$lang_files = array_merge($lang_files, $lang_files_cms);
		}

		$lang_files = array_merge($lang_files, $cache->obtain_lang_files());
		// Make sure we keep these files as last inclusion... to be sure they override what is needed to be overridden!!!
		$lang_files = array_merge($lang_files, array('lang_dyn_menu', 'lang_main_settings', 'lang_user_created'));

		foreach ($lang_files as $lang_file)
		{
			// Do not suppress error if in DEBUG_EXTRA mode
			$include_result = (defined('DEBUG_EXTRA')) ? (include(PHPBB_ROOT_PATH . 'language/lang_' . $config['default_lang'] . '/' . $lang_file . '.' . PHP_EXT)) : (@include(PHPBB_ROOT_PATH . 'language/lang_' . $config['default_lang'] . '/' . $lang_file . '.' . PHP_EXT));

			if ($include_result === false)
			{
				die('Language file ' . PHPBB_ROOT_PATH . 'language/lang_' . $config['default_lang'] . '/' . $lang_file . '.' . PHP_EXT . ' couldn\'t be opened.');
			}
		}
	}
	return true;
}

/**
* Setup extra lang
*/
function setup_extra_lang($lang_files_array, $lang_base_path = '', $lang_override = '')
{
	global $config, $lang, $images, $faq, $mtnc;

	if (empty($lang_files_array))
	{
		return false;
	}

	if (!is_array($lang_files_array))
	{
		$lang_files_array = array($lang_files_array);
	}

	$lang_base_path = (empty($lang_base_path) ? (PHPBB_ROOT_PATH . 'language/') : $lang_base_path);
	for ($i = 0; $i < sizeof($lang_files_array); $i++)
	{
		$lang_override = !empty($lang_override) ? $lang_override : $config['default_lang'];
		$user_lang_file = $lang_base_path . 'lang_' . $lang_override . '/' . $lang_files_array[$i] . '.' . PHP_EXT;
		$default_lang_file = $lang_base_path . 'lang_english/' . $lang_files_array[$i] . '.' . PHP_EXT;
		if (@file_exists($user_lang_file))
		{
			@include($user_lang_file);
		}
		elseif (@file_exists($default_lang_file))
		{
			@include($default_lang_file);
		}
	}

	return true;
}

/**
* Merge $lang with $user->lang
*/
function merge_user_lang()
{
	global $user, $lang;

	$user->lang = array_merge($user->lang, $lang);

	return true;
}

/**
* Stopwords, Synonyms, INIT
*/
function stopwords_synonyms_init()
{
	global $config, $stopwords_array, $synonyms_array;

	if (empty($stopwords_array))
	{
		$stopwords_array = @file(PHPBB_ROOT_PATH . 'language/lang_' . $config['default_lang'] . '/search_stopwords.txt');
	}

	if (empty($synonyms_array))
	{
		$synonyms_array = @file(PHPBB_ROOT_PATH . 'language/lang_' . $config['default_lang'] . '/search_synonyms.txt');
	}
}

/**
 * Enter description here...
 *
* @return unknown
 */
function confirm_backend()
{
	return 'phpbb2';
}

// Server functions (building urls, redirecting...)

/**
* Append session id to url.
* This function supports hooks.
*
* @param string $url The url the session id needs to be appended to (can have params)
* @param mixed $params String or array of additional url parameters
* @param bool $is_amp Is url using &amp; (true) or & (false)
* @param string $session_id Possibility to use a custom session id instead of the global one
* @param bool $is_route Is url generated by a route.
*
* @return string The corrected url.
*
* Examples:
* <code>
* append_sid("{$phpbb_root_path}viewtopic.$phpEx?t=1&amp;f=2");
* append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=1&amp;f=2');
* append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=1&f=2', false);
* append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('t' => 1, 'f' => 2));
* </code>
*
*/
function append_sid($url, $params = false, $is_amp = true, $session_id = false, $is_route = false)
{
	global $SID, $user, $_EXTRA_URL;
	
	//
	// Obtain number of new private messages
	// if user is logged in
	if(isset($user) || is_object($user))
	{
		$SID = $user->session_id;
	}	
	
	if ($params === '' || (is_array($params) && empty($params)))
	{
		// Do not append the ? if the param-list is empty anyway.
		$params = false;
	}

	// Update the root path with the correct relative web path
	if ($is_route === true)
	{
		$url = generate_board_url();
	}

	$append_sid_overwrite = false;

	if ($append_sid_overwrite)
	{
		return $append_sid_overwrite;
	}

	$params_is_array = is_array($params);	//array ( [f] => 1 [t] => 3 [start] => 0 [quickmod] => 1 [redirect] => '')

	// Get anchor
	$anchor = '';
	if (strpos($url, '#') !== false)
	{
		list($url, $anchor) = explode('#', $url, 2);
		$anchor = '#' . $anchor;
	}
	else if (!$params_is_array && strpos($params, '#') !== false)
	{
		list($params, $anchor) = explode('#', $params, 2);
		$anchor = '#' . $anchor;
	}

	// Handle really simple cases quickly
	if ($SID == '' && $session_id === false && empty($_EXTRA_URL) && !$params_is_array && !$anchor)
	{
		if ($params === false)
		{
			return $url;
		}

		$url_delim = (strpos($url, '?') === false) ? '?' : (($is_amp) ? '&amp;' : '&');
		return $url . ($params !== false ? $url_delim. $params : '');
	}

	// Assign sid if session id is not specified
	if ($session_id === false)
	{
		$session_id = $SID;
	}

	$amp_delim = ($is_amp) ? '&amp;' : '&';
	$url_delim = (strpos($url, '?') === false) ? '?' : $amp_delim;

	// Appending custom url parameter?
	$append_url = (!empty($_EXTRA_URL)) ? implode($amp_delim, $_EXTRA_URL) : '';

	// Use the short variant if possible ;)
	if ($params === false)
	{
		// Append session id
		if (!$session_id)
		{
			return $url . (($append_url) ? $url_delim . $append_url : '') . $anchor;
		}
		else
		{
			return $url . (($append_url) ? $url_delim . $append_url . $amp_delim : $url_delim) . 'sid=' . $session_id . $anchor;
		}
	}

	// Build string if parameters are specified as array
	if (is_array($params))
	{
		$output = array();

		foreach ($params as $key => $item)
		{
			if ($item === NULL)
			{
				continue;
			}

			if ($key == '#')
			{
				$anchor = '#' . $item;
				continue;
			}

			$output[] = $key . '=' . $item;
		}

		$params = implode($amp_delim, $output);
	}

	// Append session id and parameters (even if they are empty)
	// If parameters are empty, the developer can still append his/her parameters without caring about the delimiter
	$url = $url . (($append_url) ? $url_delim . $append_url . $amp_delim : $url_delim) . $params . ((!$session_id) ? '' : $amp_delim . 'sid=' . $session_id) . $anchor;

	if (!empty($SID) && !preg_match('#sid=#', $url))
	{
		$url .= $SID;
	}
}

/**
* Returns url from the session/current page with an re-appended SID with optionally stripping vars from the url
*/
function build_url($strip_vars = false)
{
	global $user, $phpbb_root_path;

	// Append SID
	$redirect = append_sid('index.php', false, false);

	// Add delimiter if not there...
	if (strpos($redirect, '?') === false)
	{
		$redirect .= '?';
	}

	// Strip vars...
	if ($strip_vars !== false && strpos($redirect, '?') !== false)
	{
		if (!is_array($strip_vars))
		{
			$strip_vars = array($strip_vars);
		}

		$query = $_query = array();

		$args = substr($redirect, strpos($redirect, '?') + 1);
		$args = ($args) ? explode('&', $args) : array();
		$redirect = substr($redirect, 0, strpos($redirect, '?'));

		foreach ($args as $argument)
		{
			$arguments = explode('=', $argument);
			$key = $arguments[0];
			unset($arguments[0]);

			if ($key === '')
			{
				continue;
			}

			$query[$key] = implode('=', $arguments);
		}

		// Strip the vars off
		foreach ($strip_vars as $strip)
		{
			if (isset($query[$strip]))
			{
				unset($query[$strip]);
			}
		}

		// Glue the remaining parts together... already urlencoded
		foreach ($query as $key => $value)
		{
			$_query[] = $key . '=' . $value;
		}
		$query = implode('&', $_query);

		$redirect .= ($query) ? '?' . $query : '';
	}

	// We need to be cautious here.
	// On some situations, the redirect path is an absolute URL, sometimes a relative path
	// For a relative path, let's prefix it with $phpbb_root_path to point to the correct location,
	// else we use the URL directly.
	$url_parts = @parse_url($redirect);

	// URL
	if ($url_parts !== false && !empty($url_parts['scheme']) && !empty($url_parts['host']))
	{
		return str_replace('&', '&amp;', $redirect);
	}

	return $phpbb_root_path . str_replace('&', '&amp;', $redirect);
}

/**
* Generate board url (example: http://www.example.com/phpBB)
*
* @param bool $without_script_path if set to true the script path gets not appended (example: http://www.example.com)
*
* @return string the generated board url
*/
function generate_board_url($without_script_path = false)
{
	global $board_config, $userdata, $user;
	
	$server_name = !empty($board_config['server_name']) ? preg_replace('/^\/?(.*?)\/?$/', "\\1", trim($board_config['server_name'])) : 'localhost';
	$server_protocol = ($board_config['cookie_secure'] ) ? 'https://' : 'http://';
	$server_port = (($board_config['server_port']) && ($board_config['server_port'] <> 80)) ? ':' . trim($board_config['server_port']) . '/' : ((!empty($_SERVER['SERVER_PORT'])) ? (int) $_SERVER['SERVER_PORT'] : (int) getenv('SERVER_PORT'));
	$script_name_phpbb = preg_replace('/^\/?(.*?)\/?$/', "\\1", trim($board_config['script_path'])) . '/';		
	$server_url = $server_protocol . str_replace("//", "/", $server_name . $server_port . $server_name . '/'); //On some server the slash is not added and this trick will fix it	

	// Forcing server vars is the only way to specify/override the protocol
	if (!$server_name)
	{
		$server_protocol = ($board_config['server_protocol']) ? $board_config['server_protocol'] : (($board_config['cookie_secure']) ? 'https://' : 'http://');
		$server_name = $board_config['server_name'];
		$server_port = (int) $board_config['server_port'];
		$script_path = $board_config['script_path'];

		$url = $server_protocol . $server_name;
		$cookie_secure = $board_config['cookie_secure'];
	}
	else
	{
		// Do not rely on cookie_secure, users seem to think that it means a secured cookie instead of an encrypted connection
		$cookie_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 1 : 0;
		$url = (($cookie_secure) ? 'https://' : 'http://') . $server_name;

		$script_path = $url;
	}

	if ($server_port && (($cookie_secure && $server_port <> 443) || (!$cookie_secure && $server_port <> 80)))
	{
		// HTTP HOST can carry a port number (we fetch $user->host, but for old versions this may be true)
		if (strpos($server_name, ':') === false)
		{
			$url .= ':' . $server_port;
		}
	}

	if (!$without_script_path)
	{
		$url .= $script_path;
	}

	// Strip / from the end
	if (substr($url, -1, 1) == '/')
	{
		$url = substr($url, 0, -1);
	}

	return $url;
}

/*
* Creates a full server path
* for icy templates
*/
function create_server_url($without_script_path = false)
{
	// usage: $server_url = create_server_url();
	global $board_config;

	$server_protocol = ($board_config['cookie_secure']) ? 'https://' : 'http://';
	$server_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($board_config['server_name']));
	$server_port = ($board_config['server_port'] <> 80) ? ':' . trim($board_config['server_port']) : '';
	$script_name = preg_replace('/^\/?(.*?)\/?$/', '\1', trim($board_config['script_path']));
	$script_name = ($script_name == '') ? '' : '/' . $script_name;
	$server_url = $server_protocol . $server_name . $server_port . ($without_script_path ? '' : $script_name);
	while(substr($server_url, -1, 1) == '/')
	{
		$server_url = substr($server_url, 0, -1);
	}
	$server_url = $server_url . '/';

	return $server_url;
}

/**
* Get username details for placing into templates.
*
* @param string $mode Can be profile (for getting an url to the profile), username (for obtaining the username), colour (for obtaining the user colour) or full (for obtaining a html string representing a coloured link to the users profile).
* @param int $user_id The users id
* @param string $username The users name
* @param string $username_colour The users colour
* @param string $guest_username optional parameter to specify the guest username. It will be used in favor of the GUEST language variable then.
* @param string $custom_profile_url optional parameter to specify a profile url. The user id get appended to this url as &amp;u={user_id}
*
* @return string A string consisting of what is wanted based on $mode.
*/
function get_username_string($mode, $user_id, $username = false, $user_colour = false, $guest_username = false, $custom_profile_url = false)
{
	global $user, $lang, $phpEx;

	$lang['Guest'] = !$guest_username ? $lang['Guest'] : $guest_username;

	$this_userdata = get_userdata($user_id, false);
	$topic_poster_style = 'style="font-weight : bold;"';

	$username = ($username) ? $username : $this_userdata['username'];
	
	if ($this_userdata['user_level'] == ADMIN)
	{
		$user_colour = ($user_colour) ? $user_colour : $user->theme['fontcolor3'];		
		$user_style = 'style="color:#' . $user_colour . '; font-weight : bold;"';
	}
	else if ($this_userdata['user_level'] == MOD)
	{
		$user_colour = ($user_colour) ? $user_colour : $user->theme['fontcolor2'];
		$user_style = 'style="color:#' . $user_colour . '; font-weight : bold;"';
	}
	else
	{
		$user_colour = ($user_colour) ? $user_colour : $user->theme['fontcolor1'];
		$user_style = 'style="color:#' . $user_colour . '; font-weight : bold;"';		
	}
	// print_r(substr($user_colour, 0, 3) . substr($user_colour, 3, 2));
	// Only show the link if not anonymous
	if ($user_id != ANONYMOUS)
	{
		$profile_url = append_sid(PHPBB_URL . "profile.$phpEx?mode=viewprofile&amp;u=" . (int) $user_id);
		$full_url = '<a href="' . $profile_url . '"><span ' . $user_style . '>' . $username . '</span></a>';
	}
	else
	{
		$profile_url = $lang['Guest'];
		$full_url = $lang['Guest'];
	}

	switch ($mode)
	{
		case 'profile':
			return $profile_url;
		break;

		case 'username':
			return $username;
		break;

		case 'colour':
			return $user_colour;
		break;

		case 'full':
		default:
			return $full_url;
		break;
	}
}

/**
* Get user avatar
*
* @param array $user_row Row from the users table
* @param string $alt Optional language string for alt tag within image, can be a language key or text
* @param bool $ignore_config Ignores the config-setting, to be still able to view the avatar in the UCP
* @param bool $lazy If true, will be lazy loaded (requires JS)
*
* @return string Avatar html
*/
function phpbb_get_user_avatar($user_row, $alt = 'USER_AVATAR', $ignore_config = false, $lazy = false)
{
	return phpbb_get_avatar($user_row, $alt, $ignore_config, $lazy);
}

/**
* Get group avatar
*
* @param array $group_row Row from the groups table
* @param string $alt Optional language string for alt tag within image, can be a language key or text
* @param bool $ignore_config Ignores the config-setting, to be still able to view the avatar in the UCP
* @param bool $lazy If true, will be lazy loaded (requires JS)
*
* @return string Avatar html
*/
function phpbb_get_group_avatar($user_row, $alt = 'GROUP_AVATAR', $ignore_config = false, $lazy = false)
{
	return phpbb_get_avatar($user_row, $alt, $ignore_config, $lazy);
}

/**
* Build gravatar URL for output on page
*
* @param array $row User data or group data that has been cleaned with
*        \phpbb\avatar\manager::clean_row
* @return string Gravatar URL
*/
function get_gravatar_url($row)
{
	$url = '//secure.gravatar.com/avatar/';
	
	$url .=  md5(strtolower(trim($row['avatar'])));

	if ($row['avatar_width'] || $row['avatar_height'])
	{
		$url .= '?s=' . max($row['avatar_width'], $row['avatar_height']);
	}
	
	return $url;
}

/**
* Get avatar
*
* @param array $row Row cleaned by \phpbb\avatar\manager::clean_row
* @param string $alt Optional language string for alt tag within image, can be a language key or text
* @param bool $ignore_config Ignores the config-setting, to be still able to view the avatar in the UCP
* @param bool $lazy If true, will be lazy loaded (requires JS)
*
* @return string Avatar html
*/
function phpbb_get_avatar($row, $alt, $ignore_config = false, $lazy = false)
{
	global $user, $board_config, $cache, $phpbb_root_path, $phpEx;

	if (!$user->optionget('viewavatars') && !$ignore_config)
	{
		return '';
	}
	
	$row = array(
		'avatar' 		=> isset($row['avatar']) ? $row['avatar'] : $row['user_avatar'],
		'avatar_type' 	=> isset($row['avatar_type']) ? $row['avatar_type'] : $row['user_avatar_type'],
		'avatar_width' 	=> isset($row['avatar_width']) ? $row['avatar_width'] : (isset($row['user_avatar_width']) ? $row['user_avatar_width'] : '120'),
		'avatar_height' => isset($row['avatar_height']) ? $row['avatar_height'] : (isset($row['user_avatar_height']) ? $row['user_avatar_height'] : '120'),
	);
	
	$avatar_data = array(
		'src' => $row['avatar'],
		'width' => $row['avatar_width'],
		'height' => $row['avatar_height'],
	);

	
	$driver = $row['avatar_type'];
	$html = '';

	if ($driver)
	{
		$html = '<img src="' . get_gravatar_url($row) . '" ' .
			($row['avatar_width'] ? ('width="' . $row['avatar_width'] . '" ') : '') .
			($row['avatar_height'] ? ('height="' . $row['avatar_height'] . '" ') : '') .
			'alt="' . ((!empty($lang[$alt])) ? $lang[$alt] : $alt) . '" />';
			
		if (!empty($html))
		{
			return $html;
		}

		$root_path = generate_board_url();

		$avatar_data = array(
			'src' => $root_path . $board_config['avatar_gallery_path'] . '/' . $row['avatar'],
			'width' => $row['avatar_width'],
			'height' => $row['avatar_height'],
		);
	}
	else
	{
		$avatar_data['src'] = '';
	}

	if (!empty($avatar_data['src']))
	{
		if ($lazy)
		{
			// Determine board url - we may need it later
			$board_url = generate_board_url() . '/';
			// This path is sent with the base template paths in the assign_vars()
			// call below. We need to correct it in case we are accessing from a
			// controller because the web paths will be incorrect otherwise.

			$web_path = $board_url;

			if (is_dir($phpbb_root_path . $user->template_path . $user->template_name . '/theme/images/'))
			{			
				$theme_images = "{$web_path}{$user->template_path}" . rawurlencode($user->template_name) . '/theme/images';
			}
			elseif (is_dir($phpbb_root_path . $user->template_path . $user->template_name . '/images/'))
			{			
				$theme_images = "{$web_path}{$user->template_path}" . rawurlencode($user->template_name . '/images');
			}			
			$src = 'src="' . $theme_images . '/no_avatar.gif" data-src="' . $avatar_data['src'] . '"';
		}
		else
		{
			$src = 'src="' . $avatar_data['src'] . '"';
		}

		$html = '<img class="avatar" ' . $src . ' ' .
			($avatar_data['width'] ? ('width="' . $avatar_data['width'] . '" ') : '') .
			($avatar_data['height'] ? ('height="' . $avatar_data['height'] . '" ') : '') .
			'alt="' . ((!empty($user->lang[$alt])) ? $user->lang[$alt] : $alt) . '" />';
	}
	return $html;
}

/**
* Get user rank title and image
*
* @param array $user_data the current stored users data
* @param int $user_posts the users number of posts
*
* @return array An associative array containing the rank title (title), the rank image as full img tag (img) and the rank image source (img_src)
*
* Note: since we do not want to break backwards-compatibility, this function will only properly assign ranks to guests if you call it for them with user_posts == false
*/
function phpbb_get_user_rank($user_data, $user_posts, &$rank_title = null, &$rank_img = null, &$rank_img_src = null)
{
	global $ranks, $config, $phpbb_root_path;

	$user_rank_data = array(
		'title'		=> $rank_title ? $rank_title : null,
		'img'		=> $rank_img ? $rank_img : null,
		'img_src'	=> $rank_img_src ? $rank_img_src : null,
	);	
	
	if (empty($ranks))
	{
		global $cache;
		$ranks = $cache->obtain_ranks();
	}

	if (!empty($user_data))
	{
		$user_rank_data['title'] = (isset($ranks['special'][$user_data['user_rank']]['rank_title'])) ? $ranks['special'][$user_data['user_rank']]['rank_title'] : '';
		$user_rank_data['img_src'] = (!empty($ranks['special'][$user_data['user_rank']]['rank_image'])) ? $phpbb_root_path . $config['ranks_path'] . '/' . $ranks['special'][$user_data['user_rank']]['rank_image'] : '';
		$user_rank_data['img'] = (!empty($ranks['special'][$user_data['user_rank']]['rank_image'])) ? '<img src="' . $user_rank_data['img_src'] . '" alt="' . $ranks['special'][$user_data['user_rank']]['rank_title'] . '" title="' . $ranks['special'][$user_data['user_rank']]['rank_title'] . '" />' : '';
	
	}
	else if ($user_posts !== false)
	{
		if (!empty($ranks['normal']))
		{
			foreach ($ranks['normal'] as $rank)
			{
				if ($user_posts >= $rank['rank_min'])
				{
					$user_rank_data['title'] = $rank['rank_title'];
					$user_rank_data['img'] = (!empty($rank['rank_image'])) ? '<img src="' . $phpbb_root_path . $config['ranks_path'] . '/' . $rank['rank_image'] . '" alt="' . $rank['rank_title'] . '" title="' . $rank['rank_title'] . '" />' : '';
					$user_rank_data['img_src'] = (!empty($rank['rank_image'])) ? $phpbb_root_path . $config['ranks_path'] . '/' . $rank['rank_image'] : '';
					break;
				}
			}
		}
	}
	
	return $user_rank_data;		
}

/*
* Gets all social networks and instant messaging (SN/IM) fields feeded only from profile table (doesn't get chat id...)
* This function should simplify adding/removing SN/IM fields to user profile
*/
function get_user_sn_im_array()
{
	$user_sn_im_array = array(
		'500px' => array('field' => 'user_500px', 'lang' => '500PX', 'icon_tpl' => '', 'icon_tpl_vt' => '', 'url' => '{REF}', 'alt_name' => '500px', 'form' => '500px'),
		'aim' => array('field' => 'user_aim', 'lang' => 'AIM', 'icon_tpl' => 'icon_aim', 'icon_tpl_vt' => 'icon_aim2', 'url' => 'aim:goim?screenname={REF}&amp;message=Hello', 'alt_name' => 'aim', 'form' => 'aim'),
		'facebook' => array('field' => 'user_facebook', 'lang' => 'FACEBOOK', 'icon_tpl' => '', 'icon_tpl_vt' => '', 'url' => '{REF}', 'alt_name' => 'facebook', 'form' => 'facebook'),
		'flickr' => array('field' => 'user_flickr', 'lang' => 'FLICKR', 'icon_tpl' => '', 'icon_tpl_vt' => '', 'url' => '{REF}', 'alt_name' => 'flickr', 'form' => 'flickr'),
		'github' => array('field' => 'user_github', 'lang' => 'GITHUB', 'icon_tpl' => '', 'icon_tpl_vt' => '', 'url' => '{REF}', 'alt_name' => 'github', 'form' => 'github'),
		'googleplus' => array('field' => 'user_googleplus', 'lang' => 'GOOGLEPLUS', 'icon_tpl' => '', 'icon_tpl_vt' => '', 'url' => '{REF}', 'alt_name' => 'googleplus', 'form' => 'googleplus'),
		'icq' => array('field' => 'user_icq', 'lang' => 'ICQ', 'icon_tpl' => 'icon_icq', 'icon_tpl_vt' => 'icon_icq2', 'url' => 'http://www.icq.com/people/webmsg.php?to={REF}', 'alt_name' => 'icq', 'form' => 'icq'),
		'instagram' => array('field' => 'user_instagram', 'lang' => 'INSTAGRAM', 'icon_tpl' => '', 'icon_tpl_vt' => '', 'url' => '{REF}', 'alt_name' => 'instagram', 'form' => 'instagram'),
		'jabber' => array('field' => 'user_jabber', 'lang' => 'JABBER', 'icon_tpl' => '', 'icon_tpl_vt' => '', 'url' => '{REF}', 'alt_name' => 'jabber', 'form' => 'jabber'),
		'linkedin' => array('field' => 'user_linkedin', 'lang' => 'LINKEDIN', 'icon_tpl' => '', 'icon_tpl_vt' => '', 'url' => '{REF}', 'alt_name' => 'linkedin', 'form' => 'linkedin'),
		'msn' => array('field' => 'user_msnm', 'lang' => 'MSNM', 'icon_tpl' => 'icon_msnm', 'icon_tpl_vt' => 'icon_msnm2', 'url' => 'http://spaces.live.com/{REF}', 'alt_name' => 'msnm', 'form' => 'msn'),
		'pinterest' => array('field' => 'user_pinterest', 'lang' => 'PINTEREST', 'icon_tpl' => '', 'icon_tpl_vt' => '', 'url' => '{REF}', 'alt_name' => 'pinterest', 'form' => 'pinterest'),
		'skype' => array('field' => 'user_skype', 'lang' => 'SKYPE', 'icon_tpl' => 'icon_skype', 'icon_tpl_vt' => 'icon_skype2', 'url' => 'callto://{REF}', 'alt_name' => 'skype', 'form' => 'skype'),
		'twitter' => array('field' => 'user_twitter', 'lang' => 'TWITTER', 'icon_tpl' => '', 'icon_tpl_vt' => '', 'url' => '{REF}', 'alt_name' => 'twitter', 'form' => 'twitter'),
		'vimeo' => array('field' => 'user_vimeo', 'lang' => 'VIMEO', 'icon_tpl' => '', 'icon_tpl_vt' => '', 'url' => '{REF}', 'alt_name' => 'vimeo', 'form' => 'vimeo'),
		'yahoo' => array('field' => 'user_yim', 'lang' => 'YIM', 'icon_tpl' => 'icon_yim', 'icon_tpl_vt' => 'icon_yim2', 'url' => 'http://edit.yahoo.com/config/send_webmesg?.target={REF}&amp;.src=pg', 'alt_name' => 'yim', 'form' => 'yim'),
		'youtube' => array('field' => 'user_youtube', 'lang' => 'YOUTUBE', 'icon_tpl' => '', 'icon_tpl_vt' => '', 'url' => '{REF}', 'alt_name' => 'youtube', 'form' => 'youtube'),
	);

	return $user_sn_im_array;
}

/*
* This function will build a complete IM link with image and lang
*/
function build_im_link($im_type, $user_data, $im_icon_type = false, $im_img = false, $im_url = false, $im_status = false, $im_lang = false)
{
	global $config, $user, $lang, $images;

	$available_im = get_user_sn_im_array();
	$extra_im = array(
		'chat' => array('field' => 'user_id', 'lang' => 'AJAX_SHOUTBOX_PVT_LINK', 'icon_tpl' => 'icon_im_chat', 'icon_tpl_vt' => 'icon_im_chat', 'url' => '{REF}')
	);
	$available_im = array_merge($available_im, $extra_im);

	// Default values
	$im_icon = '';
	$im_icon_append = '';
	if (!empty($user_data[$available_im[$im_type]['field']]))
	{
		$im_id = $user_data[$available_im[$im_type]['field']];
		$im_ref = $im_id;
	}
	else
	{
		return '';
	}

	if (!empty($im_status) && in_array($im_type, array('chat')) && in_array($im_status, array('online', 'offline', 'hidden')))
	{
		$im_icon_append = '_' . $im_status;
	}

	if (!empty($available_im[$im_type]))
	{
		if (!empty($im_icon_type) && in_array($im_icon_type, array('icon', 'icon_tpl', 'icon_tpl_vt')))
		{
			if ($im_icon_type == 'icon')
			{
				$im_icon = $images['icon_im_' . $im_type . $im_icon_append];
			}
			else
			{
				$im_icon = $images[$available_im[$im_type][$im_icon_type]];
			}
		}

		$im_ref = str_replace('{REF}', $im_ref, $available_im[$im_type]['url']);
		if ($im_type == 'chat')
		{
			// JHL: No chat icon if the user is anonymous, or the profiled user is offline
			if (empty($user->data['session_logged_in']) || empty($user_data['user_session_time']) || ($user_data['user_session_time'] < (time() - $config['online_time'])))
			{
				return '';
			}

			$ajax_chat_page = !empty($config['ajax_chat_link_type']) ? CMS_PAGE_AJAX_CHAT : CMS_PAGE_AJAX_SHOUTBOX;
			$ajax_chat_room = 'chat_room=' . (min($user->data['user_id'], $user_data['user_id']) . '|' . max($user->data['user_id'], $user_data['user_id']));
			$ajax_chat_link = append_sid($ajax_chat_page . '?' . $ajax_chat_room);

			$im_ref = !empty($config['ajax_chat_link_type']) ? ($ajax_chat_link . '" target="_chat') : ('#" onclick="window.open(\'' . $ajax_chat_link . '\', \'_chat\', \'width=720,height=600,resizable=yes\'); return false;');
		}

		$im_img = (!empty($im_img) && !empty($im_icon)) ? $im_icon : false;
		$im_lang = !empty($im_lang) ? $im_lang : (!empty($available_im[$im_type]['lang']) ? $lang[$available_im[$im_type]['lang']] : '');
	}

	$link_title = ($im_type == 'chat') ? '' : (' - ' . $im_id);
	$link_title = $im_lang . $link_title;
	$link_content = !empty($im_img) ? ('<img src="' . $im_img . '" alt="' . $im_lang . '"' . (empty($im_url) ? '' : (' title="' . $im_id . '"')) . ' />') : $im_lang;
	$im_link = !empty($im_url) ? $im_ref : ('<a href="' . $im_ref . '" title="' . $link_title . '">' . $link_content . '</a>');

	return $im_link;
}

/**
* Generate sort selection fields
*/
function gen_sort_selects(&$limit_days, &$sort_by_text, &$sort_days, &$sort_key, &$sort_dir, &$s_limit_days, &$s_sort_key, &$s_sort_dir, &$u_sort_param, $def_st = false, $def_sk = false, $def_sd = false)
{
	global $user, $phpbb_dispatcher;

	$sort_dir_text = array('a' => $user->lang('Ascending'), 'd' => $user->lang('Descending'));

	$sorts = array(
		'st'	=> array(
			'key'		=> 'sort_days',
			'default'	=> $def_st,
			'options'	=> $limit_days,
			'output'	=> &$s_limit_days,
		),

		'sk'	=> array(
			'key'		=> 'sort_key',
			'default'	=> $def_sk,
			'options'	=> $sort_by_text,
			'output'	=> &$s_sort_key,
		),

		'sd'	=> array(
			'key'		=> 'sort_dir',
			'default'	=> $def_sd,
			'options'	=> $sort_dir_text,
			'output'	=> &$s_sort_dir,
		),
	);
	$u_sort_param  = '';

	foreach ($sorts as $name => $sort_ary)
	{
		$key = $sort_ary['key'];
		$selected = ${$sort_ary['key']};

		// Check if the key is selectable. If not, we reset to the default or first key found.
		// This ensures the values are always valid. We also set $sort_dir/sort_key/etc. to the
		// correct value, else the protection is void. ;)
		if (!isset($sort_ary['options'][$selected]))
		{
			if ($sort_ary['default'] !== false)
			{
				$selected = ${$key} = $sort_ary['default'];
			}
			else
			{
				@reset($sort_ary['options']);
				$selected = ${$key} = key($sort_ary['options']);
			}
		}

		$sort_ary['output'] = '<select name="' . $name . '" id="' . $name . '">';
		foreach ($sort_ary['options'] as $option => $text)
		{
			$sort_ary['output'] .= '<option value="' . $option . '"' . (($selected == $option) ? ' selected="selected"' : '') . '>' . $text . '</option>';
		}
		$sort_ary['output'] .= '</select>';

		$u_sort_param .= ($selected !== $sort_ary['default']) ? ((strlen($u_sort_param)) ? '&amp;' : '') . "{$name}={$selected}" : '';
	}

	return;
}
 
/**
* Generate Jumpbox
*/
function make_jumpbox($action, $match_forum_id = 0)
{
	$list = array();
	return make_jumpbox_ref($action, $match_forum_id, $list);
}

function make_jumpbox_ref($action, $match_forum_id, &$forums_list)
{
	global $template, $user, $lang, $db, $nav_links, $phpEx, $SID;

	$sql = "SELECT c.cat_id, c.cat_title, c.cat_order
			FROM " . CATEGORIES_TABLE . " c, " . FORUMS_TABLE . " f
			WHERE f.cat_id = c.cat_id
			GROUP BY c.cat_id, c.cat_title, c.cat_order
			ORDER BY c.cat_order";
	if (!($result = $db->sql_query($sql)))
	{
		message_die(GENERAL_ERROR, "Couldn't obtain category list.", "", __LINE__, __FILE__, $sql);
	}

	$category_rows = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$category_rows[] = $row;
	}

	if ($total_categories = count($category_rows))
	{
		$sql = "SELECT f.*, f.forum_id, f.forum_name, f.forum_parent, f.cat_id as forum_type, f.forum_id as left_id, f.forum_id as right_id
				FROM " . FORUMS_TABLE . " f
				ORDER BY f.cat_id, f.forum_order";
		if (!($result = $db->sql_query($sql)))
		{
			message_die(GENERAL_ERROR, 'Could not obtain forums information', '', __LINE__, __FILE__, $sql);
		}

		$boxstring = '<select name="' . POST_FORUM_URL . '" onchange="if(this.options[this.selectedIndex].value != -1){ forms[\'jumpbox\'].submit() }"><option value="-1">' . $lang['Select_forum'] . '</option>';
		
		$forum_rows = array();
		$forums_list = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$forum_rows[] = $row;

			// Begin Simple Subforums MOD
			$forums_list[] = $row;
			// End Simple Subforums MOD
		}
		
		$right = $padding = 0;
		$padding_store = array('0' => 0);
		$display_jumpbox = false;
		$iteration = 0;
		$select_all = false;		
		
		if ($total_forums = count($forum_rows))
		{
			for($i = 0; $i < $total_categories; $i++)
			{
				$boxstring_forums = '';
				for($j = 0; $j < $total_forums; $j++)
				{
					$padding = &$j;
					$padding_store[$forum_rows[$j]['forum_parent']] = $padding;					
					if (!$forum_rows[$j]['forum_parent'] && $forum_rows[$j]['cat_id'] == $category_rows[$i]['cat_id'] && $forum_rows[$j]['auth_view'] <= AUTH_REG)
					{
//						if (!$forum_rows[$j]['forum_parent'] && $forum_rows[$j]['cat_id'] == $category_rows[$i]['cat_id'] && $is_auth[$forum_rows[$j]['forum_id']]['auth_view'])
//						{

							// Begin Simple Subforums MOD
							$id = $forum_rows[$j]['forum_id'];
							// End Simple Subforums MOD

						$selected = ($forum_rows[$j]['forum_id'] == $match_forum_id) ? 'selected="selected"' : '';
						$boxstring_forums .=  '<option value="' . $forum_rows[$j]['forum_id'] . '"' . $selected . '>' . $forum_rows[$j]['forum_name'] . '</option>';

						//
						// Add an array to $nav_links for the Mozilla navigation bar.
						// 'chapter' and 'forum' can create multiple items, therefore we are using a nested array.
						//
						$nav_links['chapter forum'][$forum_rows[$j]['forum_id']] = array (
							'url' => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=" . $forum_rows[$j]['forum_id']),
							'title' => $forum_rows[$j]['forum_name']
						);

						// Begin Simple Subforums MOD
						for($k = 0; $k < $total_forums; $k++)
						{
							if ($forum_rows[$k]['forum_parent'] == $id && $forum_rows[$k]['cat_id'] == $category_rows[$i]['cat_id'] && $forum_rows[$k]['auth_view'] <= AUTH_REG)
							{
//								if ($forum_rows[$k]['forum_parent'] == $id && $forum_rows[$k]['cat_id'] == $category_rows[$i]['cat_id'] && $is_auth[$forum_rows[$k]['forum_id']]['auth_view'])
//								{
								$forum_id = $forum_rows[$k]['forum_id'];
								$tpl_ary = array();
								if (!$display_jumpbox)
								{
									$tpl_ary[] = array(
										'FORUM_ID'		=> ($select_all) ? 0 : -1,
										'FORUM_NAME'	=> ($select_all) ? $user->lang('All_Forums') : $user->lang('Select_Forum'),
										'S_FORUM_COUNT'	=> $iteration,
										'LINK'			=> append_sid($action . 'f =' . $forum_id),
									);
									
									$iteration = &$k;
									$display_jumpbox = true;
								}								
								
								$tpl_ary[] = array(
									'FORUM_ID'		=> $forum_rows[$k]['forum_id'],
									'FORUM_NAME'	=> $forum_rows[$k]['forum_name'],
									'SELECTED'		=> ($forum_rows[$k]['forum_id'] == $forum_id) ? ' selected="selected"' : '',
									'S_FORUM_COUNT'	=> $iteration,
									'S_IS_CAT'		=> ($forum_rows[$k]['forum_parent'] == 0) ? true : false,
									'S_IS_LINK'		=> false,
									'S_IS_POST'		=> false,
									'LINK'			=> append_sid($action . 'f =' . $forum_id),
								);								
																
								$selected = ($forum_rows[$k]['forum_id'] == $match_forum_id) ? 'selected="selected"' : '';
								$boxstring_forums .=  '<option value="' . $forum_rows[$k]['forum_id'] . '"' . $selected . '>-- ' . $forum_rows[$k]['forum_name'] . '</option>';
								
								//
								// Add an array to $nav_links for the Mozilla navigation bar.
								// 'chapter' and 'forum' can create multiple items, therefore we are using a nested array.
								//
								$nav_links['chapter forum'][$forum_rows[$k]['forum_id']] = array (
									'url' => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=" . $forum_rows[$k]['forum_id']),
									'title' => $forum_rows[$k]['forum_name']
								);
								
								$template->assign_block_vars_array('jumpbox_forums', $tpl_ary);
								
								unset($tpl_ary);

								for ($i = 0; $i < $padding; $i++)
								{
									$template->assign_block_vars('jumpbox_forums.level', array());
								}
								$iteration++;								
							}
						}
						// End Simple Subforums MOD

					}
				}

				if (!empty($boxstring_forums))
				{
					$boxstring .= '<option value="-1">&nbsp;</option>';
					$boxstring .= '<option value="-1">' . $category_rows[$i]['cat_title'] . '</option>';
					$boxstring .= '<option value="-1">----------------</option>';
					$boxstring .= $boxstring_forums;
				}
			}
		}

		$boxstring .= '</select>';
	}
	else
	{
		$boxstring .= '<select name="' . POST_FORUM_URL . '" onchange="if(this.options[this.selectedIndex].value != -1){ forms[\'jumpbox\'].submit() }"></select>';
	}

// Let the jumpbox work again in sites having additional session id checks.
//	if (!empty($SID))
//	{
		$boxstring .= '<input type="hidden" name="sid" value="' . $user->data['session_id'] . '" />';
//	}

	$template->set_filenames(array(
		'jumpbox' => 'jumpbox.tpl')
	);
	$template->assign_vars(array(
		'L_GO' => $lang['Go'],
		'L_JUMP_TO' => $lang['Jump_to'],
		'L_SELECT_FORUM' => $lang['Select_forum'],

		'S_JUMPBOX_SELECT' => $boxstring,
		'S_DISPLAY_JUMPBOX'	=> $display_jumpbox,		
		'S_JUMPBOX_ACTION' => append_sid($action))
	);
	$template->assign_var_from_handle('JUMPBOX', 'jumpbox');

	return;
}

if (!function_exists('htmlspecialchars_decode'))
{
	/**
	* A wrapper for htmlspecialchars_decode
	* @ignore
	*/
	function htmlspecialchars_decode($string, $quote_style = ENT_COMPAT)
	{
		return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
	}
}

/**
* Generates a text with approx. the specified length which contains the specified words and their context
*
* @param	string	$text	The full text from which context shall be extracted
* @param	string	$words	An array of words which should be contained in the result, has to be a valid part of a PCRE pattern (escape with preg_quote!)
* @param	int		$length	The desired length of the resulting text, however the result might be shorter or longer than this value
*
* @return	string			Context of the specified words separated by "..."
*/
function get_context($text, $words, $length = 400)
{
	// first replace all whitespaces with single spaces
	$text = preg_replace('/ +/', ' ', strtr($text, "\t\n\r\x0C ", '     '));

	// we need to turn the entities back into their original form, to not cut the message in between them
	$entities = array('&lt;', '&gt;', '&#91;', '&#93;', '&#46;', '&#58;', '&#058;');
	$characters = array('<', '>', '[', ']', '.', ':', ':');
	$text = str_replace($entities, $characters, $text);

	$word_indizes = array();
	if (count($words))
	{
		$match = '';
		// find the starting indizes of all words
		foreach ($words as $word)
		{
			if ($word)
			{
				if (preg_match('#(?:[^\w]|^)(' . $word . ')(?:[^\w]|$)#i', $text, $match))
				{
					if (empty($match[1]))
					{
						continue;
					}

					$pos = utf8_strpos($text, $match[1]);
					if ($pos !== false)
					{
						$word_indizes[] = $pos;
					}
				}
			}
		}
		unset($match);

		if (count($word_indizes))
		{
			$word_indizes = array_unique($word_indizes);
			sort($word_indizes);

			$wordnum = count($word_indizes);
			// number of characters on the right and left side of each word
			$sequence_length = (int) ($length / (2 * $wordnum)) - 2;
			$final_text = '';
			$word = $j = 0;
			$final_text_index = -1;

			// cycle through every character in the original text
			for ($i = $word_indizes[$word], $n = utf8_strlen($text); $i < $n; $i++)
			{
				// if the current position is the start of one of the words then append $sequence_length characters to the final text
				if (isset($word_indizes[$word]) && ($i == $word_indizes[$word]))
				{
					if ($final_text_index < $i - $sequence_length - 1)
					{
						$final_text .= '... ' . preg_replace('#^([^ ]*)#', '', utf8_substr($text, $i - $sequence_length, $sequence_length));
					}
					else
					{
						// if the final text is already nearer to the current word than $sequence_length we only append the text
						// from its current index on and distribute the unused length to all other sequenes
						$sequence_length += (int) (($final_text_index - $i + $sequence_length + 1) / (2 * $wordnum));
						$final_text .= utf8_substr($text, $final_text_index + 1, $i - $final_text_index - 1);
					}
					$final_text_index = $i - 1;

					// add the following characters to the final text (see below)
					$word++;
					$j = 1;
				}

				if ($j > 0)
				{
					// add the character to the final text and increment the sequence counter
					$final_text .= utf8_substr($text, $i, 1);
					$final_text_index++;
					$j++;

					// if this is a whitespace then check whether we are done with this sequence
					if (utf8_substr($text, $i, 1) == ' ')
					{
						// only check whether we have to exit the context generation completely if we haven't already reached the end anyway
						if ($i + 4 < $n)
						{
							if (($j > $sequence_length && $word >= $wordnum) || utf8_strlen($final_text) > $length)
							{
								$final_text .= ' ...';
								break;
							}
						}
						else
						{
							// make sure the text really reaches the end
							$j -= 4;
						}

						// stop context generation and wait for the next word
						if ($j > $sequence_length)
						{
							$j = 0;
						}
					}
				}
			}
			return str_replace($characters, $entities, $final_text);
		}
	}

	if (!count($words) || !count($word_indizes))
	{
		return str_replace($characters, $entities, ((utf8_strlen($text) >= $length + 3) ? utf8_substr($text, 0, $length) . '...' : $text));
	}
}

/**
* Cleans a search string by removing single wildcards from it and replacing multiple spaces with a single one.
*
* @param string $search_string The full search string which should be cleaned.
*
* @return string The cleaned search string without any wildcards and multiple spaces.
*/
function phpbb_clean_search_string($search_string)
{
	// This regular expressions matches every single wildcard.
	// That means one after a whitespace or the beginning of the string or one before a whitespace or the end of the string.
	$search_string = preg_replace('#(?<=^|\s)\*+(?=\s|$)#', '', $search_string);
	$search_string = trim($search_string);
	$search_string = preg_replace(array('#\s+#u', '#\*+#u'), array(' ', '*'), $search_string);
	return $search_string;
}

/**
* Decode text whereby text is coming from the db and expected to be pre-parsed content
* We are placing this outside of the message parser because we are often in need of it...
*
* NOTE: special chars are kept encoded
*
* @param string &$message Original message, passed by reference
* @param string $bbcode_uid BBCode UID
* @return null
*/
function decode_message(&$message, $bbcode_uid = '')
{
	global $cache, $phpbb_dispatcher;

	if (preg_match('#^<[rt][ >]#', $message))
	{
		$message = htmlspecialchars($cache->get('text_formatter.utils')->unparse($message), ENT_COMPAT);
	}
	else
	{
		if ($bbcode_uid)
		{
			$match = array('<br />', "[/*:m:$bbcode_uid]", ":u:$bbcode_uid", ":o:$bbcode_uid", ":$bbcode_uid");
			$replace = array("\n", '', '', '', '');
		}
		else
		{
			$match = array('<br />');
			$replace = array("\n");
		}

		$message = str_replace($match, $replace, $message);

		$match = get_preg_expression('bbcode_htm');
		$replace = array('\1', '\1', '\2', '\2', '\1', '', '');

		$message = preg_replace($match, $replace, $message);
	}
}

/**
* Little helper for the build_hidden_fields function
*/
function _build_hidden_fields($key, $value, $specialchar, $stripslashes)
{
	$hidden_fields = '';

	if (!is_array($value))
	{
		$value = ($stripslashes) ? stripslashes($value) : $value;
		$value = ($specialchar) ? htmlspecialchars($value, ENT_COMPAT, 'UTF-8') : $value;

		$hidden_fields .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
	}
	else
	{
		foreach ($value as $_key => $_value)
		{
			$_key = ($stripslashes) ? stripslashes($_key) : $_key;
			$_key = ($specialchar) ? htmlspecialchars($_key, ENT_COMPAT, 'UTF-8') : $_key;

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
		$name = ($specialchar) ? htmlspecialchars($name, ENT_COMPAT, 'UTF-8') : $name;

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
		$key = htmlspecialchars(strtolower(trim(substr($line, 0, $delim_pos))));
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
		else if (($value[0] == "'" && $value[strlen($value) - 1] == "'") || ($value[0] == '"' && $value[strlen($value) - 1] == '"'))
		{
			$value = htmlspecialchars(substr($value, 1, strlen($value) - 2));
		}
		else
		{
			$value = htmlspecialchars($value);
		}

		$parsed_items[$key] = $value;
	}

	if (isset($parsed_items['parent']) && isset($parsed_items['name']) && $parsed_items['parent'] == $parsed_items['name'])
	{
		unset($parsed_items['parent']);
	}

	return $parsed_items;
}

//
// Initialise user settings on page load
function init_userprefs($userdata)
{
	global $user, $cache, $board_config, $theme, $images;
	global $template, $lang, $phpEx, $phpbb_root_path, $db;
	global $nav_links;
	
	if ($userdata['user_id'] != ANONYMOUS)
	{
		if (!empty($userdata['user_lang']))
		{
			$language = phpbb_ltrim(basename(phpbb_rtrim($userdata['user_lang'])), "'");
		}

		if (!empty($userdata['user_dateformat']))
		{
			$board_config['default_dateformat'] = $userdata['user_dateformat'];
		}

		if (isset($userdata['user_timezone']))
		{
			$board_config['board_timezone'] = $userdata['user_timezone'];
		}
	}
	else
	{
		$language = phpbb_ltrim(basename(phpbb_rtrim($board_config['default_lang'])), "'");
	}

	if (!file_exists(@phpbb_realpath($phpbb_root_path . 'language/lang_' . $language . '/lang_main.'.$phpEx)))
	{
		if ($userdata['user_id'] != ANONYMOUS)
		{
			// For logged in users, try the board default language next
			$language = phpbb_ltrim(basename(phpbb_rtrim($board_config['default_lang'])), "'");
		}
		else
		{
			// For guests it means the default language is not present, try english
			// This is a long shot since it means serious errors in the setup to reach here,
			// but english is part of a new install so it's worth us trying
			$language = 'english';
		}

		if (!file_exists(@phpbb_realpath($phpbb_root_path . 'language/lang_' . $language . '/lang_main.'.$phpEx)))
		{
			message_die(CRITICAL_ERROR, 'Could not locate valid language pack in init_userprefs()');
		}
	}

	// If we've had to change the value in any way then let's write it back to the database
	// before we go any further since it means there is something wrong with it
	if ($userdata['user_id'] != ANONYMOUS && $userdata['user_lang'] !== $language)
	{
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_lang = '" . $language . "'
			WHERE user_lang = '" . $userdata['user_lang'] . "'";

		if (!($result = $db->sql_query($sql)))
		{
			message_die(CRITICAL_ERROR, 'Could not update user language info');
		}

		$userdata['user_lang'] = $language;
	}
	elseif ($userdata['user_id'] == ANONYMOUS && $board_config['default_lang'] !== $language)
	{
		$sql = 'UPDATE ' . CONFIG_TABLE . "
			SET config_value = '" . $language . "'
			WHERE config_name = 'default_lang'";

		if (!($result = $db->sql_query($sql)))
		{
			message_die(CRITICAL_ERROR, 'Could not update user language info');
		}
	}

	$board_config['default_lang'] = $language;

	include($phpbb_root_path . 'language/lang_' . $language . '/lang_main.' . $phpEx); // Also include phpBB lang keys
	if ((@include $phpbb_root_path . "language/lang_" . $language . "/common.$phpEx") === false)
	{
		// 
	}		
	if ( defined('IN_ADMIN') )
	{
		if ((@include $phpbb_root_path . "language/lang_" . $language . "/lang_main.$phpEx") === false)
		{
			$board_config['default_lang'] = 'english'; 
		}					
		// Also include phpBB lang keys
		elseif ((@include $phpbb_root_path . "language/lang_" . $board_config['default_lang'] . "/lang_main.$phpEx") === false)
		{
			message_die(CRITICAL_ERROR, 'Language file ' . $phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . ' & Language file ' . $phpbb_root_path . 'language/lang_' . $language . '/lang_main.$phpEx' . ' couldn\'t be opened.');
		}		
	}

	//
	// Set up style
	//
	if (!$board_config['override_user_style'])
	{
		if ($userdata['user_id'] != ANONYMOUS && $userdata['user_style'] > 0)
		{
			if ($theme = setup_style($userdata['user_style']))
			{
				return;
			}
		}
	}

	$theme = setup_style($board_config['default_style']);

	//
	// Mozilla navigation bar
	// Default items that should be valid on all pages.
	// Defined here to correctly assign the Language Variables
	// and be able to change the variables within code.
	//
	$nav_links['top'] = array (
		'url' => append_sid($phpbb_root_path . 'index.' . $phpEx),
		'title' => sprintf($lang['Forum_Index'], $board_config['sitename'])
	);
	$nav_links['search'] = array (
		'url' => append_sid($phpbb_root_path . 'search.' . $phpEx),
		'title' => $lang['Search']
	);
	$nav_links['help'] = array (
		'url' => append_sid($phpbb_root_path . 'faq.' . $phpEx),
		'title' => $lang['FAQ']
	);
	$nav_links['author'] = array (
		'url' => append_sid($phpbb_root_path . 'memberlist.' . $phpEx),
		'title' => $lang['Memberlist']
	);

	return;
}

function setup_style($style)
{
	global $user, $db, $board_config, $template, $images, $phpbb_root_path;

	//
	// Set up style
	//
	if ($user->data['user_id'] == ANONYMOUS && empty($user->data['user_style']))
	{
		$user->data['user_style'] = $board_config['default_style'];
	}
		
	$style_request = request_var('style', 0);
	if ($style_request && (!$board_config['override_user_style'] || !defined('IN_ADMIN')))
	{
		global $SID, $_EXTRA_URL;

		$style = $style_request;
		$SID .= '&amp;style=' . $style;
		$_EXTRA_URL = array('style=' . $style);
	}
	else
	{
			// Set up style
		$style = ($style) ? $style : ((!$board_config['override_user_style']) ? $user->data['user_style'] : $board_config['default_style']);
	}	
		
	$sql = 'SELECT *
		FROM ' . THEMES_TABLE . '
		WHERE themes_id = ' . (int) $style;
	if (!($result = $db->sql_query($sql)))
	{
		message_die(CRITICAL_ERROR, 'Could not query database for theme info');
	}	
	$row = $db->sql_fetchrow($result);
	
	$template_path = 'templates/';
	
	if (empty($row['template_name']))
	{
		/* Check for all installed styles first */
		$sql = 'SELECT * FROM ' . THEMES_TABLE;
		$result = $db->sql_query($sql);
		while ($rows = $db->sql_fetchrow($result))
		{
			$styles_installed[] = $rows['template_name'];
		}
		
		/* If query fails need to define this for foreach */
		if (!is_array($styles_installed))
		{
			$rows['template_name'] = 'subSilver';
			$styles_installed[] = $rows['template_name'];
		}		
		$db->sql_freeresult($result);
			
					
		// We are trying to setup a style which does not exist in the database
		// Try to fallback to the board default (if the user had a custom style)
		// and then any users using this style to the default if it succeeds
		/** **/
		if ($style != $board_config['default_style'])
		{					
			$sql = 'SELECT *
				FROM ' . THEMES_TABLE . '
				WHERE themes_id = ' . (int) $board_config['default_style'];
			$result = $db->sql_query($sql);
			
			if ($row = $db->sql_fetchrow($result))
			{
				$db->sql_freeresult($result);

				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_style = ' . (int) $board_config['default_style'] . "
					WHERE user_style = $style";
				if (!($result = $db->sql_query($sql)))
				{
					message_die(CRITICAL_ERROR, 'Could not update user theme info');
				}
			}
			else
			{
				message_die(CRITICAL_ERROR, "Could not get theme data for themes_id [$style]");
			}
		}
		else
		{			
			/** 
			* Now check for the correct existance of all of the $user->style['style_path'] images into
			* each of the effectively installed styles. see $user->style['style_id'], $user->style['style_parent_id']
			*/
			foreach ($styles_installed as $style_installed)
			{
				$style_installed = !empty($style_installed) ? $style_installed : $rows['template_name'];
				$theme_info_path = $phpbb_root_path . 'templates/' . $style_installed . '/theme_info.cfg';
				
				if ((@file_exists($theme_info_path)))
				{
					$template_name = $row['template_name'] = $style_installed;
						
					/**
					* Reset custom module default style, once used.
					*/
					$sql = "SELECT  mxt.*, bbt.*
							FROM " . THEMES_TABLE . " mxt, " . THEMES_TABLE . " bbt
							WHERE mxt.style_name = bbt.style_name
							AND mxt.style_name = '$style_installed'";				
					if ($row = $db->sql_fetchrow($result = @$db->sql_query($sql)))
					{
						$db->sql_freeresult($result);
						
						$config_name = 'default_style';
						/** */
						$board_config[$config_name] = $row['themes_id'];
						
						$sql = 'UPDATE ' . CONFIG_TABLE . "
							SET config_value = '" . $db->sql_escape($board_config[$config_name]) . "'
							WHERE config_name = '" . $db->sql_escape($config_name) . "'";
						$db->sql_query($sql);				
						
						if (!$db->sql_affectedrows() && !isset($board_config[$config_name]))
						{
							$sql = 'INSERT INTO ' . CONFIG_TABLE . ' ' . $db->sql_build_array('INSERT', array(
								'config_name'	=> $config_name,
								'config_value'	=> $board_config[$config_name]));
							$db->sql_query($sql);
						}
						
						if (!($result = $db->sql_query($sql)))
						{
							message_die(CRITICAL_ERROR, 'Could not update default_style theme info');
						}
						/** */
					}									
				}
				$template_name = $row['template_name'];	
			}					
			print_r(array( CRITICAL_ERROR, "Could not get theme data for themes_id [$style]", '', __LINE__, __FILE__, $sql ));
		}
		/** **/
	}
	else
	{
		$template_name = $row['template_name'];
	}
	
	$template = new phpbb_Template($phpbb_root_path . $template_path . $template_name);
	@define('PHPBB_ROOT_PATH', $phpbb_root_path); //for ICY-PHOENIX Styles
		
	if (is_object($template))
	{
		if(is_dir($phpbb_root_path . $user->current_template_path . '/theme/images/'))
		{
			$current_template_images = $current_template_images = $user->current_template_path . "/theme/images";						
		}
		elseif(is_dir($phpbb_root_path . $user->current_template_path . '/images/'))
		{
			$current_template_images = $current_template_images = $user->current_template_path . "/images";					
		}			
			
		$current_template_path = $template_path . $template_name;
		//include($phpbb_root_path . $template_path . $template_name . '/' . $template_name . '.cfg');
		if(@file_exists(@phpbb_realpath($phpbb_root_path . $template_path . $template_name . '/' . $template_name . '.cfg')) )
		{
			@include($phpbb_root_path . $template_path . $template_name . '/' . $template_name . '.cfg');			
		}
		elseif( @file_exists(@phpbb_realpath($phpbb_root_path. "templates/" . $template_name . "/style.cfg")) )
		{
			//
			// Do not alter this line!
			//
			@define(TEMPLATE_CONFIG, TRUE);

			$current_template_images = $current_template_path . "/theme/images";
			//		
			// - First try phpBB2 then phpBB3 template lang images then old Olympus image sets
			//		
			if ( file_exists($phpbb_root_path . $current_template_path . '/images/') )
			{
				$current_template_images = $current_template_path . '/images';
			}		
			else if ( file_exists($phpbb_root_path . $current_template_path  . '/theme/images/') )
			{		
				$current_template_images = $current_template_path  . '/theme/images';
			}		
			if ( file_exists($phpbb_root_path . $current_template_path  . '/imageset/') )
			{		
				$current_template_images = $current_template_path  . '/imageset';
			}
			
			$images['icon_quote'] = "$current_template_images/{LANG}/" . $user->img('icon_post_quote.gif', '', '', '', 'filename');
			$images['icon_edit'] = "$current_template_images/{LANG}/" . $user->img('icon_post_edit.gif', '', '', '', 'filename');			
			$images['icon_search'] = "$current_template_images/{LANG}/" . $user->img('icon_user_search.gif', '', '', '', 'filename');
			$images['icon_profile'] = "$current_template_images/{LANG}/" . $user->img('icon_user_profile.gif', '', '', '', 'filename');
			$images['icon_pm'] = "$current_template_images/{LANG}/" . $user->img('icon_contact_pm.gif', '', '', '', 'filename');
			$images['icon_email'] = "$current_template_images/{LANG}/" . $user->img('icon_contact_email.gif', '', '', '', 'filename');
			$images['icon_delpost'] = "$current_template_images/{LANG}/" . $user->img('icon_post_delete.gif', '', '', '', 'filename');
			$images['icon_ip'] = "$current_template_images/{LANG}/" . $user->img('icon_user_ip.gif', '', '', '', 'filename');
			$images['icon_www'] = "$current_template_images/{LANG}/" . $user->img('icon_contact_www.gif', '', '', '', 'filename');
			$images['icon_icq'] = "$current_template_images/{LANG}/" . $user->img('icon_contact_icq_add.gif', '', '', '', 'filename');
			$images['icon_aim'] = "$current_template_images/{LANG}/" . $user->img('icon_contact_aim.gif', '', '', '', 'filename');
			$images['icon_yim'] = "$current_template_images/{LANG}/" . $user->img('icon_contact_yim.gif', '', '', '', 'filename');
			$images['icon_msnm'] = "$current_template_images/{LANG}/" . $user->img('icon_contact_msnm.gif', '', '', '', 'filename');
			$images['icon_minipost'] = "$current_template_images/" . $user->img('icon_post_target.gif', '', '', '', 'filename');
			$images['icon_gotopost'] = "$current_template_images/" . $user->img('icon_gotopost.gif', '', '', '', 'filename');
			$images['icon_minipost_new'] = "$current_template_images/" . $user->img('icon_post_target_unread.gif', '', '', '', 'filename');
			$images['icon_latest_reply'] = "$current_template_images/" . $user->img('icon_latest_reply.gif', '', '', '', 'filename');
			$images['icon_newest_reply'] = "$current_template_images/" . $user->img('icon_newest_reply.gif', '', '', '', 'filename');

			$images['forum'] = "$current_template_images/" . $user->img('forum_read.gif', '', '27', '', 'filename');
			$images['forum_new'] = "$current_template_images/" . $user->img('forum_unread.gif', '', '', '', 'filename');
			$images['forum_locked'] = "$current_template_images/" . $user->img('forum_read_locked.gif', '', '', '', 'filename');

			// Begin Simple Subforums MOD
			$images['forums'] = "$current_template_images/" . $user->img('forum_read_subforum.gif', '', '', '', 'filename');
			$images['forums_new'] = "$current_template_images/" . $user->img('forum_unread_subforum.gif', '', '', '', 'filename');
			// End Simple Subforums MOD

			$images['folder'] = "$current_template_images/" . $user->img('topic_read.gif', '', '', '', 'filename');
			$images['folder_new'] = "$current_template_images/" . $user->img('topic_unread.gif', '', '', '', 'filename');
			$images['folder_hot'] = "$current_template_images/" . $user->img('topic_read_hot.gif', '', '', '', 'filename');
			$images['folder_hot_new'] = "$current_template_images/" . $user->img('topic_unread_hot.gif', '', '', '', 'filename');
			$images['folder_locked'] = "$current_template_images/" . $user->img('topic_read_locked.gif', '', '', '', 'filename');
			$images['folder_locked_new'] = "$current_template_images/" . $user->img('topic_unread_locked.gif', '', '', '', 'filename');
			$images['folder_sticky'] = "$current_template_images/" . $user->img('topic_read_mine.gif', '', '', '', 'filename');
			$images['folder_sticky_new'] = "$current_template_images/" . $user->img('topic_unread_mine.gif', '', '', '', 'filename');
			$images['folder_announce'] = "$current_template_images/" . $user->img('announce_read.gif', '', '', '', 'filename');
			$images['folder_announce_new'] = "$current_template_images/" . $user->img('announce_unread.gif', '', '', '', 'filename');

			$images['post_new'] = "$current_template_images/{LANG}/" . $user->img('button_topic_new.gif', '', '', '', 'filename');
			$images['post_locked'] = "$current_template_images/{LANG}/" . $user->img('button_topic_locked.gif', '', '', '', 'filename');
			$images['reply_new'] = "$current_template_images/{LANG}/" . $user->img('button_topic_reply.gif', '', '', '', 'filename');
			$images['reply_locked'] = "$current_template_images/{LANG}/" . $user->img('icon_post_target_unread.gif', '', '', '', 'filename');

			$images['pm_inbox'] = "$current_template_images/" . $user->img('msg_inbox.gif', '', '', '', 'filename');
			$images['pm_outbox'] = "$current_template_images/" . $user->img('msg_outbox.gif', '', '', '', 'filename');
			$images['pm_savebox'] = "$current_template_images/" . $user->img('msg_savebox.gif', '', '', '', 'filename');
			$images['pm_sentbox'] = "$current_template_images/" . $user->img('msg_sentbox.gif', '', '', '', 'filename');
			$images['pm_readmsg'] = "$current_template_images/" . $user->img('topic_read.gif', '', '', '', 'filename');
			$images['pm_unreadmsg'] = "$current_template_images/" . $user->img('topic_unread.gif', '', '', '', 'filename');
			$images['pm_replymsg'] = "$current_template_images/{LANG}/" . $user->img('reply.gif', '', '', '', 'filename');
			$images['pm_postmsg'] = "$current_template_images/{LANG}/" . $user->img('msg_newpost.gif', '', '', '', 'filename');
			$images['pm_quotemsg'] = "$current_template_images/{LANG}/" . $user->img('icon_quote.gif', '', '', '', 'filename');
			$images['pm_editmsg'] = "$current_template_images/{LANG}/" . $user->img('icon_edit.gif', '', '', '', 'filename');
			$images['pm_new_msg'] = "";
			$images['pm_no_new_msg'] = "";

			$images['Topic_watch'] = "";
			$images['topic_un_watch'] = "";
			$images['topic_mod_lock'] = "$current_template_images/" . $user->img('topic_lock.gif', '', '', '', 'filename');
			$images['topic_mod_unlock'] = "$current_template_images/" . $user->img('topic_unlock.gif', '', '', '', 'filename');
			$images['topic_mod_split'] = "$current_template_images/" . $user->img('topic_split.gif', '', '', '', 'filename');
			$images['topic_mod_move'] = "$current_template_images/" . $user->img('topic_move.gif', '', '', '', 'filename');
			$images['topic_mod_delete'] = "$current_template_images/" . $user->img('topic_delete.gif', '', '', '', 'filename');

			$images['voting_graphic'][0] = "$current_template_images/voting_bar.gif";
			$images['voting_graphic'][1] = "$current_template_images/voting_bar.gif";
			$images['voting_graphic'][2] = "$current_template_images/voting_bar.gif";
			$images['voting_graphic'][3] = "$current_template_images/voting_bar.gif";
			$images['voting_graphic'][4] = "$current_template_images/voting_bar.gif";

			//
			// Vote graphic length defines the maximum length of a vote result
			// graphic, ie. 100% = this length
			//
			$board_config['vote_graphic_length'] = 205;
			$board_config['privmsg_graphic_length'] = 175;			
		}
		else		
		{
			@include($phpbb_root_path . $template_path . 'prosilver/prosilver.cfg');
		}
		
		if (!defined('TEMPLATE_CONFIG'))
		{
			message_die(CRITICAL_ERROR, "Could not open $template_name template config file", '', __LINE__, __FILE__, $sql);
		}

		$img_lang = (file_exists(@phpbb_realpath($phpbb_root_path . $current_template_path . '/images/lang_' . $board_config['default_lang']))) ? $board_config['default_lang'] : 'english';

		while(list($key, $value) = @each($images))
		{
			if (!is_array($value))
			{
				$images[$key] = str_replace('{LANG}', $user->img_lang_dir, $value);
			}
		}
	}

	return $row;
}

//
// Pick a template/theme combo, 
//
function select_style($default_style, $select_name = "style", $all = "")
{
	global $db;

	$sql_where = (!$all) ? '' : '';
	$sql = 'SELECT t.themes_id, t.style_name
		FROM ' . THEMES_TABLE . " t
		$sql_where
		ORDER BY style_name";
	if (!($result = $db->sql_query($sql)))
	{
		message_die(CRITICAL_ERROR, 'Could not query user theme info');
	}
	
	$style_options = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$selected = ($row['themes_id'] == $default) ? ' selected="selected"' : '';
		$style_options .= '<option value="' . $row['themes_id'] . '"' . $selected . '>' . $row['style_name'] . '</option>';
	}
	$db->sql_freeresult($result);

	return $style_options;
}

//
// Encode the IP from decimals into hexademicals
//
function encode_ip($dotquad_ip)
{
	$ip_sep = explode('.', $dotquad_ip);
	return sprintf('%02x%02x%02x%02x', $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);
}

//
// Decode the IP from hexademicals to decimals
//
function decode_ip($int_ip)
{
	$hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
	return hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
}

//
// Create date/time from format and timezone
//
function create_date($format, $gmepoch, $tz, $forcedate = false)
{
	global $user, $board_config, $lang;
	
	static $translate;
	static $midnight;
	static $date_cache;

	$format = (!$format) ? $user->date_format : $format;
	$now = time();
	$delta = $now - $gmepoch;

	if (!isset($date_cache[$format]))
	{
		// Is the user requesting a friendly date format (i.e. 'Today 12:42')?
		$date_cache[$format] = array(
			'is_short'		=> strpos($format, '|'),
			'format_short'	=> substr($format, 0, strpos($format, '|')) . '||' . substr(strrchr($format, '|'), 1),
			'format_long'	=> str_replace('|', '', $format),
			// Filter out values that are not strings (e.g. arrays) for strtr().
			'lang'			=> array_filter($user->lang['datetime'], 'is_string'),
		);

		// Short representation of month in format? Some languages use different terms for the long and short format of May
		if ((strpos($format, '\M') === false && strpos($format, 'M') !== false) || (strpos($format, '\r') === false && strpos($format, 'r') !== false))
		{
			$date_cache[$format]['lang']['May'] = $user->lang['datetime']['May_short'];
		}
	}

	// Zone offset
	$zone_offset = $user->timezone + $user->dst;
	
	// Show date <= 1 hour ago as 'xx min ago' but not greater than 60 seconds in the future
	// A small tolerence is given for times in the future but in the same minute are displayed as '< than a minute ago'
	if ($delta <= 3600 && $delta > -60 && ($delta >= -5 || (($now / 60) % 60) == (($gmepoch / 60) % 60)) && $date_cache[$format]['is_short'] !== false && !$forcedate && isset($this->lang['datetime']['AGO']))
	{
		return $this->lang(array('datetime', 'AGO'), max(0, (int) floor($delta / 60)));
	}

	if (!$midnight)
	{
		list($d, $m, $y) = explode(' ', gmdate('j n Y', time() + $zone_offset));
		$midnight = gmmktime(0, 0, 0, $m, $d, $y) - $zone_offset;
	}

	if ($date_cache[$format]['is_short'] !== false && !$forcedate && !($gmepoch < $midnight - 86400 || $gmepoch > $midnight + 172800))
	{
		$day = false;

		if ($gmepoch > $midnight + 86400)
		{
			$day = 'TOMORROW';
		}
		else if ($gmepoch > $midnight)
		{
			$day = 'TODAY';
		}
		else if ($gmepoch > $midnight - 86400)
		{
			$day = 'YESTERDAY';
		}

		if ($day !== false)
		{
			return str_replace('||', $user->lang['datetime'][$day], strtr(@gmdate($date_cache[$format]['format_short'], $gmepoch + $zone_offset), $date_cache[$format]['lang']));
		}
	}	
	
	if (empty($translate) && $board_config['default_lang'] != 'english')
	{
		@reset($lang['datetime']);
		while (list($match, $replace) = @each($lang['datetime']))
		{
			$translate[$match] = $replace;
		}
	}
	return (!is_array($translate)) ? @strtr(@gmdate($format, $gmepoch + (3600 * $tz)), $translate) : @strtr(@gmdate($format, $gmepoch + (3600 * $tz)), $date_cache[$format]['lang']);
}

//Form validation


/**
* Add a secret hash   for use in links/GET requests
* @param string  $link_name The name of the link; has to match the name used in check_link_hash, otherwise no restrictions apply
* @return string the hash
unique_id()
*/
function generate_link_hash($link_name)
{
	global $user;

	if (!isset($user->data["hash_$link_name"]))
	{
		$user->data["hash_$link_name"] = substr(sha1($user->data['user_form_salt'] . $link_name), 0, 8);
	}

	return $user->data["hash_$link_name"];
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

/**
* Add a secret token to the form (requires the S_FORM_TOKEN template variable)
* @param string  $form_name The name of the form; has to match the name used in check_form_key, otherwise no restrictions apply
* @param string  $template_variable_suffix A string that is appended to the name of the template variable to which the form elements are assigned
*/
function add_form_key($form_name, $template_variable_suffix = '')
{
	global $config, $template, $user;

	$now = time();
	$token_sid = ($user->data['user_id'] == ANONYMOUS) ? $user->session_id : '';
	$token = sha1($now . $user->data['user_form_salt'] . $form_name . $token_sid);

	$s_fields = build_hidden_fields(array(
		'creation_time' => $now,
		'form_token'	=> $token,
	));

	$template->assign_var('S_FORM_TOKEN' . $template_variable_suffix, $s_fields);
}

/**
 * Check the form key. Required for all altering actions not secured by confirm_box
 *
 * @param	string	$form_name	The name of the form; has to match the name used
 *								in add_form_key, otherwise no restrictions apply
 * @param	int		$timespan	The maximum acceptable age for a submitted form
 *								in seconds. Defaults to the config setting.
 * @return	bool	True, if the form key was valid, false otherwise
 */
function check_form_key($form_name, $timespan = false)
{
	global $config, $request, $user;

	if ($timespan === false)
	{
		// we enforce a minimum value of half a minute here.
		$timespan = ($config['form_token_lifetime'] == -1) ? -1 : max(30, $config['form_token_lifetime']);
	}

	if ($request->is_set_post('creation_time') && $request->is_set_post('form_token'))
	{
		$creation_time	= abs(request_var('creation_time', 0));
		$token = $request->variable('form_token', '');

		$diff = time() - $creation_time;

		// If creation_time and the time() now is zero we can assume it was not a human doing this (the check for if ($diff)...
		if (defined('DEBUG_TEST') || $diff && ($diff <= $timespan || $timespan === -1))
		{
			$token_sid = ($user->data['user_id'] == ANONYMOUS && !empty($config['form_token_sid_guests'])) ? $user->session_id : '';
			$key = sha1($creation_time . $user->data['user_form_salt'] . $form_name . $token_sid);

			if ($key === $token)
			{
				return true;
			}
		}
	}

	return false;
}

//
// Pagination routine, generates
// page number sequence
//
function generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = TRUE)
{
	global $lang;

	$total_pages = ceil($num_items/$per_page);

	if ($total_pages == 1)
	{
		return '';
	}

	$on_page = floor($start_item / $per_page) + 1;

	$page_string = '';
	if ($total_pages > 10)
	{
		$init_page_max = ($total_pages > 3) ? 3 : $total_pages;

		for($i = 1; $i < $init_page_max + 1; $i++)
		{
			$page_string .= ( $i == $on_page ) ? '<strong>' . $i . '</strong>' : '<a href="' . append_sid($base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
			if ( $i <  $init_page_max )
			{
				$page_string .= ", ";
			}
		}

		if ( $total_pages > 3 )
		{
			if ( $on_page > 1  && $on_page < $total_pages )
			{
				$page_string .= ( $on_page > 5 ) ? ' ... ' : ', ';

				$init_page_min = ( $on_page > 4 ) ? $on_page : 5;
				$init_page_max = ( $on_page < $total_pages - 4 ) ? $on_page : $total_pages - 4;

				for($i = $init_page_min - 1; $i < $init_page_max + 2; $i++)
				{
					$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . append_sid($base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
					if ( $i <  $init_page_max + 1 )
					{
						$page_string .= '';
					}
				}

				$page_string .= ( $on_page < $total_pages - 4 ) ? ' ... ' : ', ';
			}
			else
			{
				$page_string .= ' ... ';
			}

			for($i = $total_pages - 2; $i < $total_pages + 1; $i++)
			{
				$page_string .= ( $i == $on_page ) ? '<strong>' . $i . '</strong>'  : '<a href="' . append_sid($base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
				if( $i <  $total_pages )
				{
					$page_string .= ", ";
				}
			}
		}
	}
	else
	{
		for($i = 1; $i < $total_pages + 1; $i++)
		{
			$page_string .= ( $i == $on_page ) ? '<strong>' . $i . '</strong>' : '<a href="' . append_sid($base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
			if ( $i <  $total_pages )
			{
				$page_string .= ', ';
			}
		}
	}

	if ($add_prevnext_text)
	{
		if ($on_page > 1)
		{
			$page_string = ' <a href="' . append_sid($base_url . "&amp;start=" . (($on_page - 2) * $per_page)) . '">' . $lang['Previous'] . '</a>&nbsp;&nbsp;' . $page_string;
		}

		if ($on_page < $total_pages)
		{
			$page_string .= '&nbsp;&nbsp;<a href="' . append_sid($base_url . "&amp;start=" . ($on_page * $per_page)) . '">' . $lang['Next'] . '</a>';
		}

	}

	$page_string = $lang['Goto_page'] . ' ' . $page_string;

	return $page_string;
}

/**
* Generate the debug output string
*
*/
function phpbb_generate_debug_output()
{
	
	global $db, $board_config, $auth, $user;
	$debug_info = array();

	// Output page creation time
	if (defined('PHPBB_DISPLAY_LOAD_TIME'))
	{
		if (isset($GLOBALS['starttime']))
		{
			$totaltime = microtime(true) - $GLOBALS['starttime'];
			$debug_info[] = sprintf('<span title="SQL time: %.3fs / PHP time: %.3fs">Time: %.3fs</span>', $db->get_sql_time(), ($totaltime - $db->get_sql_time()), $totaltime);
		}

		$debug_info[] = sprintf('<span title="Cached: %d">Queries: %d</span>', $db->sql_num_queries(true), $db->sql_num_queries());

		$memory_usage = memory_get_peak_usage();
		if ($memory_usage)
		{
			$memory_usage = get_formatted_filesize($memory_usage);

			$debug_info[] = 'Peak Memory Usage: ' . $memory_usage;
		}
	}

	if (defined('DEBUG'))
	{
		$debug_info[] = 'GZIP: ' . (($board_config['gzip_compress'] && @extension_loaded('zlib')) ? 'On' : 'Off');

		if ($user->load)
		{
			$debug_info[] = 'Load: ' . $user->load;
		}

		if ($auth->acl_get('a_'))
		{
			$debug_info[] = '<a href="' . build_url() . '&amp;explain=1">SQL Explain</a>';
		}
	}

	return implode(' | ', $debug_info);
}

//
// This does exactly what preg_quote() does in PHP 4-ish
// If you just need the 1-parameter preg_quote call, then don't bother using this.
//
function phpbb_preg_quote($str, $delimiter)
{
	$text = preg_quote($str);
	$text = str_replace($delimiter, '\\' . $delimiter, $text);
	
	return $text;
}

//
// Obtain list of naughty words and build preg style replacement arrays for use by the
// calling script, note that the vars are passed as references this just makes it easier
// to return both sets of arrays
//
function obtain_word_list(&$orig_word, &$replacement_word)
{
	global $db;

	//
	// Define censored word matches
	//
	$sql = "SELECT word, replacement
		FROM  " . WORDS_TABLE;
	if(!($result = $db->sql_query($sql)))
	{
		message_die(GENERAL_ERROR, 'Could not get censored words from database', '', __LINE__, __FILE__, $sql);
	}

	if ($row = $db->sql_fetchrow($result))
	{
		do 
		{
			$orig_word[] = '#\b(' . str_replace('\*', '\w*?', preg_quote($row['word'], '#')) . ')\b#i';
			$replacement_word[] = $row['replacement'];
		}
		while ($row = $db->sql_fetchrow($result));
	}

	return true;
}

function empty_cache_folders_admin()
{
	global $cache, $phpbb_root_path;

	$cache->destroy_datafiles(array('_'), $phpbb_root_path . '/cache/', 'sql', true);

	return true;
}

function empty_cache_folders_cms()
{
	global $cache;
	
	global $board_config, $phpbb_root_path, $phpEx;

	$cache->destroy_datafiles(array('_'), $phpbb_root_path . '/cache/', 'sql', true);
	
	return true;
}

function empty_cache_folders($cache_folder = '', $files_per_step = 0)
{
	global $board_config, $phpbb_root_path, $phpEx;
	
	$skip_files = array(
		'.',
		'..',
		'.htaccess',
		'index.htm',
		'index.html',
		'index.' . PHP_EXT,
		'empty_cache.bat',
	);

	$sql_prefix = 'sql_';
	$tpl_prefix = 'tpl_';

	// Make sure the forum tree is deleted...
	@unlink(MAIN_CACHE_FOLDER . CACHE_TREE_FILE);

	$cache_dirs_array = array(MAIN_CACHE_FOLDER, CMS_CACHE_FOLDER, FORUMS_CACHE_FOLDER, POSTS_CACHE_FOLDER, SQL_CACHE_FOLDER, TOPICS_CACHE_FOLDER, USERS_CACHE_FOLDER);
	$cache_dirs_array = ((empty($cache_folder) || !in_array($cache_folder, $cache_dirs_array)) ? $cache_dirs_array : array($cache_folder));
	$files_counter = 0;
	for ($i = 0; $i < sizeof($cache_dirs_array); $i++)
	{
		$dir = $cache_dirs_array[$i];
		$dir = ((is_dir($dir)) ? $dir : @phpbb_realpath($dir));
		$res = opendir($dir);
		while(($file = readdir($res)) !== false)
		{
			$file_full_path = $dir . $file;
			if (!in_array($file, $skip_files) && !is_dir($file_full_path))
			{
				@chmod($file_full_path, 0777);
				$res2 = @unlink($file_full_path);
				$files_counter++;
			}
			if (($files_per_step > 0) && ($files_counter >= $files_per_step))
			{
				closedir($res);
				return $files_per_step;
			}
		}
		closedir($res);
	}
	return true;
}

function empty_images_cache_folders($files_per_step = 0)
{
	global $board_config, $phpbb_root_path, $phpEx;

	$skip_files = array(
		'.',
		'..',
		'.htaccess',
		'index.htm',
		'index.html',
		'index.' . $phpEx,
	);

	$cache_dirs_array = array(POSTED_IMAGES_THUMBS_PATH);
	if (!empty($board_config['plugins']['album']['enabled']))
	{
		$cache_dirs_array = array_merge($cache_dirs_array, array(
			$phpbb_root_path . ALBUM_CACHE_PATH,
			$phpbb_root_path . ALBUM_MED_CACHE_PATH,
			$phpbb_root_path . ALBUM_WM_CACHE_PATH
		));
	}

	$files_counter = 0;
	for ($i = 0; $i < sizeof($cache_dirs_array); $i++)
	{
		$dir = $cache_dirs_array[$i];
		$dir = ((is_dir($dir)) ? $dir : @phpbb_realpath($dir));
		$res = opendir($dir);
		while(($file = readdir($res)) !== false)
		{
			$file_full_path = $dir . $file;
			if (!in_array($file, $skip_files))
			{
				if (is_dir($file_full_path))
				{
					$subres = @opendir($file_full_path);
					while(($subfile = readdir($subres)) !== false)
					{
						$subfile_full_path = $file_full_path . '/' . $subfile;
						if (!in_array($subfile, $skip_files) && !is_dir($subfile_full_path))
						{
							if(preg_match('/(\.gif$|\.png$|\.jpg|\.jpeg)$/is', $subfile))
							{
								@chmod($subfile_full_path, 0777);
								$res2 = @unlink($subfile_full_path);
								$files_counter++;
							}
							if (($files_per_step > 0) && ($files_counter >= $files_per_step))
							{
								closedir($subres);
								return $files_per_step;
							}
						}
					}
					closedir($subres);
				}
				elseif(preg_match('/(\.gif$|\.png$|\.jpg|\.jpeg)$/is', $file))
				{
					@chmod($file_full_path, 0777);
					$res2 = @unlink($file_full_path);
					$files_counter++;
				}
			}
			if (($files_per_step > 0) && ($files_counter >= $files_per_step))
			{
				closedir($res);
				return $files_per_step;
			}
		}
		closedir($res);
		if ($cg == true)
		{
			return true;
		}
	}
	return true;
}

/**
* Closing the cache object and the database
*/
function garbage_collection()
{
	global $db, $cache;

	// Unload cache, must be done before the DB connection if closed
	if (!empty($cache))
	{
		$cache->unload();
	}

	// Close our DB connection.
	if (!empty($db))
	{
		$db->sql_close();
	}
}

/**
* Handler for exit calls in phpBB.
*
* Note: This function is called after the template has been outputted.
*/
function exit_handler()
{
	global $phpbb_hook, $config;

	if (!empty($phpbb_hook) && $phpbb_hook->call_hook(__FUNCTION__))
	{
		if ($phpbb_hook->hook_return(__FUNCTION__))
		{
			return $phpbb_hook->hook_return_result(__FUNCTION__);
		}
	}

	// URL Rewrite - BEGIN
	// Compress buffered output if required and send to browser
	if (!empty($config['url_rw_runtime']))
	{
		$contents = rewrite_urls(ob_get_contents());
		ob_end_clean();
		(@extension_loaded('zlib') && !empty($config['gzip_compress_runtime'])) ? ob_start('ob_gzhandler') : ob_start();
		echo $contents;
	}
	// URL Rewrite - END

	// As a pre-caution... some setups display a blank page if the flush() is not there.
	(empty($config['gzip_compress_runtime']) && empty($config['url_rw_runtime'])) ? @flush() : @ob_flush();

	exit;
}

/**
* Full page generation
*/
function full_page_generation($page_template, $page_title = '', $page_description = '', $page_keywords = '')
{
	global $template, $meta_content, $phpbb_root_path, $phpEx;

	global $db, $cache, $config, $user, $images, $theme, $lang, $tree;
	global $table_prefix, $SID, $_SID;
	global $starttime, $base_memory_usage, $do_gzip_compress, $start;
	global $gen_simple_header, $meta_content, $nav_separator, $nav_links, $nav_pgm, $nav_add_page_title, $skip_nav_cat;
	global $breadcrumbs;
	global $forum_id, $topic_id;	
	
	$meta_content['page_title'] = (!empty($page_title) ? $page_title : (!empty($meta_content['page_title']) ? $meta_content['page_title'] : ''));
	$meta_content['description'] = (!empty($page_description) ? $page_description : (!empty($meta_content['description']) ? $meta_content['description'] : ''));
	$meta_content['keywords'] = (!empty($page_keywords) ? $page_keywords : (!empty($meta_content['keywords']) ? $meta_content['keywords'] : ''));
	//include($phpbb_root_path . 'includes/page_header.'.$phpEx);
	$template->set_filenames(array('body' => $page_template));
	//include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
}

/**
* Add log event
*/
function add_log()
{
	global $db, $user;

	// In phpBB 3.1.x i want to have logging in a class to be able to control it
	// For now, we need a quite hakish approach to circumvent logging for some actions
	// @todo implement cleanly
	if (!empty($GLOBALS['skip_add_log']))
	{
		return false;
	}

	$args = func_get_args();

	$mode = array_shift($args);
	$reportee_id = ($mode == 'user') ? intval(array_shift($args)) : '';
	$forum_id = ($mode == 'mod') ? intval(array_shift($args)) : '';
	$topic_id = ($mode == 'mod') ? intval(array_shift($args)) : '';
	$action = array_shift($args);
	$data = (!sizeof($args)) ? '' : serialize($args);

	$sql_ary = array(
		'user_id' => (empty($user->data)) ? ANONYMOUS : $user->data['user_id'],
		'log_ip' => $user->ip,
		'log_time' => time(),
		'log_operation' => $action,
		'log_data' => $data,
	);

	switch ($mode)
	{
		case 'admin':
			$sql_ary['log_type'] = LOG_ADMIN;
		break;

		case 'mod':
			$sql_ary += array(
				'log_type' => LOG_MOD,
				'forum_id' => $forum_id,
				'topic_id' => $topic_id
			);
		break;

		case 'user':
			$sql_ary += array(
				'log_type' => LOG_USERS,
				'reportee_id' => $reportee_id
			);
		break;

		case 'critical':
			$sql_ary['log_type'] = LOG_CRITICAL;
		break;

		default:
			return false;
	}

	$db->sql_query('INSERT INTO ' . LOG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

	return $db->sql_nextid();
}

/**
* Return a nicely formatted backtrace (parts from the php manual by diz at ysagoon dot com)
*/
function get_backtrace()
{
	$output = '<div style="font-family: monospace;">';
	$backtrace = debug_backtrace();
	$path = phpbb_realpath(IP_ROOT_PATH);

	foreach ($backtrace as $number => $trace)
	{
		// We skip the first one, because it only shows this file/function
		if ($number == 0)
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
* mode can be: ipv4|ipv6
*/
function get_preg_expression($mode)
{
	switch ($mode)
	{
		// Whoa these look impressive!
		// The code to generate the following two regular expressions which match valid IPv4/IPv6 addresses can be found in the develop directory
		case 'ipv4':
			return '#^(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$#';
		break;

		case 'ipv6':
			return '#^(?:(?:(?:[\dA-F]{1,4}:){6}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:::(?:[\dA-F]{1,4}:){5}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:):(?:[\dA-F]{1,4}:){4}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,2}:(?:[\dA-F]{1,4}:){3}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,3}:(?:[\dA-F]{1,4}:){2}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,4}:(?:[\dA-F]{1,4}:)(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,5}:(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,6}:[\dA-F]{1,4})|(?:(?:[\dA-F]{1,4}:){1,7}:))$#i';
		break;
	}

	return '';
}

/**
* Generate regexp for naughty words censoring
* Depends on whether installed PHP version supports unicode properties
*
* @param string	$word			word template to be replaced
*
* @return string $preg_expr		regex to use with word censor
*/
function get_censor_preg_expression($word)
{
	// Unescape the asterisk to simplify further conversions
	$word = str_replace('\*', '*', preg_quote($word, '#'));

	// Replace asterisk(s) inside the pattern, at the start and at the end of it with regexes
	$word = preg_replace(array('#(?<=[\p{Nd}\p{L}_])\*+(?=[\p{Nd}\p{L}_])#iu', '#^\*+#', '#\*+$#'), array('([\x20]*?|[\p{Nd}\p{L}_-]*?)', '[\p{Nd}\p{L}_-]*?', '[\p{Nd}\p{L}_-]*?'), $word);

	// Generate the final substitution
	$preg_expr = '#(?<![\p{Nd}\p{L}_-])(' . $word . ')(?![\p{Nd}\p{L}_-])#iu';

	return $preg_expr;
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
* @param string $host:Fully-Qualified Domain Name
* @param string $type: Resource record type to lookup
*						Supported types are: MX (default), A, AAAA, NS, TXT, CNAME
*						Other types may work or may not work
*
* @return mixed: true if entry found,
*					false if entry not found,
*					null if this function is not supported by this environment
*
* Since null can also be returned, you probably want to compare the result
* with === true or === false,
*
* @author bantu
*/
function phpbb_checkdnsrr($host, $type = 'MX')
{
	// The dot indicates to search the DNS root (helps those having DNS prefixes on the same domain)
	if (substr($host, -1) == '.')
	{
		$host_fqdn = $host;
		$host = substr($host, 0, -1);
	}
	else
	{
		$host_fqdn = $host . '.';
	}
	// $host: has format some.host.example.com
	// $host_fqdn: has format some.host.example.com.

	// If we're looking for an A record we can use gethostbyname()
	if (($type == 'A') && function_exists('gethostbyname'))
	{
		return (@gethostbyname($host_fqdn) == $host_fqdn) ? false : true;
	}

	// checkdnsrr() is available on Windows since PHP 5.3,
	// but until 5.3.3 it only works for MX records
	// See: http://bugs.php.net/bug.php?id=51844

	// Call checkdnsrr() if
	// we're looking for an MX record or
	// we're not on Windows or
	// we're running a PHP version where #51844 has been fixed

	// checkdnsrr() supports AAAA since 5.0.0
	// checkdnsrr() supports TXT since 5.2.4
	if ((($type == 'MX') || (DIRECTORY_SEPARATOR != '\\') || version_compare(PHP_VERSION, '5.3.3', '>=')) && (($type != 'AAAA') || version_compare(PHP_VERSION, '5.0.0', '>=')) && (($type != 'TXT') || version_compare(PHP_VERSION, '5.2.4', '>=')) && function_exists('checkdnsrr')
	)
	{
		return checkdnsrr($host_fqdn, $type);
	}

	// dns_get_record() is available since PHP 5; since PHP 5.3 also on Windows,
	// but on Windows it does not work reliable for AAAA records before PHP 5.3.1

	// Call dns_get_record() if
	// we're not looking for an AAAA record or
	// we're not on Windows or
	// we're running a PHP version where AAAA lookups work reliable
	if ((($type != 'AAAA') || (DIRECTORY_SEPARATOR != '\\') || version_compare(PHP_VERSION, '5.3.1', '>=')) && function_exists('dns_get_record'))
	{
		// dns_get_record() expects an integer as second parameter
		// We have to convert the string $type to the corresponding integer constant.
		$type_constant = 'DNS_' . $type;
		$type_param = (defined($type_constant)) ? constant($type_constant) : DNS_ANY;

		// dns_get_record() might throw E_WARNING and return false for records that do not exist
		$resultset = @dns_get_record($host_fqdn, $type_param);

		if (empty($resultset) || !is_array($resultset))
		{
			return false;
		}
		elseif ($type_param == DNS_ANY)
		{
			// $resultset is a non-empty array
			return true;
		}

		foreach ($resultset as $result)
		{
			if (isset($result['host']) && ($result['host'] == $host) && isset($result['type']) && ($result['type'] == $type))
			{
				return true;
			}
		}

		return false;
	}

	// If we're on Windows we can still try to call nslookup via exec() as a last resort
	if ((DIRECTORY_SEPARATOR == '\\') && function_exists('exec'))
	{
		@exec('nslookup -type=' . escapeshellarg($type) . ' ' . escapeshellarg($host_fqdn), $output);

		// If output is empty, the nslookup failed
		if (empty($output))
		{
			return NULL;
		}

		foreach ($output as $line)
		{
			$line = trim($line);

			if (empty($line))
			{
				continue;
			}

			// Squash tabs and multiple whitespaces to a single whitespace.
			$line = preg_replace('/\s+/', ' ', $line);

			switch ($type)
			{
				case 'MX':
					if (stripos($line, "$host MX") === 0)
					{
						return true;
					}
				break;

				case 'NS':
					if (stripos($line, "$host nameserver") === 0)
					{
						return true;
					}
				break;

				case 'TXT':
					if (stripos($line, "$host text") === 0)
					{
						return true;
					}
				break;

				case 'CNAME':
					if (stripos($line, "$host canonical name") === 0)
					{
						return true;
					}

				default:
				case 'A':
				case 'AAAA':
					if (!empty($host_matches))
					{
						// Second line
						if (stripos($line, "Address: ") === 0)
						{
							return true;
						}
						else
						{
							$host_matches = false;
						}
					}
					else if (stripos($line, "Name: $host") === 0)
					{
						// First line
						$host_matches = true;
					}
				break;
			}
		}

		return false;
	}

	return NULL;
}

/**
* Handler for init calls in phpBB. This function is called in user::setup();
* This function supports hooks.
*/
function phpbb_user_session_handler()
{
	global $phpbb_hook;

	if (!empty($phpbb_hook) && $phpbb_hook->call_hook(__FUNCTION__))
	{
		if ($phpbb_hook->hook_return(__FUNCTION__))
		{
			return $phpbb_hook->hook_return_result(__FUNCTION__);
		}
	}

	return;
}

/**
* Error and message handler, call with trigger_error if reqd
*/
function msg_handler($errno, $msg_text, $errfile, $errline)
{
	global $config, $lang;
	global $msg_code, $msg_title, $msg_long_text;

	// Do not display notices if we suppress them via @
	if (error_reporting() == 0)
	{
		return;
	}

	// Message handler is stripping text. In case we need it, we are possible to define long text...
	if (isset($msg_long_text) && $msg_long_text && !$msg_text)
	{
		$msg_text = $msg_long_text;
	}

	$msg_code = empty($msg_code) ? GENERAL_MESSAGE : $msg_code;

	switch ($errno)
	{
		case E_NOTICE:
			// Mighty Gorgon: if you want to report uninitialized variables, comment the "BREAK" below...
		break;
		case E_WARNING:
			// Check the error reporting level and return if the error level does not match

			// If DEBUG is defined to FALSE then return
			if (defined('DEBUG') && !DEBUG)
			{
				return;
			}

			// If DEBUG is defined the default level is E_ALL
			if (($errno & ((defined('DEBUG')) ? E_ALL : error_reporting())) == 0)
			{
				return;
			}

			if ((strpos($errfile, 'cache') === false) && (strpos($errfile, 'template.') === false))
			{
				// flush the content, else we get a white page if output buffering is on
				if ((int) @ini_get('output_buffering') === 1 || strtolower(@ini_get('output_buffering')) === 'on')
				{
					@ob_flush();
				}

				// Another quick fix for those having gzip compression enabled, but do not flush if the coder wants to catch "something". ;)
				$config['gzip_compress_runtime'] = (isset($config['gzip_compress_runtime']) ? $config['gzip_compress_runtime'] : $config['gzip_compress']);
				if (!empty($config['gzip_compress_runtime']))
				{
					if (@extension_loaded('zlib') && !headers_sent() && !ob_get_level())
					{
						@ob_flush();
					}
				}

				// remove complete path to installation, with the risk of changing backslashes meant to be there
				$errfile = str_replace(array(phpbb_realpath(IP_ROOT_PATH), '\\'), array('', '/'), $errfile);
				$msg_text = str_replace(array(phpbb_realpath(IP_ROOT_PATH), '\\'), array('', '/'), $msg_text);

				echo '<b>[Icy Phoenix Debug] PHP Notice</b>: in file <b>' . $errfile . '</b> on line <b>' . $errline . '</b>: <b>' . $msg_text . '</b><br />' . "\n";
			}

			return;

		break;

		case E_USER_ERROR:

			$msg_text = (!empty($lang[$msg_text])) ? $lang[$msg_text] : $msg_text;
			$msg_title_default = (!empty($lang['General_Error'])) ? $lang['General_Error'] : 'General Error';
			$msg_title = (!empty($lang[$msg_title])) ? $lang[$msg_title] : $msg_title_default;
			$return_url = (!empty($lang['CLICK_RETURN_HOME'])) ? sprintf($lang['CLICK_RETURN_HOME'], '<a href="' . IP_ROOT_PATH . '">', '</a>') : ('<a href="' . IP_ROOT_PATH . '">Return to home page</a>');
			garbage_collection();
			html_message($msg_title, $msg_text, $return_url);
			exit_handler();

			// On a fatal error (and E_USER_ERROR *is* fatal) we never want other scripts to continue and force an exit here.
			exit;
		break;

		case E_USER_WARNING:
		case E_USER_NOTICE:
			define('IN_ERROR_HANDLER', true);
			$status_not_found_array = array('ERROR_NO_ATTACHMENT', 'NO_FORUM', 'NO_TOPIC', 'NO_USER');
			if (in_array($msg_text, $status_not_found_array))
			{
				if (!defined('STATUS_404')) define('STATUS_404', true);
			}
			message_die($msg_code, $msg_text, $msg_title, $errline, $errfile, '');
	}
}

/**
* HTML Message
*/
function html_message($msg_title, $msg_text, $return_url)
{
	global $lang;
	$encoding_charset = !empty($lang['ENCODING']) ? $lang['ENCODING'] : 'UTF-8';

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">';
	echo '<head>';
	echo '<meta http-equiv="content-type" content="text/html; charset=' . $encoding_charset . '" />';
	echo '<title>' . $msg_title . '</title>';
	echo '<style type="text/css">';
	echo "\n" . '/* <![CDATA[ */' . "\n";
	echo '* { margin: 0; padding: 0; } html { font-size: 100%; height: 100%; margin-bottom: 1px; background-color: #e8eef8; } body { font-family: "Trebuchet MS", "Lucida Grande", Verdana, Helvetica, Arial, sans-serif; color: #225599; background: #e8eef8; font-size: 62.5%; margin: 0; } ';
	echo 'a:link, a:active, a:visited { color: #336699; text-decoration: none; } a:hover { color: #dd2222; text-decoration: underline; } ';
	echo '#wrap { padding: 0 20px 15px 20px; min-width: 615px; } #page-header { text-align: right; height: 40px; } #page-footer { clear: both; font-size: 1em; text-align: center; } ';
	echo '.panel { margin: 4px 0; background-color: #ffffff; border: solid 1px #dde8ee; } ';
	echo '#errorpage #page-header a { font-weight: bold; line-height: 6em; } #errorpage #content { padding: 10px; } #errorpage #content h1 { line-height: 1.2em; margin-bottom: 0; color: #dd2222; } ';
	echo '#errorpage #content div { margin-top: 20px; margin-bottom: 5px; border-bottom: 1px solid #dddddd; padding-bottom: 5px; color: #333333; font: bold 1.2em "Trebuchet MS", "Lucida Grande", Arial, Helvetica, sans-serif; text-decoration: none; line-height: 120%; text-align: left; } ';
	echo "\n" . '/* ]]> */' . "\n";
	echo '</style>';
	echo '</head>';
	echo '<body id="errorpage">';
	echo '<div id="wrap">';
	echo '	<div id="page-header">';
	echo '		' . $return_url;
	echo '	</div>';
	echo '	<div class="panel">';
	echo '		<div id="content">';
	echo '			<h1>' . $msg_title . '</h1>';
	echo '			<div>' . $msg_text . '</div>';
	echo '		</div>';
	echo '	</div>';
	echo '	<div id="page-footer">';
	echo '		Powered by <a href="http://www.icyphoenix.com/" target="_blank">Icy Phoenix</a> based on <a href="http://www.phpbb.com/" target="_blank">phpBB</a>';
	echo '	</div>';
	echo '</div>';
	echo '</body>';
	echo '</html>';
}

//
// This is general replacement for die(), allows templated
// output in users (or default) language, etc.
//
// $msg_code can be one of these constants:
//
// GENERAL_MESSAGE : Use for any simple text message, eg. results 
// of an operation, authorisation failures, etc.
//
// Backported from MX-Publisher Portal-CMS
// by FlorinCB aka orynider
//
// GENERAL ERROR : Use for any error which occurs _AFTER_ the 
// common.php include and session code, ie. most errors in 
// pages/functions
//
// CRITICAL_MESSAGE : Used when basic config data is available but 
// a session may not exist, eg. banned users
//
// CRITICAL_ERROR : Used when config data cannot be obtained, eg
// no database connection. Should _not_ be used in 99.5% of cases
//
function message_die($msg_code, $msg_text = '', $msg_title = '', $err_line = '', $err_file = '', $sql = '')
{
	global $db, $layouttemplate, $template, $board_config, $theme, $lang, $phpEx, $phpbb_root_path, $nav_links, $gen_simple_header, $images, $phpbb_root_path;
	global $user, $userdata, $user_ip, $session_length, $backend;
	global $starttime, $cache;

	static $msg_history;
	
	$default_lang = (isset($user->lang['default_lang'])) ? $user->lang['default_lang'] : $board_config['default_lang'];

	if( !isset($msg_history) )
	{
		$msg_history = array();
	}	
	
	$msg_history[] = array(
		'msg_code'	=> $msg_code,
		'msg_text'	=> $msg_text,
		'msg_title'	=> $msg_title,
		'err_line'	=> $err_line,
		'err_file'	=> $err_file,
		'sql'		=> $sql
	);
	
	//
	//This will check whaever we are installing
	//

	if(defined('HAS_DIED'))
	{
		//
		// This message is printed at the end of the report.
		// Of course, you can change it to suit your own needs. ;-)
		//
		$custom_error_message = 'Please, contact the %swebmaster%s. Thank you.';
		if ( !empty($board_config) && !empty($board_config['board_email']) )
		{
			$custom_error_message = sprintf($custom_error_message, '<a href="mailto:' . $board_config['board_email'] . '">', '</a>');
		}
		else
		{
			$custom_error_message = sprintf($custom_error_message, '', '');
		}
		echo "<html>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n<body>\n<b>Critical Error!</b><br />\nmx_message_die() was called multiple times.<br />&nbsp;<hr />";
		for( $i = 0; $i < count($msg_history); $i++ )
		{
			echo '<b>Error #' . ($i+1) . "</b>\n<br />\n";
			if( !empty($msg_history[$i]['msg_title']) )
			{
				echo '<b>' . $msg_history[$i]['msg_title'] . "</b>\n<br />\n";
			}
			echo $msg_history[$i]['msg_text'] . "\n<br /><br />\n";
			if( !empty($msg_history[$i]['err_line']) )
			{
				echo '<b>Line :</b> ' . $msg_history[$i]['err_line'] . '<br /><b>File :</b> ' . $msg_history[$i]['err_file'] . "</b>\n<br />\n";
			}
			if( !empty($msg_history[$i]['sql']) )
			{
				echo '<b>SQL :</b> ' . $msg_history[$i]['sql'] . "\n<br />\n";
			}
			echo "&nbsp;<hr />\n";
		}
		echo $custom_error_message . '<hr /><br clear="all" />';
		die("</body>\n</html>");
	}
	
	define('HAS_DIED', 1);
	$sql_store = $sql;

	//
	// Get SQL error if we are debugging. Do this as soon as possible to prevent
	// subsequent queries from overwriting the status of sql_error()
	//
	if (DEBUG && ($msg_code == GENERAL_ERROR || $msg_code == CRITICAL_ERROR))
	{
		$sql_error = $db->sql_error();

		$debug_text = '';

		if ( $sql_error['message'] != '' )
		{
			$debug_text .= '<br /><br />SQL Error : ' . $sql_error['code'] . ' ' . $sql_error['message'];
		}

		if ( $sql_store != '' )
		{
			$debug_text .= "<br /><br />$sql_store";
		}

		if ( $err_line != '' && $err_file != '' )
		{
			$debug_text .= '</br /><br />Line : ' . $err_line . '<br />File : ' . $err_file;
		}
	}

	//Security check
	if(!@is_object($user) && !isset($auth))
	{
		echo '<b>Couldn\'t initalize the user and auth classes required to call message_die().\n<br />\n';
	}

	if(!@is_object($user))
	{
		$user = new user();
	}
	
	if(!isset($auth))
	{
		$auth = new auth();
	}
	
	if ( empty($page_id) && is_request('portalpage') )
	{
		$page_id = request_var('portalpage', 1);
	}
	else if ( empty($page_id) )
	{
		$page_id = request_var('page', 1);
	}

	if (!$page_id)
	{
		$page_id = 1;
	}

	//
	// Start user session
	// - populate $userdata and $lang
	//
	if( empty($userdata) && ( $msg_code == GENERAL_MESSAGE || $msg_code == GENERAL_ERROR ) )
	{
		//$user->init($user_ip, $page_id, false);
		$userdata = $user->session_pagestart($user_ip, $page_id);
	}

	if(empty($theme))
	{
		global $user_ip;
		$user->page_id = 1;
		$user->user_ip = $user_ip;
		//$user->_init_userprefs();
		init_userprefs($user->data);		
	}

	$default_lang = (isset($user->lang['default_lang'])) ? $user->encode_lang($user->lang['default_lang']) : $board_config['default_lang'];

	if ( empty($default_lang) )
	{
		// - populate $default_lang
		$default_lang= 'english';
	}

	$lang_path = $phpbb_root_path . 'language/';

	//
	// If the header hasn't been output then do it
	//
	if ( !defined('HEADER_INC') && $msg_code != CRITICAL_ERROR )
	{
		if ( empty($lang) || empty($lang['Board_disable']) )
		{
			if ((@include $lang_path . "lang_" . $default_lang . "/lang_main.$phpEx") === false)
			{
				if ((@include $lang_path . "lang_english/lang_main.$phpEx") === false)
				{
					die('Language file (message_die) ' . $lang_path . "lang_" . $default_lang . "/lang_main.$phpEx" . ' couldn\'t be opened.');
				}
			}

			if ((@include $phpbb_root_path . "language/lang_" . $default_lang . "/lang_main.$phpEx") === false)
			{
				if ((@include $phpbb_root_path . "language/lang_english/lang_main.$phpEx") === false)
				{
					die('Language file (message_die) ' . $phpbb_root_path . "language/lang_" . $default_lang . "/lang_main.$phpEx" . ' couldn\'t be opened.');
				}
			}
		}

		$page_title = !empty($msg_title) ? $msg_title : $lang['Information'];

		if(!is_object($template))
		{
			$user->init_style();
		}

		//
		// Load the Page Header
		//
		if ( !defined('IN_ADMIN') )
		{
			include($phpbb_root_path . 'includes/page_header.'.$phpEx);
		}
		else
		{
			include($phpbb_root_path . 'admin/page_header_admin.'.$phpEx);
		}
	}

	switch($msg_code)
	{
		case GENERAL_MESSAGE:
			if ( $msg_title == '' )
			{
				$msg_title = $lang['Information'];
			}
		break;

		case CRITICAL_MESSAGE:
			if ( $msg_title == '' )
			{
				$msg_title = $lang['Critical_Information'];
			}
		break;

		case GENERAL_ERROR:
			if ( $msg_text == '' )
			{
				$msg_text = $lang['An_error_occured'];
			}

			if ( $msg_title == '' )
			{
				$msg_title = $lang['General_Error'];
			}
		break;

		case CRITICAL_ERROR:
			//
			// Critical errors mean we cannot rely on _ANY_ DB information being
			// available so we're going to dump out a simple echo'd statement
			//
			if ((@include($lang_path . "lang_" . $default_lang . "/lang_main.$phpEx")) === false)
			{
				if ((@include($lang_path . "lang_english/lang_main.$phpEx")) === false)
				{
					$phpbb_lang_error = 'Language file ' . $lang_path . "lang_" . $default_lang . "/lang_main.$phpEx" . ' couldn\'t be opened.';
				}
				else
				{
					$phpbb_lang_error = false;
				}
			}

			if ((@include($phpbb_root_path . "language/lang_" . $default_lang . "/lang_main.$phpEx")) === false)
			{
				if ((@include($phpbb_root_path . "language/lang_english/lang_main.$phpEx")) === false)
				{
					$lang_error = 'Language file ' . $phpbb_root_path . "language/lang_" . $default_lang . "/lang_main.$phpEx" . ' couldn\'t be opened.';
				}
				else
				{
					$lang_error = false;
				}
			}

			if ($msg_text == '')
			{
				$msg_text = $lang['A_critical_error'];
			}

			if ($msg_title == '')
			{
				$msg_title = 'phpBB2 : <b>' . $lang['Critical_Error'] . '</b>';
			}
		break;
	}


	//
	// Add on DEBUG info if we've enabled debug mode and this is an error. This
	// prevents debug info being output for general messages should DEBUG be
	// set TRUE by accident (preventing confusion for the end user!)
	//
	if ( DEBUG && ( $msg_code == GENERAL_ERROR || $msg_code == CRITICAL_ERROR ) )
	{
		if ( $debug_text != '' )
		{
			$msg_text = $msg_text . '<br /><br /><b><u>DEBUG MODE</u></b> ' . $debug_text;
		}
	}

	if (isset($phpbb_lang_error))
	{
		$msg_text = $msg_text . '<br /><br /><b><u>ALLSO</u></b> ' . $phpbb_lang_error;
	}

	if (isset($lang_error))
	{
		$msg_text = $msg_text . '<br /><br /><b><u>ALLSO</u></b> ' . $lang_error;
	}

	if ( $msg_code != CRITICAL_ERROR )
	{
		if ( !empty($lang[$msg_text]) )
		{
			$msg_text = $lang[$msg_text];
		}
		if ( !defined('IN_ADMIN') )
		{
			$message_file = isset($user->full_page) ? 'full_page_body.tpl' : 'message_body.tpl';
			$template->set_filenames(array(
				'message_body' => $message_file)
			);
		}
		else
		{
			$template->set_filenames(array('message_body' => 'admin/admin_message_body.tpl'));
		}
		
		//
		// Fix for correcting possible "bad" links to phpBB
		//
		if (!(strpos($msg_text, 'href') === false))
		{
			$msg_text = str_replace('<a href="index', '<a href="'.$phpbb_root_path.'index', $msg_text);
			$msg_text = str_replace('<a href="viewforum', '<a href="'.$phpbb_root_path.'viewforum', $msg_text);
			$msg_text = str_replace('<a href="viewtopic', '<a href="'.$phpbb_root_path.'viewtopic', $msg_text);
			$msg_text = str_replace('<a href="modcp', '<a href="'.$phpbb_root_path.'modcp', $msg_text);
			$msg_text = str_replace('<a href="groupcp', '<a href="'.$phpbb_root_path.'groupcp', $msg_text);
			$msg_text = str_replace('<a href="posting', '<a href="'.$phpbb_root_path.'posting', $msg_text);
		}
		
		$template->assign_vars(array(
			'MESSAGE_TITLE' => $msg_title,
			'MESSAGE_TEXT' => $msg_text)
		);
		
		ob_start();
		$template->pparse('message_body');
		$phpbb_output = ob_get_contents();
		ob_end_clean();
		
		$phpbb_output = str_replace('"templates/'.$theme['template_name'], '"' . $phpbb_root_path . 'templates/'.$theme['template_name'], $phpbb_output);
		echo($phpbb_output);
		unset($phpbb_output);
		
		if ( !defined('IN_ADMIN') )
		{
			include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
		}
		else
		{
			include($phpbb_root_path . 'admin/page_footer_admin.'.$phpEx);
		}
	}
	else
	{
		if (!defined('TEMPLATE_ROOT_PATH'))
		{
			define('TEMPLATE_ROOT_PATH', $phpbb_root_path.'templates/'.$theme['template_name'].'/');
		}
		if (file_exists($phpbb_root_path . TEMPLATE_ROOT_PATH . 'msgdie_header.tpl'))
		{		
			$layouttemplate->set_filenames(array(
				'overall_header' => 'msgdie_header.tpl',
			));
			$layouttemplate->pparse('overall_header');			
		}	
		echo "<html>\n<body>\n" . $msg_title . "\n<br /><br />\n" . $msg_text . "</body>\n</html>";		
		if (file_exists($phpbb_root_path . TEMPLATE_ROOT_PATH . 'msgdie_footer.tpl'))
		{		
			$layouttemplate->set_filenames(array(
				'overall_footer' => 'msgdie_footer.tpl',
			));
			$layouttemplate->pparse('overall_footer');			
		}		
	}
	exit;
}

//
// This function is for compatibility with PHP 4.x's realpath()
// function.  In later versions of PHP, it needs to be called
// to do checks with some functions.  Older versions of PHP don't
// seem to need this, so we'll just return the original value.
// dougk_ff7 <October 5, 2002>
function phpbb_realpath($path)
{
	global $phpbb_root_path, $phpEx;

	return (!@function_exists('realpath') || !@realpath($phpbb_root_path . 'includes/functions.'.$phpEx)) ? $path : @realpath($path);
}

/**
* Redirects the user to another page then exits the script nicely
* This function is intended for urls within the board. It's not meant to redirect to cross-domains.
*
* @param string $url The url to redirect to
* @param bool $return If true, do not redirect but return the sanitized URL. Default is no return.
* @param bool $disable_cd_check If true, redirect() will redirect to an external domain. If false, the redirect point to the boards url if it does not match the current domain. Default is false.
*/
function redirect($url)
{
	global $db, $board_config, $_SERVER;

	if (!empty($db))
	{
		$db->sql_close();
	}

	if (strstr(urldecode($url), "\n") || strstr(urldecode($url), "\r") || strstr(urldecode($url), ';url'))
	{
		message_die(GENERAL_ERROR, 'Tried to redirect to potentially insecure url.');
	}

	$server_protocol = ($board_config['cookie_secure']) ? 'https://' : 'http://';
	$server_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($board_config['server_name']));
	$server_port = ($board_config['server_port'] <> 80) ? ':' . trim($board_config['server_port']) : '';
	$script_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($board_config['script_path']));
	$script_name = ($script_name == '') ? $_SERVER : '/' . $script_name;
	$url = preg_replace('#^\/?(.*?)\/?$#', '/\1', trim($url));

	// Redirect via an HTML form for PITA webservers
	if (@preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE')))
	{
		header('Refresh: 0; URL=' . $server_protocol . $server_name . $server_port . $script_name . $url);
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><meta http-equiv="refresh" content="0; url=' . $server_protocol . $server_name . $server_port . $script_name . $url . '"><title>Redirect</title></head><body><div align="center">If your browser does not support meta redirection please click <a href="' . $server_protocol . $server_name . $server_port . $script_name . $url . '">HERE</a> to be redirected</div></body></html>';
		exit;
	}

	// Behave as per HTTP/1.1 spec for others
	header('Location: ' . $server_protocol . $server_name . $server_port . $script_name . $url);
	exit;
}

/**
* Redirects the user to another page then exits the script nicely
* This function is intended for urls within the board. It's not meant to redirect to cross-domains.
*
* @param string $url The url to redirect to
* @param bool $return If true, do not redirect but return the sanitized URL. Default is no return.
* @param bool $disable_cd_check If true, redirect() will redirect to an external domain. If false, the redirect point to the boards url if it does not match the current domain. Default is false.
*/
function phpbb_redirect($url, $return = false, $disable_cd_check = false)
{
	global $db, $cache, $config, $user, $lang, $_SERVER;

	$failover_flag = false;

	if (empty($lang))
	{
		setup_basic_lang();
	}

	if (!$return)
	{
		garbage_collection();
	}

	$server_url = create_server_url();

	// Make sure no &amp;'s are in, this will break the redirect
	$url = str_replace('&amp;', '&', $url);
	// Determine which type of redirect we need to handle...
	$url_parts = @parse_url($url);

	if ($url_parts === false)
	{
		// Malformed url, redirect to current page...
		$url = $server_url . $user->page['page'];
	}
	elseif (!empty($url_parts['scheme']) && !empty($url_parts['host']))
	{
		// Attention: only able to redirect within the same domain if $disable_cd_check is false (yourdomain.com -> www.yourdomain.com will not work)
		if (!$disable_cd_check && ($url_parts['host'] !== $user->host))
		{
			$url = $server_url;
		}
	}
	elseif ($url[0] == '/')
	{
		// Absolute uri, prepend direct url...
		$url = create_server_url(true) . $url;
	}
	else
	{
		// Relative uri
		$pathinfo = pathinfo($url);

		if (!$disable_cd_check && !file_exists($pathinfo['dirname'] . '/'))
		{
			$url = str_replace('../', '', $url);
			$pathinfo = pathinfo($url);

			if (!file_exists($pathinfo['dirname'] . '/'))
			{
				// fallback to "last known user page"
				// at least this way we know the user does not leave the phpBB root
				$url = $server_url . $user->page['page'];
				$failover_flag = true;
			}
		}

		if (!$failover_flag)
		{
			// Is the uri pointing to the current directory?
			if ($pathinfo['dirname'] == '.')
			{
				$url = str_replace('./', '', $url);

				// Strip / from the beginning
				if ($url && (substr($url, 0, 1) == '/'))
				{
					$url = substr($url, 1);
				}

				if ($user->page['page_dir'])
				{
					$url = $server_url . $user->page['page_dir'] . '/' . $url;
				}
				else
				{
					$url = $server_url . $url;
				}
			}
			else
			{
				// Used ./ before, but IP_ROOT_PATH is working better with urls within another root path
				$root_dirs = explode('/', str_replace('\\', '/', phpbb_realpath(IP_ROOT_PATH)));
				$page_dirs = explode('/', str_replace('\\', '/', phpbb_realpath($pathinfo['dirname'])));
				$intersection = array_intersect_assoc($root_dirs, $page_dirs);

				$root_dirs = array_diff_assoc($root_dirs, $intersection);
				$page_dirs = array_diff_assoc($page_dirs, $intersection);

				$dir = str_repeat('../', sizeof($root_dirs)) . implode('/', $page_dirs);

				// Strip / from the end
				if ($dir && substr($dir, -1, 1) == '/')
				{
					$dir = substr($dir, 0, -1);
				}

				// Strip / from the beginning
				if ($dir && substr($dir, 0, 1) == '/')
				{
					$dir = substr($dir, 1);
				}

				$url = str_replace($pathinfo['dirname'] . '/', '', $url);

				// Strip / from the beginning
				if (substr($url, 0, 1) == '/')
				{
					$url = substr($url, 1);
				}

				$url = (!empty($dir) ? $dir . '/' : '') . $url;
				$url = $server_url . $url;
			}
		}
	}

	// Make sure no linebreaks are there... to prevent http response splitting for PHP < 4.4.2
	if ((strpos(urldecode($url), "\n") !== false) || (strpos(urldecode($url), "\r") !== false) || (strpos($url, ';') !== false))
	{
		message_die(GENERAL_ERROR, 'Tried to redirect to potentially insecure url');
		//trigger_error('Tried to redirect to potentially insecure url.', E_USER_ERROR);
	}

	// Now, also check the protocol and for a valid url the last time...
	$allowed_protocols = array('http', 'https', 'ftp', 'ftps');
	$url_parts = parse_url($url);

	if (($url_parts === false) || empty($url_parts['scheme']) || !in_array($url_parts['scheme'], $allowed_protocols))
	{
		message_die(GENERAL_ERROR, 'Tried to redirect to potentially insecure url');
		//trigger_error('Tried to redirect to potentially insecure url.', E_USER_ERROR);
	}

	if ($return)
	{
		return $url;
	}

	if (strstr(urldecode($url), "\n") || strstr(urldecode($url), "\r") || strstr(urldecode($url), ';url'))
	{
		message_die(GENERAL_ERROR, 'Tried to redirect to potentially insecure url.');
	}

	$server_protocol = ($board_config['cookie_secure']) ? 'https://' : 'http://';
	$server_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($board_config['server_name']));
	$server_port = ($board_config['server_port'] <> 80) ? ':' . trim($board_config['server_port']) : '';
	$script_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($board_config['script_path']));
	$script_name = ($script_name == '') ? $_SERVER['REQUEST_URI'] : '/' . $script_name;
	$url = preg_replace('#^\/?(.*?)\/?$#', '/\1', trim($url));

	// Redirect via an HTML form for PITA webservers
	if (@preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE')))
	{
		header('Refresh: 0; URL=' . $server_protocol . $server_name . $server_port . $script_name . $url);
		
		$encoding_charset = !empty($lang['ENCODING']) ? $lang['ENCODING'] : 'UTF-8';
		$lang_dir = !empty($lang['DIRECTION']) ? $lang['DIRECTION'] : 'ltr';
		$header_lang = !empty($lang['HEADER_LANG']) ? $lang['HEADER_LANG'] : 'en-gb';
		$xml_header_lang = !empty($lang['HEADER_LANG_XML']) ? $lang['HEADER_LANG_XML'] : 'en-gb';
		
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		echo '<html xmlns="http://www.w3.org/1999/xhtml" dir="' . $lang_dir . '" lang="' . $header_lang . '" xml:lang="' . $xml_header_lang . '">';
		echo '<head>';
		echo '<meta http-equiv="content-type" content="text/html; charset=' . $encoding_charset . '" />';
		echo '<meta http-equiv="refresh" content="0; url=' . str_replace('&', '&amp;', $url) . '" />';
		echo '<title>' . $lang['Redirect'] . '</title>';
		echo '</head>';
		echo '<body>';
		echo '<div style="text-align: center;">' . sprintf($lang['Redirect_to'], '<a href="' . str_replace('&', '&amp;', $url) . '">', '</a>') . '</div>';
		echo '</body>';
		echo '</html>';

		exit;
	}

	// Behave as per HTTP/1.1 spec for others
	header('Location: ' . $server_protocol . $server_name . $server_port . $script_name . $url);
	exit;
}

/**
 * Extract current session page
 *
 * @param string $root_path current root path (phpbb_root_path)
 * @return array
 */
function extract_current_page($root_path)
{
	global $board_config;
	$page_array = array();

	// First of all, get the request uri...
	$script_name = preg_replace('/^\/?(.*?)\/?$/', "\\1", trim($_SERVER['SCRIPT_NAME']));
	$args = func_get_args();

	// If we are unable to get the script name we use REQUEST_URI as a failover and note it within the page array for easier support...
	if (!$script_name)
	{
		$script_name = htmlspecialchars_decode($_SERVER('REQUEST_URI'));
		$script_name = (($pos = strpos($script_name, '?')) !== false) ? substr($script_name, 0, $pos) : $script_name;
		$page_array['failover'] = 1;
	}

	// Replace backslashes and doubled slashes (could happen on some proxy setups)
	$script_name = str_replace(array('\\', '//'), '/', $script_name);

	// Now, remove the sid and let us get a clean query string...
	$use_args = array();

	// Since some browser do not encode correctly we need to do this with some "special" characters...
	// " -> %22, ' => %27, < -> %3C, > -> %3E
	$find = array('"', "'", '<', '>', '&quot;', '&lt;', '&gt;');
	$replace = array('%22', '%27', '%3C', '%3E', '%22', '%3C', '%3E');

	foreach ($args as $key => $argument)
	{
		if (strpos($argument, 'sid=') === 0)
		{
			continue;
		}

		$use_args[] = str_replace($find, $replace, $argument);
	}
	unset($args);

	// The following examples given are for an request uri of {path to the phpbb directory}/adm/index.php?i=10&b=2

	// The current query string
	$query_string = trim(implode('&', $use_args));

	// basenamed page name (for example: index.php)
	$page_name = (substr($script_name, -1, 1) == '/') ? '' : basename($script_name);
	$page_name = urlencode(htmlspecialchars($page_name));

	if (substr($root_path, 0, 2) === './' && strpos($root_path, '..') === false)
	{
		$root_dirs = explode('/', str_replace('\\', '/', rtrim($root_path, '/')));
		$page_dirs = explode('/', str_replace('\\', '/', '.'));
	}
	else
	{
		// current directory within the phpBB root (for example: adm)
		$root_dirs = explode('/', str_replace('\\', '/', phpbb_realpath($root_path)));
		$page_dirs = explode('/', str_replace('\\', '/', phpbb_realpath('./')));
	}

	$intersection = array_intersect_assoc($root_dirs, $page_dirs);

	$root_dirs = array_diff_assoc($root_dirs, $intersection);
	$page_dirs = array_diff_assoc($page_dirs, $intersection);

	$page_dir = str_repeat('../', count($root_dirs)) . implode('/', $page_dirs);

	if ($page_dir && substr($page_dir, -1, 1) == '/')
	{
		$page_dir = substr($page_dir, 0, -1);
	}

	// Current page from phpBB root (for example: adm/index.php?i=10&b=2)
	$page = (($page_dir) ? $page_dir . '/' : '') . $page_name;
	if ($query_string)
	{
		$page .= '?' . $query_string;
	}

	// The script path from the webroot to the current directory (for example: /phpBB3/adm/) : always prefixed with / and ends in /
	$script_path = dirname(__FILE__);

	// The script path from the webroot to the phpBB root (for example: /phpBB3/)
	$script_dirs = explode('/', $script_path);
	array_splice($script_dirs, -count($page_dirs));
	$root_script_path = implode('/', $script_dirs) . (count($root_dirs) ? '/' . implode('/', $root_dirs) : '');

	// We are on the base level (phpBB root == webroot), lets adjust the variables a bit...
	if (!$root_script_path)
	{
		$root_script_path = ($page_dir) ? str_replace($page_dir, '', $script_path) : $script_path;
	}

	$script_path .= (substr($script_path, -1, 1) == '/') ? '' : '/';
	$root_script_path .= (substr($root_script_path, -1, 1) == '/') ? '' : '/';

	$forum_id = request_var('f', 0);
	// maximum forum id value is maximum value of mediumint unsigned column
	$forum_id = ($forum_id > 0 && $forum_id < 16777215) ? $forum_id : 0;

	$page_array += array(
			'page_name'			=> $page_name,
			'page_dir'			=> $page_dir,

			'query_string'		=> $query_string,
			'script_path'		=> str_replace(' ', '%20', htmlspecialchars($script_path)),
			'root_script_path'	=> str_replace(' ', '%20', htmlspecialchars($root_script_path)),

			'page'				=> $page,
			'forum'				=> $forum_id,
		);

	return $page_array;
}

/**
* Get valid hostname/port. HTTP_HOST is used, SERVER_NAME if HTTP_HOST not present.
*/
function extract_current_hostname()
{
	global $board_config;
	
	/**
	* A wrapper for htmlspecialchars_decode
	* @Get hostname
	*/
	$host = (!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : ((!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME'));

	// Should be a string and lowered
	$host = (string) strtolower($host);

	// If host is equal the cookie domain or the server name (if config is set), then we assume it is valid
	if ((isset($board_config['cookie_domain']) && $host === $board_config['cookie_domain']) || (isset($board_config['server_name']) && $host === $board_config['server_name']))
	{
		return $host;
	}

	// Is the host actually a IP? If so, we use the IP... (IPv4)
	if (long2ip(ip2long($host)) === $host)
	{
		return $host;
	}

	// Now return the hostname (this also removes any port definition). The http:// is prepended to construct a valid URL, hosts never have a scheme assigned
	$host = @parse_url('http://' . $host);
	$host = (!empty($host['host'])) ? $host['host'] : '';

	// Remove any portions not removed by parse_url (#)
	$host = str_replace('#', '', $host);

	// If, by any means, the host is now empty, we will use a "best approach" way to guess one
	if (empty($host))
	{
		if (!empty($board_config['server_name']))
		{
			$host = $board_config['server_name'];
		}
		else if (!empty($board_config['cookie_domain']))
		{
			$host = (strpos($board_config['cookie_domain'], '.') === 0) ? substr($board_config['cookie_domain'], 1) : $board_config['cookie_domain'];
		}
		else
		{
			// Set to OS hostname or localhost
			$host = (function_exists('php_uname')) ? php_uname('n') : 'localhost';
		}
	}

	// It may be still no valid host, but for sure only a hostname (we may further expand on the cookie domain... if set)
	return $host;
}

?>