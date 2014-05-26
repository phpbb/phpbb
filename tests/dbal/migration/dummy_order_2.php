<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_dbal_migration_dummy_order_2 extends \phpbb\db\migration\migration
{
	function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'column_order_test1'	=> array(
					'foobar5'	=> array('BOOL', 0, 'after' => 'non-existing'),
				),
			),
		);
	}
}
