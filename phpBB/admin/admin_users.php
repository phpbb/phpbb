<?php
/***************************************************************************
 *                              admin_users.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

if($setmodules == 1)
{
	$filename = basename(__FILE__);
	$module['Users']['Manage'] = $filename;

	return;
}

//
// Include required files, get $phpEx and check permissions
//
$phpbb_root_path = "./../";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_INDEX, $session_length);
init_userprefs($userdata);
//
// End session management
//
if( !$userdata['session_logged_in'] )
{
	header("Location: ../login.$phpEx?forward_page=admin/");
}
else if( $userdata['user_level'] != ADMIN )
{
	message_die(GENERAL_MESSAGE, $lang['Not_admin']);
}

//
// Begin program
//
if ( isset($HTTP_GET_VARS['submit']) ) {
	//
	// This looks familiar doesn't it? It's the user profile page! :)
	//

	//
	// Let's find out a little about them...
	//
	$userdata = get_userdata_from_id($HTTP_GET_VARS[POST_USERS_URL]);

	//
	// Now parse and display it as a template
	//
	$user_id = $userdata['user_id'];
	$username = $userdata['username'];
	$email = $userdata['user_email'];
	$password = "";
	$password_confirm = "";

	$icq = $userdata['user_icq'];
	$aim = $userdata['user_aim'];
	$msn = $userdata['user_msnm'];
	$yim = $userdata['user_yim'];

	$website = $userdata['user_website'];
	$location = $userdata['user_from'];
	$occupation = $userdata['user_occ'];
	$interests = $userdata['user_interests'];
	$signature = $userdata['user_sig'];

	$viewemail = $userdata['user_viewemail'];
	$notifypm = $userdata['user_notify_pm'];
	$attachsig = $userdata['user_attachsig'];
	$allowhtml = $userdata['user_allowhtml'];
	$allowbbcode = $userdata['user_allowbbcode'];
	$allowsmilies = $userdata['user_allowsmile'];
	$allowviewonline = $userdata['user_allow_viewonline'];

	$user_avatar = $userdata['user_avatar'];
	$user_theme = $userdata['user_theme'];
	$user_lang = $userdata['user_lang'];
	$user_timezone = $userdata['user_timezone'];
	$user_template = $userdata['user_template'];
	$user_dateformat = $userdata['user_dateformat'];
	$user_status = $userdata['user_active'];
	$user_allowavatar = $userdata['user_allowavatar'];
	$user_allowpm = $userdata['user_allow_pm'];
	

	$COPPA = false;
	
	if(!isset($user_template))
	{
		$selected_template = $board_config['default_template'];
	}

	$html_status =   ($board_config['allow_html']) ? $lang['ON'] : $lang['OFF'];
	$bbcode_status =  ($board_config['allow_bbcode']) ? $lang['ON'] : $lang['OFF'];
	$smilies_status =  ($board_config['allow_smilies']) ? $lang['ON'] : $lang['OFF'];

	$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" /><input type="hidden" name="agreed" value="true" /><input type="hidden" name="coppa" value="' . $coppa . '" />';
	$s_hidden_fields .= '<input type="hidden" name="user_id" value="' . $userdata['user_id'] . '" />';
	// Send the users current email address. If they change it, and account activation is turned on
	// the user account will be disabled and the user will have to reactivate their account.
	$s_hidden_fields .= '<input type="hidden" name="current_email" value="' . $userdata['user_email'] . '" />';


	$template->set_filenames(array(
		"body" => "admin/user_edit_body.tpl")
	);

	$template->assign_vars(array(
		"L_USER_TITLE" => $lang['User'] . " " . $lang['User_admin'],
		"L_USER_EXPLAIN" => $lang['User_admin_explain'],

		"USERNAME" => stripslashes($username),
		"EMAIL" => stripslashes($email),
		"YIM" => stripslashes($yim),
		"ICQ" => stripslashes($icq),
		"MSN" => stripslashes($msn),
		"AIM" => stripslashes($aim),
		"OCCUPATION" => stripslashes($occupation),
		"INTERESTS" => stripslashes($interests),
		"LOCATION" => stripslashes($location),
		"WEBSITE" => stripslashes($website),
		"SIGNATURE" => stripslashes(str_replace("<br />", "\n", $signature)),
		"VIEW_EMAIL_YES" => ($viewemail) ? "checked=\"checked\"" : "",
		"VIEW_EMAIL_NO" => (!$viewemail) ? "checked=\"checked\"" : "",
		"HIDE_USER_YES" => (!$allowviewonline) ? "checked=\"checked\"" : "",
		"HIDE_USER_NO" => ($allowviewonline) ? "checked=\"checked\"" : "",
		"NOTIFY_PM_YES" => ($notifypm) ? "checked=\"checked\"" : "",
		"NOTIFY_PM_NO" => (!$notifypm) ? "checked=\"checked\"" : "",
		"ALWAYS_ADD_SIGNATURE_YES" => ($attachsig) ? "checked=\"checked\"" : "",
		"ALWAYS_ADD_SIGNATURE_NO" => (!$attachsig) ? "checked=\"checked\"" : "",
		"ALWAYS_ALLOW_BBCODE_YES" => ($allowbbcode) ? "checked=\"checked\"" : "",
		"ALWAYS_ALLOW_BBCODE_NO" => (!$allowbbcode) ? "checked=\"checked\"" : "",
		"ALWAYS_ALLOW_HTML_YES" => ($allowhtml) ? "checked=\"checked\"" : "",
		"ALWAYS_ALLOW_HTML_NO" => (!$allowhtml) ? "checked=\"checked\"" : "",
		"ALWAYS_ALLOW_SMILIES_YES" => ($allowsmilies) ? "checked=\"checked\"" : "",
		"ALWAYS_ALLOW_SMILIES_NO" => (!$allowsmilies) ? "checked=\"checked\"" : "",
		"AVATAR" => ($user_avatar != "") ? "<img src=\"../" . $board_config['avatar_path'] . "/" . stripslashes($user_avatar) . "\" alt=\"\" />" : "",
		"TIMEZONE_SELECT" => tz_select($user_timezone),
		"DATE_FORMAT" => stripslashes($user_dateformat),
		"HTML_STATUS" => $html_status,
		"BBCODE_STATUS" => $bbcode_status,
		"SMILIES_STATUS" => $smilies_status,
		"ALLOWPM_YES" => ($user_allowpm) ? "checked=\"checked\"" : "",
		"ALLOWAVATAR_YES" => ($user_allowavatar) ? "checked=\"checked\"" : "",
		"STATUS_YES" => ($user_status) ? "checked=\"checked\"" : "",
		"ALLOWPM_NO" => (!$user_allowpm) ? "checked=\"checked\"" : "",
		"ALLOWAVATAR_NO" => (!$user_allowavatar) ? "checked=\"checked\"" : "",
		"STATUS_NO" => (!$user_status) ? "checked=\"checked\"" : "",

		"L_PASSWORD_IF_CHANGED" => $lang['password_if_changed'],
		"L_PASSWORD_CONFIRM_IF_CHANGED" => $lang['password_confirm_if_changed'],
		"L_SUBMIT" => $lang['Submit'],
		"L_RESET" => $lang['Reset'],
		"L_ICQ_NUMBER" => $lang['ICQ'],
		"L_MESSENGER" => $lang['MSNM'],
		"L_YAHOO" => $lang['YIM'],
		"L_WEBSITE" => $lang['Website'],
		"L_AIM" => $lang['AIM'],
		"L_LOCATION" => $lang['From'],
		"L_OCCUPATION" => $lang['Occupation'],
		"L_BOARD_LANGUAGE" => $lang['Board_lang'],
		"L_BOARD_THEME" => $lang['Board_theme'],
		"L_BOARD_TEMPLATE" => $lang['Board_template'],
		"L_TIMEZONE" => $lang['Timezone'],
		"L_DATE_FORMAT" => $lang['Date_format'],
		"L_DATE_FORMAT_EXPLAIN" => $lang['Date_format_explain'],
		"L_YES" => $lang['Yes'],
		"L_NO" => $lang['No'],
		"L_INTERESTS" => $lang['Interests'],
		"L_ALWAYS_ALLOW_SMILIES" => $lang['Always_smile'],
		"L_ALWAYS_ALLOW_BBCODE" => $lang['Always_bbcode'],
		"L_ALWAYS_ALLOW_HTML" => $lang['Always_html'],
		"L_HIDE_USER" => $lang['Hide_user'],
		"L_ALWAYS_ADD_SIGNATURE" => $lang['Always_add_sig'],
		
		"L_SPECIAL" => $lang['User_special'],
		"L_SPECIAL_EXPLAIN" => $lang['User_specail_explain'],
		"L_STATUS" => $lang['User_status'],
		"L_ALLOWPM" => $lang['User_allowpm'],
		"L_ALLOWAVATAR" => $lang['User_allowavatar'],
		
		"L_AVATAR_PANEL" => $lang['Avatar_panel'],
		"L_AVATAR_EXPLAIN" => $lang['Admin_avatar_explain'],
		"L_DELETE_AVATAR" => $lang['Delete_Image'],
		"L_CURRENT_IMAGE" => $lang['Current_Image'],

		"L_SIGNATURE" => $lang['Signature'],
		"L_SIGNATURE_EXPLAIN" => $lang['Signature_explain'],
		"L_NOTIFY_ON_PRIVMSG" => $lang['Notify_on_privmsg'],
		"L_PREFERENCES" => $lang['Preferences'],
		"L_PUBLIC_VIEW_EMAIL" => $lang['Public_view_email'],
		"L_ITEMS_REQUIRED" => $lang['Items_required'],
		"L_REGISTRATION_INFO" => $lang['Registration_info'],
		"L_PROFILE_INFO" => $lang['Profile_info'],
		"L_PROFILE_INFO_NOTICE" => $lang['Profile_info_warn'],
		"L_CONFIRM" => $lang['Confirm'],
		"L_EMAIL_ADDRESS" => $lang['Email_address'],

		"L_HTML_IS" => $lang['HTML'] . " " . $lang['is'],
		"L_BBCODE_IS" => $lang['BBCode'] . " " . $lang['is'],
		"L_SMILIES_ARE" => $lang['Smilies'] . " " . $lang['are'],

		"L_DELETE_USER" => $lang['User_delete'],
		"L_DELETE_USER_EXPLAIN" => $lang['User_delete_explain'],

		"S_HIDDEN_FIELDS" => $s_hidden_fields,
		"S_PROFILE_ACTION" => append_sid("admin_users.$phpEx"))
	);

	include('page_header_admin.'.$phpEx);
	$template->pparse("body");
}
else if($HTTP_POST_VARS[submit] && $HTTP_POST_VARS['user_id'])
{
//
// Ok, the profile has been modified and submitted, let's update
//

	$user_id = $HTTP_POST_VARS['user_id'];
	$current_email = trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['current_email'])));

	$username = (!empty($HTTP_POST_VARS['username'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['username']))) : "";
	$email = (!empty($HTTP_POST_VARS['email'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['email']))) : "";

	$password = (!empty($HTTP_POST_VARS['password'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['password']))) : "";
	$password_confirm = (!empty($HTTP_POST_VARS['password_confirm'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['password_confirm']))) : "";

	$icq = (!empty($HTTP_POST_VARS['icq'])) ? trim(strip_tags($HTTP_POST_VARS['icq'])) : "";
	$aim = (!empty($HTTP_POST_VARS['aim'])) ? trim(strip_tags($HTTP_POST_VARS['aim'])) : "";
	$msn = (!empty($HTTP_POST_VARS['msn'])) ? trim(strip_tags($HTTP_POST_VARS['msn'])) : "";
	$yim = (!empty($HTTP_POST_VARS['yim'])) ? trim(strip_tags($HTTP_POST_VARS['yim'])) : "";

	$website = (!empty($HTTP_POST_VARS['website'])) ? trim(strip_tags($HTTP_POST_VARS['website'])) : "";
	if($website != "")
	{
		if( !ereg("^http\:\/\/", $website) )
		{
			$website = "http://" . $website;
		}
	}
	$location = (!empty($HTTP_POST_VARS['location'])) ? trim(strip_tags($HTTP_POST_VARS['location'])) : "";
	$occupation = (!empty($HTTP_POST_VARS['occupation'])) ? trim(strip_tags($HTTP_POST_VARS['occupation'])) : "";
	$interests = (!empty($HTTP_POST_VARS['interests'])) ? trim(strip_tags($HTTP_POST_VARS['interests'])) : "";
	$signature = (!empty($HTTP_POST_VARS['signature'])) ? trim(strip_tags(str_replace("<br />", "\n", $HTTP_POST_VARS['signature']))) : "";

	$viewemail = (isset($HTTP_POST_VARS['viewemail'])) ? $HTTP_POST_VARS['viewemail'] : 0;
	$allowviewonline = (isset($HTTP_POST_VARS['hideonline'])) ? ( ($HTTP_POST_VARS['hideonline']) ? 0 : 1 ) : 1;
	$notifypm = (isset($HTTP_POST_VARS['notifypm'])) ? $HTTP_POST_VARS['notifypm'] : 1;
	$attachsig = (isset($HTTP_POST_VARS['attachsig'])) ? $HTTP_POST_VARS['attachsig'] : 0;

	$allowhtml = (isset($HTTP_POST_VARS['allowhtml'])) ? $HTTP_POST_VARS['allowhtml'] : $board_config['allow_html'];
	$allowbbcode = (isset($HTTP_POST_VARS['allowbbcode'])) ? $HTTP_POST_VARS['allowbbcode'] : $board_config['allow_bbcode'];
	$allowsmilies = (isset($HTTP_POST_VARS['allowsmilies'])) ? $HTTP_POST_VARS['allowsmilies'] : $board_config['allow_smilies'];

	$user_theme = ($HTTP_POST_VARS['theme']) ? $HTTP_POST_VARS['theme'] : $board_config['default_theme'];
	$user_lang = ($HTTP_POST_VARS['language']) ? $HTTP_POST_VARS['language'] : $board_config['default_lang'];
	$user_timezone = (isset($HTTP_POST_VARS['timezone'])) ? $HTTP_POST_VARS['timezone'] : $board_config['default_timezone'];
	$user_template = ($HTTP_POST_VARS['template']) ? $HTTP_POST_VARS['template'] : $board_config['default_template'];
	$user_dateformat = ($HTTP_POST_VARS['dateformat']) ? trim($HTTP_POST_VARS['dateformat']) : $board_config['default_dateformat'];

	$user_status = (!empty($HTTP_POST_VARS['user_status'])) ? $HTTP_POST_VARS['user_status'] : 0;
	$user_allowpm = (!empty($HTTP_POST_VARS['user_allowpm'])) ? $HTTP_POST_VARS['usr_allowpm'] : 0;
	$user_allowavatar = (!empty($HTTP_POST_VARS['usr_allowavatar'])) ? $HTTP_POST_VARS['user_allowavatar'] : 0;

	if(isset($HTTP_POST_VARS['submit']))
	{
		$error = FALSE;
		$passwd_sql = "";
	}

	if(!empty($password) && !empty($password_confirm))
	{
		// Awww, the user wants to change their password, isn't that cute..
		if($password != $password_confirm)
		{
			$error = TRUE;
			$error_msg = $lang['Password_mismatch'];
		}
		else
		{
			$password = md5($password);
			$passwd_sql = "user_password = '$password', ";
		}
	}
	else if($password && !$password_confirm)
	{
		$error = TRUE;
		$error_msg = $lang['Password_mismatch'];
	}

	if($username != $userdata['username'] || $mode == "register")
	{
		if(!validate_username($username))
		{
			$error = TRUE;
			if(isset($error_msg))
			{
				$error_msg .= "<br />";
			}
			$error_msg .= $lang['Invalid_username'];
		}
		else
		{
			$username_sql = "username = '$username', ";
		}
	}

	if(isset($HTTP_POST_VARS['avatardel']) && $mode == "editprofile")
	{
		if(file_exists("./".$board_config['avatar_path']."/".$userdata['user_avatar']))
		{
			@unlink("./".$board_config['avatar_path']."/".$userdata['user_avatar']);
			$avatar_sql = ", user_avatar = ''";
		}
	}

	if(!$error)
	{
		if( $HTTP_POST_VARS['deleteuser'] )
		{
			$sql = "UPDATE " . POSTS_TABLE . "
			SET poster_id = '-1'
			WHERE poster_id = $user_id";
			if( $result = $db->sql_query($sql) )
			{
				$sql = "UPDATE " . TOPICS_TABLE . "
				SET topic_poster = '-1'
				WHERE topic_poster = $user_id";
				if( $result = $db->sql_query($sql) )
				{
					$sql = "DELETE FROM " . USERS_TABLE . "
					WHERE user_id = $user_id";
					if( $result = $db->sql_query($sql) )
					{
						$sql = "DELETE FROM " . USER_GROUP_TABLE . "
						WHERE user_id = $user_id";
						$result = @$db->sql_query($sql);
						include('page_header_admin.'. $phpEx);
						$template->set_filenames(array(
							"body" => "admin/admin_message_body.tpl")
						);

						$template->assign_vars(array(
							"MESSAGE_TITLE" => $lang['User'] . $lang['User_admin'],
							"MESSAGE_TEXT" => $lang['User_deleted'])
						);
						$template->pparse("body");
					}
					else
					{
						$error = TRUE;
					}
				}
				else
				{
					$error = TRUE;
				}
			}
			else
			{
				$error = TRUE;
			}

			if( $error == TRUE )
			{
					include('page_header_admin.' . $phpEx);
					$template->set_filenames(array(
						"body" => "admin/admin_message_body.tpl")
					);
					
					$template->assign_vars(array(
						"MESSAGE_TITLE" => $lang['User'] . $lang['User_admin'],
						"MESSAGE_TEXT" => "Could not update user table")
					);
					$template->pparse("body");
			}
		}
		else
		{
			$sql = "UPDATE " . USERS_TABLE . "
			SET " . $username_sql . $passwd_sql . "user_email = '$email', user_icq = '$icq', user_website = '$website', user_occ = '$occupation', user_from = '$location', user_interests = '$interests', user_sig = '$signature', user_viewemail = $viewemail, user_aim = '$aim', user_yim = '$yim', user_msnm = '$msn', user_attachsig = $attachsig, user_allowsmile = $allowsmilies, user_allowhtml = $allowhtml, user_allowavatar = $user_allowavatar, user_allowbbcode = $allowbbcode, user_allow_viewonline = $allowviewonline, user_allow_pm = $user_allowpm user_notify_pm = $notifypm, user_timezone = $user_timezone, user_dateformat = '$user_dateformat', user_lang = '$user_lang', user_active = $user_status, user_actkey = '$user_actkey'" . $avatar_sql . "
			WHERE user_id = $user_id";
			if($result = $db->sql_query($sql))
			{
						include('page_header_admin.' . $phpEx);
						$template->set_filenames(array(
							"body" => "admin/admin_message_body.tpl")
						);

						$template->assign_vars(array(
							"MESSAGE_TITLE" => $lang['User'] . $lang['User_admin'],
							"MESSAGE_TEXT" => $lang['Profile_updated'])
						);
						$template->pparse("body");
			}
			else
			{
						include('page_header_admin.' . $phpEx);
						$template->set_filenames(array(
							"body" => "admin/admin_message_body.tpl")
						);

						$template->assign_vars(array(
							"MESSAGE_TITLE" => $lang['User'] . $lang['User_admin'],
							"MESSAGE_TEXT" => "Error updating user profile")
						);
						$template->pparse("body");
			}
		}
	}
	else
	{
		include('page_header_admin.' . $phpEx);
		$template->set_filenames(array(
			"body" => "admin/admin_message_body.tpl")
		);

		$template->assign_vars(array(
			"MESSAGE_TITLE" => $lang['User'] . $lang['User_admin'],
			"MESSAGE_TEXT" => $error_msg)
		);
		$template->pparse("body");
	}
}
else
{
	//
	// Default user selection box
	//
	// This should be altered on the final system
	//

	$sql = "SELECT user_id, username
		FROM " . USERS_TABLE . "
		WHERE user_id <> " . ANONYMOUS;
	$u_result = $db->sql_query($sql);
	$user_list = $db->sql_fetchrowset($u_result);

	$select_list = "<select name=\"" . POST_USERS_URL . "\">";
	for($i = 0; $i < count($user_list); $i++)
	{
		$select_list .= "<option value=\"" . $user_list[$i]['user_id'] . "\">" . $user_list[$i]['username'] . "</option>";
	}
	$select_list .= "</select>";

	include('page_header_admin.'.$phpEx);

	$template->set_filenames(array(
		"body" => "admin/user_select_body.tpl")
	);

	$template->assign_vars(array(
		"L_USER_TITLE" => $lang['User'] . " " . $lang['User_admin'],
		"L_USER_EXPLAIN" => $lang['User_admin_explain'],
		"L_USER_SELECT" => $lang['Select_a'] . " " . $lang['User'],
		"L_LOOK_UP" => $lang['Look_up'] . " " . $lang['User'],

		"S_USER_ACTION" => append_sid("admin_users.$phpEx"),
		"S_USER_SELECT" => $select_list)
	);
	$template->pparse('body');

}

include('page_footer_admin.'.$phpEx);

?>
