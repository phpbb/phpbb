<?php 

// Clean up an entry (posting), remove HTML, BBcode, stopwords, etc
function clean_words($entry, &$search, &$replace)
{
	// Weird, $init_match doesn't work with static when double quotes (") are used...
	static $init_match =   array('^', '$', '&', '(', ')', '<', '>', '`', "'", '|', ',', '@', '_', '?', '%');
	static $init_replace = array(" ", " ", " ", " ", " ", " ", " ", " ", "",  " ", " ", " ", " ", " ", " ");

	static $later_match =   array("-", "~", "+", ".", "[", "]", "{", "}", ":", "\\", "/", "=", "#", "\"", ";", "*", "!");
	static $later_replace = array(" ", " ", " ", " ", " ", " ", " ", " ", " ", " " , " ", " ", " ", " ",  " ", " ", " ");

	$entry = " " . strip_tags(strtolower($entry)) . " ";

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

	// Remove stopwords
	$entry =  preg_replace($search, $replace, $entry); 

	return $entry;
}

function split_words(&$entry)
{
	preg_match_all("/\b(\w[\w']*\w+|\w+?)\b/", $entry, $split_entries);

	return $split_entries[1];
}

function remove_common($percent, $delete_common = 0)
{
	global $db;
	
	$sql = "
		SELECT
			COUNT(DISTINCT post_id) as total_posts
	   FROM " . SEARCH_MATCH_TABLE;
	$result = $db->sql_query($sql); 
	if( !$result )
	{
	   $error = $db->sql_error();
	   die("Couldn't get maximum post ID :: " . $sql . " :: " . $error['message']);
	}
	$total_posts = $db->sql_fetchrow($result);
	$total_posts = $total_posts['total_posts'];
	
	$common_threshold = floor($total_posts * ($percent/100));

	$sql = "
		SELECT 
			word_id, 
			count(word_id) AS word_occur 
		FROM 
			".SEARCH_MATCH_TABLE."
		GROUP BY 
			word_id
		HAVING
			word_occur > $common_threshold
		";
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
		$sql = "DELETE FROM phpbb_search_wordmatch 
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

set_time_limit(0);
$common_percent = 40; // Percentage of posts in which a word has to appear to be marked as common

$phpbb_root_path = "../";

include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'config.'.$phpEx);
include($phpbb_root_path . 'includes/constants.'.$phpEx);
include($phpbb_root_path . 'includes/db.'.$phpEx);

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
	$search[] = "/\b" . preg_quote($filter_word, "/") . "\b/is";
	$replace[] = '';
} 

for ($j = 0; $j < count($synonym_list); $j++)
{ 
	list($replace_synonym, $match_synonym) = split(" ", trim(strtolower($synonym_list[$j]))); 
	$search[] = "/\b" . preg_quote(trim($match_synonym), "/") . "\b/is";
	$replace[] = " " . trim($replace_synonym) . " ";
} 

//
// Fetch a batch of posts_text entries
//
$sql = "
	SELECT 
		count(*) as total,
		max(post_id) as max_post_id 
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
		FROM " .
			POSTS_TEXT_TABLE ."
		WHERE 
			post_id BETWEEN $batchstart AND $batchend";
	if(!$posts_result = $db->sql_query($sql))
	{
		$error = $db->sql_error();
		die("Couldn't get post_text :: " . $sql . " :: " . $error['message']);
	}

	$rowset = $db->sql_fetchrowset($posts_result);

	if( $post_rows = $db->sql_numrows($posts_result) )
	{

	//	$sql = "LOCK TABLES phpbb_posts_text WRITE";
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
				FROM phpbb_search_wordlist
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
				$sql_insert .= "$comma($post_id, ".$row['word_id'].", ".$word_count[$row['word_text']]." ,0)";
			}
			
			$sql = "INSERT INTO ".SEARCH_MATCH_TABLE."
				(post_id, word_id, word_count, title_match)
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
			//$phrase_string = preg_replace("/\b" . preg_quote($word[$j], "/") . "\b/is", $word_id, $phrase_string);
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
		print "Removed ". remove_common($common_percent, 1) ." words that where too common.<br>";
	}
	
}

echo "<br>Done";

?>

</body>
</html>
