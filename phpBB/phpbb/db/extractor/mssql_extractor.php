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

class mssql_extractor extends base_extractor
{
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

		$this->flush("COMMIT\nGO\n");
		parent::write_end();
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
		$sql_data .= "BEGIN TRANSACTION\n";
		$sql_data .= "GO\n";
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
		$sql_data .= "IF OBJECT_ID(N'$table_name', N'U') IS NOT NULL\n";
		$sql_data .= "DROP TABLE $table_name;\n";
		$sql_data .= "GO\n";
		$sql_data .= "\nCREATE TABLE [$table_name] (\n";
		$rows = array();

		$text_flag = false;

		$sql = "SELECT COLUMN_NAME, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMNPROPERTY(object_id(TABLE_NAME), COLUMN_NAME, 'IsIdentity') as IS_IDENTITY
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_NAME = '$table_name'";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$line = "\t[{$row['COLUMN_NAME']}] [{$row['DATA_TYPE']}]";

			if ($row['DATA_TYPE'] == 'text')
			{
				$text_flag = true;
			}

			if ($row['IS_IDENTITY'])
			{
				$line .= ' IDENTITY (1 , 1)';
			}

			if ($row['CHARACTER_MAXIMUM_LENGTH'] && $row['DATA_TYPE'] !== 'text')
			{
				$line .= ' (' . $row['CHARACTER_MAXIMUM_LENGTH'] . ')';
			}

			if ($row['IS_NULLABLE'] == 'YES')
			{
				$line .= ' NULL';
			}
			else
			{
				$line .= ' NOT NULL';
			}

			if ($row['COLUMN_DEFAULT'])
			{
				$line .= ' DEFAULT ' . $row['COLUMN_DEFAULT'];
			}

			$rows[] = $line;
		}
		$this->db->sql_freeresult($result);

		$sql_data .= implode(",\n", $rows);
		$sql_data .= "\n) ON [PRIMARY]";

		if ($text_flag)
		{
			$sql_data .= " TEXTIMAGE_ON [PRIMARY]";
		}

		$sql_data .= "\nGO\n\n";
		$rows = array();

		$sql = "SELECT CONSTRAINT_NAME, COLUMN_NAME
			FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
			WHERE TABLE_NAME = '$table_name'";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!sizeof($rows))
			{
				$sql_data .= "ALTER TABLE [$table_name] WITH NOCHECK ADD\n";
				$sql_data .= "\tCONSTRAINT [{$row['CONSTRAINT_NAME']}] PRIMARY KEY  CLUSTERED \n\t(\n";
			}
			$rows[] = "\t\t[{$row['COLUMN_NAME']}]";
		}
		if (sizeof($rows))
		{
			$sql_data .= implode(",\n", $rows);
			$sql_data .= "\n\t)  ON [PRIMARY] \nGO\n";
		}
		$this->db->sql_freeresult($result);

		$index = array();
		$sql = "EXEC sp_statistics '$table_name'";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['TYPE'] == 3)
			{
				$index[$row['INDEX_NAME']][] = '[' . $row['COLUMN_NAME'] . ']';
			}
		}
		$this->db->sql_freeresult($result);

		foreach ($index as $index_name => $column_name)
		{
			$index[$index_name] = implode(', ', $column_name);
		}

		foreach ($index as $index_name => $columns)
		{
			$sql_data .= "\nCREATE  INDEX [$index_name] ON [$table_name]($columns) ON [PRIMARY]\nGO\n";
		}
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

		if ($this->db->get_sql_layer() === 'mssqlnative')
		{
			$this->write_data_mssqlnative($table_name);
		}
		else
		{
			$this->write_data_odbc($table_name);
		}
	}

	/**
	* Extracts data from database table (for MSSQL Native driver)
	*
	* @param	string	$table_name	name of the database table
	* @return null
	* @throws \phpbb\db\extractor\exception\extractor_not_initialized_exception when calling this function before init_extractor()
	*/
	protected function write_data_mssqlnative($table_name)
	{
		if (!$this->is_initialized)
		{
			throw new extractor_not_initialized_exception();
		}

		$ary_type = $ary_name = array();
		$ident_set = false;
		$sql_data = '';

		// Grab all of the data from current table.
		$sql = "SELECT * FROM $table_name";
		$this->db->mssqlnative_set_query_options(array('Scrollable' => SQLSRV_CURSOR_STATIC));
		$result = $this->db->sql_query($sql);

		$retrieved_data = $this->db->mssqlnative_num_rows($result);

		if (!$retrieved_data)
		{
			$this->db->sql_freeresult($result);
			return;
		}

		$sql = "SELECT COLUMN_NAME, DATA_TYPE
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE INFORMATION_SCHEMA.COLUMNS.TABLE_NAME = '" . $this->db->sql_escape($table_name) . "'";
		$result_fields = $this->db->sql_query($sql);

		$i_num_fields = 0;
		while ($row = $this->db->sql_fetchrow($result_fields))
		{
			$ary_type[$i_num_fields] = $row['DATA_TYPE'];
			$ary_name[$i_num_fields] = $row['COLUMN_NAME'];
			$i_num_fields++;
		}
		$this->db->sql_freeresult($result_fields);

		$sql = "SELECT 1 as has_identity
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE COLUMNPROPERTY(object_id('$table_name'), COLUMN_NAME, 'IsIdentity') = 1";
		$result2 = $this->db->sql_query($sql);
		$row2 = $this->db->sql_fetchrow($result2);

		if (!empty($row2['has_identity']))
		{
			$sql_data .= "\nSET IDENTITY_INSERT $table_name ON\nGO\n";
			$ident_set = true;
		}
		$this->db->sql_freeresult($result2);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$schema_vals = $schema_fields = array();

			// Build the SQL statement to recreate the data.
			for ($i = 0; $i < $i_num_fields; $i++)
			{
				$str_val = $row[$ary_name[$i]];

				// defaults to type number - better quote just to be safe, so check for is_int too
				if (is_int($ary_type[$i]) || preg_match('#char|text|bool|varbinary#i', $ary_type[$i]))
				{
					$str_quote = '';
					$str_empty = "''";
					$str_val = sanitize_data_mssql(str_replace("'", "''", $str_val));
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

				if (empty($str_val) && $str_val !== '0' && !(is_int($str_val) || is_float($str_val)))
				{
					$str_val = $str_empty;
				}

				$schema_vals[$i] = $str_quote . $str_val . $str_quote;
				$schema_fields[$i] = $ary_name[$i];
			}

			// Take the ordered fields and their associated data and build it
			// into a valid sql statement to recreate that field in the data.
			$sql_data .= "INSERT INTO $table_name (" . implode(', ', $schema_fields) . ') VALUES (' . implode(', ', $schema_vals) . ");\nGO\n";

			$this->flush($sql_data);
			$sql_data = '';
		}
		$this->db->sql_freeresult($result);

		if ($ident_set)
		{
			$sql_data .= "\nSET IDENTITY_INSERT $table_name OFF\nGO\n";
		}
		$this->flush($sql_data);
	}

	/**
	* Extracts data from database table (for ODBC driver)
	*
	* @param	string	$table_name	name of the database table
	* @return null
	* @throws \phpbb\db\extractor\exception\extractor_not_initialized_exception when calling this function before init_extractor()
	*/
	protected function write_data_odbc($table_name)
	{
		if (!$this->is_initialized)
		{
			throw new extractor_not_initialized_exception();
		}

		$ary_type = $ary_name = array();
		$ident_set = false;
		$sql_data = '';

		// Grab all of the data from current table.
		$sql = "SELECT *
			FROM $table_name";
		$result = $this->db->sql_query($sql);

		$retrieved_data = odbc_num_rows($result);

		if ($retrieved_data)
		{
			$sql = "SELECT 1 as has_identity
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE COLUMNPROPERTY(object_id('$table_name'), COLUMN_NAME, 'IsIdentity') = 1";
			$result2 = $this->db->sql_query($sql);
			$row2 = $this->db->sql_fetchrow($result2);
			if (!empty($row2['has_identity']))
			{
				$sql_data .= "\nSET IDENTITY_INSERT $table_name ON\nGO\n";
				$ident_set = true;
			}
			$this->db->sql_freeresult($result2);
		}

		$i_num_fields = odbc_num_fields($result);

		for ($i = 0; $i < $i_num_fields; $i++)
		{
			$ary_type[$i] = odbc_field_type($result, $i + 1);
			$ary_name[$i] = odbc_field_name($result, $i + 1);
		}

		while ($row = $this->db->sql_fetchrow($result))
		{
			$schema_vals = $schema_fields = array();

			// Build the SQL statement to recreate the data.
			for ($i = 0; $i < $i_num_fields; $i++)
			{
				$str_val = $row[$ary_name[$i]];

				if (preg_match('#char|text|bool|varbinary#i', $ary_type[$i]))
				{
					$str_quote = '';
					$str_empty = "''";
					$str_val = sanitize_data_mssql(str_replace("'", "''", $str_val));
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

				if (empty($str_val) && $str_val !== '0' && !(is_int($str_val) || is_float($str_val)))
				{
					$str_val = $str_empty;
				}

				$schema_vals[$i] = $str_quote . $str_val . $str_quote;
				$schema_fields[$i] = $ary_name[$i];
			}

			// Take the ordered fields and their associated data and build it
			// into a valid sql statement to recreate that field in the data.
			$sql_data .= "INSERT INTO $table_name (" . implode(', ', $schema_fields) . ') VALUES (' . implode(', ', $schema_vals) . ");\nGO\n";

			$this->flush($sql_data);

			$sql_data = '';

		}
		$this->db->sql_freeresult($result);

		if ($retrieved_data && $ident_set)
		{
			$sql_data .= "\nSET IDENTITY_INSERT $table_name OFF\nGO\n";
		}
		$this->flush($sql_data);
	}
}
