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
	
	if(!isset($coppa))
	{
		$coppa = FALSE;
	}

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
		"body" => "admin/user_edit_body.tpl"
		)
	);

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
		"ALLOW_AVATAR" => $board_config['allow_avatar_upload'],
		"AVATAR" => ($user_avatar != "") ? "<img src=\"" . $board_config['avatar_path'] . "/" . stripslashes($user_avatar) . "\" alt=\"\" />" : "",
		"AVATAR_SIZE" => $board_config['avatar_filesize'],
		"TIMEZONE_SELECT" => tz_select($user_timezone),
		"DATE_FORMAT" => stripslashes($user_dateformat),
		"HTML_STATUS" => $html_status,
		"BBCODE_STATUS" => $bbcode_status,
		"SMILIES_STATUS" => $smilies_status,

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
	
		"S_ALLOW_AVATAR_UPLOAD" => $board_config['allow_avatar_upload'],
		"S_ALLOW_AVATAR_LOCAL" => $board_config['allow_avatar_local'],
		"S_ALLOW_AVATAR_REMOTE" => $board_config['allow_avatar_remote'],
		"S_HIDDEN_FIELDS" => $s_hidden_fields,
		"S_PROFILE_ACTION" => append_sid("admin_users.$phpEx"))
	);

	//
	// This is another cheat using the block_var capability
	// of the templates to 'fake' an IF...ELSE...ENDIF solution
	// it works well :)
	//
	if( $board_config['allow_avatar_upload'] || $board_config['allow_avatar_local'] || $board_config['allow_avatar_remote'] )
	{
		$template->assign_block_vars("avatarblock", array() );

		if($board_config['allow_avatar_upload'])
		{
			$template->assign_block_vars("avatarblock.avatarupload", array() );
		}
		if($board_config['allow_avatar_remote'])
		{
			$template->assign_block_vars("avatarblock.avatarremote", array() );
		}
		if($board_config['allow_avatar_local'])
		{
			$template->assign_block_vars("avatarblock.avatargallery", array() );
		}

	}
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

	$user_avatar_url = (!empty($HTTP_POST_VARS['avatarurl'])) ? $HTTP_POST_VARS['avatarurl'] : "";
	$user_avatar_loc = ($HTTP_POST_FILES['avatar']['tmp_name'] != "none") ? $HTTP_POST_FILES['avatar']['tmp_name'] : "";
	$user_avatar_name = (!empty($HTTP_POST_FILES['avatar']['name'])) ? $HTTP_POST_FILES['avatar']['name'] : "";
	$user_avatar_size = (!empty($HTTP_POST_FILES['avatar']['size'])) ? $HTTP_POST_FILES['avatar']['size'] : 0;
	$user_avatar_type = (!empty($HTTP_POST_FILES['avatar']['type'])) ? $HTTP_POST_FILES['avatar']['type'] : "";
	$user_avatar = (empty($user_avatar_loc) && $mode == "editprofile") ? $userdata['user_avatar'] : "";


	if(isset($HTTP_POST_VARS['submit']))
	{
		$error = FALSE;
		$passwd_sql = "";
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
		$match_email = str_replace("*@", ".*@", $ban_email_list[$i]['ban_email']);
		if( preg_match("/^" . $match_email . "$/is", $email) )
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
						message_die(GENERAL_ERROR, "Could not update users table", "", __LINE__, __FILE__, $sql);
					}
				}
				else
				{
					message_die(GENERAL_ERROR, "Could not update topics table", "", __LINE__, __FILE__, $sql);
				}
			}
			else
			{
				message_die(GENERAL_ERROR, "Could not update posts table", "", __LINE__, __FILE__, $sql);
			}
		}
		else
		{
			$sql = "UPDATE " . USERS_TABLE . "
			SET " . $username_sql . $passwd_sql . "user_email = '$email', user_icq = '$icq', user_website = '$website', user_occ = '$occupation', user_from = '$location', user_interests = '$interests', user_sig = '$signature', user_viewemail = $viewemail, user_aim = '$aim', user_yim = '$yim', user_msnm = '$msn', user_attachsig = $attachsig, user_allowsmile = $allowsmilies, user_allowhtml = $allowhtml, user_allowbbcode = $allowbbcode, user_allow_viewonline = $allowviewonline, user_notify_pm = $notifypm, user_timezone = $user_timezone, user_dateformat = '$user_dateformat', user_lang = '$user_lang', user_active = '1', user_actkey = '$user_actkey'" . $avatar_sql . "
			WHERE poster_id = $user_id";
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
				message_die(GENERAL_ERROR, "Could not update users table", "", __LINE__, __FILE__, $sql);
			}
		}
	}
	else
	{
	message_die(GENERAL_ERROR, $error_msg, "", __LINE__, __FILE__, "");
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

?>
