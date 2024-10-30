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

namespace phpbb\search;

/**
* @ignore
*/
define('SEARCH_RESULT_NOT_IN_CACHE', 0);
define('SEARCH_RESULT_IN_CACHE', 1);
define('SEARCH_RESULT_INCOMPLETE', 2);

/**
* optional base class for search plugins providing simple caching based on ACM
* and functions to retrieve ignore_words and synonyms
*/
class base
{
	var $ignore_words = array();
	var $match_synonym = array();
	var $replace_synonym = array();

	function search_backend(&$error)
	{
		// This class cannot be used as a search plugin
		$error = true;
	}

	/**
	* Retrieves cached search results
	*
	* @param string $search_key		an md5 string generated from all the passed search options to identify the results
	* @param int	&$result_count	will contain the number of all results for the search (not only for the current page)
	* @param array 	&$id_ary 		is filled with the ids belonging to the requested page that are stored in the cache
	* @param int 	&$start			indicates the first index of the page
	* @param int 	$per_page		number of ids each page is supposed to contain
	* @param string $sort_dir		is either a or d representing ASC and DESC
	*
	* @return int SEARCH_RESULT_NOT_IN_CACHE or SEARCH_RESULT_IN_CACHE or SEARCH_RESULT_INCOMPLETE
	*/
	function obtain_ids($search_key, &$result_count, &$id_ary, &$start, $per_page, $sort_dir)
	{
		global $cache;

		if (!($stored_ids = $cache->get('_search_results_' . $search_key)))
		{
			// no search results cached for this search_key
			return SEARCH_RESULT_NOT_IN_CACHE;
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

			// If the sort direction differs from the direction in the cache, then reverse the ids array
			if ($reverse_ids)
			{
				$stored_ids = array_reverse($stored_ids);
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

			if (!$complete)
			{
				return SEARCH_RESULT_INCOMPLETE;
			}
			return SEARCH_RESULT_IN_CACHE;
		}
	}

	/**
	* Caches post/topic ids
	*
	* @param string $search_key		an md5 string generated from all the passed search options to identify the results
	* @param string $keywords 		contains the keywords as entered by the user
	* @param array	$author_ary		an array of author ids, if the author should be ignored during the search the array is empty
	* @param int 	$result_count	contains the number of all results for the search (not only for the current page)
	* @param array	&$id_ary 		contains a list of post or topic ids that shall be cached, the first element
	* 	must have the absolute index $start in the result set.
	* @param int	$start			indicates the first index of the page
	* @param string $sort_dir		is either a or d representing ASC and DESC
	*
	* @return null
	*/
	function save_ids($search_key, $keywords, $author_ary, $result_count, &$id_ary, $start, $sort_dir)
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

		unset($store);
		unset($store_ids);
		unset($id_range);
	}

	/**
	* Removes old entries from the search results table and removes searches with keywords that contain a word in $words.
	*/
	function destroy_cache($words, $authors = false)
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
}
