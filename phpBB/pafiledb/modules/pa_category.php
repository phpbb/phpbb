<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_category.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
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
class pafiledb_category extends pafiledb_public
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function main( $action  = false )
	{
		global $template, $user, $phpEx, $pafiledb_config, $tplEx;
		global $mx_root_path, $module_root_path, $is_block, $mx_request_vars;

		// =======================================================
		// Request vars
		// =======================================================
		$start = $mx_request_vars->request('start', MX_TYPE_INT, 0);
		$cat_id = $mx_request_vars->request('cat_id', MX_TYPE_INT, '');

		if ( empty( $cat_id ) )
		{
			mx_message_die( GENERAL_MESSAGE, $user->lang['Cat_not_exist'] );
		}

		//
		// Sorting of items
		//
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

		// =======================================================
		// If user not allowed to view file listing (read) and there is no sub Category
		// or the user is not allowed to view these category we gave him a nice message.
		// =======================================================
		$show_category = false;
		if ( isset( $this->subcat_rowset[$cat_id] ) )
		{
			foreach( $this->subcat_rowset[$cat_id] as $sub_cat_id => $sub_cat_row )
			{
				if ( $this->auth_user[$sub_cat_id]['auth_view'] )
				{
					$show_category = true;
					break;
				}
			}
		}

		if ( ( !$this->auth_user[$cat_id]['auth_read'] ) && ( !$show_category ) )
		{
			if ( !$user->data['is_registered'] )
			{
				// mx_redirect(mx_append_sid($mx_root_path . "login.$phpEx?redirect=". $this->this_mxurl("action=category&cat_id=" . $cat_id, true), true));
			}

			$message = sprintf( $user->lang['Sorry_auth_view'], $this->auth_user[$cat_id]['auth_read_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		if ( !isset( $this->cat_rowset[$cat_id] ) )
		{
			mx_message_die( GENERAL_MESSAGE, $user->lang['Cat_not_exist'] );
		}

		//
		// Validate Comments Setup
		//
		if ( $this->comments[$cat_id]['activated'] && !$this->comments[$cat_id]['internal_comments'] && $this->comments[$cat_id]['comments_forum_id'] < 1 )
		{
			//
			// Commenting is enabled but no category forum id specified
			//
			$message = $user->lang['No_cat_comments_forum_id'];
			mx_message_die(GENERAL_MESSAGE, $message);
		}

		$template->assign_vars( array(
			'U_DOWNLOAD' => mx_append_sid( $this->this_mxurl() ),
			'DOWNLOAD' => $pafiledb_config['module_name'] )
		);

		$no_file_message = true;
		$filelist = false;

		// ===================================================
		// assign var for navigation
		// ===================================================
		$this->generate_navigation( $cat_id );

		if ( isset( $this->subcat_rowset[$cat_id] ) )
		{
			$no_file_message = false;

			if ($pafiledb_config['use_simple_navigation'])
			{
				$this->display_categories($cat_id);
			}
			else
			{
				$this->display_categories_original($cat_id);
			}
		}

		$this->display_items( $sort_method, $sort_order, $start, $cat_id, $no_file_message );

		//
		// User authorisation levels output
		//
		$this->auth_can($cat_id);

		$this->display( $user->lang['Download'], 'pa_category_body.'.$tplEx );
	}
}
?>