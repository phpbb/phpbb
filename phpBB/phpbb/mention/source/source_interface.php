<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\mention\source;

interface source_interface
{
	/**
	 * Searches database for names to mention
	 * and returns and array of found items
	 *
	 * @param string $keyword  Search string
	 * @param int    $topic_id Current topic ID
	 * @return array Array of names
	 */
	public function get($keyword, $topic_id);
}
