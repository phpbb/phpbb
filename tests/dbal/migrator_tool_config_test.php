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

class phpbb_dbal_migrator_tool_config_test extends phpbb_test_case
{
	public function setup()
	{
		$this->config = new \phpbb\config\config(array());

		$this->tool = new \phpbb\db\migration\tool\config($this->config);

		parent::setup();
	}

	public function test_add()
	{
		$this->tool->add('foo', 'bar');
		$this->assertEquals('bar', $this->config['foo']);
	}

	public function test_add_twice()
	{
		$this->tool->add('foo', 'bar');
		$this->assertEquals('bar', $this->config['foo']);

		$this->tool->add('foo', 'bar2');
		$this->assertEquals('bar', $this->config['foo']);
	}

	public function test_update()
	{
		$this->config->set('foo', 'bar');

		$this->tool->update('foo', 'bar2');
		$this->assertEquals('bar2', $this->config['foo']);
	}

	public function test_update_if_equals()
	{
		$this->config->set('foo', 'bar');

		$this->tool->update_if_equals('', 'foo', 'bar2');
		$this->assertEquals('bar', $this->config['foo']);

		$this->tool->update_if_equals('bar', 'foo', 'bar2');
		$this->assertEquals('bar2', $this->config['foo']);
	}

	public function test_remove()
	{
		$this->config->set('foo', 'bar');

		$this->tool->remove('foo');
		$this->assertFalse(isset($this->config['foo']));
	}

	public function test_reverse_add()
	{
		$this->config->set('foo', 'bar');

		$this->tool->reverse('add', 'foo');
		$this->assertFalse(isset($this->config['foo']));
	}

	public function test_reverse_remove()
	{
		$this->config->delete('foo');

		$this->tool->reverse('remove', 'foo');
		$this->assertEquals('', $this->config['foo']);
	}

	public function test_reverse_update_if_equals()
	{
		$this->config->set('foo', 'bar');

		$this->tool->reverse('update_if_equals', 'test', 'foo', 'bar');
		$this->assertEquals('test', $this->config['foo']);
	}
}
