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

class phpbb_dbal_connect_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/../fixtures/empty.xml');
	}

	public function test_failing_connect()
	{
		global $phpbb_filesystem;

		$phpbb_filesystem = new phpbb\filesystem\filesystem();

		$config = $this->get_database_config();

		$db = new $config['dbms']();

		// Failure to connect results in a trigger_error call in dbal.
		// phpunit converts triggered errors to exceptions.
		// In particular there should be no fatals here.
		try
		{
			$db->sql_connect($config['dbhost'], 'phpbbogus', 'phpbbogus', 'phpbbogus', $config['dbport']);
			$this->assertFalse(true);
		}
		catch (Exception $e)
		{
			// should have a legitimate message
			$this->assertNotEmpty($e->getMessage());
		}
	}
}
