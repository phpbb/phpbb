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

require_once dirname(__FILE__) . '/../../phpBB/includes/acp/acp_board.php';
require_once dirname(__FILE__) . '/auth_provider/invalid.php';
require_once dirname(__FILE__) . '/auth_provider/valid.php';

class phpbb_acp_board_select_auth_method_test extends phpbb_test_case
{
	protected $acp_board;

	public static function select_auth_method_data()
	{
		return array(
			array('acp_board_valid', '<option value="acp_board_valid" selected="selected" data-toggle-setting="#auth_acp_board_valid_settings">Acp_board_valid</option>'),
			array('acp_board_invalid', '<option value="acp_board_valid" data-toggle-setting="#auth_acp_board_valid_settings">Acp_board_valid</option>'),
		);
	}

	public function setUp()
	{
		parent::setUp();

		global $phpbb_container;
		$phpbb_container = new phpbb_mock_container_builder();

		$phpbb_container->set('auth.provider_collection', array(
				'auth.provider.acp_board_valid'		=> new phpbb\auth\provider\acp\board_valid,
				'auth.provider.acp_board_invalid'	=> new phpbb\auth\provider\acp\board_invalid,
		));

		$this->acp_board = new acp_board();
	}

	/**
	* @dataProvider select_auth_method_data
	*/
	public function test_select_auth_method($selected, $expected)
	{
		$this->assertEquals($expected, $this->acp_board->select_auth_method($selected));
	}
}
