<?php
/***************************************************************************
 *                                 modcp.php
 *                            -------------------
 *   begin                : July 4, 2001
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

/**
 * Moderator Control Panel
 *
 * From this 'Control Panel' the moderator of a forum will be able to do
 * mass topic operations (locking/unlocking/moving/deleteing), and it will
 * provide an interface to do quick locking/unlocking/moving/deleting of
 * topics via the moderator operations buttons on all of the viewtopic pages.
 */

$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/bbcode.'.$phpEx);

//
// Obtain initial var settings
//
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

$confirm = ( $HTTP_POST_VARS['confirm'] ) ? TRUE : FALSE;
$cancel = ( $HTTP_POST_VARS['cancel'] ) ? TRUE : FALSE;

//
// Check if user did or did not confirm
// If they did not, forward them to the last page they were on
//
if( $cancel )
{
	if( $topic_id )
	{
		$redirect = "viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id";
	}
	else if( $forum_id )
	{
		$redirect = "viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id";
	}
	else
	{
		$redirect = "index.$phpEx";
	}
	header("Location: " . append_sid($redirect));
}

//
// Continue var definitions
//
$start = ( isset($HTTP_GET_VARS['start']) ) ? $HTTP_GET_VARS['start'] : 0;

$delete = ($HTTP_POST_VARS['delete']) ? TRUE : FALSE;
$move = ($HTTP_POST_VARS['move']) ? TRUE : FALSE;
$lock = ($HTTP_POST_VARS['lock']) ? TRUE : FALSE;
$unlock = ($HTTP_POST_VARS['unlock']) ? TRUE : FALSE;

if( isset($HTTP_POST_VARS['mode']) || isset($HTTP_GET_VARS['mode']) )
{
	$mode = ( isset($HTTP_POST_VARS['mode']) ) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
}
else
{
	if($delete)
	{
		$mode = 'delete';
	}
	else if($move)
	{
		$mode = 'move';
	}
	else if($lock)
	{
		$mode = 'lock';
	}
	else if($unlock)
	{
		$mode = 'unlock';
	}
	else
	{
		$mode = "";
	}
}

//
// Obtain relevant data
//
if( $topic_id )
{
	$sql = "SELECT f.forum_id, f.forum_name, f.forum_topics
		FROM " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f
		WHERE t.topic_id = " . $topic_id . "
			AND f.forum_id = t.forum_id";
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_MESSAGE, $lang['Topic_post_not_exist'], "", __LINE__, __FILE__, $sql);
	}
	$topic_row = $db->sql_fetchrow($result);

	$forum_topics = $topic_row['forum_topics'];
	$forum_id = $topic_row['forum_id'];
	$forum_name = $topic_row['forum_name'];
}
else if( $forum_id )
{
	$sql = "SELECT forum_name, forum_topics
		FROM " . FORUMS_TABLE . "
		WHERE forum_id = " . $forum_id;
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_MESSAGE, $lang['Topic_post_not_exist'], "", __LINE__, __FILE__, $sql);
	}
	$topic_row = $db->sql_fetchrow($result);

	$forum_topics = $topic_row['forum_topics'];
	$forum_name = $topic_row['forum_name'];
}

//
// Start session management
//
$userdata = session_pagestart($user_ip, $forum_id, $session_length);
init_userprefs($userdata);
//
// End session management
//

//
// Start auth check
//
$is_auth = auth(AUTH_ALL, $forum_id, $userdata);

if( !$is_auth['auth_mod'] )
{
	message_die(GENERAL_MESSAGE, $lang['Not_Moderator'], $lang['Not_Authorised']);
}
//
// End Auth Check
//

//
// Load page header
//
$page_title = $lang['Mod_CP'];
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->assign_vars(array(
	"FORUM_NAME" => $forum_name,

	"U_VIEW_FORUM" => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id"))
);

//
// Do major work ...
//
switch($mode)
{
	case 'delete':
		if($confirm)
		{
			$topics = ( isset($HTTP_POST_VARS['topic_id_list']) ) ?  $HTTP_POST_VARS['topic_id_list'] : array($topic_id);

			$topic_id_sql = "";
			for($i = 0; $i < count($topics); $i++)
			{
				if( $topic_id_sql != "" )
				{
					$topic_id_sql .= ", ";
				}
				$topic_id_sql .= $topics[$i];
			}

			$sql = "SELECT post_id 
				FROM " . POSTS_TABLE . " 
				WHERE topic_id IN ($topic_id_sql)";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not get post id information", "", __LINE__, __FILE__, $sql);
			}
			$rowset = $db->sql_fetchrowset($result);

			$post_id_sql = "";
			for($i = 0; $i < count($rowset); $i++)
			{
				if( $post_id_sql != "" )
				{
					$post_id_sql .= ", ";
				}
				$post_id_sql .= $rowset[$i]['post_id'];
			}

			$sql = "SELECT vote_id 
				FROM " . VOTE_DESC_TABLE . " 
				WHERE topic_id IN ($topic_id_sql)";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not get vote id information", "", __LINE__, __FILE__, $sql);
			}
			$rowset = $db->sql_fetchrowset($result);

			$vote_id_sql = "";
			for($i = 0; $i < count($rowset); $i++)
			{
				if( $vote_id_sql != "" )
				{
					$vote_id_sql .= ", ";
				}
				$vote_id_sql .= $rowset[$i]['vote_id'];
			}

			//
			// Got all required info so go ahead and start deleting everything
			//
			$sql = "DELETE 
				FROM " . TOPICS_TABLE . " 
				WHERE topic_id IN ($topic_id_sql) 
					OR topic_moved_id IN ($topic_id_sql)";
			if( !$result = $db->sql_query($sql, BEGIN_TRANSACTION) )
			{
				message_die(GENERAL_ERROR, "Could not delete topics", "", __LINE__, __FILE__, $sql);
			}

			$sql = "DELETE 
				FROM " . POSTS_TABLE . " 
				WHERE post_id IN ($post_id_sql)";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not delete posts", "", __LINE__, __FILE__, $sql);
			}

			$sql = "DELETE 
				FROM " . POSTS_TEXT_TABLE . " 
				WHERE post_id IN ($post_id_sql)";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not delete posts text", "", __LINE__, __FILE__, $sql);
			}

			if( $vote_id_sql != "" )
			{
				$sql = "DELETE 
					FROM " . VOTE_DESC_TABLE . " 
					WHERE vote_id IN ($vote_id_sql)";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Could not delete vote descriptions", "", __LINE__, __FILE__, $sql);
				}

				$sql = "DELETE 
					FROM " . VOTE_RESULTS_TABLE . " 
					WHERE vote_id IN ($vote_id_sql)";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Could not delete vote results", "", __LINE__, __FILE__, $sql);
				}

				$sql = "DELETE 
					FROM " . VOTE_USERS_TABLE . " 
					WHERE vote_id IN ($vote_id_sql)";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Could not delete vote users", "", __LINE__, __FILE__, $sql);
				}
			}

			$sql = "DELETE 
				FROM " . TOPICS_WATCH_TABLE . " 
				WHERE topic_id IN ($topic_id_sql)";
			if( !$result = $db->sql_query($sql, END_TRANSACTION) )
			{
				message_die(GENERAL_ERROR, "Could not delete watched post list", "", __LINE__, __FILE__, $sql);
			}

			sync("forum", $forum_id);

			if( !empty($topic_id) )
			{
				$next_page = "viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id";
				$return_message = $lang['to_return_forum'];
			}
			else
			{
				$next_page = "modcp.$phpEx?" . POST_FORUM_URL . "=$forum_id";
				$return_message = $lang['Return_to_modcp'];
			}

			$message = $lang['Topics_Removed'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid($next_page) . "\">" . $lang['HERE'] . "</a> " . $return_message;
			message_die(GENERAL_MESSAGE, $message);
		}
		else
		{
			if( empty($HTTP_POST_VARS['topic_id_list']) && empty($topic_id) )
			{
				message_die(GENERAL_MESSAGE, $lang['None_selected'], "");
			}

			$hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '"><input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '">';

			if( isset($HTTP_POST_VARS['topic_id_list']) )
			{
				$topics = $HTTP_POST_VARS['topic_id_list'];
				for($i = 0; $i < count($topics); $i++)
				{
					$hidden_fields .= '<input type="hidden" name="topic_id_list[]" value="' . $topics[$i] . '">';
				}
			}
			else
			{
				$hidden_fields .= '<input type="hidden" name="' . POST_TOPIC_URL . '" value="' . $topic_id . '">';
			}

			//
			// Set template files
			//
			$template->set_filenames(array(
				"confirm" => "confirm_body.tpl")
			);

			$template->assign_vars(array(
				"MESSAGE_TITLE" => $lang['Confirm'],
				"MESSAGE_TEXT" => $lang['Confirm_delete_topic'],

				"L_YES" => $lang['Yes'],
				"L_NO" => $lang['No'],

				"S_CONFIRM_ACTION" => append_sid("modcp.$phpEx"),
				"S_HIDDEN_FIELDS" => $hidden_fields)
			);

			$template->pparse("confirm");

			include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
		}
		break;

	case 'move':
		if( $confirm )
		{
			$new_forum_id = $HTTP_POST_VARS['new_forum'];
			$old_forum_id = $forum_id;
			$topics = ( isset($HTTP_POST_VARS['topic_id_list']) ) ?  $HTTP_POST_VARS['topic_id_list'] : array($topic_id);

			$topic_list = "";
			for($i = 0; $i < count($topics); $i++)
			{
				if( $topic_list != "" )
				{
					$topic_list .= ", ";
				}
				$topic_list .= $topics[$i];
			}

			$sql_select = "SELECT * 
				FROM " . TOPICS_TABLE . " 
				WHERE topic_id IN ($topic_list)";

			if( !$result = $db->sql_query($sql_select, BEGIN_TRANSACTION) )
			{
				message_die(GENERAL_ERROR, "Could not select from topic table!", "Error", __LINE__, __FILE__, $sql_select);
			}

			$row = $db->sql_fetchrowset($result);

			for($i = 0; $i < count($row); $i++)
			{
				$topic_id = $row[$i]['topic_id'];

				$sql = "INSERT INTO " . TOPICS_TABLE . " (forum_id, topic_title, topic_poster, topic_time, topic_status, topic_type, topic_vote, topic_views, topic_replies, topic_last_post_id, topic_moved_id)
					VALUES ($old_forum_id, '" . addslashes($row[$i]['topic_title']) . "', '" . $row[$i]['topic_poster'] . "', " . $row[$i]['topic_time'] . ", " . TOPIC_MOVED . ", " . POST_NORMAL . ", " . $row[$i]['topic_vote'] . ", " . $row[$i]['topic_views'] . ", " . $row[$i]['topic_replies'] . ", " . $row[$i]['topic_last_post_id'] . ", $topic_id)";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Could not insert new topic", "Error", __LINE__, __FILE__, $sql);
				}

				$sql = "UPDATE " . TOPICS_TABLE . " 
					SET forum_id = $new_forum_id  
					WHERE topic_id = $topic_id";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Could not update old topic", "Error", __LINE__, __FILE__, $sql);
				}

				$sql = "UPDATE " . POSTS_TABLE . " 
					SET forum_id = $new_forum_id 
					WHERE topic_id = $topic_id";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Could not update post topic ids", "Error", __LINE__, __FILE__, $sql);
				}
			}

			// Sync the forum indexes
			sync("forum", $new_forum_id);
			sync("forum", $old_forum_id);

			if( !empty($topic_id) )
			{
				$next_page = "viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id";
				$return_message = $lang['to_return_topic'];
			}
			else
			{
				$next_page = "modcp.$phpEx?" . POST_FORUM_URL . "=$old_forum_id";
				$return_message = $lang['Return_to_modcp'];
			}

			$message = $lang['Topics_Moved'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid($next_page) . "\">" . $lang['HERE'] . "</a> " . $return_message;

			message_die(GENERAL_MESSAGE, $message);
		}
		else
		{
			if( empty($HTTP_POST_VARS['topic_id_list']) && empty($topic_id) )
			{
				message_die(GENERAL_MESSAGE, $lang['None_selected']);
			}

			$hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '"><input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '">';

			if( isset($HTTP_POST_VARS['topic_id_list']) )
			{
				$topics = $HTTP_POST_VARS['topic_id_list'];
				for($i = 0; $i < count($topics); $i++)
				{
					$hidden_fields .= '<input type="hidden" name="topic_id_list[]" value="' . $topics[$i] . '">';
				}
			}
			else
			{
				$hidden_fields .= '<input type="hidden" name="' . POST_TOPIC_URL . '" value="' . $topic_id . '">';
			}

			//
			// Set template files
			//
			$template->set_filenames(array(
				"movetopic" => "modcp_move.tpl")
			);

			$template->assign_vars(array(
				"MESSAGE_TITLE" => $lang['Confirm'],
				"MESSAGE_TEXT" => $lang['Confirm_move_topic'],

				"L_MOVE_TO_FORUM" => $lang['Move_to_forum'], 
				"L_YES" => $lang['Yes'],
				"L_NO" => $lang['No'],

				"S_FORUM_BOX" => make_forum_select("new_forum"), 
				"S_MODCP_ACTION" => append_sid("modcp.$phpEx"),
				"S_HIDDEN_FIELDS" => $hidden_fields)
			);
			$template->pparse("movetopic");

			include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

		}
		break;

	case 'lock':
		if($confirm)
		{
			$topics = ( isset($HTTP_POST_VARS['topic_id_list']) ) ?  $HTTP_POST_VARS['topic_id_list'] : array($topic_id);

			$topic_id_sql = "";
			for($i = 0; $i < count($topics); $i++)
			{
				if( $topic_id_sql != "")
				{
					$topic_id_sql .= ", ";
				}
				$topic_id_sql .= $topics[$i];
			}

			$sql = "UPDATE " . TOPICS_TABLE . " 
				SET topic_status = " . TOPIC_LOCKED . " 
				WHERE topic_id IN ($topic_id_sql)";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Coule not update topics table!", "Error", __LINE__, __FILE__, $sql);
			}

			if( !empty($topic_id) )
			{
				$next_page = "viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id";
				$return_message = $lang['to_return_topic'];
			}
			else
			{
				$next_page = "modcp.$phpEx?" . POST_FORUM_URL . "=$forum_id";
				$return_message = $lang['Return_to_modcp'];
			}

			$message = $lang['Topics_Locked'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid($next_page)."\">" . $lang['HERE'] . "</a> " . $return_message;

			message_die(GENERAL_MESSAGE, $message);
		}
		else
		{
			if( empty($HTTP_POST_VARS['topic_id_list']) && empty($topic_id) )
			{
				message_die(GENERAL_MESSAGE, $lang['None_selected'], $lang['Error']);
			}

			$hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '"><input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '">';

			if( isset($HTTP_POST_VARS['topic_id_list']) )
			{
				$topics = $HTTP_POST_VARS['topic_id_list'];
				for($i = 0; $i < count($topics); $i++)
				{
					$hidden_fields .= '<input type="hidden" name="topic_id_list[]" value="' . $topics[$i] . '">';
				}
			}
			else
			{
				$hidden_fields .= '<input type="hidden" name="' . POST_TOPIC_URL . '" value="' . $topic_id . '">';
			}

			//
			// Set template files
			//
			$template->set_filenames(array(
				"confirm" => "confirm_body.tpl")
			);

			$template->assign_vars(array(
				"MESSAGE_TITLE" => $lang['Confirm'],
				"MESSAGE_TEXT" => $lang['Confirm_lock_topic'],

				"L_YES" => $lang['Yes'],
				"L_NO" => $lang['No'],

				"S_CONFIRM_ACTION" => append_sid("modcp.$phpEx"),
				"S_HIDDEN_FIELDS" => $hidden_fields)
			);

			$template->pparse("confirm");

			include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
		}
		break;

	case 'unlock':
		if($confirm)
		{
			$topics = ( isset($HTTP_POST_VARS['topic_id_list']) ) ?  $HTTP_POST_VARS['topic_id_list'] : array($topic_id);

			$topic_id_sql = "";
			for($i = 0; $i < count($topics); $i++)
			{
				if( $topic_id_sql != "")
				{
					$topic_id_sql .= ", ";
				}
				$topic_id_sql .= $topics[$i];
			}

			$sql = "UPDATE " . TOPICS_TABLE . " 
				SET topic_status = " . TOPIC_UNLOCKED . " 
				WHERE topic_id IN ($topic_id_sql)";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not update topics table!", "Error", __LINE__, __FILE__, $sql);
			}

			if( !empty($topic_id) )
			{
				$next_page = "viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id";
				$return_message = $lang['to_return_topic'];
			}
			else
			{
				$next_page = "modcp.$phpEx?" . POST_FORUM_URL . "=$forum_id";
				$return_message = $lang['Return_to_modcp'];
			}

			$msg = $lang['Topics_Unlocked'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid($next_page) . "\">" . $lang['HERE'] . "</a> " . $return_message;

			message_die(GENERAL_MESSAGE, $msg);
		}
		else
		{
			if( empty($HTTP_POST_VARS['topic_id_list']) && empty($topic_id) )
			{
				message_die(GENERAL_MESSAGE, $lang['None_selected'], $lang['Error']);
			}

			$hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '"><input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '">';

			if( isset($HTTP_POST_VARS['topic_id_list']) )
			{
				$topics = $HTTP_POST_VARS['topic_id_list'];
				for($i = 0; $i < count($topics); $i++)
				{
					$hidden_fields .= '<input type="hidden" name="topic_id_list[]" value="' . $topics[$i] . '">';
				}
			}
			else
			{
				$hidden_fields .= '<input type="hidden" name="' . POST_TOPIC_URL . '" value="' . $topic_id . '">';
			}

			//
			// Set template files
			//
			$template->set_filenames(array(
				"confirm" => "confirm_body.tpl")
			);

			$template->assign_vars(array(
				"MESSAGE_TITLE" => $lang['Confirm'],
				"MESSAGE_TEXT" => $lang['Confirm_unlock_topic'],

				"L_YES" => $lang['Yes'],
				"L_NO" => $lang['No'],

				"S_CONFIRM_ACTION" => append_sid("modcp.$phpEx"),
				"S_HIDDEN_FIELDS" => $hidden_fields)
			);
			$template->pparse("confirm");

			include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
		}

	break;

	case 'split':
		if( $HTTP_POST_VARS['split_type_all'] || $HTTP_POST_VARS['split_type_beyond'] )
		{
			$posts = $HTTP_POST_VARS['post_id_list'];

			$sql = "SELECT poster_id, topic_id, post_time
				FROM " . POSTS_TABLE . "
				WHERE post_id = " . $posts[0];
			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not get post information", "", __LINE__, __FILE__, $sql);
			}

			$post_rowset = $db->sql_fetchrow($result);
			$first_poster = $post_rowset['poster_id'];
			$topic_id = $post_rowset['topic_id'];
			$post_time = $post_rowset['post_time'];

			$post_subject = trim(strip_tags($HTTP_POST_VARS['subject']));
			if( empty($subject) )
			{
				message_die(GENERAL_MESSAGE, $lang['Empty_subject']);
			}

			$new_forum_id = $HTTP_POST_VARS['new_forum_id'];
			$topic_time = time();

			$sql  = "INSERT INTO " . TOPICS_TABLE . "
				(topic_title, topic_poster, topic_time, forum_id, topic_status, topic_type)
				VALUES ('$post_subject', $first_poster, " . $topic_time . ", $new_forum_id, " . TOPIC_UNLOCKED . ", " . POST_NORMAL . ")";
			if(!$result = $db->sql_query($sql, BEGIN_TRANSACTION))
			{
				message_die(GENERAL_ERROR, "Could not insert new topic", "", __LINE__, __FILE__, $sql);
			}

			$new_topic_id = $db->sql_nextid();

			if($HTTP_POST_VARS['split_type_all'])
			{
				$post_id_sql = "";
				for($i = 0; $i < count($posts); $i++)
				{
					if( $post_id_sql != "" )
					{
						$post_id_sql .= ", ";
					}
					$post_id_sql .= $posts[$i];
				}

				$sql = "UPDATE " . POSTS_TABLE . "
					SET topic_id = $new_topic_id
					WHERE post_id IN ($post_id_sql)";
			}
			else if($HTTP_POST_VARS['split_type_beyond'])
			{
				$sql = "UPDATE " . POSTS_TABLE . "
					SET topic_id = $new_topic_id
					WHERE post_time > $post_time
						AND topic_id = $topic_id";
			}

			if( !$result = $db->sql_query($sql, END_TRANSACTION) )
			{
				message_die(GENERAL_ERROR, "Could not update posts table!", "", __LINE__, __FILE__, $sql);
			}

			sync("topic", $new_topic_id);
			sync("topic", $topic_id);
			sync("forum", $forum_id);

			$message = $lang['Topic_split'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$new_topic_id") . "\">" . $lang['Here'] . "</a> " . $lang['to_return_topic'];
			message_die(GENERAL_MESSAGE, $message);
		}
		else
		{
			//
			// Set template files
			//
			$template->set_filenames(array(
				"split_body" => "modcp_split.tpl")
			);

			$sql = "SELECT u.username, p.*, pt.post_text, pt.post_subject, p.post_username
				FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . POSTS_TEXT_TABLE . " pt
				WHERE p.topic_id = $topic_id
					AND p.poster_id = u.user_id
					AND p.post_id = pt.post_id
				ORDER BY p.post_time ASC";
			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not get topic/post information", "", __LINE__, __FILE__, $sql);
			}

			$s_hidden_fields = '<input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '"><input type="hidden" name="mode" value="split">';

			if( ( $total_posts = $db->sql_numrows($result) ) > 0 )
			{
				$postrow = $db->sql_fetchrowset($result);

				$template->assign_vars(array(
					"L_SPLIT_TOPIC" => $lang['Split_Topic'],
					"L_SPLIT_TOPIC_EXPLAIN" => $lang['Split_Topic_explain'],
					"L_AUTHOR" => $lang['Author'],
					"L_MESSAGE" => $lang['Message'],
					"L_SELECT" => $lang['Select'],
					"L_SPLIT_SUBJECT" => $lang['Split_title'],
					"L_SPLIT_FORUM" => $lang['Split_forum'],
					"L_POSTED" => $lang['Posted'],
					"L_SPLIT_POSTS" => $lang['Split_posts'],
					"L_SUBMIT" => $lang['Submit'],
					"L_SPLIT_AFTER" => $lang['Split_after'], 
					"L_POST_SUBJECT" => $lang['Post_subject'], 

					"S_SPLIT_ACTION" => append_sid("modcp.$phpEx"),
					"S_HIDDEN_FIELDS" => $s_hidden_fields,

					"FORUM_INPUT" => make_forum_select("new_forum_id", $forum_id))
				);

				for($i = 0; $i < $total_posts; $i++)
				{
					$post_id = $postrow[$i]['post_id'];
					$poster_id = $postrow[$i]['user_id'];
					$poster = $postrow[$i]['username'];

					$post_date = create_date($board_config['default_dateformat'], $postrow[$i]['post_time'], $board_config['board_timezone']);

					$bbcode_uid = $postrow[$i]['bbcode_uid'];
					$message = $postrow[$i]['post_text'];
					$post_subject = ( $postrow[$i]['post_subject'] != "" ) ? $postrow[$i]['post_subject'] : $topic_title;

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

					if( $board_config['allow_bbcode'] && $bbcode_uid != "" )
					{
						$message = bbencode_second_pass($message, $bbcode_uid);
					}
					else if( !$board_config['allow_bbcode'] && $bbcode != "" )
					{
						$message = preg_replace("/\:[0-9a-z\:]+\]/si", "]", $message);
					}

					//
					// Define censored word matches
					//
					$orig_word = array();
					$replacement_word = array();
					obtain_word_list($orig_word, $replacement_word);

					if( count($orig_word) )
					{
						$post_subject = preg_replace($orig_word, $replacement_word, $post_subject);
						$message = preg_replace($orig_word, $replacement_word, $message);
					}

					$message = make_clickable($message);

					if($board_config['allow_smilies'] && $postrow[$i]['enable_smilies'])
					{
						$message = smilies_pass($message);
					}

					$message = str_replace("\n", "<br />", $message);
					
					$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
					$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

					$template->assign_block_vars("postrow", array(
						"ROW_COLOR" => "#" . $row_color,
						"ROW_CLASS" => $row_class,
						"POSTER_NAME" => $poster,
						"POST_DATE" => $post_date,
						"POST_SUBJECT" => $post_subject,
						"MESSAGE" => $message,
						"POST_ID" => $post_id)
					);
				}

				$template->pparse("split_body");
			}
		}
		break;

	case 'ip':
		$rdns_ip_num = ( isset($HTTP_GET_VARS['rdns']) ) ? $HTTP_GET_VARS['rdns'] : "";

		if( !$post_id )
		{
			message_die(GENERAL_ERROR, "Error, no post id found", "Error", __LINE__, __FILE__);
		}

		//
		// Set template files
		//
		$template->set_filenames(array(
			"viewip" => "modcp_viewip.tpl")
		);

		// Look up relevent data for this post
		$sql = "SELECT poster_ip, poster_id 
			FROM " . POSTS_TABLE . " 
			WHERE post_id = $post_id";
		if(!$result = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Could not get poster IP information", "Error", __LINE__, __FILE__, $sql);
		}

		$post_row = $db->sql_fetchrow($result);

		$ip_this_post = decode_ip($post_row['poster_ip']);
		$ip_this_post = ( $rdns_ip_num == $ip_this_post ) ? gethostbyaddr($ip_this_post) : $ip_this_post;

		$poster_id = $post_row['poster_id'];

		$template->assign_vars(array(
			"L_IP_INFO" => $lang['IP_info'],
			"L_THIS_POST_IP" => $lang['This_posts_IP'],
			"L_OTHER_IPS" => $lang['Other_IP_this_user'],
			"L_OTHER_USERS" => $lang['Users_this_IP'],
			"L_SEARCH_POSTS" => $lang['Search_user_posts'], 
			"L_LOOKUP_IP" => $lang['Lookup_IP'], 

			"SEARCH_IMG" => $images['icon_search'], 

			"IP" => $ip_this_post, 
				
			"U_LOOKUP_IP" => append_sid("modcp.$phpEx?mode=ip&" . POST_POST_URL . "=$post_id&amp;" . POST_TOPIC_URL . "=$topic_id&amp;rdns=" . $ip_this_post))
		);

		//
		// Get other IP's this user has posted under
		//
		$sql = "SELECT DISTINCT poster_ip 
			FROM " . POSTS_TABLE . " 
			WHERE poster_id = $poster_id 
				AND poster_ip <> '" . $post_row['poster_ip'] . "' 
			ORDER BY poster_ip DESC";
		if(!$result = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Could not get IP information for this user", "Error", __LINE__, __FILE__, $sql);
		}

		$poster_ips = $db->sql_fetchrowset($result);
		for($i = 0; $i < count($poster_ips); $i++)
		{
			$ip = decode_ip($poster_ips[$i]['poster_ip']);
			$ip = ( $rdns_ip_num == $ip ) ? gethostbyaddr($ip) : $ip;

			$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
			$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

			$template->assign_block_vars("iprow", array(
				"ROW_COLOR" => "#" . $row_color, 
				"ROW_CLASS" => $row_class, 
				"IP" => $ip, 

				"U_LOOKUP_IP" => append_sid("modcp.$phpEx?mode=ip&" . POST_POST_URL . "=$post_id&amp;" . POST_TOPIC_URL . "=$topic_id&amp;rdns=" . $ip))
			);
		}

		//
		// Get other users who've posted under this IP
		//
		$sql = "SELECT DISTINCT u.username, u.user_id 
			FROM " . USERS_TABLE ." u, " . POSTS_TABLE . " p 
			WHERE p.poster_id = u.user_id 
				AND p.poster_ip = '" . $post_row['poster_ip'] . "'";
		if(!$result = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Could not get posters information based on IP", "Error", __LINE__, __FILE__, $sql);
		}

		$poster_ids = $db->sql_fetchrowset($result);
		for($i = 0; $i < count($poster_ids); $i++)
		{
			$id = $poster_ids[$i]['user_id'];
			$username = ( $is == ANONYMOUS ) ? $lang['Guest'] : $poster_ids[$i]['username'];

			$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
			$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

			$template->assign_block_vars("userrow", array(
				"USERNAME" => $username,
				"ROW_COLOR" => "#" . $row_color, 
				"ROW_CLASS" => $row_class, 

				"U_PROFILE" => append_sid("profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=$id"),
				"U_SEARCHPOSTS" => append_sid("search.$phpEx?a=" . urlencode($username) . "&amp;f=all&amp;b=0&amp;d=DESC&amp;c=100&amp;dosearch=1"))
			);
		}

		$template->pparse("viewip");

		break;

	case 'auth':
		break;

	default:
		$template->assign_vars(array(
			"L_MOD_CP" => $lang['Mod_CP'],
			"L_MOD_CP_EXPLAIN" => $lang['Mod_CP_explain'],
			"L_SELECT" => $lang['Select'],
			"L_DELETE" => $lang['Delete'],
			"L_MOVE" => $lang['Move'],
			"L_LOCK" => $lang['Lock'],
			"L_UNLOCK" => $lang['Unlock'],

			"S_HIDDEN_FIELDS" => "<input type=\"hidden\" name=\"" . POST_FORUM_URL . "\" value=\"$forum_id\">",
			"S_MODCP_ACTION" => append_sid("modcp.$phpEx"))
		);

		$sql = "SELECT t.*, u.username, u.user_id, p.post_time
			FROM " . TOPICS_TABLE . " t, " . USERS_TABLE . " u, " . POSTS_TABLE . " p
			WHERE t.forum_id = $forum_id
				AND t.topic_poster = u.user_id
				AND p.post_id = t.topic_last_post_id
				AND t.topic_type <> " . TOPIC_MOVED . "
			ORDER BY t.topic_type DESC, p.post_time DESC
			LIMIT $start, " . $board_config['topics_per_page'];

		if(!$t_result = $db->sql_query($sql))
		{
	   		message_die(GENERAL_ERROR, "Couldn't obtain topic information", "", __LINE__, __FILE__, $sql);
		}
		$topic_rowset = $db->sql_fetchrowset($t_result);

		//
		// Set template files
		//
		$template->set_filenames(array(
			"body" => "modcp_body.tpl")
		);

		//
		// Define censored word matches
		//
		$orig_word = array();
		$replacement_word = array();
		obtain_word_list($orig_word, $replacement_word);

		for($i = 0; $i < count($topic_rowset); $i++)
		{
			$topic_title = "";

			if( $topic_rowset[$i]['topic_status'] == TOPIC_LOCKED )
			{
				$folder_image = "<img src=\"" . $images['folder_locked'] . "\" alt=\"Topic Locked\">";
			}
			else
			{
				$folder_image = "<img src=\"" . $images['folder'] . "\">";
			}

			$topic_id = $topic_rowset[$i]['topic_id'];

			if( $topic_rowset[$i]['topic_type'] == POST_STICKY )
			{
				$topic_type = $lang['Topic_Sticky'] . " ";
			}
			else if( $topic_rowset[$i]['topic_type'] == POST_ANNOUNCE )
			{
				$topic_type = $lang['Topic_Announcement'] . " ";
			}
			else
			{
				$topic_type = "";
			}

			if( $topic_rowset[$i]['topic_vote'] )
			{
				$topic_type .= $lang['Topic_Poll'] . " ";
			}

			$topic_title = $topic_rowset[$i]['topic_title'];
			if( count($orig_word) )
			{
				$topic_title = preg_replace($orig_word, $replacement_word, $topic_title);
			}

			$u_view_topic = append_sid("modcp.$phpEx?mode=split&amp;" . POST_TOPIC_URL . "=$topic_id");
			$topic_replies = $topic_rowset[$i]['topic_replies'];

			$last_post_time = create_date($board_config['default_dateformat'], $topic_rowset[$i]['post_time'], $board_config['board_timezone']);

			$template->assign_block_vars("topicrow", array(
				"U_VIEW_TOPIC" => $u_view_topic,

				"FOLDER_IMG" => $folder_image, 
				"TOPIC_TYPE" => $topic_type, 
				"TOPIC_TITLE" => $topic_title,
				"REPLIES" => $topic_replies,
				"LAST_POST" => $last_post_time,
				"TOPIC_ID" => $topic_id)
			);
		}

		$pagination = generate_pagination("modcp.$phpEx?" . POST_FORUM_URL . "=$forum_id", $forum_topics, $board_config['topics_per_page'], $start);

		$template->assign_vars(array(
			"PAGINATION" => $pagination,
			"FORUM_ID" => $forum_id,
			"POST_FORUM_URL" => POST_FORUM_URL,
			"ON_PAGE" => (floor($start/$board_config['topics_per_page'])+1),
			"TOTAL_PAGES" => ceil($forum_topics/$board_config['topics_per_page']),

			"L_OF" => $lang['of'],
			"L_PAGE" => $lang['Page'],
			"L_GOTO_PAGE" => $lang['Goto_page'])
		);

		$template->pparse("body");

		break;
}

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>