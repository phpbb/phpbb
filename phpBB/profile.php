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
$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

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
	global $db, $lang;

	$sql = "SELECT themes_id, themes_name
	  			FROM " . THEMES_TABLE . " 
	  			ORDER BY themes_name";
	if($result = $db->sql_query($sql))
	{
		$num = $db->sql_numrows($result);
		$rowset = $db->sql_fetchrowset($result);

		if($num)
		{
			$theme_select = "<select name=\"theme\">\n";
			for($i = 0; $i < $num; $i++)
			{
				if(stripslashes($rowset[$i]['themes_name']) == $default || $rowset[$i]['themes_id'] == $default)
				{
					$selected = " selected";
				}
				else
				{
					$selected = "";
				}
				$theme_select .= "\t<option value=\"" . $rowset[$i]['themes_id'] ."\"$selected>" . stripslashes($rowset[$i]['themes_name']) . "</option>\n";
			}
			$theme_select .= "</select>\n";
		}
		else
		{
			$theme_select = "<select name=\"theme\"><option value=\"-1\">" . $lang['No_themes'] . "</option></select>"; 
		}
	}
	else
	{
		message_die(GENERAL_ERROR, "Couldn't query themes table", "", __LINE__, __FILE__, $sql);
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
			"+9" => "(GMT +9:00 hours) Osaka, Sapporo, Seoul, Tokyo, Yakutsk",
			"+9.5" => "(GMT +9:30 hours) Adelaide, Darwin",
			"+10" => "(GMT +10:00 hours) Guam, Melbourne, Papua New Guinea, Sydney, Vladivostok",
			"+11" => "(GMT +11:00 hours) Magadan, New Caledonia, Solomon Islands",
			"+12" => "(GMT +12:00 hours) Auckland, Wellington, Fiji, Kamchatka, Marshall Island");

	while(list($offset, $zone) = each($tz_array))
	{
		$selected = ($offset == $default) ? " selected" : "";
		$tz_select .= "\t<option value=\"$offset\"$selected>$zone</option>\n";
	}
	$tz_select .= "</select>\n";

	return($tz_select);
}

//
// End of functions defns
//

//
// Start of program proper
//
if(isset($HTTP_GET_VARS['mode']) || isset($HTTP_POST_VARS['mode']))
{
	$mode = ($HTTP_GET_VARS['mode']) ? $HTTP_GET_VARS['mode'] : $HTTP_POST_VARS['mode'];
	//
	// Begin page proper
	//
	if($mode == "viewprofile")
	{
		$pagetype = "profile";
		$page_title = "$l_profile";

		//
		// Output page header and
		// profile_view template
		//
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			"body" => "profile_view_body.tpl",
			"jumpbox" => "jumpbox.tpl")
		);
		$jumpbox = make_jumpbox();
		$template->assign_vars(array(
			"JUMPBOX_LIST" => $jumpbox,
			"SELECT_NAME" => POST_FORUM_URL)
		);
		$template->assign_var_from_handle("JUMPBOX", "jumpbox");
		//
		// End header
		//

		if(!$HTTP_GET_VARS[POST_USERS_URL])
		{
			message_die(GENERAL_ERROR, "You must supply the user ID number of the user you want to view", "", __LINE__, __FILE__);
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
			$email_addr = str_replace("@", " at ", $profiledata['user_email']);
			$email = "<a href=\"mailto:$email_addr\">$email_addr</a>";
		}
		else
		{
			$email = $l_hidden;
		}

		if($members[$i]['user_icq'])
		{
			$icq_status = "<a href=\"http://wwp.icq.com/" . $members[$i]['user_icq'] . "#pager\"><img src=\"http://online.mirabilis.com/scripts/online.dll?icq=" . $members[$i]['user_icq'] . "&img=5\" alt=\"$l_icqstatus\" border=\"0\"></a>";

			$icq_add = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=" . $members[$i]['user_icq'] . "\"><img src=\"" . $images['icq'] . "\" alt=\"$l_icq\" border=\"0\"></a>";
		}
		else
		{
			$icq_status = "&nbsp;";
			$icq_add = "&nbsp;";
		}

		$aim = ($members[$i]['user_aim']) ? "<a href=\"aim:goim?screenname=" . $members[$i]['user_aim'] . "&message=Hello+Are+you+there?\"><img src=\"" . $images['aim'] . "\" border=\"0\"></a>" : "&nbsp;";

		$msnm = ($members[$i]['user_msnm']) ? "<a href=\"profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=$poster_id\"><img src=\"" . $images['msnm'] . "\" border=\"0\"></a>" : "&nbsp;";

		$yim = ($members[$i]['user_yim']) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=" . $members[$i]['user_yim'] . "&.src=pg\"><img src=\"" . $images['yim'] . "\" border=\"0\"></a>" : "&nbsp;";

		$search = "<a href=\"" . append_sid("search.$phpEx?a=" . urlencode($members[$i]['username']) . "&f=all&b=0&d=DESC&c=100&dosearch=1") . "\"><img src=\"" . $images['search_icon'] . "\" border=\"0\"></a>";

		$template->assign_vars(array(
			"USERNAME" => stripslashes($profiledata['username']),
			"JOINED" => create_date($board_config['default_dateformat'], $profiledata['user_regdate'], $board_config['default_timezone']),
			"POSTS_PER_DAY" => $posts_per_day,
			"POSTS" => $profiledata['user_posts'],
			"PERCENTAGE" => $percentage . "%",
			"EMAIL" => $email,
			"ICQ_STATUS" => $icq_status,
			"AIM" => stripslashes($profiledata['user_aim']),
			"MSN" => stripslashes($profiledata['user_msnm']),
			"YIM" => stripslashes($profiledata['user_yim']),
			"WEBSITE" => stripslashes($profiledata['user_website']),
			"LOCATION" => stripslashes($profiledata['user_from']),
			"OCCUPATION" => stripslashes($profiledata['user_occ']),
			"INTERESTS" => stripslashes($profiledata['user_interests']),
			"AVATAR_IMG" => $board_config['avatar_path'] . "/" . stripslashes($profiledata['user_avatar']),

			"L_VIEWING_PROFILE" => $l_viewing_profile,
			"L_USERNAME" => $lang['Username'],
			"L_VIEW_USERS_POSTS" => $l_view_users_posts,
			"L_JOINED" => $l_joined,
			"L_PER_DAY" => $l_per_day,
			"L_OF_TOTAL" => $l_of_total,
			"L_EMAIL_ADDRESS" => $l_emailaddress,
			"L_ICQ_NUMBER" => $l_icq_number,
			"L_YAHOO" => $l_yahoo,
			"L_AIM" => $l_aim,
			"L_WEBSITE" => $l_website,
			"L_MESSENGER" => $l_messenger,
			"L_LOCATION" => $l_from,
			"L_OCCUPATION" => $l_occupation,
			"L_INTERESTS" => $l_interests,

			"U_SEARCH_USER" => append_sid("search.$phpEx?a=" . urlencode($profiledata['username']) . "&f=all&b=0&d=DESC&c=100&dosearch=1"),
			"U_USER_WEBSITE" => stripslashes($profiledata['user_website']),

			"S_PROFILE_ACTION" => append_sid("profile.$phpEx"))
		);

		$template->pparse("body");

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

	}
	else if($mode == "editprofile" || $mode == "register")
	{

		if(!$userdata['session_logged_in'] && $mode == "editprofile")
		{
			header(append_sid("Location: login.$phpEx?forward_page=$PHP_SELF&mode=editprofile"));
		}

		$pagetype = ($mode == "edit") ? "editprofile" : "register";
		$page_title = ($mode == "edit") ? $lang['Edit_profile'] : $lang['Register'];

		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		//
		// Start processing for output
		//
		if($mode == "register" && !isset($HTTP_POST_VARS['agreed']) && !isset($HTTP_GET_VARS['agreed']))
		{
			if(!isset($HTTP_POST_VARS['agreed']) && !isset($HTTP_GET_VARS['agreed']))
			{
				//
				// Load agreement template since user has not yet
				// agreed to registration conditions/coppa
				//
				$template->set_filenames(array(
					"body" => "agreement.tpl",
					"jumpbox" => "jumpbox.tpl")
				);
				$jumpbox = make_jumpbox();
				$template->assign_vars(array(
					"JUMPBOX_LIST" => $jumpbox,
					"SELECT_NAME" => POST_FORUM_URL)
				);
				$template->assign_var_from_handle("JUMPBOX", "jumpbox");
				$template->assign_vars(array(
					"COPPA" => $coppa,

					"U_AGREE_OVER13" => append_sid("profile.$phpEx?mode=register&agreed=true"),
					"U_AGREE_UNDER13" => append_sid("profile.$phpEx?mode=register&agreed=true&coppa=true"))
				);
				$template->pparse("body");

				include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
			}
		}
		else if(isset($HTTP_POST_VARS['submit']) || $mode == "register")
		{
			if($mode == "editprofile")
			{
				$user_id = $HTTP_POST_VARS['user_id'];
			}
			$username = (!empty($HTTP_POST_VARS['username'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['username']))) : "";
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

			$user_avatar_url = (!empty($HTTP_POST_VARS['avatarurl'])) ? $HTTP_POST_VARS['avatarurl'] : "";
			$user_avatar_loc = ($HTTP_POST_FILES['avatar']['tmp_name'] != "none") ? $HTTP_POST_FILES['avatar']['tmp_name'] : "";
			$user_avatar_name = (!empty($HTTP_POST_FILES['avatar']['name'])) ? $HTTP_POST_FILES['avatar']['name'] : "";
			$user_avatar_size = (!empty($HTTP_POST_FILES['avatar']['size'])) ? $HTTP_POST_FILES['avatar']['size'] : 0;
			$user_avatar_type = (!empty($HTTP_POST_FILES['avatar']['type'])) ? $HTTP_POST_FILES['avatar']['type'] : "";
			$user_avatar = (empty($user_avatar_loc) && $mode == "editprofile") ? $userdata['user_avatar'] : "";
		}

		if(isset($HTTP_POST_VARS['submit']))
		{
			$error = FALSE;

			$passwd_sql = "";
			if($mode == "editprofile")
			{
				if($user_id != $userdata['user_id'])
				{
					$error = TRUE;
					$error_msg = $lang['Wrong_Profile'];
				}
			}
			else if($mode == "register")
			{
				$coppa = (!$HTTP_POST_VARS['coppa'] && !$HTTP_GET_VARS['coppa']) ? 0 : 1;

				if(empty($username) || empty($password) || empty($password_confirm) || empty($email))
				{
					$error = TRUE;
					$error_msg = $lang['Fields_empty'];
				}
			}

			//
			// Do a ban check on this email address
			//
			$sql = "SELECT ban_email 
				FROM " . BANLIST_TABLE; 
			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Couldn't obtain email ban information.", "", __LINE__, __FILE__, $sql);
			}
			$ban_email_list = $db->sql_fetchrowset($result);
			for($i = 0; $i < count($ban_email_list); $i++)
			{
				if( eregi("^" . $ban_email_list[$i]['ban_email'] . "$", $email) )
				{
					$error = TRUE;
					if(isset($error_msg))
					{
						$error_msg .= "<br />";
					}
					$error_msg .= $lang['Sorry_banned_email'];
				}
			}

			if(!empty($password) && !empty($password_confirm))
			{
				// The user wants to change their password, isn't that cute..
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

			if($board_config['allow_namechange'] || $mode == "register")
			{
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
			}

			if($mode == "register")
			{
				//
				// The AUTO_INCREMENT field in MySQL v3.23 doesn't work
				// correctly when there is a row with -1 in that field
				// so we have to explicitly get the next user ID
				//
				$sql = "SELECT MAX(user_id) AS total
					FROM " . USERS_TABLE;
				if($result = $db->sql_query($sql))
				{
					$user_id_row = $db->sql_fetchrow($result);
					$new_user_id = $user_id_row['total'] + 1;

					unset($result);
					unset($user_id_row);
				}
				else
				{
					message_die(GENERAL_ERROR, "Couldn't obtained next user_id information.", "", __LINE__, __FILE__, $sql);
				}
			}

			if($board_config['allow_avatar_upload'] && !$error)
			{
				//
				// Only allow one type of upload, either a 
				// filename or a URL
				//
				if(!empty($user_avatar_loc) && !empty($user_avatar_url))
				{
					$error = TRUE;
					if(isset($error_msg))
					{
						$error_msg .= "<br />";
					}
					$error_msg .= $lang['Only_one_avatar'];
				}

				if(isset($HTTP_POST_VARS['avatardel']) && $mode == "editprofile")
				{
					if(file_exists("./".$board_config['avatar_path']."/".$userdata['user_avatar']))
					{
						@unlink("./".$board_config['avatar_path']."/".$userdata['user_avatar']);
						$avatar_sql = ", user_avatar = ''";
					}
				}
				else if(!empty($user_avatar_loc))
				{
					if($board_config['allow_avatar_upload'])
					{
						if(file_exists($user_avatar_loc) && ereg(".jpg$|.gif$|.png$", $user_avatar_name))
						{
							if($user_avatar_size <= $board_config['avatar_filesize'] && $avatar_size > 0)
							{
								$error_type = false;

								//
								// Opera appends the image name after the type, not big, not clever!
								//
								preg_match("'(image\/[a-z]+)'", $user_avatar_type, $user_avatar_type);
								$user_avatar_type = $user_avatar_type[1];

								switch($user_avatar_type)
								{
									case "image/jpeg":
										$imgtype = '.jpg';
										break;
									case "image/pjpeg":
										$imgtype = '.jpg';
										break;
									case "image/gif":
										$imgtype = '.gif';
										break;
									case "image/png":
										$imgtype = '.png';
										break;
									default:
										$error = true;
										$error_msg = (!empty($error_msg)) ? $error_msg . "<br>" . $lang['Avatar_filetype'] : $lang['Avatar_filetype'];
										break;
								}

								if(!$error)
								{
									list($width, $height) = getimagesize($user_avatar_loc);

									if( $width <= $board_config['avatar_max_width'] && 
										$height <= $board_config['avatar_max_height'] )
									{
										$user_id = ($mode == "register") ? $new_user_id : $userdata['user_id'];

										$avatar_filename = $user_id . $imgtype;

										if($mode == "editprofile")
										{
											if(file_exists("./" . $board_config['avatar_path'] . "/" . $user_id))
											{
												@unlink("./" . $board_config['avatar_path'] . "/" . $user_id);
											}
										}
										@copy($user_avatar_loc, "./" . $board_config['avatar_path'] . "/$avatar_filename");
										$avatar_sql = ", user_avatar = '$avatar_filename'";
									}
									else
									{
										$error = true;
										$error_msg = (!empty($error_msg)) ? $error_msg . "<br>" . $lang['Avatar_imagesize'] : $lang['Avatar_imagesize'];
									}
								}
							}
							else
							{
								$error = true;
								$error_msg = (!empty($error_msg)) ? $error_msg . "<br>" . $lang['Avatar_filesize'] : $lang['Avatar_filesize'];
							}
						}
						else
						{
							$error = true;
							$error_msg = (!empty($error_msg)) ? $error_msg . "<br>" . $lang['Avatar_filetype'] : $lang['Avatar_filetype'];
						}
					} // if ... allow_avatar_upload
				}
				else if(!empty($user_avatar_url))
				{
					if($board_config['allow_avatar_upload'])
					{
						//
						// First check what port we should connect 
						// to, look for a :[xxxx]/ or, if that doesn't
						// exist assume port 80 (http)
						//
						preg_match("/^(http:\/\/)?([^\/]+?)\:?([0-9]*)\/(.*)$/", $user_avatar_url, $url_ary);

						if(!empty($url_ary[4]))
						{
							$port = (!empty($url_ary[3])) ? $url_ary[3] : 80;

							$fsock = fsockopen($url_ary[2], $port, $errno, $errstr);
							if($fsock)
							{
								$base_get = "/" . $url_ary[4];

								//
								// Uses HTTP 1.1, could use HTTP 1.0 ...
								//
								fputs($fsock, "GET $base_get HTTP/1.1\r\n");
								fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
								fputs($fsock, "Connection: close\r\n\r\n"); 

								unset($avatar_data);
								while(!feof($fsock))
								{ 
									$avatar_data .= fread($fsock, $board_config['avatar_filesize']); 
								} 
								fclose($fsock); 

								if(preg_match("/Content-Length\: ([0-9]+)[^\/]+Content-Type\: (image\/[a-z]+)[\s]+/i", $avatar_data, $file_data))
								{
									$file_size = $file_data[1];
									$file_type = $file_data[2];

									switch($file_type)
									{
										case "image/jpeg":
											$imgtype = '.jpg';
											break;
										case "image/pjpeg":
											$imgtype = '.jpg';
											break;
										case "image/gif":
											$imgtype = '.gif';
											break;
										case "image/png":
											$imgtype = '.png';
											break;
										default:
											$error = true;
											$error_msg = (!empty($error_msg)) ? $error_msg . "<br>" . $lang['Avatar_filetype'] : $lang['Avatar_filetype'];
											break;
									}

									if(!$error && $file_size > 0 && $file_size < $board_config['avatar_filesize'])
									{
										$avatar_data = substr($avatar_data, strlen($avatar_data) - $file_size, $file_size);

										$tmp_filename = tempnam ("/tmp", $userdata['user_id'] . "-");
										$fptr = fopen($tmp_filename, "wb");
										$bytes_written = fwrite($fptr, $avatar_data, $file_size);
										fclose($fptr);

										if($bytes_written == $file_size)
										{
											list($width, $height) = getimagesize($tmp_filename);

											if( $width <= $board_config['avatar_max_width'] && $height <= $board_config['avatar_max_height'] )
											{
												$user_id = ($mode == "register") ? $new_user_id : $userdata['user_id'];

												$avatar_filename = $user_id . $imgtype;

												if($mode == "editprofile")
												{
													if(file_exists("./" . $board_config['avatar_path'] . "/" . $user_id))
													{
														@unlink("./" . $board_config['avatar_path'] . "/" . $user_id);
													}
												}
												copy($tmp_filename, "./" . $board_config['avatar_path'] . "/$avatar_filename");
													$avatar_sql = ", user_avatar = '$avatar_filename'";
												@unlink($tmp_filename);
											}
											else
											{
												//
												// Image too large
												//
												@unlink($tmp_filename);
												$error = true;
												$error_msg = (!empty($error_msg)) ? $error_msg . "<br>" . $lang['Avatar_imagesize'] : $lang['Avatar_imagesize'];
											}
										}
										else
										{
											//
											// Error writing file
											//
											@unlink($tmp_filename);
											message_die(GENERAL_ERROR, "Could not write avatar file to local storage. Please contact the board administrator with this message", "", __LINE__, __FILE__);
										}
									}
								}
								else
								{
									//
									// No data
									//
									$error = true;
									$error_msg = (!empty($error_msg)) ? $error_msg . "<br>" . $lang['File_no_data'] : $lang['File_no_data'];
								}
							}
							else
							{
								//
								// No connection
								//
								$error = true;
								$error_msg = (!empty($error_msg)) ? $error_msg . "<br>" . $lang['No_connection_URL'] : $lang['No_connection_URL'];
							}
						}
						else
						{
							$error = true;
							$error_msg = (!empty($error_msg)) ? $error_msg . "<br>" . $lang['Incomplete_URL'] : $lang['Incomplete_URL'];
						}
					} // if ... allow_avatar_upload
				}
			}

			if(!$error)
			{
				if($mode == "editprofile")
				{
					$sql = "UPDATE " . USERS_TABLE . "
						SET " . $username_sql . $passwd_sql . "user_email = '$email', user_icq = '$icq', user_website = '$website', user_occ = '$occupation', user_from = '$location', user_interests = '$interests', user_sig = '$signature', user_viewemail = $viewemail, user_aim = '$aim', user_yim = '$yim', user_msnm = '$msn', user_attachsig = $attachsig, user_allowsmile = $allowsmilies, user_allowhtml = $allowhtml, user_allowbbcode = $allowbbcode, user_allow_viewonline = $allowviewonline, user_notify_pm = $notifypm, user_timezone = $user_timezone, user_dateformat = '$user_dateformat', user_lang = '$user_lang', user_template = '$user_template', user_theme = $user_theme" . $avatar_sql . "
						WHERE user_id = $user_id";

					if($result = $db->sql_query($sql))
					{
						message_die(GENERAL_MESSAGE, $lang['Profile_updated']);
					}
					else
					{
						message_die(GENERAL_ERROR, "Could not update users table", "", __LINE__, __FILE__, $sql);
					}
				}
				else
				{
					//
					// Get current date
					//
					$regdate = get_gmt_ts();

					if(SQL_LAYER != "mssql")
					{
						$user_id_sql = "user_id,";
						$user_id_value = $new_user_id . ", ";
					}
					else
					{
						$user_id_sql = "";
						$user_id_value = "";
					}

					$sql = "INSERT INTO " . USERS_TABLE . "	(" . $user_id_sql . "username, user_regdate, user_password, user_email, user_icq, user_website, user_occ, user_from, user_interests, user_sig, user_avatar, user_viewemail, user_aim, user_yim, user_msnm, user_attachsig, user_allowsmile, user_allowhtml, user_allowbbcode, user_allow_viewonline, user_notify_pm, user_timezone, user_dateformat, user_lang, user_template, user_theme, user_level, user_allow_pm, user_active, user_actkey)
						VALUES (" . $user_id_value . "'$username', $regdate, '$password', '$email', '$icq', '$website', '$occupation', '$location', '$interests', '$signature', '$avatar_filename', $viewemail, '$aim', '$yim', '$msn', $attachsig, $allowsmilies, $allowhtml, $allowbbcode, $allowviewonline, $notifypm, $user_timezone, '$user_dateformat', '$user_lang', '$user_template', $user_theme, 0, 1, ";

					if($board_config['require_activation'] || $coppa == 1)
					{
						$act_key = generate_activation_key();
						$sql .= "0, '$act_key')";
					}
					else
					{
						$sql .= "1, '')";
					}

					if($result = $db->sql_query($sql, BEGIN_TRANSACTION))
					{
						$sql = "INSERT INTO " . GROUPS_TABLE . " (group_name, group_description, group_single_user, group_moderator)
							VALUES ('$username', 'Personal User', 1, 0)";
						if($result = $db->sql_query($sql))
						{
							$group_id = $db->sql_nextid();

							$sql = "INSERT INTO " . USER_GROUP_TABLE . " (user_id, group_id, user_pending)
								VALUES ($new_user_id, $group_id, 0)";
							if($result = $db->sql_query($sql, END_TRANSACTION))
							{
								if($board_config['require_activation'])
								{
									$msg = $lang['Account_inactive'];
									$email_msg = $lang['Welcome_email_activate'];
								}
								else if($coppa)
								{
									$msg = $lang['COPPA'];
									$email_msg = $lang['Welcome_COPPA'];
								}
								else
								{
									$msg = $lang['Account_added']; //$l_acountadded;
									$email_msg = $lang['Welcome_email']; //$l_welcomemail;
								}

								if(!$coppa)
								{
									$email_msg .= "\r\n" . $board_config['board_email'];
									$email_headers = "From: " . $board_config['board_email_from'] . "\r\n";
								
									if($board_config['smtp_delivery'] && $board_config['smtp_host'] != "")
									{
										include($phpbb_root_path . 'includes/smtp.'.$phpEx);
										smtpmail($email, $lang['Welcome_subject'], $email_msg, $email_headers);
									}
									else
									{
										mail($email, $lang['Welcome_subject'], $email_msg, $email_headers);
									}
								}

								message_die(GENERAL_MESSAGE, $msg);
							}
							else
							{
								message_die(GENERAL_ERROR, "Couldn't insert data into user_group table", "", __LINE__, __FILE__, $sql);
							}
						}
						else
						{
							message_die(GENERAL_ERROR, "Couldn't insert data into groups table", "", __LINE__, __FILE__, $sql);	
						}
					}
					else
					{
						message_die(GENERAL_ERROR, "Couldn't insert data into users table", "", __LINE__, __FILE__, $sql);
					}
				} // if mode == register
			}
			else
			{
				$template->set_filenames(array(
					"reg_header" => "error_body.tpl")
				);
				$template->assign_vars(array(
					"ERROR_MESSAGE" => $error_msg)
				);
				$template->pparse("reg_header");
			}

		}
		else if($mode == "editprofile")
		{
			$user_id = $userdata['user_id'];
			$username = stripslashes($userdata['username']);
			$email = $userdata['user_email'];
			$password = "";
			$password_confirm = "";

			$icq = $userdata['user_icq'];
			$aim = stripslashes($userdata['user_aim']);
			$msn = stripslashes($userdata['user_msnm']);
			$yim = stripslashes($userdata['user_yim']);

			$website = stripslashes($userdata['user_website']);
			$location = stripslashes($userdata['user_from']);
			$occupation = stripslashes($userdata['user_occ']);
			$interests = stripslashes($userdata['user_interests']);
			$signature = stripslashes($userdata['user_sig']);

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
		}

		if(!isset($coppa))
		{
			$coppa = FALSE;
		}

		if(!isset($user_template))
		{
			$selected_template = $board_config['default_template'];
		}

		$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '"><input type="hidden" name="agreed" value="true"><input type="hidden" name="coppa" value="' . $coppa . '">';
		if($mode == "editprofile")
		{
			$s_hidden_fields .= '<input type="hidden" name="user_id" value="' . $userdata['user_id'] . '">';
		}
			
		$template->set_filenames(array(
			"body" => "profile_add_body.tpl",
			"jumpbox" => "jumpbox.tpl")
		);

		$jumpbox = make_jumpbox();
		$template->assign_vars(array(
			"JUMPBOX_LIST" => $jumpbox,
			"SELECT_NAME" => POST_FORUM_URL)
		);
		$template->assign_var_from_handle("JUMPBOX", "jumpbox");

		$template->assign_vars(array(
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
			"VIEW_EMAIL_YES" => ($viewemail) ? "CHECKED" : "",
			"VIEW_EMAIL_NO" => (!$viewemail) ? "CHECKED" : "", 
			"HIDE_USER_YES" => (!$allowviewonline) ? "CHECKED" : "",
			"HIDE_USER_NO" => ($allowviewonline) ? "CHECKED" : "", 
			"NOTIFY_PM_YES" => ($notifypm) ? "CHECKED" : "", 
			"NOTIFY_PM_NO" => (!$notifypm) ? "CHECKED" : "", 
			"ALWAYS_ADD_SIGNATURE_YES" => ($attachsig) ? "CHECKED" : "",
			"ALWAYS_ADD_SIGNATURE_NO" => (!$attachsig) ? "CHECKED" : "",
			"ALWAYS_ALLOW_BBCODE_YES" => ($allowbbcode) ? "CHECKED" : "",
			"ALWAYS_ALLOW_BBCODE_NO" => (!$allowbbcode) ? "CHECKED" : "",
			"ALWAYS_ALLOW_HTML_YES" => ($allowhtml) ? "CHECKED" : "",
			"ALWAYS_ALLOW_HTML_NO" => (!$allowhtml) ? "CHECKED" : "",
			"ALWAYS_ALLOW_SMILIES_YES" => ($allowsmilies) ? "CHECKED" : "",
			"ALWAYS_ALLOW_SMILIES_NO" => (!$allowsmilies) ? "CHECKED" : "",
			"ALLOW_AVATAR" => $board_config['allow_avatar_upload'],
			"AVATAR" => ($user_avatar != "") ? "<img src=\"".$board_config['avatar_path']."/$user_avatar\">" : "",
			"AVATAR_SIZE" => $board_config['avatar_filesize'], 
			"LANGUAGE_SELECT" => language_select($user_lang),
			"THEME_SELECT" => theme_select($user_theme),
			"TIMEZONE_SELECT" => tz_select($user_timezone),
			"DATE_FORMAT" => stripslashes($user_dateformat),
			"TEMPLATE_SELECT" => template_select($user_template),

			"L_PASSWORD_IF_CHANGED" => ($mode == "editprofile") ? $l_password_if_changed : "",
			"L_PASSWORD_CONFIRM_IF_CHANGED" => ($mode == "editprofile") ? $l_password_confirm_if_changed : "",
			"L_SUBMIT" => $lang['Submit'], 
			"L_RESET" => $lang['Reset'], 
			"L_ICQ_NUMBER" => $lang['ICQ'],
			"L_MESSENGER" => $lang['MSNM'],
			"L_YAHOO" => $lang['YIM'],
			"L_WEBSITE" => $lang['Website'],
			"L_AIM" => $lang['AIM'],
			"L_LOCATION" => $lang['From'],
			"L_OCCUPATION" => $l_occupation,
			"L_BOARD_LANGUAGE" => $lang['Board_lang'],
			"L_BOARD_THEME" => $lang['Board_theme'],
			"L_BOARD_TEMPLATE" => $l_boardtemplate,
			"L_TIMEZONE" => $l_timezone,
			"L_DATE_FORMAT" => $l_date_format,
			"L_DATE_FORMAT_EXPLANATION" => $l_date_format_explanation,
			"L_YES" => $lang['Yes'],
			"L_NO" => $lang['No'],
			"L_INTERESTS" => $l_interests,
			"L_USER_UNIQUE" => $l_useruniq,
			"L_ALWAYS_ALLOW_SMILIES" => $lang['Always_smile'],
			"L_ALWAYS_ALLOW_BBCODE" => $lang['Always_bbcode'],
			"L_ALWAYS_ALLOW_HTML" => $lang['Always_html'], 
			"L_HIDE_USER" => $lang['Hide_user'], 
			"L_ALWAYS_ADD_SIGNATURE" => $lang['Always_add_sig'],

			"L_AVATAR_PANEL" => $lang['Avatar_panel'],
			"L_AVATAR_EXPLAIN" => $lang['Avatar_explain'],
			"L_UPLOAD_AVATAR_FILE" => $lang['Upload_Avatar_file'],
			"L_UPLOAD_AVATAR_URL" => $lang['Upload_Avatar_URL'], 
			"L_UPLOAD_AVATAR_URL_EXPLAIN" => $lang['Upload_Avatar_URL_explain'], 
			"L_AVATAR_GALLERY" => $lang['Select_from_gallery'], 
			"L_SHOW_GALLERY" => $lang['Avatar_gallery'], 
			"L_LINK_REMOTE_AVATAR" => $lang['Link_remote_Avatar'], 
			"L_LINK_REMOTE_AVATAR_EXPLAIN" => $lang['Link_remote_Avatar_explain'], 
			"L_DELETE_AVATAR" => $lang['Delete_Image'],
			"L_CURRENT_IMAGE" => $lang['Current_Image'],

			"L_SIGNATURE" => $l_signature,
			"L_SIGNATURE_EXPLAIN" => $l_sigexplain, 
			"L_NOTIFY_ON_PRIVMSG" => $lang['Notify_on_privmsg'], 
			"L_PREFERENCES" => $l_preferences,
			"L_PUBLIC_VIEW_EMAIL" => $l_publicmail,
			"L_ITEMS_REQUIRED" => $l_itemsreq,
			"L_REGISTRATION_INFO" => $l_reginfo,
			"L_PROFILE_INFO" => $l_profile_info,
			"L_PROFILE_INFO_NOTICE" => $l_profile_info_notice,
			"L_CONFIRM" => $l_confirm,
			"L_EMAIL_ADDRESS" => $l_emailaddress,

			"S_ALLOW_AVATAR_UPLOAD" => $board_config['allow_avatar_upload'], 
			"S_ALLOW_AVATAR_LOCAL" => $board_config['allow_avatar_local'],
			"S_ALLOW_AVATAR_REMOTE" => $board_config['allow_avatar_remote'], 
			"S_HIDDEN_FIELDS" => $s_hidden_fields, 
			"S_PROFILE_ACTION" => append_sid("profile.$phpEx"))
		);

		$template->pparse("body");

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
	else if($mode == "activate")
	{
		$sql = "SELECT user_id
				FROM " . USERS_TABLE . "
				WHERE user_actkey = '$act_key'";
			if($result = $db->sql_query($sql))
			{
				if($num = $db->sql_numrows($result))
				{
					$rowset = $db->sql_fetchrowset($result);

					$sql_update = "UPDATE " . USERS_TABLE . "
						SET user_active = 1, user_actkey = ''
						WHERE user_id = " . $rowset[0]['user_id'];
					if($result = $db->sql_query($sql_update))
					{
						message_die(GENERAL_MESSAGE, $lang['Account_active']);
					}
					else
					{
						message_die(GENERAL_ERROR, "Couldn't update users table", "", __LINE__, __FILE__, $sql_update);
					}
				}
				else
				{
					message_die(GENERAL_ERROR, $lang['']); //wrongactiv
				}
			}
			else
			{
				message_die(GENERAL_ERROR, "Couldn't obtain user information", "", __LINE__, __FILE__, $sql);
			}
			break;
	}
}

?>