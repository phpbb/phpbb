<?php
/***************************************************************************
*                                  upgrade.php
*                              -------------------
*     begin                : Wed Sep 05 2001
*     copyright            : (C) 2001 The phpBB Group
*     email                : support@phpbb.com
*
*     $Id$
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
	include('includes/functions.'.$phpEx);
}

//
// Force the DB type to be MySQL
//
$dbms = 'mysql';

include('includes/db.'.$phpEx);
include('includes/bbcode.'.$phpEx);

//
// Set smiley path, uncomment the following line to set the path manually
//
//$smiley_path = '/forums/images/smiles/';
//
/*
if( !isset($smiley_path) )
{
	// Did we get a path through the URL?
	if(isset($HTTP_GET_VARS['smiley_path']))
	{
		$smiley_path = $HTTP_GET_VARS['smiley_path'];
	}
	else
	{
		// Check the current directory name
		$base_dir = dirname($PHP_SELF);
		if ($base_dir != '/phpBB2' && $base_dir != '\phpBB2')
		{
			// User isn't installing in the default /phpBB2/ dir, probably installing in 1.4 dir?
			$smiley_path = $base_dir . '/images/smiles/';
		}
		else
		{
			// Fall back to the default 1.4 path
			$smiley_path = '/phpBB/images/smiles/';
		}
	}
}
*/

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


// ---------------
// Begin functions
//
function clean_words($entry, &$search, &$replace)
{
	// Weird, $init_match doesn't work with static when double quotes (") are used...
	static $init_match =   array('^', '$', '&', '(', ')', '<', '>', '`', "'", '|', ',', '@', '_', '?', '%');
	static $init_replace = array(" ", " ", " ", " ", " ", " ", " ", " ", "",  " ", " ", " ", " ", " ", " ");

	static $later_match =   array("-", "~", "+", ".", "[", "]", "{", "}", ":", "\\", "/", "=", "#", "\"", ";", "*", "!");
	static $later_replace = array(" ", " ", " ", " ", " ", " ", " ", " ", " ", " " , " ", " ", " ", " ",  " ", " ", " ");

	static $sgml_match = array("&nbsp;", "&szlig;", "&agrave;", "&aacute;", "&acirc;", "&atilde;", "&auml;", "&aring;", "&aelig;", "&ccedil;", "&egrave;", "&eacute;", "&ecirc;", "&euml;", "&igrave;", "&iacute;", "&icirc;", "&iuml;", "&eth;", "&ntilde;", "&ograve;", "&oacute;", "&ocirc;", "&otilde;", "&ouml;", "&oslash;", "&ugrave;", "&uacute;", "&ucirc;", "&uuml;", "&yacute;", "&thorn;", "&yuml;");
	static $sgml_replace = array(" ", "s", "a", "a", "a", "a", "a", "a", "a", "c", "e", "e", "e", "e", "i", "i", "i", "i", "o", "n", "o", "o", "o", "o", "o", "o", "u", "u", "u", "u", "y", "t", "y");

	static $accent_match = array("ß", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï", "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "þ", "ÿ");
	static $accent_replace = array("s", "a", "a", "a", "a", "a", "a", "a", "c", "e", "e", "e", "e", "i", "i", "i", "i", "o", "n", "o", "o", "o", "o", "o", "o", "u", "u", "u", "u", "y", "t", "y");

	$entry = " " . strip_tags(strtolower($entry)) . " ";

	$entry = str_replace($sgml_match, $sgml_match, $entry);
	$entry = str_replace($accent_match, $accent_replace, $entry);

	// Replace line endings by a space
	$entry = preg_replace("/[\n\r]/is", " ", $entry); 
	// Remove URL's
	$entry = preg_replace("/\b[a-z0-9]+:\/\/[a-z0-9\.\-]+(\/[a-z0-9\?\.%_\-\+=&\/]+)?/si", " ", $entry); 

	// Filter out strange characters like ^, $, &, change "it's" to "its"
	// str_replace with arrays is buggy in some PHP versions so traverse the arrays manually ;(
	for($i = 0; $i < count($init_match); $i++)
	{
		$entry = str_replace($init_match[$i], $init_replace[$i], $entry);
	}

	// Quickly remove BBcode.
	$entry = preg_replace("/\[code:[0-9]+:[0-9a-z]{10,}\].*?\[\/code:[0-9]+:[0-9a-z]{10,}\]/is", " ", $entry); 
	$entry = preg_replace("/\[img\].*?\[\/img\]/is", " ", $entry); 
	$entry = preg_replace("/\[\/?[a-z\*=\+\-]+[0-9a-z]?(\:[a-z0-9]+)?:[a-z0-9]{10,}(\:[a-z0-9]+)?=?.*?\]/si", " ", $entry);
	// URLs
	$entry = preg_replace("/\[\/?[a-z\*]+[=\+\-]?[0-9a-z]+?:[a-z0-9]{10,}[=.*?]?\]/si", " ", $entry);
	$entry = preg_replace("/\[\/?url(=.*?)?\]/si", " ", $entry);
	// Numbers
	$entry = preg_replace("/\b[0-9]+\b/si", " ", $entry); 
	// HTML entities like &1234;
	$entry = preg_replace("/\b&[a-z]+;\b/is", " ", $entry); 
	// 'words' that consist of <2 or >50 characters are removed.
	$entry = preg_replace("/\b[a-z0-9]{1,2}?\b/si", " ", $entry); 
	$entry = preg_replace("/\b[a-z0-9]{50,}?\b/si", " ", $entry); 

	// Remove some more strange characters
	for($i = 0; $i < count($later_match); $i++)
	{
		$entry = str_replace($later_match[$i], $later_replace[$i], $entry);
	}

	return $entry;
}

function split_words(&$entry)
{
	preg_match_all("/\b(\w[\w']*\w+|\w+?)\b/", $entry, $split_entries);

	return $split_entries[1];
}

function remove_common($percent, $delete_common = 0)
{
	global $db;
	
	$sql = "SELECT COUNT(DISTINCT post_id) as total_posts
	   FROM " . SEARCH_MATCH_TABLE;
	$result = query($sql, "Couldn't select post count"); 

	$total_posts = $db->sql_fetchrow($result);

	$total_posts = $total_posts['total_posts'];
	
	$common_threshold = floor($total_posts * ( $percent / 100 ));

	$sql = "SELECT word_id 
		FROM " . SEARCH_MATCH_TABLE . "
		GROUP BY word_id
		HAVING count(word_id) > $common_threshold";
	$result = query($sql, "Couldn't select matches"); 

	$common_words =  $db->sql_numrows($result);

	while($row = $db->sql_fetchrow($result))
	{
		$common_word_ids[] = $row['word_id'];
	}
	$db->sql_freeresult($result);
	
	if(count($common_word_ids) != 0)
	{
		$common_word_ids = implode(',',$common_word_ids);
	}
	else
	{
		// We didn't remove any common words
		return 0;
	}

	$sql = "UPDATE ". SEARCH_WORD_TABLE ."
		SET word_common = 1
		WHERE word_id IN ($common_word_ids)";
	$result = query($sql, "Couldn't update search_wordmatch table common word field"); 

	if( $delete_common)
	{
		$sql = "DELETE FROM ".SEARCH_MATCH_TABLE." 
			WHERE word_id IN ($common_word_ids)";
		$result = query($sql, "Couldn't delete common words"); 
	}
	
	return $common_words;
}

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

function common_footer()
{
?>
</BODY>
</HTML>
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

function smiley_replace($text = "")
{
	global $db;

	static $search, $replace;

	// Did we get the smiley info in a previous call?
	if( !is_array($search) )
	{
		$sql = "SELECT code, smile_url
			FROM smiles";
		$result = query($sql, "Unable to get list of smilies from the DB");

		$smilies = $db->sql_fetchrowset($result);
		usort($smilies, 'smiley_sort');

		$search = array();
		$replace = array();
		for($i = 0; $i < count($smilies); $i++)
		{
			$search[] = '/<IMG SRC=".*?\/' . phpbb_preg_quote($smilies[$i]['smile_url'], '/') .'">/i';
			$replace[] = $smilies[$i]['code'];
		}
	}

	return ( $text != "" ) ? preg_replace($search, $replace, $text) : "";
	
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
		if( preg_match("/(INSERT INTO (\w+)\s.*);/i", str_replace("phpbb_", $table_prefix, $insertfile[$i]), $matches) )
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
		print "<br><a href=\"$PHP_SELF?next=$next\">Next step: <b>$next</b></a><br>\n";
		break;
	}
	else
	{
		print "<HR>Next step: $next<p>\n";
	}
}
//
// End functions
// -------------


//
// Start at the beginning if the user hasn't specified a specific starting point.
//
$next = ( isset($HTTP_GET_VARS['next']) ) ? $HTTP_GET_VARS['next'] : 'start';

// If debug is set we'll do all steps in one go.
$debug = 1;

// Percentage of posts in which a word must appear to be
// deemed 'common'
$common_percent = 40;

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

//
// Get mysql_basic data
//
$inserts = get_inserts();

//
// Get smiley data
//
smiley_replace();

if( !empty($next) )
{
	switch($next)
	{
		case 'start':

		case 'cleanstart':
			end_step('mod_old_tables');

		case 'mod_old_tables':
			common_header();

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

			while( list($old, $new) = each($modtables) )
			{
				$result = query("SHOW INDEX FROM $old", "Couldn't get list of indices for table $old");

				while( $row = $db->sql_fetchrow($result) )
				{
					$index = $row['Key_name'];
					if( $index != 'PRIMARY' )
					{
						query("ALTER TABLE $old DROP INDEX $index", "Couldn't DROP INDEX $old.$index");
					}
				}

				// Rename table
				$new = $table_prefix . $new;

				print "Renaming '$old' to '$new'<br>\n";
				query("ALTER TABLE $old RENAME $new", "Failed to rename $old to $new");
				
			}
			common_footer();
			end_step('create_tables');
			
		case 'create_tables':
			common_header();
			
			// Create array with tables in 'old' database
			$result = query('SHOW TABLES', "Couldn't get list of current tables");

			while( $table = $db->sql_fetchrow($result) )
			{
				$currenttables[] = $table[0];
			}
			
			// Check what tables we need to CREATE
			while( list($table, $definition) = each($table_def) )
			{
				if( !inarray($table, $currenttables) )
				{
					print " * Creating $table: ";

					query($definition, "Couldn't create table $table");

					print "OK<br>\n";
				}
			}
			
			common_footer();
			end_step('create_config');
			
		case 'create_config':
			common_header();

			print "Inserting new values into new layout config table ...";

			@reset($inserts);
			while( list($table, $inserts_table) = each($inserts) )
			{
				if( $table == CONFIG_TABLE )
				{
					while( list($nr, $insert) = each($inserts_table) )
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
			print "Converting configuration table ...<br />\n";

			$sql = "SELECT * 
				FROM $table_prefix" . "old_config";
			$result = query($sql, "Couldn't get info from old config table");

			$oldconfig = $db->sql_fetchrow($result);

			//
			// We don't need several original config types and two others
			// have changed name ... so take account of this.
			//
			$ignore_configs = array("selected", "admin_passwd", "override_themes", "allow_sig");
			$rename_configs = array(
				"email_from" => "board_email",
				"email_sig" => "board_email_sig"
			);

			while( list($name, $value) = each($oldconfig) )
			{
				if( is_int($name) )
				{
					continue;
				}

				if( !inarray($name, $ignore_configs) )
				{
					print " * Updating $name...<br>\n";

					$name = ( !empty($rename_configs[$name]) ) ? $rename_configs[$name] : $name;

					$sql = "REPLACE INTO " . CONFIG_TABLE . " (config_name, config_value) 
						VALUES ('$name', '" . stripslashes($value) . "')";
					query($sql, "Couldn't update config table with values from old config table");
				}
			}
			
			print "<br />\nCompleted<br />\n";
			end_step('convert_ip_date');

		case 'convert_ip':
			common_header();
			print "Converting post IP information ... <br />\n";

			$names = array( 
				POSTS_TABLE => array(
					'id' => 'post_id',
					'field' => 'poster_ip'
				), 
				PRIVMSGS_TABLE => array( 
					'id' => 'msg_id', 
					'field' => 'poster_ip'
				), 
				BANLIST_TABLE => array( 
					'id' => 'ban_id', 
					'field' => 'ban_ip'
				)
			);

			lock_tables(1, array(POSTS_TABLE, PRIVMSGS_TABLE, BANLIST_TABLE));

			while( list($table, $data_array) = each($names) )
			{
				$field_id = $data_array['id'];
				$field = $data_array['field'];

				$sql = "SELECT $field_id, $field 
					FROM $table";
				$result = query($sql, "Couldn't obtain ip data from $table (" . $fields . ")");

				$rowset = $db->sql_fetchrowset($result);

				print " * Converting IP format of " . $field . " in $table ... ";
				flush();

				for($i = 0; $i < count($rowset); $i++)
				{

					$sql = "UPDATE $table 
						SET $field = '" . encode_ip($rowset[$i][$field]) . "' 
						WHERE $field_id = " . $rowset[$i][$field_id];
					query($sql, "Couldn't convert IP format of $field in $table with $field_id of " . $rowset[$field_id]);
				}

				print "Done<br />\n";
			}

			lock_tables(0);

			echo "\n<br />Complete<br />\n";
			common_footer();
			end_step('convert_dates');

		case 'convert_dates':
			common_header();
			print "Converting post and topic date information ... <br />\n";

			$names = array(
				POSTS_TABLE => array('post_time'),
				TOPICS_TABLE => array('topic_time'), 
				PRIVMSGS_TABLE => array('msg_time')
			);

			lock_tables(1, array(POSTS_TABLE, TOPICS_TABLE, PRIVMSGS_TABLE));

			while( list($table, $fields) = each($names) )
			{
				print " * Converting date format of $fields[$i] in $table ... ";
				flush();

				for($i = 0; $i < count($fields); $i++)
				{
					$sql = "UPDATE $table 
						SET " . $fields[$i] . " = UNIX_TIMESTAMP(" . $fields[$i] . ")";
					query($sql, "Couldn't convert date format of $table(" . $fields[$i] . ")");
				}

				print "Done<br />\n";
			}

			echo "\n<br />Complete<br />\n";
			lock_tables(0);
			common_footer();
			end_step('fix_addslashes');

		case 'fix_addslashes':
			$slashfields[TOPICS_TABLE] = array('topic_title');
			$slashfields[FORUMS_TABLE] = array('forum_desc', 'forum_name');
			$slashfields[CATEGORIES_TABLE] = array('cat_title');
			$slashfields[WORDS_TABLE] = array('word', 'replacement');
			$slashfields[RANKS_TABLE] = array('rank_title');
			$slashfields[DISALLOW_TABLE] = array('disallow_username');

			//convert smilies?
			$slashes = array(
				"\\'" => "'",
				"\\\"" => "\"",
				"\\\\" => "\\");
			$slashes = array(
				"\\'" => "'",
				"\\\"" => "\"",
				"\\\\" => "\\");

			lock_tables(1, array(TOPICS_TABLE, FORUMS_TABLE, CATEGORIES_TABLE, WORDS_TABLE, RANKS_TABLE, DISALLOW_TABLE, SMILIES_TABLE));

			while( list($table, $fields) = each($slashfields) )
			{
				print " * Removing slashes from $table table.<br>\n";
				flush();

				while( list($nr, $field) = each($fields) )
				{
					@reset($slashes);
					while( list($search, $replace) = each($slashes) )
					{
						$sql = "UPDATE $table 
							SET $field = REPLACE($field, '" . addslashes($search) . "', '" . addslashes($replace) . "')";
						query($sql, "Couldn't remove extraneous slashes from the old data.");
					}
				}
			}
			lock_tables(0);
			
			end_step('remove_topics');

		case 'remove_topics':
			common_header();
			print "Removing posts with no corresponding topics ... ";
			flush();

			$sql = "SELECT p.post_id 
				FROM " . POSTS_TABLE . " p 
				LEFT JOIN " . TOPICS_TABLE . " t ON p.topic_id = t.topic_id  
				WHERE t.topic_id IS NULL";
			$result = query($sql, "Couldn't obtain list of deleted topics");
			
			$post_total = $db->sql_numrows($result);

			if( $post_total )
			{
				$post_id_ary = array();
				while( $row = $db->sql_fetchrow($result) )
				{
					$post_id_ary[] = $row['post_id'];
				}

				$sql = "DELETE FROM " . POSTS_TABLE . "  
					WHERE post_id IN (" . implode(", ", $post_id_ary) . ")";
				query($sql, "Couldn't update posts to remove deleted user poster_id values");

				$sql = "DELETE FROM " . POSTS_TEXT_TABLE . "
					WHERE post_id IN (" . implode(", ", $post_id_ary) . ")";
				query($sql, "Couldn't update posts to remove deleted user poster_id values");
			}

			echo "Removed $post_total posts ... Done<br />\n";
			end_step('convert_users');

		case 'convert_users':
			$sql = "SELECT COUNT(*) AS total, MAX(user_id) AS maxid 
				FROM " . USERS_TABLE;
			$result = query($sql, "Couldn't get max post_id.");

			$maxid = $db->sql_fetchrow($result);

			$totalposts = $maxid['total'];
			$maxid = $maxid['maxid'];

			$sql = "ALTER TABLE " . USERS_TABLE . " 
				ADD user_sig_bbcode_uid CHAR(10),
				MODIFY user_sig text";
			query($sql, "Couldn't add user_sig_bbcode_uid field to users table");

			print "Going to convert BBcode and registration dates in User table.<br>\n";
			flush();

			$super_mods = array();
			$first_admin = -2;

			$batchsize = 1000;
			for($i = -1; $i <= $maxid; $i += $batchsize)
			{
				$batchstart = $i;
				$batchend = $i + $batchsize;
				
				print " * Converting Users $batchstart to $batchend<br>\n";
				flush();

				$sql = "SELECT * 
					FROM " . USERS_TABLE . " 
					WHERE user_id 
						BETWEEN $batchstart 
							AND $batchend";
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

				lock_tables(1, array(USERS_TABLE, GROUPS_TABLE, USER_GROUP_TABLE, POSTS_TABLE));

				while( $row = $db->sql_fetchrow($result) )
				{
					$sql = "INSERT INTO " . GROUPS_TABLE . " (group_name, group_description, group_single_user) 
						VALUES ('" . addslashes($row['username']) . "', 'Personal User', 1)";
					query($sql, "Wasn't able to insert user ".$row['user_id']." into table ".GROUPS_TABLE);

					$group_id = $db->sql_nextid();

					if( $group_id != 0 )
					{
						$sql = "INSERT INTO " . USER_GROUP_TABLE . " (group_id, user_id, user_pending)	
							VALUES ($group_id, " . $row['user_id'] . ", 0)";
						query($sql, "Wasn't able to insert user ".$row['user_id']." into table ".USER_GROUP_TABLE);
					}
					else
					{
						print "Couldn't get insert ID for " . GROUPS_TABLE . " table<br>\n";
					}

					if( is_int($row['user_regdate']) )
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
					@reset($checklength);
					while($field = each($checklength))
					{
						$row[$field[1]] = strlen($row[$field[1]]) < 3 ? '' : $row[$field[1]];
					}

					preg_match('/(.*?) (\d{1,2}), (\d{4})/', $row['user_regdate'], $parts);
					$row['user_regdate'] = gmmktime(0, 0, 0, $months[$parts[1]], $parts[2], $parts[3]);

					$website = $row['user_website'];
					if( substr(strtolower($website), 0, 7) != "http://" )
					{
						$website = "http://" . $website;
					}
					if( strtolower($website) == 'http://' )
					{
						$website = '';
					}
					$row['user_website'] = addslashes($website);
					
					$row['user_icq'] = (ereg("^[0-9]+$", $row['user_icq'])) ? $row['user_icq'] : '';
					reset($checklength);

					while($field = each($checklength))
					{
						if( strlen($row[$field[1]]) < 3 )
						{
							$row[$field[1]] = '';
						}
						$row[$field[1]] = addslashes($row[$field[1]]);
					}
					
					//
					// Is user a super moderator?
					//
					if( $row['user_level'] == 3 )
					{
						$super_mods[] = $row['user_id'];
					}

					$row['user_level'] = ( $row['user_level'] == 4 ) ? ADMIN : USER;

					//
					// Used to define a 'practical' group moderator user_id
					// for super mods a little latter.
					//
					if( $first_admin == -2 && $row['user_level'] == ADMIN )
					{
						$first_admin = $row['user_id'];
					}

					$sql = "SELECT COUNT(post_id) AS user_posts 
						FROM " . POSTS_TABLE . " 
						WHERE poster_id = " . $row['user_id'];
					$count_result = query($sql, "Couldn't obtain user post count");

					$post_count = $db->sql_fetchrow($count_result);

					$sql = "UPDATE " . USERS_TABLE . " 
						SET 
							user_sig = '" . $row['user_sig'] . "',
							user_sig_bbcode_uid = '$uid',
							user_regdate = '" . $row['user_regdate'] . "',
							user_website = '" . $row['user_website'] . "',
							user_occ = '" . $row['user_occ'] . "',
							user_email = '" . $row['user_email'] . "',
							user_from = '" . $row['user_from'] . "',
							user_intrest = '" . $row['user_intrest'] . "', 
							user_aim = '" . $row['user_aim'] . "',
							user_yim = '" . $row['user_yim'] . "',
							user_msnm = '" . $row['user_msnm'] . "',
							user_level = '" . $row['user_level'] . "', 
							user_posts = " . $post_count['user_posts'] . ", 
							user_desmile = NOT(user_desmile), 
							user_bbcode = 1, 
							user_theme = 1 
						WHERE user_id = " . $row['user_id'];
					query($sql, "Couldn't update ".USERS_TABLE." table with new BBcode and regdate for user_id ".$row['user_id']);
				}

				lock_tables(0);
			}

			//
			// Handle super-mods, create hidden group for them
			//
			// Iterate trough access table
			if( count($super_mods) && $first_admin != -2 )
			{
				print "\n<br /><br />\n * Creating new group for super moderators ... ";
				flush();

				$sql = "INSERT INTO " . GROUPS_TABLE . " (group_type, group_name, group_description, group_moderator, group_single_user)
					VALUES (" . GROUP_HIDDEN . ", 'Super Moderators', 'Converted super moderators', $first_admin, 0)";
				$result = query($sql, "Couldn't create group for ".$row['forum_name']);

				$group_id = $db->sql_nextid();

				if( $group_id <= 0 )
				{
					print "<font color=\"red\">Group creation failed. Aborting creation of groups...<br></font>\n";
					continue 2;
				}

				print "Done<br />\n";

				print " * Updating auth_access for super moderator group ...";
				flush();

				$sql = "SELECT forum_id 
					FROM " . FORUMS_TABLE;
				$result = query($sql, "Couldn't obtain forum_id list");

				while( $row = $db->sql_fetchrow($result) )
				{
					$sql = "INSERT INTO " . AUTH_ACCESS_TABLE . " (group_id, forum_id, auth_mod)
						VALUES ($group_id, " . $row['forum_id'] . ", 1)";
					$result_insert = query($sql, "Unable to set group auth access for super mods.");
				}

				for($i = 0; $i < count($super_mods); $i++)
				{
					$sql = "INSERT INTO " . USER_GROUP_TABLE . " (group_id, user_id, user_pending)
						VALUES ($group_id, " . $super_mods[$i] . ", 0)";
					query($sql, "Unable to add user_id $user_id to group_id $group_id (super mods)<br>\n");
				}
			
				print "Done";
			}

			echo "\n<br />Complete<br />\n";
			end_step('convert_posts');

		case 'convert_posts':
			common_header();

			print "Adding enable_sig field to " . POSTS_TABLE . ".<br>\n";
			$sql = "ALTER TABLE " . POSTS_TABLE . " 
				ADD enable_sig tinyint(1) DEFAULT '1' NOT NULL";
			$result = query($sql, "Couldn't add enable_sig field to " . POSTS_TABLE . ".");
			
			print "Adding enable_bbcode field to " . POSTS_TEXT_TABLE . ".<br>\n";
			$sql = "ALTER TABLE " . POSTS_TEXT_TABLE . "  
				ADD enable_bbcode tinyint(1) DEFAULT '1' NOT NULL";
			$result = query($sql, "Couldn't add enable_bbcode field to " . POSTS_TABLE . ".");

			print "Adding bbcode_uid field to " . POSTS_TEXT_TABLE . ".<br>\n";
			$sql = "ALTER TABLE " . POSTS_TEXT_TABLE . "  
				ADD bbcode_uid char(10) NOT NULL";
			$result = query($sql, "Couldn't add bbcode_uid field to " . POSTS_TABLE . ".");
			
			print "Adding post_edit_time field to " . POSTS_TABLE . ".<br>\n";
			$sql = "ALTER TABLE " . POSTS_TABLE . "  
				ADD post_edit_time int(11)";
			$result = query($sql, "Couldn't add post_edit_time field to " . POSTS_TABLE . ".");

			print "Adding post_edit_count field to " . POSTS_TABLE . ".<br>\n";
			$sql = "ALTER TABLE " . POSTS_TABLE . "  
				ADD post_edit_count smallint(5) UNSIGNED DEFAULT '0' NOT NULL";
			$result = query($sql, "Couldn't add post_edit_count field to " . POSTS_TABLE . ".");

			$sql = "SELECT COUNT(*) as total, MAX(post_id) as maxid 
				FROM " . POSTS_TEXT_TABLE;
			$result = query($sql, "Couldn't get max post_id.");

			$maxid = $db->sql_fetchrow($result);

			$totalposts = $maxid['total'];
			$maxid = $maxid['maxid'];

			print "Going to convert BBcode in posts with $batchsize messages at a time and $totalposts in total<br />\n";

			$batchsize = 2000;
			for($i = 0; $i <= $maxid; $i += $batchsize)
			{
				$batchstart = $i + 1;
				$batchend = $i + $batchsize;
				
				print " * Converting BBcode in post number $batchstart to $batchend<br>\n";
				flush();

				$sql = "SELECT * 
					FROM " . POSTS_TEXT_TABLE . "
					WHERE post_id 
						BETWEEN $batchstart 
							AND $batchend";
				$result = query($sql, "Couldn't get ". POSTS_TEXT_TABLE .".post_id $batchstart to $batchend");

				lock_tables(1, array(POSTS_TEXT_TABLE, POSTS_TABLE));

				while( $row = $db->sql_fetchrow($result) )
				{
					if( $row['bbcode_uid'] != '' )
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
					$row['post_text'] = smiley_replace($row['post_text']);
					$row['post_text'] = bbencode_first_pass($row['post_text'], $uid);
					$row['post_text'] = addslashes($row['post_text']);

					$edited_sql = "";
					if( preg_match("/^(.*?)([\n]+<font size=\-1>\[ This message was .*?)$/s", $row['post_text'], $matches) )
					{
						$row['post_text'] = $matches[1];
						$edit_info = $matches[2];

						$edit_times = count(explode(" message ", $edit_info)) - 1; // Taken from example for substr_count in annotated PHP manual

						if( preg_match("/^.* by: (.*?) on (....)-(..)-(..) (..):(..) \]<\/font>/s", $edit_info, $matches) )
						{
							$edited_user = $matches[1];
							$edited_time = gmmktime($matches[5], $matches[6], 0, $matches[3], $matches[4], $matches[2]);

							//
							// This isn't strictly correct since 2.0 won't include and edit
							// statement if the edit wasn't by the user who posted ...
							//
							$edited_sql = ", post_edit_time = $edited_time, post_edit_count = $edit_times";
						}
					}
	
					if( preg_match("/^(.*?)\n-----------------\n.*$/is", $row['post_text'], $matches) )
					{
						$row['post_text'] = $matches[1];
						$enable_sig = 1;
					}
					else
					{
						$checksig = preg_replace('/\[addsig\]$/', '', $row['post_text']);
						$enable_sig = ( strlen($checksig) == strlen($row['post_text']) ) ? 0 : 1;
					}

					$sql = "UPDATE " . POSTS_TEXT_TABLE . " 
						SET post_text = '$checksig', bbcode_uid = '$uid'
						WHERE post_id = " . $row['post_id'];
					query($sql, "Couldn't update " . POSTS_TEXT_TABLE . " table with new BBcode for post_id :: " . $row['post_id']);

					$sql = "UPDATE " . POSTS_TABLE . " 
						SET enable_sig = $enable_sig" . $edited_sql . " 
						WHERE post_id = " . $row['post_id'];
					query($sql, "Couldn't update " . POSTS_TABLE . " table with signature status for post with post_id :: " . $row['post_id']);
				}

				lock_tables(0);
			}

			print "Updating poster_id for deleted users ... ";
			flush();

			$sql = "SELECT DISTINCT p.post_id 
				FROM " . POSTS_TABLE . " p 
				LEFT JOIN " . USERS_TABLE . " u ON p.poster_id = u.user_id 
				WHERE u.user_id IS NULL";
			$result = query($sql, "Couldn't obtain list of deleted users");
			
			$users_removed = $db->sql_numrows($result);

			if( $users_removed )
			{
				$post_id_ary = array();
				while( $row = $db->sql_fetchrow($result) )
				{
					$post_id_ary[] = $row['post_id'];
				}

				$sql = "UPDATE " . POSTS_TABLE . " 
					SET poster_id = " . ANONYMOUS . ", enable_sig = 0 
					WHERE post_id IN (" . implode(", ", $post_id_ary) . ")";
				query($sql, "Couldn't update posts to remove deleted user poster_id values");
			}

			print "Removed $users_removed Users ... Done<br />\n";

			echo "<br />Complete<br />\n";
			end_step('convert_pm');

		case 'convert_pm':
			common_header();
			print "Converting private messages ... <br />\n";

			$sql = "SELECT COUNT(*) as total, max(msg_id) as maxid 
				FROM " . PRIVMSGS_TABLE;
			$result = query($sql, "Couldn't get max privmsgs_id.");

			$maxid = $db->sql_fetchrow($result);

			$totalposts = $maxid['total'];
			$maxid = $maxid['maxid'];

			$sql = "ALTER TABLE " . PRIVMSGS_TABLE . " 
				ADD privmsgs_subject VARCHAR(255),
				ADD privmsgs_attach_sig TINYINT(1) DEFAULT 1";
			query($sql, "Couldn't add privmsgs_subject field to " . PRIVMSGS_TABLE . " table");

			print "Going to convert Private messsages with $batchsize messages at a time and $totalposts in total.<br>\n";

			$batchsize = 2000;
			for($i = 0; $i <= $maxid; $i += $batchsize)
			{
				$batchstart = $i + 1;
				$batchend = $i + $batchsize;
				
				print " * Converting Private Message numbers $batchstart to $batchend<br />\n";
				flush();

				$sql = "SELECT * 
					FROM " . PRIVMSGS_TABLE . "
					WHERE msg_id 
						BETWEEN $batchstart 
							AND $batchend";
				$result = query($sql, "Couldn't get " . POSTS_TEXT_TABLE . " post_id $batchstart to $batchend");

				lock_tables(1, array(PRIVMSGS_TABLE, PRIVMSGS_TEXT_TABLE));

				while( $row = $db->sql_fetchrow($result) )
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
					$row['msg_text'] = smiley_replace($row['msg_text']);
					$row['msg_text'] = bbencode_first_pass($row['msg_text'], $uid);
					
					$checksig = preg_replace('/\[addsig\]$/', '', $row['msg_text']);
					$enable_sig = (strlen($checksig) == strlen($row['msg_text'])) ? 0 : 1;

					if( preg_match("/^(.*?)\n-----------------\n.*$/is", $checksig, $matches) )
					{
						$checksig = $matches[1];
						$enable_sig = 1;
					}

					$row['msg_text'] = $checksig;
					
					$row['msg_status'] = ($row['msg_status'] == 1) ? PRIVMSGS_READ_MAIL : PRIVMSGS_NEW_MAIL;

					// Subject contains first 60 characters of msg, remove any BBCode tags
					$subject = addslashes(strip_tags(substr($row['msg_text'], 0, 60)));
					$subject = preg_replace("/\[.*?\:(([a-z0-9]:)?)$uid.*?\]/si", "", $subject);
					
					$row['msg_text'] = addslashes($row['msg_text']);

					$sql = "INSERT INTO " . PRIVMSGS_TEXT_TABLE . " (privmsgs_text_id, privmsgs_bbcode_uid, privmsgs_text)
						VALUES ('" . $row['msg_id'] . "', '$uid', '" . $row['msg_text'] . "')";
					query($sql, "Couldn't insert PrivMsg text into " . PRIVMSGS_TEXT_TABLE . " table msg_id " . $row['msg_id']);

					$sql = "UPDATE " . PRIVMSGS_TABLE . " 
						SET msg_text = NULL, msg_status = " . $row['msg_status'] . ", privmsgs_subject = '$subject', privmsgs_attach_sig = $enable_sig
						WHERE msg_id = " . $row['msg_id'];
					query($sql, "Couldn't update " . PRIVMSGS_TABLE . " table for msg_id " . $row['post_id']);
				}
					
			}

			lock_tables(0);
			
			echo "<br />Complete<br />\n";
			end_step('convert_mods');

		case 'convert_mods';
			common_header();
			print "Converting moderators ... <br />\n";

			$sql = "SELECT * 
				FROM forum_mods";
			$result = query($sql, "Couldn't get list with all forum moderators");

			while( $row = $db->sql_fetchrow($result) )
			{
				// Check if this moderator and this forum still exist
				$sql = "SELECT NULL 
					FROM " . USERS_TABLE . ", " . FORUMS_TABLE . " 
					WHERE user_id = " . $row['user_id'] . " 
						AND forum_id = " . $row['forum_id'];
				$check_data = query($sql, "Couldn't check if user ".$row['user_id']." and forum ".$row['forum_id']." exist");

				if( !mysql_numrows($check_data) )
				{
					// Either the moderator or the forum have been deleted, this line in forum_mods was redundant, skip it.
					continue;
				}

				$sql = "SELECT g.group_id 
					FROM " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug 
					WHERE g.group_id=ug.group_id 
						AND ug.user_id = " . $row['user_id'] . "
						AND g.group_single_user = 1";
				$insert_group = query($sql, "Couldn't get group number for user ".$row['user_id'].".");

				$group_id = $db->sql_fetchrow($insert_group);
				$group_id = $group_id['group_id'];

				print " * Adding moderator for forum :: ".$row['forum_id']."<br />\n";

				$sql = "INSERT INTO " . AUTH_ACCESS_TABLE . " (group_id, forum_id, auth_mod) VALUES ($group_id, ".$row['forum_id'].", 1)";
				query($sql, "Couldn't set moderator (user_id = " . $row['user_id'] . ") for forum " . $row['forum_id'] . ".");
			}
			
			echo "<br />Complete<br />\n";
			end_step('convert_privforums');

		case 'convert_privforums':
			common_header();
			print "Converting private forums ... <br />\n";
			
			$sql = "SELECT fa.*, f.forum_name 
					FROM forum_access fa 
					LEFT JOIN " . FORUMS_TABLE . " f ON fa.forum_id = f.forum_id  
					ORDER BY fa.forum_id, fa.user_id";
			$forum_access = query($sql, "Couldn't get list with special forum access (forum_access)");

			$forum_id = -1;
			while( $row = $db->sql_fetchrow($forum_access) )
			{
				// Iterate trough access table
				if( $row['forum_id'] != $forum_id )
				{
					// This is a new forum, create new group.
					$forum_id = $row['forum_id'];

					print " * Creating new group for forum :: $forum_id<br />\n";

					$sql = "INSERT INTO " . GROUPS_TABLE . " (group_type, group_name, group_description, group_moderator, group_single_user)
						VALUES (" . GROUP_HIDDEN . ", '" . addslashes($row['forum_name']) . " Group', 'Converted Private Forum Group', " . $row['user_id'] . ", 0)";
					$result = query($sql, "Couldn't create group for ".$row['forum_name']);

					$group_id = $db->sql_nextid();

					if( $group_id <= 0 )
					{
						print "<font color=\"red\">Group creation failed. Aborting creation of groups...<br></font>\n";
						continue 2;
					}

					print " * Creating auth_access group for forum :: $forum_id<br />\n";

					$sql = "INSERT INTO " . AUTH_ACCESS_TABLE . " (group_id, forum_id, auth_view, auth_read, auth_post, auth_reply, auth_edit, auth_delete)
						VALUES ($group_id, $forum_id, 1, 1, 1, 1, 1, 1)";
					$result = query($sql, "Unable to set group auth access.");

					if( $db->sql_affectedrows($result) <= 0 )
					{
						print "<font color=\"red\">Group creation failed. Aborting creation of groups...</font><br>\n";
						continue 2;
					}
				}

				// Add user to the group
				$user_id = $row['user_id'];

				$sql = "INSERT INTO " . USER_GROUP_TABLE . " (group_id, user_id, user_pending)
					VALUES ($group_id, $user_id, 0)";
				query($sql, "Unable to add user_id $user_id to group_id $group_id <br>\n");
			}

			echo "<br />Complete<br />\n";
			end_step('update_schema');

		case 'update_schema':
			common_header();
			print "Updating DB Schema ... <br />\n";

			$rename = array(
				$table_prefix . "users" => array(
					"user_interests" => "user_intrest",
					"user_allowsmile" => "user_desmile",
					"user_allowhtml" => "user_html",
					"user_allowbbcode" => "user_bbcode", 
					"user_style" => "user_theme" 
				),
				$table_prefix . "privmsgs" => array(
					 "privmsgs_id" => "msg_id",
					 "privmsgs_from_userid" => "from_userid",
					 "privmsgs_to_userid" => "to_userid",
					 "privmsgs_date" => "msg_time",
					 "privmsgs_ip" => "poster_ip",
					 "privmsgs_type" => "msg_status" 
				),
				$table_prefix . "smilies" => array(
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
				print " * Table :: $table <br />\n";
				flush();
				
				$sql = "SHOW FIELDS 
					FROM $table";
				$result = query($sql, "Can't get definition of current $table table");

				$current_def = $db->sql_fetchrowset($result);

				while(list($nr, $def) = each($current_def))
				{
					$current_fields[] = $def['Field'];
				}
				
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

					if( !inarray($field, $current_fields) && $oldfield == $field )
					{
						// If the current is not a key of $current_def and it is not a field that is 
						// to be renamed then the field doesn't currently exist.
						$changes[] = " ADD $field " . $create_def[$table][$field];
					}
					else
					{
						$changes[] = " CHANGE $oldfield $field " . $create_def[$table][$field];
					}
				}
				
				$alter_sql .= join(',', $changes);
				unset($changes);
				unset($current_fields);
				
				$sql = "SHOW INDEX 
					FROM $table";
				$result = query($sql, "Couldn't get list of indices for table $table");

				unset($indices);

				while( $row = $db->sql_fetchrow($result) )
				{
					$indices[] = $row['Key_name'];
				}
				
				while (list($key_name, $key_field) = each($key_def[$table]) )
				{
					if(!inarray($key_name, $indices))
					{
						$alter_sql .= ($key_name == 'PRIMARY') ? ", ADD PRIMARY KEY ($key_field)" : ", ADD INDEX $key_name ($key_field)";
					}
				}
				query($alter_sql, "Couldn't alter table $table");
				flush();
			}

			echo "<br />Complete<br />\n";
			end_step('convert_forums');

		case 'convert_forums':
			print "Converting forums ... <br />\n";

			$sql = "SELECT * 
				FROM " . FORUMS_TABLE;
			$result = query($sql, "Couldn't get list with all forums");

			while( $row = $db->sql_fetchrow($result) )
			{
				print " * Forum :: '" . $row['forum_name'] . "'<br />\n";
				// Old auth structure:
				// forum_type: (only concerns viewing)
				//		0 = Public
				//		1 = Private
				switch($row['forum_type'])
				{
					case 0:
						$auth_view			= AUTH_ALL;
						$auth_read			= AUTH_ALL;
						break;
					default:
						$auth_view			= AUTH_ALL;
						$auth_read			= AUTH_ALL;
						break;
				}
			
				// forum_access: (only concerns posting)
				//		1 = Registered users only
				//		2 = Anonymous Posting
				//		3 = Moderators/Administrators only
				switch($row['forum_access'])
				{
					case 1:
						// Public forum, no anonymous posting
						$auth_post			= AUTH_REG;
						$auth_reply			= AUTH_REG;
						$auth_edit			= AUTH_REG;
						$auth_delete		= AUTH_REG;
						$auth_vote			= AUTH_REG;
						$auth_pollcreate	= AUTH_REG;
						$auth_sticky		= AUTH_MOD;
						$auth_announce		= AUTH_MOD;
						break;
					case 2:
						$auth_post			= AUTH_ALL;
						$auth_reply			= AUTH_ALL;
						$auth_edit			= AUTH_ALL;
						$auth_delete		= AUTH_ALL;
						$auth_vote			= AUTH_ALL;
						$auth_pollcreate	= AUTH_ALL;
						$auth_sticky		= AUTH_MOD;
						$auth_announce		= AUTH_MOD;
						break;
					default:
						$auth_post			= AUTH_MOD;
						$auth_reply			= AUTH_MOD;
						$auth_edit			= AUTH_MOD;
						$auth_delete		= AUTH_MOD;
						$auth_vote			= AUTH_MOD;
						$auth_pollcreate	= AUTH_MOD;
						$auth_sticky		= AUTH_MOD;
						$auth_announce		= AUTH_MOD;
						break;
				}
				
				$sql = "UPDATE " . FORUMS_TABLE . " SET
					auth_view = $auth_view,
					auth_read = $auth_read,
					auth_post = $auth_post,
					auth_reply = $auth_reply,
					auth_edit = $auth_edit,
					auth_delete = $auth_delete,
					auth_vote = $auth_vote,
					auth_pollcreate = $auth_pollcreate,
					auth_sticky = $auth_sticky,
					auth_announce = $auth_announce
					WHERE forum_id = ". $row['forum_id'];
				query($sql, "Was unable to update forum permissions!");
			}

			echo "<br />Complete<br />\n";
			end_step('insert_themes');

		case 'insert_themes':
			print "Inserting new values into new themes table ... ";

			@reset($inserts);
			while( list($table, $inserts_table) = each($inserts) )
			{
				if( $table == THEMES_TABLE )
				{
					while( list($nr, $insert) = each($inserts_table) )
					{
						query($insert, "Couldn't insert value into " . THEMES_TABLE);

						print ".";
						flush();
					}
				}
			}

			echo "<br />Complete<br />\n";
			end_step('gen_searchlist');
			//end_step('convert_config');

		case 'gen_searchlist':
			common_header();
			print "Running search word list generation. This may take some time, do <b>not</b> stop it while it's running! ... ";

			//
			// Generate search word list
			//
			// Fetch a batch of posts_text entries
			//
			$sql = "SELECT COUNT(*) as total, MAX(post_id) as max_post_id 
				FROM " . POSTS_TEXT_TABLE;
			$result = query($sql, "Couldn't get post count totals");

			$max_post_id = $db->sql_fetchrow($result);

			$totalposts = $max_post_id['total'];
			$max_post_id = $max_post_id['max_post_id'];

			$postcounter = ( !isset($HTTP_GET_VARS['batchstart']) ) ? 0 : $HTTP_GET_VARS['batchstart'];

			$batchsize = 200; // Process this many posts per loop
			$batchcount = 0;

			for(;$postcounter <= $max_post_id; $postcounter += $batchsize)
			{
				$batchstart = $postcounter + 1;
				$batchend = $postcounter + $batchsize;
				$batchcount++;
				
				$sql = "SELECT *
					FROM " . POSTS_TEXT_TABLE ."
					WHERE post_id 
						BETWEEN $batchstart 
							AND $batchend";
				$posts_result = query($sql, "Couldn't obtain post_text");

				if( $post_rows = $db->sql_numrows($posts_result) )
				{
					$rowset = $db->sql_fetchrowset($posts_result);

					print "\n<p>\n<a href='upgrade.$phpEx?next=gen_searchlist&amp;batchstart=$batchstart'>Restart from posting $batchstart</a><br>\n";

					// For every post in the batch:
					for($post_nr = 0; $post_nr < $post_rows; $post_nr++ )
					{ 
						print ".";
						flush();

						$matches = array();

						$post_id = $rowset[$post_nr]['post_id']; 
						$data = $rowset[$post_nr]['post_text'];  // Raw data

						$text = clean_words($data, $search, $replace); // Cleaned up post
						$matches = split_words($text);

						$num_matches = count($matches);

						if( $num_matches < 1 )
						{
							// Skip this post if no words where found
							continue;
						}

						$word = array();
						$word_count = array();
						$sql_in = "";

						// For all words in the posting
						$sql_insert = '';
						$sql_select = '';

						for($j = 0; $j < $num_matches; $j++)
						{
							$this_word = strtolower(trim($matches[$j]));
							if($this_word != '')
							{
								$word_count[$this_word]++;
								$comma = ($sql_insert != '')? ', ': '';
							
								$sql_insert .= "$comma('" . $this_word . "')";
								$sql_select .= "$comma'" . $this_word . "'";
							}
						}

						if( $sql_insert == '' )
						{
							die("no words found");
						}
							
						$sql = 'INSERT IGNORE INTO ' . SEARCH_WORD_TABLE . ' (word_text)
							VALUES ' . $sql_insert;
						$result = query($sql, "Failed inserting new word into search word table :: " . $sql_insert);

						// Get the word_id's out of the DB (to see if they are already there)
						$sql = "SELECT word_id, word_text
							FROM ".SEARCH_WORD_TABLE." 
							WHERE word_text IN ($sql_select)
							GROUP BY word_text";
						$result = query($sql, "Couldn't select words from search word table");

						if( $word_check_count = $db->sql_numrows($result) )
						{
							$selected_words = $db->sql_fetchrowset($result);
						}
						else
						{
							print "Couldn't do sql_numrows<br>\n";
						}

						$db->sql_freeresult($result);
						
						$sql_insert = '';
						while( list($junk, $row) = each($selected_words) )
						{
							$comma = ( $sql_insert != '' ) ? ', ': '';
							$sql_insert .= "$comma($post_id, " . $row['word_id'] . ", 0)";
						}
						
						$sql = "INSERT INTO " . SEARCH_MATCH_TABLE . " (post_id, word_id, title_match)
							VALUES $sql_insert";
						query($sql, "Couldn't insert new word match into search_wordmatch table :: " . $post_id); 

					} // All posts
				}

				$db->sql_freeresult($posts_result);
				
				// Remove common words after the first 2 batches and after every 4th batch after that.
				if( $batchcount % 4 == 3 )
				{
					print "<br>Removing common words (words that appear in more than $common_percent of the posts)<br>\n";
					flush();
					print "Removed " . remove_common($common_percent, 1) . " words that where too common.<br>";
				}
				
			}

			echo "<br />Complete<br />\n";
			common_footer();
			end_step('final_config');

		case 'final_config':
			common_header();

			print "Updating forum post information ... <br />\n";
			//
			// Update forum last post information
			//
			$sql = "SELECT forum_id, forum_name 
				FROM " . FORUMS_TABLE;
			$f_result = query($sql, "Couldn't obtain forum_ids");

			while( $forum_row = $db->sql_fetchrow($f_result) )
			{
				print " * Forum :: " . $forum_row['forum_name'] . " ... ";
				flush();

				$id = $forum_row['forum_id'];

				$sql = "SELECT MAX(p.post_id) AS last_post
					FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t
					WHERE p.forum_id = $id
						AND p.topic_id = t.topic_id";
				$result = query($sql, "Could not get post ID forum post information :: $id");

				if( $row = $db->sql_fetchrow($result) )
				{
					$last_post = ($row['last_post']) ? $row['last_post'] : 0;
				}
				else
				{
					$last_post = 0;
				}

				$sql = "SELECT COUNT(post_id) AS total
					FROM " . POSTS_TABLE . "
					WHERE forum_id = $id";
				$result = query($sql, "Could not get post count forum post information :: $id");

				if( $row = $db->sql_fetchrow($result) )
				{
					$total_posts = ($row['total']) ? $row['total'] : 0;
				}
				else
				{
					$total_posts = 0;
				}

				$sql = "SELECT COUNT(topic_id) AS total
					FROM " . TOPICS_TABLE . "
					WHERE forum_id = $id 
						AND topic_status <> " . TOPIC_MOVED;
				$result = query($sql, "Could not get topic count forum post information :: $id");

				if( $row = $db->sql_fetchrow($result) )
				{
					$total_topics = ($row['total']) ? $row['total'] : 0;
				}
				else
				{
					$total_topics = 0;
				}

				$sql = "UPDATE " . FORUMS_TABLE . "
					SET forum_last_post_id = $last_post, forum_posts = $total_posts, forum_topics = $total_topics
					WHERE forum_id = $id";
				query($sql, "Could not update forum post information :: $id");

				print "Done<br />\n";
			}

			print "<br />\nFinal Configuration ... ";
			flush();

			//
			// Update the default admin user with their information.
			//
			$sql = "SELECT MIN(user_regdate) AS oldest_time 
				FROM " . USERS_TABLE . " 
				WHERE user_regdate > 0";
			$result = query($sql, "Couldn't obtain oldest post time");

			$row = $db->sql_fetchrow($result);

			$sql = "INSERT INTO " . $table_prefix . "config (config_name, config_value) 
				VALUES ('board_startdate', " . $row['oldest_time']  . ")";
			query($sql, "Couldn't insert board_startdate");

			//
			// Change session table to HEAP if MySQL version matches
			//
			$sql = "SELECT VERSION() AS mysql_version";
			$result = query($sql, "Couldn't obtain MySQL Version");

			$row = $db->sql_fetchrow($result);

			$version = $row['mysql_version'];

			if( preg_match("/^(3\.23|4\.)/", $version) )
			{
				$sql = "ALTER TABLE " . $table_prefix . "sessions 
					TYPE=HEAP";
				query($sql, "Couldn't alter sessions table type to HEAP");
			}

			echo "Done<br />\n";
			common_footer();
			end_step('common_footer');

		case 'dropfields':
			common_header();
			print "Dropping unused fields ... <br />\n";

			$fields = array(
				BANLIST_TABLE => array("ban_start", "ban_end", "ban_time_type"),
				FORUMS_TABLE => array("forum_access", "forum_moderator", "forum_type", "forum_pass"), 
				PRIVMSGS_TABLE => array("msg_text"), 
				RANKS_TABLE => array("rank_max"), 
				SMILIES_TABLE => array("emotion"), 
				USERS_TABLE => array("user_hint")
			);

			while( list($table, $field_data) = each($fields) )
			{
				print " * Table :: $table<br />\n";

				for($i = 0; $i < count($field_data); $i++)
				{
					print "&nbsp;&nbsp;&nbsp; # Field :: " . $field_data[$i] . " ... ";
					flush();

					$sql = "ALTER TABLE $table 
						DROP COLUMN " . $field_data[$i];
					query($sql, "Couldn't drop field :: " . $field_data[$i] . " from table :: $table");

					print "Done<br />\n";

				}
			}

			print "\n<br />Completed<br />\n";
			common_footer();
			end_step('droptables');

		case 'droptables':
			common_header();
			print "Dropping unused tables ... <br />\n";

			$drop_tables = array('access', 'forum_access', 'forum_mods', 'headermetafooter', 'sessions', 'themes', 'whosonline', $table_prefix . 'old_config');

			for($i = 0; $i < count($drop_tables); $i++)
			{
				print " * Table :: " . $drop_tables[$i] . " ... ";
				flush();

				$sql = "DROP TABLE " . $drop_tables[$i];
				query($sql, "Couldn't drop table :: " . $drop_tables[$i]);

				print "Done<br />\n";
			}

			echo "\n<br />Completed<br /><br />\n\n<font size=\"+1\"><b>UPGRADE COMPLETED</b></font><br />\n";
	}
}

print "<br />If the upgrade completed without error you may click <a href=\"index.$phpEx\">Here</a> to proceed to the index<br />";

?>