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
class mssql extends tools
{
	/**
	 * Is the used MS SQL Server a SQL Server 2000?
	 * @var bool
	 */
	protected $is_sql_server_2000;

	/**
	 * Get the column types for mssql based databases
	 *
	 * @return array
	 */
	static public function get_dbms_type_map()
	{
		return array(
			'mssql'		=> array(
				'INT:'		=> '[int]',
				'BINT'		=> '[float]',
				'ULINT'		=> '[int]',
				'UINT'		=> '[int]',
				'UINT:'		=> '[int]',
				'TINT:'		=> '[int]',
				'USINT'		=> '[int]',
				'BOOL'		=> '[int]',
				'VCHAR'		=> '[varchar] (255)',
				'VCHAR:'	=> '[varchar] (%d)',
				'CHAR:'		=> '[char] (%d)',
				'XSTEXT'	=> '[varchar] (1000)',
				'STEXT'		=> '[varchar] (3000)',
				'TEXT'		=> '[varchar] (8000)',
				'MTEXT'		=> '[text]',
				'XSTEXT_UNI'=> '[nvarchar] (100)',
				'STEXT_UNI'	=> '[nvarchar] (255)',
				'TEXT_UNI'	=> '[nvarchar] (4000)',
				'MTEXT_UNI'	=> '[ntext]',
				'TIMESTAMP'	=> '[int]',
				'DECIMAL'	=> '[float]',
				'DECIMAL:'	=> '[float]',
				'PDECIMAL'	=> '[float]',
				'PDECIMAL:'	=> '[float]',
				'VCHAR_UNI'	=> '[nvarchar] (255)',
				'VCHAR_UNI:'=> '[nvarchar] (%d)',
				'VCHAR_CI'	=> '[nvarchar] (255)',
				'VARBINARY'	=> '[varchar] (255)',
			),

			'mssqlnative'	=> array(
				'INT:'		=> '[int]',
				'BINT'		=> '[float]',
				'ULINT'		=> '[int]',
				'UINT'		=> '[int]',
				'UINT:'		=> '[int]',
				'TINT:'		=> '[int]',
				'USINT'		=> '[int]',
				'BOOL'		=> '[int]',
				'VCHAR'		=> '[varchar] (255)',
				'VCHAR:'	=> '[varchar] (%d)',
				'CHAR:'		=> '[char] (%d)',
				'XSTEXT'	=> '[varchar] (1000)',
				'STEXT'		=> '[varchar] (3000)',
				'TEXT'		=> '[varchar] (8000)',
				'MTEXT'		=> '[text]',
				'XSTEXT_UNI'=> '[nvarchar] (100)',
				'STEXT_UNI'	=> '[nvarchar] (255)',
				'TEXT_UNI'	=> '[nvarchar] (4000)',
				'MTEXT_UNI'	=> '[ntext]',
				'TIMESTAMP'	=> '[int]',
				'DECIMAL'	=> '[float]',
				'DECIMAL:'	=> '[float]',
				'PDECIMAL'	=> '[float]',
				'PDECIMAL:'	=> '[float]',
				'VCHAR_UNI'	=> '[nvarchar] (255)',
				'VCHAR_UNI:'=> '[nvarchar] (%d)',
				'VCHAR_CI'	=> '[nvarchar] (255)',
				'VARBINARY'	=> '[varchar] (255)',
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
		switch ($this->db->get_sql_layer())
		{
			case 'mssql_odbc':
				$this->sql_layer = 'mssql';
			break;

			case 'mssqlnative':
				$this->sql_layer = 'mssqlnative';
			break;
		}

		$this->dbms_type_map = self::get_dbms_type_map();
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_list_tables()
	{
		$sql = "SELECT name
			FROM sysobjects
			WHERE type='U'";
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
		$table_sql = 'CREATE TABLE [' . $table_name . '] (' . "\n";

		if (!isset($table_data['PRIMARY_KEY']))
		{
			$table_data['COLUMNS']['mssqlindex'] = array('UINT', null, 'auto_increment');
			$table_data['PRIMARY_KEY'] = 'mssqlindex';
		}

		// Iterate through the columns to create a table
		foreach ($table_data['COLUMNS'] as $column_name => $column_data)
		{
			// here lies an array, filled with information compiled on the column's data
			$prepared_column = $this->sql_prepare_column_data($table_name, $column_name, $column_data);

			if (isset($prepared_column['auto_increment']) && $prepared_column['auto_increment'] && strlen($column_name) > 26) // "${column_name}_gen"
			{
				trigger_error("Index name '{$column_name}_gen' on table '$table_name' is too long. The maximum auto increment column length is 26 characters.", E_USER_ERROR);
			}

			// here we add the definition of the new column to the list of columns
			$columns[] = "\t [{$column_name}] " . $prepared_column['column_type_sql_default'];

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

		// Close the table for two DBMS and add to the statements
		$table_sql .= "\n);";
		$statements[] = $table_sql;

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

				// We need the data here
				$old_return_statements = $this->return_statements;
				$this->return_statements = true;

				$primary_key_stmts = $this->sql_create_primary_key($table_name, $table_data['PRIMARY_KEY']);
				foreach ($primary_key_stmts as $pk_stmt)
				{
					$statements[] = $pk_stmt;
				}

				$this->return_statements = $old_return_statements;
			}
		}

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

		$sql = "SELECT c.name
			FROM syscolumns c
			LEFT JOIN sysobjects o ON c.id = o.id
			WHERE o.name = '{$table_name}'";
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
		$sql = "EXEC sp_statistics '$table_name'";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['TYPE'] == 3)
			{
				if (strtolower($row['INDEX_NAME']) == strtolower($index_name))
				{
					$this->db->sql_freeresult($result);
					return true;
				}
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
		$sql = "EXEC sp_statistics '$table_name'";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Usually NON_UNIQUE is the column we want to check, but we allow for both
			if ($row['TYPE'] == 3)
			{
				if (strtolower($row['INDEX_NAME']) == strtolower($index_name))
				{
					$this->db->sql_freeresult($result);
					return true;
				}
			}
		}
		$this->db->sql_freeresult($result);

		return false;
	}

	/**
	 * {@inheritDoc}
	*/
	function sql_prepare_column_data($table_name, $column_name, $column_data)
	{
		if (strlen($column_name) > 30)
		{
			trigger_error("Column name '$column_name' on table '$table_name' is too long. The maximum is 30 characters.", E_USER_ERROR);
		}

		// Get type
		list($column_type, ) = $this->get_column_type($column_data[0]);

		// Adjust default value if db-dependent specified
		if (is_array($column_data[1]))
		{
			$column_data[1] = (isset($column_data[1][$this->sql_layer])) ? $column_data[1][$this->sql_layer] : $column_data[1]['default'];
		}

		$sql = '';

		$return_array = array();

		$sql .= " {$column_type} ";
		$sql_default = " {$column_type} ";

		// For adding columns we need the default definition
		if (!is_null($column_data[1]))
		{
			// For hexadecimal values do not use single quotes
			if (strpos($column_data[1], '0x') === 0)
			{
				$return_array['default'] = 'DEFAULT (' . $column_data[1] . ') ';
				$sql_default .= $return_array['default'];
			}
			else
			{
				$return_array['default'] = 'DEFAULT (' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ') ';
				$sql_default .= $return_array['default'];
			}
		}

		if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
		{
			// $sql .= 'IDENTITY (1, 1) ';
			$sql_default .= 'IDENTITY (1, 1) ';
		}

		$return_array['textimage'] = $column_type === '[text]';

		if (!is_null($column_data[1]) || (isset($column_data[2]) && $column_data[2] == 'auto_increment'))
		{
			$sql .= 'NOT NULL';
			$sql_default .= 'NOT NULL';
		}
		else
		{
			$sql .= 'NULL';
			$sql_default .= 'NULL';
		}

		$return_array['column_type_sql_default'] = $sql_default;

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
		$statements[] = 'ALTER TABLE [' . $table_name . '] ADD [' . $column_name . '] ' . $column_data['column_type_sql_default'];

		return $this->_sql_run_sql($statements);
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_column_remove($table_name, $column_name, $inline = false)
	{
		$statements = array();

		// We need the data here
		$old_return_statements = $this->return_statements;
		$this->return_statements = true;

		$indexes = $this->get_existing_indexes($table_name, $column_name);
		$indexes = array_merge($indexes, $this->get_existing_indexes($table_name, $column_name, true));

		// Drop any indexes
		$recreate_indexes = array();
		if (!empty($indexes))
		{
			foreach ($indexes as $index_name => $index_data)
			{
				$result = $this->sql_index_drop($table_name, $index_name);
				$statements = array_merge($statements, $result);
				if (count($index_data) > 1)
				{
					// Remove this column from the index and recreate it
					$recreate_indexes[$index_name] = array_diff($index_data, array($column_name));
				}
			}
		}

		// Drop primary keys depending on this column
		$result = $this->mssql_get_drop_default_primary_key_queries($table_name, $column_name);
		$statements = array_merge($statements, $result);

		// Drop default value constraint
		$result = $this->mssql_get_drop_default_constraints_queries($table_name, $column_name);
		$statements = array_merge($statements, $result);

		// Remove the column
		$statements[] = 'ALTER TABLE [' . $table_name . '] DROP COLUMN [' . $column_name . ']';

		if (!empty($recreate_indexes))
		{
			// Recreate indexes after we removed the column
			foreach ($recreate_indexes as $index_name => $index_data)
			{
				$result = $this->sql_create_index($table_name, $index_name, $index_data);
				$statements = array_merge($statements, $result);
			}
		}

		$this->return_statements = $old_return_statements;

		return $this->_sql_run_sql($statements);
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_index_drop($table_name, $index_name)
	{
		$statements = array();

		$statements[] = 'DROP INDEX [' . $table_name . '].[' . $index_name . ']';

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

		return $this->_sql_run_sql($statements);
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_create_primary_key($table_name, $column, $inline = false)
	{
		$statements = array();

		$sql = "ALTER TABLE [{$table_name}] WITH NOCHECK ADD ";
		$sql .= "CONSTRAINT [PK_{$table_name}] PRIMARY KEY  CLUSTERED (";
		$sql .= '[' . implode("],\n\t\t[", $column) . ']';
		$sql .= ')';

		$statements[] = $sql;

		return $this->_sql_run_sql($statements);
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_create_unique_index($table_name, $index_name, $column)
	{
		$statements = array();

		if ($this->mssql_is_sql_server_2000())
		{
			$this->check_index_name_length($table_name, $index_name);
		}

		$statements[] = 'CREATE UNIQUE INDEX [' . $index_name . '] ON [' . $table_name . ']([' . implode('], [', $column) . '])';

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

		$statements[] = 'CREATE INDEX [' . $index_name . '] ON [' . $table_name . ']([' . implode('], [', $column) . '])';

		return $this->_sql_run_sql($statements);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_max_index_name_length()
	{
		if ($this->mssql_is_sql_server_2000())
		{
			return parent::get_max_index_name_length();
		}
		else
		{
			return 128;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_list_index($table_name)
	{
		$index_array = array();
		$sql = "EXEC sp_statistics '$table_name'";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['TYPE'] == 3)
			{
				$index_array[] = strtolower($row['INDEX_NAME']);
			}
		}
		$this->db->sql_freeresult($result);

		return $index_array;
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_column_change($table_name, $column_name, $column_data, $inline = false)
	{
		$column_data = $this->sql_prepare_column_data($table_name, $column_name, $column_data);
		$statements = array();

		// We need the data here
		$old_return_statements = $this->return_statements;
		$this->return_statements = true;

		$indexes = $this->get_existing_indexes($table_name, $column_name);
		$unique_indexes = $this->get_existing_indexes($table_name, $column_name, true);

		// Drop any indexes
		if (!empty($indexes) || !empty($unique_indexes))
		{
			$drop_indexes = array_merge(array_keys($indexes), array_keys($unique_indexes));
			foreach ($drop_indexes as $index_name)
			{
				$result = $this->sql_index_drop($table_name, $index_name);
				$statements = array_merge($statements, $result);
			}
		}

		// Drop default value constraint
		$result = $this->mssql_get_drop_default_constraints_queries($table_name, $column_name);
		$statements = array_merge($statements, $result);

		// Change the column
		$statements[] = 'ALTER TABLE [' . $table_name . '] ALTER COLUMN [' . $column_name . '] ' . $column_data['column_type_sql'];

		if (!empty($column_data['default']) && !$this->mssql_is_column_identity($table_name, $column_name))
		{
			// Add new default value constraint
			$statements[] = 'ALTER TABLE [' . $table_name . '] ADD CONSTRAINT [DF_' . $table_name . '_' . $column_name . '_1] ' . $column_data['default'] . ' FOR [' . $column_name . ']';
		}

		if (!empty($indexes))
		{
			// Recreate indexes after we changed the column
			foreach ($indexes as $index_name => $index_data)
			{
				$result = $this->sql_create_index($table_name, $index_name, $index_data);
				$statements = array_merge($statements, $result);
			}
		}

		if (!empty($unique_indexes))
		{
			// Recreate unique indexes after we changed the column
			foreach ($unique_indexes as $index_name => $index_data)
			{
				$result = $this->sql_create_unique_index($table_name, $index_name, $index_data);
				$statements = array_merge($statements, $result);
			}
		}

		$this->return_statements = $old_return_statements;

		return $this->_sql_run_sql($statements);
	}

	/**
	* Get queries to drop the default constraints of a column
	*
	* We need to drop the default constraints of a column,
	* before being able to change their type or deleting them.
	*
	* @param string $table_name
	* @param string $column_name
	* @return array		Array with SQL statements
	*/
	protected function mssql_get_drop_default_constraints_queries($table_name, $column_name)
	{
		$statements = array();
		if ($this->mssql_is_sql_server_2000())
		{
			// http://msdn.microsoft.com/en-us/library/aa175912%28v=sql.80%29.aspx
			// Deprecated in SQL Server 2005
			$sql = "SELECT so.name AS def_name
				FROM sysobjects so
				JOIN sysconstraints sc ON so.id = sc.constid
				WHERE object_name(so.parent_obj) = '{$table_name}'
					AND so.xtype = 'D'
					AND sc.colid = (SELECT colid FROM syscolumns
						WHERE id = object_id('{$table_name}')
							AND name = '{$column_name}')";
		}
		else
		{
			$sql = "SELECT dobj.name AS def_name
				FROM sys.columns col
					LEFT OUTER JOIN sys.objects dobj ON (dobj.object_id = col.default_object_id AND dobj.type = 'D')
				WHERE col.object_id = object_id('{$table_name}')
					AND col.name = '{$column_name}'
					AND dobj.name IS NOT NULL";
		}

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$statements[] = 'ALTER TABLE [' . $table_name . '] DROP CONSTRAINT [' . $row['def_name'] . ']';
		}
		$this->db->sql_freeresult($result);

		return $statements;
	}

	/**
	 * Get queries to drop the primary keys depending on the specified column
	 *
	 * We need to drop primary keys depending on this column before being able
	 * to delete them.
	 *
	 * @param string $table_name
	 * @param string $column_name
	 * @return array		Array with SQL statements
	 */
	protected function mssql_get_drop_default_primary_key_queries($table_name, $column_name)
	{
		$statements = array();

		$sql = "SELECT ccu.CONSTRAINT_NAME, ccu.COLUMN_NAME
			FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
				JOIN INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE ccu ON tc.CONSTRAINT_NAME = ccu.Constraint_name
			WHERE tc.TABLE_NAME = '{$table_name}'
				AND tc.CONSTRAINT_TYPE = 'Primary Key'
				AND ccu.COLUMN_NAME = '{$column_name}'";

		$result = $this->db->sql_query($sql);

		while ($primary_key = $this->db->sql_fetchrow($result))
		{
			$statements[] = 'ALTER TABLE [' . $table_name . '] DROP CONSTRAINT [' . $primary_key['CONSTRAINT_NAME'] . ']';
		}
		$this->db->sql_freeresult($result);

		return $statements;
	}

	/**
	 * Checks to see if column is an identity column
	 *
	 * Identity columns cannot have defaults set for them.
	 *
	 * @param string $table_name
	 * @param string $column_name
	 * @return bool		true if identity, false if not
	 */
	protected function mssql_is_column_identity($table_name, $column_name)
	{
		if ($this->mssql_is_sql_server_2000())
		{
			// http://msdn.microsoft.com/en-us/library/aa175912%28v=sql.80%29.aspx
			// Deprecated in SQL Server 2005
			$sql = "SELECT COLUMNPROPERTY(object_id('{$table_name}'), '{$column_name}', 'IsIdentity') AS is_identity";
		}
		else
		{
			$sql = "SELECT is_identity FROM sys.columns
					WHERE object_id = object_id('{$table_name}')
					AND name = '{$column_name}'";
		}

		$result = $this->db->sql_query($sql);
		$is_identity = $this->db->sql_fetchfield('is_identity');
		$this->db->sql_freeresult($result);

		return (bool) $is_identity;
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
		$existing_indexes = array();
		if ($this->mssql_is_sql_server_2000())
		{
			// http://msdn.microsoft.com/en-us/library/aa175912%28v=sql.80%29.aspx
			// Deprecated in SQL Server 2005
			$sql = "SELECT DISTINCT ix.name AS phpbb_index_name
				FROM sysindexes ix
				INNER JOIN sysindexkeys ixc
					ON ixc.id = ix.id
						AND ixc.indid = ix.indid
				INNER JOIN syscolumns cols
					ON cols.colid = ixc.colid
						AND cols.id = ix.id
				WHERE ix.id = object_id('{$table_name}')
					AND cols.name = '{$column_name}'
					AND INDEXPROPERTY(ix.id, ix.name, 'IsUnique') = " . ($unique ? '1' : '0');
		}
		else
		{
			$sql = "SELECT DISTINCT ix.name AS phpbb_index_name
				FROM sys.indexes ix
				INNER JOIN sys.index_columns ixc
					ON ixc.object_id = ix.object_id
						AND ixc.index_id = ix.index_id
				INNER JOIN sys.columns cols
					ON cols.column_id = ixc.column_id
						AND cols.object_id = ix.object_id
				WHERE ix.object_id = object_id('{$table_name}')
					AND cols.name = '{$column_name}'
					AND ix.is_primary_key = 0
					AND ix.is_unique = " . ($unique ? '1' : '0');
		}

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!isset($row['is_unique']) || ($unique && $row['is_unique'] == 'UNIQUE') || (!$unique && $row['is_unique'] == 'NONUNIQUE'))
			{
				$existing_indexes[$row['phpbb_index_name']] = array();
			}
		}
		$this->db->sql_freeresult($result);

		if (empty($existing_indexes))
		{
			return array();
		}

		if ($this->mssql_is_sql_server_2000())
		{
			$sql = "SELECT DISTINCT ix.name AS phpbb_index_name, cols.name AS phpbb_column_name
				FROM sysindexes ix
				INNER JOIN sysindexkeys ixc
					ON ixc.id = ix.id
						AND ixc.indid = ix.indid
				INNER JOIN syscolumns cols
					ON cols.colid = ixc.colid
						AND cols.id = ix.id
				WHERE ix.id = object_id('{$table_name}')
					AND " . $this->db->sql_in_set('ix.name', array_keys($existing_indexes));
		}
		else
		{
			$sql = "SELECT DISTINCT ix.name AS phpbb_index_name, cols.name AS phpbb_column_name
				FROM sys.indexes ix
				INNER JOIN sys.index_columns ixc
					ON ixc.object_id = ix.object_id
						AND ixc.index_id = ix.index_id
				INNER JOIN sys.columns cols
					ON cols.column_id = ixc.column_id
						AND cols.object_id = ix.object_id
				WHERE ix.object_id = object_id('{$table_name}')
					AND " . $this->db->sql_in_set('ix.name', array_keys($existing_indexes));
		}

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$existing_indexes[$row['phpbb_index_name']][] = $row['phpbb_column_name'];
		}
		$this->db->sql_freeresult($result);

		return $existing_indexes;
	}

	/**
	* Is the used MS SQL Server a SQL Server 2000?
	*
	* @return bool
	*/
	protected function mssql_is_sql_server_2000()
	{
		if ($this->is_sql_server_2000 === null)
		{
			$sql = "SELECT CAST(SERVERPROPERTY('productversion') AS VARCHAR(25)) AS mssql_version";
			$result = $this->db->sql_query($sql);
			$properties = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
			$this->is_sql_server_2000 = $properties['mssql_version'][0] == '8';
		}

		return $this->is_sql_server_2000;
	}

}
