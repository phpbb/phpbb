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
	die("Hacking attempt");
}

error_reporting  (E_ERROR | E_WARNING | E_PARSE); // This will NOT report uninitialized variables
set_magic_quotes_runtime(0); // Disable magic_quotes_runtime

//
// addslashes to vars if magic_quotes_gpc is off this is a security precaution
// to prevent someone trying to break out of a SQL statement.
//
function slash_input_data(&$data)
{
	if ( is_array($data) )
	{
		while( list($k, $v) = each($data) )
		{
			$data[$k] = ( is_array($v) ) ? slash_input_data($v) : addslashes($v);
		}

		@reset($data);
	}
	return $data;
}

if ( !get_magic_quotes_gpc() )
{
	$HTTP_GET_VARS = slash_input_data($HTTP_GET_VARS);
	$HTTP_POST_VARS = slash_input_data($HTTP_POST_VARS);
	$HTTP_COOKIE_VARS = slash_input_data($HTTP_COOKIE_VARS);
}

//
// Define some basic configuration arrays this also prevents
// malicious rewriting of language and otherarray values via
// URI params
//
$board_config = array();
$userdata = array();
$theme = array();
$images = array();
$lang = array();
$gen_simple_header = FALSE;

require($phpbb_root_path . 'config.'.$phpEx);

if( !defined("PHPBB_INSTALLED") )
{
	header("Location: install.$phpEx");
}

// Debug Level
define('DEBUG', 1); // Debugging on
//define('DEBUG', 0); // Debugging off


// User Levels <- Do not change the values of USER or ADMIN
define('ANONYMOUS', -1);
define('USER', 0);
define('ADMIN', 1);
define('MOD', 2);


// User related
define('USER_ACTIVATION_NONE', 0);
define('USER_ACTIVATION_SELF', 1);
define('USER_ACTIVATION_ADMIN', 2);

define('USER_AVATAR_NONE', 0);
define('USER_AVATAR_UPLOAD', 1);
define('USER_AVATAR_REMOTE', 2);
define('USER_AVATAR_GALLERY', 3);


// Group settings
define('GROUP_OPEN', 0);
define('GROUP_CLOSED', 1);
define('GROUP_HIDDEN', 2);


// Forum state
define('FORUM_UNLOCKED', 0);
define('FORUM_LOCKED', 1);


// Topic status
define('TOPIC_UNLOCKED', 0);
define('TOPIC_LOCKED', 1);
define('TOPIC_MOVED', 2);
define('TOPIC_WATCH_NOTIFIED', 1);
define('TOPIC_WATCH_UN_NOTIFIED', 0);


// Topic types
define('POST_NORMAL', 0);
define('POST_STICKY', 1);
define('POST_ANNOUNCE', 2);
define('POST_GLOBAL_ANNOUNCE', 3);


// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);


// Error codes
define('GENERAL_MESSAGE', 200);
define('GENERAL_ERROR', 202);
define('CRITICAL_MESSAGE', 203);
define('CRITICAL_ERROR', 204);


// Private messaging
define('PRIVMSGS_READ_MAIL', 0);
define('PRIVMSGS_NEW_MAIL', 1);
define('PRIVMSGS_SENT_MAIL', 2);
define('PRIVMSGS_SAVED_IN_MAIL', 3);
define('PRIVMSGS_SAVED_OUT_MAIL', 4);
define('PRIVMSGS_UNREAD_MAIL', 5);


// URL PARAMETERS
define('POST_TOPIC_URL', 't');
define('POST_CAT_URL', 'c');
define('POST_FORUM_URL', 'f');
define('POST_USERS_URL', 'u');
define('POST_POST_URL', 'p');
define('POST_GROUPS_URL', 'g');

// Session parameters
define('SESSION_METHOD_COOKIE', 100);
define('SESSION_METHOD_GET', 101);


// Page numbers for session handling
define('PAGE_INDEX', 0);
define('PAGE_LOGIN', -1);
define('PAGE_SEARCH', -2);
define('PAGE_REGISTER', -3);
define('PAGE_PROFILE', -4);
define('PAGE_VIEWONLINE', -6);
define('PAGE_VIEWMEMBERS', -7);
define('PAGE_FAQ', -8);
define('PAGE_POSTING', -9);
define('PAGE_PRIVMSGS', -10);
define('PAGE_GROUPCP', -11);
define('PAGE_TOPIC_OFFSET', 5000);


// Auth settings
define('AUTH_LIST_ALL', 0);
define('AUTH_ALL', 0);

define('AUTH_REG', 1);
define('AUTH_ACL', 2);
define('AUTH_MOD', 3);
define('AUTH_ADMIN', 5);

define('AUTH_VIEW', 1);
define('AUTH_READ', 2);
define('AUTH_POST', 3);
define('AUTH_REPLY', 4);
define('AUTH_EDIT', 5);
define('AUTH_DELETE', 6);
define('AUTH_ANNOUNCE', 7);
define('AUTH_STICKY', 8);
define('AUTH_POLLCREATE', 9);
define('AUTH_VOTE', 10);
define('AUTH_ATTACH', 11);


// Table names
define('AUTH_ACCESS_TABLE', $table_prefix.'auth_access');
define('BANLIST_TABLE', $table_prefix.'banlist');
define('CATEGORIES_TABLE', $table_prefix.'categories');
define('CONFIG_TABLE', $table_prefix.'config');
define('DISALLOW_TABLE', $table_prefix.'disallow');
define('FORUMS_TABLE', $table_prefix.'forums');
define('GROUPS_TABLE', $table_prefix.'groups');
define('POSTS_TABLE', $table_prefix.'posts');
define('POSTS_TEXT_TABLE', $table_prefix.'posts_text');
define('PRIVMSGS_TABLE', $table_prefix.'privmsgs');
define('PRIVMSGS_TEXT_TABLE', $table_prefix.'privmsgs_text');
define('PRIVMSGS_IGNORE_TABLE', $table_prefix.'privmsgs_ignore');
define('PRUNE_TABLE', $table_prefix.'forum_prune');
define('RANKS_TABLE', $table_prefix.'ranks');
define('SEARCH_TABLE', $table_prefix.'search_results');
define('SEARCH_WORD_TABLE', $table_prefix.'search_wordlist');
define('SEARCH_MATCH_TABLE', $table_prefix.'search_wordmatch');
define('SESSIONS_TABLE', $table_prefix.'sessions');
define('SMILIES_TABLE', $table_prefix.'smilies');
define('THEMES_TABLE', $table_prefix.'themes');
define('THEMES_NAME_TABLE', $table_prefix.'themes_name');
define('TOPICS_TABLE', $table_prefix.'topics');
define('TOPICS_WATCH_TABLE', $table_prefix.'topics_watch');
define('USER_GROUP_TABLE', $table_prefix.'user_group');
define('USERS_TABLE', $table_prefix.'users');
define('WORDS_TABLE', $table_prefix.'words');
define('VOTE_DESC_TABLE', $table_prefix.'vote_desc');
define('VOTE_RESULTS_TABLE', $table_prefix.'vote_results');
define('VOTE_USERS_TABLE', $table_prefix.'vote_voters');

include($phpbb_root_path . 'includes/template.'.$phpEx);
include($phpbb_root_path . 'includes/sessions.'.$phpEx);
include($phpbb_root_path . 'includes/auth.'.$phpEx);
include($phpbb_root_path . 'includes/functions.'.$phpEx);

switch($dbms)
{
	case 'mysql':
		include($phpbb_root_path . 'db/mysql.'.$phpEx);
		break;

	case 'mysql4':
		include($phpbb_root_path . 'db/mysql4.'.$phpEx);
		break;

	case 'postgres':
		include($phpbb_root_path . 'db/postgres7.'.$phpEx);
		break;

	case 'mssql':
		include($phpbb_root_path . 'db/mssql.'.$phpEx);
		break;

	case 'oracle':
		include($phpbb_root_path . 'db/oracle.'.$phpEx);
		break;

	case 'msaccess':
		include($phpbb_root_path . 'db/msaccess.'.$phpEx);
		break;

	case 'mssql-odbc':
		include($phpbb_root_path . 'db/mssql-odbc.'.$phpEx);
		break;
}

// Make the database connection.
$db = new sql_db($dbhost, $dbuser, $dbpasswd, $dbname, false);
if ( !$db->db_connect_id )
{
   message_die(CRITICAL_ERROR, "Could not connect to the database");
}

//
// Mozilla navigation bar
// Default items that should be valid on all pages.
// Defined here and not in page_header.php so they can be redefined in the code
//
$nav_links['top'] = array ( 
	'url' => append_sid($phpbb_root_dir."index.".$phpEx),
	'title' => sprintf($lang['Forum_Index'], $board_config['sitename'])
);
$nav_links['search'] = array ( 
	'url' => append_sid($phpbb_root_dir."search.".$phpEx),
	'title' => $lang['Search']
);
$nav_links['help'] = array ( 
	'url' => append_sid($phpbb_root_dir."faq.".$phpEx),
	'title' => $lang['FAQ']
);
$nav_links['author'] = array ( 
	'url' => append_sid($phpbb_root_dir."memberlist.".$phpEx),
	'title' => $lang['Memberlist']
);

//
// Obtain and encode users IP
//
if( getenv('HTTP_X_FORWARDED_FOR') != '' )
{
	$client_ip = ( !empty($HTTP_SERVER_VARS['REMOTE_ADDR']) ) ? $HTTP_SERVER_VARS['REMOTE_ADDR'] : ( ( !empty($HTTP_ENV_VARS['REMOTE_ADDR']) ) ? $HTTP_ENV_VARS['REMOTE_ADDR'] : $REMOTE_ADDR );

	if ( preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", getenv('HTTP_X_FORWARDED_FOR'), $ip_list) )
	{
		$private_ip = array('/^127\.0\.0\.1/', '/^192\.168\..*/', '/^172\.16\..*/', '/^10..*/', '/^224..*/', '/^240..*/');
		$client_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);
	}
}
else
{
	$client_ip = ( !empty($HTTP_SERVER_VARS['REMOTE_ADDR']) ) ? $HTTP_SERVER_VARS['REMOTE_ADDR'] : ( ( !empty($HTTP_ENV_VARS['REMOTE_ADDR']) ) ? $HTTP_ENV_VARS['REMOTE_ADDR'] : $REMOTE_ADDR );
}
$user_ip = encode_ip($client_ip);

//
// Setup forum wide options, if this fails
// then we output a CRITICAL_ERROR since
// basic forum information is not available
//
$sql = "SELECT *
	FROM " . CONFIG_TABLE;
if ( !($result = $db->sql_query($sql)) )
{
	message_die(CRITICAL_ERROR, 'Could not query config information', '', __LINE__, __FILE__, $sql);
}

while($row = $db->sql_fetchrow($result))
{
	$board_config[$row['config_name']] = $row['config_value'];
}

//
// Show 'Board is disabled' message if needed.
//
if ( $board_config['board_disable'] && !defined('IN_ADMIN') && !defined('IN_LOGIN') )
{
	message_die(GENERAL_MESSAGE, 'Board_disable', 'Information');
}

?>