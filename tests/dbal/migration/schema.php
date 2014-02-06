<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
		);
	}
}
