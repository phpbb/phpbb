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
$action = (isset($_REQUEST['action']))? $_REQUEST['action'] : ((isset($_POST['addgroup'])) ? 'addgroup' : '');
$group_id = (isset($_REQUEST['g']))? intval($_REQUEST['g']) : '';

// Which page?
page_header($user->lang['MANAGE']);

switch ($action)
{
	case 'edit':
	case 'addgroup':

		if (isset($_POST['submit']))
		{
			$group_name = (!empty($_POST['group_name'])) ? $_POST['group_name'] : '';
			$group_description = (!empty($_POST['group_description'])) ? $_POST['group_description'] : '';
			$group_type = (!empty($_POST['group_type'])) ? $_POST['group_type'] : '';
			$group_color = (!empty($_POST['group_color'])) ? $_POST['group_color'] : '';
			$group_rank = (!empty($_POST['group_rank'])) ? $_POST['group_rank'] : '';

			$force_color = (!empty($_POST['force_color'])) ? true : false;

			// Check data

			if ($group_color != '')
			{
				$color_sql = (!$force_color) ? "AND user_colour = ''" : '';
				switch (SQL_LAYER)
				{
					case 'mysql':
					case 'mysql4':
						$sql = "SELECT user_id
							FROM " . USER_GROUP_TABLE . " 
							WHERE group_id = $group_id";
						$result = $db->sql_query($sql);

						if ($row = $db->sql_fetchrow($result))
						{
							$user_id_sql = '';
							do
							{
								$user_id_sql .= (($user_id_sql != '') ? ', ' : '') . $row['user_id'];
							}
							while ($row = $db->sql_fetchrow($result));

							$sql = "UPDATE " . USERS_TABLE . " 
								SET user_colour = '$group_color' 
								WHERE user_id IN ($user_id_sql)
									$color_sql";
							$db->sql_query($sql);
						}
						$db->sql_freeresult($result);
						unset($user_id_sql);

						break;

					default:
						$sql = "UPDATE " . USERS_TABLE . " 
							SET user_colour = '$group_color' 
							WHERE user_id IN (
								SELECT user_id
									FROM " . USER_GROUP_TABLE . " 
									WHERE group_id = $group_id)
								$color_sql";
						$db->sql_query($sql);
				}

				trigger_error('Done');
			}
		}

		if ($action == 'edit' && empty($_POST['submit']))
		{
			$sql = "SELECT * 
				FROM " . GROUPS_TABLE . " 
				WHERE group_id = $group_id";
			$result = $db->sql_query($sql);

			if (!extract($db->sql_fetchrow($result)))
			{
				trigger_error($user->lang['NO_GROUP']);
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

		$type_open = ($group_type == GROUP_OPEN) ? ' checked="checked"' : '';
		$type_closed = ($group_type == GROUP_CLOSED) ? ' checked="checked"' : '';
		$type_hidden = ($group_type == GROUP_HIDDEN) ? ' checked="checked"' : '';
		$type_free = ($group_type == GROUP_FREE) ? ' checked="checked"' : '';

		$force_color_yes = (!isset($force_color) || $force_color) ? ' checked="checked"' : '';
		$force_color_no = (isset($force_color) && !$force_color) ? ' checked="checked"' : '';

?>

<script language="javascript" type="text/javascript">
<!--

function swatch()
{
	window.open('./swatch.php?form=settings&amp;name=group_color', '_swatch', 'HEIGHT=115,resizable=yes,scrollbars=no,WIDTH=636');
	return false;
}

//-->
</script>

<form name="settings" method="post" action="admin_groups.<?php echo "$phpEx$SID&amp;action=edit&amp;g=$group_id"; ?>"><table class="bg" width="90%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['GROUP_DETAILS']; ?></th>
	</tr>
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
		<td class="row1" nowrap="nowrap"><input type="text" name="group_color" value="<?php echo (!empty($group_color)) ? $group_color : ''; ?>" size="6" maxlength="6" /> <input type="radio" name="force_color" value="1"<?php echo $force_color_yes; ?> /> <?php echo $user->lang['FORCE_COLOR']; ?> &nbsp; <input type="radio" name="force_color" value="0"<?php echo $force_color_no; ?> /> <?php echo $user->lang['USER_COLOR']; ?></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['GROUP_RANK']; ?>:</td>
		<td class="row1"><select name="group_rank"><?php echo $rank_options; ?></select></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['GROUP_AVATAR']; ?>:<br /><span class="gensmall"><?php echo $user->lang['GROUP_AVATAR_EXPLAIN']; ?></span></td>
		<td class="row1">&nbsp;</td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><?php

	if ($group_type == GROUP_SPECIAL)
	{

?><input type="hidden" name="group_type" value="<?php echo GROUP_SPECIAL; ?>" /><?php

	}

?><input class="mainoption" type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" /> &nbsp; <input class="liteoption" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
	</tr>
</table></form>

<h1><?php echo $user->lang['GROUP_SETTINGS']; ?></h1>

<p><?php echo $user->lang['GROUP_SETTINGS_EXPLAIN']; ?></p>

<form method="post" action="admin_groups.<?php echo "$phpEx$SID&amp;action=edit&amp;g=$group_id"; ?>"><table class="bg" width="90%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['GROUP_SETTINGS']; ?></th>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['GROUP_LANG']; ?>:</td>
		<td class="row1"><select name="tz"><?php echo '<option value="-1" selected="selected">' . $user->lang['USER_DEFAULT'] . '</option>' . language_select(); ?></select></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['GROUP_TIMEZONE']; ?>:</td>
		<td class="row1"><select name="tz"><?php echo '<option value="-1" selected="selected">' . $user->lang['USER_DEFAULT'] . '</option>' . tz_select(); ?></select></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['GROUP_DST']; ?>:</td>
		<td class="row1" nowrap="nowrap"><input type="radio" name="dst" value="0" /> <?php echo $user->lang['DISABLED']; ?> &nbsp; <input type="radio" name="dst" value="1" /> <?php echo $user->lang['ENABLED']; ?> &nbsp; <input type="radio" name="dst" value="-1" checked="checked" /> <?php echo $user->lang['USER_DEFAULT']; ?></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><?php

	if ($group_type == GROUP_SPECIAL)
	{

?><input type="hidden" name="group_type" value="<?php echo GROUP_SPECIAL; ?>" /><?php

	}

?><input class="mainoption" type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" /> &nbsp; <input class="liteoption" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
	</tr>
</table></form>

<?php

		break;

	case 'add':
		break;

	case 'delete':
		break;

	case 'list':

?>

<h1><?php echo $user->lang['GROUP_MEMBERS']; ?></h1>

<p><?php echo $user->lang['GROUP_LIST_EXPLAIN']; ?></p>

<form method="post" action="admin_groups.<?php echo "$phpEx$SID&amp;action=list"; ?>"><table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['']; ?></th>
	</tr>
</table></form>

<?php

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

		$sql = "SELECT ug.group_id, u.user_id, u.username
			FROM " . USERS_TABLE . " u, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g 
			WHERE ug.user_pending = 1 
				AND g.group_type = " . GROUP_SPECIAL . " 
				AND u.user_id = ug.user_id
			ORDER BY ug.group_id, u.user_id";
		$result = $db->sql_query($sql);

		$pending = array();
		if ($row = $db->sql_fetchrow($result) )
		{
			do
			{
				$pending[$row['group_id']][] = $row;
			}
			while ($row = $db->sql_fetchrow($result) );
		}
		$db->sql_freeresult($result);

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
</table></form>

<?php

		break;

}

page_footer();

?>