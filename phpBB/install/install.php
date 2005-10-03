<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/
define('IN_PHPBB', true);

// Error reporting level and runtime escaping
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(E_ALL);
set_magic_quotes_runtime(0);

@set_time_limit(120);

// Include essential scripts
$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'includes/functions.'.$phpEx);
include($phpbb_root_path . 'includes/session.'.$phpEx);
include($phpbb_root_path . 'includes/acm/acm_file.'.$phpEx);
include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);

// Be paranoid with passed vars
if (@ini_get('register_globals'))
{
	foreach ($_REQUEST as $var_name => $void)
	{
		unset(${$var_name});
	}
}

define('STRIP', (get_magic_quotes_gpc()) ? true : false);

// Instantiate classes for future use
$user = new user();
$auth = new auth();
$cache = new acm();


// Try opening config file
if (@file_exists($phpbb_root_path . 'config.'.$phpEx))
{
//	include($phpbb_root_path . 'config.'.$phpEx);

	if (defined('PHPBB_INSTALLED'))
	{
//		redirect("../index.$phpEx");
	}
}


// Obtain various vars
$stage = request_var('stage', 0);
//, 'acm_type'
// These are all strings so we'll just traverse an array
$var_ary = array('language', 'dbms', 'dbhost', 'dbport', 'dbuser', 'dbpasswd', 'dbname', 'table_prefix', 'admin_name', 'admin_pass1', 'admin_pass2', 'board_email1', 'board_email2', 'server_name', 'server_port', 'script_path', 'img_imagick', 'ftp_path', 'ftp_user', 'ftp_pass');

foreach ($var_ary as $var)
{
	$$var = request_var($var, '');
}

// Set some vars
define('ANONYMOUS', 1);

$error = array();

// Other PHP modules we may find useful
//$php_dlls_other	= array('zlib', 'mbstring', 'ftp');
$php_dlls_other	= array('zlib', 'ftp', 'xml', 'mhash');

// Supported DB layers including relevant details
$available_dbms = array(
	'firebird'	=> array(
		'LABEL'			=> 'FireBird',
		'SCHEMA'		=> 'firebird',
		'MODULE'		=> 'interbase', 
		'DELIM'			=> ';;',
		'COMMENTS'		=> 'remove_remarks'
	),
	'mysql'		=> array(
		'LABEL'			=> 'MySQL',
		'SCHEMA'		=> 'mysql',
		'MODULE'		=> 'mysql', 
		'DELIM'			=> ';',
		'COMMENTS'		=> 'remove_remarks'
	),
	'mysqli'	=> array(
		'LABEL'			=> 'MySQL 4.1.x (MySQLi)',
		'SCHEMA'		=> 'mysql',
		'MODULE'		=> 'mysqli', 
		'DELIM'			=> ';',
		'COMMENTS'		=> 'remove_remarks'
	),
	'mysql4'	=> array(
		'LABEL'			=> 'MySQL 4',
		'SCHEMA'		=> 'mysql',
		'MODULE'		=> 'mysql', 
		'DELIM'			=> ';',
		'COMMENTS'		=> 'remove_remarks'
	),
	'mssql'		=> array(
		'LABEL'			=> 'MS SQL Server 7/2000',
		'SCHEMA'		=> 'mssql',
		'MODULE'		=> 'mssql', 
		'DELIM'			=> 'GO',
		'COMMENTS'		=> 'remove_comments'
	),
	'mssql_odbc'=>	array(
		'LABEL'			=> 'MS SQL Server [ ODBC ]',
		'SCHEMA'		=> 'mssql',
		'MODULE'		=> 'odbc', 
		'DELIM'			=> 'GO',
		'COMMENTS'		=> 'remove_comments'
	),
	'oracle'	=>	array(
		'LABEL'			=> 'Oracle',
		'SCHEMA'		=> 'oracle',
		'MODULE'		=> 'oci8', 
		'DELIM'			=> '/',
		'COMMENTS'		=> 'remove_comments'
	),
	'postgres' => array(
		'LABEL'			=> 'PostgreSQL 7.x',
		'SCHEMA'		=> 'postgres',
		'MODULE'		=> 'pgsql', 
		'DELIM'			=> ';',
		'COMMENTS'		=> 'remove_comments'
	),
	'sqlite'		=> array(
		'LABEL'			=> 'SQLite',
		'SCHEMA'		=> 'sqlite',
		'MODULE'		=> 'sqlite', 
		'DELIM'			=> ';',
		'COMMENTS'		=> 'remove_remarks'
	),
);

$suffix = ((defined('PHP_OS')) && (preg_match('#win#i', PHP_OS))) ? 'dll' : 'so';


//
// Variables defined ... start program proper
//


// Try and load an appropriate language if required
if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && !$language)
{
	$accept_lang_ary = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
	foreach ($accept_lang_ary as $accept_lang)
	{
		// Set correct format ... guess full xx_YY form
		$accept_lang = substr($accept_lang, 0, 2) . '_' . strtoupper(substr($accept_lang, 3, 2));
		if (file_exists($phpbb_root_path . 'language/' . $accept_lang))
		{
			$language = $accept_lang;
			break;
		}
		else
		{
			// No match on xx_YY so try xx
			$accept_lang = substr($accept_lang, 0, 2);
			if (file_exists($phpbb_root_path . 'language/' . $accept_lang))
			{
				$language = $accept_lang;
				break;
			}
		}
	}
}

// No appropriate language found ... so let's use the first one in the language
// dir, this may or may not be English
if (!$language)
{
	$dir = @opendir($phpbb_root_path . 'language');
	while ($file = readdir($dir))
	{
		$path = $phpbb_root_path . 'language/' . $file;

		if (!is_file($path) && !is_link($path) && file_exists($path . '/iso.txt'))
		{
			$language = $file;
			break;
		}
	}
}

include($phpbb_root_path . 'language/' . $language . '/common.'.$phpEx);
include($phpbb_root_path . 'language/' . $language . '/admin.'.$phpEx);


// Here we do a number of tests and where appropriate reset the installation level
// depending on the outcome of those tests. It's perhaps a little clunky but
// it means we have a fairly clear and logical path through the installation and
// this source ... well, till I go and fill it with fudge ... damn, dribbled
// on my keyboard
if (isset($_POST['retest']))
{
	$stage = 0;
}
else if (isset($_POST['testdb']))
{
	$stage = 1;
}
else if (isset($_POST['install']))
{
	// Check for missing data
	$var_ary = array(
		'admin'		=> array('admin_name', 'admin_pass1', 'admin_pass2', 'board_email1', 'board_email2'),
		'server'	=> array('server_name', 'server_port', 'script_path')
	);

	foreach ($var_ary as $var_type => $var_block)
	{
		foreach ($var_block as $var)
		{
			if (!$$var)
			{
				$error[$var_type][] = $lang['INST_ERR_MISSING_DATA'];
				break;
			}
		}
	}

	// Check the entered email address and password
	if ($admin_pass1 != $admin_pass2 && $admin_pass1 != '')
	{
		$error['admin'][] = $lang['INST_ERR_PASSWORD_MISMATCH'];
	}

	if ($board_email1 != $board_email2 && $board_email1 != '')
	{
		$error['admin'][] = $lang['INST_ERR_EMAIL_MISMATCH'];
	}

	// Test the database connectivity
	if (!@extension_loaded($available_dbms[$dbms]['MODULE']))
	{
		if (!can_load_dll($available_dbms[$dbms]['MODULE']))
		{
			$error['db'][] = $lang['INST_ERR_NO_DB'];
		}
	}

	connect_check_db(false, $error, $dbms, $table_prefix, $dbhost, $dbuser, $dbpasswd, $dbname, $dbport);

	// No errors so lets do the twist
	if (sizeof($error))
	{
		$stage = 1;
	}
}
else if (isset($_POST['dldone']))
{
	// A minor fudge ... we're basically trying to see if the user uploaded
	// their downloaded config file ... it's not worth IMO trying to
	// open it and compare all the data. If a user wants to screw up this
	// simple task ... well ... uhm
	if (filesize($phpbb_root_path . 'config.'.$phpEx) < 10)
	{
		$stage = 2;
	}
	else
	{
		$stage = 3;
	}
}


// Zero stage of installation
//
// Here we basically imform the user of any potential issues such as no database
// support, missing directories, etc. We also give some insight into "missing"
// modules which we'd quite like installed (but which are not essential)
if ($stage == 0)
{
	// Test for DB modules
	$dlls_db = array();
	$passed['db'] = false;
	foreach ($available_dbms as $db_name => $db_ary)
	{
		$dll = $db_ary['MODULE'];

		if (!extension_loaded($dll))
		{
			if (!can_load_dll($dll))
			{
				$dlls_db[$db_name] = '<b style="color:red">' . $lang['UNAVAILABLE'] . '</b>';
				continue;
			}
		}
		$dlls_db[$db_name] = '<b style="color:green">' . $lang['AVAILABLE'] . '</b>';
		$passed['db'] = true;
	}

	// Test for other modules
	$dlls_other = array();
	foreach ($php_dlls_other as $dll)
	{
		if (!extension_loaded($dll))
		{
			if (!can_load_dll($dll))
			{
				$dlls_other[$dll] = '<b style="color:red">' . $lang['UNAVAILABLE'] . '</b>';
				continue;
			}
		}
		$dlls_other[$dll] = '<b style="color:green">' . $lang['AVAILABLE'] . '</b>';
	}

	inst_page_header();

?>

<h1><?php echo $lang['INSTALL_ADVICE']; ?></h1>

<p><?php echo $lang['INSTALL_ADVICE_EXPLAIN']; ?></p>

<hr />

<h1><?php echo $lang['PHP_AND_APPS']; ?></h1>

<h2><?php echo $lang['INSTALL_REQUIRED']; ?></h2>

<p><?php echo $lang['INSTALL_REQUIRED_PHP']; ?></p>

<table cellspacing="1" cellpadding="4" border="0"> 
	<tr>
		<td>&bull;&nbsp;<b><?php echo $lang['PHP_VERSION_REQD']; ?>: </b></td>
		<td><?php

	$php_version = phpversion();

	if (version_compare($php_version, '4.1.0') < 0)
	{
		$passed['db'] = false;
		echo '<b style="color:red">' . $lang['NO'] . '</b>';
	}
	else
	{
		// We also give feedback on whether we're running in safe mode
		echo '<b style="color:green">' . $lang['YES'];
		if (@ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'on')
		{
			echo ', ' . $lang['PHP_SAFE_MODE'];
		}
		echo '</b>';
	}

?></td>
	</tr>
	<tr>
		<td rowspan="<?php echo sizeof($dlls_db); ?>" valign="top">&bull;&nbsp;<b><?php echo $lang['PHP_REQD_DB']; ?>: </b></td>
<?php

	$i = 0;
	foreach ($dlls_db as $dll => $available)
	{
		echo ($i++ > 0) ? '<tr>' : '';

?>
		<td><?php echo $lang['DLL_' . strtoupper($dll)]; ?> </td>
		<td><?php echo $available; ?></td>
	</tr>
<?php

	}

?>
</table>

<h2><?php echo $lang['INSTALL_OPTIONAL']; ?></h2>

<p><?php echo $lang['INSTALL_OPTIONAL_PHP']; ?></p>

<table cellspacing="1" cellpadding="4" border="0"> 
	<tr>
<?php

	// Optional modules which may be useful to us
	foreach ($dlls_other as $dll => $yesno)
	{

?>
	<tr>
		<td>&bull;&nbsp;<b><?php echo $lang['DLL_' . strtoupper($dll)]; ?>: </b></td>
		<td><?php echo $yesno; ?></td>
	</tr>
<?php

	}

	$exe = ((defined('PHP_OS')) && (preg_match('#win#i', PHP_OS))) ? '.exe' : '';

	// Imagemagick are you there? Give me a sign or a path ...
	$img_imagick = '';
	if (empty($_ENV['MAGICK_HOME']))
	{
		$locations = array('C:/WINDOWS/', 'C:/WINNT/', 'C:/WINDOWS/SYSTEM/', 'C:/WINNT/SYSTEM/', 'C:/WINDOWS/SYSTEM32/', 'C:/WINNT/SYSTEM32/', '/usr/bin/', '/usr/sbin/', '/usr/local/bin/', '/usr/local/sbin/', '/opt/', '/usr/imagemagick/', '/usr/bin/imagemagick/');

		foreach ($locations as $location)
		{
			if (@is_readable($location . 'mogrify' . $exe) && @filesize($location . 'mogrify' . $exe) > 3000)
			{
				$img_imagick = str_replace('\\', '/', $location);
				continue;
			}
		}
	}
	else
	{
		$img_imagick = str_replace('\\', '/', $_ENV['MAGICK_HOME']);
	}

?>
	<tr>
		<td>&bull;&nbsp;<b><?php echo $lang['APP_MAGICK']; ?>: </b></td>
		<td><?php echo ($img_imagick) ? '<b style="color:green">' . $lang['AVAILABLE'] . ', ' . $img_imagick . '</b>' : '<b style="color:blue">' . $lang['NO_LOCATION'] . '</b>'; ?></td>
	</tr>
</table>

<h1 align="center" <?php echo ($passed['db']) ? 'style="color:green">' . $lang['TESTS_PASSED'] : 'style="color:red">' . $lang['TESTS_FAILED']; ?></h2>

<hr />

<h1><?php echo $lang['DIRECTORIES_AND_FILES']; ?></h2>

<h2><? echo $lang['INSTALL_REQUIRED']; ?></h2>

<p><?php echo $lang['INSTALL_REQUIRED_FILES']; ?></p>

<table cellspacing="1" cellpadding="4" border="0"> 
<?php

	$directories = array('cache/', 'files/', 'store/');

	umask(0);

	$passed['files'] = true;
	foreach ($directories as $dir)
	{
		$write = $exists = true;
		if (file_exists($phpbb_root_path . $dir))
		{
			if (!is_writeable($phpbb_root_path . $dir))
			{
				$write = (@chmod($phpbb_root_path . $dir, 0777)) ? true : false;
			}
		}
		else
		{
			$write = $exists = (@mkdir($phpbb_root_path . $dir, 0777)) ? true : false;
		}

		$passed['files'] = ($exists && $write && $passed['files']) ? true : false;

		$exists = ($exists) ? '<b style="color:green">' . $lang['FILE_FOUND'] . '</b>' : '<b style="color:red">' . $lang['FILE_NOT_FOUND'] . '</b>';
		$write = ($write) ? ', <b style="color:green">' . $lang['FILE_WRITEABLE'] . '</b>' : (($exists) ? ', <b style="color:red">' . $lang['FILE_UNWRITEABLE'] . '</b>' : '');

?>
	<tr>
		<td>&bull;&nbsp;<b><?php echo $dir; ?></b></td>
		<td><?php echo $exists . $write; ?></td>
	</tr>
<?php

	}

?>
</table>

<h2><?php echo $lang['INSTALL_OPTIONAL']; ?></h2>

<p><?php echo $lang['INSTALL_OPTIONAL_FILES']; ?></p>

<table cellspacing="1" cellpadding="4" border="0"> 
<?php

	// config.php ... let's just warn the user it's not writeable
	$dir = 'config.'.$phpEx;
	$write = $exists = true;
	if (file_exists($phpbb_root_path . $dir))
	{
		if (!is_writeable($phpbb_root_path . $dir))
		{
			$write = false;
		}
	}
	else
	{
		$write = $exists = false;
	}

	$exists = ($exists) ? '<b style="color:green">' . $lang['FILE_FOUND'] . '</b>' : '<b style="color:red">' . $lang['FILE_NOT_FOUND'] . '</b>';
	$write = ($write) ? ', <b style="color:green">' . $lang['FILE_WRITEABLE'] . '</b>' : (($exists) ? ', <b style="color:red">' . $lang['FILE_UNWRITEABLE'] . '</b>' : '');

?>
	<tr>
		<td>&bull;&nbsp;<b><?php echo $dir; ?></b></td>
		<td><?php echo $exists . $write; ?></td>
	</tr>
</table>

<h1 align="center" <?php echo ($passed['files']) ? 'style="color:green">' . $lang['TESTS_PASSED'] : 'style="color:red">' . $lang['TESTS_FAILED']; ?></h2>

<hr />

<h1><?php echo $lang['INSTALL_NEXT']; ?></h1>

<?php

	$next_text = ($passed['db'] && $passed['files']) ? $lang['INSTALL_NEXT_PASS'] : $lang['INSTALL_NEXT_FAIL'];

	$s_hidden_fields = ($img_imagick) ? '<input type="hidden" name="img_imagick" value="' . addslashes($img_imagick) . '" />' : '';

?>

<p><?php echo $next_text; ?></p>

<table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<td class="cat" colspan="2" align="center"><?php echo $s_hidden_fields; ?><input type="hidden" name="stage" value="1" /><input class="btnlite" name="retest" type="submit" value="<?php echo $lang['INSTALL_TEST']; ?>" /><?php 
	
	if ($passed['db'] && $passed['files'])
	{

?>&nbsp;&nbsp; <input class="btnmain" name="submit" type="submit" value="<?php echo $lang['INSTALL_NEXT']; ?>" /><?php
	
	}
	
?></td>
	</tr>
</table></form>

<?php

	inst_page_footer();
	exit;

}


// Second stage installation
//
// The basic tests have been passed so now we will proceed with asking
// the user for detailed information on their database, server, etc.
if ($stage == 1)
{
	// If the user has decided to test the connection to their database
	// then give it a go
	if (isset($_POST['testdb']))
	{
		// Let's try and load the required module if need be
		if (!@extension_loaded($available_dbms[$dbms]['MODULE']))
		{
			if (!can_load_dll($available_dbms[$dbms]['MODULE']))
			{
				$error['db'][] = $lang['INST_ERR_NO_DB'];;
			}
		}

		connect_check_db(true, $error, $dbms, $table_prefix, $dbhost, $dbuser, $dbpasswd, $dbname, $dbport);
	}


	$available_dbms_temp = array();
	foreach ($available_dbms as $type => $dbms_ary)
	{
		if (!extension_loaded($dbms_ary['MODULE']))
		{
			if (!can_load_dll($dbms_ary['MODULE']))
			{
				continue;
			}
		}

		$available_dbms_temp[$type] = $dbms_ary;
	}

	$available_dbms = &$available_dbms_temp;

	// Here we guess at some server information, however we only
	// do this if no "errors" exist ... if they do then the user
	// has relady set the info and we can bypass it
	if (!sizeof($error))
	{
		if (!empty($_SERVER['SERVER_NAME']) || !empty($_ENV['SERVER_NAME']))
		{
			$server_name = (!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : $_ENV['SERVER_NAME'];
		}
		else if (!empty($_SERVER['HTTP_HOST']) || !empty($_ENV['HTTP_HOST']))
		{
			$server_name = (!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : $_ENV['HTTP_HOST'];
		}
		else
		{
			$server_name = '';
		}

		if (!empty($_SERVER['SERVER_PORT']) || !empty($_ENV['SERVER_PORT']))
		{
			$server_port = (!empty($_SERVER['SERVER_PORT'])) ? $_SERVER['SERVER_PORT'] : $_ENV['SERVER_PORT'];
		}
		else
		{
			$server_port = '80';
		}

		$script_path = preg_replace('#install\/install\.' . $phpEx . '#i', '', $_SERVER['PHP_SELF']);
	}

	// Generate list of available DB's
	$dbms_options = '';
	foreach ($available_dbms as $dbms_name => $details)
	{
		$selected = ($dbms_name == $dbms) ? ' selected="selected"' : '';
		$dbms_options .= '<option value="' . $dbms_name . '"' . $selected .'>' . $details['LABEL'] . '</option>';
	}

	$s_hidden_fields = '<input type="hidden" name="stage" value="2" />';
	$s_hidden_fields .= ($img_imagick) ? '<input type="hidden" name="img_imagick" value="' . addslashes($img_imagick) . '" />' : '';;

	inst_page_header();

?>

<h1><?php echo $lang['INITIAL_CONFIG']; ?></h1>

<p><?php echo $lang['INITIAL_CONFIG_EXPLAIN']; ?></p>

<table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $lang['ADMIN_CONFIG']; ?></th>
	</tr>
<?php

	if (isset($error['admin']) && sizeof($error['admin']))
	{
?>
	<tr>
		<td class="row3" colspan="2" align="center"><span class="gen" style="color:red"><?php echo implode('<br />', $error['admin']); ?></span></td>
	</tr>
<?php

	}
?>
	<tr>
		<td class="row1" width="50%" width="50%"><b><?php echo $lang['DEFAULT_LANG']; ?>: </b></td>
		<td class="row2"><select name="lang"><?php echo inst_language_select($language); ?></select></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['ADMIN_USERNAME']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="admin_name" value="<?php echo ($admin_name != '') ? $admin_name : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['CONTACT_EMAIL']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="board_email1" value="<?php echo ($board_email1 != '') ? $board_email1 : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['CONTACT_EMAIL_CONFIRM']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="board_email2" value="<?php echo ($board_email2 != '') ? $board_email2 : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['ADMIN_PASSWORD']; ?>: </b></td>
		<td class="row2"><input class="post" type="password" name="admin_pass1" value="<?php echo ($admin_pass1 != '') ? $admin_pass1 : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['ADMIN_PASSWORD_CONFIRM']; ?>: </b></td>
		<td class="row2"><input class="post" type="password" name="admin_pass2" value="<?php echo ($admin_pass2 != '') ? $admin_pass2 : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2">&nbsp;</td>
	</tr>
</table>

<br clear="all" />

<table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $lang['DB_CONFIG']; ?></th>
	</tr>
<?php

	if (isset($error['db']) && sizeof($error['db']))
	{
?>
	<tr>
		<td class="row3" colspan="2" align="center"><span class="gen" style="color:red"><?php echo implode('<br />', $error['db']); ?></span></td>
	</tr>
<?php

	}
?>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['DBMS']; ?>: </b></td>
		<td class="row2"><select name="dbms" onchange="if (document.install_form.upgrade.options[upgrade.selectedIndex].value == 1) { document.install_form.dbms.selectedIndex=0}"><?php echo $dbms_options; ?></select></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['DB_HOST']; ?>: </b><br /><span class="gensmall"><?php echo $lang['DB_HOST_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="dbhost" value="<?php echo ($dbhost != '') ? $dbhost : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['DB_PORT']; ?>: </b><br /><span class="gensmall"><?php echo $lang['DB_PORT_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="dbport" value="<?php echo ($dbport != '') ? $dbport : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['DB_NAME']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="dbname" value="<?php echo ($dbname != '') ? $dbname : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['DB_USERNAME']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="dbuser" value="<?php echo ($dbuser != '') ? $dbuser : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['DB_PASSWORD']; ?>: </b></td>
		<td class="row2"><input class="post" type="password" name="dbpasswd" value="<?php echo ($dbpasswd != '') ? $dbpasswd : ''; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['TABLE_PREFIX']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="table_prefix" value="<?php echo (!empty($table_prefix)) ? $table_prefix : 'phpbb_'; ?>" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="btnlite" type="submit" name="testdb" value="<?php echo $lang['DB_TEST']; ?>" /></td>
	</tr>
</table>

<br clear="all" />

<table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $lang['SERVER_CONFIG']; ?></th>
	</tr>
<?php

	if (isset($error['server']) && sizeof($error['server']))
	{
?>
	<tr>
		<td class="row3" colspan="2" align="center"><span class="gen" style="color:red"><?php echo implode('<br />', $error['server']); ?></span></td>
	</tr>
<?php

	}
?>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['SERVER_NAME']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="server_name" value="<?php echo $server_name; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['SERVER_PORT']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="server_port" value="<?php echo $server_port; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['SCRIPT_PATH']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="script_path" value="<?php echo $script_path; ?>" /></td>
	</tr>
	<!-- tr>
		<td class="row1" width="50%"><b><?php echo $lang['CACHE_STORE']; ?>: </b><br /><span class="gensmall"><?php echo $lang['CACHE_STORE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="acm_type" value="db" <?php echo ($acm_type == 'db') ? 'checked="checked" ' : ''; ?>/> <?php echo $lang['STORE_DATABASE']; ?> <input type="radio" name="acm_type" value="file" <?php echo (!$acm_type || $acm_type == 'file') ? 'checked="checked" ' : ''; ?>/> <?php echo $lang['STORE_FILESYSTEM']; ?></td>
	</tr -->
	<tr>
		<td class="cat" colspan="2" align="center"><?php echo $s_hidden_fields; ?><input class="btnmain" name="install" type="submit" value="<?php echo $lang['INSTALL_START']; ?>" /></td>
	</tr>
</table></form>
<?php

	inst_page_footer();
	exit;
}


// Third stage
//
// Here we attempt to write out the config file. If we cannot write it directly
// we'll offer the user various options
if ($stage == 2)
{
	// Here we are checking to see one final time which modules
	// we need to load and whether we can load them. This includes
	// modules in addition to that required by the DB layer
	$load_extensions = array();
	$check_exts = array_merge(array($available_dbms[$dbms]['MODULE']), $php_dlls_other);

	foreach ($check_exts as $dll)
	{
		if (!extension_loaded($dll))
		{
			if (!can_load_dll($dll))
			{
				continue;
			}
			$load_extensions[] = "$dll.$suffix";
		}
	}

	$load_extensions = implode(',', $load_extensions);

	// Define the contents of config.php
	$config_data = "<?php\n";
	$config_data .= "// phpBB 2.x auto-generated config file\n// Do not change anything in this file!\n";
	$config_data .= "\$dbms = '$dbms';\n";
	$config_data .= "\$dbhost = '$dbhost';\n";
	$config_data .= "\$dbport = '$dbport';\n";
	$config_data .= "\$dbname = '$dbname';\n";
	$config_data .= "\$dbuser = '$dbuser';\n";
	$config_data .= "\$dbpasswd = '$dbpasswd';\n\n";
	$config_data .= "\$table_prefix = '$table_prefix';\n";
//	$config_data .= "\$acm_type = '" . (($acm_type) ? $acm_type : 'file') . "';\n";
	$config_data .= "\$acm_type = 'file';\n";
	$config_data .= "\$load_extensions = '$load_extensions';\n\n";
	$config_data .= "define('PHPBB_INSTALLED', true);\n";
	$config_data .= "define('DEBUG', true);\n"; // Comment out when final
	$config_data .= '?' . '>'; // Done this to prevent highlighting editors getting confused!

	// Attempt to write out the config directly ...
	if (filesize($phpbb_root_path . 'config.' . $phpEx) == 0 && is_writeable($phpbb_root_path . 'config.' . $phpEx))
	{
		// Lets jump to the DB setup stage ... if nothing goes wrong below
		$stage = 3;

		if (!($fp = @fopen($phpbb_root_path . 'config.'.$phpEx, 'w')))
		{
			// Something went wrong ... so let's try another method
			$stage = 2;
		}

		if (!(@fwrite($fp, $config_data)))
		{
			// Something went wrong ... so let's try another method
			$stage = 2;
		}

		@fclose($fp);
	}

	// We couldn't write it directly so we'll give the user three alternatives
	if ($stage == 2)
	{
		$ignore_ftp = false;

		// User is trying to upload via FTP ... so let's process it
		if (isset($_POST['sendftp']))
		{
			if (($conn_id = @ftp_connect('localhost')))
			{
				if (@ftp_login($conn_id, $ftp_user, $ftp_pass))
				{
					// Write out a temp file ... if safe mode is on we'll write it to our
					// local cache/tmp directory
					$tmp_path = (!@ini_get('safe_mode')) ? false : $phpbb_root_path . 'cache/tmp';
					$filename = tempnam($tmp_path, uniqid(rand()) . 'cfg');

					$fp = @fopen($filename, 'w');
					@fwrite($fp, $config_data);
					@fclose($fp);

					if (@ftp_chdir($conn_id, $ftp_dir))
					{
						// So far, so good so now we'll try and upload the file. If it
						// works we'll jump to stage 3, else we'll fall back again
						if (@ftp_put($conn_id, 'config.' . $phpEx, $filename, FTP_ASCII))
						{
							$stage = 3;
						}
						else
						{
							// Since we couldn't put the file something is fundamentally wrong, e.g.
							// the file is owned by a different user, etc. We'll give up trying
							// FTP at this point
							$ignore_ftp = true;
						}
					}
					else
					{
						$error['ftp'][] = $lang['INST_ERR_FTP_PATH'];
					}

					// Remove the temporary file now
					@unlink($filename);
				}
				else
				{
					$error['ftp'][] = $lang['INST_ERR_FTP_LOGIN'];
				}
				@ftp_quit($conn_id);
			}
		}
		else if (isset($_POST['dlftp']))
		{
			// The user requested a download, so send the relevant headers
			// and dump out the data
			header("Content-Type: text/x-delimtext; name=\"config.$phpEx\"");
			header("Content-disposition: attachment; filename=config.$phpEx");
			echo $config_data;
			exit;
		}

		// Here we give the users up to three options to complete the setup
		// of config.php, FTP, download and a retry and direct writing
		if ($stage == 2)
		{
			inst_page_header();

?>

<p><?php echo $lang['INSTALL_SEND_CONFIG']; ?></p>

<?php

			$s_hidden_fields = '<input type="hidden" name="stage" value="2" />';
			$s_hidden_fields .= '<input type="hidden" name="dbms" value="' . $dbms . '" />';
			$s_hidden_fields .= '<input type="hidden" name="table_prefix" value="' . $table_prefix . '" />';
			$s_hidden_fields .= '<input type="hidden" name="dbhost" value="' . $dbhost . '" />';
			$s_hidden_fields .= '<input type="hidden" name="dbport" value="' . $dbport . '" />';
			$s_hidden_fields .= '<input type="hidden" name="dbname" value="' . $dbname . '" />';
			$s_hidden_fields .= '<input type="hidden" name="dbuser" value="' . $dbuser . '" />';
			$s_hidden_fields .= '<input type="hidden" name="dbpasswd" value="' . $dbpasswd . '" />';
			$s_hidden_fields .= '<input type="hidden" name="admin_name" value="' . $admin_name . '" />';
			$s_hidden_fields .= '<input type="hidden" name="admin_pass1" value="' . $admin_pass1 . '" />';
			$s_hidden_fields .= '<input type="hidden" name="admin_pass2" value="' . $admin_pass2 . '" />';
			$s_hidden_fields .= '<input type="hidden" name="server_port" value="' . $server_port . '" />';
			$s_hidden_fields .= '<input type="hidden" name="server_name" value="' . $server_name . '" />';
			$s_hidden_fields .= '<input type="hidden" name="script_path" value="' . $script_path . '" />';
			$s_hidden_fields .= '<input type="hidden" name="board_email1" value="' . $board_email1 . '" />';
			$s_hidden_fields .= '<input type="hidden" name="board_email2" value="' . $board_email2 . '" />';
			$s_hidden_fields .= '<input type="hidden" name="language" value="' . $language . '" />';

			// Can we ftp? If we can then let's offer that option on top of download
			// We first see if the relevant extension is loaded and then whether a server is 
			// listening on the ftp port
			if (extension_loaded('ftp') && ($fsock = @fsockopen('localhost', 21, $errno, $errstr, 1)) && !$ignore_ftp)
			{
				@fclose($fsock);

?>

<h1><?php echo $lang['FTP_CONFIG']; ?></h1>

<p><?php echo $lang['FTP_CONFIG_EXPLAIN']; ?></p>

<table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2">&nbsp;</th>
	</tr>
<?php

				if (sizeof($error['ftp']))
				{

?>
	<tr>
		<td class="row3" colspan="2" align="center"><span class="gen" style="color:red"><?php echo implode('<br />', $error['ftp']); ?></span></td>
	</tr>
<?php

				}

?>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['FTP_PATH']; ?>: </b><br /><span class="gensmall"><?php echo $lang['FTP_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="ftp_dir" size="40" maxlength="255" value="<?php echo $ftp_dir; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['FTP_USERNAME']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="ftp_user" size="40" maxlength="255" value="<?php echo $ftp_user; ?>" ></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><b><?php echo $lang['FTP_PASSWORD']; ?>: </b></td>
		<td class="row2"><input class="post" type="password" name="ftp_pass" size="40" maxlength="255" value="<?php echo $ftp_pass; ?>" ></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="btnmain" name="sendftp" type="submit" value="<?php echo $lang['FTP_UPLOAD']; ?>" /></td>
	</tr>
</table>

<br />

<?php

			}

?>

<h1><?php echo $lang['DL_CONFIG']; ?></h1>

<p><?php echo $lang['DL_CONFIG_EXPLAIN']; ?></p>

<table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<td class="cat" align="center"><input class="btnmain" name="dlftp" type="submit" value="<?php echo $lang['DL_DOWNLOAD']; ?>" /> &nbsp;&nbsp; <input class="btnmain" name="dldone" type="submit" value="<?php echo $lang['DL_DONE']; ?>" /></td>
	</tr>
</table>

<br />

<h1><?php echo $lang['RETRY_WRITE']; ?></h1>

<p><?php echo $lang['RETRY_WRITE_EXPLAIN']; ?></p>

<table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<td class="cat" align="center"><input class="btnmain" name="retry" type="submit" value="<?php echo $lang['CONFIG_RETRY']; ?>" /></td>
	</tr>
</table>

<?php echo $s_hidden_fields; ?></form>

<?php

			inst_page_footer();
			exit;

		}
	}
}


// Everything should now be in place so we'll go ahead with the actual
// setup of the database. Hopefully nothing will go wrong from this
// point on ... it really shouldn't
if ($stage == 3)
{
	// If we get here and the extension isn't loaded we know that we
	// can go ahead and load it without fear of failure ... probably 
	if (!extension_loaded($available_dbms[$dbms]['MODULE']))
	{
		@dl($available_dbms[$dbms]['MODULE'] . ".$prefix");
	}

	// Load the appropriate database class if not already loaded
	include($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);

	// Instantiate the database
	$db = new $sql_db();
	$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false);

	// We ship the Access schema complete, we don't need to create tables nor
	// populate it (at this time ... this may change). So we skip this section
	if ($dbms != 'msaccess')
	{
		// NOTE: trigger_error does not work here.
		$db->return_on_error = true;

		// Ok we have the db info go ahead and read in the relevant schema
		// and work on building the table
		$dbms_schema = 'schemas/' . $available_dbms[$dbms]['SCHEMA'] . '_schema.sql';

		// How should we treat this schema?
		$remove_remarks = $available_dbms[$dbms]['COMMENTS'];
		$delimiter = $available_dbms[$dbms]['DELIM'];

		$sql_query = @fread(@fopen($dbms_schema, 'r'), @filesize($dbms_schema));
		$sql_query = preg_replace('#phpbb_#is', $table_prefix, $sql_query);

		$remove_remarks($sql_query);
		$sql_query = split_sql_file($sql_query, $delimiter);

		foreach ($sql_query as $sql)
		{
			$sql = trim(str_replace('|', ';', $sql));
			if (!$db->sql_query($sql))
			{
				$error = $db->sql_error();
				inst_db_error($error['message'], $sql, __LINE__, __FILE__);
			}
		}
		unset($sql_query);

		// Ok tables have been built, let's fill in the basic information
		$sql_query = fread(fopen('schemas/schema_data.sql', 'r'), filesize('schemas/schema_data.sql'));

		// Deal with any special comments, used at present for mssql set identity switching
		switch ($dbms)
		{
			case 'mssql':
			case 'mssql_odbc':
				$sql_query = preg_replace('#\# MSSQL IDENTITY (phpbb_[a-z_]+) (ON|OFF) \##s', 'SET IDENTITY_INSERT \1 \2', $sql_query);
				break;

			case 'postgres':
				$sql_query = preg_replace('#\# POSTGRES (BEGIN|COMMIT) \##s', '\1; ', $sql_query);
				break;

			default:
				//$sql_query = preg_replace('#\# MSSQL IDENTITY (phpbb_[a-z_]+) (ON|OFF) \##s', '', $sql_query);
		}

		$sql_query = preg_replace('#phpbb_#', $table_prefix, $sql_query);

		remove_remarks($sql_query);
		$sql_query = split_sql_file($sql_query, ';');

		foreach ($sql_query as $sql)
		{
			$sql = trim(str_replace('|', ';', $sql));
			if (!$db->sql_query($sql))
			{
				$error = $db->sql_error();
				inst_db_error($error['message'], $sql, __LINE__, __FILE__);
			}
		}
		unset($sql_query);
	}


	$current_time = time();

	// Set default config and post data, this applies to all DB's including Access
	$sql_ary = array(
		'INSERT INTO ' . $table_prefix . "config (config_name, config_value)
			VALUES ('board_startdate', $current_time)",

		'INSERT INTO ' . $table_prefix . "config (config_name, config_value)
			VALUES ('default_lang', '" . $db->sql_escape($language) . "')",

		'UPDATE ' . $table_prefix . "config
			SET config_value = '" . $db->sql_escape($img_imagick) . "'
			WHERE config_name = 'img_imagick'",

		'UPDATE ' . $table_prefix . "config
			SET config_value = '" . $db->sql_escape($server_name) . "'
			WHERE config_name = 'server_name'",

		'UPDATE ' . $table_prefix . "config
			SET config_value = '" . $db->sql_escape($server_port) . "'
			WHERE config_name = 'server_port'",

		'UPDATE ' . $table_prefix . "config
			SET config_value = '" . $db->sql_escape($script_path) . "'
			WHERE config_name = 'script_path'",

		'UPDATE ' . $table_prefix . "config
			SET config_value = '" . $db->sql_escape($board_email1) . "'
			WHERE config_name = 'board_email'",

		'UPDATE ' . $table_prefix . "config
			SET config_value = '" . $db->sql_escape($board_email1) . "'
			WHERE config_name = 'board_contact'",

		'UPDATE ' . $table_prefix . "config
			SET config_value = '" . $db->sql_escape($server_name) . "'
			WHERE config_name = 'cookie_domain'",

		'UPDATE ' . $table_prefix . "config
			SET config_value = '" . $db->sql_escape($admin_name) . "'
			WHERE config_name = 'newest_username'",

		'UPDATE ' . $table_prefix . "users
			SET username = '" . $db->sql_escape($admin_name) . "', user_password='" . $db->sql_escape(md5($admin_pass1)) . "', user_lang = '" . $db->sql_escape($language) . "', user_email='" . $db->sql_escape($board_email1) . "'
			WHERE username = 'Admin'",

		'UPDATE ' . $table_prefix . "moderator_cache
			SET username = '" . $db->sql_escape($admin_name) . "'
			WHERE username = 'Admin'",

		'UPDATE ' . $table_prefix . "forums
			SET forum_last_poster_name = '" . $db->sql_escape($admin_name) . "'
			WHERE forum_last_poster_name = 'Admin'",

		'UPDATE ' . $table_prefix . "topics
			SET topic_first_poster_name = '" . $db->sql_escape($admin_name) . "', topic_last_poster_name = '" . $db->sql_escape($admin_name) . "'
			WHERE topic_first_poster_name = 'Admin'
				OR topic_last_poster_name = 'Admin'",

		'UPDATE ' . $table_prefix . "users
			SET user_regdate = $current_time", 

		'UPDATE ' . $table_prefix . "posts
			SET post_time = $current_time", 

		'UPDATE ' . $table_prefix . "topics
			SET topic_time = $current_time, topic_last_post_time = $current_time", 

		'UPDATE ' . $table_prefix . "forums
			SET forum_last_post_time = $current_time", 
	);

	foreach ($sql_ary as $sql)
	{
		$sql = trim(str_replace('|', ';', $sql));

		if (!$db->sql_query($sql))
		{
			$error = $db->sql_error();
			inst_db_error($error['message'], $sql, __LINE__, __FILE__);
		}
	}

	$stage = 4;
}

// Install completed ... log the user in ... we're done
if ($stage == 4)
{
	// Load the basic configuration data
	define('SESSIONS_TABLE', $table_prefix . 'sessions');
	define('BOTS_TABLE', $table_prefix . 'bots');
	define('USERS_TABLE', $table_prefix . 'users');
	define('GROUPS_TABLE', $table_prefix . 'groups');
	define('BANLIST_TABLE', $table_prefix . 'banlist');
	define('CONFIG_TABLE', $table_prefix . 'config');
	define('USER_NORMAL', 0);
	define('USER_INACTIVE', 1);
	define('USER_IGNORE', 2);
	define('USER_FOUNDER', 3);
	

	$sql = "SELECT *
		FROM {$table_prefix}config";
	$result = $db->sql_query($sql);

	$config = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$config[$row['config_name']] = $row['config_value'];
	}
	$db->sql_freeresult($result);

	$user->session_begin();
	$auth->login($admin_name, $admin_pass1);

	inst_page_header();

?>

<h1 align="center"><?php echo $lang['INSTALL_CONGRATS']; ?></h1>


<p><?php echo sprintf($lang['INSTALL_CONGRATS_EXPLAIN'], '<a href="../docs/README.html" target="_blank">', '</a>'); ?></p>

<a href="<?php echo "../adm/index.$phpEx$SID"; ?>"><h2 align="center"><?php echo $lang['INSTALL_LOGIN']; ?></h2></a>

<?php

	$db->sql_close();

	inst_page_footer();
	exit;

}

exit;

// ---------
// FUNCTIONS
//

// Output page -> header
function inst_page_header()
{
	global $phpEx, $lang;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<link rel="stylesheet" href="../adm/subSilver.css" type="text/css">
<style type="text/css">
<!--
th		{ background-image: url('../adm/images/cellpic3.gif') }
td.cat	{ background-image: url('../adm/images/cellpic1.gif') }
//-->
</style>
<title><?php echo $lang['WELCOME_INSTALL']; ?></title>
</head>
<body>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td><img src="../adm/images/header_left.jpg" width="200" height="60" alt="phpBB Logo" title="phpBB Logo" border="0"/></td>
		<td width="100%" background="../adm/images/header_bg.jpg" height="60" align="right" nowrap="nowrap"><span class="maintitle"><?php echo $lang['WELCOME_INSTALL']; ?></span> &nbsp; &nbsp; &nbsp;</td>
	</tr>
</table>

<table width="85%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td><br clear="all" />

<form action="<?php echo "install.$phpEx"; ?>" name="installation" method="post">

<?php

}

function inst_db_error($error, $sql, $line, $file)
{
	global $lang, $db;

	inst_page_header();

?>

<h1 style="color:red;text-align:center"><?php echo $lang['INST_ERR_FATAL']; ?></h1>

<p><?php echo $lang['INST_ERR_FATAL_DB']; ?></p>

<p><?php echo "$file [ $line ]"; ?></p>

<p>SQL : <?php echo $sql; ?></p>

<p><b><?php echo $error; ?></b></p>

<?php

	$db->sql_close();
	inst_page_footer();
	exit;
}

// Output page -> footer
function inst_page_footer()
{

?>

<div class="copyright" align="center">Powered by phpBB 2.2 &copy; <a href="http://www.phpbb.com/" target="_phpbb" class="copyright">phpBB Group</a>, 2003</div>

		<br clear="all" /></td>
	</tr>
</table>

</body>
</html>
<?php

}

function inst_language_select($default = '')
{
	global $phpbb_root_path, $phpEx;

	$dir = @opendir($phpbb_root_path . 'language');

	while ($file = readdir($dir))
	{
		$path = $phpbb_root_path . 'language/' . $file;

		if (is_file($path) || is_link($path) || $file == '.' || $file == '..')
		{
			continue;
		}

		if (file_exists($path . '/iso.txt'))
		{
			list($displayname) = @file($path . '/iso.txt');
			$lang[$displayname] = $file;
		}
	}
	@closedir($dir);

	@asort($lang);
	@reset($lang);

	$user_select = '';
	foreach ($lang as $displayname => $filename)
	{
		$selected = (strtolower($default) == strtolower($filename)) ? ' selected="selected"' : '';
		$user_select .= '<option value="' . $filename . '"' . $selected . '>' . ucwords($displayname) . '</option>';
	}

	return $user_select;
}

function can_load_dll($dll)
{
	global $suffix;
return false;
	return ((@ini_get('enable_dl') || strtolower(@ini_get('enable_dl')) == 'on') && (!@ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'off') && @dl($dll . ".$suffix")) ? true : false;
}

function connect_check_db($error_connect, &$error, &$dbms, &$table_prefix, &$dbhost, &$dbuser, &$dbpasswd, &$dbname, &$dbport)
{
	global $phpbb_root_path, $phpEx, $config, $lang;

	// Include the DB layer
	include($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);

	// Instantiate it and set return on error true
	$db = new $sql_db();
	$db->sql_return_on_error(true);

	// Try and connect ...
	if (is_array($db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false)))
	{
		$db_error = $db->sql_error();
		$error['db'][] = $lang['INST_ERR_DB_CONNECT'] . '<br />' . (($db_error['message']) ? $db_error['message'] : $lang['INST_ERR_DB_NO_ERROR']);
	}
	else
	{
		switch ($dbms)
		{
			case 'mysql':
			case 'mysql4':
			case 'mysqli':
			case 'sqlite':
				$sql = "SHOW TABLES";
				$field = "Tables_in_{$dbname}";
				break;

			case 'mssql':
			case 'mssql_odbc':
				$sql = "SELECT name 
					FROM sysobjects 
					WHERE type='U'";
				$field = "name";
				break;

			case 'postgres':
				$sql = "SELECT relname 
					FROM pg_class 
					WHERE relkind = 'r' 
						AND relname NOT LIKE 'pg\_%'";
				$field = "relname";
				break;

			case 'firebird':
				$sql = 'SELECT rdb$relation_name
					FROM rdb$relations
					WHERE rdb$view_source is null
						AND rdb$system_flag = 0';
				$field = 'rdb$relation_name';
				break;

			case 'oracle':
				$sql = 'SELECT table_name FROM USER_TABLES';
				$field = 'table_name';
				break;
		}
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			// Likely matches for an existing phpBB installation
			$table_ary = array($table_prefix . 'attachments', $table_prefix . 'config', $table_prefix . 'sessions', $table_prefix . 'topics', $table_prefix . 'users');

			do
			{
				// All phpBB installations will at least have config else it won't
				// work
				if (in_array(strtolower($row[$field]), $table_ary))
				{
					$error['db'][] = $lang['INST_ERR_PREFIX'];
					break;
				}
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);

		$db->sql_close();
	}

	if ($error_connect && (!isset($error['db']) || !sizeof($error['db'])))
	{
		$error['db'][] = $lang['INSTALL_DB_CONNECT'];
	}
}
//
// FUNCTIONS
// ---------

?>