<?php
/**
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @ignore
*/


if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_captcha
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $config;

		$user->add_lang('acp/board');

		include(PHPBB_ROOT_PATH . 'includes/captcha/captcha_factory.' . PHP_EXT);

		$selected = request_var('select_captcha', $config['captcha_plugin']);
		$configure = request_var('configure', false);
		
		// Oh, they are just here for the view
		if (request::is_set('captcha_demo', request::GET))
		{
			$this->deliver_demo($selected);
		}
		
		// Delegate
		if ($configure)
		{
			$config_captcha = phpbb_captcha_factory::get_instance($selected);
			$config_captcha->acp_page($id, $this);
		}
		else
		{
			$captchas = phpbb_captcha_factory::get_captcha_types();

			$config_vars = array(
				'enable_confirm'		=> 'REG_ENABLE',
				'enable_post_confirm'	=> 'POST_ENABLE',
			);

			$this->tpl_name = 'acp_captcha';
			$this->page_title = 'ACP_VC_SETTINGS';
			$form_key = 'acp_captcha';
			add_form_key($form_key);

			$submit = request_var('main_submit', false);

			if ($submit && check_form_key($form_key))
			{
				$config_vars = array_keys($config_vars);
				foreach ($config_vars as $config_var)
				{
					set_config($config_var, request_var($config_var, false));
				}
				if ($selected !== $config['captcha_plugin'])
				{
					// sanity check
					if (isset($captchas['available'][$selected]))
					{
						$old_captcha = phpbb_captcha_factory::get_instance($config['captcha_plugin']);
						$old_captcha->uninstall();
						set_config('captcha_plugin', $selected);
						$new_captcha = phpbb_captcha_factory::get_instance($config['captcha_plugin']);
						$new_captcha->install();
					}
					else
					{
						trigger_error($user->lang['CAPTCHA_UNAVAILABLE'] . adm_back_link($this->u_action));
					}
				}
				trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
			}
			else if ($submit)
			{
				trigger_error($user->lang['FORM_INVALID'] . adm_back_link());
			}
			else
			{
				$captcha_select = '';
				foreach ($captchas['available'] as $value => $title)
				{
					$current = ($selected !== false && $value == $selected) ? ' selected="selected"' : '';
					$captcha_select .= '<option value="' . $value . '"' . $current . '>' . $user->lang[$title] . '</option>';
				}
				foreach ($captchas['unavailable'] as $value => $title)
				{
					$captcha_select .= '<option value="' . $value . '"' . $current . ' class="disabled-option" >' . $user->lang[$title] . '</option>';
				}

				$demo_captcha = phpbb_captcha_factory::get_instance($selected);
				
				foreach ($config_vars as $config_var => $template_var)
				{
					$template->assign_var($template_var, request_var($config_var, $config[$config_var])) ;
				}

				$template->assign_vars(array(
					'CAPTCHA_PREVIEW'	=> $demo_captcha->get_demo_template($id),
					'CAPTCHA_SELECT'	=> $captcha_select,
				));
			}
			
		}
	}
	
	
	/**
	* Entry point for delivering image CAPTCHAs in the ACP.
	*/
	function deliver_demo($selected)
	{
		global $db, $user, $config;
		
		$captcha = phpbb_captcha_factory::get_instance($selected);
		$captcha->init(CONFIRM_REG);
		$captcha->execute_demo();
		garbage_collection();
		exit_handler();
	}
	
	
	
	
}

?>