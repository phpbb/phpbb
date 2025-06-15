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

namespace phpbb\db\migration\data\v400;

use phpbb\db\migration\migration;

class rename_auth_role_id_index extends migration
{
	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v400\dev',
		];
	}

	public function update_schema()
	{
		return [
			'drop_keys' => [
				$this->table_prefix . 'acl_users' => [
					'auth_role_id',
				],
			],
			'add_index'	=> [
				$this->table_prefix . 'acl_users' => [
					'usr_auth_role_id'	=> ['auth_role_id'],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_keys' => [
				$this->table_prefix . 'acl_users' => [
					'usr_auth_role_id',
				],
			],
			'add_index'	=> [
				$this->table_prefix . 'acl_users' => [
					'auth_role_id'	=> ['auth_role_id'],
				],
			],
		];
	}
}
