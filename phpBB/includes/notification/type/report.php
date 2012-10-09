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
* Post notifications class
* This class handles notifications for replies to a topic
*
* @package notifications
*/
class phpbb_notification_type_report extends phpbb_notification_type_post_in_queue
{
	/**
	* Email template to use to send notifications
	*
	* @var string
	*/
	public $email_template = 'topic_notify';

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_REPORT';

	/**
	* Permission to check for (in find_users_for_notification)
	*
	* @var string Permission name
	*/
	protected $permission = 'm_report';

	/**
	* Get the type of notification this is
	* phpbb_notification_type_
	*/
	public static function get_item_type()
	{
		return 'report';
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		return array(
			'POST_SUBJECT'				=> htmlspecialchars_decode(censor_text($this->get_data('post_subject'))),
			'TOPIC_TITLE'				=> htmlspecialchars_decode(censor_text($this->get_data('topic_title'))),

			'U_VIEW_POST'				=> generate_board_url() . "/viewtopic.{$this->php_ext}?p={$this->item_id}#p{$this->item_id}",
			'U_NEWEST_POST'				=> generate_board_url() . "/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_parent_id}&view=unread#unread",
			'U_TOPIC'					=> generate_board_url() . "/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_parent_id}",
			'U_VIEW_TOPIC'				=> generate_board_url() . "/viewtopic.{$this->php_ext}?f={$this->get_data('forum_id')}&t={$this->item_parent_id}",
			'U_FORUM'					=> generate_board_url() . "/viewforum.{$this->php_ext}?f={$this->get_data('forum_id')}",
			'U_STOP_WATCHING_TOPIC'		=> generate_board_url() . "/viewtopic.{$this->php_ext}?uid={$this->user_id}&f={$this->get_data('forum_id')}&t={$this->item_parent_id}&unwatch=topic",
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
}
