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
* @package crypto
*/
interface phpbb_crypto_driver_interface
{
	/**
	* Check if hash type is supported
	*
	* @return bool		True if supported, false if not
	*/
	public function is_supported();
	/**
	* Returns the hash prefix
	*
	* @return string	Hash prefix
	*/
	public function get_prefix();

	/**
	* Returns the name of the hash type
	*
	* @return string	Hash type of driver
	*/
	public function get_type();

	/**
	* Hash the password
	*
	* @return string	Password hash
	*/
	public function hash($password);

	/**
	* Check the password against the supplied hash
	*
	* @return bool		True if password is correct, else false
	*/
	public function check($password, $hash);
}
