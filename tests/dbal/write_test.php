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

class phpbb_dbal_write_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/config.xml');
	}

	public function build_array_insert_data()
	{
		return array(
			array(array(
				'config_name'	=> 'test_version',
				'config_value'	=> '0.0.0',
				'is_dynamic'	=> 1,
			)),
			array(array(
				'config_name'	=> 'second config',
				'config_value'	=> '10',
				'is_dynamic'	=> 0,
			)),
		);
	}

	/**
	* @dataProvider build_array_insert_data
	*/
	public function test_build_array_insert($sql_ary)
	{
		$db = $this->new_dbal();

		$sql = 'INSERT INTO phpbb_config ' . $db->sql_build_array('INSERT', $sql_ary);
		$result = $db->sql_query($sql);

		$sql = "SELECT *
			FROM phpbb_config
			WHERE config_name = '" . $sql_ary['config_name'] . "'";
		$result = $db->sql_query_limit($sql, 1);

		$this->assertEquals($sql_ary, $db->sql_fetchrow($result));

		$db->sql_freeresult($result);
	}

	public function test_delete()
	{
		$db = $this->new_dbal();

		$sql = "DELETE FROM phpbb_config
			WHERE config_name = 'config1'";
		$result = $db->sql_query($sql);

		$sql = 'SELECT *
			FROM phpbb_config';
		$result = $db->sql_query($sql);
		$rows = $db->sql_fetchrowset($result);

		$this->assertEquals(1, sizeof($rows));
		$this->assertEquals('config2', $rows[0]['config_name']);

		$db->sql_freeresult($result);
	}

	public function test_multiple_insert()
	{
		$db = $this->new_dbal();

		// empty the table
		$sql = 'DELETE FROM phpbb_config';
		$db->sql_query($sql);

		$batch_ary = array(
			array(
				'config_name'	=> 'batch one',
				'config_value'	=> 'b1',
				'is_dynamic'	=> 0,
			),
			array(
				'config_name'	=> 'batch two',
				'config_value'	=> 'b2',
				'is_dynamic'	=> 1,
			),
		);

		$result = $db->sql_multi_insert('phpbb_config', $batch_ary);

		$sql = 'SELECT *
			FROM phpbb_config
			ORDER BY config_name ASC';
		$result = $db->sql_query($sql);

		$this->assertEquals($batch_ary, $db->sql_fetchrowset($result));

		$db->sql_freeresult($result);
	}

	public function update_data()
	{
		return array(
			array(
				array(
					'config_value'	=> '23',
					'is_dynamic'	=> 0,
				),
				" WHERE config_name = 'config1'",
				array(
					array(
						'config_name'	=> 'config1',
						'config_value'	=> '23',
						'is_dynamic'	=> 0,
					),
					array(
						'config_name'	=> 'config2',
						'config_value'	=> 'bar',
						'is_dynamic'	=> 1,
					),
				),
			),
			array(
				array(
					'config_value'	=> '0',
					'is_dynamic'	=> 1,
				),
				'',
				array(
					array(
						'config_name'	=> 'config1',
						'config_value'	=> '0',
						'is_dynamic'	=> 1,
					),
					array(
						'config_name'	=> 'config2',
						'config_value'	=> '0',
						'is_dynamic'	=> 1,
					),
				),
			),
		);
	}

	/**
	* @dataProvider update_data
	*/
	public function test_update($sql_ary, $where, $expected)
	{
		$db = $this->new_dbal();

		$sql = 'UPDATE phpbb_config
			SET ' . $db->sql_build_array('UPDATE', $sql_ary) . $where;
		$result = $db->sql_query($sql);

		$sql = 'SELECT *
			FROM phpbb_config
			ORDER BY config_name ASC';
		$result = $db->sql_query($sql);

		$this->assertEquals($expected, $db->sql_fetchrowset($result));

		$db->sql_freeresult($result);
	}
}
