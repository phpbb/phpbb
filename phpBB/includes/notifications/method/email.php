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
* Email notification method class
* This class handles sending emails for notifications
*
* @package notifications
*/
class phpbb_notifications_method_email extends phpbb_notifications_method_base
{
	public static function is_available()
	{
		// Email is always available
		return true;
	}

	public function notify()
	{
		// email the user
	}
}
