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

class oracle_extractor extends base_extractor
{
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
		$sql_data .= "DROP TABLE $table_name\n/\n";
		$sql_data .= "\nCREATE TABLE $table_name (\n";

		$sql = "SELECT COLUMN_NAME, DATA_TYPE, DATA_PRECISION, DATA_LENGTH, NULLABLE, DATA_DEFAULT
			FROM ALL_TAB_COLS
			WHERE table_name = '{$table_name}'";
		$result = $this->db->sql_query($sql);

		$rows = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$line = '  "' . $row['column_name'] . '" ' . $row['data_type'];

			if ($row['data_type'] !== 'CLOB')
			{
				if ($row['data_type'] !== 'VARCHAR2' && $row['data_type'] !== 'CHAR')
				{
					$line .= '(' . $row['data_precision'] . ')';
				}
				else
				{
					$line .= '(' . $row['data_length'] . ')';
				}
			}

			if (!empty($row['data_default']))
			{
				$line .= ' DEFAULT ' . $row['data_default'];
			}

			if ($row['nullable'] == 'N')
			{
				$line .= ' NOT NULL';
			}
			$rows[] = $line;
		}
		$this->db->sql_freeresult($result);

		$sql = "SELECT A.CONSTRAINT_NAME, A.COLUMN_NAME
			FROM USER_CONS_COLUMNS A, USER_CONSTRAINTS B
			WHERE A.CONSTRAINT_NAME = B.CONSTRAINT_NAME
				AND B.CONSTRAINT_TYPE = 'P'
				AND A.TABLE_NAME = '{$table_name}'";
		$result = $this->db->sql_query($sql);

		$primary_key = array();
		$constraint_name = '';
		while ($row = $this->db->sql_fetchrow($result))
		{
			$constraint_name = '"' . $row['constraint_name'] . '"';
			$primary_key[] = '"' . $row['column_name'] . '"';
		}
		$this->db->sql_freeresult($result);

		if (count($primary_key))
		{
			$rows[] = "  CONSTRAINT {$constraint_name} PRIMARY KEY (" . implode(', ', $primary_key) . ')';
		}

		$sql = "SELECT A.CONSTRAINT_NAME, A.COLUMN_NAME
			FROM USER_CONS_COLUMNS A, USER_CONSTRAINTS B
			WHERE A.CONSTRAINT_NAME = B.CONSTRAINT_NAME
				AND B.CONSTRAINT_TYPE = 'U'
				AND A.TABLE_NAME = '{$table_name}'";
		$result = $this->db->sql_query($sql);

		$unique = array();
		$constraint_name = '';
		while ($row = $this->db->sql_fetchrow($result))
		{
			$constraint_name = '"' . $row['constraint_name'] . '"';
			$unique[] = '"' . $row['column_name'] . '"';
		}
		$this->db->sql_freeresult($result);

		if (count($unique))
		{
			$rows[] = "  CONSTRAINT {$constraint_name} UNIQUE (" . implode(', ', $unique) . ')';
		}

		$sql_data .= implode(",\n", $rows);
		$sql_data .= "\n)\n/\n";

		$sql = "SELECT A.REFERENCED_NAME, C.*
			FROM USER_DEPENDENCIES A, USER_TRIGGERS B, USER_SEQUENCES C
			WHERE A.REFERENCED_TYPE = 'SEQUENCE'
				AND A.NAME = B.TRIGGER_NAME
				AND B.TABLE_NAME = '{$table_name}'
				AND C.SEQUENCE_NAME = A.REFERENCED_NAME";
		$result = $this->db->sql_query($sql);

		$type = $this->request->variable('type', '');

		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql_data .= "\nDROP SEQUENCE \"{$row['referenced_name']}\"\n/\n";
			$sql_data .= "\nCREATE SEQUENCE \"{$row['referenced_name']}\"";

			if ($type == 'full')
			{
				$sql_data .= ' START WITH ' . $row['last_number'];
			}

			$sql_data .= "\n/\n";
		}
		$this->db->sql_freeresult($result);

		$sql = "SELECT DESCRIPTION, WHEN_CLAUSE, TRIGGER_BODY
			FROM USER_TRIGGERS
			WHERE TABLE_NAME = '{$table_name}'";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql_data .= "\nCREATE OR REPLACE TRIGGER {$row['description']}WHEN ({$row['when_clause']})\n{$row['trigger_body']}\n/\n";
		}
		$this->db->sql_freeresult($result);

		$sql = "SELECT A.INDEX_NAME, B.COLUMN_NAME
			FROM USER_INDEXES A, USER_IND_COLUMNS B
			WHERE A.UNIQUENESS = 'NONUNIQUE'
				AND A.INDEX_NAME = B.INDEX_NAME
				AND B.TABLE_NAME = '{$table_name}'";
		$result = $this->db->sql_query($sql);

		$index = array();

		while ($row = $this->db->sql_fetchrow($result))
		{
			$index[$row['index_name']][] = $row['column_name'];
		}

		foreach ($index as $index_name => $column_names)
		{
			$sql_data .= "\nCREATE INDEX $index_name ON $table_name(" . implode(', ', $column_names) . ")\n/\n";
		}
		$this->db->sql_freeresult($result);
		$this->flush($sql_data);
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

		$ary_type = $ary_name = array();

		// Grab all of the data from current table.
		$sql = "SELECT *
			FROM $table_name";
		$result = $this->db->sql_query($sql);

		$i_num_fields = ocinumcols($result);

		for ($i = 0; $i < $i_num_fields; $i++)
		{
			$ary_type[$i] = ocicolumntype($result, $i + 1);
			$ary_name[$i] = ocicolumnname($result, $i + 1);
		}

		while ($row = $this->db->sql_fetchrow($result))
		{
			$schema_vals = $schema_fields = array();

			// Build the SQL statement to recreate the data.
			for ($i = 0; $i < $i_num_fields; $i++)
			{
				// Oracle uses uppercase - we use lowercase
				$str_val = $row[strtolower($ary_name[$i])];

				if (preg_match('#char|text|bool|raw|clob#i', $ary_type[$i]))
				{
					$str_quote = '';
					$str_empty = "''";
					$str_val = sanitize_data_oracle($str_val);
				}
				else if (preg_match('#date|timestamp#i', $ary_type[$i]))
				{
					if (empty($str_val))
					{
						$str_quote = '';
					}
					else
					{
						$str_quote = "'";
					}
				}
				else
				{
					$str_quote = '';
					$str_empty = 'NULL';
				}

				if (empty($str_val) && $str_val !== '0')
				{
					$str_val = $str_empty;
				}

				$schema_vals[$i] = $str_quote . $str_val . $str_quote;
				$schema_fields[$i] = '"' . $ary_name[$i] . '"';
			}

			// Take the ordered fields and their associated data and build it
			// into a valid sql statement to recreate that field in the data.
			$sql_data = "INSERT INTO $table_name (" . implode(', ', $schema_fields) . ') VALUES (' . implode(', ', $schema_vals) . ")\n/\n";

			$this->flush($sql_data);
		}
		$this->db->sql_freeresult($result);
	}

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
		$this->flush($sql_data);
	}
}
