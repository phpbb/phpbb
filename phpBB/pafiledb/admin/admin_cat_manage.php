<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: admin_cat_manage.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

if ( !defined( 'IN_PORTAL' ) || !defined( 'IN_ADMIN' ) )
{
	die( "Hacking attempt" );
}

class pafiledb_cat_manage extends pafiledb_admin
{
	var $tpl_name;
	var $page_title;
	
	/**
	* Constructor
	* Init bbcode cache entries if bitfield is specified
	*/
	function pafiledb_cat_manage($u_action = '')
	{
		global $config, $phpbb_root_path;
		
		if ($u_action)
		{
			$this->u_action = $u_action;
		}		
	}

	function main( $action )
	{
		global $db, $template, $template, $user, $phpbb_admin_path, $phpEx, $auth;
		global $pafiledb_functions, $pafiledb_cache, $pafiledb_config, $phpbb_root_path, $phpbb_root_path, $phpbb_root_path, $mx_request_vars, $acp_pafiledb, $config;

		// Get action variable other wise set it to the main		
		$action = ( isset( $_REQUEST['action'] ) ) ? htmlspecialchars( $_REQUEST['action'] ) : '';
		$cat_id = ( isset( $_REQUEST['cat_id'] ) ) ? intval( $_REQUEST['cat_id'] ) : 0;
		$cat_id_other = ( isset( $_REQUEST['cat_id_other'] ) ) ? intval( $_REQUEST['cat_id_other'] ) : 0;
		
		$action = isset($_REQUEST['addcategory']) ? 'do_add' : $action;

		if ($action == 'do_add' && !$cat_id)
		{
			$new_cat_id = $this->update_add_cat();
			$action = 'add';
			if ( !sizeof( $this->error ) )
			{
				$this->_pafiledb();
				
				$acl_url = "&amp;mode=cat_manage&amp;cat_id=$new_cat_id";
				
				$message = $user->lang['Catadded'] . '<br /><br />' . sprintf( $user->lang['Click_return'], '<a href="' . $this->u_action . '">', '</a>' ) . '<br /><br />' . sprintf( $user->lang['Click_edit_permissions'], '<a href="' . $this->u_action ."&amp;cat_id=$new_cat_id" . '">', '</a>' );

				// redirect directly to permission settings screen if authed
				if ($action == 'add' && !$forum_perm_from && $auth->acl_get('a_pafiledb'))
				{
					meta_refresh(4, append_sid("{$phpbb_admin_path}index.$phpEx", 'i=pafiledb' . $acl_url));
				}

				trigger_error($message . adm_back_link($this->u_action . $acl_url));				
				
			}
			$action = 'add';
		}
		elseif ( $action == 'do_add' && $cat_id )
		{
			$new_cat_id = $this->update_add_cat( $cat_id );
			if ( !sizeof( $this->error ) )
			{
				$this->_pafiledb();
				$message = $user->lang['Catedited'] . '<br /><br />' . sprintf( $user->lang['Click_return'], '<a href="' . $this->u_action . '">', '</a>' ) . '<br /><br />' . sprintf( $user->lang['Click_edit_permissions'], '<a href="' . $this->u_action."&amp;cat_id=$new_cat_id" . '">', '</a>' );
				trigger_error($message . adm_back_link($this->u_action));
			}
			$action = 'edit';
		}
		elseif ( $action == 'do_delete' )
		{
			$this->delete_cat( $cat_id );
			if ( !sizeof( $this->error ) )
			{
				$this->_pafiledb();
				$message = $user->lang['Catsdeleted'] . '<br /><br />' . sprintf( $user->lang['Click_return'], '<a href="' . $this->u_action . '">', '</a>' ) . '<br /><br />' . sprintf( $user->lang['Click_return_admin_index'], '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx"). '">', '</a>' );
				trigger_error($message . adm_back_link($this->u_action));
			}
		}
		elseif ( $action == 'cat_order' )
		{
			$this->order_cat( $cat_id_other );
		}
		elseif ( $action == 'sync' )
		{
			$this->sync( $cat_id_other );
		}
		elseif ( $action == 'sync_all' )
		{
			$this->sync_all();
		}

		switch ( $action )
		{
			case '':
			case 'cat_order':
			case 'sync':
			default:
				$template_file = 'acp_pafiledb_cat_manage.html';
				$this->tpl_name = 'acp_pafiledb_cat_manage';
				$l_title = $user->lang['Panel_cat_title'];
				$this->page_title = $l_title;
				$l_explain = $user->lang['Panel_cat_explain'];
				$s_hidden_fields = '<input type="hidden" name="action" value="add">';
				break;
			case 'add':
				$template_file = 'acp_pafiledb_cat_edit.html';
				$this->tpl_name = 'acp_pafiledb_cat_edit';
				$l_title = $user->lang['Acattitle'];
				$this->page_title = $l_title;				
				$l_explain = $user->lang['Catexplain'];
				$s_hidden_fields = '<input type="hidden" name="action" value="do_add">';
				break;
			case 'edit':
				$template_file = 'acp_pafiledb_cat_edit.html';
				$this->tpl_name = 'acp_pafiledb_cat_edit';				
				$l_title = $user->lang['Ecattitle'];
				$this->page_title = $l_title;				
				$l_explain = $user->lang['Catexplain'];
				$s_hidden_fields = '<input type="hidden" name="action" value="do_add">';
				$s_hidden_fields .= '<input type="hidden" name="cat_id" value="' . $cat_id . '">';
				break;
			case 'delete':
				$template_file = 'acp_pafiledb_cat_delete.html';
				$this->tpl_name = 'acp_pafiledb_cat_delete';				
				$l_title = $user->lang['Dcattitle'];
				$this->page_title = $l_title;				
				$l_explain = $user->lang['Catexplain'];
				$s_hidden_fields = '<input type="hidden" name="action" value="do_delete">';
				break;
		}

		$template->set_filenames( array( 'body' => $template_file ) );
		
		if ( sizeof( $this->error ) ) $template->assign_block_vars( 'pafiledb_error', array() );

		$template->assign_vars( array(
			'L_CAT_TITLE' => $l_title,
			'L_CAT_EXPLAIN' => $l_explain,

			'ERROR' => ( sizeof( $this->error ) ) ? implode( '<br />', $this->error ) : '',
			'S_HIDDEN_FIELDS' => $s_hidden_fields,
			'U_ACTION' => $this->u_action,
		));

		if ( $action == '' || $action == 'cat_order' || $action == 'sync' || $action == 'sync_all' )
		{
			$template->assign_vars( array(
				'L_CREATE_CATEGORY' => $user->lang['Create_category'],
				'L_EDIT' => $user->lang['Edit'],
				'L_DELETE' => $user->lang['Delete'],
				'L_MOVE_UP' => $user->lang['Move_up'],
				'L_MOVE_DOWN' => $user->lang['Move_down'],
				'L_SUB_CAT' => $user->lang['Sub_category'],
				'L_RESYNC' => $user->lang['Resync']
			));
			$this->admin_cat_main( $cat_id );
		}
		elseif ( $action == 'add' || $action == 'edit' )
		{
			if ( $action == 'add' )
			{
				if ( !$_POST['cat_parent'] )
				{
					$cat_list .= '<option value="0" selected>' . $user->lang['None'] . '</option>';
				}
				else
				{
					$cat_list .= '<option value="0">' . $user->lang['None'] . '</option>';
				}

				$cat_list .= ( !$_POST['cat_parent'] ) ? $this->generate_jumpbox() : $this->generate_jumpbox( 0, 0, array( $_POST['cat_parent'] => 1 ) );
				$checked_yes = ( $_POST['cat_allow_file'] ) ? ' checked' : '';
				$checked_no = ( !$_POST['cat_allow_file'] ) ? ' checked' : '';
				$cat_name = ( !empty( $_POST['cat_name'] ) ) ? $_POST['cat_name'] : '';
				$cat_desc = ( !empty( $_POST['cat_desc'] ) ) ? $_POST['cat_desc'] : '';

				//
				// Comments
				//
				$use_comments_yes = "";
				$use_comments_no = "";
				$use_comments_default = "checked=\"checked\"";

				$internal_comments_internal = "";
				$internal_comments_phpbb = "";
				$internal_comments_default = "checked=\"checked\"";

				$autogenerate_comments_yes = "";
				$autogenerate_comments_no = "";
				$autogenerate_comments_default = "checked=\"checked\"";

				$comments_forum_id = -1;

				//
				// Ratings
				//
				$use_ratings_yes = "";
				$use_ratings_no = "";
				$use_ratings_default = "checked=\"checked\"";

				//
				// Instructions
				//
				$pretext_show = "";
				$pretext_hide = "";
				$pretext_default = "checked=\"checked\"";

				//
				// Notification
				//
				$notify_none = "";
				$notify_pm = "";
				$notify_email = "";
				$notify_default = "checked=\"checked\"";

				$notify_group_list = mx_get_groups('', 'notify_group');
			}
			else
			{
				if ( !$this->cat_rowset[$cat_id]['cat_parent'] )
				{
					$cat_list .= '<option value="0" selected>' . $user->lang['None'] . '</option>\n';
				}
				else
				{
					$cat_list .= '<option value="0">' . $user->lang['None'] . '</option>\n';
				}
				$cat_list .= $this->generate_jumpbox( 0, 0, array( $this->cat_rowset[$cat_id]['cat_parent'] => 1 ) );

				if ( $this->cat_rowset[$cat_id]['cat_allow_file'] )
				{
					$checked_yes = ' checked';
					$checked_no = '';
				}
				else
				{
					$checked_yes = '';
					$checked_no = ' checked';
				}

				$cat_name = $this->cat_rowset[$cat_id]['cat_name'];
				$cat_desc = $this->cat_rowset[$cat_id]['cat_desc'];

				//
				// Comments
				//
				$use_comments_yes = ( $this->cat_rowset[$cat_id]['cat_allow_comments'] == 1 ) ? "checked=\"checked\"" : "";
				$use_comments_no = ( $this->cat_rowset[$cat_id]['cat_allow_comments'] == 0 ) ? "checked=\"checked\"" : "";
				$use_comments_default = ( $this->cat_rowset[$cat_id]['cat_allow_comments'] == -1 ) ? "checked=\"checked\"" : "";

				$internal_comments_internal = ( $this->cat_rowset[$cat_id]['internal_comments'] == 1 ) ? "checked=\"checked\"" : "";
				$internal_comments_phpbb = ( $this->cat_rowset[$cat_id]['internal_comments'] == 0 ) ? "checked=\"checked\"" : "";
				$internal_comments_default = ( $this->cat_rowset[$cat_id]['internal_comments'] == -1 ) ? "checked=\"checked\"" : "";

				$comments_forum_id = $this->cat_rowset[$cat_id]['comments_forum_id'];

				$autogenerate_comments_yes = ( $this->cat_rowset[$cat_id]['autogenerate_comments'] == 1 ) ? "checked=\"checked\"" : "";
				$autogenerate_comments_no = ( $this->cat_rowset[$cat_id]['autogenerate_comments'] == 0 ) ? "checked=\"checked\"" : "";
				$autogenerate_comments_default = ( $this->cat_rowset[$cat_id]['autogenerate_comments'] == -1 ) ? "checked=\"checked\"" : "";

				//
				// Ratings
				//
				$use_ratings_yes = ( $this->cat_rowset[$cat_id]['cat_allow_ratings'] == 1 ) ? "checked=\"checked\"" : "";
				$use_ratings_no = ( $this->cat_rowset[$cat_id]['cat_allow_ratings'] == 0 ) ? "checked=\"checked\"" : "";
				$use_ratings_default = ( $this->cat_rowset[$cat_id]['cat_allow_ratings'] == -1 ) ? "checked=\"checked\"" : "";

				//
				// Instructions
				//
				$pretext_show = ( $this->cat_rowset[$cat_id]['show_pretext'] == 1 ) ? "checked=\"checked\"" : "";
				$pretext_hide = ( $this->cat_rowset[$cat_id]['show_pretext'] == 0 ) ? "checked=\"checked\"" : "";
				$pretext_default = ( $this->cat_rowset[$cat_id]['show_pretext'] == -1 ) ? "checked=\"checked\"" : "";

				//
				// Notification
				//
				$notify_none = ( $this->cat_rowset[$cat_id]['notify'] == 0 ) ? "checked=\"checked\"" : "";
				$notify_pm = ( $this->cat_rowset[$cat_id]['notify'] == 1 ) ? "checked=\"checked\"" : "";
				$notify_email = ( $this->cat_rowset[$cat_id]['notify'] == 2 ) ? "checked=\"checked\"" : "";
				$notify_default = ( $this->cat_rowset[$cat_id]['notify'] == -1 ) ? "checked=\"checked\"" : "";

				$notify_group_list = mx_get_groups($this->cat_rowset[$cat_id]['notify_group'], 'notify_group');
			}

			$template->assign_vars( array(
				'CAT_NAME' => $cat_name,
				'CAT_DESC' => $cat_desc,
				'CHECKED_YES' => $checked_yes,
				'CHECKED_NO' => $checked_no,

				//
				// Comments
				//
				'L_COMMENTS_TITLE' => $user->lang['Comments_title'],

				'L_USE_COMMENTS' => $user->lang['Use_comments'],
				'L_USE_COMMENTS_EXPLAIN' => $user->lang['Use_comments_explain'],
				'S_USE_COMMENTS_YES' => $use_comments_yes,
				'S_USE_COMMENTS_NO' => $use_comments_no,
				'S_USE_COMMENTS_DEFAULT' => $use_comments_default,

				'L_INTERNAL_COMMENTS' => $user->lang['Internal_comments'],
				'L_INTERNAL_COMMENTS_EXPLAIN' => $user->lang['Internal_comments_explain'],
				'S_INTERNAL_COMMENTS_INTERNAL' => $internal_comments_internal,
				'S_INTERNAL_COMMENTS_PHPBB' => $internal_comments_phpbb,
				'S_INTERNAL_COMMENTS_DEFAULT' => $internal_comments_default,
				'L_INTERNAL_COMMENTS_INTERNAL' => $user->lang['Internal_comments_internal'],
				'L_INTERNAL_COMMENTS_PHPBB' => $user->lang['Internal_comments_phpBB'],

				'L_FORUM_ID' => $user->lang['Forum_id'],
				'L_FORUM_ID_EXPLAIN' => $user->lang['Forum_id_explain'],
				'FORUM_LIST' => $config['portal_backend'] != 'internal' ? $this->get_forums( $comments_forum_id, true, 'comments_forum_id' ) : 'not available',
				//'FORUM_LIST' => $this->get_forums( $comments_forum_id, true, 'comments_forum_id' ),

				'L_AUTOGENERATE_COMMENTS' => $user->lang['Autogenerate_comments'],
				'L_AUTOGENERATE_COMMENTS_EXPLAIN' => $user->lang['Autogenerate_comments_explain'],
				'S_AUTOGENERATE_COMMENTS_YES' => $autogenerate_comments_yes,
				'S_AUTOGENERATE_COMMENTS_NO' => $autogenerate_comments_no,
				'S_AUTOGENERATE_COMMENTS_DEFAULT' => $autogenerate_comments_default,

				//
				// Ratings
				//
				'L_RATINGS_TITLE' => $user->lang['Ratings_title'],

				'L_USE_RATINGS' => $user->lang['Use_ratings'],
				'L_USE_RATINGS_EXPLAIN' => $user->lang['Use_ratings_explain'],
				'S_USE_RATINGS_YES' => $use_ratings_yes,
				'S_USE_RATINGS_NO' => $use_ratings_no,
				'S_USE_RATINGS_DEFAULT' => $use_ratings_default,

				//
				// Instructions
				//
				'L_INSTRUCTIONS_TITLE' => $user->lang['Instructions_title'],

				'L_PRE_TEXT_NAME' => $user->lang['Pre_text_name'],
				'L_PRE_TEXT_EXPLAIN' => $user->lang['Pre_text_explain'],
				'S_SHOW_PRETEXT' => $pretext_show,
				'S_HIDE_PRETEXT' => $pretext_hide,
				'S_DEFAULT_PRETEXT' => $pretext_default,

				'L_SHOW' => $user->lang['Show'],
				'L_HIDE' => $user->lang['Hide'],

				//
				// Notifications
				//
				'L_NOTIFICATIONS_TITLE' => $user->lang['Notifications_title'],

				'L_NOTIFY' => $user->lang['Notify'],
				'L_NOTIFY_EXPLAIN' => $user->lang['Notify_explain'],
				'L_EMAIL' => $user->lang['Email'],
				'L_PM' => $user->lang['PM'],

				'S_NOTIFY_NONE' => $notify_none,
				'S_NOTIFY_EMAIL' => $notify_email,
				'S_NOTIFY_PM' => $notify_pm,
				'S_NOTIFY_DEFAULT' => $notify_default,

				'L_NOTIFY_GROUP' => $user->lang['Notify_group'],
				'L_NOTIFY_GROUP_EXPLAIN' => $user->lang['Notify_group_explain'],
				'NOTIFY_GROUP' => $notify_group_list,

				'L_CAT_NAME' => $user->lang['Catname'],
				'L_CAT_NAME_INFO' => $user->lang['Catnameinfo'],
				'L_CAT_DESC' => $user->lang['Catdesc'],
				'L_CAT_DESC_INFO' => $user->lang['Catdescinfo'],
				'L_CAT_PARENT' => $user->lang['Catparent'],
				'L_CAT_PARENT_INFO' => $user->lang['Catparentinfo'],
				'L_CAT_ALLOWFILE' => $user->lang['Allow_file'],
				'L_CAT_ALLOWFILE_INFO' => $user->lang['Allow_file_info'],
				'L_CAT_ALLOWCOMMENTS' => $user->lang['Allow_comments'],
				'L_CAT_ALLOWCOMMENTS_INFO' => $user->lang['Allow_comments_info'],
				'L_CAT_ALLOWRATINGS' => $user->lang['Allow_ratings'],
				'L_CAT_ALLOWRATINGS_INFO' => $user->lang['Allow_ratings_info'],

				'L_DEFAULT' => $user->lang['Use_default'],
				'L_NONE' => $user->lang['None'],
				'L_YES' => $user->lang['Yes'],
				'L_NO' => $user->lang['No'],
				'L_CAT_NAME_FIELD_EMPTY' => $user->lang['Cat_name_missing'],
				'S_CAT_LIST' => $cat_list )
			);
		}
		elseif ( $action == 'delete' )
		{
			$select_cat = $this->generate_jumpbox( 0, 0, array( $cat_id => 1 ) );
			$file_to_select_cat = $this->generate_jumpbox( 0, 0, '', true );

			$template->assign_vars( array(
				'S_SELECT_CAT' => $select_cat,
				'S_FILE_SELECT_CAT' => $file_to_select_cat,

				'L_DELETE' => $user->lang['Delete'],
				'L_DO_FILE' => $user->lang['Delfiles'],
				'L_DO_CAT' => $user->lang['Do_cat'],
				'L_MOVE_TO' => $user->lang['Move_to'],
				'L_SELECT_CAT' => $user->lang['Select_a_Category'],
				'L_DELETE' => $user->lang['Delete'],
				'L_MOVE' => $user->lang['Move']
			));
		}

		//$template->pparse( 'admin' );
		
		$this->_pafiledb();
		$pafiledb_cache->unload();
				
	}
}
?>