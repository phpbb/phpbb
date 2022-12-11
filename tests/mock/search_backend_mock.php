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

use phpbb\search\backend\search_backend_interface;

class search_backend_mock implements search_backend_interface
{
	public $index_created = false;

	public function get_name(): string
	{
		return 'Mock search backend';
	}

	public function is_available(): bool
	{
		return true;
	}

	public function init()
	{
		return false;
	}

	public function get_search_query(): string
	{
		return '';
	}

	public function get_common_words(): array
	{
		return [];
	}

	public function get_word_length()
	{
		return false;
	}

	public function split_keywords(string &$keywords, string $terms): bool
	{
		return false;
	}

	public function keyword_search(string $type, string $fields, string $terms, array $sort_by_sql, string $sort_key, string $sort_dir, string $sort_days, array $ex_fid_ary, string $post_visibility, int $topic_id, array $author_ary, string $author_name, array &$id_ary, int &$start, int $per_page)
	{
		return 0;
	}

	public function author_search(string $type, bool $firstpost_only, array $sort_by_sql, string $sort_key, string $sort_dir, string $sort_days, array $ex_fid_ary, string $post_visibility, int $topic_id, array $author_ary, string $author_name, array &$id_ary, int &$start, int $per_page)
	{
		return 0;
	}

	public function supports_phrase_search(): bool
	{
		return false;
	}

	public function index(string $mode, int $post_id, string &$message, string &$subject, int $poster_id, int $forum_id)
	{
		// Nothing
	}

	public function index_remove(array $post_ids, array $author_ids, array $forum_ids): void
	{
		// Nothing
	}

	public function tidy(): void
	{
		// Nothing
	}

	public function create_index(int &$post_counter = 0): ?array
	{
		$this->index_created = true;
		return null;
	}

	public function delete_index(int &$post_counter = 0): ?array
	{
		$this->index_created = true;
		return null;
	}

	public function index_created(): bool
	{
		return $this->index_created;
	}

	public function index_stats()
	{
		return [];
	}

	public function get_acp_options(): array
	{
		return [];
	}

	public function get_type(): string
	{
		return static::class;
	}
}

