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

/**#@+
* @ignore
*/
define('IN_PHPBB', true);
define('IN_INSTALL', true);
/**#@-*/

$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

if (version_compare(PHP_VERSION, '5.3.3') < 0)
{
	die('You are running an unsupported PHP version. Please upgrade to PHP 5.3.3 or higher before trying to install phpBB 3.1');
}

function phpbb_require_updated($path, $optional = false)
{
	global $phpbb_root_path, $table_prefix;

	$new_path = $phpbb_root_path . 'install/update/new/' . $path;
	$old_path = $phpbb_root_path . $path;

	if (file_exists($new_path))
	{
		require($new_path);
	}
	else if (!$optional || file_exists($old_path))
	{
		require($old_path);
	}
}

function phpbb_include_updated($path, $optional = false)
{
	global $phpbb_root_path;

	$new_path = $phpbb_root_path . 'install/update/new/' . $path;
	$old_path = $phpbb_root_path . $path;

	if (file_exists($new_path))
	{
		include($new_path);
	}
	else if (!$optional || file_exists($old_path))
	{
		include($old_path);
	}
}

phpbb_require_updated('includes/startup.' . $phpEx);

// Try to override some limits - maybe it helps some...
@set_time_limit(0);
$mem_limit = @ini_get('memory_limit');
if (!empty($mem_limit))
{
	$unit = strtolower(substr($mem_limit, -1, 1));
	$mem_limit = (int) $mem_limit;

	if ($unit == 'k')
	{
		$mem_limit = floor($mem_limit / 1024);
	}
	else if ($unit == 'g')
	{
		$mem_limit *= 1024;
	}
	else if (is_numeric($unit))
	{
		$mem_limit = floor((int) ($mem_limit . $unit) / 1048576);
	}
	$mem_limit = max(128, $mem_limit) . 'M';
}
else
{
	$mem_limit = '128M';
}
@ini_set('memory_limit', $mem_limit);

// In case $phpbb_adm_relative_path is not set (in case of an update), use the default.
$phpbb_adm_relative_path = (isset($phpbb_adm_relative_path)) ? $phpbb_adm_relative_path : 'adm/';
$phpbb_admin_path = (defined('PHPBB_ADMIN_PATH')) ? PHPBB_ADMIN_PATH : $phpbb_root_path . $phpbb_adm_relative_path;

// Include essential scripts
phpbb_require_updated('phpbb/class_loader.' . $phpEx);

phpbb_require_updated('includes/functions.' . $phpEx);

phpbb_require_updated('includes/functions_content.' . $phpEx, true);

phpbb_include_updated('includes/functions_admin.' . $phpEx);
phpbb_include_updated('includes/utf/utf_normalizer.' . $phpEx);
phpbb_include_updated('includes/utf/utf_tools.' . $phpEx);
phpbb_require_updated('includes/functions_install.' . $phpEx);

// Setup class loader first
$phpbb_class_loader_new = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}install/update/new/phpbb/", $phpEx);
$phpbb_class_loader_new->register();
$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}phpbb/", $phpEx);
$phpbb_class_loader->register();
$phpbb_class_loader_ext = new \phpbb\class_loader('\\', "{$phpbb_root_path}ext/", $phpEx);
$phpbb_class_loader_ext->register();

// Set up container
$phpbb_config_php_file = new \phpbb\config_php_file($phpbb_root_path, $phpEx);
$phpbb_container_builder = new \phpbb\di\container_builder($phpbb_config_php_file, $phpbb_root_path, $phpEx);
$phpbb_container_builder->set_use_extensions(false);
$phpbb_container_builder->set_dump_container(false);
$phpbb_container_builder->set_use_custom_pass(false);
$phpbb_container_builder->set_inject_config(false);
$phpbb_container_builder->set_compile_container(false);

$other_config_path = $phpbb_root_path . 'install/update/new/config/';
$config_path = file_exists($other_config_path . 'services.yml') ? $other_config_path : $phpbb_root_path . 'config/';
$phpbb_container_builder->set_config_path($config_path);

$phpbb_container_builder->set_custom_parameters(array(
	'core.root_path'			=> $phpbb_root_path,
	'core.adm_relative_path'	=> $phpbb_adm_relative_path,
	'core.php_ext'				=> $phpEx,
	'core.table_prefix'			=> '',
	'cache.driver.class'		=> 'phpbb\cache\driver\file',
));

$phpbb_container = $phpbb_container_builder->get_container();
$phpbb_container->register('dbal.conn.driver')->setSynthetic(true);
$phpbb_container->compile();

$phpbb_class_loader->set_cache($phpbb_container->get('cache.driver'));
$phpbb_class_loader_ext->set_cache($phpbb_container->get('cache.driver'));

$phpbb_dispatcher = $phpbb_container->get('dispatcher');
$request	= $phpbb_container->get('request');

// make sure request_var uses this request instance
request_var('', 0, false, false, $request); // "dependency injection" for a function

// Try and load an appropriate language if required
$language = basename($request->variable('language', ''));

if ($request->header('Accept-Language') && !$language)
{
	$accept_lang_ary = explode(',', strtolower($request->header('Accept-Language')));
	foreach ($accept_lang_ary as $accept_lang)
	{
		// Set correct format ... guess full xx_yy form
		$accept_lang = substr($accept_lang, 0, 2) . '_' . substr($accept_lang, 3, 2);

		if (file_exists($phpbb_root_path . 'language/' . $accept_lang) && is_dir($phpbb_root_path . 'language/' . $accept_lang))
		{
			$language = $accept_lang;
			break;
		}
		else
		{
			// No match on xx_yy so try xx
			$accept_lang = substr($accept_lang, 0, 2);
			if (file_exists($phpbb_root_path . 'language/' . $accept_lang) && is_dir($phpbb_root_path . 'language/' . $accept_lang))
			{
				$language = $accept_lang;
				break;
			}
		}
	}
}

// No appropriate language found ... so let's use the first one in the language
// dir, this may or may not be English
if (!$language)
{
	$dir = @opendir($phpbb_root_path . 'language');

	if (!$dir)
	{
		die('Unable to access the language directory');
		exit;
	}

	while (($file = readdir($dir)) !== false)
	{
		$path = $phpbb_root_path . 'language/' . $file;

		if (!is_file($path) && !is_link($path) && file_exists($path . '/iso.txt'))
		{
			$language = $file;
			break;
		}
	}
	closedir($dir);
}

if (!file_exists($phpbb_root_path . 'language/' . $language) || !is_dir($phpbb_root_path . 'language/' . $language))
{
	die('No language found!');
}

// And finally, load the relevant language files
$load_lang_files = array('common', 'acp/common', 'acp/board', 'install', 'posting');
$new_path = $phpbb_root_path . 'install/update/new/language/' . $language . '/';
$old_path = $phpbb_root_path . 'language/' . $language . '/';

// NOTE: we can not use "phpbb_include_updated" as the files uses vars which would be required
// to be global while loading.
foreach ($load_lang_files as $lang_file)
{
	if (file_exists($new_path . $lang_file . '.' . $phpEx))
	{
		include($new_path . $lang_file . '.' . $phpEx);
	}
	else
	{
		include($old_path . $lang_file . '.' . $phpEx);
	}
}

// usually we would need every single constant here - and it would be consistent. For 3.0.x, use a dirty hack... :(

// Define needed constants
define('CHMOD_ALL', 7);
define('CHMOD_READ', 4);
define('CHMOD_WRITE', 2);
define('CHMOD_EXECUTE', 1);

$mode = $request->variable('mode', 'overview');
$sub = $request->variable('sub', '');

// Set PHP error handler to ours
set_error_handler(defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler');

$user = new \phpbb\user('\phpbb\datetime');
$auth = new \phpbb\auth\auth();

// Add own hook handler, if present. :o
if (file_exists($phpbb_root_path . 'includes/hooks/index.' . $phpEx))
{
	require($phpbb_root_path . 'includes/hooks/index.' . $phpEx);
	$phpbb_hook = new phpbb_hook(array('exit_handler', 'phpbb_user_session_handler', 'append_sid', array('template', 'display')));

	$phpbb_hook_finder = $phpbb_container->get('hook_finder');
	foreach ($phpbb_hook_finder->find() as $hook)
	{
		@include($phpbb_root_path . 'includes/hooks/' . $hook . '.' . $phpEx);
	}
}
else
{
	$phpbb_hook = false;
}

// Set some standard variables we want to force
$config = new \phpbb\config\config(array(
	'load_tplcompile'	=> '1'
));

$symfony_request = $phpbb_container->get('symfony_request');
$phpbb_filesystem = $phpbb_container->get('filesystem');
$phpbb_path_helper = $phpbb_container->get('path_helper');
$template = new \phpbb\template\twig\twig($phpbb_path_helper, $config, $user, new \phpbb\template\context());
$paths = array($phpbb_root_path . 'install/update/new/adm/style', $phpbb_admin_path . 'style');
$paths = array_filter($paths, 'is_dir');
$template->set_custom_style(array(
	array(
		'name' 		=> 'adm',
		'ext_path' 	=> 'adm/style/',
	),
), $paths);

$path = array_shift($paths);

$template->assign_var('T_ASSETS_PATH', $path . '/../../assets');
$template->assign_var('T_TEMPLATE_PATH', $path);

$install = new module();

$install->create('install', "index.$phpEx", $mode, $sub);
$install->load();

// Generate the page
$install->page_header();
$install->generate_navigation();

$template->set_filenames(array(
	'body' => $install->get_tpl_name())
);

$install->page_footer();

class module
{
	var $id = 0;
	var $type = 'install';
	var $module_ary = array();
	var $filename;
	var $module_url = '';
	var $tpl_name = '';
	var $mode;
	var $sub;

	/**
	* Private methods, should not be overwritten
	*/
	function create($module_type, $module_url, $selected_mod = false, $selected_submod = false)
	{
		global $db, $config, $phpEx, $phpbb_root_path;

		$module = array();

		// Grab module information using Bart's "neat-o-module" system (tm)
		$dir = @opendir('.');

		if (!$dir)
		{
			$this->error('Unable to access the installation directory', __LINE__, __FILE__);
		}

		$setmodules = 1;
		while (($file = readdir($dir)) !== false)
		{
			if (preg_match('#^install_(.*?)\.' . $phpEx . '$#', $file))
			{
				include($file);
			}
		}
		closedir($dir);

		unset($setmodules);

		if (!sizeof($module))
		{
			$this->error('No installation modules found', __LINE__, __FILE__);
		}

		// Order to use and count further if modules get assigned to the same position or not having an order
		$max_module_order = 1000;

		foreach ($module as $row)
		{
			// Module order not specified or module already assigned at this position?
			if (!isset($row['module_order']) || isset($this->module_ary[$row['module_order']]))
			{
				$row['module_order'] = $max_module_order;
				$max_module_order++;
			}

			$this->module_ary[$row['module_order']]['name'] = $row['module_title'];
			$this->module_ary[$row['module_order']]['filename'] = $row['module_filename'];
			$this->module_ary[$row['module_order']]['subs'] = $row['module_subs'];
			$this->module_ary[$row['module_order']]['stages'] = $row['module_stages'];

			if (strtolower($selected_mod) == strtolower($row['module_title']))
			{
				$this->id = (int) $row['module_order'];
				$this->filename = (string) $row['module_filename'];
				$this->module_url = (string) $module_url;
				$this->mode = (string) $selected_mod;
				// Check that the sub-mode specified is valid or set a default if not
				if (is_array($row['module_subs']))
				{
					$this->sub = strtolower((in_array(strtoupper($selected_submod), $row['module_subs'])) ? $selected_submod : $row['module_subs'][0]);
				}
				else if (is_array($row['module_stages']))
				{
					$this->sub = strtolower((in_array(strtoupper($selected_submod), $row['module_stages'])) ? $selected_submod : $row['module_stages'][0]);
				}
				else
				{
					$this->sub = '';
				}
			}
		} // END foreach
	} // END create

	/**
	* Load and run the relevant module if applicable
	*/
	function load($mode = false, $run = true)
	{
		global $phpbb_root_path, $phpEx;

		if ($run)
		{
			if (!empty($mode))
			{
				$this->mode = $mode;
			}

			$module = $this->filename;
			if (!class_exists($module))
			{
				$this->error('Module "' . htmlspecialchars($module) . '" not accessible.', __LINE__, __FILE__);
			}
			$this->module = new $module($this);

			if (method_exists($this->module, 'main'))
			{
				$this->module->main($this->mode, $this->sub);
			}
		}
	}

	/**
	* Output the standard page header
	*/
	function page_header()
	{
		if (defined('HEADER_INC'))
		{
			return;
		}

		define('HEADER_INC', true);
		global $template, $lang, $stage, $phpbb_admin_path, $path;

		$template->assign_vars(array(
			'L_CHANGE'				=> $lang['CHANGE'],
			'L_COLON'				=> $lang['COLON'],
			'L_INSTALL_PANEL'		=> $lang['INSTALL_PANEL'],
			'L_SELECT_LANG'			=> $lang['SELECT_LANG'],
			'L_SKIP'				=> $lang['SKIP'],
			'PAGE_TITLE'			=> $this->get_page_title(),
			'T_IMAGE_PATH'			=> htmlspecialchars($phpbb_admin_path) . 'images/',
			'T_JQUERY_LINK'			=> $path . '/../../assets/javascript/jquery.min.js',

			'S_CONTENT_DIRECTION' 	=> $lang['DIRECTION'],
			'S_CONTENT_FLOW_BEGIN'	=> ($lang['DIRECTION'] == 'ltr') ? 'left' : 'right',
			'S_CONTENT_FLOW_END'	=> ($lang['DIRECTION'] == 'ltr') ? 'right' : 'left',
			'S_CONTENT_ENCODING' 	=> 'UTF-8',

			'S_USER_LANG'			=> $lang['USER_LANG'],
			)
		);

		header('Content-type: text/html; charset=UTF-8');
		header('Cache-Control: private, no-cache="set-cookie"');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');

		return;
	}

	/**
	* Output the standard page footer
	*/
	function page_footer()
	{
		global $db, $template;

		$template->display('body');

		// Close our DB connection.
		if (!empty($db) && is_object($db))
		{
			$db->sql_close();
		}

		if (function_exists('exit_handler'))
		{
			exit_handler();
		}
	}

	/**
	* Returns desired template name
	*/
	function get_tpl_name()
	{
		return $this->module->tpl_name . '.html';
	}

	/**
	* Returns the desired page title
	*/
	function get_page_title()
	{
		global $lang;

		if (!isset($this->module->page_title))
		{
			return '';
		}

		return (isset($lang[$this->module->page_title])) ? $lang[$this->module->page_title] : $this->module->page_title;
	}

	/**
	* Generate an HTTP/1.1 header to redirect the user to another page
	* This is used during the installation when we do not have a database available to call the normal redirect function
	* @param string $page The page to redirect to relative to the installer root path
	*/
	function redirect($page)
	{
		global $request;

		// HTTP_HOST is having the correct browser url in most cases...
		$server_name = strtolower(htmlspecialchars_decode($request->header('Host', $request->server('SERVER_NAME'))));
		$server_port = $request->server('SERVER_PORT', 0);
		$secure = $request->is_secure() ? 1 : 0;

		$script_name = htmlspecialchars_decode($request->server('PHP_SELF'));
		if (!$script_name)
		{
			$script_name = htmlspecialchars_decode($request->server('REQUEST_URI'));
		}

		// Replace backslashes and doubled slashes (could happen on some proxy setups)
		$script_name = str_replace(array('\\', '//'), '/', $script_name);
		$script_path = trim(dirname($script_name));

		$url = (($secure) ? 'https://' : 'http://') . $server_name;

		if ($server_port && (($secure && $server_port <> 443) || (!$secure && $server_port <> 80)))
		{
			// HTTP HOST can carry a port number...
			if (strpos($server_name, ':') === false)
			{
				$url .= ':' . $server_port;
			}
		}

		$url .= $script_path . '/' . $page;
		header('Location: ' . $url);
		exit;
	}

	/**
	* Generate the navigation tabs
	*/
	function generate_navigation()
	{
		global $lang, $template, $phpEx, $language;

		if (is_array($this->module_ary))
		{
			@ksort($this->module_ary);
			foreach ($this->module_ary as $cat_ary)
			{
				$cat = $cat_ary['name'];
				$l_cat = (!empty($lang['CAT_' . $cat])) ? $lang['CAT_' . $cat] : preg_replace('#_#', ' ', $cat);
				$cat = strtolower($cat);
				$url = $this->module_url . "?mode=$cat&amp;language=$language";

				if ($this->mode == $cat)
				{
					$template->assign_block_vars('t_block1', array(
						'L_TITLE'		=> $l_cat,
						'S_SELECTED'	=> true,
						'U_TITLE'		=> $url,
					));

					if (is_array($this->module_ary[$this->id]['subs']))
					{
						$subs = $this->module_ary[$this->id]['subs'];
						foreach ($subs as $option)
						{
							$l_option = (!empty($lang['SUB_' . $option])) ? $lang['SUB_' . $option] : preg_replace('#_#', ' ', $option);
							$option = strtolower($option);
							$url = $this->module_url . '?mode=' . $this->mode . "&amp;sub=$option&amp;language=$language";

							$template->assign_block_vars('l_block1', array(
								'L_TITLE'		=> $l_option,
								'S_SELECTED'	=> ($this->sub == $option),
								'U_TITLE'		=> $url,
							));
						}
					}

					if (is_array($this->module_ary[$this->id]['stages']))
					{
						$subs = $this->module_ary[$this->id]['stages'];
						$matched = false;
						foreach ($subs as $option)
						{
							$l_option = (!empty($lang['STAGE_' . $option])) ? $lang['STAGE_' . $option] : preg_replace('#_#', ' ', $option);
							$option = strtolower($option);
							$matched = ($this->sub == $option) ? true : $matched;

							$template->assign_block_vars('l_block2', array(
								'L_TITLE'		=> $l_option,
								'S_SELECTED'	=> ($this->sub == $option),
								'S_COMPLETE'	=> !$matched,
							));
						}
					}
				}
				else
				{
					$template->assign_block_vars('t_block1', array(
						'L_TITLE'		=> $l_cat,
						'S_SELECTED'	=> false,
						'U_TITLE'		=> $url,
					));
				}
			}
		}
	}

	/**
	* Output an error message
	* If skip is true, return and continue execution, else exit
	*/
	function error($error, $line, $file, $skip = false)
	{
		global $lang, $db, $template, $phpbb_admin_path;

		if ($skip)
		{
			$template->assign_block_vars('checks', array(
				'S_LEGEND'	=> true,
				'LEGEND'	=> $lang['INST_ERR'],
			));

			$template->assign_block_vars('checks', array(
				'TITLE'		=> basename($file) . ' [ ' . $line . ' ]',
				'RESULT'	=> '<b style="color:red">' . $error . '</b>',
			));

			return;
		}

		echo '<!DOCTYPE html>';
		echo '<html dir="ltr">';
		echo '<head>';
		echo '<meta charset="utf-8">';
		echo '<title>' . $lang['INST_ERR_FATAL'] . '</title>';
		echo '<link href="' . htmlspecialchars($phpbb_admin_path) . 'style/admin.css" rel="stylesheet" type="text/css" media="screen" />';
		echo '</head>';
		echo '<body id="errorpage">';
		echo '<div id="wrap">';
		echo '	<div id="page-header">';
		echo '	</div>';
		echo '	<div id="page-body">';
		echo '		<div id="acp">';
		echo '		<div class="panel">';
		echo '			<span class="corners-top"><span></span></span>';
		echo '			<div id="content">';
		echo '				<h1>' . $lang['INST_ERR_FATAL'] . '</h1>';
		echo '		<p>' . $lang['INST_ERR_FATAL'] . "</p>\n";
		echo '		<p>' . basename($file) . ' [ ' . $line . " ]</p>\n";
		echo '		<p><b>' . $error . "</b></p>\n";
		echo '			</div>';
		echo '			<span class="corners-bottom"><span></span></span>';
		echo '		</div>';
		echo '		</div>';
		echo '	</div>';
		echo '	<div id="page-footer">';
		echo '		Powered by <a href="https://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Limited';
		echo '	</div>';
		echo '</div>';
		echo '</body>';
		echo '</html>';

		if (!empty($db) && is_object($db))
		{
			$db->sql_close();
		}

		exit_handler();
	}

	/**
	* Output an error message for a database related problem
	* If skip is true, return and continue execution, else exit
	*/
	function db_error($error, $sql, $line, $file, $skip = false)
	{
		global $lang, $db, $template;

		if ($skip)
		{
			$template->assign_block_vars('checks', array(
				'S_LEGEND'	=> true,
				'LEGEND'	=> $lang['INST_ERR_FATAL'],
			));

			$template->assign_block_vars('checks', array(
				'TITLE'		=> basename($file) . ' [ ' . $line . ' ]',
				'RESULT'	=> '<b style="color:red">' . $error . '</b><br />&#187; SQL:' . $sql,
			));

			return;
		}

		$template->set_filenames(array(
			'body' => 'install_error.html')
		);
		$this->page_header();
		$this->generate_navigation();

		$template->assign_vars(array(
			'MESSAGE_TITLE'		=> $lang['INST_ERR_FATAL_DB'],
			'MESSAGE_TEXT'		=> '<p>' . basename($file) . ' [ ' . $line . ' ]</p><p>SQL : ' . $sql . '</p><p><b>' . $error . '</b></p>',
		));

		// Rollback if in transaction
		if ($db->get_transaction())
		{
			$db->sql_transaction('rollback');
		}

		$this->page_footer();
	}

	/**
	* Generate the relevant HTML for an input field and the associated label and explanatory text
	*/
	function input_field($name, $type, $value = '', $options = '')
	{
		global $lang;
		$tpl_type = explode(':', $type);
		$tpl = '';

		switch ($tpl_type[0])
		{
			case 'text':
			case 'password':
			// HTML5 text-like input types
			case 'color':
			case 'date':
			case 'time':
			case 'datetime':
			case 'datetime-local':
			case 'email':
			case 'month':
			case 'number':
			case 'range':
			case 'search':
			case 'tel':
			case 'url':
			case 'week':

				$size = (int) $tpl_type[1];
				$maxlength = (int) $tpl_type[2];
				$autocomplete = (isset($options['autocomplete']) && $options['autocomplete'] == 'off') ? ' autocomplete="off"' : '';

				$tpl = '<input id="' . $name . '" type="' . $tpl_type[0] . '"' . (($size) ? ' size="' . $size . '"' : '') . ' maxlength="' . (($maxlength) ? $maxlength : 255) . '" name="' . $name . '"' . $autocomplete . ' value="' . $value . '" />';
			break;

			case 'textarea':
				$rows = (int) $tpl_type[1];
				$cols = (int) $tpl_type[2];

				$tpl = '<textarea id="' . $name . '" name="' . $name . '" rows="' . $rows . '" cols="' . $cols . '">' . $value . '</textarea>';
			break;

			case 'radio':
				$key_yes	= ($value) ? ' checked="checked" id="' . $name . '"' : '';
				$key_no		= (!$value) ? ' checked="checked" id="' . $name . '"' : '';

				$tpl_type_cond = explode('_', $tpl_type[1]);
				$type_no = ($tpl_type_cond[0] == 'disabled' || $tpl_type_cond[0] == 'enabled') ? false : true;

				$tpl_no = '<label><input type="radio" name="' . $name . '" value="0"' . $key_no . ' class="radio" /> ' . (($type_no) ? $lang['NO'] : $lang['DISABLED']) . '</label>';
				$tpl_yes = '<label><input type="radio" name="' . $name . '" value="1"' . $key_yes . ' class="radio" /> ' . (($type_no) ? $lang['YES'] : $lang['ENABLED']) . '</label>';

				$tpl = ($tpl_type_cond[0] == 'yes' || $tpl_type_cond[0] == 'enabled') ? $tpl_yes . '&nbsp;&nbsp;' . $tpl_no : $tpl_no . '&nbsp;&nbsp;' . $tpl_yes;
			break;

			case 'select':
				// @codingStandardsIgnoreStart
				eval('$s_options = ' . str_replace('{VALUE}', $value, $options) . ';');
				// @codingStandardsIgnoreEnd
				$tpl = '<select id="' . $name . '" name="' . $name . '">' . $s_options . '</select>';
			break;

			case 'custom':
				// @codingStandardsIgnoreStart
				eval('$tpl = ' . str_replace('{VALUE}', $value, $options) . ';');
				// @codingStandardsIgnoreEnd
			break;

			default:
			break;
		}

		return $tpl;
	}

	/**
	* Generate the drop down of available language packs
	*/
	function inst_language_select($default = '')
	{
		global $phpbb_root_path, $phpEx;

		$dir = @opendir($phpbb_root_path . 'language');

		if (!$dir)
		{
			$this->error('Unable to access the language directory', __LINE__, __FILE__);
		}

		while ($file = readdir($dir))
		{
			$path = $phpbb_root_path . 'language/' . $file;

			if ($file == '.' || $file == '..' || is_link($path) || is_file($path) || $file == 'CVS')
			{
				continue;
			}

			if (file_exists($path . '/iso.txt'))
			{
				list($displayname, $localname) = @file($path . '/iso.txt');
				$lang[$localname] = $file;
			}
		}
		closedir($dir);

		@asort($lang);
		@reset($lang);

		$user_select = '';
		foreach ($lang as $displayname => $filename)
		{
			$selected = (strtolower($default) == strtolower($filename)) ? ' selected="selected"' : '';
			$user_select .= '<option value="' . $filename . '"' . $selected . '>' . ucwords($displayname) . '</option>';
		}

		return $user_select;
	}
}
