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

include($phpbb_root_path . 'vendor/autoload.php');
include($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'phpbb/class_loader.' . $phpEx);
$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}phpbb/", $phpEx);
$phpbb_class_loader->register();

// JS file to save bundle to
$target = $phpbb_root_path . 'assets/iconify/iconify-bundle.js';

// Icons to bundle, the list of iconify icons used in phpBB
$iconify_bundler = new \phpbb\assets\iconify_bundler($phpbb_root_path);
$output = $iconify_bundler->run();

// Save to file
file_put_contents($target, $output);

echo 'Saved ', $target, ' (', strlen($output), " bytes)\n";
