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

// ---------
// FUNCTIONS
//
function page_header($text, $form_action = false)
{
	global $phpEx, $lang;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['ENCODING']; ?>">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $lang['Welcome_install'];?></title>
<link rel="stylesheet" href="../templates/subSilver/subSilver.css" type="text/css">
<style type="text/css">
<!--
th			{ background-image: url('../templates/subSilver/images/cellpic3.gif') }
td.cat		{ background-image: url('../templates/subSilver/images/cellpic1.gif') }
td.rowpic	{ background-image: url('../templates/subSilver/images/cellpic2.jpg'); background-repeat: repeat-y }
td.catHead,td.catSides,td.catLeft,td.catRight,td.catBottom { background-image: url('../templates/subSilver/images/cellpic1.gif') }

/* Import the fancy styles for IE only (NS4.x doesn't use the @import function) */
@import url("../templates/subSilver/formIE.css"); 
//-->
</style>
</head>
<body bgcolor="#E5E5E5" text="#000000" link="#006699" vlink="#5584AA">

<table width="100%" border="0" cellspacing="0" cellpadding="10" align="center"> 
	<tr>
		<td class="bodyline" width="100%"><table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td><table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td><img src="../templates/subSilver/images/logo_phpBB.gif" border="0" alt="Forum Home" vspace="1" /></td>
						<td align="center" width="100%" valign="middle"><span class="maintitle"><?php echo $lang['Welcome_install'];?></span></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td><br /><br /></td>
			</tr>
			<tr>
				<td colspan="2"><table width="90%" border="0" align="center" cellspacing="0" cellpadding="0">
					<tr>
						<td><span class="gen"><?php echo $text; ?></span></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td><br /><br /></td>
			</tr>
			<tr>
				<td width="100%"><table width="100%" cellpadding="2" cellspacing="1" border="0" class="forumline"><form action="<?php echo ($form_action) ? $form_action : 'install.'.$phpEx; ?>" name="install" method="post">
<?php

}

function page_footer()
{

?>
				</table></form></td>
			</tr>
		</table></td>
	</tr>
</table>

</body>
</html>
<?php

}

function page_common_form($hidden, $submit)
{

?>
					<tr> 
					  <td class="catBottom" align="center" colspan="2"><?php echo $hidden; ?><input class="mainoption" type="submit" value="<?php echo $submit; ?>" /></td>
					</tr>
<?php

}

function page_upgrade_form()
{
	global $lang;

?>
					<tr>
						<td class="catBottom" align="center" colspan="2"><?php echo $lang['continue_upgrade']; ?></td>
					</tr>
					<tr>
						<td class="catBottom" align="center" colspan="2"><input type="submit" name="upgrade_now" value="<?php echo $lang['upgrade_submit']; ?>" /></td>
					</tr>
<?php 

}

function page_error($error_title, $error)
{

?>
					<tr>
						<th><?php echo $error_title; ?></th>
					</tr>
					<tr>
						<td class="row1" align="center"><span class="gen"><?php echo $error; ?></span></td>
					</tr>
<?php

}

// Guess an initial language ... borrowed from phpBB 2.2 it's not perfect, 
// really it should do a straight match first pass and then try a "fuzzy"
// match on a second pass instead of a straight "fuzzy" match.
function guess_lang()
{
	global $phpbb_root_path, $HTTP_SERVER_VARS;

	// The order here _is_ important, at least for major_minor
	// matches. Don't go moving these around without checking with
	// me first - psoTFX
	$match_lang = array(
		'arabic'					=> 'ar([_-][a-z]+)?', 
		'bulgarian'					=> 'bg', 
		'catalan'					=> 'ca', 
		'czech'						=> 'cs', 
		'danish'					=> 'da', 
		'german'					=> 'de([_-][a-z]+)?',
		'english'					=> 'en([_-][a-z]+)?', 
		'estonian'					=> 'et', 
		'finnish'					=> 'fi', 
		'french'					=> 'fr([_-][a-z]+)?', 
		'greek'						=> 'el', 
		'spanish_argentina'			=> 'es[_-]ar', 
		'spanish'					=> 'es([_-][a-z]+)?', 
		'gaelic'					=> 'gd', 
		'galego'					=> 'gl', 
		'gujarati'					=> 'gu', 
		'hebrew'					=> 'he', 
		'hindi'						=> 'hi', 
		'croatian'					=> 'hr', 
		'hungarian'					=> 'hu', 
		'icelandic'					=> 'is', 
		'indonesian'				=> 'id([_-][a-z]+)?', 
		'italian'					=> 'it([_-][a-z]+)?', 
		'japanese'					=> 'ja([_-][a-z]+)?', 
		'korean'					=> 'ko([_-][a-z]+)?', 
		'latvian'					=> 'lv', 
		'lithuanian'				=> 'lt', 
		'macedonian'				=> 'mk', 
		'dutch'						=> 'nl([_-][a-z]+)?', 
		'norwegian'					=> 'no', 
		'punjabi'					=> 'pa', 
		'polish'					=> 'pl', 
		'portuguese_brazil'			=> 'pt[_-]br', 
		'portuguese'				=> 'pt([_-][a-z]+)?', 
		'romanian'					=> 'ro([_-][a-z]+)?', 
		'russian'					=> 'ru([_-][a-z]+)?', 
		'slovenian'					=> 'sl([_-][a-z]+)?', 
		'albanian'					=> 'sq', 
		'serbian'					=> 'sr([_-][a-z]+)?', 
		'slovak'					=> 'sv([_-][a-z]+)?', 
		'swedish'					=> 'sv([_-][a-z]+)?', 
		'thai'						=> 'th([_-][a-z]+)?', 
		'turkish'					=> 'tr([_-][a-z]+)?', 
		'ukranian'					=> 'uk([_-][a-z]+)?', 
		'urdu'						=> 'ur', 
		'viatnamese'				=> 'vi',
		'chinese_traditional_taiwan'=> 'zh[_-]tw',
		'chinese_simplified'		=> 'zh', 
	);

	if (isset($HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE']))
	{
		$accept_lang_ary = explode(',', $HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE']);
		for ($i = 0; $i < sizeof($accept_lang_ary); $i++)
		{
			@reset($match_lang);
			while (list($lang, $match) = each($match_lang))
			{
				if (preg_match('#' . $match . '#i', trim($accept_lang_ary[$i])))
				{
					if (file_exists(@phpbb_realpath($phpbb_root_path . 'language/lang_' . $lang)))
					{
						return $lang;
					}
				}
			}
		}
	}

	return 'english';
	
}
//
// FUNCTIONS
// ---------

// Begin
error_reporting  (E_ERROR | E_WARNING | E_PARSE); // This will NOT report uninitialized variables
set_magic_quotes_runtime(0); // Disable magic_quotes_runtime

// Slash data if it isn't slashed
if (!get_magic_quotes_gpc())
{
	if (is_array($HTTP_GET_VARS))
	{
		while (list($k, $v) = each($HTTP_GET_VARS))
		{
			if (is_array($HTTP_GET_VARS[$k]))
			{
				while (list($k2, $v2) = each($HTTP_GET_VARS[$k]))
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

	if (is_array($HTTP_POST_VARS))
	{
		while (list($k, $v) = each($HTTP_POST_VARS))
		{
			if (is_array($HTTP_POST_VARS[$k]))
			{
				while (list($k2, $v2) = each($HTTP_POST_VARS[$k]))
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

	if (is_array($HTTP_COOKIE_VARS))
	{
		while (list($k, $v) = each($HTTP_COOKIE_VARS))
		{
			if (is_array($HTTP_COOKIE_VARS[$k]))
			{
				while (list($k2, $v2) = each($HTTP_COOKIE_VARS[$k]))
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

// Begin main prog
define('IN_PHPBB', true);
// Uncomment the following line to completely disable the ftp option...
// define('NO_FTP', true);
$phpbb_root_path = './../';
include($phpbb_root_path.'extension.inc');

// Initialise some basic arrays
$userdata = array();
$lang = array();
$error = false;

// Include some required functions
include($phpbb_root_path.'includes/constants.'.$phpEx);
include($phpbb_root_path.'includes/functions.'.$phpEx);
include($phpbb_root_path.'includes/sessions.'.$phpEx);

// Define schema info
$available_dbms = array(
	'mysql'=> array(
		'LABEL'			=> 'MySQL 3.x',
		'SCHEMA'		=> 'mysql', 
		'DELIM'			=> ';',
		'DELIM_BASIC'	=> ';',
		'COMMENTS'		=> 'remove_remarks'
	), 
	'mysql4' => array(
		'LABEL'			=> 'MySQL 4.x',
		'SCHEMA'		=> 'mysql', 
		'DELIM'			=> ';', 
		'DELIM_BASIC'	=> ';',
		'COMMENTS'		=> 'remove_remarks'
	), 
	'postgres' => array(
		'LABEL'			=> 'PostgreSQL 7.x',
		'SCHEMA'		=> 'postgres', 
		'DELIM'			=> ';', 
		'DELIM_BASIC'	=> ';',
		'COMMENTS'		=> 'remove_comments'
	), 
	'mssql' => array(
		'LABEL'			=> 'MS SQL Server 7/2000',
		'SCHEMA'		=> 'mssql', 
		'DELIM'			=> 'GO', 
		'DELIM_BASIC'	=> ';',
		'COMMENTS'		=> 'remove_comments'
	),
	'msaccess' => array(
		'LABEL'			=> 'MS Access [ ODBC ]',
		'SCHEMA'		=> '', 
		'DELIM'			=> '', 
		'DELIM_BASIC'	=> ';',
		'COMMENTS'		=> ''
	),
	'mssql-odbc' =>	array(
		'LABEL'			=> 'MS SQL Server [ ODBC ]',
		'SCHEMA'		=> 'mssql', 
		'DELIM'			=> 'GO',
		'DELIM_BASIC'	=> ';',
		'COMMENTS'		=> 'remove_comments'
	)
);

// Obtain various vars
$confirm = (isset($HTTP_POST_VARS['confirm'])) ? true : false;
$cancel = (isset($HTTP_POST_VARS['cancel'])) ? true : false;

if (isset($HTTP_POST_VARS['install_step']) || isset($HTTP_GET_VARS['install_step']))
{
	$install_step = (isset($HTTP_POST_VARS['install_step'])) ? $HTTP_POST_VARS['install_step'] : $HTTP_GET_VARS['install_step'];
}
else
{
	$install_step = '';
}

$upgrade = (!empty($HTTP_POST_VARS['upgrade'])) ? $HTTP_POST_VARS['upgrade']: '';
$upgrade_now = (!empty($HTTP_POST_VARS['upgrade_now'])) ? $HTTP_POST_VARS['upgrade_now']:'';

$dbms = isset($HTTP_POST_VARS['dbms']) ? $HTTP_POST_VARS['dbms'] : '';

$dbhost = (!empty($HTTP_POST_VARS['dbhost'])) ? $HTTP_POST_VARS['dbhost'] : '';
$dbuser = (!empty($HTTP_POST_VARS['dbuser'])) ? $HTTP_POST_VARS['dbuser'] : '';
$dbpasswd = (!empty($HTTP_POST_VARS['dbpasswd'])) ? $HTTP_POST_VARS['dbpasswd'] : '';
$dbname = (!empty($HTTP_POST_VARS['dbname'])) ? $HTTP_POST_VARS['dbname'] : '';

$table_prefix = (!empty($HTTP_POST_VARS['prefix'])) ? $HTTP_POST_VARS['prefix'] : '';

$admin_name = (!empty($HTTP_POST_VARS['admin_name'])) ? $HTTP_POST_VARS['admin_name'] : '';
$admin_pass1 = (!empty($HTTP_POST_VARS['admin_pass1'])) ? $HTTP_POST_VARS['admin_pass1'] : '';
$admin_pass2 = (!empty($HTTP_POST_VARS['admin_pass2'])) ? $HTTP_POST_VARS['admin_pass2'] : '';

$ftp_path = (!empty($HTTP_POST_VARS['ftp_path'])) ? $HTTP_POST_VARS['ftp_path'] : '';
$ftp_user = (!empty($HTTP_POST_VARS['ftp_user'])) ? $HTTP_POST_VARS['ftp_user'] : '';
$ftp_pass = (!empty($HTTP_POST_VARS['ftp_pass'])) ? $HTTP_POST_VARS['ftp_pass'] : '';

if (isset($HTTP_POST_VARS['lang']) && preg_match('#^[a-z_]+$#', $HTTP_POST_VARS['lang']))
{
	$language = strip_tags($HTTP_POST_VARS['lang']);
}
else
{
	$language = guess_lang();
}

$board_email = (!empty($HTTP_POST_VARS['board_email'])) ? $HTTP_POST_VARS['board_email'] : '';
$script_path = (!empty($HTTP_POST_VARS['script_path'])) ? $HTTP_POST_VARS['script_path'] : str_replace('install', '', dirname($HTTP_SERVER_VARS['PHP_SELF']));

if (!empty($HTTP_POST_VARS['server_name']))
{
	$server_name = $HTTP_POST_VARS['server_name'];
}
else
{
	// Guess at some basic info used for install..
	if (!empty($HTTP_SERVER_VARS['SERVER_NAME']) || !empty($HTTP_ENV_VARS['SERVER_NAME']))
	{
		$server_name = (!empty($HTTP_SERVER_VARS['SERVER_NAME'])) ? $HTTP_SERVER_VARS['SERVER_NAME'] : $HTTP_ENV_VARS['SERVER_NAME'];
	}
	else if (!empty($HTTP_SERVER_VARS['HTTP_HOST']) || !empty($HTTP_ENV_VARS['HTTP_HOST']))
	{
		$server_name = (!empty($HTTP_SERVER_VARS['HTTP_HOST'])) ? $HTTP_SERVER_VARS['HTTP_HOST'] : $HTTP_ENV_VARS['HTTP_HOST'];
	}
	else
	{
		$server_name = '';
	}
}

if (!empty($HTTP_POST_VARS['server_port']))
{
	$server_port = $HTTP_POST_VARS['server_port'];
}
else
{
	if (!empty($HTTP_SERVER_VARS['SERVER_PORT']) || !empty($HTTP_ENV_VARS['SERVER_PORT']))
	{
		$server_port = (!empty($HTTP_SERVER_VARS['SERVER_PORT'])) ? $HTTP_SERVER_VARS['SERVER_PORT'] : $HTTP_ENV_VARS['SERVER_PORT'];
	}
	else
	{
		$server_port = '80';
	}
}

// Open config.php ... if it exists
if (@file_exists(@phpbb_realpath('config.'.$phpEx)))
{
	include($phpbb_root_path.'config.'.$phpEx);
}

// Is phpBB already installed? Yes? Redirect to the index
if (defined("PHPBB_INSTALLED"))
{
	redirect('../index.'.$phpEx);
}

// Import language file, setup template ...
include($phpbb_root_path.'language/lang_' . $language . '/lang_main.'.$phpEx);
include($phpbb_root_path.'language/lang_' . $language . '/lang_admin.'.$phpEx);

// Ok for the time being I'm commenting this out whilst I'm working on
// better integration of the install with upgrade as per Bart's request
// JLH
if ($upgrade == 1)
{
	// require('upgrade.'.$phpEx);
	$install_step = 1;
}

// What do we need to do?
if (!empty($HTTP_POST_VARS['send_file']) && $HTTP_POST_VARS['send_file'] == 1 && empty($HTTP_POST_VARS['upgrade_now']))
{
	header('Content-Type: text/x-delimtext; name="config.' . $phpEx . '"');
	header('Content-disposition: attachment; filename=config.' . $phpEx . '"');

	// We need to stripslashes no matter what the setting of magic_quotes_gpc is
	// because we add slashes at the top if its off, and they are added automaticlly 
	// if it is on.
	echo stripslashes($HTTP_POST_VARS['config_data']);

	exit;
}
else if (!empty($HTTP_POST_VARS['send_file']) && $HTTP_POST_VARS['send_file'] == 2)
{
	$s_hidden_fields = '<input type="hidden" name="config_data" value="' . htmlspecialchars(stripslashes($HTTP_POST_VARS['config_data'])) . '" />';
	$s_hidden_fields .= '<input type="hidden" name="ftp_file" value="1" />';

	if ($upgrade == 1)
	{
		$s_hidden_fields .= '<input type="hidden" name="upgrade" value="1" />';
	}

	page_header($lang['ftp_instructs']);

?>
					<tr>
						<th colspan="2"><?php echo $lang['ftp_info']; ?></th>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen"><?php echo $lang['ftp_path']; ?></span></td>
						<td class="row2"><input type="text" name="ftp_dir"></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen"><?php echo $lang['ftp_password']; ?></span></td>
						<td class="row2"><input type="text" name="ftp_user"></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen"><?php echo $lang['ftp_username']; ?></span></td>
						<td class="row2"><input type="password" name="ftp_pass"></td>
					</tr>
<?php

	page_common_form($s_hidden_fields, $lang['Transfer_config']);
	page_footer();
	exit;

}
else if (!empty($HTTP_POST_VARS['ftp_file']))
{
	// Try to connect ...
	$conn_id = @ftp_connect('localhost');
	$login_result = @ftp_login($conn_id, "$ftp_user", "$ftp_pass");

	if (!$conn_id || !$login_result)
	{
		page_header($lang['NoFTP_config']);

		// Error couldn't get connected... Go back to option to send file...
		$s_hidden_fields = '<input type="hidden" name="config_data" value="' . htmlspecialchars(stripslashes($HTTP_POST_VARS['config_data'])) . '" />';
		$s_hidden_fields .= '<input type="hidden" name="send_file" value="1" />';

		// If we're upgrading ...
		if ($upgrade == 1)
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

			page_upgrade_form();
		}
		else
		{
			page_common_form($s_hidden_fields, $lang['Download_config']);

		}

		page_footer();
		exit;
	}
	else
	{
		// Write out a temp file...
		$tmpfname = @tempnam('/tmp', 'cfg');

		@unlink($tmpfname); // unlink for safety on php4.0.3+

		$fp = @fopen($tmpfname, 'w');

		@fwrite($fp, stripslashes($HTTP_POST_VARS['config_data']));

		@fclose($fp);

		// Now ftp it across.
		@ftp_chdir($conn_id, $ftp_dir);

		$res = ftp_put($conn_id, 'config.'.$phpEx, $tmpfname, FTP_ASCII);

		@ftp_quit($conn_id);

		unlink($tmpfname);

		if ($upgrade == 1)	
		{
			require('upgrade.'.$phpEx);
			exit;
		}

		// Ok we are basically done with the install process let's go on 
		// and let the user configure their board now. We are going to do 
		// this by calling the admin_board.php from the normal board admin
		// section.
		$s_hidden_fields = '<input type="hidden" name="username" value="' . $admin_name . '" />';
		$s_hidden_fields .= '<input type="hidden" name="password" value="' . $admin_pass1 . '" />';
		$s_hidden_fields .= '<input type="hidden" name="redirect" value="../admin/index.'.$phpEx.'" />';
		$s_hidden_fields .= '<input type="hidden" name="submit" value="' . $lang['Login'] . '" />';

		page_header($lang['Inst_Step_2']);
		page_common_form($s_hidden_fields, $lang['Finish_Install']);
		page_footer();
		exit();
	}
}
else if ((empty($install_step) || $admin_pass1 != $admin_pass2 || empty($admin_pass1) || empty($dbhost)))
{
	// Ok we haven't installed before so lets work our way through the various
	// steps of the install process.  This could turn out to be quite a lengty 
	// process.

	// Step 0 gather the pertinant info for database setup...
	// Namely dbms, dbhost, dbname, dbuser, and dbpasswd.
	$instruction_text = $lang['Inst_Step_0'];

	if (!empty($install_step))
	{
		if ((($HTTP_POST_VARS['admin_pass1'] != $HTTP_POST_VARS['admin_pass2'])) ||
			(empty($HTTP_POST_VARS['admin_pass1']) || empty($dbhost)) && $HTTP_POST_VARS['cur_lang'] == $language)
		{
			$error = $lang['Password_mismatch'];
		}
	}

	$dirname = $phpbb_root_path . 'language';
	$dir = opendir($dirname);

	$lang_options = array();
	while ($file = readdir($dir))
	{
		if (preg_match('#^lang_#i', $file) && !is_file(@phpbb_realpath($dirname . '/' . $file)) && !is_link(@phpbb_realpath($dirname . '/' . $file)))
		{
			$filename = trim(str_replace('lang_', '', $file));
			$displayname = preg_replace('/^(.*?)_(.*)$/', '\1 [ \2 ]', $filename);
			$displayname = preg_replace('/\[(.*?)_(.*)\]/', '[ \1 - \2 ]', $displayname);
			$lang_options[$displayname] = $filename;
		}
	}

	closedir($dir);

	@asort($lang_options);
	@reset($lang_options);

	$lang_select = '<select name="lang" onchange="this.form.submit()">';
	while (list($displayname, $filename) = @each($lang_options))
	{
		$selected = ($language == $filename) ? ' selected="selected"' : '';
		$lang_select .= '<option value="' . $filename . '"' . $selected . '>' . ucwords($displayname) . '</option>';
	}
	$lang_select .= '</select>';

	$dbms_select = '<select name="dbms" onchange="if(this.form.upgrade.options[this.form.upgrade.selectedIndex].value == 1){ this.selectedIndex = 0;}">';
	while (list($dbms_name, $details) = @each($available_dbms))
	{
		$selected = ($dbms_name == $dbms) ? 'selected="selected"' : '';
		$dbms_select .= '<option value="' . $dbms_name . '">' . $details['LABEL'] . '</option>';
	}
	$dbms_select .= '</select>';

	$upgrade_option = '<select name="upgrade"';
	$upgrade_option .= 'onchange="if (this.options[this.selectedIndex].value == 1) { this.form.dbms.selectedIndex = 0; }">';
	$upgrade_option .= '<option value="0">' . $lang['Install'] . '</option>';
	$upgrade_option .= '<option value="1">' . $lang['Upgrade'] . '</option></select>';
	
	$s_hidden_fields = '<input type="hidden" name="install_step" value="1" /><input type="hidden" name="cur_lang" value="' . $language . '" />';

	page_header($instruction_text);

?>
					<tr>
						<th colspan="2"><?php echo $lang['Initial_config']; ?></th>
					</tr>
					<tr>
						<td class="row1" align="right" width="30%"><span class="gen"><?php echo $lang['Default_lang']; ?>: </span></td>
						<td class="row2"><?php echo $lang_select; ?></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen"><?php echo $lang['dbms']; ?>: </span></td>
						<td class="row2"><?php echo $dbms_select; ?></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen"><?php echo $lang['Install_Method']; ?>:</span></td>
						<td class="row2"><?php echo $upgrade_option; ?></td>
					</tr>
					<tr>
						<th colspan="2"><?php echo $lang['DB_config']; ?></th>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen"><?php echo $lang['DB_Host']; ?>: </span></td>
						<td class="row2"><input type="text" name="dbhost" value="<?php echo ($dbhost != '') ? $dbhost : ''; ?>" /></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen"><?php echo $lang['DB_Name']; ?>: </span></td>
						<td class="row2"><input type="text" name="dbname" value="<?php echo ($dbname != '') ? $dbname : ''; ?>" /></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen"><?php echo $lang['DB_Username']; ?>: </span></td>
						<td class="row2"><input type="text" name="dbuser" value="<?php echo ($dbuser != '') ? $dbuser : ''; ?>" /></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen"><?php echo $lang['DB_Password']; ?>: </span></td>
						<td class="row2"><input type="password" name="dbpasswd" value="<?php echo ($dbpasswd != '') ? $dbpasswd : ''; ?>" /></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen"><?php echo $lang['Table_Prefix']; ?>: </span></td>
						<td class="row2"><input type="text" name="prefix" value="<?php echo (!empty($table_prefix)) ? $table_prefix : "phpbb_"; ?>" /></td>
					</tr>
					<tr>
						<th colspan="2"><?php echo $lang['Admin_config']; ?></th>
					</tr>
<?php

	if ($error)
	{
?>
					<tr>
						<td class="row1" colspan="2" align="center"><span class="gen" style="color:red"><?php echo $error; ?></span></td>
					</tr>
<?php

	}
?>
					<tr>
						<td class="row1" align="right"><span class="gen"><?php echo $lang['Admin_email']; ?>: </span></td>
						<td class="row2"><input type="text" name="board_email" value="<?php echo ($board_email != '') ? $board_email : ''; ?>" /></td>
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
						<td class="row2"><input type="text" name="admin_name" value="<?php echo ($admin_name != '') ? $admin_name : ''; ?>" /></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen"><?php echo $lang['Admin_Password']; ?>: </span></td>
						<td class="row2"><input type="password" name="admin_pass1" value="<?php echo ($admin_pass1 != '') ? $admin_pass1 : ''; ?>" /></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen"><?php echo $lang['Admin_Password_confirm']; ?>: </span></td>
						<td class="row2"><input type="password" name="admin_pass2" value="<?php echo ($admin_pass2 != '') ? $admin_pass2 : ''; ?>" /></td>
					</tr>
<?php

	page_common_form($s_hidden_fields, $lang['Start_Install']);
	page_footer();
	exit;
}
else
{
	// Go ahead and create the DB, then populate it
	//
	// MS Access is slightly different in that a pre-built, pre-
	// populated DB is supplied, all we need do here is update
	// the relevant entries
	if (isset($dbms))
	{
		switch($dbms)
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

		if (!extension_loaded($check_exts) && !extension_loaded($check_other))
		{	
			page_header($lang['Install'], '');
			page_error($lang['Installer_Error'], $lang['Install_No_Ext']);
			page_footer();
			exit;
		}

		include($phpbb_root_path.'includes/db.'.$phpEx);
	}

	$dbms_schema = 'schemas/' . $available_dbms[$dbms]['SCHEMA'] . '_schema.sql';
	$dbms_basic = 'schemas/' . $available_dbms[$dbms]['SCHEMA'] . '_basic.sql';

	$remove_remarks = $available_dbms[$dbms]['COMMENTS'];;
	$delimiter = $available_dbms[$dbms]['DELIM']; 
	$delimiter_basic = $available_dbms[$dbms]['DELIM_BASIC']; 

	if ($install_step == 1)
	{
		if ($upgrade != 1)
		{
			if ($dbms != 'msaccess')
			{
				// Load in the sql parser
				include($phpbb_root_path.'includes/sql_parse.'.$phpEx);

				// Ok we have the db info go ahead and read in the relevant schema
				// and work on building the table.. probably ought to provide some
				// kind of feedback to the user as we are working here in order
				// to let them know we are actually doing something.
				$sql_query = @fread(@fopen($dbms_schema, 'r'), @filesize($dbms_schema));
				$sql_query = preg_replace('/phpbb_/', $table_prefix, $sql_query);

				$sql_query = $remove_remarks($sql_query);
				$sql_query = split_sql_file($sql_query, $delimiter);

				for ($i = 0; $i < sizeof($sql_query); $i++)
				{
					if (trim($sql_query[$i]) != '')
					{
						if (!($result = $db->sql_query($sql_query[$i])))
						{
							$error = $db->sql_error();
			
							page_header($lang['Install'], '');
							page_error($lang['Installer_Error'], $lang['Install_db_error'] . '<br />' . $error['message']);
							page_footer();
							exit;
						}
					}
				}
		
				// Ok tables have been built, let's fill in the basic information
				$sql_query = @fread(@fopen($dbms_basic, 'r'), @filesize($dbms_basic));
				$sql_query = preg_replace('/phpbb_/', $table_prefix, $sql_query);

				$sql_query = $remove_remarks($sql_query);
				$sql_query = split_sql_file($sql_query, $delimiter_basic);

				for($i = 0; $i < sizeof($sql_query); $i++)
				{
					if (trim($sql_query[$i]) != '')
					{
						if (!($result = $db->sql_query($sql_query[$i])))
						{
							$error = $db->sql_error();

							page_header($lang['Install'], '');
							page_error($lang['Installer_Error'], $lang['Install_db_error'] . '<br />' . $error['message']);
							page_footer();
							exit;
						}
					}
				}
			}

			// Ok at this point they have entered their admin password, let's go 
			// ahead and create the admin account with some basic default information
			// that they can customize later, and write out the config file.  After
			// this we are going to pass them over to the admin_forum.php script
			// to set up their forum defaults.
			$error = '';

			// Update the default admin user with their information.
			$sql = "INSERT INTO " . $table_prefix . "config (config_name, config_value) 
				VALUES ('board_startdate', " . time() . ")";
			if (!$db->sql_query($sql))
			{
				$error .= "Could not insert board_startdate :: " . $sql . " :: " . __LINE__ . " :: " . __FILE__ . "<br /><br />";
			}

			$sql = "INSERT INTO " . $table_prefix . "config (config_name, config_value) 
				VALUES ('default_lang', '" . str_replace("\'", "''", $language) . "')";
			if (!$db->sql_query($sql))
			{
				$error .= "Could not insert default_lang :: " . $sql . " :: " . __LINE__ . " :: " . __FILE__ . "<br /><br />";
			}

			$update_config = array(
				'board_email'	=> $board_email,
				'script_path'	=> $script_path,
				'server_port'	=> $server_port,
				'server_name'	=> $server_name,
			);

			while (list($config_name, $config_value) = each($update_config))
			{
				$sql = "UPDATE " . $table_prefix . "config 
					SET config_value = '$config_value' 
					WHERE config_name = '$config_name'";
				if (!$db->sql_query($sql))
				{
					$error .= "Could not insert default_lang :: " . $sql . " :: " . __LINE__ . " :: " . __FILE__ . "<br /><br />";
				}
			}

			$admin_pass_md5 = ($confirm && $userdata['user_level'] == ADMIN) ? $admin_pass1 : md5($admin_pass1);

			$sql = "UPDATE " . $table_prefix . "users 
				SET username = '" . str_replace("\'", "''", $admin_name) . "', user_password='" . str_replace("\'", "''", $admin_pass_md5) . "', user_lang = '" . str_replace("\'", "''", $language) . "', user_email='" . str_replace("\'", "''", $board_email) . "'
				WHERE username = 'Admin'";
			if (!$db->sql_query($sql))
			{
				$error .= "Could not update admin info :: " . $sql . " :: " . __LINE__ . " :: " . __FILE__ . "<br /><br />";
			}

			$sql = "UPDATE " . $table_prefix . "users 
				SET user_regdate = " . time();
			if (!$db->sql_query($sql))
			{
				$error .= "Could not update user_regdate :: " . $sql . " :: " . __LINE__ . " :: " . __FILE__ . "<br /><br />";
			}

			if ($error != '')
			{
				page_header($lang['Install'], '');
				page_error($lang['Installer_Error'], $lang['Install_db_error'] . '<br /><br />' . $error);
				page_footer();
				exit;
			}
		}

		if (!$upgrade_now)
		{
			// Write out the config file.
			$config_data = '<?php'."\n\n";
			$config_data .= "\n// phpBB 2.x auto-generated config file\n// Do not change anything in this file!\n\n";
			$config_data .= '$dbms = \'' . $dbms . '\';' . "\n\n";
			$config_data .= '$dbhost = \'' . $dbhost . '\';' . "\n";
			$config_data .= '$dbname = \'' . $dbname . '\';' . "\n";
			$config_data .= '$dbuser = \'' . $dbuser . '\';' . "\n";
			$config_data .= '$dbpasswd = \'' . $dbpasswd . '\';' . "\n\n";
			$config_data .= '$table_prefix = \'' . $table_prefix . '\';' . "\n\n";
			$config_data .= 'define(\'PHPBB_INSTALLED\', true);'."\n\n";	
			$config_data .= '?' . '>'; // Done this to prevent highlighting editors getting confused!

			@umask(0111);
			$no_open = FALSE;

			// Unable to open the file writeable do something here as an attempt
			// to get around that...
			if (!($fp = @fopen($phpbb_root_path . 'config.'.$phpEx, 'w')))
			{
				$s_hidden_fields = '<input type="hidden" name="config_data" value="' . htmlspecialchars($config_data) . '" />';

				if (@extension_loaded('ftp') && !defined('NO_FTP'))
				{
					page_header($lang['Unwriteable_config'] . '<p>' . $lang['ftp_option'] . '</p>');

?>
					<tr>
						<th colspan="2"><?php echo $lang['ftp_choose']; ?></th>
					</tr>
					<tr>
						<td class="row1" align="right" width="50%"><span class="gen"><?php echo $lang['Attempt_ftp']; ?></span></td>
						<td class="row2"><input type="radio" name="send_file" value="2"></td>
					</tr>
					<tr>
						<td class="row1" align="right" width="50%"><span class="gen"><?php echo $lang['Send_file']; ?></span></td>
						<td class="row2"><input type="radio" name="send_file" value="1"></td>
					</tr>
<?php 

				}
				else
				{
					page_header($lang['Unwriteable_config']);
					$s_hidden_fields .= '<input type="hidden" name="send_file" value="1" />';
				}

				if ($upgrade == 1)
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

					page_upgrade_form();

				}
				else
				{
					page_common_form($s_hidden_fields, $lang['Download_config']);
				}

				page_footer();
				exit;
			}

			$result = @fputs($fp, $config_data, strlen($config_data));

			@fclose($fp);
			$upgrade_now = $lang['upgrade_submit'];
		}

		// First off let's check and see if we are supposed to be doing an upgrade.
		if ($upgrade == 1 && $upgrade_now == $lang['upgrade_submit'])
		{
			define('INSTALLING', true);
			require('upgrade.'.$phpEx);
			exit;
		}

		// Ok we are basically done with the install process let's go on 
		// and let the user configure their board now. We are going to do
		// this by calling the admin_board.php from the normal board admin
		// section.
		$s_hidden_fields = '<input type="hidden" name="username" value="' . $admin_name . '" />';
		$s_hidden_fields .= '<input type="hidden" name="password" value="' . $admin_pass1 . '" />';
		$s_hidden_fields .= '<input type="hidden" name="redirect" value="admin/index.'.$phpEx.'" />';
		$s_hidden_fields .= '<input type="hidden" name="login" value="true" />';

		page_header($lang['Inst_Step_2'], '../login.'.$phpEx);
		page_common_form($s_hidden_fields, $lang['Finish_Install']);
		page_footer();
		exit;
	}
}

?>
