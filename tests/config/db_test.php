<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once __DIR__ . '/../mock/cache.php';

class phpbb_config_db_test extends phpbb_database_test_case
{
	private $cache;
	private $db;
	private $config;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/config.xml');
	}

	public function setUp()
	{
		parent::setUp();

		$this->cache = new phpbb_mock_cache;
		$this->db = $this->new_dbal();
		$this->config = new phpbb_config_db($this->cache, $this->db, 'phpbb_config');
	}

	public function test_load_config()
	{
		$this->assertEquals('23', $this->config['foo']);
		$this->assertEquals('42', $this->config['bar']);
	}

	public function test_offset_set()
	{
		$this->config['foo'] = 'x'; // temporary set
		$this->assertEquals('x', $this->config['foo']);

		$config2 = new phpbb_config_db($this->cache, $this->db, 'phpbb_config');
		$this->assertEquals('23', $config2['foo']);
	}

	public function test_set_overwrite()
	{
		$this->config->set('foo', '17');
		$this->assertEquals('17', $this->config['foo']);

		// re-read config and populate cache
		$config2 = new phpbb_config_db($this->cache, $this->db, 'phpbb_config');
		$this->cache->checkVar($this, 'config', array('foo' => '17'));
	}

	public function test_set_overwrite_uncached()
	{
		$this->config->set('bar', '17', false);

		// re-read config and populate cache
		$config2 = new phpbb_config_db($this->cache, $this->db, 'phpbb_config');
		$this->cache->checkVar($this, 'config', array('foo' => '23'));
	}

	public function test_set_new()
	{
		$this->config->set('foobar', '5');
		$this->assertEquals('5', $this->config['foobar']);

		// re-read config and populate cache
		$config2 = new phpbb_config_db($this->cache, $this->db, 'phpbb_config');
		$this->cache->checkVar($this, 'config', array('foo' => '23', 'foobar' => '5'));
	}

	public function test_set_new_uncached()
	{
		$this->config->set('foobar', '5', false);
		$this->assertEquals('5', $this->config['foobar']);

		// re-read config and populate cache
		$config2 = new phpbb_config_db($this->cache, $this->db, 'phpbb_config');
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
}
