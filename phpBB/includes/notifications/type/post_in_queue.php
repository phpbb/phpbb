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
	* Get the type of notification this is
	* phpbb_notifications_type_
	*/
	public static function get_item_type()
	{
		return 'post_in_queue';
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
		/* todo
		* find what type of notification they'd like to receive
		*/
		$auth_approve = $phpbb_container->get('auth')->acl_get_list(false, 'm_approve', $post['forum_id']);

		if (empty($auth_approve))
		{
			return array();
		}

		$notify_users = array();

		foreach ($auth_approve[$post['forum_id']]['m_approve'] as $user_id)
		{
			$notify_users[$user_id] = array('');
		}

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
