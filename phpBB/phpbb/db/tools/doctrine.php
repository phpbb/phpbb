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
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use phpbb\db\doctrine\table_helper;

/**
 * BC layer for database tools.
 *
 * In general, it is recommended to use Doctrine directly instead of this class as this
 * implementation is only a BC layer.
 *
 * In the 3.3.x version branch this class could return SQL statements instead of
 * performing changes. This functionality has been removed.
 */
class doctrine implements tools_interface
{
	/**
	 * @var Comparator
	 */
	private $comparator;

	/**
	 * @var \Doctrine\DBAL\Schema\AbstractSchemaManager
	 */
	private $schema_manager;

	/**
	 * Database tools constructors.
	 *
	 * @param Connection $connection
	 *
	 * @throws Exception If the schema manager cannot be created.
	 */
	public function __construct(Connection $connection)
	{
		$this->comparator = new Comparator();
		$this->schema_manager = $connection->createSchemaManager();
	}

	/**
	 * {@inheritDoc}
	 */
	public function perform_schema_changes(array $schema_changes): void
	{
		// @todo
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_list_tables(): array
	{
		try
		{
			return array_map('strtolower', $this->schema_manager->listTableNames());
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
			return $this->schema_manager->tablesExist([$table_name]);
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_create_table(string $table_name, array $table_data): bool
	{
		if ($this->sql_table_exists($table_name))
		{
			return false;
		}

		try
		{
			$table = new Table($table_name);
			$dbms_name = $this->schema_manager->getDatabasePlatform()->getName();

			foreach ($table_data['COLUMNS'] as $column_name => $column_data)
			{
				list($type, $options) = table_helper::convert_column_data(
					$column_data,
					$dbms_name
				);
				$table->addColumn($column_name, $type, $options);
			}

			$table_data['PRIMARY_KEY'] = (!is_array($table_data['PRIMARY_KEY']))
				? [$table_data['PRIMARY_KEY']]
				: $table_data['PRIMARY_KEY'];

			$table->setPrimaryKey($table_data['PRIMARY_KEY']);

			if (array_key_exists('KEYS', $table_data))
			{
				foreach ($table_data['KEYS'] as $key_name => $key_data)
				{
					$columns = (is_array($key_data[1])) ? $key_data[1] : [$key_data[1]];
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

			$this->schema_manager->createTable($table);
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_table_drop(string $table_name): bool
	{
		try
		{
			$this->schema_manager->dropTable($table_name);
			return true;
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
			return $this->get_asset_names($this->schema_manager->listTableColumns($table_name));
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
			return $this->asset_exists($column_name, $this->schema_manager->listTableColumns($table_name));
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_column_add(string $table_name, string $column_name, array $column_data): bool
	{
		$dbms_name = $this->schema_manager->getDatabasePlatform()->getName();
		return $this->alter_table(
			$table_name,
			function (Table $table) use ($column_name, $column_data, $dbms_name) {
				list($type, $options) = table_helper::convert_column_data($column_data, $dbms_name);
				return $table->addColumn($column_name, $type, $options);
			}
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_column_change(string $table_name, string $column_name, array $column_data): bool
	{
		// @todo: index handling.
		return $this->alter_table(
			$table_name,
			function (Table $table) use ($column_name, $column_data) {
				// @todo type maps to options['type']
				//$table->dropColumn($column_name);
				//list($type, $options) = table_helper::convert_column_data($column_data);
				//return $table->addColumn($column_name, $type, $options);
			}
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_column_remove(string $table_name, string $column_name): bool
	{
		// @todo: index handling.
		return $this->alter_table(
			$table_name,
			function (Table $table) use ($column_name) {
				return $table->dropColumn($column_name);
			}
		);
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
	public function sql_create_index(string $table_name, string $index_name, $column): bool
	{
		$column = (is_array($column)) ? $column : [$column];
		$index = new Index($index_name, $column);
		try
		{
			$this->schema_manager->createIndex($index, $table_name);
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_index_drop(string $table_name, string $index_name): bool
	{
		try
		{
			$this->schema_manager->dropIndex($index_name, $table_name);
			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
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
	public function sql_create_unique_index(string $table_name, string $index_name, $column): bool
	{
		$column = (is_array($column)) ? $column : [$column];
		$index = new Index($index_name, $column, true);
		try
		{
			$this->schema_manager->createIndex($index, $table_name);
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_create_primary_key(string $table_name, $column): bool
	{
		$column = (is_array($column)) ? $column : [$column];
		$index = new Index('primary', $column, true, true);
		try
		{
			$this->schema_manager->createIndex($index, $table_name);
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Returns an array of indices for either unique and primary keys, or simple indices.
	 *
	 * @param string	$table_name		The name of the table.
	 * @param bool		$is_non_unique	Whether to return simple indices or primary and unique ones.
	 *
	 * @return array The filtered index array.
	 */
	private function get_filtered_index_list(string $table_name, bool $is_non_unique): array
	{
		try
		{
			$indices = $this->schema_manager->listTableIndexes($table_name);
		}
		catch (Exception $e)
		{
			return [];
		}

		if ($is_non_unique)
		{
			return array_filter($indices, function(Index $index) {
				return $index->isSimpleIndex();
			});
		}

		return array_filter($indices, function(Index $index) {
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
	private function get_asset_names(array $assets): array
	{
		return array_map(
			function(AbstractAsset $asset) {
				return strtolower($asset->getName());
			},
			$assets
		);
	}

	/**
	 * Returns whether an asset name exists in a list of assets (case insensitive).
	 *
	 * @param string	$needle	The asset name to search for.
	 * @param array		$assets	The array of assets.
	 *
	 * @return bool Whether the asset name exists in a list of assets.
	 */
	private function asset_exists(string $needle, array $assets): bool
	{
		return in_array(strtolower($needle), $this->get_asset_names($assets), true);
	}

	/**
	 * Alter table.
	 *
	 * @param string	$table_name	Table name.
	 * @param callable	$callback	Callback function to modify the table.
	 *
	 * @return bool True if the changes were applied successfully, false otherwise.
	 */
	private function alter_table(string $table_name, callable $callback): bool
	{
		try
		{
			$table = $this->schema_manager->listTableDetails($table_name);
			$altered_table = clone $table;
			$altered_table = call_user_func($callback, $altered_table);
			$diff = $this->comparator->diffTable($table, $altered_table);
			if ($diff === false)
			{
				return true;
			}

			$this->schema_manager->alterTable($diff);
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}
}
