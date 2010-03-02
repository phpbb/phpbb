<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*/

/**
* @package acp
*/
class acp_captcha
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('acp/board');

		$config_vars = array(
			'enable_confirm'		=> 'REG_ENABLE',
			'enable_post_confirm'	=> 'POST_ENABLE',
			'captcha_gd'			=> 'CAPTCHA_GD',
			'captcha_gd_noise'		=> 'CAPTCHA_GD_NOISE',
		);

		$this->tpl_name = 'acp_captcha';
		$this->page_title = 'ACP_VC_SETTINGS';
		$submit = request_var('submit', '');
		if ($submit)
		{
			$config_vars = array_keys($config_vars);
			foreach ($config_vars as $config_var)
			{
				set_config($config_var, request_var($config_var, ''));
			}
			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}
		else
		{
			$array = array();

			if (@extension_loaded('gd') && function_exists('imagettfbbox') && function_exists('imagettftext'))
			{
				$template->assign_var('GD', true);
			}
			foreach ($config_vars as $config_var => $template_var)
			{
				$template->assign_var($template_var, $config[$config_var]);
			}
		}
	}
}

?>