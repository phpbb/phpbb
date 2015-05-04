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

namespace phpbb\db\extractor;

use phpbb\db\extractor\exception\extractor_not_initialized_exception;

class sqlite3_extractor extends base_extractor
{
	/**
	* {@inheritdoc}
	*/
	public function write_start($table_prefix)
	{
		if (!$this->is_initialized)
		{
			throw new extractor_not_initialized_exception();
		}

		$sql_data = "--\n";
		$sql_data .= "-- phpBB Backup Script\n";
		$sql_data .= "-- Dump of tables for $table_prefix\n";
		$sql_data .= "-- DATE : " . gmdate("d-m-Y H:i:s", $this->time) . " GMT\n";
		$sql_data .= "--\n";
		$sql_data .= "BEGIN TRANSACTION;\n";
		$this->flush($sql_data);
	}

	/**
	* {@inheritdoc}
	*/
	public function write_table($table_name)
	{
		if (!$this->is_initialized)
		{
			throw new extractor_not_initialized_exception();
		}

		$sql_data = '-- Table: ' . $table_name . "\n";
		$sql_data .= "DROP TABLE $table_name;\n";

		$sql = "SELECT sql
			FROM sqlite_master
			WHERE type = 'table'
				AND name = '" . $this->db->sql_escape($table_name) . "'
			ORDER BY name ASC;";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		// Create Table
		$sql_data .= $row['sql'] . ";\n";

		$result = $this->db->sql_query("PRAGMA index_list('" . $this->db->sql_escape($table_name) . "');");

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (strpos($row['name'], 'autoindex') !== false)
			{
				continue;
			}

			$result2 = $this->db->sql_query("PRAGMA index_info('" . $this->db->sql_escape($row['name']) . "');");

			$fields = array();
			while ($row2 = $this->db->sql_fetchrow($result2))
			{
				$fields[] = $row2['name'];
			}
			$this->db->sql_freeresult($result2);

			$sql_data .= 'CREATE ' . ($row['unique'] ? 'UNIQUE ' : '') . 'INDEX ' . $row['name'] . ' ON ' . $table_name . ' (' . implode(', ', $fields) . ");\n";
		}
		$this->db->sql_freeresult($result);

		$this->flush($sql_data . "\n");
	}

	/**
	* {@inheritdoc}
	*/
	public function write_data($table_name)
	{
		if (!$this->is_initialized)
		{
			throw new extractor_not_initialized_exception();
		}

		$result = $this->db->sql_query("PRAGMA table_info('" . $this->db->sql_escape($table_name) . "');");

		$col_types = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$col_types[$row['name']] = $row['type'];
		}
		$this->db->sql_freeresult($result);

		$sql_insert = 'INSERT INTO ' . $table_name . ' (' . implode(', ', array_keys($col_types)) . ') VALUES (';

		$sql = "SELECT *
			FROM $table_name";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			foreach ($row as $column_name => $column_data)
			{
				if (is_null($column_data))
				{
					$row[$column_name] = 'NULL';
				}
				else if ($column_data === '')
				{
					$row[$column_name] = "''";
				}
				else if (stripos($col_types[$column_name], 'text') !== false || stripos($col_types[$column_name], 'char') !== false || stripos($col_types[$column_name], 'blob') !== false)
				{
					$row[$column_name] = sanitize_data_generic(str_replace("'", "''", $column_data));
				}
			}
			$this->flush($sql_insert . implode(', ', $row) . ");\n");
		}
	}

	/**
	* Writes closing line(s) to database backup
	*
	* @return null
	* @throws \phpbb\db\extractor\exception\extractor_not_initialized_exception when calling this function before init_extractor()
	*/
	public function write_end()
	{
		if (!$this->is_initialized)
		{
			throw new extractor_not_initialized_exception();
		}

		$this->flush("COMMIT;\n");
		parent::write_end();
	}
}
