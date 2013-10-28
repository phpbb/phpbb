<?php
/**
*
* @package notifications
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\notification\type;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Topic approved notifications class
* This class handles notifications for topics when they are approved (for authors)
*
* @package notifications
*/
class approve_topic extends \phpbb\notification\type\topic
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'approve_topic';
	}

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_TOPIC_APPROVED';

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	public static $notification_option = array(
		'id'	=> 'moderation_queue',
		'lang'	=> 'NOTIFICATION_TYPE_MODERATION_QUEUE',
		'group'	=> 'NOTIFICATION_GROUP_POSTING',
	);

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

		return $this->check_user_notification_options($auth_read[$post['forum_id']]['f_read'], array_merge($options, array(
			'item_type'		=> self::$notification_option['id'],
		)));
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
		$data = parent::create_insert_array($post, $pre_create_data);

		$this->notification_time = $data['notification_time'] = time();

		return $data;
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return 'topic_approved';
	}
}
