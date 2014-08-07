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

namespace phpbb\extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* The extension manager provides means to activate/deactivate extensions.
*/
class manager
{
	/** @var ContainerInterface */
	protected $container;

	protected $db;
	protected $config;
	protected $cache;
	protected $user;
	protected $php_ext;
	protected $extensions;
	protected $extension_table;
	protected $phpbb_root_path;
	protected $cache_name;

	/**
	* Creates a manager and loads information from database
	*
	* @param ContainerInterface $container A container
	* @param \phpbb\db\driver\driver_interface $db A database connection
	* @param \phpbb\config\config $config Config object
	* @param \phpbb\filesystem $filesystem
	* @param \phpbb\user $user User object
	* @param string $extension_table The name of the table holding extensions
	* @param string $phpbb_root_path Path to the phpbb includes directory.
	* @param string $php_ext php file extension, defaults to php
	* @param \phpbb\cache\driver\driver_interface $cache A cache instance or null
	* @param string $cache_name The name of the cache variable, defaults to _ext
	*/
	public function __construct(ContainerInterface $container, \phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\filesystem $filesystem, \phpbb\user $user, $extension_table, $phpbb_root_path, $php_ext = 'php', \phpbb\cache\driver\driver_interface $cache = null, $cache_name = '_ext')
	{
		$this->cache = $cache;
		$this->cache_name = $cache_name;
		$this->config = $config;
		$this->container = $container;
		$this->db = $db;
		$this->extension_table = $extension_table;
		$this->filesystem = $filesystem;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->user = $user;

		$this->extensions = ($this->cache) ? $this->cache->get($this->cache_name) : false;

		if ($this->extensions === false)
		{
			$this->load_extensions();
		}
	}

	/**
	* Loads all extension information from the database
	*
	* @return null
	*/
	public function load_extensions()
	{
		$this->extensions = array();

		// Do not try to load any extensions if the extension table
		// does not exist or when installing or updating.
		// Note: database updater invokes this code, and in 3.0
		// there is no extension table therefore the rest of this function
		// fails
		if (defined('IN_INSTALL') || version_compare($this->config['version'], '3.1.0-dev', '<'))
		{
			return;
		}

		$sql = 'SELECT *
			FROM ' . $this->extension_table;

		$result = $this->db->sql_query($sql);
		$extensions = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		foreach ($extensions as $extension)
		{
			$extension['ext_path'] = $this->get_extension_path($extension['ext_name']);
			$this->extensions[$extension['ext_name']] = $extension;
		}

		ksort($this->extensions);

		if ($this->cache)
		{
			$this->cache->put($this->cache_name, $this->extensions);
		}
	}

	/**
	* Generates the path to an extension
	*
	* @param string $name The name of the extension
	* @param bool $phpbb_relative Whether the path should be relative to phpbb root
	* @return string Path to an extension
	*/
	public function get_extension_path($name, $phpbb_relative = false)
	{
		$name = str_replace('.', '', $name);

		return (($phpbb_relative) ? $this->phpbb_root_path : '') . 'ext/' . $name . '/';
	}

	/**
	* Instantiates the extension meta class for the extension with the given name
	*
	* @param string $name The extension name
	* @return \phpbb\extension\extension_interface Instance of the extension meta class or
	*                     \phpbb\extension\base if the class does not exist
	*/
	public function get_extension($name)
	{
		$extension_class_name = str_replace('/', '\\', $name) . '\\ext';

		$migrator = $this->container->get('migrator');

		if (class_exists($extension_class_name))
		{
			return new $extension_class_name($this->container, $this->get_finder(), $migrator, $name, $this->get_extension_path($name, true));
		}
		else
		{
			return new \phpbb\extension\base($this->container, $this->get_finder(), $migrator, $name, $this->get_extension_path($name, true));
		}
	}

	/**
	* Instantiates the metadata manager for the extension with the given name
	*
	* @param string $name The extension name
	* @param \phpbb\template\template $template The template manager
	* @return \phpbb\extension\metadata_manager Instance of the metadata manager
	*/
	public function create_extension_metadata_manager($name, \phpbb\template\template $template)
	{
		return new \phpbb\extension\metadata_manager($name, $this->config, $this, $template, $this->user, $this->phpbb_root_path);
	}

	/**
	* Runs a step of the extension enabling process.
	*
	* Allows the exentension to enable in a long running script that works
	* in multiple steps across requests. State is kept for the extension
	* in the extensions table.
	*
	* @param	string	$name	The extension's name
	* @return	bool			False if enabling is finished, true otherwise
	*/
	public function enable_step($name)
	{
		// ignore extensions that are already enabled
		if (isset($this->extensions[$name]) && $this->extensions[$name]['ext_active'])
		{
			return false;
		}

		$old_state = (isset($this->extensions[$name]['ext_state'])) ? unserialize($this->extensions[$name]['ext_state']) : false;

		$extension = $this->get_extension($name);

		if (!$extension->is_enableable())
		{
			return false;
		}

		$state = $extension->enable_step($old_state);

		$active = ($state === false);

		$extension_data = array(
			'ext_name'		=> $name,
			'ext_active'	=> $active,
			'ext_state'		=> serialize($state),
		);

		$this->extensions[$name] = $extension_data;
		$this->extensions[$name]['ext_path'] = $this->get_extension_path($extension_data['ext_name']);
		ksort($this->extensions);

		$sql = 'SELECT COUNT(ext_name) as row_count
			FROM ' . $this->extension_table . "
			WHERE ext_name = '" . $this->db->sql_escape($name) . "'";
		$result = $this->db->sql_query($sql);
		$count = $this->db->sql_fetchfield('row_count');
		$this->db->sql_freeresult($result);

		if ($count)
		{
			$sql = 'UPDATE ' . $this->extension_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $extension_data) . "
				WHERE ext_name = '" . $this->db->sql_escape($name) . "'";
			$this->db->sql_query($sql);
		}
		else
		{
			$sql = 'INSERT INTO ' . $this->extension_table . '
				' . $this->db->sql_build_array('INSERT', $extension_data);
			$this->db->sql_query($sql);
		}

		if ($this->cache)
		{
			$this->cache->purge();
		}

		if ($active)
		{
			$this->config->increment('assets_version', 1);
		}

		return !$active;
	}

	/**
	* Enables an extension
	*
	* This method completely enables an extension. But it could be long running
	* so never call this in a script that has a max_execution time.
	*
	* @param string $name The extension's name
	* @return null
	*/
	public function enable($name)
	{
		// @codingStandardsIgnoreStart
		while ($this->enable_step($name));
		// @codingStandardsIgnoreEnd
	}

	/**
	* Disables an extension
	*
	* Calls the disable method on the extension's meta class to allow it to
	* process the event.
	*
	* @param string $name The extension's name
	* @return bool False if disabling is finished, true otherwise
	*/
	public function disable_step($name)
	{
		// ignore extensions that are already disabled
		if (!isset($this->extensions[$name]) || !$this->extensions[$name]['ext_active'])
		{
			return false;
		}

		$old_state = unserialize($this->extensions[$name]['ext_state']);

		$extension = $this->get_extension($name);
		$state = $extension->disable_step($old_state);

		// continue until the state is false
		if ($state !== false)
		{
			$extension_data = array(
				'ext_state'		=> serialize($state),
			);
			$this->extensions[$name]['ext_state'] = serialize($state);

			$sql = 'UPDATE ' . $this->extension_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $extension_data) . "
				WHERE ext_name = '" . $this->db->sql_escape($name) . "'";
			$this->db->sql_query($sql);

			if ($this->cache)
			{
				$this->cache->purge();
			}

			return true;
		}

		$extension_data = array(
			'ext_active'	=> false,
			'ext_state'		=> serialize(false),
		);
		$this->extensions[$name]['ext_active'] = false;
		$this->extensions[$name]['ext_state'] = serialize(false);

		$sql = 'UPDATE ' . $this->extension_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $extension_data) . "
			WHERE ext_name = '" . $this->db->sql_escape($name) . "'";
		$this->db->sql_query($sql);

		if ($this->cache)
		{
			$this->cache->purge();
		}

		return false;
	}

	/**
	* Disables an extension
	*
	* Disables an extension completely at once. This process could run for a
	* while so never call this in a script that has a max_execution time.
	*
	* @param string $name The extension's name
	* @return null
	*/
	public function disable($name)
	{
		// @codingStandardsIgnoreStart
		while ($this->disable_step($name));
		// @codingStandardsIgnoreEnd
	}

	/**
	* Purge an extension
	*
	* Disables the extension first if active, and then calls purge on the
	* extension's meta class to delete the extension's database content.
	*
	* @param string $name The extension's name
	* @return bool False if purging is finished, true otherwise
	*/
	public function purge_step($name)
	{
		// ignore extensions that do not exist
		if (!isset($this->extensions[$name]))
		{
			return false;
		}

		// disable first if necessary
		if ($this->extensions[$name]['ext_active'])
		{
			$this->disable($name);
		}

		$old_state = unserialize($this->extensions[$name]['ext_state']);

		$extension = $this->get_extension($name);
		$state = $extension->purge_step($old_state);

		// continue until the state is false
		if ($state !== false)
		{
			$extension_data = array(
				'ext_state'		=> serialize($state),
			);
			$this->extensions[$name]['ext_state'] = serialize($state);

			$sql = 'UPDATE ' . $this->extension_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $extension_data) . "
				WHERE ext_name = '" . $this->db->sql_escape($name) . "'";
			$this->db->sql_query($sql);

			if ($this->cache)
			{
				$this->cache->purge();
			}

			return true;
		}

		unset($this->extensions[$name]);

		$sql = 'DELETE FROM ' . $this->extension_table . "
			WHERE ext_name = '" . $this->db->sql_escape($name) . "'";
		$this->db->sql_query($sql);

		if ($this->cache)
		{
			$this->cache->purge();
		}

		return false;
	}

	/**
	* Purge an extension
	*
	* Purges an extension completely at once. This process could run for a while
	* so never call this in a script that has a max_execution time.
	*
	* @param string $name The extension's name
	* @return null
	*/
	public function purge($name)
	{
		// @codingStandardsIgnoreStart
		while ($this->purge_step($name));
		// @codingStandardsIgnoreEnd
	}

	/**
	* Retrieves a list of all available extensions on the filesystem
	*
	* @return array An array with extension names as keys and paths to the
	*               extension as values
	*/
	public function all_available()
	{
		$available = array();
		if (!is_dir($this->phpbb_root_path . 'ext/'))
		{
			return $available;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \phpbb\recursive_dot_prefix_filter_iterator(
				new \RecursiveDirectoryIterator($this->phpbb_root_path . 'ext/', \FilesystemIterator::NEW_CURRENT_AND_KEY | \FilesystemIterator::FOLLOW_SYMLINKS)
			),
			\RecursiveIteratorIterator::SELF_FIRST
		);
		$iterator->setMaxDepth(2);

		foreach ($iterator as $file_info)
		{
			if ($file_info->isFile() && $file_info->getFilename() == 'composer.json')
			{
				$ext_name = $iterator->getInnerIterator()->getSubPath();
				$composer_file = $iterator->getPath() . '/composer.json';

				// Ignore the extension if there is no composer.json.
				if (!is_readable($composer_file) || !($ext_info = file_get_contents($composer_file)))
				{
					continue;
				}

				$ext_info = json_decode($ext_info, true);
				$ext_name = str_replace(DIRECTORY_SEPARATOR, '/', $ext_name);

				// Ignore the extension if directory depth is not correct or if the directory structure
				// does not match the name value specified in composer.json.
				if (substr_count($ext_name, '/') !== 1 || !isset($ext_info['name']) || $ext_name != $ext_info['name'])
				{
					continue;
				}

				$available[$ext_name] = $this->phpbb_root_path . 'ext/' . $ext_name . '/';
			}
		}
		ksort($available);
		return $available;
	}

	/**
	* Retrieves all configured extensions.
	*
	* All enabled and disabled extensions are considered configured. A purged
	* extension that is no longer in the database is not configured.
	*
	* @return array An array with extension names as keys and and the
	*               database stored extension information as values
	*/
	public function all_configured()
	{
		$configured = array();
		foreach ($this->extensions as $name => $data)
		{
			$data['ext_path'] = $this->phpbb_root_path . $data['ext_path'];
			$configured[$name] = $data;
		}
		return $configured;
	}

	/**
	* Retrieves all enabled extensions.
	*
	* @return array An array with extension names as keys and and the
	*               database stored extension information as values
	*/
	public function all_enabled()
	{
		$enabled = array();
		foreach ($this->extensions as $name => $data)
		{
			if ($data['ext_active'])
			{
				$enabled[$name] = $this->phpbb_root_path . $data['ext_path'];
			}
		}
		return $enabled;
	}

	/**
	* Retrieves all disabled extensions.
	*
	* @return array An array with extension names as keys and and the
	*               database stored extension information as values
	*/
	public function all_disabled()
	{
		$disabled = array();
		foreach ($this->extensions as $name => $data)
		{
			if (!$data['ext_active'])
			{
				$disabled[$name] = $this->phpbb_root_path . $data['ext_path'];
			}
		}
		return $disabled;
	}

	/**
	* Check to see if a given extension is available on the filesystem
	*
	* @param string $name Extension name to check NOTE: Can be user input
	* @return bool Depending on whether or not the extension is available
	*/
	public function is_available($name)
	{
		return file_exists($this->get_extension_path($name, true));
	}

	/**
	* Check to see if a given extension is enabled
	*
	* @param string $name Extension name to check
	* @return bool Depending on whether or not the extension is enabled
	*/
	public function is_enabled($name)
	{
		return isset($this->extensions[$name]) && $this->extensions[$name]['ext_active'];
	}

	/**
	* Check to see if a given extension is disabled
	*
	* @param string $name Extension name to check
	* @return bool Depending on whether or not the extension is disabled
	*/
	public function is_disabled($name)
	{
		return isset($this->extensions[$name]) && !$this->extensions[$name]['ext_active'];
	}

	/**
	* Check to see if a given extension is configured
	*
	* All enabled and disabled extensions are considered configured. A purged
	* extension that is no longer in the database is not configured.
	*
	* @param string $name Extension name to check
	* @return bool Depending on whether or not the extension is configured
	*/
	public function is_configured($name)
	{
		return isset($this->extensions[$name]);
	}

	/**
	* Check to see if a given extension is purged
	*
	* An extension is purged if it is available, not enabled and not disabled.
	*
	* @param string $name Extension name to check
	* @return bool Depending on whether or not the extension is purged
	*/
	public function is_purged($name)
	{
		return $this->is_available($name) && !$this->is_configured($name);
	}

	/**
	* Instantiates a \phpbb\finder.
	*
	* @param bool $use_all_available Should we load all extensions, or just enabled ones
	* @return \phpbb\finder An extension finder instance
	*/
	public function get_finder($use_all_available = false)
	{
		$finder = new \phpbb\finder($this->filesystem, $this->phpbb_root_path, $this->cache, $this->php_ext, $this->cache_name . '_finder');
		if ($use_all_available)
		{
			$finder->set_extensions(array_keys($this->all_available()));
		}
		else
		{
			$finder->set_extensions(array_keys($this->all_enabled()));
		}
		return $finder;
	}
}
