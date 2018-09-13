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

if (!defined('IN_PHPBB'))
{
	exit;
}

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
	'PM_AUTHOR_FOE'						=> '%s is your foe',
	'PM_DISABLED'						=> 'Private messaging has been disabled on this board.',
	'PM_FROM'							=> 'From',
	'PM_FROM_REMOVED_AUTHOR'			=> 'This message was sent by a user no longer registered.',
	'PM_ICON'							=> 'PM icon',
	'PM_INBOX'							=> 'Inbox',
	'PM_MARK_ALL_READ'					=> 'Mark all messages read',
	'PM_MARK_ALL_READ_SUCCESS'			=> 'All private messages in this folder have been marked read',
	'PM_MESSAGE'						=> 'Type your message',
	'PM_NO_USERS'						=> 'The requested users to be added do not exist.',
	'PM_OUTBOX'							=> 'Outbox',
	'PM_SENTBOX'						=> 'Sent messages',
	'PM_SUBJECT'						=> 'Message subject',
	'PM_TITLE'							=> 'Enter message subject',
	'PM_TO'								=> 'Send to',
	'PM_TOOLS'							=> 'Message tools',
	'PM_USERS_REMOVED_NO_PERMISSION'	=> 'Some users couldn’t be added as they do not have permission to read private messages.',
	'PM_USERS_REMOVED_NO_PM'			=> 'Some users couldn’t be added as they have disabled private message receipt.',
	'AVATAR'							=> 'Avatar',
	'BACK_TO_FOLDERS'					=> '&lt; Back to folders',
	'SELECT_FOLDER'						=> 'To see messages, select folder on the left.',
	'SELECT_THREAD'						=> 'To see messages, select thread on the left.',
));
