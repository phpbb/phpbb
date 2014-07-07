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

class phpbb_dbal_migrator_tool_config_text_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/migrator_config_text.xml');
	}

	public function setup()
	{
		parent::setup();

		$this->db = $this->new_dbal();
		$this->config_text = new \phpbb\config\db_text($this->db, 'phpbb_config_text');

		$this->tool = new \phpbb\db\migration\tool\config_text($this->config_text);
	}

	public function test_add()
	{
		$this->tool->add('foo', 'bar');
		$this->assertEquals('bar', $this->config_text->get('foo'));
	}

	public function test_add_twice()
	{
		$this->tool->add('foo', 'bar');
		$this->assertEquals('bar', $this->config_text->get('foo'));

		$this->tool->add('foo', 'bar2');
		$this->assertEquals('bar', $this->config_text->get('foo'));
	}

	public function test_update()
	{
		$this->config_text->set('foo', 'bar');

		$this->tool->update('foo', 'bar2');
		$this->assertEquals('bar2', $this->config_text->get('foo'));
	}

	public function test_remove()
	{
		$this->config_text->set('foo', 'bar');

		$this->tool->remove('foo');
		$this->assertNull($this->config_text->get('foo'));
	}

	public function test_reverse_add()
	{
		$this->config_text->set('foo', 'bar');

		$this->tool->reverse('add', 'foo');
		$this->assertNull($this->config_text->get('foo'));
	}

	public function test_reverse_remove()
	{
		$this->tool->reverse('remove', 'foo');
		$this->assertSame('', $this->config_text->get('foo'));
	}
}
