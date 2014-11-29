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

namespace phpbb\db;

/**
* The migrator is responsible for applying new migrations in the correct order.
*/
class migrator
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\db\tools_interface */
	protected $db_tools;

	/** @var \phpbb\db\migration\helper */
	protected $helper;

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
	* Array of migrations that have been determined to be fulfillable
	*
	* @var array
	*/
	protected $fulfillable_migrations = array();

	/**
	* 'name,' 'class,' and 'state' of the last migration run
	*
	* 'effectively_installed' set and set to true if the migration was effectively_installed
	*
	* @var array
	*/
	public $last_run_migration = false;

	/**
	 * The output handler. A null handler is configured by default.
	 *
	 * @var migrator_output_handler
	 */
	public $output_handler;

	/**
	* Constructor of the database migrator
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\db\tools_interface $db_tools, $migrations_table, $phpbb_root_path, $php_ext, $table_prefix, $tools, \phpbb\db\migration\helper $helper)
	{
		$this->config = $config;
		$this->db = $db;
		$this->db_tools = $db_tools;
		$this->helper = $helper;

		$this->migrations_table = $migrations_table;

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->table_prefix = $table_prefix;

		$this->output_handler = new null_migrator_output_handler();

		foreach ($tools as $tool)
		{
			$this->tools[$tool->get_name()] = $tool;
		}

		$this->tools['dbtools'] = $this->db_tools;

		$this->load_migration_state();
	}

	/**
	 * Set the output handler.
	 *
	 * @param migrator_output_handler $handler The output handler
	 */
	public function set_output_handler(migrator_output_handler_interface $handler)
	{
		$this->output_handler = $handler;
	}

	/**
	* Loads all migrations and their application state from the database.
	*
	* @return null
	*/
	public function load_migration_state()
	{
		$this->migration_state = array();

		// prevent errors in case the table does not exist yet
		$this->db->sql_return_on_error(true);

		$sql = "SELECT *
			FROM " . $this->migrations_table;
		$result = $this->db->sql_query($sql);

		if (!$this->db->get_sql_error_triggered())
		{
			while ($migration = $this->db->sql_fetchrow($result))
			{
				$this->migration_state[$migration['migration_name']] = $migration;

				$this->migration_state[$migration['migration_name']]['migration_depends_on'] = unserialize($migration['migration_depends_on']);
			}
		}

		$this->db->sql_freeresult($result);

		$this->db->sql_return_on_error(false);
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
			else
			{
				$this->output_handler->write(array('MIGRATION_EFFECTIVELY_INSTALLED', $name), migrator_output_handler_interface::VERBOSITY_DEBUG);
			}
		}
	}

	/**
	* Attempts to apply a step of the given migration or one of its dependencies
	*
	* @param	string	$name The class name of the migration
	* @return	bool	Whether any update step was successfully run
	* @throws \phpbb\db\migration\exception
	*/
	protected function try_apply($name)
	{
		if (!class_exists($name))
		{
			$this->output_handler->write(array('MIGRATION_NOT_VALID', $name), migrator_output_handler_interface::VERBOSITY_DEBUG);
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

		if (!empty($state['migration_depends_on']))
		{
			$this->output_handler->write(array('MIGRATION_APPLY_DEPENDENCIES', $name), migrator_output_handler_interface::VERBOSITY_DEBUG);
		}

		foreach ($state['migration_depends_on'] as $depend)
		{
			if ($this->unfulfillable($depend) !== false)
			{
				throw new \phpbb\db\migration\exception('MIGRATION_NOT_FULFILLABLE', $name, $depend);
			}

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
			'state'	=> $state,
			'task'	=> '',
		);

		if (!isset($this->migration_state[$name]))
		{
			if ($state['migration_start_time'] == 0 && $migration->effectively_installed())
			{
				$state = array(
					'migration_depends_on'	=> $migration->depends_on(),
					'migration_schema_done' => true,
					'migration_data_done'	=> true,
					'migration_data_state'	=> '',
					'migration_start_time'	=> 0,
					'migration_end_time'	=> 0,
				);

				$this->last_run_migration['effectively_installed'] = true;

				$this->output_handler->write(array('MIGRATION_EFFECTIVELY_INSTALLED', $name), migrator_output_handler_interface::VERBOSITY_VERBOSE);
			}
			else
			{
				$state['migration_start_time'] = time();
			}
		}

		$this->set_migration_state($name, $state);

		if (!$state['migration_schema_done'])
		{
			$this->output_handler->write(array('MIGRATION_SCHEMA_RUNNING', $name), migrator_output_handler_interface::VERBOSITY_VERBOSE);

			$this->last_run_migration['task'] = 'process_schema_step';
			$elapsed_time = microtime(true);
			$steps = $this->helper->get_schema_steps($migration->update_schema());
			$result = $this->process_data_step($steps, $state['migration_data_state']);
			$elapsed_time = microtime(true) - $elapsed_time;

			$state['migration_data_state'] = ($result === true) ? '' : $result;
			$state['migration_schema_done'] = ($result === true);

			$this->output_handler->write(array('MIGRATION_SCHEMA_DONE', $name, $elapsed_time), migrator_output_handler_interface::VERBOSITY_NORMAL);
		}
		else if (!$state['migration_data_done'])
		{
			try
			{
				$this->output_handler->write(array('MIGRATION_DATA_RUNNING', $name), migrator_output_handler_interface::VERBOSITY_VERBOSE);

				$this->last_run_migration['task'] = 'process_data_step';

				$elapsed_time = microtime(true);
				$result = $this->process_data_step($migration->update_data(), $state['migration_data_state']);
				$elapsed_time = microtime(true) - $elapsed_time;

				$state['migration_data_state'] = ($result === true) ? '' : $result;
				$state['migration_data_done'] = ($result === true);
				$state['migration_end_time'] = ($result === true) ? time() : 0;

				if ($state['migration_schema_done'])
				{
					$this->output_handler->write(array('MIGRATION_DATA_DONE', $name, $elapsed_time), migrator_output_handler_interface::VERBOSITY_NORMAL);
				}
				else
				{
					$this->output_handler->write(array('MIGRATION_DATA_IN_PROGRESS', $name, $elapsed_time), migrator_output_handler_interface::VERBOSITY_VERY_VERBOSE);
				}
			}
			catch (\phpbb\db\migration\exception $e)
			{
				// Revert the schema changes
				$this->revert($name);

				// Rethrow exception
				throw $e;
			}
		}

		$this->set_migration_state($name, $state);

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
	* @param	string	$name The class name of the migration
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
			'task'	=> '',
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
				$result = $this->process_data_step($migration->revert_data(), '', false);

				$state['migration_data_state'] = ($result === true) ? '' : $result;
				$state['migration_data_done'] = ($result === true) ? false : true;
			}

			$this->set_migration_state($name, $state);
		}
		else if ($state['migration_schema_done'])
		{
			$steps = $this->helper->get_schema_steps($migration->revert_schema());
			$result = $this->process_data_step($steps, $state['migration_data_state']);

			$state['migration_data_state'] = ($result === true) ? '' : $result;
			$state['migration_schema_done'] = ($result === true) ? false : true;

			if (!$state['migration_schema_done'])
			{
				$sql = 'DELETE FROM ' . $this->migrations_table . "
					WHERE migration_name = '" . $this->db->sql_escape($name) . "'";
				$this->db->sql_query($sql);

				unset($this->migration_state[$name]);
			}
		}

		return true;
	}

	/**
	* Process the data step of the migration
	*
	* @param array $steps The steps to run
	* @param bool|string $state Current state of the migration
	* @param bool $revert true to revert a data step
	* @return bool|string migration state. True if completed, serialized array if not finished
	* @throws \phpbb\db\migration\exception
	*/
	protected function process_data_step($steps, $state, $revert = false)
	{
		$state = ($state) ? unserialize($state) : false;

		// reverse order of steps if reverting
		if ($revert === true)
		{
			$steps = array_reverse($steps);
		}

		foreach ($steps as $step_identifier => $step)
		{
			$last_result = 0;
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
			catch (\phpbb\db\migration\exception $e)
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
	protected function run_step($step, $last_result = 0, $reverse = false)
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
	* @throws \phpbb\db\migration\exception
	*/
	protected function get_callable_from_step(array $step, $last_result = 0, $reverse = false)
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
					throw new \phpbb\db\migration\exception('MIGRATION_INVALID_DATA_MISSING_CONDITION', $step);
				}

				if (!isset($parameters[1]))
				{
					throw new \phpbb\db\migration\exception('MIGRATION_INVALID_DATA_MISSING_STEP', $step);
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
					throw new \phpbb\db\migration\exception('MIGRATION_INVALID_DATA_CUSTOM_NOT_CALLABLE', $step);
				}

				if ($reverse)
				{
					return false;
				}
				else
				{
					return array(
						$parameters[0],
						array($last_result),
					);
				}
			break;

			default:
				if (!$method)
				{
					throw new \phpbb\db\migration\exception('MIGRATION_INVALID_DATA_UNKNOWN_TYPE', $step);
				}

				if (!isset($this->tools[$class]))
				{
					throw new \phpbb\db\migration\exception('MIGRATION_INVALID_DATA_UNDEFINED_TOOL', $step);
				}

				if (!method_exists(get_class($this->tools[$class]), $method))
				{
					throw new \phpbb\db\migration\exception('MIGRATION_INVALID_DATA_UNDEFINED_METHOD', $step);
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
	* Insert/Update migration row into the database
	*
	* @param string $name Name of the migration
	* @param array $state
	* @return null
	*/
	protected function set_migration_state($name, $state)
	{
		$migration_row = $state;
		$migration_row['migration_depends_on'] = serialize($state['migration_depends_on']);

		if (isset($this->migration_state[$name]))
		{
			$sql = 'UPDATE ' . $this->migrations_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $migration_row) . "
				WHERE migration_name = '" . $this->db->sql_escape($name) . "'";
			$this->db->sql_query($sql);
		}
		else
		{
			$migration_row['migration_name'] = $name;
			$sql = 'INSERT INTO ' . $this->migrations_table . '
				' . $this->db->sql_build_array('INSERT', $migration_row);
			$this->db->sql_query($sql);
		}

		$this->migration_state[$name] = $state;

		$this->last_run_migration['state'] = $state;
	}

	/**
	* Checks if a migration's dependencies can even theoretically be satisfied.
	*
	* @param string	$name The class name of the migration
	* @return bool|string False if fulfillable, string of missing migration name if unfulfillable
	*/
	public function unfulfillable($name)
	{
		if (isset($this->migration_state[$name]) || isset($this->fulfillable_migrations[$name]))
		{
			return false;
		}

		if (!class_exists($name))
		{
			return $name;
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
		$this->fulfillable_migrations[$name] = true;

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
	* @return \phpbb\db\migration\migration
	*/
	protected function get_migration($name)
	{
		return new $name($this->config, $this->db, $this->db_tools, $this->phpbb_root_path, $this->php_ext, $this->table_prefix);
	}

	/**
	* This function adds all migrations sent to it to the migrations table
	*
	* THIS SHOULD NOT GENERALLY BE USED! THIS IS FOR THE PHPBB INSTALLER.
	* THIS WILL THROW ERRORS IF MIGRATIONS ALREADY EXIST IN THE TABLE, DO NOT CALL MORE THAN ONCE!
	*
	* @param array $migrations Array of migrations (names) to add to the migrations table
	* @return null
	*/
	public function populate_migrations($migrations)
	{
		foreach ($migrations as $name)
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
				$this->set_migration_state($name, $state);
			}
		}
	}

	/**
	* Creates the migrations table if it does not exist.
	* @return null
	*/
	public function create_migrations_table()
	{
		// Make sure migrations have been installed.
		if (!$this->db_tools->sql_table_exists($this->table_prefix . 'migrations'))
		{
			$this->db_tools->sql_create_table($this->table_prefix . 'migrations', array(
				'COLUMNS'		=> array(
					'migration_name'			=> array('VCHAR', ''),
					'migration_depends_on'		=> array('TEXT', ''),
					'migration_schema_done'		=> array('BOOL', 0),
					'migration_data_done'		=> array('BOOL', 0),
					'migration_data_state'		=> array('TEXT', ''),
					'migration_start_time'		=> array('TIMESTAMP', 0),
					'migration_end_time'		=> array('TIMESTAMP', 0),
				),
				'PRIMARY_KEY'	=> 'migration_name',
			));
		}
	}
}
