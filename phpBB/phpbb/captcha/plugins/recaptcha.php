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

use phpbb\exception\runtime_exception;

class recaptcha extends captcha_abstract
{
	private $response;

	/**
	* Constructor
	*/
	public function __construct()
	{
	}

	function init($type)
	{
		global $user, $request;

		$user->add_lang('captcha_recaptcha');
		parent::init($type);
		$this->response = $request->variable('g-recaptcha-response', '');
	}

	public function is_available()
	{
		global $config, $user;
		$user->add_lang('captcha_recaptcha');
		return (isset($config['recaptcha_pubkey']) && !empty($config['recaptcha_pubkey']));
	}

	/**
	*  API function
	*/
	function has_config()
	{
		return true;
	}

	public static function get_name()
	{
		return 'CAPTCHA_RECAPTCHA';
	}

	/**
	* This function is implemented because required by the upper class, but is never used for reCaptcha.
	* @throws runtime_exception
	*/
	function get_generator_class()
	{
		throw new runtime_exception('NO_GENERATOR_CLASS');
	}

	function acp_page($id, $module)
	{
		global $config, $template, $user, $phpbb_log, $request;

		$captcha_vars = array(
			'recaptcha_pubkey'				=> 'RECAPTCHA_PUBKEY',
			'recaptcha_privkey'				=> 'RECAPTCHA_PRIVKEY',
		);

		$module->tpl_name = 'captcha_recaptcha_acp';
		$module->page_title = 'ACP_VC_SETTINGS';
		$form_key = 'acp_captcha';
		add_form_key($form_key);

		$submit = $request->variable('submit', '');

		if ($submit && check_form_key($form_key))
		{
			$captcha_vars = array_keys($captcha_vars);
			foreach ($captcha_vars as $captcha_var)
			{
				$value = $request->variable($captcha_var, '');
				if ($value)
				{
					$config->set($captcha_var, $value);
				}
			}

			$recaptcha_domain = $request->variable('recaptcha_v2_domain', '', true);
			if (in_array($recaptcha_domain, recaptcha_v3::$supported_domains))
			{
				$config->set('recaptcha_v2_domain', $recaptcha_domain);
			}

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_CONFIG_VISUAL');
			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($module->u_action));
		}
		else if ($submit)
		{
			trigger_error($user->lang['FORM_INVALID'] . adm_back_link($module->u_action));
		}
		else
		{
			foreach ($captcha_vars as $captcha_var => $template_var)
			{
				$var = (isset($_REQUEST[$captcha_var])) ? $request->variable($captcha_var, '') : ((isset($config[$captcha_var])) ? $config[$captcha_var] : '');
				$template->assign_var($template_var, $var);
			}

			$template->assign_vars(array(
				'CAPTCHA_PREVIEW'		=> $this->get_demo_template($id),
				'CAPTCHA_NAME'			=> $this->get_service_name(),
				'RECAPTCHA_V2_DOMAIN'	=> $config['recaptcha_v2_domain'] ?? recaptcha_v3::GOOGLE,
				'RECAPTCHA_V2_DOMAINS'	=> recaptcha_v3::$supported_domains,
				'U_ACTION'				=> $module->u_action,
			));

		}
	}

	// not needed
	function execute_demo()
	{
	}

	// not needed
	function execute()
	{
	}

	function get_template()
	{
		global $config, $user, $template, $phpbb_root_path, $phpEx;

		if ($this->is_solved())
		{
			return false;
		}
		else
		{
			$contact_link = phpbb_get_board_contact_link($config, $phpbb_root_path, $phpEx);
			$explain = $user->lang(($this->type != CONFIRM_POST) ? 'CONFIRM_EXPLAIN' : 'POST_CONFIRM_EXPLAIN', '<a href="' . $contact_link . '">', '</a>');
			$domain = $config['recaptcha_v2_domain'] ?? recaptcha_v3::GOOGLE;

			$template->assign_vars(array(
				'RECAPTCHA_SERVER'			=> sprintf('//%1$s/recaptcha/api', $domain),
				'RECAPTCHA_PUBKEY'			=> isset($config['recaptcha_pubkey']) ? $config['recaptcha_pubkey'] : '',
				'S_RECAPTCHA_AVAILABLE'		=> self::is_available(),
				'S_CONFIRM_CODE'			=> true,
				'S_TYPE'					=> $this->type,
				'L_CONFIRM_EXPLAIN'			=> $explain,
			));

			return 'captcha_recaptcha.html';
		}
	}

	function get_demo_template($id)
	{
		return $this->get_template();
	}

	function get_hidden_fields()
	{
		$hidden_fields = array();

		// this is required for posting.php - otherwise we would forget about the captcha being already solved
		if ($this->solved)
		{
			$hidden_fields['confirm_code'] = $this->code;
		}
		$hidden_fields['confirm_id'] = $this->confirm_id;
		return $hidden_fields;
	}

	function validate()
	{
		if (!parent::validate())
		{
			return false;
		}
		else
		{
			return $this->recaptcha_check_answer();
		}
	}

	/**
	* Calls an HTTP POST function to verify if the user's guess was correct
	*
	* @return bool|string Returns false on success or error string on failure.
	*/
	function recaptcha_check_answer()
	{
		global $config, $user;

		//discard spam submissions
		if ($this->response == null || strlen($this->response) == 0)
		{
			return $user->lang['RECAPTCHA_INCORRECT'];
		}

		$recaptcha = new \ReCaptcha\ReCaptcha($config['recaptcha_privkey']);
		$result = $recaptcha->verify($this->response, $user->ip);

		if ($result->isSuccess())
		{
			$this->solved = true;
			return false;
		}
		else
		{
			return $user->lang['RECAPTCHA_INCORRECT'];
		}
	}
}
