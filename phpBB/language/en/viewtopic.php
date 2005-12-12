<?php
/** 
*
* viewtopic [English]
*
* @package language
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ATTACHMENT'			=> 'Attachment',

	'BOOKMARK_ADDED'		=> 'Bookmarked Topic successfully.',
	'BOOKMARK_REMOVED'		=> 'Removed Bookmarked Topic successfully.',
	'BOOKMARK_TOPIC'		=> 'Bookmark Topic',
	'BOOKMARK_TOPIC_REMOVE'	=> 'Remove from Bookmarks',
	'BUMPED_BY'				=> 'Last bumped by %1$s on %2$s',
	'BUMP_TOPIC'			=> 'Bump Topic',

	'CODE'					=> 'Code',

	'DELETE_TOPIC'			=> 'Delete Topic',
	'DOWNLOAD_NOTICE'		=> 'You do not have the required permissions to view the files attached to this post.',

	'EDITED_TIMES_TOTAL'	=> 'Last edited by %1$s on %2$s, edited %3$d times in total',
	'EDITED_TIME_TOTAL'		=> 'Last edited by %1$s on %2$s, edited %3$d time in total',
	'EMAIL_TOPIC'			=> 'Email Friend',
	'ERROR_NO_ATTACHMENT'	=> 'The selected Attachment does not exist anymore',

	'FILE_NOT_FOUND_404'	=> 'The file <b>%s</b> does not exist.',
	'FORK_TOPIC'			=> 'Copy Topic',

	'LINKAGE_FORBIDDEN'		=> 'You are not authorized to view, download or link from/to this Site.',
	'LOGIN_NOTIFY_TOPIC'	=> 'You have been notified about this topic, please login to view it.',
	'LOGIN_VIEWTOPIC'		=> 'The board administrator requires you to be registered and logged in to view this topic.',

	'MAKE_ANNOUNCE'			=> 'Make Announce',
	'MAKE_GLOBAL'			=> 'Make Global',
	'MAKE_NORMAL'			=> 'Make Normal',
	'MAKE_STICKY'			=> 'Make Sticky',
	'MAX_OPTIONS_SELECT'	=> 'You may select up to <b>%d</b> options',
	'MAX_OPTION_SELECT'		=> 'You may select <b>1</b> option',
	'MISSING_INLINE_ATTACHMENT'	=> 'The Attachment <b>%s</b> is no longer available',
	'MOVE_TOPIC'			=> 'Move Topic',

	'NO_ATTACHMENT_SELECTED'=> 'You haven\'t selected an attachment to download or view.',
	'NO_NEWER_TOPICS'		=> 'There are no newer topics in this forum',
	'NO_OLDER_TOPICS'		=> 'There are no older topics in this forum',
	'NO_UNREAD_POSTS'		=> 'There are no new unread posts for this topic.',
	'NO_VOTE_OPTION'		=> 'You must specify an option when voting.',

	'POLL_RUN_TILL'			=> 'Poll runs till %s',
	'POLL_VOTED_OPTION'		=> 'You voted for this option',
	'POST_BELOW_KARMA'		=> 'This post was made by <b>%1$s</b> whose karma rating of <b>%2$d</b> is below your desired minimum. To display this post click %3$sHERE%4$s.',
	'POST_ENCODING'			=> 'This post by <b>%1$s</b> was made in a character set different to yours. To view this post in its proper encoding click %2$sHERE%3$s.',
	'PRINT_TOPIC'			=> 'Print View',

	'QUICK_MOD'				=> 'Quick-mod tools',
	'QUOTE'					=> 'Quote',

	'RATE'					=> 'Rate',
	'RATE_BAD'				=> 'Bad',
	'RATE_GOOD'				=> 'Good',
	'RATING_ADDED'			=> 'Your rating for this poster has been saved.',
	'RATING_UPDATED'		=> 'Your existing rating for this poster has been updated',
	'REPLY_TO_TOPIC'		=> 'Reply to topic',
	'RETURN_POST'			=> 'Click %sHere%s to return to the post',

	'SUBMIT_VOTE'			=> 'Submit Vote',

	'TOTAL_VOTES'			=> 'Total Votes',

	'UNLOCK_TOPIC'			=> 'Unlock Topic',

	'VIEW_INFO'				=> 'Post details',
	'VIEW_NEXT_TOPIC'		=> 'Next topic',
	'VIEW_PREVIOUS_TOPIC'	=> 'Previous topic',
	'VIEW_RESULTS'			=> 'View Results',
	'VIEW_TOPIC_POST'		=> '1 Post',
	'VIEW_TOPIC_POSTS'		=> '%d Posts',
	'VIEW_UNREAD_POST'		=> 'First unread post',
	'VISIT_WEBSITE'			=> 'WWW',
	'VOTE_SUBMITTED'		=> 'Your vote has been cast',

	'WROTE'					=> 'wrote',
));

?>