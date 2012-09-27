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
* Topic notifications class
* This class handles notifications for new topics
*
* @package notifications
*/
class phpbb_notifications_type_post_in_queue extends phpbb_notifications_type_post
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
	* 					Array of data (including keys 'id' and 'lang')
	*/
	public static $notification_option = array(
		'id'	=> 'needs_approval',
		'lang'	=> 'NOTIFICATION_TYPE_IN_MODERATION_QUEUE',
	);

	/**
	* Get the type of notification this is
	* phpbb_notifications_type_
	*/
	public static function get_item_type()
	{
		return 'post_in_queue';
	}

	/**
	* Is available
	*/
	public static function is_available(ContainerBuilder $phpbb_container)
	{
		$m_approve = $phpbb_container->get('auth')->acl_getf('m_approve', true);

		return (!empty($m_approve));
	}

	/**
	* Find the users who want to receive notifications
	*
	* @param ContainerBuilder $phpbb_container
	* @param array $post Data from the post
	*
	* @return array
	*/
	public static function find_users_for_notification(ContainerBuilder $phpbb_container, $post)
	{
		$db = $phpbb_container->get('dbal.conn');

		$auth_approve = $phpbb_container->get('auth')->acl_get_list(false, 'm_approve', $post['forum_id']);

		if (empty($auth_approve))
		{
			return array();
		}

		$notify_users = array();

		$sql = 'SELECT *
			FROM ' . USER_NOTIFICATIONS_TABLE . "
			WHERE item_type = 'needs_approval'
				AND " . $db->sql_in_set('user_id', $auth_approve[$topic['forum_id']]['m_approve']);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
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
	* Function for preparing the data for insertion in an SQL query
	* (The service handles insertion)
	*
	* @param array $post Data from submit_post
	*
	* @return array Array of data ready to be inserted into the database
	*/
	public function create_insert_array($post)
	{
		$data = parent::create_insert_array($post);

		$this->time = $data['time'] = time();

		return $data;
	}
}
