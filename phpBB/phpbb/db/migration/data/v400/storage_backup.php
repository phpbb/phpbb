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
use phpbb\storage\provider\local;

class storage_backup extends migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->tables['backups']);
	}

	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v400\dev',
		];
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'backups'	=> [
					'COLUMNS' => [
						'backup_id'			=> ['UINT', null, 'auto_increment'],
						'filename'			=> ['VCHAR', ''],
					],
					'PRIMARY_KEY'	=> 'backup_id',
				],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['storage\\backup\\provider', local::class]],
			['config.add', ['storage\\backup\\config\\path', 'store']],
		];
	}
}
