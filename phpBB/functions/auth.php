<?php
/***************************************************************************  
 *                                 auth.php
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

/* Notes:
 * auth() is going to become a very complex function and can take in a LARGE number of arguments. 
 * The currently included argements should be enough to handle any situation, however, if you need access to another
 * the best option would be to create a global variable and access it that way if you can.
 * 
 * auth() returns: 
 * TRUE if the user authorized
 * FALSE if the user is not
 */
function auth($type, 
	      $db,
	      $user_id = "", 
	      $user_name = "", 
	      $user_pass = "", 
	      $user_level = "", 
	      $session_id = "", 
	      $user_ip = "", 
	      $forum_id = "", 
	      $topic_id = "", 
	      $post_id = "") 
{
   switch($type) 
     {
      case 'ip ban':
	$sql = "DELETE FROM ".BANLIST_TABLE." 
		 WHERE (ban_end < ". mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")).") 
		 AND (ban_end > 0)";
	$db->sql_query($sql);
	$sql = "SELECT ban_ip FROM ".BANLIST_TABLE;
	if($result = $db->sql_query($sql)) 
	  {
	     if($totalrows = $db->sql_numrows()) 
	       {
		  $iprow = $db->sql_fetchrowset($result);
		  for($x = 0; $x < $totalrows; $x++)
		    {
		       $ip = $iprow[$x]["ban_ip"];
		       if($ip[strlen($ip) - 1] == ".") 
			 {
			    $db_ip = explode(".", $ip);
			    $this_ip = explode(".", $user_ip);
			    
			    for($x = 0; $x < count($db_ip) - 1; $x++)
			      {
				 $my_ip .= $this_ip[$x] . ".";
			      }
			    
			    if($my_ip == $ip)
			      {
				 return(FALSE);
			      }
			 }
		       else 
			 {
			    if($ipuser == $ip)
			      {
				 return(FALSE);
			      }
			 }
		    }
		  return(TRUE);
	       }
	     else
	       {
		  return(TRUE);
	       }
	  }
	return(TRUE);
	break;
      case 'username ban':
	$sql = "DELETE FROM ".BANLIST_TABLE."
		WHERE (ban_end < ". mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")).")
		AND (ban_end > 0)";
	$db->sql_query($sql);
	$sql = "SELECT ban_userid FROM ".BANLIST_TABLE." WHERE ban_userid = '$user_id'";
	if($result = $db->sql_query($sql)) 
	  {
	   if($db->sql_numrows())
	       {
		  return(FALSE);
	       }
	     else
	       {
		  return(TRUE);
	       }
	  }
	else
	  {
	     return(TRUE);
	  }
	break;
     }
}


/*
 * The following functions are used for getting user information. They are not related directly to auth()
 */

function get_userdata_from_id($userid, $db) 
{
   $sql = "SELECT * FROM ".USERS_TABLE." WHERE user_id = $userid";
   if(!$result = $db->sql_query($sql)) 
     {
	$userdata = array("error" => "1");
	return ($userdata);
     }
   if($db->sql_numrows())
     {
	$myrow = $db->sql_fetchrowset($result);
	return($myrow[0]);
     }
   else
     {
	$userdata = array("error" => "1");
	return ($userdata);
     }
}

?>
