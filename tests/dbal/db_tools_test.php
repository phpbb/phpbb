<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/db/db_tools.php';

class phpbb_dbal_db_tools_test extends phpbb_database_test_case
{
	protected $db;
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
		$this->tools = new phpbb_db_tools($this->db);

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
			'c_char_size' => '',
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

	public function test_auto_increment()
	{
		$sql = 'DELETE FROM prefix_table_name';
		$result = $this->db->sql_query($sql);

		$row1 = array_merge(self::get_default_values(), array(
			'c_uint' => 1,
			'c_vchar' => '1', // these values are necessary to avoid unique index issues
			'c_vchar_size' => '1',
		));
		$row2 = array_merge(self::get_default_values(), array(
			'c_uint' => 2,
			'c_vchar' => '2',
			'c_vchar_size' => '2',
		));

		$sql = 'INSERT INTO prefix_table_name ' . $this->db->sql_build_array('INSERT', $row1);
		$result = $this->db->sql_query($sql);
		$id1 = $this->db->sql_nextid();

		$sql = 'INSERT INTO prefix_table_name ' . $this->db->sql_build_array('INSERT', $row2);
		$result = $this->db->sql_query($sql);
		$id2 = $this->db->sql_nextid();

		$this->assertGreaterThan($id1, $id2, 'Auto increment should increase the id value');

		$sql = "SELECT *
			FROM prefix_table_name WHERE c_id = $id1";
		$result = $this->db->sql_query($sql);
		$row_actual = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$row1['c_id'] = $id1;
		$this->assertEquals($row1, $row_actual);

		$sql = "SELECT *
			FROM prefix_table_name WHERE c_id = $id2";
		$result = $this->db->sql_query($sql);
		$row_actual = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$row2['c_id'] = $id2;
		$this->assertEquals($row2, $row_actual);
	}

	public function test_column_exists()
	{
		$this->assertTrue($this->tools->sql_column_exists('prefix_table_name', 'c_id'));
		$this->assertFalse($this->tools->sql_column_exists('prefix_table_name', 'column_does_not_exist'));
	}

	public function test_column_remove()
	{
		$this->assertTrue($this->tools->sql_column_exists('prefix_table_name', 'c_id'));

		$this->assertTrue($this->tools->sql_column_remove('prefix_table_name', 'c_id'));

		$this->assertFalse($this->tools->sql_column_exists('prefix_table_name', 'c_id'));
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

		$this->tools->sql_table_drop('prefix_test_table');
	}

}
