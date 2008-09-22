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

class phpbb_captcha_gd extends phpbb_default_captcha implements phpbb_captcha_plugin
{

	function __construct()
	{
		include_once(PHPBB_ROOT_PATH . 'includes/captcha/captcha_gd.' . PHP_EXT);
	}

	public static function get_instance()
	{
		return new phpbb_captcha_gd();
	}

	static function is_available()
	{
		return (@extension_loaded('gd') || can_load_dll('gd'));
	}

	static function get_name()
	{
		return 'CAPTCHA_GD';
	}

	static function get_class_name()
	{
		return 'phpbb_captcha_gd';
	}

	function acp_page($id, &$module)
	{
		global $config, $db, $template, $user;

		$captcha_vars = array(
			'captcha_gd_x_grid'				=> 'CAPTCHA_GD_X_GRID',
			'captcha_gd_y_grid'				=> 'CAPTCHA_GD_Y_GRID',
			'captcha_gd_foreground_noise'	=> 'CAPTCHA_GD_FOREGROUND_NOISE',
			'captcha_gd'					=> 'CAPTCHA_GD_PREVIEWED'
		);

		$module->tpl_name = 'captcha_gd_acp';
		$module->page_title = 'ACP_VC_SETTINGS';
		$form_key = 'acp_captcha';
		add_form_key($form_key);

		$submit = request_var('submit', '');

		if ($submit && check_form_key($form_key))
		{
			$captcha_vars = array_keys($captcha_vars);
			foreach ($captcha_vars as $captcha_var)
			{
				$value = request_var($captcha_var, 0);
				if ($value >= 0)
				{
					set_config($captcha_var, $value);
				}
			}
			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($module->u_action));
		}
		else if ($submit)
		{
			trigger_error($user->lang['FORM_INVALID'] . adm_back_link($module->u_action));
		}
		else
		{
			foreach ($captcha_vars as $captcha_var => $template_var)
			{
				$var = (isset($_REQUEST[$captcha_var])) ? request_var($captcha_var, 0) : $config[$captcha_var];
				$template->assign_var($template_var, $var);
			}
			$template->assign_vars(array(
				'CAPTCHA_PREVIEW'	=> $this->get_demo_template($id),
				'CAPTCHA_NAME'		=> $this->get_class_name(),
			));

		}
	}
}

?>