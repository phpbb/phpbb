<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/


class phpbb_dbal_utf8_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/utf8_fixture.xml');
	}

	static public function utf8_data_fixture_data()
	{
		return array(
			array('Belarusian', 'Беларуская'),
			array('Arabic', 'العربية'),
			array('Czech', 'Čeština'),
			array('German', 'Deutsch öäüß'),
		);
	}

	/**
	* @dataProvider utf8_data_fixture_data
	*/
	public function test_utf8_data_fixture($english, $local)
	{
		$this->markTestIncomplete('Currently UTF8 characters can not be part of fixtures, see PHPBB3-11547');

		$db = $this->new_dbal();

		$sql = "SELECT config_value
			FROM phpbb_config
			WHERE config_name = '" . $db->sql_escape($english) . "'";
		$result = $db->sql_query($sql);

		$this->assertEquals($local, $db->sql_fetchfield('config_value'));
		$db->sql_freeresult($result);
	}
}
