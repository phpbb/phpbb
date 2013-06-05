<?php
/**
*
* @package extension
* @copyright (c) 2011 phpBB Group
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
* The extension finder provides a simple way to locate files in active extensions
*
* @package extension
*/
class phpbb_extension_finder
{
	protected $extension_manager;
	protected $filesystem;
	protected $phpbb_root_path;
	protected $cache;
	protected $php_ext;

	/**
	* The cache variable name used to store $this->cached_queries in $this->cache.
	*
	* Allows the use of multiple differently configured finders with the same cache.
	* @var string
	*/
	protected $cache_name;

	/**
	* An associative array, containing all search parameters set in methods.
	* @var	array
	*/
	protected $query;

	/**
	* A map from md5 hashes of serialized queries to their previously retrieved
	* results.
	* @var	array
	*/
	protected $cached_queries;

	/**
	* Creates a new finder instance with its dependencies
	*
	* @param phpbb_extension_manager $extension_manager An extension manager
	*            instance that provides the finder with a list of active
	*            extensions and their locations
	* @param phpbb_filesystem $filesystem Filesystem instance
	* @param string $phpbb_root_path Path to the phpbb root directory
	* @param phpbb_cache_driver_interface $cache A cache instance or null
	* @param string $php_ext php file extension
	* @param string $cache_name The name of the cache variable, defaults to
	*                           _ext_finder
	*/
	public function __construct(phpbb_extension_manager $extension_manager, phpbb_filesystem $filesystem, $phpbb_root_path = '', phpbb_cache_driver_interface $cache = null, $php_ext = 'php', $cache_name = '_ext_finder')
	{
		$this->extension_manager = $extension_manager;
		$this->filesystem = $filesystem;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->cache = $cache;
		$this->php_ext = $php_ext;
		$this->cache_name = $cache_name;

		$this->query = array(
			'core_path' => false,
			'core_suffix' => false,
			'core_prefix' => false,
			'core_directory' => false,
			'extension_suffix' => false,
			'extension_prefix' => false,
			'extension_directory' => false,
		);

		$this->cached_queries = ($this->cache) ? $this->cache->get($this->cache_name) : false;
	}

	/**
	* Sets a core path to be searched in addition to extensions
	*
	* @param string $core_path The path relative to phpbb_root_path
	* @return phpbb_extension_finder This object for chaining calls
	*/
	public function core_path($core_path)
	{
		$this->query['core_path'] = $core_path;
		return $this;
	}

	/**
	* Sets the suffix all files found in extensions and core must match.
	*
	* There is no default file extension, so to find PHP files only, you will
	* have to specify .php as a suffix. However when using get_classes, the .php
	* file extension is automatically added to suffixes.
	*
	* @param string $suffix A filename suffix
	* @return phpbb_extension_finder This object for chaining calls
	*/
	public function suffix($suffix)
	{
		$this->core_suffix($suffix);
		$this->extension_suffix($suffix);
		return $this;
	}

	/**
	* Sets a suffix all files found in extensions must match
	*
	* There is no default file extension, so to find PHP files only, you will
	* have to specify .php as a suffix. However when using get_classes, the .php
	* file extension is automatically added to suffixes.
	*
	* @param string $extension_suffix A filename suffix
	* @return phpbb_extension_finder This object for chaining calls
	*/
	public function extension_suffix($extension_suffix)
	{
		$this->query['extension_suffix'] = $extension_suffix;
		return $this;
	}

	/**
	* Sets a suffix all files found in the core path must match
	*
	* There is no default file extension, so to find PHP files only, you will
	* have to specify .php as a suffix. However when using get_classes, the .php
	* file extension is automatically added to suffixes.
	*
	* @param string $core_suffix A filename suffix
	* @return phpbb_extension_finder This object for chaining calls
	*/
	public function core_suffix($core_suffix)
	{
		$this->query['core_suffix'] = $core_suffix;
		return $this;
	}

	/**
	* Sets the prefix all files found in extensions and core must match
	*
	* @param string $prefix A filename prefix
	* @return phpbb_extension_finder This object for chaining calls
	*/
	public function prefix($prefix)
	{
		$this->core_prefix($prefix);
		$this->extension_prefix($prefix);
		return $this;
	}

	/**
	* Sets a prefix all files found in extensions must match
	*
	* @param string $extension_prefix A filename prefix
	* @return phpbb_extension_finder This object for chaining calls
	*/
	public function extension_prefix($extension_prefix)
	{
		$this->query['extension_prefix'] = $extension_prefix;
		return $this;
	}

	/**
	* Sets a prefix all files found in the core path must match
	*
	* @param string $core_prefix A filename prefix
	* @return phpbb_extension_finder This object for chaining calls
	*/
	public function core_prefix($core_prefix)
	{
		$this->query['core_prefix'] = $core_prefix;
		return $this;
	}

	/**
	* Sets a directory all files found in extensions and core must be contained in
	*
	* Automatically sets the core_directory if its value does not differ from
	* the current directory.
	*
	* @param string $directory
	* @return phpbb_extension_finder This object for chaining calls
	*/
	public function directory($directory)
	{
		$this->core_directory($directory);
		$this->extension_directory($directory);
		return $this;
	}

	/**
	* Sets a directory all files found in extensions must be contained in
	*
	* @param string $extension_directory
	* @return phpbb_extension_finder This object for chaining calls
	*/
	public function extension_directory($extension_directory)
	{
		$this->query['extension_directory'] = $this->sanitise_directory($extension_directory);
		return $this;
	}

	/**
	* Sets a directory all files found in the core path must be contained in
	*
	* @param string $core_directory
	* @return phpbb_extension_finder This object for chaining calls
	*/
	public function core_directory($core_directory)
	{
		$this->query['core_directory'] = $this->sanitise_directory($core_directory);
		return $this;
	}

	/**
	* Removes occurances of /./ and makes sure path ends without trailing slash
	*
	* @param string $directory A directory pattern
	* @return string A cleaned up directory pattern
	*/
	protected function sanitise_directory($directory)
	{
		$directory = $this->filesystem->clean_path($directory);
		$dir_len = strlen($directory);

		if ($dir_len > 1 && $directory[$dir_len - 1] === '/')
		{
			$directory = substr($directory, 0, -1);
		}

		return $directory;
	}

	/**
	* Finds classes matching the configured options if they follow phpBB naming rules.
	*
	* The php file extension is automatically added to suffixes.
	*
	* Note: If a file is matched but contains a class name not following the
	* phpBB naming rules an incorrect class name will be returned.
	*
	* @param bool $cache Whether the result should be cached
	* @param bool $use_all_available Use all available instead of just all
	* 						enabled extensions
	* @return array An array of found class names
	*/
	public function get_classes($cache = true, $use_all_available = false)
	{
		$this->query['extension_suffix'] .= '.' . $this->php_ext;
		$this->query['core_suffix'] .= '.' . $this->php_ext;

		$files = $this->find($cache, false, $use_all_available);

		return $this->get_classes_from_files($files);
	}

	/**
	* Get class names from a list of files
	*
	* @param array $files Array of files (from find())
	* @return array Array of class names
	*/
	public function get_classes_from_files($files)
	{
		$classes = array();
		foreach ($files as $file => $ext_name)
		{
			$file = preg_replace('#^includes/#', '', $file);

			$classes[] = 'phpbb_' . str_replace('/', '_', substr($file, 0, -strlen('.' . $this->php_ext)));
		}
		return $classes;
	}

	/**
	* Finds all directories matching the configured options
	*
	* @param bool $cache Whether the result should be cached
	* @param bool $use_all_available Use all available instead of just all
	* 						enabled extensions
	* @param bool $extension_keys Whether the result should have extension name as array key
	* @return array An array of paths to found directories
	*/
	public function get_directories($cache = true, $use_all_available = false, $extension_keys = false)
	{
		return $this->find_with_root_path($cache, true, $use_all_available, $extension_keys);
	}

	/**
	* Finds all files matching the configured options.
	*
	* @param bool $cache Whether the result should be cached
	* @param bool $use_all_available Use all available instead of just all
	* 						enabled extensions
	* @return array An array of paths to found files
	*/
	public function get_files($cache = true, $use_all_available = false)
	{
		return $this->find_with_root_path($cache, false, $use_all_available);
	}

	/**
	* A wrapper around the general find which prepends a root path to results
	*
	* @param bool $cache Whether the result should be cached
	* @param bool $is_dir Directories will be returned when true, only files
	*                     otherwise
	* @param bool $use_all_available Use all available instead of just all
	* 						enabled extensions
	* @param bool $extension_keys If true, result will be associative array
	*					with extension name as key
	* @return array An array of paths to found items
	*/
	protected function find_with_root_path($cache = true, $is_dir = false, $use_all_available = false, $extension_keys = false)
	{
		$items = $this->find($cache, $is_dir, $use_all_available);

		$result = array();
		foreach ($items as $item => $ext_name)
		{
			if ($extension_keys)
			{
				$result[$ext_name] = $this->phpbb_root_path . $item;
			}
			else
			{
				$result[] = $this->phpbb_root_path . $item;
			}
		}

		return $result;
	}

	/**
	* Finds all file system entries matching the configured options
	*
	* @param bool $cache Whether the result should be cached
	* @param bool $is_dir Directories will be returned when true, only files
	*                     otherwise
	* @param bool $use_all_available Use all available instead of just all
	* 						enabled extensions
	* @return array An array of paths to found items
	*/
	public function find($cache = true, $is_dir = false, $use_all_available = false)
	{
		if ($use_all_available)
		{
			$extensions = $this->extension_manager->all_available();
		}
		else
		{
			$extensions = $this->extension_manager->all_enabled();
		}

		if ($this->query['core_path'])
		{
			$extensions['/'] = $this->phpbb_root_path . $this->query['core_path'];
		}

		$files = array();
		$file_list = $this->find_from_paths($extensions, $cache, $is_dir);

		foreach ($file_list as $file)
		{
			$files[$file['named_path']] = $file['ext_name'];
		}

		return $files;
	}
	
	/**
	* Finds all file system entries matching the configured options for one
	* specific extension
	*
	* @param string $extension_name Name of the extension
	* @param string $extension_path Relative path to the extension root directory
	* @param bool $cache Whether the result should be cached
	* @param bool $is_dir Directories will be returned when true, only files
	*                     otherwise
	* @return array An array of paths to found items
	*/
	public function find_from_extension($extension_name, $extension_path, $cache = true, $is_dir = false)
	{
		$extensions = array(
			$extension_name => $extension_path,
		);

		$files = array();
		$file_list = $this->find_from_paths($extensions, $cache, $is_dir);

		foreach ($file_list as $file)
		{
			$files[$file['named_path']] = $file['ext_name'];
		}

		return $files;
	}

	/**
	* Finds all file system entries matching the configured options from
	* an array of paths
	*
	* @param array $extensions Array of extensions (name => full relative path)
	* @param bool $cache Whether the result should be cached
	* @param bool $is_dir Directories will be returned when true, only files
	*                     otherwise
	* @return array An array of paths to found items
	*/
	public function find_from_paths($extensions, $cache = true, $is_dir = false)
	{
		$this->query['is_dir'] = $is_dir;
		$query = md5(serialize($this->query) . serialize($extensions));

		if (!defined('DEBUG') && $cache && isset($this->cached_queries[$query]))
		{
			return $this->cached_queries[$query];
		}

		$files = array();

		foreach ($extensions as $name => $path)
		{
			$ext_name = $name;

			if (!file_exists($path))
			{
				continue;
			}

			if ($name === '/')
			{
				$location = $this->query['core_path'];
				$name = '';
				$suffix = $this->query['core_suffix'];
				$prefix = $this->query['core_prefix'];
				$directory = $this->query['core_directory'];
			}
			else
			{
				$location = 'ext/';
				$name .= '/';
				$suffix = $this->query['extension_suffix'];
				$prefix = $this->query['extension_prefix'];
				$directory = $this->query['extension_directory'];
			}

			// match only first directory if leading slash is given
			if ($directory === '/')
			{
				$directory_pattern = '^' . preg_quote(DIRECTORY_SEPARATOR, '#');
			}
			else if ($directory && $directory[0] === '/')
			{
				$directory_pattern = '^' . preg_quote(str_replace('/', DIRECTORY_SEPARATOR, $directory) . DIRECTORY_SEPARATOR, '#');
			}
			else
			{
				$directory_pattern = preg_quote(DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $directory) . DIRECTORY_SEPARATOR, '#');
			}
			if ($is_dir)
			{
				$directory_pattern .= '$';
			}
			$directory_pattern = '#' . $directory_pattern . '#';

			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
			foreach ($iterator as $file_info)
			{
				$filename = $file_info->getFilename();
				if ($filename == '.' || $filename == '..')
				{
					continue;
				}

				if ($file_info->isDir() == $is_dir)
				{
					if ($is_dir)
					{
						$relative_path = $iterator->getInnerIterator()->getSubPath() . DIRECTORY_SEPARATOR . basename($filename) . DIRECTORY_SEPARATOR;
						if ($relative_path[0] !== DIRECTORY_SEPARATOR)
						{
							$relative_path = DIRECTORY_SEPARATOR . $relative_path;
						}
					}
					else
					{
						$relative_path = DIRECTORY_SEPARATOR . $iterator->getInnerIterator()->getSubPathname();
					}

					if ((!$suffix || substr($relative_path, -strlen($suffix)) === $suffix) &&
						(!$prefix || substr($filename, 0, strlen($prefix)) === $prefix) &&
						(!$directory || preg_match($directory_pattern, $relative_path)))
					{
						$files[] = array(
							'named_path'	=> str_replace(DIRECTORY_SEPARATOR, '/', $location . $name . substr($relative_path, 1)),
							'ext_name'		=> $ext_name,
							'path'			=> str_replace(array(DIRECTORY_SEPARATOR, $this->phpbb_root_path), array('/', ''), $file_info->getPath()) . '/',
							'filename'		=> $filename,
						);
					}
				}
			}
		}

		if ($cache && $this->cache)
		{
			$this->cached_queries[$query] = $files;
			$this->cache->put($this->cache_name, $this->cached_queries);
		}

		return $files;
	}
}
