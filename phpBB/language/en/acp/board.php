<?php
/** 
*
* acp_board [English]
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

// Board Settings
$lang = array_merge($lang, array(
	'ACP_BOARD_SETTINGS_EXPLAIN'	=> 'Here you can determine the basic operation of your board, from the site name through user registration to private messaging.',

	'CUSTOM_DATEFORMAT'				=> 'Custom…',
	'DEFAULT_DATE_FORMAT'			=> 'Date Format',
	'DEFAULT_DATE_FORMAT_EXPLAIN'	=> 'The date format is the same as the PHP <code>date</code> function.',
	'DEFAULT_LANGUAGE'				=> 'Default language',
	'DEFAULT_STYLE'					=> 'Default style',
	'DISABLE_BOARD'					=> 'Disable board',
	'DISABLE_BOARD_EXPLAIN'			=> 'This will make the board unavailable to users. You can also enter a short (255 character) message to display if you wish.',
	'OVERRIDE_STYLE'				=> 'Override user style',
	'OVERRIDE_STYLE_EXPLAIN'		=> 'Replaces users style with the default.',
	'RELATIVE_DAYS'					=> 'Relative days',
	'SITE_DESC'						=> 'Site description',
	'SITE_NAME'						=> 'Site name',
	'SYSTEM_DST'					=> 'Enable Daylight Savings Time',
	'SYSTEM_TIMEZONE'				=> 'System timezone',
	'WARNINGS_EXPIRE'				=> 'Warning duration',
	'WARNINGS_EXPIRE_EXPLAIN'		=> 'Number of days after it is issued before a warning will expire from a user’s record',
));

// Board Features
$lang = array_merge($lang, array(
	'ACP_BOARD_FEATURES_EXPLAIN'	=> 'Here you can enable/disable several board features',

	'ALLOW_ATTACHMENTS'			=> 'Allow attachments',
	'ALLOW_BOOKMARKS'			=> 'Allow bookmarking topics',
	'ALLOW_BOOKMARKS_EXPLAIN'	=> 'User is able to store personal bookmarks',
	'ALLOW_BBCODE'				=> 'Allow BBCode',
	'ALLOW_FORUM_NOTIFY'		=> 'Allow forum watching',
	'ALLOW_NAME_CHANGE'			=> 'Allow username changes',
	'ALLOW_NO_CENSORS'			=> 'Allow disable of censors',
	'ALLOW_NO_CENSORS_EXPLAIN'	=> 'User can disable word censoring.',
	'ALLOW_PM_ATTACHMENTS'		=> 'Allow attachments in private messages',
	'ALLOW_SIG'					=> 'Allow signatures',
	'ALLOW_SIG_BBCODE'			=> 'Allow BBCode in user signatures',
	'ALLOW_SIG_FLASH'			=> 'Allow use of <code>[FLASH]</code> BBCode tag in user signatures',
	'ALLOW_SIG_IMG'				=> 'Allow use of <code>[IMG]</code> BBCode tag in user signatures',
	'ALLOW_SIG_LINKS'			=> 'Allow use of links in user signatures',
	'ALLOW_SIG_LINKS_EXPLAIN'	=> 'If disallowed the <code>[URL]</code> bbcode tag and automatic/magic URLs are disabled.',
	'ALLOW_SIG_SMILIES'			=> 'Allow use of smilies in user signatures',
	'ALLOW_SMILIES'				=> 'Allow smilies',
	'ALLOW_TOPIC_NOTIFY'		=> 'Allow topic watching',
	'BOARD_PM'					=> 'Private messaging',
	'BOARD_PM_EXPLAIN'			=> 'Enable or disable private messaging for all users.',
));

// Avatar Settings
$lang = array_merge($lang, array(
	'ACP_AVATAR_SETTINGS_EXPLAIN'	=> 'Avatars are generally small, unique images a user can associate with themselves. Depending on the style they are usually displayed below the username when viewing topics. Here you can determine how users can define their avatars. Please note that in order to upload avatars you need to have created the directory you name below and ensure it can be written to by the web server. Please also note that filesize limits are only imposed on uploaded avatars, they do not apply to remotely linked images.',

	'ALLOW_LOCAL'					=> 'Enable gallery avatars',
	'ALLOW_REMOTE'					=> 'Enable remote avatars',
	'ALLOW_REMOTE_EXPLAIN'			=> 'Avatars linked to from another website',
	'ALLOW_UPLOAD'					=> 'Enable avatar uploading',
	'AVATAR_GALLERY_PATH'			=> 'Avatar gallery path',
	'AVATAR_GALLERY_PATH_EXPLAIN'	=> 'Path under your phpBB root dir for pre-loaded images, e.g. <samp>images/avatars/gallery</samp>',
	'AVATAR_STORAGE_PATH'			=> 'Avatar storage path',
	'AVATAR_STORAGE_PATH_EXPLAIN'	=> 'Path under your phpBB root dir, e.g. <samp>images/avatars/upload</samp>',
	'MAX_AVATAR_SIZE'				=> 'Maximum avatar dimensions',
	'MAX_AVATAR_SIZE_EXPLAIN'		=> '(Height x Width in pixels)',
	'MAX_FILESIZE'					=> 'Maximum avatar file size',
	'MAX_FILESIZE_EXPLAIN'			=> 'For uploaded avatar files',
	'MIN_AVATAR_SIZE'				=> 'Minimum avatar dimensions',
	'MIN_AVATAR_SIZE_EXPLAIN'		=> '(Height x Width in pixels)',
));

// Message Settings
$lang = array_merge($lang, array(
	'ACP_MESSAGE_SETTINGS_EXPLAIN'		=> 'Here you can set all default settings for private messaging',

	'ALLOW_BBCODE_PM'			=> 'Allow BBCode in private messages',
	'ALLOW_FLASH_PM'			=> 'Allow use of <code>[FLASH]</code> BBCode tag',
	'ALLOW_FORWARD_PM'			=> 'Allow forwarding of private messages',
	'ALLOW_IMG_PM'				=> 'Allow use of <code>[IMG]</code> BBCode tag',
	'ALLOW_MASS_PM'				=> 'Allow sending of private messages to multiple users and groups',
	'ALLOW_PRINT_PM'			=> 'Allow print view in private messaging',
	'ALLOW_QUOTE_PM'			=> 'Allow quotes in private messages',
	'ALLOW_SIG_PM'				=> 'Allow signature in private messages',
	'ALLOW_SMILIES_PM'			=> 'Allow smilies in private messages',
	'BOXES_LIMIT'				=> 'Max private messages per box',
	'BOXES_LIMIT_EXPLAIN'		=> 'Users may receive no more than this many messages in each of their private message boxes or zero for unlimited messages.',
	'BOXES_MAX'					=> 'Max private message folders',
	'BOXES_MAX_EXPLAIN'			=> 'By default users may create this many personal folders for private messages.',
	'ENABLE_PM_ICONS'			=> 'Enable use of topic icons in private messages',
	'FULL_FOLDER_ACTION'		=> 'Full folder default action',
	'FULL_FOLDER_ACTION_EXPLAIN'=> 'Default Action to take if a user’s folder is full and if the users folder action set is not applicable. For the “sent messages” folder the default action is always deleting old messages.',
	'HOLD_NEW_MESSAGES'			=> 'Hold new messages',
	'PM_EDIT_TIME'				=> 'Limit editing time',
	'PM_EDIT_TIME_EXPLAIN'		=> 'Limits the time available to edit a private message not already delivered, zero equals infinity',
));

// Post Settings
$lang = array_merge($lang, array(
	'ACP_POST_SETTINGS_EXPLAIN'			=> 'Here you can set all default settings for posting',
	'ALLOW_POST_LINKS'					=> 'Allow links in posts/private messages',
	'ALLOW_POST_LINKS_EXPLAIN'			=> 'If disallowed the URL bbcode tag and automatic/magic urls are disabled.',

	'BUMP_INTERVAL'					=> 'Bump Interval',
	'BUMP_INTERVAL_EXPLAIN'			=> 'Number of minutes, hours or days between the last post to a topic and the ability to bump this topic.',
	'CHAR_LIMIT'					=> 'Max characters per post',
	'CHAR_LIMIT_EXPLAIN'			=> 'Set to 0 for unlimited characters.',
	'DISPLAY_LAST_EDITED'			=> 'Display last edited time information',
	'DISPLAY_LAST_EDITED_EXPLAIN'	=> 'Choose if the last edited by information to be displayed on posts',
	'EDIT_TIME'						=> 'Limit editing time',
	'EDIT_TIME_EXPLAIN'				=> 'Limits the time available to edit a new post, zero equals infinity',
	'FLOOD_INTERVAL'				=> 'Flood Interval',
	'FLOOD_INTERVAL_EXPLAIN'		=> 'Number of seconds a user must wait between posting new messages. To enable users to ignore this alter their permissions.',
	'HOT_THRESHOLD'					=> 'Posts for Popular Threshold, Set to 0 to disable hot topics.',
	'MAX_POLL_OPTIONS'				=> 'Max number of poll options',
	'MAX_POST_FONT_SIZE'			=> 'Max font size per post',
	'MAX_POST_FONT_SIZE_EXPLAIN'	=> 'Set to 0 for unlimited font size.',
	'MAX_POST_IMG_HEIGHT'			=> 'Max image height per post',
	'MAX_POST_IMG_HEIGHT_EXPLAIN'	=> 'Maximum height of an image/flash file in postings. Set to 0 for unlimited size.',
	'MAX_POST_IMG_WIDTH'			=> 'Max image width per post',
	'MAX_POST_IMG_WIDTH_EXPLAIN'	=> 'Maximum width of an image/flash file in postings. Set to 0 for unlimited size.',
	'MAX_POST_URLS'					=> 'Max links per post',
	'MAX_POST_URLS_EXPLAIN'			=> 'Set to 0 for unlimited links.',
	'POSTING'						=> 'Posting',
	'POSTS_PER_PAGE'				=> 'Posts Per Page',
	'QUOTE_DEPTH_LIMIT'				=> 'Max nested quotes per post',
	'QUOTE_DEPTH_LIMIT_EXPLAIN'		=> 'Set to 0 for unlimited depth.',
	'SMILIES_LIMIT'					=> 'Max smilies per post',
	'SMILIES_LIMIT_EXPLAIN'			=> 'Set to 0 for unlimited smilies.',
	'TOPICS_PER_PAGE'				=> 'Topics Per Page',
));

// Signature Settings
$lang = array_merge($lang, array(
	'ACP_SIGNATURE_SETTINGS_EXPLAIN'	=> 'Here you can set all default settings for signatures',

	'MAX_SIG_FONT_SIZE'				=> 'Maximum signature font size',
	'MAX_SIG_FONT_SIZE_EXPLAIN'		=> 'Maximum font size allowed in user signatures. Set to 0 for unlimited size.',
	'MAX_SIG_IMG_HEIGHT'			=> 'Maximum signature image height',
	'MAX_SIG_IMG_HEIGHT_EXPLAIN'	=> 'Maximum height of an image/flash file in user signatures. Set to 0 for unlimited size.',
	'MAX_SIG_IMG_WIDTH'				=> 'Maximum signature image width',
	'MAX_SIG_IMG_WIDTH_EXPLAIN'		=> 'Maximum width of an image/flash file in user signatures. Set to 0 for unlimited size.',
	'MAX_SIG_LENGTH'				=> 'Maximum signature length',
	'MAX_SIG_LENGTH_EXPLAIN'		=> 'Maximum number of characters in user signatures.',
	'MAX_SIG_SMILIES'				=> 'Maximum smilies per signature',
	'MAX_SIG_SMILIES_EXPLAIN'		=> 'Maximum smilies allowed in user signatures. Set to 0 for unlimited size.',
	'MAX_SIG_URLS'					=> 'Maximum signature links',
	'MAX_SIG_URLS_EXPLAIN'			=> 'Maximum number of links in user signatures. Set to 0 for unlimited links.',
));

// Registration Settings
$lang = array_merge($lang, array(
	'ACP_REGISTER_SETTINGS_EXPLAIN'		=> 'Here you are able to define registration and profile related settings',

	'ACC_ACTIVATION'			=> 'Account activation',
	'ACC_ACTIVATION_EXPLAIN'	=> 'This determines whether users have immediate access to the board or if confirmation is required. You can also completely disable new registrations.',
	'ACC_ADMIN'					=> 'Admin',
	'ACC_DISABLE'				=> 'Disable',
	'ACC_NONE'					=> 'None',
	'ACC_USER'					=> 'User',
//	'ACC_USER_ADMIN'			=> 'User + Admin',
	'ALLOW_EMAIL_REUSE'			=> 'Allow email address re-use',
	'ALLOW_EMAIL_REUSE_EXPLAIN'	=> 'Different users can register with the same email address.',
	'COPPA'						=> 'Coppa',
	'COPPA_FAX'					=> 'COPPA fax number',
	'COPPA_MAIL'				=> 'COPPA mailing address',
	'COPPA_MAIL_EXPLAIN'		=> 'This is the mailing address where parents will send COPPA registration forms',
	'ENABLE_COPPA'				=> 'Enable COPPA',
	'ENABLE_COPPA_EXPLAIN'		=> 'This requires users to declare whether they are 13 or over for compliance with the U.S. COPPA Act. If this is disabled the COPPA specific groups will no longer be displayed.',
	'MAX_CHARS'					=> 'Max',
	'MIN_CHARS'					=> 'Min',
	'NO_AUTH_PLUGIN'			=> 'No suitable auth plugin found.',
	'PASSWORD_LENGTH'			=> 'Password length',
	'PASSWORD_LENGTH_EXPLAIN'	=> 'Minimum and maximum number of characters in passwords.',
	'REG_LIMIT'					=> 'Registration attempts',
	'REG_LIMIT_EXPLAIN'			=> 'Number of attempts users can make at the confirmation code before being locked out that session.',
	'USERNAME_ALPHA_ONLY'		=> 'Alphanumeric only',
	'USERNAME_ALPHA_SPACERS'	=> 'Alphanumeric and spacers',
	'USERNAME_CHARS'			=> 'Limit username chars',
	'USERNAME_CHARS_ANY'		=> 'Any character',
	'USERNAME_CHARS_EXPLAIN'	=> 'Restrict type of characters that may be used in usernames, spacers are; space, -, +, _, [ and ]',
	'USERNAME_LENGTH'			=> 'Username length',
	'USERNAME_LENGTH_EXPLAIN'	=> 'Minimum and maximum number of characters in usernames.',
));

// Visual Confirmation Settings
$lang = array_merge($lang, array(
	'ACP_VC_SETTINGS_EXPLAIN'		=> 'Here you are able to define visual confirmation defaults and captcha settings.',

	'CAPTCHA_GD'					=> 'GD CAPTCHA',
	'CAPTCHA_GD_NOISE'				=> 'GD CAPTCHA Noise',
	'CAPTCHA_GD_EXPLAIN'			=> 'Use GD to make a more advanced CAPTCHA',
	'CAPTCHA_GD_NOISE_EXPLAIN'		=> 'Use noise to make the GD based CAPTCHA harder',
	'VISUAL_CONFIRM_POST'			=> 'Enable visual confirmation for guest postings',
	'VISUAL_CONFIRM_POST_EXPLAIN'	=> 'Requires anonymous users to enter a random code matching an image to help prevent mass postings.',
	'VISUAL_CONFIRM_REG'			=> 'Enable visual confirmation for registrations',
	'VISUAL_CONFIRM_REG_EXPLAIN'	=> 'Requires new users to enter a random code matching an image to help prevent mass registrations.',
));

// Cookie Settings
$lang = array_merge($lang, array(
	'ACP_COOKIE_SETTINGS_EXPLAIN'		=> 'These details define the data used to send cookies to your users browsers. In most cases the default values for the cookie settings should be sufficient. If you do need to change any do so with care, incorrect settings can prevent users logging in.',

	'COOKIE_DOMAIN'				=> 'Cookie domain',
	'COOKIE_NAME'				=> 'Cookie name',
	'COOKIE_PATH'				=> 'Cookie path',
	'COOKIE_SECURE'				=> 'Cookie secure',
	'COOKIE_SECURE_EXPLAIN'		=> 'If your server is running via SSL set this to enabled else leave as disabled. Having this enabled and not running via SSL will result in server errors during redirects.',
	'ONLINE_LENGTH'				=> 'View online time span',
	'ONLINE_LENGTH_EXPLAIN'		=> 'Time in minutes after which inactive users will not appear in viewonline listings, lower equals less processing.',
	'SESSION_LENGTH'			=> 'Session length',
	'SESSION_LENGTH_EXPLAIN'	=> 'Sessions will expire after this time, in seconds.',
));

// Load Settings
$lang = array_merge($lang, array(
	'ACP_LOAD_SETTINGS_EXPLAIN'	=> 'Here you can enable and disable certain board functions to reduce the amount of processing required. On most servers there is no need to disable any functions. However on certain systems or in shared hosting environments it may be beneficial to disable capabilities you do not really need. You can also specify limits for system load and active sessions beyond which the board will go offline.',

	'CUSTOM_PROFILE_FIELDS'			=> 'Custom profile fields',
	'LIMIT_LOAD'					=> 'Limit system load',
	'LIMIT_LOAD_EXPLAIN'			=> 'If the 1 minute system load exceeds this value the board will go offline, 1.0 equals ~100% utilisation of one processor. This only functions on UNIX based servers.',
	'LIMIT_SESSIONS'				=> 'Limit sessions',
	'LIMIT_SESSIONS_EXPLAIN'		=> 'If the number of sessions exceeds this value within a one minute period the board will go offline. Set to 0 for unlimited sessions.',
	'LOAD_CPF_MEMBERLIST'			=> 'Display custom profile fields in memberlist',
	'LOAD_CPF_VIEWPROFILE'			=> 'Display custom profile fields in user profiles',
	'LOAD_CPF_VIEWTOPIC'			=> 'Display custom profile fields on viewtopic',
	'LOAD_USER_ACTIVITY'			=> 'Show users activity',
	'LOAD_USER_ACTIVITY_EXPLAIN'	=> 'Displays active topic/forum in user profiles and user control panel. It is recommended to disable this on boards with more than one million posts.',
	'RECOMPILE_TEMPLATES'			=> 'Recompile stale templates',
	'RECOMPILE_TEMPLATES_EXPLAIN'	=> 'Check for updated template files on filesystem and recompile.',
	'YES_ANON_READ_MARKING'			=> 'Enable topic marking for guests',
	'YES_ANON_READ_MARKING_EXPLAIN'	=> 'Stores read/unread status information for guests. If disabled posts are always read for guests.',
	'YES_BIRTHDAYS'					=> 'Enable birthday listing',
	'YES_JUMPBOX'					=> 'Enable display of Jumpbox',
	'YES_MODERATORS'				=> 'Enable display of Moderators',
	'YES_ONLINE'					=> 'Enable online user listings',
	'YES_ONLINE_EXPLAIN'			=> 'Display online user information on index, forum and topic pages.',
	'YES_ONLINE_GUESTS'				=> 'Enable online guest listings in viewonline',
	'YES_ONLINE_GUESTS_EXPLAIN'		=> 'Allow display of guest user information in viewonline.',
	'YES_ONLINE_TRACK'				=> 'Enable display of user online img',
	'YES_ONLINE_TRACK_EXPLAIN'		=> 'Display online information for user in profiles and viewtopic.',
	'YES_POST_MARKING'				=> 'Enable dotted topics',
	'YES_POST_MARKING_EXPLAIN'		=> 'Indicates whether user has posted to a topic.',
	'YES_READ_MARKING'				=> 'Enable server-side topic marking',
	'YES_READ_MARKING_EXPLAIN'		=> 'Stores read/unread status information in the database rather than a cookie.',
));

// Auth settings
$lang = array_merge($lang, array(
	'ACP_AUTH_SETTINGS_EXPLAIN'	=> 'phpBB supports authentication plug-ins, or modules. These allow you determine how users are authenticated when they log into the board. By default three plug-ins are provided; DB, LDAP and Apache. Not all methods require additional information so only fill out fields if they are relevant to the selected method.',

	'AUTH_METHOD'				=> 'Select an authentication method',

	'APACHE_SETUP_BEFORE_USE'	=> 'You have to setup apache authentication before you switch phpBB to this authentication method. Keep in mind that the username you use for apache authentication has to be the same as your phpBB username.',

	'LDAP_DN'					=> 'LDAP base <var>dn</var>',
	'LDAP_DN_EXPLAIN'			=> 'This is the Distinguished Name, locating the user information, e.g. <samp>o=My Company,c=US</samp>',
	'LDAP_EMAIL'				=> 'LDAP email attribute',
	'LDAP_EMAIL_EXPLAIN'		=> 'Set this to the name of your user entry email attribute (if one exists) in order to automatically set the email address for new users. Leaving this empty results in empty email address for users who log in for the first time.',
	'LDAP_NO_EMAIL'				=> 'The specified email attribute does not exist.',
	'LDAP_NO_IDENTITY'			=> 'Could not find a login identity for %s',
	'LDAP_SERVER'				=> 'LDAP server name',
	'LDAP_SERVER_EXPLAIN'		=> 'If using LDAP this is the name or IP address of the server.',
	'LDAP_UID'					=> 'LDAP <var>uid</var>',
	'LDAP_UID_EXPLAIN'			=> 'This is the key under which to search for a given login identity, e.g. <var>uid</var>, <var>sn</var>, etc.',
));

// Server Settings
$lang = array_merge($lang, array(
	'ACP_SERVER_SETTINGS_EXPLAIN'	=> 'Here you define server and domain dependant settings. Please ensure the data you enter is accurate, errors will result in emails containing incorrect information. When entering the domain name remember it does include http:// or other protocol term. Only alter the port number if you know your server uses a different value, port 80 is correct in most cases.',

	'ENABLE_GZIP'				=> 'Enable GZip Compression',
	'FORCE_SERVER_VARS'			=> 'Force server URL settings',
	'FORCE_SERVER_VARS_EXPLAIN'	=> 'If set to yes the server settings defined here will be used in favour of the automatically determined values',
	'ICONS_PATH'				=> 'Post icons storage path',
	'ICONS_PATH_EXPLAIN'		=> 'Path under your phpBB root dir, e.g. <samp>images/icons</samp>',
	'PATH_SETTINGS'				=> 'Path settings',
	'RANKS_PATH'				=> 'Rank image storage path',
	'RANKS_PATH_EXPLAIN'		=> 'Path under your phpBB root dir, e.g. <samp>images/ranks</samp>',
	'SEND_ENCODING'				=> 'Send encoding',
	'SEND_ENCODING_EXPLAIN'		=> 'Send the file encoding from phpBB via HTTP overriding the webserver configuration',
	'SERVER_NAME'				=> 'Domain name',
	'SERVER_NAME_EXPLAIN'		=> 'The domain name this board runs from (for example: <samp>www.foo.bar</samp>)',
	'SERVER_PORT'				=> 'Server Port',
	'SERVER_PORT_EXPLAIN'		=> 'The port your server is running on, usually 80, only change if different',
	'SERVER_PROTOCOL'			=> 'Server protocol',
	'SERVER_PROTOCOL_EXPLAIN'	=> 'This is used as the server protocol if these settings are forced. If empty or not forced the protocol is determined by the cookie secure settings (<samp>http://</samp> or <samp>https://</samp>)',
	'SERVER_URL_SETTINGS'		=> 'Server URL settings',
	'SMILIES_PATH'				=> 'Smilies storage path',
	'SMILIES_PATH_EXPLAIN'		=> 'Path under your phpBB root dir, e.g. <samp>images/smilies</samp>',
	'UPLOAD_ICONS_PATH'			=> 'Extension group icons storage path',
	'UPLOAD_ICONS_PATH_EXPLAIN'	=> 'Path under your phpBB root dir, e.g. <samp>images/upload_icons</samp>',
));

// Security Settings
$lang = array_merge($lang, array(
	'ACP_SECURITY_SETTINGS_EXPLAIN'		=> 'Here you are able to define session and login related settings',

	'ALL'							=> 'All',
	'ALLOW_AUTOLOGIN'				=> 'Allow persistent logins', 
	'ALLOW_AUTOLOGIN_EXPLAIN'		=> 'Determines whether users can autologin when they visit the board.', 
	'AUTOLOGIN_LENGTH'				=> 'Persistent login key expiration length (in days)', 
	'AUTOLOGIN_LENGTH_EXPLAIN'		=> 'Number of days after which persistent login keys are removed or zero to disable.', 
	'BROWSER_VALID'					=> 'Validate browser',
	'BROWSER_VALID_EXPLAIN'			=> 'Enables browser validation for each session improving security.',
	'CHECK_DNSBL'					=> 'Check IP against DNS Blackhole List',
	'CHECK_DNSBL_EXPLAIN'			=> 'If enabled the IP is checked against the following DNSBL services on registration and posting: <a href="http://spamcop.net">spamcop.net</a>, <a href="http://dsbl.org">dsbl.org</a> and <a href="http://spamhaus.org">spamhaus.org</a>. This lookup may take a bit, depending on the servers configuration. If slowdowns are experienced or too much false positives reported it is recommended to disable this check.',
	'CLASS_B'						=> 'A.B',
	'CLASS_C'						=> 'A.B.C',
	'EMAIL_CHECK_MX'				=> 'Check email domain for valid MX Record',
	'EMAIL_CHECK_MX_EXPLAIN'		=> 'If enabled, the email domain provided on registration and profile changes is checked for a valid MX record.',
	'FORCE_PASS_CHANGE'				=> 'Force password change',
	'FORCE_PASS_CHANGE_EXPLAIN'		=> 'Require user to change their password after a set number of days or zero to disable.',
	'IP_VALID'						=> 'Session IP validation',
	'IP_VALID_EXPLAIN'				=> 'Determines how much of the users IP is used to validate a session; <samp>All</samp> compares the complete address, <samp>A.B.C</samp> the first x.x.x, <samp>A.B</samp> the first x.x, <samp>None</samp> disables checking.',
	'MAX_LOGIN_ATTEMPTS'			=> 'Maximum number of login attempts',
	'MAX_LOGIN_ATTEMPTS_EXPLAIN'	=> 'After this number of failed logins the user needs to additionally confirm his login visually (visual confirmation)',
	'NO_IP_VALIDATION'				=> 'None',
	'PASSWORD_TYPE'					=> 'Password complexity',
	'PASSWORD_TYPE_EXPLAIN'			=> 'Determines how complex a password needs to be when set or altered, subsequent options include the previous ones.',
	'PASS_TYPE_ALPHA'				=> 'Must contain alphanumerics',
	'PASS_TYPE_ANY'					=> 'No requirements',
	'PASS_TYPE_CASE'				=> 'Must be mixed case',
	'PASS_TYPE_SYMBOL'				=> 'Must contain symbols',
	'TPL_ALLOW_PHP'					=> 'Allow php in templates',
	'TPL_ALLOW_PHP_EXPLAIN'			=> 'If this option is enabled, <code>PHP</code> and <code>INCLUDEPHP</code> statements will be recognized and parsed in templates.',
));

// Email Settings
$lang = array_merge($lang, array(
	'ACP_EMAIL_SETTINGS_EXPLAIN'	=> 'This information is used when the board sends emails to your users. Please ensure the email address you specify is valid, any bounced or undeliverable messages will likely be sent to that address. If your host does not provide a native (PHP based) email service you can instead send messages directly using SMTP. This requires the address of an appropriate server (ask your provider if necessary), do not specify any old name here! If the server requires authentication (and only if it does) enter the necessary username and password. Please note only basic authentication is offered, different authentication implementations are not currently supported.',

	'ADMIN_EMAIL'					=> 'Return email address',
	'ADMIN_EMAIL_EXPLAIN'			=> 'This will be used as the return address on all emails.',
	'BOARD_EMAIL_FORM'				=> 'Users send email via board',
	'BOARD_EMAIL_FORM_EXPLAIN'		=> 'Instead of showing the users email address users are able to send emails via the board.',
	'BOARD_HIDE_EMAILS'				=> 'Hide email addresses',
	'BOARD_HIDE_EMAILS_EXPLAIN'		=> 'This function keeps email addresses completely private.',
	'CONTACT_EMAIL'					=> 'Contact email address',
	'CONTACT_EMAIL_EXPLAIN'			=> 'This address will be used whenever a specific contact point is needed, e.g. spam, error output, etc.',
	'EMAIL_FUNCTION_NAME'			=> 'Email function name',
	'EMAIL_FUNCTION_NAME_EXPLAIN'	=> 'The email function used to send mails through PHP.',
	'EMAIL_PACKAGE_SIZE'			=> 'Email package size',
	'EMAIL_PACKAGE_SIZE_EXPLAIN'	=> 'This is the number of emails sent in one package.',
	'EMAIL_SIG'						=> 'Email signature',
	'EMAIL_SIG_EXPLAIN'				=> 'This text will be attached to all emails the board sends.',
	'ENABLE_EMAIL'					=> 'Enable board-wide emails',
	'ENABLE_EMAIL_EXPLAIN'			=> 'If this is set to disabled no emails will be sent by the board at all.',
	'SMTP_AUTH_METHOD'				=> 'Authentication method for SMTP',
	'SMTP_AUTH_METHOD_EXPLAIN'		=> 'Only used if a username/password is set, ask your provider if you are unsure which method to use.',
	'SMTP_CRAM_MD5'					=> 'CRAM-MD5',
	'SMTP_DIGEST_MD5'				=> 'DIGEST-MD5',
	'SMTP_LOGIN'					=> 'LOGIN',
	'SMTP_PASSWORD'					=> 'SMTP Password',
	'SMTP_PASSWORD_EXPLAIN'			=> 'Only enter a password if your SMTP server requires it.',
	'SMTP_PLAIN'					=> 'PLAIN',
	'SMTP_POP_BEFORE_SMTP'			=> 'POP-BEFORE-SMTP',
	'SMTP_PORT'						=> 'SMTP Server Port',
	'SMTP_PORT_EXPLAIN'				=> 'Only change this if you know your SMTP server is on a different port.',
	'SMTP_SERVER'					=> 'SMTP Server Address',
	'SMTP_SETTINGS'					=> 'SMTP Settings',
	'SMTP_USERNAME'					=> 'SMTP Username',
	'SMTP_USERNAME_EXPLAIN'			=> 'Only enter a username if your SMTP  server requires it.',
	'USE_SMTP'						=> 'Use SMTP Server for email',
	'USE_SMTP_EXPLAIN'				=> 'Select “Yes” if you want or have to send email via a named server instead of the local mail function.',
));

// Jabber settings
$lang = array_merge($lang, array(
	'ACP_JABBER_SETTINGS_EXPLAIN'	=> 'Here you can enable and control the use Jabber for instant messaging and board notices. Jabber is an opensource protocol and therefore available for use by anyone. Some Jabber servers include gateways or transports which allow you to contact users on other networks. Not all servers offer all transports and changes in protocols can prevent transports from operating. Note that it may take several seconds to update Jabber account details, do not stop the script till completed!',

	'ERR_JAB_AUTH'			=> 'Could not authorise on Jabber server.',
	'ERR_JAB_CONNECT'		=> 'Could not connect to Jabber server.',
	'ERR_JAB_PASSCHG'		=> 'Could not change password.',
	'ERR_JAB_PASSFAIL'		=> 'Password update failed, %s.',
	'ERR_JAB_REGISTER'		=> 'An error occured trying to register this account, %s.',
	'ERR_JAB_USERNAME'		=> 'The username specified already exists, please choose an alternative.',

	'JAB_CHANGED'				=> 'Jabber account changed successfully.',
	'JAB_ENABLE'				=> 'Enable Jabber',
	'JAB_ENABLE_EXPLAIN'		=> 'Enables use of jabber messaging and notifications',
	'JAB_PACKAGE_SIZE'			=> 'Jabber package size',
	'JAB_PACKAGE_SIZE_EXPLAIN'	=> 'This is the number of messages sent in one package. If set to 0 the message is sent immediatly and gets not queued for later sending.',
	'JAB_PASSWORD'				=> 'Jabber password',
	'JAB_PASS_CHANGED'			=> 'Jabber password changed successfully.',
	'JAB_PORT'					=> 'Jabber port',
	'JAB_PORT_EXPLAIN'			=> 'Leave blank unless you know it is not port 5222',
	'JAB_REGISTERED'			=> 'New account registered successfully.',
	'JAB_RESOURCE'				=> 'Jabber resource',
	'JAB_RESOURCE_EXPLAIN'		=> 'The resource locates this particular connection, e.g. board, home, etc.',
	'JAB_SERVER'				=> 'Jabber server',
	'JAB_SERVER_EXPLAIN'		=> 'See %sjabber.org%s for a list of servers',
	'JAB_SETTINGS_CHANGED'		=> 'Jabber settings changed successfully.',
	'JAB_USERNAME'				=> 'Jabber username',
	'JAB_USERNAME_EXPLAIN'		=> 'If this user is not registered it will be created if possible.',
));

?>