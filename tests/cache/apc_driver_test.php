<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

// Important: apc.enable_cli=1 must be in php.ini.
// http://forums.devshed.com/php-development-5/apc-problem-561290.html
// http://php.net/manual/en/apc.configuration.php

require_once dirname(__FILE__) . '/common_test_case.php';

class phpbb_cache_apc_driver_test extends phpbb_cache_common_test_case
{
	protected static $config;
	protected $driver;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	static public function setUpBeforeClass()
	{
		if (!extension_loaded('apc'))
		{
			self::markTestSkipped('apc extension is not loaded');
		}
	}

	protected function setUp()
	{
		parent::setUp();

		$this->driver = new phpbb_cache_driver_apc;
		$this->driver->purge();
	}

	public function test_cache_sql()
	{
		global $db, $cache;
		$db = $this->new_dbal();
		$cache = new phpbb_cache_service($this->driver);

		$sql = "SELECT * FROM phpbb_config
			WHERE config_name = 'foo'";

		$key = $this->driver->key_prefix . 'sql_' . md5(preg_replace('/[\n\r\s\t]+/', ' ', $sql));
		$this->assertFalse(apc_fetch($key));

		$result = $db->sql_query($sql, 300);
		$first_result = $db->sql_fetchrow($result);
		$expected = array('config_name' => 'foo', 'config_value' => '23', 'is_dynamic' => 0);
		$this->assertEquals($expected, $first_result);

		$this->assertTrue((bool) apc_fetch($key));

		$sql = 'DELETE FROM phpbb_config';
		$result = $db->sql_query($sql);

		$sql = "SELECT * FROM phpbb_config
			WHERE config_name = 'foo'";
		$result = $db->sql_query($sql, 300);

		$this->assertEquals($expected, $db->sql_fetchrow($result));

		$sql = "SELECT * FROM phpbb_config
			WHERE config_name = 'foo'";
		$result = $db->sql_query($sql);

		$no_cache_result = $db->sql_fetchrow($result);
		$this->assertSame(false, $no_cache_result);

		$db->sql_close();
	}
}
