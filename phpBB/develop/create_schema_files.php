<?php
/**
*
* @package phpBB3
* @copyright (c) 2006 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
* This file creates new schema files for every database.
* The filenames will be prefixed with an underscore to not overwrite the current schema files.
*
* If you overwrite the original schema files please make sure you save the file with UNIX linefeeds.
*/

$schema_path = dirname(__FILE__) . '/../install/schemas/';

if (!is_writable($schema_path))
{
	die('Schema path not writable');
}

$schema_data = get_schema_struct();
$dbms_type_map = array(
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

// A list of types being unsigned for better reference in some db's
$unsigned_types = array('UINT', 'UINT:', 'USINT', 'BOOL', 'TIMESTAMP');
$supported_dbms = array('firebird', 'mssql', 'mysql_40', 'mysql_41', 'oracle', 'postgres', 'sqlite');

foreach ($supported_dbms as $dbms)
{
	$fp = fopen($schema_path . $dbms . '_schema.sql', 'wb');

	$line = '';

	// Write Header
	switch ($dbms)
	{
		case 'mysql_40':
		case 'mysql_41':
		case 'firebird':
		case 'sqlite':
			fwrite($fp, "# DO NOT EDIT THIS FILE, IT IS GENERATED\n");
			fwrite($fp, "#\n");
			fwrite($fp, "# To change the contents of this file, edit\n");
			fwrite($fp, "# phpBB/develop/create_schema_files.php and\n");
			fwrite($fp, "# run it.\n");
		break;

		case 'mssql':
		case 'oracle':
		case 'postgres':
			fwrite($fp, "/*\n");
			fwrite($fp, " * DO NOT EDIT THIS FILE, IT IS GENERATED\n");
			fwrite($fp, " *\n");
			fwrite($fp, " * To change the contents of this file, edit\n");
			fwrite($fp, " * phpBB/develop/create_schema_files.php and\n");
			fwrite($fp, " * run it.\n");
			fwrite($fp, " */\n\n");
		break;
	}

	switch ($dbms)
	{
		case 'firebird':
			$line .= custom_data('firebird') . "\n";
		break;

		case 'sqlite':
			$line .= "BEGIN TRANSACTION;\n\n";
		break;

		case 'oracle':
			$line .= custom_data('oracle') . "\n";
		break;

		case 'postgres':
			$line .= "BEGIN;\n\n";
			$line .= custom_data('postgres') . "\n";
		break;
	}

	fwrite($fp, $line);

	foreach ($schema_data as $table_name => $table_data)
	{
		// Write comment about table
		switch ($dbms)
		{
			case 'mysql_40':
			case 'mysql_41':
			case 'firebird':
			case 'sqlite':
				fwrite($fp, "# Table: '{$table_name}'\n");
			break;

			case 'mssql':
			case 'oracle':
			case 'postgres':
				fwrite($fp, "/*\n\tTable: '{$table_name}'\n*/\n");
			break;
		}

		// Create Table statement
		$generator = $textimage = false;
		$line = '';

		switch ($dbms)
		{
			case 'mysql_40':
			case 'mysql_41':
			case 'firebird':
			case 'oracle':
			case 'sqlite':
			case 'postgres':
				$line = "CREATE TABLE {$table_name} (\n";
			break;

			case 'mssql':
				$line = "CREATE TABLE [{$table_name}] (\n";
			break;
		}

		// Table specific so we don't get overlap
		$modded_array = array();

		// Write columns one by one...
		foreach ($table_data['COLUMNS'] as $column_name => $column_data)
		{
			if (strlen($column_name) > 30)
			{
				trigger_error("Column name '$column_name' on table '$table_name' is too long. The maximum is 30 characters.", E_USER_ERROR);
			}
			if (isset($column_data[2]) && $column_data[2] == 'auto_increment' && strlen($column_name) > 26) // "${column_name}_gen"
			{
				trigger_error("Index name '${column_name}_gen' on table '$table_name' is too long. The maximum is 30 characters.", E_USER_ERROR);
			}

			// Get type
			if (strpos($column_data[0], ':') !== false)
			{
				list($orig_column_type, $column_length) = explode(':', $column_data[0]);
				if (!is_array($dbms_type_map[$dbms][$orig_column_type . ':']))
				{
					$column_type = sprintf($dbms_type_map[$dbms][$orig_column_type . ':'], $column_length);
				}
				else
				{
					if (isset($dbms_type_map[$dbms][$orig_column_type . ':']['rule']))
					{
						switch ($dbms_type_map[$dbms][$orig_column_type . ':']['rule'][0])
						{
							case 'div':
								$column_length /= $dbms_type_map[$dbms][$orig_column_type . ':']['rule'][1];
								$column_length = ceil($column_length);
								$column_type = sprintf($dbms_type_map[$dbms][$orig_column_type . ':'][0], $column_length);
							break;
						}
					}

					if (isset($dbms_type_map[$dbms][$orig_column_type . ':']['limit']))
					{
						switch ($dbms_type_map[$dbms][$orig_column_type . ':']['limit'][0])
						{
							case 'mult':
								$column_length *= $dbms_type_map[$dbms][$orig_column_type . ':']['limit'][1];
								if ($column_length > $dbms_type_map[$dbms][$orig_column_type . ':']['limit'][2])
								{
									$column_type = $dbms_type_map[$dbms][$orig_column_type . ':']['limit'][3];
									$modded_array[$column_name] = $column_type;
								}
								else
								{
									$column_type = sprintf($dbms_type_map[$dbms][$orig_column_type . ':'][0], $column_length);
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
				$column_type = $dbms_type_map[$dbms][$column_data[0]];
				if ($column_type == 'text' || $column_type == 'blob')
				{
					$modded_array[$column_name] = $column_type;
				}
			}

			// Adjust default value if db-dependent specified
			if (is_array($column_data[1]))
			{
				$column_data[1] = (isset($column_data[1][$dbms])) ? $column_data[1][$dbms] : $column_data[1]['default'];
			}

			switch ($dbms)
			{
				case 'mysql_40':
				case 'mysql_41':
					$line .= "\t{$column_name} {$column_type} ";

					// For hexadecimal values do not use single quotes
					if (!is_null($column_data[1]) && substr($column_type, -4) !== 'text' && substr($column_type, -4) !== 'blob')
					{
						$line .= (strpos($column_data[1], '0x') === 0) ? "DEFAULT {$column_data[1]} " : "DEFAULT '{$column_data[1]}' ";
					}
					$line .= 'NOT NULL';

					if (isset($column_data[2]))
					{
						if ($column_data[2] == 'auto_increment')
						{
							$line .= ' auto_increment';
						}
						else if ($dbms === 'mysql_41' && $column_data[2] == 'true_sort')
						{
							$line .= ' COLLATE utf8_unicode_ci';
						}
					}

					$line .= ",\n";
				break;

				case 'sqlite':
					if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
					{
						$line .= "\t{$column_name} INTEGER PRIMARY KEY ";
						$generator = $column_name;
					}
					else
					{
						$line .= "\t{$column_name} {$column_type} ";
					}

					$line .= 'NOT NULL ';
					$line .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}'" : '';
					$line .= ",\n";
				break;

				case 'firebird':
					$line .= "\t{$column_name} {$column_type} ";

					if (!is_null($column_data[1]))
					{
						$line .= 'DEFAULT ' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ' ';
					}

					$line .= 'NOT NULL';

					// This is a UNICODE column and thus should be given it's fair share
					if (preg_match('/^X?STEXT_UNI|VCHAR_(CI|UNI:?)/', $column_data[0]))
					{
						$line .= ' COLLATE UNICODE';
					}

					$line .= ",\n";

					if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
					{
						$generator = $column_name;
					}
				break;

				case 'mssql':
					if ($column_type == '[text]')
					{
						$textimage = true;
					}

					$line .= "\t[{$column_name}] {$column_type} ";

					if (!is_null($column_data[1]))
					{
						// For hexadecimal values do not use single quotes
						if (strpos($column_data[1], '0x') === 0)
						{
							$line .= 'DEFAULT (' . $column_data[1] . ') ';
						}
						else
						{
							$line .= 'DEFAULT (' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ') ';
						}
					}

					if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
					{
						$line .= 'IDENTITY (1, 1) ';
					}

					$line .= 'NOT NULL';
					$line .= " ,\n";
				break;

				case 'oracle':
					$line .= "\t{$column_name} {$column_type} ";
					$line .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}' " : '';

					// In Oracle empty strings ('') are treated as NULL.
					// Therefore in oracle we allow NULL's for all DEFAULT '' entries
					$line .= ($column_data[1] === '') ? ",\n" : "NOT NULL,\n";

					if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
					{
						$generator = $column_name;
					}
				break;

				case 'postgres':
					$line .= "\t{$column_name} {$column_type} ";

					if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
					{
						$line .= "DEFAULT nextval('{$table_name}_seq'),\n";

						// Make sure the sequence will be created before creating the table
						$line = "CREATE SEQUENCE {$table_name}_seq;\n\n" . $line;
					}
					else
					{
						$line .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}' " : '';
						$line .= "NOT NULL";

						// Unsigned? Then add a CHECK contraint
						if (in_array($orig_column_type, $unsigned_types))
						{
							$line .= " CHECK ({$column_name} >= 0)";
						}

						$line .= ",\n";
					}
				break;
			}
		}

		switch ($dbms)
		{
			case 'firebird':
				// Remove last line delimiter...
				$line = substr($line, 0, -2);
				$line .= "\n);;\n\n";
			break;

			case 'mssql':
				$line = substr($line, 0, -2);
				$line .= "\n) ON [PRIMARY]" . (($textimage) ? ' TEXTIMAGE_ON [PRIMARY]' : '') . "\n";
				$line .= "GO\n\n";
			break;
		}

		// Write primary key
		if (isset($table_data['PRIMARY_KEY']))
		{
			if (!is_array($table_data['PRIMARY_KEY']))
			{
				$table_data['PRIMARY_KEY'] = array($table_data['PRIMARY_KEY']);
			}

			switch ($dbms)
			{
				case 'mysql_40':
				case 'mysql_41':
				case 'postgres':
					$line .= "\tPRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . "),\n";
				break;

				case 'firebird':
					$line .= "ALTER TABLE {$table_name} ADD PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . ");;\n\n";
				break;

				case 'sqlite':
					if ($generator === false || !in_array($generator, $table_data['PRIMARY_KEY']))
					{
						$line .= "\tPRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . "),\n";
					}
				break;

				case 'mssql':
					$line .= "ALTER TABLE [{$table_name}] WITH NOCHECK ADD \n";
					$line .= "\tCONSTRAINT [PK_{$table_name}] PRIMARY KEY  CLUSTERED \n";
					$line .= "\t(\n";
					$line .= "\t\t[" . implode("],\n\t\t[", $table_data['PRIMARY_KEY']) . "]\n";
					$line .= "\t)  ON [PRIMARY] \n";
					$line .= "GO\n\n";
				break;

				case 'oracle':
					$line .= "\tCONSTRAINT pk_{$table_name} PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . "),\n";
				break;
			}
		}

		switch ($dbms)
		{
			case 'oracle':
				// UNIQUE contrains to be added?
				if (isset($table_data['KEYS']))
				{
					foreach ($table_data['KEYS'] as $key_name => $key_data)
					{
						if (!is_array($key_data[1]))
						{
							$key_data[1] = array($key_data[1]);
						}

						if ($key_data[0] == 'UNIQUE')
						{
							$line .= "\tCONSTRAINT u_phpbb_{$key_name} UNIQUE (" . implode(', ', $key_data[1]) . "),\n";
						}
					}
				}

				// Remove last line delimiter...
				$line = substr($line, 0, -2);
				$line .= "\n)\n/\n\n";
			break;

			case 'postgres':
				// Remove last line delimiter...
				$line = substr($line, 0, -2);
				$line .= "\n);\n\n";
			break;

			case 'sqlite':
				// Remove last line delimiter...
				$line = substr($line, 0, -2);
				$line .= "\n);\n\n";
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

				if (strlen($table_name . $key_name) > 30)
				{
					trigger_error("Index name '${table_name}_$key_name' on table '$table_name' is too long. The maximum is 30 characters.", E_USER_ERROR);
				}

				switch ($dbms)
				{
					case 'mysql_40':
					case 'mysql_41':
						$line .= ($key_data[0] == 'INDEX') ? "\tKEY" : '';
						$line .= ($key_data[0] == 'UNIQUE') ? "\tUNIQUE" : '';
						foreach ($key_data[1] as $key => $col_name)
						{
							if (isset($modded_array[$col_name]))
							{
								switch ($modded_array[$col_name])
								{
									case 'text':
									case 'blob':
										$key_data[1][$key] = $col_name . '(255)';
									break;
								}
							}
						}
						$line .= ' ' . $key_name . ' (' . implode(', ', $key_data[1]) . "),\n";
					break;

					case 'firebird':
						$line .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';
						$line .= ($key_data[0] == 'UNIQUE') ? 'CREATE UNIQUE INDEX' : '';

						$line .= ' ' . $table_name . '_' . $key_name . ' ON ' . $table_name . '(' . implode(', ', $key_data[1]) . ");;\n";
					break;

					case 'mssql':
						$line .= ($key_data[0] == 'INDEX') ? 'CREATE  INDEX' : '';
						$line .= ($key_data[0] == 'UNIQUE') ? 'CREATE  UNIQUE  INDEX' : '';
						$line .= " [{$key_name}] ON [{$table_name}]([" . implode('], [', $key_data[1]) . "]) ON [PRIMARY]\n";
						$line .= "GO\n\n";
					break;

					case 'oracle':
						if ($key_data[0] == 'UNIQUE')
						{
							continue;
						}

						$line .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';

						$line .= " {$table_name}_{$key_name} ON {$table_name} (" . implode(', ', $key_data[1]) . ")\n";
						$line .= "/\n";
					break;

					case 'sqlite':
						$line .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';
						$line .= ($key_data[0] == 'UNIQUE') ? 'CREATE UNIQUE INDEX' : '';

						$line .= " {$table_name}_{$key_name} ON {$table_name} (" . implode(', ', $key_data[1]) . ");\n";
					break;

					case 'postgres':
						$line .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';
						$line .= ($key_data[0] == 'UNIQUE') ? 'CREATE UNIQUE INDEX' : '';

						$line .= " {$table_name}_{$key_name} ON {$table_name} (" . implode(', ', $key_data[1]) . ");\n";
					break;
				}
			}
		}

		switch ($dbms)
		{
			case 'mysql_40':
				// Remove last line delimiter...
				$line = substr($line, 0, -2);
				$line .= "\n);\n\n";
			break;

			case 'mysql_41':
				// Remove last line delimiter...
				$line = substr($line, 0, -2);
				$line .= "\n) CHARACTER SET `utf8` COLLATE `utf8_bin`;\n\n";
			break;

			// Create Generator
			case 'firebird':
				if ($generator !== false)
				{
					$line .= "\nCREATE GENERATOR {$table_name}_gen;;\n";
					$line .= 'SET GENERATOR ' . $table_name . "_gen TO 0;;\n\n";

					$line .= 'CREATE TRIGGER t_' . $table_name . ' FOR ' . $table_name . "\n";
					$line .= "BEFORE INSERT\nAS\nBEGIN\n";
					$line .= "\tNEW.{$generator} = GEN_ID({$table_name}_gen, 1);\nEND;;\n\n";
				}
			break;

			case 'oracle':
				if ($generator !== false)
				{
					$line .= "\nCREATE SEQUENCE {$table_name}_seq\n/\n\n";

					$line .= "CREATE OR REPLACE TRIGGER t_{$table_name}\n";
					$line .= "BEFORE INSERT ON {$table_name}\n";
					$line .= "FOR EACH ROW WHEN (\n";
					$line .= "\tnew.{$generator} IS NULL OR new.{$generator} = 0\n";
					$line .= ")\nBEGIN\n";
					$line .= "\tSELECT {$table_name}_seq.nextval\n";
					$line .= "\tINTO :new.{$generator}\n";
					$line .= "\tFROM dual;\nEND;\n/\n\n";
				}
			break;
		}

		fwrite($fp, $line . "\n");
	}

	$line = '';

	// Write custom function at the end for some db's
	switch ($dbms)
	{
		case 'mssql':
			// No need to do this, no transaction support for schema changes
			//$line = "\nCOMMIT\nGO\n\n";
		break;

		case 'sqlite':
			$line = "\nCOMMIT;";
		break;

		case 'postgres':
			$line = "\nCOMMIT;";
		break;
	}

	fwrite($fp, $line);
	fclose($fp);
}


/**
* Define the basic structure
* The format:
*		array('{TABLE_NAME}' => {TABLE_DATA})
*		{TABLE_DATA}:
*			COLUMNS = array({column_name} = array({column_type}, {default}, {auto_increment}))
*			PRIMARY_KEY = {column_name(s)}
*			KEYS = array({key_name} = array({key_type}, {column_name(s)})),
*
*	Column Types:
*	INT:x		=> SIGNED int(x)
*	BINT		=> BIGINT
*	UINT		=> mediumint(8) UNSIGNED
*	UINT:x		=> int(x) UNSIGNED
*	TINT:x		=> tinyint(x)
*	USINT		=> smallint(4) UNSIGNED (for _order columns)
*	BOOL		=> tinyint(1) UNSIGNED
*	VCHAR		=> varchar(255)
*	CHAR:x		=> char(x)
*	XSTEXT_UNI	=> text for storing 100 characters (topic_title for example)
*	STEXT_UNI	=> text for storing 255 characters (normal input field with a max of 255 single-byte chars) - same as VCHAR_UNI
*	TEXT_UNI	=> text for storing 3000 characters (short text, descriptions, comments, etc.)
*	MTEXT_UNI	=> mediumtext (post text, large text)
*	VCHAR:x		=> varchar(x)
*	TIMESTAMP	=> int(11) UNSIGNED
*	DECIMAL		=> decimal number (5,2)
*	DECIMAL:	=> decimal number (x,2)
*	PDECIMAL	=> precision decimal number (6,3)
*	PDECIMAL:	=> precision decimal number (x,3)
*	VCHAR_UNI	=> varchar(255) BINARY
*	VCHAR_CI	=> varchar_ci for postgresql, others VCHAR
*/
function get_schema_struct()
{
	$schema_data = array();

	$schema_data['phpbb_attachments'] = array(
		'COLUMNS'		=> array(
			'attach_id'			=> array('UINT', NULL, 'auto_increment'),
			'post_msg_id'		=> array('UINT', 0),
			'topic_id'			=> array('UINT', 0),
			'in_message'		=> array('BOOL', 0),
			'poster_id'			=> array('UINT', 0),
			'is_orphan'			=> array('BOOL', 1),
			'physical_filename'	=> array('VCHAR', ''),
			'real_filename'		=> array('VCHAR', ''),
			'download_count'	=> array('UINT', 0),
			'attach_comment'	=> array('TEXT_UNI', ''),
			'extension'			=> array('VCHAR:100', ''),
			'mimetype'			=> array('VCHAR:100', ''),
			'filesize'			=> array('UINT:20', 0),
			'filetime'			=> array('TIMESTAMP', 0),
			'thumbnail'			=> array('BOOL', 0),
		),
		'PRIMARY_KEY'	=> 'attach_id',
		'KEYS'			=> array(
			'filetime'			=> array('INDEX', 'filetime'),
			'post_msg_id'		=> array('INDEX', 'post_msg_id'),
			'topic_id'			=> array('INDEX', 'topic_id'),
			'poster_id'			=> array('INDEX', 'poster_id'),
			'is_orphan'			=> array('INDEX', 'is_orphan'),
		),
	);

	$schema_data['phpbb_acl_groups'] = array(
		'COLUMNS'		=> array(
			'group_id'			=> array('UINT', 0),
			'forum_id'			=> array('UINT', 0),
			'auth_option_id'	=> array('UINT', 0),
			'auth_role_id'		=> array('UINT', 0),
			'auth_setting'		=> array('TINT:2', 0),
		),
		'KEYS'			=> array(
			'group_id'		=> array('INDEX', 'group_id'),
			'auth_opt_id'	=> array('INDEX', 'auth_option_id'),
			'auth_role_id'	=> array('INDEX', 'auth_role_id'),
		),
	);

	$schema_data['phpbb_acl_options'] = array(
		'COLUMNS'		=> array(
			'auth_option_id'	=> array('UINT', NULL, 'auto_increment'),
			'auth_option'		=> array('VCHAR:50', ''),
			'is_global'			=> array('BOOL', 0),
			'is_local'			=> array('BOOL', 0),
			'founder_only'		=> array('BOOL', 0),
		),
		'PRIMARY_KEY'	=> 'auth_option_id',
		'KEYS'			=> array(
			'auth_option'		=> array('UNIQUE', 'auth_option'),
		),
	);

	$schema_data['phpbb_acl_roles'] = array(
		'COLUMNS'		=> array(
			'role_id'			=> array('UINT', NULL, 'auto_increment'),
			'role_name'			=> array('VCHAR_UNI', ''),
			'role_description'	=> array('TEXT_UNI', ''),
			'role_type'			=> array('VCHAR:10', ''),
			'role_order'		=> array('USINT', 0),
		),
		'PRIMARY_KEY'	=> 'role_id',
		'KEYS'			=> array(
			'role_type'			=> array('INDEX', 'role_type'),
			'role_order'		=> array('INDEX', 'role_order'),
		),
	);

	$schema_data['phpbb_acl_roles_data'] = array(
		'COLUMNS'		=> array(
			'role_id'			=> array('UINT', 0),
			'auth_option_id'	=> array('UINT', 0),
			'auth_setting'		=> array('TINT:2', 0),
		),
		'PRIMARY_KEY'	=> array('role_id', 'auth_option_id'),
		'KEYS'			=> array(
			'ath_op_id'			=> array('INDEX', 'auth_option_id'),
		),
	);

	$schema_data['phpbb_acl_users'] = array(
		'COLUMNS'		=> array(
			'user_id'			=> array('UINT', 0),
			'forum_id'			=> array('UINT', 0),
			'auth_option_id'	=> array('UINT', 0),
			'auth_role_id'		=> array('UINT', 0),
			'auth_setting'		=> array('TINT:2', 0),
		),
		'KEYS'			=> array(
			'user_id'			=> array('INDEX', 'user_id'),
			'auth_option_id'	=> array('INDEX', 'auth_option_id'),
			'auth_role_id'		=> array('INDEX', 'auth_role_id'),
		),
	);

	$schema_data['phpbb_banlist'] = array(
		'COLUMNS'		=> array(
			'ban_id'			=> array('UINT', NULL, 'auto_increment'),
			'ban_userid'		=> array('UINT', 0),
			'ban_ip'			=> array('VCHAR:40', ''),
			'ban_email'			=> array('VCHAR_UNI:100', ''),
			'ban_start'			=> array('TIMESTAMP', 0),
			'ban_end'			=> array('TIMESTAMP', 0),
			'ban_exclude'		=> array('BOOL', 0),
			'ban_reason'		=> array('VCHAR_UNI', ''),
			'ban_give_reason'	=> array('VCHAR_UNI', ''),
		),
		'PRIMARY_KEY'			=> 'ban_id',
		'KEYS'			=> array(
			'ban_end'			=> array('INDEX', 'ban_end'),
			'ban_user'			=> array('INDEX', array('ban_userid', 'ban_exclude')),
			'ban_email'			=> array('INDEX', array('ban_email', 'ban_exclude')),
			'ban_ip'			=> array('INDEX', array('ban_ip', 'ban_exclude')),
		),
	);

	$schema_data['phpbb_bbcodes'] = array(
		'COLUMNS'		=> array(
			'bbcode_id'				=> array('USINT', 0),
			'bbcode_tag'			=> array('VCHAR:16', ''),
			'bbcode_helpline'		=> array('VCHAR_UNI', ''),
			'display_on_posting'	=> array('BOOL', 0),
			'bbcode_match'			=> array('TEXT_UNI', ''),
			'bbcode_tpl'			=> array('MTEXT_UNI', ''),
			'first_pass_match'		=> array('MTEXT_UNI', ''),
			'first_pass_replace'	=> array('MTEXT_UNI', ''),
			'second_pass_match'		=> array('MTEXT_UNI', ''),
			'second_pass_replace'	=> array('MTEXT_UNI', ''),
		),
		'PRIMARY_KEY'	=> 'bbcode_id',
		'KEYS'			=> array(
			'display_on_post'		=> array('INDEX', 'display_on_posting'),
		),
	);

	$schema_data['phpbb_bookmarks'] = array(
		'COLUMNS'		=> array(
			'topic_id'			=> array('UINT', 0),
			'user_id'			=> array('UINT', 0),
		),
		'PRIMARY_KEY'			=> array('topic_id', 'user_id'),
	);

	$schema_data['phpbb_bots'] = array(
		'COLUMNS'		=> array(
			'bot_id'			=> array('UINT', NULL, 'auto_increment'),
			'bot_active'		=> array('BOOL', 1),
			'bot_name'			=> array('STEXT_UNI', ''),
			'user_id'			=> array('UINT', 0),
			'bot_agent'			=> array('VCHAR', ''),
			'bot_ip'			=> array('VCHAR', ''),
		),
		'PRIMARY_KEY'	=> 'bot_id',
		'KEYS'			=> array(
			'bot_active'		=> array('INDEX', 'bot_active'),
		),
	);

	$schema_data['phpbb_config'] = array(
		'COLUMNS'		=> array(
			'config_name'		=> array('VCHAR', ''),
			'config_value'		=> array('VCHAR_UNI', ''),
			'is_dynamic'		=> array('BOOL', 0),
		),
		'PRIMARY_KEY'	=> 'config_name',
		'KEYS'			=> array(
			'is_dynamic'		=> array('INDEX', 'is_dynamic'),
		),
	);

	$schema_data['phpbb_config_text'] = array(
		'COLUMNS'		=> array(
			'config_name'		=> array('VCHAR', ''),
			'config_value'		=> array('MTEXT', ''),
		),
		'PRIMARY_KEY'	=> 'config_name',
	);

	$schema_data['phpbb_confirm'] = array(
		'COLUMNS'		=> array(
			'confirm_id'		=> array('CHAR:32', ''),
			'session_id'		=> array('CHAR:32', ''),
			'confirm_type'		=> array('TINT:3', 0),
			'code'				=> array('VCHAR:8', ''),
			'seed'				=> array('UINT:10', 0),
			'attempts'			=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> array('session_id', 'confirm_id'),
		'KEYS'			=> array(
			'confirm_type'		=> array('INDEX', 'confirm_type'),
		),
	);

	$schema_data['phpbb_disallow'] = array(
		'COLUMNS'		=> array(
			'disallow_id'		=> array('UINT', NULL, 'auto_increment'),
			'disallow_username'	=> array('VCHAR_UNI:255', ''),
		),
		'PRIMARY_KEY'	=> 'disallow_id',
	);

	$schema_data['phpbb_drafts'] = array(
		'COLUMNS'		=> array(
			'draft_id'			=> array('UINT', NULL, 'auto_increment'),
			'user_id'			=> array('UINT', 0),
			'topic_id'			=> array('UINT', 0),
			'forum_id'			=> array('UINT', 0),
			'save_time'			=> array('TIMESTAMP', 0),
			'draft_subject'		=> array('STEXT_UNI', ''),
			'draft_message'		=> array('MTEXT_UNI', ''),
		),
		'PRIMARY_KEY'	=> 'draft_id',
		'KEYS'			=> array(
			'save_time'			=> array('INDEX', 'save_time'),
		),
	);

	$schema_data['phpbb_ext'] = array(
		'COLUMNS'		=> array(
			'ext_name'				=> array('VCHAR', ''),
			'ext_active'			=> array('BOOL', 0),
			'ext_state'				=> array('TEXT', ''),
		),
		'KEYS'			=> array(
			'ext_name'				=> array('UNIQUE', 'ext_name'),
		),
	);

	$schema_data['phpbb_extensions'] = array(
		'COLUMNS'		=> array(
			'extension_id'		=> array('UINT', NULL, 'auto_increment'),
			'group_id'			=> array('UINT', 0),
			'extension'			=> array('VCHAR:100', ''),
		),
		'PRIMARY_KEY'	=> 'extension_id',
	);

	$schema_data['phpbb_extension_groups'] = array(
		'COLUMNS'		=> array(
			'group_id'			=> array('UINT', NULL, 'auto_increment'),
			'group_name'		=> array('VCHAR_UNI', ''),
			'cat_id'			=> array('TINT:2', 0),
			'allow_group'		=> array('BOOL', 0),
			'download_mode'		=> array('BOOL', 1),
			'upload_icon'		=> array('VCHAR', ''),
			'max_filesize'		=> array('UINT:20', 0),
			'allowed_forums'	=> array('TEXT', ''),
			'allow_in_pm'		=> array('BOOL', 0),
		),
		'PRIMARY_KEY'	=> 'group_id',
	);

	$schema_data['phpbb_forums'] = array(
		'COLUMNS'		=> array(
			'forum_id'				=> array('UINT', NULL, 'auto_increment'),
			'parent_id'				=> array('UINT', 0),
			'left_id'				=> array('UINT', 0),
			'right_id'				=> array('UINT', 0),
			'forum_parents'			=> array('MTEXT', ''),
			'forum_name'			=> array('STEXT_UNI', ''),
			'forum_desc'			=> array('TEXT_UNI', ''),
			'forum_desc_bitfield'	=> array('VCHAR:255', ''),
			'forum_desc_options'	=> array('UINT:11', 7),
			'forum_desc_uid'		=> array('VCHAR:8', ''),
			'forum_link'			=> array('VCHAR_UNI', ''),
			'forum_password'		=> array('VCHAR_UNI:40', ''),
			'forum_style'			=> array('UINT', 0),
			'forum_image'			=> array('VCHAR', ''),
			'forum_rules'			=> array('TEXT_UNI', ''),
			'forum_rules_link'		=> array('VCHAR_UNI', ''),
			'forum_rules_bitfield'	=> array('VCHAR:255', ''),
			'forum_rules_options'	=> array('UINT:11', 7),
			'forum_rules_uid'		=> array('VCHAR:8', ''),
			'forum_topics_per_page'	=> array('TINT:4', 0),
			'forum_type'			=> array('TINT:4', 0),
			'forum_status'			=> array('TINT:4', 0),
			'forum_posts'			=> array('UINT', 0),
			'forum_topics'			=> array('UINT', 0),
			'forum_topics_real'		=> array('UINT', 0),
			'forum_last_post_id'	=> array('UINT', 0),
			'forum_last_poster_id'	=> array('UINT', 0),
			'forum_last_post_subject' => array('STEXT_UNI', ''),
			'forum_last_post_time'	=> array('TIMESTAMP', 0),
			'forum_last_poster_name'=> array('VCHAR_UNI', ''),
			'forum_last_poster_colour'=> array('VCHAR:6', ''),
			'forum_flags'			=> array('TINT:4', 32),
			'forum_options'			=> array('UINT:20', 0),
			'display_subforum_list'	=> array('BOOL', 1),
			'display_on_index'		=> array('BOOL', 1),
			'enable_indexing'		=> array('BOOL', 1),
			'enable_icons'			=> array('BOOL', 1),
			'enable_prune'			=> array('BOOL', 0),
			'prune_next'			=> array('TIMESTAMP', 0),
			'prune_days'			=> array('UINT', 0),
			'prune_viewed'			=> array('UINT', 0),
			'prune_freq'			=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> 'forum_id',
		'KEYS'			=> array(
			'left_right_id'			=> array('INDEX', array('left_id', 'right_id')),
			'forum_lastpost_id'		=> array('INDEX', 'forum_last_post_id'),
		),
	);

	$schema_data['phpbb_forums_access'] = array(
		'COLUMNS'		=> array(
			'forum_id'				=> array('UINT', 0),
			'user_id'				=> array('UINT', 0),
			'session_id'			=> array('CHAR:32', ''),
		),
		'PRIMARY_KEY'	=> array('forum_id', 'user_id', 'session_id'),
	);

	$schema_data['phpbb_forums_track'] = array(
		'COLUMNS'		=> array(
			'user_id'				=> array('UINT', 0),
			'forum_id'				=> array('UINT', 0),
			'mark_time'				=> array('TIMESTAMP', 0),
		),
		'PRIMARY_KEY'	=> array('user_id', 'forum_id'),
	);

	$schema_data['phpbb_forums_watch'] = array(
		'COLUMNS'		=> array(
			'forum_id'				=> array('UINT', 0),
			'user_id'				=> array('UINT', 0),
			'notify_status'			=> array('BOOL', 0),
		),
		'KEYS'			=> array(
			'forum_id'				=> array('INDEX', 'forum_id'),
			'user_id'				=> array('INDEX', 'user_id'),
			'notify_stat'			=> array('INDEX', 'notify_status'),
		),
	);

	$schema_data['phpbb_groups'] = array(
		'COLUMNS'		=> array(
			'group_id'				=> array('UINT', NULL, 'auto_increment'),
			'group_type'			=> array('TINT:4', 1),
			'group_founder_manage'	=> array('BOOL', 0),
			'group_skip_auth'		=> array('BOOL', 0),
			'group_name'			=> array('VCHAR_CI', ''),
			'group_desc'			=> array('TEXT_UNI', ''),
			'group_desc_bitfield'	=> array('VCHAR:255', ''),
			'group_desc_options'	=> array('UINT:11', 7),
			'group_desc_uid'		=> array('VCHAR:8', ''),
			'group_display'			=> array('BOOL', 0),
			'group_avatar'			=> array('VCHAR', ''),
			'group_avatar_type'		=> array('VCHAR:255', ''),
			'group_avatar_width'	=> array('USINT', 0),
			'group_avatar_height'	=> array('USINT', 0),
			'group_rank'			=> array('UINT', 0),
			'group_colour'			=> array('VCHAR:6', ''),
			'group_sig_chars'		=> array('UINT', 0),
			'group_receive_pm'		=> array('BOOL', 0),
			'group_message_limit'	=> array('UINT', 0),
			'group_max_recipients'	=> array('UINT', 0),
			'group_legend'			=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> 'group_id',
		'KEYS'			=> array(
			'group_legend_name'		=> array('INDEX', array('group_legend', 'group_name')),
		),
	);

	$schema_data['phpbb_icons'] = array(
		'COLUMNS'		=> array(
			'icons_id'				=> array('UINT', NULL, 'auto_increment'),
			'icons_url'				=> array('VCHAR', ''),
			'icons_width'			=> array('TINT:4', 0),
			'icons_height'			=> array('TINT:4', 0),
			'icons_order'			=> array('UINT', 0),
			'display_on_posting'	=> array('BOOL', 1),
		),
		'PRIMARY_KEY'	=> 'icons_id',
		'KEYS'			=> array(
			'display_on_posting'	=> array('INDEX', 'display_on_posting'),
		),
	);

	$schema_data['phpbb_lang'] = array(
		'COLUMNS'		=> array(
			'lang_id'				=> array('TINT:4', NULL, 'auto_increment'),
			'lang_iso'				=> array('VCHAR:30', ''),
			'lang_dir'				=> array('VCHAR:30', ''),
			'lang_english_name'		=> array('VCHAR_UNI:100', ''),
			'lang_local_name'		=> array('VCHAR_UNI:255', ''),
			'lang_author'			=> array('VCHAR_UNI:255', ''),
		),
		'PRIMARY_KEY'	=> 'lang_id',
		'KEYS'			=> array(
			'lang_iso'				=> array('INDEX', 'lang_iso'),
		),
	);

	$schema_data['phpbb_log'] = array(
		'COLUMNS'		=> array(
			'log_id'				=> array('UINT', NULL, 'auto_increment'),
			'log_type'				=> array('TINT:4', 0),
			'user_id'				=> array('UINT', 0),
			'forum_id'				=> array('UINT', 0),
			'topic_id'				=> array('UINT', 0),
			'reportee_id'			=> array('UINT', 0),
			'log_ip'				=> array('VCHAR:40', ''),
			'log_time'				=> array('TIMESTAMP', 0),
			'log_operation'			=> array('TEXT_UNI', ''),
			'log_data'				=> array('MTEXT_UNI', ''),
		),
		'PRIMARY_KEY'	=> 'log_id',
		'KEYS'			=> array(
			'log_type'				=> array('INDEX', 'log_type'),
			'log_time'				=> array('INDEX', 'log_time'),
			'forum_id'				=> array('INDEX', 'forum_id'),
			'topic_id'				=> array('INDEX', 'topic_id'),
			'reportee_id'			=> array('INDEX', 'reportee_id'),
			'user_id'				=> array('INDEX', 'user_id'),
		),
	);

	$schema_data['phpbb_login_attempts'] = array(
		'COLUMNS'		=> array(
			'attempt_ip'			=> array('VCHAR:40', ''),
			'attempt_browser'		=> array('VCHAR:150', ''),
			'attempt_forwarded_for'	=> array('VCHAR:255', ''),
			'attempt_time'			=> array('TIMESTAMP', 0),
			'user_id'				=> array('UINT', 0),
			'username'				=> array('VCHAR_UNI:255', 0),
			'username_clean'		=> array('VCHAR_CI', 0),
		),
		'KEYS'			=> array(
			'att_ip'				=> array('INDEX', array('attempt_ip', 'attempt_time')),
			'att_for'		=> array('INDEX', array('attempt_forwarded_for', 'attempt_time')),
			'att_time'				=> array('INDEX', array('attempt_time')),
			'user_id'					=> array('INDEX', 'user_id'),
		),
	);

	$schema_data['phpbb_moderator_cache'] = array(
		'COLUMNS'		=> array(
			'forum_id'				=> array('UINT', 0),
			'user_id'				=> array('UINT', 0),
			'username'				=> array('VCHAR_UNI:255', ''),
			'group_id'				=> array('UINT', 0),
			'group_name'			=> array('VCHAR_UNI', ''),
			'display_on_index'		=> array('BOOL', 1),
		),
		'KEYS'			=> array(
			'disp_idx'				=> array('INDEX', 'display_on_index'),
			'forum_id'				=> array('INDEX', 'forum_id'),
		),
	);

	$schema_data['phpbb_migrations'] = array(
		'COLUMNS'		=> array(
			'migration_name'			=> array('VCHAR', ''),
			'migration_depends_on'		=> array('TEXT', ''),
			'migration_schema_done'		=> array('BOOL', 0),
			'migration_data_done'		=> array('BOOL', 0),
			'migration_data_state'		=> array('TEXT', ''),
			'migration_start_time'		=> array('TIMESTAMP', 0),
			'migration_end_time'		=> array('TIMESTAMP', 0),
		),
		'PRIMARY_KEY'	=> 'migration_name',
	);

	$schema_data['phpbb_modules'] = array(
		'COLUMNS'		=> array(
			'module_id'				=> array('UINT', NULL, 'auto_increment'),
			'module_enabled'		=> array('BOOL', 1),
			'module_display'		=> array('BOOL', 1),
			'module_basename'		=> array('VCHAR', ''),
			'module_class'			=> array('VCHAR:10', ''),
			'parent_id'				=> array('UINT', 0),
			'left_id'				=> array('UINT', 0),
			'right_id'				=> array('UINT', 0),
			'module_langname'		=> array('VCHAR', ''),
			'module_mode'			=> array('VCHAR', ''),
			'module_auth'			=> array('VCHAR', ''),
		),
		'PRIMARY_KEY'	=> 'module_id',
		'KEYS'			=> array(
			'left_right_id'			=> array('INDEX', array('left_id', 'right_id')),
			'module_enabled'		=> array('INDEX', 'module_enabled'),
			'class_left_id'			=> array('INDEX', array('module_class', 'left_id')),
		),
	);

	$schema_data['phpbb_notification_types'] = array(
		'COLUMNS'			=> array(
			'notification_type_id'		=> array('USINT', NULL, 'auto_increment'),
			'notification_type_name'	=> array('VCHAR:255', ''),
			'notification_type_enabled'	=> array('BOOL', 1),
		),
		'PRIMARY_KEY'		=> array('notification_type_id'),
		'KEYS'				=> array(
			'type'			=> array('UNIQUE', array('notification_type_name')),
		),
	);

	$schema_data['phpbb_notifications'] = array(
		'COLUMNS'			=> array(
			'notification_id'				=> array('UINT:10', NULL, 'auto_increment'),
			'notification_type_id'			=> array('USINT', 0),
			'item_id'						=> array('UINT', 0),
			'item_parent_id'				=> array('UINT', 0),
			'user_id'						=> array('UINT', 0),
			'notification_read'				=> array('BOOL', 0),
			'notification_time'				=> array('TIMESTAMP', 1),
			'notification_data'				=> array('TEXT_UNI', ''),
		),
		'PRIMARY_KEY'		=> 'notification_id',
		'KEYS'				=> array(
			'item_ident'		=> array('INDEX', array('notification_type_id', 'item_id')),
			'user'				=> array('INDEX', array('user_id', 'notification_read')),
		),
	);

	$schema_data['phpbb_poll_options'] = array(
		'COLUMNS'		=> array(
			'poll_option_id'		=> array('TINT:4', 0),
			'topic_id'				=> array('UINT', 0),
			'poll_option_text'		=> array('TEXT_UNI', ''),
			'poll_option_total'		=> array('UINT', 0),
		),
		'KEYS'			=> array(
			'poll_opt_id'			=> array('INDEX', 'poll_option_id'),
			'topic_id'				=> array('INDEX', 'topic_id'),
		),
	);

	$schema_data['phpbb_poll_votes'] = array(
		'COLUMNS'		=> array(
			'topic_id'				=> array('UINT', 0),
			'poll_option_id'		=> array('TINT:4', 0),
			'vote_user_id'			=> array('UINT', 0),
			'vote_user_ip'			=> array('VCHAR:40', ''),
		),
		'KEYS'			=> array(
			'topic_id'				=> array('INDEX', 'topic_id'),
			'vote_user_id'			=> array('INDEX', 'vote_user_id'),
			'vote_user_ip'			=> array('INDEX', 'vote_user_ip'),
		),
	);

	$schema_data['phpbb_posts'] = array(
		'COLUMNS'		=> array(
			'post_id'				=> array('UINT', NULL, 'auto_increment'),
			'topic_id'				=> array('UINT', 0),
			'forum_id'				=> array('UINT', 0),
			'poster_id'				=> array('UINT', 0),
			'icon_id'				=> array('UINT', 0),
			'poster_ip'				=> array('VCHAR:40', ''),
			'post_time'				=> array('TIMESTAMP', 0),
			'post_approved'			=> array('BOOL', 1),
			'post_reported'			=> array('BOOL', 0),
			'enable_bbcode'			=> array('BOOL', 1),
			'enable_smilies'		=> array('BOOL', 1),
			'enable_magic_url'		=> array('BOOL', 1),
			'enable_sig'			=> array('BOOL', 1),
			'post_username'			=> array('VCHAR_UNI:255', ''),
			'post_subject'			=> array('STEXT_UNI', '', 'true_sort'),
			'post_text'				=> array('MTEXT_UNI', ''),
			'post_checksum'			=> array('VCHAR:32', ''),
			'post_attachment'		=> array('BOOL', 0),
			'bbcode_bitfield'		=> array('VCHAR:255', ''),
			'bbcode_uid'			=> array('VCHAR:8', ''),
			'post_postcount'		=> array('BOOL', 1),
			'post_edit_time'		=> array('TIMESTAMP', 0),
			'post_edit_reason'		=> array('STEXT_UNI', ''),
			'post_edit_user'		=> array('UINT', 0),
			'post_edit_count'		=> array('USINT', 0),
			'post_edit_locked'		=> array('BOOL', 0),
		),
		'PRIMARY_KEY'	=> 'post_id',
		'KEYS'			=> array(
			'forum_id'				=> array('INDEX', 'forum_id'),
			'topic_id'				=> array('INDEX', 'topic_id'),
			'poster_ip'				=> array('INDEX', 'poster_ip'),
			'poster_id'				=> array('INDEX', 'poster_id'),
			'post_approved'			=> array('INDEX', 'post_approved'),
			'post_username'			=> array('INDEX', 'post_username'),
			'tid_post_time'			=> array('INDEX', array('topic_id', 'post_time')),
		),
	);

	$schema_data['phpbb_privmsgs'] = array(
		'COLUMNS'		=> array(
			'msg_id'				=> array('UINT', NULL, 'auto_increment'),
			'root_level'			=> array('UINT', 0),
			'author_id'				=> array('UINT', 0),
			'icon_id'				=> array('UINT', 0),
			'author_ip'				=> array('VCHAR:40', ''),
			'message_time'			=> array('TIMESTAMP', 0),
			'enable_bbcode'			=> array('BOOL', 1),
			'enable_smilies'		=> array('BOOL', 1),
			'enable_magic_url'		=> array('BOOL', 1),
			'enable_sig'			=> array('BOOL', 1),
			'message_subject'		=> array('STEXT_UNI', ''),
			'message_text'			=> array('MTEXT_UNI', ''),
			'message_edit_reason'	=> array('STEXT_UNI', ''),
			'message_edit_user'		=> array('UINT', 0),
			'message_attachment'	=> array('BOOL', 0),
			'bbcode_bitfield'		=> array('VCHAR:255', ''),
			'bbcode_uid'			=> array('VCHAR:8', ''),
			'message_edit_time'		=> array('TIMESTAMP', 0),
			'message_edit_count'	=> array('USINT', 0),
			'to_address'			=> array('TEXT_UNI', ''),
			'bcc_address'			=> array('TEXT_UNI', ''),
			'message_reported'		=> array('BOOL', 0),
		),
		'PRIMARY_KEY'	=> 'msg_id',
		'KEYS'			=> array(
			'author_ip'				=> array('INDEX', 'author_ip'),
			'message_time'			=> array('INDEX', 'message_time'),
			'author_id'				=> array('INDEX', 'author_id'),
			'root_level'			=> array('INDEX', 'root_level'),
		),
	);

	$schema_data['phpbb_privmsgs_folder'] = array(
		'COLUMNS'		=> array(
			'folder_id'				=> array('UINT', NULL, 'auto_increment'),
			'user_id'				=> array('UINT', 0),
			'folder_name'			=> array('VCHAR_UNI', ''),
			'pm_count'				=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> 'folder_id',
		'KEYS'			=> array(
			'user_id'				=> array('INDEX', 'user_id'),
		),
	);

	$schema_data['phpbb_privmsgs_rules'] = array(
		'COLUMNS'		=> array(
			'rule_id'				=> array('UINT', NULL, 'auto_increment'),
			'user_id'				=> array('UINT', 0),
			'rule_check'			=> array('UINT', 0),
			'rule_connection'		=> array('UINT', 0),
			'rule_string'			=> array('VCHAR_UNI', ''),
			'rule_user_id'			=> array('UINT', 0),
			'rule_group_id'			=> array('UINT', 0),
			'rule_action'			=> array('UINT', 0),
			'rule_folder_id'		=> array('INT:11', 0),
		),
		'PRIMARY_KEY'	=> 'rule_id',
		'KEYS'			=> array(
			'user_id'				=> array('INDEX', 'user_id'),
		),
	);

	$schema_data['phpbb_privmsgs_to'] = array(
		'COLUMNS'		=> array(
			'msg_id'				=> array('UINT', 0),
			'user_id'				=> array('UINT', 0),
			'author_id'				=> array('UINT', 0),
			'pm_deleted'			=> array('BOOL', 0),
			'pm_new'				=> array('BOOL', 1),
			'pm_unread'				=> array('BOOL', 1),
			'pm_replied'			=> array('BOOL', 0),
			'pm_marked'				=> array('BOOL', 0),
			'pm_forwarded'			=> array('BOOL', 0),
			'folder_id'				=> array('INT:11', 0),
		),
		'KEYS'			=> array(
			'msg_id'				=> array('INDEX', 'msg_id'),
			'author_id'				=> array('INDEX', 'author_id'),
			'usr_flder_id'			=> array('INDEX', array('user_id', 'folder_id')),
		),
	);

	$schema_data['phpbb_profile_fields'] = array(
		'COLUMNS'		=> array(
			'field_id'				=> array('UINT', NULL, 'auto_increment'),
			'field_name'			=> array('VCHAR_UNI', ''),
			'field_type'			=> array('TINT:4', 0),
			'field_ident'			=> array('VCHAR:20', ''),
			'field_length'			=> array('VCHAR:20', ''),
			'field_minlen'			=> array('VCHAR', ''),
			'field_maxlen'			=> array('VCHAR', ''),
			'field_novalue'			=> array('VCHAR_UNI', ''),
			'field_default_value'	=> array('VCHAR_UNI', ''),
			'field_validation'		=> array('VCHAR_UNI:20', ''),
			'field_required'		=> array('BOOL', 0),
			'field_show_novalue'	=> array('BOOL', 0),
			'field_show_on_reg'		=> array('BOOL', 0),
			'field_show_on_pm'		=> array('BOOL', 0),
			'field_show_on_vt'		=> array('BOOL', 0),
			'field_show_profile'	=> array('BOOL', 0),
			'field_hide'			=> array('BOOL', 0),
			'field_no_view'			=> array('BOOL', 0),
			'field_active'			=> array('BOOL', 0),
			'field_order'			=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> 'field_id',
		'KEYS'			=> array(
			'fld_type'			=> array('INDEX', 'field_type'),
			'fld_ordr'			=> array('INDEX', 'field_order'),
		),
	);

	$schema_data['phpbb_profile_fields_data'] = array(
		'COLUMNS'		=> array(
			'user_id'				=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> 'user_id',
	);

	$schema_data['phpbb_profile_fields_lang'] = array(
		'COLUMNS'		=> array(
			'field_id'				=> array('UINT', 0),
			'lang_id'				=> array('UINT', 0),
			'option_id'				=> array('UINT', 0),
			'field_type'			=> array('TINT:4', 0),
			'lang_value'			=> array('VCHAR_UNI', ''),
		),
		'PRIMARY_KEY'	=> array('field_id', 'lang_id', 'option_id'),
	);

	$schema_data['phpbb_profile_lang'] = array(
		'COLUMNS'		=> array(
			'field_id'				=> array('UINT', 0),
			'lang_id'				=> array('UINT', 0),
			'lang_name'				=> array('VCHAR_UNI', ''),
			'lang_explain'			=> array('TEXT_UNI', ''),
			'lang_default_value'	=> array('VCHAR_UNI', ''),
		),
		'PRIMARY_KEY'	=> array('field_id', 'lang_id'),
	);

	$schema_data['phpbb_ranks'] = array(
		'COLUMNS'		=> array(
			'rank_id'				=> array('UINT', NULL, 'auto_increment'),
			'rank_title'			=> array('VCHAR_UNI', ''),
			'rank_min'				=> array('UINT', 0),
			'rank_special'			=> array('BOOL', 0),
			'rank_image'			=> array('VCHAR', ''),
		),
		'PRIMARY_KEY'	=> 'rank_id',
	);

	$schema_data['phpbb_reports'] = array(
		'COLUMNS'		=> array(
			'report_id'							=> array('UINT', NULL, 'auto_increment'),
			'reason_id'							=> array('USINT', 0),
			'post_id'							=> array('UINT', 0),
			'pm_id'								=> array('UINT', 0),
			'user_id'							=> array('UINT', 0),
			'user_notify'						=> array('BOOL', 0),
			'report_closed'						=> array('BOOL', 0),
			'report_time'						=> array('TIMESTAMP', 0),
			'report_text'						=> array('MTEXT_UNI', ''),
			'reported_post_text'				=> array('MTEXT_UNI', ''),
			'reported_post_uid'					=> array('VCHAR:8', ''),
			'reported_post_bitfield'			=> array('VCHAR:255', ''),
			'reported_post_enable_magic_url'	=> array('BOOL', 1),
			'reported_post_enable_smilies'		=> array('BOOL', 1),
			'reported_post_enable_bbcode'		=> array('BOOL', 1)
		),
		'PRIMARY_KEY'	=> 'report_id',
		'KEYS'			=> array(
			'post_id'			=> array('INDEX', 'post_id'),
			'pm_id'				=> array('INDEX', 'pm_id'),
		),
	);

	$schema_data['phpbb_reports_reasons'] = array(
		'COLUMNS'		=> array(
			'reason_id'				=> array('USINT', NULL, 'auto_increment'),
			'reason_title'			=> array('VCHAR_UNI', ''),
			'reason_description'	=> array('MTEXT_UNI', ''),
			'reason_order'			=> array('USINT', 0),
		),
		'PRIMARY_KEY'	=> 'reason_id',
	);

	$schema_data['phpbb_search_results'] = array(
		'COLUMNS'		=> array(
			'search_key'			=> array('VCHAR:32', ''),
			'search_time'			=> array('TIMESTAMP', 0),
			'search_keywords'		=> array('MTEXT_UNI', ''),
			'search_authors'		=> array('MTEXT', ''),
		),
		'PRIMARY_KEY'	=> 'search_key',
	);

	$schema_data['phpbb_search_wordlist'] = array(
		'COLUMNS'		=> array(
			'word_id'			=> array('UINT', NULL, 'auto_increment'),
			'word_text'			=> array('VCHAR_UNI', ''),
			'word_common'		=> array('BOOL', 0),
			'word_count'		=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> 'word_id',
		'KEYS'			=> array(
			'wrd_txt'			=> array('UNIQUE', 'word_text'),
			'wrd_cnt'			=> array('INDEX', 'word_count'),
		),
	);

	$schema_data['phpbb_search_wordmatch'] = array(
		'COLUMNS'		=> array(
			'post_id'			=> array('UINT', 0),
			'word_id'			=> array('UINT', 0),
			'title_match'		=> array('BOOL', 0),
		),
		'KEYS'			=> array(
			'unq_mtch'			=> array('UNIQUE', array('word_id', 'post_id', 'title_match')),
			'word_id'			=> array('INDEX', 'word_id'),
			'post_id'			=> array('INDEX', 'post_id'),
		),
	);

	$schema_data['phpbb_sessions'] = array(
		'COLUMNS'		=> array(
			'session_id'			=> array('CHAR:32', ''),
			'session_user_id'		=> array('UINT', 0),
			'session_forum_id'		=> array('UINT', 0),
			'session_last_visit'	=> array('TIMESTAMP', 0),
			'session_start'			=> array('TIMESTAMP', 0),
			'session_time'			=> array('TIMESTAMP', 0),
			'session_ip'			=> array('VCHAR:40', ''),
			'session_browser'		=> array('VCHAR:150', ''),
			'session_forwarded_for'	=> array('VCHAR:255', ''),
			'session_page'			=> array('VCHAR_UNI', ''),
			'session_viewonline'	=> array('BOOL', 1),
			'session_autologin'		=> array('BOOL', 0),
			'session_admin'			=> array('BOOL', 0),
		),
		'PRIMARY_KEY'	=> 'session_id',
		'KEYS'			=> array(
			'session_time'		=> array('INDEX', 'session_time'),
			'session_user_id'	=> array('INDEX', 'session_user_id'),
			'session_fid'		=> array('INDEX', 'session_forum_id'),
		),
	);

	$schema_data['phpbb_sessions_keys'] = array(
		'COLUMNS'		=> array(
			'key_id'			=> array('CHAR:32', ''),
			'user_id'			=> array('UINT', 0),
			'last_ip'			=> array('VCHAR:40', ''),
			'last_login'		=> array('TIMESTAMP', 0),
		),
		'PRIMARY_KEY'	=> array('key_id', 'user_id'),
		'KEYS'			=> array(
			'last_login'		=> array('INDEX', 'last_login'),
		),
	);

	$schema_data['phpbb_sitelist'] = array(
		'COLUMNS'		=> array(
			'site_id'		=> array('UINT', NULL, 'auto_increment'),
			'site_ip'		=> array('VCHAR:40', ''),
			'site_hostname'	=> array('VCHAR', ''),
			'ip_exclude'	=> array('BOOL', 0),
		),
		'PRIMARY_KEY'		=> 'site_id',
	);

	$schema_data['phpbb_smilies'] = array(
		'COLUMNS'		=> array(
			'smiley_id'			=> array('UINT', NULL, 'auto_increment'),
			// We may want to set 'code' to VCHAR:50 or check if unicode support is possible... at the moment only ASCII characters are allowed.
			'code'				=> array('VCHAR_UNI:50', ''),
			'emotion'			=> array('VCHAR_UNI:50', ''),
			'smiley_url'		=> array('VCHAR:50', ''),
			'smiley_width'		=> array('USINT', 0),
			'smiley_height'		=> array('USINT', 0),
			'smiley_order'		=> array('UINT', 0),
			'display_on_posting'=> array('BOOL', 1),
		),
		'PRIMARY_KEY'	=> 'smiley_id',
		'KEYS'			=> array(
			'display_on_post'		=> array('INDEX', 'display_on_posting'),
		),
	);

	$schema_data['phpbb_styles'] = array(
		'COLUMNS'		=> array(
			'style_id'				=> array('UINT', NULL, 'auto_increment'),
			'style_name'			=> array('VCHAR_UNI:255', ''),
			'style_copyright'		=> array('VCHAR_UNI', ''),
			'style_active'			=> array('BOOL', 1),
			'style_path'			=> array('VCHAR:100', ''),
			'bbcode_bitfield'		=> array('VCHAR:255', 'kNg='),
			'style_parent_id'		=> array('UINT:4', 0),
			'style_parent_tree'		=> array('TEXT', ''),
		),
		'PRIMARY_KEY'	=> 'style_id',
		'KEYS'			=> array(
			'style_name'		=> array('UNIQUE', 'style_name'),
		),
	);

	$schema_data['phpbb_teampage'] = array(
		'COLUMNS'		=> array(
			'teampage_id'		=> array('UINT', NULL, 'auto_increment'),
			'group_id'			=> array('UINT', 0),
			'teampage_name'		=> array('VCHAR_UNI:255', ''),
			'teampage_position'	=> array('UINT', 0),
			'teampage_parent'	=> array('UINT', 0),
		),
		'PRIMARY_KEY'	=> 'teampage_id',
	);

	$schema_data['phpbb_topics'] = array(
		'COLUMNS'		=> array(
			'topic_id'					=> array('UINT', NULL, 'auto_increment'),
			'forum_id'					=> array('UINT', 0),
			'icon_id'					=> array('UINT', 0),
			'topic_attachment'			=> array('BOOL', 0),
			'topic_approved'			=> array('BOOL', 1),
			'topic_reported'			=> array('BOOL', 0),
			'topic_title'				=> array('STEXT_UNI', '', 'true_sort'),
			'topic_poster'				=> array('UINT', 0),
			'topic_time'				=> array('TIMESTAMP', 0),
			'topic_time_limit'			=> array('TIMESTAMP', 0),
			'topic_views'				=> array('UINT', 0),
			'topic_replies'				=> array('UINT', 0),
			'topic_replies_real'		=> array('UINT', 0),
			'topic_status'				=> array('TINT:3', 0),
			'topic_type'				=> array('TINT:3', 0),
			'topic_first_post_id'		=> array('UINT', 0),
			'topic_first_poster_name'	=> array('VCHAR_UNI', ''),
			'topic_first_poster_colour'	=> array('VCHAR:6', ''),
			'topic_last_post_id'		=> array('UINT', 0),
			'topic_last_poster_id'		=> array('UINT', 0),
			'topic_last_poster_name'	=> array('VCHAR_UNI', ''),
			'topic_last_poster_colour'	=> array('VCHAR:6', ''),
			'topic_last_post_subject'	=> array('STEXT_UNI', ''),
			'topic_last_post_time'		=> array('TIMESTAMP', 0),
			'topic_last_view_time'		=> array('TIMESTAMP', 0),
			'topic_moved_id'			=> array('UINT', 0),
			'topic_bumped'				=> array('BOOL', 0),
			'topic_bumper'				=> array('UINT', 0),
			'poll_title'				=> array('STEXT_UNI', ''),
			'poll_start'				=> array('TIMESTAMP', 0),
			'poll_length'				=> array('TIMESTAMP', 0),
			'poll_max_options'			=> array('TINT:4', 1),
			'poll_last_vote'			=> array('TIMESTAMP', 0),
			'poll_vote_change'			=> array('BOOL', 0),
		),
		'PRIMARY_KEY'	=> 'topic_id',
		'KEYS'			=> array(
			'forum_id'			=> array('INDEX', 'forum_id'),
			'forum_id_type'		=> array('INDEX', array('forum_id', 'topic_type')),
			'last_post_time'	=> array('INDEX', 'topic_last_post_time'),
			'topic_approved'	=> array('INDEX', 'topic_approved'),
			'forum_appr_last'	=> array('INDEX', array('forum_id', 'topic_approved', 'topic_last_post_id')),
			'fid_time_moved'	=> array('INDEX', array('forum_id', 'topic_last_post_time', 'topic_moved_id')),
		),
	);

	$schema_data['phpbb_topics_track'] = array(
		'COLUMNS'		=> array(
			'user_id'			=> array('UINT', 0),
			'topic_id'			=> array('UINT', 0),
			'forum_id'			=> array('UINT', 0),
			'mark_time'			=> array('TIMESTAMP', 0),
		),
		'PRIMARY_KEY'	=> array('user_id', 'topic_id'),
		'KEYS'			=> array(
			'topic_id'			=> array('INDEX', 'topic_id'),
			'forum_id'			=> array('INDEX', 'forum_id'),
		),
	);

	$schema_data['phpbb_topics_posted'] = array(
		'COLUMNS'		=> array(
			'user_id'			=> array('UINT', 0),
			'topic_id'			=> array('UINT', 0),
			'topic_posted'		=> array('BOOL', 0),
		),
		'PRIMARY_KEY'	=> array('user_id', 'topic_id'),
	);

	$schema_data['phpbb_topics_watch'] = array(
		'COLUMNS'		=> array(
			'topic_id'			=> array('UINT', 0),
			'user_id'			=> array('UINT', 0),
			'notify_status'		=> array('BOOL', 0),
		),
		'KEYS'			=> array(
			'topic_id'			=> array('INDEX', 'topic_id'),
			'user_id'			=> array('INDEX', 'user_id'),
			'notify_stat'		=> array('INDEX', 'notify_status'),
		),
	);

	$schema_data['phpbb_user_notifications'] = array(
		'COLUMNS'		=> array(
			'item_type'			=> array('VCHAR:255', ''),
			'item_id'			=> array('UINT', 0),
			'user_id'			=> array('UINT', 0),
			'method'			=> array('VCHAR:255', ''),
			'notify'			=> array('BOOL', 1),
		),
	);

	$schema_data['phpbb_user_group'] = array(
		'COLUMNS'		=> array(
			'group_id'			=> array('UINT', 0),
			'user_id'			=> array('UINT', 0),
			'group_leader'		=> array('BOOL', 0),
			'user_pending'		=> array('BOOL', 1),
		),
		'KEYS'			=> array(
			'group_id'			=> array('INDEX', 'group_id'),
			'user_id'			=> array('INDEX', 'user_id'),
			'group_leader'		=> array('INDEX', 'group_leader'),
		),
	);

	$schema_data['phpbb_users'] = array(
		'COLUMNS'		=> array(
			'user_id'					=> array('UINT', NULL, 'auto_increment'),
			'user_type'					=> array('TINT:2', 0),
			'group_id'					=> array('UINT', 3),
			'user_permissions'			=> array('MTEXT', ''),
			'user_perm_from'			=> array('UINT', 0),
			'user_ip'					=> array('VCHAR:40', ''),
			'user_regdate'				=> array('TIMESTAMP', 0),
			'username'					=> array('VCHAR_CI', ''),
			'username_clean'			=> array('VCHAR_CI', ''),
			'user_password'				=> array('VCHAR_UNI:40', ''),
			'user_passchg'				=> array('TIMESTAMP', 0),
			'user_pass_convert'			=> array('BOOL', 0),
			'user_email'				=> array('VCHAR_UNI:100', ''),
			'user_email_hash'			=> array('BINT', 0),
			'user_birthday'				=> array('VCHAR:10', ''),
			'user_lastvisit'			=> array('TIMESTAMP', 0),
			'user_lastmark'				=> array('TIMESTAMP', 0),
			'user_lastpost_time'		=> array('TIMESTAMP', 0),
			'user_lastpage'				=> array('VCHAR_UNI:200', ''),
			'user_last_confirm_key'		=> array('VCHAR:10', ''),
			'user_last_search'			=> array('TIMESTAMP', 0),
			'user_warnings'				=> array('TINT:4', 0),
			'user_last_warning'			=> array('TIMESTAMP', 0),
			'user_login_attempts'		=> array('TINT:4', 0),
			'user_inactive_reason'		=> array('TINT:2', 0),
			'user_inactive_time'		=> array('TIMESTAMP', 0),
			'user_posts'				=> array('UINT', 0),
			'user_lang'					=> array('VCHAR:30', ''),
			'user_timezone'				=> array('VCHAR:100', 'UTC'),
			'user_dateformat'			=> array('VCHAR_UNI:30', 'd M Y H:i'),
			'user_style'				=> array('UINT', 0),
			'user_rank'					=> array('UINT', 0),
			'user_colour'				=> array('VCHAR:6', ''),
			'user_new_privmsg'			=> array('INT:4', 0),
			'user_unread_privmsg'		=> array('INT:4', 0),
			'user_last_privmsg'			=> array('TIMESTAMP', 0),
			'user_message_rules'		=> array('BOOL', 0),
			'user_full_folder'			=> array('INT:11', -3),
			'user_emailtime'			=> array('TIMESTAMP', 0),
			'user_topic_show_days'		=> array('USINT', 0),
			'user_topic_sortby_type'	=> array('VCHAR:1', 't'),
			'user_topic_sortby_dir'		=> array('VCHAR:1', 'd'),
			'user_post_show_days'		=> array('USINT', 0),
			'user_post_sortby_type'		=> array('VCHAR:1', 't'),
			'user_post_sortby_dir'		=> array('VCHAR:1', 'a'),
			'user_notify'				=> array('BOOL', 0),
			'user_notify_pm'			=> array('BOOL', 1),
			'user_notify_type'			=> array('TINT:4', 0),
			'user_allow_pm'				=> array('BOOL', 1),
			'user_allow_viewonline'		=> array('BOOL', 1),
			'user_allow_viewemail'		=> array('BOOL', 1),
			'user_allow_massemail'		=> array('BOOL', 1),
			'user_options'				=> array('UINT:11', 230271),
			'user_avatar'				=> array('VCHAR', ''),
			'user_avatar_type'			=> array('VCHAR:255', ''),
			'user_avatar_width'			=> array('USINT', 0),
			'user_avatar_height'		=> array('USINT', 0),
			'user_sig'					=> array('MTEXT_UNI', ''),
			'user_sig_bbcode_uid'		=> array('VCHAR:8', ''),
			'user_sig_bbcode_bitfield'	=> array('VCHAR:255', ''),
			'user_from'					=> array('VCHAR_UNI:100', ''),
			'user_icq'					=> array('VCHAR:15', ''),
			'user_aim'					=> array('VCHAR_UNI', ''),
			'user_yim'					=> array('VCHAR_UNI', ''),
			'user_msnm'					=> array('VCHAR_UNI', ''),
			'user_jabber'				=> array('VCHAR_UNI', ''),
			'user_website'				=> array('VCHAR_UNI:200', ''),
			'user_occ'					=> array('TEXT_UNI', ''),
			'user_interests'			=> array('TEXT_UNI', ''),
			'user_actkey'				=> array('VCHAR:32', ''),
			'user_newpasswd'			=> array('VCHAR_UNI:40', ''),
			'user_form_salt'			=> array('VCHAR_UNI:32', ''),
			'user_new'					=> array('BOOL', 1),
			'user_reminded'				=> array('TINT:4', 0),
			'user_reminded_time'		=> array('TIMESTAMP', 0),
		),
		'PRIMARY_KEY'	=> 'user_id',
		'KEYS'			=> array(
			'user_birthday'				=> array('INDEX', 'user_birthday'),
			'user_email_hash'			=> array('INDEX', 'user_email_hash'),
			'user_type'					=> array('INDEX', 'user_type'),
			'username_clean'			=> array('UNIQUE', 'username_clean'),
		),
	);

	$schema_data['phpbb_warnings'] = array(
		'COLUMNS'		=> array(
			'warning_id'			=> array('UINT', NULL, 'auto_increment'),
			'user_id'				=> array('UINT', 0),
			'post_id'				=> array('UINT', 0),
			'log_id'				=> array('UINT', 0),
			'warning_time'			=> array('TIMESTAMP', 0),
		),
		'PRIMARY_KEY'	=> 'warning_id',
	);

	$schema_data['phpbb_words'] = array(
		'COLUMNS'		=> array(
			'word_id'				=> array('UINT', NULL, 'auto_increment'),
			'word'					=> array('VCHAR_UNI', ''),
			'replacement'			=> array('VCHAR_UNI', ''),
		),
		'PRIMARY_KEY'	=> 'word_id',
	);

	$schema_data['phpbb_zebra'] = array(
		'COLUMNS'		=> array(
			'user_id'				=> array('UINT', 0),
			'zebra_id'				=> array('UINT', 0),
			'friend'				=> array('BOOL', 0),
			'foe'					=> array('BOOL', 0),
		),
		'PRIMARY_KEY'	=> array('user_id', 'zebra_id'),
	);

	return $schema_data;
}


/**
* Data put into the header for various dbms
*/
function custom_data($dbms)
{
	switch ($dbms)
	{
		case 'oracle':
			return <<<EOF
/*
  This first section is optional, however its probably the best method
  of running phpBB on Oracle. If you already have a tablespace and user created
  for phpBB you can leave this section commented out!

  The first set of statements create a phpBB tablespace and a phpBB user,
  make sure you change the password of the phpBB user before you run this script!!
*/

/*
CREATE TABLESPACE "PHPBB"
	LOGGING
	DATAFILE 'E:\ORACLE\ORADATA\LOCAL\PHPBB.ora'
	SIZE 10M
	AUTOEXTEND ON NEXT 10M
	MAXSIZE 100M;

CREATE USER "PHPBB"
	PROFILE "DEFAULT"
	IDENTIFIED BY "phpbb_password"
	DEFAULT TABLESPACE "PHPBB"
	QUOTA UNLIMITED ON "PHPBB"
	ACCOUNT UNLOCK;

GRANT ANALYZE ANY TO "PHPBB";
GRANT CREATE SEQUENCE TO "PHPBB";
GRANT CREATE SESSION TO "PHPBB";
GRANT CREATE TABLE TO "PHPBB";
GRANT CREATE TRIGGER TO "PHPBB";
GRANT CREATE VIEW TO "PHPBB";
GRANT "CONNECT" TO "PHPBB";

COMMIT;
DISCONNECT;

CONNECT phpbb/phpbb_password;
*/
EOF;

		break;

		case 'postgres':
			return <<<EOF
/*
	Domain definition
*/
CREATE DOMAIN varchar_ci AS varchar(255) NOT NULL DEFAULT ''::character varying;

/*
	Operation Functions
*/
CREATE FUNCTION _varchar_ci_equal(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) = LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_not_equal(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) != LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_less_than(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) < LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_less_equal(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) <= LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_greater_than(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) > LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_greater_equals(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) >= LOWER($2)' LANGUAGE SQL STRICT;

/*
	Operators
*/
CREATE OPERATOR <(
  PROCEDURE = _varchar_ci_less_than,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = >,
  NEGATOR = >=,
  RESTRICT = scalarltsel,
  JOIN = scalarltjoinsel);

CREATE OPERATOR <=(
  PROCEDURE = _varchar_ci_less_equal,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = >=,
  NEGATOR = >,
  RESTRICT = scalarltsel,
  JOIN = scalarltjoinsel);

CREATE OPERATOR >(
  PROCEDURE = _varchar_ci_greater_than,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = <,
  NEGATOR = <=,
  RESTRICT = scalargtsel,
  JOIN = scalargtjoinsel);

CREATE OPERATOR >=(
  PROCEDURE = _varchar_ci_greater_equals,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = <=,
  NEGATOR = <,
  RESTRICT = scalargtsel,
  JOIN = scalargtjoinsel);

CREATE OPERATOR <>(
  PROCEDURE = _varchar_ci_not_equal,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = <>,
  NEGATOR = =,
  RESTRICT = neqsel,
  JOIN = neqjoinsel);

CREATE OPERATOR =(
  PROCEDURE = _varchar_ci_equal,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = =,
  NEGATOR = <>,
  RESTRICT = eqsel,
  JOIN = eqjoinsel,
  HASHES,
  MERGES,
  SORT1= <);

EOF;
		break;
	}

	return '';
}

echo 'done';
