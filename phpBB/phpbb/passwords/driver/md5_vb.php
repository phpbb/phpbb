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
class md5_vb extends base
{
	const PREFIX = '$md5_vb$';

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
			// Works for vB 3.8.x, 4.x.x, 5.0.x
			return $hash === md5(md5($password) . $user_row['user_passwd_salt']);
		}
	}
}
