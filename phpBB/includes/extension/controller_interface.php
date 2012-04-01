<?php
/**
*
* @package extension
* @copyright (c) 2011 phpBB Group
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
* The interface that extension classes have to implement to run front pages
*
* @package extension
*/
interface phpbb_extension_controller_interface
{
	/**
	* Handle the request to display a page from an extension
	*
	* @return	null
	*/
	public function handle();
}
