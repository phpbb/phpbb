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
			'\phpbb\db\migration\data\v400\dev',
		];
	}

	public function update_schema()
	{
		$rename_index = $table_keys = [];
		$db_table_schema = $this->get_schema();
		foreach ($db_table_schema as $table_name => $table_data)
		{
			if (isset($table_data['KEYS']))
			{
				foreach ($table_data['KEYS'] as $key_name => $key_data)
				{
					$table_keys[$table_name][] = $key_name;
				}
			}
		}

		$short_table_names = table_helper::map_short_table_names([], $this->table_prefix);
		foreach ($table_keys as $table_name => $key_names)
		{
			$key_name_new = $short_table_names[$table_name] . '_' . $key_name;
			foreach ($key_names as $key_name)
			{
				$rename_index[$table_name][$key_name] = $key_name_new;
				$rename_index[$table_name][$table_name . '_' . $key_name] = $key_name_new;
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
}
