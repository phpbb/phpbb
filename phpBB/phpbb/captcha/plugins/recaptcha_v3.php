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

/**
 * Google reCAPTCHA v3 plugin.
 */
class recaptcha_v3 extends captcha_abstract
{
	/**
	 * Possible request methods to verify the token.
	 */
	const CURL			= 'curl';
	const POST			= 'post';
	const SOCKET		= 'socket';

	/**
	 * Possible domain names to load the script and verify the token.
	 */
	const GOOGLE		= 'google.com';
	const RECAPTCHA		= 'recaptcha.net';
	const RECAPTCHA_CN	= 'recaptcha.google.cn';

	/** @var string[] List of supported domains */
	static public $supported_domains = [
		self::GOOGLE,
		self::RECAPTCHA,
		self::RECAPTCHA_CN
	];

	/** @var array CAPTCHA types mapped to their action */
	static protected $actions = [
		0				=> 'default',
		CONFIRM_REG		=> 'register',
		CONFIRM_LOGIN	=> 'login',
		CONFIRM_POST	=> 'post',
		CONFIRM_REPORT	=> 'report',
	];

	/**
	 * Get CAPTCHA types mapped to their action.
	 *
	 * @static
	 * @return array
	 */
	static public function get_actions()
	{
		return self::$actions;
	}

	/**
	 * Execute.
	 *
	 * Not needed by this CAPTCHA plugin.
	 *
	 * @return void
	 */
	public function execute()
	{
	}

	/**
	 * Execute demo.
	 *
	 * Not needed by this CAPTCHA plugin.
	 *
	 * @return void
	 */
	public function execute_demo()
	{
	}

	/**
	 * Get generator class.
	 *
	 * Not needed by this CAPTCHA plugin.
	 *
	 * @throws \Exception
	 * @return void
	 */
	public function get_generator_class()
	{
		throw new \Exception('No generator class given.');
	}

	/**
	 * Get CAPTCHA plugin name.
	 *
	 * @return string
	 */
	public function get_name()
	{
		return 'CAPTCHA_RECAPTCHA_V3';
	}

	/**
	 * Indicator that this CAPTCHA plugin requires configuration.
	 *
	 * @return bool
	 */
	public function has_config()
	{
		return true;
	}

	/**
	 * Initialize this CAPTCHA plugin.
	 *
	 * @param int	$type	The CAPTCHA type
	 * @return void
	 */
	public function init($type)
	{
		/**
		 * @var \phpbb\language\language	$language	Language object
		 */
		global $language;

		$language->add_lang('captcha_recaptcha');

		parent::init($type);
	}

	/**
	 * Whether or not this CAPTCHA plugin is available and setup.
	 *
	 * @return bool
	 */
	public function is_available()
	{
		/**
		 * @var \phpbb\config\config		$config		Config object
		 * @var \phpbb\language\language	$language	Language object
		 */
		global $config, $language;

		$language->add_lang('captcha_recaptcha');

		return ($config->offsetGet('recaptcha_v3_key') ?? false) && ($config->offsetGet('recaptcha_v3_secret') ?? false);
	}

	/**
	 * Create the ACP page for configuring this CAPTCHA plugin.
	 *
	 * @param string		$id			The ACP module identifier
	 * @param \acp_captcha	$module		The ACP module basename
	 * @return void
	 */
	public function acp_page($id, $module)
	{
		/**
		 * @var \phpbb\config\config		$config		Config object
		 * @var \phpbb\language\language	$language	Language object
		 * @var \phpbb\log\log				$phpbb_log	Log object
		 * @var \phpbb\request\request		$request	Request object
		 * @var \phpbb\template\template	$template	Template object
		 * @var \phpbb\user					$user		User object
		 */
		global $config, $language, $phpbb_log, $request, $template, $user;

		$module->tpl_name		= 'captcha_recaptcha_v3_acp';
		$module->page_title		= 'ACP_VC_SETTINGS';
		$recaptcha_v3_method	= $request->variable('recaptcha_v3_method', '', true);

		$form_key = 'acp_captcha';
		add_form_key($form_key);

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($language->lang('FORM_INVALID') . adm_back_link($module->u_action), E_USER_WARNING);
			}

			if (empty($recaptcha_v3_method))
			{
				trigger_error($language->lang('EMPTY_RECAPTCHA_V3_REQUEST_METHOD') . adm_back_link($module->u_action), E_USER_WARNING);
			}

			$recaptcha_domain = $request->variable('recaptcha_v3_domain', '', true);
			if (in_array($recaptcha_domain, self::$supported_domains))
			{
				$config->set('recaptcha_v3_domain', $recaptcha_domain);
			}

			$config->set('recaptcha_v3_key', $request->variable('recaptcha_v3_key', '', true));
			$config->set('recaptcha_v3_secret', $request->variable('recaptcha_v3_secret', '', true));
			$config->set('recaptcha_v3_method', $recaptcha_v3_method);

			foreach (self::$actions as $action)
			{
				$config->set("recaptcha_v3_threshold_{$action}", $request->variable("recaptcha_v3_threshold_{$action}", 0.50));
			}

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_CONFIG_VISUAL');

			trigger_error($language->lang('CONFIG_UPDATED') . adm_back_link($module->u_action));
		}

		foreach (self::$actions as $action)
		{
			$template->assign_block_vars('thresholds', [
				'key'	=> "recaptcha_v3_threshold_{$action}",
				'value'	=> $config["recaptcha_v3_threshold_{$action}"] ?? 0.5,
			]);
		}

		$template->assign_vars([
			'CAPTCHA_NAME'				=> $this->get_service_name(),
			'CAPTCHA_PREVIEW'			=> $this->get_demo_template($id),

			'RECAPTCHA_V3_KEY'			=> $config['recaptcha_v3_key'] ?? '',
			'RECAPTCHA_V3_SECRET'		=> $config['recaptcha_v3_secret'] ?? '',

			'RECAPTCHA_V3_DOMAIN'		=> $config['recaptcha_v3_domain'] ?? self::GOOGLE,
			'RECAPTCHA_V3_DOMAINS'		=> self::$supported_domains,

			'RECAPTCHA_V3_METHOD'		=> $config['recaptcha_v3_method'] ?? '',
			'RECAPTCHA_V3_METHODS'		=> [
				self::POST		=> ini_get('allow_url_fopen') && function_exists('file_get_contents'),
				self::CURL		=> extension_loaded('curl') && function_exists('curl_init'),
				self::SOCKET	=> function_exists('fsockopen'),
			],

			'U_ACTION'					=> $module->u_action,
		]);
	}

	/**
	 * Create the ACP page for previewing this CAPTCHA plugin.
	 *
	 * @param string	$id		The module identifier
	 * @return bool|string
	 */
	public function get_demo_template($id)
	{
		return $this->get_template();
	}

	/**
	 * Get the template for this CAPTCHA plugin.
	 *
	 * @return bool|string		False if CAPTCHA is already solved, template file name otherwise
	 */
	public function get_template()
	{
		/**
		 * @var \phpbb\config\config		$config				Config object
		 * @var \phpbb\language\language	$language			Language object
		 * @var \phpbb\template\template	$template			Template object
		 * @var string						$phpbb_root_path	phpBB root path
		 * @var string						$phpEx				php File extensions
		 */
		global $config, $language, $template, $phpbb_root_path, $phpEx;

		if ($this->is_solved())
		{
			return false;
		}

		$contact = phpbb_get_board_contact_link($config, $phpbb_root_path, $phpEx);
		$explain = $this->type !== CONFIRM_POST ? 'CONFIRM_EXPLAIN' : 'POST_CONFIRM_EXPLAIN';

		$domain = $config['recaptcha_v3_domain'] ?? self::GOOGLE;
		$render = $config['recaptcha_v3_key'] ?? '';

		$template->assign_vars([
			'CONFIRM_EXPLAIN'		=> $language->lang($explain, '<a href="' . $contact . '">', '</a>'),

			'RECAPTCHA_ACTION'		=> self::$actions[$this->type] ?? reset(self::$actions),
			'RECAPTCHA_KEY'			=> $config['recaptcha_v3_key'] ?? '',
			'U_RECAPTCHA_SCRIPT'	=> sprintf('//%1$s/recaptcha/api.js?render=%2$s', $domain, $render),

			'S_CONFIRM_CODE'		=> true,
			'S_RECAPTCHA_AVAILABLE'	=> $this->is_available(),
			'S_TYPE'				=> $this->type,
		]);

		return 'captcha_recaptcha_v3.html';
	}

	/**
	 * Validate the user's input.
	 *
	 * @return bool|string
	 */
	public function validate()
	{
		if (!parent::validate())
		{
			return false;
		}

		return $this->recaptcha_verify_token();
	}

	/**
	 * Validate the token returned by Google reCAPTCHA v3.
	 *
	 * @return bool|string		False on success, string containing the error otherwise
	 */
	protected function recaptcha_verify_token()
	{
		/**
		 * @var \phpbb\config\config		$config		Config object
		 * @var \phpbb\language\language	$language	Language object
		 * @var \phpbb\request\request		$request	Request object
		 * @var \phpbb\user					$user		User object
		 */
		global $config, $language, $request, $user;

		$token		= $request->variable('recaptcha_token', '', true);
		$action		= $request->variable('recaptcha_action', '', true);
		$action		= in_array($action, self::$actions) ? $action : reset(self::$actions);
		$threshold	= (double) $config["recaptcha_v3_threshold_{$action}"] ?? 0.5;

		// No token was provided, discard spam submissions
		if (empty($token))
		{
			return $language->lang('RECAPTCHA_INCORRECT');
		}

		// Create the request method that should be used
		switch ($config['recaptcha_v3_method'] ?? '')
		{
			case self::CURL:
				$method = new \ReCaptcha\RequestMethod\CurlPost();
			break;

			case self::SOCKET:
				$method = new \ReCaptcha\RequestMethod\SocketPost();
			break;

			case self::POST:
			default:
				$method = new \ReCaptcha\RequestMethod\Post();
			break;
		}

		// Create the recaptcha instance
		$recaptcha = new \ReCaptcha\ReCaptcha($config['recaptcha_v3_secret'], $method);

		// Set the expected action and threshold, and verify the token
		$result = $recaptcha->setExpectedAction($action)
							->setScoreThreshold($threshold)
							->verify($token, $user->ip);

		if ($result->isSuccess())
		{
			$this->solved = true;
			$this->confirm_code = $this->code;

			return false;
		}

		return $language->lang('RECAPTCHA_INCORRECT');
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_login_error_attempts(): string
	{
		global $language;

		$language->add_lang('captcha_recaptcha');

		return 'RECAPTCHA_V3_LOGIN_ERROR_ATTEMPTS';
	}
}
