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

	function run_race($key, $driver_class, $host, $port)
	{
		$driver1 = new $driver_class($host, $port);
		$driver2 = new $driver_class($host, $port);
		$race_condition_data_written = false;

		$driver1->atomic_operation($key, function ($data) use ($key, $driver2, &$race_condition_data_written) {
			// Set key inside atomic_operation so that a race condition occurs
			//  (change happened while atomic_operation was occurring)
			//  (only write once otherwise we'll never have a chance to do the atomic_operation)
			if(!$race_condition_data_written)
			{
				$driver2->_write($key, 'first');
				$race_condition_data_written = true;
			}

			return $data . 'second';
		});
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
		$driver = new phpbb_cache_driver_redis($host, $port);
		// Atomic operation needs key written first
		$driver->put($key, '_');
		$this->run_race($key, 'phpbb_cache_driver_redis', $host, $port);
		$result = $driver->redis->get($key);
		$driver->redis->delete($key);
		$this->assertEquals('first' . 'second', $result);
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
		// Atomic operation needs key written first
		$driver->put($key, '_');
		$this->run_race($key, 'phpbb_cache_driver_memcache', null, null);
		$result = $driver->memcache->get($key);
		$driver->memcache->delete($key);
		$this->assertEquals('first' . 'second', $result);
	}
}
