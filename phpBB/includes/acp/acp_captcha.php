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

		$config_vars = array('enable_confirm'			=> 'REG_ENABLE',
						'enable_post_confirm'			=> 'POST_ENABLE',
						'policy_overlap'				=> 'OVERLAP_ENABLE',
						'policy_overlap_noise_pixel'	=> 'OVERLAP_NOISE_PIXEL',
						'policy_overlap_noise_line'		=> 'OVERLAP_NOISE_LINE_ENABLE',
						'policy_entropy'				=> 'ENTROPY_ENABLE',
						'policy_entropy_noise_pixel'	=> 'ENTROPY_NOISE_PIXEL',
						'policy_entropy_noise_line'		=> 'ENTROPY_NOISE_LINE_ENABLE',
						'policy_shape'					=> 'SHAPE_ENABLE',
						'policy_shape_noise_pixel'		=> 'SHAPE_NOISE_PIXEL',
						'policy_shape_noise_line'		=> 'SHAPE_NOISE_LINE_ENABLE',
						'policy_3dbitmap'				=> 'THREEDBITMAP_ENABLE',
						'policy_cells'					=> 'CELLS_ENABLE',
						'policy_stencil'				=> 'STENCIL_ENABLE',
						'policy_composite'				=> 'COMPOSITE_ENABLE'
					);

		$policy_modules = array('policy_entropy', 'policy_3dbitmap', 'policy_overlap', 'policy_shape', 'policy_cells', 'policy_stencil', 'policy_composite');

		switch ($mode)
		{
			case 'visual':
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

					foreach ($config_vars as $config_var => $template_var)
					{
						$array[$template_var] = $config[$config_var];
					}
					$template->assign_vars($array);

					
					if (@extension_loaded('gd'))
					{
						$template->assign_var('GD', true);
						foreach ($policy_modules as $module_name)
						{
							$template->assign_var('U_' . strtoupper($module_name), sprintf($user->lang['CAPTCHA_EXPLAIN'], '<a href="' . append_sid("{$phpbb_root_path}adm/index.$phpEx", 'i=captcha&amp;mode=img&amp;policy=' . $module_name) . '" target="_blank">', '</a>'));
						}
						if (function_exists('imagettfbbox') && function_exists('imagettftext'))
						{
							$template->assign_var('TTF', true);
						}
					}
				}
			break;

			case 'img':
				$policy = request_var('policy', '');

				if (!@extension_loaded('gd'))
				{
					trigger_error($user->lang['NO_GD']);
				}

				if (!($policy === 'policy_entropy' || $policy === 'policy_3dbitmap') && (!function_exists('imagettfbbox') || !function_exists('imagettftext')))
				{
					trigger_error($user->lang['NO_TTF']);
				}

				if (!in_array($policy, $policy_modules))
				{
					trigger_error($user->lang['BAD_POLICY']);
				}

				$user->add_lang('ucp');

				include($phpbb_root_path . 'includes/captcha/captcha_gd.' . $phpEx);

				$captcha = new captcha();
				$captcha->execute(gen_rand_string(), $policy);
			break;
		}
	}
}

?>