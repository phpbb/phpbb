<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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

	if (!function_exists('phpbb_get_avatar'))
	{
		global $phpbb_root_path, $phpEx;

		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
	}

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
