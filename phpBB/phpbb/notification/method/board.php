<?php
/**
*
* @package notifications
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\notification\method;

/**
* In Board notification method class
* This class handles in board notifications. This method is enabled by default.
*
* @package notifications
*/
class board extends \phpbb\notification\method\base
{

	/**
	* Add a notification to the queue
	*
	* @param \phpbb\notification\type\type_interface $notification
	*/
	public function add_to_queue(\phpbb\notification\type\type_interface $notification)
	{
		$this->queue[] = $notification;
	}

	/**
	* Get notification method name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'board';
	}

	/**
	* Is this method available for the user?
	* This is checked on the notifications options
	*/
	public function is_available()
	{
		return $this->config['allow_board_notifications'];
	}

	/**
	* Parse the queue and notify the users
	*/
	public function notify()
	{
		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, $this->notification_manager->getNotificationsTable());

		foreach ($this->queue as $notification)
		{
			$data = $notification->get_insert_array();
			$insert_buffer->insert($data);
		}

		$insert_buffer->flush();

		// We're done, empty the queue
		$this->empty_queue();
	}

	/**
	* Is the method enable by default?
	*
	* @return bool
	*/
	public function is_enabled_by_default()
	{
		return true;
	}
}
