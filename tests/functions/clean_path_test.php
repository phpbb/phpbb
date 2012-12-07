<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_clean_path_test extends phpbb_test_case
{
	public function clean_path_test_data()
	{
		return array(
			array('foo', 'foo'),
			array('foo/bar', 'foo/bar'),
			array('foo/bar/', 'foo/bar/'),
			array('foo/./bar', 'foo/bar'),
			array('foo/./././bar', 'foo/bar'),
			array('foo/bar/.', 'foo/bar'),
			array('./foo/bar', './foo/bar'),
			array('../foo/bar', '../foo/bar'),
			array('one/two/three', 'one/two/three'),
			array('one/two/../three', 'one/three'),
			array('one/../two/three', 'two/three'),
			array('one/two/..', 'one'),
			array('one/two/../', 'one/'),
			array('one/two/../three/../four', 'one/four'),
			array('one/two/three/../../four', 'one/four'),
		);
	}

	/**
	* @dataProvider clean_path_test_data
	*/
	public function test_clean_path($input, $expected)
	{
		$output = phpbb_clean_path($input);

		$this->assertEquals($expected, $output);
	}
}
