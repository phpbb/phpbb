<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

// This is to help with creating migration files for new versions
// Use this to find what migrations are not depended on by any other migration
//  (the current migration tree tips)

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$phpbb_extension_manager = $phpbb_container->get('ext.manager');
$finder = $phpbb_extension_manager->get_finder();

$migrations = $finder
	->core_path('phpbb/db/migration/data/')
	->get_classes();
$tips = $migrations;

foreach ($migrations as $migration_class)
{
	foreach ($migration_class::depends_on() as $dependency)
	{
		if (($tips_key = array_search($dependency, $tips)) !== false)
		{
			unset($tips[$tips_key]);
		}
	}
}

foreach ($tips as $migration)
{
	echo "\t\t\t'{$migration}',\n";
}

