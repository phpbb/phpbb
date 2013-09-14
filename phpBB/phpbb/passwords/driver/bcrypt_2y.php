<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

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
class phpbb_passwords_driver_bcrypt_2y extends phpbb_passwords_driver_bcrypt
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
	public function get_type()
	{
		return get_class($this);
	}

	/**
	* @inheritdoc
	*/
	public function is_supported()
	{
		return (version_compare(PHP_VERSION, '5.3.7', '<')) ? false : true;
	}
}
