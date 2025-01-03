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

use phpbb\db\migration\container_aware_migration;

class storage_track_index extends container_aware_migration
{
	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v400\storage_track',
		];
	}

	public function update_schema()
	{
		return [
			'add_unique_index'	=> [
				$this->table_prefix . 'storage'		=> [
					'uidx_storage'	=> ['file_path', 'storage'],
				],
			]
		];
	}

	public function revert_schema()
	{
		return [
			'drop_keys' => [
				$this->table_prefix . 'storage' => [
					'uidx_storage',
				],
			],
		];
	}
}
