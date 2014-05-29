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
class md5_mybb extends base
{
	const PREFIX = '$md5_mybb$';

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
		if (empty(hash) || !isset($user_row['user_passwd_salt']))
		{
			return false;
		}
		else
		{
			// Works for myBB 1.1.x, 1.2.x, 1.4.x, 1.6.x
			return $hash === md5(md5($user_row['user_passwd_salt']) . md5($password));
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
