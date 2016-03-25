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

class md5_vb extends base
{
	const PREFIX = '$md5_vb$';

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
	public function is_legacy()
	{
		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function hash($password, $user_row = '')
	{
		// Do not support hashing
		return false;
	}

	/**
	* {@inheritdoc}
	*/
	public function check($password, $hash, $user_row = array())
	{
		if (empty($hash) || strlen($hash) != 32 || !isset($user_row['user_passwd_salt']))
		{
			return false;
		}
		else
		{
			// Works for vB 3.8.x, 4.x.x, 5.0.x
			return $this->helper->string_compare($hash, md5(md5($password) . $user_row['user_passwd_salt']));
		}
	}
}
