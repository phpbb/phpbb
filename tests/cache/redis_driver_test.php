<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/common_test_case.php';

class phpbb_cache_redis_driver_test extends phpbb_cache_common_test_case
{
	protected static $config;
	protected $driver;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	static public function setUpBeforeClass()
	{
		if (!extension_loaded('redis'))
		{
			self::markTestSkipped('redis extension is not loaded');
		}

		$config = phpbb_test_case_helpers::get_test_config();
		if (isset($config['redis_host']) || isset($config['redis_port']))
		{
			$host = isset($config['redis_host']) ? $config['redis_host'] : 'localhost';
			$port = isset($config['redis_port']) ? $config['redis_port'] : 6379;
			self::$config = array('host' => $host, 'port' => $port);
		}
		else
		{
			self::markTestSkipped('Test redis host/port is not specified');
		}
	}

	protected function setUp()
	{
		parent::setUp();

		$this->driver = new phpbb_cache_driver_redis(self::$config['host'], self::$config['port']);
		$this->driver->purge();
	}
}
