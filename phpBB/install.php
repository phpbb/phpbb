<?php
/***************************************************************************
 *                                 install.php
 *                            -------------------
 *   begin                : Tuesday, Sept 11, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : supportphpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

$phpbb_root_path='./';
include($phpbb_root_path.'extension.inc');

$userdata = array();
$lang = array();
$reinstall = false;

if( !get_magic_quotes_gpc() )
{
	if( is_array($HTTP_GET_VARS) )
	{
		while( list($k, $v) = each($HTTP_GET_VARS) )
		{
			if( is_array($HTTP_GET_VARS[$k]) )
			{
				while( list($k2, $v2) = each($HTTP_GET_VARS[$k]) )
				{
					$HTTP_GET_VARS[$k][$k2] = addslashes($v2);
				}
				@reset($HTTP_GET_VARS[$k]);
			}
			else
			{
				$HTTP_GET_VARS[$k] = addslashes($v);
			}
		}
		@reset($HTTP_GET_VARS);
	}

	if( is_array($HTTP_POST_VARS) )
	{
		while( list($k, $v) = each($HTTP_POST_VARS) )
		{
			if( is_array($HTTP_POST_VARS[$k]) )
			{
				while( list($k2, $v2) = each($HTTP_POST_VARS[$k]) )
				{
					$HTTP_POST_VARS[$k][$k2] = addslashes($v2);
				}
				@reset($HTTP_POST_VARS[$k]);
			}
			else
			{
				$HTTP_POST_VARS[$k] = addslashes($v);
			}
		}
		@reset($HTTP_POST_VARS);
	}

	if( is_array($HTTP_COOKIE_VARS) )
	{
		while( list($k, $v) = each($HTTP_COOKIE_VARS) )
		{
			if( is_array($HTTP_COOKIE_VARS[$k]) )
			{
				while( list($k2, $v2) = each($HTTP_COOKIE_VARS[$k]) )
				{
					$HTTP_COOKIE_VARS[$k][$k2] = addslashes($v2);
				}
				@reset($HTTP_COOKIE_VARS[$k]);
			}
			else
			{
				$HTTP_COOKIE_VARS[$k] = addslashes($v);
			}
		}
		@reset($HTTP_COOKIE_VARS);
	}
}

/***************************************************************************
 *								Install Customization Section
 *
 *		This section can be modified to set up some basic default information
 * 	used by the install script.  Specifically the default theme data
 *		and the default template.
 *
 **************************************************************************/

$default_language = 'english';
$default_template = 'subSilver';

$available_dbms = array(
	"mysql" => array(
		"LABEL" => "MySQL 3.x",
		"SCHEMA" => "mysql", 
		"DELIM" => ";",
		"DELIM_BASIC" => ";",
		"COMMENTS" => "remove_remarks"
	), 
	"mysql4" => array(
		"LABEL" => "MySQL 4.x",
		"SCHEMA" => "mysql", 
		"DELIM" => ";", 
		"DELIM_BASIC" => ";",
		"COMMENTS" => "remove_remarks"
	), 
	"postgres" => array(
		"LABEL" => "PostgreSQL 7.x",
		"SCHEMA" => "postgres", 
		"DELIM" => ";", 
		"DELIM_BASIC" => ";",
		"COMMENTS" => "remove_comments"
	), 
	"mssql" => array(
		"LABEL" => "MS SQL Server 7/2000",
		"SCHEMA" => "mssql", 
		"DELIM" => "GO", 
		"DELIM_BASIC" => ";",
		"COMMENTS" => "remove_comments"
	),
	"msaccess" => array(
		"LABEL" => "MS Access [ ODBC ]",
		"SCHEMA" => "", 
		"DELIM" => "", 
		"DELIM_BASIC" => ";",
		"COMMENTS" => ""
	),
	"mssql-odbc" =>	array(
		"LABEL" => "MS SQL Server [ ODBC ]",
		"SCHEMA" => "mssql", 
		"DELIM" => "GO",
		"DELIM_BASIC" => ";",
		"COMMENTS" => "remove_comments"
	)
);

//
// drop table schema
//
$sql_array = array();

$sql_array['drop_schema'][] = "DROP TABLE phpbb_auth_access";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_banlist";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_categories";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_config";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_disallow";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_forum_prune";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_forums";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_groups";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_posts";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_posts_text";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_privmsgs";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_privmsgs_text";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_ranks";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_search_results";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_search_wordlist";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_search_wordmatch";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_sessions";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_smilies";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_themes";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_themes_name";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_topics";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_topics_watch";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_user_group";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_users";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_vote_desc";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_vote_results";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_vote_voters";
$sql_array['drop_schema'][] = "DROP TABLE phpbb_words";

//
// Uncomment the following line to completely disable the ftp option...
//
// define('NO_FTP', true);

/***************************************************************************
*		
*						End Install Customization Section
*
***************************************************************************/

//
// Obtain various vars
//
$confirm = ( isset($HTTP_POST_VARS['confirm']) ) ? true : false;
$cancel = ( isset($HTTP_POST_VARS['cancel']) ) ? true : false;

if( isset($HTTP_POST_VARS['install_step']) || isset($HTTP_GET_VARS['install_step']) )
{
	$install_step = ( isset($HTTP_POST_VARS['install_step']) ) ? $HTTP_POST_VARS['install_step'] : $HTTP_GET_VARS['install_step'];
}
else
{
	$install_step = "";
}

$upgrade = ( !empty($HTTP_POST_VARS['upgrade']) ) ? $HTTP_POST_VARS['upgrade']: '';
$dbms = isset($HTTP_POST_VARS['dbms']) ? $HTTP_POST_VARS['dbms'] : "";
$language = ( !empty($HTTP_POST_VARS['language']) ) ? $HTTP_POST_VARS['language'] : $default_language;

$dbhost = ( !empty($HTTP_POST_VARS['dbhost']) ) ? $HTTP_POST_VARS['dbhost'] : "";
$dbuser = ( !empty($HTTP_POST_VARS['dbuser']) ) ? $HTTP_POST_VARS['dbuser'] : "";
$dbpasswd = ( !empty($HTTP_POST_VARS['dbpasswd']) ) ? $HTTP_POST_VARS['dbpasswd'] : "";
$dbname = ( !empty($HTTP_POST_VARS['dbname']) ) ? $HTTP_POST_VARS['dbname'] : "";

$table_prefix = ( !empty($HTTP_POST_VARS['prefix']) ) ? $HTTP_POST_VARS['prefix'] : "";

$admin_username = ( !empty($HTTP_POST_VARS['admin_user']) ) ? $HTTP_POST_VARS['admin_user'] : "";
$admin_pass1 = ( !empty($HTTP_POST_VARS['admin_pass1']) ) ? $HTTP_POST_VARS['admin_pass1'] : "";
$admin_pass2 = ( !empty($HTTP_POST_VARS['admin_pass2']) ) ? $HTTP_POST_VARS['admin_pass2'] : "";

$ftp_path = ( !empty($HTTP_POST_VARS['ftp_path']) ) ? $HTTP_POST_VARS['ftp_path'] : "";
$ftp_user = ( !empty($HTTP_POST_VARS['ftp_user']) ) ? $HTTP_POST_VARS['ftp_user'] : "";
$ftp_pass = ( !empty($HTTP_POST_VARS['ftp_pass']) ) ? $HTTP_POST_VARS['ftp_pass'] : "";

if( @file_exists('config.'.$phpEx) )
{
	include('config.'.$phpEx);
}

if( !defined("PHPBB_INSTALLED") )
{
	include($phpbb_root_path.'includes/sql_parse.'.$phpEx);
	include($phpbb_root_path.'includes/constants.'.$phpEx);
	include($phpbb_root_path.'includes/template.'.$phpEx);
	include($phpbb_root_path.'includes/functions.'.$phpEx);
	include($phpbb_root_path.'includes/sessions.'.$phpEx);

	//
	// Import language file, setup template ...
	//
	include($phpbb_root_path.'language/lang_' . $language . '/lang_main.'.$phpEx);
	include($phpbb_root_path.'language/lang_' . $language . '/lang_admin.'.$phpEx);

	$template = new Template($phpbb_root_path . "templates/" . $default_template);

	if( $upgrade == 1 )
	{
		require('upgrade.'.$phpEx);
		$install_step = 1;
	}

	//
	// Load default template for install
	// 
	$template->set_filenames(array(
		"body" => "install.tpl")
	);

	$template->assign_vars(array(
		"L_INSTALLATION" => $lang['Welcome_install'])
	);
}
else
{
	define("IN_ADMIN", 1);

	include($phpbb_root_path.'common.'.$phpEx);
	include($phpbb_root_path.'includes/sql_parse.'.$phpEx);

	//
	// Set page ID for session management
	//
	$userdata = session_pagestart($user_ip, PAGE_INDEX, $session_length);
	init_userprefs($userdata);
	//
	// End session management
	//

	if( $userdata['user_level'] == ADMIN && !$cancel && $dbms != 'msaccess' )
	{
		if( !$confirm )
		{
			//
			// Sorry this has already been installed can't do anything more with it
			//
			include($phpbb_root_path . 'includes/page_header.'.$phpEx);

			$template->set_filenames(array(
				"confirm" => "confirm_body.tpl")
			);

			$template->assign_vars(array(
				"MESSAGE_TITLE" => $lang['Admin_config'],
				"MESSAGE_TEXT" => $lang['Re_install'],

				"L_YES" => $lang['Yes'],
				"L_NO" => $lang['No'],

				"S_CONFIRM_ACTION" => append_sid("install.$phpEx"),
				"S_HIDDEN_FIELDS" => $hidden_fields)
			);

			$template->pparse("confirm");

			include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
		}

		include($phpbb_root_path.'language/lang_' . $language . '/lang_main.'.$phpEx);
		include($phpbb_root_path.'language/lang_' . $language . '/lang_admin.'.$phpEx);

		$template = new Template($phpbb_root_path . "templates/" . $default_template);

		$template->set_filenames(array(
			"body" => "install.tpl")
		);

		$template->assign_vars(array(
			"L_INSTALLATION" => $lang['Welcome_install'])
		);

		$reinstall = true;
	}
	else
	{
		header("HTTP/1.0 302 Redirect");
		header("Location: " . append_sid("index.$phpEx", true));
	}
}

//
//
//
if( !empty($HTTP_POST_VARS['send_file']) && $HTTP_POST_VARS['send_file'] == 1  && !defined("PHPBB_INSTALLED") )
{
	header("Content-Type: text/x-delimtext; name=\"config.php\"");
	header("Content-disposition: attachment; filename=config.php");

	//
	// We need to stripslashes no matter what the setting of magic_quotes_gpc is
	// because we add slahes at the top if its off, and they are added automaticlly 
	// if it is on.
	//
	$HTTP_POST_VARS['config_data'] = stripslashes($HTTP_POST_VARS['config_data']);

	echo $HTTP_POST_VARS['config_data'];

	exit;
}
else if( !empty($HTTP_POST_VARS['send_file']) && $HTTP_POST_VARS['send_file'] == 2 && !defined("PHPBB_INSTALLED")  )
{
	//
	// Ok we couldn't write the config file so let's try ftping it.
	//

	$HTTP_POST_VARS['config_data'] = stripslashes($HTTP_POST_VARS['config_data']);

	$s_hidden_fields = '<input type="hidden" name="config_data" value="' . htmlspecialchars($HTTP_POST_VARS['config_data']) . '" />';
	$s_hidden_fields .= '<input type="hidden" name="ftp_file" value="1" />';

	$template->assign_block_vars("switch_ftp_file", array());
	$template->assign_block_vars("switch_common_install", array());

	$template->assign_vars(array(
		"L_INSTRUCTION_TEXT" => $lang['ftp_instructs'],
		"L_FTP_INFO" => $lang['ftp_info'],
		"L_FTP_PATH" => $lang['ftp_path'],
		"L_FTP_PASS" => $lang['ftp_password'],
		"L_FTP_USER" => $lang['ftp_username'],
		"L_SUBMIT" => $lang['Transfer_config'],

		"S_HIDDEN_FIELDS" => $s_hidden_fields, 
		"S_FORM_ACTION" => "install.$phpEx")
	);

	$template->pparse("body");

	exit;

}
else if( !empty($HTTP_POST_VARS['ftp_file']) && !defined("PHPBB_INSTALLED")  )
{
	//
	// Here we'll actually send the file...
	//
	$HTTP_POST_VARS['config_data'] = stripslashes($HTTP_POST_VARS['config_data']);
	
	$conn_id = @ftp_connect('localhost');
	$login_result = @ftp_login($conn_id, "$ftp_user", "$ftp_pass");

	if( !$conn_id || !$login_result )
	{
		//
		// Error couldn't get connected... Go back to option to send file...
		//
		$s_hidden_fields = '<input type="hidden" name="config_data" value="' . htmlspecialchars($config_data) . '" />';
		$s_hidden_fields .= '<input type="hidden" name="send_file" value="1" />';

		$template->assign_block_vars("switch_common_install", array());

		$template->assign_vars(array(
			"L_INSTRUCTION_TEXT" => $lang['NoFTP_config'],
			"L_SUBMIT" => $lang['Download_config'],

			"S_HIDDEN_FIELDS" => $s_hidden_fields, 
			"S_FORM_ACTION" => "install.$phpEx")
		);

		$template->pparse('body');

		exit;
	}
	else
	{
		//
		// Write out a temp file...
		//
		$tmpfname = @tempnam('/tmp', 'cfg');

		@unlink($tmpfname); // unlink for safety on php4.0.3+

		$fp = @fopen($tmpfname, 'w');

		@fwrite($fp, $HTTP_POST_VARS['config_data']);

		@fclose($fp);

		//
		// Now ftp it across.
		//
		@ftp_chdir($conn_id, $ftp_dir);

		$res = ftp_put($conn_id, 'config.php', $tmpfname, FTP_ASCII);

		@ftp_quit($conn_id);

		unlink($tmpfname);
		
		//
		// Ok we are basically done with the install process let's go on 
		// and let the user configure their board now.
		//
		// We are going to do this by calling the admin_board.php from the
		// normal board admin section.
		//
		$s_hidden_fields = '<input type="hidden" name="username" value="' . $admin_name . '" />';
		$s_hidden_fields .= '<input type="hidden" name="password" value="' . $admin_pass1 . '" />';
		$s_hidden_fields .= '<input type="hidden" name="redirect" value="admin/" />';
		$s_hidden_fields .= '<input type="hidden" name="submit" value="' . $lang['Login'] . '" />';

		$template->assign_block_vars("switch_common_install", array());

		$template->assign_vars(array(
			"L_INSTRUCTION_TEXT" => $lang['Inst_Step_2'],
			"L_SUBMIT" => $lang['Finish_Install'],

			"S_HIDDEN_FIELDS" => $s_hidden_fields, 
			"S_FORM_ACTION" => "login.$phpEx")
		);
		
		$template->pparse('body');

		exit();
	}
}
else if( ( empty($install_step) || $admin_pass1 != $admin_pass2 || $dbhost == "" )  && !defined("PHPBB_INSTALLED") )
{
	//
	// Ok we haven't installed before so lets work our way through the various
	// steps of the install process.  This could turn out to be quite a lengty 
	// process.
	//

	//
	// Step 0 gather the pertinant info for database setup...
	// Namely dbms, dbhost, dbname, dbuser, and dbpasswd.
	//
	$instruction_text = $lang['Inst_Step_0'];

	if( $HTTP_POST_VARS['admin_pass1'] != $HTTP_POST_VARS['admin_pass2'] )
	{
		$instruction_text = $lang['Password_mismatch'] . '<br />' . $instruction_text;
	}

	$lang_options = language_select($language, 'language');

	$dbms_options = '<select name="dbms">';
	while( list($dbms_name, $details) = @each($available_dbms) )
	{
		$selected = ( $dbms_name == $dbms ) ? "selected=\"selected\"" : "";
		$dbms_options .= '<option value="' . $dbms_name . '">' . $details['LABEL'] . '</option>';
	}
	$dbms_options .= '</select>';

	$upgrade_option = '<select name="upgrade"';
	$upgrade_option .= 'onchange="if( this.options[this.selectedIndex].value == 1 ) { document.install_form.dbms.selectedIndex=0; document.install_form.dbms.disabled=1; } else { document.install_form.dbms.disabled=0; }">';
	$upgrade_option .= '<option value="0">' . $lang['Install'] . '</option>';
	$upgrade_option .= '<option value="1">' . $lang['Upgrade'] . '</option></select>';
	
	$s_hidden_fields = '<input type="hidden" name="install_step" value="1" />';

	$template->assign_block_vars("switch_stage_one_install", array());
	$template->assign_block_vars("switch_common_install", array());

	$template->assign_vars(array(
		"L_INSTRUCTION_TEXT" => $instruction_text,
		"L_INITIAL_CONFIGURATION" => $lang['Initial_config'], 
		"L_DATABASE_CONFIGURATION" => $lang['DB_config'], 
		"L_ADMIN_CONFIGURATION" => $lang['Admin_config'], 
		"L_LANGUAGE" => $lang['Default_lang'], 
		"L_DBMS" => $lang['dbms'], 
		"L_DB_HOST" => $lang['DB_Host'], 
		"L_DB_NAME" => $lang['DB_Name'], 
		"L_DB_USER" => $lang['Database'] . ' ' . $lang['Username'], 
		"L_DB_PASSWORD" => $lang['Database'] . ' ' . $lang['Password'], 
		"L_DB_PREFIX" => $lang['Table_Prefix'], 
		"L_UPGRADE" => $lang['Install_Method'],
		"L_ADMIN_USERNAME" => $lang['Administrator'] . ' ' . $lang['Username'], 
		"L_ADMIN_PASSWORD" => $lang['Administrator'] . ' ' . $lang['Password'], 
		"L_ADMIN_CONFIRM_PASSWORD" => $lang['Confirm'] . ' ' . $lang['Password'], 
		"L_SUBMIT" => $lang['Start_Install'], 

		"DB_PREFIX" => ( !empty($table_prefix) ) ? $table_prefix : "phpbb_", 
		"DB_HOST" => ( $dbhost != "" ) ? $dbhost : "", 
		"DB_USER" => ( $dbuser != "" ) ? $dbuser : "", 
		"DB_PASSWD" => ( $dbpasswd != "" ) ? $dbpasswd : "", 
		"ADMIN_USERNAME" => ( $admin_username != "" ) ? $admin_username : "", 

		"S_LANG_SELECT" => $lang_options, 
		"S_DBMS_SELECT" => $dbms_options, 
		"S_HIDDEN_FIELDS" => $s_hidden_fields,
		"S_UPGRADE_SELECT" => $upgrade_option,
		"S_FORM_ACTION" => "install.$phpEx")
	);

	$template->pparse("body");

	exit;
}
else
{
	//
	// Go ahead and create the DB, then populate it
	//
	// MS Access is slightly different in that a pre-built, pre-
	// populated DB is supplied, all we need do here is update
	// the relevant entries
	//
	if( $reinstall )
	{
		$sql_query = preg_replace('/phpbb_/', $table_prefix, $sql_array['drop_schema']);
		$sql_count = count($sql_query);

		for($i = 0; $i < $sql_count; $i++)
		{
			$result = $db->sql_query($sql_query[$i]);
			if( !$result )
			{
				$error = $db->sql_error();

				$template->assign_block_vars("switch_error_install", array());

				$template->assign_vars(array(
					"L_ERROR_TITLE" => $lang['Installer_Error'],
					"L_ERROR" => $lang['Install_db_error'] . '<br /><br />' . $error)
				);

				$template->pparse('body');

				exit;
			}
		}

		$admin_name = $userdata['username'];
		$admin_pass1 = $userdata['user_password'];
		$language = $userdata['user_lang'];
	}
	else if( isset($dbms) ) 
	{
		include($phpbb_root_path.'includes/db.'.$phpEx);
	}

	$dbms_schema = 'db/schemas/' . $available_dbms[$dbms]['SCHEMA'] . '_schema.sql';
	$dbms_basic = 'db/schemas/' . $available_dbms[$dbms]['SCHEMA'] . '_basic.sql';

	$remove_remarks = $available_dbms[$dbms]['COMMENTS'];;
	$delimiter = $available_dbms[$dbms]['DELIM']; 
	$delimiter_basic = $available_dbms[$dbms]['DELIM_BASIC']; 

	if( $install_step == 1 || $reinstall )
	{
		if( $upgrade != 1 )
		{
			if( $dbms != 'msaccess' )
			{
				//
				// Ok we have the db info go ahead and read in the relevant schema
				// and work on building the table.. probably ought to provide some
				// kind of feedback to the user as we are working here in order
				// to let them know we are actually doing something.
				//
				$sql_query = @fread(@fopen($dbms_schema, 'r'), @filesize($dbms_schema));
				$sql_query = preg_replace('/phpbb_/', $table_prefix, $sql_query);

				$sql_query = $remove_remarks($sql_query);
				$sql_query = split_sql_file($sql_query, $delimiter);

				$sql_count = count($sql_query);

				for($i = 0; $i < $sql_count; $i++)
				{
					$result = $db->sql_query($sql_query[$i]);
					if( !$result )
					{
						$error = $db->sql_error();
		
						$template->assign_block_vars("switch_error_install", array());

						$template->assign_vars(array(
							"L_ERROR_TITLE" => $lang['Installer_Error'],
							"L_ERROR" => $lang['Install_db_error'] . '<br />' . $error['message'])
						);

						$template->pparse('body');

						exit;
					}
				}
		
				//
				// Ok tables have been built, let's fill in the basic information
				//
				$sql_query = @fread(@fopen($dbms_basic, 'r'), @filesize($dbms_basic));
				$sql_query = preg_replace('/phpbb_/', $table_prefix, $sql_query);

				$sql_query = $remove_remarks($sql_query);
				$sql_query = split_sql_file($sql_query, $delimiter_basic);

				$sql_count = count($sql_query);

				for($i = 0; $i < $sql_count; $i++)
				{
					$result = $db->sql_query($sql_query[$i]);
					if( !$result )
					{
						$error = $db->sql_error();
		
						$template->assign_block_vars("switch_error_install", array());

						$template->assign_vars(array(
							"L_ERROR_TITLE" => $lang['Installer_Error'],
							"L_ERROR" => $lang['Install_db_error'] . "<br />" . $error["message"])
						);

						$template->pparse('body');

						exit;
					}
				}
			}

			//
			// Ok at this point they have entered their admin password, let's go 
			// ahead and create the admin account with some basic default information
			// that they can customize later, and write out the config file.  After
			// this we are going to pass them over to the admin_forum.php script
			// to set up their forum defaults.
			//
			$error = "";

			//
			// Update the default admin user with their information.
			//
			$sql = "INSERT INTO " . $table_prefix . "config (config_name, config_value) 
				VALUES ('board_startdate', " . time() . ")";
			$result = $db->sql_query($sql);
			if( !$result )
			{
				$error .= "Could not insert board_startdate :: " . $sql . " :: " . __LINE__ . " :: " . __FILE__ . "<br /><br />";
			}

			$sql = "INSERT INTO " . $table_prefix . "config (config_name, config_value) 
				VALUES ('default_lang', '$language')";
			$result = $db->sql_query($sql);
			if( !$result )
			{
				$error .= "Could not insert default_lang :: " . $sql . " :: " . __LINE__ . " :: " . __FILE__ . "<br /><br />";
			}

			$admin_pass_md5 = ( $confirm && $userdata['user_level'] == ADMIN ) ? $admin_pass1 : md5($admin_pass1);

			$sql = "UPDATE " . $table_prefix . "users 
				SET username = '$admin_name', user_password='$admin_pass_md5', user_lang = '" . $language . "' 
				WHERE username = 'Admin'";
			$result = $db->sql_query($sql);
			if( !$result )
			{
				$error .= "Could not update admin info :: " . $sql . " :: " . __LINE__ . " :: " . __FILE__ . "<br /><br />";
			}

			$sql = "UPDATE " . $table_prefix . "users 
				SET user_regdate = " . time();
			$result = $db->sql_query($sql);
			if( !$result )
			{
				$error .= "Could not update user_regdate :: " . $sql . " :: " . __LINE__ . " :: " . __FILE__ . "<br /><br />";
			}

			//
			// Change session table to HEAP if MySQL version matches
			//
			if( preg_match("/^mysql/", $dbms) )
			{
				$sql = "SELECT VERSION() AS mysql_version";
				if($result = $db->sql_query($sql))
				{
					$row = $db->sql_fetchrow($result);
					$version = $row['mysql_version'];

					if( preg_match("/^(3\.23|4\.)/", $version) )
					{
						$sql = "ALTER TABLE " . $table_prefix . "sessions 
							TYPE=HEAP";
						if( !$result = $db->sql_query($sql))
						{
							$error .= "Could not alter session table to HEAP type :: " . $sql . " :: " . __LINE__ . " :: " . __FILE__ . "<br /><br />";
						}
					}
				}
			}

			if( $error != "" )
			{
				$template->assign_block_vars("switch_error_install", array());

				$template->assign_vars(array(
					"L_ERROR_TITLE" => $lang['Installer_Error'],
					"L_ERROR" => $lang['Install_db_error'] . '<br /><br />' . $error)
				);

				$template->pparse('body');

				exit;
			}
		}

		if( !$reinstall )
		{
			$template->assign_block_vars("switch_common_install", array());

			//
			// Write out the config file.
			//
			$config_data = '<?php'."\n\n";
			$config_data .= "//\n// phpBB 2.x auto-generated config file\n// Do not change anything in this file!\n//\n\n";
			$config_data .= '$dbms = "' . $dbms . '";' . "\n\n";
			$config_data .= '$dbhost = "' . $dbhost . '";' . "\n";
			$config_data .= '$dbname = "' . $dbname . '";' . "\n";
			$config_data .= '$dbuser = "' . $dbuser . '";' . "\n";
			$config_data .= '$dbpasswd = "' . $dbpasswd . '";' . "\n\n";
			$config_data .= '$table_prefix = "' . $table_prefix . '";' . "\n\n";
			$config_data .= 'define(\'PHPBB_INSTALLED\', true);'."\n\n";	
			$config_data .= '?' . '>'; // Done this to prevent highlighting editors getting confused!

			@umask(0111);
			$no_open = FALSE;

			$fp = @fopen('config.php', 'w');
			if( !$fp )
			{
				//
				// Unable to open the file writeable do something here as an attempt
				// to get around that...
				//
				$s_hidden_fields = '<input type="hidden" name="config_data" value="' . htmlspecialchars($config_data) . '" />';

				if( extension_loaded('ftp') && !defined('NO_FTP') )
				{
					$template->assign_block_vars('switch_ftp_option', array());

					$lang['Unwriteable_config'] .= '<p>' . $lang['ftp_option'] . '</p>';

					$template->assign_vars(array(
						"L_CHOOSE_FTP" => $lang['ftp_choose'],
						"L_ATTEMPT_FTP" => $lang['Attempt_ftp'],
						"L_SEND_FILE" => $lang['Send_file'])
					);
				}
				else
				{
					$s_hidden_fields .= '<input type="hidden" name="send_file" value="1" />';
				}

				$template->assign_vars(array(
					"L_INSTRUCTION_TEXT" => $lang['Unwriteable_config'],
					"L_SUBMIT" => $lang['Download_config'],

					"S_HIDDEN_FIELDS" => $s_hidden_fields, 
					"S_FORM_ACTION" => "install.$phpEx")
				);

				$template->pparse('body');

				exit;
			}

			$result = @fputs($fp, $config_data, strlen($config_data));

			@fclose($fp);
		}
		else
		{
			$template->assign_block_vars("switch_common_install", array());
		}

		//
		// Ok we are basically done with the install process let's go on 
		// and let the user configure their board now.
		//
		// We are going to do this by calling the admin_board.php from the
		// normal board admin section.
		//
		if( !$reinstall )
		{
			$s_hidden_fields = '<input type="hidden" name="username" value="' . $admin_name . '" />';
			$s_hidden_fields .= '<input type="hidden" name="password" value="' . $admin_pass1 . '" />';
			$s_hidden_fields .= '<input type="hidden" name="redirect" value="admin/" />';
			$s_hidden_fields .= '<input type="hidden" name="login" value="true" />';
		}
		else
		{
			$s_hidden_fields = "";
		}

		$template->assign_vars(array(
			"L_INSTRUCTION_TEXT" => $lang['Inst_Step_2'],
			"L_SUBMIT" => $lang['Finish_Install'],

			"S_HIDDEN_FIELDS" => $s_hidden_fields, 
			"S_FORM_ACTION" => ( $reinstall ) ? append_sid("login.$phpEx") : "login.$phpEx")
		);
		
		$template->pparse('body');

		exit;
	}
}

?>