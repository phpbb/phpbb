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
* Post notifications class
* This class handles notifications for replies to a topic
*
* @package notifications
*/
class phpbb_notifications_type_post extends phpbb_notifications_type_base
{
	/**
	* Get the type of notification this is
	* phpbb_notifications_type_
	*/
	public static function get_item_type()
	{
		return 'post';
	}

	/**
	* Get the id of the
	*
	* @param array $post The data from the post
	*/
	public static function get_item_id($post)
	{
		return $post['post_id'];
	}

	/**
	* Find the users who want to receive notifications
	*
	* @param ContainerBuilder $phpbb_container
	* @param array $post Data from
	*
	* @return array
	*/
	public static function find_users_for_notification(ContainerBuilder $phpbb_container, $post)
	{
		$users = parent::_find_users_for_notification($phpbb_container, $post['topic_id']);

		if (!sizeof($users))
		{
			return array();
		}

		$auth_read = $phpbb_container->get('auth')->acl_get_list(array_keys($users), 'f_read', $post['forum_id']);

		if (empty($auth_read))
		{
			return array();
		}

		$notify_users = array();

		foreach ($auth_read[$post['forum_id']]['f_read'] as $user_id)
		{
			$notify_users[$user_id] = $users[$user_id];
		}

		return $notify_users;
	}

	/**
	* Get the HTML formatted title of this notification
	*
	* @return string
	*/
	public function get_formatted_title()
	{
		if ($this->get_data('post_username'))
		{
			$username = $this->get_data('post_username');
		}
		else
		{
			$user_data = $this->service->get_user($this->get_data('poster_id'));

			$username = get_username_string('no_profile', $user_data['user_id'], $user_data['username'], $user_data['user_colour']);
		}

		return $username . ' posted in the topic ' . censor_text($this->get_data('topic_title'));
	}

	/**
	* Get the title of this notification
	*
	* @return string
	*/
	public function get_title()
	{
		if ($this->get_data('post_username'))
		{
			$username = $this->get_data('post_username');
		}
		else
		{
			$username = $user_data['username'];
		}

		return $username . ' posted in the topic ' . censor_text($this->get_data('topic_title'));
	}

	/**
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'viewtopic.' . $this->php_ext, "p={$this->item_id}#p{$this->item_id}");
	}

	/**
	* Get the full url to this item
	*
	* @return string URL
	*/
	public function get_full_url()
	{
		return generate_board_url() . "/viewtopic.{$this->php_ext}?p={$this->item_id}#p{$this->item_id}";
	}

	/**
	* Users needed to query before this notification can be displayed
	*
	* @return array Array of user_ids
	*/
	public function users_to_query()
	{
		return array($this->data['poster_id']);
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
		$this->item_id = $post['post_id'];

		$this->set_data('poster_id', $post['poster_id']);

		$this->set_data('topic_title', $post['topic_title']);

		$this->set_data('post_username', $post['post_username']);

		return parent::create_insert_array($post);
	}
}
