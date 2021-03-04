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

require_once __DIR__ . '/phpbb_php_ini_fake.php';

class phpbb_wrapper_phpbb_php_ini_test extends phpbb_test_case
{
	/** @var \phpbb_php_ini_fake php_ini */
	protected $php_ini;

	protected function setUp(): void
	{
		$this->php_ini = new phpbb_php_ini_fake;
	}

	public function test_get_string()
	{
		$this->assertSame('', $this->php_ini->getString(false));
		$this->assertSame('phpbb', $this->php_ini->getString(' phpbb '));
	}

	public function test_get_bool()
	{
		$this->assertSame(true, $this->php_ini->getBool('ON'));
		$this->assertSame(true, $this->php_ini->getBool('on'));
		$this->assertSame(true, $this->php_ini->getBool('1'));

		$this->assertSame(false, $this->php_ini->getBool('OFF'));
		$this->assertSame(false, $this->php_ini->getBool('off'));
		$this->assertSame(false, $this->php_ini->getBool('0'));
		$this->assertSame(false, $this->php_ini->getBool(''));
	}

	public function test_get_int()
	{
		$this->assertSame(1234, $this->php_ini->getNumeric('1234'));
		$this->assertSame(-12345, $this->php_ini->getNumeric('-12345'));
		$this->assertSame(null, $this->php_ini->getNumeric('phpBB'));
	}

	public function test_get_float()
	{
		$this->assertSame(1234.0, $this->php_ini->getNumeric('1234.0'));
		$this->assertSame(-12345.0, $this->php_ini->getNumeric('-12345.0'));
		$this->assertSame(null, $this->php_ini->getNumeric('phpBB'));
	}

	public function test_get_bytes_invalid()
	{
		$this->assertSame(null, $this->php_ini->getBytes(false));
		$this->assertSame(null, $this->php_ini->getBytes('phpBB'));
		$this->assertSame(null, $this->php_ini->getBytes('k'));
		$this->assertSame(null, $this->php_ini->getBytes('-k'));
		$this->assertSame(null, $this->php_ini->getBytes('M'));
		$this->assertSame(null, $this->php_ini->getBytes('-M'));
	}

	/**
	* @dataProvider get_bytes_data
	*/
	public function test_get_bytes($expected, $value)
	{
		$actual = $this->php_ini->getBytes($value);

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
