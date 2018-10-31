<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: admin_ug_auth_manage.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

if ( !defined( 'IN_PORTAL' ) || !defined( 'IN_ADMIN' ) )
{
	die( "Hacking attempt" );
}

class pafiledb_ug_auth_manage extends pafiledb_admin
{
	var $tpl_name;
	var $page_title;

	/**
	* Constructor
	* Init bbcode cache entries if bitfield is specified
	*/
	function pafiledb_ug_auth_manage($u_action = '')
	{
		global $config, $phpbb_root_path;
		
		if ($u_action)
		{
			$this->u_action = $u_action;
		}		
	}

	function main($mode)
	{
		global $db, $template, $template, $user, $phpEx, $pafiledb_functions, $pafiledb_cache, $pafiledb_config, $phpbb_root_path, $mx_request_vars;
		global $cat_auth_fields, $cat_auth_const, $cat_auth_levels, $global_auth_fields;
		global $optionlist_mod, $optionlist_acl_adv;

		$params = array( 'action' => 'action', 'user_id' => POST_USERS_URL, 'group_id' => POST_GROUPS_URL );

		foreach( $params as $var => $param )
		{
			$$var = ( isset( $_REQUEST[$param] ) ) ? $_REQUEST[$param] : '';
		}
		
		$action = $action ? $action : 'group';

		$user_id = intval( $user_id );
		$group_id = intval( $group_id );

		$cat_auth_fields = array( 'auth_view', 'auth_read', 'auth_view_file', 'auth_edit_file', 'auth_delete_file', 'auth_upload', 'auth_download', 'auth_rate', 'auth_email', 'auth_view_comment', 'auth_post_comment', 'auth_edit_comment', 'auth_delete_comment' );
		$global_auth_fields = array( 'auth_search', 'auth_stats', 'auth_toplist', 'auth_viewall' );

		$global_fields_names = array(
			'auth_search' => $user->lang['Auth_search'],
			'auth_stats' => $user->lang['Auth_stats'],
			'auth_toplist' => $user->lang['Auth_toplist'],
			'auth_viewall' => $user->lang['Auth_viewall']
		);

		$field_names = array(
			'auth_view' => $user->lang['View'],
			'auth_read' => $user->lang['Read'],
			'auth_view_file' => $user->lang['View_file'],
			'auth_edit_file' => $user->lang['Edit_file'],
			'auth_delete_file' => $user->lang['Delete_file'],
			'auth_upload' => $user->lang['Upload'],
			'auth_download' => $user->lang['Download_file'],
			'auth_rate' => $user->lang['Rate'],
			'auth_email' => $user->lang['Email'],
			'auth_view_comment' => $user->lang['View_comment'],
			'auth_post_comment' => $user->lang['Post_comment'],
			'auth_edit_comment' => $user->lang['Edit_comment'],
			'auth_delete_comment' => $user->lang['Delete_comment']
		);

		$permissions_menu = array(
			append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=catauth_manage" ) => $user->lang['Cat_Permissions'],
			append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=ug_auth_manage&amp;action=user" ) => $user->lang['User_Permissions'],
			append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=ug_auth_manage&amp;action=group" ) => $user->lang['Group_Permissions'],
			append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=ug_auth_manage&amp;action=global_user" ) => $user->lang['User_Global_Permissions'],
			append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=ug_auth_manage&amp;action=global_group" ) => $user->lang['Group_Global_Permissions']
		);
		
		foreach( $permissions_menu as $url => $l_name )
		{
			$template->assign_block_vars( 'pertype', array(
				'U_NAME' => $url,
				'L_NAME' => $l_name
			));
		}

		if ( isset( $_POST['submit'] ) && ( ( $action == 'user' && $user_id ) || ( $action == 'group' && $group_id ) ) )
		{
			if ( $action == 'user' )
			{
				$sql = "SELECT g.group_id
					FROM " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug
					LEFT JOIN " . USERS_TABLE . " u ON (ug.group_id = u.group_id)
					WHERE ug.user_id = $user_id
					AND g.group_id = ug.group_id";
				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					mx_message_die( GENERAL_ERROR, "Couldn't obtain user/group information", "", __LINE__, __FILE__, $sql );
				}
				$row = $db->sql_fetchrow( $result );
				$group_id = $row['group_id'];
				$db->sql_freeresult( $result );
			}

			$change_mod_list = ( isset( $_POST['moderator'] ) ) ? $_POST['moderator'] : array();

			$change_acl_list = array();
			for( $j = 0; $j < count( $cat_auth_fields ); $j++ )
			{
				$auth_field = $cat_auth_fields[$j];

				while ( list( $cat_id, $value ) = @each( $_POST['private_' . $auth_field] ) )
				{
					$change_acl_list[$cat_id][$auth_field] = $value;
				}
			}

			$sql = ( $action == 'user' ) ? "SELECT aa.* FROM " . PA_AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g LEFT JOIN " . USERS_TABLE . " u ON (ug.group_id = u.group_id) WHERE ug.user_id = $user_id AND g.group_id = ug.group_id AND aa.group_id = ug.group_id" : "SELECT * FROM " . PA_AUTH_ACCESS_TABLE . " WHERE group_id = $group_id";
			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, "Couldn't obtain user/group permissions", "", __LINE__, __FILE__, $sql );
			}

			$auth_access = array();
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				$auth_access[$row['cat_id']] = $row;
			}
			$db->sql_freeresult( $result );

			$cat_auth_action = array();
			$update_acl_status = array();
			$update_mod_status = array();

			foreach( $this->cat_rowset as $cat_id => $cat_data )
			{
				if ( 	( isset( $auth_access[$cat_id]['auth_mod'] ) && $change_mod_list[$cat_id]['auth_mod'] != $auth_access[$cat_id]['auth_mod'] ) ||
						( !isset( $auth_access[$cat_id]['auth_mod'] ) && !empty( $change_mod_list[$cat_id]['auth_mod'] ) ) )
				{
					$update_mod_status[$cat_id] = $change_mod_list[$cat_id]['auth_mod'];

					if ( !$update_mod_status[$cat_id] )
					{
						$cat_auth_action[$cat_id] = 'delete';
					}
					else if ( !isset( $auth_access[$cat_id]['auth_mod'] ) )
					{
						$cat_auth_action[$cat_id] = 'insert';
					}
					else
					{
						$cat_auth_action[$cat_id] = 'update';
					}
				}

				for( $j = 0; $j < count( $cat_auth_fields ); $j++ )
				{
					$auth_field = $cat_auth_fields[$j];

					if ( $cat_data[$auth_field] == AUTH_ACL && isset( $change_acl_list[$cat_id][$auth_field] ) )
					{
						if ( ( empty( $auth_access[$cat_id]['auth_mod'] ) &&
									( isset( $auth_access[$cat_id][$auth_field] ) && $change_acl_list[$cat_id][$auth_field] != $auth_access[$cat_id][$auth_field] ) ||
									( !isset( $auth_access[$cat_id][$auth_field] ) && !empty( $change_acl_list[$cat_id][$auth_field] ) ) ) || !empty( $update_mod_status[$cat_id] )
								)
						{
							$update_acl_status[$cat_id][$auth_field] = ( !empty( $update_mod_status[$cat_id] ) ) ? 0 : $change_acl_list[$cat_id][$auth_field];

							if ( isset( $auth_access[$cat_id][$auth_field] ) && empty( $update_acl_status[$cat_id][$auth_field] ) && $cat_auth_action[$cat_id] != 'insert' && $cat_auth_action[$cat_id] != 'update' )
							{
								$cat_auth_action[$cat_id] = 'delete';
							}
							else if ( !isset( $auth_access[$cat_id][$auth_field] ) && !( $cat_auth_action[$cat_id] == 'delete' && empty( $update_acl_status[$cat_id][$auth_field] ) ) )
							{
								$cat_auth_action[$cat_id] = 'insert';
							}
							else if ( isset( $auth_access[$cat_id][$auth_field] ) && !empty( $update_acl_status[$cat_id][$auth_field] ) )
							{
								$cat_auth_action[$cat_id] = 'update';
							}
						}
						else if ( ( empty( $auth_access[$cat_id]['auth_mod'] ) &&
									( isset( $auth_access[$cat_id][$auth_field] ) && $change_acl_list[$cat_id][$auth_field] == $auth_access[$cat_id][$auth_field] ) ) && $cat_auth_action[$cat_id] == 'delete' )
						{
							$cat_auth_action[$cat_id] = 'update';
						}
					}
				}
			}

			// Checks complete, make updates to DB

			$delete_sql = '';
			while ( list( $cat_id, $action ) = @each( $cat_auth_action ) )
			{
				if ( $action == 'delete' )
				{
					$delete_sql .= ( ( $delete_sql != '' ) ? ', ' : '' ) . $cat_id;
				}
				else
				{
					if ( $action == 'insert' )
					{
						$sql_field = '';
						$sql_value = '';
						while ( list( $auth_type, $value ) = @each( $update_acl_status[$cat_id] ) )
						{
							$sql_field .= ( ( $sql_field != '' ) ? ', ' : '' ) . $auth_type;
							$sql_value .= ( ( $sql_value != '' ) ? ', ' : '' ) . $value;
						}
						$sql_field .= ( ( $sql_field != '' ) ? ', ' : '' ) . 'auth_mod';
						$sql_value .= ( ( $sql_value != '' ) ? ', ' : '' ) . ( ( !isset( $update_mod_status[$cat_id] ) ) ? 0 : $update_mod_status[$cat_id] );

						$sql = "INSERT INTO " . PA_AUTH_ACCESS_TABLE . " (cat_id, group_id, $sql_field)
									VALUES ($cat_id, $group_id, $sql_value)";
					}
					else
					{
						$sql_values = '';
						while ( list( $auth_type, $value ) = @each( $update_acl_status[$cat_id] ) )
						{
							$sql_values .= ( ( $sql_values != '' ) ? ', ' : '' ) . $auth_type . ' = ' . $value;
						}
						$sql_values .= ( ( $sql_values != '' ) ? ', ' : '' ) . 'auth_mod = ' . ( ( !isset( $update_mod_status[$cat_id] ) ) ? 0 : $update_mod_status[$cat_id] );

						$sql = "UPDATE " . PA_AUTH_ACCESS_TABLE . "
							SET $sql_values
							WHERE group_id = $group_id
							AND cat_id = $cat_id";
					}
					if ( !( $result = $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, "Couldn't update private forum permissions", "", __LINE__, __FILE__, $sql );
					}
				}
			}

			if ( $delete_sql != '' )
			{
				$sql = "DELETE FROM " . PA_AUTH_ACCESS_TABLE . "
					WHERE group_id = $group_id
					AND cat_id IN ($delete_sql)";
				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					mx_message_die( GENERAL_ERROR, "Couldn't delete permission entries", "", __LINE__, __FILE__, $sql );
				}
			}

			$l_auth_return = ( $action == 'user' ) ? $user->lang['Click_return_userauth'] : $user->lang['Click_return_groupauth'];
			$message = $user->lang['Auth_updated'] . '<br /><br />' . sprintf( $l_auth_return, '<a href="' . $this->u_action . "&action=$action" . '">', '</a>' ) . '<br /><br />' . sprintf( $user->lang['Click_return_admin_index'], '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx") . '">', '</a>' );
			trigger_error($message . adm_back_link($this->u_action));
		}
		elseif ( isset( $_POST['submit'] ) && ( ( $action == 'global_user' && $user_id ) || ( $action == 'global_group' && $group_id ) ) )
		{
			if ( $action == 'global_user' )
			{			
				$sql = "SELECT g.group_id
					FROM " . GROUPS_TABLE . " g
						LEFT JOIN " . USER_GROUP_TABLE . " ug ON (ug.group_id = g.group_id)
					WHERE ug.user_id = " . $user_id . "
					ORDER BY g.group_type DESC, g.group_id DESC";
				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					mx_message_die( GENERAL_ERROR, "Couldn't obtain user/group information", "", __LINE__, __FILE__, $sql );
				}
				$row = $db->sql_fetchrow( $result );
				$group_id = $row['group_id'];
				$db->sql_freeresult( $result );
			}

			$change_acl_list = array();
			for( $j = 0; $j < count( $global_auth_fields ); $j++ )
			{
				$auth_field = $global_auth_fields[$j];
				$change_acl_list[$auth_field] = $_POST['private_' . $auth_field];
			}

			$sql = ( $action == 'global_user' ) ? "SELECT aa.* FROM " . PA_AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g WHERE ug.user_id = $user_id AND g.group_id = ug.group_id AND aa.group_id = ug.group_id AND g.group_id = " . true . " AND aa.cat_id = '0'" : "SELECT * FROM " . PA_AUTH_ACCESS_TABLE . " WHERE group_id = $group_id AND cat_id = '0'";
			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, "Couldn't obtain user/group permissions", "", __LINE__, __FILE__, $sql );
			}

			$auth_access = '';
			if ( $row = $db->sql_fetchrow( $result ) )
			{
				$auth_access = $row;
			}
			$db->sql_freeresult( $result );

			$global_auth_action = array();
			$update_acl_status = array();

			for( $j = 0; $j < count( $global_auth_fields ); $j++ )
			{
				$auth_field = $global_auth_fields[$j];

				if ( $pafiledb_config[$auth_field] == AUTH_ACL && isset( $change_acl_list[$auth_field] ) )
				{
					if ( ( !$this->is_moderator( $group_id ) &&
								( isset( $auth_access[$auth_field] ) && $change_acl_list[$auth_field] != $auth_access[$auth_field] ) ||
								( !isset( $auth_access[$cat_id][$auth_field] ) && !empty( $change_acl_list[$auth_field] ) ) )
							)
					{
						$update_acl_status[$auth_field] = $change_acl_list[$auth_field];

						if ( isset( $auth_access[$auth_field] ) && empty( $update_acl_status[$auth_field] ) && $global_auth_action != 'insert' && $global_auth_action != 'update' )
						{
							$global_auth_action = 'delete';
						}
						else if ( !isset( $auth_access[$auth_field] ) && !( $global_auth_action == 'delete' && empty( $update_acl_status[$auth_field] ) ) )
						{
							$global_auth_action = 'insert';
						}
						else if ( isset( $auth_access[$auth_field] ) && !empty( $update_acl_status[$auth_field] ) )
						{
							$global_auth_action = 'update';
						}
					}
					else if ( ( !$this->is_moderator( $auth_access['group_id'] ) &&
								( isset( $auth_access[$auth_field] ) && $change_acl_list[$auth_field] == $auth_access[$auth_field] ) ) && $global_auth_action == 'delete' )
					{
						$global_auth_action = 'update';
					}
				}
			}

			// Checks complete, make updates to DB

			$delete_sql = 0;

			if ( $global_auth_action == 'delete' )
			{
				$delete_sql = 1;
			}
			else
			{
				if ( $global_auth_action == 'insert' )
				{
					$sql_field = '';
					$sql_value = '';
					while ( list( $auth_type, $value ) = @each( $update_acl_status ) )
					{
						$sql_field .= ( ( $sql_field != '' ) ? ', ' : '' ) . $auth_type;
						$sql_value .= ( ( $sql_value != '' ) ? ', ' : '' ) . $value;
					}
					$sql = "INSERT INTO " . PA_AUTH_ACCESS_TABLE . " (cat_id, group_id, $sql_field)
								VALUES (0, $group_id, $sql_value)";
				}
				else
				{
					$sql_values = '';
					while ( list( $auth_type, $value ) = @each( $update_acl_status ) )
					{
						$sql_values .= ( ( $sql_values != '' ) ? ', ' : '' ) . $auth_type . ' = ' . $value;
					}
					$sql = "UPDATE " . PA_AUTH_ACCESS_TABLE . "
							SET $sql_values
							WHERE group_id = $group_id
							AND cat_id = 0";
				}
				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					mx_message_die( GENERAL_ERROR, "Couldn't update private forum permissions", "", __LINE__, __FILE__, $sql );
				}
			}

			if ( $delete_sql )
			{
				$sql = "DELETE FROM " . PA_AUTH_ACCESS_TABLE . "
					WHERE group_id = $group_id
					AND cat_id = 0";
				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					mx_message_die( GENERAL_ERROR, "Couldn't delete permission entries", "", __LINE__, __FILE__, $sql );
				}
			}

			$l_auth_return = ( $action == 'global_user' ) ? $user->lang['Click_return_userauth'] : $user->lang['Click_return_groupauth'];
			$message = $user->lang['Auth_updated'] . '<br /><br />' . sprintf( $l_auth_return, '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", "&amp;action=$action") . '">', '</a>' ) . '<br /><br />' . sprintf( $user->lang['Click_return_admin_index'], '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx") . '">', '</a>' );
			trigger_error($message . adm_back_link($this->u_action));
		}
		elseif ( ( $action == 'user' && ( isset( $_POST['username'] ) || $user_id ) ) || ( $action == 'group' && $group_id ) )
		{
			if ( isset( $_POST['username'] ) )
			{
				$this_userdata = mx_get_userdata( $_POST['username'], true );
				if ( !is_array( $this_userdata ) )
				{
					trigger_error($user->lang['No_such_user'] . adm_back_link($this->u_action));
				}
				$user_id = $this_userdata['user_id'];
			}

			// Front end

			$sql = 'SELECT u.user_id, u.username, u.username_clean, u.user_regdate, u.user_posts, u.group_id, ug.group_leader, ug.user_pending
				FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . " ug
				WHERE ";
				
			$sql .= ( $action == 'user' ) ? "u.user_id = $user_id AND ug.user_id = u.user_id" : "ug.group_id = $group_id AND u.user_id = ug.user_id";
			
			$sql .= " ORDER BY ug.group_leader DESC, ug.user_pending ASC, u.username_clean";
			
			if ( !( $result = @$db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, "Couldn't obtain user/group information", "", __LINE__, __FILE__, $sql );
			}
			$ug_info = array();
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				$ug_info[] = $row;
			}
			$db->sql_freeresult( $result );
			
			$sql_user = 'SELECT aa.*, g.group_name, g.group_id, g.group_type
				FROM ' . GROUPS_TABLE . ' g, ' . PA_AUTH_ACCESS_TABLE . ' aa
					LEFT JOIN ' . USER_GROUP_TABLE . ' ug ON (ug.group_id = g.group_id)
				WHERE ug.user_id = ' . $user_id . '
					AND g.group_id = aa.group_id
				ORDER BY g.group_type DESC, g.group_id DESC';

			$sql_group = 'SELECT * 
				FROM ' . PA_AUTH_ACCESS_TABLE . ' 
				WHERE group_id = ' . $group_id;				

			$sql = ( $action == 'user' ) ? $sql_user : $sql_group;
			@$db->sql_query($sql);


			$auth_access = array();
			$auth_access_count = array();
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				$auth_access[$row['cat_id']][] = $row;
				$auth_access_count[$row['cat_id']]++;
			}
			$db->sql_freeresult( $result );

			$is_admin = ( $action == 'user' ) ? ( ( $ug_info[0]['user_level'] == ADMIN && $ug_info[0]['user_id'] != ANONYMOUS ) ? 1 : 0 ) : 0;

			foreach( $this->cat_rowset as $cat_id => $cat_data )
			{
				for( $j = 0; $j < count( $cat_auth_fields ); $j++ )
				{
					$key = $cat_auth_fields[$j];
					$value = $cat_data[$key];

					switch ( $value )
					{
						case AUTH_ALL:
						case AUTH_REG:
							$auth_ug[$cat_id][$key] = 1;
							break;

						case AUTH_ACL:
							$auth_ug[$cat_id][$key] = ( !empty( $auth_access_count[$cat_id] ) ) ? $this->auth_check_user( AUTH_ACL, $key, $auth_access[$cat_id], $is_admin ) : 0;
							$auth_field_acl[$cat_id][$key] = $auth_ug[$cat_id][$key];
							break;

						case AUTH_MOD:
							$auth_ug[$cat_id][$key] = ( !empty( $auth_access_count[$cat_id] ) ) ? $this->auth_check_user( AUTH_MOD, $key, $auth_access[$cat_id], $is_admin ) : 0;
							break;

						case AUTH_ADMIN:
							$auth_ug[$cat_id][$key] = $is_admin;
							break;

						default:
							$auth_ug[$cat_id][$key] = 0;
							break;
					}
				}

				// Is user a moderator?

				$auth_ug[$cat_id]['auth_mod'] = ( !empty( $auth_access_count[$cat_id] ) ) ? $this->auth_check_user( AUTH_MOD, 'auth_mod', $auth_access[$cat_id], 0 ) : 0;
			}

			$optionlist_acl_adv = array();
			$optionlist_mod = array();

			foreach( $auth_ug as $cat_id => $user_ary )
			{
				for( $k = 0; $k < count( $cat_auth_fields ); $k++ )
				{
					$field_name = $cat_auth_fields[$k];

					if ( $this->cat_rowset[$cat_id][$field_name] == AUTH_ACL )
					{
						$optionlist_acl_adv[$cat_id][$k] = '<select name="private_' . $field_name . '[' . $cat_id . ']">';

						if ( isset( $auth_field_acl[$cat_id][$field_name] ) && !( $is_admin || $user_ary['auth_mod'] ) )
						{
							if ( !$auth_field_acl[$cat_id][$field_name] )
							{
								$optionlist_acl_adv[$cat_id][$k] .= '<option value="1">' . $user->lang['ON'] . '</option><option value="0" selected="selected">' . $user->lang['OFF'] . '</option>';
							}
							else
							{
								$optionlist_acl_adv[$cat_id][$k] .= '<option value="1" selected="selected">' . $user->lang['ON'] . '</option><option value="0">' . $user->lang['OFF'] . '</option>';
							}
						}
						else
						{
							if ( $is_admin || $user_ary['auth_mod'] )
							{
								$optionlist_acl_adv[$cat_id][$k] .= '<option value="1">' . $user->lang['ON'] . '</option>';
							}
							else
							{
								$optionlist_acl_adv[$cat_id][$k] .= '<option value="1">' . $user->lang['ON'] . '</option><option value="0" selected="selected">' . $user->lang['OFF'] . '</option>';
							}
						}

						$optionlist_acl_adv[$cat_id][$k] .= '</select>';
					}
				}

				$optionlist_mod[$cat_id] = '<select name="moderator[' . $cat_id . ']">';
				$optionlist_mod[$cat_id] .= ( $user_ary['auth_mod'] ) ? '<option value="1" selected="selected">' . $user->lang['Is_Moderator'] . '</option><option value="0">' . $user->lang['Not_Moderator'] . '</option>' : '<option value="1">' . $user->lang['Is_Moderator'] . '</option><option value="0" selected="selected">' . $user->lang['Not_Moderator'] . '</option>';
				$optionlist_mod[$cat_id] .= '</select>';
			}
			$this->admin_display_cat_auth_ug();

			if ( $action == 'user' )
			{
				$t_username = $ug_info[0]['username'];
			}
			else
			{
				$t_groupname = $ug_info[0]['group_name'];
			}

			$name = array();
			$id = array();
			for( $i = 0; $i < count( $ug_info ); $i++ )
			{
				if ( ( $action == 'user' && !$ug_info[$i]['group_single_user'] ) || $action == 'group' )
				{
					$name[] = ( $action == 'user' ) ? $ug_info[$i]['group_name'] : $ug_info[$i]['username'];
					$id[] = ( $action == 'user' ) ? intval( $ug_info[$i]['group_id'] ) : intval( $ug_info[$i]['user_id'] );
				}
			}

			if ( count( $name ) )
			{
				$t_usergroup_list = '';
				for( $i = 0; $i < count( $ug_info ); $i++ )
				{
					$ug = ( $action == 'user' ) ? 'group&amp;' . POST_GROUPS_URL : 'user&amp;' . POST_USERS_URL;

					$t_usergroup_list .= ( ( $t_usergroup_list != '' ) ? ', ' : '' ) . '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=ug_auth_manage&amp;action=$ug=" . $id[$i] ) . '">' . $name[$i] . '</a>';
				}
			}
			else
			{
				$t_usergroup_list = $user->lang['None'];
			}

			for( $i = 0; $i < count( $cat_auth_fields ); $i++ )
			{
				$cell_title = $field_names[$cat_auth_fields[$i]];

				$template->assign_block_vars( 'acltype', array( 'L_UG_ACL_TYPE' => $cell_title ) );
				$s_column_span++;
			}

			$s_hidden_fields = '<input type="hidden" name="action" value="' . $action . '" />';
			$s_hidden_fields .= ( $action == 'user' ) ? '<input type="hidden" name="' . POST_USERS_URL . '" value="' . $user_id . '" />' : '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';

			//$template->set_filenames( array( 'body' => 'acp_pafiledb_auth_ug.html' ) );
			
			$this->tpl_name = 'acp_pafiledb_auth_ug';
			$this->page_title= ($action == 'user') ? $user->lang['Auth_Control_User'] : $user->lang['Auth_Control_Group'];

			if ( $action == 'user' )
			{
				$template->assign_vars( array(
					'USER' => true,
					'USERNAME' => $t_username,
					'USER_LEVEL' => $user->lang['User_Level'],
					'USER_GROUP_MEMBERSHIPS' => $user->lang['Group_memberships'] . ' : ' . $t_usergroup_list
				));
			}
			else
			{
				$template->assign_vars( array(
					'USER' => false,
					'USERNAME' => $t_groupname,
					'GROUP_MEMBERSHIP' => $user->lang['Usergroup_members'] . ' : ' . $t_usergroup_list
				));
			}

			$template->assign_vars( array(
				'SHOW_MOD' => true,
				'L_USER_OR_GROUPNAME' => ( $action == 'user' ) ? $user->lang['Username'] : $user->lang['Group_name'],

				'L_AUTH_TITLE' => ( $action == 'user' ) ? $user->lang['Auth_Control_User'] : $user->lang['Auth_Control_Group'],
				'L_AUTH_EXPLAIN' => ( $action == 'user' ) ? $user->lang['User_auth_explain'] : $user->lang['Group_auth_explain'],
				'L_MODERATOR_STATUS' => $user->lang['Moderator_status'],
				'L_PERMISSIONS' => $user->lang['Permissions'],
				'L_SUBMIT' => $user->lang['Submit'],
				'L_RESET' => $user->lang['Reset'],
				'L_CAT' => $user->lang['Category'],

				'U_USER_OR_GROUP' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=ug_auth_manage"),

				'S_COLUMN_SPAN' => $s_column_span + 2,
				'S_AUTH_ACTION' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=ug_auth_manage"),
				'S_HIDDEN_FIELDS' => $s_hidden_fields
			));
		}
		elseif ( ( $action == 'global_user' && ( isset( $_POST['username'] ) || $user_id ) ) || ( $action == 'global_group' && $group_id ) )
		{
			if ( isset( $_POST['username'] ) )
			{
				$this_userdata = mx_get_userdata( $_POST['username'], true );
				if ( !is_array( $this_userdata ) )
				{
					trigger_error($user->lang['No_such_user'] . adm_back_link($this->u_action));
				}
				$user_id = $this_userdata['user_id'];
			}

			// Front end

			if ( $action == 'global_user' )
			{
				$sql = "SELECT g.group_id
					FROM " . GROUPS_TABLE . " g
						LEFT JOIN " . USER_GROUP_TABLE . " ug ON (ug.group_id = g.group_id)
					WHERE ug.user_id = " . $user_id . "
					ORDER BY g.group_type DESC, g.group_id DESC";
				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					mx_message_die( GENERAL_ERROR, "Couldn't obtain user/group information", "", __LINE__, __FILE__, $sql );
				}
				$row = $db->sql_fetchrow( $result );
				$group_id = $row['group_id'];
				$db->sql_freeresult( $result );
			}
			
			$sql_user = 'SELECT u.*, g.group_name, g.group_id, g.group_type
				FROM ' . USERS_TABLE . ' u, ' . GROUPS_TABLE . ' g
					LEFT JOIN ' . USER_GROUP_TABLE . ' ug ON (ug.group_id = g.group_id)
				WHERE u.user_id = ' . $user_id . '
					AND ug.user_id = u.user_id 
				ORDER BY g.group_type DESC, g.group_id DESC';

			$sql_group = 'SELECT u.*, g.group_name, g.group_id, g.group_type
				FROM ' . USERS_TABLE . ' u, ' . GROUPS_TABLE . ' g
					LEFT JOIN ' . USER_GROUP_TABLE . ' ug ON (ug.group_id = g.group_id)
				WHERE g.group_id = ' . $group_id . '
					AND ug.user_id = u.user_id 
				ORDER BY g.group_type DESC, g.group_id DESC';			

			$sql = ( $action == 'global_user' ) ? $sql_user : $sql_group;			

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, "Couldn't obtain user/group information", "", __LINE__, __FILE__, $sql );
			}
			$ug_info = array();
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				$ug_info[] = $row;
			}
			$db->sql_freeresult( $result );
			
			$sql_user = "SELECT aa.*, g.group_id 
				FROM " . PA_AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g, " . USERS_TABLE . " u 
				WHERE ug.user_id = $user_id 
					AND u.group_id = ug.group_id
					AND g.group_id = ug.group_id 
					AND aa.group_id = ug.group_id
					AND aa.cat_id = 0
				ORDER BY g.group_type DESC, g.group_id DESC";

			$sql_group = 'SELECT * 
				FROM ' . PA_AUTH_ACCESS_TABLE . ' 
				WHERE group_id = ' . $group_id . '
					AND cat_id = 0';				

			$sql = ( $action == 'global_user' ) ? $sql_user : $sql_group;			

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, "Couldn't obtain user/group permissions", "", __LINE__, __FILE__, $sql );
			}

			$auth_access = array();
			$auth_access_count = 0;
			if ( $row = $db->sql_fetchrow( $result ) )
			{
				$auth_access = $row;
				$auth_access_count++;
			}
			$db->sql_freeresult( $result );

			$is_admin = ( $action == 'global_user' ) ? ( ( $ug_info[0]['user_level'] == ADMIN && $ug_info[0]['user_id'] != ANONYMOUS ) ? 1 : 0 ) : 0;

			for( $j = 0; $j < count( $global_auth_fields ); $j++ )
			{
				$key = $global_auth_fields[$j];
				$value = $pafiledb_config[$key];

				switch ( $value )
				{
					case AUTH_ALL:
					case AUTH_REG:
						$auth_ug[$key] = 1;
						break;

					case AUTH_ACL:
						$auth_ug[$key] = ( !empty( $auth_access_count ) ) ? $this->global_auth_check_user( AUTH_ACL, $key, $auth_access, $is_admin ) : 0;
						$auth_field_acl[$key] = $auth_ug[$key];
						break;

					case AUTH_MOD:
						$auth_ug[$key] = ( !empty( $auth_access_count ) ) ? $this->global_auth_check_user( AUTH_MOD, $key, $auth_access, $is_admin ) : 0;
						break;

					case AUTH_ADMIN:
						$auth_ug[$key] = $is_admin;
						break;

					default:
						$auth_ug[$key] = 0;
						break;
				}
			}

			for( $k = 0; $k < count( $global_auth_fields ); $k++ )
			{
				$field_name = $global_auth_fields[$k];

				if ( $pafiledb_config[$field_name] == AUTH_ACL )
				{
					$optionlist_acl_adv[$k] = '<select name="private_' . $field_name . '">';

					if ( isset( $auth_field_acl[$field_name] ) && !( $is_admin || $this->is_moderator( $group_id ) ) )
					{
						if ( !$auth_field_acl[$field_name] )
						{
							$optionlist_acl_adv[$k] .= '<option value="1">' . $user->lang['ON'] . '</option><option value="0" selected="selected">' . $user->lang['OFF'] . '</option>';
						}
						else
						{
							$optionlist_acl_adv[$k] .= '<option value="1" selected="selected">' . $user->lang['ON'] . '</option><option value="0">' . $user->lang['OFF'] . '</option>';
						}
					}
					else
					{
						if ( $is_admin || $this->is_moderator( $group_id ) )
						{
							$optionlist_acl_adv[$k] .= '<option value="1">' . $user->lang['ON'] . '</option>';
						}
						else
						{
							$optionlist_acl_adv[$k] .= '<option value="1">' . $user->lang['ON'] . '</option><option value="0" selected="selected">' . $user->lang['OFF'] . '</option>';
						}
					}

					$optionlist_acl_adv[$k] .= '</select>';
				}
			}

			$template->assign_block_vars( 'cat_row', array(
				'CAT_NAME' => ( $action == 'global_user' ) ? $user->lang['User_Global_Permissions'] : $user->lang['Group_Global_Permissions'],
				'IS_HIGHER_CAT' => false,
				'PRE' => '',

				'U_CAT' => $this->u_action
			));

			for( $j = 0; $j < count( $global_auth_fields ); $j++ )
			{
				$template->assign_block_vars( 'cat_row.aclvalues', array( 'S_ACL_SELECT' => $optionlist_acl_adv[$j] ) );
			}

			if ( $action == 'global_user' )
			{
				$t_username = $ug_info[0]['username'];
			}
			else
			{
				$t_groupname = $ug_info[0]['group_name'];
			}

			$name = array();
			$id = array();
			for( $i = 0; $i < count( $ug_info ); $i++ )
			{
				if ( ( $action == 'global_user' && !$ug_info[$i]['group_single_user'] ) || $action == 'global_group' )
				{
					$name[] = ( $action == 'global_user' ) ? $ug_info[$i]['group_name'] : $ug_info[$i]['username'];
					$id[] = ( $action == 'global_user' ) ? intval( $ug_info[$i]['group_id'] ) : intval( $ug_info[$i]['user_id'] );
				}
			}

			if ( count( $name ) )
			{
				$t_usergroup_list = '';
				for( $i = 0; $i < count( $ug_info ); $i++ )
				{
					$ug = ( $action == 'global_user' ) ? 'global_group&amp;' . POST_GROUPS_URL : 'global_user&amp;' . POST_USERS_URL;

					$t_usergroup_list .= ( ( $t_usergroup_list != '' ) ? ', ' : '' ) . '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=ug_auth_manage&amp;action=$ug=" . $id[$i] ) . '">' . $name[$i] . '</a>';
				}
			}
			else
			{
				$t_usergroup_list = $user->lang['None'];
			}

			for( $i = 0; $i < count( $global_auth_fields ); $i++ )
			{
				$cell_title = $global_fields_names[$global_auth_fields[$i]];

				$template->assign_block_vars( 'acltype', array( 'L_UG_ACL_TYPE' => $cell_title ) );
				$s_column_span++;
			}

			$s_hidden_fields = '<input type="hidden" name="action" value="' . $action . '" />';
			$s_hidden_fields .= ( $action == 'global_user' ) ? '<input type="hidden" name="' . POST_USERS_URL . '" value="' . $user_id . '" />' : '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';

			//$template->set_filenames( array( 'body' => 'acp_pafiledb_auth_manage.html' ) );
			
			$this->tpl_name = 'acp_pafiledb_auth_manage';
			$this->page_title= ($action == 'user') ? $user->lang['Auth_Control_User'] : $user->lang['Auth_Control_Group'];

			if ( $action == 'global_user' )
			{
				$template->assign_vars( array(
					'USER' => true,
					'USERNAME' => $t_username,
					'USER_LEVEL' => $user->lang['User_Level'],
					'USER_GROUP_MEMBERSHIPS' => $user->lang['Group_memberships'] . ' : ' . $t_usergroup_list
				));
			}
			else
			{
				$template->assign_vars( array(
					'USER' => false,
					'USERNAME' => $t_groupname,
					'GROUP_MEMBERSHIP' => $user->lang['Usergroup_members'] . ' : ' . $t_usergroup_list
				));
			}

			$template->assign_vars( array(
				'SHOW_MOD' => false,

				'L_USER_OR_GROUPNAME' => ( $action == 'global_user' ) ? $user->lang['Username'] : $user->lang['Group_name'],

				'L_AUTH_TITLE' => ( $action == 'global_user' ) ? $user->lang['Auth_Control_User'] : $user->lang['Auth_Control_Group'],
				'L_AUTH_EXPLAIN' => ( $action == 'global_user' ) ? $user->lang['User_auth_explain'] : $user->lang['Group_auth_explain'],
				'L_PERMISSIONS' => $user->lang['Permissions'],
				'L_SUBMIT' => $user->lang['Submit'],
				'L_RESET' => $user->lang['Reset'],
				'L_CAT' => ( $action == 'global_user' ) ? $user->lang['User_Global_Permissions'] : $user->lang['Group_Global_Permissions'],

				'U_USER_OR_GROUP' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=ug_auth_manage"),

				'S_COLUMN_SPAN' => $s_column_span + 1,
				'S_AUTH_ACTION' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=ug_auth_manage"),
				'S_HIDDEN_FIELDS' => $s_hidden_fields
			));
		}
		else
		{
			// Select a user/group
			//$template->set_filenames( array('body' => ($action == 'user' || $action == 'global_user') ? 'acp_pafiledb_user_select.html' : 'acp_pafiledb_auth_select_body.html'));
			
			$this->tpl_name = ($action == 'user' || $action == 'global_user') ? 'acp_pafiledb_user_select' : 'acp_pafiledb_auth_select';
			$this->page_title = ($action == 'user' || $action == 'global_user') ? $user->lang['Auth_Control_User'] : $user->lang['Auth_Control_Group'];


			if ( $action == 'user' || $action == 'global_user' )
			{
				$template->assign_vars( array(
					'L_FIND_USERNAME' => $user->lang['Find_username'],
					'U_SEARCH_USER' => mx_append_sid( $phpbb_root_path . "search.$phpEx?mode=searchuser" )
				));
			}
			else
			{
				// Get us all the groups
				$sql = 'SELECT g.group_id, g.group_name, g.group_type
					FROM ' . GROUPS_TABLE . ' g
					ORDER BY g.group_type ASC, g.group_name';
				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					mx_message_die( GENERAL_ERROR, "Couldn't get group list", "", __LINE__, __FILE__, $sql );
				}

				if ( $row = $db->sql_fetchrow( $result ) )
				{
					$select_list = '<select name="' . POST_GROUPS_URL . '">';
					do
					{
						$select_list .= ($row['group_type'] == GROUP_SPECIAL) ? '<option value="' . $row['group_id'] . '">' . $user->lang['G_' . $row['group_name']] . '</option>' : '<option value="' . $row['group_id'] . '">' . $row['group_name'] . '</option>';
					}
					while ( $row = $db->sql_fetchrow( $result ) );
					$select_list .= '</select>';
				}
				else
				{
					trigger_error($user->lang['NO_GROUP'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$template->assign_vars( array( 'S_AUTH_SELECT' => $select_list ) );
			}

			$s_hidden_fields = '<input type="hidden" name="action" value="' . $action . '" />';

			$l_type = ( $action == 'user' || $action == 'global_user' ) ? 'USER' : 'AUTH';

			$template->assign_vars( array(
				'L_' . $l_type . '_TITLE' => ( $action == 'user' || $action == 'global_user' ) ? $user->lang['Auth_Control_User'] : $user->lang['Auth_Control_Group'],
				'L_' . $l_type . '_EXPLAIN' => ( $action == 'user' || $action == 'global_user' ) ? $user->lang['User_auth_explain'] : $user->lang['Group_auth_explain'],
				'L_' . $l_type . '_SELECT' => ( $action == 'user' || $action == 'global_user' ) ? $user->lang['Select_a_User'] : $user->lang['Select_a_Group'],
				'L_LOOK_UP' => ( $action == 'user' || $action == 'global_user' ) ? $user->lang['Look_up_User'] : $user->lang['Look_up_Group'],

				'S_HIDDEN_FIELDS' => $s_hidden_fields,
				'S_' . $l_type . '_ACTION' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=pafiledb&amp;mode=ug_auth_manage")
			));
		}
		
		$l_type = ( $action == 'user' || $action == 'global_user' ) ? 'USER' : 'AUTH';
		
		$template->assign_vars( array(
			'L_USERNAME' => $user->lang['Username'],
			'L_AUTH_SELECT' => ( $action == 'user' || $action == 'global_user' ) ? $user->lang['SELECT_USER'] : $user->lang['SELECT_GROUP'],
			'L_LOOK_UP' => ( $action == 'user' || $action == 'global_user' ) ? $user->lang['Look_up_User'] : $user->lang['Look_up_Group'],			
			'L_' . $l_type . '_TITLE' => $user->lang['User_admin'],
			'L_' . $l_type . '_EXPLAIN' => $user->lang['User_admin_explain']
		));	

		$this->_pafiledb();
		$pafiledb_cache->unload();
	}
}
?>