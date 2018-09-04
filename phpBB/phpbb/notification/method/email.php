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
	protected $topics_watch_table;

	/** @var string */
	protected $topics_track_table;

	/** @var string */
	protected $posts_table;

	/** @var string */
	protected $forums_watch_table;

	/** @var string */
	protected $forums_track_table;

	/**
	 * Notification Method email Constructor
	 *
	 * @param \phpbb\user_loader $user_loader
	 * @param \phpbb\user $user
	 * @param \phpbb\config\config $config
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 * @param string $topics_watch_table
	 * @param string $topics_track_table
	 * @param string $posts_table
	 * @param string $forums_watch_table
	 * @param string $forums_track_table
	 */
	public function __construct(\phpbb\user_loader $user_loader, \phpbb\user $user, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, $phpbb_root_path, $php_ext, $topics_watch_table, $topics_track_table, $posts_table, $forums_watch_table, $forums_track_table)
	{
		parent::__construct($user_loader, $phpbb_root_path, $php_ext);

		$this->user = $user;
		$this->config = $config;
		$this->db = $db;
		$this->topics_watch_table = $topics_watch_table;
		$this->topics_track_table = $topics_track_table;
		$this->posts_table = $posts_table;
		$this->forums_watch_table = $forums_watch_table;
		$this->forums_track_table = $forums_track_table;
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

		if ($notification_type_id == 'notification.type.post' && !empty($options['item_parent_id']))
		{
			// Topics watch
			$sql = 'SELECT tw.user_id
					FROM ' . $this->topics_watch_table . ' tw
					LEFT JOIN ' . $this->topics_track_table . ' tt
						ON (tt.user_id = tw.user_id AND tt.topic_id = tw.topic_id)
					LEFT JOIN ' . $this->posts_table . ' p
						ON (p.topic_id = tw.topic_id)
					WHERE tw.topic_id = ' . (int) $options['item_parent_id'] . '
						AND p.post_time > tt.mark_time
					HAVING COUNT(p.post_id) > 1';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$notified_users[$row['user_id']] = $row;
			}
			$this->db->sql_freeresult($result);

			// Forums watch
			$sql = 'SELECT fw.user_id
					FROM ' . $this->forums_watch_table . ' fw
					LEFT JOIN ' . $this->forums_track_table . ' ft
						ON (ft.user_id = fw.user_id AND ft.forum_id = fw.forum_id)
					LEFT JOIN ' . $this->posts_table . ' p
						ON (p.forum_id = fw.forum_id)
					WHERE p.topic_id = ' . (int) $options['item_parent_id'] . '
						AND p.post_time > ft.mark_time
					HAVING COUNT(p.post_id) > 1';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$notified_users[$row['user_id']] = $row;
			}
			$this->db->sql_freeresult($result);
		}

		return $notified_users;
	}

	/**
	* Parse the queue and notify the users
	*/
	public function notify()
	{
		return $this->notify_using_messenger(NOTIFY_EMAIL);
	}
}
