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

namespace phpbb\captcha\plugins;

use phpbb\config\config;
use phpbb\db\driver\driver;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;

class turnstile extends base
{
	private const API_ENDPOINT = 'https://api.cloudflare.com/client/v4/captcha/validate';

	/** @var config */
	protected config $config;

	/** @var language */
	protected language $language;

	/** @var log_interface */
	protected log_interface $log;

	/** @var request_interface */
	protected request_interface $request;

	/** @var template */
	protected template $template;

	/** @var user */
	protected user $user;

	/** @var string Service name */
	protected string $service_name = '';

	/**
	 * Constructor for turnstile captcha plugin
	 *
	 * @param config $config
	 * @param driver_interface $db
	 * @param language $language
	 * @param log_interface $log
	 * @param request_interface $request
	 * @param template $template
	 * @param user $user
	 */
	public function __construct(config $config, driver_interface $db, language $language, log_interface $log, request_interface $request, template $template, user $user)
	{
		parent::__construct($db);

		$this->config = $config;
		$this->language = $language;
		$this->log = $log;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	public function is_available(): bool
	{
		$this->language->add_lang('captcha_turnstile');

		return !empty($this->config->offsetGet('captcha_turnstile_sitekey'))
			&& !empty($this->config->offsetGet('captcha_turnstile_secret'));
	}

	public function has_config(): bool
	{
		return true;
	}

	public function get_name(): string
	{
		return 'CAPTCHA_TURNSTILE';
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_name(string $name): void
	{
		$this->service_name = $name;
	}

	public function init(int $type): void
	{
		$this->language->add_lang('captcha_turnstile');
	}

	public function get_hidden_fields(): array
	{
		$hidden_fields = [];

		// Required for posting page to store solved state
		if ($this->solved)
		{
			$hidden_fields['confirm_code'] = $this->confirm_code;
		}
		$hidden_fields['confirm_id'] = $this->confirm_id;
		return $hidden_fields;
	}

	public function validate(): bool
	{
		// Implement server-side validation logic here
		// Example: Validate the submitted CAPTCHA value using Cloudflare API

		// Your Cloudflare API credentials
		$api_email = 'your_email@example.com';
		$api_key = 'your_api_key';

		// Cloudflare API endpoint for CAPTCHA verification
		$endpoint = 'https://api.cloudflare.com/client/v4/captcha/validate';

		// CAPTCHA data to be sent in the request
		$data = [
			'email' => $api_email,
			'key' => $api_key,
			'response' => $this->confirm_code
		];

		// Initialize cURL session
		$ch = curl_init();

		// Set cURL options
		curl_setopt_array($ch, [
			CURLOPT_URL => $endpoint,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/json',
				'Accept: application/json'
			],
			CURLOPT_RETURNTRANSFER => true
		]);

		// Execute the cURL request
		$response = curl_exec($ch);

		// Check for errors
		if ($response === false) {
			// Handle cURL error
			curl_close($ch);
			return false;
		}

		// Decode the JSON response
		$result = json_decode($response, true);

		// Check if the response indicates success
		if (isset($result['success']) && $result['success'] === true) {
			// CAPTCHA validation passed
			curl_close($ch);
			return true;
		} else {
			// CAPTCHA validation failed
			curl_close($ch);
			return false;
		}
	}

	public function is_solved(): bool
	{
		return false;
	}

	public function reset(): void
	{
		// TODO: Implement reset() method.
	}

	public function get_attempt_count(): int
	{
		// TODO: Implement get_attempt_count() method.
		return 0;
	}

	public function get_template(): string
	{
		return 'custom_captcha.html'; // Template file for displaying the CAPTCHA
	}

	public function get_demo_template(): string
	{
		return 'captcha_turnstile_acp_demo.html';
	}

	public function garbage_collect(int $confirm_type = 0): void
	{
		// TODO: Implement garbage_collect() method.
	}

	/**
	 * {@inheritDoc}
	 */
	public function acp_page($id, $module): void
	{
		$captcha_vars = [
			'captcha_turnstile_sitekey'			=> 'CAPTCHA_TURNSTILE_SITEKEY',
			'captcha_turnstile_secret'			=> 'CAPTCHA_TURNSTILE_SECRET',
		];

		$module->tpl_name = 'captcha_turnstile_acp';
		$module->page_title = 'ACP_VC_SETTINGS';
		$form_key = 'acp_captcha';
		add_form_key($form_key);

		$submit = $this->request->is_set_post('submit');

		if ($submit && check_form_key($form_key))
		{
			$captcha_vars = array_keys($captcha_vars);
			foreach ($captcha_vars as $captcha_var)
			{
				$value = $this->request->variable($captcha_var, '');
				if ($value)
				{
					$this->config->set($captcha_var, $value);
				}
			}

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_VISUAL');
			trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($module->u_action));
		}
		else if ($submit)
		{
			trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($module->u_action));
		}
		else
		{
			foreach ($captcha_vars as $captcha_var => $template_var)
			{
				$var = $this->request->is_set($captcha_var) ? $this->request->variable($captcha_var, '') : $this->config->offsetGet($captcha_var);;
				$this->template->assign_var($template_var, $var);
			}

			$this->template->assign_vars(array(
				'CAPTCHA_PREVIEW'		=> $this->get_demo_template(),
				'CAPTCHA_NAME'			=> $this->service_name,
				'U_ACTION'				=> $module->u_action,
			));
		}
	}
}
