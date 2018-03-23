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

class convert_password extends base
{
	const PREFIX = '$CP$';

	/**
	* {@inheritdoc}
	*/
	public function get_prefix()
	{
		return self::PREFIX;
	}

	/**
	* {@inheritdoc}
	*/
	public function hash($password, $user_row = '')
	{
		return false;
	}

	/**
	* {@inheritdoc}
	*/
	public function check($password, $hash, $user_row = array())
	{
		return false;
	}
}
