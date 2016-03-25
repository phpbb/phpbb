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

class phpbb_functions_style_select_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/style_select.xml');
	}

	static public function style_select_data()
	{
		return array(
			array('', false, '<option value="1">prosilver</option><option value="2">subsilver2</option>'),
			array('', true, '<option value="1">prosilver</option><option value="2">subsilver2</option><option value="3">zoo</option>'),
			array('1', false, '<option value="1" selected="selected">prosilver</option><option value="2">subsilver2</option>'),
			array('1', true, '<option value="1" selected="selected">prosilver</option><option value="2">subsilver2</option><option value="3">zoo</option>'),
			array('3', false, '<option value="1">prosilver</option><option value="2">subsilver2</option>'),
			array('3', true, '<option value="1">prosilver</option><option value="2">subsilver2</option><option value="3" selected="selected">zoo</option>'),
		);
	}

	/**
	* @dataProvider style_select_data
	*/
	public function test_style_select($default, $all, $expected)
	{
		global $db;
		$db = $this->new_dbal();

		$this->assertEquals($expected, style_select($default, $all));
	}
}
