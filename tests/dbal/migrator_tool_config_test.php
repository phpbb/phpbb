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
		try
		{
			$this->tool->add('foo', 'bar');
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals('bar', $this->config['foo']);

		try
		{
			$this->tool->add('foo', 'bar');
			$this->fail('Exception not thrown');
		}
		catch (Exception $e) {}
	}

	public function test_update()
	{
		$this->config->set('foo', 'bar');
		try
		{
			$this->tool->update('foo', 'bar2');
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals('bar2', $this->config['foo']);
	}

	public function test_update_if_equals()
	{
		$this->config->set('foo', 'bar');

		try
		{
			$this->tool->update_if_equals('', 'foo', 'bar2');
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals('bar', $this->config['foo']);

		try
		{
			$this->tool->update_if_equals('bar', 'foo', 'bar2');
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals('bar2', $this->config['foo']);
	}

	public function test_remove()
	{
		$this->config->set('foo', 'bar');

		try
		{
			$this->tool->remove('foo');
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertFalse(isset($this->config['foo']));
	}

	public function test_reverse()
	{
		$this->config->set('foo', 'bar');

		try
		{
			$this->tool->reverse('add', 'foo');
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertFalse(isset($this->config['foo']));

		$this->config->set('foo', 'bar');

		try
		{
			$this->tool->reverse('update_if_equals', 'test', 'foo', 'bar');
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals('test', $this->config['foo']);
	}
}
