<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_license.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
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
class pafiledb_license extends pafiledb_public
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function main( $action  = false )
	{
		global $template, $user, $board_config, $phpEx, $pafiledb_config, $db, $images, $userdata;
		global $phpbb_root_path, $mx_root_path, $module_root_path, $is_block, $page_id, $tplEx;

		if ( isset( $_REQUEST['license_id'] ) )
		{
			$license_id = intval( $_REQUEST['license_id'] );
		}
		else
		{
			mx_message_die( GENERAL_MESSAGE, $user->lang['License_not_exist'] );
		}

		if ( isset( $_REQUEST['file_id'] ) )
		{
			$file_id = intval( $_REQUEST['file_id'] );
		}
		else
		{
			mx_message_die( GENERAL_MESSAGE, $user->lang['File_not_exist'] );
		}

		$sql = 'SELECT file_catid, file_name
			FROM ' . PA_FILES_TABLE . "
			WHERE file_id = $file_id";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query file info', '', __LINE__, __FILE__, $sql );
		}

		if ( !$file_data = $db->sql_fetchrow( $result ) )
		{
			mx_message_die( GENERAL_MESSAGE, $user->lang['File_not_exist'] );
		}

		$db->sql_freeresult( $result );

		if ( ( !$this->auth_user[$file_data['file_catid']]['auth_download'] ) )
		{
			if ( !$userdata['session_logged_in'] )
			{
				// mx_redirect(mx_append_sid($mx_root_path . 'login.'.$phpEx.'?redirect='.$this->this_mxurl('action=license&license_id=' . $license_id . '&file_id=' . $file_id), true));
			}

			$message = sprintf( $user->lang['Sorry_auth_download'], $this->auth_user[$file_data['file_catid']]['auth_download_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		$sql = 'SELECT *
			FROM ' . PA_LICENSE_TABLE . "
			WHERE license_id = $license_id";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query license info for this file', '', __LINE__, __FILE__, $sql );
		}

		if ( !$license = $db->sql_fetchrow( $result ) )
		{
			mx_message_die( GENERAL_MESSAGE, $user->lang['License_not_exist'] );
		}

		$db->sql_freeresult( $result );

		$template->assign_vars( array(
			'L_INDEX' => "<<",
			'L_LICENSE' => $user->lang['License'],
			'L_LEWARN' => $user->lang['Licensewarn'],
			'L_AGREE' => $user->lang['Iagree'],
			'L_NOT_AGREE' => $user->lang['Dontagree'],

			'U_INDEX' => mx_append_sid( $mx_root_path . 'index.' . $phpEx ),
			'U_DOWNLOAD_HOME' => mx_append_sid( $this->this_mxurl() ),
			'U_FILE_NAME' => mx_append_sid( $this->this_mxurl( 'action=file&file_id=' . $file_id ) ),
			'U_DOWNLOAD' => mx_append_sid( $this->this_mxurl( 'action=download&file_id=' . $file_id, 1 ) ),

			'LE_NAME' => $license['license_name'],
			'FILE_NAME' => $file_data['file_name'],
			'LE_TEXT' => nl2br( $license['license_text'] ),
			'DOWNLOAD' => $pafiledb_config['module_name']
		));

		// ===================================================
		// assign var for navigation
		// ===================================================
		$this->generate_navigation( $file_data['file_catid'] );

		$this->display( $user->lang['Download'], 'pa_license_body.'.$tplEx );
	}
}
?>