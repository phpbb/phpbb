<?php
/**
*
* @package install
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

class phpbb_environment_install_checker extends phpbb_environment_checker
{
	var $php_optional_modules = array('zlib', 'ftp', 'gd', 'xml');
	var $categories = array(
			'PHP_SETTINGS',
			'MBSTRING_CHECK',
			'PHP_SUPPORTED_DB',
			'PHP_OPTIONAL_MODULE',
			'FILES_REQUIRED',
			'FILES_OPTIONAL',
		);
	var $dir_is_writable = array();
	var $dir_exists = array();
	var $category = '';
	var $any_db_support = false;
	var $imagemagic;

	function set_errors()
	{
		// Initialize checks if not set externally
		if (empty($this->common_checks_result))
		{
			$this->common_checks();
		}

		// Reset checks for every category
		$this->errors = array();
		switch ($this->category)
		{
			// Set the required common PHP ini and version checks
			case 'PHP_SETTINGS':
				$this->errors = array(
					'PHP_VERSION_REQD'				=> version_compare(PHP_VERSION, '4.3.3', '>='),
					'PHP_REGISTER_GLOBALS'			=> $this->common_checks_result['REGISTER_GLOBALS'],
					'PHP_URL_FOPEN_SUPPORT'			=> $this->common_checks_result['URL_FOPEN_SUPPORT'],
					'PHP_GETIMAGESIZE_SUPPORT'		=> $this->common_checks_result['GETIMAGESIZE_SUPPORT'],
					'PCRE_UTF_SUPPORT'				=> $this->common_checks_result['PCRE_UTF_SUPPORT'],
				);
			break;

			// Set the required mbstring PHP ini checks
			case 'MBSTRING_CHECK':
				if (extension_loaded('mbstring'))
				{
					$this->errors = array(
						'MBSTRING_FUNC_OVERLOAD'		=> $this->common_checks_result['MBSTRING_FUNC_OVERLOAD'],
						'MBSTRING_ENCODING_TRANSLATION'	=> $this->common_checks_result['MBSTRING_ENCODING_TRANSLATION'],
						'MBSTRING_HTTP_INPUT'			=> $this->common_checks_result['MBSTRING_HTTP_INPUT'],
						'MBSTRING_HTTP_OUTPUT'			=> $this->common_checks_result['MBSTRING_HTTP_OUTPUT'],
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
				foreach ($this->php_optional_modules as $dll)
				{
					$this->errors['DLL_' . strtoupper($dll)] = (!@extension_loaded($dll)) ? can_load_dll($dll) : true;
				}
				$this->imagemagic = phpbb_search_imagemagick();
				$this->errors['APP_MAGICK'] = $this->imagemagic;
			break;

			// Set the required directories checks
			case 'FILES_REQUIRED':
				$directories = array('cache/', 'files/', 'store/');
				foreach ($directories as $dir)
				{
					$this->errors[$dir] = ($this->dir_exists[$dir] = phpbb_check_dir_exists($dir))
											&& ($this->dir_is_writable[$dir] = phpbb_test_dir_is_writable($dir));
				}
			break;

			// Set the optional files/directories checks
			case 'FILES_OPTIONAL':
				$directories = array('config.' . $this->phpEx, 'images/avatars/upload/');
				foreach ($directories as $dir)
				{
					$this->errors[$dir] = ($this->dir_exists[$dir] = file_exists($this->phpbb_root_path . $dir))
											&& ($this->dir_is_writable[$dir] = phpbb_is_writable($this->phpbb_root_path . $dir));
				}
			break;

			default:
			break;
		}
	}

	function set_notices()
	{
		// No notices for installation procedure,
		// but we implement the function as it is abstract declared
	}
}
