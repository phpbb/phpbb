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

namespace phpbb\attachment;

abstract class attachment_category
{
	/** @var int None category */
	public const NONE = 0;

	/** @var int Inline images */
	public const IMAGE = 1;

	/** @var int Not used within the database, only while displaying posts */
	public const THUMB = 4;

	/** @var int Browser-playable audio files */
	public const AUDIO = 7;

	/** @var int Browser-playable video files */
	public const VIDEO = 8;
}
