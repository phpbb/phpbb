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

class phpbb_dbal_migration_dummy_primary_keys extends \phpbb\db\migration\migration
{
	function update_schema()
	{
		return [
			'add_tables'	=> [
				$this->table_prefix . 'dummy_primary_keys_test'	=> [
					'COLUMNS'			=> [
						'old_pk'			=> ['UINT', null],
						'new_pk'			=> ['UINT', null],
						'foo'				=> ['UINT', null],
					],
					'PRIMARY_KEY'		=> 'old_pk',
				],
				$this->table_prefix . 'dummy_primary_keys_composite'	=> [
					'COLUMNS'			=> [
						'pk1'				=> ['UINT', null],
						'pk2'				=> ['UINT', null],
						'foo'				=> ['UINT', null],
					],
				],
			],
			'drop_primary_keys'	=> [
				$this->table_prefix . 'dummy_primary_keys_test'	=> ['PRIMARY_KEY'],
			],
			'add_primary_keys'	=> [
				$this->table_prefix . 'dummy_primary_keys_test'		=> ['new_pk'],
				$this->table_prefix . 'dummy_primary_keys_composite'	=> ['pk1', 'pk2'],
			],
		];
	}
}
