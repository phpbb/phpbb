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
	public $email_template = '';

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_REPORT_CLOSED';

	public function is_available()
	{
		return false;
	}

	/**
	* Find the users who want to receive notifications
	*
	* @param array $pm Data from submit_pm
	* @param array $options Options for finding users for notification
	*
	* @return array
	*/
	public function find_users_for_notification($pm, $options = array())
	{
		if ($pm['reporter'] == $this->user->data['user_id'])
		{
			return array();
		}

		return array($pm['reporter'] => array(''));
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return false;
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		return array();
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
	* Get the HTML formatted title of this notification
	*
	* @return string
	*/
	public function get_title()
	{
		$username = $this->user_loader->get_username($this->get_data('closer_id'), 'no_profile');

		return $this->user->lang(
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
		return $this->user->lang(
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
		return array($this->get_data('closer_id'));
	}

	/**
	* Function for preparing the data for insertion in an SQL query
	* (The service handles insertion)
	*
	* @param array $pm PM Data
	* @param array $pre_create_data Data from pre_create_insert_array()
	*
	* @return array Array of data ready to be inserted into the database
	*/
	public function create_insert_array($pm, $pre_create_data = array())
	{
		$this->set_data('closer_id', $pm['closer_id']);

		$data = parent::create_insert_array($pm, $pre_create_data);

		$this->notification_time = $data['notification_time'] = time();

		return $data;
	}
}
