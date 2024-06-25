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

namespace phpbb\cache\driver;

if (!defined('PHPBB_ACM_MEMCACHED_PORT'))
{
	define('PHPBB_ACM_MEMCACHED_PORT', 11211);
}

if (!defined('PHPBB_ACM_MEMCACHED_COMPRESS'))
{
	define('PHPBB_ACM_MEMCACHED_COMPRESS', true);
}

if (!defined('PHPBB_ACM_MEMCACHED_HOST'))
{
	define('PHPBB_ACM_MEMCACHED_HOST', 'localhost');
}

if (!defined('PHPBB_ACM_MEMCACHED'))
{
	//can define multiple servers with host1/port1,host2/port2 format
	define('PHPBB_ACM_MEMCACHED', PHPBB_ACM_MEMCACHED_HOST . '/' . PHPBB_ACM_MEMCACHED_PORT);
}

/**
* ACM for Memcached
*/
class memcached extends memory
{
	/** @var string Extension to use */
	protected $extension = 'memcached';

	/** @var \Memcached Memcached class */
	protected $memcached;

	/** @var int Flags */
	protected $flags = 0;

	/**
	 * Memcached constructor
	 *
	 * @param string $memcached_servers Memcached servers string (optional)
	 */
	public function __construct($memcached_servers = '')
	{
		// Call the parent constructor
		parent::__construct();

		$memcached_servers = $memcached_servers ?: PHPBB_ACM_MEMCACHED;

		$this->memcached = new \Memcached();
		$this->memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
		// Memcached defaults to using compression, disable if we don't want
		// to use it
		if (!PHPBB_ACM_MEMCACHED_COMPRESS)
		{
			$this->memcached->setOption(\Memcached::OPT_COMPRESSION, false);
		}

		$server_list = [];
		foreach (explode(',', $memcached_servers) as $u)
		{
			if (preg_match('#(.*)/(\d+)#', $u, $parts))
			{
				$server_list[] = [trim($parts[1]), (int) trim($parts[2])];
			}
		}

		$this->memcached->addServers($server_list);

		if (empty($server_list) || empty($this->memcached->getStats()))
		{
			trigger_error('Could not connect to memcached server(s).');
		}
	}

	/**
	* {@inheritDoc}
	*/
	public function unload()
	{
		parent::unload();

		unset($this->memcached);
	}

	/**
	* {@inheritDoc}
	*/
	public function purge()
	{
		$this->memcached->flush();

		parent::purge();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function _read(string $var)
	{
		return $this->memcached->get($this->key_prefix . $var);
	}

	/**
	* {@inheritDoc}
	*/
	protected function _write(string $var, $data, int $ttl = 2592000): bool
	{
		if (!$this->memcached->replace($this->key_prefix . $var, $data, $ttl))
		{
			return $this->memcached->set($this->key_prefix . $var, $data, $ttl);
		}
		return true;
	}

	/**
	* {@inheritDoc}
	*/
	protected function _delete(string $var): bool
	{
		return $this->memcached->delete($this->key_prefix . $var);
	}
}
