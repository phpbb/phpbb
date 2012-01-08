<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../mock/phpbb_php_ini.php';

class phpbb_wrapper_phpbb_php_ini_test extends phpbb_test_case
{
	protected $reader;

	public function setUp()
	{
		$this->reader = new phpbb_mock_phpbb_php_ini;
	}

	public function test_get_string()
	{
		$this->assertEquals('phpbb', $this->reader->get_string(' phpBB '));
	}

	public function test_get_bool()
	{
		$this->assertEquals(true, $this->reader->get_bool('ON'));
		$this->assertEquals(true, $this->reader->get_bool('on'));
		$this->assertEquals(true, $this->reader->get_bool('1'));

		$this->assertEquals(false, $this->reader->get_bool('OFF'));
		$this->assertEquals(false, $this->reader->get_bool('off'));
		$this->assertEquals(false, $this->reader->get_bool('0'));
		$this->assertEquals(false, $this->reader->get_bool(''));
	}

	public function test_get_int()
	{
		$this->assertEquals(1234, $this->reader->get_int('1234'));
		$this->assertEquals(false, $this->reader->get_int('phpBB'));
	}

	public function test_get_float()
	{
		$this->assertEquals(1234.0, $this->reader->get_float('1234'));
		$this->assertEquals(false, $this->reader->get_float('phpBB'));
	}

	public function test_get_bytes()
	{
		$this->assertEquals(false, $this->reader->get_bytes('phpBB'));
		$this->assertEquals(false, $this->reader->get_bytes('M'));
		$this->assertEquals(32 * pow(2, 20), $this->reader->get_bytes('32M'));
		$this->assertEquals(8 * pow(2, 30), $this->reader->get_bytes('8G'));
		$this->assertEquals(1234, $this->reader->get_bytes('1234'));
	}
}
