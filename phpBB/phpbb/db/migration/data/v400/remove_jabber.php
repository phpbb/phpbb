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
			'\phpbb\db\migration\data\v31x\add_jabber_ssl_context_config_options',
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
			['config.remove', ['jab_enable']],
			['config.remove', ['jab_host']],
			['config.remove', ['jab_package_size']],
			['config.remove', ['jab_password']],
			['config.remove', ['jab_port']],
			['config.remove', ['jab_use_ssl']],
			['config.remove', ['jab_username']],
			['config.remove', ['jab_verify_peer']],
			['config.remove', ['jab_verify_peer_name']],
			['config.remove', ['jab_allow_self_signed']],
			['module.remove', [
				'acp',
				'ACP_CLIENT_COMMUNICATION',
				'ACP_JABBER_SETTINGS',
			]],
			['permission.remove', ['a_jabber']],
			['permission.remove', ['u_sendim']],
		];
	}

	public function revert_data(): array
	{
		return [
			['config.add', ['jab_enable', 0]],
			['config.add', ['jab_host', '']],
			['config.add', ['jab_package_size', 20]],
			['config.add', ['jab_password', '']],
			['config.add', ['jab_port', 5222]],
			['config.add', ['jab_use_ssl', 0]],
			['config.add', ['jab_username', '']],
			['config.add', ['jab_verify_peer', 1]],
			['config.add', ['jab_verify_peer_name', 1]],
			['config.add', ['jab_allow_self_signed', 0]],
			['module.add', [
				'acp',
				'ACP_CLIENT_COMMUNICATION',
				[
					'module_basename'	=> 'acp_jabber',
					'module_langname'	=> 'ACP_JABBER_SETTINGS',
					'module_mode'		=> 'settings',
					'module_auth'		=> 'acl_a_jabber',
				],
			]],
			['permission.add', ['a_jabber', true]],
			['permission.add', ['u_sendim', true]],
		];
	}
}
