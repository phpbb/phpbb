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

namespace phpbb\notification\type;

/**
* Post quoting notifications class
* This class handles notifying users when they have been quoted in a post
*/

class quote extends \phpbb\notification\type\post
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'notification.type.quote';
	}

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
	* @param array $post Data from submit_post
	* @param array $options Options for finding users for notification
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
			$users[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		return $this->get_authorised_recipients($users, $post['forum_id'], $options, true);
	}

	/**
	* Update a notification
	*
	* @param array $post Data specific for this type that will be updated
	*/
	public function update_notifications($post)
	{
		$old_notifications = array();
		$sql = 'SELECT n.user_id
			FROM ' . $this->notifications_table . ' n, ' . $this->notification_types_table . ' nt
			WHERE n.notification_type_id = ' . (int) $this->notification_type_id . '
				AND n.item_id = ' . self::get_item_id($post) . '
				AND nt.notification_type_id = n.notification_type_id
				AND nt.notification_type_enabled = 1';
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
		$this->notification_manager->add_notifications_for_users($this->get_type(), $post, $add_notifications);

		// Remove the necessary notifications
		if (!empty($remove_notifications))
		{
			$sql = 'DELETE FROM ' . $this->notifications_table . '
				WHERE notification_type_id = ' . (int) $this->notification_type_id . '
					AND item_id = ' . self::get_item_id($post) . '
					AND ' . $this->db->sql_in_set('user_id', $remove_notifications);
			$this->db->sql_query($sql);
		}

		// return true to continue with the update code in the notifications service (this will update the rest of the notifications)
		return true;
	}

	/**
	* {inheritDoc}
	*/
	public function get_redirect_url()
	{
		return $this->get_url();
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return 'quote';
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		$user_data = $this->user_loader->get_user($this->get_data('poster_id'));

		return array_merge(parent::get_email_template_variables(), array(
			'AUTHOR_NAME'		=> htmlspecialchars_decode($user_data['username']),
		));
	}
}
