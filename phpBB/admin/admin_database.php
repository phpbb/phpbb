<?php
/***************************************************************************
*                              admin_database.php
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

if (!empty($setmodules))
{
	$filename = basename(__FILE__);
	$module['DB']['DB_BACKUP'] = ($auth->acl_get('a_backup')) ? $filename . "$SID&amp;mode=backup" : '';

	$file_uploads = @ini_get('file_uploads');
	if (!empty($file_uploads) && $file_uploads !== 0 && strtolower($file_uploads) != 'off' && $auth->acl_get('a_restore'))
	{
		$module['DB']['DB_RESTORE'] = $filename . "$SID&amp;mode=restore";
	}

	return;
}

define('IN_PHPBB', 1);
// Load default header
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);
require_once($phpbb_root_path . 'includes/functions_admin.'.$phpEx);

$mode = (isset($_GET['mode'])) ? $_GET['mode'] : '';

// Increase maximum execution time, but don't complain about it if it isn't
// allowed.
@set_time_limit(1200);

// Begin program proper
switch($mode)
{
	case 'backup':
		if (!$auth->acl_get('a_backup'))
		{
			trigger_error($user->lang['NO_ADMIN']);
		}

		if (SQL_LAYER == 'oracle' || SQL_LAYER == 'odbc' || SQL_LAYER == 'mssql')
		{
			switch (SQL_LAYER)
			{
				case 'oracle':
					$db_type = 'Oracle';
					break;
				case 'odbc':
					$db_type = 'ODBC';
					break;
				case 'mssql':
					$db_type = 'MSSQL';
					break;
			}

			trigger_error($user->lang['Backups_not_supported']);
			break;
		}

		$additional_tables = (isset($_POST['tables'])) ? $_POST['tables'] : ((isset($_GET['tables'])) ? $_GET['tables'] : '');
		$backup_type = (isset($_POST['type'])) ? $_POST['type'] : ((isset($_GET['type'])) ? $_GET['type'] : '');
		$search = (!empty($_POST['search'])) ? intval($_POST['search']) : ((!empty($_GET['search'])) ? intval($_GET['search']) : 0);
		$store_path = (isset($_POST['store'])) ? $_POST['store'] : ((isset($_GET['store'])) ? $_GET['store'] : '');
		$compress = (!empty($_POST['compress'])) ? $_POST['compress'] : ((!empty($_GET['compress'])) ? $_GET['compress'] : 'none');

		if (!isset($_POST['backupstart']) && !isset($_GET['backupstart']))
		{
			page_header($user->lang['DB_BACKUP']);

?>

<h1><?php echo $user->lang['DB_BACKUP']; ?></h1>

<p><?php echo $user->lang['Backup_explain']; ?></p>

<form method="post" action="<?php echo "admin_database.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['Backup_options']; ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Backup_type']; ?>: </td>
		<td class="row2"><input type="radio" name="type" value="full"  checked="checked" /> <?php echo $user->lang['Full_backup']; ?>&nbsp;&nbsp;<input type="radio" name="type" value="structure" /> <?php echo $user->lang['Structure_only']; ?>&nbsp;&nbsp;<input type="radio" name="type" value="data" /> <?php echo $user->lang['Data_only']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Include_search_index']; ?>: <br /><span class="gensmall"><?php echo $user->lang['Include_search_index_explain']; ?></span></td>
		<td class="row2"><input type="radio" name="search" value="0" /> <?php echo $user->lang['NO']; ?>&nbsp;&nbsp;<input type="radio" name="search" value="1" checked="checked" /> <?php echo $user->lang['YES']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Additional_tables']; ?>: <br /><span class="gensmall"><?php echo $user->lang['Additional_tables_explain']; ?></span></td>
		<td class="row2"><input type="text" name="tables" size="40" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Store_local']; ?>: <br /><span class="gensmall"><?php echo $user->lang['Store_local_explain']; ?></span></td>
		<td class="row2"><input type="text" name="store" size="40" /></td>
	</tr>
<?php

			if (extension_loaded('zlib') || extension_loaded('bz2'))
			{

?>
	<tr>
		<td class="row1"><?php echo $user->lang['Compress_file']; ?>: </td>
		<td class="row2"><input type="radio" name="compress" value="none" checked="checked" /> <?php echo $user->lang['NONE']; ?><?php

				if (extension_loaded('zlib'))
				{


?>&nbsp;&nbsp;<input type="radio" name="compress" value="gzip" />.gz&nbsp;&nbsp;<input type="radio" name="compress" value="zip" />.zip<?php

				}

				if (extension_loaded('bz2'))
				{

?>&nbsp;&nbsp;<input type="radio" name="compress" value="bzip" />.bz2<?php

				}

?></td>
	</tr>
<?php

			}

?>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" name="backupstart" value="<?php echo $user->lang['Start_backup']; ?>" class="mainoption" /></td>
	</tr>
</table></form>

<?php

			break;
		}
		else if (!isset($_POST['startdownload']) && !isset($_GET['startdownload']))
		{
			$meta = "<meta http-equiv=\"refresh\" content=\"0;url=admin_database.$phpEx?mode=backup&amp;type=$backup_type&amp;tables=" . quotemeta($additional_tables) . "&amp;search=$search&amp;store=" . quotemeta($store_path) . "&amp;compress=$compress&amp;backupstart=1&amp;startdownload=1\">";

			$message = (empty($store_path)) ? $user->lang['Backup_download'] : $user->lang['Backup_writing'];

			page_header($user->lang['DB_Backup'], $meta);
			page_message($user->lang['DB_Backup'], $message);
			page_footer();
		}

		$tables = (SQL_LAYER != 'postgresql') ? mysql_get_tables() : pg_get_tables();
		@sort($tables);

		if (!empty($additional_tables))
		{
			$additional_tables = explode(',', $additional_tables);

			for($i = 0; $i < count($additional_tables); $i++)
			{
				$tables[] = trim($additional_tables[$i]);
			}
			unset($additional_tables);
		}

		//
		// Enable output buffering
		//
		@ob_start();
		@ob_implicit_flush(0);

		//
		// Build the sql script file...
		//
		echo "#\n";
		echo "# phpBB Backup Script\n";
		echo "# Dump of tables for $dbname\n";
		echo "#\n# DATE : " .  gmdate("d-m-Y H:i:s", time()) . " GMT\n";
		echo "#\n";

		if (SQL_LAYER == 'postgresql')
		{
			 echo "\n" . pg_get_sequences("\n", $backup_type);
		}

		for($i = 0; $i < count($tables); $i++)
		{
			$table_name = $tables[$i];

			if (SQL_LAYER != 'mysql4')
			{
				$table_def_function = "get_table_def_" . SQL_LAYER;
				$table_content_function = "get_table_content_" . SQL_LAYER;
			}
			else
			{
				$table_def_function = "get_table_def_mysql";
				$table_content_function = "get_table_content_mysql";
			}

			if ($backup_type != 'data')
			{
				echo "#\n# TABLE: " . $table_name . "\n#\n";
				echo $table_def_function($table_name, "\n") . "\n";
			}

			if ($backup_type != 'structure')
			{
				//
				// Skip search table data?
				//
				if ($search || (!$search && !preg_match('/search_word/', $table_name)))
				{
					$table_content_function($table_name, "output_table_content");
				}
			}
		}

		//
		// Flush the buffer, send the file
		//
		switch ($compress)
		{
			case 'gzip':
				$extension = 'sql.gz';
				$contents = gzencode(ob_get_contents());
				ob_end_clean();
				break;

			case 'zip':
				$extension = 'zip';
				$zip = new zipfile;
				$zip->add_file(ob_get_contents(), "phpbb_db_backup.sql", time());
				ob_end_clean();
				$contents = $zip->file();
				break;

			case 'bzip':
				$extension = 'bz2';
				$contents = bzcompress(ob_get_contents());
				ob_end_clean();
				break;

			default:
				$extension = 'sql';
				$contents = ob_get_contents();
				ob_end_clean();
		}

		add_admin_log('log_db_backup');

		if (empty($store_path))
		{
			header("Pragma: no-cache");
			header("Content-Type: text/x-delimtext; name=\"phpbb_db_backup.$extension\"");
			header("Content-disposition: attachment; filename=phpbb_db_backup.$extension");

			echo $contents;
			unset($contents);
		}
		else
		{
			if (!($fp = fopen('./../' . $store_path . "/phpbb_db_backup.$extension", 'wb')))
			{
				message_die(ERROR, 'Could not open backup file');
			}

			if (!fwrite($fp, $contents))
			{
				message_die(ERROR, 'Could not write backup file content');
			}

			fclose($fp);
			unset($contents);

			trigger_error($user->lang['Backup_success']);
		}

		exit;
		break;

	case 'restore':
		if (!$auth->acl_get('a_restore'))
		{
			trigger_error($user->lang['No_admin']);
		}

		if (isset($_POST['restorestart']))
		{
			//
			// Handle the file upload ....
			// If no file was uploaded report an error...
			//
			if (!empty($_POST['local']))
			{
				$file_tmpname = './../' . str_replace('\\\\', '/', $_POST['local']);
				$filename = substr($file_tmpname, strrpos($file_tmpname, '/'));
			}
			else
			{
				$filename = (!empty($HTTP_POST_FILES['backup_file']['name'])) ? $HTTP_POST_FILES['backup_file']['name'] : '';
				$file_tmpname = ($HTTP_POST_FILES['backup_file']['tmp_name'] != 'none') ? $HTTP_POST_FILES['backup_file']['tmp_name'] : '';
			}

			if ($file_tmpname == '' || $filename == '' || !file_exists($file_tmpname))
			{
				trigger_error($user->lang['Restore_Error_no_file']);
			}

			$ext = substr($filename, strrpos($filename, '.') + 1);

			if (!preg_match('/^(sql|gz|bz2)$/', $ext))
			{
				trigger_error($user->lang['Restore_Error_filename']);
			}

			if ((!extension_loaded('zlib') && $ext == 'gz') || (!extension_loaded('zip') && $ext == 'zip') || ($ext == 'bz2' && !extension_loaded('bz2')))
			{
				trigger_error($user->lang['Compress_unsupported']);
			}

			$sql_query = '';
			switch ($ext)
			{
				case 'gz':
					$fp = gzopen($file_tmpname, 'rb');
					while (!gzeof($fp))
					{
						$sql_query .= gzgets($fp, 100000);
					}
					gzclose($fp);
					break;

				case 'bz2':
					$sql_query = bzdecompress(fread(fopen($file_tmpname, 'rb'), filesize($file_tmpname)));
					break;

				case 'zip':


				default;
					$sql_query = fread(fopen($file_tmpname, 'r'), filesize($file_tmpname));
			}

			if ($sql_query != '')
			{
				// Strip out sql comments...
				$sql_query = remove_remarks($sql_query);
				$pieces = split_sql_file($sql_query, ';');

				$sql_count = count($pieces);
				for($i = 0; $i < $sql_count; $i++)
				{
					$sql = trim($pieces[$i]);

					if (!empty($sql) && $sql[0] != '#')
					{
						$db->sql_query($sql);
					}
				}
			}

			add_admin_log('log_db_restore');

			trigger_error($user->lang['Restore_success']);
		}

		//
		// Restore page
		//
		page_header($user->lang['DB_RESTORE']);

?>

<h1><?php echo $user->lang['DB_RESTORE']; ?></h1>

<p><?php echo $user->lang['Restore_explain']; ?></p>

<form enctype="multipart/form-data" method="post" action="<?php echo "admin_database.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
		<th colspan="2"><?php echo $user->lang['Select_file']; ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Upload_file']; ?>: <br /><span class="gensmall"><?php

		echo $user->lang['Supported_extensions'];

		$types = ': <u>sql</u>';
		if (extension_loaded('zlib'))
		{
			$types .= ', <u>sql.gz</u>';
		}
		if (extension_loaded('bz2'))
		{
			$types .= ', <u>bz2</u>';
		}

		echo $types;

?></span></td>
		<td class="row2"><input type="file" name="backup_file" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Local_backup_file']; ?>: <br /><span class="gensmall"><?php echo $user->lang['Local_backup_file_explain']; ?></span></td>
		<td class="row2"><input type="text" name="local" size="40" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" name="restorestart" value="<?php echo $user->lang['Start_Restore']; ?>" class="mainoption" /></td>
	</tr>
</table></form>

<?php

		break;

	default:
		trigger_error($user->lang['No_admin']);
		exit;

}

page_footer();

// -----------------------------------------------
// Begin Functions
//

//
// Table defns (not from phpMyAdmin)
//
function mysql_get_tables()
{
	global $db, $table_prefix;

	$tables = array();

	$result = mysql_list_tables($db->dbname, $db->db_connect_id);
	if ($row = $db->sql_fetchrow($result))
	{
		do
		{
			if (preg_match('/^' . $table_prefix . '/', $row[0]))
			{
				$tables[] = $row[0];
			}
		}
		while ($row = $db->sql_fetchrow($result));
	}

	return $tables;
}

//
// The following functions are adapted from phpMyAdmin and upgrade_20.php
//
// This function is used for grabbing the sequences for postgres...
//
function pg_get_sequences($crlf, $backup_type)
{
	global $db;

	$get_seq_sql = "SELECT relname FROM pg_class WHERE NOT relname ~ 'pg_.*'
		AND relkind = 'S' ORDER BY relname";

	$seq = $db->sql_query($get_seq_sql);

	if (!$num_seq = $db->sql_numrows($seq))
	{

		$return_val = "# No Sequences Found $crlf";

	}
	else
	{
		$return_val = "# Sequences $crlf";
		$i_seq = 0;

		while($i_seq < $num_seq)
		{
			$row = $db->sql_fetchrow($seq);
			$sequence = $row['relname'];

			$get_props_sql = "SELECT * FROM $sequence";
			$seq_props = $db->sql_query($get_props_sql);

			if ($db->sql_numrows($seq_props) > 0)
			{
				$row1 = $db->sql_fetchrow($seq_props);

				if ($backup_type == 'structure')
				{
					$row['last_value'] = 1;
				}

				$return_val .= "CREATE SEQUENCE $sequence start " . $row['last_value'] . ' increment ' . $row['increment_by'] . ' maxvalue ' . $row['max_value'] . ' minvalue ' . $row['min_value'] . ' cache ' . $row['cache_value'] . "; $crlf";

			}  // End if numrows > 0

			if (($row['last_value'] > 1) && ($backup_type != 'structure'))
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
function get_table_def_postgresql($table, $crlf)
{
	global $db;

	$schema_create = "";
	//
	// Get a listing of the fields, with their associated types, etc.
	//

	$field_query = "SELECT a.attnum, a.attname AS field, t.typname as type, a.attlen AS length, a.atttypmod as lengthvar, a.attnotnull as notnull
		FROM pg_class c, pg_attribute a, pg_type t
		WHERE c.relname = '$table'
			AND a.attnum > 0
			AND a.attrelid = c.oid
			AND a.atttypid = t.oid
		ORDER BY a.attnum";
	$result = $db->sql_query($field_query);

	if (!$result)
	{
		message_die(GENERAL_ERROR, "Failed in get_table_def (show fields)", "", __LINE__, __FILE__, $field_query);
	} // end if..

	$schema_create .= "DROP TABLE $table;$crlf";

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

		$schema_create .= ",$crlf";

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

	if (!$result)
	{
		message_die(GENERAL_ERROR, "Failed in get_table_def (show fields)", "", __LINE__, __FILE__, $sql_pri_keys);
	}

	while ($row = $db->sql_fetchrow($result))
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
			$props['column_names'] = ereg_replace(", $", "" , $props['column_names']);
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
	global $db;

	$schema_create = "";
	$field_query = "SHOW FIELDS FROM $table";
	$key_query = "SHOW KEYS FROM $table";

	// If the user has selected to drop existing tables when doing a restore.
	// Then we add the statement to drop the tables....
	$schema_create .= "DROP TABLE IF EXISTS $table;$crlf";
	$schema_create .= "CREATE TABLE $table($crlf";

	// Ok lets grab the fields...
	$result = $db->sql_query($field_query);

	while ($row = $db->sql_fetchrow($result))
	{
		$schema_create .= '	' . $row['Field'] . ' ' . $row['Type'];

		if (!empty($row['Default']))
		{
			$schema_create .= ' DEFAULT \'' . $row['Default'] . '\'';
		}

		if ($row['Null'] != "YES")
		{
			$schema_create .= ' NOT NULL';
		}

		if ($row['Extra'] != "")
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

	while($row = $db->sql_fetchrow($result))
	{
		$kname = $row['Key_name'];

		if (($kname != 'PRIMARY') && ($row['Non_unique'] == 0))
		{
			$kname = "UNIQUE|$kname";
		}

		if (!is_array($index[$kname]))
		{
			$index[$kname] = array();
		}

		$index[$kname][] = $row['Column_name'];
	}

	foreach ($index as $x => $columns)
	{
		$schema_create .= ", $crlf";

		if ($x == 'PRIMARY')
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

	if (get_magic_quotes_runtime())
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
function get_table_content_postgresql($table, $handler)
{
	global $db;

	// Grab all of the data from current table.
	$result = $db->sql_query("SELECT * FROM $table");

	$i_num_fields = $db->sql_numfields($result);

	for ($i = 0; $i < $i_num_fields; $i++)
	{
		$aryType[] = $db->sql_fieldtype($i, $result);
		$aryName[] = $db->sql_fieldname($i, $result);
	}

	$iRec = 0;

	while ($row = $db->sql_fetchrow($result))
	{
		unset($schema_vals);
		unset($schema_fields);
		unset($schema_insert);

		// Build the SQL statement to recreate the data.
		for($i = 0; $i < $i_num_fields; $i++)
		{
			$strVal = $row[$aryName[$i]];
			if (preg_match('#char|text|bool#i', $aryType[$i]))
			{
				$strQuote = "'";
				$strEmpty = "";
				$strVal = addslashes($strVal);
			}
			elseif (preg_match('#date|timestamp#i', $aryType[$i]))
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

		$schema_vals = preg_replace('#,$#', '', $schema_vals);
		$schema_vals = preg_replace('#^ #', '', $schema_vals);
		$schema_fields = preg_replace('#,$#', '', $schema_fields);
		$schema_fields = preg_replace('#^ #', '', $schema_fields);

		// Take the ordered fields and their associated data and build it
		// into a valid sql statement to recreate that field in the data.
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

	// Grab the data from the table.
	$result = $db->sql_query("SELECT * FROM $table");

	// Loop through the resulting rows and build the sql statement.
	$schema_insert = "";
	if ($row = $db->sql_fetchrow($result))
	{
		$schema_insert = "\n#\n# Table Data for $table\n#\n";

		$handler($schema_insert);

		do
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
			$table_list = preg_replace('#, $#', '', $table_list);
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
				if (!isset($row[$j]))
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
			$schema_insert = preg_replace('#,$#', '', $schema_insert);
			$schema_insert .= ');';
			//
			// Go ahead and send the insert statement to the handler function.
			//
			$handler(trim($schema_insert));
		}
		while ($row = $db->sql_fetchrow($result));
	}

	return true;
}

function output_table_content($content)
{
	global $tempfile;

	//fwrite($tempfile, $content . "\n");
	//$backup_sql .= $content . "\n";
	echo $content ."\n";
	return;
}


//
// Zip creation class from phpMyAdmin 2.3.0 (c) Tobias Ratschiller, Olivier Müller, Loïc Chapeaux, Marc Delisle
// http://www.phpmyadmin.net/
//
// Based on work by Eric Mueller and Denis125
// Official ZIP file format: http://www.pkware.com/appnote.txt
//
class zipfile
{
	var $datasec      = array();
	var $ctrl_dir     = array();
	var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
	var $old_offset   = 0;

	function unix_to_dos_time($unixtime = 0)
	{
		$timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);

		if ($timearray['year'] < 1980)
		{
			$timearray['year']    = 1980;
			$timearray['mon']     = 1;
			$timearray['mday']    = 1;
			$timearray['hours']   = 0;
			$timearray['minutes'] = 0;
			$timearray['seconds'] = 0;
		}

		return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
				($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
	}

	function add_file($data, $name, $time = 0)
	{
		$name     = str_replace('\\', '/', $name);

		$dtime    = dechex($this->unix_to_dos_time($time));
		$hexdtime = '\x' . $dtime[6] . $dtime[7]
				  . '\x' . $dtime[4] . $dtime[5]
				  . '\x' . $dtime[2] . $dtime[3]
				  . '\x' . $dtime[0] . $dtime[1];
		eval('$hexdtime = "' . $hexdtime . '";');

		$fr   = "\x50\x4b\x03\x04";
		$fr   .= "\x14\x00";            // ver needed to extract
		$fr   .= "\x00\x00";            // gen purpose bit flag
		$fr   .= "\x08\x00";            // compression method
		$fr   .= $hexdtime;             // last mod time and date

		$unc_len = strlen($data);
		$crc     = crc32($data);
		$zdata   = gzcompress($data);
		$zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
		$c_len   = strlen($zdata);
		$fr      .= pack('V', $crc);             // crc32
		$fr      .= pack('V', $c_len);           // compressed filesize
		$fr      .= pack('V', $unc_len);         // uncompressed filesize
		$fr      .= pack('v', strlen($name));    // length of filename
		$fr      .= pack('v', 0);                // extra field length
		$fr      .= $name;

		// "file data" segment
		$fr .= $zdata;

		// "data descriptor" segment (optional but necessary if archive is not
		// served as file)
		$fr .= pack('V', $crc);                 // crc32
		$fr .= pack('V', $c_len);               // compressed filesize
		$fr .= pack('V', $unc_len);             // uncompressed filesize

		// add this entry to array
		$this -> datasec[] = $fr;
		$new_offset        = strlen(implode('', $this->datasec));

		// now add to central directory record
		$cdrec = "\x50\x4b\x01\x02";
		$cdrec .= "\x00\x00";                // version made by
		$cdrec .= "\x14\x00";                // version needed to extract
		$cdrec .= "\x00\x00";                // gen purpose bit flag
		$cdrec .= "\x08\x00";                // compression method
		$cdrec .= $hexdtime;                 // last mod time & date
		$cdrec .= pack('V', $crc);           // crc32
		$cdrec .= pack('V', $c_len);         // compressed filesize
		$cdrec .= pack('V', $unc_len);       // uncompressed filesize
		$cdrec .= pack('v', strlen($name)); // length of filename
		$cdrec .= pack('v', 0);             // extra field length
		$cdrec .= pack('v', 0);             // file comment length
		$cdrec .= pack('v', 0);             // disk number start
		$cdrec .= pack('v', 0);             // internal file attributes
		$cdrec .= pack('V', 32);            // external file attributes - 'archive' bit set

		$cdrec .= pack('V', $this -> old_offset); // relative offset of local header
		$this -> old_offset = $new_offset;

		$cdrec .= $name;

		// optional extra field, file comment goes here
		// save to central directory
		$this -> ctrl_dir[] = $cdrec;
	}

	function file()
	{
		$data    = implode('', $this -> datasec);
		$ctrldir = implode('', $this -> ctrl_dir);

		return $data . $ctrldir . $this -> eof_ctrl_dir .
			pack('v', sizeof($this -> ctrl_dir)) .  // total # of entries "on this disk"
			pack('v', sizeof($this -> ctrl_dir)) .  // total # of entries overall
			pack('V', strlen($ctrldir)) .           // size of central dir
			pack('V', strlen($data)) .              // offset to start of central dir
			"\x00\x00";                             // .zip file comment length
	}
}

//
// End Functions
// -----------------------------------------------

?>