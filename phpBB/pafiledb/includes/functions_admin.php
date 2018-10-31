<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: functions_admin.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
* @copyright (c) 2002-2006 [Mohd Basri, PHP Arena, pafileDB, Jon Ohlsson] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

if ( !defined( 'IN_PORTAL' ) )
{
	die( "Hacking attempt" );
}

/**
 * Public pafiledb_admin class.
 *
 */
class pafiledb_admin extends pafiledb_public
{
	var $u_action;
	var $tpl_name;
	var $page_title;
	
	/**
	* Constructor
	* Init bbcode cache entries if bitfield is specified
	*/
	function pafiledb_admin($u_action = '')
	{
		global $config, $phpbb_root_path;
		
		if ($u_action)
		{
			$this->u_action = $u_action;
		}		
	}

	/**
	 * load admin module
	 *
	 * @param unknown_type $module_name send module name to load it
	 */
	function adminmodule( $module_name )
	{
		if ( !class_exists( 'pafiledb_' . $module_name ) )
		{
			global $phpbb_root_path, $phpEx;

			$this->module_name = $module_name;

			require_once( $phpbb_root_path . 'pafiledb/admin/admin_' . $module_name . '.' . $phpEx );
			eval( '$this->modules[' . $module_name . '] = new pafiledb_' . $module_name . '($this->u_action);' );

			if ( method_exists( $this->modules[$module_name], 'init' ) )
			{
				$this->modules[$module_name]->init();
			}
		}
	}

	function admin_display_cat_auth( $cat_parent = 0, $depth = 0 )
	{
		global $user, $phpbb_root_path, $template, $phpEx;
		global $cat_auth_fields, $cat_auth_const, $cat_auth_levels;
		global $cat_auth_approval_fields, $cat_auth_approval_const, $cat_auth_approval_levels;

		$pre = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $depth );
		if ( isset( $this->subcat_rowset[$cat_parent] ) )
		{
			foreach( $this->subcat_rowset[$cat_parent] as $sub_cat_id => $cat_data )
			{
				$template->assign_block_vars( 'cat_row', array(
					'CATEGORY_NAME' => $cat_data['cat_name'],
					'IS_HIGHER_CAT' => ( $cat_data['cat_allow_file'] ) ? false : true,
					'PRE' => $pre,
					'U_CAT' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=catauth_manage&amp;cat_parent=$sub_cat_id")
				));

				for( $j = 0; $j < count( $cat_auth_fields ); $j++ )
				{
					$custom_auth[$j] = '&nbsp;<select name="' . $cat_auth_fields[$j] . '[' . $sub_cat_id . ']' . '">';

					for( $k = 0; $k < count( $cat_auth_levels ); $k++ )
					{
						$selected = ( $cat_data[$cat_auth_fields[$j]] == $cat_auth_const[$k] ) ? ' selected="selected"' : '';
						$custom_auth[$j] .= '<option value="' . $cat_auth_const[$k] . '"' . $selected . '>' . $user->lang['Category_' . $cat_auth_levels[$k]] . '</option>';
					}
					$custom_auth[$j] .= '</select>&nbsp;';

					$template->assign_block_vars( 'cat_row.cat_auth_data', array( 'S_AUTH_LEVELS_SELECT' => $custom_auth[$j] ) );
				}

				for( $j = 0; $j < count( $cat_auth_approval_fields ); $j++ )
				{
					$custom_auth_approval[$j] = '&nbsp;<select name="' . $cat_auth_approval_fields[$j] . '[' . $sub_cat_id . ']' . '">';

					for( $k = 0; $k < count( $cat_auth_approval_levels ); $k++ )
					{
						$selected = ( $cat_data[$cat_auth_approval_fields[$j]] == $cat_auth_approval_const[$k] ) ? ' selected="selected"' : '';
						$custom_auth_approval[$j] .= '<option value="' . $cat_auth_approval_const[$k] . '"' . $selected . '>' . $user->lang['Category_' . $cat_auth_approval_levels[$k]] . '</option>';
					}
					$custom_auth_approval[$j] .= '</select>&nbsp;';

					$template->assign_block_vars( 'cat_row.cat_auth_data', array( 'S_AUTH_LEVELS_SELECT' => $custom_auth_approval[$j] ) );
				}

				$this->admin_display_cat_auth( $sub_cat_id, $depth + 1 );
			}
			return;
		}
		return;
	}

	function admin_display_cat_auth_ug( $cat_parent = 0, $depth = 0 )
	{
		global $phpbb_root_path, $template, $phpEx, $phpbb_admin_path;
		global $cat_auth_fields, $cat_auth_const, $cat_auth_levels, $optionlist_mod, $optionlist_acl_adv;

		$pre = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $depth );
		if ( isset( $this->subcat_rowset[$cat_parent] ) )
		{
			foreach( $this->subcat_rowset[$cat_parent] as $sub_cat_id => $cat_data )
			{
				$template->assign_block_vars( 'cat_row', array(
					'CAT_NAME' => $cat_data['cat_name'],
					'IS_HIGHER_CAT' => ( $cat_data['cat_allow_file'] ) ? false : true,
					'PRE' => $pre,
					'U_CAT' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=catauth&cat_id=$sub_cat_id"),
					'S_MOD_SELECT' => $optionlist_mod[$sub_cat_id]
				));

				for( $j = 0; $j < count( $cat_auth_fields ); $j++ )
				{
					$template->assign_block_vars( 'cat_row.aclvalues', array( 'S_ACL_SELECT' => $optionlist_acl_adv[$sub_cat_id][$j] ) );
				}
				$this->admin_display_cat_auth_ug( $sub_cat_id, $depth + 1 );
			}
			return;
		}
		return;
	}

	function admin_cat_main( $cat_parent = 0, $depth = 0 )
	{
		global $phpbb_root_path, $phpbb_admin_path, $template, $phpEx;

		$pre = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $depth );
		if ( isset( $this->subcat_rowset[$cat_parent] ) )
		{
			foreach( $this->subcat_rowset[$cat_parent] as $subcat_id => $cat_data )
			{
				$template->assign_block_vars( 'cat_row', array(
					'IS_HIGHER_CAT' => ( $cat_data['cat_allow_file'] == PA_CAT_ALLOW_FILE ) ? false : true,
					'U_CAT' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=cat_manage&cat_id=$subcat_id" ),
					'U_CAT_EDIT' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=cat_manage&amp;action=edit&amp;cat_id=$subcat_id" ),
					'U_CAT_DELETE' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=cat_manage&amp;action=delete&amp;cat_id=$subcat_id" ),
					'U_CAT_MOVE_UP' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=cat_manage&amp;action=cat_order&amp;move=-15&amp;cat_id_other=$subcat_id" ),
					'U_CAT_MOVE_DOWN' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=cat_manage&amp;action=cat_order&amp;move=15&amp;cat_id_other=$subcat_id" ),
					'U_CAT_RESYNC' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=cat_manage&amp;action=sync&amp;cat_id_other=$subcat_id" ),
					'CAT_NAME' => $cat_data['cat_name'],
					'PRE' => $pre
				));
				$this->admin_cat_main( $subcat_id, $depth + 1 );
			}
			return;
		}
		return;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $sel_id
	 * @param unknown_type $use_default_option
	 * @param unknown_type $select_name
	 * @return unknown
	 */
	function get_forums( $sel_id = 0, $use_default_option = false, $select_name = 'forum_id' )
	{
		global $db, $user;

		$sql = "SELECT forum_id, forum_name
			FROM " . FORUMS_TABLE;

		if ( !$result = $db->sql_query( $sql ) )
		{
			mx_message_die( GENERAL_ERROR, "Couldn't get list of forums", "", __LINE__, __FILE__, $sql );
		}

		$forumlist = '<select name="'.$select_name.'">';

		if ( $sel_id == 0 )
		{
			$forumlist .= '<option value="0" selected >'.$user->lang['Select_topic_id'].'</option>';
		}

		if ( $use_default_option )
		{
			$status = $sel_id == "-1" ? "selected" : "";
			$forumlist .= '<option value="-1" '.$status.' >::'.$user->lang['Use_default'].'::</option>';
		}

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			if ( $sel_id == $row['forum_id'] )
			{
				$status = "selected";
			}
			else
			{
				$status = '';
			}
			$forumlist .= '<option value="' . $row['forum_id'] . '" ' . $status . '>' . $row['forum_name'] . '</option>';
		}

		$forumlist .= '</select>';

		return $forumlist;
	}

	function pa_size_select( $select_name, $size_compare )
	{
		global $user;

		$size_types_text = array( $user->lang['Bytes'], $user->lang['KB'], $user->lang['MB'] );
		$size_types = array( 'b', 'kb', 'mb' );

		$select_field = '<select name="' . $select_name . '">';

		for ( $i = 0; $i < count( $size_types_text ); $i++ )
		{
			$selected = ( $size_compare == $size_types[$i] ) ? ' selected="selected"' : '';

			$select_field .= '<option value="' . $size_types[$i] . '"' . $selected . '>' . $size_types_text[$i] . '</option>';
		}

		$select_field .= '</select>';

		return ( $select_field );
	}

	function global_auth_check_user( $type, $key, $global_u_access, $is_admin )
	{
		$auth_user = 0;

		if ( !empty( $global_u_access ) )
		{
			$result = 0;
			switch ( $type )
			{
				case AUTH_ACL:
					$result = $global_u_access[$key];

				case AUTH_MOD:
					$result = $result || is_moderator( $global_u_access['group_id'] );

				case AUTH_ADMIN:
					$result = $result || $is_admin;
					break;
			}

			$auth_user = $auth_user || $result;
		}
		else
		{
			$auth_user = $is_admin;
		}

		return $auth_user;
	}

	function is_moderator( $group_id )
	{
		static $is_mod = false;

		if ( $is_mod !== false )
		{
			return $is_mod;
		}

		global $db;

		$sql = "SELECT *
			FROM " . PA_AUTH_ACCESS_TABLE . "
			WHERE group_id = $group_id
			AND auth_mod = '1'";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, "Couldn't check for moderator $sql", "", __LINE__, __FILE__, $sql );
		}

		return ( $is_mod = ( $db->sql_fetchrow( $result ) ) ? 1 : 0 );
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $cat_id
	 * @return unknown
	 */
	function update_add_cat( $cat_id = false )
	{
		global $db, $_POST, $user;

		$cat_name = ( isset( $_POST['cat_name'] ) ) ? htmlspecialchars( $_POST['cat_name'] ) : '';
		$cat_desc = ( isset( $_POST['cat_desc'] ) ) ? htmlspecialchars( $_POST['cat_desc'] ) : '';
		$cat_parent = ( isset( $_POST['cat_parent'] ) ) ? intval( $_POST['cat_parent'] ) : 0;
		$cat_allow_file = ( isset( $_POST['cat_allow_file'] ) ) ? intval( $_POST['cat_allow_file'] ) : 0;

		$cat_use_comments = ( isset( $_POST['cat_allow_comments'] ) ) ? intval( $_POST['cat_allow_comments'] ) : 0;
		$cat_internal_comments = ( isset( $_POST['internal_comments'] ) ) ? intval( $_POST['internal_comments'] ) : 0;
		$cat_autogenerate_comments = ( isset( $_POST['autogenerate_comments'] ) ) ? intval( $_POST['autogenerate_comments'] ) : 0;
		$comments_forum_id = intval( $_POST['comments_forum_id'] );

		$cat_show_pretext = ( isset( $_POST['show_pretext'] ) ) ? intval( $_POST['show_pretext'] ) : 0;

		$cat_use_ratings = ( isset( $_POST['cat_allow_ratings'] ) ) ? intval( $_POST['cat_allow_ratings'] ) : 0;

		$cat_notify = ( isset( $_POST['notify'] ) ) ? intval( $_POST['notify'] ) : 0;
		$cat_notify_group = ( isset( $_POST['notify_group'] ) ) ? intval( $_POST['notify_group'] ) : 0;

		if ( empty( $cat_name ) )
		{
			$this->error[] = $user->lang['Cat_name_missing'];
		}

		if ( $cat_parent )
		{
			if ( !$this->cat_rowset[$cat_parent]['cat_allow_file'] && !$cat_allow_file )
			{
				$this->error[] = $user->lang['Cat_conflict'];
			}
		}

		if ( sizeof( $this->error ) )
		{
			return;
		}

		$cat_name = str_replace( "\'", "''", $cat_name );
		$cat_desc = str_replace( "\'", "''", $cat_desc );

		if ( !$cat_id )
		{
			$cat_order = 0;
			if ( !empty( $this->subcat_rowset[$cat_parent] ) )
			{
				foreach( $this->subcat_rowset[$cat_parent] as $cat_data )
				{
					if ( $cat_order < $cat_data['cat_order'] )
					{
						$cat_order = $cat_data['cat_order'];
					}
				}
			}

			$cat_order += 10;

			$sql = "INSERT INTO " . PA_CATEGORY_TABLE . " (cat_name, cat_desc, cat_parent, parents_data, cat_order, cat_allow_file, cat_allow_ratings, cat_allow_comments, internal_comments, autogenerate_comments, comments_forum_id, show_pretext, notify, notify_group)
				VALUES('$cat_name', '$cat_desc', $cat_parent, '', $cat_order, $cat_allow_file, $cat_use_ratings, $cat_use_comments, $cat_internal_comments, $cat_autogenerate_comments, $comments_forum_id, $cat_show_pretext, $cat_notify, $cat_notify_group)";
				
			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldn\'t add a new category', '', __LINE__, __FILE__, $sql );
			}
		}
		else
		{
			$sql = 'UPDATE ' . PA_CATEGORY_TABLE . "
				SET cat_name = '$cat_name', cat_desc = '$cat_desc', cat_parent = $cat_parent, cat_allow_file = $cat_allow_file, cat_allow_ratings = $cat_use_ratings, cat_allow_comments = $cat_use_comments, internal_comments = $cat_internal_comments, autogenerate_comments = $cat_autogenerate_comments, comments_forum_id = $comments_forum_id, show_pretext = $cat_show_pretext, notify = $cat_notify, notify_group = $cat_notify_group
				WHERE cat_id = $cat_id";

			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldn\'t Edit this category', '', __LINE__, __FILE__, $sql );
			}

			if ( $cat_parent != $this->cat_rowset[$cat_id]['cat_parent'] )
			{
				$this->reorder_cat( $this->cat_rowset[$cat_id]['cat_parent'] );
				$this->reorder_cat( $cat_parent );
			}
			$this->modified( true );
		}

		if ( $cat_id )
		{
			return $cat_id;
		}
		else
		{
			return $db->sql_nextid();
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $cat_id
	 */
	function delete_cat( $cat_id = false )
	{
		global $db, $_POST, $user;

		$file_to_cat_id = ( isset( $_POST['file_to_cat_id'] ) ) ? intval( $_POST['file_to_cat_id'] ) : '';
		$subcat_to_cat_id = ( isset( $_POST['subcat_to_cat_id'] ) ) ? intval( $_POST['subcat_to_cat_id'] ) : '';
		$file_mode = ( isset( $_POST['file_mode'] ) ) ? htmlspecialchars( $_POST['file_mode'] ) : 'move';
		$subcat_mode = ( isset( $_POST['subcat_mode'] ) ) ? htmlspecialchars( $_POST['subcat_mode'] ) : 'move';

		if ( empty( $cat_id ) )
		{
			$this->error[] = $user->lang['Cdelerror'];
		}
		else
		{
			if ( ( $file_to_cat_id == -1 || empty( $file_to_cat_id ) ) && $file_mode == 'move' )
			{
				$this->error[] = $user->lang['Cdelerror'];
			}

			if ( $subcat_mode == 'move' && empty( $subcat_to_cat_id ) )
			{
				$this->error[] = $user->lang['Cdelerror'];
			}

			if ( sizeof( $this->error ) )
			{
				return;
			}

			$sql = 'DELETE FROM ' . PA_CATEGORY_TABLE . "
				WHERE cat_id = $cat_id";

			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt Query Info', '', __LINE__, __FILE__, $sql );
			}

			$this->reorder_cat( $this->cat_rowset[$cat_id]['cat_parent'] );

			if ( $file_mode == 'delete' )
			{
				$this->delete_items( $cat_id, 'category' );
			}
			else
			{
				$this->move_items( $cat_id, $file_to_cat_id );
			}

			if ( $subcat_mode == 'delete' )
			{
				$this->delete_subcat( $cat_id, $file_mode, $file_to_cat_id );
			}
			else
			{
				$this->move_subcat( $cat_id, $subcat_to_cat_id );
			}
			$this->modified( true );
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $from_cat
	 * @param unknown_type $to_cat
	 */
	function move_items( $from_cat, $to_cat )
	{
		global $db;

		$sql = 'UPDATE ' . PA_FILES_TABLE . "
			SET file_catid = $to_cat
			WHERE file_catid = $from_cat";

		if ( !( $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt move files', '', __LINE__, __FILE__, $sql );
		}

		$this->modified( true );
		return;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $cat_id
	 * @param unknown_type $file_mode
	 * @param unknown_type $to_cat
	 */
	function delete_subcat( $cat_id, $file_mode = 'delete', $to_cat = false )
	{
		global $db;

		if ( empty( $this->subcat_rowset[$cat_id] ) || count( $this->subcat_rowset[$cat_id] ) <= 0 )
		{
			return;
		}

		foreach( $this->subcat_rowset[$cat_id] as $sub_cat_id => $subcat_data )
		{
			$this->delete_subcat( $sub_cat_id, $file_mode, $to_cat );

			$sql = 'DELETE FROM ' . PA_CATEGORY_TABLE . "
				WHERE cat_id = $sub_cat_id";

			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt Query Info', '', __LINE__, __FILE__, $sql );
			}

			if ( $file_mode == 'delete' )
			{
				$this->delete_items( $sub_cat_id, 'category' );
			}
			else
			{
				$this->move_items( $sub_cat_id, $to_cat );
			}
		}
		$this->modified( true );
		return;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $from_cat
	 * @param unknown_type $to_cat
	 */
	function move_subcat( $from_cat, $to_cat )
	{
		global $db;

		$sql = 'UPDATE ' . PA_CATEGORY_TABLE . "
			SET cat_parent = $to_cat
			WHERE cat_parent = $from_cat";

		if ( !( $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt move Sub Category', '', __LINE__, __FILE__, $sql );
		}
		$this->modified( true );
		return;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $cat_parent
	 */
	function reorder_cat( $cat_parent )
	{
		global $db;

		$sql = 'SELECT cat_id, cat_order
			FROM ' . PA_CATEGORY_TABLE . "
			WHERE cat_parent = $cat_parent
			ORDER BY cat_order ASC";

		if ( !$result = $db->sql_query( $sql ) )
		{
			mx_message_die( GENERAL_ERROR, 'Could not get list of Categories', '', __LINE__, __FILE__, $sql );
		}

		$i = 10;
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$cat_id = $row['cat_id'];

			$sql = 'UPDATE ' . PA_CATEGORY_TABLE . "
					SET cat_order = $i
					WHERE cat_id = $cat_id";
			if ( !$db->sql_query( $sql ) )
			{
				mx_message_die( GENERAL_ERROR, 'Could not update order fields', '', __LINE__, __FILE__, $sql );
			}
			$i += 10;
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $cat_id
	 */
	function order_cat( $cat_id )
	{
		global $db, $_GET;

		$move = ( isset( $_GET['move'] ) ) ? intval( $_GET['move'] ) : 15;
		$cat_parent = $this->cat_rowset[$cat_id]['cat_parent'];

		$sql = 'UPDATE ' . PA_CATEGORY_TABLE . "
				SET cat_order = cat_order + $move
				WHERE cat_id = $cat_id";

		if ( !$result = $db->sql_query( $sql ) )
		{
			mx_message_die( GENERAL_ERROR, 'Could not change category order', '', __LINE__, __FILE__, $sql );
		}

		$this->reorder_cat( $cat_parent );
		$this->init();
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	function file_mainenance()
	{
		return false;
	}
}
?>