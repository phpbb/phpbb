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
//
// First thing to check for is the case that we couldn't write the config
// file and they chose to have use send it to them.
//

if($HTTP_POST_VARS['send_file'] == 1)
{
	header("Content-Type: text/x-delimtext; name=\"config.php\"");
	header("Content-disposition: attachment; filename=config.php");
	if(get_magic_quotes_gpc())
	{
		$HTTP_POST_VARS['config_data'] = stripslashes($HTTP_POST_VARS['config_data']);
	}
	echo $HTTP_POST_VARS['config_data'];
	exit();
}

$phpbb_root_path='./';
include($phpbb_root_path.'extension.inc');


/***************************************************************************
 *								Install Customization Section
 *
 *		This section can be modified to set up some basic default information
 * 	used by the install script.  Specifically the default theme data
 *		and the default template.
 *
 **************************************************************************/
$userdata = "some false data"; 
$theme = array(
	'themes_id' => '2',
	'themes_name' => 'Default',
	'template_name' => 'Default',
	'td_color1' => 'CCCCCC', 
	'td_color2' => 'DDDDDD'
);
$default_language = 'english';
$default_template = 'Default';
					
$available_dbms[] = array(
	"LABEL" => "MySQL",
	"VALUE" => "mysql"
);
$available_dbms[] = array(
	"LABEL" => "MS SQL",
	"VALUE" => "mssql"
);
$available_dbms[] = array(
	"LABEL" => "Postgres",
	"VALUE" => "postgres"
);
$available_dbms[] = array(
	"LABEL" => "ODBC - MSAccess",
	"VALUE" => "odbc:access"
);
$available_dbms[] = array(
	"LABEL" => "ODBC - DB2",
	"VALUE" => "odbc:db2"
);
/***************************************************************************
*		
*						End Install Customization Section
*
***************************************************************************/

//
//	Fill an array with a list of available languages.
//

$dir = opendir($phpbb_root_path.'/language');
while($file = readdir($dir))
{
	if(preg_match("/^lang_(.*)\.$phpEx/", $file, $matches))
	{
		$available_lang[] = $matches[1];
	}
}

//
// Bring in the extra files that contain functions we need.
//

$language = ($HTTP_POST_VARS['language']) ? $HTTP_POST_VARS['language'] : $default_language;
include($phpbb_root_path.'includes/sql_parse.'.$phpEx);
include($phpbb_root_path.'includes/constants.'.$phpEx);
include($phpbb_root_path.'includes/template.'.$phpEx);
include($phpbb_root_path.'includes/functions.'.$phpEx);
include($phpbb_root_path.'language/lang_'.$language.'.'.$phpEx);

//
// Create an instance of the template class.
//
$template = new Template($phpbb_root_path . "templates/" . $default_template);

 
if(file_exists('config.'.$phpEx))
{
	include('config.'.$phpEx);
}
if($installed)
{
	//
	// Sorry this has already been installed can't do anything more with it
	//
	$template->set_filenames(array(
		"body" => "install_error.tpl")
	);
	$template->assign_vars(array(
		"L_TITLE" => $lang['Installer_Error'],
		"L_ERROR" => $lang['Previous_Install'])
	);
	$template->pparse('body');
	die();
}
//
// Ok we haven't installed before so lets work our way through the various
// steps of the install process.  This could turn out to be quite a lengty 
// process.
//
$installStep = ($HTTP_POST_VARS['installStep']) ? $HTTP_POST_VARS['installStep']: $HTTP_GET_VARS['installStep'];
$dbms = ($HTTP_POST_VARS['dbms']);
if( (!isset($installStep) || $installStep == 0) || ($HTTP_POST_VARS['admin_pass1'] != $HTTP_POST_VARS['admin_pass2']) )
{
	//
	// Step 0 gather the pertinant info for database setup...
	// Namely dbms, dbhost, dbname, dbuser, and dbpasswd.
	//
	$Instruct = $lang['Inst_Step_0'];
	if( $HTTP_POST_VARS['admin_pass1'] != $HTTP_POST_VARS['admin_pass2'] )
	{
		$Instruct = $lang['Password_mismatch'] . '<br>' . $Instruct;
	}
	$template->set_filenames(array(
		"body" => "install.tpl")
	);
	$template->assign_vars(array(
		"L_INSTRUCT" => $Instruct,
		"L_SUBMIT" => $lang['Start_Install'],
		"S_FORM_ACTION" => 'install.'.$phpEx)
	);
	$template->assign_block_vars("hidden_fields", array(
		"NAME" => "installStep",
		"VALUE" => "1")
	);
	$template->assign_block_vars("inputs", array(
		"NAME" => "dbhost",
		"TYPE" => "text",
		"VALUE" => $HTTP_POST_VARS['dbhost'],
		"L_LABEL" => $lang['DB_Host'] . ':')
	);
	$template->assign_block_vars("inputs", array(
		"NAME" => "dbname",
		"TYPE" => "text",
		"VALUE" => $HTTP_POST_VARS['dbname'],
		"L_LABEL" => $lang['DB_Name'] . ':')
	);
	$template->assign_block_vars("inputs", array(
		"NAME" => "dbuser",
		"TYPE" => "text",
		"VALUE" => $HTTP_POST_VARS['dbuser'],
		"L_LABEL" => $lang['Database'] . ' ' . $lang['Username'] . ':')
	);
	$template->assign_block_vars("inputs", array(
		"NAME" => "dbpasswd",
		"TYPE" => "password",
		"VALUE" => $HTTP_POST_VARS['dbpasswd'],
		"L_LABEL" => $lang['Database'] . ' ' . $lang['Password'] . ':')
	);
	$template->assign_block_vars("inputs", array(
		"NAME" => "prefix",
		"TYPE" => "text",
		"VALUE" => (!empty($HTTP_POST_VARS['prefix'])) ? $HTTP_POST_VARS['prefix'] : "phpbb_",
		"L_LABEL" => $lang['Table_Prefix'] . ':')
	);
	$template->assign_block_vars("inputs", array(
		"NAME" => "admin_name",
		"TYPE" => "text",
		"VALUE" => $HTTP_POST_VARS['admin_name'],
		"L_LABEL" => $lang['Administrator'] . ' ' . $lang['Username'] . ':')
	);
	$template->assign_block_vars("inputs", array(
		"NAME" => "admin_pass1",
		"TYPE" => "password",
		"VALUE" => $HTTP_POST_VARS['admin_pass1'],
		"L_LABEL" => $lang['Administrator'] . ' ' . $lang['Password'] . ':')
	);
	$template->assign_block_vars("inputs", array(
		"NAME" => "admin_pass2",
		"TYPE" => "password",
		"VALUE" => $HTTP_POST_VARS['admin_pass2'],
		"L_LABEL" => $lang['Confirm'] . ' ' . $lang['Password'] . ':')
	);
	$template->assign_block_vars("selects", array(
		"NAME" => "language",
		"L_LABEL" => $lang['Install_lang'])
	);
	for($i = 0; $i < count($available_lang); $i++)
	{
		$template->assign_block_vars("selects.options", array(
			"LABEL" => $available_lang[$i],
			"DEFAULT" => ($available_lang[$i] == $HTTP_POST_VARS['language'])?'SELECTED':'',
			"VALUE" => $available_lang[$i])
		);
	}
	$template->assign_block_vars("selects", array(
		"NAME" => "dbms",
		"L_LABEL" => $lang['dbms'])
	);
	for($i = 0; $i < count($available_dbms); $i++)
	{
		$template->assign_block_vars("selects.options", array(
			"LABEL" => $available_dbms[$i]['LABEL'],
			"DEFAULT" => ($available_dbms[$i]['VALUE'] == $HTTP_POST_VARS['dbms'])?'SELECTED':'',
			"VALUE" => $available_dbms[$i]['VALUE'])
		);
	}
	$template->pparse("body");
	exit();
}
//
// If the dbms is set to be odbc then we need to skip most of the 
// steps and go straight to writing the config file.  We'll spit
// out some additional instructions later on what to do after installation
// for the odbc DBMS.
//
if (ereg(':', $dbms) && $installStep < 2)
{
	$dbms = explode(':', $dbms);
	$dbhost = $dbms[1] . ':' . $dbhost;
	$dbms = $dbms[0];
	$installStep = 2;
}
elseif ( isset($dbms) ) 
{
	include($phpbb_root_path.'includes/db.'.$phpEx);
}

$dbms_schema = 'db/'.$dbms.'_schema.sql';
$dbms_basic = 'db/'.$dbms.'_basic.sql';
$remove_remarks = ($dbms == 'mysql')?'remove_remarks':'remove_comments';
$delimiter = ( $dbms == 'mssql' )?'GO':';'; 
switch ( $installStep )
{
	case 1:
		//
		// Ok we have the db info go ahead and read in the relevant schema
		// and work on building the table.. probably ought to provide some
		// kind of feedback to the user as we are working here in order
		// to let them know we are actually doing something.
		//
		$sql_query = fread(fopen($dbms_schema, 'r'), filesize($dbms_schema));
		$sql_query = $remove_remarks($sql_query);
		$sql_query = split_sql_file($sql_query, $delimiter);
		$sql_count = count($sql_query);
		$sql_query = preg_replace('/phpbb_/', $HTTP_POST_VARS['prefix'], $sql_query);
		for($i = 0; $i < $sql_count; $i++)
		{
			$result = $db->sql_query($sql_query[$i]);
			if( !$result )
			{
				$error = $db->sql_error();
				$template->set_filenames(array(
					"body" => "install_error.tpl")
				);
				$template->assign_vars(array(
					"L_TITLE" => $lang['Installer_Error'],
					"L_ERROR" => $lang['Install_db_error'] . '<br>' . $error['message'])
				);
				$template->pparse('body');
				die();
			}
		}
		//
		// Ok tables have been built, let's fill in the basic information
		//
		$sql_query = fread(fopen($dbms_basic, 'r'), filesize($dbms_basic));
		$sql_query = $remove_remarks($sql_query);
		$sql_query = split_sql_file($sql_query, $delimiter);
		$sql_count = count($sql_query);
		$sql_query = preg_replace('/phpbb_/', $HTTP_POST_VARS['prefix'], $sql_query);
		for($i = 0; $i < $sql_count; $i++)
		{
			$result = $db->sql_query($sql_query[$i]);
			if( !$result )
			{
				$error = $db->sql_error();
				$template->set_filenames(array(
					"body" => "install_error.tpl")
				);
				$template->assign_vars(array(
					"L_TITLE" => $lang['Installer_Error'],
					"L_ERROR" => $lang['Install_db_error'] . "<br>" . $error["message"])
				);
				$template->pparse('body');
				die();
			}
		}
		//
		// Ok at this point they have entered their admin password, let's go 
		// ahead and create the admin account with some basic default information
		// that they can customize later, and write out the config file.  After
		// this we are going to pass them over to the admin_forum.php script
		// to set up their forum defaults.
		//
		if( $dbms == 'odbc' )
		{
			//
			// Output the instructions for the odbc...
			//
			$template->set_filenames(array(
				"body" => "install.tpl")
			);
			
			$template->assign_vars(array(
				"L_INSTRUCT" => $lang['ODBC_Instructs'],
				"L_SUBMIT" => $lang['OK'],
				"S_FORM_ACTION" => 'install.'.$phpEx)
			);
			$template->assign_block_vars("hidden_fields", array(
				"NAME" => "installStep",
				"VALUE" => '3')
			);
			exit();
		}
		else
		{
			//
			// Update the default admin user with their information.
			//
			$sql = "UPDATE ".$HTTP_POST_VARS['prefix']."users 
						SET username='".$HTTP_POST_VARS['admin_name']."',
							user_password='".md5($HTTP_POST_VARS['admin_pass1'])."'
						WHERE username = 'Admin'";
			$result = $db->sql_query($sql);
			if( !$result )
			{
				$error = $db->sql_error();
				$template->set_filenames(array(
					"body" => "install_error.tpl")
				);
				$template->assign_vars(array(
					"L_TITLE" => $lang['Installer_Error'],
					"L_ERROR" => $lang['Install_db_error'] . '<br>' . $error['message'])
				);
				$template->pparse('body');
				die();
			}
			//
			// Write out the config file.
			//
			$config_data = '<?php'."\n";
			$config_data.= '$dbms = "'.$dbms.'";'."\n";
			$config_data.= '$dbhost = "'.$dbhost.'";'."\n";
			$config_data.= '$dbname = "'.$dbname.'";'."\n";
			$config_data.= '$dbuser = "'.$dbuser.'";'."\n";
			$config_data.= '$dbpasswd = "'.$dbpasswd.'";'."\n";
			$config_data.= '$installed = True;'."\n";	
			$config_data.= '$table_prefix = "'.$HTTP_POST_VARS['prefix'].'";'."\n";
			$config_data.= '?>';
			@umask(0111);
			$noOpen = False;
			$fp = @fopen('config.php', 'w');
			if(!$fp)
			{
				//
				// Unable to open the file writeable do something here as an attempt
				// to get around that...
				$template->set_filenames(array(
						"body" => "install.tpl")
				);
				$template->assign_vars(array(
					"L_INSTRUCT" => $lang['UnWrite_Config'],
					"L_SUBMIT" => $lang['Send_Config'],
					"S_FORM_ACTION" => 'install.'.$phpEx)
				);
				$template->assign_block_vars("hidden_fields", array(
					"NAME" => "config_data",
					"VALUE" => htmlspecialchars($config_data) )
				);
				$template->assign_block_vars("hidden_fields", array(
					"NAME" => "send_file",
					"VALUE" => "1")
				);
				$template->pparse('body');
				exit();
			}
			$result = fputs($fp, $config_data, strlen($config_data));
			fclose($fp);
			//
			// Ok we are basically done with the install process let's go on 
			// and let the user configure their board now.
			// We are going to do this by calling the admin_board.php from the
			// normal board admin section.
			//
			$template->set_filenames(array(
				"body" => "install.tpl")
			);
			$template->assign_vars(array(
				"L_INSTRUCT" => $lang['Inst_Step_2'],
				"L_SUBMIT" => $lang['Finish_Install'],
				"S_FORM_ACTION" => 'login.'.$phpEx)
			);
			
			$template->assign_block_vars("hidden_fields", array(
				"NAME" => 'username',
				"VALUE" => $admin_name)
			);
			$template->assign_block_vars("hidden_fields", array(
				"NAME" => 'password',
				"VALUE" => $admin_pass1)
			);
			$template->assign_block_vars("hidden_fields", array(
				"NAME" => 'submit',
				"VALUE" => 'Login')
			);
			$template->assign_block_vars("hidden_fields", array(
				"NAME" => 'forward_page',
				"VALUE" => 'admin/admin_board.'.$phpEx.'?mode=config')
			);
			$template->pparse('body');
			exit();
		}
		break;
}
