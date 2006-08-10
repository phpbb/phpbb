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
	'ACP_ADMINISTRATORS'		=> 'Administrators',
	'ACP_ADMIN_LOGS'			=> 'Admin Log',
	'ACP_ADMIN_ROLES'			=> 'Admin Roles',
	'ACP_ATTACHMENTS'			=> 'Attachments',
	'ACP_ATTACHMENT_SETTINGS'	=> 'Attachment Settings',
	'ACP_AUTH_SETTINGS'			=> 'Authentication',
	'ACP_AUTOMATION'			=> 'Automation',
	'ACP_AVATAR_SETTINGS'		=> 'Avatar Settings',

	'ACP_BACKUP'				=> 'Backup',
	'ACP_BAN'					=> 'Banning',
	'ACP_BAN_EMAILS'			=> 'Ban Emails',
	'ACP_BAN_IPS'				=> 'Ban IPs',
	'ACP_BAN_USERNAMES'			=> 'Ban Usernames',
	'ACP_BASIC_PERMISSIONS'		=> 'Basic Permissions',
	'ACP_BBCODES'				=> 'BBCodes',
	'ACP_BOARD_CONFIGURATION'	=> 'Board Configuration',
	'ACP_BOARD_DEFAULTS'		=> 'Board Defaults',
	'ACP_BOARD_FEATURES'		=> 'Board Features',
	'ACP_BOARD_MANAGEMENT'		=> 'Board Management',
	'ACP_BOARD_SETTINGS'		=> 'Board Settings',
	'ACP_BOTS'					=> 'Spiders/Robots',
	
	'ACP_CAPTCHA'				=> 'CAPTCHA',

	'ACP_CAT_DATABASE'			=> 'Database',
	'ACP_CAT_DOT_MODS'			=> '.Mods',
	'ACP_CAT_FORUMS'			=> 'Forums',
	'ACP_CAT_GENERAL'			=> 'General',
	'ACP_CAT_MAINTENANCE'		=> 'Maintenance',
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
	
	'ACP_DATABASE'				=> 'Database Management',
	'ACP_DISALLOW'				=> 'Disallow',
	'ACP_DISALLOW_USERNAMES'	=> 'Disallow Usernames',
	
	'ACP_EMAIL_SETTINGS'		=> 'Email Settings',
	'ACP_EXTENSION_GROUPS'		=> 'Manage Extension Groups',
	
	'ACP_FORUM_BASED_PERMISSIONS'	=> 'Forum Based Permissions',
	'ACP_FORUM_LOGS'				=> 'Forum Logs',
	'ACP_FORUM_MANAGEMENT'			=> 'Forum Management',
	'ACP_FORUM_MODERATORS'			=> 'Forum Moderators',
	'ACP_FORUM_PERMISSIONS'			=> 'Forum Permissions',
	'ACP_FORUM_ROLES'				=> 'Forum Roles',

	'ACP_GENERAL_CONFIGURATION'		=> 'General Configuration',
	'ACP_GENERAL_TASKS'				=> 'General Tasks',
	'ACP_GLOBAL_MODERATORS'			=> 'Global Moderators',
	'ACP_GLOBAL_PERMISSIONS'		=> 'Global Permissions',
	'ACP_GROUPS'					=> 'Groups',
	'ACP_GROUPS_FORUM_PERMISSIONS'	=> 'Groups Forum Permissions',
	'ACP_GROUPS_MANAGE'				=> 'Manage Groups',
	'ACP_GROUPS_MANAGEMENT'			=> 'Group Management',
	'ACP_GROUPS_PERMISSIONS'		=> 'Groups Permissions',
	
	'ACP_ICONS'					=> 'Topic Icons',
	'ACP_ICONS_SMILIES'			=> 'Topic Icons/Smilies',
	'ACP_IMAGESETS'				=> 'Imagesets',
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
	'ACP_MANAGE_REASONS'		=> 'Manage Report/Denial Reasons',
	'ACP_MANAGE_USERS'			=> 'Manage Users',
	'ACP_MASS_EMAIL'			=> 'Mass Email',
	'ACP_MESSAGES'				=> 'Messages',
	'ACP_MESSAGE_SETTINGS'		=> 'Private Message Settings',
	'ACP_MODULE_MANAGEMENT'		=> 'Module Management',
	'ACP_MOD_LOGS'				=> 'Moderator Log',
	'ACP_MOD_ROLES'				=> 'Moderator Roles',
	
	'ACP_ORPHAN_ATTACHMENTS'	=> 'Orphan Attachments',
	
	'ACP_PERMISSIONS'			=> 'Permissions',
	'ACP_PERMISSION_MASKS'		=> 'Permission Masks',
	'ACP_PERMISSION_ROLES'		=> 'Permission Roles',
	'ACP_PERMISSION_SETTINGS'	=> 'Permission Settings',
	'ACP_PERMISSION_TRACE'		=> 'Permission Trace',
	'ACP_PHP_INFO'				=> 'PHP Information',
	'ACP_POST_SETTINGS'			=> 'Post Settings',
	'ACP_PRUNE_FORUMS'			=> 'Prune Forums',
	'ACP_PRUNE_USERS'			=> 'Prune Users',
	'ACP_PRUNING'				=> 'Pruning',
	
	'ACP_QUICK_ACCESS'			=> 'Quick Access',
	
	'ACP_RANKS'					=> 'Ranks',
	'ACP_REASONS'				=> 'Report/Denial Reasons',
	'ACP_REGISTER_SETTINGS'		=> 'User Registration Settings',

	'ACP_RESTORE'				=> 'Restore',

	'ACP_SEARCH'				=> 'Search Configuration',
	'ACP_SEARCH_INDEX'			=> 'Search Index',
	'ACP_SEARCH_SETTINGS'		=> 'Search Settings',

	'ACP_SECURITY_SETTINGS'		=> 'Security Settings',
	'ACP_SERVER_CONFIGURATION'	=> 'Server Configuration',
	'ACP_SERVER_SETTINGS'		=> 'Server Settings',
	'ACP_SIGNATURE_SETTINGS'	=> 'Signature Settings',
	'ACP_SMILIES'				=> 'Smilies',
	'ACP_SPECIAL_PERMISSIONS'	=> 'Special Permissions',
	'ACP_STYLE_COMPONENTS'		=> 'Style Components',
	'ACP_STYLE_MANAGEMENT'		=> 'Style Management',
	'ACP_STYLES'				=> 'Styles',
	
	'ACP_TEMPLATES'				=> 'Templates',
	'ACP_THEMES'				=> 'Themes',
	
	'ACP_UPDATE'					=> 'Updating',
	'ACP_USERS_FORUM_PERMISSIONS'	=> 'Users Forum Permissions',
	'ACP_USERS_LOGS'				=> 'User Logs',
	'ACP_USERS_PERMISSIONS'			=> 'Users Permissions',
	'ACP_USER_ATTACH'				=> 'Attachments',
	'ACP_USER_AVATAR'				=> 'Avatar',
	'ACP_USER_FEEDBACK'				=> 'Feedback',
	'ACP_USER_GROUPS'				=> 'Groups',
	'ACP_USER_MANAGEMENT'			=> 'User Management',
	'ACP_USER_OVERVIEW'				=> 'Overview',
	'ACP_USER_PERM'					=> 'Permissions',
	'ACP_USER_PREFS'				=> 'Preferences',
	'ACP_USER_PROFILE'				=> 'Profile',
	'ACP_USER_RANK'					=> 'Rank',
	'ACP_USER_ROLES'				=> 'User Roles',
	'ACP_USER_SECURITY'				=> 'User Security',
	'ACP_USER_SIG'					=> 'Signature',

	'ACP_VC_SETTINGS'					=> 'Visual Confirmation Settings',
	'ACP_VC_CAPTCHA_DISPLAY'			=> 'CAPTCHA Image Preview',
	'ACP_VERSION_CHECK'					=> 'Check for Updates',
	'ACP_VIEW_ADMIN_PERMISSIONS'		=> 'View Admin Permissions',
	'ACP_VIEW_FORUM_MOD_PERMISSIONS'	=> 'View Forum Moderator Permissions',
	'ACP_VIEW_FORUM_PERMISSIONS'		=> 'View Forum Permissions',
	'ACP_VIEW_GLOBAL_MOD_PERMISSIONS'	=> 'View Global Moderator Permissions',
	'ACP_VIEW_USER_PERMISSIONS'			=> 'View User Permissions',
	
	'ACP_WORDS'					=> 'Word Censoring',

	'ACTION'				=> 'Action',
	'ACTIONS'				=> 'Actions',
	'ACTIVATE'				=> 'Activate',
	'ADD'					=> 'Add',
	'ADMIN'					=> 'Administration',
	'ADMIN_INDEX'			=> 'Admin Index',
	'ADMIN_PANEL'			=> 'Administration Control Panel',

	'BACK'					=> 'Back',

	'COLOUR_SWATCH'			=> 'Web-safe colour swatch',
	'CONFIG_UPDATED'		=> 'Configuration updated successfully',
	'CONFIRM_OPERATION'		=> 'Are you sure you wish to carry out this operation?',

	'DEACTIVATE'				=> 'Deactivate',
	'DIMENSIONS'				=> 'Dimensions',
	'DISABLE'					=> 'Disable',
	'DOWNLOAD'					=> 'Download',
	'DOWNLOAD_AS'				=> 'Download as',
	'DOWNLOAD_STORE'			=> 'Download or Store file',
	'DOWNLOAD_STORE_EXPLAIN'	=> 'You may directly download the file or save it in your store/ folder.',

	'EDIT'					=> 'Edit',
	'ENABLE'				=> 'Enable',
	'EXPORT_DOWNLOAD'		=> 'Download',
	'EXPORT_STORE'			=> 'Store',

	'FORUM_INDEX'			=> 'Forum Index',

	'GENERAL_OPTIONS'		=> 'General Options',
	'GENERAL_SETTINGS'		=> 'General Settings',
	'GLOBAL_MASK'			=> 'Global Permission Mask',

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

	'OFF'					=> 'OFF',
	'ON'					=> 'ON',

	'PARSE_BBCODE'			=> 'Parse BBCode',
	'PARSE_SMILIES'			=> 'Parse Smilies',
	'PARSE_URLS'			=> 'Parse Links',
	'PROCEED_TO_ACP'		=> '%sProceed to the ACP%s',
	'REMIND'				=> 'Remind',
	'REORDER'				=> 'Reorder',
	'RESYNC'				=> 'Sync',
	'RETURN_TO'				=> 'Return to ...',

	'SELECT_ANONYMOUS'		=> 'Select Anonymous User',
	'SELECT_OPTION'			=> 'Select option',

	'UCP'					=> 'User Control Panel',
	'USERNAMES_EXPLAIN'		=> 'Place each username on a seperate line',
	'USER_CONTROL_PANEL'	=> 'User Control Panel',

	'WARNING'				=> 'Warning',

	'DEBUG_EXTRA_WARNING'	=> 'The DEBUG_EXTRA constant is defined which is only meant for development purposes by the developers.<br />The board is running additional code to display sql reports, which slows down the board in a significant manner. Additionally sql errors are always displayed with a full backtrace to all users instead of displaying it solely to administrators, which is the default setting.<br /><br />With this said, please be aware that you are currently running your installation in <b>Debug Mode</b> and should you take this board live, please remove the constant from the config file.',
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
	'ACP_USERS_LOGS_EXPLAIN'	=> 'This lists all actions carried out by users or on users.',
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
	'ADMIN_LOG_INDEX_EXPLAIN'	=> 'This gives an overview of the last five actions carried out by board administrators. A full copy of the log can be viewed from the appropriate menu item or following the link below.',
	'AVATAR_DIR_SIZE'			=> 'Avatar directory size',

	'BOARD_STARTED'		=> 'Board started',

	'DATABASE_SERVER_INFO'	=> 'Database server',
	'DATABASE_SIZE'			=> 'Database size',

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

	'RESET_DATE'			=> 'Reset Date',
	'RESET_ONLINE'			=> 'Reset Online',
	'RESYNC_POSTCOUNTS'		=> 'Resync Postcounts',
	'RESYNC_POST_MARKING'	=> 'Resync dotted topics',
	'RESYNC_STATS'			=> 'Resync Stats',

	'STATISTIC'			=> 'Statistic',

	'TOPICS_PER_DAY'	=> 'Topics per day',

	'UPLOAD_DIR_SIZE'	=> 'Upload directory size',
	'USERS_PER_DAY'		=> 'Users per day',

	'VALUE'				=> 'Value',
	'VIEW_ADMIN_LOG'	=> 'View administrator log',

	'WELCOME_PHPBB'			=> 'Welcome to phpBB',
));

// Log Entries
$lang = array_merge($lang, array(
	'LOG_ACL_ADD_USER_GLOBAL_U_'		=> '<b>Added or edited users user permissions</b><br />&#187; %s',
	'LOG_ACL_ADD_GROUP_GLOBAL_U_'		=> '<b>Added or edited groups user permissions</b><br />&#187; %s',
	'LOG_ACL_ADD_USER_GLOBAL_M_'		=> '<b>Added or edited users global moderator permissions</b><br />&#187; %s',
	'LOG_ACL_ADD_GROUP_GLOBAL_M_'		=> '<b>Added or edited groups global moderator permissions</b><br />&#187; %s',
	'LOG_ACL_ADD_USER_GLOBAL_A_'		=> '<b>Added or edited users admin permissions</b><br />&#187; %s',
	'LOG_ACL_ADD_GROUP_GLOBAL_A_'		=> '<b>Added or edited groups admin permissions</b><br />&#187; %s',

	'LOG_ACL_ADD_ADMIN_GLOBAL_A_'		=> '<b>Added or edited Administrators</b><br />&#187; %s',
	'LOG_ACL_ADD_MOD_GLOBAL_M_'			=> '<b>Added or edited Global Moderators</b><br />&#187; %s',

	'LOG_ACL_ADD_USER_LOCAL_F_'			=> '<b>Added or edited users forum access</b> from %1$s<br />&#187; %2$s',
	'LOG_ACL_ADD_USER_LOCAL_M_'			=> '<b>Added or edited users forum moderator access</b> from %1$s<br />&#187; %2$s',
	'LOG_ACL_ADD_GROUP_LOCAL_F_'		=> '<b>Added or edited groups forum access</b> from %1$s<br />&#187; %2$s',
	'LOG_ACL_ADD_GROUP_LOCAL_M_'		=> '<b>Added or edited groups forum moderator access</b> from %1$s<br />&#187; %2$s',

	'LOG_ACL_ADD_MOD_LOCAL_M_'			=> '<b>Added or edited Moderators</b> from %1$s<br />&#187; %2$s',
	'LOG_ACL_ADD_FORUM_LOCAL_F_'		=> '<b>Added or edited Forum Permissions</b> from %1$s<br />&#187; %2$s',

	'LOG_ACL_DEL_ADMIN_GLOBAL_A_'		=> '<b>Removed Administrators</b><br />&#187; %s',
	'LOG_ACL_DEL_MOD_GLOBAL_M_'			=> '<b>Removed Global Moderators</b><br />&#187; %s',
	'LOG_ACL_DEL_MOD_LOCAL_M_'			=> '<b>Removed Moderators</b> from %1$s<br />&#187; %2$s',
	'LOG_ACL_DEL_FORUM_LOCAL_F_'		=> '<b>Removed User/Group Forum Permissions</b> from %1$s<br />&#187; %2$s',

	'LOG_ACL_TRANSFER_PERMISSIONS'		=> '<b>Permissions transfered from</b><br />&#187; %s',
	'LOG_ACL_RESTORE_PERMISSIONS'		=> '<b>Own permissions restored after using permissions from</b><br />&#187; %s',
	
	'LOG_ADMIN_AUTH_FAIL'		=> '<b>Failed administration login attempt</b>',
	'LOG_ADMIN_AUTH_SUCCESS'	=> '<b>Successful administration login</b>',

	'LOG_ATTACH_EXT_ADD'		=> '<b>Added or edited attachment extension</b><br />&#187; %s',
	'LOG_ATTACH_EXT_DEL'		=> '<b>Removed attachment extension</b><br />&#187; %s',
	'LOG_ATTACH_EXT_UPDATE'		=> '<b>Updated attachment extension</b><br />&#187; %s',
	'LOG_ATTACH_EXTGROUP_ADD'	=> '<b>Added extension group</b><br />&#187; %s',
	'LOG_ATTACH_EXTGROUP_EDIT'	=> '<b>Edited extension group</b><br />&#187; %s',
	'LOG_ATTACH_EXTGROUP_DEL'	=> '<b>Removed extension group</b><br />&#187; %s',
	'LOG_ATTACH_FILEUPLOAD'		=> '<b>Orphan File uploaded to Post</b><br />&#187; ID %1$d - %2$s',
	'LOG_ATTACH_ORPHAN_DEL'		=> '<b>Orphan Files deleted</b><br />&#187; %s',

	'LOG_BAN_EXCLUDE_USER'	=> '<b>Excluded user from ban</b> for reason "<i>%1$s</i>"<br />&#187; %2$s ',
	'LOG_BAN_EXCLUDE_IP'	=> '<b>Excluded ip from ban</b> for reason "<i>%1$s</i>"<br />&#187; %2$s ',
	'LOG_BAN_EXCLUDE_EMAIL' => '<b>Excluded email from ban</b> for reason "<i>%1$s</i>"<br />&#187; %2$s ',
	'LOG_BAN_USER'			=> '<b>Banned User</b> for reason "<i>%1$s</i>"<br />&#187; %2$s ',
	'LOG_BAN_IP'			=> '<b>Banned ip</b> for reason "<i>%1$s</i>"<br />&#187; %2$s',
	'LOG_BAN_EMAIL'			=> '<b>Banned email</b> for reason "<i>%1$s</i>"<br />&#187; %2$s',
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
	'LOG_CLEAR_USERS'		=> '<b>Cleared user logs</b>',

	'LOG_CONFIG_ATTACH'			=> '<b>Altered attachment settings</b>',
	'LOG_CONFIG_AUTH'			=> '<b>Altered authentication settings</b>',
	'LOG_CONFIG_AVATAR'			=> '<b>Altered avatar settings</b>',
	'LOG_CONFIG_COOKIE'			=> '<b>Altered cookie settings</b>',
	'LOG_CONFIG_EMAIL'			=> '<b>Altered email settings</b>',
	'LOG_CONFIG_FEATURES'		=> '<b>Altered board features</b>',
	'LOG_CONFIG_LOAD'			=> '<b>Altered load settings</b>',
	'LOG_CONFIG_MESSAGE'		=> '<b>Altered private message settings</b>',
	'LOG_CONFIG_POST'			=> '<b>Altered post settings</b>',
	'LOG_CONFIG_REGISTRATION'	=> '<b>Altered user registration settings</b>',
	'LOG_CONFIG_SEARCH'			=> '<b>Altered search settings</b>',
	'LOG_CONFIG_SECURITY'		=> '<b>Altered security settings</b>',
	'LOG_CONFIG_SERVER'			=> '<b>Altered server settings</b>',
	'LOG_CONFIG_SETTINGS'		=> '<b>Altered board settings</b>',
	'LOG_CONFIG_SIGNATURE'		=> '<b>Altered signature settings</b>',
	'LOG_CONFIG_VISUAL'			=> '<b>Altered visual confirmation settings</b>',

	'LOG_APPROVE_TOPIC'			=> '<b>Approved topic</b><br />&#187; %s',
	'LOG_BUMP_TOPIC'			=> '<b>User bumped topic</b><br />&#187; %s',
	'LOG_DELETE_POST'			=> '<b>Deleted post</b><br />&#187; %s',
	'LOG_DELETE_TOPIC'			=> '<b>Deleted topic</b><br />&#187; %s',
	'LOG_FORK'					=> '<b>Copied topic</b><br />&#187; from %s',
	'LOG_LOCK'					=> '<b>Locked topic</b><br />&#187; %s',
	'LOG_LOCK_POST'				=> '<b>Locked post</b><br />&#187; %s',
	'LOG_MERGE'					=> '<b>Merged posts</b> into topic<br />&#187;%s',
	'LOG_MOVE'					=> '<b>Moved topic</b><br />&#187; from %s',
	'LOG_TOPIC_DELETED'			=> '<b>Deleted topic</b><br />&#187; %s',
	'LOG_TOPIC_RESYNC'			=> '<b>Resynchronised topic counters</b><br />&#187; %s',
	'LOG_TOPIC_TYPE_CHANGED'	=> '<b>Changed topic type</b><br />&#187; %s',
	'LOG_UNLOCK'				=> '<b>Unlocked topic</b><br />&#187; %s',
	'LOG_UNLOCK_POST'			=> '<b>Unlocked post</b><br />&#187; %s',

	'LOG_DISALLOW_ADD'		=> '<b>Added disallowed username</b><br />&#187; %s',
	'LOG_DISALLOW_DELETE'	=> '<b>Deleted disallowed username</b>',

	'LOG_DB_BACKUP'			=> '<b>Database backup</b>',
	'LOG_DB_RESTORE'		=> '<b>Database restore</b>',

	'LOG_DOWNLOAD_EXCLUDE_IP'	=> '<b>Exluded ip/hostname from download list</b><br />&#187; %s',
	'LOG_DOWNLOAD_IP'			=> '<b>Added ip/hostname to download list</b><br />&#187; %s',
	'LOG_DOWNLOAD_REMOVE_IP'	=> '<b>Removed ip/hostname from download list</b><br />&#187; %s',

	'LOG_ERROR_JABBER'		=> '<b>Jabber Error</b><br />&#187; %s',
	'LOG_ERROR_EMAIL'		=> '<b>Email Error</b><br />&#187; %s',
	
	'LOG_FORUM_ADD'							=> '<b>Created new forum</b><br />&#187; %s',
	'LOG_FORUM_DEL_FORUM'					=> '<b>Deleted forum</b><br />&#187; %s',
	'LOG_FORUM_DEL_FORUMS'					=> '<b>Deleted forum and its subforums</b><br />&#187; %s',
	'LOG_FORUM_DEL_MOVE_FORUMS'				=> '<b>Deleted forum and moved subforums</b> to %1$s<br />&#187; %2$s',
	'LOG_FORUM_DEL_MOVE_POSTS'				=> '<b>Deleted forum and moved posts </b> to %1$s<br />&#187; %2$s',
	'LOG_FORUM_DEL_MOVE_POSTS_FORUMS'		=> '<b>Deleted forum and its subforums, moved messages</b> to %1$s<br />&#187; %2$s',
	'LOG_FORUM_DEL_MOVE_POSTS_MOVE_FORUMS'	=> '<b>Deleted forum, moved posts</b> to %1$s <b>and subforums</b> to %2$s<br />&#187; %3$s',
	'LOG_FORUM_DEL_POSTS'					=> '<b>Deleted forum and its messages</b><br />&#187; %s',
	'LOG_FORUM_DEL_POSTS_FORUMS'			=> '<b>Deleted forum, its messages and subforums</b><br />&#187; %s',
	'LOG_FORUM_DEL_POSTS_MOVE_FORUMS'		=> '<b>Deleted forum and its messages, moved subforums</b> to %1$s<br />&#187; %2$s',
	'LOG_FORUM_EDIT'						=> '<b>Edited forum details</b><br />&#187; %s',
	'LOG_FORUM_MOVE_DOWN'					=> '<b>Moved forum</b> %1$s <b>below</b> %2$s',
	'LOG_FORUM_MOVE_UP'						=> '<b>Moved forum</b> %1$s <b>above</b> %2$s',
	'LOG_FORUM_SYNC'						=> '<b>Re-synchronised forum</b><br />&#187; %s',

	'LOG_GROUP_CREATED'		=> '<b>New usergroup created</b><br />&#187; %s',
	'LOG_GROUP_DEFAULTS'	=> '<b>Group made default for members</b><br />&#187; %s',
	'LOG_GROUP_DELETE'		=> '<b>Usergroup deleted</b><br />&#187; %s',
	'LOG_GROUP_DEMOTED'		=> '<b>Leaders demoted in usergroup</b> %1$s<br />&#187; %2$s',
	'LOG_GROUP_PROMOTED'	=> '<b>Members promoted to leader in usergroup</b> %1$s<br />&#187; %2$s',
	'LOG_GROUP_REMOVE'		=> '<b>Members removed from usergroup</b> %1$s<br />&#187; %2$s',
	'LOG_GROUP_UPDATED'		=> '<b>Usergroup details updated</b><br />&#187; %s',
	'LOG_MODS_ADDED'		=> '<b>Added new leaders to usergroup</b> %1$s<br />&#187; %2$s',
	'LOG_USERS_APPROVED'	=> '<b>Users approved in usergroup</b> %1$s<br />&#187; %2$s',
	'LOG_USERS_ADDED'		=> '<b>Added new members to usergroup</b> %1$s<br />&#187; %2$s',

	'LOG_IMAGESET_ADD_DB'		=> '<b>Added new imageset to database</b><br />&#187; %s',
	'LOG_IMAGESET_ADD_FS'		=> '<b>Add new imageset on filesystem</b><br />&#187; %s',
	'LOG_IMAGESET_DELETE'		=> '<b>Deleted imageset</b><br />&#187; %s',
	'LOG_IMAGESET_EDIT_DETAILS'	=> '<b>Edited imageset details</b><br />&#187; %s',
	'LOG_IMAGESET_EDIT'			=> '<b>Edited imageset</b><br />&#187; %s',
	'LOG_IMAGESET_EXPORT'		=> '<b>Exported imageset</b><br />&#187; %s',
	'LOG_IMAGESET_REFRESHED'	=> '<b>Refreshed imageset</b><br />&#187; %s',

	'LOG_INDEX_ACTIVATE'	=> '<b>Activated inactive users</b><br />&#187; %s',
	'LOG_INDEX_DELETE'		=> '<b>Deleted inactive users</b><br />&#187; %s',
	'LOG_INDEX_REMIND'		=> '<b>Sent reminder emails to inactive users</b><br />&#187; %s',
	'LOG_INSTALL_CONVERTED'	=> '<b>Converted from %1$s to phpBB %2$s</b>',
	'LOG_INSTALL_INSTALLED'	=> '<b>Installed phpBB %s</b>',

	'LOG_IP_BROWSER_CHECK'	=> '<b>Session IP/Browser check failed</b><br />&#187;User IP "<i>%1$s</i>" checked against session IP "<i>%2$s</i>" and user browser string "<i>%3$s</i>" checked against session browser string "<i>%4$s</i>".',

	'LOG_JAB_CHANGED'			=> '<b>Jabber account changed</b>',
	'LOG_JAB_PASSCHG'			=> '<b>Jabber password changed</b>',
	'LOG_JAB_REGISTER'			=> '<b>Jabber account registered</b>',
	'LOG_JAB_SETTINGS_CHANGED'	=> '<b>Jabber settings changed</b>',

	'LOG_LANGUAGE_PACK_DELETED'		=> '<b>Deleted language pack</b><br />&#187; %s',
	'LOG_LANGUAGE_PACK_INSTALLED'	=> '<b>Installed language pack</b><br />&#187; %s',
	'LOG_LANGUAGE_PACK_UPDATED'		=> '<b>Updated language pack details</b><br />&#187; %s',
	'LOG_LANGUAGE_FILE_REPLACED'	=> '<b>Replaced language file</b><br />&#187; %s',

	'LOG_MASS_EMAIL'		=> '<b>Sent mass email</b><br />&#187; %s',

	'LOG_MCP_CHANGE_POSTER'	=> '<b>Changed poster in topic "%1$s"</b><br />&#187; from %2$s to %3$s',

	'LOG_MODULE_DISABLE'	=> '<b>Module disabled</b>',
	'LOG_MODULE_ENABLE'		=> '<b>Module enabled</b>',
	'LOG_MODULE_MOVE_DOWN'	=> '<b>Module moved down</b><br />&#187; %s',
	'LOG_MODULE_MOVE_UP'	=> '<b>Module moved up</b><br />&#187; %s',
	'LOG_MODULE_REMOVED'	=> '<b>Module removed</b><br />&#187; %s',
	'LOG_MODULE_ADD'		=> '<b>Module added</b><br />&#187; %s',
	'LOG_MODULE_EDIT'		=> '<b>Module edited</b><br />&#187; %s',

	'LOG_A_ROLE_ADD'		=> '<b>Admin Role added</b><br />&#187; %s',
	'LOG_A_ROLE_EDIT'		=> '<b>Admin Role edited</b><br />&#187; %s',
	'LOG_A_ROLE_REMOVED'	=> '<b>Admin Role removed</b><br />&#187; %s',
	'LOG_F_ROLE_ADD'		=> '<b>Forum Role added</b><br />&#187; %s',
	'LOG_F_ROLE_EDIT'		=> '<b>Forum Role edited</b><br />&#187; %s',
	'LOG_F_ROLE_REMOVED'	=> '<b>Forum Role removed</b><br />&#187; %s',
	'LOG_M_ROLE_ADD'		=> '<b>Moderator Role added</b><br />&#187; %s',
	'LOG_M_ROLE_EDIT'		=> '<b>Moderator Role edited</b><br />&#187; %s',
	'LOG_M_ROLE_REMOVED'	=> '<b>Moderator Role removed</b><br />&#187; %s',
	'LOG_U_ROLE_ADD'		=> '<b>User Role added</b><br />&#187; %s',
	'LOG_U_ROLE_EDIT'		=> '<b>User Role edited</b><br />&#187; %s',
	'LOG_U_ROLE_REMOVED'	=> '<b>User Role removed</b><br />&#187; %s',

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

	'LOG_REASON_ADDED'		=> '<b>Added report/denial reason</b><br />&#187; %s',
	'LOG_REASON_REMOVED'	=> '<b>Removed report/denial reason</b><br />&#187; %s',
	'LOG_REASON_UPDATED'	=> '<b>Updated report/denial reason</b><br />&#187; %s',

	'LOG_RESET_DATE'			=> '<b>Board start date reset</b>',
	'LOG_RESET_ONLINE'			=> '<b>Most users online reset</b>',
	'LOG_RESYNC_POSTCOUNTS'		=> '<b>User postcounts synced</b>',
	'LOG_RESYNC_POST_MARKING'	=> '<b>Dotted topics synced</b>',
	'LOG_RESYNC_STATS'			=> '<b>Post, topic and user stats reset</b>',

	'LOG_STYLE_ADD'				=> '<b>Added new style</b><br />&#187; %s',
	'LOG_STYLE_DELETE'			=> '<b>Deleted style</b><br />&#187; %s',
	'LOG_STYLE_EDIT_DETAILS'	=> '<b>Edited style</b><br />&#187; %s',
	'LOG_STYLE_EXPORT'			=> '<b>Exported style</b><br />&#187; %s',

	'LOG_TEMPLATE_ADD_DB'			=> '<b>Added new template set to database</b><br />&#187; %s',
	'LOG_TEMPLATE_ADD_FS'			=> '<b>Add new template set on filesystem</b><br />&#187; %s',
	'LOG_TEMPLATE_CACHE_CLEARED'	=> '<b>Deleted cached versions of template files in template set <i>%1$s</i></b><br />&#187; %2$s',
	'LOG_TEMPLATE_DELETE'			=> '<b>Deleted template set</b><br />&#187; %s',
	'LOG_TEMPLATE_EDIT'				=> '<b>Edited template set <i>%1$s</i></b><br />&#187; %2$s',
	'LOG_TEMPLATE_EDIT_DETAILS'		=> '<b>Edited template details</b><br />&#187; %s',
	'LOG_TEMPLATE_EXPORT'			=> '<b>Exported template set</b><br />&#187; %s',
	'LOG_TEMPLATE_REFRESHED'		=> '<b>Refreshed template set</b><br />&#187; %s',

	'LOG_THEME_ADD_DB'			=> '<b>Added new theme to database</b><br />&#187; %s',
	'LOG_THEME_ADD_FS'			=> '<b>Add new theme on filesystem</b><br />&#187; %s',
	'LOG_THEME_DELETE'			=> '<b>Theme deleted</b><br />&#187; %s',
	'LOG_THEME_EDIT_DETAILS'	=> '<b>Edited theme details</b><br />&#187; %s',
	'LOG_THEME_EDIT'			=> '<b>Edited theme <i>%1$s</i></b><br />&#187; Modified class <i>%2$s</i>',
	'LOG_THEME_EDIT_ADD'		=> '<b>Edited theme <i>%1$s</i></b><br />&#187; Added class <i>%2$s</i>',
	'LOG_THEME_EXPORT'			=> '<b>Exported theme</b><br />&#187; %s',
	'LOG_THEME_REFRESHED'		=> '<b>Refreshed theme</b><br />&#187; %s',

	'LOG_USER_ACTIVE'		=> '<b>User activated</b><br />&#187; %s',
	'LOG_USER_BAN_USER'		=> '<b>Banned User via user management</b> for reason "<i>%1$s</i>"<br />&#187; %2$s',
	'LOG_USER_BAN_IP'		=> '<b>Banned ip via user management</b> for reason "<i>%1$s</i>"<br />&#187; %2$s',
	'LOG_USER_BAN_EMAIL'	=> '<b>Banned email via user management</b> for reason "<i>%1$s</i>"<br />&#187; %2$s',
	'LOG_USER_DELETED'		=> '<b>Deleted user</b><br />&#187; %s',
	'LOG_USER_DEL_ATTACH'	=> '<b>Removed all attachments made by the user</b><br />&#187; %s',
	'LOG_USER_DEL_AVATAR'	=> '<b>Removed user avatar</b><br />&#187; %s',
	'LOG_USER_DEL_POSTS'	=> '<b>Removed all posts made by the user</b><br />&#187; %s',
	'LOG_USER_DEL_SIG'		=> '<b>Removed user signature</b><br />&#187; %s',
	'LOG_USER_INACTIVE'		=> '<b>User deactivated</b><br />&#187; %s',
	'LOG_USER_MOVE_POSTS'	=> '<b>Moved user posts</b><br />&#187; posts by "%1$s" to forum "%2$s"',
	'LOG_USER_NEW_PASSWORD'	=> '<b>Changed user password</b><br />&#187; %s',
	'LOG_USER_REACTIVATE'	=> '<b>Forced user account re-activation</b><br />&#187; %s',
	'LOG_USER_UPDATE_EMAIL'	=> '<b>User "%1$s" changed email</b><br />&#187; from "%2$s" to "%3$s"',
	'LOG_USER_UPDATE_NAME'	=> '<b>Changed username</b><br />&#187; from "%1$s" to "%2$s"',
	'LOG_USER_USER_UPDATE'	=> '<b>Updated user details</b><br />&#187; %s',

	'LOG_USER_ACTIVE_USER'		=> '<b>User account activated</b>',
	'LOG_USER_DEL_AVATAR_USER'	=> '<b>User avatar removed</b>',
	'LOG_USER_DEL_SIG_USER'		=> '<b>User signature removed</b>',
	'LOG_USER_FEEDBACK'			=> '<b>Added user feedback</b><br />&#187; %s',
	'LOG_USER_GENERAL'			=> '%s',
	'LOG_USER_INACTIVE_USER'	=> '<b>User account de-activated</b>',
	'LOG_USER_LOCK'				=> '<b>User locked own topic</b><br />&#187; %s',
	'LOG_USER_MOVE_POSTS_USER'	=> '<b>Moved all posts to forum "%s"</b>',
	'LOG_USER_REACTIVATE_USER'	=> '<b>Forced user account re-activation</b>',
	'LOG_USER_UNLOCK'			=> '<b>User unlocked own topic</b><br />&#187; %s',
	'LOG_USER_WARNING'			=> '<b>Added user warning</b><br />&#187;%s',
	'LOG_USER_WARNING_BODY'		=> '<b>The following warning was issued to this user</b><br />&#187;%s',

	'LOG_USER_GROUP_CHANGE'			=> '<b>User changed default group</b><br />&#187; %s',
	'LOG_USER_GROUP_DEMOTE'			=> '<b>User demoted as leaders from usergroup</b><br />&#187; %s',
	'LOG_USER_GROUP_JOIN'			=> '<b>User joined group</b><br />&#187; %s',
	'LOG_USER_GROUP_JOIN_PENDING'	=> '<b>User joined group and needs to be approved</b><br />&#187; %s',
	'LOG_USER_GROUP_RESIGN'			=> '<b>User resigned membership from group</b><br />&#187; %s',

	'LOG_WORD_ADD'			=> '<b>Added word censor</b><br />&#187; %s',
	'LOG_WORD_DELETE'		=> '<b>Deleted word censor</b><br />&#187; %s',
	'LOG_WORD_EDIT'			=> '<b>Edited word censor</b><br />&#187; %s',
));

?>