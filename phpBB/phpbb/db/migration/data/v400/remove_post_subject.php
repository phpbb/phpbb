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

/**
 * Migration to remove individual post subjects.
 * Posts will now use the topic title instead of having separate subjects.
 */
class remove_post_subject extends migration
{
	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\dev',
			'\phpbb\db\migration\data\v30x\release_3_0_2_rc2',
			'\phpbb\db\migration\data\v310\dev',
		];
	}

	public function effectively_installed(): bool
	{
		return !$this->db_tools->sql_column_exists($this->table_prefix . 'posts', 'post_subject');
	}

	public function update_schema(): array
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'posts' => [
					'post_subject',
				],
				$this->table_prefix . 'topics' => [
					'topic_last_post_subject',
				],
				$this->table_prefix . 'forums' => [
					'forum_last_post_subject',
				],
			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'posts' => [
					'post_subject' => ['STEXT_UNI', '', 'after' => 'post_time'],
				],
				$this->table_prefix . 'topics' => [
					'topic_last_post_subject' => ['STEXT_UNI', '', 'after' => 'topic_last_post_id'],
				],
				$this->table_prefix . 'forums' => [
					'forum_last_post_subject' => ['STEXT_UNI', '', 'after' => 'forum_last_post_id'],
				],
			],
		];
	}

	public function update_data(): array
	{
		return [
			['config.remove', ['display_last_subject']],
		];
	}
}
