<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/common_test_case.php';

class phpbb_cache_atomic_driver_test extends phpbb_database_test_case
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
		} else if (!function_exists('pcntl_fork'))
		{
			self::markTestSkipped('platform does not support pctl_fork');
		}

		$config = phpbb_test_case_helpers::get_test_config();
		$host = isset($config['redis_host']) ? $config['redis_host'] : 'localhost';
		$port = isset($config['redis_port']) ? $config['redis_port'] : 6379;
		self::$config = array('host' => $host, 'port' => $port);
	}

	public function test_race_condition()
	{
		$key = uniqid();
		// Insert data into cache
		// fork
		$child_pid = pcntl_fork();
		if ($child_pid == 0)
		{
			// CHILD
			$this->driver = new phpbb_cache_driver_redis(self::$config['host'], self::$config['port']);
			// fork 1 writes data
			$this->driver->atomic_operation($key, function ($d) {
				return $d . 'child';
			});
			exit;
		} else {
			// PARENT
			// fork 2 writes data <-- Should fail, re-read and re-write
			$this->driver = new phpbb_cache_driver_redis(self::$config['host'], self::$config['port']);
			$this->driver->atomic_operation($key, function ($d) {
				// Wait so that fork 1 always writes first
				usleep(1000);
				return $d . 'parent';
			});
		}
		$this->assertEquals('child' . 'parent', $this->driver->redis->get($key));
		$this->driver->redis->delete($key);
	}
}
