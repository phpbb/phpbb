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
	private $db;

	public function __construct()
	{
		$this->cache_dir = __DIR__ . '/../tmp/cache/';
	}

	protected function setUp()
	{
		global $cache;

		parent::setUp();

		if (file_exists($this->cache_dir))
		{
			// cache directory possibly left after aborted
			// or failed run earlier
			$this->remove_cache_dir();
		}
		$this->create_cache_dir();

		$this->cache = $cache = new phpbb_cache_driver_file(__DIR__ . '/../../phpBB', 'php', $this->cache_dir);
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

		// File should destroy
		$this->cache->destroy('_new_file');
	}

	public function test_purge()
	{
		$this->cache->put('foo', 'bar');
		$this->cache->save();
		$this->cache->put('_new_file', 'foo');

		$this->assertEquals('bar', $this->cache->get('foo'));
		$this->assertEquals('foo', $this->cache->get('_new_file'));

		// Cache directory should not have the global, nor new file
		$this->cache->purge();

		$this->assertEquals(false, $this->cache->get('foo'));
		$this->assertEquals(false, $this->cache->get('_new_file'));
	}
}
