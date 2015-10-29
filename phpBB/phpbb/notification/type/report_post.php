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
* Reported post notifications class
* This class handles notifications for reported posts
*/
class report_post extends \phpbb\notification\type\post_in_queue
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'notification.type.report_post';
	}

	/**
	* Get the CSS style class of the notification
	*
	* @return string
	*/
	public function get_style_class()
	{
		return 'notification-reported';
	}

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_REPORT_POST';

	/**
	* Inherit notification read status from post.
	*
	* @var bool
	*/
	protected $inherit_read_status = false;

	/**
	* Permission to check for (in find_users_for_notification)
	*
	* @var string Permission name
	*/
	protected $permission = 'm_report';

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id' and 'lang')
	*/
	static public $notification_option = array(
		'id'	=> 'notification.type.report',
		'lang'	=> 'NOTIFICATION_TYPE_REPORT',
		'group'	=> 'NOTIFICATION_GROUP_MODERATION',
	);

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
		$notify_users = parent::find_users_for_notification($post, $options);

		// never notify reporter
		unset($notify_users[$this->user->data['user_id']]);

		return $notify_users;
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return 'report_post';
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		$board_url = generate_board_url();

		return array(
			'POST_SUBJECT'				=> htmlspecialchars_decode(censor_text($this->get_data('post_subject'))),
			'TOPIC_TITLE'				=> htmlspecialchars_decode(censor_text($this->get_data('topic_title'))),

			'U_VIEW_REPORT'				=> "{$board_url}/mcp.{$this->php_ext}?f={$this->get_data('forum_id')}&amp;p={$this->item_id}&amp;i=reports&amp;mode=report_details#reports",
			'U_VIEW_POST'				=> "{$board_url}/viewtopic.{$this->php_ext}?p={$this->item_id}#p{$this->item_id}",
			'U_NEWEST_POST'				=> "{$board_url}/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_parent_id}&view=unread#unread",
			'U_TOPIC'					=> "{$board_url}/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_parent_id}",
			'U_VIEW_TOPIC'				=> "{$board_url}/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_parent_id}",
			'U_FORUM'					=> "{$board_url}/viewforum.{$this->php_ext}?f={$this->get_data('forum_id')}",
		);
	}

	/**
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'mcp.' . $this->php_ext, "f={$this->get_data('forum_id')}&amp;p={$this->item_id}&amp;i=reports&amp;mode=report_details#reports");
	}

	/**
	* Get the HTML formatted title of this notification
	*
	* @return string
	*/
	public function get_title()
	{
		$this->language->add_lang('mcp');

		$username = $this->user_loader->get_username($this->get_data('reporter_id'), 'no_profile');

		return $this->language->lang(
			$this->language_key,
			$username
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
			censor_text($this->get_data('post_subject'))
		);
	}

	/**
	* Get the reason for the notification
	*
	* @return string
	*/
	public function get_reason()
	{
		if ($this->get_data('report_text'))
		{
			return $this->language->lang(
				'NOTIFICATION_REASON',
				$this->get_data('report_text')
			);
		}

		if ($this->language->is_set($this->get_data('reason_title')))
		{
			return $this->language->lang(
				'NOTIFICATION_REASON',
				$this->language->lang($this->get_data('reason_title'))
			);
		}

		return $this->language->lang(
			'NOTIFICATION_REASON',
			$this->get_data('reason_description')
		);
	}

	/**
	* Get the user's avatar
	*/
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('reporter_id'), false, true);
	}

	/**
	* Users needed to query before this notification can be displayed
	*
	* @return array Array of user_ids
	*/
	public function users_to_query()
	{
		return array($this->get_data('reporter_id'));
	}

	/**
	* {@inheritdoc}
	*/
	public function create_insert_array($post, $pre_create_data = array())
	{
		$this->set_data('reporter_id', $this->user->data['user_id']);
		$this->set_data('reason_title', strtoupper($post['reason_title']));
		$this->set_data('reason_description', $post['reason_description']);
		$this->set_data('report_text', $post['report_text']);

		parent::create_insert_array($post, $pre_create_data);
	}
}
