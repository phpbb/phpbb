<?php
/***************************************************************************
 *                                install.php
 *                            -------------------
 *   begin                : Tuesday, Sept 11, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
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

error_reporting  (E_ERROR | E_WARNING | E_PARSE); // This will NOT report uninitialized variables
set_magic_quotes_runtime(0); // Disable magic_quotes_runtime

if ( !get_magic_quotes_gpc() )
{
	$_GET = slash_input_data($_GET);
	$_POST = slash_input_data($_POST);
}

define('IN_PHPBB', true);
$phpbb_root_path='./../';
require($phpbb_root_path . 'extension.inc');
require($phpbb_root_path . 'includes/functions.'.$phpEx);


/***************************************************************************
 *				Install Customization Section
 *
 *	This section can be modified to set up some basic default information
 * 	used by the install script.  Specifically the default theme data
 *	and the default template.
 *
 **************************************************************************/
$default_language = 'en';

if ( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) )
{
	$accept_lang_ary = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
	foreach ( $accept_lang_ary as $accept_lang )
	{
		// Set correct format ... guess full xx_YY form
		$accept_lang = substr($accept_lang, 0, 2) . '_' . strtoupper(substr($accept_lang, 3, 2));
		if ( file_exists($phpbb_root_path . 'language/' . $accept_lang) )
		{
			$default_language = $accept_lang;
			break;
		}
		else
		{
			// No match on xx_YY so try xx
			$accept_lang = substr($accept_lang, 0, 2);
			if ( file_exists($phpbb_root_path . 'language/' . $accept_lang) )
			{
				$default_language = $accept_lang;
				break;
			}
		}
	}
}

$default_template = 'subSilver';

$available_dbms = array(
	'mysql' => array(
		'LABEL' => 'MySQL 3.x',
		'SCHEMA' => 'mysql',
		'DELIM' => ';',
		'DELIM_BASIC' => ';',
		'COMMENTS' => 'remove_remarks'
	),
	'mysql4' => array(
		'LABEL' => 'MySQL 4.x',
		'SCHEMA' => 'mysql',
		'DELIM' => ';',
		'DELIM_BASIC' => ';',
		'COMMENTS' => 'remove_remarks'
	),
	'postgres' => array(
		'LABEL' => 'PostgreSQL 7.x',
		'SCHEMA' => 'postgres',
		'DELIM' => ';',
		'DELIM_BASIC' => ';',
		'COMMENTS' => 'remove_comments'
	),
	'mssql' => array(
		'LABEL' => 'MS SQL Server 7/2000',
		'SCHEMA' => 'mssql',
		'DELIM' => 'GO',
		'DELIM_BASIC' => ';',
		'COMMENTS' => 'remove_comments'
	),
	'msaccess' => array(
		'LABEL' => 'MS Access [ ODBC ]',
		'SCHEMA' => '',
		'DELIM' => '',
		'DELIM_BASIC' => ';',
		'COMMENTS' => ''
	),
	'mssql-odbc' =>	array(
		'LABEL' => 'MS SQL Server [ ODBC ]',
		'SCHEMA' => 'mssql',
		'DELIM' => 'GO',
		'DELIM_BASIC' => ';',
		'COMMENTS' => 'remove_comments'
	)
);

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
// Try opening config file
//
if ( @file_exists('../config.'.$phpEx) )
{
	include('../config.'.$phpEx);
}

//
// Obtain various vars
//
$confirm = ( isset($_POST['confirm']) ) ? true : false;
$cancel = ( isset($_POST['cancel']) ) ? true : false;

if ( isset($_POST['install_step']) || isset($_GET['install_step']) )
{
	$install_step = ( isset($_POST['install_step']) ) ? $_POST['install_step'] : $_GET['install_step'];
}
else
{
	$install_step = '';
}

$upgrade = ( !empty($_POST['upgrade']) ) ? $_POST['upgrade']: '';
$upgrade_now = ( !empty($_POST['upgrade_now']) ) ? $_POST['upgrade_now']:'';

$dbms = isset($_POST['dbms']) ? $_POST['dbms'] : '';
$language = ( !empty($_POST['language']) ) ? $_POST['language'] : $default_language;

$dbhost = ( !empty($_POST['dbhost']) ) ? $_POST['dbhost'] : '';
$dbuser = ( !empty($_POST['dbuser']) ) ? $_POST['dbuser'] : '';
$dbpasswd = ( !empty($_POST['dbpasswd']) ) ? $_POST['dbpasswd'] : '';
$dbname = ( !empty($_POST['dbname']) ) ? $_POST['dbname'] : '';

$table_prefix = ( !empty($_POST['prefix']) ) ? $_POST['prefix'] : '';

$admin_name = ( !empty($_POST['admin_name']) ) ? $_POST['admin_name'] : '';
$admin_pass1 = ( !empty($_POST['admin_pass1']) ) ? $_POST['admin_pass1'] : '';
$admin_pass2 = ( !empty($_POST['admin_pass2']) ) ? $_POST['admin_pass2'] : '';

$ftp_path = ( !empty($_POST['ftp_path']) ) ? $_POST['ftp_path'] : '';
$ftp_user = ( !empty($_POST['ftp_user']) ) ? $_POST['ftp_user'] : '';
$ftp_pass = ( !empty($_POST['ftp_pass']) ) ? $_POST['ftp_pass'] : '';

$server_name = ( !empty($_POST['server_name']) ) ? $_POST['server_name'] : '';
$server_port = ( !empty($_POST['server_port']) ) ? $_POST['server_port'] : '';
$board_email = ( !empty($_POST['board_email']) ) ? $_POST['board_email'] : '';
$script_path = ( !empty($_POST['script_path']) ) ? $_POST['script_path'] : '';

//
// Do we install/upgrade/update or quit back to index?
//
if ( !defined('PHPBB_INSTALLED') )
{
	include($phpbb_root_path . 'includes/session.'.$phpEx);
	include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);

	// Import language file, setup template ...
	include($phpbb_root_path . 'language/' . $language . '/lang_main.'.$phpEx);
	include($phpbb_root_path . 'language/' . $language . '/lang_admin.'.$phpEx);

	if ( $upgrade == 1 )
	{
		require('upgrade.'.$phpEx);
		$install_step = 1;
	}
}
else
{
	header("Location: ../index.$phpEx");
	exit;
}

//
// What shall we do?
//
if ( !empty($_POST['send_file']) && $_POST['send_file'] == 1  && !defined("PHPBB_INSTALLED") && empty($_POST['upgrade_now']) )
{
	//
	// We need to stripslashes no matter what the setting of magic_quotes_gpc is
	// because we add slahes at the top if its off, and they are added automaticlly
	// if it is on.
	//
	$_POST['config_data'] = stripslashes($_POST['config_data']);

	header("Content-Type: text/x-delimtext; name=\"config.$phpEx\"");
	header("Content-disposition: attachment; filename=config.$phpEx");
	echo $_POST['config_data'];
	exit;

}
else if ( !empty($_POST['send_file']) && $_POST['send_file'] == 2 && !defined("PHPBB_INSTALLED")  )
{
	//
	// Ok we couldn't write the config file so let's try ftping it.
	//

	$_POST['config_data'] = stripslashes($_POST['config_data']);

	$s_hidden_fields = '<input type="hidden" name="config_data" value="' . htmlspecialchars($_POST['config_data']) . '" />';
	$s_hidden_fields .= '<input type="hidden" name="ftp_file" value="1" />';

	if ( $upgrade == 1 )
	{
		$s_hidden_fields .= '<input type="hidden" name="upgrade" value="1" />';
	}

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
else if( !empty($_POST['ftp_file']) && !defined("PHPBB_INSTALLED")  )
{
	//
	// Here we'll actually send the file...
	//
	$_POST['config_data'] = stripslashes($_POST['config_data']);

	$conn_id = @ftp_connect('localhost');
	$login_result = @ftp_login($conn_id, "$ftp_user", "$ftp_pass");

	if ( !$conn_id || !$login_result )
	{
		//
		// Error couldn't get connected... Go back to option to send file...
		//
		$s_hidden_fields = '<input type="hidden" name="config_data" value="' . htmlspecialchars($_POST['config_data']) . '" />';
		$s_hidden_fields .= '<input type="hidden" name="send_file" value="1" />';

		page_header($lang['NoFTP_config'], "install.$phpEx");

		if ( $upgrade == 1 )
		{
			$s_hidden_fields .= '<input type="hidden" name="upgrade" value="1" />';
			$s_hidden_fields .= '<input type="hidden" name="dbms" value="'.$dmbs.'" />';
			$s_hidden_fields .= '<input type="hidden" name="prefix" value="'.$table_prefix.'" />';
			$s_hidden_fields .= '<input type="hidden" name="dbhost" value="'.$dbhost.'" />';
			$s_hidden_fields .= '<input type="hidden" name="dbname" value="'.$dbname.'" />';
			$s_hidden_fields .= '<input type="hidden" name="dbuser" value="'.$dbuser.'" />';
			$s_hidden_fields .= '<input type="hidden" name="dbpasswd" value="'.$dbpasswd.'" />';
			$s_hidden_fields .= '<input type="hidden" name="install_step" value="1" />';
			$s_hidden_fields .= '<input type="hidden" name="admin_pass1" value="1" />';
			$s_hidden_fields .= '<input type="hidden" name="admin_pass2" value="1" />';
			$s_hidden_fields .= '<input type="hidden" name="server_port" value="'.$server_port.'" />';
			$s_hidden_fields .= '<input type="hidden" name="server_name" value="'.$server_name.'" />';
			$s_hidden_fields .= '<input type="hidden" name="script_path" value="'.$script_path.'" />';
			$s_hidden_fields .= '<input type="hidden" name="board_email" value="'.$board_email.'" />';

			$template->assign_block_vars("switch_upgrade_install", array());
			$template->assign_vars(array(
				"L_UPGRADE_INST" => $lang['continue_upgrade'],
				"L_UPGRADE_SUBMIT" => $lang['upgrade_submit'])
			);
		}

		page_footer($lang['Download_config'], $s_hidden_fields);

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

		@fwrite($fp, $_POST['config_data']);

		@fclose($fp);

		//
		// Now ftp it across.
		//
		@ftp_chdir($conn_id, $ftp_dir);

		$res = ftp_put($conn_id, '../config.'.$phpEx, $tmpfname, FTP_ASCII);

		@ftp_quit($conn_id);

		unlink($tmpfname);

		if( $upgrade == 1 )
		{
			require('upgrade.'.$phpEx);
			exit;
		}

		//
		// Ok we are basically done with the install process let's go on and let the user
		// configure their board now. We are going to do this by calling the admin_board.php
		// from the normal board admin section.
		//
		$s_hidden_fields = '<input type="hidden" name="username" value="' . $admin_name . '" />';
		$s_hidden_fields .= '<input type="hidden" name="password" value="' . $admin_pass1 . '" />';
		$s_hidden_fields .= '<input type="hidden" name="redirect" value="admin/index.php" />';
		$s_hidden_fields .= '<input type="hidden" name="submit" value="' . $lang['Login'] . '" />';

		page_header($lang['Inst_Step_2'], "../login.$phpEx");
		page_footer($lang['Finish_Install'], $s_hidden_fields);

		exit();
	}

}
else if ( ( empty($install_step) || $admin_pass1 != $admin_pass2 || empty($admin_pass1) || $dbhost == '' )  && !defined("PHPBB_INSTALLED") )
{
	//
	// Ok we haven't installed before so lets work our way through the various
	// steps of the install process.  This could turn out to be quite a lengty
	// process.
	//
	// Step 0 gather the pertinant info for database setup...
	// Namely dbms, dbhost, dbname, dbuser, and dbpasswd.
	//

	//
	// Guess at some basic info used for install..
	//
	if ( !empty($_SERVER['SERVER_NAME']) || !empty($_ENV['SERVER_NAME']) )
	{
		$server_name = ( !empty($_SERVER['SERVER_NAME']) ) ? $_SERVER['SERVER_NAME'] : $_ENV['SERVER_NAME'];
	}
	else if ( !empty($_SERVER['HTTP_HOST']) || !empty($_ENV['HTTP_HOST']) )
	{
		$server_name = ( !empty($_SERVER['HTTP_HOST']) ) ? $_SERVER['HTTP_HOST'] : $_ENV['HTTP_HOST'];
	}
	else
	{
		$server_name = '';
	}

	if ( !empty($_SERVER['SERVER_PORT']) || !empty($_ENV['SERVER_PORT']) )
	{
		$server_port = ( !empty($_SERVER['SERVER_PORT']) ) ? $_SERVER['SERVER_PORT'] : $_ENV['SERVER_PORT'];
	}
	else
	{
		$server_port = '80';
	}

	$script_path = preg_replace('/install\/install\.'.$phpEx.'/i', '', $_SERVER['PHP_SELF']);

	//
	//
	//
	$instruction_text = $lang['Inst_Step_0'];
/*
	if ( (($_POST['admin_pass1'] != $_POST['admin_pass2']) && $install_step != '0') || ( empty($_POST['admin_pass1']) && !empty($dbhost)) )
	{
		$instruction_text = $lang['Password_mismatch'] . '<br />' . $instruction_text;
	}
*/
	$lang_options = language_select($language, 'language', '../language');

	foreach( $available_dbms as $dbms_name => $details )
	{
		$selected = ( $dbms_name == $dbms ) ? ' selected="selected' : '';
		$dbms_options .= '<option value="' . $dbms_name . '">' . $details['LABEL'] . '</option>';
	}

	$upgrade_option = '<option value="0">' . $lang['Install'] . '</option>';
	$upgrade_option .= '<option value="1">' . $lang['Upgrade'] . '</option>';
	$upgrade_option .= '<option value="2">' . $lang['Update'] . '</option>';

	$s_hidden_fields = '<input type="hidden" name="install_step" value="1" />';

	page_header($instruction_text, "install.$phpEx");

?>
	<tr>
		<th colspan="2"><?php echo $lang['Initial_config']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="right" width="40%"><span class="gen"><?php echo $lang['Default_lang']; ?>: </span></td>
		<td class="row2"><?php echo $lang_options; ?></select></td>
	</tr>
	<tr>
		<td class="row1" align="right"><span class="gen"><?php echo $lang['dbms']; ?>: </span></td>
		<td class="row2"><select name="dbms" onchange="if(document.install_form.upgrade.options[upgrade.selectedIndex].value == 1) { document.install_form.dbms.selectedIndex=0}"><?php echo $dbms_options; ?></select></td>
	</tr>
	<tr>
		<td class="row1" align="right"><span class="gen"><?php echo $lang['Install_Method']; ?>: </span></td>
		<td class="row2"><select name="upgrade" onchange="if( this.options[this.selectedIndex].value == 1 ) { document.install_form.dbms.selectedIndex=0; }"><?php echo $upgrade_option; ?></select></td>
	</tr>
	<tr>
		<th colspan="2"><?php echo $lang['DB_config']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="right"><span class="gen"><?php echo $lang['DB_Host']; ?>: </span></td>
		<td class="row2"><input type="text" name="dbhost" value="<?php echo ( $dbhost != '' ) ? $dbhost : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" align="right"><span class="gen"><?php echo $lang['DB_Name']; ?>: </span></td>
		<td class="row2"><input type="text" name="dbname" value="<?php echo ( $dbname != '' ) ? $dbname : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" align="right"><span class="gen"><?php echo $lang['DB_Username']; ?>: </span></td>
		<td class="row2"><input type="text" name="dbuser" value="<?php echo ( $dbuser != '' ) ? $dbuser : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" align="right"><span class="gen"><?php echo $lang['DB_Password']; ?>: </span></td>
		<td class="row2"><input type="password" name="dbpasswd" value="<?php echo ( $dbpasswd != '' ) ? $dbpasswd : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" align="right"><span class="gen"><?php echo $lang['Table_Prefix']; ?>: </span></td>
		<td class="row2"><input type="text" name="prefix" value="<?php echo ( !empty($table_prefix) ) ? $table_prefix : 'phpbb_'; ?>" /></td>
	</tr>
	<tr>
		<th colspan="2"><?php echo $lang['Admin_config']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="right"><span class="gen"><?php echo $lang['Server_name']; ?>: </span></td>
		<td class="row2"><input type="text" name="server_name" value="<?php echo $server_name; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" align="right"><span class="gen"><?php echo $lang['Server_port']; ?>: </span></td>
		<td class="row2"><input type="text" name="server_port" value="<?php echo $server_port; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" align="right"><span class="gen"><?php echo $lang['Script_path']; ?>: </span></td>
		<td class="row2"><input type="text" name="script_path" value="<?php echo $script_path; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" align="right"><span class="gen"><?php echo $lang['Admin_Username']; ?>: </span></td>
		<td class="row2"><input type="text" name="admin_name" value="<?php echo ( $admin_name != '' ) ? $admin_name : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" align="right"><span class="gen"><?php echo $lang['Admin_email']; ?>: </span></td>
		<td class="row2"><input type="text" name="board_email" value="<?php echo ( $board_email != '' ) ? $board_email : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" align="right"><span class="gen"><?php echo $lang['Admin_Password']; ?>: </span></td>
		<td class="row2"><input type="password" name="admin_pass1" value="<?php echo ( $admin_pass1 != '' ) ? $admin_pass1 : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" align="right"><span class="gen"><?php echo $lang['Admin_Password_confirm']; ?>: </span></td>
		<td class="row2"><input type="password" name="admin_pass2" value="<?php echo ( $admin_pass2 != '' ) ? $admin_pass2 : ''; ?>" /></td>
	</tr>
<?php

	page_footer($lang['Start_Install'], $s_hidden_fields, "install.$phpEx");

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
	if ( isset($dbms) )
	{
		switch ( $dbms )
		{
			case 'msaccess':
			case 'mssql-odbc':
				$check_exts = 'odbc';
				$check_other = 'odbc';
				break;
			case 'mssql':
				$check_exts = 'mssql';
				$check_other = 'sybase';
				break;
			case 'mysql':
			case 'mysql4':
				$check_exts = 'mysql';
				$check_other = 'mysql';
				break;
			case 'postgres':
				$check_exts = 'pgsql';
				$check_other = 'pgsql';
				break;
		}

		if ( !extension_loaded( $check_exts ) && !extension_loaded( $check_other ) )
		{
/*			$template->assign_block_vars("switch_error_install", array());

			$template->assign_vars(array(
				"L_ERROR_TITLE" => $lang['Installer_Error'],
				"L_ERROR" => $lang['Install_No_Ext'])
			);
			$template->pparse('body');				*/

			die("Error during installation: no $check_exts extension");
		}

		include($phpbb_root_path . 'db/' . $dbms . '.' . $phpEx);

		$db = new sql_db($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false);
	}

	$dbms_schema = 'schemas/' . $available_dbms[$dbms]['SCHEMA'] . '_schema.sql';
	$dbms_basic = 'schemas/' . $available_dbms[$dbms]['SCHEMA'] . '_basic.sql';

	$remove_remarks = $available_dbms[$dbms]['COMMENTS'];;
	$delimiter = $available_dbms[$dbms]['DELIM'];
	$delimiter_basic = $available_dbms[$dbms]['DELIM_BASIC'];

	if ( $install_step == 1 || $reinstall )
	{
		if ( $upgrade != 1 )
		{
			if ( $dbms != 'msaccess' )
			{
				
				//
				// Ok we have the db info go ahead and read in the relevant schema
				// and work on building the table.. probably ought to provide some
				// kind of feedback to the user as we are working here in order
				// to let them know we are actually doing something.
				//
				$sql_query = @fread(@fopen($dbms_schema, 'r'), @filesize($dbms_schema));
				$sql_query = preg_replace('/phpbb_/is', $table_prefix, $sql_query);

				$sql_query = $remove_remarks($sql_query);
				$sql_query = split_sql_file($sql_query, $delimiter);

				$sql_count = count($sql_query);

				for($i = 0; $i < $sql_count; $i++)
				{
					$db->sql_query($sql_query[$i]);
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
					$db->sql_query($sql_query[$i]);
				}
			}

			//
			// Ok at this point they have entered their admin password, let's go
			// ahead and create the admin account with some basic default information
			// that they can customize later, and write out the config file.  After
			// this we are going to pass them over to the admin_forum.php script
			// to set up their forum defaults.
			//
			$admin_pass_md5 = ( $confirm && $userdata['user_level'] == ADMIN ) ? $admin_pass1 : md5($admin_pass1);
			$error = '';

			//
			// Update the default admin user with their information.
			//
			$sql = "INSERT INTO " . $table_prefix . "config (config_name, config_value)
				VALUES ('board_startdate', " . time() . ")";
			$db->sql_query($sql);

			$sql = "INSERT INTO " . $table_prefix . "config (config_name, config_value)
				VALUES ('default_lang', '" . str_replace("\'", "''", $language) . "')";
			$db->sql_query($sql);

			$sql = "UPDATE " . $table_prefix . "config
				SET config_value = '" . $server_name . "'
				WHERE config_name = 'server_name'";
			$db->sql_query($sql);

			$sql = "UPDATE " . $table_prefix . "config
				SET config_value = '" . $server_port . "'
				WHERE config_name = 'server_port'";
			$db->sql_query($sql);

			$sql = "UPDATE " . $table_prefix . "config
				SET config_value = '" . $script_path . "'
				WHERE config_name = 'script_path'";
			$db->sql_query($sql);

			$sql = "UPDATE " . $table_prefix . "config
				SET config_value = '" . $board_email . "'
				WHERE config_name = 'board_email'";
			$db->sql_query($sql);

			$sql = "UPDATE " . $table_prefix . "config
				SET config_value = '" . $server_name . "'
				WHERE config_name = 'cookie_domain'";
			$db->sql_query($sql);

			$sql = "UPDATE " . $table_prefix . "config
				SET config_value = '" . $admin_name . "'
				WHERE config_name = 'newest_username'";
			$db->sql_query($sql);

			$sql = "UPDATE " . $table_prefix . "users
				SET username = '" . str_replace("\'", "''", $admin_name) . "', user_password='" . str_replace("\'", "''", $admin_pass_md5) . "', user_lang = '" . str_replace("\'", "''", $language) . "', user_email='" . str_replace("\'", "''", $board_email) . "'
				WHERE username = 'Admin'";
			$db->sql_query($sql);

			$sql = "UPDATE " . $table_prefix . "moderator_cache
				SET username = '" . $admin_name . "'
				WHERE username = 'Admin'";
			$db->sql_query($sql);

			$sql = "UPDATE " . $table_prefix . "forums
				SET forum_last_poster_name = '" . $admin_name . "'
				WHERE forum_last_poster_name = 'Admin'";
			$db->sql_query($sql);

			$sql = "UPDATE " . $table_prefix . "users
				SET user_regdate = " . time();
			$db->sql_query($sql);

/*			if ( $error != '' )
			{
				$template->assign_block_vars("switch_error_install", array());

				$template->assign_vars(array(
					"L_ERROR_TITLE" => $lang['Installer_Error'],
					"L_ERROR" => $lang['Install_db_error'] . '<br /><br />' . $error)
				);

				$template->pparse('body');

				exit;
			}*/
		}

		if ( !$reinstall && !$upgrade_now )
		{
//			$template->assign_block_vars("switch_common_install", array());

			//
			// Write out the config file.
			//
			$config_data = '<?php'."\n\n";
			$config_data .= "//\n// phpBB 2.x auto-generated config file\n// Do not change anything in this file!\n//\n\n";
			$config_data .= '$dbms = "' . $dbms . '";' . "\n\n";
			$config_data .= '$dbhost = "' . $dbhost . '";' . "\n";
			$config_data .= '$dbport = "' . $dbport . '";' . "\n";
			$config_data .= '$dbname = "' . $dbname . '";' . "\n";
			$config_data .= '$dbuser = "' . $dbuser . '";' . "\n";
			$config_data .= '$dbpasswd = "' . $dbpasswd . '";' . "\n\n";
			$config_data .= "\$acm_type = 'file';\n";
			$config_data .= '$table_prefix = "' . $table_prefix . '";' . "\n\n";
			$config_data .= 'define(\'PHPBB_INSTALLED\', true);'."\n\n";
			$config_data .= '?' . '>'; // Done this to prevent highlighting editors getting confused!

			@umask(0111);
			$no_open = FALSE;

			if ( !($fp = fopen('../config.'.$phpEx, 'w')) )
			{
				//
				// Unable to open the file writeable do something here as an attempt
				// to get around that...
				//
				$s_hidden_fields = '<input type="hidden" name="config_data" value="' . htmlspecialchars($config_data) . '" />';

				if ( extension_loaded('ftp') && !defined('NO_FTP') )
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

				if ( $upgrade == 1 )
				{
					$s_hidden_fields .= '<input type="hidden" name="upgrade" value="1" />';
					$s_hidden_fields .= '<input type="hidden" name="dbms" value="'.$dbms.'" />';
					$s_hidden_fields .= '<input type="hidden" name="prefix" value="'.$table_prefix.'" />';
					$s_hidden_fields .= '<input type="hidden" name="dbhost" value="'.$dbhost.'" />';
					$s_hidden_fields .= '<input type="hidden" name="dbname" value="'.$dbname.'" />';
					$s_hidden_fields .= '<input type="hidden" name="dbuser" value="'.$dbuser.'" />';
					$s_hidden_fields .= '<input type="hidden" name="dbpasswd" value="'.$dbpasswd.'" />';
					$s_hidden_fields .= '<input type="hidden" name="install_step" value="1" />';
					$s_hidden_fields .= '<input type="hidden" name="admin_pass1" value="1" />';
					$s_hidden_fields .= '<input type="hidden" name="admin_pass2" value="1" />';
					$s_hidden_fields .= '<input type="hidden" name="server_port" value="'.$server_port.'" />';
					$s_hidden_fields .= '<input type="hidden" name="server_name" value="'.$server_name.'" />';
					$s_hidden_fields .= '<input type="hidden" name="script_path" value="'.$script_path.'" />';
					$s_hidden_fields .= '<input type="hidden" name="board_email" value="'.$board_email.'" />';

					$template->assign_block_vars("switch_upgrade_install", array());
					$template->assign_vars(array(
						"L_UPGRADE_INST" => $lang['continue_upgrade'],
						"L_UPGRADE_SUBMIT" => $lang['upgrade_submit'])
					);
				}

				/*$template->assign_vars(array(
					"L_INSTRUCTION_TEXT" => $lang['Unwriteable_config'],
					"L_SUBMIT" => $lang['Download_config'],

					"S_HIDDEN_FIELDS" => $s_hidden_fields,
					"S_FORM_ACTION" => "install.$phpEx")
				);

				$template->pparse('body');
				*/
				
				exit;
			}

			$result = @fputs($fp, $config_data, strlen($config_data));

			@fclose($fp);
			$upgrade_now = $lang['upgrade_submit'];
		}
		else
		{
//			$template->assign_block_vars("switch_common_install", array());
		}

		//
		// First off let's check and see if we are supposed to be doing an upgrade.
		//
		if ( $upgrade == 1 && $upgrade_now == $lang['upgrade_submit'] )
		{
			define('INSTALLING', true);
			require('upgrade.'.$phpEx);
			exit;
		}
		//
		// Ok we are basically done with the install process let's go on
		// and let the user configure their board now.
		//
		// We are going to do this by calling the admin_board.php from the
		// normal board admin section.
		//
		if ( !$reinstall )
		{
			$s_hidden_fields = '<input type="hidden" name="username" value="' . $admin_name . '" />';
			$s_hidden_fields .= '<input type="hidden" name="password" value="' . $admin_pass1 . '" />';
			$s_hidden_fields .= '<input type="hidden" name="redirect" value="admin/index.php" />';
			$s_hidden_fields .= '<input type="hidden" name="login" value="true" />';
		}
		else
		{
			$s_hidden_fields = '';
		}

		page_header($lang['Inst_Step_2'], "../login.$phpEx");
		page_footer($lang['Finish_Install'], $s_hidden_fields);

		exit;
	}
}

// addslashes to vars if magic_quotes_gpc is off this is a security precaution
// to prevent someone trying to break out of a SQL statement.
function slash_input_data(&$data)
{
	if ( is_array($data) )
	{
		foreach ( $data as $k => $v )
		{
			$data[$k] = ( is_array($v) ) ? slash_input_data($v) : addslashes($v);
		}
	}
	return $data;
}

// Output page -> header
function page_header($l_instructions, $s_action)
{
	global $phpEx, $lang;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<link rel="stylesheet" href="../admin/subSilver.css" type="text/css">
<style type="text/css">
<!--
th		{ background-image: url('../admin/images/cellpic3.gif') }
td.cat	{ background-image: url('../admin/images/cellpic1.gif') }
//-->
</style>
<title><?php echo $lang['Welcome_install']; ?></title>
</head>
<body>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td><img src="../admin/images/header_left.jpg" width="200" height="60" alt="phpBB Logo" title="phpBB Logo" border="0"/></td>
		<td width="100%" background="../admin/images/header_bg.jpg" height="60" align="right" nowrap="nowrap"><span class="maintitle"><?php echo $lang['Welcome_install']; ?></span> &nbsp; &nbsp; &nbsp;</td>
	</tr>
</table>

<table width="85%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td><br clear="all" /><br />

<p><?php echo $l_instructions; ?></p>

<br clear="all" />

<form action="<?php echo $s_action; ?>" name="install_form" method="post"><table class="bg" width="100%" cellspacing="1" cellpadding="2" border="0">
<?php

}

// Output page -> footer
function page_footer($l_submit, $s_hidden_fields)
{
	global $lang;

?>
	<tr>
		<td class="cat" align="center" colspan="2"><?php echo $s_hidden_fields; ?><input class="mainoption" type="submit" value="<?php echo $l_submit; ?>" /></td>
	</tr>
</table></form>

<div align="center"><span class="copyright">Powered by phpBB <?php echo $config['version']; ?> &copy; 2002 <a href="http://www.phpbb.com/" target="_phpbb" class="copyright">phpBB Group</a></span></div>

<br clear="all" />

</body>
</html>
<?php

}

?>