<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/
define('IN_PHPBB', 1);
define('IN_ADMIN', true);
define('NEED_SID', true);

// Include files
$phpbb_root_path = './../';
$phpbb_admin_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'common.'.$phpEx);
require($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
require($phpbb_root_path . 'includes/functions_module.'.$phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('acp/common');
// End session management

// Did user forget to login? Give 'em a chance to here ...
if ($user->data['user_id'] == ANONYMOUS)
{
	login_box('', $user->lang['LOGIN_ADMIN'], $user->lang['LOGIN_ADMIN_SUCCESS'], true);
}

// Have they authenticated (again) as an admin for this session?
if (!isset($user->data['session_admin']) || !$user->data['session_admin'])
{
	login_box('', $user->lang['LOGIN_ADMIN_CONFIRM'], $user->lang['LOGIN_ADMIN_SUCCESS'], true, false);
}

// Is user any type of admin? No, then stop here, each script needs to
// check specific permissions but this is a catchall
if (!$auth->acl_get('a_'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// Some oft used variables
$safe_mode		= (@ini_get('safe_mode') || @strtolower(ini_get('safe_mode')) == 'on') ? true : false;
$file_uploads 	= (@ini_get('file_uploads') || strtolower(@ini_get('file_uploads')) == 'on') ? true : false;
$module_id		= request_var('i', '');
$mode			= request_var('mode', '');

// Force pagination seperation for admin style
$user->theme['pagination_sep'] = '';

// Set custom template for admin area
$template->set_custom_template($phpbb_admin_path . 'style', 'admin');
$template->assign_var('T_TEMPLATE_PATH', $phpbb_admin_path . 'style');

// Instantiate new module
$module = new p_master();

// Instantiate module system and generate list of available modules
$module->list_modules('acp');

// Select the active module
$module->set_active($module_id, $mode);

// Assign data to the template engine for the list of modules
// We do this before loading the active module for correct menu display in trigger_error
$module->assign_tpl_vars("{$phpbb_admin_path}index.$phpEx$SID");

// Load and execute the relevant module
$module->load_active();

// Generate the page
adm_page_header($module->get_page_title());

$template->set_filenames(array(
	'body' => $module->get_tpl_name())
);

adm_page_footer();

// ---------
// FUNCTIONS
//
function adm_page_header($page_title)
{
	global $config, $db, $user, $template;
	global $phpbb_root_path, $phpbb_admin_path, $phpEx, $SID;

	if (defined('HEADER_INC'))
	{
		return;
	}
	
	define('HEADER_INC', true);

	// gzip_compression
	if ($config['gzip_compress'])
	{
		if (extension_loaded('zlib') && !headers_sent())
		{
			ob_start('ob_gzhandler');
		}
	}

	$template->assign_vars(array(
		'PAGE_TITLE'			=> $page_title,
		'USERNAME'				=> $user->data['username'],
		'ROOT_PATH'				=> $phpbb_admin_path,

		'U_LOGOUT'				=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;mode=logout",
		'U_ADM_INDEX'			=> "{$phpbb_admin_path}index.$phpEx$SID",
		'U_INDEX'				=> "{$phpbb_root_path}index.$phpEx$SID",

		'S_CONTENT_DIRECTION' 	=> $user->lang['DIRECTION'],
		'S_CONTENT_ENCODING' 	=> $user->lang['ENCODING'],
		'S_CONTENT_DIR_LEFT' 	=> $user->lang['LEFT'],
		'S_CONTENT_DIR_RIGHT' 	=> $user->lang['RIGHT'],
		)
	);

	if (!empty($config['send_encoding']))
	{
		header('Content-type: text/html; charset: ' . $user->lang['ENCODING']);
	}
	header('Cache-Control: private, no-cache="set-cookie", pre-check=0, post-check=0');
	header('Expires: 0');
	header('Pragma: no-cache');

	return;
}

function adm_page_footer($copyright_html = true)
{
	global $db, $config, $template, $SID, $user, $auth, $starttime, $phpbb_root_path, $phpEx, $cache;

	// Output page creation time
	if (defined('DEBUG'))
	{
		$mtime = explode(' ', microtime());
		$totaltime = $mtime[0] + $mtime[1] - $starttime;

		if (!empty($_REQUEST['explain']) && $auth->acl_get('a_') && method_exists($db, 'sql_report'))
		{
			$db->sql_report('display');
		}

		$debug_output = sprintf('Time : %.3fs | ' . $db->sql_num_queries() . ' Queries | GZIP : ' .  (($config['gzip_compress']) ? 'On' : 'Off' ) . ' | Load : '  . (($user->load) ? $user->load : 'N/A'), $totaltime);

		if ($auth->acl_get('a_') && defined('DEBUG_EXTRA'))
		{
			if (function_exists('memory_get_usage'))
			{
				if ($memory_usage = memory_get_usage())
				{
					global $base_memory_usage;
					$memory_usage -= $base_memory_usage;
					$memory_usage = ($memory_usage >= 1048576) ? round((round($memory_usage / 1048576 * 100) / 100), 2) . ' ' . $user->lang['MB'] : (($memory_usage >= 1024) ? round((round($memory_usage / 1024 * 100) / 100), 2) . ' ' . $user->lang['KB'] : $memory_usage . ' ' . $user->lang['BYTES']);

					$debug_output .= ' | Memory Usage: ' . $memory_usage;
				}
			}

			$debug_output .= ' | <a href="' . (($_SERVER['REQUEST_URI']) ? htmlspecialchars($_SERVER['REQUEST_URI']) : "index.$phpEx$SID") . ((strpos($_SERVER['REQUEST_URI'], '?') !== false) ? '&amp;' : '?') . 'explain=1">Explain</a>';
		}
	}

	$template->assign_vars(array(
		'DEBUG_OUTPUT'		=> (defined('DEBUG')) ? $debug_output : '',
		'S_COPYRIGHT_HTML'	=> $copyright_html,
		'VERSION'			=> $config['version']
		)
	);

	$template->display('body');

	// Unload cache, must be done before the DB connection if closed
	if (!empty($cache))
	{
		$cache->unload();
	}

	// Close our DB connection.
	$db->sql_close();

	exit;
}

function adm_back_link($u_action)
{
	return '<br /><br /><a href="' . $u_action . '">&laquo; Back to previous page</a>';
}

function build_select($option_ary, $option_default = false)
{
	global $user;

	$html = '';
	foreach ($option_ary as $value => $title)
	{
		$selected = ($option_default !== false && $value == $option_default) ? ' selected="selected"' : '';
		$html .= '<option value="' . $value . '"' . $selected . '>' . $user->lang[$title] . '</option>';
	}

	return $html;
}

function h_radio($name, &$input_ary, $input_default = false, $id = false, $key = false)
{
	global $user;

	$html = '';
	$id_assigned = false;
	foreach ($input_ary as $value => $title)
	{
		$selected = ($input_default !== false && $value == $input_default) ? ' checked="checked"' : '';
		$html .= ($html) ? ' &nbsp; ' : '';
		$html .= '<input type="radio" name="' . $name . '"' . (($id && !$id_assigned) ? ' id="' . $id . '"' : '') . ' value="' . $value . '"' . $selected . (($key) ? ' accesskey="' . $key . '"' : '') . ' class="radio" /> ' . $user->lang[$title];
		$id_assigned = true;
	}

	return $html;
}

function build_cfg_template($tpl_type, $key, &$new, $config_key, $vars)
{
	global $user, $module;

	$tpl = '';
	$name = 'config[' . $config_key . ']';

	switch ($tpl_type[0])
	{
		case 'text':
		case 'password':
			$size = (int) $tpl_type[1];
			$maxlength = (int) $tpl_type[2];

			$tpl = '<input id="' . $key . '" type="' . $tpl_type[0] . '"' . (($size) ? ' size="' . $size . '"' : '') . ' maxlength="' . (($maxlength) ? $maxlength : 255) . '" name="' . $name . '" value="' . $new[$config_key] . '" />';
			break;

		case 'dimension':
			$size = (int) $tpl_type[1];
			$maxlength = (int) $tpl_type[2];

			$tpl = '<input id="' . $key . '" type="text"' . (($size) ? ' size="' . $size . '"' : '') . ' maxlength="' . (($maxlength) ? $maxlength : 255) . '" name="config[' . $config_key . '_height]" value="' . $new[$config_key . '_height'] . '" /> x <input type="text"' . (($size) ? ' size="' . $size . '"' : '') . ' maxlength="' . (($maxlength) ? $maxlength : 255) . '" name="config[' . $config_key . '_width]" value="' . $new[$config_key . '_width'] . '" />';
			break;

		case 'textarea':
			$rows = (int) $tpl_type[1];
			$cols = (int) $tpl_type[2];

			$tpl = '<textarea id="' . $key . '" name="' . $name . '" rows="' . $rows . '" cols="' . $cols . '">' . $new[$config_key] . '</textarea>';
			break;

		case 'radio':
			$key_yes	= ($new[$config_key]) ? ' checked="checked"' : '';
			$key_no		= (!$new[$config_key]) ? ' checked="checked"' : '';

			$tpl_type_cond = explode('_', $tpl_type[1]);
			$type_no = ($tpl_type_cond[0] == 'disabled' || $tpl_type_cond[0] == 'enabled') ? false : true;

			$tpl_no = '<input type="radio" name="' . $name . '" value="0"' . $key_no . ' class="radio" />&nbsp;' . (($type_no) ? $user->lang['NO'] : $user->lang['DISABLED']);
			$tpl_yes = '<input type="radio" id="' . $key . '" name="' . $name . '" value="1"' . $key_yes . ' class="radio" />&nbsp;' . (($type_no) ? $user->lang['YES'] : $user->lang['ENABLED']);

			$tpl = ($tpl_type_cond[0] == 'yes' || $tpl_type_cond[0] == 'enabled') ? $tpl_yes . '&nbsp;&nbsp;' . $tpl_no : $tpl_no . '&nbsp;&nbsp;' . $tpl_yes;
			break;

		case 'select':
		case 'custom':
			
			$return = '';

			if (isset($vars['method']))
			{
				$call = array($module->module, $vars['method']);
			}
			else if (isset($vars['function']))
			{
				$call = $vars['function'];
			}
			else
			{
				break;
			}
			
			if (isset($vars['params']))
			{
				$args = array();
				foreach ($vars['params'] as $value)
				{
					switch ($value)
					{
						case '{CONFIG_VALUE}':
							$value = $new[$config_key];
						break;

						case '{KEY}':
							$value = $key;
						break;
					}

					$args[] = $value;
				}
			}
			else
			{
				$args = array($new[$config_key], $key);
			}
			
			$return = call_user_func_array($call, $args);

			if ($tpl_type[0] == 'select')
			{
				$tpl = '<select id="' . $key . '" name="' . $name . '">' . $return . '</select>';
			}
			else
			{
				$tpl = $return;
			}

			break;

		default:
			break;
	}

	if (isset($vars['append']))
	{
		$tpl .= $vars['append'];
	}
	
	return $tpl;
}

?>