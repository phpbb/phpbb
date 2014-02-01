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

class test extends \phpbb\notification\type\base
{
	public function get_type()
	{
		return 'test';
	}

	public static function get_item_id($post)
	{
		return (int) $post['post_id'];
	}

	public static function get_item_parent_id($post)
	{
		return (int) $post['topic_id'];
	}

	public function find_users_for_notification($post, $options = array())
	{
		return $this->check_user_notification_options(array(0), $options);
	}

	public function create_insert_array($post, $pre_create_data = array())
	{
		$this->notification_time = $post['post_time'];

		return parent::create_insert_array($post, $pre_create_data);
	}

	public function create_update_array($type_data)
	{
		$data = $this->create_insert_array($type_data);

		// Unset data unique to each row
		unset(
			$data['notification_id'],
			$data['notification_read'],
			$data['user_id']
		);

		return $data;
	}

	public function get_title()
	{
		return 'test title';
	}

	public function users_to_query()
	{
		return array();
	}

	public function get_url()
	{
		return '';
	}

	public function get_email_template()
	{
		return false;
	}

	public function get_email_template_variables()
	{
		return array();
	}
}
