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

use phpbb\exception\runtime_exception;
use phpbb\file_downloader;
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
	* @param \phpbb\filesystem\filesystem_interface $filesystem
	* @param string $extension_table The name of the table holding extensions
	* @param string $phpbb_root_path Path to the phpbb includes directory.
	* @param string $php_ext php file extension, defaults to php
	* @param \phpbb\cache\service $cache A cache instance or null
	* @param string $cache_name The name of the cache variable, defaults to _ext
	*/
	public function __construct(ContainerInterface $container, \phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\filesystem\filesystem_interface $filesystem, $extension_table, $phpbb_root_path, $php_ext = 'php', \phpbb\cache\service $cache = null, $cache_name = '_ext')
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
	* @return \phpbb\extension\metadata_manager Instance of the metadata manager
	*/
	public function create_extension_metadata_manager($name)
	{
		if (!isset($this->extensions[$name]['metadata']))
		{
			$metadata = new \phpbb\extension\metadata_manager($name, $this->get_extension_path($name, true));
			$this->extensions[$name]['metadata'] = $metadata;
		}
		return $this->extensions[$name]['metadata'];
	}

	/**
	* Update the database entry for an extension
	*
	* @param string $name Extension name to update
	* @param array	$data Data to update in the database
	* @param string	$action Action to perform, by default 'update', may be also 'insert' or 'delete'
	*/
	protected function update_state($name, $data, $action = 'update')
	{
		switch ($action)
		{
			case 'insert':
				$this->extensions[$name] = $data;
				$this->extensions[$name]['ext_path'] = $this->get_extension_path($name);
				ksort($this->extensions);
				$sql = 'INSERT INTO ' . $this->extension_table . ' ' . $this->db->sql_build_array('INSERT', $data);
				$this->db->sql_query($sql);
			break;

			case 'update':
				$this->extensions[$name] = array_merge($this->extensions[$name], $data);
				$sql = 'UPDATE ' . $this->extension_table . '
					SET ' . $this->db->sql_build_array('UPDATE', $data) . "
					WHERE ext_name = '" . $this->db->sql_escape($name) . "'";
				$this->db->sql_query($sql);
			break;

			case 'delete':
				unset($this->extensions[$name]);
				$sql = 'DELETE FROM ' . $this->extension_table . "
					WHERE ext_name = '" . $this->db->sql_escape($name) . "'";
				$this->db->sql_query($sql);
			break;
		}

		if ($this->cache)
		{
			$this->cache->deferred_purge();
		}
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
		if ($this->is_enabled($name))
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

		$this->update_state($name, $extension_data, $this->is_configured($name) ? 'update' : 'insert');

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
		// ignore extensions that are not enabled
		if (!$this->is_enabled($name))
		{
			return false;
		}

		$old_state = unserialize($this->extensions[$name]['ext_state']);

		$extension = $this->get_extension($name);
		$state = $extension->disable_step($old_state);
		$active = ($state !== false);

		$extension_data = array(
			'ext_active'	=> $active,
			'ext_state'		=> serialize($state),
		);
		$this->update_state($name, $extension_data);

		return $active;
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
		// ignore extensions that are not configured
		if (!$this->is_configured($name))
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
		$purged = ($state === false);

		$extension_data = array(
			'ext_state'	=> serialize($state),
		);

		$this->update_state($name, $extension_data, $purged ? 'delete' : 'update');

		// continue until the state is false
		return !$purged;
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
				$ext_name = str_replace(DIRECTORY_SEPARATOR, '/', $ext_name);
				if ($this->is_available($ext_name))
				{
					$available[$ext_name] = $this->get_extension_path($ext_name, true);
				}
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
	* @param bool $phpbb_relative Whether the path should be relative to phpbb root
	*
	* @return array An array with extension names as keys and and the
	*               database stored extension information as values
	*/
	public function all_configured($phpbb_relative = true)
	{
		$configured = array();
		foreach ($this->extensions as $name => $data)
		{
			if ($this->is_configured($name))
			{
				unset($data['metadata']);
				$data['ext_path'] = ($phpbb_relative ? $this->phpbb_root_path : '') . $data['ext_path'];
				$configured[$name] = $data;
			}
		}
		return $configured;
	}

	/**
	* Retrieves all enabled extensions.
	* @param bool $phpbb_relative Whether the path should be relative to phpbb root
	*
	* @return array An array with extension names as keys and and the
	*               database stored extension information as values
	*/
	public function all_enabled($phpbb_relative = true)
	{
		$enabled = array();
		foreach ($this->extensions as $name => $data)
		{
			if ($this->is_enabled($name))
			{
				$enabled[$name] = ($phpbb_relative ? $this->phpbb_root_path : '') . $data['ext_path'];
			}
		}
		return $enabled;
	}

	/**
	* Retrieves all disabled extensions.
	*
	* @param bool $phpbb_relative Whether the path should be relative to phpbb root
	*
	* @return array An array with extension names as keys and and the
	*               database stored extension information as values
	*/
	public function all_disabled($phpbb_relative = true)
	{
		$disabled = array();
		foreach ($this->extensions as $name => $data)
		{
			if ($this->is_disabled($name))
			{
				$disabled[$name] = ($phpbb_relative ? $this->phpbb_root_path : '') . $data['ext_path'];
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
		$md_manager = $this->create_extension_metadata_manager($name);
		try
		{
			return $md_manager->get_metadata('all') && $md_manager->validate_enable();
		}
		catch (\phpbb\extension\exception $e)
		{
			return false;
		}
	}

	/**
	* Check to see if a given extension is enabled
	*
	* @param string $name Extension name to check
	* @return bool Depending on whether or not the extension is enabled
	*/
	public function is_enabled($name)
	{
		return isset($this->extensions[$name]['ext_active']) && $this->extensions[$name]['ext_active'];
	}

	/**
	* Check to see if a given extension is disabled
	*
	* @param string $name Extension name to check
	* @return bool Depending on whether or not the extension is disabled
	*/
	public function is_disabled($name)
	{
		return isset($this->extensions[$name]['ext_active']) && !$this->extensions[$name]['ext_active'];
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
		return isset($this->extensions[$name]['ext_active']);
	}

	/**
	* Check the version and return the available updates (for an extension).
	*
	* @param \phpbb\extension\metadata_manager $md_manager The metadata manager for the version to check.
	* @param bool $force_update Ignores cached data. Defaults to false.
	* @param bool $force_cache Force the use of the cache. Override $force_update.
	* @param string $stability Force the stability (null by default).
	* @return array
	* @throws runtime_exception
	*/
	public function version_check(\phpbb\extension\metadata_manager $md_manager, $force_update = false, $force_cache = false, $stability = null)
	{
		$meta = $md_manager->get_metadata('all');

		if (!isset($meta['extra']['version-check']))
		{
			throw new runtime_exception('NO_VERSIONCHECK');
		}

		$version_check = $meta['extra']['version-check'];

		$version_helper = new \phpbb\version_helper($this->cache, $this->config, new file_downloader());
		$version_helper->set_current_version($meta['version']);
		$version_helper->set_file_location($version_check['host'], $version_check['directory'], $version_check['filename'], isset($version_check['ssl']) ? $version_check['ssl'] : false);
		$version_helper->force_stability($stability);

		return $version_helper->get_ext_update_on_branch($force_update, $force_cache);
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
