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

function make_jumpbox($db)
{
	$sql = "SELECT cat_id, cat_title FROM ".CATEGORIES_TABLE." ORDER BY cat_order";
	
	$boxstring = "";
	if($result = $db->sql_query($sql))
	{
		if($total_cats = $db->sql_numrows($result))
		{
			$cat_rows = $db->sql_fetchrowset($result);
			for($x = 0; $x < $total_cats; $x++)
			{
				$boxstring .= "<option value=\"-1\">&nbsp;</option>\n";
				$boxstring .= "<option value=\"-1\">".stripslashes($cat_rows[$x]["cat_title"])."</OPTION>\n";
				$boxstring .= "<option value=\"-1\">----------------</OPTION>\n";

				$f_sql = "SELECT forum_name, forum_id FROM ".FORUMS_TABLE."
					WHERE cat_id = ". $cat_rows[$x]["cat_id"] . " ORDER BY forum_id";
				
				if($f_result = $db->sql_query($f_sql))
				{
					if($total_forums = $db->sql_numrows($f_result)) 
					{
						$f_rows = $db->sql_fetchrowset($f_result);
						
						for($y = 0; $y < $total_forums; $y++)
						{
							$name = stripslashes($f_rows[$y]["forum_name"]);
							$boxstring .=  "<option value=\"".$f_rows[$y]["forum_id"]."\">$name</OPTION>\n";
						}
					}
				}
				else 
				{
					$boxstring .= "<option value=\"-1\">Error!</option>\n";
				}

			}
		}
		else
		{
			$boxstring .= "<option value=\"-1\">No Forums to Jump to</option>\n";
		}
	}
	else
	{
		$boxstring .= "<option value=\"-1\">Cat Error</option>\n";
	}

	return($boxstring);
}

function get_moderators($db, $forum_id)
{
   $sql = "SELECT u.username, u.user_id FROM " . FORUM_MODS_TABLE ." f, " . USERS_TABLE . " u
           WHERE f.forum_id = '$forum_id' AND u.user_id = f.user_id";
   if($result = $db->sql_query($sql))
     {
	if($total_mods = $db->sql_numrows($result))
	  {
	     $rowset = $db->sql_fetchrowset($result);
	     for($x = 0; $x < $total_mods; $x++)
	       {
		  $modArray[] = array("id" => $rowset[$x]["user_id"],
				      "name" => $rowset[$x]["username"]);
	       }
	  }
	else 
	  {
	     $modArray[] = array("id" => "-1",
				 "name" => "ERROR");
	  }

     }
   else
     {
	$modArray[] = array("id" => "-1",
			    "name" => "ERROR");
     }
   
   return($modArray);
}

?>
