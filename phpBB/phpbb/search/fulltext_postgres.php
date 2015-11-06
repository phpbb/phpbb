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
* Fulltext search for PostgreSQL
*/
class fulltext_postgres extends \phpbb\search\base
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
	 * Creates a new \phpbb\search\fulltext_postgres, which is used as a search backend
	 *
	 * @param string|bool $error Any error that occurs is passed on through this reference variable otherwise false
	 * @param string $phpbb_root_path Relative path to phpBB root
	 * @param string $phpEx PHP file extension
	 * @param \phpbb\auth\auth $auth Auth object
	 * @param \phpbb\config\config $config Config object
	 * @param \phpbb\db\driver\driver_interface Database object
	 * @param \phpbb\user $user User object
	 * @param \phpbb\event\dispatcher_interface	$phpbb_dispatcher	Event dispatcher object
	 */
	public function __construct(&$error, $phpbb_root_path, $phpEx, $auth, $config, $db, $user, $phpbb_dispatcher)
	{
		$this->config = $config;
		$this->db = $db;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->user = $user;

		$this->word_length = array('min' => $this->config['fulltext_postgres_min_word_len'], 'max' => $this->config['fulltext_postgres_max_word_len']);

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
	* @return string|bool Language key of the error/incompatiblity occurred
	*/
	public function init()
	{
		if ($this->db->get_sql_layer() != 'postgres')
		{
			return $this->user->lang['FULLTEXT_POSTGRES_INCOMPATIBLE_DATABASE'];
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
				else if (strpos($word, '-') === 0)
				{
					$this->search_query .= $word . ' ';
					$this->tsearch_query .= '&!' . substr($word, 1) . ' ';
				}
				else if (strpos($word, '|') === 0)
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
		$search_key_array = array(
			implode(', ', $this->split_words),
			$type,
			$fields,
			$terms,
			$sort_days,
			$sort_key,
			$topic_id,
			implode(',', $ex_fid_ary),
			$post_visibility,
			implode(',', $author_ary)
		);

		/**
		* Allow changing the search_key for cached results
		*
		* @event core.search_postgres_by_keyword_modify_search_key
		* @var	array	search_key_array	Array with search parameters to generate the search_key
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
		extract($this->phpbb_dispatcher->trigger_event('core.search_postgres_by_keyword_modify_search_key', compact($vars)));

		$search_key = md5(implode('#', $search_key_array));

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

		$tsearch_query = $this->tsearch_query;

		/**
		* Allow changing the query used to search for posts using fulltext_postgres
		*
		* @event core.search_postgres_keywords_main_query_before
		* @var	string	tsearch_query		The parsed keywords used for this search
		* @var	int		result_count		The previous result count for the format of the query.
		*									Set to 0 to force a re-count
		* @var	bool	join_topic			Weather or not TOPICS_TABLE should be CROSS JOIN'ED
		* @var	array	author_ary			Array of user_id containing the users to filter the results to
		* @var	string	author_name			An extra username to search on (!empty(author_ary) must be true, to be relevant)
		* @var	array	ex_fid_ary			Which forums not to search on
		* @var	int		topic_id			Limit the search to this topic_id only
		* @var	string	sql_sort_table		Extra tables to include in the SQL query.
		*									Used in conjunction with sql_sort_join
		* @var	string	sql_sort_join		SQL conditions to join all the tables used together.
		*									Used in conjunction with sql_sort_table
		* @var	int		sort_days			Time, in days, of the oldest possible post to list
		* @var	string	sql_match			Which columns to do the search on.
		* @var	string	sql_match_where		Extra conditions to use to properly filter the matching process
		* @var	string	sort_by_sql			The possible predefined sort types
		* @var	string	sort_key			The sort type used from the possible sort types
		* @var	string	sort_dir			"a" for ASC or "d" dor DESC for the sort order used
		* @var	string	sql_sort			The result SQL when processing sort_by_sql + sort_key + sort_dir
		* @var	int		start				How many posts to skip in the search results (used for pagination)
		* @since 3.1.5-RC1
		*/
		$vars = array(
			'tsearch_query',
			'result_count',
			'join_topic',
			'author_ary',
			'author_name',
			'ex_fid_ary',
			'topic_id',
			'sql_sort_table',
			'sql_sort_join',
			'sort_days',
			'sql_match',
			'sql_match_where',
			'sort_by_sql',
			'sort_key',
			'sort_dir',
			'sql_sort',
			'start',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.search_postgres_keywords_main_query_before', compact($vars)));

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
		$sql_where_options .= ' AND ' . $post_visibility;
		$sql_where_options .= $sql_author;
		$sql_where_options .= ($sort_days) ? ' AND p.post_time >= ' . (time() - ($sort_days * 86400)) : '';
		$sql_where_options .= $sql_match_where;

		$tmp_sql_match = array();
		$sql_match = str_replace(',', " || ' ' ||", $sql_match);
		$tmp_sql_match = "to_tsvector ('" . $this->db->sql_escape($this->config['fulltext_postgres_ts_name']) . "', " . $sql_match . ") @@ to_tsquery ('" . $this->db->sql_escape($this->config['fulltext_postgres_ts_name']) . "', '" . $this->db->sql_escape($this->tsearch_query) . "')";

		$this->db->sql_transaction('begin');

		$sql_from = "FROM $sql_from$sql_sort_table" . POSTS_TABLE . " p";
		$sql_where = "WHERE (" . $tmp_sql_match . ")
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
		if (!sizeof($author_ary))
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
		* @event core.search_postgres_by_author_modify_search_key
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
		extract($this->phpbb_dispatcher->trigger_event('core.search_postgres_by_author_modify_search_key', compact($vars)));

		$search_key = md5(implode('#', $search_key_array));

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

		$m_approve_fid_sql = ' AND ' . $post_visibility;

		/**
		* Allow changing the query used to search for posts by author in fulltext_postgres
		*
		* @event core.search_postgres_author_count_query_before
		* @var	int		result_count		The previous result count for the format of the query.
		*									Set to 0 to force a re-count
		* @var	string	sql_sort_table		CROSS JOIN'ed table to allow doing the sort chosen
		* @var	string	sql_sort_join		Condition to define how to join the CROSS JOIN'ed table specifyed in sql_sort_table
		* @var	array	author_ary			Array of user_id containing the users to filter the results to
		* @var	string	author_name			An extra username to search on
		* @var	string	sql_author			SQL WHERE condition for the post author ids
		* @var	int		topic_id			Limit the search to this topic_id only
		* @var	string	sql_topic_id		SQL of topic_id
		* @var	string	sort_by_sql			The possible predefined sort types
		* @var	string	sort_key			The sort type used from the possible sort types
		* @var	string	sort_dir			"a" for ASC or "d" dor DESC for the sort order used
		* @var	string	sql_sort			The result SQL when processing sort_by_sql + sort_key + sort_dir
		* @var	string	sort_days			Time, in days, that the oldest post showing can have
		* @var	string	sql_time			The SQL to search on the time specifyed by sort_days
		* @var	bool	firstpost_only		Wether or not to search only on the first post of the topics
		* @var	array	ex_fid_ary			Forum ids that must not be searched on
		* @var	array	sql_fora			SQL query for ex_fid_ary
		* @var	string	m_approve_fid_sql	WHERE clause condition on post_visibility restrictions
		* @var	int		start				How many posts to skip in the search results (used for pagination)
		* @since 3.1.5-RC1
		*/
		$vars = array(
			'result_count',
			'sql_sort_table',
			'sql_sort_join',
			'author_ary',
			'author_name',
			'sql_author',
			'topic_id',
			'sql_topic_id',
			'sort_by_sql',
			'sort_key',
			'sort_dir',
			'sql_sort',
			'sort_days',
			'sql_time',
			'firstpost_only',
			'ex_fid_ary',
			'sql_fora',
			'm_approve_fid_sql',
			'start',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.search_postgres_author_count_query_before', compact($vars)));

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

		if (!isset($this->stats['post_content']))
		{
			$this->db->sql_query("CREATE INDEX " . POSTS_TABLE . "_" . $this->config['fulltext_postgres_ts_name'] . "_post_content ON " . POSTS_TABLE . " USING gin (to_tsvector ('" . $this->db->sql_escape($this->config['fulltext_postgres_ts_name']) . "', post_text || ' ' || post_subject))");
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

		if (isset($this->stats['post_content']))
		{
			$this->db->sql_query('DROP INDEX ' . $this->stats['post_content']['relname']);
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

		return (isset($this->stats['post_subject']) && isset($this->stats['post_content'])) ? true : false;
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
		if ($this->db->get_sql_layer() != 'postgres')
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
				if ($row['relname'] == POSTS_TABLE . '_' . $this->config['fulltext_postgres_ts_name'] . '_post_subject' || $row['relname'] == POSTS_TABLE . '_post_subject')
				{
					$this->stats['post_subject'] = $row;
				}
				else if ($row['relname'] == POSTS_TABLE . '_' . $this->config['fulltext_postgres_ts_name'] . '_post_content' || $row['relname'] == POSTS_TABLE . '_post_content')
				{
					$this->stats['post_content'] = $row;
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
			<dd>' . (($this->db->get_sql_layer() == 'postgres') ? $this->user->lang['YES'] : $this->user->lang['NO']) . '</dd>
		</dl>
		<dl>
			<dt><label>' . $this->user->lang['FULLTEXT_POSTGRES_TS_NAME'] . '</label><br /><span>' . $this->user->lang['FULLTEXT_POSTGRES_TS_NAME_EXPLAIN'] . '</span></dt>
			<dd><select name="config[fulltext_postgres_ts_name]">';

		if ($this->db->get_sql_layer() == 'postgres')
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
                        <dd><input id="fulltext_postgres_min_word_len" type="number" min="0" max="255" name="config[fulltext_postgres_min_word_len]" value="' . (int) $this->config['fulltext_postgres_min_word_len'] . '" /></dd>
                </dl>
                <dl>
                        <dt><label for="fulltext_postgres_max_word_len">' . $this->user->lang['FULLTEXT_POSTGRES_MAX_WORD_LEN'] . $this->user->lang['COLON'] . '</label><br /><span>' . $this->user->lang['FULLTEXT_POSTGRES_MAX_WORD_LEN_EXPLAIN'] . '</span></dt>
                        <dd><input id="fulltext_postgres_max_word_len" type="number" min="0" max="255" name="config[fulltext_postgres_max_word_len]" value="' . (int) $this->config['fulltext_postgres_max_word_len'] . '" /></dd>
                </dl>
		';

		// These are fields required in the config table
		return array(
			'tpl'		=> $tpl,
			'config'	=> array('fulltext_postgres_ts_name' => 'string', 'fulltext_postgres_min_word_len' => 'integer:0:255', 'fulltext_postgres_max_word_len' => 'integer:0:255')
		);
	}
}
