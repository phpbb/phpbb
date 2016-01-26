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

$_SERVER['SCRIPT_NAME'] =  str_replace('ucp.php', 'app.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['PHP_SELF'] =  str_replace('ucp.php', 'app.php', $_SERVER['PHP_SELF']);
$_SERVER['SCRIPT_FILENAME'] =  str_replace('ucp.php', 'app.php', $_SERVER['SCRIPT_FILENAME']);

require 'app.php';
