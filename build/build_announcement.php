#!/usr/bin/env php
<?php
/**
*
* @package build
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (php_sapi_name() !== 'cli' || $_SERVER['argc'] != 5)
{
	echo "Usage (CLI only): build_announcement.php email|bbcode new_version release_files_dir checksum_algorithm\n";
	exit(1);
}

$mode = $_SERVER['argv'][1];
$version = $_SERVER['argv'][2];
$root = $_SERVER['argv'][3];
$checksum_algorithm = $_SERVER['argv'][4];

$series_version = substr($version, 0, 3);
$base_url = "https://download.phpbb.com/pub/release/$series_version";

if (strpos($version, 'RC') === false)
{
	// Final release
	$install_url	= "$base_url/$version";
	$update_url		= "$base_url/update/to_$version";
}
else
{
	$install_url	= "$base_url/release_candidates/$version";
	$update_url		= "$base_url/release_candidates/update/other_to_$version";
}

if ($mode === 'bbcode')
{
	$template = "[url=%1\$s/%2\$s]%2\$s[/url]\n{$checksum_algorithm}sum: %3\$s\n";
}
else
{
	$template = "%s/%s\n{$checksum_algorithm}sum: %s\n";
}

function phpbb_rnatsort($array)
{
	$strrnatcmp = function($a, $b)
	{
		return strnatcmp($b, $a);
	};
	usort($array, $strrnatcmp);
	return $array;
}

function phpbb_string_ends_with($haystack, $needle)
{
	return substr($haystack, -strlen($needle)) === $needle;
}

function phpbb_is_update_file($filename)
{
	return strpos($filename, '_to_') !== false;
}

function phpbb_get_checksum($checksum_file)
{
	return array_shift(explode(' ', file_get_contents($checksum_file)));
}

$install_files = $update_files = array();
foreach (phpbb_rnatsort(array_diff(scandir($root), array('.', '..'))) as $filename)
{
	if (phpbb_string_ends_with($filename, $checksum_algorithm))
	{
		continue;
	}
	else if (phpbb_is_update_file($filename))
	{
		$update_files[] = $filename;
	}
	else
	{
		$install_files[] = $filename;
	}
}

foreach ($install_files as $filename)
{
	printf($template, $install_url, $filename, phpbb_get_checksum("$root/$filename.$checksum_algorithm"));
}

echo "\n";

foreach ($update_files as $filename)
{
	printf($template, $update_url, $filename, phpbb_get_checksum("$root/$filename.$checksum_algorithm"));
}
