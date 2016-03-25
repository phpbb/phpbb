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
* Bookmark updating notifications class
* This class handles notifications for replies to a bookmarked topic
*/

class bookmark extends \phpbb\notification\type\post
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'notification.type.bookmark';
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
	static public $notification_option = array(
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

		$users = array();

		$sql = 'SELECT user_id
			FROM ' . BOOKMARKS_TABLE . '
			WHERE ' . $this->db->sql_in_set('topic_id', $post['topic_id']) . '
				AND user_id <> ' . (int) $post['poster_id'];
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$users[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		$notify_users = $this->get_authorised_recipients($users, $post['forum_id'], $options, true);

		if (empty($notify_users))
		{
			return array();
		}

		// Try to find the users who already have been notified about replies and have not read the topic since and just update their notifications
		$notified_users = $this->notification_manager->get_notified_users($this->get_type(), array(
			'item_parent_id'	=> static::get_item_parent_id($post),
			'read'				=> 0,
		));

		foreach ($notified_users as $user => $notification_data)
		{
			unset($notify_users[$user]);

			/** @var bookmark $notification */
			$notification = $this->notification_manager->get_item_type_class($this->get_type(), $notification_data);
			$update_responders = $notification->add_responders($post);
			if (!empty($update_responders))
			{
				$this->notification_manager->update_notification($notification, $update_responders, array(
					'item_parent_id'	=> self::get_item_parent_id($post),
					'read'				=> 0,
					'user_id'			=> $user,
				));
			}
		}

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
