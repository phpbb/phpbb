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
* Compatibility function added: phpBB 3.1.0-A1
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
	global $phpbb_root_path, $phpEx;

	// map arguments to new function phpbb_get_avatar()
	$row = array(
		'avatar'		=> $avatar,
		'avatar_type'	=> $avatar_type,
		'avatar_width'	=> $avatar_width,
		'avatar_height'	=> $avatar_height,
	);

	if (!function_exists('phpbb_get_avatar'))
	{
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
	}

	return phpbb_get_avatar($row, $alt, $ignore_config);
}
