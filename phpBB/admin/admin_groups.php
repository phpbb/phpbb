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

if (!empty($setmodules) )
{
	if (!$auth->acl_get('a_group') )
	{
		return;
	}

	$module['GROUP']['MANAGE'] = basename(__FILE__) . "$SID";

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

// Do we have general permissions?
if (!$auth->acl_get('a_group') )
{
	trigger_error($user->lang['NO_ADMIN']);
}


// Check and set some common vars
$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : ((isset($_POST['addgroup'])) ? 'addgroup' : '');
$group_id = (isset($_REQUEST['g'])) ? intval($_REQUEST['g']) : '';
$start = (isset($_GET['start'])) ? intval($_GET['start']) : 0;

// Which page?
page_header($user->lang['MANAGE']);

switch ($action)
{
	case 'edit':
	case 'addgroup':

		$error = '';

		// Grab data, even when submitting updates
		if ($action == 'edit')
		{
			$sql = "SELECT * 
				FROM " . GROUPS_TABLE . " 
				WHERE group_id = $group_id";
			$result = $db->sql_query($sql);

			if (!extract($db->sql_fetchrow($result)))
			{
				trigger_error($user->lang['NO_GROUP']);
			}
			$db->sql_freeresult($result);
		}
		
		// Did we submit?
		if (isset($_POST['submit']) || isset($_POST['submitprefs']))
		{
			if (isset($_POST['submit']))
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
			}
			else
			{
				$user_lang = (!empty($_POST['user_lang'])) ? htmlspecialchars($_POST['user_lang']) : '';
				$user_tz = (isset($_POST['user_tz'])) ? doubleval($_POST['user_tz']) : '';
				$user_dst = (isset($_POST['user_dst'])) ? intval($_POST['user_dst']) : '';
			}

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
				$user_sql .= (isset($_POST['submitprefs']) && $user_lang != -1) ? ((($user_sql != '') ? ', ' : '') . "user_lang = '$user_lang'") : '';
				$user_sql .= (isset($_POST['submitprefs']) && $user_tz != -14) ? ((($user_sql != '') ? ', ' : '') . "user_timezone = $user_tz") : '';
				$user_sql .= (isset($_POST['submitprefs']) && $user_dst != -1) ? ((($user_sql != '') ? ', ' : '') . "user_dst = $user_dst") : '';

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
							$sql = "SELECT user_id
								FROM " . USER_GROUP_TABLE . " 
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

								$sql = "UPDATE " . USERS_TABLE . " 
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
						$sql = "UPDATE " . USERS_TABLE . " 
							SET $user_sql 
							WHERE user_id IN (
								SELECT user_id
									FROM " . USER_GROUP_TABLE . " 
									WHERE group_id = $group_id)";
						$db->sql_query($sql);
				}

				trigger_error($user->lang['GROUP_UPDATED']);
			}
		}

?>

<h1><?php echo $user->lang['MANAGE'] . ' : <i>' . $group_name . '</i>'; ?></h1>

<p><?php echo $user->lang['GROUP_EDIT_EXPLAIN']; ?></p>

<?php 

		$sql = "SELECT * 
			FROM " . RANKS_TABLE . "
			WHERE rank_special = 1
			ORDER BY rank_title";
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

		$type_open = ($group_type == GROUP_OPEN) ? ' checked="checked"' : '';
		$type_closed = ($group_type == GROUP_CLOSED) ? ' checked="checked"' : '';
		$type_hidden = ($group_type == GROUP_HIDDEN) ? ' checked="checked"' : '';
		$type_free = ($group_type == GROUP_FREE) ? ' checked="checked"' : '';

?>

<script language="javascript" type="text/javascript">
<!--

function swatch()
{
	window.open('./swatch.php?form=settings&amp;name=group_colour', '_swatch', 'HEIGHT=115,resizable=yes,scrollbars=no,WIDTH=636');
	return false;
}

//-->
</script>

<form name="settings" method="post" action="admin_groups.<?php echo "$phpEx$SID&amp;action=$action&amp;g=$group_id"; ?>"><table class="bg" width="90%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['GROUP_DETAILS']; ?></th>
	</tr>
<?php

		if ($error != '')
		{

?>
	<tr>
		<td class="row1" colspan="2" align="center"><span style="color:red"><?php echo $error; ?></span></td>
	</tr>
<?php

		}

?>
	<tr>
		<td class="row2"><?php echo $user->lang['GROUP_NAME']; ?>:</td>
		<td class="row1"><?php 
	
		if ($group_type != GROUP_SPECIAL)
		{
		
?><input type="text" name="group_name" value="<?php echo (!empty($group_name)) ? $group_name : ''; ?>" size="40" maxlength="40" /><?php
	
		}
		else
		{
		
?><b><?php echo (!empty($user->lang['G_' . $group_name])) ? $user->lang['G_' . $group_name] : $group_name; ?></b><?php
	
		}
	
?></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['GROUP_DESC']; ?>:</td>
		<td class="row1"><input type="text" name="group_description" value="<?php echo (!empty($group_description)) ? $group_description : ''; ?>" size="40" maxlength="255" /></td>
	</tr>
<?php

		if ($group_type != GROUP_SPECIAL)
		{

?>
	<tr>
		<td class="row2"><?php echo $user->lang['GROUP_TYPE']; ?>:<br /><span class="gensmall"><?php echo $user->lang['GROUP_TYPE_EXPLAIN']; ?></span></td>
		<td class="row1" nowrap="nowrap"><input type="radio" name="group_type" value="<?php echo GROUP_FREE . '"' . $type_free; ?> /> <?php echo $user->lang['GROUP_OPEN']; ?> &nbsp; <input type="radio" name="group_type" value="<?php echo GROUP_OPEN . '"' . $type_open; ?> /> <?php echo $user->lang['GROUP_REQUEST']; ?> &nbsp; <input type="radio" name="group_type" value="<?php echo GROUP_CLOSED . '"' . $type_closed; ?> /> <?php echo $user->lang['GROUP_CLOSED']; ?> &nbsp; <input type="radio" name="group_type" value="<?php echo GROUP_HIDDEN . '"' . $type_hidden; ?> /> <?php echo $user->lang['GROUP_HIDDEN']; ?></td>
	</tr>
<?php

		}

?>
	<tr>
		<th colspan="2"><?php echo $user->lang['GROUP_SETTINGS_SAVE']; ?></th>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['GROUP_COLOR']; ?>:<br /><span class="gensmall"><?php echo sprintf($user->lang['GROUP_COLOR_EXPLAIN'], '<a href="swatch.html" onclick="swatch();return false" target="_swatch">', '</a>'); ?></span></td>
		<td class="row1" nowrap="nowrap"><input type="text" name="group_colour" value="<?php echo (!empty($group_colour)) ? $group_colour : ''; ?>" size="6" maxlength="6" /></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['GROUP_RANK']; ?>:</td>
		<td class="row1"><select name="group_rank"><?php echo $rank_options; ?></select></td>
	</tr>
	<!-- tr>
		<td class="row2"><?php echo $user->lang['GROUP_AVATAR']; ?>:<br /><span class="gensmall"><?php echo $user->lang['GROUP_AVATAR_EXPLAIN']; ?></span></td>
		<td class="row1">&nbsp;</td>
	</tr -->
	<tr>
		<td class="cat" colspan="2" align="center"><input class="mainoption" type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" /> &nbsp; <input class="liteoption" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
	</tr>
</table></form>

<?php

		if ($action != 'addgroup')
		{

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
		<td class="cat" colspan="2" align="center"><?php

			if ($group_type == GROUP_SPECIAL)
			{

?><input type="hidden" name="group_type" value="<?php echo GROUP_SPECIAL; ?>" /><?php

			}

?><input class="mainoption" type="submit" name="submitprefs" value="<?php echo $user->lang['SUBMIT']; ?>" /> &nbsp; <input class="liteoption" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
	</tr>
</table></form>

<?php

		}

		break;

	case 'add':
		break;

	case 'delete':
	case 'deletegroup':
		break;

	case 'list':

		$sql = "SELECT * 
			FROM " . GROUPS_TABLE . " 
			WHERE group_id = $group_id";
		$result = $db->sql_query($sql);

		if (!extract($db->sql_fetchrow($result)))
		{
			trigger_error($user->lang['NO_GROUP']);
		}
		$db->sql_freeresult($result);

?>

<h1><?php echo $user->lang['GROUP_MEMBERS']; ?></h1>

<p><?php echo $user->lang['GROUP_MEMBERS_EXPLAIN']; ?></p>

<?php

		if ($group_type != GROUP_SPECIAL)
		{

?>
<h1><?php echo $user->lang['GROUP_MODS']; ?></h1>

<p><?php echo $user->lang['GROUP_MODS_EXPLAIN']; ?></p>

<form name="mods" method="post" action="admin_groups.<?php echo "$phpEx$SID&amp;action=list"; ?>"><table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $user->lang['USERNAME']; ?></th>
		<th><?php echo $user->lang['JOINED']; ?></th>
		<th><?php echo $user->lang['POSTS']; ?></th>
		<th width="2%"><?php echo $user->lang['MARK']; ?></th>
	</tr>
<?php

			// Group moderators
			$sql = "SELECT u.user_id, u.username 
				FROM " . USERS_TABLE . " u, " . GROUPS_MODERATOR_TABLE . " gm 
				WHERE gm.group_id = $group_id 
				ORDER BY u.user_id";
			$result = $db->sql_query($sql);

			$db->sql_freeresult($result);

			if ($row = $db->sql_fetchrow($result) )
			{
				do
				{
	

				}
				while ($row = $db->sql_fetchrow($result) );

?>

<?php 

			}
			else
			{

?>
	<tr>
		<td class="row3" colspan="4" align="center"><?php echo $user->lang['GROUPS_NO_MODS']; ?></td>
	</tr>
<?php

			}

?>
	<tr>
		<td class="cat" colspan="4" align="right"></td>
	</tr>
</table></form>

<?php

			// Pending users
			$sql = "SELECT u.user_id, u.username
				FROM " . USERS_TABLE . " u, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g 
				WHERE ug.user_pending = 1 
					AND u.user_id = ug.user_id
				ORDER BY ug.group_id, u.user_id";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result) )
			{

?>
<h1><?php echo $user->lang['GROUP_PENDING']; ?></h1>

<p><?php echo $user->lang['GROUP_PENDING_EXPLAIN']; ?></p>

<form name="pending" method="post" action="admin_groups.<?php echo "$phpEx$SID&amp;action=list"; ?>"><table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $user->lang['USERNAME']; ?></th>
		<th><?php echo $user->lang['JOINED']; ?></th>
		<th><?php echo $user->lang['POSTS']; ?></th>
		<th width="2%"><?php echo $user->lang['MARK']; ?></th>
	</tr>
<?php

				do
				{

?>
	<tr>
		<td class="<?php echo $row_class; ?>"><a href="../ucp.<?php echo "$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id']; ?>" target="_profile"><?php echo $row['username']; ?></a></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $user->format_date($row['user_regdate'], $user->lang['DATE_FORMAT']); ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['user_posts']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><input type="checkbox" name="mark[<?php echo $row['user_id']; ?>]" /></td>
	</tr>
<?php

				}
				while ($row = $db->sql_fetchrow($result) );

?>
</table></form>

<?php

			}
			$db->sql_freeresult($result);
		}

		$sql = "SELECT COUNT(user_id) AS total_members 
			FROM " . USER_GROUP_TABLE . " 
			WHERE group_id = $group_id";
		$result = $db->sql_query($sql);

		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		$total_members = $row['total_members'];

		// Existing members
		$sql = "SELECT u.user_id, u.username, u.user_regdate, u.user_posts 
			FROM " . USERS_TABLE . " u, " . USER_GROUP_TABLE . " ug 
			WHERE ug.group_id = $group_id 
				AND ug.user_pending = 0  
				AND u.user_id = ug.user_id
			ORDER BY u.username 
			LIMIT $start, " . $config['topics_per_page'];
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result) )
		{

?>
<h1><?php echo $user->lang['GROUP_LIST']; ?></h1>

<p><?php echo $user->lang['GROUP_LIST_EXPLAIN']; ?></p>

<?php

?>
<form name="list" method="post" action="admin_groups.<?php echo "$phpEx$SID&amp;action=list"; ?>"><table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $user->lang['USERNAME']; ?></th>
		<th><?php echo $user->lang['JOINED']; ?></th>
		<th><?php echo $user->lang['POSTS']; ?></th>
		<th width="2%"><?php echo $user->lang['MARK']; ?></th>
	</tr>
<?php

			do
			{

				$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
	<tr>
		<td class="<?php echo $row_class; ?>"><a href="../ucp.<?php echo "$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id']; ?>" target="_profile"><?php echo $row['username']; ?></a></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $user->format_date($row['user_regdate'], $user->lang['DATE_FORMAT']); ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['user_posts']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><input type="checkbox" name="mark[<?php echo $row['user_id']; ?>]" /></td>
	</tr>
<?php

			}
			while ($row = $db->sql_fetchrow($result));

?>
	<tr>
		<td class="cat" colspan="4" align="right"><input class="liteoption" type="submit" name="delete" value="<?php echo $user->lang['DELETE_MARKED']; ?>" /> </td>
	</tr>
</table>

<table width="80%" cellspacing="1" cellpadding="0" border="0" align="center">
	<tr>
		<td valign="top"><?php echo on_page($total_members, $config['topics_per_page'], $start); ?></td>
		<td align="right"><b><span class="gensmall"><a href="javascript:marklist('list', true);" class="gensmall"><?php echo $user->lang['MARK_ALL']; ?></a> :: <a href="javascript:marklist('list', false);" class="gensmall"><?php echo $user->lang['UNMARK_ALL']; ?></a></span></b>&nbsp;<br /><span class="nav"><?php echo generate_pagination("admin_groups.$phpEx$SID&amp;action=list&amp;g=$group_id", $total_members, $config['topics_per_page'], $start); ?></span></td>
	</tr>
</table></form>

<?php

		}
		$db->sql_freeresult($result);

		break;

	default:
	
		// Default mangement page

?>

<h1><?php echo $user->lang['MANAGE']; ?></h1>

<p><?php echo $user->lang['GROUP_MANAGE_EXPLAIN']; ?></p>

<h1><?php echo $user->lang['USER_DEF_GROUPS']; ?></h1>

<p><?php echo $user->lang['USER_DEF_GROUPS_EXPLAIN']; ?></p>

<form method="post" action="admin_groups.<?php echo "$phpEx$SID"; ?>"><table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th width="95%"><?php echo $user->lang['MANAGE']; ?></th>
		<th><?php echo $user->lang['ACTION']; ?></th>
	</tr>
<?php

		$sql = "SELECT group_id, group_name, group_type 
			FROM " . GROUPS_TABLE . "
			ORDER BY group_type ASC, group_name";
		$result = $db->sql_query($sql);

		$special_toggle = false;
		if ($row = $db->sql_fetchrow($result) )
		{
			do
			{

				if ($row['group_type'] == GROUP_SPECIAL && !$special_toggle)
				{
					$special_toggle = true;

?>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="mainoption" type="submit" name="addgroup" value="<?php echo $user->lang['ADD_NEW_GROUP']; ?>" /></td>
	</tr>
</table>

<h1><?php echo $user->lang['SPECIAL_GROUPS']; ?></h1>

<p><?php echo $user->lang['SPECIAL_GROUPS_EXPLAIN']; ?></p>

<table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th width="95%"><?php echo $user->lang['MANAGE']; ?></th>
		<th><?php echo $user->lang['ACTION']; ?></th>
	</tr>
<?php

				}

				$row_class = ($row_class != 'row1') ? 'row1' : 'row2';

				$group_id = $row['group_id'];
				$group_name = (!empty($user->lang['G_' . $row['group_name']]))? $user->lang['G_' . $row['group_name']] : $row['group_name'];

?>
	<tr>
		<td class="<?php echo $row_class; ?>"><a href="admin_groups.<?php echo "$phpEx$SID&amp;action=list&amp;g=$group_id"; ?>"><?php echo $group_name;?></a></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<a href="admin_groups.<?php echo "$phpEx$SID&amp;action=add&amp;g=$group_id"; ?>"><?php echo $user->lang['ADD']; ?></a> | <a href="admin_groups.<?php echo "$phpEx$SID&amp;action=edit&amp;g=$group_id"; ?>"><?php echo $user->lang['EDIT']; ?></a><?php 

				if (!$special_toggle)
				{

?> | <a href="admin_groups.<?php echo "$phpEx$SID&amp;action=delete&amp;g=$group_id"; ?>"><?php echo $user->lang['DELETE']; ?></a><?php
	
				}
			
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
		<td class="<?php echo $row_class; ?>" align="center"><input class="liteoption" type="submit" name="approve[<?php echo $pending_ary['user_id']; ?>]" value="<?php echo $user->lang['Approve_selected'];?>" /> &nbsp; <input class="liteoption" type="submit" name="decline[<?php echo $pending_ary['user_id']; ?>]" value="<?php echo $user->lang['Deny_selected'];?>" /></td>
	</tr>
<?php

					}
				}
			}
			while ($row = $db->sql_fetchrow($result) );
		}
		$db->sql_freeresult($result);

?>
	<tr>
		<td class="cat" colspan="2">&nbsp;</td>
	</tr>
</table></form>

<?php

		break;

}

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
//-->
</script>

<?php

page_footer();

?>