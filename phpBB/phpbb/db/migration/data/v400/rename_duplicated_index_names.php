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

class rename_duplicated_index_names extends migration
{
	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v400\storage_track_index',
		];
	}

	public function update_schema()
	{
		$rename_index = [];
		$is_prefixed_index = false;
		$tables_index_names = $this->get_tables_index_names();
		$short_table_names = table_helper::map_short_table_names(array_keys($tables_index_names), $this->table_prefix);

		foreach ($tables_index_names as $table_name => $key_names)
		{
			foreach ($key_names as $key_name)
			{
				$prefixless_table_name = strpos($table_name, $this->table_prefix) === 0 ? substr($table_name, strlen($this->table_prefix)) : $table_name;

				// Check if there's at least one index name is prefixed, otherwise we operate on generated database schema
				$is_prefixed_index = $is_prefixed_index || (strpos($key_name, $table_name) === 0);

				// If key name is prefixed by its table name (with or without tables prefix), remove that key name prefix.
				$cleaned_key_name = !$is_prefixed_index ? $key_name : str_replace(strpos($key_name, $table_name) === 0 ? $table_name . '_' : $prefixless_table_name . '_', '', $key_name);

				$key_name_new = $short_table_names[$table_name] . '_' . $cleaned_key_name;
				$rename_index[$table_name][$key_name !== $cleaned_key_name ? $key_name : $cleaned_key_name] = $key_name_new;
			}
		}

		return [
			'rename_index' => $rename_index,
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
		$table_keys = [];
		$schema_manager = $this->db_doctrine->createSchemaManager();
		$table_names = $schema_manager->listTableNames();

		if (!empty($table_names))
		{
			foreach ($table_names as $table_name)
			{
				$indices = $schema_manager->listTableIndexes($table_name);

				$index_names = array_keys(
					array_filter($indices, function (\Doctrine\DBAL\Schema\Index $index)
					{
						return !$index->isPrimary();
					})
				);

				if (!empty($index_names))
				{
					$table_keys[$table_name] = $index_names;
				}
			}
		}
		else
		{
			$db_table_schema = $this->get_schema();
			foreach ($db_table_schema as $table_name => $table_data)
			{
				if (isset($table_data['KEYS']))
				{
					$table_keys[$table_name] = array_keys($table_data['KEYS']);
				}
			}
		}

		return $table_keys;
	}
}
