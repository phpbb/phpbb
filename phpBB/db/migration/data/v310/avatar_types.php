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

class avatar_types extends \phpbb\db\migration\migration
{
	/**
	* @var avatar type map
	*/
	protected $avatar_type_map = array(
		AVATAR_UPLOAD	=> 'avatar.driver.upload',
		AVATAR_REMOTE	=> 'avatar.driver.remote',
		AVATAR_GALLERY	=> 'avatar.driver.local',
	);

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
		foreach ($this->avatar_type_map as $old => $new)
		{
			$sql = 'UPDATE ' . $this->table_prefix . "users
				SET user_avatar_type = '$new'
				WHERE user_avatar_type = '$old'";
			$this->db->sql_query($sql);
		}
	}

	public function update_group_avatar_type()
	{
		foreach ($this->avatar_type_map as $old => $new)
		{
			$sql = 'UPDATE ' . $this->table_prefix . "groups
				SET group_avatar_type = '$new'
				WHERE group_avatar_type = '$old'";
			$this->db->sql_query($sql);
		}
	}
}
