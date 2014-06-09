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

namespace phpbb\hook;

/**
* The hook finder locates installed hooks.
*/
class finder
{
	protected $phpbb_root_path;
	protected $cache;
	protected $php_ext;

	/**
	* Creates a new finder instance.
	*
	* @param string $phpbb_root_path Path to the phpbb root directory
	* @param string $php_ext php file extension
	* @param \phpbb\cache\driver\driver_interface $cache A cache instance or null
	*/
	public function __construct($phpbb_root_path, $php_ext, \phpbb\cache\driver\driver_interface $cache = null)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->cache = $cache;
		$this->php_ext = $php_ext;
	}

	/**
	* Finds all hook files.
	*
	* @param bool $cache Whether the result should be cached
	* @return array An array of paths to found hook files
	*/
	public function find($cache = true)
	{
		if (!defined('DEBUG') && $cache && $this->cache)
		{
			$hook_files = $this->cache->get('_hooks');
			if ($hook_files !== false)
			{
				return $hook_files;
			}
		}

		$hook_files = array();

		// Now search for hooks...
		$dh = @opendir($this->phpbb_root_path . 'includes/hooks/');

		if ($dh)
		{
			while (($file = readdir($dh)) !== false)
			{
				if (strpos($file, 'hook_') === 0 && substr($file, -strlen('.' . $this->php_ext)) === '.' . $this->php_ext)
				{
					$hook_files[] = substr($file, 0, -(strlen($this->php_ext) + 1));
				}
			}
			closedir($dh);
		}

		if ($cache && $this->cache)
		{
			$this->cache->put('_hooks', $hook_files);
		}

		return $hook_files;
	}
}
