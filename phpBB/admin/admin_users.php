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

define("IN_ADMIN", true);

if($setmodules == 1)
{
	$filename = basename(__FILE__);
	$module['Users']['Manage'] = $filename;

	return;
}

//
// Load default header
//
$phpbb_root_dir = "./../";
require('pagestart.inc');

if( isset($HTTP_POST_VARS['mode']) || isset($HTTP_GET_VARS['mode']) )
{
	$mode = ( isset($HTTP_POST_VARS['mode']) ) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
}
else
{
	$mode = "";
}

// ---------
// Functions
//
function validate_optional_fields(&$icq, &$aim, &$msnm, &$yim, &$website, &$location, &$occupation, &$interests, &$sig)
{
	// ICQ number has to be only numbers.
	if (!preg_match("/^[0-9]+$/", $icq))
	{
		$icq = "";
	}
	
	// AIM address has to have length >= 2.
	if (strlen($aim) < 2)
	{
		$aim = "";
	}
	
	// MSNM address has to have length >= 2.
	if (strlen($msnm) < 2)
	{
		$msnm = "";
	}
	
	// YIM address has to have length >= 2.
	if (strlen($yim) < 2)
	{
		$yim = "";
	}

	// website has to start with http://, followed by something with length at least 3 that
	// contains at least one dot.
	if($website != "")
	{
		if( !ereg("^http\:\/\/", $website) )
		{
			$website = "http://" . $website;
		}

		if (!preg_match("#^http\\:\\/\\/[a-z0-9]+\.[a-z0-9]+#i", $website))
		{
			$website = "";
		}
	}
	
	// location has to have length >= 2.
	if (strlen($location) < 2)
	{
		$location = "";
	}
	
	// occupation has to have length >= 2.
	if (strlen($occupation) < 2)
	{
		$occupation = "";
	}
	
	// interests has to have length >= 2.
	if (strlen($interests) < 2)
	{
		$interests = "";
	}
	
	// sig has to have length >= 2.
	if (strlen($sig) < 2)
	{
		$sig = "";
	}
	
	return;
}
//
// End Functions
//


//
// Begin program
//
if( $mode == "searchuser" )
{
	if( isset($HTTP_POST_VARS['search']) )
	{
		$username_list = username_search("admin_users.$phpEx", $HTTP_POST_VARS['search_author'], 1);
	}
	else
	{
		username_search("admin_users.$phpEx", "", 1);
	}
	
	//
	// Remove this later
	//
	exit;
}
else if ( isset($HTTP_POST_VARS['username']) || isset($HTTP_GET_VARS[POST_USERS_URL]) || isset($HTTP_POST_VARS[POST_USERS_URL]) )
{
	//
	// Let's find out a little about them...
	//
	if( isset($HTTP_GET_VARS[POST_USERS_URL]) || isset($HTTP_POST_VARS[POST_USERS_URL]) )
	{
		$user_id = ( isset($HTTP_POST_VARS[POST_USERS_URL]) ) ? $HTTP_POST_VARS[POST_USERS_URL] : $HTTP_GET_VARS[POST_USERS_URL];
		$this_userdata = get_userdata_from_id($user_id);
	}
	else
	{
		$this_userdata = get_userdata($HTTP_POST_VARS['username']);
	}

	//
	// Now parse and display it as a template
	//
	$user_id = $this_userdata['user_id'];
	$username = $this_userdata['username'];
	$email = $this_userdata['user_email'];
	$password = "";
	$password_confirm = "";

	$icq = $this_userdata['user_icq'];
	$aim = $this_userdata['user_aim'];
	$msn = $this_userdata['user_msnm'];
	$yim = $this_userdata['user_yim'];

	$website = $this_userdata['user_website'];
	$location = $this_userdata['user_from'];
	$occupation = $this_userdata['user_occ'];
	$interests = $this_userdata['user_interests'];
	$signature = $this_userdata['user_sig'];

	$viewemail = $this_userdata['user_viewemail'];
	$notifypm = $this_userdata['user_notify_pm'];
	$attachsig = $this_userdata['user_attachsig'];
	$allowhtml = $this_userdata['user_allowhtml'];
	$allowbbcode = $this_userdata['user_allowbbcode'];
	$allowsmilies = $this_userdata['user_allowsmile'];
	$allowviewonline = $this_userdata['user_allow_viewonline'];

	$user_avatar = $this_userdata['user_avatar'];
	$user_avatar_type = $this_userdata['user_avatar_type'];
	$user_style = $this_userdata['user_style'];
	$user_lang = $this_userdata['user_lang'];
	$user_timezone = $this_userdata['user_timezone'];
	$user_dateformat = $this_userdata['user_dateformat'];
	
	$user_status = $this_userdata['user_active'];
	$user_allowavatar = $this_userdata['user_allowavatar'];
	$user_allowpm = $this_userdata['user_allow_pm'];
	
	$COPPA = false;

	$html_status =   ($board_config['allow_html']) ? $lang['ON'] : $lang['OFF'];
	$bbcode_status =  ($board_config['allow_bbcode']) ? $lang['ON'] : $lang['OFF'];
	$smilies_status =  ($board_config['allow_smilies']) ? $lang['ON'] : $lang['OFF'];

	$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" /><input type="hidden" name="agreed" value="true" /><input type="hidden" name="coppa" value="' . $coppa . '" />';
	$s_hidden_fields .= '<input type="hidden" name="user_id" value="' . $this_userdata['user_id'] . '" />';
	
	if( $user_avatar_type )
	{
		switch( $user_avatar_type )
		{
			case USER_AVATAR_UPLOAD:
				$avatar = "<img src=\"../" . $board_config['avatar_path'] . "/" . $user_avatar . "\" alt=\"\" />";
				break;
			case USER_AVATAR_REMOTE:
				$avatar = "<img src=\"$user_avatar\" alt=\"\" />";
				break;
			case USER_AVATAR_GALLERY:
				$avatar = "<img src=\"../" . $board_config['avatar_gallery_path'] . "/" . $user_avatar . "\" alt=\"\" />";
				break;
		}
	}
	else
	{
		$avatar = "";
	}

	$signature = preg_replace("/\:[0-9a-z\:]*?\]/si", "]", $signature);

	$template->set_filenames(array(
		"body" => "admin/user_edit_body.tpl")
	);

	$template->assign_vars(array(
		"L_USER_TITLE" => $lang['User'] . " " . $lang['User_admin'],
		"L_USER_EXPLAIN" => $lang['User_admin_explain'],

		"USERNAME" => $username,
		"EMAIL" => $email,
		"YIM" => $yim,
		"ICQ" => $icq,
		"MSN" => $msn,
		"AIM" => $aim,
		"OCCUPATION" => $occupation,
		"INTERESTS" => $interests,
		"LOCATION" => $location,
		"WEBSITE" => $website,
		"SIGNATURE" => str_replace("<br />", "\n", $signature),
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
		"AVATAR" => $avatar,
		"LANGUAGE_SELECT" => language_select($user_lang, 'language', '../language'),
		"TIMEZONE_SELECT" => tz_select($user_timezone),
		"STYLE_SELECT" => style_select($user_style, 'style'),
		"DATE_FORMAT" => $user_dateformat,
		"HTML_STATUS" => $html_status,
		"BBCODE_STATUS" => $bbcode_status,
		"SMILIES_STATUS" => $smilies_status,
		"ALLOW_PM_YES" => ($user_allowpm) ? "checked=\"checked\"" : "",
		"ALLOW_PM_NO" => (!$user_allowpm) ? "checked=\"checked\"" : "",
		"ALLOW_AVATAR_YES" => ($user_allowavatar) ? "checked=\"checked\"" : "",
		"ALLOW_AVATAR_NO" => (!$user_allowavatar) ? "checked=\"checked\"" : "",
		"USER_ACTIVE_YES" => ($user_status) ? "checked=\"checked\"" : "",
		"USER_ACTIVE_NO" => (!$user_status) ? "checked=\"checked\"" : "", 

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
		"L_BOARD_STYLE" => $lang['Board_style'],
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
		"L_USER_ACTIVE" => $lang['User_status'],
		"L_ALLOW_PM" => $lang['User_allowpm'],
		"L_ALLOW_AVATAR" => $lang['User_allowavatar'],
		
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

	$template->pparse("body");
}
else if( isset($HTTP_POST_VARS['submit']) && isset($HTTP_POST_VARS['user_id']) )
{
	//
	// Ok, the profile has been modified and submitted, let's update
	//
	$user_id = intval($HTTP_POST_VARS['user_id']);

	$username = (!empty($HTTP_POST_VARS['username'])) ? trim(strip_tags($HTTP_POST_VARS['username'])) : "";
	$email = (!empty($HTTP_POST_VARS['email'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['email']))) : "";

	$password = (!empty($HTTP_POST_VARS['password'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['password']))) : "";
	$password_confirm = (!empty($HTTP_POST_VARS['password_confirm'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['password_confirm']))) : "";

	$icq = (!empty($HTTP_POST_VARS['icq'])) ? trim(strip_tags($HTTP_POST_VARS['icq'])) : "";
	$aim = (!empty($HTTP_POST_VARS['aim'])) ? trim(strip_tags($HTTP_POST_VARS['aim'])) : "";
	$msn = (!empty($HTTP_POST_VARS['msn'])) ? trim(strip_tags($HTTP_POST_VARS['msn'])) : "";
	$yim = (!empty($HTTP_POST_VARS['yim'])) ? trim(strip_tags($HTTP_POST_VARS['yim'])) : "";

	$website = (!empty($HTTP_POST_VARS['website'])) ? trim(strip_tags($HTTP_POST_VARS['website'])) : "";
	$location = (!empty($HTTP_POST_VARS['location'])) ? trim(strip_tags($HTTP_POST_VARS['location'])) : "";
	$occupation = (!empty($HTTP_POST_VARS['occupation'])) ? trim(strip_tags($HTTP_POST_VARS['occupation'])) : "";
	$interests = (!empty($HTTP_POST_VARS['interests'])) ? trim(strip_tags($HTTP_POST_VARS['interests'])) : "";
	$signature = (!empty($HTTP_POST_VARS['signature'])) ? trim(strip_tags(str_replace("<br />", "\n", $HTTP_POST_VARS['signature']))) : "";

	validate_optional_fields($icq, $aim, $msn, $yim, $website, $location, $occupation, $interests, $signature);

	$viewemail = (isset($HTTP_POST_VARS['viewemail'])) ? intval($HTTP_POST_VARS['viewemail']) : 0;
	$allowviewonline = (isset($HTTP_POST_VARS['hideonline'])) ? ( ($HTTP_POST_VARS['hideonline']) ? 0 : 1 ) : 1;
	$notifypm = (isset($HTTP_POST_VARS['notifypm'])) ? intval($HTTP_POST_VARS['notifypm']) : 1;
	$attachsig = (isset($HTTP_POST_VARS['attachsig'])) ? intval($HTTP_POST_VARS['attachsig']) : 0;

	$allowhtml = (isset($HTTP_POST_VARS['allowhtml'])) ? intval($HTTP_POST_VARS['allowhtml']) : $board_config['allow_html'];
	$allowbbcode = (isset($HTTP_POST_VARS['allowbbcode'])) ? intval($HTTP_POST_VARS['allowbbcode']) : $board_config['allow_bbcode'];
	$allowsmilies = (isset($HTTP_POST_VARS['allowsmilies'])) ? intval($HTTP_POST_VARS['allowsmilies']) : $board_config['allow_smilies'];

	$user_style = ($HTTP_POST_VARS['style']) ? intval($HTTP_POST_VARS['style']) : $board_config['default_style'];
	$user_lang = ($HTTP_POST_VARS['language']) ? $HTTP_POST_VARS['language'] : $board_config['default_lang'];
	$user_timezone = (isset($HTTP_POST_VARS['timezone'])) ? doubleval($HTTP_POST_VARS['timezone']) : $board_config['board_timezone'];
	$user_template = ($HTTP_POST_VARS['template']) ? $HTTP_POST_VARS['template'] : $board_config['board_template'];
	$user_dateformat = ($HTTP_POST_VARS['dateformat']) ? trim($HTTP_POST_VARS['dateformat']) : $board_config['default_dateformat'];

	$user_status = (!empty($HTTP_POST_VARS['user_status'])) ? intval($HTTP_POST_VARS['user_status']) : 0;
	$user_allowpm = (!empty($HTTP_POST_VARS['user_allowpm'])) ? intval($HTTP_POST_VARS['user_allowpm']) : 0;
	$user_allowavatar = (!empty($HTTP_POST_VARS['usr_allowavatar'])) ? intval($HTTP_POST_VARS['user_allowavatar']) : 0;

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
	else if(!$password && $password_confirm)
	{
		$error = TRUE;
		$error_msg = $lang['Password_mismatch'];
	}

	if( $signature != "" )
	{
		if( strlen($signature) > $board_config['max_sig_chars'] )
		{
			$error = TRUE;
			if(isset($error_msg))
			{
				$error_msg .= "<br />";
			}
			$error_msg .= $lang['Signature_too_long'];
		}
		else
		{
			$signature_bbcode_uid = ( $allowbbcode ) ? make_bbcode_uid() : "";
			$signature = prepare_message($signature, $allowhtml, $allowbbcode, $allowsmilies, $signature_bbcode_uid);
		}
	}

	if( isset($HTTP_POST_VARS['avatardel']) )
	{
		if( $user_avatar_type == USER_AVATAR_UPLOAD )
		{
			if( file_exists("./../" . $board_config['avatar_path'] . "/" . $user_avatar) )
			{
				@unlink("./../" . $board_config['avatar_path'] . "/" . $user_avatar);
			}
		}
		$avatar_sql = ", user_avatar = '', user_avatar_type = " . USER_AVATAR_NONE;
	}

	if(!$error)
	{
		if( $HTTP_POST_VARS['deleteuser'] )
		{
			$sql = "UPDATE " . POSTS_TABLE . "
				SET poster_id = '-1', post_username = '$username' 
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
				SET " . $username_sql . $passwd_sql . "user_email = '$email', user_icq = '$icq', user_website = '$website', user_occ = '$occupation', user_from = '$location', user_interests = '$interests', user_sig = '$signature', user_viewemail = $viewemail, user_aim = '$aim', user_yim = '$yim', user_msnm = '$msn', user_attachsig = $attachsig, user_allowsmile = $allowsmilies, user_allowhtml = $allowhtml, user_allowavatar = $user_allowavatar, user_allowbbcode = $allowbbcode, user_allow_viewonline = $allowviewonline, user_allow_pm = $user_allowpm, user_notify_pm = $notifypm, user_lang = '$user_lang', user_style = $user_style, user_timezone = $user_timezone, user_dateformat = '$user_dateformat', user_active = $user_status, user_actkey = '$user_actkey'" . $avatar_sql . "
				WHERE user_id = $user_id";
			if($result = $db->sql_query($sql))
			{
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
		WHERE user_id <> " . ANONYMOUS ."
		ORDER BY username";
	$u_result = $db->sql_query($sql);
	$user_list = $db->sql_fetchrowset($u_result);

	$select_list = "<select name=\"" . POST_USERS_URL . "\">";
	for($i = 0; $i < count($user_list); $i++)
	{
		$select_list .= "<option value=\"" . $user_list[$i]['user_id'] . "\">" . $user_list[$i]['username'] . "</option>";
	}
	$select_list .= "</select>";

	$template->set_filenames(array(
		"body" => "admin/user_select_body.tpl")
	);

	$template->assign_vars(array(
		"L_USER_TITLE" => $lang['User'] . " " . $lang['User_admin'],
		"L_USER_EXPLAIN" => $lang['User_admin_explain'],
		"L_USER_SELECT" => $lang['Select_a'] . " " . $lang['User'],
		"L_LOOK_UP" => $lang['Look_up'] . " " . $lang['User'],
		"L_FIND_USERNAME" => $lang['Find_username'],

		"U_SEARCH_USER" => append_sid("admin_users.$phpEx?mode=searchuser"), 

		"S_USER_ACTION" => append_sid("admin_users.$phpEx"),
		"S_USER_SELECT" => $select_list)
	);
	$template->pparse('body');

}

include('page_footer_admin.'.$phpEx);

?>