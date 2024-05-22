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

namespace phpbb\db\migration\data\v33x;

use phpbb\db\migration\migration;

class add_user_last_active extends migration
{
	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v33x\v3311',
		];
	}

	public function update_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'users'	=> [
					'user_last_active'		=> ['TIMESTAMP', 0, 'after' => 'user_lastvisit'],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'users'	=> ['user_last_active'],
			],
		];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'set_user_last_active']]],
		];
	}

	public function set_user_last_active($start = 0)
	{
		// Get maximum user id from database
		$sql = "SELECT MAX(user_id) AS max_user_id
			FROM {$this->table_prefix}users";
		$result = $this->db->sql_query($sql);
		$max_id = (int) $this->db->sql_fetchfield('max_user_id');
		$this->db->sql_freeresult($result);

		if ($start > $max_id)
		{
			return;
		}

		// Keep setting user_last_active time
		$next_start = $start + 10000;

		$sql = 'UPDATE ' . $this->table_prefix . 'users
			SET user_last_active = user_lastvisit
			WHERE user_id > ' . (int) $start . '
				AND user_id <= ' . (int) ($next_start);
		$this->db->sql_query($sql);

		return $next_start;
	}
}
