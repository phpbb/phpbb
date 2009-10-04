<?php
/**
*
* @package VC
* @version $Id$
* @copyright (c) 2006, 2008 phpBB Group
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
if (!class_exists('phpbb_default_captcha'))
{
	include($phpbb_root_path . 'includes/captcha/plugins/captcha_abstract.' . $phpEx);
}

/**
* @package VC
*/
class phpbb_captcha_gd extends phpbb_default_captcha
{

	var $captcha_vars = array(
		'captcha_gd_x_grid'				=> 'CAPTCHA_GD_X_GRID',
		'captcha_gd_y_grid'				=> 'CAPTCHA_GD_Y_GRID',
		'captcha_gd_foreground_noise'	=> 'CAPTCHA_GD_FOREGROUND_NOISE',
//		'captcha_gd'					=> 'CAPTCHA_GD_PREVIEWED',
		'captcha_gd_wave'				=> 'CAPTCHA_GD_WAVE',
		'captcha_gd_3d_noise'			=> 'CAPTCHA_GD_3D_NOISE',
		'captcha_gd_fonts'				=> 'CAPTCHA_GD_FONTS',
	);

	function phpbb_captcha_gd()
	{
		global $phpbb_root_path, $phpEx;

		if (!class_exists('captcha'))
		{
			include($phpbb_root_path . 'includes/captcha/captcha_gd.' . $phpEx);
		}
	}

	function &get_instance()
	{
		$instance =& new phpbb_captcha_gd();
		return $instance;
	}

	function is_available()
	{
		global $phpbb_root_path, $phpEx;

		if (@extension_loaded('gd'))
		{
			return true;
		}

		if (!function_exists('can_load_dll'))
		{
			include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
		}

		return can_load_dll('gd');
	}

	/**
	*  API function
	*/
	function has_config()
	{
		return true;
	}

	function get_name()
	{
		return 'CAPTCHA_GD';
	}

	function get_class_name()
	{
		return 'phpbb_captcha_gd';
	}

	function acp_page($id, &$module)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('acp/board');

		$config_vars = array(
			'enable_confirm'		=> 'REG_ENABLE',
			'enable_post_confirm'	=> 'POST_ENABLE',
			'confirm_refresh'		=> 'CONFIRM_REFRESH',
			'captcha_gd'			=> 'CAPTCHA_GD',
		);

		$module->tpl_name = 'captcha_gd_acp';
		$module->page_title = 'ACP_VC_SETTINGS';
		$form_key = 'acp_captcha';
		add_form_key($form_key);

		$submit = request_var('submit', '');

		if ($submit && check_form_key($form_key))
		{
			$captcha_vars = array_keys($this->captcha_vars);
			foreach ($captcha_vars as $captcha_var)
			{
				$value = request_var($captcha_var, 0);
				if ($value >= 0)
				{
					set_config($captcha_var, $value);
				}
			}

			add_log('admin', 'LOG_CONFIG_VISUAL');
			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($module->u_action));
		}
		else if ($submit)
		{
			trigger_error($user->lang['FORM_INVALID'] . adm_back_link($module->u_action));
		}
		else
		{
			foreach ($this->captcha_vars as $captcha_var => $template_var)
			{
				$var = (isset($_REQUEST[$captcha_var])) ? request_var($captcha_var, 0) : $config[$captcha_var];
				$template->assign_var($template_var, $var);
			}

			$template->assign_vars(array(
				'CAPTCHA_PREVIEW'	=> $this->get_demo_template($id),
				'CAPTCHA_NAME'		=> $this->get_class_name(),
				'U_ACTION'			=> $module->u_action,
			));
		}
	}

	function execute_demo()
	{
		global $config;

		$config_old = $config;
		foreach ($this->captcha_vars as $captcha_var => $template_var)
		{
				$config[$captcha_var] = request_var($captcha_var, (int) $config[$captcha_var]);
		}
		parent::execute_demo();
		$config = $config_old;
	}

}

?>