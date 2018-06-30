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
* Wraps an url into a simple html page. Used to display attachments in IE.
* this is a workaround for now; might be moved to template system later
* direct any complaints to 1 Microsoft Way, Redmond
*
* @deprecated: 3.3.0-dev (To be removed: 4.0.0)
*/
function wrap_img_in_html($src, $title)
{
	echo '<!DOCTYPE html>';
	echo '<html>';
	echo '<head>';
	echo '<meta charset="utf-8">';
	echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
	echo '<title>' . $title . '</title>';
	echo '</head>';
	echo '<body>';
	echo '<div>';
	echo '<img src="' . $src . '" alt="' . $title . '" />';
	echo '</div>';
	echo '</body>';
	echo '</html>';
}

/**
* Garbage Collection
*
* @param bool $exit		Whether to die or not.
*
* @return null
*
* @deprecated: 3.3.0-dev (To be removed: 4.0.0)
*/
function file_gc($exit = true)
{
	global $cache, $db;

	if (!empty($cache))
	{
		$cache->unload();
	}

	$db->sql_close();

	if ($exit)
	{
		exit;
	}
}

/**
* Check if the browser is internet explorer version 7+
*
* @param string $user_agent	User agent HTTP header
* @param int $version IE version to check against
*
* @return bool true if internet explorer version is greater than $version
*
* @deprecated: 3.3.0-dev (To be removed: 4.0.0)
*/
function phpbb_is_greater_ie_version($user_agent, $version)
{
	if (preg_match('/msie (\d+)/', strtolower($user_agent), $matches))
	{
		$ie_version = (int) $matches[1];
		return ($ie_version > $version);
	}
	else
	{
		return false;
	}
}
