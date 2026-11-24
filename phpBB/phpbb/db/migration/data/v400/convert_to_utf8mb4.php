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

class convert_to_utf8mb4 extends migration
{
	public function effectively_installed()
	{
		return $this->db->get_sql_layer() !== 'mysqli';
	}

	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\v400a1',
			'\phpbb\db\migration\data\v400\convert_to_utf8mb4_check',
		];
	}

	public function update_schema(): array
	{
		/** Doctrine DBAL doesn't let to set primary key length, limit varchar columns length instead.
		 *  Shortened columns (former -> new, in characters) so their primary keys fit the
		 *  767-byte InnoDB index limit under utf8mb4 (4 bytes per character):
		 *   config.config_name        VARCHAR(255) -> VARCHAR(191)
		 *   config_text.config_name   VARCHAR(255) -> VARCHAR(191)
		 *   migrations.migration_name VARCHAR(255) -> VARCHAR(191)
		 *   oauth_accounts.provider   VARCHAR(255) -> VARCHAR(189) (PK also holds bigint user_id)
		 */
		return [
			'change_columns' => [
				$this->table_prefix . 'config' => [
					'config_name'	=> ['VCHAR:191', ''],
				],
				$this->table_prefix . 'config_text' => [
					'config_name'	=> ['VCHAR:191', ''],
				],
				$this->table_prefix . 'migrations' => [
					'migration_name'	=> ['VCHAR:191', ''],
				],
				$this->table_prefix . 'oauth_accounts' => [
					'provider'	=> ['VCHAR:189', ''], // Limit to 189 as primary key is composed with bigint unsigned (8 bytes)
				],
			],
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
			'add_unique_index'	=> [
				$this->table_prefix . 'ext'		=> [
					'ext_name'	=> ['ext_name(191)'],
				],
				$this->table_prefix . 'notification_types'		=> [
					'type'	=> ['notification_type_name(191)'],
				],
				$this->table_prefix . 'search_wordlist'		=> [
					'wrd_txt'	=> ['word_text(191)'],
				],
				$this->table_prefix . 'storage'		=> [
					'uidx_storage'	=> ['file_path(95)', 'storage(95)'],
				],
				$this->table_prefix . 'styles'		=> [
					'style_name'	=> ['style_name(191)'],
				],
				$this->table_prefix . 'users'		=> [
					'username_clean'	=> ['username_clean(191)'],
				],
			],
			'add_index'	=> [
				$this->table_prefix . 'groups'		=> [
					'group_legend_name'	=> ['group_legend', 'group_name(190)'],
				],
				$this->table_prefix . 'login_attempts'		=> [
					'att_for'	=> ['attempt_forwarded_for(190)', 'attempt_time'],
				],
				$this->table_prefix . 'oauth_states'		=> [
					'provider'	=> ['provider(191)'],
				],
				$this->table_prefix . 'oauth_tokens'		=> [
					'provider'	=> ['provider(191)'],
				],
				$this->table_prefix . 'posts'		=> [
					'post_username'	=> ['post_username(191)'],
				],
			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'change_columns' => [
				$this->table_prefix . 'config' => [
					'config_name'	=> ['VCHAR:255', ''],
				],
				$this->table_prefix . 'config_text' => [
					'config_name'	=> ['VCHAR:255', ''],
				],
				$this->table_prefix . 'migrations' => [
					'migration_name'	=> ['VCHAR:255', ''],
				],
				$this->table_prefix . 'oauth_accounts' => [
					'provider'	=> ['VCHAR:255', ''],
				],
			],
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

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'convert_tables_charset_collation'], ['utf8mb4']]],
			['custom', [[$this, 'change_database_default_charset_collation'], ['utf8mb4']]],
		];
	}

	public function revert_data(): array
	{
		return [
			['custom', [[$this, 'convert_tables_charset_collation'], ['utf8mb3']]],
			['custom', [[$this, 'change_database_default_charset_collation'], ['utf8mb3']]],
		];
	}

	public function convert_tables_charset_collation($charset = 'utf8mb4')
	{
		$existing_tables = array_keys($this->db_tools->sql_list_tables());
		foreach ($existing_tables as $table_name)
		{
			$sql = 'ALTER TABLE ' . $this->db->sql_quote($table_name) . " CONVERT TO CHARACTER SET $charset COLLATE {$charset}_bin";
			$this->db->sql_query($sql);
		}
	}

	public function change_database_default_charset_collation($charset = 'utf8mb4')
	{
		$phpbb_config_php_file = new \phpbb\config_php_file($this->phpbb_root_path, $this->php_ext);
		$dbname = $phpbb_config_php_file->get('dbname');
		$this->db->sql_query('ALTER DATABASE ' . $this->db->sql_quote($dbname) . " CHARACTER SET $charset COLLATE {$charset}_bin");
	}
}
