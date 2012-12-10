<?php
/**
*
* @package acp
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
*/
define('IN_PHPBB', true);
define('ADMIN_START', true);
define('NEED_SID', true);

// Include files
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'common.' . $phpEx);
require($phpbb_root_path . 'includes/functions_acp.' . $phpEx);
require($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
require($phpbb_root_path . 'includes/functions_module.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('acp/common');
// End session management

// Have they authenticated (again) as an admin for this session?
if (!isset($user->data['session_admin']) || !$user->data['session_admin'])
{
	login_box('', $user->lang['LOGIN_ADMIN_CONFIRM'], $user->lang['LOGIN_ADMIN_SUCCESS'], true, false);
}

// Is user any type of admin? No, then stop here, each script needs to
// check specific permissions but this is a catchall
if (!$auth->acl_get('a_'))
{
	trigger_error('NO_ADMIN');
}

// We define the admin variables now, because the user is now able to use the admin related features...
define('IN_ADMIN', true);
$phpbb_admin_path = (defined('PHPBB_ADMIN_PATH')) ? PHPBB_ADMIN_PATH : './';

// Some oft used variables
$safe_mode		= (@ini_get('safe_mode') == '1' || strtolower(@ini_get('safe_mode')) === 'on') ? true : false;
$file_uploads	= (@ini_get('file_uploads') == '1' || strtolower(@ini_get('file_uploads')) === 'on') ? true : false;
$module_id		= request_var('i', '');
$mode			= request_var('mode', '');

// Set custom style for admin area
$phpbb_style->set_ext_dir_prefix('adm/');
$phpbb_style->set_custom_style('admin', $phpbb_admin_path . 'style', array(), '');
$template->assign_var('T_ASSETS_PATH', $phpbb_root_path . 'assets');
$template->assign_var('T_TEMPLATE_PATH', $phpbb_admin_path . 'style');

// Instantiate new module
$module = new p_master();

// Instantiate module system and generate list of available modules
$module->list_modules('acp');

// Select the active module
$module->set_active($module_id, $mode);

// Assign data to the template engine for the list of modules
// We do this before loading the active module for correct menu display in trigger_error
$module->assign_tpl_vars(append_sid("{$phpbb_admin_path}index.$phpEx"));

// Load and execute the relevant module
$module->load_active();

// Generate the page
adm_page_header($module->get_page_title());

$template->set_filenames(array(
	'body' => $module->get_tpl_name(),
));

adm_page_footer();
