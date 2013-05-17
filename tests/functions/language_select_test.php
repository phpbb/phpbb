<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_functions_language_select_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/language_select.xml');
	}

	static public function language_select_data()
	{
		return array(
			array('', '<option value="cs">Čeština</option><option value="en">English</option>'),
			array('en', '<option value="cs">Čeština</option><option value="en" selected="selected">English</option>'),
			array('cs', '<option value="cs" selected="selected">Čeština</option><option value="en">English</option>'),
			array('de', '<option value="cs">Čeština</option><option value="en">English</option>'),
		);
	}

	/**
	* @dataProvider language_select_data
	*/
	public function test_language_select($default, $expected)
	{
		global $db;
		$db = $this->new_dbal();

		$this->assertEquals($expected, language_select($default));
	}
}
