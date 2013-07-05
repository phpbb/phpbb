<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * Mock provider class with basic functions to help test
 * sessions.
 */
class phpbb_provider {

	function autologin()
	{
		return array();
	}

	function kill()
	{

	}

	function validate_session($data)
	{
		return true;
	}
}
