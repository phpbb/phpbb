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

class phpbb_functions_confirm_box_test extends phpbb_test_case
{
	public static function data_confirm_box_check(): array
	{
		return [
			'valid_match_string_user_id' => [
				'2',
				'0e123456789012345678901234567890',
				'0E12345678',
				2,
				'0e123456789012345678901234567890',
				'0E12345678',
				true,
			],
			'reject_magic_hash_confirm_key_00' => [
				'2',
				'abcdef1234567890abcdef1234567890',
				'0E12345678',
				2,
				'abcdef1234567890abcdef1234567890',
				'00',
				false,
			],
			'reject_magic_hash_confirm_key_0e0' => [
				'2',
				'abcdef1234567890abcdef1234567890',
				'0E12345678',
				2,
				'abcdef1234567890abcdef1234567890',
				'0e0',
				false,
			],
			'reject_magic_hash_session_id' => [
				'2',
				'0e123456789012345678901234567890',
				'ABCDEF1234',
				2,
				'00',
				'ABCDEF1234',
				false,
			],
			'reject_case_mismatch_confirm_key' => [
				'2',
				'abcdef1234567890abcdef1234567890',
				'ABCDEF1234',
				2,
				'abcdef1234567890abcdef1234567890',
				'abcdef1234',
				false,
			],
		];
	}

	/**
	 * @backupGlobals enabled
	 * @dataProvider data_confirm_box_check
	 */
	public function test_confirm_box_check(
		string|int $actual_user_id,
		string $actual_session_id,
		string $actual_confirm_key,
		int $req_user_id,
		string $req_session_id,
		string $req_confirm_key,
		bool $expected
	): void
	{
		global $user, $db, $request, $language, $phpbb_root_path, $phpEx;

		$_POST = [];

		$language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$user = new \phpbb\user($language, '\phpbb\datetime');
		$user->data['user_id'] = $actual_user_id;
		$user->data['user_last_confirm_key'] = $actual_confirm_key;
		$user->session_id = $actual_session_id;

		$request = new phpbb_mock_request(
			[],
			[
				'confirm' => $language->lang('YES'),
				'confirm_uid' => $req_user_id,
				'sess' => $req_session_id,
				'confirm_key' => $req_confirm_key,
			]
		);

		$db = $this->createMock(\phpbb\db\driver\driver_interface::class);
		$db->expects($expected ? $this->once() : $this->never())
			->method('sql_query');

		$this->assertSame($expected, confirm_box(true));
	}
}
