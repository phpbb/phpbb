<?php
/***************************************************************************
 *                                search.php
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

define('IN_PHPBB', true);
$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/bbcode.'.$phpEx);
include($phpbb_root_path . 'includes/functions_posting.'.$phpEx);

//
// Start session management
//
$userdata = $session->start();
$acl = new auth('read', $userdata);
//
// End session management
//

$session->configure($userdata);

//
// Define initial vars
//
if ( isset($HTTP_POST_VARS['mode']) || isset($HTTP_GET_VARS['mode']) )
{
	$mode = ( isset($HTTP_POST_VARS['mode']) ) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
}
else
{
	$mode = '';
}

if ( isset($HTTP_POST_VARS['search_keywords']) || isset($HTTP_GET_VARS['search_keywords']) )
{
	$search_keywords = ( isset($HTTP_POST_VARS['search_keywords']) ) ? $HTTP_POST_VARS['search_keywords'] : $HTTP_GET_VARS['search_keywords'];
}
else
{
	$search_keywords = '';
}

if ( isset($HTTP_POST_VARS['search_author']) || isset($HTTP_GET_VARS['search_author']))
{
	$search_author = ( isset($HTTP_POST_VARS['search_author']) ) ? $HTTP_POST_VARS['search_author'] : $HTTP_GET_VARS['search_author'];
}
else
{
	$search_author = '';
}

$search_id = ( isset($HTTP_GET_VARS['search_id']) ) ? $HTTP_GET_VARS['search_id'] : '';

$show_results = ( isset($HTTP_POST_VARS['show_results']) ) ? $HTTP_POST_VARS['show_results'] : 'posts';

if ( isset($HTTP_POST_VARS['search_terms']) )
{
	$search_terms = ( $HTTP_POST_VARS['search_terms'] == 'all' ) ? 1 : 0;
}
else
{
	$search_terms = 0;
}

if ( isset($HTTP_POST_VARS['search_fields']) )
{
	$search_fields = ( $HTTP_POST_VARS['search_fields'] == 'all' ) ? 1 : 0;
}
else
{
	$search_fields = 0;
}

$return_chars = ( isset($HTTP_POST_VARS['return_chars']) ) ? intval($HTTP_POST_VARS['return_chars']) : 200;

$search_cat = ( isset($HTTP_POST_VARS['search_cat']) ) ? intval($HTTP_POST_VARS['search_cat']) : -1;
$search_forum = ( isset($HTTP_POST_VARS['search_forum']) ) ? intval($HTTP_POST_VARS['search_forum']) : -1;

$sort_by = ( isset($HTTP_POST_VARS['sort_by']) ) ? intval($HTTP_POST_VARS['sort_by']) : 0;

if ( isset($HTTP_POST_VARS['sort_dir']) )
{
	$sort_dir = ( $HTTP_POST_VARS['sort_dir'] == 'DESC' ) ? 'DESC' : 'ASC';
}
else
{
	$sort_dir =  'DESC';
}

if ( !empty($HTTP_POST_VARS['search_time']) || !empty($HTTP_GET_VARS['search_time']))
{
	$search_time = time() - ( ( ( !empty($HTTP_POST_VARS['search_time']) ) ? intval($HTTP_POST_VARS['search_time']) : intval($HTTP_GET_VARS['search_time']) ) * 86400 );
}
else
{
	$search_time = 0;
}

$start = ( isset($HTTP_GET_VARS['start']) ) ? intval($HTTP_GET_VARS['start']) : 0;

$sort_by_types = array($lang['Sort_Time'], $lang['Sort_Post_Subject'], $lang['Sort_Topic_Title'], $lang['Sort_Author'], $lang['Sort_Forum']);

//
// Begin core code
//
if ( $mode == 'searchuser' )
{
	//
	// This handles the simple windowed user search functions called from various other scripts
	//
	username_search();

	exit;
}
else if ( $search_keywords != '' || $search_author != '' || $search_id )
{
	$store_vars = array('search_results', 'total_match_count', 'split_search', 'sort_by', 'sort_dir', 'show_results', 'return_chars');

	//
	// Cycle through options ...
	//
	if ( $search_id == 'newposts' || $search_id == 'egosearch' || $search_id == 'unanswered' || $search_keywords != '' || $search_author != '' )
	{
		if ( $search_id == 'newposts' || $search_id == 'egosearch' || ( $search_author != '' && $search_keywords == '' )  )
		{
			if ( $search_id == 'newposts' )
			{
				if ( $userdata['session_logged_in'] )
				{
					$sql = "SELECT post_id 
						FROM " . POSTS_TABLE . " 
						WHERE post_time >= " . $userdata['user_lastvisit'];
				}
				else
				{
					header("Location: login.$phpEx?redirect=search.$phpEx&search_id=newposts", true);
					exit;
				}

				$show_results = 'topics';
				$sort_by = 0;
				$sort_dir = 'DESC';
			}
			else if ( $search_id == 'egosearch' )
			{
				if ( $userdata['session_logged_in'] )
				{
					$sql = "SELECT post_id 
						FROM " . POSTS_TABLE . " 
						WHERE poster_id = " . $userdata['user_id'];;
				}
				else
				{
					header("Location: login.$phpEx?redirect=search.$phpEx&search_id=egosearch", true);
					exit;
				}

				$show_results = 'topics';
				$sort_by = 0;
				$sort_dir = 'DESC';
			}
			else
			{
				$search_author = str_replace('*', '%', trim($search_author));
				
				$sql = "SELECT user_id
					FROM " . USERS_TABLE . "
					WHERE username LIKE '" . str_replace("\'", "''", $search_author) . "'";
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(ERROR, "Couldn't obtain list of matching users (searching for: $search_author)", "", __LINE__, __FILE__, $sql);
				}

				$matching_userids = '';
				if ( $row = $db->sql_fetchrow($result) )
				{
					do
					{
						$matching_userids .= ( ( $matching_userids != '' ) ? ', ' : '' ) . $row['user_id'];
					}
					while( $row = $db->sql_fetchrow($result) );
				}
				else
				{
					message_die(MESSAGE, $lang['No_search_match']);
				}

				$sql = "SELECT post_id 
					FROM " . POSTS_TABLE . " 
					WHERE poster_id IN ($matching_userids)";
			}

			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(ERROR, 'Could not obtain matched posts list', '', __LINE__, __FILE__, $sql);
			}

			$search_ids = array();
			while( $row = $db->sql_fetchrow($result) )
			{
				$search_ids[] = $row['post_id'];
			}
			$db->sql_freeresult($result);

			$total_match_count = count($search_ids);

		}
		else if ( $search_keywords != '' )
		{
			$stopword_array = @file($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/search_stopwords.txt'); 
			$synonym_array = @file($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/search_synonyms.txt'); 
		
			$split_search = array();
			$cleaned_search = clean_words('search', stripslashes($search_keywords), $stopword_array, $synonym_array);
			$split_search = split_words($cleaned_search, 'search');

			$search_msg_only = ( !$search_fields ) ? "AND m.title_match = 0" : '';

			$word_count = 0;
			$current_match_type = 'or';

			$word_match = array();
			$result_list = array();

			for($i = 0; $i < count($split_search); $i++)
			{
				switch ( $split_search[$i] )
				{
					case 'and':
						$current_match_type = 'and';
						break;

					case 'or':
						$current_match_type = 'or';
						break;

					case 'not':
						$current_match_type = 'not';
						break;

					default:
						if ( !empty($search_terms) )
						{
							$current_match_type = 'and';
						}

						$match_word = str_replace('*', '%', $split_search[$i]);

						$sql = "SELECT m.post_id 
							FROM " . SEARCH_WORD_TABLE . " w, " . SEARCH_MATCH_TABLE . " m 
							WHERE w.word_text LIKE '$match_word' 
								AND m.word_id = w.word_id 
								AND w.word_common <> 1 
								$search_msg_only";
						if ( !($result = $db->sql_query($sql)) )
						{
							message_die(ERROR, 'Could not obtain matched posts list', '', __LINE__, __FILE__, $sql);
						}

						$row = array();
						while( $temp_row = $db->sql_fetchrow($result) )
						{
							$row[$temp_row['post_id']] = 1;

							if ( !$word_count )
							{
								$result_list[$temp_row['post_id']] = 1;
							}
							else if ( $current_match_type == 'or' )
							{
								$result_list[$temp_row['post_id']] = 1;
							}
							else if ( $current_match_type == 'not' )
							{
								$result_list[$temp_row['post_id']] = 0;
							}
						}

						if ( $current_match_type == 'and' && $word_count )
						{
							@reset($result_list);
							while( list($post_id, $match_count) = @each($result_list) )
							{
								if ( !$row[$post_id] )
								{
									$result_list[$post_id] = 0;
								}
							}
						}

						$word_count++;

						$db->sql_freeresult($result);
					}
			}

			@reset($result_list);

			$search_ids = array();
			while( list($post_id, $matches) = each($result_list) )
			{
				if ( $matches )
				{
					$search_ids[] = $post_id;
				}
			}	
			
			unset($result_list);
			$total_match_count = count($search_ids);
		}

		//
		// If user is logged in then we'll check to see which (if any) private
		// forums they are allowed to view and include them in the search.
		//
		// If not logged in we explicitly prevent searching of private forums
		//
		$auth_sql = '';
		if ( $search_forum != -1 )
		{
			if ( !$acl->get_acl($search_forum, 'forum', 'read') )
			{
				message_die(MESSAGE, $lang['No_searchable_forums']);
			}

			$auth_sql = "f.forum_id = $search_forum";
		}
		else
		{
			if ( $search_cat != -1 )
			{
				$auth_sql = "f.cat_id = $search_cat";
			}

			$auth_ary = $acl->get_acl(); 
			@reset($auth_ary);

			$allowed_forum_sql = '';
			while( list($key, $value) = @each($auth_ary) )
			{
				if ( $value['forum']['read'] )
				{
					$allowed_forum_sql .= ( ( $allowed_forum_sql != '' ) ? ', ' : '' ) . $key;
				}
			}

			$auth_sql .= ( $auth_sql != '' ) ? " AND f.forum_id IN ($allowed_forum_sql) " : "f.forum_id IN ($allowed_forum_sql) ";
		}

		//
		// Author name search 
		//
		if ( $search_author != '' )
		{
			$search_author = str_replace('*', '%', trim(str_replace("\'", "''", $search_author)));
		}

		if ( $total_match_count )
		{
			if ( $show_results == 'topics' )
			{
				$where_sql = '';

				if ( $search_time )
				{
					$where_sql .= ( $search_author == '' && $auth_sql == ''  ) ? " AND post_time >= $search_time " : " AND p.post_time >= $search_time ";
				}

				if ( $search_author == '' && $auth_sql == '' )
				{
					$sql = "SELECT topic_id 
						FROM " . POSTS_TABLE . "
						WHERE post_id IN (" . implode(", ", $search_ids) . ") 
							$where_sql 
						GROUP BY topic_id";
				}
				else
				{
					$from_sql = POSTS_TABLE . " p"; 

					if ( $search_author != '' )
					{
						$from_sql .= ", " . USERS_TABLE . " u";
						$where_sql .= " AND u.user_id = p.poster_id AND u.username LIKE '$search_author' ";
					}

					if ( $auth_sql != '' )
					{
						$from_sql .= ", " . FORUMS_TABLE . " f";
						$where_sql .= " AND f.forum_id = p.forum_id AND $auth_sql";
					}

					$sql = "SELECT p.topic_id 
						FROM $from_sql 
						WHERE p.post_id IN (" . implode(", ", $search_ids) . ") 
							$where_sql 
						GROUP BY p.topic_id";
				}

				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(ERROR, 'Could not obtain topic ids', '', __LINE__, __FILE__, $sql);
				}

				$search_ids = array();
				while( $row = $db->sql_fetchrow($result) )
				{
					$search_ids[] = $row['topic_id'];
				}
				$db->sql_freeresult($result);

				$total_match_count = sizeof($search_ids);
		
			}
			else if ( $search_author != '' || $search_time || $auth_sql != '' )
			{
				$where_sql = ( $search_author == '' && $auth_sql == '' ) ? 'post_id IN (' . implode(', ', $search_ids) . ')' : 'p.post_id IN (' . implode(', ', $search_ids) . ')';
				$from_sql = (  $search_author == '' && $auth_sql == '' ) ? POSTS_TABLE : POSTS_TABLE . ' p';

				if ( $search_time )
				{
					$where_sql .= ( $search_author == '' && $auth_sql == '' ) ? " AND post_time >= $search_time " : " AND p.post_time >= $search_time";
				}

				if ( $auth_sql != '' )
				{
					$from_sql .= ", " . FORUMS_TABLE . " f";
					$where_sql .= " AND f.forum_id = p.forum_id AND $auth_sql";
				}

				if ( $search_author != '' )
				{
					$from_sql .= ", " . USERS_TABLE . " u";
					$where_sql .= " AND u.user_id = p.poster_id AND u.username LIKE '$search_author'";
				}

				$sql = "SELECT p.post_id 
					FROM $from_sql 
					WHERE $where_sql";
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(ERROR, 'Could not obtain post ids', '', __LINE__, __FILE__, $sql);
				}

				$search_ids = array();
				while( $row = $db->sql_fetchrow($result) )
				{
					$search_ids[] = $row['post_id'];
				}

				$db->sql_freeresult($result);

				$total_match_count = count($search_ids);
			}
		}
		else if ( $search_id == 'unanswered' )
		{
			if ( $auth_sql != '' )
			{
				$sql = "SELECT t.topic_id, f.forum_id
					FROM " . TOPICS_TABLE . "  t, " . FORUMS_TABLE . " f
					WHERE t.topic_replies = 0 
						AND t.forum_id = f.forum_id
						AND t.topic_moved_id = 0
						AND $auth_sql";
			}
			else
			{
				$sql = "SELECT topic_id 
					FROM " . TOPICS_TABLE . "  
					WHERE topic_replies = 0 
						AND topic_moved_id = 0";
			}
				
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(ERROR, 'Could not obtain post ids', '', __LINE__, __FILE__, $sql);
			}

			$search_ids = array();
			while( $row = $db->sql_fetchrow($result) )
			{
				$search_ids[] = $row['topic_id'];
			}
			$db->sql_freeresult($result);

			$total_match_count = count($search_ids);

			//
			// Basic requirements
			//
			$show_results = 'topics';
			$sort_by = 0;
			$sort_dir = 'DESC';
		}
		else
		{
			message_die(MESSAGE, $lang['No_search_match']);
		}

		//
		// Finish building query (for all combinations)
		// and run it ...
		//
		$sql = "SELECT session_id 
			FROM " . SESSIONS_TABLE;
		if ( $result = $db->sql_query($sql) )
		{
			$delete_search_ids = array();
			while( $row = $db->sql_fetchrow($result) )
			{
				$delete_search_ids[] = "'" . $row['session_id'] . "'";
			}

			if ( count($delete_search_ids) )
			{
				$sql = "DELETE FROM " . SEARCH_TABLE . " 
					WHERE session_id NOT IN (" . implode(", ", $delete_search_ids) . ")";
				if ( !$result = $db->sql_query($sql) )
				{
					message_die(ERROR, 'Could not delete old search id sessions', '', __LINE__, __FILE__, $sql);
				}
			}
		}

		//
		// Store new result data
		//
		$search_results = implode(', ', $search_ids);
		$per_page = ( $show_results == 'posts' ) ? $board_config['posts_per_page'] : $board_config['topics_per_page'];

		//
		// Combine both results and search data (apart from original query)
		// so we can serialize it and place it in the DB
		//
		$store_search_data = array();
		for($i = 0; $i < count($store_vars); $i++)
		{
			$store_search_data[$store_vars[$i]] = $$store_vars[$i];
		}

		$result_array = serialize($store_search_data);
		unset($store_search_data);

		mt_srand ((double) microtime() * 1000000);
		$search_id = mt_rand();

		$sql = "UPDATE " . SEARCH_TABLE . " 
			SET search_id = $search_id, search_array = '$result_array'
			WHERE session_id = '" . $userdata['session_id'] . "'";
		if ( !($result = $db->sql_query($sql)) || !$db->sql_affectedrows() )
		{
			$sql = "INSERT INTO " . SEARCH_TABLE . " (search_id, session_id, search_array) 
				VALUES($search_id, '" . $userdata['session_id'] . "', '" . str_replace("\'", "''", $result_array) . "')";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(ERROR, 'Could not insert search results', '', __LINE__, __FILE__, $sql);
			}
		}
	}
	else
	{
		if ( intval($search_id) )
		{
			$sql = "SELECT search_array 
				FROM " . SEARCH_TABLE . " 
				WHERE search_id = $search_id  
					AND session_id = '". $userdata['session_id'] . "'";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(ERROR, 'Could not obtain search results', '', __LINE__, __FILE__, $sql);
			}

			if ( $row = $db->sql_fetchrow($result) )
			{
				$search_data = unserialize($row['search_array']);
				for($i = 0; $i < count($store_vars); $i++)
				{
					$$store_vars[$i] = $search_data[$store_vars[$i]];
				}
			}
		}
	}

	//
	// Look up data ...
	//
	if ( $search_results != '' )
	{
		if ( $show_results == 'posts' )
		{
			$sql = "SELECT pt.post_text, pt.bbcode_uid, pt.post_subject, p.*, f.forum_id, f.forum_name, t.*, u.username, u.user_id, u.user_sig, u.user_sig_bbcode_uid  
				FROM " . FORUMS_TABLE . " f, " . TOPICS_TABLE . " t, " . USERS_TABLE . " u, " . POSTS_TABLE . " p, " . POSTS_TEXT_TABLE . " pt 
				WHERE p.post_id IN ($search_results)
					AND pt.post_id = p.post_id
					AND f.forum_id = p.forum_id
					AND p.topic_id = t.topic_id
					AND p.poster_id = u.user_id";
		}
		else
		{
			$sql = "SELECT t.*, f.forum_id, f.forum_name, u.username, u.user_id, u2.username as user2, u2.user_id as id2, p.post_username, p2.post_username AS post_username2, p2.post_time 
				FROM " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f, " . USERS_TABLE . " u, " . POSTS_TABLE . " p, " . POSTS_TABLE . " p2, " . USERS_TABLE . " u2
				WHERE t.topic_id IN ($search_results) 
					AND t.topic_poster = u.user_id
					AND f.forum_id = t.forum_id 
					AND p.post_id = t.topic_first_post_id
					AND p2.post_id = t.topic_last_post_id
					AND u2.user_id = p2.poster_id";
		}

		$per_page = ( $show_results == 'posts' ) ? $board_config['posts_per_page'] : $board_config['topics_per_page'];

		$sql .= " ORDER BY ";
		switch ( $sort_by )
		{
			case 1:
				$sql .= ( $show_results == 'posts' ) ? 'pt.post_subject' : 't.topic_title';
				break;
			case 2:
				$sql .= 't.topic_title';
				break;
			case 3:
				$sql .= 'u.username';
				break;
			case 4:
				$sql .= 'f.forum_id';
				break;
			default:
				$sql .= ( $show_results == 'posts' ) ? 'p.post_time' : 'p2.post_time';
				break;
		}
		$sql .= " $sort_dir LIMIT $start, " . $per_page;

		if ( !$result = $db->sql_query($sql) )
		{
			message_die(ERROR, 'Could not obtain search results', '', __LINE__, __FILE__, $sql);
		}

		$searchset = array();
		while( $row = $db->sql_fetchrow($result) )
		{
			$searchset[] = $row;
		}
		
		$db->sql_freeresult($result);		
		
		//
		// Define censored word matches
		//
		$orig_word = array();
		$replacement_word = array();
		obtain_word_list($orig_word, $replacement_word);

		//
		// Output header
		//
		$page_title = $lang['Search'];
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);	

		if ( $show_results == 'posts' )
		{
			$template->set_filenames(array(
				'body' => 'search_results_posts.tpl')
			);
		}
		else
		{
			$template->set_filenames(array(
				'body' => 'search_results_topics.tpl')
			);
		}
		make_jumpbox('viewforum.'.$phpEx);

		$l_search_matches = ( $total_match_count == 1 ) ? sprintf($lang['Found_search_match'], $total_match_count) : sprintf($lang['Found_search_matches'], $total_match_count);

		$template->assign_vars(array(
			'L_SEARCH_MATCHES' => $l_search_matches, 
			'L_TOPIC' => $lang['Topic'])
		);

		$highlight_active = '';
		$highlight_match = array();
		for($j = 0; $j < count($split_search); $j++ )
		{
			$split_word = $split_search[$j];

			if ( $split_word != 'and' && $split_word != 'or' && $split_word != 'not' )
			{
				$highlight_match[] = '#\b(' . str_replace("*", "([\w]+)?", $split_word) . ')\b#is';
				$highlight_active .= " " . $split_word;

				for ($k = 0; $k < count($synonym_array); $k++)
				{ 
					list($replace_synonym, $match_synonym) = split(' ', trim(strtolower($synonym_array[$k]))); 

					if ( $replace_synonym == $split_word )
					{
						$highlight_match[] = '#\b(' . str_replace("*", "([\w]+)?", $replace_synonym) . ')\b#is';
						$highlight_active .= ' ' . $match_synonym;
					}
				} 
			}
		}

		$highlight_active = urlencode(trim($highlight_active));

		$tracking_topics = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_t']) ) ? unserialize($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_t']) : array();
		$tracking_forums = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f']) ) ? unserialize($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f']) : array();

		for($i = 0; $i < count($searchset); $i++)
		{
			$forum_url = append_sid("viewforum.$phpEx?" . POST_FORUM_URL . '=' . $searchset[$i]['forum_id']);
			$topic_url = append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . '=' . $searchset[$i]['topic_id'] . "&amp;highlight=$highlight_active");
			$post_url = append_sid("viewtopic.$phpEx?" . POST_POST_URL . '=' . $searchset[$i]['post_id'] . "&amp;highlight=$highlight_active") . '#' . $searchset[$i]['post_id'];

			$post_date = create_date($board_config['default_dateformat'], $searchset[$i]['post_time'], $board_config['board_timezone']);

			$message = $searchset[$i]['post_text'];
			$topic_title = $searchset[$i]['topic_title'];

			$forum_id = $searchset[$i]['forum_id'];
			$topic_id = $searchset[$i]['topic_id'];

			if ( $show_results == 'posts' )
			{
				if ( isset($return_chars) )
				{
					$bbcode_uid = $searchset[$i]['bbcode_uid'];

					//
					// If the board has HTML off but the post has HTML
					// on then we process it, else leave it alone
					//
					if ( $return_chars != -1 )
					{
						$message = strip_tags($message);
						$message = preg_replace("/\[.*?:$bbcode_uid:?.*?\]/si", '', $message);
						$message = preg_replace('/\[url\]|\[\/url\]/si', '', $message);
						$message = ( strlen($message) > $return_chars ) ? substr($message, 0, $return_chars) . ' ...' : $message;

						if ( count($search_string) )
						{
							$message = preg_replace($search_string, $replace_string, $message);
						}
					}
					else
					{
						if ( !$board_config['allow_html'] )
						{
							if ( $postrow[$i]['enable_html'] )
							{
								$message = preg_replace('#(<)([\/]?.*?)(>)#is', '&lt;\\2&gt;', $message);
							}
						}

						if ( $bbcode_uid != '' )
						{
							$message = ( $board_config['allow_bbcode'] ) ? bbencode_second_pass($message, $bbcode_uid) : preg_replace('/\:[0-9a-z\:]+\]/si', ']', $message);
						}

						$message = make_clickable($message);

						if ( $highlight_active )
						{
							if ( preg_match('/<.*>/', $message) )
							{
								$message = preg_replace($highlight_match, '<!-- #sh -->\1<!-- #eh -->', $message);

								$end_html = 0;
								$start_html = 1;
								$temp_message = '';
								$message = ' ' . $message . ' ';

								while( $start_html = strpos($message, '<', $start_html) )
								{
									$grab_length = $start_html - $end_html - 1;
									$temp_message .= substr($message, $end_html + 1, $grab_length);

									if ( $end_html = strpos($message, '>', $start_html) )
									{
										$length = $end_html - $start_html + 1;
										$hold_string = substr($message, $start_html, $length);

										if ( strrpos(' ' . $hold_string, '<') != 1 )
										{
											$end_html = $start_html + 1;
											$end_counter = 1;

											while ( $end_counter && $end_html < strlen($message) )
											{
												if ( substr($message, $end_html, 1) == '>' )
												{
													$end_counter--;
												}
												else if ( substr($message, $end_html, 1) == '<' )
												{
													$end_counter++;
												}

												$end_html++;
											}

											$length = $end_html - $start_html + 1;
											$hold_string = substr($message, $start_html, $length);
											$hold_string = str_replace('<!-- #sh -->', '', $hold_string);
											$hold_string = str_replace('<!-- #eh -->', '', $hold_string);
										}
										else if ( $hold_string == '<!-- #sh -->' )
										{
											$hold_string = str_replace('<!-- #sh -->', '<span style="color:#' . $theme['fontcolor3'] . '"><b>', $hold_string);
										}
										else if ( $hold_string == '<!-- #eh -->' )
										{
											$hold_string = str_replace('<!-- #eh -->', '</b></span>', $hold_string);
										}

										$temp_message .= $hold_string;

										$start_html += $length;
									}
									else
									{
										$start_html = strlen($message);
									}
								}

								$grab_length = strlen($message) - $end_html - 1;
								$temp_message .= substr($message, $end_html + 1, $grab_length);

								$message = trim($temp_message);
							}
							else
							{
								$message = preg_replace($highlight_match, '<span style="color:#' . $theme['fontcolor3'] . '"><b>\1</b></span>', $message);
							}
						}
					}

					if ( count($orig_word) )
					{
						$topic_title = preg_replace($orig_word, $replacement_word, $topic_title);
						$post_subject = ( $searchset[$i]['post_subject'] != "" ) ? preg_replace($orig_word, $replacement_word, $searchset[$i]['post_subject']) : $topic_title;

						$message = preg_replace($orig_word, $replacement_word, $message);
					}
					else
					{
						$post_subject = ( $searchset[$i]['post_subject'] != '' ) ? $searchset[$i]['post_subject'] : $topic_title;
					}

					if ($board_config['allow_smilies'] && $searchset[$i]['enable_smilies'])
					{
						$message = smilies_pass($message);
					}

					$message = str_replace("\n", '<br />', $message);

				}

				$poster = ( $searchset[$i]['user_id'] != ANONYMOUS ) ? '<a href="' . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $searchset[$i]['user_id']) . '">' : '';
				$poster .= ( $searchset[$i]['user_id'] != ANONYMOUS ) ? $searchset[$i]['username'] : ( ( $searchset[$i]['post_username'] != "" ) ? $searchset[$i]['post_username'] : $lang['Guest'] );
				$poster .= ( $searchset[$i]['user_id'] != ANONYMOUS ) ? '</a>' : '';

				if ( $userdata['session_logged_in'] && $searchset[$i]['post_time'] > $userdata['user_lastvisit'] )
				{
					if ( !empty($tracking_topics[$topic_id]) && !empty($tracking_forums[$forum_id]) )
					{
						$topic_last_read = ( $tracking_topics[$topic_id] > $tracking_forums[$forum_id] ) ? $tracking_topics[$topic_id] : $tracking_forums[$forum_id];
					}
					else if ( !empty($tracking_topics[$topic_id]) || !empty($tracking_forums[$forum_id]) )
					{
						$topic_last_read = ( !empty($tracking_topics[$topic_id]) ) ? $tracking_topics[$topic_id] : $tracking_forums[$forum_id];
					}

					if ( $searchset[$i]['post_time'] > $topic_last_read )
					{
						$mini_post_img = $images['icon_minipost_new'];
						$mini_post_alt = $lang['New_post'];
					}
					else
					{
						$mini_post_img = $images['icon_minipost'];
						$mini_post_alt = $lang['Post'];
					}
				}
				else
				{
					$mini_post_img = $images['icon_minipost'];
					$mini_post_alt = $lang['Post'];
				}

				$template->assign_block_vars("searchresults", array( 
					'TOPIC_TITLE' => $topic_title,
					'FORUM_NAME' => $searchset[$i]['forum_name'],
					'POST_SUBJECT' => $post_subject,
					'POST_DATE' => $post_date,
					'POSTER_NAME' => $poster,
					'TOPIC_REPLIES' => $searchset[$i]['topic_replies'],
					'TOPIC_VIEWS' => $searchset[$i]['topic_views'],
					'MESSAGE' => $message,
					'MINI_POST_IMG' => $mini_post_img, 

					'L_MINI_POST_ALT' => $mini_post_alt, 

					'U_POST' => $post_url,
					'U_TOPIC' => $topic_url,
					'U_FORUM' => $forum_url)
				);
			}
			else
			{
				$message = '';

				if ( count($orig_word) )
				{
					$topic_title = preg_replace($orig_word, $replacement_word, $searchset[$i]['topic_title']);
				}

				$topic_type = $searchset[$i]['topic_type'];

				if ($topic_type == POST_ANNOUNCE)
				{
					$topic_type = $lang['Topic_Announcement'] . ' ';
				}
				else if ($topic_type == POST_STICKY)
				{
					$topic_type = $lang['Topic_Sticky'] . ' ';
				}
				else
				{
					$topic_type = '';
				}

				if ( $searchset[$i]['topic_vote'] )
				{
					$topic_type .= $lang['Topic_Poll'] . ' ';
				}

				$views = $searchset[$i]['topic_views'];
				$replies = $searchset[$i]['topic_replies'];

				if ( ( $replies + 1 ) > $board_config['posts_per_page'] )
				{
					$total_pages = ceil( ( $replies + 1 ) / $board_config['posts_per_page'] );
					$goto_page = ' [ <img src="' . $images['icon_gotopost'] . '" alt="' . $lang['Goto_page'] . '" title="' . $lang['Goto_page'] . '" />' . $lang['Goto_page'] . ': ';

					$times = 1;
					for($j = 0; $j < $replies + 1; $j += $board_config['posts_per_page'])
					{
						$goto_page .= '<a href="' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=" . $topic_id . "&amp;start=$j") . '">' . $times . '</a>';
						if ( $times == 1 && $total_pages > 4 )
						{
							$goto_page .= ' ... ';
							$times = $total_pages - 3;
							$j += ( $total_pages - 4 ) * $board_config['posts_per_page'];
						}
						else if ( $times < $total_pages )
						{
							$goto_page .= ', ';
						}
						$times++;
					}
					$goto_page .= ' ] ';
				}
				else
				{
					$goto_page = '';
				}

				if ( $searchset[$i]['topic_status'] == TOPIC_MOVED )
				{
					$topic_type = $lang['Topic_Moved'] . ' ';
					$topic_id = $searchset[$i]['topic_moved_id'];

					$folder_image = '<img src="' . $images['folder'] . '" alt="' . $lang['No_new_posts'] . '" />';
					$newest_post_img = '';
				}
				else
				{
					if ( $searchset[$i]['topic_status'] == TOPIC_LOCKED )
					{
						$folder = $images['folder_locked'];
						$folder_new = $images['folder_locked_new'];
					}
					else if ( $searchset[$i]['topic_type'] == POST_ANNOUNCE )
					{
						$folder = $images['folder_announce'];
						$folder_new = $images['folder_announce_new'];
					}
					else if ( $searchset[$i]['topic_type'] == POST_STICKY )
					{
						$folder = $images['folder_sticky'];
						$folder_new = $images['folder_sticky_new'];
					}
					else
					{
						if ( $replies >= $board_config['hot_threshold'] )
						{
							$folder = $images['folder_hot'];
							$folder_new = $images['folder_hot_new'];
						}
						else
						{
							$folder = $images['folder'];
							$folder_new = $images['folder_new'];
						}
					}

					if ( $userdata['session_logged_in'] )
					{
						if ( $searchset[$i]['post_time'] > $userdata['user_lastvisit'] ) 
						{
							if ( !empty($tracking_topics) || !empty($tracking_forums) || isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all']) )
							{

								$unread_topics = true;

								if ( !empty($tracking_topics[$topic_id]) )
								{
									if ( $tracking_topics[$topic_id] > $searchset[$i]['post_time'] )
									{
										$unread_topics = false;
									}
								}

								if ( !empty($tracking_forums[$forum_id]) )
								{
									if ( $tracking_forums[$forum_id] > $searchset[$i]['post_time'] )
									{
										$unread_topics = false;
									}
								}

								if ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all']) )
								{
									if ( $HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all'] > $searchset[$i]['post_time'] )
									{
										$unread_topics = false;
									}
								}

								if ( $unread_topics )
								{
									$folder_image = $folder_new;
									$folder_alt = $lang['New_posts'];

									$newest_post_img = '<a href="' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;view=newest") . '"><img src="' . $images['icon_newest_reply'] . '" alt="' . $lang['View_newest_post'] . '" title="' . $lang['View_newest_post'] . '" border="0" /></a> ';
								}
								else
								{
									$folder_alt = ( $searchset[$i]['topic_status'] == TOPIC_LOCKED ) ? $lang['Topic_locked'] : $lang['No_new_posts'];

									$folder_image = $folder;
									$folder_alt = $folder_alt;
									$newest_post_img = '';
								}

							}
							else if ( $searchset[$i]['post_time'] > $userdata['user_lastvisit'] ) 
							{
								$folder_image = $folder_new;
								$folder_alt = $lang['New_posts'];

								$newest_post_img = '<a href="' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;view=newest") . '"><img src="' . $images['icon_newest_reply'] . '" alt="' . $lang['View_newest_post'] . '" title="' . $lang['View_newest_post'] . '" border="0" /></a> ';
							}
							else 
							{
								$folder_image = $folder;
								$folder_alt = ( $searchset[$i]['topic_status'] == TOPIC_LOCKED ) ? $lang['Topic_locked'] : $lang['No_new_posts'];
								$newest_post_img = '';
							}
						}
						else
						{
							$folder_image = $folder;
							$folder_alt = ( $searchset[$i]['topic_status'] == TOPIC_LOCKED ) ? $lang['Topic_locked'] : $lang['No_new_posts'];
							$newest_post_img = '';
						}
					}
					else
					{
						$folder_image = $folder;
						$folder_alt = ( $searchset[$i]['topic_status'] == TOPIC_LOCKED ) ? $lang['Topic_locked'] : $lang['No_new_posts'];
						$newest_post_img = '';
					}
				}


				$topic_author = ( $searchset[$i]['user_id'] != ANONYMOUS ) ? '<a href="' . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . '=' . $searchset[$i]['user_id']) . '">' : '';
				$topic_author .= ( $searchset[$i]['user_id'] != ANONYMOUS ) ? $searchset[$i]['username'] : ( ( $searchset[$i]['post_username'] != '' ) ? $searchset[$i]['post_username'] : $lang['Guest'] );

				$topic_author .= ( $searchset[$i]['user_id'] != ANONYMOUS ) ? '</a>' : '';

				$first_post_time = create_date($board_config['default_dateformat'], $searchset[$i]['topic_time'], $board_config['board_timezone']);

				$last_post_time = create_date($board_config['default_dateformat'], $searchset[$i]['post_time'], $board_config['board_timezone']);

				$last_post_author = ( $searchset[$i]['id2'] == ANONYMOUS ) ? ( ($searchset[$i]['post_username2'] != '' ) ? $searchset[$i]['post_username2'] . ' ' : $lang['Guest'] . ' ' ) : '<a href="' . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . '='  . $searchset[$i]['id2']) . '">' . $searchset[$i]['user2'] . '</a>';

				$last_post_url = '<a href="' . append_sid("viewtopic.$phpEx?"  . POST_POST_URL . '=' . $searchset[$i]['topic_last_post_id']) . '#' . $searchset[$i]['topic_last_post_id'] . '"><img src="' . $images['icon_latest_reply'] . '" alt="' . $lang['View_latest_post'] . '" title="' . $lang['View_latest_post'] . '" border="0" /></a>';

				$template->assign_block_vars('searchresults', array( 
					'FORUM_NAME' => $searchset[$i]['forum_name'],
					'FORUM_ID' => $forum_id,
					'TOPIC_ID' => $topic_id,
					'FOLDER' => $folder_image,
					'NEWEST_POST_IMG' => $newest_post_img, 
					'TOPIC_FOLDER_IMG' => $folder_image, 
					'GOTO_PAGE' => $goto_page,
					'REPLIES' => $replies,
					'TOPIC_TITLE' => $topic_title,
					'TOPIC_TYPE' => $topic_type,
					'VIEWS' => $views,
					'TOPIC_AUTHOR' => $topic_author, 
					'FIRST_POST_TIME' => $first_post_time, 
					'LAST_POST_TIME' => $last_post_time,
					'LAST_POST_AUTHOR' => $last_post_author,
					'LAST_POST_IMG' => $last_post_url,

					'L_TOPIC_FOLDER_ALT' => $folder_alt, 

					'U_VIEW_FORUM' => $forum_url, 
					'U_VIEW_TOPIC' => $topic_url)
				);
			}
		}

		$base_url = "search.$phpEx?search_id=$search_id";

		$template->assign_vars(array(
			'PAGINATION' => generate_pagination($base_url, $total_match_count, $per_page, $start),
			'PAGE_NUMBER' => sprintf($lang['Page_of'], ( floor( $start / $per_page ) + 1 ), ceil( $total_match_count / $per_page )), 

			'L_AUTHOR' => $lang['Author'],
			'L_MESSAGE' => $lang['Message'],
			'L_FORUM' => $lang['Forum'],
			'L_TOPICS' => $lang['Topics'],
			'L_REPLIES' => $lang['Replies'],
			'L_VIEWS' => $lang['Views'],
			'L_POSTS' => $lang['Posts'],
			'L_LASTPOST' => $lang['Last_Post'], 
			'L_POSTED' => $lang['Posted'], 
			'L_SUBJECT' => $lang['Subject'],

			'L_GOTO_PAGE' => $lang['Goto_page'])
		);

		$template->pparse('body');

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
	else
	{
		message_die(MESSAGE, $lang['No_search_match']);
	}
}

//
// Search forum
//
$sql = "SELECT c.cat_title, c.cat_id, f.forum_name, f.forum_id  
	FROM " . CATEGORIES_TABLE . " c, " . FORUMS_TABLE . " f
	WHERE f.cat_id = c.cat_id 
	ORDER BY c.cat_id, f.forum_order";
$result = $db->sql_query($sql);

$s_forums = '';
while( $row = $db->sql_fetchrow($result) )
{
	if ( $acl->get_acl($row['forum_id'], 'forum', 'read') )
	{
		$s_forums .= '<option value="' . $row['forum_id'] . '">' . $row['forum_name'] . '</option>';
		if ( empty($list_cat[$row['cat_id']]) )
		{
			$list_cat[$row['cat_id']] = $row['cat_title'];
		}
	}
}

if ( $s_forums != '' )
{
	$s_forums = '<option value="-1">' . $lang['All_available'] . '</option>' . $s_forums;

	//
	// Category to search
	//
	$s_categories = '<option value="-1">' . $lang['All_available'] . '</option>';
	while( list($cat_id, $cat_title) = @each($list_cat))
	{
		$s_categories .= '<option value="' . $cat_id . '">' . $cat_title . '</option>';
	}
}
else
{
	message_die(MESSAGE, $lang['No_searchable_forums']);
}

//
// Number of chars returned
//
$s_characters = '<option value="-1">' . $lang['All_available'] . '</option>';
$s_characters .= '<option value="0">0</option>';
$s_characters .= '<option value="25">25</option>';
$s_characters .= '<option value="50">50</option>';

for($i = 100; $i < 1100 ; $i += 100)
{
	$selected = ( $i == 200 ) ? ' selected="selected"' : '';
	$s_characters .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
}

//
// Sorting
//
$s_sort_by = "";
for($i = 0; $i < count($sort_by_types); $i++)
{
	$s_sort_by .= '<option value="' . $i . '">' . $sort_by_types[$i] . '</option>';
}

//
// Search time
//
$previous_days = array(0, 1, 7, 14, 30, 90, 180, 364);
$previous_days_text = array($lang['All_Posts'], $lang['1_Day'], $lang['7_Days'], $lang['2_Weeks'], $lang['1_Month'], $lang['3_Months'], $lang['6_Months'], $lang['1_Year']);

$s_time = '';
for($i = 0; $i < count($previous_days); $i++)
{
	$selected = ( $topic_days == $previous_days[$i] ) ? ' selected="selected"' : '';
	$s_time .= '<option value="' . $previous_days[$i] . '"' . $selected . '>' . $previous_days_text[$i] . '</option>';
}

$template->assign_vars(array(
	'L_SEARCH_QUERY' => $lang['Search_query'], 
	'L_SEARCH_OPTIONS' => $lang['Search_options'], 
	'L_SEARCH_KEYWORDS' => $lang['Search_keywords'], 
	'L_SEARCH_KEYWORDS_EXPLAIN' => $lang['Search_keywords_explain'], 
	'L_SEARCH_AUTHOR' => $lang['Search_author'],
	'L_SEARCH_AUTHOR_EXPLAIN' => $lang['Search_author_explain'], 
	'L_SEARCH_ANY_TERMS' => $lang['Search_for_any'],
	'L_SEARCH_ALL_TERMS' => $lang['Search_for_all'], 
	'L_SEARCH_MESSAGE_ONLY' => $lang['Search_msg_only'], 
	'L_SEARCH_MESSAGE_TITLE' => $lang['Search_title_msg'], 
	'L_CATEGORY' => $lang['Category'], 
	'L_RETURN_FIRST' => $lang['Return_first'],
	'L_CHARACTERS' => $lang['characters_posts'], 
	'L_SORT_BY' => $lang['Sort_by'],
	'L_SORT_ASCENDING' => $lang['Sort_Ascending'],
	'L_SORT_DESCENDING' => $lang['Sort_Descending'],
	'L_SEARCH_PREVIOUS' => $lang['Search_previous'], 
	'L_DISPLAY_RESULTS' => $lang['Display_results'], 
	'L_FORUM' => $lang['Forum'],
	'L_TOPICS' => $lang['Topics'],
	'L_POSTS' => $lang['Posts'],

	'S_SEARCH_ACTION' => "search.$phpEx$SID&amp;mode=results",
	'S_CHARACTER_OPTIONS' => $s_characters,
	'S_FORUM_OPTIONS' => $s_forums, 
	'S_CATEGORY_OPTIONS' => $s_categories, 
	'S_TIME_OPTIONS' => $s_time, 
	'S_SORT_OPTIONS' => $s_sort_by,
	'S_HIDDEN_FIELDS' => $s_hidden_fields)
);

//
// Output the basic page
//
$page_title = $lang['Search'];
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	'body' => 'search_body.html')
);
make_jumpbox('viewforum.'.$phpEx);

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

//
// Username search
//
function username_search()
{
	global $SID, $HTTP_GET_VARS, $HTTP_POST_VARS, $phpEx, $phpbb_root_path;
	global $db, $board_config, $template, $acl, $lang, $theme;
	global $starttime;

	$field = ( isset($HTTP_GET_VARS['field']) ) ? $HTTP_GET_VARS['field'] : 'username';
	$start = ( isset($HTTP_GET_VARS['start']) ) ? intval($HTTP_GET_VARS['start']) : 0;

	$sort_order = ( !empty($HTTP_POST_VARS['sort_order']) ) ? $HTTP_POST_VARS['sort_order'] : ( ( !empty($HTTP_GET_VARS['sort_order']) ) ? $HTTP_GET_VARS['sort_order'] : 'd' );

	$sort_by = ( !empty($HTTP_POST_VARS['sort_by']) ) ? intval($HTTP_POST_VARS['sort_by']) : ( ( !empty($HTTP_GET_VARS['sort_by']) ) ? $HTTP_GET_VARS['sort_by'] : '4' );

	$joined_select = ( !empty($HTTP_POST_VARS['joined_select']) ) ? $HTTP_POST_VARS['joined_select'] : ( ( !empty($HTTP_GET_VARS['joined_select']) ) ? $HTTP_GET_VARS['joined_select'] : 'lt' );
	$active_select = ( !empty($HTTP_POST_VARS['active_select']) ) ? $HTTP_POST_VARS['active_select'] : ( ( !empty($HTTP_GET_VARS['active_select']) ) ? $HTTP_GET_VARS['active_select'] : 'lt' );
	$count_select = ( !empty($HTTP_POST_VARS['count_select']) ) ? $HTTP_POST_VARS['count_select'] : ( ( !empty($HTTP_GET_VARS['count_select']) ) ? $HTTP_GET_VARS['count_select'] : 'eq' );

	$username = ( !empty($HTTP_POST_VARS['username']) ) ? $HTTP_POST_VARS['username'] : ( ( !empty($HTTP_GET_VARS['username']) ) ? $HTTP_GET_VARS['username'] : '' );
	$email = ( !empty($HTTP_POST_VARS['email']) ) ? $HTTP_POST_VARS['email'] : ( ( !empty($HTTP_GET_VARS['email']) ) ? $HTTP_GET_VARS['email'] : '' );
	$icq = ( !empty($HTTP_POST_VARS['icq']) ) ? intval($HTTP_POST_VARS['icq']) : ( ( !empty($HTTP_GET_VARS['icq']) ) ? $HTTP_GET_VARS['icq'] : '' );
	$aim = ( !empty($HTTP_POST_VARS['aim']) ) ? $HTTP_POST_VARS['aim'] : ( ( !empty($HTTP_GET_VARS['aim']) ) ? $HTTP_GET_VARS['aim'] : '' );
	$yahoo = ( !empty($HTTP_POST_VARS['yahoo']) ) ? $HTTP_POST_VARS['yahoo'] : ( ( !empty($HTTP_GET_VARS['yahoo']) ) ? $HTTP_GET_VARS['yahoo'] : '' );
	$msn = ( !empty($HTTP_POST_VARS['msn']) ) ? $HTTP_POST_VARS['msn'] : ( ( !empty($HTTP_GET_VARS['msn']) ) ? $HTTP_GET_VARS['msn'] : '' );
	$joined = ( !empty($HTTP_POST_VARS['joined']) ) ? explode('-', $HTTP_POST_VARS['joined']) : ( ( !empty($HTTP_GET_VARS['joined']) ) ? explode('-', $HTTP_GET_VARS['joined']) : array() );
	$active = ( !empty($HTTP_POST_VARS['active']) ) ? explode('-', $HTTP_POST_VARS['active']) : ( ( !empty($HTTP_GET_VARS['active']) ) ? explode('-', $HTTP_GET_VARS['active']) : array() );
	$count = ( !empty($HTTP_POST_VARS['count']) ) ? intval($HTTP_POST_VARS['count']) : ( ( !empty($HTTP_GET_VARS['count']) ) ? $HTTP_GET_VARS['count'] : '' );

	//
	//
	//
	$sort_by_types_text = array($lang['Sort_Username'], $lang['Sort_Email'], $lang['Sort_Post_count'], $lang['Sort_Joined'], $lang['Sort_Last_active']);
	$s_sort_by = '';
	for($i = 0; $i < count($sort_by_types_text); $i++)
	{
		$selected = ( $sort_by == $i ) ? ' selected="selected"' : '';
		$s_sort_by .= '<option value="' . $i . '"' . $selected . '>' . $sort_by_types_text[$i] . '</option>';
	}

	$sort_order_text = array('a' => $lang['Ascending'], 'd' => $lang['Descending']);
	$s_sort_order = '';
	foreach ( $sort_order_text as $key => $value )
	{
		$selected = ( $sort_order == $key ) ? ' selected="selected"' : '';
		$s_sort_order .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
	}

	$find_count = array('lt' => $lang['Less_than'], 'eq' => $lang['Equal_to'], 'gt' => $lang['More_than']);
	$s_find_count = '';
	foreach ( $find_count as $key => $value )
	{
		$selected = ( $count_select == $key ) ? ' selected="selected"' : '';
		$s_find_count .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
	}

	$find_time = array('lt' => $lang['Before'], 'gt' => $lang['After']);
	$s_find_join_time = '';
	foreach ( $find_time as $key => $value )
	{
		$selected = ( $joined_select == $key ) ? ' selected="selected"' : '';
		$s_find_join_time .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
	}
	$s_find_active_time = '';
	foreach ( $find_time as $key => $value )
	{
		$selected = ( $active_select == $key ) ? ' selected="selected"' : '';
		$s_find_active_time .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
	}

	//
	//
	//
	$where_sql = '';
	if ( isset($HTTP_POST_VARS['submit']) )
	{
		$key_match = array('lt' => '<', 'gt' => '>', 'eq' => '=');
		$sort_by_types = array('username', 'user_email', 'user_posts', 'user_regdate', 'user_session_time');

		$where_sql .= ( $username ) ? " AND username LIKE '" . str_replace('*', '%', $username) ."'" : '';
		$where_sql .= ( $email ) ? " AND user_email LIKE '" . str_replace('*', '%', $email) ."' " : '';
		$where_sql .= ( $icq ) ? " AND user_icq LIKE '" . str_replace('*', '%', $icq) ."' " : '';
		$where_sql .= ( $aim ) ? " AND user_aim LIKE '" . str_replace('*', '%', $aim) ."' " : '';
		$where_sql .= ( $yahoo ) ? " AND user_yim LIKE '" . str_replace('*', '%', $yahoo) ."' " : '';
		$where_sql .= ( $msn ) ? " AND user_msnm LIKE '" . str_replace('*', '%', $msn) ."' " : '';
		$where_sql .= ( $joined ) ? " AND user_regdate " . $key_match[$joined_select] . " " . gmmktime(0, 0, 0, intval($joined[1]), intval($joined[2]), intval($joined[0])) : '';
		$where_sql .= ( $count ) ? " AND user_posts " . $key_match[$count_select] . " $count " : '';
		$where_sql .= ( $active ) ? " AND user_lastvisit " . $key_match[$active_select] . " " . gmmktime(0, 0, 0, $active[1], intval($active[2]), intval($active[0])) : '';

		$order_by = $sort_by_types[$sort_by] . "  " . ( ( $sort_order == 'a' ) ? 'ASC' : 'DESC' );
	}
	else
	{
		$order_by = "user_lastvisit DESC ";
	}

	$sql = "SELECT COUNT(user_id) AS total_users 
		FROM " . USERS_TABLE . " 
		WHERE user_id <> " . ANONYMOUS . " 
		$where_sql";
	$result = $db->sql_query($sql);

	$total_users = ( $row = $db->sql_fetchrow($result) ) ? $row['total_users'] : 0;

	$pagination = generate_pagination("search.$phpEx$SID&amp;mode=searchuser&amp;field=$field&amp;username=" . urlencode($username) . "&amp;email=" . urlencode($email) . "&amp;icq=$icq&amp;aim=" . urlencode($aim) . "&amp;yahoo=" . urlencode($yahoo) . "&amp;msn=" . urlencode($msn) . "&amp;joined=" . urlencode(implode('-', $joined)) . "&amp;active=" . urlencode(implode('-', $active)) . "&amp;count=$count&amp;sort_order=$sort_order&amp;sort_by=$sort_by&amp;joined_select=$joined_select&amp;active_select=$active_select&amp;count_select=$count_select", $total_users, $board_config['topics_per_page'], $start);

	//
	//
	//
	$page_title = $lang['Search'];
	include($phpbb_root_path . 'includes/page_header.'.$phpEx);

	$template->set_filenames(array(
		'search_user_body' => 'search_username.html')
	);

	$template->assign_vars(array(
		'USERNAME' => $username, 
		'EMAIL' => $email, 
		'ICQ' => $icq, 
		'AIM' => $aim, 
		'YAHOO' => $yahoo, 
		'MSNM' => $msn, 
		'JOINED' => implode('-', $joined), 
		'ACTIVE' => implode('-', $active), 
		'COUNT' => $count, 

		'PAGINATION' => $pagination,
		'PAGE_NUMBER' => sprintf($lang['Page_of'], ( floor( $start / $board_config['topics_per_page'] ) + 1 ), ceil( $total_users / $board_config['topics_per_page'] )), 

		'L_CLOSE_WINDOW' => $lang['Close_window'], 
		'L_SEARCH_USERNAME' => $lang['Find_username'], 
		'L_SEARCH_EXPLAIN' => $lang['Find_username_explain'], 
		'L_EMAIL' => $lang['Email'], 
		'L_ICQ_NUMBER' => $lang['ICQ'],
		'L_MESSENGER' => $lang['MSNM'],
		'L_YAHOO' => $lang['YIM'],
		'L_AIM' => $lang['AIM'], 
		'L_JOINED' => $lang['Joined'], 
		'L_ACTIVE' => $lang['Last_active'], 
		'L_POSTS' => $lang['Posts'], 
		'L_SORT_BY' => $lang['Sort_by'],
		'L_SORT_ASCENDING' => $lang['Sort_Ascending'],
		'L_SORT_DESCENDING' => $lang['Sort_Descending'],

		'L_UPDATE_USERNAME' => $lang['Select_username'], 
		'L_SELECT' => $lang['Select'], 
		'L_SEARCH' => $lang['Search'], 
		'L_CLOSE_WINDOW' => $lang['Close_window'], 

		'S_FIELD_NAME' => $field, 
		'S_COUNT_OPTIONS' => $s_find_count, 
		'S_JOINED_TIME_OPTIONS' => $s_find_join_time, 
		'S_ACTIVE_TIME_OPTIONS' => $s_find_active_time, 
		'S_SORT_OPTIONS' => $s_sort_by, 
		'S_SORT_ORDER' => $s_sort_order, 
		'S_USERNAME_OPTIONS' => $username_list, 
		'S_SEARCH_ACTION' => "search.$phpEx$SID&amp;mode=searchuser&amp;field=$field")
	);

	$sql = "SELECT username, user_id, user_viewemail, user_posts, user_regdate, user_email, user_lastvisit 
		FROM " . USERS_TABLE . " 
		WHERE user_id <> " . ANONYMOUS . " 
		$where_sql 
		ORDER BY $order_by 
		LIMIT $start, " . $board_config['topics_per_page'];
	$result = $db->sql_query($sql);

	if ( $row = $db->sql_fetchrow($result) )
	{
		$i = 0;
		do
		{
			$username = $row['username'];
			$user_id = $row['user_id'];

			$from = ( !empty($row['user_from']) ) ? $row['user_from'] : '&nbsp;';
			$joined = create_date($lang['DATE_FORMAT'], $row['user_regdate'], $board_config['board_timezone']);
			$posts = ( $row['user_posts'] ) ? $row['user_posts'] : 0;
			$active = ( !$row['user_lastvisit'] ) ? $lang['Never'] : create_date($lang['DATE_FORMAT'], $row['user_lastvisit'], $board_config['board_timezone']);

			$temp_url = "profile.$phpEx$SID&amp;mode=viewprofile&amp;u=$user_id";
			$profile_img = '<a href="' . $temp_url . '">' . create_img($theme['icon_profile'], $lang['Read_profile']) . '</a>';
			$profile = '<a href="' . $temp_url . '">' . $lang['Read_profile'] . '</a>';

			$template->assign_block_vars('memberrow', array(
				'ROW_NUMBER' => $i + ( $start + 1 ), 
				'USERNAME' => $username,
				'JOINED' => $joined,
				'POSTS' => $posts, 
				'ACTIVE' => $active, 
				'PROFILE_IMG' => $profile_img, 
				'PROFILE' => $profile, 
				'S_ROW_COUNT' => $i)				
			);

			$i++;
		}
		while ( $row = $db->sql_fetchrow($result) );
	}

	$template->display('search_user_body');

	include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	exit;
}

?>