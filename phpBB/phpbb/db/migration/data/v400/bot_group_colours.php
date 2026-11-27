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

use phpbb\db\migration\migration;

class bot_group_colours extends migration
{
	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\v400a2',
		];
	}

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'update_bot_colours']]],
		];
	}

	public function update_bot_colours(): void
	{
		$colour_map = [
			'BOTS' => ['old' => '9E8DA7', 'new' => '6B5876'],
			'AI_CRAWLERS' => ['old' => '7D9EAE', 'new' => '486878'],
		];

		foreach ($colour_map as $group_name => $colours)
		{
			$sql = 'UPDATE ' . $this->table_prefix . 'groups
				SET group_colour = \'' . $this->db->sql_escape($colours['new']) . '\'
				WHERE group_name = \'' . $this->db->sql_escape($group_name) . '\'
					AND group_colour = \'' . $this->db->sql_escape($colours['old']) . '\'';
			$this->db->sql_query($sql);

			$sql = 'UPDATE ' . $this->table_prefix . 'users
				SET user_colour = \'' . $this->db->sql_escape($colours['new']) . '\'
				WHERE user_type = ' . USER_IGNORE . '
					AND user_colour = \'' . $this->db->sql_escape($colours['old']) . '\'';
			$this->db->sql_query($sql);
		}
	}
}
