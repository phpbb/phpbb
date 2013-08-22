<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

abstract class phpbb_atomic_test_case extends PHPUnit_Framework_TestCase
{

	protected function run_race($key, $driver_class, $host, $port)
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

	function test_race_condition($driver_name, $driver_class_name, $host=null, $port=null)
	{
		if (!extension_loaded($driver_name))
		{
			self::markTestSkipped("$driver_name extension is not loaded");
		}

		// Prefix with '_' because certain memory cache
		//        operations don't actually hit cache otherwise
		$key = uniqid('_');
		$driver = new $driver_class_name($host, $port);
		// Atomic operation needs key written first
		$driver->put($key, '_');
		$this->run_race($key, $driver_class_name, $host, $port);
		$result = $driver->get($key);
		$driver->_delete($key);
		$this->assertEquals('first' . 'second', $result);
	}
}
