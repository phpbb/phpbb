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
* Controller interface
* @package phpBB3
*/
interface phpbb_controller_interface
{
	/**
	* Handle the loading of the controller page.
	*
	* @return Symfony\Component\HttpFoundation\Response Symfony Response
	*/
	public function handle();
}
