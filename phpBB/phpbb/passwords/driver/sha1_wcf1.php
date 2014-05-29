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
class sha1_wcf1 extends base
{
	const PREFIX = '$wcf1$';

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
			// Works for standard WCF 1.x, i.e. WBB3 and similar
			return $hash === sha1($user_row['user_passwd_salt'] . sha1($user_row['user_passwd_salt'] . sha1($password)));
		}
	}
}
