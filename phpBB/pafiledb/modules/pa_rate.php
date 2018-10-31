<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_rate.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
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
class pafiledb_rate extends pafiledb_public
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function main( $action  = false )
	{
		global $template, $user, $board_config, $phpEx, $pafiledb_config, $db, $userdata;
		global $phpbb_root_path, $pafiledb_user, $pafiledb_functions, $tplEx;
		global $mx_root_path, $module_root_path, $is_block, $mx_request_vars;

		// =======================================================
		// Request vars
		// =======================================================
		$file_id = $mx_request_vars->request('file_id', MX_TYPE_INT, '');

		if ( empty( $file_id ) )
		{
			mx_message_die( GENERAL_MESSAGE, $user->lang['File_not_exist'] );
		}

		$rating = $mx_request_vars->request('rating', MX_TYPE_INT, 0);

		$sql = 'SELECT file_name, file_catid
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

		if ( ( !$this->auth_user[$file_data['file_catid']]['auth_rate'] ) )
		{
			if ( !$userdata['session_logged_in'] )
			{
				// mx_redirect(mx_append_sid($mx_root_path . "login.$phpEx?redirect=".$this->this_mxurl("action=rate&file_id=" . $file_id), true));
			}

			$message = sprintf( $user->lang['Sorry_auth_rate'], $this->auth_user[$file_data['file_catid']]['auth_rate_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		$template->assign_vars( array(
			'L_INDEX' => "<<",
			'L_RATE' => $user->lang['Rate'],

			'U_INDEX' => mx_append_sid( $mx_root_path . 'index.' . $phpEx ),
			'U_DOWNLOAD_HOME' => mx_append_sid( $this->this_mxurl() ),
			'U_FILE_NAME' => mx_append_sid( $this->this_mxurl( 'action=file&file_id=' . $file_id ) ),

			'FILE_NAME' => $file_data['file_name'],
			'DOWNLOAD' => $pafiledb_config['module_name']
		));

		if ( isset( $_POST['submit'] ) )
		{
			$result_msg = str_replace( "{filename}", $file_data['file_name'], $user->lang['Rconf'] );

			$result_msg = str_replace( "{rate}", $rating, $result_msg );

			if ( ( $rating <= 0 ) or ( $rating > 10 ) )
			{
				mx_message_die( GENERAL_ERROR, 'Bad submited value' );
			}

			$this->update_voter_info( $file_id, $rating );

			$rate_info = $pafiledb_functions->get_rating( $file_id );

			$result_msg = str_replace( "{newrating}", $rate_info, $result_msg );

			$message = $result_msg . '<br /><br />' . sprintf( $user->lang['Click_return'], '<a href="' . mx_append_sid( $this->this_mxurl( 'action=file&file_id=' . $file_id ) ) . '">', '</a>' );
			mx_message_die( GENERAL_MESSAGE, $message );
		}
		else
		{
			$rate_info = str_replace( "{filename}", $file_data['file_name'], $user->lang['Rateinfo'] );

			$template->assign_vars( array(
				'S_RATE_ACTION' => mx_append_sid( $this->this_mxurl( 'action=rate&file_id=' . $file_id ) ),
				'L_RATE' => $user->lang['Rate'],
				'L_RERROR' => $user->lang['Rerror'],
				'L_R1' => $user->lang['R1'],
				'L_R2' => $user->lang['R2'],
				'L_R3' => $user->lang['R3'],
				'L_R4' => $user->lang['R4'],
				'L_R5' => $user->lang['R5'],
				'L_R6' => $user->lang['R6'],
				'L_R7' => $user->lang['R7'],
				'L_R8' => $user->lang['R8'],
				'L_R9' => $user->lang['R9'],
				'L_R10' => $user->lang['R10'],
				'RATEINFO' => $rate_info,
				'ID' => $file_id
			));
		}

		// ===================================================
		// assign var for navigation
		// ===================================================
		$this->generate_navigation( $file_data['file_catid'] );
		$this->display( $user->lang['Download'], 'pa_rate_body.'.$tplEx );
	}
}
?>