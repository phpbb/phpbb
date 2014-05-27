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
 * Mock fileupload class with some basic values to help with testing the
 * filespec class
 */
class phpbb_mock_fileupload
{
	public $max_filesize = 100;
	public $error_prefix = '';

	public function valid_dimensions($filespec)
	{
		return true;
	}
}
