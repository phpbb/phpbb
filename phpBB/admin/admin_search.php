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
	$module['DB']['Search_indexing'] = $filename . $SID;

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
	$batchsize = 200; // Process this many posts per batch
	$batchstart = ( !isset($HTTP_GET_VARS['batchstart']) ) ? $row['min_post_id'] : $HTTP_GET_VARS['batchstart'];
	$batchcount = ( !isset($HTTP_GET_VARS['batchcount']) ) ? 1 : $HTTP_GET_VARS['batchcount'];
	$loopcount = 0;
	$batchend = $batchstart + $batchsize;

	//
	// Search re-indexing is tough on the server ... so we'll check the load
	// each loop and if we're on a 1min load of 3 or more we'll re-load the page
	// and try again. No idea how well this will work in practice so we'll see ...
	//
	if ( file_exists('/proc/loadavg') )
	{
		if ( $load = @file('/proc/loadavg') )
		{
			list($load) = explode(' ', $load[0]);

			if ( $load > 3 )
			{
				header("Location: admin_search.$phpEx$SID&batchstart=$batchstart&batchcount=$batch_count");
				exit;
			}
		}
	}

	//
	// Try and load stopword and synonym files
	//
	$stopword_array = array();
	$synonym_array = array();

	$dir = opendir($phpbb_root_path . 'language/');
	while ( $file = readdir($dir) )
	{
		if ( preg_match('#^lang_#', $file) && !is_file($phpbb_root_path . 'language/' . $file) && !is_link($phpbb_root_path . 'language/' . $file) )
		{
			unset($tmp_array);
			$tmp_array = @file($phpbb_root_path . 'language/' . $file . '/search_stopwords.txt');
			if ( is_array($tmp_array) )
			{
				$stopword_array = array_unique(array_merge($stopword_array, $tmp_array));
			}

			unset($tmp_array);
			$tmp_array = @file($phpbb_root_path . 'language/' . $file . '/search_synonyms.txt');
			if ( is_array($tmp_array) )
			{
				$synonym_array = array_unique(array_merge($synonym_array, $tmp_array));
			}
		}
	}

	closedir($dir);

	if ( !isset($HTTP_GET_VARS['batchstart']) )
	{
		//
		// Take board offline
		//
		$sql = "UPDATE " . CONFIG_TABLE . " 
			SET config_value = '1' 
			WHERE config_name = 'board_disable'";
		$db->sql_query($sql);

		//
		// Empty existing tables
		//
		$db->sql_query("TRUNCATE " . SEARCH_TABLE);
		$db->sql_query("TRUNCATE " . SEARCH_WORD_TABLE);
		$db->sql_query("TRUNCATE " . SEARCH_MATCH_TABLE);
	}

	//
	// Fetch a batch of posts_text entries
	//
	$sql = "SELECT COUNT(*) AS total, MAX(post_id) AS max_post_id, MIN(post_id) AS min_post_id 
		FROM " . POSTS_TEXT_TABLE;
	$result = $db->sql_query($sql);

	$row = $db->sql_fetchrow($result);
	$totalposts = $row['total'];
	$max_post_id = $row['max_post_id'];

	$db->sql_freeresult($result);

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
			$post_id = $row['post_id']; 

			$search_raw_words = array();
			$search_raw_words['text'] = split_words(clean_words('post', $row['post_text'], $stopword_array, $synonym_array));
			$search_raw_words['title'] = split_words(clean_words('post', $row['post_subject'], $stopword_array, $synonym_array));

			$word = array();
			$word_insert_sql = array();
			foreach ( $search_raw_words as $word_in => $search_matches )
			{
				$word_insert_sql[$word_in] = '';
				if ( !empty($search_matches) )
				{
					for ($i = 0; $i < count($search_matches); $i++)
					{ 
						$search_matches[$i] = trim($search_matches[$i]);

						if ( $search_matches[$i] != '' ) 
						{
							$word[] = $search_matches[$i];
							$word_insert_sql[$word_in] .= ( $word_insert_sql[$word_in] != '' ) ? ", '" . $search_matches[$i] . "'" : "'" . $search_matches[$i] . "'";
						} 
					}
				}
			}

			if ( count($word) )
			{
				$word_text_sql = '';
				$word = array_unique($word);

				for($i = 0; $i < count($word); $i++)
				{
					$word_text_sql .= ( ( $word_text_sql != '' ) ? ', ' : '' ) . "'" . $word[$i] . "'";
				}

				$check_words = array();
				switch( SQL_LAYER )
				{
					case 'postgresql':
					case 'msaccess':
					case 'mssql-odbc':
					case 'oracle':
					case 'db2':
						$sql = "SELECT word_id, word_text     
							FROM " . SEARCH_WORD_TABLE . " 
							WHERE word_text IN ($word_text_sql)";
						$result = $db->sql_query($sql);

						while ( $row = $db->sql_fetchrow($result) )
						{
							$check_words[$row['word_text']] = $row['word_id'];
						}
						break;
				}

				$value_sql = '';
				$match_word = array();
				for ($i = 0; $i < count($word); $i++)
				{ 
					$new_match = true;
					if ( isset($check_words[$word[$i]]) )
					{
						$new_match = false;
					}

					if ( $new_match )
					{
						switch( SQL_LAYER )
						{
							case 'mysql':
							case 'mysql4':
								$value_sql .= ( ( $value_sql != '' ) ? ', ' : '' ) . '(\'' . $word[$i] . '\')';
								break;
							case 'mssql':
								$value_sql .= ( ( $value_sql != '' ) ? ' UNION ALL ' : '' ) . "SELECT '" . $word[$i] . "'";
								break;
							default:
								$sql = "INSERT INTO " . SEARCH_WORD_TABLE . " (word_text) 
									VALUES ('" . $word[$i] . "')"; 
								$db->sql_query($sql);
								break;
						}
					}
				}

				if ( $value_sql != '' )
				{
					switch ( SQL_LAYER )
					{
						case 'mysql':
						case 'mysql4':
							$sql = "INSERT IGNORE INTO " . SEARCH_WORD_TABLE . " (word_text) 
								VALUES $value_sql"; 
							break;
						case 'mssql':
							$sql = "INSERT INTO " . SEARCH_WORD_TABLE . " (word_text) 
								$value_sql"; 
							break;
					}

					$db->sql_query($sql);
				}
			}

			foreach ( $word_insert_sql as $word_in => $match_sql )
			{
				$title_match = ( $word_in == 'title' ) ? 1 : 0;

				if ( $match_sql != '' )
				{
					$sql = "INSERT INTO " . SEARCH_MATCH_TABLE . " (post_id, word_id, title_match) 
						SELECT $post_id, word_id, $title_match  
							FROM " . SEARCH_WORD_TABLE . " 
							WHERE word_text IN ($match_sql)"; 
					$db->sql_query($sql);
				}
			}

		}
		while ( $row = $db->sql_fetchrow($result) );
	}

	$db->sql_freeresult($result);

	// Remove common words after the first 2 batches and after every 4th batch after that.
	if ( $batchcount % 4 == 3 )
	{
//		remove_common('global', $board_config['common_search']);
	}

	$batchcount++;

	if ( ( $batchstart + $batchsize ) < $max_post_id )
	{
		header("Location: admin_search.$phpEx$SID&batchstart=" . ( $batchstart + $batchsize ) . "&batchcount=$batch_count");
		exit;
	}
	else
	{
		$sql = "UPDATE " . CONFIG_TABLE . " 
			SET config_value = '0' 
			WHERE config_name = 'board_disable'";
		$db->sql_query($sql);

		page_header($lang['Search_indexing']);

?>

<h1><?php echo $lang['Search_indexing']; ?></h1>

<p><?php echo $lang['Search_indexing_complete']; ?></p>

<?php

		page_footer();

	}

	exit;

}
else if ( isset($HTTP_POST_VARS['cancel']) )
{
	$sql = "UPDATE " . CONFIG_TABLE . " 
		SET config_value = '0' 
		WHERE config_name = 'board_disable'";
	$db->sql_query($sql);

	page_header($lang['Search_indexing']);

?>

<h1><?php echo $lang['Search_indexing']; ?></h1>

<p><?php echo $lang['Search_indexing_cancel']; ?></p>

<?php

	page_footer();

}
else
{
	page_header($lang['Search_indexing']);

?>

<h1><?php echo $lang['Search_indexing']; ?></h1>

<p><?php echo $lang['Search_indexing_explain']; ?></p>

<form method="post" action="<?php echo "admin_search.$phpEx$SID"; ?>"><table cellspacing="1" cellpadding="4" border="0" align="center" bgcolor="#98AAB1">
	<tr>
		<td class="cat" height="28" align="center">&nbsp;<input type="submit" name="start" value="<?php echo $lang['Start']; ?>" class="mainoption" /> &nbsp; <input type="submit" name="cancel" value="<?php echo $lang['Cancel']; ?>" class="mainoption" />&nbsp;</td>
	</tr>
</table></form>

<?php

	page_footer();

}

?>