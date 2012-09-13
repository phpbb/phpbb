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
class phpbb_notifications_type_topic extends phpbb_notifications_type_base
{
	/**
	* Get the type of notification this is
	* phpbb_notifications_type_
	*/
	public static function get_item_type()
	{
		return 'topic';
	}

	/**
	* Get the id of the
	*
	* @param array $post The data from the post
	*/
	public static function get_item_id($post)
	{
		return $post['topic_id'];
	}

	/**
	* Find the users who want to receive notifications
	*
	* @param ContainerBuilder $phpbb_container
	* @param array $post Data from
	*
	* @return array
	*/
	public static function find_users_for_notification(ContainerBuilder $phpbb_container, $topic)
	{
		// Let's continue to use the phpBB subscriptions system, at least for now.
		// It may not be the nicest thing, but it is already working and it would be significant work to replace it
		//$users = parent::_find_users_for_notification($phpbb_container, $topic['forum_id']);

		$db = $phpbb_container->get('dbal.conn');

		$users = array();

		/* todo
		* find what type of notification they'd like to receive
		* make sure not to send duplicate notifications
		*/
		$sql = 'SELECT user_id
			FROM ' . FORUMS_WATCH_TABLE . '
			WHERE forum_id = ' . (int) $topic['forum_id'] . '
				AND notify_status = ' . NOTIFY_YES;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$users[$row['user_id']] = array('');
		}
		$db->sql_freeresult($result);

		if (empty($users))
		{
			return array();
		}

		$auth_read = $phpbb_container->get('auth')->acl_get_list(array_keys($users), 'f_read', $topic['forum_id']);

		if (empty($auth_read))
		{
			return array();
		}

		$notify_users = array();

		foreach ($auth_read[$topic['forum_id']]['f_read'] as $user_id)
		{
			$notify_users[$user_id] = $users[$user_id];
		}

		return $notify_users;
	}

	/**
	* Get the user's avatar
	*/
	public function get_avatar()
	{
		return $this->_get_avatar($this->get_data('poster_id'));
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

		return $this->phpbb_container->get('user')->lang(
			'NOTIFICATION_TOPIC',
			$username,
			censor_text($this->get_data('topic_title')),
			$this->get_data('forum_name')
		);
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
			$user_data = $this->service->get_user($this->get_data('poster_id'));

			$username = $user_data['username'];
		}

		return $this->phpbb_container->get('user')->lang(
			'NOTIFICATION_TOPIC',
			$username,
			censor_text($this->get_data('topic_title')),
			$this->get_data('forum_name')
		);
	}

	/**
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'viewtopic.' . $this->php_ext, "f={$this->get_data('forum_id')}&amp;t={$this->item_id}");
	}

	/**
	* Get the full url to this item
	*
	* @return string URL
	*/
	public function get_full_url()
	{
		return generate_board_url() . "/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_id}";
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
		$this->item_id = $post['topic_id'];

		$this->set_data('poster_id', $post['poster_id']);

		$this->set_data('topic_title', $post['topic_title']);

		$this->set_data('post_username', (($post['post_username'] != $this->phpbb_container->get('user')->data['username']) ? $post['post_username'] : ''));

		$this->set_data('forum_name', $post['forum_name']);

		$this->set_data('forum_id', $post['forum_id']);

		return parent::create_insert_array($post);
	}
}
