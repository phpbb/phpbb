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
// Load default header
//
$phpbb_root_dir = "./../";

require('pagestart.inc');
include($phpbb_root_dir . 'includes/bbcode.'.$phpEx);
include($phpbb_root_dir . 'includes/post.'.$phpEx);

//
//
//
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
		if( !preg_match("#^http:\/\/#i", $website) )
		{
			$website = "http://" . $website;
		}

		if ( !preg_match("#^http\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+#i", $website) )
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
if( $mode == "edit" || $mode == "save" && ( isset($HTTP_POST_VARS['username']) || isset($HTTP_GET_VARS[POST_USERS_URL]) || isset($HTTP_POST_VARS[POST_USERS_URL]) ) )
{

	//
	// Ok, the profile has been modified and submitted, let's update
	//
	if( ( $mode == "save" && isset($HTTP_POST_VARS['submit']) ) || isset($HTTP_POST_VARS['avatargallery']) || isset($HTTP_POST_VARS['submitavatar']) || isset($HTTP_POST_VARS['cancelavatar']) )
	{
		$user_id = intval($HTTP_POST_VARS['id']);

		$this_userdata = get_userdata_from_id($user_id);
		if( !$this_userdata )
		{
			message_die(GENERAL_MESSAGE, $lang['No_user_id_specified']);
		}

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

		$viewemail = (isset($HTTP_POST_VARS['viewemail'])) ? ( ($HTTP_POST_VARS['viewemail']) ? TRUE : 0 ) : 0;
		$allowviewonline = (isset($HTTP_POST_VARS['hideonline'])) ? ( ($HTTP_POST_VARS['hideonline']) ? 0 : TRUE ) : TRUE;
		$notifyreply = (isset($HTTP_POST_VARS['notifyreply'])) ? ( ($HTTP_POST_VARS['notifyreply']) ? TRUE : 0 ) : 0;
		$notifypm = (isset($HTTP_POST_VARS['notifypm'])) ? ( ($HTTP_POST_VARS['notifypm']) ? TRUE : 0 ) : TRUE;
		$popuppm = (isset($HTTP_POST_VARS['popup_pm'])) ? ( ($HTTP_POST_VARS['popup_pm']) ? TRUE : 0 ) : TRUE;
		$attachsig = (isset($HTTP_POST_VARS['attachsig'])) ? ( ($HTTP_POST_VARS['attachsig']) ? TRUE : 0 ) : 0;

		$allowhtml = (isset($HTTP_POST_VARS['allowhtml'])) ? intval($HTTP_POST_VARS['allowhtml']) : $board_config['allow_html'];
		$allowbbcode = (isset($HTTP_POST_VARS['allowbbcode'])) ? intval($HTTP_POST_VARS['allowbbcode']) : $board_config['allow_bbcode'];
		$allowsmilies = (isset($HTTP_POST_VARS['allowsmilies'])) ? intval($HTTP_POST_VARS['allowsmilies']) : $board_config['allow_smilies'];

		$user_style = ($HTTP_POST_VARS['style']) ? intval($HTTP_POST_VARS['style']) : $board_config['default_style'];
		$user_lang = ($HTTP_POST_VARS['language']) ? $HTTP_POST_VARS['language'] : $board_config['default_lang'];
		$user_timezone = (isset($HTTP_POST_VARS['timezone'])) ? doubleval($HTTP_POST_VARS['timezone']) : $board_config['board_timezone'];
		$user_template = ($HTTP_POST_VARS['template']) ? $HTTP_POST_VARS['template'] : $board_config['board_template'];
		$user_dateformat = ($HTTP_POST_VARS['dateformat']) ? trim($HTTP_POST_VARS['dateformat']) : $board_config['default_dateformat'];

		$user_avatar_local = ( isset($HTTP_POST_VARS['avatarselect']) && !empty($HTTP_POST_VARS['submitavatar']) && $board_config['allow_avatar_local'] ) ? $HTTP_POST_VARS['avatarselect'] : ( ( isset($HTTP_POST_VARS['avatarlocal'])  ) ? $HTTP_POST_VARS['avatarlocal'] : "" );

		$user_avatar_remoteurl = (!empty($HTTP_POST_VARS['avatarremoteurl'])) ? trim($HTTP_POST_VARS['avatarremoteurl']) : "";
		$user_avatar_url = (!empty($HTTP_POST_VARS['avatarurl'])) ? trim($HTTP_POST_VARS['avatarurl']) : "";
		$user_avatar_loc = ($HTTP_POST_FILES['avatar']['tmp_name'] != "none") ? $HTTP_POST_FILES['avatar']['tmp_name'] : "";
		$user_avatar_name = (!empty($HTTP_POST_FILES['avatar']['name'])) ? $HTTP_POST_FILES['avatar']['name'] : "";
		$user_avatar_size = (!empty($HTTP_POST_FILES['avatar']['size'])) ? $HTTP_POST_FILES['avatar']['size'] : 0;
		$user_avatar_filetype = (!empty($HTTP_POST_FILES['avatar']['type'])) ? $HTTP_POST_FILES['avatar']['type'] : "";

		$user_avatar = ( empty($user_avatar_loc) ) ? $this_userdata['user_avatar'] : "";
		$user_avatar_type = ( empty($user_avatar_loc) ) ? $this_userdata['user_avatar_type'] : "";		

		$user_status = (!empty($HTTP_POST_VARS['user_status'])) ? intval($HTTP_POST_VARS['user_status']) : 0;
		$user_allowpm = (!empty($HTTP_POST_VARS['user_allowpm'])) ? intval($HTTP_POST_VARS['user_allowpm']) : 0;
		$user_rank = (!empty($HTTP_POST_VARS['user_rank'])) ? intval($HTTP_POST_VARS['user_rank']) : 0;
		$user_allowavatar = (!empty($HTTP_POST_VARS['user_allowavatar'])) ? intval($HTTP_POST_VARS['user_allowavatar']) : 0;
	}

	if( isset($HTTP_POST_VARS['submit']) )
	{
		$error = FALSE;

		if( $username != $this_userdata['username'] )
		{
			if( !validate_username($username) )
			{
				$error = TRUE;
				if( isset($error_msg) )
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

		$passwd_sql = "";
		if( !empty($password) && !empty($password_confirm) )
		{
			//
			// Awww, the user wants to change their password, isn't that cute..
			//
			if($password != $password_confirm)
			{
				$error = TRUE;
				if( isset($error_msg) )
				{
					$error_msg .= "<br />";
				}
				$error_msg .= $lang['Password_mismatch'];
			}
			else
			{
				$password = md5($password);
				$passwd_sql = "user_password = '$password', ";
			}
		}
		else if( $password && !$password_confirm )
		{
			$error = TRUE;
			if( isset($error_msg) )
			{
				$error_msg .= "<br />";
			}
			$error_msg .= $lang['Password_mismatch'];
		}
		else if( !$password && $password_confirm )
		{
			$error = TRUE;
			if( isset($error_msg) )
			{
				$error_msg .= "<br />";
			}
			$error_msg .= $lang['Password_mismatch'];
		}

		if( $signature != "" )
		{
			$signature_bbcode_uid = ( $allowbbcode ) ? make_bbcode_uid() : "";
			$signature = prepare_message($signature, $allowhtml, $allowbbcode, $allowsmilies, $signature_bbcode_uid);
		}

		$avatar_sql = "";
		if( isset($HTTP_POST_VARS['avatardel']) )
		{
			if( $this_userdata['user_avatar_type'] == USER_AVATAR_UPLOAD && $this_userdata['user_avatar'] != "" )
			{
				if( @file_exists("./../" . $board_config['avatar_path'] . "/" . $this_userdata['user_avatar']) )
				{
					@unlink("./../" . $board_config['avatar_path'] . "/" . $this_userdata['user_avatar']);
				}
			}
			$avatar_sql = ", user_avatar = '', user_avatar_type = " . USER_AVATAR_NONE;
		}
		else if( !$error && ( $user_avatar_loc != "" || !empty($user_avatar_url) ) )
		{
			//
			// Only allow one type of upload, either a
			// filename or a URL
			//
			if( !empty($user_avatar_loc) && !empty($user_avatar_url) )
			{
				$error = TRUE;
				if( isset($error_msg) )
				{
					$error_msg .= "<br />";
				}
				$error_msg .= $lang['Only_one_avatar'];
			}

			if( $user_avatar_loc != ""  )
			{
				if( file_exists($user_avatar_loc) && ereg(".jpg$|.gif$|.png$", $user_avatar_name) )
				{
					if( $user_avatar_size <= $board_config['avatar_filesize'] && $avatar_size > 0)
					{
						$error_type = false;

						//
						// Opera appends the image name after the type, not big, not clever!
						//
						preg_match("'image\/[x\-]*([a-z]+)'", $user_avatar_filetype, $user_avatar_filetype);
						$user_avatar_filetype = $user_avatar_filetype[1];

						switch( $user_avatar_filetype )
						{
							case "jpeg":
							case "pjpeg":
								$imgtype = '.jpg';
								break;
							case "gif":
								$imgtype = '.gif';
								break;
							case "png":
								$imgtype = '.png';
								break;
							default:
								$error = true;
								$error_msg = (!empty($error_msg)) ? $error_msg . "<br />" . $lang['Avatar_filetype'] : $lang['Avatar_filetype'];
								break;
						}

						if( !$error )
						{
							list($width, $height) = @getimagesize($user_avatar_loc);

							if( $width <= $board_config['avatar_max_width'] && $height <= $board_config['avatar_max_height'] )
							{
								$user_id = $this_userdata['user_id'];

								$avatar_filename = $user_id . $imgtype;

								if( $this_userdata['user_avatar_type'] == USER_AVATAR_UPLOAD && $this_userdata['user_avatar'] != "" )
								{
									if( @file_exists("./../" . $board_config['avatar_path'] . "/" . $this_userdata['user_avatar']) )
									{
										@unlink("./../" . $board_config['avatar_path'] . "/". $this_userdata['user_avatar']);
									}
								}
								@copy($user_avatar_loc, "./../" . $board_config['avatar_path'] . "/$avatar_filename");

								$avatar_sql = ", user_avatar = '$avatar_filename', user_avatar_type = " . USER_AVATAR_UPLOAD;
							}
							else
							{
								$l_avatar_size = sprintf($lang['Avatar_imagesize'], $board_config['avatar_max_width'], $board_config['avatar_max_height']);

								$error = true;
								$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $l_avatar_size : $l_avatar_size;
							}
						}
					}
					else
					{
						$l_avatar_size = sprintf($lang['Avatar_filesize'], round($board_config['avatar_filesize'] / 1024));

						$error = true;
						$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $l_avatar_size : $l_avatar_size;
					}
				}
				else
				{
					$error = true;
					$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $lang['Avatar_filetype'] : $lang['Avatar_filetype'];
				}
			}
			else if( !empty($user_avatar_url) )
			{
				//
				// First check what port we should connect
				// to, look for a :[xxxx]/ or, if that doesn't
				// exist assume port 80 (http)
				//
				preg_match("/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/", $user_avatar_url, $url_ary);

				if( !empty($url_ary[4]) )
				{
					$port = (!empty($url_ary[3])) ? $url_ary[3] : 80;

					$fsock = @fsockopen($url_ary[2], $port, $errno, $errstr);
					if( $fsock )
					{
						$base_get = "/" . $url_ary[4];

						//
						// Uses HTTP 1.1, could use HTTP 1.0 ...
						//
						@fputs($fsock, "GET $base_get HTTP/1.1\r\n");
						@fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
						@fputs($fsock, "Connection: close\r\n\r\n");

						unset($avatar_data);
						while( !@feof($fsock) )
						{
							$avatar_data .= @fread($fsock, $board_config['avatar_filesize']);
						}
						@fclose($fsock);

						if( preg_match("/Content-Length\: ([0-9]+)[^\/]+Content-Type\: image\/[x\-]*([a-z]+)[\s]+/i", $avatar_data, $file_data) )
						{
							$file_size = $file_data[1];
							$file_type = $file_data[2];

							switch( $file_type )
							{
								case "jpeg":
								case "pjpeg":
									$imgtype = '.jpg';
									break;
								case "gif":
									$imgtype = '.gif';
									break;
								case "png":
									$imgtype = '.png';
									break;
								default:
									$error = true;
									$error_msg = (!empty($error_msg)) ? $error_msg . "<br />" . $lang['Avatar_filetype'] : $lang['Avatar_filetype'];
									break;
							}

							if( !$error && $file_size > 0 && $file_size < $board_config['avatar_filesize'] )
							{
								$avatar_data = substr($avatar_data, strlen($avatar_data) - $file_size, $file_size);

								$tmp_filename = tempnam ("/tmp", $userdata['user_id'] . "-");
								$fptr = @fopen($tmp_filename, "wb");
								$bytes_written = @fwrite($fptr, $avatar_data, $file_size);
								@fclose($fptr);

								if( $bytes_written == $file_size )
								{
									list($width, $height) = @getimagesize($tmp_filename);

									if( $width <= $board_config['avatar_max_width'] && $height <= $board_config['avatar_max_height'] )
									{
										$user_id = $this_userdata['user_id'];

										$avatar_filename = $user_id . $imgtype;

										if( $this_userdata['user_avatar_type'] == USER_AVATAR_UPLOAD && $this_userdata['user_avatar'] != "")
										{
											if( file_exists("./../" . $board_config['avatar_path'] . "/" . $this_userdata['user_avatar']) )
											{
												@unlink("./../" . $board_config['avatar_path'] . "/" . $this_userdata['user_avatar']);
											}
										}
										@copy($tmp_filename, "./../" . $board_config['avatar_path'] . "/$avatar_filename");
										@unlink($tmp_filename);

										$avatar_sql = ", user_avatar = '$avatar_filename', user_avatar_type = " . USER_AVATAR_UPLOAD;
									}
									else
									{
										$l_avatar_size = sprintf($lang['Avatar_imagesize'], $board_config['avatar_max_width'], $board_config['avatar_max_height']);

										$error = true;
										$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $l_avatar_size : $l_avatar_size;
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
							$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $lang['File_no_data'] : $lang['File_no_data'];
						}
					}
					else
					{
						//
						// No connection
						//
						$error = true;
						$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $lang['No_connection_URL'] : $lang['No_connection_URL'];
					}
				}
				else
				{
					$error = true;
					$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $lang['Incomplete_URL'] : $lang['Incomplete_URL'];
				}
			}
			else if( !empty($user_avatar_name) )
			{
				$l_avatar_size = sprintf($lang['Avatar_filesize'], round($board_config['avatar_filesize'] / 1024));

				$error = true;
				$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $l_avatar_size : $l_avatar_size;
			}
		}
		else if( $user_avatar_remoteurl != "" && $avatar_sql == "" && !$error )
		{
			if( !preg_match("#^http:\/\/#i", $user_avatar_remoteurl) )
			{
				$user_avatar_remoteurl = "http://" . $user_avatar_remoteurl;
			}

			if( preg_match("#^http:\/\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+\/.*?\.(gif|jpg|png)$#is", $user_avatar_remoteurl) )
			{
				$avatar_sql = ", user_avatar = '$user_avatar_remoteurl', user_avatar_type = " . USER_AVATAR_REMOTE;
			}
			else
			{
				$error = true;
				$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $lang['Wrong_remote_avatar_format'] : $lang['Wrong_remote_avatar_format'];
			}
		}
		else if( !$error && $user_avatar_local != "" && $avatar_sql == "" )
		{
			$avatar_sql = ", user_avatar = '$user_avatar_local', user_avatar_type = " . USER_AVATAR_GALLERY;
		}

		if( !$error )
		{
			if( $HTTP_POST_VARS['deleteuser'] )
			{
				$sql = "SELECT g.group_id 
					FROM " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g  
					WHERE ug.user_id = $user_id 
						AND g.group_id = ug.group_id 
						AND g.group_single_user = 1";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't obtain group information for this user", "", __LINE__, __FILE__, $sql);
				}

				$row = $db->sql_fetchrow($result);
				
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
							if( $result = $db->sql_query($sql) )
							{
								$sql = "DELETE FROM " . GROUPS_TABLE . "
									WHERE group_id = " . $row['group_id'];
								if( $result = $db->sql_query($sql) )
								{
									$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . "
										WHERE user_id = $user_id";
									$result = @$db->sql_query($sql);

									$message = $lang['User_deleted'];
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
					if( isset($error_msg) )
					{
						$error_msg .= "<br />";
					}
					$error_msg .= $lang['Admin_user_fail'];
				}
			}
			else
			{
				$sql = "UPDATE " . USERS_TABLE . "
					SET " . $username_sql . $passwd_sql . "user_email = '$email', user_icq = '$icq', user_website = '$website', user_occ = '$occupation', user_from = '$location', user_interests = '$interests', user_sig = '$signature', user_viewemail = $viewemail, user_aim = '$aim', user_yim = '$yim', user_msnm = '$msn', user_attachsig = $attachsig, user_sig_bbcode_uid = '$signature_bbcode_uid', user_allowsmile = $allowsmilies, user_allowhtml = $allowhtml, user_allowavatar = $user_allowavatar, user_allowbbcode = $allowbbcode, user_allow_viewonline = $allowviewonline, user_notify = $notifyreply, user_allow_pm = $user_allowpm, user_notify_pm = $notifypm, user_popup_pm = $popuppm, user_lang = '$user_lang', user_style = $user_style, user_timezone = $user_timezone, user_dateformat = '$user_dateformat', user_active = $user_status, user_actkey = '$user_actkey', user_rank = $user_rank" . $avatar_sql . "
					WHERE user_id = $user_id";
				if( $result = $db->sql_query($sql) )
				{
					$message .= $lang['Admin_user_updated'];
				}
				else
				{
					$error = TRUE;
					if( isset($error_msg) )
					{
						$error_msg .= "<br />";
					}
					$error_msg .= $lang['Admin_user_fail'];
				}
			}

			$message .= "<br /><br />" . sprintf($lang['Click_return_useradmin'], "<a href=\"" . append_sid("admin_users.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);
		}
		else
		{
			$template->set_filenames(array(
				"reg_header" => "error_body.tpl")
			);
			$template->assign_vars(array(
				"ERROR_MESSAGE" => $error_msg)
			);
			$template->assign_var_from_handle("ERROR_BOX", "reg_header");

			$username = stripslashes($username);
			$email = stripslashes($email);
			$password = "";
			$password_confirm = "";

			$icq = stripslashes($icq);
			$aim = stripslashes($aim);
			$msn = stripslashes($msn);
			$yim = stripslashes($yim);

			$website = stripslashes($website);
			$location = stripslashes($location);
			$occupation = stripslashes($occupation);
			$interests = stripslashes($interests);
			$signature = stripslashes($signature);

			$user_lang = stripslashes($user_lang);
			$user_dateformat = stripslashes($user_dateformat);
		}
	}
	else if( !isset($HTTP_POST_VARS['submit']) && $mode != "save" && !isset($HTTP_POST_VARS['avatargallery']) && !isset($HTTP_POST_VARS['submitavatar']) && !isset($HTTP_POST_VARS['cancelavatar']))
	{
		if( isset($HTTP_GET_VARS[POST_USERS_URL]) || isset($HTTP_POST_VARS[POST_USERS_URL]) )
		{
			$user_id = ( isset($HTTP_POST_VARS[POST_USERS_URL]) ) ? $HTTP_POST_VARS[POST_USERS_URL] : $HTTP_GET_VARS[POST_USERS_URL];
			$this_userdata = get_userdata_from_id($user_id);
			if( !$this_userdata )
			{
				message_die(GENERAL_MESSAGE, $lang['No_user_id_specified']);
			}
		}
		else
		{
			$this_userdata = get_userdata($HTTP_POST_VARS['username']);
			if( !$this_userdata )
			{
				message_die(GENERAL_MESSAGE, $lang['No_user_id_specified']);
			}
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
		$popuppm = $userdata['user_popup_pm'];
		$notifyreply = $userdata['user_notify'];
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

		$html_status =  ($this_userdata['user_allowhtml']) ? $lang['HTML_is_ON'] : $lang['HTML_is_OFF'];
		$bbcode_status = ($this_userdata['user_allowbbcode']) ? $lang['BBCode_is_ON'] : $lang['BBCode_is_OFF'];
		$smilies_status = ($this_userdata['user_allowsmile']) ? $lang['Smilies_are_ON'] : $lang['Smilies_are_OFF'];
	}

	if( isset($HTTP_POST_VARS['avatargallery']) )
	{
		if( !$error )
		{
			$user_id = intval($HTTP_POST_VARS['id']);

			$template->set_filenames(array(
				"body" => "admin/user_avatar_gallery.tpl")
			);

			$dir = @opendir("../" . $board_config['avatar_gallery_path']);

			$avatar_images = array();
			while( $file = @readdir($dir) )
			{
				if( $file != "." && $file != ".." && !is_file($file) && !is_link($file) )
				{
					$sub_dir = @opendir("../" . $board_config['avatar_gallery_path'] . "/" . $file);

					$avatar_row_count = 0;
					$avatar_col_count = 0;

					while( $sub_file = @readdir($sub_dir) )
					{
						if( preg_match("/(\.gif$|\.png$|\.jpg)$/is", $sub_file) )
						{
							$avatar_images[$file][$avatar_row_count][$avatar_col_count] = $file . "/" . $sub_file;

							$avatar_col_count++;
							if( $avatar_col_count == 5 )
							{
								$avatar_row_count++;
								$avatar_col_count = 0;
							}
						}
					}
				}
			}
	
			@closedir($dir);

			if( isset($HTTP_POST_VARS['avatarcategory']) )
			{
				$category = $HTTP_POST_VARS['avatarcategory'];
			}
			else
			{
				list($category, ) = each($avatar_images);
			}
			@reset($avatar_images);

			$s_categories = "";
			while( list($key) = each($avatar_images) )
			{
				$selected = ( $key == $category ) ? "selected=\"selected\"" : "";
				if( count($avatar_images[$key]) )
				{
					$s_categories .= '<option value="' . $key . '"' . $selected . '>' . ucfirst($key) . '</option>';
				}
			}

			$s_colspan = 0;
			for($i = 0; $i < count($avatar_images[$category]); $i++)
			{
				$template->assign_block_vars("avatar_row", array());

				$s_colspan = max($s_colspan, count($avatar_images[$category][$i]));

				for($j = 0; $j < count($avatar_images[$category][$i]); $j++)
				{
					$template->assign_block_vars("avatar_row.avatar_column", array(
						"AVATAR_IMAGE" => "../" . $board_config['avatar_gallery_path'] . "/" . $avatar_images[$category][$i][$j])
					);

					$template->assign_block_vars("avatar_row.avatar_option_column", array(
						"S_OPTIONS_AVATAR" => $avatar_images[$category][$i][$j])
					);
				}
			}

			$coppa = ( ( !$HTTP_POST_VARS['coppa'] && !$HTTP_GET_VARS['coppa'] ) || $mode == "register") ? 0 : TRUE;

			$s_hidden_fields = '<input type="hidden" name="mode" value="edit" /><input type="hidden" name="agreed" value="true" /><input type="hidden" name="coppa" value="' . $coppa . '" />';
			$s_hidden_fields .= '<input type="hidden" name="id" value="' . $user_id . '" />';

			$s_hidden_fields .= '<input type="hidden" name="username" value="' . addslashes($username) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="email" value="' . addslashes($email) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="icq" value="' . addslashes($icq) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="aim" value="' . addslashes($aim) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="msn" value="' . addslashes($msn) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="yim" value="' . addslashes($yim) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="website" value="' . addslashes($website) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="location" value="' . addslashes($location) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="occupation" value="' . addslashes($occupation) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="interests" value="' . addslashes($interests) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="signature" value="' . addslashes($signature) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="viewemail" value="' . $viewemail . '" />';
			$s_hidden_fields .= '<input type="hidden" name="notifypm" value="' . $notifypm . '" />';
			$s_hidden_fields .= '<input type="hidden" name="popup_pm" value="' . $popuppm . '" />';
			$s_hidden_fields .= '<input type="hidden" name="notifyreply" value="' . $notifyreply . '" />';
			$s_hidden_fields .= '<input type="hidden" name="attachsig" value="' . $attachsig . '" />';
			$s_hidden_fields .= '<input type="hidden" name="allowhtml" value="' . $allowhtml . '" />';
			$s_hidden_fields .= '<input type="hidden" name="allowbbcode" value="' . $allowbbcode . '" />';
			$s_hidden_fields .= '<input type="hidden" name="allowsmilies" value="' . $allowsmilies . '" />';
			$s_hidden_fields .= '<input type="hidden" name="hideonline" value="' . !$allowviewonline . '" />';
			$s_hidden_fields .= '<input type="hidden" name="style" value="' . $user_style . '" />'; 
			$s_hidden_fields .= '<input type="hidden" name="language" value="' . $user_lang . '" />';
			$s_hidden_fields .= '<input type="hidden" name="timezone" value="' . $user_timezone . '" />';
			$s_hidden_fields .= '<input type="hidden" name="dateformat" value="' . addslashes($user_dateformat) . '" />';

			$s_hidden_fields .= '<input type="hidden" name="user_status" value="' . $user_status . '" />';
			$s_hidden_fields .= '<input type="hidden" name="user_allowpm" value="' . $user_allowpm . '" />';
			$s_hidden_fields .= '<input type="hidden" name="user_allowavatar" value="' . $user_allowavatar . '" />';
			$s_hidden_fields .= '<input type="hidden" name="user_rank" value="' . $user_rank . '" />';

			$template->assign_vars(array(
				"L_USER_TITLE" => $lang['User_admin'],
				"L_USER_EXPLAIN" => $lang['User_admin_explain'],
				"L_AVATAR_GALLERY" => $lang['Avatar_gallery'], 
				"L_SELECT_AVATAR" => $lang['Select_avatar'], 
				"L_RETURN_PROFILE" => $lang['Return_profile'], 
				"L_CATEGORY" => $lang['Select_category'], 
				"L_GO" => $lang['Go'],

				"S_OPTIONS_CATEGORIES" => $s_categories, 
				"S_COLSPAN" => $s_colspan, 
				"S_PROFILE_ACTION" => append_sid("admin_users.$phpEx?mode=$mode"), 
				"S_HIDDEN_FIELDS" => $s_hidden_fields)
			);
		}
	}
	else
	{

		$s_hidden_fields = '<input type="hidden" name="mode" value="save" /><input type="hidden" name="agreed" value="true" /><input type="hidden" name="coppa" value="' . $coppa . '" />';
		$s_hidden_fields .= '<input type="hidden" name="id" value="' . $this_userdata['user_id'] . '" />';

		if( !empty($user_avatar_local) )
		{
			$s_hidden_fields .= '<input type="hidden" name="avatarlocal" value="' . $user_avatar_local . '" />';
		}

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

		$sql = "SELECT * FROM " . RANKS_TABLE . "
			WHERE rank_special = 1
			ORDER BY rank_title";
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't obtain ranks data", "", __LINE__, __FILE__, $sql);
		}
		$rank_count = $db->sql_numrows($result);

		$rank_rows = $db->sql_fetchrowset($result);

		$rank_select_box = "<option value=\"0\">" . $lang['No_assigned_rank'] . "</option>";

		for($i = 0; $i < $rank_count; $i++)
		{
			$rank = $rank_rows[$i]['rank_title'];
			$rank_id = $rank_rows[$i]['rank_id'];
			
			$selected = ( $this_userdata['user_rank'] == $rank_id ) ? "selected=\"selected\"" : "";
			$rank_select_box .= "<option value=\"" . $rank_id . "\" " . $selected . ">" . $rank . "</option>";
		}

		$signature = preg_replace("/\:[0-9a-z\:]*?\]/si", "]", $signature);
		
		$template->set_filenames(array(
			"body" => "admin/user_edit_body.tpl")
		);

		$template->assign_vars(array(
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
			"POPUP_PM_YES" => ($popuppm) ? "checked=\"checked\"" : "",
			"POPUP_PM_NO" => (!$popuppm) ? "checked=\"checked\"" : "",
			"ALWAYS_ADD_SIGNATURE_YES" => ($attachsig) ? "checked=\"checked\"" : "",
			"ALWAYS_ADD_SIGNATURE_NO" => (!$attachsig) ? "checked=\"checked\"" : "",
			"NOTIFY_REPLY_YES" => ($notifyreply) ? "checked=\"checked\"" : "",
			"NOTIFY_REPLY_NO" => (!$notifyreply) ? "checked=\"checked\"" : "",
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
			"ALLOW_PM_YES" => ($user_allowpm) ? "checked=\"checked\"" : "",
			"ALLOW_PM_NO" => (!$user_allowpm) ? "checked=\"checked\"" : "",
			"ALLOW_AVATAR_YES" => ($user_allowavatar) ? "checked=\"checked\"" : "",
			"ALLOW_AVATAR_NO" => (!$user_allowavatar) ? "checked=\"checked\"" : "",
			"USER_ACTIVE_YES" => ($user_status) ? "checked=\"checked\"" : "",
			"USER_ACTIVE_NO" => (!$user_status) ? "checked=\"checked\"" : "", 
			"RANK_SELECT_BOX" => $rank_select_box,

			"L_USER_TITLE" => $lang['User_admin'],
			"L_USER_EXPLAIN" => $lang['User_admin_explain'],
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
			"L_UPLOAD_AVATAR_FILE" => $lang['Upload_Avatar_file'],
			"L_UPLOAD_AVATAR_URL" => $lang['Upload_Avatar_URL'],
			"L_AVATAR_GALLERY" => $lang['Select_from_gallery'],
			"L_SHOW_GALLERY" => $lang['View_avatar_gallery'],
			"L_LINK_REMOTE_AVATAR" => $lang['Link_remote_Avatar'],

			"L_SIGNATURE" => $lang['Signature'],
			"L_SIGNATURE_EXPLAIN" => sprintf($lang['Signature_explain'], $board_config['max_sig_chars']),
			"L_NOTIFY_ON_PRIVMSG" => $lang['Notify_on_privmsg'],
			"L_NOTIFY_ON_REPLY" => $lang['Always_notify'],
			"L_POPUP_ON_PRIVMSG" => $lang['Popup_on_privmsg'],
			"L_PREFERENCES" => $lang['Preferences'],
			"L_PUBLIC_VIEW_EMAIL" => $lang['Public_view_email'],
			"L_ITEMS_REQUIRED" => $lang['Items_required'],
			"L_REGISTRATION_INFO" => $lang['Registration_info'],
			"L_PROFILE_INFO" => $lang['Profile_info'],
			"L_PROFILE_INFO_NOTICE" => $lang['Profile_info_warn'],
			"L_CONFIRM" => $lang['Confirm'],
			"L_EMAIL_ADDRESS" => $lang['Email_address'],

			"HTML_STATUS" => $html_status,
			"BBCODE_STATUS" => $bbcode_status,
			"SMILIES_STATUS" => $smilies_status,

			"L_DELETE_USER" => $lang['User_delete'],
			"L_DELETE_USER_EXPLAIN" => $lang['User_delete_explain'],
			"L_SELECT_RANK" => $lang['Rank_title'],

			"S_HIDDEN_FIELDS" => $s_hidden_fields,
			"S_PROFILE_ACTION" => append_sid("admin_users.$phpEx"))
		);

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
	}

	$template->pparse("body");

}
else
{
	//
	// Default user selection box
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
		"L_USER_TITLE" => $lang['User_admin'],
		"L_USER_EXPLAIN" => $lang['User_admin_explain'],
		"L_USER_SELECT" => $lang['Select_a_User'],
		"L_LOOK_UP" => $lang['Look_up_user'],
		"L_FIND_USERNAME" => $lang['Find_username'],

		"U_SEARCH_USER" => append_sid("../search.$phpEx?mode=searchuser"), 

		"S_USER_ACTION" => append_sid("admin_users.$phpEx"),
		"S_USER_SELECT" => $select_list)
	);
	$template->pparse('body');

}

include('page_footer_admin.'.$phpEx);

?>