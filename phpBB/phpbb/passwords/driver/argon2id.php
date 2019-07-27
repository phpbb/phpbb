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

class argon2id extends argon2i
{
	/**
	* {@inheritdoc}
	*/
	public function get_algo_name()
	{
		return 'PASSWORD_ARGON2ID';
	}

	/**
	* {@inheritdoc}
	*/
	public function get_prefix()
	{
		return '$argon2id$';
	}
}
