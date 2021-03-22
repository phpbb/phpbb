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

/**
* optional base class for search plugins providing simple caching based on ACM
* and functions to retrieve ignore_words and synonyms
*/
abstract class base implements search_backend_interface
{
	public const SEARCH_RESULT_NOT_IN_CACHE = 0;
	public const SEARCH_RESULT_IN_CACHE = 1;
	public const SEARCH_RESULT_INCOMPLETE = 2;

	/**
	 * Retrieves cached search results
	 *
	 * @param string $search_key an md5 string generated from all the passed search options to identify the results
	 * @param int    &$result_count will contain the number of all results for the search (not only for the current page)
	 * @param array    &$id_ary is filled with the ids belonging to the requested page that are stored in the cache
	 * @param int    &$start indicates the first index of the page
	 * @param int $per_page number of ids each page is supposed to contain
	 * @param string $sort_dir is either a or d representing ASC and DESC
	 *
	 * @return int self::SEARCH_RESULT_NOT_IN_CACHE or self::SEARCH_RESULT_IN_CACHE or self::SEARCH_RESULT_INCOMPLETE
	 */
	protected function obtain_ids(string $search_key, &$result_count, &$id_ary, &$start, $per_page, string $sort_dir): int
	{
		global $cache;

		if (!($stored_ids = $cache->get('_search_results_' . $search_key)))
		{
			// no search results cached for this search_key
			return self::SEARCH_RESULT_NOT_IN_CACHE;
		}
		else
		{
			$result_count = $stored_ids[-1];
			$reverse_ids = ($stored_ids[-2] != $sort_dir) ? true : false;
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
			// from the dirction in the cache and reverse the ids later
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
	 * @param array    &$id_ary contains a list of post or topic ids that shall be cached, the first element
	 *    must have the absolute index $start in the result set.
	 * @param int $start indicates the first index of the page
	 * @param string $sort_dir is either a or d representing ASC and DESC
	 *
	 * @return null
	 */
	protected function save_ids(string $search_key, string $keywords, $author_ary, int $result_count, &$id_ary, int $start, string $sort_dir)
	{
		global $cache, $config, $db, $user;

		$length = min(count($id_ary), $config['search_block_size']);

		// nothing to cache so exit
		if (!$length)
		{
			return;
		}

		$store_ids = array_slice($id_ary, 0, $length);

		// create a new resultset if there is none for this search_key yet
		// or add the ids to the existing resultset
		if (!($store = $cache->get('_search_results_' . $search_key)))
		{
			// add the current keywords to the recent searches in the cache which are listed on the search page
			if (!empty($keywords) || count($author_ary))
			{
				$sql = 'SELECT search_time
					FROM ' . SEARCH_RESULTS_TABLE . '
					WHERE search_key = \'' . $db->sql_escape($search_key) . '\'';
				$result = $db->sql_query($sql);

				if (!$db->sql_fetchrow($result))
				{
					$sql_ary = array(
						'search_key'		=> $search_key,
						'search_time'		=> time(),
						'search_keywords'	=> $keywords,
						'search_authors'	=> ' ' . implode(' ', $author_ary) . ' '
					);

					$sql = 'INSERT INTO ' . SEARCH_RESULTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
					$db->sql_query($sql);
				}
				$db->sql_freeresult($result);
			}

			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_last_search = ' . time() . '
				WHERE user_id = ' . $user->data['user_id'];
			$db->sql_query($sql);

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
			if (count($store) - 2 > 20 * $config['search_block_size'])
			{
				// remove everything in front of two blocks in front of the current start index
				for ($i = 0, $n = $id_range[0] - 2 * $config['search_block_size']; $i < $n; $i++)
				{
					if (isset($store[$i]))
					{
						unset($store[$i]);
					}
				}

				// remove everything after two blocks after the current stop index
				end($id_range);
				for ($i = $store[-1] - 1, $n = current($id_range) + 2 * $config['search_block_size']; $i > $n; $i--)
				{
					if (isset($store[$i]))
					{
						unset($store[$i]);
					}
				}
			}
			$cache->put('_search_results_' . $search_key, $store, $config['search_store_results']);

			$sql = 'UPDATE ' . SEARCH_RESULTS_TABLE . '
				SET search_time = ' . time() . '
				WHERE search_key = \'' . $db->sql_escape($search_key) . '\'';
			$db->sql_query($sql);
		}

		unset($store, $store_ids, $id_range);
	}

	/**
	 * Removes old entries from the search results table and removes searches with keywords that contain a word in $words.
	 *
	 * @param array $words
	 * @param array|bool $authors
	 */
	protected function destroy_cache($words, $authors = false): void
	{
		global $db, $cache, $config;

		// clear all searches that searched for the specified words
		if (count($words))
		{
			$sql_where = '';
			foreach ($words as $word)
			{
				$sql_where .= " OR search_keywords " . $db->sql_like_expression($db->get_any_char() . $word . $db->get_any_char());
			}

			$sql = 'SELECT search_key
				FROM ' . SEARCH_RESULTS_TABLE . "
				WHERE search_keywords LIKE '%*%' $sql_where";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$cache->destroy('_search_results_' . $row['search_key']);
			}
			$db->sql_freeresult($result);
		}

		// clear all searches that searched for the specified authors
		if (is_array($authors) && count($authors))
		{
			$sql_where = '';
			foreach ($authors as $author)
			{
				$sql_where .= (($sql_where) ? ' OR ' : '') . 'search_authors ' . $db->sql_like_expression($db->get_any_char() . ' ' . (int) $author . ' ' . $db->get_any_char());
			}

			$sql = 'SELECT search_key
				FROM ' . SEARCH_RESULTS_TABLE . "
				WHERE $sql_where";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$cache->destroy('_search_results_' . $row['search_key']);
			}
			$db->sql_freeresult($result);
		}

		$sql = 'DELETE
			FROM ' . SEARCH_RESULTS_TABLE . '
			WHERE search_time < ' . (time() - (int) $config['search_store_results']);
		$db->sql_query($sql);
	}

	/**
	 * {@inheritdoc}
	 */
	public function create_index($acp_module, $u_action)
	{
		$sql = 'SELECT forum_id, enable_indexing
			FROM ' . FORUMS_TABLE;
		$result = $this->db->sql_query($sql, 3600);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$forums[$row['forum_id']] = (bool) $row['enable_indexing'];
		}
		$this->db->sql_freeresult($result);

		$starttime = microtime(true);
		$row_count = 0;

		$post_counter = &$acp_module->state[2];
		while (still_on_time() && $post_counter <= $acp_module->max_post_id)
		{
			$sql = 'SELECT post_id, post_subject, post_text, poster_id, forum_id
				FROM ' . POSTS_TABLE . '
				WHERE post_id >= ' . (int) ($post_counter + 1) . '
					AND post_id <= ' . (int) ($post_counter + $acp_module->batch_size);
			$result = $this->db->sql_query($sql);

			$buffer = $this->db->sql_buffer_nested_transactions();

			if ($buffer)
			{
				$rows = $this->db->sql_fetchrowset($result);
				$rows[] = false; // indicate end of array for while loop below

				$this->db->sql_freeresult($result);
			}

			$i = 0;
			while ($row = ($buffer ? $rows[$i++] : $this->db->sql_fetchrow($result)))
			{
				// Indexing enabled for this forum
				if (isset($forums[$row['forum_id']]) && $forums[$row['forum_id']])
				{
					$this->index('post', $row['post_id'], $row['post_text'], $row['post_subject'], $row['poster_id'], $row['forum_id']);
				}
				$row_count++;
			}
			if (!$buffer)
			{
				$this->db->sql_freeresult($result);
			}

			$post_counter += $acp_module->batch_size;
		}

		// save the current state
		$acp_module->save_state();

		// pretend the number of posts was as big as the number of ids we indexed so far
		// just an estimation as it includes deleted posts
		$num_posts = $this->config['num_posts'];
		$this->config['num_posts'] = min($this->config['num_posts'], $post_counter);
		$this->tidy();
		$this->config['num_posts'] = $num_posts;

		if ($post_counter <= $acp_module->max_post_id)
		{
			$totaltime = microtime(true) - $starttime;
			$rows_per_second = $row_count / $totaltime;
			meta_refresh(1, $u_action);
			trigger_error($this->user->lang('SEARCH_INDEX_CREATE_REDIRECT', (int) $row_count, $post_counter) . $this->user->lang('SEARCH_INDEX_CREATE_REDIRECT_RATE', $rows_per_second));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete_index($acp_module, $u_action)
	{
		$starttime = microtime(true);
		$row_count = 0;
		$post_counter = &$acp_module->state[2];
		while (still_on_time() && $post_counter <= $acp_module->max_post_id)
		{
			$sql = 'SELECT post_id, poster_id, forum_id
				FROM ' . POSTS_TABLE . '
				WHERE post_id >= ' . (int) ($post_counter + 1) . '
					AND post_id <= ' . (int) ($post_counter + $acp_module->batch_size);
			$result = $this->db->sql_query($sql);

			$ids = $posters = $forum_ids = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$ids[] = $row['post_id'];
				$posters[] = $row['poster_id'];
				$forum_ids[] = $row['forum_id'];
			}
			$result->sql_freeresult($result);
			$row_count += count($ids);

			if (count($ids))
			{
				$this->index_remove($ids, $posters, $forum_ids);
			}

			$post_counter += $acp_module->batch_size;
		}

		// save the current state
		$acp_module->save_state();

		if ($post_counter <= $acp_module->max_post_id)
		{
			$totaltime = microtime(true) - $starttime;
			$rows_per_second = $row_count / $totaltime;
			meta_refresh(1, append_sid($u_action));
			trigger_error($this->user->lang('SEARCH_INDEX_DELETE_REDIRECT', (int) $row_count, $post_counter) . $this->user->lang('SEARCH_INDEX_DELETE_REDIRECT_RATE', $rows_per_second));
		}
	}
}
