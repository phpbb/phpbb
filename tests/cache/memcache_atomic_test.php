<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/atomic_test_case.php';

class phpbb_memcache_atomic_driver_test extends phpbb_atomic_test_case
{
	public function test_memcache_race_condition()
	{
		$this->test_race_condition(
			'memcache',
			'phpbb_cache_driver_memcache'
		);
	}
}
