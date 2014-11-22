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

namespace phpbb\db\migration;

/**
* The schema generator generates the schema based on the existing migrations
*/
class schema_generator
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\db\tools_array */
	protected $db_tools;

	/** @var array */
	protected $class_names;

	/** @var string */
	protected $table_prefix;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var array */
	protected $tables;

	/** @var array */
	protected $dependencies = array();

	/**
	* Constructor
	*/
	public function __construct(array $class_names, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\db\tools_array $db_tools, $phpbb_root_path, $php_ext, $table_prefix)
	{
		$this->config = $config;
		$this->db = $db;
		$this->db_tools = $db_tools;
		$this->class_names = $class_names;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->table_prefix = $table_prefix;
	}

	/**
	* Loads all migrations and their application state from the database.
	*
	* @return array
	*/
	public function get_schema()
	{
		if (!empty($this->tables))
		{
			return $this->tables;
		}

		$migrations = $this->class_names;

		$tree = array();
		$check_dependencies = true;
		while (!empty($migrations))
		{
			foreach ($migrations as $migration_class)
			{
				/** @var $migration_class \phpbb\db\migration\migration */
				$open_dependencies = array_diff($migration_class::depends_on(), $tree);

				if (empty($open_dependencies))
				{
					/** @var $migration \phpbb\db\migration\migration */
					$migration = new $migration_class($this->config, $this->db, $this->db_tools, $this->phpbb_root_path, $this->php_ext, $this->table_prefix);
					$tree[] = $migration_class;
					$migration_key = array_search($migration_class, $migrations);

					$this->db_tools->perform_schema_changes($migration->update_schema());
					unset($migrations[$migration_key]);
				}
				else if ($check_dependencies)
				{
					$this->dependencies = array_merge($this->dependencies, $open_dependencies);
				}
			}

			// Only run this check after the first run
			if ($check_dependencies)
			{
				$this->check_dependencies();
				$check_dependencies = false;
			}
		}

		$this->tables = $this->db_tools->get_structure();

		ksort($this->tables);
		return $this->tables;
	}

	/**
	* Check if one of the migrations files' dependencies can't be resolved
	* by the supplied list of migrations
	*
	* @throws \UnexpectedValueException If a dependency can't be resolved
	*/
	protected function check_dependencies()
	{
		// Strip duplicate values from array
		$this->dependencies = array_unique($this->dependencies);

		foreach ($this->dependencies as $dependency)
		{
			if (!in_array($dependency, $this->class_names))
			{
				throw new \UnexpectedValueException("Unable to resolve the dependency '$dependency'");
			}
		}
	}
}
