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
include('extension.inc');
include('common.'.$phpEx);

$total_users = get_user_count($db, "");
$total_posts = get_total_posts($db, "");
$newest_userdata = get_newest_user($db, "");
$newest_user = $newest_userdata["username"];
$newest_uid = $newest_userdata["user_id"];
$users_browsing = "4 Users";

$pagetype = "index";
$page_title = "Forum Index";
include('page_header.'.$phpEx);

$template->set_block("body", "catrow", "cats");
$template->set_block("catrow", "forumrow", "forums");

$sql = "SELECT c.* FROM ".CATEGORIES_TABLE." c, ".FORUMS_TABLE." f WHERE f.cat_id=c.cat_id GROUP BY c.cat_id ORDER BY c.cat_order";
if(!$q_categories = $db->sql_query($sql)) 
{
	error_die($db, QUERY_ERROR);
}

$total_categories = $db->sql_numrows();

if($total_categories)
{
	$category_rows = $db->sql_fetchrowset($q_categories);
	$sql = "SELECT f.*, u.username, p.post_time FROM ".FORUMS_TABLE." f LEFT JOIN ".POSTS_TABLE." p ON p.post_id = f.forum_last_post_id LEFT JOIN ".USERS_TABLE." u ON u.user_id = p.poster_id ORDER BY f.forum_id";
	if(!$q_forums = $db->sql_query($sql))
	{
		error_die($db, QUERY_ERROR);
	}

	$total_forums = $db->sql_numrows($q_forums);
	$forum_rows = $db->sql_fetchrowset($q_forums);
	
	for($i = 0; $i < $total_categories; $i++)
	{
		$template->set_var(array("CAT_ID" => $category_rows[$i]["cat_id"],
			"PHP_SELF" => $PHP_SELF,
			"CAT_DESC" => stripslashes($category_rows[$i]["cat_title"])));
		$template->parse("cats", "catrow", true);

		for($j = 0; $j < $total_forums; $j++)
		{
			if($forum_rows[$j]["cat_id"] == $category_rows[$i]["cat_id"])
			{
				$folder_image = "<img src=\"images/folder.gif\">";
				$posts = $forum_rows[$j]["forum_posts"];
				$topics = $forum_rows[$j]["forum_topics"];
				if($forum_rows[$j]["username"] != "" && $forum_rows[$j]["post_time"] > 0){
					$last_post_user = $forum_rows[$j]["username"];
					$last_post_time = date($date_format, $forum_rows[$j]["post_time"]);
					$last_post = $last_post_time." by ".$last_post_user;
				}
				else
				{
					$last_post = "No Posts";
				}

				$moderators = "<a href=\"profile.$phpEx?mode=viewprofile&user_id=1\">theFinn</a>";
				if($row_color == "#DDDDDD")
				{
					$row_color = "#CCCCCC";
				}
				else
				{
					$row_color = "#DDDDDD";
				}
				$template->set_var(array("FOLDER" => $folder_image,
					"FORUM_NAME" => stripslashes($forum_rows[$j]["forum_name"]),
					"FORUM_ID" => $forum_rows[$j]["forum_id"],
					"FORUM_DESC" => stripslashes($forum_rows[$j]["forum_desc"]),
					"ROW_COLOR" => $row_color,
					"PHPEX" => $phpEx,
					"POSTS" => $posts,
					"TOPICS" => $topics,
					"LAST_POST" => $last_post,
					"MODERATORS" => $moderators));

				$template->parse("forums", "forumrow", true);
			} // if ... then
		} // for total forums
		$template->parse("cats", "forums", true);
		$template->set_var("forums", "");

	} // for ... categories

}// if ... total_categories
$template->pparse("output", "body");

include('page_tail.'.$phpEx);
?>
