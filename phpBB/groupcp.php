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
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_GROUPCP, $session_length);
init_userprefs($userdata);
//
// End session management
//

if( isset($HTTP_GET_VARS[POST_GROUPS_URL]) || isset($HTTP_POST_VARS[POST_GROUPS_URL]) )
{
	$group_id = ( isset($HTTP_GET_VARS[POST_GROUPS_URL]) ) ? intval($HTTP_GET_VARS[POST_GROUPS_URL]) : intval($HTTP_POST_VARS[POST_GROUPS_URL]);
}
else
{
	$group_id = "";
}

$confirm = ( isset($HTTP_POST_VARS['confirm']) ) ? TRUE : 0;
$cancel = ( isset($HTTP_POST_VARS['cancel']) ) ? TRUE : 0;

$start = ( isset($HTTP_GET_VARS['start']) ) ? intval($HTTP_GET_VARS['start']) : 0;

$is_moderator = FALSE;

if( isset($HTTP_POST_VARS['groupstatus']) && $group_id )
{
	if( !$userdata['session_logged_in'] )
	{
		header("Location: " . append_sid("login.$phpEx?redirect=groupcp.$phpEx", true));
	}

	$sql = "SELECT group_moderator 
		FROM " . GROUPS_TABLE . "  
		WHERE group_id = $group_id";
		
	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain user and group information", "", __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);

	if( $row['group_moderator'] != $userdata['user_id'] && $userdata['user_level'] != ADMIN )
	{
		$template->assign_vars(array(
			"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("index.$phpEx") . '">')
		);

		$message = $lang["Not_group_moderator"] . "<br /><br />" . sprintf($lang['Click_return_group'], "<a href=\"" . append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_index'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

		message_die(GENERAL_MESSAGE, $message);
	}

	$sql = "UPDATE " . GROUPS_TABLE . " 
		SET group_type = " . intval($HTTP_POST_VARS['group_type']) . "
		WHERE group_id = $group_id";
	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain user and group information", "", __LINE__, __FILE__, $sql);
	}

	$template->assign_vars(array(
		"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id") . '">')
	);

	$message = $lang["Group_type_updated"] . "<br /><br />" . sprintf($lang['Click_return_group'], "<a href=\"" . append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_index'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

	message_die(GENERAL_MESSAGE, $message);

}
else if( isset($HTTP_POST_VARS['joingroup']) && $group_id )
{
	//
	// First, joining a group
	//

	//
	// If the user isn't logged in redirect them to login
	//
	if( !$userdata['session_logged_in'] )
	{
		header("Location: " . append_sid("login.$phpEx?redirect=groupcp.$phpEx", true));
	}

	$sql = "SELECT ug.user_id, g.group_type
		FROM " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g 
		WHERE g.group_id = $group_id 
			AND ug.group_id = g.group_id";
	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain user and group information", "", __LINE__, __FILE__, $sql);
	}

	$rowset = $db->sql_fetchrowset($result);

	if( $rowset[0]['group_type'] == GROUP_OPEN )
	{
		for($i = 0; $i < count($rowset); $i++ )
		{
			if( $userdata['user_id'] == $rowset[$i]['user_id'] )
			{
				$template->assign_vars(array(
					"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("index.$phpEx") . '">')
				);

				$message = $lang["Already_member_group"] . "<br /><br />" . sprintf($lang['Click_return_group'], "<a href=\"" . append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_index'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

				message_die(GENERAL_MESSAGE, $message);
			}
		}
	}
	else
	{
		$template->assign_vars(array(
			"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("index.$phpEx") . '">')
		);

		$message = $lang["This_closed_group"] . "<br /><br />" . sprintf($lang['Click_return_group'], "<a href=\"" . append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_index'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

		message_die(GENERAL_MESSAGE, $message);
	}

	$sql = "INSERT INTO " . USER_GROUP_TABLE . " (group_id, user_id, user_pending) 
		VALUES ($group_id, " . $userdata['user_id'] . ", 1)";
	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Error inserting user group subscription", "", __LINE__, __FILE__, $sql);
	}

	$sql = "SELECT u.user_email, u.username, g.group_name 
		FROM ".USERS_TABLE . " u, " . GROUPS_TABLE . " g 
		WHERE u.user_id = g.group_moderator 
			AND g.group_id = $group_id";
	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Error getting group moderator data", "", __LINE__, __FILE__, $sql);
	}

	$moderator = $db->sql_fetchrow($result);

	include($phpbb_root_path . 'includes/emailer.'.$phpEx);
	$emailer = new emailer($board_config['smtp_delivery']);

	$email_headers = "From: " . $board_config['board_email'] . "\nReturn-Path: " . $board_config['board_email'] . "\r\n";

	if( isset($HTTP_SERVER_VARS['PATH_INFO']) && dirname($HTTP_SERVER_VARS['PATH_INFO']) != '/')
	{
		$path = dirname($HTTP_SERVER_VARS['PATH_INFO']);
	}
	else if( dirname($HTTP_SERVER_VARS['SCRIPT_NAME']) != '/')
	{
		$path = dirname($HTTP_SERVER_VARS['SCRIPT_NAME']);
	}
	else
	{
		$path = '';
	}
	$server_name = ( isset($HTTP_SERVER_VARS['HTTP_HOST']) ) ? $HTTP_SERVER_VARS['HTTP_HOST'] : $HTTP_SERVER_VARS['SERVER_NAME'];
	$protocol = ( !empty($HTTP_SERVER_VARS['HTTPS']) ) ? ( ( $HTTP_SERVER_VARS['HTTPS'] == "on" ) ? "https://" : "http://" ) : "http://";

	$emailer->use_template("group_request");
	$emailer->email_address($moderator['user_email']);
	$emailer->set_subject($lang['Group_request']);
	$emailer->extra_headers($email_headers);

	$emailer->assign_vars(array(
		"SITENAME" => $board_config['sitename'], 
		"GROUP_MODERATOR" => $moderator['username'],
		"EMAIL_SIG" => str_replace("<br />", "\n", "-- \n" . $board_config['board_email_sig']), 

		"U_GROUPCP" => $protocol . $server_name . $path . "/groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id")
	);
	$emailer->send();
	$emailer->reset();

	$template->assign_vars(array(
		"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("index.$phpEx") . '">')
	);

	$message = $lang["Group_joined"] . "<br /><br />" . sprintf($lang['Click_return_group'], "<a href=\"" . append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_index'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

	message_die(GENERAL_MESSAGE, $message);
}
else if( isset($HTTP_POST_VARS['unsub']) || isset($HTTP_POST_VARS['unsubpending']) && $group_id )
{
	//
	// Second, unsubscribing from a group
	//

	//
	// Check for confirmation of unsub.
	//
	if( $cancel )
	{
		header("Location: " . append_sid("groupcp.$phpEx", true));
	}

	if( !$userdata['session_logged_in'] )
	{
		header("Location: " . append_sid("login.$phpEx?redirect=groupcp.$phpEx", true));
	}

	if( $confirm )
	{
		$sql = "DELETE FROM " . USER_GROUP_TABLE . " 
			WHERE user_id = " . $userdata['user_id'] . " 
				AND group_id = $group_id";
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Could not delete group memebership data", "Error", __LINE__, __FILE__, $sql);
		}

		$template->assign_vars(array(
			"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("index.$phpEx") . '">')
		);

		$message = $lang["Usub_success"] . "<br /><br />" . sprintf($lang['Click_return_group'], "<a href=\"" . append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_index'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

		message_die(GENERAL_MESSAGE, $message);
	}
	else
	{
		$unsub_msg = ( isset($HTTP_POST_VARS['unsub']) ) ? $lang['Confirm_unsub'] : $lang['Confirm_unsub_pending'];

		$s_hidden_fields = '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" /><input type="hidden" name="unsub" value="1" />';

		$page_title = $lang['Group_Control_Panel'];
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			"confirm" => "confirm_body.tpl")
		);

		$template->assign_vars(array(
			"MESSAGE_TITLE" => $lang['Confirm'],
			"MESSAGE_TEXT" => $unsub_msg,
			"L_YES" => $lang['Yes'],
			"L_NO" => $lang['No'],
			"S_CONFIRM_ACTION" => append_sid("groupcp.$phpEx"),
			"S_HIDDEN_FIELDS" => $s_hidden_fields)
		);

		$template->pparse("confirm");

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}

}
else if( $group_id )
{
	//
	// For security, get the ID of the group moderator.
	//
	$sql = "SELECT group_moderator 
		FROM " . GROUPS_TABLE . " 
		WHERE group_id = $group_id";
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Could not get moderator information", $lang['Error'], __LINE__, __FILE__, $sql);
	}
	$row = $db->sql_fetchrow($result);

	$group_moderator = $row['group_moderator'];
	
	if( $group_moderator == $userdata['user_id'] || $userdata['user_level'] == ADMIN )
	{
		$is_moderator = TRUE;
	}
		
	//
	// Handle Additions, removals, approvals and denials
	//
	if( $HTTP_POST_VARS['add'] || $HTTP_POST_VARS['remove'] || isset($HTTP_POST_VARS['approve']) || isset($HTTP_POST_VARS['deny']) )
	{
		if( !$is_moderator )
		{
			$template->assign_vars(array(
				"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("index.$phpEx") . '">')
			);

			$message = $lang["Not_group_moderator"] . "<br /><br />" . sprintf($lang['Click_return_index'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);
		}

		if( isset($HTTP_POST_VARS['add']) )
		{
			$username = ( isset($HTTP_POST_VARS['username']) ) ? $HTTP_POST_VARS['username'] : "";
			
			$sql = "SELECT user_id, user_email 
				FROM " . USERS_TABLE . " 
				WHERE username = '$username'";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not get user information", $lang['Error'], __LINE__, __FILE__, $sql);
			}
			$row = $db->sql_fetchrow($result);

			if( !$db->sql_numrows($result) )
			{
				$template->assign_vars(array(
					"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id") . '">')
				);

				$message = $lang["Could_not_add_user"] . "<br /><br />" . sprintf($lang['Click_return_group'], "<a href=\"" . append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_index'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

				message_die(GENERAL_MESSAGE, $message);
			}
			
			$sql = "SELECT ug.user_id 
				FROM " . USER_GROUP_TABLE . " ug 
				WHERE ug.user_id = " . $row['user_id'] . " 
					AND ug.group_id = $group_id";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not get user information", $lang['Error'], __LINE__, __FILE__, $sql);
			}

			if( !$db->sql_numrows($result) )
			{
				$sql = "INSERT INTO " . USER_GROUP_TABLE . " (user_id, group_id, user_pending) 
					VALUES (" . $row['user_id'] . ", $group_id, 0)";

				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Could not add user to group", "Error", __LINE__, __FILE__, $sql);
				}
				
				// Email the user and tell them they're in the group

				//
				// Get the group name
				//
				$group_sql = "SELECT group_name 
					FROM " . GROUPS_TABLE . " 
					WHERE group_id = $group_id";
				if(!$result = $db->sql_query($group_sql))
				{
					message_die(GENERAL_ERROR, "Could not get group information", "Error", __LINE__, __FILE__, $group_sql);
				}
				$group_name_row = $db->sql_fetchrow($result);

				$group_name = $group_name_row['group_name'];

				include($phpbb_root_path . 'includes/emailer.'.$phpEx);
				$emailer = new emailer($board_config['smtp_delivery']);

				$email_headers = "From: " . $board_config['board_email'] . "\nReturn-Path: " . $board_config['board_email'] . "\r\n";

				if( isset($HTTP_SERVER_VARS['PATH_INFO']) && dirname($HTTP_SERVER_VARS['PATH_INFO']) != '/')
				{
					$path = dirname($HTTP_SERVER_VARS['PATH_INFO']);
				}
				else if( dirname($HTTP_SERVER_VARS['SCRIPT_NAME']) != '/')
				{
					$path = dirname($HTTP_SERVER_VARS['SCRIPT_NAME']);
				}
				else
				{
					$path = '';
				}
				$server_name = ( isset($HTTP_SERVER_VARS['HTTP_HOST']) ) ? $HTTP_SERVER_VARS['HTTP_HOST'] : $HTTP_SERVER_VARS['SERVER_NAME'];
				$protocol = ( !empty($HTTP_SERVER_VARS['HTTPS']) ) ?  ( ( $HTTP_SERVER_VARS['HTTPS'] == "on" ) ? "https://" : "http://" )  : "http://";

				$emailer->use_template("group_added");
				$emailer->email_address($row['user_email']);
				$emailer->set_subject($lang['Group_added']);
				$emailer->extra_headers($email_headers);

				$emailer->assign_vars(array(
					"SITENAME" => $board_config['sitename'], 
					"GROUP_NAME" => $group_name,
					"EMAIL_SIG" => str_replace("<br />", "\n", "-- \n" . $board_config['board_email_sig']), 

					"U_GROUPCP" => $protocol . $server_name . $path . "/groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id")
				);
				$emailer->send();
				$emailer->reset();
			}
			else
			{
				$template->assign_vars(array(
					"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id") . '">')
				);

				$message = $lang["User_is_member_group"] . "<br /><br />" . sprintf($lang['Click_return_group'], "<a href=\"" . append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_index'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

				message_die(GENERAL_MESSAGE, $message);
			}
		}
		else 
		{
			if( ( ( isset($HTTP_POST_VARS['approve']) || isset($HTTP_POST_VARS['deny']) ) && isset($HTTP_POST_VARS['pending_members']) ) || ( isset($HTTP_POST_VARS['remove']) && isset($HTTP_POST_VARS['members']) ) )
			{

				$members = ( isset($HTTP_POST_VARS['approve']) || isset($HTTP_POST_VARS['deny']) ) ? $HTTP_POST_VARS['pending_members'] : $HTTP_POST_VARS['members'];

				$sql_in = "";
				for($i = 0; $i < count($members); $i++)
				{
					if($i > 0)
					{
						$sql_in .= ", ";
					}
					$sql_in .= $members[$i];
				}

				if( isset($HTTP_POST_VARS['approve']) )
				{
					$sql = "UPDATE " . USER_GROUP_TABLE . " 
						SET user_pending = 0 
						WHERE user_id IN ($sql_in) 
							AND group_id = $group_id";

					$sql_select = "SELECT user_email 
						FROM ". USERS_TABLE . " 
						WHERE user_id IN ($sql_in)";
				}
				else if( isset($HTTP_POST_VARS['deny']) || isset($HTTP_POST_VARS['remove']) )
				{
					$sql = "DELETE FROM 
						" . USER_GROUP_TABLE . " 
						WHERE user_id IN ($sql_in) 
							AND group_id = $group_id";
				}

				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Could not update user group table.", "Error", __LINE__, __FILE__, $sql);
				}
		
				//
				// Email users when they are approved
				//
				if( isset($HTTP_POST_VARS['approve']) )
				{
					if( !$result = $db->sql_query($sql_select) )
					{
						message_die(GENERAL_ERROR, "Could not get user email information", "Error", __LINE__, __FILE__, $sql);
					}
					$email_rowset = $db->sql_fetchrowset($result);

					$members_count = $db->sql_numrows($result);
		
					//
					// Get the group name
					//
					$group_sql = "SELECT group_name 
						FROM " . GROUPS_TABLE . " 
						WHERE group_id = $group_id";
					if(!$result = $db->sql_query($group_sql))
					{
						message_die(GENERAL_ERROR, "Could not get group information", "Error", __LINE__, __FILE__, $group_sql);
					}
					$group_name_row = $db->sql_fetchrow($result);

					$group_name = $group_name_row['group_name'];

					$email_addresses = "";
					for($i = 0; $i < $members_count; $i++)
					{
						if($i > 0)
						{
							$email_addresses .= ", ";
						}
						$email_addresses .= $email_rowset[$i]['user_email'];
					}

					include($phpbb_root_path . 'includes/emailer.'.$phpEx);
					$emailer = new emailer($board_config['smtp_delivery']);

					$email_headers = "From: " . $board_config['board_email'] . "\nReturn-Path: " . $board_config['board_email'] . "\r\n";

					if( isset($HTTP_SERVER_VARS['PATH_INFO']) && dirname($HTTP_SERVER_VARS['PATH_INFO']) != '/')
					{
						$path = dirname($HTTP_SERVER_VARS['PATH_INFO']);
					}
					else if( dirname($HTTP_SERVER_VARS['SCRIPT_NAME']) != '/')
					{
						$path = dirname($HTTP_SERVER_VARS['SCRIPT_NAME']);
					}
					else
					{
						$path = '';
					}
					$server_name = ( isset($HTTP_SERVER_VARS['HTTP_HOST']) ) ? $HTTP_SERVER_VARS['HTTP_HOST'] : $HTTP_SERVER_VARS['SERVER_NAME'];
					$protocol = ( !empty($HTTP_SERVER_VARS['HTTPS']) ) ?  ( ( $HTTP_SERVER_VARS['HTTPS'] == "on" ) ? "https://" : "http://" )  : "http://";

					$emailer->use_template("group_approved");
					$emailer->email_address($email_addresses);
					$emailer->set_subject($lang['Group_approved']);
					$emailer->extra_headers($email_headers);

					$emailer->assign_vars(array(
						"SITENAME" => $board_config['sitename'], 
						"GROUP_NAME" => $group_name,
						"EMAIL_SIG" => str_replace("<br />", "\n", "-- \n" . $board_config['board_email_sig']), 

						"U_GROUPCP" => $protocol . $server_name . $path . "/groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id")
					);
					$emailer->send();
					$emailer->reset();
				}
			}
		}
	}
	//
	// END approve or deny
	//
	
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
		message_die(GENERAL_MESSAGE, $lang['Group_not_exist']); 
	}
	$group_info = $db->sql_fetchrow($result);

	//
	// Get moderator details for this group
	//
	$sql = "SELECT username, user_id, user_viewemail, user_posts, user_regdate, user_from, user_website, user_email, user_icq, user_aim, user_yim, user_msnm  
		FROM " . USERS_TABLE . " 
		WHERE user_id = " . $group_info['group_moderator'];
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Error getting user list for group", "", __LINE__, __FILE__, $sql);
	}

	$group_moderator = $db->sql_fetchrow($result); 

	//
	// Get user information for this group
	//
	$sql = "SELECT u.username, u.user_id, u.user_viewemail, u.user_posts, u.user_regdate, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_msnm, ug.user_pending 
		FROM " . USERS_TABLE . " u, " . USER_GROUP_TABLE . " ug
		WHERE ug.group_id = $group_id
			AND u.user_id = ug.user_id
			AND ug.user_pending = 0 
			AND ug.user_id <> " . $group_moderator['user_id'] . " 
		ORDER BY u.username"; 
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Error getting user list for group", "", __LINE__, __FILE__, $sql);
	}

	if( $members_count = $db->sql_numrows($result) )
	{
		$group_members = $db->sql_fetchrowset($result); 
	}

	$sql = "SELECT u.username, u.user_id, u.user_viewemail, u.user_posts, u.user_regdate, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_msnm
		FROM " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug, " . USERS_TABLE . " u
		WHERE ug.group_id = $group_id
			AND g.group_id = ug.group_id
			AND ug.user_pending = 1
			AND u.user_id = ug.user_id
		ORDER BY u.username"; 
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Error getting user pending information", "", __LINE__, __FILE__, $sql);
	}

	if( $modgroup_pending_count = $db->sql_numrows($result) )
	{
		$modgroup_pending_list = $db->sql_fetchrowset($result);
	}

	$is_group_member = 0;
	if( $members_count )
	{
		for($i = 0; $i < $members_count; $i++)
		{
			if( $group_members[$i]['user_id'] == $userdata['user_id'] && $userdata['session_logged_in'] )
			{
				$is_group_member = TRUE; 
			}
		}
	}

	$is_group_pending_member = 0;
	if( $modgroup_pending_count )
	{
		for($i = 0; $i < $modgroup_pending_count; $i++)
		{
			if( $modgroup_pending_list[$i]['user_id'] == $userdata['user_id'] && $userdata['session_logged_in'] )
			{
				$is_group_pending_member = TRUE;
			}
		}
	}

	if( $userdata['user_level'] == ADMIN )
	{
		$is_moderator = TRUE;
	}

	if( $userdata['user_id'] == $group_info['group_moderator'] )
	{
		$is_moderator = TRUE;

		$group_details =  $lang['Are_group_moderator'];

		$s_hidden_fields = "<input type=\"hidden\" name=\"" . POST_GROUPS_URL . "\" value=\"$group_id\" />";
	}
	else if( $is_group_member || $is_group_pending_member )
	{
		$template->assign_block_vars("switch_unsubscribe_group_input", array());

		$group_details =  ( $is_group_pending_member ) ? $lang['Pending_this_group'] : $lang['Member_this_group'];

		$s_hidden_fields = "<input type=\"hidden\" name=\"" . POST_GROUPS_URL . "\" value=\"$group_id\" />";
	}
	else if( $userdata['user_id'] == ANONYMOUS )
	{
		$group_details =  $lang['Login_to_join'];
		$s_hidden_fields = "";
	}
	else
	{
		if( $group_info['group_type'] == GROUP_OPEN )
		{
			$template->assign_block_vars("switch_subscribe_group_input", array());

			$group_details =  $lang['This_open_group'];
			$s_hidden_fields = "<input type=\"hidden\" name=\"" . POST_GROUPS_URL . "\" value=\"$group_id\" />";
		}
		else if( $group_info['group_type'] == GROUP_CLOSED )
		{
			$group_details =  $lang['This_closed_group'];
			$s_hidden_fields = "";
		}
		else if( $group_info['group_type'] == GROUP_HIDDEN )
		{
			$group_details =  $lang['This_hidden_group'];
			$s_hidden_fields = "";
		}
	}

	$page_title = $lang['Group_Control_Panel'];
	include($phpbb_root_path . 'includes/page_header.'.$phpEx);

	//
	// Load templates
	//
	$template->set_filenames(array(
		"info" => "groupcp_info_body.tpl", 
		"pendinginfo" => "groupcp_pending_info.tpl",
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
	// Add the moderator
	//
	$username = $group_moderator['username'];
	$user_id = $group_moderator['user_id'];
	$from = $group_moderator['user_from'];

	$joined = create_date($board_config['default_dateformat'], $group_moderator['user_regdate'], $board_config['board_timezone']);

	$posts = ($group_moderator['user_posts']) ? $group_moderator['user_posts'] : 0;

	$profile_img = "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$user_id") . "\"><img src=\"" . $images['icon_profile'] . "\" alt=\"" . $lang['Read_profile'] . "\" border=\"0\" /></a>";

	$pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post&amp;" . POST_USERS_URL . "=$user_id") . "\"><img src=\"". $images['icon_pm'] . "\" alt=\"" . $lang['Private_messaging'] . "\" border=\"0\" /></a>";

	if( !empty($group_moderator['user_viewemail']) )
	{
		$email_uri = ( $board_config['board_email_form'] ) ? append_sid("profile.$phpEx?mode=email&amp;" . POST_USERS_URL ."=" . $group_moderator['user_id']) : "mailto:" . $group_moderator['user_email'];

		$email_img = "<a href=\"$email_uri\"><img src=\"" . $images['icon_email'] . "\" alt=\"" . $lang['Send_email'] . "\" border=\"0\" /></a>";
	}
	else
	{
		$email_img = "";
	}

	$www_img = ( $group_moderator['user_website'] ) ? "<a href=\"" . $group_moderator['user_website'] . "\" target=\"_userwww\"><img src=\"" . $images['icon_www'] . "\" alt=\"" . $lang['Visit_website'] . "\" border=\"0\" /></a>" : "&nbsp;";

	if( !empty($group_moderator['user_icq']) )
	{
		$icq_status_img = "<a href=\"http://wwp.icq.com/" . $group_moderator['user_icq'] . "#pager\"><img src=\"http://web.icq.com/whitepages/online?icq=" . $group_moderator['user_icq'] . "&amp;img=5\" width=\"18\" height=\"18\" border=\"0\" /></a>";

		//
		// This cannot stay like this, it needs a 'proper' solution, eg a separate
		// template for overlaying the ICQ icon, or we just do away with the icq status 
		// display (which is after all somewhat a pain in the rear :D 
		//
		if( $theme['template_name'] == "subSilver" )
		{
			$icq_add_img = '<table width="59" border="0" cellspacing="0" cellpadding="0"><tr><td nowrap="nowrap" class="icqback"><img src="images/spacer.gif" width="3" height="18" alt = "">' . $icq_status_img . '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $group_moderator['user_icq'] . '"><img src="images/spacer.gif" width="35" height="18" border="0" alt="' . $lang['ICQ'] . '" /></a></td></tr></table>'; 
			$icq_status_img = "";
		}
		else
		{
			$icq_add_img = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=" . $group_moderator['user_icq'] . "\"><img src=\"" . $images['icon_icq'] . "\" alt=\"" . $lang['ICQ'] . "\" border=\"0\" /></a>";
		}
	}
	else
	{
		$icq_status_img = "";
		$icq_add_img = "";
	}

	$aim_img = ( $group_moderator['user_aim'] ) ? "<a href=\"aim:goim?screenname=" . $group_moderator['user_aim'] . "&amp;message=Hello+Are+you+there?\"><img src=\"" . $images['icon_aim'] . "\" border=\"0\" alt=\"" . $lang['AIM'] . "\" /></a>" : "";

	$msn_img = ( $group_moderator['user_msnm'] ) ? "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $group_moderator['user_id']) . "\"><img src=\"" . $images['icon_msnm'] . "\" border=\"0\" alt=\"" . $lang['MSNM'] . "\" /></a>" : "";

	$yim_img = ( $group_moderator['user_yim'] ) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=" . $group_moderator['user_yim'] . "&amp;.src=pg\"><img src=\"" . $images['icon_yim'] . "\" border=\"0\" alt=\"" . $lang['YIM'] . "\" /></a>" : "";

	$search_img = "<a href=\"" . append_sid("search.$phpEx?search_author=" . urlencode($group_moderator['username']) . "&amp;showresults=topics") . "\"><img src=\"" . $images['icon_search'] . "\" border=\"0\" alt=\"" . $lang['Search_user_posts'] . "\" /></a>";
		
	$template->assign_vars(array(
		"L_GROUP_INFORMATION" => $lang['Group_Information'],
		"L_GROUP_NAME" => $lang['Group_name'],
		"L_GROUP_DESC" => $lang['Group_description'],
		"L_GROUP_TYPE" => $lang['Group_type'],
		"L_GROUP_MEMBERSHIP" => $lang['Group_membership'],
		"L_SUBSCRIBE" => $lang['Subscribe'],
		"L_UNSUBSCRIBE" => $lang['Unsubscribe'],
		"L_JOIN_GROUP" => $lang['Join_group'], 
		"L_UNSUBSCRIBE_GROUP" => $lang['Unsubscribe'], 
		"L_GROUP_OPEN" => $lang['Group_open'],
		"L_GROUP_CLOSED" => $lang['Group_closed'],
		"L_GROUP_HIDDEN" => $lang['Group_hidden'], 
		"L_UPDATE" => $lang['Update'], 

		"GROUP_NAME" => $group_info['group_name'],
		"GROUP_DESC" => $group_info['group_description'],
		"GROUP_DETAILS" => $group_details,

		"S_GROUP_OPEN_TYPE" => GROUP_OPEN,
		"S_GROUP_CLOSED_TYPE" => GROUP_CLOSED,
		"S_GROUP_HIDDEN_TYPE" => GROUP_HIDDEN,
		"S_GROUP_OPEN_CHECKED" => ( $group_info['group_type'] == GROUP_OPEN ) ? "checked=\"checked\"" : "",
		"S_GROUP_CLOSED_CHECKED" => ( $group_info['group_type'] == GROUP_CLOSED ) ? "checked=\"checked\"" : "",
		"S_GROUP_HIDDEN_CHECKED" => ( $group_info['group_type'] == GROUP_HIDDEN ) ? "checked=\"checked\"" : "",
		"S_HIDDEN_FIELDS" => $s_hidden_fields)
	);

	//
	// Generate memberlist if there any!
	//
	$template->assign_vars(array(
		"L_GROUP_MODERATOR" => $lang['Group_Moderator'], 
		"L_GROUP_MEMBERS" => $lang['Group_Members'], 
		"L_PENDING_MEMBERS" => $lang['Pending_members'], 
		"L_SELECT_SORT_METHOD" => $lang['Select_sort_method'], 
		"L_PM" => $lang['Private_Message'], 
		"L_EMAIL" => $lang['Email'],
		"L_WEBSITE" => $lang['Website'],
		"L_FROM" => $lang['Location'],
		"L_ORDER" => $lang['Order'],
		"L_SORT" => $lang['Sort'],
		"L_SUBMIT" => $lang['Sort'],
		"L_AIM" => $lang['AIM'],
		"L_YIM" => $lang['YIM'],
		"L_MSNM" => $lang['MSNM'],
		"L_ICQ" => $lang['ICQ'],
		"L_SELECT" => $lang['Select'],
		"L_REMOVE_SELECTED" => $lang['Remove_selected'],
		"L_ADD_MEMBER" => $lang['Add_member'],
		"L_FIND_USERNAME" => $lang['Find_username'],

		"MOD_ROW_COLOR" => "#" . $theme['td_color1'],
		"MOD_ROW_CLASS" => $theme['td_class1'],
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
		"MOD_SEARCH_IMG" => $search, 

		"U_MOD_VIEWPROFILE" => append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $user_id),
		"U_SEARCH_USER" => append_sid("search.$phpEx?mode=searchuser"), 

		"S_MODE_SELECT" => $select_sort_mode,
		"S_ORDER_SELECT" => $select_sort_order,
		"S_GROUPCP_ACTION" => append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id"))
	);

	//
	// Dump out the remaining users
	//
	for($i = $start; $i < min($board_config['topics_per_page'] + $start, $members_count); $i++)
	{
		$username = $group_members[$i]['username'];
		$user_id = $group_members[$i]['user_id'];

		$from = $group_members[$i]['user_from'];

		$joined = create_date($board_config['default_dateformat'], $group_members[$i]['user_regdate'], $board_config['board_timezone']);

		$posts = ($group_members[$i]['user_posts']) ? $group_members[$i]['user_posts'] : 0;

		$profile_img = "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$user_id") . "\"><img src=\"" . $images['icon_profile'] . "\" alt=\"" . $lang['Read_profile'] . "\" border=\"0\" /></a>";

		$pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post&amp;" . POST_USERS_URL . "=$user_id") . "\"><img src=\"". $images['icon_pm'] . "\" alt=\"" . $lang['Private_messaging'] . "\" border=\"0\" /></a>";

		if( !empty($group_members[$i]['user_viewemail']) )
		{
			$email_uri = ( $board_config['board_email_form'] ) ? append_sid("profile.$phpEx?mode=email&amp;" . POST_USERS_URL ."=" . $group_members[$i]['user_id']) : "mailto:" . $group_members[$i]['user_email'];

			$email_img = "<a href=\"$email_uri\"><img src=\"" . $images['icon_email'] . "\" alt=\"" . $lang['Send_email'] . "\" border=\"0\" /></a>";
		}
		else
		{
			$email_img = "";
		}

		$www_img = ( $group_members[$i]['user_website'] ) ? "<a href=\"" . $group_members[$i]['user_website'] . "\" target=\"_userwww\"><img src=\"" . $images['icon_www'] . "\" alt=\"" . $lang['Visit_website'] . "\" border=\"0\" /></a>" : "&nbsp;";

		if( !empty($group_members[$i]['user_icq']) )
		{
			$icq_status_img = "<a href=\"http://wwp.icq.com/" . $group_members[$i]['user_icq'] . "#pager\"><img src=\"http://web.icq.com/whitepages/online?icq=" . $group_members[$i]['user_icq'] . "&amp;img=5\" width=\"18\" height=\"18\" border=\"0\" /></a>";

			//
			// This cannot stay like this, it needs a 'proper' solution, eg a separate
			// template for overlaying the ICQ icon, or we just do away with the icq status 
			// display (which is after all somewhat a pain in the rear :D 
			//
			if( $theme['template_name'] == "subSilver" )
			{
				$icq_add_img = '<table width="59" border="0" cellspacing="0" cellpadding="0"><tr><td nowrap="nowrap" class="icqback"><img src="images/spacer.gif" width="3" height="18" alt = "">' . $icq_status_img . '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $group_members[$i]['user_icq'] . '"><img src="images/spacer.gif" width="35" height="18" border="0" alt="' . $lang['ICQ'] . '" /></a></td></tr></table>'; 
				$icq_status_img = "";
			}
			else
			{
				$icq_add_img = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=" . $group_members[$i]['user_icq'] . "\"><img src=\"" . $images['icon_icq'] . "\" alt=\"" . $lang['ICQ'] . "\" border=\"0\" /></a>";
			}
		}
		else
		{
			$icq_status_img = "";
			$icq_add_img = "";
		}

		$aim_img = ( $group_members[$i]['user_aim'] ) ? "<a href=\"aim:goim?screenname=" . $group_members[$i]['user_aim'] . "&amp;message=Hello+Are+you+there?\"><img src=\"" . $images['icon_aim'] . "\" border=\"0\" alt=\"" . $lang['AIM'] . "\" /></a>" : "";

		$msn_img = ( $group_members[$i]['user_msnm'] ) ? "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $group_members[$i]['user_id']) . "\"><img src=\"" . $images['icon_msnm'] . "\" border=\"0\" alt=\"" . $lang['MSNM'] . "\" /></a>" : "";

		$yim_img = ( $group_members[$i]['user_yim'] ) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=" . $group_members[$i]['user_yim'] . "&amp;.src=pg\"><img src=\"" . $images['icon_yim'] . "\" border=\"0\" alt=\"" . $lang['YIM'] . "\" /></a>" : "";

		$search_img = "<a href=\"" . append_sid("search.$phpEx?search_author=" . urlencode($group_members[$i]['username']) . "&amp;showresults=topics") . "\"><img src=\"" . $images['icon_search'] . "\" border=\"0\" alt=\"" . $lang['Search_user_posts'] . "\" /></a>";
			
		if( $group_info['group_type'] != GROUP_HIDDEN || $is_group_member || $is_moderator )
		{
			$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
			$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

			$template->assign_block_vars("member_row", array(
				"ROW_COLOR" => "#" . $row_color,
				"ROW_CLASS" => $row_class,
				"USERNAME" => $username,
				"FROM" => $from,
				"JOINED" => $joined,
				"POSTS" => $posts,

				"USER_ID" => $user_id, 

				"EMAIL_IMG" => $email_img,
				"PM_IMG" => $pm_img,
				"WWW_IMG" => $www_img,
				"ICQ_STATUS_IMG" => $icq_status_img,
				"ICQ_ADD_IMG" => $icq_add_img,
				"AIM_IMG" => $aim_img,
				"YIM_IMG" => $yim_img,
				"MSN_IMG" => $msn_img,
				"SEARCH_IMG" => $search,

				"U_VIEWPROFILE" => append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $user_id))
			);

			if( $is_moderator )
			{
				$template->assign_block_vars("member_row.switch_mod_option", array());
			}
		}
	}

	if( !$members_count )
	{
		//
		// No group members
		//
		$template->assign_block_vars("switch_no_members", array());

		$template->assign_vars(array(
			"L_NO_MEMBERS" => $lang['No_group_members'])
		);
	}

	$template->assign_vars(array(
		"PAGINATION" => generate_pagination("groupcp.$phpEx?" . POST_GROUPS_URL . "=$group_id", $members_count, $board_config['topics_per_page'], $start),
		"PAGE_NUMBER" => sprintf($lang['Page_of'], ( floor( $start / $board_config['topics_per_page'] ) + 1 ), ceil( $members_count / $board_config['topics_per_page'] )), 

		"L_GOTO_PAGE" => $lang['Goto_page'])
	);

	if( $group_info['group_type'] == GROUP_HIDDEN && !$is_group_member && !$is_moderator )
	{
		//
		// No group members
		//
		$template->assign_block_vars("switch_hidden_group", array());

		$template->assign_vars(array(
			"L_HIDDEN_MEMBERS" => $lang['Group_hidden_members'])
		);
	}

	//
	// We've displayed the members who belong to the group, now we 
	// do that pending memebers... 
	//
	if( $is_moderator )
	{
		//
		// Users pending in ONLY THIS GROUP (which is moderated by this user)
		//
		if( $modgroup_pending_count )
		{
			for($i = 0; $i < $modgroup_pending_count; $i++)
			{
				$username = $modgroup_pending_list[$i]['username'];
				$user_id = $modgroup_pending_list[$i]['user_id'];

				$from = $modgroup_pending_list[$i]['user_from'];

				$joined = create_date($board_config['default_dateformat'], $modgroup_pending_list[$i]['user_regdate'], $board_config['board_timezone']);

				$posts = ( $modgroup_pending_list[$i]['user_posts'] ) ? $modgroup_pending_list[$i]['user_posts'] : 0;

				$profile_img = "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$user_id") . "\"><img src=\"" . $images['icon_profile'] . "\" alt=\"" . $lang['Read_profile'] . "\" border=\"0\" /></a>";

				$pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post&amp;" . POST_USERS_URL . "=$user_id") . "\"><img src=\"". $images['icon_pm'] . "\" alt=\"" . $lang['Private_messaging'] . "\" border=\"0\" /></a>";

				if( !empty($modgroup_pending_list[$i]['user_viewemail']) )
				{
					$email_uri = ( $board_config['board_email_form'] ) ? append_sid("profile.$phpEx?mode=email&amp;" . POST_USERS_URL ."=" . $modgroup_pending_list[$i]['user_id']) : "mailto:" . $modgroup_pending_list[$i]['user_email'];

					$email_img = "<a href=\"$email_uri\"><img src=\"" . $images['icon_email'] . "\" alt=\"" . $lang['Send_email'] . " " . $modgroup_pending_list[$i]['username'] . "\" border=\"0\" /></a>";
				}
				else
				{
					$email_img = "";
				}

				$www_img = ( $modgroup_pending_list[$i]['user_website'] ) ? "<a href=\"" . $modgroup_pending_list[$i]['user_website'] . "\" target=\"_userwww\"><img src=\"" . $images['icon_www'] . "\" alt=\"" . $lang['Visit_website'] . "\" border=\"0\" /></a>" : "";

				if( !empty($modgroup_pending_list[$i]['user_icq']) )
				{
					$icq_status_img = "<a href=\"http://wwp.icq.com/" . $modgroup_pending_list[$i]['user_icq'] . "#pager\"><img src=\"http://web.icq.com/whitepages/online?icq=" . $modgroup_pending_list[$i]['user_icq'] . "&amp;img=5\" width=\"18\" height=\"18\" border=\"0\" /></a>";

					//
					// This cannot stay like this, it needs a 'proper' solution, eg a separate
					// template for overlaying the ICQ icon, or we just do away with the icq status 
					// display (which is after all somewhat a pain in the rear :D 
					//
					if( $theme['template_name'] == "subSilver" )
					{
						$icq_add_img = '<table width="59" border="0" cellspacing="0" cellpadding="0"><tr><td nowrap="nowrap" class="icqback"><img src="images/spacer.gif" width="3" height="18" alt = "">' . $icq_status_img . '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $modgroup_pending_list[$i]['user_icq'] . '"><img src="images/spacer.gif" width="35" height="18" border="0" alt="' . $lang['ICQ'] . '" /></a></td></tr></table>'; 
						$icq_status_img = "";
					}
					else
					{
						$icq_add_img = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=" . $modgroup_pending_list[$i]['user_icq'] . "\"><img src=\"" . $images['icon_icq'] . "\" alt=\"" . $lang['ICQ'] . "\" border=\"0\" /></a>";
					}
				}
				else
				{
					$icq_status_img = "";
					$icq_add_img = "";
				}

				$aim_img = ( $modgroup_pending_list[$i]['user_aim'] ) ? "<a href=\"aim:goim?screenname=" . $modgroup_pending_list[$i]['user_aim'] . "&amp;message=Hello+Are+you+there?\"><img src=\"" . $images['icon_aim'] . "\" border=\"0\" alt=\"" . $lang['AIM'] . "\" /></a>" : "";

				$msn_img = ( $modgroup_pending_list[$i]['user_msnm'] ) ? "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $modgroup_pending_list[$i]['user_id']) . "\"><img src=\"" . $images['icon_msnm'] . "\" border=\"0\" alt=\"" . $lang['MSNM'] . "\" /></a>" : "";

				$yim_img = ( $modgroup_pending_list[$i]['user_yim'] ) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=" . $modgroup_pending_list[$i]['user_yim'] . "&amp;.src=pg\"><img src=\"" . $images['icon_yim'] . "\" border=\"0\" alt=\"" . $lang['YIM'] . "\" /></a>" : "";

				$search_img = "<a href=\"" . append_sid("search.$phpEx?search_author=" . urlencode($modgroup_pending_list[$i]['username']) . "&amp;showresults=topics") . "\"><img src=\"" . $images['icon_search'] . "\" border=\"0\" alt=\"" . $lang['Search_user_posts'] . "\" /></a>";

				$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
				$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

				$user_select = '<input type="checkbox" name="member[]" value="' . $user_id . '">';

				$template->assign_block_vars("pending_members_row", array(
					"U_VIEWPROFILE" => append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $user_id),

					"ROW_CLASS" => $row_class,
					"USERNAME" => $username,
					"FROM" => $from,
					"JOINED" => $joined,
					"POSTS" => $posts,

					"USER_ID" => $user_id, 

					"EMAIL_IMG" => $email_img,
					"PM_IMG" => $pm_img,
					"SEARCH_IMG" => $search, 
					"WWW_IMG" => $www_img,
					"ICQ_STATUS_IMG" => $icq_status_img,
					"ICQ_ADD_IMG" => $icq_add_img,
					"AIM_IMG" => $aim_img,
					"YIM_IMG" => $yim_img,
					"MSN_IMG" => $msn_img,
					"SELECT" => $user_select)
				);
			}

			$template->assign_block_vars("switch_pending_members", array() );

			$template->assign_vars(array(
				"L_SELECT" => $lang['Select'],
				"L_APPROVE_SELECTED" => $lang['Approve_selected'],
				"L_DENY_SELECTED" => $lang['Deny_selected'])
			);

			$template->assign_var_from_handle("PENDING_USER_BOX", "pendinginfo");
		
		}
	}

	if( $is_moderator )
	{
		$template->assign_block_vars("switch_mod_option", array());
		$template->assign_block_vars("switch_add_member", array());
	}



	//
	// Parse group info output
	//
	$template->pparse("info");

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
		message_die(GENERAL_MESSAGE, $lang['No_groups_exist']); 
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

	$s_member_groups = '<select name="' . POST_GROUPS_URL . '">';
	$s_member_groups_opt = "";
	$s_pending_groups = '<select name="' . POST_GROUPS_URL . '">';
	$s_pending_groups_opt = "";

	for($i = 0; $i < count($membergroup_list); $i++)
	{
		if( $membergroup_list[$i]['user_pending'] )
		{
			$s_pending_groups_opt .= '<option value="' . $membergroup_list[$i]['group_id'] . '">' . $membergroup_list[$i]['group_name'] . '</option>';
		}
		else
		{
			$s_member_groups_opt .= '<option value="' . $membergroup_list[$i]['group_id'] . '">' . $membergroup_list[$i]['group_name'] . '</option>';
		}
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
	$s_group_list .= $s_group_list_opt . "</select>";

	//
	// Load and process templates
	//
	include($phpbb_root_path . 'includes/page_header.'.$phpEx);

	$template->set_filenames(array(
		"user" => "groupcp_user_body.tpl",
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

	if($s_pending_groups_opt != "" || $s_member_groups_opt != "")
	{
		$template->assign_block_vars("groups_joined", array() );
	}

	if( $s_member_groups_opt != "" )
	{
		$template->assign_block_vars("groups_joined.groups_member", array() );
	}

	if( $s_pending_groups_opt != "" )
	{
		$template->assign_block_vars("groups_joined.groups_pending", array() );
	}

	if( $s_group_list_opt != "")
	{
		$template->assign_block_vars("groups_remaining", array() );
	}

	$template->assign_vars(array(
		"L_GROUP_MEMBERSHIP_DETAILS" => $lang['Group_member_details'],
		"L_JOIN_A_GROUP" => $lang['Group_member_join'],
		"L_YOU_BELONG_GROUPS" => $lang['Current_memberships'],
		"L_SELECT_A_GROUP" => $lang['Non_member_groups'],
		"L_PENDING_GROUPS" => $lang['Memberships_pending'],
		"L_SUBSCRIBE" => $lang['Subscribe'],
		"L_UNSUBSCRIBE" => $lang['Unsubscribe'],
		"L_VIEW_INFORMATION" => $lang['View_Information'], 

		"S_USERGROUP_ACTION" => append_sid("groupcp.$phpEx"), 

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