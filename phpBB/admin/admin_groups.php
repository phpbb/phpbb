<?php
/***************************************************************************
 *                             admin_groups.php
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
	$module['Groups']['Manage'] = $filename;

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

if( (isset($HTTP_POST_VARS['mode']) || isset($HTTP_GET_VARS['mode'])) && empty($HTTP_POST_VARS['updategroup']))
{

	//
	// Ok they are editing a group or creating a new group
	//
	include("page_header_admin." . $phpEx);
	if ( $HTTP_POST_VARS['mode'] == "editgroup" )
	{
		//
		// They're editing. Grab the vars.
		//
		$sql = "SELECT *
			FROM " . GROUPS_TABLE . "
			WHERE group_single_user <> " . TRUE . "
			AND group_id = " . $g;
		if(!$result = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Error getting group information", "", __LINE__, __FILE__, $sql);
		}
		if( !$db->sql_numrows($result) )
		{
			message_die(GENERAL_MESSAGE, "That user group does not exist");
		}
		$group_info = $db->sql_fetchrow($result);
	}
	else if ( $HTTP_GET_VARS['mode'] == "newgroup" )
	{
		$group_info = array (
			"group_name" => "",
			"group_description" => "",
			"group_moderator" => "",
			"group_type" => "1"
		);
		$group_open = "checked=\"checked\"";
	}
	//
	// Ok, now we know everything about them, let's show the page.
	//
	$sql = "SELECT user_id, username
		FROM " . USERS_TABLE . "
		WHERE user_id <> " . ANONYMOUS . "
		ORDER BY username";
	$u_result = $db->sql_query($sql);
	$user_list = $db->sql_fetchrowset($u_result);

	$select_list = "<select name=\"group_moderator\">";
	for($i = 0; $i < count($user_list); $i++)
	{
		if( $user_list[$i]['user_id'] == $group_info['group_moderator'] )
		{
			$select_list .= "<option selected value=\"" . $user_list[$i]['user_id'] . "\">" . $user_list[$i]['username'] . "</option>";
		}
		else
		{
			$select_list .= "<option value=\"" . $user_list[$i]['user_id'] . "\">" . $user_list[$i]['username'] . "</option>";
		}
	}
	$select_list .= "</select>";
	$template->set_filenames(array(
		"body" => "admin/group_edit_body.tpl")
	);
	if( !empty($group_info['group_type']) )
	{
		$group_open = "checked=\"checked\"";
	}
	else
	{
		$group_closed = "checked=\"checked\"";
	}
	$template->assign_vars(array(
		"L_GROUP_INFO" => $lang['Group_edit_explain'],
		"L_GROUP_NAME" => $lang['group_name'],
		"L_GROUP_DESCRIPTION" => $lang['group_description'],
		"L_GROUP_MODERATOR" => $lang['group_moderator'],
		"L_GROUP_STATUS" => $lang['group_status'],
		"L_GROUP_OPEN" => $lang['group_open'],
		"L_GROUP_CLOSED" => $lang['group_closed'],
		"L_GROUP_DELETE" => $lang['group_delete'],
		"L_GROUP_DELETE_CHECK" => $lang['group_delete_check'],
		"L_SUBMIT" => $lang['submit_group_changes'],
		"L_RESET" => $lang['reset_group_changes'],

		"S_GROUP_NAME" => $group_info['group_name'],
		"S_GROUP_DESCRIPTION" => $group_info['group_description'],
		"S_GROUP_MODERATOR" => $select_list,
		"S_GROUP_OPEN_CHECKED" => $group_open,
		"S_GROUP_CLOSED_CHECKED" => $group_closed,
		"S_GROUP_ACTION" => append_sid("admin_groups.$phpEx"),
		"S_GROUP_MODE" => $mode,
		"GROUP_ID" => $g)
	);
	$template->pparse('body');
}
else if( $HTTP_POST_VARS['updategroup'] == "update" )
{
	//
	// Ok, they are submitting a group, let's save the data based on if it's new or editing
	//
	if( isset($deletegroup) )
	{
		$sql = "DELETE FROM " . GROUPS_TABLE . "
			WHERE group_id = " . $group_id;
	}
	else
	{
		switch($mode)
		{
			case 'editgroup':
				$sql = "UPDATE " . GROUPS_TABLE . "
					SET group_type = '" . $group_type . "',
					group_name = '" . $group_name . "',
					group_description = '" . $group_description . "',
					group_moderator = '" . $group_moderator . "'
					WHERE group_id = '" . $group_id . "'";
				break;

			case 'newgroup':
				$sql = "INSERT INTO " . GROUPS_TABLE . "
					(
						group_type,
						group_name,
						group_description,
						group_moderator,
						group_single_user
					)
					VALUES
					(
						'" . $group_type . "',
						'" . $group_name . "',
						'" . $group_description . "',
						'" . $group_moderator . "',
						'0'
					)";

			break;

			case 'default':
				message_die(GENERAL_ERROR, $lang['Group_mode_not_selected']);
			break;
		}
	}
	if ( !$result = $db->sql_query($sql) )
	{
		$error = TRUE;
	}
	if ( $mode == "newgroup" )
	{
		$sql = "SELECT * FROM " . GROUPS_TABLE . "
			WHERE group_name = '" . $group_name . "'";
		if ( !$result = $db->sql_query($sql) )
		{
			$error = TRUE;
		}
		$group_info = $db->sql_fetchrow($result);
		$sql = "INSERT INTO " . USER_GROUP_TABLE . "
			(
				group_id,
				user_id,
				user_pending
			)
			VALUES
			(
				'" . $group_info['group_id'] . "',
				'" . $group_info['group_moderator'] . "',
				'0'
			)";
		if ( !$result = $db->sql_query($sql) )
		{
			$error = TRUE;
		}
	}
	if ( isset($error) )
	{
		message_die(GENERAL_ERROR, $lang['Error_updating_groups'], __LINE__, __FILE__, $sql);
	}
	else
	{
	message_die(GENERALL_MESSAGE, $lang['Success_updating_groups']);
	}
}
else
{
	include("page_header_admin." . $phpEx);

	$sql = "SELECT group_id, group_name
		FROM " . GROUPS_TABLE . "
		WHERE group_single_user <> " . TRUE . "
		ORDER BY group_name";
	$g_result = $db->sql_query($sql);
	$group_list = $db->sql_fetchrowset($g_result);

	$select_list = "<select name=\"" . POST_GROUPS_URL . "\">";
	for($i = 0; $i < count($group_list); $i++)
	{
		$select_list .= "<option value=\"" . $group_list[$i]['group_id'] . "\">" . $group_list[$i]['group_name'] . "</option>";
	}
	$select_list .= "</select>";

	$template->set_filenames(array(
		"body" => "admin/group_select_body.tpl")
	);

	$template->assign_vars(array(
		"L_GROUP_TITLE" => $lang['Group'] . " " . $lang['Admin'],
		"L_GROUP_EXPLAIN" => $lang['Group_admin_explain'],
		"L_GROUP_SELECT" => $lang['Select_a'] . " " . $lang['Group'],
		"L_LOOK_UP" => $lang['Look_up'] . " " . $lang['Group'],
		"L_GROUP_NEW" => $lang['New_group'],

		"S_GROUP_ACTION" => append_sid("admin_groups.$phpEx"),
		"S_GROUP_SELECT" => $select_list)
	);

	$template->pparse('body');
}
include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>