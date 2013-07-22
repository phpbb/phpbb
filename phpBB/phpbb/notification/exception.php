<?php
/**
*
* @package notifications
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
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
* Notifications exception
*
* @package notifications
*/
class phpbb_notification_exception extends \Exception
{
	public function __toString()
	{
		return $this->getMessage();
	}
}
