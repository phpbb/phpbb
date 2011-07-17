<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_wrapper_mt_rand_test extends phpbb_test_case
{
	public function test_max_equals_min()
	{
		$result = phpbb_mt_rand(42, 42);
		$this->assertEquals(42, $result);
	}

	public function test_max_equals_min_negative()
	{
		$result = phpbb_mt_rand(-42, -42);
		$this->assertEquals(-42, $result);
	}

	public function test_max_greater_min()
	{
		$result = phpbb_mt_rand(3, 4);
		$this->assertGreaterThanOrEqual(3, $result);
		$this->assertLessThanOrEqual(4, $result);
	}

	public function test_min_greater_max()
	{
		$result = phpbb_mt_rand(4, 3);
		$this->assertGreaterThanOrEqual(3, $result);
		$this->assertLessThanOrEqual(4, $result);
	}

	public function test_min_greater_max_negative()
	{
		$result = phpbb_mt_rand(-3, -4);
		$this->assertGreaterThanOrEqual(-4, $result);
		$this->assertLessThanOrEqual(-3, $result);
	}
}
