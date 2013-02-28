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
class phpbb_notification_type_topic extends phpbb_notification_type_base
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'topic';
	}

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_TOPIC';

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	public static $notification_option = array(
		'lang'	=> 'NOTIFICATION_TYPE_TOPIC',
		'group'	=> 'NOTIFICATION_GROUP_POSTING',
	);

	/**
	* Is available
	*/
	public function is_available()
	{
		return $this->config['allow_forum_notify'];
	}

	/**
	* Get the id of the item
	*
	* @param array $post The data from the post
	*/
	public static function get_item_id($post)
	{
		return (int) $post['topic_id'];
	}

	/**
	* Get the id of the parent
	*
	* @param array $post The data from the post
	*/
	public static function get_item_parent_id($post)
	{
		return (int) $post['forum_id'];
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
			'ignore_users'			=> array(),
		), $options);

		$users = array();

		$sql = 'SELECT user_id
			FROM ' . FORUMS_WATCH_TABLE . '
			WHERE forum_id = ' . (int) $topic['forum_id'] . '
				AND notify_status = ' . NOTIFY_YES . '
				AND user_id <> ' . (int) $topic['poster_id'];
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$users[] = $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($users))
		{
			return array();
		}

		$auth_read = $this->auth->acl_get_list($users, 'f_read', $topic['forum_id']);

		if (empty($auth_read))
		{
			return array();
		}

		return $this->check_user_notification_options($auth_read[$topic['forum_id']]['f_read'], $options);
	}

	/**
	* Get the user's avatar
	*/
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('poster_id'));
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
			$username = $this->user_loader->get_username($this->get_data('poster_id'), 'no_profile');
		}

		return $this->user->lang(
			$this->language_key,
			$username,
			censor_text($this->get_data('topic_title')),
			$this->get_data('forum_name')
		);
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return 'newtopic_notify';
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		$board_url = generate_board_url();

		if ($this->get_data('post_username'))
		{
			$username = $this->get_data('post_username');
		}
		else
		{
			$username = $this->user_loader->get_username($this->get_data('poster_id'), 'no_profile');
		}

		return array(
			'AUTHOR_NAME'				=> htmlspecialchars_decode($username),
			'FORUM_NAME'				=> htmlspecialchars_decode($this->get_data('forum_name')),
			'TOPIC_TITLE'				=> htmlspecialchars_decode(censor_text($this->get_data('topic_title'))),

			'U_TOPIC'					=> "{$board_url}/viewtopic.{$this->php_ext}?f={$this->item_parent_id}&t={$this->item_id}",
			'U_VIEW_TOPIC'				=> "{$board_url}/viewtopic.{$this->php_ext}?f={$this->item_parent_id}&t={$this->item_id}",
			'U_FORUM'					=> "{$board_url}/viewforum.{$this->php_ext}?f={$this->item_parent_id}",
			'U_STOP_WATCHING_FORUM'		=> "{$board_url}/viewforum.{$this->php_ext}?uid={$this->user_id}&f={$this->item_parent_id}&unwatch=forum",
		);
	}

	/**
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'viewtopic.' . $this->php_ext, "f={$this->item_parent_id}&amp;t={$this->item_id}");
	}

	/**
	* Users needed to query before this notification can be displayed
	*
	* @return array Array of user_ids
	*/
	public function users_to_query()
	{
		return array($this->get_data('poster_id'));
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
		if (!sizeof($notify_users))
		{
			return array();
		}

		$tracking_data = array();
		$sql = 'SELECT user_id, mark_time FROM ' . TOPICS_TRACK_TABLE . '
			WHERE topic_id = ' . (int) $post['topic_id'] . '
				AND ' . $this->db->sql_in_set('user_id', array_keys($notify_users));
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$tracking_data[$row['user_id']] = $row['mark_time'];
		}

		return $tracking_data;
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
		$this->set_data('poster_id', $post['poster_id']);

		$this->set_data('topic_title', $post['topic_title']);

		$this->set_data('post_username', (($post['poster_id'] == ANONYMOUS) ? $post['post_username'] : ''));

		$this->set_data('forum_name', $post['forum_name']);

		$this->notification_time = $post['post_time'];

		// Topics can be "read" before they are public (while awaiting approval).
		// Make sure that if the user has read the topic, it's marked as read in the notification
		if (isset($pre_create_data[$this->user_id]) && $pre_create_data[$this->user_id] >= $this->notification_time)
		{
			$this->notification_read = true;
		}

		return parent::create_insert_array($post, $pre_create_data);
	}
}
