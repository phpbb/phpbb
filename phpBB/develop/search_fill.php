<?php 

set_time_limit(0);
$common_percent = 40; // Percentage of posts in which a word has to appear to be marked as common

$phpbb_root_path = "../";

include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'config.'.$phpEx);
include($phpbb_root_path . 'includes/constants.'.$phpEx);
include($phpbb_root_path . 'includes/db.'.$phpEx);
include($phpbb_root_path . 'includes/functions.'.$phpEx);
include($phpbb_root_path . 'includes/search.'.$phpEx);

print "<html>\n<body>\n";

//
// Try and load stopword and synonym files
//
//$stopword_array = file($phpbb_root_path . "language/lang_" . $board_config['default_lang'] . "/search_stopwords.txt"); 
//$synonym_array = file($phpbb_root_path . "language/lang_" . $board_config['default_lang'] . "/search_synonyms.txt"); 

// This needs fixing! Shouldn't be hardcoded to English files!
$stopword_array = file($phpbb_root_path . "language/lang_english/search_stopwords.txt"); 
$synonym_array = file($phpbb_root_path . "language/lang_english/search_synonyms.txt"); 

for ($j = 0; $j < count($stopword_array); $j++)
{ 
	$filter_word = trim(strtolower($stopword_array[$j])); 
	$search[] = "/\b" . phpbb_preg_quote($filter_word, "/") . "\b/is";
	$replace[] = '';
} 

for ($j = 0; $j < count($synonym_list); $j++)
{ 
	list($replace_synonym, $match_synonym) = split(" ", trim(strtolower($synonym_list[$j]))); 
	$search[] = "/\b" . phpbb_preg_quote(trim($match_synonym), "/") . "\b/is";
	$replace[] = " " . trim($replace_synonym) . " ";
} 

//
// Fetch a batch of posts_text entries
//
$sql = "
	SELECT count(*) as total, max(post_id) as max_post_id 
	FROM ". POSTS_TEXT_TABLE;
if(!$result = $db->sql_query($sql)) 
{
	$error = $db->sql_error();
	die("Couldn't get maximum post ID :: " . $sql . " :: " . $error['message']);
}
$max_post_id = $db->sql_fetchrow($result);
$totalposts = $max_post_id['total'];
$max_post_id = $max_post_id['max_post_id'];

$postcounter = (!isset($HTTP_GET_VARS['batchstart'])) ? 0 : $HTTP_GET_VARS['batchstart'];

$batchsize = 200; // Process this many posts per loop
$batchcount = 0;
for(;$postcounter <= $max_post_id; $postcounter += $batchsize)
{
	$batchstart = $postcounter + 1;
	$batchend = $postcounter + $batchsize;
	$batchcount++;
	
	$sql = "SELECT *
		FROM " . POSTS_TEXT_TABLE ."
		WHERE post_id 
			BETWEEN $batchstart 
				AND $batchend";
	if(!$posts_result = $db->sql_query($sql))
	{
		$error = $db->sql_error();
		die("Couldn't get post_text :: " . $sql . " :: " . $error['message']);
	}

	$rowset = $db->sql_fetchrowset($posts_result);

	if( $post_rows = $db->sql_numrows($posts_result) )
	{

	//	$sql = "LOCK TABLES ".POST_TEXT_TABLE." WRITE";
	//	$result = $db->sql_query($sql); 
		print "\n<p>\n<a href='$PHP_SELF?batchstart=$batchstart'>Restart from posting $batchstart</a><br>\n";

		// For every post in the batch:
		for($post_nr = 0; $post_nr < $post_rows; $post_nr++ )
		{ 

			print ".";
			flush();
			$matches = array();

			$post_id = $rowset[$post_nr]['post_id']; 
			$data = $rowset[$post_nr]['post_text'];  // Raw data

			$text = clean_words($data, $search, $replace); // Cleaned up post
			$matches = split_words($text);
			$num_matches = count($matches);
			if($num_matches < 1)
			{
				// Skip this post if no words where found
				continue;
			}

			$word = array();
			$word_count = array();
			$sql_in = "";
			$phrase_string = $text;

			// For all words in the posting
			$sql_insert = '';
			$sql_select = '';
			for($j = 0; $j < $num_matches; $j++)
			{
				$this_word = strtolower(trim($matches[$j]));
				if($this_word != '')
				{
					$word_count[$this_word]++;
					$comma = ($sql_insert != '')? ', ': '';
				
					$sql_insert .= "$comma('" .$this_word. "')";
					$sql_select .= "$comma'" .$this_word. "'";
				}
			}
			if($sql_insert == '')
			{
				die("no words found");
			}
				
			$sql = 'INSERT IGNORE INTO '.SEARCH_WORD_TABLE."
				(word_text)
				VALUES $sql_insert";
			if( !$result = $db->sql_query($sql) )
			{
				$error = $db->sql_error();
				die("Couldn't INSERT words :: " . $sql . " :: " . $error['message']);
			}

			// Get the word_id's out of the DB (to see if they are already there)
			$sql = "SELECT word_id, word_text
				FROM ".SEARCH_WORD_TABLE." 
				WHERE word_text IN ($sql_select)
				GROUP BY word_text";
			$result = $db->sql_query($sql);
			if( !$result )
			{
				$error = $db->sql_error();
				die("Couldn't select words :: " . $sql . " :: " . $error['message']);
			}
			if( $word_check_count = $db->sql_numrows($result) )
			{
				$selected_words = $db->sql_fetchrowset($result);
			}
			else
			{
				print "Couldn't do sql_numrows<br>\n";
			}
			$db->sql_freeresult($result);
			
			$sql_insert = '';
			while(list($junk, $row) = each($selected_words))
			{
				$comma = ($sql_insert != '')? ', ': '';
				$sql_insert .= "$comma($post_id, ".$row['word_id'].", 0)";
			}
			
			$sql = "INSERT INTO ".SEARCH_MATCH_TABLE."
				(post_id, word_id, title_match)
				VALUES
				$sql_insert
				";
			$result = $db->sql_query($sql); 
			if( !$result )
			{
				$error = $db->sql_error();
				die("Couldn't insert new word match :: " . $sql . " :: " . $error['message']);
			}

/*
			//$phrase_string = preg_replace("/\b" . phpbb_preg_quote($word[$j], "/") . "\b/is", $word_id, $phrase_string);
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
		} // All posts

	//	$sql = "UNLOCK TABLES";
	//	$result = $db->sql_query($sql); 

	}
	else
	{
		print "Couldn't get rowcount for number of posts<br>$sql<br>\n";
	} // All posts;

	$db->sql_freeresult($posts_result);
	
	// Remove common words after the first 2 batches and after every 4th batch after that.
	if( $batchcount % 4 == 3 )
	{
		print "<br>Removing common words (words that appear in more than $common_percent of the posts)<br>\n";
		flush();
		print "Removed ". remove_common_global($common_percent, 1) ." words that where too common.<br>";
	}
	
}

echo "<br>Done";

?>

</body>
</html>
