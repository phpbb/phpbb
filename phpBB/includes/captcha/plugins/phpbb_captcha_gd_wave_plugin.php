<?php
/**
*
* @package VC
* @version $Id$
* @copyright (c) 2006 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Placeholder for autoload
*/
include_once(PHPBB_ROOT_PATH . 'includes/captcha/plugins/captcha_abstract.' . PHP_EXT);

class phpbb_captcha_gd_wave extends phpbb_default_captcha implements phpbb_captcha_plugin
{

	function __construct()
	{
		include_once(PHPBB_ROOT_PATH . 'includes/captcha/captcha_gd_wave.' . PHP_EXT);
	}

	public static function get_instance()
	{
		return new phpbb_captcha_gd_wave();
	}

	static function is_available()
	{
		return (@extension_loaded('gd') || can_load_dll('gd'));
	}

	static function get_name()
	{
		return 'CAPTCHA_GD_WAVE';
	}

	static function get_class_name()
	{
		return 'phpbb_captcha_gd_wave';
	}

	function acp_page($id, &$module)
	{
		global $config, $db, $template, $user;
		
		trigger_error($user->lang['CAPTCHA_NO_OPTIONS'] . adm_back_link($module->u_action));
	}
}

?>