<?php
/**
*
* @package controller
* @copyright (c) 2012 phpBB Group
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
* @package controller
*/
interface phpbb_controller_interface
{
	/**
	* Handle calls to the controller
	*
	* @return null
	*/
	public function handle();

	/**
	* Should only return a string value for the access name of the controller
	*
	* Example:
	* 	return 'my_controller';
	*
	* @return string Access name
	*/
	public function get_access_name();
}
