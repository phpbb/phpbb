<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
