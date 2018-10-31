<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_user_upload.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
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
class pafiledb_user_upload extends pafiledb_public
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function main( $action  = false )
	{
		global $pafiledb_config, $board_config, $phpbb_root_path, $tplEx;
		global $template, $db, $user, $userdata, $user_ip, $phpEx, $pafiledb_functions;
		global $mx_root_path, $module_root_path, $is_block, $mx_request_vars, $mx_block;

		//
		// Go full page
		//
		$mx_block->full_page = true;

		// =======================================================
		// Request vars
		// =======================================================
		$cat_id = $mx_request_vars->request('cat_id', MX_TYPE_INT, 0);
		$file_id = $mx_request_vars->request('file_id', MX_TYPE_INT, 0);

		$do = ( isset( $_REQUEST['do'] ) ) ? intval( $_REQUEST['do'] ) : '';
		$mirrors = ( isset( $_POST['mirrors'] ) ) ? true : 0;

		//
		// Main Auth
		//
		if ( !empty( $cat_id ) )
		{
			if ( !$this->auth_user[$cat_id]['auth_upload'] )
			{
				$message = sprintf( $user->lang['Sorry_auth_upload'], $this->auth_user[$cat_id]['auth_upload_type'] );
			}
		}
		else
		{
			$dropmenu = ( !$cat_id ) ? $this->generate_jumpbox( 0, 0, '', true, true, 'auth_upload' ) : $this->generate_jumpbox( 0, 0, array( $cat_id => 1 ), true, true, 'auth_upload' );

			if ( empty( $dropmenu ) )
			{
				$message = sprintf( $user->lang['Sorry_auth_upload'], $this->auth_user[$cat_id]['auth_upload_type'] );
			}
		}

		//
		// Not authorized? Output nice message and die.
		//
		if (!empty($message))
		{
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		//
		// Load file info...if file_id is set
		//
		if ( $file_id )
		{
			$sql = 'SELECT *
				FROM ' . PA_FILES_TABLE . "
				WHERE file_id = '".$file_id."'";

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt query File data', '', __LINE__, __FILE__, $sql );
			}

			$file_data = $db->sql_fetchrow( $result );
			$cat_id = $file_data['file_catid'];

			$db->sql_freeresult( $result );
		}

		//
		// Further security.
		// Reset vars if no related data exist.
		//
		if ( $file_id && !$cat_id )
		{
			$file_id = 0;
		}

		if ( $cat_id && !$this->cat_rowset[$cat_id]['cat_id'] )
		{
			$cat_id = 0;
		}

		//
		// Load custom fields
		//
		$custom_field = new custom_field();
		$custom_field->init();

		// =======================================================
		// Delete
		// =======================================================
		if ( $do == 'delete' && $file_id )
		{
			if ( ( $this->auth_user[$cat_id]['auth_delete_file'] && $file_data['user_id'] == $userdata['user_id'] ) || $this->auth_user[$cat_id]['auth_mod'] )
			{
				//
				// Notification
				//
				$this->update_add_item_notify($file_id, 'delete');

				//
				// Comments
				//
				if ($this->comments[$cat_id]['activated'] && $pafiledb_config['del_topic'])
				{
					if ( $this->comments[$cat_id]['internal_comments'] )
					{
						$sql = 'DELETE FROM ' . PA_COMMENTS_TABLE . "
						WHERE file_id = '" . $file_id . "'";

						if ( !( $db->sql_query( $sql ) ) )
						{
							mx_message_die( GENERAL_ERROR, 'Couldnt delete comments', '', __LINE__, __FILE__, $sql );
						}
					}
					else
					{
						if ( $file_data['topic_id'] )
						{
							include( $module_root_path . 'pafiledb/includes/functions_comment.' . $phpEx );
							$mx_pa_comments = new pafiledb_comments();
							$mx_pa_comments->init( $file_data, 'phpbb');
							$mx_pa_comments->post('delete_all', $file_data['topic_id']);
						}
					}
				}

				$this->delete_items( $file_id );
				$this->_pafiledb();
				$message = $user->lang['Filedeleted'] . '<br /><br />' . sprintf( $user->lang['Click_return'], '<a href="' . mx_append_sid( $this->this_mxurl( "action=category&cat_id=" . $cat_id ) ) . '">', '</a>' );
				mx_message_die( GENERAL_MESSAGE, $message );
			}
			else
			{
				$message = sprintf( $user->lang['Sorry_auth_delete'], $this->auth_user[$cat_id]['auth_delete_type'] );
				mx_message_die( GENERAL_MESSAGE, $message );
			}
		}

		// =======================================================
		// IF submit then upload the file and update the sql for it
		// =======================================================
		if ( isset( $_POST['submit'] ) && $cat_id )
		{
			if ( !$file_id )
			{
				if ( $this->auth_user[$cat_id]['auth_upload'] || $this->auth_user[$cat_id]['auth_mod'] )
				{
					$pa_post_mode = 'add';

					$file_id = $this->update_add_item();
					$custom_field->file_update_data( $file_id );

					if ( $this->auth_user[$cat_id]['auth_approval'] || $this->auth_user[$cat_id]['auth_mod'] )
					{
						$message = $user->lang['Fileadded'] . '<br /><br />' . sprintf( $user->lang['Click_return'], '<a href="' . mx_append_sid( $this->this_mxurl( "action=file&file_id=" . $file_id ) ) . '">', '</a>' );
					}
					else
					{
						$message = $user->lang['Fileadded_not_validated'] . '<br /><br />' . sprintf( $user->lang['Click_return'], '<a href="' . mx_append_sid( $this->this_mxurl( "action=category&cat_id=" . $cat_id ) ) . '">', '</a>' );
					}

					$this->_pafiledb();
				}
				else
				{
					$message = sprintf( $user->lang['Sorry_auth_upload'], $this->auth_user[$cat_id]['auth_upload_type'] );
				}
			}
			else
			{
				if ( ($this->auth_user[$cat_id]['auth_edit_file'] && $file_data['user_id'] == $userdata['user_id'] ) || $this->auth_user[$cat_id]['auth_mod'] )
				{
					$pa_post_mode = 'edit';

					$file_id = $this->update_add_item( $file_id );
					$custom_field->file_update_data( $file_id );

					if ( $this->auth_user[$cat_id]['auth_approval_edit'] || $this->auth_user[$cat_id]['auth_mod'] )
					{
						$message = $user->lang['Fileedited'] . '<br /><br />' . sprintf( $user->lang['Click_return'], '<a href="' . mx_append_sid( $this->this_mxurl( "action=file&file_id=" . $file_id ) ) . '">', '</a>' );
					}
					else
					{
						$message = $user->lang['Fileedited_not_validated'] . '<br /><br />' . sprintf( $user->lang['Click_return'], '<a href="' . mx_append_sid( $this->this_mxurl( "action=category&cat_id=" . $cat_id ) ) . '">', '</a>' );

					}

					$this->_pafiledb();
				}
				else
				{
					$message = sprintf( $user->lang['Sorry_auth_edit'], $this->auth_user[$cat_id]['auth_edit_type'] );
				}
			}

			//
			// Notification
			//
			$this->update_add_item_notify($file_id, $pa_post_mode);

			//
			// Auto comment
			//
			if ( $this->comments[$cat_id]['activated'] && $this->comments[$cat_id]['autogenerate_comments'] )
			{
				//
				// Autogenerate comment (duplicate the notification message)
				//
				$mx_pa_notification = new mx_pa_notification();
				$mx_pa_notification->init( $file_id, $pafiledb_config['allow_comment_wysiwyg'] );
				$mx_pa_notification->_compose_auto_note($pa_post_mode == 'add' ? MX_NEW_NOTIFICATION : MX_EDITED_NOTIFICATION);

				//
				// Generate comment
				//
				$this->update_add_comment('', $file_id, 0, addslashes(trim($mx_pa_notification->topic_title)), addslashes(trim($mx_pa_notification->message)),true,false,false,true);
			}

			mx_message_die( GENERAL_MESSAGE, $message );
		}
		else
		// =======================================================
		// IF not submit then load data MAIN form
		// =======================================================
		{
			if ( !$file_id )
			{
				$file_name = '';
				$file_desc = '';
				$file_long_desc = '';
				$file_author = '';
				$file_version = '';
				$file_website = '';
				$file_posticons = $pafiledb_functions->post_icons();
				$file_cat_list = ( !$cat_id ) ? $this->generate_jumpbox( 0, 0, '', true ) : $this->generate_jumpbox( 0, 0, array( $cat_id => 1 ), true, true );
				$file_license = $pafiledb_functions->license_list();
				$pin_checked_yes = '';
				$pin_checked_no = ' checked';
				$disable_checked_yes = '';
				$disable_checked_no = ' checked';
				$disable_msg = 'The file is unavailable at the moment!';
				$file_download = 0;
				$approved_checked_yes = '';
				$approved_checked_no = ' checked';
				$file_ssurl = '';
				$ss_checked_yes = '';
				$ss_checked_no = ' checked';
				$file_url = '';
				$custom_exist = $custom_field->display_edit();
				$mode = 'ADD';
				$l_title = $user->lang['Afiletitle'];
			}
			else
			{
				//
				// AUTH CHECK
				//
				if ( ( $this->auth_user[$cat_id]['auth_edit_file'] && $file_data['user_id'] == $userdata['user_id'] ) || $this->auth_user[$cat_id]['auth_mod'] )
				{
					$file_name = $file_data['file_name'];
					$file_desc = $file_data['file_desc'];
					$file_long_desc = $file_data['file_longdesc'];
					$file_author = $file_data['file_creator'];
					$file_version = $file_data['file_version'];
					$file_website = $file_data['file_docsurl'];
					$file_posticons = $pafiledb_functions->post_icons( $file_data['file_posticon'] );
					$file_cat_list = $this->generate_jumpbox( 0, 0, array( $cat_id => 1 ), true );
					$file_license = $pafiledb_functions->license_list( $file_data['file_license'] );
					$pin_checked_yes = ( $file_data['file_pin'] ) ? ' checked' : '';
					$pin_checked_no = ( !$file_data['file_pin'] ) ? ' checked' : '';
					$disable_checked_yes = ( $file_data['file_disable'] ) ? ' checked' : '';
					$disable_checked_no = ( !$file_data['file_disable'] ) ? ' checked' : '';
					$disable_msg = $file_data['disable_msg'];
					$file_download = intval( $file_data['file_dls'] );
					$approved_checked_yes = ( $file_data['file_approved'] ) ? ' checked' : '';
					$approved_checked_no = ( !$file_data['file_approved'] ) ? ' checked' : '';
					$file_approved = ( $file_data['file_approved'] == '1' ) ? 1 : 0;
					$file_ssurl = $file_data['file_ssurl'];
					$ss_checked_yes = ( $file_data['file_sshot_link'] ) ? ' checked' : '';
					$ss_checked_no = ( !$file_data['file_sshot_link'] ) ? ' checked' : '';
					$file_url = $file_data['file_dlurl'];
					$file_unique_name = $file_data['unique_name'];
					$file_dir = $file_data['file_dir'];
					$custom_exist = $custom_field->display_edit( $file_id );
					$mode = 'EDIT';
					$l_title = $user->lang['Efiletitle'];

					$s_hidden_fields = '<input type="hidden" name="file_id" value="' . $file_id . '">';
				}
				else
				{
					$message = sprintf( $user->lang['Sorry_auth_edit'], $this->auth_user[$cat_id]['auth_edit_type'] );
					mx_message_die( GENERAL_MESSAGE, $message );
				}
			}

			$s_hidden_fields .= '<input type="hidden" name="action" value="user_upload">';

			$template->assign_vars( array(
				'S_ADD_FILE_ACTION' => mx_append_sid( $this->this_mxurl() ),

				'DOWNLOAD' => $pafiledb_config['module_name'],
				'FILESIZE' => intval( $pafiledb_config['max_file_size'] ),
				'FILE_NAME' => $file_name,
				'FILE_DESC' => $file_desc,
				'FILE_LONG_DESC' => $file_long_desc,
				'FILE_AUTHOR' => $file_author,
				'FILE_VERSION' => $file_version,
				'FILE_SSURL' => $file_ssurl,
				'FILE_WEBSITE' => $file_website,
				'FILE_DLURL' => $file_url,
				'FILE_DOWNLOAD' => $file_download,
				'CUSTOM_EXIST' => $custom_exist,
				'AUTH_APPROVAL' => false,
				'APPROVED_CHECKED_YES' => $approved_checked_yes,
				'APPROVED_CHECKED_NO' => $approved_checked_no,
				'SS_CHECKED_YES' => $ss_checked_yes,
				'SS_CHECKED_NO' => $ss_checked_no,
				'PIN_CHECKED_YES' => $pin_checked_yes,
				'PIN_CHECKED_NO' => $pin_checked_no,
				'DISABLE_CHECKED_YES' => $disable_checked_yes,
				'DISABLE_CHECKED_NO' => $disable_checked_no,
				'DISABLE_MSG' => $disable_msg,

				'L_UPLOAD' => $user->lang['User_upload'],
				'L_FILE_TITLE' => $l_title,
				'L_FILE_APPROVED' => $user->lang['Approved'],
				'L_FILE_APPROVED_INFO' => $user->lang['Approved_info'],
				'L_ADDTIONAL_FIELD' => $user->lang['Addtional_field'],
				'L_SCREENSHOT' => $user->lang['Scrsht'],
				'L_FILES' => $user->lang['Files'],
				'L_FILE_NAME' => $user->lang['Filename'],
				'L_FILE_NAME_INFO' => $user->lang['Filenameinfo'],
				'L_FILE_SHORT_DESC' => $user->lang['Filesd'],
				'L_FILE_SHORT_DESC_INFO' => $user->lang['Filesdinfo'],
				'L_FILE_LONG_DESC' => $user->lang['Fileld'],
				'L_FILE_LONG_DESC_INFO' => $user->lang['Fileldinfo'],
				'L_FILE_AUTHOR' => $user->lang['Filecreator'],
				'L_FILE_AUTHOR_INFO' => $user->lang['Filecreatorinfo'],
				'L_FILE_VERSION' => $user->lang['Fileversion'],
				'L_FILE_VERSION_INFO' => $user->lang['Fileversioninfo'],
				'L_FILESS' => $user->lang['Filess'],
				'L_FILESSINFO' => $user->lang['Filessinfo'],
				'L_FILESS_UPLOAD' => $user->lang['Filess_upload'],
				'L_FILESSINFO_UPLOAD' => $user->lang['Filessinfo_upload'],
				'L_FILE_SSLINK' => $user->lang['Filess_link'],
				'L_FILE_SSLINK_INFO' => $user->lang['Filess_link_info'],
				'L_FILESSUPLOAD' => $user->lang['Filessupload'],
				'L_FILE_WEBSITE' => $user->lang['Filedocs'],
				'L_FILE_WEBSITE_INFO' => $user->lang['Filedocsinfo'],
				'L_FILE_URL' => $user->lang['Fileurl'],
				'L_FILE_UPLOAD' => $user->lang['File_upload'],
				'L_FILEINFO_UPLOAD' => $user->lang['Fileinfo_upload'],
				'L_FILE_URL_INFO' => $user->lang['Fileurlinfo'],
				'L_FILE_POSTICONS' => $user->lang['Filepi'],
				'L_FILE_POSTICONS_INFO' => $user->lang['Filepiinfo'],
				'L_FILE_CAT' => $user->lang['Filecat'],
				'L_FILE_CAT_INFO' => $user->lang['Filecatinfo'],
				'L_FILE_LICENSE' => $user->lang['Filelicense'],
				'L_NONE' => $user->lang['None'],
				'L_FILE_LICENSE_INFO' => $user->lang['Filelicenseinfo'],
				'L_FILE_PINNED' => $user->lang['Filepin'],
				'L_FILE_PINNED_INFO' => $user->lang['Filepininfo'],
				'L_FILE_DISABLE' => $user->lang['Filedisable'],
				'L_FILE_DISABLE_INFO' => $user->lang['Filedisableinfo'],
				'L_FILE_DISABLE_MSG' => $user->lang['Filedisablemsg'],
				'L_FILE_DISABLE_MSG_INFO' => $user->lang['Filedisablemsginfo'],
				'L_FILE_DOWNLOAD' => $user->lang['Filedls'],
				'L_NO' => $user->lang['No'],
				'L_YES' => $user->lang['Yes'],

				'S_POSTICONS' => $file_posticons,
				'S_LICENSE_LIST' => $file_license,
				'S_CAT_LIST' => $file_cat_list,
				'S_HIDDEN_FIELDS' => $s_hidden_fields,
				'MODE' => $mode,
				'U_DOWNLOAD' => mx_append_sid( $this->this_mxurl() )
			));

			$this->display( $user->lang['Download'], 'pa_file_add.'.$tplEx );
		}
	}
}
?>