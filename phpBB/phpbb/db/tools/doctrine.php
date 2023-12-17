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

namespace phpbb\db\tools;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Sequence;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use phpbb\db\doctrine\comparator;
use phpbb\db\doctrine\table_helper;

/**
 * BC layer for database tools.
 *
 * In general, it is recommended to use Doctrine directly instead of this class as this
 * implementation is only a BC layer.
 */
class doctrine implements tools_interface
{
	/**
	 * @var AbstractSchemaManager
	 */
	private $schema_manager;

	/**
	 * @var Connection
	 */
	private $connection;

	/**
	 * @var bool
	 */
	private $return_statements;

	/**
	 * Database tools constructors.
	 *
	 * @param Connection $connection
	 * @param bool       $return_statements
	 */
	public function __construct(Connection $connection, bool $return_statements = false)
	{
		$this->return_statements = $return_statements;
		$this->connection = $connection;
	}

	/**
	 * @return AbstractSchemaManager
	 *
	 * @throws Exception
	 */
	protected function get_schema_manager(): AbstractSchemaManager
	{
		if ($this->schema_manager == null)
		{
			$this->schema_manager = $this->connection->createSchemaManager();
		}

		return $this->schema_manager;
	}

	/**
	 * @return Schema
	 *
	 * @throws Exception
	 */
	protected function get_schema(): Schema
	{
		return $this->get_schema_manager()->createSchema();
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_list_tables(): array
	{
		try
		{
			$tables = array_map('strtolower', $this->get_schema_manager()->listTableNames());
			return array_combine($tables, $tables);
		}
		catch (Exception $e)
		{
			return [];
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_table_exists(string $table_name): bool
	{
		try
		{
			return $this->get_schema_manager()->tablesExist([$table_name]);
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_list_columns(string $table_name): array
	{
		try
		{
			return $this->get_asset_names($this->get_schema_manager()->listTableColumns($table_name));
		}
		catch (Exception $e)
		{
			return [];
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_column_exists(string $table_name, string $column_name): bool
	{
		try
		{
			return $this->asset_exists($column_name, $this->get_schema_manager()->listTableColumns($table_name));
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_list_index(string $table_name): array
	{
		return $this->get_asset_names($this->get_filtered_index_list($table_name, true));
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_index_exists(string $table_name, string $index_name): bool
	{
		return $this->asset_exists($index_name, $this->get_filtered_index_list($table_name, true));
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_unique_index_exists(string $table_name, string $index_name): bool
	{
		return $this->asset_exists($index_name, $this->get_filtered_index_list($table_name, false));
	}

	/**
	 * {@inheritDoc}
	 */
	public function perform_schema_changes(array $schema_changes)
	{
		if (empty($schema_changes))
		{
			return true;
		}

		return $this->alter_schema(
			function (Schema $schema) use ($schema_changes): void
			{
				$this->schema_perform_changes($schema, $schema_changes);
			}
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_create_table(string $table_name, array $table_data)
	{
		return $this->alter_schema(
			function (Schema $schema) use ($table_name, $table_data): void
			{
				$this->schema_create_table($schema, $table_name, $table_data, true);
			}
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_table_drop(string $table_name)
	{
		return $this->alter_schema(
			function (Schema $schema) use ($table_name): void
			{
				$this->schema_drop_table($schema, $table_name, true);
			}
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_column_add(string $table_name, string $column_name, array $column_data)
	{
		return $this->alter_schema(
			function (Schema $schema) use ($table_name, $column_name, $column_data): void
			{
				$this->schema_column_add($schema, $table_name, $column_name, $column_data);
			}
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_column_change(string $table_name, string $column_name, array $column_data)
	{
		$column_indexes = $this->get_filtered_index_list($table_name, true);

		$column_indexes = array_filter($column_indexes, function($index) use ($column_name) {
			$index_columns = array_map('strtolower', $index->getUnquotedColumns());
			return in_array($column_name, $index_columns, true);
		});

		if (count($column_indexes))
		{
			$ret = $this->alter_schema(
				function (Schema $schema) use ($table_name, $column_name, $column_data, $column_indexes): void
				{
					foreach ($column_indexes as $index)
					{
						$this->schema_index_drop($schema, $table_name, $index->getName());
					}
				}
			);

			if ($ret !== true)
			{
				return $ret;
			}
		}

		return $this->alter_schema(
			function (Schema $schema) use ($table_name, $column_name, $column_data, $column_indexes): void
			{
				$this->schema_column_change($schema, $table_name, $column_name, $column_data);

				if (count($column_indexes))
				{
					foreach ($column_indexes as $index)
					{
						$this->schema_create_index($schema, $table_name, $index->getName(), $index->getColumns());
					}
				}
			}
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_column_remove(string $table_name, string $column_name)
	{
		// Check if this column is part of a primary key. If yes, remove the primary key.
		$primary_key_indexes = $this->get_filtered_index_list($table_name, false);

		$primary_key_indexes = array_filter($primary_key_indexes, function($index) use ($column_name) {
			$index_columns = array_map('strtolower', $index->getUnquotedColumns());
			return in_array($column_name, $index_columns, true) && $index->isPrimary();
		});

		if (count($primary_key_indexes))
		{
			$ret = $this->alter_schema(
				function (Schema $schema) use ($table_name, $column_name): void
				{
					$table = $schema->getTable($table_name);
					$table->dropPrimaryKey();
				}
			);

			if ($ret !== true)
			{
				return $ret;
			}
		}

		return $this->alter_schema(
			function (Schema $schema) use ($table_name, $column_name): void
			{
				$this->schema_column_remove($schema, $table_name, $column_name);
			}
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_create_index(string $table_name, string $index_name, $column)
	{
		return $this->alter_schema(
			function (Schema $schema) use ($table_name, $index_name, $column): void
			{
				$this->schema_create_index($schema, $table_name, $index_name, $column);
			}
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_index_drop(string $table_name, string $index_name)
	{
		return $this->alter_schema(
			function (Schema $schema) use ($table_name, $index_name): void
			{
				$this->schema_index_drop($schema, $table_name, $index_name);
			}
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_create_unique_index(string $table_name, string $index_name, $column)
	{
		return $this->alter_schema(
			function (Schema $schema) use ($table_name, $index_name, $column): void
			{
				$this->schema_create_unique_index($schema, $table_name, $index_name, $column);
			}
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_create_primary_key(string $table_name, $column)
	{
		return $this->alter_schema(
			function (Schema $schema) use ($table_name, $column): void
			{
				$this->schema_create_primary_key($schema, $column, $table_name);
			}
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_truncate_table(string $table_name): void
	{
		try
		{
			$this->connection->executeQuery($this->get_schema_manager()->getDatabasePlatform()->getTruncateTableSQL($table_name));
		}
		catch (Exception $e)
		{
			return;
		}
	}

	/**
	 * Returns an array of indices for either unique and primary keys, or simple indices.
	 *
	 * @param string $table_name    The name of the table.
	 * @param bool   $is_non_unique Whether to return simple indices or primary and unique ones.
	 *
	 * @return Index[] The filtered index array.
	 */
	protected function get_filtered_index_list(string $table_name, bool $is_non_unique): array
	{
		try
		{
			$indices = $this->get_schema_manager()->listTableIndexes($table_name);
		}
		catch (Exception $e)
		{
			return [];
		}

		if ($is_non_unique)
		{
			return array_filter($indices, function (Index $index)
			{
				return $index->isSimpleIndex();
			});
		}

		return array_filter($indices, function (Index $index)
		{
			return !$index->isSimpleIndex();
		});
	}

	/**
	 * Returns an array of lowercase asset names.
	 *
	 * @param array $assets Array of assets.
	 *
	 * @return array An array of lowercase asset names.
	 */
	protected function get_asset_names(array $assets): array
	{
		return array_map(
			function (AbstractAsset $asset)
			{
				return strtolower($asset->getName());
			},
			$assets
		);
	}

	/**
	 * Returns whether an asset name exists in a list of assets (case insensitive).
	 *
	 * @param string $needle The asset name to search for.
	 * @param array  $assets The array of assets.
	 *
	 * @return bool Whether the asset name exists in a list of assets.
	 */
	protected function asset_exists(string $needle, array $assets): bool
	{
		return in_array(strtolower($needle), $this->get_asset_names($assets), true);
	}

	/**
	 * Alter the current database representation using a callback and execute the changes.
	 * Returns false in case of error.
	 *
	 * @param callable $callback Callback taking the schema as parameters and returning it altered (or null in case of error)
	 *
	 * @return bool|string[]
	 */
	protected function alter_schema(callable $callback)
	{
		try
		{
			$current_schema = $this->get_schema();
			$new_schema = clone $current_schema;
			call_user_func($callback, $new_schema);

			$comparator = new comparator();
			$schemaDiff = $comparator->compareSchemas($current_schema, $new_schema);
			$queries = $schemaDiff->toSql($this->get_schema_manager()->getDatabasePlatform());

			if ($this->return_statements)
			{
				return $queries;
			}

			foreach ($queries as $query)
			{
				// executeQuery() must be used here because $query might return a result set, for instance REPAIR does
				$this->connection->executeQuery($query);
			}

			return true;
		}
		catch (Exception $e)
		{
			// @todo: check if it makes sense to properly handle the exception
			return false;
		}
	}

	/**
	 * Alter table.
	 *
	 * @param string   $table_name Table name.
	 * @param callable $callback   Callback function to modify the table.
	 *
	 * @throws SchemaException
	 */
	protected function alter_table(Schema $schema, string $table_name, callable $callback): void
	{
		$table = $schema->getTable($table_name);
		call_user_func($callback, $table);
	}

	/**
	 * Perform schema changes
	 *
	 * @param Schema $schema
	 * @param array $schema_changes
	 */
	protected function schema_perform_changes(Schema $schema, array $schema_changes): void
	{
		$mapping = [
			'drop_tables' => [
				'method' => 'schema_drop_table',
				'use_key' => false,
			],
			'add_tables' => [
				'method' => 'schema_create_table',
				'use_key' => true,
			],
			'change_columns' => [
				'method' => 'schema_column_change_add',
				'use_key' => true,
				'per_table' => true,
			],
			'add_columns' => [
				'method' => 'schema_column_add',
				'use_key' => true,
				'per_table' => true,
			],
			'drop_columns' => [
				'method' => 'schema_column_remove',
				'use_key' => false,
				'per_table' => true,
			],
			'drop_keys' => [
				'method' => 'schema_index_drop',
				'use_key' => false,
				'per_table' => true,
			],
			'add_primary_keys' => [
				'method' => 'schema_create_primary_key',
				'use_key' => true,
			],
			'add_unique_index' => [
				'method' => 'schema_create_unique_index',
				'use_key' => true,
				'per_table' => true,
			],
			'add_index' => [
				'method' => 'schema_create_index',
				'use_key' => true,
				'per_table' => true,
			],
		];

		foreach ($mapping as $action => $params)
		{
			if (array_key_exists($action, $schema_changes))
			{
				foreach ($schema_changes[$action] as $table_name => $table_data)
				{
					if (array_key_exists('per_table', $params) && $params['per_table'])
					{
						foreach ($table_data as $key => $data)
						{
							if ($params['use_key'] == false)
							{
								$this->{$params['method']}($schema, $table_name, $data, true);
							}
							else
							{
								$this->{$params['method']}($schema, $table_name, $key, $data, true);
							}
						}
					}
					else
					{
						if ($params['use_key'] == false)
						{
							$this->{$params['method']}($schema, $table_data, true);
						}
						else
						{
							$this->{$params['method']}($schema, $table_name, $table_data, true);
						}
					}
				}
			}
		}
	}

	/**
	 * Update the schema representation with a new table.
	 * Returns null in case of errors
	 *
	 * @param Schema $schema
	 * @param string $table_name
	 * @param array  $table_data
	 * @param bool   $safe_check
	 *
	 * @throws SchemaException
	 */
	protected function schema_create_table(Schema $schema, string $table_name, array $table_data, bool $safe_check = false): void
	{
		if ($safe_check && $this->sql_table_exists($table_name))
		{
			return;
		}

		$table = $schema->createTable($table_name);
		$dbms_name = $this->get_schema_manager()->getDatabasePlatform()->getName();

		foreach ($table_data['COLUMNS'] as $column_name => $column_data)
		{
			list($type, $options) = table_helper::convert_column_data(
				$column_data,
				$dbms_name
			);
			$table->addColumn($column_name, $type, $options);
		}

		if (array_key_exists('PRIMARY_KEY', $table_data))
		{
			$table_data['PRIMARY_KEY'] = (!is_array($table_data['PRIMARY_KEY']))
				? [$table_data['PRIMARY_KEY']]
				: $table_data['PRIMARY_KEY'];

			$table->setPrimaryKey($table_data['PRIMARY_KEY']);
		}

		if (array_key_exists('KEYS', $table_data))
		{
			foreach ($table_data['KEYS'] as $key_name => $key_data)
			{
				$columns = (is_array($key_data[1])) ? $key_data[1] : [$key_data[1]];

				// Supports key columns defined with there length
				$columns = array_map(function (string $column)
				{
					if (strpos($column, ':') !== false)
					{
						$parts = explode(':', $column, 2);
						return $parts[0];
					}
					return $column;
				}, $columns);

				if ($key_data[0] === 'UNIQUE')
				{
					$table->addUniqueIndex($columns, $key_name);
				}
				else
				{
					$table->addIndex($columns, $key_name);
				}
			}
		}

		switch ($dbms_name)
		{
			case 'mysql':
				$table->addOption('collate', 'utf8_bin');
			break;
		}
	}

	/**
	 * @param Schema $schema
	 * @param string $table_name
	 * @param bool   $safe_check
	 *
	 * @throws SchemaException
	 */
	protected function schema_drop_table(Schema $schema, string $table_name, bool $safe_check = false): void
	{
		if ($safe_check && !$schema->hasTable($table_name))
		{
			return;
		}

		$schema->dropTable($table_name);
	}

	/**
	 * @param Schema $schema
	 * @param string $table_name
	 * @param string $column_name
	 * @param array  $column_data
	 * @param bool   $safe_check
	 *
	 * @throws SchemaException
	 */
	protected function schema_column_add(Schema $schema, string $table_name, string $column_name, array $column_data, bool $safe_check = false): void
	{
		$this->alter_table(
			$schema,
			$table_name,
			function (Table $table) use ($column_name, $column_data, $safe_check)
			{
				if ($safe_check && $table->hasColumn($column_name))
				{
					return false;
				}

				$dbms_name = $this->get_schema_manager()->getDatabasePlatform()->getName();

				list($type, $options) = table_helper::convert_column_data($column_data, $dbms_name);
				$table->addColumn($column_name, $type, $options);
				return $table;
			}
		);
	}

	/**
	 * @param Schema $schema
	 * @param string $table_name
	 * @param string $column_name
	 * @param array  $column_data
	 * @param bool   $safe_check
	 *
	 * @throws SchemaException
	 */
	protected function schema_column_change(Schema $schema, string $table_name, string $column_name, array $column_data, bool $safe_check = false): void
	{
		$this->alter_table(
			$schema,
			$table_name,
			function (Table $table) use ($column_name, $column_data, $safe_check): void
			{
				if ($safe_check && !$table->hasColumn($column_name))
				{
					return;
				}

				$dbms_name = $this->get_schema_manager()->getDatabasePlatform()->getName();

				list($type, $options) = table_helper::convert_column_data($column_data, $dbms_name);
				$options['type'] = Type::getType($type);
				$table->changeColumn($column_name, $options);
			}
		);
	}

	/**
	 * @param Schema $schema
	 * @param string $table_name
	 * @param string $column_name
	 * @param array  $column_data
	 * @param bool   $safe_check
	 *
	 * @throws SchemaException
	 */
	protected function schema_column_change_add(Schema $schema, string $table_name, string $column_name, array $column_data, bool $safe_check = false): void
	{
		$table = $schema->getTable($table_name);
		if ($table->hasColumn($column_name))
		{
			$this->schema_column_change($schema, $table_name, $column_name, $column_data, $safe_check);
		}
		else
		{
			$this->schema_column_add($schema, $table_name, $column_name, $column_data, $safe_check);
		}
	}

	/**
	 * @param Schema $schema
	 * @param string $table_name
	 * @param string $column_name
	 * @param bool   $safe_check
	 *
	 * @throws SchemaException
	 */
	protected function schema_column_remove(Schema $schema, string $table_name, string $column_name, bool $safe_check = false): void
	{
		$this->alter_table(
			$schema,
			$table_name,
			function (Table $table) use ($schema, $table_name, $column_name, $safe_check): void
			{
				if ($safe_check && !$table->hasColumn($column_name))
				{
					return;
				}

				/*
				 * As our sequences does not have the same name as these generated
				 * by default by doctrine or the DBMS, we have to manage them ourselves.
				 */
				if ($table->getColumn($column_name)->getAutoincrement())
				{
					foreach ($schema->getSequences() as $sequence)
					{
						if ($this->isSequenceAutoIncrementsFor($sequence, $table))
						{
							$schema->dropSequence($sequence->getName());
						}
					}
				}

				// Re-create / delete the indices using this column
				foreach ($table->getIndexes() as $index)
				{
					$index_columns = array_map('strtolower', $index->getUnquotedColumns());
					$key = array_search($column_name, $index_columns, true);
					if ($key !== false)
					{
						unset($index_columns[$key]);
						$this->recreate_index($table, $index, $index_columns);
					}
				}

				$table->dropColumn($column_name);
			}
		);
	}

	/**
	 * @param Schema $schema
	 * @param string $table_name
	 * @param string $index_name
	 * @param string|array $column
	 * @param bool   $safe_check
	 *
	 * @throws SchemaException
	 */
	protected function schema_create_index(Schema $schema, string $table_name, string $index_name, $column, bool $safe_check = false): void
	{
		$columns = (is_array($column)) ? $column : [$column];
		$table = $schema->getTable($table_name);

		if ($safe_check && $table->hasIndex($index_name))
		{
			return;
		}

		$table->addIndex($columns, $index_name);
	}

	/**
	 * @param Schema $schema
	 * @param string $table_name
	 * @param string $index_name
	 * @param string|array $column
	 * @param bool   $safe_check
	 *
	 * @throws SchemaException
	 */
	protected function schema_create_unique_index(Schema $schema, string $table_name, string $index_name, $column, bool $safe_check = false): void
	{
		$columns = (is_array($column)) ? $column : [$column];
		$table = $schema->getTable($table_name);

		if ($safe_check && $table->hasIndex($index_name))
		{
			return;
		}

		$table->addUniqueIndex($columns, $index_name);
	}

	/**
	 * @param Schema $schema
	 * @param string $table_name
	 * @param string $index_name
	 * @param bool   $safe_check
	 *
	 * @throws SchemaException
	 */
	protected function schema_index_drop(Schema $schema, string $table_name, string $index_name, bool $safe_check = false): void
	{
		$table = $schema->getTable($table_name);

		if ($safe_check && !$table->hasIndex($index_name))
		{
			return;
		}

		$table->dropIndex($index_name);
	}

	/**
	 * @param        $column
	 * @param Schema $schema
	 * @param string $table_name
	 * @param bool   $safe_check
	 *
	 * @throws SchemaException
	 */
	protected function schema_create_primary_key(Schema $schema, $column, string $table_name, bool $safe_check = false): void
	{
		$columns = (is_array($column)) ? $column : [$column];
		$table = $schema->getTable($table_name);
		$table->dropPrimaryKey();
		$table->setPrimaryKey($columns);
	}

	/**
	 * Recreate an index of a table
	 *
	 * @param Table $table
	 * @param Index $index
	 * @param array  Columns to use in the new (recreated) index
	 *
	 * @throws SchemaException
	 */
	protected function recreate_index(Table $table, Index $index, array $new_columns): void
	{
		if ($index->isPrimary())
		{
			$table->dropPrimaryKey();
		}
		else
		{
			$table->dropIndex($index->getName());
		}

		if (count($new_columns) > 0)
		{
			if ($index->isPrimary())
			{
				$table->setPrimaryKey(
					$new_columns,
					$index->getName(),
				);
			}
			else if ($index->isUnique())
			{
				$table->addUniqueIndex(
					$new_columns,
					$index->getName(),
					$index->getOptions(),
				);
			}
			else
			{
				$table->addIndex(
					$new_columns,
					$index->getName(),
					$index->getFlags(),
					$index->getOptions(),
				);
			}
		}
	}

	/**
	 * @param Sequence $sequence
	 * @param Table    $table
	 *
	 * @return bool
	 * @throws SchemaException
	 *
	 * @see Sequence
	 */
	private function isSequenceAutoIncrementsFor(Sequence $sequence, Table $table): bool
	{
		$primaryKey = $table->getPrimaryKey();

		if ($primaryKey === null)
		{
			return false;
		}

		$pkColumns = $primaryKey->getColumns();

		if (count($pkColumns) !== 1)
		{
			return false;
		}

		$column = $table->getColumn($pkColumns[0]);

		if (! $column->getAutoincrement())
		{
			return false;
		}

		$sequenceName      = $sequence->getShortestName($table->getNamespaceName());
		$tableName         = $table->getShortestName($table->getNamespaceName());
		$tableSequenceName = sprintf('%s_seq', $tableName);

		return $tableSequenceName === $sequenceName;
	}
}
