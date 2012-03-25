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
* Implementation of abstract class phpbb_environment_checker.
*
* Provides environment checks for ACP main page.
*
* @package phpBB
*/
class phpbb_environment_acp_checker extends phpbb_environment_checker
{
	/**
	* Function to assign a set of checks/assertions for errors
	* If assertion results in false, it will be treated as error
	* Puts results into $this->errors array
	*/
	public function set_errors()
	{
		// Initialize common_checks_result if not set externally
		if (empty($this->common_checks_result))
		{
			$this->common_checks();
		}

		$this->errors = array(
			'ERROR_REGISTER_GLOBALS'				=> $this->common_checks_result['REGISTER_GLOBALS'],
			'ERROR_MBSTRING_FUNC_OVERLOAD'			=> $this->common_checks_result['MBSTRING_FUNC_OVERLOAD'],
			'ERROR_MBSTRING_ENCODING_TRANSLATION'	=> $this->common_checks_result['MBSTRING_ENCODING_TRANSLATION'],
			'ERROR_MBSTRING_HTTP_INPUT'				=> $this->common_checks_result['MBSTRING_HTTP_INPUT'],
			'ERROR_MBSTRING_HTTP_OUTPUT'			=> $this->common_checks_result['MBSTRING_HTTP_OUTPUT'],
			'ERROR_GETIMAGESIZE_SUPPORT'			=> $this->common_checks_result['GETIMAGESIZE_SUPPORT'],
			'ERROR_REMOVE_INSTALL'					=> !file_exists($this->phpbb_root_path . 'install') || is_file($this->phpbb_root_path . 'install'),

		);
	}

	/**
	* Function to assign a set of checks/assertions for notices
	* If assertion results in false, it will be treated as notice
	* Puts results into $this->notices array
	*/
	public function set_notices()
	{
		// Initialize common_checks_result if not set externally
		if (empty($this->common_checks_result))
		{
			$this->common_checks();
		}

		$this->notices = array(
			'ERROR_SAFE_MODE'					=> !$this->php_ini->get_bool('safe_mode'),
			'ERROR_URL_FOPEN_SUPPORT'			=> $this->common_checks_result['URL_FOPEN_SUPPORT'],
			'ERROR_DIRECTORY_AVATARS_UNWRITABLE'=> phpbb_is_writable($this->phpbb_root_path . $this->config['avatar_path']),
			'ERROR_DIRECTORY_STORE_UNWRITABLE'	=> phpbb_is_writable($this->phpbb_root_path . 'store'),
			'ERROR_DIRECTORY_CACHE_UNWRITABLE'	=> phpbb_is_writable($this->phpbb_root_path . 'cache'),
			'ERROR_DIRECTORY_FILES_UNWRITABLE'	=> phpbb_is_writable($this->phpbb_root_path . $this->config['upload_path']),
			'ERROR_PCRE_UTF_SUPPORT'			=> $this->common_checks_result['PCRE_UTF_SUPPORT'],
			'ERROR_WRITABLE_CONFIG'				=> defined('PHPBB_DISABLE_CONFIG_CHECK') ||
													!file_exists($this->phpbb_root_path . 'config.' . $this->phpEx) ||
													!phpbb_is_writable($this->phpbb_root_path . 'config.' . $this->phpEx) ||
													!(@fileperms($this->phpbb_root_path . 'config.' . $this->phpEx) & 0x0002),
			'ERROR_PHP_VERSION_OLD'				=> !$this->auth->acl_get('a_server') || version_compare(PHP_VERSION, '5.2.0', '>='),
		);
	}
}
