<?php
/**
*
* @package notifications
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\notification\type;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Topic disapproved notifications class
* This class handles notifications for topics when they are disapproved (for authors)
*
* @package notifications
*/
class disapprove_topic extends \phpbb\notification\type\approve_topic
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'disapprove_topic';
	}

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_TOPIC_DISAPPROVED';

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	public static $notification_option = array(
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
		return $this->user->lang(
			$this->language_key,
			censor_text($this->get_data('topic_title')),
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
			'REASON'			=> htmlspecialchars_decode($this->get_data('disapprove_reason')),
		));
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
		$this->set_data('disapprove_reason', $post['disapprove_reason']);

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
		return 'topic_disapproved';
	}
}
