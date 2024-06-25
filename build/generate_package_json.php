#!/usr/bin/env php
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

if (version_compare(PHP_VERSION, '7.0-dev', '<'))
{
	die('generate_package_json.php requires at least PHP 7.0.');
}

define('IN_PHPBB', true);
include_once('../phpBB/includes/functions.php');

$json_data = new \stdClass();
$json_data->metadata = new stdClass();

$json_data->metadata->current_version_date = '';
$json_data->metadata->current_version = '';
$json_data->metadata->download_path = '';
$json_data->metadata->show_update_package = true;
$json_data->metadata->historic = false;

$json_data->package = [];

// Open build.xml
$build_xml = simplexml_load_file('build.xml');
$current_version = (string) $build_xml->xpath('/project/property[@name=\'newversion\']/@value')[0]->value;
$previous_version = (string) $build_xml->xpath('/project/property[@name=\'prevversion\']/@value')[0]->value;
$older_verions = explode(', ', (string) $build_xml->xpath('/project/property[@name=\'olderversions\']/@value')[0]->value);

// Clean and sort version info
$older_verions[] = $previous_version;
$older_verions = array_filter($older_verions, function($version) {
	preg_match(get_preg_expression('semantic_version'), $version, $matches);
	return empty($matches['prerelease']) || strpos($matches['prerelease'], 'pl') !== false;
});
usort($older_verions, function($version_a, $version_b)
{
	return phpbb_version_compare($version_b, $version_a);
});

// Set metadata
$json_data->metadata->current_version = $current_version;
$json_data->metadata->current_version_date = date('Y-m-d');
$json_data->metadata->download_path = 'https://download.phpbb.com/pub/release/' . preg_replace('#([0-9]+\.[0-9]+)(\..+)#', '$1', $current_version) . '/' . $current_version . '/';

// Add package, patch files, and changed files
phpbb_add_package_file(
	$json_data->package,
	'phpBB ' . $current_version,
	'phpBB-' . $current_version,
	'full',
	''
);
phpbb_add_package_file(
	$json_data->package,
	'phpBB ' . $current_version . ' Patch Files',
	'phpBB-' . $current_version . '-patch',
	'update',
	'patch'
);
phpbb_add_package_file(
	$json_data->package,
	'phpBB ' . $current_version . ' Changed Files',
	'phpBB-' . $current_version . '-files',
	'update',
	'files'
);

// Loop through packages and assign to packages array
foreach ($older_verions as $version)
{
	phpbb_add_package_file(
		$json_data->package,
		'phpBB ' . $version . ' to ' . $current_version . ' Update Package',
		'phpBB-' . $version . '_to_' . $current_version,
		'update',
		'advanced_update',
		$version
	);
}

echo(json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

function phpbb_add_package_file(array &$package_list, $name, $file_name, $type, $subtype, $from = '')
{
	if (!file_exists(__DIR__ . '/new_version/release_files/' . $file_name . '.zip'))
	{
		trigger_error('File does not exist: ' . __DIR__ . '/new_version/release_files/' . $file_name . '.zip');
		return;
	}

	$package_file = new stdClass();
	$package_file->name = $name;
	$package_file->filename = $file_name;
	$package_file->type = $type;
	if (!empty($subtype))
	{
		$package_file->subtype = $subtype;
	}
	if (!empty($from))
	{
		$package_file->from = $from;
	}
	$package_file->files = [];

	foreach (['zip', 'tar.bz2'] as $extension)
	{
		$file_path = 'new_version/release_files/' . $file_name  . '.' . $extension;
		$filedata = new stdClass();
		$filedata->filesize = filesize($file_path);
		$filedata->checksum = trim(preg_replace('/(^\w+)(.+)/', '$1', file_get_contents($file_path . '.sha256')));
		$filedata->filetype = $extension;

		if (file_exists($file_path . '.sig'))
		{
			$filedata->signature = trim(file_get_contents($file_path . '.sig'));
		}

		$package_file->files[] = $filedata;
	}

	$package_list[] = $package_file;
}
