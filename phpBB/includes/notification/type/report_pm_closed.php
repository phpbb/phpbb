<?php
/**
*
* @package notifications
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* PM report closed notifications class
* This class handles notifications for when reports are closed on PMs (for the one who reported the PM)
*
* @package notifications
*/
class phpbb_notification_type_report_pm_closed extends phpbb_notification_type_pm
{
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
	* @param array $pm Data from
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
		$user_data = $this->notification_manager->get_user($this->get_data('closer_id'));

		$username = get_username_string('no_profile', $user_data['user_id'], $user_data['username'], $user_data['user_colour']);

		return $this->user->lang(
			$this->language_key,
			$username,
			censor_text($this->get_data('message_subject'))
		);
	}

	/**
	* Get the user's avatar
	*/
	public function get_avatar()
	{
		return $this->get_user_avatar($this->get_data('closer_id'));
	}

	/**
	* Users needed to query before this notification can be displayed
	*
	* @return array Array of user_ids
	*/
	public function users_to_query()
	{
		return array($this->data['closer_id']);
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

		$this->time = $data['time'] = time();

		return $data;
	}
}
