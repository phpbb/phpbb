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

require_once dirname(__FILE__) . '/auth_provider/invalid.php';
require_once dirname(__FILE__) . '/auth_provider/valid.php';

class phpbb_acp_board_select_auth_method_test extends phpbb_test_case
{
	/** @var \phpbb\acp\controller\board acp_board */
	protected $acp_board;

	public static function select_auth_method_data()
	{
		return array(
			array('acp_board_valid', '<option value="acp_board_valid" selected="selected" data-toggle-setting="#auth_acp_board_valid_settings">Acp_board_valid</option>'),
			array('acp_board_invalid', '<option value="acp_board_valid" data-toggle-setting="#auth_acp_board_valid_settings">Acp_board_valid</option>'),
		);
	}

	public function setUp(): void
	{
		parent::setUp();

		global $phpbb_container;

		$phpbb_container = new phpbb_mock_container_builder();
		$auth_collection = new \phpbb\auth\provider_collection($phpbb_container, new \phpbb\config\config([]));

		$valid = new phpbb\auth\provider\acp\board_valid();
		$invalid = new phpbb\auth\provider\acp\board_invalid();

		$auth_collection->add('auth.provider.acp_board_valid', $valid);
		$auth_collection->add('auth.provider.acp_board_invalid', $invalid);

		$phpbb_container->set('auth.provider.acp_board_valid', $valid);
		$phpbb_container->set('auth.provider.acp_board_invalid', $invalid);

		$this->acp_board = new \phpbb\acp\controller\board(
			$auth_collection,
			$this->createMock('\phpbb\avatar\manager'),
			$this->createMock('\phpbb\cache\driver\driver_interface'),
			$this->createMock('\phpbb\config\config'),
			$this->createMock('\phpbb\db\driver\driver_interface'),
			$this->createMock('\phpbb\event\dispatcher'),
			$this->createMock('\phpbb\acp\helper\controller'),
			$this->createMock('\phpbb\language\language'),
			$this->createMock('\phpbb\log\log'),
			$this->createMock('\phpbb\request\request'),
			$this->createMock('\phpbb\template\template'),
			$this->createMock('\phpbb\textformatter\cache_interface'),
			$this->createMock('\phpbb\user'),
			'',
			'',
			[]
		);
	}

	/**
	* @dataProvider select_auth_method_data
	*/
	public function test_select_auth_method($selected, $expected)
	{
		$this->assertEquals($expected, $this->acp_board->select_auth_method($selected));
	}
}
