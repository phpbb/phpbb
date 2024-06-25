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

/**
* ACM for APCU
*/
class apcu extends \phpbb\cache\driver\memory
{
	var $extension = 'apcu';

	/**
	* {@inheritDoc}
	*/
	function purge()
	{
		if (PHP_SAPI !== 'cli' || @ini_get('apc.enable_cli'))
		{
			/*
			 * Use an iterator to selectively delete our cache entries without disturbing
			 * any other cache users (e.g. other phpBB boards hosted on this server)
			 */
			apcu_delete(new \APCUIterator('#^' . $this->key_prefix . '#'));
		}

		parent::purge();
	}

	/**
	* {@inheritDoc}
	*/
	protected function _read(string $var)
	{
		return apcu_fetch($this->key_prefix . $var);
	}

	/**
	* {@inheritDoc}
	*/
	protected function _write(string $var, $data, int $ttl = 2592000): bool
	{
		return apcu_store($this->key_prefix . $var, $data, $ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function _delete(string $var): bool
	{
		return apcu_delete($this->key_prefix . $var);
	}
}
