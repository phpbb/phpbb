<?php
/***************************************************************************
 *                                common.php
 *                            -------------------
 *   begin                : Saturday, Feb 23, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
}

error_reporting(E_ERROR | E_WARNING | E_PARSE); // This will NOT report uninitialized variables
//error_reporting(E_ALL);
set_magic_quotes_runtime(0);

// If magic quotes is off, addslashes
if ( !get_magic_quotes_gpc() )
{
	$_GET = slash_input_data($_GET);
	$_POST = slash_input_data($_POST);
	$_COOKIE = slash_input_data($_COOKIE);
}

require($phpbb_root_path . 'config.'.$phpEx);
//require($phpbb_root_path . 'config_cache.'.$phpEx);

if ( !defined('PHPBB_INSTALLED') )
{
	header('Location: install/install.'.$phpEx);
	exit;
}

// Include files
require($phpbb_root_path . 'includes/acm/cache_' . $acm_type . '.'.$phpEx);
require($phpbb_root_path . 'includes/template.'.$phpEx);
require($phpbb_root_path . 'includes/session.'.$phpEx);
require($phpbb_root_path . 'includes/functions.'.$phpEx);
require($phpbb_root_path . 'db/' . $dbms . '.'.$phpEx);

// User related
define('ANONYMOUS', 0);

define('USER_ACTIVATION_NONE', 0);
define('USER_ACTIVATION_SELF', 1);
define('USER_ACTIVATION_ADMIN', 2);
define('USER_ACTIVATION_DISABLE', 3);

define('USER_AVATAR_NONE', 0);
define('USER_AVATAR_UPLOAD', 1);
define('USER_AVATAR_REMOTE', 2);
define('USER_AVATAR_GALLERY', 3);

// ACL
define('ACL_DENY', 0);
define('ACL_ALLOW', 1);
define('ACL_INHERIT', 2);

// Group settings
define('GROUP_OPEN', 0);
define('GROUP_CLOSED', 1);
define('GROUP_HIDDEN', 2);
define('GROUP_SPECIAL', 3);

// Forum/Topic states
define('ITEM_UNLOCKED', 0);
define('ITEM_LOCKED', 1);
define('ITEM_MOVED', 2);

// Topic types
define('POST_NORMAL', 0);
define('POST_STICKY', 1);
define('POST_ANNOUNCE', 2);

// Lastread types
define('LASTREAD_NORMAL', 0); // not used at the moment
define('LASTREAD_POSTED', 1);

// Error codes
define('MESSAGE', 200);
define('ERROR', 201);

// Private messaging
define('PRIVMSGS_READ_MAIL', 0);
define('PRIVMSGS_NEW_MAIL', 1);
define('PRIVMSGS_UNREAD_MAIL', 5);

// Table names
define('ACL_GROUPS_TABLE', $table_prefix.'auth_groups');
define('ACL_OPTIONS_TABLE', $table_prefix.'auth_options');
define('ACL_PRESETS_TABLE', $table_prefix.'auth_presets');
define('ACL_USERS_TABLE', $table_prefix.'auth_users');
define('BANLIST_TABLE', $table_prefix.'banlist');
define('CATEGORIES_TABLE', $table_prefix.'categories'); //
define('CONFIG_TABLE', $table_prefix.'config');
define('CONFIG_USER_TABLE', $table_prefix.'config_defaults');
define('DISALLOW_TABLE', $table_prefix.'disallow'); //
define('FORUMS_TABLE', $table_prefix.'forums');
define('FORUMS_WATCH_TABLE', $table_prefix.'forums_watch');
define('GROUPS_TABLE', $table_prefix.'groups');
define('ICONS_TABLE', $table_prefix.'icons');
define('LASTREAD_TABLE', $table_prefix.'lastread');
define('LOG_ADMIN_TABLE', $table_prefix.'log_admin');
define('LOG_MOD_TABLE', $table_prefix.'log_moderator');
define('MODERATOR_TABLE', $table_prefix.'moderator_cache');
define('POSTS_TABLE', $table_prefix.'posts');
define('POSTS_TEXT_TABLE', $table_prefix.'posts_text');
define('PRIVMSGS_TABLE', $table_prefix.'privmsgs');
define('PRIVMSGS_TEXT_TABLE', $table_prefix.'privmsgs_text');
define('RANKS_TABLE', $table_prefix.'ranks');
define('SEARCH_TABLE', $table_prefix.'search_results');
define('SEARCH_WORD_TABLE', $table_prefix.'search_wordlist');
define('SEARCH_MATCH_TABLE', $table_prefix.'search_wordmatch');
define('SESSIONS_TABLE', $table_prefix.'sessions');
define('SMILIES_TABLE', $table_prefix.'smilies');
define('STYLES_TABLE', $table_prefix.'styles');
define('STYLES_TPL_TABLE', $table_prefix.'styles_template');
define('STYLES_CSS_TABLE', $table_prefix.'styles_theme');
define('STYLES_IMAGE_TABLE', $table_prefix.'styles_imageset');
define('TOPICS_TABLE', $table_prefix.'topics');
define('TOPICS_PREFETCH_TABLE', $table_prefix.'topics_prefetch');
define('TOPICS_RATINGS_TABLE', $table_prefix.'topics_rating');
define('TOPICS_WATCH_TABLE', $table_prefix.'topics_watch');
define('USER_GROUP_TABLE', $table_prefix.'user_group');
define('USERS_TABLE', $table_prefix.'users');
define('WORDS_TABLE', $table_prefix.'words');
define('POLL_OPTIONS_TABLE', $table_prefix.'poll_results');
define('POLL_VOTES_TABLE', $table_prefix.'poll_voters');

define('VOTE_DESC_TABLE', $table_prefix.'vote_desc');
define('VOTE_RESULTS_TABLE', $table_prefix.'vote_results');
define('VOTE_USERS_TABLE', $table_prefix.'vote_voters');

// Set PHP error handler to ours
set_error_handler('msg_handler');

// Experimental cache manager
$cache = new acm();

// Need these here so instantiate them now
$template = new Template();
$db = new sql_db($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false);

/*
// Obtain boardwide default config (rebuilding cache if reqd)
if ( empty($config) )
{
	require_once($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
	$config = config_config();
}

$sql = "SELECT *
	FROM " . CONFIG_TABLE . "
	WHERE is_dynamic = 1";
$result = $db->sql_query($sql, false);

while ( $row = $db->sql_fetchrow($result) )
{
	$config[$row['config_name']] = $row['config_value'];
}

// Re-cache acl options if reqd
if ( empty($acl_options) )
{
	require_once($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
	$auth_admin = new auth_admin();
	$acl_options = $auth_admin->acl_cache_options();
}
*/

if (!$config = $cache->get('config'))
{
	$config = array();

	$sql = 'SELECT * FROM ' . CONFIG_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$config[$row['config_name']] = $row['config_value'];
	}

	$cache->put('config', $config);
}

if ($cache->exists('acl_options'))
{
	$acl_options = $cache->get('acl_options');
}
else
{
	require_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
	$auth_admin = new auth_admin();
	$acl_options = $auth_admin->acl_cache_options();
}

/*
if (time() - $config['cache_interval'] >= $config['cache_last_gc'])
{
	$cache->tidy($config['cache_gc']);
}
*/

// Instantiate some basic classes
$user = new user();
$auth = new auth();

// Show 'Board is disabled' message
if ( $config['board_disable'] && !defined('IN_ADMIN') && !defined('IN_LOGIN') )
{
	$message = ( !empty($config['board_disable_msg']) ) ? $config['board_disable_msg'] : 'Board_disable';
	trigger_error($message);
}

// addslashes to vars if magic_quotes_gpc is off
function slash_input_data(&$data)
{
	if ( is_array($data) )
	{
		foreach ( $data as $k => $v )
		{
			$data[$k] = ( is_array($v) ) ? slash_input_data($v) : addslashes($v);
		}
	}
	return $data;
}

?>
