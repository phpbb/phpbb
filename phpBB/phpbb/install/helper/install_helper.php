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

namespace phpbb\install\helper;

/**
 * General helper functionality for the installer
 */
class install_helper
{
	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param string	$phpbb_root_path	path to phpBB's root
	 * @param string	$php_ext			Extension of PHP files
	 */
	public function __construct($phpbb_root_path, $php_ext)
	{
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;
	}

	/**
	 * Check whether phpBB is installed.
	 *
	 * @return bool
	 */
	public function is_phpbb_installed()
	{
		$config_path = $this->phpbb_root_path . 'config.' . $this->php_ext;
		$install_lock_path = $this->phpbb_root_path . 'cache/install_lock';

		if (file_exists($config_path) && !file_exists($install_lock_path) && filesize($config_path))
		{
			return true;
		}

		return false;
	}
}
