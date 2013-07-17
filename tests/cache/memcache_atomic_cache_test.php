<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/common_test_case.php';

class phpbb_cache_memcache_atomic_driver_test extends phpbb_database_test_case
{
	protected static $config;
	protected $driver;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	static public function setUpBeforeClass()
	{
		if (!extension_loaded('memcache'))
		{
			self::markTestSkipped('memcache extension is not loaded');
		}
	}

	public function test_race_condition()
	{
		$key = '_' . uniqid();
		// Memcache's Check-and-swap operations require a set key first
		$this->driver = new phpbb_cache_driver_memcache();
		$this->driver->put($key, "");
		// Wait a bit to make sure that key is saved before continuing
		usleep(10000);
		// fork
		$child_pid = pcntl_fork();
		if ($child_pid == 0)
		{
			// CHILD
			$this->driver = new phpbb_cache_driver_memcache();
			// fork 1 writes data
			$this->driver->atomic_operation($key, function ($d) {
				return $d . 'child';
			});
			exit;
		} else {
			// PARENT
			// fork 2 writes data <-- Should fail, re-read and re-write
			$this->driver = new phpbb_cache_driver_memcache();
			$this->driver->atomic_operation($key, function ($d) {
				// Wait so that fork 1 always writes first
				usleep(10000);
				return $d . 'parent';
			});
		}
		// Save result and clean so we don't pollute the cache
		$result = $this->driver->get($key);
		$this->driver->memcache->delete($key);

		$this->assertEquals('child' . 'parent', $result);
	}
}
