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
	* @var \phpbb\textformatter\utils_interface
	*/
	protected $utils;

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
	static public $notification_option = array(
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

		$usernames = $this->utils->get_outermost_quote_authors($post['post_text']);

		if (empty($usernames))
		{
			return array();
		}

		$usernames = array_unique($usernames);

		$usernames = array_map('utf8_clean_string', $usernames);

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
	* @return true
	*/
	public function update_notifications($post)
	{
		$old_notifications = $this->notification_manager->get_notified_users($this->get_type(), array(
			'item_id'	=> static::get_item_id($post),
		));

		// Find the new users to notify
		$notifications = $this->find_users_for_notification($post);

		// Find the notifications we must delete
		$remove_notifications = array_diff(array_keys($old_notifications), array_keys($notifications));

		// Find the notifications we must add
		$add_notifications = array();
		foreach (array_diff(array_keys($notifications), array_keys($old_notifications)) as $user_id)
		{
			$add_notifications[$user_id] = $notifications[$user_id];
		}

		// Add the necessary notifications
		$this->notification_manager->add_notifications_for_users($this->get_type(), $post, $add_notifications);

		// Remove the necessary notifications
		if (!empty($remove_notifications))
		{
			$this->notification_manager->delete_notifications($this->get_type(), static::get_item_id($post), false, $remove_notifications);
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
			'AUTHOR_NAME'		=> html_entity_decode($user_data['username'], ENT_COMPAT),
		));
	}

	/**
	* Set the utils service used to retrieve quote authors
	*
	* @param \phpbb\textformatter\utils_interface $utils
	*/
	public function set_utils(\phpbb\textformatter\utils_interface $utils)
	{
		$this->utils = $utils;
	}
}
