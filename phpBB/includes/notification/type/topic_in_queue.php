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
class phpbb_notification_type_topic_in_queue extends phpbb_notification_type_topic
{
	/**
	* Email template to use to send notifications
	*
	* @var string
	*/
	public $email_template = 'notifications/topic_in_queue';

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
	* 					Array of data (including keys 'id' and 'lang')
	*/
	public static $notification_option = array(
		'id'	=> 'needs_approval',
		'lang'	=> 'NOTIFICATION_TYPE_IN_MODERATION_QUEUE',
	);

	/**
	* Is available
	*/
	public static function is_available(ContainerBuilder $phpbb_container)
	{
		$m_approve = $phpbb_container->get('auth')->acl_getf('m_approve', true);

		return (!empty($m_approve));
	}

	/**
	* Get the type of notification this is
	* phpbb_notification_type_
	*/
	public static function get_item_type()
	{
		return 'topic_in_queue';
	}

	/**
	* Find the users who want to receive notifications
	*
	* @param ContainerBuilder $phpbb_container
	* @param array $topic Data from the topic
	*
	* @return array
	*/
	public static function find_users_for_notification(ContainerBuilder $phpbb_container, $topic, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'		=> array(),
		), $options);

		$db = $phpbb_container->get('dbal.conn');

		$auth_approve = $phpbb_container->get('auth')->acl_get_list(false, 'm_approve', $topic['forum_id']);

		if (empty($auth_approve))
		{
			return array();
		}

		$notify_users = array();

		$sql = 'SELECT *
			FROM ' . USER_NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . self::$notification_option['id'] . "'
				AND " . $db->sql_in_set('user_id', $auth_approve[$topic['forum_id']]['m_approve']);
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
	* Function for preparing the data for insertion in an SQL query
	* (The service handles insertion)
	*
	* @param array $topic Data from submit_post
	*
	* @return array Array of data ready to be inserted into the database
	*/
	public function create_insert_array($topic)
	{
		$data = parent::create_insert_array($topic);

		$this->time = $data['time'] = time();

		return $data;
	}
}
