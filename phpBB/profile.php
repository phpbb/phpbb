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
				$selected = " selected";
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
			$selected = " selected";
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
// Start of program proper
//
if(isset($HTTP_GET_VARS['mode']) || isset($HTTP_POST_VARS['mode']))
{
	$mode = ($HTTP_GET_VARS['mode']) ? $HTTP_GET_VARS['mode'] : $HTTP_POST_VARS['mode'];
	//
	// Begin page proper
	//
	switch($mode)
	{
		case 'viewprofile':
			$pagetype = "profile";
			$page_title = "$l_profile";

			//
			// Output page header and
			// profile_view template
			//
			include('includes/page_header.'.$phpEx);

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
				"USERNAME" => stripslashes($profiledata['username']),
				"JOINED" => create_date($board_config['default_dateformat'], $profiledata['user_regdate'], $board_config['default_timezone']),
				"POSTS_PER_DAY" => $posts_per_day,
				"POSTS" => $profiledata['user_posts'],
				"PERCENTAGE" => $percentage . "%",
				"EMAIL" => $email,
				"ICQ" => stripslashes($profiledata['user_icq']),
				"AIM" => stripslashes($profiledata['user_aim']),
				"MSN" => stripslashes($profiledata['user_msnm']),
				"L_YAHOO" => stripslashes($l_yahoo),
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
				"L_AIM" => $l_aim,
				"L_WEBSITE" => $l_website,
				"L_MESSENGER" => $l_messenger,
				"L_LOCATION" => $l_from,
				"L_OCCUPATION" => $l_occupation,
				"L_INTERESTS" => $l_interests,
				"L_AVATAR" => $lang['Avatar'],

				"U_SEARCH_USER" => append_sid("search.$phpEx?a=".urlencode($profiledata['username'])."&f=all&b=0&d=DESC&c=100&dosearch=1"),
				"U_USER_WEBSITE" => stripslashes($profiledata['user_website']),

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

			//
			// Output page header and
			// profile_add template
			//
			include('includes/page_header.'.$phpEx);
			//
			// End header
			//

			if(isset($HTTP_POST_VARS['submit']))
			{
				$user_id = $HTTP_POST_VARS['user_id'];
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
				$interests = (!empty($HTTP_POST_VARS['interests'])) ? trim($HTTP_POST_VARS['interests']) : "";
				$signature = (!empty($HTTP_POST_VARS['signature'])) ? trim(str_replace("<br />", "\n", $HTTP_POST_VARS['signature'])) : "";

				$viewemail = $HTTP_POST_VARS['viewemail'];
				$notifypm = $HTTP_POST_VARS['notifypm'];
				$attachsig = $HTTP_POST_VARS['attachsig'];
				$allowhtml = $HTTP_POST_VARS['allowhtml'];
				$allowbbcode = $HTTP_POST_VARS['allowbbcode'];
				$allowsmilies = $HTTP_POST_VARS['allowsmilies'];
				$allowviewonline = ($HTTP_POST_VARS['allowviewonline']) ? 0 : 1;

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
				$user_avatar = (empty($user_avatar_loc)) ? $userdata['user_avatar'] : "";

				$error = FALSE;

				$passwd_sql = "";
				if($user_id != $userdata['user_id'])
				{
					$error = TRUE;
					$error_msg = $lang['Wrong_Profile'];
				}

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
						$passwd_sql = "user_password = '$password', ";
					}
				}
				else if($password && !$password_confirm)
				{
					$error = TRUE;
					$error_msg = $l_mismatch . "<br />" . $l_tryagain;
				}

				if($board_config['allow_namechange'])
				{
					if($username != $userdata['username'])
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
						else
						{
							$username_sql = "username = '$username', ";
						}
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

					if(isset($HTTP_POST_VARS['avatardel']))
					{
						if(file_exists("./".$board_config['avatar_path']."/".$userdata['user_avatar']))
						{
							@unlink("./".$board_config['avatar_path']."/".$userdata['user_avatar']);
							$avatar_sql = ", user_avatar = ''";
						}
					}
					else if(!empty($user_avatar_loc))
					{
						if(file_exists($user_avatar_loc) && ereg(".jpg$|.gif$|.png$", $user_avatar_name))
						{
							if($user_avatar_size <= $board_config['avatar_filesize'] && $avatar_size > 0)
							{
								$error_type = false;
								switch($user_avatar_type)
								{
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
										$error_msg = (!empty($error_msg)) ? $error_msg."<br>The avatar filetype must be .jpg, .gif or .png" : "The avatar filetype must be .jpg, .gif or .png";
										break;
								}

								if(!$error)
								{
									list($width, $height) = getimagesize($user_avatar_loc);

									if( $width <= $board_config['avatar_max_width'] && 
										$height <= $board_config['avatar_max_height'] )
									{
										$avatar_filename = $userdata['user_id'] . $imgtype;

										if(file_exists("./" . $board_config['avatar_path'] . "/" . $userdata['user_id']))
										{
											@unlink("./" . $board_config['avatar_path'] . "/" . $userdata['user_id']);
										}
										@copy($user_avatar_loc, "./" . $board_config['avatar_path'] . "/$avatar_filename");
										$avatar_sql = ", user_avatar = '$avatar_filename'";
									}
									else
									{
										$error = true;
										$error_msg = (!empty($error_msg)) ? $error_msg . "<br>The avatar must be less than " . $board_config['avatar_max_width'] . " pixels wide and " . $board_config['avatar_max_height'] . " pixels high" : "The avatar must be less than " . $board_config['avatar_max_width'] . " pixels wide and " . $board_config['avatar_max_height'] . " pixels high";
									}
								}
							}
							else
							{
								$error = true;
								$error_msg = (!empty($error_msg)) ? $error_msg."<br>The avatar image file size must more than 0 kB and less than ".round($board_config['avatar_filesize']/1024)." kB" : "The avatar image file size must more than 0 kB and less than ".round($board_config['avatar_filesize']/1024)." kB";
							}
						}
						else
						{
							$error = true;
							$error_msg = (!empty($error_msg)) ? $error_msg."<br>The avatar filetype must be .jpg, .gif or .png" : "The avatar filetype must be .jpg, .gif or .png";
						}
					}
					else if(!empty($user_avatar_url))
					{
						//
						// First check what port we should connect 
						// to, look for a :[xxxx]/ or, if that doesn't
						// exist see whether we're http:// or ftp://
						// if neither of these then assume its http://
						//
						preg_match("/^(http:\/\/)?([^\/]+?)\:?([0-9]*)\/(.*)$/", $user_avatar_url, $url_ary);
						if(!empty($url_ary[3]))
						{
							$port = $url_ary[3];
						}
						else
						{
							$port = 80;
						}

						if(!empty($url_ary[4]))
						{
							$fsock = fsockopen($url_ary[2], $port, $errno, $errstr);
							if($fsock)
							{
								$base_get = "http://" . $url_ary[2] . "/" . $url_ary[4];
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

								if(preg_match("/Content-Length\: ([0-9]+)[^\/]+Content-Type\: ([^.*]+?)[\s]+/i", $avatar_data, $file_data))
								{
									$file_size = $file_data[1];
									$file_type = $file_data[2];

									switch($file_type)
									{
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
											$error_msg = (!empty($error_msg)) ? $error_msg . "<br>The avatar filetype must be .jpg, .gif or .png" : "The avatar filetype must be .jpg, .gif or .png";
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
												$avatar_filename = $userdata['user_id'] . $imgtype;

												if(file_exists("./" . $board_config['avatar_path'] . "/" . $userdata['user_avatar']))
												{
													@unlink("./" . $board_config['avatar_path'] . "/" . $userdata['user_avatar']);
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
												$error_msg = (!empty($error_msg)) ? $error_msg."<br>The avatar image file size must more than 0 kB and less than ".round($board_config['avatar_filesize']/1024)." kB" : "The avatar image file size must more than 0 kB and less than ".round($board_config['avatar_filesize']/1024)." kB";
											}
										}
										else
										{
											//
											// Error writing file
											//
											@unlink($tmp_filename);
											$error = true;
											$error_msg = (!empty($error_msg)) ? $error_msg . "<br>Could not write the file to local storage, please contact the board administrator" : "Could not write the file to local storage, please contact the board administrator";
										}
									}
								}
								else
								{
									//
									// No data
									//
									$error = true;
									$error_msg = (!empty($error_msg)) ? $error_msg . "<br>The file at that URL contains no data" : "The file at that URL contains no data";
								}
							}
							else
							{
								//
								// No connection
								//
								$error = true;
								$error_msg = (!empty($error_msg)) ? $error_msg . "<br>A connection could not be made to that URL" : "A connection could not be made to that URL";
							}
						}
						else
						{
							$error = true;
							$error_msg = (!empty($error_msg)) ? $error_msg . "<br>The URL you entered is incomplete" : "The URL you entered is incomplete";
						}
					}
				}

				if(!$error)
				{

					$sql = "UPDATE ".USERS_TABLE."
						SET " . $username_sql . $passwd_sql . "user_email = '$email', user_icq = '$icq', user_website = '$website', user_occ = '$occupation', user_from = '$location', user_interests = '$interests', user_sig = '$signature', user_viewemail = $viewemail, user_aim = '$aim', user_yim = '$yim', user_msnm = '$msn', user_attachsig = $attachsig, user_allowsmile = $allowsmilies, user_allowhtml = $allowhtml, user_allowbbcode = $allowbbcode, user_allow_viewonline = $allowviewonline, user_notify_pm = $notifypm, user_timezone = $user_timezone, user_dateformat = '$user_dateformat', user_lang = '$user_lang', user_template = '$user_template', user_theme = $user_theme".$avatar_sql."
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
					else
					{
						if(DEBUG)
						{
							$error = $db->sql_error();
							$error_msg = "Could not update the users table.<br>Reason: ".$error['message']."<br>Query: $sql";
						}
						else
						{
							$error_msg = $l_dberror;
						}
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
			}

			$s_hidden_fields = '<input type="hidden" name="user_id" value="' . $userdata['user_id'] . '"><input type="hidden" name="mode" value="' . $mode . '"><input type="hidden" name="agreed" value="true"><input type="hidden" name="coppa" value="0">';
			
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
				"L_HIDE_USER" => $lang['Hide_user'], 
				"L_ALWAYS_ADD_SIGNATURE" => $l_alwayssig,
				"L_AVATAR" => $lang['Avatar'],
				"L_AVATAR_EXPLAIN" => $lang['Avatar_explain'],
				"L_UPLOAD_AVATAR" => $lang['Upload_Avatar'],
				"L_AVATAR_URL" => $lang['Avatar_URL'], 
				"L_AVATAR_GALLERY" => $lang['Avatar_gallery'], 
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

				"S_HIDDEN_FIELDS" => $s_hidden_fields, 
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
			$aim = (!empty($HTTP_POST_VARS['aim'])) ? trim(strip_tags($HTTP_POST_VARS['aim'])) : "";
			$msn = (!empty($HTTP_POST_VARS['msn'])) ? trim(strip_tags($HTTP_POST_VARS['msn'])) : "";
			$yim = (!empty($HTTP_POST_VARS['yim'])) ? trim(strip_tags($HTTP_POST_VARS['yim'])) : "";

			$website = (!empty($HTTP_POST_VARS['website'])) ? trim(strip_tags($HTTP_POST_VARS['website'])) : "";
			$location = (!empty($HTTP_POST_VARS['location'])) ? trim(strip_tags($HTTP_POST_VARS['location'])) : "";
			$occupation = (!empty($HTTP_POST_VARS['occupation'])) ? trim(strip_tags($HTTP_POST_VARS['occupation'])) : "";
			$interests = (!empty($HTTP_POST_VARS['interests'])) ? trim($HTTP_POST_VARS['interests']) : "";
			$signature = (!empty($HTTP_POST_VARS['signature'])) ? trim($HTTP_POST_VARS['signature']) : "";

			$viewemail = (!empty($HTTP_POST_VARS['viewemail'])) ? $HTTP_POST_VARS['viewemail'] : 0;
			$notifypm = (!empty($HTTP_POST_VARS['notifypm'])) ? $HTTP_POST_VARS['notifypm'] : 1;
			$attachsig = (!empty($HTTP_POST_VARS['attachsig'])) ? $HTTP_POST_VARS['attachsig'] : 0;
			$allowhtml = (!empty($HTTP_POST_VARS['allowhtml'])) ? $HTTP_POST_VARS['allowhtml'] : $board_config['allow_html'];
			$allowbbcode = (!empty($HTTP_POST_VARS['allowbbcode'])) ? $HTTP_POST_VARS['allowbbcode'] : $board_config['allow_bbcode'];
			$allowsmilies = (!empty($HTTP_POST_VARS['allowsmilies'])) ? $HTTP_POST_VARS['allowsmilies'] : $board_config['allow_smilies'];
			$allowviewonline = (!empty($HTTP_POST_VARS['allowviewonline'])) ? $HTTP_POST_VARS['allowviewonline'] : 1;

			$user_theme = ($HTTP_POST_VARS['theme']) ? $HTTP_POST_VARS['theme'] : $board_config['default_theme'];
			$user_lang = ($HTTP_POST_VARS['language']) ? $HTTP_POST_VARS['language'] : $board_config['default_lang'];
			$user_timezone = str_replace("+", "", (isset($HTTP_POST_VARS['timezone'])) ? $HTTP_POST_VARS['timezone'] : $board_config['default_timezone']);
			$user_template = ($HTTP_POST_VARS['template']) ? $HTTP_POST_VARS['template'] : $board_config['default_template'];
			$user_dateformat = ($HTTP_POST_VARS['dateformat']) ? trim($HTTP_POST_VARS['dateformat']) : $board_config['default_dateformat'];

			$user_avatar_loc = ($HTTP_POST_FILES['avatar']['tmp_name'] != "none") ? $HTTP_POST_FILES['avatar']['tmp_name'] : "";
			$user_avatar_name = (!empty($HTTP_POST_FILES['avatar']['name'])) ? $HTTP_POST_FILES['avatar']['name'] : "";
			$user_avatar_size = (!empty($HTTP_POST_FILES['avatar']['size'])) ? $HTTP_POST_FILES['avatar']['size'] : 0;
			$user_avatar_type = (!empty($HTTP_POST_FILES['avatar']['type'])) ? $HTTP_POST_FILES['avatar']['type'] : "";

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
				//
				// Load agreement template
				// since user has not yet
				// agreed to registration
				// conditions/coppa
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
					if(!validate_username($username))
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
					
					//
					// Do a ban check on this email address
					//
					$sql = "SELECT ban_email 
						FROM " . BANLIST_TABLE; 
					if(!$result = $db->sql_query($sql))
					{
						error_die(QUERY_ERROR, "Couldn't obtain email ban list information.", __LINE__, __FILE__);
					}
					$ban_email_list = $db->sql_fetchrowset($result);
					for($i = 0; $i < count($ban_email_list); $i++)
					{
						if( eregi("^".$ban_email_list[$i]['ban_email']."$", $email) )
						{
							$error = TRUE;
							if(isset($error_msg))
							{
								$error_msg .= "<br />";
							}
							$error_msg .= $lang['Sorry_banned_email'];
						}
					}
				}

				//
				// The AUTO_INCREMENT field in MySQL v3.23 doesn't work
				// correctly when there is a row with -1 in that field
				// so we have to explicitly get the next user ID.
				//
				$sql = "SELECT MAX(user_id) AS total
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

				$avatar_filename = "";
				if($board_config['allow_avatar_upload'] && !$error)
				{
					if(!empty($user_avatar_loc))
					{
						if(file_exists($user_avatar_loc) && ereg(".jpg$|.gif$|.png$", $user_avatar_name))
						{
							if($user_avatar_size <= $board_config['avatar_filesize'] && $avatar_size > 0)
							{
								$error_type = false;
								switch($user_avatar_type)
								{
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
										$error_type = true;
										break;
								}

								if(!$error_type)
								{
									list($width, $height) = getimagesize($user_avatar_loc);

									if( $width <= $board_config['avatar_max_width'] && 
										$height <= $board_config['avatar_max_height'] )
									{
										$avatar_filename = $new_user_id . $imgtype;

										if(file_exists("./" . $board_config['avatar_path'] . "/" . $new_user_id))
										{
											@unlink("./" . $board_config['avatar_path'] . "/" . $new_user_id);
										}
										@copy($user_avatar_loc, "./" . $board_config['avatar_path'] . "/$avatar_filename");
										$avatar_sql = ", user_avatar = '$avatar_filename'";
									}
									else
									{
										$error = true;
										$error_msg = (!empty($error_msg)) ? $error_msg . "<br>The avatar must be less than " . $board_config['avatar_max_width'] . " pixels wide and " . $board_config['avatar_max_height'] . " pixels high" : "The avatar must be less than " . $board_config['avatar_max_width'] . " pixels wide and " . $board_config['avatar_max_height'] . " pixels high";
									}
								}
								else
								{
									$error = true;
									$error_msg = (!empty($error_msg)) ? $error_msg."<br>The avatar filetype must be .jpg, .gif or .png" : "The avatar filetype must be .jpg, .gif or .png";
								}
							}
							else
							{
								$error = true;
								$error_msg = (!empty($error_msg)) ? $error_msg."<br>The avatar image file size must more than 0 kB and less than ".round($board_config['avatar_filesize']/1024)." kB" : "The avatar image file size must more than 0 kB and less than ".round($board_config['avatar_filesize']/1024)." kB";
							}
						}
						else
						{
							$error = true;
							$error_msg = (!empty($error_msg)) ? $error_msg."<br>The avatar filetype must be .jpg, .gif or .png" : "The avatar filetype must be .jpg, .gif or .png";
						}
					}
				}

				if(isset($HTTP_POST_VARS['submit']) && !$error)
				{

					$md_pass = md5($password);
					$sql = "INSERT INTO ".USERS_TABLE."	(user_id, username, user_regdate, user_password, user_email, user_icq, user_website, user_occ,	user_from, user_interests, user_sig, user_avatar, user_viewemail, user_aim, user_yim, user_msnm, user_attachsig, user_allowsmile, user_allowhtml, user_allowbbcode, user_allow_viewonline, user_notify_pm, user_timezone, user_dateformat, user_lang, user_template, user_theme, user_level, user_active, user_actkey)
						VALUES ($new_user_id, '$username', '$regdate', '$md_pass', '$email', '$icq', '$website', '$occupation', '$location', '$interests', '$signature', '$avatar_filename', '$viewemail', '$aim', '$yim', '$msn', $attachsig, $allowsmilies, '$allowhtml', $allowbbcode, $allowviewonline, $notifypm, $user_timezone, '$user_dateformat', '$user_lang', '$user_template', $user_theme, 0, ";
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
						$sql = "INSERT INTO ".GROUPS_TABLE."
							(group_name, group_description, group_single_user)
							VALUES
							('$username', 'Personal User', 1)";
						if($result = $db->sql_query($sql))
						{
							$group_id = $db->sql_nextid();

							$sql = "INSERT INTO ".USER_GROUP_TABLE."
								(user_id, group_id)
								VALUES
								($new_user_id, $group_id)";
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
									$email_msg .= "\r\n" . $board_config['board_email'];
									mail($email, $l_welcomesubj, $email_msg, "From: ".$board_config['board_email_from']."\r\n");
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

				$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '"><input type="hidden" name="agreed" value="true"><input type="hidden" name="coppa" value="' . $coppa . '">';

				//
				// Load profile_add template
				// to allow user to insert
				// new user reg details
				//
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
					"SIGNATURE" => stripslashes($signature),
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
					"LANGUAGE_SELECT" => language_select($user_lang),
					"THEME_SELECT" => theme_select($user_theme),
					"TIMEZONE_SELECT" => tz_select($user_timezone),
					"DATE_FORMAT" => stripslashes($user_dateformat),
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
					"L_HIDE_USER" => $lang['Hide_user'], 
					"L_ALWAYS_ADD_SIGNATURE" => $l_alwayssig,
					"L_AVATAR_EXPLAIN" => $lang['Avatar_explain'],
					"L_UPLOAD_AVATAR" => $lang['Upload_Avatar'],
					"L_AVATAR_URL" => $lang['Avatar_URL'], 
					"L_AVATAR_GALLERY" => $lang['Avatar_gallery'], 
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

					"S_HIDDEN_FIELDS" => $s_hidden_fields, 
					"S_PROFILE_ACTION" => append_sid("profile.$phpEx"))
				);

				$template->pparse("body");
				include('includes/page_tail.'.$phpEx);
			}
			break;

		case 'activate':

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

}

?>