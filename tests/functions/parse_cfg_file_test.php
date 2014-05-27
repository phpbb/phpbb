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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_functions_parse_cfg_file extends phpbb_test_case
{
	public function parse_cfg_file_data()
	{
		return array(
			array(
				array(
					'#',
					'# phpBB Style Configuration File',
					'#',
					'# This file is part of the phpBB Forum Software package.',
					'#',
					'# @copyright (c) phpBB Limited <https://www.phpbb.com>',
					'# @license GNU General Public License, version 2 (GPL-2.0)',
					'#',
					'# For full copyright and license information, please see',
					'# the docs/CREDITS.txt file.',
					'#',
					'# At the left is the name, please do not change this',
					'# At the right the value is entered',
					'# For on/off options the valid values are on, off, 1, 0, true and false',
					'#',
					'# Values get trimmed, if you want to add a space in front or at the end of',
					'# the value, then enclose the value with single or double quotes.',
					'# Single and double quotes do not need to be escaped.',
					'#',
					'',
					'# General Information about this style',
					'name = prosilver',
					'copyright = © phpBB Limited, 2007',
					'version = 3.0.12',
				),
				array(
					'name'		=> 'prosilver',
					'copyright'	=> '© phpBB Limited, 2007',
					'version'	=> '3.0.12',
				),
			),
			array(
				array(
					'name = subsilver2',
					'copyright = © 2005 phpBB Limited',
					'version = 3.0.12',
				),
				array(
					'name'		=> 'subsilver2',
					'copyright'	=> '© 2005 phpBB Limited',
					'version'	=> '3.0.12',
				),
			),
			array(
				array(
					'foo = on',
					'foo1 = true',
					'foo2 = 1',
					'bar = off',
					'bar1 = false',
					'bar2 = 0',
					'foobar =',
					'foobar1 = "asdf"',
					'foobar2 = \'qwer\'',
				),
				array(
					'foo'		=> true,
					'foo1'		=> true,
					'foo2'		=> true,
					'bar'		=> false,
					'bar1'		=> false,
					'bar2'		=> false,
					'foobar'	=> '',
					'foobar1'	=> 'asdf',
					'foobar2'	=> 'qwer',
				),
			),
			array(
				array(
					'foo = &amp; bar',
					'bar = <a href="test">Test</a>',
				),
				array(
					'foo'		=> '&amp;amp; bar',
					'bar'		=> '&lt;a href=&quot;test&quot;&gt;Test&lt;/a&gt;',
				),
			),
		);
	}

	/**
	* @dataProvider parse_cfg_file_data
	*/
	public function test_parse_cfg_file($file_contents, $expected)
	{
		$this->assertEquals($expected, parse_cfg_file(false, $file_contents));
	}
}
