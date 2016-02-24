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
* Idea and original RSS Feed 2.0 MOD (Version 1.0.8/9) by leviatan21
* Original MOD: http://www.phpbb.com/community/viewtopic.php?f=69&t=1214645
* MOD Author Profile: http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=345763
* MOD Author Homepage: http://www.mssti.com/phpbb3/
*
**/

$_SERVER['SCRIPT_NAME'] =  str_replace('feed.php', 'app.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['PHP_SELF'] =  str_replace('feed.php', 'app.php', $_SERVER['PHP_SELF']);
$_SERVER['SCRIPT_FILENAME'] =  str_replace('feed.php', 'app.php', $_SERVER['SCRIPT_FILENAME']);
$_SERVER['REQUEST_URI'] = '/app.php'.$_SERVER['REQUEST_URI'];
$_SERVER['DOCUMENT_URI'] = '/app.php'.$_SERVER['DOCUMENT_URI'];

require 'app.php';
