<?php
/**
*
* @package db
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
* The migrator is responsible for applying new migrations in the correct order.
*
* @package db
*/
class phpbb_db_migrator
{
	/** @var phpbb_config */
	protected $config;

	/** @var phpbb_db_driver */
	protected $db;

	/** @var phpbb_db_tools */
	protected $db_tools;

	/** @var string */
	protected $table_prefix;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $migrations_table;

	/** @var array State of all migrations (SELECT * FROM migrations table) */
	protected $migration_state;

	/** @var array Array of all migrations available to be run */
	protected $migrations = array();

	/** @var array 'name' and 'class' of the last migration run */
	public $last_run_migration = false;

	/**
	* Constructor of the database migrator
	*/
	public function __construct(phpbb_config $config, phpbb_db_driver $db, phpbb_db_tools $db_tools, $migrations_table, $phpbb_root_path, $php_ext, $table_prefix, $tools)
	{
		$this->config = $config;
		$this->db = $db;
		$this->db_tools = $db_tools;

		$this->migrations_table = $migrations_table;

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->table_prefix = $table_prefix;

		foreach ($tools as $tool)
		{
			$this->tools[$tool->get_name()] = $tool;
		}

		$this->load_migration_state();
	}

	/**
	* Loads all migrations and their application state from the database.
	*
	* @return null
	*/
	public function load_migration_state()
	{
		$sql = "SELECT *
			FROM " . $this->migrations_table;
		$result = $this->db->sql_query($sql);

		$this->migration_state = array();
		while ($migration = $this->db->sql_fetchrow($result))
		{
			$this->migration_state[$migration['migration_name']] = $migration;
		}

		$this->db->sql_freeresult($result);
	}

	/**
	* Sets the list of available migration class names to the given array.
	*
	* @param array $class_names An array of migration class names
	* @return null
	*/
	public function set_migrations($class_names)
	{
		$this->migrations = $class_names;
	}

	/**
	* Load migration data files from a directory
	*
	* This does not loop through sub-directories.
	* Migration data files loaded with this function MUST contain
	* 	ONLY ONE class in them (or an exception will be thrown).
	*
	* @param string $path Path to migration data files
	* @param bool $check_fulfillable If TRUE (default), we will check
	* 	if all of the migrations are fulfillable after loading them.
	* 	If FALSE, we will not check. You SHOULD check at least once
	* 	to prevent errors (if including multiple directories, check
	* 	with the last call to prevent throwing errors unnecessarily).
	* @return array Array of migrations with names
	*/
	public function load_migrations($path, $check_fulfillable = true)
	{
		$handle = opendir($path);
		while (($file = readdir($handle)) !== false)
		{
			if (strpos($file, '_') !== 0 && strrpos($file, '.' . $this->php_ext) === (strlen($file) - strlen($this->php_ext) - 1))
			{
				// We try to find what class existed by comparing the classes declared before and after including the file.
				$declared_classes = get_declared_classes();

				include ($path . $file);

				$added_classes = array_diff(get_declared_classes(), $declared_classes);

				if (
					// The phpbb_db_migrations class may not have been loaded until now, so make sure to ignore it.
					!(sizeof($added_classes) == 2 && in_array('phpbb_db_migration', $added_classes)) &&
					sizeof($added_classes) != 1
				)
				{
					throw new phpbb_db_migration_exception('MIGRATION DATA FILE INVALID', $path . $file);
				}

				$name = array_pop($added_classes);

				if (!in_array($name, $this->migrations))
				{
					$this->migrations[] = $name;
				}
			}
		}

		if ($check_fulfillable)
		{
			foreach ($this->migrations as $name)
			{
				if ($this->unfulfillable($name))
				{
					throw new phpbb_db_migration_exception('MIGRATION NOT FULFILLABLE', $name);
				}
			}
		}

		return $this->migrations;
	}

	/**
	* Runs a single update step from the next migration to be applied.
	*
	* The update step can either be a schema or a (partial) data update. To
	* check if update() needs to be called again use the finished() method.
	*
	* @return null
	*/
	public function update()
	{
		foreach ($this->migrations as $name)
		{
			if (!isset($this->migration_state[$name]) ||
				!$this->migration_state[$name]['migration_schema_done'] ||
				!$this->migration_state[$name]['migration_data_done'])
			{
				if (!$this->try_apply($name))
				{
					continue;
				}
				else
				{
					return;
				}
			}
		}
	}

	/**
	* Attempts to apply a step of the given migration or one of its dependencies
	*
	* @param	string	The class name of the migration
	* @return	bool	Whether any update step was successfully run
	*/
	protected function try_apply($name)
	{
		if (!class_exists($name))
		{
			return false;
		}

		$migration = $this->get_migration($name);

		$state = (isset($this->migration_state[$name])) ?
			$this->migration_state[$name] :
			array(
				'migration_schema_done' => false,
				'migration_data_done' => false,
				'migration_data_state' => '',
				'migration_start_time' => 0,
				'migration_end_time' => 0,
			);

		$depends = $migration->depends_on();

		foreach ($depends as $depend)
		{
			if (!isset($this->migration_state[$depend]) ||
				!$this->migration_state[$depend]['migration_schema_done'] ||
				!$this->migration_state[$depend]['migration_data_done'])
			{
				return $this->try_apply($depend);
			}
		}

		$this->last_run_migration = array(
			'name'	=> $name,
			'class'	=> $migration,
		);

		if (!isset($this->migration_state[$name]))
		{
			$state['migration_start_time'] = time();
			$this->insert_migration($name, $state);
		}

		if (!$state['migration_schema_done'])
		{
			$this->apply_schema_changes($migration->update_schema());
			$state['migration_schema_done'] = true;
		}
		else
		{
			$state = $this->process_data_step($migration);

			$state['migration_data_state'] = $state;
			$state['migration_data_done'] = ($state === true);
			$state['migration_end_time'] = ($state === true) ? time() : 0;
		}

		$sql = 'UPDATE ' . $this->migrations_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $state) . "
			WHERE migration_name = '" . $this->db->sql_escape($name) . "'";
		$this->db->sql_query($sql);

		$this->migration_state[$name] = $state;

		return true;
	}

	/**
	* Apply schema changes from a migration
	*
	* Just calls db_tools->perform_schema_changes
	*
	* @param array $schema_changes from migration
	*/
	protected function apply_schema_changes($schema_changes)
	{
		$this->db_tools->perform_schema_changes($schema_changes);
	}

	/**
	* Process the data step of the migration
	*
	* @param phpbb_db_migration $migration
	* @return mixed migration status or bool true if everything completed successfully
	*/
	protected function process_data_step($migration)
	{
		$steps = $migration->update_data();

		foreach ($steps as $step)
		{
			try
			{
				// Result will be null or true if everything completed correctly
				$result = $this->run_step($step);
				if ($result !== null && $result !== true)
				{
					return $result;
				}
			}
			catch (phpbb_db_migration_exception $e)
			{
				// We should try rolling back here

				echo $e;
				die();
			}
		}

		return true;
	}

	/**
	* Run a single step
	*
	* An exception should be thrown if an error occurs
	*
	* @param mixed $step
	* @return null
	*/
	protected function run_step($step)
	{
		$callable_and_parameters = $this->get_callable_from_step($step);
		$callable = $callable_and_parameters[0];
		$parameters = $callable_and_parameters[1];

		return call_user_func_array($callable, $parameters);
	}

	/**
	* Get a callable statement from a data step
	*
	* @param mixed $step Data step from migration
	* @return array Array with parameters for call_user_func_array(), 0 is the callable, 1 is parameters
	*/
	public function get_callable_from_step($step)
	{
		$type = $step[0];
		$parameters = $step[1];

		$parts = explode('.', $type);

		$class = $parts[0];
		$method = false;

		if (isset($parts[1]))
		{
			$method = $parts[1];
		}

		switch ($class)
		{
			case 'if':
				if (!isset($parameters[0]))
				{
					throw new phpbb_db_migration_exception('MIGRATION_INVALID_DATA_MISSING_CONDITION', $step);
				}

				if (!isset($parameters[1]))
				{
					throw new phpbb_db_migration_exception('MIGRATION_INVALID_DATA_MISSING_STEP', $step);
				}

				$condition = $parameters[0];
				$step = $parameters[1];

				$callable_and_parameters = $this->get_callable_from_step($step);
				$callable = $callable_and_parameters[0];
				$sub_parameters = $callable_and_parameters[1];

				return array(
					function ($condition) use ($callable, $sub_parameters) {
						return call_user_func_array($callable, $sub_parameters);
					},
					array($condition)
				);
			break;
			case 'custom':
				if (!is_callable($parameters[0]))
				{
					throw new phpbb_db_migration_exception('MIGRATION_INVALID_DATA_CUSTOM_NOT_CALLABLE', $step);
				}

				return array($parameters[0], array());
			break;

			default:
				if (!$method)
				{
					throw new phpbb_db_migration_exception('MIGRATION_INVALID_DATA_UNKNOWN_TYPE', $step);
				}

				if (!isset($this->tools[$class]))
				{
					throw new phpbb_db_migration_exception('MIGRATION_INVALID_DATA_UNDEFINED_TOOL', $step);
				}

				if (!method_exists(get_class($this->tools[$class]), $method))
				{
					throw new phpbb_db_migration_exception('MIGRATION_INVALID_DATA_UNDEFINED_METHOD', $step);
				}

				return array(
					array($this->tools[$class], $method),
					$parameters,
				);
			break;
		}
	}

	/**
	* Insert migration row into the database
	*
	* @param string $name Name of the migration
	* @param array $state
	* @return null
	*/
	protected function insert_migration($name, $state)
	{
		$migration_row = $state;
		$migration_row['migration_name'] = $name;

		$sql = 'INSERT INTO ' . $this->migrations_table . '
			' . $this->db->sql_build_array('INSERT', $migration_row);
		$this->db->sql_query($sql);

		$this->migration_state[$name] = $state;
	}

	/**
	* Checks if a migration's dependencies can even theoretically be satisfied.
	*
	* @param string	$name The class name of the migration
	* @return bool Whether the migration cannot be fulfilled
	*/
	public function unfulfillable($name)
	{
		if (isset($this->migration_state[$name]))
		{
			return false;
		}

		if (!class_exists($name))
		{
			return true;
		}

		$migration = $this->get_migration($name);
		$depends = $migration->depends_on();

		foreach ($depends as $depend)
		{
			if ($this->unfulfillable($depend))
			{
				return true;
			}
		}

		return false;
	}

	/**
	* Checks whether all available, fulfillable migrations have been applied.
	*
	* @return bool Whether the migrations have been applied
	*/
	public function finished()
	{
		foreach ($this->migrations as $name)
		{
			if (!isset($this->migration_state[$name]))
			{
				// skip unfulfillable migrations, but fulfillables mean we
				// are not finished yet
				if ($this->unfulfillable($name))
				{
					continue;
				}
				return false;
			}

			$migration = $this->migration_state[$name];

			if (!$migration['migration_schema_done'] || !$migration['migration_data_done'])
			{
				return false;
			}
		}

		return true;
	}

	/**
	* Helper to get a migration
	*
	* @param string $name Name of the migration
	* @return phpbb_db_migration
	*/
	protected function get_migration($name)
	{
		return new $name($this->config, $this->db, $this->db_tools, $this->phpbb_root_path, $this->php_ext, $this->table_prefix);
	}
}
