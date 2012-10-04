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
* Post quoting notifications class
* This class handles notifications for quoting users in a post
*
* @package notifications
*/
class phpbb_notification_type_quote extends phpbb_notification_type_post
{
	/**
	* Email template to use to send notifications
	*
	* @var string
	*/
	public $email_template = 'notifications/quote';

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
	* Get the type of notification this is
	* phpbb_notification_type_
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
	public static function find_users_for_notification(ContainerBuilder $phpbb_container, $post, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'		=> array(),
		), $options);

		$db = $phpbb_container->get('dbal.conn');

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
			WHERE ' . $db->sql_in_set('username_clean', $usernames) . '
				AND user_id <> ' . (int) $post['poster_id'];
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$users[] = $row['user_id'];
		}
		$db->sql_freeresult($result);

		if (empty($users))
		{
			return array();
		}

		$auth_read = $phpbb_container->get('auth')->acl_get_list($users, 'f_read', $post['forum_id']);

		if (empty($auth_read))
		{
			return array();
		}

		$notify_users = array();

		$sql = 'SELECT *
			FROM ' . USER_NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . self::get_item_type() . "'
				AND " . $db->sql_in_set('user_id', $auth_read[$post['forum_id']]['f_read']);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if (isset($options['ignore_users'][$row['user_id']]) && in_array($row['method'], $options['ignore_users'][$row['user_id']]))
			{
				continue;
			}

			if (!isset($rowset[$row['user_id']]))
			{
				$notify_users[$row['user_id']] = array();
			}

			$notify_users[$row['user_id']][] = $row['method'];
		}
		$db->sql_freeresult($result);

		return $notify_users;
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

		// todo Adding notifications while editing a post can be funky.
		// If the user has read the topic/post already, and the user is newly quoted it an edit,
		// The notification will be stuck as unread until another post is made and the user visits
		// the topic again because the posts will not be marked as read since the topic is already
		// marked as read

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

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		$user_data = $this->service->get_user($this->get_data('poster_id'));

		return array_merge(parent::get_email_template_variables(), array(
			'AUTHOR_NAME'		=> htmlspecialchars_decode($user_data['username']),
		));
	}
}
