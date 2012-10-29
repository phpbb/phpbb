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
* Post quoting notifications class
* This class handles notifications for quoting users in a post
*
* @package notifications
*/
class phpbb_notification_type_quote extends phpbb_notification_type_post
{
	/**
	* regular expression to match to find usernames
	*
	* @var string
	*/
	protected static $regular_expression_match = '#\[quote=&quot;(.+?)&quot;#';

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_QUOTE';

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	public static $notification_option = array(
		'lang'	=> 'NOTIFICATION_TYPE_QUOTE',
		'group'	=> 'NOTIFICATION_GROUP_POSTING',
	);

	/**
	* Is available
	*/
	public function is_available()
	{
		return true;
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

		$usernames = false;
		preg_match_all(self::$regular_expression_match, $post['post_text'], $usernames);

		if (empty($usernames[1]))
		{
			return array();
		}

		$usernames[1] = array_unique($usernames[1]);

		$usernames = array_map('utf8_clean_string', $usernames[1]);

		$users = array();

		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . '
			WHERE ' . $this->db->sql_in_set('username_clean', $usernames) . '
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

		$auth_read = $this->auth->acl_get_list($users, 'f_read', $post['forum_id']);

		if (empty($auth_read))
		{
			return array();
		}

		$notify_users = $this->check_user_notification_options($auth_read[$post['forum_id']]['f_read'], $options);

		// Try to find the users who already have been notified about replies and have not read the topic since and just update their notifications
		$update_notifications = array();
		$sql = 'SELECT *
			FROM ' . NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . get_class($this) . "'
				AND item_parent_id = " . (int) self::get_item_parent_id($post) . '
				AND unread = 1
				AND is_enabled = 1';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Do not create a new notification
			unset($notify_users[$row['user_id']]);

			$notification = $this->notification_manager->get_item_type_class(get_class($this), $row);
			$sql = 'UPDATE ' . NOTIFICATIONS_TABLE . '
				SET ' . $this->db->sql_build_array('UPDATE', $notification->add_responders($post)) . '
				WHERE notification_id = ' . $row['notification_id'];
			$this->db->sql_query($sql);
		}
		$this->db->sql_freeresult($result);

		return $notify_users;
	}

	/**
	* Update a notification
	*
	* @param array $data Data specific for this type that will be updated
	*/
	public function update_notifications($post)
	{
		$old_notifications = array();
		$sql = 'SELECT user_id
			FROM ' . NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . get_class($this) . "'
				AND item_id = " . self::get_item_id($post) . '
				AND is_enabled = 1';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$old_notifications[] = $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		// Find the new users to notify
		$notifications = $this->find_users_for_notification($post);

		// Find the notifications we must delete
		$remove_notifications = array_diff($old_notifications, array_keys($notifications));

		// Find the notifications we must add
		$add_notifications = array();
		foreach (array_diff(array_keys($notifications), $old_notifications) as $user_id)
		{
			$add_notifications[$user_id] = $notifications[$user_id];
		}

		// Add the necessary notifications
		$this->notification_manager->add_notifications_for_users(get_class($this), $post, $add_notifications);

		// Remove the necessary notifications
		if (!empty($remove_notifications))
		{
			$sql = 'DELETE FROM ' . NOTIFICATIONS_TABLE . "
				WHERE item_type = '" . get_class($this) . "'
					AND item_id = " . self::get_item_id($post) . '
					AND ' . $this->db->sql_in_set('user_id', $remove_notifications);
			$this->db->sql_query($sql);
		}

		// return true to continue with the update code in the notifications service (this will update the rest of the notifications)
		return true;
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return 'notifications/quote';
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		$user_data = $this->notification_manager->get_user($this->get_data('poster_id'));

		return array_merge(parent::get_email_template_variables(), array(
			'AUTHOR_NAME'		=> htmlspecialchars_decode($user_data['username']),
		));
	}
}
