<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\passwords\driver;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package passwords
*/
class phpass extends \phpbb\passwords\driver\salted_md5
{
	const PREFIX = '$P$';

	/**
	* @inheritdoc
	*/
	public function get_prefix()
	{
		return self::PREFIX;
	}
}
