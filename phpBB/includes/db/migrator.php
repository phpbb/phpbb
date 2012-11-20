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
	var $db;
	var $db_tools;
	var $table_prefix;

	var $phpbb_root_path;
	var $php_ext;

	var $migrations_table;
	var $migration_state;

	var $migrations;

	/**
	* Constructor of the database migrator
	*
	* @param dbal			$db			Connected database abstraction instance
	* @param phpbb_db_tools	$db_tools	Instance of db schema manipulation tools
	* @param string			$table_prefix The prefix for all table names
	* @param string			$migrations_table The name of the db table storing
	*									information on applied migrations
	* @param string			$phpbb_root_path
	* @param string			$php_ext
	*/
	function phpbb_db_migrator($db, $db_tools, $table_prefix, $migrations_table, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->db_tools = $db_tools;
		$this->table_prefix = $table_prefix;
		$this->migrations_table = $migrations_table;
		$this->migrations = array();

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->load_migration_state();
	}

	/**
	* Loads all migrations and their application state from the database.
	*
	* @return null
	*/
	function load_migration_state()
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
	function set_migrations($class_names)
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
	function update()
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
	function try_apply($name)
	{
		if (!class_exists($name))
		{
			return false;
		}

		$migration = new $name($this->db, $this->db_tools, $this->table_prefix, $this->phpbb_root_path, $this->php_ext);
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
			$this->process_data_step($migration);
			$state['migration_data_done'] = true;
			$state['migration_end_time'] = time();
		}

		$sql = 'UPDATE ' . $this->migrations_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $state) . "
			WHERE migration_name = '" . $this->db->sql_escape($name) . "'";
		$this->db->sql_query($sql);

		$this->migration_state[$name] = $state;

		return true;
	}

	function process_data_step($migration)
	{
		$continue = false;
		$steps = $migration->update_data();

		foreach ($steps as $step)
		{
			$continue = $this->run_step($step);
			if (!$continue)
			{
				return false;
			}
		}

		return $continue;
	}

	function run_step($step)
	{

	}

	function insert_migration($name, $state)
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
	* @param	string	$name	The class name of the migration
	* @return	bool			Whether the migration cannot be fulfilled
	*/
	function unfulfillable($name)
	{
		if (isset($this->migration_state[$name]))
		{
			return false;
		}

		if (!class_exists($name))
		{
			return true;
		}

		$migration = new $name($this->db, $this->db_tools, $this->table_prefix, $this->phpbb_root_path, $this->php_ext);
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
	function finished()
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

	function apply_schema_changes($schema_changes)
	{
		$this->db_tools->perform_schema_changes($schema_changes);
	}
}
