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

if ( !defined('IN_INSTALL') )
{
	// Someone has tried to access the file direct. This is not a good idea, so exit
	exit;
}

if (!empty($setmodules))
{
	$module[] = array(
		'module_type'		=> 'install',
		'module_title'		=> 'INSTALL',
		'module_filename'	=> substr(basename(__FILE__), 0, -strlen($phpEx)-1),
		'module_order'		=> 10,
		'module_subs'		=> '',
		'module_stages'		=> array('INTRO', 'REQUIREMENTS', 'DATABASE', 'ADMINISTRATOR', 'CONFIG_FILE', 'ADVANCED', 'CREATE_TABLE', 'FINAL'),
		'module_reqs'		=> ''
	);
}

/**
* Installation
* @package install
*/
class install_install extends module
{
	function install_install(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		global $lang, $template, $language;

		switch ($sub)
		{
			case 'intro' :
				$this->page_title = $lang['SUB_INTRO'];

				$template->assign_vars(array(
					'TITLE'			=> $lang['INSTALL_INTRO'],
					'BODY'			=> $lang['INSTALL_INTRO_BODY'],
					'L_SUBMIT'		=> $lang['NEXT'],
					'S_LANG_SELECT'	=> '<select id="language" name="language">' . $this->p_master->inst_language_select($language) . '</select>',
					'U_ACTION'		=> $this->p_master->module_url . "?mode=$mode&amp;sub=requirements&amp;language=$language",
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

			case 'config_file' :
				$this->create_config_file($mode, $sub);
			
			break;

			case 'advanced' :
				$this->obtain_advanced_settings($mode, $sub);

			break;

			case 'create_table':
				$this->load_schema($mode, $sub);
			
			break;

			case 'final' :
				$this->add_modules($mode, $sub);
				$this->add_language($mode, $sub);
				$this->add_bots($mode, $sub);
				$this->email_admin($mode, $sub);
			
			break;
		}

		$this->tpl_name = 'install_install';
	}

	/**
	* Checks that the server we are installing on meets the requirements for running phpBB
	*/
	function check_server_requirements($mode, $sub)
	{
		global $lang, $template, $phpbb_root_path, $phpEx, $language;

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

			if (!@extension_loaded($dll))
			{
				if (!$this->can_load_dll($dll))
				{
					$template->assign_block_vars('checks', array(
						'TITLE'		=> $lang['DLL_' . strtoupper($db_name)],
						'RESULT'	=> '<span style="color:red">' . $lang['UNAVAILABLE'] . '</span>',

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
			if (!@extension_loaded($dll))
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
		$exe = ((defined('PHP_OS')) && (preg_match('#^win#i', PHP_OS))) ? '.exe' : '';

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
			if (file_exists($phpbb_root_path . $dir) && is_dir($phpbb_root_path . $dir))
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

			$exists = ($exists) ? '<b style="color:green">' . $lang['FOUND'] . '</b>' : '<b style="color:red">' . $lang['NOT_FOUND'] . '</b>';
			$write = ($write) ? ', <b style="color:green">' . $lang['WRITEABLE'] . '</b>' : (($exists) ? ', <b style="color:red">' . $lang['UNWRITEABLE'] . '</b>' : '');

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

		$directories = array('config.'.$phpEx, 'images/avatars/upload/');

		foreach ($directories as $dir)
		{
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

			$exists_str = ($exists) ? '<b style="color:green">' . $lang['FOUND'] . '</b>' : '<b style="color:red">' . $lang['NOT_FOUND'] . '</b>';
			$write_str = ($write) ? ', <b style="color:green">' . $lang['WRITEABLE'] . '</b>' : (($exists) ? ', <b style="color:red">' . $lang['UNWRITEABLE'] . '</b>' : '');

			$template->assign_block_vars('checks', array(
				'TITLE'		=> $dir,
				'RESULT'	=> $exists_str . $write_str,

				'S_EXPLAIN'	=> false,
				'S_LEGEND'	=> false,
			));
		}

		// And finally where do we want to go next (well today is taken isn't it :P)
		$s_hidden_fields = ($img_imagick) ? '<input type="hidden" name="img_imagick" value="' . addslashes($img_imagick) . '" />' : '';

		$url = ($passed['php'] && $passed['db'] && $passed['files']) ? $this->p_master->module_url . "?mode=$mode&amp;sub=database&amp;language=$language" : $this->p_master->module_url . "?mode=$mode&amp;sub=requirements&amp;language=$language	";
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
					$error['db'][] = $lang['INST_ERR_NO_DB'];
				}
			}
			
			$dbpasswd = html_entity_decode($dbpasswd);

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
				if (!@extension_loaded($dbms_ary['MODULE']))
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
		$s_hidden_fields .= '<input type="hidden" name="language" value="' . $language . '" />';
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

		$url = ($connect_test) ? $this->p_master->module_url . "?mode=$mode&amp;sub=administrator" : $this->p_master->module_url . "?mode=$mode&amp;sub=database";
		$s_hidden_fields .= ($connect_test) ? '' : '<input type="hidden" name="testdb" value="true" />';

		$submit = $lang['NEXT_STEP'];

		$template->assign_vars(array(
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Obtain the administrator's name, password and email address
	*/
	function obtain_admin_settings($mode, $sub)
	{
		global $lang, $template, $phpEx;

		$this->page_title = $lang['STAGE_ADMINISTRATOR'];

		// Obtain any submitted data
		foreach ($this->request_vars as $var)
		{
			$$var = request_var($var, '');
		}

		if ($dbms == '')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			$this->p_master->redirect("index?mode=install");
		}

		$s_hidden_fields = ($img_imagick) ? '<input type="hidden" name="img_imagick" value="' . addslashes($img_imagick) . '" />' : '';
		$passed = false;

		if (isset($_POST['check']))
		{
			$error = array();

			// Check the entered email address and password
			if ($admin_name == '' || $admin_pass1 == '' || $admin_pass2 == '' || $board_email1 == '' || $board_email2 =='')
			{
				$error[] = $lang['INST_ERR_MISSING_DATA'];
			}

			if ($admin_pass1 != $admin_pass2 && $admin_pass1 != '')
			{
				$error[] = $lang['INST_ERR_PASSWORD_MISMATCH'];
			}

			// Test against the default username rules
			if ($admin_name != '' && strlen($admin_name) < 3)
			{
				$error[] = $lang['INST_ERR_USER_TOO_SHORT'];
			}

			if ($admin_name != '' && strlen($admin_name) > 20)
			{
				$error[] = $lang['INST_ERR_USER_TOO_LONG'];
			}

			// Test against the default password rules
			if ($admin_pass1 != '' && strlen($admin_pass1) < 6)
			{
				$error[] = $lang['INST_ERR_PASSWORD_TOO_SHORT'];
			}

			if ($admin_pass1 != '' && strlen($admin_pass1) > 30)
			{
				$error[] = $lang['INST_ERR_PASSWORD_TOO_LONG'];
			}

			if ($board_email1 != $board_email2 && $board_email1 != '')
			{
				$error[] = $lang['INST_ERR_EMAIL_MISMATCH'];
			}

			if ($board_email1 != '' && !preg_match('#^[a-z0-9\.\-_\+]+?@(.*?\.)*?[a-z0-9\-_]+?\.[a-z]{2,4}$#i', $board_email1))
			{
				$error[] = $lang['INST_ERR_EMAIL_INVALID'];
			}

			$template->assign_block_vars('checks', array(
				'S_LEGEND'			=> true,
				'S_FIRST_ROW'		=> true,
				'LEGEND'			=> $lang['STAGE_ADMINISTRATOR'],
				'LEGEND_EXPLAIN'	=> false,
			));

			if (!sizeof($error))
			{
				$passed = true;
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['ADMIN_TEST'],
					'RESULT'	=> '<b style="color:green">' . $lang['TESTS_PASSED'] . '</b>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
			else
			{
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['ADMIN_TEST'],
					'RESULT'	=> '<b style="color:red">' . implode('<br />', $error) . '</b>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
		}

		if (!$passed)
		{
			foreach ($this->admin_config_options as $config_key => $vars)
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
		else
		{
			foreach ($this->admin_config_options as $config_key => $vars)
			{
				if (!is_array($vars))
				{
					continue;
				}
				$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $$config_key . '" />';
			}
		}
		
		$s_hidden_fields .= ($img_imagick) ? '<input type="hidden" name="img_imagick" value="' . addslashes($img_imagick) . '" />' : '';
		$s_hidden_fields .= '<input type="hidden" name="language" value="' . $language . '" />';

		foreach ($this->db_config_options as $config_key => $vars)
		{
			if (!is_array($vars))
			{
				continue;
			}
			$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $$config_key . '" />';
		}

		$submit = $lang['NEXT_STEP'];

		$url = ($passed) ? $this->p_master->module_url . "?mode=$mode&amp;sub=config_file" : $this->p_master->module_url . "?mode=$mode&amp;sub=administrator";
		$s_hidden_fields .= ($passed) ? '' : '<input type="hidden" name="check" value="true" />';

		$template->assign_vars(array(
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Writes the config file to disk, or if unable to do so offers alternative methods
	*/
	function create_config_file($mode, $sub)
	{
		global $lang, $template, $phpbb_root_path, $phpEx;

		$this->page_title = $lang['STAGE_CONFIG_FILE'];

		// Obtain any submitted data
		foreach ($this->request_vars as $var)
		{
			$$var = request_var($var, '');
		}

		if ($dbms == '')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			$this->p_master->redirect("index?mode=install");
		}

		$s_hidden_fields = ($img_imagick) ? '<input type="hidden" name="img_imagick" value="' . addslashes($img_imagick) . '" />' : '';
		$s_hidden_fields .= '<input type="hidden" name="language" value="' . $language . '" />';
		$written = false;

		// Create a list of any PHP modules we wish to have loaded
		$load_extensions = array();
		$check_exts = array_merge(array($this->available_dbms[$dbms]['MODULE']), $this->php_dlls_other);

		foreach ($check_exts as $dll)
		{
			if (!@extension_loaded($dll))
			{
				if (!$this->can_load_dll($dll))
				{
					continue;
				}
				$load_extensions[] = "$dll.$suffix";
			}
		}

		$dbpasswd = html_entity_decode($dbpasswd);
		$load_extensions = implode(',', $load_extensions);

		// Time to convert the data provided into a config file
		$config_data = "<?php\n";
		$config_data .= "// phpBB 3.0.x auto-generated configuration file\n// Do not change anything in this file!\n";
		$config_data .= "\$dbms = '$dbms';\n";
		$config_data .= "\$dbhost = '$dbhost';\n";
		$config_data .= "\$dbport = '$dbport';\n";
		$config_data .= "\$dbname = '$dbname';\n";
		$config_data .= "\$dbuser = '$dbuser';\n";
		$config_data .= "\$dbpasswd = '$dbpasswd';\n\n";
		$config_data .= "\$table_prefix = '$table_prefix';\n";
//		$config_data .= "\$acm_type = '" . (($acm_type) ? $acm_type : 'file') . "';\n";
		$config_data .= "\$acm_type = 'file';\n";
		$config_data .= "\$load_extensions = '$load_extensions';\n\n";
		$config_data .= "define('PHPBB_INSTALLED', true);\n";
		$config_data .= "define('DEBUG', true);\n"; // @todo Comment out when final
		$config_data .= "define('DEBUG_EXTRA', true);\n"; // @todo Comment out when final
		$config_data .= '?' . '>'; // Done this to prevent highlighting editors getting confused!
	
		// Attempt to write out the config file directly. If it works, this is the easiest way to do it ...
		if ((file_exists($phpbb_root_path . 'config.' . $phpEx) && is_writeable($phpbb_root_path . 'config.' . $phpEx)) || is_writable($phpbb_root_path))
		{
			// Assume it will work ... if nothing goes wrong below
			$written = true;

			if (!($fp = @fopen($phpbb_root_path . 'config.'.$phpEx, 'w')))
			{
				// Something went wrong ... so let's try another method
				$written = false;
			}

			if (!(@fwrite($fp, $config_data)))
			{
				// Something went wrong ... so let's try another method
				$written = false;
			}

			@fclose($fp);
		}

		if (isset($_POST['dldone']))
		{
			// Do a basic check to make sure that the file has been uploaded
			// Note that all we check is that the file has _something_ in it
			// We don't compare the contents exactly - if they can't upload
			// a single file correctly, it's likely they will have other problems....
			if (filesize($phpbb_root_path . 'config.'.$phpEx) > 10)
			{
				$written = true;
			}
		}

		$config_options = array_merge($this->db_config_options, $this->admin_config_options);

		foreach ($config_options as $config_key => $vars)
		{
			if (!is_array($vars))
			{
				continue;
			}
			$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $$config_key . '" />';
		}

		if (!$written)
		{
			// OK, so it didn't work let's try the alternatives

			if (isset($_POST['dlconfig']))
			{
				// They want a copy of the file to download, so send the relevant headers and dump out the data
				header("Content-Type: text/x-delimtext; name=\"config.$phpEx\"");
				header("Content-disposition: attachment; filename=config.$phpEx");
				echo $config_data;
				exit;
			}

			// The option to download the config file is always available, so output it here
			$template->assign_vars(array(
				'BODY'					=> $lang['CONFIG_FILE_UNABLE_WRITE'],
				'L_DL_CONFIG'			=> $lang['DL_CONFIG'],
				'L_DL_CONFIG_EXPLAIN'	=> $lang['DL_CONFIG_EXPLAIN'],
				'L_DL_DONE'				=> $lang['DONE'],
				'L_DL_DOWNLOAD'			=> $lang['DL_DOWNLOAD'],
				'S_HIDDEN'				=> $s_hidden_fields,
				'S_SHOW_DOWNLOAD'		=> true,
				'U_ACTION'				=> $this->p_master->module_url . "?mode=$mode&amp;sub=config_file",
			));
			return;
		}
		else
		{
			$template->assign_vars(array(
				'BODY'		=> $lang['CONFIG_FILE_WRITTEN'],
				'L_SUBMIT'	=> $lang['NEXT_STEP'],
				'S_HIDDEN'	=> $s_hidden_fields,
				'U_ACTION'	=> $this->p_master->module_url . "?mode=$mode&amp;sub=advanced",
			));
			return;
		}
	}

	/**
	* Provide an opportunity to customise some advanced settings during the install
	* in case it is necessary for them to be set to access later
	*/
	function obtain_advanced_settings($mode, $sub)
	{
		global $lang, $template, $phpEx;

		$this->page_title = $lang['STAGE_ADVANCED'];

		// Obtain any submitted data
		foreach ($this->request_vars as $var)
		{
			$$var = request_var($var, '');
		}

		if ($dbms == '')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			$this->p_master->redirect("index?mode=install");
		}

		$s_hidden_fields = ($img_imagick) ? '<input type="hidden" name="img_imagick" value="' . addslashes($img_imagick) . '" />' : '';
		$s_hidden_fields .= '<input type="hidden" name="language" value="' . $language . '" />';

		$email_enable = ($email_enable !== '') ? $email_enable : true;
		$server_name = ($server_name !== '') ? $server_name : ((!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME'));
		$server_port = ($server_port !== '') ? $server_port : ((!empty($_SERVER['SERVER_PORT'])) ? (int) $_SERVER['SERVER_PORT'] : (int) getenv('SERVER_PORT'));
		$server_protocol = ($server_protocol !== '') ? $server_protocol : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://');
		$cookie_secure = ($cookie_secure !== '') ? $cookie_secure : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true : false);
		
		foreach ($this->advanced_config_options as $config_key => $vars)
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

		$config_options = array_merge($this->db_config_options, $this->admin_config_options);
		foreach ($config_options as $config_key => $vars)
		{
			if (!is_array($vars))
			{
				continue;
			}
			$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $$config_key . '" />';
		}

		$submit = $lang['NEXT_STEP'];

		$url = $this->p_master->module_url . "?mode=$mode&amp;sub=create_table";

		$template->assign_vars(array(
			'BODY'		=> $lang['STAGE_ADVANCED_EXPLAIN'],
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Load the contents of the schema into the database and then alter it based on what has been input during the installation
	*/
	function load_schema($mode, $sub)
	{
		global $db, $lang, $template, $phpbb_root_path, $phpEx;

		$this->page_title = $lang['STAGE_CREATE_TABLE'];

		// Obtain any submitted data
		foreach ($this->request_vars as $var)
		{
			$$var = request_var($var, '');
		}

		if ($dbms == '')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			$this->p_master->redirect("index?mode=install");
		}

		$cookie_domain = ($server_name != '') ? $server_name : (!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME');

		// If we get here and the extension isn't loaded it should be safe to just go ahead and load it 
		if (!@extension_loaded($this->available_dbms[$dbms]['MODULE']))
		{
			@dl($this->available_dbms[$dbms]['MODULE'] . ".$prefix");
		}

		$dbpasswd = html_entity_decode($dbpasswd);

		// Load the appropriate database class if not already loaded
		include($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);

		// Instantiate the database
		$sql_db = 'dbal_' . $dbms;
		$db = new $sql_db();
		$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false);

		// NOTE: trigger_error does not work here.
		$db->return_on_error = true;

		// Ok we have the db info go ahead and read in the relevant schema
		// and work on building the table
		$dbms_schema = 'schemas/' . $this->available_dbms[$dbms]['SCHEMA'] . '_schema.sql';

		// How should we treat this schema?
		$remove_remarks = $this->available_dbms[$dbms]['COMMENTS'];
		$delimiter = $this->available_dbms[$dbms]['DELIM'];

		$sql_query = @file_get_contents($dbms_schema);

		switch ($dbms)
		{
			case 'mysql':
			case 'mysql4':
				// We don't want MySQL mixing up collations
				if (version_compare(mysql_get_server_info(), '4.1.2', '>='))
				{
					$sql_query = preg_replace('/^\);$/m', ') DEFAULT CHARACTER SET latin1;', $sql_query);
				}

			break;

			case 'mysqli':
				// mysqli only works with MySQL > 4.1.3 so we'll just do a straight replace if using this DBMS
				$sql_query = preg_replace('/^\);$/m', ') DEFAULT CHARACTER SET latin1;', $sql_query);
			
			break;
		}

		$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);

		$remove_remarks($sql_query);

		$sql_query = split_sql_file($sql_query, $delimiter);

		foreach ($sql_query as $sql)
		{
			//$sql = trim(str_replace('|', ';', $sql));
			if (!$db->sql_query($sql))
			{
				$error = $db->sql_error();
				$this->p_master->db_error($error['message'], $sql, __LINE__, __FILE__);
			}
		}
		unset($sql_query);

		// Ok tables have been built, let's fill in the basic information
		$sql_query = file_get_contents('schemas/schema_data.sql');

		// Deal with any special comments and with MySQL < 4.1.2
		switch ($dbms)
		{
			case 'mssql':
			case 'mssql_odbc':
				$sql_query = preg_replace('#\# MSSQL IDENTITY (phpbb_[a-z_]+) (ON|OFF) \##s', 'SET IDENTITY_INSERT \1 \2;', $sql_query);
			break;

			case 'postgres':
				$sql_query = preg_replace('#\# POSTGRES (BEGIN|COMMIT) \##s', '\1; ', $sql_query);
			break;
		}

		$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);

		// Since there is only one schema file we know the comment style and are able to remove it directly with remove_remarks
		remove_remarks($sql_query);
		$sql_query = split_sql_file($sql_query, ';');

		foreach ($sql_query as $sql)
		{
			//$sql = trim(str_replace('|', ';', $sql));
			if (!$db->sql_query($sql))
			{
				$error = $db->sql_error();
				$this->p_master->db_error($error['message'], $sql, __LINE__, __FILE__);
			}
		}
		unset($sql_query);

		$current_time = time();

		// Set default config and post data, this applies to all DB's
		$sql_ary = array(
			'INSERT INTO ' . $table_prefix . "config (config_name, config_value)
				VALUES ('board_startdate', $current_time)",

			'INSERT INTO ' . $table_prefix . "config (config_name, config_value)
				VALUES ('default_lang', '" . $db->sql_escape($default_lang) . "')",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($img_imagick) . "'
				WHERE config_name = 'img_imagick'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($server_name) . "'
				WHERE config_name = 'server_name'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($server_port) . "'
				WHERE config_name = 'server_port'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($board_email1) . "'
				WHERE config_name = 'board_email'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($board_email1) . "'
				WHERE config_name = 'board_contact'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($cookie_domain) . "'
				WHERE config_name = 'cookie_domain'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($lang['default_dateformat']) . "'
				WHERE config_name = 'default_dateformat'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($email_enable) . "'
				WHERE config_name = 'email_enable'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($smtp_delivery) . "'
				WHERE config_name = 'smtp_delivery'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($smtp_host) . "'
				WHERE config_name = 'smtp_host'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($smtp_auth) . "'
				WHERE config_name = 'smtp_auth_method'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($smtp_user) . "'
				WHERE config_name = 'smtp_username'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($smtp_pass) . "'
				WHERE config_name = 'smtp_password'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($cookie_secure) . "'
				WHERE config_name = 'cookie_secure'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($force_server_vars) . "'
				WHERE config_name = 'force_server_vars'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($server_name) . "'
				WHERE config_name = 'server_name'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($server_protocol) . "'
				WHERE config_name = 'server_protocol'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($server_port) . "'
				WHERE config_name = 'server_port'",

			'UPDATE ' . $table_prefix . "config
				SET config_value = '" . $db->sql_escape($admin_name) . "'
				WHERE config_name = 'newest_username'",

			'UPDATE ' . $table_prefix . "users
				SET username = '" . $db->sql_escape($admin_name) . "', user_password='" . $db->sql_escape(md5($admin_pass1)) . "', user_lang = '" . $db->sql_escape($default_lang) . "', user_email='" . $db->sql_escape($board_email1) . "', user_dateformat='" . $db->sql_escape($lang['default_dateformat']) . "', user_email_hash = '" . (int) (crc32(strtolower($board_email1)) . strlen($board_email1)) . "'
				WHERE username = 'Admin'",

			'UPDATE ' . $table_prefix . "moderator_cache
				SET username = '" . $db->sql_escape($admin_name) . "'
				WHERE username = 'Admin'",

			'UPDATE ' . $table_prefix . "forums
				SET forum_last_poster_name = '" . $db->sql_escape($admin_name) . "'
				WHERE forum_last_poster_name = 'Admin'",

			'UPDATE ' . $table_prefix . "topics
				SET topic_first_poster_name = '" . $db->sql_escape($admin_name) . "', topic_last_poster_name = '" . $db->sql_escape($admin_name) . "'
				WHERE topic_first_poster_name = 'Admin'
					OR topic_last_poster_name = 'Admin'",

			'UPDATE ' . $table_prefix . "users
				SET user_regdate = $current_time", 

			'UPDATE ' . $table_prefix . "posts
				SET post_time = $current_time", 

			'UPDATE ' . $table_prefix . "topics
				SET topic_time = $current_time, topic_last_post_time = $current_time", 

			'UPDATE ' . $table_prefix . "forums
				SET forum_last_post_time = $current_time", 
		);

		// This is for people who have TTF disabled
		if (!(@function_exists('imagettfbbox') && @function_exists('imagettftext')))
		{
			$sql_ary[] = 'UPDATE ' . $table_prefix . "config
					SET config_value = '0'
					WHERE config_name = 'policy_shape'";
		}

		foreach ($sql_ary as $sql)
		{
			$sql = trim(str_replace('|', ';', $sql));

			if (!$db->sql_query($sql))
			{
				$error = $db->sql_error();
				$this->p_master->db_error($error['message'], $sql, __LINE__, __FILE__);
			}
		}

		foreach ($this->request_vars as $var)
		{
			$s_hidden_fields .= '<input type="hidden" name="' . $var . '" value="' . $$var . '" />';
		}

		$submit = $lang['NEXT_STEP'];

		$url = $this->p_master->module_url . "?mode=$mode&amp;sub=final";

		$template->assign_vars(array(
			'BODY'		=> $lang['STAGE_CREATE_TABLE_EXPLAIN'],
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Populate the module tables
	*/
	function add_modules($mode, $sub)
	{
		global $db, $lang, $phpbb_root_path, $phpEx;

		// Obtain any submitted data
		foreach ($this->request_vars as $var)
		{
			$$var = request_var($var, '');
		}

		$dbpasswd = html_entity_decode($dbpasswd);

		// Load the appropriate database class if not already loaded
		include($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);

		// Instantiate the database
		$sql_db = 'dbal_' . $dbms;
		$db = new $sql_db();
		$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false);

		// NOTE: trigger_error does not work here.
		$db->return_on_error = true;

		include_once($phpbb_root_path . 'includes/constants.' . $phpEx);
		include_once($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);

		$_module = &new acp_modules();
		$module_classes = array('acp', 'mcp', 'ucp');

		// Add categories
		foreach ($module_classes as $module_class)
		{
			$categories = array();

			// Set the module class
			$_module->module_class = $module_class;

			foreach ($this->module_categories[$module_class] as $cat_name => $subs)
			{
				$module_data = array(
					'module_basename'	=> '',
					'module_enabled'	=> 1,
					'module_display'	=> 1,
					'parent_id'			=> 0,
					'module_class'		=> $module_class,
					'module_langname'	=> $cat_name,
					'module_mode'		=> '',
					'module_auth'		=> '',
				);

				// Add category
				$_module->update_module_data($module_data, true);

				// Check for last sql error happened
				if ($db->sql_error_triggered)
				{
					$error = $db->sql_error($db->sql_error_sql);
					$this->p_master->db_error($error['message'], $db->sql_error_sql, __LINE__, __FILE__);
				}

				$categories[$cat_name]['id'] = $module_data['module_id'];
				$categories[$cat_name]['parent_id'] = 0;

				// Create sub-categories...
				if (is_array($subs))
				{
					foreach ($subs as $level2_name)
					{
						$module_data = array(
							'module_basename'	=> '',
							'module_enabled'	=> 1,
							'module_display'	=> 1,
							'parent_id'			=> $categories[$cat_name]['id'],
							'module_class'		=> $module_class,
							'module_langname'	=> $level2_name,
							'module_mode'		=> '',
							'module_auth'		=> '',
						);

						$_module->update_module_data($module_data, true);

						// Check for last sql error happened
						if ($db->sql_error_triggered)
						{
							$error = $db->sql_error($db->sql_error_sql);
							$this->p_master->db_error($error['message'], $db->sql_error_sql, __LINE__, __FILE__);
						}

						$categories[$level2_name]['id'] = $module_data['module_id'];
						$categories[$level2_name]['parent_id'] = $categories[$cat_name]['id'];
					}
				}
			}

			// Get the modules we want to add... returned sorted by name
			$module_info = $_module->get_module_infos('', $module_class);

			foreach ($module_info as $module_basename => $fileinfo)
			{
				foreach ($fileinfo['modes'] as $module_mode => $row)
				{
					foreach ($row['cat'] as $cat_name)
					{
						if (!isset($categories[$cat_name]))
						{
							continue;
						}
						$module_data = array(
							'module_basename'	=> $module_basename,
							'module_enabled'	=> 1,
							'module_display'	=> (isset($row['display'])) ? $row['display'] : 1,
							'parent_id'			=> $categories[$cat_name]['id'],
							'module_class'		=> $module_class,
							'module_langname'	=> $row['title'],
							'module_mode'		=> $module_mode,
							'module_auth'		=> $row['auth'],
						);

						$_module->update_module_data($module_data, true);

						// Check for last sql error happened
						if ($db->sql_error_triggered)
						{
							$error = $db->sql_error($db->sql_error_sql);
							$this->p_master->db_error($error['message'], $db->sql_error_sql, __LINE__, __FILE__);
						}
					}
				}
			}

			// Move some of the modules around since the code above will put them in the wrong place
			if ($module_class == 'acp')
			{
				// Move main module 4 up...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'main'
						AND module_class = 'acp'
						AND module_mode = 'main'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
	
				$_module->move_module_by($row, 'move_up', 4);

				// Move permissions intro screen module 4 up...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'permissions'
						AND module_class = 'acp'
						AND module_mode = 'intro'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
	
				$_module->move_module_by($row, 'move_up', 4);

				// Move manage users screen module 4 up...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'users'
						AND module_class = 'acp'
						AND module_mode = 'overview'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
	
				$_module->move_module_by($row, 'move_up', 4);
			}

			// And now for the special ones
			// (these are modules which appear in multiple categories and thus get added manually to some for more control)
			if (isset($this->module_extras[$module_class]))
			{
				foreach ($this->module_extras[$module_class] as $cat_name => $mods)
				{
					$sql = 'SELECT module_id, left_id, right_id
						FROM ' . MODULES_TABLE . " 
						WHERE module_langname = '" . $db->sql_escape($cat_name) . "'
							AND module_class = '" . $db->sql_escape($module_class) . "'";
					$result = $db->sql_query_limit($sql, 1);
					$row2 = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					foreach ($mods as $mod_name)
					{
						$sql = 'SELECT *
							FROM ' . MODULES_TABLE . " 
							WHERE module_langname = '" . $db->sql_escape($mod_name) . "'
								AND module_class = '" . $db->sql_escape($module_class) . "'
								AND module_basename <> ''";
						$result = $db->sql_query_limit($sql, 1);
						$module_data = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						unset($module_data['module_id']);
						unset($module_data['left_id']);
						unset($module_data['right_id']);

						$module_data['parent_id'] = $row2['module_id'];

						$_module->update_module_data($module_data, true);

						// Check for last sql error happened
						if ($db->sql_error_triggered)
						{
							$error = $db->sql_error($db->sql_error_sql);
							$this->p_master->db_error($error['message'], $db->sql_error_sql, __LINE__, __FILE__);
						}
					}
				}
			}

			$_module->remove_cache_file();
		}
	}

	/**
	* Populate the language tables
	*/
	function add_language($mode, $sub)
	{
		global $db, $lang, $phpbb_root_path, $phpEx;

		$dir = @opendir($phpbb_root_path . 'language');
		while (($file = readdir($dir)) !== false)
		{
			$path = $phpbb_root_path . 'language/' . $file;

			if (is_dir($path) && !is_link($path) && file_exists($path . '/iso.txt'))
			{
				$lang_pack = file("{$phpbb_root_path}language/$path/iso.txt");
				$sql_ary = array(
					'lang_iso'			=> basename($path),
					'lang_dir'			=> basename($path),
					'lang_english_name'	=> trim(htmlspecialchars($lang_pack[0])),
					'lang_local_name'	=> trim(htmlspecialchars($lang_pack[1])),
					'lang_author'		=> trim(htmlspecialchars($lang_pack[2])),
				);

				$db->sql_query('INSERT INTO ' . LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

				if ($db->sql_error_triggered)
				{
					$error = $db->sql_error($db->sql_error_sql);
					$this->p_master->db_error($error['message'], $db->sql_error_sql, __LINE__, __FILE__);
				}
			}
		}
	}

	/**
	* Add search robots to the database
	*/
	function add_bots($mode, $sub)
	{
		global $db, $lang, $phpbb_root_path, $phpEx, $config;

		// Obtain any submitted data
		foreach ($this->request_vars as $var)
		{
			$$var = request_var($var, '');
		}

		// Fill the config array - it is needed by those functions we call
		$sql = 'SELECT *
			FROM ' . CONFIG_TABLE;
		$result = $db->sql_query($sql);

		$config = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$config[$row['config_name']] = $row['config_value'];
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . "
			WHERE group_name = 'BOTS'";
		$result = $db->sql_query($sql);
		$group_id = (int) $db->sql_fetchfield('group_id');
		$db->sql_freeresult($result);

		if (!$group_id)
		{
			// If we reach this point then something has gone very wrong
			$this->p_master->error($lang['NO_GROUP'], __LINE__, __FILE__);
		}

		if (!function_exists('user_add'))
		{
			include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}

		foreach ($this->bot_list as $bot_name => $bot_ary)
		{
			$user_row = array(
				'user_type'			=> USER_IGNORE,
				'group_id'			=> $group_id,
				'username'			=> $bot_name,
				'user_regdate'		=> time(),
				'user_password'		=> '',
				'user_colour'		=> '9E8DA7',
				'user_email'		=> '',
				'user_lang'			=> $default_lang,
				'user_style'		=> 1,
				'user_timezone'		=> 0,
				'user_dateformat'	=> $lang['default_dateformat'],
			);
			
			$user_id = user_add($user_row);

			if (!$user_id)
			{
				// If we can't insert this user then continue to the next one to avoid inconsistant data
				$this->p_master->db_error('Unable to insert bot into users table', $db->sql_error_sql, __LINE__, __FILE__, true);
				continue;
			}

			$sql = 'INSERT INTO ' . BOTS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'bot_active'	=> 1,
				'bot_name'		=> $bot_name,
				'user_id'		=> $user_id,
				'bot_agent'		=> $bot_ary[0],
				'bot_ip'		=> $bot_ary[1],
			));

			$result = $db->sql_query($sql);
		}
	}

	/**
	* Sends an email to the board administrator with their password and some useful links
	*/
	function email_admin($mode, $sub)
	{
		global $auth, $config, $db, $lang, $template, $user, $phpbb_root_path, $phpEx;

		$this->page_title = $lang['STAGE_FINAL'];

		// Obtain any submitted data
		foreach ($this->request_vars as $var)
		{
			$$var = request_var($var, '');
		}

		// Load the basic configuration data
		include_once($phpbb_root_path . 'includes/constants.' . $phpEx);

		$sql = 'SELECT *
			FROM ' . CONFIG_TABLE;
		$result = $db->sql_query($sql);

		$config = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$config[$row['config_name']] = $row['config_value'];
		}
		$db->sql_freeresult($result);

		$user->session_begin();
		$auth->login($admin_name, $admin_pass1, false, true, true);

		// OK, Now that we've reached this point we can be confident that everything
		// is installed and working......I hope :)
		// So it's time to send an email to the administrator confirming the details
		// they entered

		if ($config['email_enable'])
		{
			include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

			$messenger = new messenger(false);

			$messenger->template('installed', $language);

			$messenger->replyto($config['board_contact']);
			$messenger->to($board_email1, $admin_name);

			$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
			$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
			$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
			$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);

			$messenger->assign_vars(array(
				'USERNAME'		=> html_entity_decode($admin_name),
				'PASSWORD'		=> html_entity_decode($admin_pass1),
				'U_BOARD'		=> generate_board_url(),
				'EMAIL_SIG'		=> str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']))
			);

			$messenger->send(NOTIFY_EMAIL);
		}

		// And finally, add a note to the log
		add_log('admin', 'LOG_INSTALL_INSTALLED', $config['version']);

		$template->assign_vars(array(
			'TITLE'		=> $lang['INSTALL_CONGRATS'],
			'BODY'		=> sprintf($lang['INSTALL_CONGRATS_EXPLAIN'], '<a href="../docs/README.html" target="_blank">', '</a>'),
			'L_SUBMIT'	=> $lang['INSTALL_LOGIN'],
			'U_ACTION'	=> append_sid($phpbb_root_path . 'adm/index.' . $phpEx),
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

		// Check that we actually have a database name before going any further.....
		if ($dbms != 'sqlite' && $dbname === '')
		{
			$error[] = $lang['INST_ERR_DB_NO_NAME'];
			return false;
		}

		// Make sure we don't have a daft user who thinks having the SQLite database in the forum directory is a good idea
		if ($dbms == 'sqlite' && stripos(phpbb_realpath($dbhost), phpbb_realpath('../')) === 0)
		{
			$error[] = $lang['INST_ERR_DB_FORUM_PATH'];
			return false;
		}

		// Check the prefix length to ensure that index names are not too long
		switch ($dbms)
		{
			case 'mysql':
			case 'mysql4':
			case 'mysqli':
			case 'postgres':
				$prefix_length = 36;

			break;

			case 'mssql':
			case 'mssql_odbc':
				$prefix_length = 90;
			
			break;

			case 'oracle':
			case 'sqlite':
				$prefix_length = 200;
			
			break;

			case 'firebird':
				$prefix_length = 6;

			break;
		}

		if (strlen($table_prefix) > $prefix_length)
		{
			$error[] = sprintf($lang['INST_ERR_PREFIX_TOO_LONG'], $prefix_length);
			return false;
		}

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
					$field = 'name';
				break;

				case 'postgres':
					$sql = "SELECT relname 
						FROM pg_class 
						WHERE relkind = 'r' 
							AND relname NOT LIKE 'pg\_%'";
					$field = 'relname';
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
					// All phpBB installations will at least have config else it won't work
					if (in_array(strtolower($row[$field]), $table_ary))
					{
						$error[] = $lang['INST_ERR_PREFIX'];
						break;
					}
				}
				while ($row = $db->sql_fetchrow($result));
			}
			$db->sql_freeresult($result);

			// Make sure that the user has selected a sensible DBAL for the DBMS actually installed
			switch ($dbms)
			{
				case 'mysql4':
					if (version_compare(mysql_get_server_info($db->db_connect_id), '4.0.0', '<'))
					{
						$error[] = $lang['INST_ERR_DB_NO_MYSQL4'];
					}

				break;

				case 'mysqli':
					if (version_compare(mysqli_get_server_info($db->db_connect_id), '4.1.3', '<'))
					{
						$error[] = $lang['INST_ERR_DB_NO_MYSQLI'];
					}
				
				break;
			}

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
	* Generate a list of available mail server authentication methods
	*/
	function mail_auth_select($selected_method)
	{
		global $lang;

		$auth_methods = array('PLAIN', 'LOGIN', 'CRAM-MD5', 'DIGEST-MD5', 'POP-BEFORE-SMTP');
		$s_smtp_auth_options = '';

		foreach ($auth_methods as $method)
		{
			$s_smtp_auth_options .= '<option value="' . $method . '"' . (($selected_method == $method) ? ' selected="selected"' : '') . '>' . $lang['SMTP_' . str_replace('-', '_', $method)] . '</option>';
		}

		return $s_smtp_auth_options;
	}


	/**
	* The variables that we will be passing between pages
	* Used to retrieve data quickly on each page
	*/
	var $request_vars = array('language', 'dbms', 'dbhost', 'dbport', 'dbuser', 'dbpasswd', 'dbname', 'table_prefix', 'default_lang', 'admin_name', 'admin_pass1', 'admin_pass2', 'board_email1', 'board_email2', 'img_imagick', 'ftp_path', 'ftp_user', 'ftp_pass', 'email_enable', 'smtp_delivery', 'smtp_host', 'smtp_auth', 'smtp_user', 'smtp_pass', 'cookie_secure', 'force_server_vars', 'server_protocol', 'server_name', 'server_port');

	/**
	* The information below will be used to build the input fields presented to the user
	*/
	var $db_config_options = array(
		'legend1'				=> 'DB_CONFIG',
		'dbms'					=> array('lang' => 'DBMS',			'type' => 'select', 'options' => '$this->module->dbms_select(\'{VALUE}\')', 'explain' => false),
		'dbhost'				=> array('lang' => 'DB_HOST',		'type' => 'text:25:100', 'explain' => true),
		'dbport'				=> array('lang' => 'DB_PORT',		'type' => 'text:25:100', 'explain' => true),
		'dbname'				=> array('lang' => 'DB_NAME',		'type' => 'text:25:100', 'explain' => false),
		'dbuser'				=> array('lang' => 'DB_USERNAME',	'type' => 'text:25:100', 'explain' => false),
		'dbpasswd'				=> array('lang' => 'DB_PASSWORD',	'type' => 'password:25:100', 'explain' => false),
		'table_prefix'			=> array('lang' => 'TABLE_PREFIX',	'type' => 'text:25:100', 'explain' => false),
	);
	var $admin_config_options = array(
		'legend1'				=> 'ADMIN_CONFIG',
		'default_lang'			=> array('lang' => 'DEFAULT_LANG',				'type' => 'select', 'options' => '$this->module->inst_language_select(\'{VALUE}\')', 'explain' => false),
		'admin_name'			=> array('lang' => 'ADMIN_USERNAME',			'type' => 'text:25:100', 'explain' => true),
		'admin_pass1'			=> array('lang' => 'ADMIN_PASSWORD',			'type' => 'password:25:100', 'explain' => true),
		'admin_pass2'			=> array('lang' => 'ADMIN_PASSWORD_CONFIRM',	'type' => 'password:25:100', 'explain' => false),
		'board_email1'			=> array('lang' => 'CONTACT_EMAIL',				'type' => 'text:25:100', 'explain' => false),
		'board_email2'			=> array('lang' => 'CONTACT_EMAIL_CONFIRM',		'type' => 'text:25:100', 'explain' => false),
	);
	var $advanced_config_options = array(
		'legend1'				=> 'ACP_EMAIL_SETTINGS',
		'email_enable'			=> array('lang' => 'ENABLE_EMAIL',		'type' => 'radio:enabled_disabled', 'explain' => true),
		'smtp_delivery'			=> array('lang' => 'USE_SMTP',			'type' => 'radio:yes_no', 'explain' => true),
		'smtp_host'				=> array('lang' => 'SMTP_SERVER',		'type' => 'text:25:50', 'explain' => false),
		'smtp_auth'				=> array('lang' => 'SMTP_AUTH_METHOD',	'type' => 'select', 'options' => '$this->module->mail_auth_select(\'{VALUE}\')', 'explain' => true),
		'smtp_user'				=> array('lang' => 'SMTP_USERNAME',		'type' => 'text:25:255', 'explain' => true),
		'smtp_pass'				=> array('lang' => 'SMTP_PASSWORD',		'type' => 'password:25:255', 'explain' => true),

		'legend2'				=> 'SERVER_URL_SETTINGS',
		'cookie_secure'			=> array('lang' => 'COOKIE_SECURE',		'type' => 'radio:enabled_disabled', 'explain' => true),
		'force_server_vars'		=> array('lang' => 'FORCE_SERVER_VARS',	'type' => 'radio:yes_no', 'explain' => true),
		'server_protocol'		=> array('lang' => 'SERVER_PROTOCOL',	'type' => 'text:10:10', 'explain' => true),
		'server_name'			=> array('lang' => 'SERVER_NAME',		'type' => 'text:40:255', 'explain' => true),
		'server_port'			=> array('lang' => 'SERVER_PORT',		'type' => 'text:5:5', 'explain' => true),
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
			'LABEL'			=> 'MS SQL Server 2000+',
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
			'LABEL'			=> 'PostgreSQL 7.x/8.x',
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

	/**
	* A list of the web-crawlers/bots we recognise by default
	*/
	var $bot_list = array(
		'Alexa'			=> array('ia_archiver', '66.28.250.,209.237.238.'),
		'Fastcrawler'	=> array('FAST MetaWeb Crawler', '66.151.181.'),
		'Googlebot'		=> array('Googlebot/', ''),
		'Inktomi'		=> array('Slurp/', '216.35.116.,66.196.'),
	);

	/**
	* Define the module structure so that we can populate the database without
	* needing to hard-code module_id values
	*/
	var $module_categories = array(
		'acp'	=> array(
			'ACP_CAT_GENERAL'		=> array(
				'ACP_QUICK_ACCESS',
				'ACP_BOARD_CONFIGURATION',
				'ACP_CLIENT_COMMUNICATION',
				'ACP_SERVER_CONFIGURATION',
			),
			'ACP_CAT_FORUMS'		=> array(
				'ACP_MANAGE_FORUMS',
				'ACP_FORUM_BASED_PERMISSIONS',
			),
			'ACP_CAT_POSTING'		=> array(
				'ACP_MESSAGES',
				'ACP_ATTACHMENTS',
			),
			'ACP_CAT_USERGROUP'		=> array(
				'ACP_CAT_USERS',
				'ACP_GROUPS',
				'ACP_USER_SECURITY',
			),
			'ACP_CAT_PERMISSIONS'	=> array(
				'ACP_GLOBAL_PERMISSIONS',
				'ACP_FORUM_BASED_PERMISSIONS',
				'ACP_PERMISSION_ROLES',
				'ACP_PERMISSION_MASKS',
			),
			'ACP_CAT_STYLES'		=> array(
				'ACP_STYLE_MANAGEMENT',
				'ACP_STYLE_COMPONENTS',
			),
			'ACP_CAT_MAINTENANCE'	=> array(
				'ACP_FORUM_LOGS',
				'ACP_CAT_DATABASE',
			),
			'ACP_CAT_SYSTEM'		=> array(
				'ACP_AUTOMATION',
				'ACP_GENERAL_TASKS',
				'ACP_MODULE_MANAGEMENT',
			),
			'ACP_CAT_DOT_MODS'		=> null,
		),
		'mcp'	=> array(
			'MCP_MAIN'		=> null,
			'MCP_QUEUE'		=> null,
			'MCP_REPORTS'	=> null,
			'MCP_NOTES'		=> null,
			'MCP_WARN'		=> null,
			'MCP_LOGS'		=> null,
			'MCP_BAN'		=> null,
		),
		'ucp'	=> array(
			'UCP_MAIN'			=> null,
			'UCP_PROFILE'		=> null,
			'UCP_PREFS'			=> null,
			'UCP_PM'			=> null,
			'UCP_USERGROUPS'	=> null,
			'UCP_ATTACHMENTS'	=> null,
			'UCP_ZEBRA'			=> null,
		),
	);

	var $module_extras = array(
		'acp'	=> array(
			'ACP_QUICK_ACCESS' => array(
				'ACP_MANAGE_USERS',
				'ACP_GROUPS_MANAGE',
				'ACP_MANAGE_FORUMS',
				'ACP_MOD_LOGS',
				'ACP_BOTS',
				'ACP_PHP_INFO',
			),
			'ACP_FORUM_BASED_PERMISSIONS' => array(
				'ACP_FORUM_PERMISSIONS',
				'ACP_FORUM_MODERATORS',
				'ACP_USERS_FORUM_PERMISSIONS',
				'ACP_GROUPS_FORUM_PERMISSIONS',
			),
		),
	);
}

?>