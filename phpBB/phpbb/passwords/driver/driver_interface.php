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
* @package passwords
*/
interface driver_interface
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
	* Hash the password
	*
	* @param string $password The password that should be hashed
	*
	* @return bool|string	Password hash or false if something went wrong
	*			during hashing
	*/
	public function hash($password);

	/**
	* Check the password against the supplied hash
	*
	* @param string		$password The password to check
	* @param string		$hash The password hash to check against
	*
	* @return bool		True if password is correct, else false
	*/
	public function check($password, $hash);

	/**
	* Get only the settings of the specified hash
	*
	* @param string		$hash Password hash
	* @param bool		$full Return full settings or only settings
	*			related to the salt
	* @return string	String containing the hash settings
	*/
	public function get_settings_only($hash, $full = false);
}
