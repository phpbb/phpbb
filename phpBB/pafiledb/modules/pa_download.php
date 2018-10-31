<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_download.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
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
class pafiledb_download extends pafiledb_public
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function main( $action  = false )
	{
		global $user, $db, $pafiledb_user, $pafiledb_config, $board_config, $phpEx, $userdata, $tplEx;
		global $phpbb_root_path, $pafiledb_functions;
		global $mx_script_name, $module_root_path;

		// **********************************************************************
		// Read language definition
		// **********************************************************************
		if ( !MXBB_MODULE )
		{
			$user->add_lang('mods/pafiledb_main');
		}
		else
		{
			if ( !file_exists( $module_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.' . $phpEx ) )
			{
				include( $module_root_path . 'language/lang_english/lang_main.' . $phpEx );
			}
			else
			{
				include( $module_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.' . $phpEx );
			}
		}

		if ( isset( $_REQUEST['file_id'] ) )
		{
			$file_id = intval( $_REQUEST['file_id'] );
		}
		else
		{
			mx_message_die( GENERAL_MESSAGE, $user->lang['File_not_exist'] );
		}

		$mirror_id = ( isset( $_REQUEST['mirror_id'] ) ) ? intval( $_REQUEST['mirror_id'] ) : false;

		$sql = 'SELECT *
			FROM ' . PA_FILES_TABLE . " AS f
			WHERE f.file_id = $file_id";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt select download', '', __LINE__, __FILE__, $sql );
		}
		// =========================================================================
		// Id doesn't match with any file in the database another nice error message
		// =========================================================================
		if ( !$file_data = $db->sql_fetchrow( $result ) )
		{
			mx_message_die( GENERAL_MESSAGE, $user->lang['File_not_exist'] );
		}

		$db->sql_freeresult( $result );
		// =========================================================================
		// Check if the user is authorized to download the file
		// =========================================================================
		if ( ( !$this->auth_user[$file_data['file_catid']]['auth_download'] ) )
		{
			if ( !$userdata['session_logged_in'] )
			{
				// mx_redirect(mx_append_sid($mx_root_path . "login.$phpEx?redirect=".$this->this_mxurl("action=download&file_id=" . $file_id), true));
			}

			$message = sprintf( $user->lang['Sorry_auth_download'], $this->auth_user[$file_data['file_catid']]['auth_download_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}
		// =========================================================================
		// Check for hot links
		// Borrowed from Smartor Album mod, thanks Smartor
		// =========================================================================
		$url_referer = trim( getenv( 'HTTP_REFERER' ) );
		if ( $url_referer == '' )
		{
			$url_referer = trim( $_SERVER['HTTP_REFERER'] );
		}

		if ( ( $pafiledb_config['hotlink_prevent'] ) and ( !empty( $url_referer ) ) )
		{
			$check_referer = explode( '?', $url_referer );
			$check_referer = trim( $check_referer[0] );

			$good_referers = array();

			if ( $pafiledb_config['hotlink_allowed'] != '' )
			{
				$good_referers = explode( ',', $pafiledb_config['hotlink_allowed'] );
			}

			$good_referers[] = $board_config['server_name'];
			$errored = true;

			for ( $i = 0; $i < count( $good_referers ); $i++ )
			{
				$good_referers[$i] = trim( $good_referers[$i] );

				if ( ( strstr( $check_referer, $good_referers[$i] ) ) and ( $good_referers[$i] != '' ) )
				{
					$errored = false;
				}
			}

			if ( $errored )
			{
				mx_message_die( GENERAL_MESSAGE, $user->lang['Directly_linked'] );
			}
		}

		$sql = 'SELECT *
			FROM ' . PA_MIRRORS_TABLE . " AS f
			WHERE f.file_id = $file_id
			ORDER BY mirror_id";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt select download', '', __LINE__, __FILE__, $sql );
		}

		$mirrors_data = array();
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$mirrors_data[$row['mirror_id']] = $row;
		}

		$db->sql_freeresult( $result );

		if ( !empty( $mirrors_data ) && !$mirror_id )
		{
			global $template, $db, $theme, $gen_simple_header, $starttime;

			$template->assign_vars( array(
				'L_INDEX' => "<<",
				'L_MIRRORS' => $user->lang['Mirrors'],
				'L_MIRROR_LOCATION' => $user->lang['Mirror_location'],
				'L_DOWNLOAD' => $user->lang['Download_file'],

				'U_INDEX' => mx_append_sid( $mx_root_path . 'index.' . $phpEx ),
				'U_DOWNLOAD_HOME' => mx_append_sid( $this->this_mxurl() ),

				'FILE_NAME' => $file_data['file_name'],
				'DOWNLOAD' => $pafiledb_config['module_name']
			));

			$template->assign_block_vars( 'mirror_row', array(
				'U_DOWNLOAD' => mx_append_sid( $$this->this_mxurl( 'action=download&file_id=' . $file_id . '&mirror_id=-1' ) ),
				'MIRROR_LOCATION' => $board_config['sitename']
			));

			foreach( $mirrors_data as $mir_id => $mirror_data )
			{
				$template->assign_block_vars( 'mirror_row', array(
					'U_DOWNLOAD' => mx_append_sid( $this->this_mxurl( 'action=download&file_id=' . $file_id . '&mirror_id=' . $mir_id ) ),
					'MIRROR_LOCATION' => $mirror_data['mirror_location']
				));
			}

			// ===================================================
			// assign var for navigation
			// ===================================================
			$this->generate_navigation( $file_data['file_catid'] );

			include( $mx_root_path . 'includes/page_header.' . $phpEx );
			$this->display( $user->lang['Download'], 'pa_mirrors_body.'.$tplEx );
			include( $mx_root_path . 'includes/page_tail.' . $phpEx );
		}
		elseif ( ( !empty( $mirrors_data ) && $mirror_id == -1 ) || ( empty( $mirrors_data ) ) )
		{
			$real_filename = $file_data['real_name'];
			$physical_filename = $file_data['unique_name'];
			$upload_dir = ( !empty( $file_data['upload_dir'] ) ) ? $file_data['upload_dir'] : $pafiledb_config['upload_dir'];
			$file_url = $file_data['file_dlurl'];
		}
		elseif ( $mirror_id > 0 && !empty( $mirrors_data[$mirror_id] ) )
		{
			$real_filename = $mirrors_data[$mirror_id]['real_name'];
			$physical_filename = $mirrors_data[$mirror_id]['unique_name'];
			$upload_dir = ( !empty( $mirrors_data[$mirror_id]['upload_dir'] ) ) ? $mirrors_data[$mirror_id]['upload_dir'] : $pafiledb_config['upload_dir'];
			$file_url = $mirrors_data[$mirror_id]['file_dlurl'];
		}
		else
		{
			mx_message_die( GENERAL_MESSAGE, 'Mirror doesn\'t exist' );
		}
		// =========================================================================
		// Update download counter and the last downloaded date
		// =========================================================================
		$current_time = time();
		$file_dls = intval( $file_data['file_dls'] ) + 1;
		$sql = 'UPDATE ' . PA_FILES_TABLE . "
			SET file_dls = $file_dls, file_last = $current_time
			WHERE file_id = $file_id";

		if ( !( $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Update Files table', '', __LINE__, __FILE__, $sql );
		}
		// =========================================================================
		// Update downloader Info for the given file
		// =========================================================================
		$pafiledb_user->update_info( $file_id );

		if ( !empty( $file_url ) )
		{
			$file_url = ( ( !strstr( $file_url, '://' ) ) && ( strpos( $file_url, 'pafiledb/uploads' ) === false ) ) ? 'http://' . $file_url : ( ( strpos( $file_url, 'pafiledb/uploads' ) && ( !strstr( $file_url, '://' ) ) ) ? $module_root_path . $file_url : $file_url );
			pa_redirect( $file_url );
		}
		else
		{
			if(!send_file_to_browser($real_filename, $physical_filename, $module_root_path . $upload_dir))
			{
			   mx_message_die(GENERAL_ERROR, $user->lang['Error_no_download'] . '<br /><br /><b>404 File Not Found:</b> The File <i>' . $real_filename . '</i> does not exist.');
			}
		}
	}
}
?>