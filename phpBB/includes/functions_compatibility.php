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
* @return bool Always true
* @deprecated 3.2.0-dev
*/
function phpbb_pcre_utf8_support()
{
	return true;
}
