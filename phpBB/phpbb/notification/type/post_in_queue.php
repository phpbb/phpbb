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
* Post in queue notifications class
* This class handles notifications for posts that are put in the moderation queue (for moderators)
*/

class post_in_queue extends \phpbb\notification\type\post
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'notification.type.post_in_queue';
	}

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
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	public static $notification_option = array(
		'id'	=> 'notification.type.needs_approval',
		'lang'	=> 'NOTIFICATION_TYPE_IN_MODERATION_QUEUE',
		'group'	=> 'NOTIFICATION_GROUP_MODERATION',
	);

	/**
	* Permission to check for (in find_users_for_notification)
	*
	* @var string Permission name
	*/
	protected $permission = 'm_approve';

	/**
	* Is available
	*/
	public function is_available()
	{
		$has_permission = $this->auth->acl_getf($this->permission, true);

		return (!empty($has_permission));
	}

	/**
	* Find the users who want to receive notifications
	*
	* @param array $post Data from the post
	* @param array $options Options for finding users for notification
	*
	* @return array
	*/
	public function find_users_for_notification($post, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'		=> array(),
		), $options);

		// 0 is for global moderator permissions
		$auth_approve = $this->auth->acl_get_list(false, $this->permission, array($post['forum_id'], 0));

		if (empty($auth_approve))
		{
			return array();
		}

		$has_permission = array();

		if (isset($auth_approve[$post['forum_id']][$this->permission]))
		{
			$has_permission = $auth_approve[$post['forum_id']][$this->permission];
		}

		if (isset($auth_approve[0][$this->permission]))
		{
			$has_permission = array_unique(array_merge($has_permission, $auth_approve[0][$this->permission]));
		}
		sort($has_permission);

		$auth_read = $this->auth->acl_get_list($has_permission, 'f_read', $post['forum_id']);
		if (empty($auth_read))
		{
			return array();
		}

		return $this->check_user_notification_options($auth_read[$post['forum_id']]['f_read'], array_merge($options, array(
			'item_type'		=> static::$notification_option['id'],
		)));
	}

	/**
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'mcp.' . $this->php_ext, "i=queue&amp;mode=approve_details&amp;f={$this->get_data('forum_id')}&amp;p={$this->item_id}");
	}

	/**
	* {inheritDoc}
	*/
	public function get_redirect_url()
	{
		return parent::get_url();
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
		$data = parent::create_insert_array($post, $pre_create_data);

		$this->notification_time = $data['notification_time'] = time();

		return $data;
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return 'post_in_queue';
	}
}
