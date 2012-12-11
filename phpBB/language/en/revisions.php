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

	'COMPARING'			=> 'Comparing',
	'COMPARE_SELECTED'	=> 'Compare selected',
	'CURRENT_REVISION'	=> 'Current Revision',

	'ERROR_REVISION_NOT_FOUND'			=> 'The specified revision could not be found.',
	'ERROR_REVISION_INSERT_FAIL'		=> 'An error occurred when attempting to insert a new revisions.',
	'ERROR_REVISION_POST_UPDATE_FAIL'	=> 'An error occurred when attempting to update the post to the specified revision.',
	'ERROR_POST_EDIT_LOCKED'			=> 'This post is locked from editing; only an authorized user can restore it to another revision.',

	'LAST_REVISION_TIME'		=> 'Last revision %1$s',
	'LAST_FIVE_REVISIONS'		=> 'Last 5 Revisions',
	'LOGIN_REVISION'			=> 'You must login in order to view the revisions for this post.',

	'NO_AUTH_DELETE_REVISIONS'	=> 'You do not have permission to delete revisions for this post.',
	'NO_AUTH_REVERT'			=> 'You do not have permission to restore this post to another revision.',
	'NO_AUTH_VIEW_REVISIONS'	=> 'You do not have permission to view the revisions for this post.',
	'NO_DIFF'					=> 'There are no differences to display within the specified revision range.',
	'NO_POST'					=> 'The specified post does not exist.',
	'NO_REASON'					=> 'No reason given.',
	'NO_REVISIONS'				=> 'The specified revision does not exist.',
	'NO_REVISIONS_POST'			=> 'There are no revisions associated with the specified post ID.',

	'POST_REVERTED_SUCCESS'		=> 'The post has been restored to the selected revision.',
	'PROTECT'					=> 'Protect',

	'RETURN_POST'				=> 'Return to the post',
	'RETURN_REVISION'			=> 'Return to the post revisions page',
	'REVERT'					=> 'Restore',
	'REVERT_POST_TITLE'			=> 'Restore post to another revision',
	'REVERT_POST_TITLE_CONFIRM'	=> '<strong>Are you sure you wish to restore this post to the selected revision?</strong><br />A new revision containing the current post will be created so that you can undo this change later if desired.',
	'REVERT_TO_THIS'			=> 'Restore the post to this revision',
	'REVERTING_POST'			=> 'Restoring Post',
	'REVISION'					=> 'Revision',
	'REVISION_ADDITIONS'		=> array(
		1		=>'1 addition',
		2		=> '%1$s additions',
	),
	'REVISION_COUNT'			=> array(
		1		=> '<span id="compare_summary">1</span> revision',
		2		=> '<span id="compare_summary">%1$s</span> revisions',
	),
	'REVISION_DELETE'					=> 'Delete revision',
	'REVISION_DELETE_CONFIRM'			=> 'Are you sure you wish to delete the selected revision? This cannot be undone.',
	'REVISION_DELETED_SUCCESS'			=> 'The selected revision has been deleted.',
	'REVISION_DELETED_SUCCESS_NO_MORE'	=> 'The selected revision has been deleted. There are no more revisions to display.',	
	'REVISION_PROTECT'					=> 'Protect revision',
	'REVISION_PROTECT_CONFIRM'			=> 'Are you sure you wish to mark the selected revision as protected? This revision will not be deleted in automatic pruning tasks.',
	'REVISION_PROTECTED_SUCCESS'		=> 'The selected revision has been marked as protected.',
	'REVISION_UNPROTECT'				=> 'Unprotect revision',
	'REVISION_UNPROTECT_CONFIRM'		=> 'Are you sure you wish to mark the selected revision as unprotected? This revision will may be deleted in automatic pruning tasks.',
	'REVISION_UNPROTECTED_SUCCESS'		=> 'The selected revision has been marked as unprotected.',
	'REVISION_DELETIONS'				=> array(
		1 =>'1 deletion',
		2 => '%1$s deletions',
	),
	'REVISION_USER_COUNT'				=> array(
		0		=> '0 users',
		1		=> '1 user',
		2		=> '%1$s users',
	),
	'REVISION_VIEW_TITLE'				=> 'Viewing a Post Revision',

	'REVISIONS'							=> 'Revisions',
	'REVISIONS_COMPARE_TITLE'			=> 'Comparing Post Revisions',
	'REVISIONS_FROM_DIFFERENT_POSTS'	=> 'You cannot compare revisions from separate posts.',
	'REVISIONS_REVERT_TITLE'			=> 'Restore Post to Selected Revision',
	'REVISIONS_REVERT_EXPLAIN'			=> 'The changes between the current revision and the selected revision are displayed below. If you are satisfied with those changes, click Submit.',

	'SUBMIT_FORM_AGAIN'			=> 'This form has timed out. Please try again by resubmitting the form.',

	'UNPROTECT'					=> 'Unprotect',

	'WITH'						=> 'with',

	'VIEW'							=> 'View',
	'VIEWING_POST_REVISION'			=> 'Viewing Post Revision',
	'VIEWING_POST_REVISION_EXPLAIN'	=> 'This page displays the revision made by %1$s on %2$s.',
	'VIEWING_POST_REVISION_HISTORY'	=> 'Viewing Post Revision History',
));
