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

/**
*/
class phpbb_mock_search implements \phpbb\search\backend\search_backend_interface
{

	public function __construct($auth, $config, $db, $phpbb_dispatcher, $user, $phpbb_root_path, $phpEx)
	{
	}

	public function get_name()
	{
	}

	public function get_search_query()
	{
	}

	public function get_common_words()
	{
	}

	public function get_word_length()
	{
	}

	public function init()
	{
	}

	public function split_keywords(&$keywords, $terms)
	{
	}

	public function keyword_search($type, $fields, $terms, $sort_by_sql, $sort_key, $sort_dir, $sort_days, $ex_fid_ary, $post_visibility, $topic_id, $author_ary, $author_name, &$id_ary, &$start, $per_page)
	{
	}

	public function author_search($type, $firstpost_only, $sort_by_sql, $sort_key, $sort_dir, $sort_days, $ex_fid_ary, $post_visibility, $topic_id, $author_ary, $author_name, &$id_ary, &$start, $per_page)
	{
	}

	public function index($mode, $post_id, &$message, &$subject, $poster_id, $forum_id)
	{
	}

	public function index_remove($post_ids, $author_ids, $forum_ids)
	{
	}

	public function tidy()
	{
	}

	public function create_index($acp_module, $u_action)
	{
	}

	public function delete_index($acp_module, $u_action)
	{
	}

	public function index_created()
	{
	}

	public function index_stats()
	{
	}

	protected function get_stats()
	{
	}

	public function acp()
	{
	}
}
