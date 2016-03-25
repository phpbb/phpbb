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

namespace phpbb\db\migration\data\v310;

class avatars extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		// Get current avatar type of guest user
		$sql = 'SELECT user_avatar_type
			FROM ' . $this->table_prefix . 'users
			WHERE user_id = ' . ANONYMOUS;
		$result = $this->db->sql_query($sql);
		$backup_type = $this->db->sql_fetchfield('user_avatar_type');
		$this->db->sql_freeresult($result);

		// Try to set avatar type to string
		$sql = 'UPDATE ' . $this->table_prefix . "users
			SET user_avatar_type = 'avatar.driver.upload'
			WHERE user_id = " . ANONYMOUS;
		$this->db->sql_return_on_error(true);
		$effectively_installed = $this->db->sql_query($sql);
		$this->db->sql_return_on_error();

		// Restore avatar type of guest user to previous state
		$sql = 'UPDATE ' . $this->table_prefix . "users
			SET user_avatar_type = '{$backup_type}'
			WHERE user_id = " . ANONYMOUS;
		$this->db->sql_query($sql);

		return $effectively_installed !== false;
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_11');
	}

	public function update_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'users'			=> array(
					'user_avatar_type'		=> array('VCHAR:255', ''),
				),
				$this->table_prefix . 'groups'			=> array(
					'group_avatar_type'		=> array('VCHAR:255', ''),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'users'			=> array(
					'user_avatar_type'		=> array('TINT:2', ''),
				),
				$this->table_prefix . 'groups'			=> array(
					'group_avatar_type'		=> array('TINT:2', ''),
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('allow_avatar_gravatar', 0)),
			array('custom', array(array($this, 'update_module_auth'))),
		);
	}

	public function update_module_auth()
	{
		$sql = 'UPDATE ' . $this->table_prefix . "modules
			SET module_auth = 'cfg_allow_avatar'
			WHERE module_class = 'ucp'
				AND module_basename = 'ucp_profile'
				AND module_mode = 'avatar'";
		$this->db->sql_query($sql);
	}
}
