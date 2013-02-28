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
* Topic in queue notifications class
* This class handles notifications for topics when they are put in the moderation queue (for moderators)
*
* @package notifications
*/
class phpbb_notification_type_topic_in_queue extends phpbb_notification_type_topic
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'topic_in_queue';
	}

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_TOPIC_IN_QUEUE';

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	public static $notification_option = array(
		'id'	=> 'needs_approval',
		'lang'	=> 'NOTIFICATION_TYPE_IN_MODERATION_QUEUE',
		'group'	=> 'NOTIFICATION_GROUP_MODERATION',
	);

	/**
	* Is available
	*/
	public function is_available()
	{
		$m_approve = $this->auth->acl_getf('m_approve', true);

		return (!empty($m_approve));
	}

	/**
	* Find the users who want to receive notifications
	*
	* @param array $topic Data from the topic
	*
	* @return array
	*/
	public function find_users_for_notification($topic, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'		=> array(),
		), $options);

		// 0 is for global
		$auth_approve = $this->auth->acl_get_list(false, 'm_approve', array($topic['forum_id'], 0));

		if (empty($auth_approve))
		{
			return array();
		}

		$auth_approve[$topic['forum_id']] = array_unique(array_merge($auth_approve[$topic['forum_id']], $auth_approve[0]));

		return $this->check_user_notification_options($auth_approve[$topic['forum_id']]['m_approve'], array_merge($options, array(
			'item_type'		=> self::$notification_option['id'],
		)));
	}

	/**
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'mcp.' . $this->php_ext, "i=queue&amp;mode=approve_details&amp;f={$this->item_parent_id}&amp;t={$this->item_id}");
	}

	/**
	* Function for preparing the data for insertion in an SQL query
	* (The service handles insertion)
	*
	* @param array $topic Data from submit_post
	* @param array $pre_create_data Data from pre_create_insert_array()
	*
	* @return array Array of data ready to be inserted into the database
	*/
	public function create_insert_array($topic, $pre_create_data = array())
	{
		$data = parent::create_insert_array($topic, $pre_create_data);

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
		return 'topic_in_queue';
	}
}
