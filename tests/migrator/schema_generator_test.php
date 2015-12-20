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

require_once __DIR__ . '/../dbal/migration/dummy_order.php';
require_once __DIR__ . '/../dbal/migration/dummy_order_0.php';
require_once __DIR__ . '/../dbal/migration/dummy_order_1.php';
require_once __DIR__ . '/../dbal/migration/dummy_order_2.php';
require_once __DIR__ . '/../dbal/migration/dummy_order_3.php';
require_once __DIR__ . '/../dbal/migration/dummy_order_4.php';
require_once __DIR__ . '/../dbal/migration/dummy_order_5.php';

class schema_generator_test extends phpbb_test_case
{
	/** @var \phpbb\db\migration\schema_generator */
	protected $generator;

	public function setUp()
	{
		parent::setUp();

		$this->config = new \phpbb\config\config(array());
		$this->db = new \phpbb\db\driver\sqlite();
		$factory = new \phpbb\db\tools\factory();
		$this->db_tools = $factory->get($this->db);
		$this->table_prefix = 'phpbb_';
	}

	protected function get_schema_generator(array $class_names)
	{
		$this->generator = new \phpbb\db\migration\schema_generator($class_names, $this->config, $this->db, $this->db_tools, $this->phpbb_root_path, $this->php_ext, $this->table_prefix);

		return $this->generator;
	}

	/**
	 * @expectedException \UnexpectedValueException
	 */
	public function test_check_dependencies_fail()
	{
		$this->get_schema_generator(array('\phpbb\db\migration\data\v310\forgot_password'));

		$this->generator->get_schema();
	}

	public function test_get_schema_success()
	{
		$this->get_schema_generator(array(
			'\phpbb\db\migration\data\v30x\release_3_0_1_rc1',
			'\phpbb\db\migration\data\v30x\release_3_0_0',
			'\phpbb\db\migration\data\v310\boardindex'
		));

		$this->assertArrayHasKey('phpbb_users', $this->generator->get_schema());
	}

	public function column_add_after_data()
	{
		return array(
			array(
				'phpbb_dbal_migration_dummy_order_0',
				array(
					'foobar1',
					'foobar2',
					'foobar3',
				),
			),
			array(
				'phpbb_dbal_migration_dummy_order_1',
				array(
					'foobar1',
					'foobar3',
					'foobar4',
				),
			),
			array(
				'phpbb_dbal_migration_dummy_order_2',
				array(
					'foobar1',
					'foobar3',
					'foobar5',
				),
			),
			array(
				'phpbb_dbal_migration_dummy_order_3',
				array(
					'foobar1',
					'foobar3',
					'foobar6',
				),
			),
			array(
				'phpbb_dbal_migration_dummy_order_4',
				array(
					'foobar1',
					'foobar3',
					'foobar7',
				),
			),
			array(
				'phpbb_dbal_migration_dummy_order_5',
				array(
					'foobar1',
					'foobar3',
					'foobar9',
					'foobar8',
				),
			),
		);
	}

	/**
	* @dataProvider column_add_after_data
	*/
	public function test_column_add_after($migration, $expected)
	{
		$this->get_schema_generator(array(
			'phpbb_dbal_migration_dummy_order',
			$migration,
		));

		$tables = $this->generator->get_schema();

		$this->assertEquals(
			$expected,
			array_keys($tables[$this->table_prefix . 'column_order_test1']['COLUMNS']),
			'The schema generator could not position the column correctly, using the "after" option in the migration script.'
		);
	}
}
