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
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @ignore
*/
include_once($phpbb_root_path . 'includes/search/search.' . $phpEx);

/**
* fulltext_mysql
* Fulltext search for MySQL
* @package search
*/
class fulltext_mysql extends search_backend
{
	var $stats = array();
	var $word_length = array();
	var $split_words = array();
	var $search_query;
	var $common_words = array();
	var $pcre_properties = false;
	var $mbstring_regex = false;

	function fulltext_mysql(&$error)
	{
		global $config;

		$this->word_length = array('min' => $config['fulltext_mysql_min_word_len'], 'max' => $config['fulltext_mysql_max_word_len']);

		if (version_compare(PHP_VERSION, '5.1.0', '>=') || (version_compare(PHP_VERSION, '5.0.0-dev', '<=') && version_compare(PHP_VERSION, '4.4.0', '>=')))
		{
			$this->pcre_properties = true;
		}

		if (function_exists('mb_ereg'))
		{
			$this->mbstring_regex = true;
		}

		$error = false;
	}

	/**
	* Checks for correct MySQL version and stores min/max word length in the config
	*/
	function init()
	{
		global $db, $user;

		if ($db->sql_layer != 'mysql4' && $db->sql_layer != 'mysqli')
		{
			return $user->lang['FULLTEXT_MYSQL_INCOMPATIBLE_VERSION'];
		}

		$result = $db->sql_query('SHOW TABLE STATUS LIKE \'' . POSTS_TABLE . '\'');
		$info = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$engine = '';
		if (isset($info['Engine']))
		{
			$engine = $info['Engine'];
		}
		else if (isset($info['Type']))
		{
			$engine = $info['Type'];
		}

		if ($engine != 'MyISAM')
		{
			return $user->lang['FULLTEXT_MYSQL_NOT_MYISAM'];
		}

		$sql = 'SHOW VARIABLES
			LIKE \'ft\_%\'';
		$result = $db->sql_query($sql);

		$mysql_info = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$mysql_info[$row['Variable_name']] = $row['Value'];
		}
		$db->sql_freeresult($result);

		set_config('fulltext_mysql_max_word_len', $mysql_info['ft_max_word_len']);
		set_config('fulltext_mysql_min_word_len', $mysql_info['ft_min_word_len']);

		return false;
	}

	/**
	* Splits keywords entered by a user into an array of words stored in $this->split_words
	* Stores the tidied search query in $this->search_query
	*
	* @param string &$keywords Contains the keyword as entered by the user
	* @param string $terms is either 'all' or 'any'
	* @return bool false if no valid keywords were found and otherwise true
	*/
	function split_keywords(&$keywords, $terms)
	{
		global $config;

		if ($terms == 'all')
		{
			$match		= array('#\sand\s#iu', '#\sor\s#iu', '#\snot\s#iu', '#\+#', '#-#', '#\|#');
			$replace	= array(' +', ' |', ' -', ' +', ' -', ' |');

			$keywords = preg_replace($match, $replace, $keywords);
		}

		$match = array();
		// New lines, carriage returns
		$match[] = "#[\n\r]+#";
		// NCRs like &nbsp; etc.
		$match[] = '#(&amp;|&)[\#a-z0-9]+?;#i';

		// Filter out as above
		$keywords = preg_replace($match, ' ', trim($keywords));

		// Split words
		if ($this->pcre_properties)
		{
			$split_keywords = preg_replace('#([^\p{L}\p{N}\'*])#u', '$1$1', str_replace('\'\'', '\' \'', trim($keywords)));
		}
		else if ($this->mbstring_regex)
		{
			$split_keywords = mb_ereg_replace('([^\w\'*])', '\\1\\1', str_replace('\'\'', '\' \'', trim($keywords)));
		}
		else
		{
			$split_keywords = preg_replace('#([^\w\'*])#u', '$1$1', str_replace('\'\'', '\' \'', trim($keywords)));
		}

		if ($this->pcre_properties)
		{
			$matches = array();
			preg_match_all('#(?:[^\p{L}\p{N}*]|^)([+\-|]?(?:[\p{L}\p{N}*]+\'?)*[\p{L}\p{N}*])(?:[^\p{L}\p{N}*]|$)#u', $split_keywords, $matches);
			$this->split_words = $matches[1];
		}
		else if ($this->mbstring_regex)
		{
			mb_regex_encoding('UTF-8');
			mb_ereg_search_init($split_keywords, '(?:[^\w*]|^)([+\-|]?(?:[\w*]+\'?)*[\w*])(?:[^\w*]|$)');

			while (($word = mb_ereg_search_regs()))
			{
				$this->split_words[] = $word[1];
			}
		}
		else
		{
			$matches = array();
			preg_match_all('#(?:[^\w*]|^)([+\-|]?(?:[\w*]+\'?)*[\w*])(?:[^\w*]|$)#u', $split_keywords, $matches);
			$this->split_words = $matches[1];
		}

		foreach ($this->split_words as $i => $word)
		{
			$clean_word = preg_replace('#^[+\-|]#', '', $word);

			// check word length
			$clean_len = utf8_strlen(str_replace('*', '', $clean_word));
			if (($clean_len < $config['fulltext_mysql_min_word_len']) || ($clean_len > $config['fulltext_mysql_max_word_len']))
			{
				$this->common_words[] = $word;
				unset($this->split_words[$i]);
			}
		}

		$this->search_query = implode(' ', $this->split_words);

		if (sizeof($this->split_words))
		{
			$this->split_words = array_values($this->split_words);
			sort($this->split_words);
			return true;
		}
		return false;
	}

	/**
	* Turns text into an array of words
	*/
	function split_message($text)
	{
		global $config;

		// Split words
		if ($this->pcre_properties)
		{
			$text = preg_replace('#([^\p{L}\p{N}\'*])#u', '$1$1', str_replace('\'\'', '\' \'', trim($text)));
		}
		else if ($this->mbstring_regex)
		{
			$text = mb_ereg_replace('([^\w\'*])', '\\1\\1', str_replace('\'\'', '\' \'', trim($text)));
		}
		else
		{
			$text = preg_replace('#([^\w\'*])#u', '$1$1', str_replace('\'\'', '\' \'', trim($text)));
		}

		if ($this->pcre_properties)
		{
			$matches = array();
			preg_match_all('#(?:[^\p{L}\p{N}*]|^)([+\-|]?(?:[\p{L}\p{N}*]+\'?)*[\p{L}\p{N}*])(?:[^\p{L}\p{N}*]|$)#u', $text, $matches);
			$text = $matches[1];
		}
		else if ($this->mbstring_regex)
		{
			mb_regex_encoding('UTF-8');
			mb_ereg_search_init($text, '(?:[^\w*]|^)([+\-|]?(?:[\w*]+\'?)*[\w*])(?:[^\w*]|$)');

			$text = array();
			while (($word = mb_ereg_search_regs()))
			{
				$text[] = $word[1];
			}
		}
		else
		{
			$matches = array();
			preg_match_all('#(?:[^\w*]|^)([+\-|]?(?:[\w*]+\'?)*[\w*])(?:[^\w*]|$)#u', $text, $matches);
			$text = $matches[1];
		}

		// remove too short or too long words
		$text = array_values($text);
		for ($i = 0, $n = sizeof($text); $i < $n; $i++)
		{
			$text[$i] = trim($text[$i]);
			if (utf8_strlen($text[$i]) < $config['fulltext_mysql_min_word_len'] || utf8_strlen($text[$i]) > $config['fulltext_mysql_max_word_len'])
			{
				unset($text[$i]);
			}
		}

		return array_values($text);
	}

	/**
	* Performs a search on keywords depending on display specific params.
	*
	* @param array &$id_ary passed by reference, to be filled with ids for the page specified by $start and $per_page, should be ordered
	* @param int $start indicates the first index of the page
	* @param int $per_page number of ids each page is supposed to contain
	* @return total number of results
	*/
	function keyword_search($type, &$fields, &$terms, &$sort_by_sql, &$sort_key, &$sort_dir, &$sort_days, &$ex_fid_ary, &$m_approve_fid_ary, &$topic_id, &$author_ary, &$id_ary, $start, $per_page)
	{
		global $config, $db;

		// No keywords? No posts.
		if (!sizeof($this->split_words))
		{
			return false;
		}

		// generate a search_key from all the options to identify the results
		$search_key = md5(implode('#', array(
			implode(',', $this->split_words),
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
			$m_approve_fid_sql = ' AND (p.post_approved = 1 OR ' . $db->sql_in_set('p.forum_id', $m_approve_fid_ary, true) . ')';
		}

		$sql_select			= (!$result_count) ? 'SQL_CALC_FOUND_ROWS ' : '';
		$sql_select			= ($type == 'posts') ? $sql_select . 'p.post_id' : 'DISTINCT ' . $sql_select . 't.topic_id';
		$sql_from			= ($join_topic) ? TOPICS_TABLE . ' t, ' : '';
		$field				= ($type == 'posts') ? 'post_id' : 'topic_id';
		$sql_author			= (sizeof($author_ary) == 1) ? ' = ' . $author_ary[0] : 'IN (' . implode(', ', $author_ary) . ')';

		$sql_where_options = $sql_sort_join;
		$sql_where_options .= ($topic_id) ? ' AND p.topic_id = ' . $topic_id : '';
		$sql_where_options .= ($join_topic) ? ' AND t.topic_id = p.topic_id' : '';
		$sql_where_options .= (sizeof($ex_fid_ary)) ? ' AND ' . $db->sql_in_set('p.forum_id', $ex_fid_ary, true) : '';
		$sql_where_options .= $m_approve_fid_sql;
		$sql_where_options .= (sizeof($author_ary)) ? ' AND p.poster_id ' . $sql_author : '';
		$sql_where_options .= ($sort_days) ? ' AND p.post_time >= ' . (time() - ($sort_days * 86400)) : '';
		$sql_where_options .= $sql_match_where;

		// split the words into words that have to be included or must not be included and optional words
		$words = $any_words = array();
		if ($terms == 'all')
		{
			foreach ($this->split_words as $id => $word)
			{
				if ((strpos($word, '+') === 0) || (strpos($word, '-') === 0))
				{
					$words[] = $word;
				}
				else if (strpos($word, '|') === 0)
				{
					$any_words[] = substr($word, 1);
				}
				else
				{
					$words[] = '+' . $word;
				}
			}
		}
		else
		{
			foreach ($this->split_words as $id => $word)
			{
				if ((strpos($word, '+') === 0) || (strpos($word, '-') === 0) || (strpos($word, '|') === 0))
				{
					$words[] = substr($word, 1);
				}
				else
				{
					$words[] = $word;
				}
			}
		}

		// Get the ids for the current result block
		$any_words = (sizeof($any_words)) ? ' +(' . implode(' ', $any_words) . ')' : '';

		$sql = "SELECT $sql_select
			FROM $sql_from$sql_sort_table" . POSTS_TABLE . " p
			WHERE MATCH ($sql_match) AGAINST ('" . $db->sql_escape(implode(' ', $words)) . $any_words . "' IN BOOLEAN MODE)
				$sql_where_options
			ORDER BY $sql_sort";
		$result = $db->sql_query_limit($sql, $config['search_block_size'], $start);

		while ($row = $db->sql_fetchrow($result))
		{
			$id_ary[] = $row[$field];
		}
		$db->sql_freeresult($result);

		$id_ary = array_unique($id_ary);

		if (!sizeof($id_ary))
		{
			return false;
		}

		// if the total result count is not cached yet, retrieve it from the db
		if (!$result_count)
		{
			$sql = 'SELECT FOUND_ROWS() as result_count';
			$result = $db->sql_query($sql);
			$result_count = (int) $db->sql_fetchfield('result_count');
			$db->sql_freeresult($result);

			if (!$result_count)
			{
				return false;
			}
		}

		// store the ids, from start on then delete anything that isn't on the current page because we only need ids for one page
		$this->save_ids($search_key, implode(' ', $this->split_words), $author_ary, $result_count, $id_ary, $start, $sort_dir);
		$id_ary = array_slice($id_ary, 0, (int) $per_page);

		return $result_count;
	}

	/**
	* Performs a search on an author's posts without caring about message contents. Depends on display specific params
	*
	* @param array &$id_ary passed by reference, to be filled with ids for the page specified by $start and $per_page, should be ordered
	* @param int $start indicates the first index of the page
	* @param int $per_page number of ids each page is supposed to contain
	* @return total number of results
	*/
	function author_search($type, &$sort_by_sql, &$sort_key, &$sort_dir, &$sort_days, &$ex_fid_ary, &$m_approve_fid_ary, &$topic_id, &$author_ary, &$id_ary, $start, $per_page)
	{
		global $config, $db;

		// No author? No posts.
		if (!sizeof($author_ary))
		{
			return 0;
		}

		// generate a search_key from all the options to identify the results
		$search_key = md5(implode('#', array(
			'',
			$type,
			'',
			'',
			$sort_days,
			$sort_key,
			$topic_id,
			implode(',', $ex_fid_ary),
			implode(',', $m_approve_fid_ary),
			implode(',', $author_ary)
		)));

		// try reading the results from cache
		$result_count = 0;
		if ($this->obtain_ids($search_key, $result_count, $id_ary, $start, $per_page, $sort_dir) == SEARCH_RESULT_IN_CACHE)
		{
			return $result_count;
		}

		$id_ary = array();

		// Create some display specific sql strings
		$sql_author		= $db->sql_in_set('p.poster_id', $author_ary);
		$sql_fora		= (sizeof($ex_fid_ary)) ? ' AND ' . $db->sql_in_set('p.forum_id', $ex_fid_ary, true) : '';
		$sql_topic_id	= ($topic_id) ? ' AND p.topic_id = ' . (int) $topic_id : '';
		$sql_time		= ($sort_days) ? ' AND p.post_time >= ' . (time() - ($sort_days * 86400)) : '';

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
				$sql_sort_table	= ($type == 'posts') ? TOPICS_TABLE . ' t, ' : '';
				$sql_sort_join	= ($type == 'posts') ? ' AND t.topic_id = p.topic_id ' : '';
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
			$m_approve_fid_sql = ' AND (p.post_approved = 1 OR ' . $db->sql_in_set('p.forum_id', $m_approve_fid_ary, true) . ')';
		}

		// If the cache was completely empty count the results
		$calc_results = ($result_count) ? '' : 'SQL_CALC_FOUND_ROWS ';

		// Build the query for really selecting the post_ids
		if ($type == 'posts')
		{
			$sql = "SELECT {$calc_results}p.post_id
				FROM " . $sql_sort_table . POSTS_TABLE . " p
				WHERE $sql_author
					$sql_topic_id
					$m_approve_fid_sql
					$sql_fora
					$sql_sort_join
					$sql_time
				ORDER BY $sql_sort";
			$field = 'post_id';
		}
		else
		{
			$sql = "SELECT {$calc_results}t.topic_id
				FROM " . $sql_sort_table . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
				WHERE $sql_author
					$sql_topic_id
					$m_approve_fid_sql
					$sql_fora
					AND t.topic_id = p.topic_id
					$sql_sort_join
					$sql_time
				GROUP BY t.topic_id
				ORDER BY $sql_sort";
			$field = 'topic_id';
		}

		// Only read one block of posts from the db and then cache it
		$result = $db->sql_query_limit($sql, $config['search_block_size'], $start);

		while ($row = $db->sql_fetchrow($result))
		{
			$id_ary[] = $row[$field];
		}
		$db->sql_freeresult($result);

		// retrieve the total result count if needed
		if (!$result_count)
		{
			$sql = 'SELECT FOUND_ROWS() as result_count';
			$result = $db->sql_query($sql);
			$result_count = (int) $db->sql_fetchfield('result_count');
			$db->sql_freeresult($result);

			if (!$result_count)
			{
				return false;
			}
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
	* Destroys cached search results, that contained one of the new words in a post so the results won't be outdated.
	*
	* @param string $mode contains the post mode: edit, post, reply, quote ...
	*/
	function index($mode, $post_id, &$message, &$subject, $poster_id, $forum_id)
	{
		global $db;

		// Split old and new post/subject to obtain array of words
		$split_text = $this->split_message($message);
		$split_title = ($subject) ? $this->split_message($subject) : array();

		$words = array();
		if ($mode == 'edit')
		{
			$old_text = array();
			$old_title = array();

			$sql = 'SELECT post_text, post_subject
				FROM ' . POSTS_TABLE . "
				WHERE post_id = $post_id";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				$old_text = $this->split_message($row['post_text']);
				$old_title = $this->split_message($row['post_subject']);
			}
			$db->sql_freeresult($result);

			$words = array_unique(array_merge(
				array_diff($split_text, $old_text), 
				array_diff($split_title, $old_title),
				array_diff($old_text, $split_text),
				array_diff($old_title, $split_title)
			));
			unset($old_title);
			unset($old_text);
		}
		else
		{
			$words = array_unique(array_merge($split_text, $split_title));
		}
		unset($split_text);
		unset($split_title);

		// destroy cached search results containing any of the words removed or added
		$this->destroy_cache($words, array($poster_id));

		unset($words);
	}

	/**
	* Destroy cached results, that might be outdated after deleting a post
	*/
	function index_remove($post_ids, $author_ids, $forum_ids)
	{
		$this->destroy_cache(array(), $author_ids);
	}

	/**
	* Destroy old cache entries
	*/
	function tidy()
	{
		global $db, $config;

		// destroy too old cached search results
		$this->destroy_cache(array());

		set_config('search_last_gc', time(), true);
	}

	/**
	* Create fulltext index
	*/
	function create_index($acp_module, $u_action)
	{
		global $db;

		// Make sure we can actually use MySQL with fulltext indexes
		if ($error = $this->init())
		{
			return $error;
		}

		if (empty($this->stats))
		{
			$this->get_stats();
		}

		$alter = array();

		if (!isset($this->stats['post_subject']))
		{
			if ($db->sql_layer == 'mysqli' || version_compare($db->mysql_version, '4.1.3', '>='))
			{
				$alter[] = 'MODIFY post_subject varchar(100) COLLATE utf8_unicode_ci DEFAULT \'\' NOT NULL';
			}
			$alter[] = 'ADD FULLTEXT (post_subject)';
		}

		if (!isset($this->stats['post_text']))
		{
			if ($db->sql_layer == 'mysqli' || version_compare($db->mysql_version, '4.1.3', '>='))
			{
				$alter[] = 'MODIFY post_text mediumtext COLLATE utf8_unicode_ci NOT NULL';
			}
			$alter[] = 'ADD FULLTEXT (post_text)';
		}

		if (sizeof($alter))
		{
			$db->sql_query('ALTER TABLE ' . POSTS_TABLE . ' ' . implode(', ', $alter));
		}

		$db->sql_query('TRUNCATE TABLE ' . SEARCH_RESULTS_TABLE);

		return false;
	}

	/**
	* Drop fulltext index
	*/
	function delete_index($acp_module, $u_action)
	{
		global $db;

		// Make sure we can actually use MySQL with fulltext indexes
		if ($error = $this->init())
		{
			return $error;
		}

		if (empty($this->stats))
		{
			$this->get_stats();
		}

		$alter = array();

		if (isset($this->stats['post_subject']))
		{
			$alter[] = 'DROP INDEX post_subject';
		}

		if (isset($this->stats['post_text']))
		{
			$alter[] = 'DROP INDEX post_text';
		}

		if (sizeof($alter))
		{
			$db->sql_query('ALTER TABLE ' . POSTS_TABLE . ' ' . implode(', ', $alter));
		}

		$db->sql_query('TRUNCATE TABLE ' . SEARCH_RESULTS_TABLE);

		return false;
	}

	/**
	* Returns true if both FULLTEXT indexes exist
	*/
	function index_created()
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
	function index_stats()
	{
		global $user;

		if (empty($this->stats))
		{
			$this->get_stats();
		}

		return array(
			$user->lang['FULLTEXT_MYSQL_TOTAL_POSTS']			=> ($this->index_created()) ? $this->stats['total_posts'] : 0,
			$user->lang['FULLTEXT_MYSQL_TEXT_CARDINALITY']		=> isset($this->stats['post_text']['Cardinality']) ? $this->stats['post_text']['Cardinality'] : 0,
			$user->lang['FULLTEXT_MYSQL_SUBJECT_CARDINALITY']	=> isset($this->stats['post_subject']['Cardinality']) ? $this->stats['post_subject']['Cardinality'] : 0,
		);
	}

	function get_stats()
	{
		global $db;

		if (strpos($db->sql_layer, 'mysql') === false)
		{
			$this->stats = array();
			return;
		}

		$sql = 'SHOW INDEX
			FROM ' . POSTS_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			// deal with older MySQL versions which didn't use Index_type
			$index_type = (isset($row['Index_type'])) ? $row['Index_type'] : $row['Comment'];

			if ($index_type == 'FULLTEXT')
			{
				if ($row['Column_name'] == 'post_text')
				{
					$this->stats['post_text'] = $row;
				}
				else if ($row['Column_name'] == 'post_subject')
				{
					$this->stats['post_subject'] = $row;
				}
			}
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT COUNT(post_id) as total_posts
			FROM ' . POSTS_TABLE;
		$result = $db->sql_query($sql);
		$this->stats['total_posts'] = (int) $db->sql_fetchfield('total_posts');
		$db->sql_freeresult($result);
	}

	/**
	* Display a note, that UTF-8 support is not available with certain versions of PHP
	*/
	function acp()
	{
		global $user, $config;

		$tpl = '
		<dl>
			<dt><label>' . $user->lang['FULLTEXT_MYSQL_PCRE'] . '</label><br /><span>' . $user->lang['FULLTEXT_MYSQL_PCRE_EXPLAIN'] . '</span></dt>
			<dd>' . (($this->pcre_properties) ? $user->lang['YES'] : $user->lang['NO']) . ' (PHP ' . PHP_VERSION . ')</dd>
		</dl>
		<dl>
			<dt><label>' . $user->lang['FULLTEXT_MYSQL_MBSTRING'] . '</label><br /><span>' . $user->lang['FULLTEXT_MYSQL_MBSTRING_EXPLAIN'] . '</span></dt>
			<dd>' . (($this->mbstring_regex) ? $user->lang['YES'] : $user->lang['NO']). '</dd>
		</dl>
		';

		// These are fields required in the config table
		return array(
			'tpl'		=> $tpl,
			'config'	=> array()
		);
	}
}

?>