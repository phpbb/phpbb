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

namespace phpbb\db\migration\data\v400;

use phpbb\db\migration\migration;

/**
 * Pre-flight check for the utf8mb4 conversion: verifies that no existing value
 * in the primary-key columns that get shortened exceeds the new maximum length,
 * aborting the migration with a clear error instead of a cryptic MySQL one.
 */
class convert_to_utf8mb4_check extends migration
{
	public function effectively_installed()
	{
		return $this->db->get_sql_layer() !== 'mysqli';
	}

	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\v400a1',
		];
	}

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'check_column_lengths']]],
		];
	}

	public function check_column_lengths()
	{
		// [table, column, maximum allowed characters after shortening]
		$checks = [
			[$this->table_prefix . 'config', 'config_name', 191],
			[$this->table_prefix . 'config_text', 'config_name', 191],
			[$this->table_prefix . 'migrations', 'migration_name', 191],
			[$this->table_prefix . 'oauth_accounts', 'provider', 189],
		];

		foreach ($checks as [$table, $column, $limit])
		{
			if (!$this->db_tools->sql_table_exists($table))
			{
				continue;
			}

			$result = $this->db->sql_query("SELECT MAX(CHAR_LENGTH($column)) AS max_len FROM $table");
			$max_len = (int) $this->db->sql_fetchfield('max_len', false, $result);
			$this->db->sql_freeresult($result);

			if ($max_len > $limit)
			{
				throw new \RuntimeException(sprintf(
					'Cannot convert database to utf8mb4: column "%s.%s" contains a value of %d characters, which exceeds the maximum allowed length of %d characters.',
					$table,
					$column,
					$max_len,
					$limit
				));
			}
		}
	}
}
