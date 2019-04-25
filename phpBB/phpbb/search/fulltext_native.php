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
* phpBB's own db driven fulltext search, version 2
*/
class fulltext_native extends \phpbb\search\base
{
	const UTF8_HANGUL_FIRST = "\xEA\xB0\x80";
	const UTF8_HANGUL_LAST = "\xED\x9E\xA3";
	const UTF8_CJK_FIRST = "\xE4\xB8\x80";
	const UTF8_CJK_LAST = "\xE9\xBE\xBB";
	const UTF8_CJK_B_FIRST = "\xF0\xA0\x80\x80";
	const UTF8_CJK_B_LAST = "\xF0\xAA\x9B\x96";

	/**
	 * Associative array holding index stats
	 * @var array
	 */
	protected $stats = array();

	/**
	 * Associative array stores the min and max word length to be searched
	 * @var array
	 */
	protected $word_length = array();

	/**
	 * Contains tidied search query.
	 * Operators are prefixed in search query and common words excluded
	 * @var string
	 */
	protected $search_query;

	/**
	 * Contains common words.
	 * Common words are words with length less/more than min/max length
	 * @var array
	 */
	protected $common_words = array();

	/**
	 * Post ids of posts containing words that are to be included
	 * @var array
	 */
	protected $must_contain_ids = array();

	/**
	 * Post ids of posts containing words that should not be included
	 * @var array
	 */
	protected $must_not_contain_ids = array();

	/**
	 * Post ids of posts containing at least one word that needs to be excluded
	 * @var array
	 */
	protected $must_exclude_one_ids = array();

	/**
	 * Relative path to board root
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * PHP Extension
	 * @var string
	 */
	protected $php_ext;

	/**
	 * Config object
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * Database connection
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * phpBB event dispatcher object
	 * @var \phpbb\event\dispatcher_interface
	 */
	protected $phpbb_dispatcher;

	/**
	 * User object
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	* Initialises the fulltext_native search backend with min/max word length
	*
	* @param	boolean|string	&$error	is passed by reference and should either be set to false on success or an error message on failure
	* @param	\phpbb\event\dispatcher_interface	$phpbb_dispatcher	Event dispatcher object
	*/
	public function __construct(&$error, $phpbb_root_path, $phpEx, $auth, $config, $db, $user, $phpbb_dispatcher)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
		$this->config = $config;
		$this->db = $db;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->user = $user;

		$this->word_length = array('min' => (int) $this->config['fulltext_native_min_chars'], 'max' => (int) $this->config['fulltext_native_max_chars']);

		/**
		* Load the UTF tools
		*/
		if (!function_exists('utf8_decode_ncr'))
		{
			include($this->phpbb_root_path . 'includes/utf/utf_tools.' . $this->php_ext);
		}

		$error = false;
	}

	/**
	* Returns the name of this search backend to be displayed to administrators
	*
	* @return string Name
	*/
	public function get_name()
	{
		return 'phpBB Native Fulltext';
	}

	/**
	 * Returns the search_query
	 *
	 * @return string search query
	 */
	public function get_search_query()
	{
		return $this->search_query;
	}

	/**
	 * Returns the common_words array
	 *
	 * @return array common words that are ignored by search backend
	 */
	public function get_common_words()
	{
		return $this->common_words;
	}

	/**
	 * Returns the word_length array
	 *
	 * @return array min and max word length for searching
	 */
	public function get_word_length()
	{
		return $this->word_length;
	}

	/**
	* This function fills $this->search_query with the cleaned user search query
	*
	* If $terms is 'any' then the words will be extracted from the search query
	* and combined with | inside brackets. They will afterwards be treated like
	* an standard search query.
	*
	* Then it analyses the query and fills the internal arrays $must_not_contain_ids,
	* $must_contain_ids and $must_exclude_one_ids which are later used by keyword_search()
	*
	* @param	string	$keywords	contains the search query string as entered by the user
	* @param	string	$terms		is either 'all' (use search query as entered, default words to 'must be contained in post')
	* 	or 'any' (find all posts containing at least one of the given words)
	* @return	boolean				false if no valid keywords were found and otherwise true
	*/
	public function split_keywords($keywords, $terms)
	{
		$tokens = '+-|()* ';

		$keywords = trim($this->cleanup($keywords, $tokens));

		// allow word|word|word without brackets
		if ((strpos($keywords, ' ') === false) && (strpos($keywords, '|') !== false) && (strpos($keywords, '(') === false))
		{
			$keywords = '(' . $keywords . ')';
		}

		$open_bracket = $space = false;
		for ($i = 0, $n = strlen($keywords); $i < $n; $i++)
		{
			if ($open_bracket !== false)
			{
				switch ($keywords[$i])
				{
					case ')':
						if ($open_bracket + 1 == $i)
						{
							$keywords[$i - 1] = '|';
							$keywords[$i] = '|';
						}
						$open_bracket = false;
					break;
					case '(':
						$keywords[$i] = '|';
					break;
					case '+':
					case '-':
					case ' ':
						$keywords[$i] = '|';
					break;
					case '*':
						// $i can never be 0 here since $open_bracket is initialised to false
						if (strpos($tokens, $keywords[$i - 1]) !== false && ($i + 1 === $n || strpos($tokens, $keywords[$i + 1]) !== false))
						{
							$keywords[$i] = '|';
						}
					break;
				}
			}
			else
			{
				switch ($keywords[$i])
				{
					case ')':
						$keywords[$i] = ' ';
					break;
					case '(':
						$open_bracket = $i;
						$space = false;
					break;
					case '|':
						$keywords[$i] = ' ';
					break;
					case '-':
					case '+':
						$space = $keywords[$i];
					break;
					case ' ':
						if ($space !== false)
						{
							$keywords[$i] = $space;
						}
					break;
					default:
						$space = false;
				}
			}
		}

		if ($open_bracket !== false)
		{
			$keywords .= ')';
		}

		$match = array(
			'#  +#',
			'#\|\|+#',
			'#(\+|\-)(?:\+|\-)+#',
			'#\(\|#',
			'#\|\)#',
		);
		$replace = array(
			' ',
			'|',
			'$1',
			'(',
			')',
		);

		$keywords = preg_replace($match, $replace, $keywords);
		$num_keywords = count(explode(' ', $keywords));

		// We limit the number of allowed keywords to minimize load on the database
		if ($this->config['max_num_search_keywords'] && $num_keywords > $this->config['max_num_search_keywords'])
		{
			trigger_error($this->user->lang('MAX_NUM_SEARCH_KEYWORDS_REFINE', (int) $this->config['max_num_search_keywords'], $num_keywords));
		}

		// $keywords input format: each word separated by a space, words in a bracket are not separated

		// the user wants to search for any word, convert the search query
		if ($terms == 'any')
		{
			$words = array();

			preg_match_all('#([^\\s+\\-|()]+)(?:$|[\\s+\\-|()])#u', $keywords, $words);
			if (count($words[1]))
			{
				$keywords = '(' . implode('|', $words[1]) . ')';
			}
		}

		// Remove non trailing wildcards from each word to prevent a full table scan (it's now using the database index)
		$match = '#\*(?!$|\s)#';
		$replace = '$1';
		$keywords = preg_replace($match, $replace, $keywords);

		// Only allow one wildcard in the search query to limit the database load
		$match = '#\*#';
		$replace = '$1';
		$count_wildcards = substr_count($keywords, '*');

		// Reverse the string to remove all wildcards except the first one
		$keywords = strrev(preg_replace($match, $replace, strrev($keywords), $count_wildcards - 1));
		unset($count_wildcards);

		// set the search_query which is shown to the user
		$this->search_query = $keywords;

		$exact_words = array();
		preg_match_all('#([^\\s+\\-|()]+)(?:$|[\\s+\\-|()])#u', $keywords, $exact_words);
		$exact_words = $exact_words[1];

		$common_ids = $words = array();

		if (count($exact_words))
		{
			$sql = 'SELECT word_id, word_text, word_common
				FROM ' . SEARCH_WORDLIST_TABLE . '
				WHERE ' . $this->db->sql_in_set('word_text', $exact_words) . '
				ORDER BY word_count ASC';
			$result = $this->db->sql_query($sql);

			// store an array of words and ids, remove common words
			while ($row = $this->db->sql_fetchrow($result))
			{
				if ($row['word_common'])
				{
					$this->common_words[] = $row['word_text'];
					$common_ids[$row['word_text']] = (int) $row['word_id'];
					continue;
				}

				$words[$row['word_text']] = (int) $row['word_id'];
			}
			$this->db->sql_freeresult($result);
		}

		// Handle +, - without preceeding whitespace character
		$match		= array('#(\S)\+#', '#(\S)-#');
		$replace	= array('$1 +', '$1 +');

		$keywords = preg_replace($match, $replace, $keywords);

		// now analyse the search query, first split it using the spaces
		$query = explode(' ', $keywords);

		$this->must_contain_ids = array();
		$this->must_not_contain_ids = array();
		$this->must_exclude_one_ids = array();

		foreach ($query as $word)
		{
			if (empty($word))
			{
				continue;
			}

			// words which should not be included
			if ($word[0] == '-')
			{
				$word = substr($word, 1);

				// a group of which at least one may not be in the resulting posts
				if ($word[0] == '(')
				{
					$word = array_unique(explode('|', substr($word, 1, -1)));
					$mode = 'must_exclude_one';
				}
				// one word which should not be in the resulting posts
				else
				{
					$mode = 'must_not_contain';
				}
				$ignore_no_id = true;
			}
			// words which have to be included
			else
			{
				// no prefix is the same as a +prefix
				if ($word[0] == '+')
				{
					$word = substr($word, 1);
				}

				// a group of words of which at least one word should be in every resulting post
				if ($word[0] == '(')
				{
					$word = array_unique(explode('|', substr($word, 1, -1)));
				}
				$ignore_no_id = false;
				$mode = 'must_contain';
			}

			if (empty($word))
			{
				continue;
			}

			// if this is an array of words then retrieve an id for each
			if (is_array($word))
			{
				$non_common_words = array();
				$id_words = array();
				foreach ($word as $i => $word_part)
				{
					if (strpos($word_part, '*') !== false)
					{
						$len = utf8_strlen(str_replace('*', '', $word_part));
						if ($len >= $this->word_length['min'] && $len <= $this->word_length['max'])
						{
							$id_words[] = '\'' . $this->db->sql_escape(str_replace('*', '%', $word_part)) . '\'';
							$non_common_words[] = $word_part;
						}
						else
						{
							$this->common_words[] = $word_part;
						}
					}
					else if (isset($words[$word_part]))
					{
						$id_words[] = $words[$word_part];
						$non_common_words[] = $word_part;
					}
					else
					{
						$len = utf8_strlen($word_part);
						if ($len < $this->word_length['min'] || $len > $this->word_length['max'])
						{
							$this->common_words[] = $word_part;
						}
					}
				}
				if (count($id_words))
				{
					sort($id_words);
					if (count($id_words) > 1)
					{
						$this->{$mode . '_ids'}[] = $id_words;
					}
					else
					{
						$mode = ($mode == 'must_exclude_one') ? 'must_not_contain' : $mode;
						$this->{$mode . '_ids'}[] = $id_words[0];
					}
				}
				// throw an error if we shall not ignore unexistant words
				else if (!$ignore_no_id && count($non_common_words))
				{
					trigger_error(sprintf($this->user->lang['WORDS_IN_NO_POST'], implode($this->user->lang['COMMA_SEPARATOR'], $non_common_words)));
				}
				unset($non_common_words);
			}
			// else we only need one id
			else if (($wildcard = strpos($word, '*') !== false) || isset($words[$word]))
			{
				if ($wildcard)
				{
					$len = utf8_strlen(str_replace('*', '', $word));
					if ($len >= $this->word_length['min'] && $len <= $this->word_length['max'])
					{
						$this->{$mode . '_ids'}[] = '\'' . $this->db->sql_escape(str_replace('*', '%', $word)) . '\'';
					}
					else
					{
						$this->common_words[] = $word;
					}
				}
				else
				{
					$this->{$mode . '_ids'}[] = $words[$word];
				}
			}
			else
			{
				if (!isset($common_ids[$word]))
				{
					$len = utf8_strlen($word);
					if ($len < $this->word_length['min'] || $len > $this->word_length['max'])
					{
						$this->common_words[] = $word;
					}
				}
			}
		}

		// Return true if all words are not common words
		if (count($exact_words) - count($this->common_words) > 0)
		{
			return true;
		}
		return false;
	}

	/**
	* Performs a search on keywords depending on display specific params. You have to run split_keywords() first
	*
	* @param	string		$type				contains either posts or topics depending on what should be searched for
	* @param	string		$fields				contains either titleonly (topic titles should be searched), msgonly (only message bodies should be searched), firstpost (only subject and body of the first post should be searched) or all (all post bodies and subjects should be searched)
	* @param	string		$terms				is either 'all' (use query as entered, words without prefix should default to "have to be in field") or 'any' (ignore search query parts and just return all posts that contain any of the specified words)
	* @param	array		$sort_by_sql		contains SQL code for the ORDER BY part of a query
	* @param	string		$sort_key			is the key of $sort_by_sql for the selected sorting
	* @param	string		$sort_dir			is either a or d representing ASC and DESC
	* @param	string		$sort_days			specifies the maximum amount of days a post may be old
	* @param	array		$ex_fid_ary			specifies an array of forum ids which should not be searched
	* @param	string		$post_visibility	specifies which types of posts the user can view in which forums
	* @param	int			$topic_id			is set to 0 or a topic id, if it is not 0 then only posts in this topic should be searched
	* @param	array		$author_ary			an array of author ids if the author should be ignored during the search the array is empty
	* @param	string		$author_name		specifies the author match, when ANONYMOUS is also a search-match
	* @param	array		&$id_ary			passed by reference, to be filled with ids for the page specified by $start and $per_page, should be ordered
	* @param	int			$start				indicates the first index of the page
	* @param	int			$per_page			number of ids each page is supposed to contain
	* @return	boolean|int						total number of results
	*/
	public function keyword_search($type, $fields, $terms, $sort_by_sql, $sort_key, $sort_dir, $sort_days, $ex_fid_ary, $post_visibility, $topic_id, $author_ary, $author_name, &$id_ary, &$start, $per_page)
	{
		// No keywords? No posts.
		if (empty($this->search_query))
		{
			return false;
		}

		// we can't search for negatives only
		if (empty($this->must_contain_ids))
		{
			return false;
		}

		$must_contain_ids = $this->must_contain_ids;
		$must_not_contain_ids = $this->must_not_contain_ids;
		$must_exclude_one_ids = $this->must_exclude_one_ids;

		sort($must_contain_ids);
		sort($must_not_contain_ids);
		sort($must_exclude_one_ids);

		// generate a search_key from all the options to identify the results
		$search_key_array = array(
			serialize($must_contain_ids),
			serialize($must_not_contain_ids),
			serialize($must_exclude_one_ids),
			$type,
			$fields,
			$terms,
			$sort_days,
			$sort_key,
			$topic_id,
			implode(',', $ex_fid_ary),
			$post_visibility,
			implode(',', $author_ary),
			$author_name,
		);

		/**
		* Allow changing the search_key for cached results
		*
		* @event core.search_native_by_keyword_modify_search_key
		* @var	array	search_key_array	Array with search parameters to generate the search_key
		* @var	array	must_contain_ids	Array with post ids of posts containing words that are to be included
		* @var	array	must_not_contain_ids	Array with post ids of posts containing words that should not be included
		* @var	array	must_exclude_one_ids	Array with post ids of posts containing at least one word that needs to be excluded
		* @var	string	type				Searching type ('posts', 'topics')
		* @var	string	fields				Searching fields ('titleonly', 'msgonly', 'firstpost', 'all')
		* @var	string	terms				Searching terms ('all', 'any')
		* @var	int		sort_days			Time, in days, of the oldest possible post to list
		* @var	string	sort_key			The sort type used from the possible sort types
		* @var	int		topic_id			Limit the search to this topic_id only
		* @var	array	ex_fid_ary			Which forums not to search on
		* @var	string	post_visibility		Post visibility data
		* @var	array	author_ary			Array of user_id containing the users to filter the results to
		* @since 3.1.7-RC1
		*/
		$vars = array(
			'search_key_array',
			'must_contain_ids',
			'must_not_contain_ids',
			'must_exclude_one_ids',
			'type',
			'fields',
			'terms',
			'sort_days',
			'sort_key',
			'topic_id',
			'ex_fid_ary',
			'post_visibility',
			'author_ary',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.search_native_by_keyword_modify_search_key', compact($vars)));

		$search_key = md5(implode('#', $search_key_array));

		// try reading the results from cache
		$total_results = 0;
		if ($this->obtain_ids($search_key, $total_results, $id_ary, $start, $per_page, $sort_dir) == SEARCH_RESULT_IN_CACHE)
		{
			return $total_results;
		}

		$id_ary = array();

		$sql_where = array();
		$m_num = 0;
		$w_num = 0;

		$sql_array = array(
			'SELECT'	=> ($type == 'posts') ? 'p.post_id' : 'p.topic_id',
			'FROM'		=> array(
				SEARCH_WORDMATCH_TABLE	=> array(),
				SEARCH_WORDLIST_TABLE	=> array(),
			),
			'LEFT_JOIN' => array(array(
				'FROM'	=> array(POSTS_TABLE => 'p'),
				'ON'	=> 'm0.post_id = p.post_id',
			)),
		);

		$title_match = '';
		$left_join_topics = false;
		$group_by = true;
		// Build some display specific sql strings
		switch ($fields)
		{
			case 'titleonly':
				$title_match = 'title_match = 1';
				$group_by = false;
			// no break
			case 'firstpost':
				$left_join_topics = true;
				$sql_where[] = 'p.post_id = t.topic_first_post_id';
			break;

			case 'msgonly':
				$title_match = 'title_match = 0';
				$group_by = false;
			break;
		}

		if ($type == 'topics')
		{
			$left_join_topics = true;
			$group_by = true;
		}

		/**
		* @todo Add a query optimizer (handle stuff like "+(4|3) +4")
		*/

		foreach ($this->must_contain_ids as $subquery)
		{
			if (is_array($subquery))
			{
				$group_by = true;

				$word_id_sql = array();
				$word_ids = array();
				foreach ($subquery as $id)
				{
					if (is_string($id))
					{
						$sql_array['LEFT_JOIN'][] = array(
							'FROM'	=> array(SEARCH_WORDLIST_TABLE => 'w' . $w_num),
							'ON'	=> "w$w_num.word_text LIKE $id"
						);
						$word_ids[] = "w$w_num.word_id";

						$w_num++;
					}
					else
					{
						$word_ids[] = $id;
					}
				}

				$sql_where[] = $this->db->sql_in_set("m$m_num.word_id", $word_ids);

				unset($word_id_sql);
				unset($word_ids);
			}
			else if (is_string($subquery))
			{
				$sql_array['FROM'][SEARCH_WORDLIST_TABLE][] = 'w' . $w_num;

				$sql_where[] = "w$w_num.word_text LIKE $subquery";
				$sql_where[] = "m$m_num.word_id = w$w_num.word_id";

				$group_by = true;
				$w_num++;
			}
			else
			{
				$sql_where[] = "m$m_num.word_id = $subquery";
			}

			$sql_array['FROM'][SEARCH_WORDMATCH_TABLE][] = 'm' . $m_num;

			if ($title_match)
			{
				$sql_where[] = "m$m_num.$title_match";
			}

			if ($m_num != 0)
			{
				$sql_where[] = "m$m_num.post_id = m0.post_id";
			}
			$m_num++;
		}

		foreach ($this->must_not_contain_ids as $key => $subquery)
		{
			if (is_string($subquery))
			{
				$sql_array['LEFT_JOIN'][] = array(
					'FROM'	=> array(SEARCH_WORDLIST_TABLE => 'w' . $w_num),
					'ON'	=> "w$w_num.word_text LIKE $subquery"
				);

				$this->must_not_contain_ids[$key] = "w$w_num.word_id";

				$group_by = true;
				$w_num++;
			}
		}

		if (count($this->must_not_contain_ids))
		{
			$sql_array['LEFT_JOIN'][] = array(
				'FROM'	=> array(SEARCH_WORDMATCH_TABLE => 'm' . $m_num),
				'ON'	=> $this->db->sql_in_set("m$m_num.word_id", $this->must_not_contain_ids) . (($title_match) ? " AND m$m_num.$title_match" : '') . " AND m$m_num.post_id = m0.post_id"
			);

			$sql_where[] = "m$m_num.word_id IS NULL";
			$m_num++;
		}

		foreach ($this->must_exclude_one_ids as $ids)
		{
			$is_null_joins = array();
			foreach ($ids as $id)
			{
				if (is_string($id))
				{
					$sql_array['LEFT_JOIN'][] = array(
						'FROM'	=> array(SEARCH_WORDLIST_TABLE => 'w' . $w_num),
						'ON'	=> "w$w_num.word_text LIKE $id"
					);
					$id = "w$w_num.word_id";

					$group_by = true;
					$w_num++;
				}

				$sql_array['LEFT_JOIN'][] = array(
					'FROM'	=> array(SEARCH_WORDMATCH_TABLE => 'm' . $m_num),
					'ON'	=> "m$m_num.word_id = $id AND m$m_num.post_id = m0.post_id" . (($title_match) ? " AND m$m_num.$title_match" : '')
				);
				$is_null_joins[] = "m$m_num.word_id IS NULL";

				$m_num++;
			}
			$sql_where[] = '(' . implode(' OR ', $is_null_joins) . ')';
		}

		$sql_where[] = $post_visibility;

		$search_query = $this->search_query;
		$must_exclude_one_ids = $this->must_exclude_one_ids;
		$must_not_contain_ids = $this->must_not_contain_ids;
		$must_contain_ids = $this->must_contain_ids;

		/**
		* Allow changing the query used for counting for posts using fulltext_native
		*
		* @event core.search_native_keywords_count_query_before
		* @var	string	search_query			The parsed keywords used for this search
		* @var	array	must_not_contain_ids	Ids that cannot be taken into account for the results
		* @var	array	must_exclude_one_ids	Ids that cannot be on the results
		* @var	array	must_contain_ids		Ids that must be on the results
		* @var	int		total_results			The previous result count for the format of the query
		*										Set to 0 to force a re-count
		* @var	array	sql_array				The data on how to search in the DB at this point
		* @var	bool	left_join_topics		Whether or not TOPICS_TABLE should be CROSS JOIN'ED
		* @var	array	author_ary				Array of user_id containing the users to filter the results to
		* @var	string	author_name				An extra username to search on (!empty(author_ary) must be true, to be relevant)
		* @var	array	ex_fid_ary				Which forums not to search on
		* @var	int		topic_id				Limit the search to this topic_id only
		* @var	string	sql_sort_table			Extra tables to include in the SQL query.
		*										Used in conjunction with sql_sort_join
		* @var	string	sql_sort_join			SQL conditions to join all the tables used together.
		*										Used in conjunction with sql_sort_table
		* @var	int		sort_days				Time, in days, of the oldest possible post to list
		* @var	string	sql_where				An array of the current WHERE clause conditions
		* @var	string	sql_match				Which columns to do the search on
		* @var	string	sql_match_where			Extra conditions to use to properly filter the matching process
		* @var	bool	group_by				Whether or not the SQL query requires a GROUP BY for the elements in the SELECT clause
		* @var	string	sort_by_sql				The possible predefined sort types
		* @var	string	sort_key				The sort type used from the possible sort types
		* @var	string	sort_dir				"a" for ASC or "d" dor DESC for the sort order used
		* @var	string	sql_sort				The result SQL when processing sort_by_sql + sort_key + sort_dir
		* @var	int		start					How many posts to skip in the search results (used for pagination)
		* @since 3.1.5-RC1
		*/
		$vars = array(
			'search_query',
			'must_not_contain_ids',
			'must_exclude_one_ids',
			'must_contain_ids',
			'total_results',
			'sql_array',
			'left_join_topics',
			'author_ary',
			'author_name',
			'ex_fid_ary',
			'topic_id',
			'sql_sort_table',
			'sql_sort_join',
			'sort_days',
			'sql_where',
			'sql_match',
			'sql_match_where',
			'group_by',
			'sort_by_sql',
			'sort_key',
			'sort_dir',
			'sql_sort',
			'start',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.search_native_keywords_count_query_before', compact($vars)));

		if ($topic_id)
		{
			$sql_where[] = 'p.topic_id = ' . $topic_id;
		}

		if (count($author_ary))
		{
			if ($author_name)
			{
				// first one matches post of registered users, second one guests and deleted users
				$sql_author = '(' . $this->db->sql_in_set('p.poster_id', array_diff($author_ary, array(ANONYMOUS)), false, true) . ' OR p.post_username ' . $author_name . ')';
			}
			else
			{
				$sql_author = $this->db->sql_in_set('p.poster_id', $author_ary);
			}
			$sql_where[] = $sql_author;
		}

		if (count($ex_fid_ary))
		{
			$sql_where[] = $this->db->sql_in_set('p.forum_id', $ex_fid_ary, true);
		}

		if ($sort_days)
		{
			$sql_where[] = 'p.post_time >= ' . (time() - ($sort_days * 86400));
		}

		$sql_array['WHERE'] = implode(' AND ', $sql_where);

		$is_mysql = false;
		// if the total result count is not cached yet, retrieve it from the db
		if (!$total_results)
		{
			$sql = '';
			$sql_array_count = $sql_array;

			if ($left_join_topics)
			{
				$sql_array_count['LEFT_JOIN'][] = array(
					'FROM'	=> array(TOPICS_TABLE => 't'),
					'ON'	=> 'p.topic_id = t.topic_id'
				);
			}

			switch ($this->db->get_sql_layer())
			{
				case 'mysql4':
				case 'mysqli':

					// 3.x does not support SQL_CALC_FOUND_ROWS
					// $sql_array['SELECT'] = 'SQL_CALC_FOUND_ROWS ' . $sql_array['SELECT'];
					$is_mysql = true;

				break;

				case 'sqlite3':
					$sql_array_count['SELECT'] = ($type == 'posts') ? 'DISTINCT p.post_id' : 'DISTINCT p.topic_id';
					$sql = 'SELECT COUNT(' . (($type == 'posts') ? 'post_id' : 'topic_id') . ') as total_results
							FROM (' . $this->db->sql_build_query('SELECT', $sql_array_count) . ')';

				// no break

				default:
					$sql_array_count['SELECT'] = ($type == 'posts') ? 'COUNT(DISTINCT p.post_id) AS total_results' : 'COUNT(DISTINCT p.topic_id) AS total_results';
					$sql = (!$sql) ? $this->db->sql_build_query('SELECT', $sql_array_count) : $sql;

					$result = $this->db->sql_query($sql);
					$total_results = (int) $this->db->sql_fetchfield('total_results');
					$this->db->sql_freeresult($result);

					if (!$total_results)
					{
						return false;
					}
				break;
			}

			unset($sql_array_count, $sql);
		}

		// Build sql strings for sorting
		$sql_sort = $sort_by_sql[$sort_key] . (($sort_dir == 'a') ? ' ASC' : ' DESC');

		switch ($sql_sort[0])
		{
			case 'u':
				$sql_array['FROM'][USERS_TABLE] = 'u';
				$sql_where[] = 'u.user_id = p.poster_id ';
			break;

			case 't':
				$left_join_topics = true;
			break;

			case 'f':
				$sql_array['FROM'][FORUMS_TABLE] = 'f';
				$sql_where[] = 'f.forum_id = p.forum_id';
			break;
		}

		if ($left_join_topics)
		{
			$sql_array['LEFT_JOIN'][] = array(
				'FROM'	=> array(TOPICS_TABLE => 't'),
				'ON'	=> 'p.topic_id = t.topic_id'
			);
		}

		// if using mysql and the total result count is not calculated yet, get it from the db
		if (!$total_results && $is_mysql)
		{
			// Also count rows for the query as if there was not LIMIT. Add SQL_CALC_FOUND_ROWS to SQL
			$sql_array['SELECT'] = 'SQL_CALC_FOUND_ROWS ' . $sql_array['SELECT'];
		}

		$sql_array['WHERE'] = implode(' AND ', $sql_where);
		$sql_array['GROUP_BY'] = ($group_by) ? (($type == 'posts') ? 'p.post_id' : 'p.topic_id') . ', ' . $sort_by_sql[$sort_key] : '';
		$sql_array['ORDER_BY'] = $sql_sort;

		unset($sql_where, $sql_sort, $group_by);

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $this->config['search_block_size'], $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$id_ary[] = (int) $row[(($type == 'posts') ? 'post_id' : 'topic_id')];
		}
		$this->db->sql_freeresult($result);

		if (!$total_results && $is_mysql)
		{
			// Get the number of results as calculated by MySQL
			$sql_count = 'SELECT FOUND_ROWS() as total_results';
			$result = $this->db->sql_query($sql_count);
			$total_results = (int) $this->db->sql_fetchfield('total_results');
			$this->db->sql_freeresult($result);

			if (!$total_results)
			{
				return false;
			}
		}

		if ($start >= $total_results)
		{
			$start = floor(($total_results - 1) / $per_page) * $per_page;

			$result = $this->db->sql_query_limit($sql, $this->config['search_block_size'], $start);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$id_ary[] = (int) $row[(($type == 'posts') ? 'post_id' : 'topic_id')];
			}
			$this->db->sql_freeresult($result);

		}

		// store the ids, from start on then delete anything that isn't on the current page because we only need ids for one page
		$this->save_ids($search_key, $this->search_query, $author_ary, $total_results, $id_ary, $start, $sort_dir);
		$id_ary = array_slice($id_ary, 0, (int) $per_page);

		return $total_results;
	}

	/**
	* Performs a search on an author's posts without caring about message contents. Depends on display specific params
	*
	* @param	string		$type				contains either posts or topics depending on what should be searched for
	* @param	boolean		$firstpost_only		if true, only topic starting posts will be considered
	* @param	array		$sort_by_sql		contains SQL code for the ORDER BY part of a query
	* @param	string		$sort_key			is the key of $sort_by_sql for the selected sorting
	* @param	string		$sort_dir			is either a or d representing ASC and DESC
	* @param	string		$sort_days			specifies the maximum amount of days a post may be old
	* @param	array		$ex_fid_ary			specifies an array of forum ids which should not be searched
	* @param	string		$post_visibility	specifies which types of posts the user can view in which forums
	* @param	int			$topic_id			is set to 0 or a topic id, if it is not 0 then only posts in this topic should be searched
	* @param	array		$author_ary			an array of author ids
	* @param	string		$author_name		specifies the author match, when ANONYMOUS is also a search-match
	* @param	array		&$id_ary			passed by reference, to be filled with ids for the page specified by $start and $per_page, should be ordered
	* @param	int			$start				indicates the first index of the page
	* @param	int			$per_page			number of ids each page is supposed to contain
	* @return	boolean|int						total number of results
	*/
	public function author_search($type, $firstpost_only, $sort_by_sql, $sort_key, $sort_dir, $sort_days, $ex_fid_ary, $post_visibility, $topic_id, $author_ary, $author_name, &$id_ary, &$start, $per_page)
	{
		// No author? No posts
		if (!count($author_ary))
		{
			return 0;
		}

		// generate a search_key from all the options to identify the results
		$search_key_array = array(
			'',
			$type,
			($firstpost_only) ? 'firstpost' : '',
			'',
			'',
			$sort_days,
			$sort_key,
			$topic_id,
			implode(',', $ex_fid_ary),
			$post_visibility,
			implode(',', $author_ary),
			$author_name,
		);

		/**
		* Allow changing the search_key for cached results
		*
		* @event core.search_native_by_author_modify_search_key
		* @var	array	search_key_array	Array with search parameters to generate the search_key
		* @var	string	type				Searching type ('posts', 'topics')
		* @var	boolean	firstpost_only		Flag indicating if only topic starting posts are considered
		* @var	int		sort_days			Time, in days, of the oldest possible post to list
		* @var	string	sort_key			The sort type used from the possible sort types
		* @var	int		topic_id			Limit the search to this topic_id only
		* @var	array	ex_fid_ary			Which forums not to search on
		* @var	string	post_visibility		Post visibility data
		* @var	array	author_ary			Array of user_id containing the users to filter the results to
		* @var	string	author_name			The username to search on
		* @since 3.1.7-RC1
		*/
		$vars = array(
			'search_key_array',
			'type',
			'firstpost_only',
			'sort_days',
			'sort_key',
			'topic_id',
			'ex_fid_ary',
			'post_visibility',
			'author_ary',
			'author_name',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.search_native_by_author_modify_search_key', compact($vars)));

		$search_key = md5(implode('#', $search_key_array));

		// try reading the results from cache
		$total_results = 0;
		if ($this->obtain_ids($search_key, $total_results, $id_ary, $start, $per_page, $sort_dir) == SEARCH_RESULT_IN_CACHE)
		{
			return $total_results;
		}

		$id_ary = array();

		// Create some display specific sql strings
		if ($author_name)
		{
			// first one matches post of registered users, second one guests and deleted users
			$sql_author = '(' . $this->db->sql_in_set('p.poster_id', array_diff($author_ary, array(ANONYMOUS)), false, true) . ' OR p.post_username ' . $author_name . ')';
		}
		else
		{
			$sql_author = $this->db->sql_in_set('p.poster_id', $author_ary);
		}
		$sql_fora		= (count($ex_fid_ary)) ? ' AND ' . $this->db->sql_in_set('p.forum_id', $ex_fid_ary, true) : '';
		$sql_time		= ($sort_days) ? ' AND p.post_time >= ' . (time() - ($sort_days * 86400)) : '';
		$sql_topic_id	= ($topic_id) ? ' AND p.topic_id = ' . (int) $topic_id : '';
		$sql_firstpost = ($firstpost_only) ? ' AND p.post_id = t.topic_first_post_id' : '';
		$post_visibility = ($post_visibility) ? ' AND ' . $post_visibility : '';

		// Build sql strings for sorting
		$sql_sort = $sort_by_sql[$sort_key] . (($sort_dir == 'a') ? ' ASC' : ' DESC');
		$sql_sort_table = $sql_sort_join = '';
		switch ($sql_sort[0])
		{
			case 'u':
				$sql_sort_table	= USERS_TABLE . ' u, ';
				$sql_sort_join	= ' AND u.user_id = p.poster_id ';
			break;

			case 't':
				$sql_sort_table	= ($type == 'posts' && !$firstpost_only) ? TOPICS_TABLE . ' t, ' : '';
				$sql_sort_join	= ($type == 'posts' && !$firstpost_only) ? ' AND t.topic_id = p.topic_id ' : '';
			break;

			case 'f':
				$sql_sort_table	= FORUMS_TABLE . ' f, ';
				$sql_sort_join	= ' AND f.forum_id = p.forum_id ';
			break;
		}

		$select = ($type == 'posts') ? 'p.post_id' : 't.topic_id';
		$is_mysql = false;

		/**
		* Allow changing the query used to search for posts by author in fulltext_native
		*
		* @event core.search_native_author_count_query_before
		* @var	int		total_results		The previous result count for the format of the query.
		*									Set to 0 to force a re-count
		* @var	string	type				The type of search being made
		* @var	string	select				SQL SELECT clause for what to get
		* @var	string	sql_sort_table		CROSS JOIN'ed table to allow doing the sort chosen
		* @var	string	sql_sort_join		Condition to define how to join the CROSS JOIN'ed table specifyed in sql_sort_table
		* @var	array	sql_author			SQL WHERE condition for the post author ids
		* @var	int		topic_id			Limit the search to this topic_id only
		* @var	string	sort_by_sql			The possible predefined sort types
		* @var	string	sort_key			The sort type used from the possible sort types
		* @var	string	sort_dir			"a" for ASC or "d" dor DESC for the sort order used
		* @var	string	sql_sort			The result SQL when processing sort_by_sql + sort_key + sort_dir
		* @var	string	sort_days			Time, in days, that the oldest post showing can have
		* @var	string	sql_time			The SQL to search on the time specifyed by sort_days
		* @var	bool	firstpost_only		Wether or not to search only on the first post of the topics
		* @var	string	sql_firstpost		The SQL used in the WHERE claused to filter by firstpost.
		* @var	array	ex_fid_ary			Forum ids that must not be searched on
		* @var	array	sql_fora			SQL query for ex_fid_ary
		* @var	int		start				How many posts to skip in the search results (used for pagination)
		* @since 3.1.5-RC1
		*/
		$vars = array(
			'total_results',
			'type',
			'select',
			'sql_sort_table',
			'sql_sort_join',
			'sql_author',
			'topic_id',
			'sort_by_sql',
			'sort_key',
			'sort_dir',
			'sql_sort',
			'sort_days',
			'sql_time',
			'firstpost_only',
			'sql_firstpost',
			'ex_fid_ary',
			'sql_fora',
			'start',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.search_native_author_count_query_before', compact($vars)));

		// If the cache was completely empty count the results
		if (!$total_results)
		{
			switch ($this->db->get_sql_layer())
			{
				case 'mysql4':
				case 'mysqli':
//					$select = 'SQL_CALC_FOUND_ROWS ' . $select;
					$is_mysql = true;
				break;

				default:
					if ($type == 'posts')
					{
						$sql = 'SELECT COUNT(p.post_id) as total_results
							FROM ' . POSTS_TABLE . ' p' . (($firstpost_only) ? ', ' . TOPICS_TABLE . ' t ' : ' ') . "
							WHERE $sql_author
								$sql_topic_id
								$sql_firstpost
								$post_visibility
								$sql_fora
								$sql_time";
					}
					else
					{
						if ($this->db->get_sql_layer() == 'sqlite3')
						{
							$sql = 'SELECT COUNT(topic_id) as total_results
								FROM (SELECT DISTINCT t.topic_id';
						}
						else
						{
							$sql = 'SELECT COUNT(DISTINCT t.topic_id) as total_results';
						}

						$sql .= ' FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
							WHERE $sql_author
								$sql_topic_id
								$sql_firstpost
								$post_visibility
								$sql_fora
								AND t.topic_id = p.topic_id
								$sql_time" . ($this->db->get_sql_layer() == 'sqlite3' ? ')' : '');
					}
					$result = $this->db->sql_query($sql);

					$total_results = (int) $this->db->sql_fetchfield('total_results');
					$this->db->sql_freeresult($result);

					if (!$total_results)
					{
						return false;
					}
				break;
			}
		}

		// Build the query for really selecting the post_ids
		if ($type == 'posts')
		{
			$sql = "SELECT $select
				FROM " . $sql_sort_table . POSTS_TABLE . ' p' . (($firstpost_only) ? ', ' . TOPICS_TABLE . ' t' : '') . "
				WHERE $sql_author
					$sql_topic_id
					$sql_firstpost
					$post_visibility
					$sql_fora
					$sql_sort_join
					$sql_time
				ORDER BY $sql_sort";
			$field = 'post_id';
		}
		else
		{
			$sql = "SELECT $select
				FROM " . $sql_sort_table . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
				WHERE $sql_author
					$sql_topic_id
					$sql_firstpost
					$post_visibility
					$sql_fora
					AND t.topic_id = p.topic_id
					$sql_sort_join
					$sql_time
				GROUP BY t.topic_id, " . $sort_by_sql[$sort_key] . '
				ORDER BY ' . $sql_sort;
			$field = 'topic_id';
		}

		// Only read one block of posts from the db and then cache it
		$result = $this->db->sql_query_limit($sql, $this->config['search_block_size'], $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$id_ary[] = (int) $row[$field];
		}
		$this->db->sql_freeresult($result);

		if (!$total_results && $is_mysql)
		{
			// Count rows for the executed queries. Replace $select within $sql with SQL_CALC_FOUND_ROWS, and run it.
			$sql_calc = str_replace('SELECT ' . $select, 'SELECT SQL_CALC_FOUND_ROWS ' . $select, $sql);

			$result = $this->db->sql_query($sql_calc);
			$this->db->sql_freeresult($result);

			$sql_count = 'SELECT FOUND_ROWS() as total_results';
			$result = $this->db->sql_query($sql_count);
			$total_results = (int) $this->db->sql_fetchfield('total_results');
			$this->db->sql_freeresult($result);

			if (!$total_results)
			{
				return false;
			}
		}

		if ($start >= $total_results)
		{
			$start = floor(($total_results - 1) / $per_page) * $per_page;

			$result = $this->db->sql_query_limit($sql, $this->config['search_block_size'], $start);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$id_ary[] = (int) $row[$field];
			}
			$this->db->sql_freeresult($result);
		}

		if (count($id_ary))
		{
			$this->save_ids($search_key, '', $author_ary, $total_results, $id_ary, $start, $sort_dir);
			$id_ary = array_slice($id_ary, 0, $per_page);

			return $total_results;
		}
		return false;
	}

	/**
	* Split a text into words of a given length
	*
	* The text is converted to UTF-8, cleaned up, and split. Then, words that
	* conform to the defined length range are returned in an array.
	*
	* NOTE: duplicates are NOT removed from the return array
	*
	* @param	string	$text	Text to split, encoded in UTF-8
	* @return	array			Array of UTF-8 words
	*/
	public function split_message($text)
	{
		$match = $words = array();

		/**
		* Taken from the original code
		*/
		// Do not index code
		$match[] = '#\[code(?:=.*?)?(\:?[0-9a-z]{5,})\].*?\[\/code(\:?[0-9a-z]{5,})\]#is';
		// BBcode
		$match[] = '#\[\/?[a-z0-9\*\+\-]+(?:=.*?)?(?::[a-z])?(\:?[0-9a-z]{5,})\]#';

		$min = $this->word_length['min'];

		$isset_min = $min - 1;

		/**
		* Clean up the string, remove HTML tags, remove BBCodes
		*/
		$word = strtok($this->cleanup(preg_replace($match, ' ', strip_tags($text)), -1), ' ');

		while (strlen($word))
		{
			if (strlen($word) > 255 || strlen($word) <= $isset_min)
			{
				/**
				* Words longer than 255 bytes are ignored. This will have to be
				* changed whenever we change the length of search_wordlist.word_text
				*
				* Words shorter than $isset_min bytes are ignored, too
				*/
				$word = strtok(' ');
				continue;
			}

			$len = utf8_strlen($word);

			/**
			* Test whether the word is too short to be indexed.
			*
			* Note that this limit does NOT apply to CJK and Hangul
			*/
			if ($len < $min)
			{
				/**
				* Note: this could be optimized. If the codepoint is lower than Hangul's range
				* we know that it will also be lower than CJK ranges
				*/
				if ((strncmp($word, self::UTF8_HANGUL_FIRST, 3) < 0 || strncmp($word, self::UTF8_HANGUL_LAST, 3) > 0)
					&& (strncmp($word, self::UTF8_CJK_FIRST, 3) < 0 || strncmp($word, self::UTF8_CJK_LAST, 3) > 0)
					&& (strncmp($word, self::UTF8_CJK_B_FIRST, 4) < 0 || strncmp($word, self::UTF8_CJK_B_LAST, 4) > 0))
				{
					$word = strtok(' ');
					continue;
				}
			}

			$words[] = $word;
			$word = strtok(' ');
		}

		return $words;
	}

	/**
	* Updates wordlist and wordmatch tables when a message is posted or changed
	*
	* @param	string	$mode		Contains the post mode: edit, post, reply, quote
	* @param	int		$post_id	The id of the post which is modified/created
	* @param	string	&$message	New or updated post content
	* @param	string	&$subject	New or updated post subject
	* @param	int		$poster_id	Post author's user id
	* @param	int		$forum_id	The id of the forum in which the post is located
	*/
	public function index($mode, $post_id, &$message, &$subject, $poster_id, $forum_id)
	{
		if (!$this->config['fulltext_native_load_upd'])
		{
			/**
			* The search indexer is disabled, return
			*/
			return;
		}

		// Split old and new post/subject to obtain array of 'words'
		$split_text = $this->split_message($message);
		$split_title = $this->split_message($subject);

		$cur_words = array('post' => array(), 'title' => array());

		$words = array();
		if ($mode == 'edit')
		{
			$words['add']['post'] = array();
			$words['add']['title'] = array();
			$words['del']['post'] = array();
			$words['del']['title'] = array();

			$sql = 'SELECT w.word_id, w.word_text, m.title_match
				FROM ' . SEARCH_WORDLIST_TABLE . ' w, ' . SEARCH_WORDMATCH_TABLE . " m
				WHERE m.post_id = $post_id
					AND w.word_id = m.word_id";
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$which = ($row['title_match']) ? 'title' : 'post';
				$cur_words[$which][$row['word_text']] = $row['word_id'];
			}
			$this->db->sql_freeresult($result);

			$words['add']['post'] = array_diff($split_text, array_keys($cur_words['post']));
			$words['add']['title'] = array_diff($split_title, array_keys($cur_words['title']));
			$words['del']['post'] = array_diff(array_keys($cur_words['post']), $split_text);
			$words['del']['title'] = array_diff(array_keys($cur_words['title']), $split_title);
		}
		else
		{
			$words['add']['post'] = $split_text;
			$words['add']['title'] = $split_title;
			$words['del']['post'] = array();
			$words['del']['title'] = array();
		}

		/**
		* Event to modify method arguments and words before the native search index is updated
		*
		* @event core.search_native_index_before
		* @var string	mode				Contains the post mode: edit, post, reply, quote
		* @var int		post_id				The id of the post which is modified/created
		* @var string	message				New or updated post content
		* @var string	subject				New or updated post subject
		* @var int		poster_id			Post author's user id
		* @var int		forum_id			The id of the forum in which the post is located
		* @var array	words				Grouped lists of words added to or remove from the index
		* @var array	split_text			Array of words from the message
		* @var array	split_title			Array of words from the title
		* @var array	cur_words			Array of words currently in the index for comparing to new words
		* 									when mode is edit. Empty for other modes.
		* @since 3.2.3-RC1
		*/
		$vars = array(
			'mode',
			'post_id',
			'message',
			'subject',
			'poster_id',
			'forum_id',
			'words',
			'split_text',
			'split_title',
			'cur_words',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.search_native_index_before', compact($vars)));

		unset($split_text);
		unset($split_title);

		// Get unique words from the above arrays
		$unique_add_words = array_unique(array_merge($words['add']['post'], $words['add']['title']));

		// We now have unique arrays of all words to be added and removed and
		// individual arrays of added and removed words for text and title. What
		// we need to do now is add the new words (if they don't already exist)
		// and then add (or remove) matches between the words and this post
		if (count($unique_add_words))
		{
			$sql = 'SELECT word_id, word_text
				FROM ' . SEARCH_WORDLIST_TABLE . '
				WHERE ' . $this->db->sql_in_set('word_text', $unique_add_words);
			$result = $this->db->sql_query($sql);

			$word_ids = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$word_ids[$row['word_text']] = $row['word_id'];
			}
			$this->db->sql_freeresult($result);
			$new_words = array_diff($unique_add_words, array_keys($word_ids));

			$this->db->sql_transaction('begin');
			if (count($new_words))
			{
				$sql_ary = array();

				foreach ($new_words as $word)
				{
					$sql_ary[] = array('word_text' => (string) $word, 'word_count' => 0);
				}
				$this->db->sql_return_on_error(true);
				$this->db->sql_multi_insert(SEARCH_WORDLIST_TABLE, $sql_ary);
				$this->db->sql_return_on_error(false);
			}
			unset($new_words, $sql_ary);
		}
		else
		{
			$this->db->sql_transaction('begin');
		}

		// now update the search match table, remove links to removed words and add links to new words
		foreach ($words['del'] as $word_in => $word_ary)
		{
			$title_match = ($word_in == 'title') ? 1 : 0;

			if (count($word_ary))
			{
				$sql_in = array();
				foreach ($word_ary as $word)
				{
					$sql_in[] = $cur_words[$word_in][$word];
				}

				$sql = 'DELETE FROM ' . SEARCH_WORDMATCH_TABLE . '
					WHERE ' . $this->db->sql_in_set('word_id', $sql_in) . '
						AND post_id = ' . intval($post_id) . "
						AND title_match = $title_match";
				$this->db->sql_query($sql);

				$sql = 'UPDATE ' . SEARCH_WORDLIST_TABLE . '
					SET word_count = word_count - 1
					WHERE ' . $this->db->sql_in_set('word_id', $sql_in) . '
						AND word_count > 0';
				$this->db->sql_query($sql);

				unset($sql_in);
			}
		}

		$this->db->sql_return_on_error(true);
		foreach ($words['add'] as $word_in => $word_ary)
		{
			$title_match = ($word_in == 'title') ? 1 : 0;

			if (count($word_ary))
			{
				$sql = 'INSERT INTO ' . SEARCH_WORDMATCH_TABLE . ' (post_id, word_id, title_match)
					SELECT ' . (int) $post_id . ', word_id, ' . (int) $title_match . '
					FROM ' . SEARCH_WORDLIST_TABLE . '
					WHERE ' . $this->db->sql_in_set('word_text', $word_ary);
				$this->db->sql_query($sql);

				$sql = 'UPDATE ' . SEARCH_WORDLIST_TABLE . '
					SET word_count = word_count + 1
					WHERE ' . $this->db->sql_in_set('word_text', $word_ary);
				$this->db->sql_query($sql);
			}
		}
		$this->db->sql_return_on_error(false);

		$this->db->sql_transaction('commit');

		// destroy cached search results containing any of the words removed or added
		$this->destroy_cache(array_unique(array_merge($words['add']['post'], $words['add']['title'], $words['del']['post'], $words['del']['title'])), array($poster_id));

		unset($unique_add_words);
		unset($words);
		unset($cur_words);
	}

	/**
	* Removes entries from the wordmatch table for the specified post_ids
	*/
	public function index_remove($post_ids, $author_ids, $forum_ids)
	{
		if (count($post_ids))
		{
			$sql = 'SELECT w.word_id, w.word_text, m.title_match
				FROM ' . SEARCH_WORDMATCH_TABLE . ' m, ' . SEARCH_WORDLIST_TABLE . ' w
				WHERE ' . $this->db->sql_in_set('m.post_id', $post_ids) . '
					AND w.word_id = m.word_id';
			$result = $this->db->sql_query($sql);

			$message_word_ids = $title_word_ids = $word_texts = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				if ($row['title_match'])
				{
					$title_word_ids[] = $row['word_id'];
				}
				else
				{
					$message_word_ids[] = $row['word_id'];
				}
				$word_texts[] = $row['word_text'];
			}
			$this->db->sql_freeresult($result);

			if (count($title_word_ids))
			{
				$sql = 'UPDATE ' . SEARCH_WORDLIST_TABLE . '
					SET word_count = word_count - 1
					WHERE ' . $this->db->sql_in_set('word_id', $title_word_ids) . '
						AND word_count > 0';
				$this->db->sql_query($sql);
			}

			if (count($message_word_ids))
			{
				$sql = 'UPDATE ' . SEARCH_WORDLIST_TABLE . '
					SET word_count = word_count - 1
					WHERE ' . $this->db->sql_in_set('word_id', $message_word_ids) . '
						AND word_count > 0';
				$this->db->sql_query($sql);
			}

			unset($title_word_ids);
			unset($message_word_ids);

			$sql = 'DELETE FROM ' . SEARCH_WORDMATCH_TABLE . '
				WHERE ' . $this->db->sql_in_set('post_id', $post_ids);
			$this->db->sql_query($sql);
		}

		$this->destroy_cache(array_unique($word_texts), array_unique($author_ids));
	}

	/**
	* Tidy up indexes: Tag 'common words' and remove
	* words no longer referenced in the match table
	*/
	public function tidy()
	{
		// Is the fulltext indexer disabled? If yes then we need not
		// carry on ... it's okay ... I know when I'm not wanted boo hoo
		if (!$this->config['fulltext_native_load_upd'])
		{
			$this->config->set('search_last_gc', time(), false);
			return;
		}

		$destroy_cache_words = array();

		// Remove common words
		if ($this->config['num_posts'] >= 100 && $this->config['fulltext_native_common_thres'])
		{
			$common_threshold = ((double) $this->config['fulltext_native_common_thres']) / 100.0;
			// First, get the IDs of common words
			$sql = 'SELECT word_id, word_text
				FROM ' . SEARCH_WORDLIST_TABLE . '
				WHERE word_count > ' . floor($this->config['num_posts'] * $common_threshold) . '
					OR word_common = 1';
			$result = $this->db->sql_query($sql);

			$sql_in = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$sql_in[] = $row['word_id'];
				$destroy_cache_words[] = $row['word_text'];
			}
			$this->db->sql_freeresult($result);

			if (count($sql_in))
			{
				// Flag the words
				$sql = 'UPDATE ' . SEARCH_WORDLIST_TABLE . '
					SET word_common = 1
					WHERE ' . $this->db->sql_in_set('word_id', $sql_in);
				$this->db->sql_query($sql);

				// by setting search_last_gc to the new time here we make sure that if a user reloads because the
				// following query takes too long, he won't run into it again
				$this->config->set('search_last_gc', time(), false);

				// Delete the matches
				$sql = 'DELETE FROM ' . SEARCH_WORDMATCH_TABLE . '
					WHERE ' . $this->db->sql_in_set('word_id', $sql_in);
				$this->db->sql_query($sql);
			}
			unset($sql_in);
		}

		if (count($destroy_cache_words))
		{
			// destroy cached search results containing any of the words that are now common or were removed
			$this->destroy_cache(array_unique($destroy_cache_words));
		}

		$this->config->set('search_last_gc', time(), false);
	}

	/**
	* Deletes all words from the index
	*/
	public function delete_index($acp_module, $u_action)
	{
		$sql_queries = [];

		switch ($this->db->get_sql_layer())
		{
			case 'sqlite3':
				$sql_queries[] = 'DELETE FROM ' . SEARCH_WORDLIST_TABLE;
				$sql_queries[] = 'DELETE FROM ' . SEARCH_WORDMATCH_TABLE;
				$sql_queries[] = 'DELETE FROM ' . SEARCH_RESULTS_TABLE;
			break;

			default:
				$sql_queries[] = 'TRUNCATE TABLE ' . SEARCH_WORDLIST_TABLE;
				$sql_queries[] = 'TRUNCATE TABLE ' . SEARCH_WORDMATCH_TABLE;
				$sql_queries[] = 'TRUNCATE TABLE ' . SEARCH_RESULTS_TABLE;
			break;
		}

		$stats = $this->stats;

		/**
		* Event to modify SQL queries before the native search index is deleted
		*
		* @event core.search_native_delete_index_before
		* @var array	sql_queries			Array with queries for deleting the search index
		* @var array	stats				Array with statistics of the current index (read only)
		* @since 3.2.3-RC1
		*/
		$vars = array(
			'sql_queries',
			'stats',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.search_native_delete_index_before', compact($vars)));

		foreach ($sql_queries as $sql_query)
		{
			$this->db->sql_query($sql_query);
		}
	}

	/**
	* Returns true if both FULLTEXT indexes exist
	*/
	public function index_created()
	{
		if (!count($this->stats))
		{
			$this->get_stats();
		}

		return ($this->stats['total_words'] && $this->stats['total_matches']) ? true : false;
	}

	/**
	* Returns an associative array containing information about the indexes
	*/
	public function index_stats()
	{
		if (!count($this->stats))
		{
			$this->get_stats();
		}

		return array(
			$this->user->lang['TOTAL_WORDS']		=> $this->stats['total_words'],
			$this->user->lang['TOTAL_MATCHES']	=> $this->stats['total_matches']);
	}

	protected function get_stats()
	{
		$this->stats['total_words']		= $this->db->get_estimated_row_count(SEARCH_WORDLIST_TABLE);
		$this->stats['total_matches']	= $this->db->get_estimated_row_count(SEARCH_WORDMATCH_TABLE);
	}

	/**
	* Clean up a text to remove non-alphanumeric characters
	*
	* This method receives a UTF-8 string, normalizes and validates it, replaces all
	* non-alphanumeric characters with strings then returns the result.
	*
	* Any number of "allowed chars" can be passed as a UTF-8 string in NFC.
	*
	* @param	string	$text			Text to split, in UTF-8 (not normalized or sanitized)
	* @param	string	$allowed_chars	String of special chars to allow
	* @param	string	$encoding		Text encoding
	* @return	string					Cleaned up text, only alphanumeric chars are left
	*/
	protected function cleanup($text, $allowed_chars = null, $encoding = 'utf-8')
	{
		static $conv = array(), $conv_loaded = array();
		$allow = array();

		// Convert the text to UTF-8
		$encoding = strtolower($encoding);
		if ($encoding != 'utf-8')
		{
			$text = utf8_recode($text, $encoding);
		}

		$utf_len_mask = array(
			"\xC0"	=>	2,
			"\xD0"	=>	2,
			"\xE0"	=>	3,
			"\xF0"	=>	4
		);

		/**
		* Replace HTML entities and NCRs
		*/
		$text = htmlspecialchars_decode(utf8_decode_ncr($text), ENT_QUOTES);

		/**
		* Normalize to NFC
		*/
		$text = \Normalizer::normalize($text);

		/**
		* The first thing we do is:
		*
		* - convert ASCII-7 letters to lowercase
		* - remove the ASCII-7 non-alpha characters
		* - remove the bytes that should not appear in a valid UTF-8 string: 0xC0,
		*   0xC1 and 0xF5-0xFF
		*
		* @todo in theory, the third one is already taken care of during normalization and those chars should have been replaced by Unicode replacement chars
		*/
		$sb_match	= "ISTCPAMELRDOJBNHFGVWUQKYXZ\r\n\t!\"#$%&'()*+,-./:;<=>?@[\\]^_`{|}~\x00\x01\x02\x03\x04\x05\x06\x07\x08\x0B\x0C\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F\xC0\xC1\xF5\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF";
		$sb_replace	= 'istcpamelrdojbnhfgvwuqkyxz                                                                              ';

		/**
		* This is the list of legal ASCII chars, it is automatically extended
		* with ASCII chars from $allowed_chars
		*/
		$legal_ascii = ' eaisntroludcpmghbfvq10xy2j9kw354867z';

		/**
		* Prepare an array containing the extra chars to allow
		*/
		if (isset($allowed_chars[0]))
		{
			$pos = 0;
			$len = strlen($allowed_chars);
			do
			{
				$c = $allowed_chars[$pos];

				if ($c < "\x80")
				{
					/**
					* ASCII char
					*/
					$sb_pos = strpos($sb_match, $c);
					if (is_int($sb_pos))
					{
						/**
						* Remove the char from $sb_match and its corresponding
						* replacement in $sb_replace
						*/
						$sb_match = substr($sb_match, 0, $sb_pos) . substr($sb_match, $sb_pos + 1);
						$sb_replace = substr($sb_replace, 0, $sb_pos) . substr($sb_replace, $sb_pos + 1);
						$legal_ascii .= $c;
					}

					++$pos;
				}
				else
				{
					/**
					* UTF-8 char
					*/
					$utf_len = $utf_len_mask[$c & "\xF0"];
					$allow[substr($allowed_chars, $pos, $utf_len)] = 1;
					$pos += $utf_len;
				}
			}
			while ($pos < $len);
		}

		$text = strtr($text, $sb_match, $sb_replace);
		$ret = '';

		$pos = 0;
		$len = strlen($text);

		do
		{
			/**
			* Do all consecutive ASCII chars at once
			*/
			if ($spn = strspn($text, $legal_ascii, $pos))
			{
				$ret .= substr($text, $pos, $spn);
				$pos += $spn;
			}

			if ($pos >= $len)
			{
				return $ret;
			}

			/**
			* Capture the UTF char
			*/
			$utf_len = $utf_len_mask[$text[$pos] & "\xF0"];
			$utf_char = substr($text, $pos, $utf_len);
			$pos += $utf_len;

			if (($utf_char >= self::UTF8_HANGUL_FIRST && $utf_char <= self::UTF8_HANGUL_LAST)
				|| ($utf_char >= self::UTF8_CJK_FIRST && $utf_char <= self::UTF8_CJK_LAST)
				|| ($utf_char >= self::UTF8_CJK_B_FIRST && $utf_char <= self::UTF8_CJK_B_LAST))
			{
				/**
				* All characters within these ranges are valid
				*
				* We separate them with a space in order to index each character
				* individually
				*/
				$ret .= ' ' . $utf_char . ' ';
				continue;
			}

			if (isset($allow[$utf_char]))
			{
				/**
				* The char is explicitly allowed
				*/
				$ret .= $utf_char;
				continue;
			}

			if (isset($conv[$utf_char]))
			{
				/**
				* The char is mapped to something, maybe to itself actually
				*/
				$ret .= $conv[$utf_char];
				continue;
			}

			/**
			* The char isn't mapped, but did we load its conversion table?
			*
			* The search indexer table is split into blocks. The block number of
			* each char is equal to its codepoint right-shifted for 11 bits. It
			* means that out of the 11, 16 or 21 meaningful bits of a 2-, 3- or
			* 4- byte sequence we only keep the leftmost 0, 5 or 10 bits. Thus,
			* all UTF chars encoded in 2 bytes are in the same first block.
			*/
			if (isset($utf_char[2]))
			{
				if (isset($utf_char[3]))
				{
					/**
					* 1111 0nnn 10nn nnnn 10nx xxxx 10xx xxxx
					* 0000 0111 0011 1111 0010 0000
					*/
					$idx = ((ord($utf_char[0]) & 0x07) << 7) | ((ord($utf_char[1]) & 0x3F) << 1) | ((ord($utf_char[2]) & 0x20) >> 5);
				}
				else
				{
					/**
					* 1110 nnnn 10nx xxxx 10xx xxxx
					* 0000 0111 0010 0000
					*/
					$idx = ((ord($utf_char[0]) & 0x07) << 1) | ((ord($utf_char[1]) & 0x20) >> 5);
				}
			}
			else
			{
				/**
				* 110x xxxx 10xx xxxx
				* 0000 0000 0000 0000
				*/
				$idx = 0;
			}

			/**
			* Check if the required conv table has been loaded already
			*/
			if (!isset($conv_loaded[$idx]))
			{
				$conv_loaded[$idx] = 1;
				$file = $this->phpbb_root_path . 'includes/utf/data/search_indexer_' . $idx . '.' . $this->php_ext;

				if (file_exists($file))
				{
					$conv += include($file);
				}
			}

			if (isset($conv[$utf_char]))
			{
				$ret .= $conv[$utf_char];
			}
			else
			{
				/**
				* We add an entry to the conversion table so that we
				* don't have to convert to codepoint and perform the checks
				* that are above this block
				*/
				$conv[$utf_char] = ' ';
				$ret .= ' ';
			}
		}
		while (1);

		return $ret;
	}

	/**
	* Returns a list of options for the ACP to display
	*/
	public function acp()
	{
		/**
		* if we need any options, copied from fulltext_native for now, will have to be adjusted or removed
		*/

		$tpl = '
		<dl>
			<dt><label for="fulltext_native_load_upd">' . $this->user->lang['YES_SEARCH_UPDATE'] . $this->user->lang['COLON'] . '</label><br /><span>' . $this->user->lang['YES_SEARCH_UPDATE_EXPLAIN'] . '</span></dt>
			<dd><label><input type="radio" id="fulltext_native_load_upd" name="config[fulltext_native_load_upd]" value="1"' . (($this->config['fulltext_native_load_upd']) ? ' checked="checked"' : '') . ' class="radio" /> ' . $this->user->lang['YES'] . '</label><label><input type="radio" name="config[fulltext_native_load_upd]" value="0"' . ((!$this->config['fulltext_native_load_upd']) ? ' checked="checked"' : '') . ' class="radio" /> ' . $this->user->lang['NO'] . '</label></dd>
		</dl>
		<dl>
			<dt><label for="fulltext_native_min_chars">' . $this->user->lang['MIN_SEARCH_CHARS'] . $this->user->lang['COLON'] . '</label><br /><span>' . $this->user->lang['MIN_SEARCH_CHARS_EXPLAIN'] . '</span></dt>
			<dd><input id="fulltext_native_min_chars" type="number" min="0" max="255" name="config[fulltext_native_min_chars]" value="' . (int) $this->config['fulltext_native_min_chars'] . '" /></dd>
		</dl>
		<dl>
			<dt><label for="fulltext_native_max_chars">' . $this->user->lang['MAX_SEARCH_CHARS'] . $this->user->lang['COLON'] . '</label><br /><span>' . $this->user->lang['MAX_SEARCH_CHARS_EXPLAIN'] . '</span></dt>
			<dd><input id="fulltext_native_max_chars" type="number" min="0" max="255" name="config[fulltext_native_max_chars]" value="' . (int) $this->config['fulltext_native_max_chars'] . '" /></dd>
		</dl>
		<dl>
			<dt><label for="fulltext_native_common_thres">' . $this->user->lang['COMMON_WORD_THRESHOLD'] . $this->user->lang['COLON'] . '</label><br /><span>' . $this->user->lang['COMMON_WORD_THRESHOLD_EXPLAIN'] . '</span></dt>
			<dd><input id="fulltext_native_common_thres" type="text" name="config[fulltext_native_common_thres]" value="' . (double) $this->config['fulltext_native_common_thres'] . '" /> %</dd>
		</dl>
		';

		// These are fields required in the config table
		return array(
			'tpl'		=> $tpl,
			'config'	=> array('fulltext_native_load_upd' => 'bool', 'fulltext_native_min_chars' => 'integer:0:255', 'fulltext_native_max_chars' => 'integer:0:255', 'fulltext_native_common_thres' => 'double:0:100')
		);
	}
}
