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

use phpbb\template\asset;

class phpbb_template_asset_test extends phpbb_test_case
{
	public function set_path_data()
	{
		return array(
			// array(phpbb_root_path, given path, expected path),
			array('.', 'foo/bar', 'foo/bar'),
			array('../', 'foo/bar', 'foo/bar'),
			array('./phpBB/', 'foo/bar', 'foo/bar'),
			array('../', __DIR__ . '/foo/bar', '../' . basename(dirname(dirname(__DIR__))) . '/tests/template/foo/bar'),
			array('./', __DIR__ . '/foo/bar', './tests/template/foo/bar'),
			array('./phpBB/', __DIR__ . '/foo/bar', 'tests/template/foo/bar'),
		);
	}

	/**
	 * @dataProvider set_path_data
	 */
	public function test_set_path($phpbb_root_path, $path, $expected)
	{
		$path_helper = $this->getMockBuilder('\phpbb\path_helper')
			->disableOriginalConstructor()
			->setMethods(array())
			->getMock();

		$path_helper->method('get_phpbb_root_path')
			->willReturn($phpbb_root_path);

		$asset = new asset('', $path_helper, new phpbb\filesystem\filesystem());

		$asset->set_path($path, true);
		$this->assertEquals($expected, $asset->get_path());
	}
}
