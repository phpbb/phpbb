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

namespace phpbb\forum\enumeration;

/**
 * Enumeration type for forum types.
 */
class forum_types
{
	/**
	 * @var int
	 */
	const FORUM_CATEGORY = 0;

	/**
	 * @var int
	 */
	const FORUM_POST = 1;

	/**
	 * @var int
	 */
	const FORUM_LINK = 2;
}
