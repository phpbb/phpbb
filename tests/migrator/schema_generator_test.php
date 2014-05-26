<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once __DIR__ . '/../dbal/migration/dummy_order.php';

class schmema_generator_test extends phpbb_test_case
{
	public function setUp()
	{
		parent::setUp();

		$this->config = new \phpbb\config\config(array());
		$this->db = new \phpbb\db\driver\sqlite();
		$this->db_tools = new \phpbb\db\tools($this->db);
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

	protected $expected_results_between = array(
		'foobar1' => array('BOOL', 0),
		'foobar2' => array('BOOL', 0),
		'foobar3' => array('BOOL', 0),
	);

	public function test_check_column_position_between_success()
	{
		$this->get_schema_generator(array(
			'phpbb_dbal_migration_dummy_order',
		));

		$tables = $this->generator->get_schema();
		$columns = $tables[$this->table_prefix . 'column_order_test1']['COLUMNS'];

		$this->assertEquals($columns, $expected_results_between, 'The schema generator could not position the column correctly between column 1 and 3, using the "after" option in the migration script.');
	}

	protected $expected_results_after_last = array(
		'foobar1' => array('BOOL', 0),
		'foobar3' => array('BOOL', 0),
		'foobar4' => array('BOOL', 0),
	);

	public function test_check_column_position_after_last_success()
	{
		$this->get_schema_generator(array(
			'phpbb_dbal_migration_dummy_order',
		));

		$tables = $this->generator->get_schema();
		$columns = $tables[$this->table_prefix . 'column_order_test2']['COLUMNS'];

		$this->assertEquals($columns, $expected_results_after_last, 'The schema generator could not position the column correctly after the last column, using the "after" option in the migration script.');
	}

	protected $expected_results_after_missing = array(
		'foobar1' => array('BOOL', 0),
		'foobar3' => array('BOOL', 0),
		'foobar5' => array('BOOL', 0),
	);

	public function test_check_column_position_after_missing_success()
	{
		$this->get_schema_generator(array(
			'phpbb_dbal_migration_dummy_order',
		));

		$tables = $this->generator->get_schema();
		$columns = $tables[$this->table_prefix . 'column_order_test3']['COLUMNS'];

		$this->assertEquals($columns, $expected_results_after_missing, 'The schema generator could not position the column after a "missing" column value, using the "after" option in the migration script.');
	}

	protected $expected_results_after_empty = array(
		'foobar1' => array('BOOL', 0),
		'foobar3' => array('BOOL', 0),
		'foobar5' => array('BOOL', 0),
	);

	public function test_check_column_position_after_empty_success()
	{
		$this->get_schema_generator(array(
			'phpbb_dbal_migration_dummy_order',
		));

		$tables = $this->generator->get_schema();
		$columns = $tables[$this->table_prefix . 'column_order_test4']['COLUMNS'];

		$this->assertEquals($columns, $expected_results_after_empty, 'The schema generator could not position the column after an "empty" column value, using the "after" option in the migration script.');
	}
}
