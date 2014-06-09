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

class phpbb_dbal_concatenate_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/config.xml');
	}

	public function test_concatenate_string()
	{
		$db = $this->new_dbal();

		$sql = 'SELECT config_name, ' . $db->sql_concatenate('config_name', "'" . $db->sql_escape('append') . "'") . ' AS string
			FROM phpbb_config';
		$result = $db->sql_query($sql);

		$db->sql_return_on_error(false);

		$this->assertEquals(array(
				array(
					'config_name'	=> 'config1',
					'string'		=> 'config1append',
				),
				array(
					'config_name'	=> 'config2',
					'string'		=> 'config2append',
				),
			),
			$db->sql_fetchrowset($result)
		);
	}

	public function test_concatenate_statement()
	{
		$db = $this->new_dbal();

		$sql = 'SELECT config_name, ' . $db->sql_concatenate('config_name', 'config_value') . ' AS string
			FROM phpbb_config';
		$result = $db->sql_query($sql);

		$db->sql_return_on_error(false);

		$this->assertEquals(array(
				array(
					'config_name'	=> 'config1',
					'string'		=> 'config1foo',
				),
				array(
					'config_name'	=> 'config2',
					'string'		=> 'config2bar',
				),
			),
			$db->sql_fetchrowset($result)
		);
	}
}
