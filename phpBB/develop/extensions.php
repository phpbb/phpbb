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
$phpbb_root_path = './../';

include($phpbb_root_path . 'common.'.$phpEx);

function usage()
{
	echo "Usage: extensions.php COMMAND [OPTION]...\n";
	echo "Console extension manager.\n";
	echo "\n";
	echo "list:\n";
	echo "    Lists all extensions in the database and the filesystem.\n";
	echo "    Next to each extension name are two flags:\n";
	echo "\n";
	echo "     * P|M - present|missing: whether the extension exists in the filesystem\n";
	echo "     * A|I - active|inactive: whether the extension is activated in the database\n";
	echo "\n";
	echo "enable NAME:\n";
	echo "    Enables the specified extension.\n";
	echo "\n";
	echo "disable NAME:\n";
	echo "    Disables the specified extension.\n";
	exit(2);
}

function list_extensions()
{
	global $db, $phpbb_root_path;

	$sql = "SELECT ext_name, ext_active from " . EXT_TABLE;

	$result = $db->sql_query($sql);
	$extensions = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$extensions[$row['ext_name']]['active'] = (bool) $row['ext_active'];
		if (file_exists($phpbb_root_path . 'ext/' . $row['ext_name']))
		{
			$extensions[$row['ext_name']]['present'] = true;
		}
		else
		{
			$extensions[$row['ext_name']]['present'] = false;
		}
	}

	$iterator = new DirectoryIterator($phpbb_root_path . 'ext');
	foreach ($iterator as $file)
	{
		// ignore hidden files
		// php refuses to subscript iterator objects
		$file = "$file";
		if ($file[0] != '.')
		{
			if (!array_key_exists($file, $extensions))
			{
				$extensions[$file] = array('active' => false, 'present' => true);
			}
		}
	}

	ksort($extensions);
	foreach ($extensions as $name => $ext)
	{
		$present = $ext['active'] ? 'P' : 'M';
		$active = $ext['active'] ? 'A' : 'I';
		printf("%-20s %s %s\n", $name, $present, $active);
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

	default:
		usage();
}
