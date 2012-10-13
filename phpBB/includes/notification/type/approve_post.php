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
* Post notifications class
* This class handles notifications for replies to a topic
*
* @package notifications
*/
class phpbb_notification_type_approve_post extends phpbb_notification_type_post
{
	/**
	* Email template to use to send notifications
	*
	* @var string
	*/
	public $email_template = 'post_approved';

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_POST_APPROVED';

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id' and 'lang')
	*/
	public static $notification_option = array(
		'id'	=> 'moderation_queue',
		'lang'	=> 'NOTIFICATION_TYPE_MODERATION_QUEUE',
	);

	/**
	* Get the type of notification this is
	* phpbb_notification_type_
	*/
	public static function get_item_type()
	{
		return 'approve_post';
	}

	/**
	* Is available
	*/
	public function is_available()
	{
		return !$this->auth->acl_get('m_approve');
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
		$users[$post['poster_id']] = array('');

		$auth_read = $this->auth->acl_get_list(array_keys($users), 'f_read', $post['forum_id']);

		if (empty($auth_read))
		{
			return array();
		}

		$notify_users = array();

		$sql = 'SELECT *
			FROM ' . USER_NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . self::$notification_option['id'] . "'
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

	/**
	* Pre create insert array function
	* This allows you to perform certain actions, like run a query
	* and load data, before create_insert_array() is run. The data
	* returned from this function will be sent to create_insert_array().
	*
	* @param array $post Post data from submit_post
	* @param array $notify_users Notify users list
	* 		Formated from find_users_for_notification()
	* @return array Whatever you want to send to create_insert_array().
	*/
	public function pre_create_insert_array($post, $notify_users)
	{
		/*if (!sizeof($notify_users))
		{
			return array();
		}

		// Mark the topic unread before the post
		$sql = 'UPDATE ' . TOPICS_TRACK_TABLE . '
			SET mark_time = ' . (int) ($post['post_time'] - 1) . '
			WHERE topic_id = ' . (int) $post['topic_id'] . '
				AND ' . $this->db->sql_in_set('user_id', array_keys($notify_users));
		$this->db->sql_query($sql);*/

		// In the parent class, this is used to check if the post is already
		// read by a user and marks the notification read if it was marked read.
		// Returning an empty array in effect, forces it to be marked as unread
		// (and also saves a query)
		return array();
	}

	/**
	* Function for preparing the data for insertion in an SQL query
	* (The service handles insertion)
	*
	* @param array $post Data from submit_post
	* @param array $pre_create_data Data from pre_create_insert_array()
	*
	* @return array Array of data ready to be inserted into the database
	*/
	public function create_insert_array($post, $pre_create_data = array())
	{
		$this->set_data('post_subject', $post['post_subject']);

		$data = parent::create_insert_array($post, $pre_create_data);

		$this->time = $data['time'] = time();

		return $data;
	}
}
