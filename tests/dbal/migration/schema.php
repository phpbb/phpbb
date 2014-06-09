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

class phpbb_dbal_migration_schema extends \phpbb\db\migration\migration
{
	function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'config' => array(
					'test_column1' => array('BOOL', 1),
				),
			),
			'add_tables' => array(
				$this->table_prefix . 'foobar' => array(
					'COLUMNS' => array(
						'module_id' => array('UINT:3', NULL, 'auto_increment'),
					),
					'PRIMARY_KEY'	=> 'module_id',
				),
			),
		);
	}

	function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'config' => array(
					'test_column1',
				),
			),
			'drop_tables'	=> array(
				$this->table_prefix . 'foobar',
			),
		);
	}
}
