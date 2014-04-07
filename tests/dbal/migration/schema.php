<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
