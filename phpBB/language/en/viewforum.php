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
	'ACTIVE_TOPICS'			=> 'Active topics',
	'ANNOUNCEMENTS'			=> 'Announcements',

	'FORUM_PERMISSIONS'		=> 'Forum permissions',

	'ICON_ANNOUNCEMENT'		=> 'Announcement',
	'ICON_STICKY'			=> 'Sticky',

	'LOGIN_NOTIFY_FORUM'	=> 'You have been notified about this forum, please login to view it.',

	'MARK_TOPICS_READ'		=> 'Mark topics read',

	'NEW_POSTS_HOT'			=> 'New posts [ Popular ]',	// Not used anymore
	'NEW_POSTS_LOCKED'		=> 'New posts [ Locked ]',	// Not used anymore
	'NO_NEW_POSTS_HOT'		=> 'No new posts [ Popular ]',	// Not used anymore
	'NO_NEW_POSTS_LOCKED'	=> 'No new posts [ Locked ]',	// Not used anymore
	'NO_READ_ACCESS'		=> 'You do not have the required permissions to read topics within this forum.',
	'NO_UNREAD_POSTS_HOT'		=> 'No unread posts [ Popular ]',
	'NO_UNREAD_POSTS_LOCKED'	=> 'No unread posts [ Locked ]',

	'POST_FORUM_LOCKED'		=> 'Forum is locked',

	'TOPICS_MARKED'			=> 'The topics for this forum have now been marked read.',

	'UNREAD_POSTS_HOT'		=> 'Unread posts [ Popular ]',
	'UNREAD_POSTS_LOCKED'	=> 'Unread posts [ Locked ]',

	'VIEW_FORUM'			=> 'View forum',
	'VIEW_FORUM_TOPICS'		=> array(
		1	=> '%d topic',
		2	=> '%d topics',
	),
));
