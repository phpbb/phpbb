<?php
/** 
*
* acp common [English]
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

// Common
$lang = array_merge($lang, array(
	'ACP_ADMIN_LOGS'			=> 'Admin Log',
	'ACP_ATTACHMENTS'			=> 'Attachments',
	'ACP_ATTACHMENT_SETTINGS'	=> 'Attachment Settings',
	'ACP_AUTH_SETTINGS'			=> 'Authentication',
	'ACP_AUTOMATION'			=> 'Automation',
	'ACP_AVATAR_SETTINGS'		=> 'Avatar Settings',
	'ACP_BAN'					=> 'Banning',
	'ACP_BAN_EMAILS'			=> 'Ban Emails',
	'ACP_BAN_IPS'				=> 'Ban IPs',
	'ACP_BAN_USERNAMES'			=> 'Ban Usernames',
	'ACP_BBCODES'				=> 'BBCodes',
	'ACP_BOARD_DEFAULTS'		=> 'Board Defaults',
	'ACP_BOARD_MANAGEMENT'		=> 'Board Management',
	'ACP_BOARD_SETTINGS'		=> 'Board Settings',
	'ACP_BOTS'					=> 'Spiders/Robots',
	'ACP_CAT_DATABASE'			=> 'Database',
	'ACP_CAT_DOT_MODS'			=> '.Mods',
	'ACP_CAT_FORUMS'			=> 'Forums',
	'ACP_CAT_GENERAL'			=> 'General',
	'ACP_CAT_MAINTANENCE'		=> 'Maintanence',
	'ACP_CAT_PERMISSIONS'		=> 'Permissions',
	'ACP_CAT_POSTING'			=> 'Posting',
	'ACP_CAT_STYLES'			=> 'Styles',
	'ACP_CAT_SYSTEM'			=> 'System',
	'ACP_CAT_USERGROUP'			=> 'Users and Groups',
	'ACP_CAT_USERS'				=> 'Users',
	'ACP_CLIENT_COMMUNICATION'	=> 'Client Communication',
	'ACP_COOKIE_SETTINGS'		=> 'Cookie Settings',
	'ACP_CRITICAL_LOGS'			=> 'Error Log',
	'ACP_CUSTOM_PROFILE_FIELDS'	=> 'Custom Profile Fields',
	'ACP_DISALLOW'				=> 'Disallow',
	'ACP_DISALLOW_USERNAMES'	=> 'Disallow Usernames',
	'ACP_EMAIL_SETTINGS'		=> 'Email Settings',
	'ACP_EXTENSION_GROUPS'		=> 'Manage Extension Groups',
	'ACP_FORUM_LOGS'			=> 'Forum Logs',
	'ACP_FORUM_MANAGEMENT'		=> 'Forum Management',
	'ACP_GENERAL_CONFIGURATION'	=> 'General Configuration',
	'ACP_GENERAL_TASKS'			=> 'General Tasks',
	'ACP_GROUPS'				=> 'Groups',
	'ACP_GROUPS_MANAGE'			=> 'Manage Groups',
	'ACP_GROUPS_MANAGEMENT'		=> 'Group Management',
	'ACP_ICONS'					=> 'Topic Icons',
	'ACP_ICONS_SMILIES'			=> 'Topic Icons/Smilies',
	'ACP_INDEX'					=> 'Admin index',
	'ACP_JABBER_SETTINGS'		=> 'Jabber Settings',
	'ACP_LANGUAGE'				=> 'Language Management',
	'ACP_LANGUAGE_PACKS'		=> 'Language Packs',
	'ACP_LOAD_SETTINGS'			=> 'Load Settings',
	'ACP_LOGGING'				=> 'Logging',
	'ACP_MAIN'					=> 'Admin index',
	'ACP_MANAGE_EXTENSIONS'		=> 'Manage Extensions',
	'ACP_MANAGE_FORUMS'			=> 'Manage Forums',
	'ACP_MANAGE_RANKS'			=> 'Manage Ranks',
	'ACP_MANAGE_USERS'			=> 'Manage Users',
	'ACP_MASS_EMAIL'			=> 'Mass Email',
	'ACP_MESSAGES'				=> 'Messages',
	'ACP_MESSAGE_SETTINGS'		=> 'Message Settings',
	'ACP_MODULE_MANAGEMENT'		=> 'Module Management',
	'ACP_MOD_LOGS'				=> 'Moderator Log',
	'ACP_ORPHAN_ATTACHMENTS'	=> 'Orphan Attachments',
	'ACP_PERMISSION_SETTINGS'	=> 'Permission Settings',
	'ACP_PHP_INFO'				=> 'PHP Information',
	'ACP_PRUNE_FORUMS'			=> 'Prune Forums',
	'ACP_PRUNE_USERS'			=> 'Prune Users',
	'ACP_PRUNING'				=> 'Pruning',
	'ACP_RANKS'					=> 'Ranks',
	'ACP_SERVER_CONFIGURATION'	=> 'Server Configuration',
	'ACP_SERVER_SETTINGS'		=> 'Server Settings',
	'ACP_SMILIES'				=> 'Smilies',
	'ACP_STYLE_MANAGEMENT'		=> 'Style Management',
	'ACP_USER_ATTACH'			=> 'Attachments',
	'ACP_USER_AVATAR'			=> 'Avatar',
	'ACP_USER_FEEDBACK'			=> 'Feedback',
	'ACP_USER_GROUPS'			=> 'Groups',
	'ACP_USER_MANAGEMENT'		=> 'User Management',
	'ACP_USER_OVERVIEW'			=> 'Overview',
	'ACP_USER_PERM'				=> 'Permissions',
	'ACP_USER_PREFS'			=> 'Preferences',
	'ACP_USER_PROFILE'			=> 'Profile',
	'ACP_USER_SECURITY'			=> 'User Security',
	'ACP_USER_SIG'				=> 'Signature',
	'ACP_WORDS'					=> 'Word Censoring',

	'ACTION'				=> 'Action',
	'ACTIVATE'				=> 'Activate',
	'ADD'					=> 'Add',
	'ADMIN'					=> 'Administration',
	'ADMIN_INDEX'			=> 'Admin Index',
	'ADMIN_PANEL'			=> 'Administration Control Panel',

	'BACK'					=> 'Back',

	'COLOUR_SWATCH'			=> 'Web-safe colour swatch',
	'CONFIG_UPDATED'		=> 'Configuration updated successfully',
	'CONFIRM_OPERATION'		=> 'Are you sure you wish to carry out this operation?',

	'DEACTIVATE'			=> 'Deactivate',
	'DIMENSIONS'			=> 'Dimensions',
	'DISABLE'				=> 'Disable',
	'DISPLAY'				=> 'Display',
	'DOWNLOAD'				=> 'Download',
	'DOWNLOAD_AS'			=> 'Download as',

	'EDIT'					=> 'Edit',
	'ENABLE'				=> 'Enable',

	'FORUM_INDEX'			=> 'Forum Index',

	'GENERAL_OPTIONS'		=> 'General Options',
	'GENERAL_SETTINGS'		=> 'General Settings',

	'INSTALL'				=> 'Install',
	'IP'					=> 'User IP',
	'IP_HOSTNAME'			=> 'IP addresses or hostnames',

	'LOGGED_IN_AS'			=> 'You are logged in as:',
	'LOGIN_ADMIN'			=> 'To administer the board you must be an authenticated user.',
	'LOGIN_ADMIN_CONFIRM'	=> 'To administer the board you must re-authenticate yourself.',
	'LOGIN_ADMIN_SUCCESS'	=> 'You have successfully authenticated and will now be redirected to the Administration Control Panel',
	'LOOK_UP_FORUM'			=> 'Select a Forum',

	'MANAGE'				=> 'Manage',
	'MOVE_DOWN'				=> 'Move Down',
	'MOVE_UP'				=> 'Move Up',

	'NOTIFY'				=> 'Notification',
	'NO_ADMIN'				=> 'You are not authorised to administer this board.',
	'NO_EMAILS_DEFINED'		=> 'No valid email addresses found',
	'NO_IPS_DEFINED'		=> 'No IPs or Hostnames defined',

	'OFF'					=> 'OFF',
	'ON'					=> 'ON',

	'REMIND'				=> 'Remind',
	'REORDER'				=> 'Reorder',
	'RETURN_TO'				=> 'Return to ...',

	'SELECT_OPTION'			=> 'Select option',

	'UCP'					=> 'User Control Panel',
	'USERNAMES_EXPLAIN'		=> 'Place each username on a seperate line',
	'USER_CONTROL_PANEL'	=> 'User Control Panel',

	'WARNING'				=> 'Warning',
));

// PHP info
$lang = array_merge($lang, array(
	'ACP_PHP_INFO_EXPLAIN'	=> 'This page lists information on the version of PHP installed on this server. It includes details of loaded modules, available variables and default settings. This information may be useful when diagnosing problems. Please be aware that some hosting companies will limit what information is displayed here for security reasons. You are advised to not give out any details on this page except when asked by support or other Team Member on the support forums.',
));

// Logs
$lang = array_merge($lang, array(
	'ACP_ADMIN_LOGS_EXPLAIN'	=> 'This lists all the actions carried out by board administrators. You can sort by username, date, IP or action. If you have appropriate permissions you can also clear individual operations or the log as a whole.',
	'ACP_CRITICAL_LOGS_EXPLAIN'	=> 'This lists the actions carried out by the board itself. These log provides you with information you are able to use for solving specific problems, for example non-delivery of emails. You can sort by username, date, IP or action. If you have appropriate permissions you can also clear individual operations or the log as a whole.',
	'ACP_MOD_LOGS_EXPLAIN'		=> 'This lists the actions carried out by board moderators, select a forum from the drop down list. You can sort by username, date, IP or action. If you have appropriate permissions you can also clear individual operations or the log as a whole.',
	'ALL_ENTRIES'				=> 'All entries',

	'DISPLAY_LOG'	=> 'Display entries from previous',

	'NO_ENTRIES'	=> 'No log entries for this period',

	'SORT_IP'		=> 'IP address',
	'SORT_DATE'		=> 'Date',
	'SORT_ACTION'	=> 'Log action',
));

// Index page
$lang = array_merge($lang, array(
	'ADMIN_INTRO'				=> 'Thank you for choosing phpBB as your forum solution. This screen will give you a quick overview of all the various statistics of your board. The links on the left hand side of this screen allow you to control every aspect of your forum experience. Each page will have instructions on how to use the tools.',
	'ADMIN_LOG'					=> 'Logged administrator actions',
	'ADMIN_LOG_INDEX_EXPLAIN'	=> 'This gives an overview of the last five actions carried out by board administrators. A full copy of the log can be viewed from the appropriate menu item to the left.',
	'AVATAR_DIR_SIZE'			=> 'Avatar directory size',

	'BOARD_STARTED'		=> 'Board started',

	'DATABASE_SIZE'		=> 'Database size',

	'FILES_PER_DAY'		=> 'Attachments per day',
	'FORUM_STATS'		=> 'Forum Statistics',

	'GZIP_COMPRESSION'	=> 'Gzip compression',

	'INACTIVE_USERS'			=> 'Inactive Users',
	'INACTIVE_USERS_EXPLAIN'	=> 'This is a list of users who have registered but whos accounts are inactive. You can activate, delete or remind (by sending an email) these users if you wish.',

	'NO_INACTIVE_USERS'	=> 'No inactive users',
	'NOT_AVAILABLE'		=> 'Not available',
	'NUMBER_FILES'		=> 'Number of Attachments',
	'NUMBER_POSTS'		=> 'Number of posts',
	'NUMBER_TOPICS'		=> 'Number of topics',
	'NUMBER_USERS'		=> 'Number of users',

	'POSTS_PER_DAY'		=> 'Posts per day',

	'RESET_DATE'		=> 'Reset Date',
	'RESET_ONLINE'		=> 'Reset Online',
	'RESYNC_POSTCOUNTS'	=> 'Resync Postcounts',
	'RESYNC_STATS'		=> 'Resync Stats',

	'STATISTIC'			=> 'Statistic',

	'TOPICS_PER_DAY'	=> 'Topics per day',

	'UPLOAD_DIR_SIZE'	=> 'Upload directory size',
	'USERS_PER_DAY'		=> 'Users per day',

	'VALUE'				=> 'Value',

	'WELCOME_PHPBB'			=> 'Welcome to phpBB',
));

// Log
$lang = array_merge($lang, array(
	'LOG_ATTACH_EXT_ADD'		=> '<b>Added or edited attachment extension</b><br />&#187; %s',
	'LOG_ATTACH_EXT_DEL'		=> '<b>Removed attachment extension</b><br />&#187; %s',
	'LOG_ATTACH_EXT_UPDATE'		=> '<b>Updated attachment extension</b><br />&#187; %s',
	'LOG_ATTACH_EXTGROUP_ADD'	=> '<b>Added extension group</b><br />&#187; %s',
	'LOG_ATTACH_EXTGROUP_EDIT'	=> '<b>Edited extension group</b><br />&#187; %s',
	'LOG_ATTACH_EXTGROUP_DEL'	=> '<b>Removed extension group</b><br />&#187; %s',
	'LOG_ATTACH_FILEUPLOAD'		=> '<b>Orphan File uploaded to Post Number %1$d - %2$s</b>',
	'LOG_ATTACH_ORPHAN_DEL'		=> '<b>Orphan Files deleted</b><br />&#187; %s',

	'LOG_BAN_EXCLUDE_USER'	=> '<b>Excluded user from ban</b> for reason "<i>%s</i>"<br />&#187; %s ',
	'LOG_BAN_EXCLUDE_IP'	=> '<b>Excluded ip from ban</b> for reason "<i>%s</i>"<br />&#187; %s ',
	'LOG_BAN_EXCLUDE_EMAIL' => '<b>Excluded email from ban</b> for reason "<i>%s</i>"<br />&#187; %s ',
	'LOG_BAN_USER'			=> '<b>Banned User</b> for reason "<i>%s</i>"<br />&#187; %s ',
	'LOG_BAN_IP'			=> '<b>Banned ip</b> for reason "<i>%s</i>"<br />&#187; %s',
	'LOG_BAN_EMAIL'			=> '<b>Banned email</b> for reason "<i>%s</i>"<br />&#187; %s',
	'LOG_UNBAN_USER'		=> '<b>Unbanned user</b><br />&#187; %s',
	'LOG_UNBAN_IP'			=> '<b>Unbanned ip</b><br />&#187; %s',
	'LOG_UNBAN_EMAIL'		=> '<b>Unbanned email</b><br />&#187; %s',

	'LOG_BBCODE_ADD'		=> '<b>Added new BBCode</b><br />&#187; %s',
	'LOG_BBCODE_EDIT'		=> '<b>Edited BBCode</b><br />&#187; %s',
	'LOG_BBCODE_DELETE'		=> '<b>Deleted BBCode</b><br />&#187; %s',

	'LOG_BOT_ADDED'		=> '<b>New bot added</b><br />&#187; %s',
	'LOG_BOT_DELETE'	=> '<b>Deleted bot</b><br />&#187; %s',
	'LOG_BOT_UPDATED'	=> '<b>Existing bot updated</b><br />&#187; %s',

	'LOG_CLEAR_ADMIN'		=> '<b>Cleared admin log</b>',
	'LOG_CLEAR_CRITICAL'	=> '<b>Cleared error log</b>',
	'LOG_CLEAR_MOD'			=> '<b>Cleared moderator log</b>',
	'LOG_CLEAR_USER'		=> '<b>Cleared user log</b><br />&#187; %s',

	'LOG_CONFIG_ATTACH'		=> '<b>Altered attachment settings</b>',
	'LOG_CONFIG_AUTH'		=> '<b>Altered authentication settings</b>',
	'LOG_CONFIG_AVATAR'		=> '<b>Altered avatar settings</b>',
	'LOG_CONFIG_COOKIE'		=> '<b>Altered cookie settings</b>',
	'LOG_CONFIG_DEFAULT'	=> '<b>Altered board defaults</b>',
	'LOG_CONFIG_EMAIL'		=> '<b>Altered email settings</b>',
	'LOG_CONFIG_LOAD'		=> '<b>Altered load settings</b>',
	'LOG_CONFIG_MESSAGE'	=> '<b>Altered private message settings</b>',
	'LOG_CONFIG_SERVER'		=> '<b>Altered server settings</b>',
	'LOG_CONFIG_SETTINGS'	=> '<b>Altered board settings</b>',

	'LOG_DISALLOW_ADD'		=> '<b>Added disallowed username</b><br />&#187; %s',
	'LOG_DISALLOW_DELETE'	=> '<b>Deleted disallowed username</b>',

	'LOG_DOWNLOAD_EXCLUDE_IP'	=> '<b>Exluded ip/hostname from download list</b><br />&#187; %s',
	'LOG_DOWNLOAD_IP'			=> '<b>Added ip/hostname to download list</b><br />&#187; %s',
	'LOG_DOWNLOAD_REMOVE_IP'	=> '<b>Removed ip/hostname from download list</b><br />&#187; %s',

	'LOG_ERROR_JABBER'		=> '<b>Jabber Error</b><br />&#187; %s',
	'LOG_ERROR_EMAIL'		=> '<b>Email Error</b><br />&#187; %s',
	
	'LOG_FORUM_ADD'							=> '<b>Created new forum</b><br />&#187; %s',
	'LOG_FORUM_EDIT'						=> '<b>Edited forum details</b><br />&#187; %s',
	'LOG_FORUM_MOVE_DOWN'					=> '<b>Moved forum</b> %s <b>below</b> %s',
	'LOG_FORUM_MOVE_UP'						=> '<b>Moved forum</b> %s <b>above</b> %s',
	'LOG_FORUM_SYNC'						=> '<b>Re-synchronised forum</b><br />&#187; %s',
	'LOG_FORUM_DEL_POSTS'					=> '<b>Deleted forum and its messages</b><br />&#187; %s',
	'LOG_FORUM_DEL_FORUMS'					=> '<b>Deleted forum and its subforums</b><br />&#187; %s',
	'LOG_FORUM_DEL_POSTS_MOVE_FORUMS'		=> '<b>Deleted forum and its messages, moved subforums</b> to %s<br />&#187; %s',
	'LOG_FORUM_DEL_MOVE_POSTS_FORUMS'		=> '<b>Deleted forum and its subforums, moved messages</b> to %s<br />&#187; %s',
	'LOG_FORUM_DEL_MOVE_POSTS'				=> '<b>Deleted forum and moved posts </b> to %s<br />&#187; %s',
	'LOG_FORUM_DEL_MOVE_FORUMS'				=> '<b>Deleted forum and moved subforums</b> to %s<br />&#187; %s',
	'LOG_FORUM_DEL_POSTS_FORUMS'			=> '<b>Deleted forum, its messages and subforums</b><br />&#187; %s',
	'LOG_FORUM_DEL_MOVE_POSTS_MOVE_FORUMS'	=> '<b>Deleted forum, moved posts</b> to %s <b>and subforums</b> to %s<br />&#187; %s',

	'LOG_GROUP_CREATED'		=> '<b>New usergroup created</b><br />&#187; %s',
	'LOG_GROUP_DEFAULTS'	=> '<b>Group made default for members</b><br />&#187; %s',
	'LOG_GROUP_DELETE'		=> '<b>Usergroup deleted</b><br />&#187; %s',
	'LOG_GROUP_DEMOTED'		=> '<b>Leaders demoted in usergroup</b> %s<br />&#187; %s',
	'LOG_GROUP_PROMOTED'	=> '<b>Members promoted to leader in usergroup</b> %s<br />&#187; %s',
	'LOG_GROUP_REMOVE'		=> '<b>Members removed from usergroup</b> %s<br />&#187; %s',
	'LOG_GROUP_UPDATED'		=> '<b>Usergroup details updated</b><br />&#187; %s',
	'LOG_MODS_ADDED'		=> '<b>Added new leaders to usergroup</b> %s<br />&#187; %s',
	'LOG_USERS_APPROVED'	=> '<b>Users approved in usergroup</b> %s<br />&#187; %s',
	'LOG_USERS_ADDED'		=> '<b>Added new members to usergroup</b> %s<br />&#187; %s',

	'LOG_INDEX_ACTIVATE'	=> '<b>Activated inactive users</b><br />&#187; %s',
	'LOG_INDEX_DELETE'		=> '<b>Deleted inactive users</b><br />&#187; %s',
	'LOG_INDEX_REMIND'		=> '<b>Sent reminder emails to inactive users</b><br />&#187; %s',

	'LOG_JAB_CHANGED'			=> '<b>Jabber account changed</b>',
	'LOG_JAB_PASSCHG'			=> '<b>Jabber password changed</b>',
	'LOG_JAB_REGISTER'			=> '<b>Jabber account registered</b>',
	'LOG_JAB_SETTINGS_CHANGED'	=> '<b>Jabber settings changed</b>',

	'LOG_LANGUAGE_PACK_DELETED'		=> '<b>Deleted language pack</b><br />&#187; %s',
	'LOG_LANGUAGE_PACK_INSTALLED'	=> '<b>Installed language pack</b><br />&#187; %s',
	'LOG_LANGUAGE_PACK_UPDATED'		=> '<b>Updated language pack details</b><br />&#187; %s',

	'LOG_MASS_EMAIL'		=> '<b>Sent mass email</b><br />&#187; %s',

	'LOG_MODULE_DISABLE'	=> '<b>Module disabled</b>',
	'LOG_MODULE_ENABLE'		=> '<b>Module enabled</b>',
	'LOG_MODULE_MOVE_DOWN'	=> '<b>Module moved down</b><br />&#187; %s',
	'LOG_MODULE_MOVE_UP'	=> '<b>Module moved up</b><br />&#187; %s',
	'LOG_MODULE_REMOVED'	=> '<b>Module removed</b><br />&#187; %s',
	'LOG_MODULE_ADD'		=> '<b>Module added</b><br />&#187; %s',
	'LOG_MODULE_EDIT'		=> '<b>Module edited</b><br />&#187; %s',

	'LOG_PROFILE_FIELD_ACTIVATE'	=> '<b>Profile field activated</b><br />&#187; %s',
	'LOG_PROFILE_FIELD_CREATE'		=> '<b>Profile field added</b><br />&#187; %s',
	'LOG_PROFILE_FIELD_DEACTIVATE'	=> '<b>Profile field deactivated</b><br />&#187; %s',
	'LOG_PROFILE_FIELD_EDIT'		=> '<b>Profile field changed</b><br />&#187; %s',
	'LOG_PROFILE_FIELD_REMOVED'		=> '<b>Profile field removed</b><br />&#187; %s',

	'LOG_PRUNE'					=> '<b>Pruned forums</b><br />&#187; %s',
	'LOG_AUTO_PRUNE'			=> '<b>Auto-pruned forums</b><br />&#187; %s',
	'LOG_PRUNE_USER_DEAC'		=> '<b>Users deactivated</b><br />&#187; %s',
	'LOG_PRUNE_USER_DEL_DEL'	=> '<b>Users pruned and posts deleted</b><br />&#187; %s',
	'LOG_PRUNE_USER_DEL_ANON'	=> '<b>Users pruned and posts retained</b><br />&#187; %s',

	'LOG_RESET_DATE'		=> '<b>Board start date reset</b>',
	'LOG_RESET_ONLINE'		=> '<b>Most users online reset</b>',
	'LOG_RESYNC_POSTCOUNTS'	=> '<b>User postcounts synced</b>',
	'LOG_RESYNC_STATS'		=> '<b>Post, topic and user stats reset</b>',

	'LOG_USER_ACTIVE'		=> '<b>User activated</b><br />&#187; %s',
	'LOG_USER_BAN_USER'		=> '<b>Banned User via user management</b> for reason "<i>%s</i>"<br />&#187; %s',
	'LOG_USER_BAN_IP'		=> '<b>Banned ip via user management</b> for reason "<i>%s</i>"<br />&#187; %s',
	'LOG_USER_BAN_EMAIL'	=> '<b>Banned email via user management</b> for reason "<i>%s</i>"<br />&#187; %s',
	'LOG_USER_DELETED'		=> '<b>Deleted user</b><br />&#187; %s',
	'LOG_USER_DEL_ATTACH'	=> '<b>Removed all attachments made by the user</b><br />&#187; %s',
	'LOG_USER_DEL_AVATAR'	=> '<b>Removed user avatar</b><br />&#187; %s',
	'LOG_USER_DEL_POSTS'	=> '<b>Removed all posts made by the user</b><br />&#187; %s',
	'LOG_USER_DEL_SIG'		=> '<b>Removed user signature</b><br />&#187; %s',
	'LOG_USER_GROUP_CHANGE'	=> '<b>User changed default group</b><br />&#187; %s',
	'LOG_USER_INACTIVE'		=> '<b>User deactivated</b><br />&#187; %s',
	'LOG_USER_MOVE_POSTS'	=> '<b>Moved user posts</b><br />&#187; posts by "%s" to forum "%s"',
	'LOG_USER_NEW_PASSWORD'	=> '<b>Changed user password</b><br />&#187; %s',
	'LOG_USER_REACTIVATE'	=> '<b>Forced user account re-activation</b><br />&#187; %s',
	'LOG_USER_UPDATE_NAME'	=> '<b>Changed username</b><br />&#187; from "%s" to "%s"',
	'LOG_USER_USER_UPDATE'	=> '<b>Updated user details</b><br />&#187; %s',

	'LOG_WORD_ADD'			=> '<b>Added word censor</b><br />&#187; %s',
	'LOG_WORD_DELETE'		=> '<b>Deleted word censor</b><br />&#187; %s',
	'LOG_WORD_EDIT'			=> '<b>Edited word censor</b><br />&#187; %s',
));

?>