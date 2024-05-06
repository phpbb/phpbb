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

namespace phpbb\lock;

use phpbb\cache\driver\driver_interface as cache_interface;
use phpbb\config\config;

class posting
{
	/** @var cache_interface */
	private $cache;

	/** @var config */
	private $config;

	/** @var string */
	private $lock_name = '';

	/**
	 * Constructor for posting lock
	 *
	 * @param cache_interface $cache
	 * @param config $config
	 */
	public function __construct(cache_interface $cache, config $config)
	{
		$this->cache = $cache;
		$this->config = $config;
	}

	/**
	 * Set lock name
	 *
	 * @param int $creation_time Creation time of form, must be checked already
	 * @param string $form_token Form token used for form, must be checked already
	 *
	 * @return void
	 */
	private function set_lock_name(int $creation_time, string $form_token): void
	{
		$this->lock_name = sha1(((string) $creation_time) . $form_token) . '_posting_lock';
	}

	/**
	 * Acquire lock for current posting form submission
	 *
	 * @param int $creation_time Creation time of form, must be checked already
	 * @param string $form_token Form token used for form, must be checked already
	 *
	 * @return bool True if lock could be acquired, false if not
	 */
	public function acquire(int $creation_time, string $form_token): bool
	{
		$this->set_lock_name($creation_time, $form_token);

		// Lock is held for session, cannot acquire it unless special flag for testing is set
		if ($this->cache->_exists($this->lock_name) && !$this->config->offsetExists('ci_tests_no_lock_posting'))
		{
			return false;
		}

		$this->cache->put($this->lock_name, true, $this->config['flood_interval']);

		return true;
	}
}
