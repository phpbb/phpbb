<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : posting.php [ English ]
// STARTED   : Sat Dec 16, 2000
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

// DO NOT CHANGE
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

$lang += array(
	'ADD_ATTACHMENT'			=> 'Add an Attachment',
	'ADD_ATTACHMENT_EXPLAIN'	=> 'If you wish to attach one or more files enter the details below',
	'ADD_FILE'					=> 'Add File',
	'ADD_POLL'					=> 'Add Poll',
	'ADD_POLL_EXPLAIN'			=> 'If you do not want to add a poll to your topic leave the fields blank',
	'ALREADY_DELETED'			=> 'Sorry but this message is already deleted.',
	'ATTACHMENT_PHP_SIZE_NA'	=> 'The attachment is too big.<br />Could not get determine the maximum size defined by PHP in php.ini.',
	'ATTACHMENT_PHP_SIZE_OVERRUN'	=> 'The attachment is too big, maximum upload size is %d MB.<br />Please note this is set in php.ini and cannot be overriden.',
	'ATTACHMENT_TOO_BIG'		=> 'The attachment is too big, maximum allowed size is %1d %2s',
	'ATTACH_QUOTA_REACHED'		=> 'Sorry, the board attachment quota has been reached.',
	'ATTACH_SIG'				=> 'Attach a signature (signatures can be altered via the UCP)',

	'BBCODE_A_HELP'				=> 'Close all open bbCode tags',
	'BBCODE_B_HELP'				=> 'Bold text: [b]text[/b]  (alt+b)',
	'BBCODE_C_HELP'				=> 'Code display: [code]code[/code]  (alt+c)',
	'BBCODE_E_HELP'				=> 'List: Add list element',
	'BBCODE_F_HELP'				=> 'Font size: [size=x-small]small text[/size]',
	'BBCODE_IS_OFF'				=> '%sBBCode%s is <u>OFF</u>',
	'BBCODE_IS_ON'				=> '%sBBCode%s is <u>ON</u>',
	'BBCODE_I_HELP'				=> 'Italic text: [i]text[/i]  (alt+i)',
	'BBCODE_L_HELP'				=> 'List: [list]text[/list]  (alt+l)',
	'BBCODE_O_HELP'				=> 'Ordered list: [list=]text[/list]  (alt+o)',
	'BBCODE_P_HELP'				=> 'Insert image: [img]http://image_url[/img]  (alt+p)',
	'BBCODE_Q_HELP'				=> 'Quote text: [quote]text[/quote]  (alt+q)',
	'BBCODE_S_HELP'				=> 'Font color: [color=red]text[/color]  Tip: you can also use color=#FF0000',
	'BBCODE_U_HELP'				=> 'Underline text: [u]text[/u]  (alt+u)',
	'BBCODE_W_HELP'				=> 'Insert URL: [url]http://url[/url] or [url=http://url]URL text[/url]  (alt+w)',
	'BUMP_ERROR'				=> 'You cannot bump this topic so soon after the last post.',

	'CANNOT_DELETE_REPLIED'		=> 'Sorry but you may only delete posts which have not been replied to.',
	'CANNOT_EDIT_POST_LOCKED'	=> 'This post has been locked. You can no longer edit that post.',
	'CANNOT_EDIT_TIME'			=> 'You can no longer edit or delete that post',
	'CANNOT_POST_ANNOUNCE'		=> 'Sorry but you cannot post announcements.',
	'CANNOT_POST_NEWS'			=> 'Sorry but you cannot post news topics.',
	'CANNOT_POST_STICKY'		=> 'Sorry but you cannot post sticky topics.',
	'CHANGE_TOPIC_TO'			=> 'Change topic type to',
	'CLOSE_TAGS'				=> 'Close Tags',
	'CLOSE_WINDOW'				=> 'Close Window',
	'CURRENT_TOPIC'				=> 'Current Topic',

	'DAYS'						=> 'Days',
	'DELETE_FILE'				=> 'Delete File',
	'DELETE_MESSAGE'			=> 'Delete Message',
	'DELETE_MESSAGE_CONFIRM'	=> 'Are you sure you want to delete this message?',
	'DELETE_OWN_POSTS'			=> 'Sorry but you can only delete your own posts.',
	'DELETE_POST'				=> 'Delete',
	'DELETE_POST_CONFIRM'		=> 'Are you sure you want to delete this message?',
	'DELETE_POST_WARN'			=> 'Once deleted the post cannot be recovered',
	'DISABLE_BBCODE'			=> 'Disable BBCode',
	'DISABLE_HTML'				=> 'Disable HTML',
	'DISABLE_MAGIC_URL'			=> 'Do not automatically parse URLs',
	'DISABLE_SMILIES'			=> 'Disable Smilies',
	'DISALLOWED_EXTENSION'		=> 'The Extension %s is not allowed',
	'DRAFT_LOADED'				=> 'Draft loaded into posting area, you may want to finish your post now.<br />Your Draft will be deleted after submitting this post.',
	'DRAFT_SAVED'				=> 'Draft successfully saved.',
	'DRAFT_TITLE'				=> 'Draft Title',

	'EDIT_POST'					=> 'Edit Post',
	'EDIT_REASON'				=> 'Reason for editing this post',
	'EMOTICONS'					=> 'Emoticons',
	'EMPTY_MESSAGE'				=> 'You must enter a message when posting.',
	'ERROR_IMAGESIZE'			=> 'The Image you tried to attach is too big, maximum allowed dimensions are %1d px X %2d px.',

	'FLASH_IS_OFF'				=> '[flash] is <u>ON</u>',
	'FLASH_IS_ON'				=> '[flash] is <u>ON</u>',
	'FLOOD_ERROR'				=> 'You cannot make another post so soon after your last.',
	'FONT_COLOR'				=> 'Font color',
	'FONT_HUGE'					=> 'Huge',
	'FONT_LARGE'				=> 'Large',
	'FONT_NORMAL'				=> 'Normal',
	'FONT_SIZE'					=> 'Font size',
	'FONT_SMALL'				=> 'Small',
	'FONT_TINY'					=> 'Tiny',

	'GENERAL_UPLOAD_ERROR'		=> 'Could not upload Attachment to %s',

	'HTML_IS_OFF'				=> 'HTML is <u>OFF</u>',
	'HTML_IS_ON'				=> 'HTML is <u>ON</u>',

	'IMAGES_ARE_OFF'			=> '[img] is <u>OFF</u>',
	'IMAGES_ARE_ON'				=> '[img] is <u>ON</u>',
	'INVALID_FILENAME'			=> '%s is an invalid filename',

	'KARMA_LEVEL'				=> 'Karma Level',

	'LOAD'						=> 'Load',
	'LOAD_DRAFT'				=> 'Load Draft',
	'LOAD_DRAFT_EXPLAIN'		=> 'Here you are able to select the draft you want to continue writing. Your current post will be canceled, all current post contents will be deleted. View, edit and delete drafts within your User Control Panel.',

	'MESSAGE_BODY_EXPLAIN'		=> 'Enter your message here, it may contain no more than <b>%d</b> characters.',
	'MESSAGE_DELETED'			=> 'Your message has been deleted successfully',
	'MORE_EMOTICONS'			=> 'View more Emoticons',

	'NOTIFY_REPLY'				=> 'Send me an email when a reply is posted',
	'NO_DELETE_POLL_OPTIONS'	=> 'You cannot delete existing poll options',
	'NO_POLL_TITLE'				=> 'You have to enter a poll title',
	'NO_POST'					=> 'The requested post does not exist.',
	'NO_POST_MODE'				=> 'No post mode specified',

	'PLACE_INLINE'				=> 'Place Inline',
	'POLL_DELETE'				=> 'Delete Poll',
	'POLL_FOR'					=> 'Run poll for',
	'POLL_FOR_EXPLAIN'			=> 'Enter 0 or leave blank for a never ending poll',
	'POLL_MAX_OPTIONS'			=> 'Options per user',
	'POLL_MAX_OPTIONS_EXPLAIN'	=> 'This is the number of options each user may select when voting.',
	'POLL_OPTIONS'				=> 'Poll options',
	'POLL_OPTIONS_EXPLAIN'		=> 'Place each option on a new line. You may enter up to <b>%d</b> options',
	'POLL_QUESTION'				=> 'Poll question',
	'POSTED_ATTACHMENTS'		=> 'Posted attachments',
	'POST_ANNOUNCEMENT'			=> 'Announce',
	'POST_DELETED'				=> 'Your message has been deleted successfully',
	'POST_GLOBAL'				=> 'Global',
	'POST_ICON'					=> 'Post icon',
	'POST_NORMAL'				=> 'Normal',
	'POST_REPLY'				=> 'Post a reply',
	'POST_REVIEW'				=> 'Post Review',
	'POST_REVIEW_EXPLAIN'		=> 'At least one new post has been made to this topic. You may wish to review your post inlight of this.',
	'POST_STICKY'				=> 'Sticky',
	'POST_STORED'				=> 'Your message has been posted successfully',
	'POST_STORED_MOD'			=> 'Your message has been saved but requires approval',
	'POST_TOPIC'				=> 'Post a new topic',
	'POST_TOPIC_AS'				=> 'Post topic as',

	'QUOTE_DEPTH_EXCEEDED'		=> 'You may embed only %1$d quotes within each other.',

	'SAVE'						=> 'Save',
	'SAVE_DATE'					=> 'Saved at',
	'SMILIES_ARE_OFF'			=> 'Smilies are <u>OFF</u>',
	'SMILIES_ARE_ON'			=> 'Smilies are <u>ON</u>',
	'STICKY_ANNOUNCE_TIME_LIMIT'=> 'Sticky/Announcement time limit',
	'STICK_TOPIC_FOR'			=> 'Stick topic for',
	'STICK_TOPIC_FOR_EXPLAIN'	=> 'Enter 0 or leave blank for a never ending Sticky/Announcement',
	'STYLES_TIP'				=> 'Tip: Styles can be applied quickly to selected text',

	'TOO_FEW_CHARS'				=> 'Your message contains too few characters.',
	'TOO_FEW_POLL_OPTIONS'		=> 'You must enter at least two poll options',
	'TOO_MANY_ATTACHMENTS'		=> 'Cannot add another attacment, %d is the maxmimum.',
	'TOO_MANY_CHARS'			=> 'Your message contains too many characters.',
	'TOO_MANY_POLL_OPTIONS'		=> 'You have tried to enter too many poll options',
	'TOO_MANY_SMILIES'			=> 'Your message contains too many emoticons.',
	'TOO_MANY_USER_OPTIONS'		=> 'You cannot specify more Options per User than existing poll options',
	'TOPIC_BUMPED'				=> 'Topic has been bumped successfully',
	'TOPIC_REVIEW'				=> 'Topic review',

	'UNAUTHORISED_BBCODE'		=> 'You cannot use certain bbcodes: ',
	'UPDATE_COMMENT'			=> 'Update comment',
	'USER_CANNOT_BUMP'			=> 'You cannot bump topics in this forum',
	'USER_CANNOT_DELETE'		=> 'You cannot delete posts in this forum',
	'USER_CANNOT_EDIT'			=> 'You cannot edit posts in this forum',
	'USER_CANNOT_QUOTE'			=> 'You cannot quote posts in this forum',
	'USER_CANNOT_REPLY'			=> 'You cannot reply in this forum',

	'VIEW_MESSAGE'				=> 'Click %sHere%s to view your message',
);

?>