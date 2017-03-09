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
* Topic approved notifications class
* This class handles notifications for topics when they are approved (for authors)
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
		return 'notification.type.approve_topic';
	}

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_TOPIC_APPROVED';

	/**
	* Inherit notification read status from topic.
	*
	* @var bool
	*/
	protected $inherit_read_status = false;

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	static public $notification_option = array(
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
		$users[$post['poster_id']] = $this->notification_manager->get_default_methods();

		return $this->get_authorised_recipients(array_keys($users), $post['forum_id'], array_merge($options, array(
			'item_type'		=> static::$notification_option['id'],
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
	* {@inheritdoc}
	*/
	public function create_insert_array($post, $pre_create_data = array())
	{

		parent::create_insert_array($post, $pre_create_data);

		$this->notification_time = time();
	}

	/**
	* {@inheritdoc}
	*/
	public function get_insert_array()
	{
		$data = parent::get_insert_array();
		$data['notification_time'] = $this->notification_time;

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
