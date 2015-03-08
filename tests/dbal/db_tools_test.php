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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_dbal_db_tools_test extends phpbb_database_test_case
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	/** @var \phpbb\db\tools\tools_interface */
	protected $tools;
	protected $table_exists;
	protected $table_data;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/config.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$factory = new \phpbb\db\tools\factory();
		$this->tools = $factory->get($this->db);

		$this->table_data = array(
			'COLUMNS'		=> array(
				'c_id'				=> array('UINT', NULL, 'auto_increment'),
				'c_int_size'			=> array('INT:4', 4),
				'c_bint'				=> array('BINT', 4),
				'c_uint'				=> array('UINT', 4),
				'c_uint_size'			=> array('UINT:4', 4),
				'c_tint_size'			=> array('TINT:2', 4),
				'c_usint'				=> array('USINT', 4),
				'c_bool'				=> array('BOOL', 1),
				'c_vchar'				=> array('VCHAR', 'foo'),
				'c_vchar_size'		=> array('VCHAR:4', 'foo'),
				'c_vchar_null'		=> array('VCHAR', null),
				'c_char_size'			=> array('CHAR:4', 'foo'),
				'c_xstext'			=> array('XSTEXT', 'foo'),
				'c_stext'				=> array('STEXT', 'foo'),
				'c_text'				=> array('TEXT', 'foo'),
				'c_mtext'				=> array('MTEXT', 'foo'),
				'c_xstext_uni'		=> array('XSTEXT_UNI', 'foo'),
				'c_stext_uni'			=> array('STEXT_UNI', 'foo'),
				'c_text_uni'			=> array('TEXT_UNI', 'foo'),
				'c_mtext_uni'			=> array('MTEXT_UNI', 'foo'),
				'c_timestamp'			=> array('TIMESTAMP', 4),
				'c_decimal'			=> array('DECIMAL', 4.2),
				'c_decimal_size'		=> array('DECIMAL:6', 4.2),
				'c_pdecimal'			=> array('PDECIMAL', 4.2),
				'c_pdecimal_size'		=> array('PDECIMAL:7', 4.2),
				'c_vchar_uni'			=> array('VCHAR_UNI', 'foo'),
				'c_vchar_uni_size'	=> array('VCHAR_UNI:4', 'foo'),
				'c_vchar_ci'			=> array('VCHAR_CI', 'foo'),
				'c_varbinary'			=> array('VARBINARY', 'foo'),
			),
			'PRIMARY_KEY'	=> 'c_id',
			'KEYS'			=> array(
				'i_simple'	=> array('INDEX', 'c_uint'),
				'i_uniq'	=> array('UNIQUE', 'c_vchar'),
				'i_comp'	=> array('INDEX', array('c_vchar_uni', 'c_bool')),
				'i_comp_uniq'	=> array('UNIQUE', array('c_vchar_size', 'c_usint')),
			),
		);
		$this->tools->sql_create_table('prefix_table_name', $this->table_data);
		$this->table_exists = true;
	}

	protected function tearDown()
	{
		if ($this->table_exists)
		{
			$this->tools->sql_table_drop('prefix_table_name');
		}

		parent::tearDown();
	}

	public function test_created_and_drop_table()
	{
		// table is empty after creation and queryable
		$sql = 'SELECT * FROM prefix_table_name';
		$result = $this->db->sql_query($sql);
		$this->assertTrue(! $this->db->sql_fetchrow($result));
		$this->db->sql_freeresult($result);

		$this->table_exists = false;
		$this->tools->sql_table_drop('prefix_table_name');
	}

	static protected function get_default_values()
	{
		return array(
			'c_int_size' => 0,
			'c_bint' => 0,
			'c_uint' => 0,
			'c_uint_size' => 0,
			'c_tint_size' => 0,
			'c_usint' => 0,
			'c_bool' => 0,
			'c_vchar' => '',
			'c_vchar_size' => '',
			'c_vchar_null' => null,
			'c_char_size' => 'abcd',
			'c_xstext' => '',
			'c_stext' => '',
			'c_text' => '',
			'c_mtext' => '',
			'c_xstext_uni' => '',
			'c_stext_uni' => '',
			'c_text_uni' => '',
			'c_mtext_uni' => '',
			'c_timestamp' => 0,
			'c_decimal' => 0,
			'c_decimal_size' => 0,
			'c_pdecimal' => 0,
			'c_pdecimal_size' => 0,
			'c_vchar_uni' => '',
			'c_vchar_uni_size' => '',
			'c_vchar_ci' => '',
			'c_varbinary' => '',
		);
	}

	static public function column_values()
	{
		return array(
			array('c_int_size', -9999),
			array('c_bint', '99999999999999999'),
			array('c_uint', 16777215),
			array('c_uint_size', 9999),
			array('c_tint_size', -99),
			array('c_usint', 99),
			array('c_bool', 0),
			array('c_vchar', str_repeat('a', 255)),
			array('c_vchar_size', str_repeat('a', 4)),
			array('c_vchar_null', str_repeat('a', 4)),
			array('c_char_size', str_repeat('a', 4)),
			array('c_xstext', str_repeat('a', 1000)),
			array('c_stext', str_repeat('a', 3000)),
			array('c_text', str_repeat('a', 8000)),
			array('c_mtext', str_repeat('a', 10000)),
			array('c_xstext_uni', str_repeat("\xC3\x84", 100)),
			array('c_stext_uni', str_repeat("\xC3\x84", 255)),
			array('c_text_uni', str_repeat("\xC3\x84", 4000)),
			array('c_mtext_uni', str_repeat("\xC3\x84", 10000)),
			array('c_timestamp', 2147483647),
			array('c_decimal', 999.99),
			array('c_decimal_size', 9999.99),
			array('c_pdecimal', 999.999),
			array('c_pdecimal_size', 9999.999),
			array('c_vchar_uni', str_repeat("\xC3\x84", 255)),
			array('c_vchar_uni_size', str_repeat("\xC3\x84", 4)),
			array('c_vchar_ci', str_repeat("\xC3\x84", 255)),
			array('c_varbinary', str_repeat("\x00\xFF", 127)),
		);
	}

	/**
	* @dataProvider column_values
	*/
	public function test_created_column($column_name, $column_value)
	{
		if ($column_name === 'c_varbinary' && stripos(get_class($this->db), 'mysql') === false)
		{
			$this->markTestIncomplete('Binary handling is not implemented properly on non-MySQL DBMSes.');
		}

		$row_insert = self::get_default_values();
		$row_insert[$column_name] = $column_value;

		// empty table
		$sql = 'DELETE FROM prefix_table_name';
		$result = $this->db->sql_query($sql);

		$sql = 'INSERT INTO prefix_table_name ' . $this->db->sql_build_array('INSERT', $row_insert);
		$result = $this->db->sql_query($sql);

		$sql = "SELECT *
			FROM prefix_table_name";
		$result = $this->db->sql_query($sql);
		$row_actual = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$row_expect = $row_insert;

		unset($row_actual['id']); // auto increment id changes, so ignore

		$type = $this->table_data['COLUMNS'][$column_name][0];
		$this->assertEquals($row_expect[$column_name], $row_actual[$column_name], "Column $column_name of type $type should have equal return and input value.");
	}

	public function test_list_columns()
	{
		$this->assertEquals(
			array_keys($this->table_data['COLUMNS']),
			array_values($this->tools->sql_list_columns('prefix_table_name'))
		);
	}

	public function test_column_exists()
	{
		$this->assertTrue($this->tools->sql_column_exists('prefix_table_name', 'c_id'));
		$this->assertFalse($this->tools->sql_column_exists('prefix_table_name', 'column_does_not_exist'));
	}

	public function test_column_change_with_index()
	{
		// Create column
		$this->assertFalse($this->tools->sql_column_exists('prefix_table_name', 'c_bug_12012'));
		$this->assertTrue($this->tools->sql_column_add('prefix_table_name', 'c_bug_12012', array('DECIMAL', 0)));
		$this->assertTrue($this->tools->sql_column_exists('prefix_table_name', 'c_bug_12012'));

		// Create index over the column
		$this->assertFalse($this->tools->sql_index_exists('prefix_table_name', 'i_bug_12012'));
		$this->assertTrue($this->tools->sql_create_index('prefix_table_name', 'i_bug_12012', array('c_bug_12012', 'c_bool')));
		$this->assertTrue($this->tools->sql_index_exists('prefix_table_name', 'i_bug_12012'));

		// Change type from int to string
		$this->assertTrue($this->tools->sql_column_change('prefix_table_name', 'c_bug_12012', array('VCHAR:100', '')));

		// Remove the index
		$this->assertTrue($this->tools->sql_index_exists('prefix_table_name', 'i_bug_12012'));
		$this->assertTrue($this->tools->sql_index_drop('prefix_table_name', 'i_bug_12012'));
		$this->assertFalse($this->tools->sql_index_exists('prefix_table_name', 'i_bug_12012'));

		// Remove the column
		$this->assertTrue($this->tools->sql_column_exists('prefix_table_name', 'c_bug_12012'));
		$this->assertTrue($this->tools->sql_column_remove('prefix_table_name', 'c_bug_12012'));
		$this->assertFalse($this->tools->sql_column_exists('prefix_table_name', 'c_bug_12012'));
	}

	public function test_column_change_with_composite_primary()
	{
		// Remove the old primary key
		$this->assertTrue($this->tools->sql_column_remove('prefix_table_name', 'c_id'));
		$this->assertTrue($this->tools->sql_column_add('prefix_table_name', 'c_id', array('UINT', 0)));

		// Create a composite key
		$this->assertTrue($this->tools->sql_create_primary_key('prefix_table_name', array('c_id', 'c_uint')));

		// Create column
		$this->assertFalse($this->tools->sql_column_exists('prefix_table_name', 'c_bug_12643'));
		$this->assertTrue($this->tools->sql_column_add('prefix_table_name', 'c_bug_12643', array('DECIMAL', 0)));
		$this->assertTrue($this->tools->sql_column_exists('prefix_table_name', 'c_bug_12643'));

		// Change type from int to string
		$this->assertTrue($this->tools->sql_column_change('prefix_table_name', 'c_bug_12643', array('VCHAR:100', '')));
	}

	public function test_column_remove()
	{
		$this->assertTrue($this->tools->sql_column_exists('prefix_table_name', 'c_int_size'));

		$this->assertTrue($this->tools->sql_column_remove('prefix_table_name', 'c_int_size'));

		$this->assertFalse($this->tools->sql_column_exists('prefix_table_name', 'c_int_size'));
	}

	public function test_column_remove_similar_name()
	{
		$this->assertTrue($this->tools->sql_column_exists('prefix_table_name', 'c_vchar'));
		$this->assertTrue($this->tools->sql_column_exists('prefix_table_name', 'c_vchar_size'));

		$this->assertTrue($this->tools->sql_column_remove('prefix_table_name', 'c_vchar'));

		$this->assertFalse($this->tools->sql_column_exists('prefix_table_name', 'c_vchar'));
		$this->assertTrue($this->tools->sql_column_exists('prefix_table_name', 'c_vchar_size'));
	}

	public function test_column_remove_with_index()
	{
		// Create column
		$this->assertFalse($this->tools->sql_column_exists('prefix_table_name', 'c_bug_12012_2'));
		$this->assertTrue($this->tools->sql_column_add('prefix_table_name', 'c_bug_12012_2', array('UINT', 4)));
		$this->assertTrue($this->tools->sql_column_exists('prefix_table_name', 'c_bug_12012_2'));

		// Create index over the column
		$this->assertFalse($this->tools->sql_index_exists('prefix_table_name', 'bug_12012_2'));
		$this->assertTrue($this->tools->sql_create_index('prefix_table_name', 'bug_12012_2', array('c_bug_12012_2', 'c_bool')));
		$this->assertTrue($this->tools->sql_index_exists('prefix_table_name', 'bug_12012_2'));

		$this->assertFalse($this->tools->sql_index_exists('prefix_table_name', 'bug_12012_3'));
		$this->assertTrue($this->tools->sql_create_index('prefix_table_name', 'bug_12012_3', array('c_bug_12012_2')));
		$this->assertTrue($this->tools->sql_index_exists('prefix_table_name', 'bug_12012_3'));

		// Remove the column
		$this->assertTrue($this->tools->sql_column_exists('prefix_table_name', 'c_bug_12012_2'));
		$this->assertTrue($this->tools->sql_column_remove('prefix_table_name', 'c_bug_12012_2'));
		$this->assertFalse($this->tools->sql_column_exists('prefix_table_name', 'c_bug_12012_2'));
	}

	public function test_column_remove_primary()
	{
		$this->assertTrue($this->tools->sql_column_exists('prefix_table_name', 'c_id'));

		$this->assertTrue($this->tools->sql_column_remove('prefix_table_name', 'c_id'));

		$this->assertFalse($this->tools->sql_column_exists('prefix_table_name', 'c_id'));
	}

	public function test_list_tables()
	{
		$tables = $this->tools->sql_list_tables();
		$this->assertTrue(isset($tables['prefix_table_name']));
		$this->assertFalse(isset($tables['prefix_does_not_exist']));
	}

	public function test_table_exists()
	{
		$this->assertTrue($this->tools->sql_table_exists('prefix_table_name'));
		$this->assertFalse($this->tools->sql_table_exists('prefix_does_not_exist'));
	}

	public function test_table_drop()
	{
		$this->tools->sql_create_table('prefix_test_table',
			array('COLUMNS' => array(
				'foo' => array('UINT', 42)))
		);

		$this->assertTrue($this->tools->sql_table_exists('prefix_test_table'));

		$this->tools->sql_table_drop('prefix_test_table');

		$this->assertFalse($this->tools->sql_table_exists('prefix_test_table'));
	}

	public function test_perform_schema_changes_drop_tables()
	{
		$db_tools = $this->getMock('\phpbb\db\tools\tools', array(
			'sql_table_exists',
			'sql_table_drop',
		), array(&$this->db));

		// pretend all tables exist
		$db_tools->expects($this->any())->method('sql_table_exists')
			->will($this->returnValue(true));

		// drop tables
		$db_tools->expects($this->exactly(2))->method('sql_table_drop');
		$db_tools->expects($this->at(1))->method('sql_table_drop')
			->with($this->equalTo('dropped_table_1'));
		$db_tools->expects($this->at(3))->method('sql_table_drop')
			->with($this->equalTo('dropped_table_2'));

		$db_tools->perform_schema_changes(array(
			'drop_tables' => array(
				'dropped_table_1',
				'dropped_table_2',
			),
		));
	}

	public function test_perform_schema_changes_drop_columns()
	{
		$db_tools = $this->getMock('\phpbb\db\tools\tools', array(
			'sql_column_exists',
			'sql_column_remove',
		), array(&$this->db));

		// pretend all columns exist
		$db_tools->expects($this->any())->method('sql_column_exists')
			->will($this->returnValue(true));
		$db_tools->expects($this->any())->method('sql_column_exists')
			->will($this->returnValue(true));

		// drop columns
		$db_tools->expects($this->exactly(2))->method('sql_column_remove');
		$db_tools->expects($this->at(1))->method('sql_column_remove')
			->with($this->equalTo('existing_table'), $this->equalTo('dropped_column_1'));
		$db_tools->expects($this->at(3))->method('sql_column_remove')
			->with($this->equalTo('existing_table'), $this->equalTo('dropped_column_2'));

		$db_tools->perform_schema_changes(array(
			'drop_columns' => array(
				'existing_table' => array(
					'dropped_column_1',
					'dropped_column_2',
				),
			),
		));
	}

	public function test_index_exists()
	{
		$this->assertTrue($this->tools->sql_index_exists('prefix_table_name', 'i_simple'));
	}

	public function test_unique_index_exists()
	{
		$this->assertTrue($this->tools->sql_unique_index_exists('prefix_table_name', 'i_uniq'));
	}

	public function test_create_index_against_index_exists()
	{
		$this->tools->sql_create_index('prefix_table_name', 'fookey', array('c_timestamp', 'c_decimal'));
		$this->assertTrue($this->tools->sql_index_exists('prefix_table_name', 'fookey'));
	}

	public function test_create_unique_index_against_unique_index_exists()
	{
		$this->tools->sql_create_unique_index('prefix_table_name', 'i_uniq_ts_id', array('c_timestamp', 'c_id'));
		$this->assertTrue($this->tools->sql_unique_index_exists('prefix_table_name', 'i_uniq_ts_id'));
	}

	public function test_create_int_default_null()
	{
		$this->assertFalse($this->tools->sql_column_exists('prefix_table_name', 'c_bug_13282'));
		$this->assertTrue($this->tools->sql_column_add('prefix_table_name', 'c_bug_13282', array('TINT:2')));
		$this->assertTrue($this->tools->sql_column_exists('prefix_table_name', 'c_bug_13282'));
	}
}
