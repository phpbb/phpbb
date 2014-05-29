<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\passwords\driver;

/**
* @package passwords
*/
class sha_xf1 extends base
{
	const PREFIX = '$xf1$';

	/**
	* @inheritdoc
	*/
	public function get_prefix()
	{
		return self::PREFIX;
	}

	/**
	* @inheritdoc
	*/
	public function is_legacy()
	{
		return true;
	}

	/**
	* @inheritdoc
	*/
	public function hash($password, $user_row = '')
	{
		// Do not support hashing
		return false;
	}

	/**
	* @inheritdoc
	*/
	public function check($password, $hash, $user_row = array())
	{
		if (empty($hash) || !isset($user_row['user_passwd_salt']))
		{
			return false;
		}
		else
		{
			// Works for xenforo 1.0, 1.1
			if ($hash === sha1(sha1($password) . $user_row['user_passwd_salt'])
				|| $hash === hash('sha256', hash('sha256', $password) . $user_row['user_passwd_salt']))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	/**
	* @inheritdoc
	*/
	public function get_settings_only($hash, $full = false)
	{
		return false;
	}
}
