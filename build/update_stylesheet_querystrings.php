<?php declare(strict_types=1);
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

if (php_sapi_name() !== 'cli')
{
	die("This program must be run from the command line.\n");
}

if (version_compare(PHP_VERSION, '7.1.3', '<'))
{
	die('update_stylesheet_querystrings.php requires at least PHP 7.1.3');
}

// Usage: "$ php build/update_stylesheet_querystrings.php"
$targets = [dirname(dirname(__FILE__)) . '/phpBB/styles/prosilver/theme/stylesheet.css'];

array_map('patch_glob', $targets);

function patch_glob($glob): void
{
	array_map('patch_file', glob($glob));
}

function patch_file(string $filepath): void
{
	$file	= file_get_contents($filepath);
	$old	= $file;
	$new	= preg_replace_callback(
		'(^@import\\s+url\\([\'"](?<basename>\\w++\\.css)\\?\\K(?:hash|v)=[^\'"]++)m',
		function ($match) use ($filepath)
		{
			$path = dirname($filepath) . DIRECTORY_SEPARATOR . $match['basename'];
			$hash = sprintf('%08x', crc32(file_get_contents($path)));

			return 'hash=' . $hash;
		},
		$old
	);

	if ($new !== $old)
	{
		file_put_contents($filepath, $new);
	}
}
