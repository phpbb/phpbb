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

namespace phpbb\search\backend;

use phpbb\cache\service;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\user;

/**
 * optional base class for search plugins providing simple caching based on ACM
 * and functions to retrieve ignore_words and synonyms
 */
abstract class base implements search_backend_interface
{
	public const SEARCH_RESULT_NOT_IN_CACHE = 0;
	public const SEARCH_RESULT_IN_CACHE = 1;
	public const SEARCH_RESULT_INCOMPLETE = 2;

	// Batch size for create_index and delete_index
	private const BATCH_SIZE = 100;

	/**
	 * @var service
	 */
	protected $cache;

	/**
	 * @var config
	 */
	protected $config;

	/**
	 * @var driver_interface
	 */
	protected $db;

	/**
	 * @var user
	 */
	protected $user;

	/**
	 * @var string
	 */
	protected $search_results_table;

	/**
	 * Constructor.
	 *
	 * @param service			$cache
	 * @param config			$config
	 * @param driver_interface	$db
	 * @param user				$user
	 * @param string			$search_results_table
	 */
	public function __construct(service $cache, config $config, driver_interface $db, user $user, string $search_results_table)
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
		$this->search_results_table = $search_results_table;
	}

	/**
	 * Retrieves cached search results
	 *
	 * @param string $search_key an md5 string generated from all the passed search options to identify the results
	 * @param int &$result_count will contain the number of all results for the search (not only for the current page)
	 * @param array &$id_ary is filled with the ids belonging to the requested page that are stored in the cache
	 * @param int &$start indicates the first index of the page
	 * @param int $per_page number of ids each page is supposed to contain
	 * @param string $sort_dir is either a or d representing ASC and DESC
	 *
	 * @return int self::SEARCH_RESULT_NOT_IN_CACHE or self::SEARCH_RESULT_IN_CACHE or self::SEARCH_RESULT_INCOMPLETE
	 */
	protected function obtain_ids(string $search_key, int &$result_count, array &$id_ary, int &$start, int $per_page, string $sort_dir): int
	{
		if (!($stored_ids = $this->cache->get('_search_results_' . $search_key)))
		{
			// no search results cached for this search_key
			return self::SEARCH_RESULT_NOT_IN_CACHE;
		}
		else
		{
			$result_count = $stored_ids[-1];
			$reverse_ids = $stored_ids[-2] != $sort_dir;
			$complete = true;

			// Change start parameter in case out of bounds
			if ($result_count)
			{
				if ($start < 0)
				{
					$start = 0;
				}
				else if ($start >= $result_count)
				{
					$start = floor(($result_count - 1) / $per_page) * $per_page;
				}
			}

			// change the start to the actual end of the current request if the sort direction differs
			// from the direction in the cache and reverse the ids later
			if ($reverse_ids)
			{
				$start = $result_count - $start - $per_page;

				// the user requested a page past the last index
				if ($start < 0)
				{
					return self::SEARCH_RESULT_NOT_IN_CACHE;
				}
			}

			for ($i = $start, $n = $start + $per_page; ($i < $n) && ($i < $result_count); $i++)
			{
				if (!isset($stored_ids[$i]))
				{
					$complete = false;
				}
				else
				{
					$id_ary[] = $stored_ids[$i];
				}
			}
			unset($stored_ids);

			if ($reverse_ids)
			{
				$id_ary = array_reverse($id_ary);
			}

			if (!$complete)
			{
				return self::SEARCH_RESULT_INCOMPLETE;
			}
			return self::SEARCH_RESULT_IN_CACHE;
		}
	}

	/**
	 * Caches post/topic ids
	 *
	 * @param string $search_key an md5 string generated from all the passed search options to identify the results
	 * @param string $keywords contains the keywords as entered by the user
	 * @param array $author_ary an array of author ids, if the author should be ignored during the search the array is empty
	 * @param int $result_count contains the number of all results for the search (not only for the current page)
	 * @param array &$id_ary contains a list of post or topic ids that shall be cached, the first element
	 *    must have the absolute index $start in the result set.
	 * @param int $start indicates the first index of the page
	 * @param string $sort_dir is either a or d representing ASC and DESC
	 *
	 * @return void
	 */
	protected function save_ids(string $search_key, string $keywords, array $author_ary, int $result_count, array &$id_ary, int $start, string $sort_dir): void
	{
		global $user;

		$length = min(count($id_ary), $this->config['search_block_size']);

		// nothing to cache so exit
		if (!$length)
		{
			return;
		}

		$store_ids = array_slice($id_ary, 0, $length);

		// create a new resultset if there is none for this search_key yet
		// or add the ids to the existing resultset
		if (!($store = $this->cache->get('_search_results_' . $search_key)))
		{
			// add the current keywords to the recent searches in the cache which are listed on the search page
			if (!empty($keywords) || count($author_ary))
			{
				$sql = 'SELECT search_time
					FROM ' . $this->search_results_table . '
					WHERE search_key = \'' . $this->db->sql_escape($search_key) . '\'';
				$result = $this->db->sql_query($sql);

				if (!$this->db->sql_fetchrow($result))
				{
					$sql_ary = array(
						'search_key'		=> $search_key,
						'search_time'		=> time(),
						'search_keywords'	=> $keywords,
						'search_authors'	=> ' ' . implode(' ', $author_ary) . ' '
					);

					$sql = 'INSERT INTO ' . $this->search_results_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
					$this->db->sql_query($sql);
				}
				$this->db->sql_freeresult($result);
			}

			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_last_search = ' . time() . '
				WHERE user_id = ' . $user->data['user_id'];
			$this->db->sql_query($sql);

			$store = array(-1 => $result_count, -2 => $sort_dir);
			$id_range = range($start, $start + $length - 1);
		}
		else
		{
			// we use one set of results for both sort directions so we have to calculate the indizes
			// for the reversed array and we also have to reverse the ids themselves
			if ($store[-2] != $sort_dir)
			{
				$store_ids = array_reverse($store_ids);
				$id_range = range($store[-1] - $start - $length, $store[-1] - $start - 1);
			}
			else
			{
				$id_range = range($start, $start + $length - 1);
			}
		}

		$store_ids = array_combine($id_range, $store_ids);

		// append the ids
		if (is_array($store_ids))
		{
			$store += $store_ids;

			// if the cache is too big
			if (count($store) - 2 > 20 * $this->config['search_block_size'])
			{
				// remove everything in front of two blocks in front of the current start index
				for ($i = 0, $n = $id_range[0] - 2 * $this->config['search_block_size']; $i < $n; $i++)
				{
					if (isset($store[$i]))
					{
						unset($store[$i]);
					}
				}

				// remove everything after two blocks after the current stop index
				end($id_range);
				for ($i = $store[-1] - 1, $n = current($id_range) + 2 * $this->config['search_block_size']; $i > $n; $i--)
				{
					if (isset($store[$i]))
					{
						unset($store[$i]);
					}
				}
			}
			$this->cache->put('_search_results_' . $search_key, $store, $this->config['search_store_results']);

			$sql = 'UPDATE ' . $this->search_results_table . '
				SET search_time = ' . time() . '
				WHERE search_key = \'' . $this->db->sql_escape($search_key) . '\'';
			$this->db->sql_query($sql);
		}

		unset($store, $store_ids, $id_range);
	}

	/**
	 * Removes old entries from the search results table and removes searches with keywords that contain a word in $words.
	 *
	 * @param array $words
	 * @param array|bool $authors
	 */
	protected function destroy_cache(array $words, $authors = false): void
	{
		// clear all searches that searched for the specified words
		if (count($words))
		{
			$sql_where = '';
			foreach ($words as $word)
			{
				$sql_where .= " OR search_keywords " . $this->db->sql_like_expression($this->db->get_any_char() . $word . $this->db->get_any_char());
			}

			$sql = 'SELECT search_key
				FROM ' . $this->search_results_table . "
				WHERE search_keywords LIKE '%*%' $sql_where";
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->cache->destroy('_search_results_' . $row['search_key']);
			}
			$this->db->sql_freeresult($result);
		}

		// clear all searches that searched for the specified authors
		if (is_array($authors) && count($authors))
		{
			$sql_where = '';
			foreach ($authors as $author)
			{
				$sql_where .= (($sql_where) ? ' OR ' : '') . 'search_authors ' . $this->db->sql_like_expression($this->db->get_any_char() . ' ' . (int) $author . ' ' . $this->db->get_any_char());
			}

			$sql = 'SELECT search_key
				FROM ' . $this->search_results_table . "
				WHERE $sql_where";
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->cache->destroy('_search_results_' . $row['search_key']);
			}
			$this->db->sql_freeresult($result);
		}

		$sql = 'DELETE
			FROM ' . $this->search_results_table . '
			WHERE search_time < ' . (time() - (int) $this->config['search_store_results']);
		$this->db->sql_query($sql);
	}

	/**
	 * {@inheritdoc}
	 */
	public function create_index(int &$post_counter = 0): ?array
	{
		$max_post_id = $this->get_max_post_id();
		$forums_indexing_enabled = $this->forum_ids_with_indexing_enabled();

		$starttime = microtime(true);
		$row_count = 0;

		while (still_on_time() && $post_counter < $max_post_id)
		{
			$rows = $this->get_posts_batch_after($post_counter);

			if ($this->db->sql_buffer_nested_transactions())
			{
				$rows = iterator_to_array($rows);
			}

			foreach ($rows as $row)
			{
				// Indexing enabled for this forum
				if (in_array($row['forum_id'], $forums_indexing_enabled, true))
				{
					$this->index('post', (int) $row['post_id'], $row['post_text'], $row['post_subject'], (int) $row['poster_id'], (int) $row['forum_id']);
				}
				$row_count++;
				$post_counter = (int) $row['post_id'];
			}

			// With cli process only one batch each time to be able to track progress
			if (PHP_SAPI === 'cli')
			{
				break;
			}
		}

		// pretend the number of posts was as big as the number of ids we indexed so far
		// just an estimation as it includes deleted posts
		$num_posts = $this->config['num_posts'];
		$this->config['num_posts'] = min($this->config['num_posts'], $post_counter);
		$this->tidy();
		$this->config['num_posts'] = $num_posts;

		if ($post_counter < $max_post_id) // If there are still post to index
		{
			$totaltime = microtime(true) - $starttime;
			$rows_per_second = $row_count / $totaltime;

			return [
				'row_count' => $row_count,
				'post_counter' => $post_counter,
				'max_post_id' => $max_post_id,
				'rows_per_second' => $rows_per_second,
			];
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete_index(int &$post_counter = null): ?array
	{
		$max_post_id = $this->get_max_post_id();

		$starttime = microtime(true);
		$row_count = 0;

		while (still_on_time() && $post_counter < $max_post_id)
		{
			$rows = $this->get_posts_batch_after($post_counter);
			$ids = $posters = $forum_ids = array();
			foreach ($rows as $row)
			{
				$ids[] = $row['post_id'];
				$posters[] = $row['poster_id'];
				$forum_ids[] = $row['forum_id'];
			}
			$row_count += count($ids);

			if (count($ids))
			{
				$this->index_remove($ids, $posters, $forum_ids);
				$post_counter = $ids[count($ids) - 1];
			}

			// With cli process only one batch each time to be able to track progress
			if (PHP_SAPI === 'cli')
			{
				break;
			}
		}

		if ($post_counter < $max_post_id) // If there are still post delete from index
		{
			$totaltime = microtime(true) - $starttime;
			$rows_per_second = $row_count / $totaltime;

			return [
				'row_count' => $row_count,
				'post_counter' => $post_counter,
				'max_post_id' => $max_post_id,
				'rows_per_second' => $rows_per_second,
			];
		}

		return null;
	}

	/**
	 * Return the ids of the forums that have indexing enabled
	 *
	 * @return array
	 */
	protected function forum_ids_with_indexing_enabled(): array
	{
		$forums = [];

		$sql = 'SELECT forum_id, enable_indexing
			FROM ' . FORUMS_TABLE;
		$result = $this->db->sql_query($sql, 3600);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ((bool) $row['enable_indexing'])
			{
				$forums[] = $row['forum_id'];
			}
		}
		$this->db->sql_freeresult($result);

		return $forums;
	}

	/**
	 * Get batch of posts after id
	 *
	 * @param int $post_id
	 * @return \Generator
	 */
	protected function get_posts_batch_after(int $post_id): \Generator
	{
		$sql = 'SELECT post_id, post_subject, post_text, poster_id, forum_id
				FROM ' . POSTS_TABLE . '
				WHERE post_id > ' . (int) $post_id . '
				ORDER BY post_id ASC';
		$result = $this->db->sql_query_limit($sql, self::BATCH_SIZE);

		while ($row = $this->db->sql_fetchrow($result))
		{
			yield $row;
		}

		$this->db->sql_freeresult($result);
	}

	/**
	 * Get post with higher id
	 */
	protected function get_max_post_id(): int
	{
		$sql = 'SELECT MAX(post_id) as max_post_id
			FROM '. POSTS_TABLE;
		$result = $this->db->sql_query($sql);
		$max_post_id = (int) $this->db->sql_fetchfield('max_post_id');
		$this->db->sql_freeresult($result);

		return $max_post_id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type(): string
	{
		return static::class;
	}
}
