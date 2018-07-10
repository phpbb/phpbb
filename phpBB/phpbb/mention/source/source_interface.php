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
	 * and alters the passed array of found items
	 *
	 * @param array  $names    Array of already fetched data with names
	 * @param string $keyword  Search string
	 * @param int    $topic_id Current topic ID
	 */
	public function get(array &$names, $keyword, $topic_id);
}
