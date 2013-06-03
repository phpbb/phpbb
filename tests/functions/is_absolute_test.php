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
			// Empty
			array('', false),

			// Absolute unix style
			array('/etc/phpbb', true),
			// Unix does not support \ so that is not an absolute path
			array('\etc\phpbb', false),

			// Absolute windows style
			array('c:\windows', true),
			array('C:\Windows', true),
			array('c:/windows', true),
			array('C:/Windows', true),

			// Executable
			array('etc/phpbb', false),
			array('explorer.exe', false),

			// Relative subdir
			array('Windows\System32', false),
			array('Windows\System32\explorer.exe', false),
			array('Windows/System32', false),
			array('Windows/System32/explorer.exe', false),

			// Relative updir
			array('..\Windows\System32', false),
			array('..\Windows\System32\explorer.exe', false),
			array('../Windows/System32', false),
			array('../Windows/System32/explorer.exe', false),
		);
	}

	/**
	* @dataProvider is_absolute_data
	*/
	public function test_is_absolute($path, $expected)
	{
		$this->assertEquals($expected, phpbb_is_absolute($path));
	}
}
