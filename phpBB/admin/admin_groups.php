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
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

if( !empty($setmodules) )
{
	if ( !$auth->acl_get('a_group') )
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['Groups']['Create'] = $filename . "$SID&amp;mode=create";
	$module['Groups']['Delete'] = $filename . "$SID&amp;mode=delete";
	$module['Groups']['Manage'] = $filename . "$SID&amp;mode=manage";

	return;
}

define('IN_PHPBB', 1);
//
// Include files
//
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

//
// Do we have general permissions?
//
if ( !$auth->acl_get('a_group') )
{
	message_die(MESSAGE, $lang['No_admin']);
}

if( isset($_POST[POST_GROUPS_URL]) || isset($_GET[POST_GROUPS_URL]) )
{
	$group_id = ( isset($_POST[POST_GROUPS_URL]) ) ? intval($_POST[POST_GROUPS_URL]) : intval($_GET[POST_GROUPS_URL]);
}
else
{
	$group_id = '';
}

//
// Mode setting
//
if( isset($_POST['mode']) || isset($_GET['mode']) )
{
	$mode = ( isset($_POST['mode']) ) ? $_POST['mode'] : $_GET['mode'];
}
else
{
	$mode = "";
}

if( isset($_POST['edit']) || isset($_POST['new']) )
{
	//
	// Ok they are editing a group or creating a new group
	//
	$template->set_filenames(array(
		"body" => "admin/group_edit_body.tpl")
	);

	if ( isset($_POST['edit']) )
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
		$template->assign_block_vars("group_edit", array());

	}
	else if( isset($_POST['new']) )
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

	for($i = 0; $i < count($user_list); $i++)
	{
		if( $user_list[$i]['user_id'] == $group_info['group_moderator'] )
		{
			$group_moderator = $user_list[$i]['username'];
		}
	}

	$group_open = ( $group_info['group_type'] == GROUP_OPEN ) ? "checked=\"checked\"" : "";
	$group_closed = ( $group_info['group_type'] == GROUP_CLOSED ) ? "checked=\"checked\"" : "";
	$group_hidden = ( $group_info['group_type'] == GROUP_HIDDEN ) ? "checked=\"checked\"" : "";

	$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" /><input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';

	$template->assign_vars(array(
		"GROUP_NAME" => $group_info['group_name'],
		"GROUP_DESCRIPTION" => $group_info['group_description'],
		"GROUP_MODERATOR" => $group_moderator,

		"L_GROUP_TITLE" => $lang['Group_administration'],
		"L_GROUP_EDIT_DELETE" => ( isset($_POST['new']) ) ? $lang['New_group'] : $lang['Edit_group'],
		"L_GROUP_NAME" => $lang['group_name'],
		"L_GROUP_DESCRIPTION" => $lang['group_description'],
		"L_GROUP_MODERATOR" => $lang['group_moderator'],
		"L_FIND_USERNAME" => $lang['Find_username'],
		"L_GROUP_STATUS" => $lang['group_status'],
		"L_GROUP_OPEN" => $lang['group_open'],
		"L_GROUP_CLOSED" => $lang['group_closed'],
		"L_GROUP_HIDDEN" => $lang['group_hidden'],
		"L_GROUP_DELETE" => $lang['group_delete'],
		"L_GROUP_DELETE_CHECK" => $lang['group_delete_check'],
		"L_SUBMIT" => $lang['Submit'],
		"L_RESET" => $lang['Reset'],
		"L_DELETE_MODERATOR" => $lang['delete_group_moderator'],
		"L_DELETE_MODERATOR_EXPLAIN" => $lang['delete_moderator_explain'],
		"L_YES" => $lang['Yes'],

		"U_SEARCH_USER" => append_sid("../search.$phpEx?mode=searchuser"),

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
else if( isset($_POST['group_update']) )
{
	//
	// Ok, they are submitting a group, let's save the data based on if it's new or editing
	//
	if( isset($_POST['group_delete']) )
	{
		$sql = "DELETE FROM " . GROUPS_TABLE . "
			WHERE group_id = " . $group_id;
		if ( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't update group", "", __LINE__, __FILE__, $sql);
		}

		$sql = "DELETE FROM " . USER_GROUP_TABLE . "
			WHERE group_id = " . $group_id;
		if ( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't update user_group", "", __LINE__, __FILE__, $sql);
		}

		$sql = "DELETE FROM " . AUTH_ACCESS_TABLE . "
			WHERE group_id = " . $group_id;
		if ( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't update auth_access", "", __LINE__, __FILE__, $sql);
		}

		$message = $lang['Deleted_group'] . "<br /><br />" . sprintf($lang['Click_return_groupsadmin'], "<a href=\"" . append_sid("admin_groups.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");;

		message_die(GENERAL_MESSAGE, $message);
	}
	else
	{
		$group_type = isset($_POST['group_type']) ? intval($_POST['group_type']) : GROUP_OPEN;
		$group_name = isset($_POST['group_name']) ? trim($_POST['group_name']) : "";
		$group_description = isset($_POST['group_description']) ? trim($_POST['group_description']) : "";
		$group_moderator = isset($_POST['username']) ? $_POST['username'] : "";
		$delete_old_moderator = isset($_POST['delete_old_moderator']) ? intval($_POST['delete_old_moderator']) : "";

		if( $group_name == "" )
		{
			message_die(GENERAL_MESSAGE, $lang['No_group_name']);
		}
		else if( $group_moderator == "" )
		{
			message_die(GENERAL_MESSAGE, $lang['No_group_moderator']);
		}

		$this_userdata = get_userdata($group_moderator);
		$group_moderator = $this_userdata['user_id'];

		if( !$group_moderator )
		{
			message_die(GENERAL_MESSAGE, $lang['No_group_moderator']);
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
						WHERE user_id = " . $group_info['group_moderator'] . "
							AND group_id = " . $group_id;
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
				SET group_type = $group_type, group_name = '" . str_replace("\'", "''", $group_name) . "', group_description = '" . str_replace("\'", "''", $group_description) . "', group_moderator = $group_moderator
				WHERE group_id = $group_id";
			if ( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't update group", "", __LINE__, __FILE__, $sql);
			}

			$message = $lang['Updated_group'] . "<br /><br />" . sprintf($lang['Click_return_groupsadmin'], "<a href=\"" . append_sid("admin_groups.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");;

			message_die(GENERAL_MESSAGE, $message);
		}
		else if( $mode == "newgroup" )
		{
			$sql = "SELECT MAX(group_id) AS new_group_id
				FROM " . GROUPS_TABLE;
			if ( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't insert new group", "", __LINE__, __FILE__, $sql);
			}
			$row = $db->sql_fetchrow($result);

			$new_group_id = $row['new_group_id'] + 1;

			$sql = "INSERT INTO " . GROUPS_TABLE . " (group_id, group_type, group_name, group_description, group_moderator, group_single_user)
				VALUES ($new_group_id, $group_type, '" . str_replace("\'", "''", $group_name) . "', '" . str_replace("\'", "''", $group_description) . "', $group_moderator,	'0')";
			if ( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't insert new group", "", __LINE__, __FILE__, $sql);
			}

			$sql = "INSERT INTO " . USER_GROUP_TABLE . " (group_id, user_id, user_pending)
				VALUES ($new_group_id, $group_moderator, 0)";
			if ( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't insert new user-group info", "", __LINE__, __FILE__, $sql);
			}

			$message = $lang['Added_new_group'] . "<br /><br />" . sprintf($lang['Click_return_groupsadmin'], "<a href=\"" . append_sid("admin_groups.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");;

			message_die(GENERAL_MESSAGE, $message);

		}
		else
		{
			message_die(GENERAL_MESSAGE, $lang['Group_mode_not_selected']);
		}
	}
}

page_header($lang['Manage']);

?>

<h1><?php echo $lang['Manage']; ?></h1>

<p><?php echo $lang['Group_manage_explain']; ?></p>

<form method="post" action="<?php echo "admin_groups.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="3"><?php echo $lang['Manage']; ?></th>
	</tr>
<?php

	$sql = "SELECT group_id, group_name
		FROM " . GROUPS_TABLE . "
		ORDER BY group_name";
	$result = $db->sql_query($sql);

	$groups = array();
	if ( $row = $db->sql_fetchrow($result) )
	{
		do
		{
			$groups[] = $row;
		}
		while ( $row = $db->sql_fetchrow($result) );
	}

	$sql = "SELECT ug.group_id, u.user_id, u.username
		FROM " . USERS_TABLE . " u, " . USER_GROUP_TABLE . " ug
		WHERE ug.user_pending = 1
			AND u.user_id = ug.user_id
		ORDER BY ug.group_id";
	$result = $db->sql_query($sql);

	$pending = array();
	if ( $row = $db->sql_fetchrow($result) )
	{
		do
		{
			$pending[$row['group_id']][] = $row;
		}
		while ( $row = $db->sql_fetchrow($result) );
	}

	foreach ( $groups as $group_ary )
	{
		$group_id = $group_ary['group_id'];
		$group_name = ( !empty($lang[$group_ary['group_name']]) ) ? $lang[$group_ary['group_name']] : $group_ary['group_name'];

?>
	<tr>
		<td class="cat"><span class="cattitle"><?php echo $group_name;?></span></td>
		<td class="cat" align="center">&nbsp;<input class="liteoption" type="submit" name="edit[<?php echo $group_id; ?>]" value="<?php echo $lang['Edit'];?>" />&nbsp;</td>
		<td class="cat" align="center">&nbsp;<input class="liteoption" type="submit" name="delete[<?php echo $group_id; ?>]" value="<?php echo $lang['Delete'];?>" />&nbsp;</td>
	</tr>
<?php

		if ( is_array($pending[$group_id]) )
		{
			$row_class = '';
			foreach( $pending[$group_id] as $pending_ary )
			{
				$row_class = ( $row_class != 'row1' ) ? 'row1' : 'row2';
?>
	<tr>
		<td class="<?php echo $row_class; ?>"><?php echo $pending_ary['username'];?></td>
		<td class="<?php echo $row_class; ?>" align="center"><input class="liteoption" type="submit" name="approve[<?php echo $pending_ary['user_id']; ?>]" value="<?php echo $lang['Approve_selected'];?>" /></td>
		<td class="<?php echo $row_class; ?>" align="center"><input class="liteoption" type="submit" name="decline[<?php echo $pending_ary['user_id']; ?>]" value="<?php echo $lang['Deny_selected'];?>" /></td>
	</tr>
<?php
			}
		}
		else
		{
?>
	<tr>
		<td class="row1" colspan="4" align="center">No pending users</td>
	</tr>
<?php

		}
	}

?>
</table></form>

<?php

page_footer();

?>
