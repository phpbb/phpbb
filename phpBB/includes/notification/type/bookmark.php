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
	* Email template to use to send notifications
	*
	* @var string
	*/
	public $email_template = 'notifications/bookmark';

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_BOOKMARK';

	/**
	* Get the type of notification this is
	* phpbb_notification_type_
	*/
	public static function get_item_type()
	{
		return 'bookmark';
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

		$auth_read = $this->auth->acl_get_list($users, 'f_read', $post['forum_id']);

		if (empty($auth_read))
		{
			return array();
		}

		$notify_users = array();

		$sql = 'SELECT *
			FROM ' . USER_NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . self::get_item_type() . "'
				AND " . $this->db->sql_in_set('user_id', $auth_read[$post['forum_id']]['f_read']);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
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
		$this->db->sql_freeresult($result);

		return $notify_users;
	}
}
