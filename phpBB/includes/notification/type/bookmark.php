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
* Bookmark updating notifications class
* This class handles notifications for replies to a bookmarked topic
*
* @package notifications
*/
class phpbb_notification_type_bookmark extends phpbb_notification_type_post
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'bookmark';
	}

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_BOOKMARK';

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	public static $notification_option = array(
		'lang'	=> 'NOTIFICATION_TYPE_BOOKMARK',
		'group'	=> 'NOTIFICATION_GROUP_POSTING',
	);

	/**
	* Is available
	*/
	public function is_available()
	{
		return $this->config['allow_bookmarks'];
	}

	/**
	* Find the users who want to receive notifications
	*
	* @param array $post Data from
	*
	* @return array
	*/
	public function find_users_for_notification($post, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'		=> array(),
		), $options);

		$users = array();

		$sql = 'SELECT user_id
			FROM ' . BOOKMARKS_TABLE . '
			WHERE ' . $this->db->sql_in_set('topic_id', $post['topic_id']) . '
				AND user_id <> ' . (int) $post['poster_id'];
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$users[] = $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($users))
		{
			return array();
		}
		sort($users);

		$auth_read = $this->auth->acl_get_list($users, 'f_read', $post['forum_id']);

		if (empty($auth_read))
		{
			return array();
		}

		$notify_users = $this->check_user_notification_options($auth_read[$post['forum_id']]['f_read'], $options);

		// Try to find the users who already have been notified about replies and have not read the topic since and just update their notifications
		$update_notifications = array();
		$sql = 'SELECT n.*
			FROM ' . $this->notifications_table . ' n, ' . $this->notification_types_table . ' nt
			WHERE n.notification_type_id = ' . (int) $this->notification_type_id . '
				AND n.item_parent_id = ' . (int) self::get_item_parent_id($post) . '
				AND n.notification_read = 0
				AND nt.notification_type_id = n.notification_type_id
				AND nt.notification_type_enabled = 1';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Do not create a new notification
			unset($notify_users[$row['user_id']]);

			$notification = $this->notification_manager->get_item_type_class($this->get_type(), $row);
			$sql = 'UPDATE ' . $this->notifications_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $notification->add_responders($post)) . '
				WHERE notification_id = ' . $row['notification_id'];
			$this->db->sql_query($sql);
		}
		$this->db->sql_freeresult($result);

		return $notify_users;
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return 'bookmark';
	}
}
