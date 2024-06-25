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

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\event\dispatcher_interface;
use phpbb\language\language;
use phpbb\search\exception\search_exception;
use phpbb\user;

/**
* Fulltext search for PostgreSQL
*/
class fulltext_postgres extends base implements search_backend_interface
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
	protected $tsearch_query = '';

	/**
	 * True if phrase search is supported.
	 * PostgreSQL fulltext currently doesn't support it
	 * @var boolean
	 */
	protected $phrase_search = false;

	/**
	 * phpBB event dispatcher object
	 * @var dispatcher_interface
	 */
	protected $phpbb_dispatcher;

	/**
	 * @var language
	 */
	protected $language;
	/**
	 * Contains tidied search query.
	 * Operators are prefixed in search query and common words excluded
	 * @var string
	 */
	protected $search_query = '';

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
	 * Creates a new \phpbb\search\backend\fulltext_postgres, which is used as a search backend
	 *
	 * @param config				$config				Config object
	 * @param driver_interface		$db					Database object
	 * @param dispatcher_interface	$phpbb_dispatcher	Event dispatcher object
	 * @param language				$language
	 * @param user					$user				User object
	 * @param string				$search_results_table
	 * @param string				$phpbb_root_path	Relative path to phpBB root
	 * @param string				$phpEx				PHP file extension
	 */
	public function __construct(config $config, driver_interface $db, dispatcher_interface $phpbb_dispatcher, language $language, user $user, string $search_results_table, string $phpbb_root_path, string $phpEx)
	{
		global $cache;

		parent::__construct($cache, $config, $db, $user, $search_results_table);
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->language = $language;

		$this->word_length = array('min' => $this->config['fulltext_postgres_min_word_len'], 'max' => $this->config['fulltext_postgres_max_word_len']);

		/**
		 * Load the UTF tools
		 */
		if (!function_exists('utf8_strlen'))
		{
			include($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_name(): string
	{
		return 'PostgreSQL Fulltext';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available(): bool
	{
		return $this->db->get_sql_layer() == 'postgres';
	}

	/**
	 * {@inheritdoc}
	 */
	public function init()
	{
		if (!$this->is_available())
		{
			return $this->language->lang('FULLTEXT_POSTGRES_INCOMPATIBLE_DATABASE');
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_search_query(): string
	{
		return $this->search_query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_common_words(): array
	{
		return $this->common_words;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_word_length()
	{
		return $this->word_length;
	}

	/**
	 * {@inheritdoc}
	 */
	public function split_keywords(string &$keywords, string $terms): bool
	{
		if ($terms == 'all')
		{
			$match		= array('#\sand\s#iu', '#\sor\s#iu', '#\snot\s#iu', '#(^|\s)\+#', '#(^|\s)-#', '#(^|\s)\|#');
			$replace	= array(' +', ' |', ' -', ' +', ' -', ' |');

			$keywords = preg_replace($match, $replace, $keywords);
		}

		// Filter out as above
		$split_keywords = preg_replace("#[\"\n\r\t]+#", ' ', trim(html_entity_decode($keywords, ENT_COMPAT)));

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
	 * {@inheritdoc}
	 */
	public function keyword_search(string $type, string $fields, string $terms, array $sort_by_sql, string $sort_key, string $sort_dir, string $sort_days, array $ex_fid_ary, string $post_visibility, int $topic_id, array $author_ary, string $author_name, array &$id_ary, int &$start, int $per_page)
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
		if ($this->obtain_ids($search_key, $result_count, $id_ary, $start, $per_page, $sort_dir) == self::SEARCH_RESULT_IN_CACHE)
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

		$sql_select			= ($type == 'posts') ? 'p.post_id' : 'DISTINCT t.topic_id, ' . $sort_by_sql[$sort_key];
		$sql_from			= ($join_topic) ? TOPICS_TABLE . ' t, ' : '';
		$field				= ($type == 'posts') ? 'post_id' : 'topic_id';

		if (count($author_ary) && $author_name)
		{
			// first one matches post of registered users, second one guests and deleted users
			$sql_author = '(' . $this->db->sql_in_set('p.poster_id', array_diff($author_ary, array(ANONYMOUS)), false, true) . ' OR p.post_username ' . $author_name . ')';
		}
		else if (count($author_ary))
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
		$sql_where_options .= (count($ex_fid_ary)) ? ' AND ' . $this->db->sql_in_set('p.forum_id', $ex_fid_ary, true) : '';
		$sql_where_options .= ' AND ' . $post_visibility;
		$sql_where_options .= $sql_author;
		$sql_where_options .= ($sort_days) ? ' AND p.post_time >= ' . (time() - ($sort_days * 86400)) : '';
		$sql_where_options .= $sql_match_where;

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
			$sql_count = "SELECT COUNT(DISTINCT " . (($type == 'posts') ? 'p.post_id' : 't.topic_id') . ") as result_count
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
	 * {@inheritdoc}
	 */
	public function author_search(string $type, bool $firstpost_only, array $sort_by_sql, string $sort_key, string $sort_dir, string $sort_days, array $ex_fid_ary, string $post_visibility, int $topic_id, array $author_ary, string $author_name, array &$id_ary, int &$start, int $per_page)
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
		if ($this->obtain_ids($search_key, $result_count, $id_ary, $start, $per_page, $sort_dir) == self::SEARCH_RESULT_IN_CACHE)
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
		$sql_fora		= (count($ex_fid_ary)) ? ' AND ' . $this->db->sql_in_set('p.forum_id', $ex_fid_ary, true) : '';
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
			$result_count = ($type == 'posts') ? (int) $this->db->sql_fetchfield('result_count') : count($this->db->sql_fetchrowset($result));
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
				$id_ary[] = (int) $row[$field];
			}
			$this->db->sql_freeresult($result);

			$id_ary = array_unique($id_ary);
		}

		if (count($id_ary))
		{
			$this->save_ids($search_key, '', $author_ary, $result_count, $id_ary, $start, $sort_dir);
			$id_ary = array_slice($id_ary, 0, $per_page);

			return $result_count;
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function supports_phrase_search(): bool
	{
		return $this->phrase_search;
	}

	/**
	 * {@inheritdoc}
	 */
	public function index(string $mode, int $post_id, string &$message, string &$subject, int $poster_id, int $forum_id)
	{
		// Split old and new post/subject to obtain array of words
		$split_text = $this->split_message($message);
		$split_title = ($subject) ? $this->split_message($subject) : array();

		$words = array_unique(array_merge($split_text, $split_title));

		/**
		* Event to modify method arguments and words before the PostgreSQL search index is updated
		*
		* @event core.search_postgres_index_before
		* @var string	mode				Contains the post mode: edit, post, reply, quote
		* @var int		post_id				The id of the post which is modified/created
		* @var string	message				New or updated post content
		* @var string	subject				New or updated post subject
		* @var int		poster_id			Post author's user id
		* @var int		forum_id			The id of the forum in which the post is located
		* @var array	words				Array of words added to the index
		* @var array	split_text			Array of words from the message
		* @var array	split_title			Array of words from the title
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
		);
		extract($this->phpbb_dispatcher->trigger_event('core.search_postgres_index_before', compact($vars)));

		unset($split_text);
		unset($split_title);

		// destroy cached search results containing any of the words removed or added
		$this->destroy_cache($words, array($poster_id));

		unset($words);
	}

	/**
	 * {@inheritdoc}
	 */
	public function index_remove(array $post_ids, array $author_ids, array $forum_ids): void
	{
		$this->destroy_cache([], $author_ids);
	}

	/**
	 * {@inheritdoc}
	 */
	public function tidy(): void
	{
		// destroy too old cached search results
		$this->destroy_cache(array());

		$this->config->set('search_last_gc', time(), false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function create_index(int &$post_counter = 0): ?array
	{
		// Make sure we can actually use PostgreSQL with fulltext indexes
		if ($error = $this->init())
		{
			throw new search_exception($error);
		}

		if (empty($this->stats))
		{
			$this->get_stats();
		}

		$sql_queries = [];

		if (!isset($this->stats['post_subject']))
		{
			$sql_queries[] = "CREATE INDEX " . POSTS_TABLE . "_" . $this->config['fulltext_postgres_ts_name'] . "_post_subject ON " . POSTS_TABLE . " USING gin (to_tsvector ('" . $this->db->sql_escape($this->config['fulltext_postgres_ts_name']) . "', post_subject))";
		}

		if (!isset($this->stats['post_content']))
		{
			$sql_queries[] = "CREATE INDEX " . POSTS_TABLE . "_" . $this->config['fulltext_postgres_ts_name'] . "_post_content ON " . POSTS_TABLE . " USING gin (to_tsvector ('" . $this->db->sql_escape($this->config['fulltext_postgres_ts_name']) . "', post_text))";
		}

		if (!isset($this->stats['post_subject_content']))
		{
			$sql_queries[] = "CREATE INDEX " . POSTS_TABLE . "_" . $this->config['fulltext_postgres_ts_name'] . "_post_subject_content ON " . POSTS_TABLE . " USING gin (to_tsvector ('" . $this->db->sql_escape($this->config['fulltext_postgres_ts_name']) . "', post_subject || ' ' || post_text))";
		}

		$stats = $this->stats;

		/**
		* Event to modify SQL queries before the Postgres search index is created
		*
		* @event core.search_postgres_create_index_before
		* @var array	sql_queries			Array with queries for creating the search index
		* @var array	stats				Array with statistics of the current index (read only)
		* @since 3.2.3-RC1
		*/
		$vars = array(
			'sql_queries',
			'stats',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.search_postgres_create_index_before', compact($vars)));

		foreach ($sql_queries as $sql_query)
		{
			$this->db->sql_query($sql_query);
		}

		$this->db->sql_query('TRUNCATE TABLE ' . $this->search_results_table);

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete_index(int &$post_counter = null): ?array
	{
		// Make sure we can actually use PostgreSQL with fulltext indexes
		if ($error = $this->init())
		{
			throw new search_exception($error);
		}

		if (empty($this->stats))
		{
			$this->get_stats();
		}

		$sql_queries = [];

		if (isset($this->stats['post_subject']))
		{
			$sql_queries[] = 'DROP INDEX ' . $this->stats['post_subject']['relname'];
		}

		if (isset($this->stats['post_content']))
		{
			$sql_queries[] = 'DROP INDEX ' . $this->stats['post_content']['relname'];
		}

		if (isset($this->stats['post_subject_content']))
		{
			$sql_queries[] = 'DROP INDEX ' . $this->stats['post_subject_content']['relname'];
		}

		$stats = $this->stats;

		/**
		* Event to modify SQL queries before the Postgres search index is created
		*
		* @event core.search_postgres_delete_index_before
		* @var array	sql_queries			Array with queries for deleting the search index
		* @var array	stats				Array with statistics of the current index (read only)
		* @since 3.2.3-RC1
		*/
		$vars = array(
			'sql_queries',
			'stats',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.search_postgres_delete_index_before', compact($vars)));

		foreach ($sql_queries as $sql_query)
		{
			$this->db->sql_query($sql_query);
		}

		$this->db->sql_query('TRUNCATE TABLE ' . $this->search_results_table);

		return null;
	}

	/**
	 * {@inheritdoc}
	*/
	public function index_created(): bool
	{
		if (empty($this->stats))
		{
			$this->get_stats();
		}

		return (isset($this->stats['post_subject']) && isset($this->stats['post_content'])) ? true : false;
	}

	/**
	 * {@inheritdoc}
	*/
	public function index_stats()
	{
		if (empty($this->stats))
		{
			$this->get_stats();
		}

		return array(
			$this->language->lang('FULLTEXT_POSTGRES_TOTAL_POSTS')			=> ($this->index_created()) ? $this->stats['total_posts'] : 0,
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
				else if ($row['relname'] == POSTS_TABLE . '_' . $this->config['fulltext_postgres_ts_name'] . '_post_subject_content' || $row['relname'] == POSTS_TABLE . '_post_subject_content')
				{
					$this->stats['post_subject_content'] = $row;
				}
			}
		}
		$this->db->sql_freeresult($result);

		$this->stats['total_posts'] = $this->config['num_posts'];
	}

	/**
	 * Turns text into an array of words
	 * @param string $text contains post text/subject
	 * @return array
	 */
	protected function split_message($text)
	{
		// Split words
		$text = preg_replace('#([^\p{L}\p{N}\'*])#u', '$1$1', str_replace('\'\'', '\' \'', trim($text)));
		$matches = array();
		preg_match_all('#(?:[^\p{L}\p{N}*]|^)([+\-|]?(?:[\p{L}\p{N}*]+\'?)*[\p{L}\p{N}*])(?:[^\p{L}\p{N}*]|$)#u', $text, $matches);
		$text = $matches[1];

		// remove too short or too long words
		$text = array_values($text);
		for ($i = 0, $n = count($text); $i < $n; $i++)
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
	 * {@inheritdoc}
	 */
	public function get_acp_options(): array
	{
		$tpl = '
		<dl>
			<dt><label>' . $this->language->lang('FULLTEXT_POSTGRES_VERSION_CHECK') . '</label><br /><span>' . $this->language->lang('FULLTEXT_POSTGRES_VERSION_CHECK_EXPLAIN') . '</span></dt>
			<dd>' . (($this->db->get_sql_layer() == 'postgres') ? $this->language->lang('YES') : $this->language->lang('NO')) . '</dd>
		</dl>
		<dl>
			<dt><label>' . $this->language->lang('FULLTEXT_POSTGRES_TS_NAME') . '</label><br /><span>' . $this->language->lang('FULLTEXT_POSTGRES_TS_NAME_EXPLAIN') . '</span></dt>
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
                        <dt><label for="fulltext_postgres_min_word_len">' . $this->language->lang('FULLTEXT_POSTGRES_MIN_WORD_LEN') . $this->language->lang('COLON') . '</label><br /><span>' . $this->language->lang('FULLTEXT_POSTGRES_MIN_WORD_LEN_EXPLAIN') . '</span></dt>
                        <dd><input id="fulltext_postgres_min_word_len" type="number" min="0" max="255" name="config[fulltext_postgres_min_word_len]" value="' . (int) $this->config['fulltext_postgres_min_word_len'] . '" /></dd>
                </dl>
                <dl>
                        <dt><label for="fulltext_postgres_max_word_len">' . $this->language->lang('FULLTEXT_POSTGRES_MAX_WORD_LEN') . $this->language->lang('COLON') . '</label><br /><span>' . $this->language->lang('FULLTEXT_POSTGRES_MAX_WORD_LEN_EXPLAIN') . '</span></dt>
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
