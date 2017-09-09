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

/**
* The safe extension class loader resolves class names to file system paths
* and loads them if necessary.
* In case any file system path is not available, the corresponding extension
* is disabled, and cache is purged, avoiding further errors due to the same
* issue.
*/

class safe_class_loader extends \phpbb\class_loader
{
	/** @var string */
	protected $phpbb_root_path;

	/** @var \phpbb\db\driver\factory */
	protected $db = null;

	/** @var boolean */
	protected $is_in_admin;

	/** @var string */
	protected $extension_table = '';

	/**
	* Extends the \phpbb\class_loader, which loads files with the given
	* file extension from the given path.
	*
	* @param string $namespace	Required namespace for files to be loaded
	* @param string $path		Directory to load files from relative to root path
	* @param string $root_path	Root path for the forum
	* @param string $php_ext	The file extension for PHP files
	* @param \phpbb\cache\driver\driver_interface	$cache		An implementation of the phpBB cache interface
	*/
	public function __construct($namespace, $path, $root_path, $php_ext = 'php', \phpbb\cache\driver\driver_interface $cache = null)
	{
		parent::__construct($namespace, $root_path . $path, $php_ext, $cache);

		$this->phpbb_root_path = $root_path;
		$this->is_in_admin = (bool) ((defined('ADMIN_START') && ADMIN_START) || (defined('IN_ADMIN') && IN_ADMIN));
	}

	/**
	* Resolves a phpBB class name to a relative path which can be included.
	*
	* @param string       $class The class name to resolve, must be in the
	*                            namespace the loader was constructed with.
	*                            Has to begin with \
	* @return string|bool        A relative path to the file containing the
	*                            class or false if looking it up failed.
	*/
	public function resolve_path($class)
	{
		$path = parent::resolve_path($class);

		// If path not found, or file does not exist
		if (!$path || !file_exists($path))
		{
			// Make sure that it is an extension
			$class_ary = explode('\\', $class, 4);
			// Only indicate a potentially invalid extension if it has the right class structure and
			// the file is not an optional yet checked for existence (as ext.php)
			if (sizeof($class_ary) > 3 && !in_array($class_ary[3], ['ext', 'di\\extension']))
			{
				// Get the extension name from the class name
				$ext_name = $class_ary[1] . '/' . $class_ary[2];
				// Mark the extension as invalid
				$this->mark_invalid($ext_name);
			}
		}

		return $path;
	}

	/**
	* Marks an extension as invalid (incomplete) in the DB.
	*
	* @param string $ext_name The name of the extension to invalidate.
	*/
	protected function mark_invalid($ext_name)
	{
		// We need the container as global because it may not be initialized yet, and in such case,
		// the cache creation would fail, as it uses the global container
		global $phpbb_container;
		// If the container has not been defined yet
		if (!$phpbb_container)
		{
			try
			{
				// Create a core container, without extensions and without saving to the cache, to get the needed core services
				$phpbb_config_php_file = new \phpbb\config_php_file($this->phpbb_root_path, $this->php_ext);
				extract($phpbb_config_php_file->get_all());
				$phpbb_container_builder = new \phpbb\di\container_builder($this->phpbb_root_path, $this->php_ext);
				$phpbb_container = $phpbb_container_builder
										->with_config($phpbb_config_php_file)
										->without_extensions()
										->without_cache()
										->get_container();
			}
			catch (Exception $e)
			{
				return;
			}
		}
		// Get all the required services and parameters from the container
		if (!$this->cache)
		{
			$this->cache = $phpbb_container->get('cache.driver');
		}
		if (!$this->db)
		{
			$this->db = $phpbb_container->get('dbal.conn');
		}
		if (!$this->extension_table)
		{
			$this->extension_table = $phpbb_container->getParameter('tables.ext');
		}

		// Make sure that we have identified an enabled extension, and not working on a false positive
		$sql = 'SELECT COUNT(ext_name) as row_count
			FROM ' . $this->extension_table . "
			WHERE ext_name = '" . $this->db->sql_escape($ext_name) . "'
			AND ext_active = " . true;
		$result = $this->db->sql_query($sql);
		$count = $this->db->sql_fetchfield('row_count');
		$this->db->sql_freeresult($result);

		if ($count)
		{
			// Mark the extension as "invalid": disabled && state = true
			$sql = 'UPDATE ' . $this->extension_table . '
				SET ' . $this->db->sql_build_array('UPDATE', ['ext_active' => false, 'ext_state' => serialize(true)]) . "
				WHERE ext_name = '" . $this->db->sql_escape($ext_name) . "'";
			$this->db->sql_query($sql);

			// If we have the cache, purge it
			if ($this->cache)
			{
				$this->cache->purge();
			}

			// TODO: Log action
			// TODO: Notify admin
			// TODO: Fail nicely
			// Cannot call trigger error, as the user might not have been initialized yet; same with localized errors
			// There should be a different error for ACP and regular forum
//			trigger_error(($is_in_admin) ? sprintf('Extension "%s" marked as invalid.', $ext_name) : 'Internal error. Reload the page (some issues may still exist)');
		}
	}
}
