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
* Private message reported notifications class
* This class handles notifications for private messages when they are reported
*
* @package notifications
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
		return 'report_pm';
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
	protected $permission = 'm_report';

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	public static $notification_option = array(
		'id'	=> 'report',
		'lang'	=> 'NOTIFICATION_TYPE_REPORT',
		'group'	=> 'NOTIFICATION_GROUP_MODERATION',
	);

	/**
	* Get the id of the parent
	*
	* @param array $pm The data from the pm
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
		$m_approve = $this->auth->acl_getf($this->permission, true);

		return (!empty($m_approve));
	}


	/**
	* Find the users who want to receive notifications
	*  (copied from post_in_queue)
	*
	* @param array $post Data from the post
	*
	* @return array
	*/
	public function find_users_for_notification($post, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'		=> array(),
		), $options);

		// Global
		$post['forum_id'] = 0;

		$auth_approve = $this->auth->acl_get_list(false, $this->permission, $post['forum_id']);

		if (empty($auth_approve))
		{
			return array();
		}

		if (($key = array_search($this->user->data['user_id'], $auth_approve[$post['forum_id']][$this->permission])))
		{
			unset($auth_approve[$post['forum_id']][$this->permission][$key]);
		}

		return $this->check_user_notification_options($auth_approve[$post['forum_id']][$this->permission], array_merge($options, array(
			'item_type'		=> self::$notification_option['id'],
		)));
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
		return array(
			'AUTHOR_NAME'				=> htmlspecialchars_decode($user_data['username']),
			'SUBJECT'					=> htmlspecialchars_decode(censor_text($this->get_data('message_subject'))),

			'U_VIEW_REPORT'				=> generate_board_url() . "mcp.{$this->php_ext}?r={$this->item_parent_id}&amp;i=pm_reports&amp;mode=pm_report_details",
		);
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
		$this->user->add_lang('mcp');

		$username = $this->user_loader->get_username($this->get_data('reporter_id'), 'no_profile');

		if ($this->get_data('report_text'))
		{
			return $this->user->lang(
				$this->language_key,
				$username,
				censor_text($this->get_data('message_subject')),
				$this->get_data('report_text')
			);
		}

		if (isset($this->user->lang[$this->get_data('reason_title')]))
		{
			return $this->user->lang(
				$this->language_key,
				$username,
				censor_text($this->get_data('message_subject')),
				$this->user->lang[$this->get_data('reason_title')]
			);
		}

		return $this->user->lang(
			$this->language_key,
			$username,
			censor_text($this->get_data('message_subject')),
			$this->get_data('reason_description')
		);
	}

	/**
	* Get the user's avatar
	*/
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('reporter_id'));
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
		$this->set_data('reporter_id', $this->user->data['user_id']);
		$this->set_data('reason_title', strtoupper($post['reason_title']));
		$this->set_data('reason_description', $post['reason_description']);
		$this->set_data('report_text', $post['report_text']);

		return parent::create_insert_array($post, $pre_create_data);
	}
}
