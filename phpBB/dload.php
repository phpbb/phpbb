<?php
/**
*
* @package phpBB3 Mod - pafileDB
* @version $Id: dload.php,v 1.2 2008/10/17 17:48:03 orynider Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

$phpEx = substr(strrchr(__FILE__, '.'), 1);

if ( !defined('PORTAL_BACKEND') && @file_exists( './viewtopic.' . $phpEx ) ) // -------------------------------------------- phpBB MOD MODE
{
	define('MXBB_MODULE', false );
	define('IN_PHPBB', true );
	define('IN_PORTAL', true );
	define('IN_DOWNLOAD', true );

	// When run as a phpBB mod these paths are identical ;)
	$phpbb_root_path = $module_root_path = $mx_root_path = './';

	include( $phpbb_root_path . 'common.' . $phpEx );

	@ini_set( 'display_errors', '1' );

	include_once($phpbb_root_path . 'pafiledb/includes/functions_mxp.' . $phpEx);
	
	define( 'PAGE_DOWNLOAD', -501 ); // If this id generates a conflict with other mods, change it ;)

	define('PORTAL_BACKEND', 'phpbb3');
	$tplEx = 'html';
	
	//
	// instatiate the mx_request_vars class
	//
	$mx_request_vars = new mx_request_vars();

	$is_block = false;	

	// Start session management
	$user->session_begin();
	$auth->acl($user->data);
	$user->setup();
	$userdata = $user->data;
	//
	// End session management
	//

	//
	// Get phpBB config settings
	//
	$board_config = $config;
}
else
{
	define( 'MXBB_MODULE', true );

	if ( !function_exists( 'read_block_config' ) )
	{
		if( isset($_REQUEST['action']) && $_REQUEST['action'] == 'download' )
		{
		   define('MX_GZIP_DISABLED', true);
		}

		define( 'IN_PORTAL', true );
		$mx_root_path = '../../';
		$phpEx = substr(strrchr(__FILE__, '.'), 1);
		include_once( $mx_root_path . 'common.' . $phpEx );

		// Start session management
		$mx_user->init($user_ip, PAGE_INDEX);
		// End session management

		$block_id = ( !empty( $_GET['block_id'] ) ) ? $_GET['block_id'] : $_POST['id'];
		if ( empty( $block_id ) )
		{
			$sql = "SELECT * FROM " . BLOCK_TABLE . "  WHERE block_title = 'PafileDB' LIMIT 1";
			if ( !$result = $db->sql_query( $sql ) )
			{
				mx_message_die( GENERAL_ERROR, "Could not query PafileDB module information", "", __LINE__, __FILE__, $sql );
			}
			$row = $db->sql_fetchrow( $result );
			$block_id = $row['block_id'];
		}
		$is_block = false;
	}
	else
	{
		if( !defined('IN_PORTAL') || !is_object($mx_block))
		{
			die("Hacking attempt");
		}
		//
		// Read Block Settings (default mode)
		//
		$title = $mx_block->block_info['block_title'];
		$block_size = ( isset( $block_size ) && !empty( $block_size ) ? $block_size : '100%' );

		//Check for cash mod
		if (file_exists($phpbb_root_path . 'includes/functions_cash.'.$phpEx))
		{
			define('IN_CASHMOD', true);
		}

		$is_block = true;
		global $images;
	}
	define( 'MXBB_27x', @file_exists( $mx_root_path . 'mx_login.'.$phpEx ) );
	define( 'MXBB_28x', @file_exists( $mx_root_path . 'includes/sessions/index.htm' ) );
}

// -------------------------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------
// Start
// -------------------------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------

// ===================================================
// ?
// ===================================================
list( $trash, $mx_script_name_temp ) = split ( trim( $board_config['server_name'] ), PORTAL_URL );
$mx_script_name = preg_replace( '#^\/?(.*?)\/?$#', '\1', trim( $mx_script_name_temp ) );

// ===================================================
// Include the common file
// ===================================================
include_once( $module_root_path . 'pafiledb/pafiledb_common.' . $phpEx );

// ===================================================
// Get action variable otherwise set it to the main
// ===================================================
$action = $mx_request_vars->request('action', MX_TYPE_NO_TAGS, 'main');

// ===================================================
// Is admin?
// ===================================================
$is_admin = $auth->acl_get('a_') ? true : 0;


// ===================================================
// if the module is disabled give them a nice message
// ===================================================
if (!($pafiledb_config['enable_module'] || $is_admin))
{
	trigger_error($user->lang['pafiledb_disable']);
}

// ===================================================
// an array of all expected actions
// ===================================================
$actions = array(
	'download' => 'download',
	'category' => 'category',
	'file' => 'file',
	'viewall' => 'viewall',
	'search' => 'search',
	'license' => 'license',
	'rate' => 'rate',
	'email' => 'email',
	'stats' => 'stats',
	'toplist' => 'toplist',
	'user_upload' => 'user_upload',
	'post_comment' => 'post_comment',
	'mcp' => 'mcp',
	'ucp' => 'ucp',
	'main' => 'main' );

// ===================================================
// Lets Build the page
// ===================================================
$page_title = $user->lang['Download'];

if ( $action != 'download' )
{
	if ( !$is_block )
	{
		//page_header($page_title);
	}
}

$pafiledb->module( $actions[$action] );
$pafiledb->modules[$actions[$action]]->main( $action );

if ( $action != 'download' )
{
	if (!$is_block)
	{
		//page_footer();
	}
}
?>