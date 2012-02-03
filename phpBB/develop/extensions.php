<?php
// -------------------------------------------------------------
//
// FILENAME  : extensions.php
// STARTED   : Fri Feb 3, 2012
// COPYRIGHT : (C) 2012 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

// Console extension manager.
//
// Usage:
//
// extensions.php list
//
// Lists all extensions in the database and the filesystem.
// Next to each extension name are two flags:
// P|M - present|missing - whether the extension exists in the filesystem
// A|I - active|inactive - whether the extension is activated in the database
//
// extensions.php enable <name>
//
// Enables the specified extension.
//
// extensions.php disable <name>
//
// Disables the specified extension.

define('IN_PHPBB', 1);
define('ANONYMOUS', 1);
$phpEx = substr(strrchr(__FILE__, '.'), 1);
$phpbb_root_path = './../';

include($phpbb_root_path . 'common.'.$phpEx);

function usage()
{
	echo "Please see comments in extensions.php for usage\n";
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
