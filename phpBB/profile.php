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
 *   This program is free software; you can redistribute it and/or modify
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
		$profiledata = get_userdata_from_id($HTTP_GET_VARS[POST_USERS_URL], $db);

		// Calculate the number of days this user has been a member ($memberdays)
		// Then calculate their posts per day
		$regdate = strtotime($profiledata['user_regdate']);
      $memberdays = (time() - $regdate) / (24*60*60);
      $posts_per_day = $profiledata['user_posts'] / $memberdays;

		// Get the users percentage of total posts
		if($profiledata['user_posts'] != 0)
		{
			$total_posts = get_db_stat("postcount", $db);
			$percentage = ($profiledata['user_posts'] / $total_posts) * 100;
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
		$template->assign_vars(array("L_VIEWINGPROFILE" => $l_viewingprofile,
												"USERNAME" => stripslashes($profiledata['username']),
												"L_USERNAME" => $l_username,
												"L_VIEWPOSTUSER" => $l_viewpostuser,
												"L_JOINED" => $l_joined,
												"JOINED" => $profiledata['user_regdate'],
												"POSTS_PER_DAY" => $posts_per_day,
												"L_PERDAY" => $l_perday,
												"POSTS" => $profiledata['user_posts'],
												"PERCENTAGE" => $percentage . "%",
												"L_OFTOTAL" => $l_oftotal,
												"L_EMAILADDRESS" => $l_emailaddress,
												"EMAIL" => $email,
												"L_ICQNUMBER" => $l_icqnumber,
												"ICQ" => $profiledata['user_icq'],
												"L_AIM" => $l_aim,
												"AIM" => $profiledata['user_aim'],
												"L_MESSENGER" => $l_messenger,
												"MSN" => $profiledata['user_msnm'],
												"L_YAHOO" => $l_yahoo,
												"YIM" => $profiledata['user_yim'],
												"L_WEBSITE" => $l_website,
												"WEBSITE" => "<a href=\"".$profiledata['user_website']."\" target=\"_blank\">".$profiledata['user_website']."</a>",
												"L_FROM" => $l_from,
												"FROM" => stripslashes($profiledata['user_from']),
												"L_OCC" => $l_occupation,
												"OCC" => stripslashes($profiledata['user_occ']),
												"L_INTERESTS" => $l_interests,
												"INTERESTS" => stripslashes($profiledata['user_intrest'])));

		$template->pparse("body");


		include('includes/page_tail.'.$phpEx);

	break;
	case 'editprofile':

	break;
	case 'register':

		$pagetype = "register";
		$page_title = "$l_register";
		include('includes/page_header.'.$phpEx);

		if(!isset($agreed))
		{
			$template->pparse("body");
			include('includes/page_tail.'.$phpEx);
		}
		else
		{
			if(isset($submit))
			{
				$error = FALSE;
				if(empty($username) || empty($password) || empty($password_confirm) || empty($email))
				{
					$error = TRUE;
					$error_msg = $l_notfilledin;
				}
				if(isset($username) && (!validate_username($username, $db)))
				{
					$error = TRUE;
					if(isset($error_msg))
					{
						$error_msg .= "<br>";
					}
					$error_msg .= $l_invalidname;
				}
				if(isset($password) && ($password != $password_confirm))
				{
					$error = TRUE;
					if(isset($error_msg))
					{
						$error_msg .= "<br>";
					}
					$error_msg .= $l_mismatch;
				}
			}

			if(isset($submit) && !$error)
			{
				// The AUTO_INCREMENT field in MySQL v3.23 dosan't work correctly when there is a row with
				// -1 in that field so we have to explicitly get the next user ID.
				$sql = "SELECT max(user_id) AS total FROM ".USERS_TABLE;
   			if($result = $db->sql_query($sql))
   			{
   				$user_id_row = $db->sql_fetchrow($result);
   				$new_user_id = $user_id_row["total"] + 1;
   				unset($result);
   				unset($user_id_row);
   			}
   			else
   			{
					error_die(SQL_QUERY, "Couldn't obtained next user_id information.", __LINE__, __FILE__);
   			}

				$md_pass = md5($password);
				$sql = "INSERT INTO ".USERS_TABLE." (
				       user_id,
						 username,
						 user_regdate,
						 user_password,
						 user_email,
						 user_icq,
						 user_website,
						 user_occ,
						 user_from,
						 user_intrest,
						 user_sig,
						 user_viewemail,
					    user_theme,
					    user_aim,
					    user_yim,
					    user_msnm,
					    user_attachsig,
					    user_desmile,
					    user_html,
					    user_bbcode,
					    user_timezone,
					    user_lang,
					    user_active,
					    user_actkey)
					    VALUES (
					    $new_user_id,
					    '".addslashes($username)."',
					    '".time()."',
					    '$md_pass',
					    '$email',
					    '$icq',
					    '".addslashes($website)."',
					    '".addslashes($occ)."',
					    '".addslashes($from)."',
					    '".addslashes($interests)."',
					    '".addslashes($sig)."',
					    '$viewemail',
					    '$theme',
						 '".addslashes($aim)."',
					    '".addslashes($yim)."',
					    '".addslashes($msn)."',
					    '$alwayssig',
					    '$alwayssmile',
					    '$alwayshtml',
					    '$alwaysbbcode',
					    '$timezone',
					    '$lang',
					    ";
				if($require_activation || $coppa)
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
						$msg = $l_accountadded;
						$email_msg = $l_welcomeemail;
					}
					if(!$coppa)
					{
						mail($email, $l_welcomesubj, $email_msg, "From: $email_from\r\n");
					}

					$template->set_filenames(array("reg_header" => "error_body.tpl"));
					$template->assign_vars(array("ERROR_MESSAGE" => $msg));
					$template->pparse("reg_header");
					include('includes/page_tail.'.$phpEx);
					exit();
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
				$template->set_filenames(array("reg_header" => "error_body.tpl"));
				$template->assign_vars(array("ERROR_MESSAGE" => $error_msg));
				$template->pparse("reg_header");
			}
			if(!isset($coppa))
			{
				$coppa = FALSE;
			}
			$template->assign_vars(array("COPPA" => $coppa,
												  "L_SUBMIT" => $l_submit,
												  "USERNAME" => $username,
												  "EMAIL" => $email,
												  "YIM" => $yim,
												  "ICQ" => $icq,
												  "MSN" => $msn,
												  "AIM" => $aim,
												  "OCC" => $occ,
												  "INTERESTS" => $interests,
												  "FROM" => $from,
												  "WEBSITE" => $website,
												  "SIG" => $sig,
												  "VIEWEMAIL_YES" => ($viewemail) ? "CHECKED" : "",
												  "VIEWEMAIL_NO" => (!$viewemail) ? "CHECKED" : "",
												  "STOREUSERNAME_YES" => (!isset($storeusername) || $storeusername == 1) ? "CHECKED" : "",
												  "STOREUSERNAME_NO" => (isset($storeusername) && $storeusername == 0) ? "CHECKED" : "",
												  "ALWAYSSIG_YES" => ($alwayssig) ? "CHECKED" : "",
												  "ALWAYSSIG_NO" => (!$alwayssig) ? "CHECKED" : "",
												  "ALWAYSBBCODE_YES" => ($alwaysbbcode) ? "CHECKED" : "",
												  "ALWAYSBBCODE_NO" => (!$alwaysbbcode) ? "CHECKED" : "",
												  "ALWAYSHTML_YES" => ($alwayshtml) ? "CHECKED" : "",
												  "ALWAYSHTML_NO" => (!$alwayshtml) ? "CHECKED" : "",
												  "ALWAYSSMILE_YES" => ($alwayssmile) ? "CHECKED" : "",
												  "ALWAYSSMILE_NO" => (!$alwayssmile) ? "CHECKED" : "",
												  "LANGUAGE_SELECT" => language_select($default_lang, "lang"),
												  "THEME_SELECT" => theme_select($theme, $db),
												  "TIMEZONE_SELECT" => tz_select($timezone),
												  "L_ICQNUMBER" => $l_icqnumber,
												  "L_STORECOOKIE" => $l_storecookie,
												  "L_MESSENGER" => $l_messenger,
												  "L_YAHOO" => $l_yahoo,
												  "L_WEBSITE" => $l_website,
												  "L_AIM" => $l_aim,
												  "L_FROM" => $l_from,
												  "L_OCC" => $l_occupation,
												  "L_ALWAYSSMILE" => $l_alwayssmile,
												  "L_BOARDLANG" => $l_boardlang,
												  "L_BOARDTHEME" => $l_boardtheme,
												  "L_TIMEZONE" => $l_timezone,
												  "L_YES" => $l_yes,
												  "L_NO" => $l_no,
												  "L_INTERESTS" => $l_interests,
												  "L_USERUNIQ" => $l_useruniq,
												  "L_ALWAYSBBCODE" => $l_alwaysbbcode,
												  "L_ALWAYSHTML" => $l_alwayshtml,
												  "L_ALWAYSSIG" => $l_alwayssig,
												  "L_SIGNATURE" => $l_signature,
												  "L_SIGEXPLAIN" => $l_sigexplain,
												  "L_PREFERENCES" => $l_preferences,
												  "L_PUBLICMAIL" => $l_publicmail,
												  "L_ITEMSREQ" => $l_itemsreq,
												  "MODE" => $mode,
												  "L_REGINFO" => $l_reginfo,
												  "L_PROFILEINFO" => $l_profileinfo,
												  "L_CONFIRM" => $l_confirm,
												  "L_EMAILADDRESS" => $l_emailaddress));
			$template->pparse("body");
			include('includes/page_tail.'.$phpEx);
		}
	break;
	case 'activate':
		$sql = "SELECT user_id FROM ".USERS_TABLE." WHERE user_actkey = '$act_key'";
		if($result = $db->sql_query($sql))
		{
			if($num = $db->sql_numrows($result))
			{
				$rowset = $db->sql_fetchrowset($result);
				$sql_update = "UPDATE ".USERS_TABLE." SET user_active = 1, user_actkey = '' WHERE user_id = ".$rowset[0]["user_id"];
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