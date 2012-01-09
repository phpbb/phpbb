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

/**
* Abstract template class for environment checks.
*
* Provides a set of common chexks and routimes to collect errors/notices.
*
* @package phpBB
*/
abstract class phpbb_environment_checker
{
	var $phpbb_root_path;
	var $phpEx;
	var $config;
	var $auth;
	var $common_checks_result = array();
	var $notices = array();
	var $errors = array();

	/**
	* Functions to assign a set of checks/assertions for errors and notices
	* Should be implemented in any child classes
	*/
	abstract function set_errors();
	abstract function set_notices();

	/**
	* Class constructor
	* Initializes some essential data
	* @param string	$phpbb_root_path	Relative path to phpBB folder. 
	* @param string	$phpEx				phpBB script files extension. 
	* @param array 	$config				phpBB configuration values array.
	* @param object	$auth				phpBB authentication class object.
	*/
	function __construct($phpbb_root_path, $phpEx, $config, $auth)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->config = $config;
		$this->auth = $auth;

		// Initialize assertion manager and phpBB PHP ini wrapper classes
		$this->asserter =  new phpbb_assertion_manager();
		$this->php_ini =  new phpbb_php_ini();
	}

	/**
	* Function to perform common checks, can be used either 
	* for set_errors() or for set_notices()
	*/
	function common_checks()
	{
		$mbstring = extension_loaded('mbstring');
		$this->common_checks_result = array(
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

	/**
	* Function to evaluate assertions for errors
	* Uses data previously assigned to $this->errors array
	* with the function set_errors()
	*/
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

	/**
	* Function to evaluate assertions for notices
	* Uses data previously assigned to $this->notices array
	* with the function set_notices()
	*/
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
