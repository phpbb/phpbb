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

use Closure;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\db\migrator;
use phpbb\db\tools\tools_interface;
use UnexpectedValueException;

/**
* The schema generator generates the schema based on the existing migrations
*/
class schema_generator
{
	/** @var config */
	protected $config;

	/** @var driver_interface */
	protected $db;

	/** @var tools_interface */
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
	protected $table_names;

	/**
	 * Constructor
	 * @param array $class_names
	 * @param config $config
	 * @param driver_interface $db
	 * @param tools_interface $db_tools
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 * @param string $table_prefix
	 * @param array $tables
	 */
	public function __construct(
		array $class_names,
		config $config,
		driver_interface $db,
		tools_interface $db_tools,
		string $phpbb_root_path,
		string $php_ext,
		string $table_prefix,
		array $tables)
	{
		$this->config = $config;
		$this->db = $db;
		$this->db_tools = $db_tools;
		$this->class_names = $class_names;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->table_prefix = $table_prefix;
		$this->table_names = $tables;
	}

	/**
	* Loads all migrations and their application state from the database.
	*
	* @return array An array describing the database schema.
	*
	* @throws UnexpectedValueException	If a migration tries to use an undefined schema change.
	* @throws UnexpectedValueException	If a dependency can't be resolved or there are circular
	* 									dependencies between migrations.
	*/
	public function get_schema() : array
	{
		if (!empty($this->tables))
		{
			return $this->tables;
		}

		$dependency_counts = [];
		$dependencies = [];
		$applicable_migrations = [];
		$migration_count = 0;
		foreach ($this->class_names as $class_name)
		{
			if (!migrator::is_migration($class_name))
			{
				continue;
			}

			$migration_count++;
			$migration_dependencies = $class_name::depends_on();
			if (empty($migration_dependencies))
			{
				$applicable_migrations[] = $class_name;
				continue;
			}

			$dependency_counts[$class_name] = count($migration_dependencies);
			foreach ($migration_dependencies as $migration_dependency)
			{
				$dependencies[$migration_dependency][] = $class_name;
			}
		}

		$applied_migrations = 0;
		while (!empty($applicable_migrations))
		{
			$migration = array_pop($applicable_migrations);
			$this->apply_migration_to_schema($migration);
			++$applied_migrations;

			if (!array_key_exists($migration, $dependencies))
			{
				continue;
			}

			$dependents = $dependencies[$migration];
			foreach ($dependents as $dependent)
			{
				$dependency_counts[$dependent]--;
				if ($dependency_counts[$dependent] === 0)
				{
					$applicable_migrations[] = $dependent;
				}
			}
		}

		if ($migration_count !== $applied_migrations)
		{
			throw new UnexpectedValueException(
				"Migrations either have circular dependencies or unsatisfiable dependencies."
			);
		}

		ksort($this->tables);
		return $this->tables;
	}

	/**
	 * Apply the changes defined in the migration to the database schema.
	 *
	 * @param string $migration_class The name of the migration class.
	 *
	 * @throws UnexpectedValueException If a migration tries to use an undefined schema change.
	 */
	private function apply_migration_to_schema(string $migration_class)
	{
		$migration = new $migration_class(
			$this->config,
			$this->db,
			$this->db_tools,
			$this->phpbb_root_path,
			$this->php_ext,
			$this->table_prefix,
			$this->table_names
		);

		$column_map = [
			'add_tables'		=> null,
			'drop_tables'		=> null,
			'add_columns'		=> 'COLUMNS',
			'drop_columns'		=> 'COLUMNS',
			'change_columns'	=> 'COLUMNS',
			'add_index'			=> 'KEYS',
			'add_primary_keys'	=> null,
			'add_unique_index'	=> 'KEYS',
			'drop_keys'			=> 'KEYS',
			'rename_index'		=> 'KEYS',
			'drop_primary_keys'	=> null,
		];

		$schema_changes = $migration->update_schema();
		foreach ($schema_changes as $change_type => $changes)
		{
			if (!array_key_exists($change_type, $column_map))
			{
				throw new UnexpectedValueException("$migration_class contains undefined schema changes: $change_type.");
			}

			$split_position = strpos($change_type, '_');
			$schema_change_type = substr($change_type, 0, $split_position);
			$schema_type = substr($change_type, $split_position + 1);

			$action = null;
			switch ($schema_change_type)
			{
				case 'add':
				case 'change':
				case 'rename':
					$action = function(&$value, $changes, $value_transform = null) {
						self::set_all($value, $changes, $value_transform);
					};
				break;

				case 'drop':
					$action = function(&$value, $changes, $value_transform = null) {
						self::unset_all($value, $changes, $value_transform);
					};
				break;

				default:
					throw new UnexpectedValueException("$migration_class contains undefined schema changes: $change_type.");
			}

			switch ($schema_type)
			{
				case 'tables':
					$action($this->tables, $changes);
				break;

				default:
					$this->for_each_table(
						$changes,
						$action,
						$column_map[$change_type],
						self::get_value_transform($schema_change_type, $schema_type)
					);
			}
		}
	}

	/**
	 * Apply `$callback` to each table specified in `$data`.
	 *
	 * @param array			$data				Array describing the schema changes.
	 * @param callable		$callback			Callback function to be applied.
	 * @param string|null	$column				Column of the `$this->tables` array for the table on which
	 * 											the change will be made or null.
	 * @param callable|null	$value_transform	Value transformation callback function or null.
	 */
	private function for_each_table(array $data, callable $callback, $column = null, $value_transform = null)
	{
		foreach ($data as $table => $values)
		{
			$target = &$this->tables[$table];
			if ($column !== null)
			{
				$target = &$target[$column];
			}

			$callback($target, $values, $value_transform);
		}
	}

	/**
	 * Set an array of key-value pairs in the schema.
	 *
	 * @param mixed			$schema				Reference to the schema entry.
	 * @param mixed			$data				Array of values to be set.
	 * @param callable|null	$value_transform	Callback to transform the value being set.
	 */
	private static function set_all(&$schema, $data, callable|null $value_transform = null)
	{
		$data = (!is_array($data)) ? [$data] : $data;
		foreach ($data as $key => $change)
		{
			if (is_callable($value_transform))
			{
				$value_transform($schema, $key, $change);
			}
			else
			{
				$schema[$key] = $change;
			}
		}
	}

	/**
	 * Remove an array of values from the schema
	 *
	 * @param mixed $schema						Reference to the schema entry.
	 * @param mixed $data						Array of values to be removed.
	 * @param callable|null	$value_transform	Callback to transform the value being set.
	 */
	private static function unset_all(&$schema, $data, callable|null $value_transform = null)
	{
		$data = (!is_array($data)) ? [$data] : $data;
		foreach ($data as $key => $change)
		{
			if (is_callable($value_transform))
			{
				$value_transform($schema, $key, $change);
			}
			else
			{
				unset($schema[$change]);
			}
		}
	}

	/**
	 * Logic for adding a new column to a table.
	 *
	 * @param array		$value	The table column entry.
	 * @param string	$key	The column name to add.
	 * @param array		$change	The column data.
	 */
	private static function handle_add_column(array &$value, string $key, array $change)
	{
		if (!array_key_exists('after', $change))
		{
			$value[$key] = $change;
			return;
		}

		$after = $change['after'];
		unset($change['after']);

		if ($after === null)
		{
			$value[$key] = array_values($change);
			return;
		}

		$offset = array_search($after, array_keys($value));
		if ($offset === false)
		{
			$value[$key] = array_values($change);
			return;
		}

		$value = array_merge(
			array_slice($value, 0, $offset + 1, true),
			[$key => array_values($change)],
			array_slice($value, $offset)
		);
	}

	/**
	 * Returns the value transform for the change.
	 *
	 * @param string $change_type	The type of the change.
	 * @param string $schema_type	The schema type on which the change is to be performed.
	 *
	 * @return Closure|null The value transformation callback or null if it is not needed.
	 */
	private static function get_value_transform(string $change_type, string $schema_type) : Closure|null
	{
		if ($change_type == 'drop' && $schema_type == 'primary_keys')
		{
				return function(&$value, $key, $change) {
					unset($value['PRIMARY_KEY']);
				};
		}

		if (!in_array($change_type, ['add', 'rename']))
		{
			return null;
		}

		switch ($schema_type)
		{
			case 'index':
				if ($change_type == 'rename')
				{
					return function(&$value, $key, $change) {
						if (isset($value[$key]))
						{
							$data_backup = $value[$key];
							unset($value[$key]);
							$value[$change] = $data_backup;
							unset($data_backup);
						}
						else
						{
							return null;
						}
					};
				}

				return function(&$value, $key, $change) {
					$value[$key] = ['INDEX', $change];
				};

			case 'unique_index':
				return function(&$value, $key, $change) {
					$value[$key] = ['UNIQUE', $change];
				};

			case 'columns':
				return function(&$value, $key, $change) {
					self::handle_add_column($value, $key, $change);
				};

			case 'primary_keys':
				return function(&$value, $key, $change) {
					$value['PRIMARY_KEY'] = $change;
				};
		}

		return null;
	}
}
