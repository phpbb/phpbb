<?php
/***************************************************************************  
 *                               functions.php
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

function get_total_posts($db, $forums_table) 
{
   $sql = "SELECT sum(forum_posts) AS total FROM ".FORUMS_TABLE;
   if(!$result = $db->sql_query($sql))
     {
	return "ERROR";
     }
   else
     {
	$rowset = $db->sql_fetchrowset($result);
	return($rowset[0]["total"]);
     }
}

function get_user_count($db)
{
   $sql = "SELECT count(user_id) AS total 
	    FROM ". USERS_TABLE ." 
	    WHERE user_id != ".ANONYMOUS."
	    AND user_level != ".DELETED;

   if(!$result = $db->sql_query($sql))
     {
	return "ERROR";
     }
   else
     {
	$rowset = $db->sql_fetchrowset($result);
	return($rowset[0]["total"]);
     }
}

function get_newest_user($db)
{
   $sql = "SELECT user_id, username
	    FROM ".USERS_TABLE."
	    WHERE user_id != " . ANONYMOUS. "
	    AND user_level != ". DELETED ."
	    ORDER BY user_id DESC LIMIT 1";
   if(!$result = $db->sql_query($sql))
     {
	return array("user_id" => "-1", "username" => "ERROR");
     }
      else
     {
	$rowset = $db->sql_fetchrowset($result);
	$return_data = array("user_id" => $rowset[0]["user_id"],
			     "username" => $rowset[0]["username"]);
	return($return_data);
     }
}
?>
