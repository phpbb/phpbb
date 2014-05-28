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

require_once dirname(__FILE__) . '/common_test_case.php';

class phpbb_cache_file_driver_test extends phpbb_cache_common_test_case
{
	private $cache_dir;
	protected $driver;

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
		parent::setUp();

		if (file_exists($this->cache_dir))
		{
			// cache directory possibly left after aborted
			// or failed run earlier
			$this->remove_cache_dir();
		}
		$this->create_cache_dir();

		$this->driver = new \phpbb\cache\driver\file($this->cache_dir);
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
}
