<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pafiledb_constants.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
* @copyright (c) 2002-2006 [Mohd Basri, PHP Arena, pafileDB, Jon Ohlsson] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

if ( !defined( 'IN_PORTAL' ) )
{
	die( "Hacking attempt" );
}

if ( !MXBB_MODULE )
{
	$server_protocol = ($board_config['cookie_secure']) ? 'https://' : 'http://';
	$server_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($board_config['server_name']));
	$server_port = ($board_config['server_port'] <> 80) ? ':' . trim($board_config['server_port']) : '';
	$script_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($board_config['script_path']));
	$script_name = ($script_name == '') ? $script_name : '/' . $script_name;

	define( 'PORTAL_URL', $server_protocol . $server_name . $server_port . $script_name . '/' );
	define( 'PHPBB_URL', PORTAL_URL );
	
	$mx_table_prefix = $table_prefix;

	$is_block = false; // This also makes the script work for phpBB ;)
}

define( 'PAGE_DOWNLOAD', -501 ); // If this id generates a conflict with other mods, change it ;)
define( 'ICONS_DIR', 'pafiledb/images/icons/' );

//
// Tables
//
define( 'PA_CATEGORY_TABLE', $mx_table_prefix . 'pa_cat' );
define( 'PA_COMMENTS_TABLE', $mx_table_prefix . 'pa_comments' );
define( 'PA_CUSTOM_TABLE', $mx_table_prefix . 'pa_custom' );
define( 'PA_CUSTOM_DATA_TABLE', $mx_table_prefix . 'pa_customdata' );
define( 'PA_DOWNLOAD_INFO_TABLE', $mx_table_prefix . 'pa_download_info' );
define( 'PA_FILES_TABLE', $mx_table_prefix . 'pa_files' );
define( 'PA_LICENSE_TABLE', $mx_table_prefix . 'pa_license' );
define( 'PA_CONFIG_TABLE', $mx_table_prefix . 'pa_config' );
define( 'PA_VOTES_TABLE', $mx_table_prefix . 'pa_votes' );
define( 'PA_AUTH_ACCESS_TABLE', $mx_table_prefix . 'pa_auth' );
define( 'PA_MIRRORS_TABLE', $mx_table_prefix . 'pa_mirrors' );
define( 'PA_SEARCH_TABLE', $mx_table_prefix . 'pa_search_results' );


// User Levels <- this values are for compatiblility with mxBB 2.8.x and phpBB2
// Revove them when MX-Publisher is fixed
!defined('DELETED') ? define('DELETED', -1) : false;
!defined('USER') ? define('USER', 0) : false;
!defined('ADMIN') ? define('ADMIN', 1) : false;
!defined('MOD') ? define('MOD', 2) : false;
// User Levels <- this values are for compatiblility with MX-Publisher 2.8.x and phpBB2

// URL PARAMETERS
!defined('POST_TOPIC_URL') ? define('POST_TOPIC_URL', 't') : false;
!defined('POST_CAT_URL') ? define('POST_CAT_URL', 'c') : false;
!defined('POST_FORUM_URL') ? define('POST_FORUM_URL', 'f') : false;
!defined('POST_USERS_URL') ? define('POST_USERS_URL', 'u') : false;
!defined('POST_POST_URL') ? define('POST_POST_URL', 'p') : false;
!defined('POST_GROUPS_URL') ? define('POST_GROUPS_URL', 'g') : false;

// Page numbers for session handling
!defined('PAGE_INDEX') ? define('PAGE_INDEX', 0) : false;
!defined('PAGE_LOGIN') ? define('PAGE_LOGIN', -1) : false;
!defined('PAGE_SEARCH') ? define('PAGE_SEARCH', -2) : false;
!defined('PAGE_REGISTER') ? define('PAGE_REGISTER', -3) : false;
!defined('PAGE_PROFILE') ? define('PAGE_PROFILE', -4) : false;
!defined('PAGE_VIEWONLINE') ? define('PAGE_VIEWONLINE', -6) : false;
!defined('PAGE_VIEWMEMBERS') ? define('PAGE_VIEWMEMBERS', -7) : false;
!defined('PAGE_FAQ') ? define('PAGE_FAQ', -8) : false;
!defined('PAGE_POSTING') ? define('PAGE_POSTING', -9) : false;
!defined('PAGE_PRIVMSGS') ? define('PAGE_PRIVMSGS', -10) : false;
!defined('PAGE_GROUPCP') ? define('PAGE_GROUPCP', -11) : false;
!defined('PAGE_TOPIC_OFFSET') ? define('PAGE_TOPIC_OFFSET', 5000) : false;

// phpBB2 Auth settings
@define('AUTH_LIST_ALL', 0);
@define('AUTH_ALL', 0);

@define('AUTH_REG', 1);
@define('AUTH_ACL', 2);
@define('AUTH_MOD', 3);
@define('AUTH_ADMIN', 5);

@define('AUTH_VIEW', 1);
@define('AUTH_READ', 2);
@define('AUTH_POST', 3);
@define('AUTH_REPLY', 4);
@define('AUTH_EDIT', 5);
@define('AUTH_DELETE', 6);
@define('AUTH_ANNOUNCE', 7);
@define('AUTH_STICKY', 8);
@define('AUTH_POLLCREATE', 9);
@define('AUTH_VOTE', 10);
@define('AUTH_ATTACH', 11);

// Error codes removed in phpBB3 does we need them here
@define('GENERAL_MESSAGE', 200);
@define('GENERAL_ERROR', 202);
@define('CRITICAL_MESSAGE', 203);
@define('CRITICAL_ERROR', 204);

//
// Switches
//
define( 'PAFILEDB_DEBUG', 1 ); // Pafiledb Mod Debugging on
define( 'PAFILEDB_QUERY_DEBUG', 1 );
define( 'PA_ROOT_CAT', 0 );
define( 'PA_CAT_ALLOW_FILE', 1 );
define( 'PA_AUTH_LIST_ALL', 0 );
define( 'PA_AUTH_ALL', 0 );
define( 'FILE_PINNED', 1 );
define( 'PA_AUTH_VIEW', 1 );
define( 'PA_AUTH_READ', 2 );
define( 'PA_AUTH_VIEW_FILE', 3 );
define( 'PA_AUTH_UPLOAD', 4 );
define( 'PA_AUTH_DOWNLOAD', 5 );
define( 'PA_AUTH_RATE', 6 );
define( 'PA_AUTH_EMAIL', 7 );
define( 'PA_AUTH_COMMENT_VIEW', 8 );
define( 'PA_AUTH_COMMENT_POST', 9 );
define( 'PA_AUTH_COMMENT_EDIT', 10 );
define( 'PA_AUTH_COMMENT_DELETE', 11 );

//
// Field Types
//
define( 'INPUT', 0 );
define( 'TEXTAREA', 1 );
define( 'RADIO', 2 );
define( 'SELECT', 3 );
define( 'SELECT_MULTIPLE', 4 );
define( 'CHECKBOX', 5 );

if ( !MXBB_MODULE || MXBB_27x )
{
	$pa_module_version = "pafileDB Download Manager v. 2.2.6";
	$pa_module_author = "Jon Ohlsson";
	$pa_module_orig_author = "Mohd";
}
else
{
	$mx_user->set_module_default_style('_core'); // For compatibility with core 2.8.x
	if (is_object($mx_page))
	{
		// -------------------------------------------------------------------------
		// Extend User Style with module lang and images
		// Usage:  $mx_user->extend(LANG, IMAGES)
		// Switches:
		// - LANG: MX_LANG_MAIN (default), MX_LANG_ADMIN, MX_LANG_ALL, MX_LANG_NONE
		// - IMAGES: MX_IMAGES (default), MX_IMAGES_NONE
		// -------------------------------------------------------------------------
		$mx_user->extend();

		$mx_page->add_copyright( 'MXP pafileDB Module' );
	}
}
?>