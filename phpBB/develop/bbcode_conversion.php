<?php
/***************************************************************************  
 *                               config.php  
 *                            -------------------                         
 *   begin                : Tuesday, March 20, 2001 
 *   copyright            : (C) 2001 The phpBB Group        
 *   email                : support@phpbb.com                           
 *                                                          
 *   $Id:
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
include('includes/constants.'.$phpEx);
include('functions/functions.'.$phpEx);
include('includes/db.'.$phpEx);
include('functions/bbcode.'.$phpEx);
 
set_time_limit(60*60);  // Increase maximum execution time to 60 minutes. 
 


$backup_name = "backup_post_text";
$table_name = POSTS_TEXT_TABLE;
$sql = "CREATE TABLE $backup_name (
   post_id int(10) DEFAULT '0' NOT NULL,
   post_text text,
   PRIMARY KEY (post_id)
);";

echo "<p>Creating backup table.. </p>\n";
flush();

$result = $db->sql_query($sql);
if (!$result) 
{
	$db_error = $db->sql_error();
	die("Error doing DB backup table creation. Reason: " . $db_error["message"]);
}

$sql = "insert into $backup_name select * from $table_name";

echo "<p>Populating backup table.. </p>\n";
flush();

$result = $db->sql_query($sql);
if (!$result) 
{
	$db_error = $db->sql_error();
	die("Error doing DB backup table data moving. Reason: " . $db_error["message"]);
}


$sql = "SELECT p.post_id, t.post_text FROM " . POSTS_TABLE . " p, " . POSTS_TEXT_TABLE . " t WHERE (p.post_id = t.post_id)";
if(!$result = $db->sql_query($sql))
{
   die("error getting posts to work on");
}
if(!$total_rows = $db->sql_numrows($result))
{
   die("error getting rowcount");
}

echo "<p><b>Found $total_rows total rows to work on. </b></p>\n";
flush();

$row = $db->sql_fetchrowset($result);

for($i = 0; $i < $total_rows; $i++)
{
	$post_id = $row[$i]['post_id'];
	$text = $row[$i]['post_text'];

	// undo 1.2.x encoding..
	$text = bbdecode($text);
	$text = undo_make_clickable($text);
	$text = str_replace("<BR>", "\n", $text);
	
	// make a uid
	$uid = make_bbcode_uid();
	
	// do 2.x first-pass encoding..
	$text = bbencode_first_pass($text, $uid);
	
	$text = addslashes($text);
	
	// put the uid in the database.
	$sql = "UPDATE " . POSTS_TABLE . " SET bbcode_uid='" . $uid . "' WHERE (post_id = $post_id)";
	$result = $db->sql_query($sql);
  if (!$result) 
	{
		$db_error = $db->sql_error();
		die("Error doing DB update in posts table. Reason: " . $db_error["message"] . " sql: $sql");
	}
	// Put the post text back in the database.
	$sql = "UPDATE " . POSTS_TEXT_TABLE . " SET post_text='" . $text . "' WHERE (post_id = $post_id)";
	$result = $db->sql_query($sql);
  if (!$result) 
	{
		$db_error = $db->sql_error();
		die("Error doing DB update in post text table. Reason: " . $db_error["message"] . " sql: $sql");
	}
	
	if (($i % 100) == 0)
	{
		echo "Done post: <b> $i </b><br>\n";
		flush();
	}
	
	
}



echo "<p><b>Done.</b></p>\n";



// -------------------------------------------------------------------------------
// Everything below here is 1.x BBCode functions.
// -------------------------------------------------------------------------------


function bbdecode($message) {

		// Undo [code]
		$code_start_html = "<!-- BBCode Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD><font size=-1>Code:</font><HR></TD></TR><TR><TD><FONT SIZE=-1><PRE>";
		$code_end_html = "</PRE></FONT></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode End -->";
		$message = str_replace($code_start_html, "[code]", $message);
		$message = str_replace($code_end_html, "[/code]", $message);

		// Undo [quote]
		$quote_start_html = "<!-- BBCode Quote Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD><font size=-1>Quote:</font><HR></TD></TR><TR><TD><FONT SIZE=-1><BLOCKQUOTE>";
		$quote_end_html = "</BLOCKQUOTE></FONT></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode Quote End -->";
		$message = str_replace($quote_start_html, "[quote]", $message);
		$message = str_replace($quote_end_html, "[/quote]", $message);
		
		// Undo [b] and [i]
		$message = preg_replace("#<!-- BBCode Start --><B>(.*?)</B><!-- BBCode End -->#s", "[b]\\1[/b]", $message);
		$message = preg_replace("#<!-- BBCode Start --><I>(.*?)</I><!-- BBCode End -->#s", "[i]\\1[/i]", $message);
		
		// Undo [url] (long form)
		$message = preg_replace("#<!-- BBCode u2 Start --><A HREF=\"([a-z]+?://)(.*?)\" TARGET=\"_blank\">(.*?)</A><!-- BBCode u2 End -->#s", "[url=\\1\\2]\\3[/url]", $message);
		
		// Undo [url] (short form)
		$message = preg_replace("#<!-- BBCode u1 Start --><A HREF=\"([a-z]+?://)(.*?)\" TARGET=\"_blank\">(.*?)</A><!-- BBCode u1 End -->#s", "[url]\\3[/url]", $message);
		
		// Undo [email]
		$message = preg_replace("#<!-- BBCode Start --><A HREF=\"mailto:(.*?)\">(.*?)</A><!-- BBCode End -->#s", "[email]\\1[/email]", $message);
		
		// Undo [img]
		$message = preg_replace("#<!-- BBCode Start --><IMG SRC=\"(.*?)\" BORDER=\"0\"><!-- BBCode End -->#s", "[img]\\1[/img]", $message);
		
		// Undo lists (unordered/ordered)
	
		// <li> tags:
		$message = str_replace("<!-- BBCode --><LI>", "[*]", $message);
		
		// [list] tags:
		$message = str_replace("<!-- BBCode ulist Start --><UL>", "[list]", $message);
		
		// [list=x] tags:
		$message = preg_replace("#<!-- BBCode olist Start --><OL TYPE=([A1])>#si", "[list=\\1]", $message);
		
		// [/list] tags:
		$message = str_replace("</UL><!-- BBCode ulist End -->", "[/list]", $message);
		$message = str_replace("</OL><!-- BBCode olist End -->", "[/list]", $message);

		return($message);
}


/**
 * Nathan Codding - Feb 6, 2001
 * Reverses the effects of make_clickable(), for use in editpost.
 * - Does not distinguish between "www.xxxx.yyyy" and "http://aaaa.bbbb" type URLs.
 *
 */
 
function undo_make_clickable($text) {
	
	$text = preg_replace("#<!-- BBCode auto-link start --><a href=\"(.*?)\" target=\"_blank\">.*?</a><!-- BBCode auto-link end -->#i", "\\1", $text);
	$text = preg_replace("#<!-- BBcode auto-mailto start --><a href=\"mailto:(.*?)\">.*?</a><!-- BBCode auto-mailto end -->#i", "\\1", $text);
	
	return $text;
	
}



 
?>