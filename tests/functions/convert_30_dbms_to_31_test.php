<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_convert_30_dbms_to_31_test extends phpbb_test_case
{
	public function convert_30_dbms_to_31_data()
	{
		return array(
			array('firebird'),
			array('mssql'),
			array('mssql_odbc'),
			array('mssqlnative'),
			array('mysql'),
			array('mysqli'),
			array('oracle'),
			array('postgres'),
			array('sqlite'),
		);
	}

	/**
	* @dataProvider convert_30_dbms_to_31_data
	*/
	public function test_convert_30_dbms_to_31($input)
	{
		$expected = "\\phpbb\\db\\driver\\$input";

		$output = phpbb_convert_30_dbms_to_31($input);

		$this->assertEquals($expected, $output);
	}
}
