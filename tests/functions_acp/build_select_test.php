<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_acp.php';

class phpbb_functions_acp_built_select_test extends phpbb_test_case
{
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
		global $user;

    $user = new phpbb_mock_user();
    $request = new phpbb_mock_request();

		$user->lang = new phpbb_mock_lang();

		$this->assertEquals($expected, build_select($option_ary, $option_default));
	}
}
