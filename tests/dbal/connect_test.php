<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_dbal_connect_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/../fixtures/empty.xml');
	}

	public function test_failing_connect()
	{
		global $phpbb_root_path, $phpEx;

		$config = $this->get_database_config();

		require_once dirname(__FILE__) . '/../../phpBB/includes/db/' . $config['dbms'] . '.php';
		$dbal = 'dbal_' . $config['dbms'];
		$db = new $dbal();

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
