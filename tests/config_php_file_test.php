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

class phpbb_config_php_file_test extends phpbb_test_case
{
	public function test_default()
	{
		$config_php = new \phpbb\config_php_file(dirname( __FILE__ ) . '/fixtures/', 'php');
		$this->assertSame('bar', $config_php->get('foo'));
		$this->assertNull($config_php->get('bar'));
		$this->assertSame(array('foo' => 'bar', 'foo_foo' => 'bar bar'), $config_php->get_all());
	}

	public function test_set_config_file()
	{
		$config_php = new \phpbb\config_php_file(dirname( __FILE__ ) . '/fixtures/', 'php');
		$config_php->set_config_file(dirname( __FILE__ ) . '/fixtures/config_other.php');
		$this->assertSame('foo', $config_php->get('bar'));
		$this->assertNull($config_php->get('foo'));
		$this->assertSame(array('bar' => 'foo', 'bar_bar' => 'foo foo'), $config_php->get_all());
	}

	public function test_non_existent_file()
	{
		$config_php = new \phpbb\config_php_file(dirname( __FILE__ ) . '/fixtures/non_existent/', 'php');
		$this->assertNull($config_php->get('bar'));
		$this->assertNull($config_php->get('foo'));
		$this->assertSame(array(), $config_php->get_all());
	}
}
