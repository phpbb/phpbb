<?php
/***************************************************************************  
 *                              page_header.php
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

DEFINE(HEADER_INC, TRUE);

// Parse and show the overall header.  
$template->set_filenames(array("overall_header" => "overall_header.tpl",
			  "overall_footer" => "overall_footer.tpl"));

if($userdata['session_logged_in'])
{
      $logged_in_status = "You are logged in as <b>".$userdata["username"]."</b>.";
	  $logged_in_status .= " [<A HREF=\"login.php?submit=logout\">Logout</A>]";
}
else
{
      $logged_in_status = "You are not logged in.";
}

$template->assign_vars(array("SITENAME" => $sitename,
			"PHPEX" => $phpEx,
			 "PAGE_TITLE" => $page_title,
			 "LOGIN_STATUS" => $logged_in_status,
			 "META_INFO" => $meta_tags,
			 "L_USERNAME" => $l_username,
			 "L_PASSWORD" => $l_password,
			 "L_WELCOMETO" => $l_welcometo,
			 "L_REGISTER" => $l_register,
			 "L_PROFILE" => $l_profile,
			 "L_SEARCH" => $l_search,
			 "L_PRIVATEMSGS" => $l_privmsgs,
			 "L_MEMBERLIST" => $l_memberslist,
			 "L_FAQ" => $l_faq,
			 "L_FORUM" => $l_forum,
			 "L_TOPICS" => $l_topics,
			 "L_REPLIES" => $l_replies,
			 "L_VIEWS" => $l_views,
			 "L_POSTS" => $l_posts,
			 "L_LASTPOST" => $l_lastpost,
			 "L_MODERATOR" => $l_moderator,
			 "L_MESSAGES" => $l_messages,
			 "L_POSTEDTOTAL" => $l_postedtotal,
			 "L_WEHAVE" => $l_wehave,
			 "L_REGUSERS" => $l_regedusers,
			 "L_NEWESTUSER" => $l_newestuser,
			 "L_BROWSING" => $l_browsing,
			 "L_ARECURRENTLY" => $l_arecurrently,
			 "L_THEFORUMS" => $l_theforums,
			 "L_NONEWPOSTS" => $l_nonewposts,
			 "L_NEWPOSTS" => $l_newposts));

$template->pparse("overall_header");

// Do a switch on page type, this way we only load the templates that we need at the time
switch($pagetype) 
{
 case 'index':
   $template->set_filenames(array("header" => "index_header.tpl",
			     "body" => "index_body.tpl",
			     "footer" => "index_footer.tpl"));
   
   $template->assign_vars(array("TOTAL_POSTS" => $total_posts,
			    "TOTAL_USERS" => $total_users,
			    "NEWEST_USER" => $newest_user,
			    "NEWEST_UID" => $newest_uid,
			    "USERS_BROWSING" => $users_browsing));
   
   $template->pparse("header");
   break;
   
 case 'viewforum':
   $template->set_filenames(array("header" => "viewforum_header.tpl",
			     "body" => "viewforum_body.tpl",
			     "jumpbox" => "jumpbox.tpl",
			     "footer" => "viewforum_footer.tpl"));
   
   $jumpbox = make_jumpbox($db);
   $template->assign_vars(array("JUMPBOX_LIST" => $jumpbox,
			    "JUMPBOX_ACTION" => "viewforum.".$phpEx,
			    "SELECT_NAME" => "forum_id"));
   
   $template->assign_var_from_handle("JUMPBOX", "jumpbox");
   
   $template->assign_vars(array("FORUM_ID" => $forum_id,
			    "FORUM_NAME" => $forum_name,
			    "MODERATORS" => $forum_moderators));
   
   $template->pparse("header");
   break;
 case 'viewtopic':
   $template->set_filenames(array("header" => "viewtopic_header.tpl",
			     "body" => "viewtopic_body.tpl",
			     "jumpbox" => "jumpbox.tpl",
			     "footer" => "viewtopic_footer.tpl"));
   $jumpbox = make_jumpbox($db);
   $template->assign_vars(array("JUMPBOX_LIST" => $jumpbox,
			    "JUMPBOX_ACTION" => "viewforum.".$phpEx,
			    "SELECT_NAME" => "forum_id"));
   
   $template->assign_var_from_handle("JUMPBOX", "jumpbox");
   
   $template->assign_vars(array("FORUM_ID" => $forum_id,
			    "FORUM_NAME" => $forum_name,
			    "TOPIC_ID" => $topic_id,
			    "TOPIC_TITLE" => $topic_title));
   $template->pparse("header");
   break;

	case 'viewonline':
		$template->set_filenames(array("header" => "viewonline_header.tpl",
			"body" => "viewonline_body.tpl",
			"jumpbox" => "jumpbox.tpl",
			"footer" => "viewonline_footer.tpl"));
		$jumpbox = make_jumpbox($db);
		$template->assign_vars(array("TOTAL_POSTS" => $total_posts,
			"TOTAL_USERS" => $total_users,
			"NEWEST_USER" => $newest_user,
			"NEWEST_UID" => $newest_uid,
			"JUMPBOX_LIST" => $jumpbox,
			"JUMPBOX_ACTION" => "viewforum.".$phpEx,
			"SELECT_NAME" => POST_FORUM_URL));
		$template->assign_var_from_handle("JUMPBOX", "jumpbox");
		$template->pparse("header");
		break;
	
 case 'newtopic':
   $template->set_filenames(array("header" => "newtopic_header.tpl",
			     "body" => "posting_body.tpl"));
   $template->assign_vars(array("FORUM_ID" => $forum_id,
			    "FORUM_NAME" => $forum_name,
			    "L_POSTNEWIN" => $l_postnewin));
   $template->pparse("header");
   break;
   case 'register':
   	if(!isset($agreed))
   	{
   		if(!isset($coppa))
   		{
   			$coppa = FALSE;
   		}
   		$template->set_filenames(array("body" => "agreement.tpl"));
			$template->assign_vars(array("COPPA" => $coppa));   		
   	}
   	else
   	{
   		$template->set_filenames(array("body" => "profile_add_body.tpl"));
   	}
   break;
}
			    
?>
