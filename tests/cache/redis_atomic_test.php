<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/atomic_test_case.php';

class phpbb_redis_atomic_driver_test extends phpbb_atomic_test_case
{
	public function test_redis_race_condition()
	{
		$config = phpbb_test_case_helpers::get_test_config();
		$host = isset($config['redis_host']) ? $config['redis_host'] : 'localhost';
		$port = isset($config['redis_port']) ? $config['redis_port'] : 6379;

		$this->test_race_condition(
			'redis',
			'phpbb_cache_driver_redis',
			$host,
			$port
		);
	}
}
