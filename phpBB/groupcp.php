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
if( isset($HTTP_POST_VARS['joingroup']) )
{


}
else if( isset($HTTP_GET_VARS[POST_GROUPS_URL]) || isset($HTTP_POST_VARS[POST_GROUPS_URL]) )
{

	$group_id = ( isset($HTTP_POST_VARS[POST_GROUPS_URL]) ) ? $HTTP_POST_VARS[POST_GROUPS_URL] : $HTTP_GET_VARS[POST_GROUPS_URL];

	//
	// Get group details
	//
	$sql = "SELECT * 
		FROM " . GROUPS_TABLE . " 
		WHERE group_id = $group_id 
			AND group_single_user = 0";
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Error getting group information", "", __LINE__, __FILE__, $sql);
	}
	if( !$db->sql_numrows($result) )
	{
		message_die(GENERAL_MESSAGE, "That user group does not exist");
	}
	$group_info = $db->sql_fetchrow($result);

	//
	// Get user information for this group
	//
	$sql = "SELECT u.username, u.user_id, u.user_viewemail, u.user_posts, u.user_regdate, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_msnm, u.user_avatar 
		FROM " . USERS_TABLE . " u, " . USER_GROUP_TABLE . " ug     
		WHERE ug.group_id = $group_id 
			AND u.user_id = ug.user_id  
		ORDER BY u.user_regdate";
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Error getting user list for group", "", __LINE__, __FILE__, $sql);
	}
	if( $members_count = $db->sql_numrows($result) )
	{
		$group_members = $db->sql_fetchrowset($result);
	}

	//
	// Load templates
	//
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

	$is_group_member = 0;
	if($members_count)
	{
		for($i = 0; $i < $members_count; $i++)
		{
			if($group_members[$i]['user_id'] == $userdata['user_id'] && $userdata['session_logged_in'])
			{
				$is_group_member = TRUE;
			}
		}
	}

	if( $userdata['user_id'] == $group_info['group_moderator'] )
	{
		$group_details =  $lang['Are_group_moderator'];
		$s_hidden_fields = "";
	}
	else if($is_group_member)
	{
		$group_details =  $lang['Member_this_group'] . " <input type=\"submit\" name=\"unsub\" value=\"" . $lang['Unsubscribe'] . "\">";
		$s_hidden_fields = "";
	}
	else
	{
		if($group_info['group_type'])
		{
			//
			// I don't like this being here ...
			//
			$group_details =  $lang['This_open_group'] . " <input type=\"submit\" name=\"joingroup\" value=\"" . $lang['Join_group'] . "\">";
			$s_hidden_fields = "<input type=\"hidden\" name=\"" . POST_GROUPS_URL . "\" value=\"$group_id\">";
		}
		else
		{
			$group_details =  $lang['This_closed_group'];
			$s_hidden_fields = "";
		}
	}

	$template->assign_vars(array(
		"L_GROUP_INFORMATION" => $lang['Group_Information'],
		"L_GROUP_NAME" => $lang['Group_name'],
		"L_GROUP_DESC" => $lang['Group_description'], 
		"L_GROUP_MEMBERSHIP" => $lang['Group_membership'],
		"L_SUBSCRIBE" => $lang['Subscribe'], 
		"L_UNSUBSCRIBE" => $lang['Unsubscribe'], 

		"GROUP_NAME" => $group_info['group_name'],
		"GROUP_DESC" => $group_info['group_description'],
		"GROUP_DETAILS" => $group_details,
			
		"S_GROUP_INFO_ACTION" => append_sid("groupcp.$phpEx"), 
		"S_HIDDEN_FIELDS" => $s_hidden_fields)
	);

	//
	// Parse group info output
	//
	$template->pparse("info");

	//
	// Generate memberlist if there any!
	//
	if( $members_count )
	{
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
											
		for($i = 0; $i < $members_count; $i++)
		{
			$username = stripslashes($group_members[$i]['username']);
			$user_id = $group_members[$i]['user_id'];

			$from = stripslashes($group_members[$i]['user_from']);

			$joined = create_date($board_config['default_dateformat'], $group_members[$i]['user_regdate'], $board_config['default_timezone']);

			$posts = ($group_members[$i]['user_posts']) ? $group_members[$i]['user_posts'] : 0;
		
			if( !empty($group_members[$i]['user_viewemail']) )
			{
				$altered_email = str_replace("@", " at ", $group_members[$i]['user_email']);
				$email_img = "<a href=\"mailto:$altered_email\"><img src=\"" . $images['icon_email'] . "\" border=\"0\" alt=\"" . $lang['Send_an_email'] . "\"></a>";
			}
			else
			{
				$email_img = "&nbsp;";
			}

			$pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post&" . POST_USERS_URL . "=" . $group_members[$i]['user_id']) . "\"><img src=\"" . $images['icon_pm'] . "\" border=\"0\" alt=\"" . $lang['Send_private_message'] . "\"></a>";
		
			if($group_members[$i]['user_website'] != "")
			{
				$www_img = "<a href=\"" . stripslashes($group_members[$i]['user_website']) . "\" target=\"_userwww\"><img src=\"" . $images['icon_www'] . "\" border=\"0\"/></a>";
			}
			else
			{
				$www_img = "&nbsp;";
			}

			if($group_members[$i]['user_icq'])
			{
				$icq_status_img = "<a href=\"http://wwp.icq.com/" . $group_members[$i]['user_icq'] . "#pager\"><img src=\"http://online.mirabilis.com/scripts/online.dll?icq=" . $group_members[$i]['user_icq'] . "&img=5\" border=\"0\"></a>";

				$icq_add_img = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=" . $group_members[$i]['user_icq'] . "\"><img src=\"" . $images['icq'] . "\" alt=\"". $lang['ICQ'] . "\" border=\"0\"></a>";
			}
			else
			{
				$icq_status_img = "&nbsp;";
				$icq_add_img = "&nbsp;";
			}
	
			$aim_img = ($group_members[$i]['user_aim']) ? "<a href=\"aim:goim?screenname=" . $group_members[$i]['user_aim'] . "&message=Hello+Are+you+there?\"><img src=\"" . $images['icon_aim'] . "\" border=\"0\"></a>" : "&nbsp;";

			$msn_img = ($group_members[$i]['user_msnm']) ? "<a href=\"profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=$poster_id\"><img src=\"" . $images['icon_msnm'] . "\" border=\"0\"></a>" : "&nbsp;";

			$yim_img = ($group_members[$i]['user_yim']) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=" . $group_members[$i]['user_yim'] . "&.src=pg\"><img src=\"" . $images['icon_yim'] . "\" border=\"0\"></a>" : "&nbsp;";

			$search_img = "<a href=\"" . append_sid("search.$phpEx?a=" . urlencode($group_members[$i]['username']) . "&f=all&b=0&d=DESC&c=100&dosearch=1") . "\"><img src=\"" . $images['icon_search'] . "\" border=\"0\"></a>";

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
else 
{

	$sql = "SELECT group_id, group_name  
		FROM " . GROUPS_TABLE . "  
		WHERE group_single_user <> " . TRUE . " 
		ORDER BY group_name";
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Error getting group information", "", __LINE__, __FILE__, $sql);
	}
	if( !$db->sql_numrows($result) )
	{
		message_die(GENERAL_MESSAGE, "No groups exist");
	}
	$group_list = $db->sql_fetchrowset($result);

	$sql = "SELECT g.group_id, g.group_name, ug.user_pending 
		FROM " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug 
		WHERE ug.user_id = " . $userdata['user_id'] . "
			AND g.group_id = ug.group_id 
			AND g.group_single_user <> " . TRUE . " 
		ORDER BY g.group_name";
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Error getting group information", "", __LINE__, __FILE__, $sql);
	}
	if($db->sql_numrows($result))
	{
		$membergroup_list = $db->sql_fetchrowset($result);
	}

	$template->set_filenames(array(
		"user" => "groupcp_user_body.tpl",
		"jumpbox" => "jumpbox.tpl")
	);

	$jumpbox = make_jumpbox();
	$template->assign_vars(array(
		"JUMPBOX_LIST" => $jumpbox,
		"SELECT_NAME" => POST_FORUM_URL)
	);
	$template->assign_var_from_handle("JUMPBOX", "jumpbox");

	$s_member_groups = '<select name="' . POST_GROUPS_URL . '">';
	$s_member_groups_opt = "";
	$s_pending_groups = '<select name="' . POST_GROUPS_URL . '">';
	$s_pending_groups_opt = "";

	for($i = 0; $i < count($membergroup_list); $i++)
	{
		if($membergroup_list[$i]['user_pending'])
		{
			$s_pending_groups_opt .= '<option value="' . $membergroup_list[$i]['group_id'] . '">' . $membergroup_list[$i]['group_name'] . '</option>';
		}
		else
		{
			$s_member_groups_opt .= '<option value="' . $membergroup_list[$i]['group_id'] . '">' . $membergroup_list[$i]['group_name'] . '</option>';
		}
	}
	if($s_member_groups_opt == "")
	{
		$s_member_groups_opt = "<option>" . $lang['None'] . "</option>";
	}
	if($s_pending_groups_opt == "")
	{
		$s_pending_groups_opt = "<option>" . $lang['None'] . "</option>";
	}
	$s_pending_groups .= $s_pending_groups_opt . "</select>";
	$s_member_groups .= $s_member_groups_opt . "</select>";

	//
	// Remaining groups
	//
	$s_group_list = '<select name="' . POST_GROUPS_URL . '">';
	for($i = 0; $i < count($group_list); $i++)
	{
		if( !strstr($s_pending_groups, $group_list[$i]['group_name']) && !strstr($s_member_groups, $group_list[$i]['group_name']) )
		{
			$s_group_list_opt .= '<option value="' . $group_list[$i]['group_id'] . '">' . $group_list[$i]['group_name'] . '</option>';
		}
	}
	if($s_group_list_opt == "")
	{
		$s_group_list_opt = "<option>" . $lang['None'] . "</option>";
	}
	$s_group_list .= $s_group_list_opt . "</select>";

	$template->assign_vars(array(
		"L_GROUP_MEMBERSHIP_DETAILS" => $lang['Group_member_details'],
		"L_JOIN_A_GROUP" => $lang['Group_member_join'],
		"L_YOU_BELONG_GROUPS" => $lang['Current_memberships'], 
		"L_SELECT_A_GROUP" => $lang['Non_member_groups'], 
		"L_PENDING_GROUPS" => $lang['Memberships_pending'], 
		"L_SUBSCRIBE" => $lang['Subscribe'], 
		"L_UNSUBSCRIBE" => $lang['Unsubscribe'], 
		"L_VIEW_INFORMATION" => $lang['View_Information'], 

		"GROUP_LIST_SELECT" => $s_group_list,
		"GROUP_PENDING_SELECT" => $s_pending_groups,
		"GROUP_MEMBER_SELECT" => $s_member_groups)
	);

	$template->pparse("user");
}

//
// Page footer
//
include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>