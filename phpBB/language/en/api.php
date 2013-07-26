<?php
/**
 *
 * api [English]
 *
 * @package language
 * @copyright (c) 2012 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'AUTH_TITLE'				=> 'Authentication request',
	'AUTH_INFORMATION'			=> 'An application wants to access your account.',
	'AUTH_ALLOW'				=> 'Allow',
	'AUTH_DENY'					=> 'Deny',
	'AUTH_APPNAME'				=> 'Application name',
	'AUTH_APPNAME_EXPLAIN'		=> 'A name that identifies the application you are authorizing.',
	'AUTH_MISSING_NAME'			=> 'The application name is required.',
	'AUTH_RETURN'				=> 'Return to authentication.',
	'COLON'						=> ':',
	'AUTH_FORM_ERROR'			=> 'Invalid form.',
	'AUTH_KEY_ERROR'			=> 'Invalid key.',
));
