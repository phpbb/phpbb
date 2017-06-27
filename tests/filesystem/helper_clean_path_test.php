<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

use phpbb\filesystem\helper as filesystem_helper;

class phpbb_filesystem_helper_clean_path_test extends phpbb_test_case
{

	public function setUp()
	{
		parent::setUp();
	}

	public function clean_path_data()
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
			array('./../foo/bar', './../foo/bar'),
			array('././../foo/bar', './../foo/bar'),
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
	* @dataProvider clean_path_data
	*/
	public function test_clean_path($input, $expected)
	{
		$this->assertEquals($expected, filesystem_helper::clean_path($input));
	}
}
