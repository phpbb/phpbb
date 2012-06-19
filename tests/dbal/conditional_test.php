<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_dbal_conditional_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/config.xml');
	}

	public function test_conditional_string()
	{
		$db = $this->new_dbal();

		$sql = 'SELECT config_name, ' . $db->sql_conditional('is_dynamic = 1', "'" . $db->sql_escape('true') . "'", "'" . $db->sql_escape('false') . "'") . ' AS string
			FROM phpbb_config';
		$result = $db->sql_query($sql);

		$db->sql_return_on_error(false);

		$this->assertEquals(array(
				array(
					'config_name'	=> 'config1',
					'string'		=> 'false',
				),
				array(
					'config_name'	=> 'config2',
					'string'		=> 'true',
				),
			),
			$db->sql_fetchrowset($result)
		);
	}

	public function test_conditional_statement()
	{
		$db = $this->new_dbal();

		$sql = 'SELECT config_name, ' . $db->sql_conditional('is_dynamic = 1', 'is_dynamic', 'config_value') . ' AS string
			FROM phpbb_config';
		$result = $db->sql_query($sql);

		$db->sql_return_on_error(false);

		$this->assertEquals(array(
				array(
					'config_name'	=> 'config1',
					'string'		=> 'foo',
				),
				array(
					'config_name'	=> 'config2',
					'string'		=> '1',
				),
			),
			$db->sql_fetchrowset($result)
		);
	}
}
