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
define('IN_PHPBB', true);
define('IN_INSTALL', true);

$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

@set_time_limit(120);

include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

// Try and load an appropriate language if required
$language = request_var('language', '');

if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && !$language)
{
	$accept_lang_ary = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
	foreach ($accept_lang_ary as $accept_lang)
	{
		// Set correct format ... guess full xx_YY form
		$accept_lang = substr($accept_lang, 0, 2) . '_' . strtoupper(substr($accept_lang, 3, 2));

		if (file_exists($phpbb_root_path . 'language/' . $accept_lang))
		{
			$language = $accept_lang;
			break;
		}
		else
		{
			// No match on xx_YY so try xx
			$accept_lang = substr($accept_lang, 0, 2);
			if (file_exists($phpbb_root_path . 'language/' . $accept_lang))
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
	while (($file = readdir($dir)) !== false)
	{
		$path = $phpbb_root_path . 'language/' . $file;

		if (!is_file($path) && !is_link($path) && file_exists($path . '/iso.txt'))
		{
			$language = $file;
			break;
		}
	}
}

$user->lang_path = $phpbb_root_path . 'language/' . $language . '/';
$user->add_lang(array('common', 'acp/common', 'acp/board', 'install', 'posting'));

$mode = request_var('mode', 'overview');
$sub = request_var('sub', '');

$template->set_custom_template($phpbb_root_path . 'adm/style', 'admin');
$template->assign_var('T_TEMPLATE_PATH', $phpbb_root_path . 'adm/style');

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

/**
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

	// Private methods, should not be overwritten
	function create($module_type, $module_url, $selected_mod = false, $selected_submod = false)
	{
		global $db, $config, $phpEx;

		$module = array();

		// Grab module information using Bart's "neat-o-module" system (tm)
		$dir = @opendir('.');

		$setmodules = 1;
		while (($file = readdir($dir)) !== false)
		{
			if (preg_match('#^install_(.*?)\.' . $phpEx . '$#', $file))
			{
				include($file);
			}
		}
		@closedir($dir);

		unset($setmodules);

		if (!sizeof($module))
		{
			$this->error('No installation modules found', __LINE__, __FILE__);
		}

		foreach ($module as $row)
		{
			// Check any module pre-reqs
			if ($row['module_reqs'] != '')
			{
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

	// Load and run the relevant module if applicable
	function load($mode = false, $run = true)
	{
		global $phpbb_root_path, $phpEx;

		if ($run)
		{
			if (!empty($mode))
			{
				$this->mode = $mode;
			}

			eval("\$this->module = new $this->filename(\$this);");
			if (method_exists($this->module, 'main'))
			{
				$this->module->main($this->mode, $this->sub);
			}
		}
	} // END load

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

		global $template, $user, $stage;

		$template->assign_vars(array(
			'L_INSTALL_PANEL'		=> $user->lang['INSTALL_PANEL'],
			'PAGE_TITLE'			=> $this->get_page_title(),

			'META'					=> $this->get_meta(),

			'S_CONTENT_DIRECTION' 	=> $user->lang['DIRECTION'],
			'S_CONTENT_ENCODING' 	=> $user->lang['ENCODING'],
			'S_CONTENT_DIR_LEFT' 	=> $user->lang['LEFT'],
			'S_CONTENT_DIR_RIGHT' 	=> $user->lang['RIGHT'],
			)
		);

		if (!empty($user->lang['ENCODING']))
		{
			header('Content-type: text/html; charset: ' . $user->lang['ENCODING']);
		}
		header('Cache-Control: private, no-cache="set-cookie", pre-check=0, post-check=0');
		header('Expires: 0');
		header('Pragma: no-cache');

		return;
	}

	/**
	* Output the standard page footer
	*/
	function page_footer()
	{
		global $template;

		$template->display('body');
	
		// Close our DB connection.
		if (isset($db))
		{
			$db->sql_close();
		}

		exit;
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
		global $user;

		if (!isset($this->module->page_title))
		{
			return '';
		}

		return (isset($user->lang[$this->module->page_title])) ? $user->lang[$this->module->page_title] : $this->module->page_title;
	}

	/**
	* Returns the desired meta tags for the page
	*/
	function get_meta()
	{
		return (isset($this->module->meta)) ? $this->module->meta : '';
	}

	/**
	* Generate the navigation tabs
	*/
	function generate_navigation()
	{
		global $user, $template, $phpEx;

		if (is_array($this->module_ary))
		{
			@ksort($this->module_ary);
			foreach ($this->module_ary as $cat_ary)
			{
				$cat = $cat_ary['name'];
				$l_cat = (!empty($user->lang['CAT_' . $cat])) ? $user->lang['CAT_' . $cat] : preg_replace('#_#', ' ', $cat);
				$cat = strtolower($cat);
				$url = $this->module_url . '?mode=' . $cat;
				
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
							$l_option = (!empty($user->lang['SUB_' . $option])) ? $user->lang['SUB_' . $option] : preg_replace('#_#', ' ', $option);
							$option = strtolower($option);
							$url = $this->module_url . '?mode=' . $this->mode . '&amp;sub=' . $option;

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
							$l_option = (!empty($user->lang['STAGE_' . $option])) ? $user->lang['STAGE_' . $option] : preg_replace('#_#', ' ', $option);
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
	* @todo: Really should change the caption based on $skip and calling code at some point
	*/
	function error($error, $line, $file, $skip = false)
	{
		global $user, $db;

		if (!$skip)
		{
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
			echo '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">';
			echo '<head>';
			echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
			echo '<title>' . $user->lang['INST_ERR_FATAL'] . '</title>';
			echo '<link href="../adm/style/admin.css" rel="stylesheet" type="text/css" media="screen" />';
			echo '</head>';
			echo '<body id="errorpage">';
			echo '<div id="wrap">';
			echo '	<div id="page-header">';
			echo '	</div>';
			echo '	<div id="page-body">';
			echo '		<div class="panel">';
			echo '			<span class="corners-top"><span></span></span>';
			echo '			<div id="content">';
			echo '				<h1>' . $user->lang['INST_ERR_FATAL'] . '</h1>';
		}

		echo '		<p>' . $user->lang['INST_ERR_FATAL'] . "</p>\n";
		echo '		<p>' . basename($file) . ' [ ' . $line . " ]</p>\n";
		echo '		<p><b>' . $error . "</b></p>\n";

		if ($skip)
		{
			return;
		}

		echo '			</div>';
		echo '			<span class="corners-bottom"><span></span></span>';
		echo '		</div>';
		echo '	</div>';
		echo '	<div id="page-footer">';
		echo '		Powered by phpBB &copy; ' . date('Y') . ' <a href="http://www.phpbb.com/">phpBB Group</a>';
		echo '	</div>';
		echo '</div>';
		echo '</body>';
		echo '</html>';

		if (isset($db))
		{
			$db->sql_close();
		}

		exit;
	}

	/**
	* Output an error message for a database related problem
	* If skip is true, return and continue execution, else exit
	*/
	function db_error($error, $sql, $line, $file, $skip = false)
	{
		global $user, $db;

		$this->page_header();

		echo '		<h2 style="color:red;text-align:center">' . $user->lang['INST_ERR_FATAL'] . "</h2>\n";
		echo '		<p>' . $user->lang['INST_ERR_FATAL_DB'] . "</p>\n";
		echo '		<p>' . basename($file) . ' [ ' . $line . " ]</p>\n";
		echo '		<p>SQL : ' . $sql . "</p>\n";
		echo '		<p><b>' . $error . "</b></p>\n";

		if ($skip)
		{
			return;
		}

		$db->sql_close();
		$this->page_footer();
		exit;
	}

	/**
	* Generate the relevant HTML for an input field and the assosciated label and explanatory text
	*/
	function input_field($name, $type, $value='', $options='')
	{
		global $user;
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

				$tpl = '<textarea id="' . $key . '" name="' . $name . '" rows="' . $rows . '" cols="' . $cols . '">' . $value . '</textarea>';
			break;

			case 'radio':
				$key_yes	= ($value) ? ' checked="checked"' : '';
				$key_no		= (!$value) ? ' checked="checked"' : '';

				$tpl_type_cond = explode('_', $tpl_type[1]);
				$type_no = ($tpl_type_cond[0] == 'disabled' || $tpl_type_cond[0] == 'enabled') ? false : true;

				$tpl_no = '<input type="radio" name="' . $name . '" value="0"' . $key_no . ' class="radio" />&nbsp;' . (($type_no) ? $user->lang['NO'] : $user->lang['DISABLED']);
				$tpl_yes = '<input type="radio" name="' . $name . '" value="1"' . $key_yes . ' class="radio" />&nbsp;' . (($type_no) ? $user->lang['YES'] : $user->lang['ENABLED']);

				$tpl = ($tpl_type_cond[0] == 'yes' || $tpl_type_cond[0] == 'enabled') ? $tpl_yes . '&nbsp;&nbsp;' . $tpl_no : $tpl_no . '&nbsp;&nbsp;' . $tpl_yes;
			break;

			case 'select':
				eval('$s_options = ' . str_replace('{VALUE}', $value, $options) . ';');
				$tpl = '<select name="' . $name . '">' . $s_options . '</select>';
			break;

			case 'custom':
				eval('$tpl = ' . str_replace('{VALUE}', $value, $options) . ';');
			break;

			default:
			break;
		}

		return $tpl;
	}
}
?>