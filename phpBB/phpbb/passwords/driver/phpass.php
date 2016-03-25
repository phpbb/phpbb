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

namespace phpbb\passwords\driver;

class phpass extends salted_md5
{
	const PREFIX = '$P$';

	/**
	* {@inheritdoc}
	*/
	public function get_prefix()
	{
		return self::PREFIX;
	}
}
