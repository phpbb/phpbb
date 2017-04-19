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

class mysql_extractor extends base_extractor
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

		$sql_data = "#\n";
		$sql_data .= "# phpBB Backup Script\n";
		$sql_data .= "# Dump of tables for $table_prefix\n";
		$sql_data .= "# DATE : " . gmdate("d-m-Y H:i:s", $this->time) . " GMT\n";
		$sql_data .= "#\n";
		$this->flush($sql_data);
	}

	/**
	* {@inheritdoc}
	*/
	public function write_table($table_name)
	{
		static $new_extract;

		if (!$this->is_initialized)
		{
			throw new extractor_not_initialized_exception();
		}

		if ($new_extract === null)
		{
			if ($this->db->get_sql_layer() === 'mysqli' || version_compare($this->db->sql_server_info(true), '3.23.20', '>='))
			{
				$new_extract = true;
			}
			else
			{
				$new_extract = false;
			}
		}

		if ($new_extract)
		{
			$this->new_write_table($table_name);
		}
		else
		{
			$this->old_write_table($table_name);
		}
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

		if ($this->db->get_sql_layer() === 'mysqli')
		{
			$this->write_data_mysqli($table_name);
		}
		else
		{
			$this->write_data_mysql($table_name);
		}
	}

	/**
	* Extracts data from database table (for MySQLi driver)
	*
	* @param	string	$table_name	name of the database table
	* @return null
	* @throws \phpbb\db\extractor\exception\extractor_not_initialized_exception when calling this function before init_extractor()
	*/
	protected function write_data_mysqli($table_name)
	{
		if (!$this->is_initialized)
		{
			throw new extractor_not_initialized_exception();
		}

		$sql = "SELECT *
			FROM $table_name";
		$result = mysqli_query($this->db->get_db_connect_id(), $sql, MYSQLI_USE_RESULT);
		if ($result != false)
		{
			$fields_cnt = mysqli_num_fields($result);

			// Get field information
			$field = mysqli_fetch_fields($result);
			$field_set = array();

			for ($j = 0; $j < $fields_cnt; $j++)
			{
				$field_set[] = $field[$j]->name;
			}

			$search			= array("\\", "'", "\x00", "\x0a", "\x0d", "\x1a", '"');
			$replace		= array("\\\\", "\\'", '\0', '\n', '\r', '\Z', '\\"');
			$fields			= implode(', ', $field_set);
			$sql_data		= 'INSERT INTO ' . $table_name . ' (' . $fields . ') VALUES ';
			$first_set		= true;
			$query_len		= 0;
			$max_len		= get_usable_memory();

			while ($row = mysqli_fetch_row($result))
			{
				$values	= array();
				if ($first_set)
				{
					$query = $sql_data . '(';
				}
				else
				{
					$query  .= ',(';
				}

				for ($j = 0; $j < $fields_cnt; $j++)
				{
					if (!isset($row[$j]) || is_null($row[$j]))
					{
						$values[$j] = 'NULL';
					}
					else if (($field[$j]->flags & 32768) && !($field[$j]->flags & 1024))
					{
						$values[$j] = $row[$j];
					}
					else
					{
						$values[$j] = "'" . str_replace($search, $replace, $row[$j]) . "'";
					}
				}
				$query .= implode(', ', $values) . ')';

				$query_len += strlen($query);
				if ($query_len > $max_len)
				{
					$this->flush($query . ";\n\n");
					$query = '';
					$query_len = 0;
					$first_set = true;
				}
				else
				{
					$first_set = false;
				}
			}
			mysqli_free_result($result);

			// check to make sure we have nothing left to flush
			if (!$first_set && $query)
			{
				$this->flush($query . ";\n\n");
			}
		}
	}

	/**
	* Extracts data from database table (for MySQL driver)
	*
	* @param	string	$table_name	name of the database table
	* @return null
	* @throws \phpbb\db\extractor\exception\extractor_not_initialized_exception when calling this function before init_extractor()
	*/
	protected function write_data_mysql($table_name)
	{
		if (!$this->is_initialized)
		{
			throw new extractor_not_initialized_exception();
		}

		$sql = "SELECT *
			FROM $table_name";
		$result = mysql_unbuffered_query($sql, $this->db->get_db_connect_id());

		if ($result != false)
		{
			$fields_cnt = mysql_num_fields($result);

			// Get field information
			$field = array();
			for ($i = 0; $i < $fields_cnt; $i++)
			{
				$field[] = mysql_fetch_field($result, $i);
			}
			$field_set = array();

			for ($j = 0; $j < $fields_cnt; $j++)
			{
				$field_set[] = $field[$j]->name;
			}

			$search			= array("\\", "'", "\x00", "\x0a", "\x0d", "\x1a", '"');
			$replace		= array("\\\\", "\\'", '\0', '\n', '\r', '\Z', '\\"');
			$fields			= implode(', ', $field_set);
			$sql_data		= 'INSERT INTO ' . $table_name . ' (' . $fields . ') VALUES ';
			$first_set		= true;
			$query_len		= 0;
			$max_len		= get_usable_memory();

			while ($row = mysql_fetch_row($result))
			{
				$values = array();
				if ($first_set)
				{
					$query = $sql_data . '(';
				}
				else
				{
					$query  .= ',(';
				}

				for ($j = 0; $j < $fields_cnt; $j++)
				{
					if (!isset($row[$j]) || is_null($row[$j]))
					{
						$values[$j] = 'NULL';
					}
					else if ($field[$j]->numeric && ($field[$j]->type !== 'timestamp'))
					{
						$values[$j] = $row[$j];
					}
					else
					{
						$values[$j] = "'" . str_replace($search, $replace, $row[$j]) . "'";
					}
				}
				$query .= implode(', ', $values) . ')';

				$query_len += strlen($query);
				if ($query_len > $max_len)
				{
					$this->flush($query . ";\n\n");
					$query = '';
					$query_len = 0;
					$first_set = true;
				}
				else
				{
					$first_set = false;
				}
			}
			mysql_free_result($result);

			// check to make sure we have nothing left to flush
			if (!$first_set && $query)
			{
				$this->flush($query . ";\n\n");
			}
		}
	}

	/**
	* Extracts database table structure (for MySQLi or MySQL 3.23.20+)
	*
	* @param	string	$table_name	name of the database table
	* @return null
	* @throws \phpbb\db\extractor\exception\extractor_not_initialized_exception when calling this function before init_extractor()
	*/
	protected function new_write_table($table_name)
	{
		if (!$this->is_initialized)
		{
			throw new extractor_not_initialized_exception();
		}

		$sql = 'SHOW CREATE TABLE ' . $table_name;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);

		$sql_data = '# Table: ' . $table_name . "\n";
		$sql_data .= "DROP TABLE IF EXISTS $table_name;\n";
		$this->flush($sql_data . $row['Create Table'] . ";\n\n");

		$this->db->sql_freeresult($result);
	}

	/**
	* Extracts database table structure (for MySQL verisons older than 3.23.20)
	*
	* @param	string	$table_name	name of the database table
	* @return null
	* @throws \phpbb\db\extractor\exception\extractor_not_initialized_exception when calling this function before init_extractor()
	*/
	protected function old_write_table($table_name)
	{
		if (!$this->is_initialized)
		{
			throw new extractor_not_initialized_exception();
		}

		$sql_data = '# Table: ' . $table_name . "\n";
		$sql_data .= "DROP TABLE IF EXISTS $table_name;\n";
		$sql_data .= "CREATE TABLE $table_name(\n";
		$rows = array();

		$sql = "SHOW FIELDS
			FROM $table_name";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$line = '   ' . $row['Field'] . ' ' . $row['Type'];

			if (!is_null($row['Default']))
			{
				$line .= " DEFAULT '{$row['Default']}'";
			}

			if ($row['Null'] != 'YES')
			{
				$line .= ' NOT NULL';
			}

			if ($row['Extra'] != '')
			{
				$line .= ' ' . $row['Extra'];
			}

			$rows[] = $line;
		}
		$this->db->sql_freeresult($result);

		$sql = "SHOW KEYS
			FROM $table_name";

		$result = $this->db->sql_query($sql);

		$index = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$kname = $row['Key_name'];

			if ($kname != 'PRIMARY')
			{
				if ($row['Non_unique'] == 0)
				{
					$kname = "UNIQUE|$kname";
				}
			}

			if ($row['Sub_part'])
			{
				$row['Column_name'] .= '(' . $row['Sub_part'] . ')';
			}
			$index[$kname][] = $row['Column_name'];
		}
		$this->db->sql_freeresult($result);

		foreach ($index as $key => $columns)
		{
			$line = '   ';

			if ($key == 'PRIMARY')
			{
				$line .= 'PRIMARY KEY (' . implode(', ', $columns) . ')';
			}
			else if (strpos($key, 'UNIQUE') === 0)
			{
				$line .= 'UNIQUE ' . substr($key, 7) . ' (' . implode(', ', $columns) . ')';
			}
			else if (strpos($key, 'FULLTEXT') === 0)
			{
				$line .= 'FULLTEXT ' . substr($key, 9) . ' (' . implode(', ', $columns) . ')';
			}
			else
			{
				$line .= "KEY $key (" . implode(', ', $columns) . ')';
			}

			$rows[] = $line;
		}

		$sql_data .= implode(",\n", $rows);
		$sql_data .= "\n);\n\n";

		$this->flush($sql_data);
	}
}
