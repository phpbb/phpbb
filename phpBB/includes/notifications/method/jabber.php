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
* Jabber notification method class
* This class handles sending Jabber notifications for notifications
*
* @package notifications
*/
class phpbb_notifications_method_jabber extends phpbb_notifications_method_base
{
	public static function is_available()
	{
		// Is jabber enabled & can this user receive jabber messages?
		return false; // for now
	}

	public function notify()
	{
		// message the user
	}
}
