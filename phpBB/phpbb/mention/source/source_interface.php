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
	 * @return bool Whether there are no more satisfying names left
	 */
	public function get(array &$names, string $keyword, int $topic_id): bool;

	/**
	 * Returns the priority of the currently selected name
	 * Please note that simple inner priorities for a certain source
	 * can be set with ORDER BY SQL clause
	 *
	 * @param array $row Array of fetched data for the name type (e.g. user row)
	 * @return int Priority (defaults to 1)
	 */
	public function get_priority(array $row): int;
}
