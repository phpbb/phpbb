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

if (!empty($setmodules))
{
	if (!$auth->acl_get('a_group'))
	{
		return;
	}

	$module['GROUP']['MANAGE'] = basename(__FILE__) . "$SID&amp;mode=manage";
	$module['GROUP']['GROUP_PREFS'] = basename(__FILE__) . "$SID&amp;mode=prefs";

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);

// Do we have general permissions?
if (!$auth->acl_get('a_group') )
{
	trigger_error($user->lang['NO_ADMIN']);
}



// Check and set some common vars
$update		= (isset($_POST['update'])) ? true : false;
$mode		= (isset($_REQUEST['mode'])) ? htmlspecialchars($_REQUEST['mode']) : '';
$group_id	= (isset($_REQUEST['g'])) ? intval($_REQUEST['g']) : '';

if (isset($_POST['addgroup']))
{
	$action = 'addgroup';
}
else if (isset($_POST['delete']))
{
	$action = 'delete';
}
else if (isset($_POST['addleaders']) || isset($_POST['addusers']))
{
	$action = (isset($_POST['addleaders'])) ? 'addleaders' : 'addusers';
}
else
{
	$action = (isset($_REQUEST['action'])) ? htmlspecialchars($_REQUEST['action']) : '';
}

$start		= (isset($_GET['start']) && $action == 'member') ? intval($_GET['start']) : 0;
$start_mod	= (isset($_GET['start']) && $action == 'leader') ? intval($_GET['start']) : 0;
$start_pend = (isset($_GET['start']) && $action == 'pending') ? intval($_GET['start']) : 0;



// Grab basic data for group, if group_id is set since it's used
// in several places below
if ($group_id)
{
	$sql = 'SELECT * 
		FROM ' . GROUPS_TABLE . " 
		WHERE group_id = $group_id";
	$result = $db->sql_query($sql);

	if (!extract($db->sql_fetchrow($result)))
	{
		trigger_error($user->lang['NO_GROUP']);
	}
	$db->sql_freeresult($result);
}



switch ($mode)
{
	case 'manage':
		// Page header
		adm_page_header($user->lang['MANAGE']);

		// Which page?
		switch ($action)
		{
			case 'edit':
			case 'addgroup':

				// Did we submit?
				if ($update)
				{
					if ($group_type != GROUP_SPECIAL)
					{
						$group_name = (!empty($_POST['group_name'])) ? htmlspecialchars($_POST['group_name']) : '';
						$group_type = (!empty($_POST['group_type'])) ? intval($_POST['group_type']) : '';
					}
					$group_description = (!empty($_POST['group_description'])) ? htmlspecialchars($_POST['group_description']) : '';
					$group_colour = (!empty($_POST['group_colour'])) ? htmlspecialchars($_POST['group_colour']) : '';
					$group_rank = (isset($_POST['group_rank'])) ? intval($_POST['group_rank']) : '';
					$group_avatar = (!empty($_POST['group_avatar'])) ? htmlspecialchars($_POST['group_avatar']) : '';

					// Check data
					if ($group_name == '' || strlen($group_name) > 40)
					{
						$error .= (($error != '') ? '<br />' : '') . (($group_name == '') ? $user->lang['GROUP_ERR_USERNAME'] : $user->lang['GROUP_ERR_USER_LONG']);
					}
					if (strlen($group_description) > 255)
					{
						$error .= (($error != '') ? '<br />' : '') . $user->lang['GROUP_ERR_DESC_LONG'];
					}
					if ($group_type < GROUP_OPEN || $group_type > GROUP_FREE)
					{
						$error .= (($error != '') ? '<br />' : '') . $user->lang['GROUP_ERR_TYPE'];
					}
		/*
					// Update DB
					if (!$error)
					{
						// Update group preferences
						$sql = "UPDATE " . GROUPS_TABLE . " 
							SET group_name = '$group_name', group_description = '$group_description', group_type = $group_type, group_rank = $group_rank, group_colour = '$group_colour' 
							WHERE group_id = $group_id";
						$db->sql_query($sql);

						$user_sql = '';
						$user_sql .= (isset($_POST['submit'])) ? ((($user_sql != '') ? ', ' : '') . "user_colour = '$group_colour'") : '';
						$user_sql .= (isset($_POST['submit']) && $group_rank != -1) ? ((($user_sql != '') ? ', ' : '') . "user_rank = $group_rank") : '';

						// Update group members preferences
						switch (SQL_LAYER)
						{
							case 'mysql':
							case 'mysql4':
								// batchwise? 500 at a time or so maybe? try to reduce memory useage
								$more = true;
								$start = 0;
								do
								{
									$sql = 'SELECT user_id
										FROM ' . USER_GROUP_TABLE . " 
										WHERE group_id = $group_id 
										LIMIT $start, 500";
									$result = $db->sql_query($sql);

									if ($row = $db->sql_fetchrow($result))
									{
										$user_count = 0;
										$user_id_sql = '';
										do
										{
											$user_id_sql .= (($user_id_sql != '') ? ', ' : '') . $row['user_id'];
											$user_count++;
										}
										while ($row = $db->sql_fetchrow($result));

										$sql = 'UPDATE ' . USERS_TABLE . " 
											SET $user_sql 
											WHERE user_id IN ($user_id_sql)";
										$db->sql_query($sql);

										if ($user_count == 500)
										{
											$start += 500;
										}
										else
										{
											$more = false;
										}
									}
									else
									{
										$more = false;
									}
									$db->sql_freeresult($result);
									unset($user_id_sql);
								}
								while ($more);

								break;

							default:
								$sql ='"UPDATE ' . USERS_TABLE . " 
									SET $user_sql 
									WHERE user_id IN (
										SELECT user_id
											FROM " . USER_GROUP_TABLE . " 
											WHERE group_id = $group_id)";
								$db->sql_query($sql);
						}

						trigger_error($user->lang['GROUP_UPDATED']);
					}*/
				}


?>

<h1><?php echo $user->lang['MANAGE'] . ' : <i>' . $group_name . '</i>'; ?></h1>

<p><?php echo $user->lang['GROUP_EDIT_EXPLAIN']; ?></p>

<?php 

				$sql = 'SELECT * 
					FROM ' . RANKS_TABLE . '
					WHERE rank_special = 1
					ORDER BY rank_title';
				$result = $db->sql_query($sql);

				$rank_options = '<option value="-1"' . ((empty($group_rank)) ? 'selected="selected" ' : '') . '>' . $user->lang['USER_DEFAULT'] . '</option>';
				if ($row = $db->sql_fetchrow($result))
				{
					do
					{
						$selected = (!empty($group_rank) && $row['rank_id'] == $group_rank) ? ' selected="selected"' : '';
						$rank_options .= '<option value="' . $row['rank_id'] . '"' . $selected . '>' . $row['rank_title'] . '</option>';
					}
					while ($row = $db->sql_fetchrow($result));
				}
				$db->sql_freeresult($result);

				$type_open		= ($group_type == GROUP_OPEN) ? ' checked="checked"' : '';
				$type_closed	= ($group_type == GROUP_CLOSED) ? ' checked="checked"' : '';
				$type_hidden	= ($group_type == GROUP_HIDDEN) ? ' checked="checked"' : '';
				$type_free		= ($group_type == GROUP_FREE) ? ' checked="checked"' : '';

?>

<script language="javascript" type="text/javascript">
<!--

function swatch()
{
	window.open('./swatch.<?php echo $phpEx; ?>?form=settings&amp;name=group_colour', '_swatch', 'HEIGHT=115,resizable=yes,scrollbars=no,WIDTH=636');
	return false;
}

//-->
</script>

<form name="settings" method="post" action="admin_groups.<?php echo "$phpEx$SID&amp;action=$action&amp;g=$group_id"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['GROUP_DETAILS']; ?></th>
	</tr>
<?php

				if (sizeof($error))
				{

?>
	<tr>
		<td class="row1" colspan="2" align="center"><span style="color:red"><?php echo implode('<br />', $error); ?></span></td>
	</tr>
<?php

				}

?>
	<tr>
		<td class="row2" width="40%"><b><?php echo $user->lang['GROUP_NAME']; ?>:</b></td>
		<td class="row1"><?php 
	
				if ($group_type != GROUP_SPECIAL)
				{
		
?><input class="post" type="text" name="group_name" value="<?php echo (!empty($group_name)) ? $group_name : ''; ?>" size="40" maxlength="40" /><?php
			
				}
				else
				{
				
?><b><?php echo (!empty($user->lang['G_' . $group_name])) ? $user->lang['G_' . $group_name] : $group_name; ?></b><?php
	
				}
	
?></td>
	</tr>
	<tr>
		<td class="row2"><b><?php echo $user->lang['GROUP_DESC']; ?>:</b></td>
		<td class="row1"><input class="post" type="text" name="group_description" value="<?php echo (!empty($group_description)) ? $group_description : ''; ?>" size="40" maxlength="255" /></td>
	</tr>
<?php

				if ($group_type != GROUP_SPECIAL)
				{

?>
	<tr>
		<td class="row2"><b><?php echo $user->lang['GROUP_TYPE']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['GROUP_TYPE_EXPLAIN']; ?></span></td>
		<td class="row1" nowrap="nowrap"><input type="radio" name="group_type" value="<?php echo GROUP_FREE . '"' . $type_free; ?> /> <?php echo $user->lang['GROUP_OPEN']; ?> &nbsp; <input type="radio" name="group_type" value="<?php echo GROUP_OPEN . '"' . $type_open; ?> /> <?php echo $user->lang['GROUP_REQUEST']; ?> &nbsp; <input type="radio" name="group_type" value="<?php echo GROUP_CLOSED . '"' . $type_closed; ?> /> <?php echo $user->lang['GROUP_CLOSED']; ?> &nbsp; <input type="radio" name="group_type" value="<?php echo GROUP_HIDDEN . '"' . $type_hidden; ?> /> <?php echo $user->lang['GROUP_HIDDEN']; ?></td>
	</tr>
<?php

				}

?>
	<tr>
		<th colspan="2"><?php echo $user->lang['GROUP_SETTINGS_SAVE']; ?></th>
	</tr>
	<tr>
		<td class="row2"><b><?php echo $user->lang['GROUP_COLOR']; ?>:</b><br /><span class="gensmall"><?php echo sprintf($user->lang['GROUP_COLOR_EXPLAIN'], '<a href="swatch.' . $phpEx . '" onclick="swatch();return false" target="_swatch">', '</a>'); ?></span></td>
		<td class="row1" nowrap="nowrap"><input class="post" type="text" name="group_colour" value="<?php echo (!empty($group_colour)) ? $group_colour : ''; ?>" size="6" maxlength="6" /></td>
	</tr>
	<tr>
		<td class="row2"><b><?php echo $user->lang['GROUP_RANK']; ?>:</b></td>
		<td class="row1"><select name="group_rank"><?php echo $rank_options; ?></select></td>
	</tr>
	<!-- tr>
		<td class="row2"><b><?php echo $user->lang['GROUP_AVATAR']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['GROUP_AVATAR_EXPLAIN']; ?></span></td>
		<td class="row1">&nbsp;</td>
	</tr -->
	<tr>
		<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" /> &nbsp; <input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
	</tr>
</table></form>
<?php

				break;


			case 'addleaders':
			case 'addusers':
				// TODO : 
				// Need to add users to user_group table when adding as moderator (if applicable)

				$username_ary = (!empty($_POST['usernames'])) ? array_unique(explode("\n", $_POST['usernames'])) : '';
				if (!$username_ary)
				{
					trigger_error($user->lang['NO_USERS']);
				}

				$sql_where = array();
				foreach ($username_ary as $username)
				{
					if ($username = trim($username))
					{
						$sql_where[] = "'$username'";
					}
				}
				unset($username_ary);

				// Grab the user ids
				$sql = 'SELECT user_id 
					FROM ' . USERS_TABLE . ' 
					WHERE username IN (' . implode(', ', $sql_where) . ')';
				$result = $db->sql_query($sql);

				if (!($row = $db->sql_fetchrow($result)))
				{
					trigger_error($user->lang['NO_USERS']);
				}

				$id_ary = array();
				do
				{
					$id_ary[] = $row['user_id'];
				}
				while ($row = $db->sql_fetchrow($result));
				$db->sql_freeresult($result);

				// Remove users who are already members of this group
				$sql = 'SELECT user_id, group_leader  
					FROM ' . USER_GROUP_TABLE . '   
					WHERE user_id IN (' . implode(', ', $id_ary) . ") 
						AND group_id = $group_id";
				$result = $db->sql_query($sql);

				$add_id_ary = $update_id_ary = array();
				if ($row = $db->sql_fetchrow($result))
				{
					do
					{
						$add_id_ary[] = $row['user_id'];

						if ($action == 'addleaders' && !$row['group_leader'])
						{
							$update_id_ary[] = $row['user_id'];
						}
					}
					while ($row = $db->sql_fetchrow($result));
				}
				$db->sql_freeresult($result);

				// Do all the users exist in this group?
				$add_id_ary = array_diff($id_ary, $add_id_ary);
				unset($id_ary);

				// If we have no users 
				if (!sizeof($add_id_ary) && !sizeof($update_id_ary))
				{
					trigger_error($user->lang['GROUP_USERS_EXIST']);
				}

				if (sizeof($add_id_ary))
				{
					$group_leader = ($action == 'addleaders') ? 1  : 0;

					// Insert the new users 
					switch (SQL_LAYER)
					{
						case 'mysql':
						case 'mysql4':
							$sql = 'INSERT INTO ' . USER_GROUP_TABLE . " (user_id, group_id, group_leader) 
								VALUES " . implode(', ', preg_replace('#^([0-9]+)$#', "(\\1, $group_id, $group_leader)",  $add_id_ary));
							$db->sql_query($sql);
							break;

						case 'mssql':
						case 'sqlite':
							$sql = 'INSERT INTO ' . USER_GROUP_TABLE . " (user_id, group_id, group_leader) 
								" . implode(' UNION ALL ', preg_replace('#^([0-9]+)$#', "(\\1, $group_id, $group_leader)",  $add_id_ary));
							$db->sql_query($sql);
							break;

						default:
							foreach ($add_id_ary as $user_id)
							{
								$sql = 'INSERT INTO ' . USER_GROUP_TABLE . " (user_id, group_id, group_leader)
									VALUES ($user_id, $group_id, $group_leader)";
								$db->sql_query($sql);
							}
							break;
					}

					$sql = 'UPDATE ' . USERS_TABLE . " 
						SET user_permissions = '' 
						WHERE user_id IN (" . implode(', ', $add_id_ary) . ')';
					$db->sql_query($sql);
				}

				if (sizeof($update_id_ary))
				{
					$sql = 'UPDATE ' . USER_GROUP_TABLE . ' 
						SET group_leader = 1 
						WHERE user_id IN (' . implode(', ', $update_id_ary) . ")
							AND group_id = $group_id";
					$db->sql_query($sql);
				}


				// Update user settings (color, rank) if applicable
				if (!empty($_POST['default']))
				{
					$sql = 'UPDATE ' . USERS_TABLE . " 
						SET group_id = $group_id, user_colour = '$group_colour', user_rank = " . intval($group_rank) . "  
						WHERE user_id IN (" . implode(', ', $add_id_ary) . ")";
					$db->sql_query($sql);
				}

//				add_log();

				$message = ($mode == 'addleaders') ? 'GROUP_MODS_ADDED' : 'GROUP_USERS_ADDED';
				trigger_error($user->lang[$message]);
			
			
			
				break;




			// Show list of leaders, existing and pending members
			case 'list':

?>

<h1><?php echo $user->lang['GROUP_MEMBERS']; ?></h1>

<p><?php echo $user->lang['GROUP_MEMBERS_EXPLAIN']; ?></p>

<?php

				// Total number of group leaders
				$sql = 'SELECT COUNT(user_id) AS total_leaders 
					FROM ' . USER_GROUP_TABLE . " 
					WHERE group_id = $group_id 
						AND group_leader = 1";
				$result = $db->sql_query($sql);

				$total_leaders = ($row = $db->sql_fetchrow($result)) ? $row['total_leaders'] : 0;
				$db->sql_freeresult($result);

				// Total number of group members (non-leaders)
				$sql = 'SELECT COUNT(user_id) AS total_members 
					FROM ' . USER_GROUP_TABLE . " 
					WHERE group_id = $group_id 
						AND group_leader <> 1";
				$result = $db->sql_query($sql);

				$total_members = ($row = $db->sql_fetchrow($result)) ? $row['total_members'] : 0;
				$db->sql_freeresult($result);

				// Grab the members
				$sql = 'SELECT u.user_id, u.username, u.user_regdate, u.user_posts, u.group_id, ug.group_leader, ug.user_pending 
					FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . " ug 
					WHERE ug.group_id = $group_id 
						AND u.user_id = ug.user_id 
					ORDER BY ug.group_leader DESC, ug.user_pending DESC, u.username 
					LIMIT $start, " . $config['topics_per_page'];
				$result = $db->sql_query($sql);

				$leader = $member = 0;
				$group_data = array();
				if ($row = $db->sql_fetchrow($result))
				{
					do
					{
						$type = ($row['group_leader']) ? 'leader' : 'member';

						$group_data[$type][$$type]['user_id'] = $row['user_id'];
						$group_data[$type][$$type]['group_id'] = $row['group_id'];
						$group_data[$type][$$type]['username'] = $row['username'];
						$group_data[$type][$$type]['user_regdate'] = $row['user_regdate'];
						$group_data[$type][$$type]['user_posts'] = $row['user_posts'];
						$group_data[$type][$$type]['user_pending'] = $row['user_pending'];

						$$type++;
					}
					while ($row = $db->sql_fetchrow($result));
				}
				$db->sql_freeresult($result);

				if ($group_type != GROUP_SPECIAL)
				{

?>

<h1><?php echo $user->lang['GROUP_MODS']; ?></h1>

<p><?php echo $user->lang['GROUP_MODS_EXPLAIN']; ?></p>

<form name="mod" method="post" action="<?php echo "admin_groups.$phpEx$SID&amp;mode=$mode&amp;g=$group_id"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th width="55%"><?php echo $user->lang['USERNAME']; ?></th>
		<th width="3%" nowrap="nowrap">Default</th>
		<th width="20%"><?php echo $user->lang['JOINED']; ?></th>
		<th width="20%"><?php echo $user->lang['POSTS']; ?></th>
		<th width="2%"><?php echo $user->lang['MARK']; ?></th>
	</tr>
<?php

					if (sizeof($group_data['leader']))
					{
						foreach ($group_data['leader'] as $row)
						{
							$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
	<tr>
		<td class="<?php echo $row_class; ?>"><a href="../ucp.<?php echo "$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id']; ?>" target="_profile"><?php echo $row['username']; ?></a></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo ($row['group_id'] == $group_id) ? $user->lang['YES'] : $user->lang['NO']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $user->format_date($row['user_regdate'], $user->lang['DATE_FORMAT']); ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['user_posts']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><input type="checkbox" name="mark[<?php echo $row['user_id']; ?>]" /></td>
	</tr>
<?php	

						}
					}
					else
					{

?>
	<tr>
		<td class="row1" colspan="5" align="center"><?php echo $user->lang['GROUPS_NO_MODS']; ?></td>
	</tr>
<?php

					}

?>

	<tr>
		<td class="cat" colspan="5"><table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td><input class="btnlite" type="submit" name="displayadd" value="<?php echo $user->lang['ADD_USERS']; ?>" onclick="showbox('addleaders');return false" /></td>
				<td align="right"><input class="btnlite" type="submit" name="delete" value="<?php echo $user->lang['DELETE_MARKED']; ?>" /> </td>
			</tr>
		</table></td>
	</tr>
</table>

<table width="95%" cellspacing="1" cellpadding="1" border="0" align="center">
	<tr>
		<td valign="top"><?php echo on_page($total_moderators, $config['topics_per_page'], $start_mod); ?></td>
		<td align="right"><b><span class="gensmall"><a href="javascript:marklist('mod', true);" class="gensmall"><?php echo $user->lang['MARK_ALL']; ?></a> :: <a href="javascript:marklist('mod', false);" class="gensmall"><?php echo $user->lang['UNMARK_ALL']; ?></a></span></b>&nbsp;<br /><span class="nav"><?php echo generate_pagination("admin_groups.$phpEx$SID&amp;action=list&amp;mode=mod&amp;g=$group_id", $total_members, $config['topics_per_page'], $start); ?></span></td>
	</tr>
</table>

<br clear="all" />

<div style="display: none;" id="addleaders"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['ADD_USERS']; ?></th>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['USER_GETS_GROUP_SET']; ?>:</b> <br /><span class="gensmall"><?php echo $user->lang['USER_GETS_GROUP_SET_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="default" value="1" /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="default" value="0" checked="checked" /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['USERNAME']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['USERNAMES_EXPLAIN']; ?></span></td>
		<td class="row2"><textarea name="usernames" cols="40" rows="5"></textarea></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="addleaders" value="<?php echo $user->lang['SUBMIT']; ?>" /> &nbsp; <input class="btnlite" type="submit" value="<?php echo $user->lang['FIND_USERNAME']; ?>" onclick="window.open('<?php echo "../memberlist.$phpEx$SID"; ?>&amp;mode=searchuser&amp;form=mod&amp;field=usernames', '_phpbbsearch', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=740');return false;" /></td>
	</tr>
</table></div>

</form>

<?php

				}

				// Existing members

?>
<h1><?php echo $user->lang['GROUP_LIST']; ?></h1>

<p><?php echo $user->lang['GROUP_LIST_EXPLAIN']; ?></p>

<form name="list" method="post" action="<?php echo "admin_groups.$phpEx$SID&amp;mode=$mode&amp;g=$group_id"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th width="55%"><?php echo $user->lang['USERNAME']; ?></th>
		<th width="3%" nowrap="nowrap">Default</th>
		<th width="20%"><?php echo $user->lang['JOINED']; ?></th>
		<th width="20%"><?php echo $user->lang['POSTS']; ?></th>
		<th width="2%"><?php echo $user->lang['MARK']; ?></th>
	</tr>
<?php


				if (sizeof($group_data['member']))
				{
					$pending = $group_data['member'][0]['user_pending'];

					foreach ($group_data['member'] as $row)
					{
						if ($row['user_pending'] != $pending)
						{
							$pending = $row['user_pending'];

?>
	<tr>
		<td class="row3" colspan="5"><b>Approved Members</b></td>
	</tr>
<?php

						}

						if ($pending)
						{

?>
	<tr>
		<td class="row3" colspan="5"><b>Pending Members</b></td>
	</tr>
<?php

						}

						$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
	<tr>
		<td class="<?php echo $row_class; ?>"><a href="../ucp.<?php echo "$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id']; ?>" target="_profile"><?php echo $row['username']; ?></a></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo ($row['group_id'] == $group_id) ? $user->lang['YES'] : $user->lang['NO']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $user->format_date($row['user_regdate'], $user->lang['DATE_FORMAT']); ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['user_posts']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><input type="checkbox" name="mark[<?php echo $row['user_id']; ?>]" /></td>
	</tr>
<?php

					}
				}
				else
				{

?>
	<tr>
		<td class="row1" colspan="5" align="center"><?php echo $user->lang['GROUPS_NO_MEMBERS']; ?></td>
	</tr>
<?php

				}

?>
	<tr>
		<td class="cat" colspan="5"><table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td><input class="btnlite" type="submit" name="displayadd" value="<?php echo $user->lang['ADD_USERS']; ?>" onclick="showbox('addusers');return false" /></td>
				<td align="right"><select name="action"><option value="approve">Approve Marked</option><option value="default">Make Default Marked</option><option value="delete">Delete Marked</option></select> <input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['UPDATE']; ?>" />&nbsp;<!--input class="btnlite" type="submit" name="approve" value="Approve Marked" /> &nbsp; <input class="btnlite" type="submit" name="delete" value="<?php echo $user->lang['DELETE_MARKED']; ?>" /--> </td>
			</tr>
		</table></td>
	</tr>
</table>

<table width="95%" cellspacing="1" cellpadding="1" border="0" align="center">
	<tr>
		<td valign="top"><?php echo on_page($total_members, $config['topics_per_page'], $start); ?></td>
		<td align="right"><b><span class="gensmall"><a href="javascript:marklist('list', true);" class="gensmall"><?php echo $user->lang['MARK_ALL']; ?></a> :: <a href="javascript:marklist('list', false);" class="gensmall"><?php echo $user->lang['UNMARK_ALL']; ?></a></span></b>&nbsp;<br /><span class="nav"><?php echo generate_pagination("admin_groups.$phpEx$SID&amp;action=list&amp;mode=member&amp;g=$group_id", $total_members, $config['topics_per_page'], $start); ?></span></td>
	</tr>
</table>

<br clear="all" />

<div style="display: none;" id="addusers"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['ADD_USERS']; ?></th>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['USER_GETS_GROUP_SET']; ?>:</b> <br /><span class="gensmall"><?php echo $user->lang['USER_GETS_GROUP_SET_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="default" value="1" /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="default" value="0" checked="checked" /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['USERNAME']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['USERNAMES_EXPLAIN']; ?></span></td>
		<td class="row2"><textarea name="usernames" cols="40" rows="5"></textarea></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="addusers" value="<?php echo $user->lang['SUBMIT']; ?>" /> &nbsp; <input class="btnlite" type="submit" value="<?php echo $user->lang['FIND_USERNAME']; ?>" onclick="window.open('<?php echo "../memberlist.$phpEx$SID"; ?>&amp;mode=searchuser&amp;form=mod&amp;field=usernames', '_phpbbsearch', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=740');return false;" /></td>
	</tr>
</table></div>

</form>

<?php

				break;

			// Management front end
			default:

?>

<h1><?php echo $user->lang['MANAGE']; ?></h1>

<p><?php echo $user->lang['GROUP_MANAGE_EXPLAIN']; ?></p>

<h1><?php echo $user->lang['USER_DEF_GROUPS']; ?></h1>

<p><?php echo $user->lang['USER_DEF_GROUPS_EXPLAIN']; ?></p>

<form method="post" action="<?php echo "admin_groups.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th width="95%"><?php echo $user->lang['MANAGE']; ?></th>
		<th nowrap="nowrap"><?php echo $user->lang['TOTAL_MEMBERS']; ?></th>
		<th colspan="3"><?php echo $user->lang['OPTIONS']; ?></th>
	</tr>
<?php

				$sql = 'SELECT g.group_id, g.group_name, g.group_type, COUNT(ug.user_id) AS total_members 
					FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug 
					WHERE ug.group_id = g.group_id 
					GROUP BY ug.group_id 
					ORDER BY g.group_type ASC, g.group_name';
				$result = $db->sql_query($sql);

				$special_toggle = false;
				while ($row = $db->sql_fetchrow($result) )
				{
					$row_class = ($row_class != 'row1') ? 'row1' : 'row2';

					if ($row['group_type'] == GROUP_SPECIAL && !$special_toggle)
					{
						$special_toggle = true;

?>
	<tr>
		<td class="cat" colspan="5" align="right">Create new group: <input class="post" type="text" name="group_name" maxlength="30" /> <input class="btnmain" type="submit" name="add" value="<?php echo $user->lang['SUBMIT']; ?>" /></td>
	</tr>
</table>

<h1><?php echo $user->lang['SPECIAL_GROUPS']; ?></h1>

<p><?php echo $user->lang['SPECIAL_GROUPS_EXPLAIN']; ?></p>

<table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th width="95%"><?php echo $user->lang['MANAGE']; ?></th>
		<th><?php echo $user->lang['TOTAL_MEMBERS']; ?></th>
		<th colspan="3"><?php echo $user->lang['OPTIONS']; ?></th>
	</tr>
<?php

					}

					$group_id = $row['group_id'];
					$group_name = (!empty($user->lang['G_' . $row['group_name']]))? $user->lang['G_' . $row['group_name']] : $row['group_name'];

?>
	<tr>
		<td width="95%" class="<?php echo $row_class; ?>"><a href="admin_groups.<?php echo "$phpEx$SID&amp;mode=$mode&amp;action=list&amp;g=$group_id"; ?>"><?php echo $group_name;?></a></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<?php echo $row['total_members']; ?>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_groups.$phpEx$SID&amp;mode=$mode&amp;action=default&amp;g=$group_id"; ?>">Default<?php echo $user->lang['']; ?></a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_groups.$phpEx$SID&amp;mode=$mode&amp;action=edit&amp;g=$group_id"; ?>"><?php echo $user->lang['EDIT']; ?></a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<?php 
	
					echo (!$special_toggle) ? "<a href=\"admin_groups.$phpEx$SID&amp;mode=$mode&amp;&amp;action=delete&amp;g=$group_id\">" . $user->lang['DELETE'] . '</a>' : $user->lang['DELETE'];


?>&nbsp;</td>
	</tr>
<?php

					if (is_array($pending[$group_id]) )
					{
						foreach ($pending[$group_id] as $pending_ary )
						{
							$row_class = ($row_class != 'row1') ? 'row1' : 'row2';

?>
	<tr>
		<td class="<?php echo $row_class; ?>"><?php echo $pending_ary['username'];?></td>
		<td class="<?php echo $row_class; ?>" align="center"><input class="btnlite" type="submit" name="approve[<?php echo $pending_ary['user_id']; ?>]" value="<?php echo $user->lang['Approve_selected'];?>" /> &nbsp; <input class="btnlite" type="submit" name="decline[<?php echo $pending_ary['user_id']; ?>]" value="<?php echo $user->lang['Deny_selected'];?>" /></td>
	</tr>
<?php

						}
					}
				}
				$db->sql_freeresult($result);

?>
	<tr>
		<td class="cat" colspan="5">&nbsp;</td>
	</tr>
</table></form>

<?php

				break;





		}

		// Common javascript
?>

<script language="Javascript" type="text/javascript">
<!--
function marklist(match, status)
{
	len = eval('document.' + match + '.length');
	for (i = 0; i < len; i++)
	{
		eval('document.' + match + '.elements[i].checked = ' + status);
	}
}

function getElement(id)
{ 
	return document.getElementById ? document.getElementById(id) : document.all ? document.all(id) : null; 
} 

function showbox(id)
{ 
	var el = getElement(id); 
	if (el && el.style) 
		el.style.display = ''; 
} 

function hidebox(id)
{ 
	var el = getElement(id); 
	if (el && el.style) 
		el.style.display = 'none'; 
} 

//-->
</script>

<?php

		adm_page_footer();
		break;




	case 'prefs':
		adm_page_header($user->lang['GROUP_PREFS']);


		adm_page_footer();
		break;



	default:
		trigger_error($user->lang['NO_MODE']);
}

exit;


















/*











	case 'add':


		break;






	case 'delete':
		// TODO:
		// Need to offer ability to demote moderators or remove from group
		break;





	case 'approve':
		break;











adm_page_footer();





	case 'prefs':

			}
			else
			{
				$user_lang = (!empty($_POST['user_lang'])) ? htmlspecialchars($_POST['user_lang']) : '';
				$user_tz = (isset($_POST['user_tz'])) ? doubleval($_POST['user_tz']) : '';
				$user_dst = (isset($_POST['user_dst'])) ? intval($_POST['user_dst']) : '';
			}

?>
<h1><?php echo $user->lang['GROUP_SETTINGS']; ?></h1>

<p><?php echo $user->lang['GROUP_SETTINGS_EXPLAIN']; ?></p>

<form method="post" action="admin_groups.<?php echo "$phpEx$SID&amp;action=edit&amp;g=$group_id"; ?>"><table class="bg" width="90%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['GROUP_SETTINGS']; ?></th>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['GROUP_LANG']; ?>:</td>
		<td class="row1"><select name="user_lang"><?php echo '<option value="-1" selected="selected">' . $user->lang['USER_DEFAULT'] . '</option>' . language_select(); ?></select></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['GROUP_TIMEZONE']; ?>:</td>
		<td class="row1"><select name="user_tz"><?php echo '<option value="-14" selected="selected">' . $user->lang['USER_DEFAULT'] . '</option>' . tz_select(); ?></select></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['GROUP_DST']; ?>:</td>
		<td class="row1" nowrap="nowrap"><input type="radio" name="user_dst" value="0" /> <?php echo $user->lang['DISABLED']; ?> &nbsp; <input type="radio" name="user_dst" value="1" /> <?php echo $user->lang['ENABLED']; ?> &nbsp; <input type="radio" name="user_dst" value="-1" checked="checked" /> <?php echo $user->lang['USER_DEFAULT']; ?></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="submitprefs" value="<?php echo $user->lang['SUBMIT']; ?>" /> &nbsp; <input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
	</tr>
</table></form>

<?php

*/

?>