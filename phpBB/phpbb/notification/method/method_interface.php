<?php
/**
*
* @package notifications
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\notification\method;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Base notifications method interface
* @package notifications
*/
interface method_interface
{
	/**
	* Get notification method name
	*
	* @return string
	*/
	public function get_type();

	/**
	* Is this method available for the user?
	* This is checked on the notifications options
	*/
	public function is_available();

	/**
	* Add a notification to the queue
	*
	* @param \phpbb\notification\type\type_interface $notification
	*/
	public function add_to_queue(\phpbb\notification\type\type_interface $notification);

	/**
	* Parse the queue and notify the users
	*/
	public function notify();
}
