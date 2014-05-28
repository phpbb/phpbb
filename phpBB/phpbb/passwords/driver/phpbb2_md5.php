<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\passwords\driver;

/**
* @package passwords
*/
class phpbb2_md5 extends base
{
	const PREFIX = '$phpbb2_md5$';

	/** @var \phpbb\request\request phpBB request object */
	protected $request;

	/** @var \phpbb\passwords\driver\salted_md5 */
	protected $salted_md5;

	/** @var phpBB root path */
	protected $phpbb_root_path;

	/** @var php file extension */
	protected $php_ext;

	/**
	* Constructor of passwords driver object
	*
	* @param \phpbb\request\request $request phpBB request object
	* @param \phpbb\passwords\driver\salted_md5 $salted_md5 Salted md5 driver
	* @param string $phpbb_root_path phpBB root path
	* @param string $php_ext PHP file extension
	*/
	public function __construct($request, \phpbb\passwords\driver\salted_md5 $salted_md5, $phpbb_root_path, $php_ext)
	{
		$this->request = $request;
		$this->salted_md5 = $salted_md5;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* @inheritdoc
	*/
	public function get_prefix()
	{
		return self::PREFIX;
	}

	/**
	* @inheritdoc
	*/
	public function is_legacy()
	{
		return true;
	}

	/**
	* @inheritdoc
	*/
	public function hash($password, $user_row = '')
	{
		// Do not support hashing
		return false;
	}

	/**
	* @inheritdoc
	*/
	public function check($password, $hash, $user_row = array())
	{
		if (strlen($hash) != 32 && strlen($hash) != 34)
		{
			return false;
		}

		// enable super globals to get literal value
		// this is needed to prevent unicode normalization
		$super_globals_disabled = $this->request->super_globals_disabled();
		if ($super_globals_disabled)
		{
			$this->request->enable_super_globals();
		}

		// in phpBB2 passwords were used exactly as they were sent, with addslashes applied
		$password_old_format = isset($_REQUEST['password']) ? (string) $_REQUEST['password'] : '';
		$password_old_format = (!STRIP) ? addslashes($password_old_format) : $password_old_format;
		$password_new_format = $this->request->variable('password', '', true);

		if ($super_globals_disabled)
		{
			$this->request->disable_super_globals();
		}

		if ($password == $password_new_format)
		{
			if (!function_exists('utf8_to_cp1252'))
			{
				include($this->phpbb_root_path . 'includes/utf/data/recode_basic.' . $this->php_ext);
			}

			if (md5($password_old_format) === $hash || md5(\utf8_to_cp1252($password_old_format)) === $hash
				|| $this->salted_md5->check(md5($password_old_format), $hash) === true
				|| $this->salted_md5->check(md5(\utf8_to_cp1252($password_old_format)), $hash) === true)
			{
				return true;
			}
		}

		return false;
	}

	/**
	* @inheritdoc
	*/
	public function get_settings_only($hash, $full = false)
	{
		return false;
	}
}
