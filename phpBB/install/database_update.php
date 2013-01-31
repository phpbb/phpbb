<?php
/**
*
* @package install
* @copyright (c) 2006 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

define('UPDATES_TO_VERSION', '3.1.0-dev');

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

@set_time_limit(0);

// Include essential scripts
include($phpbb_root_path . 'config.' . $phpEx);

if (!defined('PHPBB_INSTALLED') || empty($dbms) || empty($acm_type))
{
	die("Please read: <a href='../docs/INSTALL.html'>INSTALL.html</a> before attempting to update.");
}

// In case $phpbb_adm_relative_path is not set (in case of an update), use the default.
$phpbb_adm_relative_path = (isset($phpbb_adm_relative_path)) ? $phpbb_adm_relative_path : 'adm/';
$phpbb_admin_path = (defined('PHPBB_ADMIN_PATH')) ? PHPBB_ADMIN_PATH : $phpbb_root_path . $phpbb_adm_relative_path;

// Include files
require($phpbb_root_path . 'includes/class_loader.' . $phpEx);

require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/functions_container.' . $phpEx);

phpbb_require_updated('includes/functions_content.' . $phpEx, true);

require($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

phpbb_require_updated('includes/db/db_tools.' . $phpEx);

// new table constants are separately defined here in case the updater is run
// before the files are updated
if (!defined('LOGIN_ATTEMPT_TABLE'))
{
	define('LOGIN_ATTEMPT_TABLE', $table_prefix . 'login_attempts');
}
if (!defined('NOTIFICATION_TYPES_TABLE'))
{
	define('NOTIFICATION_TYPES_TABLE', $table_prefix . 'notification_types');
}
if (!defined('NOTIFICATIONS_TYPES_TABLE'))
{
	define('NOTIFICATIONS_TYPES_TABLE', $table_prefix . 'notifications_types');
}
if (!defined('NOTIFICATIONS_TABLE'))
{
	define('NOTIFICATIONS_TABLE', $table_prefix . 'notifications');
}
if (!defined('USER_NOTIFICATIONS_TABLE'))
{
	define('USER_NOTIFICATIONS_TABLE', $table_prefix . 'user_notifications');
}
if (!defined('EXT_TABLE'))
{
	define('EXT_TABLE', $table_prefix . 'ext');
}

// Setup class loader first
$phpbb_class_loader = new phpbb_class_loader('phpbb_', "{$phpbb_root_path}includes/", ".$phpEx");
$phpbb_class_loader->register();
$phpbb_class_loader_ext = new phpbb_class_loader('phpbb_ext_', "{$phpbb_root_path}ext/", ".$phpEx");
$phpbb_class_loader_ext->register();

// Set up container
$phpbb_container = phpbb_create_default_container($phpbb_root_path, $phpEx);

$phpbb_class_loader->set_cache($phpbb_container->get('cache.driver'));
$phpbb_class_loader_ext->set_cache($phpbb_container->get('cache.driver'));

// set up caching
$cache = $phpbb_container->get('cache');

// Instantiate some basic classes
$phpbb_dispatcher = $phpbb_container->get('dispatcher');
$request	= $phpbb_container->get('request');
$user		= $phpbb_container->get('user');
$auth		= $phpbb_container->get('auth');
$db			= $phpbb_container->get('dbal.conn');

// make sure request_var uses this request instance
request_var('', 0, false, false, $request); // "dependency injection" for a function

// Add own hook handler, if present. :o
if (file_exists($phpbb_root_path . 'includes/hooks/index.' . $phpEx))
{
	require($phpbb_root_path . 'includes/hooks/index.' . $phpEx);
	$phpbb_hook = new phpbb_hook(array('exit_handler', 'phpbb_user_session_handler', 'append_sid', array('template', 'display')));

	$phpbb_hook_finder = $phpbb_container->get('hook_finder');
	foreach ($phpbb_hook_finder->find() as $hook)
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

$user->ip = '';
if ($request->server('REMOTE_ADDR'))
{
	$user->ip = (function_exists('phpbb_ip_normalise')) ? phpbb_ip_normalise($request->server('REMOTE_ADDR')) : $request->server('REMOTE_ADDR');
}

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
$config = new phpbb_config_db($db, $phpbb_container->get('cache.driver'), CONFIG_TABLE);
set_config(null, null, null, $config);
set_config_count(null, null, null, $config);

// Update asset_version
if (isset($config['assets_version']))
{
	set_config('assets_version', $config['assets_version'] + 1);
}

// phpbb_db_tools will be taken from new files (under install/update/new)
// if possible, falling back to the board's copy.
$db_tools = new phpbb_db_tools($db, true);

$database_update_info = database_update_info();

$error_ary = array();
$errored = false;

$sql = 'SELECT topic_id
	FROM ' . TOPICS_TABLE . '
	WHERE forum_id = 0
		AND topic_type = ' . POST_GLOBAL;
$result = $db->sql_query_limit($sql, 1);
$has_global = (int) $db->sql_fetchfield('topic_id');
$db->sql_freeresult($result);
$ga_forum_id = request_var('ga_forum_id', 0);

if ($has_global && !$ga_forum_id)
{
	?>
	<!DOCTYPE html>
	<html dir="<?php echo $lang['DIRECTION']; ?>" lang="<?php echo $lang['USER_LANG']; ?>">
	<head>
	<meta charset="utf-8">

	<title><?php echo $lang['UPDATING_TO_LATEST_STABLE']; ?></title>

	<link href="<?php echo htmlspecialchars($phpbb_admin_path); ?>style/admin.css" rel="stylesheet" type="text/css" media="screen" />

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

		<form action="" method="post" id="select_ga_forum_id">
			<?php
				if (isset($lang['SELECT_FORUM_GA']))
				{
					// Language string is available:
					echo $lang['SELECT_FORUM_GA'];
				}
				else
				{
					echo 'In phpBB 3.1 the global announcements are linked to forums. Select a forum for your current global announcements (can be moved later):';
				}
			?>
			<select id="ga_forum_id" name="ga_forum_id"><?php echo make_forum_select(false, false, true, true) ?></select>

			<input type="submit" name="post" value="<?php echo $lang['SUBMIT']; ?>" class="button1" />
		</form>
	<?php
	_print_footer();
	exit_handler();
}

header('Content-type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html dir="<?php echo $lang['DIRECTION']; ?>" lang="<?php echo $lang['USER_LANG']; ?>">
<head>
<meta charset="utf-8">

<title><?php echo $lang['UPDATING_TO_LATEST_STABLE']; ?></title>

<link href="<?php echo htmlspecialchars($phpbb_admin_path); ?>style/admin.css" rel="stylesheet" type="text/css" media="screen" />

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
$phpbb_container->get('cache.driver')->purge();

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

	if (defined('DEBUG'))
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
	global $phpbb_root_path, $phpEx, $db, $phpbb_extension_manager, $config;

	// modules require an extension manager
	if (empty($phpbb_extension_manager))
	{
		$phpbb_extension_manager = new phpbb_extension_manager($db, $config, EXT_TABLE, $phpbb_root_path, ".$phpEx");
	}

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

/**
* Add a new permission, optionally copy permission setting from another
*
* @param auth_admin $auth_admin auth_admin object
* @param phpbb_db_driver $db Database object
* @param string $permission_name Name of the permission to add
* @param bool $is_global True is global, false is local
* @param string $copy_from Optional permission name from which to copy
* @return bool true on success, false on failure
*/
function _add_permission(auth_admin $auth_admin, phpbb_db_driver $db, $permission_name, $is_global = true, $copy_from = '')
{
	// Only add a permission that don't already exist
	if (!empty($auth_admin->acl_options['id'][$permission_name]))
	{
		return true;
	}

	$permission_scope = $is_global ? 'global' : 'local';

	$result = $auth_admin->acl_add_option(array(
		$permission_scope => array($permission_name),
	));

	if (!$result)
	{
		return $result;
	}

	// The permission has been added, now we can copy it if needed
	if ($copy_from && isset($auth_admin->acl_options['id'][$copy_from]))
	{
		$old_id = $auth_admin->acl_options['id'][$copy_from];
		$new_id = $auth_admin->acl_options['id'][$permission_name];

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

		$auth_admin->acl_clear_prefetch();
	}

	return true;
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
					'post_username'		=> array('post_username:255'),
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
		// No changes from 3.0.7-PL1 to 3.0.8-RC1
		'3.0.7-PL1'		=> array(),
		// No changes from 3.0.8-RC1 to 3.0.8
		'3.0.8-RC1'		=> array(),
		// Changes from 3.0.8 to 3.0.9-RC1
		'3.0.8'			=> array(
			'add_tables'		=> array(
				LOGIN_ATTEMPT_TABLE	=> array(
					'COLUMNS'			=> array(
						// this column was removed from the database updater
						// after 3.0.9-RC3 was released. It might still exist
						// in 3.0.9-RCX installations and has to be dropped in
						// 3.0.12 after the db_tools class is capable of properly
						// removing a primary key.
						// 'attempt_id'			=> array('UINT', NULL, 'auto_increment'),
						'attempt_ip'			=> array('VCHAR:40', ''),
						'attempt_browser'		=> array('VCHAR:150', ''),
						'attempt_forwarded_for'	=> array('VCHAR:255', ''),
						'attempt_time'			=> array('TIMESTAMP', 0),
						'user_id'				=> array('UINT', 0),
						'username'				=> array('VCHAR_UNI:255', 0),
						'username_clean'		=> array('VCHAR_CI', 0),
					),
					//'PRIMARY_KEY'		=> 'attempt_id',
					'KEYS'				=> array(
						'att_ip'			=> array('INDEX', array('attempt_ip', 'attempt_time')),
						'att_for'	=> array('INDEX', array('attempt_forwarded_for', 'attempt_time')),
						'att_time'			=> array('INDEX', array('attempt_time')),
						'user_id'				=> array('INDEX', 'user_id'),
					),
				),
			),
			'change_columns'	=> array(
				BBCODES_TABLE	=> array(
					'bbcode_id'	=> array('USINT', 0),
				),
			),
		),
		// No changes from 3.0.9-RC1 to 3.0.9-RC2
		'3.0.9-RC1'		=> array(),
		// No changes from 3.0.9-RC2 to 3.0.9-RC3
		'3.0.9-RC2'		=> array(),
		// No changes from 3.0.9-RC3 to 3.0.9-RC4
		'3.0.9-RC3'     => array(),
		// No changes from 3.0.9-RC4 to 3.0.9
		'3.0.9-RC4'     => array(),
		// No changes from 3.0.9 to 3.0.10-RC1
		'3.0.9'			=> array(),
		// No changes from 3.0.10-RC1 to 3.0.10-RC2
		'3.0.10-RC1'	=> array(),
		// No changes from 3.0.10-RC2 to 3.0.10-RC3
		'3.0.10-RC2'	=> array(),
		// No changes from 3.0.10-RC3 to 3.0.10
		'3.0.10-RC3'	=> array(),
		// No changes from 3.0.10 to 3.0.11-RC1
		'3.0.10'		=> array(),
		// Changes from 3.0.11-RC1 to 3.0.11-RC2
		'3.0.11-RC1'	=> array(
			'add_columns'		=> array(
				PROFILE_FIELDS_TABLE			=> array(
					'field_show_novalue'		=> array('BOOL', 0),
				),
			),
		),
		// No changes from 3.0.11-RC2 to 3.0.11
		'3.0.11-RC2'	=> array(),
		// No changes from 3.0.11 to 3.0.12-RC1
		'3.0.11'		=> array(),

		/** @todo DROP LOGIN_ATTEMPT_TABLE.attempt_id in 3.0.12-RC1 */

		// Changes from 3.1.0-dev to 3.1.0-A1
		'3.1.0-dev'		=> array(
			'add_tables'		=> array(
				EXT_TABLE				=> array(
					'COLUMNS'			=> array(
						'ext_name'		=> array('VCHAR', ''),
						'ext_active'	=> array('BOOL', 0),
						'ext_state'		=> array('TEXT', ''),
					),
					'KEYS'				=> array(
						'ext_name'		=> array('UNIQUE', 'ext_name'),
					),
				),
				NOTIFICATION_TYPES_TABLE	=> array(
					'COLUMNS'			=> array(
						'notification_type'			=> array('VCHAR:255', ''),
						'notification_type_enabled'	=> array('BOOL', 1),
					),
					'PRIMARY_KEY'		=> array('notification_type', 'notification_type_enabled'),
				),
				NOTIFICATIONS_TABLE		=> array(
					'COLUMNS'			=> array(
						'notification_id'  				=> array('UINT', NULL, 'auto_increment'),
						'item_type'			   			=> array('VCHAR:255', ''),
						'item_id'		  				=> array('UINT', 0),
						'item_parent_id'   				=> array('UINT', 0),
						'user_id'						=> array('UINT', 0),
						'notification_read'				=> array('BOOL', 0),
						'notification_time'				=> array('TIMESTAMP', 1),
						'notification_data'			   	=> array('TEXT_UNI', ''),
					),
					'PRIMARY_KEY'		=> 'notification_id',
					'KEYS'				=> array(
						'item_ident'		=> array('INDEX', array('item_type', 'item_id')),
						'user'				=> array('INDEX', array('user_id', 'notification_read')),
					),
				),
				USER_NOTIFICATIONS_TABLE	=> array(
					'COLUMNS'			=> array(
						'item_type'			=> array('VCHAR:255', ''),
						'item_id'			=> array('UINT', 0),
						'user_id'			=> array('UINT', 0),
						'method'			=> array('VCHAR:255', ''),
						'notify'			=> array('BOOL', 1),
					),
				),
			),
			'add_columns'		=> array(
				GROUPS_TABLE		=> array(
					'group_teampage'	=> array('UINT', 0, 'after' => 'group_legend'),
				),
				PROFILE_FIELDS_TABLE	=> array(
					'field_show_on_pm'		=> array('BOOL', 0),
				),
				STYLES_TABLE		=> array(
					'style_path'			=> array('VCHAR:100', ''),
					'bbcode_bitfield'		=> array('VCHAR:255', 'kNg='),
					'style_parent_id'		=> array('UINT:4', 0),
					'style_parent_tree'		=> array('TEXT', ''),
				),
				REPORTS_TABLE		=> array(
					'reported_post_text'				=> array('MTEXT_UNI', ''),
					'reported_post_uid'					=> array('VCHAR:8', ''),
					'reported_post_bitfield'			=> array('VCHAR:255', ''),
					'reported_post_enable_bbcode'		=> array('BOOL', 1),
					'reported_post_enable_smilies'		=> array('BOOL', 1),
					'reported_post_enable_magic_url'	=> array('BOOL', 1),
				),
			),
			'change_columns'	=> array(
				GROUPS_TABLE		=> array(
					'group_legend'		=> array('UINT', 0),
				),
				USERS_TABLE			=> array(
					'user_timezone'		=> array('VCHAR:100', ''),
				),
			),
		),
	);
}

/****************************************************************************
* ADD YOUR DATABASE DATA CHANGES HERE										*
* REMEMBER: You NEED to enter a schema array above and a data array here,	*
* even if both or one of them are empty.									*
*****************************************************************************/
function change_database_data(&$no_updates, $version)
{
	global $db, $db_tools, $errored, $error_ary, $config, $table_prefix, $phpbb_root_path, $phpEx;

	$update_helpers = new phpbb_update_helpers();

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

		// Changes from 3.0.7-PL1 to 3.0.8-RC1
		case '3.0.7-PL1':
			// Update file extension group names to use language strings.
			$sql = 'SELECT lang_dir
				FROM ' . LANG_TABLE;
			$result = $db->sql_query($sql);

			$extension_groups_updated = array();
			while ($lang_dir = $db->sql_fetchfield('lang_dir'))
			{
				$lang_dir = basename($lang_dir);

				// The language strings we need are either in language/.../acp/attachments.php
				// in the update package if we're updating to 3.0.8-RC1 or later,
				// or they are in language/.../install.php when we're updating from 3.0.7-PL1 or earlier.
				// On an already updated board, they can also already be in language/.../acp/attachments.php
				// in the board root.
				$lang_files = array(
					"{$phpbb_root_path}install/update/new/language/$lang_dir/acp/attachments.$phpEx",
					"{$phpbb_root_path}language/$lang_dir/install.$phpEx",
					"{$phpbb_root_path}language/$lang_dir/acp/attachments.$phpEx",
				);

				foreach ($lang_files as $lang_file)
				{
					if (!file_exists($lang_file))
					{
						continue;
					}

					$lang = array();
					include($lang_file);

					foreach($lang as $lang_key => $lang_val)
					{
						if (isset($extension_groups_updated[$lang_key]) || strpos($lang_key, 'EXT_GROUP_') !== 0)
						{
							continue;
						}

						$sql_ary = array(
							'group_name'	=> substr($lang_key, 10), // Strip off 'EXT_GROUP_'
						);

						$sql = 'UPDATE ' . EXTENSION_GROUPS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
							WHERE group_name = '" . $db->sql_escape($lang_val) . "'";
						_sql($sql, $errored, $error_ary);

						$extension_groups_updated[$lang_key] = true;
					}
				}
			}
			$db->sql_freeresult($result);

			// Install modules
			$modules_to_install = array(
				'post'					=> array(
					'base'		=> 'board',
					'class'		=> 'acp',
					'title'		=> 'ACP_POST_SETTINGS',
					'auth'		=> 'acl_a_board',
					'cat'		=> 'ACP_MESSAGES',
					'after'		=> array('message', 'ACP_MESSAGE_SETTINGS')
				),
			);

			_add_modules($modules_to_install);

			// update
			$sql = 'UPDATE ' . MODULES_TABLE . '
				SET module_auth = \'cfg_allow_avatar && (cfg_allow_avatar_local || cfg_allow_avatar_remote || cfg_allow_avatar_upload || cfg_allow_avatar_remote_upload)\'
				WHERE module_class = \'ucp\'
					AND module_basename = \'profile\'
					AND module_mode = \'avatar\'';
			_sql($sql, $errored, $error_ary);

			// add Bing Bot
			$bot_name = 'Bing [Bot]';
			$bot_name_clean = utf8_clean_string($bot_name);

			$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . "
				WHERE username_clean = '" . $db->sql_escape($bot_name_clean) . "'";
			$result = $db->sql_query($sql);
			$bing_already_added = (bool) $db->sql_fetchfield('user_id');
			$db->sql_freeresult($result);

			if (!$bing_already_added)
			{
				$bot_agent = 'bingbot/';
				$bot_ip = '';
				$sql = 'SELECT group_id, group_colour
					FROM ' . GROUPS_TABLE . "
					WHERE group_name = 'BOTS'";
				$result = $db->sql_query($sql);
				$group_row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$group_row)
				{
					// default fallback, should never get here
					$group_row['group_id'] = 6;
					$group_row['group_colour'] = '9E8DA7';
				}

				if (!function_exists('user_add'))
				{
					include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
				}

				$user_row = array(
					'user_type'				=> USER_IGNORE,
					'group_id'				=> $group_row['group_id'],
					'username'				=> $bot_name,
					'user_regdate'			=> time(),
					'user_password'			=> '',
					'user_colour'			=> $group_row['group_colour'],
					'user_email'			=> '',
					'user_lang'				=> $config['default_lang'],
					'user_style'			=> $config['default_style'],
					'user_timezone'			=> 'UTC',
					'user_dateformat'		=> $config['default_dateformat'],
					'user_allow_massemail'	=> 0,
				);

				$user_id = user_add($user_row);

				$sql = 'INSERT INTO ' . BOTS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
					'bot_active'	=> 1,
					'bot_name'		=> (string) $bot_name,
					'user_id'		=> (int) $user_id,
					'bot_agent'		=> (string) $bot_agent,
					'bot_ip'		=> (string) $bot_ip,
				));

				_sql($sql, $errored, $error_ary);
			}
			// end Bing Bot addition

			// Delete shadow topics pointing to not existing topics
			$batch_size = 500;

			// Set of affected forums we have to resync
			$sync_forum_ids = array();

			do
			{
				$sql_array = array(
					'SELECT'	=> 't1.topic_id, t1.forum_id',
					'FROM'		=> array(
						TOPICS_TABLE	=> 't1',
					),
					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(TOPICS_TABLE	=> 't2'),
							'ON'	=> 't1.topic_moved_id = t2.topic_id',
						),
					),
					'WHERE'		=> 't1.topic_moved_id <> 0
								AND t2.topic_id IS NULL',
				);
				$sql = $db->sql_build_query('SELECT', $sql_array);
				$result = $db->sql_query_limit($sql, $batch_size);

				$topic_ids = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$topic_ids[] = (int) $row['topic_id'];

					$sync_forum_ids[(int) $row['forum_id']] = (int) $row['forum_id'];
				}
				$db->sql_freeresult($result);

				if (!empty($topic_ids))
				{
					$sql = 'DELETE FROM ' . TOPICS_TABLE . '
						WHERE ' . $db->sql_in_set('topic_id', $topic_ids);
					$db->sql_query($sql);
				}
			}
			while (sizeof($topic_ids) == $batch_size);

			// Sync the forums we have deleted shadow topics from.
			sync('forum', 'forum_id', $sync_forum_ids, true, true);

			// Unread posts search load switch
			set_config('load_unreads_search', '1');

			// Reduce queue interval to 60 seconds, email package size to 20
			if ($config['queue_interval'] == 600)
			{
				set_config('queue_interval', '60');
			}

			if ($config['email_package_size'] == 50)
			{
				set_config('email_package_size', '20');
			}

			$no_updates = false;
		break;

		// No changes from 3.0.8-RC1 to 3.0.8
		case '3.0.8-RC1':
		break;

		// Changes from 3.0.8 to 3.0.9-RC1
		case '3.0.8':
			set_config('ip_login_limit_max', '50');
			set_config('ip_login_limit_time', '21600');
			set_config('ip_login_limit_use_forwarded', '0');

			// Update file extension group names to use language strings, again.
			$sql = 'SELECT group_id, group_name
				FROM ' . EXTENSION_GROUPS_TABLE . '
				WHERE group_name ' . $db->sql_like_expression('EXT_GROUP_' . $db->any_char);
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$sql_ary = array(
					'group_name'	=> substr($row['group_name'], 10), // Strip off 'EXT_GROUP_'
				);

				$sql = 'UPDATE ' . EXTENSION_GROUPS_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE group_id = ' . $row['group_id'];
				_sql($sql, $errored, $error_ary);
			}
			$db->sql_freeresult($result);

			/*
			* Due to a bug, vanilla phpbb could not create captcha tables
			* in 3.0.8 on firebird. It was possible for board administrators
			* to adjust the code to work. If code was manually adjusted by
			* board administrators, index names would not be the same as
			* what 3.0.9 and newer expect. This code fragment drops captcha
			* tables, destroying all entered Q&A captcha configuration, such
			* that when Q&A is configured next the respective tables will be
			* created with correct index names.
			*
			* If you wish to preserve your Q&A captcha configuration, you can
			* manually rename indexes to the currently expected name:
			* 	phpbb_captcha_questions_lang_iso	=> phpbb_captcha_questions_lang
			* 	phpbb_captcha_answers_question_id	=> phpbb_captcha_answers_qid
			*
			* Again, this needs to be done only if a board was manually modified
			* to fix broken captcha code.
			*
			if ($db_tools->sql_layer == 'firebird')
			{
				$changes = array(
					'drop_tables'	=> array(
						$table_prefix . 'captcha_questions',
						$table_prefix . 'captcha_answers',
						$table_prefix . 'qa_confirm',
					),
				);
				$statements = $db_tools->perform_schema_changes($changes);

				foreach ($statements as $sql)
				{
					_sql($sql, $errored, $error_ary);
				}
			}
			*/

			$no_updates = false;
		break;

		// No changes from 3.0.9-RC1 to 3.0.9-RC2
		case '3.0.9-RC1':
		break;

		// No changes from 3.0.9-RC2 to 3.0.9-RC3
		case '3.0.9-RC2':
		break;

		// No changes from 3.0.9-RC3 to 3.0.9-RC4
		case '3.0.9-RC3':
		break;

		// No changes from 3.0.9-RC4 to 3.0.9
		case '3.0.9-RC4':
		break;

		// Changes from 3.0.9 to 3.0.10-RC1
		case '3.0.9':
			if (!isset($config['email_max_chunk_size']))
			{
				set_config('email_max_chunk_size', '50');
			}

			$no_updates = false;
		break;

		// No changes from 3.0.10-RC1 to 3.0.10-RC2
		case '3.0.10-RC1':
		break;

		// No changes from 3.0.10-RC2 to 3.0.10-RC3
		case '3.0.10-RC2':
		break;

		// No changes from 3.0.10-RC3 to 3.0.10
		case '3.0.10-RC3':
		break;

		// Changes from 3.0.10 to 3.0.11-RC1
		case '3.0.10':
			// Updates users having current style a deactivated one
			$sql = 'SELECT style_id
				FROM ' . STYLES_TABLE . '
				WHERE style_active = 0';
			$result = $db->sql_query($sql);

			$deactivated_style_ids = array();
			while ($style_id = $db->sql_fetchfield('style_id', false, $result))
			{
				$deactivated_style_ids[] = (int) $style_id;
			}
			$db->sql_freeresult($result);

			if (!empty($deactivated_style_ids))
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_style = ' . (int) $config['default_style'] .'
					WHERE ' . $db->sql_in_set('user_style', $deactivated_style_ids);
				_sql($sql, $errored, $error_ary);
			}

			// Delete orphan private messages
			$batch_size = 500;

			$sql_array = array(
				'SELECT'	=> 'p.msg_id',
				'FROM'		=> array(
					PRIVMSGS_TABLE	=> 'p',
				),
				'LEFT_JOIN'	=> array(
					array(
						'FROM'	=> array(PRIVMSGS_TO_TABLE => 't'),
						'ON'	=> 'p.msg_id = t.msg_id',
					),
				),
				'WHERE'		=> 't.user_id IS NULL',
			);
			$sql = $db->sql_build_query('SELECT', $sql_array);

			do
			{
				$result = $db->sql_query_limit($sql, $batch_size);

				$delete_pms = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$delete_pms[] = (int) $row['msg_id'];
				}
				$db->sql_freeresult($result);

				if (!empty($delete_pms))
				{
					$sql = 'DELETE FROM ' . PRIVMSGS_TABLE . '
						WHERE ' . $db->sql_in_set('msg_id', $delete_pms);
					_sql($sql, $errored, $error_ary);
				}
			}
			while (sizeof($delete_pms) == $batch_size);

			$no_updates = false;
		break;

		// No changes from 3.0.11-RC1 to 3.0.11-RC2
		case '3.0.11-RC1':
		break;

		// No changes from 3.0.11-RC2 to 3.0.11
		case '3.0.11-RC2':
		break;

		// Changes from 3.0.11 to 3.0.12-RC1
		case '3.0.11':
			$sql = 'UPDATE ' . MODULES_TABLE . '
				SET module_auth = \'acl_u_sig\'
				WHERE module_class = \'ucp\'
					AND module_basename = \'profile\'
					AND module_mode = \'signature\'';
			_sql($sql, $errored, $error_ary);

			// Update bots
			if (!function_exists('user_delete'))
			{
				include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
			}

			$bots_updates = array(
				// Bot Deletions
				'NG-Search [Bot]'		=> false,
				'Nutch/CVS [Bot]'		=> false,
				'OmniExplorer [Bot]'	=> false,
				'Seekport [Bot]'		=> false,
				'Synoo [Bot]'			=> false,
				'WiseNut [Bot]'			=> false,

				// Bot Updates
				// Bot name to bot user agent map
				'Baidu [Spider]'	=> 'Baiduspider',
				'Exabot [Bot]'		=> 'Exabot',
				'Voyager [Bot]'		=> 'voyager/',
				'W3C [Validator]'	=> 'W3C_Validator',
			);

			foreach ($bots_updates as $bot_name => $bot_agent)
			{
				$sql = 'SELECT user_id
					FROM ' . USERS_TABLE . '
					WHERE user_type = ' . USER_IGNORE . "
						AND username_clean = '" . $db->sql_escape(utf8_clean_string($bot_name)) . "'";
				$result = $db->sql_query($sql);
				$bot_user_id = (int) $db->sql_fetchfield('user_id');
				$db->sql_freeresult($result);

				if ($bot_user_id)
				{
					if ($bot_agent === false)
					{
						$sql = 'DELETE FROM ' . BOTS_TABLE . "
							WHERE user_id = $bot_user_id";
						_sql($sql, $errored, $error_ary);

						user_delete('remove', $bot_user_id);
					}
					else
					{
						$sql = 'UPDATE ' . BOTS_TABLE . "
							SET bot_agent = '" .  $db->sql_escape($bot_agent) . "'
							WHERE user_id = $bot_user_id";
						_sql($sql, $errored, $error_ary);
					}
				}
			}

			// Disable receiving pms for bots
			$sql = 'SELECT user_id
				FROM ' . BOTS_TABLE;
			$result = $db->sql_query($sql);

			$bot_user_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$bot_user_ids[] = (int) $row['user_id'];
			}
			$db->sql_freeresult($result);

			if (!empty($bot_user_ids))
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_allow_pm = 0
					WHERE ' . $db->sql_in_set('user_id', $bot_user_ids);
				_sql($sql, $errored, $error_ary);
			}

			$no_updates = false;
		break;

		// Changes from 3.1.0-dev to 3.1.0-A1
		case '3.1.0-dev':

			// rename all module basenames to full classname
			$sql = 'SELECT module_id, module_basename, module_class
				FROM ' . MODULES_TABLE;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$module_id = (int) $row['module_id'];
				unset($row['module_id']);

				if (!empty($row['module_basename']) && !empty($row['module_class']))
				{
					// all the class names start with class name or with phpbb_ for auto loading
					if (strpos($row['module_basename'], $row['module_class'] . '_') !== 0 &&
						strpos($row['module_basename'], 'phpbb_') !== 0)
					{
						$row['module_basename'] = $row['module_class'] . '_' . $row['module_basename'];

						$sql_update = $db->sql_build_array('UPDATE', $row);

						$sql = 'UPDATE ' . MODULES_TABLE . '
							SET ' . $sql_update . '
							WHERE module_id = ' . $module_id;
						_sql($sql, $errored, $error_ary);
					}
				}
			}

			$db->sql_freeresult($result);

			if (substr($config['search_type'], 0, 6) !== 'phpbb_')
			{
				// try to guess the new auto loaded search class name
				// works for native and mysql fulltext
				set_config('search_type', 'phpbb_search_' . $config['search_type']);
			}

			if (!isset($config['fulltext_postgres_ts_name']))
			{
				set_config('fulltext_postgres_ts_name', 'simple');
			}

			if (!isset($config['fulltext_postgres_min_word_len']))
			{
				set_config('fulltext_postgres_min_word_len', 4);
			}

			if (!isset($config['fulltext_postgres_max_word_len']))
			{
				set_config('fulltext_postgres_max_word_len', 254);
			}

			if (!isset($config['fulltext_sphinx_stopwords']))
			{
				set_config('fulltext_sphinx_stopwords', 0);
			}

			if (!isset($config['fulltext_sphinx_indexer_mem_limit']))
			{
				set_config('fulltext_sphinx_indexer_mem_limit', 512);
			}

			if (!isset($config['load_jquery_cdn']))
			{
				set_config('load_jquery_cdn', 0);
				set_config('load_jquery_url', '//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js');
			}

			if (!isset($config['use_system_cron']))
			{
				set_config('use_system_cron', 0);
			}

			$sql = 'SELECT group_teampage
				FROM ' . GROUPS_TABLE . '
				WHERE group_teampage > 0';
			$result = $db->sql_query_limit($sql, 1);
			$added_groups_teampage = (bool) $db->sql_fetchfield('group_teampage');
			$db->sql_freeresult($result);

			if (!$added_groups_teampage)
			{
				$sql = 'UPDATE ' . GROUPS_TABLE . '
					SET group_teampage = 1
					WHERE group_type = ' . GROUP_SPECIAL . "
						AND group_name = 'ADMINISTRATORS'";
				_sql($sql, $errored, $error_ary);

				$sql = 'UPDATE ' . GROUPS_TABLE . '
					SET group_teampage = 2
					WHERE group_type = ' . GROUP_SPECIAL . "
						AND group_name = 'GLOBAL_MODERATORS'";
				_sql($sql, $errored, $error_ary);
			}

			if (!isset($config['legend_sort_groupname']))
			{
				set_config('legend_sort_groupname', '0');
				set_config('teampage_forums', '1');
			}

			$sql = 'SELECT group_legend
				FROM ' . GROUPS_TABLE . '
				WHERE group_teampage > 1';
			$result = $db->sql_query_limit($sql, 1);
			$updated_group_legend = (bool) $db->sql_fetchfield('group_teampage');
			$db->sql_freeresult($result);

			if (!$updated_group_legend)
			{
				$sql = 'SELECT group_id
					FROM ' . GROUPS_TABLE . '
					WHERE group_legend = 1
					ORDER BY group_name ASC';
				$result = $db->sql_query($sql);

				$next_legend = 1;
				while ($row = $db->sql_fetchrow($result))
				{
					$sql = 'UPDATE ' . GROUPS_TABLE . '
						SET group_legend = ' . $next_legend . '
						WHERE group_id = ' . (int) $row['group_id'];
					_sql($sql, $errored, $error_ary);

					$next_legend++;
				}
				$db->sql_freeresult($result);
				unset($next_legend);
			}

			// Rename styles module to Customise
			$sql = 'UPDATE ' . MODULES_TABLE . "
				SET module_langname = 'ACP_CAT_CUSTOMISE'
				WHERE module_langname = 'ACP_CAT_STYLES'";
			_sql($sql, $errored, $error_ary);

			// Install modules
			$modules_to_install = array(
				'position'	=> array(
					'base'		=> 'acp_groups',
					'class'		=> 'acp',
					'title'		=> 'ACP_GROUPS_POSITION',
					'auth'		=> 'acl_a_group',
					'cat'		=> 'ACP_GROUPS',
				),
				'manage'	=> array(
					'base'		=> 'acp_attachments',
					'class'		=> 'acp',
					'title'		=> 'ACP_MANAGE_ATTACHMENTS',
					'auth'		=> 'acl_a_attach',
					'cat'		=> 'ACP_ATTACHMENTS',
				),
				'install'	=> array(
					'base'		=> 'acp_styles',
					'class'		=> 'acp',
					'title'		=> 'ACP_STYLES_INSTALL',
					'auth'		=> 'acl_a_styles',
					'cat'		=> 'ACP_STYLE_MANAGEMENT',
				),
				'cache'	=> array(
					'base'		=> 'acp_styles',
					'class'		=> 'acp',
					'title'		=> 'ACP_STYLES_CACHE',
					'auth'		=> 'acl_a_styles',
					'cat'		=> 'ACP_STYLE_MANAGEMENT',
				),
				'autologin_keys'	=> array(
					'base'		=> 'ucp_profile',
					'class'		=> 'ucp',
					'title'		=> 'UCP_PROFILE_AUTOLOGIN_KEYS',
					'auth'		=> '',
					'cat'		=> 'UCP_PROFILE',
				),
				'notification_options'	=> array(
					'base'		=> 'ucp_notifications',
					'class'		=> 'ucp',
					'title'		=> 'UCP_NOTIFICATION_OPTIONS',
					'auth'		=> '',
					'cat'		=> 'UCP_PREFS',
				),
				'notification_list'	=> array(
					'base'		=> 'ucp_notifications',
					'class'		=> 'ucp',
					'title'		=> 'UCP_NOTIFICATION_LIST',
					'auth'		=> '',
					'cat'		=> 'UCP_MAIN',
				),
				// To add a category, the mode and basename must be empty
				// The mode is taken from the array key
				'' => array(
					'base'		=> '',
					'class'		=> 'acp',
					'title'		=> 'ACP_EXTENSION_MANAGEMENT',
					'auth'		=> 'acl_a_extensions',
					'cat'		=> 'ACP_CAT_CUSTOMISE',
				),
				'extensions'    => array(
					'base'		=> 'acp_extensions',
					'class'		=> 'acp',
					'title'		=> 'ACP_EXTENSIONS',
					'auth'		=> 'acl_a_extensions',
					'cat'		=> 'ACP_EXTENSION_MANAGEMENT',
				),
			);

			_add_modules($modules_to_install);

			// We need a separate array for the new language sub heading
			// because it requires another empty key
			$modules_to_install = array(
				'' => array(
					'base'	=> '',
					'class'	=> 'acp',
					'title'	=> 'ACP_LANGUAGE',
					'auth'	=> 'acl_a_language',
					'cat'	=> 'ACP_CAT_CUSTOMISE',
				),
			);

			_add_modules($modules_to_install);

			// Move language management to new location in the Customise tab
			// First get language module id
			$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
				WHERE module_basename = 'acp_language'";
			$result = $db->sql_query($sql);
			$language_module_id = $db->sql_fetchfield('module_id');
			$db->sql_freeresult($result);
			// Next get language management module id of the one just created
			$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
				WHERE module_langname = 'ACP_LANGUAGE'";
			$result = $db->sql_query($sql);
			$language_management_module_id = $db->sql_fetchfield('module_id');
			$db->sql_freeresult($result);

			if (!class_exists('acp_modules'))
			{
				include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);
			}
			// acp_modules calls adm_back_link, which is undefined at this point
			if (!function_exists('adm_back_link'))
			{
				include($phpbb_root_path . 'includes/functions_acp.' . $phpEx);
			}
			$module_manager = new acp_modules();
			$module_manager->module_class = 'acp';
			$module_manager->move_module($language_module_id, $language_management_module_id);

			$sql = 'DELETE FROM ' . MODULES_TABLE . "
				WHERE (module_basename = 'styles' OR module_basename = 'acp_styles') AND (module_mode = 'imageset' OR module_mode = 'theme' OR module_mode = 'template')";
			_sql($sql, $errored, $error_ary);

			// Localise Global Announcements
			$sql = 'SELECT topic_id, topic_approved, (topic_replies + 1) AS topic_posts, topic_last_post_id, topic_last_post_subject, topic_last_post_time, topic_last_poster_id, topic_last_poster_name, topic_last_poster_colour
				FROM ' . TOPICS_TABLE . '
				WHERE forum_id = 0
					AND topic_type = ' . POST_GLOBAL;
			$result = $db->sql_query($sql);

			$global_announcements = $update_lastpost_data = array();
			$update_lastpost_data['forum_last_post_time'] = 0;
			$update_forum_data = array(
				'forum_posts'		=> 0,
				'forum_topics'		=> 0,
				'forum_topics_real'	=> 0,
			);

			while ($row = $db->sql_fetchrow($result))
			{
				$global_announcements[] = (int) $row['topic_id'];

				$update_forum_data['forum_posts'] += (int) $row['topic_posts'];
				$update_forum_data['forum_topics_real']++;
				if ($row['topic_approved'])
				{
					$update_forum_data['forum_topics']++;
				}

				if ($update_lastpost_data['forum_last_post_time'] < $row['topic_last_post_time'])
				{
					$update_lastpost_data = array(
						'forum_last_post_id'		=> (int) $row['topic_last_post_id'],
						'forum_last_post_subject'	=> $row['topic_last_post_subject'],
						'forum_last_post_time'		=> (int) $row['topic_last_post_time'],
						'forum_last_poster_id'		=> (int) $row['topic_last_poster_id'],
						'forum_last_poster_name'	=> $row['topic_last_poster_name'],
						'forum_last_poster_colour'	=> $row['topic_last_poster_colour'],
					);
				}
			}
			$db->sql_freeresult($result);

			if (!empty($global_announcements))
			{
				// Update the post/topic-count for the forum and the last-post if needed
				$ga_forum_id = request_var('ga_forum_id', 0);

				$sql = 'SELECT forum_last_post_time
					FROM ' . FORUMS_TABLE . '
					WHERE forum_id = ' . $ga_forum_id;
				$result = $db->sql_query($sql);
				$lastpost = (int) $db->sql_fetchfield('forum_last_post_time');
				$db->sql_freeresult($result);

				$sql_update = 'forum_posts = forum_posts + ' . $update_forum_data['forum_posts'] . ', ';
				$sql_update .= 'forum_topics_real = forum_topics_real + ' . $update_forum_data['forum_topics_real'] . ', ';
				$sql_update .= 'forum_topics = forum_topics + ' . $update_forum_data['forum_topics'];
				if ($lastpost < $update_lastpost_data['forum_last_post_time'])
				{
					$sql_update .= ', ' . $db->sql_build_array('UPDATE', $update_lastpost_data);
				}

				$sql = 'UPDATE ' . FORUMS_TABLE . '
					SET ' . $sql_update . '
					WHERE forum_id = ' . $ga_forum_id;
				_sql($sql, $errored, $error_ary);

				// Update some forum_ids
				$table_ary = array(TOPICS_TABLE, POSTS_TABLE, LOG_TABLE, DRAFTS_TABLE, TOPICS_TRACK_TABLE);
				foreach ($table_ary as $table)
				{
					$sql = "UPDATE $table
						SET forum_id = $ga_forum_id
						WHERE " . $db->sql_in_set('topic_id', $global_announcements);
					_sql($sql, $errored, $error_ary);
				}
				unset($table_ary);
			}

			// Allow custom profile fields in pm templates
			if (!isset($config['load_cpf_pm']))
			{
				set_config('load_cpf_pm', '0');
			}

			if (!isset($config['teampage_memberships']))
			{
				set_config('teampage_memberships', '1');
			}

			// Check if styles table was already updated
			if ($db_tools->sql_table_exists(STYLES_THEME_TABLE))
			{
				// Get list of valid 3.1 styles
				$available_styles = array('prosilver');

				$iterator = new DirectoryIterator($phpbb_root_path . 'styles');
				$skip_dirs = array('.', '..', 'prosilver');
				foreach ($iterator as $fileinfo)
				{
					if ($fileinfo->isDir() && !in_array($fileinfo->getFilename(), $skip_dirs) && file_exists($fileinfo->getPathname() . '/style.cfg'))
					{
						$style_cfg = parse_cfg_file($fileinfo->getPathname() . '/style.cfg');
						if (isset($style_cfg['phpbb_version']) && version_compare($style_cfg['phpbb_version'], '3.1.0-dev', '>='))
						{
							// 3.1 style
							$available_styles[] = $fileinfo->getFilename();
						}
					}
				}

				// Get all installed styles
				if ($db_tools->sql_table_exists(STYLES_IMAGESET_TABLE))
				{
					$sql = 'SELECT s.style_id, t.template_path, t.template_id, t.bbcode_bitfield, t.template_inherits_id, t.template_inherit_path, c.theme_path, c.theme_id, i.imageset_path
						FROM ' . STYLES_TABLE . ' s, ' . STYLES_TEMPLATE_TABLE . ' t, ' . STYLES_THEME_TABLE . ' c, ' . STYLES_IMAGESET_TABLE . " i
						WHERE t.template_id = s.template_id
							AND c.theme_id = s.theme_id
							AND i.imageset_id = s.imageset_id";
				}
				else
				{
					$sql = 'SELECT s.style_id, t.template_path, t.template_id, t.bbcode_bitfield, t.template_inherits_id, t.template_inherit_path, c.theme_path, c.theme_id
						FROM ' . STYLES_TABLE . ' s, ' . STYLES_TEMPLATE_TABLE . ' t, ' . STYLES_THEME_TABLE . " c
						WHERE t.template_id = s.template_id
							AND c.theme_id = s.theme_id";
				}
				$result = $db->sql_query($sql);

				$styles = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$styles[] = $row;
				}
				$db->sql_freeresult($result);

				// Decide which styles to keep, all others will be deleted
				$valid_styles = array();
				foreach ($styles as $style_row)
				{
					if (
						// Delete styles with parent style (not supported yet)
						$style_row['template_inherits_id'] == 0 &&
						// Check if components match
						$style_row['template_path'] == $style_row['theme_path'] && (!isset($style_row['imageset_path']) || $style_row['template_path'] == $style_row['imageset_path']) &&
						// Check if components are valid
						in_array($style_row['template_path'], $available_styles)
						)
					{
						// Valid style. Keep it
						$sql_ary = array(
							'style_path'	=> $style_row['template_path'],
							'bbcode_bitfield'	=> $style_row['bbcode_bitfield'],
							'style_parent_id'	=> 0,
							'style_parent_tree'	=> '',
						);
						_sql('UPDATE ' . STYLES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE style_id = ' . $style_row['style_id'], $errored, $error_ary);
						$valid_styles[] = (int) $style_row['style_id'];
					}
				}

				// Remove old styles tables
				$changes = array(
					'drop_columns'	=> array(
						STYLES_TABLE		=> array(
							'imageset_id',
							'template_id',
							'theme_id',
						),
					),

					'drop_tables'	=> array(
						STYLES_IMAGESET_TABLE,
						STYLES_IMAGESET_DATA_TABLE,
						STYLES_TEMPLATE_TABLE,
						STYLES_TEMPLATE_DATA_TABLE,
						STYLES_THEME_TABLE,
					)
				);
				$statements = $db_tools->perform_schema_changes($changes);

				foreach ($statements as $sql)
				{
					_sql($sql, $errored, $error_ary);
				}

				// Remove old entries from styles table
				if (!sizeof($valid_styles))
				{
					// No valid styles: remove everything and add prosilver
					_sql('DELETE FROM ' . STYLES_TABLE, $errored, $error_ary);

					$sql = 'INSERT INTO ' . STYLES_TABLE . " (style_name, style_copyright, style_active, style_path, bbcode_bitfield, style_parent_id, style_parent_tree) VALUES ('prosilver', '&copy; phpBB Group', 1, 'prosilver', 'kNg=', 0, '')";
					_sql($sql, $errored, $error_ary);

					$sql = 'SELECT style_id
						FROM ' . $table . "
						WHERE style_name = 'prosilver'";
					$result = _sql($sql, $errored, $error_ary);
					$default_style = $db->sql_fetchfield($result);
					$db->sql_freeresult($result);

					set_config('default_style', $default_style);

					$sql = 'UPDATE ' . USERS_TABLE . ' SET user_style = 0';
					_sql($sql, $errored, $error_ary);
				}
				else
				{
					// There are valid styles in styles table. Remove styles that are outdated
					_sql('DELETE FROM ' . STYLES_TABLE . ' WHERE ' . $db->sql_in_set('style_id', $valid_styles, true), $errored, $error_ary);

					// Change default style
					if (!in_array($config['default_style'], $valid_styles))
					{
						set_config('default_style', $valid_styles[0]);
					}

					// Reset styles for users
					_sql('UPDATE ' . USERS_TABLE . ' SET user_style = 0 WHERE ' . $db->sql_in_set('user_style', $valid_styles, true), $errored, $error_ary);
				}
			}

			// Create config value for displaying last subject on forum list
			if (!isset($config['display_last_subject']))
			{
				$config->set('display_last_subject', '1');
			}

			if (!isset($config['assets_version']))
			{
				$config->set('assets_version', '1');
			}

			// If the column exists, we did not yet update the users timezone
			if ($db_tools->sql_column_exists(USERS_TABLE, 'user_dst'))
			{
				// Update user timezones
				$sql = 'SELECT user_dst, user_timezone
					FROM ' . USERS_TABLE . '
					GROUP BY user_timezone, user_dst';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$sql = 'UPDATE ' . USERS_TABLE . "
						SET user_timezone = '" . $db->sql_escape($update_helpers->convert_phpbb30_timezone($row['user_timezone'], $row['user_dst'])) . "'
						WHERE user_timezone = '" . $db->sql_escape($row['user_timezone']) . "'
							AND user_dst = " . (int) $row['user_dst'];
					_sql($sql, $errored, $error_ary);
				}
				$db->sql_freeresult($result);

				// Update board default timezone
				set_config('board_timezone', $update_helpers->convert_phpbb30_timezone($config['board_timezone'], $config['board_dst']));

				// After we have calculated the timezones we can delete user_dst column from user table.
				$statements = $db_tools->sql_column_remove(USERS_TABLE, 'user_dst');
				foreach ($statements as $sql)
				{
					_sql($sql, $errored, $error_ary);
				}
			}

			if (!isset($config['site_home_url']))
			{
				$config->set('site_home_url', '');
				$config->set('site_home_text', '');
			}

			if (!isset($config['load_notifications']))
			{
				$config->set('load_notifications', 1);

				// Convert notifications
				$convert_notifications = array(
					array(
						'check'			=> ($config['allow_topic_notify']),
						'item_type'		=> 'post',
					),
					array(
						'check'			=> ($config['allow_forum_notify']),
						'item_type'		=> 'topic',
					),
					array(
						'check'			=> ($config['allow_bookmarks']),
						'item_type'		=> 'bookmark',
					),
					array(
						'check'			=> ($config['allow_privmsg']),
						'item_type'		=> 'pm',
					),
				);

				foreach ($convert_notifications as $convert_data)
				{
					if ($convert_data['check'])
					{
						$sql = 'SELECT user_id, user_notify_type
							FROM ' . USERS_TABLE . '
								WHERE user_notify = 1';
						$result = $db->sql_query($sql);
						while ($row = $db->sql_fetchrow($result))
						{
							_sql('INSERT INTO ' . $table_prefix . 'user_notifications ' . $db->sql_build_array('INSERT', array(
								'item_type'		=> $convert_data['item_type'],
								'item_id'		=> 0,
								'user_id'		=> $row['user_id'],
								'method'		=> '',
							)), $errored, $error_ary);

							if ($row['user_notify_type'] == NOTIFY_EMAIL || $row['user_notify_type'] == NOTIFY_BOTH)
							{
								_sql('INSERT INTO ' . $table_prefix . 'user_notifications ' . $db->sql_build_array('INSERT', array(
									'item_type'		=> $convert_data['item_type'],
									'item_id'		=> 0,
									'user_id'		=> $row['user_id'],
									'method'		=> 'email',
								)), $errored, $error_ary);
							}

							if ($row['user_notify_type'] == NOTIFY_IM || $row['user_notify_type'] == NOTIFY_BOTH)
							{
								_sql('INSERT INTO ' . $table_prefix . 'user_notifications ' . $db->sql_build_array('INSERT', array(
									'item_type'		=> $convert_data['item_type'],
									'item_id'		=> 0,
									'user_id'		=> $row['user_id'],
									'method'		=> 'jabber',
								)), $errored, $error_ary);
							}
						}
						$db->sql_freeresult($result);
					}
				}
			}

			// PHPBB3-10601: Make inbox default. Add basename to ucp's pm category

			// Get the category wanted while checking, at the same time, if this has already been applied
			$sql = 'SELECT module_id, module_basename
					FROM ' . MODULES_TABLE . "
					WHERE module_basename <> 'ucp_pm' AND
						module_langname='UCP_PM'
						";
			$result = $db->sql_query_limit($sql, 1);

			if ($row = $db->sql_fetchrow($result))
			{
				// This update is still not applied. Applying it

				$sql = 'UPDATE ' . MODULES_TABLE . "
					SET module_basename = 'ucp_pm'
					WHERE  module_id = " . (int) $row['module_id'];

				_sql($sql, $errored, $error_ary);
			}
			$db->sql_freeresult($result);
			

			// Add new permissions
			include_once($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
			$auth_admin = new auth_admin();

			_add_permission($auth_admin, $db, 'u_chgprofileinfo', true, 'u_sig');
			_add_permission($auth_admin, $db, 'a_extensions', true, 'a_styles');

			// Update the auth setting for the module
			$sql = 'UPDATE ' . MODULES_TABLE . "
				SET module_auth = 'acl_u_chgprofileinfo'
				WHERE module_class = 'ucp'
					AND module_basename = 'ucp_profile'
					AND module_mode = 'profile_info'";
			_sql($sql, $errored, $error_ary);

			$no_updates = false;
		break;
	}
}
