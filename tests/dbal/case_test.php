<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_dbal_case_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/config.xml');
	}

	public function test_case_string()
	{
		$db = $this->new_dbal();

		$sql = 'SELECT ' . $db->sql_case('1 = 1', '1', '0') . ' AS bool
			FROM phpbb_config';
		$result = $db->sql_query_limit($sql, 1);

		$this->assertEquals(true, (bool) $db->sql_fetchfield('bool'));

		$sql = 'SELECT ' . $db->sql_case('1 = 0', '1', '0') . ' AS bool
			FROM phpbb_config';
		$result = $db->sql_query_limit($sql, 1);

		$this->assertEquals(false, (bool) $db->sql_fetchfield('bool'));
	}

	public function test_case_statement()
	{
		$db = $this->new_dbal();

		$sql = 'SELECT ' . $db->sql_case('is_dynamic = 1', 'is_dynamic', '0') . " AS bool
			FROM phpbb_config
			WHERE is_dynamic = 1";
		$result = $db->sql_query_limit($sql, 1);

		$this->assertEquals(true, (bool) $db->sql_fetchfield('bool'));

		$sql = 'SELECT ' . $db->sql_case('is_dynamic = 1', '1', 'is_dynamic') . " AS bool
			FROM phpbb_config
			WHERE is_dynamic = 0";
		$result = $db->sql_query_limit($sql, 1);

		$this->assertEquals(false, (bool) $db->sql_fetchfield('bool'));
	}
}
