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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Returns an array of available DBMS with some data, if a DBMS is specified it will only
* return data for that DBMS and will load its extension if necessary.
*/
function get_available_dbms($dbms = false, $return_unavailable = false, $only_20x_options = false)
{
	global $lang;
	$available_dbms = array(
		// Note: php 5.5 alpha 2 deprecated mysql.
		// Keep mysqli before mysql in this list.
		'mysqli'	=> array(
			'LABEL'			=> 'MySQL with MySQLi Extension',
			'SCHEMA'		=> 'mysql_41',
			'MODULE'		=> 'mysqli',
			'DELIM'			=> ';',
			'DRIVER'		=> 'phpbb\db\driver\mysqli',
			'AVAILABLE'		=> true,
			'2.0.x'			=> true,
		),
		'mysql'		=> array(
			'LABEL'			=> 'MySQL',
			'SCHEMA'		=> 'mysql',
			'MODULE'		=> 'mysql',
			'DELIM'			=> ';',
			'DRIVER'		=> 'phpbb\db\driver\mysql',
			'AVAILABLE'		=> true,
			'2.0.x'			=> true,
		),
		'mssql'		=> array(
			'LABEL'			=> 'MS SQL Server 2000+',
			'SCHEMA'		=> 'mssql',
			'MODULE'		=> 'mssql',
			'DELIM'			=> 'GO',
			'DRIVER'		=> 'phpbb\db\driver\mssql',
			'AVAILABLE'		=> true,
			'2.0.x'			=> true,
		),
		'mssql_odbc'=>	array(
			'LABEL'			=> 'MS SQL Server [ ODBC ]',
			'SCHEMA'		=> 'mssql',
			'MODULE'		=> 'odbc',
			'DELIM'			=> 'GO',
			'DRIVER'		=> 'phpbb\db\driver\mssql_odbc',
			'AVAILABLE'		=> true,
			'2.0.x'			=> true,
		),
		'mssqlnative'		=> array(
			'LABEL'			=> 'MS SQL Server 2005+ [ Native ]',
			'SCHEMA'		=> 'mssql',
			'MODULE'		=> 'sqlsrv',
			'DELIM'			=> 'GO',
			'DRIVER'		=> 'phpbb\db\driver\mssqlnative',
			'AVAILABLE'		=> true,
			'2.0.x'			=> false,
		),
		'oracle'	=>	array(
			'LABEL'			=> 'Oracle',
			'SCHEMA'		=> 'oracle',
			'MODULE'		=> 'oci8',
			'DELIM'			=> '/',
			'DRIVER'		=> 'phpbb\db\driver\oracle',
			'AVAILABLE'		=> true,
			'2.0.x'			=> false,
		),
		'postgres' => array(
			'LABEL'			=> 'PostgreSQL 8.3+',
			'SCHEMA'		=> 'postgres',
			'MODULE'		=> 'pgsql',
			'DELIM'			=> ';',
			'DRIVER'		=> 'phpbb\db\driver\postgres',
			'AVAILABLE'		=> true,
			'2.0.x'			=> true,
		),
		'sqlite'		=> array(
			'LABEL'			=> 'SQLite',
			'SCHEMA'		=> 'sqlite',
			'MODULE'		=> 'sqlite',
			'DELIM'			=> ';',
			'DRIVER'		=> 'phpbb\db\driver\sqlite',
			'AVAILABLE'		=> true,
			'2.0.x'			=> false,
		),
		'sqlite3'		=> array(
			'LABEL'			=> 'SQLite3',
			'SCHEMA'		=> 'sqlite',
			'MODULE'		=> 'sqlite3',
			'DELIM'			=> ';',
			'DRIVER'		=> 'phpbb\db\driver\sqlite3',
			'AVAILABLE'		=> true,
			'2.0.x'			=> false,
		),
	);

	if ($dbms)
	{
		if (isset($available_dbms[$dbms]))
		{
			$available_dbms = array($dbms => $available_dbms[$dbms]);
		}
		else
		{
			return array();
		}
	}

	// now perform some checks whether they are really available
	foreach ($available_dbms as $db_name => $db_ary)
	{
		if ($only_20x_options && !$db_ary['2.0.x'])
		{
			if ($return_unavailable)
			{
				$available_dbms[$db_name]['AVAILABLE'] = false;
			}
			else
			{
				unset($available_dbms[$db_name]);
			}
			continue;
		}

		$dll = $db_ary['MODULE'];

		if (!@extension_loaded($dll))
		{
			if ($return_unavailable)
			{
				$available_dbms[$db_name]['AVAILABLE'] = false;
			}
			else
			{
				unset($available_dbms[$db_name]);
			}
			continue;
		}
		$any_db_support = true;
	}

	if ($return_unavailable)
	{
		$available_dbms['ANY_DB_SUPPORT'] = $any_db_support;
	}
	return $available_dbms;
}

/**
* Generate the drop down of available database options
*/
function dbms_select($default = '', $only_20x_options = false)
{
	global $lang;

	$available_dbms = get_available_dbms(false, false, $only_20x_options);
	$dbms_options = '';
	foreach ($available_dbms as $dbms_name => $details)
	{
		$selected = ($dbms_name == $default) ? ' selected="selected"' : '';
		$dbms_options .= '<option value="' . $dbms_name . '"' . $selected .'>' . $lang['DLL_' . strtoupper($dbms_name)] . '</option>';
	}
	return $dbms_options;
}

/**
* Get tables of a database
*
* @deprecated
*/
function get_tables(&$db)
{
	$db_tools = new \phpbb\db\tools($db);

	return $db_tools->sql_list_tables();
}

/**
* Used to test whether we are able to connect to the database the user has specified
* and identify any problems (eg there are already tables with the names we want to use
* @param	array	$dbms should be of the format of an element of the array returned by {@link get_available_dbms get_available_dbms()}
*					necessary extensions should be loaded already
*/
function connect_check_db($error_connect, &$error, $dbms_details, $table_prefix, $dbhost, $dbuser, $dbpasswd, $dbname, $dbport, $prefix_may_exist = false, $load_dbal = true, $unicode_check = true)
{
	global $phpbb_root_path, $phpEx, $config, $lang;

	$dbms = $dbms_details['DRIVER'];

	// Instantiate it and set return on error true
	$db = new $dbms();
	$db->sql_return_on_error(true);

	// Check that we actually have a database name before going any further.....
	if ($dbms_details['DRIVER'] != 'phpbb\db\driver\sqlite' && $dbms_details['DRIVER'] != 'phpbb\db\driver\sqlite3' && $dbms_details['DRIVER'] != 'phpbb\db\driver\oracle' && $dbname === '')
	{
		$error[] = $lang['INST_ERR_DB_NO_NAME'];
		return false;
	}

	// Make sure we don't have a daft user who thinks having the SQLite database in the forum directory is a good idea
	if (($dbms_details['DRIVER'] == 'phpbb\db\driver\sqlite' || $dbms_details['DRIVER'] == 'phpbb\db\driver\sqlite3') && stripos(phpbb_realpath($dbhost), phpbb_realpath('../')) === 0)
	{
		$error[] = $lang['INST_ERR_DB_FORUM_PATH'];
		return false;
	}

	// Check the prefix length to ensure that index names are not too long and does not contain invalid characters
	switch ($dbms_details['DRIVER'])
	{
		case 'phpbb\db\driver\mysql':
		case 'phpbb\db\driver\mysqli':
			if (strspn($table_prefix, '-./\\') !== 0)
			{
				$error[] = $lang['INST_ERR_PREFIX_INVALID'];
				return false;
			}

		// no break;

		case 'phpbb\db\driver\postgres':
			$prefix_length = 36;
		break;

		case 'phpbb\db\driver\mssql':
		case 'phpbb\db\driver\mssql_odbc':
		case 'phpbb\db\driver\mssqlnative':
			$prefix_length = 90;
		break;

		case 'phpbb\db\driver\sqlite':
		case 'phpbb\db\driver\sqlite3':
			$prefix_length = 200;
		break;

		case 'phpbb\db\driver\oracle':
			$prefix_length = 6;
		break;
	}

	if (strlen($table_prefix) > $prefix_length)
	{
		$error[] = sprintf($lang['INST_ERR_PREFIX_TOO_LONG'], $prefix_length);
		return false;
	}

	// Try and connect ...
	if (is_array($db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, true)))
	{
		$db_error = $db->sql_error();
		$error[] = $lang['INST_ERR_DB_CONNECT'] . '<br />' . (($db_error['message']) ? utf8_convert_message($db_error['message']) : $lang['INST_ERR_DB_NO_ERROR']);
	}
	else
	{
		// Likely matches for an existing phpBB installation
		if (!$prefix_may_exist)
		{
			$temp_prefix = strtolower($table_prefix);
			$table_ary = array($temp_prefix . 'attachments', $temp_prefix . 'config', $temp_prefix . 'sessions', $temp_prefix . 'topics', $temp_prefix . 'users');

			$tables = get_tables($db);
			$tables = array_map('strtolower', $tables);
			$table_intersect = array_intersect($tables, $table_ary);

			if (sizeof($table_intersect))
			{
				$error[] = $lang['INST_ERR_PREFIX'];
			}
		}

		// Make sure that the user has selected a sensible DBAL for the DBMS actually installed
		switch ($dbms_details['DRIVER'])
		{
			case 'phpbb\db\driver\mysqli':
				if (version_compare(mysqli_get_server_info($db->get_db_connect_id()), '4.1.3', '<'))
				{
					$error[] = $lang['INST_ERR_DB_NO_MYSQLI'];
				}
			break;

			case 'phpbb\db\driver\sqlite':
				if (version_compare(sqlite_libversion(), '2.8.2', '<'))
				{
					$error[] = $lang['INST_ERR_DB_NO_SQLITE'];
				}
			break;

			case 'phpbb\db\driver\sqlite3':
				$version = \SQLite3::version();
				if (version_compare($version['versionString'], '3.6.15', '<'))
				{
					$error[] = $lang['INST_ERR_DB_NO_SQLITE3'];
				}
			break;

			case 'phpbb\db\driver\oracle':
				if ($unicode_check)
				{
					$sql = "SELECT *
						FROM NLS_DATABASE_PARAMETERS
						WHERE PARAMETER = 'NLS_RDBMS_VERSION'
							OR PARAMETER = 'NLS_CHARACTERSET'";
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$stats[$row['parameter']] = $row['value'];
					}
					$db->sql_freeresult($result);

					if (version_compare($stats['NLS_RDBMS_VERSION'], '9.2', '<') && $stats['NLS_CHARACTERSET'] !== 'UTF8')
					{
						$error[] = $lang['INST_ERR_DB_NO_ORACLE'];
					}
				}
			break;

			case 'phpbb\db\driver\postgres':
				if ($unicode_check)
				{
					$sql = "SHOW server_encoding;";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if ($row['server_encoding'] !== 'UNICODE' && $row['server_encoding'] !== 'UTF8')
					{
						$error[] = $lang['INST_ERR_DB_NO_POSTGRES'];
					}
				}
			break;
		}

	}

	if ($error_connect && (!isset($error) || !sizeof($error)))
	{
		return true;
	}
	return false;
}

/**
* Removes "/* style" as well as "# style" comments from $input.
*
* @param string $input		Input string
*
* @return string			Input string with comments removed
*/
function phpbb_remove_comments($input)
{
	// Remove /* */ comments (http://ostermiller.org/findcomment.html)
	$input = preg_replace('#/\*(.|[\r\n])*?\*/#', "\n", $input);

	// Remove # style comments
	$input = preg_replace('/\n{2,}/', "\n", preg_replace('/^#.*$/m', "\n", $input));

	return $input;
}

/**
* split_sql_file will split an uploaded sql file into single sql statements.
* Note: expects trim() to have already been run on $sql.
*/
function split_sql_file($sql, $delimiter)
{
	$sql = str_replace("\r" , '', $sql);
	$data = preg_split('/' . preg_quote($delimiter, '/') . '$/m', $sql);

	$data = array_map('trim', $data);

	// The empty case
	$end_data = end($data);

	if (empty($end_data))
	{
		unset($data[key($data)]);
	}

	return $data;
}

/**
* For replacing {L_*} strings with preg_replace_callback
*/
function adjust_language_keys_callback($matches)
{
	if (!empty($matches[1]))
	{
		global $lang, $db;

		return (!empty($lang[$matches[1]])) ? $db->sql_escape($lang[$matches[1]]) : $db->sql_escape($matches[1]);
	}
}

/**
* Creates the output to be stored in a phpBB config.php file
*
* @param	array	$data Array containing the database connection information
* @param	string	$dbms The name of the DBAL class to use
* @param	bool	$debug If the debug constants should be enabled by default or not
* @param	bool	$debug_container If the container should be compiled on
*					every page load or not
* @param	bool	$debug_test If the DEBUG_TEST constant should be added
*					NOTE: Only for use within the testing framework
*
* @return	string	The output to write to the file
*/
function phpbb_create_config_file_data($data, $dbms, $debug = false, $debug_container = false, $debug_test = false)
{
	$config_data = "<?php\n";
	$config_data .= "// phpBB 3.1.x auto-generated configuration file\n// Do not change anything in this file!\n";

	$config_data_array = array(
		'dbms'			=> $dbms,
		'dbhost'		=> $data['dbhost'],
		'dbport'		=> $data['dbport'],
		'dbname'		=> $data['dbname'],
		'dbuser'		=> $data['dbuser'],
		'dbpasswd'		=> htmlspecialchars_decode($data['dbpasswd']),
		'table_prefix'	=> $data['table_prefix'],

		'phpbb_adm_relative_path'	=> 'adm/',

		'acm_type'		=> 'phpbb\cache\driver\file',
	);

	foreach ($config_data_array as $key => $value)
	{
		$config_data .= "\${$key} = '" . str_replace("'", "\\'", str_replace('\\', '\\\\', $value)) . "';\n";
	}

	$config_data .= "\n@define('PHPBB_INSTALLED', true);\n";
	$config_data .= "// @define('PHPBB_DISPLAY_LOAD_TIME', true);\n";

	if ($debug)
	{
		$config_data .= "@define('DEBUG', true);\n";
	}
	else
	{
		$config_data .= "// @define('DEBUG', true);\n";
	}

	if ($debug_container)
	{
		$config_data .= "@define('DEBUG_CONTAINER', true);\n";
	}
	else
	{
		$config_data .= "// @define('DEBUG_CONTAINER', true);\n";
	}

	if ($debug_test)
	{
		$config_data .= "@define('DEBUG_TEST', true);\n";
	}

	return $config_data;
}

/**
* Check whether a file should be ignored on update
*
* We ignore new files in some circumstances:
* 1. The file is a language file, but the language is not installed
* 2. The file is a style file, but the style is not installed
* 3. The file is a style language file, but the language is not installed
*
* @param	string	$phpbb_root_path	phpBB root path
* @param	string	$file				File including path from phpbb root
* @return	bool		Should we ignore the new file or add it to the board?
*/
function phpbb_ignore_new_file_on_update($phpbb_root_path, $file)
{
	$ignore_new_file = false;

	// We ignore new files in some circumstances:
	// 1. The file is a language file, but the language is not installed
	if (!$ignore_new_file && strpos($file, 'language/') === 0)
	{
		list($language_dir, $language_iso) = explode('/', $file);
		$ignore_new_file = !file_exists($phpbb_root_path . $language_dir . '/' . $language_iso);
	}

	// 2. The file is a style file, but the style is not installed
	if (!$ignore_new_file && strpos($file, 'styles/') === 0)
	{
		list($styles_dir, $style_name) = explode('/', $file);
		$ignore_new_file = !file_exists($phpbb_root_path . $styles_dir . '/' . $style_name);
	}

	// 3. The file is a style language file, but the language is not installed
	if (!$ignore_new_file && strpos($file, 'styles/') === 0)
	{
		$dirs = explode('/', $file);
		if (sizeof($dirs) >= 5)
		{
			list($styles_dir, $style_name, $template_component, $language_iso) = explode('/', $file);
			if ($template_component == 'theme' && $language_iso !== 'images')
			{
				$ignore_new_file = !file_exists($phpbb_root_path . 'language/' . $language_iso);
			}
		}
	}

	return $ignore_new_file;
}

/**
* Check whether phpBB is installed.
*
* @param string $phpbb_root_path	Path to the phpBB board root.
* @param string $php_ext			PHP file extension.
*
* @return bool Returns true if phpBB is installed.
*/
function phpbb_check_installation_exists($phpbb_root_path, $php_ext)
{
	// Try opening config file
	if (file_exists($phpbb_root_path . 'config.' . $php_ext))
	{
		include($phpbb_root_path . 'config.' . $php_ext);
	}

	return defined('PHPBB_INSTALLED');
}
