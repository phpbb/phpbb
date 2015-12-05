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

// Common
$lang = array_merge($lang, array(
	'ACP_ADMINISTRATORS'		=> 'Administrators',
	'ACP_ADMIN_LOGS'			=> 'Admin log',
	'ACP_ADMIN_ROLES'			=> 'Admin roles',
	'ACP_ATTACHMENTS'			=> 'Attachments',
	'ACP_ATTACHMENT_SETTINGS'	=> 'Attachment settings',
	'ACP_AUTH_SETTINGS'			=> 'Authentication',
	'ACP_AUTOMATION'			=> 'Automation',
	'ACP_AVATAR_SETTINGS'		=> 'Avatar settings',

	'ACP_BACKUP'				=> 'Backup',
	'ACP_BAN'					=> 'Banning',
	'ACP_BAN_EMAILS'			=> 'Ban emails',
	'ACP_BAN_IPS'				=> 'Ban IPs',
	'ACP_BAN_USERNAMES'			=> 'Ban users',
	'ACP_BBCODES'				=> 'BBCodes',
	'ACP_BOARD_CONFIGURATION'	=> 'Board configuration',
	'ACP_BOARD_FEATURES'		=> 'Board features',
	'ACP_BOARD_MANAGEMENT'		=> 'Board management',
	'ACP_BOARD_SETTINGS'		=> 'Board settings',
	'ACP_BOTS'					=> 'Spiders/Robots',

	'ACP_CAPTCHA'				=> 'CAPTCHA',

	'ACP_CAT_CUSTOMISE'			=> 'Customise',
	'ACP_CAT_DATABASE'			=> 'Database',
	'ACP_CAT_DOT_MODS'			=> 'Extensions',
	'ACP_CAT_FORUMS'			=> 'Forums',
	'ACP_CAT_GENERAL'			=> 'General',
	'ACP_CAT_MAINTENANCE'		=> 'Maintenance',
	'ACP_CAT_PERMISSIONS'		=> 'Permissions',
	'ACP_CAT_POSTING'			=> 'Posting',
	'ACP_CAT_STYLES'			=> 'Styles',
	'ACP_CAT_SYSTEM'			=> 'System',
	'ACP_CAT_USERGROUP'			=> 'Users and Groups',
	'ACP_CAT_USERS'				=> 'Users',
	'ACP_CLIENT_COMMUNICATION'	=> 'Client communication',
	'ACP_COOKIE_SETTINGS'		=> 'Cookie settings',
	'ACP_CONTACT'				=> 'Contact page',
	'ACP_CONTACT_SETTINGS'		=> 'Contact page settings',
	'ACP_CRITICAL_LOGS'			=> 'Error log',
	'ACP_CUSTOM_PROFILE_FIELDS'	=> 'Custom profile fields',

	'ACP_DATABASE'				=> 'Database management',
	'ACP_DISALLOW'				=> 'Disallow',
	'ACP_DISALLOW_USERNAMES'	=> 'Disallow usernames',

	'ACP_EMAIL_SETTINGS'		=> 'Email settings',
	'ACP_EXTENSION_GROUPS'		=> 'Manage attachment extension groups',
	'ACP_EXTENSION_MANAGEMENT'	=> 'Extension management',
	'ACP_EXTENSIONS'			=> 'Manage extensions',

	'ACP_FORUM_BASED_PERMISSIONS'	=> 'Forum based permissions',
	'ACP_FORUM_LOGS'				=> 'Forum logs',
	'ACP_FORUM_MANAGEMENT'			=> 'Forum management',
	'ACP_FORUM_MODERATORS'			=> 'Forum moderators',
	'ACP_FORUM_PERMISSIONS'			=> 'Forum permissions',
	'ACP_FORUM_PERMISSIONS_COPY'	=> 'Copy forum permissions',
	'ACP_FORUM_ROLES'				=> 'Forum roles',

	'ACP_GENERAL_CONFIGURATION'		=> 'General configuration',
	'ACP_GENERAL_TASKS'				=> 'General tasks',
	'ACP_GLOBAL_MODERATORS'			=> 'Global moderators',
	'ACP_GLOBAL_PERMISSIONS'		=> 'Global permissions',
	'ACP_GROUPS'					=> 'Groups',
	'ACP_GROUPS_FORUM_PERMISSIONS'	=> 'Group forum permissions',
	'ACP_GROUPS_MANAGE'				=> 'Manage groups',
	'ACP_GROUPS_MANAGEMENT'			=> 'Group management',
	'ACP_GROUPS_PERMISSIONS'		=> 'Group permissions',
	'ACP_GROUPS_POSITION'			=> 'Manage group positions',

	'ACP_ICONS'					=> 'Topic icons',
	'ACP_ICONS_SMILIES'			=> 'Topic icons/smilies',
	'ACP_INACTIVE_USERS'		=> 'Inactive users',
	'ACP_INDEX'					=> 'ACP index',

	'ACP_JABBER_SETTINGS'		=> 'Jabber settings',

	'ACP_LANGUAGE'				=> 'Language management',
	'ACP_LANGUAGE_PACKS'		=> 'Language packs',
	'ACP_LOAD_SETTINGS'			=> 'Load settings',
	'ACP_LOGGING'				=> 'Logging',

	'ACP_MAIN'					=> 'ACP index',

	'ACP_MANAGE_ATTACHMENTS'			=> 'Manage attachments',
	'ACP_MANAGE_ATTACHMENTS_EXPLAIN'	=> 'Here you can list and delete files attached to posts and private messages.',

	'ACP_MANAGE_EXTENSIONS'		=> 'Manage attachment extensions',
	'ACP_MANAGE_FORUMS'			=> 'Manage forums',
	'ACP_MANAGE_RANKS'			=> 'Manage ranks',
	'ACP_MANAGE_REASONS'		=> 'Manage report/denial reasons',
	'ACP_MANAGE_USERS'			=> 'Manage users',
	'ACP_MASS_EMAIL'			=> 'Mass email',
	'ACP_MESSAGES'				=> 'Messages',
	'ACP_MESSAGE_SETTINGS'		=> 'Private message settings',
	'ACP_MODULE_MANAGEMENT'		=> 'Module management',
	'ACP_MOD_LOGS'				=> 'Moderator log',
	'ACP_MOD_ROLES'				=> 'Moderator roles',

	'ACP_NO_ITEMS'				=> 'There are no items yet.',

	'ACP_ORPHAN_ATTACHMENTS'	=> 'Orphaned attachments',

	'ACP_PERMISSIONS'			=> 'Permissions',
	'ACP_PERMISSION_MASKS'		=> 'Permission masks',
	'ACP_PERMISSION_ROLES'		=> 'Permission roles',
	'ACP_PERMISSION_TRACE'		=> 'Permission trace',
	'ACP_PHP_INFO'				=> 'PHP information',
	'ACP_POST_SETTINGS'			=> 'Post settings',
	'ACP_PRUNE_FORUMS'			=> 'Prune forums',
	'ACP_PRUNE_USERS'			=> 'Prune users',
	'ACP_PRUNING'				=> 'Pruning',

	'ACP_QUICK_ACCESS'			=> 'Quick access',

	'ACP_RANKS'					=> 'Ranks',
	'ACP_REASONS'				=> 'Report/denial reasons',
	'ACP_REGISTER_SETTINGS'		=> 'User registration settings',

	'ACP_RESTORE'				=> 'Restore',

	'ACP_FEED'					=> 'Feed management',
	'ACP_FEED_SETTINGS'			=> 'Feed settings',

	'ACP_SEARCH'				=> 'Search configuration',
	'ACP_SEARCH_INDEX'			=> 'Search index',
	'ACP_SEARCH_SETTINGS'		=> 'Search settings',

	'ACP_SECURITY_SETTINGS'		=> 'Security settings',
	'ACP_SEND_STATISTICS'		=> 'Send statistical information',
	'ACP_SERVER_CONFIGURATION'	=> 'Server configuration',
	'ACP_SERVER_SETTINGS'		=> 'Server settings',
	'ACP_SIGNATURE_SETTINGS'	=> 'Signature settings',
	'ACP_SMILIES'				=> 'Smilies',
	'ACP_STYLE_MANAGEMENT'		=> 'Style management',
	'ACP_STYLES'				=> 'Styles',
	'ACP_STYLES_CACHE'			=> 'Purge Cache',
	'ACP_STYLES_INSTALL'		=> 'Install Styles',

	'ACP_SUBMIT_CHANGES'		=> 'Submit changes',

	'ACP_TEMPLATES'				=> 'Templates',
	'ACP_THEMES'				=> 'Themes',

	'ACP_UPDATE'					=> 'Updating',
	'ACP_USERS_FORUM_PERMISSIONS'	=> 'User forum permissions',
	'ACP_USERS_LOGS'				=> 'User logs',
	'ACP_USERS_PERMISSIONS'			=> 'User permissions',
	'ACP_USER_ATTACH'				=> 'Attachments',
	'ACP_USER_AVATAR'				=> 'Avatar',
	'ACP_USER_FEEDBACK'				=> 'Feedback',
	'ACP_USER_GROUPS'				=> 'Groups',
	'ACP_USER_MANAGEMENT'			=> 'User management',
	'ACP_USER_OVERVIEW'				=> 'Overview',
	'ACP_USER_PERM'					=> 'Permissions',
	'ACP_USER_PREFS'				=> 'Preferences',
	'ACP_USER_PROFILE'				=> 'Profile',
	'ACP_USER_RANK'					=> 'Rank',
	'ACP_USER_ROLES'				=> 'User roles',
	'ACP_USER_SECURITY'				=> 'User security',
	'ACP_USER_SIG'					=> 'Signature',
	'ACP_USER_WARNINGS'				=> 'Warnings',

	'ACP_VC_SETTINGS'					=> 'Spambot countermeasures',
	'ACP_VC_CAPTCHA_DISPLAY'			=> 'CAPTCHA image preview',
	'ACP_VERSION_CHECK'					=> 'Check for updates',
	'ACP_VIEW_ADMIN_PERMISSIONS'		=> 'View administrative permissions',
	'ACP_VIEW_FORUM_MOD_PERMISSIONS'	=> 'View forum moderation permissions',
	'ACP_VIEW_FORUM_PERMISSIONS'		=> 'View forum-based permissions',
	'ACP_VIEW_GLOBAL_MOD_PERMISSIONS'	=> 'View global moderation permissions',
	'ACP_VIEW_USER_PERMISSIONS'			=> 'View user-based permissions',

	'ACP_WORDS'					=> 'Word censoring',

	'ACTION'				=> 'Action',
	'ACTIONS'				=> 'Actions',
	'ACTIVATE'				=> 'Activate',
	'ADD'					=> 'Add',
	'ADMIN'					=> 'Administration',
	'ADMIN_INDEX'			=> 'Admin index',
	'ADMIN_PANEL'			=> 'Administration Control Panel',

	'ADM_LOGOUT'			=> 'ACP&nbsp;Logout',
	'ADM_LOGGED_OUT'		=> 'Successfully logged out from Administration Control Panel',

	'BACK'					=> 'Back',

	'COLOUR_SWATCH'			=> 'Web-safe colour swatch',
	'CONFIG_UPDATED'		=> 'Configuration updated successfully.',
	'CRON_LOCK_ERROR'		=> 'Could not obtain cron lock.',
	'CRON_NO_SUCH_TASK'		=> 'Could not find cron task “%s”.',
	'CRON_NO_TASK'			=> 'No cron tasks need to be run right now.',
	'CRON_NO_TASKS'			=> 'No cron tasks could be found.',
	'CURRENT_VERSION'		=> 'Current version',

	'DEACTIVATE'				=> 'Deactivate',
	'DIRECTORY_DOES_NOT_EXIST'	=> 'The entered path “%s” does not exist.',
	'DIRECTORY_NOT_DIR'			=> 'The entered path “%s” is not a directory.',
	'DIRECTORY_NOT_WRITABLE'	=> 'The entered path “%s” is not writable.',
	'DISABLE'					=> 'Disable',
	'DOWNLOAD'					=> 'Download',
	'DOWNLOAD_AS'				=> 'Download as',
	'DOWNLOAD_STORE'			=> 'Download or store file',
	'DOWNLOAD_STORE_EXPLAIN'	=> 'You may directly download the file or save it in your <samp>store/</samp> folder.',
	'DOWNLOADS'					=> 'Downloads',

	'EDIT'					=> 'Edit',
	'ENABLE'				=> 'Enable',
	'EXPORT_DOWNLOAD'		=> 'Download',
	'EXPORT_STORE'			=> 'Store',

	'GENERAL_OPTIONS'		=> 'General options',
	'GENERAL_SETTINGS'		=> 'General settings',
	'GLOBAL_MASK'			=> 'Global permission mask',

	'INSTALL'				=> 'Install',
	'IP'					=> 'User IP',
	'IP_HOSTNAME'			=> 'IP addresses or hostnames',

	'LATEST_VERSION'		=> 'Latest version',
	'LOAD_NOTIFICATIONS'			=> 'Display Notifications',
	'LOAD_NOTIFICATIONS_EXPLAIN'	=> 'Display the notifications list on every page (typically in the header).',
	'LOGGED_IN_AS'			=> 'You are logged in as:',
	'LOGIN_ADMIN'			=> 'To administer the board you must be an authenticated user.',
	'LOGIN_ADMIN_CONFIRM'	=> 'To administer the board you must re-authenticate yourself.',
	'LOGIN_ADMIN_SUCCESS'	=> 'You have successfully authenticated and will now be redirected to the Administration Control Panel.',
	'LOOK_UP_FORUM'			=> 'Select a forum',
	'LOOK_UP_FORUMS_EXPLAIN'=> 'You are able to select more than one forum.',

	'MANAGE'				=> 'Manage',
	'MENU_TOGGLE'			=> 'Hide or display the side menu',
	'MORE'					=> 'More',			// Not used at the moment
	'MORE_INFORMATION'		=> 'More information »',
	'MOVE_DOWN'				=> 'Move down',
	'MOVE_UP'				=> 'Move up',

	'NOTIFY'				=> 'Notification',
	'NO_ADMIN'				=> 'You are not authorised to administer this board.',
	'NO_EMAILS_DEFINED'		=> 'No valid email addresses found.',
	'NO_FILES_TO_DELETE'	=> 'Attachments you selected for deletion do not exist.',
	'NO_PASSWORD_SUPPLIED'	=> 'You need to enter your password to access the Administration Control Panel.',

	'OFF'					=> 'Off',
	'ON'					=> 'On',

	'PARSE_BBCODE'						=> 'Parse BBCode',
	'PARSE_SMILIES'						=> 'Parse smilies',
	'PARSE_URLS'						=> 'Parse links',
	'PERMISSIONS_TRANSFERRED'			=> 'Permissions transferred',
	'PERMISSIONS_TRANSFERRED_EXPLAIN'	=> 'You currently have the permissions from %1$s. You are able to browse the board with this user’s permissions, but not access the administration control panel since admin permissions were not transferred. You can <a href="%2$s"><strong>revert to your permission set</strong></a> at any time.',
	'PROCEED_TO_ACP'					=> '%sProceed to the ACP%s',

	'RELEASE_ANNOUNCEMENT'		=> 'Announcement',
	'REMIND'							=> 'Remind',
	'REPARSE_LOCK_ERROR'				=> 'Reparsing is already in progress by another process.',
	'RESYNC'							=> 'Resynchronise',

	'RUNNING_TASK'			=> 'Running task: %s.',
	'SELECT_ANONYMOUS'		=> 'Select anonymous user',
	'SELECT_OPTION'			=> 'Select option',

	'SETTING_TOO_LOW'		=> 'The provided value for the setting “%1$s” is too low. The minimum acceptable value is %2$d.',
	'SETTING_TOO_BIG'		=> 'The provided value for the setting “%1$s” is too high. The maximum acceptable value is %2$d.',
	'SETTING_TOO_LONG'		=> 'The provided value for the setting “%1$s” is too long. The maximum acceptable length is %2$d.',
	'SETTING_TOO_SHORT'		=> 'The provided value for the setting “%1$s” is too short. The minimum acceptable length is %2$d.',

	'SHOW_ALL_OPERATIONS'	=> 'Show all operations',

	'TASKS_NOT_READY'			=> 'Not ready tasks:',
	'TASKS_READY'			=> 'Ready tasks:',
	'TOTAL_SIZE'			=> 'Total size',

	'UCP'					=> 'User Control Panel',
	'USERNAMES_EXPLAIN'		=> 'Place each username on a separate line.',
	'USER_CONTROL_PANEL'	=> 'User Control Panel',

	'WARNING'				=> 'Warning',
));

// PHP info
$lang = array_merge($lang, array(
	'ACP_PHP_INFO_EXPLAIN'	=> 'This page lists information on the version of PHP installed on this server. It includes details of loaded modules, available variables and default settings. This information may be useful when diagnosing problems. Please be aware that some hosting companies will limit what information is displayed here for security reasons. You are advised to not give out any details on this page except when asked by <a href="https://www.phpbb.com/about/team/">official team members</a> on the support forums.',

	'NO_PHPINFO_AVAILABLE'	=> 'Information about your PHP configuration is unable to be determined. Phpinfo() has been disabled for security reasons.',
));

// Logs
$lang = array_merge($lang, array(
	'ACP_ADMIN_LOGS_EXPLAIN'	=> 'This lists all the actions carried out by board administrators. You can sort by username, date, IP or action. If you have appropriate permissions you can also clear individual operations or the log as a whole.',
	'ACP_CRITICAL_LOGS_EXPLAIN'	=> 'This lists the actions carried out by the board itself. This log provides you with information you are able to use for solving specific problems, for example non-delivery of emails. You can sort by username, date, IP or action. If you have appropriate permissions you can also clear individual operations or the log as a whole.',
	'ACP_MOD_LOGS_EXPLAIN'		=> 'This lists all actions done on forums, topics and posts as well as actions carried out on users by moderators, including banning. You can sort by username, date, IP or action. If you have appropriate permissions you can also clear individual operations or the log as a whole.',
	'ACP_USERS_LOGS_EXPLAIN'	=> 'This lists all actions carried out by users or on users (reports, warnings and user notes).',
	'ALL_ENTRIES'				=> 'All entries',

	'DISPLAY_LOG'	=> 'Display entries from previous',

	'NO_ENTRIES'	=> 'No log entries for this period.',

	'SORT_IP'		=> 'IP address',
	'SORT_DATE'		=> 'Date',
	'SORT_ACTION'	=> 'Log action',
));

// Index page
$lang = array_merge($lang, array(
	'ADMIN_INTRO'				=> 'Thank you for choosing phpBB as your board solution. This screen will give you a quick overview of all the various statistics of your board. The links on the left hand side of this screen allow you to control every aspect of your board experience. Each page will have instructions on how to use the tools.',
	'ADMIN_LOG'					=> 'Logged administrator actions',
	'ADMIN_LOG_INDEX_EXPLAIN'	=> 'This gives an overview of the last five actions carried out by board administrators. A full copy of the log can be viewed from the appropriate menu item or following the link below.',
	'AVATAR_DIR_SIZE'			=> 'Avatar directory size',

	'BOARD_STARTED'		=> 'Board started',
	'BOARD_VERSION'		=> 'Board version',

	'DATABASE_SERVER_INFO'	=> 'Database server',
	'DATABASE_SIZE'			=> 'Database size',

	// Enviroment configuration checks, mbstring related
	'ERROR_MBSTRING_FUNC_OVERLOAD'					=> 'Function overloading is improperly configured',
	'ERROR_MBSTRING_FUNC_OVERLOAD_EXPLAIN'			=> '<var>mbstring.func_overload</var> must be set to either 0 or 4. You can check the current value on the <samp>PHP information</samp> page.',
	'ERROR_MBSTRING_ENCODING_TRANSLATION'			=> 'Transparent character encoding is improperly configured',
	'ERROR_MBSTRING_ENCODING_TRANSLATION_EXPLAIN'	=> '<var>mbstring.encoding_translation</var> must be set to 0. You can check the current value on the <samp>PHP information</samp> page.',
	'ERROR_MBSTRING_HTTP_INPUT'						=> 'HTTP input character conversion is improperly configured',
	'ERROR_MBSTRING_HTTP_INPUT_EXPLAIN'				=> '<var>mbstring.http_input</var> must be set to <samp>pass</samp>. You can check the current value on the <samp>PHP information</samp> page.',
	'ERROR_MBSTRING_HTTP_OUTPUT'					=> 'HTTP output character conversion is improperly configured',
	'ERROR_MBSTRING_HTTP_OUTPUT_EXPLAIN'			=> '<var>mbstring.http_output</var> must be set to <samp>pass</samp>. You can check the current value on the <samp>PHP information</samp> page.',

	'FILES_PER_DAY'		=> 'Attachments per day',
	'FORUM_STATS'		=> 'Board statistics',

	'GZIP_COMPRESSION'	=> 'GZip compression',

	'NO_SEARCH_INDEX'	=> 'The selected search backend does not have a search index.<br />Please create the index for “%1$s” in the %2$ssearch index%3$s section.',
	'NOT_AVAILABLE'		=> 'Not available',
	'NUMBER_FILES'		=> 'Number of attachments',
	'NUMBER_POSTS'		=> 'Number of posts',
	'NUMBER_TOPICS'		=> 'Number of topics',
	'NUMBER_USERS'		=> 'Number of users',
	'NUMBER_ORPHAN'		=> 'Orphan attachments',

	'PHP_VERSION_OLD'	=> 'The version of PHP on this server will no longer be supported by future versions of phpBB. %sDetails%s',

	'POSTS_PER_DAY'		=> 'Posts per day',

	'PURGE_CACHE'			=> 'Purge the cache',
	'PURGE_CACHE_CONFIRM'	=> 'Are you sure you wish to purge the cache?',
	'PURGE_CACHE_EXPLAIN'	=> 'Purge all cache related items, this includes any cached template files or queries.',
	'PURGE_CACHE_SUCCESS'	=> 'Cache successfully purged.',

	'PURGE_SESSIONS'			=> 'Purge all sessions',
	'PURGE_SESSIONS_CONFIRM'	=> 'Are you sure you wish to purge all sessions? This will log out all users.',
	'PURGE_SESSIONS_EXPLAIN'	=> 'Purge all sessions. This will log out all users by truncating the session table.',
	'PURGE_SESSIONS_SUCCESS'	=> 'Sessions successfully purged.',

	'RESET_DATE'					=> 'Reset board’s start date',
	'RESET_DATE_CONFIRM'			=> 'Are you sure you wish to reset the board’s start date?',
	'RESET_DATE_SUCCESS'				=> 'Board’s start date reset',
	'RESET_ONLINE'					=> 'Reset most users ever online',
	'RESET_ONLINE_CONFIRM'			=> 'Are you sure you wish to reset the most users ever online counter?',
	'RESET_ONLINE_SUCCESS'				=> 'Most users ever online reset',
	'RESYNC_POSTCOUNTS'				=> 'Resynchronise post counts',
	'RESYNC_POSTCOUNTS_EXPLAIN'		=> 'Only existing posts will be taken into consideration. Pruned posts will not be counted.',
	'RESYNC_POSTCOUNTS_CONFIRM'		=> 'Are you sure you wish to resynchronise post counts?',
	'RESYNC_POSTCOUNTS_SUCCESS'			=> 'Resynchronised post counts',
	'RESYNC_POST_MARKING'			=> 'Resynchronise dotted topics',
	'RESYNC_POST_MARKING_CONFIRM'	=> 'Are you sure you wish to resynchronise dotted topics?',
	'RESYNC_POST_MARKING_EXPLAIN'	=> 'First unmarks all topics and then correctly marks topics that have seen any activity during the past six months.',
	'RESYNC_POST_MARKING_SUCCESS'	=> 'Resynchronised dotted topics',
	'RESYNC_STATS'					=> 'Resynchronise statistics',
	'RESYNC_STATS_CONFIRM'			=> 'Are you sure you wish to resynchronise statistics?',
	'RESYNC_STATS_EXPLAIN'			=> 'Recalculates the total number of posts, topics, users and files.',
	'RESYNC_STATS_SUCCESS'			=> 'Resynchronised statistics',
	'RUN'							=> 'Run now',

	'STATISTIC'					=> 'Statistic',
	'STATISTIC_RESYNC_OPTIONS'	=> 'Resynchronise or reset statistics',

	'TIMEZONE_INVALID'	=> 'The timezone you selected is invalid.',
	'TIMEZONE_SELECTED'	=> '(currently selected)',
	'TOPICS_PER_DAY'	=> 'Topics per day',

	'UPLOAD_DIR_SIZE'	=> 'Size of posted attachments',
	'USERS_PER_DAY'		=> 'Users per day',

	'VALUE'						=> 'Value',
	'VERSIONCHECK_FAIL'			=> 'Failed to obtain latest version information.',
	'VERSIONCHECK_FORCE_UPDATE'	=> 'Re-Check version',
	'VERSION_CHECK'				=> 'Version check',
	'VERSION_CHECK_EXPLAIN'		=> 'Checks to see if your phpBB installation is up to date.',
	'VERSION_NOT_UP_TO_DATE_ACP'	=> 'Your phpBB installation is not up to date.<br />Below is a link to the release announcement, which contains more information as well as instructions on updating.',
	'VERSION_NOT_UP_TO_DATE_TITLE'	=> 'Your phpBB installation is not up to date.',
	'VERSION_UP_TO_DATE_ACP'	=> 'Your phpBB installation is up to date. There are no updates available at this time.',
	'VIEW_ADMIN_LOG'			=> 'View administrator log',
	'VIEW_INACTIVE_USERS'		=> 'View inactive users',

	'WELCOME_PHPBB'			=> 'Welcome to phpBB',
	'WRITABLE_CONFIG'		=> 'Your config file (config.php) is currently world-writable. We strongly encourage you to change the permissions to 640 or at least to 644 (for example: <a href="http://en.wikipedia.org/wiki/Chmod" rel="external">chmod</a> 640 config.php).',
));

// Inactive Users
$lang = array_merge($lang, array(
	'INACTIVE_DATE'					=> 'Inactive date',
	'INACTIVE_REASON'				=> 'Reason',
	'INACTIVE_REASON_MANUAL'		=> 'Account deactivated by administrator',
	'INACTIVE_REASON_PROFILE'		=> 'Profile details changed',
	'INACTIVE_REASON_REGISTER'		=> 'Newly registered account',
	'INACTIVE_REASON_REMIND'		=> 'Forced user account reactivation',
	'INACTIVE_REASON_UNKNOWN'		=> 'Unknown',
	'INACTIVE_USERS'				=> 'Inactive users',
	'INACTIVE_USERS_EXPLAIN'		=> 'This is a list of users who have registered but whose accounts are inactive. You can activate, delete or remind (by sending an email) these users if you wish.',
	'INACTIVE_USERS_EXPLAIN_INDEX'	=> 'This is a list of the last 10 registered users who have inactive accounts. Accounts are inactive either because account activation was enabled in user registration settings and these users’ accounts have not yet been activated, or because these accounts have been deactivated. A full list is available by following the link below from where you can activate, delete or remind (by sending an email) these users if you wish.',

	'NO_INACTIVE_USERS'	=> 'No inactive users',

	'SORT_INACTIVE'		=> 'Inactive date',
	'SORT_LAST_VISIT'	=> 'Last visit',
	'SORT_REASON'		=> 'Reason',
	'SORT_REG_DATE'		=> 'Registration date',
	'SORT_LAST_REMINDER'=> 'Last reminded',
	'SORT_REMINDER'		=> 'Reminder sent',

	'USER_IS_INACTIVE'		=> 'User is inactive',
));

// Send statistics page
$lang = array_merge($lang, array(
	'EXPLAIN_SEND_STATISTICS'	=> 'Please send information about your server and board configurations to phpBB for statistical analysis. All information that could identify you or your website has been removed - the data is entirely <strong>anonymous</strong>. We base decisions about future phpBB versions on this information. The statistics are made available publically. We also share this data with the PHP project, the programming language phpBB is made with.',
	'EXPLAIN_SHOW_STATISTICS'	=> 'Using the button below you can preview all variables that will be transmitted.',
	'DONT_SEND_STATISTICS'		=> 'Return to the ACP if you do not wish to send statistical information to phpBB.',
	'GO_ACP_MAIN'				=> 'Go to the ACP start page',
	'HIDE_STATISTICS'			=> 'Hide details',
	'SEND_STATISTICS'			=> 'Send statistical information',
	'SHOW_STATISTICS'			=> 'Show details',
	'THANKS_SEND_STATISTICS'	=> 'Thank you for submitting your information.',
));

// Log Entries
$lang = array_merge($lang, array(
	'LOG_ACL_ADD_USER_GLOBAL_U_'		=> '<strong>Added or edited users’ user permissions</strong><br />» %s',
	'LOG_ACL_ADD_GROUP_GLOBAL_U_'		=> '<strong>Added or edited groups’ user permissions</strong><br />» %s',
	'LOG_ACL_ADD_USER_GLOBAL_M_'		=> '<strong>Added or edited users’ global moderator permissions</strong><br />» %s',
	'LOG_ACL_ADD_GROUP_GLOBAL_M_'		=> '<strong>Added or edited groups’ global moderator permissions</strong><br />» %s',
	'LOG_ACL_ADD_USER_GLOBAL_A_'		=> '<strong>Added or edited users’ administrator permissions</strong><br />» %s',
	'LOG_ACL_ADD_GROUP_GLOBAL_A_'		=> '<strong>Added or edited groups’ administrator permissions</strong><br />» %s',

	'LOG_ACL_ADD_ADMIN_GLOBAL_A_'		=> '<strong>Added or edited Administrators</strong><br />» %s',
	'LOG_ACL_ADD_MOD_GLOBAL_M_'			=> '<strong>Added or edited Global Moderators</strong><br />» %s',

	'LOG_ACL_ADD_USER_LOCAL_F_'			=> '<strong>Added or edited users’ forum access</strong> from %1$s<br />» %2$s',
	'LOG_ACL_ADD_USER_LOCAL_M_'			=> '<strong>Added or edited users’ forum moderator access</strong> from %1$s<br />» %2$s',
	'LOG_ACL_ADD_GROUP_LOCAL_F_'		=> '<strong>Added or edited groups’ forum access</strong> from %1$s<br />» %2$s',
	'LOG_ACL_ADD_GROUP_LOCAL_M_'		=> '<strong>Added or edited groups’ forum moderator access</strong> from %1$s<br />» %2$s',

	'LOG_ACL_ADD_MOD_LOCAL_M_'			=> '<strong>Added or edited Moderators</strong> from %1$s<br />» %2$s',
	'LOG_ACL_ADD_FORUM_LOCAL_F_'		=> '<strong>Added or edited forum permissions</strong> from %1$s<br />» %2$s',

	'LOG_ACL_DEL_ADMIN_GLOBAL_A_'		=> '<strong>Removed Administrators</strong><br />» %s',
	'LOG_ACL_DEL_MOD_GLOBAL_M_'			=> '<strong>Removed Global Moderators</strong><br />» %s',
	'LOG_ACL_DEL_MOD_LOCAL_M_'			=> '<strong>Removed Moderators</strong> from %1$s<br />» %2$s',
	'LOG_ACL_DEL_FORUM_LOCAL_F_'		=> '<strong>Removed User/Group forum permissions</strong> from %1$s<br />» %2$s',

	'LOG_ACL_TRANSFER_PERMISSIONS'		=> '<strong>Permissions transferred from</strong><br />» %s',
	'LOG_ACL_RESTORE_PERMISSIONS'		=> '<strong>Own permissions restored after using permissions from</strong><br />» %s',

	'LOG_ADMIN_AUTH_FAIL'		=> '<strong>Failed administration login attempt</strong>',
	'LOG_ADMIN_AUTH_SUCCESS'	=> '<strong>Successful administration login</strong>',

	'LOG_ATTACHMENTS_DELETED'	=> '<strong>Removed user attachments</strong><br />» %s',

	'LOG_ATTACH_EXT_ADD'		=> '<strong>Added or edited attachment extension</strong><br />» %s',
	'LOG_ATTACH_EXT_DEL'		=> '<strong>Removed attachment extension</strong><br />» %s',
	'LOG_ATTACH_EXT_UPDATE'		=> '<strong>Updated attachment extension</strong><br />» %s',
	'LOG_ATTACH_EXTGROUP_ADD'	=> '<strong>Added extension group</strong><br />» %s',
	'LOG_ATTACH_EXTGROUP_EDIT'	=> '<strong>Edited extension group</strong><br />» %s',
	'LOG_ATTACH_EXTGROUP_DEL'	=> '<strong>Removed extension group</strong><br />» %s',
	'LOG_ATTACH_FILEUPLOAD'		=> '<strong>Orphan File uploaded to Post</strong><br />» ID %1$d - %2$s',
	'LOG_ATTACH_ORPHAN_DEL'		=> '<strong>Orphan Files deleted</strong><br />» %s',

	'LOG_BAN_EXCLUDE_USER'	=> '<strong>Excluded user from ban</strong> for reason “<em>%1$s</em>”<br />» %2$s',
	'LOG_BAN_EXCLUDE_IP'	=> '<strong>Excluded IP from ban</strong> for reason “<em>%1$s</em>”<br />» %2$s',
	'LOG_BAN_EXCLUDE_EMAIL' => '<strong>Excluded email from ban</strong> for reason “<em>%1$s</em>”<br />» %2$s',
	'LOG_BAN_USER'			=> '<strong>Banned user</strong> for reason “<em>%1$s</em>”<br />» %2$s',
	'LOG_BAN_IP'			=> '<strong>Banned IP</strong> for reason “<em>%1$s</em>”<br />» %2$s',
	'LOG_BAN_EMAIL'			=> '<strong>Banned email</strong> for reason “<em>%1$s</em>”<br />» %2$s',
	'LOG_UNBAN_USER'		=> '<strong>Unbanned user</strong><br />» %s',
	'LOG_UNBAN_IP'			=> '<strong>Unbanned IP</strong><br />» %s',
	'LOG_UNBAN_EMAIL'		=> '<strong>Unbanned email</strong><br />» %s',

	'LOG_BBCODE_ADD'		=> '<strong>Added new BBCode</strong><br />» %s',
	'LOG_BBCODE_EDIT'		=> '<strong>Edited BBCode</strong><br />» %s',
	'LOG_BBCODE_DELETE'		=> '<strong>Deleted BBCode</strong><br />» %s',

	'LOG_BOT_ADDED'		=> '<strong>New bot added</strong><br />» %s',
	'LOG_BOT_DELETE'	=> '<strong>Deleted bot</strong><br />» %s',
	'LOG_BOT_UPDATED'	=> '<strong>Existing bot updated</strong><br />» %s',

	'LOG_CLEAR_ADMIN'		=> '<strong>Cleared admin log</strong>',
	'LOG_CLEAR_CRITICAL'	=> '<strong>Cleared error log</strong>',
	'LOG_CLEAR_MOD'			=> '<strong>Cleared moderator log</strong>',
	'LOG_CLEAR_USER'		=> '<strong>Cleared user log</strong><br />» %s',
	'LOG_CLEAR_USERS'		=> '<strong>Cleared user logs</strong>',

	'LOG_CONFIG_ATTACH'			=> '<strong>Altered attachment settings</strong>',
	'LOG_CONFIG_AUTH'			=> '<strong>Altered authentication settings</strong>',
	'LOG_CONFIG_AVATAR'			=> '<strong>Altered avatar settings</strong>',
	'LOG_CONFIG_COOKIE'			=> '<strong>Altered cookie settings</strong>',
	'LOG_CONFIG_EMAIL'			=> '<strong>Altered email settings</strong>',
	'LOG_CONFIG_FEATURES'		=> '<strong>Altered board features</strong>',
	'LOG_CONFIG_LOAD'			=> '<strong>Altered load settings</strong>',
	'LOG_CONFIG_MESSAGE'		=> '<strong>Altered private message settings</strong>',
	'LOG_CONFIG_POST'			=> '<strong>Altered post settings</strong>',
	'LOG_CONFIG_REGISTRATION'	=> '<strong>Altered user registration settings</strong>',
	'LOG_CONFIG_FEED'			=> '<strong>Altered syndication feeds settings</strong>',
	'LOG_CONFIG_SEARCH'			=> '<strong>Altered search settings</strong>',
	'LOG_CONFIG_SECURITY'		=> '<strong>Altered security settings</strong>',
	'LOG_CONFIG_SERVER'			=> '<strong>Altered server settings</strong>',
	'LOG_CONFIG_SETTINGS'		=> '<strong>Altered board settings</strong>',
	'LOG_CONFIG_SIGNATURE'		=> '<strong>Altered signature settings</strong>',
	'LOG_CONFIG_VISUAL'			=> '<strong>Altered anti-spambot settings</strong>',

	'LOG_APPROVE_TOPIC'			=> '<strong>Approved topic</strong><br />» %s',
	'LOG_BUMP_TOPIC'			=> '<strong>User bumped topic</strong><br />» %s',
	'LOG_DELETE_POST'			=> '<strong>Deleted post “%1$s” written by “%2$s” for the following reason</strong><br />» %3$s',
	'LOG_DELETE_SHADOW_TOPIC'	=> '<strong>Deleted shadow topic</strong><br />» %s',
	'LOG_DELETE_TOPIC'			=> '<strong>Deleted topic “%1$s” written by “%2$s” for the following reason</strong><br />» %3$s',
	'LOG_FORK'					=> '<strong>Copied topic</strong><br />» from %s',
	'LOG_LOCK'					=> '<strong>Locked topic</strong><br />» %s',
	'LOG_LOCK_POST'				=> '<strong>Locked post</strong><br />» %s',
	'LOG_MERGE'					=> '<strong>Merged posts</strong> into topic<br />» %s',
	'LOG_MOVE'					=> '<strong>Moved topic</strong><br />» from %1$s to %2$s',
	'LOG_MOVED_TOPIC'			=> '<strong>Moved topic</strong><br />» %s',
	'LOG_PM_REPORT_CLOSED'		=> '<strong>Closed PM report</strong><br />» %s',
	'LOG_PM_REPORT_DELETED'		=> '<strong>Deleted PM report</strong><br />» %s',
	'LOG_POST_APPROVED'			=> '<strong>Approved post</strong><br />» %s',
	'LOG_POST_DISAPPROVED'		=> '<strong>Disapproved post “%1$s” written by “%3$s” for the following reason</strong><br />» %2$s',
	'LOG_POST_EDITED'			=> '<strong>Edited post “%1$s” written by “%2$s” for the following reason</strong><br />» %3$s',
	'LOG_POST_RESTORED'			=> '<strong>Restored post</strong><br />» %s',
	'LOG_REPORT_CLOSED'			=> '<strong>Closed report</strong><br />» %s',
	'LOG_REPORT_DELETED'		=> '<strong>Deleted report</strong><br />» %s',
	'LOG_RESTORE_TOPIC'			=> '<strong>Restored topic “%1$s” written by</strong><br />» %2$s',
	'LOG_SOFTDELETE_POST'		=> '<strong>Soft deleted post “%1$s” written by “%2$s” for the following reason</strong><br />» %3$s',
	'LOG_SOFTDELETE_TOPIC'		=> '<strong>Soft deleted topic “%1$s” written by “%2$s” for the following reason</strong><br />» %3$s',
	'LOG_SPLIT_DESTINATION'		=> '<strong>Moved split posts</strong><br />» to %s',
	'LOG_SPLIT_SOURCE'			=> '<strong>Split posts</strong><br />» from %s',

	'LOG_TOPIC_APPROVED'		=> '<strong>Approved topic</strong><br />» %s',
	'LOG_TOPIC_RESTORED'		=> '<strong>Restored topic</strong><br />» %s',
	'LOG_TOPIC_DISAPPROVED'		=> '<strong>Disapproved topic “%1$s” written by “%3$s” for the following reason</strong><br />» %2$s',
	'LOG_TOPIC_RESYNC'			=> '<strong>Resynchronised topic counters</strong><br />» %s',
	'LOG_TOPIC_TYPE_CHANGED'	=> '<strong>Changed topic type</strong><br />» %s',
	'LOG_UNLOCK'				=> '<strong>Unlocked topic</strong><br />» %s',
	'LOG_UNLOCK_POST'			=> '<strong>Unlocked post</strong><br />» %s',

	'LOG_DISALLOW_ADD'		=> '<strong>Added disallowed username</strong><br />» %s',
	'LOG_DISALLOW_DELETE'	=> '<strong>Deleted disallowed username</strong>',

	'LOG_DB_BACKUP'			=> '<strong>Database backup</strong>',
	'LOG_DB_DELETE'			=> '<strong>Deleted database backup</strong>',
	'LOG_DB_RESTORE'		=> '<strong>Restored database backup</strong>',

	'LOG_DOWNLOAD_EXCLUDE_IP'	=> '<strong>Excluded IP/hostname from download list</strong><br />» %s',
	'LOG_DOWNLOAD_IP'			=> '<strong>Added IP/hostname to download list</strong><br />» %s',
	'LOG_DOWNLOAD_REMOVE_IP'	=> '<strong>Removed IP/hostname from download list</strong><br />» %s',

	'LOG_ERROR_JABBER'		=> '<strong>Jabber error</strong><br />» %s',
	'LOG_ERROR_EMAIL'		=> '<strong>Email error</strong><br />» %s',

	'LOG_FORUM_ADD'							=> '<strong>Created new forum</strong><br />» %s',
	'LOG_FORUM_COPIED_PERMISSIONS'			=> '<strong>Copied forum permissions</strong> from %1$s<br />» %2$s',
	'LOG_FORUM_DEL_FORUM'					=> '<strong>Deleted forum</strong><br />» %s',
	'LOG_FORUM_DEL_FORUMS'					=> '<strong>Deleted forum and its subforums</strong><br />» %s',
	'LOG_FORUM_DEL_MOVE_FORUMS'				=> '<strong>Deleted forum and moved subforums</strong> to %1$s<br />» %2$s',
	'LOG_FORUM_DEL_MOVE_POSTS'				=> '<strong>Deleted forum and moved posts </strong> to %1$s<br />» %2$s',
	'LOG_FORUM_DEL_MOVE_POSTS_FORUMS'		=> '<strong>Deleted forum and its subforums, moved posts</strong> to %1$s<br />» %2$s',
	'LOG_FORUM_DEL_MOVE_POSTS_MOVE_FORUMS'	=> '<strong>Deleted forum, moved posts</strong> to %1$s <strong>and subforums</strong> to %2$s<br />» %3$s',
	'LOG_FORUM_DEL_POSTS'					=> '<strong>Deleted forum and its posts</strong><br />» %s',
	'LOG_FORUM_DEL_POSTS_FORUMS'			=> '<strong>Deleted forum, its posts and subforums</strong><br />» %s',
	'LOG_FORUM_DEL_POSTS_MOVE_FORUMS'		=> '<strong>Deleted forum and its posts, moved subforums</strong> to %1$s<br />» %2$s',
	'LOG_FORUM_EDIT'						=> '<strong>Edited forum details</strong><br />» %s',
	'LOG_FORUM_MOVE_DOWN'					=> '<strong>Moved forum</strong> %1$s <strong>below</strong> %2$s',
	'LOG_FORUM_MOVE_UP'						=> '<strong>Moved forum</strong> %1$s <strong>above</strong> %2$s',
	'LOG_FORUM_SYNC'						=> '<strong>Re-synchronised forum</strong><br />» %s',

	'LOG_GENERAL_ERROR'	=> '<strong>A general error occurred</strong>: %1$s <br />» %2$s',

	'LOG_GROUP_CREATED'		=> '<strong>New usergroup created</strong><br />» %s',
	'LOG_GROUP_DEFAULTS'	=> '<strong>Group “%1$s” made default for members</strong><br />» %2$s',
	'LOG_GROUP_DELETE'		=> '<strong>Usergroup deleted</strong><br />» %s',
	'LOG_GROUP_DEMOTED'		=> '<strong>Leaders demoted in usergroup</strong> %1$s<br />» %2$s',
	'LOG_GROUP_PROMOTED'	=> '<strong>Members promoted to leader in usergroup</strong> %1$s<br />» %2$s',
	'LOG_GROUP_REMOVE'		=> '<strong>Members removed from usergroup</strong> %1$s<br />» %2$s',
	'LOG_GROUP_UPDATED'		=> '<strong>Usergroup details updated</strong><br />» %s',
	'LOG_MODS_ADDED'		=> '<strong>Added new leaders to usergroup</strong> %1$s<br />» %2$s',
	'LOG_USERS_ADDED'		=> '<strong>Added new members to usergroup</strong> %1$s<br />» %2$s',
	'LOG_USERS_APPROVED'	=> '<strong>Users approved in usergroup</strong> %1$s<br />» %2$s',
	'LOG_USERS_PENDING'		=> '<strong>Users requested to join group “%1$s” and need to be approved</strong><br />» %2$s',

	'LOG_IMAGE_GENERATION_ERROR'	=> '<strong>Error while creating image</strong><br />» Error in %1$s on line %2$s: %3$s',

	'LOG_INACTIVE_ACTIVATE'	=> '<strong>Activated inactive users</strong><br />» %s',
	'LOG_INACTIVE_DELETE'	=> '<strong>Deleted inactive users</strong><br />» %s',
	'LOG_INACTIVE_REMIND'	=> '<strong>Sent reminder emails to inactive users</strong><br />» %s',
	'LOG_INSTALL_CONVERTED'	=> '<strong>Converted from %1$s to phpBB %2$s</strong>',
	'LOG_INSTALL_INSTALLED'	=> '<strong>Installed phpBB %s</strong>',

	'LOG_IP_BROWSER_FORWARDED_CHECK'	=> '<strong>Session IP/browser/X_FORWARDED_FOR check failed</strong><br />»User IP “<em>%1$s</em>” checked against session IP “<em>%2$s</em>”, user browser string “<em>%3$s</em>” checked against session browser string “<em>%4$s</em>” and user X_FORWARDED_FOR string “<em>%5$s</em>” checked against session X_FORWARDED_FOR string “<em>%6$s</em>”.',

	'LOG_JAB_CHANGED'			=> '<strong>Jabber account changed</strong>',
	'LOG_JAB_PASSCHG'			=> '<strong>Jabber password changed</strong>',
	'LOG_JAB_REGISTER'			=> '<strong>Jabber account registered</strong>',
	'LOG_JAB_SETTINGS_CHANGED'	=> '<strong>Jabber settings changed</strong>',

	'LOG_LANGUAGE_PACK_DELETED'		=> '<strong>Deleted language pack</strong><br />» %s',
	'LOG_LANGUAGE_PACK_INSTALLED'	=> '<strong>Installed language pack</strong><br />» %s',
	'LOG_LANGUAGE_PACK_UPDATED'		=> '<strong>Updated language pack details</strong><br />» %s',
	'LOG_LANGUAGE_FILE_REPLACED'	=> '<strong>Replaced language file</strong><br />» %s',
	'LOG_LANGUAGE_FILE_SUBMITTED'	=> '<strong>Submitted language file and placed in store folder</strong><br />» %s',

	'LOG_MASS_EMAIL'		=> '<strong>Sent mass email</strong><br />» %s',

	'LOG_MCP_CHANGE_POSTER'	=> '<strong>Changed poster in topic “%1$s”</strong><br />» from %2$s to %3$s',

	'LOG_MODULE_DISABLE'	=> '<strong>Module disabled</strong><br />» %s',
	'LOG_MODULE_ENABLE'		=> '<strong>Module enabled</strong><br />» %s',
	'LOG_MODULE_MOVE_DOWN'	=> '<strong>Module moved down</strong><br />» %1$s below %2$s',
	'LOG_MODULE_MOVE_UP'	=> '<strong>Module moved up</strong><br />» %1$s above %2$s',
	'LOG_MODULE_REMOVED'	=> '<strong>Module removed</strong><br />» %s',
	'LOG_MODULE_ADD'		=> '<strong>Module added</strong><br />» %s',
	'LOG_MODULE_EDIT'		=> '<strong>Module edited</strong><br />» %s',

	'LOG_A_ROLE_ADD'		=> '<strong>Admin role added</strong><br />» %s',
	'LOG_A_ROLE_EDIT'		=> '<strong>Admin role edited</strong><br />» %s',
	'LOG_A_ROLE_REMOVED'	=> '<strong>Admin role removed</strong><br />» %s',
	'LOG_F_ROLE_ADD'		=> '<strong>Forum role added</strong><br />» %s',
	'LOG_F_ROLE_EDIT'		=> '<strong>Forum role edited</strong><br />» %s',
	'LOG_F_ROLE_REMOVED'	=> '<strong>Forum role removed</strong><br />» %s',
	'LOG_M_ROLE_ADD'		=> '<strong>Moderator role added</strong><br />» %s',
	'LOG_M_ROLE_EDIT'		=> '<strong>Moderator role edited</strong><br />» %s',
	'LOG_M_ROLE_REMOVED'	=> '<strong>Moderator role removed</strong><br />» %s',
	'LOG_U_ROLE_ADD'		=> '<strong>User role added</strong><br />» %s',
	'LOG_U_ROLE_EDIT'		=> '<strong>User role edited</strong><br />» %s',
	'LOG_U_ROLE_REMOVED'	=> '<strong>User role removed</strong><br />» %s',

	'LOG_PLUPLOAD_TIDY_FAILED'		=> '<strong>Unable to open %1$s for tidying, check permissions.</strong><br />Exception: %2$s<br />Trace: %3$s',

	'LOG_PROFILE_FIELD_ACTIVATE'	=> '<strong>Profile field activated</strong><br />» %s',
	'LOG_PROFILE_FIELD_CREATE'		=> '<strong>Profile field added</strong><br />» %s',
	'LOG_PROFILE_FIELD_DEACTIVATE'	=> '<strong>Profile field deactivated</strong><br />» %s',
	'LOG_PROFILE_FIELD_EDIT'		=> '<strong>Profile field changed</strong><br />» %s',
	'LOG_PROFILE_FIELD_REMOVED'		=> '<strong>Profile field removed</strong><br />» %s',

	'LOG_PRUNE'					=> '<strong>Pruned forums</strong><br />» %s',
	'LOG_AUTO_PRUNE'			=> '<strong>Auto-pruned forums</strong><br />» %s',
	'LOG_PRUNE_SHADOW'		=> '<strong>Auto-pruned shadow topics</strong><br />» %s',
	'LOG_PRUNE_USER_DEAC'		=> '<strong>Users deactivated</strong><br />» %s',
	'LOG_PRUNE_USER_DEL_DEL'	=> '<strong>Users pruned and posts deleted</strong><br />» %s',
	'LOG_PRUNE_USER_DEL_ANON'	=> '<strong>Users pruned and posts retained</strong><br />» %s',

	'LOG_PURGE_CACHE'			=> '<strong>Purged cache</strong>',
	'LOG_PURGE_SESSIONS'		=> '<strong>Purged sessions</strong>',

	'LOG_RANK_ADDED'		=> '<strong>Added new rank</strong><br />» %s',
	'LOG_RANK_REMOVED'		=> '<strong>Removed rank</strong><br />» %s',
	'LOG_RANK_UPDATED'		=> '<strong>Updated rank</strong><br />» %s',

	'LOG_REASON_ADDED'		=> '<strong>Added report/denial reason</strong><br />» %s',
	'LOG_REASON_REMOVED'	=> '<strong>Removed report/denial reason</strong><br />» %s',
	'LOG_REASON_UPDATED'	=> '<strong>Updated report/denial reason</strong><br />» %s',

	'LOG_REFERER_INVALID'		=> '<strong>Referrer validation failed</strong><br />»Referrer was “<em>%1$s</em>”. The request was rejected and the session killed.',
	'LOG_RESET_DATE'			=> '<strong>Board start date reset</strong>',
	'LOG_RESET_ONLINE'			=> '<strong>Most users online reset</strong>',
	'LOG_RESYNC_FILES_STATS'	=> '<strong>File statistics resynchronised</strong>',
	'LOG_RESYNC_POSTCOUNTS'		=> '<strong>User post counts resynchronised</strong>',
	'LOG_RESYNC_POST_MARKING'	=> '<strong>Dotted topics resynchronised</strong>',
	'LOG_RESYNC_STATS'			=> '<strong>Post, topic and user statistics resynchronised</strong>',

	'LOG_SEARCH_INDEX_CREATED'	=> '<strong>Created search index for</strong><br />» %s',
	'LOG_SEARCH_INDEX_REMOVED'	=> '<strong>Removed search index for</strong><br />» %s',
	'LOG_SPHINX_ERROR'			=> '<strong>Sphinx Error</strong><br />» %s',
	'LOG_STYLE_ADD'				=> '<strong>Added new style</strong><br />» %s',
	'LOG_STYLE_DELETE'			=> '<strong>Deleted style</strong><br />» %s',
	'LOG_STYLE_EDIT_DETAILS'	=> '<strong>Edited style</strong><br />» %s',
	'LOG_STYLE_EXPORT'			=> '<strong>Exported style</strong><br />» %s',

	// @deprecated 3.1
	'LOG_TEMPLATE_ADD_DB'			=> '<strong>Added new template set to database</strong><br />» %s',
	// @deprecated 3.1
	'LOG_TEMPLATE_ADD_FS'			=> '<strong>Add new template set on filesystem</strong><br />» %s',
	'LOG_TEMPLATE_CACHE_CLEARED'	=> '<strong>Deleted cached versions of template files in template set <em>%1$s</em></strong><br />» %2$s',
	'LOG_TEMPLATE_DELETE'			=> '<strong>Deleted template set</strong><br />» %s',
	'LOG_TEMPLATE_EDIT'				=> '<strong>Edited template set <em>%1$s</em></strong><br />» %2$s',
	'LOG_TEMPLATE_EDIT_DETAILS'		=> '<strong>Edited template details</strong><br />» %s',
	'LOG_TEMPLATE_EXPORT'			=> '<strong>Exported template set</strong><br />» %s',
	// @deprecated 3.1
	'LOG_TEMPLATE_REFRESHED'		=> '<strong>Refreshed template set</strong><br />» %s',

	// @deprecated 3.1
	'LOG_THEME_ADD_DB'			=> '<strong>Added new theme to database</strong><br />» %s',
	// @deprecated 3.1
	'LOG_THEME_ADD_FS'			=> '<strong>Add new theme on filesystem</strong><br />» %s',
	'LOG_THEME_DELETE'			=> '<strong>Theme deleted</strong><br />» %s',
	'LOG_THEME_EDIT_DETAILS'	=> '<strong>Edited theme details</strong><br />» %s',
	'LOG_THEME_EDIT'			=> '<strong>Edited theme <em>%1$s</em></strong>',
	'LOG_THEME_EDIT_FILE'		=> '<strong>Edited theme <em>%1$s</em></strong><br />» Modified file <em>%2$s</em>',
	'LOG_THEME_EXPORT'			=> '<strong>Exported theme</strong><br />» %s',
	// @deprecated 3.1
	'LOG_THEME_REFRESHED'		=> '<strong>Refreshed theme</strong><br />» %s',

	'LOG_UPDATE_DATABASE'	=> '<strong>Updated Database from version %1$s to version %2$s</strong>',
	'LOG_UPDATE_PHPBB'		=> '<strong>Updated phpBB from version %1$s to version %2$s</strong>',

	'LOG_USER_ACTIVE'		=> '<strong>User activated</strong><br />» %s',
	'LOG_USER_BAN_USER'		=> '<strong>Banned User via user management</strong> for reason “<em>%1$s</em>”<br />» %2$s',
	'LOG_USER_BAN_IP'		=> '<strong>Banned IP via user management</strong> for reason “<em>%1$s</em>”<br />» %2$s',
	'LOG_USER_BAN_EMAIL'	=> '<strong>Banned email via user management</strong> for reason “<em>%1$s</em>”<br />» %2$s',
	'LOG_USER_DELETED'		=> '<strong>Deleted user</strong><br />» %s',
	'LOG_USER_DEL_ATTACH'	=> '<strong>Removed all attachments made by the user</strong><br />» %s',
	'LOG_USER_DEL_AVATAR'	=> '<strong>Removed user avatar</strong><br />» %s',
	'LOG_USER_DEL_OUTBOX'	=> '<strong>Emptied user outbox</strong><br />» %s',
	'LOG_USER_DEL_POSTS'	=> '<strong>Removed all posts made by the user</strong><br />» %s',
	'LOG_USER_DEL_SIG'		=> '<strong>Removed user signature</strong><br />» %s',
	'LOG_USER_INACTIVE'		=> '<strong>User deactivated</strong><br />» %s',
	'LOG_USER_MOVE_POSTS'	=> '<strong>Moved user posts</strong><br />» posts by “%1$s” to forum “%2$s”',
	'LOG_USER_NEW_PASSWORD'	=> '<strong>Changed user password</strong><br />» %s',
	'LOG_USER_REACTIVATE'	=> '<strong>Forced user account reactivation</strong><br />» %s',
	'LOG_USER_REMOVED_NR'	=> '<strong>Removed newly registered flag from user</strong><br />» %s',

	'LOG_USER_UPDATE_EMAIL'	=> '<strong>User “%1$s” changed email</strong><br />» from “%2$s” to “%3$s”',
	'LOG_USER_UPDATE_NAME'	=> '<strong>Changed username</strong><br />» from “%1$s” to “%2$s”',
	'LOG_USER_USER_UPDATE'	=> '<strong>Updated user details</strong><br />» %s',

	'LOG_USER_ACTIVE_USER'		=> '<strong>User account activated</strong>',
	'LOG_USER_DEL_AVATAR_USER'	=> '<strong>User avatar removed</strong>',
	'LOG_USER_DEL_SIG_USER'		=> '<strong>User signature removed</strong>',
	'LOG_USER_FEEDBACK'			=> '<strong>Added user feedback</strong><br />» %s',
	'LOG_USER_GENERAL'			=> '<strong>Entry added:</strong><br />» %s',
	'LOG_USER_INACTIVE_USER'	=> '<strong>User account de-activated</strong>',
	'LOG_USER_LOCK'				=> '<strong>User locked own topic</strong><br />» %s',
	'LOG_USER_MOVE_POSTS_USER'	=> '<strong>Moved all posts to forum</strong>» %s',
	'LOG_USER_REACTIVATE_USER'	=> '<strong>Forced user account reactivation</strong>',
	'LOG_USER_UNLOCK'			=> '<strong>User unlocked own topic</strong><br />» %s',
	'LOG_USER_WARNING'			=> '<strong>Added user warning</strong><br />» %s',
	'LOG_USER_WARNING_BODY'		=> '<strong>The following warning was issued to this user</strong><br />» %s',

	'LOG_USER_GROUP_CHANGE'			=> '<strong>User changed default group</strong><br />» %s',
	'LOG_USER_GROUP_DEMOTE'			=> '<strong>User demoted as leaders from usergroup</strong><br />» %s',
	'LOG_USER_GROUP_JOIN'			=> '<strong>User joined group</strong><br />» %s',
	'LOG_USER_GROUP_JOIN_PENDING'	=> '<strong>User joined group and needs to be approved</strong><br />» %s',
	'LOG_USER_GROUP_RESIGN'			=> '<strong>User resigned membership from group</strong><br />» %s',

	'LOG_WARNING_DELETED'		=> '<strong>Deleted user warning</strong><br />» %s',
	'LOG_WARNINGS_DELETED'		=> array(
		1 => '<strong>Deleted user warning</strong><br />» %1$s',
		2 => '<strong>Deleted %2$d user warnings</strong><br />» %1$s', // Example: '<strong>Deleted 2 user warnings</strong><br />» username'
	),
	'LOG_WARNINGS_DELETED_ALL'	=> '<strong>Deleted all user warnings</strong><br />» %s',

	'LOG_WORD_ADD'			=> '<strong>Added word censor</strong><br />» %s',
	'LOG_WORD_DELETE'		=> '<strong>Deleted word censor</strong><br />» %s',
	'LOG_WORD_EDIT'			=> '<strong>Edited word censor</strong><br />» %s',

	'LOG_EXT_ENABLE'	=> '<strong>Extension enabled</strong><br />» %s',
	'LOG_EXT_DISABLE'	=> '<strong>Extension disabled</strong><br />» %s',
	'LOG_EXT_PURGE'		=> '<strong>Extension’s data deleted</strong><br />» %s',
));
