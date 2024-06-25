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

class phpbb_dbal_auto_increment_test extends phpbb_database_test_case
{
	protected $db;
	protected $db_doctrine;
	protected $tools;
	protected $table_exists;
	protected $table_data;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/config.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->db_doctrine = $this->new_doctrine_dbal();
		$factory = new \phpbb\db\tools\factory();
		$this->tools = $factory->get($this->db_doctrine);

		$this->table_data = array(
			'COLUMNS'		=> array(
				'c_id'				=> array('UINT', NULL, 'auto_increment'),
				'c_uint'				=> array('UINT', 4),
			),
			'PRIMARY_KEY'	=> 'c_id',
		);
		$this->tools->sql_create_table('prefix_table_name', $this->table_data);
		$this->table_exists = true;
	}

	protected function tearDown(): void
	{
		if ($this->table_exists)
		{
			$this->tools->sql_table_drop('prefix_table_name');
		}

		parent::tearDown();
	}

	protected static function get_default_values()
	{
		return array(
			'c_uint' => 0,
		);
	}

	public function test_auto_increment()
	{
		$sql = 'DELETE FROM prefix_table_name';
		$this->db->sql_query($sql);

		$row1 = array_merge(self::get_default_values(), array(
			'c_uint' => 1,
		));
		$row2 = array_merge(self::get_default_values(), array(
			'c_uint' => 2,
		));

		$sql = 'INSERT INTO prefix_table_name ' . $this->db->sql_build_array('INSERT', $row1);
		$this->db->sql_query($sql);
		$id1 = $this->db->sql_nextid();

		$sql = 'INSERT INTO prefix_table_name ' . $this->db->sql_build_array('INSERT', $row2);
		$this->db->sql_query($sql);
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
}
