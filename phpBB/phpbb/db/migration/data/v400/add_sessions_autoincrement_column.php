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

class add_sessions_autoincrement_column extends migration
{
	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\rename_duplicated_index_names',
		];
	}

	public function update_schema(): array
	{
		return [
			'drop_primary_keys' => [
				$this->table_prefix . 'sessions',
			],
			'add_columns' => [
				$this->table_prefix . 'sessions' => [
					'id' => ['BINT', null, 'auto_increment'],
				],
			],
			'add_index'	=> [
				$this->table_prefix . 'sessions' => [
					'session_id'	=> ['session_id'],
				],
			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'sessions' => ['id'],
			],
			'drop_keys'	=> [
				$this->table_prefix . 'sessions' => [
					'session_id',
				],
			],
			'add_primary_keys' => [
				$this->table_prefix . 'sessions' => ['session_id'],
			],
		];
	}
}
