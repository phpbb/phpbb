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

	'COMPARE'			=> 'Compare',
	'COMPARING'			=> 'Comparing',
	'COMPARE_SELECTED'	=> 'Compare selected',
	'CURRENT_REVISION'	=> 'Current Post',

	'ERROR_AUTH_ACTION'					=> 'You do not have permission to %s revisions for this post.',
	'ERROR_AUTH_RESTORE'				=> 'You do not have permission to restore this post to another revision.',
	'ERROR_AUTH_VIEW'					=> 'You do not have permission to view the revisions for this post.',
	'ERROR_NO_POST_REVISIONS'			=> 'The specified post ID does not exist.',
	'ERROR_POST_EDIT_LOCKED'			=> 'This post is locked from editing; only an authorized user can restore it to another revision.',
	'ERROR_REVISION_INSERT_FAIL'		=> 'An error occurred when attempting to insert a new revisions.',
	'ERROR_REVISION_NOT_FOUND'			=> 'The specified revision could not be found.',
	'ERROR_REVISION_POST_UPDATE_FAIL'	=> 'An error occurred when attempting to update the post to the specified revision.',

	'LAST_REVISION_TIME'		=> 'Last revision &raquo; %1$s',
	'LAST_FIVE_REVISIONS'		=> 'Last 5 Revisions',
	'LOGIN_REVISION'			=> 'You must login in order to view the revisions for this post.',

	'NO_DIFF'					=> 'There are no differences to display within the specified revision range.',
	'NO_POST'					=> 'The specified post does not exist.',
	'NO_REASON'					=> 'No reason given.',
	'NO_REVISIONS'				=> 'The specified revision does not exist.',
	'NO_REVISIONS_POST'			=> 'There are no revisions associated with the specified post ID.',

	'POST_RESTORED_SUCCESS'		=> 'The post has been restored to the selected revision.',
	'PROTECT'					=> 'Protect',

	'RETURN_POST'						=> 'Return to the post',
	'RETURN_REVISION'					=> 'Return to the post revisions page',
	'RESTORE'							=> 'Restore',
	'RESTORE_POST_TITLE'				=> 'Restore post to another revision',
	'RESTORE_POST_TITLE_CONFIRM'		=> '<strong>Are you sure you wish to restore this post to the selected revision?</strong><br />A new revision containing the current post will be created so that you can undo this change later if desired.',
	'RESTORE_TO_THIS'					=> 'Restore the post to this revision',
	'RESTORING_POST'					=> 'Restoring Post',
	'REVISION'							=> 'Revision',
	'REVISION_ADDITIONS'				=> array(
		1		=> '1 addition',
		2		=> '%1$s additions',
	),
	'REVISION_COUNT'					=> array(
		1		=> '<span id="compare_summary">1</span> revision',
		2		=> '<span id="compare_summary">%1$s</span> revisions',
	),
	'REVISION_DELETE'					=> array(
		1 => 'Delete revision',
		2 => 'Delete revisions',
	),
	'REVISION_DELETE_CONFIRM'			=> array(
		1 => 'Are you sure you wish to delete the selected revision? This cannot be undone.',
		2 => 'Are you sure you wish to delete the selected revisions? This cannot be undone.',
	),
	'REVISION_DELETED_SUCCESS'			=> array(
		1 => 'The selected revision has been deleted.',
		2 => 'The selected revisions have been deleted.',
	),
	'REVISION_DELETED_SUCCESS_NO_MORE'	=> array(
		1 => 'The selected revision has been deleted. There are no more revisions to display.',
		2 => 'The selected revisions have been deleted. There are no more revisions to display.',
	),
	'REVISION_DELETE_FAIL'				=> array(
		1 => 'The selected revision could not be deleted.',
		2 => 'The selected revisions could not be deleted.',
	),
	'REVISION_PROTECT'					=> array(
		1 => 'Protect revision',
		2 => 'Protect revisions',
	),
	'REVISION_PROTECT_CONFIRM'			=> array(
		1 => 'Are you sure you wish to mark the selected revision as protected? This revision will not be deleted in automatic pruning tasks.',
		2 => 'Are you sure you wish to mark the selected revisions as protected? These revisions will not be deleted in automatic pruning tasks.',
	),
	'REVISION_PROTECTED_SUCCESS'		=> array(
		1 => 'The selected revision has been marked as protected.',
		2 => 'The selected revisions have been marked as protected.',
	),
	'REVISION_PROTECT_FAIL'				=> array(
		1 => 'The selected revision could not be marked as protected.',
		2 => 'The selected revisions could not be marked as protected.',
	),
	'REVISION_UNPROTECT'				=> array(
		1 => 'Unprotect revision',
		2 => 'Unprotect revisions',
	),
	'REVISION_UNPROTECT_CONFIRM'		=> array(
		1 => 'Are you sure you wish to mark the selected revision as unprotected? This revision may be deleted in automatic pruning tasks.',
		2 => 'Are you sure you wish to mark the selected revisions as unprotected? These revisions may be deleted in automatic pruning tasks.',
	),
	'REVISION_UNPROTECTED_SUCCESS'		=> array(
		1 => 'The selected revision has been marked as unprotected.',
		2 => 'The selected revisions have been marked as unprotected.',
	),
	'REVISION_UNPROTECT_FAIL'			=> array(
		1 => 'The selected revision could not be marked as unprotected.',
		2 => 'The selected revisions could not be marked as unprotected.',
	),
	'REVISION_DELETIONS'				=> array(
		1 => '1 deletion',
		2 => '%1$s deletions',
	),
	'REVISION_RESTORE'					=> array(
		1 => 'Restore revision',
		2 => 'Restore revisions',
	),
	'REVISION_USER_COUNT'				=> array(
		0 => '0 users',
		1 => '1 user',
		2 => '%1$s users',
	),

	// These are used exclusively in the revision comparison list where
	// plurals don't work right
	'SINGLE_REVISION_PROTECT'		=> 'Protect revision',
	'SINGLE_REVISION_UNPROTECT'		=> 'Unprotect revision',
	'SINGLE_REVISION_DELETE'		=> 'Delete revision',
	'SINGLE_REVISION_RESTORE'		=> 'Restore revision',

	'REVISIONS'							=> 'Revisions',
	'REVISIONS_COMPARE_TITLE'			=> 'Comparing Post Revisions',
	'REVISIONS_FROM_DIFFERENT_POSTS'	=> 'You cannot compare revisions from separate posts.',
	'REVISIONS_RESTORE_TITLE'			=> 'Restore Post to Selected Revision',
	'REVISIONS_RESTORE_EXPLAIN'			=> 'The changes between the current revision and the selected revision are displayed below. If you are satisfied with those changes, click Submit.',

	'SUBMIT_FORM_AGAIN'			=> 'This form has timed out. Please try again by resubmitting the form.',

	'UNPROTECT'					=> 'Unprotect',

	'WITH'						=> 'with',

	'VIEW'							=> 'View',
	'VIEWING_POST_REVISION'			=> 'Post Revision',
	'VIEWING_POST_REVISION_EXPLAIN'	=> 'This page displays the revision made by %1$s on %2$s.',
	'VIEWING_POST_REVISION_HISTORY'	=> 'Post Revision History',
));
