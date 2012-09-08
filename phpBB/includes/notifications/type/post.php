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
* @package notifications
*/
class phpbb_notifications_type_post extends phpbb_notifications_type_base
{
	/**
	* Get the type of notification this is
	* phpbb_notifications_type_
	*/
	public function get_type()
	{
		return 'post';
	}

	public function get_title()
	{
		return $this->data['post_username'] . ' posted in the topic ' . censor_text($this->data['topic_title']);
	}

	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'viewtopic.' . $this->php_ext, "p={$this->item_id}#p{$this->item_id}");
	}

	/**
	* Users needed to query before this notification can be displayed
	*
	* @return array Array of user_ids
	*/
	public function users_to_query()
	{
		return array($this->data['poster_id']);
	}

	public function create_insert_array($post)
	{
		$this->item_id = $post['post_id'];

		$this->set_data('poster_id', $post['poster_id']);

		$this->set_data('topic_title', $post['topic_title']);

		$this->set_data('post_username', $post['post_username']);

		return parent::create_insert_array($post);
	}
}
