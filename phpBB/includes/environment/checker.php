<?php
/**
*
* @package phpBB
* @copyright (c) 2011 phpBB Group
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

class phpbb_environment_checker
{
	var $phpbb_root_path;
	var $phpEx;
	var $config;
	var $auth;
	var $checks = array();
	var $notices = array();
	var $errors = array();

	function phpbb_environment_checker($phpbb_root_path, $phpEx, $config, $auth)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->config = $config;
		$this->auth = $auth;

		$this->asserter =  new phpbb_assertion_manager();
		$this->php_ini =  new phpbb_ini_reader();
	}

	function check()
	{
		$mbstring = extension_loaded('mbstring');
		$this->checks = array(
			'REGISTER_GLOBALS'				=> !$this->php_ini->get_bool('register_globals'),
			'MBSTRING_FUNC_OVERLOAD'		=> !$mbstring || !($this->php_ini->get_int('mbstring.func_overload') & (MB_OVERLOAD_MAIL | MB_OVERLOAD_STRING)),
			'MBSTRING_ENCODING_TRANSLATION'	=> !$mbstring || !$this->php_ini->get_bool('mbstring.encoding_translation'),
			'MBSTRING_HTTP_INPUT'			=> !$mbstring || $this->php_ini->get_string('mbstring.http_input') == 'pass',
			'MBSTRING_HTTP_OUTPUT'			=> !$mbstring || $this->php_ini->get_string('mbstring.http_output') == 'pass',
			'GETIMAGESIZE_SUPPORT'			=> function_exists('getimagesize'),
			'URL_FOPEN_SUPPORT'				=> $this->php_ini->get_bool('allow_url_fopen'),
			'PCRE_UTF_SUPPORT'				=> preg_match('/\p{L}/u', 'a'),
		);
	}

	function set_errors()
	{

	}

	function set_notices()
	{

	}

	function get_errors()
	{
		// Initialize errors checks if not set externally
		if (empty($this->errors))
		{
			$this->set_errors();
		}

		// Clear previous results
		$this->asserter->failed_assertions = array();

		foreach ($this->errors as $message => $assertion)
		{
			$this->asserter->assert($assertion, $message);
		}

		return $this->asserter->get_failed_assertions();
	}

	function get_notices()
	{
		// Initialize notices checks if not set externally
		if (empty($this->notices))
		{
			$this->set_notices();
		}

		// Clear previous results
		$this->asserter->failed_assertions = array();

		foreach ($this->notices as $message => $assertion)
		{
			$this->asserter->assert($assertion, $message);
		}

		return $this->asserter->get_failed_assertions();
	}
}
