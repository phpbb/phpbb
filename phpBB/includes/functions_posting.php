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
		global $board_config, $db, $lang;

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
		if ( !strlen($message) || ( $board_config['max_post_chars'] && strlen($message) > $board_config['max_post_chars'] ) )
		{
			$warn_msg .= ( !strlen($message) ) ? $lang['Too_few_chars'] . '<br />' : $lang['Too_many_chars'] . '<br />';
		}

		// Smiley check
		if ( $board_config['max_post_smilies'] && $smilies )
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

				if ( $match > $board_config['max_post_smilies'] )
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
//			return $warn_msg;
		}

		$warn_msg .= $this->html($message, $html);
		$warn_msg .= $this->bbcode($message, $bbcode, $uid);
		$warn_msg .= $this->magic_url($message, $url);
		$warn_msg .= $this->attach($_FILE);

		return $warn_msg;
	}

	function html(&$message, $html)
	{
		global $board_config, $lang;

		if ( $html )
		{
			// If $html is true then "allowed_tags" are converted back from entity
			// form, others remain
			$allowed_tags = split(',', str_replace(' ', '', $board_config['allow_html_tags']));

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
		global $board_config;

	}

	// Replace magic urls of form http://xxx.xxx., www.xxx. and xxx@xxx.xxx.
	// Cuts down displayed size of link if over 50 chars, turns absolute links
	// into relative versions when the server/script path matches the link
	function magic_url(&$message, $url)
	{
		global $board_config;

		if ( $url )
		{
			$server_protocol = ( $board_config['cookie_secure'] ) ? 'https://' : 'http://';
			$server_port = ( $board_config['server_port'] <> 80 ) ? ':' . trim($board_config['server_port']) . '/' : '/';

			$match = array();
			$replace = array();

			// relative urls for this board
			$match[] = '#' . $server_protocol . trim($board_config['server_name']) . $server_port . preg_replace('/^\/?(.*?)(\/)?$/', '\1', trim($board_config['script_path'])) . '/([^\t <\n\r\"]+)#i';
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
		global $board_config;

		$allowed_ext = explode(',', $board_config['attach_ext']);
	}
}

// Will parse poll info ... probably
class parse_poll extends parse_message
{
	function parse_poll()
	{
		global $board_config;

	}
}

// Parses a given message and updates/maintains
// the fulltext word indexes NOTE this is not complete
// nor 'entirely' (!) functional ...
class fulltext_search
{
	function split_words(&$text)
	{
		global $phpbb_root_path, $board_config;

		static $drop_char_match =   array('^', '$', '&', '(', ')', '<', '>', '`', '\'', '"', '|', ',', '@', '_', '?', '%', '-', '~', '+', '.', '[', ']', '{', '}', ':', '\\', '/', '=', '#', '\'', ';', '!',   '*');
		static $drop_char_replace = array(' ', ' ', ' ', ' ', ' ', ' ', ' ', '',  '',   ' ', ' ', ' ', ' ', '',  ' ', ' ', '',  ' ',   ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ' , ' ', ' ', ' ', ' ',  ' ', ' ', ' ');
		$stopwords_array = @file($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/search_stopwords.txt');
		$synonym_array = @file($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/search_synonyms.txt');

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
		$match[] = '#\b([a-z0-9]{1,' . $board_config['min_search_chars'] . '}|[a-z0-9]{' . $board_config['max_search_chars'] . ',})\b#';

		$text = preg_replace($match, ' ', ' ' . strtolower($text) . ' ');

		// Filter out non-alphabetical chars
		for($i = 0; $i < count($drop_char_match); $i++)
		{
			$text = str_replace($drop_char_match[$i], $drop_char_replace[$i], $text);
		}

		if ( !empty($stopword_list) )
		{
			$text = str_replace($stopword_list, '', $text);
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

	function add(&$post_id, &$new_msg, &$new_title, $old_msg = '', $old_title = '')
	{
		global $board_config, $db;

		$mtime = explode(' ', microtime());
		$starttime = $mtime[1] + $mtime[0];

		//
		// Split old and new post/subject to obtain array of 'words'
		//
		$split_text_new = $this->split_words($new_msg);
		$split_text_old = $this->split_words(addslashes($old_msg));
		$split_title_new = ( $new_title ) ? $this->split_words($new_title) : array();
		$split_title_old = ( $old_title ) ? $this->split_words(addslashes($old_title)) : array();

		//
		// Define new words to be added and old words to be removed
		//
		$words = array();
		$words['add']['text'] = array_diff($split_text_new, $split_text_old);
		$words['del']['text'] = array_diff($split_text_old, $split_text_new);
		$words['add']['title'] = array_diff($split_title_new, $split_title_old);
		$words['del']['title'] = array_diff($split_title_old, $split_title_new);

		//
		// Get unique words from the above arrays
		//
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
		if ( $board_config['session_last_gc'] < time - ( $board_config['session_gc'] / 2 ) )
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
	global $SID, $auth, $db, $session, $board_config, $template, $theme, $lang;
	global $user_ip, $starttime;
	global $phpEx, $phpbb_root_path;
	global $userdata;

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
						'SMILEY_IMG' => $board_config['smilies_path'] . '/' . $row['smile_url'],
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

// ---------------------------------------------
// OLD CODE FROM 2.0.x
//
define('BBCODE_UID_LEN', 10);

$html_entities_match = array('#&#', '#<#', '#>#');
$html_entities_replace = array('&amp;', '&lt;', '&gt;');

$unhtml_specialchars_match = array('#&gt;#', '#&lt;#', '#&quot;#', '#&amp;#');
$unhtml_specialchars_replace = array('>', '<', '"', '&');

//
// This function will prepare a posted message for
// entry into the database.
//
function prepare_message($message, $html_on, $bbcode_on, $smile_on, $bbcode_uid = 0)
{
	global $board_config;
	global $html_entities_match, $html_entities_replace;
	global $code_entities_match, $code_entities_replace;

	//
	// Clean up the message
	//
	$message = trim($message);

	if ( $html_on )
	{


		$end_html = 0;
		$start_html = 1;
		$tmp_message = '';
		$message = ' ' . $message . ' ';





		while ( $start_html = strpos($message, '<', $start_html) )
		{
			$tmp_message .= preg_replace($html_entities_match, $html_entities_replace, substr($message, $end_html + 1, ( $start_html - $end_html - 1 )));

			if ( $end_html = strpos($message, '>', $start_html) )
			{
				$length = $end_html - $start_html + 1;
				$hold_string = substr($message, $start_html, $length);

				if ( ( $unclosed_open = strrpos(' ' . $hold_string, '<') ) != 1 )
				{
					$tmp_message .= preg_replace($html_entities_match, $html_entities_replace, substr($hold_string, 0, $unclosed_open - 1));
					$hold_string = substr($hold_string, $unclosed_open - 1);
				}

				$tagallowed = false;
				for($i = 0; $i < sizeof($allowed_html_tags); $i++)
				{
					$match_tag = trim($allowed_html_tags[$i]);

					if ( preg_match('/^<\/?' . $match_tag . '\b/i', $hold_string) )
					{
						$tagallowed = true;
					}
				}

				$tmp_message .= ( $length && !$tagallowed ) ? preg_replace($html_entities_match, $html_entities_replace, $hold_string) : $hold_string;

				$start_html += $length;
			}
			else
			{
				$tmp_message .= preg_replace($html_entities_match, $html_entities_replace, substr($message, $start_html, strlen($message)));

				$start_html = strlen($message);
				$end_html = $start_html;
			}
		}

		if ( $end_html != strlen($message) && $tmp_message != '' )
		{
			$tmp_message .= preg_replace($html_entities_match, $html_entities_replace, substr($message, $end_html + 1));
		}

		$message = ( $tmp_message != '' ) ? trim($tmp_message) : trim($message);
	}
	else
	{
		$message = preg_replace($html_entities_match, $html_entities_replace, $message);
	}

	if( $bbcode_on && $bbcode_uid != '' )
	{
		$tmp_message = $message;
		if ( ($match_count = preg_match_all('#^(.*?)\[code\](.*?)\[\/code\](.*?)$#is', $tmp_message, $match)) )
		{
			$code_entities_match = array('#<#', '#>#', '#"#', '#:#', '#\[#', '#\]#', '#\(#', '#\)#', '#\{#', '#\}#');
			$code_entities_replace = array('&lt;', '&gt;', '&quot;', '&#58;', '&#91;', '&#93;', '&#40;', '&#41;', '&#123;', '&#125;');

			$message = '';

			for($i = 0; $i < $match_count; $i++)
			{
				$message .= $match[1][$i] . '[code]' . preg_replace($code_entities_match, $code_entities_replace, $match[2][$i]) . '[/code]';
				$tmp_message = $match[3][$i];
			}

			$message .= $tmp_message;
		}

		$message = bbencode_first_pass($message, $bbcode_uid);
	}

	return $message;
}

function unprepare_message($message)
{
	global $unhtml_specialchars_match, $unhtml_specialchars_replace;

	return preg_replace($unhtml_specialchars_match, $unhtml_specialchars_replace, $message);
}

//
// Prepare a message for posting
//
function prepare_post(&$mode, &$post_data, &$bbcode_on, &$html_on, &$smilies_on, &$error_msg, &$username, &$bbcode_uid, &$subject, &$message, &$poll_title, &$poll_options, &$poll_length)
{
	global $board_config, $userdata, $lang, $phpEx, $phpbb_root_path;

	// Check username
	if ( !empty($username) )
	{
		$username = htmlspecialchars(trim(strip_tags($username)));

		if ( !$userdata['session_logged_in'] || ( $userdata['session_logged_in'] && $username != $userdata['username'] ) )
		{
			include($phpbb_root_path . 'includes/functions_validate.'.$phpEx);

			$result = validate_username($username);
			if ( $result['error'] )
			{
				$error_msg .= ( !empty($error_msg) ) ? '<br />' . $result['error_msg'] : $result['error_msg'];
			}
		}
	}

	// Check subject
	if ( !empty($subject) )
	{
		$subject = htmlspecialchars(trim($subject));
	}
	else if ( $mode == 'newtopic' || ( $mode == 'editpost' && $post_data['first_post'] ) )
	{
		$error_msg .= ( !empty($error_msg) ) ? '<br />' . $lang['Empty_subject'] : $lang['Empty_subject'];
	}

	// Check message
	if ( !empty($message) )
	{
		$bbcode_uid = ( $bbcode_on ) ? make_bbcode_uid() : '';
		$message = prepare_message(trim($message), $html_on, $bbcode_on, $smilies_on, $bbcode_uid);
	}
	else if ( $mode != 'delete' && $mode != 'polldelete' )
	{
		$error_msg .= ( !empty($error_msg) ) ? '<br />' . $lang['Empty_message'] : $lang['Empty_message'];
	}

	//
	// Handle poll stuff
	//
	if ( $mode == 'newtopic' || ( $mode == 'editpost' && $post_data['first_post'] ) )
	{
		$poll_length = ( isset($poll_length) ) ? max(0, intval($poll_length)) : 0;

		if ( !empty($poll_title) )
		{
			$poll_title = htmlspecialchars(trim($poll_title));
		}

		if( !empty($poll_options) )
		{
			$temp_option_text = array();
			while( list($option_id, $option_text) = @each($poll_options) )
			{
				$option_text = trim($option_text);
				if ( !empty($option_text) )
				{
					$temp_option_text[$option_id] = htmlspecialchars($option_text);
				}
			}
			$option_text = $temp_option_text;

			if ( count($poll_options) < 2 )
			{
				$error_msg .= ( !empty($error_msg) ) ? '<br />' . $lang['To_few_poll_options'] : $lang['To_few_poll_options'];
			}
			else if ( count($poll_options) > $board_config['max_poll_options'] )
			{
				$error_msg .= ( !empty($error_msg) ) ? '<br />' . $lang['To_many_poll_options'] : $lang['To_many_poll_options'];
			}
			else if ( $poll_title == '' )
			{
				$error_msg .= ( !empty($error_msg) ) ? '<br />' . $lang['Empty_poll_title'] : $lang['Empty_poll_title'];
			}
		}
	}

	return;
}

//
// Post a new topic/reply/poll or edit existing post/poll
//
function submit_post($mode, &$post_data, &$message, &$meta, &$forum_id, &$topic_id, &$post_id, &$poll_id, &$topic_type, &$bbcode_on, &$html_on, &$smilies_on, &$attach_sig, &$bbcode_uid, &$post_username, &$post_subject, &$post_message, &$poll_title, &$poll_options, &$poll_length)
{
	global $board_config, $lang, $db, $phpbb_root_path, $phpEx;
	global $userdata, $user_ip;

	$current_time = time();

	if ( $mode == 'newtopic' || $mode == 'reply' )
	{
		//
		// Flood control
		//
		$where_sql = ( $userdata['user_id'] == ANONYMOUS ) ? "poster_ip = '$user_ip'" : 'poster_id = ' . $userdata['user_id'];
		$sql = "SELECT MAX(post_time) AS last_post_time
			FROM " . POSTS_TABLE . "
			WHERE $where_sql";
		if ( $result = $db->sql_query($sql) )
		{
			if( $row = $db->sql_fetchrow($result) )
			{
				if ( $row['last_post_time'] > 0 && ( $current_time - $row['last_post_time'] ) < $board_config['flood_interval'] )
				{
					message_die(GENERAL_MESSAGE, $lang['Flood_Error']);
				}
			}
		}
	}
	else if ( $mode == 'editpost' )
	{
		remove_search_post($post_id);
	}

	if ( $mode == 'newtopic' || ( $mode == 'editpost' && $post_data['first_post'] ) )
	{
		$topic_vote = ( !empty($poll_title) && count($poll_options) >= 2 ) ? 1 : 0;
		$sql  = ( $mode != "editpost" ) ? "INSERT INTO " . TOPICS_TABLE . " (topic_title, topic_poster, topic_time, forum_id, topic_status, topic_type, topic_vote) VALUES ('$post_subject', " . $userdata['user_id'] . ", $current_time, $forum_id, " . TOPIC_UNLOCKED . ", $topic_type, $topic_vote)" : "UPDATE " . TOPICS_TABLE . " SET topic_title = '$post_subject', topic_type = $topic_type, topic_vote = $topic_vote WHERE topic_id = $topic_id";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
		}

		if( $mode == 'newtopic' )
		{
			$topic_id = $db->sql_nextid();
		}
	}

	$edited_sql = ( $mode == 'editpost' && !$post_data['last_post'] && $post_data['poster_post'] ) ? ", post_edit_time = $current_time, post_edit_count = post_edit_count + 1 " : "";
	$sql = ( $mode != "editpost" ) ? "INSERT INTO " . POSTS_TABLE . " (topic_id, forum_id, poster_id, post_username, post_time, poster_ip, enable_bbcode, enable_html, enable_smilies, enable_sig) VALUES ($topic_id, $forum_id, " . $userdata['user_id'] . ", '$post_username', $current_time, '$user_ip', $bbcode_on, $html_on, $smilies_on, $attach_sig)" : "UPDATE " . POSTS_TABLE . " SET enable_bbcode = $bbcode_on, enable_html = $html_on, enable_smilies = $smilies_on, enable_sig = $attach_sig" . $edited_sql . " WHERE post_id = $post_id";
	if ( !($result = $db->sql_query($sql, BEGIN_TRANSACTION)) )
	{
		message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
	}

	if( $mode != 'editpost' )
	{
		$post_id = $db->sql_nextid();
	}

	$sql = ( $mode != 'editpost' ) ? "INSERT INTO " . POSTS_TEXT_TABLE . " (post_id, post_subject, bbcode_uid, post_text) VALUES ($post_id, '$post_subject', '$bbcode_uid', '$post_message')" : "UPDATE " . POSTS_TEXT_TABLE . " SET post_text = '$post_message',  bbcode_uid = '$bbcode_uid', post_subject = '$post_subject' WHERE post_id = $post_id";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
	}

	add_search_words($post_id, stripslashes($post_message), stripslashes($post_subject));

	//
	// Add poll
	//
	if ( ( $mode == 'newtopic' || $mode == 'editpost' ) && !empty($poll_title) && count($poll_options) >= 2 )
	{
		$sql = ( !$post_data['has_poll'] ) ? "INSERT INTO " . VOTE_DESC_TABLE . " (topic_id, vote_text, vote_start, vote_length) VALUES ($topic_id, '$poll_title', $current_time, " . ( $poll_length * 86400 ) . ")" : "UPDATE " . VOTE_DESC_TABLE . " SET vote_text = '$poll_title', vote_length = " . ( $poll_length * 86400 ) . " WHERE topic_id = $topic_id";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
		}

		$delete_option_sql = '';
		$old_poll_result = array();
		if ( $mode == 'editpost' && $post_data['has_poll'] )
		{
			$sql = "SELECT vote_option_id, vote_result
				FROM " . VOTE_RESULTS_TABLE . "
				WHERE vote_id = $poll_id
				ORDER BY vote_option_id ASC";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not obtain vote data results for this topic', '', __LINE__, __FILE__, $sql);
			}

			while ( $row = $db->sql_fetchrow($result) )
			{
				$old_poll_result[$row['vote_option_id']] = $row['vote_result'];

				if( !isset($poll_options[$row['vote_option_id']]) )
				{
					$delete_option_sql .= ( $delete_option_sql != '' ) ? ', ' . $row['vote_option_id'] : $row['vote_option_id'];
				}
			}
		}
		else
		{
			$poll_id = $db->sql_nextid();
		}

		@reset($poll_options);

		$poll_option_id = 1;
		while ( list($option_id, $option_text) = each($poll_options) )
		{
			if( !empty($option_text) )
			{
				$option_text = str_replace("\'", "''", $option_text);
				$poll_result = ( $mode == "editpost" && isset($old_poll_result[$option_id]) ) ? $old_poll_result[$option_id] : 0;

				$sql = ( $mode != "editpost" || !isset($old_poll_result[$option_id]) ) ? "INSERT INTO " . VOTE_RESULTS_TABLE . " (vote_id, vote_option_id, vote_option_text, vote_result) VALUES ($poll_id, $poll_option_id, '$option_text', $poll_result)" : "UPDATE " . VOTE_RESULTS_TABLE . " SET vote_option_text = '$option_text', vote_result = $poll_result WHERE vote_option_id = $option_id AND vote_id = $poll_id";
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
				}
				$poll_option_id++;
			}
		}

		if( $delete_option_sql != '' )
		{
			$sql = "DELETE FROM " . VOTE_RESULTS_TABLE . "
				WHERE vote_option_id IN ($delete_option_sql)";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Error deleting pruned poll options', '', __LINE__, __FILE__, $sql);
			}
		}
	}

	$meta = '<meta http-equiv="refresh" content="3;url=' . "viewtopic.$phpEx$SID&amp;" . POST_POST_URL . "=" . $post_id . '#' . $post_id . '">';
	$message = $lang['Stored'] . '<br /><br />' . sprintf($lang['Click_view_message'], '<a href="' . "viewtopic.$phpEx$SID&amp;" . POST_POST_URL . "=" . $post_id . '#' . $post_id . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_forum'], '<a href="' . "viewforum.$phpEx$SID&amp;" . POST_FORUM_URL . "=$forum_id" . '">', '</a>');

	return false;
}

//
// Update post stats and details
//
function update_post_stats(&$mode, &$post_data, &$forum_id, &$topic_id, &$post_id, &$user_id)
{
	global $db;

	$sign = ( $mode == 'delete' ) ? '- 1' : '+ 1';
	$forum_update_sql = "forum_posts = forum_posts $sign";
	$topic_update_sql = '';

	if ( $mode == 'delete' )
	{
		if ( $post_data['last_post'] )
		{
			if ( $post_data['first_post'] )
			{
				$forum_update_sql .= ', forum_topics = forum_topics - 1';
			}
			else
			{

				$topic_update_sql .= "topic_replies = topic_replies - 1";

				$sql = "SELECT MAX(post_id) AS post_id
					FROM " . POSTS_TABLE . "
					WHERE topic_id = $topic_id";
				if ( !($db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
				}

				if ( $row = $db->sql_fetchrow($result) )
				{
					$topic_update_sql .= ', topic_last_post_id = ' . $row['post_id'];
				}
			}

			if ( $post_data['last_topic'] )
			{
				$sql = "SELECT MAX(post_id) AS post_id
					FROM " . POSTS_TABLE . "
					WHERE forum_id = $forum_id";
				if ( !($db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
				}

				if ( $row = $db->sql_fetchrow($result) )
				{
					$forum_update_sql .= ( $row['post_id'] ) ? ', forum_last_post_id = ' . $row['post_id'] : ', forum_last_post_id = 0';
				}
			}
		}
		else if ( $post_data['first_post'] )
		{
			$sql = "SELECT MIN(post_id) AS post_id
				FROM " . POSTS_TABLE . "
				WHERE topic_id = $topic_id";
			if ( !($db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
			}

			if ( $row = $db->sql_fetchrow($result) )
			{
				$topic_update_sql .= 'topic_replies = topic_replies - 1, topic_first_post_id = ' . $row['post_id'];
			}
		}
		else
		{
			$topic_update_sql .= 'topic_replies = topic_replies - 1';
		}
	}
	else if ( $mode != 'poll_delete' )
	{
		$forum_update_sql .= ", forum_last_post_id = $post_id" . ( ( $mode == 'newtopic' ) ? ", forum_topics = forum_topics $sign" : "" );
		$topic_update_sql = "topic_last_post_id = $post_id" . ( ( $mode == 'reply' ) ? ", topic_replies = topic_replies $sign" : ", topic_first_post_id = $post_id" );
	}
	else
	{
		$topic_update_sql .= 'topic_vote = 0';
	}

	$sql = "UPDATE " . FORUMS_TABLE . " SET
		$forum_update_sql
		WHERE forum_id = $forum_id";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
	}

	if ( $topic_update_sql != '' )
	{
		$sql = "UPDATE " . TOPICS_TABLE . " SET
			$topic_update_sql
			WHERE topic_id = $topic_id";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
		}
	}

	if ( $mode != 'poll_delete' )
	{
		$sql = "UPDATE " . USERS_TABLE . "
			SET user_posts = user_posts $sign
			WHERE user_id = $user_id";
		if ( !($result = $db->sql_query($sql, END_TRANSACTION)) )
		{
			message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
		}
	}

	return;
}

//
// Delete a post/poll
//
function delete_post($mode, &$post_data, &$message, &$meta, &$forum_id, &$topic_id, &$post_id, &$poll_id)
{
	global $board_config, $lang, $db, $phpbb_root_path, $phpEx;
	global $userdata, $user_ip;

	$topic_update_sql = '';
	if ( $mode != 'poll_delete' )
	{
		$sql = "DELETE FROM " . POSTS_TABLE . "
			WHERE post_id = $post_id";
		if ( !($db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
		}

		$sql = "DELETE FROM " . POSTS_TEXT_TABLE . "
			WHERE post_id = $post_id";
		if ( !($db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
		}

		$sql = "DELETE FROM " . SEARCH_MATCH_TABLE . "
			WHERE post_id = $post_id";
		if ( !($db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
		}

		$forum_update_sql = 'forum_posts = forum_posts - 1';
		$topic_update_sql .= 'topic_replies = topic_replies - 1';
		if ( $post_data['last_post'] )
		{
			if ( $post_data['first_post'] )
			{
				$sql = "DELETE FROM " . TOPICS_TABLE . "
					WHERE topic_id = $topic_id
						OR topic_moved_id = $topic_id";
				if ( !($db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
				}

				$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . "
					WHERE topic_id = $topic_id";
				if ( !($db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
				}
			}
		}
	}

	if( $mode == 'poll_delete' || ( $mode == 'delete' && $post_data['first_post'] && $post_data['last_post'] ) && $post_data['has_poll'] && $post_data['edit_poll'] )
	{
		$sql = "DELETE FROM " . VOTE_DESC_TABLE . "
			WHERE vote_id = $poll_id";
		if ( !($db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in deleting poll', '', __LINE__, __FILE__, $sql);
		}

		$sql = "DELETE FROM " . VOTE_RESULTS_TABLE . "
			WHERE vote_id = $poll_id";
		if ( !($db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in deleting poll', '', __LINE__, __FILE__, $sql);
		}

		$sql = "DELETE FROM " . VOTE_USERS_TABLE . "
			WHERE vote_id = $poll_id";
		if ( !($db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in deleting poll', '', __LINE__, __FILE__, $sql);
		}
	}

	remove_search_post($post_id);

	if ( $mode == 'delete' && $post_data['first_post'] && $post_data['last_post'] )
	{
		$meta = '<meta http-equiv="refresh" content="3;url=' . "viewforum.$phpEx$SID&amp;" . POST_FORUM_URL . "=" . $forum_id . '">';
		$message = $lang['Deleted'];
	}
	else
	{
		$meta = '<meta http-equiv="refresh" content="3;url=' . "viewtopic.$phpEx$SID&amp;" . POST_TOPIC_URL . "=" . $topic_id . '">';
		$message = ( ( $mode == "poll_delete" ) ? $lang['Poll_delete'] : $lang['Deleted'] ) . '<br /><br />' . sprintf($lang['Click_return_topic'], '<a href="' . "viewtopic.$phpEx$SID&amp;" . POST_TOPIC_URL . "=$topic_id" . '">', '</a>');
	}

	$message .=  '<br /><br />' . sprintf($lang['Click_return_forum'], '<a href="' . "viewforum.$phpEx$SID&amp;" . POST_FORUM_URL . "=$forum_id" . '">', '</a>');

	return;
}

//
// Handle user notification on new post
//
function user_notification($mode, &$post_data, &$forum_id, &$topic_id, &$post_id, &$notify_user)
{
	global $board_config, $lang, $db, $phpbb_root_path, $phpEx;
	global $userdata, $user_ip;

	$current_time = time();

	if ( $mode == 'delete' )
	{
		$delete_sql = ( !$post_data['first_post'] && !$post_data['last_post'] ) ? " AND user_id = " . $userdata['user_id'] : "";
		$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . " WHERE topic_id = $topic_id" . $delete_sql;
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not change topic notify data', '', __LINE__, __FILE__, $sql);
		}
	}
	else
	{
		if ( $mode == 'reply' || $mode == 'newtopic' )
		{
			$sql = "SELECT ban_userid
				FROM " . BANLIST_TABLE;
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not obtain banlist', '', __LINE__, __FILE__, $sql);
			}

			$user_id_sql = '';
			while ( $row = $db->sql_fetchrow($result) )
			{
				if ( isset($row['ban_userid']) )
				{
					$user_id_sql = ', ' . $row['ban_userid'];
				}
			}

			$sql = "SELECT u.user_id, u.username, u.user_email, u.user_lang, f.forum_name
				FROM " . FORUMS_WATCH_TABLE . " w, " . FORUMS_TABLE . " f, " . USERS_TABLE . " u
				WHERE w.forum_id = $forum_id
					AND w.user_id NOT IN (" . $userdata['user_id'] . ", " . ANONYMOUS . $user_id_sql . " )
					AND w.notify_status = " . TOPIC_WATCH_UN_NOTIFIED . "
					AND f.forum_id = w.forum_id
					AND u.user_id = w.user_id";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not obtain list of forum watchers', '', __LINE__, __FILE__, $sql);
			}

			$orig_word = array();
			$replacement_word = array();
			obtain_word_list($orig_word, $replacement_word);

			include($phpbb_root_path . 'includes/emailer.'.$phpEx);
			$emailer = new emailer($board_config['smtp_delivery']);

			$script_name = preg_replace('/^\/?(.*?)\/?$/', '\1', trim($board_config['script_path']));
			$script_name_f = ( $script_name != '' ) ? $script_name . '/viewforum.'.$phpEx : 'viewforum.'.$phpEx;
			$server_name = trim($board_config['server_name']);
			$server_protocol = ( $board_config['cookie_secure'] ) ? 'https://' : 'http://';
			$server_port = ( $board_config['server_port'] <> 80 ) ? ':' . trim($board_config['server_port']) . '/' : '/';

			$email_headers = "From: " . $board_config['board_email'] . "\nReturn-Path: " . $board_config['board_email'] . "\r\n";

			$update_watched_sql = '';
			if ( $row = $db->sql_fetchrow($result) )
			{
				$forum_name = unprepare_message($row['forum_name']);

				do
				{
					if ( $row['user_email'] != '' )
					{
						$emailer->use_template('forum_notify', $row['user_lang']);
						$emailer->email_address($row['user_email']);
						$emailer->set_subject();//$lang['Topic_reply_notification']
						$emailer->extra_headers($email_headers);

						$emailer->assign_vars(array(
							'EMAIL_SIG' => str_replace('<br />', "\n", "-- \n" . $board_config['board_email_sig']),
							'USERNAME' => $row['username'],
							'SITENAME' => $board_config['sitename'],
							'FORUM_NAME' => $forum_name,

							'U_FORUM' => $server_protocol . $server_name . $server_port . $script_name_f . '?' . POST_FORUM_URL . "=$forum_id",
							'U_STOP_WATCHING_FORUM' => $server_protocol . $server_name . $server_port . $script_name_f . '?' . POST_FORUM_URL . "=$forum_id&unwatch=forum")
						);

						$emailer->send();
						$emailer->reset();

						$update_watched_sql .= ( $update_watched_sql != '' ) ? ', ' . $row['user_id'] : $row['user_id'];
					}
				}
				while ( $row = $db->sql_fetchrow($result) );
			}

			if ( $update_watched_sql != '' )
			{
				$sql = "UPDATE " . FORUMS_WATCH_TABLE . "
					SET notify_status = " . TOPIC_WATCH_NOTIFIED . "
					WHERE forum_id = $forum_id
						AND user_id IN ($update_watched_sql)";
				$db->sql_query($sql);
			}

			if ( $mode == 'reply' )
			{
				$sql = "SELECT u.user_id, u.username, u.user_email, u.user_lang, t.topic_title
					FROM " . TOPICS_WATCH_TABLE . " tw, " . TOPICS_TABLE . " t, " . USERS_TABLE . " u
					WHERE tw.topic_id = $topic_id
						AND tw.user_id NOT IN (" . $userdata['user_id'] . ", " . ANONYMOUS . $user_id_sql . " )
						AND tw.notify_status = " . TOPIC_WATCH_UN_NOTIFIED . "
						AND t.topic_id = tw.topic_id
						AND u.user_id = tw.user_id";
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Could not obtain list of topic watchers', '', __LINE__, __FILE__, $sql);
				}

				$script_name_t = ( $script_name != '' ) ? $script_name . '/viewtopic.'.$phpEx : 'viewtopic.'.$phpEx;
				$email_headers = "From: " . $board_config['board_email'] . "\nReturn-Path: " . $board_config['board_email'] . "\r\n";

				$update_watched_sql = '';
				if ( $row = $db->sql_fetchrow($result) )
				{
					$topic_title = preg_replace($orig_word, $replacement_word, unprepare_message($row['topic_title']));

					do
					{
						if ( $row['user_email'] != '' )
						{
							$emailer->use_template('topic_notify', $row['user_lang']);
							$emailer->email_address($row['user_email']);
							$emailer->set_subject();//$lang['Topic_reply_notification']
							$emailer->extra_headers($email_headers);

							$emailer->assign_vars(array(
								'EMAIL_SIG' => str_replace('<br />', "\n", "-- \n" . $board_config['board_email_sig']),
								'USERNAME' => $row['username'],
								'SITENAME' => $board_config['sitename'],
								'TOPIC_TITLE' => $topic_title,

								'U_TOPIC' => $server_protocol . $server_name . $server_port . $script_name_t . '?' . POST_POST_URL . "=$post_id#$post_id",
								'U_STOP_WATCHING_TOPIC' => $server_protocol . $server_name . $server_port . $script_name_t . '?' . POST_TOPIC_URL . "=$topic_id&unwatch=topic")
							);

							$emailer->send();
							$emailer->reset();

							$update_watched_sql .= ( $update_watched_sql != '' ) ? ', ' . $row['user_id'] : $row['user_id'];
						}
					}
					while ( $row = $db->sql_fetchrow($result) );
				}

				if ( $update_watched_sql != '' )
				{
					$sql = "UPDATE " . TOPICS_WATCH_TABLE . "
						SET notify_status = " . TOPIC_WATCH_NOTIFIED . "
						WHERE topic_id = $topic_id
							AND user_id IN ($update_watched_sql)";
					$db->sql_query($sql);
				}
			}

		}

		$sql = "SELECT topic_id
			FROM " . TOPICS_WATCH_TABLE . "
			WHERE topic_id = $topic_id
				AND user_id = " . $userdata['user_id'];
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not obtain topic watch information', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);

		if ( !$notify_user && !empty($row['topic_id']) )
		{
			$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . "
				WHERE topic_id = $topic_id
					AND user_id = " . $userdata['user_id'];
			if ( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, 'Could not delete topic watch information', '', __LINE__, __FILE__, $sql);
			}
		}
		else if ( $notify_user && empty($row['topic_id']) )
		{
			$sql = "INSERT INTO " . TOPICS_WATCH_TABLE . " (user_id, topic_id, notify_status)
				VALUES (" . $userdata['user_id'] . ", $topic_id, 0)";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not insert topic watch information', '', __LINE__, __FILE__, $sql);
			}
		}
	}
}

?>