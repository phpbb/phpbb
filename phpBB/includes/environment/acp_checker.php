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

class phpbb_acp_environment_checker extends phpbb_environment_checker
{
	function set_errors()
	{
		// Initialize checks if not set externally
		if (empty($this->checks))
		{
			$this->check();
		}

		$this->errors = array(
			'ERROR_REGISTER_GLOBALS'				=> $this->checks['REGISTER_GLOBALS'],
			'ERROR_MBSTRING_FUNC_OVERLOAD'			=> $this->checks['MBSTRING_FUNC_OVERLOAD'],
			'ERROR_MBSTRING_ENCODING_TRANSLATION'	=> $this->checks['MBSTRING_ENCODING_TRANSLATION'],
			'ERROR_MBSTRING_HTTP_INPUT'				=> $this->checks['MBSTRING_HTTP_INPUT'],
			'ERROR_MBSTRING_HTTP_OUTPUT'			=> $this->checks['MBSTRING_HTTP_OUTPUT'],
			'ERROR_GETIMAGESIZE_SUPPORT'			=> $this->checks['GETIMAGESIZE_SUPPORT'],
			'ERROR_REMOVE_INSTALL'					=> !file_exists($this->phpbb_root_path . 'install') || is_file($this->phpbb_root_path . 'install'),
		
		);
	}

	function set_notices()
	{
		// Initialize checks if not set externally
		if (empty($this->checks))
		{
			$this->check();
		}

		$this->notices = array(
			'ERROR_SAFE_MODE'					=> !$this->php_ini->get_bool('safe_mode'),
			'ERROR_URL_FOPEN_SUPPORT'			=> $this->checks['URL_FOPEN_SUPPORT'],
			'ERROR_DIRECTORY_AVATARS_UNWRITABLE'=> phpbb_is_writable($this->phpbb_root_path . $this->config['avatar_path']),
			'ERROR_DIRECTORY_STORE_UNWRITABLE'	=> phpbb_is_writable($this->phpbb_root_path . 'store'),
			'ERROR_DIRECTORY_CACHE_UNWRITABLE'	=> phpbb_is_writable($this->phpbb_root_path . 'cache'),
			'ERROR_DIRECTORY_FILES_UNWRITABLE'	=> phpbb_is_writable($this->phpbb_root_path . $this->config['upload_path']),
			'ERROR_PCRE_UTF_SUPPORT'			=> $this->checks['PCRE_UTF_SUPPORT'],
			'ERROR_WRITABLE_CONFIG'				=> defined('PHPBB_DISABLE_CONFIG_CHECK') ||
													!file_exists($this->phpbb_root_path . 'config.' . $this->phpEx) ||
													!phpbb_is_writable($this->phpbb_root_path . 'config.' . $this->phpEx) ||
													!(@fileperms($this->phpbb_root_path . 'config.' . $this->phpEx) & 0x0002),
			'ERROR_PHP_VERSION_OLD'				=> !$this->auth->acl_get('a_server') || version_compare(PHP_VERSION, '5.2.0', '>='),
		);
	}
}
