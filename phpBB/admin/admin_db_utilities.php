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
// End session management
//

//
// Is user logged in? If yes are they an admin?
//
if( !$userdata['session_logged_in'] )
{
	header("Location: ../login.$phpEx?forward_page=admin/");
}
else if( $userdata['user_level'] != ADMIN )
{
	message_die(GENERAL_MESSAGE, $lang['Not_admin']);
}

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
	//
	// Get a listing of the fields, with their associated types, etc.
	//

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
		message_die(GENERAL_ERROR, "Failed in get_table_def (show fields)", "", __LINE__, __FILE__, $field_query);
	} // end if..

	if ($drop == 1)
	{
		$schema_create .= "DROP TABLE $table;$crlf";
	} // end if

	//
	// Ok now we actually start building the SQL statements to restore the tables
	//

	$schema_create .= "CREATE TABLE $table($crlf";

	while ($row = $db->sql_fetchrow($result))
	{
		//
		// Get the data from the table
		//
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
	//
	// Get the listing of primary keys.
	//

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
		message_die(GENERAL_ERROR, "Failed in get_table_def (show fields)", "", __LINE__, __FILE__, $sql_checks);
	}
	
	//
	// Add the constraints to the sql file.
	//
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

	//
	// Ok now we've built all the sql return it to the calling function.
	//
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

	//
	// Grab all of the data from current table.
	//

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
		// 
		// Build the SQL statement to recreate the data.
		//
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

		//
		// Take the ordered fields and their associated data and build it
		// into a valid sql statement to recreate that field in the data.
		//
		$schema_insert = "INSERT INTO $table ($schema_fields) VALUES($schema_vals);";

		$handler(trim($schema_insert));
	}

	return(true);

}// end function get_table_content_postgres...

//
// This function is for getting the data from a mysql table.
//

function get_table_content_mysql($table, $handler)
{
	global $db;
	//
	// Grab the data from the table.
	//
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

	//
	// Loop through the resulting rows and build the sql statement.
	//

	while ($row = $db->sql_fetchrow($result))
	{
		$table_list = '(';
		$num_fields = $db->sql_numfields($result);
		//
		// Grab the list of field names.
		//
		for ($j = 0; $j < $num_fields; $j++)
		{
			$table_list .= $db->sql_fieldname($j, $result) . ', ';
		}
		//
		// Get rid of the last comma
		//
		$table_list = ereg_replace(', $', '', $table_list);
		$table_list .= ')';
		//
		// Start building the SQL statement.
		//
		$schema_insert = "INSERT INTO $table $table_list VALUES(";
		//
		// Loop through the rows and fill in data for each column
		//
		for ($j = 0; $j < $num_fields; $j++)
		{
			if(!isset($row[$j]))
			{
				//
				// If there is no data for the column set it to null.
				// There was a problem here with an extra space causing the
				// sql file not to reimport if the last column was null in 
				// any table.  Should be fixed now :) JLH
				//
				$schema_insert .= ' NULL,';
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
		//
		// Get rid of the the last comma.  
		//
		$schema_insert = ereg_replace(',$', '', $schema_insert);
		$schema_insert .= ');';
		//
		// Go ahead and send the insert statement to the handler function.
		//
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

		//
		// Added lat $sql[$i-2] != "\\" to fix potential problem with restore..
		//
		if($last_char == $in_string && $char == ")" && $sql[$i-2] != "\\")
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
				switch(SQL_LAYER)
				{
					case 'oracle':
						$db_type = "Oracle";
						break;
					case 'odbc':
						$db_type = "ODBC";
						break;
					case 'mssql':
						$db_type = "MSSQL";
						break;
				}

				include('page_header_admin.'.$phpEx);

				$template->set_filenames(array(
					"body" => "admin/admin_message_body.tpl")
				);

				$template->assign_vars(array(
					"MESSAGE_TITLE" => $lang['Information'], 
					"MESSAGE_TEXT" => $lang['Backups_not_supported'])
				);

				$template->pparse("body");
				
				break;
			}

			$tables = array('auth_access', 'banlist', 'categories', 'config', 'disallow', 'forums', 'forum_prune', 'groups', 'posts', 'posts_text', 'privmsgs', 'privmsgs_text', 'ranks', 'session', 'smilies', 'themes', 'themes_name', 'topics', 'user_group', 'users', 'words');

			$additional_tables = (isset($HTTP_POST_VARS['additional_tables'])) ? $HTTP_POST_VARS['additional_tables'] : ( (isset($HTTP_GET_VARS['additional_tables'])) ? $HTTP_GET_VARS['additional_tables'] : "" );

			$backup_type = (isset($HTTP_POST_VARS['backup_type'])) ? $HTTP_POST_VARS['backup_type'] : ( (isset($HTTP_GET_VARS['backup_type'])) ? $HTTP_GET_VARS['backup_type'] : "" );

			$gzipcompress = (!empty($HTTP_POST_VARS['gzipcompress'])) ? $HTTP_POST_VARS['gzipcompress'] : ( (!empty($HTTP_GET_VARS['gzipcompress'])) ? $HTTP_GET_VARS['gzipcompress'] : 0 );

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
				include('page_header_admin.'.$phpEx);

				$template->set_filenames(array(
					"body" => "admin/db_utils_backup_body.tpl")
				);

				$s_hidden_fields = "<input type=\"hidden\" name=\"perform\" value=\"backup\" /><input type=\"hidden\" name=\"drop\" value=\"1\" /><input type=\"hidden\" name=\"perform\" value=\"$perform\" />";

				$template->assign_vars(array(
					"L_DATABASE_BACKUP" => $lang['Database_Utilities'] . " : " . $lang['Backup'], 
					"L_BACKUP_EXPLAIN" => $lang['Backup_explain'], 
					"L_FULL_BACKUP" => $lang['Full_backup'],
					"L_STRUCTURE_BACKUP" => $lang['Structure_backup'],
					"L_DATA_BACKUP" => $lang['Data_backup'],
					"L_ADDITIONAL_TABLES" => $lang['Additional_tables'],
					"L_START_BACKUP" => $lang['Start_backup'],
					"L_BACKUP_OPTIONS" => $lang['Backup_options'], 
					"L_GZIP_COMPRESS" => $lang['Gzip_compress'], 
					"L_NO" => $lang['No'], 
					"L_YES" => $lang['Yes'], 

					"S_HIDDEN_FIELDS" => $s_hidden_fields, 
					"S_DBUTILS_ACTION" => append_sid("admin_db_utilities.$phpEx"))
				);
				$template->pparse("body");

				break;
			
			}
			else if( !isset($HTTP_POST_VARS['startdownload']) && !isset($HTTP_GET_VARS['startdownload']) )
			{
				$template->set_filenames(array(
					"body" => "admin/admin_message_body.tpl")
				);
				
				$template->assign_vars(array(
					"META" => "<meta http-equiv=\"refresh\" content=\"0;url=admin_db_utilities.$phpEx?perform=backup&additional_tables=" . quotemeta($additional_tables) . "&backup_type=$backup_type&drop=1&backupstart=1&gzipcompress=$gzipcompress&startdownload=1\">", 

					"MESSAGE_TITLE" => $lang['Database_Utilities'] . " : " . $lang['Backup'], 
					"MESSAGE_TEXT" => $lang['Backup_download'])
				);

				include('page_header_admin.php');

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
			header("Pragma: no-cache");

			$do_gzip_compress = FALSE;
			if( $gzipcompress )
			{
				$phpver = phpversion();

				if($phpver >= "4.0")
				{
					if(extension_loaded("zlib"))
					{
						$do_gzip_compress = TRUE;
					}
				}
			}

			if($do_gzip_compress)
			{
				header("Content-Type: text/x-delimtext; name=\"phpbb_db_backup.sql.gz\"");
				header("Content-disposition: attachment; filename=phpbb_db_backup.sql.gz");

				echo gzencode($backup_sql);
			}
			else
			{
				header("Content-Type: text/x-delimtext; name=\"phpbb_db_backup.sql\"");
				header("Content-disposition: attachment; filename=phpbb_db_backup.sql");

				echo $backup_sql;
			}

			exit;

			break;

		case 'restore':
			if(!isset($restore_start)) 
			{	
				// 
				// Define Template files...
				//
				include('page_header_admin.'.$phpEx);

				$template->set_filenames(array(
					"body" => "admin/db_utils_restore_body.tpl")
				);

				$s_hidden_fields = "<input type=\"hidden\" name=\"perform\" value=\"restore\"><input type=\"hidden\" name=\"perform\" value=\"$perform\">";

				$template->assign_vars(array(
					"L_DATABASE_RESTORE" => $lang['Database_Utilities'] . " : " . $lang['Restore'], 
					"L_RESTORE_EXPLAIN" => $lang['Restore_explain'], 
					"L_SELECT_FILE" => $lang['Select_file'], 
					"L_START_RESTORE" => $lang['Start_Restore'], 

					"S_DBUTILS_ACTION" => append_sid("admin_db_utilities.$phpEx"), 
					"S_HIDDEN_FIELDS" => $s_hidden_fields)
				);
				$template->pparse("body");

				break;

			}
			else 
			{	
				//
				// Handle the file upload ....
				// If no file was uploaded report an error...
				//
				if($backup_file == "none")
				{
					include('page_header_admin.'.$phpEx);
					message_die(GENERAL_ERROR, "Backup file upload failed");
				}
				//
				// If I file was actually uploaded, check to make sure that we 
				// are actually passed the name of an uploaded file, and not
				// a hackers attempt at getting us to process a local system
				// file.
				//
				if(ereg("^php[0-9A-Za-z_.-]+$", basename($backup_file)))
				{
					$sql_query = fread(fopen($backup_file, 'r'), filesize($backup_file));
					//
					// Comment this line out to see if this fixes the stuff...
					//
					//$sql_query = stripslashes($sql_query);
				}
				else
				{
					include('page_header_admin.'.$phpEx);
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
								flush();
							}

							$result = $db->sql_query($sql);
	
							if(!$result && ( !(SQL_LAYER == 'postgres' && eregi("drop table", $sql) ) ) )
							{
								include('page_header_admin.'.$phpEx);
								message_die(GENERAL_ERROR, "Error importing backup file", "", __LINE__, __FILE__, mysql_error() ."<br>". $sql);
							}
						}
					}
				}

				include('page_header_admin.'.$phpEx);

				$template->set_filenames(array(
					"body" => "admin/admin_message_body.tpl")
				);

				$message = $lang['Restore_success'];
				
				$template->assign_vars(array(
					"MESSAGE_TITLE" => $lang['Database_Utilities'] . " : " . $lang['Restore'], 
					"MESSAGE_TEXT" => $message)
				);

				$template->pparse("body");
				break;
			}
			break;
	}
} 

include('page_footer_admin.'.$phpEx);

?>