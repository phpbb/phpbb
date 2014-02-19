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
 * Retrieve contents from remotely stored file
 *
 * @deprecated 3.1.0-a4 (To be removed: 3.3.0)
 */
function get_remote_file($host, $directory, $filename, &$errstr, &$errno, $port = 80, $timeout = 6)
{
	global $user;

	if ($fsock = @fsockopen($host, $port, $errno, $errstr, $timeout))
	{
		@fputs($fsock, "GET $directory/$filename HTTP/1.0\r\n");
		@fputs($fsock, "HOST: $host\r\n");
		@fputs($fsock, "Connection: close\r\n\r\n");

		$timer_stop = time() + $timeout;
		stream_set_timeout($fsock, $timeout);

		$file_info = '';
		$get_info = false;

		while (!@feof($fsock))
		{
			if ($get_info)
			{
				$file_info .= @fread($fsock, 1024);
			}
			else
			{
				$line = @fgets($fsock, 1024);
				if ($line == "\r\n")
				{
					$get_info = true;
				}
				else if (stripos($line, '404 not found') !== false)
				{
					$errstr = $user->lang['FILE_NOT_FOUND'] . ': ' . $filename;
					return false;
				}
			}

			$stream_meta_data = stream_get_meta_data($fsock);

			if (!empty($stream_meta_data['timed_out']) || time() >= $timer_stop)
			{
				$errstr = $user->lang['FSOCK_TIMEOUT'];
				return false;
			}
		}
		@fclose($fsock);
	}
	else
	{
		if ($errstr)
		{
			$errstr = utf8_convert_message($errstr);
			return false;
		}
		else
		{
			$errstr = $user->lang['FSOCK_DISABLED'];
			return false;
		}
	}

	return $file_info;
}
