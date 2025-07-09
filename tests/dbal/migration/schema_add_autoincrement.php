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

class schema_add_autoincrement extends \phpbb\db\migration\migration
{
	function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'noid' => [
					'COLUMNS' => [
						'text' => ['VCHAR:50', ''],
					],
				],
			],

			'add_columns' => [
				$this->table_prefix . 'noid' => [
					'id' => ['UINT:3', null, 'auto_increment'],
				],
			],
		];
	}

	function revert_schema()
	{
		return [
			'drop_tables'	=> [
				$this->table_prefix . 'noid',
			],
		];
	}
}
