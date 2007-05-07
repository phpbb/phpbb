<?php
/** 
*
* @package install
* @version $Id$
* @copyright (c) 2006 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

$updates_to_version = '3.0.B5';

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
	// Changes from 3.0.b5 to the next version
	'3.0.b5'			=> array(
		// Add the following columns
		'add_columns'		=> array(
			SEARCH_WORDLIST_TABLE	=> array(
				'word_count'			=> array('UINT', 0),
			),
		),
		// Change the following columns...
		'change_columns'	=> array(
			TOPICS_TABLE		=> array(
				'poll_title'		=> array('STEXT_UNI', ''),
			),
			SESSIONS_TABLE		=> array(
				'session_forwarded_for'	=> array('VCHAR:255', ''),
			),
		),
		// Remove the following keys
		'drop_keys'		=> array(
			USERS_TABLE		=> array(
				'username_clean',
			),
			STYLES_IMAGESET_TABLE	=> array(
				'imgset_nm',
			),
			BOOKMARKS_TABLE		=> array(
				'order_id',
				'topic_user_id',
			),
		),
		'add_index'			=> array(
			SEARCH_WORDLIST_TABLE	=> array(
				'wrd_cnt'			=> array('word_count'),
			),
			ACL_GROUPS_TABLE		=> array(
				'auth_role_id'		=> array('auth_role_id'),
			),
			ACL_USERS_TABLE			=> array(
				'auth_role_id'		=> array('auth_role_id'),
			),
			ACL_ROLES_DATA_TABLE	=> array(
				'ath_opt_id'		=> array('auth_option_id'),
			),
			TOPICS_TABLE			=> array(
				'forum_appr_last'	=> array('forum_id', 'topic_approved', 'topic_last_post_id'),
			),
		),
		// Add the following unique indexes
		'add_unique_index'	=> array(
			SEARCH_WORDMATCH_TABLE	=> array(
				'unq_mtch'		=> array('word_id', 'post_id', 'title_match'),
			),
			USERS_TABLE				=> array(
				'username_clean'	=> array('username_clean'),
			),
		),
		// Drop the following columns
		'drop_columns'		=> array(
			STYLES_IMAGESET_TABLE	=> array(
				'site_logo',
				'upload_bar',
				'poll_left',
				'poll_center',
				'poll_right',
				'icon_friend',
				'icon_foe',
				'forum_link',
				'forum_read',
				'forum_read_locked',
				'forum_read_subforum',
				'forum_unread',
				'forum_unread_locked',
				'forum_unread_subforum',
				'topic_moved',
				'topic_read',
				'topic_read_mine',
				'topic_read_hot',
				'topic_read_hot_mine',
				'topic_read_locked',
				'topic_read_locked_mine',
				'topic_unread',
				'topic_unread_mine',
				'topic_unread_hot',
				'topic_unread_hot_mine',
				'topic_unread_locked',
				'topic_unread_locked_mine',
				'sticky_read',
				'sticky_read_mine',
				'sticky_read_locked',
				'sticky_read_locked_mine',
				'sticky_unread',
				'sticky_unread_mine',
				'sticky_unread_locked',
				'sticky_unread_locked_mine',
				'announce_read',
				'announce_read_mine',
				'announce_read_locked',
				'announce_read_locked_mine',
				'announce_unread',
				'announce_unread_mine',
				'announce_unread_locked',
				'announce_unread_locked_mine',
				'global_read',
				'global_read_mine',
				'global_read_locked',
				'global_read_locked_mine',
				'global_unread',
				'global_unread_mine',
				'global_unread_locked',
				'global_unread_locked_mine',
				'pm_read',
				'pm_unread',
				'icon_contact_aim',
				'icon_contact_email',
				'icon_contact_icq',
				'icon_contact_jabber',
				'icon_contact_msnm',
				'icon_contact_pm',
				'icon_contact_yahoo',
				'icon_contact_www',
				'icon_post_delete',
				'icon_post_edit',
				'icon_post_info',
				'icon_post_quote',
				'icon_post_report',
				'icon_post_target',
				'icon_post_target_unread',
				'icon_topic_attach',
				'icon_topic_latest',
				'icon_topic_newest',
				'icon_topic_reported',
				'icon_topic_unapproved',
				'icon_user_online',
				'icon_user_offline',
				'icon_user_profile',
				'icon_user_search',
				'icon_user_warn',
				'button_pm_forward',
				'button_pm_new',
				'button_pm_reply',
				'button_topic_locked',
				'button_topic_new',
				'button_topic_reply',
				'user_icon1',
				'user_icon2',
				'user_icon3',
				'user_icon4',
				'user_icon5',
				'user_icon6',
				'user_icon7',
				'user_icon8',
				'user_icon9',
				'user_icon10'
			),
			BOOKMARKS_TABLE		=> array(
				'order_id',
			),
		),
		// Adding primary key
		'add_primary_keys'		=> array(
			BOOKMARKS_TABLE		=> array(
				'topic_id', 'user_id',
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

$current_version = strtolower($config['version']);
$latest_version = strtolower($updates_to_version);
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
if (version_compare($current_version, '3.0.b5', '<='))
{
	switch ($map_dbms)
	{
		case 'sqlite':
		case 'firebird':
			$db->sql_query('DELETE FROM ' . STYLES_IMAGESET_TABLE);
			$db->sql_query('DELETE FROM ' . STYLES_TEMPLATE_TABLE);
			$db->sql_query('DELETE FROM ' . STYLES_TABLE);
			$db->sql_query('DELETE FROM ' . STYLES_THEME_TABLE);

//			$db->sql_query('DELETE FROM ' . STYLES_IMAGESET_DATA_TABLE);
		break;

		default:
			$db->sql_query('TRUNCATE TABLE ' . STYLES_IMAGESET_TABLE);
			$db->sql_query('TRUNCATE TABLE ' . STYLES_TEMPLATE_TABLE);
			$db->sql_query('TRUNCATE TABLE ' . STYLES_TABLE);
			$db->sql_query('TRUNCATE TABLE ' . STYLES_THEME_TABLE);

//			This table does not exist, as well as the constant not exist...
//			$db->sql_query('TRUNCATE TABLE ' . STYLES_IMAGESET_DATA_TABLE);
		break;
	}

	$tablename = $table_prefix . 'styles_imageset_data';
	switch ($map_dbms)
	{
		case 'mysql_41':
			$sql_ary = array(
				"CREATE TABLE $tablename (
					image_id smallint(4) UNSIGNED NOT NULL auto_increment,
					image_name varchar(200) DEFAULT '' NOT NULL,
					image_filename varchar(200) DEFAULT '' NOT NULL,
					image_lang varchar(30) DEFAULT '' NOT NULL,
					image_height smallint(4) UNSIGNED DEFAULT '0' NOT NULL,
					image_width smallint(4) UNSIGNED DEFAULT '0' NOT NULL,
					imageset_id tinyint(4) DEFAULT '0' NOT NULL,
					PRIMARY KEY (image_id),
					KEY i_id (imageset_id)
				) CHARACTER SET `utf8` COLLATE `utf8_bin`");
		break;

		case 'mysql_40':
			$sql_ary = array(
				"CREATE TABLE $tablename (
					image_id smallint(4) UNSIGNED NOT NULL auto_increment,
					image_name varbinary(200) DEFAULT '' NOT NULL,
					image_filename varbinary(200) DEFAULT '' NOT NULL,
					image_lang varbinary(30) DEFAULT '' NOT NULL,
					image_height smallint(4) UNSIGNED DEFAULT '0' NOT NULL,
					image_width smallint(4) UNSIGNED DEFAULT '0' NOT NULL,
					imageset_id tinyint(4) DEFAULT '0' NOT NULL,
					PRIMARY KEY (image_id),
					KEY i_id (imageset_id)
				);");
		break;

		case 'mssql':
			$sql_ary = array(
				"CREATE TABLE [$tablename] (
					[image_id] [int] IDENTITY (1, 1) NOT NULL ,
					[image_name] [varchar] (200) DEFAULT ('') NOT NULL ,
					[image_filename] [varchar] (200) DEFAULT ('') NOT NULL ,
					[image_lang] [varchar] (30) DEFAULT ('') NOT NULL ,
					[image_height] [int] DEFAULT (0) NOT NULL ,
					[image_width] [int] DEFAULT (0) NOT NULL ,
					[imageset_id] [int] DEFAULT (0) NOT NULL 
				) ON [PRIMARY]",

				"ALTER TABLE [$tablename] WITH NOCHECK ADD 
					CONSTRAINT [PK_$tablename] PRIMARY KEY  CLUSTERED 
					(
						[image_id]
					)  ON [PRIMARY]",

				"CREATE  INDEX [i_id] ON [$tablename]([imageset_id]) ON [PRIMARY]",
			);
		break;

		case 'oracle':
			$sql_ary = array(
				"CREATE TABLE $tablename (
					image_id number(4) NOT NULL,
					image_name varchar2(200) DEFAULT '' ,
					image_filename varchar2(200) DEFAULT '' ,
					image_lang varchar2(30) DEFAULT '' ,
					image_height number(4) DEFAULT '0' NOT NULL,
					image_width number(4) DEFAULT '0' NOT NULL,
					imageset_id number(4) DEFAULT '0' NOT NULL,
					CONSTRAINT pk_phpbb_styles_imageset_data PRIMARY KEY (image_id)
				)",

				"CREATE INDEX {$tablename}_i_id ON $tablename (imageset_id)",

				"CREATE SEQUENCE {$tablename}_imgset_id_seq",

				"CREATE OR REPLACE TRIGGER t_$tablename
				BEFORE INSERT ON $tablename
				FOR EACH ROW WHEN (
					new.image_id IS NULL OR new.image_id = 0
				)
				BEGIN
					SELECT {$tablename}_seq.nextval
					INTO :new.image_id
					FROM dual;
				END",
			);
		break;

		case 'postgres':
			$sql_ary = array(
				"CREATE SEQUENCE {$tablename}_seq;",

				"CREATE TABLE $tablename (
					image_id INT2 DEFAULT nextval('{$tablename}_seq'),
					image_name varchar(200) DEFAULT '' NOT NULL,
					image_filename varchar(200) DEFAULT '' NOT NULL,
					image_lang varchar(30) DEFAULT '' NOT NULL,
					image_height INT2 DEFAULT '0' NOT NULL CHECK (image_height >= 0),
					image_width INT2 DEFAULT '0' NOT NULL CHECK (image_width >= 0),
					imageset_id INT2 DEFAULT '0' NOT NULL,
					PRIMARY KEY (image_id)
				);",

				"CREATE INDEX {$tablename}_i_id ON $tablename (imageset_id);"
			);
		break;

		case 'sqlite':
			$sql_ary = array(
				"CREATE TABLE $tablename (
					image_id INTEGER PRIMARY KEY NOT NULL ,
					image_name varchar(200) NOT NULL DEFAULT '',
					image_filename varchar(200) NOT NULL DEFAULT '',
					image_lang varchar(30) NOT NULL DEFAULT '',
					image_height INTEGER UNSIGNED NOT NULL DEFAULT '0',
					image_width INTEGER UNSIGNED NOT NULL DEFAULT '0',
					imageset_id tinyint(4) NOT NULL DEFAULT '0'
				);",

				"CREATE INDEX  {$tablename}_i_id ON  $tablename (imageset_id);"
			);
		break;

		case 'firebird':
			$sql_ary = array(
				"CREATE TABLE $tablename (
					image_id INTEGER NOT NULL,
					image_name VARCHAR(200) CHARACTER SET NONE DEFAULT '' NOT NULL,
					image_filename VARCHAR(200) CHARACTER SET NONE DEFAULT '' NOT NULL,
					image_lang VARCHAR(30) CHARACTER SET NONE DEFAULT '' NOT NULL,
					image_height INTEGER DEFAULT 0 NOT NULL,
					image_width INTEGER DEFAULT 0 NOT NULL,
					imageset_id INTEGER DEFAULT 0 NOT NULL
				);",

				"ALTER TABLE $tablename ADD PRIMARY KEY (image_id);",

				"CREATE INDEX {$tablename}_i_id ON $tablename(imageset_id);",

				"CREATE GENERATOR {$tablename}_gen;",

				"SET GENERATOR {$tablename}_gen TO 0;",

				"CREATE TRIGGER t_$tablename FOR $tablename
				BEFORE INSERT
				AS
				BEGIN
					NEW.image_id = GEN_ID({$tablename}_gen, 1);
				END"
			);
		break;
	}

	// add the various statements
	foreach ($sql_ary as $sql)
	{
		$db->sql_query($sql);
	}

	$data = "INSERT INTO phpbb_styles (style_name, style_copyright, style_active, template_id, theme_id, imageset_id) VALUES ('prosilver', '&copy; phpBB Group', 1, 1, 1, 1);
	INSERT INTO phpbb_styles (style_name, style_copyright, style_active, template_id, theme_id, imageset_id) VALUES ('subsilver2', '&copy; phpBB Group', 1, 2, 2, 2);
	INSERT INTO phpbb_styles_imageset (imageset_name, imageset_copyright, imageset_path) VALUES ('prosilver', '&copy; phpBB Group', 'prosilver');
	INSERT INTO phpbb_styles_imageset (imageset_name, imageset_copyright, imageset_path) VALUES ('subsilver2', '&copy; phpBB Group', 'subsilver2');
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('site_logo', 'site_logo.gif', '', 94, 170, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('upload_bar', 'upload_bar.gif', '', 16, 280, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('poll_left', 'poll_left.gif', '', 12, 4, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('poll_center', 'poll_center.gif', '', 12, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('poll_right', 'poll_right.gif', '', 12, 4, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('forum_link', 'forum_link.gif', '', 25, 46, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('forum_read', 'forum_read.gif', '', 25, 46, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('forum_read_locked', 'forum_read_locked.gif', '', 25, 46, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('forum_read_subforum', 'forum_read_subforum.gif', '', 25, 46, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('forum_unread', 'forum_unread.gif', '', 25, 46, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('forum_unread_locked', 'forum_unread_locked.gif', '', 25, 46, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('forum_unread_subforum', 'forum_unread_subforum.gif', '', 25, 46, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_moved', 'topic_moved.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_read', 'topic_read.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_read_mine', 'topic_read_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_read_hot', 'topic_read_hot.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_read_hot_mine', 'topic_read_hot_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_read_locked', 'topic_read_locked.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_read_locked_mine', 'topic_read_locked_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_unread', 'topic_unread.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_unread_mine', 'topic_unread_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_unread_hot', 'topic_unread_hot.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_unread_hot_mine', 'topic_unread_hot_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_unread_locked', 'topic_unread_locked.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_unread_locked_mine', 'topic_unread_locked_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('sticky_read', 'sticky_read.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('sticky_read_mine', 'sticky_read_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('sticky_read_locked', 'sticky_read_locked.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('sticky_read_locked_mine', 'sticky_read_locked_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('sticky_unread', 'sticky_unread.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('sticky_unread_mine', 'sticky_unread_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('sticky_unread_locked', 'sticky_unread_locked.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('sticky_unread_locked_mine', 'sticky_unread_locked_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('announce_read', 'announce_read.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('announce_read_mine', 'announce_read_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('announce_read_locked', 'announce_read_locked.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('announce_read_locked_mine', 'announce_read_locked_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('announce_unread', 'announce_unread.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('announce_unread_mine', 'announce_unread_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('announce_unread_locked', 'announce_unread_locked.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('announce_unread_locked_mine', 'announce_unread_locked_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('global_read', 'announce_read.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('global_read_mine', 'announce_read_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('global_read_locked', 'announce_read_locked.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('global_read_locked_mine', 'announce_read_locked_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('global_unread', 'announce_unread.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('global_unread_mine', 'announce_unread_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('global_unread_locked', 'announce_unread_locked.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('global_unread_locked_mine', 'announce_unread_locked_mine.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('pm_read', 'topic_read.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('pm_unread', 'topic_unread.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_post_target', 'icon_post_target.gif', '', 9, 12, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_post_target_unread', 'icon_post_target_unread.gif', '', 9, 12, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_topic_attach', 'icon_topic_attach.gif', '', 18, 14, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_topic_latest', 'icon_topic_latest.gif', '', 9, 18, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_topic_newest', 'icon_topic_newest.gif', '', 9, 18, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_topic_reported', 'icon_topic_reported.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_topic_unapproved', 'icon_topic_unapproved.gif', '', 18, 19, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_aim', 'icon_contact_aim.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_email', 'icon_contact_email.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_icq', 'icon_contact_icq.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_jabber', 'icon_contact_jabber.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_msnm', 'icon_contact_msnm.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_pm', 'icon_contact_pm.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_yahoo', 'icon_contact_yahoo.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_www', 'icon_contact_www.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_post_delete', 'icon_post_delete.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_post_edit', 'icon_post_edit.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_post_info', 'icon_post_info.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_post_quote', 'icon_post_quote.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_post_report', 'icon_post_report.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_user_online', 'icon_user_online.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_user_offline', 'icon_user_offline.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_user_profile', 'icon_user_profile.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_user_search', 'icon_user_search.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_user_warn', 'icon_user_warn.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('button_pm_new', 'button_pm_new.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('button_pm_reply', 'button_pm_reply.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('button_topic_locked', 'button_topic_locked.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('button_topic_new', 'button_topic_new.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('button_topic_reply', 'button_topic_reply.gif', 'en', 0, 0, 2);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('site_logo', 'site_logo.gif', '', 52, 139, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('forum_link', 'forum_link.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('forum_read', 'forum_read.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('forum_read_locked', 'forum_read_locked.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('forum_read_subforum', 'forum_read_subforum.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('forum_unread', 'forum_unread.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('forum_unread_locked', 'forum_unread_locked.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('forum_unread_subforum', 'forum_unread_subforum.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_moved', 'topic_moved.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_read', 'topic_read.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_read_mine', 'topic_read_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_read_hot', 'topic_read_hot.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_read_hot_mine', 'topic_read_hot_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_read_locked', 'topic_read_locked.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_read_locked_mine', 'topic_read_locked_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_unread', 'topic_unread.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_unread_mine', 'topic_unread_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_unread_hot', 'topic_unread_hot.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_unread_hot_mine', 'topic_unread_hot_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_unread_locked', 'topic_unread_locked.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('topic_unread_locked_mine', 'topic_unread_locked_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('sticky_read', 'sticky_read.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('sticky_read_mine', 'sticky_read_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('sticky_read_locked', 'sticky_read_locked.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('sticky_read_locked_mine', 'sticky_read_locked_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('sticky_unread', 'sticky_unread.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('sticky_unread_mine', 'sticky_unread_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('sticky_unread_locked', 'sticky_unread_locked.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('sticky_unread_locked_mine', 'sticky_unread_locked_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('announce_read', 'announce_read.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('announce_read_mine', 'announce_read_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('announce_read_locked', 'announce_read_locked.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('announce_read_locked_mine', 'announce_read_locked_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('announce_unread', 'announce_unread.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('announce_unread_mine', 'announce_unread_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('announce_unread_locked', 'announce_unread_locked.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('announce_unread_locked_mine', 'announce_unread_locked_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('global_read', 'announce_read.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('global_read_mine', 'announce_read_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('global_read_locked', 'announce_read_locked.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('global_read_locked_mine', 'announce_read_locked_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('global_unread', 'announce_unread.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('global_unread_mine', 'announce_unread_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('global_unread_locked', 'announce_unread_locked.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('global_unread_locked_mine', 'announce_unread_locked_mine.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('pm_read', 'topic_read.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('pm_unread', 'topic_unread.gif', '', 27, 27, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_back_top', 'icon_back_top.gif', '', 11, 11, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_aim', 'icon_contact_aim.gif', '', 20, 20, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_email', 'icon_contact_email.gif', '', 20, 20, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_icq', 'icon_contact_icq.gif', '', 20, 20, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_jabber', 'icon_contact_jabber.gif', '', 20, 20, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_msnm', 'icon_contact_msnm.gif', '', 20, 20, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_www', 'icon_contact_www.gif', '', 20, 20, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_yahoo', 'icon_contact_yahoo.gif', '', 20, 20, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_post_delete', 'icon_post_delete.gif', '', 20, 20, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_post_info', 'icon_post_info.gif', '', 20, 20, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_post_report', 'icon_post_report.gif', '', 20, 20, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_post_target', 'icon_post_target.gif', '', 9, 11, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_post_target_unread', 'icon_post_target_unread.gif', '', 9, 11, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_topic_attach', 'icon_topic_attach.gif', '', 10, 7, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_topic_latest', 'icon_topic_latest.gif', '', 9, 11, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_topic_newest', 'icon_topic_newest.gif', '', 9, 11, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_topic_reported', 'icon_topic_reported.gif', '', 14, 16, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_topic_unapproved', 'icon_topic_unapproved.gif', '', 14, 16, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_user_profile', 'icon_user_profile.gif', '', 11, 11, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_user_warn', 'icon_user_warn.gif', '', 20, 20, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_pm', 'icon_contact_pm.gif', 'en', 20, 28, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_post_edit', 'icon_post_edit.gif', 'en', 20, 42, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_post_quote', 'icon_post_quote.gif', 'en', 20, 54, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_user_online', 'icon_user_online.gif', 'en', 58, 58, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_user_offline', 'icon_user_offline.gif', 'en', 0, 0, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_user_search', 'icon_user_search.gif', 'en', 0, 0, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('button_pm_forward', 'button_pm_forward.gif', 'en', 25, 96, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('button_pm_new', 'button_pm_new.gif', 'en', 25, 84, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('button_pm_reply', 'button_pm_reply.gif', 'en', 25, 96, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('button_topic_locked', 'button_topic_locked.gif', 'en', 25, 88, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('button_topic_new', 'button_topic_new.gif', 'en', 25, 96, 1);
	INSERT INTO phpbb_styles_imageset_data (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('button_topic_reply', 'button_topic_reply.gif', 'en', 25, 96, 1);";
	$data = str_replace('phpbb_', $table_prefix, $data);
	$sql_ary = explode("\n", $data);

	foreach ($sql_ary as $sql)
	{
		$db->sql_query($sql);
	}

	$user_char_ary = array('.*' => 'USERNAME_CHARS_ANY', '[a-z]+' => 'USERNAME_ALPHA_ONLY', '[-\]_+ [a-z]+' => 'USERNAME_ALPHA_SPACERS', '\w+' => 'USERNAME_LETTER_NUM', '[-\]_+ [\w]+' => 'USERNAME_LETTER_NUM_SPACERS', '[\x01-\x7F]+' => 'USERNAME_ASCII');

	set_config('allow_name_chars', $config['allow_name_chars']);

	// sorting thang
	if ($map_dbms === 'mysql_41')
	{
		sql_column_change($map_dbms, TOPICS_TABLE, 'topic_title', array('XSTEXT_UNI', '', 'true_sort'));
	}

	if ($config['fulltext_native_common_thres'] == 20)
	{
		set_config('fulltext_native_common_thres', '5');
	}

	set_config('default_style', '1');

	$sql = 'SELECT m.word_id, COUNT(m.word_id) as word_count
		FROM ' . SEARCH_WORDMATCH_TABLE . ' m, ' . SEARCH_WORDLIST_TABLE . ' w
		WHERE m.word_id = w.word_id
			AND w.word_common = 0
		GROUP BY m.word_id
		ORDER BY word_count ASC';
	$result = $db->sql_query($sql);

	$value = 0;
	$sql_in = array();
	while ($row = $db->sql_fetchrow($result))
	{
		if ($value != $row['word_count'] && $value != 0 || sizeof($sql_in) > 500)
		{
			$sql = 'UPDATE ' . SEARCH_WORDLIST_TABLE . '
				SET word_count = ' . $value . '
				WHERE ' . $db->sql_in_set('word_id', $sql_in);
			$db->sql_query($sql);
			$sql_in = array();
		}
		$value = $row['word_count'];
		$sql_in[] = $row['word_id'];
	}

	if (sizeof($sql_in))
	{
		$sql = 'UPDATE ' . SEARCH_WORDLIST_TABLE . '
			SET word_count = ' . $value . '
			WHERE ' . $db->sql_in_set('word_id', $sql_in);
		$db->sql_query($sql);
	}
	unset($sql_in);

	set_config('avatar_salt', md5(mt_rand()));
	set_config('captcha_gd_x_grid', 25);
	set_config('captcha_gd_y_grid', 25);
	set_config('captcha_gd_foreground_noise', 1);

	$sql = 'UPDATE ' . ACL_OPTIONS_TABLE . "
		SET is_local = 0
		WHERE auth_option = 'm_warn'";
	$db->sql_query($sql);

	$cache->destroy('_acl_options');

	$sql = 'UPDATE ' . MODULES_TABLE . '
		SET module_auth = \'acl_m_warn && acl_f_read,$id\'
		WHERE module_basename = \'warn\'
			AND module_mode = \'warn_post\'
			AND module_class = \'mcp\'';
	$db->sql_query($sql);

	$cache->destroy('_modules_mcp');

	$sql = 'UPDATE ' . USERS_TABLE . " SET user_permissions = ''";
	$db->sql_query($sql);

	$no_updates = false;
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

	<p><a href="<?php echo append_sid("{$phpbb_root_path}install/index.{$phpEx}", "mode=update&amp;sub=file_check&amp;lang=$language"); ?>">&raquo; <?php echo (isset($lang['CONTINUE_UPDATE_NOW'])) ? $lang['CONTINUE_UPDATE_NOW'] : 'Continue the update process now'; ?></a></p>

<?php
}

// Add database update to log

$user->ip = (!empty($_SERVER['REMOTE_ADDR'])) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '';
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
		Powered by phpBB &copy; <?php echo date('Y'); ?> <a href="http://www.phpbb.com/">phpBB Group</a>
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
				echo '<li>' . $lang['ERROR'] . ' :: <strong>' . $error_ary['error_code'][$i]['message'] . '</strong><br />';
				echo $lang['SQL'] . ' :: <strong>' . $error_ary['sql'][$i] . '</strong><br /><br /></li>';
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

	$db->sql_return_on_error(true);

	$sql = "SELECT $column_name FROM $table";
	$result = $db->sql_query_limit($sql, 1);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$error = ($db->sql_error_triggered) ? true : false;

	$db->sql_return_on_error(false);

	return (!$error) ? true : false;
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

			if (!is_null($column_data[1]))
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
			}

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
			$sql .= " {$column_type} ";

			$sql .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}' " : '';
			$sql .= 'NOT NULL';

			// Unsigned? Then add a CHECK contraint
			if (in_array($orig_column_type, $unsigned_types))
			{
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

	return array(
		'column_type_sql'		=> $sql,
	);
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
			$sql = 'DROP INDEX ' . $table_name . '\.' . $index_name . ' ON ' . $table_name;
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
		case 'mysql_40':
		case 'mysql_41':
		case 'oracle':
		case 'sqlite':
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
		case 'mysql_40':
		case 'mysql_41':
		case 'oracle':
		case 'sqlite':
			$sql = 'CREATE INDEX ' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			_sql($sql, $errored, $error_ary);
		break;

		case 'mssql':
			$sql = 'CREATE INDEX ' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ') ON [PRIMARY]';
			_sql($sql, $errored, $error_ary);
		break;
	}
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
			$sql = 'ALTER TABLE ' . $table_name . ' ALTER COLUMN ' . $column_name . ' SET ' . $column_data['column_type_sql'];
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

?>