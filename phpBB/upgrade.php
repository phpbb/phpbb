<?php
/***************************************************************************
*                                  upgrade.php
*                              -------------------
*     begin                : Wed Sep 05 2001
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
if ( !defined('INSTALLING') )
{
	//
	// If we are being called from the install script then we don't need these
	// as they are already included.
	//
	include('extension.inc');
	include('config.'.$phpEx);
	include('includes/constants.'.$phpEx);
}
// Force the DB type to be MySQL
$dbms = 'mysql';
include('includes/db.'.$phpEx);
include('includes/bbcode.'.$phpEx);

set_time_limit(0); // Unlimited execution time

$months = array(
	'Jan' => 1,
	'Feb' => 2,
	'Mar' => 3,
	'Apr' => 4,
	'May' => 5,
	'Jun' => 6,
	'Jul' => 7,
	'Aug' => 8,
	'Sep' => 9,
	'Sept' => 9,
	'Oct' => 10,
	'Nov' => 11,
	'Dec' => 12
	);

function common_header()
{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">
<HTML>
<HEAD>
<TITLE>phpBB - Database upgrade 1.4 to 2.0</TITLE>
</HEAD>
<BODY BGCOLOR="#000000" TEXT="#FFFFFF" LINK="#11C6BD" VLINK="#11C6BD">
<?
	return;
}

function query($sql, $errormsg)
{
	global $db;
	if(!$result = $db->sql_query($sql))
	{
		print "<br><font color=\"red\">\n";
		print "$errormsg<br>";
		$sql_error = $db->sql_error();
		print $sql_error['code'] .": ". $sql_error['message']. "<br>\n";
		print "<pre>$sql</pre>";
		print "</font>\n";
		return FALSE;
	}
	else
	{
		return $result;
	}
}

function common_footer()
{
?>
</BODY>
</HTML>
<?

	return;

}

function get_schema()
{
	global $table_prefix;
	$schemafile = file('db/schemas/mysql_schema.sql');
	$tabledata = 0;
	for($i=0; $i < count($schemafile); $i++)
	{
		$line = $schemafile[$i];
		if(preg_match("/^CREATE TABLE (\w+)/i", $line, $matches))
		{
			// Start of a new table definition, set some variables and go to the next line.
			$tabledata = 1;
			// Replace the 'phpbb_' prefix by the user defined prefix.
			$table = str_replace("phpbb_", $table_prefix, $matches[1]);
			$table_def[$table] = "CREATE TABLE $table (\n";
			continue;
		}
		if(preg_match("/^\);/", $line))
		{
			// End of the table definition
			// After this we will skip everything until the next 'CREATE' line
			$tabledata = 0;
			$table_def[$table] .= ")"; // We don't need the closing semicolon
		}
		if($tabledata == 1)
		{
			// We are inside a table definition, parse this line.
			// Add the current line to the complete table definition:
			$table_def[$table] .= $line;
			if(preg_match("/^\s*(\w+)\s+(\w+)\((\d+)\)(.*)$/", $line, $matches))
			{
				// This is a column definition
				$field = $matches[1];
				$type = $matches[2];
				$size = $matches[3];
				preg_match("/DEFAULT (NULL|\'.*?\')[,\s](.*)$/i", $matches[4], $match);
				$default = $match[1];
				preg_match("/NOT NULL/i", $matches[4]) ? $notnull = 1 : $notnull =0;
				preg_match("/auto_increment/i", $matches[4]) ? $auto_increment = 1 : $auto_increment = 0;
				/*
				$i%2 == 1 ? $color = "#FF0000" : $color = "#0000FF"; 
				print "<font color = $color>\n";
				print "$line<br>\n";
				print "$field $type($size)";
				if (isset($default)){
					print " DEFAULT $default";
				}
				if ($notnull == 1)
				{
					print " NOT NULL";
				}
				if ($auto_increment == 1)
				{
					print " auto_increment";
				}
				print "<br>\n";
				print "<font>\n";
				*/
				$field_def[$table][$field] = array(
					'type' => $type,
					'size' => $size,
					'default' => $default,
					'notnull' => $notnull,
					'auto_increment' => $auto_increment
				);
			}
			
			if(preg_match("/\s*PRIMARY\s+KEY\s*\((.*)\).*/", $line, $matches))
			{
				// Primary key
				$key_def[$table]['PRIMARY'] = $matches[1];
			}
			else if(preg_match("/\s*KEY\s+(\w+)\s*\((.*)\)/", $line, $matches))
			{
				// Normal key
				$key_def[$table][$matches[1]] = $matches[2];
			}
			else if(preg_match("/^\s*(\w+)\s*(.*?),?\s*$/", $line, $matches))
			{
				// Column definition
				$create_def[$table][$matches[1]] = $matches[2];
			}
			else
			{
				print "$line<br>";
				// It's a bird! It's a plane! It's something we didn't expect ;(
			}
		}
	}
	/*
	print "<pre>";
	print_r($schema);
	print "</pre>";
	*/
	$schema['field_def'] = $field_def;
	$schema['table_def'] = $table_def;
	$schema['create_def'] = $create_def;
	$schema['key_def'] = $key_def;
	return $schema;
}

function get_inserts()
{
	global $table_prefix;
	$insertfile = file("db/schemas/mysql_basic.sql");
	for($i=0; $i < count($insertfile); $i++)
	{
		if (preg_match("/^(.*INSERT INTO (.*?)\s.*);$/i", str_replace("phpbb_", $table_prefix, $insertfile[$i]), $matches))
		{
			$returnvalue[$matches[2]][] = $matches[1];
		}
	}
	return $returnvalue;
}

function lock_tables($state, $tables= '')
{
	if($state == 1)
	{
		if(is_array($tables))
		{
			$tables = join(' WRITE, ', $tables);
		}
		$sql = "LOCK TABLES $tables WRITE";
		query($sql, "Couldn't do: $sql");
	}
	else
	{
		query("UNLOCK TABLES", "Couldn't unlock all tables");
	}
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

//
// Alternative for in_array() which is only available in PHP4
//
function inarray($needle, $haystack) { 
	for( $i=0 ; $i < sizeof($haystack) ; $i++ )
	{ 
		if($haystack[$i]==$needle)
		{ 
			return true; 
			break; 
		} 
		else
		{ 
			$value=false; 
		} 
	} 
	return $value; 
}

function end_step($next)
{
	global $debug;
	if(!isset($debug))
	{
		// Print out link to next step and wait only if we are not debugging.
		print "<br><a href=\"$PHP_SELF?next=$next\">Next step: $next</a><br>\n";
		break;
	}
	else
	{
		print "<HR>Next step: $next<p>\n";
	}
}
//
// End function defns
///////////////////////////////////////////////////////////////////////////////



?>
<?php

// Start at the beginning if the user hasn't specified a specific starting point.
if(!isset($next)) $next = 'start';

// If debug is set we'll do all steps in one go.
$debug=1;

// Parse the MySQL schema file into some arrays.
$schema = get_schema();
$table_def = $schema['table_def'];
$field_def = $schema['field_def'];
$key_def = $schema['key_def'];
$create_def = $schema['create_def'];

/*
print "tables:<br>";
print_r($table_def);
print "create:<br>";
print_r($create_def);
die;
*/

if(isset($next))
{
	switch($next)
	{
	case 'start':

	case 'cleanstart':
		$sql = "DROP TABLE sessions";
		query($sql, "Couldn't drop table 'sessions'");
		$sql = "DROP TABLE themes";
		query($sql, "Couldn't drop table 'themes'");
		end_step('mod_old_tables');

	case 'mod_old_tables':
		common_header();
		echo "<H2>Step 2: Rename tables</H2>\n";

		$modtables = array(
			"banlist" => "banlist",
			"catagories" => "categories",
			"config" => "old_config",
			"forums" => "forums",
			"disallow" => "disallow",
			"posts" => "posts",
			"posts_text" => "posts_text",
			"priv_msgs" => "privmsgs",
			"ranks" => "ranks",
			"smiles" => "smilies",
			"topics" => "topics",
			"users" => "users",
			"words" => "words"
		);
		while(list($old, $new) = each($modtables))
		{
			$sql = "SHOW INDEX FROM $old";
			$result = query($sql, "Couldn't get list of indices for table $old");
			while($row = mysql_fetch_array($result))
			{
				$index = $row['Key_name'];
				if($index != 'PRIMARY')
				{
					query("ALTER TABLE $old DROP INDEX $index", "Couldn't DROP INDEX $old.$index");
				}
			}

			// Rename table
			$new = $table_prefix . $new;
			$sql = "ALTER TABLE $old RENAME $new";
			print "Renaming '$old' to '$new'<br>\n";
			query($sql, "Failed to rename $old to $new");
			
		}
		common_footer();
		end_step('create_tables');
		
	case 'create_tables':
		common_header();
		echo "<H2>Step 2: Create new phpBB2 tables</H2>\n";
		
		// Create array with tables in 'old' database
		$sql = 'SHOW TABLES';
		$result = query($sql, "Couldn't get list of current tables");
		while ($table = $db->sql_fetchrow($result))
		{
			$currenttables[] = $table[0];
		}
		
		// Check what tables we need to CREATE
		while (list($table, $definition) = each ($table_def))
		{
		print "<font color='green'>Table: $table</font><br>";
			if (!inarray($table, $currenttables))
			{
				print "Creating $table: ";
				$result = query($definition, "Couldn't create table $table");
				if($db->sql_affectedrows($result) < 1)
				{
					echo "Couldn't create table (no affected rows)<br>\n";
					print $definition . "<br>\n";
				}
				else
				{
					print "OK<br>\n";
				}
			}
		}
		
		common_footer();
		end_step('create_config');
		
	case 'create_config':
		common_header();
		$inserts = get_inserts();
		print "Inserting new values into new layout config table";
		while(list($table, $inserts_table) = each($inserts))
		{
			if ($table == CONFIG_TABLE)
			{
				while(list($nr, $insert) = each($inserts_table))
				{
					query($insert, "Couldn't insert value into config table");
					print ".";
				}
				print "<br>";
			}
		}
		print "New config table has been created with default values.<p>\n";
		//end_step('convert_config');
		
	case 'convert_config':
		common_header();
		print "Starting!<br>";
		$sql = "SELECT * FROM $table_prefix"."old_config";
		$result = query($sql, "Couldn't get info from old config table");
		$oldconfig = $db->sql_fetchrow($result);
		while(list($name, $value) = each($oldconfig))
		{
			if(is_int($name))
			{
				continue;
			}
			print "Updating $name...<br>\n";
			$sql = "UPDATE ".CONFIG_TABLE." 
				SET config_value = '".stripslashes($value)."'
				WHERE config_name = '$name'";
			query($sql, "Couldn't update config table with values from old config table");
		}
		end_step('droptables');
		
	case 'droptables':
		//drop access, whosonline, sessions (recreate)
		echo "Nothing here yet<br>\n";
		end_step('convert_ip_date');

	case 'convert_ip_date':
		common_header();
		$names[POSTS_TABLE]['poster_ip'] = 'ip';
		$names[POSTS_TABLE]['post_time'] = 'date';
		$names[TOPICS_TABLE]['topic_time'] = 'date';
		$names[PRIVMSGS_TABLE]['poster_ip'] = 'ip';
		$names[PRIVMSGS_TABLE]['msg_time'] = 'date';

		lock_tables(1, array(POSTS_TABLE, TOPICS_TABLE, PRIVMSGS_TABLE));
		while(list($table, $fields) = each($names))
		{
			while(list($field, $type) = each($fields))
			{
				print "Converting $type format of $field in $table...<br>\n";
				flush();
				$function = ($type == 'ip') ? 'INET_ATON' : 'UNIX_TIMESTAMP';
				$sql = "UPDATE $table SET $field = $function($field)";
				query($sql, "Couldn't convert $type format of $table($field)");
			}
		}
		lock_tables(0);
		
		common_footer();
		end_step('fix_addslashes');
		
	case 'fix_addslashes':
		$slashfields[TOPICS_TABLE] = array('topic_title');
		$slashfields[FORUMS_TABLE] = array('forum_desc');
		$slashfields[CATEGORIES_TABLE] = array('cat_title');
		$slashfields[WORDS_TABLE] = array('word', 'replacement');
		$slashfields[RANKS_TABLE] = array('rank_title');
		//convert smilies?

		$slashes = array(
			"\\'" => "'",
			"\\\"" => "\"",
			"\\\\" => "\\");
		$slashes = array(
			"\\'" => "'",
			"\\\"" => "\"",
			"\\\\" => "\\");
		lock_tables(1, array(TOPICS_TABLE, FORUMS_TABLE, CATEGORIES_TABLE, WORDS_TABLE, RANKS_TABLE));
		while(list($table, $fields) = each($slashfields))
		{
			print "Removing slashes from $table table.<br>\n";
			flush();
			while(list($nr, $field) = each($fields))
			{
				reset($slashes);
				while(list($search, $replace) = each($slashes))
				{
					$sql = "UPDATE $table SET $field = REPLACE($field, '".addslashes($search)."', '".addslashes($replace)."')";
					query($sql, "Couldn't remove extraneous slashes from the old data.");
				}
			}
		}
		lock_tables(0);
		end_step('convert_users');

	case 'convert_users':
		$sql = "
			SELECT 
				count(*) as total,
				max(user_id) as maxid 
			FROM ". USERS_TABLE;
		$result = query($sql, "Couldn't get max post_id.");
		$maxid = $db->sql_fetchrow($result);
		$totalposts = $maxid['total'];
		$maxid = $maxid['maxid'];

		$sql = "ALTER TABLE ".USERS_TABLE." 
			ADD user_sig_bbcode_uid CHAR(10),
			MODIFY user_sig text";
		query($sql, "Couldn't add user_sig_bbcode_uid field to users table");

		$batchsize = 1000;
		print "Going to convert BBcode and registration dates in User table.<br>\n";
		flush();
		for($i = 0; $i <= $maxid; $i += $batchsize)
		{
			$batchstart = $i + 1;
			$batchend = $i + $batchsize;
			
			print "Converting Users $batchstart to $batchend<br>\n";
			flush();
			$sql = "SELECT * from ". USERS_TABLE. " WHERE user_id BETWEEN $batchstart AND $batchend";
			$result = query($sql, "Couldn't get ". USERS_TABLE .".user_id $batchstart to $batchend");

			// Array with user fields that we want to check for invalid data (to few characters)
			$checklength = array(
				'user_occ',
				'user_website',
				'user_email',
				'user_from',
				'user_intrest',
				'user_aim',
				'user_yim',
				'user_msnm');

			lock_tables(1, array(USERS_TABLE, GROUPS_TABLE, USER_GROUP_TABLE));
			while($row = mysql_fetch_array($result))
			{
				$sql = "INSERT INTO ".GROUPS_TABLE." (group_name, group_description, group_single_user) VALUES ('".addslashes($row['username'])."', 'Personal User', 1)";
				query($sql, "Wasn't able to insert user ".$row['user_id']." into table ".GROUPS_TABLE);
				$group_id = mysql_insert_id();
				if($group_id != 0)
				{
					$sql = "INSERT INTO ".USER_GROUP_TABLE." (group_id, user_id, user_pending) VALUES ($group_id, ".$row['user_id'].", 0)";
					query($sql, "Wasn't able to insert user ".$row['user_id']." into table ".USER_GROUP_TABLE);
				}
				else
				{
					print "Couldn't get insert ID for ".GROUPS_TABLE." table<br>\n";
				}

				if(is_int($row['user_regdate']))
				{
					// We already converted this post to the new style BBcode, skip this post.
					continue;
				}
				//
				// Nathan's bbcode2 conversion
				//
				// undo 1.2.x encoding..
				$row['user_sig'] = bbdecode(stripslashes($row['user_sig']));
				$row['user_sig'] = undo_make_clickable($row['user_sig']);
				$row['user_sig'] = str_replace("<BR>", "\n", $row['user_sig']);
				// make a uid
				$uid = make_bbcode_uid();
				// do 2.x first-pass encoding..
				$row['user_sig'] = bbencode_first_pass($row['user_sig'], $uid);
				$row['user_sig'] = addslashes($row['user_sig']);

				// Check for invalid info like '-' and '?' for a lot of fields
				reset($checklength);
				while($field = each($checklength))
				{
					$row[$field[1]] = strlen($row[$field[1]]) < 3 ? '' : $row[$field[1]];
				}

				preg_match('/(.*?) (\d{1,2}), (\d{4})/', $row['user_regdate'], $parts);
				$row['user_regdate'] = mktime(0,0,0,$months[$parts[1]], $parts[2], $parts[3]);

				$website = $row['user_website'];
				if(substr(strtolower($website), 0, 7) != "http://")
				{
					$website = "http://" . $website;
				}
				if(strtolower($website) == 'http://'){
					$website = '';
				}
				$row['user_website'] = addslashes($website);
				
				$row['user_icq'] = (ereg("^[0-9]+$", $row['user_icq'])) ? $row['user_icq'] : '';
				reset($checklength);
				while($field = each($checklength))
				{
					if(strlen($row[$field[1]]) < 3)
					{
						$row[$field[1]] = '';
					}
					$row[$field[1]] = addslashes($row[$field[1]]);
				}

				switch($row['user_level'])
				{
					case '4':
						$row['user_level'] = ADMIN;
						break;
					case '-1':
						$row['user_level'] = ANONYMOUS;
						break;
					default:
						$row['user_level'] = USER;
				}

				$sql = "UPDATE ".USERS_TABLE." 
					SET 
						user_sig = '".$row['user_sig']."',
						user_sig_bbcode_uid = '$uid',
						user_regdate = '".$row['user_regdate']."',
						user_website = '".$row['user_website']."',
						user_occ = '".$row['user_occ']."',
						user_email = '".$row['user_email']."',
						user_from = '".$row['user_from']."',
						user_intrest = '".$row['user_intrest']."',
						user_aim = '".$row['user_aim']."',
						user_yim = '".$row['user_yim']."',
						user_msnm = '".$row['user_msnm']."',
						user_level = '".$row['user_userlevel']."'
					WHERE user_id = ".$row['user_id'];
				query($sql, "Couldn't update ".USERS_TABLE." table with new BBcode and regdate for user_id ".$row['user_id']);
			}
			lock_tables(0);
		}
		end_step('convert_posts');


	case 'convert_posts':
		common_header();
		$sql = "ALTER TABLE ".POSTS_TABLE." 
			ADD bbcode_uid char(10) NOT NULL,
			ADD enable_sig tinyint(1) DEFAULT '1' NOT NULL";
		print "Adding bbcode_uid field to ".POSTS_TABLE.".<br>\n";
		$result = query($sql, "Couldn't get add bbcode_uid field to ".POSTS_TABLE.".");
		
		$sql = "
			SELECT 
				count(*) as total,
				max(post_id) as maxid 
			FROM ". POSTS_TEXT_TABLE;
		$result = query($sql, "Couldn't get max post_id.");
		$maxid = $db->sql_fetchrow($result);
		$totalposts = $maxid['total'];
		$maxid = $maxid['maxid'];

		$batchsize = 2000;
		print "Going to convert BBcode in posts with $batchsize messages at a time and $totalposts in total.<br>\n";
		for($i = 0; $i <= $maxid; $i += $batchsize)
		{
			$batchstart = $i + 1;
			$batchend = $i + $batchsize;
			
			print "Converting BBcode in post number $batchstart to $batchend<br>\n";
			flush();
			$sql = "
				SELECT 
					pt.*,
					p.bbcode_uid
				FROM "
					.POSTS_TEXT_TABLE." pt,"
					.POSTS_TABLE." p
				WHERE pt.post_id = p.post_id
				&& pt.post_id BETWEEN $batchstart AND $batchend";
			$result = query($sql, "Couldn't get ". POSTS_TEXT_TABLE .".post_id $batchstart to $batchend");
			lock_tables(1, array(POSTS_TABLE, POSTS_TEXT_TABLE));
			while($row = mysql_fetch_array($result))
			{
				if($row['bbcode_uid'] != '')
				{
					// We already converted this post to the new style BBcode, skip this post.
					continue;
				}
				//
				// Nathan's bbcode2 conversion
				//
				// undo 1.2.x encoding..
				$row['post_text'] = bbdecode(stripslashes($row['post_text']));
				$row['post_text'] = undo_make_clickable($row['post_text']);
				$row['post_text'] = str_replace("<BR>", "\n", $row['post_text']);
				// make a uid
				$uid = make_bbcode_uid();
				// do 2.x first-pass encoding..
				$row['post_text'] = bbencode_first_pass($row['post_text'], $uid);
				$row['post_text'] = addslashes($row['post_text']);
				
				$checksig = preg_replace('/\[addsig\]$/', '', $row['post_text']);
				if(strlen($checksig) == strlen($row['post_text']))
				{
					$enable_sig = 0;
				}
				else
				{
					$enable_sig = 1;
				}

				$sql = "UPDATE ".POSTS_TEXT_TABLE." 
					SET post_text = '$checksig' 
					WHERE post_id = ".$row['post_id'];
				query($sql, "Couldn't update ".POSTS_TEXT_TABLE." table with new BBcode for post_id ".$row['post_id']);
				$sql = "UPDATE ".POSTS_TABLE." 
					SET bbcode_uid = '$uid', enable_sig = $enable_sig
					WHERE post_id = ".$row['post_id'];
				query($sql, "Couldn't update ".POSTS_TABLE." table with bbcode_uid of post_id ".$row['post_id']);
			}
			lock_tables(0);
		}
		end_step('convert_pm');

	case 'convert_pm':
		$sql = "
			SELECT 
				count(*) as total,
				max(msg_id) as maxid 
			FROM ". PRIVMSGS_TABLE;
		$result = query($sql, "Couldn't get max privmsgs_id.");
		$maxid = $db->sql_fetchrow($result);
		$totalposts = $maxid['total'];
		$maxid = $maxid['maxid'];

		$sql = "ALTER TABLE ".PRIVMSGS_TABLE." 
			ADD privmsgs_subject VARCHAR(255),
			ADD privmsgs_attach_sig TINYINT(1) DEFAULT 1";
		query($sql, "Couldn't add privmsgs_subject field to ".PRIVMSGS_TABLE." table");

		$batchsize = 2000;
		print "Going to convert Private messsages with $batchsize messages at a time and $totalposts in total.<br>\n";
		for($i = 0; $i <= $maxid; $i += $batchsize)
		{
			$batchstart = $i + 1;
			$batchend = $i + $batchsize;
			
			print "Converting Private Message number $batchstart to $batchend<br>\n";
			flush();
			$sql = "
				SELECT 
					*
				FROM "
					.PRIVMSGS_TABLE."
				WHERE
					msg_id BETWEEN $batchstart AND $batchend";
			$result = query($sql, "Couldn't get ". POSTS_TEXT_TABLE .".post_id $batchstart to $batchend");
			lock_tables(1, array(PRIVMSGS_TABLE, PRIVMSGS_TEXT_TABLE));
			while($row = mysql_fetch_array($result))
			{
				if($row['msg_text'] == NULL)
				{
					// We already converted this post to the new style BBcode, skip this post.
					continue;
				}
				//
				// Nathan's bbcode2 conversion
				//
				// undo 1.2.x encoding..
				$row['msg_text'] = bbdecode(stripslashes($row['msg_text']));
				$row['msg_text'] = undo_make_clickable($row['msg_text']);
				$row['msg_text'] = str_replace("<BR>", "\n", $row['msg_text']);
				// make a uid
				$uid = make_bbcode_uid();
				// do 2.x first-pass encoding..
				$row['msg_text'] = bbencode_first_pass($row['msg_text'], $uid);
				
				$checksig = preg_replace('/\[addsig\]$/', '', $row['msg_text']);
				if(strlen($checksig) == strlen($row['msg_text']))
				{
					$enable_sig = 0;
				}
				else
				{
					$enable_sig = 1;
				}
				$row['msg_text'] = $checksig;
				
				if($row['msg_status'] == 1)
				{
					// Private message has been read
					$row['msg_status'] = PRIVMSGS_SAVED_IN_MAIL;
				}
				else
				{
					// Private message is new
					$row['msg_status'] = PRIVMSGS_NEW_MAIL;
				}
				

				// Subject contains first 60 characters of msg
				$subject = addslashes(strip_tags(substr($row['msg_text'], 0, 60)));
				
				$row['msg_text'] = addslashes($row['msg_text']);

				$sql = "INSERT INTO ".PRIVMSGS_TEXT_TABLE." 
					(privmsgs_text_id, privmsgs_bbcode_uid, privmsgs_text)
					VALUES
					('".$row['msg_id']."', '$uid', '".$row['msg_text']."')";
				query($sql, "Couldn't insert PrivMsg text into ".PRIVMSGS_TEXT_TABLE." table msg_id ".$row['msg_id']);
				$sql = "UPDATE ".PRIVMSGS_TABLE." 
					SET 
						msg_text = NULL,
						msg_status = ".$row['msg_status'].",
						privmsgs_subject = '$subject',
						privmsgs_attach_sig = $enable_sig
					WHERE msg_id = ".$row['msg_id'];
				query($sql, "Couldn't update ".PRIVMSGS_TABLE." table for msg_id ".$row['post_id']);
			}
				
		}
		lock_tables(0);
		end_step('convert_mods');
		
	case 'convert_mods';
		echo "<h3>Converting moderator table</h3>";
		$sql = "SELECT * FROM forum_mods";
		$result = query($sql, "Couldn't get list with all forum moderators");
		while($row = mysql_fetch_array($result))
		{
			// Check if this moderator and this forum still exist
			$sql = "SELECT NULL from ".USERS_TABLE.", ".FORUMS_TABLE." WHERE user_id = ".$row['user_id']." AND forum_id = ".$row['forum_id'];
			$check_data = query($sql, "Couldn't check if user ".$row['user_id']." and forum ".$row['forum_id']." exist");
			if(mysql_numrows($check_data) == 0)
			{
				// Either the moderator or the forum have been deleted, this line in forum_mods was redundant, skip it.
				continue;
			}

			$sql = "
				SELECT 
					g.group_id 
				FROM ".
					GROUPS_TABLE." g, ".
					USER_GROUP_TABLE." ug 
				WHERE 
					g.group_id=ug.group_id 
					AND ug.user_id = ".$row['user_id']."
					AND g.group_single_user = 1
				";
			$insert_group = query($sql, "Couldn't get group number for user ".$row['user_id'].".");
			$group_id = mysql_fetch_array($insert_group);
			$group_id = $group_id['group_id'];

			$sql = "INSERT INTO ".AUTH_ACCESS_TABLE." (group_id, forum_id, auth_mod) VALUES ($group_id, ".$row['forum_id'].", 1)";
			query($sql, "Couldn't set moderator (user_id = ".$row['user_id'].") for forum ".$row['forum_id'].".");
		}
		
		
		
	
		end_step('update_schema');

	case 'update_schema':
		common_header();
		$rename = 
			array(
				$table_prefix."users" => array(
					"user_interest" => "user_intrest",
					"user_allowsmile" => "user_desmile",
					"user_allowhtml" => "user_html",
					"user_allowbbcode" => "user_bbcode" 	
					),
				$table_prefix."privmsgs" => array(
					 "privmsgs_id" => "msg_id",
					 "privmsgs_from_userid" => "from_userid",
					 "privmsgs_to_userid" => "to_userid",
					 "privmsgs_date" => "msg_time",
					 "privmsgs_ip" => "poster_ip",
					 "privmsgs_type" => "msg_status" 
					),
				$table_prefix."smilies" => array(
					"smilies_id" => "id"
					)
			);
		$schema = get_schema();
		$table_def = $schema['table_def'];
		$field_def = $schema['field_def'];

		//print "<pre>";
		//print_r($field_def);
		//print "</pre>";
		// Loop tables in schema
		while (list($table, $table_def) = each($field_def))
		{
			// Loop fields in table
			print "<p>Table: $table<br>\n";
			flush();
			
			$sql = "SHOW FIELDS FROM $table";
			$result = query($sql, "Can't get definition of current $table table");
			$current_def = $db->sql_fetchrowset($result);
			while(list($nr, $def) = each($current_def))
			{
				$current_fields[] = $def['Field'];
			}
			//print_r($current_fields);
			
			$alter_sql = "ALTER TABLE $table ";
			while (list($field, $definition) = each($table_def))
			{
				if ($field == '')
				{
					// Skip empty fields if any (shouldn't be needed)
					continue;
				}
				$type = $definition['type'];
				$size = $definition['size'];
				$default = isset($definition['default']) ? "DEFAULT " . $definition['default'] : '';
				$notnull = $definition['notnull'] == 1 ? 'NOT NULL' : '';
				$auto_increment = $definition['auto_increment'] == 1 ? 'auto_increment' : '';
				$oldfield = isset($rename[$table][$field]) ? $rename[$table][$field] : $field;
				if(!inarray($field, $current_fields) && $oldfield == $field)
				{
					// If the current is not a key of $current_def and it is not a field that is 
					// to be renamed then the field doesn't currently exist.
				//$changes[] = "\nADD $field $type($size) $default $notnull $auto_increment ";
					$changes[] = "\nADD $field ". $create_def[$table][$field];
				}
				else
				{
				//$changes[] = "\nCHANGE $oldfield $field $type($size) $default $notnull $auto_increment";
					$changes[] = "\nCHANGE $oldfield $field ". $create_def[$table][$field];
				}
			}
			
			$alter_sql .= join(',', $changes);
			unset($changes);
			unset($current_fields);
			
			$sql = "SHOW INDEX FROM $table";
			$result = query($sql, "Couldn't get list of indices for table $table");
			unset($indices);
			while($row = mysql_fetch_array($result))
			{
				$indices[] = $row['Key_name'];
			}
			
			while (list($key_name, $key_field) = each($key_def[$table]) )
			{
				if(!inarray($key_name, $indices))
				{
					$alter_sql .= ($key_name == 'PRIMARY') ? ",\nADD PRIMARY KEY ($key_field)" : ",\nADD INDEX $key_name ($key_field)";
				}
			}
			print "$alter_sql<br>\n";
			query($alter_sql, "Couldn't alter table $table");
			flush();
		}

		end_step('insert_themes');
	case 'insert_themes':
		common_header();
		$inserts = get_inserts();
		print "Inserting new values into new themes table";
		while(list($table, $inserts_table) = each($inserts))
		{
			if ($table == THEMES_TABLE)
			{
				while(list($nr, $insert) = each($inserts_table))
				{
					query($insert, "Couldn't insert value into ".THEMES_TABLE);
					print ".";
				}
				print "<br>";
			}
		}
		//end_step('convert_config');

		
		echo "This is the end....";
		break;
	}
}
?>
