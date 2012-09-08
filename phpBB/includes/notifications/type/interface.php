<?php
/**
*
* @package notifications
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
* Base notifications interface
* @package notifications
*/
interface phpbb_notifications_type_interface
{
	public function get_type();

	public function get_title();

	public function get_url();

	public function create_insert_array($special_data);
}
