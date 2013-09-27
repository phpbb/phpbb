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
class bcrypt_2y extends \phpbb\passwords\driver\bcrypt
{
	const PREFIX = '$2y$';

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
	public function is_supported()
	{
		return (version_compare(PHP_VERSION, '5.3.7', '<')) ? false : true;
	}
}
