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
