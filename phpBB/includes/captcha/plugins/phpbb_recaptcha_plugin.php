<?php
/**
*
* @package VC
* @version $Id$
* @copyright (c) 2006, 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (!class_exists('phpbb_default_captcha'))
{
	// we need the classic captcha code for tracking solutions and attempts
	include($phpbb_root_path . 'includes/captcha/plugins/captcha_abstract.' . $phpEx);
}

/**
* @package VC
*/
class phpbb_recaptcha extends phpbb_default_captcha
{
	var $recaptcha_server = 'http://api.recaptcha.net';
	var $recaptcha_server_secure = 'https://api-secure.recaptcha.net'; // class constants :(
	var $recaptcha_verify_server = 'api-verify.recaptcha.net';
	var $challenge;
	var $response;

	// PHP4 Constructor
	function phpbb_recaptcha()
	{
		$this->recaptcha_server = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? $this->recaptcha_server_secure : $this->recaptcha_server;
	}

	function init($type)
	{
		global $config, $db, $user;

		$user->add_lang('captcha_recaptcha');
		parent::init($type);
		$this->challenge = request_var('recaptcha_challenge_field', '');
		$this->response = request_var('recaptcha_response_field', '');
	}

	function &get_instance()
	{
		$instance =& new phpbb_recaptcha();
		return $instance;
	}

	function is_available()
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

	function get_name()
	{
		return 'CAPTCHA_RECAPTCHA';
	}

	function get_class_name()
	{
		return 'phpbb_recaptcha';
	}

	function acp_page($id, &$module)
	{
		global $config, $db, $template, $user;

		$captcha_vars = array(
			'recaptcha_pubkey'				=> 'RECAPTCHA_PUBKEY',
			'recaptcha_privkey'				=> 'RECAPTCHA_PRIVKEY',
		);

		$module->tpl_name = 'captcha_recaptcha_acp';
		$module->page_title = 'ACP_VC_SETTINGS';
		$form_key = 'acp_captcha';
		add_form_key($form_key);

		$submit = request_var('submit', '');

		if ($submit && check_form_key($form_key))
		{
			$captcha_vars = array_keys($captcha_vars);
			foreach ($captcha_vars as $captcha_var)
			{
				$value = request_var($captcha_var, '');
				if ($value)
				{
					set_config($captcha_var, $value);
				}
			}

			add_log('admin', 'LOG_CONFIG_VISUAL');
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
				$var = (isset($_REQUEST[$captcha_var])) ? request_var($captcha_var, '') : ((isset($config[$captcha_var])) ? $config[$captcha_var] : '');
				$template->assign_var($template_var, $var);
			}

			$template->assign_vars(array(
				'CAPTCHA_PREVIEW'	=> $this->get_demo_template($id),
				'CAPTCHA_NAME'		=> $this->get_class_name(),
				'U_ACTION'			=> $module->u_action,
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
		global $config, $user, $template;

		if ($this->is_solved())
		{
			return false;
		}
		else
		{
			$explain = $user->lang(($this->type != CONFIRM_POST) ? 'CONFIRM_EXPLAIN' : 'POST_CONFIRM_EXPLAIN', '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">', '</a>');

			$template->assign_vars(array(
				'RECAPTCHA_SERVER'			=> $this->recaptcha_server,
				'RECAPTCHA_PUBKEY'			=> isset($config['recaptcha_pubkey']) ? $config['recaptcha_pubkey'] : '',
				'RECAPTCHA_ERRORGET'		=> '',
				'S_RECAPTCHA_AVAILABLE'		=> $this->is_available(),
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

	function uninstall()
	{
		$this->garbage_collect(0);
	}

	function install()
	{
		return;
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

// Code from here on is based on recaptchalib.php
/*
 * This is a PHP library that handles calling reCAPTCHA.
 *	- Documentation and latest version
 *		  http://recaptcha.net/plugins/php/
 *	- Get a reCAPTCHA API Key
 *		  http://recaptcha.net/api/getkey
 *	- Discussion group
 *		  http://groups.google.com/group/recaptcha
 *
 * Copyright (c) 2007 reCAPTCHA -- http://recaptcha.net
 * AUTHORS:
 *   Mike Crawford
 *   Ben Maurer
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

	/**
	* Submits an HTTP POST to a reCAPTCHA server
	* @param string $host
	* @param string $path
	* @param array $data
	* @param int port
	* @return array response
	*/
	function _recaptcha_http_post($host, $path, $data, $port = 80)
	{
		$req = $this->_recaptcha_qsencode ($data);

		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
		$http_request .= "Content-Length: " . strlen($req) . "\r\n";
		$http_request .= "User-Agent: reCAPTCHA/PHP/phpBB\r\n";
		$http_request .= "\r\n";
		$http_request .= $req;

		$response = '';
		if (false == ($fs = @fsockopen($host, $port, $errno, $errstr, 10)))
		{
			trigger_error('Could not open socket', E_USER_ERROR);
		}

		fwrite($fs, $http_request);

		while (!feof($fs))
		{
			// One TCP-IP packet
			$response .= fgets($fs, 1160);
		}
		fclose($fs);
		$response = explode("\r\n\r\n", $response, 2);

		return $response;
	}

	/**
	* Calls an HTTP POST function to verify if the user's guess was correct
	* @param array $extra_params an array of extra variables to post to the server
	* @return ReCaptchaResponse
	*/
	function recaptcha_check_answer($extra_params = array())
	{
		global $config, $user;

		//discard spam submissions
		if ($this->challenge == null || strlen($this->challenge) == 0 || $this->response == null || strlen($this->response) == 0)
		{
			return $user->lang['RECAPTCHA_INCORRECT'];
		}

		$response = $this->_recaptcha_http_post($this->recaptcha_verify_server, '/verify',
			array(
				'privatekey'	=> $config['recaptcha_privkey'],
				'remoteip'		=> $user->ip,
				'challenge'		=> $this->challenge,
				'response'		=> $this->response
			) + $extra_params
		);

		$answers = explode("\n", $response[1]);

		if (trim($answers[0]) === 'true')
		{
			$this->solved = true;
			return false;
		}
		else
		{
			return $user->lang['RECAPTCHA_INCORRECT'];
		}
	}

	/**
	* Encodes the given data into a query string format
	* @param $data - array of string elements to be encoded
	* @return string - encoded request
	*/
	function _recaptcha_qsencode($data)
	{
		$req = '';
		foreach ($data as $key => $value)
		{
			$req .= $key . '=' . urlencode(stripslashes($value)) . '&';
		}

		// Cut the last '&'
		$req = substr($req, 0, strlen($req) - 1);
		return $req;
	}
}

?>