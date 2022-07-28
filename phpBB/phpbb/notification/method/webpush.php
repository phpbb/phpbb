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

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\notification\type\type_interface;
use phpbb\user;
use phpbb\user_loader;

/**
* Web push notification method class
* This class handles sending push messages for notifications
*/

class webpush extends \phpbb\notification\method\messenger_base
{
	/** @var config */
	protected $config;

	/** @var driver_interface */
	protected $db;

	/** @var user */
	protected $user;

	/** @var string Notification web push table */
	protected $notification_webpush_table;

	/**
	 * Notification Method web push constructor
	 *
	 * @param user_loader $user_loader
	 * @param user $user
	 * @param config $config
	 * @param driver_interface $db
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 * @param string $notification_webpush_table
	 */
	public function __construct(user_loader $user_loader, user $user, config $config, driver_interface $db, string $phpbb_root_path, string $php_ext, string $notification_webpush_table)
	{
		parent::__construct($user_loader, $phpbb_root_path, $php_ext);

		$this->user = $user;
		$this->config = $config;
		$this->db = $db;
		$this->notification_webpush_table = $notification_webpush_table;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_type(): string
	{
		return 'notification.method.webpush';
	}

	/**
	* {@inheritDoc}
	*/
	public function is_available(type_interface $notification_type = null): bool
	{
		return parent::is_available($notification_type) && $this->config['webpush_enable'] && !empty($this->user->data['user_push_subscriptions']);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_notified_users($notification_type_id, array $options): array
	{
		$notified_users = [];

		$sql = 'SELECT user_id
			FROM ' . $this->notification_webpush_table . '
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
		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, $this->notification_webpush_table);

		/** @var type_interface $notification */
		foreach ($this->queue as $notification)
		{
			$data = self::clean_data($notification->get_insert_array());
			$insert_buffer->insert($data);
		}

		$insert_buffer->flush();

		// @todo: add actual web push code

		return false;
	}

	/**
	* {@inheritdoc}
	*/
	public function mark_notifications($notification_type_id, $item_id, $user_id, $time = false, $mark_read = true)
	{
		$sql = 'DELETE FROM ' . $this->notification_webpush_table . '
			WHERE ' . ($notification_type_id !== false ? $this->db->sql_in_set('notification_type_id', $notification_type_id) : '1=1') .
			($user_id !== false ? ' AND ' . $this->db->sql_in_set('user_id', $user_id) : '') .
			($item_id !== false ? ' AND ' . $this->db->sql_in_set('item_id', $item_id) : '');
		$this->db->sql_query($sql);
	}

	/**
	* {@inheritdoc}
	*/
	public function mark_notifications_by_parent($notification_type_id, $item_parent_id, $user_id, $time = false, $mark_read = true)
	{
		$sql = 'DELETE FROM ' . $this->notification_webpush_table . '
			WHERE ' . ($notification_type_id !== false ? $this->db->sql_in_set('notification_type_id', $notification_type_id) : '1=1') .
			($user_id !== false ? ' AND ' . $this->db->sql_in_set('user_id', $user_id) : '') .
			($item_parent_id !== false ? ' AND ' . $this->db->sql_in_set('item_parent_id', $item_parent_id, false, true) : '');
		$this->db->sql_query($sql);
	}

	/**
	 * Clean data to contain only what we need for webpush notifications table
	 *
	 * @param array $data Notification data
	 * @return array Cleaned notification data
	 */
	public static function clean_data(array $data)
	{
		$row = [
			'notification_type_id'	=> null,
			'item_id'				=> null,
			'item_parent_id'		=> null,
			'user_id'				=> null,
		];

		return array_intersect_key($data, $row);
	}
}
