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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_acp.php';

class phpbb_functions_acp_built_select_test extends phpbb_test_case
{
	protected function setUp()
	{
		parent::setUp();

		global $user;

		$user = new phpbb_mock_user();
		$user->lang = new phpbb_mock_lang();
	}

	public function build_select_data()
	{
		return array(
			array(
				array(
					'test'		=> 'TEST',
					'second'	=> 'SEC_OPTION',
				),
				false,
				'<option value="test">TEST</option><option value="second">SEC_OPTION</option>',
			),
			array(
				array(
					'test'		=> 'TEST',
					'second'	=> 'SEC_OPTION',
				),
				'test',
				'<option value="test" selected="selected">TEST</option><option value="second">SEC_OPTION</option>',
			),
			array(
				array(
					'test'		=> 'TEST',
					'second'	=> 'SEC_OPTION',
				),
				'second',
				'<option value="test">TEST</option><option value="second" selected="selected">SEC_OPTION</option>',
			),
		);
	}

	/**
	* @dataProvider build_select_data
	*/
	public function test_build_select($option_ary, $option_default, $expected)
	{
		$this->assertEquals($expected, build_select($option_ary, $option_default));
	}
}
