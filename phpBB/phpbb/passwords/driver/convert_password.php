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
class convert_password extends base
{
	const PREFIX = '$CP$';

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
	public function hash($password, $user_row = '')
	{
		return false;
	}

	/**
	* @inheritdoc
	*/
	public function check($password, $hash, $user_row = array())
	{
		return false;
	}
}
