<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = 'phpBB/';
$phpEx = 'php';
require_once $phpbb_root_path . 'includes/startup.php';

$table_prefix = 'phpbb_';
require_once $phpbb_root_path . 'includes/constants.php';
require_once $phpbb_root_path . 'includes/class_loader.' . $phpEx;

$phpbb_class_loader_ext = new phpbb_class_loader('phpbb_ext_', $phpbb_root_path . 'ext/', ".php");
$phpbb_class_loader_ext->register();
$phpbb_class_loader = new phpbb_class_loader('phpbb_', $phpbb_root_path . 'includes/', ".php");
$phpbb_class_loader->register();

require_once 'test_framework/phpbb_test_case_helpers.php';
require_once 'test_framework/phpbb_test_case.php';
require_once 'test_framework/phpbb_database_test_case.php';
require_once 'test_framework/phpbb_database_test_connection_manager.php';

// For functional tests, we need to make available the phpBB objects
require_once $phpbb_root_path . 'config.php';
// Setup class loader first
$phpbb_class_loader_ext = new phpbb_class_loader('phpbb_ext_', $phpbb_root_path . 'ext/', ".$phpEx");
$phpbb_class_loader_ext->register();
$phpbb_class_loader = new phpbb_class_loader('phpbb_', $phpbb_root_path . 'includes/', ".$phpEx");
$phpbb_class_loader->register();

// set up caching
$cache_factory = new phpbb_cache_factory($acm_type);
$cache = $cache_factory->get_service();
$phpbb_class_loader_ext->set_cache($cache->get_driver());
$phpbb_class_loader->set_cache($cache->get_driver());

// We have to include this because the class loader doesn't
// recognize classes without the phpbb_ prefix
// So user and auth and the DBAL aren't found unless we require these files
require($phpbb_root_path . 'includes/session.' . $phpEx);
require($phpbb_root_path . 'includes/auth.' . $phpEx);
require($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);
// Instantiate some basic classes
$user		= new user();
$auth		= new auth();
$db			= new $sql_db();

$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, defined('PHPBB_DB_NEW_LINK') ? PHPBB_DB_NEW_LINK : false);

// We do not need this any longer, unset for safety purposes
unset($dbpasswd);

// Grab global variables, re-cache if necessary
$config = new phpbb_config_db($db, $cache->get_driver(), CONFIG_TABLE);

// load extensions
$phpbb_extension_manager = new phpbb_extension_manager($db, EXT_TABLE, $phpbb_root_path, ".$phpEx", $cache->get_driver());

if (version_compare(PHP_VERSION, '5.3.0-dev', '>='))
{
	require_once 'test_framework/phpbb_functional_test_case.php';
}
