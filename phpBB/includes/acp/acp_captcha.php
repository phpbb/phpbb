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
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('acp/board');

		include($phpbb_root_path . 'includes/captcha/captcha_factory.' . $phpEx);
		$captchas = phpbb_captcha_factory::get_captcha_types();

		$selected = request_var('select_captcha', $config['captcha_plugin']);
		$selected = (isset($captchas['available'][$selected]) || isset($captchas['unavailable'][$selected])) ? $selected : $config['captcha_plugin'];
		$configure = request_var('configure', false);


		// Oh, they are just here for the view
		if (isset($_GET['captcha_demo']))
		{
			$this->deliver_demo($selected);
		}

		// Delegate
		if ($configure)
		{
			$config_captcha =& phpbb_captcha_factory::get_instance($selected);
			$config_captcha->acp_page($id, $this);
		}
		else
		{
			$config_vars = array(
				'enable_confirm'		=> array('tpl' => 'REG_ENABLE', 'default' => false),
				'enable_post_confirm'	=> array('tpl' => 'POST_ENABLE', 'default' => false),
				'confirm_refresh'		=> array('tpl' => 'CONFIRM_REFRESH', 'default' => false),
				'max_reg_attempts'		=> array('tpl' => 'REG_LIMIT', 'default' => 0),
				'max_login_attempts'		=> array('tpl' => 'MAX_LOGIN_ATTEMPTS', 'default' => 0),
			);

			$this->tpl_name = 'acp_captcha';
			$this->page_title = 'ACP_VC_SETTINGS';
			$form_key = 'acp_captcha';
			add_form_key($form_key);

			$submit = request_var('main_submit', false);

			if ($submit && check_form_key($form_key))
			{
				foreach ($config_vars as $config_var => $options)
				{
					set_config($config_var, request_var($config_var, $options['default']));
				}

				if ($selected !== $config['captcha_plugin'])
				{
					// sanity check
					if (isset($captchas['available'][$selected]))
					{
						$old_captcha =& phpbb_captcha_factory::get_instance($config['captcha_plugin']);
						$old_captcha->uninstall();

						set_config('captcha_plugin', $selected);
						$new_captcha =& phpbb_captcha_factory::get_instance($config['captcha_plugin']);
						$new_captcha->install();

						add_log('admin', 'LOG_CONFIG_VISUAL');
					}
					else
					{
						trigger_error($user->lang['CAPTCHA_UNAVAILABLE'] . adm_back_link($this->u_action), E_USER_WARNING);
					}
				}
				trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
			}
			else if ($submit)
			{
				trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
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
					$current = ($selected !== false && $value == $selected) ? ' selected="selected"' : '';
					$captcha_select .= '<option value="' . $value . '"' . $current . ' class="disabled-option">' . $user->lang[$title] . '</option>';
				}

				$demo_captcha =& phpbb_captcha_factory::get_instance($selected);

				foreach ($config_vars as $config_var => $options)
				{
					$template->assign_var($options['tpl'], (isset($_POST[$config_var])) ? request_var($config_var, $options['default']) : $config[$config_var]) ;
				}

				$template->assign_vars(array(
					'CAPTCHA_PREVIEW_TPL'	=> $demo_captcha->get_demo_template($id),
					'S_CAPTCHA_HAS_CONFIG'	=> $demo_captcha->has_config(),
					'CAPTCHA_SELECT'		=> $captcha_select,

					'U_ACTION'				=> $this->u_action,
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

		$captcha =& phpbb_captcha_factory::get_instance($selected);
		$captcha->init(CONFIRM_REG);
		$captcha->execute_demo();

		garbage_collection();
		exit_handler();
	}
}

?>