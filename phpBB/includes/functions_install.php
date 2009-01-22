<?php
/**
*
* @package install
* @version $Id$
* @copyright (c) 2006 phpBB Group
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
* Determine if we are able to load a specified PHP module and do so if possible
*
* @param string	$dll	Name of the DLL without extension. For example 'sqlite'.
* @return bool	Returns true of successfully loaded, else false.
*/
function can_load_dll($dll)
{
	return ((@ini_get('enable_dl') || strtolower(@ini_get('enable_dl')) == 'on') && (!@ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'off') && @dl($dll . '.' . PHP_SHLIB_SUFFIX)) ? true : false;
}

/**
* Returns an array of available DBMS with some data, if a DBMS is specified it will only return data for that DBMS and will load its extension if necessary.
*
*/
function get_available_dbms($dbms = false, $return_unavailable = false, $only_30x_options = false)
{
	$available_dbms = array(
		'firebird'	=> array(
			'LABEL'			=> 'FireBird',
			'MODULE'		=> 'interbase',
			'DRIVER'		=> 'firebird',
			'AVAILABLE'		=> true,
			'3.0.x'			=> true,
		),
		'mysqli'	=> array(
			'LABEL'			=> 'MySQL with MySQLi Extension',
			'MODULE'		=> 'mysqli',
			'DRIVER'		=> 'mysqli',
			'AVAILABLE'		=> true,
			'3.0.x'			=> true,
		),
		'mysql'		=> array(
			'LABEL'			=> 'MySQL',
			'MODULE'		=> 'mysql',
			'DRIVER'		=> 'mysql',
			'AVAILABLE'		=> true,
			'3.0.x'			=> true,
		),
		'mssql'		=> array(
			'LABEL'			=> 'MS SQL Server 2000+',
			'MODULE'		=> 'mssql',
			'DRIVER'		=> 'mssql',
			'AVAILABLE'		=> true,
			'3.0.x'			=> true,
		),
		'mssql_odbc'=>	array(
			'LABEL'			=> 'MS SQL Server [ ODBC ]',
			'MODULE'		=> 'odbc',
			'DRIVER'		=> 'mssql_odbc',
			'AVAILABLE'		=> true,
			'3.0.x'			=> true,
		),
		'mssql_2005'=>	array(
			'LABEL'			=> 'MS SQL Server [ 2005/2008 ]',
			'MODULE'		=> array('sqlsrv', 'sqlsrv_ts'),
			'DRIVER'		=> 'mssql_2005',
			'AVAILABLE'		=> true,
			'3.0.x'			=> true,
		),
		'db2'		=> array(
			'LABEL'			=> 'IBM DB2',
			'MODULE'		=> 'ibm_db2',
			'DRIVER'		=> 'db2',
			'AVAILABLE'		=> true,
			'3.0.x'			=> false,
		),
		'oracle'	=>	array(
			'LABEL'			=> 'Oracle',
			'MODULE'		=> 'oci8',
			'DRIVER'		=> 'oracle',
			'AVAILABLE'		=> true,
			'3.0.x'			=> true,
		),
		'postgres' => array(
			'LABEL'			=> 'PostgreSQL 7.x/8.x',
			'MODULE'		=> 'pgsql',
			'DRIVER'		=> 'postgres',
			'AVAILABLE'		=> true,
			'3.0.x'			=> true,
		),
		'sqlite'		=> array(
			'LABEL'			=> 'SQLite',
			'MODULE'		=> 'sqlite',
			'DRIVER'		=> 'sqlite',
			'AVAILABLE'		=> true,
			'3.0.x'			=> true,
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

	$any_db_support = false;

	// now perform some checks whether they are really available
	foreach ($available_dbms as $db_name => $db_ary)
	{
		if ($only_30x_options && !$db_ary['3.0.x'])
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

		if (!is_array($dll))
		{
			$dll = array($dll);
		}

		$is_available = false;
		foreach ($dll as $test_dll)
		{
			if (@extension_loaded($test_dll) || can_load_dll($test_dll))
			{
				$is_available = true;
				break;
			}
		}

		if (!$is_available)
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
function dbms_select($default = '', $only_30x_options = false)
{
	$available_dbms = get_available_dbms(false, false, $only_30x_options);

	$dbms_options = '';
	foreach ($available_dbms as $dbms_name => $details)
	{
		$selected = ($dbms_name == $default) ? ' selected="selected"' : '';
		$dbms_options .= '<option value="' . $dbms_name . '"' . $selected .'>' . phpbb::$user->lang['DLL_' . strtoupper($dbms_name)] . '</option>';
	}

	return $dbms_options;
}

/**
* Get tables of a database
*/
function get_tables($db)
{
	switch ($db->dbms_type)
	{
		case 'mysql':
			$sql = 'SHOW TABLES';
		break;

		case 'sqlite':
			$sql = 'SELECT name
				FROM sqlite_master
				WHERE type = "table"';
		break;

		case 'mssql':
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

		case 'db2':
			$sql = "SELECT tabname
				FROM SYSCAT.TABLES
				WHERE type = 'T'
					AND tabschema = 'DB2ADMIN'";
			$field = 'tabname';
		break;

		case 'oracle':
			$sql = 'SELECT table_name
				FROM USER_TABLES';
		break;
	}

	$result = $db->sql_query($sql);

	$tables = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$tables[] = current($row);
	}
	$db->sql_freeresult($result);

	return $tables;
}

/**
* Used to test whether we are able to connect to the database the user has specified
* and identify any problems (eg there are already tables with the names we want to use
* @param	array	$dbms should be of the format of an element of the array returned by {@link get_available_dbms get_available_dbms()}
*					necessary extensions should be loaded already
*/
function connect_check_db($dbms_details, $table_prefix, $dbhost, $dbuser, $dbpasswd, $dbname, $dbport, &$error, $prefix_may_exist = false)
{
	$dbms = $dbms_details['DRIVER'];

	phpbb::assign('checkdb', phpbb_db_dbal::new_instance($dbms));
	$db = phpbb::get_instance('checkdb');

	$db->sql_return_on_error(true);

	// Check that we actually have a database name before going any further.....
	if ($dbms_details['DRIVER'] != 'sqlite' && $dbms_details['DRIVER'] != 'oracle' && $dbname === '')
	{
		$error[] = phpbb::$user->lang['INST_ERR_DB_NO_NAME'];
		return false;
	}

	// Make sure we don't have a daft user who thinks having the SQLite database in the forum directory is a good idea
	if ($dbms_details['DRIVER'] == 'sqlite' && stripos(phpbb::$url->realpath($dbhost), phpbb::$url->realpath('../')) === 0)
	{
		$error[] = phpbb::$user->lang['INST_ERR_DB_FORUM_PATH'];
		return false;
	}

	// Check the prefix length to ensure that index names are not too long and does not contain invalid characters
	switch ($dbms_details['DRIVER'])
	{
		case 'mysql':
		case 'mysqli':
			if (strspn($table_prefix, '-./\\') !== 0)
			{
				$error[] = phpbb::$user->lang['INST_ERR_PREFIX_INVALID'];
				return false;
			}

		// no break;

		case 'postgres':
			$prefix_length = 36;
		break;

		case 'mssql':
		case 'mssql_odbc':
		case 'mssql_2005':
			$prefix_length = 90;
		break;

		case 'db2':
			$prefix_length = 108;
		break;

		case 'sqlite':
			$prefix_length = 200;
		break;

		case 'firebird':
		case 'oracle':
			$prefix_length = 6;
		break;
	}

	if (strlen($table_prefix) > $prefix_length)
	{
		$error[] = phpbb::$user->lang('INST_ERR_PREFIX_TOO_LONG', $prefix_length);
		return false;
	}

	// Try and connect ...
	if (is_array($db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, true)))
	{
		$db_error = $db->sql_error();
		$error[] = phpbb::$user->lang['INST_ERR_DB_CONNECT'] . '<br />' . (($db_error['message']) ? $db_error['message'] : phpbb::$user->lang['INST_ERR_DB_NO_ERROR']);
		return false;
	}

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
			$error[] = phpbb::$user->lang['INST_ERR_PREFIX'];
			return false;
		}
	}

	// Make sure that the user has selected a sensible DBAL for the DBMS actually installed
	switch ($dbms_details['DRIVER'])
	{
		case 'mysql':
			if (version_compare($db->sql_server_info(true), '4.1.3', '<'))
			{
				$error[] = phpbb::$user->lang['INST_ERR_DB_MYSQL_VERSION'];
			}
		break;

		case 'mysqli':
			if (version_compare($db->sql_server_info(true), '4.1.3', '<'))
			{
				$error[] = phpbb::$user->lang['INST_ERR_DB_MYSQLI_VERSION'];
			}
		break;

		case 'sqlite':
			if (version_compare($db->sql_server_info(true), '2.8.2', '<'))
			{
				$error[] = phpbb::$user->lang['INST_ERR_DB_SQLITE_VERSION'];
			}
		break;

		case 'firebird':
			// check the version of FB, use some hackery if we can't get access to the server info
			if ($db->service_handle !== false && strtolower($dbuser) == 'sysdba')
			{
				$val = @ibase_server_info($db->service_handle, IBASE_SVC_SERVER_VERSION);
				preg_match('#V([\d.]+)#', $val, $match);
				if ($match[1] < 2)
				{
					$error[] = phpbb::$user->lang['INST_ERR_DB_FIREBIRD_VERSION'];
				}
				$db_info = @ibase_db_info($db->service_handle, $dbname, IBASE_STS_HDR_PAGES);

				preg_match('/^\\s*Page size\\s*(\\d+)/m', $db_info, $regs);
				$page_size = intval($regs[1]);
				if ($page_size < 8192)
				{
					$error[] = phpbb::$user->lang['INST_ERR_DB_NO_FIREBIRD_PS'];
				}
			}
			else
			{
				$sql = "SELECT *
					FROM RDB\$FUNCTIONS
					WHERE RDB\$SYSTEM_FLAG IS NULL
						AND RDB\$FUNCTION_NAME = 'CHAR_LENGTH'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				// if its a UDF, its too old
				if ($row)
				{
					$error[] = phpbb::$user->lang['INST_ERR_DB_FIREBIRD_VERSION'];
				}
				else
				{
					$sql = "SELECT FIRST 0 char_length('')
						FROM RDB\$DATABASE";
					$result = $db->sql_query($sql);
					if (!$result) // This can only fail if char_length is not defined
					{
						$error[] = phpbb::$user->lang['INST_ERR_DB_FIREBIRD_VERSION'];
					}
					$db->sql_freeresult($result);
				}

				// Setup the stuff for our random table
				$char_array = array_merge(range('A', 'Z'), range('0', '9'));
				$char_len = mt_rand(7, 9);
				$char_array_len = sizeof($char_array) - 1;

				$final = '';

				for ($i = 0; $i < $char_len; $i++)
				{
					$final .= $char_array[mt_rand(0, $char_array_len)];
				}

				// Create some random table
				$sql = 'CREATE TABLE ' . $final . " (
					FIELD1 VARCHAR(255) CHARACTER SET UTF8 DEFAULT '' NOT NULL COLLATE UNICODE,
					FIELD2 INTEGER DEFAULT 0 NOT NULL);";
				$db->sql_query($sql);

				// Create an index that should fail if the page size is less than 8192
				$sql = 'CREATE INDEX ' . $final . ' ON ' . $final . '(FIELD1, FIELD2);';
				$db->sql_query($sql);

				if (ibase_errmsg() !== false)
				{
					$error[] = phpbb::$user->lang['INST_ERR_DB_NO_FIREBIRD_PS'];
				}

				// Kill the old table
				$db->sql_query('DROP TABLE ' . $final . ';');

				unset($final);
			}
		break;

		case 'oracle':
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

			if (version_compare($stats['NLS_RDBMS_VERSION'], '9.2', '<'))
			{
				$error[] = phpbb::$user->lang['INST_ERR_DB_ORACLE_VERSION'];
			}

			if ($stats['NLS_CHARACTERSET'] !== 'AL32UTF8')
			{
				$error[] = phpbb::$user->lang['INST_ERR_DB_NO_ORACLE_NLS'];
			}
		break;

		case 'postgres':

			if (version_compare($db->sql_server_info(true), '8.2', '<'))
			{
				$error[] = phpbb::$user->lang['INST_ERR_DB_POSTGRES_VERSION'];
			}
			else
			{
				$sql = "SHOW server_encoding;";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if ($row['server_encoding'] !== 'UNICODE' && $row['server_encoding'] !== 'UTF8')
				{
					$error[] = phpbb::$user->lang['INST_ERR_DB_NO_POSTGRES_UTF8'];
				}
			}
		break;

		case 'mssql_odbc':
			/**
			* @todo check odbc.defaultlrl (min 128K) and odbc.defaultbinmode (1)
			*/
		break;

		case 'db2':
			if (version_compare($db->sql_server_info(true), '8.2.2', '<'))
			{
				$error[] = phpbb::$user->lang['INST_ERR_DB_DB2_VERSION'];
			}

			// Now check the extension version
			if (!function_exists('db2_escape_string'))
			{
				$error[] = phpbb::$user->lang['INST_ERR_DB_DB2_EXT_VERSION'];
			}
		break;
	}

	if (sizeof($error))
	{
		return false;
	}

	return true;
}

?>