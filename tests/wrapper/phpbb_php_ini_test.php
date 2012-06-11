<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/phpbb_php_ini_fake.php';

class phpbb_wrapper_phpbb_php_ini_test extends phpbb_test_case
{
	protected $php_ini;

	public function setUp()
	{
		$this->php_ini = new phpbb_php_ini_fake;
	}

	public function test_get_string()
	{
		$this->assertSame('phpbb', $this->php_ini->get_string(' phpbb '));
	}

	public function test_get_bool()
	{
		$this->assertSame(true, $this->php_ini->get_bool('ON'));
		$this->assertSame(true, $this->php_ini->get_bool('on'));
		$this->assertSame(true, $this->php_ini->get_bool('1'));

		$this->assertSame(false, $this->php_ini->get_bool('OFF'));
		$this->assertSame(false, $this->php_ini->get_bool('off'));
		$this->assertSame(false, $this->php_ini->get_bool('0'));
		$this->assertSame(false, $this->php_ini->get_bool(''));
	}

	public function test_get_int()
	{
		$this->assertSame(1234, $this->php_ini->get_int('1234'));
		$this->assertSame(-12345, $this->php_ini->get_int('-12345'));
		$this->assertSame(false, $this->php_ini->get_int('phpBB'));
	}

	public function test_get_float()
	{
		$this->assertSame(1234.0, $this->php_ini->get_float('1234'));
		$this->assertSame(-12345.0, $this->php_ini->get_float('-12345'));
		$this->assertSame(false, $this->php_ini->get_float('phpBB'));
	}

	public function test_get_bytes()
	{
		$this->assertSame(false, $this->php_ini->get_bytes('phpBB'));
		$this->assertSame(false, $this->php_ini->get_bytes('k'));
		$this->assertSame(false, $this->php_ini->get_bytes('-k'));
		$this->assertSame(false, $this->php_ini->get_bytes('M'));
		$this->assertSame(false, $this->php_ini->get_bytes('-M'));
		$this->assertEquals(32 * pow(2, 20), $this->php_ini->get_bytes('32m'));
		$this->assertEquals(- 32 * pow(2, 20), $this->php_ini->get_bytes('-32m'));
		$this->assertEquals(8 * pow(2, 30), $this->php_ini->get_bytes('8G'));
		$this->assertEquals(- 8 * pow(2, 30), $this->php_ini->get_bytes('-8G'));
		$this->assertEquals(1234, $this->php_ini->get_bytes('1234'));
		$this->assertEquals(-12345, $this->php_ini->get_bytes('-12345'));
	}
}
