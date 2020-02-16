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
 * Enumeration type for forum feature flags.
 */
class forum_feature_flags
{
	/**
	 * @var int
	 */
	const FORUM_FLAG_LINK_TRACK = 1;

	/**
	 * @var int
	 */
	const FORUM_FLAG_PRUNE_POLL = 2;

	/**
	 * @var int
	 */
	const FORUM_FLAG_PRUNE_ANNOUNCE = 4;

	/**
	 * @var int
	 */
	const FORUM_FLAG_PRUNE_STICKY = 8;

	/**
	 * @var int
	 */
	const FORUM_FLAG_ACTIVE_TOPICS = 16;

	/**
	 * @var int
	 */
	const FORUM_FLAG_POST_REVIEW = 32;

	/**
	 * @var int
	 */
	const FORUM_FLAG_QUICK_REPLY = 64;
}
