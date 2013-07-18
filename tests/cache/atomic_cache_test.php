<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

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
		if (!function_exists('pcntl_fork'))
		{
			self::markTestSkipped('platform does not support pctl_fork');
		}
	}

	static function run_race($key, $driver_class, $host, $port)
	{
		// fork process into parent and child
		$process_id = pcntl_fork();
		$is_child_process = $process_id == 0;
		// Set a begin time for the race
		$start_time = microtime() + 20000;
		if ($is_child_process)
		{
			// CHILD FORK
			$driver = new $driver_class($host, $port);
			// wait to start race
			usleep($start_time - microtime());
			// read & write data
			$driver->atomic_operation($key, function ($data) {
				return $data . 'child';
			});
			// Child ends execution, parent will continue with control flow
			exit;
		} else {
			// PARENT FORK
			$driver = new $driver_class($host, $port);
			// wait to start race
			usleep($start_time - microtime());
			// read & write data <-- should fail, re-read and re-write
			$driver->atomic_operation($key, function ($data) {
				// wait so that child always writes first
				usleep(10000);
				return $data . 'parent';
			});
			return $driver->get($key);
		}
	}

	public function test_redis_race_condition()
	{
		if (!extension_loaded('redis'))
		{
			self::markTestSkipped('redis extension is not loaded');
		}

		$config = phpbb_test_case_helpers::get_test_config();
		$host = isset($config['redis_host']) ? $config['redis_host'] : 'localhost';
		$port = isset($config['redis_port']) ? $config['redis_port'] : 6379;

		// Prefix with '_' because certain memory cache
		//        operations don't actually hit cache otherwise
		$key = uniqid('_');
		$result = self::run_race($key, 'phpbb_cache_driver_redis', $host, $port);
		$driver = new phpbb_cache_driver_redis($host, $port);
		$driver->redis->delete($key);

		$this->assertEquals('child' . 'parent', $result);
	}

	public function test_memcache_race_condition()
	{
		if (!extension_loaded('memcache'))
		{
			self::markTestSkipped('memcache extension is not loaded');
		}

		// Prefix with '_' because certain memory cache
		//        operations don't actually hit cache otherwise
		$key = uniqid('_');
		$driver = new phpbb_cache_driver_memcache();
		// Memcache needs key set before atomic_operations
		$driver->put($key, "");
		// Wait a bit to make sure that key is saved before continuing
		usleep(10000);

		$result = self::run_race($key, 'phpbb_cache_driver_memcache', null, null);
		$driver->memcache->delete($driver->key_prefix . $key);

		$this->assertEquals('child' . 'parent', $result);
	}
}
