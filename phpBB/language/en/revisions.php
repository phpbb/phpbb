<?php
/**
*
* revisions [English]
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

$lang = array_merge($lang, array(
	'BY'			=> 'by',

	'ERROR_REVISION_NOT_FOUND'			=> 'The specified revision could not be found.',
	'ERROR_REVISION_INSERT_FAIL'		=> 'An error occurred when attempting to insert a new revisions.',
	'ERROR_REVISION_POST_UPDATE_FAIL'	=> 'An error occurred when attempting to update the post to the specified revision.',
	'ERROR_POST_EDIT_LOCKED'			=> 'This post is locked from editing; only an authorized user can revert it to another revision.',

	'LAST_REVISION_TIME'		=> 'Last revision %1$s',

	'NO_AUTH_VIEW_REVISIONS'	=> 'You do not have permission to view these revisions.',
	'NO_DIFF'					=> 'There are no differences to display within the specified revision range.',
	'NO_POST'					=> 'The specified post does not exist.',
	'NO_REVISIONS'				=> 'The specified revision does not exist.',
	'NO_REVISIONS_POST'			=> 'There are no revisions associated with the specified post ID.',
	'NO_REASON'					=> 'No reason given.',

	'POST_REVERTED_SUCCESS'			=> 'The post has been reverted to the selected revision.',

	'RETURN_POST'				=> 'Return to the topic.',
	'RETURN_REVISION'			=> 'Return to the post revisions page.',
	'REVERT_POST_TITLE'			=> 'Revert post to another revision',
	'REVERT_POST_TITLE_CONFIRM'	=> '<strong>Are you sure you wish to revert this post to the selected revision?</strong><br />A new revision containing the current post will be created so that you can undo this change later if desired.',
	'REVISION'							=> 'Revision',
	'REVISIONS'							=> 'Revisions',
	'REVISION_ADDITIONS'		=> array(
		1 =>'1 addition',
		2 => '%1$s additions',
	),
	'REVISION_COUNT'					=> array(
		1		=> '1 revision',
		2		=> '%1$s revisions',
	),
	'REVISIONS_COMPARE_TITLE'			=> 'Post Revision History',
	'REVISIONS_REVERT_TITLE'			=> 'Revert Post to Selected Revision',
	'REVISIONS_REVERT_EXPLAIN'			=> 'The changes between the current revision and the selected revision are displayed below. If you are satisfied with those changes, click Submit.',
	'REVISION_DELETIONS'		=> array(
		1 =>'1 deletion',
		2 => '%1$s deletions',
	),
	'REVISIONS_FROM_DIFFERENT_POSTS'	=> 'You cannot compare revisions from separate posts.',
	'REVISION_USER_COUNT'				=> array(
		0		=> '0 users',
		1		=> '1 user',
		2		=> '%1$s users',
	),

	'SUBMIT_FORM_AGAIN'			=> 'This form has timed out. Please try again by resubmitting the form.',

	'WITH'						=> 'with',
));
