<?php 

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//

//
// Do not change anything below this line.
//
set_time_limit(0);

$phpbb_root_path = "../";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/search.'.$phpEx);

$common_percent = 0.4; // Percentage of posts in which a word has to appear to be marked as common

print "<html>\n<body>\n";

//
// Try and load stopword and synonym files
//
// This needs fixing! Shouldn't be hardcoded to English files!
$stopword_array = file($phpbb_root_path . "language/lang_english/search_stopwords.txt"); 
$synonym_array = file($phpbb_root_path . "language/lang_english/search_synonyms.txt"); 

//
// Fetch a batch of posts_text entries
//
$sql = "SELECT COUNT(*) as total, MAX(post_id) as max_post_id 
	FROM ". POSTS_TABLE;
if ( !($result = $db->sql_query($sql)) ) 
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
		FROM " . POSTS_TABLE . " 
		WHERE post_id 
			BETWEEN $batchstart 
				AND $batchend";
	if( !($result = $db->sql_query($sql)) )
	{
		$error = $db->sql_error();
		die("Couldn't get post_text :: " . $sql . " :: " . $error['message']);
	}

	$rowset = $db->sql_fetchrowset($result);
	$db->sql_freeresult($result);

	$post_rows = count($rowset);
	
	if( $post_rows )
	{

	//	$sql = "LOCK TABLES ".POST_TEXT_TABLE." WRITE";
	//	$result = $db->sql_query($sql); 
		print "\n<p>\n<a href='$PHP_SELF?batchstart=$batchstart'>Restart from posting $batchstart</a><br>\n";

		// For every post in the batch:
		for($post_nr = 0; $post_nr < $post_rows; $post_nr++ )
		{ 
			print ".";
			flush();

			$post_id = $rowset[$post_nr]['post_id']; 

			$matches = array();
			$matches['text'] = split_words(clean_words("post", $rowset[$post_nr]['post_text'], $stopword_array, $synonym_array));
			$matches['title'] = split_words(clean_words("post", $rowset[$post_nr]['post_subject'], $stopword_array, $synonym_array));

			while( list($match_type, $match_ary) = @each($matches) )
			{
				$title_match = ( $match_type == 'title' ) ? 1 : 0;

				$num_matches = count($match_ary);

				if ( $num_matches < 1 )
				{
					// Skip this post if no words where found
					continue;
				}

				// For all words in the posting
				$sql_in = "";

				$sql_insert = '';
				$sql_select = '';

				$word = array();
				$word_count = array();

				for($j = 0; $j < $num_matches; $j++)
				{
					$this_word = strtolower(trim($match_ary[$j]));
					if ( $this_word != '' )
					{
						$word_count[$this_word] = ( isset($word_count[$this_word]) ) ? $word_count[$this_word] + 1 : 0;
						$comma = ($sql_insert != '')? ', ': '';
					
						$sql_insert .= "$comma('" . $this_word . "')";
						$sql_select .= "$comma'" . $this_word . "'";
					}
				}

				if ( $sql_insert == '' )
				{
					die("no words found");
				}
					
				$sql = 'INSERT IGNORE INTO ' . SEARCH_WORD_TABLE . "
					(word_text)
					VALUES $sql_insert";
				if ( !$result = $db->sql_query($sql) )
				{
					$error = $db->sql_error();
					die("Couldn't INSERT words :: " . $sql . " :: " . $error['message']);
				}

				// Get the word_id's out of the DB (to see if they are already there)
				$sql = "SELECT word_id, word_text
					FROM " . SEARCH_WORD_TABLE . " 
					WHERE word_text IN ($sql_select)
					GROUP BY word_text";
				$result = $db->sql_query($sql);
				if ( !$result )
				{
					$error = $db->sql_error();
					die("Couldn't select words :: " . $sql . " :: " . $error['message']);
				}

				$sql_insert = array();
				while( $row = $db->sql_fetchrow($result) )
				{
					$sql_insert[] = "($post_id, " . $row['word_id'] . ", $title_match)";
				}

				$db->sql_freeresult($result);

				$sql = "INSERT INTO " . SEARCH_MATCH_TABLE . "
					(post_id, word_id, title_match)
					VALUES " . implode(", ", $sql_insert);
				$result = $db->sql_query($sql); 
				if ( !$result )
				{
					$error = $db->sql_error();
					die("Couldn't insert new word match :: " . $sql . " :: " . $error['message']);
				}

			} // All posts
		}

	//	$sql = "UNLOCK TABLES";
	//	$result = $db->sql_query($sql); 

	}

	// Remove common words after the first 2 batches and after every 4th batch after that.
	if( $batchcount % 4 == 3 )
	{
		print "<br>Removing common words (words that appear in more than $common_percent of the posts)<br>\n";
		flush();
		print "Removed ". remove_common("global", $common_percent) ." words that where too common.<br>";
	}
}

echo "<br>Done";

?>

</body>
</html>
