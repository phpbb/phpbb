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
 ***************************************************************************/

$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/post.'.$phpEx);
include($phpbb_root_path . 'includes/bbcode.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_PROFILE, $session_length);
init_userprefs($userdata);
//
// End session management
//


// -----------------------
// Page specific functions
//
//
// Check to see if email address is banned
// or already present in the DB
//
function validate_email($email)
{
	global $db;

	if($email != "")
	{
		if( preg_match("/^[a-z0-9\.\-_]+@[a-z0-9\-_]+\.([a-z0-9\-_]+\.)?[a-z]+$/is", $email) )
		{
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
					return(0);
				}
			}
			$sql = "SELECT user_email
				FROM " . USERS_TABLE . "
				WHERE user_email = '" . $email . "'";
			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Couldn't obtain user email information.", "", __LINE__, __FILE__, $sql);
			}
			$email_taken = $db->sql_fetchrow($result);
			if($email_taken['user_email'] != "")
			{
				return(0);
			}

			return(1);
		}
		else
		{
			return(0);
		}
	}
	else
	{
		return(0);
	}
}

//
// Does supplementary validation of optional profile fields. This expects common stuff like trim() and strip_tags()
// to have already been run. Params are passed by-ref, so we can set them to the empty string if they fail.
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

		if (!preg_match("#^http\\:\\/\\/[a-z0-9\-]+\.[a-z0-9\-]+#i", $website))
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

function generate_password()
{
	$chars = array( 
		"a","A","b","B","c","C","d","D","e","E","f","F","g","G","h","H","i","I","j","J", "k","K","l","L","m","M","n","N","o","O","p","P","q","Q","r","R","s","S","t","T", "u","U","v","V","w","W","x","X","y","Y","z","Z","1","2","3","4","5","6","7","8", 
		"9","0");
	
	$max_chars = count($chars) - 1;
	srand((double)microtime()*1000000);
	
	for($i = 0; $i < 8; $i++)
	{
		$new_passwd = ($i == 0) ? $chars[rand(0, $max_chars)] : $new_passwd . $chars[rand(0, $max_chars)];
	}

	return($new_passwd);
}
//
// End page specific functions
// ---------------------------


//
// Start of program proper
//
if( isset($HTTP_GET_VARS['mode']) || isset($HTTP_POST_VARS['mode']) )
{
	$mode = ( isset($HTTP_GET_VARS['mode']) ) ? $HTTP_GET_VARS['mode'] : $HTTP_POST_VARS['mode'];
	//
	// Begin page proper
	//
	if( $mode == "viewprofile" )
	{

		if( empty($HTTP_GET_VARS[POST_USERS_URL]) || $HTTP_GET_VARS[POST_USERS_URL] == ANONYMOUS )
		{
			message_die(GENERAL_MESSAGE, $lang['No_user_id_specified']);
		}
		$profiledata = get_userdata_from_id($HTTP_GET_VARS[POST_USERS_URL]);

		$sql = "SELECT *
			FROM " . RANKS_TABLE . "
			ORDER BY rank_special, rank_min";
		if(!$ranks_result = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Couldn't obtain ranks information.", "", __LINE__, __FILE__, $sql);
		}
		$ranksrow = $db->sql_fetchrowset($ranksresult);
		
		//
		// Output page header and
		// profile_view template
		//
		$page_title = $lang['Viewing_profile'];
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			"body" => "profile_view_body.tpl",
			"jumpbox" => "jumpbox.tpl")
		);

		$jumpbox = make_jumpbox();
		$template->assign_vars(array(
			"L_GO" => $lang['Go'],
			"L_JUMP_TO" => $lang['Jump_to'],
			"L_SELECT_FORUM" => $lang['Select_forum'],

			"S_JUMPBOX_LIST" => $jumpbox,
			"S_JUMPBOX_ACTION" => append_sid("viewforum.$phpEx"))
		);
		$template->assign_var_from_handle("JUMPBOX", "jumpbox");
		//
		// End header
		//

		//
		// Calculate the number of days this user has been a member ($memberdays)
		// Then calculate their posts per day
		//
		$regdate = $profiledata['user_regdate'];

		$memberdays = max(1, round( ( time() - $regdate ) / 86400 ));
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

		if( $profiledata['user_viewemail'] && $profiledata['user_email'] != "" )
		{
			// Replace the @ with 'at'. Some anti-spam mesures.
			$email_addr = str_replace("@", " at ", $profiledata['user_email']);
			$email = "<a href=\"mailto:$email_addr\">$email_addr</a>";
			$email_img = "<a href=\"mailto:$email_addr\"><img src=\"" . $images['icon_email'] . "\" alt=\"" . $lang['Send_email'] . " " . $profiledata['username'] . "\" border=\"0\" /></a>";
		}
		else
		{
			$email = "";
			$email_img = "";
		}

		if( $profiledata['user_avatar'] != "" && $profiledata['user_id'] != ANONYMOUS )
		{
			$avatar_img = (eregi("http", $profiledata['user_avatar']) && $board_config['allow_avatar_remote']) ? "<img src=\"" . $profiledata['user_avatar'] . "\">" : "<img src=\"" . $board_config['avatar_path'] . "/" . $profiledata['user_avatar'] . "\" alt=\"\" />";;
		}
		else
		{
			$avatar_img = "&nbsp;";
		}

		$poster_rank = "";
		$rank_image = "";

		if( $profiledata['user_rank'] )
		{
			for($j = 0; $j < count($ranksrow); $j++)
			{
				if( $profiledata['user_rank'] == $ranksrow[$j]['rank_id'] && $ranksrow[$j]['rank_special'])
				{
					$poster_rank = $ranksrow[$j]['rank_title'];
					$rank_image = ($ranksrow[$j]['rank_image']) ? "<img src=\"" . $ranksrow[$j]['rank_image'] . "\"><br />" : "";
				}
			}
		}
		else
		{
			for($j = 0; $j < count($ranksrow); $j++)
			{
				if( $profiledata['user_posts'] > $ranksrow[$j]['rank_min'] && $profiledata['user_posts'] < $ranksrow[$j]['rank_max'] && !$ranksrow[$j]['rank_special'])
				{
					$poster_rank = $ranksrow[$j]['rank_title'];
					$rank_image = ($ranksrow[$j]['rank_image']) ? "<img src=\"" . $ranksrow[$j]['rank_image'] . "\"><br />" : "";
				}
			}
		}


		if( !empty($profiledata['user_icq']) )
		{
			$icq_status_img = "<a href=\"http://wwp.icq.com/" . $profiledata['user_icq'] . "#pager\"><img src=\"http://web.icq.com/whitepages/online?icq=" . $profiledata['user_icq'] . "&amp;img=5\" width=\"18\" height=\"18\" border=\"0\" /></a>";

			//
			// This cannot stay like this, it needs a 'proper' solution, eg a separate
			// template for overlaying the ICQ icon, or we just do away with the icq status 
			// display (which is after all somewhat a pain in the rear :D 
			//
			if( $theme['template_name'] == "subSilver" )
			{
				$icq_add_img = '<table width="59" border="0" cellspacing="0" cellpadding="0"><tr><td nowrap="nowrap" class="icqback"><img src="images/spacer.gif" width="3" height="18" alt = "">' . $icq_status_img . '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $profiledata['user_icq'] . '"><img src="images/spacer.gif" width="35" height="18" border="0" alt="' . $lang['ICQ'] . '" /></a></td></tr></table>'; 
				$icq_status_img = "";
			}
			else
			{
				$icq_add_img = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=" . $profiledata['user_icq'] . "\"><img src=\"" . $images['icon_icq'] . "\" alt=\"" . $lang['ICQ'] . "\" border=\"0\" /></a>";
			}
		}
		else
		{
			$icq_status_img = "&nbsp;";
			$icq_add_img = "&nbsp;";
		}

		$aim_img = ($profiledata['user_aim']) ? "<a href=\"aim:goim?screenname=" . $profiledata['user_aim'] . "&amp;message=Hello+Are+you+there?\"><img src=\"" . $images['icon_aim'] . "\" border=\"0\" alt=\"" . $lang['AIM'] . "\" /></a>" : "&nbsp;";

		$msnm_img = ($profiledata['user_msnm']) ? "<a href=\"profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$poster_id\"><img src=\"" . $images['icon_msnm'] . "\" border=\"0\" alt=\"" . $lang['MSNM'] . "\" /></a>" : "&nbsp;";

		$yim_img = ($members[$i]['user_yim']) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=" . $members[$i]['user_yim'] . "&amp;.src=pg\"><img src=\"" . $images['icon_yim'] . "\" border=\"0\" alt=\"" . $lang['YIM'] . "\" /></a>" : "&nbsp;";

		$search_img = "<a href=\"" . append_sid("search.$phpEx?search_author=" . urlencode($profiledata['username']) . "&amp;showresults=topics") . "\"><img src=\"" . $images['icon_search'] . "\" border=\"0\" alt=\"" . $lang['Search_user_posts'] . "\" /></a>";
		$search = "<a href=\"" . append_sid("search.$phpEx?search_author=" . urlencode($profiledata['username']) . "&amp;showresults=topics") . "\">" . $lang['Search_user_posts'] . "</a>";

		$www_img = ($profiledata['user_website']) ? "<a href=\"" . $profiledata['user_website'] . "\"><img src=\"" . $images['icon_www'] . "\" alt=\"" . $lang['Visit_website'] . "\" border=\"0\" /></a>" : "&nbsp;";

		$pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post&amp;" . POST_USERS_URL . "=" . $profiledata['user_id']) . "\"><img src=\"". $images['icon_pm'] . "\" alt=\"" . $lang['Private_messaging'] . "\" border=\"0\" /></a>";

		$template->assign_vars(array(
			"USERNAME" => $profiledata['username'],
			"JOINED" => create_date($board_config['default_dateformat'], $profiledata['user_regdate'], $board_config['board_timezone']),
			"POSTER_RANK" => $poster_rank,
			"RANK_IMAGE" => $rank_image,
			"POSTS_PER_DAY" => $posts_per_day,
			"POSTS" => $profiledata['user_posts'],
			"PERCENTAGE" => $percentage . "%",
			"EMAIL" => $email,
			"EMAIL_IMG" => $email_img,
			"PM_IMG" => $pm_img,
			"UL_SEARCH" => $search,
			"SEARCH_IMG" => $search_img,
			"ICQ_ADD_IMG" => $icq_add_img,
			"ICQ_STATUS_IMG" => $icq_status_img,
			"AIM" => ( ($profiledata['user_aim']) ? $profiledata['user_aim'] : "&nbsp;" ),
			"AIM_IMG" => $aim_img,
			"MSN" => ( ($profiledata['user_msnm']) ? $profiledata['user_msnm'] : "&nbsp;" ),
			"MSN_IMG" => $msnm_img,
			"YIM" => ( ($profiledata['user_yim']) ? $profiledata['user_yim'] : "&nbsp;" ),
			"YIM_IMG" => $yim_img,
			"WEBSITE" => ( ($profiledata['user_website']) ? $profiledata['user_website'] : "&nbsp;" ),
			"WEBSITE_IMG" => $www_img,
			"LOCATION" => ( ($profiledata['user_from']) ? $profiledata['user_from'] : "&nbsp;" ),
			"OCCUPATION" => ( ($profiledata['user_occ']) ? $profiledata['user_occ'] : "&nbsp;" ),
			"INTERESTS" => ( ($profiledata['user_interests']) ? $profiledata['user_interests'] : "&nbsp;" ),
			"AVATAR_IMG" => $avatar_img,

			"L_VIEWING_PROFILE" => $lang['Viewing_profile_of'], 
			"L_POSTER_RANK" => $lang['Poster_rank'], 
			"L_PER_DAY" => $lang['posts_per_day'],
			"L_OF_TOTAL" => $lang['of_total'],
			"L_CONTACT" => $lang['Contact'],
			"L_EMAIL_ADDRESS" => $lang['Email_address'],
			"L_EMAIL" => $lang['Email'],
			"L_PM" => $lang['Private_message'],
			"L_ICQ_NUMBER" => $lang['ICQ'],
			"L_YAHOO" => $lang['YIM'],
			"L_AIM" => $lang['AIM'],
			"L_MESSENGER" => $lang['MSNM'],
			"L_WEBSITE" => $lang['Website'],
			"L_LOCATION" => $lang['From'],
			"L_OCCUPATION" => $lang['Occupation'],
			"L_INTERESTS" => $lang['Interests'],

			"U_SEARCH_USER" => append_sid("search.$phpEx?search_author=" . urlencode($profiledata['username']) . "&amp;showresults=topics"),

			"S_PROFILE_ACTION" => append_sid("profile.$phpEx"))
		);

		$template->pparse("body");

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

	}
	else if( $mode == "editprofile" || $mode == "register" )
	{

		if( !$userdata['session_logged_in'] && $mode == "editprofile" )
		{
			header(append_sid("Location: login.$phpEx?forward_page=$PHP_SELF&mode=editprofile"));
		}

		$page_title = ($mode == "editprofile") ? $lang['Edit_profile'] : $lang['Register'];

		//
		// Start processing for output
		//
		if( $mode == "register" && !isset($HTTP_POST_VARS['agreed']) && !isset($HTTP_GET_VARS['agreed']) )
		{
			if(!isset($HTTP_POST_VARS['agreed']) && !isset($HTTP_GET_VARS['agreed']))
			{
				//
				// Load agreement template since user has not yet
				// agreed to registration conditions/coppa
				//
				include($phpbb_root_path . 'includes/page_header.'.$phpEx);

				$template->set_filenames(array(
					"body" => "agreement.tpl",
					"jumpbox" => "jumpbox.tpl")
				);

				$jumpbox = make_jumpbox();
				$template->assign_vars(array(
					"L_GO" => $lang['Go'],
					"L_JUMP_TO" => $lang['Jump_to'],
					"L_SELECT_FORUM" => $lang['Select_forum'],

					"S_JUMPBOX_LIST" => $jumpbox,
					"S_JUMPBOX_ACTION" => append_sid("viewforum.$phpEx"))
				);
				$template->assign_var_from_handle("JUMPBOX", "jumpbox");

				$template->assign_vars(array(
					"COPPA" => $coppa,

					"U_AGREE_OVER13" => append_sid("profile.$phpEx?mode=register&amp;agreed=true"),
					"U_AGREE_UNDER13" => append_sid("profile.$phpEx?mode=register&amp;agreed=true&amp;coppa=true"))
				);
				$template->pparse("body");

				include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
			}
		}
		else if( isset($HTTP_POST_VARS['submit']) || $mode == "register" )
		{
			if( $mode == "editprofile" )
			{
				$user_id = $HTTP_POST_VARS['user_id'];
				$current_email = trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['current_email'])));
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

			// Run some validation on the optional fields. These are pass-by-ref, so they'll be changed to 
			// empty strings if they fail.
			validate_optional_fields($icq, $aim, $msn, $yim, $website, $location, $occupation, $interests, $signature);

			$viewemail = (isset($HTTP_POST_VARS['viewemail'])) ? ( ($HTTP_POST_VARS['viewemail']) ? TRUE : 0 ) : 0;
			$allowviewonline = (isset($HTTP_POST_VARS['hideonline'])) ? ( ($HTTP_POST_VARS['hideonline']) ? 0 : TRUE ) : TRUE;
			$notifyreply = (isset($HTTP_POST_VARS['notifyreply'])) ? ( ($HTTP_POST_VARS['notifyreply']) ? TRUE : 0 ) : 0;
			$notifypm = (isset($HTTP_POST_VARS['notifypm'])) ? ( ($HTTP_POST_VARS['notifypm']) ? TRUE : 0 ) : TRUE;
			$attachsig = (isset($HTTP_POST_VARS['attachsig'])) ? ( ($HTTP_POST_VARS['attachsig']) ? TRUE : 0 ) : 0;

			$allowhtml = (isset($HTTP_POST_VARS['allowhtml'])) ? ( ($HTTP_POST_VARS['allowhtml']) ? TRUE : 0 ) : $userdata['user_allowhtml'];
			$allowbbcode = (isset($HTTP_POST_VARS['allowbbcode'])) ? ( ($HTTP_POST_VARS['allowbbcode']) ? TRUE : 0 ) : $userdata['user_allowbbcode'];
			$allowsmilies = (isset($HTTP_POST_VARS['allowsmilies'])) ? ( ($HTTP_POST_VARS['allowsmilies']) ? TRUE : 0 ) : $userdata['user_allowsmilies'];

			$user_style = ( isset($HTTP_POST_VARS['style']) ) ? intval($HTTP_POST_VARS['style']) : $board_config['default_style'];

			$user_lang = ($HTTP_POST_VARS['language']) ? $HTTP_POST_VARS['language'] : $board_config['default_lang'];
			$user_timezone = (isset($HTTP_POST_VARS['timezone'])) ? intval($HTTP_POST_VARS['timezone']) : $board_config['board_timezone'];
			$user_dateformat = ($HTTP_POST_VARS['dateformat']) ? trim($HTTP_POST_VARS['dateformat']) : $board_config['default_dateformat'];

			$user_avatar_remoteurl = (!empty($HTTP_POST_VARS['avatarremoteurl'])) ? $HTTP_POST_VARS['avatarremoteurl'] : "";
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
			else if( $mode == "register" )
			{
				$coppa = (!$HTTP_POST_VARS['coppa'] && !$HTTP_GET_VARS['coppa']) ? 0 : TRUE;

				if( empty($username) || empty($password) || empty($password_confirm) || empty($email) )
				{
					$error = TRUE;
					$error_msg = $lang['Fields_empty'];
				}
			}

			$passwd_sql = "";
			if( !empty($password) && !empty($password_confirm) )
			{
				// Awww, the user wants to change their password, isn't that cute..
				if( $password != $password_confirm )
				{
					$error = TRUE;
					$error_msg = $lang['Password_mismatch'];
				}
				else
				{
					$password = md5($password);

					if( $mode == "editprofile" )
					{
						$sql = "SELECT user_password 
							FROM " . USERS_TABLE . " 
							WHERE user_id = $user_id";
						if($result = $db->sql_query($sql))
						{
							$row = $db->sql_fetchrow($result);

							if( $row['user_password'] != $password )
							{
								$error = TRUE;
								$error_msg = $lang['Current_password_mismatch'];
							}
						}
						else
						{
							message_die(GENERAL_ERROR, "Couldn't obtain user_password information.", "", __LINE__, __FILE__, $sql);
						}
					}
					
					if( !$error )
					{
						$passwd_sql = "user_password = '$password', ";
					}
				}
			}
			else if( ( $password && !$password_confirm ) || ( !$password && $password_confirm ) )
			{
				$error = TRUE;
				$error_msg = $lang['Password_mismatch'];
			}

			//
			// Do a ban check on this email address
			//
			if($email != $userdata['user_email'] || $mode == "register")
			{
				if( !validate_email($email) )
				{
					$error = TRUE;
					if(isset($error_msg))
					{
						$error_msg .= "<br />";
					}
					$error_msg .= $lang['Sorry_banned_or_taken_email'];
				}
			}

			$username_sql = "";
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
					$row = $db->sql_fetchrow($result);
					$new_user_id = $row['total'] + 1;

					unset($result);
					unset($row);
				}
				else
				{
					message_die(GENERAL_ERROR, "Couldn't obtained next user_id information.", "", __LINE__, __FILE__, $sql);
				}

				$sql = "SELECT MAX(group_id) AS total
					FROM " . GROUPS_TABLE;
				if($result = $db->sql_query($sql))
				{
					$row = $db->sql_fetchrow($result);
					$new_group_id = $row['total'] + 1;

					unset($result);
					unset($row);
				}
				else
				{
					message_die(GENERAL_ERROR, "Couldn't obtained next user_id information.", "", __LINE__, __FILE__, $sql);
				}
			}

			$avatar_sql = "";
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

				if( isset($HTTP_POST_VARS['avatardel']) && $mode == "editprofile" )
				{
					if(file_exists("./".$board_config['avatar_path']."/".$userdata['user_avatar']))
					{
						@unlink("./".$board_config['avatar_path']."/".$userdata['user_avatar']);
						$avatar_sql = ", user_avatar = ''";
					}
				}
				else if( $user_avatar_loc != "" )
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
								preg_match("'image\/[x\-]*([a-z]+)'", $user_avatar_type, $user_avatar_type);
								$user_avatar_type = $user_avatar_type[1];

								switch($user_avatar_type)
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

								if(!$error)
								{
									list($width, $height) = @getimagesize($user_avatar_loc);

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
										$error_msg = (!empty($error_msg)) ? $error_msg . "<br />" . $lang['Avatar_imagesize'] : $lang['Avatar_imagesize'];
									}
								}
							}
							else
							{
								$error = true;
								$error_filesize = $lang['Avatar_filesize'] . " " . round($board_config['avatar_filesize'] / 1024) . " " . $lang['kB'];
								$error_msg = (!empty($error_msg)) ? $error_msg . "<br />" . $error_filesize : $error_filesize;
							}
						}
						else
						{
							$error = true;
							echo $error_msg = (!empty($error_msg)) ? $error_msg . "<br />" . $lang['Avatar_filetype'] : $lang['Avatar_filetype'];
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
						preg_match("/^(http:\/\/)?([a-z0-9\.]+)\:?([0-9]*)\/(.*)$/", $user_avatar_url, $url_ary);

						if( !empty($url_ary[4]) )
						{
							$port = (!empty($url_ary[3])) ? $url_ary[3] : 80;

							$fsock = @fsockopen($url_ary[2], $port, $errno, $errstr);
							if($fsock)
							{
								$base_get = "/" . $url_ary[4];

								//
								// Uses HTTP 1.1, could use HTTP 1.0 ...
								//
								@fputs($fsock, "GET $base_get HTTP/1.1\r\n");
								@fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
								@fputs($fsock, "Connection: close\r\n\r\n");

								unset($avatar_data);
								while(!@feof($fsock))
								{
									$avatar_data .= @fread($fsock, $board_config['avatar_filesize']);
								}
								@fclose($fsock);

								if(preg_match("/Content-Length\: ([0-9]+)[^\/]+Content-Type\: image\/[x\-]*([a-z]+)[\s]+/i", $avatar_data, $file_data))
								{
									$file_size = $file_data[1];
									$file_type = $file_data[2];

									switch($file_type)
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

									if(!$error && $file_size > 0 && $file_size < $board_config['avatar_filesize'])
									{
										$avatar_data = substr($avatar_data, strlen($avatar_data) - $file_size, $file_size);

										$tmp_filename = tempnam ("/tmp", $userdata['user_id'] . "-");
										$fptr = @fopen($tmp_filename, "wb");
										$bytes_written = @fwrite($fptr, $avatar_data, $file_size);
										@fclose($fptr);

										if($bytes_written == $file_size)
										{
											list($width, $height) = @getimagesize($tmp_filename);

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
												@copy($tmp_filename, "./" . $board_config['avatar_path'] . "/$avatar_filename");
												@unlink($tmp_filename);

												$avatar_sql = ", user_avatar = '$avatar_filename'";
											}
											else
											{
												//
												// Image too large
												//
												@unlink($tmp_filename);
												$error = true;
												$error_msg = (!empty($error_msg)) ? $error_msg . "<br />" . $lang['Avatar_imagesize'] : $lang['Avatar_imagesize'];
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
									$error_msg = (!empty($error_msg)) ? $error_msg . "<br />" . $lang['File_no_data'] : $lang['File_no_data'];
								}
							}
							else
							{
								//
								// No connection
								//
								$error = true;
								$error_msg = (!empty($error_msg)) ? $error_msg . "<br />" . $lang['No_connection_URL'] : $lang['No_connection_URL'];
							}
						}
						else
						{
							$error = true;
							$error_msg = (!empty($error_msg)) ? $error_msg . "<br />" . $lang['Incomplete_URL'] : $lang['Incomplete_URL'];
						}
					} // if ... allow_avatar_upload
				}
				else if( !empty($user_avatar_name) )
				{
					$error = true;
					$error_filesize = $lang['Avatar_filesize'] . " " . round($board_config['avatar_filesize'] / 1024) . " " . $lang['kB'];
					$error_msg = (!empty($error_msg)) ? $error_msg . "<br />" . $error_filesize : $error_filesize;
				}
			}

			if($board_config['allow_avatar_remote'] && !$error)
			{
				if($user_avatar_remoteurl != "" && $avatar_sql == "")
				{
					if( !eregi("^http\:\/\/", $user_avatar_remoteurl) )
					{
						$user_avatar_remoteurl = "http://" . $user_avatar_remoteurl;
					}
					$avatar_sql = ", user_avatar = '$user_avatar_remoteurl'";
				}
			}

			if(!$error)
			{
				if($mode == "editprofile")
				{
					if($email != $current_email && ( $board_config['require_activation'] == USER_ACTIVATION_SELF || $board_config['require_activation'] == USER_ACTIVATION_ADMIN ) )
					{
						$user_active = 0;
						$user_actkey = generate_activation_key();

						//
						// The user is inactive, remove their session forcing them to login again before they can post.
						//
						$sql = "DELETE FROM " . SESSIONS_TABLE . "
			  				  WHERE session_user_id = " . $userdata['user_id'];

				  		$db->sql_query($sql);

					}
					else
					{
						$user_active = 1;
						$user_actkey = "";
					}

					$sql = "UPDATE " . USERS_TABLE . "
						SET " . $username_sql . $passwd_sql . "user_email = '$email', user_icq = '$icq', user_website = '$website', user_occ = '$occupation', user_from = '$location', user_interests = '$interests', user_sig = '$signature', user_sig_bbcode_uid = '$signature_bbcode_uid', user_viewemail = $viewemail, user_aim = '$aim', user_yim = '$yim', user_msnm = '$msn', user_attachsig = $attachsig, user_allowsmile = $allowsmilies, user_allowhtml = $allowhtml, user_allowbbcode = $allowbbcode, user_allow_viewonline = $allowviewonline, user_notify = $notifyreply, user_notify_pm = $notifypm, user_timezone = $user_timezone, user_dateformat = '$user_dateformat', user_lang = '$user_lang', user_style = $user_style, user_active = $user_active, user_actkey = '$user_actkey'" . $avatar_sql . "
						WHERE user_id = $user_id";

					if($result = $db->sql_query($sql))
					{
						if( $user_active == 0 )
						{
							//
							// The users account has been deactivated, send them an email with a new activation key
							//
							include($phpbb_root_path . 'includes/emailer.'.$phpEx);
							$emailer = new emailer($board_config['smtp_delivery']);

							$email_headers = "From: " . $board_config['board_email'] . "\nReturn-Path: " . $board_config['board_email'] . "\r\n";

							$path = (dirname($HTTP_SERVER_VARS['REQUEST_URI']) == "/") ? "" : dirname($HTTP_SERVER_VARS['REQUEST_URI']);

							if( $board_config['require_activation'] == USER_ACTIVATION_SELF )
							{
								$emailer->use_template("user_activate");
								$emailer->email_address($email);
							}
							else
							{
								$emailer->use_template("admin_activate");
								$emailer->email_address($board_config['board_email']);
							}
							$emailer->set_subject($lang['Reactivate']);
							$emailer->extra_headers($email_headers);

							$emailer->assign_vars(array(
								"SITENAME" => $board_config['sitename'],
								"USERNAME" => $username,
								"EMAIL_SIG" => str_replace("<br />", "\n", "-- \n" . $board_config['board_email_sig']), 

								"U_ACTIVATE" => "http://" . $HTTP_SERVER_VARS['SERVER_NAME'] . $path . "/profile.$phpEx?mode=activate&act_key=$act_key")
							);
							$emailer->send();
							$emailer->reset();

							$message = $lang['Profile_updated'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("index.$phpEx") . "\">" . $lang['Here'] . "</a> " . $lang['to_return_index'];

						}
						else
						{
							$message = $lang['Profile_updated'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("index.$phpEx") . "\">" . $lang['Here'] . "</a> " . $lang['to_return_index'];
						}

						$template->assign_vars(array(
							"META" => '<meta http-equiv="refresh" content="3;url=index.' . $phpEx . '">')
						);

						message_die(GENERAL_MESSAGE, $message);
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
					$sql = "INSERT INTO " . USERS_TABLE . "	(user_id, username, user_regdate, user_password, user_email, user_icq, user_website, user_occ, user_from, user_interests, user_sig, user_sig_bbcode_uid, user_avatar, user_viewemail, user_aim, user_yim, user_msnm, user_attachsig, user_allowsmile, user_allowhtml, user_allowbbcode, user_allow_viewonline, user_notify, user_notify_pm, user_timezone, user_dateformat, user_lang, user_style, user_level, user_allow_pm, user_active, user_actkey)
						VALUES ($new_user_id, '$username', " . time() . ", '$password', '$email', '$icq', '$website', '$occupation', '$location', '$interests', '$signature', '$signature_bbcode_uid', '$avatar_filename', $viewemail, '$aim', '$yim', '$msn', $attachsig, $allowsmilies, $allowhtml, $allowbbcode, $allowviewonline, $notifyreply, $notifypm, $user_timezone, '$user_dateformat', '$user_lang', $user_style, 0, 1, ";

					if( $board_config['require_activation'] ==USER_ACTIVATION_SELF || $board_config['require_activation'] == USER_ACTIVATION_ADMIN || $coppa == 1)
					{
						$user_actkey = generate_activation_key();
						$sql .= "0, '$user_actkey')";
					}
					else
					{
						$sql .= "1, '')";
					}

					if($result = $db->sql_query($sql, BEGIN_TRANSACTION))
					{
						$sql = "INSERT INTO " . GROUPS_TABLE . " (group_id, group_name, group_description, group_single_user, group_moderator)
							VALUES ($new_group_id, '', 'Personal User', 1, 0)";
						if($result = $db->sql_query($sql))
						{
							$sql = "INSERT INTO " . USER_GROUP_TABLE . " (user_id, group_id, user_pending)
								VALUES ($new_user_id, $new_group_id, 0)";
							if($result = $db->sql_query($sql, END_TRANSACTION))
							{
								if( $board_config['require_activation'] == USER_ACTIVATION_SELF )
								{
									$message = $lang['Account_inactive'];
									$email_template = "user_welcome_inactive";
								}
								else if( $board_config['require_activation'] == USER_ACTIVATION_ADMIN )
								{
									$message = $lang['Account_inactive_admin'];
									$email_template = "admin_welcome_inactive";
								}
								else if( $coppa )
								{
									$message = $lang['COPPA'];
									$email_template = "coppa_welcome_inactive";
								}
								else
								{
									$message = $lang['Account_added'];
									$email_template = "user_welcome";
								}

								include($phpbb_root_path . 'includes/emailer.'.$phpEx);
								$emailer = new emailer($board_config['smtp_delivery']);

								$email_headers = "From: " . $board_config['board_email'] . "\nReturn-Path: " . $board_config['board_email'] . "\r\n";

								$path = (dirname($HTTP_SERVER_VARS['REQUEST_URI']) == "/") ? "" : dirname($HTTP_SERVER_VARS['REQUEST_URI']);

								$emailer->use_template($email_template);
								$emailer->email_address($email);
								$emailer->set_subject($lang['Welcome_subject']);
								$emailer->extra_headers($email_headers);

								if($coppa)
								{
									$emailer->assign_vars(array(
										"WELCOME_MSG" => $lang['Welcome_subject'],
										"USERNAME" => $username,
										"PASSWORD" => $password_confirm,
										"EMAIL_SIG" => str_replace("<br />", "\n", "-- \n" . $board_config['board_email_sig']),

										"U_ACTIVATE" => "http://" . $HTTP_SERVER_VARS['SERVER_NAME'] . $path . "/profile.$phpEx?mode=activate&act_key=$user_actkey",
										"FAX_INFO" => $board_config['coppa_fax'],
										"MAIL_INFO" => $board_config['coppa_mail'],
										"EMAIL_ADDRESS" => $email,
										"ICQ" => $icq,
										"AIM" => $aim,
										"YIM" => $yim,
										"MSN" => $msn,
										"WEB_SITE" => $website,
										"FROM" => $location,
										"OCC" => $occupation,
										"INTERESTS" => $interests,
										"SITENAME" => $board_config['sitename']));
								}
								else
								{
									$emailer->assign_vars(array(
										"WELCOME_MSG" => $lang['Welcome_subject'],
										"USERNAME" => $username,
										"PASSWORD" => $password_confirm,
										"EMAIL_SIG" => str_replace("<br />", "\n", "-- \n" . $board_config['board_email_sig']),
	
										"U_ACTIVATE" => "http://" . $HTTP_SERVER_VARS['SERVER_NAME'] . $path . "/profile.$phpEx?mode=activate&act_key=$user_actkey")
									);
								}

								$emailer->send();
								$emailer->reset();

								if( $board_config['require_activation'] == USER_ACTIVATION_ADMIN )
								{
									$emailer->use_template("admin_activate");
									$emailer->email_address($board_config['board_email']);
									$emailer->set_subject($lang['New_account_subject']);
									$emailer->extra_headers($email_headers);

									$emailer->assign_vars(array(
										"WELCOME_MSG" => $lang['Welcome_subject'],
										"USERNAME" => $username,
										"EMAIL_SIG" => str_replace("<br />", "\n", "-- \n" . $board_config['board_email_sig']),

										"U_ACTIVATE" => "http://" . $HTTP_SERVER_VARS['SERVER_NAME'] . $path . "/profile.$phpEx?mode=activate&act_key=$user_actkey")
									);
									$emailer->send();
									$emailer->reset();
								}

								$template->assign_vars(array(
									"META" => '<meta http-equiv="refresh" content="5;url=index.' . $phpEx . '">')
								);

								$message = $message . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("index.$phpEx") . "\">" . $lang['Here'] . "</a> " . $lang['to_return_index'];

								message_die(GENERAL_MESSAGE, $message);
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

			//
			// If an error occured we need to stripslashes on returned data
			// 
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
		else if($mode == "editprofile")
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
			$signature_bbcode_uid = $userdata['user_sig_bbcode_uid'];

			$viewemail = $userdata['user_viewemail'];
			$notifypm = $userdata['user_notify_pm'];
			$notifyreply = $userdata['user_notify'];
			$attachsig = $userdata['user_attachsig'];
			$allowhtml = $userdata['user_allowhtml'];
			$allowbbcode = $userdata['user_allowbbcode'];
			$allowsmilies = $userdata['user_allowsmile'];
			$allowviewonline = $userdata['user_allow_viewonline'];

			$user_avatar = $userdata['user_avatar'];
			$user_style = $userdata['user_style'];
			$user_lang = $userdata['user_lang'];
			$user_timezone = $userdata['user_timezone'];
			$user_dateformat = $userdata['user_dateformat'];
		}

		if( !isset($coppa) )
		{
			$coppa = FALSE;
		}

		if( !isset($user_template) )
		{
			$selected_template = $board_config['system_template'];
		}

		$html_status =   ($userdata['user_allowhtml']) ? $lang['ON'] : $lang['OFF'];
		$bbcode_status =  ($userdata['user_allowbbcode']) ? $lang['ON'] : $lang['OFF'];
		$smilies_status =  ($userdata['user_allowsmile']) ? $lang['ON'] : $lang['OFF'];

		$signature = preg_replace("/\:[0-9a-z\:]*?\]/si", "]", $signature);

		if($user_avatar != "")
		{
			$avatar_img = (eregi("^http", $user_avatar) && $board_config['allow_avatar_remote']) ? "<img src=\"" . $user_avatar . "\">" : "<img src=\"" . $board_config['avatar_path'] . "/" . $user_avatar . "\" alt=\"\" />";
		}
		else
		{
			$avatar_img = "";
		}

		$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" /><input type="hidden" name="agreed" value="true" /><input type="hidden" name="coppa" value="' . $coppa . '" />';
		if( $mode == "editprofile" )
		{
			$s_hidden_fields .= '<input type="hidden" name="user_id" value="' . $userdata['user_id'] . '" />';
			//
			// Send the users current email address. If they change it, and account activation is turned on
			// the user account will be disabled and the user will have to reactivate their account.
			//
			$s_hidden_fields .= '<input type="hidden" name="current_email" value="' . $userdata['user_email'] . '" />';
		}

		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		if( $error )
		{
			$template->set_filenames(array(
				"reg_header" => "error_body.tpl")
			);
			$template->assign_vars(array(
				"ERROR_MESSAGE" => $error_msg)
			);
			$template->pparse("reg_header");
		}

		$template->set_filenames(array(
			"body" => "profile_add_body.tpl",
			"jumpbox" => "jumpbox.tpl")
		);

		$jumpbox = make_jumpbox();
		$template->assign_vars(array(
			"L_GO" => $lang['Go'],
			"L_JUMP_TO" => $lang['Jump_to'],
			"L_SELECT_FORUM" => $lang['Select_forum'],

			"S_JUMPBOX_LIST" => $jumpbox,
			"S_JUMPBOX_ACTION" => append_sid("viewforum.$phpEx"))
		);
		$template->assign_var_from_handle("JUMPBOX", "jumpbox");

		if( $mode == "editprofile" )
		{
			$template->assign_block_vars("edit_profile", array());
		}

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
			"ALLOW_AVATAR" => $board_config['allow_avatar_upload'],
			"AVATAR" => $avatar_img,
			"AVATAR_SIZE" => $board_config['avatar_filesize'],
			"LANGUAGE_SELECT" => language_select($user_lang, 'language'),
			"STYLE_SELECT" => style_select($user_style, 'style'),
			"TIMEZONE_SELECT" => tz_select($user_timezone, 'timezone'),
			"DATE_FORMAT" => $user_dateformat,
			"HTML_STATUS" => $html_status,
			"BBCODE_STATUS" => $bbcode_status,
			"SMILIES_STATUS" => $smilies_status,

			"L_CURRENT_PASSWORD" => $lang['Current_password'], 
			"L_NEW_PASSWORD" => ( $mode == "register" ) ? $lang['Password'] : $lang['New_password'], 
			"L_CONFIRM_PASSWORD" => $lang['Confirm_password'],
			"L_PASSWORD_IF_CHANGED" => ($mode == "editprofile") ? $lang['password_if_changed'] : "",
			"L_PASSWORD_CONFIRM_IF_CHANGED" => ($mode == "editprofile") ? $lang['password_confirm_if_changed'] : "",
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
			"L_NOTIFY_ON_REPLY" => $lang['Always_notify'],
			"L_NOTIFY_ON_REPLY_EXPLAIN" => $lang['Always_notify_explain'],
			"L_NOTIFY_ON_PRIVMSG" => $lang['Notify_on_privmsg'],
			"L_PREFERENCES" => $lang['Preferences'],
			"L_PUBLIC_VIEW_EMAIL" => $lang['Public_view_email'],
			"L_ITEMS_REQUIRED" => $lang['Items_required'],
			"L_REGISTRATION_INFO" => $lang['Registration_info'],
			"L_PROFILE_INFO" => $lang['Profile_info'],
			"L_PROFILE_INFO_NOTICE" => $lang['Profile_info_warn'],
			"L_EMAIL_ADDRESS" => $lang['Email_address'],

			"L_HTML_IS" => $lang['HTML'] . " " . $lang['is'],
			"L_BBCODE_IS" => $lang['BBCode'] . " " . $lang['is'],
			"L_SMILIES_ARE" => $lang['Smilies'] . " " . $lang['are'],

			"S_ALLOW_AVATAR_UPLOAD" => $board_config['allow_avatar_upload'],
			"S_ALLOW_AVATAR_LOCAL" => $board_config['allow_avatar_local'],
			"S_ALLOW_AVATAR_REMOTE" => $board_config['allow_avatar_remote'],
			"S_HIDDEN_FIELDS" => $s_hidden_fields,
			"S_PROFILE_ACTION" => append_sid("profile.$phpEx"))
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

		$template->pparse("body");

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
	else if($mode == "sendpassword")
	{
		if( isset($HTTP_POST_VARS['submit']) )
		{
			$username = (!empty($HTTP_POST_VARS['username'])) ? trim(strip_tags($HTTP_POST_VARS['username'])) : "";
			$email = (!empty($HTTP_POST_VARS['email'])) ? trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['email']))) : "";

			$sql = "SELECT user_id, username, user_email 
				FROM " . USERS_TABLE . " 
				WHERE user_email = '$email' 
					AND username = '$username'";
			if( $result = $db->sql_query($sql) )
			{
				if( !$db->sql_numrows($result) )
				{
					message_die(GENERAL_MESSAGE, $lang['No_email_match']);
				}

				$row = $db->sql_fetchrow($result); 

				$username = $row['username'];
				$user_actkey = generate_activation_key();
				$user_password = generate_password();

				$sql = "UPDATE " . USERS_TABLE . " 
					SET user_active = 0, user_newpasswd = '" .md5($user_password) . "', user_actkey = '$user_actkey' 
					WHERE user_id = " . $row['user_id'];
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't update new password information", "", __LINE__, __FILE__, $sql);
				}

				include($phpbb_root_path . 'includes/emailer.'.$phpEx);
				$emailer = new emailer($board_config['smtp_delivery']);

				$email_headers = "From: " . $board_config['board_email'] . "\nReturn-Path: " . $board_config['board_email'] . "\r\n";

				$path = (dirname($HTTP_SERVER_VARS['REQUEST_URI']) == "/") ? "" : dirname($HTTP_SERVER_VARS['REQUEST_URI']);

				$emailer->use_template("user_activate_passwd");
				$emailer->email_address($row['user_email']);
				$emailer->set_subject($lang['New_password_activation']);
				$emailer->extra_headers($email_headers);

				$emailer->assign_vars(array(
					"USERNAME" => $username,
					"PASSWORD" => $user_password,
					"EMAIL_SIG" => str_replace("<br />", "\n", "-- \n" . $board_config['board_email_sig']), 
					
					"U_ACTIVATE" => "http://" . $HTTP_SERVER_VARS['SERVER_NAME'] . $path . "/profile.$phpEx?mode=activate&act_key=$user_actkey")
				);
				$emailer->send();
				$emailer->reset();

				$template->assign_vars(array(
					"META" => '<meta http-equiv="refresh" content="5;url=index.' . $phpEx . '">')
				);

				$message = $lang['Password_updated'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("index.$phpEx") . "\">" . $lang['Here'] . "</a> " . $lang['to_return_index'];

				message_die(GENERAL_MESSAGE, $message);
			}
			else
			{
				message_die(GENERAL_ERROR, "Couldn't obtain user information for sendpassword", "", __LINE__, __FILE__, $sql);
			}
		}
		else
		{
			$username = "";
			$email = "";
		}

		//
		// Output basic page
		//
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			"body" => "profile_send_pass.tpl",
			"jumpbox" => "jumpbox.tpl")
		);

		$jumpbox = make_jumpbox();
		$template->assign_vars(array(
			"L_GO" => $lang['Go'],
			"L_JUMP_TO" => $lang['Jump_to'],
			"L_SELECT_FORUM" => $lang['Select_forum'],

			"S_JUMPBOX_LIST" => $jumpbox,
			"S_JUMPBOX_ACTION" => append_sid("viewforum.$phpEx"))
		);
		$template->assign_var_from_handle("JUMPBOX", "jumpbox");

		$template->assign_vars(array(
			"USERNAME" => $username,
			"EMAIL" => $email,

			"L_SEND_PASSWORD" => $lang['Send_password'], 
			"L_ITEMS_REQUIRED" => $lang['Items_required'],
			"L_EMAIL_ADDRESS" => $lang['Email_address'],
			"L_SUBMIT" => $lang['Submit'],
			"L_RESET" => $lang['Reset'])
		);

		$template->pparse("body");

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
	else if($mode == "activate")
	{
		$sql = "SELECT user_id, user_email, user_newpasswd 
			FROM " . USERS_TABLE . "
			WHERE user_actkey = '$act_key'";
			if( $result = $db->sql_query($sql) )
			{
				if( $row = $db->sql_fetchrow($result) )
				{
					if( $row['user_newpasswd'] != "" )
					{
						$sql_update_pass = ", user_password = '" . $row['user_newpasswd'] . "', user_newpasswd = ''";
					}
					else
					{
						$sql_update_pass = "";
					}
					$sql_update = "UPDATE " . USERS_TABLE . "
						SET user_active = 1, user_actkey = ''" . $sql_update_pass . " 
						WHERE user_id = " . $row['user_id'];
					if($result = $db->sql_query($sql_update))
					{
						if( $board_config['require_activation'] == USER_ACTIVATION_ADMIN && $sql_update_pass == "" )
						{
							include($phpbb_root_path . 'includes/emailer.'.$phpEx);
							$emailer = new emailer($board_config['smtp_delivery']);

							$email_headers = "From: " . $board_config['board_email'] . "\nReturn-Path: " . $board_config['board_email'] . "\r\n";

							$path = (dirname($HTTP_SERVER_VARS['REQUEST_URI']) == "/") ? "" : dirname($HTTP_SERVER_VARS['REQUEST_URI']);

							$emailer->use_template("admin_welcome_activated");
							$emailer->email_address($row['user_email']);
							$emailer->set_subject($lang['Account_activated_subject']);
							$emailer->extra_headers($email_headers);

							$emailer->assign_vars(array(
								"USERNAME" => $username,
								"PASSWORD" => $password_confirm,
								"EMAIL_SIG" => str_replace("<br />", "\n", "-- \n" . $board_config['board_email_sig']))
							);
							$emailer->send();
							$emailer->reset();

							message_die(GENERAL_MESSAGE, $lang['Account_active_admin']);
						}
						else
						{
							$message = ( $sql_update_pass == "" ) ? $lang['Account_active'] : $lang['Password_activated']; 
							message_die(GENERAL_MESSAGE, $message);
						}
					}
					else
					{
						message_die(GENERAL_ERROR, "Couldn't update users table", "", __LINE__, __FILE__, $sql_update);
					}
				}
				else
				{
					message_die(GENERAL_ERROR, $lang['Wrong_activation']); //wrongactiv
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