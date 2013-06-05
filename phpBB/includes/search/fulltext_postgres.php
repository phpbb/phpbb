<?php
/**
*
* @package search
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* fulltext_postgres
* Fulltext search for PostgreSQL
* @package search
*/
class phpbb_search_fulltext_postgres extends phpbb_search_base
{
	/**
	 * Associative array holding index stats
	 * @var array
	 */
	protected $stats = array();

	/**
	 * Holds the words entered by user, obtained by splitting the entered query on whitespace
	 * @var array
	 */
	protected $split_words = array();

	/**
	 * True if PostgreSQL version supports tsearch
	 * @var boolean
	 */
	protected $tsearch_usable = false;

	/**
	 * Stores the PostgreSQL version
	 * @var string
	 */
	protected $version;

	/**
	 * Stores the tsearch query
	 * @var string
	 */
	protected $tsearch_query;

	/**
	 * True if phrase search is supported.
	 * PostgreSQL fulltext currently doesn't support it
	 * @var boolean
	 */
	protected $phrase_search = false;

	/**
	 * Config object
	 * @var phpbb_config
	 */
	protected $config;

	/**
	 * Database connection
	 * @var phpbb_db_driver
	 */
	protected $db;

	/**
	 * User object
	 * @var phpbb_user
	 */
	protected $user;

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
	 * Associative array stores the min and max word length to be searched
	 * @var array
	 */
	protected $word_length = array();

	/**
	 * Constructor
	 * Creates a new phpbb_search_fulltext_postgres, which is used as a search backend
	 *
	 * @param string|bool $error Any error that occurs is passed on through this reference variable otherwise false
	 */
	public function __construct(&$error, $phpbb_root_path, $phpEx, $auth, $config, $db, $user)
	{
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;

		$this->word_length = array('min' => $this->config['fulltext_postgres_min_word_len'], 'max' => $this->config['fulltext_postgres_max_word_len']);

		if ($this->db->sql_layer == 'postgres')
		{
			$pgsql_version = explode(',', substr($this->db->sql_server_info(), 10));
			$this->version = trim($pgsql_version[0]);
			if (version_compare($this->version, '8.3', '>='))
			{
				$this->tsearch_usable = true;
			}
		}

		/**
		 * Load the UTF tools
		 */
		if (!function_exists('utf8_strlen'))
		{
			include($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);
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
		return 'PostgreSQL Fulltext';
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
	 * Returns if phrase search is supported or not
	 *
	 * @return bool
	 */
	public function supports_phrase_search()
	{
		return $this->phrase_search;
	}

	/**
	* Checks for correct PostgreSQL version and stores min/max word length in the config
	*
	* @return string|bool Language key of the error/incompatiblity occured
	*/
	public function init()
	{
		if ($this->db->sql_layer != 'postgres')
		{
			return $this->user->lang['FULLTEXT_POSTGRES_INCOMPATIBLE_DATABASE'];
		}

		if (!$this->tsearch_usable)
		{
			return $this->user->lang['FULLTEXT_POSTGRES_TS_NOT_USABLE'];
		}

		return false;
	}

	/**
	* Splits keywords entered by a user into an array of words stored in $this->split_words
	* Stores the tidied search query in $this->search_query
	*
	* @param	string	&$keywords	Contains the keyword as entered by the user
	* @param	string	$terms	is either 'all' or 'any'
	* @return	bool	false	if no valid keywords were found and otherwise true
	*/
	public function split_keywords(&$keywords, $terms)
	{
		if ($terms == 'all')
		{
			$match		= array('#\sand\s#iu', '#\sor\s#iu', '#\snot\s#iu', '#(^|\s)\+#', '#(^|\s)-#', '#(^|\s)\|#');
			$replace	= array(' +', ' |', ' -', ' +', ' -', ' |');

			$keywords = preg_replace($match, $replace, $keywords);
		}

		// Filter out as above
		$split_keywords = preg_replace("#[\"\n\r\t]+#", ' ', trim(htmlspecialchars_decode($keywords)));

		// Split words
		$split_keywords = preg_replace('#([^\p{L}\p{N}\'*"()])#u', '$1$1', str_replace('\'\'', '\' \'', trim($split_keywords)));
		$matches = array();
		preg_match_all('#(?:[^\p{L}\p{N}*"()]|^)([+\-|]?(?:[\p{L}\p{N}*"()]+\'?)*[\p{L}\p{N}*"()])(?:[^\p{L}\p{N}*"()]|$)#u', $split_keywords, $matches);
		$this->split_words = $matches[1];

		foreach ($this->split_words as $i => $word)
		{
			$clean_word = preg_replace('#^[+\-|"]#', '', $word);

			// check word length
			$clean_len = utf8_strlen(str_replace('*', '', $clean_word));
			if (($clean_len < $this->config['fulltext_postgres_min_word_len']) || ($clean_len > $this->config['fulltext_postgres_max_word_len']))
			{
				$this->common_words[] = $word;
				unset($this->split_words[$i]);
			}
		}

		if ($terms == 'any')
		{
			$this->search_query = '';
			$this->tsearch_query = '';
			foreach ($this->split_words as $word)
			{
				if ((strpos($word, '+') === 0) || (strpos($word, '-') === 0) || (strpos($word, '|') === 0))
				{
					$word = substr($word, 1);
				}
				$this->search_query .= $word . ' ';
				$this->tsearch_query .= '|' . $word . ' ';
			}
		}
		else
		{
			$this->search_query = '';
			$this->tsearch_query = '';
			foreach ($this->split_words as $word)
			{
				if (strpos($word, '+') === 0)
				{
					$this->search_query .= $word . ' ';
					$this->tsearch_query .= '&' . substr($word, 1) . ' ';
				}
				elseif (strpos($word, '-') === 0)
				{
					$this->search_query .= $word . ' ';
					$this->tsearch_query .= '&!' . substr($word, 1) . ' ';
				}
				elseif (strpos($word, '|') === 0)
				{
					$this->search_query .= $word . ' ';
					$this->tsearch_query .= '|' . substr($word, 1) . ' ';
				}
				else
				{
					$this->search_query .= '+' . $word . ' ';
					$this->tsearch_query .= '&' . $word . ' ';
				}
			}
		}

		$this->tsearch_query = substr($this->tsearch_query, 1);
		$this->search_query = utf8_htmlspecialchars($this->search_query);

		if ($this->search_query)
		{
			$this->split_words = array_values($this->split_words);
			sort($this->split_words);
			return true;
		}
		return false;
	}

	/**
	* Turns text into an array of words
	* @param string $text contains post text/subject
	*/
	public function split_message($text)
	{
		// Split words
		$text = preg_replace('#([^\p{L}\p{N}\'*])#u', '$1$1', str_replace('\'\'', '\' \'', trim($text)));
		$matches = array();
		preg_match_all('#(?:[^\p{L}\p{N}*]|^)([+\-|]?(?:[\p{L}\p{N}*]+\'?)*[\p{L}\p{N}*])(?:[^\p{L}\p{N}*]|$)#u', $text, $matches);
		$text = $matches[1];

		// remove too short or too long words
		$text = array_values($text);
		for ($i = 0, $n = sizeof($text); $i < $n; $i++)
		{
			$text[$i] = trim($text[$i]);
			if (utf8_strlen($text[$i]) < $this->config['fulltext_postgres_min_word_len'] || utf8_strlen($text[$i]) > $this->config['fulltext_postgres_max_word_len'])
			{
				unset($text[$i]);
			}
		}

		return array_values($text);
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
	* @param	array		$m_approve_fid_ary	specifies an array of forum ids in which the searcher is allowed to view unapproved posts
	* @param	int			$topic_id			is set to 0 or a topic id, if it is not 0 then only posts in this topic should be searched
	* @param	array		$author_ary			an array of author ids if the author should be ignored during the search the array is empty
	* @param	string		$author_name		specifies the author match, when ANONYMOUS is also a search-match
	* @param	array		&$id_ary			passed by reference, to be filled with ids for the page specified by $start and $per_page, should be ordered
	* @param	int			$start				indicates the first index of the page
	* @param	int			$per_page			number of ids each page is supposed to contain
	* @return	boolean|int						total number of results
	*/
	public function keyword_search($type, $fields, $terms, $sort_by_sql, $sort_key, $sort_dir, $sort_days, $ex_fid_ary, $m_approve_fid_ary, $topic_id, $author_ary, $author_name, &$id_ary, &$start, $per_page)
	{
		// No keywords? No posts
		if (!$this->search_query)
		{
			return false;
		}

		// When search query contains queries like -foo
		if (strpos($this->search_query, '+') === false)
		{
			return false;
		}

		// generate a search_key from all the options to identify the results
		$search_key = md5(implode('#', array(
			implode(', ', $this->split_words),
			$type,
			$fields,
			$terms,
			$sort_days,
			$sort_key,
			$topic_id,
			implode(',', $ex_fid_ary),
			implode(',', $m_approve_fid_ary),
			implode(',', $author_ary)
		)));

		if ($start < 0)
		{
			$start = 0;
		}

		// try reading the results from cache
		$result_count = 0;
		if ($this->obtain_ids($search_key, $result_count, $id_ary, $start, $per_page, $sort_dir) == SEARCH_RESULT_IN_CACHE)
		{
			return $result_count;
		}

		$id_ary = array();

		$join_topic = ($type == 'posts') ? false : true;

		// Build sql strings for sorting
		$sql_sort = $sort_by_sql[$sort_key] . (($sort_dir == 'a') ? ' ASC' : ' DESC');
		$sql_sort_table = $sql_sort_join = '';

		switch ($sql_sort[0])
		{
			case 'u':
				$sql_sort_table	= USERS_TABLE . ' u, ';
				$sql_sort_join	= ($type == 'posts') ? ' AND u.user_id = p.poster_id ' : ' AND u.user_id = t.topic_poster ';
			break;

			case 't':
				$join_topic = true;
			break;

			case 'f':
				$sql_sort_table	= FORUMS_TABLE . ' f, ';
				$sql_sort_join	= ' AND f.forum_id = p.forum_id ';
			break;
		}

		// Build some display specific sql strings
		switch ($fields)
		{
			case 'titleonly':
				$sql_match = 'p.post_subject';
				$sql_match_where = ' AND p.post_id = t.topic_first_post_id';
				$join_topic = true;
			break;

			case 'msgonly':
				$sql_match = 'p.post_text';
				$sql_match_where = '';
			break;

			case 'firstpost':
				$sql_match = 'p.post_subject, p.post_text';
				$sql_match_where = ' AND p.post_id = t.topic_first_post_id';
				$join_topic = true;
			break;

			default:
				$sql_match = 'p.post_subject, p.post_text';
				$sql_match_where = '';
			break;
		}

		if (!sizeof($m_approve_fid_ary))
		{
			$m_approve_fid_sql = ' AND p.post_approved = 1';
		}
		else if ($m_approve_fid_ary === array(-1))
		{
			$m_approve_fid_sql = '';
		}
		else
		{
			$m_approve_fid_sql = ' AND (p.post_approved = 1 OR ' . $this->db->sql_in_set('p.forum_id', $m_approve_fid_ary, true) . ')';
		}

		$sql_select			= ($type == 'posts') ? 'p.post_id' : 'DISTINCT t.topic_id';
		$sql_from			= ($join_topic) ? TOPICS_TABLE . ' t, ' : '';
		$field				= ($type == 'posts') ? 'post_id' : 'topic_id';
		$sql_author			= (sizeof($author_ary) == 1) ? ' = ' . $author_ary[0] : 'IN (' . implode(', ', $author_ary) . ')';

		if (sizeof($author_ary) && $author_name)
		{
			// first one matches post of registered users, second one guests and deleted users
			$sql_author = '(' . $this->db->sql_in_set('p.poster_id', array_diff($author_ary, array(ANONYMOUS)), false, true) . ' OR p.post_username ' . $author_name . ')';
		}
		else if (sizeof($author_ary))
		{
			$sql_author = ' AND ' . $this->db->sql_in_set('p.poster_id', $author_ary);
		}
		else
		{
			$sql_author = '';
		}

		$sql_where_options = $sql_sort_join;
		$sql_where_options .= ($topic_id) ? ' AND p.topic_id = ' . $topic_id : '';
		$sql_where_options .= ($join_topic) ? ' AND t.topic_id = p.topic_id' : '';
		$sql_where_options .= (sizeof($ex_fid_ary)) ? ' AND ' . $this->db->sql_in_set('p.forum_id', $ex_fid_ary, true) : '';
		$sql_where_options .= $m_approve_fid_sql;
		$sql_where_options .= $sql_author;
		$sql_where_options .= ($sort_days) ? ' AND p.post_time >= ' . (time() - ($sort_days * 86400)) : '';
		$sql_where_options .= $sql_match_where;

		$tmp_sql_match = array();
		foreach (explode(',', $sql_match) as $sql_match_column)
		{
			$tmp_sql_match[] = "to_tsvector ('" . $this->db->sql_escape($this->config['fulltext_postgres_ts_name']) . "', " . $sql_match_column . ") @@ to_tsquery ('" . $this->db->sql_escape($this->config['fulltext_postgres_ts_name']) . "', '" . $this->db->sql_escape($this->tsearch_query) . "')";
		}

		$this->db->sql_transaction('begin');

		$sql_from = "FROM $sql_from$sql_sort_table" . POSTS_TABLE . " p";
		$sql_where = "WHERE (" . implode(' OR ', $tmp_sql_match) . ")
			$sql_where_options";
		$sql = "SELECT $sql_select
			$sql_from
			$sql_where
			ORDER BY $sql_sort";
		$result = $this->db->sql_query_limit($sql, $this->config['search_block_size'], $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$id_ary[] = $row[$field];
		}
		$this->db->sql_freeresult($result);

		$id_ary = array_unique($id_ary);

		// if the total result count is not cached yet, retrieve it from the db
		if (!$result_count)
		{
			$sql_count = "SELECT COUNT(*) as result_count
				$sql_from
				$sql_where";
			$result = $this->db->sql_query($sql_count);
			$result_count = (int) $this->db->sql_fetchfield('result_count');
			$this->db->sql_freeresult($result);

			if (!$result_count)
			{
				return false;
			}
		}

		$this->db->sql_transaction('commit');

		if ($start >= $result_count)
		{
			$start = floor(($result_count - 1) / $per_page) * $per_page;

			$result = $this->db->sql_query_limit($sql, $this->config['search_block_size'], $start);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$id_ary[] = $row[$field];
			}
			$this->db->sql_freeresult($result);

			$id_ary = array_unique($id_ary);
		}

		// store the ids, from start on then delete anything that isn't on the current page because we only need ids for one page
		$this->save_ids($search_key, implode(' ', $this->split_words), $author_ary, $result_count, $id_ary, $start, $sort_dir);
		$id_ary = array_slice($id_ary, 0, (int) $per_page);

		return $result_count;
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
	* @param	array		$m_approve_fid_ary	specifies an array of forum ids in which the searcher is allowed to view unapproved posts
	* @param	int			$topic_id			is set to 0 or a topic id, if it is not 0 then only posts in this topic should be searched
	* @param	array		$author_ary			an array of author ids
	* @param	string		$author_name		specifies the author match, when ANONYMOUS is also a search-match
	* @param	array		&$id_ary			passed by reference, to be filled with ids for the page specified by $start and $per_page, should be ordered
	* @param	int			$start				indicates the first index of the page
	* @param	int			$per_page			number of ids each page is supposed to contain
	* @return	boolean|int						total number of results
	*/
	public function author_search($type, $firstpost_only, $sort_by_sql, $sort_key, $sort_dir, $sort_days, $ex_fid_ary, $m_approve_fid_ary, $topic_id, $author_ary, $author_name, &$id_ary, &$start, $per_page)
	{
		// No author? No posts
		if (!sizeof($author_ary))
		{
			return 0;
		}

		// generate a search_key from all the options to identify the results
		$search_key = md5(implode('#', array(
			'',
			$type,
			($firstpost_only) ? 'firstpost' : '',
			'',
			'',
			$sort_days,
			$sort_key,
			$topic_id,
			implode(',', $ex_fid_ary),
			implode(',', $m_approve_fid_ary),
			implode(',', $author_ary),
			$author_name,
		)));

		if ($start < 0)
		{
			$start = 0;
		}

		// try reading the results from cache
		$result_count = 0;
		if ($this->obtain_ids($search_key, $result_count, $id_ary, $start, $per_page, $sort_dir) == SEARCH_RESULT_IN_CACHE)
		{
			return $result_count;
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
		$sql_fora		= (sizeof($ex_fid_ary)) ? ' AND ' . $this->db->sql_in_set('p.forum_id', $ex_fid_ary, true) : '';
		$sql_topic_id	= ($topic_id) ? ' AND p.topic_id = ' . (int) $topic_id : '';
		$sql_time		= ($sort_days) ? ' AND p.post_time >= ' . (time() - ($sort_days * 86400)) : '';
		$sql_firstpost = ($firstpost_only) ? ' AND p.post_id = t.topic_first_post_id' : '';

		// Build sql strings for sorting
		$sql_sort = $sort_by_sql[$sort_key] . (($sort_dir == 'a') ? ' ASC' : ' DESC');
		$sql_sort_table = $sql_sort_join = '';
		switch ($sql_sort[0])
		{
			case 'u':
				$sql_sort_table	= USERS_TABLE . ' u, ';
				$sql_sort_join	= ($type == 'posts') ? ' AND u.user_id = p.poster_id ' : ' AND u.user_id = t.topic_poster ';
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

		if (!sizeof($m_approve_fid_ary))
		{
			$m_approve_fid_sql = ' AND p.post_approved = 1';
		}
		else if ($m_approve_fid_ary == array(-1))
		{
			$m_approve_fid_sql = '';
		}
		else
		{
			$m_approve_fid_sql = ' AND (p.post_approved = 1 OR ' . $this->db->sql_in_set('p.forum_id', $m_approve_fid_ary, true) . ')';
		}

		// Build the query for really selecting the post_ids
		if ($type == 'posts')
		{
			$sql = "SELECT p.post_id
				FROM " . $sql_sort_table . POSTS_TABLE . ' p' . (($firstpost_only) ? ', ' . TOPICS_TABLE . ' t ' : ' ') . "
				WHERE $sql_author
					$sql_topic_id
					$sql_firstpost
					$m_approve_fid_sql
					$sql_fora
					$sql_sort_join
					$sql_time
				ORDER BY $sql_sort";
			$field = 'post_id';
		}
		else
		{
			$sql = "SELECT t.topic_id
				FROM " . $sql_sort_table . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
				WHERE $sql_author
					$sql_topic_id
					$sql_firstpost
					$m_approve_fid_sql
					$sql_fora
					AND t.topic_id = p.topic_id
					$sql_sort_join
					$sql_time
				GROUP BY t.topic_id, $sort_by_sql[$sort_key]
				ORDER BY $sql_sort";
			$field = 'topic_id';
		}

		$this->db->sql_transaction('begin');

		// Only read one block of posts from the db and then cache it
		$result = $this->db->sql_query_limit($sql, $this->config['search_block_size'], $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$id_ary[] = $row[$field];
		}
		$this->db->sql_freeresult($result);

		// retrieve the total result count if needed
		if (!$result_count)
		{
			if ($type == 'posts')
			{
				$sql_count = "SELECT COUNT(*) as result_count
					FROM " . $sql_sort_table . POSTS_TABLE . ' p' . (($firstpost_only) ? ', ' . TOPICS_TABLE . ' t ' : ' ') . "
					WHERE $sql_author
						$sql_topic_id
						$sql_firstpost
						$m_approve_fid_sql
						$sql_fora
						$sql_sort_join
						$sql_time";
			}
			else
			{
				$sql_count = "SELECT COUNT(*) as result_count
					FROM " . $sql_sort_table . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
					WHERE $sql_author
						$sql_topic_id
						$sql_firstpost
						$m_approve_fid_sql
						$sql_fora
						AND t.topic_id = p.topic_id
						$sql_sort_join
						$sql_time
					GROUP BY t.topic_id, $sort_by_sql[$sort_key]";
			}

			$result = $this->db->sql_query($sql_count);
			$result_count = (int) $this->db->sql_fetchfield('result_count');

			if (!$result_count)
			{
				return false;
			}
		}

		$this->db->sql_transaction('commit');

		if ($start >= $result_count)
		{
			$start = floor(($result_count - 1) / $per_page) * $per_page;

			$result = $this->db->sql_query_limit($sql, $this->config['search_block_size'], $start);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$id_ary[] = (int) $row[$field];
			}
			$this->db->sql_freeresult($result);

			$id_ary = array_unique($id_ary);
		}

		if (sizeof($id_ary))
		{
			$this->save_ids($search_key, '', $author_ary, $result_count, $id_ary, $start, $sort_dir);
			$id_ary = array_slice($id_ary, 0, $per_page);

			return $result_count;
		}
		return false;
	}

	/**
	* Destroys cached search results, that contained one of the new words in a post so the results won't be outdated
	*
	* @param	string		$mode		contains the post mode: edit, post, reply, quote ...
	* @param	int			$post_id	contains the post id of the post to index
	* @param	string		$message	contains the post text of the post
	* @param	string		$subject	contains the subject of the post to index
	* @param	int			$poster_id	contains the user id of the poster
	* @param	int			$forum_id	contains the forum id of parent forum of the post
	*/
	public function index($mode, $post_id, &$message, &$subject, $poster_id, $forum_id)
	{
		// Split old and new post/subject to obtain array of words
		$split_text = $this->split_message($message);
		$split_title = ($subject) ? $this->split_message($subject) : array();

		$words = array_unique(array_merge($split_text, $split_title));

		unset($split_text);
		unset($split_title);

		// destroy cached search results containing any of the words removed or added
		$this->destroy_cache($words, array($poster_id));

		unset($words);
	}

	/**
	* Destroy cached results, that might be outdated after deleting a post
	*/
	public function index_remove($post_ids, $author_ids, $forum_ids)
	{
		$this->destroy_cache(array(), $author_ids);
	}

	/**
	* Destroy old cache entries
	*/
	public function tidy()
	{
		// destroy too old cached search results
		$this->destroy_cache(array());

		set_config('search_last_gc', time(), true);
	}

	/**
	* Create fulltext index
	*
	* @return string|bool error string is returned incase of errors otherwise false
	*/
	public function create_index($acp_module, $u_action)
	{
		// Make sure we can actually use PostgreSQL with fulltext indexes
		if ($error = $this->init())
		{
			return $error;
		}

		if (empty($this->stats))
		{
			$this->get_stats();
		}

		if (!isset($this->stats['post_subject']))
		{
			$this->db->sql_query("CREATE INDEX " . POSTS_TABLE . "_" . $this->config['fulltext_postgres_ts_name'] . "_post_subject ON " . POSTS_TABLE . " USING gin (to_tsvector ('" . $this->db->sql_escape($this->config['fulltext_postgres_ts_name']) . "', post_subject))");
		}

		if (!isset($this->stats['post_text']))
		{
			$this->db->sql_query("CREATE INDEX " . POSTS_TABLE . "_" . $this->config['fulltext_postgres_ts_name'] . "_post_text ON " . POSTS_TABLE . " USING gin (to_tsvector ('" . $this->db->sql_escape($this->config['fulltext_postgres_ts_name']) . "', post_text))");
		}

		$this->db->sql_query('TRUNCATE TABLE ' . SEARCH_RESULTS_TABLE);

		return false;
	}

	/**
	* Drop fulltext index
	*
	* @return string|bool error string is returned incase of errors otherwise false
	*/
	public function delete_index($acp_module, $u_action)
	{
		// Make sure we can actually use PostgreSQL with fulltext indexes
		if ($error = $this->init())
		{
			return $error;
		}

		if (empty($this->stats))
		{
			$this->get_stats();
		}

		if (isset($this->stats['post_subject']))
		{
			$this->db->sql_query('DROP INDEX ' . $this->stats['post_subject']['relname']);
		}

		if (isset($this->stats['post_text']))
		{
			$this->db->sql_query('DROP INDEX ' . $this->stats['post_text']['relname']);
		}

		$this->db->sql_query('TRUNCATE TABLE ' . SEARCH_RESULTS_TABLE);

		return false;
	}

	/**
	* Returns true if both FULLTEXT indexes exist
	*/
	public function index_created()
	{
		if (empty($this->stats))
		{
			$this->get_stats();
		}

		return (isset($this->stats['post_text']) && isset($this->stats['post_subject'])) ? true : false;
	}

	/**
	* Returns an associative array containing information about the indexes
	*/
	public function index_stats()
	{
		if (empty($this->stats))
		{
			$this->get_stats();
		}

		return array(
			$this->user->lang['FULLTEXT_POSTGRES_TOTAL_POSTS']			=> ($this->index_created()) ? $this->stats['total_posts'] : 0,
		);
	}

	/**
	 * Computes the stats and store them in the $this->stats associative array
	 */
	protected function get_stats()
	{
		if ($this->db->sql_layer != 'postgres')
		{
			$this->stats = array();
			return;
		}

		$sql = "SELECT c2.relname, pg_catalog.pg_get_indexdef(i.indexrelid, 0, true) AS indexdef
			  FROM pg_catalog.pg_class c1, pg_catalog.pg_index i, pg_catalog.pg_class c2
			 WHERE c1.relname = '" . POSTS_TABLE . "'
			   AND pg_catalog.pg_table_is_visible(c1.oid)
			   AND c1.oid = i.indrelid
			   AND i.indexrelid = c2.oid";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// deal with older PostgreSQL versions which didn't use Index_type
			if (strpos($row['indexdef'], 'to_tsvector') !== false)
			{
				if ($row['relname'] == POSTS_TABLE . '_' . $this->config['fulltext_postgres_ts_name'] . '_post_text' || $row['relname'] == POSTS_TABLE . '_post_text')
				{
					$this->stats['post_text'] = $row;
				}
				else if ($row['relname'] == POSTS_TABLE . '_' . $this->config['fulltext_postgres_ts_name'] . '_post_subject' || $row['relname'] == POSTS_TABLE . '_post_subject')
				{
					$this->stats['post_subject'] = $row;
				}
			}
		}
		$this->db->sql_freeresult($result);

		$this->stats['total_posts'] = $this->config['num_posts'];
	}

	/**
	* Display various options that can be configured for the backend from the acp
	*
	* @return associative array containing template and config variables
	*/
	public function acp()
	{
		$tpl = '
		<dl>
			<dt><label>' . $this->user->lang['FULLTEXT_POSTGRES_VERSION_CHECK'] . '</label><br /><span>' . $this->user->lang['FULLTEXT_POSTGRES_VERSION_CHECK_EXPLAIN'] . '</span></dt>
			<dd>' . (($this->tsearch_usable) ? $this->user->lang['YES'] : $this->user->lang['NO']) . ' (PostgreSQL ' . $this->version . ')</dd>
		</dl>
		<dl>
			<dt><label>' . $this->user->lang['FULLTEXT_POSTGRES_TS_NAME'] . '</label><br /><span>' . $this->user->lang['FULLTEXT_POSTGRES_TS_NAME_EXPLAIN'] . '</span></dt>
			<dd><select name="config[fulltext_postgres_ts_name]">';

		if ($this->db->sql_layer == 'postgres' && $this->tsearch_usable)
		{
			$sql = 'SELECT cfgname AS ts_name
				  FROM pg_ts_config';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$tpl .= '<option value="' . $row['ts_name'] . '"' . ($row['ts_name'] === $this->config['fulltext_postgres_ts_name'] ? ' selected="selected"' : '') . '>' . $row['ts_name'] . '</option>';
			}
			$this->db->sql_freeresult($result);
		}
		else
		{
			$tpl .= '<option value="' . $this->config['fulltext_postgres_ts_name'] . '" selected="selected">' . $this->config['fulltext_postgres_ts_name'] . '</option>';
		}

		$tpl .= '</select></dd>
		</dl>
                <dl>
                        <dt><label for="fulltext_postgres_min_word_len">' . $this->user->lang['FULLTEXT_POSTGRES_MIN_WORD_LEN'] . $this->user->lang['COLON'] . '</label><br /><span>' . $this->user->lang['FULLTEXT_POSTGRES_MIN_WORD_LEN_EXPLAIN'] . '</span></dt>
                        <dd><input id="fulltext_postgres_min_word_len" type="number" size="3" maxlength="3" min="0" max="255" name="config[fulltext_postgres_min_word_len]" value="' . (int) $this->config['fulltext_postgres_min_word_len'] . '" /></dd>
                </dl>
                <dl>
                        <dt><label for="fulltext_postgres_max_word_len">' . $this->user->lang['FULLTEXT_POSTGRES_MAX_WORD_LEN'] . $this->user->lang['COLON'] . '</label><br /><span>' . $this->user->lang['FULLTEXT_POSTGRES_MAX_WORD_LEN_EXPLAIN'] . '</span></dt>
                        <dd><input id="fulltext_postgres_max_word_len" type="number" size="3" maxlength="3" min="0" max="255" name="config[fulltext_postgres_max_word_len]" value="' . (int) $this->config['fulltext_postgres_max_word_len'] . '" /></dd>
                </dl>
		';

		// These are fields required in the config table
		return array(
			'tpl'		=> $tpl,
			'config'	=> array('fulltext_postgres_ts_name' => 'string', 'fulltext_postgres_min_word_len' => 'integer:0:255', 'fulltext_postgres_max_word_len' => 'integer:0:255')
		);
	}
}
