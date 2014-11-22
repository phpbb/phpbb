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
 * Database Tools version that works on a local array without touching the database
 * This version MUST NOT BE used in installed boards, but only to generate the schema!
 */
class tools_array implements tools_interface
{
	protected $table_data = array();

	public function get_structure()
	{
		return $this->table_data;
	}

	/**
	 * {@inheritDoc}
	 */
	public function perform_schema_changes($schema_changes)
	{
		if (empty($schema_changes))
		{
			return;
		}

		// Drop tables?
		if (!empty($schema_changes['drop_tables']))
		{
			foreach ($schema_changes['drop_tables'] as $table)
			{
				// only drop table if it exists
				if ($this->sql_table_exists($table))
				{
					$this->sql_table_drop($table);
				}
			}
		}

		// Add tables?
		if (!empty($schema_changes['add_tables']))
		{
			foreach ($schema_changes['add_tables'] as $table => $table_data)
			{
				$this->sql_create_table($table, $table_data);
			}
		}

		// Change columns?
		if (!empty($schema_changes['change_columns']))
		{
			foreach ($schema_changes['change_columns'] as $table => $columns)
			{
				foreach ($columns as $column_name => $column_data)
				{
					// If the column exists we change it, else we add it ;)
					if ($column_exists = $this->sql_column_exists($table, $column_name))
					{
						$this->sql_column_change($table, $column_name, $column_data, true);
					}
					else
					{
						$this->sql_column_add($table, $column_name, $column_data, true);
					}
				}
			}
		}

		// Add columns?
		if (!empty($schema_changes['add_columns']))
		{
			foreach ($schema_changes['add_columns'] as $table => $columns)
			{
				foreach ($columns as $column_name => $column_data)
				{
					// Only add the column if it does not exist yet
					if ($column_exists = $this->sql_column_exists($table, $column_name))
					{
						continue;
						// This is commented out here because it can take tremendous time on updates
//						$result = $this->sql_column_change($table, $column_name, $column_data, true);
					}
					else
					{
						$this->sql_column_add($table, $column_name, $column_data, true);
					}
				}
			}
		}

		// Remove keys?
		if (!empty($schema_changes['drop_keys']))
		{
			foreach ($schema_changes['drop_keys'] as $table => $indexes)
			{
				foreach ($indexes as $index_name)
				{
					if (!$this->sql_index_exists($table, $index_name))
					{
						continue;
					}
					$this->sql_index_drop($table, $index_name);
				}
			}
		}

		// Drop columns?
		if (!empty($schema_changes['drop_columns']))
		{
			foreach ($schema_changes['drop_columns'] as $table => $columns)
			{
				foreach ($columns as $column)
				{
					// Only remove the column if it exists...
					if ($this->sql_column_exists($table, $column))
					{
						$this->sql_column_remove($table, $column, true);
					}
				}
			}
		}

		// Add primary keys?
		if (!empty($schema_changes['add_primary_keys']))
		{
			foreach ($schema_changes['add_primary_keys'] as $table => $columns)
			{
				$this->sql_create_primary_key($table, $columns, true);
			}
		}

		// Add unique indexes?
		if (!empty($schema_changes['add_unique_index']))
		{
			foreach ($schema_changes['add_unique_index'] as $table => $index_array)
			{
				foreach ($index_array as $index_name => $column)
				{
					if ($this->sql_unique_index_exists($table, $index_name))
					{
						continue;
					}

					$this->sql_create_unique_index($table, $index_name, $column);
				}
			}
		}

		// Add indexes?
		if (!empty($schema_changes['add_index']))
		{
			foreach ($schema_changes['add_index'] as $table => $index_array)
			{
				foreach ($index_array as $index_name => $column)
				{
					if ($this->sql_index_exists($table, $index_name))
					{
						continue;
					}

					$this->sql_create_index($table, $index_name, $column);
				}
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_list_tables()
	{
		return array_keys($this->table_data);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_table_exists($table_name)
	{
		return isset($this->table_data[$table_name]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_create_table($table_name, $table_data)
	{
		if (isset($this->table_data[$table_name]))
		{
			throw new \Exception('Table "' . $table_name . '" already exists');
		}

		$this->table_data[$table_name] = array_merge(array(
			'COLUMNS' => array(),
		), $table_data);

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_table_drop($table_name)
	{
		if (!isset($this->table_data[$table_name]))
		{
			throw new \Exception('Table "' . $table_name . '" does not exist');
		}

		unset($this->table_data[$table_name]);

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_list_columns($table_name)
	{
		if (!isset($this->table_data[$table_name]))
		{
			throw new \Exception('Table "' . $table_name . '" does not exist');
		}

		return array_keys($this->table_data[$table_name]['COLUMNS']);
	}

	/**
	 * {@inheritDoc}
	 */
	function sql_column_exists($table_name, $column_name)
	{
		if (!isset($this->table_data[$table_name]))
		{
			throw new \Exception('Table "' . $table_name . '" does not exist');
		}

		return isset($this->table_data[$table_name]['COLUMNS'][$column_name]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_column_add($table_name, $column_name, $column_data, $inline = false)
	{
		if (!isset($this->table_data[$table_name]))
		{
			throw new \Exception('Table "' . $table_name . '" does not exist');
		}
		if (isset($this->table_data[$table_name]['COLUMNS'][$column_name]))
		{
			throw new \Exception('Column "' . $column_name . '" already exists on table "' . $table_name . '"');
		}

		if (!empty($column_data['after']))
		{
			$columns = $this->table_data[$table_name]['COLUMNS'];
			$offset = array_search($column_data['after'], array_keys($columns));
			unset($column_data['after']);
			if ($offset === false)
			{
				$this->table_data[$table_name]['COLUMNS'][$column_name] = array_values($column_data);
			}
			else
			{
				$this->table_data[$table_name]['COLUMNS'] = array_merge(array_slice($columns, 0, $offset + 1, true), array($column_name => array_values($column_data)), array_slice($columns, $offset));
			}
		}
		else
		{
			$this->table_data[$table_name]['COLUMNS'][$column_name] = $column_data;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_column_change($table_name, $column_name, $column_data, $inline = false)
	{
		if (!isset($this->table_data[$table_name]))
		{
			throw new \Exception('Table "' . $table_name . '" does not exist');
		}
		if (!isset($this->table_data[$table_name]['COLUMNS'][$column_name]))
		{
			throw new \Exception('Column "' . $column_name . '" does not exist on table "' . $table_name . '"');
		}

		$this->table_data[$table_name]['COLUMNS'][$column_name] = $column_data;

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_column_remove($table_name, $column_name, $inline = false)
	{
		if (!isset($this->table_data[$table_name]))
		{
			throw new \Exception('Table "' . $table_name . '" does not exist');
		}
		if (!isset($this->table_data[$table_name]['COLUMNS'][$column_name]))
		{
			throw new \Exception('Column "' . $column_name . '" does not exist on table "' . $table_name . '"');
		}

		unset($this->table_data[$table_name]['COLUMNS'][$column_name]);

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_list_index($table_name)
	{
		if (!isset($this->table_data[$table_name]))
		{
			throw new \Exception('Table "' . $table_name . '" does not exist');
		}

		if (!isset($this->table_data[$table_name]['KEYS']))
		{
			return array();
		}

		$indexes = array();
		foreach ($this->table_data[$table_name]['KEYS'] as $index_name => $index_data)
		{
			if ($index_data[0] === 'INDEX')
			{
				$indexes[] = $index_name;
			}
		}

		return $indexes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_index_exists($table_name, $index_name)
	{
		if (!isset($this->table_data[$table_name]))
		{
			throw new \Exception('Table "' . $table_name . '" does not exist');
		}

		return isset($this->table_data[$table_name]['KEYS'][$index_name]) && $this->table_data[$table_name]['KEYS'][$index_name][0] === 'INDEX';
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_create_index($table_name, $index_name, $column)
	{
		if (!isset($this->table_data[$table_name]))
		{
			throw new \Exception('Table "' . $table_name . '" does not exist');
		}
		if (isset($this->table_data[$table_name]['KEYS'][$index_name]))
		{
			throw new \Exception('Index "' . $index_name . '" already exists on table "' . $table_name . '"');
		}

		$this->table_data[$table_name]['KEYS'][$index_name] = array('INDEX', $column);

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_index_drop($table_name, $index_name)
	{
		if (!isset($this->table_data[$table_name]))
		{
			throw new \Exception('Table "' . $table_name . '" does not exist');
		}
		if (!isset($this->table_data[$table_name]['KEYS'][$index_name]))
		{
			throw new \Exception('Index "' . $index_name . '" does not exists on table "' . $table_name . '"');
		}

		unset($this->table_data[$table_name]['KEYS'][$index_name]);

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_unique_index_exists($table_name, $index_name)
	{
		if (!isset($this->table_data[$table_name]))
		{
			throw new \Exception('Table "' . $table_name . '" does not exist');
		}

		return isset($this->table_data[$table_name]['KEYS'][$index_name]) && $this->table_data[$table_name]['KEYS'][$index_name][0] === 'UNIQUE';
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_create_unique_index($table_name, $index_name, $column)
	{
		if (!isset($this->table_data[$table_name]))
		{
			throw new \Exception('Table "' . $table_name . '" does not exist');
		}
		if (isset($this->table_data[$table_name]['KEYS'][$index_name]))
		{
			throw new \Exception('Unique index "' . $index_name . '" already exists on table "' . $table_name . '"');
		}

		$this->table_data[$table_name]['KEYS'][$index_name] = array('UNIQUE', $column);

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_create_primary_key($table_name, $column, $inline = false)
	{
		if (!isset($this->table_data[$table_name]))
		{
			throw new \Exception('Table "' . $table_name . '" does not exist');
		}
		if (isset($this->table_data[$table_name]['PRIMARY_KEY']))
		{
			throw new \Exception('Primary key already exists on table "' . $table_name . '"');
		}

		$this->table_data[$table_name]['PRIMARY_KEY'] = $column;

		return true;
	}
}
