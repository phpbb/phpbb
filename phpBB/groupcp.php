<?php
/***************************************************************************
 *                               groupcp.php
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

$pagetype = "groupcp";
$page_title = $lang['Group_Control_Panel'];

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_GROUPCP, $session_length);
init_userprefs($userdata);
//
// End session management
//

if(!isset($HTTP_GET_VARS['start']))
{
	$start = 0;
}


//
// Page header
//
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

//
// What shall we do? hhmmmm
//
if( isset($HTTP_GET_VARS[POST_GROUPS_URL]) )
{

	$group_id = $HTTP_GET_VARS[POST_GROUPS_URL];

	$sql = "SELECT * 
		FROM " . GROUPS_TABLE . " 
		WHERE group_id = $group_id";
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Error getting group information", "", __LINE__, __FILE__, $sql);
	}
	if( !$db->sql_numrows($result) )
	{
		message_die(GENERAL_MESSAGE, "That user group does not exist");
	}
	$group_info = $db->sql_fetchrow($result);

	$template->set_filenames(array(
		"info" => "groupcp_info_body.tpl",
		"list" => "groupcp_list_body.tpl",
		"jumpbox" => "jumpbox.tpl")
	);

	$jumpbox = make_jumpbox();
	$template->assign_vars(array(
		"JUMPBOX_LIST" => $jumpbox,
		"SELECT_NAME" => POST_FORUM_URL)
	);
	$template->assign_var_from_handle("JUMPBOX", "jumpbox");

	$template->assign_vars(array(
		"L_GROUP_NAME" => "Group Name", 
		"L_GROUP_DESC" => "Group Description", 

		"GROUP_NAME" => $group_info['group_name'],
		"GROUP_DESC" => $group_info['group_description'],
		"GROUP_MEMBERSHIP_DETAILS" => "")
	);


	$sql = "SELECT u.username, u.user_id, u.user_viewemail, u.user_posts, u.user_regdate, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_msnm, u.user_avatar 
		FROM " . USERS_TABLE . " u, " . USER_GROUP_TABLE . " ug     
		WHERE ug.group_id = $group_id 
			AND u.user_id = ug.user_id  
		ORDER BY u.user_regdate";
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Error getting user list for group", "", __LINE__, __FILE__, $sql);
	}

	//
	// Parse group info output
	//
	$template->pparse("info");


	//
	// Generate memberlist if there any!
	//
	if( ( $users_list = $db->sql_numrows($result) ) > 0 )
	{
		$group_members = $db->sql_fetchrowset($result);

		$template->assign_vars(array(
			"L_SELECT_SORT_METHOD" => $lang['Select_sort_method'], 
			"L_EMAIL" => $lang['Email'],
			"L_WEBSITE" => $lang['Website'],
			"L_FROM" => $lang['From'], 
			"L_ORDER" => $lang['Order'], 
			"L_SORT" => $lang['Sort'], 
			"L_SUBMIT" => $lang['Sort'], 
			"L_AIM" => $lang['AIM'], 
			"L_YIM" => $lang['YIM'], 
			"L_MSNM" => $lang['MSNM'], 
			"L_ICQ" => $lang['ICQ'], 

			"S_MODE_SELECT" => $select_sort_mode,
			"S_ORDER_SELECT" => $select_sort_order, 
			"S_MODE_ACTION" => append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id"))
		);
											
		for($i = 0; $i < $users_list; $i++)
		{
			$username = stripslashes($group_members[$i]['username']);
			$user_id = $group_members[$i]['user_id'];

			$from = stripslashes($group_members[$i]['user_from']);

			$joined = create_date($board_config['default_dateformat'], $group_members[$i]['user_regdate'], $board_config['default_timezone']);

			$posts = ($group_members[$i]['user_posts']) ? $group_members[$i]['user_posts'] : 0;
		
			if($group_members[$i]['user_avatar'] != "")
			{
				$poster_avatar = (strstr("http", $group_members[$i]['user_avatar']) && $board_config['allow_avatar_remote']) ? "<img src=\"" . $group_members[$i]['user_avatar'] . "\">" : "<img src=\"" . $board_config['avatar_path'] . "/" . $group_members[$i]['user_avatar'] . "\">";
			}
			else
			{
				$poster_avatar = "";
			}

			if( !empty($group_members[$i]['user_viewemail']) )
			{
				$altered_email = str_replace("@", " at ", $group_members[$i]['user_email']);
				$email_img = "<a href=\"mailto:$altered_email\"><img src=\"" . $images['email'] . "\" border=\"0\" alt=\"" . $lang['Send_an_email'] . "\"></a>";
			}
			else
			{
				$email_img = "&nbsp;";
			}

			$pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post&" . POST_USERS_URL . "=" . $group_members[$i]['user_id']) . "\"><img src=\"" . $images['privmsg'] . "\" border=\"0\" alt=\"" . $lang['Send_private_message'] . "\"></a>";
		
			if($group_members[$i]['user_website'] != "")
			{
				if(!eregi("^http\:\/\/", $group_members[$i]['user_website']))
				{
					$website_url = "http://" . stripslashes($group_members[$i]['user_website']);
				}
				else
				{
					$website_url = stripslashes($group_members[$i]['user_website']);
				}
				$www_img = "<a href=\"$website_url\" target=\"_userwww\"><img src=\"" . $images['www'] . "\" border=\"0\"/></a>";
			}
			else
			{
				$www_img = "&nbsp;";
			}

			if($group_members[$i]['user_icq'])
			{
				$icq_status_img = "<a href=\"http://wwp.icq.com/" . $group_members[$i]['user_icq'] . "#pager\"><img src=\"http://online.mirabilis.com/scripts/online.dll?icq=" . $group_members[$i]['user_icq'] . "&img=5\" alt=\"$l_icqstatus\" border=\"0\"></a>";

				$icq_add_img = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=" . $group_members[$i]['user_icq'] . "\"><img src=\"" . $images['icq'] . "\" alt=\"$l_icq\" border=\"0\"></a>";
			}
			else
			{
				$icq_status_img = "&nbsp;";
				$icq_add_img = "&nbsp;";
			}
	
			$aim_img = ($group_members[$i]['user_aim']) ? "<a href=\"aim:goim?screenname=" . $group_members[$i]['user_aim'] . "&message=Hello+Are+you+there?\"><img src=\"" . $images['aim'] . "\" border=\"0\"></a>" : "&nbsp;";

			$msn_img = ($group_members[$i]['user_msnm']) ? "<a href=\"profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=$poster_id\"><img src=\"" . $images['msnm'] . "\" border=\"0\"></a>" : "&nbsp;";

			$yim_img = ($group_members[$i]['user_yim']) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=" . $group_members[$i]['user_yim'] . "&.src=pg\"><img src=\"" . $images['yim'] . "\" border=\"0\"></a>" : "&nbsp;";

			$search_img = "<a href=\"" . append_sid("search.$phpEx?a=" . urlencode($group_members[$i]['username']) . "&f=all&b=0&d=DESC&c=100&dosearch=1") . "\"><img src=\"" . $images['search_icon'] . "\" border=\"0\"></a>";

			if(!($i % 2))
			{
				$row_color = "#" . $theme['td_color1'];
			}
			else
			{
				$row_color = "#" . $theme['td_color2'];
			}

			if($user_id == $group_info['group_moderator'])
			{
				$template->assign_vars(array(
					"U_MOD_VIEWPROFILE" => append_sid("profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=" . $user_id), 
			
					"MOD_ROW_COLOR" => $row_color,
					"MOD_USERNAME" => $username,
					"MOD_FROM" => $from,
					"MOD_JOINED" => $joined,
					"MOD_POSTS" => $posts,
					"MOD_EMAIL_IMG" => $email_img,
					"MOD_PM_IMG" => $pm_img,
					"MOD_WWW_IMG" => $www_img,
					"MOD_ICQ_STATUS_IMG" => $icq_status_img, 
					"MOD_ICQ_ADD_IMG" => $icq_add_img, 
					"MOD_AIM_IMG" => $aim_img, 
					"MOD_YIM_IMG" => $yim_img, 
					"MOD_MSN_IMG" => $msn_img, 
					"MOD_SEARCH_IMG" => $search)
				);
			}
			else
			{
				$template->assign_block_vars("memberrow", array(
					"U_VIEWPROFILE" => append_sid("profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=" . $user_id), 
			
					"ROW_COLOR" => $row_color,
					"USERNAME" => $username,
					"FROM" => $from,
					"JOINED" => $joined,
					"POSTS" => $posts,
					"EMAIL_IMG" => $email_img,
					"PM_IMG" => $pm_img,
					"WWW_IMG" => $www_img,
					"ICQ_STATUS_IMG" => $icq_status_img, 
					"ICQ_ADD_IMG" => $icq_add_img, 
					"AIM_IMG" => $aim_img, 
					"YIM_IMG" => $yim_img, 
					"MSN_IMG" => $msn_img, 
					"SEARCH_IMG" => $search)
				);
			}
		}

		$pagination = generate_pagination("groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id", $users_list, $board_config['topics_per_page'], $start)."&nbsp;";

		$template->assign_vars(array(
			"PAGINATION" => $pagination,
			"ON_PAGE" => ( floor( $start / $board_config['topics_per_page'] ) + 1 ),
			"TOTAL_PAGES" => ceil( $users_list / $board_config['topics_per_page'] ),
		
			"L_OF" => $lang['of'],
			"L_PAGE" => $lang['Page'],
			"L_GOTO_PAGE" => $lang['Goto_page'])
		);

		$template->pparse("list");

	}
	else
	{
		//
		// No group members
		//
	}

}


//
// Page footer
//
include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>