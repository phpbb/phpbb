<?php
/***************************************************************************
 *                                posting.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
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

$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/post.'.$phpEx);
include($phpbb_root_path . 'includes/bbcode.'.$phpEx);

// -----------------------
// Page specific functions
//
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

function remove_old( $post_id )
{
	global $db;

	if( count($word_id_list) )
	{
		$word_id_sql = "";
		for($i = 0; $i < count($word_id_list); $i++ )
		{
			if( $word_id_sql != "" )
			{
				$word_id_sql .= ", ";
			}
			$word_id_sql .= $word_id_list[$i]['word_id'];
		}
		$word_id_sql = " AND sl.word_id IN ($word_id_sql)";
	}
	else
	{
		$word_id_sql = "";
	}

}

function remove_common($percent, $word_id_list = array())
{
	global $db;

	if( count($word_id_list) )
	{
		$word_id_sql = "";
		for($i = 0; $i < count($word_id_list); $i++ )
		{
			if( $word_id_sql != "" )
			{
				$word_id_sql .= ", ";
			}
			$word_id_sql .= $word_id_list[$i]['word_id'];
		}
		$word_id_sql = " AND w.word_id IN ($word_id_sql)";

		$sql = "SELECT w.word_id, SUM(m.word_count) AS post_occur_count 
			FROM " . SEARCH_WORD_TABLE . " w, " . SEARCH_MATCH_TABLE . " m 
			WHERE w.word_id = m.word_id 
				$word_id_sql 
			GROUP BY w.word_id 
			ORDER BY post_occur_count DESC";
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't obtain search word sums", "", __LINE__, __FILE__, $sql);
		}

		if( $post_count = $db->sql_numrows($result) )
		{
			$rowset = $db->sql_fetchrowset($result);

			$sql = "SELECT COUNT(post_id) AS total_posts 
				FROM " . POSTS_TABLE;
				
			$result = $db->sql_query($sql); 
			if( !$result )
			{
				message_die(GENERAL_ERROR, "Couldn't obtain post count", "", __LINE__, __FILE__, $sql);
			}

			$row = $db->sql_fetchrow($result);

			$words_removed = 0;

			for($i = 0; $i < $post_count; $i++)
			{
				if( ( $rowset[$i]['post_occur_count'] / $row['total_posts'] ) >= $percent )
				{
					$sql = "DELETE FROM " . SEARCH_WORD_TABLE . "  
						WHERE word_id = " . $rowset[$i]['word_id'];
					$result = $db->sql_query($sql); 
					if( !$result )
					{
						message_die(GENERAL_ERROR, "Couldn't delete word list entry", "", __LINE__, __FILE__, $sql);
					}

					$sql = "DELETE FROM " . SEARCH_MATCH_TABLE . " 
						WHERE word_id = " . $rowset[$i]['word_id'];
					$result = $db->sql_query($sql); 
					if( !$result )
					{
						message_die(GENERAL_ERROR, "Couldn't delete word match entry", "", __LINE__, __FILE__, $sql);
					}

					$words_removed++;
				}
			}
		}
	}

	return $words_removed;
}

function remove_old_words($post_id)
{
	global $db, $phpbb_root_path, $board_config, $lang;

	$stopword_array = @file($phpbb_root_path . "language/lang_" . $board_config['default_lang'] . "/search_stopwords.txt"); 
	$synonym_array = @file($phpbb_root_path . "language/lang_" . $board_config['default_lang'] . "/search_synonyms.txt"); 

	$sql = "SELECT post_text 
		FROM " . POSTS_TEXT_TABLE . " 
		WHERE post_id = $post_id";
	if( $result = $db->sql_query($sql) )
	{
		$row = $db->sql_fetchrow($result);

		$search_text = clean_words($row['post_text'], $stopword_array, $synonym_array);
		$search_matches = split_words($search_text);

		if( count($search_matches) )
		{
			$word = array();
			$word_count = array();
			$phrase_string = $text;

			$sql_in = "";
			for ($j = 0; $j < count($search_matches); $j++)
			{ 
				$this_word = strtolower(trim($search_matches[$j]));

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
				FROM " . SEARCH_WORD_TABLE . " 
				WHERE word_text IN ($sql_in)";
			$result = $db->sql_query($sql);
			if( !$result )
			{
				message_die(GENERAL_ERROR, "Couldn't select words", "", __LINE__, __FILE__, $sql);
			}

			if( $word_check_count = $db->sql_numrows($result) )
			{
				$check_words = $db->sql_fetchrowset($result);

				$word_id_sql = "";
				for($i = 0; $i < count($check_words); $i++ )
				{
					if( $word_id_sql != "" )
					{
						$word_id_sql .= ", ";
					}
					$word_id_sql .= $check_words[$i]['word_id'];
				}
				$word_id_sql = "word_id IN ($word_id_sql)";

				$sql = "SELECT word_id, COUNT(post_id) AS post_occur_count 
					FROM " . SEARCH_MATCH_TABLE . "   
					WHERE $word_id_sql 
					GROUP BY word_id 
					ORDER BY post_occur_count DESC";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't obtain search word sums", "", __LINE__, __FILE__, $sql);
				}

				if( $post_count = $db->sql_numrows($result) )
				{
					$rowset = $db->sql_fetchrowset($result);

					for($i = 0; $i < $post_count; $i++)
					{
						if( $rowset[$i]['post_occur_count'] == 1 )
						{
							$sql = "DELETE FROM " . SEARCH_WORD_TABLE . "  
								WHERE word_id = " . $rowset[$i]['word_id'];
							$result = $db->sql_query($sql); 
							if( !$result )
							{
								message_die(GENERAL_ERROR, "Couldn't delete word list entry", "", __LINE__, __FILE__, $sql);
							}
						}
					}
				}

				$sql = "DELETE FROM " . SEARCH_MATCH_TABLE . "  
					WHERE post_id = $post_id";
				$result = $db->sql_query($sql); 
				if( !$result )
				{
					message_die(GENERAL_ERROR, "Couldn't delete word match entry for this post", "", __LINE__, __FILE__, $sql);
				}
			}
		}
	}
	else
	{
		message_die(GENERAL_ERROR, "Couldn't obtain post text", "", __LINE__, __FILE__, $sql);
	}

	return;
}

function add_search_words($post_id, $text)
{
	global $db, $phpbb_root_path, $board_config, $lang;

	$stopword_array = @file($phpbb_root_path . "language/lang_" . $board_config['default_lang'] . "/search_stopwords.txt"); 
	$synonym_array = @file($phpbb_root_path . "language/lang_" . $board_config['default_lang'] . "/search_synonyms.txt"); 

	$search_text = clean_words($text, $stopword_array, $synonym_array);
	$search_matches = split_words($search_text);

	if( count($search_matches) )
	{
		$word = array();
		$word_count = array();
		$phrase_string = $text;

		$sql_in = "";
		for ($j = 0; $j < count($search_matches); $j++)
		{ 
			$this_word = strtolower(trim($search_matches[$j]));

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
			FROM " . SEARCH_WORD_TABLE . " 
			WHERE word_text IN ($sql_in)";
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, "Couldn't select words", "", __LINE__, __FILE__, $sql);
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
					$sql = "INSERT INTO " . SEARCH_WORD_TABLE . "  (word_text) 
						VALUES ('". addslashes($word[$j]) . "')"; 
					$result = $db->sql_query($sql); 
					if( !$result )
					{
						message_die(GENERAL_ERROR, "Couldn't insert new word", "", __LINE__, __FILE__, $sql);
					}

					$word_id = $db->sql_nextid();
				}
				
				$sql = "INSERT INTO " . SEARCH_MATCH_TABLE . " (post_id, word_id, word_count, title_match) 
					VALUES ($post_id, $word_id, " . $word_count[$word[$j]] . ", 0)"; 
				$result = $db->sql_query($sql); 
				if( !$result )
				{
					message_die(GENERAL_ERROR, "Couldn't insert new word match", "", __LINE__, __FILE__, $sql);
				}
			}
		}
	}

	remove_common(0.25, $check_words);

	return;
}

function topic_review($topic_id, $is_inline_review)
{
	global $db, $board_config, $template, $lang, $images, $theme, $phpEx;
	global $userdata, $session_length, $user_ip;
	global $orig_word, $replacement_word;
	global $starttime;

	if( !$is_inline_review )
	{
		if( !isset($topic_id) )
		{
			message_die(GENERAL_MESSAGE, 'Topic_not_exist');
		}

		//
		// Get topic info ...
		//
		$sql = "SELECT f.forum_id, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_sticky, f.auth_announce, f.auth_pollcreate, f.auth_vote, f.auth_attachments 
			FROM " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f 
			WHERE t.topic_id = $topic_id
				AND f.forum_id = t.forum_id";
		if(!$result = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Couldn't obtain topic information", "", __LINE__, __FILE__, $sql);
		}

		if( !$total_rows = $db->sql_numrows($result) )
		{
			message_die(GENERAL_MESSAGE, 'Topic_post_not_exist');
		}
		$forum_row = $db->sql_fetchrow($result);

		$forum_id = $forum_row['forum_id'];
		
		//
		// Start session management
		//
		$userdata = session_pagestart($user_ip, $forum_id, $session_length);
		init_userprefs($userdata);
		//
		// End session management
		//

		$is_auth = array();
		$is_auth = auth(AUTH_ALL, $forum_id, $userdata, $forum_row);

	}

	//
	// Go ahead and pull all data for this topic
	//
	$sql = "SELECT u.username, u.user_id, p.*,  pt.post_text, pt.post_subject
		FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . POSTS_TEXT_TABLE . " pt
		WHERE p.topic_id = $topic_id
			AND p.poster_id = u.user_id
			AND p.post_id = pt.post_id
		ORDER BY p.post_time DESC
		LIMIT " . $board_config['posts_per_page'];
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Couldn't obtain post/user information.", "", __LINE__, __FILE__, $sql);
	}

	if(!$total_posts = $db->sql_numrows($result))
	{
		message_die(GENERAL_ERROR, "There don't appear to be any posts for this topic.", "", __LINE__, __FILE__, $sql);
	}
	$postrow = $db->sql_fetchrowset($result);

	//
	// Define censored word matches
	//
	if( empty($orig_word) && empty($replacement_word) )
	{
		$orig_word = array();
		$replacement_word = array();
		obtain_word_list($orig_word, $replacement_word);
	}

	//
	// Dump out the page header and load viewtopic body template
	//
	if( !$is_inline_review )
	{
		$gen_simple_header = TRUE;

		$page_title = $lang['Review_topic'] ." - $topic_title";
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			"reviewbody" => "posting_topic_review.tpl")
		);
	}

	//
	// Okay, let's do the loop, yeah come on baby let's do the loop
	// and it goes like this ...
	//
	for($i = 0; $i < $total_posts; $i++)
	{
		$poster_id = $postrow[$i]['user_id'];
		$poster = $postrow[$i]['username'];

		$post_date = create_date($board_config['default_dateformat'], $postrow[$i]['post_time'], $board_config['board_timezone']);

		$mini_post_img = '<img src="' . $images['icon_minipost'] . '" alt="' . $lang['Post'] . '" />';

		//
		// Handle anon users posting with usernames
		//
		if( $poster_id == ANONYMOUS && $postrow[$i]['post_username'] != '' )
		{
			$poster = $postrow[$i]['post_username'];
			$poster_rank = $lang['Guest'];
		}

		$post_subject = ( $postrow[$i]['post_subject'] != "" ) ? $postrow[$i]['post_subject'] : "";

		$message = $postrow[$i]['post_text'];
		$bbcode_uid = $postrow[$i]['bbcode_uid'];

		//
		// If the board has HTML off but the post has HTML
		// on then we process it, else leave it alone
		//
		if( !$board_config['allow_html'] )
		{
			if( $postrow[$i]['enable_html'] )
			{
				$message = preg_replace("#(<)([\/]?.*?)(>)#is", "&lt;\\2&gt;", $message);
			}
		}

		if( $bbcode_uid != "" )
		{
			$message = ( $board_config['allow_bbcode'] ) ? bbencode_second_pass($message, $bbcode_uid) : preg_replace("/\:[0-9a-z\:]+\]/si", "]", $message);
		}

		$message = make_clickable($message);

		if( count($orig_word) )
		{
			$post_subject = preg_replace($orig_word, $replacement_word, $post_subject);
			$message = preg_replace($orig_word, $replacement_word, $message);
		}

		if( $board_config['allow_smilies'] && $postrow[$i]['enable_smilies'] )
		{
			$message = smilies_pass($message);
		}

		$message = str_replace("\n", "<br />", $message);

		//
		// Again this will be handled by the templating
		// code at some point
		//
		$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
		$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

		$template->assign_block_vars("postrow", array(
			"ROW_COLOR" => "#" . $row_color, 
			"ROW_CLASS" => $row_class, 

			"MINI_POST_IMG" => $mini_post_img, 
			"POSTER_NAME" => $poster, 
			"POST_DATE" => $post_date, 
			"POST_SUBJECT" => $post_subject, 
			"MESSAGE" => $message)
		);
	}

	$template->assign_vars(array(
		"L_POSTED" => $lang['Posted'],
		"L_POST_SUBJECT" => $lang['Post_subject'], 
		"L_TOPIC_REVIEW" => $lang['Topic_review'])
	);

	if( !$is_inline_review )
	{
		$template->pparse("reviewbody");

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
}
//
// End page specific functions
// ---------------------------

// -------------------------------------------
// Do some initial checks, set basic variables,
// etc.
//
$html_entities_match = array("#<#", "#>#", "#& #", "#\"#");
$html_entities_replace = array("&lt;", "&gt;", "&amp; ", "&quot;");

$submit = ( isset($HTTP_POST_VARS['submit']) ) ? TRUE : 0;
$cancel = ( isset($HTTP_POST_VARS['cancel']) ) ? TRUE : 0;
$preview = ( isset($HTTP_POST_VARS['preview']) ) ? TRUE : 0;
$confirm = ( isset($HTTP_POST_VARS['confirm']) ) ? TRUE : 0;
$delete = ( isset($HTTP_POST_VARS['delete']) ) ? TRUE : 0;
$poll_delete = ( isset($HTTP_POST_VARS['poll_delete']) ) ? TRUE : 0;

$poll_add_option = ( isset($HTTP_POST_VARS['add_poll_option']) ) ? TRUE : 0;
$poll_edit_option = ( isset($HTTP_POST_VARS['edit_poll_option']) ) ? TRUE : 0;
$poll_delete_option = ( isset($HTTP_POST_VARS['del_poll_option']) ) ? TRUE : 0;

$refresh = $preview || $poll_add_option || $poll_edit_option || $poll_delete_option;

//
// Mode, topic_id, post_id and forum_id settings
//
if( isset($HTTP_POST_VARS['mode']) || isset($HTTP_GET_VARS['mode']) )
{
	$mode = ( isset($HTTP_POST_VARS['mode']) ) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
}
else
{
	$mode = "";
}

if( isset($HTTP_GET_VARS[POST_FORUM_URL]) || isset($HTTP_POST_VARS[POST_FORUM_URL]) )
{
	$forum_id = (isset($HTTP_POST_VARS[POST_FORUM_URL])) ? $HTTP_POST_VARS[POST_FORUM_URL] : $HTTP_GET_VARS[POST_FORUM_URL];
}
else
{
	$forum_id = "";
}

if( isset($HTTP_GET_VARS[POST_POST_URL]) || isset($HTTP_POST_VARS[POST_POST_URL]) )
{
	$post_id = (isset($HTTP_POST_VARS[POST_POST_URL])) ? $HTTP_POST_VARS[POST_POST_URL] : $HTTP_GET_VARS[POST_POST_URL];
}
else
{
	$post_id = "";
}

if( isset($HTTP_GET_VARS[POST_TOPIC_URL]) || isset($HTTP_POST_VARS[POST_TOPIC_URL]) )
{
	$topic_id = (isset($HTTP_POST_VARS[POST_TOPIC_URL])) ? $HTTP_POST_VARS[POST_TOPIC_URL] : $HTTP_GET_VARS[POST_TOPIC_URL];
}
else
{
	$topic_id = "";
}


//
// Was cancel pressed? If so then redirect to the appropriate
// page, no point in continuing with any further checks
//
if( $cancel )
{
	if($post_id != "")
	{
		$redirect = "viewtopic.$phpEx?" . POST_POST_URL . "=$post_id";
		$post_append = "#$post_id";
	}
	else if($topic_id != "")
	{
		$redirect = "viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id";
		$post_append = "";
	}
	else if($forum_id != "")
	{
		$redirect = "viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id";
		$post_append = "";
	}
	else
	{
		$redirect = "index.$phpEx";
		$post_append = "";
	}
	header("Location:" . append_sid($redirect) . $post_append, true);
}
//
// Continue var definitions
//

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_POSTING, $session_length);
init_userprefs($userdata);
//
// End session management
//

//
// If the mode is set to topic review then output
// that review ...
//
if( $mode == "topicreview" )
{
	topic_review($topic_id, false);
	exit;
}

//
// Set toggles for various options
//
if( !$board_config['allow_html'] )
{
	$html_on = 0;
}
else
{
	$html_on = ( $submit || $refresh ) ? ( ( !empty($HTTP_POST_VARS['disable_html']) ) ? 0 : TRUE ) : ( ( $userdata['user_id'] == ANONYMOUS ) ? $board_config['allow_html'] : $userdata['user_allowhtml'] );
}

if( !$board_config['allow_bbcode'] )
{
	$bbcode_on = 0;
}
else
{
	$bbcode_on = ( $submit || $refresh ) ? ( ( !empty($HTTP_POST_VARS['disable_bbcode']) ) ? 0 : TRUE ) : ( ( $userdata['user_id'] == ANONYMOUS ) ? $board_config['allow_bbcode'] : $userdata['user_allowbbcode'] );
}

if( !$board_config['allow_smilies'] )
{
	$smilies_on = 0;
}
else
{
	$smilies_on = ( $submit || $refresh ) ? ( ( !empty($HTTP_POST_VARS['disable_smilies']) ) ? 0 : TRUE ) : ( ( $userdata['user_id'] == ANONYMOUS ) ? $board_config['allow_smilies'] : $userdata['user_allowsmile'] );
}

$attach_sig = ( $submit || $refresh ) ? ( ( !empty($HTTP_POST_VARS['attach_sig']) ) ? TRUE : 0 ) : ( ( $userdata['user_id'] == ANONYMOUS ) ? 0 : $userdata['user_attachsig'] );

//
// Here we do various lookups to find topic_id, forum_id, post_id etc.
// Doing it here prevents spoofing (eg. faking forum_id, topic_id or post_id
//
if( $mode != "newtopic" )
{
	if( $mode == "reply" || $mode == "quote" || $mode == "vote" )
	{
		if( ( $mode == "reply" || $mode == "vote" ) && $topic_id )
		{
			$sql = "SELECT f.forum_id, f.forum_status, f.forum_name, t.topic_status
				FROM " . FORUMS_TABLE . " f, " . TOPICS_TABLE . " t
				WHERE t.topic_id = $topic_id
					AND f.forum_id = t.forum_id";
		}
		else if( $mode == "quote" && $post_id )
		{
			$sql = "SELECT f.forum_id, f.forum_status, f.forum_name, t.topic_id, t.topic_status
				FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f
				WHERE p.post_id = $post_id
					AND t.topic_id = p.topic_id
					AND f.forum_id = t.forum_id";
		}
		else
		{
			$message = ( $mode == "quote" ) ? $lang['No_post_id'] : $lang['No_topic_id'];
			message_die(GENERAL_MESSAGE, $message);
		}
	}
	else if( $mode == "editpost" || $mode == "delete" )
	{
		if( $post_id )
		{
			$sql = "SELECT p2.post_id, t.topic_id, t.topic_status, t.topic_last_post_id, t.topic_vote, f.forum_id, f.forum_name, f.forum_status, f.forum_last_post_id 
				FROM " . POSTS_TABLE . " p, " . POSTS_TABLE . " p2, " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f
				WHERE p.post_id = $post_id 
					AND p2.topic_id = p.topic_id 
					AND t.topic_id = p.topic_id
					AND f.forum_id = t.forum_id
				ORDER BY p2.post_time ASC
				LIMIT 1";

		}
		else
		{
			message_die(GENERAL_MESSAGE, $lang['No_post_id']);
		}
	}
	else
	{
		message_die(GENERAL_MESSAGE, $lang['No_valid_mode']);
	}

	if( $result = $db->sql_query($sql) )
	{
		$check_row = $db->sql_fetchrow($result);

		$forum_id = $check_row['forum_id'];

		$forum_name = $check_row['forum_name'];
		$topic_status = $check_row['topic_status'];
		$forum_status = $check_row['forum_status'];

		if( $mode == "editpost" || $mode == "delete" )
		{
			$topic_id = $check_row['topic_id'];

			$is_first_post_topic = ($check_row['post_id'] == $post_id) ? TRUE : 0;
			$is_last_post_topic = ($check_row['topic_last_post_id'] == $post_id) ? TRUE : 0;
			$is_last_post_forum = ($check_row['forum_last_post_id'] == $post_id) ? TRUE : 0;

			$post_has_poll = ($check_row['topic_vote']) ? TRUE : 0;

			if( $is_first_post_topic && $post_has_poll )
			{
				$sql = "SELECT vd.vote_id, vr.vote_result 
					FROM " . VOTE_DESC_TABLE . " vd, " . VOTE_RESULTS_TABLE . " vr 
					WHERE vd.topic_id = $topic_id 
						AND vr.vote_id = vd.vote_id";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't obtain vote data for this topic", "", __LINE__, __FILE__, $sql);
				}

				if( $vote_rows = $db->sql_numrows($result) )
				{
					$rowset = $db->sql_fetchrowset($result);

					$vote_id = $rowset[0]['vote_id'];
			
					$vote_results_sum = 0;
					for($i = 0; $i < $vote_rows; $i++ )
					{
						$vote_results_sum += $rowset[$i]['vote_result'];
					}

					$can_edit_poll = ( !$vote_results_sum ) ? TRUE : 0;
				}
			}
			else
			{
				$can_edit_poll = 0;
			}
		}
		else
		{
			if( $mode == "quote" )
			{
				$topic_id = $check_row['topic_id'];
			}

			$is_first_post_topic = 0;
			$is_last_post_topic = 0;
			$post_has_poll = 0;
			$can_edit_poll = 0;
		}
	}
	else
	{
		message_die(GENERAL_MESSAGE, $lang['No_such_post']);
	}
}
else
{
	$sql = "SELECT forum_name, forum_status
		FROM " . FORUMS_TABLE . " 
		WHERE forum_id = $forum_id";
	if( $result = $db->sql_query($sql) )
	{
		$check_row = $db->sql_fetchrow($result);

		$forum_status = $check_row['forum_status']; 
		$forum_name = $check_row['forum_name'];
		$topic_status = TOPIC_UNLOCKED;

		$is_first_post_topic = TRUE;
		$is_last_post_topic = 0;
		$post_has_poll = 0;
		$can_edit_poll = 0;

	}
	else
	{
		message_die(GENERAL_MESSAGE, $lang['Forum_not_exist']);
	}
}

//
// Is topic or forum locked?
//
if( $forum_status == FORUM_LOCKED )
{
	message_die(GENERAL_MESSAGE, $lang['Forum_locked']);
}
else if( $topic_status == TOPIC_LOCKED )
{
	message_die(GENERAL_MESSAGE, $lang['Topic_locked']);
}

//
// Set topic type
//
if( isset($HTTP_POST_VARS['topictype']) )
{
	if( $HTTP_POST_VARS['topictype']  == "announce" )
	{
		$topic_type = POST_ANNOUNCE;
	}
	else if( $HTTP_POST_VARS['topictype'] == "sticky" )
	{
		$topic_type = POST_STICKY;
	}
	else
	{
		$topic_type = POST_NORMAL;
	}
}
else
{
	$topic_type = POST_NORMAL;
}

//
// Auth checks
//
$auth_type = AUTH_ALL;
switch( $mode )
{
	case 'newtopic':
		if( $topic_type == POST_ANNOUNCE  )
		{
			$is_auth_type = "auth_announce";
			$auth_string = $lang['can_post_announcements'];
		}
		else if( $topic_type == POST_STICKY )
		{
			$is_auth_type = "auth_sticky";
			$auth_string = $lang['can_post_sticky_topics'];
		}
		else
		{
			$is_auth_type = "auth_post";
			$auth_string = $lang['can_post_new_topics'];
		}
		break;

	case 'reply':
	case 'quote':
		$is_auth_type = "auth_reply";
		$auth_string = $lang['can_reply_to_topics'];
		break;

	case 'editpost':
		$is_auth_type = "auth_edit";
		$auth_string = $lang['can_edit_topics'];
		break;

	case 'delete':
		$is_auth_type = "auth_delete";
		$auth_string = $lang['can_delete_topics'];
		break;

	case 'vote':
		$is_auth_type = "auth_vote";
		$auth_string = $lang['can_vote'];
		break;

	case 'topicreview':
		$is_auth_type = "auth_read";
		$auth_string = $lang['can_read'];
		break;

	default:
		message_die(GENERAL_MESSAGE, $lang['No_post_mode']);
		break;
}

//
// Do required auth check
//
$is_auth = auth($auth_type, $forum_id, $userdata);

//
// The user is not authed, if they're not logged in then redirect
// them, else show them an error message
//
if( !$is_auth[$is_auth_type] )
{
	if( !$userdata['session_logged_in'] )
	{
		switch( $mode )
		{
			case 'newtopic':
				$redirect = "mode=newtopic&" . POST_FORUM_URL . "=$forum_id";
				break;
			case 'reply':
			case 'topicreview':
				$redirect = "mode=reply&" . POST_TOPIC_URL . "=$topic_id";
				break;
			case 'quote':
				$redirect = "mode=quote&" . POST_POST_URL ."=$post_id";
				break;
			case 'editpost':
				$redirect = "mode=editpost&" . POST_POST_URL ."=$post_id&" . POST_TOPIC_URL . "=$topic_id";
				break;
		}

		header("Location: " . append_sid("login.$phpEx?redirect=posting.$phpEx&" . $redirect, true));

	}
	else
	{
		$message = $lang['Sorry_auth'] . $is_auth[$is_auth_type . "_type"] . $auth_string . $lang['this_forum'];
	}

	message_die(GENERAL_MESSAGE, $message);
}
//
// End Auth
//

//
// Notify on reply
//
if( ( $mode == "reply" || $mode == "editpost" ) && $topic_id )
{
	if( $submit || $refresh )
	{
		$notify_user = ( !empty($HTTP_POST_VARS['notify']) ) ? TRUE : 0;
	}
	else
	{
		$sql = "SELECT topic_id 
			FROM " . TOPICS_WATCH_TABLE . "
			WHERE topic_id = $topic_id
				AND user_id = " . $userdata['user_id'];
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't obtain topic watch information", "", __LINE__, __FILE__, $sql);
		}

		$notify_user = ( $db->sql_numrows($result) ) ? TRUE : $userdata['user_notify'];
	}
}
else
{
	$notify_user = ( $submit || $preview ) ? ( ( !empty($HTTP_POST_VARS['notify']) ) ? TRUE : 0 ) : $userdata['user_notify'];
}

//
// End variable checks and definitions
// -----------------------------------


// -------------------------------------------------------
// All initial checks complete, we can not start the major
// posting related code
//

if( $submit && $mode != "vote" )
{
	if( !empty($HTTP_POST_VARS['username']) )
	{
		$post_username = trim(strip_tags($HTTP_POST_VARS['username']));

		if( !validate_username(stripslashes($post_username)) )
		{
			$error = TRUE;
			if(!empty($error_msg))
			{
				$error_msg .= "<br />";
			}
			$error_msg .= $lang['Bad_username'];
		}
	}
	else
	{
		$post_username = "";
	}

	$post_subject = trim(strip_tags($HTTP_POST_VARS['subject']));
	if( $mode == 'newtopic' && empty($post_subject) )
	{
		$error = TRUE;
		if( !empty($error_msg) )
		{
			$error_msg .= "<br />";
		}
		$error_msg .= $lang['Empty_subject'];
	}

	if( !empty($HTTP_POST_VARS['message']) )
	{
		if( !$error )
		{
			if( $bbcode_on )
			{
				$bbcode_uid = make_bbcode_uid();
			}

			$post_message = prepare_message($HTTP_POST_VARS['message'], $html_on, $bbcode_on, $smilies_on, $bbcode_uid);

		}
	}
	else
	{
		$error = TRUE;
		if(!empty($error_msg))
		{
			$error_msg .= "<br />";
		}
		$error_msg .= $lang['Empty_message'];
	}

	//
	// Handle poll stuff
	//
	$topic_vote = 0;

	if( $mode == "newtopic" || $mode == "editpost" )
	{
		if( $is_auth['auth_pollcreate'] && $is_first_post_topic )
		{
			$poll_title = ( isset($HTTP_POST_VARS['poll_title']) ) ? trim(strip_tags($HTTP_POST_VARS['poll_title'])) : "";
			$poll_length = ( isset($HTTP_POST_VARS['poll_length']) ) ? intval($HTTP_POST_VARS['poll_length']) : 0;
			if( $poll_length < 0 )
			{
				$poll_length = 0;
			}

			$poll_options = 0;
			$poll_option_list = array();
			if( isset($HTTP_POST_VARS['poll_option_text']) )
			{
				while( list($option_id, $option_text) = each($HTTP_POST_VARS['poll_option_text']) )
				{
					$poll_option_list[$option_id] = trim(strip_tags($option_text));
					$poll_options++;
				}
			}

			if( $poll_title == "" && $poll_options )
			{
				$error = TRUE;
				if(!empty($error_msg))
				{
					$error_msg .= "<br />";
				}
				$error_msg .= $lang['Empty_poll_title']; 
			}

			if( $poll_title != "" )
			{
				if( $poll_options < 2 )
				{
					$error = TRUE;
					if(!empty($error_msg))
					{
						$error_msg .= "<br />";
					}
					$error_msg .= $lang['To_few_poll_options']; 
				}
				else if( $poll_options > $board_config['max_poll_options'] )
				{
					$error = TRUE;
					if(!empty($error_msg))
					{
						$error_msg .= "<br />";
					}
					$error_msg .= $lang['To_many_poll_options']; 
				}
			}

			if( $poll_title != "" && $poll_options >= 2 && $poll_options <= $board_config['max_poll_options'] )
			{
				$topic_vote = 1;
				$sql_topic_vote_edit = ", topic_vote = 1";
			}
		}
	}
}

//
// Submit or confirm ... big chunk of code ... can probably be
// still further reduced, will look at it later, possibly for
// 2.2
//
if( ( $submit || $confirm || $mode == "delete"  ) && !$error )
{
	$current_time = time();

	//
	// Which mode was selected?
	//
	if( $mode == "newtopic" || $mode == "reply" )
	{
		//
		// Flood control
		//
		$sql = "SELECT MAX(post_time) AS last_post_time
			FROM " . POSTS_TABLE . "
			WHERE poster_ip = '$user_ip'";
		if($result = $db->sql_query($sql))
		{
			$db_row = $db->sql_fetchrow($result);

			$last_post_time = $db_row['last_post_time'];

			if( ($current_time - $last_post_time) < $board_config['flood_interval'] )
			{
				message_die(GENERAL_MESSAGE, $lang['Flood_Error']);
			}
		}
		//
		// End Flood control
		//

		if( $mode == "newtopic" )
		{
			$sql  = "INSERT INTO " . TOPICS_TABLE . " (topic_title, topic_poster, topic_time, forum_id, topic_status, topic_type, topic_vote)
				VALUES ('$post_subject', " . $userdata['user_id'] . ", $current_time, $forum_id, " . TOPIC_UNLOCKED . ", $topic_type, $topic_vote)";

			if( $result = $db->sql_query($sql, BEGIN_TRANSACTION) )
			{
				$new_topic_id = $db->sql_nextid();
			}
			else
			{
				message_die(GENERAL_ERROR, "Error inserting data into topics table", "", __LINE__, __FILE__, $sql);
			}

			//
			// Handle poll ...
			//
			if( $is_auth['auth_pollcreate'] && $topic_vote )
			{
				$sql = "INSERT INTO " . VOTE_DESC_TABLE . " (topic_id, vote_text, vote_start, vote_length) 
					VALUES ($new_topic_id, '$poll_title', $current_time, " . ( $poll_length * 86400 ) . ")";
				if( $result = $db->sql_query($sql) )
				{
					$new_vote_id = $db->sql_nextid();

					$poll_option_id = 1;
					while( list($option_id, $option_text) = each($poll_option_list) )
					{
						$sql = "INSERT INTO " . VOTE_RESULTS_TABLE . " (vote_id, vote_option_id, vote_option_text, vote_result)
							VALUES ($new_vote_id, $poll_option_id, '$option_text', 0)";
						if( !$result = $db->sql_query($sql) )
						{
							// Rollback ...
							if(SQL_LAYER == "mysql")
							{
								$sql_del_t = "DELETE FROM " . TOPICS_TABLE . " 
									WHERE topic_id = $topic_id";
								$db->sql_query($sql_del_t);
								$sql_del_v = "DELETE FROM " . VOTE_DESC_TABLE . " 
									WHERE vote_id = $new_vote_id";
								$db->sql_query($sql_del_v);
							}
							message_die(GENERAL_ERROR, "Couldn't insert new poll options", "", __LINE__, __FILE__, $sql);
						}
						$poll_option_id++;
					}
				}
				else
				{
					if(SQL_LAYER == "mysql")
					{
						// Rollback ...
						$sql_del_t = "DELETE FROM " . TOPICS_TABLE . " 
							WHERE topic_id = $topic_id";
						$db->sql_query($sql_del_t);
					}
					message_die(GENERAL_ERROR, "Couldn't insert new poll information", "", __LINE__, __FILE__, $sql);
				}
			}
		}
		else
		{
			$new_topic_id = $topic_id;
		}

		$sql = "INSERT INTO " . POSTS_TABLE . " (topic_id, forum_id, poster_id, post_username, post_time, poster_ip, bbcode_uid, enable_bbcode, enable_html, enable_smilies, enable_sig)
			VALUES ($new_topic_id, $forum_id, " . $userdata['user_id'] . ", '$post_username', $current_time, '$user_ip', '$bbcode_uid', $bbcode_on, $html_on, $smilies_on, $attach_sig)";
		$result = ($mode == "reply") ? $db->sql_query($sql, BEGIN_TRANSACTION) : $db->sql_query($sql);

		if( $result )
		{
			$new_post_id = $db->sql_nextid();

			$sql = "INSERT INTO " . POSTS_TEXT_TABLE . " (post_id, post_subject, post_text)
				VALUES ($new_post_id, '$post_subject', '$post_message')";

			if( $db->sql_query($sql) )
			{
				$sql = "UPDATE " . TOPICS_TABLE . "
					SET topic_last_post_id = $new_post_id";
				if($mode == "reply")
				{
					$sql .= ", topic_replies = topic_replies + 1 ";
				}
				$sql .= " WHERE topic_id = $new_topic_id";

				if( $db->sql_query($sql) )
				{				
					$sql = "UPDATE " . FORUMS_TABLE . "
						SET forum_last_post_id = $new_post_id, forum_posts = forum_posts + 1";
					if( $mode == "newtopic" )
					{
						$sql .= ", forum_topics = forum_topics + 1";
					}

					$sql .= " WHERE forum_id = $forum_id";
					
					if( $db->sql_query($sql) )
					{
						$sql = "UPDATE " . USERS_TABLE . "
							SET user_posts = user_posts + 1
							WHERE user_id = " . $userdata['user_id'];

						if( $db->sql_query($sql, END_TRANSACTION)) 
						{
							add_search_words($new_post_id, stripslashes($post_message));

							//
							// Email users who are watching this topic
							//
							if( $mode == "reply" )
							{
								$sql = "SELECT u.user_id, u.username, u.user_email, t.topic_title
									FROM " . TOPICS_WATCH_TABLE . " tw, " . TOPICS_TABLE . " t, " . USERS_TABLE . " u
									WHERE tw.topic_id = $new_topic_id
										AND tw.user_id NOT IN (" . $userdata['user_id'] . ", " . ANONYMOUS . " ) 
										AND tw.notify_status = " . TOPIC_WATCH_UN_NOTIFIED . "
										AND t.topic_id = tw.topic_id
										AND u.user_id = tw.user_id";
								if( $result = $db->sql_query($sql) )
								{
									$email_set = $db->sql_fetchrowset($result);
									$update_watched_sql = "";

									include($phpbb_root_path . 'includes/emailer.'.$phpEx);
									$emailer = new emailer($board_config['smtp_delivery']);

									$email_headers = "From: " . $board_config['board_email'] . "\nReturn-Path: " . $board_config['board_email'] . "\r\n";
									$path = (dirname($HTTP_SERVER_VARS['REQUEST_URI']) == "/") ? "" : dirname($HTTP_SERVER_VARS['REQUEST_URI']);
									$server_name = ( isset($HTTP_SERVER_VARS['HTTP_HOST']) ) ? $HTTP_SERVER_VARS['HTTP_HOST'] : $HTTP_SERVER_VARS['SERVER_NAME'];

									for($i = 0; $i < count($email_set); $i++)
									{
										if( $email_set[$i]['user_email'] != "")
										{
											$emailer->use_template("topic_notify");
											$emailer->email_address($email_set[$i]['user_email']);
											$emailer->set_subject($lang['Topic_reply_notification']);
											$emailer->extra_headers($email_headers);

											$emailer->assign_vars(array(
												"EMAIL_SIG" => str_replace("<br />", "\n", "-- \n" . $board_config['board_email_sig']),
												"USERNAME" => $email_set[$i]['username'],
												"SITENAME" => $board_config['sitename'],
												"TOPIC_TITLE" => $email_set[$i]['topic_title'],

												"U_TOPIC" => "http://" . $server_name . $path . "/viewtopic.$phpEx?" . POST_POST_URL . "=$new_post_id#$new_post_id",
												"U_STOP_WATCHING_TOPIC" => "http://" . $server_name . $path . "/viewtopic.$phpEx?" . POST_TOPIC_URL . "=$new_topic_id&unwatch=topic")
											);

											$emailer->send();
											$emailer->reset();

											if($update_watched_sql != "")
											{
												$update_watched_sql .= ", ";
											}
											$update_watched_sql .= $email_set[$i]['user_id'];
										}
									}

									if($update_watched_sql != "")
									{
										$sql = "UPDATE " . TOPICS_WATCH_TABLE . "
											SET notify_status = " . TOPIC_WATCH_NOTIFIED . "
											WHERE topic_id = $new_topic_id
												AND user_id IN ($update_watched_sql)";
										$db->sql_query($sql);
									}
								}
							}

							//
							// Handle notification request ... 
							//
							if( isset($notify_user) )
							{
								if($mode == "reply")
								{
									$sql = "SELECT *
										FROM " . TOPICS_WATCH_TABLE . "
										WHERE topic_id = $new_topic_id
											AND user_id = " . $userdata['user_id'];
									if( !$result = $db->sql_query($sql) )
									{
										message_die(GENERAL_ERROR, "Couldn't obtain topic watch information", "", __LINE__, __FILE__, $sql);
									}

									if( $db->sql_numrows($result) )
									{
										if( !$notify_user )
										{
											$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . "
												WHERE topic_id = $new_topic_id
													AND user_id = " . $userdata['user_id'];
											if( !$result = $db->sql_query($sql) )
											{
												message_die(GENERAL_ERROR, "Couldn't delete topic watch information", "", __LINE__, __FILE__, $sql);
											}
										}
									}
									else if( $notify_user )
									{
										$sql = "INSERT INTO " . TOPICS_WATCH_TABLE . " (user_id, topic_id, notify_status)
											VALUES (" . $userdata['user_id'] . ", $new_topic_id, 0)";
										if( !$result = $db->sql_query($sql) )
										{
											message_die(GENERAL_ERROR, "Couldn't insert topic watch information", "", __LINE__, __FILE__, $sql);
										}
									}
								}
								else if( $notify_user )
								{
									$sql = "INSERT INTO " . TOPICS_WATCH_TABLE . " (user_id, topic_id, notify_status)
										VALUES (" . $userdata['user_id'] . ", $new_topic_id, 0)";
									if( !$result = $db->sql_query($sql) )
									{
										message_die(GENERAL_ERROR, "Couldn't insert topic watch information", "", __LINE__, __FILE__, $sql);
									}
								}
							}

							//
							// If we get here the post has been inserted successfully.
							//
							$template->assign_vars(array(
								"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("viewtopic.$phpEx?" . POST_POST_URL . "=$new_post_id") . '#' . $new_post_id . '">')
							);

							$message = $lang['Stored'] . "<br /><br />" . sprintf($lang['Click_view_message'], "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_POST_URL . "=$new_post_id") . "#$new_post_id\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_forum'], "<a href=\"" . append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id") . "\">", "</a>");

							message_die(GENERAL_MESSAGE, $message);
						}
						else
						{
							message_die(GENERAL_ERROR, "Error updating users table", "", __LINE__, __FILE__, $sql);
						}
					}
					else
					{
						message_die(GENERAL_ERROR, "Error updating forums table", "", __LINE__, __FILE__, $sql);
					}
				}
				else
				{
					message_die(GENERAL_ERROR, "Error updating topics table", "", __LINE__, __FILE__, $sql);
				}
			}
			else
			{
				// Rollback
				if(SQL_LAYER == "mysql")
				{
					$sql = "DELETE FROM " . POSTS_TABLE . "
						WHERE post_id = $new_post_id";
					$db->sql_query($sql);
				}
				message_die(GENERAL_ERROR, "Error inserting data into posts text table", "", __LINE__, __FILE__, $sql);
			}
		}
		else
		{
			message_die(GENERAL_ERROR, "Error inserting data into posts table", "", __LINE__, __FILE__, $sql);
		}
		//
		// End of mode = newtopic || reply
		//

	}
	else if( $mode == "editpost" || $mode == "delete" )
	{
		$sql = "SELECT poster_id
			FROM " . POSTS_TABLE . "
			WHERE post_id = $post_id";
		if($result = $db->sql_query($sql))
		{
			$row = $db->sql_fetchrow($result);

			if( $userdata['user_id'] != $row['poster_id'] && !$is_auth['auth_mod'])
			{
				$message = ( $delete || $mode == "delete" ) ? $lang['Delete_own_posts'] : $lang['Edit_own_posts'];
				$message .="<br /><br />" . sprintf($lang['Click_return_topic'], "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id") . "\">", "</a>");

				message_die(GENERAL_MESSAGE, $message);
			}
		}
		else
		{
			message_die(GENERAL_ERROR, "Couldn't obtain post information", "", __LINE__, __FILE__, $sql);
		}

		//
		// The user has chosen to delete a post or a poll
		//
		if( ( $delete || $mode == "delete" ) && ( ( $is_auth['auth_delete'] && !$is_last_post_topic && !$is_auth['auth_mod'] ) ) )
		{
			$message = $lang['Cannot_delete_replied'] . "<br /><br />" . sprintf($lang['Click_return_topic'], "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);
		}
		else if( ( $delete || $poll_delete || $mode == "delete" ) && ( ( $is_auth['auth_delete'] && $is_last_post_topic ) || $is_auth['auth_mod'] ) )
		{
			//
			// Output a confirmation message if the user
			// chose to delete this post
			//
			if( ( $delete || $poll_delete || $mode == "delete" ) && !$confirm )
			{
				$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" /><input type="hidden" name="' . POST_TOPIC_URL . '" value="'. $topic_id . '" /><input type="hidden" name="' . POST_POST_URL . '" value="' . $post_id . '" />';

				$s_hidden_fields .= ( $delete || $mode == "delete" ) ? '<input type="hidden" name="delete" value="true" />' : '<input type="hidden" name="poll_delete" value="true" />';

				$l_confirm = ( ( $delete || $mode == "delete" ) ? $lang['Confirm_delete'] : $lang['Confirm_delete_poll'] );

				//
				// Output confirmation page
				//
				include($phpbb_root_path . 'includes/page_header.'.$phpEx);

				$template->set_filenames(array(
					"confirm_body" => "confirm_body.tpl")
				);
				$template->assign_vars(array(
					"MESSAGE_TITLE" => $lang['Information'],
					"MESSAGE_TEXT" => $l_confirm,

					"L_YES" => $lang['Yes'],
					"L_NO" => $lang['No'],

					"S_CONFIRM_ACTION" => append_sid("posting.$phpEx"),
					"S_HIDDEN_FIELDS" => $s_hidden_fields)
				);
				$template->pparse("confirm_body");

				include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

			}
			else if( $confirm && ( $delete || $poll_delete || $mode == "delete" ) )
			{
				//
				// Delete poll
				//
				if( $is_first_post_topic && $post_has_poll && ( $can_edit_poll || $is_auth['auth_mod'] ) )
				{
					$sql = "DELETE FROM " . VOTE_USERS_TABLE . " 
						WHERE vote_id = " . $rowset[0]['vote_id'];
					if($db->sql_query($sql, BEGIN_TRANSACTION))
					{
						$sql = "DELETE FROM " . VOTE_RESULTS_TABLE . " 
							WHERE vote_id = " . $rowset[0]['vote_id'];
						if($db->sql_query($sql))
						{
							$sql = "DELETE FROM " . VOTE_DESC_TABLE . " 
								WHERE vote_id = " . $rowset[0]['vote_id'];
							if($db->sql_query($sql, END_TRANSACTION))
							{
								//
								// If we're just deleting the poll then show results
								// and jump back to topic
								//
								if( $poll_delete )
								{
									$sql = "UPDATE " . TOPICS_TABLE . " 
										SET topic_vote = 0 
										WHERE topic_id = $topic_id";
									if($db->sql_query($sql))
									{
										$template->assign_vars(array(
											"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id") . '">')
										);

										$message = $lang['Poll_delete'] . "<br /><br />" . sprintf($lang['Click_return_topic'], "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id") . "\">", "</a>");

										message_die(GENERAL_MESSAGE, $message);
									}
									else
									{
										message_die(GENERAL_ERROR, "Couldn't update topics vote information", "", __LINE__, __FILE__, $sql);
									}
								}
							}
							else
							{
								message_die(GENERAL_ERROR, "Couldn't delete from vote descriptions table", "", __LINE__, __FILE__, $sql);
							}
						}
						else
						{
							message_die(GENERAL_ERROR, "Couldn't delete from vote results table", "", __LINE__, __FILE__, $sql);
						}
					}
					else
					{
						message_die(GENERAL_ERROR, "Couldn't delete from vote users table", "", __LINE__, __FILE__, $sql);
					}
				}
				else if( $post_has_poll && !$can_edit_poll && $poll_delete )
				{
					$message = $lang['Cannot_delete_poll'] . "<br /><br />" . sprintf($lang['Click_return_topic'], "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id") . "\">", "</a>");

					message_die(GENERAL_MESSAGE, $message);
				}
				else if( !$is_first_post_topic && $poll_delete )
				{
					$message = $lang['Post_has_no_poll'] . "<br /><br />" . sprintf($lang['Click_return_topic'], "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id") . "\">", "</a>");

					message_die(GENERAL_MESSAGE, $message);
				}

				if( $delete || $mode == "delete" )
				{
					remove_old_words($post_id);

					$sql = "DELETE FROM " . POSTS_TEXT_TABLE . "
						WHERE post_id = $post_id";
					if($db->sql_query($sql, BEGIN_TRANSACTION))
					{
						$sql = "DELETE FROM " . POSTS_TABLE . "
							WHERE post_id = $post_id";
						if($db->sql_query($sql))
						{
							if( $is_last_post_topic && $is_first_post_topic )
							{
								//
								// Delete the topic completely, updating the forum_last_post_id
								// if necessary and removing any users currently watching this topic
								//
								if( $db->sql_query($sql) )
								{
									$sql = "DELETE FROM " . TOPICS_TABLE . "
										WHERE topic_id = $topic_id";
									if( $db->sql_query($sql) )
									{
										$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . "
											WHERE topic_id = $topic_id";

										$sql_forum_upd = "forum_posts = forum_posts - 1, forum_topics = forum_topics - 1";

										$if_die_msg = "Error deleting from topics watch table";
									}
									else
									{
										message_die(GENERAL_ERROR, "Error deleting from topics table", "", __LINE__, __FILE__, $sql);
									}
								}
								else
								{
									message_die(GENERAL_ERROR, "Error deleting from post  table", "", __LINE__, __FILE__, $sql);
								}
							}
							else if( $is_last_post_topic )
							{
								//
								// Delete the post and update the _last_post_id's of both
								// the topic and forum if necessary
								//
								if($db->sql_query($sql))
								{
									$sql = "SELECT MAX(post_id) AS new_last_post_id
										FROM " . POSTS_TABLE . "
										WHERE topic_id = $topic_id";

									if($result = $db->sql_query($sql))
									{
										$row = $db->sql_fetchrow($result);

										$sql = "UPDATE " . TOPICS_TABLE . "
											SET topic_replies = topic_replies - 1, topic_last_post_id = " . $row['new_last_post_id'] . "
											WHERE topic_id = $topic_id";

										$sql_forum_upd = "forum_posts = forum_posts - 1";

										$if_die_msg = "Error updating topics table";
									}
									else
									{
										message_die(GENERAL_ERROR, "Error obtaining new last topic id", "", __LINE__, __FILE__, $sql);
									}
								}
								else
								{
									message_die(GENERAL_ERROR, "Error deleting from post table", "", __LINE__, __FILE__, $sql);
								}
							}
							else if( $is_auth['auth_mod'] )
							{
								//
								// It's not last and it's not both first and last so it's somewhere in
								// the middle(!) Only moderators can delete these posts, all we need do
								// is update the forums table data as necessary
								//
								$sql = "UPDATE " . TOPICS_TABLE . "
									SET topic_replies = topic_replies - 1 
									WHERE topic_id = $topic_id";

								$sql_forum_upd = "forum_posts = forum_posts - 1";

								$if_die_msg = "Couldn't delete from posts table";
							}

							//
							// Updating the forum is common to all three possibilities,
							// _remember_ we're still in a transaction here!
							//
							if( $db->sql_query($sql) )
							{
								if( $is_last_post_forum )
								{
									$sql = "SELECT MAX(post_id) AS new_post_id
										FROM " . POSTS_TABLE . "
										WHERE forum_id = $forum_id";

									if($result = $db->sql_query($sql))
									{
										$row = $db->sql_fetchrow($result);
									}
									else
									{
										message_die(GENERAL_ERROR, "Couldn't obtain new last post id for the forum", "", __LINE__, __FILE__, $sql);
									}

									$last_post_id_forum = ( !empty($row['new_post_id']) ) ? $row['new_post_id'] : 0;

									$new_last_sql = ", forum_last_post_id = " . $last_post_id_forum;
								}
								else
								{
									$new_last_sql = "";
								}

								$sql = "UPDATE " . FORUMS_TABLE . "
									SET " . $sql_forum_upd . $new_last_sql . "
									WHERE forum_id = $forum_id";

								if($db->sql_query($sql, END_TRANSACTION))
								{
									//
									// If we get here the post has been deleted successfully.
									//
									$message = $lang['Deleted'];

									if( !$is_first_post_topic || !$is_last_post_topic )
									{
										$template->assign_vars(array(
											"META" => '<meta http-equiv="refresh" content="3;url= ' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id") . '">')
										);

										$message .= "<br /><br />" . sprintf($lang['Click_return_topic'], "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id") . "\">", "</a>");

									}
									else
									{
										$template->assign_vars(array(
											"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id") . '">')
										);
									}
									$message .= "<br /><br />" . sprintf($lang['Click_return_forum'], "<a href=\"" . append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id") . "\">", "</a>");

									message_die(GENERAL_MESSAGE, $message);
								}
								else
								{
									message_die(GENERAL_ERROR, "Error updating forums table", "", __LINE__, __FILE__, $sql);
								}
							}
							else
							{
								message_die(GENERAL_ERROR, $if_die_msg, "", __LINE__, __FILE__, $sql);
							}
						}
						else
						{
							message_die(GENERAL_ERROR, "Error deleting from posts table", "", __LINE__, __FILE__, $sql);
						}
					}
					else
					{
						message_die(GENERAL_ERROR, "Error deleting from posts text table", "", __LINE__, __FILE__, $sql);
					}
				}
			}
			else
			{
				header("Location: " . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id", true));
			}
		}
		else
		{
			if( !$is_last_post_topic && ( !$is_auth['auth_mod'] || $row['poster_id'] == $userdata['user_id'] ) )
			{
				$edited_sql = ", post_edit_time = $current_time, post_edit_count = post_edit_count + 1 ";
			}
			else
			{
				$edited_sql = "";
			}

			//
			// Handle changing user notification
			//
			if( isset($notify_user) )
			{
				$sql = "SELECT *
					FROM " . TOPICS_WATCH_TABLE . "
					WHERE topic_id = $topic_id
						AND user_id = " . $userdata['user_id'];
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't obtain topic watch information", "", __LINE__, __FILE__, $sql);
				}

				if( $db->sql_numrows($result) )
				{
					if( !$notify_user )
					{
						$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . "
							WHERE topic_id = $topic_id
								AND user_id = " . $userdata['user_id'];
						if( !$result = $db->sql_query($sql) )
						{
							message_die(GENERAL_ERROR, "Couldn't delete topic watch information", "", __LINE__, __FILE__, $sql);
						}
					}
				}
				else if( $notify_user )
				{
					$sql = "INSERT INTO " . TOPICS_WATCH_TABLE . " (user_id, topic_id, notify_status)
						VALUES (" . $userdata['user_id'] . ", $topic_id, 0)";
					if( !$result = $db->sql_query($sql) )
					{
						message_die(GENERAL_ERROR, "Couldn't insert topic watch information", "", __LINE__, __FILE__, $sql);
					}
				}
			}

			remove_old_words($post_id);

			$sql = "UPDATE " . POSTS_TABLE . "
				SET bbcode_uid = '$bbcode_uid', enable_bbcode = $bbcode_on, enable_html = $html_on, enable_smilies = $smilies_on, enable_sig = $attach_sig" . $edited_sql . "
				WHERE post_id = $post_id";
			if($db->sql_query($sql, BEGIN_TRANSACTION))
			{
				$sql = "UPDATE " . POSTS_TEXT_TABLE . "
					SET post_text = '$post_message', post_subject = '$post_subject'
					WHERE post_id = $post_id";

				if( $is_first_post_topic )
				{
					if( $db->sql_query($sql) )
					{
						add_search_words($post_id, stripslashes($post_message));

						//
						// Update topics table here 
						//
						$sql = "UPDATE " . TOPICS_TABLE . "
							SET topic_title = '$post_subject', topic_type = $topic_type" . $sql_topic_vote_edit . " 
							WHERE topic_id = $topic_id";
						if($db->sql_query($sql, END_TRANSACTION))
						{
							//
							// Update of voting required?
							//
							if( $is_auth['auth_pollcreate'] && $topic_vote  )
							{
								if( $post_has_poll && ( $can_edit_poll || $is_auth['auth_mod'] ) )
								{
									$sql = "SELECT vote_option_id, vote_result  
										FROM " . VOTE_RESULTS_TABLE . " 
										WHERE vote_id = $vote_id 
										ORDER BY vote_option_id ASC";
									if( !$result = $db->sql_query($sql) )
									{
										message_die(GENERAL_ERROR, "Couldn't obtain vote data results for this topic", "", __LINE__, __FILE__, $sql);
									}

									if( $db->sql_numrows($result) )
									{
										$old_poll_result = array();
										while( $row = $db->sql_fetchrow($result) )
										{
											$old_poll_result[$row['vote_option_id']] = $row['vote_result'];
										}

										//
										// Previous entry with no results (or a moderator), update
										//
										$sql = "UPDATE " . VOTE_DESC_TABLE . " 
											SET vote_text = '$poll_title', vote_length = " . ( $poll_length * 86400 ) . " 
											WHERE topic_id = $topic_id";
										if( $result = $db->sql_query($sql, BEGIN_TRANSACTION) )
										{
											$sql = "DELETE FROM " . VOTE_RESULTS_TABLE . " 
												WHERE vote_id = $vote_id";
											if( $result = $db->sql_query($sql) )
											{
												$poll_option_id = 1;
												while( list($option_id, $option_text) = each($poll_option_list) )
												{
													$vote_result = ( $old_poll_result[$option_id] ) ? $old_poll_result[$option_id] : 0;

													$sql = "INSERT INTO " . VOTE_RESULTS_TABLE . " (vote_id, vote_option_id, vote_option_text, vote_result)
														VALUES ($vote_id, $poll_option_id, '$option_text', $vote_result)";
													if( !$result = $db->sql_query($sql) )
													{
														message_die(GENERAL_ERROR, "Couldn't insert new poll options", "", __LINE__, __FILE__, $sql);
													}
													$poll_option_id++;
												}
											}
											else
											{
												message_die(GENERAL_ERROR, "Couldn't delete existing options", "", __LINE__, __FILE__, $sql);
											}
										}
									}
									else
									{
										message_die(GENERAL_ERROR, "Failed to obtain row set for this poll", "", __LINE__, __FILE__, $sql);
									}
								}
								else
								{
									//
									// No previous entry, create new
									//
									$sql = "INSERT INTO " . VOTE_DESC_TABLE . " (topic_id, vote_text, vote_start, vote_length) 
										VALUES ($topic_id, '$poll_title', $current_time, " . ( $poll_length * 86400 ) . ")";
									if( $result = $db->sql_query($sql, BEGIN_TRANSACTION) )
									{
										$new_vote_id = $db->sql_nextid();

										$poll_option_id = 1;
										while( list($option_id, $option_text) = each($poll_option_list) )
										{
											$sql = "INSERT INTO " . VOTE_RESULTS_TABLE . " (vote_id, vote_option_id, vote_option_text, vote_result)
												VALUES ($new_vote_id, $poll_option_id, '$option_text', 0)";
											if( !$result = $db->sql_query($sql) )
											{
												// Rollback ...
												if(SQL_LAYER == "mysql")
												{
													$sql_del_v = "DELETE FROM " . VOTE_DESC_TABLE . " 
														WHERE vote_id = $new_vote_id";
													$db->sql_query($sql_del_v);
												}
												message_die(GENERAL_ERROR, "Couldn't insert new poll options", "", __LINE__, __FILE__, $sql);
											}
											$poll_option_id++;
										}
									}
								}
							}

							//
							// If we get here the post has been inserted successfully.
							//
							$template->assign_vars(array(
								"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("viewtopic.$phpEx?" . POST_POST_URL . "=$post_id") . '#' . $post_id . '">')
							);

							$message = $lang['Stored'] . "<br /><br />" . sprintf($lang['Click_view_message'], "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_POST_URL . "=$post_id") . "#$post_id\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_topic'], "<a href=\"" . append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id") . "\">", "</a>");

							message_die(GENERAL_MESSAGE, $message);
						}
						else
						{
							message_die(GENERAL_ERROR, "Updating topics table", "", __LINE__, __FILE__, $sql);
						}
					}
				}
				else
				{
					remove_old_words($post_id);
					add_search_words($post_id, stripslashes($post_message));

					if( $db->sql_query($sql, END_TRANSACTION) )
					{
						//
						// If we get here the post has been inserted successfully.
						//
						$template->assign_vars(array(
							"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("viewtopic.$phpEx?" . POST_POST_URL . "=$post_id") . '#' . $post_id . '">')
						);

						$message = $lang['Stored'] . "<br /><br />" . sprintf($lang['Click_view_message'], "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_POST_URL . "=$post_id") . "#$post_id\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_topic'], "<a href=\"" . append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id") . "\">", "</a>");

						message_die(GENERAL_MESSAGE, $message);
					}
					else
					{
						message_die(GENERAL_ERROR, "Error updating posts text table", "", __LINE__, __FILE__, $sql);
					}
				}
			}
			else
			{
				message_die(GENERAL_ERROR, "Error updating posts text table", "", __LINE__, __FILE__, $sql);
			}
		}
		//
		// End of mode = editpost
		//
	}
	else if( $mode == "vote" )
	{

		if( !empty($HTTP_POST_VARS['vote_id']) )
		{
			$vote_option_id = $HTTP_POST_VARS['vote_id'];

			$sql = "SELECT vd.vote_id, MAX(vr.vote_option_id) AS max_vote_option   
				FROM " . VOTE_DESC_TABLE . " vd, " . VOTE_RESULTS_TABLE . " vr
				WHERE vd.topic_id = $topic_id 
					AND vr.vote_id = vd.vote_id 
				GROUP BY vd.vote_id";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't obtain vote data for this topic", "", __LINE__, __FILE__, $sql);
			}

			if( $vote_options = $db->sql_numrows($result) )
			{
				$vote_info = $db->sql_fetchrow($result);

				if( $vote_info['max_vote_option'] < $vote_option_id )
				{
					$template->assign_vars(array(
						"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "= $topic_id") . '">')
					);

					$message = $lang['No_vote_option'] . "<br /><br />" . sprintf($lang['Click_view_message'], "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id") . "\">", "</a>");

					message_die(GENERAL_MESSAGE, $message);
				}

				$vote_id = $vote_info['vote_id'];

				$sql = "SELECT * 
					FROM " . VOTE_USERS_TABLE . "  
					WHERE vote_id = $vote_id 
						AND vote_user_id = " . $userdata['user_id'];
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't obtain user vote data for this topic", "", __LINE__, __FILE__, $sql);
				}

				$user_voted = ( $db->sql_numrows($result) ) ? TRUE : 0;

				if( !$user_voted )
				{
					$sql = "UPDATE " . VOTE_RESULTS_TABLE . " 
						SET vote_result = vote_result + 1 
						WHERE vote_id = $vote_id 
							AND vote_option_id = $vote_option_id";
					if( $db->sql_query($sql, BEGIN_TRANSACTION) )
					{
						$sql = "INSERT INTO " . VOTE_USERS_TABLE . " (vote_id, vote_user_id, vote_user_ip) 
							VALUES ($vote_id, " . $userdata['user_id'] . ", '$user_ip')";
						if( $db->sql_query($sql, END_TRANSACTION) )
						{

							$template->assign_vars(array(
								"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id") . '">')
							);

							$message = $lang['Vote_cast'] . "<br /><br />" . sprintf($lang['Click_view_message'], "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id") . "\">", "</a>");

							message_die(GENERAL_MESSAGE, $message);
						}
						else
						{
							if(SQL_LAYER == "mysql")
							{
								$sql_rewind = "UPDATE " . VOTE_RESULTS_TABLE . " 
									SET vote_option_result = vote_option_result - 1 
									WHERE vote_id = $vote_id 
										AND vote_option_id = $vote_option_id";
								$db->sql_query($sql_rewind);
							}

							message_die(GENERAL_ERROR, "Error updating vote users table", "", __LINE__, __FILE__, $sql);
						}
					}
					else
					{
						message_die(GENERAL_ERROR, "Error updating vote results table", "", __LINE__, __FILE__, $sql);
					}
				}
				else
				{
					$template->assign_vars(array(
						"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id") . '">')
					);

					$message = $lang['Already_voted'] . "<br /><br />" . sprintf($lang['Click_view_message'], "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id") . "\">", "</a>");

					message_die(GENERAL_MESSAGE, $message);
				}
			}
		}
		else
		{
			$message = $lang['No_vote_option'] . "<br /><br />" . sprintf($lang['Click_view_message'], "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);
		}
		//
		// End of mode = vote
		//
	}

}
else if( $preview || $refresh || $error )
{

	//
	// If we're previewing or refreshing then obtain the data
	// passed to the script, process it a little, do some checks
	// where neccessary, etc.
	//
	$post_username = ( isset($HTTP_POST_VARS['username']) ) ? trim(strip_tags(stripslashes($HTTP_POST_VARS['username']))) : "";
	$post_subject = ( isset($HTTP_POST_VARS['subject']) ) ? trim(strip_tags(stripslashes($HTTP_POST_VARS['subject']))) : "";
	$post_message = ( isset($HTTP_POST_VARS['message']) ) ? trim(stripslashes($HTTP_POST_VARS['message'])) : "";
	$post_message = preg_replace('#<textarea>#si', '&lt;textarea&gt;', $post_message);

	$poll_title = ( isset($HTTP_POST_VARS['poll_title']) ) ? trim(strip_tags(stripslashes($HTTP_POST_VARS['poll_title']))) : "";
	$poll_length = ( isset($HTTP_POST_VARS['poll_length']) ) ? intval($HTTP_POST_VARS['poll_length']) : 0;

	$poll_options = 0;
	$poll_option_list = array();
	if( isset($HTTP_POST_VARS['del_poll_option']) )
	{
		if( isset($HTTP_POST_VARS['poll_option_text']) )
		{
			while( list($option_id, $option_text) = each($HTTP_POST_VARS['poll_option_text']) )
			{
				if( !isset($HTTP_POST_VARS['del_poll_option'][$option_id]) )
				{
					$poll_option_list[$option_id] = trim(strip_tags(stripslashes($option_text)));
					$poll_options++;
				}
			}
		}
	}
	else 
	{
		if( isset($HTTP_POST_VARS['poll_option_text']) )
		{
			@reset($HTTP_POST_VARS['poll_option_text']);
			while( list($option_id, $option_text) = each($HTTP_POST_VARS['poll_option_text']) )
			{
				$poll_option_list[$option_id] = trim(strip_tags(preg_replace($html_entities_match, $html_entities_replace, stripslashes($option_text))));
				$poll_options++;
			}
		}

		if( isset($HTTP_POST_VARS['add_poll_option']) )
		{
			if( $poll_options < $board_config['max_poll_options'] ) 
			{
				$new_poll_option = trim(strip_tags(stripslashes($HTTP_POST_VARS['add_poll_option_text'])));

				if($new_poll_option != "")
				{
					$poll_option_list[] = $new_poll_option;
				}
			}
			else
			{
				$error = TRUE;
				if(!empty($error_msg))
				{
					$error_msg .= "<br />";
				}
				$error_msg .= $lang['To_many_poll_options']; 
			}
		}
	}

	//
	// Do mode specific things
	//
	if( $mode == "newtopic" )
	{

		$page_title = $lang['Post_a_new_topic'];
		$display_poll = ( $is_auth['auth_pollcreate'] ) ? TRUE : 0;

		$user_sig = ( $userdata['user_sig'] != "" ) ? $userdata['user_sig'] : "";

	}
	else if( $mode == "reply" )
	{

		$page_title = $lang['Post_a_reply'];
		$display_poll = 0;

		$user_sig = ( $userdata['user_sig'] != "" ) ? $userdata['user_sig'] : "";

	}
	else if( $mode == "editpost" )
	{
		$page_title = $lang['Edit_Post'];

		$sql = "SELECT u.user_id, u.user_sig 
			FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u 
			WHERE p.post_id = $post_id
				AND u.user_id = p.poster_id";
		if($result = $db->sql_query($sql))
		{
			$postrow = $db->sql_fetchrow($result);

			if($userdata['user_id'] != $postrow['user_id'] && !$is_auth['auth_mod'])
			{
				message_die(GENERAL_MESSAGE, $lang['Sorry_edit_own_posts']);
			}

			$user_sig = ( $postrow['user_sig'] != "" ) ? $postrow['user_sig'] : "";
		}
		else
		{
			message_die(GENERAL_ERROR, "Couldn't obtain post and post text", "", __LINE__, __FILE__, $sql);
		}

		if( $is_auth['auth_pollcreate'] && $is_first_post_topic )
		{
			$display_poll = ( !$post_has_poll || ( $post_has_poll && ( $is_auth['auth_mod'] || $can_edit_poll ) ) ) ? TRUE : 0;
		}
		else
		{
			$display_poll = 0;
		}
	}
}
else
{
	//
	// This is the entry point for posting, some basic variables
	// are set, for editpost/quote the original message is obtained
	// and for editpost a check is done to ensure the user isn't
	// trying to edit someone elses post ( additional checks on polling
	// capability are also carried out )
	//

	if( $mode == "newtopic" )
	{

		$page_title = $lang['Post_a_new_topic'];

		$display_poll = ( $is_auth['auth_pollcreate'] ) ? TRUE : 0;
		$poll_title = "";
		$poll_length = 0;

		$user_sig = ( $userdata['user_sig'] != "" ) ? $userdata['user_sig'] : "";

		$post_username = ($userdata['session_logged_in']) ? $userdata['username'] : "";
		$post_subject = "";
		$post_message = "";

	}
	else if( $mode == "reply" )
	{

		$page_title = $lang['Post_a_reply'];

		$display_poll = 0;
		$poll_title = "";
		$poll_length = 0;

		$user_sig = ( $userdata['user_sig'] != "" ) ? $userdata['user_sig'] : "";

		$post_username = ($userdata['session_logged_in']) ? $userdata['username'] : "";
		$post_subject = "";
		$post_message = "";

	}
	else if( $mode == "editpost" || $mode == "quote" && ( !$preview && !$refresh ) )
	{

		$sql = "SELECT p.*, pt.post_text, pt.post_subject, u.username, u.user_id, u.user_sig, t.topic_title, t.topic_type, t.topic_vote
			FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . TOPICS_TABLE . " t, " . POSTS_TEXT_TABLE . " pt
			WHERE p.post_id = $post_id
				AND pt.post_id = p.post_id
				AND p.topic_id = t.topic_id
				AND p.poster_id = u.user_id";
		if($result = $db->sql_query($sql))
		{
			$postrow = $db->sql_fetchrow($result);

			if( $mode == "editpost" )
			{
				if($userdata['user_id'] != $postrow['user_id'] && !$is_auth['auth_mod'])
				{
					message_die(GENERAL_MESSAGE, $lang['Sorry_edit_own_posts']);
				}
			}

			$post_user_id = $postrow['user_id'];
			$post_username = ( $post_user_id == ANONYMOUS && $postrow['post_username'] != "") ? $postrow['post_username'] : $postrow['username'];
			$post_subject = $postrow['post_subject'];
			$post_message = $postrow['post_text'];
			$post_bbcode_uid = $postrow['bbcode_uid'];

			if( $mode == "editpost" )
			{
				$attach_sig = ( $postrow['enable_sig'] && $postrow['user_sig'] != "" ) ? TRUE : 0; 
				$user_sig = $postrow['user_sig'];
			}
			else
			{
				$attach_sig = ( $userdata['user_attachsig'] ) ? TRUE : 0;
				$user_sig = $userdata['user_sig'];
			}

			$post_message = preg_replace("/\:$post_bbcode_uid(|\:[a-z])/si", "", $post_message);
			$post_message = str_replace("<br />", "\n", $post_message);
			$post_message = preg_replace($html_entities_match, $html_entities_replace, $post_message);
			$post_message = preg_replace('#</textarea>#si', '&lt;/textarea&gt;', $post_message);

			//
			// Finish off edit/quote grab by doing specific
			// things for each mode
			//
			if( $mode == "quote" )
			{
				$page_title = $lang['Post_a_reply'];

				$msg_date =  create_date($board_config['default_dateformat'], $postrow['post_time'], $board_config['board_timezone']);

				$post_message = "[quote=" . $post_username . "]\n" . $post_message . "\n[/quote]";

				$mode = "reply";
			}
			else if( $mode == "editpost" ) 
			{
				$page_title = $lang['Edit_Post'];

				$html_on = ( $postrow['enable_html'] && $board_config['allow_html'] ) ? TRUE : 0;
				$bbcode_on = ( $postrow['enable_bbcode'] && $board_config['allow_bbcode'] ) ? TRUE : 0;
				$smilies_on = ( $postrow['enable_smilies'] && $board_config['allow_smilies'] ) ? TRUE : 0;

				if( $is_first_post_topic )
				{
					$post_subject = $postrow['topic_title'];
					$topic_type = $postrow['topic_type'];

					if( $is_auth['auth_pollcreate'] && ( $can_edit_poll || $is_auth['auth_mod'] ) )
					{
						$sql = "SELECT vd.vote_text, vd.vote_length, vr.vote_option_id, vr.vote_option_text  
							FROM " . VOTE_DESC_TABLE . " vd, " . VOTE_RESULTS_TABLE . " vr 
							WHERE vd.topic_id = $topic_id 
								AND vr.vote_id = vd.vote_id 
							ORDER BY vr.vote_option_id ASC";
						if( !$result = $db->sql_query($sql) )
						{
							message_die(GENERAL_ERROR, "Couldn't obtain vote data for this topic", "", __LINE__, __FILE__, $sql);
						}

						$vote_results_sum = 0;
						if( $row = $db->sql_fetchrow($result) )
						{
							$poll_title = $row['vote_text'];
							$poll_length = $row['vote_length'];

							$poll_option_list[$row['vote_option_id']] = $row['vote_option_text'];
							while( $row = $db->sql_fetchrow($result) )
							{
								$poll_option_list[$row['vote_option_id']] = $row['vote_option_text'];
							}
						}
						$poll_length = $poll_length / 86400;

						$display_poll = TRUE;
					}
					else
					{
						$display_poll = ( $is_auth['auth_pollcreate'] && !$post_has_poll ) ? TRUE : 0;
						$poll_length = 0;
					}
				}
				else
				{
					$display_poll = 0;
				}
			}
			else
			{
				message_die(GENERAL_ERROR, "Couldn't obtain post and post text", "", __LINE__, __FILE__, $sql);
			}
		}
	}
	
}
//
// Major posting code complete
// ---------------------------


// --------------------
// Generate page output
//

//
// Include page header
//
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	"body" => "posting_body.tpl", 
	"pollbody" => "posting_poll_body.tpl", 
	"jumpbox" => "jumpbox.tpl", 
	"reviewbody" => "posting_topic_review.tpl")
);

$jumpbox = make_jumpbox();
$template->assign_vars(array(
	"L_GO" => $lang['Go'],
	"L_JUMP_TO" => $lang['Jump_to'],
	"L_SELECT_FORUM" => $lang['Select_forum'],
	
	"S_JUMPBOX_LIST" => $jumpbox,
	"S_JUMPBOX_ACTION" => append_sid("viewforum.$phpEx"))
);
$template->assign_var_from_handle("JUMPBOX", "jumpbox");

$template->assign_vars(array(
	"FORUM_NAME" => $forum_name,
	"L_POST_A" => $page_title,

	"U_VIEW_FORUM" => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id"))
);

//
// Output preview of post if requested
//
if( $preview && !$error )
{

	$orig_word = array();
	$replacement_word = array();
	$result = obtain_word_list($orig_word, $replacement_word);

	if( $bbcode_on )
	{
		$bbcode_uid = make_bbcode_uid();
	}

	$preview_subject = $post_subject;
	$preview_message = prepare_message($post_message, $html_on, $bbcode_on, $smilies_on, $bbcode_uid);

	//
	// Finalise processing as per viewtopic
	//
	if( !$html_on )
	{
		if( $user_sig != "" || !$userdata['user_allowhtml'] )
		{
			$user_sig = preg_replace("#(<)([\/]?.*?)(>)#is", "&lt;\\2&gt;", $user_sig);
		}
	}

	if( $attach_sig && $user_sig != "" && $userdata['user_sig_bbcode_uid'] )
	{
		$user_sig = bbencode_second_pass($user_sig, $userdata['user_sig_bbcode_uid']);
	}

	if( $bbcode_on )
	{
		$preview_message = bbencode_second_pass($preview_message, $bbcode_uid);
	}

	if( $attach_sig && $user_sig != "" )
	{
		$preview_message = $preview_message . "<br /><br />_________________<br />" . $user_sig;
	}

	if( count($orig_word) )
	{
		$preview_subject = preg_replace($orig_word, $replacement_word, $preview_subject);
		$preview_message = preg_replace($orig_word, $replacement_word, $preview_message);
	}

	if( $smilies_on )
	{
		$preview_message = smilies_pass($preview_message);
	}

	$preview_message = make_clickable($preview_message);
	$preview_message = str_replace("\n", "<br />", $preview_message);

	$template->set_filenames(array(
		"preview" => "posting_preview.tpl")
	);
	$template->assign_vars(array(
		"TOPIC_TITLE" => $preview_subject,
		"POST_SUBJECT" => $preview_subject,
		"POSTER_NAME" => $username,
		"POST_DATE" => create_date($board_config['default_dateformat'], time(), $board_config['board_timezone']),
		"MESSAGE" => $preview_message,

		"L_PREVIEW" => $lang['Preview'],
		"L_POSTED" => $lang['Posted'])
	);
	$template->assign_var_from_handle("POST_PREVIEW_BOX", "preview");
}
//
// End preview output
//

//
// Start Error handling
//
if( $error )
{
	$template->set_filenames(array(
		"reg_header" => "error_body.tpl")
	);
	$template->assign_vars(array(
		"ERROR_MESSAGE" => $error_msg)
	);
	$template->assign_var_from_handle("ERROR_BOX", "reg_header");
}
//
// End error handling
//

//
// User not logged in so offer up a username
// field box
//
if( !$userdata['session_logged_in'] || ( $mode == "editpost" && $post_user_id == ANONYMOUS ) )
{
	$template->assign_block_vars("username_select", array());
}

//
// HTML toggle selection
//
if( $board_config['allow_html'] )
{
	$html_status = $lang['ON'];
	$template->assign_block_vars("html_checkbox", array());
}
else
{
	$html_status = $lang['OFF'];
}

//
// BBCode toggle selection
//
if($board_config['allow_bbcode'])
{
	$bbcode_status = $lang['ON'];
	$template->assign_block_vars("bbcode_checkbox", array());
}
else
{
	$bbcode_status = $lang['OFF'];
}

//
// Smilies toggle selection
//
if($board_config['allow_smilies'])
{
	$smilies_status = $lang['ON'];
	$template->assign_block_vars("smilies_checkbox", array());
}
else
{
	$smilies_status = $lang['OFF'];
}

//
// Signature toggle selection - only show if
// the user has a signature
//
if( $user_sig != "" )
{
	$template->assign_block_vars("signature_checkbox", array());
}

//
// Notify checkbox - only show if user is logged in
//
if( $userdata['session_logged_in'] )
{
	if( $mode != "editpost" || ( $mode == "editpost" && $post_user_id != ANONYMOUS ) )
	{
		$template->assign_block_vars("notify_checkbox", array());
	}
}

//
// Delete selection
//
if( $mode == 'editpost' && ( ( $is_auth['auth_delete'] && $is_last_post_topic && ( !$post_has_poll || $can_edit_poll ) ) || $is_auth['auth_mod'] ) )
{
	$template->assign_block_vars("delete_checkbox", array());
}

//
// Topic type selection
//
$topic_type_radio = '';
if( $mode == 'newtopic' || ( $mode == 'editpost' && $is_first_post_topic ) )
{
	$template->assign_block_vars("type_toggle", array());

	if( $is_auth['auth_announce'] )
	{
		$announce_toggle = '<input type="radio" name="topictype" value="announce"';
		if( $topic_type == POST_ANNOUNCE )
		{
			$announce_toggle .= ' checked="checked"';
		}
		$announce_toggle .= ' /> ' . $lang['Post_Announcement'] . '&nbsp;&nbsp;';
	}

	if( $is_auth['auth_sticky'] )
	{
		$sticky_toggle = '<input type="radio" name="topictype" value="sticky"';
		if( $topic_type == POST_STICKY )
		{
			$sticky_toggle .= ' checked="checked"';
		}
		$sticky_toggle .= ' /> ' . $lang['Post_Sticky'] . '&nbsp;&nbsp;';
	}

	if( $is_auth['auth_announce'] || $is_auth['auth_sticky'] )
	{
		$topic_type_toggle = $lang['Post_topic_as'] . ': <input type="radio" name="topictype" value="normal"';
		if( $topic_type == POST_NORMAL )
		{
			$topic_type_toggle .= ' checked="checked"';
		}
		$topic_type_toggle .= ' /> ' . $lang['Post_Normal'] . '&nbsp;&nbsp;' . $sticky_toggle . $announce_toggle;
	}
}

//
// Define hidden fields
//
$hidden_form_fields = '<input type="hidden" name="mode" value="' . $mode . '" />';

switch($mode)
{
	case 'newtopic':
		$hidden_form_fields .= '<input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '" />';
		break;

	case 'reply':
		$hidden_form_fields .= '<input type="hidden" name="' . POST_TOPIC_URL . '" value="' . $topic_id . '" />';
		break;

	case 'editpost':
		$hidden_form_fields .= '<input type="hidden" name="' . POST_POST_URL . '" value="' . $post_id . '" />';
		break;
}

//
// Output the data to the template
//
$template->assign_vars(array(
	"USERNAME" => preg_replace($html_entities_match, $html_entities_replace, $post_username),
	"SUBJECT" => preg_replace($html_entities_match, $html_entities_replace, $post_subject),
	"MESSAGE" => $post_message,
	"HTML_STATUS" => $html_status,
	"BBCODE_STATUS" => $bbcode_status,
	"SMILIES_STATUS" => $smilies_status, 
	"POLL_TITLE" => preg_replace($html_entities_match, $html_entities_replace, $poll_title),
	"POLL_LENGTH" => $poll_length, 

	"L_SUBJECT" => $lang['Subject'],
	"L_MESSAGE_BODY" => $lang['Message_body'],
	"L_OPTIONS" => $lang['Options'],
	"L_PREVIEW" => $lang['Preview'],
	"L_SUBMIT" => $lang['Submit_post'],
	"L_CANCEL" => $lang['Cancel_post'],
	"L_CONFIRM_DELETE" => $lang['Confirm_delete'],
	"L_HTML_IS" => $lang['HTML'] . " " . $lang['is'],
	"L_BBCODE_IS" => $lang['BBCode'] . " " . $lang['is'],
	"L_SMILIES_ARE" => $lang['Smilies'] . " " . $lang['are'],
	"L_DISABLE_HTML" => $lang['Disable'] . $lang['HTML'] . $lang['in_this_post'], 
	"L_DISABLE_BBCODE" => $lang['Disable'] . $lang['BBCode'] . $lang['in_this_post'], 
	"L_DISABLE_SMILIES" => $lang['Disable'] . $lang['Smilies'] . $lang['in_this_post'], 
	"L_ATTACH_SIGNATURE" => $lang['Attach_signature'], 
	"L_NOTIFY_ON_REPLY" => $lang['Notify'], 
	"L_DELETE_POST" => $lang['Delete_post'],

	"U_VIEWTOPIC" => ( $mode == "reply" ) ? append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;postorder=desc") : "", 
	"U_REVIEW_TOPIC" => ( $mode == "reply" ) ? append_sid("posting.$phpEx?mode=topicreview&amp;" . POST_TOPIC_URL . "=$topic_id") : "", 

	"S_HTML_CHECKED" => (!$html_on) ? "checked=\"checked\"" : "", 
	"S_BBCODE_CHECKED" => (!$bbcode_on) ? "checked=\"checked\"" : "", 
	"S_SMILIES_CHECKED" => (!$smilies_on) ? "checked=\"checked\"" : "", 
	"S_SIGNATURE_CHECKED" => ($attach_sig) ? "checked=\"checked\"" : "", 
	"S_NOTIFY_CHECKED" => ($notify_user) ? "checked=\"checked\"" : "", 
	"S_TYPE_TOGGLE" => $topic_type_toggle, 
	"S_TOPIC_ID" => $topic_id, 
	"S_POST_ACTION" => append_sid("posting.$phpEx"),
	"S_HIDDEN_FORM_FIELDS" => $hidden_form_fields)
);

//
// Poll entry switch/output
//
if( $display_poll )
{
	$template->assign_vars(array(
		"L_ADD_A_POLL" => $lang['Add_poll'],  
		"L_ADD_POLL_EXPLAIN" => $lang['Add_poll_explain'],   
		"L_POLL_QUESTION" => $lang['Poll_question'],   
		"L_POLL_OPTION" => $lang['Poll_option'],  
		"L_ADD_OPTION" => $lang['Add_option'],
		"L_UPDATE_OPTION" => $lang['Update'],
		"L_DELETE_OPTION" => $lang['Delete'], 
		"L_POLL_LENGTH" => $lang['Poll_for'],  
		"L_DAYS" => $lang['Days'], 
		"L_POLL_LENGTH_EXPLAIN" => $lang['Poll_for_explain'], 
		"L_POLL_DELETE" => $lang['Delete_poll'],
		
		"POLL_LENGTH" => $poll_length)
	);

	if( $mode == "editpost" && ( $can_edit_poll || $is_auth['auth_mod'] ) && $post_has_poll )
	{
		$template->assign_block_vars("poll_delete_toggle", array());
	}

	if( is_array($poll_option_list) )
	{
		while( list($option_id, $option_text) = each($poll_option_list) )
		{
			$template->assign_block_vars("poll_option_rows", array(
				"POLL_OPTION" => preg_replace($html_entities_match, $html_entities_replace, $option_text), 

				"S_POLL_OPTION_NUM" => $option_id)
			);
		}
	}

	$template->assign_var_from_handle("POLLBOX", "pollbody");

}

//
// Topic review
//
if( $mode == "reply" )
{
	topic_review($topic_id, true);

	//
	// Enable inline mode ...
	//
	$template->assign_block_vars("switch_inline_mode", array());
	$template->assign_var_from_handle("TOPIC_REVIEW_BOX", "reviewbody");
}

//
// Parse and print the body
//
$template->pparse("body");

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>