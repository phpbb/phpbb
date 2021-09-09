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
class postgres extends tools
{
	/**
	 * Get the column types for postgres only
	 *
	 * @return array
	 */
	public static function get_dbms_type_map()
	{
		return array(
			'postgres'	=> array(
				'INT:'		=> 'INT4',
				'BINT'		=> 'INT8',
				'ULINT'		=> 'INT4', // unsigned
				'UINT'		=> 'INT4', // unsigned
				'UINT:'		=> 'INT4', // unsigned
				'USINT'		=> 'INT2', // unsigned
				'BOOL'		=> 'INT2', // unsigned
				'TINT:'		=> 'INT2',
				'VCHAR'		=> 'varchar(255)',
				'VCHAR:'	=> 'varchar(%d)',
				'CHAR:'		=> 'char(%d)',
				'XSTEXT'	=> 'varchar(1000)',
				'STEXT'		=> 'varchar(3000)',
				'TEXT'		=> 'varchar(8000)',
				'MTEXT'		=> 'TEXT',
				'XSTEXT_UNI'=> 'varchar(100)',
				'STEXT_UNI'	=> 'varchar(255)',
				'TEXT_UNI'	=> 'varchar(4000)',
				'MTEXT_UNI'	=> 'TEXT',
				'TIMESTAMP'	=> 'INT4', // unsigned
				'DECIMAL'	=> 'decimal(5,2)',
				'DECIMAL:'	=> 'decimal(%d,2)',
				'PDECIMAL'	=> 'decimal(6,3)',
				'PDECIMAL:'	=> 'decimal(%d,3)',
				'VCHAR_UNI'	=> 'varchar(255)',
				'VCHAR_UNI:'=> 'varchar(%d)',
				'VCHAR_CI'	=> 'varchar_ci',
				'VARBINARY'	=> 'bytea',
			),
		);
	}

	/**
	* Constructor. Set DB Object and set {@link $return_statements return_statements}.
	*
	* @param \phpbb\db\driver\driver_interface	$db					Database connection
	* @param bool		$return_statements	True if only statements should be returned and no SQL being executed
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, $return_statements = false)
	{
		parent::__construct($db, $return_statements);

		// Determine mapping database type
		$this->sql_layer = 'postgres';

		$this->dbms_type_map = self::get_dbms_type_map();
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_list_tables()
	{
		$sql = 'SELECT relname
			FROM pg_stat_user_tables';
		$result = $this->db->sql_query($sql);

		$tables = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$name = current($row);
			$tables[$name] = $name;
		}
		$this->db->sql_freeresult($result);

		return $tables;
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_table_exists($table_name)
	{
		$sql = "SELECT CAST(EXISTS(
			SELECT FROM information_schema.tables
				WHERE table_schema = 'public'
					AND table_name   = '" . $this->db->sql_escape($table_name) . "'
			) AS INTEGER)";
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$table_exists = (booL) $row['exists'];
		$this->db->sql_freeresult($result);

		return $table_exists;
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_create_table($table_name, $table_data)
	{
		// holds the DDL for a column
		$columns = $statements = array();

		if ($this->sql_table_exists($table_name))
		{
			return $this->_sql_run_sql($statements);
		}

		// Begin transaction
		$statements[] = 'begin';

		// Determine if we have created a PRIMARY KEY in the earliest
		$primary_key_gen = false;

		// Determine if the table requires a sequence
		$create_sequence = false;

		// Begin table sql statement
		$table_sql = 'CREATE TABLE ' . $table_name . ' (' . "\n";

		// Iterate through the columns to create a table
		foreach ($table_data['COLUMNS'] as $column_name => $column_data)
		{
			// here lies an array, filled with information compiled on the column's data
			$prepared_column = $this->sql_prepare_column_data($table_name, $column_name, $column_data);

			if (isset($prepared_column['auto_increment']) && $prepared_column['auto_increment'] && strlen($column_name) > 26) // "${column_name}_gen"
			{
				trigger_error("Index name '${column_name}_gen' on table '$table_name' is too long. The maximum auto increment column length is 26 characters.", E_USER_ERROR);
			}

			// here we add the definition of the new column to the list of columns
			$columns[] = "\t {$column_name} " . $prepared_column['column_type_sql'];

			// see if we have found a primary key set due to a column definition if we have found it, we can stop looking
			if (!$primary_key_gen)
			{
				$primary_key_gen = isset($prepared_column['primary_key_set']) && $prepared_column['primary_key_set'];
			}

			// create sequence DDL based off of the existence of auto incrementing columns
			if (!$create_sequence && isset($prepared_column['auto_increment']) && $prepared_column['auto_increment'])
			{
				$create_sequence = $column_name;
			}
		}

		// this makes up all the columns in the create table statement
		$table_sql .= implode(",\n", $columns);

		// we have yet to create a primary key for this table,
		// this means that we can add the one we really wanted instead
		if (!$primary_key_gen)
		{
			// Write primary key
			if (isset($table_data['PRIMARY_KEY']))
			{
				if (!is_array($table_data['PRIMARY_KEY']))
				{
					$table_data['PRIMARY_KEY'] = array($table_data['PRIMARY_KEY']);
				}

				$table_sql .= ",\n\t PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . ')';
			}
		}

		// do we need to add a sequence for auto incrementing columns?
		if ($create_sequence)
		{
			$statements[] = "CREATE SEQUENCE {$table_name}_seq;";
		}

		// close the table
		$table_sql .= "\n);";
		$statements[] = $table_sql;

		// Write Keys
		if (isset($table_data['KEYS']))
		{
			foreach ($table_data['KEYS'] as $key_name => $key_data)
			{
				if (!is_array($key_data[1]))
				{
					$key_data[1] = array($key_data[1]);
				}

				$old_return_statements = $this->return_statements;
				$this->return_statements = true;

				$key_stmts = ($key_data[0] == 'UNIQUE') ? $this->sql_create_unique_index($table_name, $key_name, $key_data[1]) : $this->sql_create_index($table_name, $key_name, $key_data[1]);

				foreach ($key_stmts as $key_stmt)
				{
					$statements[] = $key_stmt;
				}

				$this->return_statements = $old_return_statements;
			}
		}

		// Commit Transaction
		$statements[] = 'commit';

		return $this->_sql_run_sql($statements);
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_list_columns($table_name)
	{
		$columns = array();

		$sql = "SELECT a.attname
			FROM pg_class c, pg_attribute a
			WHERE c.relname = '{$table_name}'
				AND a.attnum > 0
				AND a.attrelid = c.oid";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$column = strtolower(current($row));
			$columns[$column] = $column;
		}
		$this->db->sql_freeresult($result);

		return $columns;
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_index_exists($table_name, $index_name)
	{
		$sql = "SELECT ic.relname as index_name
			FROM pg_class bc, pg_class ic, pg_index i
			WHERE (bc.oid = i.indrelid)
				AND (ic.oid = i.indexrelid)
				AND (bc.relname = '" . $table_name . "')
				AND (i.indisunique != 't')
				AND (i.indisprimary != 't')";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// This DBMS prefixes index names with the table name
			$row['index_name'] = $this->strip_table_name_from_index_name($table_name, $row['index_name']);

			if (strtolower($row['index_name']) == strtolower($index_name))
			{
				$this->db->sql_freeresult($result);
				return true;
			}
		}
		$this->db->sql_freeresult($result);

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_unique_index_exists($table_name, $index_name)
	{
		$sql = "SELECT ic.relname as index_name, i.indisunique
			FROM pg_class bc, pg_class ic, pg_index i
			WHERE (bc.oid = i.indrelid)
				AND (ic.oid = i.indexrelid)
				AND (bc.relname = '" . $table_name . "')
				AND (i.indisprimary != 't')";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['indisunique'] != 't')
			{
				continue;
			}

			// This DBMS prefixes index names with the table name
			$row['index_name'] = $this->strip_table_name_from_index_name($table_name, $row['index_name']);

			if (strtolower($row['index_name']) == strtolower($index_name))
			{
				$this->db->sql_freeresult($result);
				return true;
			}
		}
		$this->db->sql_freeresult($result);

		return false;
	}

	/**
	* Function to prepare some column information for better usage
	* @access private
	*/
	function sql_prepare_column_data($table_name, $column_name, $column_data)
	{
		if (strlen($column_name) > 30)
		{
			trigger_error("Column name '$column_name' on table '$table_name' is too long. The maximum is 30 characters.", E_USER_ERROR);
		}

		// Get type
		list($column_type, $orig_column_type) = $this->get_column_type($column_data[0]);

		// Adjust default value if db-dependent specified
		if (is_array($column_data[1]))
		{
			$column_data[1] = (isset($column_data[1][$this->sql_layer])) ? $column_data[1][$this->sql_layer] : $column_data[1]['default'];
		}

		$sql = " {$column_type} ";

		$return_array = array(
			'column_type'		=> $column_type,
			'auto_increment'	=> false,
		);

		if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
		{
			$default_val = "nextval('{$table_name}_seq')";
			$return_array['auto_increment'] = true;
		}
		else if (!is_null($column_data[1]))
		{
			$default_val = "'" . $column_data[1] . "'";
			$return_array['null'] = 'NOT NULL';
			$sql .= 'NOT NULL ';
		}
		else
		{
			// Integers need to have 0 instead of empty string as default
			if (strpos($column_type, 'INT') === 0)
			{
				$default_val = '0';
			}
			else
			{
				$default_val = "'" . $column_data[1] . "'";
			}
			$return_array['null'] = 'NULL';
			$sql .= 'NULL ';
		}

		$return_array['default'] = $default_val;

		$sql .= "DEFAULT {$default_val}";

		// Unsigned? Then add a CHECK contraint
		if (in_array($orig_column_type, $this->unsigned_types))
		{
			$return_array['constraint'] = "CHECK ({$column_name} >= 0)";
			$sql .= " CHECK ({$column_name} >= 0)";
		}

		$return_array['column_type_sql'] = $sql;

		return $return_array;
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_column_add($table_name, $column_name, $column_data, $inline = false)
	{
		$column_data = $this->sql_prepare_column_data($table_name, $column_name, $column_data);
		$statements = array();

		// Does not support AFTER, only through temporary table
		if (version_compare($this->db->sql_server_info(true), '8.0', '>='))
		{
			$statements[] = 'ALTER TABLE ' . $table_name . ' ADD COLUMN "' . $column_name . '" ' . $column_data['column_type_sql'];
		}
		else
		{
			// old versions cannot add columns with default and null information
			$statements[] = 'ALTER TABLE ' . $table_name . ' ADD COLUMN "' . $column_name . '" ' . $column_data['column_type'] . ' ' . $column_data['constraint'];

			if (isset($column_data['null']))
			{
				if ($column_data['null'] == 'NOT NULL')
				{
					$statements[] = 'ALTER TABLE ' . $table_name . ' ALTER COLUMN ' . $column_name . ' SET NOT NULL';
				}
			}

			if (isset($column_data['default']))
			{
				$statements[] = 'ALTER TABLE ' . $table_name . ' ALTER COLUMN ' . $column_name . ' SET DEFAULT ' . $column_data['default'];
			}
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_column_remove($table_name, $column_name, $inline = false)
	{
		$statements = array();

		$statements[] = 'ALTER TABLE ' . $table_name . ' DROP COLUMN "' . $column_name . '"';

		return $this->_sql_run_sql($statements);
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_index_drop($table_name, $index_name)
	{
		$statements = array();

		$statements[] = 'DROP INDEX ' . $table_name . '_' . $index_name;

		return $this->_sql_run_sql($statements);
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_table_drop($table_name)
	{
		$statements = array();

		if (!$this->sql_table_exists($table_name))
		{
			return $this->_sql_run_sql($statements);
		}

		// the most basic operation, get rid of the table
		$statements[] = 'DROP TABLE ' . $table_name;

		// PGSQL does not "tightly" bind sequences and tables, we must guess...
		$sql = "SELECT relname
			FROM pg_class
			WHERE relkind = 'S'
				AND relname = '{$table_name}_seq'";
		$result = $this->db->sql_query($sql);

		// We don't even care about storing the results. We already know the answer if we get rows back.
		if ($this->db->sql_fetchrow($result))
		{
			$statements[] =  "DROP SEQUENCE IF EXISTS {$table_name}_seq;\n";
		}
		$this->db->sql_freeresult($result);

		return $this->_sql_run_sql($statements);
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_create_primary_key($table_name, $column, $inline = false)
	{
		$statements = array();

		$statements[] = 'ALTER TABLE ' . $table_name . ' ADD PRIMARY KEY (' . implode(', ', $column) . ')';

		return $this->_sql_run_sql($statements);
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_create_unique_index($table_name, $index_name, $column)
	{
		$statements = array();

		$this->check_index_name_length($table_name, $index_name);

		$statements[] = 'CREATE UNIQUE INDEX ' . $table_name . '_' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';

		return $this->_sql_run_sql($statements);
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_create_index($table_name, $index_name, $column)
	{
		$statements = array();

		$this->check_index_name_length($table_name, $index_name);

		// remove index length
		$column = preg_replace('#:.*$#', '', $column);

		$statements[] = 'CREATE INDEX ' . $table_name . '_' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';

		return $this->_sql_run_sql($statements);
	}


	/**
	 * {@inheritDoc}
	 */
	function sql_list_index($table_name)
	{
		$index_array = array();

		$sql = "SELECT ic.relname as index_name
			FROM pg_class bc, pg_class ic, pg_index i
			WHERE (bc.oid = i.indrelid)
				AND (ic.oid = i.indexrelid)
				AND (bc.relname = '" . $table_name . "')
				AND (i.indisunique != 't')
				AND (i.indisprimary != 't')";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['index_name'] = $this->strip_table_name_from_index_name($table_name, $row['index_name']);

			$index_array[] = $row['index_name'];
		}
		$this->db->sql_freeresult($result);

		return array_map('strtolower', $index_array);
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_column_change($table_name, $column_name, $column_data, $inline = false)
	{
		$column_data = $this->sql_prepare_column_data($table_name, $column_name, $column_data);
		$statements = array();

		$sql = 'ALTER TABLE ' . $table_name . ' ';

		$sql_array = array();
		$sql_array[] = 'ALTER COLUMN ' . $column_name . ' TYPE ' . $column_data['column_type'];

		if (isset($column_data['null']))
		{
			if ($column_data['null'] == 'NOT NULL')
			{
				$sql_array[] = 'ALTER COLUMN ' . $column_name . ' SET NOT NULL';
			}
			else if ($column_data['null'] == 'NULL')
			{
				$sql_array[] = 'ALTER COLUMN ' . $column_name . ' DROP NOT NULL';
			}
		}

		if (isset($column_data['default']))
		{
			$sql_array[] = 'ALTER COLUMN ' . $column_name . ' SET DEFAULT ' . $column_data['default'];
		}

		// we don't want to double up on constraints if we change different number data types
		if (isset($column_data['constraint']))
		{
			$constraint_sql = "SELECT pg_get_constraintdef(pc.oid) AS constraint_data
				FROM pg_constraint pc, pg_class bc
				WHERE conrelid = bc.oid
					AND bc.relname = '" . $this->db->sql_escape($table_name) . "'
					AND NOT EXISTS (
						SELECT *
						FROM pg_constraint AS c, pg_inherits AS i
						WHERE i.inhrelid = pc.conrelid
							AND c.conname = pc.conname
							AND pg_get_constraintdef(c.oid) = pg_get_constraintdef(pc.oid)
							AND c.conrelid = i.inhparent
					)";

			$constraint_exists = false;

			$result = $this->db->sql_query($constraint_sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				if (trim($row['constraint_data']) == trim($column_data['constraint']))
				{
					$constraint_exists = true;
					break;
				}
			}
			$this->db->sql_freeresult($result);

			if (!$constraint_exists)
			{
				$sql_array[] = 'ADD ' . $column_data['constraint'];
			}
		}

		$sql .= implode(', ', $sql_array);

		$statements[] = $sql;

		return $this->_sql_run_sql($statements);
	}

	/**
	* Get a list with existing indexes for the column
	*
	* @param string $table_name
	* @param string $column_name
	* @param bool $unique Should we get unique indexes or normal ones
	* @return array		Array with Index name => columns
	*/
	public function get_existing_indexes($table_name, $column_name, $unique = false)
	{
		// Not supported
		throw new \Exception('DBMS is not supported');
	}
}
