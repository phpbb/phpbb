<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_dbal_migration_dummy_order extends \phpbb\db\migration\migration
{
	function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'column_order_test'	=> array(
					'COLUMNS'			=> array(
						'foobar1'			=> array('BOOL', 0),
						'foobar3'			=> array('BOOL', 0),
					),
					'PRIMARY_KEY'		=> array('foobar1'),
				),
			),
			'add_columns'	=> array(
				$this->table_prefix . 'column_order_test'	=> array(
					'foobar2'	=> array('BOOL', 0, 'after' => 'foobar1'),
				),
			),
		);
	}
}
