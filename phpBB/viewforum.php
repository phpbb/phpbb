<?php
/***************************************************************************  
 *                                 
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
include('functions/sessions.'.$phpEx);
include('functions/auth.'.$phpEx);
include('functions/functions.'.$phpEx);
include('functions/error.'.$phpEx);
include('db.'.$phpEx);

if(isset($forum_id))
{
	$sql = "SELECT f.forum_type, f.forum_name 
					 FROM ".FORUMS_TABLE." f 
					 WHERE forum_id = '$forum_id'";
}
else 
{
	error_die($db, "You have reached this page in error, please go back and try again");
}

if(!$result = $db->sql_query($sql))
{
	error_die($db, QUERY_ERROR);
}
$total_rows = $db->sql_numrows($result);
$forum_row = $db->sql_fetchrowset($result);
if(!$forum_row)
{
	error_die($db, QUERY_ERROR);
}
$forum_name = stripslashes($forum_row[0]["forum_name"]);
$forum_moderators = "<a href=\"profile.$phpEx?mode=viewprofile&user_id=1\">james</a>";
$pagetype = "viewforum";
$page_title = "View Forum - $forum_name";
include('page_header.'.$phpEx);


// Add checking for private forums here!!

$template->set_block("body", "topicrow", "topics");

if(!isset($start))
{
   $start = 0;
}

$sql = "SELECT t.*, u.username, p.post_time FROM " . TOPICS_TABLE ." t, ". USERS_TABLE. " u 
         LEFT JOIN ".POSTS_TABLE." p ON p.post_id = t.topic_last_post_id
	 WHERE t.forum_id = '$forum_id' AND t.topic_poster = u.user_id 
	 ORDER BY topic_time DESC LIMIT $start, $topics_per_page";
if(!$t_result = $db->sql_query($sql))
{
   error_die($db, QUERY_ERROR);
}
$total_topics = $db->sql_numrows();

if($total_topics)
{
   $topic_rowset = $db->sql_fetchrowset($t_result);
   for($x = 0; $x < $total_topics; $x++)
     {
	$topic_title = stripslashes($topic_rowset[$x]["topic_title"]);
	$topic_id = $topic_rowset[$x]["topic_id"];
	$replies = $topic_rowset[$x]["topic_replies"];
	$views = $topic_rowset[$x]["topic_views"];
	$last_post_time = date($date_format, $topic_rowset[$x]["post_time"]);
	$last_post_user = $topic_rowset[$x]["username"];
	$folder_img = "<img src=\"images/folder.gif\">";
	$template->set_var(array("FORUM_ID" => $forum_id,
				 "TOPIC_ID" => $topic_id,
				 "FOLDER" => $folder_img, 
				 "REPLIES" => $replies,
				 "TOPIC_TITLE" => $topic_title,
				 "VIEWS" => $views,
				 "LAST_POST" => $last_post_time . "<br>" . $last_post_user));
	$template->parse("topics", "topicrow",  true);
     }
   $template->pparse("output", array("topics", "body"));
}
else
{
   error_die($db, NO_POSTS);
}
			       

include('page_tail.'.$phpEx);


?>
