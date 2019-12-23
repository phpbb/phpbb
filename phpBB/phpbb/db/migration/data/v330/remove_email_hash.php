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

namespace phpbb\db\migration\data\v330;

class remove_email_hash extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return ['\phpbb\db\migration\data\v30x\release_3_0_0'];
	}

	public function update_schema()
	{
		return [
			'add_index' => [
				$this->table_prefix . 'users' => [
					'user_email' => ['user_email'],
				],
			],
			'drop_keys' => [
				$this->table_prefix . 'users' => [
					'user_email_hash',
				],
			],
			'drop_columns' => [
				$this->table_prefix . 'users' => ['user_email_hash'],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'users' => [
					'user_email_hash' => ['BINT', 0],
				],
			],
			'add_index' => [
				$this->table_prefix . 'users' => [
					'user_email_hash',
				],
			],
			'drop_keys' => [
				$this->table_prefix . 'users' => [
					'user_email' => ['user_email'],
				],
			],
		];
	}
}
