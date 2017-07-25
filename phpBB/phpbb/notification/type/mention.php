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
* User mention notifications class
* This class handles notifications when a user is mentioned in a post.
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

	/**
	* Is available
	*/
	public function is_available()
	{
		return $this->config['allow_topic_notify'];
	}

	/**
	* Extract user_ids of all users mentioned in the post.
	*
	* @param $db  \phpbb\db\driver\driver_interface     DB driver interface.
	* @param $users_to_notify  Array                    Usernames of all users emntioned
	*													in the post.
	*
	* @return  Array                                   An array of all mentioned userids.
	*/
	public function find_users_for_notification($db, $users_to_notify = [])
	{
		if (count($users_to_notify) > 0)
		{
			$sql_ary = "SELECT users.user_id, users.username_clean FROM ". USERS_TABLE . " as users, " . USER_NOTIFICATIONS_TABLE . " as notif WHERE " . $db->sql_in_set('username_clean', $users_to_notify) . " AND users.user_id = notif.user_id AND notif.item_type = 'notification.type.mention' " ;
			$result = $db->sql_query($sql_ary);
			$user_list = [];
			while ($row = $db->sql_fetchrow($result))
			{
				$user_list[$row['username_clean']] = $row['user_id'];
			}
			$db->sql_freeresult($result);
			return $user_list;
		}
		return [];
	}

	/**
	* Get the type and method of notification corresponding the user
	*
	* @param $db          \phpbb\db\driver\driver_interface  DB driver interface.
	* @param $user_list   Array                         Array containing all
	*													user_ids mentioned in the post.
	*
	* @return Array       Array containing the notification type object and notification
	*                     method object.
	*/
	public function get_notification_type_and_method($db, $user_list)
	{
		$notif_list = [];
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
					$notif_list[$row['user_id']] = [];
					$notif_list[$row['user_id']][] = $row['method'];
				}
			}
			$db->sql_freeresult($result);
		}
		return $notif_list;
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

		return [
			'AUTHOR_NAME'               => htmlspecialchars_decode($username),
			'POST_SUBJECT'              => htmlspecialchars_decode(censor_text($this->get_data('post_subject'))),
			'TOPIC_TITLE'               => htmlspecialchars_decode(censor_text($this->get_data('topic_title'))),
			'U_VIEW_POST'               => generate_board_url() . "/viewtopic.{$this->php_ext}?p={$this->item_id}#p{$this->item_id}",
			'U_NEWEST_POST'             => generate_board_url() . "/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_parent_id}&e=1&view=unread#unread",
			'U_TOPIC'                   => generate_board_url() . "/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_parent_id}",
			'U_VIEW_TOPIC'              => generate_board_url() . "/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_parent_id}",
			'U_FORUM'                   => generate_board_url() . "/viewforum.{$this->php_ext}?f={$this->get_data('forum_id')}",
			'U_STOP_WATCHING_TOPIC'     => generate_board_url() . "/viewtopic.{$this->php_ext}?uid={$this->user_id}&f={$this->get_data('forum_id')}&t={$this->item_parent_id}&unwatch=topic",
		];
	}

	/**
	* {@inheritdoc}
	*/
	public function create_insert_array($post, $pre_create_data = [])
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
