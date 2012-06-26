<?php
/**
*
* @package phpBB3-Akismet [English]
* @copyright (c) 2012 Nathaniel Guse
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
	'AKISMET'								=> 'Akismet',
	'PHPBB_AKISMET'							=> 'Akismet for phpBB3',
	'PHPBB_AKISMET_API_KEY'					=> 'Akismet API key (Required)',
	'PHPBB_AKISMET_API_KEY_EXPLAIN'			=> 'If you do not have an API key already, you can get one <a href="https://akismet.com/signup/">here</a>',
	'PHPBB_AKISMET_ENABLE'					=> 'Akismet Integration',
	'PHPBB_AKISMET_ENABLE_EXPLAIN'			=> 'Set to disabled to disable entire Akismet integration',
	'PHPBB_AKISMET_INVALID_KEY'				=> 'Invalid Akismet Key',

	'PHPBB_AKISMET_REMOVE_SPAM'				=> 'Remove Spam',
	'PHPBB_AKISMET_REMOVE_SPAM_CONFIRM'		=> 'Are you sure you want to remove the following post and submit it to Akismet?<blockquote><div><cite>%1$s wrote:</cite>%2$s</div></blockquote>',
	'PHPBB_AKISMET_REMOVE_SPAM_COMPLETE'	=> 'The spam post has been removed and reported to Akismet successfully!',

	'PHPBB_AKISMET_HAM'						=> 'Ham',
	'PHPBB_AKISMET_SPAM'					=> 'Spam',
));