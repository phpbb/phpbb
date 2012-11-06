<?php
/**
*
* @package VC
* @copyright (c) 2006, 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
if (!class_exists('phpbb_default_captcha', false))
{
	include($phpbb_root_path . 'includes/captcha/plugins/captcha_abstract.' . $phpEx);
}

/**
* @package VC
*/
class phpbb_captcha_nogd extends phpbb_default_captcha
{

	function phpbb_captcha_nogd()
	{
		global $phpbb_root_path, $phpEx;

		if (!class_exists('captcha'))
		{
			include_once($phpbb_root_path . 'includes/captcha/captcha_non_gd.' . $phpEx);
		}
	}

	static public function get_instance()
	{
		$instance = new phpbb_captcha_nogd();
		return $instance;
	}

	static public function is_available()
	{
		return true;
	}

	static public function get_name()
	{
		return 'CAPTCHA_NO_GD';
	}

	function get_class_name()
	{
		return 'phpbb_captcha_nogd';
	}

	function acp_page($id, &$module)
	{
		global $user;

		trigger_error($user->lang['CAPTCHA_NO_OPTIONS'] . adm_back_link($module->u_action));
	}
}
