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
	'ADD_ATTACHMENT'			=> 'Upload attachment',
	'ADD_ATTACHMENT_EXPLAIN'	=> 'If you wish to attach one or more files enter the details below.',
	'ADD_FILE'					=> 'Add the file',
	'ADD_POLL'					=> 'Poll creation',
	'ADD_POLL_EXPLAIN'			=> 'If you do not want to add a poll to your topic leave the fields blank.',
	'ALREADY_DELETED'			=> 'Sorry but this message is already deleted.',
	'ATTACH_DISK_FULL'			=> 'There is not enough free disk space to post this attachment.',
	'ATTACH_QUOTA_REACHED'		=> 'Sorry, the board attachment quota has been reached.',
	'ATTACH_SIG'				=> 'Attach a signature (signatures can be altered via the UCP)',

	'BBCODE_A_HELP'				=> 'Inline uploaded attachment: [attachment=]filename.ext[/attachment]',
	'BBCODE_B_HELP'				=> 'Bold text: [b]text[/b]',
	'BBCODE_C_HELP'				=> 'Code display: [code]code[/code]',
	'BBCODE_D_HELP'				=> 'Flash: [flash=width,height]http://url[/flash]',
	'BBCODE_F_HELP'				=> 'Font size: [size=85]small text[/size]',
	'BBCODE_IS_OFF'				=> '%sBBCode%s is <em>OFF</em>',
	'BBCODE_IS_ON'				=> '%sBBCode%s is <em>ON</em>',
	'BBCODE_I_HELP'				=> 'Italic text: [i]text[/i]',
	'BBCODE_L_HELP'				=> 'List: [list][*]text[/list]',
	'BBCODE_LISTITEM_HELP'		=> 'List item: [*]text',
	'BBCODE_O_HELP'				=> 'Ordered list: e.g. [list=1][*]First point[/list] or [list=a][*]Point a[/list]',
	'BBCODE_P_HELP'				=> 'Insert image: [img]http://image_url[/img]',
	'BBCODE_Q_HELP'				=> 'Quote text: [quote]text[/quote]',
	'BBCODE_S_HELP'				=> 'Font colour: [color=red]text[/color] or [color=#FF0000]text[/color]',
	'BBCODE_U_HELP'				=> 'Underline text: [u]text[/u]',
	'BBCODE_W_HELP'				=> 'Insert URL: [url]http://url[/url] or [url=http://url]URL text[/url]',
	'BBCODE_Y_HELP'				=> 'List: Add list element',
	'BUMP_ERROR'				=> 'You cannot bump this topic so soon after the last post.',

	'CANNOT_DELETE_REPLIED'		=> 'Sorry but you may only delete posts which have not been replied to.',
	'CANNOT_EDIT_POST_LOCKED'	=> 'This post has been locked. You can no longer edit that post.',
	'CANNOT_EDIT_TIME'			=> 'You can no longer edit or delete that post.',
	'CANNOT_POST_ANNOUNCE'		=> 'Sorry but you cannot post announcements.',
	'CANNOT_POST_STICKY'		=> 'Sorry but you cannot post sticky topics.',
	'CHANGE_TOPIC_TO'			=> 'Change topic type to',
	'CHARS_POST_CONTAINS'		=> array(
		1	=> 'Your message contains %1$d character.',
		2	=> 'Your message contains %1$d characters.',
	),
	'CHARS_SIG_CONTAINS'		=> array(
		1	=> 'Your signature contains %1$d character.',
		2	=> 'Your signature contains %1$d characters.',
	),
	'CLOSE_TAGS'				=> 'Close tags',
	'CURRENT_TOPIC'				=> 'Current topic',

	'DELETE_FILE'				=> 'Delete file',
	'DELETE_MESSAGE'			=> 'Delete message',
	'DELETE_MESSAGE_CONFIRM'	=> 'Are you sure you want to delete this message?',
	'DELETE_OWN_POSTS'			=> 'Sorry but you can only delete your own posts.',
	'DELETE_PERMANENTLY'		=> 'Delete permanently',
	'DELETE_POST_CONFIRM'		=> 'Are you sure you want to delete this post?',
	'DELETE_POST_PERMANENTLY_CONFIRM'	=> 'Are you sure you want to <strong>permanently</strong> delete this post?',
	'DELETE_POST_PERMANENTLY'	=> array(
		1	=> 'Permanently delete this post so it can not be recovered',
		2	=> 'Permanently delete %1$d posts so they can not be recovered',
	),
	'DELETE_POSTS_CONFIRM'		=> 'Are you sure you want to delete these posts?',
	'DELETE_POSTS_PERMANENTLY_CONFIRM'	=> 'Are you sure you want to <strong>permanently</strong> delete these posts?',
	'DELETE_REASON'				=> 'Reason for deletion',
	'DELETE_REASON_EXPLAIN'		=> 'The specified reason for deletion will be visible to moderators.',
	'DELETE_POST_WARN'			=> 'Delete this post',
	'DELETE_TOPIC_CONFIRM'		=> 'Are you sure you want to delete this topic?',
	'DELETE_TOPIC_PERMANENTLY'	=> array(
		1	=> 'Permanently delete this topic so it can not be recovered',
		2	=> 'Permanently delete %1$d topics so they can not be recovered',
	),
	'DELETE_TOPIC_PERMANENTLY_CONFIRM'	=> 'Are you sure you want to <strong>permanently</strong> delete this topic?',
	'DELETE_TOPICS_CONFIRM'		=> 'Are you sure you want to delete these topics?',
	'DELETE_TOPICS_PERMANENTLY_CONFIRM'	=> 'Are you sure you want to <strong>permanently</strong> delete these topics?',
	'DISABLE_BBCODE'			=> 'Disable BBCode',
	'DISABLE_MAGIC_URL'			=> 'Do not automatically parse URLs',
	'DISABLE_SMILIES'			=> 'Disable smilies',
	'DISALLOWED_CONTENT'		=> 'The upload was rejected because the uploaded file was identified as a possible attack vector.',
	'DISALLOWED_EXTENSION'		=> 'The extension %s is not allowed.',
	'DRAFT_LOADED'				=> 'Draft loaded into posting area, you may want to finish your post now.<br />Your draft will be deleted after submitting this post.',
	'DRAFT_LOADED_PM'			=> 'Draft loaded into message area, you may want to finish your private message now.<br />Your draft will be deleted after submitting this private message.',
	'DRAFT_SAVED'				=> 'Draft successfully saved.',
	'DRAFT_TITLE'				=> 'Draft title',

	'EDIT_REASON'				=> 'Reason for editing this post',
	'EMPTY_FILEUPLOAD'			=> 'The uploaded file is empty.',
	'EMPTY_MESSAGE'				=> 'You must enter a message when posting.',
	'EMPTY_REMOTE_DATA'			=> 'File could not be uploaded, please try uploading the file manually.',

	'FLASH_IS_OFF'				=> '[flash] is <em>OFF</em>',
	'FLASH_IS_ON'				=> '[flash] is <em>ON</em>',
	'FLOOD_ERROR'				=> 'You cannot make another post so soon after your last.',
	'FONT_COLOR'				=> 'Font colour',
	'FONT_COLOR_HIDE'			=> 'Hide font colour',
	'FONT_HUGE'					=> 'Huge',
	'FONT_LARGE'				=> 'Large',
	'FONT_NORMAL'				=> 'Normal',
	'FONT_SIZE'					=> 'Font size',
	'FONT_SMALL'				=> 'Small',
	'FONT_TINY'					=> 'Tiny',

	'GENERAL_UPLOAD_ERROR'		=> 'Could not upload attachment to %s.',

	'IMAGES_ARE_OFF'			=> '[img] is <em>OFF</em>',
	'IMAGES_ARE_ON'				=> '[img] is <em>ON</em>',
	'INVALID_FILENAME'			=> '%s is an invalid filename.',

	'LOAD'						=> 'Load',
	'LOAD_DRAFT'				=> 'Load draft',
	'LOAD_DRAFT_EXPLAIN'		=> 'Here you are able to select the draft you want to continue writing. Your current post will be cancelled, all current post contents will be deleted. View, edit and delete drafts within your User Control Panel.',
	'LOGIN_EXPLAIN_BUMP'		=> 'You need to login in order to bump topics within this forum.',
	'LOGIN_EXPLAIN_DELETE'		=> 'You need to login in order to delete posts within this forum.',
	'LOGIN_EXPLAIN_POST'		=> 'You need to login in order to post within this forum.',
	'LOGIN_EXPLAIN_QUOTE'		=> 'You need to login in order to quote posts within this forum.',
	'LOGIN_EXPLAIN_REPLY'		=> 'You need to login in order to reply to topics within this forum.',

	'MAX_FONT_SIZE_EXCEEDED'	=> 'You may only use fonts up to size %d.',
	'MAX_FLASH_HEIGHT_EXCEEDED'	=> array(
		1	=> 'Your flash files may only be up to %d pixel high.',
		2	=> 'Your flash files may only be up to %d pixels high.',
	),
	'MAX_FLASH_WIDTH_EXCEEDED'	=> array(
		1	=> 'Your flash files may only be up to %d pixel wide.',
		2	=> 'Your flash files may only be up to %d pixels wide.',
	),
	'MAX_IMG_HEIGHT_EXCEEDED'	=> array(
		1	=> 'Your images may only be up to %1$d pixel high.',
		2	=> 'Your images may only be up to %1$d pixels high.',
	),
	'MAX_IMG_WIDTH_EXCEEDED'	=> array(
		1	=> 'Your images may only be up to %d pixel wide.',
		2	=> 'Your images may only be up to %d pixels wide.',
	),

	'MESSAGE_BODY_EXPLAIN'		=> array(
		0	=> '', // zero means no limit, so we don't view a message here.
		1	=> 'Enter your message here, it may contain no more than <strong>%d</strong> character.',
		2	=> 'Enter your message here, it may contain no more than <strong>%d</strong> characters.',
	),
	'MESSAGE_DELETED'			=> 'This message has been deleted successfully.',
	'MORE_SMILIES'				=> 'View more smilies',

	'NOTIFY_REPLY'				=> 'Notify me when a reply is posted',
	'NOT_UPLOADED'				=> 'File could not be uploaded.',
	'NO_DELETE_POLL_OPTIONS'	=> 'You cannot delete existing poll options.',
	'NO_PM_ICON'				=> 'No PM icon',
	'NO_POLL_TITLE'				=> 'You have to enter a poll title.',
	'NO_POST'					=> 'The requested post does not exist.',
	'NO_POST_MODE'				=> 'No post mode specified.',
	'NO_TEMP_DIR'				=> 'Temporary folder could not be found or is not writable.',

	'PARTIAL_UPLOAD'			=> 'The uploaded file was only partially uploaded.',
	'PHP_UPLOAD_STOPPED'		=> 'A PHP extension has stopped the file upload.',
	'PHP_SIZE_NA'				=> 'The attachment’s file size is too large.<br />Could not determine the maximum size defined by PHP in php.ini.',
	'PHP_SIZE_OVERRUN'			=> 'The attachment’s file size is too large, the maximum upload size is %1$d %2$s.<br />Please note this is set in php.ini and cannot be overridden.',
	'PLACE_INLINE'				=> 'Place inline',
	'POLL_DELETE'				=> 'Delete poll',
	'POLL_FOR'					=> 'Run poll for',
	'POLL_FOR_EXPLAIN'			=> 'Enter 0 for a never ending poll.',
	'POLL_MAX_OPTIONS'			=> 'Options per user',
	'POLL_MAX_OPTIONS_EXPLAIN'	=> 'This is the number of options each user may select when voting.',
	'POLL_OPTIONS'				=> 'Poll options',
	'POLL_OPTIONS_EXPLAIN'		=> array(
		1	=> 'Place each option on a new line. You may enter <strong>%d</strong> option.',
		2	=> 'Place each option on a new line. You may enter up to <strong>%d</strong> options.',
	),
	'POLL_OPTIONS_EDIT_EXPLAIN'		=> array(
		1	=> 'Place each option on a new line. You may enter <strong>%d</strong> option. If you remove or add options all previous votes will be reset.',
		2	=> 'Place each option on a new line. You may enter up to <strong>%d</strong> options. If you remove or add options all previous votes will be reset.',
	),
	'POLL_QUESTION'				=> 'Poll question',
	'POLL_TITLE_TOO_LONG'		=> 'The poll title must contain fewer than 100 characters.',
	'POLL_TITLE_COMP_TOO_LONG'	=> 'The parsed size of your poll title is too large, consider removing BBCodes or smilies.',
	'POLL_VOTE_CHANGE'			=> 'Allow re-voting',
	'POLL_VOTE_CHANGE_EXPLAIN'	=> 'If enabled users are able to change their vote.',
	'POSTED_ATTACHMENTS'		=> 'Posted attachments',
	'POST_APPROVAL_NOTIFY'		=> 'You will be notified when your post has been approved.',
	'POST_CONFIRMATION'			=> 'Confirmation of post',
	'POST_CONFIRM_EXPLAIN'		=> 'To prevent automated posts the board requires you to enter a confirmation code. The code is displayed in the image you should see below. If you are visually impaired or cannot otherwise read this code please contact the %sBoard Administrator%s.',
	'POST_DELETED'				=> 'This post has been deleted successfully.',
	'POST_EDITED'				=> 'This post has been edited successfully.',
	'POST_EDITED_MOD'			=> 'This post has been edited successfully, but it will need to be approved by a moderator before it is publicly viewable.',
	'POST_GLOBAL'				=> 'Global',
	'POST_ICON'					=> 'Post icon',
	'POST_NORMAL'				=> 'Normal',
	'POST_REVIEW'				=> 'Post review',
	'POST_REVIEW_EDIT'			=> 'Post review',
	'POST_REVIEW_EDIT_EXPLAIN'	=> 'This post has been altered by another user while you were editing it. You may wish to review the current version of this post and adjust your edits.',
	'POST_REVIEW_EXPLAIN'		=> 'At least one new post has been made to this topic. You may wish to review your post in light of this.',
	'POST_STORED'				=> 'This message has been posted successfully.',
	'POST_STORED_MOD'			=> 'This message has been submitted successfully, but it will need to be approved by a moderator before it is publicly viewable.',
	'POST_TOPIC_AS'				=> 'Post topic as',
	'PROGRESS_BAR'				=> 'Progress bar',

	'QUOTE_DEPTH_EXCEEDED'		=> array(
		1	=> 'You may embed only %d quote within each other.',
		2	=> 'You may embed only %d quotes within each other.',
	),
	'QUOTE_NO_NESTING'			=> 'You may not embed quotes within each other.',

	'REMOTE_UPLOAD_TIMEOUT'		=> 'The specified file could not be uploaded because the request timed out.',
	'SAVE'						=> 'Save',
	'SAVE_DATE'					=> 'Saved at',
	'SAVE_DRAFT'				=> 'Save draft',
	'SAVE_DRAFT_CONFIRM'		=> 'Please note that saved drafts only include the subject and the message, any other element will be removed. Do you want to save your draft now?',
	'SMILIES'					=> 'Smilies',
	'SMILIES_ARE_OFF'			=> 'Smilies are <em>OFF</em>',
	'SMILIES_ARE_ON'			=> 'Smilies are <em>ON</em>',
	'STICKY_ANNOUNCE_TIME_LIMIT'=> 'Sticky/Announcement/Global time limit',
	'STICK_TOPIC_FOR'			=> 'Stick topic for',
	'STICK_TOPIC_FOR_EXPLAIN'	=> 'Enter 0 for a never ending Sticky/Announcement/Global. Please note that this number is relative to the date of the post.',
	'STYLES_TIP'				=> 'Tip: Styles can be applied quickly to selected text.',

	'TOO_FEW_CHARS'				=> 'Your message contains too few characters.',
	'TOO_FEW_CHARS_LIMIT'		=> array(
		1	=> 'You need to enter at least %1$d character.',
		2	=> 'You need to enter at least %1$d characters.',
	),
	'TOO_FEW_POLL_OPTIONS'		=> 'You must enter at least two poll options.',
	'TOO_MANY_ATTACHMENTS'		=> 'Cannot add another attachment, %d is the maximum.',
	'TOO_MANY_CHARS'			=> 'Your message contains too many characters.',
	'TOO_MANY_CHARS_LIMIT'		=> array(
		2	=> 'The maximum number of allowed characters is %1$d.',
	),
	'TOO_MANY_POLL_OPTIONS'		=> 'You have tried to enter too many poll options.',
	'TOO_MANY_SMILIES'			=> 'Your message contains too many smilies. The maximum number of smilies allowed is %d.',
	'TOO_MANY_URLS'				=> 'Your message contains too many URLs. The maximum number of URLs allowed is %d.',
	'TOO_MANY_USER_OPTIONS'		=> 'You cannot specify more options per user than existing poll options.',
	'TOPIC_BUMPED'				=> 'Topic has been bumped successfully.',

	'UNAUTHORISED_BBCODE'		=> 'You cannot use certain BBCodes: %s.',
	'UNGLOBALISE_EXPLAIN'		=> 'To switch this topic back from being global to a normal topic, you need to select the forum you wish this topic to be displayed.',
	'UNSUPPORTED_CHARACTERS_MESSAGE'	=> 'Your message contains the following unsupported characters:<br />%s',
	'UNSUPPORTED_CHARACTERS_SUBJECT'	=> 'Your subject contains the following unsupported characters:<br />%s',
	'UPDATE_COMMENT'			=> 'Update comment',
	'URL_INVALID'				=> 'The URL you specified is invalid.',
	'URL_NOT_FOUND'				=> 'The file specified could not be found.',
	'URL_IS_OFF'				=> '[url] is <em>OFF</em>',
	'URL_IS_ON'					=> '[url] is <em>ON</em>',
	'USER_CANNOT_BUMP'			=> 'You cannot bump topics in this forum.',
	'USER_CANNOT_DELETE'		=> 'You cannot delete posts in this forum.',
	'USER_CANNOT_EDIT'			=> 'You cannot edit posts in this forum.',
	'USER_CANNOT_REPLY'			=> 'You cannot reply in this forum.',
	'USER_CANNOT_FORUM_POST'	=> 'You are not able to do posting operations on this forum due to the forum type not supporting it.',

	'VIEW_MESSAGE'				=> '%sView your submitted message%s',
	'VIEW_PRIVATE_MESSAGE'		=> '%sView your submitted private message%s',

	'WRONG_FILESIZE'			=> 'The file is too big, maximum allowed size is %1$d %2$s.',
	'WRONG_SIZE'				=> 'The image must be at least %1$s wide, %2$s high and at most %3$s wide and %4$s high. The submitted image is %5$s wide and %6$s high.',
));
