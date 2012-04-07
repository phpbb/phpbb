<?php
/**
*
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

define('IN_PHPBB', 1);
define('ANONYMOUS', 1);
$phpEx = substr(strrchr(__FILE__, '.'), 1);
$phpbb_root_path = __DIR__.'/../';

include($phpbb_root_path . 'common.'.$phpEx);

function usage()
{
	echo "Usage: extensions.php COMMAND [OPTION]...\n";
	echo "Console extension manager.\n";
	echo "\n";
	echo "list:\n";
	echo "    Lists all extensions in the database and the filesystem.\n";
	echo "\n";
	echo "enable NAME:\n";
	echo "    Enables the specified extension.\n";
	echo "\n";
	echo "disable NAME:\n";
	echo "    Disables the specified extension.\n";
	echo "\n";
	echo "purge NAME:\n";
	echo "    Purges the specified extension.\n";
	exit(2);
}

function list_extensions()
{
	global $phpbb_extension_manager;

	$phpbb_extension_manager->load_extensions();
	$all = array_keys($phpbb_extension_manager->all_available());

	if (empty($all))
	{
		echo "There were no extensions found.\n";
		exit(3);
	}

	echo "Enabled:\n";
	$enabled = array_keys($phpbb_extension_manager->all_enabled());
	print_extensions($enabled);
	echo "\n";

	echo "Disabled:\n";
	$disabled = array_keys($phpbb_extension_manager->all_disabled());
	print_extensions($disabled);
	echo "\n";

	echo "Available:\n";
	$purged = array_diff($all, $enabled, $disabled);
	print_extensions($purged);
}

function print_extensions($exts)
{
	foreach ($exts as $ext)
	{
		echo "- $ext\n";
	}
}

function enable_extension($name)
{
	global $phpbb_extension_manager;

	$phpbb_extension_manager->enable($name);
}

function disable_extension($name)
{
	global $phpbb_extension_manager;

	$phpbb_extension_manager->disable($name);
}

function purge_extension($name)
{
	global $phpbb_extension_manager;

	$phpbb_extension_manager->purge($name);
}

function validate_argument_count($count)
{
	global $argv;

	if (count($argv) <= $count)
	{
		usage();
	}
}

validate_argument_count(1);

$action = $argv[1];

switch ($action)
{
	case 'list':
		list_extensions();
		break;

	case 'enable':
		validate_argument_count(2);
		enable_extension($argv[2]);
		break;

	case 'disable':
		validate_argument_count(2);
		disable_extension($argv[2]);
		break;

	case 'purge':
		validate_argument_count(2);
		purge_extension($argv[2]);
		break;

	default:
		usage();
}
