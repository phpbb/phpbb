<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
define('ADMIN_START', true);
if (!defined('PHPBB_ROOT_PATH')) define('PHPBB_ROOT_PATH', './../');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
include(PHPBB_ROOT_PATH . 'common.' . PHP_EXT);

// Start session management
phpbb::$user->session_begin(false);
phpbb::$acl->init(phpbb::$user->data);
phpbb::$user->setup();

// Set custom template for admin area
phpbb::$template->set_custom_template(PHPBB_ROOT_PATH . phpbb::$base_config['admin_folder'] . '/style', 'admin');

phpbb::$template->set_filenames(array(
	'body' => 'colour_swatch.html',
));

$form = request_var('form', '');
$name = request_var('name', '');

// We validate form and name here, only id/class allowed
$form = (!preg_match('/^[a-z0-9_-]+$/i', $form)) ? '' : $form;
$name = (!preg_match('/^[a-z0-9_-]+$/i', $name)) ? '' : $name;

phpbb::$template->assign_vars(array(
	'OPENER'		=> $form,
	'NAME'			=> $name,
	'T_IMAGES_PATH'	=> PHPBB_ROOT_PATH . 'images/',

	'S_USER_LANG'			=> phpbb::$user->lang['USER_LANG'],
	'S_CONTENT_DIRECTION'	=> phpbb::$user->lang['DIRECTION'],
	'S_CONTENT_ENCODING'	=> 'UTF-8',
));

phpbb::$template->display('body');

garbage_collection();

?>