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
* Private message reported notifications class
* This class handles notifications for private messages when they are reported
*/

class report_pm extends \phpbb\notification\type\pm
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'notification.type.report_pm';
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
	protected $language_key = 'NOTIFICATION_REPORT_PM';

	/**
	* Permission to check for (in find_users_for_notification)
	*
	* @var string Permission name
	*/
	protected $permission = 'm_pm_report';

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	public static $notification_option = [
		'id'	=> 'notification.type.report_pm',
		'lang'	=> 'NOTIFICATION_TYPE_REPORT_PM',
		'group'	=> 'NOTIFICATION_GROUP_MODERATION',
	];

	/**
	* Get the id of the parent
	*
	* @param array $pm The data from the pm
	* @return int The report id
	*/
	public static function get_item_parent_id($pm)
	{
		return (int) $pm['report_id'];
	}

	/**
	* Is this type available to the current user (defines whether or not it will be shown in the UCP Edit notification options)
	*
	* @return bool True/False whether or not this is available to the user
	*/
	public function is_available()
	{
		return $this->config['allow_pm_report'] &&
			!empty($this->auth->acl_get($this->permission));
	}

	/**
	* Find the users who want to receive notifications
	*  (copied from post_in_queue)
	*
	* @param array $post Data from the post
	* @param array $options Options for finding users for notification
	*
	* @return array
	*/
	public function find_users_for_notification($post, $options = [])
	{
		$options = array_merge([
			'ignore_users'		=> [],
		], $options);

		// Global
		$post['forum_id'] = 0;

		$auth_approve = $this->auth->acl_get_list(false, $this->permission, $post['forum_id']);

		if (empty($auth_approve))
		{
			return [];
		}

		if (($key = array_search($this->user->data['user_id'], $auth_approve[$post['forum_id']][$this->permission])))
		{
			unset($auth_approve[$post['forum_id']][$this->permission][$key]);
		}

		return $this->check_user_notification_options($auth_approve[$post['forum_id']][$this->permission], array_merge($options, [
			'item_type'		=> static::$notification_option['id'],
		]));
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return 'report_pm';
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		$user_data = $this->user_loader->get_user($this->get_data('from_user_id'));

		return [
			'AUTHOR_NAME'	=> htmlspecialchars_decode($user_data['username'], ENT_COMPAT),
			'SUBJECT'		=> htmlspecialchars_decode(censor_text($this->get_data('message_subject')), ENT_COMPAT),

			/** @deprecated	3.2.6-RC1	(to be removed in 4.0.0) use {SUBJECT} instead in report_pm.txt */
			'TOPIC_TITLE'	=> htmlspecialchars_decode(censor_text($this->get_data('message_subject')), ENT_COMPAT),

			'U_VIEW_REPORT'	=> generate_board_url() . "/mcp.{$this->php_ext}?r={$this->item_parent_id}&i=pm_reports&mode=pm_report_details",
		];
	}

	/**
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'mcp.' . $this->php_ext, "r={$this->item_parent_id}&amp;i=pm_reports&amp;mode=pm_report_details");
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
			censor_text($this->get_data('message_subject'))
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
		return [
			$this->get_data('from_user_id'),
			$this->get_data('reporter_id'),
		];
	}

	/**
	* {@inheritdoc}
	*/
	public function create_insert_array($post, $pre_create_data = [])
	{
		$this->set_data('reporter_id', $this->user->data['user_id']);
		$this->set_data('reason_title', strtoupper($post['reason_title']));
		$this->set_data('reason_description', $post['reason_description']);
		$this->set_data('report_text', $post['report_text']);

		parent::create_insert_array($post, $pre_create_data);
	}
}
