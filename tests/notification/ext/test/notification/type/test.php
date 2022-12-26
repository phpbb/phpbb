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

	public static function get_item_id($type_data)
	{
		return (int) $type_data['post_id'];
	}

	public static function get_item_parent_id($type_data)
	{
		return (int) $type_data['topic_id'];
	}

	public function find_users_for_notification($type_data, $options = array())
	{
		return $this->check_user_notification_options(array(0), $options);
	}

	public function create_insert_array($type_data, $pre_create_data = array())
	{
		$this->notification_time = $type_data['post_time'];

		parent::create_insert_array($type_data, $pre_create_data);
	}

	public function create_update_array($type_data)
	{
		$this->create_insert_array($type_data);
		$data = $this->get_insert_array();

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
