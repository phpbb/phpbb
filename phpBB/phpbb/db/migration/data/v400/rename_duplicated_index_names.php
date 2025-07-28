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

namespace phpbb\db\migration\data\v400;

use phpbb\db\migration\migration;
use phpbb\db\doctrine\table_helper;
use phpbb\db\tools\doctrine as doctrine_dbtools;

class rename_duplicated_index_names extends migration
{
	/**
	 * @var array
	 */
	protected static $table_keys;

	/**
	 * @var array
	 */
	protected static $rename_index;

	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v400\storage_track_index',
		];
	}

	public function update_schema()
	{
		if (!isset(self::$rename_index))
		{
			self::$rename_index = [];
			if (!isset(self::$table_keys))
			{
				$this->get_tables_index_names();
			}
			$short_table_names = table_helper::map_short_table_names(array_keys(self::$table_keys), $this->table_prefix);

			foreach (self::$table_keys as $table_name => $key_names)
			{
				$prefixless_table_name = doctrine_dbtools::remove_prefix($table_name, $this->table_prefix);
				foreach ($key_names as $key_name)
				{
					// If 'old' key name is already new format, do not rename it
					if (str_starts_with($key_name, $short_table_names[$table_name]))
					{
						continue;
					}

					// If 'old' key name is prefixed by its table name with and/or without table name common prefix
					// (f.e. 'phpbb_log_log_time'), remove it to prefix with the relevant table's short name
					$cleaned_key_name = $key_name;
					foreach ([$table_name, $prefixless_table_name] as $prefix)
					{
						$cleaned_key_name = doctrine_dbtools::remove_prefix($cleaned_key_name, $prefix);
					}
					$key_name_new = $short_table_names[$table_name] . '_' . $cleaned_key_name;

					self::$rename_index[$table_name][$key_name] = $key_name_new;
				}
			}
		}

		return [
			'rename_index' => self::$rename_index ?? [],
		];
	}

	public function revert_schema()
	{
		$schema = $this->update_schema();
		array_walk($schema['rename_index'], function (&$index_data, $table_name) {
		  $index_data = array_flip($index_data);
		});

		return $schema;
	}

	protected function get_schema()
	{
		$self_classname = '\\' . str_replace('/', '\\', self::class);
		$finder_factory = new \phpbb\finder\factory(null, false, $this->phpbb_root_path, $this->php_ext);
		$finder = $finder_factory->get();
		$migrator_classes = $finder->core_path('phpbb/db/migration/data/')->get_classes();
		$self_class_index = array_search($self_classname, $migrator_classes);
		unset($migrator_classes[$self_class_index]);

		$schema_generator = new \phpbb\db\migration\schema_generator(
			$migrator_classes,
			$this->config,
			$this->db,
			$this->db_tools,
			$this->phpbb_root_path,
			$this->php_ext,
			$this->table_prefix,
			$this->tables
		);

		return $schema_generator->get_schema();
	}

	public function get_tables_index_names()
	{
		$schema_manager = $this->db_tools->get_connection()->createSchemaManager();
		$table_names = $schema_manager->listTableNames();

		if (!empty($table_names))
		{
			foreach ($table_names as $table_name)
			{
				$indices = $schema_manager->listTableIndexes($table_name);
				$indices = array_keys(array_filter($indices,
					function (\Doctrine\DBAL\Schema\Index $index)
					{
						return !$index->isPrimary();
					})
				);

				if (!empty($indices))
				{
					self::$table_keys[$table_name] = $indices;
				}
			}
		}
		else
		{
			foreach ($this->get_schema() as $table_name => $table_data)
			{
				if (isset($table_data['KEYS']))
				{
					self::$table_keys[$table_name] = array_keys($table_data['KEYS']);
				}
			}
		}
	}
}
