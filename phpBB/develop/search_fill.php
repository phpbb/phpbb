<?php 

function clean_words($entry, &$stopword_list, &$synonym_list)
{
	$init_match =   array("^", "$", "&", "(", ")", "<", ">", "`", "'", "|", ",", "@", "_", "?", "%");
	$init_replace = array(" ", " ", " ", " ", " ", " ", " ", " ", "",  " ", " ", " ", " ", " ", " ");

	$later_match =   array("-", "~", "+", ".", "[", "]", "{", "}", ":", "\\", "/", "=", "#", "\"", ";", "*", "!");
	$later_replace = array(" ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ");

	$entry = " " . stripslashes(strip_tags(strtolower($entry))) . " ";

	$entry = preg_replace("/[\n\r]/is", " ", $entry); 
	$entry = preg_replace("/\b[a-z0-9]+:\/\/[a-z0-9\.\-]+(\/[a-z0-9\?\.%_\-\+=&\/]+)?/si", " ", $entry); 

	$entry = str_replace($init_match, $init_replace, $entry);

	$entry = preg_replace("/\[code:[0-9]+:[0-9a-z]{10,}\].*?\[\/code:[0-9]+:[0-9a-z]{10,}\]/is", " ", $entry); 
	$entry = preg_replace("/\[img\].*?\[\/img\]/is", " ", $entry); 
	$entry = preg_replace("/\[\/?[a-z\*=\+\-]+[0-9a-z]?(\:[a-z0-9]+)?:[a-z0-9]{10,}(\:[a-z0-9]+)?=?.*?\]/si", " ", $entry);
	$entry = preg_replace("/\[\/?[a-z\*]+[=\+\-]?[0-9a-z]+?:[a-z0-9]{10,}[=.*?]?\]/si", " ", $entry);
	$entry = preg_replace("/\[\/?url(=.*?)?\]/si", " ", $entry);
	$entry = preg_replace("/\b[0-9]+\b/si", " ", $entry); 
	$entry = preg_replace("/\b&[a-z]+;\b/is", " ", $entry); 
	$entry = preg_replace("/\b[a-z0-9]{1,2}?\b/si", " ", $entry); 
	$entry = preg_replace("/\b[a-z0-9]{50,}?\b/si", " ", $entry); 

	$entry = str_replace($later_match, $later_replace, $entry);

	if( !empty($stopword_list) )
	{
		for ($j = 0; $j < count($stopword_list); $j++)
		{ 
			$filter_word = trim(strtolower($stopword_list[$j])); 
			$entry =  preg_replace("/\b" . preg_quote($filter_word, "/") . "\b/is", " ", $entry); 
		} 
	}

	if( !empty($synonym_list) )
	{
		for ($j = 0; $j < count($synonym_list); $j++)
		{ 
			list($replace_synonym, $match_synonym) = split(" ", trim(strtolower($synonym_list[$j]))); 
			$entry =  preg_replace("/\b" . preg_quote(trim($match_synonym), "/") . "\b/is", " " . trim($replace_synonym) . " ", $entry); 
		} 
	}

	return $entry;
}

function split_words(&$entry)
{
	preg_match_all("/\b(\w[\w']*\w+|\w+?)\b/", $entry, $split_entries);

	return $split_entries[1];
}

function remove_common($percent)
{
	global $db;

	$sql = "SELECT sl.word_id, SUM(sm.word_count) AS post_occur_count 
		FROM phpbb_search_wordlist sl, phpbb_search_wordmatch sm 
		WHERE sl.word_id = sm.word_id 
		GROUP BY sl.word_id 
		ORDER BY post_occur_count DESC";
	$result = $db->sql_query($sql); 
	if( !$result )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain search word sums", "", __LINE__, __FILE__, $sql);
	}

	$post_count = $db->sql_numrows($result);
	$rowset = $db->sql_fetchrowset($result);

	$sql = "SELECT COUNT(post_id) AS total_posts 
		FROM phpbb_posts";
	$result = $db->sql_query($sql); 
	if( !$result )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain post count", "", __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);

	$words_removed = 0;

	for($i = 0; $i < $post_count; $i++)
	{
		if( ($rowset[$i]['post_occur_count'] / $row['total_posts'] ) >= $percent )
		{
			$sql = "DELETE FROM phpbb_search_wordlist 
				WHERE word_id = " . $rowset[$i]['word_id'];
			$result = $db->sql_query($sql); 
			if( !$result )
			{
				message_die(GENERAL_ERROR, "Couldn't delete word list entry", "", __LINE__, __FILE__, $sql);
			}

			$sql = "DELETE FROM phpbb_search_wordmatch 
				WHERE word_id = " . $rowset[$i]['word_id'];
			$result = $db->sql_query($sql); 
			if( !$result )
			{
				message_die(GENERAL_ERROR, "Couldn't delete word match entry", "", __LINE__, __FILE__, $sql);
			}

			$words_removed++;
		}
	}

	return $words_removed;
}

set_time_limit(2400);

$phpbb_root_path = "../";

include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'config.'.$phpEx);
include($phpbb_root_path . 'includes/constants.'.$phpEx);
include($phpbb_root_path . 'includes/db.'.$phpEx);

//
// Try and load stopword and synonym files
//
//$stopword_array = file($phpbb_root_path . "language/lang_" . $board_config['default_lang'] . "/search_stopwords.txt"); 
//$synonym_array = file($phpbb_root_path . "language/lang_" . $board_config['default_lang'] . "/search_synonyms.txt"); 
$stopword_array = file($phpbb_root_path . "language/lang_english/search_stopwords.txt"); 
$synonym_array = file($phpbb_root_path . "language/lang_english/search_synonyms.txt"); 

//
// Build search ...
//
$start = ( isset($HTTP_GET_VARS['start']) ) ? $HTTP_GET_VARS['start'] : 0;

$sql = "SELECT * 
	FROM " . POSTS_TEXT_TABLE . " 
	ORDER BY post_id ASC 
	LIMIT $start, 200"; 
$result = $db->sql_query($sql); 
if( !$result )
{
	$error = $db->sql_error();
	die("Couldn't select words :: " . $sql . " :: " . $error['message']);
}

$rowset = $db->sql_fetchrowset($result);

if( $post_rows = $db->sql_numrows($result) )
{

//	$sql = "LOCK TABLES phpbb_posts_text WRITE";
//	$result = $db->sql_query($sql); 

	for($i = 0; $i < $post_rows; $i++ )
	{ 
		$matches = array();

		$post_id = $rowset[$i]['post_id']; 
		$data = $rowset[$i]['post_text'];

		$text = clean_words($data, $stopword_array, $synonym_array);
		$matches = split_words($text);

		if( count($matches) )
		{
			$word = array();
			$word_count = array();
			$phrase_string = $text;

			$sql_in = "";
			for ($j = 0; $j < count($matches); $j++)
			{ 
				$this_word = strtolower(trim($matches[$j]));

				if( empty($word_count[$this_word]) )
				{
					$word_count[$this_word] = 1;
				}

				$new_word = true;
				for($k = 0; $k < count($word); $k++)
				{
					if( $this_word ==  $word[$k] )
					{
						$new_word = false;
						$word_count[$this_word]++;
					}
				}

				if( $new_word )
				{
					$word[] = $this_word;
				}
			}

			for($j = 0; $j < count($word); $j++)
			{
				if( $word[$j] )
				{
					if( $sql_in != "" )
					{
						$sql_in .= ", ";
					}
					$sql_in .= "'" . $word[$j] . "'";
				}
			}

			$sql = "SELECT word_id, word_text  
				FROM phpbb_search_wordlist
				WHERE word_text IN ($sql_in)";
			$result = $db->sql_query($sql);
			if( !$result )
			{
				$error = $db->sql_error();
				die("Couldn't select words :: " . $sql . " :: " . $error['message']);
			}

			if( $word_check_count = $db->sql_numrows($result) )
			{
				$check_words = $db->sql_fetchrowset($result);
			}

			for ($j = 0; $j < count($word); $j++)
			{ 
				if( $word[$j] )
				{
					$new_match = true;

					if( $word_check_count )
					{
						for($k = 0; $k < $word_check_count; $k++)
						{
							if( $word[$j] == $check_words[$k]['word_text'] )
							{
								$new_match = false;
								$word_id = $check_words[$k]['word_id'];
							}
						}
					}

					if( $new_match )
					{
						$sql = "INSERT INTO phpbb_search_wordlist (word_text) 
							VALUES ('". addslashes($word[$j]) . "')"; 
						$result = $db->sql_query($sql); 
						if( !$result )
						{
							$error = $db->sql_error();
							die("Couldn't insert new word :: " . $sql . " :: " . $error['message']);
						}

						$word_id = $db->sql_nextid();
					}
					
					$sql = "INSERT INTO phpbb_search_wordmatch (post_id, word_id, word_count, title_match) 
						VALUES ($post_id, $word_id, " . $word_count[$word[$j]] . ", 0)"; 
					$result = $db->sql_query($sql); 
					if( !$result )
					{
						$error = $db->sql_error();
						die("Couldn't insert new word match :: " . $sql . " :: " . $error['message']);
					}

					$phrase_string = preg_replace("/\b" . preg_quote($word[$j], "/") . "\b/is", $word_id, $phrase_string);
				}
			}
/*
			$phrase_string = trim(preg_replace("/ {2,}/s", " ", str_replace(array("*", "'"), " ", $phrase_string)));

			$sql = "INSERT INTO phpbb_search_phrasematch (post_id, phrase_list) 
				VALUES ($post_id, '$phrase_string')"; 
			$result = $db->sql_query($sql); 
			if( !$result )
			{
				$error = $db->sql_error();
				die("Couldn't insert new phrase match :: " . $sql . " :: " . $error['message']);
			}
*/
		}
	}

//	$sql = "UNLOCK TABLES";
//	$result = $db->sql_query($sql); 

}

if( $post_rows == 200 )
{
	header("Location: search_fill.php?start=" . ($start+200) . "&total=" . ($start + $post_rows));
}

?>
<html>
<body>

<?php

$total_rows = ( $HTTP_GET_VARS['total'] ) ? $HTTP_GET_VARS['total'] : $post_rows;

echo "<BR><BR>Total posts = " . $total_rows . "<BR><BR>";

echo remove_common(0.4);

exit;

?>

</body>
</html>