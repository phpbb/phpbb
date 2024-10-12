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
	$lang = [];
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

$lang = array_merge($lang, [
	'CAPTCHA_TURNSTILE'					=> 'Turnstile',
	'CAPTCHA_TURNSTILE_SITEKEY'			=> 'Sitekey',
	'CAPTCHA_TURNSTILE_SITEKEY_EXPLAIN'	=> 'Your Turnstile sitekey. The sitekey can be retrieved from your <a href="https://dash.cloudflare.com/?to=/:account/turnstile">Cloudflare dashboard</a>.',
	'CAPTCHA_TURNSTILE_SECRET'			=> 'Secret key',
	'CAPTCHA_TURNSTILE_SECRET_EXPLAIN'	=> 'Your Turnstile secret key. The secret key can be retrieved from your <a href="https://dash.cloudflare.com/?to=/:account/turnstile">Cloudflare dashboard</a>.',
]);
