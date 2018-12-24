<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Get user avatar
*
* @deprecated 3.1.0-a1 (To be removed: 3.3.0)
*
* @param string $avatar Users assigned avatar name
* @param int $avatar_type Type of avatar
* @param string $avatar_width Width of users avatar
* @param string $avatar_height Height of users avatar
* @param string $alt Optional language string for alt tag within image, can be a language key or text
* @param bool $ignore_config Ignores the config-setting, to be still able to view the avatar in the UCP
* @param bool $lazy If true, will be lazy loaded (requires JS)
*
* @return string Avatar image
*/
function get_user_avatar($avatar, $avatar_type, $avatar_width, $avatar_height, $alt = 'USER_AVATAR', $ignore_config = false, $lazy = false)
{
	// map arguments to new function phpbb_get_avatar()
	$row = array(
		'avatar'		=> $avatar,
		'avatar_type'	=> $avatar_type,
		'avatar_width'	=> $avatar_width,
		'avatar_height'	=> $avatar_height,
	);

	return phpbb_get_avatar($row, $alt, $ignore_config, $lazy);
}

/**
* Hash the password
*
* @deprecated 3.1.0-a2 (To be removed: 3.3.0)
*
* @param string $password Password to be hashed
*
* @return string|bool Password hash or false if something went wrong during hashing
*/
function phpbb_hash($password)
{
	global $phpbb_container;

	/* @var $passwords_manager \phpbb\passwords\manager */
	$passwords_manager = $phpbb_container->get('passwords.manager');
	return $passwords_manager->hash($password);
}

/**
* Check for correct password
*
* @deprecated 3.1.0-a2 (To be removed: 3.3.0)
*
* @param string $password The password in plain text
* @param string $hash The stored password hash
*
* @return bool Returns true if the password is correct, false if not.
*/
function phpbb_check_hash($password, $hash)
{
	global $phpbb_container;

	/* @var $passwords_manager \phpbb\passwords\manager */
	$passwords_manager = $phpbb_container->get('passwords.manager');
	return $passwords_manager->check($password, $hash);
}

/**
* Eliminates useless . and .. components from specified path.
*
* Deprecated, use filesystem class instead
*
* @param string $path Path to clean
* @return string Cleaned path
*
* @deprecated 3.1.0 (To be removed: 3.3.0)
*/
function phpbb_clean_path($path)
{
	global $phpbb_path_helper, $phpbb_container;

	if (!$phpbb_path_helper && $phpbb_container)
	{
		/* @var $phpbb_path_helper \phpbb\path_helper */
		$phpbb_path_helper = $phpbb_container->get('path_helper');
	}
	else if (!$phpbb_path_helper)
	{
		global $phpbb_root_path, $phpEx;

		// The container is not yet loaded, use a new instance
		if (!class_exists('\phpbb\path_helper'))
		{
			require($phpbb_root_path . 'phpbb/path_helper.' . $phpEx);
		}

		$request = new phpbb\request\request();
		$phpbb_path_helper = new phpbb\path_helper(
			new phpbb\symfony_request(
				$request
			),
			new phpbb\filesystem\filesystem(),
			$request,
			$phpbb_root_path,
			$phpEx
		);
	}

	return $phpbb_path_helper->clean_path($path);
}

/**
* Pick a timezone
*
* @param	string		$default			A timezone to select
* @param	boolean		$truncate			Shall we truncate the options text
*
* @return		string		Returns the options for timezone selector only
*
* @deprecated 3.1.0 (To be removed: 3.3.0)
*/
function tz_select($default = '', $truncate = false)
{
	global $template, $user;

	return phpbb_timezone_select($template, $user, $default, $truncate);
}

/**
* Cache moderators. Called whenever permissions are changed
* via admin_permissions. Changes of usernames and group names
* must be carried through for the moderators table.
*
* @deprecated 3.1.0 (To be removed: 3.3.0)
* @return null
*/
function cache_moderators()
{
	global $db, $cache, $auth;
	return phpbb_cache_moderators($db, $cache, $auth);
}

/**
* Removes moderators and administrators from foe lists.
*
* @deprecated 3.1.0 (To be removed: 3.3.0)
* @param array|bool $group_id If an array, remove all members of this group from foe lists, or false to ignore
* @param array|bool $user_id If an array, remove this user from foe lists, or false to ignore
* @return null
*/
function update_foes($group_id = false, $user_id = false)
{
	global $db, $auth;
	return phpbb_update_foes($db, $auth, $group_id, $user_id);
}

/**
* Get user rank title and image
*
* @param int $user_rank the current stored users rank id
* @param int $user_posts the users number of posts
* @param string &$rank_title the rank title will be stored here after execution
* @param string &$rank_img the rank image as full img tag is stored here after execution
* @param string &$rank_img_src the rank image source is stored here after execution
*
* @deprecated 3.1.0-RC5 (To be removed: 3.3.0)
*
* Note: since we do not want to break backwards-compatibility, this function will only properly assign ranks to guests if you call it for them with user_posts == false
*/
function get_user_rank($user_rank, $user_posts, &$rank_title, &$rank_img, &$rank_img_src)
{
	global $phpbb_root_path, $phpEx;
	if (!function_exists('phpbb_get_user_rank'))
	{
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
	}

	$rank_data = phpbb_get_user_rank(array('user_rank' => $user_rank), $user_posts);
	$rank_title = $rank_data['title'];
	$rank_img = $rank_data['img'];
	$rank_img_src = $rank_data['img_src'];
}

/**
 * Retrieve contents from remotely stored file
 *
 * @deprecated	3.1.2	Use file_downloader instead
 */
function get_remote_file($host, $directory, $filename, &$errstr, &$errno, $port = 80, $timeout = 6)
{
	global $phpbb_container;

	// Get file downloader and assign $errstr and $errno
	/* @var $file_downloader \phpbb\file_downloader */
	$file_downloader = $phpbb_container->get('file_downloader');

	$file_data = $file_downloader->get($host, $directory, $filename, $port, $timeout);
	$errstr = $file_downloader->get_error_string();
	$errno = $file_downloader->get_error_number();

	return $file_data;
}

/**
 * Add log entry
 *
 * @param	string	$mode				The mode defines which log_type is used and from which log the entry is retrieved
 * @param	int		$forum_id			Mode 'mod' ONLY: forum id of the related item, NOT INCLUDED otherwise
 * @param	int		$topic_id			Mode 'mod' ONLY: topic id of the related item, NOT INCLUDED otherwise
 * @param	int		$reportee_id		Mode 'user' ONLY: user id of the reportee, NOT INCLUDED otherwise
 * @param	string	$log_operation		Name of the operation
 * @param	array	$additional_data	More arguments can be added, depending on the log_type
 *
 * @return	int|bool		Returns the log_id, if the entry was added to the database, false otherwise.
 *
 * @deprecated	3.1.0 (To be removed: 3.3.0)
 */
function add_log()
{
	global $phpbb_log, $user;

	$args = func_get_args();
	$mode = array_shift($args);

	// This looks kind of dirty, but add_log has some additional data before the log_operation
	$additional_data = array();
	switch ($mode)
	{
		case 'admin':
		case 'critical':
			break;
		case 'mod':
			$additional_data['forum_id'] = array_shift($args);
			$additional_data['topic_id'] = array_shift($args);
			break;
		case 'user':
			$additional_data['reportee_id'] = array_shift($args);
			break;
	}

	$log_operation = array_shift($args);
	$additional_data = array_merge($additional_data, $args);

	$user_id = (empty($user->data)) ? ANONYMOUS : $user->data['user_id'];
	$user_ip = (empty($user->ip)) ? '' : $user->ip;

	return $phpbb_log->add($mode, $user_id, $user_ip, $log_operation, time(), $additional_data);
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
 *
 * @deprecated 3.1.0 (To be removed: 3.3.0)
 */
function set_config($config_name, $config_value, $is_dynamic = false, \phpbb\config\config $set_config = null)
{
	static $config = null;

	if ($set_config !== null)
	{
		$config = $set_config;

		if (empty($config_name))
		{
			return;
		}
	}

	$config->set($config_name, $config_value, !$is_dynamic);
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
 *
 * @deprecated 3.1.0 (To be removed: 3.3.0)
 */
function set_config_count($config_name, $increment, $is_dynamic = false, \phpbb\config\config $set_config = null)
{
	static $config = null;
	if ($set_config !== null)
	{
		$config = $set_config;
		if (empty($config_name))
		{
			return;
		}
	}
	$config->increment($config_name, $increment, !$is_dynamic);
}

/**
 * Wrapper function of \phpbb\request\request::variable which exists for backwards compatability.
 * See {@link \phpbb\request\request_interface::variable \phpbb\request\request_interface::variable} for
 * documentation of this function's use.
 *
 * @deprecated 3.1.0 (To be removed: 3.3.0)
 * @param	mixed			$var_name	The form variable's name from which data shall be retrieved.
 * 										If the value is an array this may be an array of indizes which will give
 * 										direct access to a value at any depth. E.g. if the value of "var" is array(1 => "a")
 * 										then specifying array("var", 1) as the name will return "a".
 * 										If you pass an instance of {@link \phpbb\request\request_interface phpbb_request_interface}
 * 										as this parameter it will overwrite the current request class instance. If you do
 * 										not do so, it will create its own instance (but leave superglobals enabled).
 * @param	mixed			$default	A default value that is returned if the variable was not set.
 * 										This function will always return a value of the same type as the default.
 * @param	bool			$multibyte	If $default is a string this paramater has to be true if the variable may contain any UTF-8 characters
 *										Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks
 * @param	bool			$cookie		This param is mapped to \phpbb\request\request_interface::COOKIE as the last param for
 * 										\phpbb\request\request_interface::variable for backwards compatability reasons.
 * @param	\phpbb\request\request_interface|null|false	If an instance of \phpbb\request\request_interface is given the instance is stored in
 *										a static variable and used for all further calls where this parameters is null. Until
 *										the function is called with an instance it automatically creates a new \phpbb\request\request
 *										instance on every call. By passing false this per-call instantiation can be restored
 *										after having passed in a \phpbb\request\request_interface instance.
 *
 * @return	mixed	The value of $_REQUEST[$var_name] run through {@link set_var set_var} to ensure that the type is the
 * 					the same as that of $default. If the variable is not set $default is returned.
 */
function request_var($var_name, $default, $multibyte = false, $cookie = false, $request = null)
{
	// This is all just an ugly hack to add "Dependency Injection" to a function
	// the only real code is the function call which maps this function to a method.
	static $static_request = null;
	if ($request instanceof \phpbb\request\request_interface)
	{
		$static_request = $request;
		if (empty($var_name))
		{
			return;
		}
	}
	else if ($request === false)
	{
		$static_request = null;
		if (empty($var_name))
		{
			return;
		}
	}
	$tmp_request = $static_request;
	// no request class set, create a temporary one ourselves to keep backwards compatibility
	if ($tmp_request === null)
	{
		// false param: enable super globals, so the created request class does not
		// make super globals inaccessible everywhere outside this function.
		$tmp_request = new \phpbb\request\request(new \phpbb\request\type_cast_helper(), false);
	}
	return $tmp_request->variable($var_name, $default, $multibyte, ($cookie) ? \phpbb\request\request_interface::COOKIE : \phpbb\request\request_interface::REQUEST);
}

/**
 * Get tables of a database
 *
 * @deprecated 3.1.0 (To be removed: 3.3.0)
 */
function get_tables($db)
{
	$db_tools_factory = new \phpbb\db\tools\factory();
	$db_tools = $db_tools_factory->get($db);

	return $db_tools->sql_list_tables();
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
function phpbb_chmod($filename, $perms = CHMOD_READ)
{
	global $phpbb_filesystem;

	try
	{
		$phpbb_filesystem->phpbb_chmod($filename, $perms);
	}
	catch (\phpbb\filesystem\exception\filesystem_exception $e)
	{
		return false;
	}

	return true;
}

/**
 * Test if a file/directory is writable
 *
 * This function calls the native is_writable() when not running under
 * Windows and it is not disabled.
 *
 * @param string $file Path to perform write test on
 * @return bool True when the path is writable, otherwise false.
 *
 * @deprecated 3.2.0-dev	use \phpbb\filesystem\filesystem::is_writable() instead
 */
function phpbb_is_writable($file)
{
	global $phpbb_filesystem;

	return $phpbb_filesystem->is_writable($file);
}

/**
 * Checks if a path ($path) is absolute or relative
 *
 * @param string $path Path to check absoluteness of
 * @return boolean
 *
 * @deprecated 3.2.0-dev	use \phpbb\filesystem\filesystem::is_absolute_path() instead
 */
function phpbb_is_absolute($path)
{
	global $phpbb_filesystem;

	return $phpbb_filesystem->is_absolute_path($path);
}

/**
 * A wrapper for realpath
 *
 * @deprecated 3.2.0-dev	use \phpbb\filesystem\filesystem::realpath() instead
 */
function phpbb_realpath($path)
{
	global $phpbb_filesystem;

	return $phpbb_filesystem->realpath($path);
}

/**
 * Determine which plural form we should use.
 * For some languages this is not as simple as for English.
 *
 * @param $rule		int			ID of the plural rule we want to use, see https://area51.phpbb.com/docs/dev/32x/language/plurals.html
 * @param $number	int|float	The number we want to get the plural case for. Float numbers are floored.
 * @return	int		The plural-case we need to use for the number plural-rule combination
 *
 * @deprecated 3.2.0-dev (To be removed: 3.3.0)
 */
function phpbb_get_plural_form($rule, $number)
{
	global $phpbb_container;

	/** @var \phpbb\language\language $language */
	$language = $phpbb_container->get('language');
	return $language->get_plural_form($number, $rule);
}

/**
* @return bool Always true
* @deprecated 3.2.0-dev
*/
function phpbb_pcre_utf8_support()
{
	return true;
}
