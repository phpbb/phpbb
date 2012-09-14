<?php
/**
*
* @package notifications
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Post tagging notifications class
* This class handles notifications for tagging users in a post (ex: @EXreaction)
*
* @package notifications
*/
class phpbb_notifications_type_quote extends phpbb_notifications_type_post
{
	protected static $regular_expression_match = '#\[quote=&quot;(.+?)&quot;:#';

	/**
	* Get the type of notification this is
	* phpbb_notifications_type_
	*/
	public static function get_item_type()
	{
		return 'quote';
	}

	/**
	* Find the users who want to receive notifications
	*
	* @param ContainerBuilder $phpbb_container
	* @param array $post Data from
	*
	* @return array
	*/
	public static function find_users_for_notification(ContainerBuilder $phpbb_container, $post)
	{
		$db = $phpbb_container->get('dbal.conn');

		$usernames = false;
		preg_match_all(self::$regular_expression_match, $post['message'], $usernames);

		if (empty($usernames[1]))
		{
			return array();
		}

		$usernames[1] = array_unique($usernames[1]);

		$usernames = array_map('utf8_clean_string', $usernames[1]);

		$users = array();

		/* todo
		* find what type of notification they'd like to receive
		*/
		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . '
			WHERE ' . $db->sql_in_set('username_clean', $usernames);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$users[$row['user_id']] = array('');
		}
		$db->sql_freeresult($result);

		if (empty($users))
		{
			return array();
		}

		$auth_read = $phpbb_container->get('auth')->acl_get_list(array_keys($users), 'f_read', $post['forum_id']);

		if (empty($auth_read))
		{
			return array();
		}

		$notify_users = array();

		foreach ($auth_read[$post['forum_id']]['f_read'] as $user_id)
		{
			$notify_users[$user_id] = $users[$user_id];
		}

		return $notify_users;
	}

	/**
	* Get the HTML formatted title of this notification
	*
	* @return string
	*/
	public function get_formatted_title()
	{
		if ($this->get_data('post_username'))
		{
			$username = $this->get_data('post_username');
		}
		else
		{
			$user_data = $this->service->get_user($this->get_data('poster_id'));

			$username = get_username_string('no_profile', $user_data['user_id'], $user_data['username'], $user_data['user_colour']);
		}

		return $this->phpbb_container->get('user')->lang(
			'NOTIFICATION_QUOTE',
			$username,
			censor_text($this->get_data('topic_title'))
		);
	}

	/**
	* Get the title of this notification
	*
	* @return string
	*/
	public function get_title()
	{
		if ($this->get_data('post_username'))
		{
			$username = $this->get_data('post_username');
		}
		else
		{
			$user_data = $this->service->get_user($this->get_data('poster_id'));

			$username = $user_data['username'];
		}

		return $this->phpbb_container->get('user')->lang(
			'NOTIFICATION_QUOTE',
			$username,
			censor_text($this->get_data('topic_title'))
		);
	}

	/**
	* Update a notification
	*
	* @param ContainerBuilder $phpbb_container
	* @param array $data Data specific for this type that will be updated
	*/
	public static function update_notifications(ContainerBuilder $phpbb_container, $post)
	{
		$service = $phpbb_container->get('notifications');
		$db = $phpbb_container->get('dbal.conn');

		$old_notifications = array();
		$sql = 'SELECT user_id
			FROM ' . NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . self::get_item_type() . "'
				AND item_id = " . self::get_item_id($post);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$old_notifications[] = $row['user_id'];
		}
		$db->sql_freeresult($result);

		// Find the new users to notify
		$notifications = self::find_users_for_notification($phpbb_container, $post);

		// Find the notifications we must delete
		$remove_notifications = array_diff($old_notifications, array_keys($notifications));

		// Find the notifications we must add
		$add_notifications = array();
		foreach (array_diff(array_keys($notifications), $old_notifications) as $user_id)
		{
			$add_notifications[$user_id] = $notifications[$user_id];
		}

		var_dump($old_notifications, $notifications, $remove_notifications, $add_notifications);

		// Add the necessary notifications
		$service->add_notifications_for_users(self::get_item_type(), $post, $add_notifications);

		// Remove the necessary notifications
		if (!empty($remove_notifications))
		{
			$sql = 'DELETE FROM ' . NOTIFICATIONS_TABLE . "
				WHERE item_type = '" . self::get_item_type() . "'
					AND item_id = " . self::get_item_id($post) . '
					AND ' . $db->sql_in_set('user_id', $remove_notifications);
			$db->sql_query($sql);
		}

		// return true to continue with the update code in the notifications service (this will update the rest of the notifications)
		return true;
	}
}
