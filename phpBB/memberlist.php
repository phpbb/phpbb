<?php
/***************************************************************************
 *                                common.php
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
 ***************************************************************************/
include('extension.inc');
include('common.'.$phpEx);

$pagetype = "memberlist";
$page_title = $l_memberslist;

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_VIEWMEMBERS, $session_length);
init_userprefs($userdata);
//
// End session management
//

include('includes/page_header.'.$phpEx);

if(!$start)
{
	$start = 0;
}
switch($mode)
{
	case 'top10':
		$sql = "SELECT username, user_id, user_viewemail, user_posts, user_regdate, user_from, user_website, user_email
				  FROM ".USERS_TABLE." WHERE user_id != ".ANONYMOUS." AND user_level != ".DELETED." ORDER BY user_posts ASC LIMIT 10";
		
	break;
	case 'alpha':
		$sql = "SELECT username, user_id, user_viewemail, user_posts, user_regdate, user_from, user_website, user_email
				  FROM ".USERS_TABLE." WHERE user_id != ".ANONYMOUS." AND user_level != ".DELETED." ORDER BY username ASC LIMIT $start, ".$board_config['topics_per_page'];
	break;
	default:
		$sql = "SELECT username, user_id, user_viewemail, user_posts, user_regdate, user_from, user_website, user_email
				  FROM ".USERS_TABLE." 	WHERE user_id != ".ANONYMOUS." AND user_level != ".DELETED." ORDER BY user_id ASC LIMIT $start, ".$board_config['topics_per_page'];
	break;
}

if(!$result = $db->sql_query($sql))
{
	if(DEBUG)
	{
		$error = $db->sql_error();
		error_die(SQL_QUERY, "Error getting memberlist.<br>Reason: ".$error['message']."<br>Query: $sql.", __LINE__, __FILE__);
	}
	else
	{
		error_die(SQL_QUERY);
	}
}
if(($selected_members = $db->sql_numrows($result)) > 0)
{
	$template->set_filenames(array("body" => "memberlist_body.tpl"));
	$template->assign_vars(array("U_VIEW_TOP10" => append_sid("memberlist.$phpEx?mode=top10"),
											"U_SORTALPHA" => append_sid("memberlist.$phpEx?mode=alpha"),
											"L_VIEW_TOP10" => $l_top10,
											"L_SORTALPHA" => $l_alpha,
											"L_EMAIL" => $l_email,
											"L_WEBSITE" => $l_website,
											"L_FROM" => $l_from));
											
	$members = $db->sql_fetchrowset($result);

	for($x = $start; $x < $selected_members; $x++)
	{
		unset($email);
		$username = stripslashes($members[$x]['username']);
		$user_id = $members[$x]['user_id'];
		$posts = $members[$x]['user_posts'];
		$from = stripslashes($members[$x]['user_from']);
		$joined = create_date($board_config['default_dateformat'], $members[$x]['user_regdate'], $board_config['default_timezone']);
		
		if($members[$x]['user_viewemail'] != 0)
		{
			$email = str_replace("@", " at ", $members[$x]['user_email']);
			$email = "<a href=\"mailto:$email\">$email</a>";
		}
		else
		{
			$email = "&nbsp;";
		}
		
		if($members[$x]['user_website'] != '')
		{
			$url_img = $images['www'];
			$url = "<a href=\"".stripslashes($members[$x]['user_website'])."\"><img src=\"".$url_img."\" border=\"0\"/></a>";
		}
		else
		{
			$url = "&nbsp;";
		}
		
		if(!($x % 2))
		{
			$row_color = "#".$theme['td_color1'];
		}
		else
		{
			$row_color = "#".$theme['td_color2'];
		}
		$template->assign_block_vars("memberrow", array(
											  "ROW_COLOR" => $row_color,
											  "U_VIEWPROFILE" => append_sid("profile.$phpEx?mode=viewprofile&".POST_USERS_URL."=".$user_id),
											  "USERNAME" => $username,
											  "FROM" => $from,
											  "JOINED" => $joined,
											  "POSTS" => $posts,
											  "EMAIL" => $email,
											  "WEBSITE" => $url));
	}
	
	if($mode != "top10")
	{
		$sql = "SELECT count(*) AS total FROM ".USERS_TABLE." WHERE user_id != ".ANONYMOUS." AND user_level != ".DELETED;
		if(!$count_result = $db->sql_query($sql))
		{
			if(DEBUG)
			{
				$error = $db->sql_error();
				error_die(SQL_QUERY, "Error getting total users<br>Reason: ".$error['message']."<br>Query: $sql", __LINE__, __FILE__);
			}
			else
			{
				error_die(SQL_QUERY);
			}
		}
		else
		{
			$total = $db->sql_fetchrowset($count_result);
			$total_members = $total[0]['total'];
			$pagination = generate_pagination("memberlist.$phpEx?mode=$mode", $total_members, $board_config['topics_per_page'], $start, TRUE)."&nbsp;";
		}
	}
	else
	{
		$pagination = "&nbsp;";
	}
	$template->assign_vars(array("PAGINATION" => $pagination));
	$template->pparse("body");
}

include('includes/page_tail.'.$phpEx);
?>	