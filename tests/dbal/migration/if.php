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
