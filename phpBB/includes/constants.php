<?php
/***************************************************************************
 *                               includes.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
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
 *
 ***************************************************************************/

//
// Constants
//

// Debug Level
define(DEBUG, 1); // Debugging on
//define(DEBUG, 0); // Debugging off

// User Levels <- Do not change the values of USER or ADMIN
define(DELETED, -1);
define(ANONYMOUS, -1);
define(USER, 0);
define(ADMIN, 1);

// Forum state
define(FORUM_UNLOCKED, 0);
define(FORUM_LOCKED, 1);

// Topic state
define(TOPIC_UNLOCKED, 0);
define(TOPIC_LOCKED, 1);

// Topic types
define(POST_NORMAL, 0);
define(POST_STICKY, 1);
define(POST_ANNOUNCE, 2);
define(POST_GLOBAL_ANNOUNCE, 3);
define(TOPIC_MOVED,4);

// SQL codes
define(BEGIN_TRANSACTION, 1);
define(END_TRANSACTION, 2);

// Error codes
define(GENERAL_MESSAGE, 200);
define(GENERAL_ERROR, 202);
define(CRITICAL_MESSAGE, 203);
define(CRITICAL_ERROR, 204);

define(SQL_CONNECT, 1);
define(BANNED, 2);
define(QUERY_ERROR, 3);
define(SESSION_CREATE, 4);
define(NO_TOPICS, 5);
define(LOGIN_FAILED, 7);

// Private messaging
define(PRIVMSGS_READ_MAIL, 0);
define(PRIVMSGS_NEW_MAIL, 1);
define(PRIVMSGS_SENT_MAIL, 2);
define(PRIVMSGS_SAVED_MAIL, 3);

// URL PARAMETERS
define(POST_TOPIC_URL, 't');
define(POST_FORUM_URL, 'f');
define(POST_USERS_URL, 'u');
define(POST_POST_URL, 'p');
define(POST_GROUPS_URL, 'g');

// Session parameters
define(SESSION_METHOD_COOKIE, 100);
define(SESSION_METHOD_GET, 101);

// Page numbers for session handling
define(PAGE_INDEX, 0);
define(PAGE_LOGIN, -1);
define(PAGE_SEARCH, -2);
define(PAGE_REGISTER, -3);
define(PAGE_PROFILE, -4);
define(PAGE_VIEWONLINE, -6);
define(PAGE_VIEWMEMBERS, -7);
define(PAGE_FAQ, -8);
define(PAGE_POSTING, -9);
define(PAGE_PRIVMSGS, -10);
define(PAGE_GROUPCP, -11);

// Auth settings
define(AUTH_ALL, 0);

define(AUTH_REG, 1);
define(AUTH_ACL, 2);
define(AUTH_MOD, 3);
define(AUTH_ADMIN, 5);

define(AUTH_VIEW, 1);

define(AUTH_READ, 2);
define(AUTH_POST, 3);
define(AUTH_REPLY, 4);
define(AUTH_EDIT, 5);
define(AUTH_DELETE, 6);

define(AUTH_ANNOUNCE, 7);
define(AUTH_STICKY, 8);
define(AUTH_VOTECREATE, 9);
define(AUTH_VOTE, 10);
define(AUTH_ATTACH, 11);

define(AUTH_LIST_ALL, 20);

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
define('RANKS_TABLE', $table_prefix.'ranks');
define('SESSIONS_TABLE', $table_prefix.'session');
define('SMILIES_TABLE', $table_prefix.'smilies');
define('THEMES_TABLE', $table_prefix.'themes');
define('TOPICS_TABLE', $table_prefix.'topics');
define('USER_GROUP_TABLE', $table_prefix.'user_group');
define('USERS_TABLE', $table_prefix.'users');
define('WORDS_TABLE', $table_prefix.'words');
define('PRUNE_TABLE', $table_prefix.'forum_prune');

?>
