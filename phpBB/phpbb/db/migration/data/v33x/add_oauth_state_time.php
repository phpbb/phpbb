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

class add_oauth_state_time extends migration
{
	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v33x\v3316',
		];
	}

	public function update_schema(): array
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'oauth_states' => [
					'state_time'	=> ['TIMESTAMP', 0],
				],
			],
			'add_index' => [
				$this->table_prefix . 'oauth_states' => [
					'state_time' => ['state_time'],
				],
			],
		];
	}

	public function update_data(): array
	{
		return [
			['config.add', ['oauth_states_last_gc', 0]],
		];
	}

	public function revert_data(): array
	{
		return [
			['config.remove', ['oauth_states_last_gc']],
		];
	}

	public function revert_schema(): array
	{
		return [
			'drop_keys' => [
				$this->table_prefix . 'oauth_states' => [
					'state_time',
				],
			],
			'drop_columns' => [
				$this->table_prefix . 'oauth_states' => [
					'state_time',
				],
			],
		];
	}
}
