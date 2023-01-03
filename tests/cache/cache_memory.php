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

class phpbb_cache_memory extends \phpbb\cache\driver\memory
{
	protected $data = array();

	/**
	* Set cache path
	*/
	function __construct()
	{
	}

	/**
	 * {@inheritDoc}
	 */
	protected function _read(string $var)
	{
		return $this->data[$var] ?? false;
	}

	/**
	* {@inheritDoc}
	*/
	protected function _write(string $var, $data, int $ttl = 2592000): bool
	{
		$this->data[$var] = $data;
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function _delete(string $var): bool
	{
		unset($this->data[$var]);
		return true;
	}
}
