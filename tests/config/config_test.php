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

class phpbb_config_test extends phpbb_test_case
{
	public function test_offset_exists()
	{
		$config = new \phpbb\config\config(array('foo' => 'bar'));

		$this->assertTrue(isset($config['foo']));
		$this->assertFalse(isset($config['foobar']));
	}

	public function test_offset_get()
	{
		$config = new \phpbb\config\config(array('foo' => 'bar'));
		$this->assertEquals('bar', $config['foo']);
	}

	public function test_offset_get_missing()
	{
		$config = new \phpbb\config\config(array());
		$this->assertEquals('', $config['foo']);
	}

	public function test_offset_set()
	{
		$config = new \phpbb\config\config(array());
		$config['foo'] = 'x';
		$this->assertEquals('x', $config['foo']);
	}

	public function test_offset_unset_fails()
	{
		$this->setExpectedTriggerError(E_USER_ERROR);
		$config = new \phpbb\config\config(array('foo' => 'x'));
		unset($config['foo']);
	}

	public function test_count()
	{
		$config = new \phpbb\config\config(array('foo' => 'bar'));
		$this->assertEquals(1, count($config));
	}

	public function test_iterate()
	{
		$vars = array('foo' => '23', 'bar' => '42');
		$config = new \phpbb\config\config($vars);

		$count = 0;
		foreach ($config as $key => $value)
		{
			$this->assertTrue(isset($vars[$key]));
			$this->assertEquals($vars[$key], $value);

			$count++;
		}

		$this->assertEquals(count($vars), $count);
	}

	public function test_set_overwrite()
	{
		$config = new \phpbb\config\config(array('foo' => 'x'));
		$config->set('foo', 'bar');
		$this->assertEquals('bar', $config['foo']);
	}

	public function test_set_new()
	{
		$config = new \phpbb\config\config(array());
		$config->set('foo', 'bar');
		$this->assertEquals('bar', $config['foo']);
	}

	public function test_set_atomic_overwrite()
	{
		$config = new \phpbb\config\config(array('foo' => 'bar'));
		$this->assertTrue($config->set_atomic('foo', 'bar', '23'));
		$this->assertEquals('23', $config['foo']);
	}

	public function test_set_atomic_new()
	{
		$config = new \phpbb\config\config(array());
		$this->assertTrue($config->set_atomic('foo', false, '23'));
		$this->assertEquals('23', $config['foo']);
	}

	public function test_set_atomic_failure()
	{
		$config = new \phpbb\config\config(array('foo' => 'bar'));
		$this->assertFalse($config->set_atomic('foo', 'wrong', '23'));
		$this->assertEquals('bar', $config['foo']);
	}

	public function test_increment()
	{
		$config = new \phpbb\config\config(array('foo' => '23'));
		$config->increment('foo', 3);
		$this->assertEquals(26, $config['foo']);
		$config->increment('foo', 1);
		$this->assertEquals(27, $config['foo']);
	}

	public function test_delete()
	{
		$config = new \phpbb\config\config(array('foo' => 'bar'));

		$config->delete('foo');
		$this->assertFalse(isset($config['foo']));
	}
}
