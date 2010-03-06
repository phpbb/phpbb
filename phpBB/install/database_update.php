<?php
/**
*
* @package install
* @version $Id$
* @copyright (c) 2006 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

$updates_to_version = '3.0.7-PL1';

// Enter any version to update from to test updates. The version within the db will not be updated.
$debug_from_version = false;

// Which oldest version does this updater support?
$oldest_from_version = '3.0.0';

// Return if we "just include it" to find out for which version the database update is responsible for
if (defined('IN_PHPBB') && defined('IN_INSTALL'))
{
	return;
}

/**
*/
define('IN_PHPBB', true);
define('IN_INSTALL', true);

$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// Report all errors, except notices and deprecation messages
if (!defined('E_DEPRECATED'))
{
	define('E_DEPRECATED', 8192);
}
//error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
error_reporting(E_ALL);

@set_time_limit(0);

// Include essential scripts
include($phpbb_root_path . 'config.' . $phpEx);

if (!defined('PHPBB_INSTALLED') || empty($dbms) || empty($acm_type))
{
	die("Please read: <a href='../docs/INSTALL.html'>INSTALL.html</a> before attempting to update.");
}

// Load Extensions
if (!empty($load_extensions) && function_exists('dl'))
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

if (file_exists($phpbb_root_path . 'includes/functions_content.' . $phpEx))
{
	require($phpbb_root_path . 'includes/functions_content.' . $phpEx);
}

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
	@set_magic_quotes_runtime(0);
	define('STRIP', (get_magic_quotes_gpc()) ? true : false);
}

$user = new user();
$cache = new cache();
$db = new $sql_db();

// Add own hook handler, if present. :o
if (file_exists($phpbb_root_path . 'includes/hooks/index.' . $phpEx))
{
	require($phpbb_root_path . 'includes/hooks/index.' . $phpEx);
	$phpbb_hook = new phpbb_hook(array('exit_handler', 'phpbb_user_session_handler', 'append_sid', array('template', 'display')));

	foreach ($cache->obtain_hooks() as $hook)
	{
		@include($phpbb_root_path . 'includes/hooks/' . $hook . '.' . $phpEx);
	}
}
else
{
	$phpbb_hook = false;
}

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

// We do not include DB Tools here, because we can not be sure the file is up-to-date ;)
// Instead, this file defines a clean db_tools version (we are also not able to provide a different file, else the database update will not work standalone)
$db_tools = new updater_db_tools($db, true);

$database_update_info = database_update_info();

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
					<div id="main" class="install-body">

	<h1><?php echo $lang['UPDATING_TO_LATEST_STABLE']; ?></h1>

	<br />

	<p><?php echo $lang['DATABASE_TYPE']; ?> :: <strong><?php echo $db->sql_layer; ?></strong><br />
<?php

if ($debug_from_version !== false)
{
	$config['version'] = $debug_from_version;
}

echo $lang['PREVIOUS_VERSION'] . ' :: <strong>' . $config['version'] . '</strong><br />';
echo $lang['UPDATED_VERSION'] . ' :: <strong>' . $updates_to_version . '</strong></p>';

$current_version = str_replace('rc', 'RC', strtolower($config['version']));
$latest_version = str_replace('rc', 'RC', strtolower($updates_to_version));
$orig_version = $config['version'];

// Fill DB version
if (empty($config['dbms_version']))
{
	set_config('dbms_version', $db->sql_server_info(true));
}

// Firebird update from Firebord 2.0 to 2.1+ required?
if ($db->sql_layer == 'firebird')
{
	// We do not trust any PHP5 function enabled, we will simply test for a function new in 2.1
	$db->sql_return_on_error(true);

	$sql = 'SELECT 1 FROM RDB$DATABASE
		WHERE BIN_AND(10, 1) = 0';
	$result = $db->sql_query($sql);

	if (!$result || $db->sql_error_triggered)
	{
		echo '<br /><br />';
		echo '<h1>' . $lang['ERROR'] . '</h1><br />';

		echo '<p>' . $lang['FIREBIRD_DBMS_UPDATE_REQUIRED'] . '</p>';

		_print_footer();

		exit_handler();
		exit;
	}

	$db->sql_freeresult($result);
	$db->sql_return_on_error(false);
}

// MySQL update from MySQL 3.x/4.x to > 4.1.x required?
if ($db->sql_layer == 'mysql' || $db->sql_layer == 'mysql4' || $db->sql_layer == 'mysqli')
{
	// Verify by fetching column... if the column type matches the new type we update dbms_version...
	$sql = "SHOW COLUMNS FROM " . CONFIG_TABLE;
	$result = $db->sql_query($sql);

	$column_type = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$field = strtolower($row['Field']);

		if ($field == 'config_value')
		{
			$column_type = strtolower($row['Type']);
			break;
		}
	}
	$db->sql_freeresult($result);

	// If column type is blob, but mysql version says we are on > 4.1.3, then the schema needs an update
	if (strpos($column_type, 'blob') !== false && version_compare($db->sql_server_info(true), '4.1.3', '>='))
	{
		echo '<br /><br />';
		echo '<h1>' . $lang['ERROR'] . '</h1><br />';

		echo '<p>' . sprintf($lang['MYSQL_SCHEMA_UPDATE_REQUIRED'], $config['dbms_version'], $db->sql_server_info(true)) . '</p>';

		_print_footer();

		exit_handler();
		exit;
	}
}

// Now check if the user wants to update from a version we no longer support updates from
if (version_compare($current_version, $oldest_from_version, '<'))
{
	echo '<br /><br /><h1>' . $lang['ERROR'] . '</h1><br />';
	echo '<p>' . sprintf($lang['DB_UPDATE_NOT_SUPPORTED'], $oldest_from_version, $current_version) . '</p>';

	_print_footer();
	exit_handler();
	exit;
}

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
	<br /><br />

	<h1><?php echo $lang['UPDATE_DATABASE_SCHEMA']; ?></h1>

	<br />
	<p><?php echo $lang['PROGRESS']; ?> :: <strong>

<?php

flush();

// We go through the schema changes from the lowest to the highest version
// We try to also include versions 'in-between'...
$no_updates = true;
$versions = array_keys($database_update_info);
for ($i = 0; $i < sizeof($versions); $i++)
{
	$version = $versions[$i];
	$schema_changes = $database_update_info[$version];

	$next_version = (isset($versions[$i + 1])) ? $versions[$i + 1] : $updates_to_version;

	// If the installed version to be updated to is < than the current version, and if the current version is >= as the version to be updated to next, we will skip the process
	if (version_compare($version, $current_version, '<') && version_compare($current_version, $next_version, '>='))
	{
		continue;
	}

	if (!sizeof($schema_changes))
	{
		continue;
	}

	$no_updates = false;

	// We run one index after the other... to be consistent with schema changes...
	foreach ($schema_changes as $key => $changes)
	{
		$statements = $db_tools->perform_schema_changes(array($key => $changes));

		foreach ($statements as $sql)
		{
			_sql($sql, $errored, $error_ary);
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
$versions = array_keys($database_update_info);

// some code magic
for ($i = 0; $i < sizeof($versions); $i++)
{
	$version = $versions[$i];
	$next_version = (isset($versions[$i + 1])) ? $versions[$i + 1] : $updates_to_version;

	// If the installed version to be updated to is < than the current version, and if the current version is >= as the version to be updated to next, we will skip the process
	if (version_compare($version, $current_version, '<') && version_compare($current_version, $next_version, '>='))
	{
		continue;
	}

	change_database_data($no_updates, $version);
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

if ($debug_from_version === false)
{
	// update the version
	$sql = "UPDATE " . CONFIG_TABLE . "
		SET config_value = '$updates_to_version'
		WHERE config_name = 'version'";
	_sql($sql, $errored, $error_ary);
}

// Reset permissions
$sql = 'UPDATE ' . USERS_TABLE . "
	SET user_permissions = '',
		user_perm_from = 0";
_sql($sql, $errored, $error_ary);

// Update the dbms version if everything is ok...
set_config('dbms_version', $db->sql_server_info(true));

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

_print_footer();

garbage_collection();

if (function_exists('exit_handler'))
{
	exit_handler();
}

/**
* Print out footer
*/
function _print_footer()
{
	echo <<<EOF
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
EOF;
}

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
		$error_ary['error_code'][] = $db->sql_error_returned;
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

function _add_modules($modules_to_install)
{
	global $phpbb_root_path, $phpEx, $db;

	include_once($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);

	$_module = new acp_modules();

	foreach ($modules_to_install as $module_mode => $module_data)
	{
		$_module->module_class = $module_data['class'];

		// Determine parent id first
		$sql = 'SELECT module_id
			FROM ' . MODULES_TABLE . "
			WHERE module_class = '" . $db->sql_escape($module_data['class']) . "'
				AND module_langname = '" . $db->sql_escape($module_data['cat']) . "'
				AND module_mode = ''
				AND module_basename = ''";
		$result = $db->sql_query($sql);

		// There may be more than one categories with the same name
		$categories = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$categories[] = (int) $row['module_id'];
		}
		$db->sql_freeresult($result);

		if (!sizeof($categories))
		{
			continue;
		}

		// Add the module to all categories found
		foreach ($categories as $parent_id)
		{
			// Check if the module already exists
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_basename = '" . $db->sql_escape($module_data['base']) . "'
					AND module_class = '" . $db->sql_escape($module_data['class']) . "'
					AND module_langname = '" . $db->sql_escape($module_data['title']) . "'
					AND module_mode = '" . $db->sql_escape($module_mode) . "'
					AND module_auth = '" . $db->sql_escape($module_data['auth']) . "'
					AND parent_id = {$parent_id}";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			// If it exists, we simply continue with the next category
			if ($row)
			{
				continue;
			}

			// Build the module sql row
			$module_row = array(
				'module_basename'	=> $module_data['base'],
				'module_enabled'	=> (isset($module_data['enabled'])) ? (int) $module_data['enabled'] : 1,
				'module_display'	=> (isset($module_data['display'])) ? (int) $module_data['display'] : 1,
				'parent_id'			=> $parent_id,
				'module_class'		=> $module_data['class'],
				'module_langname'	=> $module_data['title'],
				'module_mode'		=> $module_mode,
				'module_auth'		=> $module_data['auth'],
			);

			$_module->update_module_data($module_row, true);

			// Ok, do we need to re-order the module, move it up or down?
			if (!isset($module_data['after']))
			{
				continue;
			}

			$after_mode = $module_data['after'][0];
			$after_langname = $module_data['after'][1];

			// First of all, get the module id for the module this one has to be placed after
			$sql = 'SELECT left_id
				FROM ' . MODULES_TABLE . "
				WHERE module_class = '" . $db->sql_escape($module_data['class']) . "'
					AND module_basename = '" . $db->sql_escape($module_data['base']) . "'
					AND module_langname = '" . $db->sql_escape($after_langname) . "'
					AND module_mode = '" . $db->sql_escape($after_mode) . "'
					AND parent_id = '{$parent_id}'";
			$result = $db->sql_query($sql);
			$first_left_id = (int) $db->sql_fetchfield('left_id');
			$db->sql_freeresult($result);

			if (!$first_left_id)
			{
				continue;
			}

			// Ok, count the number of modules between $after_mode and the added module
			$sql = 'SELECT COUNT(module_id) as num_modules
				FROM ' . MODULES_TABLE . "
				WHERE module_class = '" . $db->sql_escape($module_data['class']) . "'
					AND parent_id = {$parent_id}
					AND left_id BETWEEN {$first_left_id} AND {$module_row['left_id']}
				GROUP BY left_id
				ORDER BY left_id";
			$result = $db->sql_query($sql);
			$steps = (int) $db->sql_fetchfield('num_modules');
			$db->sql_freeresult($result);

			// We need to substract 2
			$steps -= 2;

			if ($steps <= 0)
			{
				continue;
			}

			// Ok, move module up $num_modules times. ;)
			$_module->move_module_by($module_row, 'move_up', $steps);
		}
	}

	$_module->remove_cache_file();
}

/****************************************************************************
* ADD YOUR DATABASE SCHEMA CHANGES HERE										*
*****************************************************************************/
function database_update_info()
{
	return array(
		// Changes from 3.0.0 to the next version
		'3.0.0'			=> array(
			// Add the following columns
			'add_columns'		=> array(
				FORUMS_TABLE			=> array(
					'display_subforum_list'		=> array('BOOL', 1),
				),
				SESSIONS_TABLE			=> array(
					'session_forum_id'		=> array('UINT', 0),
				),
			),
			'drop_keys'		=> array(
				GROUPS_TABLE			=> array('group_legend'),
			),
			'add_index'		=> array(
				SESSIONS_TABLE			=> array(
					'session_forum_id'		=> array('session_forum_id'),
				),
				GROUPS_TABLE			=> array(
					'group_legend_name'		=> array('group_legend', 'group_name'),
				),
			),
		),
		// No changes from 3.0.1-RC1 to 3.0.1
		'3.0.1-RC1'		=> array(),
		// No changes from 3.0.1 to 3.0.2-RC1
		'3.0.1'			=> array(),
		// Changes from 3.0.2-RC1 to 3.0.2-RC2
		'3.0.2-RC1'		=> array(
			'change_columns'	=> array(
				DRAFTS_TABLE			=> array(
					'draft_subject'		=> array('STEXT_UNI', ''),
				),
				FORUMS_TABLE	=> array(
					'forum_last_post_subject' => array('STEXT_UNI', ''),
				),
				POSTS_TABLE		=> array(
					'post_subject'			=> array('STEXT_UNI', '', 'true_sort'),
				),
				PRIVMSGS_TABLE	=> array(
					'message_subject'		=> array('STEXT_UNI', ''),
				),
				TOPICS_TABLE	=> array(
					'topic_title'				=> array('STEXT_UNI', '', 'true_sort'),
					'topic_last_post_subject'	=> array('STEXT_UNI', ''),
				),
			),
			'drop_keys'		=> array(
				SESSIONS_TABLE			=> array('session_forum_id'),
			),
			'add_index'		=> array(
				SESSIONS_TABLE			=> array(
					'session_fid'		=> array('session_forum_id'),
				),
			),
		),
		// No changes from 3.0.2-RC2 to 3.0.2
		'3.0.2-RC2'		=> array(),

		// Changes from 3.0.2 to 3.0.3-RC1
		'3.0.2'			=> array(
			// Add the following columns
			'add_columns'		=> array(
				STYLES_TEMPLATE_TABLE			=> array(
					'template_inherits_id'		=> array('UINT:4', 0),
					'template_inherit_path'		=> array('VCHAR', ''),
				),
				GROUPS_TABLE					=> array(
					'group_max_recipients'		=> array('UINT', 0),
				),
			),
		),

		// No changes from 3.0.3-RC1 to 3.0.3
		'3.0.3-RC1'		=> array(),

		// Changes from 3.0.3 to 3.0.4-RC1
		'3.0.3'			=> array(
			'add_columns'		=> array(
				PROFILE_FIELDS_TABLE			=> array(
					'field_show_profile'		=> array('BOOL', 0),
				),
			),
			'change_columns'	=> array(
				STYLES_TABLE				=> array(
					'style_id'				=> array('UINT', NULL, 'auto_increment'),
					'template_id'			=> array('UINT', 0),
					'theme_id'				=> array('UINT', 0),
					'imageset_id'			=> array('UINT', 0),
				),
				STYLES_IMAGESET_TABLE		=> array(
					'imageset_id'				=> array('UINT', NULL, 'auto_increment'),
				),
				STYLES_IMAGESET_DATA_TABLE	=> array(
					'image_id'				=> array('UINT', NULL, 'auto_increment'),
					'imageset_id'			=> array('UINT', 0),
				),
				STYLES_THEME_TABLE			=> array(
					'theme_id'				=> array('UINT', NULL, 'auto_increment'),
				),
				STYLES_TEMPLATE_TABLE		=> array(
					'template_id'			=> array('UINT', NULL, 'auto_increment'),
				),
				STYLES_TEMPLATE_DATA_TABLE	=> array(
					'template_id'			=> array('UINT', 0),
				),
				FORUMS_TABLE				=> array(
					'forum_style'			=> array('UINT', 0),
				),
				USERS_TABLE					=> array(
					'user_style'			=> array('UINT', 0),
				),
			),
		),

		// Changes from 3.0.4-RC1 to 3.0.4
		'3.0.4-RC1'		=> array(),

		// Changes from 3.0.4 to 3.0.5-RC1
		'3.0.4'			=> array(
			'change_columns'	=> array(
				FORUMS_TABLE				=> array(
					'forum_style'			=> array('UINT', 0),
				),
			),
		),

		// No changes from 3.0.5-RC1 to 3.0.5
		'3.0.5-RC1'		=> array(),

		// Changes from 3.0.5 to 3.0.6-RC1
		'3.0.5'		=> array(
			'add_columns'		=> array(
				CONFIRM_TABLE			=> array(
					'attempts'		=> array('UINT', 0),
				),
				USERS_TABLE			=> array(
					'user_new'			=> array('BOOL', 1),
					'user_reminded'		=> array('TINT:4', 0),
					'user_reminded_time'=> array('TIMESTAMP', 0),
				),
				GROUPS_TABLE			=> array(
					'group_skip_auth'		=> array('BOOL', 0, 'after' => 'group_founder_manage'),
				),
				PRIVMSGS_TABLE		=> array(
					'message_reported'	=> array('BOOL', 0),
				),
				REPORTS_TABLE		=> array(
					'pm_id'				=> array('UINT', 0),
				),
				PROFILE_FIELDS_TABLE			=> array(
					'field_show_on_vt'		=> array('BOOL', 0),
				),
				FORUMS_TABLE		=> array(
					'forum_options'			=> array('UINT:20', 0),
				),
			),
			'change_columns'		=> array(
				USERS_TABLE				=> array(
					'user_options'		=> array('UINT:11', 230271),
				),
			),
			'add_index'		=> array(
				REPORTS_TABLE		=> array(
					'post_id'		=> array('post_id'),
					'pm_id'			=> array('pm_id'),
				),
				POSTS_TABLE			=> array(
					'post_username'		=> array('post_username'),
				),
			),
		),

		// No changes from 3.0.6-RC1 to 3.0.6-RC2
		'3.0.6-RC1'		=> array(),
		// No changes from 3.0.6-RC2 to 3.0.6-RC3
		'3.0.6-RC2'		=> array(),
		// No changes from 3.0.6-RC3 to 3.0.6-RC4
		'3.0.6-RC3'		=> array(),
		// No changes from 3.0.6-RC4 to 3.0.6
		'3.0.6-RC4'		=> array(),

		// Changes from 3.0.6 to 3.0.7-RC1
		'3.0.6'		=> array(
			'drop_keys'		=> array(
				LOG_TABLE			=> array('log_time'),
			),
			'add_index'		=> array(
				TOPICS_TRACK_TABLE	=> array(
					'topic_id'		=> array('topic_id'),
				),
			),
		),

		// No changes from 3.0.7-RC1 to 3.0.7-RC2
		'3.0.7-RC1'		=> array(),
		// No changes from 3.0.7-RC2 to 3.0.7
		'3.0.7-RC2'		=> array(),
		// No changes from 3.0.7 to 3.0.7-PL1
		'3.0.7'		=> array(),
	);
}

/****************************************************************************
* ADD YOUR DATABASE DATA CHANGES HERE										*
* REMEMBER: You NEED to enter a schema array above and a data array here,	*
* even if both or one of them are empty.									*
*****************************************************************************/
function change_database_data(&$no_updates, $version)
{
	global $db, $errored, $error_ary, $config, $phpbb_root_path, $phpEx;

	switch ($version)
	{
		case '3.0.0':

			$sql = 'UPDATE ' . TOPICS_TABLE . "
				SET topic_last_view_time = topic_last_post_time
				WHERE topic_last_view_time = 0";
			_sql($sql, $errored, $error_ary);

			// Update smiley sizes
			$smileys = array('icon_e_surprised.gif', 'icon_eek.gif', 'icon_cool.gif', 'icon_lol.gif', 'icon_mad.gif', 'icon_razz.gif', 'icon_redface.gif', 'icon_cry.gif', 'icon_evil.gif', 'icon_twisted.gif', 'icon_rolleyes.gif', 'icon_exclaim.gif', 'icon_question.gif', 'icon_idea.gif', 'icon_arrow.gif', 'icon_neutral.gif', 'icon_mrgreen.gif', 'icon_e_ugeek.gif');

			foreach ($smileys as $smiley)
			{
				if (file_exists($phpbb_root_path . 'images/smilies/' . $smiley))
				{
					list($width, $height) = getimagesize($phpbb_root_path . 'images/smilies/' . $smiley);

					$sql = 'UPDATE ' . SMILIES_TABLE . '
						SET smiley_width = ' . $width . ', smiley_height = ' . $height . "
						WHERE smiley_url = '" . $db->sql_escape($smiley) . "'";

					_sql($sql, $errored, $error_ary);
				}
			}

			$no_updates = false;
		break;

		// No changes from 3.0.1-RC1 to 3.0.1
		case '3.0.1-RC1':
		break;

		// changes from 3.0.1 to 3.0.2-RC1
		case '3.0.1':

			set_config('referer_validation', '1');
			set_config('check_attachment_content', '1');
			set_config('mime_triggers', 'body|head|html|img|plaintext|a href|pre|script|table|title');

			$no_updates = false;
		break;

		// No changes from 3.0.2-RC1 to 3.0.2-RC2
		case '3.0.2-RC1':
		break;

		// No changes from 3.0.2-RC2 to 3.0.2
		case '3.0.2-RC2':
		break;

		// Changes from 3.0.2 to 3.0.3-RC1
		case '3.0.2':
			set_config('enable_queue_trigger', '0');
			set_config('queue_trigger_posts', '3');

			set_config('pm_max_recipients', '0');

			// Set maximum number of recipients for the registered users, bots, guests group
			$sql = 'UPDATE ' . GROUPS_TABLE . ' SET group_max_recipients = 5
				WHERE ' . $db->sql_in_set('group_name', array('GUESTS', 'REGISTERED', 'REGISTERED_COPPA', 'BOTS'));
			_sql($sql, $errored, $error_ary);

			// Not prefilling yet
			set_config('dbms_version', '');

			// Add new permission u_masspm_group and duplicate settings from u_masspm
			include_once($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
			$auth_admin = new auth_admin();

			// Only add the new permission if it does not already exist
			if (empty($auth_admin->acl_options['id']['u_masspm_group']))
			{
				$auth_admin->acl_add_option(array('global' => array('u_masspm_group')));

				// Now the tricky part, filling the permission
				$old_id = $auth_admin->acl_options['id']['u_masspm'];
				$new_id = $auth_admin->acl_options['id']['u_masspm_group'];

				$tables = array(ACL_GROUPS_TABLE, ACL_ROLES_DATA_TABLE, ACL_USERS_TABLE);

				foreach ($tables as $table)
				{
					$sql = 'SELECT *
						FROM ' . $table . '
						WHERE auth_option_id = ' . $old_id;
					$result = _sql($sql, $errored, $error_ary);

					$sql_ary = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$row['auth_option_id'] = $new_id;
						$sql_ary[] = $row;
					}
					$db->sql_freeresult($result);

					if (sizeof($sql_ary))
					{
						$db->sql_multi_insert($table, $sql_ary);
					}
				}

				// Remove any old permission entries
				$auth_admin->acl_clear_prefetch();
			}

			/**
			* Do not resync post counts here. An admin may do this later from the ACP
			$start = 0;
			$step = ($config['num_posts']) ? (max((int) ($config['num_posts'] / 5), 20000)) : 20000;

			$sql = 'UPDATE ' . USERS_TABLE . ' SET user_posts = 0';
			_sql($sql, $errored, $error_ary);

			do
			{
				$sql = 'SELECT COUNT(post_id) AS num_posts, poster_id
					FROM ' . POSTS_TABLE . '
					WHERE post_id BETWEEN ' . ($start + 1) . ' AND ' . ($start + $step) . '
						AND post_postcount = 1 AND post_approved = 1
					GROUP BY poster_id';
				$result = _sql($sql, $errored, $error_ary);

				if ($row = $db->sql_fetchrow($result))
				{
					do
					{
						$sql = 'UPDATE ' . USERS_TABLE . " SET user_posts = user_posts + {$row['num_posts']} WHERE user_id = {$row['poster_id']}";
						_sql($sql, $errored, $error_ary);
					}
					while ($row = $db->sql_fetchrow($result));

					$start += $step;
				}
				else
				{
					$start = 0;
				}
				$db->sql_freeresult($result);
			}
			while ($start);
			*/

			$sql = 'UPDATE ' . MODULES_TABLE . '
				SET module_auth = \'acl_a_email && cfg_email_enable\'
				WHERE module_class = \'acp\'
					AND module_basename = \'email\'';
			_sql($sql, $errored, $error_ary);

			$no_updates = false;
		break;

		// Changes from 3.0.3-RC1 to 3.0.3
		case '3.0.3-RC1':
			if ($db->sql_layer == 'oracle')
			{
				// log_operation is CLOB - but we can change this later
				$sql = 'UPDATE ' . LOG_TABLE . "
					SET log_operation = 'LOG_DELETE_TOPIC'
					WHERE log_operation LIKE 'LOG_TOPIC_DELETED'";
				_sql($sql, $errored, $error_ary);
			}
			else
			{
				$sql = 'UPDATE ' . LOG_TABLE . "
					SET log_operation = 'LOG_DELETE_TOPIC'
					WHERE log_operation = 'LOG_TOPIC_DELETED'";
				_sql($sql, $errored, $error_ary);
			}

			$no_updates = false;
		break;

		// Changes from 3.0.3 to 3.0.4-RC1
		case '3.0.3':
			// Update the Custom Profile Fields based on previous settings to the new format
			$sql = 'SELECT field_id, field_required, field_show_on_reg, field_hide
					FROM ' . PROFILE_FIELDS_TABLE;
			$result = _sql($sql, $errored, $error_ary);

			while ($row = $db->sql_fetchrow($result))
			{
				$sql_ary = array(
					'field_required'	=> 0,
					'field_show_on_reg'	=> 0,
					'field_hide'		=> 0,
					'field_show_profile'=> 0,
				);

				if ($row['field_required'])
				{
					$sql_ary['field_required'] = $sql_ary['field_show_on_reg'] = $sql_ary['field_show_profile'] = 1;
				}
				else if ($row['field_show_on_reg'])
				{
					$sql_ary['field_show_on_reg'] = $sql_ary['field_show_profile'] = 1;
				}
				else if ($row['field_hide'])
				{
					// Only administrators and moderators can see this CPF, if the view is enabled, they can see it, otherwise just admins in the acp_users module
					$sql_ary['field_hide'] = 1;
				}
				else
				{
					// equivelant to "none", which is the "Display in user control panel" option
					$sql_ary['field_show_profile'] = 1;
				}

				_sql('UPDATE ' . PROFILE_FIELDS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE field_id = ' . $row['field_id'], $errored, $error_ary);
			}
			$no_updates = false;

		break;

		// Changes from 3.0.4-RC1 to 3.0.4
		case '3.0.4-RC1':
		break;

		// Changes from 3.0.4 to 3.0.5-RC1
		case '3.0.4':

			// Captcha config variables
			set_config('captcha_gd_wave', 0);
			set_config('captcha_gd_3d_noise', 1);
			set_config('captcha_gd_fonts', 1);
			set_config('confirm_refresh', 1);

			// Maximum number of keywords
			set_config('max_num_search_keywords', 10);

			// Remove static config var and put it back as dynamic variable
			$sql = 'UPDATE ' . CONFIG_TABLE . "
				SET is_dynamic = 1
				WHERE config_name = 'search_indexing_state'";
			_sql($sql, $errored, $error_ary);

			// Hash old MD5 passwords
			$sql = 'SELECT user_id, user_password
					FROM ' . USERS_TABLE . '
					WHERE user_pass_convert = 1';
			$result = _sql($sql, $errored, $error_ary);

			while ($row = $db->sql_fetchrow($result))
			{
				if (strlen($row['user_password']) == 32)
				{
					$sql_ary = array(
						'user_password'	=> phpbb_hash($row['user_password']),
					);

					_sql('UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE user_id = ' . $row['user_id'], $errored, $error_ary);
				}
			}
			$db->sql_freeresult($result);

			// Adjust bot entry
			$sql = 'UPDATE ' . BOTS_TABLE . "
				SET bot_agent = 'ichiro/'
				WHERE bot_agent = 'ichiro/2'";
			_sql($sql, $errored, $error_ary);


			// Before we are able to add a unique key to auth_option, we need to remove duplicate entries

			// We get duplicate entries first
			$sql = 'SELECT auth_option
				FROM ' . ACL_OPTIONS_TABLE . '
				GROUP BY auth_option
				HAVING COUNT(*) >= 2';
			$result = $db->sql_query($sql);

			$auth_options = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$auth_options[] = $row['auth_option'];
			}
			$db->sql_freeresult($result);

			// Remove specific auth options
			if (!empty($auth_options))
			{
				foreach ($auth_options as $option)
				{
					// Select auth_option_ids... the largest id will be preserved
					$sql = 'SELECT auth_option_id
						FROM ' . ACL_OPTIONS_TABLE . "
						WHERE auth_option = '" . $db->sql_escape($option) . "'
						ORDER BY auth_option_id DESC";
					// sql_query_limit not possible here, due to bug in postgresql layer
					$result = $db->sql_query($sql);

					// Skip first row, this is our original auth option we want to preserve
					$row = $db->sql_fetchrow($result);

					while ($row = $db->sql_fetchrow($result))
					{
						// Ok, remove this auth option...
						_sql('DELETE FROM ' . ACL_OPTIONS_TABLE . ' WHERE auth_option_id = ' . $row['auth_option_id'], $errored, $error_ary);
						_sql('DELETE FROM ' . ACL_ROLES_DATA_TABLE . ' WHERE auth_option_id = ' . $row['auth_option_id'], $errored, $error_ary);
						_sql('DELETE FROM ' . ACL_GROUPS_TABLE . ' WHERE auth_option_id = ' . $row['auth_option_id'], $errored, $error_ary);
						_sql('DELETE FROM ' . ACL_USERS_TABLE . ' WHERE auth_option_id = ' . $row['auth_option_id'], $errored, $error_ary);
					}
					$db->sql_freeresult($result);
				}
			}

			// Now make auth_option UNIQUE, by dropping the old index and adding a UNIQUE one.
			$changes = array(
				'drop_keys'			=> array(
					ACL_OPTIONS_TABLE		=> array('auth_option'),
				),
			);

			global $db_tools;

			$statements = $db_tools->perform_schema_changes($changes);

			foreach ($statements as $sql)
			{
				_sql($sql, $errored, $error_ary);
			}

			$changes = array(
				'add_unique_index'	=> array(
					ACL_OPTIONS_TABLE		=> array(
						'auth_option'		=> array('auth_option'),
					),
				),
			);

			$statements = $db_tools->perform_schema_changes($changes);

			foreach ($statements as $sql)
			{
				_sql($sql, $errored, $error_ary);
			}

			$no_updates = false;

		break;

		// No changes from 3.0.5-RC1 to 3.0.5
		case '3.0.5-RC1':
		break;

		// Changes from 3.0.5 to 3.0.6-RC1
		case '3.0.5':
			// Let's see if the GD Captcha can be enabled... we simply look for what *is* enabled...
			if (!empty($config['captcha_gd']) && !isset($config['captcha_plugin']))
			{
				set_config('captcha_plugin', 'phpbb_captcha_gd');
			}
			else if (!isset($config['captcha_plugin']))
			{
				set_config('captcha_plugin', 'phpbb_captcha_nogd');
			}

			// Entries for the Feed Feature
			set_config('feed_enable', '0');
			set_config('feed_limit', '10');

			set_config('feed_overall_forums', '1');
			set_config('feed_overall_forums_limit', '15');

			set_config('feed_overall_topics', '0');
			set_config('feed_overall_topics_limit', '15');

			set_config('feed_forum', '1');
			set_config('feed_topic', '1');
			set_config('feed_item_statistics', '1');

			// Entries for smiley pagination
			set_config('smilies_per_page', '50');

			// Entry for reporting PMs
			set_config('allow_pm_report', '1');

			// Install modules
			$modules_to_install = array(
				'feed'					=> array(
					'base'		=> 'board',
					'class'		=> 'acp',
					'title'		=> 'ACP_FEED_SETTINGS',
					'auth'		=> 'acl_a_board',
					'cat'		=> 'ACP_BOARD_CONFIGURATION',
					'after'		=> array('signature', 'ACP_SIGNATURE_SETTINGS')
				),
				'warnings'				=> array(
					'base'		=> 'users',
					'class'		=> 'acp',
					'title'		=> 'ACP_USER_WARNINGS',
					'auth'		=> 'acl_a_user',
					'display'	=> 0,
					'cat'		=> 'ACP_CAT_USERS',
					'after'		=> array('feedback', 'ACP_USER_FEEDBACK')
				),
				'send_statistics'		=> array(
					'base'		=> 'send_statistics',
					'class'		=> 'acp',
					'title'		=> 'ACP_SEND_STATISTICS',
					'auth'		=> 'acl_a_server',
					'cat'		=> 'ACP_SERVER_CONFIGURATION'
				),
				'setting_forum_copy'	=> array(
					'base'		=> 'permissions',
					'class'		=> 'acp',
					'title'		=> 'ACP_FORUM_PERMISSIONS_COPY',
					'auth'		=> 'acl_a_fauth && acl_a_authusers && acl_a_authgroups && acl_a_mauth',
					'cat'		=> 'ACP_FORUM_BASED_PERMISSIONS',
					'after'		=> array('setting_forum_local', 'ACP_FORUM_PERMISSIONS')
				),
				'pm_reports'			=> array(
					'base'		=> 'pm_reports',
					'class'		=> 'mcp',
					'title'		=> 'MCP_PM_REPORTS_OPEN',
					'auth'		=> 'aclf_m_report',
					'cat'		=> 'MCP_REPORTS'
				),
				'pm_reports_closed'		=> array(
					'base'		=> 'pm_reports',
					'class'		=> 'mcp',
					'title'		=> 'MCP_PM_REPORTS_CLOSED',
					'auth'		=> 'aclf_m_report',
					'cat'		=> 'MCP_REPORTS'
				),
				'pm_report_details'		=> array(
					'base'		=> 'pm_reports',
					'class'		=> 'mcp',
					'title'		=> 'MCP_PM_REPORT_DETAILS',
					'auth'		=> 'aclf_m_report',
					'cat'		=> 'MCP_REPORTS'
				),
			);

			_add_modules($modules_to_install);

			// Add newly_registered group... but check if it already exists (we always supported running the updater on any schema)
			$sql = 'SELECT group_id
				FROM ' . GROUPS_TABLE . "
				WHERE group_name = 'NEWLY_REGISTERED'";
			$result = $db->sql_query($sql);
			$group_id = (int) $db->sql_fetchfield('group_id');
			$db->sql_freeresult($result);

			if (!$group_id)
			{
				$sql = 'INSERT INTO ' .  GROUPS_TABLE . " (group_name, group_type, group_founder_manage, group_colour, group_legend, group_avatar, group_desc, group_desc_uid, group_max_recipients) VALUES ('NEWLY_REGISTERED', 3, 0, '', 0, '', '', '', 5)";
				_sql($sql, $errored, $error_ary);

				$group_id = $db->sql_nextid();
			}

			// Insert new user role... at the end of the chain
			$sql = 'SELECT role_id
				FROM ' . ACL_ROLES_TABLE . "
				WHERE role_name = 'ROLE_USER_NEW_MEMBER'
					AND role_type = 'u_'";
			$result = $db->sql_query($sql);
			$u_role = (int) $db->sql_fetchfield('role_id');
			$db->sql_freeresult($result);

			if (!$u_role)
			{
				$sql = 'SELECT MAX(role_order) as max_order_id
					FROM ' . ACL_ROLES_TABLE . "
					WHERE role_type = 'u_'";
				$result = $db->sql_query($sql);
				$next_order_id = (int) $db->sql_fetchfield('max_order_id');
				$db->sql_freeresult($result);

				$next_order_id++;

				$sql = 'INSERT INTO ' . ACL_ROLES_TABLE . " (role_name, role_description, role_type, role_order) VALUES ('ROLE_USER_NEW_MEMBER', 'ROLE_DESCRIPTION_USER_NEW_MEMBER', 'u_', $next_order_id)";
				_sql($sql, $errored, $error_ary);
				$u_role = $db->sql_nextid();

				if (!$errored)
				{
					// Now add the correct data to the roles...
					// The standard role says that new users are not able to send a PM, Mass PM, are not able to PM groups
					$sql = 'INSERT INTO ' . ACL_ROLES_DATA_TABLE . " (role_id, auth_option_id, auth_setting) SELECT $u_role, auth_option_id, 0 FROM " . ACL_OPTIONS_TABLE . " WHERE auth_option LIKE 'u_%' AND auth_option IN ('u_sendpm', 'u_masspm', 'u_masspm_group')";
					_sql($sql, $errored, $error_ary);

					// Add user role to group
					$sql = 'INSERT INTO ' . ACL_GROUPS_TABLE . " (group_id, forum_id, auth_option_id, auth_role_id, auth_setting) VALUES ($group_id, 0, 0, $u_role, 0)";
					_sql($sql, $errored, $error_ary);
				}
			}

			// Insert new forum role
			$sql = 'SELECT role_id
				FROM ' . ACL_ROLES_TABLE . "
				WHERE role_name = 'ROLE_FORUM_NEW_MEMBER'
					AND role_type = 'f_'";
			$result = $db->sql_query($sql);
			$f_role = (int) $db->sql_fetchfield('role_id');
			$db->sql_freeresult($result);

			if (!$f_role)
			{
				$sql = 'SELECT MAX(role_order) as max_order_id
					FROM ' . ACL_ROLES_TABLE . "
					WHERE role_type = 'f_'";
				$result = $db->sql_query($sql);
				$next_order_id = (int) $db->sql_fetchfield('max_order_id');
				$db->sql_freeresult($result);

				$next_order_id++;

				$sql = 'INSERT INTO ' . ACL_ROLES_TABLE . " (role_name, role_description, role_type, role_order) VALUES  ('ROLE_FORUM_NEW_MEMBER', 'ROLE_DESCRIPTION_FORUM_NEW_MEMBER', 'f_', $next_order_id)";
				_sql($sql, $errored, $error_ary);
				$f_role = $db->sql_nextid();

				if (!$errored)
				{
					$sql = 'INSERT INTO ' . ACL_ROLES_DATA_TABLE . " (role_id, auth_option_id, auth_setting) SELECT $f_role, auth_option_id, 0 FROM " . ACL_OPTIONS_TABLE . " WHERE auth_option LIKE 'f_%' AND auth_option IN ('f_noapprove')";
					_sql($sql, $errored, $error_ary);
				}
			}

			// Set every members user_new column to 0 (old users) only if there is no one yet (this makes sure we do not execute this more than once)
			$sql = 'SELECT 1
				FROM ' . USERS_TABLE . '
				WHERE user_new = 0';
			$result = $db->sql_query_limit($sql, 1);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$row)
			{
				$sql = 'UPDATE ' . USERS_TABLE . ' SET user_new = 0';
				_sql($sql, $errored, $error_ary);
			}

			// Newly registered users limit
			if (!isset($config['new_member_post_limit']))
			{
				set_config('new_member_post_limit', (!empty($config['enable_queue_trigger'])) ? $config['queue_trigger_posts'] : 0);
			}

			if (!isset($config['new_member_group_default']))
			{
				set_config('new_member_group_default', 0);
			}

			// To mimick the old "feature" we will assign the forum role to every forum, regardless of the setting (this makes sure there are no "this does not work!!!! YUO!!!" posts...
			// Check if the role is already assigned...
			$sql = 'SELECT forum_id
				FROM ' . ACL_GROUPS_TABLE . '
				WHERE group_id = ' . $group_id . '
					AND auth_role_id = ' . $f_role;
			$result = $db->sql_query($sql);
			$is_options = (int) $db->sql_fetchfield('forum_id');
			$db->sql_freeresult($result);

			// Not assigned at all... :/
			if (!$is_options)
			{
				// Get postable forums
				$sql = 'SELECT forum_id
					FROM ' . FORUMS_TABLE . '
					WHERE forum_type != ' . FORUM_LINK;
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					_sql('INSERT INTO ' . ACL_GROUPS_TABLE . ' (group_id, forum_id, auth_option_id, auth_role_id, auth_setting) VALUES (' . $group_id . ', ' . (int) $row['forum_id'] . ', 0, ' . $f_role . ', 0)', $errored, $error_ary);
				}
				$db->sql_freeresult($result);
			}

			// Clear permissions...
			include_once($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
			$auth_admin = new auth_admin();
			$auth_admin->acl_clear_prefetch();

			if (!isset($config['allow_avatar']))
			{
				if ($config['allow_avatar_upload'] || $config['allow_avatar_local'] || $config['allow_avatar_remote'])
				{
					set_config('allow_avatar', '1');
				}
				else
				{
					set_config('allow_avatar', '0');
				}
			}

			if (!isset($config['allow_avatar_remote_upload']))
			{
				if ($config['allow_avatar_remote'] && $config['allow_avatar_upload'])
				{
					set_config('allow_avatar_remote_upload', '1');
				}
				else
				{
					set_config('allow_avatar_remote_upload', '0');
				}
			}

			// Minimum number of characters
			if (!isset($config['min_post_chars']))
			{
				set_config('min_post_chars', '1');
			}

			if (!isset($config['allow_quick_reply']))
			{
				set_config('allow_quick_reply', '1');
			}

			// Set every members user_options column to enable
			// bbcode, smilies and URLs for signatures by default
			$sql = 'SELECT user_options
				FROM ' . USERS_TABLE . '
				WHERE user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')';
			$result = $db->sql_query_limit($sql, 1);
			$user_option = (int) $db->sql_fetchfield('user_options');
			$db->sql_freeresult($result);

			// Check if we already updated the database by checking bit 15 which we used to store the sig_bbcode option
			if (!($user_option & 1 << 15))
			{
				// 229376 is the added value to enable all three signature options
				$sql = 'UPDATE ' . USERS_TABLE . ' SET user_options = user_options + 229376';
				_sql($sql, $errored, $error_ary);
			}

			if (!isset($config['delete_time']))
			{
				set_config('delete_time', $config['edit_time']);
			}

			$no_updates = false;
		break;

		// No changes from 3.0.6-RC1 to 3.0.6-RC2
		case '3.0.6-RC1':
		break;

		// Changes from 3.0.6-RC2 to 3.0.6-RC3
		case '3.0.6-RC2':

			// Update the Custom Profile Fields based on previous settings to the new format
			$sql = 'UPDATE ' . PROFILE_FIELDS_TABLE . '
				SET field_show_on_vt = 1
				WHERE field_hide = 0
					AND (field_required = 1 OR field_show_on_reg = 1 OR field_show_profile = 1)';
			_sql($sql, $errored, $error_ary);
			$no_updates = false;

		break;

		// No changes from 3.0.6-RC3 to 3.0.6-RC4
		case '3.0.6-RC3':
		break;

		// No changes from 3.0.6-RC4 to 3.0.6
		case '3.0.6-RC4':
		break;

		// Changes from 3.0.6 to 3.0.7-RC1
		case '3.0.6':

			// ATOM Feeds
			set_config('feed_overall', '1');
			set_config('feed_http_auth', '0');
			set_config('feed_limit_post', (string) (isset($config['feed_limit']) ? (int) $config['feed_limit'] : 15));
			set_config('feed_limit_topic', (string) (isset($config['feed_overall_topics_limit']) ? (int) $config['feed_overall_topics_limit'] : 10));
			set_config('feed_topics_new', (!empty($config['feed_overall_topics']) ? '1' : '0'));
			set_config('feed_topics_active', (!empty($config['feed_overall_topics']) ? '1' : '0'));

			// Delete all text-templates from the template_data
			$sql = 'DELETE FROM ' . STYLES_TEMPLATE_DATA_TABLE . '
				WHERE template_filename ' . $db->sql_like_expression($db->any_char . '.txt');
			_sql($sql, $errored, $error_ary);

			$no_updates = false;
		break;

		// Changes from 3.0.7-RC1 to 3.0.7-RC2
		case '3.0.7-RC1':

			$sql = 'SELECT user_id, user_email, user_email_hash
				FROM ' . USERS_TABLE . '
				WHERE user_type <> ' . USER_IGNORE . "
					AND user_email <> ''";
			$result = $db->sql_query($sql);

			$i = 0;
			while ($row = $db->sql_fetchrow($result))
			{
				// Snapshot of the phpbb_email_hash() function
				// We cannot call it directly because the auto updater updates the DB first. :/
				$user_email_hash = sprintf('%u', crc32(strtolower($row['user_email']))) . strlen($row['user_email']);

				if ($user_email_hash != $row['user_email_hash'])
				{
					$sql_ary = array(
						'user_email_hash'	=> $user_email_hash,
					);

					$sql = 'UPDATE ' . USERS_TABLE . '
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE user_id = ' . (int) $row['user_id'];
					_sql($sql, $errored, $error_ary, ($i % 100 == 0));

					++$i;
				}
			}
			$db->sql_freeresult($result);

			$no_updates = false;

		break;

		// No changes from 3.0.7-RC2 to 3.0.7
		case '3.0.7-RC2':
		break;

		// No changes from 3.0.7 to 3.0.7-PL1
		case '3.0.7':
		break;
	}
}


/**
* Database Tools for handling cross-db actions such as altering columns, etc.
* Currently not supported is returning SQL for creating tables.
*
* @package dbal
*/
class updater_db_tools
{
	/**
	* Current sql layer
	*/
	var $sql_layer = '';

	/**
	* @var object DB object
	*/
	var $db = NULL;

	/**
	* The Column types for every database we support
	* @var array
	*/
	var $dbms_type_map = array(
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
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
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
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
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
			'DECIMAL:'	=> 'DOUBLE PRECISION',
			'PDECIMAL'	=> 'DOUBLE PRECISION',
			'PDECIMAL:'	=> 'DOUBLE PRECISION',
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
			'DECIMAL:'	=> '[float]',
			'PDECIMAL'	=> '[float]',
			'PDECIMAL:'	=> '[float]',
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
			'DECIMAL:'	=> 'number(%d, 2)',
			'PDECIMAL'	=> 'number(6, 3)',
			'PDECIMAL:'	=> 'number(%d, 3)',
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
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
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
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
			'VCHAR_UNI'	=> 'varchar(255)',
			'VCHAR_UNI:'=> 'varchar(%d)',
			'VCHAR_CI'	=> 'varchar_ci',
			'VARBINARY'	=> 'bytea',
		),
	);

	/**
	* A list of types being unsigned for better reference in some db's
	* @var array
	*/
	var $unsigned_types = array('UINT', 'UINT:', 'USINT', 'BOOL', 'TIMESTAMP');

	/**
	* A list of supported DBMS. We change this class to support more DBMS, the DBMS itself only need to follow some rules.
	* @var array
	*/
	var $supported_dbms = array('firebird', 'mssql', 'mysql_40', 'mysql_41', 'oracle', 'postgres', 'sqlite');

	/**
	* This is set to true if user only wants to return the 'to-be-executed' SQL statement(s) (as an array).
	* This mode has no effect on some methods (inserting of data for example). This is expressed within the methods command.
	*/
	var $return_statements = false;

	/**
	* Constructor. Set DB Object and set {@link $return_statements return_statements}.
	*
	* @param phpbb_dbal	$db					DBAL object
	* @param bool		$return_statements	True if only statements should be returned and no SQL being executed
	*/
	function updater_db_tools(&$db, $return_statements = false)
	{
		$this->db = $db;
		$this->return_statements = $return_statements;

		// Determine mapping database type
		switch ($this->db->sql_layer)
		{
			case 'mysql':
				$this->sql_layer = 'mysql_40';
			break;

			case 'mysql4':
				if (version_compare($this->db->sql_server_info(true), '4.1.3', '>='))
				{
					$this->sql_layer = 'mysql_41';
				}
				else
				{
					$this->sql_layer = 'mysql_40';
				}
			break;

			case 'mysqli':
				$this->sql_layer = 'mysql_41';
			break;

			case 'mssql':
			case 'mssql_odbc':
				$this->sql_layer = 'mssql';
			break;

			default:
				$this->sql_layer = $this->db->sql_layer;
			break;
		}
	}

	/**
	* Handle passed database update array.
	* Expected structure...
	* Key being one of the following
	*	change_columns: Column changes (only type, not name)
	*	add_columns: Add columns to a table
	*	drop_keys: Dropping keys
	*	drop_columns: Removing/Dropping columns
	*	add_primary_keys: adding primary keys
	*	add_unique_index: adding an unique index
	*	add_index: adding an index
	*
	* The values are in this format:
	*		{TABLE NAME}		=> array(
	*			{COLUMN NAME}		=> array({COLUMN TYPE}, {DEFAULT VALUE}, {OPTIONAL VARIABLES}),
	*			{KEY/INDEX NAME}	=> array({COLUMN NAMES}),
	*		)
	*
	* For more information have a look at /develop/create_schema_files.php (only available through SVN)
	*/
	function perform_schema_changes($schema_changes)
	{
		if (empty($schema_changes))
		{
			return;
		}

		$statements = array();
		$sqlite = false;

		// For SQLite we need to perform the schema changes in a much more different way
		if ($this->db->sql_layer == 'sqlite' && $this->return_statements)
		{
			$sqlite_data = array();
			$sqlite = true;
		}

		// Change columns?
		if (!empty($schema_changes['change_columns']))
		{
			foreach ($schema_changes['change_columns'] as $table => $columns)
			{
				foreach ($columns as $column_name => $column_data)
				{
					// If the column exists we change it, else we add it ;)
					if ($column_exists = $this->sql_column_exists($table, $column_name))
					{
						$result = $this->sql_column_change($table, $column_name, $column_data, true);
					}
					else
					{
						$result = $this->sql_column_add($table, $column_name, $column_data, true);
					}

					if ($sqlite)
					{
						if ($column_exists)
						{
							$sqlite_data[$table]['change_columns'][] = $result;
						}
						else
						{
							$sqlite_data[$table]['add_columns'][] = $result;
						}
					}
					else if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
					}
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
					if ($column_exists = $this->sql_column_exists($table, $column_name))
					{
						continue;
						// This is commented out here because it can take tremendous time on updates
//						$result = $this->sql_column_change($table, $column_name, $column_data, true);
					}
					else
					{
						$result = $this->sql_column_add($table, $column_name, $column_data, true);
					}

					if ($sqlite)
					{
						if ($column_exists)
						{
							continue;
//							$sqlite_data[$table]['change_columns'][] = $result;
						}
						else
						{
							$sqlite_data[$table]['add_columns'][] = $result;
						}
					}
					else if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
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
					if (!$this->sql_index_exists($table, $index_name))
					{
						continue;
					}

					$result = $this->sql_index_drop($table, $index_name);

					if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
					}
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
					// Only remove the column if it exists...
					if ($this->sql_column_exists($table, $column))
					{
						$result = $this->sql_column_remove($table, $column, true);

						if ($sqlite)
						{
							$sqlite_data[$table]['drop_columns'][] = $result;
						}
						else if ($this->return_statements)
						{
							$statements = array_merge($statements, $result);
						}
					}
				}
			}
		}

		// Add primary keys?
		if (!empty($schema_changes['add_primary_keys']))
		{
			foreach ($schema_changes['add_primary_keys'] as $table => $columns)
			{
				$result = $this->sql_create_primary_key($table, $columns, true);

				if ($sqlite)
				{
					$sqlite_data[$table]['primary_key'] = $result;
				}
				else if ($this->return_statements)
				{
					$statements = array_merge($statements, $result);
				}
			}
		}

		// Add unqiue indexes?
		if (!empty($schema_changes['add_unique_index']))
		{
			foreach ($schema_changes['add_unique_index'] as $table => $index_array)
			{
				foreach ($index_array as $index_name => $column)
				{
					if ($this->sql_unique_index_exists($table, $index_name))
					{
						continue;
					}

					$result = $this->sql_create_unique_index($table, $index_name, $column);

					if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
					}
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
					if ($this->sql_index_exists($table, $index_name))
					{
						continue;
					}

					$result = $this->sql_create_index($table, $index_name, $column);

					if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
					}
				}
			}
		}

		if ($sqlite)
		{
			foreach ($sqlite_data as $table_name => $sql_schema_changes)
			{
				// Create temporary table with original data
				$statements[] = 'begin';

				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table_name}'
					ORDER BY type DESC, name;";
				$result = $this->db->sql_query($sql);

				if (!$result)
				{
					continue;
				}

				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				// Create a backup table and populate it, destroy the existing one
				$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']);
				$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
				$statements[] = 'DROP TABLE ' . $table_name;

				// Get the columns...
				preg_match('#\((.*)\)#s', $row['sql'], $matches);

				$plain_table_cols = trim($matches[1]);
				$new_table_cols = preg_split('/,(?![\s\w]+\))/m', $plain_table_cols);
				$column_list = array();

				foreach ($new_table_cols as $declaration)
				{
					$entities = preg_split('#\s+#', trim($declaration));
					if ($entities[0] == 'PRIMARY')
					{
						continue;
					}
					$column_list[] = $entities[0];
				}

				// note down the primary key notation because sqlite only supports adding it to the end for the new table
				$primary_key = false;
				$_new_cols = array();

				foreach ($new_table_cols as $key => $declaration)
				{
					$entities = preg_split('#\s+#', trim($declaration));
					if ($entities[0] == 'PRIMARY')
					{
						$primary_key = $declaration;
						continue;
					}
					$_new_cols[] = $declaration;
				}

				$new_table_cols = $_new_cols;

				// First of all... change columns
				if (!empty($sql_schema_changes['change_columns']))
				{
					foreach ($sql_schema_changes['change_columns'] as $column_sql)
					{
						foreach ($new_table_cols as $key => $declaration)
						{
							$entities = preg_split('#\s+#', trim($declaration));
							if (strpos($column_sql, $entities[0] . ' ') === 0)
							{
								$new_table_cols[$key] = $column_sql;
							}
						}
					}
				}

				if (!empty($sql_schema_changes['add_columns']))
				{
					foreach ($sql_schema_changes['add_columns'] as $column_sql)
					{
						$new_table_cols[] = $column_sql;
					}
				}

				// Now drop them...
				if (!empty($sql_schema_changes['drop_columns']))
				{
					foreach ($sql_schema_changes['drop_columns'] as $column_name)
					{
						// Remove from column list...
						$new_column_list = array();
						foreach ($column_list as $key => $value)
						{
							if ($value === $column_name)
							{
								continue;
							}

							$new_column_list[] = $value;
						}

						$column_list = $new_column_list;

						// Remove from table...
						$_new_cols = array();
						foreach ($new_table_cols as $key => $declaration)
						{
							$entities = preg_split('#\s+#', trim($declaration));
							if (strpos($column_name . ' ', $entities[0] . ' ') === 0)
							{
								continue;
							}
							$_new_cols[] = $declaration;
						}
						$new_table_cols = $_new_cols;
					}
				}

				// Primary key...
				if (!empty($sql_schema_changes['primary_key']))
				{
					$new_table_cols[] = 'PRIMARY KEY (' . implode(', ', $sql_schema_changes['primary_key']) . ')';
				}
				// Add a new one or the old primary key
				else if ($primary_key !== false)
				{
					$new_table_cols[] = $primary_key;
				}

				$columns = implode(',', $column_list);

				// create a new table and fill it up. destroy the temp one
				$statements[] = 'CREATE TABLE ' . $table_name . ' (' . implode(',', $new_table_cols) . ');';
				$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
				$statements[] = 'DROP TABLE ' . $table_name . '_temp';

				$statements[] = 'commit';
			}
		}

		if ($this->return_statements)
		{
			return $statements;
		}
	}

	/**
	* Check if a specified column exist
	*
	* @param string	$table			Table to check the column at
	* @param string	$column_name	The column to check
	*
	* @return bool True if column exists, else false
	*/
	function sql_column_exists($table, $column_name)
	{
		switch ($this->sql_layer)
		{
			case 'mysql_40':
			case 'mysql_41':

				$sql = "SHOW COLUMNS FROM $table";
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					// lower case just in case
					if (strtolower($row['Field']) == $column_name)
					{
						$this->db->sql_freeresult($result);
						return true;
					}
				}
				$this->db->sql_freeresult($result);
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
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					// lower case just in case
					if (strtolower($row['attname']) == $column_name)
					{
						$this->db->sql_freeresult($result);
						return true;
					}
				}
				$this->db->sql_freeresult($result);

				return false;
			break;

			// same deal with PostgreSQL, we must perform more complex operations than
			// we technically could
			case 'mssql':
				$sql = "SELECT c.name
					FROM syscolumns c
					LEFT JOIN sysobjects o ON c.id = o.id
					WHERE o.name = '{$table}'";
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					// lower case just in case
					if (strtolower($row['name']) == $column_name)
					{
						$this->db->sql_freeresult($result);
						return true;
					}
				}
				$this->db->sql_freeresult($result);
				return false;
			break;

			case 'oracle':
				$sql = "SELECT column_name
					FROM user_tab_columns
					WHERE LOWER(table_name) = '" . strtolower($table) . "'";
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					// lower case just in case
					if (strtolower($row['column_name']) == $column_name)
					{
						$this->db->sql_freeresult($result);
						return true;
					}
				}
				$this->db->sql_freeresult($result);
				return false;
			break;

			case 'firebird':
				$sql = "SELECT RDB\$FIELD_NAME as FNAME
					FROM RDB\$RELATION_FIELDS
					WHERE RDB\$RELATION_NAME = '" . strtoupper($table) . "'";
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					// lower case just in case
					if (strtolower($row['fname']) == $column_name)
					{
						$this->db->sql_freeresult($result);
						return true;
					}
				}
				$this->db->sql_freeresult($result);
				return false;
			break;

			// ugh, SQLite
			case 'sqlite':
				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table}'";
				$result = $this->db->sql_query($sql);

				if (!$result)
				{
					return false;
				}

				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

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
	* Check if a specified index exists in table. Does not return PRIMARY KEY and UNIQUE indexes.
	*
	* @param string	$table_name		Table to check the index at
	* @param string	$index_name		The index name to check
	*
	* @return bool True if index exists, else false
	*/
	function sql_index_exists($table_name, $index_name)
	{
		if ($this->sql_layer == 'mssql')
		{
			$sql = "EXEC sp_statistics '$table_name'";
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				if ($row['TYPE'] == 3)
				{
					if (strtolower($row['INDEX_NAME']) == strtolower($index_name))
					{
						$this->db->sql_freeresult($result);
						return true;
					}
				}
			}
			$this->db->sql_freeresult($result);

			return false;
		}

		switch ($this->sql_layer)
		{
			case 'firebird':
				$sql = "SELECT LOWER(RDB\$INDEX_NAME) as index_name
					FROM RDB\$INDICES
					WHERE RDB\$RELATION_NAME = '" . strtoupper($table_name) . "'
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
					WHERE table_name = '" . strtoupper($table_name) . "'
						AND generated = 'N'
						AND uniqueness = 'NONUNIQUE'";
				$col = 'index_name';
			break;

			case 'sqlite':
				$sql = "PRAGMA index_list('" . $table_name . "');";
				$col = 'name';
			break;
		}

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (($this->sql_layer == 'mysql_40' || $this->sql_layer == 'mysql_41') && !$row['Non_unique'])
			{
				continue;
			}

			// These DBMS prefix index name with the table name
			switch ($this->sql_layer)
			{
				case 'firebird':
				case 'oracle':
				case 'postgres':
				case 'sqlite':
					$row[$col] = substr($row[$col], strlen($table_name) + 1);
				break;
			}

			if (strtolower($row[$col]) == strtolower($index_name))
			{
				$this->db->sql_freeresult($result);
				return true;
			}
		}
		$this->db->sql_freeresult($result);

		return false;
	}

	/**
	* Check if a specified UNIQUE index exists in table.
	*
	* @param string	$table_name		Table to check the index at
	* @param string	$index_name		The index name to check
	*
	* @return bool True if index exists, else false
	*/
	function sql_unique_index_exists($table_name, $index_name)
	{
		if ($this->sql_layer == 'mssql')
		{
			$sql = "EXEC sp_statistics '$table_name'";
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				// Usually NON_UNIQUE is the column we want to check, but we allow for both
				if ($row['TYPE'] == 3)
				{
					if (strtolower($row['INDEX_NAME']) == strtolower($index_name))
					{
						$this->db->sql_freeresult($result);
						return true;
					}
				}
			}
			$this->db->sql_freeresult($result);
			return false;
		}

		switch ($this->sql_layer)
		{
			case 'firebird':
				$sql = "SELECT LOWER(RDB\$INDEX_NAME) as index_name
					FROM RDB\$INDICES
					WHERE RDB\$RELATION_NAME = '" . strtoupper($table_name) . "'
						AND RDB\$UNIQUE_FLAG IS NOT NULL
						AND RDB\$FOREIGN_KEY IS NULL";
				$col = 'index_name';
			break;

			case 'postgres':
				$sql = "SELECT ic.relname as index_name, i.indisunique
					FROM pg_class bc, pg_class ic, pg_index i
					WHERE (bc.oid = i.indrelid)
						AND (ic.oid = i.indexrelid)
						AND (bc.relname = '" . $table_name . "')
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
				$sql = "SELECT index_name, table_owner
					FROM user_indexes
					WHERE table_name = '" . strtoupper($table_name) . "'
						AND generated = 'N'
						AND uniqueness = 'UNIQUE'";
				$col = 'index_name';
			break;

			case 'sqlite':
				$sql = "PRAGMA index_list('" . $table_name . "');";
				$col = 'name';
			break;
		}

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (($this->sql_layer == 'mysql_40' || $this->sql_layer == 'mysql_41') && ($row['Non_unique'] || $row[$col] == 'PRIMARY'))
			{
				continue;
			}

			if ($this->sql_layer == 'sqlite' && !$row['unique'])
			{
				continue;
			}

			if ($this->sql_layer == 'postgres' && $row['indisunique'] != 't')
			{
				continue;
			}

			// These DBMS prefix index name with the table name
			switch ($this->sql_layer)
			{
				case 'oracle':
					// Two cases here... prefixed with U_[table_owner] and not prefixed with table_name
					if (strpos($row[$col], 'U_') === 0)
					{
						$row[$col] = substr($row[$col], strlen('U_' . $row['table_owner']) + 1);
					}
					else if (strpos($row[$col], strtoupper($table_name)) === 0)
					{
						$row[$col] = substr($row[$col], strlen($table_name) + 1);
					}
				break;

				case 'firebird':
				case 'postgres':
				case 'sqlite':
					$row[$col] = substr($row[$col], strlen($table_name) + 1);
				break;
			}

			if (strtolower($row[$col]) == strtolower($index_name))
			{
				$this->db->sql_freeresult($result);
				return true;
			}
		}
		$this->db->sql_freeresult($result);

		return false;
	}

	/**
	* Private method for performing sql statements (either execute them or return them)
	* @access private
	*/
	function _sql_run_sql($statements)
	{
		if ($this->return_statements)
		{
			return $statements;
		}

		// We could add error handling here...
		foreach ($statements as $sql)
		{
			if ($sql === 'begin')
			{
				$this->db->sql_transaction('begin');
			}
			else if ($sql === 'commit')
			{
				$this->db->sql_transaction('commit');
			}
			else
			{
				$this->db->sql_query($sql);
			}
		}

		return true;
	}

	/**
	* Function to prepare some column information for better usage
	* @access private
	*/
	function sql_prepare_column_data($table_name, $column_name, $column_data)
	{
		// Get type
		if (strpos($column_data[0], ':') !== false)
		{
			list($orig_column_type, $column_length) = explode(':', $column_data[0]);
			if (!is_array($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']))
			{
				$column_type = sprintf($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':'], $column_length);
			}
			else
			{
				if (isset($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['rule']))
				{
					switch ($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['rule'][0])
					{
						case 'div':
							$column_length /= $this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['rule'][1];
							$column_length = ceil($column_length);
							$column_type = sprintf($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':'][0], $column_length);
						break;
					}
				}

				if (isset($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['limit']))
				{
					switch ($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['limit'][0])
					{
						case 'mult':
							$column_length *= $this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['limit'][1];
							if ($column_length > $this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['limit'][2])
							{
								$column_type = $this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['limit'][3];
							}
							else
							{
								$column_type = sprintf($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':'][0], $column_length);
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
			$column_type = $this->dbms_type_map[$this->sql_layer][$column_data[0]];
		}

		// Adjust default value if db-dependant specified
		if (is_array($column_data[1]))
		{
			$column_data[1] = (isset($column_data[1][$this->sql_layer])) ? $column_data[1][$this->sql_layer] : $column_data[1]['default'];
		}

		$sql = '';

		$return_array = array();

		switch ($this->sql_layer)
		{
			case 'firebird':
				$sql .= " {$column_type} ";
				$return_array['column_type_sql_type'] = " {$column_type} ";

				if (!is_null($column_data[1]))
				{
					$sql .= 'DEFAULT ' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ' ';
					$return_array['column_type_sql_default'] = ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ' ';
				}

				$sql .= 'NOT NULL';

				// This is a UNICODE column and thus should be given it's fair share
				if (preg_match('/^X?STEXT_UNI|VCHAR_(CI|UNI:?)/', $column_data[0]))
				{
					$sql .= ' COLLATE UNICODE';
				}

				$return_array['auto_increment'] = false;
				if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
				{
					$return_array['auto_increment'] = true;
				}

			break;

			case 'mssql':
				$sql .= " {$column_type} ";
				$sql_default = " {$column_type} ";

				// For adding columns we need the default definition
				if (!is_null($column_data[1]))
				{
					// For hexadecimal values do not use single quotes
					if (strpos($column_data[1], '0x') === 0)
					{
						$return_array['default'] = 'DEFAULT (' . $column_data[1] . ') ';
						$sql_default .= $return_array['default'];
					}
					else
					{
						$return_array['default'] = 'DEFAULT (' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ') ';
						$sql_default .= $return_array['default'];
					}
				}

				if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
				{
//					$sql .= 'IDENTITY (1, 1) ';
					$sql_default .= 'IDENTITY (1, 1) ';
				}

				$return_array['textimage'] = $column_type === '[text]';

				$sql .= 'NOT NULL';
				$sql_default .= 'NOT NULL';

				$return_array['column_type_sql_default'] = $sql_default;

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
					else if ($this->sql_layer === 'mysql_41' && $column_data[2] == 'true_sort')
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
				// Oracle does not like setting NOT NULL on a column that is already NOT NULL (this happens only on number fields)
				if (!preg_match('/number/i', $column_type))
				{
					$sql .= ($column_data[1] === '') ? '' : 'NOT NULL';
				}

				$return_array['auto_increment'] = false;
				if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
				{
					$return_array['auto_increment'] = true;
				}

			break;

			case 'postgres':
				$return_array['column_type'] = $column_type;

				$sql .= " {$column_type} ";

				$return_array['auto_increment'] = false;
				if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
				{
					$default_val = "nextval('{$table_name}_seq')";
					$return_array['auto_increment'] = true;
				}
				else if (!is_null($column_data[1]))
				{
					$default_val = "'" . $column_data[1] . "'";
					$return_array['null'] = 'NOT NULL';
					$sql .= 'NOT NULL ';
				}

				$return_array['default'] = $default_val;

				$sql .= "DEFAULT {$default_val}";

				// Unsigned? Then add a CHECK contraint
				if (in_array($orig_column_type, $this->unsigned_types))
				{
					$return_array['constraint'] = "CHECK ({$column_name} >= 0)";
					$sql .= " CHECK ({$column_name} >= 0)";
				}

			break;

			case 'sqlite':
				$return_array['primary_key_set'] = false;
				if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
				{
					$sql .= ' INTEGER PRIMARY KEY';
					$return_array['primary_key_set'] = true;
				}
				else
				{
					$sql .= ' ' . $column_type;
				}

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
	function sql_column_add($table_name, $column_name, $column_data, $inline = false)
	{
		$column_data = $this->sql_prepare_column_data($table_name, $column_name, $column_data);
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'firebird':
				// Does not support AFTER statement, only POSITION (and there you need the column position)
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD "' . strtoupper($column_name) . '" ' . $column_data['column_type_sql'];
			break;

			case 'mssql':
				// Does not support AFTER, only through temporary table
				$statements[] = 'ALTER TABLE [' . $table_name . '] ADD [' . $column_name . '] ' . $column_data['column_type_sql_default'];
			break;

			case 'mysql_40':
			case 'mysql_41':
				$after = (!empty($column_data['after'])) ? ' AFTER ' . $column_data['after'] : '';
				$statements[] = 'ALTER TABLE `' . $table_name . '` ADD COLUMN `' . $column_name . '` ' . $column_data['column_type_sql'] . $after;
			break;

			case 'oracle':
				// Does not support AFTER, only through temporary table
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD ' . $column_name . ' ' . $column_data['column_type_sql'];
			break;

			case 'postgres':
				// Does not support AFTER, only through temporary table

				if (version_compare($this->db->sql_server_info(true), '8.0', '>='))
				{
					$statements[] = 'ALTER TABLE ' . $table_name . ' ADD COLUMN "' . $column_name . '" ' . $column_data['column_type_sql'];
				}
				else
				{
					// old versions cannot add columns with default and null information
					$statements[] = 'ALTER TABLE ' . $table_name . ' ADD COLUMN "' . $column_name . '" ' . $column_data['column_type'] . ' ' . $column_data['constraint'];

					if (isset($column_data['null']))
					{
						if ($column_data['null'] == 'NOT NULL')
						{
							$statements[] = 'ALTER TABLE ' . $table_name . ' ALTER COLUMN ' . $column_name . ' SET NOT NULL';
						}
					}

					if (isset($column_data['default']))
					{
						$statements[] = 'ALTER TABLE ' . $table_name . ' ALTER COLUMN ' . $column_name . ' SET DEFAULT ' . $column_data['default'];
					}
				}
			break;

			case 'sqlite':

				if ($inline && $this->return_statements)
				{
					return $column_name . ' ' . $column_data['column_type_sql'];
				}

				if (version_compare(sqlite_libversion(), '3.0') == -1)
				{
					$sql = "SELECT sql
						FROM sqlite_master
						WHERE type = 'table'
							AND name = '{$table_name}'
						ORDER BY type DESC, name;";
					$result = $this->db->sql_query($sql);

					if (!$result)
					{
						break;
					}

					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					$statements[] = 'begin';

					// Create a backup table and populate it, destroy the existing one
					$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']);
					$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
					$statements[] = 'DROP TABLE ' . $table_name;

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
					$statements[] = 'CREATE TABLE ' . $table_name . ' (' . $new_table_cols . ');';
					$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
					$statements[] = 'DROP TABLE ' . $table_name . '_temp';

					$statements[] = 'commit';
				}
				else
				{
					$statements[] = 'ALTER TABLE ' . $table_name . ' ADD ' . $column_name . ' [' . $column_data['column_type_sql'] . ']';
				}
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Drop column
	*/
	function sql_column_remove($table_name, $column_name, $inline = false)
	{
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'firebird':
				$statements[] = 'ALTER TABLE ' . $table_name . ' DROP "' . strtoupper($column_name) . '"';
			break;

			case 'mssql':
				$statements[] = 'ALTER TABLE [' . $table_name . '] DROP COLUMN [' . $column_name . ']';
			break;

			case 'mysql_40':
			case 'mysql_41':
				$statements[] = 'ALTER TABLE `' . $table_name . '` DROP COLUMN `' . $column_name . '`';
			break;

			case 'oracle':
				$statements[] = 'ALTER TABLE ' . $table_name . ' DROP ' . $column_name;
			break;

			case 'postgres':
				$statements[] = 'ALTER TABLE ' . $table_name . ' DROP COLUMN "' . $column_name . '"';
			break;

			case 'sqlite':

				if ($inline && $this->return_statements)
				{
					return $column_name;
				}

				if (version_compare(sqlite_libversion(), '3.0') == -1)
				{
					$sql = "SELECT sql
						FROM sqlite_master
						WHERE type = 'table'
							AND name = '{$table_name}'
						ORDER BY type DESC, name;";
					$result = $this->db->sql_query($sql);

					if (!$result)
					{
						break;
					}

					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					$statements[] = 'begin';

					// Create a backup table and populate it, destroy the existing one
					$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']);
					$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
					$statements[] = 'DROP TABLE ' . $table_name;

					preg_match('#\((.*)\)#s', $row['sql'], $matches);

					$new_table_cols = trim($matches[1]);
					$old_table_cols = preg_split('/,(?![\s\w]+\))/m', $new_table_cols);
					$column_list = array();

					foreach ($old_table_cols as $declaration)
					{
						$entities = preg_split('#\s+#', trim($declaration));
						if ($entities[0] == 'PRIMARY' || $entities[0] === $column_name)
						{
							continue;
						}
						$column_list[] = $entities[0];
					}

					$columns = implode(',', $column_list);

					$new_table_cols = $new_table_cols = preg_replace('/' . $column_name . '[^,]+(?:,|$)/m', '', $new_table_cols);

					// create a new table and fill it up. destroy the temp one
					$statements[] = 'CREATE TABLE ' . $table_name . ' (' . $new_table_cols . ');';
					$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
					$statements[] = 'DROP TABLE ' . $table_name . '_temp';

					$statements[] = 'commit';
				}
				else
				{
					$statements[] = 'ALTER TABLE ' . $table_name . ' DROP COLUMN ' . $column_name;
				}
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Drop Index
	*/
	function sql_index_drop($table_name, $index_name)
	{
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'mssql':
				$statements[] = 'DROP INDEX ' . $table_name . '.' . $index_name;
			break;

			case 'mysql_40':
			case 'mysql_41':
				$statements[] = 'DROP INDEX ' . $index_name . ' ON ' . $table_name;
			break;

			case 'firebird':
			case 'oracle':
			case 'postgres':
			case 'sqlite':
				$statements[] = 'DROP INDEX ' . $table_name . '_' . $index_name;
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Add primary key
	*/
	function sql_create_primary_key($table_name, $column, $inline = false)
	{
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'firebird':
			case 'postgres':
			case 'mysql_40':
			case 'mysql_41':
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD PRIMARY KEY (' . implode(', ', $column) . ')';
			break;

			case 'mssql':
				$sql = "ALTER TABLE [{$table_name}] WITH NOCHECK ADD ";
				$sql .= "CONSTRAINT [PK_{$table_name}] PRIMARY KEY  CLUSTERED (";
				$sql .= '[' . implode("],\n\t\t[", $column) . ']';
				$sql .= ') ON [PRIMARY]';

				$statements[] = $sql;
			break;

			case 'oracle':
				$statements[] = 'ALTER TABLE ' . $table_name . 'add CONSTRAINT pk_' . $table_name . ' PRIMARY KEY (' . implode(', ', $column) . ')';
			break;

			case 'sqlite':

				if ($inline && $this->return_statements)
				{
					return $column;
				}

				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table_name}'
					ORDER BY type DESC, name;";
				$result = $this->db->sql_query($sql);

				if (!$result)
				{
					break;
				}

				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$statements[] = 'begin';

				// Create a backup table and populate it, destroy the existing one
				$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']);
				$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
				$statements[] = 'DROP TABLE ' . $table_name;

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
				$statements[] = 'CREATE TABLE ' . $table_name . ' (' . $new_table_cols . ', PRIMARY KEY (' . implode(', ', $column) . '));';
				$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
				$statements[] = 'DROP TABLE ' . $table_name . '_temp';

				$statements[] = 'commit';
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Add unique index
	*/
	function sql_create_unique_index($table_name, $index_name, $column)
	{
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'firebird':
			case 'postgres':
			case 'oracle':
			case 'sqlite':
				$statements[] = 'CREATE UNIQUE INDEX ' . $table_name . '_' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			break;

			case 'mysql_40':
			case 'mysql_41':
				$statements[] = 'CREATE UNIQUE INDEX ' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			break;

			case 'mssql':
				$statements[] = 'CREATE UNIQUE INDEX ' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ') ON [PRIMARY]';
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Add index
	*/
	function sql_create_index($table_name, $index_name, $column)
	{
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'firebird':
			case 'postgres':
			case 'oracle':
			case 'sqlite':
				$statements[] = 'CREATE INDEX ' . $table_name . '_' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			break;

			case 'mysql_40':
			case 'mysql_41':
				$statements[] = 'CREATE INDEX ' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			break;

			case 'mssql':
				$statements[] = 'CREATE INDEX ' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ') ON [PRIMARY]';
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Change column type (not name!)
	*/
	function sql_column_change($table_name, $column_name, $column_data, $inline = false)
	{
		$column_data = $this->sql_prepare_column_data($table_name, $column_name, $column_data);
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'firebird':
				// Change type...
				if (!empty($column_data['column_type_sql_default']))
				{
					$statements[] = 'ALTER TABLE ' . $table_name . ' ALTER COLUMN "' . strtoupper($column_name) . '" TYPE ' . ' ' . $column_data['column_type_sql_type'];
					$statements[] = 'ALTER TABLE ' . $table_name . ' ALTER COLUMN "' . strtoupper($column_name) . '" SET DEFAULT ' . ' ' . $column_data['column_type_sql_default'];
				}
				else
				{
					// TODO: try to change pkey without removing trigger, generator or constraints. ATM this query may fail.
					$statements[] = 'ALTER TABLE ' . $table_name . ' ALTER COLUMN "' . strtoupper($column_name) . '" TYPE ' . ' ' . $column_data['column_type_sql_type'];
				}
			break;

			case 'mssql':
				$statements[] = 'ALTER TABLE [' . $table_name . '] ALTER COLUMN [' . $column_name . '] ' . $column_data['column_type_sql'];

				if (!empty($column_data['default']))
				{
					// Using TRANSACT-SQL for this statement because we do not want to have colliding data if statements are executed at a later stage
					$statements[] = "DECLARE @drop_default_name VARCHAR(100), @cmd VARCHAR(1000)
						SET @drop_default_name =
							(SELECT so.name FROM sysobjects so
							JOIN sysconstraints sc ON so.id = sc.constid
							WHERE object_name(so.parent_obj) = '{$table_name}'
								AND so.xtype = 'D'
								AND sc.colid = (SELECT colid FROM syscolumns
									WHERE id = object_id('{$table_name}')
										AND name = '{$column_name}'))
						IF @drop_default_name <> ''
						BEGIN
							SET @cmd = 'ALTER TABLE [{$table_name}] DROP CONSTRAINT [' + @drop_default_name + ']'
							EXEC(@cmd)
						END
						SET @cmd = 'ALTER TABLE [{$table_name}] ADD CONSTRAINT [DF_{$table_name}_{$column_name}_1] {$column_data['default']} FOR [{$column_name}]'
						EXEC(@cmd)";
				}
			break;

			case 'mysql_40':
			case 'mysql_41':
				$statements[] = 'ALTER TABLE `' . $table_name . '` CHANGE `' . $column_name . '` `' . $column_name . '` ' . $column_data['column_type_sql'];
			break;

			case 'oracle':
				$statements[] = 'ALTER TABLE ' . $table_name . ' MODIFY ' . $column_name . ' ' . $column_data['column_type_sql'];
			break;

			case 'postgres':
				$sql = 'ALTER TABLE ' . $table_name . ' ';

				$sql_array = array();
				$sql_array[] = 'ALTER COLUMN ' . $column_name . ' TYPE ' . $column_data['column_type'];

				if (isset($column_data['null']))
				{
					if ($column_data['null'] == 'NOT NULL')
					{
						$sql_array[] = 'ALTER COLUMN ' . $column_name . ' SET NOT NULL';
					}
					else if ($column_data['null'] == 'NULL')
					{
						$sql_array[] = 'ALTER COLUMN ' . $column_name . ' DROP NOT NULL';
					}
				}

				if (isset($column_data['default']))
				{
					$sql_array[] = 'ALTER COLUMN ' . $column_name . ' SET DEFAULT ' . $column_data['default'];
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

					$result = $this->db->sql_query($constraint_sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						if (trim($row['constraint_data']) == trim($column_data['constraint']))
						{
							$constraint_exists = true;
							break;
						}
					}
					$this->db->sql_freeresult($result);

					if (!$constraint_exists)
					{
						$sql_array[] = 'ADD ' . $column_data['constraint'];
					}
				}

				$sql .= implode(', ', $sql_array);

				$statements[] = $sql;
			break;

			case 'sqlite':

				if ($inline && $this->return_statements)
				{
					return $column_name . ' ' . $column_data['column_type_sql'];
				}

				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table_name}'
					ORDER BY type DESC, name;";
				$result = $this->db->sql_query($sql);

				if (!$result)
				{
					break;
				}

				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$statements[] = 'begin';

				// Create a temp table and populate it, destroy the existing one
				$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']);
				$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
				$statements[] = 'DROP TABLE ' . $table_name;

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
				$statements[] = 'CREATE TABLE ' . $table_name . ' (' . implode(',', $old_table_cols) . ');';
				$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
				$statements[] = 'DROP TABLE ' . $table_name . '_temp';

				$statements[] = 'commit';

			break;
		}

		return $this->_sql_run_sql($statements);
	}
}

?>