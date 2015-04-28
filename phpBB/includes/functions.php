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
* request_var
*
* Used to get passed variable
*/
function request_var($var_name, $default, $multibyte = false, $cookie = false)
{
	if (!$cookie && isset($_COOKIE[$var_name]))
	{
		if (!isset($_GET[$var_name]) && !isset($_POST[$var_name]))
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
* Sets a configuration option's value.
*
* Please note that this function does not update the is_dynamic value for
* an already existing config option.
*
* @param string $config_name   The configuration option's name
* @param string $config_value  New configuration value
* @param bool   $is_dynamic    Whether this variable should be cached (false) or
*                              if it changes too frequently (true) to be
*                              efficiently cached.
*
* @return null
*/
function set_config($config_name, $config_value, $is_dynamic = false)
{
	global $db, $cache, $config;

	$sql = 'UPDATE ' . CONFIG_TABLE . "
		SET config_value = '" . $db->sql_escape($config_value) . "'
		WHERE config_name = '" . $db->sql_escape($config_name) . "'";
	$db->sql_query($sql);

	if (!$db->sql_affectedrows() && !isset($config[$config_name]))
	{
		$sql = 'INSERT INTO ' . CONFIG_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'config_name'	=> $config_name,
			'config_value'	=> $config_value,
			'is_dynamic'	=> ($is_dynamic) ? 1 : 0));
		$db->sql_query($sql);
	}

	$config[$config_name] = $config_value;

	if (!$is_dynamic)
	{
		$cache->destroy('config');
	}
}

/**
* Increments an integer config value directly in the database.
*
* @param string $config_name   The configuration option's name
* @param int    $increment     Amount to increment by
* @param bool   $is_dynamic    Whether this variable should be cached (false) or
*                              if it changes too frequently (true) to be
*                              efficiently cached.
*
* @return null
*/
function set_config_count($config_name, $increment, $is_dynamic = false)
{
	global $db, $cache;

	switch ($db->sql_layer)
	{
		case 'firebird':
			// Precision must be from 1 to 18
			$sql_update = 'CAST(CAST(config_value as DECIMAL(18, 0)) + ' . (int) $increment . ' as VARCHAR(255))';
		break;

		case 'postgres':
			// Need to cast to text first for PostgreSQL 7.x
			$sql_update = 'CAST(CAST(config_value::text as DECIMAL(255, 0)) + ' . (int) $increment . ' as VARCHAR(255))';
		break;

		// MySQL, SQlite, mssql, mssql_odbc, oracle
		default:
			$sql_update = 'config_value + ' . (int) $increment;
		break;
	}

	$db->sql_query('UPDATE ' . CONFIG_TABLE . ' SET config_value = ' . $sql_update . " WHERE config_name = '" . $db->sql_escape($config_name) . "'");

	if (!$is_dynamic)
	{
		$cache->destroy('config');
	}
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
* @param string $extra additional entropy
*/
function unique_id($extra = 'c')
{
	static $dss_seeded = false;
	global $config;

	$val = $config['rand_seed'] . microtime();
	$val = md5($val);
	$config['rand_seed'] = md5($config['rand_seed'] . $val . $extra);

	if ($dss_seeded !== true && ($config['rand_seed_last_update'] < time() - rand(1,10)))
	{
		set_config('rand_seed_last_update', time(), true);
		set_config('rand_seed', $config['rand_seed'], true);
		$dss_seeded = true;
	}

	return substr($val, 4, 16);
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
* Return formatted string for filesizes
*
* @param mixed	$value			filesize in bytes
*								(non-negative number; int, float or string)
* @param bool	$string_only	true if language string should be returned
* @param array	$allowed_units	only allow these units (data array indexes)
*
* @return mixed					data array if $string_only is false
* @author bantu
*/
function get_formatted_filesize($value, $string_only = true, $allowed_units = false)
{
	global $user;

	$available_units = array(
		'tb' => array(
			'min' 		=> 1099511627776, // pow(2, 40)
			'index'		=> 4,
			'si_unit'	=> 'TB',
			'iec_unit'	=> 'TIB',
		),
		'gb' => array(
			'min' 		=> 1073741824, // pow(2, 30)
			'index'		=> 3,
			'si_unit'	=> 'GB',
			'iec_unit'	=> 'GIB',
		),
		'mb' => array(
			'min'		=> 1048576, // pow(2, 20)
			'index'		=> 2,
			'si_unit'	=> 'MB',
			'iec_unit'	=> 'MIB',
		),
		'kb' => array(
			'min'		=> 1024, // pow(2, 10)
			'index'		=> 1,
			'si_unit'	=> 'KB',
			'iec_unit'	=> 'KIB',
		),
		'b' => array(
			'min'		=> 0,
			'index'		=> 0,
			'si_unit'	=> 'BYTES', // Language index
			'iec_unit'	=> 'BYTES',  // Language index
		),
	);

	foreach ($available_units as $si_identifier => $unit_info)
	{
		if (!empty($allowed_units) && $si_identifier != 'b' && !in_array($si_identifier, $allowed_units))
		{
			continue;
		}

		if ($value >= $unit_info['min'])
		{
			$unit_info['si_identifier'] = $si_identifier;

			break;
		}
	}
	unset($available_units);

	for ($i = 0; $i < $unit_info['index']; $i++)
	{
		$value /= 1024;
	}
	$value = round($value, 2);

	// Lookup units in language dictionary
	$unit_info['si_unit'] = (isset($user->lang[$unit_info['si_unit']])) ? $user->lang[$unit_info['si_unit']] : $unit_info['si_unit'];
	$unit_info['iec_unit'] = (isset($user->lang[$unit_info['iec_unit']])) ? $user->lang[$unit_info['iec_unit']] : $unit_info['iec_unit'];

	// Default to IEC
	$unit_info['unit'] = $unit_info['iec_unit'];

	if (!$string_only)
	{
		$unit_info['value'] = $value;

		return $unit_info;
	}

	return $value  . ' ' . $unit_info['unit'];
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
*
* @version Version 0.1 / slightly modified for phpBB 3.0.x (using $H$ as hash type identifier)
*
* Portable PHP password hashing framework.
*
* Written by Solar Designer <solar at openwall.com> in 2004-2006 and placed in
* the public domain.
*
* There's absolutely no warranty.
*
* The homepage URL for this framework is:
*
*	http://www.openwall.com/phpass/
*
* Please be sure to update the Version line if you edit this file in any way.
* It is suggested that you leave the main version number intact, but indicate
* your project name (after the slash) and add your own revision information.
*
* Please do not change the "private" password hashing method implemented in
* here, thereby making your hashes incompatible.  However, if you must, please
* change the hash type identifier (the "$P$") to something different.
*
* Obviously, since this code is in the public domain, the above are not
* requirements (there can be none), but merely suggestions.
*
*
* Hash the password
*/
function phpbb_hash($password)
{
	$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

	$random_state = unique_id();
	$random = '';
	$count = 6;

	if (($fh = @fopen('/dev/urandom', 'rb')))
	{
		$random = fread($fh, $count);
		fclose($fh);
	}

	if (strlen($random) < $count)
	{
		$random = '';

		for ($i = 0; $i < $count; $i += 16)
		{
			$random_state = md5(unique_id() . $random_state);
			$random .= pack('H*', md5($random_state));
		}
		$random = substr($random, 0, $count);
	}

	$hash = _hash_crypt_private($password, _hash_gensalt_private($random, $itoa64), $itoa64);

	if (strlen($hash) == 34)
	{
		return $hash;
	}

	return md5($password);
}

/**
* Check for correct password
*
* @param string $password The password in plain text
* @param string $hash The stored password hash
*
* @return bool Returns true if the password is correct, false if not.
*/
function phpbb_check_hash($password, $hash)
{
	if (strlen($password) > 4096)
	{
		// If the password is too huge, we will simply reject it
		// and not let the server try to hash it.
		return false;
	}

	$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	if (strlen($hash) == 34)
	{
		return (_hash_crypt_private($password, $hash, $itoa64) === $hash) ? true : false;
	}

	return (md5($password) === $hash) ? true : false;
}

/**
* Generate salt for hash generation
*/
function _hash_gensalt_private($input, &$itoa64, $iteration_count_log2 = 6)
{
	if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31)
	{
		$iteration_count_log2 = 8;
	}

	$output = '$H$';
	$output .= $itoa64[min($iteration_count_log2 + ((PHP_VERSION >= 5) ? 5 : 3), 30)];
	$output .= _hash_encode64($input, 6, $itoa64);

	return $output;
}

/**
* Encode hash
*/
function _hash_encode64($input, $count, &$itoa64)
{
	$output = '';
	$i = 0;

	do
	{
		$value = ord($input[$i++]);
		$output .= $itoa64[$value & 0x3f];

		if ($i < $count)
		{
			$value |= ord($input[$i]) << 8;
		}

		$output .= $itoa64[($value >> 6) & 0x3f];

		if ($i++ >= $count)
		{
			break;
		}

		if ($i < $count)
		{
			$value |= ord($input[$i]) << 16;
		}

		$output .= $itoa64[($value >> 12) & 0x3f];

		if ($i++ >= $count)
		{
			break;
		}

		$output .= $itoa64[($value >> 18) & 0x3f];
	}
	while ($i < $count);

	return $output;
}

/**
* The crypt function/replacement
*/
function _hash_crypt_private($password, $setting, &$itoa64)
{
	$output = '*';

	// Check for correct hash
	if (substr($setting, 0, 3) != '$H$' && substr($setting, 0, 3) != '$P$')
	{
		return $output;
	}

	$count_log2 = strpos($itoa64, $setting[3]);

	if ($count_log2 < 7 || $count_log2 > 30)
	{
		return $output;
	}

	$count = 1 << $count_log2;
	$salt = substr($setting, 4, 8);

	if (strlen($salt) != 8)
	{
		return $output;
	}

	/**
	* We're kind of forced to use MD5 here since it's the only
	* cryptographic primitive available in all versions of PHP
	* currently in use.  To implement our own low-level crypto
	* in PHP would result in much worse performance and
	* consequently in lower iteration counts and hashes that are
	* quicker to crack (by non-PHP code).
	*/
	if (PHP_VERSION >= 5)
	{
		$hash = md5($salt . $password, true);
		do
		{
			$hash = md5($hash . $password, true);
		}
		while (--$count);
	}
	else
	{
		$hash = pack('H*', md5($salt . $password));
		do
		{
			$hash = pack('H*', md5($hash . $password));
		}
		while (--$count);
	}

	$output = substr($setting, 0, 12);
	$output .= _hash_encode64($hash, 16, $itoa64);

	return $output;
}

/**
* Hashes an email address to a big integer
*
* @param string $email		Email address
*
* @return string			Unsigned Big Integer
*/
function phpbb_email_hash($email)
{
	return sprintf('%u', crc32(strtolower($email))) . strlen($email);
}

/**
* Wrapper for version_compare() that allows using uppercase A and B
* for alpha and beta releases.
*
* See http://www.php.net/manual/en/function.version-compare.php
*
* @param string $version1		First version number
* @param string $version2		Second version number
* @param string $operator		Comparison operator (optional)
*
* @return mixed					Boolean (true, false) if comparison operator is specified.
*								Integer (-1, 0, 1) otherwise.
*/
function phpbb_version_compare($version1, $version2, $operator = null)
{
	$version1 = strtolower($version1);
	$version2 = strtolower($version2);

	if (is_null($operator))
	{
		return version_compare($version1, $version2);
	}
	else
	{
		return version_compare($version1, $version2, $operator);
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
* @author faw, phpBB Group
*/
function phpbb_chmod($filename, $perms = CHMOD_READ)
{
	static $_chmod_info;

	// Return if the file no longer exists.
	if (!file_exists($filename))
	{
		return false;
	}

	// Determine some common vars
	if (empty($_chmod_info))
	{
		if (!function_exists('fileowner') || !function_exists('filegroup'))
		{
			// No need to further determine owner/group - it is unknown
			$_chmod_info['process'] = false;
		}
		else
		{
			global $phpbb_root_path, $phpEx;

			// Determine owner/group of common.php file and the filename we want to change here
			$common_php_owner = @fileowner($phpbb_root_path . 'common.' . $phpEx);
			$common_php_group = @filegroup($phpbb_root_path . 'common.' . $phpEx);

			// And the owner and the groups PHP is running under.
			$php_uid = (function_exists('posix_getuid')) ? @posix_getuid() : false;
			$php_gids = (function_exists('posix_getgroups')) ? @posix_getgroups() : false;

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
		$file_uid = @fileowner($filename);
		$file_gid = @filegroup($filename);

		// Change owner
		if (@chown($filename, $_chmod_info['common_owner']))
		{
			clearstatcache();
			$file_uid = @fileowner($filename);
		}

		// Change group
		if (@chgrp($filename, $_chmod_info['common_group']))
		{
			clearstatcache();
			$file_gid = @filegroup($filename);
		}

		// If the file_uid/gid now match the one from common.php we can process further, else we are not able to change something
		if ($file_uid != $_chmod_info['common_owner'] || $file_gid != $_chmod_info['common_group'])
		{
			$_chmod_info['process'] = false;
		}
	}

	// Still able to process?
	if ($_chmod_info['process'])
	{
		if ($file_uid == $_chmod_info['php_uid'])
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

	// Owner always has read/write permission
	$owner = CHMOD_READ | CHMOD_WRITE;
	if (is_dir($filename))
	{
		$owner |= CHMOD_EXECUTE;

		// Only add execute bit to the permission if the dir needs to be readable
		if ($perms & CHMOD_READ)
		{
			$perms |= CHMOD_EXECUTE;
		}
	}

	switch ($php)
	{
		case 'owner':
			$result = @chmod($filename, ($owner << 6) + (0 << 3) + (0 << 0));

			clearstatcache();

			if (is_readable($filename) && phpbb_is_writable($filename))
			{
				break;
			}

		case 'group':
			$result = @chmod($filename, ($owner << 6) + ($perms << 3) + (0 << 0));

			clearstatcache();

			if ((!($perms & CHMOD_READ) || is_readable($filename)) && (!($perms & CHMOD_WRITE) || phpbb_is_writable($filename)))
			{
				break;
			}

		case 'other':
			$result = @chmod($filename, ($owner << 6) + ($perms << 3) + ($perms << 0));

			clearstatcache();

			if ((!($perms & CHMOD_READ) || is_readable($filename)) && (!($perms & CHMOD_WRITE) || phpbb_is_writable($filename)))
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
* Test if a file/directory is writable
*
* This function calls the native is_writable() when not running under
* Windows and it is not disabled.
*
* @param string $file Path to perform write test on
* @return bool True when the path is writable, otherwise false.
*/
function phpbb_is_writable($file)
{
	if (strtolower(substr(PHP_OS, 0, 3)) === 'win' || !function_exists('is_writable'))
	{
		if (file_exists($file))
		{
			// Canonicalise path to absolute path
			$file = phpbb_realpath($file);

			if (is_dir($file))
			{
				// Test directory by creating a file inside the directory
				$result = @tempnam($file, 'i_w');

				if (is_string($result) && file_exists($result))
				{
					unlink($result);

					// Ensure the file is actually in the directory (returned realpathed)
					return (strpos($result, $file) === 0) ? true : false;
				}
			}
			else
			{
				$handle = @fopen($file, 'r+');

				if (is_resource($handle))
				{
					fclose($handle);
					return true;
				}
			}
		}
		else
		{
			// file does not exist test if we can write to the directory
			$dir = dirname($file);

			if (file_exists($dir) && is_dir($dir) && phpbb_is_writable($dir))
			{
				return true;
			}
		}

		return false;
	}
	else
	{
		return is_writable($file);
	}
}

// Compatibility functions

if (!function_exists('array_combine'))
{
	/**
	* A wrapper for the PHP5 function array_combine()
	* @param array $keys contains keys for the resulting array
	* @param array $values contains values for the resulting array
	*
	* @return Returns an array by using the values from the keys array as keys and the
	* 	values from the values array as the corresponding values. Returns false if the
	* 	number of elements for each array isn't equal or if the arrays are empty.
	*/
	function array_combine($keys, $values)
	{
		$keys = array_values($keys);
		$values = array_values($values);

		$n = sizeof($keys);
		$m = sizeof($values);
		if (!$n || !$m || ($n != $m))
		{
			return false;
		}

		$combined = array();
		for ($i = 0; $i < $n; $i++)
		{
			$combined[$keys[$i]] = $values[$i];
		}
		return $combined;
	}
}

if (!function_exists('str_split'))
{
	/**
	* A wrapper for the PHP5 function str_split()
	* @param array $string contains the string to be converted
	* @param array $split_length contains the length of each chunk
	*
	* @return  Converts a string to an array. If the optional split_length parameter is specified,
	*  	the returned array will be broken down into chunks with each being split_length in length,
	*  	otherwise each chunk will be one character in length. FALSE is returned if split_length is
	*  	less than 1. If the split_length length exceeds the length of string, the entire string is
	*  	returned as the first (and only) array element.
	*/
	function str_split($string, $split_length = 1)
	{
		if ($split_length < 1)
		{
			return false;
		}
		else if ($split_length >= strlen($string))
		{
			return array($string);
		}
		else
		{
			preg_match_all('#.{1,' . $split_length . '}#s', $string, $matches);
			return $matches[0];
		}
	}
}

if (!function_exists('stripos'))
{
	/**
	* A wrapper for the PHP5 function stripos
	* Find position of first occurrence of a case-insensitive string
	*
	* @param string $haystack is the string to search in
	* @param string $needle is the string to search for
	*
	* @return mixed Returns the numeric position of the first occurrence of needle in the haystack string. Unlike strpos(), stripos() is case-insensitive.
	* Note that the needle may be a string of one or more characters.
	* If needle is not found, stripos() will return boolean FALSE.
	*/
	function stripos($haystack, $needle)
	{
		if (preg_match('#' . preg_quote($needle, '#') . '#i', $haystack, $m))
		{
			return strpos($haystack, $m[0]);
		}

		return false;
	}
}

/**
* Checks if a path ($path) is absolute or relative
*
* @param string $path Path to check absoluteness of
* @return boolean
*/
function is_absolute($path)
{
	return (isset($path[0]) && $path[0] == '/' || preg_match('#^[a-z]:[/\\\]#i', $path)) ? true : false;
}

/**
* @author Chris Smith <chris@project-minerva.org>
* @copyright 2006 Project Minerva Team
* @param string $path The path which we should attempt to resolve.
* @return mixed
*/
function phpbb_own_realpath($path)
{
	// Now to perform funky shizzle

	// Switch to use UNIX slashes
	$path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
	$path_prefix = '';

	// Determine what sort of path we have
	if (is_absolute($path))
	{
		$absolute = true;

		if ($path[0] == '/')
		{
			// Absolute path, *NIX style
			$path_prefix = '';
		}
		else
		{
			// Absolute path, Windows style
			// Remove the drive letter and colon
			$path_prefix = $path[0] . ':';
			$path = substr($path, 2);
		}
	}
	else
	{
		// Relative Path
		// Prepend the current working directory
		if (function_exists('getcwd'))
		{
			// This is the best method, hopefully it is enabled!
			$path = str_replace(DIRECTORY_SEPARATOR, '/', getcwd()) . '/' . $path;
			$absolute = true;
			if (preg_match('#^[a-z]:#i', $path))
			{
				$path_prefix = $path[0] . ':';
				$path = substr($path, 2);
			}
			else
			{
				$path_prefix = '';
			}
		}
		else if (isset($_SERVER['SCRIPT_FILENAME']) && !empty($_SERVER['SCRIPT_FILENAME']))
		{
			// Warning: If chdir() has been used this will lie!
			// Warning: This has some problems sometime (CLI can create them easily)
			$path = str_replace(DIRECTORY_SEPARATOR, '/', dirname($_SERVER['SCRIPT_FILENAME'])) . '/' . $path;
			$absolute = true;
			$path_prefix = '';
		}
		else
		{
			// We have no way of getting the absolute path, just run on using relative ones.
			$absolute = false;
			$path_prefix = '.';
		}
	}

	// Remove any repeated slashes
	$path = preg_replace('#/{2,}#', '/', $path);

	// Remove the slashes from the start and end of the path
	$path = trim($path, '/');

	// Break the string into little bits for us to nibble on
	$bits = explode('/', $path);

	// Remove any . in the path, renumber array for the loop below
	$bits = array_values(array_diff($bits, array('.')));

	// Lets get looping, run over and resolve any .. (up directory)
	for ($i = 0, $max = sizeof($bits); $i < $max; $i++)
	{
		// @todo Optimise
		if ($bits[$i] == '..' )
		{
			if (isset($bits[$i - 1]))
			{
				if ($bits[$i - 1] != '..')
				{
					// We found a .. and we are able to traverse upwards, lets do it!
					unset($bits[$i]);
					unset($bits[$i - 1]);
					$i -= 2;
					$max -= 2;
					$bits = array_values($bits);
				}
			}
			else if ($absolute) // ie. !isset($bits[$i - 1]) && $absolute
			{
				// We have an absolute path trying to descend above the root of the filesystem
				// ... Error!
				return false;
			}
		}
	}

	// Prepend the path prefix
	array_unshift($bits, $path_prefix);

	$resolved = '';

	$max = sizeof($bits) - 1;

	// Check if we are able to resolve symlinks, Windows cannot.
	$symlink_resolve = (function_exists('readlink')) ? true : false;

	foreach ($bits as $i => $bit)
	{
		if (@is_dir("$resolved/$bit") || ($i == $max && @is_file("$resolved/$bit")))
		{
			// Path Exists
			if ($symlink_resolve && is_link("$resolved/$bit") && ($link = readlink("$resolved/$bit")))
			{
				// Resolved a symlink.
				$resolved = $link . (($i == $max) ? '' : '/');
				continue;
			}
		}
		else
		{
			// Something doesn't exist here!
			// This is correct realpath() behaviour but sadly open_basedir and safe_mode make this problematic
			// return false;
		}
		$resolved .= $bit . (($i == $max) ? '' : '/');
	}

	// @todo If the file exists fine and open_basedir only has one path we should be able to prepend it
	// because we must be inside that basedir, the question is where...
	// @internal The slash in is_dir() gets around an open_basedir restriction
	if (!@file_exists($resolved) || (!@is_dir($resolved . '/') && !is_file($resolved)))
	{
		return false;
	}

	// Put the slashes back to the native operating systems slashes
	$resolved = str_replace('/', DIRECTORY_SEPARATOR, $resolved);

	// Check for DIRECTORY_SEPARATOR at the end (and remove it!)
	if (substr($resolved, -1) == DIRECTORY_SEPARATOR)
	{
		return substr($resolved, 0, -1);
	}

	return $resolved; // We got here, in the end!
}

if (!function_exists('realpath'))
{
	/**
	* A wrapper for realpath
	* @ignore
	*/
	function phpbb_realpath($path)
	{
		return phpbb_own_realpath($path);
	}
}
else
{
	/**
	* A wrapper for realpath
	*/
	function phpbb_realpath($path)
	{
		$realpath = realpath($path);

		// Strangely there are provider not disabling realpath but returning strange values. :o
		// We at least try to cope with them.
		if ($realpath === $path || $realpath === false)
		{
			return phpbb_own_realpath($path);
		}

		// Check for DIRECTORY_SEPARATOR at the end (and remove it!)
		if (substr($realpath, -1) == DIRECTORY_SEPARATOR)
		{
			$realpath = substr($realpath, 0, -1);
		}

		return $realpath;
	}
}

/**
* Eliminates useless . and .. components from specified path.
*
* @param string $path Path to clean
* @return string Cleaned path
*/
function phpbb_clean_path($path)
{
	$exploded = explode('/', $path);
	$filtered = array();
	foreach ($exploded as $part)
	{
		if ($part === '.' && !empty($filtered))
		{
			continue;
		}

		if ($part === '..' && !empty($filtered) && $filtered[sizeof($filtered) - 1] !== '..')
		{
			array_pop($filtered);
		}
		else
		{
			$filtered[] = $part;
		}
	}
	$path = implode('/', $filtered);
	return $path;
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

// functions used for building option fields

/**
* Pick a language, any language ...
*/
function language_select($default = '')
{
	global $db;

	$sql = 'SELECT lang_iso, lang_local_name
		FROM ' . LANG_TABLE . '
		ORDER BY lang_english_name';
	$result = $db->sql_query($sql);

	$lang_options = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$selected = ($row['lang_iso'] == $default) ? ' selected="selected"' : '';
		$lang_options .= '<option value="' . $row['lang_iso'] . '"' . $selected . '>' . $row['lang_local_name'] . '</option>';
	}
	$db->sql_freeresult($result);

	return $lang_options;
}

/**
* Pick a template/theme combo,
*/
function style_select($default = '', $all = false)
{
	global $db;

	$sql_where = (!$all) ? 'WHERE style_active = 1 ' : '';
	$sql = 'SELECT style_id, style_name
		FROM ' . STYLES_TABLE . "
		$sql_where
		ORDER BY style_name";
	$result = $db->sql_query($sql);

	$style_options = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$selected = ($row['style_id'] == $default) ? ' selected="selected"' : '';
		$style_options .= '<option value="' . $row['style_id'] . '"' . $selected . '>' . $row['style_name'] . '</option>';
	}
	$db->sql_freeresult($result);

	return $style_options;
}

/**
* Pick a timezone
*/
function tz_select($default = '', $truncate = false)
{
	global $user;

	$tz_select = '';
	foreach ($user->lang['tz_zones'] as $offset => $zone)
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
			$tz_select .= '<option title="' . $zone . '" value="' . $offset . '"' . $selected . '>' . $zone_trunc . '</option>';
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
	global $db, $user, $config;

	if ($mode == 'all')
	{
		if ($forum_id === false || !sizeof($forum_id))
		{
			if ($config['load_db_lastread'] && $user->data['is_registered'])
			{
				// Mark all forums read (index page)
				$db->sql_query('DELETE FROM ' . TOPICS_TRACK_TABLE . " WHERE user_id = {$user->data['user_id']}");
				$db->sql_query('DELETE FROM ' . FORUMS_TRACK_TABLE . " WHERE user_id = {$user->data['user_id']}");
				$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_lastmark = ' . time() . " WHERE user_id = {$user->data['user_id']}");
			}
			else if ($config['load_anon_lastread'] || $user->data['is_registered'])
			{
				$tracking_topics = (isset($_COOKIE[$config['cookie_name'] . '_track'])) ? ((STRIP) ? stripslashes($_COOKIE[$config['cookie_name'] . '_track']) : $_COOKIE[$config['cookie_name'] . '_track']) : '';
				$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();

				unset($tracking_topics['tf']);
				unset($tracking_topics['t']);
				unset($tracking_topics['f']);
				$tracking_topics['l'] = base_convert(time() - $config['board_startdate'], 10, 36);

				$user->set_cookie('track', tracking_serialize($tracking_topics), time() + 31536000);
				$_COOKIE[$config['cookie_name'] . '_track'] = (STRIP) ? addslashes(tracking_serialize($tracking_topics)) : tracking_serialize($tracking_topics);

				unset($tracking_topics);

				if ($user->data['is_registered'])
				{
					$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_lastmark = ' . time() . " WHERE user_id = {$user->data['user_id']}");
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
		// $forum_id[] = 0;

		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			$sql = 'DELETE FROM ' . TOPICS_TRACK_TABLE . "
				WHERE user_id = {$user->data['user_id']}
					AND " . $db->sql_in_set('forum_id', $forum_id);
			$db->sql_query($sql);

			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TRACK_TABLE . "
				WHERE user_id = {$user->data['user_id']}
					AND " . $db->sql_in_set('forum_id', $forum_id);
			$result = $db->sql_query($sql);

			$sql_update = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$sql_update[] = (int) $row['forum_id'];
			}
			$db->sql_freeresult($result);

			if (sizeof($sql_update))
			{
				$sql = 'UPDATE ' . FORUMS_TRACK_TABLE . '
					SET mark_time = ' . time() . "
					WHERE user_id = {$user->data['user_id']}
						AND " . $db->sql_in_set('forum_id', $sql_update);
				$db->sql_query($sql);
			}

			if ($sql_insert = array_diff($forum_id, $sql_update))
			{
				$sql_ary = array();
				foreach ($sql_insert as $f_id)
				{
					$sql_ary[] = array(
						'user_id'	=> (int) $user->data['user_id'],
						'forum_id'	=> (int) $f_id,
						'mark_time'	=> time()
					);
				}

				$db->sql_multi_insert(FORUMS_TRACK_TABLE, $sql_ary);
			}
		}
		else if ($config['load_anon_lastread'] || $user->data['is_registered'])
		{
			$tracking = (isset($_COOKIE[$config['cookie_name'] . '_track'])) ? ((STRIP) ? stripslashes($_COOKIE[$config['cookie_name'] . '_track']) : $_COOKIE[$config['cookie_name'] . '_track']) : '';
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

				$tracking['f'][$f_id] = base_convert(time() - $config['board_startdate'], 10, 36);
			}

			if (isset($tracking['tf']) && empty($tracking['tf']))
			{
				unset($tracking['tf']);
			}

			$user->set_cookie('track', tracking_serialize($tracking), time() + 31536000);
			$_COOKIE[$config['cookie_name'] . '_track'] = (STRIP) ? addslashes(tracking_serialize($tracking)) : tracking_serialize($tracking);

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

		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			$sql = 'UPDATE ' . TOPICS_TRACK_TABLE . '
				SET mark_time = ' . (($post_time) ? $post_time : time()) . "
				WHERE user_id = {$user->data['user_id']}
					AND topic_id = $topic_id";
			$db->sql_query($sql);

			// insert row
			if (!$db->sql_affectedrows())
			{
				$db->sql_return_on_error(true);

				$sql_ary = array(
					'user_id'		=> (int) $user->data['user_id'],
					'topic_id'		=> (int) $topic_id,
					'forum_id'		=> (int) $forum_id,
					'mark_time'		=> ($post_time) ? (int) $post_time : time(),
				);

				$db->sql_query('INSERT INTO ' . TOPICS_TRACK_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

				$db->sql_return_on_error(false);
			}
		}
		else if ($config['load_anon_lastread'] || $user->data['is_registered'])
		{
			$tracking = (isset($_COOKIE[$config['cookie_name'] . '_track'])) ? ((STRIP) ? stripslashes($_COOKIE[$config['cookie_name'] . '_track']) : $_COOKIE[$config['cookie_name'] . '_track']) : '';
			$tracking = ($tracking) ? tracking_unserialize($tracking) : array();

			$topic_id36 = base_convert($topic_id, 10, 36);

			if (!isset($tracking['t'][$topic_id36]))
			{
				$tracking['tf'][$forum_id][$topic_id36] = true;
			}

			$post_time = ($post_time) ? $post_time : time();
			$tracking['t'][$topic_id36] = base_convert($post_time - $config['board_startdate'], 10, 36);

			// If the cookie grows larger than 10000 characters we will remove the smallest value
			// This can result in old topics being unread - but most of the time it should be accurate...
			if (isset($_COOKIE[$config['cookie_name'] . '_track']) && strlen($_COOKIE[$config['cookie_name'] . '_track']) > 10000)
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

				if ($user->data['is_registered'])
				{
					$user->data['user_lastmark'] = intval(base_convert(max($time_keys) + $config['board_startdate'], 36, 10));
					$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_lastmark = ' . $user->data['user_lastmark'] . " WHERE user_id = {$user->data['user_id']}");
				}
				else
				{
					$tracking['l'] = max($time_keys);
				}
			}

			$user->set_cookie('track', tracking_serialize($tracking), time() + 31536000);
			$_COOKIE[$config['cookie_name'] . '_track'] = (STRIP) ? addslashes(tracking_serialize($tracking)) : tracking_serialize($tracking);
		}

		return;
	}
	else if ($mode == 'post')
	{
		if ($topic_id === false)
		{
			return;
		}

		$use_user_id = (!$user_id) ? $user->data['user_id'] : $user_id;

		if ($config['load_db_track'] && $use_user_id != ANONYMOUS)
		{
			$db->sql_return_on_error(true);

			$sql_ary = array(
				'user_id'		=> (int) $use_user_id,
				'topic_id'		=> (int) $topic_id,
				'topic_posted'	=> 1
			);

			$db->sql_query('INSERT INTO ' . TOPICS_POSTED_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

			$db->sql_return_on_error(false);
		}

		return;
	}
}

/**
* Get topic tracking info by using already fetched info
*/
function get_topic_tracking($forum_id, $topic_ids, &$rowset, $forum_mark_time, $global_announce_list = false)
{
	global $config, $user;

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
				global $db;

				$sql = 'SELECT mark_time
					FROM ' . FORUMS_TRACK_TABLE . "
					WHERE user_id = {$user->data['user_id']}
						AND forum_id = 0";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

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

		$user_lastmark = (isset($mark_time[$forum_id])) ? $mark_time[$forum_id] : $user->data['user_lastmark'];

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
	global $config, $user;

	$last_read = array();

	if (!is_array($topic_ids))
	{
		$topic_ids = array($topic_ids);
	}

	if ($config['load_db_lastread'] && $user->data['is_registered'])
	{
		global $db;

		$sql = 'SELECT topic_id, mark_time
			FROM ' . TOPICS_TRACK_TABLE . "
			WHERE user_id = {$user->data['user_id']}
				AND " . $db->sql_in_set('topic_id', $topic_ids);
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$last_read[$row['topic_id']] = $row['mark_time'];
		}
		$db->sql_freeresult($result);

		$topic_ids = array_diff($topic_ids, array_keys($last_read));

		if (sizeof($topic_ids))
		{
			$sql = 'SELECT forum_id, mark_time
				FROM ' . FORUMS_TRACK_TABLE . "
				WHERE user_id = {$user->data['user_id']}
					AND forum_id " .
					(($global_announce_list && sizeof($global_announce_list)) ? "IN (0, $forum_id)" : "= $forum_id");
			$result = $db->sql_query($sql);

			$mark_time = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$mark_time[$row['forum_id']] = $row['mark_time'];
			}
			$db->sql_freeresult($result);

			$user_lastmark = (isset($mark_time[$forum_id])) ? $mark_time[$forum_id] : $user->data['user_lastmark'];

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
	else if ($config['load_anon_lastread'] || $user->data['is_registered'])
	{
		global $tracking_topics;

		if (!isset($tracking_topics) || !sizeof($tracking_topics))
		{
			$tracking_topics = (isset($_COOKIE[$config['cookie_name'] . '_track'])) ? ((STRIP) ? stripslashes($_COOKIE[$config['cookie_name'] . '_track']) : $_COOKIE[$config['cookie_name'] . '_track']) : '';
			$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();
		}

		if (!$user->data['is_registered'])
		{
			$user_lastmark = (isset($tracking_topics['l'])) ? base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate'] : 0;
		}
		else
		{
			$user_lastmark = $user->data['user_lastmark'];
		}

		foreach ($topic_ids as $topic_id)
		{
			$topic_id36 = base_convert($topic_id, 10, 36);

			if (isset($tracking_topics['t'][$topic_id36]))
			{
				$last_read[$topic_id] = base_convert($tracking_topics['t'][$topic_id36], 36, 10) + $config['board_startdate'];
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
					$mark_time[0] = base_convert($tracking_topics['f'][0], 36, 10) + $config['board_startdate'];
				}
			}

			if (isset($tracking_topics['f'][$forum_id]))
			{
				$mark_time[$forum_id] = base_convert($tracking_topics['f'][$forum_id], 36, 10) + $config['board_startdate'];
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
* Get list of unread topics
*
* @param int $user_id			User ID (or false for current user)
* @param string $sql_extra		Extra WHERE SQL statement
* @param string $sql_sort		ORDER BY SQL sorting statement
* @param string $sql_limit		Limits the size of unread topics list, 0 for unlimited query
* @param string $sql_limit_offset  Sets the offset of the first row to search, 0 to search from the start
*
* @return array[int][int]		Topic ids as keys, mark_time of topic as value
*/
function get_unread_topics($user_id = false, $sql_extra = '', $sql_sort = '', $sql_limit = 1001, $sql_limit_offset = 0)
{
	global $config, $db, $user;

	$user_id = ($user_id === false) ? (int) $user->data['user_id'] : (int) $user_id;

	// Data array we're going to return
	$unread_topics = array();

	if (empty($sql_sort))
	{
		$sql_sort = 'ORDER BY t.topic_last_post_time DESC';
	}

	if ($config['load_db_lastread'] && $user->data['is_registered'])
	{
		// Get list of the unread topics
		$last_mark = (int) $user->data['user_lastmark'];

		$sql_array = array(
			'SELECT'		=> 't.topic_id, t.topic_last_post_time, tt.mark_time as topic_mark_time, ft.mark_time as forum_mark_time',

			'FROM'			=> array(TOPICS_TABLE => 't'),

			'LEFT_JOIN'		=> array(
				array(
					'FROM'	=> array(TOPICS_TRACK_TABLE => 'tt'),
					'ON'	=> "tt.user_id = $user_id AND t.topic_id = tt.topic_id",
				),
				array(
					'FROM'	=> array(FORUMS_TRACK_TABLE => 'ft'),
					'ON'	=> "ft.user_id = $user_id AND t.forum_id = ft.forum_id",
				),
			),

			'WHERE'			=> "
				 t.topic_last_post_time > $last_mark AND
				(
				(tt.mark_time IS NOT NULL AND t.topic_last_post_time > tt.mark_time) OR
				(tt.mark_time IS NULL AND ft.mark_time IS NOT NULL AND t.topic_last_post_time > ft.mark_time) OR
				(tt.mark_time IS NULL AND ft.mark_time IS NULL)
				)
				$sql_extra
				$sql_sort",
		);

		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query_limit($sql, $sql_limit, $sql_limit_offset);

		while ($row = $db->sql_fetchrow($result))
		{
			$topic_id = (int) $row['topic_id'];
			$unread_topics[$topic_id] = ($row['topic_mark_time']) ? (int) $row['topic_mark_time'] : (($row['forum_mark_time']) ? (int) $row['forum_mark_time'] : $last_mark);
		}
		$db->sql_freeresult($result);
	}
	else if ($config['load_anon_lastread'] || $user->data['is_registered'])
	{
		global $tracking_topics;

		if (empty($tracking_topics))
		{
			$tracking_topics = request_var($config['cookie_name'] . '_track', '', false, true);
			$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();
		}

		if (!$user->data['is_registered'])
		{
			$user_lastmark = (isset($tracking_topics['l'])) ? base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate'] : 0;
		}
		else
		{
			$user_lastmark = (int) $user->data['user_lastmark'];
		}

		$sql = 'SELECT t.topic_id, t.forum_id, t.topic_last_post_time
			FROM ' . TOPICS_TABLE . ' t
			WHERE t.topic_last_post_time > ' . $user_lastmark . "
			$sql_extra
			$sql_sort";
		$result = $db->sql_query_limit($sql, $sql_limit, $sql_limit_offset);

		while ($row = $db->sql_fetchrow($result))
		{
			$forum_id = (int) $row['forum_id'];
			$topic_id = (int) $row['topic_id'];
			$topic_id36 = base_convert($topic_id, 10, 36);

			if (isset($tracking_topics['t'][$topic_id36]))
			{
				$last_read = base_convert($tracking_topics['t'][$topic_id36], 36, 10) + $config['board_startdate'];

				if ($row['topic_last_post_time'] > $last_read)
				{
					$unread_topics[$topic_id] = $last_read;
				}
			}
			else if (isset($tracking_topics['f'][$forum_id]))
			{
				$mark_time = base_convert($tracking_topics['f'][$forum_id], 36, 10) + $config['board_startdate'];

				if ($row['topic_last_post_time'] > $mark_time)
				{
					$unread_topics[$topic_id] = $mark_time;
				}
			}
			else
			{
				$unread_topics[$topic_id] = $user_lastmark;
			}
		}
		$db->sql_freeresult($result);
	}

	return $unread_topics;
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
	global $db, $tracking_topics, $user, $config, $auth;

	// Determine the users last forum mark time if not given.
	if ($mark_time_forum === false)
	{
		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			$mark_time_forum = (!empty($f_mark_time)) ? $f_mark_time : $user->data['user_lastmark'];
		}
		else if ($config['load_anon_lastread'] || $user->data['is_registered'])
		{
			$tracking_topics = (isset($_COOKIE[$config['cookie_name'] . '_track'])) ? ((STRIP) ? stripslashes($_COOKIE[$config['cookie_name'] . '_track']) : $_COOKIE[$config['cookie_name'] . '_track']) : '';
			$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();

			if (!$user->data['is_registered'])
			{
				$user->data['user_lastmark'] = (isset($tracking_topics['l'])) ? (int) (base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate']) : 0;
			}

			$mark_time_forum = (isset($tracking_topics['f'][$forum_id])) ? (int) (base_convert($tracking_topics['f'][$forum_id], 36, 10) + $config['board_startdate']) : $user->data['user_lastmark'];
		}
	}

	// Handle update of unapproved topics info.
	// Only update for moderators having m_approve permission for the forum.
	$sql_update_unapproved = ($auth->acl_get('m_approve', $forum_id)) ? '': 'AND t.topic_approved = 1';

	// Check the forum for any left unread topics.
	// If there are none, we mark the forum as read.
	if ($config['load_db_lastread'] && $user->data['is_registered'])
	{
		if ($mark_time_forum >= $forum_last_post_time)
		{
			// We do not need to mark read, this happened before. Therefore setting this to true
			$row = true;
		}
		else
		{
			$sql = 'SELECT t.forum_id
				FROM ' . TOPICS_TABLE . ' t
				LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt
					ON (tt.topic_id = t.topic_id
						AND tt.user_id = ' . $user->data['user_id'] . ')
				WHERE t.forum_id = ' . $forum_id . '
					AND t.topic_last_post_time > ' . $mark_time_forum . '
					AND t.topic_moved_id = 0 ' .
					$sql_update_unapproved . '
					AND (tt.topic_id IS NULL
						OR tt.mark_time < t.topic_last_post_time)';
			$result = $db->sql_query_limit($sql, 1);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}
	}
	else if ($config['load_anon_lastread'] || $user->data['is_registered'])
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
			$sql = 'SELECT t.topic_id
				FROM ' . TOPICS_TABLE . ' t
				WHERE t.forum_id = ' . $forum_id . '
					AND t.topic_last_post_time > ' . $mark_time_forum . '
					AND t.topic_moved_id = 0 ' .
					$sql_update_unapproved;
			$result = $db->sql_query($sql);

			$check_forum = $tracking_topics['tf'][$forum_id];
			$unread = false;

			while ($row = $db->sql_fetchrow($result))
			{
				if (!isset($check_forum[base_convert($row['topic_id'], 10, 36)]))
				{
					$unread = true;
					break;
				}
			}
			$db->sql_freeresult($result);

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
	global $template, $user;

	// Make sure $per_page is a valid value
	$per_page = ($per_page <= 0) ? 1 : $per_page;

	$seperator = '<span class="page-sep">' . $user->lang['COMMA_SEPARATOR'] . '</span>';
	$total_pages = ceil($num_items / $per_page);

	if ($total_pages == 1 || !$num_items)
	{
		return false;
	}

	$on_page = floor($start_item / $per_page) + 1;
	$url_delim = (strpos($base_url, '?') === false) ? '?' : ((strpos($base_url, '?') === strlen($base_url) - 1) ? '' : '&amp;');

	$page_string = ($on_page == 1) ? '<strong>1</strong>' : '<a href="' . $base_url . '">1</a>';

	if ($total_pages > 5)
	{
		$start_cnt = min(max(1, $on_page - 4), $total_pages - 5);
		$end_cnt = max(min($total_pages, $on_page + 4), 6);

		$page_string .= ($start_cnt > 1) ? '<span class="page-dots"> ... </span>' : $seperator;

		for ($i = $start_cnt + 1; $i < $end_cnt; $i++)
		{
			$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . $base_url . "{$url_delim}start=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
			if ($i < $end_cnt - 1)
			{
				$page_string .= $seperator;
			}
		}

		$page_string .= ($end_cnt < $total_pages) ? '<span class="page-dots"> ... </span>' : $seperator;
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
			$page_string = '<a href="' . $base_url . "{$url_delim}start=" . (($on_page - 2) * $per_page) . '">' . $user->lang['PREVIOUS'] . '</a>&nbsp;&nbsp;' . $page_string;
		}

		if ($on_page != $total_pages)
		{
			$page_string .= '&nbsp;&nbsp;<a href="' . $base_url . "{$url_delim}start=" . ($on_page * $per_page) . '">' . $user->lang['NEXT'] . '</a>';
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
	global $template, $user;

	// Make sure $per_page is a valid value
	$per_page = ($per_page <= 0) ? 1 : $per_page;

	$on_page = floor($start / $per_page) + 1;

	$template->assign_vars(array(
		'ON_PAGE'		=> $on_page)
	);

	return sprintf($user->lang['PAGE_OF'], $on_page, max(ceil($num_items / $per_page), 1));
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
function append_sid($url, $params = false, $is_amp = true, $session_id = false)
{
	global $_SID, $_EXTRA_URL, $phpbb_hook;

	if ($params === '' || (is_array($params) && empty($params)))
	{
		// Do not append the ? if the param-list is empty anyway.
		$params = false;
	}

	// Developers using the hook function need to globalise the $_SID and $_EXTRA_URL on their own and also handle it appropriately.
	// They could mimic most of what is within this function
	if (!empty($phpbb_hook) && $phpbb_hook->call_hook(__FUNCTION__, $url, $params, $is_amp, $session_id))
	{
		if ($phpbb_hook->hook_return(__FUNCTION__))
		{
			return $phpbb_hook->hook_return_result(__FUNCTION__);
		}
	}

	$params_is_array = is_array($params);

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
	if ($_SID == '' && $session_id === false && empty($_EXTRA_URL) && !$params_is_array && !$anchor)
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
		$session_id = $_SID;
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
	return $url . (($append_url) ? $url_delim . $append_url . $amp_delim : $url_delim) . $params . ((!$session_id) ? '' : $amp_delim . 'sid=' . $session_id) . $anchor;
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
	global $config, $user;

	$server_name = $user->host;
	$server_port = (!empty($_SERVER['SERVER_PORT'])) ? (int) $_SERVER['SERVER_PORT'] : (int) getenv('SERVER_PORT');

	// Forcing server vars is the only way to specify/override the protocol
	if ($config['force_server_vars'] || !$server_name)
	{
		$server_protocol = ($config['server_protocol']) ? $config['server_protocol'] : (($config['cookie_secure']) ? 'https://' : 'http://');
		$server_name = $config['server_name'];
		$server_port = (int) $config['server_port'];
		$script_path = $config['script_path'];

		$url = $server_protocol . $server_name;
		$cookie_secure = $config['cookie_secure'];
	}
	else
	{
		// Do not rely on cookie_secure, users seem to think that it means a secured cookie instead of an encrypted connection
		$cookie_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 1 : 0;
		$url = (($cookie_secure) ? 'https://' : 'http://') . $server_name;

		$script_path = $user->page['root_script_path'];
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

/**
* Redirects the user to another page then exits the script nicely
* This function is intended for urls within the board. It's not meant to redirect to cross-domains.
*
* @param string $url The url to redirect to
* @param bool $return If true, do not redirect but return the sanitized URL. Default is no return.
* @param bool $disable_cd_check If true, redirect() will redirect to an external domain. If false, the redirect point to the boards url if it does not match the current domain. Default is false.
*/
function redirect($url, $return = false, $disable_cd_check = false)
{
	global $db, $cache, $config, $user, $phpbb_root_path;

	$failover_flag = false;

	if (empty($user->lang))
	{
		$user->add_lang('common');
	}

	if (!$return)
	{
		garbage_collection();
	}

	// Make sure no &amp;'s are in, this will break the redirect
	$url = str_replace('&amp;', '&', $url);

	// Determine which type of redirect we need to handle...
	$url_parts = @parse_url($url);

	if ($url_parts === false)
	{
		// Malformed url, redirect to current page...
		$url = generate_board_url() . '/' . $user->page['page'];
	}
	else if (!empty($url_parts['scheme']) && !empty($url_parts['host']))
	{
		// Attention: only able to redirect within the same domain if $disable_cd_check is false (yourdomain.com -> www.yourdomain.com will not work)
		if (!$disable_cd_check && $url_parts['host'] !== $user->host)
		{
			trigger_error('Tried to redirect to potentially insecure url.', E_USER_ERROR);
		}
	}
	else if ($url[0] == '/')
	{
		// Absolute uri, prepend direct url...
		$url = generate_board_url(true) . $url;
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
				$url = generate_board_url() . '/' . $user->page['page'];
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
				if ($url && substr($url, 0, 1) == '/')
				{
					$url = substr($url, 1);
				}

				if ($user->page['page_dir'])
				{
					$url = generate_board_url() . '/' . $user->page['page_dir'] . '/' . $url;
				}
				else
				{
					$url = generate_board_url() . '/' . $url;
				}
			}
			else
			{
				// Used ./ before, but $phpbb_root_path is working better with urls within another root path
				$root_dirs = explode('/', str_replace('\\', '/', phpbb_realpath($phpbb_root_path)));
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
				$url = generate_board_url() . '/' . $url;
			}
		}
	}

	// Make sure we don't redirect to external URLs
	if (!$disable_cd_check && strpos($url, generate_board_url(true) . '/') !== 0)
	{
		trigger_error('Tried to redirect to potentially insecure url.', E_USER_ERROR);
	}

	// Make sure no linebreaks are there... to prevent http response splitting for PHP < 4.4.2
	if (strpos(urldecode($url), "\n") !== false || strpos(urldecode($url), "\r") !== false || strpos($url, ';') !== false)
	{
		trigger_error('Tried to redirect to potentially insecure url.', E_USER_ERROR);
	}

	// Now, also check the protocol and for a valid url the last time...
	$allowed_protocols = array('http', 'https', 'ftp', 'ftps');
	$url_parts = parse_url($url);

	if ($url_parts === false || empty($url_parts['scheme']) || !in_array($url_parts['scheme'], $allowed_protocols))
	{
		trigger_error('Tried to redirect to potentially insecure url.', E_USER_ERROR);
	}

	if ($return)
	{
		return $url;
	}

	// Redirect via an HTML form for PITA webservers
	if (@preg_match('#Microsoft|WebSTAR|Xitami#', getenv('SERVER_SOFTWARE')))
	{
		header('Refresh: 0; URL=' . $url);

		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		echo '<html xmlns="http://www.w3.org/1999/xhtml" dir="' . $user->lang['DIRECTION'] . '" lang="' . $user->lang['USER_LANG'] . '" xml:lang="' . $user->lang['USER_LANG'] . '">';
		echo '<head>';
		echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
		echo '<meta http-equiv="refresh" content="0; url=' . str_replace('&', '&amp;', $url) . '" />';
		echo '<title>' . $user->lang['REDIRECT'] . '</title>';
		echo '</head>';
		echo '<body>';
		echo '<div style="text-align: center;">' . sprintf($user->lang['URL_REDIRECT'], '<a href="' . str_replace('&', '&amp;', $url) . '">', '</a>') . '</div>';
		echo '</body>';
		echo '</html>';

		exit;
	}

	// Behave as per HTTP/1.1 spec for others
	header('Location: ' . $url);
	exit;
}

/**
* Re-Apply session id after page reloads
*/
function reapply_sid($url)
{
	global $phpEx, $phpbb_root_path;

	if ($url === "index.$phpEx")
	{
		return append_sid("index.$phpEx");
	}
	else if ($url === "{$phpbb_root_path}index.$phpEx")
	{
		return append_sid("{$phpbb_root_path}index.$phpEx");
	}

	// Remove previously added sid
	if (strpos($url, 'sid=') !== false)
	{
		// All kind of links
		$url = preg_replace('/(\?)?(&amp;|&)?sid=[a-z0-9]+/', '', $url);
		// if the sid was the first param, make the old second as first ones
		$url = preg_replace("/$phpEx(&amp;|&)+?/", "$phpEx?", $url);
	}

	return append_sid($url);
}

/**
* Returns url from the session/current page with an re-appended SID with optionally stripping vars from the url
*/
function build_url($strip_vars = false)
{
	global $user, $phpbb_root_path;

	// Append SID
	$redirect = append_sid($user->page['page'], false, false);

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
* Meta refresh assignment
* Adds META template variable with meta http tag.
*
* @param int $time Time in seconds for meta refresh tag
* @param string $url URL to redirect to. The url will go through redirect() first before the template variable is assigned
* @param bool $disable_cd_check If true, meta_refresh() will redirect to an external domain. If false, the redirect point to the boards url if it does not match the current domain. Default is false.
*/
function meta_refresh($time, $url, $disable_cd_check = false)
{
	global $template;

	$url = redirect($url, true, $disable_cd_check);
	$url = str_replace('&', '&amp;', $url);

	// For XHTML compatibility we change back & to &amp;
	$template->assign_vars(array(
		'META' => '<meta http-equiv="refresh" content="' . $time . '; url=' . $url . '" />')
	);

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
* @return null
*/
function send_status_line($code, $message)
{
	if (substr(strtolower(@php_sapi_name()), 0, 3) === 'cgi')
	{
		// in theory, we shouldn't need that due to php doing it. Reality offers a differing opinion, though
		header("Status: $code $message", true, $code);
	}
	else
	{
		if (!empty($_SERVER['SERVER_PROTOCOL']) && is_string($_SERVER['SERVER_PROTOCOL']) && preg_match('#^HTTP/[0-9]\.[0-9]$#', $_SERVER['SERVER_PROTOCOL']))
		{
			$version = $_SERVER['SERVER_PROTOCOL'];
		}
		else
		{
			$version = 'HTTP/1.0';
		}
		header("$version $code $message", true, $code);
	}
}

//Form validation


/**
* Add a secret hash   for use in links/GET requests
* @param string  $link_name The name of the link; has to match the name used in check_link_hash, otherwise no restrictions apply
* @return string the hash

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
*/
function add_form_key($form_name)
{
	global $config, $template, $user;

	$now = time();
	$token_sid = ($user->data['user_id'] == ANONYMOUS && !empty($config['form_token_sid_guests'])) ? $user->session_id : '';
	$token = sha1($now . $user->data['user_form_salt'] . $form_name . $token_sid);

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
	global $config, $user;

	if ($timespan === false)
	{
		// we enforce a minimum value of half a minute here.
		$timespan = ($config['form_token_lifetime'] == -1) ? -1 : max(30, $config['form_token_lifetime']);
	}

	if (isset($_POST['creation_time']) && isset($_POST['form_token']))
	{
		$creation_time	= abs(request_var('creation_time', 0));
		$token = request_var('form_token', '');

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

	if ($trigger)
	{
		trigger_error($user->lang['FORM_INVALID'] . $return_page);
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
	global $user, $template, $db;
	global $phpEx, $phpbb_root_path;

	if (isset($_POST['cancel']))
	{
		return false;
	}

	$confirm = false;
	if (isset($_POST['confirm']))
	{
		// language frontier
		if ($_POST['confirm'] === $user->lang['YES'])
		{
			$confirm = true;
		}
	}

	if ($check && $confirm)
	{
		$user_id = request_var('confirm_uid', 0);
		$session_id = request_var('sess', '');
		$confirm_key = request_var('confirm_key', '');

		if ($user_id != $user->data['user_id'] || $session_id != $user->session_id || !$confirm_key || !$user->data['user_last_confirm_key'] || $confirm_key != $user->data['user_last_confirm_key'])
		{
			return false;
		}

		// Reset user_last_confirm_key
		$sql = 'UPDATE ' . USERS_TABLE . " SET user_last_confirm_key = ''
			WHERE user_id = " . $user->data['user_id'];
		$db->sql_query($sql);

		return true;
	}
	else if ($check)
	{
		return false;
	}

	$s_hidden_fields = build_hidden_fields(array(
		'confirm_uid'	=> $user->data['user_id'],
		'sess'			=> $user->session_id,
		'sid'			=> $user->session_id,
	));

	// generate activation key
	$confirm_key = gen_rand_string(10);

	if (defined('IN_ADMIN') && isset($user->data['session_admin']) && $user->data['session_admin'])
	{
		adm_page_header((!isset($user->lang[$title])) ? $user->lang['CONFIRM'] : $user->lang[$title]);
	}
	else
	{
		page_header(((!isset($user->lang[$title])) ? $user->lang['CONFIRM'] : $user->lang[$title]), false);
	}

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
	$use_page = ($u_action) ? $phpbb_root_path . $u_action : $phpbb_root_path . str_replace('&', '&amp;', $user->page['page']);
	$u_action = reapply_sid($use_page);
	$u_action .= ((strpos($u_action, '?') === false) ? '?' : '&amp;') . 'confirm_key=' . $confirm_key;

	$template->assign_vars(array(
		'MESSAGE_TITLE'		=> (!isset($user->lang[$title])) ? $user->lang['CONFIRM'] : $user->lang[$title],
		'MESSAGE_TEXT'		=> (!isset($user->lang[$title . '_CONFIRM'])) ? $title : $user->lang[$title . '_CONFIRM'],

		'YES_VALUE'			=> $user->lang['YES'],
		'S_CONFIRM_ACTION'	=> $u_action,
		'S_HIDDEN_FIELDS'	=> $hidden . $s_hidden_fields)
	);

	$sql = 'UPDATE ' . USERS_TABLE . " SET user_last_confirm_key = '" . $db->sql_escape($confirm_key) . "'
		WHERE user_id = " . $user->data['user_id'];
	$db->sql_query($sql);

	if (defined('IN_ADMIN') && isset($user->data['session_admin']) && $user->data['session_admin'])
	{
		adm_page_footer();
	}
	else
	{
		page_footer();
	}
}

/**
* Generate login box or verify password
*/
function login_box($redirect = '', $l_explain = '', $l_success = '', $admin = false, $s_display = true)
{
	global $db, $user, $template, $auth, $phpEx, $phpbb_root_path, $config;

	if (!class_exists('phpbb_captcha_factory'))
	{
		include($phpbb_root_path . 'includes/captcha/captcha_factory.' . $phpEx);
	}

	$err = '';

	// Make sure user->setup() has been called
	if (empty($user->lang))
	{
		$user->setup();
	}

	// Print out error if user tries to authenticate as an administrator without having the privileges...
	if ($admin && !$auth->acl_get('a_'))
	{
		// Not authd
		// anonymous/inactive users are never able to go to the ACP even if they have the relevant permissions
		if ($user->data['is_registered'])
		{
			add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
		}
		trigger_error('NO_AUTH_ADMIN');
	}

	if (isset($_POST['login']))
	{
		// Get credential
		if ($admin)
		{
			$credential = request_var('credential', '');

			if (strspn($credential, 'abcdef0123456789') !== strlen($credential) || strlen($credential) != 32)
			{
				if ($user->data['is_registered'])
				{
					add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
				}
				trigger_error('NO_AUTH_ADMIN');
			}

			$password	= request_var('password_' . $credential, '', true);
		}
		else
		{
			$password	= request_var('password', '', true);
		}

		$username	= request_var('username', '', true);
		$autologin	= (!empty($_POST['autologin'])) ? true : false;
		$viewonline = (!empty($_POST['viewonline'])) ? 0 : 1;
		$admin 		= ($admin) ? 1 : 0;
		$viewonline = ($admin) ? $user->data['session_viewonline'] : $viewonline;

		// Check if the supplied username is equal to the one stored within the database if re-authenticating
		if ($admin && utf8_clean_string($username) != utf8_clean_string($user->data['username']))
		{
			// We log the attempt to use a different username...
			add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
			trigger_error('NO_AUTH_ADMIN_USER_DIFFER');
		}

		// If authentication is successful we redirect user to previous page
		$result = $auth->login($username, $password, $autologin, $viewonline, $admin);

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
				if ($user->data['is_registered'])
				{
					add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
				}
			}
		}

		// The result parameter is always an array, holding the relevant information...
		if ($result['status'] == LOGIN_SUCCESS)
		{
			$redirect = request_var('redirect', "{$phpbb_root_path}index.$phpEx");
			$message = ($l_success) ? $l_success : $user->lang['LOGIN_REDIRECT'];
			$l_redirect = ($admin) ? $user->lang['PROCEED_TO_ACP'] : (($redirect === "{$phpbb_root_path}index.$phpEx" || $redirect === "index.$phpEx") ? $user->lang['RETURN_INDEX'] : $user->lang['RETURN_PAGE']);

			// append/replace SID (may change during the session for AOL users)
			$redirect = reapply_sid($redirect);

			// Special case... the user is effectively banned, but we allow founders to login
			if (defined('IN_CHECK_BAN') && $result['user_row']['user_type'] != USER_FOUNDER)
			{
				return;
			}

			$redirect = meta_refresh(3, $redirect);
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

				$captcha = phpbb_captcha_factory::get_instance($config['captcha_plugin']);
				$captcha->init(CONFIRM_LOGIN);
				// $captcha->reset();

				$template->assign_vars(array(
					'CAPTCHA_TEMPLATE'			=> $captcha->get_template(),
				));

				$err = $user->lang[$result['error_msg']];
			break;

			case LOGIN_ERROR_PASSWORD_CONVERT:
				$err = sprintf(
					$user->lang[$result['error_msg']],
					($config['email_enable']) ? '<a href="' . append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=sendpassword') . '">' : '',
					($config['email_enable']) ? '</a>' : '',
					($config['board_contact']) ? '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">' : '',
					($config['board_contact']) ? '</a>' : ''
				);
			break;

			// Username, password, etc...
			default:
				$err = $user->lang[$result['error_msg']];

				// Assign admin contact to some error messages
				if ($result['error_msg'] == 'LOGIN_ERROR_USERNAME' || $result['error_msg'] == 'LOGIN_ERROR_PASSWORD')
				{
					$err = (!$config['board_contact']) ? sprintf($user->lang[$result['error_msg']], '', '') : sprintf($user->lang[$result['error_msg']], '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">', '</a>');
				}

			break;
		}
	}

	// Assign credential for username/password pair
	$credential = ($admin) ? md5(unique_id()) : false;

	$s_hidden_fields = array(
		'sid'		=> $user->session_id,
	);

	if ($redirect)
	{
		$s_hidden_fields['redirect'] = $redirect;
	}

	if ($admin)
	{
		$s_hidden_fields['credential'] = $credential;
	}

	$s_hidden_fields = build_hidden_fields($s_hidden_fields);

	$template->assign_vars(array(
		'LOGIN_ERROR'		=> $err,
		'LOGIN_EXPLAIN'		=> $l_explain,

		'U_SEND_PASSWORD' 		=> ($config['email_enable']) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=sendpassword') : '',
		'U_RESEND_ACTIVATION'	=> ($config['require_activation'] == USER_ACTIVATION_SELF && $config['email_enable']) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=resend_act') : '',
		'U_TERMS_USE'			=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=terms'),
		'U_PRIVACY'				=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=privacy'),

		'S_DISPLAY_FULL_LOGIN'	=> ($s_display) ? true : false,
		'S_HIDDEN_FIELDS' 		=> $s_hidden_fields,

		'S_ADMIN_AUTH'			=> $admin,
		'USERNAME'				=> ($admin) ? $user->data['username'] : '',

		'USERNAME_CREDENTIAL'	=> 'username',
		'PASSWORD_CREDENTIAL'	=> ($admin) ? 'password_' . $credential : 'password',
	));

	page_header($user->lang['LOGIN'], false);

	$template->set_filenames(array(
		'body' => 'login_body.html')
	);
	make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));

	page_footer();
}

/**
* Generate forum login box
*/
function login_forum_box($forum_data)
{
	global $db, $config, $user, $template, $phpEx;

	$password = request_var('password', '', true);

	$sql = 'SELECT forum_id
		FROM ' . FORUMS_ACCESS_TABLE . '
		WHERE forum_id = ' . $forum_data['forum_id'] . '
			AND user_id = ' . $user->data['user_id'] . "
			AND session_id = '" . $db->sql_escape($user->session_id) . "'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

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
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$sql_in = array();
			do
			{
				$sql_in[] = (string) $row['session_id'];
			}
			while ($row = $db->sql_fetchrow($result));

			// Remove expired sessions
			$sql = 'DELETE FROM ' . FORUMS_ACCESS_TABLE . '
				WHERE ' . $db->sql_in_set('session_id', $sql_in);
			$db->sql_query($sql);
		}
		$db->sql_freeresult($result);

		if (phpbb_check_hash($password, $forum_data['forum_password']))
		{
			$sql_ary = array(
				'forum_id'		=> (int) $forum_data['forum_id'],
				'user_id'		=> (int) $user->data['user_id'],
				'session_id'	=> (string) $user->session_id,
			);

			$db->sql_query('INSERT INTO ' . FORUMS_ACCESS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

			return true;
		}

		$template->assign_var('LOGIN_ERROR', $user->lang['WRONG_PASSWORD']);
	}

	page_header($user->lang['LOGIN'], false);

	$template->assign_vars(array(
		'FORUM_NAME'			=> isset($forum_data['forum_name']) ? $forum_data['forum_name'] : '',
		'S_LOGIN_ACTION'		=> build_url(array('f')),
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
		else if (($value[0] == "'" && $value[sizeof($value) - 1] == "'") || ($value[0] == '"' && $value[sizeof($value) - 1] == '"'))
		{
			$value = htmlspecialchars(substr($value, 1, sizeof($value)-2));
		}
		else
		{
			$value = htmlspecialchars($value);
		}

		$parsed_items[$key] = $value;
	}
	
	if (isset($parsed_items['inherit_from']) && isset($parsed_items['name']) && $parsed_items['inherit_from'] == $parsed_items['name'])
	{
		unset($parsed_items['inherit_from']);
	}

	return $parsed_items;
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

	$mode			= array_shift($args);
	$reportee_id	= ($mode == 'user') ? intval(array_shift($args)) : '';
	$forum_id		= ($mode == 'mod') ? intval(array_shift($args)) : '';
	$topic_id		= ($mode == 'mod') ? intval(array_shift($args)) : '';
	$action			= array_shift($args);
	$data			= (!sizeof($args)) ? '' : serialize($args);

	$sql_ary = array(
		'user_id'		=> (empty($user->data)) ? ANONYMOUS : $user->data['user_id'],
		'log_ip'		=> $user->ip,
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

	$db->sql_query('INSERT INTO ' . LOG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

	return $db->sql_nextid();
}

/**
* Return a nicely formatted backtrace.
*
* Turns the array returned by debug_backtrace() into HTML markup.
* Also filters out absolute paths to phpBB root.
*
* @return string	HTML markup
*/
function get_backtrace()
{
	$output = '<div style="font-family: monospace;">';
	$backtrace = debug_backtrace();

	// We skip the first one, because it only shows this file/function
	unset($backtrace[0]);

	foreach ($backtrace as $trace)
	{
		// Strip the current directory from path
		$trace['file'] = (empty($trace['file'])) ? '(not given by php)' : htmlspecialchars(phpbb_filter_root_path($trace['file']));
		$trace['line'] = (empty($trace['line'])) ? '(not given by php)' : $trace['line'];

		// Only show function arguments for include etc.
		// Other parameters may contain sensible information
		$argument = '';
		if (!empty($trace['args'][0]) && in_array($trace['function'], array('include', 'require', 'include_once', 'require_once')))
		{
			$argument = htmlspecialchars(phpbb_filter_root_path($trace['args'][0]));
		}

		$trace['class'] = (!isset($trace['class'])) ? '' : $trace['class'];
		$trace['type'] = (!isset($trace['type'])) ? '' : $trace['type'];

		$output .= '<br />';
		$output .= '<b>FILE:</b> ' . $trace['file'] . '<br />';
		$output .= '<b>LINE:</b> ' . ((!empty($trace['line'])) ? $trace['line'] : '') . '<br />';

		$output .= '<b>CALL:</b> ' . htmlspecialchars($trace['class'] . $trace['type'] . $trace['function']);
		$output .= '(' . (($argument !== '') ? "'$argument'" : '') . ')<br />';
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
			// Regex written by James Watts and Francisco Jose Martin Moreno
			// http://fightingforalostcause.net/misc/2006/compare-email-regex.php
			return '([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*(?:[\w\!\#$\%\'\*\+\-\/\=\?\^\`{\|\}\~]|&amp;)+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,63})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)';
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
			return '#^(?:(?:(?:[\dA-F]{1,4}:){6}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:::(?:[\dA-F]{1,4}:){0,5}(?:[\dA-F]{1,4}(?::[\dA-F]{1,4})?|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:):(?:[\dA-F]{1,4}:){4}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,2}:(?:[\dA-F]{1,4}:){3}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,3}:(?:[\dA-F]{1,4}:){2}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,4}:(?:[\dA-F]{1,4}:)(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,5}:(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,6}:[\dA-F]{1,4})|(?:(?:[\dA-F]{1,4}:){1,7}:)|(?:::))$#i';
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

		case 'table_prefix':
			return '#^[a-zA-Z][a-zA-Z0-9_]*$#';
		break;
	}

	return '';
}

/**
* Generate regexp for naughty words censoring
* Depends on whether installed PHP version supports unicode properties
*
* @param string	$word			word template to be replaced
* @param bool	$use_unicode	whether or not to take advantage of PCRE supporting unicode
*
* @return string $preg_expr		regex to use with word censor
*/
function get_censor_preg_expression($word, $use_unicode = true)
{
	static $unicode_support = null;

	// Check whether PHP version supports unicode properties
	if (is_null($unicode_support))
	{
		$unicode_support = ((version_compare(PHP_VERSION, '5.1.0', '>=') || (version_compare(PHP_VERSION, '5.0.0-dev', '<=') && version_compare(PHP_VERSION, '4.4.0', '>='))) && @preg_match('/\p{L}/u', 'a') !== false) ? true : false;
	}

	// Unescape the asterisk to simplify further conversions
	$word = str_replace('\*', '*', preg_quote($word, '#'));

	if ($use_unicode && $unicode_support)
	{
		// Replace asterisk(s) inside the pattern, at the start and at the end of it with regexes
		$word = preg_replace(array('#(?<=[\p{Nd}\p{L}_])\*+(?=[\p{Nd}\p{L}_])#iu', '#^\*+#', '#\*+$#'), array('([\x20]*?|[\p{Nd}\p{L}_-]*?)', '[\p{Nd}\p{L}_-]*?', '[\p{Nd}\p{L}_-]*?'), $word);

		// Generate the final substitution
		$preg_expr = '#(?<![\p{Nd}\p{L}_-])(' . $word . ')(?![\p{Nd}\p{L}_-])#iu';
	}
	else
	{
		// Replace the asterisk inside the pattern, at the start and at the end of it with regexes
		$word = preg_replace(array('#(?<=\S)\*+(?=\S)#iu', '#^\*+#', '#\*+$#'), array('(\x20*?\S*?)', '\S*?', '\S*?'), $word);

		// Generate the final substitution
		$preg_expr = '#(?<!\S)(' . $word . ')(?!\S)#iu';
	}

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
* @param string $host	Fully-Qualified Domain Name
* @param string $type	Resource record type to lookup
*						Supported types are: MX (default), A, AAAA, NS, TXT, CNAME
*						Other types may work or may not work
*
* @return mixed		true if entry found,
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
	// $host		has format	some.host.example.com
	// $host_fqdn	has format	some.host.example.com.

	// If we're looking for an A record we can use gethostbyname()
	if ($type == 'A' && function_exists('gethostbyname'))
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
	if (
		($type == 'MX' || DIRECTORY_SEPARATOR != '\\' || version_compare(PHP_VERSION, '5.3.3', '>=')) &&
		($type != 'AAAA' || version_compare(PHP_VERSION, '5.0.0', '>=')) &&
		($type != 'TXT' || version_compare(PHP_VERSION, '5.2.4', '>=')) &&
		function_exists('checkdnsrr')
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
	if (
		($type != 'AAAA' || DIRECTORY_SEPARATOR != '\\' || version_compare(PHP_VERSION, '5.3.1', '>=')) &&
		function_exists('dns_get_record')
	)
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
		else if ($type_param == DNS_ANY)
		{
			// $resultset is a non-empty array
			return true;
		}

		foreach ($resultset as $result)
		{
			if (
				isset($result['host']) && $result['host'] == $host &&
				isset($result['type']) && $result['type'] == $type
			)
			{
				return true;
			}
		}

		return false;
	}

	// If we're on Windows we can still try to call nslookup via exec() as a last resort
	if (DIRECTORY_SEPARATOR == '\\' && function_exists('exec'))
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
				break;

				default:
				case 'AAAA':
					// AAAA records returned by nslookup on Windows XP/2003 have this format.
					// Later Windows versions use the A record format below for AAAA records.
					if (stripos($line, "$host AAAA IPv6 address") === 0)
					{
						return true;
					}
				// No break

				case 'A':
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

// Handler, header and footer

/**
* Error and message handler, call with trigger_error if reqd
*/
function msg_handler($errno, $msg_text, $errfile, $errline)
{
	global $cache, $db, $auth, $template, $config, $user;
	global $phpEx, $phpbb_root_path, $msg_title, $msg_long_text;

	// Do not display notices if we suppress them via @
	if (error_reporting() == 0 && $errno != E_USER_ERROR && $errno != E_USER_WARNING && $errno != E_USER_NOTICE)
	{
		return;
	}

	// Message handler is stripping text. In case we need it, we are possible to define long text...
	if (isset($msg_long_text) && $msg_long_text && !$msg_text)
	{
		$msg_text = $msg_long_text;
	}

	if (!defined('E_DEPRECATED'))
	{
		define('E_DEPRECATED', 8192);
	}

	switch ($errno)
	{
		case E_NOTICE:
		case E_WARNING:

			// Check the error reporting level and return if the error level does not match
			// If DEBUG is defined the default level is E_ALL
			if (($errno & ((defined('DEBUG')) ? E_ALL : error_reporting())) == 0)
			{
				return;
			}

			if (strpos($errfile, 'cache') === false && strpos($errfile, 'template.') === false)
			{
				$errfile = phpbb_filter_root_path($errfile);
				$msg_text = phpbb_filter_root_path($msg_text);
				$error_name = ($errno === E_WARNING) ? 'PHP Warning' : 'PHP Notice';
				echo '<b>[phpBB Debug] ' . $error_name . '</b>: in file <b>' . $errfile . '</b> on line <b>' . $errline . '</b>: <b>' . $msg_text . '</b><br />' . "\n";

				// we are writing an image - the user won't see the debug, so let's place it in the log
				if (defined('IMAGE_OUTPUT') || defined('IN_CRON'))
				{
					add_log('critical', 'LOG_IMAGE_GENERATION_ERROR', $errfile, $errline, $msg_text);
				}
				// echo '<br /><br />BACKTRACE<br />' . get_backtrace() . '<br />' . "\n";
			}

			return;

		break;

		case E_USER_ERROR:

			if (!empty($user) && !empty($user->lang))
			{
				$msg_text = (!empty($user->lang[$msg_text])) ? $user->lang[$msg_text] : $msg_text;
				$msg_title = (!isset($msg_title)) ? $user->lang['GENERAL_ERROR'] : ((!empty($user->lang[$msg_title])) ? $user->lang[$msg_title] : $msg_title);

				$l_return_index = sprintf($user->lang['RETURN_INDEX'], '<a href="' . $phpbb_root_path . '">', '</a>');
				$l_notify = '';

				if (!empty($config['board_contact']))
				{
					$l_notify = '<p>' . sprintf($user->lang['NOTIFY_ADMIN_EMAIL'], $config['board_contact']) . '</p>';
				}
			}
			else
			{
				$msg_title = 'General Error';
				$l_return_index = '<a href="' . $phpbb_root_path . '">Return to index page</a>';
				$l_notify = '';

				if (!empty($config['board_contact']))
				{
					$l_notify = '<p>Please notify the board administrator or webmaster: <a href="mailto:' . $config['board_contact'] . '">' . $config['board_contact'] . '</a></p>';
				}
			}

			$log_text = $msg_text;
			$backtrace = get_backtrace();
			if ($backtrace)
			{
				$log_text .= '<br /><br />BACKTRACE<br />' . $backtrace;
			}

			if (defined('IN_INSTALL') || defined('DEBUG_EXTRA') || isset($auth) && $auth->acl_get('a_'))
			{
				$msg_text = $log_text;
			}

			if ((defined('DEBUG') || defined('IN_CRON') || defined('IMAGE_OUTPUT')) && isset($db))
			{
				// let's avoid loops
				$db->sql_return_on_error(true);
				add_log('critical', 'LOG_GENERAL_ERROR', $msg_title, $log_text);
				$db->sql_return_on_error(false);
			}

			// Do not send 200 OK, but service unavailable on errors
			send_status_line(503, 'Service Unavailable');

			garbage_collection();

			// Try to not call the adm page data...

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

			echo '			<div>' . $msg_text . '</div>';

			echo $l_notify;

			echo '		</div>';
			echo '	</div>';
			echo '	</div>';
			echo '	<div id="page-footer">';
			echo '		Powered by <a href="https://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Group';
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

			if (empty($user->data))
			{
				$user->session_begin();
			}

			// We re-init the auth array to get correct results on login/logout
			$auth->acl($user->data);

			if (empty($user->lang))
			{
				$user->setup();
			}

			if ($msg_text == 'ERROR_NO_ATTACHMENT' || $msg_text == 'NO_FORUM' || $msg_text == 'NO_TOPIC' || $msg_text == 'NO_USER')
			{
				send_status_line(404, 'Not Found');
			}

			$msg_text = (!empty($user->lang[$msg_text])) ? $user->lang[$msg_text] : $msg_text;
			$msg_title = (!isset($msg_title)) ? $user->lang['INFORMATION'] : ((!empty($user->lang[$msg_title])) ? $user->lang[$msg_title] : $msg_title);

			if (!defined('HEADER_INC'))
			{
				if (defined('IN_ADMIN') && isset($user->data['session_admin']) && $user->data['session_admin'])
				{
					adm_page_header($msg_title);
				}
				else
				{
					page_header($msg_title, false);
				}
			}

			$template->set_filenames(array(
				'body' => 'message_body.html')
			);

			$template->assign_vars(array(
				'MESSAGE_TITLE'		=> $msg_title,
				'MESSAGE_TEXT'		=> $msg_text,
				'S_USER_WARNING'	=> ($errno == E_USER_WARNING) ? true : false,
				'S_USER_NOTICE'		=> ($errno == E_USER_NOTICE) ? true : false)
			);

			// We do not want the cron script to be called on error messages
			define('IN_CRON', true);

			if (defined('IN_ADMIN') && isset($user->data['session_admin']) && $user->data['session_admin'])
			{
				adm_page_footer();
			}
			else
			{
				page_footer();
			}

			exit_handler();
		break;

		// PHP4 compatibility
		case E_DEPRECATED:
			return true;
		break;
	}

	// If we notice an error not handled here we pass this back to PHP by returning false
	// This may not work for all php versions
	return false;
}

/**
* Removes absolute path to phpBB root directory from error messages
* and converts backslashes to forward slashes.
*
* @param string $errfile	Absolute file path
*							(e.g. /var/www/phpbb3/phpBB/includes/functions.php)
*							Please note that if $errfile is outside of the phpBB root,
*							the root path will not be found and can not be filtered.
* @return string			Relative file path
*							(e.g. /includes/functions.php)
*/
function phpbb_filter_root_path($errfile)
{
	static $root_path;

	if (empty($root_path))
	{
		$root_path = phpbb_realpath(dirname(__FILE__) . '/../');
	}

	return str_replace(array($root_path, '\\'), array('[ROOT]', '/'), $errfile);
}

/**
* Queries the session table to get information about online guests
* @param int $item_id Limits the search to the item with this id
* @param string $item The name of the item which is stored in the session table as session_{$item}_id
* @return int The number of active distinct guest sessions
*/
function obtain_guest_count($item_id = 0, $item = 'forum')
{
	global $db, $config;

	if ($item_id)
	{
		$reading_sql = ' AND s.session_' . $item . '_id = ' . (int) $item_id;
	}
	else
	{
		$reading_sql = '';
	}
	$time = (time() - (intval($config['load_online_time']) * 60));

	// Get number of online guests

	if ($db->sql_layer === 'sqlite')
	{
		$sql = 'SELECT COUNT(session_ip) as num_guests
			FROM (
				SELECT DISTINCT s.session_ip
				FROM ' . SESSIONS_TABLE . ' s
				WHERE s.session_user_id = ' . ANONYMOUS . '
					AND s.session_time >= ' . ($time - ((int) ($time % 60))) .
				$reading_sql .
			')';
	}
	else
	{
		$sql = 'SELECT COUNT(DISTINCT s.session_ip) as num_guests
			FROM ' . SESSIONS_TABLE . ' s
			WHERE s.session_user_id = ' . ANONYMOUS . '
				AND s.session_time >= ' . ($time - ((int) ($time % 60))) .
			$reading_sql;
	}
	$result = $db->sql_query($sql);
	$guests_online = (int) $db->sql_fetchfield('num_guests');
	$db->sql_freeresult($result);

	return $guests_online;
}

/**
* Queries the session table to get information about online users
* @param int $item_id Limits the search to the item with this id
* @param string $item The name of the item which is stored in the session table as session_{$item}_id
* @return array An array containing the ids of online, hidden and visible users, as well as statistical info
*/
function obtain_users_online($item_id = 0, $item = 'forum')
{
	global $db, $config, $user;

	$reading_sql = '';
	if ($item_id !== 0)
	{
		$reading_sql = ' AND s.session_' . $item . '_id = ' . (int) $item_id;
	}

	$online_users = array(
		'online_users'			=> array(),
		'hidden_users'			=> array(),
		'total_online'			=> 0,
		'visible_online'		=> 0,
		'hidden_online'			=> 0,
		'guests_online'			=> 0,
	);

	if ($config['load_online_guests'])
	{
		$online_users['guests_online'] = obtain_guest_count($item_id, $item);
	}

	// a little discrete magic to cache this for 30 seconds
	$time = (time() - (intval($config['load_online_time']) * 60));

	$sql = 'SELECT s.session_user_id, s.session_ip, s.session_viewonline
		FROM ' . SESSIONS_TABLE . ' s
		WHERE s.session_time >= ' . ($time - ((int) ($time % 30))) .
			$reading_sql .
		' AND s.session_user_id <> ' . ANONYMOUS;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		// Skip multiple sessions for one user
		if (!isset($online_users['online_users'][$row['session_user_id']]))
		{
			$online_users['online_users'][$row['session_user_id']] = (int) $row['session_user_id'];
			if ($row['session_viewonline'])
			{
				$online_users['visible_online']++;
			}
			else
			{
				$online_users['hidden_users'][$row['session_user_id']] = (int) $row['session_user_id'];
				$online_users['hidden_online']++;
			}
		}
	}
	$online_users['total_online'] = $online_users['guests_online'] + $online_users['visible_online'] + $online_users['hidden_online'];
	$db->sql_freeresult($result);

	return $online_users;
}

/**
* Uses the result of obtain_users_online to generate a localized, readable representation.
* @param mixed $online_users result of obtain_users_online - array with user_id lists for total, hidden and visible users, and statistics
* @param int $item_id Indicate that the data is limited to one item and not global
* @param string $item The name of the item which is stored in the session table as session_{$item}_id
* @return array An array containing the string for output to the template
*/
function obtain_users_online_string($online_users, $item_id = 0, $item = 'forum')
{
	global $config, $db, $user, $auth;

	$user_online_link = $online_userlist = '';
	// Need caps version of $item for language-strings
	$item_caps = strtoupper($item);

	if (sizeof($online_users['online_users']))
	{
		$sql = 'SELECT username, username_clean, user_id, user_type, user_allow_viewonline, user_colour
				FROM ' . USERS_TABLE . '
				WHERE ' . $db->sql_in_set('user_id', $online_users['online_users']) . '
				ORDER BY username_clean ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			// User is logged in and therefore not a guest
			if ($row['user_id'] != ANONYMOUS)
			{
				if (isset($online_users['hidden_users'][$row['user_id']]))
				{
					$row['username'] = '<em>' . $row['username'] . '</em>';
				}

				if (!isset($online_users['hidden_users'][$row['user_id']]) || $auth->acl_get('u_viewonline'))
				{
					$user_online_link = get_username_string(($row['user_type'] <> USER_IGNORE) ? 'full' : 'no_profile', $row['user_id'], $row['username'], $row['user_colour']);
					$online_userlist .= ($online_userlist != '') ? ', ' . $user_online_link : $user_online_link;
				}
			}
		}
		$db->sql_freeresult($result);
	}

	if (!$online_userlist)
	{
		$online_userlist = $user->lang['NO_ONLINE_USERS'];
	}

	if ($item_id === 0)
	{
		$online_userlist = $user->lang['REGISTERED_USERS'] . ' ' . $online_userlist;
	}
	else if ($config['load_online_guests'])
	{
		$l_online = ($online_users['guests_online'] === 1) ? $user->lang['BROWSING_' . $item_caps . '_GUEST'] : $user->lang['BROWSING_' . $item_caps . '_GUESTS'];
		$online_userlist = sprintf($l_online, $online_userlist, $online_users['guests_online']);
	}
	else
	{
		$online_userlist = sprintf($user->lang['BROWSING_' . $item_caps], $online_userlist);
	}
	// Build online listing
	$vars_online = array(
		'ONLINE'	=> array('total_online', 'l_t_user_s', 0),
		'REG'		=> array('visible_online', 'l_r_user_s', !$config['load_online_guests']),
		'HIDDEN'	=> array('hidden_online', 'l_h_user_s', $config['load_online_guests']),
		'GUEST'		=> array('guests_online', 'l_g_user_s', 0)
	);

	foreach ($vars_online as $l_prefix => $var_ary)
	{
		if ($var_ary[2])
		{
			$l_suffix = '_AND';
		}
		else
		{
			$l_suffix = '';
		}
		switch ($online_users[$var_ary[0]])
		{
			case 0:
				${$var_ary[1]} = $user->lang[$l_prefix . '_USERS_ZERO_TOTAL' . $l_suffix];
			break;

			case 1:
				${$var_ary[1]} = $user->lang[$l_prefix . '_USER_TOTAL' . $l_suffix];
			break;

			default:
				${$var_ary[1]} = $user->lang[$l_prefix . '_USERS_TOTAL' . $l_suffix];
			break;
		}
	}
	unset($vars_online);

	$l_online_users = sprintf($l_t_user_s, $online_users['total_online']);
	$l_online_users .= sprintf($l_r_user_s, $online_users['visible_online']);
	$l_online_users .= sprintf($l_h_user_s, $online_users['hidden_online']);

	if ($config['load_online_guests'])
	{
		$l_online_users .= sprintf($l_g_user_s, $online_users['guests_online']);
	}



	return array(
		'online_userlist'	=> $online_userlist,
		'l_online_users'	=> $l_online_users,
	);
}

/**
* Get option bitfield from custom data
*
* @param int	$bit		The bit/value to get
* @param int	$data		Current bitfield to check
* @return bool	Returns true if value of constant is set in bitfield, else false
*/
function phpbb_optionget($bit, $data)
{
	return ($data & 1 << (int) $bit) ? true : false;
}

/**
* Set option bitfield
*
* @param int	$bit		The bit/value to set/unset
* @param bool	$set		True if option should be set, false if option should be unset.
* @param int	$data		Current bitfield to change
*
* @return int	The new bitfield
*/
function phpbb_optionset($bit, $set, $data)
{
	if ($set && !($data & 1 << $bit))
	{
		$data += 1 << $bit;
	}
	else if (!$set && ($data & 1 << $bit))
	{
		$data -= 1 << $bit;
	}

	return $data;
}

/**
* Login using http authenticate.
*
* @param array	$param		Parameter array, see $param_defaults array.
*
* @return null
*/
function phpbb_http_login($param)
{
	global $auth, $user;
	global $config;

	$param_defaults = array(
		'auth_message'	=> '',

		'autologin'		=> false,
		'viewonline'	=> true,
		'admin'			=> false,
	);

	// Overwrite default values with passed values
	$param = array_merge($param_defaults, $param);

	// User is already logged in
	// We will not overwrite his session
	if (!empty($user->data['is_registered']))
	{
		return;
	}

	// $_SERVER keys to check
	$username_keys = array(
		'PHP_AUTH_USER',
		'Authorization',
		'REMOTE_USER', 'REDIRECT_REMOTE_USER',
		'HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION',
		'REMOTE_AUTHORIZATION', 'REDIRECT_REMOTE_AUTHORIZATION',
		'AUTH_USER',
	);

	$password_keys = array(
		'PHP_AUTH_PW',
		'REMOTE_PASSWORD',
		'AUTH_PASSWORD',
	);

	$username = null;
	foreach ($username_keys as $k)
	{
		if (isset($_SERVER[$k]))
		{
			$username = $_SERVER[$k];
			break;
		}
	}

	$password = null;
	foreach ($password_keys as $k)
	{
		if (isset($_SERVER[$k]))
		{
			$password = $_SERVER[$k];
			break;
		}
	}

	// Decode encoded information (IIS, CGI, FastCGI etc.)
	if (!is_null($username) && is_null($password) && strpos($username, 'Basic ') === 0)
	{
		list($username, $password) = explode(':', base64_decode(substr($username, 6)), 2);
	}

	if (!is_null($username) && !is_null($password))
	{
		set_var($username, $username, 'string', true);
		set_var($password, $password, 'string', true);

		$auth_result = $auth->login($username, $password, $param['autologin'], $param['viewonline'], $param['admin']);

		if ($auth_result['status'] == LOGIN_SUCCESS)
		{
			return;
		}
		else if ($auth_result['status'] == LOGIN_ERROR_ATTEMPTS)
		{
			send_status_line(401, 'Unauthorized');

			trigger_error('NOT_AUTHORISED');
		}
	}

	// Prepend sitename to auth_message
	$param['auth_message'] = ($param['auth_message'] === '') ? $config['sitename'] : $config['sitename'] . ' - ' . $param['auth_message'];

	// We should probably filter out non-ASCII characters - RFC2616
	$param['auth_message'] = preg_replace('/[\x80-\xFF]/', '?', $param['auth_message']);

	header('WWW-Authenticate: Basic realm="' . $param['auth_message'] . '"');
	send_status_line(401, 'Unauthorized');

	trigger_error('NOT_AUTHORISED');
}

/**
* Generate page header
*/
function page_header($page_title = '', $display_online_list = true, $item_id = 0, $item = 'forum')
{
	global $db, $config, $template, $SID, $_SID, $_EXTRA_URL, $user, $auth, $phpEx, $phpbb_root_path;

	if (defined('HEADER_INC'))
	{
		return;
	}

	define('HEADER_INC', true);

	// gzip_compression
	if ($config['gzip_compress'])
	{
		// to avoid partially compressed output resulting in blank pages in
		// the browser or error messages, compression is disabled in a few cases:
		//
		// 1) if headers have already been sent, this indicates plaintext output
		//    has been started so further content must not be compressed
		// 2) the length of the current output buffer is non-zero. This means
		//    there is already some uncompressed content in this output buffer
		//    so further output must not be compressed
		// 3) if more than one level of output buffering is used because we
		//    cannot test all output buffer level content lengths. One level
		//    could be caused by php.ini output_buffering. Anything
		//    beyond that is manual, so the code wrapping phpBB in output buffering
		//    can easily compress the output itself.
		//
		if (@extension_loaded('zlib') && !headers_sent() && ob_get_level() <= 1 && ob_get_length() == 0)
		{
			ob_start('ob_gzhandler');
		}
	}

	// Generate logged in/logged out status
	if ($user->data['user_id'] != ANONYMOUS)
	{
		$u_login_logout = append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=logout', true, $user->session_id);
		$l_login_logout = sprintf($user->lang['LOGOUT_USER'], $user->data['username']);
	}
	else
	{
		$u_login_logout = append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login');
		$l_login_logout = $user->lang['LOGIN'];
	}

	// Last visit date/time
	$s_last_visit = ($user->data['user_id'] != ANONYMOUS) ? $user->format_date($user->data['session_last_visit']) : '';

	// Get users online list ... if required
	$l_online_users = $online_userlist = $l_online_record = $l_online_time = '';

	if ($config['load_online'] && $config['load_online_time'] && $display_online_list)
	{
		/**
		* Load online data:
		* For obtaining another session column use $item and $item_id in the function-parameter, whereby the column is session_{$item}_id.
		*/
		$item_id = max($item_id, 0);

		$online_users = obtain_users_online($item_id, $item);
		$user_online_strings = obtain_users_online_string($online_users, $item_id, $item);

		$l_online_users = $user_online_strings['l_online_users'];
		$online_userlist = $user_online_strings['online_userlist'];
		$total_online_users = $online_users['total_online'];

		if ($total_online_users > $config['record_online_users'])
		{
			set_config('record_online_users', $total_online_users, true);
			set_config('record_online_date', time(), true);
		}

		$l_online_record = sprintf($user->lang['RECORD_ONLINE_USERS'], $config['record_online_users'], $user->format_date($config['record_online_date'], false, true));

		$l_online_time = ($config['load_online_time'] == 1) ? 'VIEW_ONLINE_TIME' : 'VIEW_ONLINE_TIMES';
		$l_online_time = sprintf($user->lang[$l_online_time], $config['load_online_time']);
	}

	$l_privmsgs_text = $l_privmsgs_text_unread = '';
	$s_privmsg_new = false;

	// Obtain number of new private messages if user is logged in
	if (!empty($user->data['is_registered']))
	{
		if ($user->data['user_new_privmsg'])
		{
			$l_message_new = ($user->data['user_new_privmsg'] == 1) ? $user->lang['NEW_PM'] : $user->lang['NEW_PMS'];
			$l_privmsgs_text = sprintf($l_message_new, $user->data['user_new_privmsg']);

			if (!$user->data['user_last_privmsg'] || $user->data['user_last_privmsg'] > $user->data['session_last_visit'])
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_last_privmsg = ' . $user->data['session_last_visit'] . '
					WHERE user_id = ' . $user->data['user_id'];
				$db->sql_query($sql);

				$s_privmsg_new = true;
			}
			else
			{
				$s_privmsg_new = false;
			}
		}
		else
		{
			$l_privmsgs_text = $user->lang['NO_NEW_PM'];
			$s_privmsg_new = false;
		}

		$l_privmsgs_text_unread = '';

		if ($user->data['user_unread_privmsg'] && $user->data['user_unread_privmsg'] != $user->data['user_new_privmsg'])
		{
			$l_message_unread = ($user->data['user_unread_privmsg'] == 1) ? $user->lang['UNREAD_PM'] : $user->lang['UNREAD_PMS'];
			$l_privmsgs_text_unread = sprintf($l_message_unread, $user->data['user_unread_privmsg']);
		}
	}

	$forum_id = request_var('f', 0);
	$topic_id = request_var('t', 0);

	$s_feed_news = false;

	// Get option for news
	if ($config['feed_enable'])
	{
		$sql = 'SELECT forum_id
			FROM ' . FORUMS_TABLE . '
			WHERE ' . $db->sql_bit_and('forum_options', FORUM_OPTION_FEED_NEWS, '<> 0');
		$result = $db->sql_query_limit($sql, 1, 0, 600);
		$s_feed_news = (int) $db->sql_fetchfield('forum_id');
		$db->sql_freeresult($result);
	}

	// Determine board url - we may need it later
	$board_url = generate_board_url() . '/';
	$web_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? $board_url : $phpbb_root_path;

	// Which timezone?
	$tz = ($user->data['user_id'] != ANONYMOUS) ? strval(doubleval($user->data['user_timezone'])) : strval(doubleval($config['board_timezone']));

	// Send a proper content-language to the output
	$user_lang = $user->lang['USER_LANG'];
	if (strpos($user_lang, '-x-') !== false)
	{
		$user_lang = substr($user_lang, 0, strpos($user_lang, '-x-'));
	}

	$s_search_hidden_fields = array();
	if ($_SID)
	{
		$s_search_hidden_fields['sid'] = $_SID;
	}

	if (!empty($_EXTRA_URL))
	{
		foreach ($_EXTRA_URL as $url_param)
		{
			$url_param = explode('=', $url_param, 2);
			$s_search_hidden_fields[$url_param[0]] = $url_param[1];
		}
	}

	// The following assigns all _common_ variables that may be used at any point in a template.
	$template->assign_vars(array(
		'SITENAME'						=> $config['sitename'],
		'SITE_DESCRIPTION'				=> $config['site_desc'],
		'PAGE_TITLE'					=> $page_title,
		'SCRIPT_NAME'					=> str_replace('.' . $phpEx, '', $user->page['page_name']),
		'LAST_VISIT_DATE'				=> sprintf($user->lang['YOU_LAST_VISIT'], $s_last_visit),
		'LAST_VISIT_YOU'				=> $s_last_visit,
		'CURRENT_TIME'					=> sprintf($user->lang['CURRENT_TIME'], $user->format_date(time(), false, true)),
		'TOTAL_USERS_ONLINE'			=> $l_online_users,
		'LOGGED_IN_USER_LIST'			=> $online_userlist,
		'RECORD_USERS'					=> $l_online_record,
		'PRIVATE_MESSAGE_INFO'			=> $l_privmsgs_text,
		'PRIVATE_MESSAGE_INFO_UNREAD'	=> $l_privmsgs_text_unread,

		'S_USER_NEW_PRIVMSG'			=> $user->data['user_new_privmsg'],
		'S_USER_UNREAD_PRIVMSG'			=> $user->data['user_unread_privmsg'],
		'S_USER_NEW'					=> $user->data['user_new'],

		'SID'				=> $SID,
		'_SID'				=> $_SID,
		'SESSION_ID'		=> $user->session_id,
		'ROOT_PATH'			=> $phpbb_root_path,
		'BOARD_URL'			=> $board_url,

		'L_LOGIN_LOGOUT'	=> $l_login_logout,
		'L_INDEX'			=> $user->lang['FORUM_INDEX'],
		'L_ONLINE_EXPLAIN'	=> $l_online_time,

		'U_PRIVATEMSGS'			=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;folder=inbox'),
		'U_RETURN_INBOX'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;folder=inbox'),
		'U_POPUP_PM'			=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;mode=popup'),
		'UA_POPUP_PM'			=> addslashes(append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;mode=popup')),
		'U_MEMBERLIST'			=> append_sid("{$phpbb_root_path}memberlist.$phpEx"),
		'U_VIEWONLINE'			=> ($auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel')) ? append_sid("{$phpbb_root_path}viewonline.$phpEx") : '',
		'U_LOGIN_LOGOUT'		=> $u_login_logout,
		'U_INDEX'				=> append_sid("{$phpbb_root_path}index.$phpEx"),
		'U_SEARCH'				=> append_sid("{$phpbb_root_path}search.$phpEx"),
		'U_REGISTER'			=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register'),
		'U_PROFILE'				=> append_sid("{$phpbb_root_path}ucp.$phpEx"),
		'U_MODCP'				=> append_sid("{$phpbb_root_path}mcp.$phpEx", false, true, $user->session_id),
		'U_FAQ'					=> append_sid("{$phpbb_root_path}faq.$phpEx"),
		'U_SEARCH_SELF'			=> append_sid("{$phpbb_root_path}search.$phpEx", 'search_id=egosearch'),
		'U_SEARCH_NEW'			=> append_sid("{$phpbb_root_path}search.$phpEx", 'search_id=newposts'),
		'U_SEARCH_UNANSWERED'	=> append_sid("{$phpbb_root_path}search.$phpEx", 'search_id=unanswered'),
		'U_SEARCH_UNREAD'		=> append_sid("{$phpbb_root_path}search.$phpEx", 'search_id=unreadposts'),
		'U_SEARCH_ACTIVE_TOPICS'=> append_sid("{$phpbb_root_path}search.$phpEx", 'search_id=active_topics'),
		'U_DELETE_COOKIES'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=delete_cookies'),
		'U_TEAM'				=> ($user->data['user_id'] != ANONYMOUS && !$auth->acl_get('u_viewprofile')) ? '' : append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=leaders'),
		'U_TERMS_USE'			=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=terms'),
		'U_PRIVACY'				=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=privacy'),
		'U_RESTORE_PERMISSIONS'	=> ($user->data['user_perm_from'] && $auth->acl_get('a_switchperm')) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=restore_perm') : '',
		'U_FEED'				=> generate_board_url() . "/feed.$phpEx",

		'S_USER_LOGGED_IN'		=> ($user->data['user_id'] != ANONYMOUS) ? true : false,
		'S_AUTOLOGIN_ENABLED'	=> ($config['allow_autologin']) ? true : false,
		'S_BOARD_DISABLED'		=> ($config['board_disable']) ? true : false,
		'S_REGISTERED_USER'		=> (!empty($user->data['is_registered'])) ? true : false,
		'S_IS_BOT'				=> (!empty($user->data['is_bot'])) ? true : false,
		'S_USER_PM_POPUP'		=> $user->optionget('popuppm'),
		'S_USER_LANG'			=> $user_lang,
		'S_USER_BROWSER'		=> (isset($user->data['session_browser'])) ? $user->data['session_browser'] : $user->lang['UNKNOWN_BROWSER'],
		'S_USERNAME'			=> $user->data['username'],
		'S_CONTENT_DIRECTION'	=> $user->lang['DIRECTION'],
		'S_CONTENT_FLOW_BEGIN'	=> ($user->lang['DIRECTION'] == 'ltr') ? 'left' : 'right',
		'S_CONTENT_FLOW_END'	=> ($user->lang['DIRECTION'] == 'ltr') ? 'right' : 'left',
		'S_CONTENT_ENCODING'	=> 'UTF-8',
		'S_TIMEZONE'			=> ($user->data['user_dst'] || ($user->data['user_id'] == ANONYMOUS && $config['board_dst'])) ? sprintf($user->lang['ALL_TIMES'], $user->lang['tz'][$tz], $user->lang['tz']['dst']) : sprintf($user->lang['ALL_TIMES'], $user->lang['tz'][$tz], ''),
		'S_DISPLAY_ONLINE_LIST'	=> ($l_online_time) ? 1 : 0,
		'S_DISPLAY_SEARCH'		=> (!$config['load_search']) ? 0 : (isset($auth) ? ($auth->acl_get('u_search') && $auth->acl_getf_global('f_search')) : 1),
		'S_DISPLAY_PM'			=> ($config['allow_privmsg'] && !empty($user->data['is_registered']) && ($auth->acl_get('u_readpm') || $auth->acl_get('u_sendpm'))) ? true : false,
		'S_DISPLAY_MEMBERLIST'	=> (isset($auth)) ? $auth->acl_get('u_viewprofile') : 0,
		'S_NEW_PM'				=> ($s_privmsg_new) ? 1 : 0,
		'S_REGISTER_ENABLED'	=> ($config['require_activation'] != USER_ACTIVATION_DISABLE) ? true : false,
		'S_FORUM_ID'			=> $forum_id,
		'S_TOPIC_ID'			=> $topic_id,

		'S_LOGIN_ACTION'		=> ((!defined('ADMIN_START')) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login') : append_sid("index.$phpEx", false, true, $user->session_id)),
		'S_LOGIN_REDIRECT'		=> build_hidden_fields(array('redirect' => build_url())),

		'S_ENABLE_FEEDS'			=> ($config['feed_enable']) ? true : false,
		'S_ENABLE_FEEDS_OVERALL'	=> ($config['feed_overall']) ? true : false,
		'S_ENABLE_FEEDS_FORUMS'		=> ($config['feed_overall_forums']) ? true : false,
		'S_ENABLE_FEEDS_TOPICS'		=> ($config['feed_topics_new']) ? true : false,
		'S_ENABLE_FEEDS_TOPICS_ACTIVE'	=> ($config['feed_topics_active']) ? true : false,
		'S_ENABLE_FEEDS_NEWS'		=> ($s_feed_news) ? true : false,

		'S_LOAD_UNREADS'			=> ($config['load_unreads_search'] && ($config['load_anon_lastread'] || $user->data['is_registered'])) ? true : false,

		'S_SEARCH_HIDDEN_FIELDS'	=> build_hidden_fields($s_search_hidden_fields),

		'T_THEME_PATH'			=> "{$web_path}styles/" . rawurlencode($user->theme['theme_path']) . '/theme',
		'T_TEMPLATE_PATH'		=> "{$web_path}styles/" . rawurlencode($user->theme['template_path']) . '/template',
		'T_SUPER_TEMPLATE_PATH'	=> (isset($user->theme['template_inherit_path']) && $user->theme['template_inherit_path']) ? "{$web_path}styles/" . rawurlencode($user->theme['template_inherit_path']) . '/template' : "{$web_path}styles/" . rawurlencode($user->theme['template_path']) . '/template',
		'T_IMAGESET_PATH'		=> "{$web_path}styles/" . rawurlencode($user->theme['imageset_path']) . '/imageset',
		'T_IMAGESET_LANG_PATH'	=> "{$web_path}styles/" . rawurlencode($user->theme['imageset_path']) . '/imageset/' . $user->lang_name,
		'T_IMAGES_PATH'			=> "{$web_path}images/",
		'T_SMILIES_PATH'		=> "{$web_path}{$config['smilies_path']}/",
		'T_AVATAR_PATH'			=> "{$web_path}{$config['avatar_path']}/",
		'T_AVATAR_GALLERY_PATH'	=> "{$web_path}{$config['avatar_gallery_path']}/",
		'T_ICONS_PATH'			=> "{$web_path}{$config['icons_path']}/",
		'T_RANKS_PATH'			=> "{$web_path}{$config['ranks_path']}/",
		'T_UPLOAD_PATH'			=> "{$web_path}{$config['upload_path']}/",
		'T_STYLESHEET_LINK'		=> (!$user->theme['theme_storedb']) ? "{$web_path}styles/" . rawurlencode($user->theme['theme_path']) . '/theme/stylesheet.css' : append_sid("{$phpbb_root_path}style.$phpEx", 'id=' . $user->theme['style_id'] . '&amp;lang=' . $user->lang_name),
		'T_STYLESHEET_NAME'		=> $user->theme['theme_name'],

		'T_THEME_NAME'			=> rawurlencode($user->theme['theme_path']),
		'T_TEMPLATE_NAME'		=> rawurlencode($user->theme['template_path']),
		'T_SUPER_TEMPLATE_NAME'	=> rawurlencode((isset($user->theme['template_inherit_path']) && $user->theme['template_inherit_path']) ? $user->theme['template_inherit_path'] : $user->theme['template_path']),
		'T_IMAGESET_NAME'		=> rawurlencode($user->theme['imageset_path']),
		'T_IMAGESET_LANG_NAME'	=> $user->data['user_lang'],
		'T_IMAGES'				=> 'images',
		'T_SMILIES'				=> $config['smilies_path'],
		'T_AVATAR'				=> $config['avatar_path'],
		'T_AVATAR_GALLERY'		=> $config['avatar_gallery_path'],
		'T_ICONS'				=> $config['icons_path'],
		'T_RANKS'				=> $config['ranks_path'],
		'T_UPLOAD'				=> $config['upload_path'],

		'SITE_LOGO_IMG'			=> $user->img('site_logo'),

		'A_COOKIE_SETTINGS'		=> addslashes('; path=' . $config['cookie_path'] . ((!$config['cookie_domain'] || $config['cookie_domain'] == 'localhost' || $config['cookie_domain'] == '127.0.0.1') ? '' : '; domain=' . $config['cookie_domain']) . ((!$config['cookie_secure']) ? '' : '; secure')),
	));

	// application/xhtml+xml not used because of IE
	header('Content-type: text/html; charset=UTF-8');

	header('Cache-Control: private, no-cache="set-cookie"');
	header('Expires: 0');
	header('Pragma: no-cache');

	if (!empty($user->data['is_bot']))
	{
		// Let reverse proxies know we detected a bot.
		header('X-PHPBB-IS-BOT: yes');
	}

	return;
}

/**
* Generate page footer
*/
function page_footer($run_cron = true)
{
	global $db, $config, $template, $user, $auth, $cache, $starttime, $phpbb_root_path, $phpEx;

	// Output page creation time
	if (defined('DEBUG'))
	{
		$mtime = explode(' ', microtime());
		$totaltime = $mtime[0] + $mtime[1] - $starttime;

		if (!empty($_REQUEST['explain']) && $auth->acl_get('a_') && defined('DEBUG_EXTRA') && method_exists($db, 'sql_report'))
		{
			$db->sql_report('display');
		}

		$debug_output = sprintf('Time : %.3fs | ' . $db->sql_num_queries() . ' Queries | GZIP : ' . (($config['gzip_compress'] && @extension_loaded('zlib')) ? 'On' : 'Off') . (($user->load) ? ' | Load : ' . $user->load : ''), $totaltime);

		if ($auth->acl_get('a_') && defined('DEBUG_EXTRA'))
		{
			if (function_exists('memory_get_usage'))
			{
				if ($memory_usage = memory_get_usage())
				{
					global $base_memory_usage;
					$memory_usage -= $base_memory_usage;
					$memory_usage = get_formatted_filesize($memory_usage);

					$debug_output .= ' | Memory Usage: ' . $memory_usage;
				}
			}

			$debug_output .= ' | <a href="' . build_url() . '&amp;explain=1">Explain</a>';
		}
	}

	$template->assign_vars(array(
		'DEBUG_OUTPUT'			=> (defined('DEBUG')) ? $debug_output : '',
		'TRANSLATION_INFO'		=> (!empty($user->lang['TRANSLATION_INFO'])) ? $user->lang['TRANSLATION_INFO'] : '',
		'CREDIT_LINE'			=> $user->lang('POWERED_BY', '<a href="https://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Group'),

		'U_ACP' => ($auth->acl_get('a_') && !empty($user->data['is_registered'])) ? append_sid("{$phpbb_root_path}adm/index.$phpEx", false, true, $user->session_id) : '')
	);

	// Call cron-type script
	$call_cron = false;
	if (!defined('IN_CRON') && $run_cron && !$config['board_disable'] && !$user->data['is_bot'])
	{
		$call_cron = true;
		$time_now = (!empty($user->time_now) && is_int($user->time_now)) ? $user->time_now : time();

		// Any old lock present?
		if (!empty($config['cron_lock']))
		{
			$cron_time = explode(' ', $config['cron_lock']);

			// If 1 hour lock is present we do not call cron.php
			if ($cron_time[0] + 3600 >= $time_now)
			{
				$call_cron = false;
			}
		}
	}

	// Call cron job?
	if ($call_cron)
	{
		$cron_type = '';

		if ($time_now - $config['queue_interval'] > $config['last_queue_run'] && !defined('IN_ADMIN') && file_exists($phpbb_root_path . 'cache/queue.' . $phpEx))
		{
			// Process email queue
			$cron_type = 'queue';
		}
		else if (method_exists($cache, 'tidy') && $time_now - $config['cache_gc'] > $config['cache_last_gc'])
		{
			// Tidy the cache
			$cron_type = 'tidy_cache';
		}
		else if ($config['warnings_expire_days'] && ($time_now - $config['warnings_gc'] > $config['warnings_last_gc']))
		{
			$cron_type = 'tidy_warnings';
		}
		else if ($time_now - $config['database_gc'] > $config['database_last_gc'])
		{
			// Tidy the database
			$cron_type = 'tidy_database';
		}
		else if ($time_now - $config['search_gc'] > $config['search_last_gc'])
		{
			// Tidy the search
			$cron_type = 'tidy_search';
		}
		else if ($time_now - $config['session_gc'] > $config['session_last_gc'])
		{
			$cron_type = 'tidy_sessions';
		}

		if ($cron_type)
		{
			$template->assign_var('RUN_CRON_TASK', '<img src="' . append_sid($phpbb_root_path . 'cron.' . $phpEx, 'cron_type=' . $cron_type) . '" width="1" height="1" alt="cron" />');
		}
	}

	$template->display('body');

	garbage_collection();
	exit_handler();
}

/**
* Closing the cache object and the database
* Cool function name, eh? We might want to add operations to it later
*/
function garbage_collection()
{
	global $cache, $db;

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
* This function supports hooks.
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

	// As a pre-caution... some setups display a blank page if the flush() is not there.
	(ob_get_level() > 0) ? @ob_flush() : @flush();

	exit;
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

?>