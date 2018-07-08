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
* Post notifications class
* This class handles notifications for replies to a topic
*/

class post extends \phpbb\notification\type\base
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'notification.type.post';
	}

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_POST';

	/**
	* Inherit notification read status from post.
	*
	* @var bool
	*/
	protected $inherit_read_status = true;

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	static public $notification_option = array(
		'lang'	=> 'NOTIFICATION_TYPE_POST',
		'group'	=> 'NOTIFICATION_GROUP_POSTING',
	);

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var \phpbb\config\config */
	protected $config;

	public function set_config(\phpbb\config\config $config)
	{
		$this->config = $config;
	}

	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	/**
	* Is available
	*/
	public function is_available()
	{
		return $this->config['allow_topic_notify'];
	}

	/**
	* Get the id of the item
	*
	* @param array $post The data from the post
	* @return int The post id
	*/
	static public function get_item_id($post)
	{
		return (int) $post['post_id'];
	}

	/**
	* Get the id of the parent
	*
	* @param array $post The data from the post
	* @return int The topic id
	*/
	static public function get_item_parent_id($post)
	{
		return (int) $post['topic_id'];
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

		$sql = 'SELECT user_id
			FROM ' . TOPICS_WATCH_TABLE . '
			WHERE topic_id = ' . (int) $post['topic_id'] . '
				AND notify_status = ' . NOTIFY_YES . '
				AND user_id <> ' . (int) $post['poster_id'];
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$users[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT user_id
			FROM ' . FORUMS_WATCH_TABLE . '
			WHERE forum_id = ' . (int) $post['forum_id'] . '
				AND notify_status = ' . NOTIFY_YES . '
				AND user_id <> ' . (int) $post['poster_id'];
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$users[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		$notify_users = $this->get_authorised_recipients($users, $post['forum_id'], $options, true);

		if (empty($notify_users))
		{
			return array();
		}

		// Try to find the users who already have been notified about replies and have not read the topic since and just update their notifications
		$notified_users = $this->notification_manager->get_notified_users($this->get_type(), array(
			'item_parent_id'	=> static::get_item_parent_id($post),
			'read'				=> 0,
		));

		foreach ($notified_users as $user => $notification_data)
		{
			unset($notify_users[$user]);

			/** @var post $notification */
			$notification = $this->notification_manager->get_item_type_class($this->get_type(), $notification_data);
			$update_responders = $notification->add_responders($post);
			if (!empty($update_responders))
			{
				$this->notification_manager->update_notification($notification, $update_responders, array(
					'item_parent_id'	=> self::get_item_parent_id($post),
					'read'				=> 0,
					'user_id'			=> $user,
				));
			}
		}

		return $notify_users;
	}

	/**
	* Get the user's avatar
	*/
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('poster_id'), false, true);
	}

	/**
	* Get the HTML formatted title of this notification
	*
	* @return string
	*/
	public function get_title()
	{
		$responders = $this->get_data('responders');
		$usernames = array();

		if (!is_array($responders))
		{
			$responders = array();
		}

		$responders = array_merge(array(array(
			'poster_id'		=> $this->get_data('poster_id'),
			'username'		=> $this->get_data('post_username'),
		)), $responders);

		$responders_cnt = count($responders);
		$responders = $this->trim_user_ary($responders);
		$trimmed_responders_cnt = $responders_cnt - count($responders);

		foreach ($responders as $responder)
		{
			if ($responder['username'])
			{
				$usernames[] = $responder['username'];
			}
			else
			{
				$usernames[] = $this->user_loader->get_username($responder['poster_id'], 'no_profile');
			}
		}

		if ($trimmed_responders_cnt > 20)
		{
			$usernames[] = $this->language->lang('NOTIFICATION_MANY_OTHERS');
		}
		else if ($trimmed_responders_cnt)
		{
			$usernames[] = $this->language->lang('NOTIFICATION_X_OTHERS', $trimmed_responders_cnt);
		}

		return $this->language->lang(
			$this->language_key,
			phpbb_generate_string_list($usernames, $this->user),
			$responders_cnt
		);
	}

	/**
	* Get the HTML formatted reference of the notification
	*
	* @return string
	*/
	public function get_reference()
	{
		return $this->language->lang(
			'NOTIFICATION_REFERENCE',
			censor_text($this->get_data('topic_title'))
		);
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return 'topic_notify';
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		if ($this->get_data('post_username'))
		{
			$username = $this->get_data('post_username');
		}
		else
		{
			$username = $this->user_loader->get_username($this->get_data('poster_id'), 'username');
		}

		return array(
			'AUTHOR_NAME'				=> htmlspecialchars_decode($username),
			'POST_SUBJECT'				=> htmlspecialchars_decode(censor_text($this->get_data('post_subject'))),
			'TOPIC_TITLE'				=> htmlspecialchars_decode(censor_text($this->get_data('topic_title'))),

			'U_VIEW_POST'				=> generate_board_url() . "/viewtopic.{$this->php_ext}?p={$this->item_id}#p{$this->item_id}",
			'U_NEWEST_POST'				=> generate_board_url() . "/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_parent_id}&e=1&view=unread#unread",
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
	* {inheritDoc}
	*/
	public function get_redirect_url()
	{
		return append_sid($this->phpbb_root_path . 'viewtopic.' . $this->php_ext, "t={$this->item_parent_id}&amp;view=unread#unread");
	}

	/**
	* Users needed to query before this notification can be displayed
	*
	* @return array Array of user_ids
	*/
	public function users_to_query()
	{
		$responders = $this->get_data('responders');
		$users = array(
			$this->get_data('poster_id'),
		);

		if (is_array($responders))
		{
			foreach ($responders as $responder)
			{
				$users[] = $responder['poster_id'];
			}
		}

		return $this->trim_user_ary($users);
	}

	/**
	* Trim the user array passed down to 3 users if the array contains
	* more than 4 users.
	*
	* @param array $users Array of users
	* @return array Trimmed array of user_ids
	*/
	public function trim_user_ary($users)
	{
		if (count($users) > 4)
		{
			array_splice($users, 3);
		}
		return $users;
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
		if (!count($notify_users) || !$this->inherit_read_status)
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
		$this->db->sql_freeresult($result);

		return $tracking_data;
	}

	/**
	* {@inheritdoc}
	*/
	public function create_insert_array($post, $pre_create_data = array())
	{
		$this->set_data('poster_id', $post['poster_id']);

		$this->set_data('topic_title', $post['topic_title']);

		$this->set_data('post_subject', $post['post_subject']);

		$this->set_data('post_username', (($post['poster_id'] == ANONYMOUS) ? $post['post_username'] : ''));

		$this->set_data('forum_id', $post['forum_id']);

		$this->set_data('forum_name', $post['forum_name']);

		$this->notification_time = $post['post_time'];

		// Topics can be "read" before they are public (while awaiting approval).
		// Make sure that if the user has read the topic, it's marked as read in the notification
		if ($this->inherit_read_status && isset($pre_create_data[$this->user_id]) && $pre_create_data[$this->user_id] >= $this->notification_time)
		{
			$this->notification_read = true;
		}

		parent::create_insert_array($post, $pre_create_data);
	}

	/**
	* Add responders to the notification
	*
	* @param mixed $post
	* @return array Array of responder data
	*/
	public function add_responders($post)
	{
		// Do not add them as a responder if they were the original poster that created the notification
		if ($this->get_data('poster_id') == $post['poster_id'])
		{
			return array();
		}

		$responders = $this->get_data('responders');

		$responders = ($responders === null) ? array() : $responders;

		// Do not add more than 25 responders,
		// we trim the username list to "a, b, c and x others" anyway
		// so there is no use to add all of them anyway.
		if (count($responders) > 25)
		{
			return array();
		}

		foreach ($responders as $responder)
		{
			// Do not add them as a responder multiple times
			if ($responder['poster_id'] == $post['poster_id'])
			{
				return array();
			}
		}

		$responders[] = array(
			'poster_id'		=> $post['poster_id'],
			'username'		=> (($post['poster_id'] == ANONYMOUS) ? $post['post_username'] : ''),
		);

		$this->set_data('responders', $responders);

		$serialized_data = serialize($this->get_data(false));

		// If the data is longer then 4000 characters, it would cause a SQL error.
		// We don't add the username to the list if this is the case.
		if (utf8_strlen($serialized_data) >= 4000)
		{
			return array();
		}

		$data_array = array_merge(array(
			'post_time'		=> $post['post_time'],
			'post_id'		=> $post['post_id'],
			'topic_id'		=> $post['topic_id']
		), $this->get_data(false));

		return $data_array;
	}
}
