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

use phpbb\cache\driver\file as file_cache;
use phpbb\config\config;
use phpbb\lock\posting;

class phpbb_lock_posting_test extends phpbb_test_case
{
	/** @var \phpbb\cache\driver\file */
	protected $cache;

	/** @var config */
	protected $config;

	/** @var posting */
	protected $lock;

	public function setUp(): void
	{
		$this->cache = new file_cache(__DIR__ . '/../tmp/');
		$this->cache->purge(); // ensure cache is clean
		$this->config = new config([
			'flood_interval' => 15,
		]);
		$this->lock = new posting($this->cache, $this->config);
	}

	public function test_lock_acquire()
	{
		$this->assertTrue($this->lock->acquire(100, 'foo'));
		$this->assertFalse($this->lock->acquire(100, 'foo'));

		$this->assertTrue($this->cache->_exists(sha1('100foo') . '_posting_lock'));
		$this->assertFalse($this->lock->acquire(100, 'foo'));
		$this->cache->put(sha1('100foo') . '_posting_lock', 'foo', -30);

		$this->assertTrue($this->lock->acquire(100, 'foo'));
		$this->assertTrue($this->cache->_exists(sha1('100foo') . '_posting_lock'));
		$this->config->offsetSet('ci_tests_no_lock_posting', true);
		$this->assertTrue($this->lock->acquire(100, 'foo'));
		$this->assertTrue($this->cache->_exists(sha1('100foo') . '_posting_lock'));
		// Multiple acquires possible due to special ci test flag
		$this->assertTrue($this->lock->acquire(100, 'foo'));
	}
}
