<?php
/**
*
* @package install
* @version $Id$
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

include($phpbb_root_path . 'includes/environment_checker.' . $phpEx);

class phpbb_install_checker extends phpbb_environment_checker
{
	var $php_dlls_other = array('zlib', 'ftp', 'gd', 'xml');
	var $categories = array(
			'PHP_SETTINGS',
			'MBSTRING_CHECK',
			'PHP_SUPPORTED_DB',
			'PHP_OPTIONAL_MODULE',
			'FILES_REQUIRED',
			'FILES_OPTIONAL',
		);
	var $any_db_support;

	function set_errors($category)
	{
		// Reset checks for every category
		$this->errors = array();
		switch ($category)
		{
			// Set the required common PHP ini and version checks
			case 'PHP_SETTINGS':
				$this->errors = array(
					'PHP_VERSION_REQD'				=> version_compare(PHP_VERSION, '4.3.3', '>='),
					'PHP_REGISTER_GLOBALS'			=> !$this->php_ini->get_bool('register_globals'),
					'PHP_URL_FOPEN_SUPPORT'			=> $this->php_ini->get_bool('allow_url_fopen'),
					'PHP_GETIMAGESIZE_SUPPORT'		=> function_exists('getimagesize'),
					'PCRE_UTF_SUPPORT'				=> preg_match('/\p{L}/u', 'a'),
				);
			break;

			// Set the required mbstring PHP ini checks
			case 'MBSTRING_CHECK':
				if (@extension_loaded('mbstring'))
				{
					$this->errors = array(
						'MBSTRING_FUNC_OVERLOAD'		=> !($this->php_ini->get_int('mbstring.func_overload') & (MB_OVERLOAD_MAIL | MB_OVERLOAD_STRING)),
						'MBSTRING_ENCODING_TRANSLATION'	=> !$this->php_ini->get_bool('mbstring.encoding_translation'),
						'MBSTRING_HTTP_INPUT'			=> $this->php_ini->get_string('mbstring.http_input') == 'pass',
						'MBSTRING_HTTP_OUTPUT'			=> $this->php_ini->get_string('mbstring.http_output') == 'pass',
					);
				}
			break;

			// Set the available DBMS checks, at least 1 DBMS should be available
			case 'PHP_SUPPORTED_DB':
				$available_dbms = get_available_dbms(false, true);
				$this->any_db_support = $available_dbms['ANY_DB_SUPPORT'];
				unset($available_dbms['ANY_DB_SUPPORT']);
				foreach ($available_dbms as $db_name => $db_ary)
				{
					$this->errors['DLL_' . strtoupper($db_name)] = (bool) $db_ary['AVAILABLE'];
				}
			break;

			// Set the PHP optional modules checks
			case 'PHP_OPTIONAL_MODULE':
				foreach ($this->php_dlls_other as $dll)
				{
					$this->errors['DLL_' . strtoupper($dll)] = (!@extension_loaded($dll)) ? can_load_dll($dll) : true;
				}
				$img_imagick = phpbb_search_imagemagick();
				$this->errors['APP_MAGICK'] = $img_imagick;
			break;

			// Set the required directories checks
			case 'FILES_REQUIRED':
				$exists = $writable = array();
				$directories = array('cache/', 'files/', 'store/');
				foreach ($directories as $dir)
				{
					$this->errors[$dir] = ($exists[$dir] = phpbb_check_dir_exists($dir))
														&& ($writable[$dir] = phpbb_create_dir($dir));
				}
			break;

			// Set the optional files/directories checks
			case 'FILES_OPTIONAL':
				$directories = array('config.' . $this->phpEx, 'images/avatars/upload/');
				foreach ($directories as $dir)
				{
					$this->errors[$dir] = ($exists[$dir] = file_exists($this->phpbb_root_path . $dir))
														&& ($writable[$dir] = phpbb_is_writable($this->phpbb_root_path . $dir));
				}
			break;

			default:
			break;
		}
	}
}
