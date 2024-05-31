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

namespace phpbb\db\migration\data\v33x;

use phpbb\db\migration\migration;

class add_resend_activation_expiration extends migration
{
	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v33x\v3311',
		];
	}

	public function update_schema(): array
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'users' => [
					'user_actkey_expiration'	=> ['TIMESTAMP', 0, 'after' => 'user_actkey'],
				],
			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'users' => [
					'user_actkey_expiration',
				],
			],
		];
	}
}
