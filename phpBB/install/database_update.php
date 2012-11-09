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
					'reported_post_text'		=> array('MTEXT_UNI', ''),
					'reported_post_uid'			=> array('VCHAR:8', ''),
					'reported_post_bitfield'	=> array('VCHAR:255', ''),
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
