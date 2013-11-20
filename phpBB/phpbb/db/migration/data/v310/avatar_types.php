<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class avatar_types extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\dev',
			'\phpbb\db\migration\data\v310\avatars',
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'update_user_avatar_type'))),
			array('custom', array(array($this, 'update_group_avatar_type'))),
		);
	}

	public function update_user_avatar_type()
	{
		$sql = 'UPDATE ' . $this->table_prefix . "users
			SET user_avatar_type = 'avatar.driver.upload'
			WHERE user_avatar_type = " . AVATAR_UPLOAD;
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . $this->table_prefix . "users
			SET user_avatar_type = 'avatar.driver.remote'
			WHERE user_avatar_type = " . AVATAR_REMOTE;
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . $this->table_prefix . "users
			SET user_avatar_type = 'avatar.driver.local'
			WHERE user_avatar_type = " . AVATAR_GALLERY;
		$this->db->sql_query($sql);
	}

	public function update_group_avatar_type()
	{
		$sql = 'UPDATE ' . $this->table_prefix . "groups
			SET group_avatar_type = 'avatar.driver.upload'
			WHERE group_avatar_type = " . AVATAR_UPLOAD;
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . $this->table_prefix . "groups
			SET group_avatar_type = 'avatar.driver.remote'
			WHERE group_avatar_type = " . AVATAR_REMOTE;
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . $this->table_prefix . "groups
			SET group_avatar_type = 'avatar.driver.local'
			WHERE group_avatar_type = " . AVATAR_GALLERY;
		$this->db->sql_query($sql);
	}
}
