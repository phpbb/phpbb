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

class SQLite3
{
	public function query(string $query) {}
}

class SQLite3Result
{
	public function fetchArray(int $mode = SQLITE3_BOTH) {}
}

/**
 * @link https://www.php.net/manual/en/sqlite3.constants.php
 */
define('SQLITE3_ASSOC', 1);
define('SQLITE3_NUM', 2);
define('SQLITE3_BOTH', 3);

define('SQLITE3_OPEN_READONLY', 1);
define('SQLITE3_OPEN_READWRITE', 2);
define('SQLITE3_OPEN_CREATE', 4);
