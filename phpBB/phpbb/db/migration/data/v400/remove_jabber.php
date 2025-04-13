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

class remove_jabber extends migration
{
	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\dev',
			'\phpbb\db\migration\data\v400\add_webpush',
		];
	}

	public function update_schema(): array
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'users' => [
					'user_jabber',
				],
			]
		];
	}

	public function effectively_installed()
	{
		return true;
	}

	public function revert_schema(): array
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'users' => [
					'user_jabber' => ['VCHAR_UNI', ''],
				],
			]
		];
	}

	public function update_data(): array
	{
		return [
			['config.remove', ['jab_verify_peer']],
			['config.remove', ['jab_verify_peer_name']],
			['config.remove', ['jab_allow_self_signed']],
		];
	}

	public function revert_data(): array
	{
		return [
			['config.add', ['jab_verify_peer', 1]],
			['config.add', ['jab_verify_peer_name', 1]],
			['config.add', ['jab_allow_self_signed', 0]],
			['module.add', [
				'acp',
				'ACP_CLIENT_COMMUNICATION',
				[
					'module_basename'	=> 'acp_board',
					'module_langname'	=> 'ACP_WEBPUSH_SETTINGS',
					'module_mode'		=> 'webpush',
					'module_auth'		=> 'acl_a_board',
					'after'				=> ['settings', 'ACP_JABBER_SETTINGS'],
				],
			]],
		];
	}
}
