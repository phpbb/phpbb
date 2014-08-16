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

class group_request extends \phpbb\notification\type\base
{
	/**
	* {@inheritdoc}
	*/
	public function get_type()
	{
		return 'notification.type.group_request';
	}

	/**
	* {@inheritdoc}
	*/
	public static $notification_option = array(
		'lang'	=> 'NOTIFICATION_TYPE_GROUP_REQUEST',
	);

	/**
	* {@inheritdoc}
	*/
	public function is_available()
	{
		// Leader of any groups?
		$sql = 'SELECT group_id
			FROM ' . USER_GROUP_TABLE . '
			WHERE user_id = ' . (int) $this->user->data['user_id'] . '
				AND group_leader = 1';
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return (!empty($row)) ? true : false;
	}

	/**
	* {@inheritdoc}
	*/
	public static function get_item_id($group)
	{
		return (int) $group['user_id'];
	}

	/**
	* {@inheritdoc}
	*/
	public static function get_item_parent_id($group)
	{
		// Group id is the parent
		return (int) $group['group_id'];
	}

	/**
	* {@inheritdoc}
	*/
	public function find_users_for_notification($group, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'		=> array(),
		), $options);

		$sql = 'SELECT user_id
			FROM ' . USER_GROUP_TABLE . '
			WHERE group_leader = 1
				AND group_id = ' . (int) $group['group_id'];
		$result = $this->db->sql_query($sql);

		$user_ids = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_ids[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		$this->user_loader->load_users($user_ids);

		return $this->check_user_notification_options($user_ids, $options);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->item_id);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_title()
	{
		$username = $this->user_loader->get_username($this->item_id, 'no_profile');

		return $this->user->lang('NOTIFICATION_GROUP_REQUEST', $username, $this->get_data('group_name'));
	}

	/**
	* {@inheritdoc}
	*/
	public function get_email_template()
	{
		return 'group_request';
	}

	/**
	* {@inheritdoc}
	*/
	public function get_email_template_variables()
	{
		$user_data = $this->user_loader->get_user($this->item_id);

		return array(
			'GROUP_NAME'		   		=> htmlspecialchars_decode($this->get_data('group_name')),
			'REQUEST_USERNAME' 	   		=> htmlspecialchars_decode($user_data['username']),

			'U_PENDING'			  		=> generate_board_url() . "/ucp.{$this->php_ext}?i=groups&mode=manage&action=list&g={$this->item_parent_id}",
			'U_GROUP'					=> generate_board_url() . "/memberlist.{$this->php_ext}?mode=group&g={$this->item_parent_id}",
		);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'ucp.' . $this->php_ext, "i=groups&mode=manage&action=list&g={$this->item_parent_id}");
	}

	/**
	* {@inheritdoc}
	*/
	public function users_to_query()
	{
		return array($this->item_id);
	}

	/**
	* {@inheritdoc}
	*/
	public function create_insert_array($group, $pre_create_data = array())
	{
		$this->set_data('group_name', $group['group_name']);

		return parent::create_insert_array($group, $pre_create_data);
	}
}
