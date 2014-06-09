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

if (version_compare($version, "$series_version.0", '<'))
{
	// Everything before 3.x.0, i.e. unstable (e.g. alpha, beta, rc)
	$url = "$base_url/unstable/$version";
}
else if (strpos($version, 'RC') !== false)
{
	// Release candidate of stable release
	$url = "$base_url/qa/$version";
}
else
{
	// Stable release (e.g. 3.x.0, 3.x.1, 3.x.2, 3.x.3-PL1)
	$url = "$base_url/$version";
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

function phpbb_get_checksum($checksum_file)
{
	return array_shift(explode(' ', file_get_contents($checksum_file)));
}

foreach (phpbb_rnatsort(array_diff(scandir($root), array('.', '..'))) as $filename)
{
	if (phpbb_string_ends_with($filename, $checksum_algorithm))
	{
		continue;
	}
	else
	{
		printf($template, $url, $filename, phpbb_get_checksum("$root/$filename.$checksum_algorithm"));
	}
}
