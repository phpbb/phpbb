<?php
/**
*
* revisions [English]
*
* @package language
* @copyright (c) 2005 phpBB Group
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

$lang = array_merge($lang, array(
	'BY'			=> 'by',

	'NO_AUTH_VIEW_REVISIONS'	=> 'You do not have permission to view these revisions.',
	'NO_REVISIONS'				=> 'There are no revisions associated with the specified post ID.',

	'REVISION'							=> 'Revision',
	'REVISIONS'							=> 'Revisions',
	'REVISIONS_COMPARE_TITLE'			=> 'Differences between revisions %1$s and %2$s of %3$s',
	'REVISIONS_FROM_DIFFERENT_POSTS'	=> 'You cannot compare revisions from separate posts.',
));
