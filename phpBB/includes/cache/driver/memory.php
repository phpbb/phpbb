<?php
/**
*
* @package acm
* @copyright (c) 2005 phpBB Group
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
* ACM Abstract Memory Class
* @package acm
*/
abstract class phpbb_cache_driver_memory extends phpbb_cache_driver_base
{
	var $key_prefix;

	var $vars = array();
	var $is_modified = false;

	var $cache_dir = '';

	/**
	* Set cache path
	*/
	function __construct()
	{
		global $phpbb_root_path, $dbname, $table_prefix;

		$this->cache_dir	= $phpbb_root_path . 'cache/';
		$this->key_prefix	= substr(md5($dbname . $table_prefix), 0, 8) . '_';

		if (!isset($this->extension) || !extension_loaded($this->extension))
		{
			global $acm_type;

			trigger_error("Could not find required extension [{$this->extension}] for the ACM module $acm_type.", E_USER_ERROR);
		}

		if (isset($this->function) && !function_exists($this->function))
		{
			global $acm_type;

			trigger_error("The required function [{$this->function}] is not available for the ACM module $acm_type.", E_USER_ERROR);
		}
	}

	/**
	* Load global cache
	*/
	function load()
	{
		// grab the global cache
		$this->vars = $this->_read('global');

		if ($this->vars !== false)
		{
			return true;
		}

		return false;
	}

	/**
	* Unload cache object
	*/
	function unload()
	{
		$this->save();
		unset($this->vars);
		unset($this->sql_rowset);
		unset($this->sql_row_pointer);

		$this->vars = array();
		$this->sql_rowset = array();
		$this->sql_row_pointer = array();
	}

	/**
	* Save modified objects
	*/
	function save()
	{
		if (!$this->is_modified)
		{
			return;
		}

		$this->_write('global', $this->vars, 2592000);

		$this->is_modified = false;
	}

	/**
	* Tidy cache
	*/
	function tidy()
	{
		// cache has auto GC, no need to have any code here :)

		set_config('cache_last_gc', time(), true);
	}

	/**
	* Get saved cache object
	*/
	function get($var_name)
	{
		if ($var_name[0] == '_')
		{
			if (!$this->_exists($var_name))
			{
				return false;
			}

			return $this->_read($var_name);
		}
		else
		{
			return ($this->_exists($var_name)) ? $this->vars[$var_name] : false;
		}
	}

	/**
	* Put data into cache
	*/
	function put($var_name, $var, $ttl = 2592000)
	{
		if ($var_name[0] == '_')
		{
			$this->_write($var_name, $var, $ttl);
		}
		else
		{
			$this->vars[$var_name] = $var;
			$this->is_modified = true;
		}
	}

	/**
	* Purge cache data
	*/
	function purge()
	{
		// Purge all phpbb cache files
		$dir = @opendir($this->cache_dir);

		if (!$dir)
		{
			return;
		}

		while (($entry = readdir($dir)) !== false)
		{
			if (strpos($entry, 'sql_') !== 0 && strpos($entry, 'data_') !== 0 && strpos($entry, 'ctpl_') !== 0 && strpos($entry, 'tpl_') !== 0)
			{
				continue;
			}

			$this->remove_file($this->cache_dir . $entry);
		}
		closedir($dir);

		unset($this->vars);
		unset($this->sql_rowset);
		unset($this->sql_row_pointer);

		$this->vars = array();
		$this->sql_rowset = array();
		$this->sql_row_pointer = array();

		$this->is_modified = false;
	}


	/**
	* Destroy cache data
	*/
	function destroy($var_name, $table = '')
	{
		if ($var_name == 'sql' && !empty($table))
		{
			if (!is_array($table))
			{
				$table = array($table);
			}

			foreach ($table as $table_name)
			{
				// gives us the md5s that we want
				$temp = $this->_read('sql_' . $table_name);

				if ($temp === false)
				{
					continue;
				}

				// delete each query ref
				foreach ($temp as $md5_id => $void)
				{
					$this->_delete('sql_' . $md5_id);
				}

				// delete the table ref
				$this->_delete('sql_' . $table_name);
			}

			return;
		}

		if (!$this->_exists($var_name))
		{
			return;
		}

		if ($var_name[0] == '_')
		{
			$this->_delete($var_name);
		}
		else if (isset($this->vars[$var_name]))
		{
			$this->is_modified = true;
			unset($this->vars[$var_name]);

			// We save here to let the following cache hits succeed
			$this->save();
		}
	}

	/**
	* Check if a given cache entry exist
	*/
	function _exists($var_name)
	{
		if ($var_name[0] == '_')
		{
			return $this->_isset($var_name);
		}
		else
		{
			if (!sizeof($this->vars))
			{
				$this->load();
			}

			return isset($this->vars[$var_name]);
		}
	}

	/**
	* Removes/unlinks file
	*/
	function remove_file($filename, $check = false)
	{
		if (!function_exists('phpbb_is_writable'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions.' . $phpEx);
		}

		if ($check && !phpbb_is_writable($this->cache_dir))
		{
			// E_USER_ERROR - not using language entry - intended.
			trigger_error('Unable to remove files within ' . $this->cache_dir . '. Please check directory permissions.', E_USER_ERROR);
		}

		return @unlink($filename);
	}

	/**
	* Check if a cache var exists
	*
	* @access protected
	* @param string $var Cache key
	* @return bool True if it exists, otherwise false
	*/
	function _isset($var)
	{
		// Most caches don't need to check
		return true;
	}
}
