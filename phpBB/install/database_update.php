<?php
/**
*
* @package install
* @version $Id$
* @copyright (c) 2006 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('UPDATES_TO_VERSION', '3.0.12-dev');

// Enter any version to update from to test updates. The version within the db will not be updated.
define('DEBUG_FROM_VERSION', false);

// Which oldest version does this updater support?
define('OLDEST_FROM_VERSION', '3.0.0');

// Return if we "just include it" to find out for which version the database update is responsible for
if (defined('IN_PHPBB') && defined('IN_INSTALL'))
{
	$updates_to_version = UPDATES_TO_VERSION;
	$debug_from_version = DEBUG_FROM_VERSION;
	$oldest_from_version = OLDEST_FROM_VERSION;

	return;
}

/**
*/
define('IN_PHPBB', true);
define('IN_INSTALL', true);

$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

if (!function_exists('phpbb_require_updated'))
{
	function phpbb_require_updated($path, $optional = false)
	{
		global $phpbb_root_path;

		$new_path = $phpbb_root_path . 'install/update/new/' . $path;
		$old_path = $phpbb_root_path . $path;

		if (file_exists($new_path))
		{
			require($new_path);
		}
		else if (!$optional || file_exists($old_path))
		{
			require($old_path);
		}
	}
}

phpbb_require_updated('includes/startup.' . $phpEx);

$updates_to_version = UPDATES_TO_VERSION;
$debug_from_version = DEBUG_FROM_VERSION;
$oldest_from_version = OLDEST_FROM_VERSION;

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

phpbb_require_updated('includes/functions_content.' . $phpEx, true);

require($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

phpbb_require_updated('includes/db/db_tools.' . $phpEx);

// new table constants are separately defined here in case the updater is run
// before the files are updated
if (!defined('LOGIN_ATTEMPT_TABLE'))
{
	define('LOGIN_ATTEMPT_TABLE', $table_prefix . 'login_attempts');
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
$user->ip = (stripos($user->ip, '::ffff:') === 0) ? substr($user->ip, 7) : $user->ip;

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

// phpbb_db_tools will be taken from new files (under install/update/new)
// if possible, falling back to the board's copy.
$db_tools = new phpbb_db_tools($db, true);

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

// Firebird update from Firebird 2.0 to 2.1+ required?
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

	<p><a href="<?php echo append_sid("{$phpbb_root_path}install/index.{$phpEx}", "mode=update&amp;sub=file_check&amp;language=$language"); ?>" class="button1"><?php echo (isset($lang['CONTINUE_UPDATE_NOW'])) ? $lang['CONTINUE_UPDATE_NOW'] : 'Continue the update process now'; ?></a></p>

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
		Powered by <a href="https://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Group
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

	if ($sql === 'begin')
	{
		$result = $db->sql_transaction('begin');
	}
	else if ($sql === 'commit')
	{
		$result = $db->sql_transaction('commit');
	}
	else
	{
		$result = $db->sql_query($sql);
		if ($db->sql_error_triggered)
		{
			$errored = true;
			$error_ary['sql'][] = $db->sql_error_sql;
			$error_ary['error_code'][] = $db->sql_error_returned;
		}
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
					AND left_id BETWEEN {$first_left_id} AND {$module_row['left_id']}";
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
}

?>
