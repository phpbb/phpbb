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

class sha_xf1 extends base
{
	const PREFIX = '$xf1$';

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
		if (empty($hash) || (strlen($hash) != 40 && strlen($hash) != 64) || !isset($user_row['user_passwd_salt']))
		{
			return false;
		}
		else
		{
			// Works for xenforo 1.0, 1.1
			if ($this->helper->string_compare($hash, sha1(sha1($password) . $user_row['user_passwd_salt']))
				|| $this->helper->string_compare($hash, hash('sha256', hash('sha256', $password) . $user_row['user_passwd_salt'])))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
}
