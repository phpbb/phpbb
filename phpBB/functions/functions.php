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

function make_jumpbox($db, $phpEx)
{
     
   $boxstring = "
       <FORM ACTION=\"viewforum.$phpEx\" METHOD=\"GET\">
       <SELECT NAME=\"forum_id\"><OPTION VALUE=\"-1\">Select Forum</OPTION>
       ";
   $sql = "SELECT cat_id, cat_title FROM ".CATEGORIES_TABLE." ORDER BY cat_order";
   $result = $db->sql_query($sql);
   if($total_cats = $db->sql_numrows($result))
     {
	for($x = 0; $x < $total_cats; $x++)
	  {
	     $boxstring .= "<OPTION VALUE=\"-1\">&nbsp;</OPTION>\n";
	     $boxstring .= "<OPTION VALUE=\"-1\">".stripslashes($cat_rows[$x]["cat_title"])."</OPTION>\n";
	     $boxstring .= "<OPTION VALUE=\"-1\">----------------</OPTION>\n";
	     $cat_rows = $db->sql_fetchrowset($result);
	     $f_sql = "SELECT forum_name, forum_id FROM ".FORUMS_TABLE." 
		       WHERE cat_id = ". $cat_rows[$x]["cat_id"] . " ORDER BY forum_id";
	     if($f_result = $db->sql_query($f_sql))
	       {
		  if($total_forums = $db->sql_numrows($f_result)) {
		     $f_rows = $db->sql_fetchrowset($f_result);
		     for($y = 0; $y < $total_forums; $y++)
		       {
			  $name = stripslashes($f_rows[$y]["forum_name"]);
			  $boxstring .=  "<OPTION VALUE=\"".$f_rows[$y]["forum_id"]."\">$name</OPTION>\n";
		       }
		  }
	       }
	  }
     }
   else
     {
	$boxstring .= "<option value=\"-1\">No Forums to Jump to</option>\n";
     }
   $boxstring .=  "</SELECT>\n<br><INPUT TYPE=\"SUBMIT\" VALUE=\"Jump\"></FORM>";
   
   return($boxstring);
}

?>
