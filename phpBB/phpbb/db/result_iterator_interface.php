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

namespace phpbb\db;

use SeekableIterator as Seekable;

/**
 * Seekable iterator over a database result set.
 *
 * @deprecated 4.0.0-dev Use Doctrine DBAL directly instead of this class.
 */
interface result_iterator_interface extends Seekable
{
	/**
	 * Returns all rows of the wrapped statement.
	 *
	 * @return array All rows of the wrapped statement.
	 */
	public function fetch_all();

	/**
	 * Invalidates the content of the iterator.
	 *
	 * @return void
	 */
	public function invalidate();

	/**
	 * Returns a unique ID for the iterator.
	 *
	 * @return int A unique ID.
	 */
	public function get_id();
}
