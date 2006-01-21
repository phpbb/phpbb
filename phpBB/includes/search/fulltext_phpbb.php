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
include($phpbb_root_path . 'includes/search/search.' . $phpEx);

/**
* @package search
* fulltext_phpbb
* phpBB's own db driven fulltext search
*/
class fulltext_phpbb extends search_backend
{
	function fulltext_phpbb(&$error)
	{
		$error = false;
	}

	/**
	* Splits keywords entered by a user into an array of words stored in $this->split_words
	*
	* @param string $keywords Contains the keyword as entered by the user
	* @param string $terms is either 'all' or 'any'
	* @return false if no valid keywords were found and otherwise true
	*/
	function split_keywords(&$keywords, $terms)
	{
		global $db, $config;

		$drop_char_match =   array('^', '$', ';', '#', '&', '(', ')', '<', '>', '`', '\'', '"', ',', '@', '_', '?', '%', '~', '.', '[', ']', '{', '}', ':', '\\', '/', '=', '\'', '!');
		$drop_char_replace = array(' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '',  '',   ' ', ' ', ' ', '',  ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '' ,  ' ', ' ', ' ',  ' ');

		$this->get_ignore_words();
		$this->get_synonyms();

		if ($terms == 'all')
		{
			$match		= array('#\sand\s#i', '#\sor\s#i', '#\snot\s#i', '#\+#', '#-#', '#\|#');
			$replace	= array(' + ', ' | ', ' - ', ' + ', ' - ', ' | ');

			$keywords = preg_replace($match, $replace, $keywords);
		}

		$match = array();
		// New lines, carriage returns
		$match[] = "#[\n\r]+#";
		// NCRs like &nbsp; etc.
		$match[] = '#(&amp;|&)[\#a-z0-9]+?;#i';

		// Filter out as above
		$keywords = preg_replace($match, ' ', strtolower(trim($keywords)));
		$keywords = str_replace($drop_char_match, $drop_char_replace, $keywords);

		// Split words
		$this->split_words = explode(' ', preg_replace('#\s+#', ' ', $keywords));

		if (sizeof($this->ignore_words))
		{
			$this->common_words = array_intersect($this->split_words, $this->ignore_words);
			$this->split_words = array_diff($this->split_words, $this->ignore_words);
		}

		if (sizeof($this->replace_synonym))
		{
			$this->split_words = str_replace($this->replace_synonym, $this->match_synonym, $this->split_words);
		}

		$prefixes = array('+', '-', '|');
		$prefixed = false;
		$in_words = '';
		foreach ($this->split_words as $i => $word)
		{
			if (in_array($word, $prefixes))
			{
				$prefixed = true;
				continue;
			}

			// check word length
			$clean_len = strlen(str_replace('*', '', $word));
			if (($clean_len < $config['min_search_chars']) || ($clean_len > $config['max_search_chars']))
			{
				if ($prefixed)
				{
					$this->common_words[] = $this->split_words[$i - 1];
					unset($this->split_words[$i - 1]);
				}
				$this->common_words[] = $this->split_words[$i];
				unset($this->split_words[$i]);
			}
			else if (strpos($word, '*') === false)
			{
				$in_words .= (($in_words) ? ', ' : '') . '\'' . $db->sql_escape($word) . '\'';
			}

			$prefixed = false;
		}

		if ($in_words)
		{
			// identify common words and ignore them
			$sql = 'SELECT word_text
				FROM ' . SEARCH_WORD_TABLE . "
				WHERE word_text IN ($in_words)
					AND word_common = 1";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$key = array_search($row['word_text'], $this->split_words);

				if (isset($this->split_words[$key - 1]) && (in_array($this->split_words[$key - 1], $prefixes)))
				{
					$this->common_words[] = $this->split_words[$key - 1];
					unset($this->split_words[$key - 1]);
				}
				$this->common_words[] = $row['word_text'];
				unset($this->split_words[$key]);
			}
			$db->sql_freeresult($result);
		}

		if (sizeof($this->split_words))
		{
			$this->split_words = array_values($this->split_words);
			return true;
		}
		return false;
	}

	/**
	* Turns text into an array of words that can be stored in the word list table
	*/
	function split_message($text)
	{
		global $config;

		static $drop_char_match, $drop_char_replace;

		$this->get_ignore_words();
		$this->get_synonyms();

		if (!is_array($drop_char_match))
		{
			$drop_char_match =   array('-', '^', '$', ';', '#', '&', '(', ')', '<', '>', '`', '\'', '"', '|', ',', '@', '_', '?', '%', '~', '.', '[', ']', '{', '}', ':', '\\', '/', '=', '\'', '!', '*', '+');
			$drop_char_replace = array(' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '',  '',   ' ', ' ', ' ', ' ', '',  ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '' ,  ' ', ' ', ' ',  ' ', ' ', ' ');
		}

		$match = array();
		// Comments for hardcoded bbcode elements (urls, smilies, html)
		$match[] = '#<!\-\- .* \-\->(.*?)<!\-\- .* \-\->#is';
		// New lines, carriage returns
		$match[] = "#[\n\r]+#";
		// NCRs like &nbsp; etc.
		$match[] = '#(&amp;|&)[\#a-z0-9]+?;#i';
		// Do not index code
		$match[] = '#\[code(?:=.*?)?(\:?[0-9a-z]{5,})\].*?\[\/code(\:?[0-9a-z]{5,})\]#is';
		// BBcode
		$match[] = '#\[\/?[a-z\*\+\-]+(?:=.*?)?(\:?[0-9a-z]{5,})\]#';
		// Filter out ; and # but not &#[0-9]+;
		//$match[] = '#(&\#[0-9]+;)|;|\#|&#';

		$text = preg_replace($match, ' ', ' ' . strtolower(trim($text)) . ' ');

		// Filter out non-alphabetical chars
		$text = str_replace($drop_char_match, $drop_char_replace, $text);

		// Split words
		$text = explode(' ', preg_replace('#\s+#', ' ', trim($text)));

		if (sizeof($this->ignore_words))
		{
			$stopped_words = array_intersect($text, $this->ignore_words);
			$text = array_diff($text, $this->ignore_words);
		}

		if (sizeof($this->replace_synonym))
		{
			$text = str_replace($this->replace_synonym, $this->match_synonym, $text);
		}

		// remove too short or too long words
		$text = array_values($text);
		for ($i = 0, $n = sizeof($text); $i < $n; $i++)
		{
			$text[$i] = trim($text[$i]);
			if (strlen($text[$i]) < $config['min_search_chars'] || strlen($text[$i]) > $config['max_search_chars'])
			{
				unset($text[$i]);
			}
		}

		return $text;
	}

	/**
	* Performs a search on keywords depending on display specific params.
	*
	* @param array $id_ary passed by reference, to be filled with ids for the page specified by $start and $per_page, should be ordered
	* @param int $start indicated the first index of the page
	* @param int $per_page number of ids each page is supposed to contain
	* @return total number of results
	*/
	function keyword_search($type, &$fields, &$terms, &$sort_by_sql, &$sort_key, &$sort_dir, &$sort_days, &$ex_fid_ary, &$topic_id, &$author_ary, &$id_ary, $start, $per_page)
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
			implode(',', $author_ary)
		)));

		// try reading the results from cache
		$result_count = 0;
		if ($this->obtain_ids($search_key, $result_count, $id_ary, $start, $per_page, $sort_dir) == SEARCH_RESULT_IN_CACHE)
		{
			return $result_count;
		}

		$result_count = 0;
		$id_ary = array();

		$join_topic = ($type == 'posts') ? false : true;
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
				$sql_match = ' AND m.title_match = 1 AND p.post_id = t.topic_first_post_id';
				$join_topic = true;
				break;
			case 'msgonly':
				$sql_match = ' AND m.title_match = 0';
				break;
			case 'firstpost':
				$sql_match = ' AND p.post_id = t.topic_first_post_id';
				$join_topic = true;
				break;
			default:
				$sql_match = '';
		}

		$sql_select			= ($type == 'posts') ? 'm.post_id' : 'DISTINCT t.topic_id';
		$sql_from			= ($join_topic) ? TOPICS_TABLE . ' t, ' : '';
		$field				= ($type == 'posts') ? 'm.post_id' : 't.topic_id';
		$sql_author			= (sizeof($author_ary) == 1) ? ' = ' . $author_ary[0] : 'IN (' . implode(',', $author_ary) . ')';

		$sql_where_options = $sql_sort_join;
		$sql_where_options .= ($topic_id) ? ' AND p.topic_id = ' . $topic_id : '';
		$sql_where_options .= ($join_topic) ? ' AND t.topic_id = p.topic_id' : '';
		$sql_where_options .= (sizeof($ex_fid_ary)) ? ' AND p.forum_id NOT IN (' . implode(',', $ex_fid_ary) . ')' : '';
		$sql_where_options .= (sizeof($author_ary)) ? ' AND p.poster_id ' . $sql_author : '';
		$sql_where_options .= ($sort_days) ? ' AND p.post_time >= ' . (time() - ($sort_days * 86400)) : '';
		$sql_where_options .= $sql_match;

		// split the words into three arrays (AND, OR, NOT)
		$sql_words = array('AND' => array(), 'OR' => array(), 'NOT' => array());
		$bool = ($terms == 'all') ? 'AND' : 'OR';

		foreach ($this->split_words as $word)
		{
			switch ($word)
			{
				case '-':
					$bool = 'NOT';
					continue;
				case '+':
					$bool = 'AND';
					continue;
				case '|':
					$bool = 'OR';
					continue;
				default:
					$bool = ($terms != 'all') ? 'OR' : $bool;
					$sql_words[$bool][] = "'" . $db->sql_escape(preg_replace('#\*+#', '%', trim($word))) . "'";
					$bool = ($terms == 'all') ? 'AND' : 'OR';
			}
		}

		// Select all post_ids that contain all AND-words
		$result_ary= array('AND' => array(), 'OR' => array(), 'NOT' => array());
		if (sizeof($sql_words['AND']))
		{
			$sql_in = '';
			foreach ($sql_words['AND'] as $word)
			{
				// first select all post ids that match a word containing a wildcard
				if (strstr($word, '%'))
				{
					$sql = "SELECT $sql_select
						FROM $sql_from$sql_sort_table" . POSTS_TABLE . ' p, ' . SEARCH_MATCH_TABLE . ' m, ' . SEARCH_WORD_TABLE . " w
						WHERE w.word_text LIKE $word
							AND m.word_id = w.word_id
							AND w.word_common <> 1
							AND p.post_id = m.post_id
							$sql_where_options
						GROUP BY $field
						ORDER BY $sql_sort";
					$result = $db->sql_query($sql);

					if (!($row = $db->sql_fetchrow($result)))
					{
						$id_ary = array();
						return false;
					}

					$ids = array();
					do
					{
						$ids[] = ($type == 'topics') ? $row['topic_id'] : $row['post_id'];
					}
					while ($row = $db->sql_fetchrow($result));
					$db->sql_freeresult($result);

					// remove ids that are not present in all AND-word results
					if (sizeof($result_ary['AND']))
					{
						$result_ary['AND'] = array_intersect($result_ary['AND'], $ids);
					}
					else
					{
						$result_ary['AND'] = $ids;
					}
					unset($ids);
				}
				else
				{
					$sql_in .= (($sql_in) ? ', ' : '') . $word;
				}
			}

			if ($sql_in)
			{
				$sql = "SELECT $sql_select, COUNT(DISTINCT m.word_id) as matches
					FROM $sql_from$sql_sort_table" . POSTS_TABLE . ' p, ' . SEARCH_MATCH_TABLE . ' m, ' . SEARCH_WORD_TABLE . " w
					WHERE w.word_text IN ($sql_in)
						AND m.word_id = w.word_id
						AND w.word_common <> 1
						AND p.post_id = m.post_id
						$sql_where_options
					GROUP BY $field
					ORDER BY $sql_sort";
				$result = $db->sql_query($sql);

				if (!($row = $db->sql_fetchrow($result)))
				{
					$id_ary = array();
					return false;
				}

				// A little trick so we only need one query: using DISTINCT makes every word unique so if the
				// number of all words for one post_id equals the number of AND-words it has to contain all
				// AND-words
				$ids = array();
				do
				{
					if ($row['matches'] == sizeof($sql_words['AND']))
					{
						$ids[] = ($type == 'topics') ? $row['topic_id'] : $row['post_id'];
					}
				}
				while ($row = $db->sql_fetchrow($result));
				$db->sql_freeresult($result);

				// remove ids that are not present in all AND-word results
				if (sizeof($result_ary['AND']))
				{
					$result_ary['AND'] = array_intersect($result_ary['AND'], $ids);
				}
				else
				{
					$result_ary['AND'] = $ids;
				}
				unset($ids);
			}
		}

		// Select all post_ids that contain one of the OR-words
		if (sizeof($sql_words['OR']))
		{
			$sql_where = $sql_in = '';
			foreach ($sql_words['OR'] as $word)
			{
				if (strstr($word, '%'))
				{
					$sql_where .= (($sql_where) ? ' OR w.word_text ' : 'w.word_text ') . "LIKE $word";
				}
				else
				{
					$sql_in .= (($sql_in) ? ', ' : '') . $word;
				}
			}
			$sql_where = ($sql_in) ? $sql_where . (($sql_where) ? ' OR ' : '') . 'w.word_text IN (' . $sql_in . ')' : $sql_where;

			$sql_and = (sizeof($result_ary['AND'])) ? "AND $field IN (" . implode(', ', $result_ary['AND']) . ')' : '';
			$sql = "SELECT $sql_select
				FROM $sql_from$sql_sort_table" . POSTS_TABLE . ' p, ' . SEARCH_MATCH_TABLE . ' m, ' . SEARCH_WORD_TABLE . " w
				WHERE ($sql_where)
					AND m.word_id = w.word_id
					AND w.word_common <> 1
					AND p.post_id = m.post_id
					$sql_where_options
				ORDER BY $sql_sort";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$result_ary['OR'][] = ($type == 'topics') ? $row['topic_id'] : $row['post_id'];
			}
			$db->sql_freeresult($result);
		}

		// remove post_ids that do not contain any OR-word
		if (sizeof($result_ary['OR']))
		{
			$id_ary = (sizeof($result_ary['AND'])) ? array_intersect($result_ary['AND'], $result_ary['OR']) : $result_ary['OR'];
		}
		else
		{
			$id_ary = (sizeof($result_ary['AND'])) ? $result_ary['AND'] : array();
		}

		unset($result_ary['AND']);
		unset($result_ary['OR']);

		// remove all post_ids that contain a NOT-word
		if (sizeof($sql_words['NOT']) && sizeof($id_ary))
		{
			$sql_where = $sql_in = '';
			foreach ($sql_words['NOT'] as $word)
			{
				if (strstr($word, '%'))
				{
					$sql_where .= (($sql_where) ? ' OR w.word_text ' : 'w.word_text ') . "LIKE $word";
				}
				else
				{
					$sql_in .= (($sql_in) ? ', ' : '') . $word;
				}
			}
			$sql_where = ($sql_in) ? $sql_where . (($sql_where) ? ' OR ' : '') . 'w.word_text IN (' . $sql_in . ')' : $sql_where;

			$sql_and = "AND $field IN (" . implode(', ', $id_ary) . ')';
			$sql = "SELECT $sql_select
				FROM $sql_from" . POSTS_TABLE . ' p, ' . SEARCH_MATCH_TABLE . ' m, ' . SEARCH_WORD_TABLE . " w
				WHERE ($sql_where)
					AND m.word_id = w.word_id
					AND w.word_common <> 1
					AND p.post_id = m.post_id
					$sql_where_options";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$result_ary['NOT'][] = ($type == 'topics') ? $row['topic_id'] : $row['post_id'];
			}
			$db->sql_freeresult($result);
		}

		if (sizeof($result_ary['NOT']))
		{
			$id_ary = (sizeof($id_ary)) ? array_diff($id_ary, $result_ary['NOT']) : array();
		}
		unset($result_ary);

		if (!sizeof($id_ary))
		{
			return false;
		}

		$result_count = sizeof($id_ary);

		// store the ids, from start on then delete anything that isn't on the current page because we only need ids for one page
		$id_ary = array_slice($id_ary, $start);
		$this->save_ids($search_key, implode(' ', $this->split_words), $author_ary, $result_count, $id_ary, $start, $sort_dir);
		$id_ary = array_slice($id_ary, 0, (int) $per_page);

		return $result_count;
	}

	/**
	* Performs a search on an author's posts without caring about message contents. Depends on display specific params
	*
	* @param array $id_ary passed by reference, to be filled with ids for the page specified by $start and $per_page, should be ordered
	* @param int $start indicated the first index of the page
	* @param int $per_page number of ids each page is supposed to contain
	* @return total number of results
	*/
	function author_search($type, &$sort_by_sql, &$sort_key, &$sort_dir, &$sort_days, &$ex_fid_ary, &$topic_id, &$author_ary, &$id_ary, $start, $per_page)
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
		$sql_author		= 'p.poster_id ' . ((sizeof($author_ary) > 1) ? 'IN (' . implode(',', $author_ary) . ')' : '= ' . $author_ary[0]);
		$sql_fora		= (sizeof($ex_fid_ary)) ? ' AND p.forum_id NOT IN (' . implode(',', $ex_fid_ary) . ')' : '';
		$sql_topic_id	= (sizeof($ex_fid_ary)) ? ' AND p.forum_id NOT IN (' . implode(',', $ex_fid_ary) . ')' : '';
		$sql_time		= ($sort_days) ? ' AND p.post_time >= ' . (time() - ($sort_days * 86400)) : '';

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
				$sql_sort_table	= ($type == 'posts') ? TOPICS_TABLE . ' t, ' : '';
				$sql_sort_join	= ($type == 'posts') ? ' AND t.topic_id = p.topic_id ' : '';
				break;
			case 'f':
				$sql_sort_table	= FORUMS_TABLE . ' f, ';
				$sql_sort_join	= ' AND f.forum_id = p.forum_id ';
				break;
		}

		// If the cache was completely empty count the results
		if (!$result_count)
		{
			if ($type == 'posts')
			{
				$sql = 'SELECT COUNT(p.post_id) as result_count
					FROM ' . POSTS_TABLE . " p
					WHERE $sql_author
						$sql_fora
						$sql_time";
			}
			else
			{
				$sql = 'SELECT COUNT(DISTINCT t.topic_id) as result_count
					FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
					WHERE $sql_author
						$sql_fora
						AND t.topic_id = p.topic_id
						$sql_time";
			}
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow())
			{
				$result_count = $row['result_count'];
			}
			$db->sql_freeresult($result);
		}

		// Build the query for really selecting the post_ids
		if ($type == 'posts')
		{
			$sql = 'SELECT p.post_id
				FROM ' . $sql_sort_table . POSTS_TABLE . " p
				WHERE $sql_author
					$sql_fora
					$sql_sort_join
					$sql_time
				ORDER BY $sql_sort";
			$field = 'post_id';
		}
		else
		{
			$sql = 'SELECT t.topic_id
				FROM ' . $sql_sort_table . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
				WHERE $sql_author
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

		if (sizeof($id_ary))
		{
			$this->save_ids($search_key, '', $author_ary, $result_count, $id_ary, $start, $sort_dir);
			$id_ary = array_slice($id_ary, 0, $per_page);

			return $result_count;
		}
		return false;
	}

	/**
	* Updates wordlist and wordmatch tables when a message is posted or changed
	*
	* @param string $mode contains the post mode: edit, post, reply, quote ...
	*/
	function index($mode, $post_id, &$message, &$subject, $poster_id)
	{
		global $config, $db;

		// Is the fulltext indexer disabled? If yes then we need not
		// carry on ... it's okay ... I know when I'm not wanted boo hoo
		if (!$config['load_search_upd'])
		{
			return;
		}

		// Split old and new post/subject to obtain array of 'words'
		$split_text = $this->split_message($message);
		$split_title = ($subject) ? $this->split_message($subject) : array();
		$cur_words = array('post' => array(), 'title' => array());

		$words = array();
		if ($mode == 'edit')
		{
			$words['add']['post'] = array();
			$words['add']['title'] = array();
			$words['del']['post'] = array();
			$words['del']['title'] = array();

			$sql = 'SELECT w.word_id, w.word_text, m.title_match
				FROM ' . SEARCH_WORD_TABLE . ' w, ' . SEARCH_MATCH_TABLE . " m
				WHERE m.post_id = $post_id
					AND w.word_id = m.word_id";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$which = ($row['title_match']) ? 'title' : 'post';
				$cur_words[$which][$row['word_text']] = $row['word_id'];
			}
			$db->sql_freeresult($result);

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
		unset($split_text);
		unset($split_title);

		// Get unique words from the above arrays
		$unique_add_words = array_unique(array_merge($words['add']['post'], $words['add']['title']));

		// We now have unique arrays of all words to be added and removed and
		// individual arrays of added and removed words for text and title. What
		// we need to do now is add the new words (if they don't already exist)
		// and then add (or remove) matches between the words and this post
		if (sizeof($unique_add_words))
		{
			$sql = 'SELECT word_id, word_text
				FROM ' . SEARCH_WORD_TABLE . '
				WHERE word_text IN (' . implode(', ', preg_replace('#^(.*)$#', '\'$1\'', $unique_add_words)) . ')';
			$result = $db->sql_query($sql);

			$word_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$word_ids[$row['word_text']] = $row['word_id'];
			}
			$db->sql_freeresult($result);

			$new_words = array_diff($unique_add_words, array_keys($word_ids));

			if (sizeof($new_words))
			{
				switch (SQL_LAYER)
				{
					case 'mysql':
						$sql = 'INSERT INTO ' . SEARCH_WORD_TABLE . ' (word_text)
							VALUES ' . implode(', ', preg_replace('#^(.*)$#', '(\'$1\')', $new_words));
						$db->sql_query($sql);
						break;

					case 'mysql4':
					case 'mysqli':
					case 'mssql':
					case 'mssql_odbc':
					case 'sqlite':
						$sql = 'INSERT INTO ' . SEARCH_WORD_TABLE . ' (word_text) ' . implode(' UNION ALL ', preg_replace('#^(.*)$#', "SELECT '\$1'",  $new_words));
						$db->sql_query($sql);
						break;

					default:
						foreach ($new_words as $word)
						{
							$sql = 'INSERT INTO ' . SEARCH_WORD_TABLE . " (word_text)
								VALUES ('$word')";
							$db->sql_query($sql);
						}
						break;
				}
			}
			unset($new_words);
		}

		// now update the search match table, remove links to removed words and add links to new words
		foreach ($words['del'] as $word_in => $word_ary)
		{
			$title_match = ($word_in == 'title') ? 1 : 0;

			if (sizeof($word_ary))
			{
				$sql_in = array();
				foreach ($word_ary as $word)
				{
					$sql_in[] = $cur_words[$word_in][$word];
				}

				$sql = 'DELETE FROM ' . SEARCH_MATCH_TABLE . '
					WHERE word_id IN (' . implode(', ', $sql_in) . ')
						AND post_id = ' . intval($post_id) . "
						AND title_match = $title_match";
				$db->sql_query($sql);
				unset($sql_in);
			}
		}

		foreach ($words['add'] as $word_in => $word_ary)
		{
			$title_match = ($word_in == 'title') ? 1 : 0;

			if (sizeof($word_ary))
			{
				$sql = 'INSERT INTO ' . SEARCH_MATCH_TABLE . " (post_id, word_id, title_match)
					SELECT $post_id, word_id, $title_match
					FROM " . SEARCH_WORD_TABLE . '
					WHERE word_text IN (' . implode(', ', preg_replace('#^(.*)$#', '\'$1\'', $word_ary)) . ')';
				$db->sql_query($sql);
			}
		}

		// destroy cached search results containing any of the words removed or added
		$this->destroy_cache(array_unique(array_merge($words['add']['post'], $words['add']['title'], $words['del']['post'], $words['del']['post'])), array($poster_id));

		unset($unique_add_words);
		unset($words);
		unset($cur_words);
	}

	/**
	* Removes entries from the wordmatch table for the specified post_ids
	*/
	function index_remove($post_ids, $author_ids)
	{
		global $db;

		$sql = 'DELETE FROM ' . SEARCH_MATCH_TABLE . '
			WHERE post_id IN (' . implode(', ', $post_ids) . ')';
		$db->sql_query($sql);

		// SEARCH_WORD_TABLE will be updated by tidy()

		$this->destroy_cache(array(), $author_ids);
	}

	/**
	* Tidy up indexes: Tag 'common words' and remove
	* words no longer referenced in the match table
	*/
	function tidy()
	{
		global $db, $config;

		// Is the fulltext indexer disabled? If yes then we need not
		// carry on ... it's okay ... I know when I'm not wanted boo hoo
		if (!$config['load_search_upd'])
		{
			return;
		}

		$destroy_cache_words = array();

		// Remove common (> 50% of posts ) words
		if ($config['num_posts'] >= 100)
		{
			$sql = 'SELECT word_id
				FROM ' . SEARCH_MATCH_TABLE . '
				GROUP BY word_id
				HAVING COUNT(word_id) > ' . floor($config['num_posts'] * 0.5);
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				$sql_in = array();
				do
				{
					$sql_in[] = $row['word_id'];
				}
				while ($row = $db->sql_fetchrow($result));

				$destroy_cache_words = $sql_in;

				$sql_in = implode(', ', $sql_in);

				$sql = 'UPDATE ' . SEARCH_WORD_TABLE . "
					SET word_common = 1
					WHERE word_id IN ($sql_in)";
				$db->sql_query($sql);

				$sql = 'DELETE FROM ' . SEARCH_MATCH_TABLE . "
					WHERE word_id IN ($sql_in)";
				$db->sql_query($sql);
				unset($sql_in);
			}
			$db->sql_freeresult($result);
		}

		// Remove words with no matches ... this is a potentially nasty query
		$sql = 'SELECT w.word_id
			FROM ' . SEARCH_WORD_TABLE . ' w
			LEFT JOIN ' . SEARCH_MATCH_TABLE . ' m ON (w.word_id = m.word_id)
			WHERE w.word_common = 0 AND m.word_id IS NULL
			GROUP BY w.word_id';
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$sql_in = array();
			do
			{
				$sql_in[] = $row['word_id'];
			}
			while ($row = $db->sql_fetchrow($result));

			$destroy_cache_words = array_merge($destroy_cache_words, $sql_in);

			$sql = 'DELETE FROM ' . SEARCH_WORD_TABLE . '
				WHERE word_id IN (' . implode(', ', $sql_in) . ')';
			$db->sql_query($sql);
			unset($sql_in);
		}
		$db->sql_freeresult($result);

		// destroy cached search results containing any of the words that are now common or were removed
		$this->destroy_cache(array_unique($destroy_cache_words));
	}
}

?>