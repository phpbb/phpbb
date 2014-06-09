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
