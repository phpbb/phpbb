<?php
/***************************************************************************
*                                  search.php
*                              -------------------
*     begin                : Wed Sep 05 2001
*     copyright            : (C) 2001 The phpBB Group
*     email                : support@phpbb.com
*
*     $Id$
*
****************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

function clean_words($entry, &$stopword_list, &$synonym_list)
{
	// Weird, $init_match doesn't work with static when double quotes (") are used...
	static $init_match =   array('^', '$', '&', '(', ')', '<', '>', '`', "'", '|', ',', '@', '_', '?', '%');
	static $init_replace = array(" ", " ", " ", " ", " ", " ", " ", " ", "",  " ", " ", " ", " ", " ", " ");

	static $later_match =   array("-", "~", "+", ".", "[", "]", "{", "}", ":", "\\", "/", "=", "#", "\"", ";", "*", "!");
	static $later_replace = array(" ", " ", " ", " ", " ", " ", " ", " ", " ", " " , " ", " ", " ", " ",  " ", " ", " ");

	static $sgml_match = array("&nbsp;", "&szlig;", "&agrave;", "&aacute;", "&acirc;", "&atilde;", "&auml;", "&aring;", "&aelig;", "&ccedil;", "&egrave;", "&eacute;", "&ecirc;", "&euml;", "&igrave;", "&iacute;", "&icirc;", "&iuml;", "&eth;", "&ntilde;", "&ograve;", "&oacute;", "&ocirc;", "&otilde;", "&ouml;", "&oslash;", "&ugrave;", "&uacute;", "&ucirc;", "&uuml;", "&yacute;", "&thorn;", "&yuml;");
	static $sgml_replace = array(" ", "s", "a", "a", "a", "a", "a", "a", "a", "c", "e", "e", "e", "e", "i", "i", "i", "i", "o", "n", "o", "o", "o", "o", "o", "o", "u", "u", "u", "u", "y", "t", "y");

	static $accent_match = array("ß", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï", "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "þ", "ÿ");
	static $accent_replace = array("s", "a", "a", "a", "a", "a", "a", "a", "c", "e", "e", "e", "e", "i", "i", "i", "i", "o", "n", "o", "o", "o", "o", "o", "o", "u", "u", "u", "u", "y", "t", "y");

	$entry = " " . strip_tags(strtolower($entry)) . " ";

	$entry = str_replace($sgml_match, $sgml_match, $entry);
	$entry = str_replace($accent_match, $accent_replace, $entry);

	// Replace line endings by a space
	$entry = preg_replace("/[\n\r]/is", " ", $entry); 
	// Remove URL's
	$entry = preg_replace("/\b[a-z0-9]+:\/\/[a-z0-9\.\-]+(\/[a-z0-9\?\.%_\-\+=&\/]+)?/si", " ", $entry); 

	// Filter out strange characters like ^, $, &, change "it's" to "its"
	// str_replace with arrays is buggy in some PHP versions so traverse the arrays manually ;(
	for($i = 0; $i < count($init_match); $i++)
	{
		$entry = str_replace($init_match[$i], $init_replace[$i], $entry);
	}

	// Quickly remove BBcode.
	$entry = preg_replace("/\[code:[0-9]+:[0-9a-z]{10,}\].*?\[\/code:[0-9]+:[0-9a-z]{10,}\]/is", " ", $entry); 
	$entry = preg_replace("/\[img\].*?\[\/img\]/is", " ", $entry); 
	$entry = preg_replace("/\[\/?[a-z\*=\+\-]+[0-9a-z]?(\:[a-z0-9]+)?:[a-z0-9]{10,}(\:[a-z0-9]+)?=?.*?\]/si", " ", $entry);
	// URLs
	$entry = preg_replace("/\[\/?[a-z\*]+[=\+\-]?[0-9a-z]+?:[a-z0-9]{10,}[=.*?]?\]/si", " ", $entry);
	$entry = preg_replace("/\[\/?url(=.*?)?\]/si", " ", $entry);
	// Numbers
	$entry = preg_replace("/\b[0-9]+\b/si", " ", $entry); 
	// HTML entities like &1234;
	$entry = preg_replace("/\b&[a-z]+;\b/is", " ", $entry); 
	// 'words' that consist of <2 or >50 characters are removed.
	$entry = preg_replace("/\b[a-z0-9]{1,2}?\b/si", " ", $entry); 
	$entry = preg_replace("/\b[a-z0-9]{50,}?\b/si", " ", $entry); 

	// Remove some more strange characters
	for($i = 0; $i < count($later_match); $i++)
	{
		$entry = str_replace($later_match[$i], $later_replace[$i], $entry);
	}

	if( !empty($stopword_list) )
	{
		for ($j = 0; $j < count($stopword_list); $j++)
		{
			$filter_word = trim(strtolower($stopword_list[$j]));
			$entry =  preg_replace("/\b" . phpbb_preg_quote($filter_word, "/") . "\b/is", " ", $entry);
		}
	}

	if( !empty($synonym_list) )
	{
		for ($j = 0; $j < count($synonym_list); $j++)
		{
			list($replace_synonym, $match_synonym) = split(" ", trim(strtolower($synonym_list[$j])));
			$entry =  preg_replace("/\b" . phpbb_preg_quote(trim($match_synonym), "/") . "\b/is", " " . trim($replace_synonym) . " ", $entry);
		}
	}

	return $entry;
}

function clean_words_search($entry)
{

	$char_match =   array("^", "$", "&", "(", ")", "<", ">", "`", "'", "|", ",", "@", "_", "?", "%", "~", ".", "[", "]", "{", "}", ":", "\\", "/", "=", "#", "\"", ";", "!");
	$char_replace = array(" ", " ", " ", " ", " ", " ", " ", " ", "",  " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ");

	$sgml_match = array("&nbsp;", "&szlig;", "&agrave;", "&aacute;", "&acirc;", "&atilde;", "&auml;", "&aring;", "&aelig;", "&ccedil;", "&egrave;", "&eacute;", "&ecirc;", "&euml;", "&igrave;", "&iacute;", "&icirc;", "&iuml;", "&eth;", "&ntilde;", "&ograve;", "&oacute;", "&ocirc;", "&otilde;", "&ouml;", "&oslash;", "&ugrave;", "&uacute;", "&ucirc;", "&uuml;", "&yacute;", "&thorn;", "&yuml;");
	$sgml_replace = array(" ", "s", "a", "a", "a", "a", "a", "a", "a", "c", "e", "e", "e", "e", "i", "i", "i", "i", "o", "n", "o", "o", "o", "o", "o", "o", "u", "u", "u", "u", "y", "t", "y");

	$accent_match = array("ß", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï", "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "þ", "ÿ");
	$accent_replace = array("s", "a", "a", "a", "a", "a", "a", "a", "c", "e", "e", "e", "e", "i", "i", "i", "i", "o", "n", "o", "o", "o", "o", "o", "o", "u", "u", "u", "u", "y", "t", "y");

	$entry = " " . strip_tags(strtolower($entry)) . " ";

	$entry = str_replace("+", " and ", $entry);
	$entry = str_replace("-", " not ", $entry);

	$entry = str_replace($sgml_match, $sgml_match, $entry);
	$entry = str_replace($accent_match, $accent_replace, $entry);
	$entry = str_replace($char_match, $char_replace, $entry); 

	$entry = preg_replace("/\b[0-9]+\b/", " ", $entry);

	return $entry;
}

function split_words(&$entry)
{
	preg_match_all("/\b(\w[\w']*\w+|\w+?)\b/", $entry, $split_entries);

	return $split_entries[1];
}

//
// Check if specified words are too common now
//
function remove_common($percent, $word_id_list = array())
{
	global $db;

	// 0.01-0.06s
	$sql = "SELECT SUM(forum_posts) AS total_posts 
		FROM " . FORUMS_TABLE ;
	$result = $db->sql_query($sql); 
	if( !$result )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain post count", "", __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);

	if( $row['total_posts'] > 100 )
	{
		$common_threshold = floor($row['total_posts'] * $percent);

		$word_id_sql = "";
		if( count($word_id_list) )
		{
			$word_id_sql = "WHERE word_id IN (" . implode(", ", $word_id_list) . ") ";
		}

		// 0.020-0.024s
		$sql = "SELECT word_id 
			FROM " . SEARCH_MATCH_TABLE . " 
			$word_id_sql 
			GROUP BY word_id 
			HAVING COUNT(word_id) > $common_threshold";
		$result = $db->sql_query($sql); 
		if( !$result )
		{
			message_die(GENERAL_ERROR, "Couldn't obtain common word list", "", __LINE__, __FILE__, $sql);
		}

		// No matches
		if( $word_count = $db->sql_numrows($result) )
		{
			$common_word_id_list = array();
			while( $row = $db->sql_fetchrow($result) )
			{
				$common_word_id_list[] = $row['word_id'];
			}

			$db->sql_freeresult($result);
			
			if( count($common_word_ids) != 0 )
			{
				$common_word_id_list = implode(", ", $common_word_id_list);

				$sql = "UPDATE " . SEARCH_WORD_TABLE . "
					SET word_common = " . TRUE . " 
					WHERE word_id IN ($common_word_id_list)";
				$result = $db->sql_query($sql); 
				if( !$result )
				{
					message_die(GENERAL_ERROR, "Couldn't delete word list entry", "", __LINE__, __FILE__, $sql);
				}

				$sql = "DELETE FROM " . SEARCH_MATCH_TABLE . "  
					WHERE word_id IN ($common_word_id_list)";
				$result = $db->sql_query($sql); 
				if( !$result )
				{
					message_die(GENERAL_ERROR, "Couldn't delete word match entry", "", __LINE__, __FILE__, $sql);
				}
			}
		}
	}

	return $word_count;
}

//
// Search complete wordlist for words that are too common
//
function remove_common_global($percent, $delete_common = 0)
{
	global $db;
	
	$sql = "SELECT COUNT(DISTINCT post_id) as total_posts
	   FROM " . SEARCH_MATCH_TABLE;
	$result = $db->sql_query($sql); 
	if( !$result )
	{
	   $error = $db->sql_error();
	   die("Couldn't get maximum post ID :: " . $sql . " :: " . $error['message']);
	}
	$total_posts = $db->sql_fetchrow($result);
	$total_posts = $total_posts['total_posts'];
	
	$common_threshold = floor($total_posts * ( $percent / 100 ));

	$sql = "SELECT word_id 
		FROM " . SEARCH_MATCH_TABLE . "
		GROUP BY word_id
		HAVING count(word_id) > $common_threshold";
	$result = $db->sql_query($sql); 
	if( !$result )
	{
	   $error = $db->sql_error();
	   die("Couldn't obtain common word list :: " . $sql . " :: " . $error['message']);
	}
	$common_words =  $db->sql_numrows($result);

	while($row = $db->sql_fetchrow($result))
	{
		$common_word_ids[] = $row['word_id'];
	}
	$db->sql_freeresult($result);
	
	if(count($common_word_ids) != 0)
	{
		$common_word_ids = implode(',',$common_word_ids);
	}
	else
	{
		// We didn't remove any common words
		return 0;
	}

	$sql = "UPDATE ". SEARCH_WORD_TABLE ."
		SET word_common = 1
		WHERE word_id IN ($common_word_ids)";
	$result = $db->sql_query($sql); 
	if( !$result )
	{
		$error = $db->sql_error();
		die("Couldn't delete word list entry :: " . $sql . " :: " . $error['message']);
	}

	if( $delete_common)
	{
		$sql = "DELETE FROM ".SEARCH_MATCH_TABLE." 
			WHERE word_id IN ($common_word_ids)";
		$result = $db->sql_query($sql); 
		if( !$result )
		{
			$error = $db->sql_error();
			die("Couldn't delete word match entry :: " . $sql . " :: " . $error['message']);
		}
	}
	
	return $common_words;
}

function remove_unmatched_words()
{
	global $db;

	switch(SQL_LAYER)
	{
		case 'postgresql':
			$sql = "DELETE FROM " . SEARCH_WORD_TABLE . " 
				WHERE word_id NOT IN ( 
					SELECT word_id  
					FROM " . SEARCH_MATCH_TABLE . "  
					GROUP BY word_id)"; 
			$result = $db->sql_query($sql);
			if( !$result )
			{
				message_die(GENERAL_ERROR, "Couldn't delete old words from word table", __LINE__, __FILE__, $sql);
			}

			$unmatched_count = $db->sql_affectedrows();

			break;

		case 'oracle':
			$sql = "DELETE FROM " . SEARCH_WORD_TABLE . " 
				WHERE word_id IN (
					SELECT w.word_id 
					FROM " . SEARCH_WORD_TABLE . " w, " . SEARCH_MATCH_TABLE . " m 
					WHERE w.word_id = m.word_id(+) 
						AND m.word_id IS NULL)";
			$result = $db->sql_query($sql);
			if( !$result )
			{
				message_die(GENERAL_ERROR, "Couldn't delete old words from word table", __LINE__, __FILE__, $sql);
			}

			$unmatched_count = $db->sql_affectedrows();

			break;

		case 'mssql':
		case 'msaccess':
			$sql = "DELETE FROM " . SEARCH_WORD_TABLE . " 
				WHERE word_id IN ( 
					SELECT w.word_id  
					FROM " . SEARCH_WORD_TABLE . " w 
					LEFT JOIN " . SEARCH_MATCH_TABLE . " m ON m.word_id = w.word_id 
					WHERE m.word_id IS NULL)"; 
			$result = $db->sql_query($sql);
			if( !$result )
			{
				message_die(GENERAL_ERROR, "Couldn't delete old words from word table", __LINE__, __FILE__, $sql);
			}

			$unmatched_count = $db->sql_affectedrows();

			break;

		case 'mysql':
		case 'mysql4':
			// 0.07s
			$sql = "SELECT w.word_id 
				FROM " . SEARCH_WORD_TABLE . " w 
				LEFT JOIN " . SEARCH_MATCH_TABLE . " m ON m.word_id = w.word_id 
				WHERE m.word_id IS NULL"; 
			if( $result = $db->sql_query($sql) )
			{
				if( $unmatched_count = $db->sql_numrows($result) )
				{
					$rowset = array();
					while( $row = $db->sql_fetchrow($result) )
					{
						$rowset[] = $row['word_id'];
					}

					$word_id_sql = implode(", ", $rowset);

					if( $word_id_sql )
					{
						// 0.07s (about 15-20 words)
						$sql = "DELETE FROM " . SEARCH_WORD_TABLE . "  
							WHERE word_id IN ($word_id_sql)";
						$result = $db->sql_query($sql); 
						if( !$result )
						{
							message_die(GENERAL_ERROR, "Couldn't delete word list entry", "", __LINE__, __FILE__, $sql);
						}
					}
					else
					{
						return 0;
					}
				}
				else
				{
					return 0;
				}
			}

			break;
	}

	return $unmatched_count;
}

function add_search_words($post_id, $post_text, $post_title = "")
{
	global $db, $phpbb_root_path, $board_config, $lang;

	$stopword_array = @file($phpbb_root_path . "language/lang_" . $board_config['default_lang'] . "/search_stopwords.txt"); 
	$synonym_array = @file($phpbb_root_path . "language/lang_" . $board_config['default_lang'] . "/search_synonyms.txt"); 

	$search_text = clean_words($post_text, $stopword_array, $synonym_array);
	$search_matches = split_words($search_text);

	if( count($search_matches) )
	{
		$word = array();
		sort($search_matches);

		$word_text_sql = "";
		for ($i = 0; $i < count($search_matches); $i++)
		{ 
			$search_matches[$i] = trim($search_matches[$i]);

			if( $search_matches[$i] != "" && $search_matches[$i] != $search_matches[$i-1] ) 
			{
				$word[] = $search_matches[$i]; 

				if( $word_text_sql != "" )
				{
					$word_text_sql .= ", ";
				}

				$word_text_sql .= "'" . $search_matches[$i] . "'";
			} 
		}

		$sql = "SELECT word_id, word_text, word_common    
			FROM " . SEARCH_WORD_TABLE . " 
			WHERE word_text IN ($word_text_sql)";
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, "Couldn't select words", "", __LINE__, __FILE__, $sql);
		}

		$check_words = array();
		$word_id_list = array();
		if( $word_check_count = $db->sql_numrows($result) )
		{
			while( $row = $db->sql_fetchrow($result) )
			{
				$check_words[$row['word_text']] = $row['word_common'];
				$word_id_list[] = $row['word_id'];
			}
		}

		$match_word = array();
		for ($i = 0; $i < count($word); $i++)
		{ 
			$new_match = true;
			$word_common = false;

			if( $word_check_count )
			{
				if( isset($check_words[$word[$i]]) )
				{
					$new_match = false;
				}
			}

			if( !$check_words[$word[$i]] )
			{
				$match_word[] = "'" . $word[$i] . "'";
			}

			if( $new_match )
			{
				$sql = "INSERT INTO " . SEARCH_WORD_TABLE . "  (word_text, word_common) 
					VALUES ('". $word[$i] . "', 0)"; 
				$result = $db->sql_query($sql); 
				if( !$result )
				{
					message_die(GENERAL_ERROR, "Couldn't insert new word", "", __LINE__, __FILE__, $sql);
				}
			}
		}

		$word_sql_in = implode(", ", $match_word);

		$sql = "INSERT INTO " . SEARCH_MATCH_TABLE . " (post_id, word_id, title_match) 
			SELECT $post_id, word_id, 0 
				FROM " . SEARCH_WORD_TABLE . " 
				WHERE word_text IN ($word_sql_in)";
		$result = $db->sql_query($sql); 
		if( !$result )
		{
			message_die(GENERAL_ERROR, "Couldn't insert new word matches", "", __LINE__, __FILE__, $sql);
		}
	}

	remove_common(0.15, $word_id_list);

	return;
}

function remove_stop_words($entry, &$stopword_list)
{

	if( !empty($stopword_list) )
	{
		for ($j = 0; $j < count($stopword_list); $j++)
		{ 
			$filter_word = trim(strtolower($stopword_list[$j])); 
			if( $filter_word != "and" && $filter_word != "or" && $filter_word != "not" )
			{
				$entry =  preg_replace("/\b" . phpbb_preg_quote($filter_word, "/") . "\b/is", " ", $entry); 
			}
		} 
	}

	return $entry;
}

function replace_synonyms($entry, &$synonym_list)
{
	if( !empty($synonym_list) )
	{
		for ($j = 0; $j < count($synonym_list); $j++)
		{ 
			list($replace_synonym, $match_synonym) = split(" ", trim(strtolower($synonym_list[$j]))); 

			if( $match_synonym != "and" && $match_synonym != "or" && $match_synonym != "not" && 
				$replace_synonym != "and" && $replace_synonym != "or" && $replace_synonym != "not" )
			{
				$entry =  preg_replace("/\b" . phpbb_preg_quote(trim($match_synonym), "/") . "\b/is", " " . trim($replace_synonym) . " ", $entry); 
			}
		} 
	}

	return $entry;
}

?>
