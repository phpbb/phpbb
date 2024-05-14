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

class add_webpush extends migration
{
	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\dev',
		];
	}

	public function effectively_installed(): bool
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'notification_push');
	}

	public function update_schema(): array
	{
		return [
			'add_tables'	=> [
				$this->table_prefix . 'notification_push' => [
					'COLUMNS'	=> [
						'notification_type_id'	=> ['USINT', 0],
						'item_id'				=> ['ULINT', 0],
						'item_parent_id'		=> ['ULINT', 0],
						'user_id'				=> ['ULINT', 0],
						'push_data'				=> ['MTEXT', ''],
						'notification_time'		=> ['TIMESTAMP', 0]
					],
					'PRIMARY_KEY' => ['notification_type_id', 'item_id', 'item_parent_id', 'user_id'],
				],
				$this->table_prefix . 'push_subscriptions' => [
					'COLUMNS'	=> [
						'subscription_id'	=> ['ULINT', null, 'auto_increment'],
						'user_id'			=> ['ULINT', 0],
						'endpoint'			=> ['TEXT', ''],
						'expiration_time'	=> ['TIMESTAMP', 0],
						'p256dh'			=> ['VCHAR', ''],
						'auth'				=> ['VCHAR', ''],
					],
					'PRIMARY_KEY' => ['subscription_id', 'user_id'],
				]
			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'notification_push',
				$this->table_prefix . 'push_subscriptions',
			],
		];
	}

	public function update_data(): array
	{
		return [
			['config.add', ['webpush_enable', false]],
			['config.add', ['webpush_vapid_public', '']],
			['config.add', ['webpush_vapid_private', '']],
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

	public function revert_data(): array
	{
		return [
			['config.remove', ['webpush_enable']],
			['config.remove', ['webpush_vapid_public']],
			['config.remove', ['webpush_vapid_private']],
			['module.remove', ['acp', 'ACP_CLIENT_COMMUNICATION', 'ACP_WEBPUSH_SETTINGS']]
		];
	}
}
