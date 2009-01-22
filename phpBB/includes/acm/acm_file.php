<?php
/**
*
* @package acm
* @version $Id$
* @copyright (c) 2005 phpBB Group
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
* Define file-based cache.
* @package acm
*/
class phpbb_acm_file extends phpbb_acm_abstract
{
	/**
	* @var string The cache directory to use
	*/
	public $cache_dir = '';

	/**
	* @var array|bool The cache types this class supports. True indicates support for all types.
	*/
	public $supported = true;

	/**
	* Set cache directory
	*
	* @param string	$cache_prefix	The cache prefix the instance is responsible for
	* @access public
	*/
	public function __construct($cache_prefix)
	{
		$this->cache_dir = PHPBB_ROOT_PATH . 'cache/';
		$this->cache_prefix = $cache_prefix;
	}

	/**
	* {@link phpbb_acm_abstract::get() get()}
	*/
	public function get($var_name)
	{
		if ($var_name[0] === '#')
		{
			$var_name = substr($var_name, 1);
			return $this->get_global($var_name);
		}

		if (!$this->exists($var_name))
		{
			return false;
		}

		@include($this->cache_dir . $this->cache_prefix . '_' . $var_name . '.' . PHP_EXT);

		// If no data there, then the file expired...
		if ($expired)
		{
			// Destroy
			$this->destroy($var_name);
			return false;
		}

		return $data;
	}

	/**
	* {@link phpbb_acm_abstract::put() put()}
	*/
	public function put($var_name, $data, $ttl = 31536000)
	{
		if ($var_name[0] === '#')
		{
			$var_name = substr($var_name, 1);
			return $this->put_global($var_name, $data, $ttl);
		}

		$filename = $this->cache_dir . $this->cache_prefix . '_' . $var_name . '.' . PHP_EXT;

		if ($fp = @fopen($filename, 'wb'))
		{
			@flock($fp, LOCK_EX);
			fwrite($fp, "<?php\n\$expired = (time() > " . (time() + $ttl) . ") ? true : false;\nif (\$expired) { return; }\n\$data =  " . (sizeof($data) ? "unserialize(" . var_export(serialize($data), true) . ");" : 'array();'));
			@flock($fp, LOCK_UN);
			fclose($fp);

			phpbb::$system->chmod($filename, phpbb::CHMOD_READ | phpbb::CHMOD_WRITE);
		}

		return $data;
	}


	/**
	* {@link phpbb_acm_abstract::exists() exists()}
	*/
	public function exists($var_name)
	{
		if ($var_name[0] === '#')
		{
			$var_name = substr($var_name, 1);
			return $this->exists_global($var_name);
		}

		return file_exists($this->cache_dir . $this->cache_prefix . '_' . $var_name . '.' . PHP_EXT);
	}

	/**
	* {@link phpbb_acm_abstract::destroy() destroy()}
	*/
	public function destroy($var_name)
	{
		if ($var_name[0] === '#')
		{
			$var_name = substr($var_name, 1);
			$this->destroy_global($var_name);
		}

		if (!$this->exists($var_name))
		{
			return false;
		}

		$this->remove_file($this->cache_dir . $this->cache_prefix . '_' . $var_name . '.' . PHP_EXT, true);
	}

	/**
	* {@link phpbb_acm_abstract::load() load()}
	*/
	public function load()
	{
		// grab the global cache
		if (file_exists($this->cache_dir . $this->cache_prefix . '_global.' . PHP_EXT))
		{
			@include($this->cache_dir . $this->cache_prefix . '_global.' . PHP_EXT);
			return true;
		}

		return false;
	}

	/**
	* {@link phpbb_acm_abstract::unload() unload()}
	*/
	public function unload()
	{
		if (!$this->is_modified)
		{
			return;
		}

		$filename = $this->cache_dir . $this->cache_prefix . '_global.' . PHP_EXT;

		if ($fp = @fopen($filename, 'wb'))
		{
			@flock($fp, LOCK_EX);
			fwrite($fp, "<?php\n\$this->vars = unserialize(" . var_export(serialize($this->vars), true) . ");\n\$this->var_expires = unserialize(" . var_export(serialize($this->var_expires), true) . ");");
			@flock($fp, LOCK_UN);
			fclose($fp);

			phpbb::$system->chmod($filename, phpbb::CHMOD_READ | phpbb::CHMOD_WRITE);
		}
		else
		{
			// Now, this occurred how often? ... phew, just tell the user then...
			if (!@is_writable($this->cache_dir))
			{
				trigger_error($this->cache_dir . ' is NOT writable.', E_USER_ERROR);
			}

			trigger_error('Not able to open ' . $filename, E_USER_ERROR);
		}

		$this->is_modified = false;

		// To reset the global vars
		$this->vars = $this->var_expires = array();
	}

	/**
	* Tidy local cache data. Also see {@link phpbb_acm_abstract::tidy() tidy()}
	* @access protected
	*/
	protected function tidy_local()
	{
		$dir = @opendir($this->cache_dir);

		if (!$dir)
		{
			return;
		}

		while (($entry = readdir($dir)) !== false)
		{
			if (strpos($entry, $this->cache_prefix . '_') !== 0 || strpos($entry, $this->cache_prefix . '_global') === 0)
			{
				continue;
			}

			$expired = true;
			@include($this->cache_dir . $entry);

			if ($expired)
			{
				$this->remove_file($this->cache_dir . $entry);
			}
		}
		closedir($dir);
	}

	/**
	* Purge local cache data. Also see {@link phpbb_acm_abstract::purge() purge()}
	* @access protected
	*/
	protected function purge_local()
	{
		$dir = @opendir($this->cache_dir);

		if (!$dir)
		{
			return;
		}

		while (($entry = readdir($dir)) !== false)
		{
			if (strpos($entry, $this->cache_prefix . '_') !== 0 || strpos($entry, $this->cache_prefix . '_global') === 0)
			{
				continue;
			}

			$this->remove_file($this->cache_dir . $entry);
		}
		closedir($dir);
	}

	/**
	* Get modified date for cache entry
	*
	* @param string	$var_name	The cache variable name
	* @access public
	*/
	public function get_modified_date($var_name)
	{
		return @filemtime($this->cache_dir . $this->cache_prefix . '_' . $var_name . '.' . PHP_EXT);
	}

	/**
	* Removes/unlinks file
	*
	* @param string	$filename	The filename to remove
	* @param bool	$check		If true the cache directory is checked for correct directory permissions.
	* @access protected
	*/
	protected function remove_file($filename, $check = false)
	{
		if ($check && !@is_writable($this->cache_dir))
		{
			// E_USER_ERROR - not using language entry - intended.
			trigger_error('Unable to remove files within ' . $this->cache_dir . '. Please check directory permissions.', E_USER_ERROR);
		}

		return @unlink($filename);
	}
}

/**
* Special implementation for cache type 'sql'
* @package acm
*/
class phpbb_acm_file_sql extends phpbb_acm_file
{
	/**
	* {@link phpbb_acm_abstract::destroy() destroy()}
	*/
	public function destroy($var_name)
	{
		if ($var_name[0] === '#')
		{
			$var_name = substr($var_name, 1);
			$this->destroy_global($var_name);
		}

		$table = (!is_array($var_name)) ? array($var_name) : $var_name;
		$dir = @opendir($this->cache_dir);

		if (!$dir)
		{
			return;
		}

		while (($entry = readdir($dir)) !== false)
		{
			if (strpos($entry, $this->cache_prefix . '_') !== 0)
			{
				continue;
			}

			// The following method is more failproof than simply assuming the query is on line 3 (which it should be)
			@include($this->cache_dir . $entry);

			if (empty($data))
			{
				$this->remove_file($this->cache_dir . $entry);
				continue;
			}

			// Get the query
			$data = $data['query'];

			$found = false;
			foreach ($table as $check_table)
			{
				// Better catch partial table names than no table names. ;)
				if (strpos($data, $check_table) !== false)
				{
					$found = true;
					break;
				}
			}

			if ($found)
			{
				$this->remove_file($this->cache_dir . $entry);
			}
		}
		closedir($dir);

		return;
	}
}

?>