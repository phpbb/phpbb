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
* ACM for WinCache
*/
class wincache extends \phpbb\cache\driver\memory
{
	var $extension = 'wincache';

	/**
	* {@inheritDoc}
	*/
	function purge()
	{
		wincache_ucache_clear();

		parent::purge();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function _read(string $var)
	{
		$success = false;
		$result = wincache_ucache_get($this->key_prefix . $var, $success);

		return ($success) ? $result : false;
	}

	/**
	* {@inheritDoc}
	*/
	protected function _write(string $var, $data, int $ttl = 2592000): bool
	{
		return wincache_ucache_set($this->key_prefix . $var, $data, $ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function _delete(string $var): bool
	{
		return wincache_ucache_delete($this->key_prefix . $var);
	}
}
