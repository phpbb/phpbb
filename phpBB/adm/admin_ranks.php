<?php
/***************************************************************************
 *                              admin_ranks.php
 *                            -------------------
 *   begin                : Thursday, Jul 12, 2001
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
	if (!$auth->acl_get('a_ranks'))
	{
		return;
	}

	$module['USER']['RANKS'] = basename(__FILE__) . $SID;
	return;
}

define('IN_PHPBB', 1);
// Let's set the root dir for phpBB
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

// Do we have permission?
if (!$auth->acl_get('a_ranks'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// Check mode
if (isset($_REQUEST['mode']))
{
	$mode = $_REQUEST['mode'];
}
else
{
	// These could be entered via a form button
	if (isset($_POST['add']))
	{
		$mode = 'add';
	}
	else if (isset($_POST['save']))
	{
		$mode = 'save';
	}
	else
	{
		$mode = '';
	}
}

// Process mode
if ($mode != '')
{
	if ($mode == 'edit' || $mode == 'add')
	{
		//
		// They want to add a new rank, show the form.
		//
		$rank_id = (isset($_GET['id'])) ? intval($_GET['id']) : 0;

		$s_hidden_fields = '<input type="hidden" name="mode" value="save" />';

		if ($mode == 'edit')
		{
			if (empty($rank_id))
			{
				trigger_error($user->lang['Must_select_rank']);
			}

			$sql = "SELECT * FROM " . RANKS_TABLE . "
				WHERE rank_id = $rank_id";
			$result = $db->sql_query($sql);

			$rank_info = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$s_hidden_fields .= '<input type="hidden" name="id" value="' . $rank_id . '" />';

		}
		else
		{
			$rank_info['rank_special'] = 0;
		}

		page_header($user->lang['RANKS']);

?>

<h1><?php echo $user->lang['RANKS']; ?></h1>

<p><?php echo $user->lang['RANKS_EXPLAIN']; ?></p>

<form method="post" action="<?php echo "admin_ranks.$phpEx$SID"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['RANKS']; ?></th>
	</tr>
	<tr>
		<td class="row1" width="40%" nowrap="nowrap"><?php echo $user->lang['RANK_TITLE']; ?>: </td>
		<td class="row2"><input type="text" name="title" size="35" maxlength="40" value="<?php echo $rank_info['rank_title']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="40%" nowrap="nowrap"><?php echo $user->lang['RANK_SPECIAL']; ?>: </td>
		<td class="row2"><input type="radio" name="special_rank" value="1"<?php echo ($rank_info['rank_special']) ? ' checked="checked"' : ''; ?> /><?php echo $user->lang['YES']; ?> &nbsp;&nbsp;<input type="radio" name="special_rank" value="0"<?php echo (!$rank_info['rank_special']) ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1" width="40%" nowrap="nowrap"><?php echo $user->lang['RANK_MINIMUM']; ?>: </td>
		<td class="row2"><input type="text" name="min_posts" size="5" maxlength="10" value="<?php echo ($rank_info['rank_special']) ? '' : $rank_info['rank_min']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><?php echo $user->lang['RANK_IMAGE']; ?>: <br /><span class="gensmall"><?php echo $user->lang['RANK_IMAGE_EXPLAIN']; ?></span></td>
		<td class="row2" valign="middle"><input type="text" name="rank_image" size="40" maxlength="255" value="<?php echo ($rank_info['rank_image'] != '') ? $rank_info['rank_image'] : ''; ?>" /> &nbsp; <?php echo ($rank_info['rank_image'] != '') ? '<img src="../' . $rank_info['rank_image'] . '" />' : ''; ?></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><?php echo $s_hidden_fields; ?><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="liteoption" /></td>
	</tr>
</table></form>

<?php

		page_footer();

	}
	else if ($mode == 'save')
	{
		//
		// Ok, they sent us our info, let's update it.
		//

		$rank_id = (isset($_POST['id'])) ? intval($_POST['id']) : 0;
		$rank_title = (isset($_POST['title'])) ? trim($_POST['title']) : '';
		$special_rank = (!empty($_POST['special_rank'])) ? 1 : 0;
		$min_posts = (isset($_POST['min_posts'])) ? intval($_POST['min_posts']) : -1;
		$rank_image = (isset($_POST['rank_image'])) ? trim($_POST['rank_image']) : '';

		if ($rank_title == '')
		{
			trigger_error($user->lang['MUST_SELECT_RANK']);
		}

		if ($special_rank == 1)
		{
			$min_posts = -1;
		}

		//
		// The rank image has to be a jpg, gif or png
		//
		if ($rank_image != '')
		{
			if (!preg_match('#(\.gif|\.png|\.jpg|\.jpeg)$#is', $rank_image))
			{
				$rank_image = '';
			}
		}

		if ($rank_id)
		{
			$sql = "UPDATE " . RANKS_TABLE . "
				SET rank_title = '" . $db->sql_escape($rank_title) . "', rank_special = $special_rank, rank_min = $min_posts, rank_image = '" . $db->sql_escape($rank_image) . "'
				WHERE rank_id = $rank_id";

			$message = $user->lang['RANK_UPDATED'];
		}
		else
		{
			$sql = "INSERT INTO " . RANKS_TABLE . " (rank_title, rank_special, rank_min, rank_image)
				VALUES ('" . $db->sql_escape($rank_title) . "', $special_rank, $min_posts, '" . $db->sql_escape($rank_image) . "')";

			$message = $user->lang['RANK_ADDED'];
		}
		$db->sql_query($sql);

		trigger_error($message);

	}
	else if ($mode == 'delete')
	{
		// Ok, they want to delete their rank
		$rank_id = (isset($_REQUEST['id'])) ? intval($_REQUEST['id']) : 0;

		if ($rank_id)
		{
			$sql = "DELETE FROM " . RANKS_TABLE . "
				WHERE rank_id = $rank_id";
			$db->sql_query($sql);

			$sql = "UPDATE " . USERS_TABLE . "
				SET user_rank = 0
				WHERE user_rank = $rank_id";
			$db->sql_query($sql);

			trigger_error($user->lang['RANK_REMOVED']);

		}
		else
		{
			trigger_error($user->lang['MUST_SELECT_RANK']);
		}
	}
}

page_header($user->lang['RANKS']);

?>

<h1><?php echo $user->lang['RANKS']; ?></h1>

<p><?php echo $user->lang['RANKS_EXPLAIN']; ?></p>

<form method="post" action="<?php echo "admin_ranks.$phpEx$SID"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $user->lang['RANK_IMAGE']; ?></th>
		<th><?php echo $user->lang['RANK_TITLE']; ?></th>
        <th><?php echo $user->lang['RANK_MINIMUM']; ?></th>
		<th><?php echo $user->lang['ACTION']; ?></th>
	</tr>
<?php

//
// Show the default page
//
$sql = "SELECT * FROM " . RANKS_TABLE . "
	ORDER BY rank_min ASC, rank_special ASC";
$result = $db->sql_query($sql);

if ($row = $db->sql_fetchrow($result))
{
	do
	{
		$row_class = ($row_class != 'row1') ? 'row1' : 'row2';
?>
	<tr>
		<td class="<?php echo $row_class; ?>" align="center"><img src="../<?php echo $row['rank_image']; ?>"" border="0" alt="<?php echo $row['rank_title']; ?>" title="<?php echo $row['rank_title']; ?>" /></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['rank_title']; ?></td>
        <td class="<?php echo $row_class; ?>" align="center"><?php echo ($row['rank_special']) ? '-' : $row['rank_min']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center">&nbsp;<a href="<?php echo "admin_ranks.$phpEx$SID&amp;mode=edit&amp;id=" . $row['rank_id']; ?>"><?php echo $user->lang['EDIT']; ?></a> | <a href="<?php echo "admin_ranks.$phpEx$SID&amp;mode=delete&amp;id=" . $row['rank_id']; ?>"><?php echo $user->lang['DELETE']; ?></a>&nbsp;</td>
	</tr>
<?php

	}
	while ($row = $db->sql_fetchrow($result));
}

?>
	<tr>
		<td class="cat" colspan="5" align="center"><input type="submit" class="mainoption" name="add" value="<?php echo $user->lang['ADD_RANK']; ?>" /></td>
	</tr>
</table></form>

<?php

page_footer();

?>