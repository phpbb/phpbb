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

	var $auth;

	function phpbb_environment_checker($phpbb_root_path, $phpEx, $auth)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->auth = $auth;
	}

	function get_errors()
	{
		if (!class_exists('phpbb_assertion_manager'))
		{
			include($this->phpbb_root_path . 'includes/assertion_manager.' . $this->phpEx);
		}

		$asserter = new phpbb_assertion_manager;

		// Check PHP enviroment configuration parameters
		$asserter->assert(
			!phpbb_ini_get_bool('register_globals'),
			'ERROR_REGISTER_GLOBALS'
		);

		$mbstring = extension_loaded('mbstring');
		$asserter->assert(
			!$mbstring ||
			!(phpbb_ini_get_int('mbstring.func_overload') & (MB_OVERLOAD_MAIL | MB_OVERLOAD_STRING)),
			'ERROR_MBSTRING_FUNC_OVERLOAD'
		);
		$asserter->assert(
			!$mbstring ||
			!phpbb_ini_get_bool('mbstring.encoding_translation'),
			'ERROR_MBSTRING_ENCODING_TRANSLATION'
		);
		$asserter->assert(
			!$mbstring ||
			phpbb_ini_get_string('mbstring.http_input') == 'pass',
			'ERROR_MBSTRING_HTTP_INPUT'
		);
		$asserter->assert(
			!$mbstring ||
			phpbb_ini_get_string('mbstring.http_output') == 'pass',
			'ERROR_MBSTRING_HTTP_OUTPUT'
		);

		// Check if PHP function getimagesize() is available
		$asserter->assert(
			function_exists('getimagesize'),
			'ERROR_GETIMAGESIZE_SUPPORT'
		);

		// Check if install/ directory does not exist - note the checks order
		$asserter->assert(
			!file_exists($this->phpbb_root_path . 'install') ||
			is_file($this->phpbb_root_path . 'install'),
			'ERROR_REMOVE_INSTALL'
		);

		return $asserter->get_failed_assertions();
	}

	function get_notices()
	{
		if (!class_exists('phpbb_assertion_manager'))
		{
			include($this->phpbb_root_path . 'includes/assertion_manager.' . $this->phpEx);
		}

		$asserter = new phpbb_assertion_manager;

		// Check PHP enviroment configuration parameters
		$asserter->assert(
			!phpbb_ini_get_bool('safe_mode'),
			'ERROR_SAFE_MODE'
		);
		$asserter->assert(
			phpbb_ini_get_bool('allow_url_fopen'),
			'ERROR_URL_FOPEN_SUPPORT'
		);

		// Check if some directories are writable
		$asserter->assert(
			phpbb_is_writable($this->phpbb_root_path . 'images/avatars/upload/'),
			'ERROR_DIRECTORY_AVATARS_UNWRITABLE'
		);
		$asserter->assert(
			phpbb_is_writable($this->phpbb_root_path . 'store/'),
			'ERROR_DIRECTORY_STORE_UNWRITABLE'
		);
		$asserter->assert(
			phpbb_is_writable($this->phpbb_root_path . 'cache/'),
			'ERROR_DIRECTORY_CACHE_UNWRITABLE'
		);
		$asserter->assert(
			phpbb_is_writable($this->phpbb_root_path . 'files/'),
			'ERROR_DIRECTORY_FILES_UNWRITABLE'
		);

		// Check if PCRE supports unicode properties
		$asserter->assert(
			preg_match('/\p{L}/u', 'a'),
			'ERROR_PCRE_UTF_SUPPORT'
		);

		// Check if config.php is writable
		$asserter->assert(
			defined('PHPBB_DISABLE_CONFIG_CHECK') ||
			!file_exists($this->phpbb_root_path . 'config.' . $this->phpEx) ||
			!phpbb_is_writable($this->phpbb_root_path . 'config.' . $this->phpEx) ||
			!(@fileperms($this->phpbb_root_path . 'config.' . $this->phpEx) & 0x0002),
			'ERROR_WRITABLE_CONFIG'
		);

		// Check if PHP version is lower than 5.2.0 (for future phpBB versions)
		$asserter->assert(
			!$this->auth->acl_get('a_server') ||
			version_compare(PHP_VERSION, '5.2.0', '>='),
			'ERROR_PHP_VERSION_OLD'
		);

		return $asserter->get_failed_assertions();
	}
}
