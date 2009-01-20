<?php
/**
*
* @package acm
* @version $Id: acm_file.php 9233 2008-12-27 12:18:04Z acydburn $
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
* Base cache class.
*
* A prefix of # for $var_name indicates global data.
*
* @method mixed get($var_name)							Get cached data.
* @method mixed put($var_name, $data, $ttl = 31536000)	Put data into cache.
* @method mixed destroy($var_name)						Destroy cached data.
* @method mixed exists($var_name)						Check if cached data exists.
*
* @package acm
*/
class phpbb_acm
{
	/**
	* @var array required phpBB objects
	*/
	public $phpbb_required = array();

	/**
	* @var array Optional phpBB objects
	*/
	public $phpbb_optional = array();

	/**
	* @var array Currently registered core acm types.
	*/
	public $cache_types = array('data' => NULL, 'sql' => NULL);

	/**
	* Constructor
	* @access public
	*/
	public function __construct() { }

	/**
	* Magic method for calling type-specific functions.
	* Functions directly supported are: get(), put(), exists(), destroy()
	*
	* The type is added to the methods name, for getting sql data just use get_sql() for example.
	*
	* see {@link phpbb_acm_abstract phpbb_acm_abstract} for more information
	*
	* @access public
	*/
	public function __call($method, $arguments)
	{
		$supported_internal_functions = array('get', 'put', 'exists', 'destroy');
		$internal_method = explode('_', $method, 2);

		// Get cache type and method
		if (in_array($internal_method[0], $supported_internal_functions))
		{
			$cache_type = (empty($internal_method[1])) ? 'data' : $internal_method[1];
			$method = $internal_method[0];
		}
		else
		{
			$cache_type = $arguments[0];
			array_shift($arguments);
		}

		// Check if the cache type is initialized and exist
		if (!$this->type_exists($cache_type))
		{
			return false;
		}

		// $this->cache_types[$cache_type]->$method($arguments);
		return call_user_func_array(array($this->cache_types[$cache_type], $method), $arguments);
	}

	/**
	* Tidy cache. This removes all expired cache data.
	* @access public
	*/
	public function tidy()
	{
		foreach ($this->cache_types as $cache_type => $object)
		{
			if ($object === NULL)
			{
				continue;
			}

			$this->cache_types[$cache_type]->tidy();
		}
	}

	/**
	* Purge cache. This removes all cache data, not only the expired one.
	* @access public
	*/
	public function purge()
	{
		foreach ($this->cache_types as $cache_type => $object)
		{
			if ($object === NULL)
			{
				continue;
			}

			$this->cache_types[$cache_type]->purge();
		}
	}

	/**
	* Load cache data. This is usually only used internally.
	* @access public
	*/
	public function load()
	{
		foreach ($this->cache_types as $cache_type => $object)
		{
			if ($object === NULL)
			{
				continue;
			}

			$this->cache_types[$cache_type]->load();
		}
	}

	/**
	* Unload everything from cache and make sure non-stored cache items are properly saved.
	* @access public
	*/
	public function unload()
	{
		foreach ($this->cache_types as $cache_type => $object)
		{
			if ($object === NULL)
			{
				continue;
			}

			$this->cache_types[$cache_type]->unload();
		}
	}

	/**
	* Register a custom cache type/class.
	*
	* @param string $cache_type		The cache type to register/set
	* @param string $cache_append	String to append to the cached data as identifier (if the coder has different types to distinct from)
	* @param string $cache_object	The exact name of the cache class to load.
	*								The filename must be: <code>includes/acm/acm_{$cache_object}.php</code>
	*								The class definition must be: <code>class phpbb_acm_{$cache_object} extends phpbb_acm_abstract</code>
	*								Additionally it is possible to define classes for every cache type...
	*									for example: <code>phpbb_acm_{$cache_object}_{$cache_type} extends phpbb_acm_{$cache_object}</code>
	*
	* @return bool	Returns true on success, else false.
	* @access public
	*/
	public function register($cache_type, $cache_append = false, $cache_object = false)
	{
		$cache_object = ($cache_object === false) ? basename(phpbb::$base_config['acm_type']) : basename($cache_object);

		// We need to init every cache type...
		if (!isset($this->cache_types[$cache_type]))
		{
			$this->cache_types[$cache_type] = NULL;
		}

		// Unregister if already registered
		if ($this->cache_types[$cache_type] !== NULL)
		{
			$this->cache_types[$cache_type] = NULL;
		}

		if ($this->cache_types[$cache_type] === NULL)
		{
			$class_name = 'phpbb_acm_' . $cache_object;

			if (!class_exists($class_name))
			{
				if (!file_exists(PHPBB_ROOT_PATH . 'includes/acm/acm_' . $cache_object . '.' . PHP_EXT))
				{
					return false;
				}

				require_once PHPBB_ROOT_PATH . 'includes/acm/acm_' . $cache_object . '.' . PHP_EXT;
			}

			$class_name = (class_exists('phpbb_acm_' . $cache_object . '_' . $cache_type)) ? 'phpbb_acm_' . $cache_object . '_' . $cache_type : 'phpbb_acm_' . $cache_object;

			// Set cache prefix, for example ctpl_prosilver
			$cache_prefix = ($cache_append === false) ? $cache_type : $cache_type . '_' . $cache_append;

			$this->cache_types[$cache_type] = new $class_name($cache_prefix);

			if (!$this->supported($cache_type))
			{
				$this->cache_types[$cache_type] = NULL;
				return false;
			}
		}

		return true;
	}

	/**
	* Check if a specified cache type is supported with the ACM class
	*
	* @param string	$cache_type	The cache type to check.
	*
	* @return bool	True if the type is supported, else false.
	* @access public
	*/
	public function supported($cache_type)
	{
		if (!$this->type_exists($cache_type))
		{
			return false;
		}

		return !empty($this->cache_types[$cache_type]->supported[$cache_type]) || $this->cache_types[$cache_type]->supported === true;
	}

	/**
	* Check if the cache type exists. Sometimes some types do not exist if the relevant files are not there or do not support the given cache type.
	*
	* @param string	$cache_type	The cache type to check.
	*
	* @return bool	True if the type exist, else false.
	* @access private
	*/
	private function type_exists($cache_type)
	{
		if (!isset($this->cache_types[$cache_type]) || $this->cache_types[$cache_type] === NULL)
		{
			$this->register($cache_type);
		}

		return $this->cache_types[$cache_type] !== NULL;
	}
}


/**
* The abstract class all ACM plugins must extend.
* @package acm
*/
abstract class phpbb_acm_abstract
{
	/**
	* @var string The current cache prefix
	*/
	public $cache_prefix = '';

	/**
	* @var array Cached global data
	*/
	protected $vars = array();

	/**
	* @var array Expire information for cached global data
	*/
	protected $var_expires = array();

	/**
	* @var bool Is true if global data is modified
	*/
	protected $is_modified = false;

	/**
	* Get cached data
	*
	* @param string	$var_name	Variable name. Global variable name is prefixed with #.
	*
	* @return mixed	Returns false if there is no data available, else returns the data
	* @access public
	*/
	abstract public function get($var_name);

	/**
	* Put data into cache
	*
	* @param string	$var_name	Variable name. Global variable name is prefixed with #.
	* @param mixed	$data		Data to be put into cache.
	* @param int	$ttl		Cache lifetime in seconds.
	*
	* @return mixed	Returns $data
	* @access public
	*/
	abstract public function put($var_name, $data, $ttl = 31536000);

	/**
	* Destroy cached data.
	*
	* @param string	$var_name	Variable name. Global variable name is prefixed with #.
	*
	* @return mixed	Returns false if the cached data does not exist
	* @access public
	*/
	abstract public function destroy($var_name);

	/**
	* Check if cached data exists.
	*
	* @param string	$var_name	Variable name. Global variable name is prefixed with #.
	*
	* @return bool	True if it exists
	* @access public
	*/
	abstract public function exists($var_name);

	/**
	* Load cache data. This is usually only used internally.
	* @access public
	*/
	abstract public function load();

	/**
	* Unload everything from cache and make sure non-stored cache items are properly saved.
	* @access public
	*/
	abstract public function unload();

	/**
	* Tidy cache. This removes all expired cache data.
	* @access public
	*/
	public function tidy()
	{
		$this->tidy_local();
		$this->tidy_global();

		set_config('cache_last_gc', time(), true);
	}

	/**
	* Purge cache. This removes all cache data, not only the expired one.
	* @access public
	*/
	public function purge()
	{
		$this->purge_local();
		$this->purge_global();
	}

	/**
	* Tidy only local cache data
	* @access protected
	*/
	abstract protected function tidy_local();

	/**
	* Purge only local cache data
	* @access protected
	*/
	abstract protected function purge_local();

	/**
	* Get global cache data. See {@link get() get()}.
	* @access protected
	*/
	protected function get_global($var_name)
	{
		// Check if we have all variables
		if (!sizeof($this->vars))
		{
			$this->load();
		}

		if (!isset($this->var_expires[$var_name]))
		{
			return false;
		}

		// If expired... we remove this entry now...
		if (time() > $this->var_expires[$var_name])
		{
			$this->destroy('#' . $var_name);
			return false;
		}

		if (isset($this->vars[$var_name]))
		{
			return $this->vars[$var_name];
		}

		return false;
	}

	/**
	* Put data into global cache. See {@link put() put()}.
	* @access protected
	*/
	protected function put_global($var_name, $data, $ttl = 31536000)
	{
		$this->vars[$var_name] = $data;
		$this->var_expires[$var_name] = time() + $ttl;
		$this->is_modified = true;

		return $data;
	}

	/**
	* Check if global data exists. See {@link exists() exists()}.
	* @access protected
	*/
	protected function exists_global($var_name)
	{
		return !empty($this->vars[$var_name]) && time() <= $this->var_expires[$var_name];
	}

	/**
	* Destroy global cache data. See {@link destroy() destroy()}.
	* @access protected
	*/
	protected function destroy_global($var_name)
	{
		$this->is_modified = true;

		unset($this->vars[$var_name]);
		unset($this->var_expires[$var_name]);

		// We save here to let the following cache hits succeed
		$this->unload();
	}

	/**
	* Tidy global cache data. See {@link tidy() tidy()}.
	* @access protected
	*/
	protected function tidy_global()
	{
		// Now tidy global settings
		if (!sizeof($this->vars))
		{
			$this->load();
		}

		foreach ($this->var_expires as $var_name => $expires)
		{
			if (time() > $expires)
			{
				// We only unset, then save later
				unset($this->vars[$var_name]);
				unset($this->var_expires[$var_name]);
			}
		}

		$this->is_modified = true;
		$this->unload();
	}

	/**
	* Purge global cache data. See {@link purge() purge()}.
	* @access protected
	*/
	protected function purge_global()
	{
		// Now purge global settings
		unset($this->vars);
		unset($this->var_expires);

		$this->vars = array();
		$this->var_expires = array();

		$this->is_modified = true;
		$this->unload();
	}
}



?>