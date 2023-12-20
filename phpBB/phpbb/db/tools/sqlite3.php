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

namespace phpbb\db\tools;

/**
 * Database Tools for handling cross-db actions such as altering columns, etc.
 * Currently not supported is returning SQL for creating tables.
 */
class sqlite3 extends tools
{
	/**
	 * {@inheritDoc}
	 */
	function sql_table_exists($table_name)
	{
		$this->db->sql_return_on_error(true);
		$result = $this->db->sql_query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table_name}'");
		$this->db->sql_return_on_error(false);

		if (!empty($this->db->sql_fetchrowset($result)))
		{
			$this->db->sql_freeresult($result);
			return true;
		}

		return false;
	}
}
