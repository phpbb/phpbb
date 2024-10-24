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

class acp_captcha
{
	var $u_action;

	/** @var string Template name */
	public $tpl_name = 'acp_captcha';

	/** @var string Page title language variable */
	public $page_title = 'ACP_VC_SETTINGS';

	function main($id, $mode)
	{
		global $user, $template, $phpbb_log, $request;
		global $config, $phpbb_container;

		$user->add_lang('acp/board');

		/* @var $factory \phpbb\captcha\factory */
		$factory = $phpbb_container->get('captcha.factory');
		$captchas = $factory->get_captcha_types();

		$selected = $request->variable('select_captcha', $config['captcha_plugin']);
		$selected = (isset($captchas['available'][$selected]) || isset($captchas['unavailable'][$selected])) ? $selected : $config['captcha_plugin'];
		$configure = $request->variable('configure', false);

		// Oh, they are just here for the view
		if (isset($_GET['captcha_demo']))
		{
			$this->deliver_demo($selected);
		}

		// Delegate
		if ($configure)
		{
			$config_captcha = $factory->get_instance($selected);
			$config_captcha->acp_page($id, $this);
		}
		else
		{
			$config_vars = array(
				'enable_confirm'		=> array(
					'tpl'		=> 'REG_ENABLE',
					'default'	=> false,
					'validate'	=> 'bool',
					'lang'		=> 'VISUAL_CONFIRM_REG',
				),
				'enable_post_confirm'	=> array(
					'tpl'		=> 'POST_ENABLE',
					'default'	=> false,
					'validate'	=> 'bool',
					'lang'		=> 'VISUAL_CONFIRM_POST',
				),
				'confirm_refresh'		=> array(
					'tpl'		=> 'CONFIRM_REFRESH',
					'default'	=> false,
					'validate'	=> 'bool',
					'lang'		=> 'VISUAL_CONFIRM_REFRESH',
				),
				'max_reg_attempts'		=> array(
					'tpl'		=> 'REG_LIMIT',
					'default'	=> 0,
					'validate'	=> 'int:0:99999',
					'lang'		=> 'REG_LIMIT',
				),
				'max_login_attempts'	=> array(
					'tpl'		=> 'MAX_LOGIN_ATTEMPTS',
					'default'	=> 0,
					'validate'	=> 'int:0:99999',
					'lang'		=> 'MAX_LOGIN_ATTEMPTS',
				),
			);

			$form_key = 'acp_captcha';
			add_form_key($form_key);

			$submit = $request->variable('main_submit', false);
			$errors = $cfg_array = array();

			if ($submit)
			{
				foreach ($config_vars as $config_var => $options)
				{
					$cfg_array[$config_var] = $request->variable($config_var, $options['default']);
				}
				validate_config_vars($config_vars, $cfg_array, $errors);

				if (!check_form_key($form_key))
				{
					$errors[] = $user->lang['FORM_INVALID'];
				}
				if ($errors)
				{
					$submit = false;
				}
			}

			if ($submit)
			{
				foreach ($cfg_array as $key => $value)
				{
					$config->set($key, $value);
				}

				if ($selected !== $config['captcha_plugin'])
				{
					// sanity check
					if (isset($captchas['available'][$selected]))
					{
						$old_captcha = $factory->get_instance($config['captcha_plugin']);
						$old_captcha->garbage_collect();

						$config->set('captcha_plugin', $selected);

						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_CONFIG_VISUAL');
					}
					else
					{
						trigger_error($user->lang['CAPTCHA_UNAVAILABLE'] . adm_back_link($this->u_action), E_USER_WARNING);
					}
				}
				trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
			}
			else
			{
				$captcha_options = [];
				foreach ($captchas['available'] as $value => $title)
				{
					$captcha_options[] = [
						'value'		=> $value,
						'label'		=> $user->lang($title),
						'selected'	=> $selected !== false && $value == $selected,
					];
				}

				foreach ($captchas['unavailable'] as $value => $title)
				{
					$captcha_options[] = [
						'value'		=> $value,
						'label'		=> $user->lang($title),
						'selected'	=> $selected !== false && $value == $selected,
						'class'		=> 'disabled-option',
					];
				}

				$demo_captcha = $factory->get_instance($selected);

				foreach ($config_vars as $config_var => $options)
				{
					$template->assign_var($options['tpl'], (isset($_POST[$config_var])) ? $request->variable($config_var, $options['default']) : $config[$config_var]) ;
				}

				$template->assign_vars(array(
					'CAPTCHA_PREVIEW_TPL'	=> $demo_captcha->get_demo_template($id),
					'S_CAPTCHA_HAS_CONFIG'	=> $demo_captcha->has_config(),
					'CAPTCHA_SELECT'		=> [
						'tag'		=> 'select',
						'name'		=> 'select_captcha',
						'options'	=> $captcha_options,
					],
					'ERRORS'				=> $errors,

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
		global $phpbb_container;

		$captcha = $phpbb_container->get('captcha.factory')->get_instance($selected);
		$captcha->init(CONFIRM_REG);
		$captcha->execute_demo();

		garbage_collection();
		exit_handler();
	}
}
