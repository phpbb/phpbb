<?php
/***************************************************************************
 *                           message_parser.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

// Main message parser for posting, pm, etc. takes raw message
// and parses it for attachments, html, bbcode and smilies
class parse_message
{
	var $bbcode_tpl = null;
	var $message_mode = 0; // MSG_POST/MSG_PM

	function parse_message($message_type)
	{
		$this->message_mode = $message_type;
	}
	
	function parse(&$message, $html, $bbcode, $uid, $url, $smilies, $attach = false)
	{
		global $config, $db, $user, $_FILE;

		$warn_msg = '';

		// Do some general 'cleanup' first before processing message,
		// e.g. remove excessive newlines(?), smilies(?)
		$match = array('#sid=[a-z0-9]*?&?#', "#([\r\n][\s]+){3,}#");
		$replace = array('', "\n\n");

		$message = trim(preg_replace($match, $replace, $message));

		// Message length check
		if (!strlen($message) || (intval($config['max_post_chars']) && strlen($message) > intval($config['max_post_chars'])))
		{
			$warn_msg .= (($warn_msg != '') ? '<br />' : '') . (!strlen($message)) ? $user->lang['TOO_FEW_CHARS'] : $user->lang['TOO_MANY_CHARS'];
		}

		// Smiley check
		if (intval($config['max_post_smilies']) && $smilies )
		{
			$sql = "SELECT code	
			FROM " . SMILIES_TABLE;
			$result = $db->sql_query($sql);

			$match = 0;
			while ($row = $db->sql_fetchrow($result))
			{
				if (preg_match_all('#('. preg_quote($row['code'], '#') . ')#', $message, $matches))
				{
					$match++;
				}

				if ($match > intval($config['max_post_smilies']))
				{
					$warn_msg .= (($warn_msg != '') ? '<br />' : '') . $user->lang['TOO_MANY_SMILIES'];
					break;
				}
			}
			$db->sql_freeresult($result);
			unset($matches);
		}

		if ($warn_msg)
		{
			return $warn_msg;
		}

		$warn_msg .= (($warn_msg != '') ? '<br />' : '') . $this->html($message, $html);
		$warn_msg .= (($warn_msg != '') ? '<br />' : '') . $this->bbcode($message, $bbcode, $uid);
		$warn_msg .= (($warn_msg != '') ? '<br />' : '') . $this->emoticons($message, $smilies);
		$warn_msg .= (($warn_msg != '') ? '<br />' : '') . $this->magic_url($message, $url);
		$warn_msg .= (($warn_msg != '') ? '<br />' : '') . $this->attach($_FILE, $attach);

		return $warn_msg;
	}

	function html(&$message, $html)
	{
		global $config;

		$message = str_replace(array('<', '>'), array('&lt;', '&gt;'), $message);

		if ($html)
		{
			// If $html is true then "allowed_tags" are converted back from entity
			// form, others remain
			$allowed_tags = split(',', $config['allow_html_tags']);

			if (sizeof($allowed_tags))
			{
				$message = preg_replace('#&lt;(\/?)(' . str_replace('*', '.*?', implode('|', $allowed_tags)) . ')&gt;#is', '<\1\2>', $message);
			}
		}

		return;
	}

	function bbcode(&$message, $bbcode, $uid)
	{
		global $config;

	}

	// Replace magic urls of form http://xxx.xxx., www.xxx. and xxx@xxx.xxx.
	// Cuts down displayed size of link if over 50 chars, turns absolute links
	// into relative versions when the server/script path matches the link
	function magic_url(&$message, $url)
	{
		global $config;

		if ($url)
		{
			$server_protocol = ( $config['cookie_secure'] ) ? 'https://' : 'http://';
			$server_port = ( $config['server_port'] <> 80 ) ? ':' . trim($config['server_port']) . '/' : '/';

			$match = array();
			$replace = array();

			// relative urls for this board
			$match[] = '#' . $server_protocol . trim($config['server_name']) . $server_port . preg_replace('/^\/?(.*?)(\/)?$/', '\1', trim($config['script_path'])) . '/([^\t\n\r <"\']+)#i';
			$replace[] = '<!-- l --><a href="\1" target="_blank">\1</a><!-- l -->';

			// matches a xxxx://aaaaa.bbb.cccc. ...
			$match[] = '#(^|[\n ])([\w]+?://.*?[^\t\n\r<"]*)#ie';
			$replace[] = "'\\1<!-- m --><a href=\"\\2\" target=\"_blank\">' . ( ( strlen(str_replace(' ', '%20', '\\2')) > 55 ) ?substr(str_replace(' ', '%20', '\\2'), 0, 39) . ' ... ' . substr(str_replace(' ', '%20', '\\2'), -10) : str_replace(' ', '%20', '\\2') ) . '</a><!-- m -->'";

			// matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
			$match[] = '#(^|[\n ])(www\.[\w\-]+\.[\w\-.\~]+(?:/[^\t\n\r<"]*)?)#ie';
			$replace[] = "'\\1<!-- w --><a href=\"http://\\2\" target=\"_blank\">' . ( ( strlen(str_replace(' ', '%20', '\\2')) > 55 ) ? substr(str_replace(' ', '%20', '\\2'), 0, 39) . ' ... ' . substr(str_replace(' ', '%20', '\\2'), -10) : str_replace(' ', '%20', '\\2') ) . '</a><!-- w -->'";

			// matches an email@domain type address at the start of a line, or after a space.
			$match[] = '#(^|[\n ])([a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)#ie';
			$replace[] = "'\\1<!-- e --><a href=\"mailto:\\2\">' . ( ( strlen('\\2') > 55 ) ?substr('\\2', 0, 39) . ' ... ' . substr('\\2', -10) : '\\2' ) . '</a><!-- e -->'";

			$message = preg_replace($match, $replace, $message);
		}
	}

	function emoticons(&$message, $smile)
	{
		global $db, $user;

		$sql = "SELECT * 
		FROM " . SMILIES_TABLE;
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$match = $replace = array();
			do
			{
				$match[] = "#(?<=.\W|\W.|^\W)" . preg_quote($row['code'], '#') . "(?=.\W|\W.|\W$)#";
				$replace[] = '<!-- s' . $row['code'] . ' --><img src="{SMILE_PATH}/' . $row['smile_url'] . '" border="0" alt="' . $row['emoticon'] . '" title="' . $row['emoticon'] . '" /><!-- s' . $row['code'] . ' -->';
			}
			while ($row = $db->sql_fetchrow($result));

			$message = preg_replace($match, $replace, ' ' . $message . ' ');
		}
		$db->sql_freeresult($result);

		return;
	}

	function attach($file_ary, $attach)
	{
		global $config;

	}

	// Parse Poll
	function parse_poll(&$poll, $poll_data)
	{
		global $auth, $forum_id, $user, $config;

		// poll_options, poll_options_size
		$err_msg = '';

		// Process poll options
		if (!empty($poll_data['poll_option_text']) && (($auth->acl_get('f_poll', $forum_id) && !$poll_data['poll_last_vote']) || $auth->acl_gets('m_edit', 'a_', $forum_id)))
		{
			if (($result = $this->parse($poll_data['poll_option_text'], $poll_data['enable_html'], $poll_data['enable_bbcode'], $poll_data['bbcode_uid'], $poll_data['enable_urls'], $poll_data['enable_smilies'], false)) != '')
			{
				$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $result;
			}

			$poll['poll_options'] = explode("\n", trim($poll_data['poll_option_text']));
			$poll['poll_options_size'] = sizeof($poll['poll_options']);
			
			if (sizeof($poll['poll_options']) == 1)
			{
				$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $user->lang['TOO_FEW_POLL_OPTIONS'];
			}
			else if (sizeof($poll['poll_options']) > intval($config['max_poll_options']))
			{
				$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $user->lang['TOO_MANY_POLL_OPTIONS'];
			}
			else if (sizeof($poll['poll_options']) < $poll['poll_options_size'])
			{
				$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $user->lang['NO_DELETE_POLL_OPTIONS'];
			}

			$poll['poll_title'] = (!empty($poll_data['poll_title'])) ? trim(htmlspecialchars(strip_tags($poll_data['poll_title']))) : '';
			$poll['poll_length'] = (!empty($poll_data['poll_length'])) ? intval($poll_data['poll_length']) : 0;
		}
		$poll['poll_start'] = $poll_data['poll_start'];

		return ($err_msg);
	}
}

// Parses a given message and updates/maintains the fulltext tables
class fulltext_search
{
	function split_words(&$text)
	{
		global $user, $config;

		static $drop_char_match, $drop_char_replace, $stopwords, $synonyms;

		if (empty($drop_char_match))
		{
			$drop_char_match =   array('^', '$', '&', '(', ')', '<', '>', '`', '\'', '"', '|', ',', '@', '_', '?', '%', '-', '~', '+', '.', '[', ']', '{', '}', ':', '\\', '/', '=', '#', '\'', ';', '!', '*');
			$drop_char_replace = array(' ', ' ', ' ', ' ', ' ', ' ', ' ', '',  '',   ' ', ' ', ' ', ' ', '',  ' ', ' ', '',  ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '' ,  ' ', ' ', ' ', ' ',  ' ', ' ', ' ');
			$stopwords = @file($user->lang_path . '/search_stopwords.txt');
			$synonyms = @file($user->lang_path . '/search_synonyms.txt');
		}

		$match = array();
		// New lines, carriage returns
		$match[] = "#[\n\r]+#";
		// NCRs like &nbsp; etc.
		$match[] = '#&[\#a-z0-9]+?;#i';
		// URL's
		$match[] = '#\b[\w]+:\/\/[a-z0-9\.\-]+(\/[a-z0-9\?\.%_\-\+=&\/]+)?#';
		// BBcode
		$match[] = '#\[img:[a-z0-9]{10,}\].*?\[\/img:[a-z0-9]{10,}\]#';
		$match[] = '#\[\/?url(=.*?)?\]#';
		$match[] = '#\[\/?[a-z\*=\+\-]+(\:?[0-9a-z]+)?:[a-z0-9]{10,}(\:[a-z0-9]+)?=?.*?\]#';
		// Sequences < min_search_chars & < max_search_chars
		$match[] = '#\b([a-z0-9]{1,' . $config['min_search_chars'] . '}|[a-z0-9]{' . $config['max_search_chars'] . ',})\b#is';

		$text = preg_replace($match, ' ', ' ' . strtolower($text) . ' ');

		// Filter out non-alphabetical chars
		$text = str_replace($drop_char_match, $drop_char_replace, $text);

		if (!empty($stopwords_list))
		{
			$text = str_replace($stopwords, '', $text);
		}

		if (!empty($synonyms))
		{
			for ($j = 0; $j < count($synonyms); $j++)
			{
				list($replace_synonym, $match_synonym) = split(' ', trim(strtolower($synonyms[$j])));
				if ( $mode == 'post' || ( $match_synonym != 'not' && $match_synonym != 'and' && $match_synonym != 'or' ) )
				{
					$text =  preg_replace('#\b' . trim($match_synonym) . '\b#', ' ' . trim($replace_synonym) . ' ', $text);
				}
			}
		}

		preg_match_all('/\b([\w]+)\b/', $text, $split_entries);
		return array_unique($split_entries[1]);
	}

	function add(&$mode, &$post_id, &$message, &$subject)
	{
		global $config, $db;

//		$mtime = explode(' ', microtime());
//		$starttime = $mtime[1] + $mtime[0];

		// Split old and new post/subject to obtain array of 'words'
		$split_text = $this->split_words($message);
		$split_title = ($subject) ? $this->split_words($subject) : array();

		$words = array();
		if ($mode == 'edit')
		{
			$sql = "SELECT w.word_id, w.word_text, m.title_match
				FROM " . SEARCH_WORD_TABLE . " w, " . SEARCH_MATCH_TABLE . " m
				WHERE m.post_id = " . intval($post_id) . "
					AND w.word_id = m.word_id";
			$result = $db->sql_query($sql);
			$cur_words = array();
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
			$sql = "SELECT word_id, word_text
				FROM " . SEARCH_WORD_TABLE . "
				WHERE word_text IN (" . implode(', ', preg_replace('#^(.*)$#', '\'\1\'', $unique_add_words)) . ")";
			$result = $db->sql_query($sql);

			$word_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$word_ids[$row['word_text']] = $row['word_id'];
			}
			$db->sql_freeresult($result);

			$new_words = array_diff($unique_add_words, array_keys($word_ids));
			unset($unique_add_words);

			if (sizeof($new_words))
			{
				switch (SQL_LAYER)
				{
					case 'postgresql':
					case 'msaccess':
					case 'mssql-odbc':
					case 'oracle':
					case 'db2':
						foreach ($new_words as $word)
						{
							$sql = "INSERT INTO " . SEARCH_WORD_TABLE . " (word_text)
								VALUES ('" . $word . "')";
							$db->sql_query($sql);
						}

						break;
					case 'mysql':
					case 'mysql4':
						$sql = "INSERT INTO " . SEARCH_WORD_TABLE . " (word_text)
							VALUES " . implode(', ', preg_replace('#^(.*)$#', '(\'\1\')',  $new_words));
						$db->sql_query($sql);
						break;
					case 'mssql':
						$sql = "INSERT INTO " . SEARCH_WORD_TABLE . " (word_text)
							VALUES " . implode(' UNION ALL ', preg_replace('#^(.*)$#', 'SELECT \'\1\'',  $new_words));
						$db->sql_query($sql);
						break;
				}
			}
			unset($new_words);
		}

		foreach ($words['del'] as $word_in => $word_ary)
		{
			$title_match = ($word_in == 'title') ? 1 : 0;

			$sql = '';
			if (sizeof($word_ary))
			{
				foreach ($word_ary as $word)
				{
					$sql .= (($sql != '') ? ', ' : '') . $cur_words[$word_in][$word];
				}
				$sql = "DELETE FROM " . SEARCH_MATCH_TABLE . " WHERE word_id IN ($sql) AND post_id = " . intval($post_id) . " AND title_match = $title_match";
				$db->sql_query($sql);
			}
		}

		foreach ($words['add'] as $word_in => $word_ary)
		{
			$title_match = ( $word_in == 'title' ) ? 1 : 0;

			if (sizeof($word_ary))
			{
				$sql = "INSERT INTO " . SEARCH_MATCH_TABLE . " (post_id, word_id, title_match) SELECT $post_id, word_id, $title_match FROM " . SEARCH_WORD_TABLE . " WHERE word_text IN (" . implode(', ', preg_replace('#^(.*)$#', '\'\1\'', $word_ary)) . ")";
				$db->sql_query($sql);
			}
		}

		unset($words);

//		$mtime = explode(' ', microtime());
//		echo "Search parser time taken >> " . ($mtime[1] + $mtime[0] - $starttime);

		// Run the cleanup infrequently, once per session cleanup
		if ($config['search_last_gc'] < time() - $config['search_gc'])
		{
//			$this->search_tidy();
		}
	}

	// Tidy up indexes, tag 'common words', remove
	// words no longer referenced in the match table, etc.
	function search_tidy()
	{
		global $db;

		// Remove common (> 60% of posts ) words
		$result = $db->sql_query("SELECT SUM(forum_posts) AS total_posts FROM " . FORUMS_TABLE);

		$row = $db->sql_fetchrow($result);

		if ($row['total_posts'] >= 100)
		{
			$sql = "SELECT word_id
				FROM " . SEARCH_MATCH_TABLE . "
				GROUP BY word_id
				HAVING COUNT(word_id) > " . floor($row['total_posts'] * 0.6);
			$result = $db->sql_query($sql);

			$in_sql = '';
			while ($row = $db->sql_fetchrow($result))
			{
				$in_sql .= (( $in_sql != '') ? ', ' : '') . $row['word_id'];
			}
			$db->sql_freeresult($result);

			if ($in_sql)
			{
				$sql = "UPDATE " . SEARCH_WORD_TABLE . "
					SET word_common = " . TRUE . "
					WHERE word_id IN ($in_sql)";
				$db->sql_query($sql);

				$sql = "DELETE FROM " . SEARCH_MATCH_TABLE . "
					WHERE word_id IN ($in_sql)";
				$db->sql_query($sql);
			}
		}

		// Remove words with no matches ... this is a potentially nasty query
		$sql = "SELECT w.word_id
			FROM ( " . SEARCH_WORD_TABLE . " w
			LEFT JOIN " . SEARCH_MATCH_TABLE . " m ON w.word_id = m.word_id
				AND m.word_id IS NULL
			GROUP BY m.word_id";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$in_sql = '';
			do
			{
				$in_sql .= ', ' . $row['word_id'];
			}
			while ($row = $db->sql_fetchrow($result));

			$sql = 'DELETE FROM ' . SEARCH_WORD_TABLE . '
				WHERE word_id IN (' . substr($in_sql, 2) . ')';
			$db->sql_query($sql);
		}
		$db->sql_freeresult($result);
	}
}

?>