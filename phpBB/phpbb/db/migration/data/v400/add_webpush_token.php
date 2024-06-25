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

class add_webpush_token extends migration
{
	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\add_webpush',
		];
	}

	public function effectively_installed(): bool
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'notification_push', 'push_token');
	}

	public function update_schema(): array
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'notification_push' => [
					'push_token'	=> ['VCHAR', ''],
				],
			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'notification_push' => [
					'push_token',
				],
			],
		];
	}
}
