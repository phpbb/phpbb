<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_dbal_schema_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/config.xml');
	}

	public function test_config_value_multibyte()
	{
		$db = $this->new_dbal();

		$value = str_repeat("\xC3\x84", 255);
		$sql = "INSERT INTO phpbb_config
			(config_name, config_value)
			VALUES ('name', '$value')";
		$result = $db->sql_query($sql);

		$sql = "SELECT config_value
			FROM phpbb_config
			WHERE config_name = 'name'";
		$result = $db->sql_query_limit($sql, 1);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$this->assertEquals($value, $row['config_value']);
	}
}
