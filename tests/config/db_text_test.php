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

class phpbb_config_db_text_test extends phpbb_database_test_case
{
	/** @var \phpbb\config\db_text */
	protected $config_text;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config_text.xml');
	}

	public function setUp()
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->config_text = new \phpbb\config\db_text($this->db, 'phpbb_config_text');
	}

	public function test_get()
	{
		$this->assertSame('23', $this->config_text->get('foo'));
		$this->assertSame('string-de-ding', $this->config_text->get('meh'));
	}

	public function test_get_nonexisting()
	{
		$this->assertNull($this->config_text->get('noooooo'));
	}

	public function test_set_new_get()
	{
		$this->config_text->set('barz', 'phpbb');
		$this->assertSame('phpbb', $this->config_text->get('barz'));
	}

	public function test_set_replace_get()
	{
		$this->config_text->set('foo', '24');
		$this->assertSame('24', $this->config_text->get('foo'));
	}

	public function test_set_same_value_get()
	{
		$this->config_text->set('foo', '23');
		$this->assertSame('23', $this->config_text->get('foo'));
	}

	public function test_set_get_long_string()
	{
		$expected = str_repeat('ABC', 10000);
		$this->config_text->set('long', $expected);
		$this->assertSame($expected, $this->config_text->get('long'));
	}

	public function test_delete_get()
	{
		$this->config_text->delete('foo');
		$this->assertNull($this->config_text->get('foo'));

		$this->assertSame('42', $this->config_text->get('bar'));
		$this->assertSame('string-de-ding', $this->config_text->get('meh'));
	}

	public function test_get_array_empty()
	{
		$this->assertEmpty($this->config_text->get_array(array('key1', 'key2')));
	}

	public function test_get_array_subset()
	{
		$expected = array(
			'bar' => '42',
			'foo' => '23',
		);

		$actual = $this->config_text->get_array(array_keys($expected));
		ksort($actual);

		$this->assertSame($expected, $actual);
	}

	public function test_set_array_get_array_subset()
	{
		$set_array_param = array(
			// New entry
			'baby' => 'phpBB',
			// Entry update
			'bar' => '64',
			// Entry update - same value
			'foo' => '23',
		);

		$this->config_text->set_array($set_array_param);

		$expected = array_merge($set_array_param, array(
			'foo' => '23',
		));

		$actual = $this->config_text->get_array(array_keys($expected));
		ksort($actual);

		$this->assertSame($expected, $actual);
	}

	public function test_delete_array_get_remaining()
	{
		$this->config_text->delete_array(array('foo', 'bar'));

		$this->assertNull($this->config_text->get('bar'));
		$this->assertNull($this->config_text->get('foo'));

		$this->assertSame('string-de-ding', $this->config_text->get('meh'));
	}
}
