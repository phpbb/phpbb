<?php
/**
*
* @package extension
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
*
* @package extension
*/
interface phpbb_extension_interface
{
	public function enable();
	public function disable();
	public function purge();
}
