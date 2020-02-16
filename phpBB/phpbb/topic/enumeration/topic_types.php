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

namespace phpbb\topic\enumeration;

/**
 * Enumeration class for topic types.
 */
class topic_types
{
	/**
	 * @var int
	 */
	const POST_NORMAL = 0;

	/**
	 * @var int
	 */
	const POST_STICKY = 1;

	/**
	 * @var int
	 */
	const POST_ANNOUNCE = 2;

	/**
	 * @var int
	 */
	const POST_GLOBAL = 3;
}
