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

//
// This charset data is borrowed from mnoGoSearch 1.x (http://www.mnogosearch.com/ )
//
$charset_all = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

$charset = array();
$charset['usacii'] = array();
$charset['iso88591'] = array(0xC0, 0xC1, 0xC2, 0xC3, 0xC4, 0xC5, 0xC6, 0xC7, 0xC8, 0xC9, 0xCA, 0xCB, 0xCC, 0xCD, 0xCE, 0xCF, 0xD0, 0xD1, 0xD2, 0xD3, 0xD4, 0xD5, 0xD6, 0xD8, 0xD9, 0xDA, 0xDB, 0xDC, 0xDD, 0xDE, 0xDF, 0xE0, 0xE1, 0xE2, 0xE3, 0xE4, 0xE5, 0xE6, 0xE7, 0xE8, 0xE9, 0xEA, 0xEB, 0xEC, 0xED, 0xEE, 0xEF, 0xF0, 0xF1, 0xF2, 0xF3, 0xF4, 0xF5, 0xF6, 0xF8, 0xF9, 0xFA, 0xFB, 0xFC, 0xFD, 0xFE, 0xDF, 0); /* Western European */
$charset['iso88592'] = array(193, 195, 194, 196, 161, 198, 200, 199, 207, 201, 204, 203, 202, 208, 205, 206, 197, 165, 163, 209, 210, 211, 212, 214, 213, 192, 216, 166, 169, 170, 171, 222, 218, 220, 219, 217, 221, 175, 172, 174, 223, 225, 227, 226, 228, 177, 230, 232, 231, 239, 233, 236, 235, 234, 240, 237, 238, 229, 181, 179, 241, 242, 243, 244, 246, 245, 224, 248, 182, 185, 186, 187, 254, 250, 252, 251, 249, 253, 191, 188, 190, 223, 0); /* Central European */
$charset['iso88594'] = array(); /* Baltic */
$charset['iso88595'] = array(0xB0, 0xB1, 0xB2, 0xB3, 0xB4, 0xB5, 0xA1, 0xB6, 0xB7, 0xB8, 0xB9, 0xBA, 0xBB, 0xBC, 0xBD, 0xBE, 0xBF, 0xC0, 0xC1, 0xC2, 0xC3, 0xC4, 0xC5, 0xC6, 0xC7, 0xC8, 0xC9, 0xCA, 0xCB, 0xCC, 0xCD, 0xCE, 0xCF, 0xD0, 0xD1, 0xD2, 0xD3, 0xD4, 0xD5, 0xF1, 0xD6, 0xD7, 0xD8, 0xD9, 0xDA, 0xDB, 0xDC, 0xDD, 0xDE, 0xDF, 0xE0, 0xE1, 0xE2, 0xE3, 0xE4, 0xE5, 0xE6, 0xE7, 0xE8, 0xE9, 0xEA, 0xEB, 0xEC, 0xED, 0xEE, 0xEF, 0); /* Cyrillic */
$charset['iso88596'] = array(); /* Arabic */
$charset['iso88597'] = array(0xc1, 0xb6, 0xdc, 0xc2, 0xc3, 0xc4, 0xc5, 0xb8, 0xdd, 0xc6, 0xc7, 0xb9, 0xde, 0xc8, 0xc9, 0xba, 0xda, 0xdf, 0xc0, 0xca, 0xcb, 0xcc, 0xcd, 0xce, 0xcf, 0xbc, 0xfc, 0xd0, 0xd1, 0xd3, 0xf2, 0xd4, 0xd5, 0xbe, 0xdb, 0xfd, 0xfb, 0xe0, 0xd6, 0xd7, 0xd8, 0xd9, 0xbf, 0xfe, 0xe1, 0xe1, 0xe1, 0xe2, 0xe3, 0xe4, 0xe5, 0xe5, 0xe5, 0xe6, 0xe7, 0xe7, 0xe7, 0xe8, 0xe9, 0xe9, 0xe9, 0xe9, 0xe9, 0xea, 0xeb, 0xec, 0xed, 0xee, 0xef, 0xef, 0xef, 0xf0, 0xf1, 0xf3, 0xf3, 0xf4, 0xf5, 0xf5, 0xf5, 0xf5, 0xf5, 0xf5, 0xf6, 0xf7, 0xf8, 0xf9, 0xf9, 0xf9, 0); /* Greek */
$charset['iso88598'] = array(0xE0, 0xE1, 0xE2, 0xE3, 0xE4, 0xE5, 0xE6, 0xE7, 0xE8, 0xE9, 0xEA, 0xEB, 0xEC, 0xED, 0xEE, 0xEF, 0xF0, 0xF1, 0xF2, 0xF3, 0xF4, 0xF5, 0xF6, 0xF7, 0xF8, 0xF9, 0xFA, 0x00); /* Hebrew */
$charset['iso88599'] = array(); /* Turkish */
$charset['iso885913'] = array(); /* Baltic 2 */
$charset['cp1256'] = array(0x8d, 0x8e, 0x90, 0xc1, 0xc2, 0xc3, 0xc4, 0xc5, 0xc6, 0xc7, 0xc8, 0xc9, 0xca, 0xcb, 0xcc, 0xcd, 0xce, 0xcf, 0xd0, 0xd1, 0xd2, 0xd3, 0xd4, 0xd5, 0xd6, 0xd8, 0xd9, 0xda, 0xdb, 0xdc, 0xde, 0xdf, 0xe1, 0xe3, 0xe4, 0xe5, 0xe6, 0xec, 0xed, 0); 
$charset[''] = array();

function clean_words($mode, &$entry, &$synonym_list)
{
	// Weird, $init_match doesn't work with static when double quotes (") are used...
	static $drop_char_match =   array('^', '$', '&', '(', ')', '<', '>', '`', "'", '|', ',', '@', '_', '?', '%', '-', '~', '+', '.', '[', ']', '{', '}', ':', '\\', '/', '=', '#', '\'', ';', '!');
	static $drop_char_replace = array(" ", " ", " ", " ", " ", " ", " ", " ", "",  " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " " , " ", " ", " ", " ",  " ", " ");

//	static $accent_match = array("ß", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï", "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "þ", "ÿ");
//	static $accent_replace = array("s", "a", "a", "a", "a", "a", "a", "a", "c", "e", "e", "e", "e", "i", "i", "i", "i", "o", "n", "o", "o", "o", "o", "o", "o", "u", "u", "u", "u", "y", "t", "y");

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
		$value_sql = "";

		if( !empty($search_matches) )
		{
			sort($search_matches);

			$word = array();
			$prev_word = "";
			$word_text_sql = "";

			for ($i = 0; $i < count($search_matches); $i++)
			{ 
				$search_matches[$i] = trim($search_matches[$i]);

				if( $search_matches[$i] != "" && $search_matches[$i] != $prev_word ) 
				{
					$word[] = $search_matches[$i]; 
					$word_text_sql .= ( ( $word_text_sql != "" ) ? ", " : "" ) . "'" . $search_matches[$i] . "'";
				} 

				$prev_word = $search_matches[$i];
			}

			$sql = "SELECT word_id, word_text, word_common    
				FROM " . SEARCH_WORD_TABLE . " 
				WHERE word_text IN ($word_text_sql)";
			if( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, "Couldn't select words", "", __LINE__, __FILE__, $sql);
			}

			$check_words = array();
			$word_id_list = array();
			while( $row = $db->sql_fetchrow($result) )
			{
				$check_words[$row['word_text']] = $row['word_common'];
				$word_id_list[] = $row['word_id'];
			}

			$match_word = array();
			for ($i = 0; $i < count($word); $i++)
			{ 
				$new_match = true;
				$word_common = false;

				if( isset($check_words[$word[$i]]) )
				{
					$new_match = false;
				}
				else if( empty($check_words[$word[$i]]) )
				{
					$match_word[] = "'" . $word[$i] . "'";
				}

				if( $new_match )
				{
					switch( SQL_LAYER )
					{
						case 'mysql':
						case 'mysql4':
							$value_sql .= ( ( $value_sql != "" ) ? ", " : "" ) . "('" . $word[$i] . "', 0)";
							break;
						case 'mssql':
						case 'mssql-odbc':
							$value_sql .= ( ( $value_sql != "" ) ? " UNION ALL " : "" ) . "SELECT '" . $word[$i] . "', 0";
							break;
						default:
							$sql = "INSERT INTO " . SEARCH_WORD_TABLE . " (word_text, word_common) 
								VALUES ('" . $word[$i] . "', 0)"; 
							if( !($result = $db->sql_query($sql)) )
							{
								message_die(GENERAL_ERROR, "Couldn't insert new word", "", __LINE__, __FILE__, $sql);
							}
							break;
					}
				}
			}

			if ( $value_sql != "" )
			{
				switch ( SQL_LAYER )
				{
					case 'mysql':
					case 'mysql4':
						$sql = "INSERT INTO " . SEARCH_WORD_TABLE . " (word_text, word_common) 
							VALUES $value_sql"; 
						break;
					case 'mssql':
					case 'mssql-odbc':
						$sql = "INSERT INTO " . SEARCH_WORD_TABLE . " (word_text, word_common) 
							$value_sql"; 
						break;
				}

				if( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, "Couldn't insert new word", "", __LINE__, __FILE__, $sql);
				}
			}

			$sql = "INSERT INTO " . SEARCH_MATCH_TABLE . " (post_id, word_id, title_match) 
				SELECT $post_id, word_id, $title_match  
					FROM " . SEARCH_WORD_TABLE . " 
					WHERE word_text IN ($word_text_sql)"; 
			if( !($result = $db->sql_query($sql)) )
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
function remove_common($mode, $fraction, $word_id_list = array())
{
	global $db;

	$sql = ( $mode == "global" ) ? "SELECT COUNT(DISTINCT post_id) AS total_posts FROM " . SEARCH_MATCH_TABLE : "SELECT SUM(forum_posts) AS total_posts FROM " . FORUMS_TABLE;
	if( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain post count", "", __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);

	if( $row['total_posts'] >= 100 )
	{
		$common_threshold = floor($row['total_posts'] * $fraction);

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
		if( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, "Couldn't obtain common word list", "", __LINE__, __FILE__, $sql);
		}

		$common_word_id = "";
		while( $row = $db->sql_fetchrow($result) )
		{
			$common_word_id = ( $common_word_id != "" ) ? ", " . $row['word_id'] : $row['word_id'];
		}

		$db->sql_freeresult($result);

		if( $common_word_id != "" )
		{
			$sql = "UPDATE " . SEARCH_WORD_TABLE . "
				SET word_common = " . TRUE . " 
				WHERE word_id IN ($common_word_id)";
			if( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, "Couldn't delete word list entry", "", __LINE__, __FILE__, $sql);
			}

			$sql = "DELETE FROM " . SEARCH_MATCH_TABLE . "  
				WHERE word_id IN ($common_word_id)";
			if( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, "Couldn't delete word match entry", "", __LINE__, __FILE__, $sql);
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
		case 'mysql':
		case 'mysql4':
			$sql = "SELECT w.word_id 
				FROM " . SEARCH_WORD_TABLE . " w 
				LEFT JOIN " . SEARCH_MATCH_TABLE . " m ON m.word_id = w.word_id 
				WHERE m.word_id IS NULL"; 
			if( $result = $db->sql_query($sql) )
			{
				$word_id_sql = "";
				while( $row = $db->sql_fetchrow($result) )
				{
					$word_id_sql .= ( $word_id_sql != "" ) ? ", " . $row['word_id'] : $row['word_id']; 
				}

				if( $word_id_sql != "" )
				{
					$sql = "DELETE FROM " . SEARCH_WORD_TABLE . "  
						WHERE word_id IN ($word_id_sql)";
					if( !($result = $db->sql_query($sql, END_TRANSACTION)) )
					{
						message_die(GENERAL_ERROR, "Couldn't delete word list entry", "", __LINE__, __FILE__, $sql);
					}

					return $db->sql_affectedrows();
				}
			}
			break;

		default:
			$sql = "DELETE FROM " . SEARCH_WORD_TABLE . " 
				WHERE word_id NOT IN ( 
					SELECT word_id  
					FROM " . SEARCH_MATCH_TABLE . "  
					GROUP BY word_id)"; 
			if( !($result = $db->sql_query($sql, END_TRANSACTION)) )
			{
				message_die(GENERAL_ERROR, "Couldn't delete old words from word table", __LINE__, __FILE__, $sql);
			}

			return $db->sql_affectedrows();

			break;
	}

	return 0;
}

?>