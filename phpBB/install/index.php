<?php
/**
*
* @package install
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**#@+
* @ignore
*/
define('IN_PHPBB', true);
define('IN_INSTALL', true);
/**#@-*/

if (!defined('PHPBB_ROOT_PATH')) define('PHPBB_ROOT_PATH', './../');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));

// Include bootstrap
include PHPBB_ROOT_PATH . 'includes/core/bootstrap.' . PHP_EXT;

// Includes functions for the installer
require PHPBB_ROOT_PATH . 'includes/functions_install.' . PHP_EXT;

// Set time limit to 0
@set_time_limit(0);

/**
* @todo get memory limit and display notice if it is too low for a conversion (only within conversion)
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
*/

// Initialize some common config variables
phpbb::$config += array(
	'load_tplcompile'	=> true,
	'cookie_name'		=> '',
);

// Register the template and the user object
phpbb::register('template');
phpbb::register('user', false, false, 'db', PHPBB_ROOT_PATH . 'language/');

// Init "loose" user session
phpbb::$user->session_begin();

// Now set users language
phpbb::$user->set_language(request_var('language', ''));

// And also add the install language file
phpbb::$user->add_lang('install');

$mode = request_var('mode', 'overview');
$sub = request_var('sub', '');

// Set PHP error handler to ours
set_error_handler(defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler');

phpbb::$template->set_custom_template('../adm/style', 'admin');
phpbb::$template->assign_var('T_TEMPLATE_PATH', '../adm/style');

$install = new module();

$install->create('install', 'index.' . PHP_EXT, $mode, $sub);
$install->load();

// Generate the page
$install->page_header();
$install->generate_navigation();

phpbb::$template->set_filenames(array(
	'body' => $install->get_tpl_name())
);

$install->page_footer();

/**
* @package install
*/
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
			if (preg_match('#^install_(.*?)\.' . PHP_EXT . '$#', $file))
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
			// Check any module pre-reqs
			if ($row['module_reqs'] != '')
			{
			}

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
		global $stage;

		phpbb::$template->assign_vars(array(
			'PAGE_TITLE'			=> $this->get_page_title(),
			'T_IMAGE_PATH'			=> PHPBB_ROOT_PATH . 'adm/images/',

			'S_CONTENT_DIRECTION' 	=> phpbb::$user->lang['DIRECTION'],
			'S_CONTENT_FLOW_BEGIN'	=> (phpbb::$user->lang['DIRECTION'] == 'ltr') ? 'left' : 'right',
			'S_CONTENT_FLOW_END'	=> (phpbb::$user->lang['DIRECTION'] == 'ltr') ? 'right' : 'left',
			'S_CONTENT_ENCODING' 	=> 'UTF-8',

			'S_USER_LANG'			=> phpbb::$user->lang['USER_LANG'],
			)
		);

		header('Content-type: text/html; charset=UTF-8');
		header('Cache-Control: private, no-cache="set-cookie"');
		header('Expires: 0');
		header('Pragma: no-cache');

		return;
	}

	/**
	* Output the standard page footer
	*/
	function page_footer()
	{
		phpbb::$template->display('body');

		// Close our DB connection.
		if (phpbb::registered('db'))
		{
			phpbb::$db->sql_close();
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
		if (!isset($this->module->page_title))
		{
			return '';
		}

		return (isset(phpbb::$user->lang[$this->module->page_title])) ? phpbb::$user->lang[$this->module->page_title] : $this->module->page_title;
	}

	/**
	* Generate the navigation tabs
	*/
	function generate_navigation()
	{
		if (is_array($this->module_ary))
		{
			@ksort($this->module_ary);
			foreach ($this->module_ary as $cat_ary)
			{
				$cat = $cat_ary['name'];
				$l_cat = (!empty($lang['CAT_' . $cat])) ? $lang['CAT_' . $cat] : preg_replace('#_#', ' ', $cat);
				$cat = strtolower($cat);
				$url = $this->module_url . "?mode=$cat&amp;language=" . phpbb::$user->lang_name;

				if ($this->mode == $cat)
				{
					phpbb::$template->assign_block_vars('t_block1', array(
						'L_TITLE'		=> $l_cat,
						'S_SELECTED'	=> true,
						'U_TITLE'		=> $url,
					));

					if (is_array($this->module_ary[$this->id]['subs']))
					{
						$subs = $this->module_ary[$this->id]['subs'];
						foreach ($subs as $option)
						{
							$l_option = (!empty(phpbb::$user->lang['SUB_' . $option])) ? phpbb::$user->lang['SUB_' . $option] : preg_replace('#_#', ' ', $option);
							$option = strtolower($option);
							$url = $this->module_url . '?mode=' . $this->mode . "&amp;sub=$option&amp;language=" . phpbb::$user->lang_name;

							phpbb::$template->assign_block_vars('l_block1', array(
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
							$l_option = (!empty(phpbb::$user->lang['STAGE_' . $option])) ? phpbb::$user->lang['STAGE_' . $option] : preg_replace('#_#', ' ', $option);
							$option = strtolower($option);
							$matched = ($this->sub == $option) ? true : $matched;

							phpbb::$template->assign_block_vars('l_block2', array(
								'L_TITLE'		=> $l_option,
								'S_SELECTED'	=> ($this->sub == $option),
								'S_COMPLETE'	=> !$matched,
							));
						}
					}
				}
				else
				{
					phpbb::$template->assign_block_vars('t_block1', array(
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
	function error($error, $line, $file)
	{
		phpbb::$template->assign_block_vars('checks', array(
			'S_LEGEND'	=> true,
			'LEGEND'	=> phpbb::$user->lang['INST_ERR'],
		));

		phpbb::$template->assign_block_vars('checks', array(
			'TITLE'		=> basename($file) . ' [ ' . $line . ' ]',
			'RESULT'	=> '<b style="color:red">' . $error . '</b>',
		));

		return;
	}

	/**
	* Output an error message for a database related problem
	* If skip is true, return and continue execution, else exit
	*/
	function db_error($error, $sql, $line, $file)
	{
		phpbb::$template->assign_block_vars('checks', array(
			'S_LEGEND'	=> true,
			'LEGEND'	=> phpbb::$user->lang['INST_ERR_FATAL'],
		));

		phpbb::$template->assign_block_vars('checks', array(
			'TITLE'		=> basename($file) . ' [ ' . $line . ' ]',
			'RESULT'	=> '<b style="color:red">' . $error . '</b><br />&#187; SQL:' . $sql,
		));

		return;
	}

	/**
	* Generate the relevant HTML for an input field and the associated label and explanatory text
	*/
	function input_field($name, $type, $value='', $options='')
	{
		$tpl_type = explode(':', $type);
		$tpl = '';

		switch ($tpl_type[0])
		{
			case 'text':
			case 'password':
				$size = (int) $tpl_type[1];
				$maxlength = (int) $tpl_type[2];

				$tpl = '<input id="' . $name . '" type="' . $tpl_type[0] . '"' . (($size) ? ' size="' . $size . '"' : '') . ' maxlength="' . (($maxlength) ? $maxlength : 255) . '" name="' . $name . '" value="' . $value . '" />';
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

				$tpl_no = '<label><input type="radio" name="' . $name . '" value="0"' . $key_no . ' class="radio" /> ' . (($type_no) ? phpbb::$user->lang['NO'] : phpbb::$user->lang['DISABLED']) . '</label>';
				$tpl_yes = '<label><input type="radio" name="' . $name . '" value="1"' . $key_yes . ' class="radio" /> ' . (($type_no) ? phpbb::$user->lang['YES'] : phpbb::$user->lang['ENABLED']) . '</label>';

				$tpl = ($tpl_type_cond[0] == 'yes' || $tpl_type_cond[0] == 'enabled') ? $tpl_yes . '&nbsp;&nbsp;' . $tpl_no : $tpl_no . '&nbsp;&nbsp;' . $tpl_yes;
			break;

			case 'select':
				eval('$s_options = ' . str_replace('{VALUE}', $value, $options) . ';');
				$tpl = '<select id="' . $name . '" name="' . $name . '">' . $s_options . '</select>';
			break;

			case 'custom':
				eval('$tpl = ' . str_replace('{VALUE}', $value, $options) . ';');
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
		$dir = @opendir(PHPBB_ROOT_PATH . 'language');

		if (!$dir)
		{
			$this->error('Unable to access the language directory', __LINE__, __FILE__);
		}

		while ($file = readdir($dir))
		{
			$path = PHPBB_ROOT_PATH . 'language/' . $file;

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

?>