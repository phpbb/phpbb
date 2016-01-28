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

$_SERVER['SCRIPT_NAME'] =  str_replace('viewonline.php', 'app.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['PHP_SELF'] =  str_replace('viewonline.php', 'app.php', $_SERVER['PHP_SELF']);
$_SERVER['SCRIPT_FILENAME'] =  str_replace('viewonline.php', 'app.php', $_SERVER['SCRIPT_FILENAME']);
$_SERVER['REQUEST_URI'] = '/app.php'.$_SERVER['REQUEST_URI'];
$_SERVER['DOCUMENT_URI'] = '/app.php'.$_SERVER['DOCUMENT_URI'];

require 'app.php';
