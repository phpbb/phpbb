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

function clean_words($mode, &$entry, &$synonym_list)
{
	// Weird, $init_match doesn't work with static when double quotes (") are used...
	static $drop_char_match =   array('^', '$', '&', '(', ')', '<', '>', '`', "'", '|', ',', '@', '_', '?', '%', '-', '~', '+', '.', '[', ']', '{', '}', ':', '\\', '/', '=', '#', '\'', ';', '!');
	static $drop_char_replace = array(" ", " ", " ", " ", " ", " ", " ", " ", "",  " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " " , " ", " ", " ", " ",  " ", " ");

	static $accent_match = array("ß", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï", "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "þ", "ÿ");
	static $accent_replace = array("s", "a", "a", "a", "a", "a", "a", "a", "c", "e", "e", "e", "e", "i", "i", "i", "i", "o", "n", "o", "o", "o", "o", "o", "o", "u", "u", "u", "u", "y", "t", "y");

	$entry = " " . strip_tags(strtolower($entry)) . " ";

	for($i = 0; $i < count($accent_match); $i++)
	{
		$entry = str_replace($accent_match[$i], $accent_replace[$i], $entry);
	}

	if( $mode == "post" )
	{
		// HTML entities like &nbsp;
		$entry = preg_replace("/\b&[a-z]+;\b/is", " ", $entry); 
		// Replace line endings by a space
		$entry = preg_replace("/[\n\r]/is", " ", $entry); 
		// Remove URL's
		$entry = preg_replace("/\b[a-z0-9]+:\/\/[a-z0-9\.\-]+(\/[a-z0-9\?\.%_\-\+=&\/]+)?/si", " ", $entry); 
		// Quickly remove BBcode.
		$entry = preg_replace("/\[img:[a-z0-9]{10,}\].*?\[\/img:[a-z0-9]{10,}\]/is", " ", $entry); 
		$entry = preg_replace("/\[\/?url(=.*?)?\]/si", " ", $entry);
		$entry = preg_replace("/\[\/?[a-z\*=\+\-]+(\:?[0-9a-z]+)?:[a-z0-9]{10,}(\:[a-z0-9]+)?=?.*?\]/si", " ", $entry);
	}
	else if( $mode == "search" ) 
	{
		$entry = str_replace("+", " and ", $entry);
		$entry = str_replace("-", " not ", $entry);
	}

	// Replace numbers on their own
	$entry = preg_replace("/\b[0-9]+\b/si", " ", $entry); 

	//
	// Filter out strange characters like ^, $, &, change "it's" to "its"
	//
	for($i = 0; $i < count($drop_char_match); $i++)
	{
		$entry =  str_replace($drop_char_match[$i], $drop_char_replace[$i], $entry);
	}

	if( $mode == "post" )
	{
		$entry = str_replace("*", " ", $entry);

		// 'words' that consist of <=3 or >=50 characters are removed.
		$entry = preg_replace("/\b([a-z0-9]{1,3}|[a-z0-9]{50,})\b/si", " ", $entry); 
	}

	if( !empty($synonym_list) )
	{
		for ($j = 0; $j < count($synonym_list); $j++)
		{
			list($replace_synonym, $match_synonym) = split(" ", trim(strtolower($synonym_list[$j])));

			if( ( $match_synonym != "and" && $match_synonym != "or" && $match_synonym != "not" && 
				$replace_synonym != "and" && $replace_synonym != "or" && $replace_synonym != "not" ) || $mode == "post" )
			{
				$entry =  preg_replace("/\b" . phpbb_preg_quote(trim($match_synonym), "/") . "\b/is", " " . trim($replace_synonym) . " ", $entry);
			}
		}
	}

	return $entry;
}

function split_words(&$entry, $mode = "post")
{
	if( $mode == "post" )
	{
		preg_match_all("/\b(\w[\w']*\w+|\w+?)\b/", $entry, $split_entries);
	}
	else
	{
		preg_match_all("/(\*?[a-z0-9]+\*?)|\b([a-z0-9]+)\b/is", $entry, $split_entries);
	}

	return $split_entries[1];
}

function add_search_words($post_id, $post_text, $post_title = "")
{
	global $db, $phpbb_root_path, $board_config, $lang;

	$synonym_array = @file($phpbb_root_path . "language/lang_" . $board_config['default_lang'] . "/search_synonyms.txt"); 

	$search_raw_words = array();
	$search_raw_words['text'] = split_words(clean_words("post", $post_text, $synonym_array));
	$search_raw_words['title'] = split_words(clean_words("post", $post_title, $synonym_array));

	while( list($word_in, $search_matches) = @each($search_raw_words) )
	{
		$title_match = ( $word_in == 'title' ) ? 1 : 0;

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
						VALUES ('" . $word[$i] . "', 0)"; 
					$result = $db->sql_query($sql); 
					if( !$result )
					{
						message_die(GENERAL_ERROR, "Couldn't insert new word", "", __LINE__, __FILE__, $sql);
					}
				}
			}

			$sql = "INSERT INTO " . SEARCH_MATCH_TABLE . " (post_id, word_id, title_match) 
				SELECT $post_id, word_id, $title_match  
					FROM " . SEARCH_WORD_TABLE . " 
					WHERE word_text IN ($word_text_sql)"; 
			$result = $db->sql_query($sql); 
			if( !$result )
			{
				message_die(GENERAL_ERROR, "Couldn't insert new word matches", "", __LINE__, __FILE__, $sql);
			}
		}
	}

	remove_common("single", 0.4, $word_id_list);

	return;
}

//
// Check if specified words are too common now
//
function remove_common($mode, $percent, $word_id_list = array())
{
	global $db;

	$sql = ( $mode == "global" ) ? "SELECT COUNT(DISTINCT post_id) AS total_posts FROM " . SEARCH_MATCH_TABLE : "SELECT SUM(forum_posts) AS total_posts FROM " . FORUMS_TABLE;

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
		if( $mode == "single" && count($word_id_list) )
		{
			$word_id_sql = "WHERE word_id IN (" . implode(", ", $word_id_list) . ") ";
		}

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

?>