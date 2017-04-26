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

namespace phpbb\composer\io;

use Composer\IO\IOInterface;

interface io_interface extends IOInterface
{
	/**
	 * Returns the composer errors that occurred since the last tcall of the method.
	 *
	 * @return string
	 */
	public function get_composer_error();
}
