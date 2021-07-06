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

namespace phpbb\db\migration\data\v400;

use phpbb\db\migration\container_aware_migration;

class remove_remote_avatar extends container_aware_migration
{
	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v400\dev'];
	}

	public function update_data()
	{
		return [
			['config.remove', ['allow_avatar_remote']],
			['config.remove', ['allow_avatar_remote_upload']],
			['custom', [[$this, 'remove_remote_avatars']]],
		];
	}

	public function remove_remote_avatars(): void
	{
		// Remove remote avatar from users and groups
		$sql = 'UPDATE ' . $this->table_prefix . "users
			SET user_avatar = '',
				user_avatar_type = ''
			WHERE user_avatar_type = 'avatar.driver.remote'";

		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . $this->table_prefix . "groups
			SET group_avatar = '',
				group_avatar_type = ''
			WHERE group_avatar_type = 'avatar.driver.remote'";
		$this->db->sql_query($sql);
	}
}
