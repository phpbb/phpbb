<?php
/***************************************************************************
 *                            functions_post.php
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

if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
}

$html_entities_match = array('#&#', '#<#', '#>#');
$html_entities_replace = array('&amp;', '&lt;', '&gt;');

$unhtml_specialchars_match = array('#&gt;#', '#&lt;#', '#&quot;#', '#&amp;#');
$unhtml_specialchars_replace = array('>', '<', '"', '&');

//
// This function will prepare a posted message for
// entry into the database.
//
function prepare_message($message, $html_on, $bbcode_on, $smile_on, $bbcode_uid = 0)
{
	global $board_config;
	global $html_entities_match, $html_entities_replace;
	global $code_entities_match, $code_entities_replace;

	//
	// Clean up the message
	//
	$message = trim($message);

	if ( $html_on )
	{
		$allowed_html_tags = split(',', $board_config['allow_html_tags']);

		$end_html = 0;
		$start_html = 1;
		$tmp_message = '';
		$message = ' ' . $message . ' ';

		while ( $start_html = strpos($message, '<', $start_html) )
		{
			$tmp_message .= preg_replace($html_entities_match, $html_entities_replace, substr($message, $end_html + 1, ( $start_html - $end_html - 1 )));

			if ( $end_html = strpos($message, '>', $start_html) )
			{
				$length = $end_html - $start_html + 1;
				$hold_string = substr($message, $start_html, $length);

				if ( ( $unclosed_open = strrpos(' ' . $hold_string, '<') ) != 1 )
				{
					$tmp_message .= preg_replace($html_entities_match, $html_entities_replace, substr($hold_string, 0, $unclosed_open - 1));
					$hold_string = substr($hold_string, $unclosed_open - 1);
				}

				$tagallowed = false;
				for($i = 0; $i < sizeof($allowed_html_tags); $i++)
				{
					$match_tag = trim($allowed_html_tags[$i]);

					if ( preg_match('/^<\/?' . $match_tag . '\b/i', $hold_string) )
					{
						$tagallowed = true;
					}
				}

				$tmp_message .= ( $length && !$tagallowed ) ? preg_replace($html_entities_match, $html_entities_replace, $hold_string) : $hold_string;

				$start_html += $length;
			}
			else
			{
				$tmp_message .= preg_replace($html_entities_match, $html_entities_replace, substr($message, $start_html, strlen($message)));

				$start_html = strlen($message);
				$end_html = $start_html;
			}
		}

		if ( $end_html != strlen($message) && $tmp_message != '' )
		{
			$tmp_message .= preg_replace($html_entities_match, $html_entities_replace, substr($message, $end_html + 1));
		}

		$message = ( $tmp_message != '' ) ? trim($tmp_message) : trim($message);
	}
	else
	{
		$message = preg_replace($html_entities_match, $html_entities_replace, $message);
	}

	if( $bbcode_on && $bbcode_uid != '' )
	{
		$tmp_message = $message;
		if ( ($match_count = preg_match_all('#^(.*?)\[code\](.*?)\[\/code\](.*?)$#is', $tmp_message, $match)) )
		{
			$code_entities_match = array('#<#', '#>#', '#"#', '#:#', '#\[#', '#\]#', '#\(#', '#\)#', '#\{#', '#\}#');
			$code_entities_replace = array('&lt;', '&gt;', '&quot;', '&#58;', '&#91;', '&#93;', '&#40;', '&#41;', '&#123;', '&#125;');

			$message = '';
			
			for($i = 0; $i < $match_count; $i++)
			{
				$message .= $match[1][$i] . '[code]' . preg_replace($code_entities_match, $code_entities_replace, $match[2][$i]) . '[/code]';
				$tmp_message = $match[3][$i];
			}

			$message .= $tmp_message;
		}
		
		$message = bbencode_first_pass($message, $bbcode_uid);
	}

	return $message;
}

function unprepare_message($message)
{
	global $unhtml_specialchars_match, $unhtml_specialchars_replace;

	return preg_replace($unhtml_specialchars_match, $unhtml_specialchars_replace, $message);
}

//
// Prepare a message for posting
// 
function prepare_post(&$mode, &$post_data, &$bbcode_on, &$html_on, &$smilies_on, &$error_msg, &$username, &$bbcode_uid, &$subject, &$message, &$poll_title, &$poll_options, &$poll_length)
{
	global $board_config, $userdata, $lang, $phpEx, $phpbb_root_path;

	// Check username
	if ( !empty($username) )
	{
		$username = htmlspecialchars(trim(strip_tags($username)));

		if ( !$userdata['session_logged_in'] || ( $userdata['session_logged_in'] && $username != $userdata['username'] ) )
		{
			include($phpbb_root_path . 'includes/functions_validate.'.$phpEx);

			$result = validate_username($username);
			if ( $result['error'] )
			{
				$error_msg .= ( !empty($error_msg) ) ? '<br />' . $result['error_msg'] : $result['error_msg'];
			}
		}
	}

	// Check subject
	if ( !empty($subject) )
	{
		$subject = htmlspecialchars(trim($subject));
	}
	else if ( $mode == 'newtopic' || ( $mode == 'editpost' && $post_data['first_post'] ) )
	{
		$error_msg .= ( !empty($error_msg) ) ? '<br />' . $lang['Empty_subject'] : $lang['Empty_subject'];
	}

	// Check message
	if ( !empty($message) )
	{
		$bbcode_uid = ( $bbcode_on ) ? make_bbcode_uid() : '';
		$message = prepare_message(trim($message), $html_on, $bbcode_on, $smilies_on, $bbcode_uid);
	}
	else if ( $mode != 'delete' && $mode != 'polldelete' ) 
	{
		$error_msg .= ( !empty($error_msg) ) ? '<br />' . $lang['Empty_message'] : $lang['Empty_message'];
	}

	//
	// Handle poll stuff
	//
	if ( $mode == 'newtopic' || ( $mode == 'editpost' && $post_data['first_post'] ) )
	{
		$poll_length = ( isset($poll_length) ) ? max(0, intval($poll_length)) : 0;

		if ( !empty($poll_title) )
		{
			$poll_title = htmlspecialchars(trim($poll_title));
		}

		if( !empty($poll_options) )
		{
			$temp_option_text = array();
			while( list($option_id, $option_text) = @each($poll_options) )
			{
				$option_text = trim($option_text);
				if ( !empty($option_text) )
				{
					$temp_option_text[$option_id] = htmlspecialchars($option_text);
				}
			}
			$option_text = $temp_option_text;

			if ( count($poll_options) < 2 )
			{
				$error_msg .= ( !empty($error_msg) ) ? '<br />' . $lang['To_few_poll_options'] : $lang['To_few_poll_options'];
			}
			else if ( count($poll_options) > $board_config['max_poll_options'] ) 
			{
				$error_msg .= ( !empty($error_msg) ) ? '<br />' . $lang['To_many_poll_options'] : $lang['To_many_poll_options'];
			}
			else if ( $poll_title == '' )
			{
				$error_msg .= ( !empty($error_msg) ) ? '<br />' . $lang['Empty_poll_title'] : $lang['Empty_poll_title'];
			}
		}
	}

	return;
}

//
// Post a new topic/reply/poll or edit existing post/poll
//
function submit_post($mode, &$post_data, &$message, &$meta, &$forum_id, &$topic_id, &$post_id, &$poll_id, &$topic_type, &$bbcode_on, &$html_on, &$smilies_on, &$attach_sig, &$bbcode_uid, &$post_username, &$post_subject, &$post_message, &$poll_title, &$poll_options, &$poll_length)
{
	global $board_config, $lang, $db, $phpbb_root_path, $phpEx;
	global $userdata, $user_ip;

	$current_time = time();

	if ( $mode == 'newtopic' || $mode == 'reply' ) 
	{
		//
		// Flood control
		//
		$where_sql = ( $userdata['user_id'] == ANONYMOUS ) ? "poster_ip = '$user_ip'" : 'poster_id = ' . $userdata['user_id'];
		$sql = "SELECT MAX(post_time) AS last_post_time
			FROM " . POSTS_TABLE . "
			WHERE $where_sql";
		if ( $result = $db->sql_query($sql) )
		{
			if( $row = $db->sql_fetchrow($result) )
			{
				if ( $row['last_post_time'] > 0 && ( $current_time - $row['last_post_time'] ) < $board_config['flood_interval'] )
				{
					message_die(GENERAL_MESSAGE, $lang['Flood_Error']);
				}
			}
		}
	}
	else if ( $mode == 'editpost' )
	{
		remove_search_post($post_id);
	}

	if ( $mode == 'newtopic' || ( $mode == 'editpost' && $post_data['first_post'] ) )
	{
		$topic_vote = ( !empty($poll_title) && count($poll_options) >= 2 ) ? 1 : 0;
		$sql  = ( $mode != "editpost" ) ? "INSERT INTO " . TOPICS_TABLE . " (topic_title, topic_poster, topic_time, forum_id, topic_status, topic_type, topic_vote) VALUES ('$post_subject', " . $userdata['user_id'] . ", $current_time, $forum_id, " . TOPIC_UNLOCKED . ", $topic_type, $topic_vote)" : "UPDATE " . TOPICS_TABLE . " SET topic_title = '$post_subject', topic_type = $topic_type, topic_vote = $topic_vote WHERE topic_id = $topic_id";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
		}

		if( $mode == 'newtopic' )
		{
			$topic_id = $db->sql_nextid();
		}
	}

	$edited_sql = ( $mode == 'editpost' && !$post_data['last_post'] && $post_data['poster_post'] ) ? ", post_edit_time = $current_time, post_edit_count = post_edit_count + 1 " : "";
	$sql = ( $mode != "editpost" ) ? "INSERT INTO " . POSTS_TABLE . " (topic_id, forum_id, poster_id, post_username, post_time, poster_ip, enable_bbcode, enable_html, enable_smilies, enable_sig) VALUES ($topic_id, $forum_id, " . $userdata['user_id'] . ", '$post_username', $current_time, '$user_ip', $bbcode_on, $html_on, $smilies_on, $attach_sig)" : "UPDATE " . POSTS_TABLE . " SET enable_bbcode = $bbcode_on, enable_html = $html_on, enable_smilies = $smilies_on, enable_sig = $attach_sig" . $edited_sql . " WHERE post_id = $post_id";
	if ( !($result = $db->sql_query($sql, BEGIN_TRANSACTION)) )
	{
		message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
	}

	if( $mode != 'editpost' )
	{
		$post_id = $db->sql_nextid();
	}

	$sql = ( $mode != 'editpost' ) ? "INSERT INTO " . POSTS_TEXT_TABLE . " (post_id, post_subject, bbcode_uid, post_text) VALUES ($post_id, '$post_subject', '$bbcode_uid', '$post_message')" : "UPDATE " . POSTS_TEXT_TABLE . " SET post_text = '$post_message',  bbcode_uid = '$bbcode_uid', post_subject = '$post_subject' WHERE post_id = $post_id";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
	}

	add_search_words($post_id, stripslashes($post_message), stripslashes($post_subject));

	//
	// Add poll
	// 
	if ( ( $mode == 'newtopic' || $mode == 'editpost' ) && !empty($poll_title) && count($poll_options) >= 2 )
	{
		$sql = ( !$post_data['has_poll'] ) ? "INSERT INTO " . VOTE_DESC_TABLE . " (topic_id, vote_text, vote_start, vote_length) VALUES ($topic_id, '$poll_title', $current_time, " . ( $poll_length * 86400 ) . ")" : "UPDATE " . VOTE_DESC_TABLE . " SET vote_text = '$poll_title', vote_length = " . ( $poll_length * 86400 ) . " WHERE topic_id = $topic_id";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
		}

		$delete_option_sql = '';
		$old_poll_result = array();
		if ( $mode == 'editpost' && $post_data['has_poll'] )
		{
			$sql = "SELECT vote_option_id, vote_result  
				FROM " . VOTE_RESULTS_TABLE . " 
				WHERE vote_id = $poll_id 
				ORDER BY vote_option_id ASC";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not obtain vote data results for this topic', '', __LINE__, __FILE__, $sql);
			}

			while ( $row = $db->sql_fetchrow($result) )
			{
				$old_poll_result[$row['vote_option_id']] = $row['vote_result'];

				if( !isset($poll_options[$row['vote_option_id']]) )
				{
					$delete_option_sql .= ( $delete_option_sql != '' ) ? ', ' . $row['vote_option_id'] : $row['vote_option_id'];
				}
			}
		}
		else
		{
			$poll_id = $db->sql_nextid();
		}

		@reset($poll_options);

		$poll_option_id = 1;
		while ( list($option_id, $option_text) = each($poll_options) )
		{
			if( !empty($option_text) )
			{
				$option_text = str_replace("\'", "''", $option_text);
				$poll_result = ( $mode == "editpost" && isset($old_poll_result[$option_id]) ) ? $old_poll_result[$option_id] : 0;

				$sql = ( $mode != "editpost" || !isset($old_poll_result[$option_id]) ) ? "INSERT INTO " . VOTE_RESULTS_TABLE . " (vote_id, vote_option_id, vote_option_text, vote_result) VALUES ($poll_id, $poll_option_id, '$option_text', $poll_result)" : "UPDATE " . VOTE_RESULTS_TABLE . " SET vote_option_text = '$option_text', vote_result = $poll_result WHERE vote_option_id = $option_id AND vote_id = $poll_id";
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
				}
				$poll_option_id++;
			}
		}

		if( $delete_option_sql != '' )
		{
			$sql = "DELETE FROM " . VOTE_RESULTS_TABLE . " 
				WHERE vote_option_id IN ($delete_option_sql)";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Error deleting pruned poll options', '', __LINE__, __FILE__, $sql);
			}
		}
	}

	$meta = '<meta http-equiv="refresh" content="3;url=' . "viewtopic.$phpEx$SID&amp;" . POST_POST_URL . "=" . $post_id . '#' . $post_id . '">';
	$message = $lang['Stored'] . '<br /><br />' . sprintf($lang['Click_view_message'], '<a href="' . "viewtopic.$phpEx$SID&amp;" . POST_POST_URL . "=" . $post_id . '#' . $post_id . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_forum'], '<a href="' . "viewforum.$phpEx$SID&amp;" . POST_FORUM_URL . "=$forum_id" . '">', '</a>');

	return false;
}

//
// Update post stats and details
//
function update_post_stats(&$mode, &$post_data, &$forum_id, &$topic_id, &$post_id, &$user_id)
{
	global $db;

	$sign = ( $mode == 'delete' ) ? '- 1' : '+ 1';
	$forum_update_sql = "forum_posts = forum_posts $sign";
	$topic_update_sql = '';

	if ( $mode == 'delete' )
	{
		if ( $post_data['last_post'] )
		{
			if ( $post_data['first_post'] )
			{
				$forum_update_sql .= ', forum_topics = forum_topics - 1';
			}
			else
			{

				$topic_update_sql .= "topic_replies = topic_replies - 1";

				$sql = "SELECT MAX(post_id) AS post_id
					FROM " . POSTS_TABLE . " 
					WHERE topic_id = $topic_id";
				if ( !($db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
				}

				if ( $row = $db->sql_fetchrow($result) )
				{
					$topic_update_sql .= ', topic_last_post_id = ' . $row['post_id'];
				}
			}

			if ( $post_data['last_topic'] )
			{
				$sql = "SELECT MAX(post_id) AS post_id
					FROM " . POSTS_TABLE . " 
					WHERE forum_id = $forum_id"; 
				if ( !($db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
				}

				if ( $row = $db->sql_fetchrow($result) )
				{
					$forum_update_sql .= ( $row['post_id'] ) ? ', forum_last_post_id = ' . $row['post_id'] : ', forum_last_post_id = 0';
				}
			}
		}
		else if ( $post_data['first_post'] ) 
		{
			$sql = "SELECT MIN(post_id) AS post_id
				FROM " . POSTS_TABLE . " 
				WHERE topic_id = $topic_id";
			if ( !($db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
			}

			if ( $row = $db->sql_fetchrow($result) )
			{
				$topic_update_sql .= 'topic_replies = topic_replies - 1, topic_first_post_id = ' . $row['post_id'];
			}
		}
		else
		{
			$topic_update_sql .= 'topic_replies = topic_replies - 1';
		}
	}
	else if ( $mode != 'poll_delete' )
	{
		$forum_update_sql .= ", forum_last_post_id = $post_id" . ( ( $mode == 'newtopic' ) ? ", forum_topics = forum_topics $sign" : "" ); 
		$topic_update_sql = "topic_last_post_id = $post_id" . ( ( $mode == 'reply' ) ? ", topic_replies = topic_replies $sign" : ", topic_first_post_id = $post_id" );
	}
	else 
	{
		$topic_update_sql .= 'topic_vote = 0';
	}

	$sql = "UPDATE " . FORUMS_TABLE . " SET 
		$forum_update_sql 
		WHERE forum_id = $forum_id";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
	}

	if ( $topic_update_sql != '' )
	{
		$sql = "UPDATE " . TOPICS_TABLE . " SET 
			$topic_update_sql 
			WHERE topic_id = $topic_id";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
		}
	}

	if ( $mode != 'poll_delete' )
	{
		$sql = "UPDATE " . USERS_TABLE . "
			SET user_posts = user_posts $sign 
			WHERE user_id = $user_id";
		if ( !($result = $db->sql_query($sql, END_TRANSACTION)) )
		{
			message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
		}
	}

	return;
}

//
// Delete a post/poll
//
function delete_post($mode, &$post_data, &$message, &$meta, &$forum_id, &$topic_id, &$post_id, &$poll_id)
{
	global $board_config, $lang, $db, $phpbb_root_path, $phpEx;
	global $userdata, $user_ip;

	$topic_update_sql = '';
	if ( $mode != 'poll_delete' )
	{
		$sql = "DELETE FROM " . POSTS_TABLE . " 
			WHERE post_id = $post_id";
		if ( !($db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
		}

		$sql = "DELETE FROM " . POSTS_TEXT_TABLE . " 
			WHERE post_id = $post_id";
		if ( !($db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
		}

		$sql = "DELETE FROM " . SEARCH_MATCH_TABLE . "  
			WHERE post_id = $post_id";
		if ( !($db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
		}

		$forum_update_sql = 'forum_posts = forum_posts - 1'; 
		$topic_update_sql .= 'topic_replies = topic_replies - 1';
		if ( $post_data['last_post'] )
		{
			if ( $post_data['first_post'] )
			{
				$sql = "DELETE FROM " . TOPICS_TABLE . " 
					WHERE topic_id = $topic_id 
						OR topic_moved_id = $topic_id";
				if ( !($db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
				}

				$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . "
					WHERE topic_id = $topic_id";
				if ( !($db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
				}
			}
		}
	}

	if( $mode == 'poll_delete' || ( $mode == 'delete' && $post_data['first_post'] && $post_data['last_post'] ) && $post_data['has_poll'] && $post_data['edit_poll'] )
	{
		$sql = "DELETE FROM " . VOTE_DESC_TABLE . " 
			WHERE vote_id = $poll_id";
		if ( !($db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in deleting poll', '', __LINE__, __FILE__, $sql);
		}

		$sql = "DELETE FROM " . VOTE_RESULTS_TABLE . " 
			WHERE vote_id = $poll_id";
		if ( !($db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in deleting poll', '', __LINE__, __FILE__, $sql);
		}

		$sql = "DELETE FROM " . VOTE_USERS_TABLE . " 
			WHERE vote_id = $poll_id";
		if ( !($db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in deleting poll', '', __LINE__, __FILE__, $sql);
		}
	}

	remove_search_post($post_id);

	if ( $mode == 'delete' && $post_data['first_post'] && $post_data['last_post'] )
	{
		$meta = '<meta http-equiv="refresh" content="3;url=' . "viewforum.$phpEx$SID&amp;" . POST_FORUM_URL . "=" . $forum_id . '">';
		$message = $lang['Deleted'];
	}
	else
	{
		$meta = '<meta http-equiv="refresh" content="3;url=' . "viewtopic.$phpEx$SID&amp;" . POST_TOPIC_URL . "=" . $topic_id . '">';
		$message = ( ( $mode == "poll_delete" ) ? $lang['Poll_delete'] : $lang['Deleted'] ) . '<br /><br />' . sprintf($lang['Click_return_topic'], '<a href="' . "viewtopic.$phpEx$SID&amp;" . POST_TOPIC_URL . "=$topic_id" . '">', '</a>');
	}

	$message .=  '<br /><br />' . sprintf($lang['Click_return_forum'], '<a href="' . "viewforum.$phpEx$SID&amp;" . POST_FORUM_URL . "=$forum_id" . '">', '</a>');

	return;
}

//
// Handle user notification on new post
//
function user_notification($mode, &$post_data, &$forum_id, &$topic_id, &$post_id, &$notify_user)
{
	global $board_config, $lang, $db, $phpbb_root_path, $phpEx;
	global $userdata, $user_ip;

	$current_time = time();

	if ( $mode == 'delete' )
	{
		$delete_sql = ( !$post_data['first_post'] && !$post_data['last_post'] ) ? " AND user_id = " . $userdata['user_id'] : "";
		$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . " WHERE topic_id = $topic_id" . $delete_sql;
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not change topic notify data', '', __LINE__, __FILE__, $sql);
		}
	}
	else 
	{
		if ( $mode == 'reply' || $mode == 'newtopic' )
		{
			$sql = "SELECT ban_userid 
				FROM " . BANLIST_TABLE;
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not obtain banlist', '', __LINE__, __FILE__, $sql);
			}

			$user_id_sql = '';
			while ( $row = $db->sql_fetchrow($result) )
			{
				if ( isset($row['ban_userid']) )
				{
					$user_id_sql = ', ' . $row['ban_userid'];
				}
			}

			$sql = "SELECT u.user_id, u.username, u.user_email, u.user_lang, f.forum_name 
				FROM " . FORUMS_WATCH_TABLE . " w, " . FORUMS_TABLE . " f, " . USERS_TABLE . " u 
				WHERE w.forum_id = $forum_id 
					AND w.user_id NOT IN (" . $userdata['user_id'] . ", " . ANONYMOUS . $user_id_sql . " ) 
					AND w.notify_status = " . TOPIC_WATCH_UN_NOTIFIED . " 
					AND f.forum_id = w.forum_id 
					AND u.user_id = w.user_id";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not obtain list of forum watchers', '', __LINE__, __FILE__, $sql);
			}

			$orig_word = array();
			$replacement_word = array();
			obtain_word_list($orig_word, $replacement_word);

			include($phpbb_root_path . 'includes/emailer.'.$phpEx);
			$emailer = new emailer($board_config['smtp_delivery']);

			$script_name = preg_replace('/^\/?(.*?)\/?$/', '\1', trim($board_config['script_path']));
			$script_name_f = ( $script_name != '' ) ? $script_name . '/viewforum.'.$phpEx : 'viewforum.'.$phpEx;
			$server_name = trim($board_config['server_name']);
			$server_protocol = ( $board_config['cookie_secure'] ) ? 'https://' : 'http://';
			$server_port = ( $board_config['server_port'] <> 80 ) ? ':' . trim($board_config['server_port']) . '/' : '/';

			$email_headers = "From: " . $board_config['board_email'] . "\nReturn-Path: " . $board_config['board_email'] . "\r\n";

			$update_watched_sql = '';
			if ( $row = $db->sql_fetchrow($result) )
			{
				$forum_name = unprepare_message($row['forum_name']);

				do
				{
					if ( $row['user_email'] != '' )
					{
						$emailer->use_template('forum_notify', $row['user_lang']);
						$emailer->email_address($row['user_email']);
						$emailer->set_subject();//$lang['Topic_reply_notification']
						$emailer->extra_headers($email_headers);

						$emailer->assign_vars(array(
							'EMAIL_SIG' => str_replace('<br />', "\n", "-- \n" . $board_config['board_email_sig']),
							'USERNAME' => $row['username'],
							'SITENAME' => $board_config['sitename'],
							'FORUM_NAME' => $forum_name, 

							'U_FORUM' => $server_protocol . $server_name . $server_port . $script_name_f . '?' . POST_FORUM_URL . "=$forum_id",
							'U_STOP_WATCHING_FORUM' => $server_protocol . $server_name . $server_port . $script_name_f . '?' . POST_FORUM_URL . "=$forum_id&unwatch=forum")
						);

						$emailer->send();
						$emailer->reset();

						$update_watched_sql .= ( $update_watched_sql != '' ) ? ', ' . $row['user_id'] : $row['user_id'];
					}
				}
				while ( $row = $db->sql_fetchrow($result) );
			}

			if ( $update_watched_sql != '' )
			{
				$sql = "UPDATE " . FORUMS_WATCH_TABLE . "
					SET notify_status = " . TOPIC_WATCH_NOTIFIED . "
					WHERE forum_id = $forum_id
						AND user_id IN ($update_watched_sql)";
				$db->sql_query($sql);
			}

			if ( $mode == 'reply' )
			{
				$sql = "SELECT u.user_id, u.username, u.user_email, u.user_lang, t.topic_title 
					FROM " . TOPICS_WATCH_TABLE . " tw, " . TOPICS_TABLE . " t, " . USERS_TABLE . " u 
					WHERE tw.topic_id = $topic_id 
						AND tw.user_id NOT IN (" . $userdata['user_id'] . ", " . ANONYMOUS . $user_id_sql . " ) 
						AND tw.notify_status = " . TOPIC_WATCH_UN_NOTIFIED . " 
						AND t.topic_id = tw.topic_id 
						AND u.user_id = tw.user_id";
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Could not obtain list of topic watchers', '', __LINE__, __FILE__, $sql);
				}

				$script_name_t = ( $script_name != '' ) ? $script_name . '/viewtopic.'.$phpEx : 'viewtopic.'.$phpEx;
				$email_headers = "From: " . $board_config['board_email'] . "\nReturn-Path: " . $board_config['board_email'] . "\r\n";

				$update_watched_sql = '';
				if ( $row = $db->sql_fetchrow($result) )
				{
					$topic_title = preg_replace($orig_word, $replacement_word, unprepare_message($row['topic_title']));

					do
					{
						if ( $row['user_email'] != '' )
						{
							$emailer->use_template('topic_notify', $row['user_lang']);
							$emailer->email_address($row['user_email']);
							$emailer->set_subject();//$lang['Topic_reply_notification']
							$emailer->extra_headers($email_headers);

							$emailer->assign_vars(array(
								'EMAIL_SIG' => str_replace('<br />', "\n", "-- \n" . $board_config['board_email_sig']),
								'USERNAME' => $row['username'],
								'SITENAME' => $board_config['sitename'],
								'TOPIC_TITLE' => $topic_title, 

								'U_TOPIC' => $server_protocol . $server_name . $server_port . $script_name_t . '?' . POST_POST_URL . "=$post_id#$post_id",
								'U_STOP_WATCHING_TOPIC' => $server_protocol . $server_name . $server_port . $script_name_t . '?' . POST_TOPIC_URL . "=$topic_id&unwatch=topic")
							);

							$emailer->send();
							$emailer->reset();

							$update_watched_sql .= ( $update_watched_sql != '' ) ? ', ' . $row['user_id'] : $row['user_id'];
						}
					}
					while ( $row = $db->sql_fetchrow($result) );
				}

				if ( $update_watched_sql != '' )
				{
					$sql = "UPDATE " . TOPICS_WATCH_TABLE . "
						SET notify_status = " . TOPIC_WATCH_NOTIFIED . "
						WHERE topic_id = $topic_id
							AND user_id IN ($update_watched_sql)";
					$db->sql_query($sql);
				}
			}

		}

		$sql = "SELECT topic_id 
			FROM " . TOPICS_WATCH_TABLE . "
			WHERE topic_id = $topic_id
				AND user_id = " . $userdata['user_id'];
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not obtain topic watch information', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);

		if ( !$notify_user && !empty($row['topic_id']) )
		{
			$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . "
				WHERE topic_id = $topic_id
					AND user_id = " . $userdata['user_id'];
			if ( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, 'Could not delete topic watch information', '', __LINE__, __FILE__, $sql);
			}
		}
		else if ( $notify_user && empty($row['topic_id']) )
		{
			$sql = "INSERT INTO " . TOPICS_WATCH_TABLE . " (user_id, topic_id, notify_status)
				VALUES (" . $userdata['user_id'] . ", $topic_id, 0)";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not insert topic watch information', '', __LINE__, __FILE__, $sql);
			}
		}
	}
}

function clean_words($mode, &$entry, &$stopword_list, &$synonym_list)
{
	// Weird, $init_match doesn't work with static when double quotes (") are used...
	static $drop_char_match =   array('^', '$', '&', '(', ')', '<', '>', '`', '\'', '"', '|', ',', '@', '_', '?', '%', '-', '~', '+', '.', '[', ']', '{', '}', ':', '\\', '/', '=', '#', '\'', ';', '!');
	static $drop_char_replace = array(' ', ' ', ' ', ' ', ' ', ' ', ' ', '',  '',   ' ', ' ', ' ', ' ', '',  ' ', ' ', '',  ' ',   ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ' , ' ', ' ', ' ', ' ',  ' ', ' ');

	$entry = ' ' . strip_tags(strtolower($entry)) . ' ';

	if ( $mode == 'post' )
	{
		// Replace line endings by a space
		$entry = preg_replace('/[\n\r]/is', ' ', $entry); 
		// HTML entities like &nbsp;
		$entry = preg_replace('/\b&[a-z]+;\b/', ' ', $entry); 
		// Remove URL's
		$entry = preg_replace('/\b[a-z0-9]+:\/\/[a-z0-9\.\-]+(\/[a-z0-9\?\.%_\-\+=&\/]+)?/', ' ', $entry); 
		// Quickly remove BBcode.
		$entry = preg_replace('/\[img:[a-z0-9]{10,}\].*?\[\/img:[a-z0-9]{10,}\]/', ' ', $entry); 
		$entry = preg_replace('/\[\/?url(=.*?)?\]/', ' ', $entry);
		$entry = preg_replace('/\[\/?[a-z\*=\+\-]+(\:?[0-9a-z]+)?:[a-z0-9]{10,}(\:[a-z0-9]+)?=?.*?\]/', ' ', $entry);
	}
	else if ( $mode == 'search' ) 
	{
		$entry = str_replace('+', ' and ', $entry);
		$entry = str_replace('-', ' not ', $entry);
	}

	//
	// Filter out strange characters like ^, $, &, change "it's" to "its"
	//
	for($i = 0; $i < count($drop_char_match); $i++)
	{
		$entry =  str_replace($drop_char_match[$i], $drop_char_replace[$i], $entry);
	}

	if ( $mode == 'post' )
	{
		$entry = str_replace('*', ' ', $entry);

		// 'words' that consist of <=2 or >=20 characters are removed.
		$entry = preg_replace('/\b([a-z0-9]{1,2}|[a-z0-9]{20,})\b/',' ', $entry); 
	}

	if ( !empty($stopword_list) )
	{
		for ($j = 0; $j < count($stopword_list); $j++)
		{
			$stopword = trim($stopword_list[$j]);

			if ( $mode == 'post' || ( $stopword != 'not' && $stopword != 'and' && $stopword != 'or' ) )
			{
				$entry =  preg_replace('#\b' . preg_quote($stopword) . '\b#', ' ', $entry);
			}
		}
	}

	if ( !empty($synonym_list) )
	{
		for ($j = 0; $j < count($synonym_list); $j++)
		{
			list($replace_synonym, $match_synonym) = split(' ', trim(strtolower($synonym_list[$j])));
			if ( $mode == 'post' || ( $match_synonym != 'not' && $match_synonym != 'and' && $match_synonym != 'or' ) )
			{
				$entry =  preg_replace('#\b' . trim($match_synonym) . '\b#', ' ' . trim($replace_synonym) . ' ', $entry);
			}
		}
	}

	return $entry;
}

function split_words(&$entry, $mode = 'post')
{
	$match = ( $mode == 'post' ) ? '/\b(\w[\w\']*\w+|\w+?)\b/' : '/(\*?[\w]+\*?)|\b([\w]+)\b/';
	preg_match_all($match, $entry, $split_entries);

	return array_unique($split_entries[1]);
}

function add_search_words($post_id, $post_text, $post_title = '')
{
	global $db, $phpbb_root_path, $board_config, $lang;

	$stopwords_array = @file($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/search_stopwords.txt'); 
	$synonym_array = @file($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/search_synonyms.txt'); 

	$search_raw_words = array();
	$search_raw_words['text'] = split_words(clean_words('post', $post_text, $stopword_array, $synonym_array));
	$search_raw_words['title'] = split_words(clean_words('post', $post_title, $stopword_array, $synonym_array));

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

				if( $search_matches[$i] != '' ) 
				{
					$word[] = $search_matches[$i];
					if ( !strstr($word_insert_sql[$word_in], "'" . $search_matches[$i] . "'") )
					{
						$word_insert_sql[$word_in] .= ( $word_insert_sql[$word_in] != '' ) ? ", '" . $search_matches[$i] . "'" : "'" . $search_matches[$i] . "'";
					}
				} 
			}
		}
	}

	if ( count($word) )
	{
		sort($word);

		$prev_word = '';
		$word_text_sql = '';
		$temp_word = array();
		for($i = 0; $i < count($word); $i++)
		{
			if ( $word[$i] != $prev_word )
			{
				$temp_word[] = $word[$i];
				$word_text_sql .= ( ( $word_text_sql != '' ) ? ', ' : '' ) . "'" . $word[$i] . "'";
			}
			$prev_word = $word[$i];
		}
		$word = $temp_word;

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
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Could not select words', '', __LINE__, __FILE__, $sql);
				}

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
						if( !$db->sql_query($sql) )
						{
							message_die(GENERAL_ERROR, 'Could not insert new word', '', __LINE__, __FILE__, $sql);
						}
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

			if ( !$db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, 'Could not insert new word', '', __LINE__, __FILE__, $sql);
			}
		}
	}

	while( list($word_in, $match_sql) = @each($word_insert_sql) )
	{
		$title_match = ( $word_in == 'title' ) ? 1 : 0;

		if ( $match_sql != '' )
		{
			$sql = "INSERT INTO " . SEARCH_MATCH_TABLE . " (post_id, word_id, title_match) 
				SELECT $post_id, word_id, $title_match  
					FROM " . SEARCH_WORD_TABLE . " 
					WHERE word_text IN ($match_sql)"; 
			if ( !$db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, 'Could not insert new word matches', '', __LINE__, __FILE__, $sql);
			}
		}
	}

	if ( $mode == 'single' )
	{
		remove_common('single', 0.4, $word);
	}

	return;
}

//
// Check if specified words are too common now
//
function remove_common($mode, $fraction, $word_id_list = array())
{
	global $db;

	$sql = ( $mode == 'global' ) ? "SELECT COUNT(post_id) AS total_posts FROM " . SEARCH_MATCH_TABLE . " GROUP BY post_id" : "SELECT SUM(forum_posts) AS total_posts FROM " . FORUMS_TABLE;
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not obtain post count', '', __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);

	if ( $row['total_posts'] >= 100 )
	{
		$common_threshold = floor($row['total_posts'] * $fraction);

		if ( $mode == 'single' && count($word_id_list) )
		{
			$word_id_sql = '';
			for($i = 0; $i < count($word_id_list); $i++)
			{
				$word_id_sql .= ( ( $word_id_sql != '' ) ? ', ' : '' ) . "'" . $word_id_list[$i] . "'";
			}

			$sql = "SELECT m.word_id 
				FROM " . SEARCH_MATCH_TABLE . " m, " . SEARCH_WORD_TABLE . " w 
				WHERE w.word_text IN ($word_id_sql)  
					AND m.word_id = w.word_id 
				GROUP BY m.word_id 
				HAVING COUNT(m.word_id) > $common_threshold";
		}
		else 
		{
			$sql = "SELECT word_id 
				FROM " . SEARCH_MATCH_TABLE . " 
				GROUP BY word_id 
				HAVING COUNT(word_id) > $common_threshold";
		}

		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not obtain common word list', '', __LINE__, __FILE__, $sql);
		}

		$common_word_id = '';
		while ( $row = $db->sql_fetchrow($result) )
		{
			$common_word_id .= ( ( $common_word_id != '' ) ? ', ' : '' ) . $row['word_id'];
		}
		$db->sql_freeresult($result);

		if ( $common_word_id != '' )
		{
			$sql = "UPDATE " . SEARCH_WORD_TABLE . "
				SET word_common = " . TRUE . " 
				WHERE word_id IN ($common_word_id)";
			if ( !$db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, 'Could not delete word list entry', '', __LINE__, __FILE__, $sql);
			}

			$sql = "DELETE FROM " . SEARCH_MATCH_TABLE . "  
				WHERE word_id IN ($common_word_id)";
			if ( !$db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, 'Could not delete word match entry', '', __LINE__, __FILE__, $sql);
			}
		}
	}

	return $word_count;
}

function remove_search_post($post_id_sql)
{
	global $db;

	$words_removed = false;

	switch ( SQL_LAYER )
	{
		case 'mysql':
		case 'mysql4':
			$sql = "SELECT word_id 
				FROM " . SEARCH_MATCH_TABLE . " 
				WHERE post_id IN ($post_id_sql) 
				GROUP BY word_id";
			if ( $result = $db->sql_query($sql) )
			{
				$word_id_sql = '';
				while ( $row = $db->sql_fetchrow($result) )
				{
					$word_id_sql .= ( $word_id_sql != '' ) ? ', ' . $row['word_id'] : $row['word_id']; 
				}

				$sql = "SELECT word_id 
					FROM " . SEARCH_MATCH_TABLE . " 
					WHERE word_id IN ($word_id_sql) 
					GROUP BY word_id 
					HAVING COUNT(word_id) = 1";
				if ( $result = $db->sql_query($sql) )
				{
					$word_id_sql = '';
					while ( $row = $db->sql_fetchrow($result) )
					{
						$word_id_sql .= ( $word_id_sql != '' ) ? ', ' . $row['word_id'] : $row['word_id']; 
					}

					if ( $word_id_sql != '' )
					{
						$sql = "DELETE FROM " . SEARCH_WORD_TABLE . " 
							WHERE word_id IN ($word_id_sql)";
						if ( !$db->sql_query($sql) )
						{
							message_die(GENERAL_ERROR, 'Could not delete word list entry', '', __LINE__, __FILE__, $sql);
						}

						$words_removed = $db->sql_affectedrows();
					}
				}
			}
			break;

		default:
			$sql = "DELETE FROM " . SEARCH_WORD_TABLE . " 
				WHERE word_id IN ( 
					SELECT word_id 
					FROM " . SEARCH_MATCH_TABLE . " 
					WHERE word_id IN ( 
						SELECT word_id 
						FROM " . SEARCH_MATCH_TABLE . " 
						WHERE post_id IN ($post_id_sql) 
						GROUP BY word_id 
					) 
					GROUP BY word_id 
					HAVING COUNT(word_id) = 1
				)"; 
			if ( !$db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, 'Could not delete old words from word table', '', __LINE__, __FILE__, $sql);
			}

			$words_removed = $db->sql_affectedrows();

			break;
	}

	$sql = "DELETE FROM " . SEARCH_MATCH_TABLE . "  
		WHERE post_id IN ($post_id_sql)";
	if ( !$db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
	}

	return $words_removed;
}

//
// Fill smiley templates (or just the variables) with smileys
// Either in a window or inline
//
function generate_smilies($mode, $page_id)
{
	global $db, $session, $board_config, $template, $lang, $theme, $phpEx, $phpbb_root_path;
	global $user_ip, $starttime;
	global $userdata;

	$inline_columns = 4;
	$inline_rows = 5;
	$window_columns = 8;

	if ( $mode == 'window' )
	{
		$userdata = $session->start();
		$session->configure($userdata);

		$page_title = $lang['Review_topic'] . " - $topic_title";
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			'smiliesbody' => 'posting_smilies.tpl')
		);
	}

	$sql = "SELECT emoticon, code, smile_url   
		FROM " . SMILIES_TABLE . " 
		ORDER BY smilies_id";
	$result = $db->sql_query($sql);
	
	$num_smilies = 0;
	$rowset = array();
	while ( $row = $db->sql_fetchrow($result) )
	{
		if ( empty($rowset[$row['smile_url']]) )
		{
			$rowset[$row['smile_url']]['code'] = str_replace('\\', '\\\\', str_replace("'", "\\'", $row['code']));
			$rowset[$row['smile_url']]['emoticon'] = $row['emoticon'];
			$num_smilies++;
		}
	}

	if ( $num_smilies )
	{
		$smilies_count = ( $mode == 'inline' ) ? min(19, $num_smilies) : $num_smilies;
		$smilies_split_row = ( $mode == 'inline' ) ? $inline_columns - 1 : $window_columns - 1;

		$s_colspan = 0;
		$row = 0;
		$col = 0;

		while ( list($smile_url, $data) = @each($rowset) )
		{
			if ( !$col )
			{
				$template->assign_block_vars('smilies_row', array());
			}

			$template->assign_block_vars('smilies_row.smilies_col', array(
				'SMILEY_CODE' => $data['code'],
				'SMILEY_IMG' => $board_config['smilies_path'] . '/' . $smile_url,
				'SMILEY_DESC' => $data['emoticon'])
			);

			$s_colspan = max($s_colspan, $col + 1);

			if ( $col == $smilies_split_row )
			{
				if ( $mode == 'inline' && $row == $inline_rows - 1 )
				{
					break;
				}
				$col = 0;
				$row++;
			}
			else
			{
				$col++;
			}
		}

		if ( $mode == 'inline' && $num_smilies > $inline_rows * $inline_columns )
		{
			$template->assign_block_vars('switch_smilies_extra', array());

			$template->assign_vars(array(
				'L_MORE_SMILIES' => $lang['More_emoticons'], 
				'U_MORE_SMILIES' => "posting.$phpEx$SID&amp;mode=smilies")
			);
		}

		$template->assign_vars(array(
			'L_EMOTICONS' => $lang['Emoticons'], 
			'L_CLOSE_WINDOW' => $lang['Close_window'], 
			'S_SMILIES_COLSPAN' => $s_colspan)
		);
	}

	if ( $mode == 'window' )
	{
		$template->display('smiliesbody');

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
}

?>