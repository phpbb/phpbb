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
		'module_stages' => array('INTRO', 'REQUIREMENTS', 'BASIC', 'CONFIG_FILE', 'ADVANCED', 'FINAL'),
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

		switch($sub)
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

		$exists = ($exists) ? '<b style="color:green">' . $lang['FILE_FOUND'] . '</b>' : '<b style="color:red">' . $lang['FILE_NOT_FOUND'] . '</b>';
		$write = ($write) ? ', <b style="color:green">' . $lang['FILE_WRITEABLE'] . '</b>' : (($exists) ? ', <b style="color:red">' . $lang['FILE_UNWRITEABLE'] . '</b>' : '');

		$template->assign_block_vars('checks', array(
			'TITLE'		=> $dir,
			'RESULT'	=> $exists . $write,

			'S_EXPLAIN'	=> false,
			'S_LEGEND'	=> false,
		));

		// And finally where do we want to go next (well today is taken isn't it :P)
		$s_hidden_fields = ($img_imagick) ? '<input type="hidden" name="img_imagick" value="' . addslashes($img_imagick) . '" />' : '';

//		$url = ($passed['php'] && $passed['db'] && $passed['files']) ? $this->p_master->module_url . "?mode=$mode&amp;sub=database" : $this->p_master->module_url . "?mode=$mode&amp;sub=requirements";
// The road ahead is still under construction, follow the diversion back to the olod installer..... ;)
		$url = ($passed['php'] && $passed['db'] && $passed['files']) ? "install.$phpEx?stage=1" : $this->p_master->module_url . "?mode=$mode&amp;sub=requirements";
		$submit = ($passed['php'] && $passed['db'] && $passed['files']) ? $lang['INSTALL_START'] : $lang['INSTALL_TEST'];


		$template->assign_vars(array(
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $url,
		));
	}

	function can_load_dll($dll)
	{
		global $suffix;

		return ((@ini_get('enable_dl') || strtolower(@ini_get('enable_dl')) == 'on') && (!@ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'off') && @dl($dll . ".$suffix")) ? true : false;
	}

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
		'mysql'		=> array(
			'LABEL'			=> 'MySQL',
			'SCHEMA'		=> 'mysql',
			'MODULE'		=> 'mysql', 
			'DELIM'			=> ';',
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