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

class phpbb_dbal_migration_recall_params extends \phpbb\db\migration\migration
{
	function update_schema()
	{
		return array();
	}

	function update_data()
	{
		return array(
			array('custom', array(array($this, 'test_call'), array(5))),
		);
	}

	// This function should be called 5 times
	function test_call($times, $input)
	{
		global $migrator_test_call_input;

		$migrator_test_call_input = (int) $input;

		if ($migrator_test_call_input < $times)
		{
			return ($migrator_test_call_input + 1);
		}

		return;
	}
}
