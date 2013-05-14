<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_functions_is_absolute_test extends phpbb_test_case
{
	static public function is_absolute_data()
	{
		return array(
			array('/etc/phpbb', true),
			array('etc/phpbb', false),

			// Until we got DIRECTORY_SEPARATOR replaced in that function,
			// test results vary on OS.
			array('c:\windows', DIRECTORY_SEPARATOR == '\\'),
			array('C:\Windows', DIRECTORY_SEPARATOR == '\\'),
		);
	}

	/**
	* @dataProvider is_absolute_data
	*/
	public function test_is_absolute($path, $expected)
	{
		$this->assertEquals($expected, is_absolute($path));
	}
}
