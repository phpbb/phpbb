<?php
/***************************************************************************
 *                                profile.php
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
 *   This program is free software; you can redistribute it and/or modified
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *
 ***************************************************************************/

include('extension.inc');
include('common.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_PROFILE, $session_length);
init_userprefs($userdata);
//
// End session management
//

//
// Page specific functions
//
function validate_username($username)
{

	global $db;

	$sql = "SELECT u.username, d.disallow_username 
		FROM ".USERS_TABLE." u, ".DISALLOW_TABLE." d
		WHERE LOWER(u.username) = '".strtolower($username)."'
			OR d.disallow_username = '$username'";
	if($result = $db->sql_query($sql))
	{
		if($db->sql_numrows($result) > 0)
		{
			return(FALSE);
		}
	}
	
	return(TRUE);
}
function language_select($default, $dirname="language/")
{
	global $phpEx;
	$dir = opendir($dirname);
	$lang_select = "<select name=\"language\">\n";
	while ($file = readdir($dir)) 
	{
		if (ereg("^lang_", $file)) 
		{
			$filename = str_replace("lang_", "", $file);
			$filename = str_replace(".$phpEx", "", $filename);
			$displayname = preg_replace("/(.*)_(.*)/", "\\1 [ \\2 ]", $filename);
			$selected = (strtolower($default) == strtolower($filename)) ? " selected" : "";
			$lang_select .= "  <option value=\"$filename\"$selected>".ucwords($displayname)."</option>\n";
		}
	}
	$lang_select .= "</select>\n";
	closedir($dir);
	return $lang_select;
}
// NOTE: This function should check is_dir($file), however the is_dir function seems to be buggy on my
// system so its not currently implemented that way
// - James
function template_select($default)
{
	$dir = opendir("templates");
	$template_select = "<select name=\"template\">\n";
	while($file = readdir($dir))
	{
		unset($selected);

		if($file != "." && $file != ".." && $file != "CVS")
		{
			if($file == $default)
			{
				$selected = " selected";
			}
			$template_select .= "<option value=\"$file\"$selected>$file</option>\n";
		}
	}
	$template_select .= "</select>";
	closedir($dir);
	return($template_select);
}
function theme_select($default)
{
	global $db;

	$sql = "SELECT themes_id, themes_name
	  			FROM ".THEMES_TABLE."
	  			ORDER BY themes_name";
	if($result = $db->sql_query($sql))
	{
		$num = $db->sql_numrows($result);
		$rowset = $db->sql_fetchrowset($result);
		$theme_select = "<select name=\"theme\">\n";
		for($i = 0; $i < $num; $i++)
		{
			if(stripslashes($rowset[$i]['themes_name']) == $default || $rowset[$i]['themes_id'] == $default)
			{
				$selected = " SELECTED";
			}
			else
			{
				$selected = "";
			}
			$theme_select .= "\t<option value=\"".$rowset[$i]['themes_id']."\"$selected>".stripslashes($rowset[$i]['themes_name'])."</option>\n";
		}
		$theme_select .= "</select>\n";
	}
	else
	{
		$theme_select = "<select name=\"theme\"><option value=\"-1\">Error in theme_select</option></select>";
	}
	return($theme_select);
}
function tz_select($default)
{
	global $sys_timezone;

	if(!isset($default))
	{
		$default == $sys_timezone;
	}
	$tz_select = "<select name=\"timezone\">";
	$tz_array = array(
			"-12" => "(GMT -12:00 hours) Eniwetok, Kwajalein",
			"-11" => "(GMT -11:00 hours) Midway Island, Samoa",
			"-10" => "(GMT -10:00 hours) Hawaii",
			"-9" => "(GMT -9:00 hours) Alaska",
			"-8" => "(GMT -8:00 hours) Pacific Time (US & Canada)",
			"-7" => "(GMT -7:00 hours) Mountain Time (US & Canada)",
			"-6" => "(GMT -6:00 hours) Central Time (US & Canada), Mexico City",
			"-5" => "(GMT -5:00 hours) Eastern Time (US & Canada), Bogota, Lima, Quito",
			"-4" => "(GMT -4:00 hours) Atlantic Time (Canada), Caracas, La Paz",
			"-3.5" => "(GMT -3:30 hours) Newfoundland",
			"-3" => "(GMT -3:00 hours) Brazil, Buenos Aires, Georgetown",
			"-2" => "(GMT -2:00 hours) Mid-Atlantic, Ascension Is., St. Helena, ",
			"-1" => "(GMT -1:00 hours) Azores, Cape Verde Islands",
			"0"  => "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia",
			"+1" => "(GMT +1:00 hours) Berlin, Brussels, Copenhagen, Madrid, Paris, Rome",
			"+2" => "(GMT +2:00 hours) Kaliningrad, South Africa, Warsaw",
			"+3" => "(GMT +3:00 hours) Baghdad, Riyadh, Moscow, Nairobi",
			"+3.5" => "(GMT +3:30 hours) Tehran",
			"+4" => "(GMT +4:00 hours) Abu Dhabi, Baku, Muscat, Tbilisi",
			"+4.5" => "(GMT +4:30 hours) Kabul",
			"+5" => "(GMT +5:00 hours) Ekaterinburg, Islamabad, Karachi, Tashkent",
			"+5.5" => "(GMT +5:30 hours) Bombay, Calcutta, Madras, New Delhi",
			"+6" => "(GMT +6:00 hours) Almaty, Colombo, Dhaka",
			"+7" => "(GMT +7:00 hours) Bangkok, Hanoi, Jakarta",
			"+8" => "(GMT +8:00 hours) Beijing, Chongqing, Hong Kong, Perth, Singapore, Taipei",
			"+9" => "(GMT +9:00 hours) Tokyo, Seoul, Osaka, Sapporo, Yakutsk",
			"+9.5" => "(GMT +9:30 hours) Adelaide, Darwin",
			"+10" => "(GMT +10:00 hours) Guam, Melbourne, Papua New Guinea, Sydney, Vladivostok",
			"+11" => "(GMT +11:00 hours) Magadan, Solomon Islands, New Caledonia",
			"+12" => "(GMT +12:00 hours) Auckland, Wellington, Fiji, Kamchatka, Marshall Island");
	
	while(list($offset, $zone) = each($tz_array))
	{
		if($offset == $default)
		{
			$selected = " SELECTED";
		}
		else
		{
			$selected = "";
		}
		$tz_select .= "\t<option value=\"$offset\"$selected>$zone</option>\n";
	}
	$tz_select .= "</select>\n";

	return($tz_select);
}
//
// End of functions defns
//


//
// Begin page proper
//
switch($mode)
{
	case 'viewprofile':
		$pagetype = "profile";
		$page_title = "$l_profile";
		include('includes/page_header.'.$phpEx);

		if(!$HTTP_GET_VARS[POST_USERS_URL])
		{
			if(DEBUG)
			{
				error_die(GENERAL_ERROR, "You must supply the user ID number of the user you want to view", __LINE__, __FILE__);
			}
			else
			{
				error_die(GENERAL_ERROR, $l_nouserid);
			}
		}
		$profiledata = get_userdata_from_id($HTTP_GET_VARS[POST_USERS_URL]);

		//
		// Calculate the number of days this user has been a member ($memberdays)
		// Then calculate their posts per day
		//
		$regdate = $profiledata['user_regdate'];

		$memberdays = (time() - $regdate) / (24*60*60);
		$posts_per_day = sprintf("%.2f", $profiledata['user_posts'] / $memberdays);

		// Get the users percentage of total posts
		if($profiledata['user_posts'] != 0)
		{
			$total_posts = get_db_stat("postcount");
			$percentage = sprintf("%.2f", ($profiledata['user_posts'] / $total_posts) * 100);
		}
		else
		{
			$percentage = 0;
		}

		if($profiledata['user_viewemail'])
		{
			// Replace the @ with 'at'. Some anti-spam mesures.
			$email_addy = str_replace("@", " at ", $profiledata['user_email']);
			$email = "<a href=\"mailto:$email_addy\">$email_addy</a>";
		}
		else
		{
			$email = $l_hidden;
		}
		$template->assign_vars(array(
			"L_VIEWING_PROFILE" => $l_viewing_profile, 
			"USERNAME" => stripslashes($profiledata['username']),
			"L_USERNAME" => $l_username,
			"L_VIEW_USERS_POSTS" => $l_view_users_posts,
			"L_JOINED" => $l_joined,
			"JOINED" => create_date($board_config['default_dateformat'], $profiledata['user_regdate'], $board_config['default_timezone']),
			"POSTS_PER_DAY" => $posts_per_day,
			"L_PER_DAY" => $l_per_day,
			"POSTS" => $profiledata['user_posts'],
			"PERCENTAGE" => $percentage . "%",
			"L_OF_TOTAL" => $l_of_total,
			"L_EMAIL_ADDRESS" => $l_emailaddress,
			"EMAIL" => $email,
			"L_ICQ_NUMBER" => $l_icq_number,
			"ICQ" => $profiledata['user_icq'],
			"L_AIM" => $l_aim,
			"AIM" => $profiledata['user_aim'],
			"L_MESSENGER" => $l_messenger,
			"MSN" => $profiledata['user_msnm'],
			"L_YAHOO" => $l_yahoo,
			"YIM" => $profiledata['user_yim'],
			"L_WEBSITE" => $l_website,
			"WEBSITE" => "<a href=\"".$profiledata['user_website']."\" target=\"_blank\">".$profiledata['user_website']."</a>",
			"L_LOCATION" => $l_from,
			"LOCATION" => stripslashes($profiledata['user_from']),
			"L_OCCUPATION" => $l_occupation,
			"OCCUPATION" => stripslashes($profiledata['user_occ']),
			"L_INTERESTS" => $l_interests,
			"INTERESTS" => stripslashes($profiledata['user_interests']),
				
			"S_PROFILE_ACTION" => append_sid("profile.$phpEx"))
		);

		$template->pparse("body");
		include('includes/page_tail.'.$phpEx);
		break;

	case 'editprofile':

		if(!$userdata['session_logged_in'])
		{
			header(append_sid("Location: login.$phpEx?forward_page=$PHP_SELF&mode=editprofile"));
		}
		$pagetype = "register";
		$page_title = "$l_register";
		include('includes/page_header.'.$phpEx);

		if(isset($HTTP_POST_VARS['submit']))
		{
			$user_id = $HTTP_POST_VARS['user_id'];
			$username = (!empty($HTTP_POST_VARS['username'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['username']))) : "";
			$email = (!empty($HTTP_POST_VARS['email'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['email']))) : "";
			$password = (!empty($HTTP_POST_VARS['password'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['password']))) : "";
			$password_confirm = (!empty($HTTP_POST_VARS['password_confirm'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['password_confirm']))) : "";

			$icq = (!empty($HTTP_POST_VARS['icq'])) ? trim(strip_tags($HTTP_POST_VARS['icq'])) : "";
			$aim = (!empty($HTTP_POST_VARS['aim'])) ? trim(strip_tags(addslashes($HTTP_POST_VARS['aim']))) : "";
			$msn = (!empty($HTTP_POST_VARS['msn'])) ? trim(strip_tags(addslashes($HTTP_POST_VARS['msn']))) : "";
			$yim = (!empty($HTTP_POST_VARS['yim'])) ? trim(strip_tags(addslashes($HTTP_POST_VARS['yim']))) : "";

			$website = (!empty($HTTP_POST_VARS['website'])) ? trim(strip_tags(addslashes($HTTP_POST_VARS['website']))) : "";
			$location = (!empty($HTTP_POST_VARS['location'])) ? trim(strip_tags(addslashes($HTTP_POST_VARS['location']))) : "";
			$occupation = (!empty($HTTP_POST_VARS['occupation'])) ? trim(strip_tags(addslashes($HTTP_POST_VARS['occupation']))) : "";
			$interests = (!empty($HTTP_POST_VARS['interests'])) ? trim(addslashes($HTTP_POST_VARS['interests'])) : "";
			$signature = (!empty($HTTP_POST_VARS['signature'])) ? trim(addslashes(str_replace("\n", "<br />", $HTTP_POST_VARS['signature']))) : "";

			$viewemail = $HTTP_POST_VARS['viewemail'];
			$attachsig = $HTTP_POST_VARS['attachsig'];
			$allowhtml = $HTTP_POST_VARS['allowhtml'];
			$allowbbcode = $HTTP_POST_VARS['allowbbcode'];
			$allowsmilies = $HTTP_POST_VARS['allowsmilies'];

			$user_theme = ($HTTP_POST_VARS['theme']) ? $HTTP_POST_VARS['theme'] : $board_config['default_theme'];
			$user_lang = ($HTTP_POST_VARS['language']) ? $HTTP_POST_VARS['language'] : $board_config['default_lang'];
			$user_timezone = (isset($HTTP_POST_VARS['timezone'])) ? $HTTP_POST_VARS['timezone'] : $board_config['default_timezone'];
			$user_template = ($HTTP_POST_VARS['template']) ? $HTTP_POST_VARS['template'] : $board_config['default_template'];
			$user_dateformat = ($HTTP_POST_VARS['dateformat']) ? trim($HTTP_POST_VARS['dateformat']) : $board_config['default_dateformat'];
			
			$error = FALSE;
			
			$passwd_sql = "";
			if(!empty($password) && !empty($password_confirm))
			{
				// The user wants to change their password, isn't that cute..
				if($password != $password_confirm)
				{
					$error = TRUE;
					$error_msg = $l_mismatch . "<br />" . $l_tryagain;
				}
				else
				{
					$password = md5($password);
					$passwd_sql = ", user_password = '$password'";
				}
			}
			else if($password && !$password_confirm)
			{
				$error = TRUE;
				$error_msg = $l_mismatch . "<br />" . $l_tryagain;		
			}
			
			if($board_config['allow_namechange'])
			{
				if(!validate_username($username))
				{
					$error = TRUE;
					if(isset($error_msg))
					{
						$error_msg .= "<br />";
					}
					$error_msg .= $l_invalidname;
				}
			}
			if(!$error)
			{
					
				$sql = "UPDATE ".USERS_TABLE." 
					SET username = '$username'".$passwd_sql.", user_email = '$email', user_icq = '$icq', user_website = '$website', user_occ = '$occ', user_from = '$location', user_interests = '$interests', user_sig = '$signature', user_viewemail = $viewemail, user_aim = '$aim', user_yim = '$yim', user_msnm = '$msn', user_attachsig = $attachsig, user_desmile = $allowsmilies, user_html = $allowhtml, user_bbcode = $allowbbcode, user_timezone = $user_timezone, user_dateformat = '$user_dateformat', user_lang = '$user_lang', user_template = '$user_template', user_theme = $user_theme 
					WHERE user_id = $user_id";
				
				if($result = $db->sql_query($sql))
				{		
					$msg = $l_infoupdated;
					$template->set_filenames(array(
						"reg_header" => "error_body.tpl"
					));
					$template->assign_vars(array(
						"ERROR_MESSAGE" => $msg
					));
					$template->pparse("reg_header");
					
					include('includes/page_tail.'.$phpEx);
				}
			}
			else
			{
				$template->set_filenames(array(
					"reg_header" => "error_body.tpl"
				));
				$template->assign_vars(array(
					"ERROR_MESSAGE" => $error_msg
				));
				$template->pparse("reg_header");
			}
		}
		else
		{
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
			$signature = str_replace("<br />", "\n", $userdata['user_sig']);

			$viewemail = $userdata['user_viewemail'];
			$attachsig = $userdata['user_attachsig'];
			$allowhtml = $userdata['user_html'];
			$allowbbcode = $userdata['user_bbcode'];
			$allowsmilies = $userdata['user_desmile'];

			$user_theme = $userdata['user_theme'];
			$user_lang = $userdata['user_lang'];
			$user_timezone = $userdata['user_timezone'];
			$user_template = $userdata['user_template'];
			$user_dateformat = $userdata['user_dateformat'];
		}

		$template->set_filenames(array(
			"body" => "profile_add_body.tpl"));
		$template->assign_vars(array(
			"COPPA" => 0,
			"MODE" => $mode,
			"USER_ID" => $userdata['user_id'],
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
			"SIGNATURE" => $signature,
			"VIEW_EMAIL_YES" => ($viewemail) ? "CHECKED" : "",
			"VIEW_EMAIL_NO" => (!$viewemail) ? "CHECKED" : "",
			"ALWAYS_ADD_SIGNATURE_YES" => ($attachsig) ? "CHECKED" : "",
			"ALWAYS_ADD_SIGNATURE_NO" => (!$attachsig) ? "CHECKED" : "",
			"ALWAYS_ALLOW_BBCODE_YES" => ($allowbbcode) ? "CHECKED" : "",
			"ALWAYS_ALLOW_BBCODE_NO" => (!$allowbbcode) ? "CHECKED" : "",
			"ALWAYS_ALLOW_HTML_YES" => ($allowhtml) ? "CHECKED" : "",
			"ALWAYS_ALLOW_HTML_NO" => (!$allowhtml) ? "CHECKED" : "",
			"ALWAYS_ALLOW_SMILIES_YES" => ($allowsmilies) ? "CHECKED" : "",
			"ALWAYS_ALLOW_SMILIES_NO" => (!$allowsmilies) ? "CHECKED" : "",
			"LANGUAGE_SELECT" => language_select($user_lang),
			"THEME_SELECT" => theme_select($user_theme),
			"TIMEZONE_SELECT" => tz_select($user_timezone),
			"DATE_FORMAT" => $user_dateformat,
			"TEMPLATE_SELECT" => template_select($user_template),

			"L_PASSWORD_IF_CHANGED" => $l_password_if_changed,
			"L_PASSWORD_CONFIRM_IF_CHANGED" => $l_password_confirm_if_changed,
			"L_SUBMIT" => $l_submit,
			"L_ICQ_NUMBER" => $l_icq_number,
			"L_MESSENGER" => $l_messenger,
			"L_YAHOO" => $l_yahoo,
			"L_WEBSITE" => $l_website,
			"L_AIM" => $l_aim,
			"L_LOCATION" => $l_from,
			"L_OCCUPATION" => $l_occupation,
			"L_BOARD_LANGUAGE" => $l_boardlang,
			"L_BOARD_THEME" => $l_boardtheme,
			"L_BOARD_TEMPLATE" => $l_boardtemplate,
			"L_TIMEZONE" => $l_timezone,
			"L_DATE_FORMAT" => $l_date_format,
			"L_DATE_FORMAT_EXPLANATION" => $l_date_format_explanation,
			"L_YES" => $l_yes,
			"L_NO" => $l_no,
			"L_INTERESTS" => $l_interests,
			"L_USER_UNIQUE" => $l_useruniq,
			"L_ALWAYS_ALLOW_SMILIES" => $l_alwayssmile,
			"L_ALWAYS_ALLOW_BBCODE" => $l_alwaysbbcode,
			"L_ALWAYS_ALLOW_HTML" => $l_alwayshtml,
			"L_ALWAYS_ADD_SIGNATURE" => $l_alwayssig,
			"L_SIGNATURE" => $l_signature,
			"L_SIGNATURE_EXPLAIN" => $l_sigexplain,
			"L_PREFERENCES" => $l_preferences,
			"L_PUBLIC_VIEW_EMAIL" => $l_publicmail,
			"L_ITEMS_REQUIRED" => $l_itemsreq,
			"L_REGISTRATION_INFO" => $l_reginfo,
			"L_PROFILE_INFO" => $l_profile_info,
			"L_PROFILE_INFO_NOTICE" => $l_profile_info_notice,
			"L_CONFIRM" => $l_confirm,
			"L_EMAIL_ADDRESS" => $l_emailaddress,
				
			"S_PROFILE_ACTION" => append_sid("profile.$phpEx"))
		);

		$template->pparse("body");
		include('includes/page_tail.'.$phpEx);

		break;

	case 'register':

		$username = (!empty($HTTP_POST_VARS['username'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['username']))) : "";
		$email = (!empty($HTTP_POST_VARS['email'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['email']))) : "";
		$password = (!empty($HTTP_POST_VARS['password'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['password']))) : "";
		$password_confirm = (!empty($HTTP_POST_VARS['password_confirm'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['password_confirm']))) : "";

		$icq = (!empty($HTTP_POST_VARS['icq'])) ? trim(strip_tags($HTTP_POST_VARS['icq'])) : "";
		$aim = (!empty($HTTP_POST_VARS['aim'])) ? trim(strip_tags(addslashes($HTTP_POST_VARS['aim']))) : "";
		$msn = (!empty($HTTP_POST_VARS['msn'])) ? trim(strip_tags(addslashes($HTTP_POST_VARS['msn']))) : "";
		$yim = (!empty($HTTP_POST_VARS['yim'])) ? trim(strip_tags(addslashes($HTTP_POST_VARS['yim']))) : "";

		$website = (!empty($HTTP_POST_VARS['website'])) ? trim(strip_tags(addslashes($HTTP_POST_VARS['website']))) : "";
		$location = (!empty($HTTP_POST_VARS['location'])) ? trim(strip_tags(addslashes($HTTP_POST_VARS['location']))) : "";
		$occupation = (!empty($HTTP_POST_VARS['occupation'])) ? trim(strip_tags(addslashes($HTTP_POST_VARS['occupation']))) : "";
		$interests = (!empty($HTTP_POST_VARS['interests'])) ? trim(addslashes($HTTP_POST_VARS['interests'])) : "";
		$signature = (!empty($HTTP_POST_VARS['signature'])) ? trim(addslashes($HTTP_POST_VARS['signature'])) : "";

		$viewemail = $HTTP_POST_VARS['viewemail'];
		$attachsig = $HTTP_POST_VARS['attachsig'];
		$allowhtml = $HTTP_POST_VARS['allowhtml'];
		$allowbbcode = $HTTP_POST_VARS['allowbbcode'];
		$allowsmilies = $HTTP_POST_VARS['allowsmilies'];

		$user_theme = ($HTTP_POST_VARS['theme']) ? $HTTP_POST_VARS['theme'] : $board_config['default_theme'];
		$user_lang = ($HTTP_POST_VARS['language']) ? $HTTP_POST_VARS['language'] : $board_config['default_lang'];
		$user_timezone = str_replace("+", "", (isset($HTTP_POST_VARS['timezone'])) ? $HTTP_POST_VARS['timezone'] : $board_config['default_timezone']);
		$user_template = ($HTTP_POST_VARS['template']) ? $HTTP_POST_VARS['template'] : $board_config['default_template'];
		$user_dateformat = ($HTTP_POST_VARS['dateformat']) ? trim($HTTP_POST_VARS['dateformat']) : $board_config['default_dateformat'];

		if(!$HTTP_POST_VARS['coppa'] && !$HTTP_GET_VARS['coppa'])
		{
			$coppa = 0;
		}
		else
		{
			$coppa = 1;
		}
		
		list($hr, $min, $sec, $mon, $day, $year) = explode(",", gmdate("H,i,s,m,d,Y", time()));
		$regdate = gmmktime($hr, $min, $sec, $mon, $day, $year);

		$pagetype = "register";
		$page_title = "$l_register";
		include('includes/page_header.'.$phpEx);


		if(!isset($HTTP_POST_VARS['agreed']) && !isset($HTTP_GET_VARS['agreed']))
		{
			$template->pparse("body");
			include('includes/page_tail.'.$phpEx);
		}
		else
		{
			if(isset($HTTP_POST_VARS['submit']))
			{
				$error = FALSE;
				if(empty($username) || empty($password) || empty($password_confirm) || empty($email))
				{
					$error = TRUE;
					$error_msg = $l_notfilledin;
				}
				if(isset($username) && (!validate_username($username)))
				{
					$error = TRUE;
					if(isset($error_msg))
					{
						$error_msg .= "<br />";
					}
					$error_msg .= $l_invalidname;
				}
				if($password != $password_confirm)
				{
					$error = TRUE;
					if(isset($error_msg))
					{
						$error_msg .= "<br />";
					}
					$error_msg .= $l_mismatch;
				}
			}

			if(isset($HTTP_POST_VARS['submit']) && !$error)
			{
				//
				// The AUTO_INCREMENT field in MySQL v3.23 doesn't work 
				// correctly when there is a row with -1 in that field 
				// so we have to explicitly get the next user ID.
				//
				$sql = "SELECT max(user_id) AS total 
					FROM ".USERS_TABLE;
   				if($result = $db->sql_query($sql))
   				{
   					$user_id_row = $db->sql_fetchrow($result);
   					$new_user_id = $user_id_row['total'] + 1;
   					unset($result);
   					unset($user_id_row);
   				}
   				else
   				{
						error_die(SQL_QUERY, "Couldn't obtained next user_id information.", __LINE__, __FILE__);
   				}

				$md_pass = md5($password);
				$sql = "INSERT INTO ".USERS_TABLE." 
					(user_id, username, user_regdate, user_password, user_email, user_icq, user_website, user_occ,	user_from, user_interests, user_sig, user_viewemail, user_aim, user_yim, user_msnm, user_attachsig, user_desmile, user_html, user_bbcode, user_timezone, user_dateformat, user_lang, user_template, user_theme, user_active, user_actkey) 
					VALUES 
					($new_user_id, '$username', '$regdate', '$md_pass', '$email', '$icq', '$website', '$occupation', '$location', '$interests', '$signature', '$viewemail', '$aim', '$yim', '$msn', $attachsig, $allowsmilies, '$allowhtml', $allowbbcode, $user_timezone, '$user_dateformat', '$user_lang', '$user_template', $user_theme, ";
				if($require_activation || $coppa == 1)
				{
					$act_key = generate_activation_key();
					$sql .= "0, '$act_key')";
				}
				else
				{
					$sql .= "1, '')";
				}

				if($result = $db->sql_query($sql))
				{
					if($require_activation)
					{
					$msg = $l_accountinactive;
						$email_msg = $l_welcomeemailactivate;
					}
					else if($coppa)
					{
						$msg = $l_coppa;
						$email_msg = $l_welcomecoppa;
					}
					else
					{
						$msg = $l_acountadded;
						$email_msg = $l_welcomemail;
					}
					
					if(!$coppa)
					{
						mail($email, $l_welcomesubj, $email_msg, "From: $email_from\r\n");
					}

					$template->set_filenames(array(
						"reg_header" => "error_body.tpl"
					));
					$template->assign_vars(array(
						"ERROR_MESSAGE" => $msg
					));
					$template->pparse("reg_header");
					
					include('includes/page_tail.'.$phpEx);
				}
				else
				{
					$error = TRUE;
					$err = $db->sql_error();
					$error_msg = "Query Error: ".$err["message"];
					if(DEBUG)
					{
						$error_msg .= "<br>Query: $sql";
					}
				}

			}

			if($error)
			{
				$template->set_filenames(array(
					"reg_header" => "error_body.tpl"
				));
				$template->assign_vars(array(
					"ERROR_MESSAGE" => $error_msg
				));
				$template->pparse("reg_header");
			}
			if(!isset($coppa))
			{
				$coppa = FALSE;
			}

			if(!isset($user_template))
			{
				$selected_template = $board_config['default_template'];
			}

			$template->assign_vars(array(
				"MODE" => $mode,
				"USERNAME" => $username,
				"EMAIL" => $email,
				"YIM" => $yim,
				"ICQ" => $icq,
				"MSN" => $msn,
				"AIM" => $aim,
				"COPPA" => $coppa,
				"OCCUPATION" => $occupation,
				"INTERESTS" => $interests,
				"LOCATION" => $location,
				"WEBSITE" => $website,
				"SIGNATURE" => $signature,
				"VIEW_EMAIL_YES" => ($viewemail) ? "CHECKED" : "",
				"VIEW_EMAIL_NO" => (!$viewemail) ? "CHECKED" : "",
				"ALWAYS_ADD_SIGNATURE_YES" => ($attachsig) ? "CHECKED" : "",
				"ALWAYS_ADD_SIGNATURE_NO" => (!$attachsig) ? "CHECKED" : "",
				"ALWAYS_ALLOW_BBCODE_YES" => ($allowbbcode) ? "CHECKED" : "",
				"ALWAYS_ALLOW_BBCODE_NO" => (!$allowbbcode) ? "CHECKED" : "",
				"ALWAYS_ALLOW_HTML_YES" => ($allowhtml) ? "CHECKED" : "",
				"ALWAYS_ALLOW_HTML_NO" => (!$allowhtml) ? "CHECKED" : "",
				"ALWAYS_ALLOW_SMILIES_YES" => ($allowsmilies) ? "CHECKED" : "",
				"ALWAYS_ALLOW_SMILIES_NO" => (!$allowsmilies) ? "CHECKED" : "",
				"LANGUAGE_SELECT" => language_select($user_lang),
				"THEME_SELECT" => theme_select($user_theme),
				"TIMEZONE_SELECT" => tz_select($user_timezone),
				"DATE_FORMAT" => $user_dateformat,
				"TEMPLATE_SELECT" => template_select($user_template),

				"L_SUBMIT" => $l_submit,
				"L_ICQ_NUMBER" => $l_icq_number,
				"L_MESSENGER" => $l_messenger,
				"L_YAHOO" => $l_yahoo,
				"L_WEBSITE" => $l_website,
				"L_AIM" => $l_aim,
				"L_LOCATION" => $l_from,
				"L_OCCUPATION" => $l_occupation,
				"L_BOARD_LANGUAGE" => $l_boardlang,
				"L_BOARD_THEME" => $l_boardtheme,
				"L_BOARD_TEMPLATE" => $l_boardtemplate,
				"L_TIMEZONE" => $l_timezone,
				"L_DATE_FORMAT" => $l_date_format,
				"L_DATE_FORMAT_EXPLANATION" => $l_date_format_explanation,
				"L_YES" => $l_yes,
				"L_NO" => $l_no,
				"L_INTERESTS" => $l_interests,
				"L_USER_UNIQUE" => $l_useruniq,
				"L_ALWAYS_ALLOW_SMILIES" => $l_alwayssmile,
				"L_ALWAYS_ALLOW_BBCODE" => $l_alwaysbbcode,
				"L_ALWAYS_ALLOW_HTML" => $l_alwayshtml,
				"L_ALWAYS_ADD_SIGNATURE" => $l_alwayssig,
				"L_SIGNATURE" => $l_signature,
				"L_SIGNATURE_EXPLAIN" => $l_sigexplain,
				"L_PREFERENCES" => $l_preferences,
				"L_PUBLIC_VIEW_EMAIL" => $l_publicmail,
				"L_ITEMS_REQUIRED" => $l_itemsreq,
				"L_REGISTRATION_INFO" => $l_reginfo,
				"L_PROFILE_INFO" => $l_profile_info,
				"L_PROFILE_INFO_NOTICE" => $l_profile_info_notice,
				"L_CONFIRM" => $l_confirm,
				"L_EMAIL_ADDRESS" => $l_emailaddress,
					
				"S_PROFILE_ACTION" => append_sid("profile.$phpEx"))
			);					
				
			$template->pparse("body");
			include('includes/page_tail.'.$phpEx);
		}
		break;

	case 'activate':

		$sql = "SELECT user_id 
			FROM ".USERS_TABLE." 
			WHERE user_actkey = '$act_key'";
		if($result = $db->sql_query($sql))
		{
			if($num = $db->sql_numrows($result))
			{
				$rowset = $db->sql_fetchrowset($result);
				$sql_update = "UPDATE ".USERS_TABLE." 
					SET user_active = 1, user_actkey = '' 
					WHERE user_id = ".$rowset[0]['user_id'];
				if($result = $db->sql_query($sql_update))
				{
					error_die(GENERAL_ERROR, $l_nowactive);
				}
				else
				{
					error_die(SQL_QUERY);
				}
			}
			else
			{
				error_die(GENERAL_ERROR, $l_wrongactiv);
			}
		}
		else
		{
			error_die(SQL_QUERY);
		}
	break;
}

?>