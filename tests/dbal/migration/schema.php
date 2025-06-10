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
				$this->table_prefix . 'foobar' => [
					'COLUMNS' => [
						'module_id'			=> ['UINT:3', NULL, 'auto_increment'],
						'user_id'			=> ['ULINT', 0],
						'endpoint'			=> ['TEXT', ''],
						'expiration_time'	=> ['TIMESTAMP', 0],
						'p256dh'			=> ['VCHAR', ''],
						'auth'				=> ['VCHAR:100', ''],
					],
					'PRIMARY_KEY'	=> 'module_id',
					'KEYS'			=> [
						'i_simple'	=> ['INDEX', ['user_id', 'endpoint:191']],
						'i_uniq'	=> ['UNIQUE', ['expiration_time', 'p256dh(100)']],
						'i_auth'	=> ['INDEX', 'auth'],
					],
				],
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
