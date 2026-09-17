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

use phpbb\cron\task\core\version_check;

class update_maintenance extends base
{
	/** @var string[] Notification options */
	public static $notification_option = [
		'lang' => 'NOTIFICATION_TYPE_UPDATE_MAINTENANCE',
		'group' => 'NOTIFICATION_GROUP_ADMINISTRATION',
	];

	/**
	* {@inheritdoc}
	*/
	public function get_type()
	{
		return 'notification.type.update_maintenance';
	}

	/**
	* {@inheritdoc}
	*/
	public static function get_item_id($type_data)
	{
		return isset($type_data['item_id']) ? (int) $type_data['item_id'] : 0;
	}

	/**
	* {@inheritdoc}
	*/
	public static function get_item_parent_id($type_data)
	{
		return 0;
	}

	/**
	* {@inheritdoc}
	*/
	public function find_users_for_notification($type_data, $options)
	{
		$options = array_merge([
			'ignore_users' => [],
		], $options);

		// Grab admins that have a_board permission
		$admin_ary = $this->auth->acl_get_list(false, 'a_board');
		$users = (!empty($admin_ary[0]['a_board'])) ? $admin_ary[0]['a_board'] : [];

		// Also grab founders (they have all a_* permissions implicitly)
		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . '
			WHERE user_type = ' . USER_FOUNDER;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$users[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($users))
		{
			return [];
		}

		$users = array_unique($users);

		return $this->check_user_notification_options($users, $options);
	}

	/**
	* {@inheritdoc}
	*/
	public function users_to_query()
	{
		return [];
	}

	/**
	* {@inheritdoc}
	*/
	public function get_title()
	{
		$template = $this->get_data('template');
		$new_version = $this->get_data('new_version');
		$current_version = $this->get_data('current_version');

		if ($template === version_check::UPDATE_SECURITY)
		{
			return $this->language->lang('NOTIFICATION_UPDATE_SECURITY', $current_version, $new_version);
		}

		if ($template === version_check::UPDATE_CRITICAL)
		{
			return $this->language->lang('NOTIFICATION_UPDATE_CRITICAL', $current_version, $new_version);
		}

		return $this->language->lang('NOTIFICATION_UPDATE_MAINTENANCE', $current_version, $new_version);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_url()
	{
		// Can't redirect to ACP without exposing session ID
		return '';
	}

	/**
	* {@inheritdoc}
	*/
	public function get_email_template()
	{
		return $this->get_data('template') ?: version_check::UPDATE_MAINTENANCE;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_email_template_variables()
	{
		return [
			'CURRENT_VERSION' => $this->get_data('current_version'),
			'NEW_VERSION' => $this->get_data('new_version'),
		];
	}

	/**
	* {@inheritdoc}
	*/
	public function create_insert_array($type_data, $pre_create_data = [])
	{
		$this->set_data('template', $type_data['template']);
		$this->set_data('current_version', $type_data['current_version']);
		$this->set_data('new_version', $type_data['new_version']);
		$this->set_data('announcement', $type_data['announcement']);
		$this->set_data('download', $type_data['download']);

		parent::create_insert_array($type_data, $pre_create_data);
	}
}
