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

require_once __DIR__ . '/../../phpBB/includes/acp/acp_board.php';
require_once __DIR__ . '/auth_provider/invalid.php';
require_once __DIR__ . '/auth_provider/valid.php';

class phpbb_acp_board_select_auth_method_test extends phpbb_test_case
{
	protected $acp_board;

	public static function select_auth_method_data()
	{
		return [
			[
				'acp_board_valid',
				[
					'options' => [
						0 => [
							'value'		=> 'acp_board_valid',
							'label'		=> 'Acp_board_valid',
							'selected'	=> true,
							'data' 		=> [
								'toggle-setting' => '#auth_acp_board_valid_settings',
							],
						]
					],
				]
			],
			[
				'acp_board_invalid',
				[
					'options' => [
						0 => [
							'value'		=> 'acp_board_valid',
							'label'		=> 'Acp_board_valid',
							'selected'	=> false,
							'data' 		=> [
								'toggle-setting' => '#auth_acp_board_valid_settings',
							],
						]	
					],
				]
			],
		];
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_container, $config;
		$phpbb_container = new phpbb_mock_container_builder();
		$config = new \phpbb\config\config([]);

		// Create auth provider service collection
		$auth_provider_collection = new \phpbb\auth\provider_collection($phpbb_container, $config);
		$phpbb_container->set('auth.provider_collection', $auth_provider_collection);

		// Create auth provider services
		$phpbb_container->set('auth.provider.acp_board_valid', new phpbb\auth\provider\acp\board_valid);
		$phpbb_container->set('auth.provider.acp_board_invalid', new phpbb\auth\provider\acp\board_invalid);

		// Add auth provider servives to the service collection
		$auth_provider_collection->add('auth.provider.acp_board_valid');
		$auth_provider_collection->add('auth.provider.acp_board_invalid');

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
