<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/

// Remove the following line to enable this software, be sure you note what it
// says before continuing
die('This software is unsupported in any and all respects. By removing this notice (found in common.php) you are noting your acceptance of this. Do not ask support questions of any kind for this release at either area51.phpbb.com or www.phpbb.com. Support for this version will appear when the beta cycle begins');

if (!defined('IN_PHPBB'))
{
	exit;
}

$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];

error_reporting(E_ERROR | E_WARNING | E_PARSE); // This will NOT report uninitialized variables
//error_reporting(E_ALL);
set_magic_quotes_runtime(0);

// Be paranoid with passed vars
if (@ini_get('register_globals'))
{
	foreach ($_REQUEST as $var_name => $void)
	{
		unset(${$var_name});
	}
}

require($phpbb_root_path . 'config.'.$phpEx);

if (!defined('PHPBB_INSTALLED'))
{
	header('Location: install/install.'.$phpEx);
	exit;
}

if (defined('DEBUG_EXTRA'))
{
	$base_memory_usage = 0;
	if (function_exists('memory_get_usage'))
	{
		$base_memory_usage = memory_get_usage();
	}
}

// Load Extensions
if (!empty($load_extensions))
{
	$load_extensions = explode(',', $load_extensions);

	foreach ($load_extensions as $extension)
	{
		@dl(trim($extension));
	}
}

// Include files
require($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.'.$phpEx);
require($phpbb_root_path . 'includes/db/' . $dbms . '.'.$phpEx);
require($phpbb_root_path . 'includes/template.'.$phpEx);
require($phpbb_root_path . 'includes/session.'.$phpEx);
require($phpbb_root_path . 'includes/functions.'.$phpEx);

// User related
define('ANONYMOUS', 1);

define('USER_ACTIVATION_NONE', 0);
define('USER_ACTIVATION_SELF', 1);
define('USER_ACTIVATION_ADMIN', 2);
define('USER_ACTIVATION_DISABLE', 3);

define('AVATAR_UPLOAD', 1);
define('AVATAR_REMOTE', 2);
define('AVATAR_GALLERY', 3);

define('USER_NORMAL', 0);
define('USER_INACTIVE', 1);
define('USER_IGNORE', 2);
define('USER_FOUNDER', 3);

// ACL
define('ACL_NO', 0);
define('ACL_YES', 1);
define('ACL_UNSET', -1);

// Group settings
define('GROUP_OPEN', 0);
define('GROUP_CLOSED', 1);
define('GROUP_HIDDEN', 2);
define('GROUP_SPECIAL', 3);
define('GROUP_FREE', 4);

// Forum/Topic states
define('FORUM_CAT', 0);
define('FORUM_POST', 1);
define('FORUM_LINK', 2);
define('ITEM_UNLOCKED', 0);
define('ITEM_LOCKED', 1);
define('ITEM_MOVED', 2);

// Topic types
define('POST_NORMAL', 0);
define('POST_STICKY', 1);
define('POST_ANNOUNCE', 2);
define('POST_GLOBAL', 3);

// Lastread types
define('TRACK_NORMAL', 0);
define('TRACK_POSTED', 1);

// Notify methods
define('NOTIFY_EMAIL', 0);
define('NOTIFY_IM', 1);
define('NOTIFY_BOTH', 2);

// Email Priority Settings
define('MAIL_LOW_PRIORITY', 4);
define('MAIL_NORMAL_PRIORITY', 3);
define('MAIL_HIGH_PRIORITY', 2);

// Log types
define('LOG_ADMIN', 0);
define('LOG_MOD', 1);
define('LOG_CRITICAL', 2);
define('LOG_USERS', 3);

// Private messaging - Do NOT change these values
define('PRIVMSGS_HOLD_BOX', -4);
define('PRIVMSGS_NO_BOX', -3);
define('PRIVMSGS_OUTBOX', -2);
define('PRIVMSGS_SENTBOX', -1);
define('PRIVMSGS_INBOX', 0);

// Full Folder Actions
define('FULL_FOLDER_NONE', -3);
define('FULL_FOLDER_DELETE', -2);
define('FULL_FOLDER_HOLD', -1);

// Download Modes - Attachments
define('INLINE_LINK', 1);
define('PHYSICAL_LINK', 2);

// Categories - Attachments
define('ATTACHMENT_CATEGORY_NONE', 0);
define('ATTACHMENT_CATEGORY_IMAGE', 1); // Inline Images
define('ATTACHMENT_CATEGORY_WM', 2); // Windows Media Files - Streaming
define('ATTACHMENT_CATEGORY_RM', 3); // Real Media Files - Streaming
define('ATTACHMENT_CATEGORY_THUMB', 4); // Not used within the database, only while displaying posts
//define('SWF_CAT', 5); // Replaced by [flash]? or an additional possibility?

// BBCode UID length
define('BBCODE_UID_LEN', 5);

// Number of core BBCodes
define('NUM_CORE_BBCODES', 12);

// Profile Field Types
define('FIELD_INT', 1);
define('FIELD_STRING', 2);
define('FIELD_TEXT', 3);
define('FIELD_BOOL', 4);
define('FIELD_DROPDOWN', 5);
define('FIELD_DATE', 6);

// Table names
define('ACL_GROUPS_TABLE', $table_prefix.'auth_groups');
define('ACL_OPTIONS_TABLE', $table_prefix.'auth_options');
define('ACL_DEPS_TABLE', $table_prefix.'auth_deps');
define('ACL_PRESETS_TABLE', $table_prefix.'auth_presets');
define('ACL_USERS_TABLE', $table_prefix.'auth_users');
define('ATTACHMENTS_TABLE', $table_prefix.'attachments');
define('BANLIST_TABLE', $table_prefix.'banlist');
define('BBCODES_TABLE', $table_prefix.'bbcodes');
define('BOOKMARKS_TABLE', $table_prefix.'bookmarks');
define('BOTS_TABLE', $table_prefix.'bots');
define('CACHE_TABLE', $table_prefix.'cache');
define('CONFIG_TABLE', $table_prefix.'config');
define('CONFIRM_TABLE', $table_prefix.'confirm');
define('PROFILE_FIELDS_TABLE', $table_prefix.'profile_fields');
define('PROFILE_LANG_TABLE', $table_prefix.'profile_lang');
define('PROFILE_DATA_TABLE', $table_prefix.'profile_fields_data');
define('PROFILE_FIELDS_LANG_TABLE', $table_prefix.'profile_fields_lang');
define('DISALLOW_TABLE', $table_prefix.'disallow'); //
define('DRAFTS_TABLE', $table_prefix.'drafts');
define('EXTENSIONS_TABLE', $table_prefix.'extensions');
define('EXTENSION_GROUPS_TABLE', $table_prefix.'extension_groups');
define('FORUMS_TABLE', $table_prefix.'forums');
define('FORUMS_ACCESS_TABLE', $table_prefix.'forum_access');
define('FORUMS_TRACK_TABLE', $table_prefix.'forums_marking');
define('FORUMS_WATCH_TABLE', $table_prefix.'forums_watch');
define('GROUPS_TABLE', $table_prefix.'groups');
define('GROUPS_MODERATOR_TABLE', $table_prefix.'groups_moderator');
define('ICONS_TABLE', $table_prefix.'icons');
define('LANG_TABLE', $table_prefix.'lang');
define('LOG_TABLE', $table_prefix.'log');
define('MODERATOR_TABLE', $table_prefix.'moderator_cache');
define('MODULES_TABLE', $table_prefix . 'modules');
define('POSTS_TABLE', $table_prefix.'posts');
define('PRIVMSGS_TABLE', $table_prefix.'privmsgs');
define('PRIVMSGS_TO_TABLE', $table_prefix.'privmsgs_to');
define('PRIVMSGS_FOLDER_TABLE', $table_prefix.'privmsgs_folder');
define('PRIVMSGS_RULES_TABLE', $table_prefix.'privmsgs_rules');
define('RANKS_TABLE', $table_prefix.'ranks');
define('RATINGS_TABLE', $table_prefix.'ratings');
define('REPORTS_TABLE', $table_prefix.'reports');
define('REASONS_TABLE', $table_prefix.'reports_reasons');
define('SEARCH_TABLE', $table_prefix.'search_results');
define('SEARCH_WORD_TABLE', $table_prefix.'search_wordlist');
define('SEARCH_MATCH_TABLE', $table_prefix.'search_wordmatch');
define('SESSIONS_TABLE', $table_prefix.'sessions');
define('SITELIST_TABLE', $table_prefix.'sitelist');
define('SMILIES_TABLE', $table_prefix.'smilies');
define('STYLES_TABLE', $table_prefix.'styles');
define('STYLES_TPL_TABLE', $table_prefix.'styles_template');
define('STYLES_TPLDATA_TABLE', $table_prefix.'styles_template_data');
define('STYLES_CSS_TABLE', $table_prefix.'styles_theme');
define('STYLES_IMAGE_TABLE', $table_prefix.'styles_imageset');
define('TOPICS_TABLE', $table_prefix.'topics');
define('TOPICS_TRACK_TABLE', $table_prefix.'topics_marking');
define('TOPICS_WATCH_TABLE', $table_prefix.'topics_watch');
define('USER_GROUP_TABLE', $table_prefix.'user_group');
define('USERS_TABLE', $table_prefix.'users');
define('USERS_NOTES_TABLE', $table_prefix.'users_notes');
define('WORDS_TABLE', $table_prefix.'words');
define('POLL_OPTIONS_TABLE', $table_prefix.'poll_results');
define('POLL_VOTES_TABLE', $table_prefix.'poll_voters');
define('ZEBRA_TABLE', $table_prefix.'zebra');

define('STRIP', (get_magic_quotes_gpc()) ? true : false);

// Set PHP error handler to ours
set_error_handler('msg_handler');

// Instantiate some basic classes
$user		= new user();
$auth		= new auth();
$template	= new template();
$cache		= new acm();
$db			= new sql_db();

// Connect to DB
$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false);

// We do not need this any longer, unset for safety purposes
unset($dbpasswd);

// Grab global variables, re-cache if necessary
if ($config = $cache->get('config'))
{
	$sql = 'SELECT config_name, config_value
		FROM ' . CONFIG_TABLE . '
		WHERE is_dynamic = 1';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$config[$row['config_name']] = $row['config_value'];
	}
	$db->sql_freeresult($result);
}
else
{
	$config = $cached_config = array();

	$sql = 'SELECT config_name, config_value, is_dynamic
		FROM ' . CONFIG_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if (!$row['is_dynamic'])
		{
			$cached_config[$row['config_name']] = $row['config_value'];
		}

		$config[$row['config_name']] = $row['config_value'];
	}
	$db->sql_freeresult($result);

	$cache->put('config', $cached_config);
	unset($cached_config);
}

// Tidy the cache
if (method_exists($cache, 'tidy') && time() - $config['cache_gc'] > $config['cache_last_gc'])
{
	$cache->tidy();
	set_config('cache_last_gc', time(), TRUE);
}

// Handle email/cron queue.
if (time() - $config['queue_interval'] >= $config['last_queue_run'] && !defined('IN_ADMIN'))
{
	if (file_exists($phpbb_root_path . 'cache/queue.' . $phpEx))
	{
		include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);
		$queue = new queue();
		$queue->process();
	}
}

// Warn about install/ directory
if (file_exists('install'))
{
//	trigger_error('REMOVE_INSTALL');
}

?>