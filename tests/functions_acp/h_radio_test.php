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

class phpbb_functions_acp_h_radio_test extends phpbb_test_case
{
	protected function setUp()
	{
		parent::setUp();

		global $user;

		$user = new phpbb_mock_user();
		$user->lang = new phpbb_mock_lang();
	}

	public function h_radio_data()
	{
		return array(
			array(
				'test_name',
				array(
					'test'		=> 'TEST',
					'second'	=> 'SEC_OPTION',
				),
				false,
				false,
				false,
				'<label><input type="radio" name="test_name" value="test" class="radio" /> TEST</label><label><input type="radio" name="test_name" value="second" class="radio" /> SEC_OPTION</label>',
			),
			array(
				'test_name',
				array(
					'test'		=> 'TEST',
					'second'	=> 'SEC_OPTION',
				),
				'test',
				false,
				false,
				'<label><input type="radio" name="test_name" value="test" checked="checked" class="radio" /> TEST</label><label><input type="radio" name="test_name" value="second" class="radio" /> SEC_OPTION</label>',
			),
			array(
				'test_name',
				array(
					'test'		=> 'TEST',
					'second'	=> 'SEC_OPTION',
				),
				false,
				'test_id',
				false,
				'<label><input type="radio" name="test_name" id="test_id" value="test" class="radio" /> TEST</label><label><input type="radio" name="test_name" value="second" class="radio" /> SEC_OPTION</label>',
			),
			array(
				'test_name',
				array(
					'test'		=> 'TEST',
					'second'	=> 'SEC_OPTION',
				),
				'test',
				'test_id',
				false,
				'<label><input type="radio" name="test_name" id="test_id" value="test" checked="checked" class="radio" /> TEST</label><label><input type="radio" name="test_name" value="second" class="radio" /> SEC_OPTION</label>',
			),

			array(
				'test_name',
				array(
					'test'		=> 'TEST',
					'second'	=> 'SEC_OPTION',
				),
				false,
				false,
				'k',
				'<label><input type="radio" name="test_name" value="test" accesskey="k" class="radio" /> TEST</label><label><input type="radio" name="test_name" value="second" accesskey="k" class="radio" /> SEC_OPTION</label>',
			),
			array(
				'test_name',
				array(
					'test'		=> 'TEST',
					'second'	=> 'SEC_OPTION',
				),
				'test',
				false,
				'k',
				'<label><input type="radio" name="test_name" value="test" checked="checked" accesskey="k" class="radio" /> TEST</label><label><input type="radio" name="test_name" value="second" accesskey="k" class="radio" /> SEC_OPTION</label>',
			),
			array(
				'test_name',
				array(
					'test'		=> 'TEST',
					'second'	=> 'SEC_OPTION',
				),
				false,
				'test_id',
				'k',
				'<label><input type="radio" name="test_name" id="test_id" value="test" accesskey="k" class="radio" /> TEST</label><label><input type="radio" name="test_name" value="second" accesskey="k" class="radio" /> SEC_OPTION</label>',
			),
			array(
				'test_name',
				array(
					'test'		=> 'TEST',
					'second'	=> 'SEC_OPTION',
				),
				'test',
				'test_id',
				'k',
				'<label><input type="radio" name="test_name" id="test_id" value="test" checked="checked" accesskey="k" class="radio" /> TEST</label><label><input type="radio" name="test_name" value="second" accesskey="k" class="radio" /> SEC_OPTION</label>',
			),
		);
	}

	/**
	* @dataProvider h_radio_data
	*/
	public function test_h_radio($name, $input_ary, $input_default, $id, $key, $expected)
	{
		$this->assertEquals($expected, h_radio($name, $input_ary, $input_default, $id, $key));
	}
}
