<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_dbal_migration_recall extends \phpbb\db\migration\migration
{
	function update_schema()
	{
		return array();
	}

	function update_data()
	{
		return array(
			array('custom', array(array(&$this, 'test_call'))),
		);
	}

	// This function should be called 10 times
	function test_call($input)
	{
		global $migrator_test_call_input;

		$migrator_test_call_input = (int) $input;

		if ($migrator_test_call_input < 10)
		{
			return ($migrator_test_call_input + 1);
		}

		return;
	}
}
