<?php
/***************************************************************************
 *                              memberlist.php
 *                            -------------------
 *   begin                : Friday, May 11, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
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

$page_title = $lang['Memberlist'];

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_VIEWMEMBERS, $session_length);
init_userprefs($userdata);
//
// End session management
//

include($phpbb_root_path . 'includes/page_header.'.$phpEx);

if(!isset($HTTP_GET_VARS['start']))
{
	$start = 0;
}

if(isset($HTTP_POST_VARS['order']))
{
	$sort_order = ($HTTP_POST_VARS['order'] == "ASC") ? "ASC" : "DESC";
}
else if(isset($HTTP_GET_VARS['order']))
{
	$sort_order = ($HTTP_GET_VARS['order'] == "ASC") ? "ASC" : "DESC";
}
else
{
	$sort_order = "ASC";
}

if(isset($HTTP_GET_VARS['mode']) || isset($HTTP_POST_VARS['mode']))
{
	$mode = (isset($HTTP_POST_VARS['mode'])) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];

	switch($mode)
	{
		case 'joined':
			$order_by = "user_regdate ASC LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'username':
			$order_by = "username $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'location':
			$order_by = "user_from $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'posts':
			$order_by = "user_posts $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'email':
			$order_by = "user_email $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'website':
			$order_by = "user_website $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'topten':
			$order_by = "user_posts $sort_order LIMIT 10";
			break;
		default:
			$order_by = "user_regdate $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
	}
}
else
{
	$order_by = "user_regdate $sort_order LIMIT $start, " . $board_config['topics_per_page'];
}
$sql = "SELECT username, user_id, user_viewemail, user_posts, user_regdate, user_from, user_website, user_email, user_icq, user_aim, user_yim, user_msnm, user_avatar
	FROM " . USERS_TABLE . "
	WHERE user_id <> " . ANONYMOUS . "
	ORDER BY $order_by";

//
// Memberlist sorting
//
$mode_types_text = array($lang['Joined'], $lang['Username'], $lang['Location'], $lang['Posts'], $lang['Email'],  $lang['Website'], $lang['Top_Ten']);
$mode_types = array("joindate", "username", "location", "posts", "email", "website", "topten");

$select_sort_mode = "<select name=\"mode\">";
for($i = 0; $i < count($mode_types_text); $i++)
{
	$selected = ($mode == $mode_types[$i]) ? " selected=\"selected\"" : "";
	$select_sort_mode .= "<option value=\"" . $mode_types[$i] . "\"$selected>" . $mode_types_text[$i] . "</option>";
}
$select_sort_mode .= "</select>";

$select_sort_order = "<select name=\"order\">";
if($sort_order == "ASC")
{
	$select_sort_order .= "<option value=\"ASC\" selected=\"selected\">" . $lang['Ascending'] . "</option><option value=\"DESC\">" . $lang['Descending'] . "</option>";
}
else
{
	$select_sort_order .= "<option value=\"ASC\">" . $lang['Ascending'] . "</option><option value=\"DESC\" selected=\"selected\">" . $lang['Descending'] . "</option>";
}
$select_sort_order .= "</select>";

//
// Do the query and output the table
//
if(!$result = $db->sql_query($sql))
{
	message_die(GENERAL_ERROR, "Error getting memberlist.", "", __LINE__, __FILE__, $sql);
}

if(($selected_members = $db->sql_numrows($result)) > 0)
{
	$template->set_filenames(array(
		"body" => "memberlist_body.tpl",
		"jumpbox" => "jumpbox.tpl"));

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
		"S_MODE_ACTION" => append_sid("memberlist.$phpEx"))
	);

	$members = $db->sql_fetchrowset($result);

	for($i = 0; $i < $selected_members; $i++)
	{
		$username = stripslashes($members[$i]['username']);
		$user_id = $members[$i]['user_id'];

		$from = (!empty($members[$i]['user_from'])) ? stripslashes($members[$i]['user_from']) : "&nbsp;";

		$joined = create_date($board_config['default_dateformat'], $members[$i]['user_regdate'], $board_config['board_timezone']);

		$posts = ($members[$i]['user_posts']) ? $members[$i]['user_posts'] : 0;

		if($members[$i]['user_avatar'] != "" && $user_id != ANONYMOUS)
		{
			$poster_avatar = (strstr("http", $members[$i]['user_avatar']) && $board_config['allow_avatar_remote']) ? "<img src=\"" . $members[$i]['user_avatar'] . "\" alt=\"\" />" : "<img src=\"" . $board_config['avatar_path'] . "/" . $members[$i]['user_avatar'] . "\" alt=\"\" />";
		}
		else
		{
			$poster_avatar = "";
		}

		if( !empty($members[$i]['user_viewemail']) )
		{
			$email_uri = ( $board_config['board_email_form'] ) ? append_sid("profile.$phpEx?mode=email&amp;" . POST_USERS_URL ."=" . $members[$i]['user_id']) : "mailto:" . $members[$i]['user_email'];

			$email_img = "<a href=\"$email_uri\"><img src=\"" . $images['icon_email'] . "\" border=\"0\" alt=\"" . $lang['Send_email'] . " " . $members[$i]['username'] . "\" /></a>";
		}
		else
		{
			$email_img = "&nbsp;";
		}

		$pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post&amp;" . POST_USERS_URL . "=" . $members[$i]['user_id']) . "\"><img src=\"" . $images['icon_pm'] . "\" border=\"0\" alt=\"" . $lang['Send_private_message'] . "\" /></a>";

		if($members[$i]['user_website'] != "")
		{
			$www_img = "<a href=\"" . stripslashes($members[$i]['user_website']) . "\" target=\"_userwww\"><img src=\"" . $images['icon_www'] . "\" border=\"0\" alt=\"" . $lang['Visit_website'] . "\" /></a>";
		}
		else
		{
			$www_img = "&nbsp;";
		}

		if($members[$i]['user_icq'])
		{
			$icq_status_img = "<a href=\"http://wwp.icq.com/" . $members[$i]['user_icq'] . "#pager\"><img src=\"http://online.mirabilis.com/scripts/online.dll?icq=" . $members[$i]['user_icq'] . "&amp;img=5\" border=\"0\" alt=\"\" /></a>";

			$icq_add_img = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=" . $members[$i]['user_icq'] . "\"><img src=\"" . $images['icq'] . "\" alt=\"" . $lang['ICQ'] . "\" border=\"0\" /></a>";
		}
		else
		{
			$icq_status_img = "&nbsp;";
			$icq_add_img = "&nbsp;";
		}

		$aim_img = ($members[$i]['user_aim']) ? "<a href=\"aim:goim?screenname=" . $members[$i]['user_aim'] . "&amp;message=Hello+Are+you+there?\"><img src=\"" . $images['icon_aim'] . "\" border=\"0\" alt=\"" . $lang['AIM'] . "\" /></a>" : "&nbsp;";

		$msn_img = ($members[$i]['user_msnm']) ? "<a href=\"profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=$poster_id\"><img src=\"" . $images['icon_msnm'] . "\" border=\"0\" alt=\"" . $lang['MSNM'] . "\" /></a>" : "&nbsp;";

		$yim_img = ($members[$i]['user_yim']) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=" . $members[$i]['user_yim'] . "&.src=pg\"><img src=\"" . $images['icon_yim'] . "\" border=\"0\" alt=\"" . $lang['YIM'] . "\" /></a>" : "&nbsp;";

		$search_img = "<a href=\"" . append_sid("search.$phpEx?a=" . urlencode($members[$i]['username']) . "&amp;f=all&amp;b=0&amp;d=DESC&amp;c=100&amp;dosearch=1") . "\"><img src=\"" . $images['icon_search'] . "\" border=\"0\" alt=\"" . $lang['Search'] . "\" /></a>";

		$row_color = "#" . ( (!($i % 2)) ? $theme['td_color1'] : $theme['td_color2']);
		$row_class = (!($i % 2)) ? $theme['td_class1'] : $theme['td_class2'];

		$template->assign_block_vars("memberrow", array(
			"U_VIEWPROFILE" => append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $user_id),

			"ROW_COLOR" => $row_color,
			"ROW_CLASS" => $row_class,
			"USERNAME" => $username,
			"FROM" => $from,
			"JOINED" => $joined,
			"POSTS" => $posts,

			"AVATAR_IMG" => $poster_avatar,
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

	if($mode != "topten" || $board_config['topics_per_page'] < 10)
	{
		$sql = "SELECT count(*) AS total
			FROM " . USERS_TABLE . "
			WHERE user_id <> " . ANONYMOUS;

		if(!$count_result = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Error getting total users.", "", __LINE__, __FILE__, $sql);
		}
		else
		{
			$total = $db->sql_fetchrow($count_result);
			$total_members = $total['total'];

			$pagination = generate_pagination("memberlist.$phpEx?mode=$mode&amp;order=$sort_order", $total_members, $board_config['topics_per_page'], $start)."&nbsp;";
		}
	}
	else
	{
		$pagination = "&nbsp;";
		$total_members = 10;
	}
	$template->assign_vars(array(
		"PAGINATION" => $pagination,
		"ON_PAGE" => ( floor( $start / $board_config['topics_per_page'] ) + 1 ),
		"TOTAL_PAGES" => ceil( $total_members / $board_config['topics_per_page'] ),

		"L_OF" => $lang['of'],
		"L_PAGE" => $lang['Page'],
		"L_GOTO_PAGE" => $lang['Goto_page'])
	);
	$template->pparse("body");
}

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>