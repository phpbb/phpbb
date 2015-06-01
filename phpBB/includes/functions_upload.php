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
* Class for assigning error messages before a real filespec class can be assigned
*/
class fileerror extends \phpbb\files\filespec
{
	function fileerror($error_msg)
	{
		$this->error[] = $error_msg;
	}
}
