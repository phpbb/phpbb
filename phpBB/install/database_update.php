<?php
/** 
*
* @package install
* @version $Id$
* @copyright (c) 2006 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

$updates_to_version = '3.0.RC3';

if (defined('IN_PHPBB') && defined('IN_INSTALL'))
{
	return;
}

/**
*/
define('IN_PHPBB', true);
define('IN_INSTALL', true);

$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// Report all errors, except notices
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(E_ALL);

@set_time_limit(0);

// Include essential scripts
include($phpbb_root_path . 'config.' . $phpEx);

if (!isset($dbms))
{
	die("Please read: <a href='../docs/INSTALL.html'>INSTALL.html</a> before attempting to update.");
}

// Load Extensions
if (!empty($load_extensions))
{
	$load_extensions = explode(',', $load_extensions);

	foreach ($load_extensions as $extension)
	{
		@dl(trim($extension));
	}
}

// Include files
require($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.' . $phpEx);
require($phpbb_root_path . 'includes/cache.' . $phpEx);
require($phpbb_root_path . 'includes/template.' . $phpEx);
require($phpbb_root_path . 'includes/session.' . $phpEx);
require($phpbb_root_path . 'includes/auth.' . $phpEx);
require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

// If we are on PHP >= 6.0.0 we do not need some code
if (version_compare(PHP_VERSION, '6.0.0-dev', '>='))
{
	/**
	* @ignore
	*/
	define('STRIP', false);
}
else
{
	set_magic_quotes_runtime(0);
	define('STRIP', (get_magic_quotes_gpc()) ? true : false);
}

$user = new user();
$cache = new cache();
$db = new $sql_db();

// Connect to DB
$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, false);

// We do not need this any longer, unset for safety purposes
unset($dbpasswd);

$user->ip = (!empty($_SERVER['REMOTE_ADDR'])) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '';

$sql = "SELECT config_value
	FROM " . CONFIG_TABLE . "
	WHERE config_name = 'default_lang'";
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

$language = basename(request_var('language', ''));

if (!$language)
{
	$language = $row['config_value'];
}

if (!file_exists($phpbb_root_path . 'language/' . $language))
{
	die('No language found!');
}

// And finally, load the relevant language files
include($phpbb_root_path . 'language/' . $language . '/common.' . $phpEx);
include($phpbb_root_path . 'language/' . $language . '/acp/common.' . $phpEx);
include($phpbb_root_path . 'language/' . $language . '/install.' . $phpEx);

// Set PHP error handler to ours
//set_error_handler('msg_handler');

// Define some variables for the database update
$inline_update = (request_var('type', 0)) ? true : false;

// Database column types mapping
$dbms_type_map = array(
	'mysql_41'	=> array(
		'INT:'		=> 'int(%d)',
		'BINT'		=> 'bigint(20)',
		'UINT'		=> 'mediumint(8) UNSIGNED',
		'UINT:'		=> 'int(%d) UNSIGNED',
		'TINT:'		=> 'tinyint(%d)',
		'USINT'		=> 'smallint(4) UNSIGNED',
		'BOOL'		=> 'tinyint(1) UNSIGNED',
		'VCHAR'		=> 'varchar(255)',
		'VCHAR:'	=> 'varchar(%d)',
		'CHAR:'		=> 'char(%d)',
		'XSTEXT'	=> 'text',
		'XSTEXT_UNI'=> 'varchar(100)',
		'STEXT'		=> 'text',
		'STEXT_UNI'	=> 'varchar(255)',
		'TEXT'		=> 'text',
		'TEXT_UNI'	=> 'text',
		'MTEXT'		=> 'mediumtext',
		'MTEXT_UNI'	=> 'mediumtext',
		'TIMESTAMP'	=> 'int(11) UNSIGNED',
		'DECIMAL'	=> 'decimal(5,2)',
		'VCHAR_UNI'	=> 'varchar(255)',
		'VCHAR_UNI:'=> 'varchar(%d)',
		'VCHAR_CI'	=> 'varchar(255)',
		'VARBINARY'	=> 'varbinary(255)',
	),

	'mysql_40'	=> array(
		'INT:'		=> 'int(%d)',
		'BINT'		=> 'bigint(20)',
		'UINT'		=> 'mediumint(8) UNSIGNED',
		'UINT:'		=> 'int(%d) UNSIGNED',
		'TINT:'		=> 'tinyint(%d)',
		'USINT'		=> 'smallint(4) UNSIGNED',
		'BOOL'		=> 'tinyint(1) UNSIGNED',
		'VCHAR'		=> 'varbinary(255)',
		'VCHAR:'	=> 'varbinary(%d)',
		'CHAR:'		=> 'binary(%d)',
		'XSTEXT'	=> 'blob',
		'XSTEXT_UNI'=> 'blob',
		'STEXT'		=> 'blob',
		'STEXT_UNI'	=> 'blob',
		'TEXT'		=> 'blob',
		'TEXT_UNI'	=> 'blob',
		'MTEXT'		=> 'mediumblob',
		'MTEXT_UNI'	=> 'mediumblob',
		'TIMESTAMP'	=> 'int(11) UNSIGNED',
		'DECIMAL'	=> 'decimal(5,2)',
		'VCHAR_UNI'	=> 'blob',
		'VCHAR_UNI:'=> array('varbinary(%d)', 'limit' => array('mult', 3, 255, 'blob')),
		'VCHAR_CI'	=> 'blob',
		'VARBINARY'	=> 'varbinary(255)',
	),

	'firebird'	=> array(
		'INT:'		=> 'INTEGER',
		'BINT'		=> 'DOUBLE PRECISION',
		'UINT'		=> 'INTEGER',
		'UINT:'		=> 'INTEGER',
		'TINT:'		=> 'INTEGER',
		'USINT'		=> 'INTEGER',
		'BOOL'		=> 'INTEGER',
		'VCHAR'		=> 'VARCHAR(255) CHARACTER SET NONE',
		'VCHAR:'	=> 'VARCHAR(%d) CHARACTER SET NONE',
		'CHAR:'		=> 'CHAR(%d) CHARACTER SET NONE',
		'XSTEXT'	=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
		'STEXT'		=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
		'TEXT'		=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
		'MTEXT'		=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
		'XSTEXT_UNI'=> 'VARCHAR(100) CHARACTER SET UTF8',
		'STEXT_UNI'	=> 'VARCHAR(255) CHARACTER SET UTF8',
		'TEXT_UNI'	=> 'BLOB SUB_TYPE TEXT CHARACTER SET UTF8',
		'MTEXT_UNI'	=> 'BLOB SUB_TYPE TEXT CHARACTER SET UTF8',
		'TIMESTAMP'	=> 'INTEGER',
		'DECIMAL'	=> 'DOUBLE PRECISION',
		'VCHAR_UNI'	=> 'VARCHAR(255) CHARACTER SET UTF8',
		'VCHAR_UNI:'=> 'VARCHAR(%d) CHARACTER SET UTF8',
		'VCHAR_CI'	=> 'VARCHAR(255) CHARACTER SET UTF8',
		'VARBINARY'	=> 'CHAR(255) CHARACTER SET NONE',
	),

	'mssql'		=> array(
		'INT:'		=> '[int]',
		'BINT'		=> '[float]',
		'UINT'		=> '[int]',
		'UINT:'		=> '[int]',
		'TINT:'		=> '[int]',
		'USINT'		=> '[int]',
		'BOOL'		=> '[int]',
		'VCHAR'		=> '[varchar] (255)',
		'VCHAR:'	=> '[varchar] (%d)',
		'CHAR:'		=> '[char] (%d)',
		'XSTEXT'	=> '[varchar] (1000)',
		'STEXT'		=> '[varchar] (3000)',
		'TEXT'		=> '[varchar] (8000)',
		'MTEXT'		=> '[text]',
		'XSTEXT_UNI'=> '[varchar] (100)',
		'STEXT_UNI'	=> '[varchar] (255)',
		'TEXT_UNI'	=> '[varchar] (4000)',
		'MTEXT_UNI'	=> '[text]',
		'TIMESTAMP'	=> '[int]',
		'DECIMAL'	=> '[float]',
		'VCHAR_UNI'	=> '[varchar] (255)',
		'VCHAR_UNI:'=> '[varchar] (%d)',
		'VCHAR_CI'	=> '[varchar] (255)',
		'VARBINARY'	=> '[varchar] (255)',
	),

	'oracle'	=> array(
		'INT:'		=> 'number(%d)',
		'BINT'		=> 'number(20)',
		'UINT'		=> 'number(8)',
		'UINT:'		=> 'number(%d)',
		'TINT:'		=> 'number(%d)',
		'USINT'		=> 'number(4)',
		'BOOL'		=> 'number(1)',
		'VCHAR'		=> 'varchar2(255)',
		'VCHAR:'	=> 'varchar2(%d)',
		'CHAR:'		=> 'char(%d)',
		'XSTEXT'	=> 'varchar2(1000)',
		'STEXT'		=> 'varchar2(3000)',
		'TEXT'		=> 'clob',
		'MTEXT'		=> 'clob',
		'XSTEXT_UNI'=> 'varchar2(300)',
		'STEXT_UNI'	=> 'varchar2(765)',
		'TEXT_UNI'	=> 'clob',
		'MTEXT_UNI'	=> 'clob',
		'TIMESTAMP'	=> 'number(11)',
		'DECIMAL'	=> 'number(5, 2)',
		'VCHAR_UNI'	=> 'varchar2(765)',
		'VCHAR_UNI:'=> array('varchar2(%d)', 'limit' => array('mult', 3, 765, 'clob')),
		'VCHAR_CI'	=> 'varchar2(255)',
		'VARBINARY'	=> 'raw(255)',
	),

	'sqlite'	=> array(
		'INT:'		=> 'int(%d)',
		'BINT'		=> 'bigint(20)',
		'UINT'		=> 'INTEGER UNSIGNED', //'mediumint(8) UNSIGNED',
		'UINT:'		=> 'INTEGER UNSIGNED', // 'int(%d) UNSIGNED',
		'TINT:'		=> 'tinyint(%d)',
		'USINT'		=> 'INTEGER UNSIGNED', //'mediumint(4) UNSIGNED',
		'BOOL'		=> 'INTEGER UNSIGNED', //'tinyint(1) UNSIGNED',
		'VCHAR'		=> 'varchar(255)',
		'VCHAR:'	=> 'varchar(%d)',
		'CHAR:'		=> 'char(%d)',
		'XSTEXT'	=> 'text(65535)',
		'STEXT'		=> 'text(65535)',
		'TEXT'		=> 'text(65535)',
		'MTEXT'		=> 'mediumtext(16777215)',
		'XSTEXT_UNI'=> 'text(65535)',
		'STEXT_UNI'	=> 'text(65535)',
		'TEXT_UNI'	=> 'text(65535)',
		'MTEXT_UNI'	=> 'mediumtext(16777215)',
		'TIMESTAMP'	=> 'INTEGER UNSIGNED', //'int(11) UNSIGNED',
		'DECIMAL'	=> 'decimal(5,2)',
		'VCHAR_UNI'	=> 'varchar(255)',
		'VCHAR_UNI:'=> 'varchar(%d)',
		'VCHAR_CI'	=> 'varchar(255)',
		'VARBINARY'	=> 'blob',
	),

	'postgres'	=> array(
		'INT:'		=> 'INT4',
		'BINT'		=> 'INT8',
		'UINT'		=> 'INT4', // unsigned
		'UINT:'		=> 'INT4', // unsigned
		'USINT'		=> 'INT2', // unsigned
		'BOOL'		=> 'INT2', // unsigned
		'TINT:'		=> 'INT2',
		'VCHAR'		=> 'varchar(255)',
		'VCHAR:'	=> 'varchar(%d)',
		'CHAR:'		=> 'char(%d)',
		'XSTEXT'	=> 'varchar(1000)',
		'STEXT'		=> 'varchar(3000)',
		'TEXT'		=> 'varchar(8000)',
		'MTEXT'		=> 'TEXT',
		'XSTEXT_UNI'=> 'varchar(100)',
		'STEXT_UNI'	=> 'varchar(255)',
		'TEXT_UNI'	=> 'varchar(4000)',
		'MTEXT_UNI'	=> 'TEXT',
		'TIMESTAMP'	=> 'INT4', // unsigned
		'DECIMAL'	=> 'decimal(5,2)',
		'VCHAR_UNI'	=> 'varchar(255)',
		'VCHAR_UNI:'=> 'varchar(%d)',
		'VCHAR_CI'	=> 'varchar_ci',
		'VARBINARY'	=> 'bytea',
	),
);

// A list of types being unsigned for better reference in some db's
$unsigned_types = array('UINT', 'UINT:', 'USINT', 'BOOL', 'TIMESTAMP');

// Only an example, but also commented out
$database_update_info = array(
	// Changes from 3.0.RC1 to the next version
	'3.0.RC1'			=> array(
		// Remove the following keys
		'drop_keys'		=> array(
			STYLES_IMAGESET_DATA_TABLE	=> array(
				'i_id',
			),
			ACL_ROLES_DATA_TABLE		=> array(
				'ath_opt_id',
			),
		),
		// Add the following keys
		'add_index'		=> array(
			STYLES_IMAGESET_DATA_TABLE	=> array(
				'i_d'			=> array('imageset_id'),
			),
			ACL_ROLES_DATA_TABLE		=> array(
				'ath_opt_id'	=> array('auth_option_id'),
			),
		),
	),
	// Changes from 3.0.RC2 to the next version
	'3.0.RC2'			=> array(
		// Change the following columns
		'change_columns'		=> array(
			BANLIST_TABLE	=> array(
				'ban_reason'		=> array('VCHAR_UNI', ''),
				'ban_give_reason'	=> array('VCHAR_UNI', ''),
			),
		),
	),
	// Changes from 3.0.RC3 to the next version
	'3.0.RC3'			=> array(
		// Change the following columns
		'change_columns'		=> array(
			BANLIST_TABLE				=> array(
				'ban_reason'		=> array('VCHAR_UNI', ''),
				'ban_give_reason'	=> array('VCHAR_UNI', ''),
			),
			STYLES_TABLE				=> array(
				'style_id'			=> array('USINT', 0),
				'template_id'		=> array('USINT', 0),
				'theme_id'			=> array('USINT', 0),
				'imageset_id'		=> array('USINT', 0),
			),
			STYLES_TEMPLATE_TABLE		=> array(
				'template_id'		=> array('USINT', 0),
			),
			STYLES_TEMPLATE_DATA_TABLE	=> array(
				'template_id'		=> array('USINT', 0),
			),
			STYLES_THEME_TABLE			=> array(
				'theme_id'			=> array('USINT', 0),
			),
			STYLES_IMAGESET_TABLE		=> array(
				'imageset_id'		=> array('USINT', 0),
			),
			STYLES_IMAGESET_DATA_TABLE	=> array(
				'imageset_id'		=> array('USINT', 0),
			),
			USERS_TABLE	=> array(
				'user_style'		=> array('USINT', 0),
			),
			FORUMS_TABLE				=> array(
				'forum_style'		=> array('USINT', 0),
			),
		),
	),
);

// Determine mapping database type
switch ($db->sql_layer)
{
	case 'mysql':
		$map_dbms = 'mysql_40';
	break;

	case 'mysql4':
		if (version_compare($db->mysql_version, '4.1.3', '>='))
		{
			$map_dbms = 'mysql_41';
		}
		else
		{
			$map_dbms = 'mysql_40';
		}
	break;

	case 'mysqli':
		$map_dbms = 'mysql_41';
	break;

	case 'mssql':
	case 'mssql_odbc':
		$map_dbms = 'mssql';
	break;

	default:
		$map_dbms = $db->sql_layer;
	break;
}

$error_ary = array();
$errored = false;

header('Content-type: text/html; charset=UTF-8');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $lang['DIRECTION']; ?>" lang="<?php echo $lang['USER_LANG']; ?>" xml:lang="<?php echo $lang['USER_LANG']; ?>">
<head>

<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="content-language" content="<?php echo $lang['USER_LANG']; ?>" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="imagetoolbar" content="no" />

<title><?php echo $lang['UPDATING_TO_LATEST_STABLE']; ?></title>

<link href="../adm/style/admin.css" rel="stylesheet" type="text/css" media="screen" />

</head>

<body>
<div id="wrap">
	<div id="page-header">&nbsp;</div>

	<div id="page-body">
		<div id="acp">
		<div class="panel">
			<span class="corners-top"><span></span></span>
				<div id="content">
					<div id="main">

	<h1><?php echo $lang['UPDATING_TO_LATEST_STABLE']; ?></h1>

	<br />

	<p><?php echo $lang['DATABASE_TYPE']; ?> :: <strong><?php echo $db->sql_layer; ?></strong><br />
<?php

// To let set_config() calls succeed, we need to make the config array available globally
$config = array();
$sql = 'SELECT *
	FROM ' . CONFIG_TABLE;
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$config[$row['config_name']] = $row['config_value'];
}
$db->sql_freeresult($result);


echo $lang['PREVIOUS_VERSION'] . ' :: <strong>' . $config['version'] . '</strong><br />';
echo $lang['UPDATED_VERSION'] . ' :: <strong>' . $updates_to_version . '</strong>';

$current_version = str_replace('rc', 'RC', strtolower($config['version']));
$latest_version = str_replace('rc', 'RC', strtolower($updates_to_version));
$orig_version = $config['version'];

// If the latest version and the current version are 'unequal', we will update the version_update_from, else we do not update anything.
if ($inline_update)
{
	if ($current_version !== $latest_version)
	{
		set_config('version_update_from', $orig_version);
	}
}
else
{
	// If not called from the update script, we will actually remove the traces
	$db->sql_query('DELETE FROM ' . CONFIG_TABLE . " WHERE config_name = 'version_update_from'");
}

// Checks/Operations that have to be completed prior to starting the update itself
$exit = false;
if (version_compare($current_version, '3.0.RC3', '<='))
{
/*	</p>

	<h1>Clean Usernames</h1>

	<br />
	<p>Please note that this can take a while... Please do not stop the script.</p>
*/

	flush();

	$submit			= (isset($_POST['resolve_conflicts'])) ? true : false;
	$modify_users	= request_var('modify_users', array(0 => ''));
	$new_usernames	= request_var('new_usernames', array(0 => ''), true);

	// the admin decided to change some usernames
	if (sizeof($modify_users) && $submit)
	{
		$sql = 'SELECT user_id, username, user_type
			FROM ' . USERS_TABLE . '
			WHERE ' . $db->sql_in_set('user_id', array_keys($modify_users));
		$result = $db->sql_query($sql);

		$users = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			$users++;
			$user_id = (int) $row['user_id'];

			if (isset($modify_users[$user_id]))
			{
				$row['action'] = $modify_users[$user_id];
				$modify_users[$user_id] = $row;
			}
		}
		$db->sql_freeresult($result);

		// only if all ids really existed
		if (sizeof($modify_users) == $users)
		{
			$user->data['user_id'] = ANONYMOUS;
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
			foreach ($modify_users as $user_id => $row)
			{
				switch ($row['action'])
				{
					case 'edit':
						if (isset($new_usernames[$user_id]))
						{
							$data = array('username' => utf8_normalize_nfc($new_usernames[$user_id]));
							// Need to update config, forum, topic, posting, messages, etc.
							if ($data['username'] != $row['username'])
							{
								$check_ary = array('username' => array(
									array('string', false, $config['min_name_chars'], $config['max_name_chars']),
									array('username'),
								));
								// need a little trick for this to work properly
								$user->data['username_clean'] = utf8_clean_string($data['username']) . 'a';
								$errors = validate_data($data, $check_ary);

								if ($errors)
								{
									include($phpbb_root_path . 'language/' . $language . '/ucp.' . $phpEx);
									echo '<div class="errorbox">';
									foreach ($errors as $error)
									{
										echo '<p>' . $lang[$error] . '</p>';
									}
									echo '</div>';
								}

								if (!$errors)
								{
									// We use utf8_new_clean_string() here to make sure the new one is really used.
									$sql = 'UPDATE ' . USERS_TABLE . '
										SET ' . $db->sql_build_array('UPDATE', array(
												'username' => $data['username'],
												'username_clean' => utf8_new_clean_string($data['username'])
											)) . '
										WHERE user_id = ' . $user_id;
									$db->sql_query($sql);

									add_log('user', $user_id, 'LOG_USER_UPDATE_NAME', $row['username'], $data['username']);
									user_update_name($row['username'], $data['username']);
								}
							}
						}
					break;

					case 'delete_retain':
					case 'delete_remove':
						if ($user_id != ANONYMOUS && $row['user_type'] != USER_FOUNDER)
						{
							user_delete(substr($row['action'], 7), $user_id, $row['username']);
							add_log('admin', 'LOG_USER_DELETED', $row['username']);
						}
					break;
				}
			}
		}
	}

	// after RC3 a different utf8_clean_string function is used, this requires that
	// the unique column username_clean is recalculated, during this recalculation
	// duplicates might be created. Since the column has to be unique such usernames
	// must not exist. We need identify them and let the admin decide what to do
	// about them.
	$sql = 'SELECT user_id, username, username_clean
		FROM ' . USERS_TABLE . '
		ORDER BY user_id';
	$result = $db->sql_query($sql);

	$colliding_users = $found_names = array();
	while ($row = $db->sql_fetchrow($result))
	{
		// Calculate the new clean name. If it differs from the old one we need
		// to make sure there is no collision
		$clean_name = utf8_new_clean_string($row['username']);
		if ($clean_name != $row['username_clean'])
		{
			$user_id = (int) $row['user_id'];

			// If this clean name was not the result of another user already ...
			if (!isset($found_names[$clean_name]))
			{
				// then we need to figure out whether there are any other users
				// who already had this clean name with the old version
				$sql = 'SELECT user_id, username
					FROM ' . USERS_TABLE . '
					WHERE username_clean = \'' . $db->sql_escape($clean_name) . '\'';
				$result2 = $db->sql_query($sql);

				$user_ids = array($user_id);
				while ($row = $db->sql_fetchrow($result2))
				{
					// Make sure this clean name will still be the same with the
					// new function. If it is, then we have to add it to the list
					// of user ids for this clean name
					if (utf8_new_clean_string($row['username']) == $clean_name)
					{
						$user_ids[] = (int) $row['user_id'];
					}
				}
				$db->sql_freeresult($result2);

				// if we already found a collision save it
				if (sizeof($user_ids) > 1)
				{
					$colliding_users[$clean_name] = $user_ids;
					$found_names[$clean_name] = true;
				}
				else
				{
					// otherwise just mark this name as found
					$found_names[$clean_name] = $user_id;
				}
			}
			// Else, if we already found the username
			else
			{
				// If the value in the found_names lookup table is only true ...
				if ($found_names[$clean_name] === true)
				{
					// then the actual data was already added to $colliding_users
					// and we only need to append the user_id
					$colliding_users[$clean_name][] = $user_id;
				}
				else
				{
					// otherwise it still keeps the first user_id for this name
					// and we need to move the data to $colliding_users, and set
					// the value in the found_names lookup table to true, so
					// following users will directly be appended to $colliding_users
					$colliding_users[$clean_name] = array($found_names[$clean_name], $user_id);
					$found_names[$clean_name] = true;
				}
			}
		}
	}
	unset($found_names);
	$db->sql_freeresult($result);

	// now retrieve all information about the users and let the admin decide what to do
	if (sizeof($colliding_users))
	{
		$exit = true;
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		include($phpbb_root_path . 'language/' . $language . '/memberlist.' . $phpEx);
		include($phpbb_root_path . 'language/' . $language . '/acp/users.' . $phpEx);

		// link a few things to the correct place so we don't get any problems
		$user->lang = &$lang;
		$user->data['user_id'] = ANONYMOUS;
		$user->date_format = $config['default_dateformat'];

		// a little trick to get all user_ids
		$user_ids = call_user_func_array('array_merge', array_values($colliding_users));

		$sql = 'SELECT session_user_id, MAX(session_time) AS session_time
			FROM ' . SESSIONS_TABLE . '
			WHERE session_time >= ' . (time() - $config['session_length']) . '
				AND ' . $db->sql_in_set('session_user_id', $user_ids) . '
			GROUP BY session_user_id';
		$result = $db->sql_query($sql);

		$session_times = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$session_times[$row['session_user_id']] = $row['session_time'];
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT *
			FROM ' . USERS_TABLE . '
			WHERE ' . $db->sql_in_set('user_id', $user_ids);
		$result = $db->sql_query($sql);

		$users = array();
		while ($row = $db->sql_fetchrow($result))
		{
			if (isset($session_times[$row['user_id']]))
			{
				$row['session_time'] = $session_times[$row['user_id']];
			}
			else
			{
				$row['session_time'] = 0;
			}
			$users[(int) $row['user_id']] = $row;
		}
		$db->sql_freeresult($result);
		unset($session_times);

		// now display a table with all users, some information about them and options
		// for the admin: keep name, change name (with text input) or delete user
		$u_action = "database_update.$phpEx?language=$language&amp;type=$inline_update";
?>
<p><?php echo $lang['CHANGE_CLEAN_NAMES']; ?></p>
<form id="change_clean_names" method="post" action="<?php echo $u_action; ?>">


<?php
		foreach ($colliding_users as $clean_name => $user_ids)
		{
?>
	<fieldset class="tabulated">
		<table>
			<caption><?php echo sprintf($lang['COLLIDING_CLEAN_USERNAME'], $clean_name); ?></caption>
			<thead>
				<tr>
					<th><?php echo $lang['RANK']; ?> <?php echo $lang['USERNAME']; ?></th>
					<th><?php echo $lang['POSTS']; ?></th>
					<th><?php echo $lang['INFORMATION']; ?></th>
					<th><?php echo $lang['JOINED']; ?></th>
					<th><?php echo $lang['LAST_ACTIVE']; ?></th>
					<th><?php echo $lang['ACTION']; ?></th>
					<th><?php echo $lang['NEW_USERNAME']; ?></th>
				</tr>
			</thead>
			<tbody>
<?php
			foreach ($user_ids as $i => $user_id)
			{
				$row = $users[$user_id];
				
				$rank_title = $rank_img = '';
				get_user_rank($row['user_rank'], $row['user_posts'], $rank_title, $rank_img, $rank_img_src);

				$last_visit = (!empty($row['session_time'])) ? $row['session_time'] : $row['user_lastvisit'];

				$info = '';
				switch ($row['user_type'])
				{
					case USER_INACTIVE:
						$info .= $lang['USER_INACTIVE'];
					break;

					case USER_IGNORE:
						$info .= $lang['BOT'];
					break;

					case USER_FOUNDER:
						$info .= $lang['FOUNDER'];
					break;

					default:
						$info .= $lang['USER_ACTIVE'];
				}

				if ($user_id == ANONYMOUS)
				{
					$info = $lang['GUEST'];
				}
?>
				<tr class="bg<?php echo ($i % 2) + 1; ?>">
					<td>
						<span class="rank-img"><?php echo ($rank_img) ? $rank_img : $rank_title; ?></span>
						<?php echo get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']); ?>
					</td>
					<td class="posts"><?php echo $row['user_posts']; ?></td>
					<td class="info"><?php echo $info; ?></td>
					<td><?php echo $user->format_date($row['user_regdate']) ?></td>
					<td><?php echo (empty($last_visit)) ? ' - ' : $user->format_date($last_visit); ?>&nbsp;</td>
					<td>
						<label><input type="radio" class="radio" id="keep_user_<?php echo $user_id; ?>" name="modify_users[<?php echo $user_id; ?>]" value="keep" checked="checked" /> <?php echo $lang['KEEP_OLD_NAME']; ?></label><br />
						<label><input type="radio" class="radio" id="edit_user_<?php echo $user_id; ?>" name="modify_users[<?php echo $user_id; ?>]" value="edit" /> <?php echo $lang['EDIT_USERNAME']; ?></label><br />
<?php
				// some users must not be deleted
				if ($user_id != ANONYMOUS && $row['user_type'] != USER_FOUNDER)
				{
?>
						<label><input type="radio" class="radio" id="delete_user_retain_<?php echo $user_id; ?>" name="modify_users[<?php echo $user_id; ?>]" value="delete_retain" /> <?php echo $lang['DELETE_USER_RETAIN']; ?></label><br />
						<label><input type="radio" class="radio" id="delete_user_remove_<?php echo $user_id; ?>" name="modify_users[<?php echo $user_id; ?>]" value="delete_remove" /> <?php echo $lang['DELETE_USER_REMOVE']; ?></label>
<?php
				}
?>
					</td>
					<td>
						<input id="new_username_<?php echo $user_id; ?>" type="text" name="new_usernames[<?php echo $user_id; ?>]" value="<?php echo $row['username']; ?>" />
					</td>
				</tr>
<?php
			}
?>
			</tbody>
		</table>
	</fieldset>
<?php
		}
?>
		<p class="quick">
			<input class="button2" id="resolve_conflicts" type="submit" name="resolve_conflicts" value="<?php echo $lang['SUBMIT']; ?>" />
		</p>
	</form>
<?php
	}
	else
	{
		$sql = 'SELECT user_id, username, username_clean
			FROM ' . USERS_TABLE;
		$result = $db->sql_query($sql);
	
		while ($row = $db->sql_fetchrow($result))
		{
			$clean_name = utf8_new_clean_string($row['username']);
			if ($clean_name != $row['username_clean'])
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET username_clean = \'' . $db->sql_escape($clean_name) . '\'
					WHERE user_id = ' . (int) $row['user_id'];
				$db->sql_query($sql);
			}
		}
		$db->sql_freeresult($result);
	}
	unset($colliding_users);
}

if ($exit)
{
?>

					</div>
				</div>
			<span class="corners-bottom"><span></span></span>
		</div>
		</div>
	</div>

	<div id="page-footer">
		Powered by phpBB &copy; 2000, 2002, 2005, 2007 <a href="http://www.phpbb.com/">phpBB Group</a>
	</div>
</div>

</body>
</html>

<?php
	exit;
}

// Schema updates
?>
	</p><br /><br />

	<h1><?php echo $lang['UPDATE_DATABASE_SCHEMA']; ?></h1>

	<br />
	<p><?php echo $lang['PROGRESS']; ?> :: <strong>

<?php

flush();

// We go through the schema changes from the lowest to the highest version
// We skip those versions older than the current version
$no_updates = true;
foreach ($database_update_info as $version => $schema_changes)
{
	if (version_compare($version, $current_version, '<'))
	{
		continue;
	}

	if (!sizeof($schema_changes))
	{
		continue;
	}

	$no_updates = false;

	// Change columns?
	if (!empty($schema_changes['change_columns']))
	{
		foreach ($schema_changes['change_columns'] as $table => $columns)
		{
			foreach ($columns as $column_name => $column_data)
			{
				sql_column_change($map_dbms, $table, $column_name, $column_data);
			}
		}
	}

	// Add columns?
	if (!empty($schema_changes['add_columns']))
	{
		foreach ($schema_changes['add_columns'] as $table => $columns)
		{
			foreach ($columns as $column_name => $column_data)
			{
				// Only add the column if it does not exist yet
				if (!column_exists($map_dbms, $table, $column_name))
				{
					sql_column_add($map_dbms, $table, $column_name, $column_data);
				}
			}
		}
	}

	// Remove keys?
	if (!empty($schema_changes['drop_keys']))
	{
		foreach ($schema_changes['drop_keys'] as $table => $indexes)
		{
			foreach ($indexes as $index_name)
			{
				sql_index_drop($map_dbms, $index_name, $table);
			}
		}
	}

	// Drop columns?
	if (!empty($schema_changes['drop_columns']))
	{
		foreach ($schema_changes['drop_columns'] as $table => $columns)
		{
			foreach ($columns as $column)
			{
				sql_column_remove($map_dbms, $table, $column);
			}
		}
	}

	// Add primary keys?
	if (!empty($schema_changes['add_primary_keys']))
	{
		foreach ($schema_changes['add_primary_keys'] as $table => $columns)
		{
			sql_create_primary_key($map_dbms, $table, $columns);
		}
	}

	// Add unqiue indexes?
	if (!empty($schema_changes['add_unique_index']))
	{
		foreach ($schema_changes['add_unique_index'] as $table => $index_array)
		{
			foreach ($index_array as $index_name => $column)
			{
				sql_create_unique_index($map_dbms, $index_name, $table, $column);
			}
		}
	}

	// Add indexes?
	if (!empty($schema_changes['add_index']))
	{
		foreach ($schema_changes['add_index'] as $table => $index_array)
		{
			foreach ($index_array as $index_name => $column)
			{
				sql_create_index($map_dbms, $index_name, $table, $column);
			}
		}
	}
}

_write_result($no_updates, $errored, $error_ary);

// Data updates
$error_ary = array();
$errored = $no_updates = false;

?>

<br /><br />
<h1><?php echo $lang['UPDATING_DATA']; ?></h1>
<br />
<p><?php echo $lang['PROGRESS']; ?> :: <strong>

<?php

flush();

$no_updates = true;

// some code magic
if (version_compare($current_version, '3.0.RC1', '<='))
{
	// we have to remove a few extra entries from converted boards. 
	$sql = 'SELECT group_id
		FROM ' . GROUPS_TABLE . "
		WHERE group_name = '" . $db->sql_escape('BOTS') . "'";
	$result = $db->sql_query($sql);
	$bot_group_id = (int) $db->sql_fetchfield('group_id');
	$db->sql_freeresult($result);

	$bots = array();
	$sql = 'SELECT u.user_id
		FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . ' ug
		WHERE ug.group_id = ' . $bot_group_id . '
		AND ug.user_id = u.user_id';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$bots[] = (int)$row['user_id'];
	}
	$db->sql_freeresult($result);
	
	if (sizeof($bots))
	{
		$sql = 'DELETE FROM ' . USER_GROUP_TABLE . "
			WHERE group_id <> $bot_group_id
				AND " . $db->sql_in_set('user_id', $bots);
		$db->sql_query($sql);
	}

	if ($map_dbms === 'mysql_41')
	{
		sql_column_change($map_dbms, POSTS_TABLE, 'post_subject', array('XSTEXT_UNI', '', 'true_sort'));
	}

	$sql = 'DELETE FROM ' . CONFIG_TABLE . " WHERE config_name = 'jab_resource'";
	_sql($sql, $errored, $error_ary);

	set_config('jab_use_ssl', '0');
	set_config('allow_post_flash', '1');
 
	$no_updates = false;
}

if (version_compare($current_version, '3.0.RC2', '<='))
{
	$smileys = array();
	$sql = 'SELECT smiley_id, code 
		FROM ' . SMILIES_TABLE;
		
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$smileys[$row['smiley_id']] = $row['code'];
	}
	$db->sql_freeresult($result);
	
	foreach($smileys as $id => $code)
	{
		// 2.0 only entitized lt and gt; We need to do something about double quotes.
		if (strchr($code, '"') === false)
		{
			continue;
		}

		$new_code = str_replace('&amp;', '&', $code);
		$new_code = str_replace('&lt;', '<', $new_code);
		$new_code = str_replace('&gt;', '>', $new_code);
		$new_code = utf8_htmlspecialchars($new_code);

		$sql = 'UPDATE ' . SMILIES_TABLE . ' 
			SET code = \'' . $db->sql_escape($new_code) . '\'
			WHERE smiley_id = ' . (int) $id;
		$db->sql_query($sql);
	}

	$index_list = sql_list_index($map_dbms, ACL_ROLES_DATA_TABLE);

	if (in_array('ath_opt_id', $index_list))
	{
		sql_index_drop($map_dbms, 'ath_opt_id', ACL_ROLES_DATA_TABLE);
		sql_create_index($map_dbms, 'ath_op_id', ACL_ROLES_DATA_TABLE, array('auth_option_id'));
	}

	$no_updates = false;
}

if (version_compare($current_version, '3.0.RC3', '<='))
{
	if ($map_dbms === 'postgres')
	{
		$sql = "SELECT SETVAL('" . FORUMS_TABLE . "_seq',(select case when max(forum_id)>0 then max(forum_id)+1 else 1 end from " . FORUMS_TABLE . '));';
		_sql($sql, $errored, $error_ary);

		$no_updates = false;
	}

	// we check for:
	// ath_opt_id
	// ath_op_id
	// ACL_ROLES_DATA_TABLE_ath_opt_id
	// we want ACL_ROLES_DATA_TABLE_ath_op_id

	$table_index_fix = array(
		ACL_ROLES_DATA_TABLE => array(
			'ath_opt_id'							=> 'ath_op_id',
			'ath_op_id'								=> 'ath_op_id',
			ACL_ROLES_DATA_TABLE . '_ath_opt_id'	=> 'ath_op_id'
		),
		STYLES_IMAGESET_DATA_TABLE => array(
			'i_id'									=> 'i_d',
			'i_d'									=> 'i_d',
			STYLES_IMAGESET_DATA_TABLE . '_i_id'	=> 'i_d'
		)
	);

	// we need to create some indicies...
	$needed_creation = array();

	foreach ($table_index_fix as $table_name => $index_info)
	{
		$index_list = sql_list_fake($map_dbms, $table_name);
		foreach ($index_info as $bad_index => $good_index)
		{
			if (in_array($bad_index, $index_list))
			{
				// mysql is actually OK, it won't get a hand in this crud
				switch ($map_dbms)
				{
					// last version, mssql had issues with index removal
					case 'mssql':
						$sql = 'DROP INDEX ' . $table_name . '.' . $bad_index;
						_sql($sql, $errored, $error_ary);

						$no_updates = false;
					break;

					// last version, firebird, oracle, postgresql and sqlite all got bad index names
					// we got kinda lucky, tho: they all support the same syntax
					case 'firebird':
					case 'oracle':
					case 'postgres':
					case 'sqlite':
						$sql = 'DROP INDEX ' . $bad_index;
						_sql($sql, $errored, $error_ary);

						$no_updates = false;
					break;
				}

				$needed_creation[$table_name][$good_index] = 1;
			}
		}
	}

	$new_index_defs = array('ath_op_id' => array('auth_option_id'), 'i_d' => array('imageset_id'));

	foreach ($needed_creation as $bad_table => $index_repair_list)
	{
		foreach ($index_repair_list as $new_index => $garbage)
		{
			sql_create_index($map_dbms, $new_index, $bad_table, $new_index_defs[$new_index]);
			$no_updates = false;
		}
	}

	// Make sure empty smiley codes do not exist
	$sql = 'DELETE FROM ' . SMILIES_TABLE . " 
		WHERE code = ''";
	$db->sql_query($sql);

	set_config('allow_birthdays', '1');
}

_write_result($no_updates, $errored, $error_ary);

$error_ary = array();
$errored = $no_updates = false;

?>

<br /><br />
<h1><?php echo $lang['UPDATE_VERSION_OPTIMIZE']; ?></h1>
<br />
<p><?php echo $lang['PROGRESS']; ?> :: <strong>

<?php

flush();

// update the version
$sql = "UPDATE " . CONFIG_TABLE . "
	SET config_value = '$updates_to_version'
	WHERE config_name = 'version'";
_sql($sql, $errored, $error_ary);

// Reset permissions
$sql = 'UPDATE ' . USERS_TABLE . "
	SET user_permissions = ''";
_sql($sql, $errored, $error_ary);

/* Optimize/vacuum analyze the tables where appropriate 
// this should be done for each version in future along with 
// the version number update
switch ($db->sql_layer)
{
	case 'mysql':
	case 'mysqli':
	case 'mysql4':
		$sql = 'OPTIMIZE TABLE ' . $table_prefix . 'auth_access, ' . $table_prefix . 'banlist, ' . $table_prefix . 'categories, ' . $table_prefix . 'config, ' . $table_prefix . 'disallow, ' . $table_prefix . 'forum_prune, ' . $table_prefix . 'forums, ' . $table_prefix . 'groups, ' . $table_prefix . 'posts, ' . $table_prefix . 'posts_text, ' . $table_prefix . 'privmsgs, ' . $table_prefix . 'privmsgs_text, ' . $table_prefix . 'ranks, ' . $table_prefix . 'search_results, ' . $table_prefix . 'search_wordlist, ' . $table_prefix . 'search_wordmatch, ' . $table_prefix . 'sessions_keys' . $table_prefix . 'smilies, ' . $table_prefix . 'themes, ' . $table_prefix . 'themes_name, ' . $table_prefix . 'topics, ' . $table_prefix . 'topics_watch, ' . $table_prefix . 'user_group, ' . $table_prefix . 'users, ' . $table_prefix . 'vote_desc, ' . $table_prefix . 'vote_results, ' . $table_prefix . 'vote_voters, ' . $table_prefix . 'words';
		_sql($sql, $errored, $error_ary);
	break;

	case 'postgresql':
		_sql("VACUUM ANALYZE", $errored, $error_ary);
	break;
}
*/

_write_result($no_updates, $errored, $error_ary);

?>

<br />
<h1><?php echo $lang['UPDATE_COMPLETED']; ?></h1>

<br />

<?php

if (!$inline_update)
{
?>

	<p style="color:red"><?php echo $lang['UPDATE_FILES_NOTICE']; ?></p>

	<p><?php echo $lang['COMPLETE_LOGIN_TO_BOARD']; ?></p>

<?php
}
else
{
?>

	<p><?php echo ((isset($lang['INLINE_UPDATE_SUCCESSFUL'])) ? $lang['INLINE_UPDATE_SUCCESSFUL'] : 'The database update was successful. Now you need to continue the update process.'); ?></p>

	<p><a href="<?php echo append_sid("{$phpbb_root_path}install/index.{$phpEx}", "mode=update&amp;sub=file_check&amp;lang=$language"); ?>" class="button1"><?php echo (isset($lang['CONTINUE_UPDATE_NOW'])) ? $lang['CONTINUE_UPDATE_NOW'] : 'Continue the update process now'; ?></a></p>

<?php
}

// Add database update to log
add_log('admin', 'LOG_UPDATE_DATABASE', $orig_version, $updates_to_version);

// Now we purge the session table as well as all cache files
$cache->purge();

?>

					</div>
				</div>
			<span class="corners-bottom"><span></span></span>
		</div>
		</div>
	</div>
	
	<div id="page-footer">
		Powered by phpBB &copy; 2000, 2002, 2005, 2007 <a href="http://www.phpbb.com/">phpBB Group</a>
	</div>
</div>

</body>
</html>

<?php

/**
* Function for triggering an sql statement
*/
function _sql($sql, &$errored, &$error_ary, $echo_dot = true)
{
	global $db;

	if (defined('DEBUG_EXTRA'))
	{
		echo "<br />\n{$sql}\n<br />";
	}

	$db->sql_return_on_error(true);

	$result = $db->sql_query($sql);

	if ($db->sql_error_triggered)
	{
		$errored = true;
		$error_ary['sql'][] = $db->sql_error_sql;
		$error_ary['error_code'][] = $db->_sql_error();
	}

	$db->sql_return_on_error(false);

	if ($echo_dot)
	{
		echo ". \n";
		flush();
	}

	return $result;
}

function _write_result($no_updates, $errored, $error_ary)
{
	global $lang;

	if ($no_updates)
	{
		echo ' ' . $lang['NO_UPDATES_REQUIRED'] . '</strong></p>';
	}
	else
	{
		echo ' <span class="success">' . $lang['DONE'] . '</span></strong><br />' . $lang['RESULT'] . ' :: ';

		if ($errored)
		{
			echo ' <strong>' . $lang['SOME_QUERIES_FAILED'] . '</strong> <ul>';

			for ($i = 0; $i < sizeof($error_ary['sql']); $i++)
			{
				echo '<li>' . $lang['ERROR'] . ' :: <strong>' . htmlspecialchars($error_ary['error_code'][$i]['message']) . '</strong><br />';
				echo $lang['SQL'] . ' :: <strong>' . htmlspecialchars($error_ary['sql'][$i]) . '</strong><br /><br /></li>';
			}

			echo '</ul> <br /><br />' . $lang['SQL_FAILURE_EXPLAIN'] . '</p>';
		}
		else
		{
			echo '<strong>' . $lang['NO_ERRORS'] . '</strong></p>';
		}
	}
}

/**
* Check if a specified column exist
*/
function column_exists($dbms, $table, $column_name)
{
	global $db;

	switch ($dbms)
	{
		case 'mysql_40':
		case 'mysql_41':
			$sql = "SHOW COLUMNS
				FROM $table";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				// lower case just in case
				if (strtolower($row['Field']) == $column_name)
				{
					$db->sql_freeresult($result);
					return true;
				}
			}
			$db->sql_freeresult($result);
			return false;
		break;

		// PostgreSQL has a way of doing this in a much simpler way but would
		// not allow us to support all versions of PostgreSQL
		case 'postgres':
			$sql = "SELECT a.attname
				FROM pg_class c, pg_attribute a
				WHERE c.relname = '{$table}'
					AND a.attnum > 0
					AND a.attrelid = c.oid";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				// lower case just in case
				if (strtolower($row['attname']) == $column_name)
				{
					$db->sql_freeresult($result);
					return true;
				}
			}
			$db->sql_freeresult($result);
			return false;
		break;

		// same deal with PostgreSQL, we must perform more complex operations than
		// we technically could
		case 'mssql':
			$sql = "SELECT c.name
				FROM syscolumns c
				LEFT JOIN sysobjects o (ON c.id = o.id)
				WHERE o.name = '{$table}'";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				// lower case just in case
				if (strtolower($row['name']) == $column_name)
				{
					$db->sql_freeresult($result);
					return true;
				}
			}
			$db->sql_freeresult($result);
			return false;
		break;

		case 'oracle':
			$sql = "SELECT column_name
				FROM user_tab_columns
				WHERE table_name = '{$table}'";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				// lower case just in case
				if (strtolower($row['column_name']) == $column_name)
				{
					$db->sql_freeresult($result);
					return true;
				}
			}
			$db->sql_freeresult($result);
			return false;
		break;

		case 'firebird':
			$sql = "SELECT RDB\$FIELD_NAME as FNAME
				FROM RDB\$RELATION_FIELDS
				WHERE RDB\$RELATION_NAME = '{$table}'";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				// lower case just in case
				if (strtolower($row['fname']) == $column_name)
				{
					$db->sql_freeresult($result);
					return true;
				}
			}
			$db->sql_freeresult($result);
			return false;
		break;

		// ugh, SQLite
		case 'sqlite':
			$sql = "SELECT sql
				FROM sqlite_master 
				WHERE type = 'table' 
					AND name = '{$table}'";
			$result = $db->sql_query($sql);

			if (!$result)
			{
				return false;
			}

			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			preg_match('#\((.*)\)#s', $row['sql'], $matches);

			$cols = trim($matches[1]);
			$col_array = preg_split('/,(?![\s\w]+\))/m', $cols);

			foreach ($col_array as $declaration)
			{
				$entities = preg_split('#\s+#', trim($declaration));
				if ($entities[0] == 'PRIMARY')
				{
					continue;
				}

				if (strtolower($entities[0]) == $column_name)
				{
					return true;
				}
			}
			return false;
		break;
	}
}

/**
* Function to prepare some column information for better usage
*/
function prepare_column_data($dbms, $column_data)
{
	global $dbms_type_map, $unsigned_types;

	// Get type
	if (strpos($column_data[0], ':') !== false)
	{
		list($orig_column_type, $column_length) = explode(':', $column_data[0]);

		if (!is_array($dbms_type_map[$dbms][$orig_column_type . ':']))
		{
			$column_type = sprintf($dbms_type_map[$dbms][$orig_column_type . ':'], $column_length);
		}
		else
		{
			if (isset($dbms_type_map[$dbms][$orig_column_type . ':']['rule']))
			{
				switch ($dbms_type_map[$dbms][$orig_column_type . ':']['rule'][0])
				{
					case 'div':
						$column_length /= $dbms_type_map[$dbms][$orig_column_type . ':']['rule'][1];
						$column_length = ceil($column_length);
						$column_type = sprintf($dbms_type_map[$dbms][$orig_column_type . ':'][0], $column_length);
					break;
				}
			}

			if (isset($dbms_type_map[$dbms][$orig_column_type . ':']['limit']))
			{
				switch ($dbms_type_map[$dbms][$orig_column_type . ':']['limit'][0])
				{
					case 'mult':
						$column_length *= $dbms_type_map[$dbms][$orig_column_type . ':']['limit'][1];
						if ($column_length > $dbms_type_map[$dbms][$orig_column_type . ':']['limit'][2])
						{
							$column_type = $dbms_type_map[$dbms][$orig_column_type . ':']['limit'][3];
						}
						else
						{
							$column_type = sprintf($dbms_type_map[$dbms][$orig_column_type . ':'][0], $column_length);
						}
					break;
				}
			}
		}
		$orig_column_type .= ':';
	}
	else
	{
		$orig_column_type = $column_data[0];
		$column_type = $dbms_type_map[$dbms][$column_data[0]];
	}

	// Adjust default value if db-dependant specified
	if (is_array($column_data[1]))
	{
		$column_data[1] = (isset($column_data[1][$dbms])) ? $column_data[1][$dbms] : $column_data[1]['default'];
	}

	$sql = '';

	$return_array = array();

	switch ($dbms)
	{
		case 'firebird':
			$sql .= " {$column_type} ";

			if (!is_null($column_data[1]))
			{
				$sql .= 'DEFAULT ' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ' ';
			}

			$sql .= 'NOT NULL';

			// This is a UNICODE column and thus should be given it's fair share
			if (preg_match('/^X?STEXT_UNI|VCHAR_(CI|UNI:?)/', $column_data[0]))
			{
				$sql .= ' COLLATE UNICODE';
			}

		break;

		case 'mssql':
			$sql .= " {$column_type} ";

			// we do not support MSSQL DEFAULTs for the near future
			/*if (!is_null($column_data[1]))
			{
				// For hexadecimal values do not use single quotes
				if (strpos($column_data[1], '0x') === 0)
				{
					$sql .= 'DEFAULT (' . $column_data[1] . ') ';
				}
				else
				{
					$sql .= 'DEFAULT (' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ') ';
				}
			}*/

			$sql .= 'NOT NULL';
		break;

		case 'mysql_40':
		case 'mysql_41':
			$sql .= " {$column_type} ";

			// For hexadecimal values do not use single quotes
			if (!is_null($column_data[1]) && substr($column_type, -4) !== 'text' && substr($column_type, -4) !== 'blob')
			{
				$sql .= (strpos($column_data[1], '0x') === 0) ? "DEFAULT {$column_data[1]} " : "DEFAULT '{$column_data[1]}' ";
			}
			$sql .= 'NOT NULL';

			if (isset($column_data[2]))
			{
				if ($column_data[2] == 'auto_increment')
				{
					$sql .= ' auto_increment';
				}
				else if ($dbms === 'mysql_41' && $column_data[2] == 'true_sort')
				{
					$sql .= ' COLLATE utf8_unicode_ci';
				}
			}

		break;

		case 'oracle':
			$sql .= " {$column_type} ";
			$sql .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}' " : '';

			// In Oracle empty strings ('') are treated as NULL.
			// Therefore in oracle we allow NULL's for all DEFAULT '' entries
			$sql .= ($column_data[1] === '') ? '' : 'NOT NULL';
		break;

		case 'postgres':
			$return_array['column_type'] = $column_type;
			$return_array['null'] = 'NOT NULL';

			if (!is_null($column_data[1]))
			{
				$return_array['default'] = $column_data[1];
			}

			$sql .= " {$column_type} ";
			$sql .= 'NOT NULL ';

			$sql .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}' " : '';

			// Unsigned? Then add a CHECK contraint
			if (in_array($orig_column_type, $unsigned_types))
			{
				$return_array['constraint'] = "CHECK ({$column_name} >= 0)";
				$sql .= " CHECK ({$column_name} >= 0)";
			}
		break;

		case 'sqlite':
/*			if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
			{
				$sql .= ' INTEGER PRIMARY KEY';
			}
			else
			{
				$sql .= ' ' . $column_type;
			}
*/
			$sql .= ' ' . $column_type;

			$sql .= ' NOT NULL ';
			$sql .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}'" : '';
		break;
	}

	$return_array['column_type_sql'] = $sql;

	return $return_array;
}

/**
* Add new column
*/
function sql_column_add($dbms, $table_name, $column_name, $column_data)
{
	global $errored, $error_ary;

	$column_data = prepare_column_data($dbms, $column_data);

	switch ($dbms)
	{
		case 'firebird':
			$sql = 'ALTER TABLE "' . $table_name . '" ADD "' . $column_name . '" ' . $column_data['column_type_sql'];
			_sql($sql, $errored, $error_ary);
		break;

		case 'mssql':
			$sql = 'ALTER TABLE [' . $table_name . '] ADD [' . $column_name . '] ' . $column_data['column_type_sql'];
			_sql($sql, $errored, $error_ary);
		break;

		case 'mysql_40':
		case 'mysql_41':
			$sql = 'ALTER TABLE `' . $table_name . '` ADD COLUMN `' . $column_name . '` ' . $column_data['column_type_sql'];
			_sql($sql, $errored, $error_ary);
		break;

		case 'oracle':
			$sql = 'ALTER TABLE ' . $table_name . ' ADD ' . $column_name . ' ' . $column_data['column_type_sql'];
			_sql($sql, $errored, $error_ary);
		break;

		case 'postgres':
			$sql = 'ALTER TABLE ' . $table_name . ' ADD COLUMN "' . $column_name . '" ' . $column_data['column_type_sql'];
			_sql($sql, $errored, $error_ary);
		break;

		case 'sqlite':
			if (version_compare(sqlite_libversion(), '3.0') == -1)
			{
				global $db;
				$sql = "SELECT sql
					FROM sqlite_master 
					WHERE type = 'table' 
						AND name = '{$table_name}'
					ORDER BY type DESC, name;";
				$result = $db->sql_query($sql);

				if (!$result)
				{
					break;
				}

				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$db->sql_transaction('begin');

				// Create a backup table and populate it, destroy the existing one
				$db->sql_query(preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']));
				$db->sql_query('INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name);
				$db->sql_query('DROP TABLE ' . $table_name);

				preg_match('#\((.*)\)#s', $row['sql'], $matches);

				$new_table_cols = trim($matches[1]);
				$old_table_cols = preg_split('/,(?![\s\w]+\))/m', $new_table_cols);
				$column_list = array();

				foreach ($old_table_cols as $declaration)
				{
					$entities = preg_split('#\s+#', trim($declaration));
					if ($entities[0] == 'PRIMARY')
					{
						continue;
					}
					$column_list[] = $entities[0];
				}

				$columns = implode(',', $column_list);

				$new_table_cols = $column_name . ' ' . $column_data['column_type_sql'] . ',' . $new_table_cols;

				// create a new table and fill it up. destroy the temp one
				$db->sql_query('CREATE TABLE ' . $table_name . ' (' . $new_table_cols . ');');
				$db->sql_query('INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;');
				$db->sql_query('DROP TABLE ' . $table_name . '_temp');

				$db->sql_transaction('commit');
			}
			else
			{
				$sql = 'ALTER TABLE ' . $table_name . ' ADD ' . $column_name . ' [' . $column_data['column_type_sql'] . ']';
				_sql($sql, $errored, $error_ary);
			}
		break;
	}
}

/**
* Drop column
*/
function sql_column_remove($dbms, $table_name, $column_name)
{
	global $errored, $error_ary;

	switch ($dbms)
	{
		case 'firebird':
			$sql = 'ALTER TABLE "' . $table_name . '" DROP "' . $column_name . '"';
			_sql($sql, $errored, $error_ary);
		break;

		case 'mssql':
			$sql = 'ALTER TABLE [' . $table_name . '] DROP COLUMN [' . $column_name . ']';
			_sql($sql, $errored, $error_ary);
		break;

		case 'mysql_40':
		case 'mysql_41':
			$sql = 'ALTER TABLE `' . $table_name . '` DROP COLUMN `' . $column_name . '`';
			_sql($sql, $errored, $error_ary);
		break;

		case 'oracle':
			$sql = 'ALTER TABLE ' . $table_name . ' DROP ' . $column_name;
			_sql($sql, $errored, $error_ary);
		break;

		case 'postgres':
			$sql = 'ALTER TABLE ' . $table_name . ' DROP COLUMN "' . $column_name . '"';
			_sql($sql, $errored, $error_ary);
		break;

		case 'sqlite':
			if (version_compare(sqlite_libversion(), '3.0') == -1)
			{
				global $db;
				$sql = "SELECT sql
					FROM sqlite_master 
					WHERE type = 'table' 
						AND name = '{$table_name}'
					ORDER BY type DESC, name;";
				$result = $db->sql_query($sql);

				if (!$result)
				{
					break;
				}

				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$db->sql_transaction('begin');

				// Create a backup table and populate it, destroy the existing one
				$db->sql_query(preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']));
				$db->sql_query('INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name);
				$db->sql_query('DROP TABLE ' . $table_name);

				preg_match('#\((.*)\)#s', $row['sql'], $matches);

				$new_table_cols = trim($matches[1]);
				$old_table_cols = preg_split('/,(?![\s\w]+\))/m', $new_table_cols);
				$column_list = array();

				foreach ($old_table_cols as $declaration)
				{
					$entities = preg_split('#\s+#', trim($declaration));
					if ($entities[0] == 'PRIMARY' || $entities[0] === '$column_name')
					{
						continue;
					}
					$column_list[] = $entities[0];
				}

				$columns = implode(',', $column_list);

				$new_table_cols = $new_table_cols = preg_replace('/' . $column_name . '[^,]+(?:,|$)/m', '', $new_table_cols);

				// create a new table and fill it up. destroy the temp one
				$db->sql_query('CREATE TABLE ' . $table_name . ' (' . $new_table_cols . ');');
				$db->sql_query('INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;');
				$db->sql_query('DROP TABLE ' . $table_name . '_temp');

				$db->sql_transaction('commit');
			}
			else
			{
				$sql = 'ALTER TABLE ' . $table_name . ' DROP COLUMN ' . $column_name;
				_sql($sql, $errored, $error_ary);
			}
		break;
	}
}

function sql_index_drop($dbms, $index_name, $table_name)
{
	global $dbms_type_map, $db;
	global $errored, $error_ary;

	switch ($dbms)
	{
		case 'mssql':
			$sql = 'DROP INDEX ' . $table_name . '.' . $index_name;
			_sql($sql, $errored, $error_ary);
		break;

		case 'mysql_40':
		case 'mysql_41':
			$sql = 'DROP INDEX ' . $index_name . ' ON ' . $table_name;
			_sql($sql, $errored, $error_ary);
		break;

		case 'firebird':
		case 'oracle':
		case 'postgres':
		case 'sqlite':
			$sql = 'DROP INDEX ' . $table_name . '_' . $index_name;
			_sql($sql, $errored, $error_ary);
		break;
	}
}

function sql_create_primary_key($dbms, $table_name, $column)
{
	global $dbms_type_map, $db;
	global $errored, $error_ary;

	switch ($dbms)
	{
		case 'firebird':
		case 'postgres':
			$sql = 'ALTER TABLE ' . $table_name . ' ADD PRIMARY KEY (' . implode(', ', $column) . ')';
			_sql($sql, $errored, $error_ary);
		break;

		case 'mssql':
			$sql = "ALTER TABLE [{$table_name}] WITH NOCHECK ADD ";
			$sql .= "CONSTRAINT [PK_{$table_name}] PRIMARY KEY  CLUSTERED (";
			$sql .= '[' . implode("],\n\t\t[", $column) . ']';
			$sql .= ') ON [PRIMARY]';
			_sql($sql, $errored, $error_ary);
		break;

		case 'mysql_40':
		case 'mysql_41':
			$sql = 'ALTER TABLE ' . $table_name . ' ADD PRIMARY KEY (' . implode(', ', $column) . ')';
			_sql($sql, $errored, $error_ary);
		break;

		case 'oracle':
			$sql = 'ALTER TABLE ' . $table_name . 'add CONSTRAINT pk_' . $table_name . ' PRIMARY KEY (' . implode(', ', $column) . ')';
			_sql($sql, $errored, $error_ary);
		break;

		case 'sqlite':
			$sql = "SELECT sql
				FROM sqlite_master 
				WHERE type = 'table' 
					AND name = '{$table_name}'
				ORDER BY type DESC, name;";
			$result = _sql($sql, $errored, $error_ary);

			if (!$result)
			{
				break;
			}

			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$db->sql_transaction('begin');

			// Create a backup table and populate it, destroy the existing one
			$db->sql_query(preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']));
			$db->sql_query('INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name);
			$db->sql_query('DROP TABLE ' . $table_name);

			preg_match('#\((.*)\)#s', $row['sql'], $matches);

			$new_table_cols = trim($matches[1]);
			$old_table_cols = preg_split('/,(?![\s\w]+\))/m', $new_table_cols);
			$column_list = array();

			foreach ($old_table_cols as $declaration)
			{
				$entities = preg_split('#\s+#', trim($declaration));
				if ($entities[0] == 'PRIMARY')
				{
					continue;
				}
				$column_list[] = $entities[0];
			}

			$columns = implode(',', $column_list);

			// create a new table and fill it up. destroy the temp one
			$db->sql_query('CREATE TABLE ' . $table_name . ' (' . $new_table_cols . ', PRIMARY KEY (' . implode(', ', $column) . '));');
			$db->sql_query('INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;');
			$db->sql_query('DROP TABLE ' . $table_name . '_temp');

			$db->sql_transaction('commit');
		break;
	}
}

function sql_create_unique_index($dbms, $index_name, $table_name, $column)
{
	global $dbms_type_map, $db;
	global $errored, $error_ary;

	switch ($dbms)
	{
		case 'firebird':
		case 'postgres':
		case 'oracle':
		case 'sqlite':
			$sql = 'CREATE UNIQUE INDEX ' . $table_name . '_' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			_sql($sql, $errored, $error_ary);
		break;

		case 'mysql_40':
		case 'mysql_41':
			$sql = 'CREATE UNIQUE INDEX ' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			_sql($sql, $errored, $error_ary);
		break;

		case 'mssql':
			$sql = 'CREATE UNIQUE INDEX ' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ') ON [PRIMARY]';
			_sql($sql, $errored, $error_ary);
		break;
	}
}

function sql_create_index($dbms, $index_name, $table_name, $column)
{
	global $dbms_type_map, $db;
	global $errored, $error_ary;

	switch ($dbms)
	{
		case 'firebird':
		case 'postgres':
		case 'oracle':
		case 'sqlite':
			$sql = 'CREATE INDEX ' . $table_name . '_' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			_sql($sql, $errored, $error_ary);
		break;

		case 'mysql_40':
		case 'mysql_41':
			$sql = 'CREATE INDEX ' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			_sql($sql, $errored, $error_ary);
		break;

		case 'mssql':
			$sql = 'CREATE INDEX ' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ') ON [PRIMARY]';
			_sql($sql, $errored, $error_ary);
		break;
	}
}

// List all of the indices that belong to a table,
// does not count:
// * UNIQUE indices
// * PRIMARY keys
function sql_list_index($dbms, $table_name)
{
	global $dbms_type_map, $db;
	global $errored, $error_ary;

	$index_array = array();

	if ($dbms == 'mssql')
	{
		$sql = "EXEC sp_statistics '$table_name'";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['TYPE'] == 3)
			{
				$index_array[] = $row['INDEX_NAME'];
			}
		}
		$db->sql_freeresult($result);
	}
	else
	{
		switch ($dbms)
		{
			case 'firebird':
				$sql = "SELECT LOWER(RDB\$INDEX_NAME) as index_name
					FROM RDB\$INDICES
					WHERE RDB\$RELATION_NAME = " . strtoupper($table_name) . "
						AND RDB\$UNIQUE_FLAG IS NULL
						AND RDB\$FOREIGN_KEY IS NULL";
				$col = 'index_name';
			break;

			case 'postgres':
				$sql = "SELECT ic.relname as index_name
					FROM pg_class bc, pg_class ic, pg_index i
					WHERE (bc.oid = i.indrelid)
						AND (ic.oid = i.indexrelid)
						AND (bc.relname = '" . $table_name . "')
						AND (i.indisunique != 't')
						AND (i.indisprimary != 't')";
				$col = 'index_name';
			break;

			case 'mysql_40':
			case 'mysql_41':
				$sql = 'SHOW KEYS
					FROM ' . $table_name;
				$col = 'Key_name';
			break;

			case 'oracle':
				$sql = "SELECT index_name
					FROM user_indexes
					WHERE table_name = '" . $table_name . "'
						AND generated = 'N'";
			break;

			case 'sqlite':
				$sql = "PRAGMA index_info('" . $table_name . "');";
				$col = 'name';
			break;
		}

		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if (($dbms == 'mysql_40' || $dbms == 'mysql_41') && !$row['Non_unique'])
			{
				continue;
			}

			switch ($dbms)
			{
				case 'firebird':
				case 'oracle':
				case 'postgres':
				case 'sqlite':
					$row[$col] = substr($row[$col], strlen($table_name) + 1);
				break;
			}

			$index_array[] = $row[$col];
		}
		$db->sql_freeresult($result);
	}

	return array_map('strtolower', $index_array);
}

// This is totally fake, never use it
// it exists only to mend bad update functions introduced
// * UNIQUE indices
// * PRIMARY keys
function sql_list_fake($dbms, $table_name)
{
	global $dbms_type_map, $db;
	global $errored, $error_ary;

	$index_array = array();

	if ($dbms == 'mssql')
	{
		$sql = "EXEC sp_statistics '$table_name'";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['TYPE'] == 3)
			{
				$index_array[] = $row['INDEX_NAME'];
			}
		}
		$db->sql_freeresult($result);
	}
	else
	{
		switch ($dbms)
		{
			case 'firebird':
				$sql = "SELECT LOWER(RDB\$INDEX_NAME) as index_name
					FROM RDB\$INDICES
					WHERE RDB\$RELATION_NAME = " . strtoupper($table_name) . "
						AND RDB\$UNIQUE_FLAG IS NULL
						AND RDB\$FOREIGN_KEY IS NULL";
				$col = 'index_name';
			break;

			case 'postgres':
				$sql = "SELECT ic.relname as index_name
					FROM pg_class bc, pg_class ic, pg_index i
					WHERE (bc.oid = i.indrelid)
						AND (ic.oid = i.indexrelid)
						AND (bc.relname = '" . $table_name . "')
						AND (i.indisunique != 't')
						AND (i.indisprimary != 't')";
				$col = 'index_name';
			break;

			case 'mysql_40':
			case 'mysql_41':
				$sql = 'SHOW KEYS
					FROM ' . $table_name;
				$col = 'Key_name';
			break;

			case 'oracle':
				$sql = "SELECT index_name
					FROM user_indexes
					WHERE table_name = '" . $table_name . "'
						AND generated = 'N'";
			break;

			case 'sqlite':
				$sql = "PRAGMA index_info('" . $table_name . "');";
				$col = 'name';
			break;
		}

		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if (($dbms == 'mysql_40' || $dbms == 'mysql_41') && !$row['Non_unique'])
			{
				continue;
			}

			$index_array[] = $row[$col];
		}
		$db->sql_freeresult($result);
	}

	return array_map('strtolower', $index_array);
}

/**
* Change column type (not name!)
*/
function sql_column_change($dbms, $table_name, $column_name, $column_data)
{
	global $dbms_type_map, $db;
	global $errored, $error_ary;

	$column_data = prepare_column_data($dbms, $column_data);

	switch ($dbms)
	{
		case 'firebird':
			// Change type...
			$sql = 'ALTER TABLE "' . $table_name . '" ALTER COLUMN "' . $column_name . '" TYPE ' . ' ' . $column_data['column_type_sql'];
			_sql($sql, $errored, $error_ary);
		break;

		case 'mssql':
			$sql = 'ALTER TABLE [' . $table_name . '] ALTER COLUMN [' . $column_name . '] ' . $column_data['column_type_sql'];
			_sql($sql, $errored, $error_ary);
		break;

		case 'mysql_40':
		case 'mysql_41':
			$sql = 'ALTER TABLE `' . $table_name . '` CHANGE `' . $column_name . '` `' . $column_name . '` ' . $column_data['column_type_sql'];
			_sql($sql, $errored, $error_ary);
		break;

		case 'oracle':
			$sql = 'ALTER TABLE ' . $table_name . ' MODIFY ' . $column_name . ' ' . $column_data['column_type_sql'];
			_sql($sql, $errored, $error_ary);
		break;

		case 'postgres':
			$sql = 'ALTER TABLE ' . $table_name . ' ';

			$sql_array = array();
			$sql_array[] = 'ALTER COLUMN ' . $column_name . ' TYPE ' . $column_data['column_type'];

			if ($column_data['null'] == 'NOT NULL')
			{
				$sql_array[] = 'ALTER COLUMN ' . $column_name . ' SET NOT NULL';
			}
			else
			{
				$sql_array[] = 'ALTER COLUMN ' . $column_name . ' DROP NOT NULL';
			}

			if (isset($column_data['default']))
			{
				$sql_array[] = 'ALTER COLUMN ' . $column_name . " SET DEFAULT '" . $column_data['default'] . "'";
			}

			// we don't want to double up on constraints if we change different number data types
			if (isset($column_data['constraint']))
			{
				$constraint_sql = "SELECT consrc as constraint_data
							FROM pg_constraint, pg_class bc
							WHERE conrelid = bc.oid
								AND bc.relname = '{$table_name}'
								AND NOT EXISTS (
									SELECT *
										FROM pg_constraint as c, pg_inherits as i
										WHERE i.inhrelid = pg_constraint.conrelid
											AND c.conname = pg_constraint.conname
											AND c.consrc = pg_constraint.consrc
											AND c.conrelid = i.inhparent
								)";

				$constraint_exists = false;

				$result = $db->sql_query($constraint_sql);
				while ($row = $db->sql_fetchrow($result))
				{
					if (trim($row['constraint_data']) == trim($column_data['constraint']))
					{
						$constraint_exists = true;
						break;
					}
				}
				$db->sql_freeresult($result);

				if (!$constraint_exists)
				{
					$sql_array[] = "ADD '" . $column_data['constraint'] . "'";
				}
			}

			$sql .= implode(', ', $sql_array);

			_sql($sql, $errored, $error_ary);
		break;

		case 'sqlite':

			$sql = "SELECT sql
				FROM sqlite_master 
				WHERE type = 'table' 
					AND name = '{$table_name}'
				ORDER BY type DESC, name;";
			$result = _sql($sql, $errored, $error_ary);

			if (!$result)
			{
				break;
			}

			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$db->sql_transaction('begin');

			// Create a temp table and populate it, destroy the existing one
			$db->sql_query(preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']));
			$db->sql_query('INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name);
			$db->sql_query('DROP TABLE ' . $table_name);

			preg_match('#\((.*)\)#s', $row['sql'], $matches);

			$new_table_cols = trim($matches[1]);
			$old_table_cols = preg_split('/,(?![\s\w]+\))/m', $new_table_cols);
			$column_list = array();

			foreach ($old_table_cols as $key => $declaration)
			{
				$entities = preg_split('#\s+#', trim($declaration));
				$column_list[] = $entities[0];
				if ($entities[0] == $column_name)
				{
					$old_table_cols[$key] = $column_name . ' ' . $column_data['column_type_sql'];
				}
			}

			$columns = implode(',', $column_list);

			// create a new table and fill it up. destroy the temp one
			$db->sql_query('CREATE TABLE ' . $table_name . ' (' . implode(',', $old_table_cols) . ');');
			$db->sql_query('INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;');
			$db->sql_query('DROP TABLE ' . $table_name . '_temp');

			$db->sql_transaction('commit');

		break;
	}
}

function utf8_new_clean_string($text)
{
	static $homographs = array();
	static $utf8_case_fold_nfkc = '';
	if (empty($homographs))
	{
		global $phpbb_root_path, $phpEx;
		if (!function_exists('utf8_case_fold_nfkc') || !file_exists($phpbb_root_path . 'includes/utf/data/confusables.' . $phpEx))
		{
			if (!file_exists($phpbb_root_path . 'install/data/confusables.' . $phpEx))
			{
				global $lang;
				trigger_error(sprintf($lang['UPDATE_REQUIRES_FILE'], $phpbb_root_path . 'install/data/confusables.' . $phpEx), E_USER_ERROR);
			}
			$homographs = include($phpbb_root_path . 'install/data/confusables.' . $phpEx);
			$utf8_case_fold_nfkc = 'utf8_new_case_fold_nfkc';
		}
		else
		{
			$homographs = include($phpbb_root_path . 'includes/utf/data/confusables.' . $phpEx);
			$utf8_case_fold_nfkc = 'utf8_case_fold_nfkc';
		}
	}

	$text = $utf8_case_fold_nfkc($text);
	$text = strtr($text, $homographs);
	// Other control characters
	$text = preg_replace('#(?:[\x00-\x1F\x7F]+|(?:\xC2[\x80-\x9F])+)#', '', $text);

	// we can use trim here as all the other space characters should have been turned
	// into normal ASCII spaces by now
	return trim($text);
}

?>