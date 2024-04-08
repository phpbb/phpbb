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
use phpbb\request\request_interface;

class posting
{
	/** @var cache_interface */
	private $cache;

	/** @var config */
	private $config;

	/** @var request_interface */
	private $request;

	/** @var string */
	private $lock_name = '';

	/**
	 * Constructor for posting lock
	 *
	 * @param cache_interface $cache
	 * @param config $config
	 * @param request_interface $request
	 */
	public function __construct(cache_interface $cache, config $config, request_interface $request)
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->request = $request;
	}

	/**
	 * Get lock name
	 * @return string Lock name
	 */
	private function lock_name(): string
	{
		if ($this->lock_name)
		{
			return $this->lock_name;
		}

		$creation_time	= abs($this->request->variable('creation_time', 0));
		$token = $this->request->variable('form_token', '');

		return sha1(((string) $creation_time) . $token) . '_posting_lock';
	}

	/**
	 * Acquire lock for current posting form submission
	 *
	 * @return bool True if lock could be acquired, false if not
	 */
	public function acquire(): bool
	{
		// Lock is held for session, cannot acquire it
		if ($this->cache->_exists($this->lock_name()))
		{
			return false;
		}

		$this->cache->put($this->lock_name(), true, $this->config['flood_interval']);

		return true;
	}
}