<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
}

