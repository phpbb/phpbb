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

class guest_session_config extends migration
{
	public function effectively_installed(): bool
	{
		return isset($this->config['session_guest_length']);
	}

	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v33x\v3316',
		];
	}

	public function update_schema(): array
	{
		return [
			'add_index' => [
				$this->table_prefix . 'sessions' => [
					'session_user_ip' => ['session_user_id', 'session_ip', 'session_time'],
				],
			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'drop_keys' => [
				$this->table_prefix . 'sessions' => [
					'session_user_ip',
				],
			],
		];
	}

	public function update_data(): array
	{
		return [
			['config.add', ['session_guest_gc', 300]],
			['config.add', ['session_guest_length', 300]],
			['config.add', ['session_guest_last_gc', 0]],
		];
	}
}
