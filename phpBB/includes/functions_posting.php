<?php
/***************************************************************************
 *                           functions_posting.php
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

	function parse(&$message, $html, $bbcode, $uid, $url, $smilies)
	{
		global $config, $db, $lang;

		$warn_msg = '';

		// Do some general 'cleanup' first before processing message,
		// e.g. remove excessive newlines(?), smilies(?)
		$match = array();
		$replace = array();

		$match[] = '#sid=[a-z0-9]+&?#';
		$replace[] = '';
		$match[] = "#([\r\n][\s]+){3,}#";
		$replace[] = "\n\n";

		$message = preg_replace($match, $replace, $message);

		// Message length check
		if ( !strlen($message) || ( $config['max_post_chars'] && strlen($message) > $config['max_post_chars'] ) )
		{
			$warn_msg .= ( !strlen($message) ) ? $lang['Too_few_chars'] . '<br />' : $lang['Too_many_chars'] . '<br />';
		}

		// Smiley check
		if ( $config['max_post_smilies'] && $smilies )
		{
			$sql = "SELECT code
				FROM " . SMILIES_TABLE;
			$result = $db->sql_query($sql);

			$match = 0;
			while ( $row = $db->sql_fetchrow($result))
			{
				if ( preg_match_all('#('. preg_quote($row['code'], '#') . ')#', $message, $matches) )
				{
					$match++;
				}

				if ( $match > $config['max_post_smilies'] )
				{
					$warn_msg .= $lang['Too_many_smilies'] . '<br />';
					break;
				}
			}
			$db->sql_freeresult($result);
			unset($matches);
		}

		// Specialchars message here ... ?
		$message = htmlspecialchars($message, ENT_COMPAT, $lang['ENCODING']);

		if ( $warn_msg )
		{
			return $warn_msg;
		}

		$warn_msg .= $this->html($message, $html);
		$warn_msg .= $this->bbcode($message, $bbcode, $uid);
		$warn_msg .= $this->magic_url($message, $url);
		$warn_msg .= $this->attach($_FILE);

		return $warn_msg;
	}

	function html(&$message, $html)
	{
		global $config, $lang;

		if ( $html )
		{
			// If $html is true then "allowed_tags" are converted back from entity
			// form, others remain
			$allowed_tags = split(',', str_replace(' ', '', $config['allow_html_tags']));

			$match = array();
			$replace = array();

			foreach ( $allowed_tags as $tag )
			{
				$match[] = '#&lt;(\/?' . str_replace('*', '.*?', $tag) . ')&gt;#i';
				$replace[] = '<\1>';
			}

			$message = preg_replace($match, $replace, $message);
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

		if ( $url )
		{
			$server_protocol = ( $config['cookie_secure'] ) ? 'https://' : 'http://';
			$server_port = ( $config['server_port'] <> 80 ) ? ':' . trim($config['server_port']) . '/' : '/';

			$match = array();
			$replace = array();

			// relative urls for this board
			$match[] = '#' . $server_protocol . trim($config['server_name']) . $server_port . preg_replace('/^\/?(.*?)(\/)?$/', '\1', trim($config['script_path'])) . '/([^\t <\n\r\"]+)#i';
			$replace[] = '<a href="\1" target="_blank">\1</a>';

			// matches a xxxx://aaaaa.bbb.cccc. ...
			$match[] = '#([\n ])([\w]+?://.*?)([\t\n\r <"\'])#ie';
			$replace[] = "'\\1<!-- m --><a href=\"\\2\" target=\"_blank\">' . ( ( strlen('\\2') > 55 ) ?substr('\\2', 0, 39) . ' ... ' . substr('\\2', -10) : '\\2' ) . '</a><!-- m -->\\3'";

			// matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
			$match[] = '#(^|[\n ])(www\.[\w\-]+\.[\w\-.\~]+(?:/[^\t <\n\r\"]*)?)#ie';
			$replace[] = "'\\1<!-- m --><a href=\"http://\\2\" target=\"_blank\">' . ( ( strlen('\\2') > 55 ) ?substr('\\2', 0, 39) . ' ... ' . substr('\\2', -10) : '\\2' ) . '</a><!-- m -->'";

			// matches an email@domain type address at the start of a line, or after a space.
			$match[] = '#(^|[\n ])([a-z0-9\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)#ie';
			$replace[] = "'\\1<!-- m --><a href=\"mailto:\\2\">' . ( ( strlen('\\2') > 55 ) ?substr('\\2', 0, 39) . ' ... ' . substr('\\2', -10) : '\\2' ) . '</a><!-- m -->'";

			$message = preg_replace($match, $replace, $message);
		}
	}

	// Based off of Acyd Burns Mod
	function attach($file_ary)
	{
		global $config;

		$allowed_ext = explode(',', $config['attach_ext']);
	}
}

// Parses a given message and updates/maintains
// the fulltext word indexes NOTE this is not complete
// nor 'entirely' (!) functional ...
class fulltext_search
{
	function split_words(&$text)
	{
		global $user, $config;

		static $drop_char_match =   array('^', '$', '&', '(', ')', '<', '>', '`', '\'', '"', '|', ',', '@', '_', '?', '%', '-', '~', '+', '.', '[', ']', '{', '}', ':', '\\', '/', '=', '#', '\'', ';', '!',   '*');
		static $drop_char_replace = array(' ', ' ', ' ', ' ', ' ', ' ', ' ', '',  '',   ' ', ' ', ' ', ' ', '',  ' ', ' ', '',  ' ',   ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ' , ' ', ' ', ' ', ' ',  ' ', ' ', ' ');
		$stopwords_list = @file($user->lang_path . '/search_stopwords.txt');
		$synonym_list = @file($user->lang_path . '/search_synonyms.txt');

		$match = array();
		// New lines, carriage returns
		$match[] = "#[\n\r]+#";
		// HTML and NCRs like &nbsp; etc.
		$match[] = '#<(.*?)>.*?<\/\1>#'; // BAD!
		$match[] = '#\b&\#?[a-z0-9]+;\b#';
		// URL's
		$match[] = '#\b[\w]+:\/\/[a-z0-9\.\-]+(\/[a-z0-9\?\.%_\-\+=&\/]+)?#';
		// BBcode
		$match[] = '#\[img:[a-z0-9]{10,}\].*?\[\/img:[a-z0-9]{10,}\]#';
		$match[] = '#\[\/?url(=.*?)?\]#';
		$match[] = '#\[\/?[a-z\*=\+\-]+(\:?[0-9a-z]+)?:[a-z0-9]{10,}(\:[a-z0-9]+)?=?.*?\]#';
		// Sequences < min_search_chars & < max_search_chars
		$match[] = '#\b([a-z0-9]{1,' . $config['min_search_chars'] . '}|[a-z0-9]{' . $config['max_search_chars'] . ',})\b#';

		$text = preg_replace($match, ' ', ' ' . strtolower($text) . ' ');

		// Filter out non-alphabetical chars
		for($i = 0; $i < count($drop_char_match); $i++)
		{
			$text = str_replace($drop_char_match[$i], $drop_char_replace[$i], $text);
		}

		if ( !empty($stopwords_list) )
		{
			$text = str_replace($stopwords_list, '', $text);
		}

		if ( !empty($synonym_list) )
		{
			for ($j = 0; $j < count($synonym_list); $j++)
			{
				list($replace_synonym, $match_synonym) = split(' ', trim(strtolower($synonym_list[$j])));
				if ( $mode == 'post' || ( $match_synonym != 'not' && $match_synonym != 'and' && $match_synonym != 'or' ) )
				{
					$text =  preg_replace('#\b' . trim($match_synonym) . '\b#', ' ' . trim($replace_synonym) . ' ', $text);
				}
			}
		}

		echo "<br /><br />\n\n";
		echo "cleaned_text => " . htmlentities($text);
		echo "<br /><br />\n\n";
		preg_match_all('/\b([\w]+)\b/', $text, $split_entries);

		return array_unique($split_entries[1]);
	}

	function add(&$mode, &$post_id, &$message, &$subject)
	{
		global $config, $db;

		$mtime = explode(' ', microtime());
		$starttime = $mtime[1] + $mtime[0];

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
			$result = $db->sql_query($result);

			$cur_words = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$which = ($row['title_match']) ? 'title' : 'post';
				$cur_words[$which][$row['word_id']] = $row['word_text'];
			}
			$db->sql_freeresult($result);

			$words['add']['post'] = array_diff($split_text, $cur_words['post']);
			$words['add']['title'] = array_diff($split_title, $cur_words['title']);
			$words['del']['post'] = array_diff($cur_words['post'], $split_text);
			$words['del']['title'] = array_diff($cur_words['title'], $split_title);
		}
		else
		{
			$words['add']['post'] = $split_text;
			$words['add']['title'] = $split_title;
		}
		unset($split_text);
		unset($split_title);

		// Get unique words from the above arrays
		$unique_add_words = array_unique(array_merge($words['add']['text'], $words['add']['title']));

		//
		// We now have unique arrays of all words to be added and removed and
		// individual arrays of added and removed words for text and title. What
		// we need to do now is add the new words (if they don't already exist)
		// and then add (or remove) matches between the words and this post
		//
		if ( sizeof($unique_add_words) )
		{
			$word_id = array();
			$new_word = array();

			$sql = "SELECT word_id, word_text
				FROM " . SEARCH_WORD_TABLE . "
				WHERE word_text IN (" . implode(', ', preg_replace('#^(.*)$#', '\'\1\'', $unique_words)) . ")";
			$result = $db->sql_query($sql);

			while ( $row = $db->sql_fetchrow($result) )
			{
				$word_id[$row['word_text']] = $row['word_id'];
			}
			$db->sql_freeresult($result);

			foreach ( $unique_words as $word )
			{
				if ( empty($word_id[$word]) )
				{
					$new_words[] = $row['word_text'];
				}
			}
			unset($unique_words);

			switch( SQL_LAYER )
			{
				case 'postgresql':
				case 'msaccess':
				case 'mssql-odbc':
				case 'oracle':
				case 'db2':
					foreach ( $new_words as $word )
					{
						$sql = "INSERT INTO " . SEARCH_WORD_TABLE . " (word_text)
							VALUES ('" . $word . "')";
						$db->sql_query($sql);
					}

					break;

				default:
					switch( SQL_LAYER )
					{
						case 'mysql':
						case 'mysql4':
							$value_sql = implode(', ', preg_replace('#^(.*)$#', '(\'\1\')',  $new_words));
							break;

						case mssql:
							$value_sql = implode(' UNION ALL ', preg_replace('#^(.*)$#', 'SELECT \'\1\'',  $new_words));
							break;

					}

					if ( $value_sql )
					{
						$sql = "INSERT INTO " . SEARCH_WORD_TABLE . " (word_text)
							VALUES $value_sql";
						$db->sql_query($sql);
					}
			}
		}

		$unique_words = array_unique(array_merge($words['del']['text'], $words['del']['title']));

		$word_id = array();
		if ( count($unique_words) )
		{
			$sql = "SELECT word_id, word_text
				FROM " . SEARCH_WORD_TABLE . "
				WHERE word_text IN (" . implode(', ', preg_replace('#^(.*)$#', '\'\1\'', $unique_words)) . ")";
			$result = $db->sql_query($sql);

			while ( $row = $db->sql_fetchrow($result) )
			{
				if ( !empty($words['del']['title']) )
				{
					$words['del']['title'][] = $row['word_id'];
				}

				if ( !empty($words['del']['text']) )
				{
					$words['del']['text'][] = $row['word_id'];
				}
			}
			$db->sql_freeresult($result);

			unset($unique_words);
		}

		foreach ( $words as $sql_type => $word_in_ary )
		{
			foreach ( $word_in_ary as $word_in => $word_ary )
			{
				$word_sql = ( $sql_type == 'add' ) ? implode(', ', preg_replace('#^(.*)$#', '\'\1\'', $word_ary)) : implode(', ', $word_id);
				$title_match = ( $word_in == 'title' ) ? 1 : 0;

				if ( $word_sql != '' )
				{
					echo "<br />" . $sql = ( $sql_type == 'add' ) ? "INSERT INTO " . SEARCH_MATCH_TABLE . " (post_id, word_id, title_match)	SELECT $post_id, word_id, $title_match FROM " . SEARCH_WORD_TABLE . "	WHERE word_text IN ($word_sql)" : "DELETE FROM " . SEARCH_MATCH_TABLE . " WHERE post_id = $post_id	AND title_match = $title_match AND word_id IN ($word_sql)";
					$db->sql_query($sql);
				}
			}
		}

		unset($words);
		unset($word_in_ary);

		$mtime = explode(' ', microtime());
		echo "<br /><br />";
		echo $mtime[1] + $mtime[0] - $starttime;
		echo "<br /><br />";
		print_r($new_words);
		echo "<br /><br />";
		print_r($del_words);
		echo "<br /><br />";

		// Run the cleanup infrequently, once per session cleanup
		if ( $config['session_last_gc'] < time - ( $config['session_gc'] / 2 ) )
		{
			$this->search_tidy();
		}
	}

	//
	// Tidy up indexes, tag 'common words', remove
	// words no longer referenced in the match table, etc.
	//
	function search_tidy()
	{
		global $db;

		// Remove common (> 60% of posts ) words
		$result = $db->sql_query("SELECT SUM(forum_posts) AS total_posts FROM " . FORUMS_TABLE);

		$row = $db->sql_fetchrow($result);

		if ( $row['total_posts'] >= 100 )
		{
			$sql = "SELECT word_id
				FROM " . SEARCH_MATCH_TABLE . "
				GROUP BY word_id
				HAVING COUNT(word_id) > " . floor($row['total_posts'] * 0.6);
			$result = $db->sql_query($sql);

			$in_sql = '';
			while ( $row = $db->sql_fetchrow($result) )
			{
				$in_sql .= ( ( $in_sql != '' ) ? ', ' : '' ) . $row['word_id'];
			}
			$db->sql_freeresult($result);

			if ( $in_sql )
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

		if ( $row = $db->sql_fetchrow($result) )
		{
			$in_sql = '';
			while ( $row = $db->sql_fetchrow($result) )
			{
				$in_sql .= ( ( $in_sql != '' ) ? ', ' : '' ) . $row['word_id'];
			}
			$db->sql_freeresult($result);

			if ( $in_sql )
			{
				$sql = "DELETE FROM " . SEARCH_WORD_TABLE . "
					WHERE word_id IN ($in_sql)";
				$db->sql_query($sql);
			}
		}
	}
}

//
// Fill smiley templates (or just the variables) with smileys
// Either in a window or inline
//
function generate_smilies($mode)
{
	global $SID, $auth, $db, $session, $config, $template, $theme, $lang;
	global $user_ip, $starttime;
	global $phpEx, $phpbb_root_path;
	global $user, $userdata;

	if ( $mode == 'window' )
	{
		$page_title = $lang['Review_topic'] . " - $topic_title";
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			'smiliesbody' => 'posting_smilies.html')
		);
	}

	$where_sql = ( $mode == 'inline' ) ? 'WHERE smile_on_posting = 1 ' : '';
	$sql = "SELECT emoticon, code, smile_url, smile_width, smile_height
		FROM " . SMILIES_TABLE . "
		$where_sql
		ORDER BY smile_order, smile_width, smile_height, smilies_id";
	$result = $db->sql_query($sql);

	$num_smilies = 0;
	$smile_array = array();
	if ( $row = $db->sql_fetchrow($result) )
	{
		do
		{
			if ( !in_array($row['smile_url'], $smile_array) )
			{
				if ( $mode == 'window' || ( $mode == 'inline' && $num_smilies < 20 ) )
				{
					$template->assign_block_vars('emoticon', array(
						'SMILEY_CODE' => $row['code'],
						'SMILEY_IMG' => $config['smilies_path'] . '/' . $row['smile_url'],
						'SMILEY_WIDTH' => $row['smile_width'],
						'SMILEY_HEIGHT' => $row['smile_height'],
						'SMILEY_DESC' => $row['emoticon'])
					);
				}

				$smile_array[] = $row['smile_url'];
				$num_smilies++;
			}
		}
		while ( ( $row = $db->sql_fetchrow($result) ) );

		$db->sql_freeresult($result);

		if ( $mode == 'inline' && $num_smilies >= 20 )
		{
			$template->assign_vars(array(
				'S_SHOW_EMOTICON_LINK' => true,
				'L_MORE_SMILIES' => $lang['More_emoticons'],
				'U_MORE_SMILIES' => "posting.$phpEx$SID&amp;mode=smilies")
			);
		}

		$template->assign_vars(array(
			'L_EMOTICONS' => $lang['Emoticons'],
			'L_CLOSE_WINDOW' => $lang['Close_window'],
			'S_SMILIES_COLSPAN' => $s_colspan)
		);
	}

	if ( $mode == 'window' )
	{
		$template->display('smiliesbody');

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
}
//
// END NEW CODE
// ---------------------------------------------

?>