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
				$cell_bg = ( $cell_bg == 'row1' ) ? 'row2' : 'row1';

?>
	<tr>
		<td class="<?php echo $cell_bg; ?>" align="center"><?php echo $auth_options[$i]['auth_option']; ?></td>
		<td class="<?php echo $cell_bg; ?>" align="center"><select name="option[<?php echo $auth_options[$i]['auth_option']; ?>]" multiple="multiple"><?php echo $auth_users[$auth_options[$i]['auth_option']]; ?></select></td>
		<td class="<?php echo $cell_bg; ?>" align="center"><select name="option[<?php echo $auth_options[$i]['auth_option']; ?>]" multiple="multiple"><?php echo $auth_groups[$auth_options[$i]['auth_option']]; ?></select></td>
	</tr>

<?php
			}

?>
</table>

<?php
			break;

		case 'administrators':
			$sql = "SELECT auth_option 
				FROM " . ACL_OPTIONS_TABLE . " 
				WHERE auth_type LIKE 'admin'";
			$result = $db->sql_query($sql);

			$auth_options = array();
			while ( $row = $db->sql_fetchrow($result) ) 
			{
				$auth_options[] = $row;
			}

			$sql = "SELECT u.user_id, u.username, ao.auth_option 
				FROM " . USERS_TABLE . " u, " . ACL_USERS_TABLE . " au, " . ACL_OPTIONS_TABLE . " ao 
				WHERE ao.auth_type LIKE 'admin' 
					AND au.auth_option_id = ao.auth_option_id 
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
				WHERE ao.auth_type LIKE 'admin' 
					AND ag.auth_option_id = ao.auth_option_id 
					AND g.group_id = ag.group_id
				ORDER BY g.group_name ASC";
			$result = $db->sql_query($sql);

			$auth_groups = array();
			while ( $row = $db->sql_fetchrow($result) )
			{
				$auth_groups[$row['auth_option']] .= '<option value="' . $row['group_id'] . '">' . ( ( $row['group_name'] == 'ADMINISTRATORS' ) ? $lang['Admin_group'] : $row['group_name'] ) . '</option>';
			}

?>

<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th>Setting</th>
		<th>Users</th>
	</tr>
<?php
			for($i = 0; $i < sizeof($auth_options); $i++)
			{
				$cell_bg = ( $cell_bg == 'row1' ) ? 'row2' : 'row1';

				$l_can_cell = ( !empty($lang['acl_admin_' . $auth_options[$i]['auth_option']]) ) ? $lang['acl_admin_' . $auth_options[$i]['auth_option']] : $auth_options[$i]['auth_option'];

?>
	<tr>
		<td class="<?php echo $cell_bg; ?>"><?php echo $l_can_cell; ?></td>
		<td class="<?php echo $cell_bg; ?>" align="center"><?php if ( !empty($auth_users[$auth_options[$i]['auth_option']]) ) { ?><select name="user_option[<?php echo $auth_options[$i]['auth_option']; ?>]" multiple="multiple"><?php echo $auth_users[$auth_options[$i]['auth_option']]; ?></select><?php } else { ?>&nbsp;<?php } ?></td>
	</tr>
<?php
			}

?>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="liteoption" type="submit" name="adduser" value="Add New User" /> &nbsp; <input class="liteoption" type="submit" name="deluser" value="Remove User" /></td>
	</tr>
</table>

<br clear="all" />

<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th>Setting</th>
		<th>Groups</th>
	</tr>
<?php
			for($i = 0; $i < sizeof($auth_options); $i++)
			{
				$cell_bg = ( $cell_bg == 'row1' ) ? 'row2' : 'row1';

				$l_can_cell = ( !empty($lang['acl_admin_' . $auth_options[$i]['auth_option']]) ) ? $lang['acl_admin_' . $auth_options[$i]['auth_option']] : $auth_options[$i]['auth_option'];

?>
	<tr>
		<td class="<?php echo $cell_bg; ?>"><?php echo $l_can_cell; ?></td>
		<td class="<?php echo $cell_bg; ?>" align="center"><?php if ( !empty($auth_groups[$auth_options[$i]['auth_option']]) ) { ?><select name="group_option[<?php echo $auth_options[$i]['auth_option']; ?>]"><?php echo $auth_groups[$auth_options[$i]['auth_option']]; ?></select><?php } else { ?>&nbsp;<?php } ?></td>
	</tr>
<?php
			}

?>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="liteoption" type="submit" name="adduser" value="Add New Group" /> &nbsp; <input class="liteoption" type="submit" name="deluser" value="Remove Group" /></td>
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