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
*
* @return string Avatar image
*/
function get_user_avatar($avatar, $avatar_type, $avatar_width, $avatar_height, $alt = 'USER_AVATAR', $ignore_config = false)
{
	// map arguments to new function phpbb_get_avatar()
	$row = array(
		'avatar'		=> $avatar,
		'avatar_type'	=> $avatar_type,
		'avatar_width'	=> $avatar_width,
		'avatar_height'	=> $avatar_height,
	);

	return phpbb_get_avatar($row, $alt, $ignore_config);
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
* @deprecated
*/
function phpbb_clean_path($path)
{
	global $phpbb_path_helper, $phpbb_container;

	if (!$phpbb_path_helper && $phpbb_container)
	{
		$phpbb_path_helper = $phpbb_container->get('path_helper');
	}
	else if (!$phpbb_path_helper)
	{
		// The container is not yet loaded, use a new instance
		if (!class_exists('\phpbb\path_helper'))
		{
			global $phpbb_root_path, $phpEx;
			require($phpbb_root_path . 'phpbb/path_helper.' . $phpEx);
		}

		$phpbb_path_helper = new phpbb\path_helper(
			new phpbb\symfony_request(
				new phpbb\request\request()
			),
			new phpbb\filesystem(),
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
* @deprecated
*/
function tz_select($default = '', $truncate = false)
{
	global $user;

	$timezone_select = phpbb_timezone_select($user, $default, $truncate);
	return $timezone_select['tz_select'];
}

/**
* Cache moderators. Called whenever permissions are changed
* via admin_permissions. Changes of usernames and group names
* must be carried through for the moderators table.
*
* @deprecated 3.1
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
* @deprecated 3.1
* @param array|bool $group_id If an array, remove all members of this group from foe lists, or false to ignore
* @param array|bool $user_id If an array, remove this user from foe lists, or false to ignore
* @return null
*/
function update_foes($group_id = false, $user_id = false)
{
	global $db, $auth;
	return phpbb_update_foes($db, $auth, $group_id, $user_id);
}
