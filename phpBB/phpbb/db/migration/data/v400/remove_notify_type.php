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

class remove_notify_type extends migration
{
	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\remove_jabber',
			'\phpbb\db\migration\data\v400\dev',
			'\phpbb\db\migration\data\v30x\release_3_0_0',
		];
	}

	public function update_schema(): array
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'users' => [
					'user_notify_type',
				],
			]
		];
	}

	public function revert_schema(): array
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'users' => [
					'user_notify_type' => ['TINT:4', 0],
				],
			]
		];
	}
}
