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

define('IN_PHPBB', true);

$phpbb_root_path = './../';

if ( !defined('INSTALLING') )
{
	error_reporting  (E_ERROR | E_WARNING | E_PARSE); // This will NOT report uninitialized variables
	set_magic_quotes_runtime(0); // Disable magic_quotes_runtime

	//
	// If we are being called from the install script then we don't need these
	// as they are already included.
	//
	include($phpbb_root_path . 'extension.inc');
	include($phpbb_root_path . 'config.'.$phpEx);
	include($phpbb_root_path . 'includes/constants.'.$phpEx);
	include($phpbb_root_path . 'includes/functions.'.$phpEx);

	if( defined("PHPBB_INSTALLED") )
	{
		redirect("../index.$phpEx");
	}
}

//
// Force the DB type to be MySQL
//
$dbms = 'mysql';

include($phpbb_root_path . 'includes/db.'.$phpEx);
include($phpbb_root_path . 'includes/bbcode.'.$phpEx);
include($phpbb_root_path . 'includes/functions_search.'.$phpEx);

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
function common_header()
{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<meta http-equiv="Content-Style-Type" content="text/css">
<style type="text/css">
<!--
/* Specifiy background images for selected styles
   This can't be done within the external style sheet as NS4 sees image paths relative to
   the page which called the style sheet (i.e. this page in the root phpBB directory)
   whereas all other browsers see image paths relative to the style sheet. Stupid NS again!
*/
th			{ background-image: url('../templates/subSilver/images/cellpic3.gif') }
td.cat		{ background-image: url('../templates/subSilver/images/cellpic1.gif') }
td.rowpic	{ background-image: url('../templates/subSilver/images/cellpic2.jpg'); background-repeat: repeat-y }
td.catHead,td.catSides,td.catLeft,td.catRight,td.catBottom { background-image: url('../templates/subSilver/images/cellpic1.gif') }

font,th,td,p,body { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 11pt }
a:link,a:active,a:visited { font-family: Verdana, Arial, Helvetica, sans-serif; color : #006699; font-size:11pt }
a:hover		{ font-family: Verdana, Arial, Helvetica, sans-serif;  text-decoration: underline; color : #DD6900; font-size:11pt }
hr	{ height: 0px; border: solid #D1D7DC 0px; border-top-width: 1px;}

.maintitle,h1,h2	{font-weight: bold; font-size: 22px; font-family: "Trebuchet MS",Verdana, Arial, Helvetica, sans-serif; text-decoration: none; line-height : 120%; color : #000000;}

.ok {color:green}

/* Import the fancy styles for IE only (NS4.x doesn't use the @import function) */
@import url("../templates/subSilver/formIE.css"); 
-->
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000" link="#006699" vlink="#5584AA">

<table width="100%" border="0" cellspacing="0" cellpadding="10" align="center"> 
	<tr>
		<td><table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td><img src="../templates/subSilver/images/logo_phpBB.gif" border="0" alt="Forum Home" vspace="1" /></td>
				<td align="center" width="100%" valign="middle"><span class="maintitle">Upgrading to phpBB 2.0</span></td>
			</tr>
		</table></td>
	</tr>
</table>

<br clear="all" />

<?
	return;
}

function common_footer()
{
?>

<br clear="all" />

</body>
</html>
<?
	return;
}

function query($sql, $errormsg)
{
	global $db;

	if ( !($result = $db->sql_query($sql)) )
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

function smiley_replace($text = '')
{
	global $db;

	static $search, $replace;

	// Did we get the smiley info in a previous call?
	if ( !is_array($search) )
	{
		$sql = "SELECT code, smile_url
			FROM smiles";
		$result = query($sql, "Unable to get list of smilies from the DB");

		$smilies = $db->sql_fetchrowset($result);
		@usort($smilies, 'smiley_sort');

		$search = array();
		$replace = array();
		for($i = 0; $i < count($smilies); $i++)
		{
			$search[] = '/<IMG SRC=".*?\/' . phpbb_preg_quote($smilies[$i]['smile_url'], '/') .'">/i';
			$replace[] = $smilies[$i]['code'];
		}
	}

	return ( $text != '' ) ? preg_replace($search, $replace, $text) : '';
	
}

function get_schema()
{
	global $table_prefix;

	$schemafile = file('schemas/mysql_schema.sql');
	$tabledata = 0;

	for($i=0; $i < count($schemafile); $i++)
	{
		$line = $schemafile[$i];

		if ( preg_match('/^CREATE TABLE (\w+)/i', $line, $matches) )
		{
			// Start of a new table definition, set some variables and go to the next line.
			$tabledata = 1;
			// Replace the 'phpbb_' prefix by the user defined prefix.
			$table = str_replace('phpbb_', $table_prefix, $matches[1]);
			$table_def[$table] = "CREATE TABLE $table (\n";
			continue;
		}

		if ( preg_match('/^\);/', $line) )
		{
			// End of the table definition
			// After this we will skip everything until the next 'CREATE' line
			$tabledata = 0;
			$table_def[$table] .= ')'; // We don't need the closing semicolon
		}

		if ( $tabledata == 1 )
		{
			// We are inside a table definition, parse this line.
			// Add the current line to the complete table definition:
			$table_def[$table] .= $line;
			if ( preg_match('/^\s*(\w+)\s+(\w+)\(([\d,]+)\)(.*)$/', $line, $matches) )
			{
				// This is a column definition
				$field = $matches[1];
				$type = $matches[2];
				$size = $matches[3];

				preg_match('/DEFAULT (NULL|\'.*?\')[,\s](.*)$/i', $matches[4], $match);
				$default = $match[1];

				$notnull = ( preg_match('/NOT NULL/i', $matches[4]) ) ? 1 : 0;
				$auto_increment = ( preg_match('/auto_increment/i', $matches[4]) ) ? 1 : 0;

				$field_def[$table][$field] = array(
					'type' => $type,
					'size' => $size,
					'default' => $default,
					'notnull' => $notnull,
					'auto_increment' => $auto_increment
				);
			}
			
			if ( preg_match('/\s*PRIMARY\s+KEY\s*\((.*)\).*/', $line, $matches) )
			{
				// Primary key
				$key_def[$table]['PRIMARY'] = $matches[1];
			}
			else if ( preg_match('/\s*KEY\s+(\w+)\s*\((.*)\)/', $line, $matches) )
			{
				// Normal key
				$key_def[$table][$matches[1]] = $matches[2];
			}
			else if ( preg_match('/^\s*(\w+)\s*(.*?),?\s*$/', $line, $matches) )
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

	$schema['field_def'] = $field_def;
	$schema['table_def'] = $table_def;
	$schema['create_def'] = $create_def;
	$schema['key_def'] = $key_def;

	return $schema;
}

function get_inserts()
{
	global $table_prefix;

	$insertfile = file('schemas/mysql_basic.sql');

	for($i = 0; $i < count($insertfile); $i++)
	{
		if ( preg_match('/(INSERT INTO (\w+)\s.*);/i', str_replace('phpbb_', $table_prefix, $insertfile[$i]), $matches) )
		{
			$returnvalue[$matches[2]][] = $matches[1];
		}
	}

	return $returnvalue;
}

function lock_tables($state, $tables= '')
{
	if ( $state == 1 )
	{
		if ( is_array($tables) )
		{
			$tables = join(' WRITE, ', $tables);
		}

		query("LOCK TABLES $tables WRITE", "Couldn't do: $sql");
	}
	else
	{
		query("UNLOCK TABLES", "Couldn't unlock all tables");
	}
}

function output_table_content($content)
{
	echo $content . "\n";

	return;
}

//
// Nathan's bbcode2 conversion routines
//
function bbdecode($message)
{
	// Undo [code]
	$code_start_html = '<!-- BBCode Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD><font size=-1>Code:</font><HR></TD></TR><TR><TD><FONT SIZE=-1><PRE>';
	$code_end_html = '</PRE></FONT></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode End -->';
	$message = str_replace($code_start_html, '[code]', $message);
	$message = str_replace($code_end_html, '[/code]', $message);

	// Undo [quote]
	$quote_start_html = '<!-- BBCode Quote Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD><font size=-1>Quote:</font><HR></TD></TR><TR><TD><FONT SIZE=-1><BLOCKQUOTE>';
	$quote_end_html = '</BLOCKQUOTE></FONT></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode Quote End -->';
	$message = str_replace($quote_start_html, '[quote]', $message);
	$message = str_replace($quote_end_html, '[/quote]', $message);

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
	$message = str_replace('<!-- BBCode --><LI>', '[*]', $message);

	// [list] tags:
	$message = str_replace('<!-- BBCode ulist Start --><UL>', '[list]', $message);

	// [list=x] tags:
	$message = preg_replace('#<!-- BBCode olist Start --><OL TYPE=([A1])>#si', "[list=\\1]", $message);

	// [/list] tags:
	$message = str_replace('</UL><!-- BBCode ulist End -->', '[/list]', $message);
	$message = str_replace('</OL><!-- BBCode olist End -->', '[/list]', $message);

	return $message;
}

//
// Alternative for in_array() which is only available in PHP4
//
function inarray($needle, $haystack)
{ 
	for( $i = 0 ; $i < sizeof($haystack) ; $i++ )
	{ 
		if ( $haystack[$i] == $needle )
		{ 
			return true; 
		} 
	} 

	return false; 
}

function end_step($next)
{
	print "<hr /><a href=\"$PHP_SELF?next=$next\">Next step: <b>$next</b></a><br /><br />\n";
	exit;
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

// Parse the MySQL schema file into some arrays.
$schema = get_schema();

$table_def = $schema['table_def'];
$field_def = $schema['field_def'];
$key_def = $schema['key_def'];
$create_def = $schema['create_def'];

//
// Get mysql_basic data
//
$inserts = get_inserts();

//
// Get smiley data
//
smiley_replace();

common_header();

if ( !empty($next) )
{
	switch($next)
	{
		case 'start':
			end_step('initial_drops');

		case 'initial_drops':
			print " * Dropping sessions and themes tables :: ";
			flush();

			query("DROP TABLE sessions", "Couldn't drop table 'sessions'");
			query("DROP TABLE themes", "Couldn't drop table 'themes'");   

			print "<span class=\"ok\"><b>OK</b></span><br />\n";

			end_step('mod_old_tables');

		case 'mod_old_tables':
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
					if ( $index != 'PRIMARY' )
					{
						query("ALTER TABLE $old DROP INDEX $index", "Couldn't DROP INDEX $old.$index");
					}
				}

				// Rename table
				$new = $table_prefix . $new;

				print " * Renaming '$old' to '$new' :: ";
				flush();
				query("ALTER TABLE $old RENAME $new", "Failed to rename $old to $new");
				print "<span class=\"ok\"><b>OK</b></span><br />\n";
				
			}
			end_step('create_tables');
			
		case 'create_tables':
			// Create array with tables in 'old' database
			$result = query('SHOW TABLES', "Couldn't get list of current tables");

			while( $table = $db->sql_fetchrow($result) )
			{
				$currenttables[] = $table[0];
			}
			
			// Check what tables we need to CREATE
			while( list($table, $definition) = each($table_def) )
			{
				if ( !inarray($table, $currenttables) )
				{
					print " * Creating $table :: ";

					query($definition, "Couldn't create table $table");

					print "<span class=\"ok\"><b>OK</b></span><br />\n";
				}
			}
			
			end_step('create_config');
			
		case 'create_config':
			print " * Inserting new values into new layout config table :: ";

			@reset($inserts);
			while( list($table, $inserts_table) = each($inserts) )
			{
				if ( $table == CONFIG_TABLE )
				{
					$per_pct = ceil( count($inserts_table) / 40 );
					$inc = 0;

					while( list($nr, $insert) = each($inserts_table) )
					{
						query($insert, "Couldn't insert value into config table");

						$inc++;
						if ( $inc == $per_pct )
						{
							print ".";
							flush();
							$inc = 0;
						}
					}
				}
			}

			print " <span class=\"ok\"><b>OK</b></span><br />\n";

			end_step('convert_config');
			
		case 'convert_config':
			print " * Converting configuration table :: ";

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
				if ( is_int($name) )
				{
					continue;
				}

				if ( !inarray($name, $ignore_configs) )
				{
					$name = ( !empty($rename_configs[$name]) ) ? $rename_configs[$name] : $name;
					
					// phpBB 1.x has some problems with escaping strings in the database. Try to correct for
					// this by removing all slashes and then escaping once.
					$sql = "REPLACE INTO " . CONFIG_TABLE . " (config_name, config_value) 
						VALUES ('$name', '".addslashes(stripslashes(stripslashes($value)))."')";
					query($sql, "Couldn't update config table with values from old config table");
				}
			}
			
			$sql = "UPDATE " . CONFIG_TABLE . " 
				SET config_value = 'dutch' 
				WHERE config_name = 'default_lang' && config_value = 'nederlands'";
			query($sql, "Couldn't rename 'nederlands' to 'dutch' in config table");
			
			print "<span class=\"ok\"><b>OK</b></span><br />\n";
			end_step('convert_ips');

		case 'convert_ips':
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

			$batchsize = 2000;
			while( list($table, $data_array) = each($names) )
			{
				$sql = "SELECT MAX(" . $data_array['id'] . ") AS max_id 
					FROM $table";
				$result = query($sql, "Couldn't obtain ip data from $table (" . $fields . ")");

				$row = $db->sql_fetchrow($result);

				$maxid = $row['max_id'];

				for($i = 0; $i <= $maxid; $i += $batchsize)
				{
					$batchstart = $i;
					$batchend = $i + $batchsize;

					$field_id = $data_array['id'];
					$field = $data_array['field'];

					print " * Converting IP format '" . $field . "' / '$table' ( $batchstart to $batchend ) :: ";
					flush();

					$sql = "SELECT $field_id, $field 
						FROM $table 
						WHERE $field_id 
							BETWEEN $batchstart 
								AND $batchend";
					$result = query($sql, "Couldn't obtain ip data from $table (" . $fields . ")");

					$per_pct = ceil( $db->sql_numrows($result) / 40 );
					$inc = 0;

					while( $row = $db->sql_fetchrow($result) )
					{
						$sql = "UPDATE $table 
							SET $field = '" . encode_ip($row[$field]) . "' 
							WHERE $field_id = " . $row[$field_id];
						query($sql, "Couldn't convert IP format of $field in $table with $field_id of " . $rowset[$field_id]);

						$inc++;
						if ( $inc == $per_pct )
						{
							print ".";
							flush();
							$inc = 0;
						}
					}

					print " <span class=\"ok\"><b>OK</b></span><br />\n";
				}
			}

			lock_tables(0);
			end_step('convert_dates');

		case 'convert_dates':
			$names = array(
				POSTS_TABLE => array('post_time'),
				TOPICS_TABLE => array('topic_time'), 
				PRIVMSGS_TABLE => array('msg_time')
			);

			lock_tables(1, array(POSTS_TABLE, TOPICS_TABLE, PRIVMSGS_TABLE));

			while( list($table, $fields) = each($names) )
			{
				print " * Converting date format of $fields[$i] in $table :: ";
				flush();

				for($i = 0; $i < count($fields); $i++)
				{
					$sql = "UPDATE $table 
						SET " . $fields[$i] . " = UNIX_TIMESTAMP(" . $fields[$i] . ")";
					query($sql, "Couldn't convert date format of $table(" . $fields[$i] . ")");
				}

				print "<span class=\"ok\"><b>OK</b></span><br />\n";
			}

			lock_tables(0);
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
				print " * Removing slashes from $table table :: ";
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

				print "<span class=\"ok\"><b>OK</b></span><br />\n";
			}

			lock_tables(0);
			end_step('remove_topics');

		case 'remove_topics':
			print " * Removing posts with no corresponding topics :: ";
			flush();

			$sql = "SELECT p.post_id 
				FROM " . POSTS_TABLE . " p 
				LEFT JOIN " . TOPICS_TABLE . " t ON p.topic_id = t.topic_id  
				WHERE t.topic_id IS NULL";
			$result = query($sql, "Couldn't obtain list of deleted topics");
			
			$post_total = $db->sql_numrows($result);

			if ( $post_total )
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

			echo "<span class=\"ok\"><b>OK</b></span> ( Removed $post_total posts )<br />\n";
			end_step('convert_users');

		case 'convert_users':
			//
			// Completely remove old soft-deleted users
			//
			$sql = "DELETE FROM " . USERS_TABLE . " 
				WHERE user_level = -1";
			query($sql, "Couldn't delete old soft-deleted users");

			$sql = "SELECT COUNT(*) AS total, MAX(user_id) AS maxid 
				FROM " . USERS_TABLE;
			$result = query($sql, "Couldn't get max user_id.");

			$row = $db->sql_fetchrow($result);

			$totalposts = $row['total'];
			$maxid = $row['maxid'];

			$sql = "ALTER TABLE " . USERS_TABLE . " 
				ADD user_sig_bbcode_uid CHAR(10),
				MODIFY user_sig text";
			query($sql, "Couldn't add user_sig_bbcode_uid field to users table");

			$super_mods = array();
			$first_admin = -2;

			$batchsize = 1000;
			for($i = -1; $i <= $maxid; $i += $batchsize)
			{
				$batchstart = $i;
				$batchend = $i + $batchsize;
				
				print " * Converting Users ( $batchstart to $batchend ) :: ";
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

				$per_pct = ceil( $db->sql_numrows($result) / 40 );
				$inc = 0;

				while( $row = $db->sql_fetchrow($result) )
				{
					$sql = "INSERT INTO " . GROUPS_TABLE . " (group_name, group_description, group_single_user) 
						VALUES ('" . addslashes($row['username']) . "', 'Personal User', 1)";
					query($sql, "Wasn't able to insert user ".$row['user_id']." into table ".GROUPS_TABLE);

					$group_id = $db->sql_nextid();

					$sql = "INSERT INTO " . USER_GROUP_TABLE . " (group_id, user_id, user_pending)	
						VALUES ($group_id, " . $row['user_id'] . ", 0)";
					query($sql, "Wasn't able to insert user ".$row['user_id']." into table ".USER_GROUP_TABLE);

					if ( is_int($row['user_regdate']) )
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
					if ( substr(strtolower($website), 0, 7) != "http://" )
					{
						$website = "http://" . $website;
					}
					if( strtolower($website) == 'http://' )
					{
						$website = '';
					}
					$row['user_website'] = addslashes($website);
					
					$row['user_icq'] = (ereg("^[0-9]+$", $row['user_icq'])) ? $row['user_icq'] : '';
					@reset($checklength);

					while($field = each($checklength))
					{
						if ( strlen($row[$field[1]]) < 3 )
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

					//
					// Dutch language files have been renamed from 'nederlands' to 'dutch'
					//
					if( $row['user_lang'] == 'nederlands' )
					{
						$row['user_lang'] = 'dutch';
					}

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
							user_desmile = NOT(user_desmile), 
							user_bbcode = 1, 
							user_theme = 1 
						WHERE user_id = " . $row['user_id'];
					query($sql, "Couldn't update ".USERS_TABLE." table with new BBcode and regdate for user_id ".$row['user_id']);

					$inc++;
					if ( $inc == $per_pct )
					{
						print ".";
						flush();
						$inc = 0;
					}
				}

				// Set any non-standard (like) email addresses to nothing
				// could do this above as a preg_ but this one query may
				// be faster
				$sql = "UPDATE " . USERS_TABLE . " 
					SET user_email = '' 
					WHERE user_email NOT REGEXP '^[a-zA-Z0-9_\+\.\-]+@.*[a-zA-Z0-9\-_]+\.[a-zA-Z]{2,}$'";
				query($sql, "Couldn't update ".USERS_TABLE." table non-standard user_email entries");

				print " <span class=\"ok\"><b>OK</b></span><br />\n";

				lock_tables(0);
			}

			//
			// Handle super-mods, create hidden group for them
			//
			// Iterate trough access table
			if( count($super_mods) && $first_admin != -2 )
			{
				print "\n<br />\n * Creating new group for super moderators :: ";
				flush();

				$sql = "INSERT INTO " . GROUPS_TABLE . " (group_type, group_name, group_description, group_moderator, group_single_user)
					VALUES (" . GROUP_HIDDEN . ", 'Super Moderators', 'Converted super moderators', $first_admin, 0)";
				$result = query($sql, "Couldn't create group for ".$row['forum_name']);

				$group_id = $db->sql_nextid();

				if ( $group_id <= 0 )
				{
					print "<font color=\"red\">Group creation failed. Aborting creation of groups...<br></font>\n";
					continue 2;
				}

				print "<span class=\"ok\"><b>OK</b></span><br />\n";

				print " * Updating auth_access for super moderator group :: ";
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
			
				print "<span class=\"ok\"><b>OK</b></span><br />\n";
			}

			end_step('convert_posts');

		case 'convert_posts':
			print " * Adding enable_sig field to " . POSTS_TABLE . " :: ";
			flush();
			$sql = "ALTER TABLE " . POSTS_TABLE . " 
				ADD enable_sig tinyint(1) DEFAULT '1' NOT NULL";
			$result = query($sql, "Couldn't add enable_sig field to " . POSTS_TABLE . ".");
			print "<span class=\"ok\"><b>OK</b></span><br />\n";
			
			print " * Adding enable_bbcode field to " . POSTS_TEXT_TABLE . " :: ";
			flush();
			$sql = "ALTER TABLE " . POSTS_TEXT_TABLE . "  
				ADD enable_bbcode tinyint(1) DEFAULT '1' NOT NULL";
			$result = query($sql, "Couldn't add enable_bbcode field to " . POSTS_TABLE . ".");
			print "<span class=\"ok\"><b>OK</b></span><br />\n";

			print " * Adding bbcode_uid field to " . POSTS_TEXT_TABLE . " :: ";
			flush();
			$sql = "ALTER TABLE " . POSTS_TEXT_TABLE . "  
				ADD bbcode_uid char(10) NOT NULL";
			$result = query($sql, "Couldn't add bbcode_uid field to " . POSTS_TABLE . ".");
			print "<span class=\"ok\"><b>OK</b></span><br />\n";
			
			print " * Adding post_edit_time field to " . POSTS_TABLE . " :: ";
			flush();
			$sql = "ALTER TABLE " . POSTS_TABLE . "  
				ADD post_edit_time int(11)";
			$result = query($sql, "Couldn't add post_edit_time field to " . POSTS_TABLE . ".");
			print "<span class=\"ok\"><b>OK</b></span><br />\n";

			print " * Adding post_edit_count field to " . POSTS_TABLE . " :: ";
			flush();
			$sql = "ALTER TABLE " . POSTS_TABLE . "  
				ADD post_edit_count smallint(5) UNSIGNED DEFAULT '0' NOT NULL";
			$result = query($sql, "Couldn't add post_edit_count field to " . POSTS_TABLE . ".");
			print "<span class=\"ok\"><b>OK</b></span><br />\n<br />\n";

			$sql = "SELECT COUNT(*) as total, MAX(post_id) as maxid 
				FROM " . POSTS_TEXT_TABLE;
			$result = query($sql, "Couldn't get max post_id.");

			$maxid = $db->sql_fetchrow($result);

			$totalposts = $maxid['total'];
			$maxid = $maxid['maxid'];

			$batchsize = 2000;
			for($i = 0; $i <= $maxid; $i += $batchsize)
			{
				$batchstart = $i + 1;
				$batchend = $i + $batchsize;
				
				print " * Converting BBcode ( $batchstart to $batchend ) :: ";
				flush();

				$sql = "SELECT * 
					FROM " . POSTS_TEXT_TABLE . "
					WHERE post_id 
						BETWEEN $batchstart 
							AND $batchend";
				$result = query($sql, "Couldn't get ". POSTS_TEXT_TABLE .".post_id $batchstart to $batchend");

				lock_tables(1, array(POSTS_TEXT_TABLE, POSTS_TABLE));

				$per_pct = ceil( $db->sql_numrows($result) / 40 );
				$inc = 0;

				while( $row = $db->sql_fetchrow($result) )
				{
					if ( $row['bbcode_uid'] != '' )
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
					$row['post_text'] = str_replace('<BR>', "\n", $row['post_text']);

					// make a uid
					$uid = make_bbcode_uid();

					// do 2.x first-pass encoding..
					$row['post_text'] = smiley_replace($row['post_text']);
					$row['post_text'] = bbencode_first_pass($row['post_text'], $uid);
					$row['post_text'] = addslashes($row['post_text']);

					$edited_sql = "";
					if ( preg_match('/^(.*?)([\n]+<font size=\-1>\[ This message was .*?)$/s', $row['post_text'], $matches) )
					{
						$row['post_text'] = $matches[1];
						$edit_info = $matches[2];

						$edit_times = count(explode(' message ', $edit_info)) - 1; // Taken from example for substr_count in annotated PHP manual

						if ( preg_match('/^.* by: (.*?) on (....)-(..)-(..) (..):(..) \]<\/font>/s', $edit_info, $matches) )
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
	
					if ( preg_match("/^(.*?)\n-----------------\n.*$/is", $row['post_text'], $matches) )
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

					$inc++;
					if ( $inc == $per_pct )
					{
						print '.';
						flush();
						$inc = 0;
					}
				}

				print " <span class=\"ok\"><b>OK</b></span><br />\n";

				lock_tables(0);
			}

			print "<br />\n * Updating poster_id for deleted users :: ";
			flush();

			$sql = "SELECT DISTINCT p.post_id 
				FROM " . POSTS_TABLE . " p 
				LEFT JOIN " . USERS_TABLE . " u ON p.poster_id = u.user_id 
				WHERE u.user_id IS NULL";
			$result = query($sql, "Couldn't obtain list of deleted users");
			
			$users_removed = $db->sql_numrows($result);

			if ( $users_removed )
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

			print "<span class=\"ok\"><b>OK</b></span> ( Removed $users_removed non-existent user references )<br />\n";

			end_step('convert_privmsgs');

		case 'convert_privmsgs':
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

			$batchsize = 2000;
			for($i = 0; $i <= $maxid; $i += $batchsize)
			{
				$batchstart = $i + 1;
				$batchend = $i + $batchsize;
				
				print " * Converting Private Message ( $batchstart to $batchend ) :: ";
				flush();

				$sql = "SELECT * 
					FROM " . PRIVMSGS_TABLE . "
					WHERE msg_id 
						BETWEEN $batchstart 
							AND $batchend";
				$result = query($sql, "Couldn't get " . POSTS_TEXT_TABLE . " post_id $batchstart to $batchend");

				lock_tables(1, array(PRIVMSGS_TABLE, PRIVMSGS_TEXT_TABLE));

				$per_pct = ceil( $db->sql_numrows($result) / 40 );
				$inc = 0;

				while( $row = $db->sql_fetchrow($result) )
				{
					if ( $row['msg_text'] == NULL )
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

					if ( preg_match("/^(.*?)\n-----------------\n.*$/is", $checksig, $matches) )
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

					$inc++;
					if ( $inc == $per_pct )
					{
						print '.';
						flush();
						$inc = 0;
					}
				}

				print " <span class=\"ok\"><b>OK</b></span><br />\n";
			}

			lock_tables(0);
			end_step('convert_moderators');

		case 'convert_moderators';
			$sql = "SELECT * 
				FROM forum_mods";
			$result = query($sql, "Couldn't get list with all forum moderators");

			while( $row = $db->sql_fetchrow($result) )
			{
				// Check if this moderator and this forum still exist
				$sql = "SELECT user_id  
					FROM " . USERS_TABLE . ", " . FORUMS_TABLE . " 
					WHERE user_id = " . $row['user_id'] . " 
						AND forum_id = " . $row['forum_id'];
				$check_data = query($sql, "Couldn't check if user " . $row['user_id'] . " and forum " . $row['forum_id'] . " exist");

				if ( !($rowtest = $db->sql_fetchrow($check_data)) )
				{
					// Either the moderator or the forum have been deleted, this line in forum_mods was redundant, skip it.
					continue;
				}

				$sql = "SELECT g.group_id 
					FROM " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug 
					WHERE g.group_id = ug.group_id 
						AND ug.user_id = " . $row['user_id'] . "
						AND g.group_single_user = 1";
				$insert_group = query($sql, "Couldn't get group number for user " . $row['user_id'] . ".");

				$group_id = $db->sql_fetchrow($insert_group);
				$group_id = $group_id['group_id'];

				print " * Adding moderator for forum " . $row['forum_id'] . " :: ";
				flush();

				$sql = "INSERT INTO " . AUTH_ACCESS_TABLE . " (group_id, forum_id, auth_mod) VALUES ($group_id, ".$row['forum_id'].", 1)";
				query($sql, "Couldn't set moderator (user_id = " . $row['user_id'] . ") for forum " . $row['forum_id'] . ".");

				print "<span class=\"ok\"><b>OK</b></span><br />\n";
			}

			print " * Setting correct user_level for moderators ::";
			flush();

			$sql = "SELECT DISTINCT u.user_id 
				FROM " . USERS_TABLE . " u, " . USER_GROUP_TABLE . " ug, " . AUTH_ACCESS_TABLE . " aa 
				WHERE aa.auth_mod = 1 
					AND ug.group_id = aa.group_id 
					AND u.user_id = ug.user_id 
					AND u.user_level <> " . ADMIN;
			$result = query($sql, "Couldn't obtain list of moderators");

			if ( $row = $db->sql_fetchrow($result) )
			{
				$ug_sql = '';

				do
				{
					$ug_sql .= ( ( $ug_sql != '' ) ? ', ' : '' ) . $row['user_id'];
				}
				while ( $row = $db->sql_fetchrow($result) );

				$sql = "UPDATE " . USERS_TABLE . " 
					SET user_level = " . MOD . " 
					WHERE user_id IN ($ug_sql)";
				query($sql, "Couldn't set moderator status for users");
			}

			print "<span class=\"ok\"><b>OK</b></span><br />\n";
			
			end_step('convert_privforums');

		case 'convert_privforums':
			$sql = "SELECT fa.*, f.forum_name 
					FROM forum_access fa 
					LEFT JOIN " . FORUMS_TABLE . " f ON fa.forum_id = f.forum_id  
					ORDER BY fa.forum_id, fa.user_id";
			$forum_access = query($sql, "Couldn't get list with special forum access (forum_access)");

			$forum_id = -1;
			while( $row = $db->sql_fetchrow($forum_access) )
			{
				// Iterate trough access table
				if ( $row['forum_id'] != $forum_id )
				{
					// This is a new forum, create new group.
					$forum_id = $row['forum_id'];

					print " * Creating new group for forum $forum_id :: ";
					flush();

					$sql = "INSERT INTO " . GROUPS_TABLE . " (group_type, group_name, group_description, group_moderator, group_single_user)
						VALUES (" . GROUP_HIDDEN . ", '" . addslashes($row['forum_name']) . " Group', 'Converted Private Forum Group', " . $row['user_id'] . ", 0)";
					$result = query($sql, "Couldn't create group for ".$row['forum_name']);

					$group_id = $db->sql_nextid();

					if ( $group_id <= 0 )
					{
						print "<font color=\"red\">Group creation failed. Aborting creation of groups...<br></font>\n";
						continue 2;
					}

					print "<span class=\"ok\"><b>OK</b></span><br />\n";

					print " * Creating auth_access group for forum $forum_id :: ";
					flush();

					$sql = "INSERT INTO " . AUTH_ACCESS_TABLE . " (group_id, forum_id, auth_view, auth_read, auth_post, auth_reply, auth_edit, auth_delete, auth_sticky, auth_announce, auth_vote, auth_pollcreate)
						VALUES ($group_id, $forum_id, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1)";
					$result = query($sql, "Unable to set group auth access.");

					if ( $db->sql_affectedrows($result) <= 0 )
					{
						print "<font color=\"red\">Group creation failed. Aborting creation of groups...</font><br>\n";
						continue 2;
					}

					print "<span class=\"ok\"><b>OK</b></span><br />\n";
				}

				// Add user to the group
				$user_id = $row['user_id'];

				$sql = "INSERT INTO " . USER_GROUP_TABLE . " (group_id, user_id, user_pending)
					VALUES ($group_id, $user_id, 0)";
				query($sql, "Unable to add user_id $user_id to group_id $group_id <br>\n");
			}

			end_step('update_schema');

		case 'update_schema':
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

			// Loop tables in schema
			while (list($table, $table_def) = @each($field_def))
			{
				// Loop fields in table
				print " * Updating table '$table' :: ";
				flush();
				
				$sql = "SHOW FIELDS 
					FROM $table";
				$result = query($sql, "Can't get definition of current $table table");

				while( $row = $db->sql_fetchrow($result) )
				{
					$current_fields[] = $row['Field'];
				}
				
				$alter_sql = "ALTER TABLE $table ";
				while (list($field, $definition) = each($table_def))
				{
					if ( $field == '' )
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

					if ( !inarray($field, $current_fields) && $oldfield == $field )
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
				
				while ( list($key_name, $key_field) = each($key_def[$table]) )
				{
					if ( !inarray($key_name, $indices) )
					{
						$alter_sql .= ($key_name == 'PRIMARY') ? ", ADD PRIMARY KEY ($key_field)" : ", ADD INDEX $key_name ($key_field)";
					}
				}
				query($alter_sql, "Couldn't alter table $table");

				print "<span class=\"ok\"><b>OK</b></span><br />\n";
				flush();
			}

			end_step('convert_forums');

		case 'convert_forums':
			$sql = "SELECT * 
				FROM " . FORUMS_TABLE;
			$result = query($sql, "Couldn't get list with all forums");

			while( $row = $db->sql_fetchrow($result) )
			{
				print " * Converting forum '" . $row['forum_name'] . "' :: ";
				flush();

				// forum_access: (only concerns posting)
				//		1 = Registered users only
				//		2 = Anonymous Posting
				//		3 = Moderators/Administrators only
				switch( $row['forum_access'] )
				{
					case 1:
						// Public forum, no anonymous posting
						$auth_view			= AUTH_ALL;
						$auth_read			= AUTH_ALL;
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
						$auth_edit			= AUTH_REG;
						$auth_delete		= AUTH_REG;
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
				
				// Old auth structure:
				// forum_type: (only concerns viewing)
				//		0 = Public
				//		1 = Private
				switch( $row['forum_type'] )
				{
					case 0:
						$auth_view			= AUTH_ALL;
						$auth_read			= AUTH_ALL;
						break;
					default:
						//
						// Make it really private ... 
						//
						$auth_view			= AUTH_ACL;
						$auth_read			= AUTH_ACL;
						$auth_post			= AUTH_ACL;
						$auth_reply			= AUTH_ACL;
						$auth_edit			= AUTH_ACL;
						$auth_delete		= AUTH_ACL;
						$auth_vote			= AUTH_ACL;
						$auth_pollcreate	= AUTH_ACL;
						$auth_sticky		= AUTH_ACL;
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

				print "<span class=\"ok\"><b>OK</b></span><br />\n";
			}

			end_step('insert_themes');

		case 'insert_themes':
			print " * Inserting new values into themes table :: ";

			@reset($inserts);
			while( list($table, $inserts_table) = each($inserts) )
			{
				if ( $table == THEMES_TABLE )
				{
					$per_pct = ceil( count($inserts_table) / 40 );
					$inc = 0;

					while( list($nr, $insert) = each($inserts_table) )
					{
						query($insert, "Couldn't insert value into " . THEMES_TABLE);

						$inc++;
						if ( $inc == $per_pct )
						{
							print ".";
							flush();
							$inc = 0;
						}
					}
				}
			}

			print " <span class=\"ok\"><b>OK</b></span><br />\n";
			end_step('update_topics');

		case 'update_topics':
			$sql = "SELECT MAX(topic_id) AS max_topic 
				FROM " . TOPICS_TABLE;
			$result = query($sql, "Couldn't get max topic id");

			$row = $db->sql_fetchrow($result);

			$maxid = $row['max_topic'];

			lock_tables(1, array(TOPICS_TABLE, POSTS_TABLE));

			$batchsize = 1000;
			for($i = 0; $i <= $maxid; $i += $batchsize)
			{
				$batchstart = $i + 1;
				$batchend = $i + $batchsize;
				
				print " * Setting topic first post_id ( $batchstart to $batchend ) :: ";
				flush();

				$sql = "SELECT MIN(post_id) AS first_post_id, topic_id
					FROM " . POSTS_TABLE . "
					WHERE topic_id 
						BETWEEN $batchstart 
							AND $batchend 
					GROUP BY topic_id 
					ORDER BY topic_id ASC";
				$result = query($sql, "Couldn't get post id data");

				$per_pct = ceil( $db->sql_numrows($result) / 40 );
				$inc = 0;

				if ( $row = $db->sql_fetchrow($result) )
				{
					do
					{
						$sql = "UPDATE " . TOPICS_TABLE . " 
							SET topic_first_post_id = " . $row['first_post_id'] . " 
							WHERE topic_id = " . $row['topic_id'];
						query($sql, "Couldn't update topic first post id in topic :: $topic_id");

						$inc++;
						if ( $inc == $per_pct )
						{
							print ".";
							flush();
							$inc = 0;
						}
					}
					while ( $row = $db->sql_fetchrow($result) );
				}

				print " <span class=\"ok\"><b>OK</b></span><br />\n";
			}

			lock_tables(0);
			end_step('final_configuration');

		case 'final_configuration':
			//
			// Update forum last post information
			//
			$sql = "SELECT forum_id, forum_name 
				FROM " . FORUMS_TABLE;
			$f_result = query($sql, "Couldn't obtain forum_ids");

			while( $forum_row = $db->sql_fetchrow($f_result) )
			{
				print " * Updating '" . $forum_row['forum_name'] . "' post info :: ";
				flush();

				$id = $forum_row['forum_id'];

				$sql = "SELECT MIN(p.post_id) AS first_post, MAX(p.post_id) AS last_post
					FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t
					WHERE p.forum_id = $id
						AND p.topic_id = t.topic_id";
				$result = query($sql, "Could not get post ID forum post information :: $id");

				if ( $row = $db->sql_fetchrow($result) )
				{
					$first_post = ( $row['first_post'] ) ? $row['first_post'] : 0;
					$last_post = ($row['last_post']) ? $row['last_post'] : 0;
				}
				else
				{
					$first_post = 0;
					$last_post = 0;
				}

				$sql = "SELECT COUNT(post_id) AS total
					FROM " . POSTS_TABLE . "
					WHERE forum_id = $id";
				$result = query($sql, "Could not get post count forum post information :: $id");

				if ( $row = $db->sql_fetchrow($result) )
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

				if ( $row = $db->sql_fetchrow($result) )
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

				print "<span class=\"ok\"><b>OK</b></span><br />\n";
			}

			print "<br />\n * Update default user and finalise configuration :: ";
			flush();

			//
			// Update the default admin user with their information.
			//
			$sql = "SELECT MIN(user_regdate) AS oldest_time 
				FROM " . USERS_TABLE . " 
				WHERE user_regdate > 0 AND user_id > 0";
			$result = query($sql, "Couldn't obtain oldest post time");

			$row = $db->sql_fetchrow($result);

			$sql = "INSERT INTO " . $table_prefix . "config (config_name, config_value) 
				VALUES ('board_startdate', " . $row['oldest_time']  . ")";
			query($sql, "Couldn't insert board_startdate");

			$sql = "UPDATE " . $table_prefix . "config 
				SET config_value = '" . $server_name . "' 
				WHERE config_name = 'server_name' 
					OR config_name = 'cookie_domain'";
			query($sql, "Couldn't insert Board Server domain");

			$sql = "UPDATE " . $table_prefix . "config 
				SET config_value = '" . $server_port . "'
				WHERE config_name = 'server_port'";
			query($sql, "Couldn't insert Board server port");
			
			$sql = "UPDATE " . $table_prefix . "config 
				SET config_value = '" . $board_email . "'
				WHERE config_name = 'board_email'";
			query($sql, "Couldn't insert Board admin email");
			
			$sql = "UPDATE " . $table_prefix . "config 
				SET config_value = '" . $script_path . "'
				WHERE config_name = 'script_path'";
			query($sql, "Couldn't insert Board script path");
			
			//
			// Change session table to HEAP if MySQL version matches
			//
			$sql = "SELECT VERSION() AS mysql_version";
			$result = query($sql, "Couldn't obtain MySQL Version");

			$row = $db->sql_fetchrow($result);

			$version = $row['mysql_version'];

			if ( preg_match("/^(3\.23)|(4\.)/", $version) )
			{
				$sql = "ALTER TABLE " . $table_prefix . "sessions 
					TYPE=HEAP MAX_ROWS=500";
				$db->sql_query($sql);
			}

			echo "<span class=\"ok\"><b>OK</b></span><br />\n";
			end_step('drop_fields');

		case 'drop_fields':
			$fields = array(
				BANLIST_TABLE => array("ban_start", "ban_end", "ban_time_type"),
				FORUMS_TABLE => array("forum_access", "forum_moderator", "forum_type"), 
				PRIVMSGS_TABLE => array("msg_text"), 
				RANKS_TABLE => array("rank_max"), 
				SMILIES_TABLE => array("emotion"),
				TOPICS_TABLE => array("topic_notify")
			);

			while( list($table, $field_data) = each($fields) )
			{
				for($i = 0; $i < count($field_data); $i++)
				{
					print " * Drop field '" . $field_data[$i] . "' in '$table' :: ";
					flush();

					$sql = "ALTER TABLE $table 
						DROP COLUMN " . $field_data[$i];
					query($sql, "Couldn't drop field :: " . $field_data[$i] . " from table :: $table");

					print "<span class=\"ok\"><b>OK</b></span><br />\n";

				}
			}

			end_step('drop_tables');

		case 'drop_tables':
			$drop_tables = array('access', 'forum_access', 'forum_mods', 'headermetafooter', 'whosonline', $table_prefix . 'old_config');

			for($i = 0; $i < count($drop_tables); $i++)
			{
				print " * Dropping table '" . $drop_tables[$i] . "' :: ";
				flush();

				$sql = "DROP TABLE " . $drop_tables[$i];
				query($sql, "Couldn't drop table :: " . $drop_tables[$i]);

				print "<span class=\"ok\"><b>OK</b></span><br />\n";
			}

			end_step('fulltext_search_indexing');

		case 'fulltext_search_indexing':
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
			$per_percent = round(( $totalposts / 500 ) * 10);

			$postcounter = ( !isset($HTTP_GET_VARS['batchstart']) ) ? 0 : $HTTP_GET_VARS['batchstart'];

			$batchsize = 150; // Process this many posts per loop
			$batchcount = 0;
			$total_percent = 0;

			for(;$postcounter <= $max_post_id; $postcounter += $batchsize)
			{
				$batchstart = $postcounter + 1;
				$batchend = $postcounter + $batchsize;
				$batchcount++;

				print " * Fulltext Indexing ( $batchstart to $batchend ) :: ";
				flush();
				
				$sql = "SELECT *
					FROM " . POSTS_TEXT_TABLE ."
					WHERE post_id 
						BETWEEN $batchstart 
							AND $batchend";
				$posts_result = query($sql, "Couldn't obtain post_text");

				$per_pct = ceil( $db->sql_numrows($posts_result) / 40 );
				$inc = 0;

				if ( $row = $db->sql_fetchrow($posts_result) )
				{ 
					do
					{
						add_search_words('global', $row['post_id'], $row['post_text'], $row['post_subject']);

						$inc++;
						if ( $inc == $per_pct )
						{
							print ".";
							flush();
							$inc = 0;
						}
					}
					while( $row = $db->sql_fetchrow($posts_result) );
				}

				$db->sql_freeresult($posts_result);
				
				// Remove common words after the first 2 batches and after every 4th batch after that.
				if ( $batchcount % 4 == 3 )
				{
					remove_common('global', 4/10);
				}

				print " <span class=\"ok\"><b>OK</b></span><br />\n";
			}

			echo "\n<br /><br />\n\n<font size=\"+3\"><b>UPGRADE COMPLETED</b></font><br />\n";
	}
}

print "<br />If the upgrade completed without error you may click <a href=\"./../index.$phpEx\">Here</a> to proceed to the index<br />";

common_footer();

?>