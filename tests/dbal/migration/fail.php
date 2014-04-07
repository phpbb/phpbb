<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_dbal_migration_fail extends \phpbb\db\migration\migration
{
	function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'config' => array(
					'test_column' => array('BOOL', 1),
				),
			),
		);
	}

	function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'config' => array(
					'test_column',
				),
			),
		);
	}

	function update_data()
	{
		return array(
			array('config.add', array('foobar3', true)),
			array('config.update', array('does_not_exist', true)),
		);
	}
}
