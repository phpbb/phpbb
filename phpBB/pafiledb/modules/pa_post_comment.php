<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_post_comment.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
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
class pafiledb_post_comment extends pafiledb_public
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function main( $action  = false )
	{
		global $template, $pafiledb_functions, $user, $board_config, $phpEx, $pafiledb_config, $db, $images, $userdata;
		global $html_entities_match, $html_entities_replace, $unhtml_specialchars_match, $unhtml_specialchars_replace;
		global $mx_root_path, $module_root_path, $phpbb_root_path, $is_block, $mx_request_vars;
		global $mx_block, $theme, $bbcode, $tplEx;

		//
		// Go full page
		//
		$mx_block->full_page = true;

		//
		// Request vars
		//
		$cid = $mx_request_vars->request('cid', MX_TYPE_INT, 0);

		if ( $mx_request_vars->is_request('item_id') && $mx_request_vars->is_request('cat_id') )
		{
			$item_id = $mx_request_vars->request('item_id', MX_TYPE_INT, 0);
			$cat_id = $mx_request_vars->request('cat_id', MX_TYPE_INT, 0);
		}
		else
		{
			mx_message_die( GENERAL_MESSAGE, $user->lang['File_not_exist'] );
		}

		$delete = $mx_request_vars->request('delete', MX_TYPE_NO_TAGS, '');
		$submit = $mx_request_vars->is_request('submit');
		$preview = $mx_request_vars->is_request('preview');

		$sql = "SELECT *
			FROM " . PA_FILES_TABLE . "
			WHERE file_id = '" . $item_id . "'";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt select download', '', __LINE__, __FILE__, $sql );
		}

		if ( !$file_data = $db->sql_fetchrow( $result ) )
		{
			mx_message_die( GENERAL_MESSAGE, $user->lang['File_not_exist'] );
		}

		$db->sql_freeresult( $result );

		if ( ( !$this->auth_user[$file_data['file_catid']]['auth_post_comment'] ) )
		{
			if ( !$userdata['session_logged_in'] )
			{
				// mx_redirect(mx_append_sid($mx_root_path . "login.$phpEx?redirect=".$this->this_mxurl("action=post_comment&item_id=" . $item_id), true));
			}

			$message = sprintf( $user->lang['Sorry_auth_download'], $this->auth_user[$file_data['file_catid']]['auth_post_comment_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		if ( $mx_request_vars->is_get('cid') )
		{
			if ( $this->comments[$file_data['file_catid']]['internal_comments'] )
			{
				//
				// Query internal comment to edit
				//
				$sql = 'SELECT c.*, u.*
					FROM ' . PA_COMMENTS_TABLE . ' AS c
						LEFT JOIN ' . USERS_TABLE . " AS u ON c.poster_id = u.user_id
					WHERE c.file_id = '" . $item_id . "'
					AND c.comments_id = '" . $mx_request_vars->request('cid', MX_TYPE_INT, '') . "'";

				$comment_arg_title = 'comments_title';
				$comment_arg_message = 'comments_text';
				$comment_arg_bbcode_uid = 'comment_bbcode_uid';
			}
			else
			{
				//
				// Query internal comment to edit
				// Note: cid = post_id
				//
				$sql = "SELECT u.username, u.user_id, u.user_posts, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_regdate, u.user_msnm, u.user_viewemail, u.user_rank, u.user_sig, u.user_sig_bbcode_uid, u.user_avatar, u.user_avatar_type, u.user_allowavatar, u.user_allowsmile, p.*,  pt.post_text, pt.post_subject, pt.bbcode_uid
					FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . POSTS_TEXT_TABLE . " pt
					WHERE pt.post_id = p.post_id
						AND u.user_id = p.poster_id
						AND p.post_id = '" . $mx_request_vars->request('cid', MX_TYPE_INT, '') . "'";

				$comment_arg_title = 'post_subject';
				$comment_arg_message = 'post_text';
				$comment_arg_bbcode_uid = 'bbcode_uid';
			}

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt select comments', '', __LINE__, __FILE__, $sql );
			}

			$comment_row = $db->sql_fetchrow( $result );
		}

		$comment_title = $preview || isset($_POST['subject']) ? $_POST['subject'] : $comment_row[$comment_arg_title];
		$comment_body = $preview || isset($_POST['message']) ? $_POST['message'] : $comment_row[$comment_arg_message];
		$bbcode_uid = $preview ? '' : $comment_row[$comment_arg_bbcode_uid];

		//
		// wysiwyg
		//
		if ( $pafiledb_config['allow_comment_wysiwyg'] && file_exists( $mx_root_path . $pafiledb_config['wysiwyg_path'] . 'tinymce/jscripts/tiny_mce/blank.htm' ))
		{
			//
			// Toggles
			//
			$allow_wysiwyg = true;
			$bbcode_on = false;
			$html_on = true;
			$smilies_on = false;
			$links_on = false;
			$images_on = false;

			$user->langcode = mx_get_langcode();

			if ($mx_block->auth_mod)
         	{
				$template->assign_block_vars( "tinyMCE_admin", array(
					'PATH' => $mx_root_path,
					'LANG' => !empty($user->langcode) ? $user->langcode : $_SERVER['HTTP_ACCEPT_LANGUAGE'],
					'TEMPLATE' => $mx_root_path . 'templates/'. $theme['template_name'] . '/' . $theme['head_stylesheet']
				));
         	}
         	else
         	{
				$template->assign_block_vars( "tinyMCE", array(
					'PATH' => $mx_root_path,
					'LANG' => !empty($user->langcode) ? $user->langcode : $_SERVER['HTTP_ACCEPT_LANGUAGE'],
					'TEMPLATE' => $mx_root_path . 'templates/'. $theme['template_name'] . '/' . $theme['head_stylesheet']
				));
         	}
		}
		else
		{
			//
			// Toggles
			//
			$allow_wysiwyg = false;
			$html_on = ( $pafiledb_config['allow_comment_html'] ) ? true : 0;
			$bbcode_on = ( $pafiledb_config['allow_comment_bbcode'] ) ? true : 0;
			$smilies_on = ( $pafiledb_config['allow_comment_smilies'] ) ? true : 0;
			$links_on = ( $pafiledb_config['allow_comment_links'] ) ? true : 0;
			$images_on = ( $pafiledb_config['allow_comment_images'] ) ? true : 0;

			$board_config['allow_html_tags'] = $pafiledb_config['allowed_comment_html_tags'];

			if ($smilies_on)
			{
				$bbcode->generate_smilies( 'inline', PAGE_POSTING );
			}
		}

		//
		// Instantiate the mx_text and mx_text_formatting classes
		//
		$mx_text = new mx_text();
		$mx_text->init($html_on, $bbcode_on, $smilies_on);

		$mx_text_formatting = new mx_text_formatting();

		//
		// Allow all html tags
		// Fix: Setting 'emtpy' enables all
		//
		$mx_text->allow_all_html_tags = $allow_wysiwyg;


		// =======================================================
		// Delete
		// =======================================================
		if ( $delete == 'do' )
		{
			$sql = 'SELECT *
				FROM ' . PA_FILES_TABLE . "
				WHERE file_id = $item_id";

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldn\'t get file info', '', __LINE__, __FILE__, $sql );
			}
			$file_info = $db->sql_fetchrow( $result );

			if ( ( $this->auth_user[$file_info['file_catid']]['auth_delete_comment'] && $file_info['user_id'] == $userdata['user_id'] ) || $this->auth_user[$file_info['file_catid']]['auth_mod'] )
			{
				if ( $this->comments[$file_data['file_catid']]['internal_comments'] )
				{
					$sql = 'DELETE FROM ' . PA_COMMENTS_TABLE . "
					WHERE comments_id = $cid";

					if ( !( $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Couldnt delete comment', '', __LINE__, __FILE__, $sql );
					}
				}
				else
				{
					include( $module_root_path . 'pafiledb/includes/functions_comment.' . $phpEx );
					$pafiledb_comments = new pafiledb_comments();
					$pafiledb_comments->init( $file_info, 'phpbb' );
					$pafiledb_comments->post('delete', $cid);
				}

				$this->_pafiledb();
				$message = $user->lang['Comment_deleted'] . '<br /><br />' . sprintf( $user->lang['Click_return'], '<a href="' . mx_append_sid( $this->this_mxurl( "action=file&file_id=$item_id" ) ) . '">', '</a>' );
				mx_message_die( GENERAL_MESSAGE, $message );
			}
			else
			{
				$message = sprintf( $user->lang['Sorry_auth_delete'], $this->auth_user[$cat_id]['auth_upload_type'] );
				mx_message_die( GENERAL_MESSAGE, $message );
			}
		}

		// =======================================================
		// Submit
		// =======================================================
		if ( $submit )
		{
			$this->update_add_comment($file_data, $item_id, $cid, '', '', $html_on, $bbcode_on, $smilies_on, $allow_wysiwyg);

			$message = $user->lang['Comment_posted'] . '<br /><br />' . sprintf( $user->lang['Click_return'], '<a href="' . mx_append_sid( $this->this_mxurl( 'action=file&file_id=' . $item_id ) ) . '">', '</a>' );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		$html_status = ( $html_on ) ? $user->lang['HTML_is_ON'] : $user->lang['HTML_is_OFF'];
		$bbcode_status = ( $bbcode_on ) ? $user->lang['BBCode_is_ON'] : $user->lang['BBCode_is_OFF'];
		$smilies_status = ( $smilies_on ) ? $user->lang['Smilies_are_ON'] : $user->lang['Smilies_are_OFF'];
		$links_status = ( $links_on ) ? $user->lang['Links_are_ON'] : $user->lang['Links_are_OFF'];
		$images_status = ( $images_on ) ? $user->lang['Images_are_ON'] : $user->lang['Images_are_OFF'];

		if ( $preview )
		{
			//
			// Encode for preview
			//
			$preview_title = $mx_text->encode_preview_simple($comment_title);
			$preview_text = $mx_text->encode_preview($comment_body);

			if (!$pa_config['allow_images'] || !$pa_config['allow_links'])
			{
				$preview_text = $mx_text_formatting->remove_images_links( $preview_text, $pa_config['allow_images'], $pa_config['no_image_message'], $pa_config['allow_links'], $pa_config['no_link_message'] );
			}

			//$template->set_filenames( array( 'preview' => 'pa_post_preview.'.$tplEx ) );

			$template->assign_vars( array(
				'L_PREVIEW' => $user->lang['Preview'],
				'PREVIEW' => true,
				'SUBJECT' => $preview_title,
				'PRE_COMMENT' => $preview_text )
			);

			//
			// Decode for form editing
			//
			$comment_title = $mx_text->decode_simple($comment_title, true);
			$comment_body = $mx_text->decode($comment_body, '', true);
		}
		else
		{
			// Decode for form editing
			//
			$comment_title = $mx_text->decode_simple($comment_title);
			$comment_body = $mx_text->decode($comment_body, $bbcode_uid);
		}

		if ( $mx_request_vars->is_request('cid') )
		{
			$hidden_form_fields = '<input type="hidden" name="action" value="post_comment">
						<input type="hidden" name="cat_id" value="' . $cat_id . '">
						<input type="hidden" name="item_id" value="' . $item_id . '">
						<input type="hidden" name="cid" value="' . $mx_request_vars->request('cid', MX_TYPE_INT, '') . '">
						<input type="hidden" name="comment" value="post">';
		}
		else
		{
			//
			// New comment
			//
			$comment_title = '';
			$comment_body = '';

			$hidden_form_fields = '<input type="hidden" name="action" value="post_comment">
						<input type="hidden" name="cat_id" value="' . $cat_id . '">
						<input type="hidden" name="item_id" value="' . $item_id . '">
						<input type="hidden" name="comment" value="post">';
		}

		//
		// Output the data to the template
		//
		$template->assign_vars( array(
			'HTML_STATUS' => $html_status,
			'BBCODE_STATUS' => sprintf($bbcode_status, '<a href="' . PHPBB_URL . mx_append_sid("faq.$phpEx?mode=bbcode") . '" target="_phpbbcode">', '</a>'),
			'SMILIES_STATUS' => $smilies_status,
			'LINKS_STATUS' => $links_status,
			'IMAGES_STATUS' => $images_status,
			'FILE_NAME' => $file_data['file_name'],
			'DOWNLOAD' => $pafiledb_config['module_name'],
			'MESSAGE_LENGTH' => $pafiledb_config['max_comment_chars'],

			'TITLE' => $comment_title,
			'COMMENT' => $comment_body,

			'L_COMMENT_ADD' => $user->lang['Comment_add'],
			'L_COMMENT' => $user->lang['Message_body'],
			'L_COMMENT_TITLE' => $user->lang['Subject'],
			'L_OPTIONS' => $user->lang['Options'],
			'L_COMMENT_EXPLAIN' => sprintf( $user->lang['Comment_explain'], $pafiledb_config['max_comment_chars'] ),
			'L_PREVIEW' => $user->lang['Preview'],
			'L_SUBMIT' => $user->lang['Submit'],
			'L_DOWNLOAD' => $user->lang['Download'],

			'L_INDEX' => "<<",
			'L_CHECK_MSG_LENGTH' => $user->lang['Check_message_length'],
			'L_MSG_LENGTH_1' => $user->lang['Msg_length_1'],
			'L_MSG_LENGTH_2' => $user->lang['Msg_length_2'],
			'L_MSG_LENGTH_3' => $user->lang['Msg_length_3'],
			'L_MSG_LENGTH_4' => $user->lang['Msg_length_4'],
			'L_MSG_LENGTH_5' => $user->lang['Msg_length_5'],
			'L_MSG_LENGTH_6' => $user->lang['Msg_length_6'],

			'L_BBCODE_B_HELP' => $user->lang['bbcode_b_help'],
			'L_BBCODE_I_HELP' => $user->lang['bbcode_i_help'],
			'L_BBCODE_U_HELP' => $user->lang['bbcode_u_help'],
			'L_BBCODE_Q_HELP' => $user->lang['bbcode_q_help'],
			'L_BBCODE_C_HELP' => $user->lang['bbcode_c_help'],
			'L_BBCODE_L_HELP' => $user->lang['bbcode_l_help'],
			'L_BBCODE_O_HELP' => $user->lang['bbcode_o_help'],
			'L_BBCODE_P_HELP' => $user->lang['bbcode_p_help'],
			'L_BBCODE_W_HELP' => $user->lang['bbcode_w_help'],
			'L_BBCODE_A_HELP' => $user->lang['bbcode_a_help'],
			'L_BBCODE_S_HELP' => $user->lang['bbcode_s_help'],
			'L_BBCODE_F_HELP' => $user->lang['bbcode_f_help'],
			'L_EMPTY_MESSAGE' => $user->lang['Empty_message'],

			'L_FONT_COLOR' => $user->lang['Font_color'],
			'L_COLOR_DEFAULT' => $user->lang['color_default'],
			'L_COLOR_DARK_RED' => $user->lang['color_dark_red'],
			'L_COLOR_RED' => $user->lang['color_red'],
			'L_COLOR_ORANGE' => $user->lang['color_orange'],
			'L_COLOR_BROWN' => $user->lang['color_brown'],
			'L_COLOR_YELLOW' => $user->lang['color_yellow'],
			'L_COLOR_GREEN' => $user->lang['color_green'],
			'L_COLOR_OLIVE' => $user->lang['color_olive'],
			'L_COLOR_CYAN' => $user->lang['color_cyan'],
			'L_COLOR_BLUE' => $user->lang['color_blue'],
			'L_COLOR_DARK_BLUE' => $user->lang['color_dark_blue'],
			'L_COLOR_INDIGO' => $user->lang['color_indigo'],
			'L_COLOR_VIOLET' => $user->lang['color_violet'],
			'L_COLOR_WHITE' => $user->lang['color_white'],
			'L_COLOR_BLACK' => $user->lang['color_black'],

			'L_FONT_SIZE' => $user->lang['Font_size'],
			'L_FONT_TINY' => $user->lang['font_tiny'],
			'L_FONT_SMALL' => $user->lang['font_small'],
			'L_FONT_NORMAL' => $user->lang['font_normal'],
			'L_FONT_LARGE' => $user->lang['font_large'],
			'L_FONT_HUGE' => $user->lang['font_huge'],
			'L_BBCODE_CLOSE_TAGS' => $user->lang['Close_Tags'],
			'L_STYLES_TIP' => $user->lang['Styles_tip'],

			'U_INDEX' => mx_append_sid( $mx_root_path . 'index.' . $phpEx ),
			'U_DOWNLOAD_HOME' => mx_append_sid( $this->this_mxurl() ),
			'U_FILE_NAME' => mx_append_sid( $this->this_mxurl( 'action=file&item_id=' . $item_id ) ),

			'S_POST_ACTION' => mx_append_sid( $this->this_mxurl() ),
			'S_HIDDEN_FORM_FIELDS' => $hidden_form_fields )
		);

		if ( $bbcode_on )
		{
			$template->assign_block_vars( 'switch_bbcodes', array());
		}

		// ===================================================
		// assign var for navigation
		// ===================================================
		$this->generate_navigation( $file_data['file_catid'] );

		$this->display( $user->lang['Download'], 'pa_comment_posting.'.$tplEx );
	}
}
?>