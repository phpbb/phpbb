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

$pagetype = "modcp";
$page_title = "Modertator Control Panel";

$forum_id = ($HTTP_POST_VARS[POST_FORUM_URL]) ? $HTTP_POST_VARS[POST_FORUM_URL] : $HTTP_GET_VARS[POST_FORUM_URL];
$topic_id = ($HTTP_POST_VARS[POST_TOPIC_URL]) ? $HTTP_POST_VARS[POST_TOPIC_URL] : $HTTP_GET_VARS[POST_TOPIC_URL];



if(empty($forum_id) || !isset($forum_id))
{
	$sql = "SELECT f.forum_id, f.forum_name, f.forum_topics
		FROM " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f
		WHERE t.topic_id = " . $topic_id . "
			AND f.forum_id = t.forum_id";
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_MESSAGE, $lang['Topic_post_not_exist'], "", __LINE__, __FILE__, $sql);
	}
	$topic_row = $db->sql_fetchrowset($result);

	$forum_topics = $topic_row[0]['forum_topics'];
	$forum_id = $topic_row[0]['forum_id'];
	$forum_name = $topic_row[0]['forum_name'];
}
else
{
	$sql = "SELECT forum_name, forum_topics
		FROM " . FORUMS_TABLE . "
		WHERE forum_id = " . $forum_id;
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_MESSAGE, $lang['Topic_post_not_exist'], "", __LINE__, __FILE__, $sql);
	}
	$topic_row = $db->sql_fetchrowset($result);

	$forum_topics = $topic_row[0]['forum_topics'];
	$forum_name = $topic_row[0]['forum_name'];
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
//
// End Auth Check
//

$is_mod = ( $is_auth['auth_mod'] ) ? TRUE : FALSE;

if( !$is_mod )
{
	message_die(GENERAL_MESSAGE, $lang['Not_Moderator'], $lang['Not_Authorised']);
}


//
// Check if user did or did not confirm
// If they did not, forward them to the last page they were on
//
$confirm = ($HTTP_POST_VARS['confirm']) ? TRUE : FALSE;
if($HTTP_POST_VARS['not_confirm'])
{
	header("Location: index.$phpEx");
}

include($phpbb_root_path . 'includes/page_header.'.$phpEx);

// Set template files
$template->set_filenames(array(
	"body" => "modcp_body.tpl",
	"confirm" => "confirm_body.tpl",
	"split_body" => "split_body.tpl")
);

$template->assign_vars(array(
	"FORUM_NAME" => $forum_name,

	"U_VIEW_FORUM" => "viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id")
);

$mode = ($HTTP_POST_VARS['mode']) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
$quick_op = ($HTTP_POST_VARS['quick_op']) ? $HTTP_POST_VARS['quick_op'] : $HTTP_GET_VARS['quick_op'];

$delete = ($HTTP_POST_VARS['delete']) ? TRUE : FALSE;
$move = ($HTTP_POST_VARS['move']) ? TRUE : FALSE;
$lock = ($HTTP_POST_VARS['lock']) ? TRUE : FALSE;
$unlock = ($HTTP_POST_VARS['unlock']) ? TRUE : FALSE;

if(!$mode)
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
}

switch($mode)
{
	case 'delete':
		if($confirm)
		{
			if($HTTP_POST_VARS['preform_op'])
			{
				$topics = $HTTP_POST_VARS['preform_op'];
			}
			else
			{
				$topics = array($HTTP_POST_VARS[POST_TOPIC_URL]);
			}

			$sql = "SELECT post_id FROM ".POSTS_TABLE." WHERE ";
			$delete_topics = "DELETE FROM ".TOPICS_TABLE." WHERE ";
			$moved_topics = "DELETE FROM ".TOPICS_TABLE. " WHERE ";
			for($x = 0; $x < count($topics); $x++)
			{
				if($x > 0)
				{
					$sql .= " OR ";
					$delete_topics .= " OR ";
					$moved_topics .= " OR ";
				}
				$sql .= "topic_id = ".$topics[$x];
				$delete_topics .= "topic_id = ".$topics[$x];
				$moved_topics .= "topic_moved_id = ".$topics[$x];
			}
			$topics_removed = $x;

			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not get posts lists for deletion!", "Error", __LINE__, __FILE__, $sql);
			}
			$num_posts = $db->sql_numrows($result);
			$rowset = $db->sql_fetchrowset($result);
			$delete_posts = "DELETE FROM ".POSTS_TABLE." WHERE ";
			$delete_text = "DELETE FROM ".POSTS_TEXT_TABLE." WHERE ";
			for($x = 0; $x < $num_posts; $x++)
			{
				if($x > 0)
				{
					$delete_posts .= " OR ";
					$delete_text .= " OR ";
				}
				$delete_posts .= "post_id = ".$rowset[$x]['post_id'];
				$delete_text .= "post_id = ".$rowset[$x]['post_id'];
			}
			$posts_removed = $x;

			if(!$result = $db->sql_query($delete_text, BEGIN_TRANSACTION))
			{
				message_die(GENERAL_ERROR, "Could not delete posts text!", "Error", __LINE__, __FILE__, $delete_text);
			}

			if(!$result = $db->sql_query($delete_posts))
			{
				message_die(GENERAL_ERROR, "Could not delete posts!", "Error", __LINE__, __FILE__, $delete_posts);
			}

			if(!$result = $db->sql_query($delete_topics))
			{
				message_die(GENERAL_ERROR, "Could not delete topics!", "Error", __LINE__, __FILE__, $delete_topics);
			}

			if(!$result = $db->sql_query($moved_topics))
			{
				message_die(GENERAL_ERROR, "Could not delete moved topics!", "Error", __LINE__, __FILE__, $moved_topics);
			}

			sync("forum",$forum_id);

			if($quick_op)
			{
				$next_page = "viewforum.$phpEx?".POST_FORUM_URL."=$forum_id";
				$return_message = $lang['to_return_forum'];
			}
			else
			{
				$next_page = "modcp.$phpEx?".POST_FORUM_URL."=$forum_id";
				$return_message = $lang['Return_to_modcp'];
			}

			$msg = $lang['Topics_Removed'] . "<br />" . "<a href=\"".append_sid($next_page)."\">". $lang['Click'] . " " . $lang['Here'] ."</a> " . $return_message;
			message_die(GENERAL_MESSAGE, $msg);
		}
		else
		{
			if(empty($HTTP_POST_VARS['preform_op']) && empty($topic_id))
			{
				message_die(GENERAL_MESSAGE, $lang['None_selected'], $lang['Error']);
			}
			$hidden_fields = '<input type="hidden" name="mode" value="'.$mode.'"><input type="hidden" name="'.POST_FORUM_URL.'" value="'.$forum_id.'"><input type="hidden" name="quick_op" value="'.$quick_op.'">';
			if($HTTP_POST_VARS['preform_op'])
			{
				$topics = $HTTP_POST_VARS['preform_op'];
				for($x = 0; $x < count($topics); $x++)
				{
					$hidden_fields .= '<input type="hidden" name="preform_op[]" value="'.$topics[$x].'">';
				}
			}
			else
			{
				$hidden_fields .= '<input type="hidden" name="'.POST_TOPIC_URL.'" value="'.$topic_id.'">';
			}

			$template->assign_vars(array("MESSAGE_TITLE" => $lang['Confirm'],
												  "MESSAGE_TEXT" => $lang['Confirm_delete_topic'],
												  "L_YES" => $lang['Yes'],
												  "L_NO" => $lang['No'],
												  "S_CONFIRM_ACTION" => append_sid("modcp.$phpEx"),
												  "S_HIDDEN_FIELDS" => $hidden_fields));
			$template->pparse("confirm");
			include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
		}
		break;

	case 'move':
		if($confirm)
		{
			$new_forum = $HTTP_POST_VARS['new_forum'];
			$old_forum = $HTTP_POST_VARS[POST_FORUM_URL];
			if($HTTP_POST_VARS['preform_op'])
			{
				$topics = $HTTP_POST_VARS['preform_op'];
			}
			else
			{
				$topics = array($HTTP_POST_VARS[POST_TOPIC_URL]);
			}
			for($x = 0; $x < count($topics); $x++)
			{
				if($x != 0)
				{
					$sql_clause .= ' OR ';
				}
				$sql_clause .= 'topic_id = '.$topics[$x];
				$sql_select = 'SELECT
									topic_title,
									topic_poster,
									topic_status,
									topic_time
									FROM '.
									TOPICS_TABLE." WHERE
									topic_id = $topics[$x]";
				if(!$result = $db->sql_query($sql_select))
				{
					message_die(GENERAL_ERROR, "Could not select from topic table!", "Error", __LINE__, __FILE__, $sql_select);
				}
				else
				{
					$row = $db->sql_fetchrowset($result);

					$ttitle = $row[0]['topic_title'];
					$tpost = $row[0]['topic_poster'];
					$ttime = $row[0]['topic_time'];
					$sql_insert = 'INSERT INTO '.TOPICS_TABLE."
										(forum_id, topic_title, topic_poster, topic_time, topic_moved_id, topic_status)
										VALUES
										($old_forum, '$ttitle', '$tpost', $ttime, $topics[$x], ".TOPIC_MOVED.')';
					if(!$result = $db->sql_query($sql_insert))
					{
						message_die(GENERAL_ERROR, "Could not insert into topics table!", "Error", __LINE__, __FILE__, $sql_insert);
					}
					$newtopic_id = $db->sql_nextid();
					$sql_insert = 'INSERT INTO '.POSTS_TABLE."
										(topic_id,forum_id,poster_id,post_time)
										VALUES
										($newtopic_id,$old_forum,$tpost,$ttime)";
					if(!$result = $db->sql_query($sql_insert))
					{
						message_die(GENERAL_ERROR, "Could not insert into posts table!", "Error", __LINE__, __FILE__, $sql_insert);
					}

					//Finally, update the last_post_id column to reflect the new post just inserted
					$newpost_id = $db->sql_nextid();
					$sql = 'UPDATE '.TOPICS_TABLE." SET topic_last_post_id = $newpost_id WHERE topic_id = $newtopic_id";
					if(!$result = $db->sql_query($sql))
					{
						message_die(GENERAL_ERROR, "Could not update the topics table!", "Error", __LINE__, __FILE__, $sql);
					}
				}
			}

			$sql_replies = 'SELECT SUM(topic_replies) AS total_posts FROM '.TOPICS_TABLE.' WHERE '.$sql_clause;
			if(!$result = $db->sql_query($sql_replies))
			{
				message_die(GENERAL_ERROR, "Could not sum topic replies in topics table!", "Error", __LINE__, __FILE__, $sql_replies);
			}
			else
			{
				$posts_row = $db->sql_fetchrowset($result);
				$posts = $posts_row[0]['total_posts'] + count($topics);
			}

			$sql_post = 'UPDATE '.POSTS_TABLE." SET forum_id = $new_forum WHERE $sql_clause";
			$sql_topic = 'UPDATE '.TOPICS_TABLE." SET forum_id = $new_forum WHERE $sql_clause";
			if(!$result = $db->sql_query($sql_post))
			{
				message_die(GENERAL_ERROR, "Could not update posts table!", "Error", __LINE__, __FILE__, $sql_post);
			}
			else if(!$result = $db->sql_query($sql_topic))
			{
				message_die(GENERAL_ERROR, "Could not update topics table!", "Error", __LINE__, __FILE__, $sql_topic);
			}

			// Sync the forum indexes
			sync("forum", $new_forum);
			sync("forum", $old_forum);


			if($quick_op)
			{
				$next_page = "viewtopic.$phpEx?".POST_TOPIC_URL."=$topic_id";
				$return_message = $lang['to_return_topic'];
			}
			else
			{
				$next_page = "modcp.$phpEx?".POST_FORUM_URL."=$forum_id";
				$return_message = $lang['Return_to_modcp'];
			}
			$msg = $lang['Topics_Moved'] . "<br />" . "<a href=\"".append_sid($next_page)."\">". $lang['Click']. " " . $lang['Here'] ."</a> " . $return_message;
			message_die(GENERAL_MESSAGE, $msg);
		}
		else
		{
			if(empty($HTTP_POST_VARS['preform_op']) && empty($topic_id))
			{
				message_die(GENERAL_MESSAGE, $lang['None_selected'], $lang['Error']);
			}
			$hidden_fields = '<input type="hidden" name="mode" value="'.$mode.'"><input type="hidden" name="'.POST_FORUM_URL.'" value="'.$forum_id.'"><input type="hidden" name="quick_op" value="'.$quick_op.'">';
			$hidden_fields .= $lang['New_forum'] . ':  ' . make_forum_box('new_forum'). '</select><br><br>';
			if($HTTP_POST_VARS['preform_op'])
			{
				$topics = $HTTP_POST_VARS['preform_op'];
				for($x = 0; $x < count($topics); $x++)
				{
					$hidden_fields .= '<input type="hidden" name="preform_op[]" value="'.$topics[$x].'">';
				}
			}
			else
			{
				$hidden_fields .= '<input type="hidden" name="'.POST_TOPIC_URL.'" value="'.$topic_id.'">';
			}
			$template->assign_vars(array("MESSAGE_TITLE" => $lang['Confirm'],
													"MESSAGE_TEXT" => $lang['Confirm_move_topic'],
													"L_YES" => $lang['Yes'],
													"L_NO" => $lang['No'],
													"S_CONFIRM_ACTION" => append_sid("modcp.$phpEx"),
													"S_HIDDEN_FIELDS" => $hidden_fields));
			$template->pparse("confirm");
			include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
		}
	break;

	case 'lock':
		if($confirm)
		{
			if($HTTP_POST_VARS['preform_op'])
			{
				$topics = $HTTP_POST_VARS['preform_op'];
			}
			else
			{
				$topics = array($HTTP_POST_VARS[POST_TOPIC_URL]);
			}

			$sql = "UPDATE " . TOPICS_TABLE . " SET topic_status = " . TOPIC_LOCKED . " WHERE ";
			for($x = 0; $x < count($topics); $x++)
			{
				if($x > 0)
				{
					$sql .= " OR ";
				}
				$sql .= "topic_id = " . $topics[$x];
			}

			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Coule not update topics table!", "Error", __LINE__, __FILE__, $sql);
			}
			else
			{
				if($quick_op)
				{
					$next_page = "viewtopic.$phpEx?".POST_TOPIC_URL."=$topic_id";
					$return_message = $lang['to_return_topic'];
				}
				else
				{
					$next_page = "modcp.$phpEx?".POST_FORUM_URL."=$forum_id";
					$return_message = $lang['Return_to_modcp'];
				}
				$msg = $lang['Topics_Locked'] . "<br />" . "<a href=\"".append_sid($next_page)."\">". $lang['Click'] . " " . $lang['Here'] ."</a> " . $return_message;
				message_die(GENERAL_MESSAGE, $msg);
			}
		}
		else
		{
			if(empty($HTTP_POST_VARS['preform_op']) && empty($topic_id))
			{
				message_die(GENERAL_MESSAGE, $lang['None_selected'], $lang['Error']);
			}
			$hidden_fields = '<input type="hidden" name="mode" value="'.$mode.'"><input type="hidden" name="'.POST_FORUM_URL.'" value="'.$forum_id.'"><input type="hidden" name="quick_op" value="'.$quick_op.'">';
			if($HTTP_POST_VARS['preform_op'])
			{
				$topics = $HTTP_POST_VARS['preform_op'];
				for($x = 0; $x < count($topics); $x++)
				{
					$hidden_fields .= '<input type="hidden" name="preform_op[]" value="'.$topics[$x].'">';
				}
			}
			else
			{
				$hidden_fields .= '<input type="hidden" name="'.POST_TOPIC_URL.'" value="'.$topic_id.'">';
			}

			$template->assign_vars(array("MESSAGE_TITLE" => $lang['Confirm'],
				"MESSAGE_TEXT" => $lang['Confirm_lock_topic'],
												  "L_YES" => $lang['Yes'],
												  "L_NO" => $lang['No'],
												  "S_CONFIRM_ACTION" => append_sid("modcp.$phpEx"),
												  "S_HIDDEN_FIELDS" => $hidden_fields));
			$template->pparse("confirm");
			include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
		}

	break;

	case 'unlock':
		if($confirm)
		{
			if($HTTP_POST_VARS['preform_op'])
			{
				$topics = $HTTP_POST_VARS['preform_op'];
			}
			else
			{
				$topics = array($HTTP_POST_VARS[POST_TOPIC_URL]);
			}

			$sql = "UPDATE " . TOPICS_TABLE . " SET topic_status = " . TOPIC_UNLOCKED . " WHERE ";
			for($x = 0; $x < count($topics); $x++)
			{
				if($x > 0)
				{
					$sql .= " OR ";
				}
				$sql .= "topic_id = " . $topics[$x];
			}

			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not update topics table!", "Error", __LINE__, __FILE__, $sql);
			}
			else
			{
				if($quick_op)
				{
					$next_page = "viewtopic.$phpEx?".POST_TOPIC_URL."=$topic_id";
					$return_message = $lang['to_return_topic'];
				}
				else
				{
					$next_page = "modcp.$phpEx?".POST_FORUM_URL."=$forum_id";
					$return_message = $lang['Return_to_modcp'];
				}

				$msg = $lang['Topics_Unlocked'] . "<br />" . "<a href=\"".append_sid($next_page)."\">". $lang['Click'] . " " . $lang['Here'] ."</a> " . $return_message;

				message_die(GENERAL_MESSAGE, $msg);
			}
		}
		else
		{
			if(empty($HTTP_POST_VARS['preform_op']) && empty($topic_id))
			{
				message_die(GENERAL_MESSAGE, $lang['None_selected'], $lang['Error']);
			}

			$hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '"><input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '"><input type="hidden" name="quick_op" value="' . $quick_op . '">';

			if($HTTP_POST_VARS['preform_op'])
			{
				$topics = $HTTP_POST_VARS['preform_op'];

				for($x = 0; $x < count($topics); $x++)
				{
					$hidden_fields .= '<input type="hidden" name="preform_op[]" value="'.$topics[$x].'">';
				}
			}
			else
			{
				$hidden_fields .= '<input type="hidden" name="'.POST_TOPIC_URL.'" value="'.$topic_id.'">';
			}

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
		if($HTTP_POST_VARS['split_type_all'] || $HTTP_POST_VARS['split_type_beyond'])
		{
			$posts = $HTTP_POST_VARS['preform_op'];

			$sql = "SELECT poster_id, topic_id, post_time
				FROM " . POSTS_TABLE . "
				WHERE post_id = ".$posts[0];
			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not get post information", "", __LINE__, __FILE__, $sql);
			}

			$post_rowset = $db->sql_fetchrowset($result);
			$first_poster = $post_rowset[0]['poster_id'];
			$topic_id = $post_rowset[0]['topic_id'];
			$post_time = $post_rowset[0]['post_time'];

			$subject = trim(strip_tags(htmlspecialchars(addslashes($HTTP_POST_VARS['subject']))));
			if(empty($subject))
			{
				message_die(GENERAL_MESSAGE, $lang['Empty_subject']);
			}

			$new_forum_id = $HTTP_POST_VARS['new_forum_id'];
			$topic_time = get_gmt_ts();

			$sql  = "INSERT INTO " . TOPICS_TABLE . "
				(topic_title, topic_poster, topic_time, forum_id, topic_notify, topic_status, topic_type)
				VALUES ('$subject', $first_poster, " . $topic_time . ", $new_forum_id, 0, " . TOPIC_UNLOCKED . ", " . POST_NORMAL . ")";
			if(!$result = $db->sql_query($sql, BEGIN_TRANSACTION))
			{
				message_die(GENERAL_ERROR, "Could not insert new topic", "", __LINE__, __FILE__, $sql);
			}

			$new_topic_id = $db->sql_nextid();

			if($HTTP_POST_VARS['split_type_all'])
			{
				$sql = "UPDATE " . POSTS_TABLE . "
					SET topic_id = $new_topic_id
					WHERE ";

				for($x = 0; $x < count($posts); $x++)
				{
					if($x > 0)
					{
						$sql .= " OR ";
					}
					$sql .= "post_id = " . $posts[$x];
					$last_post_id = $posts[$x];
				}
			}
			else if($HTTP_POST_VARS['split_type_beyond'])
			{
				$sql = "UPDATE " . POSTS_TABLE . "
					SET topic_id = $new_topic_id
					WHERE post_time >= $post_time
						AND topic_id = $topic_id";
			}

			if(!$result = $db->sql_query($sql, END_TRANSACTION))
			{
				message_die(GENERAL_ERROR, "Could not update posts table!", "", __LINE__, __FILE__, $sql);
			}
			else
			{
				sync("topic", $new_topic_id);
				sync("topic", $topic_id);
				sync("forum", $forum_id);

				$next_page = "viewtopic.$phpEx?" . POST_TOPIC_URL . "=$new_topic_id";
				$return_message = $lang['to_return_topic'];

				message_die(GENERAL_MESSAGE, $lang['Topic_split'] . "<br /><a href=\"" . append_sid($next_page)."\">" . $lang['Click'] . " " . $lang['Here'] ."</a> " . $return_message);
			}
		}
		else
		{
			$topic_id = ($HTTP_POST_VARS[POST_TOPIC_URL]) ? $HTTP_POST_VARS[POST_TOPIC_URL] : $HTTP_GET_VARS[POST_TOPIC_URL];

			$sql = "SELECT u.username, p.post_time, p.post_id, p.bbcode_uid, pt.post_text, pt.post_subject, p.post_username
				FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . POSTS_TEXT_TABLE . " pt
				WHERE p.topic_id = $topic_id
					AND p.poster_id = u.user_id
					AND p.post_id = pt.post_id
				ORDER BY p.post_time ASC";
			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not get topic/post information", "", __LINE__, __FILE__, $sql);
			}

			$s_hidden_fields = "<input type=\"hidden\" name=\"" . POST_FORUM_URL . "\" value=\"$forum_id\"><input type=\"hidden\" name=\"mode\" value=\"split\">";

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

					"S_SPLIT_ACTION" => append_sid("modcp.$phpEx"),
					"S_HIDDEN_FIELDS" => $s_hidden_fields,

					"FORUM_INPUT" => make_forum_box("new_forum_id", $forum_id))
				);

				for($i = 0; $i < $total_posts; $i++)
				{
					$post_id = $postrow[$i]['post_id'];
					$poster_id = $postrow[$i]['user_id'];
					$poster = stripslashes($postrow[$i]['username']);

					$post_date = create_date($board_config['default_dateformat'], $postrow[$i]['post_time'], $board_config['default_timezone']);

					if($poster_id == ANONYMOUS && $postrow[$i]['post_username'] != '')
					{
						$poster = stripslashes($postrow[$i]['post_username']);
					}
					$post_subject = ($postrow[$i]['post_subject'] != "") ? stripslashes($postrow[$i]['post_subject']) : "";

					$bbcode_uid = $postrow[$i]['bbcode_uid'];

					$user_sig = stripslashes($postrow[$i]['user_sig']);
					$message = stripslashes($postrow[$i]['post_text']);

					if(!$board_config['allow_html'])
					{
						$user_sig = strip_tags($user_sig);
						$message = strip_tags($message);
					}

					if($board_config['allow_bbcode'])
					{
						// do bbcode stuff here
						$sig_uid = make_bbcode_uid();
						$user_sig = bbencode_first_pass($user_sig, $sig_uid);
						$user_sig = bbencode_second_pass($user_sig, $sig_uid);

						$message = bbencode_second_pass($message, $bbcode_uid);
					}

					$message = make_clickable($message);
					$message = str_replace("\n", "<br />", $message);
					$message = eregi_replace("\[addsig]$", "", $message);

					//$message = (strlen($message) > 100) ? substr($message, 0, 100) . " ..." : $message;

					if(!($i % 2))
					{
						$color = "#" . $theme['td_color1'];
					}
					else
					{
						$color = "#" . $theme['td_color2'];
					}

					$template->assign_block_vars("postrow", array(
						"POSTER_NAME" => $poster,
						"POST_DATE" => $post_date,
						"POST_SUBJECT" => $post_subject,
						"MESSAGE" => $message,
						"POST_ID" => $post_id,

						"ROW_COLOR" => $color)
					);
				}

				$template->pparse("split_body");
			}
		}
	break;
	case 'ip':
			$post_id = $HTTP_GET_VARS[POST_POST_URL];
			if(!$post_id)
			{
				message_die(GENERAL_ERROR, "Error, no post id found", "Error", __LINE__, __FILE__);
			}

			// Look up relevent data for this post
			$sql = "SELECT poster_ip, poster_id, post_username FROM ".POSTS_TABLE." WHERE post_id = $post_id";
			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not get poster IP information", "Error", __LINE__, __FILE__, $sql);
			}

			$post_row = $db->sql_fetchrow($result);

			// Get other users who've posted under this IP
			$sql = "SELECT u.username, u.user_id FROM " . USERS_TABLE ." u, " . POSTS_TABLE . " p WHERE p.poster_id = u.user_id AND p.poster_ip = '".$post_row['poster_ip']."'";
			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not get posters information based on IP", "Error", __LINE__, __FILE__, $sql);
			}

			$poster_ids = $db->sql_fetchrowset($result);
			sort($poster_ids);

			$posts = 0;
			while(list($null, $userdata) = each($poster_ids))
			{
				$username = $userdata['username'];
				$user_id = $userdata['user_id'];

				if($username != $last_username && !empty($last_username))
				{
					$other_users[] = array("username" => "$last_username", "user_id" => "$last_user_id", "posts" => "$posts");
					$posts = 1;
				}
				else
				{
					$posts += 1;
				}
				$last_username = $username;
				$last_user_ip = $user_id;
			}

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

		if(!$start)
		{
			$start = 0;
		}

		$sql = "SELECT t.topic_title, t.topic_id, t.topic_replies, t.topic_status, t.topic_type, u.username, u.user_id, p.post_time
			FROM " . TOPICS_TABLE . " t, " . USERS_TABLE . " u, " . POSTS_TABLE . " p
			WHERE t.forum_id = $forum_id
				AND t.topic_poster = u.user_id
				AND p.post_id = t.topic_last_post_id
				AND t.topic_type <> " . POST_GLOBAL_ANNOUNCE . "
			ORDER BY t.topic_type DESC, p.post_time DESC
			LIMIT $start, " . $board_config['topics_per_page'];

		if(!$t_result = $db->sql_query($sql))
		{
	   		message_die(GENERAL_ERROR, "Couldn't obtain topic information", "", __LINE__, __FILE__, $sql);
		}
		$total_topics = $db->sql_numrows($t_result);
		$topics = $db->sql_fetchrowset($t_result);

		for($x = 0; $x < $total_topics; $x++)
		{
			$topic_title = "";
			if($topics[$x]['topic_status'] == TOPIC_LOCKED)
			{
				$folder_image = "<img src=\"" . $images['folder_locked'] . "\" alt=\"Topic Locked\">";
			}
			else if($topics[$x]['topic_status'] == TOPIC_MOVED)
			{
				$topic_title = "<b>" . $lang['Topic_Moved'] . ":</b> ";
			}
			else
			{
				$folder_image = "<img src=\"" . $images['folder'] . "\">";
			}

			$topic_id = $topics[$x]['topic_id'];

			if($topics[$x]['topic_type'] == POST_STICKY)
			{
				$topic_title = "<b>".$lang['Post_Sticky'] . ":</b> ";
			}
			else if($topics[$x]['topic_type'] == POST_ANNOUNCE)
			{
				$topic_title = "<b>" . $lang['Post_Announcement'] . ":</b> ";
			}

			$topic_title .= stripslashes($topics[$x]['topic_title']);
			$u_view_topic = append_sid("viewtopic.$phpEx?".POST_TOPIC_URL."=$topic_id");
			$topic_replies = $topics[$x]['topic_replies'];

			$last_post_time = create_date($board_config['default_dateformat'], $topics[$x]['post_time'], $board_config['default_timezone']);


			$template->assign_block_vars("topicrow", array(
				"U_VIEW_TOPIC" => $u_view_topic,

				"FOLDER_IMG" => $folder_image,
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