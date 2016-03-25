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

class phpbb_config_db_test extends phpbb_database_test_case
{
	private $cache;
	private $db;
	private $config;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	public function setUp()
	{
		parent::setUp();

		$this->cache = new phpbb_mock_cache;
		$this->db = $this->new_dbal();
		$this->config = new \phpbb\config\db($this->db, $this->cache, 'phpbb_config');
	}

	public function test_load_config()
	{
		$this->assertEquals('23', $this->config['foo']);
		$this->assertEquals('42', $this->config['bar']);
	}

	public function test_load_cached()
	{
		$cache = new phpbb_mock_cache(array('config' => array('x' => 'y')));
		$this->config = new \phpbb\config\db($this->db, $cache, 'phpbb_config');

		$this->assertTrue(!isset($this->config['foo']));
		$this->assertEquals('42', $this->config['bar']);

		$this->assertEquals('y', $this->config['x']);
	}

	public function test_offset_set()
	{
		$this->config['foo'] = 'x'; // temporary set
		$this->assertEquals('x', $this->config['foo']);

		$config2 = new \phpbb\config\db($this->db, $this->cache, 'phpbb_config');
		$this->assertEquals('23', $config2['foo']);
	}

	public function test_set_overwrite()
	{
		$this->config->set('foo', '17');
		$this->assertEquals('17', $this->config['foo']);

		// re-read config and populate cache
		$config2 = new \phpbb\config\db($this->db, $this->cache, 'phpbb_config');
		$this->cache->checkVar($this, 'config', array('foo' => '17'));
	}

	public function test_set_overwrite_uncached()
	{
		$this->config->set('bar', '17', false);

		// re-read config and populate cache
		$config2 = new \phpbb\config\db($this->db, $this->cache, 'phpbb_config');
		$this->cache->checkVar($this, 'config', array('foo' => '23'));
	}

	public function test_set_new()
	{
		$this->config->set('foobar', '5');
		$this->assertEquals('5', $this->config['foobar']);

		// re-read config and populate cache
		$config2 = new \phpbb\config\db($this->db, $this->cache, 'phpbb_config');
		$this->cache->checkVar($this, 'config', array('foo' => '23', 'foobar' => '5'));
	}

	public function test_set_new_uncached()
	{
		$this->config->set('foobar', '5', false);
		$this->assertEquals('5', $this->config['foobar']);

		// re-read config and populate cache
		$config2 = new \phpbb\config\db($this->db, $this->cache, 'phpbb_config');
		$this->cache->checkVar($this, 'config', array('foo' => '23'));
	}

	public function test_set_atomic_overwrite()
	{
		$this->assertTrue($this->config->set_atomic('foo', '23', '17'));
		$this->assertEquals('17', $this->config['foo']);
	}

	public function test_set_atomic_new()
	{
		$this->assertTrue($this->config->set_atomic('foobar', false, '5'));
		$this->assertEquals('5', $this->config['foobar']);
	}

	public function test_set_atomic_failure()
	{
		$this->assertFalse($this->config->set_atomic('foo', 'wrong', '17'));
		$this->assertEquals('23', $this->config['foo']);
	}

	public function test_increment()
	{
		$this->config->increment('foo', 3);
		$this->assertEquals(26, $this->config['foo']);
		$this->config->increment('foo', 1);
		$this->assertEquals(27, $this->config['foo']);
	}

	public function test_increment_new()
	{
		$this->config->increment('foobar', 3);
		$this->assertEquals(3, $this->config['foobar']);;
	}

	public function test_delete()
	{
		$this->assertTrue(isset($this->config['foo']));
		$this->config->delete('foo');
		$this->cache->checkVarUnset($this, 'foo');
		$this->assertFalse(isset($this->config['foo']));

		// re-read config and populate cache
		$cache2 = new phpbb_mock_cache;
		$config2 = new \phpbb\config\db($this->db, $cache2, 'phpbb_config');
		$cache2->checkVarUnset($this, 'foo');
		$this->assertFalse(isset($config2['foo']));
	}

	public function test_delete_write_read_not_cacheable()
	{
		// bar is dynamic
		$this->assertTrue(isset($this->config['bar']));
		$this->config->delete('bar');
		$this->cache->checkVarUnset($this, 'bar');
		$this->assertFalse(isset($this->config['bar']));

		$this->config->set('bar', 'new bar', false);
		$this->assertEquals('new bar', $this->config['bar']);
	}

	public function test_delete_write_read_cacheable()
	{
		// foo is not dynamic
		$this->assertTrue(isset($this->config['foo']));
		$this->config->delete('foo');
		$this->cache->checkVarUnset($this, 'foo');
		$this->assertFalse(isset($this->config['foo']));

		$this->config->set('foo', 'new foo', true);
		$this->assertEquals('new foo', $this->config['foo']);
	}
}
