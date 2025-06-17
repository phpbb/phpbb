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

class phpbb_dbal_migration_schema_index extends \phpbb\db\migration\migration
{
	function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'foobar1' => [
					'COLUMNS' => [
						'user_id' => ['UINT', 0],
						'username' => ['VCHAR:50', 0],
					],
					'KEYS'	=> [
						'tstidx_user_id' => ['UNIQUE', 'user_id'],
						'tstidx_username' => ['INDEX', 'username'],
					],
				],
				$this->table_prefix . 'foobar2' => [
					'COLUMNS' => [
						'ban_userid'		=> ['ULINT', 0],
						'ban_ip'			=> ['VCHAR:40', ''],
						'ban_reason'		=> ['VCHAR:100', ''],
					],
					'KEYS'	=> [
						'tstidx_ban_userid' => ['UNIQUE', 'ban_userid'],
						'tstidxban_data' => ['INDEX', ['ban_ip', 'ban_reason']],
					],
				],
			],

			'rename_index' => [
				$this->table_prefix . 'foobar1' => [
					'tstidx_user_id' => 'fbr1_user_id',
					'tstidx_username' => 'fbr1_username',
				],
				$this->table_prefix . 'foobar2' => [
					'tstidx_ban_userid' => 'fbr2_ban_userid',
					'tstidxban_data' => 'fbr2_ban_data',
				],
			],
		];
	}

	function revert_schema()
	{
		return [
			'drop_tables'	=> [
				$this->table_prefix . 'foobar1',
				$this->table_prefix . 'foobar2',
			],
		];
	}
}
