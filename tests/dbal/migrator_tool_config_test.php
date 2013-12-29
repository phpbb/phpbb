<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
