<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once __DIR__ . '/../../phpBB/includes/functions.php';

class phpbb_cache_file_test extends phpbb_database_test_case
{
	private $cache_dir;
	private $cache;
	private $db;

	public function __construct()
	{
		$this->cache_dir = __DIR__ . '/../tmp/cache/';
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/config.xml');
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

		$config = new phpbb_config(array());
		set_config(null, null, null, $config);

		$this->db = $db = $this->new_dbal();

		$driver = new phpbb_cache_driver_file(__DIR__ . '/../../phpBB', 'php', $this->cache_dir);
		$this->cache = $cache = new phpbb_cache_driver_sql_generic($this->db, $driver);
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

	public function test_cache_sql()
	{
		if (!defined('DEBUG'))
		{
			// For $this->assertContains('Query results obtained from the cache', $db->sql_report);
			define('DEBUG', true);
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
		$this->assertNotContains('Query results obtained from the cache', $this->db->sql_report);


		$sql = 'SELECT * FROM phpbb_config WHERE config_name = \'foo\'';
		$result = $this->db->sql_query($sql, 300);

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
		$this->assertNotContains('Query results obtained from the cache', $this->db->sql_report);

		$sql = 'SELECT * FROM phpbb_config WHERE config_name = \'bar\'';
		$result = $this->db->sql_query($sql, -1);

		$this->assertEquals($second_result, $this->db->sql_fetchrow($result));

		// Query results should be from DB and file should have been created
		$this->assertNotContains('Query results obtained from the cache', $this->db->sql_report);
	}
}
