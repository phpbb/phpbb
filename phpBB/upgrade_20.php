<?php
/***************************************************************************
*                           upgrade_20.php  -  description
*                              -------------------
*     begin                : Sat Oct 14 2000
*     copyright            : (C) 2001 The phpBB Group        
*     email                : support@phpbb.com                           
* 
*     $id upgrade_20.php,v 1.9 2001/03/23 01:32:41 psotfx Exp $
* 
****************************************************************************/
  
/***************************************************************************
*
*   This program is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 2 of the License, or
*   (at your option) any later version.
*
***************************************************************************/
include('extension.inc');
include('config.'.$phpEx);
include('includes/constants.'.$phpEx);
include('includes/db.'.$phpEx);
include('functions/bbcode.'.$phpEx);

set_time_limit(20*60);  // Increase maximum execution time to 20 minutes.

function common_header()
{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">
<HTML>
<HEAD>
<TITLE>phpBB - Database upgrade 1.2 to 2.0 Developers edition</TITLE>
</HEAD>
<BODY BGCOLOR="#000000" TEXT="#FFFFFF" LINK="#11C6BD" VLINK="#11C6BD">
<?

	return;

}

function common_footer()
{
?>
</BODY>
</HTML>
<?

	return;

}

function convert_ip($ip) 
{
	if (strstr($ip, ".")) 
	{
		$ip_sep = explode(".", $ip);
		$return = (( $ip_sep[0] * 0xFFFFFF + $ip_sep[0] ) + ( $ip_sep[1] *   0xFFFF + $ip_sep[1] ) + ( $ip_sep[2] *     0xFF + $ip_sep[2] ) + ( $ip_sep[3] ) );
	}
	else 
	{
		$return = sprintf( "%d.%d.%d.%d", ( ( $ip >> 24 ) & 0xFF ), ( ( $ip >> 16 ) & 0xFF ), ( ( $ip >>  8 ) & 0xFF ), ( ( $ip       ) & 0xFF ) );
	}
	return($return);
}                                                                                                            

function convert_date($date_in)
{

	list($date, $time) = split(" ", $date_in);
// UK/European format
//	list($day, $month, $year) = split("-", $date);
// Original phpBB format
	list($year, $month, $day) = split("-", $date);
	list($hours, $minutes) = split(":", $time);
	$timestamp = gmmktime($hours, $minutes, 0, $month, $day, $year);             

	return($timestamp);
}

//
// Following functions adapted from phpMyAdmin
//
// Return table's CREATE definition 
// Returns a string containing the CREATE statement on success
//
function get_table_def($db, $table, $crlf) {

	$schema_create = "";
 	$schema_create .= "CREATE TABLE $table ($crlf";

 	$result =$db->sql_query("SHOW FIELDS FROM $table");
	if(!$result)
	{
		$error = $db->sql_error();
		error_die($db, GENERAL_ERROR, "Failed in get_table_def (show fields) : ".$error["message"]);
	}
 	while ($row = $db->sql_fetchrow($result)) {
       	$schema_create .= "   $row[Field] $row[Type]";
       
		if (!empty($row["Default"])){
          		$schema_create .= " DEFAULT '$row[Default]'";
          	}
       	if ($row["Null"] != "YES"){
          		$schema_create .= " NOT NULL";
          	}
       	if ($row["Extra"] != ""){
          		$schema_create .= " $row[Extra]";
          	}
       
		$schema_create .= ",$crlf";
	}

 	$schema_create = ereg_replace(",".$crlf."$", "", $schema_create);
 	$result = $db->sql_query("SHOW KEYS FROM $table");
	if(!$result)
	{
		$error = $db->sql_error();
		error_die($db, GENERAL_ERROR, "Failed in get_table_content (show keys) : ".$error["message"]);
	}
 
	while ($row = $db->sql_fetchrow($result)){
		$kname=$row['Key_name'];
	
		if (($kname != "PRIMARY") && ($row['Non_unique'] == 0)){
			$kname="UNIQUE|$kname";
		}
 		if(!is_array($index[$kname])){
 			$index[$kname] = array();
 		}
 		$index[$kname][] = $row['Column_name'];
 	}

	while(list($x, $columns) = @each($index)){
 		$schema_create .= ",$crlf";
 		if($x == "PRIMARY"){
 			$schema_create .= "   PRIMARY KEY (" . implode($columns, ", ") . ")";
 		} else if (substr($x,0,6) == "UNIQUE") {
			$schema_create .= "   UNIQUE ".substr($x,7)." (" . implode($columns, ", ") . ")";
		} else {
			$schema_create .= "   KEY $x (" . implode($columns, ", ") . ")";
 		}
 	}
 
	$schema_create .= "$crlf);";
 
	return (stripslashes($schema_create));
} 

//
// Get the content of table as a series of INSERT statements.
// After every row, a custom callback function $handler gets called.
// $handler must accept one parameter ($sql_insert);
//
function get_table_content($db, $table, $handler) {

	$result = $db->sql_query("SELECT * FROM $table");
	if(!$result)
	{
		$error = $db->sql_error();
		error_die($db, GENERAL_ERROR, "Failed in get_table_content (select * ) : ".$error["message"]);
	}
	$i = 0;
	
	while ($row = $db->sql_fetchrow($result)) {
      
       	$schema_insert = "INSERT INTO $table VALUES(";

		for ($j=0; $j<$db->sql_numfields($result);$j++) {
       		if (!isset($row[$j])) {
              		$schema_insert .= " NULL,";
            	} elseif ($row[$j] != "") {
              		$schema_insert .= " '".addslashes($row[$j])."',";
            	} else {
	      		$schema_insert .= " '',";
	      	}
      	}
       	$schema_insert = ereg_replace(",$", "", $schema_insert);
       	$schema_insert .= ");";
       	$handler(trim($schema_insert));
      	$i++;
      }

	return (true);
}

function output_table_content($content){
	echo $content."\n";
	
	return;
}

//
// Nathan's bbcode2 conversion routines
//
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

//
// End function defns
//



?>
<?php
if(isset($next)) 
{
	switch($next) 
	{
		case 'backup':

			$tables = array("access", "banlist", "catagories", "config", "disallow", "forum_access", "forum_mods", "forums", "headermetafooter", "posts", "priv_msgs", "ranks", "themes", "topics", "users", "words");

			if(!isset($backupstart))
			{

				common_header();
				echo "<H2>Step 1: Backup critical tables.</H2><BR>";
				echo "<P>Click the Backup button to commence a download of the critical tables in your current database. You will be prompted for a download location for the backup file, please note that the download may be large (and hence take a long time) depending on how many posts/users/forums/etc. you have. When the download has completed (and not before!) click the Next button to continue.</P><BR>\n\n";
				echo "<CENTER><FORM METHOD=\"post\" ACTION=\"$PHP_SELF\">\n";
				echo "<INPUT TYPE=\"submit\" NAME=\"backupstart\" VALUE=\"Start Backup\">\n";
				echo "<INPUT TYPE=\"hidden\" NAME=\"next\" VALUE=\"backup\">\n";
				echo "</FORM>\n\n";
				echo "<FORM METHOD=\"post\" ACTION=\"$PHP_SELF\">\n";
				echo "<INPUT TYPE=\"submit\" VALUE=\"Next > \">\n";
				echo "<INPUT TYPE=\"hidden\" NAME=\"next\" VALUE=\"create\">\n";
				echo "</FORM></CENTER>\n\n";
				common_footer();
				exit;
			}
			header("Content-Type: text/x-delimtext; name=\"phpbb_db_backup.sql\"");
			header("Content-disposition: filename=phpbb_db_backup.sql");
			header("Pragma: no-cache");
?>
#
# phpBB upgrade script
# Dump of critical tables for $dbname;
#
# DATE  : <?php echo gmdate("d-m-Y H:i:s",time())." GMT\n"; ?>
#
<?

			for($i=0;$i<count($tables);$i++)
			{
				$table_name = $tables[$i];
?>

#
# TABLE : <?php echo $table_name."\n"; ?>
#
<?php

				echo get_table_def($db, $table_name, "")."\n";
				get_table_content($db, $table_name, "output_table_content");

			}
			exit;
		break;

	case 'create':

		common_header();
		echo "<H2>Step 2: Create new tables.</H2><BR>";

		if(file_exists("./db/mysql_schema.sql")){
			$sql_query = fread(fopen("./db/mysql_schema.sql", "r"),filesize("./db/mysql_schema.sql"));
			$sql_lines  = explode(");\n", preg_replace("/(#.*\n)/", "", $sql_query));

			$error = false;
			$uploaded = 0;

			for($i=0;$i<count($sql_lines);$i++){
				if(strlen($sql_lines[$i]) > 2)
				{
					echo $sql_lines[$i].")\n\n<BR><BR>\n\n";
					$sql_lines[$i] = $sql_lines[$i].")";
					$result = $db->sql_query($sql_lines[$i]);
					if(!$result){
						die("Error creating new tables");
					}
				}
			}
		}


?>
<P>Tables created</P>
<FORM METHOD="POST" ACTION="<?php echo $PHP_SELF ?>">
<INPUT TYPE="HIDDEN" NAME="next" VALUE="convert">
<INPUT TYPE="SUBMIT" VALUE="Next >">

<?php

		common_footer();

		break;

	case 'convert':

			if(!isset($convertstart))
			{

				common_header();
				echo "<H2>Step 3: Move the data to the new tables..</H2><BR>";
				echo "<P>Now all the your old phpBB data will be moved and updated (where necessary) to the newly created phpBB2 tables. If you wish you can automatically delete your old data by checking the delete old data box below. Only do this if you backed up your data earlier! Click the Convert button to commence the operation.</P>\n\n";
				echo "<CENTER><FORM METHOD=\"post\" ACTION=\"$PHP_SELF\">\n";
				echo "Delete Old Data&nbsp;<INPUT TYPE=\"checkbox\" NAME=\"deleteolddata\">&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<INPUT TYPE=\"submit\" VALUE=\"Convert\">\n";
				echo "<INPUT TYPE=\"hidden\" NAME=\"convertstart\" VALUE=\"1\">\n";
				echo "<INPUT TYPE=\"hidden\" NAME=\"next\" VALUE=\"convert\">\n";
				echo "</FORM></CENTER>\n\n";
				common_footer();
				exit;
			}

		common_header();

		//
		// Go through tables one by one
		// could do this via arrays I guess ...
		//
		// banlist
		//
		echo "<P>Moving banlist ... ";
		$result = $db->sql_query("SELECT * FROM banlist");
		if(!$result)
		{
			die("Failed selecting from banlist");
		}
		if($db->sql_numrows($result))
		{
			while($row = $db->sql_fetchrow($result))
			{
				$sql = "INSERT INTO phpbb_banlist 
					(ban_id, ban_userid, ban_ip, ban_start, ban_end, ban_time_type) 
						VALUES 
					('".$row["ban_id"]."', '".$row["ban_userid"]."', '".convert_ip($row["ban_ip"])."', '".$row["ban_start"]."', '".$row["ban_end"]."', '".$row["ban_time_type"]."')";
				$insert_result = $db->sql_query($sql);
				if(!$insert_result)
				{
					die("Failed inserting data into phpbb_banlist");
				}
				if(isset($HTTP_POST_VARS["deleteolddata"]))
				{
					$sql = "DELETE FROM banlist WHERE ban_id = '".$row["ban_id"]."'";
					$delete_result = $db->sql_query($sql);
					if(!$delete_result)
					{
						die("Failed deleting data from banlist");
					}
				}
			}
		}
		echo "DONE</P>\n";

		//
		// catagories
		//
		echo "<P>Moving catagories ... ";
		$result = $db->sql_query("SELECT * FROM catagories");
		if(!$result)
		{
			die("Failed selecting from catagories");
		}
		if($db->sql_numrows($result))
		{
			while($row = $db->sql_fetchrow($result))
			{
				$sql = "INSERT INTO phpbb_categories
					(cat_id, cat_title, cat_order)
						VALUES
					('".$row["cat_id"]."', '".$row["cat_title"]."', '".$row["cat_order"]."')";
				$insert_result = $db->sql_query($sql);
				if(!$insert_result)
				{
					die("Failed inserting data into phpbb_categories");
				}
				if(isset($HTTP_POST_VARS["deleteolddata"]))
				{
					$sql = "DELETE FROM catagories WHERE cat_id = '".$row["cat_id"]."'";
					$delete_result = $db->sql_query($sql);
					if(!$delete_result)
					{
						die("Failed deleting data from catagories");
					}
				}
			}
		}
		echo "DONE</P>\n";

		//
		// config
		//
		echo "<P>Moving config ... ";
		$result = $db->sql_query("SELECT * FROM config");
		if(!$result)
		{
			die("Failed selecting from config");
		}
		if($db->sql_numrows($result))
		{
			while($row = $db->sql_fetchrow($result))
			{
				$sql = "INSERT INTO phpbb_config 
					(config_id, sitename, allow_html, allow_bbcode, allow_sig, allow_namechange, selected, posts_per_page, hot_threshold, topics_per_page, allow_theme_create, override_themes, email_sig, email_from, default_lang)
						VALUES
					('".$row["config_id"]."', '".$row["sitename"]."', '".$row["allow_html"]."', '".$row["allow_bbcode"]."',  '".$row["allow_sig"]."', '".$row["allow_namechange"]."', '".$row["selected"]."', '".$row["posts_per_page"]."', '".$row["hot_threshold"]."', '".$row["topics_per_page"]."', '".$row["allow_theme_create"]."', '".$row["override_themes"]."', '".$row["email_sig"]."', '".$row["email_from"]."', '".$row["default_lang"]."')";
				$insert_result = $db->sql_query($sql);
				if(!$insert_result)
				{
					die("Failed inserting data into phpbb_config");
				}
				if(isset($HTTP_POST_VARS["deleteolddata"]))
				{
					$sql = "DELETE FROM config WHERE config_id = '".$row["config_id"]."'";
					$delete_result = $db->sql_query($sql);
					if(!$delete_result)
					{
						die("Failed deleting data from config");
					}
				}
			}
		}
		echo "DONE</P>\n";

		//
		// disallow
		//
		echo "<P>Moving disallow ... ";
		$result = $db->sql_query("SELECT * FROM disallow");
		if(!$result)
		{
			die("Failed selecting from disallow");
		}
		if($db->sql_numrows($result))
		{
			while($row = $db->sql_fetchrow($result))
			{
				$sql = "INSERT INTO phpbb_disallow
					(disallow_id, disallow_username)
						VALUES
					('".$row["disallow_id"]."', '".$row["disallow_username"]."')";
				$insert_result = $db->sql_query($sql);
				if(!$insert_result)
				{
					die("Failed inserting data into phpbb_disallow");
				}
				if(isset($HTTP_POST_VARS["deleteolddata"]))
				{
					$sql = "DELETE FROM disallow WHERE disallow_id = '".$row["disallow_id"]."'";
					$delete_result = $db->sql_query($sql);
					if(!$delete_result)
					{
						die("Failed deleting data from disallow");
					}
				}
			}
		}
		echo "DONE</P>\n";

		//
		// forum_access
		//
		echo "<P>Moving forum_access ... ";
		$result = $db->sql_query("SELECT * FROM forum_access");
		if(!$result)
		{
			die("Failed selecting from forum_access");
		}
		if($db->sql_numrows($result))
		{
			while($row = $db->sql_fetchrow($result))
			{
				$sql = "INSERT INTO phpbb_forum_access
					(forum_id, user_id, can_post)
						VALUES
					('".$row["forum_id"]."', '".$row["user_id"]."', '".$row["can_post"]."')";
				$insert_result = $db->sql_query($sql);
				if(!$insert_result)
				{
					die("Failed inserting data into phpbb_forum_access");
				}
				if(isset($HTTP_POST_VARS["deleteolddata"]))
				{
					$sql = "DELETE FROM forum_access WHERE forum_id = '".$row["forum_id"]."' AND user_id = '".$row["user_id"]."'";
					$delete_result = $db->sql_query($sql);
					if(!$delete_result)
					{
						die("Failed deleting data from forum_access");
					}
				}
			}
		}
		echo "DONE</P>\n";

		//
		// forum_mods
		//
		echo "<P>Moving forum_mods ... ";
		$result = $db->sql_query("SELECT * FROM forum_mods");
		if(!$result)
		{
			die("Failed selecting from forum_mods");
		}
		if($db->sql_numrows($result))
		{
			while($row = $db->sql_fetchrow($result))
			{
				$sql = "INSERT INTO phpbb_forum_mods
					(forum_id, user_id)
						VALUES
					('".$row["forum_id"]."', '".$row["user_id"]."')";
				$insert_result = $db->sql_query($sql);
				if(!$insert_result)
				{
					die("Failed inserting data into phpbb_forum_mods");
				}
				if(isset($HTTP_POST_VARS["deleteolddata"]))
				{
					$sql = "DELETE FROM forum_mods WHERE forum_id = '".$row["forum_id"]."' AND user_id = '".$row["user_id"]."'";
					if(!$delete_result)
					{
						die("Failed deleting data from forum_mods");
					}
				}
			}
		}
		echo "DONE</P>\n";

		//
		// forums
		//
		echo "<P>Moving forums ... ";
		$result = $db->sql_query("SELECT * FROM forums");
		if(!$result)
		{
			die("Failed selecting from forums");
		}
		if($db->sql_numrows($result))
		{
			while($row = $db->sql_fetchrow($result))
			{
				$sql = "SELECT COUNT(*) AS forum_topics FROM topics WHERE forum_id = '".$row["forum_id"]."'";
				$numtopics_result = $db->sql_query($sql);
				if(!$numtopics_result)
				{
					die("Failed obtaining number of topics for this forum!");
				}
				$row["forum_topics"] = $db->sql_fetchfield("forum_topics",-1, $numtopics_result);

				$sql = "SELECT COUNT(*) AS forum_posts, MAX(post_id) AS forum_last_post_id FROM posts WHERE forum_id = '".$row["forum_id"]."'";
				$numposts_result = $db->sql_query($sql);
				if(!$numposts_result)
				{
					die("Failed obtaining number of posts/last_post_id for this forum!");
				}
				$row["forum_posts"] = $db->sql_fetchfield("forum_posts",-1, $numposts_result);
				$row["forum_last_post_id"] = $db->sql_fetchfield("forum_last_post_id",-1, $numposts_result);
				$sql = "INSERT INTO phpbb_forums
					(forum_id, forum_name, forum_desc, forum_access, cat_id, forum_order, forum_type, forum_posts, forum_topics, forum_last_post_id)
						VALUES
					('".$row["forum_id"]."', '".addslashes($row["forum_name"])."', '".addslashes($row["forum_desc"])."', '".$row["forum_access"]."', '".$row["cat_id"]."', '".$row["forum_order"]."', '".$row["forum_type"]."', '".$row["forum_posts"]."', '".$row["forum_topics"]."', '".$row["forum_last_post_id"]."')";
				$insert_result = $db->sql_query($sql);
				if(!$insert_result)
				{
					die("Failed inserting data into phpbb_forums");
				}
				if(isset($HTTP_POST_VARS["deleteolddata"]))
				{
					$sql = "DELETE FROM forums WHERE forum_id = '".$row["forum_id"]."' AND cat_id = '".$row["cat_id"]."'";
					$delete_result = $db->sql_query($sql);
					if(!$delete_result)
					{
						die("Failed deleting data from forum_mods");
					}
				}
			}
		}
		echo "DONE</P>\n";

		//
		// headermetafooter
		//
		echo "<P>Moving headermetafooter ... ";
		$result = $db->sql_query("SELECT * FROM headermetafooter");
		if(!$result)
		{
			die("Failed selecting from headermetafooter");
		}
		if($db->sql_numrows($result))
		{
			while($row = $db->sql_fetchrow($result))
			{
				$sql = "INSERT INTO phpbb_headermetafooter
					(header, meta, footer)
						VALUES
					('".$row["header"]."', '".$row["meta"]."', '".$row["footer"]."')";
				$insert_result = $db->sql_query($sql);
				if(!$insert_result)
				{
					die("Failed inserting data into phpbb_headermetafooter");
				}
				if(isset($HTTP_POST_VARS["deleteolddata"]))
				{
					$sql = "DELETE FROM headermetafooter WHERE header = '".$row["header"]."' AND meta = '".$row["meta"]."' AND footer = '".$row["footer"]."'";
					$delete_result = $db->sql_query($sql);
					if(!$delete_result)
					{
						die("Failed deleting data from headermetafooter");
					}
				}
			}
		}
		echo "DONE</P>\n";

		//
		// priv_msgs
		//
		echo "<P>Moving priv_msgs ... ";
		$result = $db->sql_query("SELECT * FROM priv_msgs");
		if(!$result)
		{
			die("Failed selecting from priv_msgs");
		}
		if($db->sql_numrows($result))
		{
			while($row = $db->sql_fetchrow($result))
			{
				$sql = "INSERT INTO phpbb_priv_msgs
					(msg_id, from_userid, to_userid, msg_time, poster_ip, msg_status, msg_text)
						VALUES
					('".$row["msg_id"]."', '".$row["from_userid"]."', '".$row["to_userid"]."', '".convert_date($row["msg_time"])."', '".convert_ip($row["poster_ip"])."', '".$row["msg_status"]."', '".addslashes($row["msg_text"])."')";
				$insert_result = $db->sql_query($sql);
				if(!$insert_result)
				{
					die("Failed inserting data into phpbb_priv_msgs");
				}
				if(isset($HTTP_POST_VARS["deleteolddata"]))
				{
					$sql = "DELETE FROM priv_msgs WHERE msg_id = '".$row["msg_id"]."'";
					$delete_result = $db->sql_query($sql);
					if(!$delete_result)
					{
						die("Failed deleting data from priv_msgs");
					}
				}
			}
		}
		echo "DONE</P>\n";

		//
		// ranks
		//
		echo "<P>Moving ranks ... ";
		$result = $db->sql_query("SELECT * FROM ranks");
		if(!$result)
		{
			die("Failed selecting from ranks");
		}
		if($db->sql_numrows($result))
		{
			while($row = $db->sql_fetchrow($result))
			{
				$sql = "INSERT INTO phpbb_ranks
					(rank_id, rank_title, rank_min, rank_max, rank_special, rank_image)
						VALUES
					('".$row["rank_id"]."', '".addslashes($row["rank_title"])."', '".$row["rank_min"]."', '".$row["rank_max"]."', '".$row["rank_special"]."', '".$row["rank_image"]."')";
				$insert_result = $db->sql_query($sql);
				if(!$insert_result)
				{
					die("Failed inserting data into phpbb_ranks");
				}
				if(isset($HTTP_POST_VARS["deleteolddata"]))
				{
					$sql = "DELETE FROM ranks WHERE rank_id = '".$row["rank_id"]."'";
					$delete_result = $db->sql_query($sql);
					if(!$delete_result)
					{
						die("Failed deleting data from ranks");
					}
				}
			}
		}
		echo "DONE</P>\n";

		//
		// themes
		//
		echo "<P>Moving ranks ... ";
		$result = $db->sql_query("SELECT * FROM themes");
		if(!$result)
		{
			die("Failed selecting from themes");
		}
		if($db->sql_numrows($result))
		{
			while($row = $db->sql_fetchrow($result))
			{
				$sql = "INSERT INTO phpbb_themes
					(theme_id, theme_name, bgcolor, textcolor, color1, color2, table_bgcolor, header_image, newtopic_image, reply_image, linkcolor, vlinkcolor, theme_default, fontface, fontsize1, fontsize2, fontsize3, fontsize4, tablewidth, replylocked_image)
						VALUES
					('".$row["theme_id"]."', '".addslashes($row["theme_name"])."', '".$row["bgcolor"]."', '".$row["textcolor"]."', '".$row["color1"]."', '".$row["color2"]."', '".$row["table_bgcolor"]."', '".$row["header_image"]."', '".$row["newtopic_image"]."', '".$row["reply_image"]."', '".$row["linkcolor"]."', '".$row["vlinkcolor"]."', '".$row["theme_default"]."', '".$row["fontface"]."', '".$row["fontsize1"]."', '".$row["fontsize2"]."', '".$row["fontsize3"]."', '".$row["fontsize4"]."', '".$row["tablewidth"]."', '".$row["replylocked_image"]."')";
				$insert_result = $db->sql_query($sql);
				if(!$insert_result)
				{
					die("Failed inserting data into phpbb_themes");
				}
				if(isset($HTTP_POST_VARS["deleteolddata"]))
				{
					$sql = "DELETE FROM themes WHERE theme_id = '".$row["theme_id"]."'";
					$delete_result = $db->sql_query($sql);
					if(!$delete_result)
					{
						die("Failed deleting data from themes");
					}
				}
			}
		}
		echo "DONE</P>\n";

		//
		// topics
		//
		echo "<P>Moving topics ... ";
		$result = $db->sql_query("SELECT * FROM topics ORDER BY topic_time DESC");
		if(!$result)
		{
			die("Failed selecting from topics");
		}
		if($db->sql_numrows($result))
		{
			while($row = $db->sql_fetchrow($result))
			{
				$sql = "SELECT COUNT(*) AS topic_replies, MAX(post_id) AS topic_last_post_id FROM posts WHERE topic_id = '".$row["topic_id"]."'";
				$numtopics_result = $db->sql_query($sql);
				if(!$numposts_result)
				{
					die("Failed obtaining number of replies/last_post_id for this topic!");
				}
				$row["topic_replies"] = $db->sql_fetchfield("topic_replies",-1, $numtopics_result) - 1;
				$row["topic_last_post_id"] = $db->sql_fetchfield("topic_last_post_id",-1, $numtopics_result);
				$sql = "INSERT INTO phpbb_topics
					(topic_id, topic_title, topic_poster, topic_time, topic_views, topic_replies, forum_id, topic_status, topic_notify, topic_last_post_id)
						VALUES
					('".$row["topic_id"]."', '".addslashes($row["topic_title"])."', '".$row["topic_poster"]."', '".convert_date($row["topic_time"])."', '".$row["topic_views"]."', '".$row["topic_replies"]."', '".$row["forum_id"]."', '".$row["topic_status"]."', '".$row["topic_notify"]."', '".$row["topic_last_post_id"]."')";
				$insert_result = $db->sql_query($sql);
				if(!$insert_result)
				{
					die("Failed inserting data into phpbb_topics");
				}
				if(isset($HTTP_POST_VARS["deleteolddata"]))
				{
					$sql = "DELETE FROM topics WHERE topic_id = '".$row["topic_id"]."'";
					$delete_result = $db->sql_query($sql);
					if(!$delete_result)
					{
						die("Failed deleting data from topics");
					}
				}
			}
		}
		echo "DONE</P>\n";

		//
		// posts/post_text
		//
		echo "<P>Moving posts & post_text ... ";
		$result = $db->sql_query("SELECT * FROM posts ORDER BY post_id");
		if(!$result)
		{
			die("Failed selecting from posts");
		}
		if($db->sql_numrows($result))
		{
			while($row = $db->sql_fetchrow($result))
			{
				//
				// Nathan's bbcode2 conversion
				//
				// undo 1.2.x encoding..
				$row['post_text'] = bbdecode($row['post_text']);
				$row['post_text'] = undo_make_clickable($row['post_text']);
				$row['post_text'] = str_replace("<BR>", "\n", $row['post_text']);
				// make a uid
				$uid = make_bbcode_uid();
				// do 2.x first-pass encoding..
				$row['post_text'] = bbencode_first_pass($row['post_text'], $uid);
				$row['post_text'] = addslashes($row['post_text']);
	
				$sql = "INSERT INTO phpbb_posts
					(post_id, topic_id, forum_id, poster_id, post_time, poster_ip, bbcode_uid)
						VALUES
					('".$row["post_id"]."', '".$row["topic_id"]."', '".$row["forum_id"]."', '".$row["poster_id"]."', '".convert_date($row["post_time"])."', '".convert_ip($row["poster_ip"])."', '".$uid."')";
				$insert_result = $db->sql_query($sql);
				if(!$insert_result)
				{
					die("Failed inserting data into phpbb_posts");
				}
				$sql = "INSERT INTO phpbb_posts_text
					(post_id, post_text)
						VALUES
					('".$row["post_id"]."', '".addslashes($row["post_text"])."')";
				$insert_result = $db->sql_query($sql);
				if(!$insert_result)
				{
					die("Failed inserting data into phpbb_posts_text");
				}
				if(isset($HTTP_POST_VARS["deleteolddata"]))
				{
					$sql = "DELETE FROM posts WHERE post_id = '".$row["post_id"]."'";
					$delete_result = $db->sql_query($sql);
					if(!$delete_result)
					{
						die("Failed deleting data from posts");
					}
				}
			}
		}
		echo "DONE</P>\n";

		//
		// users
		//
		echo "<P>Moving users ... ";
		$result = $db->sql_query("SELECT * FROM users");
		if(!$result)
		{
			die("Failed selecting from users");
		}
		if($db->sql_numrows($result))
		{
			while($row = $db->sql_fetchrow($result))
			{
				$sql = "INSERT INTO phpbb_users
					(user_id, username, user_regdate, user_password, user_email, user_icq, user_website, user_occ, user_from, user_intrest, user_sig, user_viewemail, user_theme, user_aim, user_yim, user_msnm, user_posts, user_attachsig, user_desmile, user_html, user_bbcode, user_rank, user_avatar, user_level, user_lang, user_actkey, user_newpasswd, user_notify)
						VALUES
					('".$row["user_id"]."', '".($row["username"])."', '".$row["user_regdate"]."', '".$row["user_password"]."', '".$row["user_email"]."', '".$row["user_icq"]."', '".$row["user_website"]."', '".$row["user_occ"]."', '".addslashes($row["user_from"])."', '".addslashes($row["user_intrest"])."', '".addslashes($row["user_sig"])."', '".$row["user_viewemail"]."', '".$row["user_theme"]."', '".$row["user_aim"]."', '".$row["user_yim"]."', '".$row["user_msnm"]."', '".$row["user_posts"]."', '".$row["user_attachsig"]."', '".$row["user_desmile"]."', '".$row["user_html"]."', '".$row["user_bbcode"]."', '".$row["user_rank"]."', '".$row["user_avatar"]."', '".$row["user_level"]."', '".$row["user_lang"]."', '".$row["user_actkey"]."', '".$row["user_newpasswd"]."', '".$row["user_notify"]."')";
				$insert_result = $db->sql_query($sql);
				if(!$insert_result)
				{
					die("Failed inserting data into phpbb_users");
				}
				if(isset($HTTP_POST_VARS["deleteolddata"]))
				{
					$sql = "DELETE FROM users WHERE user_id = '".$row["user_id"]."'";
					$delete_result = $db->sql_query($sql);
					if(!$delete_result)
					{
						die("Failed deleting data from users");
					}
				}
			}
		}
		echo "DONE</P>\n";

		//
		// words
		//
		echo "<P>Moving words ... ";
		$result = $db->sql_query("SELECT * FROM words");
		if(!$result)
		{
			die("Failed selecting from words");
		}
		if($db->sql_numrows($result))
		{
			while($row = $db->sql_fetchrow($result))
			{
				$sql = "INSERT INTO phpbb_words
					(word_id, word, replacement)
						VALUES
					('".$row["word_id"]."', '".addslashes($row["word"])."', '".addslashes($row["replacement"])."')";
				$insert_result = $db->sql_query($sql);
				if(!$insert_result)
				{
					die("Failed inserting data into phpbb_words");
				}
				if(isset($HTTP_POST_VARS["deleteolddata"]))
				{
					$sql = "DELETE FROM words WHERE word_id = '".$row["word_id"]."'";
					$delete_result = $db->sql_query($sql);
					if(!$delete_result)
					{
						die("Failed deleting data from words");
					}
					echo "<BR>\n";
				}
			}
		}
		echo "DONE<BR>\n";


		if(isset($HTTP_POST_VARS["deleteolddata"]))
		{
			$old_tables = array("access", "banlist", "catagories", "config", "disallow", "forum_access", "forum_mods", "forums", "headermetafooter", "posts", "priv_msgs", "ranks", "sessions", "smiles", "themes", "topics", "users", "whosonline", "words");

			echo "<P>Removing phpBB 1.x tables ... ";

			for($i=0;$i<count($old_tables);$i++)
			{
				$result = $db->sql_query("DROP TABLE ".$old_tables[$i]);
				if(!$result)
				{
					die("Couldn't drop table : ".$old_tables[$i]);
				}
			}

			echo "DONE</P>";
		}

?>
<P>All operations completed!</P>
<P>You should now clear your browsers cookie cache and try accessing your new phpBB2 install.</P>
<?php
		common_footer();

		break;

	} // switch
} // if next
else 
{
	common_header();
?>

<P>Welcome!  This script will upgrade your phpBB v1.2 database to version 2.0. <B>Please note</B> that it assumes a clean database structure is present. If you've introduced any hacks which change the field names or types, or have added additional fields or tables then this script may well fail.</P>

<P>The upgrade will perform the following functions:

<UL>
	<LI>Back up all critical tables to a downloadable file.
	<LI>Create the new tables.
	<LI>Move the data to the new setup.
</UL>

<FORM METHOD="POST" ACTION="<?php echo $PHP_SELF ?>">
	<INPUT TYPE="HIDDEN" NAME="next" VALUE="backup">
	<INPUT TYPE="SUBMIT" VALUE="Next >">
  </FORM>
<?php      
}
?>
</BODY>
</HTML>
