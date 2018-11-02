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
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'CAPTCHA_HONEYPOT'				=> 'Honeypot',
	'HONEYPOT_CAPTCHA_INVISIBLE'	=> 'This CAPTCHA is invisible to the users.',
	'honeypot_bank'					=> array(
		'color'		=> 'What color is grass?',
		'math'		=> 'How much is ' . rand(1, 100) . ' + ' . rand(1, 100),
		'name'		=> 'Name',
		'today'		=> 'What day is today?',
		'credcard'	=> 'Credit card number',
		'year'		=> 'What year is now?',
		'email2'	=> 'Email again',
		'pass'		=> 'Passcode',
		'age'		=> 'Age',
	),
));
