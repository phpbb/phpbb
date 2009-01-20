<?php
/**
*
* @package install
* @version $Id$
* @copyright (c) 2006 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

$updates_to_version = '3.1.0';

// Return if we "just include it" to find out for which version the database update is responsuble for
if (defined('IN_PHPBB') && defined('IN_INSTALL'))
{
	return;
}

/**
*/
define('IN_PHPBB', true);
define('IN_INSTALL', true);

if (!defined('PHPBB_ROOT_PATH')) define('PHPBB_ROOT_PATH', './../');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
include PHPBB_ROOT_PATH . 'common.' . PHP_EXT;

@set_time_limit(0);

// Start session management
phpbb::$user->session_begin();
phpbb::$acl->init(phpbb::$user->data);
phpbb::$user->setup('install');

if (!phpbb::$user->is_registered)
{
	login_box();
}

if (!phpbb::$acl->acl_get('a_board'))
{
	trigger_error('NO_AUTH');
}

include PHPBB_ROOT_PATH . 'includes/db/db_tools.' . PHP_EXT;

$db_tools = new phpbb_db_tools(phpbb::$db, true);

// Define some variables for the database update
$inline_update = (request_var('type', 0)) ? true : false;

// Only an example, but also commented out
$database_update_info = array(

	// No changes from 3.0.3-RC1 to 3.0.3
	'3.0.3-RC1'		=> array(),
);

$error_ary = array();
$errored = false;

header('Content-type: text/html; charset=UTF-8');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo phpbb::$user->lang['DIRECTION']; ?>" lang="<?php echo phpbb::$user->lang['USER_LANG']; ?>" xml:lang="<?php echo phpbb::$user->lang['USER_LANG']; ?>">
<head>

<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="content-language" content="<?php echo phpbb::$user->lang['USER_LANG']; ?>" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="imagetoolbar" content="no" />

<title><?php echo phpbb::$user->lang['UPDATING_TO_LATEST_STABLE']; ?></title>

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

	<h1><?php echo phpbb::$user->lang['UPDATING_TO_LATEST_STABLE']; ?></h1>

	<br />

	<p><?php echo phpbb::$user->lang['DATABASE_TYPE']; ?> :: <strong><?php echo phpbb::$db->sql_layer; ?></strong><br />
<?php

// To let set_config() calls succeed, we need to make the config array available globally
phpbb::$acm->destroy('#config');
$config = phpbb_cache::obtain_config();

echo phpbb::$user->lang['PREVIOUS_VERSION'] . ' :: <strong>' . phpbb::$config['version'] . '</strong><br />';
echo phpbb::$user->lang['UPDATED_VERSION'] . ' :: <strong>' . $updates_to_version . '</strong></p>';

$current_version = str_replace('rc', 'RC', strtolower(phpbb::$config['version']));
$latest_version = str_replace('rc', 'RC', strtolower($updates_to_version));
$orig_version = phpbb::$config['version'];

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
	phpbb::$db->sql_query('DELETE FROM ' . CONFIG_TABLE . " WHERE config_name = 'version_update_from'");
}

// Schema updates
?>
	<br /><br />

	<h1><?php echo phpbb::$user->lang['UPDATE_DATABASE_SCHEMA']; ?></h1>

	<br />
	<p><?php echo phpbb::$user->lang['PROGRESS']; ?> :: <strong>

<?php

flush();

// We go through the schema changes from the lowest to the highest version
// We try to also include versions 'in-between'...
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

	// Get statements for schema updates
	$statements = $db_tools->sql_schema_changes($schema_changes);

	if (sizeof($statements))
	{
		$no_updates = false;

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
<h1><?php echo phpbb::$user->lang['UPDATING_DATA']; ?></h1>
<br />
<p><?php echo phpbb::$user->lang['PROGRESS']; ?> :: <strong>

<?php

flush();

$no_updates = true;

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
<h1><?php echo phpbb::$user->lang['UPDATE_VERSION_OPTIMIZE']; ?></h1>
<br />
<p><?php echo phpbb::$user->lang['PROGRESS']; ?> :: <strong>

<?php

flush();

// update the version
$sql = "UPDATE " . CONFIG_TABLE . "
	SET config_value = '$updates_to_version'
	WHERE config_name = 'version'";
_sql($sql, $errored, $error_ary);

// Reset permissions
$sql = 'UPDATE ' . USERS_TABLE . "
	SET user_permissions = '',
		user_perm_from = 0";
_sql($sql, $errored, $error_ary);

/* Optimize/vacuum analyze the tables where appropriate
// this should be done for each version in future along with
// the version number update
switch ($db->dbms_type)
{
	case 'mysql':
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
<h1><?php echo phpbb::$user->lang['UPDATE_COMPLETED']; ?></h1>

<br />

<?php

if (!$inline_update)
{
	// Purge the cache...
	phpbb::$acm->purge();
?>

	<p style="color:red"><?php echo phpbb::$user->lang['UPDATE_FILES_NOTICE']; ?></p>

	<p><?php echo phpbb::$user->lang['COMPLETE_LOGIN_TO_BOARD']; ?></p>

<?php
}
else
{
?>

	<p><?php echo ((isset(phpbb::$user->lang['INLINE_UPDATE_SUCCESSFUL'])) ? phpbb::$user->lang['INLINE_UPDATE_SUCCESSFUL'] : 'The database update was successful. Now you need to continue the update process.'); ?></p>

	<p><a href="<?php echo append_sid('install/index', "mode=update&amp;sub=file_check&amp;lang=$language"); ?>" class="button1"><?php echo (isset(phpbb::$user->lang['CONTINUE_UPDATE_NOW'])) ? phpbb::$user->lang['CONTINUE_UPDATE_NOW'] : 'Continue the update process now'; ?></a></p>

<?php
}

// Add database update to log
add_log('admin', 'LOG_UPDATE_DATABASE', $orig_version, $updates_to_version);

// Now we purge the session table as well as all cache files
phpbb::$acm->purge();

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

garbage_collection();

if (function_exists('exit_handler'))
{
	exit_handler();
}

/**
* Function where all data changes are executed
*/
function change_database_data($version)
{
	global $errored, $error_ary;

	switch ($version)
	{
		default:
		break;
	}
}

/**
* Function for triggering an sql statement
*/
function _sql($sql, &$errored, &$error_ary, $echo_dot = true)
{
	if (phpbb::$base_config['debug_extra'])
	{
		echo "<br />\n{$sql}\n<br />";
	}

	phpbb::$db->sql_return_on_error(true);

	$result = phpbb::$db->sql_query($sql);
	if (phpbb::$db->sql_error_triggered)
	{
		$errored = true;
		$error_ary['sql'][] = phpbb::$db->sql_error_sql;
		$error_ary['error_code'][] = phpbb::$db->_sql_error();
	}

	phpbb::$db->sql_return_on_error(false);

	if ($echo_dot)
	{
		echo ". \n";
		flush();
	}

	return $result;
}

function _write_result($no_updates, $errored, $error_ary)
{
	if ($no_updates)
	{
		echo ' ' . phpbb::$user->lang['NO_UPDATES_REQUIRED'] . '</strong></p>';
	}
	else
	{
		echo ' <span class="success">' . phpbb::$user->lang['DONE'] . '</span></strong><br />' . phpbb::$user->lang['RESULT'] . ' :: ';

		if ($errored)
		{
			echo ' <strong>' . phpbb::$user->lang['SOME_QUERIES_FAILED'] . '</strong> <ul>';

			for ($i = 0; $i < sizeof($error_ary['sql']); $i++)
			{
				echo '<li>' . phpbb::$user->lang['ERROR'] . ' :: <strong>' . htmlspecialchars($error_ary['error_code'][$i]['message']) . '</strong><br />';
				echo phpbb::$user->lang['SQL'] . ' :: <strong>' . htmlspecialchars($error_ary['sql'][$i]) . '</strong><br /><br /></li>';
			}

			echo '</ul> <br /><br />' . phpbb::$user->lang['SQL_FAILURE_EXPLAIN'] . '</p>';
		}
		else
		{
			echo '<strong>' . phpbb::$user->lang['NO_ERRORS'] . '</strong></p>';
		}
	}
}

?>