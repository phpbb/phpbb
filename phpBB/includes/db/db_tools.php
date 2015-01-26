<?php
/**
*
* @package dbal
* @version $Id$
* @copyright (c) 2007 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Database Tools for handling cross-db actions such as altering columns, etc.
* Currently not supported is returning SQL for creating tables.
*
* @package dbal
* @note currently not used within phpBB3, but may be utilized later.
*/
class phpbb_db_tools
{
	/**
	* Current sql layer
	*/
	var $sql_layer = '';

	/**
	* @var object DB object
	*/
	var $db = NULL;

	/**
	* The Column types for every database we support
	* @var array
	*/
	var $dbms_type_map = array(
		'mysql_41'	=> array(
			'INT:'		=> 'int(%d)',
			'BINT'		=> 'bigint(20)',
			'UINT'		=> 'mediumint(8) UNSIGNED',
			'UINT:'		=> 'int(%d) UNSIGNED',
			'TINT:'		=> 'tinyint(%d)',
			'USINT'		=> 'smallint(4) UNSIGNED',
			'BOOL'		=> 'tinyint(1) UNSIGNED',
			'VCHAR'		=> 'varchar(255)',
			'VCHAR:'	=> 'varchar(%d)',
			'CHAR:'		=> 'char(%d)',
			'XSTEXT'	=> 'text',
			'XSTEXT_UNI'=> 'varchar(100)',
			'STEXT'		=> 'text',
			'STEXT_UNI'	=> 'varchar(255)',
			'TEXT'		=> 'text',
			'TEXT_UNI'	=> 'text',
			'MTEXT'		=> 'mediumtext',
			'MTEXT_UNI'	=> 'mediumtext',
			'TIMESTAMP'	=> 'int(11) UNSIGNED',
			'DECIMAL'	=> 'decimal(5,2)',
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
			'VCHAR_UNI'	=> 'varchar(255)',
			'VCHAR_UNI:'=> 'varchar(%d)',
			'VCHAR_CI'	=> 'varchar(255)',
			'VARBINARY'	=> 'varbinary(255)',
		),

		'mysql_40'	=> array(
			'INT:'		=> 'int(%d)',
			'BINT'		=> 'bigint(20)',
			'UINT'		=> 'mediumint(8) UNSIGNED',
			'UINT:'		=> 'int(%d) UNSIGNED',
			'TINT:'		=> 'tinyint(%d)',
			'USINT'		=> 'smallint(4) UNSIGNED',
			'BOOL'		=> 'tinyint(1) UNSIGNED',
			'VCHAR'		=> 'varbinary(255)',
			'VCHAR:'	=> 'varbinary(%d)',
			'CHAR:'		=> 'binary(%d)',
			'XSTEXT'	=> 'blob',
			'XSTEXT_UNI'=> 'blob',
			'STEXT'		=> 'blob',
			'STEXT_UNI'	=> 'blob',
			'TEXT'		=> 'blob',
			'TEXT_UNI'	=> 'blob',
			'MTEXT'		=> 'mediumblob',
			'MTEXT_UNI'	=> 'mediumblob',
			'TIMESTAMP'	=> 'int(11) UNSIGNED',
			'DECIMAL'	=> 'decimal(5,2)',
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
			'VCHAR_UNI'	=> 'blob',
			'VCHAR_UNI:'=> array('varbinary(%d)', 'limit' => array('mult', 3, 255, 'blob')),
			'VCHAR_CI'	=> 'blob',
			'VARBINARY'	=> 'varbinary(255)',
		),

		'firebird'	=> array(
			'INT:'		=> 'INTEGER',
			'BINT'		=> 'DOUBLE PRECISION',
			'UINT'		=> 'INTEGER',
			'UINT:'		=> 'INTEGER',
			'TINT:'		=> 'INTEGER',
			'USINT'		=> 'INTEGER',
			'BOOL'		=> 'INTEGER',
			'VCHAR'		=> 'VARCHAR(255) CHARACTER SET NONE',
			'VCHAR:'	=> 'VARCHAR(%d) CHARACTER SET NONE',
			'CHAR:'		=> 'CHAR(%d) CHARACTER SET NONE',
			'XSTEXT'	=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
			'STEXT'		=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
			'TEXT'		=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
			'MTEXT'		=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
			'XSTEXT_UNI'=> 'VARCHAR(100) CHARACTER SET UTF8',
			'STEXT_UNI'	=> 'VARCHAR(255) CHARACTER SET UTF8',
			'TEXT_UNI'	=> 'BLOB SUB_TYPE TEXT CHARACTER SET UTF8',
			'MTEXT_UNI'	=> 'BLOB SUB_TYPE TEXT CHARACTER SET UTF8',
			'TIMESTAMP'	=> 'INTEGER',
			'DECIMAL'	=> 'DOUBLE PRECISION',
			'DECIMAL:'	=> 'DOUBLE PRECISION',
			'PDECIMAL'	=> 'DOUBLE PRECISION',
			'PDECIMAL:'	=> 'DOUBLE PRECISION',
			'VCHAR_UNI'	=> 'VARCHAR(255) CHARACTER SET UTF8',
			'VCHAR_UNI:'=> 'VARCHAR(%d) CHARACTER SET UTF8',
			'VCHAR_CI'	=> 'VARCHAR(255) CHARACTER SET UTF8',
			'VARBINARY'	=> 'CHAR(255) CHARACTER SET NONE',
		),

		'mssql'		=> array(
			'INT:'		=> '[int]',
			'BINT'		=> '[float]',
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
			'XSTEXT_UNI'=> '[varchar] (100)',
			'STEXT_UNI'	=> '[varchar] (255)',
			'TEXT_UNI'	=> '[varchar] (4000)',
			'MTEXT_UNI'	=> '[text]',
			'TIMESTAMP'	=> '[int]',
			'DECIMAL'	=> '[float]',
			'DECIMAL:'	=> '[float]',
			'PDECIMAL'	=> '[float]',
			'PDECIMAL:'	=> '[float]',
			'VCHAR_UNI'	=> '[varchar] (255)',
			'VCHAR_UNI:'=> '[varchar] (%d)',
			'VCHAR_CI'	=> '[varchar] (255)',
			'VARBINARY'	=> '[varchar] (255)',
		),

		'mssqlnative'	=> array(
			'INT:'		=> '[int]',
			'BINT'		=> '[float]',
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
			'XSTEXT_UNI'=> '[varchar] (100)',
			'STEXT_UNI'	=> '[varchar] (255)',
			'TEXT_UNI'	=> '[varchar] (4000)',
			'MTEXT_UNI'	=> '[text]',
			'TIMESTAMP'	=> '[int]',
			'DECIMAL'	=> '[float]',
			'DECIMAL:'	=> '[float]',
			'PDECIMAL'	=> '[float]',
			'PDECIMAL:'	=> '[float]',
			'VCHAR_UNI'	=> '[varchar] (255)',
			'VCHAR_UNI:'=> '[varchar] (%d)',
			'VCHAR_CI'	=> '[varchar] (255)',
			'VARBINARY'	=> '[varchar] (255)',
		),

		'oracle'	=> array(
			'INT:'		=> 'number(%d)',
			'BINT'		=> 'number(20)',
			'UINT'		=> 'number(8)',
			'UINT:'		=> 'number(%d)',
			'TINT:'		=> 'number(%d)',
			'USINT'		=> 'number(4)',
			'BOOL'		=> 'number(1)',
			'VCHAR'		=> 'varchar2(255)',
			'VCHAR:'	=> 'varchar2(%d)',
			'CHAR:'		=> 'char(%d)',
			'XSTEXT'	=> 'varchar2(1000)',
			'STEXT'		=> 'varchar2(3000)',
			'TEXT'		=> 'clob',
			'MTEXT'		=> 'clob',
			'XSTEXT_UNI'=> 'varchar2(300)',
			'STEXT_UNI'	=> 'varchar2(765)',
			'TEXT_UNI'	=> 'clob',
			'MTEXT_UNI'	=> 'clob',
			'TIMESTAMP'	=> 'number(11)',
			'DECIMAL'	=> 'number(5, 2)',
			'DECIMAL:'	=> 'number(%d, 2)',
			'PDECIMAL'	=> 'number(6, 3)',
			'PDECIMAL:'	=> 'number(%d, 3)',
			'VCHAR_UNI'	=> 'varchar2(765)',
			'VCHAR_UNI:'=> array('varchar2(%d)', 'limit' => array('mult', 3, 765, 'clob')),
			'VCHAR_CI'	=> 'varchar2(255)',
			'VARBINARY'	=> 'raw(255)',
		),

		'sqlite'	=> array(
			'INT:'		=> 'int(%d)',
			'BINT'		=> 'bigint(20)',
			'UINT'		=> 'INTEGER UNSIGNED', //'mediumint(8) UNSIGNED',
			'UINT:'		=> 'INTEGER UNSIGNED', // 'int(%d) UNSIGNED',
			'TINT:'		=> 'tinyint(%d)',
			'USINT'		=> 'INTEGER UNSIGNED', //'mediumint(4) UNSIGNED',
			'BOOL'		=> 'INTEGER UNSIGNED', //'tinyint(1) UNSIGNED',
			'VCHAR'		=> 'varchar(255)',
			'VCHAR:'	=> 'varchar(%d)',
			'CHAR:'		=> 'char(%d)',
			'XSTEXT'	=> 'text(65535)',
			'STEXT'		=> 'text(65535)',
			'TEXT'		=> 'text(65535)',
			'MTEXT'		=> 'mediumtext(16777215)',
			'XSTEXT_UNI'=> 'text(65535)',
			'STEXT_UNI'	=> 'text(65535)',
			'TEXT_UNI'	=> 'text(65535)',
			'MTEXT_UNI'	=> 'mediumtext(16777215)',
			'TIMESTAMP'	=> 'INTEGER UNSIGNED', //'int(11) UNSIGNED',
			'DECIMAL'	=> 'decimal(5,2)',
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
			'VCHAR_UNI'	=> 'varchar(255)',
			'VCHAR_UNI:'=> 'varchar(%d)',
			'VCHAR_CI'	=> 'varchar(255)',
			'VARBINARY'	=> 'blob',
		),

		'postgres'	=> array(
			'INT:'		=> 'INT4',
			'BINT'		=> 'INT8',
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

	/**
	* A list of types being unsigned for better reference in some db's
	* @var array
	*/
	var $unsigned_types = array('UINT', 'UINT:', 'USINT', 'BOOL', 'TIMESTAMP');

	/**
	* A list of supported DBMS. We change this class to support more DBMS, the DBMS itself only need to follow some rules.
	* @var array
	*/
	var $supported_dbms = array('firebird', 'mssql', 'mssqlnative', 'mysql_40', 'mysql_41', 'oracle', 'postgres', 'sqlite');

	/**
	* This is set to true if user only wants to return the 'to-be-executed' SQL statement(s) (as an array).
	* This mode has no effect on some methods (inserting of data for example). This is expressed within the methods command.
	*/
	var $return_statements = false;

	/**
	* Constructor. Set DB Object and set {@link $return_statements return_statements}.
	*
	* @param phpbb_dbal	$db					DBAL object
	* @param bool		$return_statements	True if only statements should be returned and no SQL being executed
	*/
	function phpbb_db_tools(&$db, $return_statements = false)
	{
		$this->db = $db;
		$this->return_statements = $return_statements;

		// Determine mapping database type
		switch ($this->db->sql_layer)
		{
			case 'mysql':
				$this->sql_layer = 'mysql_40';
			break;

			case 'mysql4':
				if (version_compare($this->db->sql_server_info(true), '4.1.3', '>='))
				{
					$this->sql_layer = 'mysql_41';
				}
				else
				{
					$this->sql_layer = 'mysql_40';
				}
			break;

			case 'mysqli':
				$this->sql_layer = 'mysql_41';
			break;

			case 'mssql':
			case 'mssql_odbc':
				$this->sql_layer = 'mssql';
			break;

			case 'mssqlnative':
				$this->sql_layer = 'mssqlnative';
			break;

			default:
				$this->sql_layer = $this->db->sql_layer;
			break;
		}
	}

	/**
	* Gets a list of tables in the database.
	*
	* @return array		Array of table names  (all lower case)
	*/
	function sql_list_tables()
	{
		switch ($this->db->sql_layer)
		{
			case 'mysql':
			case 'mysql4':
			case 'mysqli':
				$sql = 'SHOW TABLES';
			break;

			case 'sqlite':
				$sql = 'SELECT name
					FROM sqlite_master
					WHERE type = "table"';
			break;

			case 'mssql':
			case 'mssql_odbc':
			case 'mssqlnative':
				$sql = "SELECT name
					FROM sysobjects
					WHERE type='U'";
			break;

			case 'postgres':
				$sql = 'SELECT relname
					FROM pg_stat_user_tables';
			break;

			case 'firebird':
				$sql = 'SELECT rdb$relation_name
					FROM rdb$relations
					WHERE rdb$view_source is null
						AND rdb$system_flag = 0';
			break;

			case 'oracle':
				$sql = 'SELECT table_name
					FROM USER_TABLES';
			break;
		}

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
	* Check if table exists
	*
	*
	* @param string	$table_name	The table name to check for
	* @return bool true if table exists, else false
	*/
	function sql_table_exists($table_name)
	{
		$this->db->sql_return_on_error(true);
		$result = $this->db->sql_query_limit('SELECT * FROM ' . $table_name, 1);
		$this->db->sql_return_on_error(false);

		if ($result)
		{
			$this->db->sql_freeresult($result);
			return true;
		}

		return false;
	}

	/**
	* Create SQL Table
	*
	* @param string	$table_name	The table name to create
	* @param array	$table_data	Array containing table data.
	* @return array	Statements if $return_statements is true.
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
		switch ($this->sql_layer)
		{
			case 'mssql':
			case 'mssqlnative':
				$table_sql = 'CREATE TABLE [' . $table_name . '] (' . "\n";
			break;

			default:
				$table_sql = 'CREATE TABLE ' . $table_name . ' (' . "\n";
			break;
		}

		if ($this->sql_layer == 'mssql' || $this->sql_layer == 'mssqlnative')
		{
			if (!isset($table_data['PRIMARY_KEY']))
			{
				$table_data['COLUMNS']['mssqlindex'] = array('UINT', null, 'auto_increment');
				$table_data['PRIMARY_KEY'] = 'mssqlindex';
			}
		}

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
			switch ($this->sql_layer)
			{
				case 'mssql':
				case 'mssqlnative':
					$columns[] = "\t [{$column_name}] " . $prepared_column['column_type_sql_default'];
				break;

				default:
					$columns[] = "\t {$column_name} " . $prepared_column['column_type_sql'];
				break;
			}

			// see if we have found a primary key set due to a column definition if we have found it, we can stop looking
			if (!$primary_key_gen)
			{
				$primary_key_gen = isset($prepared_column['primary_key_set']) && $prepared_column['primary_key_set'];
			}

			// create sequence DDL based off of the existance of auto incrementing columns
			if (!$create_sequence && isset($prepared_column['auto_increment']) && $prepared_column['auto_increment'])
			{
				$create_sequence = $column_name;
			}
		}

		// this makes up all the columns in the create table statement
		$table_sql .= implode(",\n", $columns);

		// Close the table for two DBMS and add to the statements
		switch ($this->sql_layer)
		{
			case 'firebird':
			case 'mssql':
			case 'mssqlnative':
				$table_sql .= "\n);";
				$statements[] = $table_sql;
			break;
		}

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

				switch ($this->sql_layer)
				{
					case 'mysql_40':
					case 'mysql_41':
					case 'postgres':
					case 'sqlite':
						$table_sql .= ",\n\t PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . ')';
					break;

					case 'firebird':
					case 'mssql':
					case 'mssqlnative':
						// We need the data here
						$old_return_statements = $this->return_statements;
						$this->return_statements = true;

						$primary_key_stmts = $this->sql_create_primary_key($table_name, $table_data['PRIMARY_KEY']);
						foreach ($primary_key_stmts as $pk_stmt)
						{
							$statements[] = $pk_stmt;
						}

						$this->return_statements = $old_return_statements;
					break;

					case 'oracle':
						$table_sql .= ",\n\t CONSTRAINT pk_{$table_name} PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . ')';
					break;
				}
			}
		}

		// close the table
		switch ($this->sql_layer)
		{
			case 'mysql_41':
				// make sure the table is in UTF-8 mode
				$table_sql .= "\n) CHARACTER SET `utf8` COLLATE `utf8_bin`;";
				$statements[] = $table_sql;
			break;

			case 'mysql_40':
			case 'sqlite':
				$table_sql .= "\n);";
				$statements[] = $table_sql;
			break;

			case 'postgres':
				// do we need to add a sequence for auto incrementing columns?
				if ($create_sequence)
				{
					$statements[] = "CREATE SEQUENCE {$table_name}_seq;";
				}

				$table_sql .= "\n);";
				$statements[] = $table_sql;
			break;

			case 'oracle':
				$table_sql .= "\n)";
				$statements[] = $table_sql;

				// do we need to add a sequence and a tigger for auto incrementing columns?
				if ($create_sequence)
				{
					// create the actual sequence
					$statements[] = "CREATE SEQUENCE {$table_name}_seq";

					// the trigger is the mechanism by which we increment the counter
					$trigger = "CREATE OR REPLACE TRIGGER t_{$table_name}\n";
					$trigger .= "BEFORE INSERT ON {$table_name}\n";
					$trigger .= "FOR EACH ROW WHEN (\n";
					$trigger .= "\tnew.{$create_sequence} IS NULL OR new.{$create_sequence} = 0\n";
					$trigger .= ")\n";
					$trigger .= "BEGIN\n";
					$trigger .= "\tSELECT {$table_name}_seq.nextval\n";
					$trigger .= "\tINTO :new.{$create_sequence}\n";
					$trigger .= "\tFROM dual;\n";
					$trigger .= "END;";

					$statements[] = $trigger;
				}
			break;

			case 'firebird':
				if ($create_sequence)
				{
					$statements[] = "CREATE GENERATOR {$table_name}_gen;";
					$statements[] = "SET GENERATOR {$table_name}_gen TO 0;";

					$trigger = "CREATE TRIGGER t_$table_name FOR $table_name\n";
					$trigger .= "BEFORE INSERT\nAS\nBEGIN\n";
					$trigger .= "\tNEW.{$create_sequence} = GEN_ID({$table_name}_gen, 1);\nEND;";
					$statements[] = $trigger;
				}
			break;
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
	* Handle passed database update array.
	* Expected structure...
	* Key being one of the following
	*	change_columns: Column changes (only type, not name)
	*	add_columns: Add columns to a table
	*	drop_keys: Dropping keys
	*	drop_columns: Removing/Dropping columns
	*	add_primary_keys: adding primary keys
	*	add_unique_index: adding an unique index
	*	add_index: adding an index (can be column:index_size if you need to provide size)
	*
	* The values are in this format:
	*		{TABLE NAME}		=> array(
	*			{COLUMN NAME}		=> array({COLUMN TYPE}, {DEFAULT VALUE}, {OPTIONAL VARIABLES}),
	*			{KEY/INDEX NAME}	=> array({COLUMN NAMES}),
	*		)
	*
	* For more information have a look at /develop/create_schema_files.php (only available through SVN)
	*/
	function perform_schema_changes($schema_changes)
	{
		if (empty($schema_changes))
		{
			return;
		}

		$statements = array();
		$sqlite = false;

		// For SQLite we need to perform the schema changes in a much more different way
		if ($this->db->sql_layer == 'sqlite' && $this->return_statements)
		{
			$sqlite_data = array();
			$sqlite = true;
		}

		// Drop tables?
		if (!empty($schema_changes['drop_tables']))
		{
			foreach ($schema_changes['drop_tables'] as $table)
			{
				// only drop table if it exists
				if ($this->sql_table_exists($table))
				{
					$result = $this->sql_table_drop($table);
					if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
					}
				}
			}
		}

		// Add tables?
		if (!empty($schema_changes['add_tables']))
		{
			foreach ($schema_changes['add_tables'] as $table => $table_data)
			{
				$result = $this->sql_create_table($table, $table_data);
				if ($this->return_statements)
				{
					$statements = array_merge($statements, $result);
				}
			}
		}

		// Change columns?
		if (!empty($schema_changes['change_columns']))
		{
			foreach ($schema_changes['change_columns'] as $table => $columns)
			{
				foreach ($columns as $column_name => $column_data)
				{
					// If the column exists we change it, else we add it ;)
					if ($column_exists = $this->sql_column_exists($table, $column_name))
					{
						$result = $this->sql_column_change($table, $column_name, $column_data, true);
					}
					else
					{
						$result = $this->sql_column_add($table, $column_name, $column_data, true);
					}

					if ($sqlite)
					{
						if ($column_exists)
						{
							$sqlite_data[$table]['change_columns'][] = $result;
						}
						else
						{
							$sqlite_data[$table]['add_columns'][] = $result;
						}
					}
					else if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
					}
				}
			}
		}

		// Add columns?
		if (!empty($schema_changes['add_columns']))
		{
			foreach ($schema_changes['add_columns'] as $table => $columns)
			{
				foreach ($columns as $column_name => $column_data)
				{
					// Only add the column if it does not exist yet
					if ($column_exists = $this->sql_column_exists($table, $column_name))
					{
						continue;
						// This is commented out here because it can take tremendous time on updates
//						$result = $this->sql_column_change($table, $column_name, $column_data, true);
					}
					else
					{
						$result = $this->sql_column_add($table, $column_name, $column_data, true);
					}

					if ($sqlite)
					{
						if ($column_exists)
						{
							continue;
//							$sqlite_data[$table]['change_columns'][] = $result;
						}
						else
						{
							$sqlite_data[$table]['add_columns'][] = $result;
						}
					}
					else if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
					}
				}
			}
		}

		// Remove keys?
		if (!empty($schema_changes['drop_keys']))
		{
			foreach ($schema_changes['drop_keys'] as $table => $indexes)
			{
				foreach ($indexes as $index_name)
				{
					if (!$this->sql_index_exists($table, $index_name))
					{
						continue;
					}

					$result = $this->sql_index_drop($table, $index_name);

					if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
					}
				}
			}
		}

		// Drop columns?
		if (!empty($schema_changes['drop_columns']))
		{
			foreach ($schema_changes['drop_columns'] as $table => $columns)
			{
				foreach ($columns as $column)
				{
					// Only remove the column if it exists...
					if ($this->sql_column_exists($table, $column))
					{
						$result = $this->sql_column_remove($table, $column, true);

						if ($sqlite)
						{
							$sqlite_data[$table]['drop_columns'][] = $result;
						}
						else if ($this->return_statements)
						{
							$statements = array_merge($statements, $result);
						}
					}
				}
			}
		}

		// Add primary keys?
		if (!empty($schema_changes['add_primary_keys']))
		{
			foreach ($schema_changes['add_primary_keys'] as $table => $columns)
			{
				$result = $this->sql_create_primary_key($table, $columns, true);

				if ($sqlite)
				{
					$sqlite_data[$table]['primary_key'] = $result;
				}
				else if ($this->return_statements)
				{
					$statements = array_merge($statements, $result);
				}
			}
		}

		// Add unique indexes?
		if (!empty($schema_changes['add_unique_index']))
		{
			foreach ($schema_changes['add_unique_index'] as $table => $index_array)
			{
				foreach ($index_array as $index_name => $column)
				{
					if ($this->sql_unique_index_exists($table, $index_name))
					{
						continue;
					}

					$result = $this->sql_create_unique_index($table, $index_name, $column);

					if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
					}
				}
			}
		}

		// Add indexes?
		if (!empty($schema_changes['add_index']))
		{
			foreach ($schema_changes['add_index'] as $table => $index_array)
			{
				foreach ($index_array as $index_name => $column)
				{
					if ($this->sql_index_exists($table, $index_name))
					{
						continue;
					}

					$result = $this->sql_create_index($table, $index_name, $column);

					if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
					}
				}
			}
		}

		if ($sqlite)
		{
			foreach ($sqlite_data as $table_name => $sql_schema_changes)
			{
				// Create temporary table with original data
				$statements[] = 'begin';

				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table_name}'
					ORDER BY type DESC, name;";
				$result = $this->db->sql_query($sql);

				if (!$result)
				{
					continue;
				}

				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				// Create a backup table and populate it, destroy the existing one
				$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']);
				$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
				$statements[] = 'DROP TABLE ' . $table_name;

				// Get the columns...
				preg_match('#\((.*)\)#s', $row['sql'], $matches);

				$plain_table_cols = trim($matches[1]);
				$new_table_cols = preg_split('/,(?![\s\w]+\))/m', $plain_table_cols);
				$column_list = array();

				foreach ($new_table_cols as $declaration)
				{
					$entities = preg_split('#\s+#', trim($declaration));
					if ($entities[0] == 'PRIMARY')
					{
						continue;
					}
					$column_list[] = $entities[0];
				}

				// note down the primary key notation because sqlite only supports adding it to the end for the new table
				$primary_key = false;
				$_new_cols = array();

				foreach ($new_table_cols as $key => $declaration)
				{
					$entities = preg_split('#\s+#', trim($declaration));
					if ($entities[0] == 'PRIMARY')
					{
						$primary_key = $declaration;
						continue;
					}
					$_new_cols[] = $declaration;
				}

				$new_table_cols = $_new_cols;

				// First of all... change columns
				if (!empty($sql_schema_changes['change_columns']))
				{
					foreach ($sql_schema_changes['change_columns'] as $column_sql)
					{
						foreach ($new_table_cols as $key => $declaration)
						{
							$entities = preg_split('#\s+#', trim($declaration));
							if (strpos($column_sql, $entities[0] . ' ') === 0)
							{
								$new_table_cols[$key] = $column_sql;
							}
						}
					}
				}

				if (!empty($sql_schema_changes['add_columns']))
				{
					foreach ($sql_schema_changes['add_columns'] as $column_sql)
					{
						$new_table_cols[] = $column_sql;
					}
				}

				// Now drop them...
				if (!empty($sql_schema_changes['drop_columns']))
				{
					foreach ($sql_schema_changes['drop_columns'] as $column_name)
					{
						// Remove from column list...
						$new_column_list = array();
						foreach ($column_list as $key => $value)
						{
							if ($value === $column_name)
							{
								continue;
							}

							$new_column_list[] = $value;
						}

						$column_list = $new_column_list;

						// Remove from table...
						$_new_cols = array();
						foreach ($new_table_cols as $key => $declaration)
						{
							$entities = preg_split('#\s+#', trim($declaration));
							if (strpos($column_name . ' ', $entities[0] . ' ') === 0)
							{
								continue;
							}
							$_new_cols[] = $declaration;
						}
						$new_table_cols = $_new_cols;
					}
				}

				// Primary key...
				if (!empty($sql_schema_changes['primary_key']))
				{
					$new_table_cols[] = 'PRIMARY KEY (' . implode(', ', $sql_schema_changes['primary_key']) . ')';
				}
				// Add a new one or the old primary key
				else if ($primary_key !== false)
				{
					$new_table_cols[] = $primary_key;
				}

				$columns = implode(',', $column_list);

				// create a new table and fill it up. destroy the temp one
				$statements[] = 'CREATE TABLE ' . $table_name . ' (' . implode(',', $new_table_cols) . ');';
				$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
				$statements[] = 'DROP TABLE ' . $table_name . '_temp';

				$statements[] = 'commit';
			}
		}

		if ($this->return_statements)
		{
			return $statements;
		}
	}

	/**
	* Gets a list of columns of a table.
	*
	* @param string $table		Table name
	*
	* @return array				Array of column names (all lower case)
	*/
	function sql_list_columns($table)
	{
		$columns = array();

		switch ($this->sql_layer)
		{
			case 'mysql_40':
			case 'mysql_41':
				$sql = "SHOW COLUMNS FROM $table";
			break;

			// PostgreSQL has a way of doing this in a much simpler way but would
			// not allow us to support all versions of PostgreSQL
			case 'postgres':
				$sql = "SELECT a.attname
					FROM pg_class c, pg_attribute a
					WHERE c.relname = '{$table}'
						AND a.attnum > 0
						AND a.attrelid = c.oid";
			break;

			// same deal with PostgreSQL, we must perform more complex operations than
			// we technically could
			case 'mssql':
			case 'mssqlnative':
				$sql = "SELECT c.name
					FROM syscolumns c
					LEFT JOIN sysobjects o ON c.id = o.id
					WHERE o.name = '{$table}'";
			break;

			case 'oracle':
				$sql = "SELECT column_name
					FROM user_tab_columns
					WHERE LOWER(table_name) = '" . strtolower($table) . "'";
			break;

			case 'firebird':
				$sql = "SELECT RDB\$FIELD_NAME as FNAME
					FROM RDB\$RELATION_FIELDS
					WHERE RDB\$RELATION_NAME = '" . strtoupper($table) . "'";
			break;

			case 'sqlite':
				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table}'";

				$result = $this->db->sql_query($sql);

				if (!$result)
				{
					return false;
				}

				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				preg_match('#\((.*)\)#s', $row['sql'], $matches);

				$cols = trim($matches[1]);
				$col_array = preg_split('/,(?![\s\w]+\))/m', $cols);

				foreach ($col_array as $declaration)
				{
					$entities = preg_split('#\s+#', trim($declaration));
					if ($entities[0] == 'PRIMARY')
					{
						continue;
					}

					$column = strtolower($entities[0]);
					$columns[$column] = $column;
				}

				return $columns;
			break;
		}

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
	* Check whether a specified column exist in a table
	*
	* @param string	$table			Table to check
	* @param string	$column_name	Column to check
	*
	* @return bool		True if column exists, false otherwise
	*/
	function sql_column_exists($table, $column_name)
	{
		$columns = $this->sql_list_columns($table);

		return isset($columns[$column_name]);
	}

	/**
	* Check if a specified index exists in table. Does not return PRIMARY KEY and UNIQUE indexes.
	*
	* @param string	$table_name		Table to check the index at
	* @param string	$index_name		The index name to check
	*
	* @return bool True if index exists, else false
	*/
	function sql_index_exists($table_name, $index_name)
	{
		if ($this->sql_layer == 'mssql' || $this->sql_layer == 'mssqlnative')
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

		switch ($this->sql_layer)
		{
			case 'firebird':
				$sql = "SELECT LOWER(RDB\$INDEX_NAME) as index_name
					FROM RDB\$INDICES
					WHERE RDB\$RELATION_NAME = '" . strtoupper($table_name) . "'
						AND RDB\$UNIQUE_FLAG IS NULL
						AND RDB\$FOREIGN_KEY IS NULL";
				$col = 'index_name';
			break;

			case 'postgres':
				$sql = "SELECT ic.relname as index_name
					FROM pg_class bc, pg_class ic, pg_index i
					WHERE (bc.oid = i.indrelid)
						AND (ic.oid = i.indexrelid)
						AND (bc.relname = '" . $table_name . "')
						AND (i.indisunique != 't')
						AND (i.indisprimary != 't')";
				$col = 'index_name';
			break;

			case 'mysql_40':
			case 'mysql_41':
				$sql = 'SHOW KEYS
					FROM ' . $table_name;
				$col = 'Key_name';
			break;

			case 'oracle':
				$sql = "SELECT index_name
					FROM user_indexes
					WHERE table_name = '" . strtoupper($table_name) . "'
						AND generated = 'N'
						AND uniqueness = 'NONUNIQUE'";
				$col = 'index_name';
			break;

			case 'sqlite':
				$sql = "PRAGMA index_list('" . $table_name . "');";
				$col = 'name';
			break;
		}

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (($this->sql_layer == 'mysql_40' || $this->sql_layer == 'mysql_41') && !$row['Non_unique'])
			{
				continue;
			}

			// These DBMS prefix index name with the table name
			switch ($this->sql_layer)
			{
				case 'firebird':
				case 'oracle':
				case 'postgres':
				case 'sqlite':
					$row[$col] = substr($row[$col], strlen($table_name) + 1);
				break;
			}

			if (strtolower($row[$col]) == strtolower($index_name))
			{
				$this->db->sql_freeresult($result);
				return true;
			}
		}
		$this->db->sql_freeresult($result);

		return false;
	}

	/**
	* Check if a specified index exists in table. Does not return PRIMARY KEY indexes.
	*
	* @param string	$table_name		Table to check the index at
	* @param string	$index_name		The index name to check
	*
	* @return bool True if index exists, else false
	*/
	function sql_unique_index_exists($table_name, $index_name)
	{
		if ($this->sql_layer == 'mssql' || $this->sql_layer == 'mssqlnative')
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

		switch ($this->sql_layer)
		{
			case 'firebird':
				$sql = "SELECT LOWER(RDB\$INDEX_NAME) as index_name
					FROM RDB\$INDICES
					WHERE RDB\$RELATION_NAME = '" . strtoupper($table_name) . "'
						AND RDB\$UNIQUE_FLAG IS NOT NULL
						AND RDB\$FOREIGN_KEY IS NULL";
				$col = 'index_name';
			break;

			case 'postgres':
				$sql = "SELECT ic.relname as index_name, i.indisunique
					FROM pg_class bc, pg_class ic, pg_index i
					WHERE (bc.oid = i.indrelid)
						AND (ic.oid = i.indexrelid)
						AND (bc.relname = '" . $table_name . "')
						AND (i.indisprimary != 't')";
				$col = 'index_name';
			break;

			case 'mysql_40':
			case 'mysql_41':
				$sql = 'SHOW KEYS
					FROM ' . $table_name;
				$col = 'Key_name';
			break;

			case 'oracle':
				$sql = "SELECT index_name, table_owner
					FROM user_indexes
					WHERE table_name = '" . strtoupper($table_name) . "'
						AND generated = 'N'
						AND uniqueness = 'UNIQUE'";
				$col = 'index_name';
			break;

			case 'sqlite':
				$sql = "PRAGMA index_list('" . $table_name . "');";
				$col = 'name';
			break;
		}

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (($this->sql_layer == 'mysql_40' || $this->sql_layer == 'mysql_41') && ($row['Non_unique'] || $row[$col] == 'PRIMARY'))
			{
				continue;
			}

			if ($this->sql_layer == 'sqlite' && !$row['unique'])
			{
				continue;
			}

			if ($this->sql_layer == 'postgres' && $row['indisunique'] != 't')
			{
				continue;
			}

			// These DBMS prefix index name with the table name
			switch ($this->sql_layer)
			{
				case 'oracle':
					// Two cases here... prefixed with U_[table_owner] and not prefixed with table_name
					if (strpos($row[$col], 'U_') === 0)
					{
						$row[$col] = substr($row[$col], strlen('U_' . $row['table_owner']) + 1);
					}
					else if (strpos($row[$col], strtoupper($table_name)) === 0)
					{
						$row[$col] = substr($row[$col], strlen($table_name) + 1);
					}
				break;

				case 'firebird':
				case 'postgres':
				case 'sqlite':
					$row[$col] = substr($row[$col], strlen($table_name) + 1);
				break;
			}

			if (strtolower($row[$col]) == strtolower($index_name))
			{
				$this->db->sql_freeresult($result);
				return true;
			}
		}
		$this->db->sql_freeresult($result);

		return false;
	}

	/**
	* Private method for performing sql statements (either execute them or return them)
	* @access private
	*/
	function _sql_run_sql($statements)
	{
		if ($this->return_statements)
		{
			return $statements;
		}

		// We could add error handling here...
		foreach ($statements as $sql)
		{
			if ($sql === 'begin')
			{
				$this->db->sql_transaction('begin');
			}
			else if ($sql === 'commit')
			{
				$this->db->sql_transaction('commit');
			}
			else
			{
				$this->db->sql_query($sql);
			}
		}

		return true;
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
		if (strpos($column_data[0], ':') !== false)
		{
			list($orig_column_type, $column_length) = explode(':', $column_data[0]);
			if (!is_array($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']))
			{
				$column_type = sprintf($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':'], $column_length);
			}
			else
			{
				if (isset($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['rule']))
				{
					switch ($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['rule'][0])
					{
						case 'div':
							$column_length /= $this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['rule'][1];
							$column_length = ceil($column_length);
							$column_type = sprintf($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':'][0], $column_length);
						break;
					}
				}

				if (isset($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['limit']))
				{
					switch ($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['limit'][0])
					{
						case 'mult':
							$column_length *= $this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['limit'][1];
							if ($column_length > $this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['limit'][2])
							{
								$column_type = $this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['limit'][3];
							}
							else
							{
								$column_type = sprintf($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':'][0], $column_length);
							}
						break;
					}
				}
			}
			$orig_column_type .= ':';
		}
		else
		{
			$orig_column_type = $column_data[0];
			$column_type = $this->dbms_type_map[$this->sql_layer][$column_data[0]];
		}

		// Adjust default value if db-dependant specified
		if (is_array($column_data[1]))
		{
			$column_data[1] = (isset($column_data[1][$this->sql_layer])) ? $column_data[1][$this->sql_layer] : $column_data[1]['default'];
		}

		$sql = '';

		$return_array = array();

		switch ($this->sql_layer)
		{
			case 'firebird':
				$sql .= " {$column_type} ";
				$return_array['column_type_sql_type'] = " {$column_type} ";

				if (!is_null($column_data[1]))
				{
					$sql .= 'DEFAULT ' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ' ';
					$return_array['column_type_sql_default'] = ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ' ';
				}

				$sql .= 'NOT NULL';

				// This is a UNICODE column and thus should be given it's fair share
				if (preg_match('/^X?STEXT_UNI|VCHAR_(CI|UNI:?)/', $column_data[0]))
				{
					$sql .= ' COLLATE UNICODE';
				}

				$return_array['auto_increment'] = false;
				if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
				{
					$return_array['auto_increment'] = true;
				}

			break;

			case 'mssql':
			case 'mssqlnative':
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
//					$sql .= 'IDENTITY (1, 1) ';
					$sql_default .= 'IDENTITY (1, 1) ';
				}

				$return_array['textimage'] = $column_type === '[text]';

				$sql .= 'NOT NULL';
				$sql_default .= 'NOT NULL';

				$return_array['column_type_sql_default'] = $sql_default;

			break;

			case 'mysql_40':
			case 'mysql_41':
				$sql .= " {$column_type} ";

				// For hexadecimal values do not use single quotes
				if (!is_null($column_data[1]) && substr($column_type, -4) !== 'text' && substr($column_type, -4) !== 'blob')
				{
					$sql .= (strpos($column_data[1], '0x') === 0) ? "DEFAULT {$column_data[1]} " : "DEFAULT '{$column_data[1]}' ";
				}
				$sql .= 'NOT NULL';

				if (isset($column_data[2]))
				{
					if ($column_data[2] == 'auto_increment')
					{
						$sql .= ' auto_increment';
					}
					else if ($this->sql_layer === 'mysql_41' && $column_data[2] == 'true_sort')
					{
						$sql .= ' COLLATE utf8_unicode_ci';
					}
				}

			break;

			case 'oracle':
				$sql .= " {$column_type} ";
				$sql .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}' " : '';

				// In Oracle empty strings ('') are treated as NULL.
				// Therefore in oracle we allow NULL's for all DEFAULT '' entries
				// Oracle does not like setting NOT NULL on a column that is already NOT NULL (this happens only on number fields)
				if (!preg_match('/number/i', $column_type))
				{
					$sql .= ($column_data[1] === '') ? '' : 'NOT NULL';
				}

				$return_array['auto_increment'] = false;
				if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
				{
					$return_array['auto_increment'] = true;
				}

			break;

			case 'postgres':
				$return_array['column_type'] = $column_type;

				$sql .= " {$column_type} ";

				$return_array['auto_increment'] = false;
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

				$return_array['default'] = $default_val;

				$sql .= "DEFAULT {$default_val}";

				// Unsigned? Then add a CHECK contraint
				if (in_array($orig_column_type, $this->unsigned_types))
				{
					$return_array['constraint'] = "CHECK ({$column_name} >= 0)";
					$sql .= " CHECK ({$column_name} >= 0)";
				}

			break;

			case 'sqlite':
				$return_array['primary_key_set'] = false;
				if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
				{
					$sql .= ' INTEGER PRIMARY KEY';
					$return_array['primary_key_set'] = true;
				}
				else
				{
					$sql .= ' ' . $column_type;
				}

				$sql .= ' NOT NULL ';
				$sql .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}'" : '';

			break;
		}

		$return_array['column_type_sql'] = $sql;

		return $return_array;
	}

	/**
	* Add new column
	*/
	function sql_column_add($table_name, $column_name, $column_data, $inline = false)
	{
		$column_data = $this->sql_prepare_column_data($table_name, $column_name, $column_data);
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'firebird':
				// Does not support AFTER statement, only POSITION (and there you need the column position)
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD "' . strtoupper($column_name) . '" ' . $column_data['column_type_sql'];
			break;

			case 'mssql':
			case 'mssqlnative':
				// Does not support AFTER, only through temporary table
				$statements[] = 'ALTER TABLE [' . $table_name . '] ADD [' . $column_name . '] ' . $column_data['column_type_sql_default'];
			break;

			case 'mysql_40':
			case 'mysql_41':
				$after = (!empty($column_data['after'])) ? ' AFTER ' . $column_data['after'] : '';
				$statements[] = 'ALTER TABLE `' . $table_name . '` ADD COLUMN `' . $column_name . '` ' . $column_data['column_type_sql'] . $after;
			break;

			case 'oracle':
				// Does not support AFTER, only through temporary table
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD ' . $column_name . ' ' . $column_data['column_type_sql'];
			break;

			case 'postgres':
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

			break;

			case 'sqlite':

				if ($inline && $this->return_statements)
				{
					return $column_name . ' ' . $column_data['column_type_sql'];
				}

				if (version_compare(sqlite_libversion(), '3.0') == -1)
				{
					$sql = "SELECT sql
						FROM sqlite_master
						WHERE type = 'table'
							AND name = '{$table_name}'
						ORDER BY type DESC, name;";
					$result = $this->db->sql_query($sql);

					if (!$result)
					{
						break;
					}

					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					$statements[] = 'begin';

					// Create a backup table and populate it, destroy the existing one
					$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']);
					$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
					$statements[] = 'DROP TABLE ' . $table_name;

					preg_match('#\((.*)\)#s', $row['sql'], $matches);

					$new_table_cols = trim($matches[1]);
					$old_table_cols = preg_split('/,(?![\s\w]+\))/m', $new_table_cols);
					$column_list = array();

					foreach ($old_table_cols as $declaration)
					{
						$entities = preg_split('#\s+#', trim($declaration));
						if ($entities[0] == 'PRIMARY')
						{
							continue;
						}
						$column_list[] = $entities[0];
					}

					$columns = implode(',', $column_list);

					$new_table_cols = $column_name . ' ' . $column_data['column_type_sql'] . ',' . $new_table_cols;

					// create a new table and fill it up. destroy the temp one
					$statements[] = 'CREATE TABLE ' . $table_name . ' (' . $new_table_cols . ');';
					$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
					$statements[] = 'DROP TABLE ' . $table_name . '_temp';

					$statements[] = 'commit';
				}
				else
				{
					$statements[] = 'ALTER TABLE ' . $table_name . ' ADD ' . $column_name . ' [' . $column_data['column_type_sql'] . ']';
				}
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Drop column
	*/
	function sql_column_remove($table_name, $column_name, $inline = false)
	{
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'firebird':
				$statements[] = 'ALTER TABLE ' . $table_name . ' DROP "' . strtoupper($column_name) . '"';
			break;

			case 'mssql':
			case 'mssqlnative':
				$sql = "SELECT CAST(SERVERPROPERTY('productversion') AS VARCHAR(25)) AS mssql_version";
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				// Remove default constraints
				if ($row['mssql_version'][0] == '8')	// SQL Server 2000
				{
					// http://msdn.microsoft.com/en-us/library/aa175912%28v=sql.80%29.aspx
					// Deprecated in SQL Server 2005
					$statements[] = "DECLARE @drop_default_name VARCHAR(100), @cmd VARCHAR(1000)
						SET @drop_default_name =
							(SELECT so.name FROM sysobjects so
							JOIN sysconstraints sc ON so.id = sc.constid
							WHERE object_name(so.parent_obj) = '{$table_name}'
								AND so.xtype = 'D'
								AND sc.colid = (SELECT colid FROM syscolumns
									WHERE id = object_id('{$table_name}')
										AND name = '{$column_name}'))
						IF @drop_default_name <> ''
						BEGIN
							SET @cmd = 'ALTER TABLE [{$table_name}] DROP CONSTRAINT [' + @drop_default_name + ']'
							EXEC(@cmd)
						END";
				}
				else
				{
					$sql = "SELECT dobj.name AS def_name
					FROM sys.columns col 
						LEFT OUTER JOIN sys.objects dobj ON (dobj.object_id = col.default_object_id AND dobj.type = 'D')
					WHERE col.object_id = object_id('{$table_name}') 
					AND col.name = '{$column_name}'
					AND dobj.name IS NOT NULL";
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if ($row)
					{
						$statements[] = 'ALTER TABLE [' . $table_name . '] DROP CONSTRAINT [' . $row['def_name'] . ']';
					}
				}

				$statements[] = 'ALTER TABLE [' . $table_name . '] DROP COLUMN [' . $column_name . ']';
			break;

			case 'mysql_40':
			case 'mysql_41':
				$statements[] = 'ALTER TABLE `' . $table_name . '` DROP COLUMN `' . $column_name . '`';
			break;

			case 'oracle':
				$statements[] = 'ALTER TABLE ' . $table_name . ' DROP COLUMN ' . $column_name;
			break;

			case 'postgres':
				$statements[] = 'ALTER TABLE ' . $table_name . ' DROP COLUMN "' . $column_name . '"';
			break;

			case 'sqlite':

				if ($inline && $this->return_statements)
				{
					return $column_name;
				}

				if (version_compare(sqlite_libversion(), '3.0') == -1)
				{
					$sql = "SELECT sql
						FROM sqlite_master
						WHERE type = 'table'
							AND name = '{$table_name}'
						ORDER BY type DESC, name;";
					$result = $this->db->sql_query($sql);

					if (!$result)
					{
						break;
					}

					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					$statements[] = 'begin';

					// Create a backup table and populate it, destroy the existing one
					$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']);
					$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
					$statements[] = 'DROP TABLE ' . $table_name;

					preg_match('#\((.*)\)#s', $row['sql'], $matches);

					$new_table_cols = trim($matches[1]);
					$old_table_cols = preg_split('/,(?![\s\w]+\))/m', $new_table_cols);
					$column_list = array();

					foreach ($old_table_cols as $declaration)
					{
						$entities = preg_split('#\s+#', trim($declaration));
						if ($entities[0] == 'PRIMARY' || $entities[0] === $column_name)
						{
							continue;
						}
						$column_list[] = $entities[0];
					}

					$columns = implode(',', $column_list);

					$new_table_cols = preg_replace('/' . $column_name . '[^,]+(?:,|$)/m', '', $new_table_cols);

					// create a new table and fill it up. destroy the temp one
					$statements[] = 'CREATE TABLE ' . $table_name . ' (' . $new_table_cols . ');';
					$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
					$statements[] = 'DROP TABLE ' . $table_name . '_temp';

					$statements[] = 'commit';
				}
				else
				{
					$statements[] = 'ALTER TABLE ' . $table_name . ' DROP COLUMN ' . $column_name;
				}
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Drop Index
	*/
	function sql_index_drop($table_name, $index_name)
	{
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'mssql':
			case 'mssqlnative':
				$statements[] = 'DROP INDEX ' . $table_name . '.' . $index_name;
			break;

			case 'mysql_40':
			case 'mysql_41':
				$statements[] = 'DROP INDEX ' . $index_name . ' ON ' . $table_name;
			break;

			case 'firebird':
			case 'oracle':
			case 'postgres':
			case 'sqlite':
				$statements[] = 'DROP INDEX ' . $table_name . '_' . $index_name;
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Drop Table
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

		switch ($this->sql_layer)
		{
			case 'firebird':
				$sql = 'SELECT RDB$GENERATOR_NAME as gen
					FROM RDB$GENERATORS
					WHERE RDB$SYSTEM_FLAG = 0
						AND RDB$GENERATOR_NAME = \'' . strtoupper($table_name) . "_GEN'";
				$result = $this->db->sql_query($sql);

				// does a generator exist?
				if ($row = $this->db->sql_fetchrow($result))
				{
					$statements[] = "DROP GENERATOR {$row['gen']};";
				}
				$this->db->sql_freeresult($result);
			break;

			case 'oracle':
				$sql = 'SELECT A.REFERENCED_NAME
					FROM USER_DEPENDENCIES A, USER_TRIGGERS B
					WHERE A.REFERENCED_TYPE = \'SEQUENCE\'
						AND A.NAME = B.TRIGGER_NAME
						AND B.TABLE_NAME = \'' . strtoupper($table_name) . "'";
				$result = $this->db->sql_query($sql);

				// any sequences ref'd to this table's triggers?
				while ($row = $this->db->sql_fetchrow($result))
				{
					$statements[] = "DROP SEQUENCE {$row['referenced_name']}";
				}
				$this->db->sql_freeresult($result);
			break;

			case 'postgres':
				// PGSQL does not "tightly" bind sequences and tables, we must guess...
				$sql = "SELECT relname
					FROM pg_class
					WHERE relkind = 'S'
						AND relname = '{$table_name}_seq'";
				$result = $this->db->sql_query($sql);

				// We don't even care about storing the results. We already know the answer if we get rows back.
				if ($this->db->sql_fetchrow($result))
				{
					$statements[] =  "DROP SEQUENCE {$table_name}_seq;\n";
				}
				$this->db->sql_freeresult($result);
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Add primary key
	*/
	function sql_create_primary_key($table_name, $column, $inline = false)
	{
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'firebird':
			case 'postgres':
			case 'mysql_40':
			case 'mysql_41':
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD PRIMARY KEY (' . implode(', ', $column) . ')';
			break;

			case 'mssql':
			case 'mssqlnative':
				$sql = "ALTER TABLE [{$table_name}] WITH NOCHECK ADD ";
				$sql .= "CONSTRAINT [PK_{$table_name}] PRIMARY KEY  CLUSTERED (";
				$sql .= '[' . implode("],\n\t\t[", $column) . ']';
				$sql .= ')';

				$statements[] = $sql;
			break;

			case 'oracle':
				$statements[] = 'ALTER TABLE ' . $table_name . 'add CONSTRAINT pk_' . $table_name . ' PRIMARY KEY (' . implode(', ', $column) . ')';
			break;

			case 'sqlite':

				if ($inline && $this->return_statements)
				{
					return $column;
				}

				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table_name}'
					ORDER BY type DESC, name;";
				$result = $this->db->sql_query($sql);

				if (!$result)
				{
					break;
				}

				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$statements[] = 'begin';

				// Create a backup table and populate it, destroy the existing one
				$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']);
				$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
				$statements[] = 'DROP TABLE ' . $table_name;

				preg_match('#\((.*)\)#s', $row['sql'], $matches);

				$new_table_cols = trim($matches[1]);
				$old_table_cols = preg_split('/,(?![\s\w]+\))/m', $new_table_cols);
				$column_list = array();

				foreach ($old_table_cols as $declaration)
				{
					$entities = preg_split('#\s+#', trim($declaration));
					if ($entities[0] == 'PRIMARY')
					{
						continue;
					}
					$column_list[] = $entities[0];
				}

				$columns = implode(',', $column_list);

				// create a new table and fill it up. destroy the temp one
				$statements[] = 'CREATE TABLE ' . $table_name . ' (' . $new_table_cols . ', PRIMARY KEY (' . implode(', ', $column) . '));';
				$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
				$statements[] = 'DROP TABLE ' . $table_name . '_temp';

				$statements[] = 'commit';
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Add unique index
	*/
	function sql_create_unique_index($table_name, $index_name, $column)
	{
		$statements = array();

		$table_prefix = substr(CONFIG_TABLE, 0, -6); // strlen(config)
		if (strlen($table_name . $index_name) - strlen($table_prefix) > 24)
		{
			$max_length = strlen($table_prefix) + 24;
			trigger_error("Index name '{$table_name}_$index_name' on table '$table_name' is too long. The maximum is $max_length characters.", E_USER_ERROR);
		}

		switch ($this->sql_layer)
		{
			case 'firebird':
			case 'postgres':
			case 'oracle':
			case 'sqlite':
				$statements[] = 'CREATE UNIQUE INDEX ' . $table_name . '_' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			break;

			case 'mysql_40':
			case 'mysql_41':
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD UNIQUE INDEX ' . $index_name . '(' . implode(', ', $column) . ')';
			break;

			case 'mssql':
			case 'mssqlnative':
				$statements[] = 'CREATE UNIQUE INDEX ' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Add index
	*/
	function sql_create_index($table_name, $index_name, $column)
	{
		$statements = array();

		$table_prefix = substr(CONFIG_TABLE, 0, -6); // strlen(config)
		if (strlen($table_name . $index_name) - strlen($table_prefix) > 24)
		{
			$max_length = strlen($table_prefix) + 24;
			trigger_error("Index name '{$table_name}_$index_name' on table '$table_name' is too long. The maximum is $max_length characters.", E_USER_ERROR);
		}

		// remove index length unless MySQL4
		if ('mysql_40' != $this->sql_layer)
		{
			$column = preg_replace('#:.*$#', '', $column);
		}

		switch ($this->sql_layer)
		{
			case 'firebird':
			case 'postgres':
			case 'oracle':
			case 'sqlite':
				$statements[] = 'CREATE INDEX ' . $table_name . '_' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			break;

			case 'mysql_40':
				// add index size to definition as required by MySQL4
				foreach ($column as $i => $col)
				{
					if (false !== strpos($col, ':'))
					{
						list($col, $index_size) = explode(':', $col);
						$column[$i] = "$col($index_size)";
					}
				}
			// no break
			case 'mysql_41':
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD INDEX ' . $index_name . '(' . implode(', ', $column) . ')';
			break;

			case 'mssql':
			case 'mssqlnative':
				$statements[] = 'CREATE INDEX ' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* List all of the indices that belong to a table,
	* does not count:
	* * UNIQUE indices
	* * PRIMARY keys
	*/
	function sql_list_index($table_name)
	{
		$index_array = array();

		if ($this->sql_layer == 'mssql' || $this->sql_layer == 'mssqlnative')
		{
			$sql = "EXEC sp_statistics '$table_name'";
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				if ($row['TYPE'] == 3)
				{
					$index_array[] = $row['INDEX_NAME'];
				}
			}
			$this->db->sql_freeresult($result);
		}
		else
		{
			switch ($this->sql_layer)
			{
				case 'firebird':
					$sql = "SELECT LOWER(RDB\$INDEX_NAME) as index_name
						FROM RDB\$INDICES
						WHERE RDB\$RELATION_NAME = '" . strtoupper($table_name) . "'
							AND RDB\$UNIQUE_FLAG IS NULL
							AND RDB\$FOREIGN_KEY IS NULL";
					$col = 'index_name';
				break;

				case 'postgres':
					$sql = "SELECT ic.relname as index_name
						FROM pg_class bc, pg_class ic, pg_index i
						WHERE (bc.oid = i.indrelid)
							AND (ic.oid = i.indexrelid)
							AND (bc.relname = '" . $table_name . "')
							AND (i.indisunique != 't')
							AND (i.indisprimary != 't')";
					$col = 'index_name';
				break;

				case 'mysql_40':
				case 'mysql_41':
					$sql = 'SHOW KEYS
						FROM ' . $table_name;
					$col = 'Key_name';
				break;

				case 'oracle':
					$sql = "SELECT index_name
						FROM user_indexes
						WHERE table_name = '" . strtoupper($table_name) . "'
							AND generated = 'N'
							AND uniqueness = 'NONUNIQUE'";
					$col = 'index_name';
				break;

				case 'sqlite':
					$sql = "PRAGMA index_info('" . $table_name . "');";
					$col = 'name';
				break;
			}

			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				if (($this->sql_layer == 'mysql_40' || $this->sql_layer == 'mysql_41') && !$row['Non_unique'])
				{
					continue;
				}

				switch ($this->sql_layer)
				{
					case 'firebird':
					case 'oracle':
					case 'postgres':
					case 'sqlite':
						$row[$col] = substr($row[$col], strlen($table_name) + 1);
					break;
				}

				$index_array[] = $row[$col];
			}
			$this->db->sql_freeresult($result);
		}

		return array_map('strtolower', $index_array);
	}

	/**
	* Change column type (not name!)
	*/
	function sql_column_change($table_name, $column_name, $column_data, $inline = false)
	{
		$column_data = $this->sql_prepare_column_data($table_name, $column_name, $column_data);
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'firebird':
				// Change type...
				if (!empty($column_data['column_type_sql_default']))
				{
					$statements[] = 'ALTER TABLE ' . $table_name . ' ALTER COLUMN "' . strtoupper($column_name) . '" TYPE ' . ' ' . $column_data['column_type_sql_type'];
					$statements[] = 'ALTER TABLE ' . $table_name . ' ALTER COLUMN "' . strtoupper($column_name) . '" SET DEFAULT ' . ' ' . $column_data['column_type_sql_default'];
				}
				else
				{
					// TODO: try to change pkey without removing trigger, generator or constraints. ATM this query may fail.
					$statements[] = 'ALTER TABLE ' . $table_name . ' ALTER COLUMN "' . strtoupper($column_name) . '" TYPE ' . ' ' . $column_data['column_type_sql_type'];
				}
			break;

			case 'mssql':
			case 'mssqlnative':
				$statements[] = 'ALTER TABLE [' . $table_name . '] ALTER COLUMN [' . $column_name . '] ' . $column_data['column_type_sql'];

				if (!empty($column_data['default']))
				{
					$sql = "SELECT CAST(SERVERPROPERTY('productversion') AS VARCHAR(25)) AS mssql_version";
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					// Using TRANSACT-SQL for this statement because we do not want to have colliding data if statements are executed at a later stage
					if ($row['mssql_version'][0] == '8')	// SQL Server 2000
					{
						$statements[] = "DECLARE @drop_default_name VARCHAR(100), @cmd VARCHAR(1000)
							SET @drop_default_name =
								(SELECT so.name FROM sysobjects so
								JOIN sysconstraints sc ON so.id = sc.constid
								WHERE object_name(so.parent_obj) = '{$table_name}'
									AND so.xtype = 'D'
									AND sc.colid = (SELECT colid FROM syscolumns
										WHERE id = object_id('{$table_name}')
											AND name = '{$column_name}'))
							IF @drop_default_name <> ''
							BEGIN
								SET @cmd = 'ALTER TABLE [{$table_name}] DROP CONSTRAINT [' + @drop_default_name + ']'
								EXEC(@cmd)
							END
							SET @cmd = 'ALTER TABLE [{$table_name}] ADD CONSTRAINT [DF_{$table_name}_{$column_name}_1] {$column_data['default']} FOR [{$column_name}]'
							EXEC(@cmd)";
					}
					else
					{
						$statements[] = "DECLARE @drop_default_name VARCHAR(100), @cmd VARCHAR(1000)
							SET @drop_default_name =
								(SELECT dobj.name FROM sys.columns col 
									LEFT OUTER JOIN sys.objects dobj ON (dobj.object_id = col.default_object_id AND dobj.type = 'D')
								WHERE col.object_id = object_id('{$table_name}') 
								AND col.name = '{$column_name}'
								AND dobj.name IS NOT NULL)
							IF @drop_default_name <> ''
							BEGIN
								SET @cmd = 'ALTER TABLE [{$table_name}] DROP CONSTRAINT [' + @drop_default_name + ']'
								EXEC(@cmd)
							END
							SET @cmd = 'ALTER TABLE [{$table_name}] ADD CONSTRAINT [DF_{$table_name}_{$column_name}_1] {$column_data['default']} FOR [{$column_name}]'
							EXEC(@cmd)";
					}
				}
			break;

			case 'mysql_40':
			case 'mysql_41':
				$statements[] = 'ALTER TABLE `' . $table_name . '` CHANGE `' . $column_name . '` `' . $column_name . '` ' . $column_data['column_type_sql'];
			break;

			case 'oracle':
				$statements[] = 'ALTER TABLE ' . $table_name . ' MODIFY ' . $column_name . ' ' . $column_data['column_type_sql'];
			break;

			case 'postgres':
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
					$constraint_sql = "SELECT consrc as constraint_data
								FROM pg_constraint, pg_class bc
								WHERE conrelid = bc.oid
									AND bc.relname = '{$table_name}'
									AND NOT EXISTS (
										SELECT *
											FROM pg_constraint as c, pg_inherits as i
											WHERE i.inhrelid = pg_constraint.conrelid
												AND c.conname = pg_constraint.conname
												AND c.consrc = pg_constraint.consrc
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
			break;

			case 'sqlite':

				if ($inline && $this->return_statements)
				{
					return $column_name . ' ' . $column_data['column_type_sql'];
				}

				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table_name}'
					ORDER BY type DESC, name;";
				$result = $this->db->sql_query($sql);

				if (!$result)
				{
					break;
				}

				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$statements[] = 'begin';

				// Create a temp table and populate it, destroy the existing one
				$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']);
				$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
				$statements[] = 'DROP TABLE ' . $table_name;

				preg_match('#\((.*)\)#s', $row['sql'], $matches);

				$new_table_cols = trim($matches[1]);
				$old_table_cols = preg_split('/,(?![\s\w]+\))/m', $new_table_cols);
				$column_list = array();

				foreach ($old_table_cols as $key => $declaration)
				{
					$entities = preg_split('#\s+#', trim($declaration));
					$column_list[] = $entities[0];
					if ($entities[0] == $column_name)
					{
						$old_table_cols[$key] = $column_name . ' ' . $column_data['column_type_sql'];
					}
				}

				$columns = implode(',', $column_list);

				// create a new table and fill it up. destroy the temp one
				$statements[] = 'CREATE TABLE ' . $table_name . ' (' . implode(',', $old_table_cols) . ');';
				$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
				$statements[] = 'DROP TABLE ' . $table_name . '_temp';

				$statements[] = 'commit';

			break;
		}

		return $this->_sql_run_sql($statements);
	}
}

?>