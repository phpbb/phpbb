<?php
/***************************************************************************
 *                           admin_permissions.php
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

if ( !empty($setmodules) )
{
	if ( !$acl->get_acl_admin('auth') )
	{
		return;
	}
	
	$filename = basename(__FILE__);
	$module['Forums']['Permissions']   = $filename . $SID . '&amp;mode=forums';
	$module['Forums']['Moderators']   = $filename . $SID . '&amp;mode=moderators';
	$module['General']['Administrators']   = $filename . $SID . '&amp;mode=administrators';

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
// Do we have forum admin permissions?
//
if ( !$acl->get_acl_admin('auth') )
{
	message_die(MESSAGE, $lang['No_admin']);
}

//
// Define some vars
//
if ( isset($HTTP_GET_VARS['f']) || isset($HTTP_POST_VARS['f']) )
{
	$forum_id = ( isset($HTTP_POST_VARS['f']) ) ? intval($HTTP_POST_VARS['f']) : intval($HTTP_GET_VARS['f']);
	$forum_sql = " WHERE forum_id = $forum_id";
}
else
{
	unset($forum_id);
	$forum_sql = '';
}

$mode = ( isset($HTTP_GET_VARS['mode']) ) ? $HTTP_GET_VARS['mode'] : $HTTP_POST_VARS['mode'];

//
// Start program proper
//
switch ( $mode )
{
	case 'forums':
		$l_title = $lang['Permissions'];
		$l_title_explain = $lang['Permissions_explain'];
		break;
	case 'moderators':
		$l_title = $lang['Moderators'];
		$l_title_explain = $lang['Moderators_explain'];
		break;
	case 'administrators':
		$l_title = $lang['Administrators'];
		$l_title_explain = $lang['Administrators_explain'];
		break;
}

//
// Get required information, either all forums if
// no id was specified or just the requsted if it
// was
//
if ( !empty($forum_id) || $mode == 'administrators' )
{
	//
	// Clear some vars, grab some info if relevant ...
	//
	$s_hidden_fields = '';
	if ( !empty($forum_id) )
	{
		$sql = "SELECT forum_name  
			FROM " . FORUMS_TABLE . " 
			WHERE forum_id = $forum_id";
		$result = $db->sql_query($sql);

		$forum_info = $db->sql_fetchrow($result);

		$l_title .= ' : <i>' . $forum_info['forum_name'] . '</i>';
		$s_hidden_fields = '<input type="hidden" name="f" value="' . $forum_id .'" />';
	}

	//
	// Generate header
	// 
	page_header($lang['Forums']);

?>

<h1><?php echo $l_title; ?></h1>

<p><?php echo $l_title_explain; ?></p>

<?php

	switch ( $mode )
	{
		case 'forums':

?>

<form method="post" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table width="50%" class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $lang['Allowed_users']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center"><select name="user_allowed" multiple="multiple" size="4"><?php echo $user_allowed_options; ?></select><br />[ <a href=""><?php echo $lang['Advanced']; ?></a> ]</td>
	</tr>
	<tr>
		<td class="cat" align="center"><input class="liteoption" type="submit" name="adduser" value="Add New User" /> &nbsp; <input class="liteoption" type="submit" name="deluser" value="Remove User" /></td>
	</tr>
</table>

<br clear="all" />

<table class="bg" width="50%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $lang['Disallowed_users']; ?></th>
	</tr>
	<tr>
		<td class="row2" align="center"><select name="user_disallowed" multiple="multiple" size="4"><?php echo $user_allowed_options; ?></select><br />[ <a href=""><?php echo $lang['Advanced']; ?></a> ]</td>
	</tr>
	<tr>
		<td class="cat" align="center"><input class="liteoption" type="submit" name="adduser" value="Add New User" /> &nbsp; <input class="liteoption" type="submit" name="deluser" value="Remove User" /></td>
	</tr>
</table>

<br clear="all" />

<table class="bg" width="50%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $lang['Allowed_groups']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center"><select name="group_allowed" multiple="multiple" size="4"><?php echo $group_allowed_options; ?></select><br />[ <a href=""><?php echo $lang['Advanced']; ?></a> ]</td>
	</tr>
	<tr>
		<td class="cat" align="center"><input class="liteoption" type="submit" name="addgroup" value="Add New Group" /> &nbsp; <input class="liteoption" type="submit" name="delgroup" value="Remove Group" /></td>
	</tr>
</table>

<br clear="all" />

<table class="bg" width="50%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $lang['Disallowed_groups']; ?></th>
	</tr>
	<tr>
		<td class="row2" align="center"><select name="group_disallowed" multiple="multiple" size="4"><?php echo $group_disallowed_options; ?></select><br />[ <a href=""><?php echo $lang['Advanced']; ?></a> ]</td>
	</tr>
	<tr>
		<td class="cat" align="center"><input class="liteoption" type="submit" name="addgroup" value="Add New Group" /> &nbsp; <input class="liteoption" type="submit" name="delgroup" value="Remove Group" /></td>
	</tr>
</table>

<?php
			break;

		case 'moderators':
			$sql = "SELECT auth_option 
				FROM " . ACL_OPTIONS_TABLE . " 
				WHERE auth_type LIKE 'mod'";
			$result = $db->sql_query($sql);

			$auth_options = array();
			while ( $row = $db->sql_fetchrow($result) ) 
			{
				$auth_options[] = $row;
			}

			$sql = "SELECT u.user_id, u.username, ao.auth_option 
				FROM " . USERS_TABLE . " u, " . ACL_USERS_TABLE . " au, " . ACL_OPTIONS_TABLE . " ao 
				WHERE ao.auth_type LIKE 'mod' 
					AND au.auth_option_id = ao.auth_option_id 
					AND au.forum_id = $forum_id 
					AND u.user_id = au.user_id
				ORDER BY u.username, u.user_regdate ASC";
			$result = $db->sql_query($sql);

			$auth_users = array();
			while ( $row = $db->sql_fetchrow($result) )
			{
				$auth_users[$row['auth_option']] .= '<option value="' . $row['user_id'] . '">' . $row['username'] . '</option>';
			}

			$sql = "SELECT g.group_id, g.group_name, ao.auth_option 
				FROM " . GROUPS_TABLE . " g, " . ACL_GROUPS_TABLE . " ag, " . ACL_OPTIONS_TABLE . " ao 
				WHERE ao.auth_type LIKE 'mod' 
					AND ag.auth_option_id = ao.auth_option_id 
					AND ag.forum_id = $forum_id 
					AND g.group_id = ag.group_id
				ORDER BY g.group_name ASC";
			$result = $db->sql_query($sql);

			$auth_groups = array();
			while ( $row = $db->sql_fetchrow($result) )
			{
				$auth_groups[$row['auth_option']] .= '<option value="' . $row['group_id'] . '">' . $row['group_name'] . '</option>';
			}

?>

<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th>Setting</th>
		<th>Users</th>
		<th>Groups</th>
	</tr>
<?php
			for($i = 0; $i < sizeof($auth_options); $i++)
			{
				$row_class = ( $row_class == 'row1' ) ? 'row2' : 'row1';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $auth_options[$i]['auth_option']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><select name="option[<?php echo $auth_options[$i]['auth_option']; ?>]" multiple="multiple"><?php echo $auth_users[$auth_options[$i]['auth_option']]; ?></select></td>
		<td class="<?php echo $row_class; ?>" align="center"><select name="option[<?php echo $auth_options[$i]['auth_option']; ?>]" multiple="multiple"><?php echo $auth_groups[$auth_options[$i]['auth_option']]; ?></select></td>
	</tr>

<?php
			}

?>
</table>

<?php
			break;

		case 'administrators':

			$where_user_sql = '';
			if ( !empty($HTTP_POST_VARS['users']) )
			{
				if ( is_array($HTTP_POST_VARS['users']) )
				{
					foreach ($HTTP_POST_VARS['users'] as $user_id)
					{
						$where_user_sql .= ( ( $where_user_sql != '' ) ? ', ' : '' ) . intval($user_id);
					}
				}
				else
				{
					$where_user_sql = intval($HTTP_POST_VARS['users']);
				}

				$where_user_sql = " AND u.user_id IN ($where_user_sql)";
			}

			$discrete_user_sql = ( empty($HTTP_POST_VARS['discrete']) || empty($HTTP_POST_VARS['users']) || is_array($HTTP_POST_VARS['users']) ) ? ' DISTINCT ' : 'ao.auth_option, ';

			$where_groups_sql = '';
			if ( !empty($HTTP_POST_VARS['groups']) )
			{
				if ( is_array($HTTP_POST_VARS['groups']) )
				{
					foreach ($HTTP_POST_VARS['groups'] as $group_id)
					{
						$where_groups_sql .= ( ( $where_groups_sql != '' ) ? ', ' : '' ) . intval($group_idf);
					}
				}
				else
				{
					$where_groups_sql = intval($HTTP_POST_VARS['groups']);
				}

				$where_groups_sql = " AND g.group_id IN ($where_groups_sql)";
			}

?>

<h1><?php echo $lang['Users']; ?></h1>

<form method="post" name="adminusers" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table width="45%" class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
<?php

			if ( empty($HTTP_POST_VARS['discrete']) || empty($HTTP_POST_VARS['users']) )
			{

				$sql = "SELECT DISTINCT u.user_id, u.username  
					FROM " . USERS_TABLE . " u, " . ACL_USERS_TABLE . " au, " . ACL_OPTIONS_TABLE . " ao 
					WHERE ao.auth_type LIKE 'admin' 
						AND au.auth_option_id = ao.auth_option_id 
						AND u.user_id = au.user_id
					ORDER BY u.username, u.user_regdate ASC";
				$result = $db->sql_query($sql);

				$users = '';
				while ( $row = $db->sql_fetchrow($result) )
				{
					$users .= '<option value="' . $row['user_id'] . '">' . $row['username'] . '</option>';
				}

?>
	<tr>
		<th><?php echo $lang['Manage_users']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center"><select style="width:280px" name="users[]" multiple="multiple" size="5"><?php echo $users; ?></select></td>
	</tr>
	<tr>
		<td class="cat" align="center"><input class="liteoption" type="submit" name="deluser" value="<?php echo $lang['Remove_selected']; ?>" /> &nbsp; <input class="liteoption" type="submit" name="discrete" value="<?php echo $lang['Advanced']; ?>" /></td>
	</tr>
<?php

			}
			else
			{
				$sql = "SELECT auth_option 
					FROM " . ACL_OPTIONS_TABLE . " 
					WHERE auth_type LIKE 'admin'";
				$result = $db->sql_query($sql);

				$auth_options = array();
				while ( $row = $db->sql_fetchrow($result) ) 
				{
					$auth_options[] = $row;
				}

				$sql = "SELECT u.user_id, u.username, ao.auth_option, au.auth_allow_deny 
					FROM " . USERS_TABLE . " u, " . ACL_USERS_TABLE . " au, " . ACL_OPTIONS_TABLE . " ao 
					WHERE ao.auth_type LIKE 'admin' 
						AND au.auth_option_id = ao.auth_option_id 
						AND u.user_id = au.user_id
						$where_user_sql 
					ORDER BY u.username, u.user_regdate ASC";
				$result = $db->sql_query($sql);

				$users = array();
				$auth_user = array();
				while ( $row = $db->sql_fetchrow($result) )
				{
					$users[] = '<option value="' . $row['user_id'] . '">' . $row['username'] . '</option>';

					$auth_user[$row['auth_option']] = ( isset($auth_user[$row['auth_option']]) ) ?  min($auth_user[$row['auth_option']], $row['auth_allow_deny']) : $row['auth_allow_deny'];
				}

				$users = implode('', array_unique($users));

?>
	<tr>
		<th>&nbsp;<?php echo $lang['User_can_admin']; ?>&nbsp;</th>
		<th>&nbsp;<?php echo $lang['Allow']; ?>&nbsp;</th>
		<th>&nbsp;<?php echo $lang['Deny']; ?>&nbsp;</th>
	</tr>
<?php

				for($i = 0; $i < sizeof($auth_options); $i++)
				{
					$row_class = ( $row_class == 'row1' ) ? 'row2' : 'row1';

					$l_can_cell = ( !empty($lang['acl_admin_' . $auth_options[$i]['auth_option']]) ) ? $lang['acl_admin_' . $auth_options[$i]['auth_option']] : $auth_options[$i]['auth_option'];

					$can_type = ( !empty($auth_user[$auth_options[$i]['auth_option']]) ) ? ' checked="checked"' : '';
					$cannot_type = ( empty($auth_user[$auth_options[$i]['auth_option']]) ) ? ' checked="checked"' : '';

?>
	<tr>
		<td class="<?php echo $row_class; ?>"><?php echo $l_can_cell; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><input type="radio" name="<?php echo $auth_options[$i]['auth_option']; ?>" value="1"<?php echo $can_type; ?> /></td>
		<td class="<?php echo $row_class; ?>" align="center"><input type="radio" name="<?php echo $auth_options[$i]['auth_option']; ?>" value="0"<?php echo $cannot_type; ?> /></td>
	</tr>
<?php
				}

?>
	<tr>
		<td class="cat" colspan="3" align="center"><input class="mainoption" type="submit" name="update" value="<?php echo $lang['Update']; ?>" />&nbsp;&nbsp;<input class="liteoption" type="submit" name="cancel" value="<?php echo $lang['Cancel']; ?>" /></td>
	</tr>
<?php
			}

?>
</table></form>

<form method="post" name="addusers" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table width="45%" class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr> 
		<th><?php echo $lang['Add_users']; ?></th>
	</tr>
	<tr> 
		<td class="row1" align="center"><textarea cols="40" rows="3" name="newuser"></textarea></td>
	</tr>
	<tr> 
		<td class="cat" align="center"> <input type="submit" name="adduser" value="<?php echo $lang['Submit']; ?>" class="mainoption" />&nbsp; <input type="reset" value="<?php echo $lang['Reset']; ?>" class="liteoption" />&nbsp; <input type="submit" name="usersubmit" value="<?php echo $lang['Find_username']; ?>" class="liteoption" onClick="window.open('<?php echo "../search.$phpEx$SID"; ?>&amp;mode=searchuser&amp;form=1&amp;field=newuser', '_phpbbsearch', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=650');return false;" /></td>
	</tr>
</table>












<h1><?php echo $lang['Groups']; ?></h1>

<form method="post" name="admingroups" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table width="45%" class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
<?php

			if ( empty($HTTP_POST_VARS['discrete']) || empty($HTTP_POST_VARS['groups']) )
			{

				$sql = "SELECT DISTINCT g.group_id, g.group_name  
					FROM " . GROUPS_TABLE . " g, " . ACL_GROUPS_TABLE . " ag, " . ACL_OPTIONS_TABLE . " ao 
					WHERE ao.auth_type LIKE 'admin' 
						AND ag.auth_option_id = ao.auth_option_id 
						AND g.group_id = ag.group_id 
					ORDER BY g.group_name ASC";
				$result = $db->sql_query($sql);

				$groups = '';
				while ( $row = $db->sql_fetchrow($result) )
				{
					$groups .= '<option value="' . $row['group_id'] . '">' . ( ( $row['group_name'] == 'ADMINISTRATORS' ) ? $lang['Admin_group'] : $row['group_name'] ) . '</option>';
				}

?>
	<tr>
		<th><?php echo $lang['Manage_groups']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center"><select style="width:280px" name="groups[]" multiple="multiple" size="5"><?php echo $groups; ?></select></td>
	</tr>
	<tr>
		<td class="cat" align="center"><input class="liteoption" type="submit" name="delgroup" value="<?php echo $lang['Remove_selected']; ?>" /> &nbsp; <input class="liteoption" type="submit" name="discrete" value="<?php echo $lang['Advanced']; ?>" /></td>
	</tr>
<?php

			}
			else
			{
				$sql = "SELECT auth_option 
					FROM " . ACL_OPTIONS_TABLE . " 
					WHERE auth_type LIKE 'admin'";
				$result = $db->sql_query($sql);

				$auth_options = array();
				while ( $row = $db->sql_fetchrow($result) ) 
				{
					$auth_options[] = $row;
				}

				$sql = "SELECT g.group_id, g.group_name, ao.auth_option, ag.auth_allow_deny   
					FROM " . GROUPS_TABLE . " g, " . ACL_GROUPS_TABLE . " ag, " . ACL_OPTIONS_TABLE . " ao 
					WHERE ao.auth_type LIKE 'admin' 
						AND ag.auth_option_id = ao.auth_option_id 
						AND g.group_id = ag.group_id 
						$where_groups_sql 
					ORDER BY g.group_name ASC";
				$result = $db->sql_query($sql);

				$groups = array();
				$auth_group = array();
				while ( $row = $db->sql_fetchrow($result) )
				{
					$groups[] = '<option value="' . $row['group_id'] . '">' . ( ( $row['group_name'] == 'ADMINISTRATORS' ) ? $lang['Admin_group'] : $row['group_name'] ) . '</option>';

					$auth_group[$row['auth_option']] = ( isset($auth_group[$row['auth_option']]) ) ?  min($auth_group[$row['auth_option']], $row['auth_allow_deny']) : $row['auth_allow_deny'];
				}

				$groups = implode('', array_unique($groups));

?>
	<tr>
		<th>&nbsp;<?php echo $lang['Group_can_admin']; ?>&nbsp;</th>
		<th>&nbsp;<?php echo $lang['Allow']; ?>&nbsp;</th>
		<th>&nbsp;<?php echo $lang['Deny']; ?>&nbsp;</th>
	</tr>
<?php

				for($i = 0; $i < sizeof($auth_options); $i++)
				{
					$row_class = ( $row_class == 'row1' ) ? 'row2' : 'row1';

					$l_can_cell = ( !empty($lang['acl_admin_' . $auth_options[$i]['auth_option']]) ) ? $lang['acl_admin_' . $auth_options[$i]['auth_option']] : $auth_options[$i]['auth_option'];

					$can_type = ( !empty($auth_group[$auth_options[$i]['auth_option']]) ) ? ' checked="checked"' : '';
					$cannot_type = ( empty($auth_group[$auth_options[$i]['auth_option']]) ) ? ' checked="checked"' : '';

?>
	<tr>
		<td class="<?php echo $row_class; ?>"><?php echo $l_can_cell; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><input type="radio" name="<?php echo $auth_options[$i]['auth_option']; ?>" value="1"<?php echo $can_type; ?> /></td>
		<td class="<?php echo $row_class; ?>" align="center"><input type="radio" name="<?php echo $auth_options[$i]['auth_option']; ?>" value="0"<?php echo $cannot_type; ?> /></td>
	</tr>
<?php
				}

?>
	<tr>
		<td class="cat" colspan="3" align="center"><input class="mainoption" type="submit" name="update" value="<?php echo $lang['Update']; ?>" />&nbsp;&nbsp;<input class="liteoption" type="submit" name="cancel" value="<?php echo $lang['Cancel']; ?>" /></td>
	</tr>
<?php
			}

?>
</table></form>

<form method="post" name="addgroups" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table width="45%" class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr> 
		<th><?php echo $lang['Add_groups']; ?></th>
	</tr>
	<tr> 
		<td class="row1" align="center"><textarea cols="40" rows="3" name="newuser"></textarea></td>
	</tr>
	<tr> 
		<td class="cat" align="center"> <input type="submit" name="addgroup" value="<?php echo $lang['Submit']; ?>" class="mainoption" />&nbsp; <input type="reset" value="<?php echo $lang['Reset']; ?>" class="liteoption" /></td>
	</tr>
</table>

<?php 
			break;
	}


?>

<?php echo $s_hidden_fields; ?></form>

<?php

}
else
{
	$sql = "SELECT forum_id, forum_name 
		FROM " . FORUMS_TABLE . "  
		ORDER BY cat_id ASC, forum_order ASC";
	$result = $db->sql_query($sql);

	$select_list = '';
	while ( $row = $db->sql_fetchrow($result) )
	{
		$select_list .= '<option value="' . $row['forum_id'] . '">' . $row['forum_name'] . '</option>';
	}
	$db->sql_freeresult($result);

	page_header($lang['Forums']);

?>

<h1><?php echo $l_title; ?></h1>

<p><?php echo $l_title_explain ?></p>

<form method="post" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th align="center"><?php echo $lang['Select_a_Forum']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center">&nbsp;<select name="f"><?php echo $select_list; ?></select> &nbsp;<input type="submit" value="<?php echo $lang['Look_up_Forum']; ?>" class="mainoption" />&nbsp;</td>
	</tr>
</table></form>

<?php

}

page_footer();

?>