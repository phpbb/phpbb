<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_security_hash_test extends phpbb_test_case
{
	public function test_check_hash_with_phpass()
	{
		$this->assertTrue(phpbb_check_hash('test', '$H$9isfrtKXWqrz8PvztXlL3.daw4U0zI1'));
		$this->assertTrue(phpbb_check_hash('test', '$P$9isfrtKXWqrz8PvztXlL3.daw4U0zI1'));
		$this->assertFalse(phpbb_check_hash('foo', '$H$9isfrtKXWqrz8PvztXlL3.daw4U0zI1'));
	}

	public function test_check_hash_with_large_input()
	{
		// 16 MB password, should be rejected quite fast
		$start_time = time();
		$this->assertFalse(phpbb_check_hash(str_repeat('a', 1024 * 1024 * 16), '$H$9isfrtKXWqrz8PvztXlL3.daw4U0zI1'));
		$this->assertLessThanOrEqual(5, time() - $start_time);
	}
}

