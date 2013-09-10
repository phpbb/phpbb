<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
			FROM \phpbb\config\config';
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
			FROM \phpbb\config\config';
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
