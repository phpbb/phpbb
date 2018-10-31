<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pafiledb_common.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

if ( !defined( 'IN_PORTAL' ) )
{
	die( "Hacking attempt" );
}


// ===================================================
// Include Files
// ===================================================
include_once($phpbb_root_path . 'pafiledb/includes/pafiledb_constants.' . $phpEx);

//
// Load addon tools
//
// - Class module_cache
// - Class mx_custom_fields (pafiledb needs its own class version in functions.php)
// - Class mx_notification
// - Class mx_text
// - Class mx_text_formatting
//
include_once($phpbb_root_path . 'pafiledb/includes/functions_tools.'.$phpEx);

// **********************************************************************
// If phpBB mod read language definition
// **********************************************************************

if (!MXBB_MODULE)
{
	$user->add_lang('mods/pafiledb_main');
}

//Load image set

include_once($phpbb_root_path . 'pafiledb/includes/functions.' . $phpEx );
include_once($phpbb_root_path . 'pafiledb/includes/functions_auth.' . $phpEx );
include_once($phpbb_root_path . 'pafiledb/includes/functions_phpbb2.' . $phpEx); //Temp fix
include_once($phpbb_root_path . 'pafiledb/includes/functions_pafiledb.' . $phpEx );


// ===================================================
// Load classes
// ===================================================

$pafiledb_cache = new module_cache($module_root_path . 'pafiledb/');
$pafiledb_functions = new pafiledb_functions();

if ( $pafiledb_cache->exists( 'config' ) )
{
	$pafiledb_config = $pafiledb_cache->get( 'config' );
}
else
{
	$pafiledb_config = $pafiledb_functions->pafiledb_config();
	$pafiledb_cache->put( 'config', $pafiledb_config );
}

$pafiledb_user = new mx_user_info();

if (defined('IN_ADMIN'))
{
	include_once($phpbb_root_path . 'pafiledb/includes/functions_admin.'.$phpEx);
	$pafiledb = new pafiledb_admin($u_action);
}
else
{
	$pafiledb = new pafiledb_public();
}

?>