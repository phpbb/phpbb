<?
/**
*
* @package VC
* @version $Id: $
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

include_once(PHPBB_ROOT_PATH . "includes/captcha/plugins/captcha_abstract." . PHP_EXT);

class phpbb_captcha_nogd extends phpbb_default_captcha implements phpbb_captcha_plugin
{

	function __construct()
	{
		include_once(PHPBB_ROOT_PATH . "includes/captcha/captcha_non_gd." . PHP_EXT);
	}
	
	public static function get_instance()
	{
		return new phpbb_captcha_nogd();
	}

	static function is_available()
	{
		return true;
	}
	
	static function get_name()
	{
		global $user;
		
		return 'CAPTCHA_NO_GD'; 
	}
	
	static function get_class_name()
	{
		return 'phpbb_captcha_nogd';
	}
	
	
	function acp_page($id, &$module)
	{
		global $user;
		
		trigger_error($user->lang['CAPTCHA_NO_OPTIONS'] . adm_back_link($module->u_action));
	}
}

