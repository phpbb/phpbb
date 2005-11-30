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
* @package search
* fulltext_phpbb
* phpBB's own db driven fulltext search
*/
class fulltext_phpbb
{
	var $split_words = array();
	var $common_words = array();
	var $old_split_words = array();

	function fulltext_phpbb(&$error)
	{
		$error = false;
	}

	function search($type, &$fields, &$terms, &$fid_ary, &$keywords, &$author, &$pid_ary, $sort_days)
	{
		global $phpbb_root_path, $phpEx, $config, $db, $user, $SID;

		// Are we looking for words
		if ($keywords)
		{
			$author = ($author) ? ' AND ' . $author : '';

			$this->split_words = $this->common_words = array();
			$drop_char_match =   array('-', '^', '$', ';', '#', '&', '(', ')', '<', '>', '`', '\'', '"', '|', ',', '@', '_', '?', '%', '~', '.', '[', ']', '{', '}', ':', '\\', '/', '=', '\'', '!', '*');
			$drop_char_replace = array(' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '',  '',   ' ', ' ', ' ', ' ', '',  ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '' ,  ' ', ' ', ' ',  ' ', ' ');

			if ($fp = @fopen($user->lang_path . '/search_stopwords.txt', 'rb'))
			{
				$stopwords = explode("\n", str_replace("\r\n", "\n", fread($fp, filesize($user->lang_path . '/search_stopwords.txt'))));
			}
			fclose($fp);

			if ($fp = @fopen($user->lang_path . '/search_synonyms.txt', 'rb'))
			{
				preg_match_all('#^(.*?) (.*?)$#ms', fread($fp, filesize($user->lang_path . '/search_synonyms.txt')), $match);
				$replace_synonym = &$match[1];
				$match_synonym = &$match[2];
			}
			fclose($fp);

			$match		= array('#\sand\s#i', '#\sor\s#i', '#\snot\s#i', '#\+#', '#-#', '#\|#');
			$replace	= array(' + ', ' | ', ' - ', ' + ', ' - ', ' | ');

			$keywords = preg_replace($match, $replace, $keywords);

			$match = array();
			// Comments for hardcoded bbcode elements (urls, smilies, html)
			$match[] = '#<!\-\- .* \-\->(.*?)<!\-\- .* \-\->#is';
			// New lines, carriage returns
			$match[] = "#[\n\r]+#";
			// NCRs like &nbsp; etc.
			$match[] = '#(&amp;|&)[\#a-z0-9]+?;#i';
			// BBcode
			$match[] = '#\[\/?[a-z\*\+\-]+(=.*)?(\:?[0-9a-z]{5,})\]#';

			// Filter out as above
			$keywords = preg_replace($match, ' ', strtolower(trim($keywords)));
			$keywords = str_replace($drop_char_match, $drop_char_replace, $keywords);

			// Split words
			$this->split_words = explode(' ', preg_replace('#\s+#', ' ', $keywords));

			if (sizeof($stopwords))
			{
				$this->common_words = array_intersect($this->split_words, $stopwords);
				$this->split_words = array_diff($this->split_words, $stopwords);
			}

			if (sizeof($replace_synonym))
			{
				$this->split_words = str_replace($replace_synonym, $match_synonym, $this->split_words);
			}
		}

		if ($this->old_split_words && sizeof($this->old_split_words))
		{
			$this->split_words = (sizeof($this->split_words)) ? array_diff($this->split_words, $this->old_split_words) : $this->old_split_words;
		}

		if (sizeof($this->split_words))
		{
			$bool = ($terms == 'all') ? 'AND' : 'OR';
			$sql_words = '';
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
						$sql_words[$bool][] = "'" . preg_replace('#\*+#', '%', trim($word)) . "'";
						$bool = ($terms == 'all') ? 'AND' : 'OR';
				}
			}

			// Build some display specific variable strings
			$sql_author = ($author) ? 'AND p.poster_id = ' . $author : '';
			$sql_fora = (sizeof($fid_ary)) ? ' AND p.forum_id IN (' . implode(',', $fid_ary) . ')' : '';
			$sql_time = ($sort_days) ? 'AND p.post_time >= ' . ($current_time - ($sort_days * 86400)) : '';
			$sql_select = ($type == 'posts') ? 'm.post_id' : 'DISTINCT t.topic_id';
			$sql_from = ($type == 'posts') ? '' : TOPICS_TABLE . ' t, ';
			$sql_topic = ($type == 'posts') ? '' : 'AND t.topic_id = p.topic_id';
			$field = ($type == 'posts') ? 'm.post_id' : 't.topic_id';

			switch ($fields)
			{
				case 'titleonly':
					$sql_match = ' AND m.title_match = 1';
					break;
				case 'msgonly':
					$sql_match = ' AND m.title_match = 0';
					break;
				default:
					$sql_match = '';
			}

			// Are we searching within an existing search set? Yes, then include the old ids
			$sql_find_in = (sizeof($pid_ary)) ? 'AND ' . (($type == 'topics') ? 't.topic_id' : 'm.post_id') . ' IN (' . implode(', ', $pid_ary) . ')' : '';

			$result_ary = array();
			$_bool = array('AND', 'OR', 'NOT');
			foreach ($_bool as $bool)
			{
				if (isset($sql_words[$bool]) && is_array($sql_words[$bool]))
				{
					switch ($bool)
					{
						case 'AND':
						case 'NOT':
							foreach ($sql_words[$bool] as $word)
							{
								if (strlen($word) < 4)
								{
									continue;
								}

								$sql_where = (strstr($word, '%')) ? "LIKE $word" : "= $word";

								$sql_and = (isset($result_ary['AND']) && sizeof($result_ary['AND'])) ? "AND $field IN (" . implode(', ', $result_ary['AND']) . ')' : '';

								$sql = "SELECT $sql_select
									FROM $sql_from" . POSTS_TABLE . ' p, ' . SEARCH_MATCH_TABLE . ' m, ' . SEARCH_WORD_TABLE . " w
									WHERE w.word_text $sql_where
										AND m.word_id = w.word_id
										AND w.word_common <> 1
										AND p.post_id = m.post_id
										$sql_topic
										$sql_fora
										$sql_author
										$sql_and
										$sql_time
										$sql_match
										$sql_find_in";
								$result = $db->sql_query_limit($sql, 1000);

								if ($db->sql_numrows() > 999)
								{
									trigger_error($user->lang['TOO_MANY_SEARCH_RESULTS']);
								}

								if (!($row = $db->sql_fetchrow($result)) && $bool == 'AND')
								{
									trigger_error($user->lang['NO_SEARCH_RESULTS']);
								}

								if ($bool == 'AND')
								{
									$result_ary['AND'] = array();
								}

								do
								{
									$result_ary[$bool][] = ($type == 'topics') ? $row['topic_id'] : $row['post_id'];
								}
								while ($row = $db->sql_fetchrow($result));
								$db->sql_freeresult($result);
							}
							break;

						case 'OR':
							$sql_where = $sql_in = '';
							foreach ($sql_words[$bool] as $word)
							{
								if (strlen($word) < 4)
								{
									continue;
								}

								if (strstr($word, '%'))
								{
									$sql_where .= (($sql_where) ? ' OR w.word_text ' : 'w.word_text ') . "LIKE $word";
								}
								else
								{
									$sql_in .= (($sql_in) ? ', ' : '') . $word;
								}
							}
							$sql_where = ($sql_in) ? (($sql_where) ? ' OR ' : '') . 'w.word_text IN (' . $sql_in . ')' : $sql_where;

							$sql_and = (isset($result_ary['AND']) && sizeof($result_ary['AND'])) ? "AND $field IN (" . implode(', ', $result_ary['AND']) . ')' : '';
							$sql = "SELECT $sql_select
								FROM $sql_from" . POSTS_TABLE . ' p, ' . SEARCH_MATCH_TABLE . ' m, ' . SEARCH_WORD_TABLE . " w
								WHERE ($sql_where)
									AND m.word_id = w.word_id
									AND w.word_common <> 1
									AND p.post_id = m.post_id
									$sql_topic
									$sql_fora
									$sql_author
									$sql_and
									$sql_time
									$sql_match
									$sql_find_in";
							$result = $db->sql_query_limit($sql, 1000);

							while ($row = $db->sql_fetchrow($result))
							{
								$result_ary[$bool][] = ($type == 'topics') ? $row['topic_id'] : $row['post_id'];
							}
							$db->sql_freeresult($result);
							break;
					}
				}
				else
				{
					$sql_words[$bool] = array();
				}
			}

			if (isset($result_ary['OR']) && sizeof($result_ary['OR']))
			{
				$pid_ary = (isset($result_ary['AND']) && sizeof($result_ary['AND'])) ? array_diff($result_ary['AND'], $result_ary['OR']) : $result_ary['OR'];
			}
			else
			{
				$pid_ary = (isset($result_ary['AND'])) ? $result_ary['AND'] : array();
			}

			if (isset($result_ary['NOT']) && sizeof($result_ary['NOT']))
			{
				$pid_ary = (sizeof($pid_ary)) ? array_diff($pid_ary, $result_ary['NOT']) : array();
			}
			unset($result_ary);

			$pid_ary = array_unique($pid_ary);

			if (!sizeof($pid_ary))
			{
				trigger_error($user->lang['NO_SEARCH_RESULTS']);
			}

			$sql = 'SELECT word_text
				FROM ' . SEARCH_WORD_TABLE . '
				WHERE word_text IN (' . implode(', ', array_unique(array_merge($sql_words['AND'], $sql_words['OR'], $sql_words['NOT']))) . ')
					AND word_common = 1';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$common_words[] = $row['word_text'];
			}
			$db->sql_freeresult($result);
		}
		else if ($author)
		{
			$sql_author = ($author) ? 'p.poster_id = ' . $author : '';
			$sql_fora = (sizeof($fid_ary)) ? ' AND p.forum_id IN (' . implode(',', $fid_ary) . ')' : '';

			if ($type == 'posts')
			{
				$sql = 'SELECT p.post_id
					FROM ' . POSTS_TABLE . " p
					WHERE $sql_author
						$sql_fora";
				$field = 'post_id';
			}
			else
			{
				$sql = 'SELECT t.topic_id
					FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
					WHERE $sql_author
						$sql_fora
						AND t.topic_id = p.topic_id
					GROUP BY t.topic_id";
				$field = 'topic_id';
			}
			$result = $db->sql_query_limit($sql, 1000);

			while ($row = $db->sql_fetchrow($result))
			{
				$pid_ary[] = $row[$field];
			}
			$db->sql_freeresult($result);
		}

		return false;
	}
}

?>