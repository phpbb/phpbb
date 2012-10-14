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
* Topic notifications class
* This class handles notifications for new topics
*
* @package notifications
*/
class phpbb_notification_type_post_in_queue extends phpbb_notification_type_post
{
	/**
	* Email template to use to send notifications
	*
	* @var string
	*/
	public $email_template = 'notifications/post_in_queue';

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_POST_IN_QUEUE';

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
	* Permission to check for (in find_users_for_notification)
	*
	* @var string Permission name
	*/
	protected $permission = 'm_approve';

	/**
	* Get the type of notification this is
	* phpbb_notification_type_
	*/
	public static function get_item_type()
	{
		return 'post_in_queue';
	}

	/**
	* Is available
	*/
	public function is_available()
	{
		$m_approve = $this->auth->acl_getf($this->permission, true);

		return (!empty($m_approve));
	}

	/**
	* Find the users who want to receive notifications
	*
	* @param array $post Data from the post
	*
	* @return array
	*/
	public function find_users_for_notification($post, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'		=> array(),
		), $options);

		// 0 is for global
		$auth_approve = $this->auth->acl_get_list(false, $this->permission, array($post['forum_id'], 0));

		if (empty($auth_approve))
		{
			return array();
		}

		$auth_approve[$post['forum_id']] = array_unique(array_merge($auth_approve[$post['forum_id']], $auth_approve[0]));

		$notify_users = array();

		$sql = 'SELECT *
			FROM ' . USER_NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . self::$notification_option['id'] . "'
				AND " . $this->db->sql_in_set('user_id', $auth_approve[$post['forum_id']][$this->permission]);
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
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'mcp.' . $this->php_ext, "i=queue&amp;mode=approve_details&amp;f={$this->get_data('forum_id')}&amp;p={$this->item_id}");
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

		$this->time = $data['time'] = time();

		return $data;
	}
}
