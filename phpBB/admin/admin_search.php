<?php 
/***************************************************************************
 *                             admin_search.php
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

if ( !empty($setmodules) )
{
	if ( !$acl->get_acl_admin('general') )
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['DB']['Search_indexing'] = $filename;

	return;
}

define('IN_PHPBB', 1);
//
// Include files
//
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.'.$phpEx);

//
// Do we have forum admin permissions?
//
if ( !$acl->get_acl_admin('general') )
{
	message_die(MESSAGE, $lang['No_admin']);
}

//
// Start indexing
//
if ( isset($HTTP_POST_VARS['start']) || isset($HTTP_GET_VARS['batchstart']) )
{
	//
	// Do not change anything below this line.
	//
	@set_time_limit(0);

	$common_percent = 0.4; // Percentage of posts in which a word has to appear to be marked as common

	//
	// Try and load stopword and synonym files
	//
	// This needs fixing! Shouldn't be hardcoded to English files!
	$stopword_array = array();
	$synonym_array = array();

	$dir = opendir($phpbb_root_path . 'language/');
	while ( $file = readdir($dir) )
	{
		if ( ereg('^lang_', $file) && !is_file($phpbb_root_path . 'language/' . $file) && !is_link($phpbb_root_path . 'language/' . $file) )
		{
			unset($tmp_array);
			$tmp_array = @file($phpbb_root_path . 'language/' . $file . '/search_stopwords.txt');

			if ( is_array($tmp_array) )
			{
				$stopword_array = array_merge($stopword_array, $tmp_array);
			}

			unset($tmp_array);
			$tmp_array = @file($phpbb_root_path . 'language/' . $file . '/search_synonyms.txt');

			if ( is_array($tmp_array) )
			{
				$synonym_array = array_merge($synonym_array, $tmp_array);
			}
		}
	}

	closedir($dir);

	$sql = "UPDATE " . CONFIG_TABLE . " 
		SET config_value = '1' 
		WHERE config_name = 'board_disable'";
	$db->sql_query($sql);

	//
	// Fetch a batch of posts_text entries
	//
	$sql = "SELECT COUNT(*) as total, MAX(post_id) as max_post_id 
		FROM " . POSTS_TEXT_TABLE;
	$result = $db->sql_query($sql);

	$max_post_id = $db->sql_fetchrow($result);

	$totalposts = $max_post_id['total'];
	$max_post_id = $max_post_id['max_post_id'];

	$postcounter = ( !isset($HTTP_GET_VARS['batchstart']) ) ? 0 : $HTTP_GET_VARS['batchstart'];

	$batchcount = 0;
	$batchsize = 200; // Process this many posts per loop
	for(;$postcounter <= $max_post_id; $postcounter += $batchsize)
	{
		$batchstart = $postcounter + 1;
		$batchend = $postcounter + $batchsize;
		$batchcount++;
		
		$sql = "SELECT * 
			FROM " . POSTS_TEXT_TABLE . " 
			WHERE post_id 
				BETWEEN $batchstart 
					AND $batchend";
		$result = $db->sql_query($sql);
		
		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{

//				print "\n<p>\n<a href='$PHP_SELF?batchstart=$batchstart'>Restart from posting $batchstart</a><br>\n";

				$post_id = $row['post_id']; 

				$matches = array();
				$matches['text'] = split_words(clean_words('post', $row['post_text'], $stopword_array, $synonym_array));
				$matches['title'] = split_words(clean_words('post', $row['post_subject'], $stopword_array, $synonym_array));

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
					$sql_in = '';
					$sql_insert = '';
					$sql_select = '';

					$word = array();
					$word_count = array();

					for($j = 0; $j < $num_matches; $j++)
					{
						if ( $this_word = strtolower(trim($match_ary[$j])) )
						{
							$word_count[$this_word] = ( isset($word_count[$this_word]) ) ? $word_count[$this_word] + 1 : 0;
							$comma = ($sql_insert != '')? ', ': '';
						
							$sql_insert .= "$comma('" . $this_word . "')";
							$sql_select .= "$comma'" . $this_word . "'";
						}
					}

					if ( $sql_insert == '' )
					{
						message_die(ERROR, 'No words found to index');
					}
						
					$sql = "INSERT IGNORE INTO " . SEARCH_WORD_TABLE . " (word_text) 
						VALUES $sql_insert";
					$db->sql_query($sql);

					// Get the word_id's out of the DB (to see if they are already there)
					$sql = "SELECT word_id, word_text
						FROM " . SEARCH_WORD_TABLE . " 
						WHERE word_text IN ($sql_select)
						GROUP BY word_text";
					$result2 = $db->sql_query($sql);

					$sql_insert = array();
					while( $row = $db->sql_fetchrow($result2) )
					{
						$sql_insert[] = "($post_id, " . $row['word_id'] . ", $title_match)";
					}

					$db->sql_freeresult($result2);

					$sql = "INSERT INTO " . SEARCH_MATCH_TABLE . " (post_id, word_id, title_match)
						VALUES " . implode(', ', $sql_insert);
					$db->sql_query($sql); 

				} // All posts
			}
			while ( $row = $db->sql_fetchrow($result) );
		}

		// Remove common words after the first 2 batches and after every 4th batch after that.
		if ( $batchcount % 4 == 3 )
		{
//			print "<br>Removing common words (words that appear in more than $common_percent of the posts)<br>\n";
//			flush();
//			print "Removed ". remove_common("global", $common_percent) ." words that where too common.<br>";
		}
	}

	echo "<br>Done";
	exit;

}
else if ( isset($HTTP_POST_VARS['cancel']) )
{


}
else
{
	page_header($lang['DB']);

?>

<h1><?php echo $lang['Search_indexing']; ?></h1>

<p><?php echo $lang['Search_indexing_explain']; ?></p>

<form method="post" action="<?php echo "admin_search.$phpEx$SID"; ?>"><table cellspacing="1" cellpadding="4" border="0" align="center" bgcolor="#98AAB1">
	<tr>
		<td class="cat" height="28" align="center">&nbsp;<input type="submit" name="start" value="<?php echo $lang['Start']; ?>" class="mainoption" />&nbsp;</td>
	</tr>
</table></form>

<?php

	page_footer();

}

?>