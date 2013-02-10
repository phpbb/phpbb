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

	/**
	* State of all migrations
	*
	* (SELECT * FROM migrations table)
	*
	* @var array
	*/
	protected $migration_state = array();

	/**
	* Array of all migrations available to be run
	*
	* @var array
	*/
	protected $migrations = array();

	/**
	* 'name' and 'class' of the last migration run
	*
	* @var array
	*/
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
		$this->migration_state = array();

		$sql = "SELECT *
			FROM " . $this->migrations_table;
		$result = $this->db->sql_query($sql);

		while ($migration = $this->db->sql_fetchrow($result))
		{
			$this->migration_state[$migration['migration_name']] = $migration;

			$this->migration_state[$migration['migration_name']]['migration_depends_on'] = unserialize($migration['migration_depends_on']);
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
	* This function adds all migrations in a specified directory to the migrations table
	*
	* THIS SHOULD NOT GENERALLY BE USED! THIS IS FOR THE PHPBB INSTALLER.
	* THIS WILL THROW ERRORS IF MIGRATIONS ALREADY EXIST IN THE TABLE, DO NOT CALL MORE THAN ONCE!
	*
	* @param string $path Path to migration data files
	* @param bool $recursive Set to true to also load data files from subdirectories
	* @return null
	*/
	public function populate_migrations_from_directory($path, $recursive = true)
	{
		$existing_migrations = $this->migrations;

		$this->migrations = array();
		$this->load_migrations($path, true, $recursive);

		foreach ($this->migrations as $name)
		{
			if ($this->migration_state($name) === false)
			{
				$state = array(
					'migration_depends_on'	=> $name::depends_on(),
					'migration_schema_done' => true,
					'migration_data_done'	=> true,
					'migration_data_state'	=> '',
					'migration_start_time'	=> time(),
					'migration_end_time'	=> time(),
				);
				$this->insert_migration($name, $state);
			}
		}

		$this->migrations = $existing_migrations;
	}

	/**
	* Load migration data files from a directory
	*
	* Migration data files loaded with this function MUST contain
	* 	ONLY ONE class in them (or an exception will be thrown).
	*
	* @param string $path Path to migration data files
	* @param bool $check_fulfillable If TRUE (default), we will check
	* 	if all of the migrations are fulfillable after loading them.
	* 	If FALSE, we will not check. You SHOULD check at least once
	* 	to prevent errors (if including multiple directories, check
	* 	with the last call to prevent throwing errors unnecessarily).
	* @param bool $recursive Set to true to also load data files from subdirectories
	* @return array Array of migration names
	*/
	public function load_migrations($path, $check_fulfillable = true, $recursive = true)
	{
		if (!is_dir($path))
		{
			throw new phpbb_db_migration_exception('DIRECTORY INVALID', $path);
		}

		$handle = opendir($path);
		while (($file = readdir($handle)) !== false)
		{
			if ($file == '.' || $file == '..')
			{
				continue;
			}

			// Recursion through subdirectories
			if (is_dir($path . $file) && $recursive)
			{
				$this->load_migrations($path . $file . '/', $check_fulfillable, $recursive);
			}

			if (strpos($file, '_') !== 0 && strrpos($file, '.' . $this->php_ext) === (strlen($file) - strlen($this->php_ext) - 1))
			{
				// We try to find what class existed by comparing the classes declared before and after including the file.
				$declared_classes = get_declared_classes();

				include ($path . $file);

				$added_classes = array_diff(get_declared_classes(), $declared_classes);

				if (
					// If two classes have been added and phpbb_db_migration is one of them, we've only added one real migration
					!(sizeof($added_classes) == 2 && in_array('phpbb_db_migration', $added_classes)) &&
					// Otherwise there should only be one class added
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
				$unfulfillable = $this->unfulfillable($name);
				if ($unfulfillable !== false)
				{
					throw new phpbb_db_migration_exception('MIGRATION_NOT_FULFILLABLE', $name, $unfulfillable);
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
				'migration_depends_on'	=> $migration->depends_on(),
				'migration_schema_done' => false,
				'migration_data_done'	=> false,
				'migration_data_state'	=> '',
				'migration_start_time'	=> 0,
				'migration_end_time'	=> 0,
			);

		foreach ($state['migration_depends_on'] as $depend)
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

		if ($migration->effectively_installed())
		{
			$state = array(
				'migration_depends_on'	=> $migration->depends_on(),
				'migration_schema_done' => true,
				'migration_data_done'	=> true,
				'migration_data_state'	=> '',
				'migration_start_time'	=> 0,
				'migration_end_time'	=> 0,
			);
		}
		else
		{
			if (!isset($this->migration_state[$name]))
			{
				$state['migration_start_time'] = time();
				$this->insert_migration($name, $state);
			}
		}

		if (!$state['migration_schema_done'])
		{
			$this->apply_schema_changes($migration->update_schema());
			$state['migration_schema_done'] = true;
		}
		else if (!$state['migration_data_done'])
		{
			try
			{
				$result = $this->process_data_step($migration->update_data(), $state['migration_data_state']);

				$state['migration_data_state'] = ($result === true) ? '' : $result;
				$state['migration_data_done'] = ($result === true);
				$state['migration_end_time'] = ($result === true) ? time() : 0;
			}
			catch (phpbb_db_migration_exception $e)
			{
				// Revert the schema changes
				$this->revert($name);

				// Rethrow exception
				throw $e;
			}
		}

		$insert = $state;
		$insert['migration_depends_on'] = serialize($state['migration_depends_on']);
		$sql = 'UPDATE ' . $this->migrations_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $insert) . "
			WHERE migration_name = '" . $this->db->sql_escape($name) . "'";
		$this->db->sql_query($sql);

		$this->migration_state[$name] = $state;

		return true;
	}

	/**
	* Runs a single revert step from the last migration installed
	*
	* YOU MUST ADD/SET ALL MIGRATIONS THAT COULD BE DEPENDENT ON THE MIGRATION TO REVERT TO BEFORE CALLING THIS METHOD!
	* The revert step can either be a schema or a (partial) data revert. To
	* check if revert() needs to be called again use the migration_state() method.
	*
	* @param string $migration String migration name to revert (including any that depend on this migration)
	* @return null
	*/
	public function revert($migration)
	{
		if (!isset($this->migration_state[$migration]))
		{
			// Not installed
			return;
		}

		foreach ($this->migration_state as $name => $state)
		{
			if (!empty($state['migration_depends_on']) && in_array($migration, $state['migration_depends_on']))
			{
				$this->revert($name);
			}
		}

		$this->try_revert($migration);
	}

	/**
	* Attempts to revert a step of the given migration or one of its dependencies
	*
	* @param	string	The class name of the migration
	* @return	bool	Whether any update step was successfully run
	*/
	protected function try_revert($name)
	{
		if (!class_exists($name))
		{
			return false;
		}

		$migration = $this->get_migration($name);

		$state = $this->migration_state[$name];

		$this->last_run_migration = array(
			'name'	=> $name,
			'class'	=> $migration,
		);

		if ($state['migration_data_done'])
		{
			if ($state['migration_data_state'] !== 'revert_data')
			{
				$result = $this->process_data_step($migration->update_data(), $state['migration_data_state'], true);

				$state['migration_data_state'] = ($result === true) ? 'revert_data' : $result;
			}
			else
			{
				$result = $this->process_data_step($migration->revert_data(), $state['migration_data_state'], false);

				$state['migration_data_state'] = ($result === true) ? '' : $result;
				$state['migration_data_done'] = ($result === true) ? false : true;
			}

			$insert = $state;
			$insert['migration_depends_on'] = serialize($state['migration_depends_on']);
			$sql = 'UPDATE ' . $this->migrations_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $insert) . "
				WHERE migration_name = '" . $this->db->sql_escape($name) . "'";
			$this->db->sql_query($sql);

			$this->migration_state[$name] = $state;
		}
		else
		{
			$this->apply_schema_changes($migration->revert_schema());

			$sql = 'DELETE FROM ' . $this->migrations_table . "
				WHERE migration_name = '" . $this->db->sql_escape($name) . "'";
			$this->db->sql_query($sql);

			unset($this->migration_state[$name]);
		}

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
	* @param array $steps The steps to run
	* @param bool|string $state Current state of the migration
	* @param bool $revert true to revert a data step
	* @return bool|string migration state. True if completed, serialized array if not finished
	*/
	protected function process_data_step($steps, $state, $revert = false)
	{
		$state = ($state) ? unserialize($state) : false;

		foreach ($steps as $step_identifier => $step)
		{
			$last_result = false;
			if ($state)
			{
				// Continue until we reach the step that matches the last step called
				if ($state['step'] != $step_identifier)
				{
					continue;
				}

				// We send the result from last time to the callable function
				$last_result = $state['result'];

				// Set state to false since we reached the point we were at
				$state = false;
			}

			try
			{
				// Result will be null or true if everything completed correctly
				$result = $this->run_step($step, $last_result, $revert);
				if ($result !== null && $result !== true)
				{
					return serialize(array(
						'result'	=> $result,
						'step'		=> $step_identifier,
					));
				}
			}
			catch (phpbb_db_migration_exception $e)
			{
				// We should try rolling back here
				foreach ($steps as $reverse_step_identifier => $reverse_step)
				{
					// If we've reached the current step we can break because we reversed everything that was run
					if ($reverse_step_identifier == $step_identifier)
					{
						break;
					}

					// Reverse the step that was run
					$result = $this->run_step($reverse_step, false, !$revert);
				}

				// rethrow the exception
				throw $e;
			}
		}

		return true;
	}

	/**
	* Run a single step
	*
	* An exception should be thrown if an error occurs
	*
	* @param mixed $step Data step from migration
	* @param mixed $last_result Result to pass to the callable (only for 'custom' method)
	* @param bool $reverse False to install, True to attempt uninstallation by reversing the call
	* @return null
	*/
	protected function run_step($step, $last_result = false, $reverse = false)
	{
		$callable_and_parameters = $this->get_callable_from_step($step, $last_result, $reverse);

		if ($callable_and_parameters === false)
		{
			return;
		}

		$callable = $callable_and_parameters[0];
		$parameters = $callable_and_parameters[1];

		return call_user_func_array($callable, $parameters);
	}

	/**
	* Get a callable statement from a data step
	*
	* @param array $step Data step from migration
	* @param mixed $last_result Result to pass to the callable (only for 'custom' method)
	* @param bool $reverse False to install, True to attempt uninstallation by reversing the call
	* @return array Array with parameters for call_user_func_array(), 0 is the callable, 1 is parameters
	*/
	protected function get_callable_from_step(array $step, $last_result = false, $reverse = false)
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

				if (!$condition)
				{
					return false;
				}

				$step = $parameters[1];

				return $this->get_callable_from_step($step);
			break;
			case 'custom':
				if (!is_callable($parameters[0]))
				{
					throw new phpbb_db_migration_exception('MIGRATION_INVALID_DATA_CUSTOM_NOT_CALLABLE', $step);
				}

				return array(
					$parameters[0],
					array($last_result),
				);
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

				// Attempt to reverse operations
				if ($reverse)
				{
					array_unshift($parameters, $method);

					return array(
						array($this->tools[$class], 'reverse'),
						$parameters,
					);
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
		$migration_row['migration_depends_on'] = serialize($state['migration_depends_on']);

		$sql = 'INSERT INTO ' . $this->migrations_table . '
			' . $this->db->sql_build_array('INSERT', $migration_row);
		$this->db->sql_query($sql);

		$this->migration_state[$name] = $state;
	}

	/**
	* Checks if a migration's dependencies can even theoretically be satisfied.
	*
	* @param string	$name The class name of the migration
	* @return bool|string False if fulfillable, string of missing migration name if unfulfillable
	*/
	public function unfulfillable($name)
	{
		if (isset($this->migration_state[$name]))
		{
			return $name;
		}

		if (!class_exists($name))
		{
			return true;
		}

		$migration = $this->get_migration($name);
		$depends = $migration->depends_on();

		foreach ($depends as $depend)
		{
			$unfulfillable = $this->unfulfillable($depend);
			if ($unfulfillable !== false)
			{
				return $unfulfillable;
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
				if ($this->unfulfillable($name) !== false)
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
	* Gets a migration state (whether it is installed and to what extent)
	*
	* @param string $migration String migration name to check if it is installed
	* @return bool|array False if the migration has not at all been installed, array
	*/
	public function migration_state($migration)
	{
		if (!isset($this->migration_state[$migration]))
		{
			return false;
		}

		return $this->migration_state[$migration];
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
