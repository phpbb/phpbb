<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_dbal_sql_affected_rows_test extends phpbb_database_test_case
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	public function setUp()
	{
		parent::setUp();
		$this->db = $this->new_dbal();
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	public function test_select()
	{
		$sql = 'SELECT *
			FROM ' . CONFIG_TABLE;
		$this->db->sql_query($sql);

		$this->assertEquals(2, $this->db->sql_affectedrows());
	}

	public function test_update()
	{
		$sql = 'UPDATE ' . CONFIG_TABLE . "
			SET config_value = 'bertie'";
		$this->db->sql_query($sql);

		$this->assertEquals(2, $this->db->sql_affectedrows());
	}

	public function test_update_all_matched_unequal_updated()
	{
		$sql = 'UPDATE ' . CONFIG_TABLE . "
			SET config_value = 'foo'";
		$this->db->sql_query($sql);

		$this->assertEquals(2, $this->db->sql_affectedrows());
	}

	public function test_update_some_matched_unequal_updated()
	{
		$sql = 'UPDATE ' . CONFIG_TABLE . "
			SET config_value = 'foo'
			WHERE config_value = 'foo'";
		$this->db->sql_query($sql);

		$this->assertEquals(1, $this->db->sql_affectedrows());
	}

	public function test_insert()
	{
		$sql = 'INSERT INTO ' . CONFIG_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
			'config_name'	=> 'bertie',
			'config_value'	=> 'rules',
		));
		$this->db->sql_query($sql);

		$this->assertEquals(1, $this->db->sql_affectedrows());
	}
}
