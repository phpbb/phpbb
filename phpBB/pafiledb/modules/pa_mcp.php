<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_mcp.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
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
class pafiledb_mcp extends pafiledb_public
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function main( $action  = false )
	{
		global $db, $user, $config, $phpEx, $debug;
		global $mx_root_path, $phpbb_root_path, $module_root_path, $is_block;
		global $pafiledb_functions, $template, $pafiledb_config, $tplEx;

		$cat_id = ( isset( $_REQUEST['cat_id'] ) ) ? intval( $_REQUEST['cat_id'] ) : 0;
		$id = ( isset( $_REQUEST['id'] ) ) ? intval( $_REQUEST['id'] ) : 0;
		$ids = ( isset( $_POST['ids'] ) ) ? array_map( 'intval', $_POST['ids'] ) : array();
		$start = ( isset( $_REQUEST['start'] ) ) ? intval( $_REQUEST['start'] ) : 0;

		$mode = $mode_notification = ( isset( $_REQUEST['mode_mcp'] ) ) ? htmlspecialchars( $_REQUEST['mode_mcp'] ) : 'all';

		$do_mode = ( isset( $_REQUEST['do_mode'] ) ) ? htmlspecialchars( $_REQUEST['do_mode'] ) : '';
		$do_mode = ( isset( $_POST['do_approve'] ) ) ? 'do_approve' : $do_mode;
		$do_mode = ( isset( $_POST['do_unapprove'] ) ) ? 'do_unapprove' : $do_mode;
		$do_mode = ( isset( $_POST['do_delete'] ) ) ? 'do_delete' : $do_mode;

		//echo('mode: '.$mode.'do_mode: '.$do_mode);
		//
		// PafileDB specific
		//
		$mirrors = ( isset( $_POST['mirrors'] ) ) ? true : 0;

		// ===================================================
		// Auth for mcp
		// ===================================================
		if ( !($this->auth_user[$cat_id]['auth_mod']) && $mode == 'cat')
		{
			$message = sprintf( $user->lang['Sorry_auth_mcp'], $this->auth_user[$cat_id]['auth_mod'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		//
		// Determine all categories in which user is moderator
		//
		$moderator_cat_ids = '';
		if ( isset( $this->cat_rowset ) )
		{
			foreach( $this->cat_rowset as $auth_cat_id => $cat_row )
			{
				if ($this->auth_user[$auth_cat_id]['auth_mod'])
				{
					$moderator_cat_ids .= !empty($moderator_cat_ids) ? ',' . $auth_cat_id : $auth_cat_id;
				}
			}
		}

		if (empty($moderator_cat_ids))
		{
			mx_message_die( GENERAL_MESSAGE, 'Sorry, you have no moderator permissions...' );
		}

		if ( isset( $_REQUEST['sort_method'] ) )
		{
			switch ( $_REQUEST['sort_method'] )
			{
				case 'Alphabetic':
					$sort_method = 'file_name';
					break;
				case 'Latest':
					$sort_method = 'file_time';
					break;
				case 'Downloads':
					$sort_method = 'file_dls';
					break;
				case 'Rating':
					$sort_method = 'rating';
					break;
				case 'Updated':
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

		$s_actions = array(
			'unapproved' => $user->lang['Unapproved_items'],
			'broken' => $user->lang['Broken_items'],
			'cat' => $user->lang['Item_cat'],
			'all' => $user->lang['All_items'] );

		switch ( $mode )
		{
			case '':
			case 'unapproved':
			case 'broken':
			case 'cat':
			case 'all':

			default:
				$template_item = 'pa_mcp.'.$tplEx;
				$l_title = $user->lang['MCP_title'];
				$l_explain = $user->lang['MCP_title_explain'];
				break;
		}

		//
		// Approve/Unapprove
		//
		if ( $do_mode == 'do_approve' || $do_mode == 'do_unapprove' )
		{
			if ( is_array( $ids ) && !empty( $ids ) )
			{
				foreach( $ids as $temp_id )
				{
					$this->approve_item( $do_mode, $temp_id );
				}
				//
				// Notification
				//
				$this->update_add_item_notify($ids, $do_mode);
			}
			else
			{
				$this->approve_item( $do_mode, $id );
				//
				// Notification
				//
				$this->update_add_item_notify($id, $do_mode);
			}
			$this->_pafiledb();
		}
		//
		// Delete
		//
		else if ( $do_mode == 'do_delete' )
		{
			if ( is_array( $ids ) && !empty( $ids ) )
			{
				foreach( $ids as $temp_id )
				{
					$sql = 'SELECT *
						FROM ' . PA_FILES_TABLE . "
						WHERE file_id = $temp_id";

					if ( !( $result = $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Couldn\'t get item info', '', __LINE__, __FILE__, $sql );
					}
					$item_info = $db->sql_fetchrow( $result );

					//
					// Notification
					//
					$this->update_add_item_notify($temp_id, 'delete');

					//
					// Comments
					//
					if ($this->comments[$item_info['file_catid']]['activated'] && $pafiledb_config['del_topic'])
					{
						if ( $this->comments[$item_info['file_catid']]['internal_comments'] )
						{
							$sql = 'DELETE FROM ' . PA_COMMENTS_TABLE . "
							WHERE file_id = '" . $temp_id . "'";

							if ( !( $db->sql_query( $sql ) ) )
							{
								mx_message_die( GENERAL_ERROR, 'Couldnt delete comments', '', __LINE__, __FILE__, $sql );
							}
						}
						else
						{
							if ( $item_info['topic_id'] )
							{
								include( $module_root_path . 'pafiledb/includes/functions_comment.' . $phpEx );
								$mx_pa_comments = new pafiledb_comments();
								$mx_pa_comments->init( $item_info, 'phpbb');
								$mx_pa_comments->post('delete_all', $item_info['topic_id']);
							}
						}
					}

					$this->delete_items( $temp_id );
				}
			}
			else
			{
				$sql = 'SELECT *
					FROM ' . PA_FILES_TABLE . "
					WHERE file_id = $id";
				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					mx_message_die( GENERAL_ERROR, 'Couldn\'t get file info', '', __LINE__, __FILE__, $sql );
				}
				$item_info = $db->sql_fetchrow( $result );

				//
				// Notification
				//
				$this->update_add_item_notify($id, 'delete');

				//
				// Comments
				//
				if ($this->comments[$item_info['file_catid']]['activated'] && $pafiledb_config['del_topic'])
				{
					if ( $this->comments[$item_info['file_catid']]['internal_comments'] )
					{
						$sql = 'DELETE FROM ' . PA_COMMENTS_TABLE . "
						WHERE file_id = '" . $id . "'";

						if ( !( $db->sql_query( $sql ) ) )
						{
							mx_message_die( GENERAL_ERROR, 'Couldnt delete comments', '', __LINE__, __FILE__, $sql );
						}
					}
					else
					{
						if ( $item_info['topic_id'] )
						{
							include( $module_root_path . 'pafiledb/includes/functions_comment.' . $phpEx );
							$mx_pa_comments = new pafiledb_comments();
							$mx_pa_comments->init( $item_info, 'phpbb');
							$mx_pa_comments->post('delete_all', $item_info['topic_id']);
						}
					}
				}

				$this->delete_items( $id );
			}

			$this->_pafiledb();
		}

		$template->set_filenames( array( 'admin' => $template_item ) );

		if ($mode == 'cat')
		{
			//$s_hidden_fields = '<input type="hidden" name="cat_id" value="' . $cat_id . '">';
		}

		$template->assign_vars( array(
			'DOWNLOAD' => $pafiledb_config['module_name'], // Module specific
			'U_DOWNLOAD' => mx_append_sid( $this->this_mxurl() ), // Module specific
			'L_MCP_TITLE' => $l_title,
			'L_MCP_EXPLAIN' => $l_explain,

			'S_HIDDEN_FIELDS' => $s_hidden_fields,
			'S_ACTION' => mx_append_sid( $this->this_mxurl( "action=mcp" ) )
		));

		//
		// Lets start displaying...
		//
		if ( in_array( $mode, array( 'unapproved', 'broken', 'cat', 'all' ) ) )
		{
			//
			// All items (or all items in cat)
			//
			if ( $mode == 'all' || $mode == 'cat' )
			{
				$where_sql = ($mode == 'cat') ? "AND file_catid = '$cat_id'" : '';
				$sql = "SELECT file_name, file_approved, file_id, file_broken
					FROM " . PA_FILES_TABLE . " as f1
					WHERE file_approved = '1'
					".$where_sql."
					AND file_catid IN (".$moderator_cat_ids.")
					ORDER BY file_time DESC";

				if ( ( !$result = $db->sql_query( $sql ) ) )
				{
					mx_message_die( GENERAL_ERROR, 'Couldn\'t get item info', '', __LINE__, __FILE__, $sql );
				}
				
				while ( $row = $db->sql_fetchrow( $result ) )
				{
					$total_num  = count($row);
				}				

				if ( !( $result = $pafiledb_functions->sql_query_limit( $sql, $pafiledb_config['pagination'], $start ) ) )
				{
					mx_message_die( GENERAL_ERROR, 'Couldn\'t get item info', '', __LINE__, __FILE__, $sql );
				}

				while ( $row = $db->sql_fetchrow( $result ) )
				{
					$all_rowset[] = $row;
				}
			}

			//
			// Unapproved files only
			//
			if ( $mode == 'unapproved' || $mode == 'all' || $mode == 'cat')
			{
				$sql = "SELECT file_name, file_approved, file_id, file_broken
					FROM " . PA_FILES_TABLE . "
					WHERE file_approved = '0'
					AND file_catid IN (".$moderator_cat_ids.")
					ORDER BY file_time DESC";

				if ($mode == 'unapproved')
				{
					if ( !( $result = $pafiledb_functions->sql_query_limit( $sql, $pafiledb_config['pagination'], $start ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Couldn\'t get item info', '', __LINE__, __FILE__, $sql );
					}
					$total_num = $db->sql_numrows( $result );
				}
				else
				{
					if ( ( !$result = $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Couldn\'t get item info', '', __LINE__, __FILE__, $sql );
					}
				}

				while ( $row = $db->sql_fetchrow( $result ) )
				{
					$unapproved_rowset[] = $row;
				}
			}

			//
			// Broken files only
			//
			if ( $mode == 'broken' || $mode == 'all' || $mode == 'cat')
			{
				$sql = "SELECT file_name, file_approved, file_id, file_broken
					FROM " . PA_FILES_TABLE . "
					WHERE file_broken = '1'
					AND file_catid IN (".$moderator_cat_ids.")
					ORDER BY file_time DESC";

				if ($mode == 'broken')
				{
					if ( !( $result = $pafiledb_functions->sql_query_limit( $sql, $pafiledb_config['pagination'], $start ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Couldn\'t get item info', '', __LINE__, __FILE__, $sql );
					}
					$total_num = $db->sql_numrows( $result );
				}
				else
				{
					if ( ( !$result = $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Couldn\'t get item info', '', __LINE__, __FILE__, $sql );
					}
				}

				while ( $row = $db->sql_fetchrow( $result ) )
				{
					$broken_rowset[] = $row;
				}
			}

			//
			// Ensure $total_num nonzero to validate pagination
			//
			$total_num = empty($total_num) ? 1 : $total_num;

			//
			// Define display sets
			//
			if ( $mode == '' )
			{
				$global_array = array(
					0 => array( 'lang_var' => $user->lang['Unapproved_items'],
						'row_set' => $unapproved_rowset,
						'approval' => 'approve' ),
					1 => array( 'lang_var' => $user->lang['Broken_items'],
						'row_set' => $broken_rowset,
						'approval' => 'both' ),
					2 => array( 'lang_var' => $user->lang['Approved_items'],
						'row_set' => $all_rowset,
						'approval' => 'unapprove' ) );
			}
			elseif ( $mode == 'all' )
			{
				$global_array = array(
					0 => array( 'lang_var' => $user->lang['Unapproved_items'],
						'row_set' => $unapproved_rowset,
						'approval' => 'approve' ),
					1 => array( 'lang_var' => $user->lang['Broken_items'],
						'row_set' => $broken_rowset,
						'approval' => 'both' ),
					2 => array( 'lang_var' => $user->lang['Approved_items'],
						'row_set' => $all_rowset,
						'approval' => 'unapprove' ) );
			}
			elseif ( $mode == 'cat' )
			{
				$global_array = array(
					0 => array( 'lang_var' => $user->lang['Unapproved_items'],
						'row_set' => $unapproved_rowset,
						'approval' => 'approve' ),
					1 => array( 'lang_var' => $user->lang['Broken_items'],
						'row_set' => $broken_rowset,
						'approval' => 'both' ),
					2 => array( 'lang_var' => $user->lang['Approved_items'],
						'row_set' => $all_rowset,
						'approval' => 'unapprove' ) );
			}
			elseif ( $mode == 'unapproved' )
			{
				$global_array = array(
					0 => array( 'lang_var' => $user->lang['Unapproved_items'],
						'row_set' => $unapproved_rowset,
						'approval' => 'approve' ) );
			}
			elseif ( $mode == 'broken' )
			{
				$global_array = array(
					0 => array( 'lang_var' => $user->lang['Broken_items'],
						'row_set' => $broken_rowset,
						'approval' => 'both' ) );
			}

			//
			// Generate Select dropdown navigation
			//
			$s_list = '';
			foreach( $s_actions as $item_mode => $user->lang_var )
			{
				$s = '';
				if ( $mode == $item_mode )
				{
					$s = ' selected="selected"';
				}
				$s_list .= '<option value="' . $item_mode . '"' . $s . '>' . $user->lang_var . '</option>';
			}

			$cat_list = '<select name="cat_id">';
			if ( !$this->cat_rowset[$cat_id]['cat_parent'] )
			{
				$cat_list .= '<option value="0" selected>' . $user->lang['None'] . '</option>\n';
			}
			else
			{
				$cat_list .= '<option value="0">' . $user->lang['None'] . '</option>\n';
			}
			$cat_list .= $this->generate_jumpbox( 0, 0, array( $cat_id => 1 ), true, true, 'auth_mod' );
			$cat_list .= '</select>';

			$template->assign_vars( array(
				'L_EDIT' => $user->lang['Editfile'],
				'L_DELETE' => $user->lang['Delete'],
				'L_CATEGORY' => $user->lang['Category'],
				'L_MODE' => $user->lang['View'],
				'L_GO' => $user->lang['Go'],
				'L_DELETE_ITEM' => $user->lang['Delete_selected'],
				'L_APPROVE' => $user->lang['Approve'],
				'L_UNAPPROVE' => $user->lang['Unapprove'],
				'L_APPROVE_ITEM' => $user->lang['Approve_selected'],
				'L_UNAPPROVE_ITEM' => $user->lang['Unapprove_selected'],
				'L_NO_ITEMS' => $user->lang['No_item'],

				'PAGINATION' => phpBB2::generate_pagination( mx_append_sid( $this->this_mxurl( "action=mcp&amp;mode_mcp=$mode&amp;sort_method=$sort_method&amp;sort_order=$sort_order" ) . ($mode == 'cat' ? "&amp;cat_id=$cat_id" : '') ), $total_num, $pafiledb_config['pagination'], $start ),
				'PAGE_NUMBER' => sprintf( $user->lang['Page_of'], ( floor( $start / $pafiledb_config['pagination'] ) + 1 ), ceil( $total_num / $pafiledb_config['pagination'] ) ),

				'S_CAT_LIST' => $cat_list,
				'S_MODE_SELECT' => $s_list )
			);

			foreach( $global_array as $data )
			{
				$approve = false;
				$unapprove = false;
				if ( $data['approval'] == 'both' )
				{
					$approve = $unapprove = true;
				}
				elseif ( $data['approval'] == 'approve' )
				{
					$approve = true;
				}
				elseif ( $data['approval'] == 'unapprove' )
				{
					$unapprove = true;
				}

				$template->assign_block_vars( 'mcp_mode', array(
					'L_MODE' => $data['lang_var'],
					'DATA' => ( isset( $data['row_set'] ) ) ? true : false,
					'APPROVE' => $approve,
					'UNAPPROVE' => $unapprove )
				);

				if ( isset( $data['row_set'] ) )
				{
					$i = ( $mode == 'unapproved' || $mode == 'broken' || ( count($global_array) > 1 && $data['approval'] == 'unapprove' ) ) ? $start + 1 : '1';

					foreach( $data['row_set'] as $item_data )
					{
						$approve_mode = ( $item_data['file_approved'] ) ? 'do_unapprove' : 'do_approve';
						$template->assign_block_vars( 'mcp_mode.row', array(
							'NAME' => $item_data['file_name'],
							'NUMBER' => $i++,
							'ID' => $item_data['file_id'],
							'U_EDIT' => mx_append_sid( $this->this_mxurl( "action=user_upload&amp;mode_mcp=edit&amp;file_id={$item_data['file_id']}" ) ),
							'U_DELETE' => mx_append_sid( $this->this_mxurl( "action=mcp&amp;mode_mcp=$mode&amp;do_mode=do_delete&amp;id={$item_data['file_id']}" ) ),
							'U_APPROVE' => mx_append_sid( $this->this_mxurl( "action=mcp&amp;mode_mcp=$mode&amp;do_mode=$approve_mode&amp;id={$item_data['file_id']}" ) . ($mode == 'cat' ? "&amp;cat_id=$cat_id" : '') ),
							'L_APPROVE' => ( $item_data['file_approved'] ) ? $user->lang['Unapprove'] : $user->lang['Approve'] )
						);
					}
				}
			}
		}

		//
		// Notification
		//
		$itemId = !empty($id) ? $id : $ids;
		$this->update_add_item_notify($itemId, $mode_notification);

		$template->assign_vars( array( 'ERROR' => ( sizeof( $this->error ) ) ? implode( '<br />', $this->error ) : '' ) );

		$this->display( $user->lang['MCP'], $template_item );
		$this->_pafiledb();
	}
}
?>