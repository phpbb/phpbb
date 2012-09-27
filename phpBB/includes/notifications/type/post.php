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
	* Email template to use to send notifications
	*
	* @var string
	*/
	public $email_template = 'topic_notify';

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_POST';

	/**
	* Get the type of notification this is
	* phpbb_notifications_type_
	*/
	public static function get_item_type()
	{
		return 'post';
	}

	/**
	* Get the id of the item
	*
	* @param array $post The data from the post
	*/
	public static function get_item_id($post)
	{
		return (int) $post['post_id'];
	}

	/**
	* Get the id of the parent
	*
	* @param array $post The data from the post
	*/
	public static function get_item_parent_id($post)
	{
		return (int) $post['topic_id'];
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
		// Let's continue to use the phpBB subscriptions system, at least for now.
		// It may not be the nicest thing, but it is already working and it would be significant work to replace it
		//$users = parent::_find_users_for_notification($phpbb_container, $post['topic_id']);

		$db = $phpbb_container->get('dbal.conn');

		$users = array();

		$sql = 'SELECT user_id
			FROM ' . TOPICS_WATCH_TABLE . '
			WHERE topic_id = ' . (int) $post['topic_id'] . '
				AND notify_status = ' . NOTIFY_YES . '
				AND user_id <> ' . (int) $post['poster_id'];
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$users[] = $row['user_id'];
		}
		$db->sql_freeresult($result);

		if (empty($users))
		{
			return array();
		}

		$auth_read = $phpbb_container->get('auth')->acl_get_list($users, 'f_read', $post['forum_id']);

		if (empty($auth_read))
		{
			return array();
		}

		$notify_users = array();

		$sql = 'SELECT *
			FROM ' . USER_NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . self::get_item_type() . "'
				AND " . $db->sql_in_set('user_id', $auth_read[$post['forum_id']]['f_read']);
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
	public function get_title()
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
			$this->language_key,
			$username,
			censor_text($this->get_data('topic_title'))
		);
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		return array(
			'POST_SUBJECT'				=> htmlspecialchars_decode(censor_text($this->get_data('post_subject'))),
			'TOPIC_TITLE'				=> htmlspecialchars_decode(censor_text($this->get_data('topic_title'))),

			'U_VIEW_POST'				=> generate_board_url() . "/viewtopic.{$this->php_ext}?p={$this->item_id}#p{$this->item_id}",
			'U_NEWEST_POST'				=> generate_board_url() . "/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_parent_id}&view=unread#unread",
			'U_TOPIC'					=> generate_board_url() . "/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_parent_id}",
			'U_VIEW_TOPIC'				=> generate_board_url() . "/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_parent_id}",
			'U_FORUM'					=> generate_board_url() . "/viewforum.{$this->php_ext}?f={$this->get_data('forum_id')}",
			'U_STOP_WATCHING_TOPIC'		=> generate_board_url() . "/viewtopic.{$this->php_ext}?uid={$this->user_id}&f={$this->get_data('forum_id')}&t={$this->item_parent_id}&unwatch=topic",
		);
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
		$this->set_data('poster_id', $post['poster_id']);

		$this->set_data('topic_title', $post['topic_title']);

		$this->set_data('post_subject', $post['post_subject']);

		$this->set_data('post_username', (($post['poster_id'] == ANONYMOUS) ? $post['post_username'] : ''));

		$this->set_data('forum_id', $post['forum_id']);

		$this->set_data('forum_name', $post['forum_name']);

		$this->time = $post['post_time'];

		return parent::create_insert_array($post);
	}
}
