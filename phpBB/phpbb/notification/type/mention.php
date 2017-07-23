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
class mention extends \phpbb\notification\type\post
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'notification.type.mention';
	}

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_MENTION';

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var \phpbb\config\config */
	protected $config;

	/**
	* Inherit notification read status from post.
	*
	* @var bool
	*/
	protected $inherit_read_status = true;

	static public $notification_option = false;

	public function set_config(\phpbb\config\config $config)
	{
		$this->config = $config;
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
	* Retrieves an ArrayIterator over the configuration values.
	*
	* @return \ArrayIterator An iterator over all config data
	*/
	public function getIterator()
	{
		return new \ArrayIterator($this->config);
	}

	/**
	* Extract user_ids of all users mentioned in the post.
	*
	* @return \Array An array of all mentioned userids.
	*/
	public function find_users_for_notification($db, $users_to_notify = array())
	{
		if (count($users_to_notify) > 0)
		{
			$sql_ary = "SELECT users.user_id, users.username_clean FROM ". USERS_TABLE . " as users, " . USER_NOTIFICATIONS_TABLE . " as notif WHERE " . $db->sql_in_set('username_clean', $users_to_notify) . " AND users.user_id = notif.user_id AND notif.item_type = 'notification.type.mention' " ;
			$result = $db->sql_query($sql_ary);
			$user_list = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$user_list[$row['username_clean']] = $row['user_id'];
			}
			$db->sql_freeresult($result);
			return $user_list;
		}
		return array();
	}

	/**
	* Get the type and method of notification corresponding the user
	*
	* @return \Array Array containing the notification type object and notification
	* method object.
	*/
	public function get_notification_type_and_method($db, $user_list, $notif_manager)
	{
		$notif_list = array();
		if (count($user_list) > 0)
		{
			$integer_ids = array_map('intval', $user_list);
			$sql_query = "SELECT user_id, item_type, method FROM " . USER_NOTIFICATIONS_TABLE . " WHERE " . $db->sql_in_set('user_id', $integer_ids) . " AND item_type = 'notification.type.mention'";
			$result = $db->sql_query($sql_query);
			while ($row = $db->sql_fetchrow($result))
			{
				// $temp_notif_list = array();
				if (is_array($notif_list[$row['user_id']]))
				{
					$notif_list[$row['user_id']][] = $row['method'];
				}
				else
				{
					$notif_list[$row['user_id']] = array();
					$notif_list[$row['user_id']][] = $row['method'];
				}
			}
			$db->sql_freeresult($result);
		}
		return $notif_list;
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
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return 'mention_notify';
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
			'AUTHOR_NAME'               => htmlspecialchars_decode($username),
			'POST_SUBJECT'              => htmlspecialchars_decode(censor_text($this->get_data('post_subject'))),
			'TOPIC_TITLE'               => htmlspecialchars_decode(censor_text($this->get_data('topic_title'))),
			'U_VIEW_POST'               => generate_board_url() . "/viewtopic.{$this->php_ext}?p={$this->item_id}#p{$this->item_id}",
			'U_NEWEST_POST'             => generate_board_url() . "/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_parent_id}&e=1&view=unread#unread",
			'U_TOPIC'                   => generate_board_url() . "/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_parent_id}",
			'U_VIEW_TOPIC'              => generate_board_url() . "/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_parent_id}",
			'U_FORUM'                   => generate_board_url() . "/viewforum.{$this->php_ext}?f={$this->get_data('forum_id')}",
			'U_STOP_WATCHING_TOPIC'     => generate_board_url() . "/viewtopic.{$this->php_ext}?uid={$this->user_id}&f={$this->get_data('forum_id')}&t={$this->item_parent_id}&unwatch=topic",
		);
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
		parent::create_insert_array($post, $pre_create_data);
	}
}
