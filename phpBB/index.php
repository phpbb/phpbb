<?php
/***************************************************************************
 *                                index.php
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

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_INDEX, $session_length);
init_userprefs($userdata);
//
// End session management
//

$viewcat = (!empty($HTTP_GET_VARS['viewcat'])) ? $HTTP_GET_VARS['viewcat'] : -1;

if( isset($HTTP_GET_VARS['mark']) || isset($HTTP_POST_VARS['mark']) )
{
	$mark_read = ( isset($HTTP_POST_VARS['mark']) ) ? $HTTP_POST_VARS['mark'] : $HTTP_GET_VARS['mark'];
}
else
{
	$mark_read = "";
}

//
// Handle marking posts
//
if( $mark_read == "forums" )
{

	$sql = "SELECT f.forum_id, t.topic_id 
		FROM " . FORUMS_TABLE . " f, " . TOPICS_TABLE . " t, " . POSTS_TABLE . " p
		WHERE t.forum_id = f.forum_id
			AND p.post_id = t.topic_last_post_id
			AND p.post_time > " . $userdata['session_last_visit'] . " 
			AND t.topic_moved_id IS NULL";
	if(!$t_result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Could not query new topic information", "", __LINE__, __FILE__, $sql);
	}

	if( $mark_read_rows = $db->sql_numrows($t_result) )
	{
		$mark_read_list = $db->sql_fetchrowset($t_result);

		for($i = 0; $i < $mark_read_rows; $i++ )
		{
			$forum_id = $mark_read_list[$i]['forum_id'];
			$topic_id = $mark_read_list[$i]['topic_id'];

			if( empty($HTTP_COOKIE_VARS['phpbb2_' . $forum_id . '_' . $topic_id]) )
			{
				setcookie('phpbb2_' . $forum_id . '_' . $topic_id, time(), 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
			}
			else
			{
				if( isset($HTTP_COOKIE_VARS['phpbb2_' . $forum_id . '_' . $topic_id]) )
				{
					setcookie('phpbb2_' . $forum_id . '_' . $topic_id, time(), 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
				}
			}
		}
	}

	$template->assign_vars(array(
		"META" => '<meta http-equiv="refresh" content="3;url=index.' . $phpEx . '">')
	);

	$message = $lang['Forums_marked_read'] . "<br /><br />" . $lang['Click'] . " <a href=\"index.$phpEx\">" . $lang['HERE'] . "</a> " . $lang['to_return_index'];

	message_die(GENERAL_MESSAGE, $message);
}
//
// End handle marking posts
//

//
// If you don't use these stats on your index
// you may want to consider removing them since
// it will reduce the number of queries speeding
// up page generation a little
//
$total_posts = get_db_stat('postcount');
$total_users = get_db_stat('usercount');
$total_topics = get_db_stat('topiccount');
$newest_userdata = get_db_stat('newestuser');
$newest_user = $newest_userdata['username'];
$newest_uid = $newest_userdata['user_id'];

//
// Start page proper
//
$sql = "SELECT c.cat_id, c.cat_title, c.cat_order
	FROM " . CATEGORIES_TABLE . " c, " . FORUMS_TABLE . " f
	WHERE f.cat_id = c.cat_id
	GROUP BY c.cat_id, c.cat_title, c.cat_order
	ORDER BY c.cat_order";
if(!$q_categories = $db->sql_query($sql))
{
	message_die(GENERAL_ERROR, "Could not query categories list", "", __LINE__, __FILE__, $sql);
}

if($total_categories = $db->sql_numrows($q_categories))
{
	$category_rows = $db->sql_fetchrowset($q_categories);

	$limit_forums = "";

	//
	// Define appropriate SQL
	//
	switch(SQL_LAYER)
	{
		case 'postgresql':
			$limit_forums = ($viewcat != -1) ? "AND f.cat_id = $viewcat " : "";

			$sql = "SELECT f.*, p.post_time, u.username, u.user_id 
				FROM " . FORUMS_TABLE . " f, " . POSTS_TABLE . " p, " . USERS_TABLE . " u
				WHERE p.post_id = f.forum_last_post_id 
					AND u.user_id = p.poster_id  
					$limit_forums
					UNION (
						SELECT f.*, NULL, NULL, NULL
						FROM " . FORUMS_TABLE . " f
						WHERE NOT EXISTS (
							SELECT p.post_time
							FROM " . POSTS_TABLE . " p
							WHERE p.post_id = f.forum_last_post_id  
						)
							$limit_forums
					)";
			break;

		case 'oracle':
			$limit_forums = ($viewcat != -1) ? "AND f.cat_id = $viewcat " : "";

			$sql = "SELECT f.*, p.post_time, u.username, u.user_id 
				FROM " . FORUMS_TABLE . " f, " . POSTS_TABLE . " p, " . USERS_TABLE . " u
				WHERE p.post_id = f.forum_last_post_id(+)
					AND u.user_id = p.poster_id(+)
					$limit_forums
				ORDER BY f.cat_id, f.forum_order";
			break;

		default:
			$limit_forums = ($viewcat != -1) ? "WHERE f.cat_id = $viewcat " : "";

			$sql = "SELECT f.*, p.post_time, u.username, u.user_id
				FROM (( " . FORUMS_TABLE . " f
				LEFT JOIN " . POSTS_TABLE . " p ON p.post_id = f.forum_last_post_id )
				LEFT JOIN " . USERS_TABLE . " u ON u.user_id = p.poster_id )
				$limit_forums
				ORDER BY f.cat_id, f.forum_order";
			break;
	}

	if(!$q_forums = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Could not query forums information", "", __LINE__, __FILE__, $sql);
	}
	if( !$total_forums = $db->sql_numrows($q_forums) )
	{
		message_die(GENERAL_MESSAGE, $lang['No_forums']);
	}
	$forum_rows = $db->sql_fetchrowset($q_forums);

	$sql = "SELECT f.forum_id, t.topic_id, p.post_time
		FROM " . FORUMS_TABLE . " f, " . TOPICS_TABLE . " t, " . POSTS_TABLE . " p
		WHERE t.forum_id = f.forum_id
			AND p.post_id = t.topic_last_post_id
			AND p.post_time > " . $userdata['session_last_visit'] . " 
			AND t.topic_moved_id IS NULL";
	if(!$new_topic_ids = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Could not query new topic information", "", __LINE__, __FILE__, $sql);
	}

	while( $topic_data = $db->sql_fetchrow($new_topic_ids) )
	{
		$new_topic_data[$topic_data['forum_id']][$topic_data['topic_id']] = $topic_data['post_time'];
	}

	//
	// Obtain list of moderators of each forum
	//
	$sql = "SELECT aa.forum_id, g.group_name, g.group_id, g.group_single_user, u.user_id, u.username
		FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g, " . USERS_TABLE . " u
		WHERE aa.auth_mod = " . TRUE . "
			AND ug.group_id = aa.group_id
			AND g.group_id = aa.group_id
			AND u.user_id = ug.user_id
		ORDER BY aa.forum_id, g.group_id, u.user_id";
	if(!$q_forum_mods = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Could not query forum moderator information", "", __LINE__, __FILE__, $sql);
	}
	$forum_mods_list = $db->sql_fetchrowset($q_forum_mods);

	for($i = 0; $i < count($forum_mods_list); $i++)
	{
		if($forum_mods_list[$i]['group_single_user'] || !$forum_mods_list[$i]['group_id'])
		{
			$forum_mods_single_user[$forum_mods_list[$i]['forum_id']][] = 1;

			$forum_mods_name[$forum_mods_list[$i]['forum_id']][] = $forum_mods_list[$i]['username'];
			$forum_mods_id[$forum_mods_list[$i]['forum_id']][] = $forum_mods_list[$i]['user_id'];
		}
		else
		{
			$forum_mods_single_user[$forum_mods_list[$i]['forum_id']][] = 0;

			$forum_mods_name[$forum_mods_list[$i]['forum_id']][] = $forum_mods_list[$i]['group_name'];
			$forum_mods_id[$forum_mods_list[$i]['forum_id']][] = $forum_mods_list[$i]['group_id'];
		}
	}

	//
	// Find which forums are visible for this user
	//
	$is_auth_ary = array();
	$is_auth_ary = auth(AUTH_VIEW, AUTH_LIST_ALL, $userdata, $forum_rows);

	$template->set_filenames(array(
		"body" => "index_body.tpl")
	);

	$template->assign_vars(array(
		"TOTAL_POSTS" => $total_posts,
		"TOTAL_USERS" => $total_users,
		"TOTAL_TOPICS" => $total_topics,
		"NEWEST_USER" => $newest_user,
		"NEWEST_UID" => $newest_uid,
		"USERS_BROWSING" => $users_browsing,

		"L_FORUM_LOCKED" => $lang['Forum_is_locked'],
		"L_MARK_FORUMS_READ" => $lang['Mark_all_forums'], 
		"L_SEARCH_NEW" => $lang['Search_new'], 

		"U_SEARCH_NEW" => append_sid("search.$phpEx?search_id=newposts"), 
		"U_MARK_READ" => append_sid("index.$phpEx?mark=forums"),
		"U_NEWEST_USER_PROFILE" => append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$newest_uid"))
	);

	//
	// Okay, let's build the index
	//
	$gen_cat = array();

	for($i = 0; $i < $total_categories; $i++)
	{
		$cat_id = $category_rows[$i]['cat_id'];

		$count = 0;

		for($j = 0; $j < $total_forums; $j++)
		{
			$forum_id = $forum_rows[$j]['forum_id'];

			if( $is_auth_ary[$forum_id]['auth_view'] && ( ($forum_rows[$j]['cat_id'] == $cat_id && $viewcat == -1) || $cat_id == $viewcat) )
			{
				if(!$gen_cat[$cat_id])
				{
					$template->assign_block_vars("catrow", array(
						"CAT_ID" => $cat_id,
						"CAT_DESC" => $category_rows[$i]['cat_title'],
						"U_VIEWCAT" => append_sid("index.$phpEx?viewcat=$cat_id"))
					);
					$gen_cat[$cat_id] = 1;
				}

				if($forum_rows[$j]['forum_status'] == FORUM_LOCKED)
				{
					$folder_image = "<img src=\"" . $images['forum_locked'] . "\" alt=\"" . $lang['Forum_locked'] . "\" />";
				}
				else
				{
					$unread_topics = false;
					if( count($new_topic_data[$forum_id]) )
					{
						while( list($check_topic_id, $check_post_time) = each($new_topic_data[$forum_id]) )
						{
							if( !isset($HTTP_COOKIE_VARS['phpbb2_' . $forum_id . '_' . $check_topic_id]) )
							{
								$unread_topics = true;
							}
							else
							{
								if($HTTP_COOKIE_VARS['phpbb2_' . $forum_id . '_' . $check_topic_id] < $check_post_time )
								{
									$unread_topics = true;
								}
							}
						}
					}

					$folder_image = ( $unread_topics ) ? "<img src=\"" . $images['forum_new'] . "\" alt=\"" . $lang['New_posts'] . "\" />" : "<img src=\"" . $images['forum'] . "\" alt=\"" . $lang['No_new_posts'] . "\" />";
				}

				$posts = $forum_rows[$j]['forum_posts'];
				$topics = $forum_rows[$j]['forum_topics'];

				if($forum_rows[$j]['username'] != "" && $forum_rows[$j]['post_time'] > 0)
				{
					$last_post_time = create_date($board_config['default_dateformat'], $forum_rows[$j]['post_time'], $board_config['board_timezone']);

					$last_post = $last_post_time . "<br />" . $lang['by'] . " ";
					$last_post .= ( $forum_rows[$j]['user_id'] == ANONYMOUS ) ? $forum_rows[$j]['username'] . " " : "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "="  . $forum_rows[$j]['user_id']) . "\">" . $forum_rows[$j]['username'] . "</a> ";

					$last_post .= "<a href=\"" . append_sid("viewtopic.$phpEx?"  . POST_POST_URL . "=" . $forum_rows[$j]['forum_last_post_id']) . "#" . $forum_rows[$j]['forum_last_post_id'] . "\"><img src=\"" . $images['icon_latest_reply'] . "\" border=\"0\" alt=\"" . $lang['View_latest_post'] . "\" /></a>";
				}
				else
				{
					$last_post = $lang['No_Posts'];
				}

				$mod_count = 0;
				$moderators_links = "";
				for($mods = 0; $mods < count($forum_mods_name[$forum_id]); $mods++)
				{
					if( !strstr($moderators_links, $forum_mods_name[$forum_id][$mods]) )
					{
						if($mods > 0)
						{
							$moderators_links .= ", ";
						}

						if( !($mod_count % 2) && $mod_count != 0 )
						{
							$moderators_links .= "<br />";
						}

						if( $forum_mods_single_user[$forum_id][$mods])
						{
							$moderators_links .= "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $forum_mods_id[$forum_id][$mods]) . "\">" . $forum_mods_name[$forum_id][$mods] . "</a>";
						}
						else
						{
							$moderators_links .= "<a href=\"" . append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=" . $forum_mods_id[$forum_id][$mods]) . "\">" . $forum_mods_name[$forum_id][$mods] . "</a>";
						}

						$mod_count++;
					}
				}

				if($moderators_links == "")
				{
					$moderators_links = "&nbsp;";
				}

				$row_color = ( !($count%2) ) ? $theme['td_color1'] : $theme['td_color2'];
				$row_class = ( !($count%2) ) ? $theme['td_class1'] : $theme['td_class2'];

				$template->assign_block_vars("catrow.forumrow",	array(
					"ROW_COLOR" => "#" . $row_color,
					"ROW_CLASS" => $row_class,
					"FOLDER" => $folder_image,
					"FORUM_NAME" => $forum_rows[$j]['forum_name'],
					"FORUM_DESC" => $forum_rows[$j]['forum_desc'],
					"POSTS" => $forum_rows[$j]['forum_posts'],
					"TOPICS" => $forum_rows[$j]['forum_topics'],
					"LAST_POST" => $last_post,
					"MODERATORS" => $moderators_links,

					"U_VIEWFORUM" => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id"))
				);

				$count++;
			}
			else if($viewcat != -1)
			{
				if(!$gen_cat[$cat_id])
				{
					$template->assign_block_vars("catrow", array(
						"CAT_ID" => $cat_id,
						"CAT_DESC" => $category_rows[$i]['cat_title'],
						"U_VIEWCAT" => append_sid("index.$phpEx?viewcat=$cat_id"))
					);
					$gen_cat[$cat_id] = 1;
				}
			}
		}
	} // for ... categories

}// if ... total_categories
else
{
	message_die(GENERAL_MESSAGE, $lang['No_forums']);
}

//
// Output page header and open the index body template
// we do this here because of the mark topics read cookie
// code
//
$page_title = $lang['Forum_Index'];
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

//
// Generate the page
//
$template->pparse("body");

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>