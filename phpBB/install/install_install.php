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
if (!defined('IN_INSTALL'))
{
	// Someone has tried to access the file direct. This is not a good idea, so exit
	exit;
}

if (!empty($setmodules))
{
	// If phpBB is already installed we do not include this module
	if (@file_exists(PHPBB_ROOT_PATH . 'config.' . PHP_EXT) && !file_exists(PHPBB_ROOT_PATH . 'cache/install_lock'))
	{
		include PHPBB_ROOT_PATH . 'config.' . PHP_EXT;

		if (phpbb::$base_config['installed'])
		{
			return;
		}
	}

	$module[] = array(
		'module_type'		=> 'install',
		'module_title'		=> 'INSTALL',
		'module_filename'	=> substr(basename(__FILE__), 0, -strlen(PHP_EXT)-1),
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
		phpbb::$template->assign_vars(array(
			'S_SUB'			=> $sub,
			'S_LANG_SELECT'	=> '<select id="language" name="language">' . $this->p_master->inst_language_select(phpbb::$user->lang_name) . '</select>',
		));

		switch ($sub)
		{
			case 'intro':
				$this->page_title = 'SUB_INTRO';

				phpbb::$template->assign_vars(array(
					'U_ACTION'		=> phpbb::$url->append_sid($this->p_master->module_url, "mode=$mode&amp;sub=requirements&amp;language=" . phpbb::$user->lang_name),
				));

			break;

			case 'requirements':
				$this->page_title = 'STAGE_REQUIREMENTS';
				$this->check_server_requirements($mode, $sub);
			break;

			case 'database':
				$this->page_title = 'STAGE_DATABASE';

				// Obtain any submitted data
				$data = $this->get_submitted_data();

				$this->obtain_database_settings($mode, $sub, $data);
			break;

			case 'administrator':
				$this->page_title = 'STAGE_ADMINISTRATOR';

				phpbb::$user->add_lang('acp/board');

				// Obtain any submitted data
				$data = $this->get_submitted_data();

				$this->obtain_admin_settings($mode, $sub, $data);

			break;

			case 'config_file':
				$this->page_title = 'STAGE_CONFIG_FILE';

				// Obtain any submitted data
				$data = $this->get_submitted_data();

				$this->create_config_file($mode, $sub, $data);
			break;

			case 'advanced':
				$this->page_title = 'STAGE_ADVANCED';

				phpbb::$user->add_lang('acp/common');
				phpbb::$user->add_lang('acp/board');

				// Obtain any submitted data
				$data = $this->get_submitted_data();

				$this->obtain_advanced_settings($mode, $sub, $data);
			break;

			case 'create_table':
				$this->page_title = 'STAGE_CREATE_TABLE';

				// Obtain any submitted data
				$data = $this->get_submitted_data();

				$this->load_schema($mode, $sub, $data);
			break;

			case 'final':
				$this->page_title = 'STAGE_FINAL';

				include PHPBB_ROOT_PATH . 'common.' . PHP_EXT;

				phpbb::$acm->purge();

				$this->build_search_index($mode, $sub);
				$this->add_modules($mode, $sub);
				$this->add_language($mode, $sub);
				$this->add_bots($mode, $sub);
				$this->email_admin($mode, $sub);

				// Remove the lock file
				@unlink(PHPBB_ROOT_PATH . 'cache/install_lock');

			break;
		}

		$this->tpl_name = 'install/install';
	}

	function build_form($data, $form_array)
	{
		foreach ($form_array as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				phpbb::$template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> phpbb::$user->lang[$vars],
				));

				continue;
			}

			$options = isset($vars['options']) ? $vars['options'] : '';

			phpbb::$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> phpbb::$user->lang[$vars['lang']],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> ($vars['explain']) ? phpbb::$user->lang[$vars['lang'] . '_EXPLAIN'] : '',
				'CONTENT'		=> $this->p_master->input_field($config_key, $vars['type'], $data[$config_key], $options),
			));
		}
	}

	function return_hidden_fields()
	{
		$args = func_get_args();
		$data = array_shift($args);

		$s_hidden_fields = '';

		foreach ($args as $argument)
		{
			if (!is_array($argument))
			{
				continue;
			}

			foreach ($argument as $config_key => $vars)
			{
				if (!is_array($vars))
				{
					continue;
				}

				$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
			}
		}

		return $s_hidden_fields;
	}

	/**
	* Checks that the server we are installing on meets the requirements for running phpBB
	*/
	function check_server_requirements($mode, $sub)
	{
		$passed = array('php' => false, 'db' => false, 'files' => false, 'pcre' => false, 'imagesize' => false,);

		// Test for basic PHP settings
		phpbb::$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> phpbb::$user->lang['PHP_SETTINGS'],
			'LEGEND_EXPLAIN'	=> phpbb::$user->lang['PHP_SETTINGS_EXPLAIN'],
		));

		// Test the minimum PHP version
		if (version_compare(PHP_VERSION, '5.2.0') < 0)
		{
			$result = '<strong style="color:red">' . phpbb::$user->lang['NO'] . '</strong>';
		}
		else
		{
			$passed['php'] = true;

			// We also give feedback on whether we're running in safe mode
			$result = '<strong style="color:green">' . phpbb::$user->lang['YES'];
			if (@ini_get('safe_mode') == '1' || strtolower(@ini_get('safe_mode')) == 'on')
			{
				$result .= ', ' . phpbb::$user->lang['PHP_SAFE_MODE'];
			}
			$result .= '</strong>';
		}

		phpbb::$template->assign_block_vars('checks', array(
			'TITLE'			=> phpbb::$user->lang['PHP_VERSION_REQD'],
			'RESULT'		=> $result,
		));

		// Check for register_globals being enabled
		if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on')
		{
			$result = '<strong style="color:red">' . phpbb::$user->lang['NO'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:green">' . phpbb::$user->lang['YES'] . '</strong>';
		}

		phpbb::$template->assign_block_vars('checks', array(
			'TITLE'			=> phpbb::$user->lang['PHP_REGISTER_GLOBALS'],
			'TITLE_EXPLAIN'	=> phpbb::$user->lang['PHP_REGISTER_GLOBALS_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
		));

		// Check for url_fopen
		if (@ini_get('allow_url_fopen') == '1' || strtolower(@ini_get('allow_url_fopen')) == 'on')
		{
			$result = '<strong style="color:green">' . phpbb::$user->lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . phpbb::$user->lang['NO'] . '</strong>';
		}

		phpbb::$template->assign_block_vars('checks', array(
			'TITLE'			=> phpbb::$user->lang['PHP_URL_FOPEN_SUPPORT'],
			'TITLE_EXPLAIN'	=> phpbb::$user->lang['PHP_URL_FOPEN_SUPPORT_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
		));

		// Check for getimagesize
		if (@function_exists('getimagesize'))
		{
			$passed['imagesize'] = true;
			$result = '<strong style="color:green">' . phpbb::$user->lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . phpbb::$user->lang['NO'] . '</strong>';
		}

		phpbb::$template->assign_block_vars('checks', array(
			'TITLE'			=> phpbb::$user->lang['PHP_GETIMAGESIZE_SUPPORT'],
			'TITLE_EXPLAIN'	=> phpbb::$user->lang['PHP_GETIMAGESIZE_SUPPORT_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
		));

		// Check for PCRE UTF-8 support
		if (@preg_match('//u', ''))
		{
			$passed['pcre'] = true;
			$result = '<strong style="color:green">' . phpbb::$user->lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . phpbb::$user->lang['NO'] . '</strong>';
		}

		phpbb::$template->assign_block_vars('checks', array(
			'TITLE'			=> phpbb::$user->lang['PCRE_UTF_SUPPORT'],
			'TITLE_EXPLAIN'	=> phpbb::$user->lang['PCRE_UTF_SUPPORT_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
		));

		// Check for PCRE unicode property support
		if (@preg_match('/\p{Ll}/u', 'a'))
		{
			$passed['pcre'] = true;
			$result = '<strong style="color:green">' . phpbb::$user->lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . phpbb::$user->lang['NO'] . '</strong>';
		}

		phpbb::$template->assign_block_vars('checks', array(
			'TITLE'			=> phpbb::$user->lang['PCRE_UNI_PROP_SUPPORT'],
			'TITLE_EXPLAIN'	=> phpbb::$user->lang['PCRE_UNI_PROP_SUPPORT_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
		));

		// MBString passes always. If the extension is loaded it only can interfere with our functions, set to false then
		$passed['mbstring'] = true;
		if (@extension_loaded('mbstring'))
		{
			// Test for available database modules
			phpbb::$template->assign_block_vars('checks', array(
				'S_LEGEND'			=> true,
				'LEGEND'			=> phpbb::$user->lang['MBSTRING_CHECK'],
				'LEGEND_EXPLAIN'	=> phpbb::$user->lang['MBSTRING_CHECK_EXPLAIN'],
			));

			$checks = array(
				array('func_overload', '&', MB_OVERLOAD_MAIL|MB_OVERLOAD_STRING),
				array('encoding_translation', '!=', 0),
				array('http_input', '!=', 'pass'),
				array('http_output', '!=', 'pass')
			);

			foreach ($checks as $mb_checks)
			{
				$ini_val = @ini_get('mbstring.' . $mb_checks[0]);
				switch ($mb_checks[1])
				{
					case '&':
						if (intval($ini_val) & $mb_checks[2])
						{
							$result = '<strong style="color:red">' . phpbb::$user->lang['NO'] . '</strong>';
							$passed['mbstring'] = false;
						}
						else
						{
							$result = '<strong style="color:green">' . phpbb::$user->lang['YES'] . '</strong>';
						}
					break;

					case '!=':
						if ($ini_val != $mb_checks[2])
						{
							$result = '<strong style="color:red">' . phpbb::$user->lang['NO'] . '</strong>';
							$passed['mbstring'] = false;
						}
						else
						{
							$result = '<strong style="color:green">' . phpbb::$user->lang['YES'] . '</strong>';
						}
					break;
				}

				phpbb::$template->assign_block_vars('checks', array(
					'TITLE'			=> phpbb::$user->lang['MBSTRING_' . strtoupper($mb_checks[0])],
					'TITLE_EXPLAIN'	=> phpbb::$user->lang['MBSTRING_' . strtoupper($mb_checks[0]) . '_EXPLAIN'],
					'RESULT'		=> $result,

					'S_EXPLAIN'		=> true,
				));
			}
		}

		// Test for available database modules
		phpbb::$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> phpbb::$user->lang['PHP_SUPPORTED_DB'],
			'LEGEND_EXPLAIN'	=> phpbb::$user->lang['PHP_SUPPORTED_DB_EXPLAIN'],
		));

		$available_dbms = get_available_dbms(false, true);
		$passed['db'] = $available_dbms['ANY_DB_SUPPORT'];
		unset($available_dbms['ANY_DB_SUPPORT']);

		foreach ($available_dbms as $db_name => $db_ary)
		{
			if (!$db_ary['AVAILABLE'])
			{
				phpbb::$template->assign_block_vars('checks', array(
					'TITLE'		=> phpbb::$user->lang['DLL_' . strtoupper($db_name)],
					'RESULT'	=> '<span style="color:red">' . phpbb::$user->lang['UNAVAILABLE'] . '</span>',
				));
			}
			else
			{
				phpbb::$template->assign_block_vars('checks', array(
					'TITLE'		=> phpbb::$user->lang['DLL_' . strtoupper($db_name)],
					'RESULT'	=> '<strong style="color:green">' . phpbb::$user->lang['AVAILABLE'] . '</strong>',
				));
			}
		}

		// Test for other modules
		phpbb::$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> phpbb::$user->lang['PHP_OPTIONAL_MODULE'],
			'LEGEND_EXPLAIN'	=> phpbb::$user->lang['PHP_OPTIONAL_MODULE_EXPLAIN'],
		));

		foreach ($this->php_dlls_other as $dll)
		{
			if (!@extension_loaded($dll))
			{
				if (!can_load_dll($dll))
				{
					phpbb::$template->assign_block_vars('checks', array(
						'TITLE'		=> phpbb::$user->lang['DLL_' . strtoupper($dll)],
						'RESULT'	=> '<strong style="color:red">' . phpbb::$user->lang['UNAVAILABLE'] . '</strong>',
					));
					continue;
				}
			}

			phpbb::$template->assign_block_vars('checks', array(
				'TITLE'		=> phpbb::$user->lang['DLL_' . strtoupper($dll)],
				'RESULT'	=> '<strong style="color:green">' . phpbb::$user->lang['AVAILABLE'] . '</strong>',
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

		phpbb::$template->assign_block_vars('checks', array(
			'TITLE'		=> phpbb::$user->lang['APP_MAGICK'],
			'RESULT'	=> ($img_imagick) ? '<strong style="color:green">' . phpbb::$user->lang['AVAILABLE'] . ', ' . htmlspecialchars($img_imagick) . '</strong>' : '<strong style="color:blue">' . phpbb::$user->lang['NO_LOCATION'] . '</strong>',
		));

		// Check permissions on files/directories we need access to
		phpbb::$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> phpbb::$user->lang['FILES_REQUIRED'],
			'LEGEND_EXPLAIN'	=> phpbb::$user->lang['FILES_REQUIRED_EXPLAIN'],
		));

		$directories = array('cache/', 'files/', 'store/');

		umask(0);

		$passed['files'] = true;
		foreach ($directories as $dir)
		{
			$exists = $write = false;

			// Try to create the directory if it does not exist
			if (!file_exists(PHPBB_ROOT_PATH . $dir))
			{
				@mkdir(PHPBB_ROOT_PATH . $dir, 0777);
				phpbb::$system->chmod(PHPBB_ROOT_PATH . $dir, phpbb::CHMOD_READ | phpbb::CHMOD_WRITE);
			}

			// Now really check
			if (file_exists(PHPBB_ROOT_PATH . $dir) && is_dir(PHPBB_ROOT_PATH . $dir))
			{
				phpbb::$system->chmod(PHPBB_ROOT_PATH . $dir, phpbb::CHMOD_READ | phpbb::CHMOD_WRITE);
				$exists = true;
			}

			// Now check if it is writable by storing a simple file
			$fp = @fopen(PHPBB_ROOT_PATH . $dir . 'test_lock', 'wb');
			if ($fp !== false)
			{
				$write = true;
			}
			@fclose($fp);

			@unlink(PHPBB_ROOT_PATH . $dir . 'test_lock');

			$passed['files'] = ($exists && $write && $passed['files']) ? true : false;

			$exists = ($exists) ? '<strong style="color:green">' . phpbb::$user->lang['FOUND'] . '</strong>' : '<strong style="color:red">' . phpbb::$user->lang['NOT_FOUND'] . '</strong>';
			$write = ($write) ? ', <strong style="color:green">' . phpbb::$user->lang['WRITABLE'] . '</strong>' : (($exists) ? ', <strong style="color:red">' . phpbb::$user->lang['UNWRITABLE'] . '</strong>' : '');

			phpbb::$template->assign_block_vars('checks', array(
				'TITLE'		=> $dir,
				'RESULT'	=> $exists . $write,
			));
		}

		// Check permissions on files/directories it would be useful access to
		phpbb::$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> phpbb::$user->lang['FILES_OPTIONAL'],
			'LEGEND_EXPLAIN'	=> phpbb::$user->lang['FILES_OPTIONAL_EXPLAIN'],
		));

		$directories = array('config.' . PHP_EXT, 'images/avatars/upload/');

		foreach ($directories as $dir)
		{
			$write = $exists = true;
			if (file_exists(PHPBB_ROOT_PATH . $dir))
			{
				if (!@is_writable(PHPBB_ROOT_PATH . $dir))
				{
					$write = false;
				}
			}
			else
			{
				$write = $exists = false;
			}

			$exists_str = ($exists) ? '<strong style="color:green">' . phpbb::$user->lang['FOUND'] . '</strong>' : '<strong style="color:red">' . phpbb::$user->lang['NOT_FOUND'] . '</strong>';
			$write_str = ($write) ? ', <strong style="color:green">' . phpbb::$user->lang['WRITABLE'] . '</strong>' : (($exists) ? ', <strong style="color:red">' . phpbb::$user->lang['UNWRITABLE'] . '</strong>' : '');

			phpbb::$template->assign_block_vars('checks', array(
				'TITLE'		=> $dir,
				'RESULT'	=> $exists_str . $write_str,
			));
		}

		// And finally where do we want to go next (well today is taken isn't it :P)
		$s_hidden_fields = ($img_imagick) ? '<input type="hidden" name="img_imagick" value="' . htmlspecialchars($img_imagick) . '" />' : '';

		$url = (!in_array(false, $passed)) ? phpbb::$url->append_sid($this->p_master->module_url, "mode=$mode&amp;sub=database&amp;language=" . phpbb::$user->lang_name) : phpbb::$url->build_url();

		phpbb::$template->assign_vars(array(
			'S_FAILED'		=> in_array(false, $passed),
			'S_HIDDEN'		=> $s_hidden_fields,
			'U_ACTION_TEST'	=> phpbb::$url->build_url(),
			'U_ACTION'		=> $url,
		));
	}

	/**
	* Obtain the information required to connect to the database
	*/
	function obtain_database_settings($mode, $sub, &$data)
	{
		$connect_test = false;
		$error = array();

		$available_dbms = get_available_dbms();

		// Has the user opted to test the connection?
		if (phpbb_request::is_set_post('testdb'))
		{
			if (!isset($available_dbms[$data['dbms']]))
			{
				$error[] = phpbb::$user->lang['INST_ERR_NO_DB'];
				$connect_test = false;
			}
			else
			{
				$connect_test = connect_check_db($available_dbms[$data['dbms']], $data['table_prefix'], $data['dbhost'], $data['dbuser'], htmlspecialchars_decode($data['dbpasswd']), $data['dbname'], $data['dbport'], $error);
			}

			if ($connect_test)
			{
				phpbb::$template->assign_vars(array(
					'S_CONNECT_TEST'	=> true,
					'S_SUCCESS'			=> true,
				));
			}
			else
			{
				phpbb::$template->assign_vars(array(
					'S_CONNECT_TEST'	=> true,
					'ERROR'				=> implode('<br />', $error),
				));
			}
		}

		if (!$connect_test)
		{
			// And now for the main part of this page
			$data['table_prefix'] = (!empty($data['table_prefix']) ? $data['table_prefix'] : 'phpbb_');
			$this->build_form($data, $this->db_config_options);
		}

		// And finally where do we want to go next (well today is taken isn't it :P)
		$s_hidden_fields = $this->return_hidden_fields($data, $this->common_config_options);

		if ($connect_test)
		{
			$s_hidden_fields .= $this->return_hidden_fields($data, $this->db_config_options);
		}

		$url = ($connect_test) ? phpbb::$url->append_sid($this->p_master->module_url, "mode=$mode&amp;sub=administrator") : phpbb::$url->build_url();
		$s_hidden_fields .= ($connect_test) ? '' : '<input type="hidden" name="testdb" value="true" />';

		phpbb::$template->assign_vars(array(
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Obtain the administrator's name, password and email address
	*/
	function obtain_admin_settings($mode, $sub, &$data)
	{
		if ($data['dbms'] == '')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			$this->p_master->redirect('index.' . PHP_EXT . '?mode=install');
		}

		$passed = false;
		$data['default_lang'] = ($data['default_lang'] !== '') ? $data['default_lang'] : $data['language'];

		if (phpbb_request::is_set_post('check'))
		{
			$error = array();

			// Check the entered email address and password
			if ($data['admin_name'] == '' || $data['admin_pass1'] == '' || $data['admin_pass2'] == '' || $data['board_email'] == '' || $data['board_contact'] == '')
			{
				$error[] = phpbb::$user->lang['INST_ERR_MISSING_DATA'];
			}

			if ($data['admin_pass1'] != $data['admin_pass2'] && $data['admin_pass1'] != '')
			{
				$error[] = phpbb::$user->lang['INST_ERR_PASSWORD_MISMATCH'];
			}

			// Test against the default username rules
			if ($data['admin_name'] != '' && utf8_strlen($data['admin_name']) < 3)
			{
				$error[] = phpbb::$user->lang['INST_ERR_USER_TOO_SHORT'];
			}

			if ($data['admin_name'] != '' && utf8_strlen($data['admin_name']) > 20)
			{
				$error[] = phpbb::$user->lang['INST_ERR_USER_TOO_LONG'];
			}

			// Test against the default password rules
			if ($data['admin_pass1'] != '' && utf8_strlen($data['admin_pass1']) < 6)
			{
				$error[] = phpbb::$user->lang['INST_ERR_PASSWORD_TOO_SHORT'];
			}

			if ($data['admin_pass1'] != '' && utf8_strlen($data['admin_pass1']) > 30)
			{
				$error[] = phpbb::$user->lang['INST_ERR_PASSWORD_TOO_LONG'];
			}

			if ($data['board_email'] != '' && !preg_match('/^' . get_preg_expression('email') . '$/i', $data['board_email']))
			{
				$error[] = phpbb::$user->lang['INST_ERR_EMAIL_INVALID'];
			}

			if ($data['board_contact'] != '' && !preg_match('/^' . get_preg_expression('email') . '$/i', $data['board_contact']))
			{
				$error[] = phpbb::$user->lang['INST_ERR_EMAIL_INVALID'];
			}

			phpbb::$template->assign_block_vars('checks', array(
				'S_LEGEND'			=> true,
				'LEGEND'			=> phpbb::$user->lang['STAGE_ADMINISTRATOR'],
			));

			if (!sizeof($error))
			{
				$passed = true;

				phpbb::$template->assign_block_vars('checks', array(
					'TITLE'		=> phpbb::$user->lang['ADMIN_TEST'],
					'RESULT'	=> '<strong style="color:green">' . phpbb::$user->lang['TESTS_PASSED'] . '</strong>',
				));
			}
			else
			{
				phpbb::$template->assign_block_vars('checks', array(
					'TITLE'		=> phpbb::$user->lang['ADMIN_TEST'],
					'RESULT'	=> '<strong style="color:red">' . implode('<br />', $error) . '</strong>',
				));
			}
		}

		$s_hidden_fields = '';

		if (!$passed)
		{
			$this->build_form($data, $this->admin_config_options);
		}
		else
		{
			$s_hidden_fields .= $this->return_hidden_fields($data, $this->admin_config_options);
		}

		$s_hidden_fields .= $this->return_hidden_fields($data, $this->common_config_options, $this->db_config_options);

		$url = ($passed) ? phpbb::$url->append_sid($this->p_master->module_url, "mode=$mode&amp;sub=config_file") : phpbb::$url->build_url();
		$s_hidden_fields .= ($passed) ? '' : '<input type="hidden" name="check" value="true" />';

		phpbb::$template->assign_vars(array(
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Writes the config file to disk, or if unable to do so offers alternative methods
	*/
	function create_config_file($mode, $sub, &$data)
	{
		if ($data['dbms'] == '')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			$this->p_master->redirect('index.' . PHP_EXT . '?mode=install');
		}

		$s_hidden_fields = $this->return_hidden_fields($data, $this->common_config_options);
		$written = false;

		// Create a list of any PHP modules we wish to have loaded
		$load_extensions = array();
		$available_dbms = get_available_dbms($data['dbms']);

		$check_exts = array_merge($this->php_dlls_other);

		foreach ($check_exts as $dll)
		{
			if (!@extension_loaded($dll))
			{
				if (!can_load_dll($dll))
				{
					continue;
				}

				$load_extensions[] = $dll . '.' . PHP_SHLIB_SUFFIX;
			}
		}

		$db_module = $available_dbms[$data['dbms']]['MODULE'];

		if (!is_array($db_module))
		{
			$db_module = array($db_module);
		}

		$load_dll = true;
		foreach ($db_module as $dll)
		{
			if (@extension_loaded($dll))
			{
				$load_dll = false;
				break;
			}

			if (!can_load_dll($dll))
			{
				$load_dll = false;
				break;
			}

			$load_dll = true;
		}

		if ($load_dll)
		{
			$dll = current($db_module);
			$load_extensions[] = $dll . '.' . PHP_SHLIB_SUFFIX;
		}

		// Create a lock file to indicate that there is an install in progress
		$fp = @fopen(PHPBB_ROOT_PATH . 'cache/install_lock', 'wb');
		if ($fp === false)
		{
			// We were unable to create the lock file - abort
			trigger_error(phpbb::$user->lang['UNABLE_WRITE_LOCK'], E_USER_ERROR);
		}
		@fclose($fp);

		phpbb::$system->chmod(PHPBB_ROOT_PATH . 'cache/install_lock', phpbb::CHMOD_READ | phpbb::CHMOD_WRITE);

		$load_extensions = implode(',', $load_extensions);

		// Time to convert the data provided into a config file
		$config_data = "<?php\n";
		$config_data .= "// phpBB 3.0.x auto-generated configuration file\n// Do not change anything in this file!\n";
		$config_data .= "if (class_exists('phpbb') && defined('IN_PHPBB'))\n{\n\tphpbb::set_config(array(\n";

		$config_data_array = array(
			'dbms'			=> $available_dbms[$data['dbms']]['DRIVER'],
			'dbhost'		=> $data['dbhost'],
			'dbport'		=> $data['dbport'],
			'dbname'		=> $data['dbname'],
			'dbuser'		=> $data['dbuser'],
			'dbpasswd'		=> htmlspecialchars_decode($data['dbpasswd']),
			'table_prefix'	=> $data['table_prefix'],
			'admin_folder'	=> 'adm',
			'acm_type'		=> 'file',
			'extensions'	=> $load_extensions,
		);

		foreach ($config_data_array as $key => $value)
		{
			$config_data .= "\t\t'{$key}' => '" . str_replace("'", "\\'", str_replace('\\', '\\\\', $value)) . "',\n";
		}
		unset($config_data_array);

		$config_data .= "\n\t\t'debug' => true,\n\t\t'debug_extra' => true,\n\n\t\t// DO NOT CHANGE\n\t\t'installed' => true,\n\t));\n}\n\n";

		// Attempt to write out the config file directly. If it works, this is the easiest way to do it ...
		if ((file_exists(PHPBB_ROOT_PATH . 'config.' . PHP_EXT) && is_writable(PHPBB_ROOT_PATH . 'config.' . PHP_EXT)) || is_writable(PHPBB_ROOT_PATH))
		{
			// Assume it will work ... if nothing goes wrong below
			$written = true;

			if (!($fp = @fopen(PHPBB_ROOT_PATH . 'config.' . PHP_EXT, 'w')))
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
				@chmod(PHPBB_ROOT_PATH . 'config.' . PHP_EXT, 0644);
			}
		}

		if (phpbb_request::is_set_post('dldone'))
		{
			// Do a basic check to make sure that the file has been uploaded
			// Note that all we check is that the file has _something_ in it
			// We don't compare the contents exactly - if they can't upload
			// a single file correctly, it's likely they will have other problems....
			if (filesize(PHPBB_ROOT_PATH . 'config.' . PHP_EXT) > 10)
			{
				$written = true;
			}
		}

		$s_hidden_fields .= $this->return_hidden_fields($data, $this->db_config_options, $this->admin_config_options);

		if (!$written)
		{
			// OK, so it didn't work let's try the alternatives
			if (phpbb_request::is_set_post('dlconfig'))
			{
				// They want a copy of the file to download, so send the relevant headers and dump out the data
				header('Content-Type: text/x-delimtext; name="config.' . PHP_EXT . '"');
				header('Content-disposition: attachment; filename=config.' . PHP_EXT);
				echo $config_data;
				exit;
			}

			// The option to download the config file is always available, so output it here
			phpbb::$template->assign_vars(array(
				'S_HIDDEN'				=> $s_hidden_fields,
				'U_ACTION'				=> phpbb::$url->build_url(),
			));
		}
		else
		{
			phpbb::$template->assign_vars(array(
				'S_WRITTEN'	=> true,
				'S_HIDDEN'	=> $s_hidden_fields,
				'U_ACTION'	=> phpbb::$url->append_sid($this->p_master->module_url, "mode=$mode&amp;sub=advanced"),
			));
		}
	}

	/**
	* Provide an opportunity to customise some advanced settings during the install
	* in case it is necessary for them to be set to access later
	*/
	function obtain_advanced_settings($mode, $sub, &$data)
	{
		if ($data['dbms'] == '')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			$this->p_master->redirect('index.' . PHP_EXT . '?mode=install');
		}

		$s_hidden_fields = $this->return_hidden_fields($data, $this->common_config_options);

		$data['email_enable'] = ($data['email_enable'] !== '') ? $data['email_enable'] : true;
		$data['server_name'] = ($data['server_name'] !== '') ? $data['server_name'] : phpbb::$user->system['host'];
		$data['server_port'] = ($data['server_port'] !== '') ? $data['server_port'] : phpbb::$user->system['port'];
		$data['server_protocol'] = ($data['server_protocol'] !== '') ? $data['server_protocol'] : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://');
		$data['cookie_secure'] = ($data['cookie_secure'] !== '') ? $data['cookie_secure'] : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true : false);

		if ($data['script_path'] === '')
		{
			// Replace backslashes and doubled slashes (could happen on some proxy setups)
			$data['script_path'] = trim(str_replace(array('/install/', '\\install\\'), '', phpbb::$user->page['script_path']));
		}

		$this->build_form($data, $this->advanced_config_options);
		$s_hidden_fields .= $this->return_hidden_fields($data, $this->db_config_options, $this->admin_config_options);

		phpbb::$template->assign_vars(array(
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> phpbb::$url->append_sid($this->p_master->module_url, "mode=$mode&amp;sub=create_table"),
		));
	}

	/**
	* Load the contents of the schema into the database and then alter it based on what has been input during the installation
	*/
	function load_schema($mode, $sub, &$data)
	{
		$s_hidden_fields = '';

		if ($data['dbms'] == '')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			$this->p_master->redirect('index.' . PHP_EXT . '?mode=install');
		}

		// If we get here and the extension isn't loaded it should be safe to just go ahead and load it
		$available_dbms = get_available_dbms($data['dbms']);

		if (!isset($available_dbms[$data['dbms']]))
		{
			// Someone's been silly and tried providing a non-existant dbms
			$this->p_master->redirect('index.' . PHP_EXT . '?mode=install');
		}

		phpbb::assign('db', phpbb_db_dbal::connect($data['dbms'], $data['dbhost'], $data['dbuser'], htmlspecialchars_decode($data['dbpasswd']), $data['dbname'], $data['dbport'], false, false));

		// Include the db tools - we work with them to create the tables
		include PHPBB_ROOT_PATH . 'includes/db/db_tools.' . PHP_EXT;

		// Load the Schema data (Fill $schema_data)
		include PHPBB_ROOT_PATH . 'install/schemas/schema_structure.' . PHP_EXT;

		// we do not return statements, we simply let them execute
		$db_tools = new phpbb_db_tools(phpbb::$db);

		foreach ($schema_data as $table_name => $table_data)
		{
			// Change prefix, we always have phpbb_, therefore we can do a substr() here
			$table_name = $data['table_prefix'] . substr($table_name, 6);

			// Now create the table
			$db_tools->sql_create_table($table_name, $table_data);
		}

		// Now get the schema data
		include PHPBB_ROOT_PATH . 'install/schemas/schema_data.' . PHP_EXT;

		// Build data array for substituted content ;)

		$cookie_domain = ($data['server_name'] != '') ? $data['server_name'] : phpbb::$user->system['host'];

		// Try to come up with the best solution for cookie domain...
		if (strpos($cookie_domain, 'www.') === 0)
		{
			$cookie_domain = str_replace('www.', '.', $cookie_domain);
		}

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

		$ref = substr(phpbb::$user->system['referer'], strpos(phpbb::$user->system['referer'], '://') + 3);

		$template_data = array(
			'BOARD_STARTDATE'	=> time(),
			'CURRENT_TIME'		=> time(),
			'DEFAULT_LANG'		=> $data['default_lang'],
			'DBMS_VERSION'		=> phpbb::$db->sql_server_info(true),
			'IMG_IMAGICK'		=> $data['img_imagick'],
			'SERVER_NAME'		=> $data['server_name'],
			'SERVER_PORT'		=> $data['server_port'],
			'BOARD_EMAIL'		=> $data['board_email'],
			'BOARD_CONTACT'		=> $data['board_contact'],
			'COOKIE_DOMAIN'		=> $cookie_domain,
			'DEFAULT_DATEFORMAT'=> phpbb::$user->lang['default_dateformat'],
			'EMAIL_ENABLE'		=> $data['email_enable'],
			'SMTP_DELIVERY'		=> $data['smtp_delivery'],
			'SMTP_HOST'			=> $data['smtp_host'],
			'SMTP_AUTH_METHOD'	=> $data['smtp_auth'],
			'SMTP_USERNAME'		=> $data['smtp_user'],
			'SMTP_PASSWORD'		=> $data['smtp_pass'],
			'COOKIE_SECURE'		=> $data['cookie_secure'],
			'COOKIE_NAME'		=> 'phpbb3_' . phpbb::$security->gen_rand_string(4),
			'FORCE_SERVER_VARS'	=> $data['force_server_vars'],
			'SCRIPT_PATH'		=> $data['script_path'],
			'SERVER_PROTOCOL'	=> $data['server_protocol'],
			'NEWEST_USERNAME'	=> $data['admin_name'],
			'AVATAR_SALT'		=> md5(phpbb::$security->gen_rand_string()),
			'CAPTCHA_PLUGIN'	=> (@extension_loaded('gd') || can_load_dll('gd')) ? 'phpbb_captcha_gd' : 'phpbb_captcha_nogd',
			'REFERER_VALIDATION'=> (!(stripos($ref, phpbb::$user->system['host']) === 0)) ? '0' : '1',
			'ADMIN_NAME'		=> $data['admin_name'],
			'ADMIN_NAME_CLEAN'	=> utf8_clean_string($data['admin_name']),
			'ADMIN_PASSWORD'	=> phpbb::$security->hash_password($data['admin_pass1']),
			'ADMIN_EMAIL'		=> $data['board_contact'],
			'ADMIN_EMAIL_HASH'	=> hexdec(crc32($data['board_contact']) . strlen($data['board_contact'])),
			'USER_IP'			=> phpbb::$user->ip,
		);

		// Apply Schema changes
		$db_tools->db->sql_transaction('begin');

		foreach ($schema_data as $schema_array)
		{
			$schema_array['table'] = $data['table_prefix'] . substr($schema_array['table'], 6);
			$db_tools->sql_insert_data($schema_array, $template_data);
		}

		$db_tools->db->sql_transaction('commit');

		// Update data
		$db_tools->db->sql_transaction('begin');

		foreach ($schema_updates as $schema_array)
		{
			$schema_array['table'] = $data['table_prefix'] . substr($schema_array['table'], 6);
			$db_tools->sql_update_data($schema_array, $template_data);
		}

		$db_tools->db->sql_transaction('commit');

		// We need to insert the role data manually... else the schema array is quite large...
		$sql = 'SELECT role_id, role_name
			FROM ' . $data['table_prefix'] . 'acl_roles';
		$result = phpbb::$db->sql_query($sql);

		$role_ids = array();
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$role_ids[$row['role_name']] = $row['role_id'];
		}
		phpbb::$db->sql_freeresult($result);

		foreach ($this->role_definitions as $role_ary)
		{
			$role_name		= $role_ary[0];
			$auth_setting	= $role_ary[1];
			$permission		= $role_ary[2];
			$condition		= $role_ary[3];
			$options		= $role_ary[4];

			$sql = '';
			$sql .= 'INSERT INTO ' . $data['table_prefix'] . 'acl_roles_data (role_id, auth_option_id, auth_setting) ';
			$sql .= 'SELECT ' . $role_ids[$role_name] . ', auth_option_id, ' . $auth_setting . ' ';
			$sql .= 'FROM ' . $data['table_prefix'] . 'acl_options ';
			$sql .= "WHERE auth_option LIKE '{$permission}%'";

			if ($options !== false)
			{
				$sql .= ' AND auth_option ' . $condition . ' (\'' . implode("', '", $options) . "')";
			}

			phpbb::$db->sql_query($sql);
		}

		phpbb::$template->assign_vars(array(
			'S_HIDDEN'	=> build_hidden_fields($data),
			'U_ACTION'	=> phpbb::$url->append_sid($this->p_master->module_url, "mode=$mode&amp;sub=final"),
		));
	}

	/**
	* Build the search index...
	*/
	function build_search_index($mode, $sub)
	{
		include_once PHPBB_ROOT_PATH . 'includes/search/fulltext_native.' . PHP_EXT;

		$error = false;
		$search = new fulltext_native($error);

		$sql = 'SELECT post_id, post_subject, post_text, poster_id, forum_id
			FROM ' . POSTS_TABLE;
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$search->index('post', $row['post_id'], $row['post_text'], $row['post_subject'], $row['poster_id'], $row['forum_id']);
		}
		phpbb::$db->sql_freeresult($result);
	}

	/**
	* Populate the module tables
	*/
	function add_modules($mode, $sub)
	{
		include_once PHPBB_ROOT_PATH . 'modules/acp/acp_modules.' . PHP_EXT;

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

				$categories[$cat_name]['id'] = (int) $module_data['module_id'];
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
							'parent_id'			=> (int) $categories[$cat_name]['id'],
							'module_class'		=> $module_class,
							'module_langname'	=> $level2_name,
							'module_mode'		=> '',
							'module_auth'		=> '',
						);

						$_module->update_module_data($module_data, true);

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
				$result = phpbb::$db->sql_query($sql);
				$row = phpbb::$db->sql_fetchrow($result);
				phpbb::$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_up', 4);

				// Move permissions intro screen module 4 up...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'permissions'
						AND module_class = 'acp'
						AND module_mode = 'intro'";
				$result = phpbb::$db->sql_query($sql);
				$row = phpbb::$db->sql_fetchrow($result);
				phpbb::$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_up', 4);

				// Move manage users screen module 5 up...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'users'
						AND module_class = 'acp'
						AND module_mode = 'overview'";
				$result = phpbb::$db->sql_query($sql);
				$row = phpbb::$db->sql_fetchrow($result);
				phpbb::$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_up', 5);
			}

			if ($module_class == 'ucp')
			{
				// Move attachment module 4 down...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'attachments'
						AND module_class = 'ucp'
						AND module_mode = 'attachments'";
				$result = phpbb::$db->sql_query($sql);
				$row = phpbb::$db->sql_fetchrow($result);
				phpbb::$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_down', 4);
			}

			// And now for the special ones
			// (these are modules which appear in multiple categories and thus get added manually to some for more control)
			if (isset($this->module_extras[$module_class]))
			{
				foreach ($this->module_extras[$module_class] as $cat_name => $mods)
				{
					$sql = 'SELECT module_id, left_id, right_id
						FROM ' . MODULES_TABLE . "
						WHERE module_langname = '" . phpbb::$db->sql_escape($cat_name) . "'
							AND module_class = '" . phpbb::$db->sql_escape($module_class) . "'";
					$result = phpbb::$db->sql_query_limit($sql, 1);
					$row2 = phpbb::$db->sql_fetchrow($result);
					phpbb::$db->sql_freeresult($result);

					foreach ($mods as $mod_name)
					{
						$sql = 'SELECT *
							FROM ' . MODULES_TABLE . "
							WHERE module_langname = '" . phpbb::$db->sql_escape($mod_name) . "'
								AND module_class = '" . phpbb::$db->sql_escape($module_class) . "'
								AND module_basename <> ''";
						$result = phpbb::$db->sql_query_limit($sql, 1);
						$row = phpbb::$db->sql_fetchrow($result);
						phpbb::$db->sql_freeresult($result);

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
		$dir = @opendir(PHPBB_ROOT_PATH . 'language');

		if (!$dir)
		{
			trigger_error('Unable to access the language directory', E_USER_ERROR);
		}

		while (($file = readdir($dir)) !== false)
		{
			$path = PHPBB_ROOT_PATH . 'language/' . $file;

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

				phpbb::$db->sql_query('INSERT INTO ' . LANG_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', $lang_pack));

				$valid_localized = array(
					'icon_back_top', 'icon_contact_aim', 'icon_contact_email', 'icon_contact_icq', 'icon_contact_jabber', 'icon_contact_msnm', 'icon_contact_pm', 'icon_contact_yahoo', 'icon_contact_www', 'icon_post_delete', 'icon_post_edit', 'icon_post_info', 'icon_post_quote', 'icon_post_report', 'icon_user_online', 'icon_user_offline', 'icon_user_profile', 'icon_user_search', 'icon_user_warn', 'button_pm_forward', 'button_pm_new', 'button_pm_reply', 'button_topic_locked', 'button_topic_new', 'button_topic_reply',
				);

				$sql_ary = array();

				$sql = 'SELECT *
					FROM ' . STYLES_IMAGESET_TABLE;
				$result = phpbb::$db->sql_query($sql);

				while ($imageset_row = phpbb::$db->sql_fetchrow($result))
				{
					if (@file_exists(PHPBB_ROOT_PATH . "styles/{$imageset_row['imageset_path']}/imageset/{$lang_pack['lang_iso']}/imageset.cfg"))
					{
						$cfg_data_imageset_data = parse_cfg_file(PHPBB_ROOT_PATH . "styles/{$imageset_row['imageset_path']}/imageset/{$lang_pack['lang_iso']}/imageset.cfg");
						foreach ($cfg_data_imageset_data as $image_name => $value)
						{
							if (strpos($value, '*') !== false)
							{
								if (substr($value, -1, 1) === '*')
								{
									list($image_filename, $image_height) = explode('*', $value);
									$image_width = 0;
								}
								else
								{
									list($image_filename, $image_height, $image_width) = explode('*', $value);
								}
							}
							else
							{
								$image_filename = $value;
								$image_height = $image_width = 0;
							}

							if (strpos($image_name, 'img_') === 0 && $image_filename)
							{
								$image_name = substr($image_name, 4);
								if (in_array($image_name, $valid_localized))
								{
									$sql_ary[] = array(
										'image_name'		=> (string) $image_name,
										'image_filename'	=> (string) $image_filename,
										'image_height'		=> (int) $image_height,
										'image_width'		=> (int) $image_width,
										'imageset_id'		=> (int) $imageset_row['imageset_id'],
										'image_lang'		=> (string) $lang_pack['lang_iso'],
									);
								}
							}
						}
					}
				}
				phpbb::$db->sql_freeresult($result);

				if (sizeof($sql_ary))
				{
					phpbb::$db->sql_multi_insert(STYLES_IMAGESET_DATA_TABLE, $sql_ary);
				}
			}
		}
		closedir($dir);
	}

	/**
	* Add search robots to the database
	*/
	function add_bots($mode, $sub)
	{
		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . "
			WHERE group_name_clean = 'bots'";
		$result = phpbb::$db->sql_query($sql);
		$group_id = (int) phpbb::$db->sql_fetchfield('group_id');
		phpbb::$db->sql_freeresult($result);

		if (!$group_id)
		{
			// If we reach this point then something has gone very wrong
			trigger_error('NO_GROUP', E_USER_ERROR);
		}

		if (!function_exists('user_add'))
		{
			include PHPBB_ROOT_PATH . 'includes/functions_user.' . PHP_EXT;
		}

		foreach ($this->bot_list as $bot_name => $bot_ary)
		{
			$user_row = array(
				'user_type'				=> phpbb::USER_IGNORE,
				'group_id'				=> $group_id,
				'username'				=> $bot_name,
				'user_regdate'			=> time(),
				'user_password'			=> '',
				'user_colour'			=> '9E8DA7',
				'user_email'			=> '',
				'user_lang'				=> phpbb::$config['default_lang'],
				'user_style'			=> 1,
				'user_timezone'			=> 0,
				'user_dateformat'		=> phpbb::$user->lang['default_dateformat'],
				'user_allow_massemail'	=> 0,
			);

			$user_id = user_add($user_row);

			if (!$user_id)
			{
				// If we can't insert this user then continue to the next one to avoid inconsistant data
				$this->p_master->db_error('Unable to insert bot into users table', $db->sql_error_sql, __LINE__, __FILE__);
				continue;
			}

			$sql = 'INSERT INTO ' . BOTS_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', array(
				'bot_active'	=> 1,
				'bot_name'		=> (string) $bot_name,
				'user_id'		=> (int) $user_id,
				'bot_agent'		=> (string) $bot_ary[0],
				'bot_ip'		=> (string) $bot_ary[1],
			));

			$result = phpbb::$db->sql_query($sql);
		}
	}

	/**
	* Sends an email to the board administrator with their password and some useful links
	*/
	function email_admin($mode, $sub)
	{
		// Obtain any submitted data
		$data = $this->get_submitted_data();

		// Normal Login
		phpbb::$user->login($data['admin_name'], $data['admin_pass1'], false, true, false);

		// Admin Login
		phpbb::$user->login($data['admin_name'], $data['admin_pass1'], false, true, true);

		phpbb::$acl->init(phpbb::$user->data);

		// OK, Now that we've reached this point we can be confident that everything is installed and working......I hope :)
		/* So it's time to send an email to the administrator confirming the details they entered
		if (phpbb::$config['email_enable'])
		{
			include_once(PHPBB_ROOT_PATH . 'includes/functions_messenger.' . PHP_EXT);

			$messenger = new messenger(false);

			$messenger->template('installed', $data['language']);

			$messenger->to($data['board_contact'], $data['admin_name']);

			$messenger->headers('X-AntiAbuse: Board servername - ' . phpbb::$config['server_name']);
			$messenger->headers('X-AntiAbuse: User_id - ' . phpbb::$user->data['user_id']);
			$messenger->headers('X-AntiAbuse: Username - ' . phpbb::$user->data['username']);
			$messenger->headers('X-AntiAbuse: User IP - ' . phpbb::$user->ip);

			$messenger->assign_vars(array(
				'USERNAME'		=> htmlspecialchars_decode($data['admin_name']),
				'PASSWORD'		=> htmlspecialchars_decode($data['admin_pass1']))
			);

			$messenger->send(NOTIFY_EMAIL);
		}
*/
		// And finally, add a note to the log
		add_log('admin', 'LOG_INSTALL_INSTALLED', phpbb::$config['version']);

		phpbb::$template->assign_vars(array(
			'L_BODY'		=> phpbb::$user->lang('INSTALL_CONGRATS_EXPLAIN', phpbb::$config['version'], phpbb::$url->append_sid('install/index', 'mode=convert&amp;language=' . $data['language']), '../docs/README.html'),
			'U_ACTION'	=> phpbb::$url->append_sid('adm/index'),
		));
	}

	/**
	* Generate a list of available mail server authentication methods
	*/
	function mail_auth_select($selected_method)
	{
		$auth_methods = array('PLAIN', 'LOGIN', 'CRAM-MD5', 'DIGEST-MD5', 'POP-BEFORE-SMTP');
		$s_smtp_auth_options = '';

		foreach ($auth_methods as $method)
		{
			$s_smtp_auth_options .= '<option value="' . $method . '"' . (($selected_method == $method) ? ' selected="selected"' : '') . '>' . phpbb::$user->lang['SMTP_' . str_replace('-', '_', $method)] . '</option>';
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
			'dbhost'		=> request_var('dbhost', ''),
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
			'board_contact'	=> strtolower(request_var('board_contact', '')),
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

	var $common_config_options = array('language' => array(), 'img_imagick' => array());

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
		'table_prefix'			=> array('lang' => 'TABLE_PREFIX',	'type' => 'text:25:100', 'explain' => false),
	);
	var $admin_config_options = array(
		'legend1'				=> 'ADMIN_CONFIG',
		'default_lang'			=> array('lang' => 'DEFAULT_LANG',				'type' => 'select', 'options' => '$this->module->inst_language_select(\'{VALUE}\')', 'explain' => false),
		'admin_name'			=> array('lang' => 'ADMIN_USERNAME',			'type' => 'text:25:100', 'explain' => true),
		'admin_pass1'			=> array('lang' => 'ADMIN_PASSWORD',			'type' => 'password:25:100', 'explain' => true),
		'admin_pass2'			=> array('lang' => 'ADMIN_PASSWORD_CONFIRM',	'type' => 'password:25:100', 'explain' => false),
		'board_contact'			=> array('lang' => 'CONTACT_EMAIL',				'type' => 'text:25:100', 'explain' => true),
		'board_email'			=> array('lang' => 'ADMIN_EMAIL',				'type' => 'text:25:100', 'explain' => true),
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
		'Baidu [Spider]'			=> array('Baiduspider+(', ''),
		'Exabot [Bot]'				=> array('Exabot/', ''),
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
		'ichiro [Crawler]'			=> array('ichiro/2', ''),
		'Majestic-12 [Bot]'			=> array('MJ12bot/', ''),
		'Metager [Bot]'				=> array('MetagerBot/', ''),
		'MSN NewsBlogs'				=> array('msnbot-NewsBlogs/', ''),
		'MSN [Bot]'					=> array('msnbot/', ''),
		'MSNbot Media'				=> array('msnbot-media/', ''),
		'NG-Search [Bot]'			=> array('NG-Search/', ''),
		'Nutch [Bot]'				=> array('http://lucene.apache.org/nutch/', ''),
		'Nutch/CVS [Bot]'			=> array('NutchCVS/', ''),
		'OmniExplorer [Bot]'		=> array('OmniExplorer_Bot/', ''),
		'Online link [Validator]'	=> array('online link validator', ''),
		'psbot [Picsearch]'			=> array('psbot/0', ''),
		'Seekport [Bot]'			=> array('Seekbot/', ''),
		'Sensis [Crawler]'			=> array('Sensis Web Crawler', ''),
		'SEO Crawler'				=> array('SEO search Crawler/', ''),
		'Seoma [Crawler]'			=> array('Seoma [SEO Crawler]', ''),
		'SEOSearch [Crawler]'		=> array('SEOsearch/', ''),
		'Snappy [Bot]'				=> array('Snappy/1.1 ( http://www.urltrends.com/ )', ''),
		'Steeler [Crawler]'			=> array('http://www.tkl.iis.u-tokyo.ac.jp/~crawler/', ''),
		'Synoo [Bot]'				=> array('SynooBot/', ''),
		'Telekom [Bot]'				=> array('crawleradmin.t-info@telekom.de', ''),
		'TurnitinBot [Bot]'			=> array('TurnitinBot/', ''),
		'Voyager [Bot]'				=> array('voyager/1.0', ''),
		'W3 [Sitesearch]'			=> array('W3 SiteSearch Crawler', ''),
		'W3C [Linkcheck]'			=> array('W3C-checklink/', ''),
		'W3C [Validator]'			=> array('W3C_*Validator', ''),
		'WiseNut [Bot]'				=> array('http://www.WISEnutbot.com', ''),
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

	var $role_definitions = array(
		// Standard Admin (a_)
		array('ROLE_ADMIN_STANDARD', 1, 'a_', 'NOT IN', array('a_switchperm', 'a_jabber', 'a_phpinfo', 'a_server', 'a_backup', 'a_styles', 'a_clearlogs', 'a_modules', 'a_language', 'a_email', 'a_bots', 'a_search', 'a_aauth', 'a_roles')),
		// Forum Admin (a_)
		array('ROLE_ADMIN_FORUM', 1, 'a_', 'IN', array('a_', 'a_authgroups', 'a_authusers', 'a_fauth', 'a_forum', 'a_forumadd', 'a_forumdel', 'a_mauth', 'a_prune', 'a_uauth', 'a_viewauth', 'a_viewlogs')),
		// User and Groups Admin (a_)
		array('ROLE_ADMIN_USERGROUP', 1, 'a_', 'IN', array('a_', 'a_authgroups', 'a_authusers', 'a_ban', 'a_group', 'a_groupadd', 'a_groupdel', 'a_ranks', 'a_uauth', 'a_user', 'a_viewauth', 'a_viewlogs')),
		// Full Admin (a_)
		array('ROLE_ADMIN_FULL', 1, 'a_', false, false),
		// All Features (u_)
		array('ROLE_USER_FULL', 1, 'u_', false, false),
		// Standard Features (u_)
		array('ROLE_USER_STANDARD', 1, 'u_', 'NOT IN', array('u_viewonline', 'u_chggrp', 'u_chgname', 'u_ignoreflood', 'u_pm_flash', 'u_pm_forward')),
		// Limited Features (u_)
		array('ROLE_USER_LIMITED', 1, 'u_', 'NOT IN', array('u_attach', 'u_viewonline', 'u_chggrp', 'u_chgname', 'u_ignoreflood', 'u_pm_attach', 'u_pm_emailpm', 'u_pm_flash', 'u_savedrafts', 'u_search', 'u_sendemail', 'u_sendim', 'u_masspm', 'u_masspm_group')),
		// No Private Messages (u_)
		array('ROLE_USER_NOPM', 1, 'u_', 'IN', array('u_', 'u_chgavatar', 'u_chgcensors', 'u_chgemail', 'u_chgpasswd', 'u_download', 'u_hideonline', 'u_sig', 'u_viewprofile')),
		array('ROLE_USER_NOPM', 0, 'u_', 'IN', array('u_readpm', 'u_sendpm', 'u_masspm', 'u_masspm_group')),
		// No Avatar (u_)
		array('ROLE_USER_NOAVATAR', 1, 'u_', 'NOT IN', array('u_attach', 'u_chgavatar', 'u_viewonline', 'u_chggrp', 'u_chgname', 'u_ignoreflood', 'u_pm_attach', 'u_pm_emailpm', 'u_pm_flash', 'u_savedrafts', 'u_search', 'u_sendemail', 'u_sendim', 'u_masspm', 'u_masspm_group')),
		array('ROLE_USER_NOAVATAR', 0, 'u_', 'IN', array('u_chgavatar', 'u_masspm', 'u_masspm_group')),
		// Download and search for guests (u_)
		array('ROLE_USER_GUESTS', 1, 'u_', 'IN', array('u_', 'u_download', 'u_search')),
		// Full Moderator (m_)
		array('ROLE_MOD_FULL', 1, 'm_', false, false),
		// Standard Moderator (m_)
		array('ROLE_MOD_STANDARD', 1, 'm_', 'NOT IN', array('m_ban', 'm_chgposter')),
		// Simple Moderator (m_)
		array('ROLE_MOD_SIMPLE', 1, 'm_', 'IN', array('m_', 'm_delete', 'm_edit', 'm_info', 'm_report')),
		// Queue Moderator (m_)
		array('ROLE_MOD_QUEUE', 1, 'm_', 'IN', array('m_', 'm_approve', 'm_edit')),
		// Full Access (f_)
		array('ROLE_FORUM_FULL', 1, 'f_', false, false),
		// Standard Access (f_)
		array('ROLE_FORUM_STANDARD', 1, 'f_', 'NOT IN', array('f_announce', 'f_flash', 'f_ignoreflood', 'f_poll', 'f_sticky', 'f_user_lock')),
		// No Access (f_)
		array('ROLE_FORUM_NOACCESS', 0, 'f_', 'IN', array('f_')),
		// Read Only Access (f_)
		array('ROLE_FORUM_READONLY', 1, 'f_', 'IN', array('f_', 'f_download', 'f_list', 'f_read', 'f_search', 'f_subscribe', 'f_print')),
		// Limited Access (f_)
		array('ROLE_FORUM_LIMITED', 1, 'f_', 'NOT IN', array('f_announce', 'f_attach', 'f_bump', 'f_delete', 'f_flash', 'f_icons', 'f_ignoreflood', 'f_poll', 'f_sticky', 'f_user_lock', 'f_votechg')),
		// Bot Access (f_)
		array('ROLE_FORUM_BOT', 1, 'f_', 'IN', array('f_', 'f_download', 'f_list', 'f_read', 'f_print')),
		// On Moderation Queue (f_)
		array('ROLE_FORUM_ONQUEUE', 1, 'f_', 'NOT IN', array('f_announce', 'f_bump', 'f_delete', 'f_flash', 'f_icons', 'f_ignoreflood', 'f_poll', 'f_sticky', 'f_user_lock', 'f_votechg', 'f_noapprove')),
		array('ROLE_FORUM_ONQUEUE', 0, 'f_', 'IN', array('f_noapprove')),
		// Standard Access + Polls (f_)
		array('ROLE_FORUM_POLLS', 1, 'f_', 'NOT IN', array('f_announce', 'f_flash', 'f_ignoreflood', 'f_sticky', 'f_user_lock')),
		// Limited Access + Polls (f_)
		array('ROLE_FORUM_LIMITED_POLLS', 1, 'f_', 'NOT IN', array('f_announce', 'f_attach', 'f_bump', 'f_delete', 'f_flash', 'f_icons', 'f_ignoreflood', 'f_sticky', 'f_user_lock', 'f_votechg')),
	);
}

?>