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

namespace phpbb\cache;

use Doctrine\Common\Cache\Cache;

/**
 * This class is a bridge between Doctrine's cache implementation and phpBB's cache drivers.
 */
class doctrine_bridge implements Cache
{
	/**
	 * @var \phpbb\cache\driver\driver_interface
	 */
	private $cache;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\cache\driver\driver_interface $cache The cache driver
	 */
	public function __construct(\phpbb\cache\driver\driver_interface $cache)
	{
		$this->cache = $cache;
	}

	public function fetch($sql)
	{
		return $this->cache->sql_load($sql);
	}

	public function contains($sql)
	{
		$cache_key = $this->cache->get_cache_id_from_sql_query($sql);
		return $this->cache->_exists('sql_' . $cache_key);
	}

	public function save($sql, $data, $ttl = 0)
	{
		return $this->cache->sql_save($sql, $data, $ttl);
	}

	public function delete($sql)
	{
		$cache_key = $this->cache->get_cache_id_from_sql_query($sql);
		$this->cache->destroy('sql_' . $cache_key);
		return true;
	}

	public function getStats()
	{
		return null;
	}
}
