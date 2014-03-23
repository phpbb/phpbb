<?php
/**
*
* @package db
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace phpbb\db\migration;

/**
* The schema generator generates the schema based on the existing migrations
*
* @package db
*/
class schema_generator
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\db\tools */
	protected $db_tools;

	/** @var \phpbb\extension\finder */
	protected $finder;

	/** @var string */
	protected $table_prefix;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var array */
	protected $tables;

	/**
	* Constructor
	*/
	public function __construct(\phpbb\extension\finder $finder, \phpbb\config\config $config, \phpbb\db\driver\driver $db, \phpbb\db\tools $db_tools, $phpbb_root_path, $php_ext, $table_prefix)
	{
		$this->config = $config;
		$this->db = $db;
		$this->db_tools = $db_tools;
		$this->finder = $finder;
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

		$migrations = $this->finder->get_classes();

		$tree = array();
		while (!empty($migrations))
		{
			foreach ($migrations as $migration_class)
			{
				$open_dependencies = array_diff($migration_class::depends_on(), $tree);
				if (empty($open_dependencies))
				{
					$migration = new $migration_class($this->config, $this->db, $this->db_tools, $this->phpbb_root_path, $this->php_ext, $this->table_prefix);
					$tree[] = $migration_class;
					$migration_key = array_search($migration_class, $migrations);

					foreach ($migration->update_schema() as $change_type => $data)
					{
						if ($change_type === 'add_tables')
						{
							foreach ($data as $table => $table_data)
							{
								$this->tables[$table] = $table_data;
							}
						}
						else if ($change_type === 'drop_tables')
						{
							foreach ($data as $table)
							{
								unset($this->tables[$table]);
							}
						}
						else if ($change_type === 'add_columns')
						{
							foreach ($data as $table => $add_columns)
							{
								foreach ($add_columns as $column => $column_data)
								{
									$this->tables[$table]['COLUMNS'][$column] = $column_data;
								}
							}
						}
						else if ($change_type === 'change_columns')
						{
							foreach ($data as $table => $change_columns)
							{
								foreach ($change_columns as $column => $column_data)
								{
									$this->tables[$table]['COLUMNS'][$column] = $column_data;
								}
							}
						}
						else if ($change_type === 'drop_columns')
						{
							foreach ($data as $table => $drop_columns)
							{
								if (is_array($drop_columns))
								{
									foreach ($drop_columns as $column)
									{
										unset($this->tables[$table]['COLUMNS'][$column]);
									}
								}
								else
								{
									unset($this->tables[$table]['COLUMNS'][$drop_columns]);
								}
							}
						}
						else if ($change_type === 'add_unique_index')
						{
							foreach ($data as $table => $add_index)
							{
								foreach ($add_index as $key => $index_data)
								{
									$this->tables[$table]['KEYS'][$key] = array('UNIQUE', $index_data);
								}
							}
						}
						else if ($change_type === 'add_index')
						{
							foreach ($data as $table => $add_index)
							{
								foreach ($add_index as $key => $index_data)
								{
									$this->tables[$table]['KEYS'][$key] = array('INDEX', $index_data);
								}
							}
						}
						else if ($change_type === 'drop_keys')
						{
							foreach ($data as $table => $drop_keys)
							{
								foreach ($drop_keys as $key)
								{
									unset($this->tables[$table]['KEYS'][$key]);
								}
							}
						}
						else
						{
							var_dump($change_type);
						}
					}
					unset($migrations[$migration_key]);
				}
			}
		}

		ksort($this->tables);
		return $this->tables;
	}
}
