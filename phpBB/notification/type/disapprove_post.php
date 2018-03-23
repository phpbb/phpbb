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
* Post disapproved notifications class
* This class handles notifications for posts when they are disapproved (for authors)
*/

class disapprove_post extends \phpbb\notification\type\approve_post
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'notification.type.disapprove_post';
	}

	/**
	* Get the CSS style class of the notification
	*
	* @return string
	*/
	public function get_style_class()
	{
		return 'notification-disapproved';
	}

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_POST_DISAPPROVED';

	/**
	* Inherit notification read status from post.
	*
	* @var bool
	*/
	protected $inherit_read_status = false;

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	static public $notification_option = array(
		'id'	=> 'moderation_queue',
		'lang'	=> 'NOTIFICATION_TYPE_MODERATION_QUEUE',
		'group'	=> 'NOTIFICATION_GROUP_POSTING',
	);

	/**
	* Get the HTML formatted title of this notification
	*
	* @return string
	*/
	public function get_title()
	{
		return $this->language->lang($this->language_key);
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
	* Get the reason for the disapproval notification
	*
	* @return string
	*/
	public function get_reason()
	{
		return $this->language->lang(
			'NOTIFICATION_REASON',
			$this->get_data('disapprove_reason')
		);
	}

	/**
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url()
	{
		return '';
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		return array_merge(parent::get_email_template_variables(), array(
			'REASON'	=> htmlspecialchars_decode($this->get_data('disapprove_reason')),
		));
	}

	/**
	* {@inheritdoc}
	*/
	public function create_insert_array($post, $pre_create_data = array())
	{
		$this->set_data('disapprove_reason', $post['disapprove_reason']);

		parent::create_insert_array($post, $pre_create_data);

		$this->notification_time = time();
	}

	/**
	* {@inheritdoc}
	*/
	public function get_insert_array()
	{
		$data = parent::get_insert_array();
		$data['notification_time'] = $this->notification_time;

		return $data;
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return 'post_disapproved';
	}
}
