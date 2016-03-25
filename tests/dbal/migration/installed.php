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

class phpbb_dbal_migration_installed extends \phpbb\db\migration\migration
{
	function effectively_installed()
	{
		return true;
	}

	function update_data()
	{
		return array(
			array('custom', array(array(&$this, 'test'))),
		);
	}

	function test()
	{
		global $migrator_test_installed_failed;

		$migrator_test_installed_failed = true;
	}
}
