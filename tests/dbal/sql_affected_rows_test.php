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

	public function test_update_same_value_matched_unequal_updated()
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
