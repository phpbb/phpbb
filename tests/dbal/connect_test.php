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
		return $this->createXMLDataSet(dirname(__FILE__) . '/../fixtures/empty.xml');
	}

	public function test_failing_connect()
	{
		global $phpbb_filesystem;

		$phpbb_filesystem = new phpbb\filesystem\filesystem();

		$config = $this->get_database_config();

		if (strpos($config['dbms'], 'sqlite'))
		{
			$this->markTestSkipped('SQLite connection cannot fail.');
		}

		$db = new $config['dbms']();

		$this->assertFalse($db->sql_connect($config['dbhost'], 'phpbbogus', 'phpbbogus', 'phpbbogus', $config['dbport']));
	}
}
