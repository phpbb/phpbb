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
	public static $dbms_type_map = array(
		'mysql'		=> array(
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

		'oracle'	=> array(
			'INT:'		=> 'number(%d)',
			'BINT'		=> 'number(20)',
			'UINT'		=> 'number(8)',
			'UINT:'		=> 'number(%d)',
			'TINT:'		=> 'number(%d)',
			'USINT'		=> 'number(4)',
			'BOOL'		=> 'number(1)',
			'VCHAR'		=> 'varchar2(255 char)',
			'VCHAR:'	=> 'varchar2(%d char)',
			'CHAR:'		=> 'char(%d char)',
			'XSTEXT'	=> 'varchar2(1000 char)',
			'STEXT'		=> 'varchar2(3000 char)',
			'TEXT'		=> 'clob',
			'MTEXT'		=> 'clob',
			'XSTEXT_UNI'=> 'varchar2(100 char)',
			'STEXT_UNI'	=> 'varchar2(255 char)',
			'TEXT_UNI'	=> 'clob',
			'MTEXT_UNI'	=> 'clob',
			'TIMESTAMP'	=> 'number(11)',
			'DECIMAL'	=> 'number(5, 2)',
			'DECIMAL:'	=> 'number(%d, 2)',
			'PDECIMAL'	=> 'number(6, 3)',
			'PDECIMAL:'	=> 'number(%d, 3)',
			'VCHAR_UNI'	=> 'varchar2(255 char)',
			'VCHAR_UNI:'=> 'varchar2(%d char)',
			'VCHAR_CI'	=> 'varchar2(255 char)',
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

		'db2'		=> array(
			'INT:'		=> 'integer',
			'BINT'		=> 'float',
			'UINT'		=> 'integer',
			'UINT:'		=> 'integer',
			'TINT:'		=> 'smallint',
			'USINT'		=> 'smallint',
			'BOOL'		=> 'smallint',
			'VCHAR'		=> 'varchar(255)',
			'VCHAR:'	=> 'varchar(%d)',
			'CHAR:'		=> 'char(%d)',
			'XSTEXT'	=> 'clob(65K)',
			'STEXT'		=> 'varchar(3000)',
			'TEXT'		=> 'clob(65K)',
			'MTEXT'		=> 'clob(16M)',
			'XSTEXT_UNI'=> 'varchar(100)',
			'STEXT_UNI'	=> 'varchar(255)',
			'TEXT_UNI'	=> 'clob(65K)',
			'MTEXT_UNI'	=> 'clob(16M)',
			'TIMESTAMP'	=> 'integer',
			'DECIMAL'	=> 'float',
			'VCHAR_UNI'	=> 'varchar(255)',
			'VCHAR_UNI:'=> 'varchar(%d)',
			'VCHAR_CI'	=> 'varchar(255)',
			'VARBINARY'	=> 'varchar(255)',
		),

		'postgres'	=> array(
			'INT:'		=> 'INT4',
			'BINT'		=> 'INT8',
			'UINT'		=> 'INT4', // unsigned
			'UINT:'		=> 'INT4', // unsigned
			'USINT'		=> 'INT2', // unsigned
			'BOOL'		=> 'boolean', // unsigned
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
			'VCHAR_CI'	=> 'varchar(255)',
			'VARBINARY'	=> 'bytea',
		),
	);

	// A list of types being unsigned for better reference in some db's
	public static $unsigned_types = array('UINT', 'UINT:', 'USINT', 'BOOL', 'TIMESTAMP');
	public static $supported_dbms = array('firebird', 'mssql', 'mysql', 'oracle', 'postgres', 'sqlite');

	/**
	* Set this to true if you only want to return the 'to-be-executed' SQL statement(s) (as an array).
	*/
	public static $return_statements = false;

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
	*	add_index: adding an index
	*
	* The values are in this format:
	*		{TABLE NAME}		=> array(
	*			{COLUMN NAME}		=> array({COLUMN TYPE}, {DEFAULT VALUE}, {OPTIONAL VARIABLES}),
	*			{KEY/INDEX NAME}	=> array({COLUMN NAMES}),
	*		)
	*
	* For more information have a look at /install/schemas/schema_data.php (only available through CVS)
	*/
	public static function perform_schema_changes($schema_changes)
	{
		if (empty($schema_changes))
		{
			return;
		}

		$statements = array();

		// Change columns?
		if (!empty($schema_changes['change_columns']))
		{
			foreach ($schema_changes['change_columns'] as $table => $columns)
			{
				foreach ($columns as $column_name => $column_data)
				{
					$result = self::sql_column_change($table, $column_name, $column_data);

					if (self::$return_statements)
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
					if (!self::sql_column_exists($table, $column_name))
					{
						$result = self::sql_column_add($table, $column_name, $column_data);

						if (self::$return_statements)
						{
							$statements = array_merge($statements, $result);
						}
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
					$result = self::sql_index_drop($table, $index_name);

					if (self::$return_statements)
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
					$result = self::sql_column_remove($table, $column);

					if (self::$return_statements)
					{
						$statements = array_merge($statements, $result);
					}
				}
			}
		}

		// Add primary keys?
		if (!empty($schema_changes['add_primary_keys']))
		{
			foreach ($schema_changes['add_primary_keys'] as $table => $columns)
			{
				$result = self::sql_create_primary_key($table, $columns);

				if (self::$return_statements)
				{
					$statements = array_merge($statements, $result);
				}
			}
		}

		// Add unqiue indexes?
		if (!empty($schema_changes['add_unique_index']))
		{
			foreach ($schema_changes['add_unique_index'] as $table => $index_array)
			{
				foreach ($index_array as $index_name => $column)
				{
					$result = self::sql_create_unique_index($table, $index_name, $column);

					if (self::$return_statements)
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
					$result = self::sql_create_unique_index($table, $index_name, $column);

					if (self::$return_statements)
					{
						$statements = array_merge($statements, $result);
					}
				}
			}
		}

		if (self::$return_statements)
		{
			return $statements;
		}
	}

	/**
	* Check if a specified column exist
	* @return bool True if column exists, else false
	*/
	public static function sql_column_exists($table, $column_name)
	{
		global $db;

		switch ($db->dbms_type)
		{
			case 'mysql':

				$sql = "SHOW COLUMNS FROM $table";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					// lower case just in case
					if (strtolower($row['Field']) == $column_name)
					{
						$db->sql_freeresult($result);
						return true;
					}
				}
				$db->sql_freeresult($result);
				return false;
			break;

			// PostgreSQL has a way of doing this in a much simpler way but would
			// not allow us to support all versions of PostgreSQL
			case 'postgres':
				$sql = "SELECT a.attname
					FROM pg_class c, pg_attribute a
					WHERE c.relname = '{$table}'
						AND a.attnum > 0
						AND a.attrelid = c.oid";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					// lower case just in case
					if (strtolower($row['attname']) == $column_name)
					{
						$db->sql_freeresult($result);
						return true;
					}
				}
				$db->sql_freeresult($result);

				return false;
			break;

			// same deal with PostgreSQL, we must perform more complex operations than
			// we technically could
			case 'mssql':
				$sql = "SELECT c.name
					FROM syscolumns c
					LEFT JOIN sysobjects o ON c.id = o.id
					WHERE o.name = '{$table}'";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					// lower case just in case
					if (strtolower($row['name']) == $column_name)
					{
						$db->sql_freeresult($result);
						return true;
					}
				}
				$db->sql_freeresult($result);
				return false;
			break;

			case 'oracle':
				$sql = "SELECT column_name
					FROM user_tab_columns
					WHERE table_name = '{$table}'";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					// lower case just in case
					if (strtolower($row['column_name']) == $column_name)
					{
						$db->sql_freeresult($result);
						return true;
					}
				}
				$db->sql_freeresult($result);
				return false;
			break;

			case 'firebird':
				$sql = "SELECT RDB\$FIELD_NAME as FNAME
					FROM RDB\$RELATION_FIELDS
					WHERE RDB\$RELATION_NAME = '{$table}'";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					// lower case just in case
					if (strtolower($row['fname']) == $column_name)
					{
						$db->sql_freeresult($result);
						return true;
					}
				}
				$db->sql_freeresult($result);
				return false;
			break;

			case 'db2':
				$sql = "SELECT colname
					FROM syscat.columns
					WHERE tabname = '$table'";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					// lower case just in case
					if (strtolower($row['colname']) == $column_name)
					{
						$db->sql_freeresult($result);
						return true;
					}
				}
				$db->sql_freeresult($result);
				return false;
			break;

			// ugh, SQLite
			case 'sqlite':
				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table}'";
				$result = $db->sql_query($sql);

				if (!$result)
				{
					return false;
				}

				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

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

					if (strtolower($entities[0]) == $column_name)
					{
						return true;
					}
				}
				return false;
			break;
		}
	}

	/**
	* Private method for performing sql statements (either execute them or return them)
	* @private
	*/
	private static function _sql_run_sql($statements)
	{
		if (self::$return_statements)
		{
			return $statements;
		}

		global $db;

		// We could add error handling here...
		foreach ($statements as $sql)
		{
			if ($sql === 'begin')
			{
				$db->sql_transaction('begin');
			}
			else if ($sql === 'commit')
			{
				$db->sql_transaction('commit');
			}
			else
			{
				$db->sql_query($sql);
			}
		}

		return true;
	}

	/**
	* Function to prepare some column information for better usage
	* @private
	*/
	private static function sql_prepare_column_data($table_name, $column_name, $column_data)
	{
		global $db;

		// Get type
		if (strpos($column_data[0], ':') !== false)
		{
			list($orig_column_type, $column_length) = explode(':', $column_data[0]);

			if (!is_array(self::$dbms_type_map[$db->dbms_type][$orig_column_type . ':']))
			{
				$column_type = sprintf(self::$dbms_type_map[$db->dbms_type][$orig_column_type . ':'], $column_length);
			}

			$orig_column_type .= ':';
		}
		else
		{
			$orig_column_type = $column_data[0];
			$column_type = self::$dbms_type_map[$db->dbms_type][$column_data[0]];
		}

		// Adjust default value if db-dependant specified
		if (is_array($column_data[1]))
		{
			$column_data[1] = (isset($column_data[1][$db->dbms_type])) ? $column_data[1][$db->dbms_type] : $column_data[1]['default'];
		}

		$sql = '';

		$return_array = array();

		switch ($db->dbms_type)
		{
			case 'firebird':
				$sql .= " {$column_type} ";

				if (!is_null($column_data[1]))
				{
					$sql .= 'DEFAULT ' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ' ';
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
				$sql .= " {$column_type} ";
				$sql_default = " {$column_type} ";

				// For adding columns we need the default definition
				if (!is_null($column_data[1]))
				{
					// For hexadecimal values do not use single quotes
					if (strpos($column_data[1], '0x') === 0)
					{
						$sql_default .= 'DEFAULT (' . $column_data[1] . ') ';
					}
					else
					{
						$sql_default .= 'DEFAULT (' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ') ';
					}
				}

				$return_array['textimage'] = $column_type === '[text]';

				$sql .= 'NOT NULL';
				$sql_default .= 'NOT NULL';

				$return_array['column_type_sql_default'] = $sql_default;
			break;

			case 'mysql':
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
					else if ($column_data[2] == 'true_sort')
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
				if (preg_match('/number/i', $column_type))
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
				if (in_array($orig_column_type, self::$unsigned_types))
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

			case 'db2':
				$sql .= " {$column_type} NOT NULL";

				if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
				{
					$sql .= ' GENERATED BY DEFAULT AS IDENTITY (START WITH 1, INCREMENT BY 1)';
				}
				else
				{
					if (preg_match('/^(integer|smallint|float)$/', $column_type))
					{
						$sql .= " DEFAULT {$column_data[1]}";
					}
					else
					{
						$sql .= " DEFAULT '{$column_data[1]}'";
					}
				}
			break;
		}

		$return_array['column_type_sql'] = $sql;

		return $return_array;
	}

	public static function sql_create_table($table_name, $table_data)
	{
		global $db;

		// holds the DDL for a column
		$columns = array();

		$table_sql = 'CREATE TABLE ' . $table_name . ' (' . "\n";

		// Determine if we have created a PRIMARY KEY in the earliest
		$primary_key_gen = false;

		// Determine if the table must be created with TEXTIMAGE
		$create_textimage = false;

		// Determine if the table requires a sequence
		$create_sequence = false;

		foreach ($table_data['COLUMNS'] as $column_name => $column_data)
		{
			// here lies an array, filled with information compiled on the column's data
			$prepared_column = self::sql_prepare_column_data($table_name, $column_name, $column_data);

			// here we add the definition of the new column to the list of columns
			$columns[] = "\t {$column_name} " . $prepared_column['column_type_sql'];

			// see if we have found a primary key set due to a column definition,
			// if we have found it, we can stop looking
			if (!$primary_key_gen)
			{
				$primary_key_gen = isset($prepared_column['primary_key_set']) && $prepared_column['primary_key_set'];
			}

			// create textimage DDL based off of the existance of certain column types
			if (!$create_textimage)
			{
				$create_textimage = isset($prepared_column['textimage']) && $prepared_column['textimage'];
			}

			// create sequence DDL based off of the existance of auto incrementing columns
			if (!$create_sequence && isset($prepared_column['auto_increment']) && $prepared_column['auto_increment'])
			{
				$create_sequence = $column_name;
			}
		}

		// this makes up all the columns in the create table statement
		$table_sql .= implode(",\n", $columns);

		switch ($db->dbms_type)
		{
			case 'firebird':
				$table_sql .= "\n);";
				$statements[] = $table_sql;
			break;

			case 'mssql':
				$table_sql .= "\n) ON [PRIMARY]" . (($create_textimage) ? ' TEXTIMAGE_ON [PRIMARY]' : '');
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

				switch ($db->dbms_type)
				{
					case 'mysql':
					case 'postgres':
					case 'db2':
					case 'sqlite':
						$table_sql .= ",\n\t PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . ')';
					break;

					case 'firebird':
					case 'mssql':
						$primary_key_stmts = self::sql_create_primary_key($table_name, $table_data['PRIMARY_KEY']);
						foreach ($primary_key_stmts as $pk_stmt)
						{
							$statements[] = $pk_stmt;
						}
					break;

					case 'oracle':
						$table_sql .= ",\n\t CONSTRAINT pk_{$table_name} PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . ')';
					break;
				}
			}
		}


		// close the table
		switch ($db->dbms_type)
		{
			case 'mysql':
				// make sure the table is in UTF-8 mode
				$table_sql .= "\n) CHARACTER SET `utf8` COLLATE `utf8_bin`;";
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

			case 'db2':
			case 'sqlite':
				$table_sql .= "\n);";
				$statements[] = $table_sql;
			break;

			case 'oracle':
				$table_sql .= "\n);";
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
					$trigger .= "\tFROM dual\n";
					$trigger .= "END;";

					$statements[] = $trigger;
				}
			break;

			case 'firebird':
				if ($create_sequence)
				{
					$statements[] = "CREATE SEQUENCE {$table_name}_seq;";
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

				$key_stmts = ($key_data[0] == 'UNIQUE') ? self::sql_create_unique_index($table_name, $key_name, $key_data[1]) : self::sql_create_index($table_name, $key_name, $key_data[1]);
				foreach ($key_stmts as $key_stmt)
				{
					$statements[] = $key_stmt;
				}
			}
		}

		return self::_sql_run_sql($statements);
	}

	/**
	* Add new column
	*/
	public static function sql_column_add($table_name, $column_name, $column_data)
	{
		$column_data = self::sql_prepare_column_data($table_name, $column_name, $column_data);
		$statements = array();

		switch ($db->dbms_type)
		{
			case 'firebird':
				$statements[] = 'ALTER TABLE "' . $table_name . '" ADD "' . $column_name . '" ' . $column_data['column_type_sql'];
			break;

			case 'mssql':
				$statements[] = 'ALTER TABLE [' . $table_name . '] ADD [' . $column_name . '] ' . $column_data['column_type_sql_default'];
			break;

			case 'mysql':
				$statements[] = 'ALTER TABLE `' . $table_name . '` ADD COLUMN `' . $column_name . '` ' . $column_data['column_type_sql'];
			break;

			case 'oracle':
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD ' . $column_name . ' ' . $column_data['column_type_sql'];
			break;

			case 'postgres':
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD COLUMN "' . $column_name . '" ' . $column_data['column_type_sql'];
			break;

			case 'db2':
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD ' . $column_name . ' ' . $column_data['column_type_sql'];
			break;

			case 'sqlite':
				global $db;

				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table_name}'
					ORDER BY type DESC, name;";
				$result = $db->sql_query($sql);

				if (!$result)
				{
					break;
				}

				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

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
			break;
		}

		return self::_sql_run_sql($statements);
	}

	/**
	* Drop column
	*/
	public static function sql_column_remove($table_name, $column_name)
	{
		global $db;

		$statements = array();

		switch ($db->dbms_type)
		{
			case 'firebird':
				$statements[] = 'ALTER TABLE "' . $table_name . '" DROP "' . $column_name . '"';
			break;

			case 'mssql':
				$statements[] = 'ALTER TABLE [' . $table_name . '] DROP COLUMN [' . $column_name . ']';
			break;

			case 'mysql':
				$statements[] = 'ALTER TABLE `' . $table_name . '` DROP COLUMN `' . $column_name . '`';
			break;

			case 'oracle':
				$statements[] = 'ALTER TABLE ' . $table_name . ' DROP ' . $column_name;
			break;

			case 'postgres':
				$statements[] = 'ALTER TABLE ' . $table_name . ' DROP COLUMN "' . $column_name . '"';
			break;

			case 'db2':
				$statements[] = 'ALTER TABLE ' . $table_name . ' DROP ' . $column_name;
			break;

			case 'sqlite':
				global $db;

				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table_name}'
					ORDER BY type DESC, name;";
				$result = $db->sql_query($sql);

				if (!$result)
				{
					break;
				}

				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

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

				$new_table_cols = $new_table_cols = preg_replace('/' . $column_name . '[^,]+(?:,|$)/m', '', $new_table_cols);

				// create a new table and fill it up. destroy the temp one
				$statements[] = 'CREATE TABLE ' . $table_name . ' (' . $new_table_cols . ');';
				$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
				$statements[] = 'DROP TABLE ' . $table_name . '_temp';

				$statements[] = 'commit';
			break;
		}

		return self::_sql_run_sql($statements);
	}

	/**
	* Drop Index
	*/
	public static function sql_index_drop($table_name, $index_name)
	{
		global $db;

		$statements = array();

		switch ($db->dbms_type)
		{
			case 'mssql':
				$statements[] = 'DROP INDEX ' . $table_name . '.' . $index_name;
			break;

			case 'mysql':
				$statements[] = 'DROP INDEX ' . $index_name . ' ON ' . $table_name;
			break;

			case 'firebird':
			case 'oracle':
			case 'postgres':
			case 'sqlite':
			case 'db2':
				$statements[] = 'DROP INDEX ' . $table_name . '_' . $index_name;
			break;
		}

		return self::_sql_run_sql($statements);
	}

	/**
	* Drop Table
	*/
	public static function sql_table_drop($table_name, $index_name)
	{
		global $db;

		$statements = array();

		// the most basic operation, get rid of the table
		$statements[] = 'DROP TABLE ' . $table_name;

		switch ($db->dbms_type)
		{
			case 'firebird':
				$sql = 'SELECT RDB$GENERATOR_NAME as gen
					FROM RDB$GENERATORS
					WHERE RDB$SYSTEM_FLAG = 0
						AND RDB$GENERATOR_NAME = \'' . strtoupper($table_name) . "_GEN'";
				$result = $db->sql_query($sql);

				// does a generator exist?
				if ($row = $db->sql_fetchrow($result))
				{
					$statements[] = "DROP GENERATOR {$row['gen']};";
				}
				$db->sql_freeresult($result);
			break;

			case 'oracle':
				$sql = 'SELECT A.REFERENCED_NAME
					FROM USER_DEPENDENCIES A, USER_TRIGGERS B
					WHERE A.REFERENCED_TYPE = 'SEQUENCE'
						AND A.NAME = B.TRIGGER_NAME
						AND B.TABLE_NAME = \'' . strtoupper($table_name) . "'";
				$result = $db->sql_query($sql);

				// any sequences ref'd to this table's triggers?
				while ($row = $db->sql_fetchrow($result))
				{
					$statements[] = "DROP SEQUENCE {$row['referenced_name']}";
				}
				$db->sql_freeresult($result);

			case 'postgres':
				// PGSQL does not "tightly" bind sequences and tables, we must guess...
				$sql = "SELECT relname
					FROM pg_class
					WHERE relkind = 'S'
						AND relname = '{$table_name}_seq'";
				$result = $db->sql_query($sql);

				// We don't even care about storing the results. We already know the answer if we get rows back.
				if ($db->sql_fetchrow($result))
				{
					$statements[] =  "DROP SEQUENCE {$table_name}_seq;\n";
				}
				$db->sql_freeresult($result);
			break;
		}

		return self::_sql_run_sql($statements);
	}

	/**
	* Add primary key
	*/
	public static function sql_create_primary_key($table_name, $column)
	{
		global $db;

		$statements = array();

		switch ($db->dbms_type)
		{
			case 'firebird':
			case 'postgres':
			case 'mysql':
			case 'db2':
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD PRIMARY KEY (' . implode(', ', $column) . ')';
			break;

			case 'mssql':
				$sql = "ALTER TABLE [{$table_name}] WITH NOCHECK ADD ";
				$sql .= "CONSTRAINT [PK_{$table_name}] PRIMARY KEY  CLUSTERED (";
				$sql .= '[' . implode("],\n\t\t[", $column) . ']';
				$sql .= ') ON [PRIMARY]';

				$statements[] = $sql;
			break;

			case 'oracle':
				$statements[] = 'ALTER TABLE ' . $table_name . 'add CONSTRAINT pk_' . $table_name . ' PRIMARY KEY (' . implode(', ', $column) . ')';
			break;

			case 'sqlite':
				global $db;

				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table_name}'
					ORDER BY type DESC, name;";
				$result = $db->sql_query($sql);

				if (!$result)
				{
					break;
				}

				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

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

		return self::_sql_run_sql($statements);
	}

	/**
	* Add unique index
	*/
	public static function sql_create_unique_index($table_name, $index_name, $column)
	{
		global $db;

		$statements = array();

		switch ($db->dbms_type)
		{
			case 'firebird':
			case 'postgres':
			case 'oracle':
			case 'sqlite':
			case 'db2':
				$statements[] = 'CREATE UNIQUE INDEX ' . $table_name . '_' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			break;

			case 'mysql':
				$statements[] = 'CREATE UNIQUE INDEX ' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			break;

			case 'mssql':
				$statements[] = 'CREATE UNIQUE INDEX ' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ') ON [PRIMARY]';
			break;
		}

		return self::_sql_run_sql($statements);
	}

	/**
	* Add index
	*/
	public static function sql_create_index($table_name, $index_name, $column)
	{
		global $db;

		$statements = array();

		switch ($db->dbms_type)
		{
			case 'firebird':
			case 'postgres':
			case 'oracle':
			case 'sqlite':
			case 'db2':
				$statements[] = 'CREATE INDEX ' . $table_name . '_' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			break;

			case 'mysql':
				$statements[] = 'CREATE INDEX ' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			break;

			case 'mssql':
				$statements[] = 'CREATE INDEX ' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ') ON [PRIMARY]';
			break;
		}

		return self::_sql_run_sql($statements);
	}

	/**
	* List all of the indices that belong to a table,
	* does not count:
	* * UNIQUE indices
	* * PRIMARY keys
	*/
	public static function sql_list_index($table_name)
	{
		global $db;

		$index_array = array();

		if ($db->dbms_type == 'mssql')
		{
			$sql = "EXEC sp_statistics '$table_name'";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['TYPE'] == 3)
				{
					$index_array[] = $row['INDEX_NAME'];
				}
			}
			$db->sql_freeresult($result);
		}
		else
		{
			switch ($db->dbms_type)
			{
				case 'firebird':
					$sql = "SELECT LOWER(RDB\$INDEX_NAME) as index_name
						FROM RDB\$INDICES
						WHERE RDB\$RELATION_NAME = " . strtoupper($table_name) . "
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

				case 'mysql':
					$sql = 'SHOW KEYS
						FROM ' . $table_name;
					$col = 'Key_name';
				break;

				case 'oracle':
					$sql = "SELECT index_name
						FROM user_indexes
						WHERE table_name = '" . $table_name . "'
							AND generated = 'N'";
				break;

				case 'sqlite':
					$sql = "PRAGMA index_info('" . $table_name . "');";
					$col = 'name';
				break;

				case 'db2':
					$sql = "SELECT indname
						FROM SYSCAT.INDEXES
						WHERE TABNAME = '$table_name'
							AND UNIQUERULE <> 'P'";
					$col = 'name';
			}

			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				if ($db->dbms_type == 'mysql' && !$row['Non_unique'])
				{
					continue;
				}

				switch ($db->dbms_type)
				{
					case 'firebird':
					case 'oracle':
					case 'postgres':
					case 'sqlite':
					case 'db2':
						$row[$col] = substr($row[$col], strlen($table_name) + 1);
					break;
				}

				$index_array[] = $row[$col];
			}
			$db->sql_freeresult($result);
		}

		return array_map('strtolower', $index_array);
	}

	/**
	* Change column type (not name!)
	*/
	public static function sql_column_change($table_name, $column_name, $column_data)
	{
		$column_data = self::sql_prepare_column_data($table_name, $column_name, $column_data);
		$statements = array();

		switch ($db->dbms_type)
		{
			case 'firebird':
				// Change type...
				$statements[] = 'ALTER TABLE "' . $table_name . '" ALTER COLUMN "' . $column_name . '" TYPE ' . ' ' . $column_data['column_type_sql'];
			break;

			case 'mssql':
				$statements[] = 'ALTER TABLE [' . $table_name . '] ALTER COLUMN [' . $column_name . '] ' . $column_data['column_type_sql'];
			break;

			case 'mysql':
				$statements[] = 'ALTER TABLE `' . $table_name . '` CHANGE `' . $column_name . '` `' . $column_name . '` ' . $column_data['column_type_sql'];
			break;

			case 'oracle':
				$statements[] = 'ALTER TABLE ' . $table_name . ' MODIFY ' . $column_name . ' ' . $column_data['column_type_sql'];
			break;

			case 'db2':
				$statements[] = 'ALTER TABLE ' . $table_name . ' ALTER ' . $column_name . ' SET DATA TYPE ' . $column_data['column_type_sql'];
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
					global $db;

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

					$result = $db->sql_query($constraint_sql);
					while ($row = $db->sql_fetchrow($result))
					{
						if (trim($row['constraint_data']) == trim($column_data['constraint']))
						{
							$constraint_exists = true;
							break;
						}
					}
					$db->sql_freeresult($result);

					if (!$constraint_exists)
					{
						$sql_array[] = 'ADD ' . $column_data['constraint'];
					}
				}

				$sql .= implode(', ', $sql_array);

				$statements[] = $sql;
			break;

			case 'sqlite':
				global $db;

				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table_name}'
					ORDER BY type DESC, name;";
				$result = $db->sql_query($sql);

				if (!$result)
				{
					break;
				}

				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

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

		return self::_sql_run_sql($statements);
	}
}

?>