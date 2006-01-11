<?php
/** 
*
* @package search
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
define('SEARCH_RESULT_NOT_IN_CACHE', 2);
define('SEARCH_RESULT_IN_CACHE', 1);
define('SEARCH_RESULT_INCOMPLETE', 2);

/**
* @package search
* search_backend
* optional base class for search plugins providing simple caching based on ACM
* and functions to retrieve ignore_words and synonyms
*/
class search_backend
{
	var $ignore_words = array();
	var $match_synonym = array();
	var $replace_synonym = array();
	var $split_words = array();
	var $common_words = array();

	function search_backend(&$error)
	{
		// This class cannot be used as a search plugin
		$error = true;
	}

	/**
	* Stores a list of common words that should be ignored in $this->ignore_words and caches them
	*/
	function get_ignore_words()
	{
		if (!sizeof($this->ignore_words))
		{
			global $user, $cache;

			$ignore_words = $cache->get('_ignore_words');

			if (!$ignore_words)
			{
				$ignore_words = array();
			}

			if (!isset($ignore_words[$user->lang_name]))
			{
				$ignore_words[$user->lang_name] = explode("\n", str_replace("\n\n", "\n", str_replace("\r", "\n", file_get_contents($user->lang_path . '/search_ignore_words.txt'))));

				$cache->put('_ignore_words', $ignore_words, 7200);
			}

			$this->ignore_words = $ignore_words[$user->lang_name];

			unset($ignore_words);
		}
	}

	/**
	* Stores a list of synonyms that should be replaced in $this->match_synonym and $this->replace_synonym and caches them
	*/
	function get_synonyms()
	{
		if (!sizeof($this->match_synonym))
		{
			global $user, $cache;

			$match_synonym = $cache->get('_match_synonym');

			if (!$match_synonym)
			{
				$match_synonym = array();
			}

			if (!isset($match_synonym[$user->lang_name]))
			{
				preg_match_all('#^\s+(\S+)\s+(\S+)\s+$#m', file_get_contents($user->lang_path . '/search_synonyms.txt'), $match);
				$match_synonym[$user->lang_name]['replace']= &$match[1];
				$match_synonym[$user->lang_name]['match'] = &$match[2];

				$cache->put('_match_synonym', $match_synonym, 7200);
			}

			$this->replace_synonym = $match_synonym[$user->lang_name]['replace'];
			$this->match_synonym = $match_synonym[$user->lang_name]['match'];

			unset($match_synonym);
		}
	}

	/**
	* Retrieves cached search results
	*
	* @param int result_count will contain the number of all results for the search (not only for the current page)
	* @param array id_ary is filled with the ids belonging to the requested page that are stored in the cache
	*
	* @return SEARCH_RESULT_NOT_IN_CACHE or SEARCH_RESULT_IN_CACHE or SEARCH_RESULT_INCOMPLETE
	*/
	function obtain_ids($search_key, &$result_count, &$id_ary, $start, $per_page, $sort_dir)
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

			// change the start to the actual end of the current request if the sort direction differs
			// from the dirction in the cache and reverse the ids later
			if ($reverse_ids)
			{
				$start = $result_count - $start - $per_page;

				// the user requested a page past the last index
				if ($start < 0)
				{
					return SEARCH_RESULT_NOT_IN_CACHE;
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
				return SEARCH_RESULT_INCOMPLETE;
			}
			return SEARCH_RESULT_IN_CACHE;
		}
	}

	/**
	* Caches post/topic ids
	*
	* @param array id_ary contains a list of post or topic ids that shall be cached, the first element
	* 	must have the absolute index $start in the result set.
	*/
	function save_ids($search_key, $keywords, $author_ary, $result_count, &$id_ary, $start, $sort_dir)
	{
		global $cache, $config, $db;

		$length = min(sizeof($id_ary), $config['search_block_size']);

		$store_ids = array_slice($id_ary, 0, $length);

		// create a new resultset if there is none for this search_key yet
		// or add the ids to the existing resultset
		if (!($store = $cache->get('_search_results_' . $search_key)))
		{
			// add the current keywords to the recent searches in the cache which are listed on the search page
			if (!empty($keywords) || sizeof($author_ary))
			{
				$sql = 'SELECT search_time
					FROM ' . SEARCH_TABLE . '
					WHERE search_key = \'' . $db->sql_escape($search_key) . '\'';
				$result = $db->sql_query($sql);

				if (!$db->sql_fetchrow($result))
				{
					$sql_ary = array(
						'search_key'		=> $search_key,
						'search_time'		=> time(),
						'search_keywords'	=> $keywords,
						'search_authors'	=> implode(' ', $author_ary)
					);

					$sql = 'INSERT INTO ' . SEARCH_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
					$db->sql_query($sql);
				}
				$db->sql_freeresult($result);
			}
			set_config('last_search_time', time());

			$store = array(-1 => $result_count, -2 => $sort_dir);
			$id_range = range($start, $start + $length - 1);
		}
		else
		{
			// we use one set of resuts for both sort directions so we have to calculate the indizes
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

		// append the ids
		$store += array_combine($id_range, $store_ids);
		$cache->put('_search_results_' . $search_key, $store, $config['search_store_results']);

		unset($store);
		unset($store_ids);
		unset($id_range);
	}

	/**
	* Removes old entries from the search results table and removes searches with keywords that contain a word in $words.
	*/
	function destroy_cache($words)
	{
		global $db, $cache, $config;

		if (sizeof($words))
		{
			$sql_where = '';
			foreach ($words as $word)
			{
				$sql_where .= ' OR search_keywords LIKE \'%' . $db->sql_escape($word) . '%\'';
			}

			$sql = 'SELECT search_key
				FROM ' . SEARCH_TABLE . "
				WHERE search_keywords LIKE '%*%' $sql_where";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$cache->destroy('_search_results_' . $row['search_key']);
			}
			$db->sql_freeresult();
		}

		$sql = 'DELETE
			FROM ' . SEARCH_TABLE . '
			WHERE search_time < ' . (time() - $config['search_store_results']);
		$db->sql_query($sql);
	}
}

?>