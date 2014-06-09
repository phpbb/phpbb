<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

require_once dirname(__FILE__) . '/phpbb_php_ini_fake.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_wrapper_phpbb_php_ini_test extends phpbb_test_case
{
	protected $php_ini;

	public function setUp()
	{
		$this->php_ini = new phpbb_php_ini_fake;
	}

	public function test_get_string()
	{
		$this->assertSame(false, $this->php_ini->get_string(false));
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

	public function test_get_bytes_invalid()
	{
		$this->assertSame(false, $this->php_ini->get_bytes(false));
		$this->assertSame(false, $this->php_ini->get_bytes('phpBB'));
		$this->assertSame(false, $this->php_ini->get_bytes('k'));
		$this->assertSame(false, $this->php_ini->get_bytes('-k'));
		$this->assertSame(false, $this->php_ini->get_bytes('M'));
		$this->assertSame(false, $this->php_ini->get_bytes('-M'));
	}

	/**
	* @dataProvider get_bytes_data
	*/
	public function test_get_bytes($expected, $value)
	{
		$actual = $this->php_ini->get_bytes($value);

		$this->assertTrue(is_float($actual) || is_int($actual));
		$this->assertEquals($expected, $actual);
	}

	static public function get_bytes_data()
	{
		return array(
			array(32 * pow(2, 20),		'32m'),
			array(- 32 * pow(2, 20),	'-32m'),
			array(8 * pow(2, 30),		'8G'),
			array(- 8 * pow(2, 30),		'-8G'),
			array(1234,					'1234'),
			array(-12345,				'-12345'),
		);
	}
}
