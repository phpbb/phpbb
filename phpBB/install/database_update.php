<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

$update_start_time = time();

/**
* @ignore
*/
define('IN_PHPBB', true);
define('IN_INSTALL', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

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
			<div class="copyright">
				Powered by <a href="https://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Limited
			</div>
		</div>
	</div>
</body>
</html>

<?php

	garbage_collection();
	exit_handler();
}

require($phpbb_root_path . 'includes/startup.' . $phpEx);
require($phpbb_root_path . 'phpbb/class_loader.' . $phpEx);

$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}phpbb/", $phpEx);
$phpbb_class_loader->register();

$phpbb_config_php_file = new \phpbb\config_php_file($phpbb_root_path, $phpEx);
extract($phpbb_config_php_file->get_all());

if (!defined('PHPBB_INSTALLED') || empty($dbms) || empty($acm_type))
{
	die("Please read: <a href='../docs/INSTALL.html'>INSTALL.html</a> before attempting to update.");
}

// In case $phpbb_adm_relative_path is not set (in case of an update), use the default.
$phpbb_adm_relative_path = (isset($phpbb_adm_relative_path)) ? $phpbb_adm_relative_path : 'adm/';
$phpbb_admin_path = (defined('PHPBB_ADMIN_PATH')) ? PHPBB_ADMIN_PATH : $phpbb_root_path . $phpbb_adm_relative_path;

// Include files
require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/functions_content.' . $phpEx);

require($phpbb_root_path . 'includes/constants.' . $phpEx);
include($phpbb_root_path . 'includes/utf/utf_normalizer.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

// Set PHP error handler to ours
set_error_handler(defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler');

// Set up container (must be done here because extensions table may not exist)
$phpbb_container_builder = new \phpbb\di\container_builder($phpbb_config_php_file, $phpbb_root_path, $phpEx);
$phpbb_container_builder->set_use_extensions(false);
$phpbb_container_builder->set_use_kernel_pass(false);
$phpbb_container_builder->set_dump_container(false);
$phpbb_container = $phpbb_container_builder->get_container();

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

if (!isset($config['version_update_from']))
{
	$config->set('version_update_from', $config['version']);
}

$orig_version = $config['version_update_from'];

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

								<p><?php echo $user->lang['DATABASE_TYPE']; ?> :: <strong><?php echo $db->get_sql_layer(); ?></strong><br />
								<?php echo $user->lang['PREVIOUS_VERSION']; ?> :: <strong><?php echo $config['version']; ?></strong><br />

<?php

define('IN_DB_UPDATE', true);

/**
* @todo mysql update?
*/

// End startup code

$migrator = $phpbb_container->get('migrator');
$migrator->set_output_handler(new \phpbb\db\log_wrapper_migrator_output_handler($user, new \phpbb\db\html_migrator_output_handler($user), $phpbb_root_path . 'store/migrations_' . time() . '.log'));

$migrator->create_migrations_table();

$phpbb_extension_manager = $phpbb_container->get('ext.manager');

$migrations = $phpbb_extension_manager
	->get_finder()
	->core_path('phpbb/db/migration/data/')
	->extension_directory('/migrations')
	->get_classes();

$migrator->set_migrations($migrations);

// What is a safe limit of execution time? Half the max execution time should be safe.
//  No more than 15 seconds so the user isn't sitting and waiting for a very long time
$phpbb_ini = new \phpbb\php\ini();
$safe_time_limit = min(15, ($phpbb_ini->get_int('max_execution_time') / 2));

// While we're going to try limit this to half the max execution time,
//  we want to try and take additional measures to prevent hitting the
//  max execution time (if, say, one migration step takes much longer
//  than the max execution time)
@set_time_limit(0);

while (!$migrator->finished())
{
	try
	{
		$migrator->update();
	}
	catch (\phpbb\db\migration\exception $e)
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

	// Are we approaching the time limit? If so we want to pause the update and continue after refreshing
	if ((time() - $update_start_time) >= $safe_time_limit)
	{
		echo '<br />' . $user->lang['DATABASE_UPDATE_NOT_COMPLETED'] . '<br /><br />';
		echo '<a href="' . append_sid($phpbb_root_path . 'install/database_update.' . $phpEx, 'type=' . $request->variable('type', 0) . '&amp;language=' . $request->variable('language', 'en')) . '" class="button1">' . $user->lang['DATABASE_UPDATE_CONTINUE'] . '</a>';

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
	echo '<a href="' . append_sid($phpbb_root_path . 'install/index.' . $phpEx, 'mode=update&amp;sub=update_db&amp;language=' . $request->variable('language', 'en')) . '" class="button1">' . $user->lang['CONTINUE_UPDATE_NOW'] . '</a>';
}
else
{
	echo '<div class="errorbox">' . $user->lang['UPDATE_FILES_NOTICE'] . '</div>';
	echo $user->lang['COMPLETE_LOGIN_TO_BOARD'];
}

$config->delete('version_update_from');

phpbb_end_update($cache, $config);
