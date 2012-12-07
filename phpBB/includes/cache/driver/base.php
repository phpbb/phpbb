<?php
/**
*
* @package acm
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
* @package acm
*/
abstract class phpbb_cache_driver_base implements phpbb_cache_driver_interface
{
	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $cache_dir;

	/**
	* Set cache directory, store some dependencies
	*
	* @param mixed $phpbb_root_path
	* @param mixed $php_ext
	* @param mixed $cache_dir
	*/
	public function __construct($phpbb_root_path, $php_ext, $cache_dir = 'cache/')
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->set_cache_dir((!is_null($cache_dir) ? $cache_dir : $this->phpbb_root_path . 'cache/'));
	}

	/**
	* Set the cache directory
	*
	* @param string $cache_dir Full path to the cache directory desired
	*/
	public function set_cache_dir($cache_dir)
	{
		if (substr($cache_dir, -1) != '/')
		{
			$cache_dir .= '/';
		}

		if (!phpbb_is_writable($cache_dir))
		{
			// We need to use die() here, because else we may encounter an infinite loop (the message handler calls $cache->unload())
			die('Fatal: ' . $cache_dir . ' is NOT writable.');
			exit;
		}

		$this->cache_dir = $cache_dir;
	}

	/**
	* Get the cache directory
	*/
	public function get_cache_dir()
	{
		return $this->cache_dir;
	}

	/**
	* Removes/unlinks file
	*/
	public function remove_file($filename, $check = false)
	{
		if ($check && !phpbb_is_writable($this->cache_dir))
		{
			// E_USER_ERROR - not using language entry - intended.
			trigger_error('Unable to remove files within ' . $this->cache_dir . '. Please check directory permissions.', E_USER_ERROR);
		}

		return @unlink($filename);
	}
}
