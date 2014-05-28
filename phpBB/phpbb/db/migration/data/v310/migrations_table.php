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

namespace phpbb\db\migration\data\v310;

class migrations_table extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'migrations');
	}

	public function update_schema()
	{
		return array(
			'add_tables'		=> array(
				$this->table_prefix . 'migrations'	=> array(
					'COLUMNS'		=> array(
						'migration_name'			=> array('VCHAR', ''),
						'migration_depends_on'		=> array('TEXT', ''),
						'migration_schema_done'		=> array('BOOL', 0),
						'migration_data_done'		=> array('BOOL', 0),
						'migration_data_state'		=> array('TEXT', ''),
						'migration_start_time'		=> array('TIMESTAMP', 0),
						'migration_end_time'		=> array('TIMESTAMP', 0),
					),
					'PRIMARY_KEY'	=> 'migration_name',
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'		=> array(
				$this->table_prefix . 'migrations',
			),
		);
	}
}
