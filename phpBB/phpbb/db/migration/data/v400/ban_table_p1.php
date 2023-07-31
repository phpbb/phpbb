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

class ban_table_p1 extends migration
{
	public static function depends_on(): array
	{
		return ['\phpbb\db\migration\data\v320\default_data_type_ids'];
	}

	public function update_schema(): array
	{
		return [
			'add_tables'	=> [
				$this->table_prefix . 'bans'	=> [
					'COLUMNS'		=> [
						'ban_id'				=> ['ULINT', null, 'auto_increment'],
						'ban_userid'			=> ['ULINT', 0],
						'ban_mode'				=> ['VCHAR', ''],
						'ban_item'				=> ['STEXT_UNI', ''],
						'ban_start'				=> ['TIMESTAMP', 0],
						'ban_end'				=> ['TIMESTAMP', 0],
						'ban_reason'			=> ['VCHAR_UNI', ''],
						'ban_reason_display'	=> ['VCHAR_UNI', ''],
					],
					'PRIMARY_KEY'	=> 'ban_id',
					'KEYS'			=> [
						'ban_userid'	=> ['INDEX', 'ban_userid'],
						'ban_end'	=> ['INDEX', 'ban_end'],
					],
				],
			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'drop_tables'	=> [
				$this->table_prefix . 'bans',
			],
		];
	}

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'old_to_new']]],
		];
	}

	public function revert_data(): array
	{
		return [
			['custom', [[$this, 'new_to_old']]],
		];
	}

	public function old_to_new($start)
	{
		$start = (int) $start;
		$limit = 500;
		$processed_rows = 0;

		$sql = 'SELECT *
			FROM ' . $this->table_prefix . 'banlist';
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		$bans = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$processed_rows++;

			if ($row['ban_exclude'])
			{
				continue;
			}

			$row['ban_userid'] = (int) $row['ban_userid'];
			$item = $mode = '';
			if ($row['ban_ip'] !== '')
			{
				$mode = 'ip';
				$item = $row['ban_ip'];
			}
			else if ($row['ban_email'] !== '')
			{
				$mode = 'email';
				$item = $row['ban_email'];
			}
			else if ($row['ban_userid'] !== 0)
			{
				$mode = 'user';
				$item = $row['ban_userid'];
			}

			if ($mode === '' || $item === '')
			{
				continue;
			}

			$bans[] = [
				'ban_mode'				=> $mode,
				'ban_userid'			=> $row['ban_userid'],
				'ban_item'				=> $item,
				'ban_start'				=> $row['ban_start'],
				'ban_end'				=> $row['ban_end'],
				'ban_reason'			=> $row['ban_reason'],
				'ban_reason_display'	=> $row['ban_give_reason'],
			];
		}
		$this->db->sql_freeresult($result);

		if ($processed_rows > 0)
		{
			$this->db->sql_multi_insert($this->table_prefix . 'bans', $bans);
		}
		else if ($processed_rows < $limit)
		{
			return;
		}

		return $limit + $start;
	}

	public function new_to_old($start)
	{
		$start = (int) $start;
		$limit = 500;
		$processed_rows = 0;

		$sql = 'SELECT *
			FROM ' . $this->table_prefix . 'bans';
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		$bans = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$processed_rows++;

			$bans[] = [
				'ban_userid'		=> (int) $row['ban_userid'],
				'ban_ip'			=> ($row['ban_mode'] === 'ip') ? $row['ban_item'] : '',
				'ban_email'			=> ($row['ban_mode'] === 'email') ? $row['ban_item'] : '',
				'ban_start'			=> $row['ban_start'],
				'ban_end'			=> $row['ban_end'],
				'ban_exclude'		=> false,
				'ban_reason'		=> $row['ban_reason'],
				'ban_give_reason'	=> $row['ban_reason_display'],
			];
		}
		$this->db->sql_freeresult($result);

		if ($processed_rows > 0)
		{
			$this->db->sql_multi_insert($this->table_prefix . 'banlist', $bans);
		}
		else if ($processed_rows < $limit)
		{
			return;
		}

		return $limit + $start;
	}
}
