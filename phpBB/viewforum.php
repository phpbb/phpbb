<?php
/***************************************************************************  
 *                                 
 *                            -------------------                         
 *   begin                : Saturday, Feb 13, 2001 
 *   copyright            : (C) 2001 The phpBB Group        
 *   email                : support@phpbb.com                           
 *                                                          
 *   $Id$                                                      *                                                            
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

//
// Obtain which forum id is required
//
if(!isset($HTTP_GET_VARS['forum']) && !isset($HTTP_POST_VARS['forum']))  // For backward compatibility
{
	$forum_id = ($HTTP_GET_VARS[POST_FORUM_URL]) ? $HTTP_GET_VARS[POST_FORUM_URL] : $HTTP_POST_VARS[POST_FORUM_URL];
}
else
{
	$forum_id = ($HTTP_GET_VARS['forum']) ? $HTTP_GET_VARS['forum'] : $HTTP_POST_VARS['forum'];
}

$pagetype = "viewforum";
$page_title = "View Forum - $forum_name";

//
// Start session management
//
$userdata = session_pagestart($user_ip, $forum_id, $session_length);
init_userprefs($userdata);
//
// End session management
//


// Check if the user has acutally sent a forum ID with his/her request
// If not give them a nice error page.
if(isset($forum_id))
{
	$sql = "SELECT f.forum_type, f.forum_name, f.forum_topics, u.username, u.user_id
		FROM ".FORUMS_TABLE." f, ".FORUM_MODS_TABLE." fm, ".USERS_TABLE." u
		WHERE f.forum_id = '$forum_id'
			AND fm.forum_id = '$forum_id'
			AND u.user_id = fm.user_id";
}
else 
{
	error_die(GENERAL_ERROR, "You have reached this page in error, please go back and try again");
}

if(!$result = $db->sql_query($sql))
{
	error_die(SQL_QUERY, "Couldn't obtain forums information.", __LINE__, __FILE__);
}

//
// Add checking for private forums here!!
//


// If the query dosan't return any rows this isn't a valid forum. Inform the user.
if(!$total_rows = $db->sql_numrows($result)) 
{
   error_die(GENERAL_ERROR, "The forum you selected does not exist. Please go back and try again.");
}

$forum_row = $db->sql_fetchrowset($result);
if(!$forum_row)
{
	error_die(SQL_QUERY, "Couldn't obtain rowset.", __LINE__, __FILE__);
}

$forum_name = stripslashes($forum_row[0]["forum_name"]);
$topics_count = $forum_row[0]["forum_topics"];
for($x = 0; $x < $db->sql_numrows($result); $x++)
{
   if($x > 0)
     $forum_moderators .= ", ";
   $forum_moderators .= "<a href=\"profile.$phpEx?mode=viewprofile&user_id=".$forum_row[$x]["user_id"]."\">".$forum_row[$x]["username"]."</a>";
}

include('includes/page_header.'.$phpEx);


if(!isset($start))
{
   $start = 0;
}

$sql = "SELECT t.*, u.username, u.user_id, u2.username as user2, u2.user_id as id2, p.post_time
	FROM " . TOPICS_TABLE ." t
	LEFT JOIN ". USERS_TABLE. " u ON t.topic_poster = u.user_id
	LEFT JOIN ".POSTS_TABLE." p ON p.post_id = t.topic_last_post_id
	LEFT JOIN " . USERS_TABLE . " u2 ON p.poster_id = u2.user_id
	WHERE t.forum_id = '$forum_id'
	ORDER BY topic_time DESC
	LIMIT $start, $topics_per_page";
if(!$t_result = $db->sql_query($sql))
{
   error_die(SQL_QUERY, "Couldn't obtain topic information.", __LINE__, __FILE__);
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
	if($replies > $posts_per_page) 
	  {
	     $goto_page = "&nbsp;&nbsp;&nbsp;(<img src=\"images/posticon.gif\">$l_gotopage: ";
	     $times = 1;
	     for($i = 0; $i < ($replies + 1); $i += $posts_per_page) 
	       {
		if($times > 4) 
		    {
		       if(($i + $posts_per_page) >= ($replies + 1)) 
			 {
			    $goto_page.=" ... <a href=\"viewtopic.$phpEx?".POST_TOPIC_URL."=".$topic_id."&start=$i\">$times</a>";
			 }
		    }
		else 
		    {
		       if($times != 1)
			 {
			    $goto_page.= ", ";
			 }
		       $goto_page.= "<a href=\"viewtopic.$phpEx?".POST_TOPIC_URL."=".$topic_id."&start=$i\">$times</a>";
		    }
		  $times++;
	     }
	     $goto_page.= ")";
	  }
	else 
	  {
	     $goto_page = "";
	  }
	$topic_poster = stripslashes($topic_rowset[$x]["username"]);
	$views = $topic_rowset[$x]["topic_views"];
	$last_post_time = date($date_format, $topic_rowset[$x]["post_time"]);
	$last_post_user = $topic_rowset[$x]["user2"];
	$folder_img = "<img src=\"images/folder.gif\">";
	$template->assign_block_vars(
		"topicrow", array("FORUM_ID" => $forum_id,
		 "TOPIC_ID" => $topic_id,
		 "FOLDER" => $folder_img, 
		 "TOPIC_POSTER" => "<a href=\"profile.$phpEx?mode=viewprofile&".POST_USERS_URL."=".$topic_rowset[$x]["user_id"]."\">".$topic_poster."</a>",
		 "GOTO_PAGE" => $goto_page,
		 "REPLIES" => $replies,
		 "TOPIC_TITLE" => $topic_title,
		 "VIEWS" => $views,
		 "LAST_POST" => $last_post_time . "<br><a href=\"profile.$phpEx?mode=viewprofile&".POST_USERS_URL."=".$topic_rowset[$x]["id2"]."\">" . $last_post_user ."</a>"));
     }

   $count = 1;
   $next = $start + $topics_per_page;
   if($topics_count > $topics_per_page)
     {
	if($next < $topics_count)
	  {
	     $pagination = "<a href=\"viewforum.$phpEx?forum_id=$forum_id&start=$next\">$l_nextpage</a> | ";
	  }
	for($x = 0; $x < $topics_count; $x++)
	  {
	     if(!($x % $topics_per_page))
	       {
		  if($x == $start)
		    {
		       $pagination .= "$count";
		    }
		  else
		    {
		       $pagination .= " <a href=\"viewforum.$phpEx?".POST_FORUM_URL."=$forum_id&start=$x\">$count</a> ";
		    }
		  $count++;
		  if(!($count % 20))
		    {
		       $pagination .= "<br>";
		    }
	       }
	  }
     }
   $template->assign_vars(array("PAGINATION" => $pagination));
   $template->pparse("body");
}
else
{
	error_die(NO_POSTS);
}
			       
include('includes/page_tail.'.$phpEx);

?>