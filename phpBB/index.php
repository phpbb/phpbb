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

			$sql = "SELECT f.*, t.topic_id, t.topic_replies, t.topic_last_post_id, u.username, u.user_id, p.post_time, p.post_username
				FROM " . FORUMS_TABLE . " f, " . TOPICS_TABLE . " t, " . POSTS_TABLE . " p, " . USERS_TABLE . " u 
				WHERE f.forum_last_post_id = p.post_id
					AND p.post_id = t.topic_last_post_id
					AND p.poster_id = u.user_id
					$limit_forums
					UNION (
						SELECT f.*, NULL, NULL, NULL, NULL, NULL, NULL, NULL
						FROM " . FORUMS_TABLE . " f
						WHERE NOT EXISTS (
							SELECT p.post_time
							FROM " . POSTS_TABLE . " p
							WHERE f.forum_last_post_id = p.post_id
						)
							$limit_forums
					)";
			break;

		case 'oracle':
			$limit_forums = ($viewcat != -1) ? "AND f.cat_id = $viewcat " : "";

			$sql = "SELECT f.*, t.topic_id, t.topic_replies, t.topic_last_post_id, u.username, u.user_id, p.post_time, p.post_username
				FROM " . FORUMS_TABLE . " f, " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t, " . USERS_TABLE . " u 
				WHERE f.forum_last_post_id = p.post_id(+)
					AND p.post_id = t.topic_last_post_id(+)
					AND p.poster_id = u.user_id(+)
					$limit_forums
				ORDER BY f.cat_id, f.forum_order";
			break;

		default:
			$limit_forums = ($viewcat != -1) ? "WHERE f.cat_id = $viewcat " : "";

			$sql = "SELECT f.*, t.topic_id, t.topic_replies, t.topic_last_post_id, u.username, u.user_id, p.post_time
				FROM ((( " . FORUMS_TABLE . " f
				LEFT JOIN " . POSTS_TABLE . " p ON f.forum_last_post_id = p.post_id )
				LEFT JOIN " . TOPICS_TABLE . " t ON p.post_id = t.topic_last_post_id )
				LEFT JOIN " . USERS_TABLE . " u ON p.poster_id = u.user_id )
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
			
/*	$sql = "SELECT f.forum_id, g.group_name, g.group_id, u.user_id, u.username 
		FROM " . AUTH_ACCESS_TABLE . " aa, " . FORUMS_TABLE . " f, " . USER_GROUP_TABLE . " ug, " . USERS_TABLE . " u, " . GROUPS_TABLE . " g 
		WHERE aa.forum_id = f.forum_id 
			AND aa.auth_mod = " . TRUE . " 
			AND ( 
				( ug.user_id = aa.user_id 
					AND u.user_id = ug.user_id 
					AND g.group_id = 0 ) 
				OR 
				( ug.group_id = aa.group_id 
					AND u.user_id = ug.user_id 
					AND g.group_id <> 0 ) 
				)
			AND g.group_id = ug.group_id 
		ORDER BY f.forum_id, g.group_id, u.user_id";*/
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
	$is_auth_ary = auth(AUTH_VIEW, AUTH_LIST_ALL, $userdata, $forum_rows);

	//
	// Output page header and open the index body template
	//
	$page_title = "Forum Index";
	include($phpbb_root_path . 'includes/page_header.'.$phpEx);

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

		"U_NEWEST_USER_PROFILE" => append_sid("profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=$newest_uid"))
	);

	//
	// Okay, let's build the index
	//
	$gen_cat = array();

	for($i = 0; $i < $total_categories; $i++)
	{
		$cat_id = $category_rows[$i]['cat_id'];

		for($j = 0; $j < $total_forums; $j++)
		{
			$forum_id = $forum_rows[$j]['forum_id'];

			if( $is_auth_ary[$forum_id]['auth_view'] && ( ($forum_rows[$j]['cat_id'] == $cat_id && $viewcat == -1) || $cat_id == $viewcat) )
			{
				if(!$gen_cat[$cat_id])
				{
					$template->assign_block_vars("catrow", array(
						"CAT_ID" => $cat_id,
						"CAT_DESC" => stripslashes($category_rows[$i]['cat_title']),
						"U_VIEWCAT" => append_sid("index.$phpEx?viewcat=$cat_id"))
					);
					$gen_cat[$cat_id] = 1;
				}

				if($forum_rows[$j]['forum_status'] == FORUM_LOCKED)
				{
					$folder_image = "<img src=\"" . $images['folder_locked'] . "\">";

				}
				else if($userdata['session_start'] == $userdata['session_time'])
				{
					$folder_image = ($forum_rows[$i]['post_time'] > $userdata['session_last_visit']) ? "<img src=\"" . $images['folder_new'] . "\">" : "<img src=\"" . $images['folder'] . "\">";
				}
				else
				{
					$folder_image = ($forum_rows[$i]['post_time'] > $userdata['session_time'] - 300) ? "<img src=\"" . $images['folder_new'] . "\">" : "<img src=\"" . $images['folder'] . "\">";
				}

				$posts = $forum_rows[$j]['forum_posts'];
				$topics = $forum_rows[$j]['forum_topics'];

				if($forum_rows[$j]['username'] != "" && $forum_rows[$j]['post_time'] > 0)
				{
					if($forum_rows[$j]['user_id'] == ANONYMOUS && $forum_rows[$j]['post_username'] != '')
					{
						$last_poster = $forum_rows[$j]['post_username'];
					}
					else
					{
						$last_poster = $forum_rows[$j]['username'];
					}
					$last_post_time = create_date($board_config['default_dateformat'], $forum_rows[$j]['post_time'], $board_config['default_timezone']);

					$last_post = $last_post_time . "<br />by ";
					$last_post .= "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "="  . $forum_rows[$j]['user_id']) . "\">" . $last_poster . "</a>&nbsp;";

					$last_post .= "<a href=\"" . append_sid("viewtopic.$phpEx?"  . POST_POST_URL . "=" . $forum_rows[$j]['topic_last_post_id']) . "#" . $forum_rows[$j]['topic_last_post_id'] . "\"><img src=\"" . $images['icon_latest_reply'] . "\" width=\"20\" height=\"11\" border=\"0\" alt=\"" . $lang['View_latest_post'] . "\"></a>";
				}
				else
				{
					$last_post = $lang['No_Posts'];
					$forum_rows[$j]['forum_name'] = stripslashes($forum_rows[$j]['forum_name']);
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

						if(!($mod_count % 2) && $mod_count != 0)
						{
							$moderators_links .= "<br />";
						}

						if( $forum_mods_single_user[$forum_id][$mods])
						{
							$moderators_links .= "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=" . $forum_mods_id[$forum_id][$mods]) . "\">" . $forum_mods_name[$forum_id][$mods] . "</a>";
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

				//
				// This should end up in the template using IF...ELSE...ENDIF
				//
				if($row_color == "#DDDDDD")
				{
					$row_color = "#CCCCCC";
				}
				else
				{
					$row_color = "#DDDDDD";
				}

				$template->assign_block_vars("catrow.forumrow",	array(
					"FOLDER" => $folder_image,
					"FORUM_NAME" => stripslashes($forum_rows[$j]['forum_name']),
					"FORUM_DESC" => stripslashes($forum_rows[$j]['forum_desc']),
					"ROW_COLOR" => $row_color,
					"POSTS" => $forum_rows[$j]['forum_posts'],
					"TOPICS" => $forum_rows[$j]['forum_topics'],
					"LAST_POST" => $last_post,
					"MODERATORS" => $moderators_links,

					"U_VIEWFORUM" => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id&" . $forum_rows[$j]['forum_posts']))
				);
			}
			else if($viewcat != -1)
			{
				if(!$gen_cat[$cat_id])
				{
					$template->assign_block_vars("catrow", array(
						"CAT_ID" => $cat_id,
						"CAT_DESC" => stripslashes($category_rows[$i]['cat_title']),
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
// Generate the page
//
$template->pparse("body");

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>