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
// Load default header
//
$phpbb_root_dir = "./../";
require('pagestart.inc');

if( isset($HTTP_POST_VARS[POST_GROUPS_URL]) || isset($HTTP_GET_VARS[POST_GROUPS_URL]) )
{
	$group_id = ( isset($HTTP_POST_VARS[POST_GROUPS_URL]) ) ? intval($HTTP_POST_VARS[POST_GROUPS_URL]) : intval($HTTP_GET_VARS[POST_GROUPS_URL]);
}
else
{
	$group_id = "";
}

if( isset($HTTP_POST_VARS['edit']) || isset($HTTP_POST_VARS['new']) )
{
	//
	// Ok they are editing a group or creating a new group
	//
	if ( isset($HTTP_POST_VARS['edit']) )
	{
		//
		// They're editing. Grab the vars.
		//
		$sql = "SELECT *
			FROM " . GROUPS_TABLE . "
			WHERE group_single_user <> " . TRUE . "
			AND group_id = $group_id";
		if(!$result = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Error getting group information", "", __LINE__, __FILE__, $sql);
		}
		if( !$db->sql_numrows($result) )
		{
			message_die(GENERAL_MESSAGE, $lang['Group_not_exist']);
		}
		$group_info = $db->sql_fetchrow($result);

		$mode = "editgroup";

	}
	else if( isset($HTTP_POST_VARS['new']) )
	{
		$group_info = array (
			"group_name" => "",
			"group_description" => "",
			"group_moderator" => "",
			"group_type" => GROUP_OPEN);
		$group_open = "checked=\"checked\"";

		$mode = "newgroup";

	}
	//
	// Ok, now we know everything about them, let's show the page.
	//
	$sql = "SELECT user_id, username
		FROM " . USERS_TABLE . "
		WHERE user_id <> " . ANONYMOUS . "
		ORDER BY username";
	$u_result = $db->sql_query($sql);
	if( !$u_result )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain user info for moderator list", "", __LINE__, __FILE__, $sql);
	}

	$user_list = $db->sql_fetchrowset($u_result);

	$select_list = "<select name=\"group_moderator\">";
	for($i = 0; $i < count($user_list); $i++)
	{
		$selected = ( $user_list[$i]['user_id'] == $group_info['group_moderator'] ) ? "selected=\"selected\"" : "";
		$select_list .= "<option value=\"" . $user_list[$i]['user_id'] . "\"$selected>" . $user_list[$i]['username'] . "</option>";
	}
	$select_list .= "</select>";

	$group_open = ( $group_info['group_type'] == GROUP_OPEN ) ? "checked=\"checked\"" : "";
	$group_closed = ( $group_info['group_type'] == GROUP_CLOSED ) ? "checked=\"checked\"" : "";
	$group_hidden = ( $group_info['group_type'] == GROUP_HIDDEN ) ? "checked=\"checked\"" : "";

	$template->set_filenames(array(
		"body" => "admin/group_edit_body.tpl")
	);

	$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" /><input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';

	$template->assign_vars(array(
		"GROUP_NAME" => $group_info['group_name'],
		"GROUP_DESCRIPTION" => $group_info['group_description'],

		"L_GROUP_TITLE" => $lang['Group'] . " " . $lang['Admin'],
		"L_GROUP_EDIT_DELETE" => ( isset($HTTP_POST_VARS['new']) ) ? $lang['New_group'] : $lang['Edit_group'], 
		"L_GROUP_NAME" => $lang['group_name'],
		"L_GROUP_DESCRIPTION" => $lang['group_description'],
		"L_GROUP_MODERATOR" => $lang['group_moderator'],
		"L_GROUP_STATUS" => $lang['group_status'],
		"L_GROUP_OPEN" => $lang['group_open'],
		"L_GROUP_CLOSED" => $lang['group_closed'],
		"L_GROUP_HIDDEN" => $lang['group_hidden'],
		"L_GROUP_DELETE" => $lang['group_delete'],
		"L_GROUP_DELETE_CHECK" => $lang['group_delete_check'],
		"L_SUBMIT" => $lang['submit_group_changes'],
		"L_RESET" => $lang['reset_group_changes'],
		"L_DELETE_MODERATOR" => $lang['delete_group_moderator'],
		"L_DELETE_MODERATOR_EXPLAIN" => $lang['delete_moderator_explain'],
		"L_YES" => $lang['Yes'],

		"S_SELECT_MODERATORS" => $select_list,
		"S_GROUP_OPEN_TYPE" => GROUP_OPEN,
		"S_GROUP_CLOSED_TYPE" => GROUP_CLOSED,
		"S_GROUP_HIDDEN_TYPE" => GROUP_HIDDEN,
		"S_GROUP_OPEN_CHECKED" => $group_open,
		"S_GROUP_CLOSED_CHECKED" => $group_closed,
		"S_GROUP_HIDDEN_CHECKED" => $group_hidden,
		"S_GROUP_ACTION" => append_sid("admin_groups.$phpEx"),
		"S_HIDDEN_FIELDS" => $s_hidden_fields)
	);

	$template->pparse('body');

}
else if( isset($HTTP_POST_VARS['group_update']) )
{
	//
	// Ok, they are submitting a group, let's save the data based on if it's new or editing
	//
	if( isset($HTTP_POST_VARS['group_delete']) )
	{
		$sql = "DELETE FROM " . GROUPS_TABLE . "
			WHERE group_id = " . $group_id;
		if ( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't update group", "", __LINE__, __FILE__, $sql);
		}

		message_die(GENERAL_MESSAGE, $lang['Deleted_group']);
	}
	else
	{
		$group_type = isset($HTTP_POST_VARS['group_type']) ? trim($HTTP_POST_VARS['group_type']) : "";
		$group_name = isset($HTTP_POST_VARS['group_name']) ? trim($HTTP_POST_VARS['group_name']) : "";
		$group_description = isset($HTTP_POST_VARS['group_description']) ? trim($HTTP_POST_VARS['group_description']) : "";
		$group_moderator = isset($HTTP_POST_VARS['group_moderator']) ? intval($HTTP_POST_VARS['group_moderator']) : "";
		$delete_old_moderator = isset($HTTP_POST_VARS['delete_old_moderator']) ? intval($HTTP_POST_VARS['delete_old_moderator']) : "";

		if( $group_name == "" )
		{
			message_die(GENERAL_MESSAGE, $lang['No_group_name']);
		}
		else if( $group_moderator == "" )
		{
			message_die(GENERAL_MESSAGE, $lang['No_group_moderator']);
		}
		else if( $group_type == "" )
		{
			message_die(GENERAL_MESSAGE, $lang['No_group_mode']);
		}
		
		if( $mode == "editgroup" )
		{
			$sql = "SELECT *
				FROM " . GROUPS_TABLE . "
				WHERE group_single_user <> " . TRUE . "
				AND group_id = " . $group_id;
			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Error getting group information", "", __LINE__, __FILE__, $sql);
			}
			if( !$db->sql_numrows($result) )
			{
				message_die(GENERAL_MESSAGE, $lang['Group_not_exist']);
			}
			$group_info = $db->sql_fetchrow($result);		
		
			if ( $group_info['group_moderator'] != $group_moderator )
			{
				if ( $delete_old_moderator != "" )
				{
					$sql = "DELETE FROM " . USER_GROUP_TABLE . "
						WHERE user_id = " . $group_info['group_moderator'] . " AND group_id = " . $group_id;
					if ( !$result = $db->sql_query($sql) )
					{
						message_die(GENERAL_ERROR, "Couldn't update group moderator", "", __LINE__, __FILE__, $sql);
					}
				}
				$sql = "INSERT INTO " . USER_GROUP_TABLE . " (group_id, user_id, user_pending)
					VALUES (" . $group_id . ", " . $group_moderator . ", 0)";
				if ( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't update group moderator", "", __LINE__, __FILE__, $sql);
				}
			}
			$sql = "UPDATE " . GROUPS_TABLE . "
				SET group_type = $group_type, group_name = '" . $group_name . "', group_description = '" . $group_description . "', group_moderator = $group_moderator 
				WHERE group_id = $group_id";
			if ( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't update group", "", __LINE__, __FILE__, $sql);
			}

			message_die(GENERAL_MESSAGE, $lang['Updated_group']);
		}
		else if( $mode == "newgroup" )
		{

			$sql = "INSERT INTO " . GROUPS_TABLE . " (group_type, group_name, group_description, group_moderator, group_single_user) 
				VALUES ('" . $group_type . "', '" . $group_name . "', '" . $group_description . "', '" . $group_moderator . "',	'0')";
			if ( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't insert new group", "", __LINE__, __FILE__, $sql);
			}

			$new_group_id = $db->sql_nextid($result);

			$sql = "INSERT INTO " . USER_GROUP_TABLE . " (group_id, user_id, user_pending)
				VALUES ($new_group_id, $group_moderator, 0)";
			if ( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't insert new user-group info", "", __LINE__, __FILE__, $sql);
			}

			message_die(GENERAL_MESSAGE, $lang['Added_new_group']);

		}
		else
		{
			message_die(GENERAL_MESSAGE, $lang['Group_mode_not_selected']);
		}
	}
}
else
{
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
		"L_CREATE_NEW_GROUP" => $lang['New_group'],

		"S_GROUP_ACTION" => append_sid("admin_groups.$phpEx"),
		"S_GROUP_SELECT" => $select_list)
	);

	$template->pparse('body');
}

include('page_footer_admin.'.$phpEx);

?>