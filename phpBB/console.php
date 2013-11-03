<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

use Symfony\Component\Console\Application;

if (php_sapi_name() != 'cli')
{
	echo 'This program must be run from the command line.' . PHP_EOL;
	exit(1);
}

define('IN_PHPBB', true);
$phpbb_root_path = __DIR__ . '/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'includes/startup.' . $phpEx);
require($phpbb_root_path . 'config.' . $phpEx);
require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/functions_container.' . $phpEx);
require($phpbb_root_path . 'phpbb/class_loader.' . $phpEx);

$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}phpbb/", $phpEx);
$phpbb_class_loader->register();
$phpbb_class_loader_ext = new \phpbb\class_loader('\\', "{$phpbb_root_path}ext/", $phpEx);
$phpbb_class_loader_ext->register();

$phpbb_container = phpbb_create_update_container($phpbb_root_path, $phpEx, "$phpbb_root_path/config");

$application = new Application('phpBB Console', PHPBB_VERSION);
foreach($phpbb_container->findTaggedServiceIds('console.command') as $id => $void)
{
	$application->add($phpbb_container->get($id));
}
$application->run();
