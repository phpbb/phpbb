<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/acp/acp_board.php';
require_once dirname(__FILE__) . '/auth_provider/invalid.php';
require_once dirname(__FILE__) . '/auth_provider/valid.php';

class phpbb_acp_board_select_auth_method_test extends phpbb_test_case
{
	public static function function_return()
	{
		return array(
			array('acp_board_valid', '<option value="acp_board_valid" selected="selected">Acp_board_valid</option>'),
			array('acp_board_invalid', '<option value="acp_board_valid">Acp_board_valid</option>'),
		);
	}

	/**
	* @dataProvider function_return
	*/
	public function test_invalid_provider($selected, $expected)
	{
		global $phpbb_container;
		$phpbb_container = new phpbb_mock_container_builder();

		$phpbb_container->set('auth.provider_collection', array(
				'auth.provider.acp_board_valid'		=> new phpbb_auth_provider_acp_board_valid,
				'auth.provider.acp_board_invalid'	=> new phpbb_auth_provider_acp_board_invalid,
		));

		$acp_board = new acp_board();

		$this->assertEquals($expected, $acp_board->select_auth_method($selected));
	}
}
