<?php
/***************************************************************************
 *                           admin_prune_users.php
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
	if ( !$acl->get_acl_admin('user') )
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['Users']['Prune_users'] = $filename . $SID;

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
if ( !$acl->get_acl_admin('user') )
{
	return;
}

//
// Set mode
//
if( isset( $HTTP_POST_VARS['mode'] ) || isset( $HTTP_GET_VARS['mode'] ) )
{
	$mode = ( isset( $HTTP_POST_VARS['mode']) ) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
}
else
{
	$mode = '';
}

//
//
//
if ( isset($HTTP_POST_VARS['prune']) )
{
	if ( empty($HTTP_POST_VARS['confirm']) )
	{
		$values = array('prune', 'deactivate', 'delete', 'users', 'username', 'email', 'joined_select', 'active_select', 'count_select', 'joined', 'active', 'count', 'deleteposts');

		$l_message = '<form method="post" action="admin_prune_users.' . $phpEx . $SID . '">' . $lang['Confirm_prune_users'] . '<br /><br /><input class="liteoption" type="submit" name="confirm" value="' . $lang['Yes'] . '" />&nbsp;&nbsp;<input class="liteoption" type="submit" name="cancel" value="' . $lang['No'] . '" />';

		foreach ( $values as $field )
		{
			$l_message .= ( !empty($HTTP_POST_VARS[$field]) ) ? '<input type="hidden" name="' . $field . '" value="' . urlencode($HTTP_POST_VARS[$field]) . '" />' : '';
		}
		
		$l_message .= '</form>';

		page_header($lang['Prune_users']);

?>

<h1><?php echo $lang['Prune_users']; ?></h1>

<p><?php echo $lang['Prune_users_explain']; ?></p>

<?php

		page_message($lang['Confirm'], $l_message, false);
		page_footer();
		
	}
	else if ( isset($HTTP_POST_VARS['confirm']) )
	{
		if ( !empty($HTTP_POST_VARS['users']) )
		{
			$users = explode("\n", urldecode($HTTP_POST_VARS['users']));

			$where_sql = '';
			foreach ( $users as $username )
			{
				$where_sql .= ( ( $where_sql != '' ) ? ', ' : '' ) . '\'' . trim($username) . '\'';
			}
			$where_sql = " AND username IN ($where_sql)";
		}
		else
		{
			$username = ( !empty($HTTP_POST_VARS['username']) ) ? urldecode($HTTP_POST_VARS['username']) : '';
			$email = ( !empty($HTTP_POST_VARS['email']) ) ? urldecode($HTTP_POST_VARS['email']) : '';

			$joined_select = ( !empty($HTTP_POST_VARS['joined_select']) ) ? $HTTP_POST_VARS['joined_select'] : 'lt';
			$active_select = ( !empty($HTTP_POST_VARS['active_select']) ) ? $HTTP_POST_VARS['active_select'] :'lt';
			$count_select = ( !empty($HTTP_POST_VARS['count_select']) ) ? $HTTP_POST_VARS['count_select'] : 'eq';
			$joined = ( !empty($HTTP_POST_VARS['joined']) ) ? explode('-', $HTTP_POST_VARS['joined']) : array();
			$active = ( !empty($HTTP_POST_VARS['active']) ) ? explode('-', $HTTP_POST_VARS['active']) :array();
			$count = ( !empty($HTTP_POST_VARS['count']) ) ? intval($HTTP_POST_VARS['count']) : '';

			$key_match = array('lt' => '<', 'gt' => '>', 'eq' => '=');
			$sort_by_types = array('username', 'user_email', 'user_posts', 'user_regdate', 'user_lastvisit');

			$where_sql = '';
			$where_sql .= ( $username ) ? " AND username LIKE '" . str_replace('*', '%', $username) ."'" : '';
			$where_sql .= ( $email ) ? " AND user_email LIKE '" . str_replace('*', '%', $email) ."' " : '';
			$where_sql .= ( $joined ) ? " AND user_regdate " . $key_match[$joined_select] . " " . gmmktime(0, 0, 0, intval($joined[1]), intval($joined[2]), intval($joined[0])) : '';
			$where_sql .= ( $count ) ? " AND user_posts " . $key_match[$count_select] . " $count " : '';
			$where_sql .= ( $active ) ? " AND user_lastvisit " . $key_match[$active_select] . " " . gmmktime(0, 0, 0, $active[1], intval($active[2]), intval($active[0])) : '';
		}

		$sql = "SELECT username, user_id FROM " . USERS_TABLE . " 
			WHERE user_id <> " . ANONYMOUS . " 
			$where_sql";
		$result = $db->sql_query($sql);

		$where_sql = '';
		$user_ids = array();
		$usernames = array();
		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				$where_sql .= ( ( $where_sql != '' ) ? ', ' : '' ) . $row['user_id'];
				$user_ids[] = $row['user_id'];
				$usernames[] = $row['username'];
			}
			while ( $row = $db->sql_fetchrow($result) );

			$where_sql = " AND user_id IN ($where_sql)";
		}
		$db->sql_freeresult($result);

		if ( $where_sql != '' )
		{
			$sql = '';
			if ( !empty($HTTP_POST_VARS['delete']) )
			{
				if ( !empty($HTTP_POST_VARS['deleteposts']) )
				{
					$l_admin_log = 'log_prune_user_del_del';

					//
					// Call unified post deletion routine?
					//
				}
				else
				{
					$l_admin_log = 'log_prune_user_del_anon';

					for($i = 0; $i < sizeof($user_ids); $i++)
					{
						$sql = "UPDATE " . POSTS_TABLE . " 
							SET poster_id = " . ANONYMOUS . ", post_username = '" . $usernames[$i] . "' 
							WHERE user_id = " . $userids[$i];
//						$db->sql_query($sql);
					}
				}

				$sql = "DELETE FROM " . USERS_TABLE;
			}
			else if ( !empty($HTTP_POST_VARS['deactivate']) )
			{
				$l_admin_log = 'log_prune_user_deac';

				$sql = "UPDATE " . USERS_TABLE . " SET user_active = 0";
			}
			$sql .= " WHERE user_id <> " . ANONYMOUS . " 
				$where_sql";
//			$db->sql_query($sql);

			add_admin_log($l_admin_log, implode(', ', $usernames));

			unset($user_ids);
			unset($usernames);
		}

		message_die(MESSAGE, $lang['Success_user_prune']);
	}
}

//
//
//
$find_count = array('lt' => $lang['Less_than'], 'eq' => $lang['Equal_to'], 'gt' => $lang['More_than']);
$s_find_count = '';
foreach ( $find_count as $key => $value )
{
	$selected = ( $key == 'eq' ) ? ' selected="selected"' : '';
	$s_find_count .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
}

$find_time = array('lt' => $lang['Before'], 'gt' => $lang['After']);
$s_find_join_time = '';
foreach ( $find_time as $key => $value )
{
	$s_find_join_time .= '<option value="' . $key . '">' . $value . '</option>';
}
$s_find_active_time = '';
foreach ( $find_time as $key => $value )
{
	$s_find_active_time .= '<option value="' . $key . '">' . $value . '</option>';
}

//
//
//
page_header($lang['Prune_users']);

?>

<h1><?php echo $lang['Prune_users']; ?></h1>

<p><?php echo $lang['Prune_users_explain']; ?></p>

<form method="post" name="post" action="<?php echo "admin_prune_users.$phpEx$SID"; ?>"><table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr> 
		<th colspan="2"><?php echo $lang['Prune_users']; ?></th>
	</tr>
	<tr> 
		<td class="row1"><?php echo $lang['Username']; ?>: </td>
		<td class="row2"><input class="post" type="text" name="username" /></td>
	</tr>
	<tr> 
		<td class="row1"><?php echo $lang['Email']; ?>: </td>
		<td class="row2"><input class="post" type="text" name="email" /></td>
	</tr>
	<tr> 
		<td class="row1"><?php echo $lang['Joined']; ?>: <br /><span class="gensmall"><?php echo $lang['Joined_explain']; ?></span></td>
		<td class="row2"><select name="joined_select"><?php echo $s_find_join_time; ?></select> <input class="post" type="text" name="joined" maxlength="10" size="10" /></td>
	</tr>
	<tr> 
		<td class="row1"><?php echo $lang['Last_active']; ?>: <br /><span class="gensmall"><?php echo $lang['Last_active_explain']; ?></span></td>
		<td class="row2"><select name="active_select"><?php echo $s_find_active_time; ?></select> <input class="post" type="text" name="active" maxlength="10" size="10" /></td>
	</tr>
	<tr> 
		<td class="row1"><?php echo $lang['Posts']; ?>: </td>
		<td class="row2"><select name="count_select"><?php echo $s_find_count; ?></select> <input class="post" type="text" name="count" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Prune_users']; ?>: <br /><span class="gensmall"><?php echo $lang['Select_users_explain']; ?></span></td>
		<td class="row2"><textarea name="users" cols="40" rows="5"></textarea></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Delete_user_posts']; ?>: <br /><span class="gensmall"><?php echo $lang['Delete_user_posts_explain']; ?></span></td>
		<td class="row2"><input type="radio" name="deleteposts" value="1" /> <?php echo $lang['Yes']; ?> &nbsp;&nbsp; <input type="radio" name="deleteposts" value="0" checked="checked" /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="liteoption" type="submit" name="delete" value="<?php echo $lang['Prune_users']; ?>" />&nbsp;&nbsp;<input class="liteoption" type="submit" name="deactivate" value="<?php echo $lang['Deactivate']; ?>" />&nbsp;&nbsp;<input type="submit" name="usersubmit" value="<?php echo $lang['Find_username']; ?>" class="liteoption" onClick="window.open('<?php echo "../search.$phpEx$SID&amp;mode=searchuser&amp;field=users"; ?>', '_phpbbsearch', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=650');return false;" /><input type="hidden" name="prune" value="1" /></td>
	</tr>
</table></form>

<?php

page_footer();

?>