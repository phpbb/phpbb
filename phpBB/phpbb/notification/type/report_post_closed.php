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
* Post report closed notifications class
* This class handles notifications for when reports are closed on posts (for the one who reported the post)
*/

class report_post_closed extends \phpbb\notification\type\post
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'notification.type.report_post_closed';
	}

	/**
	* Email template to use to send notifications
	*
	* @var string
	*/
	public $email_template = 'report_closed';

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_REPORT_CLOSED';

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	static public $notification_option = [
		'id'	=> 'notification.type.report_post_closed',
		'lang'	=> 'NOTIFICATION_TYPE_REPORT_CLOSED',
		'group'	=> 'NOTIFICATION_GROUP_MISCELLANEOUS',
	];

	/**
	* Inherit notification read status from post.
	*
	* @var bool
	*/
	protected $inherit_read_status = false;

	public function is_available()
	{
		return $this->auth->acl_getf_global('f_report');
	}

	/**
	* Find the users who want to receive notifications
	*
	* @param array $post Data from submit_post
	* @param array $options Options for finding users for notification
	*
	* @return array
	*/
	public function find_users_for_notification($post, $options = [])
	{
		$options = array_merge([
			'ignore_users'		=> [],
		], $options);

		if ($post['reporter'] == $this->user->data['user_id'])
		{
			return [];
		}

		return $this->check_user_notification_options([$post['reporter']], $options);
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return $this->email_template;
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		$post_username = $this->get_data('post_username') ?: $this->user_loader->get_username($this->get_data('poster_id'), 'username');
		$closer_username = $this->user_loader->get_username($this->get_data('closer_id'), 'username');

		return [
			'AUTHOR_NAME'	=> html_entity_decode($post_username, ENT_COMPAT),
			'CLOSER_NAME'	=> html_entity_decode($closer_username, ENT_COMPAT),
			'POST_SUBJECT'	=> html_entity_decode(censor_text($this->get_data('post_subject')), ENT_COMPAT),
			'TOPIC_TITLE'	=> html_entity_decode(censor_text($this->get_data('topic_title')), ENT_COMPAT),

			'U_VIEW_POST'	=> generate_board_url() . "/viewtopic.{$this->php_ext}?p={$this->item_id}#p{$this->item_id}",
		];
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
		return $this->get_url();
	}

	/**
	* Get the HTML formatted title of this notification
	*
	* @return string
	*/
	public function get_title()
	{
		$username = $this->user_loader->get_username($this->get_data('closer_id'), 'no_profile');

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
	* Get the user's avatar
	*/
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('closer_id'), false, true);
	}

	/**
	* Users needed to query before this notification can be displayed
	*
	* @return array Array of user_ids
	*/
	public function users_to_query()
	{
		return [$this->get_data('closer_id')];
	}

	/**
	* {@inheritdoc}
	*/
	public function create_insert_array($post, $pre_create_data = [])
	{
		$this->set_data('closer_id', $post['closer_id']);

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
}
