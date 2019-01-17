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

namespace phpbb\db\migration\data\v330;

class storage_backup extends \phpbb\db\migration\migration
{
	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'backups'	=> array(
					'COLUMNS' => array(
						'backup_id'			=> array('UINT', null, 'auto_increment'),
						'filename'			=> array('VCHAR', ''),
					),
					'PRIMARY_KEY'	=> 'backup_id',
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('storage\\backup\\provider', \phpbb\storage\provider\local::class)),
			array('config.add', array('storage\\backup\\config\\path', 'store')),
		);
	}
}
