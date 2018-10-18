<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\notification\method;

use phpbb\notification\type\type_interface;

/**
* Email notification method class
* This class handles sending emails for notifications
*/

class email extends \phpbb\notification\method\messenger_base
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string */
	protected $email_notifications_table;

	/**
	 * Notification Method email Constructor
	 *
	 * @param \phpbb\user_loader $user_loader
	 * @param \phpbb\user $user
	 * @param \phpbb\config\config $config
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 * @param string $email_notifications_table
	 */
	public function __construct(\phpbb\user_loader $user_loader, \phpbb\user $user, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, $phpbb_root_path, $php_ext, $email_notifications_table)
	{
		parent::__construct($user_loader, $phpbb_root_path, $php_ext);

		$this->user = $user;
		$this->config = $config;
		$this->db = $db;
		$this->email_notifications_table = $email_notifications_table;
	}

	/**
	* Get notification method name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'notification.method.email';
	}

	/**
	* Is this method available for the user?
	* This is checked on the notifications options
	*
	* @param type_interface $notification_type  An optional instance of a notification type. If provided, this
	*											method additionally checks if the type provides an email template.
	* @return bool
	*/
	public function is_available(type_interface $notification_type = null)
	{
		return parent::is_available($notification_type) && $this->config['email_enable'] && !empty($this->user->data['user_email']);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_notified_users($notification_type_id, array $options)
	{
		$notified_users = array();

		$sql = 'SELECT user_id
			FROM ' . $this->email_notifications_table . '
			WHERE notification_type_id = ' . (int) $notification_type_id .
			(isset($options['item_id']) ? ' AND item_id = ' . (int) $options['item_id'] : '') .
			(isset($options['item_parent_id']) ? ' AND item_parent_id = ' . (int) $options['item_parent_id'] : '') .
			(isset($options['user_id']) ? ' AND user_id = ' . (int) $options['user_id'] : '');
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$notified_users[$row['user_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $notified_users;
	}

	/**
	* Parse the queue and notify the users
	*/
	public function notify()
	{
		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, $this->email_notifications_table);

		/** @var \phpbb\notification\type\type_interface $notification */
		foreach ($this->queue as $notification)
		{
			$data = $this->clean_data($notification->get_insert_array());
			$insert_buffer->insert($data);
		}

		$insert_buffer->flush();

		return $this->notify_using_messenger(NOTIFY_EMAIL);
	}

	/**
	* {@inheritdoc}
	*/
	public function mark_notifications($notification_type_id, $item_id, $user_id, $time = false, $mark_read = true)
	{
		$sql = 'DELETE FROM ' . $this->email_notifications_table . '
			WHERE ' . (($notification_type_id !== false) ? (is_array($notification_type_id) ? $this->db->sql_in_set('notification_type_id', $notification_type_id) : 'notification_type_id = ' . $notification_type_id) : '') .
			(($user_id !== false) ? ' AND ' . (is_array($user_id) ? $this->db->sql_in_set('user_id', $user_id) : 'user_id = ' . (int) $user_id) : '') .
			(($item_id !== false) ? ' AND ' . (is_array($item_id) ? $this->db->sql_in_set('item_id', $item_id) : 'item_id = ' . (int) $item_id) : '');
		$this->db->sql_query($sql);
	}

	/**
	* {@inheritdoc}
	*/
	public function mark_notifications_by_parent($notification_type_id, $item_parent_id, $user_id, $time = false, $mark_read = true)
	{
		$sql = 'DELETE FROM ' . $this->email_notifications_table . '
			WHERE ' . (($notification_type_id !== false) ? (is_array($notification_type_id) ? $this->db->sql_in_set('notification_type_id', $notification_type_id) : 'notification_type_id = ' . $notification_type_id) : '') .
			(($user_id !== false) ? ' AND ' . (is_array($user_id) ? $this->db->sql_in_set('user_id', $user_id) : 'user_id = ' . (int) $user_id) : '') .
			(($item_parent_id !== false) ? ' AND ' . (is_array($item_parent_id) ? $this->db->sql_in_set('item_parent_id', $item_parent_id, false, true) : 'item_parent_id = ' . (int) $item_parent_id) : '');
		$this->db->sql_query($sql);
	}

	/**
	 * Clean data to contain only what we need for email notifications table
	 *
	 * @param array $data Notification data
	 * @return array Cleaned notification data
	 */
	protected function clean_data(array $data)
	{
		$model = array(
			'notification_type_id'	=> null,
			'item_id'				=> null,
			'item_parent_id'		=> null,
			'user_id'				=> null,
		);

		return array_intersect_key($data, $model);
	}
}
