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
* PM report closed notifications class
* This class handles notifications for when reports are closed on PMs (for the one who reported the PM)
*/

class report_pm_closed extends \phpbb\notification\type\pm
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'notification.type.report_pm_closed';
	}

	/**
	* Email template to use to send notifications
	*
	* @var string
	*/
	public $email_template = 'report_pm_closed';

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_REPORT_PM_CLOSED';

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	static public $notification_option = [
		'id'	=> 'notification.type.report_pm_closed',
		'lang'	=> 'NOTIFICATION_TYPE_REPORT_PM_CLOSED',
		'group'	=> 'NOTIFICATION_GROUP_MISCELLANEOUS',
	];

	public function is_available()
	{
		return (bool) $this->config['allow_pm_report'];
	}

	/**
	* Find the users who want to receive notifications
	*
	* @param array $pm Data from submit_pm
	* @param array $options Options for finding users for notification
	*
	* @return array
	*/
	public function find_users_for_notification($pm, $options = [])
	{
		$options = array_merge([
			'ignore_users'		=> [],
		], $options);

		if ($pm['reporter'] == $this->user->data['user_id'])
		{
			return [];
		}

		return $this->check_user_notification_options([$pm['reporter']], $options);
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
		$sender_data = $this->user_loader->get_username($this->get_data('from_user_id'), 'username');
		$closer_data = $this->user_loader->get_username($this->get_data('closer_id'), 'username');

		return [
			'AUTHOR_NAME'	=> htmlspecialchars_decode($sender_data['username']),
			'CLOSER_NAME'	=> htmlspecialchars_decode($closer_data['username']),
			'SUBJECT'		=> htmlspecialchars_decode(censor_text($this->get_data('message_subject'))),

			'U_VIEW_MESSAGE'=> generate_board_url() . "/ucp.{$this->php_ext}?i=pm&amp;mode=view&amp;p={$this->item_id}",
		];
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
			censor_text($this->get_data('message_subject'))
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
	public function create_insert_array($pm, $pre_create_data = [])
	{
		$this->set_data('closer_id', $pm['closer_id']);

		parent::create_insert_array($pm, $pre_create_data);

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
