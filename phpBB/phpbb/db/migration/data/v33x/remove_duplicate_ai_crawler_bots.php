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

class remove_duplicate_ai_crawler_bots extends migration
{
	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v33x\v3319',
		];
	}

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'remove_duplicate_bots']]],
		];
	}

	/**
	 * Remove AI crawler bots added by the ai_crawler_bots migration whose
	 * agent string was already configured on the board by an older bot
	 */
	public function remove_duplicate_bots(): void
	{
		$sql = 'SELECT group_id
			FROM ' . $this->table_prefix . 'groups
			WHERE ' . $this->db->sql_build_array('SELECT', ['group_name' => 'AI_CRAWLERS']);
		$result = $this->db->sql_query($sql);
		$group_id = (int) $this->db->sql_fetchfield('group_id');
		$this->db->sql_freeresult($result);

		if (!$group_id)
		{
			return;
		}

		$sql_ary = [
			'SELECT'	=> 'b.bot_id, b.user_id, b.bot_agent, u.group_id',
			'FROM'		=> [$this->table_prefix . 'bots' => 'b'],
			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [$this->table_prefix . 'users' => 'u'],
					'ON'	=> 'u.user_id = b.user_id',
				],
			],
			'ORDER_BY'	=> 'b.bot_id ASC',
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);

		// Bots are matched case insensitively against the user agent in
		// phpbb\session, so the oldest bot per agent string is kept
		$known_agents = [];
		$bot_ids = $user_ids = [];

		while ($row = $this->db->sql_fetchrow($result))
		{
			$bot_agent = strtolower($row['bot_agent']);

			if ($bot_agent !== '' && isset($known_agents[$bot_agent]) && (int) $row['group_id'] === $group_id)
			{
				$bot_ids[] = (int) $row['bot_id'];
				$user_ids[] = (int) $row['user_id'];
				continue;
			}

			$known_agents[$bot_agent] = true;
		}
		$this->db->sql_freeresult($result);

		if (!count($bot_ids))
		{
			return;
		}

		$this->db->sql_transaction('begin');

		$sql = 'DELETE FROM ' . $this->table_prefix . 'bots
			WHERE ' . $this->db->sql_in_set('bot_id', $bot_ids);
		$this->db->sql_query($sql);

		foreach (['users', 'user_group'] as $table)
		{
			$sql = 'DELETE FROM ' . $this->table_prefix . $table . '
				WHERE ' . $this->db->sql_in_set('user_id', $user_ids);
			$this->db->sql_query($sql);
		}

		$this->db->sql_transaction('commit');
	}
}
