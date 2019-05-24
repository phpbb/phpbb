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

use phpbb\exception\back_exception;

class captcha
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\captcha\factory */
	protected $factory;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config			$config		Config object
	 * @param \phpbb\captcha\factory		$factory	Captcha factory object
	 * @param \phpbb\acp\helper\controller	$helper		ACP Controller helper object
	 * @param \phpbb\language\language		$lang		Language object
	 * @param \phpbb\log\log				$log		Log object
	 * @param \phpbb\request\request		$request	Request object
	 * @param \phpbb\template\template		$template	Template object
	 * @param \phpbb\user					$user		User object
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\captcha\factory $factory,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user
	)
	{
		$this->config	= $config;
		$this->factory	= $factory;
		$this->helper	= $helper;
		$this->lang		= $lang;
		$this->log		= $log;
		$this->request	= $request;
		$this->template	= $template;
		$this->user		= $user;
	}

	function main()
	{
		$this->lang->add_lang('acp/board');

		$captchas = $this->factory->get_captcha_types();

		$selected = $this->request->variable('select_captcha', $this->config['captcha_plugin']);
		$selected = (isset($captchas['available'][$selected]) || isset($captchas['unavailable'][$selected])) ? $selected : $this->config['captcha_plugin'];
		$configure = $this->request->variable('configure', false);

		// Oh, they are just here for the view
		if ($this->request->is_set('captcha_demo', \phpbb\request\request_interface::GET))
		{
			$this->deliver_demo($selected);
		}

		// Delegate
		if ($configure)
		{
			$config_captcha = $this->factory->get_instance($selected);

			return $config_captcha->acp_page();
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

			$form_key = 'acp_captcha';
			add_form_key($form_key);

			$submit = $this->request->variable('main_submit', false);
			$errors = $cfg_array = [];

			if ($submit)
			{
				foreach ($config_vars as $config_var => $options)
				{
					$cfg_array[$config_var] = $this->request->variable($config_var, $options['default']);
				}

				validate_config_vars($config_vars, $cfg_array, $errors);

				if (!check_form_key($form_key))
				{
					$errors[] = $this->lang->lang('FORM_INVALID');
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
					$this->config->set($key, $value);
				}

				if ($selected !== $this->config['captcha_plugin'])
				{
					// sanity check
					if (isset($captchas['available'][$selected]))
					{
						$old_captcha = $this->factory->get_instance($this->config['captcha_plugin']);
						$old_captcha->uninstall();

						$this->config->set('captcha_plugin', $selected);
						$new_captcha = $this->factory->get_instance($this->config['captcha_plugin']);
						$new_captcha->install();

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_VISUAL');
					}
					else
					{

						throw new back_exception(400, 'CAPTCHA_UNAVAILABLE', 'acp_settings_captcha');
					}
				}

				return $this->helper->message_back('CONFIG_UPDATED', 'acp_settings_captcha');
			}
			else
			{
				$captcha_select = '';

				foreach ($captchas['available'] as $value => $title)
				{
					$current = ($selected !== false && $value == $selected) ? ' selected="selected"' : '';
					$captcha_select .= '<option value="' . $value . '"' . $current . '>' . $this->lang->lang($title) . '</option>';
				}

				foreach ($captchas['unavailable'] as $value => $title)
				{
					$current = ($selected !== false && $value == $selected) ? ' selected="selected"' : '';
					$captcha_select .= '<option value="' . $value . '"' . $current . ' class="disabled-option">' . $this->lang->lang($title) . '</option>';
				}

				foreach ($config_vars as $config_var => $options)
				{
					$this->template->assign_var($options['tpl'], $this->request->is_set_post($config_var) ? $this->request->variable($config_var, $options['default']) : $this->config[$config_var]) ;
				}

				$demo_captcha = $this->factory->get_instance($selected);

				$this->template->assign_vars([
					'ERROR_MSG'				=> implode('<br />', $errors),

					'CAPTCHA_SELECT'		=> $captcha_select,
					'CAPTCHA_PREVIEW_TPL'	=> $demo_captcha->get_demo_template(),
					'S_CAPTCHA_HAS_CONFIG'	=> $demo_captcha->has_config(),

					'U_ACTION'				=> $this->helper->route('acp_settings_captcha'),
				]);
			}

			return $this->helper->render('acp_captcha.html', 'ACP_VC_SETTINGS');
		}
	}

	/**
	 * Entry point for delivering image CAPTCHAs in the ACP.
	 *
	 * @param string	$selected	The selected captcha service name
	 * @return void
	 */
	protected function deliver_demo($selected)
	{
		$captcha = $this->factory->get_instance($selected);
		$captcha->init(CONFIRM_REG);
		$captcha->execute_demo();

		garbage_collection();
		exit_handler();
	}
}
