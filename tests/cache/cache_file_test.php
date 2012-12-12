<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once __DIR__ . '/../../phpBB/includes/functions.php';

class phpbb_cache_file_test extends phpbb_test_case
{
	private $cache_dir;
	private $cache;

	public function __construct()
	{
		$this->cache_dir = __DIR__ . '/../tmp/cache/';
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

		$this->cache = $cache = new phpbb_cache_driver_file(__DIR__ . '/../../phpBB', 'php', $this->cache_dir);

		// Config is needed for tidy()
		$config = new phpbb_config(array());
		set_config(null, null, null, $config);

		$this->assertEquals($this->cache_dir, $this->cache->get_cache_dir());
	}

	protected function tearDown()
	{
		if (file_exists($this->cache_dir))
		{
			$this->remove_cache_dir();
		}

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

	public function test_data()
	{
		$this->cache->put('_new_file', 'foo');

		// File should exist and load correct data
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
}
