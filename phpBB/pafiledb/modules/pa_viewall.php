<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_viewall.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
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
class pafiledb_viewall extends pafiledb_public
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function main( $action  = false )
	{
		global $template, $user, $phpEx, $pafiledb_config, $userdata, $tplEx;

		$start = ( isset( $_REQUEST['start'] ) ) ? intval( $_REQUEST['start'] ) : 0;

		if ( isset( $_REQUEST['sort_method'] ) )
		{
			switch ( $_REQUEST['sort_method'] )
			{
				case 'file_name':
					$sort_method = 'file_name';
					break;
				case 'file_time':
					$sort_method = 'file_time';
					break;
				case 'file_dls':
					$sort_method = 'file_dls';
					break;
				case 'file_rating':
					$sort_method = 'rating';
					break;
				case 'file_update_time':
					$sort_method = 'file_update_time';
					break;
				default:
					$sort_method = $pafiledb_config['sort_method'];
			}
		}
		else
		{
			$sort_method = $pafiledb_config['sort_method'];
		}

		if ( isset( $_REQUEST['sort_order'] ) )
		{
			switch ( $_REQUEST['sort_order'] )
			{
				case 'ASC':
					$sort_order = 'ASC';
					break;
				case 'DESC':
					$sort_order = 'DESC';
					break;
				default:
					$sort_order = $pafiledb_config['sort_order'];
			}
		}
		else
		{
			$sort_order = $pafiledb_config['sort_order'];
		}

		if ( !$pafiledb_config['settings_viewall'] )
		{
			mx_message_die( GENERAL_MESSAGE, $user->lang['viewall_disabled'] );
		}
		elseif ( !$this->auth_global['auth_viewall'] )
		{
			if ( !$userdata['session_logged_in'] )
			{
				mx_redirect( mx_append_sid( "login.$phpEx?redirect=dload.$phpEx?action=viewall", true ) );
			}

			$message = sprintf( $user->lang['Sorry_auth_viewall'], $this->auth_global['auth_viewall_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		$template->assign_vars( array(
			'L_VIEWALL' => $user->lang['Viewall'],
			'L_INDEX' => "<<",

			'U_INDEX' => mx_append_sid( $mx_root_path . 'index.' . $phpEx ),
			'U_DOWNLOAD' => mx_append_sid( $this->this_mxurl() ),

			'DOWNLOAD' => $pafiledb_config['module_name']
		));

		$this->display_items( $sort_method, $sort_order, $start, false,  true );

		// ===================================================
		// assign var for navigation
		// ===================================================

		$this->display( $user->lang['Download'], 'pa_viewall_body.'.$tplEx );
	}
}
?>