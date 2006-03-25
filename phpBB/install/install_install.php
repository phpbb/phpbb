<?php
/** 
*
* @package install
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/

if (!empty($setmodules))
{
	$module[] = array(
		'module_type' => 'install',
		'module_title' => 'INSTALL',
		'module_filename' => substr(basename(__FILE__), 0, -strlen($phpEx)-1),
		'module_order' => 10,
		'module_subs' => '',
		'module_stages' => array('INTRO', 'REQUIREMENTS', 'DATABASE', 'ADMINISTRATOR', 'CONFIG_FILE', 'ADVANCED', 'FINAL'),
		'module_reqs' => ''
	);

	return;
}

class install_install extends module
{
	function install_install(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		global $lang, $template;

		switch ($sub)
		{
			case 'intro' :
				$this->page_title = $lang['SUB_INTRO'];

				$template->assign_vars(array(
					'TITLE'		=> $lang['INSTALL_INTRO'],
					'BODY'		=> $lang['INSTALL_INTRO_BODY'],
					'L_SUBMIT'	=> $lang['NEXT'],
					'U_ACTION'	=> $this->p_master->module_url . "?mode=$mode&amp;sub=requirements",
				));

			break;

			case 'requirements' :
				$this->check_server_requirements($mode, $sub);

			break;

			case 'database' :
				$this->obtain_database_settings($mode, $sub);
			
			break;

			case 'administrator' :
				$this->obtain_admin_settings($mode, $sub);

			break;
		}

		$this->tpl_name = 'install_install';
	}

	/**
	* Checks that the server we are installing on meets the requirements for running phpBB
	*/
	function check_server_requirements($mode, $sub)
	{
		global $lang, $template, $phpbb_root_path, $phpEx;

		$this->page_title = $lang['STAGE_REQUIREMENTS'];

		$template->assign_vars(array(
			'TITLE'		=> $lang['REQUIREMENTS_TITLE'],
			'BODY'		=> $lang['REQUIREMENTS_EXPLAIN'],
		));

		$passed = array('php' => false, 'db' => false, 'files' => false);

		// Test for basic PHP settings
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'S_FIRST_ROW'		=> true,
			'LEGEND'			=> $lang['PHP_SETTINGS'],
			'LEGEND_EXPLAIN'	=> $lang['PHP_SETTINGS_EXPLAIN'],
		));

		// Test the minimum PHP version
		$php_version = phpversion();

		if (version_compare($php_version, '4.3.3') < 0)
		{
			$result = '<b style="color:red">' . $lang['NO'] . '</b>';
		}
		else
		{
			$passed['php'] = true;
			// We also give feedback on whether we're running in safe mode
			$result = '<b style="color:green">' . $lang['YES'];
			if (@ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'on')
			{
				$result .= ', ' . $lang['PHP_SAFE_MODE'];
			}
			$result .= '</b>';
		}
		$template->assign_block_vars('checks', array(
			'TITLE'			=> $lang['PHP_VERSION_REQD'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> false,
			'S_LEGEND'		=> false,
		));

		// Check for register_globals being enabled
		if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on')
		{
			$result = '<b style="color:red">' . $lang['NO'] . '</b>';
		}
		else
		{
			$result = '<b style="color:green">' . $lang['YES'] . '</b>';
		}

		$template->assign_block_vars('checks', array(
			'TITLE'			=> $lang['PHP_REGISTER_GLOBALS'],
			'TITLE_EXPLAIN'	=> $lang['PHP_REGISTER_GLOBALS_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
			'S_LEGEND'		=> false,
		));

		// Test for available database modules
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'S_FIRST_ROW'		=> false,
			'LEGEND'			=> $lang['PHP_SUPPORTED_DB'],
			'LEGEND_EXPLAIN'	=> $lang['PHP_SUPPORTED_DB_EXPLAIN'],
		));

		$dlls_db = array();
		$passed['db'] = false;
		foreach ($this->available_dbms as $db_name => $db_ary)
		{
			$dll = $db_ary['MODULE'];

			if (!extension_loaded($dll))
			{
				if (!$this->can_load_dll($dll))
				{
					$template->assign_block_vars('checks', array(
						'TITLE'		=> $lang['DLL_' . strtoupper($db_name)],
						'RESULT'	=> '<b style="color:red">' . $lang['UNAVAILABLE'] . '</b>',

						'S_EXPLAIN'	=> false,
						'S_LEGEND'	=> false,
					));
					continue;
				}
			}

			$template->assign_block_vars('checks', array(
				'TITLE'		=> $lang['DLL_' . strtoupper($db_name)],
				'RESULT'	=> '<b style="color:green">' . $lang['AVAILABLE'] . '</b>',

				'S_EXPLAIN'	=> false,
				'S_LEGEND'	=> false,
			));
			$passed['db'] = true;
		}

		// Test for other modules
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'S_FIRST_ROW'		=> false,
			'LEGEND'			=> $lang['PHP_OPTIONAL_MODULE'],
			'LEGEND_EXPLAIN'	=> $lang['PHP_OPTIONAL_MODULE_EXPLAIN'],
		));

		foreach ($this->php_dlls_other as $dll)
		{
			if (!extension_loaded($dll))
			{
				if (!$this->can_load_dll($dll))
				{
					$template->assign_block_vars('checks', array(
						'TITLE'		=> $lang['DLL_' . strtoupper($dll)],
						'RESULT'	=> '<b style="color:red">' . $lang['UNAVAILABLE'] . '</b>',

						'S_EXPLAIN'	=> false,
						'S_LEGEND'	=> false,
					));
					continue;
				}
			}
			$template->assign_block_vars('checks', array(
				'TITLE'		=> $lang['DLL_' . strtoupper($dll)],
				'RESULT'	=> '<b style="color:green">' . $lang['AVAILABLE'] . '</b>',

				'S_EXPLAIN'	=> false,
				'S_LEGEND'	=> false,
			));
		}

		// Can we find Imagemagick anywhere on the system?
		$exe = ((defined('PHP_OS')) && (preg_match('#win#i', PHP_OS))) ? '.exe' : '';

		$magic_home = getenv('MAGICK_HOME');
		$img_imagick = '';
		if (empty($magic_home))
		{
			$locations = array('C:/WINDOWS/', 'C:/WINNT/', 'C:/WINDOWS/SYSTEM/', 'C:/WINNT/SYSTEM/', 'C:/WINDOWS/SYSTEM32/', 'C:/WINNT/SYSTEM32/', '/usr/bin/', '/usr/sbin/', '/usr/local/bin/', '/usr/local/sbin/', '/opt/', '/usr/imagemagick/', '/usr/bin/imagemagick/');
			$path_locations = str_replace('\\', '/', (explode(($exe) ? ';' : ':', getenv('PATH'))));

			$locations = array_merge($path_locations, $locations);
			foreach ($locations as $location)
			{
				// The path might not end properly, fudge it
				if (substr($location, -1, 1) !== '/')
				{
					$location .= '/';
				}

				if (@is_readable($location . 'mogrify' . $exe) && @filesize($location . 'mogrify' . $exe) > 3000)
				{
					$img_imagick = str_replace('\\', '/', $location);
					continue;
				}
			}
		}
		else
		{
			$img_imagick = str_replace('\\', '/', $magic_home);
		}

		$template->assign_block_vars('checks', array(
			'TITLE'		=> $lang['APP_MAGICK'],
			'RESULT'	=> ($img_imagick) ? '<b style="color:green">' . $lang['AVAILABLE'] . ', ' . $img_imagick . '</b>' : '<b style="color:blue">' . $lang['NO_LOCATION'] . '</b>',

			'S_EXPLAIN'	=> false,
			'S_LEGEND'	=> false,
		));

		// Check permissions on files/directories we need access to
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'S_FIRST_ROW'		=> false,
			'LEGEND'			=> $lang['FILES_REQUIRED'],
			'LEGEND_EXPLAIN'	=> $lang['FILES_REQUIRED_EXPLAIN'],
		));

		$directories = array('cache/', 'files/', 'store/');

		umask(0);

		$passed['files'] = true;
		foreach ($directories as $dir)
		{
			$write = $exists = true;
			if (file_exists($phpbb_root_path . $dir))
			{
				if (!is_writeable($phpbb_root_path . $dir))
				{
					$write = (@chmod($phpbb_root_path . $dir, 0777)) ? true : false;
				}
			}
			else
			{
				$write = $exists = (@mkdir($phpbb_root_path . $dir, 0777)) ? true : false;
			}

			$passed['files'] = ($exists && $write && $passed['files']) ? true : false;

			$exists = ($exists) ? '<b style="color:green">' . $lang['FILE_FOUND'] . '</b>' : '<b style="color:red">' . $lang['FILE_NOT_FOUND'] . '</b>';
			$write = ($write) ? ', <b style="color:green">' . $lang['FILE_WRITEABLE'] . '</b>' : (($exists) ? ', <b style="color:red">' . $lang['FILE_UNWRITEABLE'] . '</b>' : '');

			$template->assign_block_vars('checks', array(
				'TITLE'		=> $dir,
				'RESULT'	=> $exists . $write,

				'S_EXPLAIN'	=> false,
				'S_LEGEND'	=> false,
			));
		}

		// Check permissions on files/directories it would be useful access to
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'S_FIRST_ROW'		=> false,
			'LEGEND'			=> $lang['FILES_OPTIONAL'],
			'LEGEND_EXPLAIN'	=> $lang['FILES_OPTIONAL_EXPLAIN'],
		));

		// config.php ... let's just warn the user it's not writeable
		$dir = 'config.'.$phpEx;
		$write = $exists = true;
		if (file_exists($phpbb_root_path . $dir))
		{
			if (!is_writeable($phpbb_root_path . $dir))
			{
				$write = false;
			}
		}
		else
		{
			$write = $exists = false;
		}

		$exists_str = ($exists) ? '<b style="color:green">' . $lang['FILE_FOUND'] . '</b>' : '<b style="color:red">' . $lang['FILE_NOT_FOUND'] . '</b>';
		$write_str = ($write) ? ', <b style="color:green">' . $lang['FILE_WRITEABLE'] . '</b>' : (($exists) ? ', <b style="color:red">' . $lang['FILE_UNWRITEABLE'] . '</b>' : '');

		$template->assign_block_vars('checks', array(
			'TITLE'		=> $dir,
			'RESULT'	=> $exists_str . $write_str,

			'S_EXPLAIN'	=> false,
			'S_LEGEND'	=> false,
		));

		// And finally where do we want to go next (well today is taken isn't it :P)
		$s_hidden_fields = ($img_imagick) ? '<input type="hidden" name="img_imagick" value="' . addslashes($img_imagick) . '" />' : '';

		$url = ($passed['php'] && $passed['db'] && $passed['files']) ? $this->p_master->module_url . "?mode=$mode&amp;sub=database" : $this->p_master->module_url . "?mode=$mode&amp;sub=requirements";
		$submit = ($passed['php'] && $passed['db'] && $passed['files']) ? $lang['INSTALL_START'] : $lang['INSTALL_TEST'];


		$template->assign_vars(array(
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Obtain the information required to connect to the database
	*/
	function obtain_database_settings($mode, $sub)
	{
		global $lang, $template, $phpEx;

		$this->page_title = $lang['STAGE_DATABASE'];

		// Obtain any submitted data
		foreach ($this->request_vars as $var)
		{
			$$var = request_var($var, '');
		}

		$connect_test = false;

		// Has the user opted to test the connection?
		if (isset($_POST['testdb']))
		{
			// If the module for the selected database isn't loaded, let's try and load it now
			if (!@extension_loaded($this->available_dbms[$dbms]['MODULE']))
			{
				if (!$this->can_load_dll($this->available_dbms[$dbms]['MODULE']))
				{
					$error['db'][] = $lang['INST_ERR_NO_DB'];;
				}
			}

			$connect_test = $this->connect_check_db(true, $error, $dbms, $table_prefix, $dbhost, $dbuser, $dbpasswd, $dbname, $dbport);

			$template->assign_block_vars('checks', array(
				'S_LEGEND'			=> true,
				'S_FIRST_ROW'		=> true,
				'LEGEND'			=> $lang['DB_CONNECTION'],
				'LEGEND_EXPLAIN'	=> false,
			));

			if ($connect_test)
			{
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['DB_TEST'],
					'RESULT'	=> '<b style="color:green">' . $lang['SUCCESSFUL_CONNECT'] . '</b>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
			else
			{
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['DB_TEST'],
					'RESULT'	=> '<b style="color:red">' . implode('<br />', $error) . '</b>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
		}

		if (!$connect_test)
		{
			// Update the list of available DBMS modules to only contain those which can be used
			$available_dbms_temp = array();
			foreach ($this->available_dbms as $type => $dbms_ary)
			{
				if (!extension_loaded($dbms_ary['MODULE']))
				{
					if (!$this->can_load_dll($dbms_ary['MODULE']))
					{
						continue;
					}
				}

				$available_dbms_temp[$type] = $dbms_ary;
			}

			$this->available_dbms = &$available_dbms_temp;

			// And now for the main part of this page
			$table_prefix = (!empty($table_prefix) ? $table_prefix : 'phpbb_');

			foreach ($this->db_config_options as $config_key => $vars)
			{
				if (!is_array($vars) && strpos($config_key, 'legend') === false)
				{
					continue;
				}

				if (strpos($config_key, 'legend') !== false)
				{
					$template->assign_block_vars('options', array(
						'S_LEGEND'		=> true,
						'LEGEND'		=> $lang[$vars])
					);

					continue;
				}

				$options = isset($vars['options']) ? $vars['options'] : '';

				$template->assign_block_vars('options', array(
					'KEY'			=> $config_key,
					'TITLE'			=> $lang[$vars['lang']],
					'S_EXPLAIN'		=> $vars['explain'],
					'S_LEGEND'		=> false,
					'TITLE_EXPLAIN'	=> ($vars['explain']) ? $lang[$vars['lang'] . '_EXPLAIN'] : '',
					'CONTENT'		=> $this->p_master->input_field($config_key, $vars['type'], $$config_key, $options),
					)
				);
			}
		}

		// And finally where do we want to go next (well today is taken isn't it :P)
		$s_hidden_fields = ($img_imagick) ? '<input type="hidden" name="img_imagick" value="' . addslashes($img_imagick) . '" />' : '';
		if ($connect_test)
		{
			foreach ($this->db_config_options as $config_key => $vars)
			{
				if (!is_array($vars))
				{
					continue;
				}
				$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $$config_key . '" />';
			}
		}

//		$url = ($connect_test) ? $this->p_master->module_url . "?mode=$mode&amp;sub=administrator" : $this->p_master->module_url . "?mode=$mode&amp;sub=database";
// The road ahead is still under construction, follow the diversion back to the old installer..... ;)
		$s_hidden_fields .= ($connect_test) ? '' : '<input type="hidden" name="testdb" value="true" />';
		$url = ($connect_test) ? "install.$phpEx?stage=1" : $this->p_master->module_url . "?mode=$mode&amp;sub=database";

		$submit = $lang['NEXT_STEP'];


		$template->assign_vars(array(
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Determine if we are able to load a specified PHP module
	*/
	function can_load_dll($dll)
	{
		global $suffix;

		return ((@ini_get('enable_dl') || strtolower(@ini_get('enable_dl')) == 'on') && (!@ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'off') && @dl($dll . ".$suffix")) ? true : false;
	}

	/**
	* Used to test whether we are able to connect to the database the user has specified
	* and identify any problems (eg there are already tables with the names we want to use
	*/
	function connect_check_db($error_connect, &$error, $dbms, $table_prefix, $dbhost, $dbuser, $dbpasswd, $dbname, $dbport)
	{
		global $phpbb_root_path, $phpEx, $config, $lang;

		// Include the DB layer
		include($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);

		// Instantiate it and set return on error true
		$sql_db = 'dbal_' . $dbms;
		$db = new $sql_db();
		$db->sql_return_on_error(true);

		// Try and connect ...
		if (is_array($db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false)))
		{
			$db_error = $db->sql_error();
			$error[] = $lang['INST_ERR_DB_CONNECT'] . '<br />' . (($db_error['message']) ? $db_error['message'] : $lang['INST_ERR_DB_NO_ERROR']);
		}
		else
		{
			switch ($dbms)
			{
				case 'mysql':
				case 'mysql4':
				case 'mysqli':
				case 'sqlite':
					$sql = "SHOW TABLES";
					$field = "Tables_in_{$dbname}";
					break;

				case 'mssql':
				case 'mssql_odbc':
					$sql = "SELECT name 
						FROM sysobjects 
						WHERE type='U'";
					$field = "name";
					break;

				case 'postgres':
					$sql = "SELECT relname 
						FROM pg_class 
						WHERE relkind = 'r' 
							AND relname NOT LIKE 'pg\_%'";
					$field = "relname";
					break;

				case 'firebird':
					$sql = 'SELECT rdb$relation_name
						FROM rdb$relations
						WHERE rdb$view_source is null
							AND rdb$system_flag = 0';
					$field = 'rdb$relation_name';
					break;

				case 'oracle':
					$sql = 'SELECT table_name FROM USER_TABLES';
					$field = 'table_name';
					break;
			}
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				// Likely matches for an existing phpBB installation
				$table_ary = array($table_prefix . 'attachments', $table_prefix . 'config', $table_prefix . 'sessions', $table_prefix . 'topics', $table_prefix . 'users');

				do
				{
					// All phpBB installations will at least have config else it won't
					// work
					if (in_array(strtolower($row[$field]), $table_ary))
					{
						$error[] = $lang['INST_ERR_PREFIX'];
						break;
					}
				}
				while ($row = $db->sql_fetchrow($result));
			}
			$db->sql_freeresult($result);

			$db->sql_close();
		}

		if ($error_connect && (!isset($error) || !sizeof($error)))
		{
			return true;
		}
		return false;
	}

	/**
	* Generate the drop down of available database options
	*/
	function dbms_select($default='')
	{
		$dbms_options = '';
		foreach ($this->available_dbms as $dbms_name => $details)
		{
			$selected = ($dbms_name == $default) ? ' selected="selected"' : '';
			$dbms_options .= '<option value="' . $dbms_name . '"' . $selected .'>' . $details['LABEL'] . '</option>';
		}
		return $dbms_options;
	}

	/**
	* The variables that we will be passing between pages
	* Used to retrieve data quickly on each page
	*/
	var $request_vars = array('language', 'dbms', 'dbhost', 'dbport', 'dbuser', 'dbpasswd', 'dbname', 'table_prefix', 'admin_name', 'admin_pass1', 'admin_pass2', 'board_email1', 'board_email2', 'server_name', 'server_port', 'script_path', 'img_imagick', 'ftp_path', 'ftp_user', 'ftp_pass');

	/**
	* The information below will be used to build the input fields presented to the user
	*/
	var $db_config_options = array(
		'legend'		=> 'DB_CONFIG',
		'dbms'			=> array('lang' => 'DBMS', 'type' => 'select', 'options' => '$this->module->dbms_select(\'{VALUE}\')', 'explain' => false),
		'dbhost'		=> array('lang' => 'DB_HOST', 'type' => 'text:25:100', 'explain' => true),
		'dbport'		=> array('lang' => 'DB_PORT', 'type' => 'text:25:100', 'explain' => true),
		'dbname'		=> array('lang' => 'DB_NAME', 'type' => 'text:25:100', 'explain' => false),
		'dbuser'		=> array('lang' => 'DB_USERNAME', 'type' => 'text:25:100', 'explain' => false),
		'dbpasswd'		=> array('lang' => 'DB_PASSWORD', 'type' => 'password:25:100', 'explain' => false),
		'table_prefix'	=> array('lang' => 'TABLE_PREFIX', 'type' => 'text:25:100', 'explain' => false),
	);

	/**
	* Specific PHP modules we may require for certain optional or extended features
	*/
	var $php_dlls_other = array('zlib', 'ftp', 'xml');

	/**
	* Details of the database management systems supported
	*/
	var $available_dbms = array(
		'firebird'	=> array(
			'LABEL'			=> 'FireBird',
			'SCHEMA'		=> 'firebird',
			'MODULE'		=> 'interbase', 
			'DELIM'			=> ';;',
			'COMMENTS'		=> 'remove_remarks'
		),
		'mysqli'	=> array(
			'LABEL'			=> 'MySQL 4.1.x/5.x (MySQLi)',
			'SCHEMA'		=> 'mysql',
			'MODULE'		=> 'mysqli',
			'DELIM'			=> ';',
			'COMMENTS'		=> 'remove_remarks'
		),
		'mysql4'	=> array(
			'LABEL'			=> 'MySQL 4.x/5.x',
			'SCHEMA'		=> 'mysql',
			'MODULE'		=> 'mysql', 
			'DELIM'			=> ';',
			'COMMENTS'		=> 'remove_remarks'
		),
		'mysql'		=> array(
			'LABEL'			=> 'MySQL',
			'SCHEMA'		=> 'mysql',
			'MODULE'		=> 'mysql', 
			'DELIM'			=> ';',
			'COMMENTS'		=> 'remove_remarks'
		),
		'mssql'		=> array(
			'LABEL'			=> 'MS SQL Server 7/2000',
			'SCHEMA'		=> 'mssql',
			'MODULE'		=> 'mssql', 
			'DELIM'			=> 'GO',
			'COMMENTS'		=> 'remove_comments'
		),
		'mssql_odbc'=>	array(
			'LABEL'			=> 'MS SQL Server [ ODBC ]',
			'SCHEMA'		=> 'mssql',
			'MODULE'		=> 'odbc', 
			'DELIM'			=> 'GO',
			'COMMENTS'		=> 'remove_comments'
		),
		'oracle'	=>	array(
			'LABEL'			=> 'Oracle',
			'SCHEMA'		=> 'oracle',
			'MODULE'		=> 'oci8', 
			'DELIM'			=> '/',
			'COMMENTS'		=> 'remove_comments'
		),
		'postgres' => array(
			'LABEL'			=> 'PostgreSQL 7.x',
			'SCHEMA'		=> 'postgres',
			'MODULE'		=> 'pgsql', 
			'DELIM'			=> ';',
			'COMMENTS'		=> 'remove_comments'
		),
		'sqlite'		=> array(
			'LABEL'			=> 'SQLite',
			'SCHEMA'		=> 'sqlite',
			'MODULE'		=> 'sqlite', 
			'DELIM'			=> ';',
			'COMMENTS'		=> 'remove_remarks'
		),
	);
}

?>