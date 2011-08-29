<?php
/**
*
* @package extension
* @copyright (c) 2011 phpBB Group
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

	/**
	* Creates a manager and loads information from database
	*
	* @param dbal $db A database connection
	* @param string $extension_table The name of the table holding extensions
	* @param string $phpbb_root_path Path to the phpbb includes directory.
	* @param string $phpEx php file extension
	* @param phpbb_cache_driver_interface $cache A cache instance or null
	*/
	public function __construct(dbal $db, $extension_table, $phpbb_root_path, $phpEx = '.php', phpbb_cache_driver_interface $cache = null)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->db = $db;
		$this->cache = $cache;
		$this->phpEx = $phpEx;
		$this->extension_table = $extension_table;

		if (false === ($this->extensions = $this->cache->get('_extensions')))
		{
			$this->load_extensions();
		}
	}

	/**
	* Loads all extension information from the database
	*
	* @return null
	*/
	protected function load_extensions()
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
		$this->cache->put('_extensions', $this->extensions);
	}

	/**
	* Generates the path to an extension
	*
	* @param string $name The name of the extension
	* @return string Path to an extension
	*/
	public function get_extension_path($name)
	{
		return $this->phpbb_root_path . 'ext/' . basename($name) . '/';
	}

	/**
	* Instantiates the extension meta class for the given name.
	*
	* @param string $name The extension name
	* @return phpbb_extension_interface Instance of the extension meta class or
	*                     phpbb_extension_base if the class does not exist
	*/
	public function get_extension($name)
	{
		$extension_class_name = 'phpbb_ext_' . $name;

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
	* Enables an extension
	*
	* Calls the enable method on the extension's meta class to allow it to
	* make database changes and execute other initialisation code.
	*
	* @param string $name The extension's name
	* @return null
	*/
	public function enable($name)
	{
		// ignore extensions that are already enabled
		if (isset($this->extensions[$name]) && $this->extensions[$name]['ext_active'])
		{
			return;
		}

		$extension = $this->get_extension($name);
		$extension->enable();

		$extension_data = array(
			'ext_name'	=> $name,
			'ext_active'	=> true,
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
	}

	/**
	* Disables an extension
	*
	* Calls the disable method on the extension's meta class to allow it to
	* process the event.
	*
	* @param string $name The extension's name
	* @return null
	*/
	public function disable($name)
	{
		// ignore extensions that are already disabled
		if (!isset($this->extensions[$name]) || !$this->extensions[$name]['ext_active'])
		{
			return;
		}

		$extension = $this->get_extension($name);
		$extension->disable();

		$extension_data = array(
			'ext_active'	=> false,
		);
		$this->extensions[$name]['ext_active'] = false;
		ksort($this->extensions);

		$sql = 'UPDATE ' . $this->extension_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $extension_data) . "
			WHERE ext_name = '" . $this->db->sql_escape($name) . "'";
		$this->db->sql_query($sql);
	}

	/**
	* Purge an extension
	*
	* Disables the extension first if active, and then calls purge on the
	* extension's meta class to delete the extension's database content.
	*
	* @param string $name The extension's name
	* @return null
	*/
	public function purge($name)
	{
		// ignore extensions that do not exist
		if (!isset($this->extensions[$name]))
		{
			return;
		}

		// disable first if necessary
		if ($this->extensions[$name]['ext_active'])
		{
			$this->disable($name);
		}

		$extension = $this->get_extension($name);
		$extension->purge();

		unset($this->extensions[$name]);

		$sql = 'DELETE FROM ' . $this->extension_table . "
			WHERE ext_name = '" . $this->db->sql_escape($name) . "'";
		$this->db->sql_query($sql);
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

		$iterator = new DirectoryIterator($this->phpbb_root_path . 'ext/');
		foreach ($iterator as $file_info)
		{
			$path = $this->phpbb_root_path . 'ext/' . $file_info->getBasename() . '/';
			if (!$file_info->isDot() && $file_info->isDir() && file_exists($path))
			{
				$available[$file_info->getBasename()] = $path;
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
		return $this->extensions;
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
				$enabled[$name] = $data['ext_path'];
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
				$disabled[$name] = $data['ext_path'];
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
		return new phpbb_extension_finder($this, $this->phpbb_root_path, $this->cache, $this->phpEx);
	}
}
