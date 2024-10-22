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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;

class turnstile extends base
{
	/** @var string URL to cloudflare turnstile API javascript */
	private const SCRIPT_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js';

	/** @var string API endpoint for turnstile verification */
	private const VERIFY_ENDPOINT = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

	/** @var Client */
	protected Client $client;

	/** @var language */
	protected language $language;

	/** @var log_interface */
	protected log_interface $log;

	/** @var template */
	protected template $template;

	/** @var string Service name */
	protected string $service_name = '';

	/** @var array|string[] Supported themes for Turnstile CAPTCHA */
	protected static array $supported_themes = [
		'light',
		'dark',
		'auto'
	];

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
		parent::__construct($config, $db, $language, $request, $user);

		$this->language = $language;
		$this->log = $log;
		$this->template = $template;
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_available(): bool
	{
		$this->init($this->type);

		return !empty($this->config->offsetGet('captcha_turnstile_sitekey'))
			&& !empty($this->config->offsetGet('captcha_turnstile_secret'));
	}

	/**
	 * {@inheritDoc}
	 */
	public function has_config(): bool
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
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

	/**
	 * {@inheritDoc}
	 */
	public function init(confirm_type $type): void
	{
		parent::init($type);

		$this->language->add_lang('captcha_turnstile');
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(): bool
	{
		if (parent::validate())
		{
			return true;
		}

		$turnstile_response = $this->request->variable('cf-turnstile-response', '');
		if (!$turnstile_response)
		{
			// Return without checking against server without a turnstile response
			return false;
		}

		// Retrieve form data for verification
		$form_data = [
			'secret'			=> $this->config['captcha_turnstile_secret'],
			'response'			=> $turnstile_response,
			'remoteip'			=> $this->user->ip,
		];

		// Create guzzle client
		$client = $this->get_client();

		// Check captcha with turnstile API
		try
		{
			$response = $client->request('POST', self::VERIFY_ENDPOINT, [
				'form_params' => $form_data,
			]);
		}
		catch (GuzzleException)
		{
			// Something went wrong during the request to Cloudflare, assume captcha was bad
			$this->solved = false;
			return false;
		}

		// Decode the JSON response
		$result = json_decode($response->getBody(), true);

		// Check if the response indicates success
		if (isset($result['success']) && $result['success'] === true)
		{
			$this->solved = true;
			$this->confirm_code = $this->code;
			return true;
		}
		else
		{
			$this->last_error = $this->language->lang('CAPTCHA_TURNSTILE_INCORRECT');
			return false;
		}
	}

	/**
	 * Get Guzzle client
	 *
	 * @return Client
	 */
	protected function get_client(): Client
	{
		if (!isset($this->client))
		{
			$this->client = new Client();
		}

		return $this->client;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_template(): string
	{
		if ($this->is_solved())
		{
			return '';
		}

		$this->template->assign_vars([
			'S_TURNSTILE_AVAILABLE'		=> $this->is_available(),
			'TURNSTILE_SITEKEY'			=> $this->config->offsetGet('captcha_turnstile_sitekey'),
			'TURNSTILE_THEME'			=> $this->config->offsetGet('captcha_turnstile_theme'),
			'U_TURNSTILE_SCRIPT'		=> self::SCRIPT_URL,
			'CONFIRM_TYPE_REGISTRATION'	=> $this->type->value,
		]);

		return 'captcha_turnstile.html';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_demo_template(): string
	{
		$this->template->assign_vars([
			'TURNSTILE_THEME'		=> $this->config->offsetGet('captcha_turnstile_theme'),
			'U_TURNSTILE_SCRIPT'	=> self::SCRIPT_URL,
		]);

		return 'captcha_turnstile_acp_demo.html';
	}

	/**
	 * {@inheritDoc}
	 */
	public function acp_page(mixed $id, mixed $module): void
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

			$captcha_theme = $this->request->variable('captcha_turnstile_theme', self::$supported_themes[0]);
			if (in_array($captcha_theme, self::$supported_themes))
			{
				$this->config->set('captcha_turnstile_theme', $captcha_theme);
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
				$var = $this->request->is_set($captcha_var) ? $this->request->variable($captcha_var, '') : $this->config->offsetGet($captcha_var);
				$this->template->assign_var($template_var, $var);
			}

			$this->template->assign_vars(array(
				'CAPTCHA_PREVIEW'			=> $this->get_demo_template(),
				'CAPTCHA_NAME'				=> $this->service_name,
				'CAPTCHA_TURNSTILE_THEME'	=> $this->config->offsetGet('captcha_turnstile_theme'),
				'CAPTCHA_TURNSTILE_THEMES'	=> self::$supported_themes,
				'U_ACTION'					=> $module->u_action,
			));
		}
	}
}
