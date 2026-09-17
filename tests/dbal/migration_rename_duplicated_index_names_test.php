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

use phpbb\db\migration\data\v400\rename_duplicated_index_names;

class phpbb_dbal_migration_rename_duplicated_index_names_test extends phpbb_database_test_case
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\db\tools\tools_interface */
	protected $tools;

	/** @var array */
	protected $static_properties = [];

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/config.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$factory = new \phpbb\db\tools\factory();
		$this->tools = $factory->get($this->new_doctrine_dbal());
		$this->tools->set_table_prefix('prefix_');

		// The migration caches its computed schema changes in static properties;
		// save and clear them so this test starts clean and cannot leak its
		// tables into other tests running in the same process.
		foreach (['table_keys', 'rename_index'] as $property_name)
		{
			$property = new ReflectionProperty(rename_duplicated_index_names::class, $property_name);
			$property->setAccessible(true);
			$this->static_properties[$property_name] = $property->getValue();
			$property->setValue(null, null);
		}

		$this->db->sql_query('CREATE TABLE foreign_indexed (id INT NOT NULL, val INT NOT NULL, PRIMARY KEY (id))');
		$this->db->sql_query('CREATE INDEX i_foreign ON foreign_indexed (val)');
		$this->db->sql_query('CREATE TABLE prefix_indexed (c_id INT NOT NULL, c_val INT NOT NULL, PRIMARY KEY (c_id))');
		$this->db->sql_query('CREATE INDEX i_old ON prefix_indexed (c_val)');
	}

	protected function tearDown(): void
	{
		$this->db->sql_query('DROP TABLE foreign_indexed');
		$this->db->sql_query('DROP TABLE prefix_indexed');

		foreach ($this->static_properties as $property_name => $value)
		{
			$property = new ReflectionProperty(rename_duplicated_index_names::class, $property_name);
			$property->setAccessible(true);
			$property->setValue(null, $value);
		}

		parent::tearDown();
	}

	public function test_update_schema_ignores_tables_not_carrying_the_prefix()
	{
		$migration = new rename_duplicated_index_names(
			new \phpbb\config\config([]),
			$this->db,
			$this->tools,
			__DIR__ . '/../../phpBB/',
			'php',
			'prefix_',
			[]
		);

		$schema = $migration->update_schema();

		$short_table_names = \phpbb\db\doctrine\table_helper::map_short_table_names(['prefix_indexed'], 'prefix_');
		$this->assertSame(
			['prefix_indexed' => ['i_old' => $short_table_names['prefix_indexed'] . '_i_old']],
			$schema['rename_index']
		);
	}
}
