<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_cache_file_test extends phpbb_database_test_case
{
	private $cache_dir;
	private $cache;
	private $db;

	public function __construct()
	{
		$this->cache_dir = dirname(__FILE__) . '/../tmp/cache/';
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	protected function setUp()
	{
		global $cache, $config, $db;

		parent::setUp();

		if (file_exists($this->cache_dir))
		{
			// cache directory possibly left after aborted
			// or failed run earlier
			$this->remove_cache_dir();
		}
		$this->create_cache_dir();

		$cache_factory = new phpbb_cache_factory('file');
		$this->cache = $cache = $cache_factory->get_service(dirname(__FILE__) . '/../../phpBB', 'php', $this->cache_dir);

		$this->assertEquals($this->cache_dir, $this->cache->get_cache_dir());

		$config = new phpbb_config(array());
		set_config(null, null, null, $config);

		$this->db = $db = $this->new_dbal();
	}

	protected function tearDown()
	{
		if (file_exists($this->cache_dir))
		{
			$this->remove_cache_dir();
		}

		$this->db->sql_close();

		parent::tearDown();
	}

	private function create_cache_dir()
	{
		$this->get_test_case_helpers()->makedirs($this->cache_dir);
	}

	private function remove_cache_dir()
	{
		$iterator = new DirectoryIterator($this->cache_dir);
		foreach ($iterator as $file)
		{
			if ($file != '.' && $file != '..')
			{
				unlink($this->cache_dir . '/' . $file);
			}
		}
		rmdir($this->cache_dir);
	}

	public function test_global()
	{
		$this->cache->put('foo', 'bar');
		$this->assertEquals('bar', $this->cache->get('foo'));
		$this->cache->save();
		$this->assertEquals('bar', $this->cache->get('foo'));

		// Data should destroy
		$this->cache->destroy('foo');
		$this->assertEquals(false, $this->cache->get('foo'));
	}

	public function test_data()
	{
		$this->cache->put('_new_file', 'foo');

		// File should exist and load correct data
		$this->assertEquals('foo', $this->cache->get('_new_file'));
		$this->assertFileExists($this->cache_dir . 'data_new_file.php');

		// File should destroy
		$this->cache->destroy('_new_file');
		$this->assertFileNotExists($this->cache_dir . 'data_new_file.php');
	}

	public function test_purge()
	{
		$this->cache->put('foo', 'bar');
		$this->cache->save();
		$this->cache->put('_new_file', 'foo');

		$this->assertFileExists($this->cache_dir . 'data_global.php');
		$this->assertFileExists($this->cache_dir . 'data_new_file.php');

		// Cache directory should not have the global, nor new file
		$this->cache->purge();
		$this->assertFileNotExists($this->cache_dir . 'data_global.php');
		$this->assertFileNotExists($this->cache_dir . 'data_new_file.php');
	}

	public function test_tidy()
	{
		$this->cache->put('foo', 'bar');
		$this->cache->save();
		$this->cache->put('_new_file', 'foo');
		$this->cache->put('_new_file2', 'foo', -1);

		$this->assertFileExists($this->cache_dir . 'data_global.php');
		$this->assertFileExists($this->cache_dir . 'data_new_file.php');
		$this->assertFileExists($this->cache_dir . 'data_new_file2.php');

		// Cache directory should have the global and the new file, not the second new file
		$this->cache->tidy();
		$this->assertFileExists($this->cache_dir . 'data_global.php');
		$this->assertFileExists($this->cache_dir . 'data_new_file.php');
		$this->assertFileNotExists($this->cache_dir . 'data_new_file2.php');
	}

	public function test_cache_sql()
	{
		if (!defined('DEBUG_EXTRA'))
		{
			// For $this->assertContains('Query results obtained from the cache', $db->sql_report);
			define('DEBUG_EXTRA', true);
		}

		$sql = 'SELECT * FROM phpbb_config WHERE config_name = \'foo\'';
		$result = $this->db->sql_query($sql, 300);
		$first_result = $this->db->sql_fetchrow($result);

		$this->assertEquals(array(
			'config_name'		=> 'foo',
			'config_value'		=> 23,
			'is_dynamic'		=> 0,
		), $first_result);

		// Query results should be from DB and file should have been created
		$file_name = 'data_sql_' . md5(preg_replace('/[\n\r\s\t]+/', ' ', $sql)) . '.php';
		$this->assertFileExists($this->cache_dir . $file_name);
		$this->assertNotContains('Query results obtained from the cache', $this->db->sql_report);


		$sql = 'SELECT * FROM phpbb_config WHERE config_name = \'foo\'';
		$result = $this->db->sql_query($sql, 300);

		$this->assertEquals($first_result, $this->db->sql_fetchrow($result));

		// Now the query should have loaded results from the cache
		$this->assertContains('Query results obtained from the cache', $this->db->sql_report);
	}

	public function test_cache_sql_expired()
	{
		$sql = 'SELECT * FROM phpbb_config WHERE config_name = \'bar\'';
		$result = $this->db->sql_query($sql, -1);
		$second_result = $this->db->sql_fetchrow($result);

		$this->assertEquals(array(
			'config_name'		=> 'bar',
			'config_value'		=> 42,
			'is_dynamic'		=> 1,
		), $second_result);

		// Query results should be from DB and file should have been created
		$this->assertFileExists($this->cache_dir . 'data_sql_' . md5(preg_replace('/[\n\r\s\t]+/', ' ', $sql)) . '.php');
		$this->assertNotContains('Query results obtained from the cache', $this->db->sql_report);


		$sql = 'SELECT * FROM phpbb_config WHERE config_name = \'bar\'';
		$result = $this->db->sql_query($sql, -1);

		$this->assertEquals($second_result, $this->db->sql_fetchrow($result));

		// Query results should be from DB and file should have been created
		$this->assertNotContains('Query results obtained from the cache', $this->db->sql_report);
	}

	public function test_cache_sql_tidy()
	{
		$sql1 = 'SELECT * FROM phpbb_config WHERE config_name = \'foo\'';
		$result = $this->db->sql_query($sql1, 300);

		$sql2 = 'SELECT * FROM phpbb_config WHERE config_name = \'bar\'';
		$result = $this->db->sql_query($sql2, -1);

		// Both files should exist
		$this->assertFileExists($this->cache_dir . 'data_sql_' . md5(preg_replace('/[\n\r\s\t]+/', ' ', $sql1)) . '.php');
		$this->assertFileExists($this->cache_dir . 'data_sql_' . md5(preg_replace('/[\n\r\s\t]+/', ' ', $sql2)) . '.php');

		// Cache Tidy test
		$this->cache->tidy();

		// Only the first file should exist
		$this->assertFileExists($this->cache_dir . 'data_sql_' . md5(preg_replace('/[\n\r\s\t]+/', ' ', $sql1)) . '.php');
		$this->assertFileNotExists($this->cache_dir . 'data_sql_' . md5(preg_replace('/[\n\r\s\t]+/', ' ', $sql2)) . '.php');
	}

	public function test_cache_sql_purge()
	{
		$sql1 = 'SELECT * FROM phpbb_config WHERE config_name = \'foo\'';
		$result = $this->db->sql_query($sql1, 300);

		$sql2 = 'SELECT * FROM phpbb_config WHERE config_name = \'bar\'';
		$result = $this->db->sql_query($sql2, -1);

		// Both files should exist
		$this->assertFileExists($this->cache_dir . 'data_sql_' . md5(preg_replace('/[\n\r\s\t]+/', ' ', $sql1)) . '.php');
		$this->assertFileExists($this->cache_dir . 'data_sql_' . md5(preg_replace('/[\n\r\s\t]+/', ' ', $sql2)) . '.php');

		// Cache Tidy test
		$this->cache->purge();

		// Neither file should exist
		$this->assertFileNotExists($this->cache_dir . 'data_sql_' . md5(preg_replace('/[\n\r\s\t]+/', ' ', $sql1)) . '.php');
		$this->assertFileNotExists($this->cache_dir . 'data_sql_' . md5(preg_replace('/[\n\r\s\t]+/', ' ', $sql2)) . '.php');
	}
}
