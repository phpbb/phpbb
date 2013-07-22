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
* Reported post notifications class
* This class handles notifications for reported posts
*
* @package notifications
*/
class phpbb_notification_type_report_post extends phpbb_notification_type_post_in_queue
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'report_post';
	}

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_REPORT_POST';

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
	public static $notification_option = array(
		'id'	=> 'report',
		'lang'	=> 'NOTIFICATION_TYPE_REPORT',
		'group'	=> 'NOTIFICATION_GROUP_MODERATION',
	);

	/**
	* Find the users who want to receive notifications
	*
	* @param array $post Data from the post
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
		$this->user->add_lang('mcp');

		$username = $this->user_loader->get_username($this->get_data('reporter_id'), 'no_profile');

		if ($this->get_data('report_text'))
		{
			return $this->user->lang(
				$this->language_key,
				$username,
				censor_text($this->get_data('post_subject')),
				$this->get_data('report_text')
			);
		}

		if (isset($this->user->lang[$this->get_data('reason_title')]))
		{
			return $this->user->lang(
				$this->language_key,
				$username,
				censor_text($this->get_data('post_subject')),
				$this->user->lang[$this->get_data('reason_title')]
			);
		}

		return $this->user->lang(
			$this->language_key,
			$username,
			censor_text($this->get_data('post_subject')),
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
