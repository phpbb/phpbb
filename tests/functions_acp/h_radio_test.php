<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_acp.php';

class phpbb_functions_acp_h_radio_test extends phpbb_test_case
{
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
		global $user;

		$user = new phpbb_mock_user();
		$request = new phpbb_mock_request();

		$user->lang = new phpbb_mock_lang();

		$this->assertEquals($expected, h_radio($name, $input_ary, $input_default, $id, $key));
	}
}
