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
include('config.'.$phpEx);
include('template.inc');
include('functions/error.'.$phpEx);
include('functions/sessions.'.$phpEx);
include('functions/auth.'.$phpEx);
include('db.'.$phpEx);

$total_users = 10;
$total_posts = 55668;
$newest_user = "Dave";
$newest_uid = 10;
$users_browsing = "4 Users";

$pagetype = "index";
include('page_header.'.$phpEx);

$template->set_block("body", "catrow", "cats");
$template->set_block("catrow", "forumrow", "forums");

$sql = "SELECT * FROM $categories_table ORDER BY cat_order";
if(!$result = $db->sql_query($sql)) 
{
	error_die($db, QUERY_ERROR);
}
$total_rows = $db->sql_numrows();
if($total_rows)
{
	$rows = $db->sql_fetchrowset($result);
	for($x = 0; $x < $total_rows; $x++)
	{

		$template->set_var(array("CAT_ID" => $rows[$x]["cat_id"],
				 "PHP_SELF" => $PHP_SELF,
				 "CAT_DESC" => stripslashes($rows[$x]["cat_title"])));

	$sub_sql = "SELECT f.* FROM $forums_table f WHERE f.cat_id = '".$rows[$x]["cat_id"]."' ORDER BY forum_id";
	if(!$sub_result = $db->sql_query($sub_sql))
	  {
	     error_die($db, QUERY_ERROR);
	  }
	$total_forums = $db->sql_numrows($sub_result);
	$forum_rows = $db->sql_fetchrowset($sub_result);

	if($total_forums)
	  {
	     $template->parse("cats", "catrow", true);
	     for($y = 0; $y < $total_forums; $y++)
	       {
					 $folder_image = "<img src=\"images/folder.gif\">";
					 $posts = $forum_rows[$y]["forum_posts"];
					 $topics = $forum_rows[$y]["forum_topics"];
					 $last_post = $forum_rows[$y]["forum_last_post"];
					 $last_post = date($date_format, $last_post);
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
																		"FORUM_NAME" => stripslashes($forum_rows[$y]["forum_name"]),
																		"FORUM_ID" => $forum_rows[$y]["forum_id"],
																		"FORUM_DESC" => stripslashes($forum_rows[$y]["forum_desc"]),
																		"ROW_COLOR" => $row_color,
																		"PHPEX" => $phpEx,
																		"POSTS" => $posts,
																		"TOPICS" => $topics,
																		"LAST_POST" => $last_post,
																		"MODERATORS" => $moderators));
					 $template->parse("forums", "forumrow", true);
	       }
			$template->parse("cats", "forums", true);
			$template->set_var("forums", "");
	  }
	}
}
$template->pparse("output", "body");


include('page_tail.'.$phpEx);
?>
