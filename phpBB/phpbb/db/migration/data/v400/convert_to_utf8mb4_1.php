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

class convert_to_utf8mb4_1 extends migration
{
	public function effectively_installed()
	{
		return $this->db->get_sql_layer() !== 'mysqli';
	}

	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\v400a1',
		];
	}

	public function update_schema(): array
	{
		return [
			'drop_keys' => [
				$this->table_prefix . 'ext' => [
					'ext_name',
				],
				$this->table_prefix . 'notification_types' => [
					'type',
				],
				$this->table_prefix . 'search_wordlist' => [
					'wrd_txt',
				],
				$this->table_prefix . 'storage' => [
					'uidx_storage',
				],
				$this->table_prefix . 'styles' => [
					'style_name',
				],
				$this->table_prefix . 'users' => [
					'username_clean',
				],
				$this->table_prefix . 'groups' => [
					'group_legend_name',
				],
				$this->table_prefix . 'login_attempts' => [
					'att_for',
				],
				$this->table_prefix . 'oauth_states' => [
					'provider',
				],
				$this->table_prefix . 'oauth_tokens' => [
					'provider',
				],
				$this->table_prefix . 'posts' => [
					'post_username',
				],
			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'add_unique_index'	=> [
				$this->table_prefix . 'ext'		=> [
					'ext_name'	=> ['ext_name'],
				],
				$this->table_prefix . 'notification_types'		=> [
					'type'	=> ['notification_type_name'],
				],
				$this->table_prefix . 'search_wordlist'		=> [
					'wrd_txt'	=> ['word_text'],
				],
				$this->table_prefix . 'storage'		=> [
					'uidx_storage'	=> ['file_path', 'storage'],
				],
				$this->table_prefix . 'styles'		=> [
					'style_name'	=> ['style_name'],
				],
				$this->table_prefix . 'users'		=> [
					'username_clean'	=> ['username_clean'],
				],
			],
			'add_index'	=> [
				$this->table_prefix . 'groups'		=> [
					'group_legend_name'	=> ['group_legend', 'group_name'],
				],
				$this->table_prefix . 'login_attempts'		=> [
					'att_for'	=> ['attempt_forwarded_for', 'attempt_time'],
				],
				$this->table_prefix . 'oauth_states'		=> [
					'provider'	=> ['provider'],
				],
				$this->table_prefix . 'oauth_tokens'		=> [
					'provider'	=> ['provider'],
				],
				$this->table_prefix . 'posts'		=> [
					'post_username'	=> ['post_username'],
				],
			],
		];
	}
}
