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

namespace phpbb\feed;

/**
 * Interface implemented by all feeds types
 */
interface feed_interface
{
	/**
	 * Set keys.
	 */
	public function set_keys();

	/**
	 * Open feed
	 */
	public function open();

	/**
	 * Close feed
	 */
	public function close();

	/**
	 * Set key
	 *
	 * @param string $key Key
	 * @param mixed $value Value
	 */
	public function set($key, $value);

	/**
	 * Get key
	 *
	 * @param string $key Key
	 * @return mixed
	 */
	public function get($key);

	/**
	 * Get the next post in the feed
	 *
	 * @return array
	 */
	public function get_item();

	/**
	 * Adjust a feed entry
	 *
	 * @param $item_row
	 * @param $row
	 * @return array
	 */
	public function adjust_item(&$item_row, &$row);
}
