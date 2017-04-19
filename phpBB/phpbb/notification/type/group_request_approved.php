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

class group_request_approved extends \phpbb\notification\type\base
{
	/**
	* {@inheritdoc}
	*/
	public function get_type()
	{
		return 'notification.type.group_request_approved';
	}

	/**
	* {@inheritdoc}
	*/
	public function is_available()
	{
		return false;
	}

	/**
	* {@inheritdoc}
	*/
	static public function get_item_id($group)
	{
		return (int) $group['group_id'];
	}

	/**
	* {@inheritdoc}
	*/
	static public function get_item_parent_id($group)
	{
		return 0;
	}

	/**
	* {@inheritdoc}
	*/
	public function find_users_for_notification($group, $options = array())
	{
		$users = array();

		$group['user_ids'] = (!is_array($group['user_ids'])) ? array($group['user_ids']) : $group['user_ids'];

		foreach ($group['user_ids'] as $user_id)
		{
			$users[$user_id] = $this->notification_manager->get_default_methods();
		}

		return $users;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_title()
	{
		return $this->language->lang('NOTIFICATION_GROUP_REQUEST_APPROVED', $this->get_data('group_name'));
	}

	/**
	* {@inheritdoc}
	*/
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'memberlist.' . $this->php_ext, "mode=group&g={$this->item_id}");
	}

	/**
	* {@inheritdoc}
	*/
	public function create_insert_array($group, $pre_create_data = array())
	{
		$this->set_data('group_name', $group['group_name']);

		parent::create_insert_array($group, $pre_create_data);
	}

	/**
	* {@inheritdoc}
	*/
	public function users_to_query()
	{
		return array();
	}

	/**
	* {@inheritdoc}
	*/
	public function get_email_template()
	{
		return false;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_email_template_variables()
	{
		return array();
	}
}
