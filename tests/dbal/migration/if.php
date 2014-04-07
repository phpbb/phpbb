<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_dbal_migration_if extends \phpbb\db\migration\migration
{
	function update_schema()
	{
		return array();
	}

	function update_data()
	{
		return array(
			array('if', array(
				true,
				array('custom', array(array(&$this, 'test_true'))),
			)),
			array('if', array(
				false,
				array('custom', array(array(&$this, 'test_false'))),
			)),
		);
	}

	function test_true()
	{
		global $migrator_test_if_true_failed;

		$migrator_test_if_true_failed = false;
	}

	function test_false()
	{
		global $migrator_test_if_false_failed;

		$migrator_test_if_false_failed = true;
	}
}
