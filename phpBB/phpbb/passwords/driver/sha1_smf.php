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
class sha1_smf extends base
{
	const PREFIX = '$smf$';

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
		return (isset($user_row['login_name'])) ? sha1(strtolower($user_row['login_name']) . $password) : false;
	}

	/**
	* @inheritdoc
	*/
	public function check($password, $hash, $user_row = array())
	{
		return $hash === $this->hash($password, $user_row);
	}
}
