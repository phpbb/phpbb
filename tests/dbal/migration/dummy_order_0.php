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

class phpbb_dbal_migration_dummy_order_0 extends \phpbb\db\migration\migration
{
	function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'column_order_test1'	=> array(
					'foobar2'	=> array('BOOL', 0, 'after' => 'foobar1'),
				),
			),
		);
	}
}
