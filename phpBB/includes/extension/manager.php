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
* The extension manager provides means to activate/deactivate extensions.
*
* @package extension
*/
class phpbb_extension_manager
{
	protected $cache;
	protected $phpEx;
	protected $extensions;
	protected $extension_table;
	protected $phpbb_root_path;
	protected $cache_name;

	/**
	* Creates a manager and loads information from database
	*
	* @param dbal $db A database connection
	* @param string $extension_table The name of the table holding extensions
	* @param string $phpbb_root_path Path to the phpbb includes directory.
	* @param string $phpEx php file extension
	* @param phpbb_cache_driver_interface $cache A cache instance or null
	* @param string $cache_name The name of the cache variable, defaults to _ext
	*/
	public function __construct(dbal $db, $extension_table, $phpbb_root_path, $phpEx = '.php', phpbb_cache_driver_interface $cache = null, $cache_name = '_ext')
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->db = $db;
		$this->cache = $cache;
		$this->phpEx = $phpEx;
		$this->extension_table = $extension_table;
		$this->cache_name = $cache_name;

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
		$sql = 'SELECT *
			FROM ' . $this->extension_table;

		$result = $this->db->sql_query($sql);
		$extensions = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$this->extensions = array();
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
	* @return phpbb_extension_interface Instance of the extension meta class or
	*                     phpbb_extension_base if the class does not exist
	*/
	public function get_extension($name)
	{
		$extension_class_name = 'phpbb_ext_' . str_replace('/', '_', $name) . '_ext';

		if (class_exists($extension_class_name))
		{
			return new $extension_class_name;
		}
		else
		{
			return new phpbb_extension_base;
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
		if (isset($this->extensions[$name]) && $this->extensions[$name]['ext_active'])
		{
			return false;
		}

		$old_state = (isset($this->extensions[$name]['ext_state'])) ? unserialize($this->extensions[$name]['ext_state']) : false;

		$extension = $this->get_extension($name);
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

		$sql = 'UPDATE ' . $this->extension_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $extension_data) . "
			WHERE ext_name = '" . $this->db->sql_escape($name) . "'";
		$this->db->sql_query($sql);

		if (!$this->db->sql_affectedrows())
		{
			$sql = 'INSERT INTO ' . $this->extension_table . '
				' . $this->db->sql_build_array('INSERT', $extension_data);
			$this->db->sql_query($sql);
		}

		if ($this->cache)
		{
			$this->cache->destroy($this->cache_name);
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
		while ($this->enable_step($name));
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
				$this->cache->destroy($this->cache_name);
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
			$this->cache->destroy($this->cache_name);
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
		while ($this->disable_step($name));
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
				$this->cache->destroy($this->cache_name);
			}

			return true;
		}

		unset($this->extensions[$name]);

		$sql = 'DELETE FROM ' . $this->extension_table . "
			WHERE ext_name = '" . $this->db->sql_escape($name) . "'";
		$this->db->sql_query($sql);

		if ($this->cache)
		{
			$this->cache->destroy($this->cache_name);
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
		while ($this->purge_step($name));
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

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($this->phpbb_root_path . 'ext/'),
			RecursiveIteratorIterator::SELF_FIRST);
		foreach ($iterator as $file_info)
		{
			if ($file_info->isFile() && $file_info->getFilename() == 'ext' . $this->phpEx)
			{
				$ext_name = $iterator->getInnerIterator()->getSubPath();

				$ext_name = str_replace(DIRECTORY_SEPARATOR, '/', $ext_name);

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
	* Instantiates a phpbb_extension_finder.
	*
	* @return phpbb_extension_finder An extension finder instance
	*/
	public function get_finder()
	{
		return new phpbb_extension_finder($this, $this->phpbb_root_path, $this->cache, $this->phpEx, $this->cache_name . '_finder');
	}
}
