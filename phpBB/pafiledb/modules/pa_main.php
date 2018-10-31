<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_main.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

if ( !defined( 'IN_PORTAL' ) )
{
	die( "Hacking attempt" );
}

/**
 * Enter description here...
 *
 */
class pafiledb_main extends pafiledb_public
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function main( $action  = false )
	{
		global $template, $user, $board_config, $phpEx, $pafiledb_config, $debug, $phpbb_root_path, $tplEx;

		//
		// Assign vars
		//
		$template->assign_vars( array(
			'U_DOWNLOAD' => mx_append_sid( $this->this_mxurl() ),
			'DOWNLOAD' => $pafiledb_config['module_name'],
			'TREE' => $menu_output
		));

		// ===================================================
		// Show the Category for the download database index
		// ===================================================
		if ($pafiledb_config['use_simple_navigation'])
		{
			$this->display_categories();
		}
		else
		{
			$this->display_categories_original();
		}

		$this->display( $user->lang['Download'], 'pa_main_body.'.$tplEx );
	}
}
?>