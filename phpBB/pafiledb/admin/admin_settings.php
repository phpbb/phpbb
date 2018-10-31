<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: admin_settings.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

if ( !defined( 'IN_PORTAL' ) || !defined( 'IN_ADMIN' ) )
{
	die( "Hacking attempt" );
}

class pafiledb_settings extends pafiledb_admin
{
	var $tpl_name;
	var $page_title;

	/**
	* Constructor
	* Init bbcode cache entries if bitfield is specified
	*/
	function pafiledb_settings($u_action = '')
	{
		global $config, $phpbb_root_path;
		
		if ($u_action)
		{
			$this->u_action = $u_action;
		}		
	}

	function main( $mode )
	{
		global $db, $template, $user, $phpEx, $pafiledb_functions, $pafiledb_cache, $pafiledb_config, $phpbb_root_path, $phpbb_root_path, $phpbb_root_path, $mx_request_vars, $portal_config;

		$submit = ( isset( $_POST['submit'] ) ) ? true : false;
		$size = ( isset( $_POST['max_size'] ) ) ? $_POST['max_size'] : '';

		$sql = 'SELECT *
			FROM ' . PA_CONFIG_TABLE;

		if ( !$result = $db->sql_query( $sql ) )
		{
			mx_message_die( CRITICAL_ERROR, "Could not query config information in admin_board", "", __LINE__, __FILE__, $sql );
		}
		else
		{
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				$config_name = $row['config_name'];
				$config_value = $row['config_value'];
				$pa_config[$config_name] = $config_value;

				$new[$config_name] = ( isset( $_POST[$config_name] ) ) ? $_POST[$config_name] : $pa_config[$config_name];

				if ( ( empty( $size ) ) && ( !$submit ) && ( $config_name == 'max_file_size' ) )
				{
					$size = ( intval( $pa_config[$config_name] ) >= 1048576 ) ? 'mb' : ( ( intval( $pa_config[$config_name] ) >= 1024 ) ? 'kb' : 'b' );
				}

				if ( ( !$submit ) && ( $config_name == 'max_file_size' ) )
				{
					if ( $new[$config_name] >= 1048576 )
					{
						$new[$config_name] = round( $new[$config_name] / 1048576 * 100 ) / 100;
					}
					else if ( $new[$config_name] >= 1024 )
					{
						$new[$config_name] = round( $new[$config_name] / 1024 * 100 ) / 100;
					}
				}

				if ( $submit )
				{
					if ( $config_name == 'max_file_size' )
					{
						$new[$config_name] = ( $size == 'kb' ) ? round( $new[$config_name] * 1024 ) : ( ( $size == 'mb' ) ? round( $new[$config_name] * 1048576 ) : $new[$config_name] );
					}

					if ( $config_name == 'tpl_php' && isset( $_POST[$config_name] ) && $new[$config_name] != $pa_config[$config_name] )
					{
						$template->compile_cache_clear();
					}

					$pafiledb_functions->set_config( $config_name, $new[$config_name] );
				}
			}

			if ( $submit )
			{
				$pafiledb_cache->unload();
				trigger_error($user->lang['Settings_changed'] . adm_back_link($this->u_action));
			}
		}

		//$template->set_filenames( array( 'admin' => 'acp_pafiledb_settings.html' ) );
		
		$this->tpl_name = 'acp_pafiledb_'.$mode;
		$this->page_title = $user->lang['Panel_config_title'];

		$cat_auth_levels = array( 'ALL', 'REG', 'PRIVATE', 'MOD', 'ADMIN' );
		$cat_auth_const = array( AUTH_ALL, AUTH_REG, AUTH_ACL, AUTH_MOD, AUTH_ADMIN );
		$global_auth = array( 'auth_search', 'auth_stats', 'auth_toplist', 'auth_viewall' );
		$auth_select = array();

		foreach( $global_auth as $auth )
		{
			$auth_select[$auth] = '&nbsp;<select name="' . $auth . '">';
			for( $k = 0; $k < count( $cat_auth_levels ); $k++ )
			{
				$selected = ( $new[$auth] == $cat_auth_const[$k] ) ? ' selected="selected"' : '';
				$auth_select[$auth] .= '<option value="' . $cat_auth_const[$k] . '"' . $selected . '>' . $user->lang['Category_' . $cat_auth_levels[$k]] . '</option>';
			}
			$auth_select[$auth] .= '</select>&nbsp;';
		}

		//
		// General Settings
		//
		$module_name = $new['module_name'];

		$enable_module_yes = ( $new['enable_module'] ) ? "checked=\"checked\"" : "";
		$enable_module_no = ( !$new['enable_module'] ) ? "checked=\"checked\"" : "";

		$wysiwyg_path = $new['wysiwyg_path'];
		$upload_dir = $new['upload_dir'];
		$screenshots_dir = $new['screenshots_dir'];

		//
		// File
		//
		$hotlink_prevent_yes = ( $new['hotlink_prevent'] ) ? "checked=\"checked\"" : "";
		$hotlink_prevent_no = ( !$new['hotlink_prevent'] ) ? "checked=\"checked\"" : "";

		$hotlink_allowed = $new['hotlink_allowed'];

		$php_template_yes = ( $new['settings_tpl_php'] ) ? "checked=\"checked\"" : "";
		$php_template_no = ( !$new['settings_tpl_php'] ) ? "checked=\"checked\"" : "";

		$max_file_size = $new['max_file_size'];

		$forbidden_extensions = $new['forbidden_extensions'];

		//
		// Appearance
		//
		$pagination = $new['pagination'];

		$sort_method_options = array();
		$sort_method_options = array( "file_name", "file_time", "file_rating", "file_dls", "file_update_time" );

		$sort_method_list = '<select name="sort_method">';
		for( $j = 0; $j < count( $sort_method_options ); $j++ )
		{
			if ( $new['sort_method'] == $sort_method_options[$j] )
			{
				$status = "selected";
			}
			else
			{
				$status = '';
			}
			$sort_method_list .= '<option value="' . $sort_method_options[$j] . '" ' . $status . '>' . $sort_method_options[$j] . '</option>';
		}
		$sort_method_list .= '</select>';

		$sort_order_options = array();
		$sort_order_options = array( "DESC", "ASC" );

		$sort_order_list = '<select name="sort_order">';

		for( $j = 0; $j < count( $sort_order_options ); $j++ )
		{
			if ( $new['sort_order'] == $sort_order_options[$j] )
			{
				$status = "selected";
			}
			else
			{
				$status = '';
			}
			$sort_order_list .= '<option value="' . $sort_order_options[$j] . '" ' . $status . '>' . $sort_order_options[$j] . '</option>';
		}
		$sort_order_list .= '</select>';

		$settings_topnumber = $new['settings_topnumber'];

		$view_all_yes = ( $new['settings_viewall'] ) ? "checked=\"checked\"" : "";
		$view_all_no = ( !$new['settings_viewall'] ) ? "checked=\"checked\"" : "";

		$settings_newdays = $new['settings_newdays'];
		$cat_col = $new['cat_col'];

		$use_simple_navigation_yes = ( $new['use_simple_navigation'] ) ? "checked=\"checked\"" : "";
		$use_simple_navigation_no = ( !$new['use_simple_navigation'] ) ? "checked=\"checked\"" : "";

		//
		// Instructions
		//
		$pretext_show = ( $new['show_pretext'] ) ? "checked=\"checked\"" : "";
		$pretext_hide = ( !$new['show_pretext'] ) ? "checked=\"checked\"" : "";

		$pt_header = $new['pt_header'];
		$pt_body = $new['pt_body'];


		//
		// Comments (default settings)
		//
		$use_comments_yes = ( $new['use_comments'] ) ? "checked=\"checked\"" : "";
		$use_comments_no = ( !$new['use_comments'] ) ? "checked=\"checked\"" : "";

		switch ($portal_config['portal_backend'])
		{
			case 'internal':
				$internal_comments_internal = "checked=\"checked\"";
				$internal_comments_phpbb = "";
				$comments_forum_id = 0;

				$del_topic_yes = "";
				$del_topic_no = "checked=\"checked\"";

				$autogenerate_comments_yes = "";
				$autogenerate_comments_no = "checked=\"checked\"";

				$template->assign_vars( array(
					'S_READONLY' => "disabled=\"disabled\"" )
				);
				break;

			default:
				$internal_comments_internal = ( $new['internal_comments'] ) ? "checked=\"checked\"" : "";
				$internal_comments_phpbb = ( !$new['internal_comments'] ) ? "checked=\"checked\"" : "";
				$comments_forum_id = $new['comments_forum_id'];

				$del_topic_yes = ( $new['del_topic'] ) ? "checked=\"checked\"" : "";
				$del_topic_no = ( !$new['del_topic'] ) ? "checked=\"checked\"" : "";

				$autogenerate_comments_yes = ( $new['autogenerate_comments'] ) ? "checked=\"checked\"" : "";
				$autogenerate_comments_no = ( !$new['autogenerate_comments'] ) ? "checked=\"checked\"" : "";
				$template->assign_vars( array(
					'S_READONLY' => "" )
				);
				break;
		}

		$allow_comment_wysiwyg_yes = ( $new['allow_comment_wysiwyg'] ) ? "checked=\"checked\"" : "";
		$allow_comment_wysiwyg_no = ( !$new['allow_comment_wysiwyg'] ) ? "checked=\"checked\"" : "";

		$allow_comment_html_yes = ( $new['allow_comment_html'] ) ? "checked=\"checked\"" : "";
		$allow_comment_html_no = ( !$new['allow_comment_html'] ) ? "checked=\"checked\"" : "";

		$allowed_comment_html_tags = $new['allowed_comment_html_tags'];

		$allow_comment_bbcode_yes = ( $new['allow_comment_bbcode'] ) ? "checked=\"checked\"" : "";
		$allow_comment_bbcode_no = ( !$new['allow_comment_bbcode'] ) ? "checked=\"checked\"" : "";

		$allow_comment_smilies_yes = ( $new['allow_comment_smilies'] ) ? "checked=\"checked\"" : "";
		$allow_comment_smilies_no = ( !$new['allow_comment_smilies'] ) ? "checked=\"checked\"" : "";

		$allow_comment_links_yes = ( $new['allow_comment_links'] ) ? "checked=\"checked\"" : "";
		$allow_comment_links_no = ( !$new['allow_comment_links'] ) ? "checked=\"checked\"" : "";

		$allow_comment_images_yes = ( $new['allow_comment_images'] ) ? "checked=\"checked\"" : "";
		$allow_comment_images_no = ( !$new['allow_comment_images'] ) ? "checked=\"checked\"" : "";

		$no_comment_link_message = $new['no_comment_link_message'];
		$no_comment_image_message = $new['no_comment_image_message'];

		$max_comment_chars = $new['max_comment_chars'];
		$max_comment_subject_chars = $new['max_comment_subject_chars'];

		$format_comment_truncate_links_yes = ( $new['formatting_comment_truncate_links'] ) ? "checked=\"checked\"" : "";
		$format_comment_truncate_links_no = ( !$new['formatting_comment_truncate_links'] ) ? "checked=\"checked\"" : "";

		$format_comment_image_resize = $new['formatting_comment_image_resize'];

		$format_comment_wordwrap_yes = ( $new['formatting_comment_wordwrap'] ) ? "checked=\"checked\"" : "";
		$format_comment_wordwrap_no = ( !$new['formatting_comment_wordwrap'] ) ? "checked=\"checked\"" : "";

		$comments_pag = $new['comments_pagination'];

		//
		// Ratings (default settings)
		//
		$use_ratings_yes = ( $new['use_ratings'] ) ? "checked=\"checked\"" : "";
		$use_ratings_no = ( !$new['use_ratings'] ) ? "checked=\"checked\"" : "";

		$votes_check_ip_yes = ( $new['votes_check_ip'] ) ? "checked=\"checked\"" : "";
		$votes_check_ip_no = ( !$new['votes_check_ip'] ) ? "checked=\"checked\"" : "";

		$votes_check_userid_yes = ( $new['votes_check_userid'] ) ? "checked=\"checked\"" : "";
		$votes_check_userid_no = ( !$new['votes_check_userid'] ) ? "checked=\"checked\"" : "";

		//
		// Notifications
		//
		$notify_none = ( $new['notify'] == 0 ) ? "checked=\"checked\"" : "";
		$notify_pm = ( $new['notify'] == 1 ) ? "checked=\"checked\"" : "";
		$notify_email = ( $new['notify'] == 2 ) ? "checked=\"checked\"" : "";

		$notify_group_list = mx_get_groups($new['notify_group'], 'notify_group');

		$template->assign_vars( array(
			'S_SETTINGS_ACTION' => $this->u_action,

			'L_CONFIGURATION_TITLE' => $user->lang['Panel_config_title'],
			'L_CONFIGURATION_EXPLAIN' => $user->lang['Panel_config_explain'],

			'L_RESET' => $user->lang['Reset'],
			'L_SUBMIT' => $user->lang['Submit'],
			'L_YES' => $user->lang['Yes'],
			'L_NO' => $user->lang['No'],
			'L_NONE' => $user->lang['Acc_None'],

			//
			// General
			//
			'L_GENERAL_TITLE' => $user->lang['General_title'],

			'L_MODULE_NAME' => $user->lang['Module_name'],
			'L_MODULE_NAME_EXPLAIN' => $user->lang['Module_name_explain'],
			'MODULE_NAME' => $module_name,

			'L_ENABLE_MODULE' => $user->lang['Enable_module'],
			'L_ENABLE_MODULE_EXPLAIN' => $user->lang['Enable_module_explain'],
			'S_ENABLE_MODULE_YES' => $enable_module_yes,
			'S_ENABLE_MODULE_NO' => $enable_module_no,

			'L_WYSIWYG_PATH' => $user->lang['Wysiwyg_path'],
			'L_WYSIWYG_PATH_EXPLAIN' => $user->lang['Wysiwyg_path_explain'],
			'WYSIWYG_PATH' => $wysiwyg_path,

			'L_UPLOAD_DIR' => $user->lang['Upload_directory'],
			'L_UPLOAD_DIR_EXPLAIN' => $user->lang['Upload_directory_explain'],
			'UPLOAD_DIR' => $upload_dir,

			'L_SCREENSHOT_DIR' => $user->lang['Screenshots_directory'],
			'L_SCREENSHOT_DIR_EXPLAIN' => $user->lang['Screenshots_directory_explain'],
			'SCREENSHOT_DIR' => $screenshots_dir,

			//
			// FILE
			//
			'L_FILE_TITLE' => $user->lang['File_title'],

			'L_HOTLINK' => $user->lang['Hotlink_prevent'],
			'L_HOTLINK_INFO' => $user->lang['Hotlinl_prevent_info'],
			'S_HOTLINK_YES' => $hotlink_prevent_yes,
			'S_HOTLINK_NO' => $hotlink_prevent_no,

			'L_HOTLINK_ALLOWED' => $user->lang['Hotlink_allowed'],
			'L_HOTLINK_ALLOWED_INFO' => $user->lang['Hotlink_allowed_info'],
			'HOTLINK_ALLOWED' => $hotlink_allowed,

			'L_PHP_TPL' => $user->lang['Php_template'],
			'L_PHP_TPL_INFO' => $user->lang['Php_template_info'],
			'S_PHP_TPL_YES' => $php_template_yes,
			'S_PHP_TPL_NO' => $php_template_no,

			'L_MAX_FILE_SIZE' => $user->lang['Max_filesize'],
			'L_MAX_FILE_SIZE_INFO' => $user->lang['Max_filesize_explain'],
			'MAX_FILE_SIZE' => $max_file_size,
			'S_FILESIZE' => $this->pa_size_select( 'max_size', $size ),

			'L_FORBIDDEN_EXTENSIONS' => $user->lang['Forbidden_extensions'],
			'L_FORBIDDEN_EXTENSIONS_EXPLAIN' => $user->lang['Forbidden_extensions_explain'],
			'FORBIDDEN_EXTENSIONS' => $forbidden_extensions,


			//
			// Appearance
			//
			'L_APPEARANCE_TITLE' => $user->lang['Appearance_title'],

			'L_PAGINATION' => $user->lang['File_pagination'],
			'L_PAGINATION_EXPLAIN' => $user->lang['File_pagination_explain'],
			'PAGINATION' => $pagination,

			'L_SORT_METHOD' => $user->lang['Sort_method'],
			'L_SORT_METHOD_EXPLAIN' => $user->lang['Sort_method_explain'],
			'SORT_METHOD' => $sort_method_list,

			'L_SORT_ORDER' => $user->lang['Sort_order'],
			'L_SORT_ORDER_EXPLAIN' => $user->lang['Sort_order_explain'],
			'SORT_ORDER' => $sort_order_list,

			'L_TOPNUM' => $user->lang['Topnum'],
			'L_TOPNUMINFO' => $user->lang['Topnuminfo'],
			'SETTINGS_TOPNUMBER' => $settings_topnumber,

			'CAT_COL' => $cat_col,
			'L_CAT_COL' => $user->lang['Cat_col'],

			'S_USE_SIMPLE_NAVIGATION_YES' => $use_simple_navigation_yes,
			'S_USE_SIMPLE_NAVIGATION_NO' => $use_simple_navigation_no,
			'L_USE_SIMPLE_NAVIGATION' => $user->lang['Use_simple_navigation'],
			'L_USE_SIMPLE_NAVIGATION_EXPLAIN' => $user->lang['Use_simple_navigation_explain'],

			'L_NFDAYS' => $user->lang['Nfdays'],
			'L_NFDAYSINFO' => $user->lang['Nfdaysinfo'],
			'SETTINGS_NEWDAYS' => $settings_newdays,

			'L_SHOW_VIEWALL' => $user->lang['Showva'],
			'L_VIEWALL_INFO' => $user->lang['Showvainfo'],
			'S_VIEW_ALL_YES' => $view_all_yes,
			'S_VIEW_ALL_NO' => $view_all_no,

			//
			// Comments
			//
			'L_COMMENTS_TITLE' => $user->lang['Comments_title'],
			'L_COMMENTS_TITLE_EXPLAIN' => $user->lang['Comments_title_explain'],

			'L_USE_COMMENTS' => $user->lang['Use_comments'],
			'L_USE_COMMENTS_EXPLAIN' => $user->lang['Use_comments_explain'],
			'S_USE_COMMENTS_YES' => $use_comments_yes,
			'S_USE_COMMENTS_NO' => $use_comments_no,

			'L_INTERNAL_COMMENTS' => $user->lang['Internal_comments'],
			'L_INTERNAL_COMMENTS_EXPLAIN' => $user->lang['Internal_comments_explain'],
			'S_INTERNAL_COMMENTS_INTERNAL' => $internal_comments_internal,
			'S_INTERNAL_COMMENTS_PHPBB' => $internal_comments_phpbb,
			'L_INTERNAL_COMMENTS_INTERNAL' => $user->lang['Internal_comments_internal'],
			'L_INTERNAL_COMMENTS_PHPBB' => $user->lang['Internal_comments_phpBB'],

			'L_FORUM_ID' => $user->lang['Forum_id'],
			'L_FORUM_ID_EXPLAIN' => $user->lang['Forum_id_explain'],

			'FORUM_LIST' => $this->get_forums($comments_forum_id, false, 'comments_forum_id' ),

			'L_AUTOGENERATE_COMMENTS' => $user->lang['Autogenerate_comments'],
			'L_AUTOGENERATE_COMMENTS_EXPLAIN' => $user->lang['Autogenerate_comments_explain'],
			'S_AUTOGENERATE_COMMENTS_YES' => $autogenerate_comments_yes,
			'S_AUTOGENERATE_COMMENTS_NO' => $autogenerate_comments_no,

			'L_ALLOW_COMMENT_WYSIWYG' => $user->lang['Allow_Wysiwyg'],
			'L_ALLOW_COMMENT_WYSIWYG_EXPLAIN' => $user->lang['Allow_Wysiwyg_explain'],
			'S_ALLOW_COMMENT_WYSIWYG_YES' => $allow_comment_wysiwyg_yes,
			'S_ALLOW_COMMENT_WYSIWYG_NO' => $allow_comment_wysiwyg_no,

			'L_ALLOW_COMMENT_HTML' => $user->lang['Allow_HTML'],
			'L_ALLOW_COMMENT_HTML_EXPLAIN' => $user->lang['Allow_html_explain'],
			'S_ALLOW_COMMENT_HTML_YES' => $allow_comment_html_yes,
			'S_ALLOW_COMMENT_HTML_NO' => $allow_comment_html_no,

			'L_ALLOW_COMMENT_BBCODE' => $user->lang['Allow_BBCode'],
			'L_ALLOW_COMMENT_BBCODE_EXPLAIN' => $user->lang['Allow_bbcode_explain'],
			'S_ALLOW_COMMENT_BBCODE_YES' => $allow_comment_bbcode_yes,
			'S_ALLOW_COMMENT_BBCODE_NO' => $allow_comment_bbcode_no,

			'L_ALLOW_COMMENT_SMILIES' => $user->lang['Allow_smilies'],
			'L_ALLOW_COMMENT_SMILIES_EXPLAIN' => $user->lang['Allow_smilies_explain'],
			'S_ALLOW_COMMENT_SMILIES_YES' => $allow_comment_smilies_yes,
			'S_ALLOW_COMMENT_SMILIES_NO' => $allow_comment_smilies_no,

			'L_ALLOWED_COMMENT_HTML_TAGS' => $user->lang['Allowed_tags'],
			'L_ALLOWED_COMMENT_HTML_TAGS_EXPLAIN' => $user->lang['Allowed_tags_explain'],
			'ALLOWED_COMMENT_HTML_TAGS' => $allowed_comment_html_tags,

			'L_ALLOW_COMMENT_IMAGES' => $user->lang['Allow_images'],
			'L_ALLOW_COMMENT_IMAGES_EXPLAIN' => $user->lang['Allow_images_explain'],
			'S_ALLOW_COMMENT_IMAGES_YES' => $allow_comment_images_yes,
			'S_ALLOW_COMMENT_IMAGES_NO' => $allow_comment_images_no,

			'L_ALLOW_COMMENT_LINKS' => $user->lang['Allow_links'],
			'L_ALLOW_COMMENT_LINKS_EXPLAIN' => $user->lang['Allow_links_explain'],
			'S_ALLOW_COMMENT_LINKS_YES' => $allow_comment_links_yes,
			'S_ALLOW_COMMENT_LINKS_NO' => $allow_comment_links_no,

			'L_COMMENT_LINKS_MESSAGE' => $user->lang['Allow_links_message'],
			'L_COMMENT_LINKS_MESSAGE_EXPLAIN' => $user->lang['Allow_links_message_explain'],
			'COMMENT_MESSAGE_LINK' => $no_comment_link_message,

			'L_COMMENT_IMAGES_MESSAGE' => $user->lang['Allow_images_message'],
			'L_COMMENT_IMAGES_MESSAGE_EXPLAIN' => $user->lang['Allow_images_message_explain'],
			'COMMENT_MESSAGE_IMAGE' => $no_comment_image_message,

			'L_COMMENT_MAX_SUBJECT_CHAR' => $user->lang['Max_subject_char'],
			'L_COMMENT_MAX_SUBJECT_CHAR_EXPLAIN' => $user->lang['Max_subject_char_explain'],
			'COMMENT_MAX_SUBJECT_CHAR' => $max_comment_subject_chars,

			'L_COMMENT_MAX_CHAR' => $user->lang['Max_char'],
			'L_COMMENT_MAX_CHAR_EXPLAIN' => $user->lang['Max_char_explain'],
			'COMMENT_MAX_CHAR' => $max_comment_chars,

			'L_COMMENT_FORMAT_WORDWRAP' => $user->lang['Format_wordwrap'],
			'L_COMMENT_FORMAT_WORDWRAP_EXPLAIN' => $user->lang['Format_wordwrap_explain'],
			'S_COMMENT_FORMAT_WORDWRAP_YES' => $format_comment_wordwrap_yes,
			'S_COMMENT_FORMAT_WORDWRAP_NO' => $format_comment_wordwrap_no,

			'L_COMMENT_FORMAT_IMAGE_RESIZE' => $user->lang['Format_image_resize'],
			'L_COMMENT_FORMAT_IMAGE_RESIZE_EXPLAIN' => $user->lang['Format_image_resize_explain'],
			'COMMENT_FORMAT_IMAGE_RESIZE' => $format_comment_image_resize,

			'L_COMMENT_FORMAT_TRUNCATE_LINKS' => $user->lang['Format_truncate_links'],
			'L_COMMENT_FORMAT_TRUNCATE_LINKS_EXPLAIN' => $user->lang['Format_truncate_links_explain'],
			'S_COMMENT_FORMAT_TRUNCATE_LINKS_YES' => $format_comment_truncate_links_yes,
			'S_COMMENT_FORMAT_TRUNCATE_LINKS_NO' => $format_comment_truncate_links_no,

			'L_COMMENTS_PAG' => $user->lang['Comments_pag'],
			'L_COMMENTS_PAG_EXPLAIN' => $user->lang['Comments_pag_explain'],
			'COMMENTS_PAG' => $comments_pag,

			'L_DEL_TOPIC' => $user->lang['Del_topic'],
			'L_DEL_TOPIC_EXPLAIN' => $user->lang['Del_topic_explain'],
			'S_DEL_TOPIC_YES' => $del_topic_yes,
			'S_DEL_TOPIC_NO' => $del_topic_no,

			//
			// Ratings
			//
			'L_RATINGS_TITLE' => $user->lang['Ratings_title'],
			'L_RATINGS_TITLE_EXPLAIN' => $user->lang['Ratings_title_explain'],

			'L_USE_RATINGS' => $user->lang['Use_ratings'],
			'L_USE_RATINGS_EXPLAIN' => $user->lang['Use_ratings_explain'],
			'S_USE_RATINGS_YES' => $use_ratings_yes,
			'S_USE_RATINGS_NO' => $use_ratings_no,

			'L_VOTES_CHECK_IP' => $user->lang['Votes_check_ip'],
			'L_VOTES_CHECK_IP_EXPLAIN' => $user->lang['Votes_check_ip_explain'],
			'S_VOTES_CHECK_IP_YES' => $votes_check_ip_yes,
			'S_VOTES_CHECK_IP_NO' => $votes_check_ip_no,

			'L_VOTES_CHECK_USERID' => $user->lang['Votes_check_userid'],
			'L_VOTES_CHECK_USERID_EXPLAIN' => $user->lang['Votes_check_userid_explain'],
			'S_VOTES_CHECK_USERID_YES' => $votes_check_userid_yes,
			'S_VOTES_CHECK_USERID_NO' => $votes_check_userid_no,

			//
			// Instructions
			//
			'L_INSTRUCTIONS_TITLE' => $user->lang['Instructions_title'],

			'L_SHOW' => $user->lang['Show'],
			'L_HIDE' => $user->lang['Hide'],
			'L_PRE_TEXT_NAME' => $user->lang['Pre_text_name'],
			'L_PRE_TEXT_HEADER' => $user->lang['Pre_text_header'],
			'L_PRE_TEXT_BODY' => $user->lang['Pre_text_body'],
			'L_PRE_TEXT_EXPLAIN' => $user->lang['Pre_text_explain'],
			'S_SHOW_PRETEXT' => $pretext_show,
			'S_HIDE_PRETEXT' => $pretext_hide,
			'L_PT_HEADER' => $pt_header,
			'L_PT_BODY' => $pt_body,

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

			'L_NOTIFY_GROUP' => $user->lang['Notify_group'],
			'L_NOTIFY_GROUP_EXPLAIN' => $user->lang['Notify_group_explain'],
			'NOTIFY_GROUP' => $notify_group_list,

			//
			// Permissions
			//
			'L_PERMISSION_SETTINGS' => $user->lang['Permission_settings'],

			'L_ATUH_SEARCH' => $user->lang['Auth_search'],
			'L_ATUH_SEARCH_INFO' => $user->lang['Auth_search_explain'],
			'S_ATUH_SEARCH' => $auth_select['auth_search'],

			'L_ATUH_STATS' => $user->lang['Auth_stats'],
			'L_ATUH_STATS_INFO' => $user->lang['Auth_stats_explain'],
			'S_ATUH_STATS' => $auth_select['auth_stats'],

			'L_ATUH_TOPLIST' => $user->lang['Auth_toplist'],
			'S_ATUH_TOPLIST' => $auth_select['auth_toplist'],
			'L_ATUH_TOPLIST_INFO' => $user->lang['Auth_toplist_explain'],

			'L_ATUH_VIEWALL' => $user->lang['Auth_viewall'],
			'L_ATUH_VIEWALL_INFO' => $user->lang['Auth_viewall_explain'],
			'S_ATUH_VIEWALL' => $auth_select['auth_viewall'],
		));
	}
}
?>