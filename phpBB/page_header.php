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

// Parse and show the overall header.  
$template->set_file(array("overall_header" => "overall_header.tpl",
			  "overall_footer" => "overall_footer.tpl"));

if($user_logged_in)
{
      $logged_in_status = "You are logged in as <b>".$userdata["username"]."</b>.";
}
else
{
      $logged_in_status = "You are not logged in.";
}
$template->set_var(array("SITENAME" => $sitename,
			"PHPEX" => $phpEx,
			 "PAGE_TITLE" => $page_title,
			 "LOGIN_STATUS" => $logged_in_status,
			 "META_INFO" => $meta_tags));
$template->pparse("output", "overall_header");

// Do a switch on page type, this way we only load the templates that we need at the time
switch($pagetype) 
{
 case 'index':
   $template->set_file(array("header" => "index_header.tpl",
			     "body" => "index_body.tpl",
			     "footer" => "index_footer.tpl"));
			     
   $template->set_var(array("TOTAL_POSTS" => $total_posts,
			    "TOTAL_USERS" => $total_users,
			    "NEWEST_USER" => $newest_user,
			    "NEWEST_UID" => $newest_uid,
			    "USERS_BROWSING" => $users_browsing));
		      
   $template->pparse("output", "header");
   
   break;
 case 'viewforum':
     $template->set_file(array("header" => "viewforum_header.tpl",
			       "body" => "viewforum_body.tpl",
			       "footer" => "viewforum_footer.tpl"));
     $template->set_var(array("FORUM_ID" => $forum_id,
			      "FORUM_NAME" => $forum_name,
			      "MODERATORS" => $forum_moderators));
     $template->pparse("output", "header");
     break;
}
			    
?>
