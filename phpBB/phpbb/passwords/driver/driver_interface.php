<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\passwords\driver;

interface driver_interface
{
	/**
	* Check if hash type is supported
	*
	* @return bool		True if supported, false if not
	*/
	public function is_supported();

	/**
	* Check if hash type is a legacy hash type
	*
	* @return bool		True if it's a legacy hash type, false if not
	*/
	public function is_legacy();

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
	* @param array		$user_row User's row in users table
	*
	* @return bool		True if password is correct, else false
	*/
	public function check($password, $hash, $user_row = array());

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
