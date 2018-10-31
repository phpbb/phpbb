<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_file.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
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
class pafiledb_file extends pafiledb_public
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function main( $action  = false )
	{
		global $template, $user, $config, $phpEx, $pafiledb_config, $images;
		global $phpbb_root_path, $userdata, $db, $pafiledb_functions, $tplEx;
		global $mx_root_path, $module_root_path, $is_block, $mx_request_vars;

		// =======================================================
		// Request vars
		// =======================================================
		$start = $mx_request_vars->get('start', MX_TYPE_INT, 0);
		$file_id = $mx_request_vars->request('file_id', MX_TYPE_INT, '');
		$page_num = $mx_request_vars->request('page_num', MX_TYPE_INT, 1) - 1;

		if ( empty( $file_id ) )
		{
			mx_message_die( GENERAL_MESSAGE, $user->lang['File_not_exist'] );
		}

		// =======================================================
		// =======================================================
		switch ( SQL_LAYER )
		{
			case 'oracle':
				$sql = "SELECT f.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, u.user_colour, COUNT(c.comments_id) as total_comments, cat.cat_allow_ratings, cat.cat_allow_comments
					FROM " . PA_FILES_TABLE . " AS f, " . PA_VOTES_TABLE . " AS r, " . USERS_TABLE . " AS u, " . PA_COMMENTS_TABLE . " AS c, " . PA_CATEGORY_TABLE . " AS cat
					WHERE f.file_id = r.votes_file(+)
					AND f.user_id = u.user_id(+)
					AND f.file_id = c.file_id(+)
					AND f.file_id = $file_id
					AND f.file_approved = 1
					AND f.file_catid = cat.cat_id
					GROUP BY f.file_id ";
				break;

			default:
				$sql = "SELECT f.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, u.user_colour, COUNT(c.comments_id) as total_comments, cat.cat_allow_ratings, cat.cat_allow_comments
					FROM " . PA_FILES_TABLE . " AS f
						LEFT JOIN " . PA_VOTES_TABLE . " AS r ON f.file_id = r.votes_file
						LEFT JOIN " . USERS_TABLE . " AS u ON f.user_id = u.user_id
						LEFT JOIN " . PA_COMMENTS_TABLE . " AS c ON f.file_id = c.file_id
						LEFT JOIN " . PA_CATEGORY_TABLE . " AS cat ON f.file_catid = cat.cat_id
					WHERE f.file_id = $file_id
					AND f.file_approved = 1
					GROUP BY f.file_id ";
				break;
		}

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query file info', '', __LINE__, __FILE__, $sql );
		}

		// ===================================================
		// file doesn't exist'
		// ===================================================
		if ( !$file_data = $db->sql_fetchrow( $result ) )
		{
			mx_message_die( GENERAL_MESSAGE, $user->lang['File_not_exist'] );
		}
		$db->sql_freeresult( $result );

		// ===================================================
		// Pafiledb auth for viewing file
		// ===================================================
		if ( ( !$this->auth_user[$file_data['file_catid']]['auth_view_file'] ) )
		{
			/*
			if ( !$userdata['session_logged_in'] )
			{
				mx_redirect(mx_append_sid($mx_root_path . "login.$phpEx?redirect=".$this->this_mxurl("action=file&file_id=" . $file_id), true));
			}
			*/
			$message = sprintf( $user->lang['Sorry_auth_view'], $this->auth_user[$file_data['file_catid']]['auth_view_file_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		$template->assign_vars( array(
			'L_INDEX' => "<<",

			'U_INDEX' => mx_append_sid( $mx_root_path . 'index.' . $phpEx ),
			'U_DOWNLOAD_HOME' => mx_append_sid( $this->this_mxurl() ),

			'FILE_NAME' => $file_data['file_name'],
			'DOWNLOAD' => $pafiledb_config['module_name']
		));

		// ===================================================
		// Prepare file info to display them
		// ===================================================
		$file_time = phpBB2::create_date( $config['default_dateformat'], $file_data['file_time'], $config['board_timezone'] );
		$file_last_download = ( $file_data['file_last'] ) ? phpBB2::create_date( $config['default_dateformat'], $file_data['file_last'], $config['board_timezone'] ) : $user->lang['never'];
		$file_update_time = ( $file_data['file_update_time'] ) ? phpBB2::create_date( $config['default_dateformat'], $file_data['file_update_time'], $config['board_timezone'] ) : $user->lang['never'];
		$file_author = trim( $file_data['file_creator'] );
		$file_version = trim( $file_data['file_version'] );
		$file_screenshot_url = trim( $file_data['file_ssurl'] );
		$file_website_url = trim( $file_data['file_docsurl'] );
		$file_download_link = ( $file_data['file_license'] > 0 ) ? mx_append_sid( $this->this_mxurl( 'action=license&license_id=' . $file_data['file_license'] . '&file_id=' . $file_id ) ) : mx_append_sid( $this->this_mxurl( 'action=download&file_id=' . $file_id, 1 ) );
		$file_size = $pafiledb_functions->get_file_size( $file_id, $file_data );

		$file_poster = get_username_string('full', $file_data['user_id'], $file_data['username'], $file_data['user_colour']);

		if ( !MXBB_MODULE )
		{
			$server_protocol = ($config['cookie_secure']) ? 'https://' : 'http://';
			$server_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($config['server_name']));
			$server_port = ($config['server_port'] <> 80) ? ':' . trim($config['server_port']) : '';
			$script_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($config['script_path']));
			$false_phpbb_url = $server_protocol . $server_name . $server_port . '/';
			$false_phpbb_path = './';
			$file_screenshot_url = str_replace($false_phpbb_url . $false_phpbb_path, PORTAL_URL, $file_screenshot_url);
		}

		//
		// Disabled file
		//
		if ($file_data['file_disable'])
		{
			$file_download_link = 'javascript:disable_popup()';
		}


		$template->assign_vars( array(
			'L_CLICK_HERE' => $user->lang['Click_here'],
			'L_AUTHOR' => $user->lang['Creator'],
			'L_VERSION' => $user->lang['Version'],
			'L_SCREENSHOT' => $user->lang['Scrsht'],
			'L_WEBSITE' => $user->lang['Docs'],
			'L_FILE' => $user->lang['File'],
			'L_DESC' => $user->lang['Desc'],
			'L_DATE' => $user->lang['Date'],
			'L_UPDATE_TIME' => $user->lang['Update_time'],
			'L_LASTTDL' => $user->lang['Lastdl'],
			'L_DLS' => $user->lang['Dls'],
			'L_SIZE' => $user->lang['File_size'],
			'L_EDIT' => $user->lang['Editfile'],
			'L_DELETE' => $user->lang['Deletefile'],
			'L_DOWNLOAD' => $user->lang['Downloadfile'],
			'L_EMAIL' => $user->lang['Emailfile'],
			'L_SUBMITED_BY' => $user->lang['Submiter'],

			'SHOW_AUTHOR' => ( !empty( $file_author ) ) ? true : false,
			'SHOW_VERSION' => ( !empty( $file_version ) ) ? true : false,
			'SHOW_SCREENSHOT' => ( !empty( $file_screenshot_url ) ) ? true : false,
			'SHOW_WEBSITE' => ( !empty( $file_website_url ) ) ? true : false,
			'SS_AS_LINK' => ( $file_data['file_sshot_link'] ) ? true : false,
			'FILE_NAME' => $file_data['file_name'],
			'FILE_LONGDESC' => nl2br( $file_data['file_longdesc'] ),
			'FILE_SUBMITED_BY' => $file_poster,
			'FILE_AUTHOR' => $file_author,
			'FILE_VERSION' => $file_version,
			'FILE_SCREENSHOT' => $file_screenshot_url,
			'FILE_WEBSITE' => $file_website_url,
			'FILE_DISABLE_MSG' => nl2br( $file_data['disable_msg'] ),

			'AUTH_EDIT' => ( ( $this->auth_user[$file_data['file_catid']]['auth_edit_file'] && $file_data['user_id'] == $userdata['user_id'] ) || $this->auth_user[$file_data['file_catid']]['auth_mod'] ) ? true : false,
			'AUTH_DELETE' => ( ( $this->auth_user[$file_data['file_catid']]['auth_delete_file'] && $file_data['user_id'] == $userdata['user_id'] ) || $this->auth_user[$file_data['file_catid']]['auth_mod'] ) ? true : false,
			'AUTH_DOWNLOAD' => ( $this->auth_user[$file_data['file_catid']]['auth_download'] ) ? true : false,
			'AUTH_EMAIL' => ( $this->auth_user[$file_data['file_catid']]['auth_email'] ) ? true : false,

			'DELETE_IMG' => $user->img('icon_post_delete', $label, false, '', 'src'),
			'EDIT_IMG' => $user->img('icon_post_edit', $label, false, '', 'src'),
			'DOWNLOAD_IMG' => $user->img('icon_pa_download', $label, false, '', 'src'),
			'EMAIL_IMG' => $user->img('icon_pa_email', $label, false, '', 'src'),

			'TIME' => $file_time,
			'UPDATE_TIME' => ( $file_data['file_update_time'] != $file_data['file_time'] ) ? $file_update_time : $user->lang['never'],
			'FILE_DLS' => intval( $file_data['file_dls'] ),
			'FILE_SIZE' => $file_size,
			'LAST' => $file_last_download,

			'U_DOWNLOAD' => $file_download_link,
			'U_DELETE' => mx_append_sid( $this->this_mxurl( 'action=user_upload&do=delete&file_id=' . $file_id ) ),
			'U_EDIT' => mx_append_sid( $this->this_mxurl( 'action=user_upload&file_id=' . $file_id ) ),
			'U_EMAIL' => mx_append_sid( $this->this_mxurl( 'action=email&file_id=' . $file_id ) ),

			// Buttons
			'B_DOWNLOAD_IMG' => create_button('icon_pa_download', $user->lang['Downloadfile'], $file_download_link),
			'B_DELETE_IMG' => create_button('icon_post_delete', $user->lang['Deletefile'], "javascript:delete_item('". mx_append_sid( $this->this_mxurl( 'action=user_upload&do=delete&file_id=' . $file_id )) . "')"),
			'B_EDIT_IMG' => create_button('icon_post_edit', $user->lang['Editfile'], mx_append_sid( $this->this_mxurl( 'action=user_upload&file_id=' . $file_id ) )),
			'B_EMAIL_IMG' => create_button('icon_pa_email', $user->lang['Emailfile'], mx_append_sid( $this->this_mxurl( 'action=email&file_id=' . $file_id ))),
		));

		$custom_field = new custom_field();
		$custom_field->init();
		$custom_field->display_data($file_id);

		//
		// Ratings
		//
		if ( $this->ratings[$file_data['file_catid']]['activated'] )
		{
			$file_rating = ( $file_data['rating'] != 0 ) ? round( $file_data['rating'], 2 ) . '/10' : $user->lang['Not_rated'];

			if ( $this->auth_user[$file_data['file_catid']]['auth_rate'] )
			{
				$rate_img = $user->img('icon_pa_rate', $label, false, '', 'src');
			}

			$template->assign_block_vars( 'use_ratings', array(
				'L_RATING' => $user->lang['DlRating'],
				'L_RATE' => $user->lang['Rate'],
				'L_VOTES' => $user->lang['Votes'],
				'FILE_VOTES' => $file_data['total_votes'],
				'RATING' => $file_rating,

				//
				// Allowed to rate
				//
				'RATE_IMG' => $rate_img,
				'U_RATE' => mx_append_sid( $this->this_mxurl( 'action=rate&file_id=' . $file_id ) ),

				// Buttons
				'B_RATE_IMG' => create_button('icon_pa_rate', $user->lang['Rate'], mx_append_sid( $this->this_mxurl( 'action=rate&file_id=' . $file_id ) )),

			));
		}

		//
		// Comments
		//
		if ( $this->comments[$file_data['file_catid']]['activated'] && $this->auth_user[$file_data['file_catid']]['auth_view_comment'])
		{
			$comments_type = $this->comments[$file_data['file_catid']]['internal_comments'] ? 'internal' : 'phpbb';

			//
			// Instatiate comments
			//
			include_once( $module_root_path . 'pafiledb/includes/functions_comment.' . $phpEx );
			$pafiledb_comments = new pafiledb_comments();
			$pafiledb_comments->init( $file_data, $comments_type );
			$pafiledb_comments->display_comments();
		}

		// ===================================================
		// assign var for navigation
		// ===================================================
		$this->generate_navigation( $file_data['file_catid'] );

		//
		// User authorisation levels output
		//
		$this->auth_can($file_data['file_catid']);

		//
		// Output all
		//
		$this->display( $user->lang['Download'], 'pa_file_body.'.$tplEx );
	}
}
?>