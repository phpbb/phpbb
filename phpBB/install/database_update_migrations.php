<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
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
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

require($phpbb_root_path . 'includes/startup.' . $phpEx);

if (file_exists($phpbb_root_path . 'config.' . $phpEx))
{
	require($phpbb_root_path . 'config.' . $phpEx);
}

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
$phpbb_class_loader = new phpbb_class_loader('phpbb_', "{$phpbb_root_path}includes/", ".$phpEx");
$phpbb_class_loader->register();

// Set up container
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

// make sure request_var uses this request instance
request_var('', 0, false, false, $request); // "dependency injection" for a function

// Grab global variables, re-cache if necessary
$config = $phpbb_container->get('config');
set_config(null, null, null, $config);
set_config_count(null, null, null, $config);

// End startup code

$db_tools = $phpbb_container->get('dbal.tools');
if (!$db_tools->sql_table_exists(MIGRATIONS_TABLE))
{
	$db_tools->sql_create_table(MIGRATIONS_TABLE, array(
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
$migrator->load_migrations($phpbb_root_path . 'includes/db/migration/data/');

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
		echo $e;

		garbage_collection();
		exit_handler();
	}

	echo $migrator->last_run_migration['name'] . '<br />';

	// Are we approaching the time limit? If so we want to pause the update and continue after refreshing
	if ((time() - $update_start_time) >= $safe_time_limit)
	{
		//echo '<meta http-equiv="refresh" content="0;url=' . str_replace('&', '&amp;', append_sid($phpbb_root_path . 'test.' . $phpEx)) . '" />';
		echo 'Update not yet completed.<br />';
		echo '<a href="' . append_sid($phpbb_root_path . 'test.' . $phpEx) . '">Continue</a>';

		garbage_collection();
		exit_handler();
	}
}

echo 'Finished';

garbage_collection();
exit_handler();
