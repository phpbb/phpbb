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

namespace phpbb\db;

/**
* Database Tools for handling cross-db actions such as altering columns, etc.
* Currently not supported is returning SQL for creating tables.
*/
class tools
{
	/**
	* Current sql layer
	*/
	var $sql_layer = '';

	/**
	* @var object DB object
	*/
	var $db = null;

	/**
	* The Column types for every database we support
	* @var array
	*/
	var $dbms_type_map = array();

	/**
	* Is the used MS SQL Server a SQL Server 2000?
	* @var bool
	*/
	protected $is_sql_server_2000;

	/**
	* Get the column types for every database we support
	*
	* @return array
	*/
	public static function get_dbms_type_map()
	{
		return array(
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

			'sqlite3'	=> array(
				'INT:'		=> 'INT(%d)',
				'BINT'		=> 'BIGINT(20)',
				'UINT'		=> 'INTEGER UNSIGNED',
				'UINT:'		=> 'INTEGER UNSIGNED',
				'TINT:'		=> 'TINYINT(%d)',
				'USINT'		=> 'INTEGER UNSIGNED',
				'BOOL'		=> 'INTEGER UNSIGNED',
				'VCHAR'		=> 'VARCHAR(255)',
				'VCHAR:'	=> 'VARCHAR(%d)',
				'CHAR:'		=> 'CHAR(%d)',
				'XSTEXT'	=> 'TEXT(65535)',
				'STEXT'		=> 'TEXT(65535)',
				'TEXT'		=> 'TEXT(65535)',
				'MTEXT'		=> 'MEDIUMTEXT(16777215)',
				'XSTEXT_UNI'=> 'TEXT(65535)',
				'STEXT_UNI'	=> 'TEXT(65535)',
				'TEXT_UNI'	=> 'TEXT(65535)',
				'MTEXT_UNI'	=> 'MEDIUMTEXT(16777215)',
				'TIMESTAMP'	=> 'INTEGER UNSIGNED', //'int(11) UNSIGNED',
				'DECIMAL'	=> 'DECIMAL(5,2)',
				'DECIMAL:'	=> 'DECIMAL(%d,2)',
				'PDECIMAL'	=> 'DECIMAL(6,3)',
				'PDECIMAL:'	=> 'DECIMAL(%d,3)',
				'VCHAR_UNI'	=> 'VARCHAR(255)',
				'VCHAR_UNI:'=> 'VARCHAR(%d)',
				'VCHAR_CI'	=> 'VARCHAR(255)',
				'VARBINARY'	=> 'BLOB',
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
	}

	/**
	* A list of types being unsigned for better reference in some db's
	* @var array
	*/
	var $unsigned_types = array('UINT', 'UINT:', 'USINT', 'BOOL', 'TIMESTAMP');

	/**
	* A list of supported DBMS. We change this class to support more DBMS, the DBMS itself only need to follow some rules.
	* @var array
	*/
	var $supported_dbms = array('mssql', 'mssqlnative', 'mysql_40', 'mysql_41', 'oracle', 'postgres', 'sqlite', 'sqlite3');

	/**
	* This is set to true if user only wants to return the 'to-be-executed' SQL statement(s) (as an array).
	* This mode has no effect on some methods (inserting of data for example). This is expressed within the methods command.
	*/
	var $return_statements = false;

	/**
	* Constructor. Set DB Object and set {@link $return_statements return_statements}.
	*
	* @param \phpbb\db\driver\driver_interface	$db					Database connection
	* @param bool		$return_statements	True if only statements should be returned and no SQL being executed
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, $return_statements = false)
	{
		$this->db = $db;
		$this->return_statements = $return_statements;

		$this->dbms_type_map = self::get_dbms_type_map();

		// Determine mapping database type
		switch ($this->db->get_sql_layer())
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
				$this->sql_layer = $this->db->get_sql_layer();
			break;
		}
	}

	/**
	* Setter for {@link $return_statements return_statements}.
	*
	* @param bool $return_statements True if SQL should not be executed but returned as strings
	* @return null
	*/
	public function set_return_statements($return_statements)
	{
		$this->return_statements = $return_statements;
	}

	/**
	* Gets a list of tables in the database.
	*
	* @return array		Array of table names  (all lower case)
	*/
	function sql_list_tables()
	{
		switch ($this->db->get_sql_layer())
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

			case 'sqlite3':
				$sql = 'SELECT name
					FROM sqlite_master
					WHERE type = "table"
						AND name <> "sqlite_sequence"';
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
					case 'sqlite3':
						$table_sql .= ",\n\t PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . ')';
					break;

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
			case 'sqlite3':
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
	*	drop_tables: Drop tables
	*	add_tables: Add tables
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
		if (($this->db->get_sql_layer() == 'sqlite' || $this->db->get_sql_layer() == 'sqlite3') && $this->return_statements)
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

			case 'sqlite':
			case 'sqlite3':
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
			case 'sqlite3':
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
				case 'oracle':
				case 'postgres':
				case 'sqlite':
				case 'sqlite3':
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
			case 'sqlite3':
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

			if (($this->sql_layer == 'sqlite' || $this->sql_layer == 'sqlite3') && !$row['unique'])
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

				case 'postgres':
				case 'sqlite':
				case 'sqlite3':
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
		list($column_type, $orig_column_type) = $this->get_column_type($column_data[0]);

		// Adjust default value if db-dependent specified
		if (is_array($column_data[1]))
		{
			$column_data[1] = (isset($column_data[1][$this->sql_layer])) ? $column_data[1][$this->sql_layer] : $column_data[1]['default'];
		}

		$sql = '';

		$return_array = array();

		switch ($this->sql_layer)
		{
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

			break;

			case 'mysql_40':
			case 'mysql_41':
				$sql .= " {$column_type} ";

				// For hexadecimal values do not use single quotes
				if (!is_null($column_data[1]) && substr($column_type, -4) !== 'text' && substr($column_type, -4) !== 'blob')
				{
					$sql .= (strpos($column_data[1], '0x') === 0) ? "DEFAULT {$column_data[1]} " : "DEFAULT '{$column_data[1]}' ";
				}

				if (!is_null($column_data[1]) || (isset($column_data[2]) && $column_data[2] == 'auto_increment'))
				{
					$sql .= 'NOT NULL';
				}
				else
				{
					$sql .= 'NULL';
				}

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
					$sql .= ($column_data[1] === '' || $column_data[1] === null) ? '' : 'NOT NULL';
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

			break;

			case 'sqlite':
			case 'sqlite3':
				$return_array['primary_key_set'] = false;
				if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
				{
					$sql .= ' INTEGER PRIMARY KEY';
					$return_array['primary_key_set'] = true;

					if ($this->sql_layer === 'sqlite3')
					{
						$sql .= ' AUTOINCREMENT';
					}
				}
				else
				{
					$sql .= ' ' . $column_type;
				}

				if (!is_null($column_data[1]))
				{
					$sql .= ' NOT NULL ';
					$sql .= "DEFAULT '{$column_data[1]}'";
				}

			break;
		}

		$return_array['column_type_sql'] = $sql;

		return $return_array;
	}

	/**
	* Get the column's database type from the type map
	*
	* @param string $column_map_type
	* @return array		column type for this database
	*					and map type without length
	*/
	function get_column_type($column_map_type)
	{
		if (strpos($column_map_type, ':') !== false)
		{
			list($orig_column_type, $column_length) = explode(':', $column_map_type);
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
			$orig_column_type = $column_map_type;
			$column_type = $this->dbms_type_map[$this->sql_layer][$column_map_type];
		}

		return array($column_type, $orig_column_type);
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

				$recreate_queries = $this->sqlite_get_recreate_table_queries($table_name);
				if (empty($recreate_queries))
				{
					break;
				}

				$statements[] = 'begin';

				$sql_create_table = array_shift($recreate_queries);

				// Create a backup table and populate it, destroy the existing one
				$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $sql_create_table);
				$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
				$statements[] = 'DROP TABLE ' . $table_name;

				preg_match('#\((.*)\)#s', $sql_create_table, $matches);

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
				$statements = array_merge($statements, $recreate_queries);

				$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
				$statements[] = 'DROP TABLE ' . $table_name . '_temp';

				$statements[] = 'commit';
			break;

			case 'sqlite3':
				if ($inline && $this->return_statements)
				{
					return $column_name . ' ' . $column_data['column_type_sql'];
				}

				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD ' . $column_name . ' ' . $column_data['column_type_sql'];
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
			case 'mssql':
			case 'mssqlnative':
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
						if (sizeof($index_data) > 1)
						{
							// Remove this column from the index and recreate it
							$recreate_indexes[$index_name] = array_diff($index_data, array($column_name));
						}
					}
				}

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
			case 'sqlite3':

				if ($inline && $this->return_statements)
				{
					return $column_name;
				}

				$recreate_queries = $this->sqlite_get_recreate_table_queries($table_name, $column_name);
				if (empty($recreate_queries))
				{
					break;
				}

				$statements[] = 'begin';

				$sql_create_table = array_shift($recreate_queries);

				// Create a backup table and populate it, destroy the existing one
				$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $sql_create_table);
				$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
				$statements[] = 'DROP TABLE ' . $table_name;

				preg_match('#\((.*)\)#s', $sql_create_table, $matches);

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

				$new_table_cols = trim(preg_replace('/' . $column_name . '\b[^,]+(?:,|$)/m', '', $new_table_cols));
				if (substr($new_table_cols, -1) === ',')
				{
					// Remove the comma from the last entry again
					$new_table_cols = substr($new_table_cols, 0, -1);
				}

				// create a new table and fill it up. destroy the temp one
				$statements[] = 'CREATE TABLE ' . $table_name . ' (' . $new_table_cols . ');';
				$statements = array_merge($statements, $recreate_queries);

				$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
				$statements[] = 'DROP TABLE ' . $table_name . '_temp';

				$statements[] = 'commit';
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

			case 'oracle':
			case 'postgres':
			case 'sqlite':
			case 'sqlite3':
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
				$statements[] = 'ALTER TABLE ' . $table_name . ' add CONSTRAINT pk_' . $table_name . ' PRIMARY KEY (' . implode(', ', $column) . ')';
			break;

			case 'sqlite':
			case 'sqlite3':

				if ($inline && $this->return_statements)
				{
					return $column;
				}

				$recreate_queries = $this->sqlite_get_recreate_table_queries($table_name);
				if (empty($recreate_queries))
				{
					break;
				}

				$statements[] = 'begin';

				$sql_create_table = array_shift($recreate_queries);

				// Create a backup table and populate it, destroy the existing one
				$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $sql_create_table);
				$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
				$statements[] = 'DROP TABLE ' . $table_name;

				preg_match('#\((.*)\)#s', $sql_create_table, $matches);

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
				$statements = array_merge($statements, $recreate_queries);

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
		if (strlen($table_name . '_' . $index_name) - strlen($table_prefix) > 24)
		{
			$max_length = strlen($table_prefix) + 24;
			trigger_error("Index name '{$table_name}_$index_name' on table '$table_name' is too long. The maximum is $max_length characters.", E_USER_ERROR);
		}

		switch ($this->sql_layer)
		{
			case 'postgres':
			case 'oracle':
			case 'sqlite':
			case 'sqlite3':
				$statements[] = 'CREATE UNIQUE INDEX ' . $table_name . '_' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			break;

			case 'mysql_40':
			case 'mysql_41':
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD UNIQUE INDEX ' . $index_name . '(' . implode(', ', $column) . ')';
			break;

			case 'mssql':
			case 'mssqlnative':
				$statements[] = 'CREATE UNIQUE INDEX [' . $index_name . '] ON [' . $table_name . ']([' . implode('], [', $column) . '])';
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
			case 'postgres':
			case 'oracle':
			case 'sqlite':
			case 'sqlite3':
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
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD INDEX ' . $index_name . ' (' . implode(', ', $column) . ')';
			break;

			case 'mssql':
			case 'mssqlnative':
				$statements[] = 'CREATE INDEX [' . $index_name . '] ON [' . $table_name . ']([' . implode('], [', $column) . '])';
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
				case 'sqlite3':
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
					case 'oracle':
					case 'postgres':
					case 'sqlite':
					case 'sqlite3':
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
	 * Removes table_name from the index_name if it is at the beginning
	 *
	 * @param $table_name
	 * @param $index_name
	 * @return string
	 */
	protected function strip_table_name_from_index_name($table_name, $index_name)
	{
		return (strpos(strtoupper($index_name), strtoupper($table_name)) === 0) ? substr($index_name, strlen($table_name) + 1) : $index_name;
	}

	/**
	* Change column type (not name!)
	*/
	function sql_column_change($table_name, $column_name, $column_data, $inline = false)
	{
		$original_column_data = $column_data;
		$column_data = $this->sql_prepare_column_data($table_name, $column_name, $column_data);
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'mssql':
			case 'mssqlnative':
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

				if (!empty($column_data['default']))
				{
					// Add new default value constraint
					$statements[] = 'ALTER TABLE [' . $table_name . '] ADD CONSTRAINT [DF_' . $table_name . '_' . $column_name . '_1] ' . $this->db->sql_escape($column_data['default']) . ' FOR [' . $column_name . ']';
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
			break;

			case 'mysql_40':
			case 'mysql_41':
				$statements[] = 'ALTER TABLE `' . $table_name . '` CHANGE `' . $column_name . '` `' . $column_name . '` ' . $column_data['column_type_sql'];
			break;

			case 'oracle':
				// We need the data here
				$old_return_statements = $this->return_statements;
				$this->return_statements = true;

				// Get list of existing indexes
				$indexes = $this->get_existing_indexes($table_name, $column_name);
				$unique_indexes = $this->get_existing_indexes($table_name, $column_name, true);

				// Drop any indexes
				if (!empty($indexes) || !empty($unique_indexes))
				{
					$drop_indexes = array_merge(array_keys($indexes), array_keys($unique_indexes));
					foreach ($drop_indexes as $index_name)
					{
						$result = $this->sql_index_drop($table_name, $this->strip_table_name_from_index_name($table_name, $index_name));
						$statements = array_merge($statements, $result);
					}
				}

				$temp_column_name = 'temp_' . substr(md5($column_name), 0, 25);
				// Add a temporary table with the new type
				$result = $this->sql_column_add($table_name, $temp_column_name, $original_column_data);
				$statements = array_merge($statements, $result);

				// Copy the data to the new column
				$statements[] = 'UPDATE ' . $table_name . ' SET ' . $temp_column_name . ' = ' . $column_name;

				// Drop the original column
				$result = $this->sql_column_remove($table_name, $column_name);
				$statements = array_merge($statements, $result);

				// Recreate the original column with the new type
				$result = $this->sql_column_add($table_name, $column_name, $original_column_data);
				$statements = array_merge($statements, $result);

				if (!empty($indexes))
				{
					// Recreate indexes after we changed the column
					foreach ($indexes as $index_name => $index_data)
					{
						$result = $this->sql_create_index($table_name, $this->strip_table_name_from_index_name($table_name, $index_name), $index_data);
						$statements = array_merge($statements, $result);
					}
				}

				if (!empty($unique_indexes))
				{
					// Recreate unique indexes after we changed the column
					foreach ($unique_indexes as $index_name => $index_data)
					{
						$result = $this->sql_create_unique_index($table_name, $this->strip_table_name_from_index_name($table_name, $index_name), $index_data);
						$statements = array_merge($statements, $result);
					}
				}

				// Copy the data to the original column
				$statements[] = 'UPDATE ' . $table_name . ' SET ' . $column_name . ' = ' . $temp_column_name;

				// Drop the temporary column again
				$result = $this->sql_column_remove($table_name, $temp_column_name);
				$statements = array_merge($statements, $result);

				$this->return_statements = $old_return_statements;
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
			case 'sqlite3':

				if ($inline && $this->return_statements)
				{
					return $column_name . ' ' . $column_data['column_type_sql'];
				}

				$recreate_queries = $this->sqlite_get_recreate_table_queries($table_name);
				if (empty($recreate_queries))
				{
					break;
				}

				$statements[] = 'begin';

				$sql_create_table = array_shift($recreate_queries);

				// Create a temp table and populate it, destroy the existing one
				$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $sql_create_table);
				$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
				$statements[] = 'DROP TABLE ' . $table_name;

				preg_match('#\((.*)\)#s', $sql_create_table, $matches);

				$new_table_cols = trim($matches[1]);
				$old_table_cols = preg_split('/,(?![\s\w]+\))/m', $new_table_cols);
				$column_list = array();

				foreach ($old_table_cols as $key => $declaration)
				{
					$declaration = trim($declaration);

					// Check for the beginning of the constraint section and stop
					if (preg_match('/[^\(]*\s*PRIMARY KEY\s+\(/', $declaration) ||
						preg_match('/[^\(]*\s*UNIQUE\s+\(/', $declaration) ||
						preg_match('/[^\(]*\s*FOREIGN KEY\s+\(/', $declaration) ||
						preg_match('/[^\(]*\s*CHECK\s+\(/', $declaration))
					{
						break;
					}

					$entities = preg_split('#\s+#', $declaration);
					$column_list[] = $entities[0];
					if ($entities[0] == $column_name)
					{
						$old_table_cols[$key] = $column_name . ' ' . $column_data['column_type_sql'];
					}
				}

				$columns = implode(',', $column_list);

				// Create a new table and fill it up. destroy the temp one
				$statements[] = 'CREATE TABLE ' . $table_name . ' (' . implode(',', $old_table_cols) . ');';
				$statements = array_merge($statements, $recreate_queries);

				$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
				$statements[] = 'DROP TABLE ' . $table_name . '_temp';

				$statements[] = 'commit';

			break;
		}

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
	* Get a list with existing indexes for the column
	*
	* @param string $table_name
	* @param string $column_name
	* @param bool $unique Should we get unique indexes or normal ones
	* @return array		Array with Index name => columns
	*/
	public function get_existing_indexes($table_name, $column_name, $unique = false)
	{
		switch ($this->sql_layer)
		{
			case 'mysql_40':
			case 'mysql_41':
			case 'postgres':
			case 'sqlite':
			case 'sqlite3':
				// Not supported
				throw new \Exception('DBMS is not supported');
			break;
		}

		$sql = '';
		$existing_indexes = array();

		switch ($this->sql_layer)
		{
			case 'mssql':
			case 'mssqlnative':
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
						AND ix.is_unique = " . ($unique ? '1' : '0');
				}
			break;

			case 'oracle':
				$sql = "SELECT ix.index_name  AS phpbb_index_name, ix.uniqueness AS is_unique
					FROM all_ind_columns ixc, all_indexes ix
					WHERE ix.index_name = ixc.index_name
						AND ixc.table_name = '" . strtoupper($table_name) . "'
						AND ixc.column_name = '" . strtoupper($column_name) . "'";
			break;
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

		switch ($this->sql_layer)
		{
			case 'mssql':
			case 'mssqlnative':
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
			break;

			case 'oracle':
				$sql = "SELECT index_name AS phpbb_index_name, column_name AS phpbb_column_name
					FROM all_ind_columns
					WHERE table_name = '" . strtoupper($table_name) . "'
						AND " . $this->db->sql_in_set('index_name', array_keys($existing_indexes));
			break;
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

	/**
	* Returns the Queries which are required to recreate a table including indexes
	*
	* @param string $table_name
	* @param string $remove_column	When we drop a column, we remove the column
	*								from all indexes. If the index has no other
	*								column, we drop it completly.
	* @return array
	*/
	protected function sqlite_get_recreate_table_queries($table_name, $remove_column = '')
	{
		$queries = array();

		$sql = "SELECT sql
			FROM sqlite_master
			WHERE type = 'table'
				AND name = '{$table_name}'";
		$result = $this->db->sql_query($sql);
		$sql_create_table = $this->db->sql_fetchfield('sql');
		$this->db->sql_freeresult($result);

		if (!$sql_create_table)
		{
			return array();
		}
		$queries[] = $sql_create_table;

		$sql = "SELECT sql
			FROM sqlite_master
			WHERE type = 'index'
				AND tbl_name = '{$table_name}'";
		$result = $this->db->sql_query($sql);
		while ($sql_create_index = $this->db->sql_fetchfield('sql'))
		{
			if ($remove_column)
			{
				$match = array();
				preg_match('#(?:[\w ]+)\((.*)\)#', $sql_create_index, $match);
				if (!isset($match[1]))
				{
					continue;
				}

				// Find and remove $remove_column from the index
				$columns = explode(', ', $match[1]);
				$found_column = array_search($remove_column, $columns);
				if ($found_column !== false)
				{
					unset($columns[$found_column]);

					// If the column list is not empty add the index to the list
					if (!empty($columns))
					{
						$queries[] = str_replace($match[1], implode(', ', $columns), $sql_create_index);
					}
				}
				else
				{
					$queries[] = $sql_create_index;
				}
			}
			else
			{
				$queries[] = $sql_create_index;
			}
		}
		$this->db->sql_freeresult($result);

		return $queries;
	}
}
