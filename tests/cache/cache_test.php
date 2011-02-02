<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_cache_test extends phpbb_test_case
{
	private $cache_dir;

	public function __construct()
	{
		$this->cache_dir = dirname(__FILE__) . '/../tmp/cache/';
	}

	protected function setUp()
	{
		if (file_exists($this->cache_dir))
		{
			// cache directory possibly left after aborted
			// or failed run earlier
			$this->remove_cache_dir();
		}
		$this->create_cache_dir();
	}

	protected function tearDown()
	{
		if (file_exists($this->cache_dir))
		{
			$this->remove_cache_dir();
		}
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

	public function test_cache_driver_file()
	{
		$driver = new phpbb_cache_driver_file($this->cache_dir);
		$driver->put('test_key', 'test_value');
		$driver->save();

		$this->assertEquals(
			'test_value',
			$driver->get('test_key'),
			'File ACM put and get'
		);
	}
}
