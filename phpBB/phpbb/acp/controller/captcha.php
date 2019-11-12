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

namespace phpbb\acp\controller;

class captcha
{
	var $u_action;

	public function main($id, $mode)
	{
		$this->language->add_lang('acp/board');

		/* @var $factory \phpbb\captcha\factory */
		$factory = $phpbb_container->get('captcha.factory');
		$captchas = $factory->get_captcha_types();

		$selected = $this->request->variable('select_captcha', $this->config['captcha_plugin']);
		$selected = (isset($captchas['available'][$selected]) || isset($captchas['unavailable'][$selected])) ? $selected : $this->config['captcha_plugin'];
		$configure = $this->request->variable('configure', false);

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
			$config_vars = [
				'enable_confirm'		=> [
					'tpl'		=> 'REG_ENABLE',
					'default'	=> false,
					'validate'	=> 'bool',
					'lang'		=> 'VISUAL_CONFIRM_REG',
				],
				'enable_post_confirm'	=> [
					'tpl'		=> 'POST_ENABLE',
					'default'	=> false,
					'validate'	=> 'bool',
					'lang'		=> 'VISUAL_CONFIRM_POST',
				],
				'confirm_refresh'		=> [
					'tpl'		=> 'CONFIRM_REFRESH',
					'default'	=> false,
					'validate'	=> 'bool',
					'lang'		=> 'VISUAL_CONFIRM_REFRESH',
				],
				'max_reg_attempts'		=> [
					'tpl'		=> 'REG_LIMIT',
					'default'	=> 0,
					'validate'	=> 'int:0:99999',
					'lang'		=> 'REG_LIMIT',
				],
				'max_login_attempts'	=> [
					'tpl'		=> 'MAX_LOGIN_ATTEMPTS',
					'default'	=> 0,
					'validate'	=> 'int:0:99999',
					'lang'		=> 'MAX_LOGIN_ATTEMPTS',
				],
			];

			$this->tpl_name = 'acp_captcha';
			$this->page_title = 'ACP_VC_SETTINGS';
			$form_key = 'acp_captcha';
			add_form_key($form_key);

			$submit = $this->request->variable('main_submit', false);
			$error = $cfg_array = [];

			if ($submit)
			{
				foreach ($config_vars as $config_var => $options)
				{
					$cfg_array[$config_var] = $this->request->variable($config_var, $options['default']);
				}
				validate_config_vars($config_vars, $cfg_array, $error);

				if (!check_form_key($form_key))
				{
					$error[] = $this->language->lang('FORM_INVALID');
				}
				if ($error)
				{
					$submit = false;
				}
			}

			if ($submit)
			{
				foreach ($cfg_array as $key => $value)
				{
					$this->config->set($key, $value);
				}

				if ($selected !== $this->config['captcha_plugin'])
				{
					// sanity check
					if (isset($captchas['available'][$selected]))
					{
						$old_captcha = $factory->get_instance($this->config['captcha_plugin']);
						$old_captcha->uninstall();

						$this->config->set('captcha_plugin', $selected);
						$new_captcha = $factory->get_instance($this->config['captcha_plugin']);
						$new_captcha->install();

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_VISUAL');
					}
					else
					{
						trigger_error($this->language->lang('CAPTCHA_UNAVAILABLE') . adm_back_link($this->u_action), E_USER_WARNING);
					}
				}
				trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
			}
			else
			{
				$captcha_select = '';
				foreach ($captchas['available'] as $value => $title)
				{
					$current = ($selected !== false && $value == $selected) ? ' selected="selected"' : '';
					$captcha_select .= '<option value="' . $value . '"' . $current . '>' . $this->language->lang($title) . '</option>';
				}

				foreach ($captchas['unavailable'] as $value => $title)
				{
					$current = ($selected !== false && $value == $selected) ? ' selected="selected"' : '';
					$captcha_select .= '<option value="' . $value . '"' . $current . ' class="disabled-option">' . $this->language->lang($title) . '</option>';
				}

				$demo_captcha = $factory->get_instance($selected);

				foreach ($config_vars as $config_var => $options)
				{
					$this->template->assign_var($options['tpl'], (isset($_POST[$config_var])) ? $this->request->variable($config_var, $options['default']) : $this->config[$config_var]) ;
				}

				$this->template->assign_vars([
					'CAPTCHA_PREVIEW_TPL'	=> $demo_captcha->get_demo_template($id),
					'S_CAPTCHA_HAS_CONFIG'	=> $demo_captcha->has_config(),
					'CAPTCHA_SELECT'		=> $captcha_select,
					'ERROR_MSG'				=> implode('<br />', $error),

					'U_ACTION'				=> $this->u_action,
				]);
			}
		}
	}

	/**
	 * Entry point for delivering image CAPTCHAs in the ACP.
	 */
	function deliver_demo($selected)
	{
		$captcha = $phpbb_container->get('captcha.factory')->get_instance($selected);
		$captcha->init(CONFIRM_REG);
		$captcha->execute_demo();

		garbage_collection();
		exit_handler();
	}
}
