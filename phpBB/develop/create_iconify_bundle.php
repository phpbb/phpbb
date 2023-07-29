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

/**
* This file creates Iconify bundle JS file in assets/iconify directory.
* See https://docs.iconify.design/icon-components/bundles/examples/svg-framework-custom.html
* iconify/json-tools and iconify/json dev requirements should be installed for the script to work.
*/

define('IN_PHPBB', true);
$phpbb_root_path = dirname(__FILE__) . '/../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

include($phpbb_root_path . 'common.' . $phpEx);

/** @var \phpbb\assets\iconify_bundler $iconify_bundler */
$iconify_bundler = $phpbb_container->get('assets.iconify_bundler');

// JS file to save bundle to
$target = $phpbb_root_path . 'assets/iconify/iconify-bundle.js';

// Icons to bundle, the list of iconify icons used in phpBB
$iconify_bundler->find_icons([
	$phpbb_root_path . 'styles/',
	$phpbb_root_path . 'adm/style/',
]);
$output = $iconify_bundler->with_extensions()
	->with_styles()
	->run();

// Save to file
file_put_contents($target, $output);

echo 'Saved ', $target, ' (', strlen($output), " bytes)\n";
