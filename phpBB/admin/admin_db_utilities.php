<?php
/***************************************************************************
*                             admin_db_utilities.php 
*                              -------------------
*     begin                : Thu May 31, 2001
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

/***************************************************************************
*	We will attempt to create a file based backup of all of the data in the
*	users phpBB database.  The resulting file should be able to be imported by
*	the db_restore.php function, or by using the mysql command_line
*
*	Some functions are adapted from the upgrade_20.php script and others
*	adapted from the unoficial phpMyAdmin 2.2.0.
***************************************************************************/

if($setmodules == 1)
{
	$filename = basename(__FILE__);
	$module['General']['Backup_DB'] = $filename . "?perform=backup";
	$module['General']['Restore_DB'] = $filename . "?perform=restore";

	return;
}

$phpbb_root_path = "./../";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_INDEX, $session_length);
init_userprefs($userdata);
// 
// End sessionmanagement
//

//
// Check user permissions
//
if( !$userdata['session_logged_in'] )
{
	header("Location: ../login.$phpEx?forward_page=/admin/");
}
else if( $userdata['user_level'] != ADMIN )
{
	message_die(GENERAL_MESSAGE, "You are not authorised to administer this board");
}

// 
// Define Template files...
//
$template->set_filenames(array(
	"body" => "admin/db_utilities_body.tpl")
);

//
// Set VERBOSE to 1  for debugging info..
//
define("VERBOSE", 0);

//
// Increase maximum execution time, but don't complain about it if it isn't 
// allowed.
//
@set_time_limit(600); 

//
// The following functions are adapted from phpMyAdmin and upgrade_20.php
//
//
// This function is used for grabbing the sequences for postgres...
//
function pg_get_sequences($crlf, $backup_type)
{
	global $db;

	$get_seq_sql = "SELECT relname FROM pg_class WHERE NOT relname ~ 'pg_.*' 
		AND relkind = 'S' ORDER BY relname";

	$seq = $db->sql_query($get_seq_sql);

	if( !$num_seq = $db->sql_numrows($seq) )
	{

		$return_val = "# No Sequences Found $crlf";

	}
	else
	{
		$return_val = "# Sequences $crlf";
		$i_seq = 0;

		while($i_seq < $num_seq)
		{
			$row = sql_fetchrow($seq);
			$sequence = $row['relname'];

			$get_props_sql = "SELECT * FROM $sequence";
			$seq_props = $db->sql_query($get_props_sql);

			if($db->sql_numrows($seq_props) > 0)
			{
				$row1 = $db->sql_fetchrow($seq_props);

				if($backup_type == 'structure')
				{
					$row['last_value'] = 1;
				} 

				$return_val .= "CREATE SEQUENCE $sequence start " . $row['last_value'] . ' increment ' . $row['increment_by'] . ' maxvalue ' . $row['max_value'] . ' minvalue ' . $row['min_value'] . ' cache ' . $row['cache_value'] . "; $crlf";

			}  // End if numrows > 0

			if(($row['last_value'] > 1) && ($backup_type != 'structure'))
			{
				$return_val .= "SELECT NEXTVALE('$sequence'); $crlf";
				unset($row['last_value']);
			}

			$i_seq++;

		} // End while..

	} // End else...

	return $returnval;

} // End function...

//
// The following functions will return the "CREATE TABLE syntax for the 
// varying DBMS's 
//
// This function returns, will return the table def's for postgres...
//
function get_table_def_postgres($table, $crlf)
{
	global $drop, $db;

	$schema_create = "";

	$field_query = "SELECT a.attnum, a.attname AS field, t.typename as type, a.attlen AS length, a.atttypmod as lengthvar, a.attnotnull as notnull
		FROM pg_class c, pg_attribute a, pg_type t
		WHERE c.relname = '$table'
			AND a.attnum > 0
			AND a.attrelid = c.oid
			AND a.attypid = t.oid
		ORDER BY a.attnum";
	$result = $db->sql_query($field_query);

	if(!$result)
	{
		$error = $db->sql_error();
		message_die(GENERAL_ERROR, 'Failed in get_table_def (show fields) : ' . $error['message']);
	} // end if..

	if ($drop == 1)
	{
		$schema_create .= "DROP TABLE $table;$crlf";
	} // end if

	$schema_create .= "CREATE TABLE $table($crlf";

	while ($row = $db->sql_fetchrow($result))
	{
		$sql_get_default = "SELECT d.adsrc AS rowdefault
			FROM pg_attrdef d, pg_class c
			WHERE (c.relname = '$table') 
				AND (c.oid = d.adrelid) 
				AND d.adnum = " . $row['attnum'];
		$def_res = $db->sql_query($sql_get_default);

		if (!$def_res)
		{
			unset($row['rowdefault']);
		}
		else 
		{
			$row['rowdefault'] = @pg_result($def_res, 0, 'rowdefault');
		}

		if ($row['type'] == 'bpchar')
		{
			// Internally stored as bpchar, but isn't accepted in a CREATE TABLE statement.
			$row['type'] = 'char';
		}

		$schema_create .= '	' . $row['field'] . ' ' . $row['type'];

		if (eregi('char', $row['type']))
		{
			if ($row['lengthvar'] > 0)
			{
				$schema_create .= '(' . ($row['lengthvar'] -4) . ')';
			}
		}

		if (eregi('numeric', $row['type']))
		{
			$schema_create .= '(';
			$schema_create .= sprintf("%s,%s", (($row['lengthvar'] >> 16) & 0xffff), (($row['lengthvar'] - 4) & 0xffff));
			$schema_create .= ')';
		}

		if (!empty($row['rowdefault']))
		{
			$schema_create .= ' DEFAULT ' . $row['rowdefault'];
		}

		if ($row['notnull'] == 't')
		{
			$schema_create .= ' NOT NULL';
		}

		$schema_create .= ", $crlf";

	}

	$sql_pri_keys = "SELECT ic.relname AS index_name, bc.relname AS tab_name, ta.attname AS column_name, i.indisunique AS unique_key, i.indisprimary AS primary_key
		FROM pg_class bc, pg_class ic, pg_index i, pg_attribute ta, pg_attribute ia 
		WHERE (bc.oid = i.indrelid) 
			AND (ic.oid = i.indexrelid) 
			AND (ia.attrelid = i.indexrelid) 
			AND	(ta.attrelid = bc.oid) 
			AND (bc.relname = '$table') 
			AND (ta.attrelid = i.indrelid) 
			AND (ta.attnum = i.indkey[ia.attnum-1])
		ORDER BY index_name, tab_name, column_name ";
	$result = $db->sql_query($sql_pri_keys);

	if(!$result)
	{
		message_die(GENERAL_ERROR, "Failed in get_table_def (show fields)", "", __LINE__, __FILE__, $sql_pri_keys);
	}

	while ( $row = $db->sql_fetchrow($result))
	{
		if ($row['primary_key'] == 't')
		{
			if (!empty($primary_key))
			{
				$primary_key .= ', ';
			}

			$primary_key .= $row['column_name'];
			$primary_key_name = $row['index_name'];

		}
		else
		{
			//
			// We have to store this all this info because it is possible to have a multi-column key...
			// we can loop through it again and build the statement
			//
			$index_rows[$row['index_name']]['table'] = $table;
			$index_rows[$row['index_name']]['unique'] = ($row['unique_key'] == 't') ? ' UNIQUE ' : '';
			$index_rows[$row['index_name']]['column_names'] .= $row['column_name'] . ', ';
		}
	}

	if (!empty($index_rows))
	{
		while(list($idx_name, $props) = each($index_rows))
		{
			$props['column_names'] = ereg_replace(", $", "" , $props['column_name']);
			$index_create .= 'CREATE ' . $props['unique'] . " INDEX $idx_name ON $table (" . $props['column_names'] . ");$crlf";
		}
	}

	if (!empty($primary_key))
	{
		$schema_create .= "	CONSTRAINT $primary_key_name PRIMARY KEY ($primary_key),$crlf";
	}

	//
	// Generate constraint clauses for CHECK constraints
	//
	$sql_checks = "SELECT rcname as index_name, rcsrc 
		FROM pg_relcheck, pg_class bc
		WHERE rcrelid = bc.oid 
			AND bc.relname = '$table'
			AND NOT EXISTS (
				SELECT * 
					FROM pg_relcheck as c, pg_inherits as i 
					WHERE i.inhrelid = pg_relcheck.rcrelid 
						AND c.rcname = pg_relcheck.rcname 
						AND c.rcsrc = pg_relcheck.rcsrc 
						AND c.rcrelid = i.inhparent
			)";
	$result = $db->sql_query($sql_checks);

	if (!$result)
	{
		$error = $db->sql_error();
		message_die(GENERAL_ERROR, 'Failed in get_table_def (show fields) : ' . $error['message']);
	}

	while ($row = $db->sql_fetchrow($result))
	{
		$schema_create .= '	CONSTRAINT ' . $row['index_name'] . ' CHECK ' . $row['rcsrc'] . ",$crlf";
	}

	$schema_create = ereg_replace(',' . $crlf . '$', '', $schema_create);
	$index_create = ereg_replace(',' . $crlf . '$', '', $index_create);

	$schema_create .= "$crlf);$crlf";

	if (!empty($index_create))
	{
		$schema_create .= $index_create;
	}

	return (stripslashes($schema_create));

}

//
// This function returns the "CREATE TABLE" syntax for mysql dbms...
//
function get_table_def_mysql($table, $crlf) 
{
	global $drop, $db;

	$schema_create = "";
	$field_query = "SHOW FIELDS FROM $table";
	$key_query = "SHOW KEYS FROM $table";

	//
	// If the user has selected to drop existing tables when doing a restore.
	// Then we add the statement to drop the tables....
	//
	if ($drop == 1)
	{
		$schema_create .= "DROP TABLE IF EXISTS $table;$crlf";
	}
	
	$schema_create .= "CREATE TABLE $table($crlf";

	//
	// Ok lets grab the fields...
	//
	$result = $db->sql_query($field_query);
	if(!result)
	{
		message_die(GENERAL_ERROR, "Failed in get_table_def (show fields)", "", __LINE__, __FILE__, $field_query);
	}

	while ($row = $db->sql_fetchrow($result))
	{
		$schema_create .= '	' . $row['Field'] . ' ' . $row['Type'];

		if(!empty($row['Default']))
		{
			$schema_create .= ' DEFAULT \'' . $row['Default'] . '\'';
		}

		if($row['Null'] != "YES") 
		{
			$schema_create .= ' NOT NULL';
		}

		if($row['Extra'] != "")
		{
			$schema_create .= ' ' . $row['Extra'];
		}

		$schema_create .= ",$crlf";
	}
	//
	// Drop the last ',$crlf' off ;)
	//
	$schema_create = ereg_replace(',' . $crlf . '$', "", $schema_create);

	//
	// Get any Indexed fields from the database...
	//
	$result = $db->sql_query($key_query);
	if(!$result)
	{
		message_die(GENERAL_ERROR, "FAILED IN get_table_def (show keys)", "", __LINE__, __FILE__, $key_query);
	}

	while($row = $db->sql_fetchrow($result))
	{
		$kname = $row['Key_name'];

		if(($kname != 'PRIMARY') && ($row['Non_unique'] == 0))
		{
			$kname = "UNIQUE|$kname";
		}

		if(!is_array($index[$kname])) 
		{
			$index[$kname] = array();
		}

		$index[$kname][] = $row['Column_name'];
	}

	while(list($x, $columns) = @each($index)) 
	{
		$schema_create .= ", $crlf";

		if($x == 'PRIMARY')
		{
			$schema_create .= '	PRIMARY KEY (' . implode($columns, ', ') . ')';
		} 
		elseif (substr($x,0,6) == 'UNIQUE')
		{
			$schema_create .= '	UNIQUE ' . substr($x,7) . ' (' . implode($columns, ', ') . ')';
		} 
		else
		{
			$schema_create .= "	KEY $x (" . implode($columns, ', ') . ')';
		}
	}

	$schema_create .= "$crlf);";

	if(get_magic_quotes_runtime()) 
	{
		return(stripslashes($schema_create));
	} 
	else 
	{
		return($schema_create);
	}
	
} // End get_table_def_mysql


//
// This fuction will return a tables create definition to be used as an sql
// statement.
//
//
// The following functions Get the data from the tables and format it as a 
// series of INSERT statements, for each different DBMS...
// After every row a custom callback function $handler gets called.
// $handler must accept one parameter ($sql_insert);
//
//
// Here is the function for postgres...
//
function get_table_content_postgres($table, $handler)
{
	global $db;

	$result = $db->sql_query("SELECT * FROM $table");

	if (!$result)
	{
		message_die(GENERAL_ERROR, "Faild in get_table_content (select *)", "", __LINE__, __FILE__, "SELECT * FROM $table");
	}

	$i_num_fields = $db->sql_numfields($result);

	for ($i = 0; $i < $i_num_fields; $i++)
	{
		$aryType[] = $db->sql_fieldtype($i, $result);
		$aryName[] = $db->sql_fieldname($i, $result);
	}

	$iRec = 0;

	while($row = $db->fetchrow($result))
	{
		unset($schema_vals);
		unset($schema_fields);
		unset($schema_insert);

		for($i = 0; $i < $i_num_fields; $i++)
		{
			$strVal = $row[$aryName[$i]];
			if (eregi("char|text|bool", $aryType[$i]))
			{
				$strQuote = "'";
				$strEmpty = "";
				$strVal = addslashes($strVal);
			}
			elseif (eregi("date|timestamp", $aryType[$i]))
			{
				if ($empty($strVal))
				{
					$strQuote = "";
				}
				else
				{
					$strQuote = "'";
				}
			}
			else
			{
				$strQuote = "";
				$strEmpty = "NULL";
			}

			if (empty($strVal) && $strVal != "0")
			{
				$strVal = $strEmpty;
			}

			$schema_vals .= " $strQuote$strVal$strQuote,";
			$schema_fields .= " $aryName[$i],";			

		}

		$schema_vals = ereg_replace(",$", "", $schema_vals);
		$schema_vals = ereg_replace("^ ", "", $schema_vals);
		$schema_fields = ereg_replace(",$", "", $schema_fields);
		$schema_fields = ereg_replace("^ ", "", $schema_fields);

		$schema_insert = "INSERT INTO $table ($schema_fields) VALUES($schema_vals);";

		$handler(trim($schema_insert));
	}

	return(true);

}// end function get_table_content_postgres...


function get_table_content_mysql($table, $handler)
{
	global $db;

	$result = $db->sql_query("SELECT * FROM $table");

	if (!$result)
	{
		message_die(GENERAL_ERROR, "Faild in get_table_content (select *)", "", __LINE__, __FILE__, "SELECT * FROM $table");
	}

	if($db->sql_numrows($result) > 0) 
	{
		$schema_insert = "\n#\n# Table Data for $table\n#\n";
	}
	else
	{
		$schema_insert = "";
	}

	$handler($schema_insert);

	while ($row = $db->sql_fetchrow($result))
	{
		$table_list = '(';
		$num_fields = $db->sql_numfields($result);

		for ($j = 0; $j < $num_fields; $j++)
		{
			$table_list .= $db->sql_fieldname($j, $result) . ', ';
		}

		$table_list = ereg_replace(', $', '', $table_list);
		$table_list .= ')';
		
		$schema_insert = "INSERT INTO $table $table_list VALUES(";

		for ($j = 0; $j < $num_fields; $j++)
		{
			if(!isset($row[$j]))
			{
				$schema_insert .= ' NULL, ';
			} 
			elseif ($row[$j] != '') 
			{
				$schema_insert .= ' \'' . addslashes($row[$j]) . '\',';
			} 
			else
			{
				$schema_insert .= '\'\',';
			}
		}

		$schema_insert = ereg_replace(',$', '', $schema_insert);
		$schema_insert .= ');';
		$handler(trim($schema_insert));

	}
	return(true);
}

function output_table_content($content)
{
	global $backup_sql;

	$backup_sql .= $content . "\n";

	return;
}

//
// remove_remarks will strip the sql comment lines out of an uploaded sql file
//
function remove_remarks($sql)
{
	$i = 0;	

	while($i < strlen($sql))
	{
		if( $sql[$i]	== "#" && ( $sql[$i-1] == "\n" || $i==0 ) )
		{
			$j = 1;
			
			while( $sql[$i + $j] != "\n" )
			{
				$j++;
			}
			$sql = substr($sql,0,$i) . substr($sql,$i+$j);
		}
		$i++;
	}

  return($sql);

}

//
// split_sql_file will split an uploaded sql file into single sql statements.
//
function split_sql_file($sql, $delimiter)
{
	$sql = trim($sql);
	$char = "";
	$last_char = "";
	$ret = array();
	$in_string = true;
	
	for($i = 0; $i < strlen($sql); $i++)
	{
		$char = $sql[$i];
		
		//
		// if delimiter found, add the parsed part to the returned array
		//
		if($char == $delimiter && !$in_string) 
		{
			$ret[] = substr($sql, 0, $i);
			$sql = substr($sql, $i + 1);
			$i = 0;
			$last_char = "";
		}

		if($last_char == $in_string && $char == ")")
		{
			$in_string = false;
		}

		if($char == $in_string && $last_char != "\\")
		{ 
			$in_string = false;
		}
		elseif(!$in_string && ($char == "\"" || $char == "'") && ($last_char != "\\"))
		{
			$in_string = $char;
		}

		$last_char = $char;
	}

	if (!empty($sql))
	{
		$ret[] = $sql;
	}

	return($ret);
}
//  
// End Functions
// -------------

//
// Begin program proper
//

if( isset($HTTP_GET_VARS['perform']) || isset($HTTP_POST_VARS['perform']) )
{
	$perform = (isset($HTTP_POST_VARS['perform'])) ? $HTTP_POST_VARS['perform'] : $HTTP_GET_VARS['perform'];

	switch($perform)
	{
		case 'backup':
			if( SQL_LAYER == 'oracle' || SQL_LAYER == 'odbc' || SQL_LAYER == 'mssql' )
			{
				//
				// Page header
				//
				$template_header = "admin/page_header.tpl";
				include('page_header_admin.'.$phpEx);

				switch(SQL_LAYER)
				{
					case 'oracle':
						$db_type = "Oracle";
						break;
					case 'ofbc':
						$db_type = "ODBC";
						break;
					case 'mssql':
						$db_type = "MSSQL";
						break;
				}

				$db_message = "<h2>Database backups are not currently supported for your Database system (" . $db_type . ")</h2>\n";

				$template->assign_vars(array(
					"U_DB_MESSAGE" => $db_message,
					"U_DB_LINKS" => $db_links)
				);
				$template->pparse("body");
				
				break;
			}

			$tables = array('auth_access', 'banlist', 'categories', 'config', 'disallow', 'forums', 'groups', 'posts', 'posts_text', 'privmsgs', 'privmsgs_text', 'ranks', 'session', 'smilies', 'themes', 'themes_name', 'topics', 'user_group', 'users', 'words');

			$additional_tables = (isset($HTTP_POST_VARS['additional_tables'])) ? $HTTP_POST_VARS['additional_tables'] : ( (isset($HTTP_GET_VARS['additional_tables'])) ? $HTTP_GET_VARS['additional_tables'] : "" );
			$backup_type = (isset($HTTP_POST_VARS['backup_type'])) ? $HTTP_POST_VARS['backup_type'] : ( (isset($HTTP_GET_VARS['backup_type'])) ? $HTTP_GET_VARS['backup_type'] : "" );

			if(!empty($additional_tables)) 
			{
				if(ereg(",", $additional_tables))
				{
					$additional_tables = split(",", $additional_tables);

					for($i = 0; $i < count($additional_tables); $i++)
					{
						$tables[] = trim($additional_tables[$i]);
					}

				}
				else
				{
					$tables[] = trim($additional_tables);
				}
			}

			if( !isset($HTTP_POST_VARS['backupstart']) && !isset($HTTP_GET_VARS['backupstart']))
			{
				//
				// Page header
				//
				$template_header = "admin/page_header.tpl";
				include('page_header_admin.'.$phpEx);

				$db_message = "<H2>This will perform a backup of all phpBB2 related tables.</H2><BR>\n";
				$db_message .= "<P>If you have any additional custom tables in the same database with phpBB that you would like to back up as well please enter their names seperated by commas in the Additional Tables textbox below.<BR>\n";
				$db_message .= "Otherwise just select the form of backup you want to perform and click the Start Backup button below.</P><BR>\n\n";
				$db_links = "<FORM METHOD=\"post\" ACTION=\"". append_sid($PHP_SELF) . "\">\n";
				$db_links .= "<TABLE BORDER=0>\n";
				$db_links .= "<TR><TD>Additional Tables:</TD><TD><INPUT TYPE=\"text\" NAME=\"additional_tables\"></TD></TR>\n";
				$db_links .= "<TR><TD>Full Backup:</TD><TD><INPUT TYPE=\"radio\" NAME=\"backup_type\" VALUE=\"full\" checked></TD></TR>\n";
				$db_links .= "<TR><TD>Table Structure Only:</TD><TD><INPUT TYPE=\"radio\" NAME=\"backup_type\" VALUE=\"structure\"></TD></TR>\n";
				$db_links .= "<TR><TD>Table Data Only:</TD><TD><INPUT TYPE=\"radio\" NAME=\"backup_type\" VALUE=\"data\"></TD></TR>\n";
				$db_links .= "</TABLE><INPUT TYPE=\"hidden\" NAME=\"perform\" VALUE=\"backup\">\n";
				$db_links .= "<INPUT TYPE=\"hidden\" NAME=\"drop\" VALUE=\"1\">";
				$db_links .= "<INPUT TYPE=\"submit\" NAME=\"backupstart\" VALUE=\"Start Backup\"></FORM></P>\n";

				$template->assign_vars(array(
					"U_DB_MESSAGE" => $db_message,
					"U_DB_LINKS" => $db_links)
				);
				$template->pparse("body");

				break;
			
			}
			else if( !isset($HTTP_POST_VARS['startdownload']) && !isset($HTTP_GET_VARS['startdownload']) )
			{
				//
				// Page header
				//
				$template->assign_vars(array(
					"META" => "<meta http-equiv=\"refresh\" content=\"0;url=admin_db_utilities.$phpEx?perform=backup&additional_tables=".quotemeta($additional_tables)."&backup_type=$backup_type&drop=1&backupstart=1&startdownload=1\">")
				);

				$template_header = "admin/page_header.tpl";
				include('page_header_admin.'.$phpEx);

				$db_message = "<H2>Your backup file will start downloading soon</H2><br>\n";

				$template->assign_vars(array(
					"U_DB_MESSAGE" => $db_message,
					"U_DB_LINKS" => $db_links)
				);

				$template->pparse("body");

				include('page_footer_admin.'.$phpEx);

			}

			//
			// Build the sql script file...
			//
			$backup_sql = "#\n";
			$backup_sql .= "# phpBB Backup Script\n";
			$backup_sql .= "# Dump of tables for $dbname\n";
			$backup_sql .= "#\n# DATE : " .  gmdate("d-m-Y H:i:s", time()) . " GMT\n";
			$backup_sql .= "#\n";

			if(SQL_LAYER == 'postgres')
			{
				$backup_sql = "\n" . pg_get_sequences("\n", $backup_type);
			}

			for($i = 0; $i < count($tables); $i++)
			{
				$table_name = $tables[$i];
				$table_def_function = "get_table_def_" . SQL_LAYER;
				$table_content_function = "get_table_content_" . SQL_LAYER;

				if($backup_type != 'data')
				{
					$backup_sql .= "#\n# TABLE: " . $table_prefix . $table_name . "\n#\n";
					$backup_sql .= $table_def_function($table_prefix . $table_name, "\n") . "\n";
				} 

				if($backup_type != 'structure')
				{
					$table_content_function($table_prefix . $table_name, "output_table_content");
				}
			}

			//
			// move forward with sending the file across...
			//
			header("Content-Type: text/x-delimtext; name=\"phpbb_db_backup.sql\"");
			header("Content-disposition: attachment; filename=phpbb_db_backup.sql");
			header("Pragma: no-cache");

			echo $backup_sql;

			exit;

			break;

		case 'restore':
			if(!isset($restore_start)) 
			{	
				//
				// Page header
				//
				$template_header = "admin/page_header.tpl";
				include('page_header_admin.'.$phpEx);

				$db_message = "<H2>This will perform a full restore of a previously Backed up phpBB database</H2><BR>\n";
				$db_message .= "<P><b>WARNING: This will overwrite any existing data</b><br>\n";
				$db_links = "<FORM ENCTYPE=\"multipart/form-data\" METHOD=\"post\" ACTION=\"" . append_sid($PHP_SELF) . "\">\n";
				$db_links .= "<INPUT TYPE=\"hidden\" NAME=\"perform\" VALUE=\"restore\">\n";
				$db_links .= "Backup File:<INPUT TYPE=\"file\" NAME=\"backup_file\">\n";
				$db_links .= "<INPUT TYPE=\"submit\" NAME=\"restore_start\" VALUE=\"Start Restore\">\n";
				$db_links .= "</FORM></P>\n";

				$template->assign_vars(array(
					"U_DB_MESSAGE" => $db_message,
					"U_DB_LINKS" => $db_links)
				);
				$template->pparse("body");

				break;

			}
			else 
			{	
				// Handle the file upload ....
				if($backup_file == "none")
				{
					message_die(GENERAL_ERROR, "Backup file upload failed");
				}

				if(ereg("^php[0-9A-Za-z_.-]+$", basename($backup_file)))
				{
					$sql_query = fread(fopen($backup_file, 'r'), filesize($backup_file));
					$sql_query = stripslashes($sql_query);
				}
				else
				{
					message_die(GENERAL_ERROR, "Trouble Accessing uploaded file");
				}

				$sql_query = trim($sql_query);

				if($sql_query != "") 
				{
					// Strip out sql comments...
					$sql_query = remove_remarks($sql_query);
					$pieces = split_sql_file($sql_query, ";");

					for($i = 0; $i < count($pieces); $i++)
					{
						$sql = trim($pieces[$i]);

						if(!empty($sql) and $sql[0] != "#")
						{	
							if(VERBOSE == 1) 
							{
								echo "Executing: $sql\n<br>";
							}

							$result = $db->sql_query($sql);
	
							if(!$result && ( !(SQL_LAYER == 'postgres' && eregi("drop table", $sql) ) ) )
							{
								message_die(GENERAL_ERROR, "Error importing backup file", "", __LINE__, __FILE__, $sql);
							}
						}
					}
				}

				//
				// Page header
				//
				$template_header = "admin/page_header.tpl";
				include('page_header_admin.'.$phpEx);

				$db_message = "<CENTER><H2>The Database has been successfully restored..</H2>\n";
				$db_message .= "<P><BR>Your board should be back to the state it was when the backup was made.<BR></P>\n";

				$template->assign_vars(array(
					"U_DB_MESSAGE" => $db_message,
					"U_DB_LINKS" => $db_links)
				);

				$template->pparse("body");
				break;
			}
			break;
	}
} 
else 
{
	//
	// Page header
	//
	$template_header = "admin/page_header.tpl";
	include('page_header_admin.'.$phpEx);

	$db_message = "<h2>These Utilties will help you to backup or restore your phpBB database</h2><br>\n";

	$template->assign_vars(array(
		"U_DB_MESSAGE" => $db_message,
		"U_DB_LINKS" => $db_links)
	);

	$template->pparse("body");
}

include('page_footer_admin.'.$phpEx);

?>