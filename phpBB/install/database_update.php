<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

$update_start_time = time();

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
* @ignore
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

function phpbb_end_update($cache, $config)
{
	$cache->purge();

	$config->increment('assets_version', 1);

?>
								</p>
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

<?php

	garbage_collection();
	exit_handler();
}

phpbb_require_updated('includes/startup.' . $phpEx);

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
require($phpbb_root_path . 'includes/functions_content.' . $phpEx);
require($phpbb_root_path . 'includes/functions_container.' . $phpEx);

require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

// Set PHP error handler to ours
set_error_handler(defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler');

// Setup class loader first
$phpbb_class_loader = new phpbb_class_loader('phpbb_', "{$phpbb_root_path}includes/", $phpEx);
$phpbb_class_loader->register();

// Set up container (must be done here because extensions table may not exist)
$container_extensions = array(
	new phpbb_di_extension_config($phpbb_root_path . 'config.' . $phpEx),
	new phpbb_di_extension_core($phpbb_root_path),
);
$container_passes = array(
	new phpbb_di_pass_collection_pass(),
	//new phpbb_di_pass_kernel_pass(),
);
$phpbb_container = phpbb_create_container($container_extensions, $phpbb_root_path, $phpEx);

// Compile the container
foreach ($container_passes as $pass)
{
	$phpbb_container->addCompilerPass($pass);
}
$phpbb_container->compile();

// set up caching
$cache = $phpbb_container->get('cache');

// Instantiate some basic classes
$phpbb_dispatcher = $phpbb_container->get('dispatcher');
$request	= $phpbb_container->get('request');
$user		= $phpbb_container->get('user');
$auth		= $phpbb_container->get('auth');
$db			= $phpbb_container->get('dbal.conn');
$phpbb_log	= $phpbb_container->get('log');

// make sure request_var uses this request instance
request_var('', 0, false, false, $request); // "dependency injection" for a function

// Grab global variables, re-cache if necessary
$config = $phpbb_container->get('config');
set_config(null, null, null, $config);
set_config_count(null, null, null, $config);
$orig_version = $config['version'];

$user->add_lang(array('common', 'acp/common', 'install', 'migrator'));

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

header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html dir="<?php echo $user->lang['DIRECTION']; ?>" lang="<?php echo $user->lang['USER_LANG']; ?>">
<head>
<meta charset="utf-8">

<title><?php echo $user->lang['UPDATING_TO_LATEST_STABLE']; ?></title>

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

								<h1><?php echo $user->lang['UPDATING_TO_LATEST_STABLE']; ?></h1>

								<br />

								<p><?php echo $user->lang['DATABASE_TYPE']; ?> :: <strong><?php echo $db->sql_layer; ?></strong><br />
								<?php echo $user->lang['PREVIOUS_VERSION']; ?> :: <strong><?php echo $config['version']; ?></strong><br />

<?php

/**
* @todo firebird/mysql update?
*/

// End startup code

// Make sure migrations have been installed.
$db_tools = $phpbb_container->get('dbal.tools');
if (!$db_tools->sql_table_exists($table_prefix . 'migrations'))
{
	$db_tools->sql_create_table($table_prefix . 'migrations', array(
		'COLUMNS'		=> array(
			'migration_name'			=> array('VCHAR', ''),
			'migration_depends_on'		=> array('TEXT', ''),
			'migration_schema_done'		=> array('BOOL', 0),
			'migration_data_done'		=> array('BOOL', 0),
			'migration_data_state'		=> array('TEXT', ''),
			'migration_start_time'		=> array('TIMESTAMP', 0),
			'migration_end_time'		=> array('TIMESTAMP', 0),
		),
		'PRIMARY_KEY'	=> 'migration_name',
	));
}

$migrator = $phpbb_container->get('migrator');
$phpbb_extension_manager = $phpbb_container->get('ext.manager');
$finder = $phpbb_extension_manager->get_finder();

$migrations = $finder
	->core_path('includes/db/migration/data/')
	->get_classes();
$migrator->set_migrations($migrations);

// What is a safe limit of execution time? Half the max execution time should be safe.
$safe_time_limit = (ini_get('max_execution_time') / 2);

while (!$migrator->finished())
{
	try
	{
		$migrator->update();
	}
	catch (phpbb_db_migration_exception $e)
	{
		echo $e->getLocalisedMessage($user);

		phpbb_end_update($cache, $config);
	}

	$state = array_merge(array(
			'migration_schema_done' => false,
			'migration_data_done'	=> false,
		),
		$migrator->last_run_migration['state']
	);

	if (isset($migrator->last_run_migration['effectively_installed']) && $migrator->last_run_migration['effectively_installed'])
	{
		echo $user->lang('MIGRATION_EFFECTIVELY_INSTALLED', $migrator->last_run_migration['name']) . '<br />';
	}
	else
	{
		if ($state['migration_data_done'])
		{
			echo $user->lang('MIGRATION_DATA_DONE', $migrator->last_run_migration['name']) . '<br />';
		}
		else if ($state['migration_schema_done'])
		{
			echo $user->lang('MIGRATION_SCHEMA_DONE', $migrator->last_run_migration['name']) . '<br />';
		}
	}

	// Are we approaching the time limit? If so we want to pause the update and continue after refreshing
	if ((time() - $update_start_time) >= $safe_time_limit)
	{
		echo $user->lang['DATABASE_UPDATE_NOT_COMPLETED'] . '<br />';
		echo '<a href="' . append_sid($phpbb_root_path . 'install/database_update.' . $phpEx, 'type=' . $request->variable('type', 0) . '&amp;language=' . $user->lang['USER_LANG']) . '">' . $user->lang['DATABASE_UPDATE_CONTINUE'] . '</a>';

		phpbb_end_update($cache, $config);
	}
}

if ($orig_version != $config['version'])
{
	add_log('admin', 'LOG_UPDATE_DATABASE', $orig_version, $config['version']);
}

echo $user->lang['DATABASE_UPDATE_COMPLETE'] . '<br />';

if ($request->variable('type', 0))
{
	echo $user->lang['INLINE_UPDATE_SUCCESSFUL'] . '<br /><br />';
	echo '<a href="' . append_sid($phpbb_root_path . 'install/index.' . $phpEx, 'mode=update&amp;sub=file_check&amp;language=' . $user->lang['USER_LANG']) . '" class="button1">' . $user->lang['CONTINUE_UPDATE_NOW'] . '</a>';
}
else
{
	echo '<div class="errorbox">' . $user->lang['UPDATE_FILES_NOTICE'] . '</div>';
	echo $user->lang['COMPLETE_LOGIN_TO_BOARD'];
}

phpbb_end_update($cache, $config);
