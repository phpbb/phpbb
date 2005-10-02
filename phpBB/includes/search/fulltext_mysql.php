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
* fulltext_mysql
* Search indexing for MySQL
*/
class fulltext_mysql
{
	var $version = 4;
	var $split_words = array();
	var $common_words = array();
	var $old_split_words = array();

	function fulltext_mysql(&$error)
	{
		global $db;

		$result = $db->sql_query('SELECT VERSION() AS mysql_version');
		$version = ($row = $db->sql_fetchrow($result)) ? $row['mysql_version'] : '';
		$db->sql_freeresult($result);

		// Need to check for fulltext indexes ... maybe all of thise is best left in acp?

		$error = (!preg_match('#^4|5|6#s', $version)) ? true : false;
	}

	function search($type, &$fields, &$terms, &$fid_ary, &$keywords, &$author, &$pid_ary, $sort_days)
	{
		global $phpbb_root_path, $phpEx, $config, $db, $user, $SID;

		// Are we looking for words
		if ($keywords)
		{
			$author = ($author) ? ' AND ' . $author : '';

			$this->split_words = $this->common_words = array();
			$drop_char_match =   array('^', '$', ';', '#', '&', '(', ')', '<', '>', '`', '\'', '"', '|', ',', '@', '_', '?', '%', '~', '.', '[', ']', '{', '}', ':', '\\', '/', '=', '\'', '!', '*');
			$drop_char_replace = array(' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '', '', ' ', ' ', ' ', ' ', '',  ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '' ,  ' ', ' ', ' ',  ' ', ' ');

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
			$replace	= array(' +', ' |', ' -', ' +', ' -', ' |');

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
			// Build some display specific variable strings
			$sql_select = ($type == 'posts') ? 'p.post_id' : 'DISTINCT t.topic_id';
			$sql_from = ($type == 'posts') ? '' : TOPICS_TABLE . ' t, ';
			$sql_fora = (sizeof($fid_ary)) ? ' AND p.forum_id IN (' . implode(',', $fid_ary) . ')' : '';
			$sql_author = ($author) ? 'AND p.poster_id = ' . $author : '';
			$sql_time = ($sort_days) ? 'AND p.post_time >= ' . ($current_time - ($sort_days * 86400)) : '';
			$sql_topic = ($type == 'posts') ? '' : 'AND t.topic_id = p.topic_id';

			switch ($fields)
			{
				case 'titleonly':
					$sql_match = 'p.post_subject';
					break;
				case 'msgonly':
					$sql_match = 'p.post_text';
					break;
				default:
					$sql_match = 'p.post_text, p.post_subject';
			}

			// Are we searching within an existing search set? Yes, then include the old ids
			$sql_find_in = (sizeof($pid_ary)) ? 'AND ' . (($type == 'topics') ? 't.topic_id' : 'p.post_id') . ' IN (' . implode(', ', $pid_ary) . ')' : '';

			$words = array();
			if ($terms == 'all')
			{
				foreach ($this->split_words as $id => $word)
				{
					$words[] = (strpos($word, '+') === 0) ? $word : '+' . $word;
				}
			}
			else
			{
				$words = $this->split_words;
			}

			$sql = "SELECT $sql_select
				FROM $sql_from" . POSTS_TABLE . " p
				WHERE MATCH ($sql_match) AGAINST ('" . implode(' ', $words) . "' IN BOOLEAN MODE)
					$sql_topic
					$sql_find_in
					$sql_fora
					$sql_author
					$sql_time";
			$result = $db->sql_query_limit($sql, 1000);

			if ($db->sql_numrows() > 999)
			{
				trigger_error($user->lang['TOO_MANY_SEARCH_RESULTS']);
			}

			$pid_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$pid_ary[] = ($type == 'topics') ? $row['topic_id'] : $row['post_id'];
			}
			$db->sql_freeresult($result);

			$pid_ary = array_unique($pid_ary);

			if (!sizeof($pid_ary))
			{
				trigger_error($user->lang['NO_SEARCH_RESULTS']);
			}
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