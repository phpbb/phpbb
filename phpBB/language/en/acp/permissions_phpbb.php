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

/**
*	EXTENSION-DEVELOPERS PLEASE NOTE
*
*	You are able to put your permission sets into your extension.
*	The permissions logic should be added via the 'core.permissions' event.
*	You can easily add new permission categories, types and permissions, by
*	simply merging them into the respective arrays.
*	The respective language strings should be added into a language file, that
*	start with 'permissions_', so they are automatically loaded within the ACP.
*/

$lang = array_merge($lang, array(
	'ACL_CAT_ACTIONS'		=> 'Actions',
	'ACL_CAT_CONTENT'		=> 'Content',
	'ACL_CAT_FORUMS'		=> 'Forums',
	'ACL_CAT_MISC'			=> 'Misc',
	'ACL_CAT_PERMISSIONS'	=> 'Permissions',
	'ACL_CAT_PM'			=> 'Private messages',
	'ACL_CAT_POLLS'			=> 'Polls',
	'ACL_CAT_POST'			=> 'Post',
	'ACL_CAT_POST_ACTIONS'	=> 'Post actions',
	'ACL_CAT_POSTING'		=> 'Posting',
	'ACL_CAT_PROFILE'		=> 'Profile',
	'ACL_CAT_SETTINGS'		=> 'Settings',
	'ACL_CAT_TOPIC_ACTIONS'	=> 'Topic actions',
	'ACL_CAT_USER_GROUP'	=> 'Users &amp; Groups',
));

// User Permissions
$lang = array_merge($lang, array(
	'ACL_U_VIEWPROFILE'	=> 'Can view profiles, memberlist and online list',
	'ACL_U_CHGNAME'		=> 'Can change username',
	'ACL_U_CHGPASSWD'	=> 'Can change password',
	'ACL_U_CHGEMAIL'	=> 'Can change email address',
	'ACL_U_CHGAVATAR'	=> 'Can change avatar',
	'ACL_U_CHGGRP'		=> 'Can change default usergroup',
	'ACL_U_CHGPROFILEINFO'	=> 'Can change profile field information',

	'ACL_U_ATTACH'		=> 'Can attach files',
	'ACL_U_DOWNLOAD'	=> 'Can download files',
	'ACL_U_SAVEDRAFTS'	=> 'Can save drafts',
	'ACL_U_CHGCENSORS'	=> 'Can disable word censors',
	'ACL_U_SIG'			=> 'Can use signature',

	'ACL_U_SENDPM'		=> 'Can send private messages',
	'ACL_U_MASSPM'		=> 'Can send private messages to multiple users',
	'ACL_U_MASSPM_GROUP'=> 'Can send private messages to groups',
	'ACL_U_READPM'		=> 'Can read private messages',
	'ACL_U_PM_EDIT'		=> 'Can edit own private messages',
	'ACL_U_PM_DELETE'	=> 'Can remove private messages from own folder',
	'ACL_U_PM_FORWARD'	=> 'Can forward private messages',
	'ACL_U_PM_EMAILPM'	=> 'Can email private messages',
	'ACL_U_PM_PRINTPM'	=> 'Can print private messages',
	'ACL_U_PM_ATTACH'	=> 'Can attach files in private messages',
	'ACL_U_PM_DOWNLOAD'	=> 'Can download files in private messages',
	'ACL_U_PM_BBCODE'	=> 'Can use BBCode in private messages',
	'ACL_U_PM_SMILIES'	=> 'Can use smilies in private messages',
	'ACL_U_PM_IMG'		=> 'Can use [img] BBCode tag in private messages',
	'ACL_U_PM_FLASH'	=> 'Can use [flash] BBCode tag in private messages',

	'ACL_U_SENDEMAIL'	=> 'Can send emails',
	'ACL_U_SENDIM'		=> 'Can send instant messages',
	'ACL_U_IGNOREFLOOD'	=> 'Can ignore flood limit',
	'ACL_U_HIDEONLINE'	=> 'Can hide online status',
	'ACL_U_VIEWONLINE'	=> 'Can view hidden online users',
	'ACL_U_SEARCH'		=> 'Can search board',
));

// Forum Permissions
$lang = array_merge($lang, array(
	'ACL_F_LIST'		=> 'Can see forum',
	'ACL_F_LIST_TOPICS' => 'Can see topics',
	'ACL_F_READ'		=> 'Can read forum',
	'ACL_F_SEARCH'		=> 'Can search the forum',
	'ACL_F_SUBSCRIBE'	=> 'Can subscribe forum',
	'ACL_F_PRINT'		=> 'Can print topics',
	'ACL_F_EMAIL'		=> 'Can email topics',
	'ACL_F_BUMP'		=> 'Can bump topics',
	'ACL_F_USER_LOCK'	=> 'Can lock own topics',
	'ACL_F_DOWNLOAD'	=> 'Can download files',
	'ACL_F_REPORT'		=> 'Can report posts',

	'ACL_F_POST'		=> 'Can start new topics',
	'ACL_F_STICKY'		=> 'Can post stickies',
	'ACL_F_ANNOUNCE'	=> 'Can post announcements',
	'ACL_F_ANNOUNCE_GLOBAL'	=> 'Can post global announcements',
	'ACL_F_REPLY'		=> 'Can reply to topics',
	'ACL_F_EDIT'		=> 'Can edit own posts',
	'ACL_F_DELETE'		=> 'Can permanently delete own posts',
	'ACL_F_SOFTDELETE'	=> 'Can soft delete own posts<br /><em>Moderators, who have the approve posts permission, can restore soft deleted posts.</em>',
	'ACL_F_IGNOREFLOOD' => 'Can ignore flood limit',
	'ACL_F_POSTCOUNT'	=> 'Increment post counter<br /><em>Please note that this setting only affects new posts.</em>',
	'ACL_F_NOAPPROVE'	=> 'Can post without approval',

	'ACL_F_ATTACH'		=> 'Can attach files',
	'ACL_F_ICONS'		=> 'Can use topic/post icons',
	'ACL_F_BBCODE'		=> 'Can use BBCode',
	'ACL_F_FLASH'		=> 'Can use [flash] BBCode tag',
	'ACL_F_IMG'			=> 'Can use [img] BBCode tag',
	'ACL_F_SIGS'		=> 'Can use signatures',
	'ACL_F_SMILIES'		=> 'Can use smilies',

	'ACL_F_POLL'		=> 'Can create polls',
	'ACL_F_VOTE'		=> 'Can vote in polls',
	'ACL_F_VOTECHG'		=> 'Can change existing vote',
));

// Moderator Permissions
$lang = array_merge($lang, array(
	'ACL_M_EDIT'		=> 'Can edit posts',
	'ACL_M_DELETE'		=> 'Can permanently delete posts',
	'ACL_M_SOFTDELETE'	=> 'Can soft delete posts<br /><em>Moderators, who have the approve posts permission, can restore soft deleted posts.</em>',
	'ACL_M_APPROVE'		=> 'Can approve and restore posts',
	'ACL_M_REPORT'		=> 'Can close and delete reports',
	'ACL_M_CHGPOSTER'	=> 'Can change post author',

	'ACL_M_MOVE'	=> 'Can move topics',
	'ACL_M_LOCK'	=> 'Can lock topics',
	'ACL_M_SPLIT'	=> 'Can split topics',
	'ACL_M_MERGE'	=> 'Can merge topics',

	'ACL_M_INFO'		=> 'Can view post details',
	'ACL_M_WARN'		=> 'Can issue warnings<br /><em>This setting is only assigned globally. It is not forum based.</em>', // This moderator setting is only global (and not local)
	'ACL_M_PM_REPORT'	=> 'Can close and delete reports of private messages<br /><em>This setting is only assigned globally. It is not forum based.</em>', // This moderator setting is only global (and not local)
	'ACL_M_BAN'			=> 'Can manage bans<br /><em>This setting is only assigned globally. It is not forum based.</em>', // This moderator setting is only global (and not local)
));

// Admin Permissions
$lang = array_merge($lang, array(
	'ACL_A_BOARD'		=> 'Can alter board settings/check for updates',
	'ACL_A_SERVER'		=> 'Can alter server/communication settings',
	'ACL_A_JABBER'		=> 'Can alter Jabber settings',
	'ACL_A_PHPINFO'		=> 'Can view php settings',

	'ACL_A_FORUM'		=> 'Can manage forums',
	'ACL_A_FORUMADD'	=> 'Can add new forums',
	'ACL_A_FORUMDEL'	=> 'Can delete forums',
	'ACL_A_PRUNE'		=> 'Can prune forums',

	'ACL_A_ICONS'		=> 'Can alter topic/post icons and smilies',
	'ACL_A_WORDS'		=> 'Can alter word censors',
	'ACL_A_BBCODE'		=> 'Can define BBCode tags',
	'ACL_A_ATTACH'		=> 'Can alter attachment related settings',

	'ACL_A_USER'		=> 'Can manage users<br /><em>This also includes seeing the users browser agent within the viewonline list.</em>',
	'ACL_A_USERDEL'		=> 'Can delete/prune users',
	'ACL_A_GROUP'		=> 'Can manage groups',
	'ACL_A_GROUPADD'	=> 'Can add new groups',
	'ACL_A_GROUPDEL'	=> 'Can delete groups',
	'ACL_A_RANKS'		=> 'Can manage ranks',
	'ACL_A_PROFILE'		=> 'Can manage custom profile fields',
	'ACL_A_NAMES'		=> 'Can manage disallowed names',
	'ACL_A_BAN'			=> 'Can manage bans',

	'ACL_A_VIEWAUTH'	=> 'Can view permission masks',
	'ACL_A_AUTHGROUPS'	=> 'Can alter permissions for individual groups',
	'ACL_A_AUTHUSERS'	=> 'Can alter permissions for individual users',
	'ACL_A_FAUTH'		=> 'Can alter forum permission class',
	'ACL_A_MAUTH'		=> 'Can alter moderator permission class',
	'ACL_A_AAUTH'		=> 'Can alter admin permission class',
	'ACL_A_UAUTH'		=> 'Can alter user permission class',
	'ACL_A_ROLES'		=> 'Can manage roles',
	'ACL_A_SWITCHPERM'	=> 'Can use others permissions',

	'ACL_A_STYLES'		=> 'Can manage styles',
	'ACL_A_EXTENSIONS'	=> 'Can manage extensions',
	'ACL_A_VIEWLOGS'	=> 'Can view logs',
	'ACL_A_CLEARLOGS'	=> 'Can clear logs',
	'ACL_A_MODULES'		=> 'Can manage modules',
	'ACL_A_LANGUAGE'	=> 'Can manage language packs',
	'ACL_A_EMAIL'		=> 'Can send mass email',
	'ACL_A_BOTS'		=> 'Can manage bots',
	'ACL_A_REASONS'		=> 'Can manage report/denial reasons',
	'ACL_A_BACKUP'		=> 'Can backup/restore database',
	'ACL_A_SEARCH'		=> 'Can manage search backends and settings',
));
