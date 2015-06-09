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
*/
if (!defined('IN_INSTALL'))
{
	// Someone has tried to access the file direct. This is not a good idea, so exit
	exit;
}

if (!empty($setmodules))
{
	// If phpBB is already installed we do not include this module
	if (phpbb_check_installation_exists($phpbb_root_path, $phpEx) && !file_exists($phpbb_root_path . 'cache/install_lock'))
	{
		return;
	}

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
*/
class install_install extends module
{
	function install_install(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		global $lang, $template, $language, $phpbb_root_path, $phpEx;
		global $phpbb_container, $cache, $phpbb_log, $request, $phpbb_config_php_file;

		switch ($sub)
		{
			case 'intro':
				$phpbb_container->get('cache.driver')->purge();

				$this->page_title = $lang['SUB_INTRO'];

				$template->assign_vars(array(
					'TITLE'			=> $lang['INSTALL_INTRO'],
					'BODY'			=> $lang['INSTALL_INTRO_BODY'],
					'L_SUBMIT'		=> $lang['NEXT_STEP'],
					'S_LANG_SELECT'	=> '<select id="language" name="language">' . $this->p_master->inst_language_select($language) . '</select>',
					'U_ACTION'		=> $this->p_master->module_url . "?mode=$mode&amp;sub=requirements&amp;language=$language",
				));

			break;

			case 'requirements':
				$this->check_server_requirements($mode, $sub);

			break;

			case 'database':
				$this->obtain_database_settings($mode, $sub);

			break;

			case 'administrator':
				$this->obtain_admin_settings($mode, $sub);

			break;

			case 'config_file':
				$this->create_config_file($mode, $sub);

			break;

			case 'advanced':
				$this->obtain_advanced_settings($mode, $sub);

			break;

			case 'create_table':
				$this->load_schema($mode, $sub);
			break;

			case 'final':
				// Enable super globals to prevent issues with the new \phpbb\request\request object
				$request->enable_super_globals();

				// Create a normal container now
				$phpbb_container_builder = new \phpbb\di\container_builder($phpbb_config_php_file, $phpbb_root_path, $phpEx);
				$phpbb_container = $phpbb_container_builder->get_container();

				// Sets the global variables
				$cache = $phpbb_container->get('cache');
				$phpbb_log = $phpbb_container->get('log');

				$this->build_search_index($mode, $sub);
				$this->add_modules($mode, $sub);
				$this->add_language($mode, $sub);
				$this->add_bots($mode, $sub);
				$this->email_admin($mode, $sub);
				$this->disable_avatars_if_unwritable();
				$this->populate_migrations($phpbb_container->get('ext.manager'), $phpbb_container->get('migrator'));

				// Remove the lock file
				@unlink($phpbb_root_path . 'cache/install_lock');

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

		$passed = array('php' => false, 'db' => false, 'files' => false, 'pcre' => false, 'imagesize' => false, 'json' => false,);

		// Test for basic PHP settings
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $lang['PHP_SETTINGS'],
			'LEGEND_EXPLAIN'	=> $lang['PHP_SETTINGS_EXPLAIN'],
		));

		// Test the minimum PHP version
		$php_version = PHP_VERSION;

		if (version_compare($php_version, '5.3.3') < 0)
		{
			$result = '<strong style="color:red">' . $lang['NO'] . '</strong>';
		}
		else
		{
			$passed['php'] = true;

			// We also give feedback on whether we're running in safe mode
			$result = '<strong style="color:green">' . $lang['YES'];
			if (@ini_get('safe_mode') == '1' || strtolower(@ini_get('safe_mode')) == 'on')
			{
				$result .= ', ' . $lang['PHP_SAFE_MODE'];
			}
			$result .= '</strong>';
		}

		$template->assign_block_vars('checks', array(
			'TITLE'			=> $lang['PHP_VERSION_REQD'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> false,
			'S_LEGEND'		=> false,
		));

		// Don't check for register_globals on 5.4+
		if (version_compare($php_version, '5.4.0-dev') < 0)
		{
			// Check for register_globals being enabled
			if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on')
			{
				$result = '<strong style="color:red">' . $lang['NO'] . '</strong>';
			}
			else
			{
				$result = '<strong style="color:green">' . $lang['YES'] . '</strong>';
			}

			$template->assign_block_vars('checks', array(
				'TITLE'			=> $lang['PHP_REGISTER_GLOBALS'],
				'TITLE_EXPLAIN'	=> $lang['PHP_REGISTER_GLOBALS_EXPLAIN'],
				'RESULT'		=> $result,

				'S_EXPLAIN'		=> true,
				'S_LEGEND'		=> false,
			));
		}

		// Check for url_fopen
		if (@ini_get('allow_url_fopen') == '1' || strtolower(@ini_get('allow_url_fopen')) == 'on')
		{
			$result = '<strong style="color:green">' . $lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . $lang['NO'] . '</strong>';
		}

		$template->assign_block_vars('checks', array(
			'TITLE'			=> $lang['PHP_URL_FOPEN_SUPPORT'],
			'TITLE_EXPLAIN'	=> $lang['PHP_URL_FOPEN_SUPPORT_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
			'S_LEGEND'		=> false,
		));

		// Check for getimagesize
		if (@function_exists('getimagesize'))
		{
			$passed['imagesize'] = true;
			$result = '<strong style="color:green">' . $lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . $lang['NO'] . '</strong>';
		}

		$template->assign_block_vars('checks', array(
			'TITLE'			=> $lang['PHP_GETIMAGESIZE_SUPPORT'],
			'TITLE_EXPLAIN'	=> $lang['PHP_GETIMAGESIZE_SUPPORT_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
			'S_LEGEND'		=> false,
		));

		// Check for PCRE UTF-8 support
		if (@preg_match('//u', ''))
		{
			$passed['pcre'] = true;
			$result = '<strong style="color:green">' . $lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . $lang['NO'] . '</strong>';
		}

		$template->assign_block_vars('checks', array(
			'TITLE'			=> $lang['PCRE_UTF_SUPPORT'],
			'TITLE_EXPLAIN'	=> $lang['PCRE_UTF_SUPPORT_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
			'S_LEGEND'		=> false,
		));

		// Check for php json support
		if (@extension_loaded('json'))
		{
			$passed['json'] = true;
			$result = '<strong style="color:green">' . $lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . $lang['NO'] . '</strong>';
		}

		$template->assign_block_vars('checks', array(
			'TITLE'			=> $lang['PHP_JSON_SUPPORT'],
			'TITLE_EXPLAIN'	=> $lang['PHP_JSON_SUPPORT_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
			'S_LEGEND'		=> false,
		));

		$passed['mbstring'] = true;
		if (@extension_loaded('mbstring'))
		{
			// Test for available database modules
			$template->assign_block_vars('checks', array(
				'S_LEGEND'			=> true,
				'LEGEND'			=> $lang['MBSTRING_CHECK'],
				'LEGEND_EXPLAIN'	=> $lang['MBSTRING_CHECK_EXPLAIN'],
			));

			$checks = array(
				array('func_overload', '&', MB_OVERLOAD_MAIL|MB_OVERLOAD_STRING),
				array('encoding_translation', '!=', 0),
				array('http_input', '!=', array('pass', '')),
				array('http_output', '!=', array('pass', ''))
			);

			foreach ($checks as $mb_checks)
			{
				$ini_val = @ini_get('mbstring.' . $mb_checks[0]);
				switch ($mb_checks[1])
				{
					case '&':
						if (intval($ini_val) & $mb_checks[2])
						{
							$result = '<strong style="color:red">' . $lang['NO'] . '</strong>';
							$passed['mbstring'] = false;
						}
						else
						{
							$result = '<strong style="color:green">' . $lang['YES'] . '</strong>';
						}
					break;

					case '!=':
						if (!is_array($mb_checks[2]) && $ini_val != $mb_checks[2] ||
							is_array($mb_checks[2]) && !in_array($ini_val, $mb_checks[2]))
						{
							$result = '<strong style="color:red">' . $lang['NO'] . '</strong>';
							$passed['mbstring'] = false;
						}
						else
						{
							$result = '<strong style="color:green">' . $lang['YES'] . '</strong>';
						}
					break;
				}
				$template->assign_block_vars('checks', array(
					'TITLE'			=> $lang['MBSTRING_' . strtoupper($mb_checks[0])],
					'TITLE_EXPLAIN'	=> $lang['MBSTRING_' . strtoupper($mb_checks[0]) . '_EXPLAIN'],
					'RESULT'		=> $result,

					'S_EXPLAIN'		=> true,
					'S_LEGEND'		=> false,
				));
			}
		}

		// Test for available database modules
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $lang['PHP_SUPPORTED_DB'],
			'LEGEND_EXPLAIN'	=> $lang['PHP_SUPPORTED_DB_EXPLAIN'],
		));

		$available_dbms = get_available_dbms(false, true);
		$passed['db'] = $available_dbms['ANY_DB_SUPPORT'];
		unset($available_dbms['ANY_DB_SUPPORT']);

		foreach ($available_dbms as $db_name => $db_ary)
		{
			if (!$db_ary['AVAILABLE'])
			{
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['DLL_' . strtoupper($db_name)],
					'RESULT'	=> '<span style="color:red">' . $lang['UNAVAILABLE'] . '</span>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
			else
			{
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['DLL_' . strtoupper($db_name)],
					'RESULT'	=> '<strong style="color:green">' . $lang['AVAILABLE'] . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
		}

		// Test for other modules
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $lang['PHP_OPTIONAL_MODULE'],
			'LEGEND_EXPLAIN'	=> $lang['PHP_OPTIONAL_MODULE_EXPLAIN'],
		));

		foreach ($this->php_dlls_other as $dll)
		{
			if (!@extension_loaded($dll))
			{
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['DLL_' . strtoupper($dll)],
					'RESULT'	=> '<strong style="color:red">' . $lang['UNAVAILABLE'] . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
				continue;
			}

			$template->assign_block_vars('checks', array(
				'TITLE'		=> $lang['DLL_' . strtoupper($dll)],
				'RESULT'	=> '<strong style="color:green">' . $lang['AVAILABLE'] . '</strong>',

				'S_EXPLAIN'	=> false,
				'S_LEGEND'	=> false,
			));
		}

		// Can we find Imagemagick anywhere on the system?
		$exe = (DIRECTORY_SEPARATOR == '\\') ? '.exe' : '';

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

				if (@file_exists($location) && @is_readable($location . 'mogrify' . $exe) && @filesize($location . 'mogrify' . $exe) > 3000)
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
			'RESULT'	=> ($img_imagick) ? '<strong style="color:green">' . $lang['AVAILABLE'] . ', ' . $img_imagick . '</strong>' : '<strong style="color:blue">' . $lang['NO_LOCATION'] . '</strong>',

			'S_EXPLAIN'	=> false,
			'S_LEGEND'	=> false,
		));

		// Check permissions on files/directories we need access to
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $lang['FILES_REQUIRED'],
			'LEGEND_EXPLAIN'	=> $lang['FILES_REQUIRED_EXPLAIN'],
		));

		$directories = array('cache/', 'files/', 'store/');

		umask(0);

		$passed['files'] = true;
		foreach ($directories as $dir)
		{
			$exists = $write = false;

			// Try to create the directory if it does not exist
			if (!file_exists($phpbb_root_path . $dir))
			{
				@mkdir($phpbb_root_path . $dir, 0777);
				phpbb_chmod($phpbb_root_path . $dir, CHMOD_READ | CHMOD_WRITE);
			}

			// Now really check
			if (file_exists($phpbb_root_path . $dir) && is_dir($phpbb_root_path . $dir))
			{
				phpbb_chmod($phpbb_root_path . $dir, CHMOD_READ | CHMOD_WRITE);
				$exists = true;
			}

			// Now check if it is writable by storing a simple file
			$fp = @fopen($phpbb_root_path . $dir . 'test_lock', 'wb');
			if ($fp !== false)
			{
				$write = true;
			}
			@fclose($fp);

			@unlink($phpbb_root_path . $dir . 'test_lock');

			$passed['files'] = ($exists && $write && $passed['files']) ? true : false;

			$exists = ($exists) ? '<strong style="color:green">' . $lang['FOUND'] . '</strong>' : '<strong style="color:red">' . $lang['NOT_FOUND'] . '</strong>';
			$write = ($write) ? ', <strong style="color:green">' . $lang['WRITABLE'] . '</strong>' : (($exists) ? ', <strong style="color:red">' . $lang['UNWRITABLE'] . '</strong>' : '');

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
			'LEGEND'			=> $lang['FILES_OPTIONAL'],
			'LEGEND_EXPLAIN'	=> $lang['FILES_OPTIONAL_EXPLAIN'],
		));

		$directories = array('config.' . $phpEx, 'images/avatars/upload/');

		foreach ($directories as $dir)
		{
			$write = $exists = true;
			if (file_exists($phpbb_root_path . $dir))
			{
				if (!phpbb_is_writable($phpbb_root_path . $dir))
				{
					$write = false;
				}
			}
			else
			{
				$write = $exists = false;
			}

			$exists_str = ($exists) ? '<strong style="color:green">' . $lang['FOUND'] . '</strong>' : '<strong style="color:red">' . $lang['NOT_FOUND'] . '</strong>';
			$write_str = ($write) ? ', <strong style="color:green">' . $lang['WRITABLE'] . '</strong>' : (($exists) ? ', <strong style="color:red">' . $lang['UNWRITABLE'] . '</strong>' : '');

			$template->assign_block_vars('checks', array(
				'TITLE'		=> $dir,
				'RESULT'	=> $exists_str . $write_str,

				'S_EXPLAIN'	=> false,
				'S_LEGEND'	=> false,
			));
		}

		// And finally where do we want to go next (well today is taken isn't it :P)
		$s_hidden_fields = ($img_imagick) ? '<input type="hidden" name="img_imagick" value="' . addslashes($img_imagick) . '" />' : '';

		$url = (!in_array(false, $passed)) ? $this->p_master->module_url . "?mode=$mode&amp;sub=database&amp;language=$language" : $this->p_master->module_url . "?mode=$mode&amp;sub=requirements&amp;language=$language	";
		$submit = (!in_array(false, $passed)) ? $lang['INSTALL_START'] : $lang['INSTALL_TEST'];

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
		$data = $this->get_submitted_data();

		$connect_test = false;
		$error = array();
		$available_dbms = get_available_dbms(false, true);

		// Has the user opted to test the connection?
		if (isset($_POST['testdb']))
		{
			if (!isset($available_dbms[$data['dbms']]) || !$available_dbms[$data['dbms']]['AVAILABLE'])
			{
				$error[] = $lang['INST_ERR_NO_DB'];
				$connect_test = false;
			}
			else if (!preg_match(get_preg_expression('table_prefix'), $data['table_prefix']))
			{
				$error[] = $lang['INST_ERR_DB_INVALID_PREFIX'];
				$connect_test = false;
			}
			else
			{
				$connect_test = connect_check_db(true, $error, $available_dbms[$data['dbms']], $data['table_prefix'], $data['dbhost'], $data['dbuser'], htmlspecialchars_decode($data['dbpasswd']), $data['dbname'], $data['dbport']);
			}

			$template->assign_block_vars('checks', array(
				'S_LEGEND'			=> true,
				'LEGEND'			=> $lang['DB_CONNECTION'],
				'LEGEND_EXPLAIN'	=> false,
			));

			if ($connect_test)
			{
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['DB_TEST'],
					'RESULT'	=> '<strong style="color:green">' . $lang['SUCCESSFUL_CONNECT'] . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
			else
			{
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['DB_TEST'],
					'RESULT'	=> '<strong style="color:red">' . implode('<br />', $error) . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
		}

		if (!$connect_test)
		{
			// Update the list of available DBMS modules to only contain those which can be used
			$available_dbms_temp = array();
			foreach ($available_dbms as $type => $dbms_ary)
			{
				if (!$dbms_ary['AVAILABLE'])
				{
					continue;
				}

				$available_dbms_temp[$type] = $dbms_ary;
			}

			$available_dbms = &$available_dbms_temp;

			// And now for the main part of this page
			$data['table_prefix'] = (!empty($data['table_prefix']) ? $data['table_prefix'] : 'phpbb_');

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
					'CONTENT'		=> $this->p_master->input_field($config_key, $vars['type'], $data[$config_key], $options),
					)
				);
			}
		}

		// And finally where do we want to go next (well today is taken isn't it :P)
		$s_hidden_fields = ($data['img_imagick']) ? '<input type="hidden" name="img_imagick" value="' . addslashes($data['img_imagick']) . '" />' : '';
		$s_hidden_fields .= '<input type="hidden" name="language" value="' . $data['language'] . '" />';
		if ($connect_test)
		{
			foreach ($this->db_config_options as $config_key => $vars)
			{
				if (!is_array($vars))
				{
					continue;
				}
				$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
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
		$data = $this->get_submitted_data();

		if ($data['dbms'] == '')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			$this->p_master->redirect("index.$phpEx?mode=install");
		}

		$s_hidden_fields = ($data['img_imagick']) ? '<input type="hidden" name="img_imagick" value="' . addslashes($data['img_imagick']) . '" />' : '';
		$passed = false;

		$data['default_lang'] = ($data['default_lang'] !== '') ? $data['default_lang'] : $data['language'];

		if (isset($_POST['check']))
		{
			$error = array();

			// Check the entered email address and password
			if ($data['admin_name'] == '' || $data['admin_pass1'] == '' || $data['admin_pass2'] == '' || $data['board_email'] == '')
			{
				$error[] = $lang['INST_ERR_MISSING_DATA'];
			}

			if ($data['admin_pass1'] != $data['admin_pass2'] && $data['admin_pass1'] != '')
			{
				$error[] = $lang['INST_ERR_PASSWORD_MISMATCH'];
			}

			// Test against the default username rules
			if ($data['admin_name'] != '' && utf8_strlen($data['admin_name']) < 3)
			{
				$error[] = $lang['INST_ERR_USER_TOO_SHORT'];
			}

			if ($data['admin_name'] != '' && utf8_strlen($data['admin_name']) > 20)
			{
				$error[] = $lang['INST_ERR_USER_TOO_LONG'];
			}

			// Test against the default password rules
			if ($data['admin_pass1'] != '' && utf8_strlen($data['admin_pass1']) < 6)
			{
				$error[] = $lang['INST_ERR_PASSWORD_TOO_SHORT'];
			}

			if ($data['admin_pass1'] != '' && utf8_strlen($data['admin_pass1']) > 30)
			{
				$error[] = $lang['INST_ERR_PASSWORD_TOO_LONG'];
			}

			if ($data['board_email'] != '' && !preg_match('/^' . get_preg_expression('email') . '$/i', $data['board_email']))
			{
				$error[] = $lang['INST_ERR_EMAIL_INVALID'];
			}

			$template->assign_block_vars('checks', array(
				'S_LEGEND'			=> true,
				'LEGEND'			=> $lang['STAGE_ADMINISTRATOR'],
				'LEGEND_EXPLAIN'	=> false,
			));

			if (!sizeof($error))
			{
				$passed = true;
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['ADMIN_TEST'],
					'RESULT'	=> '<strong style="color:green">' . $lang['TESTS_PASSED'] . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
			else
			{
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['ADMIN_TEST'],
					'RESULT'	=> '<strong style="color:red">' . implode('<br />', $error) . '</strong>',

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
					'CONTENT'		=> $this->p_master->input_field($config_key, $vars['type'], $data[$config_key], $options),
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
				$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
			}
		}

		$s_hidden_fields .= ($data['img_imagick']) ? '<input type="hidden" name="img_imagick" value="' . addslashes($data['img_imagick']) . '" />' : '';
		$s_hidden_fields .= '<input type="hidden" name="language" value="' . $data['language'] . '" />';

		foreach ($this->db_config_options as $config_key => $vars)
		{
			if (!is_array($vars))
			{
				continue;
			}
			$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
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
		$data = $this->get_submitted_data();

		if ($data['dbms'] == '')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			$this->p_master->redirect("index.$phpEx?mode=install");
		}

		$s_hidden_fields = ($data['img_imagick']) ? '<input type="hidden" name="img_imagick" value="' . addslashes($data['img_imagick']) . '" />' : '';
		$s_hidden_fields .= '<input type="hidden" name="language" value="' . $data['language'] . '" />';
		$written = false;

		// Create a list of any PHP modules we wish to have loaded
		$available_dbms = get_available_dbms($data['dbms']);

		// Create a lock file to indicate that there is an install in progress
		$fp = @fopen($phpbb_root_path . 'cache/install_lock', 'wb');
		if ($fp === false)
		{
			// We were unable to create the lock file - abort
			$this->p_master->error($lang['UNABLE_WRITE_LOCK'], __LINE__, __FILE__);
		}
		@fclose($fp);

		@chmod($phpbb_root_path . 'cache/install_lock', 0777);

		// Time to convert the data provided into a config file
		$config_data = phpbb_create_config_file_data($data, $available_dbms[$data['dbms']]['DRIVER']);

		// Attempt to write out the config file directly. If it works, this is the easiest way to do it ...
		if ((file_exists($phpbb_root_path . 'config.' . $phpEx) && phpbb_is_writable($phpbb_root_path . 'config.' . $phpEx)) || phpbb_is_writable($phpbb_root_path))
		{
			// Assume it will work ... if nothing goes wrong below
			$written = true;

			if (!($fp = @fopen($phpbb_root_path . 'config.' . $phpEx, 'w')))
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

			if ($written)
			{
				// We may revert back to chmod() if we see problems with users not able to change their config.php file directly
				phpbb_chmod($phpbb_root_path . 'config.' . $phpEx, CHMOD_READ);
			}
		}

		if (isset($_POST['dldone']))
		{
			// Do a basic check to make sure that the file has been uploaded
			// Note that all we check is that the file has _something_ in it
			// We don't compare the contents exactly - if they can't upload
			// a single file correctly, it's likely they will have other problems....
			if (filesize($phpbb_root_path . 'config.' . $phpEx) > 10)
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
			$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
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
		global $lang, $template, $phpEx, $request;

		$this->page_title = $lang['STAGE_ADVANCED'];

		// Obtain any submitted data
		$data = $this->get_submitted_data();

		if ($data['dbms'] == '')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			$this->p_master->redirect("index.$phpEx?mode=install");
		}

		$s_hidden_fields = ($data['img_imagick']) ? '<input type="hidden" name="img_imagick" value="' . addslashes($data['img_imagick']) . '" />' : '';
		$s_hidden_fields .= '<input type="hidden" name="language" value="' . $data['language'] . '" />';

		// HTTP_HOST is having the correct browser url in most cases...
		$server_name = strtolower(htmlspecialchars_decode($request->header('Host', $request->server('SERVER_NAME'))));

		// HTTP HOST can carry a port number...
		if (strpos($server_name, ':') !== false)
		{
			$server_name = substr($server_name, 0, strpos($server_name, ':'));
		}

		$data['email_enable'] = ($data['email_enable'] !== '') ? $data['email_enable'] : true;
		$data['server_name'] = ($data['server_name'] !== '') ? $data['server_name'] : $server_name;
		$data['server_port'] = ($data['server_port'] !== '') ? $data['server_port'] : $request->server('SERVER_PORT', 0);
		$data['server_protocol'] = ($data['server_protocol'] !== '') ? $data['server_protocol'] : ($request->is_secure() ? 'https://' : 'http://');
		$data['cookie_secure'] = ($data['cookie_secure'] !== '') ? $data['cookie_secure'] : $request->is_secure();

		if ($data['script_path'] === '')
		{
			$name = htmlspecialchars_decode($request->server('PHP_SELF'));
			if (!$name)
			{
				$name = htmlspecialchars_decode($request->server('REQUEST_URI'));
			}

			// Replace backslashes and doubled slashes (could happen on some proxy setups)
			$name = str_replace(array('\\', '//'), '/', $name);
			$data['script_path'] = trim(dirname(dirname($name)));
		}

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
				'CONTENT'		=> $this->p_master->input_field($config_key, $vars['type'], $data[$config_key], $options),
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
			$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
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
		global $db, $lang, $template, $phpbb_root_path, $phpEx, $request;

		$this->page_title = $lang['STAGE_CREATE_TABLE'];
		$s_hidden_fields = '';

		// Obtain any submitted data
		$data = $this->get_submitted_data();

		if ($data['dbms'] == '')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			$this->p_master->redirect("index.$phpEx?mode=install");
		}

		// HTTP_HOST is having the correct browser url in most cases...
		$server_name = strtolower(htmlspecialchars_decode($request->header('Host', $request->server('SERVER_NAME'))));
		$referer = strtolower($request->header('Referer'));

		// HTTP HOST can carry a port number...
		if (strpos($server_name, ':') !== false)
		{
			$server_name = substr($server_name, 0, strpos($server_name, ':'));
		}

		$cookie_domain = ($data['server_name'] != '') ? $data['server_name'] : $server_name;

		// Try to come up with the best solution for cookie domain...
		if (strpos($cookie_domain, 'www.') === 0)
		{
			$cookie_domain = str_replace('www.', '.', $cookie_domain);
		}

		// If we get here and the extension isn't loaded it should be safe to just go ahead and load it
		$available_dbms = get_available_dbms($data['dbms']);

		if (!isset($available_dbms[$data['dbms']]))
		{
			// Someone's been silly and tried providing a non-existant dbms
			$this->p_master->redirect("index.$phpEx?mode=install");
		}

		$dbms = $available_dbms[$data['dbms']]['DRIVER'];

		// Instantiate the database
		$db = new $dbms();
		$db->sql_connect($data['dbhost'], $data['dbuser'], htmlspecialchars_decode($data['dbpasswd']), $data['dbname'], $data['dbport'], false, false);

		// NOTE: trigger_error does not work here.
		$db->sql_return_on_error(true);

		// If mysql is chosen, we need to adjust the schema filename slightly to reflect the correct version. ;)
		if ($data['dbms'] == 'mysql')
		{
			if (version_compare($db->sql_server_info(true), '4.1.3', '>='))
			{
				$available_dbms[$data['dbms']]['SCHEMA'] .= '_41';
			}
			else
			{
				$available_dbms[$data['dbms']]['SCHEMA'] .= '_40';
			}
		}

		// Ok we have the db info go ahead and read in the relevant schema
		// and work on building the table
		$dbms_schema = 'schemas/' . $available_dbms[$data['dbms']]['SCHEMA'] . '_schema.sql';

		// How should we treat this schema?
		$delimiter = $available_dbms[$data['dbms']]['DELIM'];

		if (file_exists($dbms_schema))
		{
			$sql_query = @file_get_contents($dbms_schema);
			$sql_query = preg_replace('#phpbb_#i', $data['table_prefix'], $sql_query);
			$sql_query = phpbb_remove_comments($sql_query);
			$sql_query = split_sql_file($sql_query, $delimiter);

			foreach ($sql_query as $sql)
			{
				// Ignore errors when the functions or types already exist
				// to allow installing phpBB twice in the same database with
				// a different prefix
				$db->sql_query($sql);
			}
			unset($sql_query);
		}

		// Ok we have the db info go ahead and work on building the table
		if (file_exists('schemas/schema.json'))
		{
			$db_table_schema = @file_get_contents('schemas/schema.json');
			$db_table_schema = json_decode($db_table_schema, true);
		}
		else
		{
			global $phpbb_root_path, $phpEx, $table_prefix;
			$table_prefix = 'phpbb_';

			if (!defined('CONFIG_TABLE'))
			{
				// We need to include the constants file for the table constants
				// when we generate the schema from the migration files.
				include($phpbb_root_path . 'includes/constants.' . $phpEx);
			}

			$finder = new \phpbb\finder(new \phpbb\filesystem(), $phpbb_root_path, null, $phpEx);
			$classes = $finder->core_path('phpbb/db/migration/data/')
				->get_classes();

			$sqlite_db = new \phpbb\db\driver\sqlite();
			$schema_generator = new \phpbb\db\migration\schema_generator($classes, new \phpbb\config\config(array()), $sqlite_db, new \phpbb\db\tools($sqlite_db, true), $phpbb_root_path, $phpEx, $table_prefix);
			$db_table_schema = $schema_generator->get_schema();
		}

		if (!defined('CONFIG_TABLE'))
		{
			// CONFIG_TABLE is required by sql_create_index() to check the
			// length of index names. However table_prefix is not defined
			// here yet, so we need to create the constant ourselves.
			define('CONFIG_TABLE', $data['table_prefix'] . 'config');
		}

		$db_tools = new \phpbb\db\tools($db);
		foreach ($db_table_schema as $table_name => $table_data)
		{
			$db_tools->sql_create_table(
				$data['table_prefix'] . substr($table_name, 6),
				$table_data
			);
		}

		// Ok tables have been built, let's fill in the basic information
		$sql_query = file_get_contents('schemas/schema_data.sql');

		// Deal with any special comments and characters
		switch ($data['dbms'])
		{
			case 'mssql':
			case 'mssql_odbc':
			case 'mssqlnative':
				$sql_query = preg_replace('#\# MSSQL IDENTITY (phpbb_[a-z_]+) (ON|OFF) \##s', 'SET IDENTITY_INSERT \1 \2;', $sql_query);
			break;

			case 'postgres':
				$sql_query = preg_replace('#\# POSTGRES (BEGIN|COMMIT) \##s', '\1; ', $sql_query);
			break;

			case 'mysql':
			case 'mysqli':
				$sql_query = str_replace('\\', '\\\\', $sql_query);
			break;
		}

		// Change prefix
		$sql_query = preg_replace('# phpbb_([^\s]*) #i', ' ' . $data['table_prefix'] . '\1 ', $sql_query);

		// Change language strings...
		$sql_query = preg_replace_callback('#\{L_([A-Z0-9\-_]*)\}#s', 'adjust_language_keys_callback', $sql_query);

		$sql_query = phpbb_remove_comments($sql_query);
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

		$user_ip = $request->server('REMOTE_ADDR') ? phpbb_ip_normalise($request->server('REMOTE_ADDR')) : '';

		if ($data['script_path'] !== '/')
		{
			// Adjust destination path (no trailing slash)
			if (substr($data['script_path'], -1) == '/')
			{
				$data['script_path'] = substr($data['script_path'], 0, -1);
			}

			$data['script_path'] = str_replace(array('../', './'), '', $data['script_path']);

			if ($data['script_path'][0] != '/')
			{
				$data['script_path'] = '/' . $data['script_path'];
			}
		}

		// Set default config and post data, this applies to all DB's
		$sql_ary = array(
			'INSERT INTO ' . $data['table_prefix'] . "config (config_name, config_value)
				VALUES ('board_startdate', '$current_time')",

			'INSERT INTO ' . $data['table_prefix'] . "config (config_name, config_value)
				VALUES ('default_lang', '" . $db->sql_escape($data['default_lang']) . "')",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['img_imagick']) . "'
				WHERE config_name = 'img_imagick'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['server_name']) . "'
				WHERE config_name = 'server_name'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['server_port']) . "'
				WHERE config_name = 'server_port'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['board_email']) . "'
				WHERE config_name = 'board_email'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['board_email']) . "'
				WHERE config_name = 'board_contact'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($cookie_domain) . "'
				WHERE config_name = 'cookie_domain'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($lang['default_dateformat']) . "'
				WHERE config_name = 'default_dateformat'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['email_enable']) . "'
				WHERE config_name = 'email_enable'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['smtp_delivery']) . "'
				WHERE config_name = 'smtp_delivery'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['smtp_host']) . "'
				WHERE config_name = 'smtp_host'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['smtp_auth']) . "'
				WHERE config_name = 'smtp_auth_method'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['smtp_user']) . "'
				WHERE config_name = 'smtp_username'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['smtp_pass']) . "'
				WHERE config_name = 'smtp_password'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['cookie_secure']) . "'
				WHERE config_name = 'cookie_secure'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['force_server_vars']) . "'
				WHERE config_name = 'force_server_vars'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['script_path']) . "'
				WHERE config_name = 'script_path'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['server_protocol']) . "'
				WHERE config_name = 'server_protocol'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['admin_name']) . "'
				WHERE config_name = 'newest_username'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . md5(mt_rand()) . "'
				WHERE config_name = 'avatar_salt'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . md5(mt_rand()) . "'
				WHERE config_name = 'plupload_salt'",

			'UPDATE ' . $data['table_prefix'] . "users
				SET username = '" . $db->sql_escape($data['admin_name']) . "', user_password='" . $db->sql_escape(md5($data['admin_pass1'])) . "', user_ip = '" . $db->sql_escape($user_ip) . "', user_lang = '" . $db->sql_escape($data['default_lang']) . "', user_email='" . $db->sql_escape($data['board_email']) . "', user_dateformat='" . $db->sql_escape($lang['default_dateformat']) . "', user_email_hash = " . $db->sql_escape(phpbb_email_hash($data['board_email'])) . ", username_clean = '" . $db->sql_escape(utf8_clean_string($data['admin_name'])) . "'
				WHERE username = 'Admin'",

			'UPDATE ' . $data['table_prefix'] . "moderator_cache
				SET username = '" . $db->sql_escape($data['admin_name']) . "'
				WHERE username = 'Admin'",

			'UPDATE ' . $data['table_prefix'] . "forums
				SET forum_last_poster_name = '" . $db->sql_escape($data['admin_name']) . "'
				WHERE forum_last_poster_name = 'Admin'",

			'UPDATE ' . $data['table_prefix'] . "topics
				SET topic_first_poster_name = '" . $db->sql_escape($data['admin_name']) . "', topic_last_poster_name = '" . $db->sql_escape($data['admin_name']) . "'
				WHERE topic_first_poster_name = 'Admin'
					OR topic_last_poster_name = 'Admin'",

			'UPDATE ' . $data['table_prefix'] . "users
				SET user_regdate = $current_time",

			'UPDATE ' . $data['table_prefix'] . "posts
				SET post_time = $current_time, poster_ip = '" . $db->sql_escape($user_ip) . "'",

			'UPDATE ' . $data['table_prefix'] . "topics
				SET topic_time = $current_time, topic_last_post_time = $current_time",

			'UPDATE ' . $data['table_prefix'] . "forums
				SET forum_last_post_time = $current_time",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($db->sql_server_info(true)) . "'
				WHERE config_name = 'dbms_version'",
		);

		if (@extension_loaded('gd'))
		{
			$sql_ary[] = 'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = 'core.captcha.plugins.gd'
				WHERE config_name = 'captcha_plugin'";

			$sql_ary[] = 'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '1'
				WHERE config_name = 'captcha_gd'";
		}

		$ref = substr($referer, strpos($referer, '://') + 3);

		if (!(stripos($ref, $server_name) === 0))
		{
			$sql_ary[] = 'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '0'
				WHERE config_name = 'referer_validation'";
		}

		// We set a (semi-)unique cookie name to bypass login issues related to the cookie name.
		$cookie_name = 'phpbb3_';
		$rand_str = md5(mt_rand());
		$rand_str = str_replace('0', 'z', base_convert($rand_str, 16, 35));
		$rand_str = substr($rand_str, 0, 5);
		$cookie_name .= strtolower($rand_str);

		$sql_ary[] = 'UPDATE ' . $data['table_prefix'] . "config
			SET config_value = '" . $db->sql_escape($cookie_name) . "'
			WHERE config_name = 'cookie_name'";

		foreach ($sql_ary as $sql)
		{
			//$sql = trim(str_replace('|', ';', $sql));

			if (!$db->sql_query($sql))
			{
				$error = $db->sql_error();
				$this->p_master->db_error($error['message'], $sql, __LINE__, __FILE__);
			}
		}

		$submit = $lang['NEXT_STEP'];

		$url = $this->p_master->module_url . "?mode=$mode&amp;sub=final";

		$template->assign_vars(array(
			'BODY'		=> $lang['STAGE_CREATE_TABLE_EXPLAIN'],
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> build_hidden_fields($data),
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Build the search index...
	*/
	function build_search_index($mode, $sub)
	{
		global $db, $lang, $phpbb_root_path, $phpbb_dispatcher, $phpEx, $config, $auth, $user;

		// Obtain any submitted data
		$data = $this->get_submitted_data();
		$table_prefix = $data['table_prefix'];

		// If we get here and the extension isn't loaded it should be safe to just go ahead and load it
		$available_dbms = get_available_dbms($data['dbms']);

		if (!isset($available_dbms[$data['dbms']]))
		{
			// Someone's been silly and tried providing a non-existant dbms
			$this->p_master->redirect("index.$phpEx?mode=install");
		}

		$dbms = $available_dbms[$data['dbms']]['DRIVER'];

		// Instantiate the database
		$db = new $dbms();
		$db->sql_connect($data['dbhost'], $data['dbuser'], htmlspecialchars_decode($data['dbpasswd']), $data['dbname'], $data['dbport'], false, false);

		// NOTE: trigger_error does not work here.
		$db->sql_return_on_error(true);

		include_once($phpbb_root_path . 'includes/constants.' . $phpEx);
		include_once($phpbb_root_path . 'phpbb/search/fulltext_native.' . $phpEx);

		// We need to fill the config to let internal functions correctly work
		$config = new \phpbb\config\db($db, new \phpbb\cache\driver\null, CONFIG_TABLE);
		set_config(null, null, null, $config);
		set_config_count(null, null, null, $config);

		$error = false;
		$search = new \phpbb\search\fulltext_native($error, $phpbb_root_path, $phpEx, $auth, $config, $db, $user, $phpbb_dispatcher);

		$sql = 'SELECT post_id, post_subject, post_text, poster_id, forum_id
			FROM ' . POSTS_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$search->index('post', $row['post_id'], $row['post_text'], $row['post_subject'], $row['poster_id'], $row['forum_id']);
		}
		$db->sql_freeresult($result);
	}

	/**
	* Populate the module tables
	*/
	function add_modules($mode, $sub)
	{
		global $db, $lang, $phpbb_root_path, $phpEx, $phpbb_extension_manager, $config, $phpbb_container;

		// modules require an extension manager
		if (empty($phpbb_extension_manager))
		{
			$phpbb_extension_manager = $phpbb_container->get('ext.manager');
		}

		include_once($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);

		$_module = new acp_modules();
		$module_classes = array('acp', 'mcp', 'ucp');

		// Add categories
		foreach ($module_classes as $module_class)
		{
			$categories = array();

			// Set the module class
			$_module->module_class = $module_class;

			foreach ($this->module_categories[$module_class] as $cat_name => $subs)
			{
				$basename = '';
				// Check if this sub-category has a basename. If it has, use it.
				if (isset($this->module_categories_basenames[$cat_name]))
				{
					$basename = $this->module_categories_basenames[$cat_name];
				}
				$module_data = array(
					'module_basename'	=> $basename,
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
				if ($db->get_sql_error_triggered())
				{
					$error = $db->sql_error($db->get_sql_error_sql());
					$this->p_master->db_error($error['message'], $db->get_sql_error_sql(), __LINE__, __FILE__);
				}

				$categories[$cat_name]['id'] = (int) $module_data['module_id'];
				$categories[$cat_name]['parent_id'] = 0;

				// Create sub-categories...
				if (is_array($subs))
				{
					foreach ($subs as $level2_name)
					{
						$basename = '';
						// Check if this sub-category has a basename. If it has, use it.
						if (isset($this->module_categories_basenames[$level2_name]))
						{
							$basename = $this->module_categories_basenames[$level2_name];
						}
						$module_data = array(
							'module_basename'	=> $basename,
							'module_enabled'	=> 1,
							'module_display'	=> 1,
							'parent_id'			=> (int) $categories[$cat_name]['id'],
							'module_class'		=> $module_class,
							'module_langname'	=> $level2_name,
							'module_mode'		=> '',
							'module_auth'		=> '',
						);

						$_module->update_module_data($module_data, true);

						// Check for last sql error happened
						if ($db->get_sql_error_triggered())
						{
							$error = $db->sql_error($db->get_sql_error_sql());
							$this->p_master->db_error($error['message'], $db->get_sql_error_sql(), __LINE__, __FILE__);
						}

						$categories[$level2_name]['id'] = (int) $module_data['module_id'];
						$categories[$level2_name]['parent_id'] = (int) $categories[$cat_name]['id'];
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
							'module_display'	=> (isset($row['display'])) ? (int) $row['display'] : 1,
							'parent_id'			=> (int) $categories[$cat_name]['id'],
							'module_class'		=> $module_class,
							'module_langname'	=> $row['title'],
							'module_mode'		=> $module_mode,
							'module_auth'		=> $row['auth'],
						);

						$_module->update_module_data($module_data, true);

						// Check for last sql error happened
						if ($db->get_sql_error_triggered())
						{
							$error = $db->sql_error($db->get_sql_error_sql());
							$this->p_master->db_error($error['message'], $db->get_sql_error_sql(), __LINE__, __FILE__);
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
					WHERE module_basename = 'acp_main'
						AND module_class = 'acp'
						AND module_mode = 'main'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_up', 4);

				// Move permissions intro screen module 4 up...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'acp_permissions'
						AND module_class = 'acp'
						AND module_mode = 'intro'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_up', 4);

				// Move manage users screen module 5 up...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'acp_users'
						AND module_class = 'acp'
						AND module_mode = 'overview'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_up', 5);

				// Move extension management module 1 up...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_langname = 'ACP_EXTENSION_MANAGEMENT'
						AND module_class = 'acp'
						AND module_mode = ''
						AND module_basename = ''";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_up', 1);
			}

			if ($module_class == 'mcp')
			{
				// Move pm report details module 3 down...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'mcp_pm_reports'
						AND module_class = 'mcp'
						AND module_mode = 'pm_report_details'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_down', 3);

				// Move closed pm reports module 3 down...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'mcp_pm_reports'
						AND module_class = 'mcp'
						AND module_mode = 'pm_reports_closed'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_down', 3);

				// Move open pm reports module 3 down...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'mcp_pm_reports'
						AND module_class = 'mcp'
						AND module_mode = 'pm_reports'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_down', 3);
			}

			if ($module_class == 'ucp')
			{
				// Move attachment module 4 down...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'ucp_attachments'
						AND module_class = 'ucp'
						AND module_mode = 'attachments'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_down', 4);

				// Move notification options module 4 down...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'ucp_notifications'
						AND module_class = 'ucp'
						AND module_mode = 'notification_options'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_down', 4);

				// Move OAuth module 5 down...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'ucp_auth_link'
						AND module_class = 'ucp'
						AND module_mode = 'auth_link'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_down', 5);
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
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						$module_data = array(
							'module_basename'	=> $row['module_basename'],
							'module_enabled'	=> (int) $row['module_enabled'],
							'module_display'	=> (int) $row['module_display'],
							'parent_id'			=> (int) $row2['module_id'],
							'module_class'		=> $row['module_class'],
							'module_langname'	=> $row['module_langname'],
							'module_mode'		=> $row['module_mode'],
							'module_auth'		=> $row['module_auth'],
						);

						$_module->update_module_data($module_data, true);

						// Check for last sql error happened
						if ($db->get_sql_error_triggered())
						{
							$error = $db->sql_error($db->get_sql_error_sql());
							$this->p_master->db_error($error['message'], $db->get_sql_error_sql(), __LINE__, __FILE__);
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

		if (!$dir)
		{
			$this->error('Unable to access the language directory', __LINE__, __FILE__);
		}

		$installed_languages = array();
		while (($file = readdir($dir)) !== false)
		{
			$path = $phpbb_root_path . 'language/' . $file;

			if ($file == '.' || $file == '..' || is_link($path) || is_file($path) || $file == 'CVS')
			{
				continue;
			}

			if (is_dir($path) && file_exists($path . '/iso.txt'))
			{
				$lang_file = file("$path/iso.txt");

				$lang_pack = array(
					'lang_iso'			=> basename($path),
					'lang_dir'			=> basename($path),
					'lang_english_name'	=> trim(htmlspecialchars($lang_file[0])),
					'lang_local_name'	=> trim(htmlspecialchars($lang_file[1], ENT_COMPAT, 'UTF-8')),
					'lang_author'		=> trim(htmlspecialchars($lang_file[2], ENT_COMPAT, 'UTF-8')),
				);

				$db->sql_query('INSERT INTO ' . LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $lang_pack));

				$installed_languages[] = (int) $db->sql_nextid();
				if ($db->get_sql_error_triggered())
				{
					$error = $db->sql_error($db->get_sql_error_sql());
					$this->p_master->db_error($error['message'], $db->get_sql_error_sql(), __LINE__, __FILE__);
				}
			}
		}
		closedir($dir);

		$sql = 'SELECT *
			FROM ' . PROFILE_FIELDS_TABLE;
		$result = $db->sql_query($sql);

		$profile_fields = array();
		$insert_buffer = new \phpbb\db\sql_insert_buffer($db, PROFILE_LANG_TABLE);
		while ($row = $db->sql_fetchrow($result))
		{
			foreach ($installed_languages as $lang_id)
			{
				$insert_buffer->insert(array(
					'field_id'				=> $row['field_id'],
					'lang_id'				=> $lang_id,
					'lang_name'				=> strtoupper(substr($row['field_name'], 6)),// Remove phpbb_ from field name
					'lang_explain'			=> '',
					'lang_default_value'	=> '',
				));
			}
		}
		$db->sql_freeresult($result);

		$insert_buffer->flush();
	}

	/**
	* Add search robots to the database
	*/
	function add_bots($mode, $sub)
	{
		global $db, $lang, $phpbb_root_path, $phpEx, $config;

		// Obtain any submitted data
		$data = $this->get_submitted_data();

		// We need to fill the config to let internal functions correctly work
		$config = new \phpbb\config\db($db, new \phpbb\cache\driver\null, CONFIG_TABLE);
		set_config(null, null, null, $config);
		set_config_count(null, null, null, $config);

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
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}

		foreach ($this->bot_list as $bot_name => $bot_ary)
		{
			$user_row = array(
				'user_type'				=> USER_IGNORE,
				'group_id'				=> $group_id,
				'username'				=> $bot_name,
				'user_regdate'			=> time(),
				'user_password'			=> '',
				'user_colour'			=> '9E8DA7',
				'user_email'			=> '',
				'user_lang'				=> $data['default_lang'],
				'user_style'			=> 1,
				'user_timezone'			=> 'UTC',
				'user_dateformat'		=> $lang['default_dateformat'],
				'user_allow_massemail'	=> 0,
				'user_allow_pm'			=> 0,
			);

			$user_id = user_add($user_row);

			if (!$user_id)
			{
				// If we can't insert this user then continue to the next one to avoid inconsistent data
				$this->p_master->db_error('Unable to insert bot into users table', $db->get_sql_error_sql(), __LINE__, __FILE__, true);
				continue;
			}

			$sql = 'INSERT INTO ' . BOTS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'bot_active'	=> 1,
				'bot_name'		=> (string) $bot_name,
				'user_id'		=> (int) $user_id,
				'bot_agent'		=> (string) $bot_ary[0],
				'bot_ip'		=> (string) $bot_ary[1],
			));

			$db->sql_query($sql);
		}
	}

	/**
	* Sends an email to the board administrator with their password and some useful links
	*/
	function email_admin($mode, $sub)
	{
		global $auth, $config, $db, $lang, $template, $user, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$this->page_title = $lang['STAGE_FINAL'];

		// Obtain any submitted data
		$data = $this->get_submitted_data();

		// We need to fill the config to let internal functions correctly work
		$config = new \phpbb\config\db($db, new \phpbb\cache\driver\null, CONFIG_TABLE);
		set_config(null, null, null, $config);
		set_config_count(null, null, null, $config);

		$user->session_begin();
		$auth->login($data['admin_name'], $data['admin_pass1'], false, true, true);

		// OK, Now that we've reached this point we can be confident that everything
		// is installed and working......I hope :)
		// So it's time to send an email to the administrator confirming the details
		// they entered

		if ($config['email_enable'])
		{
			include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

			$messenger = new messenger(false);

			$messenger->template('installed', $data['language']);

			$messenger->to($data['board_email'], $data['admin_name']);

			$messenger->anti_abuse_headers($config, $user);

			$messenger->assign_vars(array(
				'USERNAME'		=> htmlspecialchars_decode($data['admin_name']),
				'PASSWORD'		=> htmlspecialchars_decode($data['admin_pass1']))
			);

			$messenger->send(NOTIFY_EMAIL);
		}

		// And finally, add a note to the log
		add_log('admin', 'LOG_INSTALL_INSTALLED', $config['version']);

		$template->assign_vars(array(
			'TITLE'		=> $lang['INSTALL_CONGRATS'],
			'BODY'		=> sprintf($lang['INSTALL_CONGRATS_EXPLAIN'], $config['version'], append_sid($phpbb_root_path . 'install/index.' . $phpEx, 'mode=convert&amp;language=' . $data['language']), '../docs/README.html'),
			'L_SUBMIT'	=> $lang['INSTALL_LOGIN'],
			'U_ACTION'	=> append_sid($phpbb_admin_path . 'index.' . $phpEx, 'i=send_statistics&amp;mode=send_statistics'),
		));
	}

	/**
	* Check if the avatar directory is writable and disable avatars
	* if it isn't writable.
	*/
	function disable_avatars_if_unwritable()
	{
		global $phpbb_root_path;

		if (!phpbb_is_writable($phpbb_root_path . 'images/avatars/upload/'))
		{
			set_config('allow_avatar', 0);
			set_config('allow_avatar_upload', 0);
		}
	}

	/**
	* Populate migrations for the installation
	*
	* This "installs" all migrations from (root path)/phpbb/db/migrations/data.
	* "installs" means it adds all migrations to the migrations table, but does not
	* perform any of the actions in the migrations.
	*
	* @param \phpbb\extension\manager $extension_manager
	* @param \phpbb\db\migrator $migrator
	*/
	function populate_migrations($extension_manager, $migrator)
	{
		$finder = $extension_manager->get_finder();

		$migrations = $finder
			->core_path('phpbb/db/migration/data/')
			->get_classes();
		$migrator->populate_migrations($migrations);
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
	* Get submitted data
	*/
	function get_submitted_data()
	{
		return array(
			'language'		=> basename(request_var('language', '')),
			'dbms'			=> request_var('dbms', ''),
			'dbhost'		=> request_var('dbhost', '', true),
			'dbport'		=> request_var('dbport', ''),
			'dbuser'		=> request_var('dbuser', ''),
			'dbpasswd'		=> request_var('dbpasswd', '', true),
			'dbname'		=> request_var('dbname', ''),
			'table_prefix'	=> request_var('table_prefix', ''),
			'default_lang'	=> basename(request_var('default_lang', '')),
			'admin_name'	=> utf8_normalize_nfc(request_var('admin_name', '', true)),
			'admin_pass1'	=> request_var('admin_pass1', '', true),
			'admin_pass2'	=> request_var('admin_pass2', '', true),
			'board_email'	=> strtolower(request_var('board_email', '')),
			'img_imagick'	=> request_var('img_imagick', ''),
			'ftp_path'		=> request_var('ftp_path', ''),
			'ftp_user'		=> request_var('ftp_user', ''),
			'ftp_pass'		=> request_var('ftp_pass', ''),
			'email_enable'	=> request_var('email_enable', ''),
			'smtp_delivery'	=> request_var('smtp_delivery', ''),
			'smtp_host'		=> request_var('smtp_host', ''),
			'smtp_auth'		=> request_var('smtp_auth', ''),
			'smtp_user'		=> request_var('smtp_user', ''),
			'smtp_pass'		=> request_var('smtp_pass', ''),
			'cookie_secure'	=> request_var('cookie_secure', ''),
			'force_server_vars'	=> request_var('force_server_vars', ''),
			'server_protocol'	=> request_var('server_protocol', ''),
			'server_name'	=> request_var('server_name', ''),
			'server_port'	=> request_var('server_port', ''),
			'script_path'	=> request_var('script_path', ''),
		);
	}

	/**
	* The information below will be used to build the input fields presented to the user
	*/
	var $db_config_options = array(
		'legend1'				=> 'DB_CONFIG',
		'dbms'					=> array('lang' => 'DBMS',			'type' => 'select', 'options' => 'dbms_select(\'{VALUE}\')', 'explain' => false),
		'dbhost'				=> array('lang' => 'DB_HOST',		'type' => 'text:25:100', 'explain' => true),
		'dbport'				=> array('lang' => 'DB_PORT',		'type' => 'text:25:100', 'explain' => true),
		'dbname'				=> array('lang' => 'DB_NAME',		'type' => 'text:25:100', 'explain' => false),
		'dbuser'				=> array('lang' => 'DB_USERNAME',	'type' => 'text:25:100', 'explain' => false),
		'dbpasswd'				=> array('lang' => 'DB_PASSWORD',	'type' => 'password:25:100', 'explain' => false),
		'table_prefix'			=> array('lang' => 'TABLE_PREFIX',	'type' => 'text:25:100', 'explain' => true),
	);
	var $admin_config_options = array(
		'legend1'				=> 'ADMIN_CONFIG',
		'default_lang'			=> array('lang' => 'DEFAULT_LANG',				'type' => 'select', 'options' => '$this->module->inst_language_select(\'{VALUE}\')', 'explain' => false),
		'admin_name'			=> array('lang' => 'ADMIN_USERNAME',			'type' => 'text:25:100', 'explain' => true),
		'admin_pass1'			=> array('lang' => 'ADMIN_PASSWORD',			'type' => 'password:25:100', 'explain' => true),
		'admin_pass2'			=> array('lang' => 'ADMIN_PASSWORD_CONFIRM',	'type' => 'password:25:100', 'explain' => false),
		'board_email'			=> array('lang' => 'CONTACT_EMAIL',				'type' => 'email:25:100', 'explain' => false),
	);
	var $advanced_config_options = array(
		'legend1'				=> 'ACP_EMAIL_SETTINGS',
		'email_enable'			=> array('lang' => 'ENABLE_EMAIL',		'type' => 'radio:enabled_disabled', 'explain' => true),
		'smtp_delivery'			=> array('lang' => 'USE_SMTP',			'type' => 'radio:yes_no', 'explain' => true),
		'smtp_host'				=> array('lang' => 'SMTP_SERVER',		'type' => 'text:25:50', 'explain' => false),
		'smtp_auth'				=> array('lang' => 'SMTP_AUTH_METHOD',	'type' => 'select', 'options' => '$this->module->mail_auth_select(\'{VALUE}\')', 'explain' => true),
		'smtp_user'				=> array('lang' => 'SMTP_USERNAME',		'type' => 'text:25:255', 'explain' => true, 'options' => array('autocomplete' => 'off')),
		'smtp_pass'				=> array('lang' => 'SMTP_PASSWORD',		'type' => 'password:25:255', 'explain' => true, 'options' => array('autocomplete' => 'off')),

		'legend2'				=> 'SERVER_URL_SETTINGS',
		'cookie_secure'			=> array('lang' => 'COOKIE_SECURE',		'type' => 'radio:enabled_disabled', 'explain' => true),
		'force_server_vars'		=> array('lang' => 'FORCE_SERVER_VARS',	'type' => 'radio:yes_no', 'explain' => true),
		'server_protocol'		=> array('lang' => 'SERVER_PROTOCOL',	'type' => 'text:10:10', 'explain' => true),
		'server_name'			=> array('lang' => 'SERVER_NAME',		'type' => 'text:40:255', 'explain' => true),
		'server_port'			=> array('lang' => 'SERVER_PORT',		'type' => 'text:5:5', 'explain' => true),
		'script_path'			=> array('lang' => 'SCRIPT_PATH',		'type' => 'text::255', 'explain' => true),
	);

	/**
	* Specific PHP modules we may require for certain optional or extended features
	*/
	var $php_dlls_other = array('zlib', 'ftp', 'gd', 'xml');

	/**
	* A list of the web-crawlers/bots we recognise by default
	*
	* Candidates but not included:
	* 'Accoona [Bot]'				'Accoona-AI-Agent/'
	* 'ASPseek [Crawler]'			'ASPseek/'
	* 'Boitho [Crawler]'			'boitho.com-dc/'
	* 'Bunnybot [Bot]'				'powered by www.buncat.de'
	* 'Cosmix [Bot]'				'cfetch/'
	* 'Crawler Search [Crawler]'	'.Crawler-Search.de'
	* 'Findexa [Crawler]'			'Findexa Crawler ('
	* 'GBSpider [Spider]'			'GBSpider v'
	* 'genie [Bot]'					'genieBot ('
	* 'Hogsearch [Bot]'				'oegp v. 1.3.0'
	* 'Insuranco [Bot]'				'InsurancoBot'
	* 'IRLbot [Bot]'				'http://irl.cs.tamu.edu/crawler'
	* 'ISC Systems [Bot]'			'ISC Systems iRc Search'
	* 'Jyxobot [Bot]'				'Jyxobot/'
	* 'Kraehe [Metasuche]'			'-DIE-KRAEHE- META-SEARCH-ENGINE/'
	* 'LinkWalker'					'LinkWalker'
	* 'MMSBot [Bot]'				'http://www.mmsweb.at/bot.html'
	* 'Naver [Bot]'					'nhnbot@naver.com)'
	* 'NetResearchServer'			'NetResearchServer/'
	* 'Nimble [Crawler]'			'NimbleCrawler'
	* 'Ocelli [Bot]'				'Ocelli/'
	* 'Onsearch [Bot]'				'onCHECK-Robot'
	* 'Orange [Spider]'				'OrangeSpider'
	* 'Sproose [Bot]'				'http://www.sproose.com/bot'
	* 'Susie [Sync]'				'!Susie (http://www.sync2it.com/susie)'
	* 'Tbot [Bot]'					'Tbot/'
	* 'Thumbshots [Capture]'		'thumbshots-de-Bot'
	* 'Vagabondo [Crawler]'			'http://webagent.wise-guys.nl/'
	* 'Walhello [Bot]'				'appie 1.1 (www.walhello.com)'
	* 'WissenOnline [Bot]'			'WissenOnline-Bot'
	* 'WWWeasel [Bot]'				'WWWeasel Robot v'
	* 'Xaldon [Spider]'				'Xaldon WebSpider'
	*/
	var $bot_list = array(
		'AdsBot [Google]'			=> array('AdsBot-Google', ''),
		'Alexa [Bot]'				=> array('ia_archiver', ''),
		'Alta Vista [Bot]'			=> array('Scooter/', ''),
		'Ask Jeeves [Bot]'			=> array('Ask Jeeves', ''),
		'Baidu [Spider]'			=> array('Baiduspider', ''),
		'Bing [Bot]'				=> array('bingbot/', ''),
		'Exabot [Bot]'				=> array('Exabot', ''),
		'FAST Enterprise [Crawler]'	=> array('FAST Enterprise Crawler', ''),
		'FAST WebCrawler [Crawler]'	=> array('FAST-WebCrawler/', ''),
		'Francis [Bot]'				=> array('http://www.neomo.de/', ''),
		'Gigabot [Bot]'				=> array('Gigabot/', ''),
		'Google Adsense [Bot]'		=> array('Mediapartners-Google', ''),
		'Google Desktop'			=> array('Google Desktop', ''),
		'Google Feedfetcher'		=> array('Feedfetcher-Google', ''),
		'Google [Bot]'				=> array('Googlebot', ''),
		'Heise IT-Markt [Crawler]'	=> array('heise-IT-Markt-Crawler', ''),
		'Heritrix [Crawler]'		=> array('heritrix/1.', ''),
		'IBM Research [Bot]'		=> array('ibm.com/cs/crawler', ''),
		'ICCrawler - ICjobs'		=> array('ICCrawler - ICjobs', ''),
		'ichiro [Crawler]'			=> array('ichiro/', ''),
		'Majestic-12 [Bot]'			=> array('MJ12bot/', ''),
		'Metager [Bot]'				=> array('MetagerBot/', ''),
		'MSN NewsBlogs'				=> array('msnbot-NewsBlogs/', ''),
		'MSN [Bot]'					=> array('msnbot/', ''),
		'MSNbot Media'				=> array('msnbot-media/', ''),
		'Nutch [Bot]'				=> array('http://lucene.apache.org/nutch/', ''),
		'Online link [Validator]'	=> array('online link validator', ''),
		'psbot [Picsearch]'			=> array('psbot/0', ''),
		'Sensis [Crawler]'			=> array('Sensis Web Crawler', ''),
		'SEO Crawler'				=> array('SEO search Crawler/', ''),
		'Seoma [Crawler]'			=> array('Seoma [SEO Crawler]', ''),
		'SEOSearch [Crawler]'		=> array('SEOsearch/', ''),
		'Snappy [Bot]'				=> array('Snappy/1.1 ( http://www.urltrends.com/ )', ''),
		'Steeler [Crawler]'			=> array('http://www.tkl.iis.u-tokyo.ac.jp/~crawler/', ''),
		'Telekom [Bot]'				=> array('crawleradmin.t-info@telekom.de', ''),
		'TurnitinBot [Bot]'			=> array('TurnitinBot/', ''),
		'Voyager [Bot]'				=> array('voyager/', ''),
		'W3 [Sitesearch]'			=> array('W3 SiteSearch Crawler', ''),
		'W3C [Linkcheck]'			=> array('W3C-checklink/', ''),
		'W3C [Validator]'			=> array('W3C_Validator', ''),
		'YaCy [Bot]'				=> array('yacybot', ''),
		'Yahoo MMCrawler [Bot]'		=> array('Yahoo-MMCrawler/', ''),
		'Yahoo Slurp [Bot]'			=> array('Yahoo! DE Slurp', ''),
		'Yahoo [Bot]'				=> array('Yahoo! Slurp', ''),
		'YahooSeeker [Bot]'			=> array('YahooSeeker/', ''),
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
			'ACP_CAT_CUSTOMISE'		=> array(
				'ACP_STYLE_MANAGEMENT',
				'ACP_EXTENSION_MANAGEMENT',
				'ACP_LANGUAGE',
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
			'UCP_ZEBRA'			=> null,
		),
	);
	var $module_categories_basenames = array(
		'UCP_PM' => 'ucp_pm',
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
				'ACP_FORUM_PERMISSIONS_COPY',
				'ACP_FORUM_MODERATORS',
				'ACP_USERS_FORUM_PERMISSIONS',
				'ACP_GROUPS_FORUM_PERMISSIONS',
			),
		),
	);
}
