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

namespace phpbb\passwords\driver;

interface rehashable_driver_interface extends driver_interface
{
	/**
	 * Check if password needs to be rehashed
	 *
	 * @param string $hash Hash to check for rehash
	 * @return bool True if password needs to be rehashed, false if not
	 */
	public function needs_rehash($hash);
}
