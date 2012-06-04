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

	'LAST_REVISION_TIME'		=> 'Last revision %1$s',
	'REVISION_ADDITIONS'		=> array(
		1 =>'1 addition',
		2 => '%1$s additions',
	),
	'REVISION_DELETIONS'		=> array(
		1 =>'1 deletion',
		2 => '%1$s deletions',
	),

	'NO_AUTH_VIEW_REVISIONS'	=> 'You do not have permission to view these revisions.',
	'NO_DIFF'					=> 'There are no differences to display within the specified revision range.',
	'NO_POST'					=> 'The specified post does not exist.',
	'NO_REVISIONS'				=> 'The specified revision does not exist.',
	'NO_REVISIONS_POST'			=> 'There are no revisions associated with the specified post ID.',
	'NO_REASON'					=> 'No reason given.',

	'REVERT_POST_TITLE'			=> 'Revert post to another revision',
	'REVERT_POST_TITLE_CONFIRM'	=> '<strong>Are you sure you wish to revert this post to the selected revision?</strong><br />A new revision containing the current post will be created so that you can undo this change later if desired.',
	'REVISION'							=> 'Revision',
	'REVISIONS'							=> 'Revisions',
	'REVISION_COUNT'					=> array(
		0		=> '0 revisions',
		1		=> '1 revision',
		2		=> '%1$s revisions',
	),
	'REVISIONS_COMPARE_TITLE'			=> 'Post Revision History',
	'REVISIONS_FROM_DIFFERENT_POSTS'	=> 'You cannot compare revisions from separate posts.',
	'REVISION_USER_COUNT'				=> array(
		0		=> '0 users',
		1		=> '1 user',
		2		=> '%1$s users',
	),

	'WITH'						=> 'with',
));
